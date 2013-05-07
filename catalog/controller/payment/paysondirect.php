<?php

class ControllerPaymentPaysondirect extends Controller {

    private $testMode;
    private $api;
    private $isInvoice;

    function __construct($registry) {
        parent::__construct($registry);
        $this->testMode = ($this->config->get('payment_mode') == 0);
        $this->api = $this->getAPIInstance();
        $this->isInvoice = isset($this->data['isInvoice']) || isset($this->request->get['method']);
    }

    public function setInvoice() {
        $this->data['isInvoice'] = true;
        $this->isInvoice = true;
    }

    public function index() {

        $this->data['button_confirm'] = $this->language->get('button_confirm');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysondirect.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/paysondirect.tpl';
        } else {
            $this->template = 'default/template/payment/paysondirect.tpl';
        }
        $this->render();
    }

    public function confirm() {

        $this->load->model('checkout/order');
        $order_data = $this->model_checkout_order->getOrder($this->session->data['order_id']);


        $this->data['store_name'] = html_entity_decode($order_data['store_name'], ENT_QUOTES, 'UTF-8');
        //Payson send the responds to the shop
        $this->data['ok_url'] = $this->url->link('payment/paysondirect/returnFromPayson');
        $this->data['cancel_url'] = $this->url->link('checkout/checkout');
        $this->data['ipn_url'] = $this->url->link('payment/paysondirect/paysonIpn');

        $this->data['order_id'] = $order_data['order_id'];
        $this->data['amount'] = $this->currency->format($order_data['total'], $order_data['currency_code'], $order_data['currency_value'], false);
        $this->data['currency_code'] = $order_data['currency_code'];
        $this->data['language_code'] = $order_data['language_code'];
        $this->data['salt'] = md5($this->config->get('payson_secure_word')) . '1-' . $this->data['order_id'];
        //Customer info
        $this->data['sender_email'] = $order_data['email'];
        $this->data['sender_first_name'] = html_entity_decode($order_data['firstname'], ENT_QUOTES, 'UTF-8');
        $this->data['sender_last_name'] = html_entity_decode($order_data['lastname'], ENT_QUOTES, 'UTF-8');

        //Call PaysonAPI    	

        $this->data['action'] = $this->getPaymentURL();

        echo $this->data['action'];
    }

    public function returnFromPayson() {

        $this->load->language('payment/paysondirect');
        $paymentDetails = null;

        if (isset($this->request->get['TOKEN'])) {

            $paymentDetails = $this->api->paymentDetails(new PaymentDetailsData($this->request->get['TOKEN']))->getPaymentDetails();

            if ($this->handlePaymentDetails($paymentDetails))
                $this->redirect($this->url->link('checkout/success'));
            else
                $this->redirect($this->url->link('checkout/checkout'));
        }
    }

    /**
     * 
     * @param PaymentDetails $paymentDetails
     */
    private function handlePaymentDetails($paymentDetails, $orderId = 0) {
        $this->load->model('checkout/order');

        $paymentType = $paymentDetails->getType();
        $transferStatus = $paymentDetails->getStatus();
        $invoiceStatus = $paymentDetails->getInvoiceStatus();

        if ($orderId == 0)
            $orderId = $this->session->data['order_id'];

        if ($paymentType == "INVOICE") {
            if ($invoiceStatus == "ORDERCREATED") {
                $this->model_checkout_order->confirm($orderId, $this->config->get('paysoninvoice_order_status_id'));
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET 
										shipping_firstname  = '" . $paymentDetails->getShippingAddressName() . "',
										shipping_lastname 	= '',
										shipping_address_1 	= '" . $paymentDetails->getShippingAddressStreetAddress() . "',
										shipping_city 		= '" . $paymentDetails->getShippingAddressCity() . "', 
										shipping_country 	= '" . $paymentDetails->getShippingAddressCountry() . "', 
										shipping_postcode 	= '" . $paymentDetails->getShippingAddressPostalCode() . "'
										WHERE order_id 		= '" . $orderId . "'");

                return true;
            }
        } elseif ($paymentType == "TRANSFER") {
            if ($transferStatus == "COMPLETED") {
                $this->model_checkout_order->confirm($orderId, $this->config->get('paysondirect_order_status_id'));
                return true;
            }
        }

        if (($paymentType == "INVOICE" || $paymentType == "TRANSFER") && $transferStatus == "ERROR") {
            $this->model_checkout_order->confirm($orderId, 8);
            $this->paysonApiError($this->language->get('text_denied'));
            return false;
        }

        $this->redirect($this->url->link('checkout/checkout'));
    }

    private function getPaymentURL() {
        require_once 'payson/paysonapi.php';

        if (!$this->testMode) {
            $sender = new Sender($this->data['sender_email'], $this->data['sender_first_name'], $this->data['sender_last_name']);
            $receiver = new Receiver(trim($this->config->get('payson_user_name')), $this->data['amount']);
        } else {
            $sender = new Sender('test-shopper@payson.se', $this->data['sender_first_name'], $this->data['sender_last_name']);
            $receiver = new Receiver('testagent-1@payson.se', $this->data['amount']);
        }


        $receivers = array($receiver);

        $payData = new PayData($this->data['ok_url'], $this->data['cancel_url'], $this->data['ipn_url'], $this->data['store_name'] . ' Order: ' . $this->data['order_id'], $sender, $receivers);
        $payData->setCurrencyCode($this->currencyPaysondirect());
        $payData->setLocaleCode($this->languagePaysondirect());

        $constraints = "";

        if ($this->isInvoice) {
            if ($this->hasInvoiceEnabled())
                $constraints = array(FundingConstraint::INVOICE);
            else {
                $this->paysonApiError($this->language->get('error_invoice_not_enabled'));
                return;
            }
        }
        else
            $constraints = array($this->config->get('payson_payment_method'));

        $orderItems = $this->getOrderItems();

        $payData->setOrderItems($orderItems);

        $payData->setFundingConstraints($constraints);
        $payData->setGuaranteeOffered('NO');
        $payData->setTrackingId($this->data['salt']);
        //$payData->setTrackingId($this->data['order_id']);

        $payResponse = $this->api->pay($payData);

        if ($payResponse->getResponseEnvelope()->wasSuccessful()) {  //ack = SUCCESS och token  = token = N�got
            //return the url: https://www.payson.se/paysecure/?token=#
            return $this->api->getForwardPayUrl($payResponse);
        } else {
            $error = $payResponse->getResponseEnvelope()->getErrors();
            if ($this->config->get('payson_logg') == 1) {
                $this->log->write($error[0]->getErrorId() . '<br />' . $error[0]->getMessage() . '<br />' . $error[0]->getParameter());
            }
            if ($error[0]->getErrorId()) {
                $this->response->addHeader("HTTP/1.0 500 Internal Server Error");
                $this->response->setOutput($error[0]->getMessage());
            }
        }
    }

    private function getAPIInstance() {
        require_once 'payson/paysonapi.php';

        if (!$this->testMode) {
            $credentials = new PaysonCredentials(trim($this->config->get('payson_agent_id')), trim($this->config->get('payson_md5')), null, 'PAYSON-MODULE-INFO: payson_opencart|2.0|' . VERSION);
        } else {
            $credentials = new PaysonCredentials(1, 'fddb19ac-7470-42b6-a91d-072cb1495f0a', null, 'PAYSON-MODULE-INFO: payson_opencart|2.0|' . VERSION);
        }

        $api = new PaysonApi($credentials, $this->testMode);

        return $api;
    }

    private function hasInvoiceEnabled() {
        return $this->config->get("paysoninvoice_status") == 1;
    }

    private function getOrderItems() {
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

            $tax_amount = 0;
            foreach ($tax_rates_product as $tax_rate) {
                if ($tax_rate['type'] == "F")
                    $this->data['order_items'][] = new OrderItem(html_entity_decode($tax_rate['name'], ENT_QUOTES, 'UTF-8'), $tax_rate['amount'], 1, 0, 'Fixed tax rate');
                else {
                    $tax_amount += $tax_rate['amount'];
                }
            }

            $this->data['order_items'][] = new OrderItem(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'), $product_price, $product['quantity'], ($tax_amount + $product_price) / ($product_price) - 1, isset($product['sku']) ? $product['sku'] : $product['model']);
        }

        $orderTotals = $this->getOrderTotals();

        foreach ($orderTotals as $orderTotal) {
            $this->data['order_items'][] = new OrderItem(html_entity_decode($orderTotal['title'], ENT_QUOTES, 'UTF-8'), $orderTotal['value'], 1, $orderTotal['tax_rate'] / 100, $orderTotal['code']);
        }

        return $this->data['order_items'];
    }

    private function getOrderTotals() {
        $total_data = array();
        $total = 0;
        $payson_tax = array();

        $cartTax = $this->cart->getTaxes();


        $this->load->model('setting/extension');

        $sort_order = array();

        $results = $this->model_setting_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);
        $ignoredOrderTotals = array_map('trim', explode(',', $this->config->get('paysondirect_ignored_order_totals')));
        foreach ($results as $result) {
            if (in_array($result['code'], $ignoredOrderTotals))
                continue;

            if ($this->config->get($result['code'] . '_status')) {
                $amount = 0;
                $taxes = array();
                foreach ($cartTax as $key => $value) {
                    $taxes[$key] = 0;
                }
                $this->load->model('total/' . $result['code']);

                @$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);

                foreach ($taxes as $tax_id => $value) {
                    $amount += $value;
                }

                $payson_tax[$result['code']] = $amount;
            }
        }

        $sort_order = array();

        foreach ($total_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $total_data);

        foreach ($total_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];

            if (isset($payson_tax[$value['code']])) {
                if ($payson_tax[$value['code']]) {
                    $total_data[$key]['tax_rate'] = abs($payson_tax[$value['code']] / $value['value'] * 100);
                } else {
                    $total_data[$key]['tax_rate'] = 0;
                }
            } else {
                $total_data[$key]['tax_rate'] = '0';
            }
        }

        return $total_data;
    }

    function paysonIpn() {
        $this->load->model('checkout/order');
        $postData = file_get_contents("php://input");

        $orderId = 0;

        // Set up API
        //$this->myFile($postData);
        // Validate the request
        $response = $this->api->validate($postData);
        //OBS!  token �r samma i ipn och return
        if ($response->isVerified()) {
            // IPN request is verified with Payson
            // Check details to find out what happened with the payment
            $salt = explode("-", $response->getPaymentDetails()->getTrackingId());

            if ($salt[0] == (md5($this->config->get('payson_secure_word')) . '1')) {
                $orderId = $salt[count($salt) - 1];


                $this->storeIPNResponse($response->getPaymentDetails(), $orderId);


                $this->handlePaymentDetails($response->getPaymentDetails(), $orderId);
            } else {
                if ($this->config->get('payson_logg') == 1) {
                    $this->log->write('<Payson Direct ipn> The secure word from the Tracking is incorrect.');
                }
            }
        } else {
            if ($this->config->get('payson_logg') == 1) {
                $this->log->write('<Payson Direct ipn>The response could not validate.');
            }
        }
    }

    /**
     * 
     * @param PaymentDetails $paymentDetails
     * @param int $orderId
     */
    private function storeIPNResponse($paymentDetails, $orderId) {

        $this->db->query("INSERT INTO " . DB_PREFIX . "payson_order SET 
	  						order_id                      = '" . $orderId . "', 
	  						valid                         = '" . 1 . "', 
	  						added 						  = NOW(), 
	  						updated                       = NOW(), 
	  						ipn_status                    = '" . $paymentDetails->getStatus() . "', 	
	  						sender_email                  = '" . $paymentDetails->getSenderEmail() . "', 
	  						currency_code                 = '" . $paymentDetails->getCurrencyCode() . "',
	  						tracking_id                   = '" . $paymentDetails->getTrackingId() . "',
	  						type                          = '" . $paymentDetails->getType() . "',
	  						purchase_id                   = '" . $paymentDetails->getPurchaseId() . "',
	  						invoice_status                = '" . $paymentDetails->getInvoiceStatus() . "',
	  						customer                      = '" . $paymentDetails->getCustom() . "', 
	  						shippingAddress_name          = '" . $paymentDetails->getShippingAddressName() . "', 
	  						shippingAddress_street_ddress = '" . $paymentDetails->getShippingAddressStreetAddress() . "', 
	  						shippingAddress_postal_code   = '" . $paymentDetails->getShippingAddressPostalCode() . "', 
	  						shippingAddress_city 		  = '" . $paymentDetails->getShippingAddressPostalCode() . "', 
	  						shippingAddress_country       = '" . $paymentDetails->getShippingAddressCity() . "', 
	  						token                         =  '" . $paymentDetails->getToken() . "'"
        );
    }

    public function languagePaysondirect() {
        switch (strtoupper($this->data['language_code'])) {
            case "SV":
                return "SV";
            case "FI":
                return "FI";
            default:
                return "EN";
        }
    }

    public function currencyPaysondirect() {
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
									alert("' . $error . $this->language->get('text_payson_payment_method') . '");
									window.location="' . (HTTPS_SERVER . 'index.php?route=checkout/checkout') . '";
								</script>
							</head>
					</html>';
        print_r($error_code);
        exit;
    }

}

?>