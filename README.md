`shopmay` is a sort of ecommerce made of a bunch of services written using
different languages. I wrote it as codebase for an "Observability workshop". So
the idea is to learn about how to instrument applications because as every
developer know it is hard to understand what is going on in production.

## Items
It is a service contained in the subdirectory `./items`. It is written in PHP
using Expressive 3 as framework.
It contains and manage the items that you can buy from `shopmany`. MySQL is used
as db.

In order to run it you can use `docker-compose`:

```
docker-compose up item
```

Just curl the main entrypoint and you should see a list of items in JS : (it takes a couple of seconds to work because
it loads data and it configures mySQL)

```
$ curl http://localhost/item/10
{"items":[{"id":0,"name":"pen"},{"id":1,"name":"cup"},{"id":2,"name":"coffe"},{"id":3,"name":"table"}]}
```
