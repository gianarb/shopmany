package handler

import (
	"context"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"net/http"

	"github.com/gianarb/shopmany/frontend/config"
	"go.opentelemetry.io/otel/api/propagation"
	"go.opentelemetry.io/otel/plugin/httptrace"
	"go.uber.org/zap"
)

const unhealthy = "unhealty"
const healthy = "healthy"

type healthResponse struct {
	Status string
	Checks []check
}

type check struct {
	Error  string
	Status string
	Name   string
}

func NewHealthHandler(config config.Config, hclient *http.Client) *healthHandler {
	logger, _ := zap.NewProduction()
	return &healthHandler{
		config:  config,
		hclient: hclient,
		logger:  logger,
	}
}

type healthHandler struct {
	config  config.Config
	hclient *http.Client
	logger  *zap.Logger
}

func (h *healthHandler) WithLogger(logger *zap.Logger) {
	h.logger = logger
}

func (h *healthHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	b := healthResponse{
		Status: unhealthy,
		Checks: []check{},
	}
	w.Header().Add("Content-Type", "application/json")

	itemCheck := checkItem(r.Context(), h.config.ItemHost, h.hclient)
	if itemCheck.Status == healthy {
		b.Status = healthy
	}

	b.Checks = append(b.Checks, itemCheck)

	body, err := json.Marshal(b)
	if err != nil {
		h.logger.Error(err.Error())
		w.WriteHeader(500)
	}
	if b.Status == unhealthy {
		w.WriteHeader(500)
	}
	fmt.Fprintf(w, string(body))
}

func checkItem(ctx context.Context, host string, hclient *http.Client) check {
	c := check{
		Name:   "item",
		Error:  "",
		Status: unhealthy,
	}
	req, _ := http.NewRequest("GET", fmt.Sprintf("%s/health", host), nil)
	ctx, req = httptrace.W3C(ctx, req)
	propagation.InjectHTTP(ctx, props, req.Header)
	resp, err := hclient.Do(req)
	if err != nil {
		c.Error = err.Error()
		return c
	}
	defer resp.Body.Close()
	if resp.StatusCode >= 200 && resp.StatusCode < 300 {
		c.Status = healthy
		return c
	}
	b, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		c.Error = err.Error()
		return c
	}
	c.Error = string(b)

	return c
}
