<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $checkout_script;?>"></script>
<?php echo $header; ?>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <?php if ($error_warning) { ?>
  <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php } ?>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
   
  <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
      <?php if(!$paymentId) { ?>
       <div class="alert alert-danger"> <?php echo $initerror; ?> </div>
      <?php } ?>

      <h1><?php echo $heading_title; ?></h1>
      <div class="panel-group" id="accordion">
          
     <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title"><?php echo $text_checkout_option; ?></h4>
           
          </div>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <td class="text-left"><?php echo $column_name; ?></td>
                    <td class="text-left"><?php echo $column_model; ?></td>
                    <td class="text-right"><?php echo $column_quantity; ?></td>
                    <td class="text-right"><?php echo $column_price; ?></td>
                    <td class="text-right"><?php echo $column_total; ?></td>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $product) { ?>
                  <tr>
                    <td class="text-left"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
                      <?php foreach ($product['option'] as $option) { ?>
                      <br />
                      &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
                      <?php } ?>
                      <?php if($product['recurring']) { ?>
                      <br />
                      <span class="label label-info"><?php echo $text_recurring_item; ?></span> <small><?php echo $product['recurring']; ?></small>
                      <?php } ?></td>
                    <td class="text-left"><?php echo $product['model']; ?></td>
                    <td class="text-right"><?php echo $product['quantity']; ?></td>
                    <td class="text-right"><?php echo $product['price']; ?></td>
                    <td class="text-right"><?php echo $product['total']; ?></td>
                  </tr>
                  <?php } ?>
                  <?php foreach ($vouchers as $voucher) { ?>
                  <tr>
                    <td class="text-left"><?php echo $voucher['description']; ?></td>
                    <td class="text-left"></td>
                    <td class="text-right">1</td>
                    <td class="text-right"><?php echo $voucher['amount']; ?></td>
                    <td class="text-right"><?php echo $voucher['amount']; ?></td>
                  </tr>
                  <?php } ?>
                </tbody>
                <tfoot>
                  <?php foreach ($totals as $total) { ?>
                  <tr>
                    <td colspan="4" class="text-right"><strong><?php echo $total['title']; ?>:</strong></td>
                    <td class="text-right"><?php echo $total['text']; ?></td>
                  </tr>
                  <?php } ?>
                </tfoot>
              </table>
            </div>
        </div>
        <?php if($paymentId) { ?>
            <div id="dibs-complete-checkout"></div>
        <?php } ?>
      </div>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?>
<script>
     var checkoutOptions = {
               checkoutKey: "<?php echo $checkoutkey; ?>",
               paymentId : "<?php echo $paymentId; ?>",
               containerId : "dibs-complete-checkout",
               language: "<?php echo $language; ?>",
	   };
	  var checkout = new Dibs.Checkout(checkoutOptions);
          console.log(checkoutOptions);
	  //this is the event that the merchant should listen to redirect to the “payment-is-ok” page
                checkout.on('payment-completed', function(response) {
                       /*
                       Response:
                                      status: boolean,
                                      paymentId: string (GUID without dashes)
                       */
                       var respObject = JSON.stringify(response);
                       var paymentId = response.paymentId;
                       var checkoutUrl = "<?php echo $checkoutconfirmurl;?>&paymentId=" + paymentId;
                       window.location = checkoutUrl;
   		});	
                cartRemove = cart.remove;
                cart.remove = function(key) {
            	$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
                                window.location = 'index.php?route=checkout/dibseasy';
        		},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
            };
</script>