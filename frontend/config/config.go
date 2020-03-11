package config

type Config struct {
	ItemHost      string `long:"item-host" description:"The hostname where the item service is located" default:"http://item"`
	DiscountHost  string `long:"discount-host" description:"The hostname where the discount service is located" default:"http://discount:3000"`
	PayHost       string `long:"pay-host" description:"The hostname where the pay service is located" default:"http://pay:8080"`
	Tracer        string `long:"tracer" description:"The place where traces get shiped to. By default it is stdout. Jaeger is also supported" default:"stdout"`
	JaegerAddress string `long:"tracer-jaeger-address" description:"If Jaeger is set as tracer output this is the way you ovverride where to ship data to" default:"http://localhost:14268/api/traces"`
}
