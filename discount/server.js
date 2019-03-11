var express = require("express");

var app = express();

const MongoClient = require('mongodb').MongoClient;
const assert = require('assert');

// Connection URL
const url = 'mongodb://discountdb:27017';

// Database Name
const dbName = 'shopmany';

// Create a new MongoClient
const client = new MongoClient(url, { useNewUrlParser: true });


app.get("/discount", function(req, res, next) {
  client.connect(function(err) {
    db = client.db(dbName);
      db.collection('discount').find({}).toArray(function(err, discounts) {
        assert.equal(err, null);
        discounts.forEach(function (s) {
          if (s.itemID+"" == req.query.itemid) {
            res.send({"discount": {s}})
            return
          }
        });
        res.status(404).send({ error: 'Discount not found' });
        return
    })
    client.close();
  });
});

app.use(function(req, res, next){
  res.status(404)
  res.send({ error: 'Route Not found' });
});

app.listen(3000, () => {
  console.log("Server running on port 3000");
});
