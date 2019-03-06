$(document).ready(function() {

  var totPriceCart = 0.00;
  var customerID = randomString(12, "aA#");

  $(".tot-price").append(totPriceCart.toFixed(2));
  $(".me-name").append("John");
  $(".me-id").append(customerID);

  $.get( "/api/items", function( data ) {
    items = $("#items")
    data.items.forEach(function(item) {
      if (item.discount != 0) {
        item.isDiscount = true
      }
      items.append(Mustache.render(itemTemplate, item))
    })

    $(".order").click(function(e) {
      var originalPrice = parseFloat(data.items[$(this).attr("data-id")].price);
      var discount = data.items[$(this).attr("data-id")].discount;
      var price = originalPrice - (discount * originalPrice/100);
      totPriceCart= totPriceCart + price;
      $(".tot-price").html(totPriceCart.toFixed(2))
      $("#cart").append(Mustache.render(cartItemTemplate, {
        "name": data.items[$(this).attr("data-id")].name,
        "price": parseFloat(data.items[$(this).attr("data-id")].price).toFixed(2)
      }))
    });
  })
  .fail(function() {
    alert( "error" );
  });

  $("#pay").click(function(e) {
    var body = JSON.stringify({
      "customer_id": customerID,
      "tot_price": totPriceCart
    });
    jQuery.ajax({
      url: '/api/pay',
      type: 'POST',
      dataType: 'json',
      data: body,
      contentType: 'application/json',
      success: function(result) {
        totPriceCart = 0;
        $(".tot-price").html(totPriceCart.toFixed(2));
        $("#cart").html("");
        alert("Thank and see you soon!");
      }})
      .fail(function(err) {
        alert("They checkout failed sorry. Try again later.");
      });
  })
})

// Generate the clusterID
function randomString(length, chars) {
   var mask = '';
  if (chars.indexOf('a') > -1) mask += 'abcdefghijklmnopqrstuvwxyz';
  if (chars.indexOf('A') > -1) mask += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  if (chars.indexOf('#') > -1) mask += '0123456789';
  var result = '';
  for (var i = length; i > 0; --i) result += mask[Math.floor(Math.random() * mask.length)];
  return result;
}


var itemTemplate = `<div class="col-md-4">
        <figure class="card card-product">
            <div class="img-wrap"><img class="img-responsive" src="/img/product-{{id}}.jpg"></div>
            <figcaption class="info-wrap">
                    <h4 class="title">{{name}}</h4>
                    <p class="desc">{{description}}</p>
                    <div class="rating-wrap">
                    </div> <!-- rating-wrap.// -->
            </figcaption>
            <div class="bottom-wrap">
                <button href="" class="btn btn-sm btn-primary float-right order" data-price="{{price}}" data-discount="{{discount}}" data-id="{{id}}">Order Now</button>
                <div class="price-wrap h5">
                    <span class="price-new">$ {{price}}</span>
                    {{#isDiscount}}
                    <del class="price-old" style="color:#28a745">-{{discount}}%</del>
                    {{/isDiscount}}
                </div> <!-- price-wrap.// -->
            </div> <!-- bottom-wrap.// -->
        </figure>
    </div> <!-- col // -->`

var cartItemTemplate = `<li>\${{price}} {{name}}</li>`
