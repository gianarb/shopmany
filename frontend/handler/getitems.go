package handler

import (
	"fmt"
	"io/ioutil"
	"net/http"
)

func GetItemsHandler(hclient *http.Client) func(w http.ResponseWriter, r *http.Request) {
	return func(w http.ResponseWriter, r *http.Request) {
		w.Header().Add("Content-Type", "application/json")
		resp, err := http.Get("http://item/item")
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			fmt.Fprintf(w, fmt.Sprintf("{\"err\": \"%s\"}", err))
			return
		}
		defer resp.Body.Close()
		body, err := ioutil.ReadAll(resp.Body)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			fmt.Fprintf(w, fmt.Sprintf("{\"err\": \"%s\"}", err))
			return
		}
		fmt.Fprintf(w, string(body))
	}
}
