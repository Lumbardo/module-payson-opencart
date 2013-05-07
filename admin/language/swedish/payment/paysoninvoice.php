<?php
// Example field added (see related part in admin/controller/module/my_module.php)
$_['paysoninvoice_example'] 		   = 'Example Extra Text';

// Heading Goes here:
$_['heading_title']      			   = 'Payson Faktura';
// Text
$_['text_payment']       			   = 'Payment';
$_['text_success']       			   = 'Success: Du har &auml; ndrat Payson Direktbetalning modulen!';
$_['text_paysoninvoice'] 			   = '<a onclick="window.open(\'https://www.payson.se/tj%C3%A4nster/paysonfaktura\');"><img src="view/image/payment/paysoninvoice.png" alt="Payson Invoice" title="Payson" /></a>';

// Entry
$_['entry_paysoninvoice_mode']     	 	='Mode:<br /><span class="help">V&auml;lj l&auml;get (Produktionsmilj&ouml; eller testmilj&ouml;)</span>';
$_['entry_paysoninvoice_mode_live']     ='Produktionsmilj&ouml;';
$_['entry_paysoninvoice_mode_sandbox']  ='Testmilj&ouml;';

$_['entry_paysoninvoice_user_name']    = 'E-postadress:<br /><span class="help">Ange din e-postadress f&ouml;r ditt Paysonkonto</span>';
$_['entry_paysoninvoice_agent_id']     = 'Agent Id:<br /><span class="help">Ange ditt agentID f&ouml;r ditt Paysonkonto</span>';
$_['entry_paysoninvoice_md5']     	   = 'MD5-nyckel:<br /><span class="help">Ange din MD5nyckel f&ouml;r ditt Paysonkonto</span>';
$_['entry_paysoninvoice_fee']  		   = 'Fakturaavgift:<br /><span class="help">Ange fakturaavgiften exkl. moms</span>';
$_['entry_paysoninvoice_fee_tax']      = 'Faktureringsavgift skatt:<br /><span class="help">Ange skatt f&ouml;r fakturan (25 %)</span>';
$_['entry_paysoninvoice_secure_word']  = 'Hemligt ord :<br /><span class="help">Ange ett hemligt ord.</span>';

$_['entry_total']        			   = 'Totalt:<br /><span class="help">Kassan totala ordern m&aring;ste uppn&aring; innan betalningsmetod blir aktiv.</span>';
$_['entry_order_status']               = 'Order Status:<br /><span class="help">Set the status of orders made with this payment module that have completed payment to this value
										 ("Processing" recommended)</span>';
$_['entry_geo_zone']                   = 'Geo Zone:';
$_['entry_status']                     = 'Status:';
$_['entry_sort_order']                 = 'Sort Order:';
$_['entry_logg']   					   = 'Logg:<br /><span class="help">Fels&ouml;ka svaret fr&aring;n Payson Faktura (Ange 1 eller 0)</span>';

// Error
$_['error_permission']                 = 'Varning: Du har inte beh&ouml;righet att &auml; ndra betalningsmetoden Payson Direct!';
$_['error_user_name']     			   = 'E-mail Required!';
$_['error_agent_id']     			   = 'Agent ID Required!';
$_['error_md5']     				   = 'MD5-key Required!';
?>