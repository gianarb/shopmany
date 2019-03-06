package handler

import (
	"fmt"
	"net/http"

	"github.com/gianarb/shopmany/frontend/config"
)

type payHandler struct {
	config  config.Config
	hclient *http.Client
}

func NewPayHandler(config config.Config, hclient *http.Client) *payHandler {
	return &payHandler{
		config:  config,
		hclient: hclient,
	}
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
