<?php

class ControllerPaymentPaysondirect extends Controller {

    private $testMode;
    private $api;
    private $isInvoice;

    const MODULE_VERSION = '2.8.2';

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
        $this->load->language('payment/paysondirect');
        $this->data['button_confirm'] = $this->language->get('button_confirm');

        if ($this->isInvoice) {
            $total = 0;
            $taxAmount = 0;

            if ($this->config->get('paysoninvoice_fee_tax_class_id')) {
                $tax_rates = $this->tax->getRates($this->config->get('paysoninvoice_fee_fee'), $this->config->get('paysoninvoice_fee_tax_class_id'));

                foreach ($tax_rates as $tax_rate) {
                    $taxAmount += $tax_rate['amount'];
                }
            }
            $total = $this->config->get('paysoninvoice_fee_fee') + $taxAmount;

            $this->data['text_invoice_terms'] = sprintf($this->language->get('text_invoice_terms'), $this->config->get('paysoninvoice_fee_status') ? $total : 0);
        }

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

            $secureWordFromShop = md5($this->config->get('payson_secure_word')) . '1';

            $paymentDetailsResponse = $this->api->paymentDetails(new PaymentDetailsData($this->request->get['TOKEN']));

            if ($paymentDetailsResponse->getResponseEnvelope()->wasSuccessful()) {
                $paymentDetails = $paymentDetailsResponse->getPaymentDetails();

                // Get the secure word as hash and order id
                $trackingFromDetails = explode('-', $paymentDetails->getTrackingId());

                if ($secureWordFromShop != $trackingFromDetails[0]) {
                    $this->writeToLog($this->language->get('Call doesnt seem to come from Payson. Please contact store owner if this should be a valid call.'), $paymentDetails);
                    $this->paysonApiError($this->language->get('Call doesnt seem to come from Payson. Please contact store owner if this should be a valid call.'));
                }
                if ($this->handlePaymentDetails($paymentDetails, $trackingFromDetails[1]))
                    $this->redirect($this->url->link('checkout/success'));
                else
                    $this->redirect($this->url->link('checkout/checkout'));
            } else {
                $this->logErrorsAndReturnThem($paymentDetailsResponse);
            }
        }
    }

    /**
     * 
     * @param PaymentDetails $paymentDetails
     */
    private function handlePaymentDetails($paymentDetails, $orderId = 0, $ipnCall = false) {
        $this->load->language('payment/paysondirect');
        $this->load->model('checkout/order');

        $paymentType = $paymentDetails->getType();
        $transferStatus = $paymentDetails->getStatus();
        $invoiceStatus = $paymentDetails->getInvoiceStatus();

        if ($orderId == 0)
            $orderId = $this->session->data['order_id'];

        $this->storeIPNResponse($paymentDetails, $orderId);

        $order_info = $this->model_checkout_order->getOrder($orderId);

        if ($paymentType == "INVOICE") {
            if ($invoiceStatus == "ORDERCREATED") {
                if (!$order_info['order_status_id']) {
                    $this->model_checkout_order->confirm($orderId, $this->config->get('paysoninvoice_order_status_id'));
                } else {
                    $this->model_checkout_order->update($orderId, $this->config->get('paysoninvoice_order_status_id'));
                }
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
                if (!$order_info['order_status_id']) {
                    $this->model_checkout_order->confirm($orderId, $this->config->get('paysondirect_order_status_id'));
                } else {
                    $this->model_checkout_order->update($orderId, $this->config->get('paysondirect_order_status_id'));
                }
                return true;
            }
        }

        if (($paymentType == "INVOICE" || $paymentType == "TRANSFER") && $transferStatus == "ERROR") {
            if ($ipnCall)
                $this->writeToLog('Order created with error status.&#10;Purchase type:&#9;&#9;' . $paymentType . '&#10;Order id:&#9;&#9;&#9;&#9;' . $orderId, $paymentDetails);
            $this->paysonApiError($this->language->get('text_denied'));
            return false;
        }

        $this->redirect($this->url->link('checkout/checkout'));
    }

    private function getPaymentURL() {
        require_once 'payson/paysonapi.php';

        $this->load->language('payment/paysondirect');

        if (!$this->testMode) {
            $receiver = new Receiver(trim($this->config->get('payson_user_name')), $this->data['amount']);
        } else {
            $receiver = new Receiver('testagent-1@payson.se', $this->data['amount']);
        }

        $sender = new Sender($this->data['sender_email'], $this->data['sender_first_name'], $this->data['sender_last_name']);

        $receivers = array($receiver);

        $payData = new PayData($this->data['ok_url'], $this->data['cancel_url'], $this->data['ipn_url'], $this->data['store_name'] . ' Order: ' . $this->data['order_id'], $sender, $receivers);
        $payData->setCurrencyCode($this->currencyPaysondirect());
        $payData->setLocaleCode($this->languagePaysondirect());

        $orderItems = $this->getOrderItems();

        $constraints = "";
        
        if ($this->isInvoice) {
            if ($this->hasInvoiceEnabled()) {
                $constraints = array(FundingConstraint::INVOICE);

                foreach ($orderItems as $key => $orderTotal) {
                    if ($orderTotal->getSku() == "paysoninvoice_fee") {
                        $payData->setInvoiceFee($orderTotal->getUnitPrice()*($orderTotal->getTaxPercentage()+1));
                        unset($orderItems[$key]);
                    }
                }
            } else {
                $this->paysonApiError($this->language->get('error_invoice_not_enabled'));
                return;
            }
        }
        else
            $constraints = array($this->config->get('payson_payment_method'));


        $payData->setOrderItems($orderItems);


        $this->writeArrayToLog($orderItems, sprintf('Order items sent to Payson (%sSEK)', $this->data['amount']));

        $payData->setFundingConstraints($constraints);
        $payData->setGuaranteeOffered('NO');
        $payData->setTrackingId($this->data['salt']);

        $payResponse = $this->api->pay($payData);

        if ($payResponse->getResponseEnvelope()->wasSuccessful()) {  //ack = SUCCESS och token  = token = N�got
            //return the url: https://www.payson.se/paysecure/?token=#
            return $this->api->getForwardPayUrl($payResponse);
        } else {
            $errors = $this->logErrorsAndReturnThem($payResponse);

            if ($errors[0]->getErrorId()) {
                $this->response->addHeader("HTTP/1.0 500 Internal Server Error");
                $this->response->setOutput($errors[0]->getMessage());
            }
        }
    }

    function logErrorsAndReturnThem($response) {
        $errors = $response->getResponseEnvelope()->getErrors();

        if ($this->config->get('payson_logg') == 1) {
            $this->writeToLog(print_r($errors, true));
        }

        return $errors;
    }

    /**
     * 
     * @param string $message
     * @param PaymentDetails $paymentDetails
     */
    function writeToLog($message, $paymentDetails = False) {
        $paymentDetailsFormat = "Payson reference:&#9;%s&#10;Correlation id:&#9;%s&#10;";
        if ($this->config->get('payson_logg') == 1) {

            $this->log->write('PAYSON&#10;' . $message . '&#10;' . ($paymentDetails != false ? sprintf($paymentDetailsFormat, $paymentDetails->getPurchaseId(), $paymentDetails->getCorrelationId()) : '') . $this->writeModuleInfoToLog());
        }
    }

    private function writeArrayToLog($array, $additionalInfo = "") {
        if ($this->config->get('payson_logg') == 1) {
            $this->log->write('PAYSON&#10;Additional information:&#9;' . $additionalInfo . '&#10;&#10;' . print_r($array, true) . '&#10;' . $this->writeModuleInfoToLog());
        }
    }

    private function writeModuleInfoToLog() {
        return 'Module version: ' . self::MODULE_VERSION . '&#10;------------------------------------------------------------------------&#10;';
    }

    private function getAPIInstance() {
        require_once 'payson/paysonapi.php';

        if (!$this->testMode) {
            $credentials = new PaysonCredentials(trim($this->config->get('payson_agent_id')), trim($this->config->get('payson_md5')), null, 'payson_opencart|' . self::MODULE_VERSION . '|' . VERSION);
        } else {
            $credentials = new PaysonCredentials(1, 'fddb19ac-7470-42b6-a91d-072cb1495f0a', null, 'payson_opencart|' . self::MODULE_VERSION . '|' . VERSION);
        }

        $api = new PaysonApi($credentials, $this->testMode);

        return $api;
    }

    private function hasInvoiceEnabled() {
        return $this->config->get("paysoninvoice_status") == 1;
    }

    private function getOrderItems() {
        require_once 'payson/orderitem.php';

        $this->load->language('payment/paysondirect');

        $orderId = $this->session->data['order_id'];

        $order_data = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $query = "SELECT `order_product_id`, `name`, `model`, `price`, `quantity`, `tax` / `price` AS 'tax_rate' FROM `" . DB_PREFIX . "order_product` WHERE `order_id` = " . (int) $orderId . " UNION ALL SELECT 0, '" . $this->language->get('text_gift_card') . "', `code`, `amount`, '1', 0.00 FROM `" . DB_PREFIX . "order_voucher` WHERE `order_id` = " . (int) $orderId;
        $product_query = $this->db->query($query)->rows;

        foreach ($product_query as $product) {

            $productOptions = $this->db->query("SELECT name, value FROM " . DB_PREFIX . 'order_option WHERE order_id = ' . (int) $orderId . ' AND order_product_id=' . (int) $product['order_product_id'])->rows;
            $optionsArray = array();
            if ($productOptions) {
                foreach ($productOptions as $option) {
                    $optionsArray[] = $option['name'] . ': ' . $option['value'];
                }
            }

            $productTitle = (strlen($product['name']) > 80 ? substr($product['name'], 0, strpos($product['name'], ' ', 80)) : $product['name']);

            if (!empty($optionsArray))
                $productTitle .= ' | ' . join('; ', $optionsArray);

            $product_price = $this->currency->format($product['price'], $order_data['currency_code'], $order_data['currency_value'], false);

            $this->data['order_items'][] = new OrderItem(html_entity_decode($productTitle, ENT_QUOTES, 'UTF-8'), $product_price, $product['quantity'], $product['tax_rate'], $product['model']);
        }

        $orderTotals = $this->getOrderTotals();

        foreach ($orderTotals as $orderTotal) {
            $orderTotalAmount = $this->currency->format($orderTotal['value'], $order_data['currency_code'], $order_data['currency_value'], false);
            $this->data['order_items'][] = new OrderItem(html_entity_decode($orderTotal['title'], ENT_QUOTES, 'UTF-8'), $orderTotalAmount, 1, $orderTotal['tax_rate'] / 100, $orderTotal['code']);
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

        foreach ($results as $result) {

            if ($this->config->get($result['code'] . '_status')) {
                $amount = 0;
                $taxes = array();
                foreach ($cartTax as $key => $value) {
                    $taxes[$key] = 0;
                }
                $this->load->model('total/' . $result['code']);

                $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);

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

        $ignoredOrderTotals = array_map('trim', explode(',', $this->config->get('paysondirect_ignored_order_totals')));
        foreach ($total_data as $key => $orderTotal) {
            if (in_array($orderTotal['code'], $ignoredOrderTotals)) {
                unset($total_data[$key]);
            }
        }

        return $total_data;
    }

    function paysonIpn() {
        $this->load->model('checkout/order');
        $postData = file_get_contents("php://input");

        $orderId = 0;

        // Set up API
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


                $this->handlePaymentDetails($response->getPaymentDetails(), $orderId, true);
            }
            else
                $this->writeToLog('The secure word could not be verified.', $response->getPaymentDetails());
        }
        else
            $this->writeToLog('The IPN response from Payson could not be validated.', $response->getPaymentDetails());
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
            case "SE":
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
        echo ($error_code);
        exit;
    }

}

?>