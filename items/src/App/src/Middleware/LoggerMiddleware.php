<?php
namespace App\Middleware;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Monolog\Processor\TagProcessor;

class LoggerMiddleware implements MiddlewareInterface
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
        $this->logger->pushProcessor(new TagProcessor([
            "service" => "logger_middleware",
        ]));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $isGood = true;
        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
            $this->logger->panic("HTTP Server", [
                "path", $request->getUri()->getPath(),
                "method", $request->getMethod(),
                "status_code" => $response->getStatusCode(),
                "error" => $e->getMessage(),
            ]);
            $isGood=false;
        }
        if ($isGood) {
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() <= 299) {
                $this->logger->info("HTTP Server", [
                    "path", $request->getUri()->getPath(),
                    "method", $request->getMethod(),
                    "status_code" => $response->getStatusCode(),
                ]);
            } else {
                $this->logger->warn("HTTP Server", [
                    "path", $request->getUri()->getPath(),
                    "method", $request->getMethod(),
                    "status_code" => $response->getStatusCode(),
                ]);
            }
        }
        return $response;
    }
}
