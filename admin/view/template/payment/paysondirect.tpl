<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
            <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <table class="form"> 
                    <tr>
                        <td><?php echo $entry_sort_order; ?></td>
                        <td><input type="text" name="paysondirect_sort_order" value="<?php echo $paysondirect_sort_order; ?>" size="1" /></td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_status; ?></td>
                        <td><select name="paysondirect_status">
                                <?php if ($paysondirect_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><?php echo $payment_method_mode; ?></td>
                        <td><select name="payment_mode">
                                <?php if ($payment_mode) { ?>
                                <option value="1" selected="selected"><?php echo $payment_method_mode_live; ?></option>
                                <option value="0"><?php echo $payment_method_mode_sandbox; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $payment_method_mode_live; ?></option>
                                <option value="0" selected="selected"><?php echo $payment_method_mode_sandbox; ?></option>
                                <?php } ?>
                            </select></td>
                    </tr> 
                    <tr>
                        <td><span class="required">*</span> <?php echo $user_name; ?></td>
                        <td><input type="text" name="payson_user_name" value="<?php echo $payson_user_name; ?>" />
                            <?php if ($error_user_name) { ?>
                            <span class="error"><?php echo $error_user_name; ?></span>
                            <?php } ?></td>
                    </tr>

                    <tr>
                        <td><span class="required">*</span> <?php echo $agent_id; ?></td>
                        <td><input type="text" name="payson_agent_id" value="<?php echo $payson_agent_id; ?>" />
                            <?php if ($error_agent_id) { ?>
                            <span class="error"><?php echo $error_agent_id; ?></span>
                            <?php } ?></td>
                    </tr>


                    <tr>
                        <td><span class="required">*</span> <?php echo $md5; ?></td>
                        <td><input type="text" name="payson_md5" value="<?php echo $payson_md5; ?>" />
                            <?php if ($error_md5) { ?>
                            <span class="error"><?php echo $error_md5; ?></span>
                            <?php } ?></td>
                    </tr>       
                    <tr>
                        <td><?php echo $payment_method_card_bank_info; ?></td>
                        <td>

                            <?php if ($payson_payment_method == '2') { ?>
                            <input type="radio" name="payson_payment_method" value="0" />
                            <?php echo $payment_method_card_bank; ?><br />
                            <input type="radio" name="payson_payment_method" value="1" />
                            <?php echo $payment_method_card; ?><br />
                            <input type="radio" name="payson_payment_method" value="2" checked="checked"/>
                            <?php echo $payment_method_bank; ?>		        
                            <?php } elseif ($payson_payment_method == '1') { ?>
                            <input type="radio" name="payson_payment_method" value="0" />
                            <?php echo $payment_method_card_bank; ?><br />
                            <input type="radio" name="payson_payment_method" value="1" checked="checked" />
                            <?php echo $payment_method_card; ?><br />
                            <input type="radio" name="payson_payment_method" value="2" />
                            <?php echo $payment_method_bank; ?>		
                            <?php } else {?>	
	            <input type="radio" name="payson_payment_method" value="0" checked="checked" />
	            <?php echo $payment_method_card_bank; ?><br />
	            <input type="radio" name="payson_payment_method" value="1" />
				<?php echo $payment_method_card; ?><br />
	 			<input type="radio" name="payson_payment_method" value="2" />
	 			<?php echo $payment_method_bank; ?>				
 			<?php } ?>
 			
 			</td>
          </tr>

          <tr>
            <td><?php echo $secure_word; ?></td>
            <td><input type="text" name="payson_secure_word" value="<?php echo $payson_secure_word; ?>" /></td>
          </tr>
          
          <tr>
            <td><?php echo $entry_logg; ?></td>
                                <td><select name="payson_logg">
                                <option value="1" <?php echo ($payson_logg == 1 ? 'selected="selected"' : '""') . '>'  . $text_enabled?></option>
                                <option value="0" <?php echo ($payson_logg == 0 ? 'selected="selected"' : '""') . '>' . $text_disabled?></option>
                            </select></td>
          </tr>          
                  
          <tr>
            <td><?php echo $entry_total; ?></td>
            <td><input type="text" name="paysondirect_total" value="<?php echo $paysondirect_total; ?>" /></td>
          </tr>        
          <tr>
            <td><?php echo $entry_order_status; ?></td>
            <td><select name="paysondirect_order_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $paysondirect_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="paysondirect_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $paysondirect_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_totals_to_ignore; ?></td>
            <td><input type="text" name="paysondirect_ignored_order_totals" value="<?php echo ($paysondirect_ignored_order_totals == '' ? '' : $paysondirect_ignored_order_totals); ?>" />
            <?php if ($error_ignored_order_totals) { ?>

            <span class="error"><?php echo $error_ignored_order_totals; ?></span>
            <?php } ?></td>
          </tr>    
          
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?> 