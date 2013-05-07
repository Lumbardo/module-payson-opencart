<?php
class ControllerPaymentPaysondirect extends Controller {
	protected function index() {
		$this->load->model('checkout/order');
    	$order_data = $this->model_checkout_order->getOrder($this->session->data['order_id']);
    	
    	$this->data['button_confirm']	 = $this->language->get('button_confirm');
    	$this->data['store_name']		 = html_entity_decode($order_data['store_name'], ENT_QUOTES, 'UTF-8');
    	//Payson send the responds to the shop
    	$this->data['ok_url'] 			 = $this->url->link('payment/paysondirect/confirm');
    	$this->data['cancel_url'] 		 = $this->url->link('checkout/checkout');
    	$this->data['ipn_url'] 			 = $this->url->link('payment/paysondirect/paysonIpn');
    	
    	$this->data['order_id']			 = $order_data['order_id'];
    	$this->data['amount']	         = $this->currency->format($order_data['total'], $order_data['currency_code'], $order_data['currency_value'], false);	 
    	$this->data['currency_code']     = $order_data['currency_code'];
		$this->data['language_code']     = $order_data['language_code'];
		$this->data['salt'] 			 = md5($this->config->get('payson_secure_word')).'1-'. $this->data['order_id'];
		//Customer info
   		$this->data['sender_email']		 = $order_data['email'];
    	$this->data['sender_first_name'] = html_entity_decode($order_data['firstname'], ENT_QUOTES, 'UTF-8');
		$this->data['sender_last_name']  = html_entity_decode($order_data['lastname'], ENT_QUOTES, 'UTF-8');
		
    	//Call PaysonAPI    	
    	
		if ($this->config->get('payment_mode') == 0)
    		$this->data['action'] 		 = $this->paysonApiSandbox();
		else 
			$this->data['action'] 		 = $this->paysonApi();

		//Choose which template to display this module with
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysondirect.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/paysondirect.tpl';
		} else {
			$this->template = 'default/template/payment/paysondirect.tpl';
		}			
		$this->render();
	}
	
	public function confirm() {
		$status = false;
		$this->load->model('checkout/order');
		$this->load->language('payment/paysondirect');
		
		if(isset($this->request->get['TOKEN'])){	
			$this->load->model('payment/paysondirect');
			$status =  $this->model_payment_paysondirect->getIpnStatus($this->request->get['TOKEN']);
			//$this->log->write($status);
		}
		//print_r($status);exit;
		if($status === 1){
			//create the order and redirect to success site
			//$this->myFile($this->config->get('paysondirect_order_status_id'));
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('paysondirect_order_status_id'));
			$this->redirect($this->url->link('checkout/success'));
		}elseif($status === 2){
			//create the order with status denied
			$this->model_checkout_order->confirm($this->session->data['order_id'], 8);
			$this->paysonApiError($this->language->get('text_denied'));
		}
		else 
			$this->redirect($this->url->link('checkout/checkout'));	

	}
	
	function paysonApi(){
		require_once 'payson/paysonapi.php';
		$credentials = new PaysonCredentials(trim($this->config->get('payson_agent_id')), trim($this->config->get('payson_md5')), null, 'PAYSON-MODULE-INFO: payson_opencart|1.5|'.VERSION);
		$api = new PaysonApi($credentials);
		
		$receiver = new Receiver(trim($this->config->get('payson_user_name')), $this->data['amount']);
		$receivers = array($receiver);
		
		$sender = new Sender($this->data['sender_email'], $this->data['sender_first_name'], $this->data['sender_last_name']);
		$payData = new PayData($this->data['ok_url'], $this->data['cancel_url'], $this->data['ipn_url'], $this->data['store_name'].' Order: '.$this->data['order_id'], $sender, $receivers); 
		$payData->setCurrencyCode($this->currencyPaysondirect());
		$payData->setLocaleCode($this->languagePaysondirect());
		$constraints = array($this->config->get('payson_payment_method'));
		$payData->setFundingConstraints($constraints);
		$payData->setGuaranteeOffered('NO');
		$payData->setTrackingId($this->data['salt']);
		//$payData->setTrackingId($this->data['order_id']);
		
		$payResponse = $api->pay($payData);
			
		if ($payResponse->getResponseEnvelope()->wasSuccessful())  //ack = SUCCESS och token  = token = N�got
		{   
			//return the url: https://www.payson.se/paysecure/?token=#
			return $api->getForwardPayUrl($payResponse);
		}
		else{
			$error = $payResponse->getResponseEnvelope()->getErrors();
			if ($this->config->get('payson_logg') == 1){    
				$this->log->write($error[0]->getErrorId().'<br />'. $error[0]->getMessage() .'<br />'. $error[0]->getParameter());
			}
			if ($error[0]->getErrorId()) {
				$this->paysonApiError($error[0]->getMessage());	
			}
		}	
	}

	function product_list(){
		
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
			$credentials = new PaysonCredentials(trim($this->config->get('payson_agent_id')), trim($this->config->get('payson_md5')), null, 'PAYSON-MODULE-INFO: payson_opencart|1.5|'.VERSION);	
		}
		
		$api = new PaysonApi($credentials);
		//$this->myFile($postData);
		// Validate the request
		$response = $api->validate($postData);
		//OBS!  token �r samma i ipn och return
		if($response->isVerified()){
				// IPN request is verified with Payson
				// Check details to find out what happened with the payment
				$salt = explode("-", $response->getPaymentDetails()->getTrackingId());
				
				if($salt[0] == (md5($this->config->get('payson_secure_word')).'1')){
					$ipn_respons = array(
						'order_id' 						=> $salt[count($salt) - 1], 
						'valid' 						=> 1, 
						'ipn_status' 					=> $response->getPaymentDetails()->getStatus(),
						'sender_email'					=> $response->getPaymentDetails()->getSenderEmail(),
						'currency_code' 				=> $response->getPaymentDetails()->getCurrencyCode(),
						'tracking_id' 					=> $response->getPaymentDetails()->getTrackingId(), 
						'token' 						=> $response->getPaymentDetails()->getToken(),
						'type' 							=> $response->getPaymentDetails()->getType(),
						'purchase_id' 					=> $response->getPaymentDetails()->getPurchaseId(),
						'customer' 		 				=> $response->getPaymentDetails()->getCustom()			    				
					);
					//$this->myFile($ipn_respons);
					$this->load->model('payment/paysondirect');
					$this->model_payment_paysondirect->setPaysonOrderDb($ipn_respons);
				}
				else{
					if ($this->config->get('payson_logg') == 1){ 
							$this->log->write('<Payson Direct ipn> The secure word from the Tracking is incorrect.');
					}
				}
		}
		else{
			if ($this->config->get('payson_logg') == 1){
				$this->log->write('<Payson Direct ipn>The response could not validate.');
			}
		}
	}
	
	function paysonApiSandbox(){
		require_once 'payson/paysonapiTest.php';
		$credentials = new PaysonCredentials(1, 'fddb19ac-7470-42b6-a91d-072cb1495f0a', null, 'PAYSON-MODULE-INFO: payson_opencart|1.5|'.VERSION);
		$api = new PaysonApi($credentials);
		
		$receiver = new Receiver('testagent-1@payson.se', $this->data['amount']);
		$receivers = array($receiver);
		
		$sender = new Sender('test-shopper@payson.se', $this->data['sender_first_name'], $this->data['sender_last_name']);
		$payData = new PayData($this->data['ok_url'], $this->data['cancel_url'], $this->data['ipn_url'], $this->data['store_name'].' Order: '.$this->data['order_id'], $sender, $receivers); 
		$payData->setCurrencyCode($this->currencyPaysondirect());
		$payData->setLocaleCode($this->languagePaysondirect());
		$constraints = array($this->config->get('payson_payment_method'));
		$payData->setFundingConstraints($constraints);
		$payData->setGuaranteeOffered('NO');
		$payData->setTrackingId($this->data['salt']);
		
		$payResponse = $api->pay($payData);
			
		if ($payResponse->getResponseEnvelope()->wasSuccessful())  //ack = SUCCESS och token  = token = N�got
		{   
			//return the url: https://www.payson.se/paysecure/?token=#
			return $api->getForwardPayUrl($payResponse);
		}
		else{
			$error = $payResponse->getResponseEnvelope()->getErrors();
			if ($this->config->get('payson_logg') == 1){ 
				$this->log->write($error[0]->getErrorId().'<br />'. $error[0]->getMessage() .'<br />'. $error[0]->getParameter());
			}
			if ($error[0]->getErrorId()) {
				$this->paysonApiError($error[0]->getMessage());	
			}
		}	
	}
	
	public function languagePaysondirect(){
	        switch (strtoupper($this->data['language_code'])) {
            case "SV":
                return "SV";
            case "FI":
                return "FI";
            default:
                return "EN";
        }
	}
	
	public function currencyPaysondirect(){
	 	switch (strtoupper($this->data['currency_code'])) {
            case "SEK":
                return "SEK";
            default:
                return "EUR";
        }
	}

	public function paysonApiError($error) {
		$this->load->language('payment/paysondirect');
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
	
	public function myFile($arg, $arg2 = NULL) {
		/*$myFile = "testFile.txt";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $arg.'******');
		fwrite($fh, $arg2);
		fclose($fh);*/
	}	
	
}
?>