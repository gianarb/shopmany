<?php
namespace App\Middleware;

use Psr\Container\ContainerInterface;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TracerMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("config")["opentracing-jaeger-exporter"];
        return new TracerMiddleware($config);
    }
}
