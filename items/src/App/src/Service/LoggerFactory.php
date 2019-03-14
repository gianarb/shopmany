<?php
namespace App\Service;

use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $logger = new Logger("items");
        $handler = new StreamHandler('php://stdout');
        $handler->setFormatter(new JsonFormatter());
        $logger->pushHandler($handler);
        return $logger;
    }
}

