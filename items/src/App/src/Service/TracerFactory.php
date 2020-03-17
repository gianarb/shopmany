<?php
namespace App\Service;

use Zipkin\Endpoint;
use Psr\Log\NullLogger;
use Zipkin\TracingBuilder;
use OpenTracing\GlobalTracer;
use OpenTracing\NoopTracer;
use ZipkinOpenTracing\Tracer;
use Zipkin\Samplers\BinarySampler;
use Psr\Container\ContainerInterface;
use Zipkin\Reporters\Http\CurlFactory;
use Zipkin\Reporters\Http as HttpReporter;

class TracerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $zipkinConfig = $container->get('config')['zipkin'] ?? [];
        if (empty($zipkinConfig)) {
            // If zipkin is not configured then we return an empty tracer.
            return NoopTracer::create();
        }

        $endpoint = Endpoint::create($zipkinConfig['serviceName']);
        $reporter = new HttpReporter(CurlFactory::create(), ["endpoint_url" => $zipkinConfig['reporterURL'] ?? 'http://localhost:9411/api/v2/spans']);
        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
           ->havingSampler($sampler)
           ->havingReporter($reporter)
           ->build();

        $zipkinTracer = new Tracer($tracing);

        register_shutdown_function(function () {
            /* Flush the tracer to the backend */
            $zipkinTracer = GlobalTracer::get();
            $zipkinTracer->flush();
        });

        GlobalTracer::set($zipkinTracer);
        return $zipkinTracer;
    }
}
