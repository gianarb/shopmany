package main

import (
	"fmt"
	"log"
	"net/http"

	"github.com/gianarb/shopmany/frontend/config"
	"github.com/gianarb/shopmany/frontend/handler"
	flags "github.com/jessevdk/go-flags"
)

func main() {
	config := config.Config{}
	_, err := flags.Parse(&config)

	if err != nil {
		panic(err)
	}

	fmt.Printf("Item Host: %v\n", config.ItemHost)
	fmt.Printf("Pay Host: %v\n", config.PayHost)
	fmt.Printf("Discount Host: %v\n", config.DiscountHost)

	httpClient := &http.Client{}
	fs := http.FileServer(http.Dir("static"))

	http.Handle("/", fs)
	http.Handle("/api/items", handler.NewGetItemsHandler(config, httpClient))
	http.Handle("/api/pay", handler.NewPayHandler(config, httpClient))

	log.Println("Listening on port 3000...")
	http.ListenAndServe(":3000", nil)
}
