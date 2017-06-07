<?php
class ModelExtensionPaymentDibseasy extends Model {
        
    const METHOD_CODE = 'dibseasy';
    const SHIPPING_CODE = 'free';
    const PAYMENT_API_TEST_URL = 'https://test.api.dibspayment.eu/v1/payments';
    const PAYMENT_API_LIVE_URL = 'https://api.dibspayment.eu/v1/payments';
    const PAYMENT_TRANSACTION_URL_PATTERN_TEST = 'https://test.api.dibspayment.eu/v1/payments/{transactionId}';
    const PAYMENT_TRANSACTION_URL_PATTERN_LIVE = 'https://api.dibspayment.eu/v1/payments/{transactionId}';
    const CHECKOUT_SCRIPT_TEST = 'https://test.checkout.dibspayment.eu/v1/checkout.js?v=1';
    const CHECKOUT_SCRIPT_LIVE = 'https://checkout.dibspayment.eu/v1/checkout.js?v=1';
    protected $products = array();
    protected $logger;
    public $paymentId;

        public function __construct($registry) {
                $this->logger = new Log('dibs.easy.log');
                parent::__construct($registry);
        }
        
	public function getMethod($address, $total) {
            $this->load->language('extension/payment/dibseasy');
            $status = true;
            $method_data = array();
            if ($status) {
                $method_data = array(
                    'code'       => self::METHOD_CODE,
                    'title'      =>  $this->language->get('text_title'),
                    'terms'      => '',
                    'sort_order' => $this->config->get('dibseasy_sort_order')
                );
            }
           return $method_data;
	}
        
        
        public function getCheckoutConfirm() {
                $this->load->language('extension/payment/dibseasy');
       	 	$redirect = '';
                if(!$this->validateCart()) {
                   $this->response->redirect($this->url->link('checkout/cart', '', true));
                }
                
                // Set payment method
	        $this->session->data['payment_method'] = self::METHOD_CODE;
                $this->session->data['payment_method'] =  array(
			'code'       => self::METHOD_CODE,
			'title'      => $this->language->get('text_title'),
			'sort_order' => '1');
		
                
                // Set shipping method without shipping addresses
                $this->setShippingMethod();
                
                if (!$redirect) {
                       
			$order_data = array();
                        
			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			$this->load->model('extension/extension');

			$sort_order = array();

			$results = $this->model_extension_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);
                			// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);

			$order_data['totals'] = $totals;
                        
                        $this->load->language('checkout/checkout');

			$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$order_data['store_id'] = $this->config->get('config_store_id');
			$order_data['store_name'] = $this->config->get('config_name');

			if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
			} else {
				if ($this->request->server['HTTPS']) {
					$order_data['store_url'] = HTTPS_SERVER;
				} else {
					$order_data['store_url'] = HTTP_SERVER;
				}
			}

			if ($this->customer->isLogged()) {
				$this->load->model('account/customer');
				$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
				$order_data['customer_id'] = $this->customer->getId();
				$order_data['customer_group_id'] = $customer_info['customer_group_id'];
				$order_data['firstname'] = $customer_info['firstname'];
				$order_data['lastname'] = $customer_info['lastname'];
				$order_data['email'] = $customer_info['email'];
				$order_data['telephone'] = $customer_info['telephone'];
				$order_data['fax'] = $customer_info['fax'];
				$order_data['custom_field'] = json_decode($customer_info['custom_field'], true);
                                $order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());
			} else {
                                $order_data['customer_id'] = 0;
				$order_data['customer_group_id'] = 1;
				$order_data['firstname'] = '';
				$order_data['lastname'] = '';
				$order_data['email'] = '';
				$order_data['telephone'] = '';
				$order_data['fax'] = '';
                                
                        }
                            $order_data['payment_firstname'] = '';
                            $order_data['payment_lastname'] = '';
                            $order_data['payment_company'] = '';
                            $order_data['payment_address_1'] = '';
                            $order_data['payment_address_2'] = '';
                            $order_data['payment_city'] = '';
                            $order_data['payment_postcode'] = '';
                            $order_data['payment_zone'] = '';
                            $order_data['payment_zone_id'] = '';
                            $order_data['payment_country'] ='';
                            $order_data['payment_country_id'] = '';
                            $order_data['payment_address_format'] = '';       
                            
                            // We don't know the shipping still...
                            $order_data['shipping_firstname'] = '';
                            $order_data['shipping_lastname'] = '';
                            $order_data['shipping_company'] = '';
                            $order_data['shipping_address_1'] = '';
                            $order_data['shipping_address_2'] = '';
                            $order_data['shipping_city'] = '';
                            $order_data['shipping_postcode'] = '';
                            $order_data['shipping_zone'] = '';
                            $order_data['shipping_zone_id'] = '';
                            $order_data['shipping_country'] = '';
                            $order_data['shipping_country_id'] = '';
                            $order_data['shipping_address_format'] = '';
                            $order_data['shipping_custom_field'] = array();
                            $order_data['shipping_method'] = '';
                            $order_data['shipping_code'] = '';

			if (isset($this->session->data['payment_method']['title'])) {
				$order_data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$order_data['payment_method'] = '';
			}

			if (isset($this->session->data['payment_method']['code'])) {
				$order_data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$order_data['payment_code'] = '';
			}

			if ($this->cart->hasShipping()) {
                         	if (isset($this->session->data['shipping_method']['title'])) {
					$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
				} else {
					$order_data['shipping_method'] = '';
				}

				if (isset($this->session->data['shipping_method']['code'])) {
					$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
				} else {
					$order_data['shipping_code'] = '';
				}
                                
                                $shippingMethod = $this->session->data['shipping_method'];
                                $order_data['shipping_code'] = $shippingMethod['code'];
                                $order_data['shipping_method'] = $shippingMethod['title'];
                   	 }
			
			$order_data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}

				$order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				);
			}

			// Gift Voucher
			$order_data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$order_data['vouchers'][] = array(
						'description'      => $voucher['description'],
						'code'             => token(10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'amount'           => $voucher['amount']
					);
				}
			}

			
			$order_data['total'] = $total_data['total'];

			if (isset($this->request->cookie['tracking'])) {
				$order_data['tracking'] = $this->request->cookie['tracking'];

				$subtotal = $this->cart->getSubTotal();

				// Affiliate
				$this->load->model('affiliate/affiliate');

				$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);

				if ($affiliate_info) {
					$order_data['affiliate_id'] = $affiliate_info['affiliate_id'];
					$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
				} else {
					$order_data['affiliate_id'] = 0;
					$order_data['commission'] = 0;
				}

				// Marketing
				$this->load->model('checkout/marketing');

				$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

				if ($marketing_info) {
					$order_data['marketing_id'] = $marketing_info['marketing_id'];
				} else {
					$order_data['marketing_id'] = 0;
				}
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';
			}

			$order_data['language_id'] = $this->config->get('config_language_id');
			$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
			$order_data['currency_code'] = $this->session->data['currency'];
			$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
			$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$order_data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$order_data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$order_data['accept_language'] = '';
			}

			$this->load->model('checkout/order');

                        $order_data['comment'] = '';
			$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);

			$data['text_recurring_item'] = $this->language->get('text_recurring_item');
			$data['text_payment_recurring'] = $this->language->get('text_payment_recurring');

			$data['column_name'] = $this->language->get('column_name');
			$data['column_model'] = $this->language->get('column_model');
			$data['column_quantity'] = $this->language->get('column_quantity');
			$data['column_price'] = $this->language->get('column_price');
			$data['column_total'] = $this->language->get('column_total');

			$this->load->model('tool/upload');

			$data['products'] = array();
                      
                        foreach ($this->cart->getProducts() as $product) {
				$option_data = array();
                   
				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year'),
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				$data['products'][] = array(
					'cart_id'    => $product['cart_id'],
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'recurring'  => $recurring,
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
					'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']),
					'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			// Gift Voucher
			$data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$data['vouchers'][] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
					);
				}
			}

			$data['totals'] = array();

			foreach ($order_data['totals'] as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
				);
                                
			}
                        $data['checkoutkey'] = trim($this->config->get('dibseasy_checkoutkey'));
                        if($this->config->get('dibseasy_testmode') == 0) {
                            $data['checkoutkey'] = trim($this->config->get('dibseasy_checkoutkey_live'));
                        } else {
                            $data['checkoutkey'] =  trim($this->config->get('dibseasy_checkoutkey_test'));
                        }
                        $data['language'] = $this->config->get('dibseasy_language');
                        
                        if($this->config->get('dibseasy_testmode') == 0) {
                             $data['checkout_script'] = self::CHECKOUT_SCRIPT_LIVE;
                        } else {
                             $data['checkout_script'] = self::CHECKOUT_SCRIPT_TEST;
                        }
                        
                        $data['checkoutconfirmurl'] = $this->url->link('extension/payment/dibseasy/confirm', '', true);
        	} else {
			$data['redirect'] = $redirect;
		}

		return $data;
        }
        
        protected function setShippingMethod() {
            if ($this->validateCart() && $this->cart->hasShipping()) {
                $json['shipping_methods'] = array();
				$this->load->model('extension/extension');
        			$results = $this->model_extension_extension->getExtensions('shipping');
                                $shippingMethod = $this->config->get('dibseasy_shipping_method') != null ? 
                                        $this->config->get('dibseasy_shipping_method') : self::SHIPPING_CODE;
                        if ($this->config->get($shippingMethod . '_status')) {
                                $this->load->model('extension/shipping/' . $shippingMethod);
                                $quote = $this->{'model_extension_shipping_' . $shippingMethod}->getQuote(array('country_id'=>0, 'zone_id'=>0));
                                if ($quote) {
                                        $json['shipping_methods'][$shippingMethod] = array(
                                                'title'      => $quote['title'],
                                                'quote'      => $quote['quote'],
                                                'sort_order' => $quote['sort_order'],
                                                'error'      => $quote['error']
                                        );
                                }
                        }
	        $this->session->data['shipping_method'] =  $json['shipping_methods'][$shippingMethod]['quote'][$shippingMethod];
            }
        }
        
        protected function validateCart() {
            // Validate cart has products and has stock.
            if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
                    $json['redirect'] = $this->url->link('checkout/cart');
                    return false;
            }
            // Validate minimum quantity requirements.
            $products = $this->cart->getProducts();
            foreach ($products as $product) {
                    $product_total = 0;

                    foreach ($products as $product_2) {
                            if ($product_2['product_id'] == $product['product_id']) {
                                    $product_total += $product_2['quantity'];
                            }
                    }
                    if ($product['minimum'] > $product_total) {
                            $json['redirect'] = $this->url->link('checkout/cart');
                            return false;
                            break;
                    }
            }
            return true;
        }
        
        public function initCheckout() {
            $paymentId = '';
         
            if($this->config->get('dibseasy_testmode') == 0) {
                $url = self::PAYMENT_API_LIVE_URL;
            } else {
                $url = self::PAYMENT_API_TEST_URL;
            }
            
            $response = $this->makeCurlRequest($url, $this->collectData());
            if($response && isset($response->paymentId)) {
                return $response->paymentId;
            } else {
                $this->logger->write($response);
            }
            return false;
        }
        
        protected function makeCurlRequest($url, $data, $method = 'POST') {
            $curl = curl_init();
            $header = array();
            $headers[] = "Content-Type: text/json"; 
            $headers[] = "Accept: test/json"; 
            if($this->config->get('dibseasy_testmode') == 1) {
               $headers[] = 'Authorization: ' . str_replace('-', '', trim($this->config->get('dibseasy_testkey')));
            } else {
               $headers[] = 'Authorization: ' . str_replace('-', '', trim($this->config->get('dibseasy_livekey')));
            }
            $postData = $data;
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            if($this->config->get('dibseasy_debug')) {
                   $this->logger->write("Curl request:");
                   $this->logger->write($data);
            }
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
            $this->logger->write($info);
            if ($info['http_code'] == 401 || $info['http_code'] == 404 || $info['http_code'] == 403) {
                $this->logger->write("Authorization failed, please check your secret key and mode test/live");
            } else {
                if( $response ) {
                   $responseDecoded = json_decode($response);
                   if($this->config->get('dibseasy_debug')) {
                       $this->logger->write("Curl response:");
                       $this->logger->write($response);
                   }
                   return ($responseDecoded) ? $responseDecoded : null;
                }
            }
            
            if(curl_error($curl)) {
              $this->logger->write("Curl error:");
              $this->logger->write(curl_error($curl));
            }
            
         
        }
        
        protected function getTotalTaxRate($tax_class_id) {
             $totalRate = 0;   
               foreach($this->tax->getRates(0, $tax_class_id) as $tax) {
                   if('P' == $tax['type']) {
                       $totalRate += $tax['rate'];
                   }
               }
              return $totalRate;
        }

        public function collectData() {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $products = $this->cart->getProducts();
            $totalPriceCalculated = 0;
            foreach ($this->cart->getProducts() as $product) {
                $price = $this->currency->format($product['price'], $order_info['currency_code'], '', false);
                $price = round($price * 100);
                $totalPriceCalculated += $price;
                
            }
            $taxes = $this->cart->getTaxes();
            foreach ($this->cart->getProducts() as $product) {
                $option_data = array();
                $netPrice = $this->currency->format($product['price'], $order_info['currency_code'], '', false);
                $grossPrice =  $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);     
                $taxAmount = $this->getTaxAmount($netPrice, $product['tax_class_id'], $order_info);
                $taxRates = $this->tax->getRates($netPrice,  $product['tax_class_id']);
                
                $this->currency->format($product['price'], $order_info['currency_code'], '', false);
                $taxRate = $this->getTotalTaxRate($product['tax_class_id']);
                $this->products[] = array(
                    'reference' => $product['product_id'],
                    'name' => $product['name'],
                    'quantity' => $product['quantity'],
                    'unit' => 'pcs',
                    'unitPrice' => round($netPrice * $product['quantity'] * 100),
                    'taxRate' => $taxRate * 100,
                    'taxAmount' => round(($taxAmount * $product['quantity']) * 100),
                    'grossTotalAmount' => round($grossPrice * 100) * $product['quantity'],
                    'netTotalAmount' => round($netPrice * $product['quantity'] * 100));
             }
            $totals = $this->getTotals();  
            foreach($totals['totals'] as $total) {
                    $shipping_method = $this->session->data['shipping_method'];
                    $shipping_tax_class = isset($shipping_method['tax_class_id']) ? $shipping_method['tax_class_id'] : 0;
                if( in_array($total['code'], $this->additional_totals()) && abs($total['value']) > 0) {
                    if($total['code'] == 'shipping') {
                        $netPrice = $this->currency->format($total['value'], $order_info['currency_code'], '', false);
                        $grossPrice =  $this->currency->format($this->tax->calculate($total['value'], $shipping_tax_class, $this->config->get('config_tax')), $this->session->data['currency'], '', false);     
                        $taxAmount = $this->tax->getTax($netPrice, $shipping_tax_class);
                        $taxRate = $this->getTotalTaxRate($shipping_tax_class);
                    } else {
                        $grossPrice = $netPrice = $this->currency->format($total['value'], $order_info['currency_code'], '', false);;
                        $taxAmount = $taxRate = 0;
                    }
                    $price = $this->currency->format($total['value'], $order_info['currency_code'], '', false);
                    $this->products[] = array(
                        'reference' => $total['code'],
                        'name' => $total['title'],
                        'quantity' => 1,
                        'unit' => 1,
                        'unitPrice' => round($netPrice * 100),
                        'taxRate' => $taxRate * 100,
                        'taxAmount' => round($taxAmount * 100),
                        'grossTotalAmount' => round($grossPrice * 100),
                        'netTotalAmount' => round($netPrice * 100));
                  }
              }
              $totalPriceCalculated = 0;
              foreach($this->products as $product) {
                  $totalPriceCalculated += $product['grossTotalAmount'];
              }
              $total = round($this->currency->format($order_info['total'], $order_info['currency_code'], '', false) * 100);
              $delta = $total - $totalPriceCalculated;
              if(isset($this->session->data['coupon'])) {
                   $this->products[] = array(
                    'reference' => 'coupon',
                    'name' => 'Coupon',
                    'quantity' => 1,
                    'unit' => 1,
                    'unitPrice' => $delta,
                    'taxRate' => 0,
                    'taxAmount' => 0,
                    'grossTotalAmount' => $delta,
                    'netTotalAmount' => $delta);
              } else {
                if($total !=  $totalPriceCalculated) {
                    $delta = $total - $totalPriceCalculated;
                    $this->products[] = array(
                        'reference' => 'rouding',
                        'name' => 'rounding',
                        'quantity' => 1,
                        'unit' => 1,
                        'unitPrice' => $delta,
                        'taxRate' => 0,
                        'taxAmount' => 0,
                        'grossTotalAmount' => $delta,
                        'netTotalAmount' => $delta);
                    }
            }
            $data = array(
                'order' => array(
                    'items' => $this->products,
                    'amount' => round($this->currency->format($order_info['total'], $order_info['currency_code'], '', false) * 100),
                    'currency' => $order_info['currency_code'],
                    'reference' => 'opc_' . $this->session->data['order_id']),
                    'checkout' => array(
                    'url' => $this->url->link('extension/payment/dibseasy/confirm', '', true),
                ),
                'merchantNumber' => trim($this->config->get('dibseasy_merchant')),
            );
            if($this->config->get('dibseasy_debug')) {
                   $this->logger->write("Collected data:");
                   $this->logger->write($data);
            }
            
            return json_encode($data);
        }
        
        public function getTransactionInfo($transactionId) {
             if($this->config->get('dibseasy_testmode') == 1) {
                  $url = str_replace('{transactionId}', $transactionId, self::PAYMENT_TRANSACTION_URL_PATTERN_TEST);
             } else {
                  $url = str_replace('{transactionId}', $transactionId, self::PAYMENT_TRANSACTION_URL_PATTERN_LIVE);
             }
            return $this->makeCurlRequest($url, array(), 'GET');
        }
        
        public function getCountryByIsoCode3($iso_code_3) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE `iso_code_3` = '" . $this->db->escape($iso_code_3) . "' AND `status` = '1'");
		return $query->row;
	}
        
        public function setAddresses($order_id, $data) {
            $setFields = '';
            foreach($data as $key => $value) {
               $setFields .= '`'.$key. '`' . "='" . $this->db->escape($value) . "',";
            }
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET ". $setFields ." date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
        }
        
        protected function getTotals() {
               $order_data = array();
            $order_data['totals'] = array();
            $this->load->model('extension/extension');
            $sort_order = array();
            $totals = array();
            $taxes = $this->cart->getTaxes();
            $total = 0;
            // Because __call can not keep var references so we put them into an array.                     
            $total_data = array(
                    'totals' => &$totals,
                    'taxes'  => &$taxes,
                    'total'  => &$total
            );
            $results = $this->model_extension_extension->getExtensions('total');
            foreach ($results as $key => $value) {
                    $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
            }
            array_multisort($sort_order, SORT_ASC, $results);
            foreach ($results as $result) {
                    if ($this->config->get($result['code'] . '_status')) {
                            $this->load->model('extension/total/' . $result['code']);
                            $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                    }
            }
            
            return $total_data;
        }
        
        protected function additional_totals() {
            return array('shipping');
        }
        
        public function getTaxAmount($value, $tax_class_id, $order_info) {
            $amount = 0;
            $tax_rates = $this->tax->getRates($value,  $tax_class_id);
            foreach ($tax_rates as $tax_rate) {
                  if($tax_rate['type'] == 'F') {
                        $amount +=  $this->currency->format($tax_rate['amount'], $order_info['currency_code'], '', false);
                    } else {
                       $amount += $tax_rate['amount'];
                    }
            }
            return $amount;
	}
       
}
