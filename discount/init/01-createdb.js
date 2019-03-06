let res = [
  db.discount.drop(),
  db.discount.drop(),
  db.discount.createIndex({ id: 1 }, { unique: true }),
  db.discount.insert({ itemID: 1, dropOffPercent: 50})
]

printjson(res)
