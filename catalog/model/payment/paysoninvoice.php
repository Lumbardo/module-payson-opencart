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
            $totalFee = $this->config->get('paysoninvoice_fee_fee') + $taxAmountFee;
        }

        if ($status) {
            $method_data = array(
                'code' => 'paysoninvoice',
                'title' => sprintf($this->language->get('text_title'),  round($this->currency->convert($totalFee, strtoupper($this->config->get('config_currency')), 'SEK'), 2)),
                'sort_order' => $this->config->get('paysoninvoice_sort_order')
            );
        }
        return $method_data;
    }   
}
?>