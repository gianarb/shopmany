package main

import (
	"fmt"
	"log"
	"net/http"

	"github.com/gianarb/shopmany/frontend/config"
	"github.com/gianarb/shopmany/frontend/handler"
	flags "github.com/jessevdk/go-flags"
	"go.opentelemetry.io/otel/api/global"
	"go.opentelemetry.io/otel/exporters/trace/jaeger"
	"go.opentelemetry.io/otel/exporters/trace/stdout"
	"go.opentelemetry.io/otel/plugin/othttp"
	sdktrace "go.opentelemetry.io/otel/sdk/trace"
	"go.uber.org/zap"
)

func main() {
	logger, _ := zap.NewProduction()
	defer logger.Sync()
	config := config.Config{}
	_, err := flags.Parse(&config)

	if err != nil {
		panic(err)
	}

	exporter, err := stdout.NewExporter(stdout.Options{PrettyPrint: true})
	if err != nil {
		log.Fatal(err)
	}
	tp, err := sdktrace.NewProvider(sdktrace.WithConfig(sdktrace.Config{DefaultSampler: sdktrace.AlwaysSample()}),
		sdktrace.WithSyncer(exporter))
	if err != nil {
		log.Fatal(err)
	}
	global.SetTraceProvider(tp)

	if config.Tracer == "jaeger" {

		logger.Info("Used the tracer output jaeger")
		// Create Jaeger Exporter
		exporter, err := jaeger.NewExporter(
			jaeger.WithCollectorEndpoint(config.JaegerAddress),
			jaeger.WithProcess(jaeger.Process{
				ServiceName: "frontend",
			}),
		)
		if err != nil {
			log.Fatal(err)
		}

		// For demoing purposes, always sample. In a production application, you should
		// configure this to a trace.ProbabilitySampler set at the desired
		// probability.
		tp, err := sdktrace.NewProvider(
			sdktrace.WithConfig(sdktrace.Config{DefaultSampler: sdktrace.AlwaysSample()}),
			sdktrace.WithSyncer(exporter))
		if err != nil {
			log.Fatal(err)
		}
		global.SetTraceProvider(tp)
		defer exporter.Flush()
	}

	fmt.Printf("Item Host: %v\n", config.ItemHost)
	fmt.Printf("Pay Host: %v\n", config.PayHost)
	fmt.Printf("Discount Host: %v\n", config.DiscountHost)

	mux := http.NewServeMux()

	httpClient := &http.Client{}
	fs := http.FileServer(http.Dir("static"))

	httpdLogger := logger.With(zap.String("service", "httpd"))
	getItemsHandler := handler.NewGetItemsHandler(config, httpClient)
	getItemsHandler.WithLogger(logger)
	payHandler := handler.NewPayHandler(config, httpClient)
	payHandler.WithLogger(logger)
	healthHandler := handler.NewHealthHandler(config, httpClient)
	healthHandler.WithLogger(logger)

	mux.Handle("/", fs)
	mux.Handle("/api/items", othttp.NewHandler(getItemsHandler, "http.GetItems"))
	mux.Handle("/api/pay", othttp.NewHandler(payHandler, "http.Pay"))
	mux.Handle("/health", othttp.NewHandler(healthHandler, "http.health"))

	log.Println("Listening on port 3000...")
	http.ListenAndServe(":3000", loggingMiddleware(httpdLogger.With(zap.String("from", "middleware")), mux))
}

func loggingMiddleware(logger *zap.Logger, h http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		logger.Info(
			"HTTP Request",
			zap.String("Path", r.URL.Path),
			zap.String("Method", r.Method),
			zap.String("RemoteAddr", r.RemoteAddr))
		h.ServeHTTP(w, r)
	})
}
