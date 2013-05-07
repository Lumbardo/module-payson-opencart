<?php
// Example field added (see related part in admin/controller/module/my_module.php)
$_['paysondirect_example'] 			= 'Example Extra Text';

// Heading Goes here:
$_['heading_title']            		= 'Payson Direct';
// Text
$_['text_payment']             		= 'Payment';
$_['text_success']       	   		= 'Success: Du har &auml;ndrat Payson Direktbetalning modulen!';
$_['text_paysondirect']       		= '<a onclick="window.open(\'https://www.payson.se/tj%C3%A4nster/ta-betalt\');"><img src="view/image/payment/payson.png" alt="Payson" title="Payson" /></a>';

// Entry
$_['payment_method_mode']     	 	='Mode:<br /><span class="help">V&auml;lj l&auml;get (Produktionsmilj&ouml; eller testmilj&ouml;)</span>';
$_['payment_method_mode_live']     	='Produktionsmilj&ouml;';
$_['payment_method_mode_sandbox']   ='Testmilj&ouml;';

$_['user_name']     	       		= 'E-postadress:<br /><span class="help">Ange din e-postadress f&ouml;r ditt Paysonkonto</span>';
$_['agent_id']       	       		= 'Agent Id:<br /><span class="help">Ange ditt agentID f&ouml;r ditt Paysonkonto</span>';
$_['md5']     		     	   		= 'MD5-nyckel:<br /><span class="help">Ange din MD5nyckel f&ouml;r ditt Paysonkonto</span>';
$_['payment_method_card_bank_info'] = 'Betalningsmetoden:<br /><span class="help">Ange betalningsmetoden som du t&auml;nker aktivera f&ouml;r kunderna (Visa, Mastercard & Internetbank).</span>';
$_['payment_method_card_bank'] 		= 'KORT / BANK:';
$_['payment_method_card']      		= 'KORT:';
$_['payment_method_bank']      		= 'BANK:';
$_['secure_word']      				= 'Hemligt ord :<br /><span class="help">Ange ett hemligt ord.</span>';

$_['entry_total']             		= 'Totalt:<br /><span class="help">Kassan totala ordern m&aring;ste uppn&aring; innan betalningsmetod blir aktiv.</span>';
$_['entry_order_status']       		= 'Order Status:';
$_['entry_geo_zone']           		= 'Geo Zone:';
$_['entry_status']             		= 'Status:';
$_['entry_sort_order']         		= 'Sorteringsordning:';
$_['entry_logg']   					= 'Logg:<br /><span class="help">Fels&ouml;ka svaret fr&aring;n Payson Direkt (Ange 1 elle 0)</span>';

// Error
$_['error_permission']   			= 'Varning: Du har inte beh&ouml;righet att &auml; ndra betalningsmetoden Payson Direct!';
$_['error_user_name']     			= 'E-postadress saknas!';
$_['error_agent_id']     			= 'Agent ID saknas!';
$_['error_md5']     				= 'MD5-nyckel saknas!';

?>