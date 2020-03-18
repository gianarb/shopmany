'use strict';

const opentelemetry = require('@opentelemetry/api');
const { NodeTracerProvider } = require('@opentelemetry/node');
const { SimpleSpanProcessor } = require('@opentelemetry/tracing');
const { JaegerExporter } = require('@opentelemetry/exporter-jaeger');
const { B3Propagator } = require('@opentelemetry/core');

module.exports = (serviceName, jaegerHost, logger) => {
  const provider = new NodeTracerProvider({
    plugins: {
      dns: {
        enabled: true,
        path: '@opentelemetry/plugin-dns',
      },
      mongodb: {
        enabled: true,
        path: '@opentelemetry/plugin-mongodb',
      },
      http: {
        enabled: true,
        path: '@opentelemetry/plugin-http',
      },
      express: {
        enabled: true,
        path: '@opentelemetry/plugin-express',
      },
    }
  });

  let exporter = new JaegerExporter({
    logger: logger,
    serviceName: serviceName,
    host: jaegerHost
  });

  provider.addSpanProcessor(new SimpleSpanProcessor(exporter));
  provider.register({
    propagator: new B3Propagator(),
  });
  return opentelemetry.trace.getTracer("discount");
};
