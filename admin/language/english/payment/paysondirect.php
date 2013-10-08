<?php
$_['paysondirect_example'] = 'Example Extra Text';

// Heading Goes here:
$_['heading_title']                	= 'Payson Direct';
// Text
$_['text_payment']      			= 'Payment';
$_['text_success']       			= 'Success: You have modified Payson Direct payment module!';
$_['text_paysondirect']       		= '<a onclick="window.open(\'https://www.payson.se/tj%C3%A4nster/ta-betalt\');"><img src="view/image/payment/payson.png" alt="Payson" title="Payson" /></a>';

// Entry
$_['payment_method_mode']     	 	='Mode:<br /><span class="help">Select environment (Production or Test)</span>';
$_['payment_method_mode_live']     	='Production';
$_['payment_method_mode_sandbox']     	 	='Test';

$_['user_name']     	 			= 'Seller Email:<br /><span class="help">Enter your seller email for Payson.</span>';
$_['agent_id']       	 			= 'Agent id:<br /><span class="help">Enter your Agent id for Payson.</span>';
$_['md5']     		     			= 'md5:<span class="help">Enter your MD5 key for Payson.</span>';
$_['payment_method_card_bank_info'] = 'Payment method:<br /><span class="help">Enabled payment methods (Visa, Mastercard & Internetbank).</span>';
$_['payment_method_card_bank'] 		= 'CREDITCARD / BANK';
$_['payment_method_card']      		= 'CREDITCARD';
$_['payment_method_bank']      		= 'BANK';
$_['secure_word']      				= 'Secure word:<br /><span class="help">Enter a secure word for Paysondirect</span>';

$_['entry_total']        			= 'Total:<br /><span class="help">The checkout total the order must reach before this payment method becomes active.</span>';
$_['entry_order_status'] 			= 'Order Status:';
$_['entry_geo_zone']     			= 'Geo Zone:';
$_['entry_status']       			= 'Status:';
$_['entry_sort_order']   			= 'Sort Order:';
$_['entry_logg']   				= 'Logg:<br /><span class="help">You can find your logs in Admin | System -> Error Log. </span>';
$_['entry_totals_to_ignore'] 			= 'Order totals to ignore:<br/><span class="help">Comma separated list with order totals not to send to payson</span>';

$_['entry_order_item_details_to_ignore'] 	= 'Order Item Details to ignore by CREDITCARD / BANK:<br /><span class="help">Note: Order Items are required for INVOICE payments and optional for other payment types. Also, please note that the total sum of all order items amount (inc. VAT) must match the total sum of all receivers amount.</span>';
// Error
$_['error_permission']   			= 'Warning: You do not have permission to modify payment Payson Direct!';
$_['error_user_name']     			= 'E-mail Required!';
$_['error_agent_id']     			= 'Agent ID Required!';
$_['error_md5']     				= 'MD5-key Required!';
$_['error_ignored_order_totals']     		= 'Enter a comma separated list with order totals not to send to payson';
?>