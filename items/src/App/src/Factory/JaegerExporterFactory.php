<?php
namespace App\Factory;

use Psr\Container\ContainerInterface;
use OpenCensus\Trace\Exporter\JaegerExporter;

class JaegerExporterFactory
{
    public function __invoke(ContainerInterface $container) {
        $options = $container->get('config')['opentracing-jaeger-exporter'];
        return new JaegerExporter("items", $options);
    }
}
