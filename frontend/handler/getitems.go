package handler

import (
	"context"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"net/http"
	"strconv"

	"github.com/gianarb/shopmany/frontend/config"
)

type ItemsResponse struct {
	Items []Item `json:"items"`
}

type Item struct {
	ID          int     `json:"id"`
	Name        string  `json:"name"`
	Description string  `json:"description"`
	Price       float64 `json:"price"`
	Discount    int     `json:"discount"`
}

type DiscountResponse struct {
	Discount struct {
		ID             string `json:"_id"`
		ItemID         int    `json:"itemID"`
		DropOffPercent int    `json:"dropOffPercent"`
	} `json:"discount"`
}

func getDiscountPerItem(ctx context.Context, hclient *http.Client, itemID int, discountHost string) (int, error) {
	req, err := http.NewRequest("GET", fmt.Sprintf("%s/discount", discountHost), nil)
	if err != nil {
		return 0, err
	}
	q := req.URL.Query()
	q.Add("itemid", strconv.Itoa(itemID))
	req.URL.RawQuery = q.Encode()
	resp, err := hclient.Do(req)
	if err != nil {
		return 0, err
	}
	if resp.StatusCode == 200 {
		d := DiscountResponse{}
		body, err := ioutil.ReadAll(resp.Body)
		if err != nil {
			return 0, err
		}
		err = json.Unmarshal(body, &d)
		if err != nil {
			return 0, err
		}
		return d.Discount.DropOffPercent, nil
	}

	return 0, nil
}

type getItemsHandler struct {
	config  config.Config
	hclient *http.Client
}

func NewGetItemsHandler(config config.Config, hclient *http.Client) *getItemsHandler {
	return &getItemsHandler{
		config:  config,
		hclient: hclient,
	}
}

func (h *getItemsHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	ctx := r.Context()
	w.Header().Add("Content-Type", "application/json")
	req, err := http.NewRequest("GET", fmt.Sprintf("%s/item", h.config.ItemHost), nil)
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}
	resp, err := h.hclient.Do(req)
	defer resp.Body.Close()
	body, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}
	items := ItemsResponse{
		Items: []Item{},
	}
	err = json.Unmarshal(body, &items)
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}

	for k, item := range items.Items {
		d, err := getDiscountPerItem(ctx, h.hclient, item.ID, h.config.DiscountHost)
		if err != nil {
			http.Error(w, err.Error(), 500)
			continue
		}
		items.Items[k].Discount = d
	}

	b, err := json.Marshal(items)
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}
	fmt.Fprintf(w, string(b))
}
