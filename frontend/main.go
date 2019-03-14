package main

import (
	"fmt"
	"log"
	"net/http"

	"github.com/gianarb/shopmany/frontend/config"
	"github.com/gianarb/shopmany/frontend/handler"
	flags "github.com/jessevdk/go-flags"
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
	mux.Handle("/api/items", getItemsHandler)
	mux.Handle("/api/pay", payHandler)
	mux.Handle("/health", healthHandler)

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
