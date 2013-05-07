<?php
class ControllerPaymentPaysoninvoice extends Controller {
	private $error = array();
	protected function index() {
		$this->load->model('checkout/order');
    	$order_data = $this->model_checkout_order->getOrder($this->session->data['order_id']);

    	$this->data['button_confirm']	 = $this->language->get('button_confirm');
    	$this->data['store_name']		 = html_entity_decode($order_data['store_name'], ENT_QUOTES, 'UTF-8');
    	
    	//Payson send the responds to the shop
    	$this->data['ok_url'] 			 = $this->url->link('payment/paysoninvoice/confirm');
    	$this->data['cancel_url'] 		 = $this->url->link('checkout/checkout');
    	$this->data['ipn_url'] 			 = $this->url->link('payment/paysoninvoice/paysonIpn');
    	//Order info
    	$this->data['order_id']			 = $order_data['order_id'];
    	$this->data['amount']			 = $this->currency->format($order_data['total'], $order_data['currency_code'], $order_data['currency_value'], false);
    	$this->data['currency_code']     = $order_data['currency_code'];
		$this->data['language_code']     = $order_data['language_code'];
		$this->data['total_paysoninvoice_invoice_fee'] = $this->currency->format(($this->config->get('paysoninvoice_invoice_fee_tax')/100) * $this->config->get('paysoninvoice_invoice_fee')+ $this->config->get('paysoninvoice_invoice_fee'), $order_data['currency_code'], $order_data['currency_value'], false);
		$this->data['salt'] 			 = md5($this->config->get('paysoninvoice_secure_word')).'1-'. $this->data['order_id'];
		
		//Customer info
   		$this->data['sender_email']		 = $order_data['email'];
    	$this->data['sender_first_name'] = html_entity_decode($order_data['firstname'], ENT_QUOTES, 'UTF-8');
		$this->data['sender_last_name']  = html_entity_decode($order_data['lastname'], ENT_QUOTES, 'UTF-8');
	
  		//shipping info
		if (isset($this->session->data['shipping_method'])) {
			$shipping_data = $this->session->data['shipping_method'];
			$this->data['shipping_name']	 = $shipping_data['title'];
	    	$this->data['shipping_cost']	 = $this->currency->format($shipping_data['cost'], $order_data['currency_code'], $order_data['currency_value'], false);
	    	$shipping_price_total = $this->tax->calculate($shipping_data['cost'],  $shipping_data['tax_class_id'], $this->config->get('config_tax'));
			//########
			//########Division by zero in 
	    	//$this->data['$shipping_tax_rate'] = ($shipping_price_total/$shipping_data['cost'])-1;
	    	$shipping_data['cost'] != 0 ? $this->data['$shipping_tax_rate'] = ($shipping_price_total/$shipping_data['cost'])-1 : $this->data['$shipping_tax_rate'] = 0;
		}
		
		$this->getOrderItems();
    	//Call PaysonAPI
		if ($this->config->get('paysoninvoice_mode') == 0)
    		$this->data['action'] 		     = $this->paysonApiSandbox();
		else 
			$this->data['action'] 		     = $this->paysonApi();
			
		//Choose which template to display this module with
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysoninvoice.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/paysoninvoice.tpl';
		} else {
			$this->template = 'default/template/payment/paysoninvoice.tpl';
		}	
		$this->render();
	}
	
	public function confirm() {
		$status = false;
		$this->load->model('checkout/order');
		$this->load->language('payment/paysoninvoice');
		
		if(isset($this->request->get['TOKEN'])){	
			$this->load->model('payment/paysoninvoice');
			$status =  $this->model_payment_paysoninvoice->getIpnStatus($this->request->get['TOKEN']);
			//$this->log->write($status);
		}

		if($status === 1){
			//create the order and redirect to success
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('paysoninvoice_order_status_id'));
			$this->redirect($this->url->link('checkout/success'));
		}elseif($status === 2){
			//create the order with status denied
			$this->model_checkout_order->confirm($this->session->data['order_id'], 8);
			$this->paysonApiError($this->language->get('text_denied'));
		}else 
			$this->redirect($this->url->link('checkout/checkout'));	

	}
	
	function paysonApi(){
		require_once 'payson/paysonapi.php';
		
		$credentials = new PaysonCredentials(trim($this->config->get('paysoninvoice_agent_id')), trim($this->config->get('paysoninvoice_md5')), null, 'PAYSON-MODULE-INFO: payson_opencart|1.5|'.VERSION);
		$api = new PaysonApi($credentials);
		
		$receiver = new Receiver(trim($this->config->get('paysoninvoice_user_name')), $this->data['amount']);
		$receivers = array($receiver);
		
		$sender = new Sender($this->data['sender_email'], $this->data['sender_first_name'], $this->data['sender_last_name']);
		$payData = new PayData($this->data['ok_url'], $this->data['cancel_url'], $this->data['ipn_url'], $this->data['store_name'].' Order: '.$this->data['order_id'], $sender, $receivers); 
		$payData->setCurrencyCode($this->currencyPaysoninvoice());
		$payData->setLocaleCode($this->languagePaysoninvoice());
		$payData->setGuaranteeOffered('NO');
		$payData->setTrackingId($this->data['salt']);
		$payData->setOrderItems($this->data['order_items']);
		//It chooses the payment method invoice
		$constraints = array(FundingConstraint::INVOICE);
		$payData->setFeesPayer('PRIMARYRECEIVER');
		$payData->setInvoiceFee($this->getPaysoninvoiceFee());
		$payData->setFundingConstraints($constraints);
		
		$payResponse = $api->pay($payData);
		
		if ($payResponse->getResponseEnvelope()->wasSuccessful())  //ack = SUCCESS och token  = token = N�got
		{   
			//return the url: https://www.payson.se/paysecure/?token=#
			return $api->getForwardPayUrl($payResponse);
		}
		else{
			$error = $payResponse->getResponseEnvelope()->getErrors();
			if ($this->config->get('paysoninvoice_logg') == 1){ 
				$this->log->write('<Payson Ivoice api>'.$error[0]->getErrorId().'<br />'. $error[0]->getMessage() .'<br />'. $error[0]->getParameter());
			}
			if ($error[0]->getErrorId()) {
				$this->paysonApiError($error[0]->getMessage());	
			}
		}	
	}
	
	public function paysonApiError($error) {
		$this->load->language('payment/paysoninvoice');
		$error_code = '<html>
							<head>
								<script type="text/javascript"> 
									alert("'.$error.$this->language->get('text_payson_payment_method').'");
									window.location="'.(HTTPS_SERVER.'index.php?route=checkout/checkout').'";
								</script>
							</head>
					</html>';
		print_r($error_code);
		exit;
	}

	function paysonApiSandbox(){
		require_once 'payson/paysonapiTest.php';
		
		$credentials = new PaysonCredentials(1, 'fddb19ac-7470-42b6-a91d-072cb1495f0a', null, 'PAYSON-MODULE-INFO: payson_opencart|1.5|'.VERSION);
		$api = new PaysonApi($credentials);
		
		$receiver = new Receiver('testagent-1@payson.se', $this->data['amount']);
		$receivers = array($receiver);
		
		$sender = new Sender('test-shopper@payson.se', $this->data['sender_first_name'], $this->data['sender_last_name']);
		$payData = new PayData($this->data['ok_url'], $this->data['cancel_url'], $this->data['ipn_url'], $this->data['store_name'].' Order: '.$this->data['order_id'], $sender, $receivers); 
		$payData->setCurrencyCode($this->currencyPaysoninvoice());
		$payData->setLocaleCode($this->languagePaysoninvoice());
		$payData->setGuaranteeOffered('NO');
		$payData->setTrackingId($this->data['salt']);
		$payData->setOrderItems($this->data['order_items']);
		//It chooses the payment method invoice
		$constraints = array(FundingConstraint::INVOICE);
		$payData->setFeesPayer('PRIMARYRECEIVER');
		$payData->setInvoiceFee($this->getPaysoninvoiceFee());
		$payData->setFundingConstraints($constraints);
				
		$payResponse = $api->pay($payData);
		
		if ($payResponse->getResponseEnvelope()->wasSuccessful())  //ack = SUCCESS och token  = token = N�got
		{   
			//return the url: https://www.payson.se/paysecure/?token=#
			return $api->getForwardPayUrl($payResponse);
		}
		else{
			$error = $payResponse->getResponseEnvelope()->getErrors();
			if ($this->config->get('paysoninvoice_logg') == 1){ 
				$this->log->write('<Payson Ivoice api>'.$error[0]->getErrorId().'<br />'. $error[0]->getMessage() .'<br />'. $error[0]->getParameter());			
			}
			if ($error[0]->getErrorId()) {
				$this->paysonApiError($error[0]->getMessage());	
			}
		}	
	}
	
	private function getPaysoninvoiceFee(){
		if($this->config->get('paysoninvoice_fee_fee') >= 0 && $this->config->get('paysoninvoice_fee_status')){
			$this->load->model('total/paysoninvoice_fee');
			$order_data = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			
			$tax_rates = $this->tax->getRates($this->config->get('paysoninvoice_fee_fee'), $this->config->get('paysoninvoice_fee_tax_class_id'));
			$paysoninvoice_fee_amount = $this->currency->format($this->config->get('paysoninvoice_fee_fee'), $order_data['currency_code'], $order_data['currency_value'], false);
			if (empty($tax_rates))
				return $paysoninvoice_fee_amount;
			foreach ($tax_rates as $tax_rate) {
					return $paysoninvoice_fee_amount + $this->currency->format($tax_rate['amount'], $order_data['currency_code'], $order_data['currency_value'], false);			
			}
		}
	}
	
	private function getHandlingFeePaysoninvoice($order_data){
		$this->load->model('total/handling');
		$tax_rates = $this->tax->getRates($this->config->get('handling_fee'), $this->config->get('handling_tax_class_id'));

		if (empty($tax_rates))
			return array('handling_fee' => $this->currency->format($this->config->get('handling_fee'), $order_data['currency_code'], $order_data['currency_value'], false), 'handling_rate' => 0.0);
		foreach ($tax_rates as $tax_rate) {	
				return array('handling_fee' => $this->currency->format($this->config->get('handling_fee'), $order_data['currency_code'], $order_data['currency_value'], false), 'handling_rate' => $tax_rate['rate']/100);
		}	
	}
	
	private function getLowOrderFeePaysoninvoice($order_data){
		$this->load->model('total/low_order_fee');
		$tax_rates = $this->tax->getRates($this->config->get('low_order_fee_fee'), $this->config->get('low_order_fee_tax_class_id'));
		if (empty($tax_rates))
			return array('low_order_fee' => $this->currency->format($this->config->get('low_order_fee_fee'), $order_data['currency_code'], $order_data['currency_value'], false), 'low_order_fee_rate' => 0.0);
		foreach ($tax_rates as $tax_rate) {	
				return array('low_order_fee' => $this->currency->format($this->config->get('low_order_fee_fee'), $order_data['currency_code'], $order_data['currency_value'], false), 'low_order_fee_rate' => $tax_rate['rate']/100);
		}
	}
	
	private function getVoucherPaysoninvoice($order_data){
		$voucher_data = $this->session->data['vouchers'];
		foreach ($voucher_data as $voucher) {
			return array('voucher_description' => $voucher['description'], 'voucher_amount' => $this->currency->format($voucher['amount'], $order_data['currency_code'], $order_data['currency_value'], false), 'voucher_id' => $voucher['voucher_theme_id']); 			
		}
	}
	
	private function getCouponPaysoninvoice($order_data){
		$this->load->model('checkout/coupon');
        $this->load->model('total/coupon');
        
        $coupon_info = array();
        //$coupon_to_payson;
        $coupon_total = 0;
        $coupon_taxes = $this->cart->getTaxes();
        $tax_sum_products = 0;
        foreach($coupon_taxes AS $tax) {
            $tax_sum_products += $tax;
        }
        $this->model_total_coupon->getTotal($coupon_info, $coupon_total, $coupon_taxes);
      
        if($coupon_total < 0) {
            $tax_sum_after_coupon = 0;
            foreach($coupon_taxes AS $tax) {
                $tax_sum_after_coupon += $tax;
            }
         
            return array(
            	'discount' => $this->currency->format($coupon_total, $order_data['currency_code'], $order_data['currency_value'], false), 
            	'taxRate' => ($tax_sum_after_coupon - $tax_sum_products) / $coupon_total
            );
        }         
	}
	
	private function getOrderItems(){
		require_once 'payson/orderitem.php';
		
		$products_data = $this->cart->getProducts();
    	$order_data = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		foreach ($products_data as $product) { 
			$option_data = array();
			
			foreach ($product['option'] as $option) {
				//start
				if ($option['type'] != 'file') {
					$value = $option['option_value'];	
				} else {
					$filename = $this->encryption->decrypt($option['option_value']);
							
					$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
				}
					
				$option_data[] = $option['name'] . ': ' . (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value);
			}
	
			if ($option_data) {
					$name = $product['name'] . ' ' . implode('; ', $option_data);
			} else {
					$name = $product['name'];
			}
			$product_price = $this->currency->format($product['price'], $order_data['currency_code'], $order_data['currency_value'], false);
			
		    $tax_rates_product = $this->tax->getRates($product['price'], $product['tax_class_id']);
			
		    if (empty($tax_rates_product))	
				$this->data['order_items'][] = new OrderItem(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'), $product_price, $product['quantity'], 0.0, $product['product_id']);
		   
			foreach ($tax_rates_product as $tax_rate) {		
				$this->data['order_items'][] = new OrderItem(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'), $product_price, $product['quantity'], $tax_rate['rate']/100, $product['product_id']);	
			}
		}
		
		if(isset($this->session->data['shipping_method'])){
			 $tax_rates_shipping = $this->tax->getRates($this->data['shipping_cost'], $this->session->data['shipping_method']['tax_class_id']);

			foreach ($tax_rates_shipping as $tax_rate) {		
				$this->data['order_items'][] = new OrderItem(html_entity_decode($this->data['shipping_name'], ENT_QUOTES, 'UTF-8'), $this->data['shipping_cost'], 1, $tax_rate['rate']/100, 'shipping');
			}
		}
		
		if ($this->config->get('coupon_status')){
			$discount = $this->getCouponPaysoninvoice($order_data);
			if($discount['discount'] < 0)
				$this->data['order_items'][] = new OrderItem('Discount', $discount['discount'], 1, $discount['taxRate'], 'discount');
		}
			
		if (($this->cart->getSubTotal() < $this->config->get('handling_total')) && ($this->cart->getSubTotal() > 0) && $this->config->get('handling_status')) {
				$handling_fee = $this->getHandlingFeePaysoninvoice($order_data);
				$this->data['order_items'][] = new OrderItem('Handling fee', $handling_fee['handling_fee'], 1, $handling_fee['handling_rate'], 'handling');
		}
		
		if ($this->cart->getSubTotal() && ($this->cart->getSubTotal() < $this->config->get('low_order_fee_total')) && $this->config->get('low_order_fee_status')) {
			$low_order_fee = $this->getLowOrderFeePaysoninvoice($order_data);
			$this->data['order_items'][] = new OrderItem('Low order fee', $low_order_fee['low_order_fee'], 1, $low_order_fee['low_order_fee_rate'], 'Low order fee');
		}
		
		 if (isset($this->session->data['vouchers']) && $this->getVoucherPaysoninvoice($order_data)){
			$voucher = $this->getVoucherPaysoninvoice($order_data);
			$this->data['order_items'][] = new OrderItem($voucher['voucher_description'], $voucher['voucher_amount'], 1, 0.0, $voucher['voucher_id']);
		 }
			//$value != '' ? $value : 'Product';

	}
	
	function paysonIpn(){	
		$this->load->model('checkout/order');
		$postData = file_get_contents("php://input");
		
		// Set up API
		if ($this->config->get('paysoninvoice_mode') == 0){
			require 'payson/paysonapiTest.php';
			$credentials = new PaysonCredentials(1, 'fddb19ac-7470-42b6-a91d-072cb1495f0a', null, 'PAYSON-MODULE-INFO: payson_opencart|1.5|'.VERSION);
		}
		else{
			require 'payson/paysonapi.php';
			$credentials = new PaysonCredentials(trim($this->config->get('paysoninvoice_agent_id')), trim($this->config->get('paysoninvoice_md5')), null, 'PAYSON-MODULE-INFO: payson_opencart|1.5|'.VERSION);
		}
		$api = new PaysonApi($credentials);
		
		// Validate the request
		$response = $api->validate($postData);

		//OBS!  token �r samma i ipn och return
			if($response->isVerified()){
				// IPN request is verified with Payson
				// Check details to find out what happened with the payment	
				$salt= explode("-", $response->getPaymentDetails()->getTrackingId());
		
				if($salt[0] == (md5($this->config->get('paysoninvoice_secure_word')).'1')){
							
					$ipn_respons = array(
						'order_id' 						=> $salt[count($salt) - 1], 
						'valid' 						=> 1, 
						'sender_email'					=> $response->getPaymentDetails()->getSenderEmail(),
						'currency_code' 				=> $response->getPaymentDetails()->getCurrencyCode(),
						'tracking_id' 					=> $response->getPaymentDetails()->getTrackingId(), 
						'ipn_status' 					=> $response->getPaymentDetails()->getStatus(),
						'token' 						=> $response->getPaymentDetails()->getToken(),
						'type' 							=> $response->getPaymentDetails()->getType(),
						'purchase_id' 					=> $response->getPaymentDetails()->getPurchaseId(),
					   	'invoice_status' 				=> $response->getPaymentDetails()->getInvoiceStatus(),
						'customer' 		 				=> $response->getPaymentDetails()->getCustom(),
					    'shippingAddress_name' 			=> $response->getPaymentDetails()->getShippingAddressName(),
						'shippingAddress_street_ddress' => $response->getPaymentDetails()->getShippingAddressStreetAddress(),
						'shippingAddress_postal_code' 	=> $response->getPaymentDetails()->getShippingAddressPostalCode(),
						'shippingAddress_city' 			=> $response->getPaymentDetails()->getShippingAddressCity(),
						'shippingAddress_country' 		=> $response->getPaymentDetails()->getShippingAddressCountry()				
					);
					$this->load->model('payment/paysoninvoice');
					$this->model_payment_paysoninvoice->setPaysonOrderDb($ipn_respons);
				}
				else{
					if ($this->config->get('paysoninvoice_logg') == 1){ 
						$this->log->write('<Payson Ivoice ipn> The secure word from the Tracking is incorrect.');
					}
				} 				
		}
		else{
			if ($this->config->get('paysoninvoice_logg') == 1){
				$this->log->write('<Payson Ivoice api>The response could not validate.');
				
			}
		}
	}
	
	public function languagePaysoninvoice(){			
		    switch (strtoupper($this->data['language_code'])) {
            case "SV":
                return "SV";
            case "FI":
                return "FI";
            default:
                return "EN";
        }
	}
	
	public function currencyPaysoninvoice(){
			return strtoupper($this->data['currency_code']); 
	}

	public function myFile($arg, $arg2 = NULL) {
		/*$myFile = "testFile.txt";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $arg.'******');
		fwrite($fh, $arg2);
		fclose($fh);*/
	}
}
?>