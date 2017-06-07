<?php

class ControllerExtensionPaymentDibseasy extends Controller {
	private $error = array();

	public function index() {
                
          	$this->load->language('extension/payment/dibseasy');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                    $this->model_setting_setting->editSetting('dibseasy', $this->request->post);
                    $this->session->data['success'] = $this->language->get('text_success');
                    $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
        	$data['entry_dibseasy_merchant'] = $this->language->get('entry_dibseasy_merchant');
          
                
               	$data['entry_dibseasy_checkoutkey_test'] = $this->language->get('entry_dibseasy_checkoutkey');
                $data['entry_dibseasy_checkoutkey_live'] = $this->language->get('entry_dibseasy_checkoutk_live');
                
                $data['entry_order_status'] = $this->language->get('entry_order_status');
                
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
                
                $data['entry_shipping_method'] = $this->language->get('entry_shipping_method');
                $data['text_free_shipping'] = $this->language->get('text_free_shipping');
                $data['text_flat_shipping'] = $this->language->get('text_flat_shipping');
                
                $data['entry_debug'] = $this->language->get('entry_debug');
                $data['entry_testmode'] = $this->language->get('entry_testmode');
                $data['entry_dibseasy_livekey'] = $this->language->get('entry_dibseasy_livekey');
                $data['entry_dibseasy_testkey'] = $this->language->get('entry_dibseasy_testkey');
                $data['entry_shipping_method_description'] = $this->language->get('entry_shipping_method_description');
                $data['entry_testmode_description'] =  $this->language->get('entry_testmode_description');
                $data['entry_debug_description'] = $this->language->get('entry_debug_description');
                $data['entry_language'] = $this->language->get('entry_language');
                
                
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/dibseasy', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/dibseasy', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

		if (isset($this->request->post['dibseasy_merchant'])) {
			$data['dibseasy_merchant'] = $this->request->post['dibseasy_merchant'];
		} else {
			$data['dibseasy_merchant'] = $this->config->get('dibseasy_merchant');
		}
                
                if (isset($this->request->post['dibseasy_checkoutkey_test'])) {
			$data['dibseasy_checkoutkey_test'] = $this->request->post['dibseasy_checkoutkey_test'];
		} else {
			$data['dibseasy_checkoutkey_test'] = $this->config->get('dibseasy_checkoutkey_test');
		}
                
                if (isset($this->request->post['dibseasy_checkoutkey_live'])) {
			$data['dibseasy_checkoutkey_live'] = $this->request->post['dibseasy_checkoutkey_live'];
		} else {
			$data['dibseasy_checkoutkey_live'] = $this->config->get('dibseasy_checkoutkey_live');
		}
                
                if (isset($this->request->post['dibseasy_total'])) {
			$data['dibseasy_total'] = $this->request->post['dibseasy_total'];
		} else {
			$data['dibseasy_total'] = $this->config->get('dibseasy_total');
		}
                
                if (isset($this->request->post['dibseasy_shipping_method'])) {
			$data['dibseasy_shipping_method'] = $this->request->post['dibseasy_shipping_method'];
		} else {
			$data['dibseasy_shipping_method'] = $this->config->get('dibseasy_shipping_method');
		}
                
                if (isset($this->request->post['dibseasy_order_status_id'])) {
			$data['dibseasy_order_status_id'] = $this->request->post['dibseasy_order_status_id'];
		} else {
			$data['dibseasy_order_status_id'] = $this->config->get('dibseasy_order_status_id');
		}
                
                if (isset($this->request->post['dibseasy_order_status_id'])) {
			$data['dibseasy_order_status_id'] = $this->request->post['dibseasy_order_status_id'];
		} else {
			$data['dibseasy_order_status_id'] = $this->config->get('dibseasy_order_status_id');
		}
                
                if (isset($this->request->post['dibseasy_testmode'])) {
			$data['dibseasy_testmode'] = $this->request->post['dibseasy_testmode'];
		} else {
			$data['dibseasy_testmode'] = $this->config->get('dibseasy_testmode');
		}
                
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		
		if (isset($this->request->post['dibseasy_status'])) {
			$data['dibseasy_status'] = $this->request->post['dibseasy_status'];
		} else {
			$data['dibseasy_status'] = $this->config->get('dibseasy_status');
		}
                
                if (isset($this->request->post['dibseasy_debug'])) {
			$data['dibseasy_debug'] = $this->request->post['dibseasy_debug'];
		} else {
			$data['dibseasy_debug'] = $this->config->get('dibseasy_debug');
		}
                
                if (isset($this->request->post['dibseasy_livekey'])) {
			$data['dibseasy_livekey'] = $this->request->post['dibseasy_livekey'];
		} else {
			$data['dibseasy_livekey'] = $this->config->get('dibseasy_livekey');
		}
           
                if (isset($this->request->post['dibseasy_testkey'])) {
			$data['dibseasy_testkey'] = $this->request->post['dibseasy_testkey'];
		} else {
			$data['dibseasy_testkey'] = $this->config->get('dibseasy_testkey');
		}
                
                if (isset($this->request->post['dibseasy_language'])) {
			$data['dibseasy_language'] = $this->request->post['dibseasy_language'];
		} else {
			$data['dibseasy_language'] = $this->config->get('dibseasy_language');
		}
          
		$data['error_merchant'] = '';
                if(isset($this->error['error_merchant'])) {
                    $data['error_merchant'] = $this->error['error_merchant']; 
                }
                
                $data['free_shipping_disabled'] = '';
                if(isset($this->error['free_shipping_disabled'])) {
                    $data['free_shipping_disabled'] = $this->error['free_shipping_disabled'];
                }
                
                $data['checkout_key_test'] = '';
                if(isset($this->error['checkout_key_test'])) {
                    $data['checkout_key_test'] = $this->error['checkout_key_test']; 
                }
                
                $data['checkout_key_live'] = '';
                if(isset($this->error['checkout_key_live'])) {
                    $data['checkout_key_live'] = $this->error['checkout_key_live']; 
                }
                
                $data['dibseasy_livekey_error'] = '';
                if(isset($this->error['dibseasy_livekey'])) {
                    $data['dibseasy_livekey_error'] = $this->error['dibseasy_livekey']; 
                }
                
                $data['dibseasy_testkey_error'] = '';
                if(isset($this->error['dibseasy_testkey'])) {
                    $data['dibseasy_testkey_error'] = $this->error['dibseasy_testkey']; 
                }
                $data['text_english'] = 'English';
                $data['text_swedish'] = 'Swedish';
                $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/dibseasy', $data));
	}

	protected function validate() {
           	if (!$this->user->hasPermission('modify', 'extension/payment/dibseasy')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
                if (isset($this->request->post['dibseasy_merchant']) && strlen(trim($this->request->post['dibseasy_merchant'])) == 0) {
                    $this->error['error_merchant'] = $this->language->get('error_merchant'); //"Merchant id is required";
                }
                if (isset($this->request->post['dibseasy_checkoutkey']) && strlen(trim($this->request->post['dibseasy_checkoutkey'])) == 0) {
                    $this->error['checkout_key'] = $this->language->get('checkout_key'); // "Merchant id is required";
                }
                
                if(isset($this->request->post['dibseasy_shipping_method'])) {
                    if($this->request->post['dibseasy_shipping_method'] == 'free') {
                        $this->load->model('setting/setting'); 
                        $settings = $this->model_setting_setting->getSetting('free');
                        if(!$settings) {
                           $this->error['free_shipping_disabled'] = $this->language->get('free_shipping_disabled');
                        } else {
                          if(isset($settings['free_status']) && $settings['free_status'] != 1) {
                             $this->error['free_shipping_disabled'] = $this->language->get('free_shipping_disabled');
                          }
                        }
                    }
                }
                
                if(isset($this->request->post['dibseasy_livekey']) && strlen(trim($this->request->post['dibseasy_livekey'])) == 0) {
                    $this->error['dibseasy_livekey'] = $this->language->get('entry_dibseasy_livekey_error');
                }
                
                if(isset($this->request->post['dibseasy_testkey']) && strlen(trim($this->request->post['dibseasy_testkey'])) == 0) {
                    $this->error['dibseasy_testkey'] = $this->language->get('entry_dibseasy_testkey_error');
                }
                
                
                
                return !$this->error ? true : false;
	}
}
