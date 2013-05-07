<?php

class ModelPaymentPaysondirect extends Model {

    private $currency_supported_by_p_direct = array('SEK', 'EUR');

    public function getMethod($address, $total) {
        $this->language->load('payment/paysondirect');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('paysondirect_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('paysondirect_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('paysondirect_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }
        if (!in_array(strtoupper($_SESSION ['currency']), $this->currency_supported_by_p_direct)) {
            $status = false;
        }


        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'paysondirect',
                'title' => $this->language->get('text_title'),
                'sort_order' => $this->config->get('paysondirect_sort_order')
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
	  						customer                      = '" . $ipn_respons['customer'] . "', 		
	  						token                         =  '" . $ipn_respons['token'] . "'"
        );
    }

    public function getIpnStatus($token) {
        $this->load->model('checkout/order');
        //$this->log->write($token);
        if (isset($token)) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "payson_order WHERE token = '" . $token . "'");

            if ($query->num_rows){
                if($query->row['ipn_status'] ==='COMPLETED')
                	//$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('paysondirect_order_status_id'));
					return 1;
				if($query->row['ipn_status'] ==='ERROR')
					return 2;
            }
            else
            	return 0;
        }
        else
            return 0;
    }

}

?>