package main

import (
	"fmt"
	"log"
	"net/http"

	"github.com/opentracing-contrib/go-stdlib/nethttp"
	opentracing "github.com/opentracing/opentracing-go"
	jaeger "github.com/uber/jaeger-client-go"
	jconfig "github.com/uber/jaeger-client-go/config"

	"github.com/gianarb/shopmany/frontend/config"
	"github.com/gianarb/shopmany/frontend/handler"
	flags "github.com/jessevdk/go-flags"
	jaegerZap "github.com/uber/jaeger-client-go/log/zap"
	"go.opencensus.io/plugin/ochttp"
	"go.uber.org/zap"
)

func main() {
	logger, _ := zap.NewProduction()
	defer logger.Sync()

	config := config.Config{}
	_, err := flags.Parse(&config)
	if err != nil {
		logger.Fatal(err.Error())
	}

	cfg, err := jconfig.FromEnv()
	if err != nil {
		logger.Fatal(err.Error())
	}
	cfg.Reporter.LogSpans = true
	cfg.Sampler = &jconfig.SamplerConfig{
		Type:  "const",
		Param: 1,
	}
	tracer, closer, err := cfg.NewTracer(jconfig.Logger(jaegerZap.NewLogger(logger.With(zap.String("service", "jaeger-go")))))
	if err != nil {
		logger.Fatal(err.Error())
	}
	defer closer.Close()
	opentracing.SetGlobalTracer(tracer)

	fmt.Printf("Item Host: %v\n", config.ItemHost)
	fmt.Printf("Pay Host: %v\n", config.PayHost)
	fmt.Printf("Discount Host: %v\n", config.DiscountHost)

	mux := http.NewServeMux()

	httpClient := &http.Client{Transport: &ochttp.Transport{}}
	fs := http.FileServer(http.Dir("static"))

	httpdLogger := logger.With(zap.String("service", "httpd"))
	getItemsHandler := handler.NewGetItemsHandler(config, httpClient)
	getItemsHandler.WithLogger(logger)
	payHandler := handler.NewPayHandler(config, httpClient)
	payHandler.WithLogger(logger)
	healthHandler := handler.NewHealthHandler(config, httpClient)
	healthHandler.WithLogger(logger)

	mux.Handle("/", fs)
	mux.Handle("/api/items", getItemsHandler)
	mux.Handle("/api/pay", payHandler)
	mux.Handle("/health", healthHandler)

	log.Println("Listening on port 3000...")
	http.ListenAndServe(":3000", nethttp.Middleware(tracer, loggingMiddleware(httpdLogger.With(zap.String("from", "middleware")), mux)))
}

func loggingMiddleware(logger *zap.Logger, h http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if span := opentracing.SpanFromContext(r.Context()); span != nil {
			if sc, ok := span.Context().(jaeger.SpanContext); ok {
				w.Header().Add("X-Trace-ID", sc.TraceID().String())
			}
		}
		logger.Info(
			"HTTP Request",
			zap.String("Path", r.URL.Path),
			zap.String("Method", r.Method),
			zap.String("RemoteAddr", r.RemoteAddr))
		h.ServeHTTP(w, r)
	})
}
