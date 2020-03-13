<?php
namespace App\Service;

use Psr\Container\ContainerInterface;
use OpenTracing\GlobalTracer;
use Psr\Log\NullLogger;
use Zipkin\Endpoint;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;
use Zipkin\Reporters\Http;

class TracerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $zipkinConfig = $container->get('config')['zipkin'];
        $endpoint = Endpoint::create(zipkinConfig['serviceName'], zipkinConfig['host'] , null, zipkinConfig['port']);
        $reporter = new Zipkin\Reporters\Http();
        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
           ->havingSampler($sampler)
           ->havingReporter($reporter)
           ->build();

        $zipkinTracer = new ZipkinOpenTracing\Tracer($tracing);

        register_shutdown_function(function() {
        /* Flush the tracer to the backend */
            $zipkinTracer = GlobalTracer::get();
            $zipkinTracer->flush();
        });

        GlobalTracer::set($zipkinTracer);
        return $zipkinTracer;
    }
}

