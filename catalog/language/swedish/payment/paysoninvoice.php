<?php
//if(preg_match('/^Mozilla\/.*?Firefox/i',$_SERVER['HTTP_USER_AGENT']) || preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT']))
$_['text_title']  = '<img src="https://www.payson.se/sites/all/files/images/external/payson_faktura.png" style="width:140px;height:55px;display: block; float: left;" alt="Payson Faktura" title="Paysoninvoice" /><div style="width:500px; float: right;">'.utf8_encode("Om du v�ljer att betala med Paysonfaktura så tillkommer en avgift. Betalningsvillkor är 10 dagar och fakturan kommer att sändas separat med e-post till den e-postadress Du anger. För att betala mot Paysonfaktura måste Du ha fyllt 18 år och vara folkbokförd i Sverige samt godkännas i den kreditprövning som genomf�rs vid köpet.").'</div>';   
//else
 //   $_['text_title']  = '<img src="catalog/view/theme/default/image/paysoninvoice.png" alt="Paysoninvoice" title="Paysoninvoice" />';
 //$_['text_title']  =  '<img src="catalog/view/theme/default/image/paysoninvoice.png" alt="Paysoninvoice" title="Paysoninvoice" style="border: 0px solid #EEEEEE;" />'; 
$_['text_denied']  = 'Betalningen blev nekad.';
$_['text_payson_payment_method']  = ' Testa med en annan betalningsmetod.';
?>