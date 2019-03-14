<?php
namespace App\Middleware;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OpenTracing\Formats;

use Jaeger\Config;
use Jaeger;
use OpenTracing\GlobalTracer;


class TracerMiddleware implements MiddlewareInterface
{
    private $tracer;

    public function __construct($config)
    {
        $config = new Config($config["options"], $config["service_name"]);
        $this->tracer = $config->initializeTracer();
        GlobalTracer::get($tracer);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $spanContext = GlobalTracer::get()->extract(
            Formats\HTTP_HEADERS,
            getallheaders()
        );
        $spanOpt = [];
        $spanName = $request->getMethod()." ".$request->getUri()->getPath();
        if ($spanContext != null) {
            $spanOpt['child_of'] = $spanContext;
        }
        $span = GlobalTracer::get()->startSpan($spanName, $spanOpt);
        $span->setTag("request_uri", $request->getUri()->__toString());
        $span->setTag("request_headers", json_encode($request->getHeaders()));
        $span->setTag("request_method", $request->getMethod());

        $response = $handler->handle($request);

        $span->setTag("response_status_code", $response->getStatusCode());

        $span->finish();
        $this->tracer->flush();
        return $response;
    }
}
