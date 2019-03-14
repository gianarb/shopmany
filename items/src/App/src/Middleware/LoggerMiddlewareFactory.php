<?php
namespace App\Middleware;

use Psr\Container\ContainerInterface;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Monolog\Processor\TagProcessor;

class LoggerMiddlewareFactory
{
    private $logger;

    public function __invoke(ContainerInterface $container) {
        $logger = $container->get("Logger");
        return new LoggerMiddleware($logger);
    }
}
