<?php
namespace App\Middleware;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OpenTracing\Formats;
use OpenTracing\GlobalTracer;

class TracerMiddleware implements MiddlewareInterface
{
    private $tracer;

    public function __construct($tracer)
    {
        $this->tracer = $tracer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $spanContext = GlobalTracer::get()->extract(
            Formats\HTTP_HEADERS,
            getallheaders()
        );
        $span = GlobalTracer::get()->startSpan($request->getMethod().": ".$request->getUri()->getPath(), ['child_of' => $spanContext]);
        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
        }
        $span->finish();
        return $response;
    }
}
