<div class="buttons">
    <?php echo isset($isInvoice) ? $text_invoice_terms : ''?>
    <div class="right" id="paymentButton">
        <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
    </div>
    <div class="error" id="paysonError"></div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
        $.ajax({
            type: 'POST',
            url: 'index.php?route=payment/paysondirect/confirm' + '<?php echo isset($isInvoice) ? "&method=invoice" : ""?>',
            dataType: 'json',            
            beforeSend: function() {
                $('#button-confirm').attr('disabled', true);
                $('#paymentButton').before('<div class="attention"><img src="catalog/view/theme/default/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
            },
            complete: function() {
                $('#button-confirm').attr('disabled', false);
                $('.attention').remove();
            },
            success: function(json) {
                    
                if (json['error']) {
                                   
                       alert(json['error']);
                       $('#confirm .checkout-content').slideUp('slow');
                       $('#payment-method .checkout-content').slideDown('slow');
                       
                    
                }

                if (json['paymentURL']) {
                    location.href = json['paymentURL'];
                }
            }
        });
    });
//--></script> 