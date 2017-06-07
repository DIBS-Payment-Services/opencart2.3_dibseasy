
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-cod" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-cod" class="form-horizontal">
         <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-dibseasy-merchant"><?php echo $entry_dibseasy_merchant; ?></label>
          <div class="col-sm-10">
              <input type="text" name="dibseasy_merchant" value="<?php echo $dibseasy_merchant; ?>" placeholder="" id="input-dibseasy-merchant" class="form-control" />
              <?php if ($error_merchant) { ?>
                <div class="text-danger"><?php echo $error_merchant; ?></div>
	      <?php } ?>
          </div>
         </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="input-dibseasy-merchant" style="font-size: 15px;">Live key</label>
          <div class="col-sm-10">
          </div>
        </div>
        <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-dibseasy-livekey"><?php echo $entry_dibseasy_livekey; ?></label>
          <div class="col-sm-10">
              <input type="text" name="dibseasy_livekey" value="<?php echo $dibseasy_livekey; ?>" placeholder="" id="input-dibseasy-livekey" class="form-control" />
               <?php if ($dibseasy_livekey_error) { ?>
                <div class="text-danger"><?php echo $dibseasy_livekey_error; ?></div>
	      <?php } ?>
          </div>
         </div>
         <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-dibseasy-checkoutkeylive"><?php echo $entry_dibseasy_checkoutkey_live; ?></label>
          <div class="col-sm-10">
              <input type="text" name="dibseasy_checkoutkey_live" value="<?php echo $dibseasy_checkoutkey_live; ?>" placeholder="" id="input-dibseasy-checkoutkey-live" class="form-control" />
               <?php if ($checkout_key_live) { ?>
                <div class="text-danger"><?php echo $checkout_key_live; ?></div>
	      <?php } ?>
          </div>
         </div>
         <div class="form-group">
            <label class="col-sm-2 control-label" for="input-dibseasy-merchant" style="font-size: 15px;">Test (Sandbox) key</label>
          <div class="col-sm-10">
          </div>
        </div>
         <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-dibseasy-testkey"><?php echo $entry_dibseasy_testkey; ?></label>
          <div class="col-sm-10">
              <input type="text" name="dibseasy_testkey" value="<?php echo $dibseasy_testkey; ?>" placeholder="" id="input-dibseasy-testkey" class="form-control" />
               <?php if ($dibseasy_testkey_error) { ?>
                <div class="text-danger"><?php echo $dibseasy_testkey_error; ?></div>
	      <?php } ?>
          </div>
         </div>
         <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-dibseasy-checkoutkeytest"><?php echo $entry_dibseasy_checkoutkey_test; ?></label>
          <div class="col-sm-10">
              <input type="text" name="dibseasy_checkoutkey_test" value="<?php echo $dibseasy_checkoutkey_test; ?>" placeholder="" id="input-dibseasy-checkoutkey-test" class="form-control" />
               <?php if ($checkout_key_test) { ?>
                <div class="text-danger"><?php echo $checkout_key_test; ?></div>
	      <?php } ?>
          </div>
         </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
            <div class="col-sm-10">
              <select name="dibseasy_order_status_id" id="input-order-status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $dibseasy_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
              <label class="col-sm-2 control-label" for="input-shipping-method"> <span data-toggle="tooltip" title="<?php echo $entry_shipping_method_description; ?>"><?php echo $entry_shipping_method; ?></span></label>
            <div class="col-sm-10">
              <select name="dibseasy_shipping_method" id="input-status" class="form-control">
                <?php if ($dibseasy_shipping_method == 'free') { ?>
                    <option value="free" selected="selected"><?php echo $text_free_shipping; ?></option>
                    <option value="flat"><?php echo $text_flat_shipping; ?></option>
                <?php } else { ?>
                    <option value="free"><?php echo $text_free_shipping; ?></option>
                    <option value="flat" selected="selected"><?php echo $text_flat_shipping; ?></option>
                <?php } ?>
              </select>
                <?php if ($free_shipping_disabled) { ?>
                    <div class="text-danger"><?php echo $free_shipping_disabled; ?></div>
                <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="dibseasy_status" id="input-status" class="form-control">
                <?php if ($dibseasy_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
              <label class="col-sm-2 control-label" for="input-status"><span data-toggle="tooltip" title="<?php echo $entry_testmode_description; ?>"><?php echo $entry_testmode; ?></span></label>
            <div class="col-sm-10">
              <select name="dibseasy_testmode" id="input-status" class="form-control">
                <?php if ($dibseasy_testmode) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
              <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_language; ?></label>
            <div class="col-sm-10">
              <select name="dibseasy_language" id="input-language" class="form-control">
                <?php if ($dibseasy_language == 'en-GB') { ?>
                <option value="en-GB" selected="selected"><?php echo $text_english; ?></option>
                <option value="sv-SE"><?php echo $text_swedish; ?></option>
                <?php } else { ?>
                <option value="en-GB"><?php echo $text_english; ?></option>
                <option value="sv-SE" selected="selected"><?php echo $text_swedish; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
              <label class="col-sm-2 control-label" for="input-status"><span data-toggle="tooltip" title="<?php echo $entry_debug_description; ?>"><?php echo $entry_debug; ?></span></label>
            <div class="col-sm-10">
              <select name="dibseasy_debug" id="input-status" class="form-control">
                <?php if ($dibseasy_debug) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?> 