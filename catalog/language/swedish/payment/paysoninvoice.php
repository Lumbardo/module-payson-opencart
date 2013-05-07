<?php
//if(preg_match('/^Mozilla\/.*?Firefox/i',$_SERVER['HTTP_USER_AGENT']) || preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT']))
$_['text_title']  = '<img src="https://www.payson.se/sites/all/files/images/external/payson_faktura.png" style="width:140px;height:55px;display: block; float: left;" alt="Payson Faktura" title="Paysoninvoice" /><div style="width:500px; float: right;">'.utf8_encode("Om du v&auml;ljer att betala med Paysonfaktura s&aring; tillkommer en avgift om %skr. Betalningsvillkor &auml;r 10 dagar och fakturan kommer att s&auml;ndas separat med e-post till den e-postadress Du anger. F&ouml;r att betala mot Paysonfaktura m&aring;ste Du ha fyllt 18 &aring;r och vara folkbokf&ouml;rd i Sverige samt godk&auml;nnas i den kreditpr&ouml;vning som genomf&ouml;rs vid k&ouml;pet.").'</div>';   
//else
 //   $_['text_title']  = '<img src="catalog/view/theme/default/image/paysoninvoice.png" alt="Paysoninvoice" title="Paysoninvoice" />';
 //$_['text_title']  =  '<img src="catalog/view/theme/default/image/paysoninvoice.png" alt="Paysoninvoice" title="Paysoninvoice" style="border: 0px solid #EEEEEE;" />'; 
$_['text_denied']  = 'Betalningen blev nekad.';
$_['text_payson_payment_method']  = ' Testa med en annan betalningsmetod.';
?>