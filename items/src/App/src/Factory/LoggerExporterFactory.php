<?php
namespace App\Factory;

use Psr\Container\ContainerInterface;
use OpenCensus\Trace\Exporter\LoggerExporter;
use Monolog\Processor\TagProcessor;

class LoggerExporterFactory
{
    public function __invoke(ContainerInterface $container) {
        $logger = $container->get("Logger");
        return new LoggerExporter($logger);
    }
}
