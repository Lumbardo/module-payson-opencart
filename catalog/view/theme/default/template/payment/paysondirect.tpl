<div class="buttons">
  <div class="right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
	$.ajax({ 
		type: 'GET',
		url: 'index.php?route=payment/paysondirect/confirm' + '<?php echo isset($isInvoice) ? "&method=invoice" : ""?>' ,
		success: function(data) {
			location.href = data;
		},
                error: function(error)
                {
                  alert(error.responseText);  
                }
	});
});
//--></script> 