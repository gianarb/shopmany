<?php
namespace App\Middleware;

use ErrorException;
use OpenTracing\Formats;
use OpenTracing\Tags;
use OpenTracing\GlobalTracer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TracerMiddleware implements MiddlewareInterface
{
    private $tracer;

    public function __construct($tracer)
    {
        $this->tracer = $tracer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $spanContext = $this->tracer->extract(
            Formats\HTTP_HEADERS,
            $request
        );
        $span = $this->tracer->startSpan($request->getMethod(), [
            'child_of' => $spanContext,
            'tags' => [
                Tags\HTTP_METHOD => $request->getMethod(),
                'http.path' => $request->getUri()->getPath(),
            ]
        ]);
        
        try {
            $response = $handler->handle($request);
            $span->setTag(Tags\HTTP_STATUS_CODE, $response->getStatusCode());
            return $response;
        } catch (\Throwable $e) {
            $span->setTag(Tags\ERROR, $e->getMessage());
        } finally {
            $span->finish();
        }
    }
}
