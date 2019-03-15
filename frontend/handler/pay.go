package handler

import (
	"fmt"
	"net/http"

	"github.com/gianarb/shopmany/frontend/config"
	opentracing "github.com/opentracing/opentracing-go"
	"go.uber.org/zap"
)

type payHandler struct {
	config  config.Config
	hclient *http.Client
	logger  *zap.Logger
}

func NewPayHandler(config config.Config, hclient *http.Client) *payHandler {
	logger, _ := zap.NewProduction()
	return &payHandler{
		config:  config,
		hclient: hclient,
		logger:  logger,
	}
}

func (h *payHandler) WithLogger(logger *zap.Logger) {
	h.logger = logger
}

func (h *payHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	w.Header().Add("Content-Type", "application/json")
	if r.Method != "POST" {
		http.Error(w, "Method not supported", 405)
		return
	}
	req, err := http.NewRequest("POST", fmt.Sprintf("%s/pay", h.config.PayHost), r.Body)
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}
	req.WithContext(r.Context())
	if span := opentracing.SpanFromContext(r.Context()); span != nil {
		opentracing.GlobalTracer().Inject(
			span.Context(),
			opentracing.HTTPHeaders,
			opentracing.HTTPHeadersCarrier(req.Header))
	}
	req.Header.Add("Content-Type", "application/json")
	resp, err := h.hclient.Do(req)
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}
	if resp.StatusCode > 299 {
		http.Error(w, "Payment failed", 500)
		return
	}
	w.WriteHeader(201)
	fmt.Fprintf(w, "{}")
	return
}
