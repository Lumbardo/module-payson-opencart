<?php

class ControllerPaymentPaysoninvoice extends Controller {

    private $error = array();

    /**
     * 
     * @return type
     */
    public function index() {
        	$action = new Action('payment/paysondirect');
	
		if (file_exists($action->getFile())) {
			require_once($action->getFile());

			$class = $action->getClass();

			$controller = new $class($this->registry);
                        
                        $controller->setInvoice();
                        
			$controller->{$action->getMethod()}($action->getArgs());     
                        
                        $this->output = $controller->output;
                }
    }
}

?>