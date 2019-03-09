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
// Use connect method to connect to the Server
  client.connect(function(err) {
    db = client.db(dbName);
      db.collection('discount').find({}).toArray(function(err, discounts) {
        assert.equal(err, null);
        discounts.forEach(function (s) {
          if (s.itemID+"" == req.query.itemid) {
            res.send({"discount": {s}})
          }
      });
    })
    client.close();
  });
});

app.listen(3000, () => {
  console.log("Server running on port 3000");
});
