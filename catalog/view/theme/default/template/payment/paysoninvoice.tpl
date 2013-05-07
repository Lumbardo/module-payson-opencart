<?php //confirm buttons i checkout
ini_set("zlib.output_compression", "Off");
 ?>
 <div class="buttons">
  <div class="right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
	$.ajax({ 
		type: 'GET',
		url: 'index.php?route=payment/paysoninvoice/confirm',
		success: function() {
			location = '<?php echo $action; ?>';
		}		
	});
});
//--></script> 