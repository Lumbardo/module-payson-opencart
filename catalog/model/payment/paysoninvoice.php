<?php

class ModelPaymentPaysoninvoice extends Model {
    private $minimumAmountInvoice = 30;
    
    public function getMethod($address, $total) {
        $this->load->language('payment/paysoninvoice');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('paysoninvoice_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");
        
        if (!$this->config->get('paysoninvoice_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }
        if (strtoupper($this->session->data['currency']) != 'SEK'){
            $status = false;
        }
             
        if(strtoupper($this->config->get('config_currency')) == 'SEK' && $total < $this->minimumAmountInvoice){
            $status = false;
        }
        if(strtoupper($this->config->get('config_currency')) != 'SEK' && $this->currency->convert($total, strtoupper($this->config->get('config_currency')), 'SEK') < $this->minimumAmountInvoice){
            $status = false;
        }
                
        $method_data = array();

        $this->load->model('total/paysoninvoice_fee');
        
        $totalFee = 0;
        $taxAmountFee = 0;
        if($this->config->get('paysoninvoice_fee_status') && $this->config->get('paysoninvoice_fee_fee') > 0){
            if ($this->config->get('paysoninvoice_fee_tax_class_id')) {
                $tax_rates = $this->tax->getRates($this->config->get('paysoninvoice_fee_fee'), $this->config->get('paysoninvoice_fee_tax_class_id'));

                foreach ($tax_rates as $tax_rate) {
                    $taxAmountFee += $tax_rate['amount'];
                }
            }
            $total = $this->config->get('paysoninvoice_fee_fee') + $taxAmountFee;
        }

        if ($status) {
            $method_data = array(
                'code' => 'paysoninvoice',
                'title' => sprintf($this->language->get('text_title'),  $this->config->get('paysoninvoice_fee_status') ? $total : 0),
                'sort_order' => $this->config->get('paysoninvoice_sort_order')
            );
        }
        return $method_data;
    }
    
    public function setPaysonOrderDb($ipn_respons) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "payson_order SET 
	  						order_id                      = '" . $ipn_respons['order_id'] . "', 
	  						valid                         = '" . $ipn_respons['valid'] . "', 
	  						added 						  = NOW(), 
	  						updated                       = NOW(), 
	  						ipn_status                    = '" . $ipn_respons['ipn_status'] . "', 	
	  						sender_email                  = '" . $ipn_respons['sender_email'] . "', 
	  						currency_code                 = '" . $ipn_respons['currency_code'] . "',
	  						tracking_id                   = '" . $ipn_respons['tracking_id'] . "',
	  						type                          = '" . $ipn_respons['type'] . "',
	  						purchase_id                   = '" . $ipn_respons['purchase_id'] . "',
	  						invoice_status                = '" . $ipn_respons['invoice_status'] . "',
	  						customer                      = '" . $ipn_respons['customer'] . "', 
	  						shippingAddress_name          = '" . $ipn_respons['shippingAddress_name'] . "', 
	  						shippingAddress_street_ddress = '" . $ipn_respons['shippingAddress_street_ddress'] . "', 
	  						shippingAddress_postal_code   = '" . $ipn_respons['shippingAddress_postal_code'] . "', 
	  						shippingAddress_city 		  = '" . $ipn_respons['shippingAddress_city'] . "', 
	  						shippingAddress_country       = '" . $ipn_respons['shippingAddress_country'] . "', 
	  						token                         =  '" . $ipn_respons['token'] . "'"
        );
    }

    public function getIpnStatus($token) {
        $this->load->model('checkout/order');
        if (isset($token)) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "payson_order WHERE token = '" . $token . "'");

            if ($query->num_rows) {

                foreach ($query->rows as $payson_order) {
                    $this->db->query("UPDATE `" . DB_PREFIX . "order` SET 
										shipping_firstname  = '" . $payson_order['shippingAddress_name'] . "',
										shipping_lastname 	= '',
										shipping_address_1 	= '" . $payson_order['shippingAddress_street_ddress'] . "',
										shipping_city 		= '" . $payson_order['shippingAddress_city'] . "', 
										shipping_country 	= '" . $payson_order['shippingAddress_country'] . "', 
										shipping_postcode 	= '" . $payson_order['shippingAddress_postal_code'] . "'
										WHERE order_id 		= '" . (int) $payson_order['order_id'] . "'");
                }
                if ($query->row['ipn_status'] === 'PENDING')
                    return 1;
                if ($query->row['ipn_status'] === 'ERROR')
                    return 2;
            }
        }
        return 0;
    }
    
}

?>