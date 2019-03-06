package config

type Config struct {
	ItemHost     string `long:"item-host" description:"The hostname where the item service is located" default:"http://item"`
	DiscountHost string `long:"discount-host" description:"The hostname where the discount service is located" default:"http://discount:3000"`
	PayHost      string `long:"pay-host" description:"The hostname where the pay service is located" default:"http://pay:8080"`
}
