'use strict';

const opentelemetry = require('@opentelemetry/api');
const { NodeTracerProvider } = require('@opentelemetry/node');
const { SimpleSpanProcessor } = require('@opentelemetry/tracing');
const { JaegerExporter } = require('@opentelemetry/exporter-jaeger');

module.exports = (serviceName, jaegerHost, logger) => {
  const provider = new NodeTracerProvider();

  let exporter = new JaegerExporter({
    logger: logger,
    serviceName: serviceName,
    host: jaegerHost
  });

  provider.addSpanProcessor(new SimpleSpanProcessor(exporter));
  opentelemetry.trace.initGlobalTracerProvider(provider);

  return opentelemetry.trace.getTracer("discount");
};
