$(document).ready(function() {

  totPriceCart = 0.00

  $(".tot-price").append(totPriceCart.toFixed(2))

  $(".me-name").append("John");
  $(".me-id").append(randomString(12, "aA#"));

  $.get( "/api/items", function( data ) {
      items = $("#items")
      data.items.forEach(function(item) {
          items.append(Mustache.render(itemTemplate, item))
      })

      $(".order").click(function(e) {
        totPriceCart= totPriceCart + parseFloat(data.items[$(this).attr("data-id")].price);
        $(".tot-price").html(totPriceCart.toFixed(2))
        $("#cart").append(Mustache.render(cartItemTemplate, {
          "name": data.items[$(this).attr("data-id")].name,
          "price": data.items[$(this).attr("data-id")].price
        }))
      });
  })
  .fail(function() {
      alert( "error" );
  });
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
            <div class="img-wrap"><img class="img-responsive" src="https://picsum.photos/350/220"></div>
            <figcaption class="info-wrap">
                    <h4 class="title">{{name}}</h4>
                    <p class="desc">Some small description goes here</p>
                    <div class="rating-wrap">
                    </div> <!-- rating-wrap.// -->
            </figcaption>
            <div class="bottom-wrap">
                <button href="" class="btn btn-sm btn-primary float-right order" data-price="{{price}}" data-id="{{id}}">Order Now</button>
                <div class="price-wrap h5">
                    <span class="price-new">$ {{price}}</span> <del class="price-old">-{{discount}}%</del>
                </div> <!-- price-wrap.// -->
            </div> <!-- bottom-wrap.// -->
        </figure>
    </div> <!-- col // -->`

var cartItemTemplate = `<li>\${{price}} {{name}}</li>`
