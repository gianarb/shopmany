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
    private $tracer;

    public function __invoke(ContainerInterface $container) {
        $tracer = $container->get("Tracer");
        return new TracerMiddleware($tracer);
    }
}
