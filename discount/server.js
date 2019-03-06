var express = require("express");

var app = express();

const MongoClient = require('mongodb').MongoClient;
const url = 'mongodb://discountdb:27017';
const dbName = 'shopmany';
const client = new MongoClient(url, { useNewUrlParser: true });
app.use(errorHandler)


app.get("/discount", function(req, res, next) {
  client.connect(function(err) {
    db = client.db(dbName);
    db.collection('discount').find({}).toArray(function(err, discounts) {
      if (err != null) {
        return next(err)
      }
      var goodDiscount = null
      discounts.forEach(function (s) {
        if (s.itemID+"" == req.query.itemid) {
          goodDiscount = s
        }
      });
      if (goodDiscount != null) {
        res.json({"discount": goodDiscount})
      } else {
        res.status(404).json({ error: 'Discount not found' });
      }
      return
    })
  });
});

app.use(function(req, res, next) {
  return res.status(404).json({error: "route not found"});
});

function errorHandler(err, req, res, next) {
  var st = err.status
  if (st == 0 || st == null) {
    st = 500;
  }
  res.status(err.status);
  res.json({ error: err })
}

app.listen(3000, () => {
  console.log("Server running on port 3000");
});
