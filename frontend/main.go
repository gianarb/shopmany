package main

import (
	"log"
	"net/http"

	"github.com/gianarb/shopmany/frontend/handler"
)

func main() {
	httpClient := &http.Client{}
	fs := http.FileServer(http.Dir("static"))

	http.Handle("/", fs)
	http.HandleFunc("/api/items", handler.GetItemsHandler(httpClient))

	log.Println("Listening on port 3000...")
	http.ListenAndServe(":3000", nil)
}
