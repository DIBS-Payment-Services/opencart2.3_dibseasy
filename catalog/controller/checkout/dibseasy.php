<?php

class ControllerCheckoutDibseasy extends Controller { 
    
    public function index() {

       	$this->load->language('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
                $this->document->addStyle('catalog/view/theme/default/stylesheet/easy_checkout.css');
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'), 1);
		$data['text_checkout_account'] = sprintf($this->language->get('text_checkout_account'), 2);
		$data['text_checkout_payment_address'] = sprintf($this->language->get('text_checkout_payment_address'), 2);
		$data['text_checkout_shipping_address'] = sprintf($this->language->get('text_checkout_shipping_address'), 3);
		$data['text_checkout_shipping_method'] = sprintf($this->language->get('text_checkout_shipping_method'), 4);
		
		if ($this->cart->hasShipping()) {
			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 5);
			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 6);
		} else {
			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 3);
			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 4);	
		}

        $this->load->model('extension/payment/dibseasy');
        $dibs_model = $this->model_extension_payment_dibseasy;

        $checkoutData = $dibs_model->getCheckoutConfirm();

        $data['paymentId'] = '';

        if($paymentId = $this->model_extension_payment_dibseasy->initCheckout()) {
            $data['paymentId'] = $paymentId;
        }else {
            $data['initerror'] = 'An error occurred during payment initialization';
        }

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
			$data['error_warning'] = '';
		}

		$data['logged'] = $this->customer->isLogged();

		if (isset($this->session->data['account'])) {
			$data['account'] = $this->session->data['account'];
		} else {
			$data['account'] = '';
		}

		$data['shipping_required'] = $this->cart->hasShipping();

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('checkout/dibseasy', array_merge($data, $checkoutData)));
	}
        
public function updateview() {
   
       $this->load->model('extension/payment/dibseasy');
       $action = $this->request->post['action'];
       $this->load->language('checkout/dibseasy');
       $data = array();
        try {
            switch($action) {
                case 'set-shipping-method':
                     $code = $this->request->post['code'];
                     $this->model_extension_payment_dibseasy->setShippingMethod($code);
                   break;
                case 'start':
                      $this->model_extension_payment_dibseasy->start();
                    break;
                case 'address-changed':
                      $this->model_extension_payment_dibseasy->setShippingAddress();
                   break;
            }
            
            $data['shipping_methods'] = $this->model_extension_payment_dibseasy->getShippingMethods();
            
            $data['totals'] = $this->model_extension_payment_dibseasy->getTotals();
           
            $data['code'] = isset($this->session->data['shipping_method']['code']) ? $this->session->data['shipping_method']['code'] : null;
            $data['checkout_url'] = $this->config->get('payment_dibseasy_otherpayment_button_url');
            if(empty($data['checkout_url'])) {
                $data['checkout_url'] = $this->url->link('checkout/checkout', '', true);
            }
            
            $data['button_checkout_label'] = $this->language->get('button_checkout_label');

            $data['order_summary_label'] = $this->language->get('order_summary_label');

            $data['shipping_methods_label'] = $this->language->get('shipping_methods_label');

            $data['shipping_total_label'] = $this->language->get('shipping_total_label');

            $data['currency_code'] = $this->session->data['currency'];
             
            $result = array('outputHtml' => $this->load->view('checkout/dibseasy_totals.tpl', $data));
        } catch(Exception $e) {
            $this->session->data['error'] = $e->getMessage();
            $result = array('outputHtml' => [], 'exception' => 1, 'message' => $e->getMessage());
            unset($this->session->data['dibseasy']['paymentid']);
        }
        echo json_encode($result);
    }
    
}