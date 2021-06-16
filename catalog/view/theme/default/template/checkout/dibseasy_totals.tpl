<?php  if($shipping_methods){ ?>
    <div id="dibs-easy-shipping-title" class="dibs-easy-ctrl-title"><?php echo $shipping_methods_label; ?></div>
    <div id="dibs-easy-shipping-methods">
        <div class="dibs-easy-shipping-method-wrapper">
       <?php  foreach($shipping_methods as $shipping_method){ ?>
          <?php  foreach($shipping_method['quote'] as $quote){ ?>
            <?php  if ($quote['code'] == $code || empty($code)){ ?>
            <?php  $code = $quote['code']; ?>
                <div id="<?php  echo $quote['code'];  ?>" class="dibs-easy-shipping-selector dibs-easy-active"></div>
                <div> <span><?php  echo $quote['title'];  ?></span> - <span><?php echo $quote['text'];  ?></span></div>
            <?php  }else{ ?>
                <div id="<?php echo  $quote['code'];  ?>"  onclick="updateView({action:'set-shipping-method', code: <?php echo "'".$quote['code']."'";  ?>})" class="dibs-easy-shipping-selector dibs-easy-non-active"></div>
               <div> <span><?php echo $quote['title'];  ?></span> - <span><?php echo $quote['text'];  ?></span></div>
            <?php  } ?>
             <div class="clear"></div>
         <?php  } ?>
        <?php  } ?>
        </div>
    </div>
  <?php  } ?>
<div id="dibs-easy-ordersummary-title" class="dibs-easy-ctrl-title"><?php echo $order_summary_label; ?></div>
<table id="totals-table"> 
    <tbody>
  <?php foreach($totals['totals'] as $total){  ?>
      <tr id="tr-<?php  echo $total['code'];  ?>">
         <?php  if ($total['code'] == "sub_total"){ ?>
            <td class="dibs-easy-totals-lable" id="dibs-easy-totals-lable-<?php echo  $total['code']  ?>" id="dibs-easy-totals-lable-<?php echo $total['code'];  ?>"><?php echo $total['title'];  ?></td>  
            <td class="dibs-easy-totals-total" id="dibs-easy-totals-<?php  echo $total['code'];  ?>" id="dibs-easy-totals-<?php  echo $total['code'];  ?>" align="right"><?php  echo $total['value'];  ?></td>
         <?php  } ?>
         
         
         <?php  if ($total['code'] == "shipping"){ ?>
            <td class="dibs-easy-totals-lable" id="dibs-easy-totals-lable-<?php  echo $total['code'];  ?>" id="dibs-easy-totals-lable-<?php  echo $total['code'];  ?>"><?php  echo $total['title'];  ?></td>
            <td class="dibs-easy-totals-total" id="dibs-easy-totals-<?php  echo $total['code'];  ?>" id="dibs-easy-totals-<?php  echo $total['code'];  ?>" align="right">
            <span id="dibs-easy-grand-shipping-value"><?php  echo $total['value'];  ?></span></td>
         <?php  } ?>

          <?php  if ($total['code'] == "coupon"){ ?>
              <td class="dibs-easy-totals-lable" id="dibs-easy-totals-lable-<?php  echo $total['code'];  ?>" id="dibs-easy-totals-lable-<?php  echo $total['code'];  ?>"><?php  echo $total['title'];  ?></td>
              <td class="dibs-easy-totals-total" id="dibs-easy-totals-<?php  echo $total['code'];  ?>" id="dibs-easy-totals-<?php  echo $total['code'];  ?>" align="right">
                  <span id="dibs-easy-grand-shipping-value"><?php  echo $total['value'];  ?></span></td>
          <?php  } ?>
         
         <?php  if($total['code'] == "total_taxes"){ ?>
            <td class="dibs-easy-totals-lable"><?php  echo $total['title'];  ?></td>  
            <td class="dibs-easy-totals-total" align="right">
            <span id="dibs-easy-ing-value"><?php  echo $total['value'];  ?></span></td>
         <?php  } ?>
      </tr>
   <?php  } ?>
   
     <?php  foreach($totals['totals'] as $total){  ?>
      <tr id="tr-grand-total-id">
         <?php  if($total['code'] == "total"){ ?>
            <td class="dibs-easy-totals-lable" id="dibs-easy-totals-lable-<?php echo $total['code']; ?>" id="dibs-easy-totals-lable-<?php echo $total['code']; ?>"><?php echo $total['title'];  ?></td>  
            <td class="dibs-easy-totals-total" id="dibs-easy-totals-<?php  echo $total['code'];  ?>" id="dibs-easy-totals-<?php  echo $total['code'];  ?>" align="right">
            <span id="dibs-easy-grand-total-currency"><?php echo $currency_code;  ?></span><span id="dibs-easy-grand-total-value"><?php  echo $total['value'];  ?></span>  
            </td>
         <?php  } ?>
      </tr>
   <?php  } ?>
   
  </tbody>
 </table>