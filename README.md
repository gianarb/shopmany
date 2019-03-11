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

```bash
docker-compose up item
```

Just curl the main entrypoint and you should see a list of items in JS : (it takes a couple of seconds to work because
it loads data and it configures mySQL)

```bash
$ curl http://localhost:3001/item
{"items":[{"id":0,"name":"pen"},{"id":1,"name":"cup"},{"id":2,"name":"coffe"},{"id":3,"name":"table"}]}
```

## Discount
Discount uses mongodn as backend and it is an application in NodeJS capable of
giving back the discount % that should be applied to a specific item.

```bash
docker-compose up discount
```

Check it out

```bash
$ curl http://localhost:3003/discount?itemid=10
{"discount": {"dropoff_percent": 12}}
```

## pay
Pay is a java service that manages the purchase of a set of items from a
specific customer. It uses SpringBoot as framework and MySQL as backend
(probably).

```
docker-compose up pay
```

Check it out
```
$ curl http://localhost:3002:/pay
```

## Frontend
Frontend is an HTMP/CSS/JS application with a Go backend and is used a
frontend for the ecommerce.
```
docker-compose up frontend
```

Check it out `http://localhost:3000` using your browser
```
