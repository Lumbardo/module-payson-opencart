<?php 
class ControllerPaymentPaysoninvoice extends Controller {
	private $error = array(); 
	 
	public function index() { 
		//Load the language file for this module
		$this->load->language('payment/paysoninvoice');
		
		//Set the title from the language file $_['heading_title'] string
		$this->document->setTitle($this->language->get('heading_title'));
		
		//create the table payson_order in the database
		$this->load->model('module/paysoninvoice');
		$this->model_module_paysoninvoice->createModuleTables();
		
		//Load the settings model. You can also add any other models you want to load here.
		$this->load->model('setting/setting');
		//Save the settings if the user has submitted the admin form (ie if someone has pressed save).		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('paysoninvoice', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['entry_paysoninvoice_mode'] = $this->language->get('entry_paysoninvoice_mode');
		$this->data['entry_paysoninvoice_mode'] = $this->language->get('entry_paysoninvoice_mode');
		$this->data['entry_paysoninvoice_mode_live'] = $this->language->get('entry_paysoninvoice_mode_live');
		$this->data['entry_paysoninvoice_mode_sandbox'] = $this->language->get('entry_paysoninvoice_mode_sandbox');
		
		$this->data['entry_paysoninvoice_user_name'] = $this->language->get('entry_paysoninvoice_user_name');
		$this->data['entry_paysoninvoice_agent_id'] = $this->language->get('entry_paysoninvoice_agent_id');
		$this->data['entry_paysoninvoice_md5'] = $this->language->get('entry_paysoninvoice_md5');
		$this->data['entry_paysoninvoice_fee'] = $this->language->get('entry_paysoninvoice_fee');
		$this->data['entry_paysoninvoice_fee_tax'] = $this->language->get('entry_paysoninvoice_fee_tax');
		
		$this->data['entry_paysoninvoice_secure_word'] = $this->language->get('entry_paysoninvoice_secure_word');
		$this->data['entry_logg'] = $this->language->get('entry_logg');
		
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
				
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_total'] = $this->language->get('entry_total');	
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->error['user_name'])) {
			$this->data['error_user_name'] = $this->error['user_name'];
		} else {
			$this->data['error_user_name'] = '';
		}
		
		if (isset($this->error['agent_id'])) {
			$this->data['error_agent_id'] = $this->error['agent_id'];
		} else {
			$this->data['error_agent_id'] = '';
		}
		
		if (isset($this->error['md5'])) {
			$this->data['error_md5'] = $this->error['md5'];
		} else {
			$this->data['error_md5'] = '';
		}
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/paysoninvoice', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('payment/paysoninvoice', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');	
		
		
		if (isset($this->request->post['paysoninvoice_mode'])) {
			$this->data['paysoninvoice_mode'] = $this->request->post['paysoninvoice_mode'];
		} else {
			$this->data['paysoninvoice_mode'] = $this->config->get('paysoninvoice_mode');
		}
		
		if (isset($this->request->post['paysoninvoice_user_name'])) {
			$this->data['paysoninvoice_user_name'] = $this->request->post['paysoninvoice_user_name'];
		} else {
			$this->data['paysoninvoice_user_name'] = $this->config->get('paysoninvoice_user_name');
		}
		
		if (isset($this->request->post['paysoninvoice_agent_id'])) {
			$this->data['paysoninvoice_agent_id'] = $this->request->post['paysoninvoice_agent_id'];
		} else {
			$this->data['paysoninvoice_agent_id'] = $this->config->get('paysoninvoice_agent_id');
		}
		if (isset($this->request->post['paysoninvoice_md5'])) {
			$this->data['paysoninvoice_md5'] = $this->request->post['paysoninvoice_md5'];
		} else {
			$this->data['paysoninvoice_md5'] = $this->config->get('paysoninvoice_md5');
		}
		
		if (isset($this->request->post['paysoninvoice_invoice_fee'])) {
			$this->data['paysoninvoice_invoice_fee'] = $this->request->post['paysoninvoice_invoice_fee'];
		} else {
			$this->data['paysoninvoice_invoice_fee'] = $this->config->get('paysoninvoice_invoice_fee');
		}
		if (isset($this->request->post['paysoninvoice_invoice_fee_tax'])) {
			$this->data['paysoninvoice_invoice_fee_tax'] = $this->request->post['paysoninvoice_invoice_fee_tax'];
		} else {
			$this->data['paysoninvoice_invoice_fee_tax'] = $this->config->get('paysoninvoice_invoice_fee_tax');
		}
		
		if (isset($this->request->post['paysoninvoice_secure_word'])) {
			$this->data['paysoninvoice_secure_word'] = $this->request->post['paysoninvoice_secure_word'];
		} else {
			$this->data['paysoninvoice_secure_word'] = $this->config->get('paysoninvoice_secure_word');
		}
		
		if (isset($this->request->post['paysoninvoice_logg'])) {
			$this->data['paysoninvoice_logg'] = $this->request->post['paysoninvoice_logg'];
		} else {
			$this->data['paysoninvoice_logg'] = $this->config->get('paysoninvoice_logg');
		}
		
		if (isset($this->request->post['paysoninvoice_total'])) {
			$this->data['paysoninvoice_total'] = $this->request->post['paysoninvoice_total'];
		} else {
			$this->data['paysoninvoice_total'] = $this->config->get('paysoninvoice_total'); 
		}
				
		if (isset($this->request->post['paysoninvoice_order_status_id'])) {
			$this->data['paysoninvoice_order_status_id'] = $this->request->post['paysoninvoice_order_status_id'];
		} else {
			$this->data['paysoninvoice_order_status_id'] = $this->config->get('paysoninvoice_order_status_id'); 
		} 
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['paysoninvoice_geo_zone_id'])) {
			$this->data['paysoninvoice_geo_zone_id'] = $this->request->post['paysoninvoice_geo_zone_id'];
		} else {
			$this->data['paysoninvoice_geo_zone_id'] = $this->config->get('paysoninvoice_geo_zone_id'); 
		} 
		
		$this->load->model('localisation/geo_zone');						
		
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['paysoninvoice_status'])) {
			$this->data['paysoninvoice_status'] = $this->request->post['paysoninvoice_status'];
		} else {
			$this->data['paysoninvoice_status'] = $this->config->get('paysoninvoice_status');
		}
		
		if (isset($this->request->post['paysoninvoice_sort_order'])) {
			$this->data['paysoninvoice_sort_order'] = $this->request->post['paysoninvoice_sort_order'];
		} else {
			$this->data['paysoninvoice_sort_order'] = $this->config->get('paysoninvoice_sort_order');
		}

		$this->template = 'payment/paysoninvoice.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/paysoninvoice')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->request->post['paysoninvoice_mode'] != 0){
			if (!$this->request->post['paysoninvoice_agent_id']) {
				$this->error['agent_id'] = $this->language->get('error_agent_id');
			}
			
			if (!$this->request->post['paysoninvoice_user_name']) {
				$this->error['user_name'] = $this->language->get('error_user_name');
			}
			if (!$this->request->post['paysoninvoice_md5']) {
				$this->error['md5'] = $this->language->get('error_md5');
			}
		}	
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>