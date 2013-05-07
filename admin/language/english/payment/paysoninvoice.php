<?php
$_['paysoninvoice_example'] 		   = 'Example Extra Text';

// Heading Goes here:

$_['heading_title']      			   = 'Payson Invoice';
// Text
$_['text_payment']       			   = 'Payment';
$_['text_success']       			   = 'Success: You have modified Payson Invoice payment module!';
$_['text_paysoninvoice'] 			   = '<a onclick="window.open(\'https://www.payson.se/tj%C3%A4nster/paysonfaktura\');"><img src="view/image/payment/paysoninvoice.png" alt="Payson" title="Payson" /></a>';

// Entry
$_['entry_paysoninvoice_mode']     	 	='Mode:<br /><span class="help">Select the mode (Real or Sandbox)</span>';
$_['entry_paysoninvoice_mode_live']     ='Real';
$_['entry_paysoninvoice_mode_sandbox']  ='Sandbox';

$_['entry_paysoninvoice_user_name']    = 'User name:';
$_['entry_paysoninvoice_agent_id']     = 'Agent id:';
$_['entry_paysoninvoice_md5']     	   = 'md5:';
$_['entry_paysoninvoice_fee']      	   = 'Invoice fee:';
$_['entry_paysoninvoice_fee_tax']      = 'Invoice fee tax:<br /><span class="help">TAX for invoice (25 %)</span>';
$_['entry_paysoninvoice_secure_word']  = 'Secure word:<br /><span class="help">Enter a secure word for paysoninvoice</span>';

$_['entry_total']        			   = 'Total:<br /><span class="help">The checkout total the order must reach before this payment method becomes active.</span>';
$_['entry_order_status'] 			   = 'Order Status:';
$_['entry_geo_zone']     			   = 'Geo Zone:';
$_['entry_status']       			   = 'Status:';
$_['entry_sort_order']   			   = 'Sort Order:';
$_['entry_logg']   		 			   = 'Logg:<br /><span class="help">Ddebug the response from Payson (1 or 0).</span>';


// Error
$_['error_permission']   			   = 'Warning: You do not have permission to modify payment Payson Direct!';
$_['error_user_name']     			   = 'E-mail Required!';
$_['error_agent_id']     			   = 'Agent ID Required!';
$_['error_md5']     				   = 'MD5-key Required!';
?>