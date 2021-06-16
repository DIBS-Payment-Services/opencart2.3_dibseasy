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
                'code' => self::METHOD_CODE,
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('dibseasy_sort_order')
            );
        }

        return $method_data;
    }

    public function getCheckoutConfirm() {
        $this->load->language('extension/payment/dibseasy');
        $redirect = '';
        if (!$this->validateCart()) {
            $this->response->redirect($this->url->link('checkout/cart', '', true));
        }

        // Set payment method
        $this->session->data['payment_method'] = self::METHOD_CODE;
        $this->session->data['payment_method'] = array(
            'code' => self::METHOD_CODE,
            'title' => $this->language->get('text_title'),
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
                'taxes' => &$taxes,
                'total' => &$total
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
            $order_data['payment_country'] = '';
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
                        'product_option_id' => $option['product_option_id'],
                        'product_option_value_id' => $option['product_option_value_id'],
                        'option_id' => $option['option_id'],
                        'option_value_id' => $option['option_value_id'],
                        'name' => $option['name'],
                        'value' => $option['value'],
                        'type' => $option['type']
                    );
                }

                $order_data['products'][] = array(
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'model' => $product['model'],
                    'option' => $option_data,
                    'download' => $product['download'],
                    'quantity' => $product['quantity'],
                    'subtract' => $product['subtract'],
                    'price' => $product['price'],
                    'total' => $product['total'],
                    'tax' => $this->tax->getTax($product['price'], $product['tax_class_id']),
                    'reward' => $product['reward']
                );
            }

            // Gift Voucher
            $order_data['vouchers'] = array();

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $voucher) {
                    $order_data['vouchers'][] = array(
                        'description' => $voucher['description'],
                        'code' => token(10),
                        'to_name' => $voucher['to_name'],
                        'to_email' => $voucher['to_email'],
                        'from_name' => $voucher['from_name'],
                        'from_email' => $voucher['from_email'],
                        'voucher_theme_id' => $voucher['voucher_theme_id'],
                        'message' => $voucher['message'],
                        'amount' => $voucher['amount']
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
                        'name' => $option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                    );
                }

                $recurring = '';

                if ($product['recurring']) {
                    $frequencies = array(
                        'day' => $this->language->get('text_day'),
                        'week' => $this->language->get('text_week'),
                        'semi_month' => $this->language->get('text_semi_month'),
                        'month' => $this->language->get('text_month'),
                        'year' => $this->language->get('text_year'),
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
                    'cart_id' => $product['cart_id'],
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'model' => $product['model'],
                    'option' => $option_data,
                    'recurring' => $recurring,
                    'quantity' => $product['quantity'],
                    'subtract' => $product['subtract'],
                    'price' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
                    'total' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']),
                    'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                );
            }

            // Gift Voucher
            $data['vouchers'] = array();

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $voucher) {
                    $data['vouchers'][] = array(
                        'description' => $voucher['description'],
                        'amount' => $this->currency->format($voucher['amount'], $this->session->data['currency'])
                    );
                }
            }

            $data['totals'] = array();

            foreach ($order_data['totals'] as $total) {
                $data['totals'][] = array(
                    'title' => $total['title'],
                    'text' => $this->currency->format($total['value'], $this->session->data['currency'])
                );
            }

            $data['checkoutkey'] = trim($this->config->get('dibseasy_checkoutkey'));

            if ($this->config->get('dibseasy_testmode') == 0) {
                $data['checkoutkey'] = trim($this->config->get('dibseasy_checkoutkey_live'));
            } else {
                $data['checkoutkey'] = trim($this->config->get('dibseasy_checkoutkey_test'));
            }
            $data['language'] = $this->config->get('dibseasy_language');

            if ($this->config->get('dibseasy_testmode') == 0) {
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

    protected function setShippingMethodb() {
        if ($this->validateCart() && $this->cart->hasShipping()) {
            $json['shipping_methods'] = array();
            $this->load->model('extension/extension');
            $results = $this->model_extension_extension->getExtensions('shipping');
            $shippingMethod = $this->config->get('dibseasy_shipping_method') != null ?
                    $this->config->get('dibseasy_shipping_method') : self::SHIPPING_CODE;
            if ($this->config->get($shippingMethod . '_status')) {
                $this->load->model('extension/shipping/' . $shippingMethod);
                $quote = $this->{'model_extension_shipping_' . $shippingMethod}->getQuote(array('country_id' => 0, 'zone_id' => 0));
                if ($quote) {
                    $json['shipping_methods'][$shippingMethod] = array(
                        'title' => $quote['title'],
                        'quote' => $quote['quote'],
                        'sort_order' => $quote['sort_order'],
                        'error' => $quote['error']
                    );
                }
            }
            $this->session->data['shipping_method'] = $json['shipping_methods'][$shippingMethod]['quote'][$shippingMethod];
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
        if (!$this->cart->hasProducts()) {
            unset($this->session->data['dibseasy']['paymentid']);
        }
        if ($this->config->get('dibseasy_testmode') == 0) {
            $url = self::PAYMENT_API_LIVE_URL;
        } else {
            $url = self::PAYMENT_API_TEST_URL;
        }
        $response = $this->makeCurlRequest($url, $this->collectData());

        if ($response && isset($response->paymentId)) {
            $this->session->data['dibseasy']['paymentid'] = $response->paymentId;
            return $response->paymentId;
        } else {
            $this->logger->write($response);
        }
        return false;
    }

    protected function makeCurlRequest($url, $data, $method = 'POST') {
        $curl = curl_init();
        $headers[] = "Content-Type: text/json";
        $headers[] = "Accept: test/json";
        $headers[] = 'commercePlatformTag: OC23';
        if ($this->config->get('dibseasy_testmode') == 1) {
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

        if ($this->config->get('dibseasy_debug')) {
            $this->logger->write("Curl request:");
            $this->logger->write($data);
        }

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        if ($info['http_code'] == 401 || $info['http_code'] == 404 || $info['http_code'] == 403) {
            $this->logger->write("Authorization failed, please check your secret key and mode test/live");
        } else {
            if ($response) {
                $responseDecoded = json_decode($response);
                if ($this->config->get('dibseasy_debug')) {
                    $this->logger->write("Curl response:");
                    $this->logger->write($response);
                }
                return ($responseDecoded) ? $responseDecoded : null;
            }
        }
        if (curl_error($curl)) {
            $this->logger->write("Curl error:");
            $this->logger->write(curl_error($curl));
        }
    }

    protected function getTotalTaxRate($tax_class_id) {
        $totalRate = 0;
        foreach ($this->tax->getRates(0, $tax_class_id) as $tax) {
            if ('P' == $tax['type']) {
                $totalRate += $tax['rate'];
            }
        }
        return $totalRate;
    }

    /**
     * construct object for creating payment in Nets in json format
     *
     * @return false|string
     */
    public function collectData() {
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $items = $this->getRequestObjectItems();

        //get total amount = sum of all gross totals of items
        $itemsGrossPriceSumma = $this->getItemGrossTotalSum($items);
        $data = array(
            'order' => array(
                'items' => $items,
                //'amount' => $this->getNetsIntValue($this->formatPrice($this->getGrandTotal())),
                'amount' =>$this->getNetsIntValue($this->formatAmount($itemsGrossPriceSumma / 100)),
                'currency' => $order_info['currency_code'],
                'reference' => 'opc_' . $this->session->data['order_id']),
            'checkout' => array(
                'termsUrl' => $this->config->get('dibseasy_terms_and_conditions'),
                'charge' => ($this->config->get('dibseasy_autocapture')) ? 'true' : 'false',
                'merchantTermsUrl' => $this->config->get('dibseasy_merchant_terms_and_conditions')
            ),
            'merchantNumber' => trim($this->config->get('dibseasy_merchant')),
        );
        //echo "<pre>";print_r($data);die;
        $data['checkout']['merchantHandlesConsumerData'] = true;
        $data['checkout']['returnUrl'] = $this->url->link('extension/payment/dibseasy/confirm', '', true);

        if ($this->config->get('dibseasy_checkout_type') == 'hosted') {

            if ($this->customer->isLogged()) {
                $this->load->model('account/customer');
                $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
                $email = $customer_info['email'];
            } elseif (isset($this->session->data['guest'])) {
                $email = $this->session->data['guest']['email'];
            }

            $consumerData = array(
                'email' => $email,
                'privatePerson' => array(
                    'firstName' => !empty($this->session->data['shipping_address']['firstname']) ? $this->session->data['shipping_address']['firstname'] : 'FirstName',
                    'lastName' => !empty($this->session->data['shipping_address']['lastname']) ? $this->session->data['shipping_address']['lastname'] : 'LastName',
                )
            );

            $shippingAddress = array(
                "addressLine1" => !empty($this->session->data['shipping_address']['address_1']) ? $this->session->data['shipping_address']['address_1'] : null,
                "addressLine2" => !empty($this->session->data['shipping_address']['address_2']) ? $this->session->data['shipping_address']['address_2'] : null,
                "postalCode" => !empty($this->session->data['shipping_address']['postcode']) ? $this->session->data['shipping_address']['postcode'] : null,
                "city" => !empty($this->session->data['shipping_address']['city']) ? $this->session->data['shipping_address']['city'] : null,
                "country" => !empty($this->session->data['shipping_address']['iso_code_3']) ? $this->session->data['shipping_address']['iso_code_3'] : null
            );

            if ($this->validateShippingAddress($shippingAddress)) {
                $consumerData['shippingAddress'] = $shippingAddress;
            }

            $data['checkout']['consumer'] = $consumerData;
            $data['checkout']['merchantHandlesConsumerData'] = true;
            $data['checkout']['integrationType'] = 'HostedPaymentPage';
            $data['checkout']['returnUrl'] = $this->url->link('extension/payment/dibseasy/confirmhosted', '', true);
            $data['checkout']['cancelUrl'] = $this->url->link('checkout/cart', '', true);
        }

        if ($this->config->get('dibseasy_checkout_type') == 'embedded') {
            $data['checkout']['url'] = $this->url->link('extension/payment/dibseasy/confirm', '', true);
        }

        $customerType = $this->config->get('dibseasy_allowed_customer_type');

        $supportedTypes = array();

        if (trim($customerType)) {
            $default = null;
            switch ($customerType) {
                case 'b2c' :
                    $supportedTypes = array('B2C');
                    $default = 'B2C';
                    break;
                case 'b2b':
                    $supportedTypes = array('B2B');
                    $default = 'B2B';
                    break;
                case 'b2c_b2b_b2c':
                    $supportedTypes = array('B2C', 'B2B');
                    $default = 'B2C';
                    break;
                case 'b2b_b2c_b2b':
                    $supportedTypes = array('B2C', 'B2B');
                    $default = 'B2B';
                    break;
            }
        }
        $consumerType = array('supportedTypes' => $supportedTypes, 'default' => $default);
        if ($consumerType) {
            $checkout = $data['checkout'];
            $checkout['consumerType'] = $consumerType;
            $data['checkout'] = $checkout;
        }

        if ($this->config->get('dibseasy_debug')) {
            $this->logger->write("Collected data:");
            $this->logger->write($data);
        }
        //echo "ok<pre>";print_r($data);die;
        return json_encode($data);
    }

    public function getTransactionInfo($transactionId) {
        if ($this->config->get('dibseasy_testmode') == 1) {
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
        foreach ($data as $key => $value) {
            $setFields .= '`' . $key . '`' . "='" . $this->db->escape($value) . "',";
        }
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET " . $setFields . " date_modified = NOW() WHERE order_id = '" . (int) $order_id . "'");
    }

    public function getTotals() {
        $order_data = array();
        $order_data['totals'] = array();
        $this->load->model('extension/extension');
        $sort_order = array();
        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;
        $number_format = false;
        // Because __call can not keep var references so we put them into an array.                     
        $total_data = array(
            'totals' => &$totals,
            'taxes' => &$taxes,
            'total' => &$total
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
        $taxTotal = array_sum($taxes);

        $decimal_place = $this->currency->getDecimalPlace($this->session->data['currency']);

        foreach ($total_data['totals'] as $d) {
            $value = $d['value'];

            $code = $d['code'];
            if (isset($total_translations[$code])) {
                $d['title'] = $total_translations[$code];
                error_log($code);
            }

            if ($number_format) {
                $d['value'] = number_format($this->formatPrice($value), $decimal_place, ".", ",");
            } else {
                $d['value'] = $this->formatPrice($value);
            }
            $totals_rounded[] = $d;
        }
        $total_data['totals'] = $totals_rounded;

        if ($taxTotal > 0) {
            $total_data['totals'][] = ['code' => 'total_taxes',
                'title' => $this->language->get('totals_tax_label'),
                'value' => number_format($taxTotal, $decimal_place, ".", ","),
                'sort_order' => -1];
        }
//        $taxTotal = 0;
//        foreach ($total_data['totals'] as $key => $total) {
//            if ('tax' == $total['code']) {
//                $taxTotal += $total['value'];
//            }
//        }
//
//        if ($taxTotal) {
//            $totalTaxes = array('code' => 'total_taxes',
//                'title' => 'Taxes',
//                'value' => $taxTotal,
//                'sort_order' => 1
//            );
//            array_push($total_data['totals'], $totalTaxes);
//        }

        return $total_data;
    }

    protected function additional_totals() {
        return array('shipping', 'total_taxes', 'coupon');
    }

    public function getTaxAmount($value, $tax_class_id, $order_info) {
        $amount = 0;
        $tax_rates = $this->tax->getRates($value, $tax_class_id);
        foreach ($tax_rates as $tax_rate) {
            if ($tax_rate['type'] == 'F') {
                $amount += $this->currency->format($tax_rate['amount'], $order_info['currency_code'], '', false);
            } else {
                $amount += $tax_rate['amount'];
            }
        }
        return $amount;
    }

    private function formatPrice($value) {
        return $this->format($value, $this->session->data['currency'], '', false);
    }

    private function getNetsIntValue($value) {
        //$return = round($value, 2) * 100;
        // round added because (int)10.12 doing 10.11
        $intvalue = $value * 100;
        if ($intvalue < 10 && $intvalue > 0) {
            return (int) round($value * 1000);
        } else {
            return (int) round($value * 100);
        }
        return (int) $return;
    }

    private function format($number, $currency, $value = '', $format = true) {
        $decimal_place = $this->currency->getDecimalPlace($currency);
        if (empty($decimal_place)) {
            $decimal_place = 2;
        }
        if (!$value) {
            $value = $this->currency->getValue($currency);
        }

        $amount = $value ? (float) $number * $value : (float) $number;
        $amount = round($amount, (int) $decimal_place);
        return $amount;
    }

    /**
     *
     * @return float
     */
    public function getGrandTotal() {
        $totals = $this->getTotals();
        $total = 0;
        foreach ($totals['totals'] as $total) {
            if ($total['code'] == 'total') {
                $total = $total['value'];
                break;
            }
        }
        return $total;
    }

    /**
     * @param array $shippingAddress
     * @return bool
     */
    private function validateShippingAddress($shippingAddress = array()) {
        return !empty($shippingAddress['addressLine1']) &&
                !empty($shippingAddress['postalCode']) &&
                !empty($shippingAddress['city']) &&
                !empty($shippingAddress['country']);
    }
    private function formatAmount($value) {
        return $this->formatNumber($value, $this->session->data['currency'], '', false);
    }

    private function formatNumber($number, $currency, $value = '', $format = true) {
        $decimal_place = $this->currency->getDecimalPlace($currency);
        if (empty($decimal_place)) {
            $decimal_place = 2;
        }
        $amount = (float) $number;
        $amount = round($amount, (int) $decimal_place);
        return $amount;
    }
    /**
     * Prepare Coupon Item Array for Nets API
     *
     * @return array
     * @throws Exception
     */
    public function getCouponItemArray($total) {
        $this->load->language('extension/total/coupon', 'coupon');

        $coupon_info = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);
        $items = "";
        if ($coupon_info) {
            $discount_total = 0;
            $tax_rate_amount = 0;
            if (!$coupon_info['product']) {
                $sub_total = $this->cart->getSubTotal();
            } else {
                $sub_total = 0;

                foreach ($this->cart->getProducts() as $product) {
                    if (in_array($product['product_id'], $coupon_info['product'])) {
                        $sub_total += $product['total'];
                    }
                }
            }

            if ($coupon_info['type'] == 'F') {
                $coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
            }
            $taxrate = 0;
            foreach ($this->cart->getProducts() as $product) {
                $discount = 0;

                if (!$coupon_info['product']) {
                    $status = true;
                } else {
                    $status = in_array($product['product_id'], $coupon_info['product']);
                }

                if ($status) {
                    if ($coupon_info['type'] == 'F') {
                        $discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
                    } elseif ($coupon_info['type'] == 'P') {
                        $discount = $product['total'] / 100 * $coupon_info['discount'];
                    }
                    $discount = $this->formatPrice($discount);
                    if ($product['tax_class_id']) {
                        $tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);

                        foreach ($tax_rates as $tax_rate) {
                            if ($tax_rate['type'] == 'P') {
                                $tax_rate_amount += $tax_rate['amount'];
                                $taxrate = $tax_rate['rate'];
                            }
                        }
                    }
                }

                $discount_total += $discount;
            }

            if ($coupon_info['shipping'] && isset($this->session->data['shipping_method'])) {
                if (!empty($this->session->data['shipping_method']['tax_class_id'])) {
                    $tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);

                    foreach ($tax_rates as $tax_rate) {
                        if ($tax_rate['type'] == 'P') {
                            $tax_rate_amount += $tax_rate['amount'];
                            $taxrate = $tax_rate['rate'];
                        }
                    }
                }

                $discount_total += $this->session->data['shipping_method']['cost'];
            }

            // If discount greater than total
            if ($discount_total > $total['total']) {
                $discount_total = $total['total'];
            }

            if ($discount_total > 0) {
                $taxRate = 0;
                $quantity = 1;
                //get gross total by new formula (qty * netprice) * 1.25 (25=>taxrate)
                if ($coupon_info['type'] == 'F') {
                    $discount_total = $this->formatPrice($coupon_info['discount']);
                }
                $grossCouponPrice = $netPrice = $discount_total;
                if ($tax_rate_amount) {
                    $grossCouponPrice = ($quantity * $netPrice) + ($tax_rate_amount);
                }
                $taxAmount = $grossCouponPrice - $netPrice;
                $items = array(
                    'reference' => 'coupon',
                    'name' => sprintf($this->language->get('text_coupon'), $this->session->data['coupon']),
                    'quantity' => 1,
                    'unit' => 'units',
                    'unitPrice' => $this->getNetsIntValue($netPrice),
                    'taxRate' => $this->getNetsIntValue($taxrate),
                    'taxAmount' => -$this->getNetsIntValue($taxAmount),
                    'grossTotalAmount' => - $this->getNetsIntValue($grossCouponPrice),
                    'netTotalAmount' => -$this->getNetsIntValue($netPrice));
            }
        }
        return $items;
    }    
    /**
     * If there is any mismatch in total amount due to round function
     * Get difference amount(delta only in range 0 to 0.9) = Open cart total - Nets API total
     * @return array
     * @throws Exception
     */
    public function getDelta($totals, $itemsArray) {
        $delta = 0;
        //get opencart calculated total amount
        $opencartTotal = 0;
        foreach ($totals['totals'] as $totalArray) {
            if ($totalArray['code'] == 'total') {
                $opencartTotal += $totalArray['value'];
            }
        }
        //get Nets total amount = sum of all gross totals of items
        $itemsGrossPriceSum = 0;
        foreach ($itemsArray as $total) {
            $itemsGrossPriceSum += $total['grossTotalAmount'];
        }

        $delta = $actualDelta = $this->formatAmount($opencartTotal) - $this->formatAmount($itemsGrossPriceSum / 100);
        if ($delta < 0) {
            $delta = -($delta);
        }
        //get delta in range 0 to 0.9 only
        if ($delta > 0 && $delta <= 1) {
            return $actualDelta;
        }
        return $delta;
    }
    public function start() {
        if (isset($this->session->data['dibseasy']['paymentid'])) {
            $this->updateCart();
        }
    }
    /**
     * Update cart on Easy side:
     * https://tech.dibspayment.com/easy/api/rest/update-cart
     */
    public function updateCart() {
        $totals = $this->getTotals(false);
        $items = $this->getRequestObjectItems();
        $itemsGrossPriceSumma = $this->getItemGrossTotalSum($items);
        $requestData = array(
            'amount' => $this->getNetsIntValue($this->formatAmount($itemsGrossPriceSumma / 100)),
            //'amount' => round($this->currency->format($totals['total'], $this->session->data['currency'], '', false) * 100),
            'items' => $items,
            'shipping' => ['costSpecified' => true]
        );
        $paymentId = $this->session->data['dibseasy']['paymentid'];
        
        $result = $this->makeCurlRequest($this->getApiUrlPrefix() . $paymentId . '/orderitems', json_encode($requestData), 'PUT');
        //echo "<pre>s";print_r($result);die;
    }
    protected function getApiUrlPrefix() {
        $urlPrefix = '';
        if ($this->config->get('dibseasy_testmode') == 1) {
            $urlPrefix = 'https://test.api.dibspayment.eu/v1/payments/';
        } else {
            $urlPrefix = 'https://api.dibspayment.eu/v1/payments/';
        }
        return $urlPrefix;
    }

    public function getRequestObjectItems() {
        $this->load->model('checkout/order');
        $items = array();
        $productItemsArray = array();
        $productTaxDetailsArray = array();

        //To prepare product item array
        foreach ($this->cart->getProducts() as $product) {
            $taxRate = $taxAmount = 0;
            $qty = $product['quantity'];
            $unitPrice = $this->formatPrice($product['price']);
            $grossTotalAmount = $netPrice = $this->formatPrice($product['price'] * $qty);
            $rates = $this->cart->tax->getRates($netPrice, $product['tax_class_id']);

            if (!empty($product['tax_class_id'])) {
                $coupanRates = $rates;
                $coupanProductTaxArray = array_shift($coupanRates);
            }

            //get gross total by new formula (qty * netprice) * 1.25 (25=>taxrate)
            $productTaxDetailsArray = array_shift($rates);

            if (isset($productTaxDetailsArray['type'])) {
                $taxRate = $this->formatAmount((float) $productTaxDetailsArray['rate']);

                if ($productTaxDetailsArray['type'] == 'F') {
                    $taxAmount = $this->formatPrice((float) $productTaxDetailsArray['rate']);
                    $grossTotalAmount = $this->formatAmount($netPrice + ($qty * $taxAmount));
                } elseif ($productTaxDetailsArray['type'] == 'P') {
                    $taxAmount = 1 + ($this->formatAmount((float) $productTaxDetailsArray['rate']) / 100); // 1.25
                    $grossTotalAmount = $this->formatAmount($netPrice * ($taxAmount));
                }
            }
            $items[] = $productItemsArray[] = array(
                'reference' => $product['product_id'],
                'name' => str_replace(array('\'', '&'), '', $product['name']),
                'quantity' => $qty,
                'unit' => 'pcs',
                'unitPrice' => $this->getNetsIntValue($unitPrice),
                'taxRate' => $this->getNetsIntValue($taxRate),
                'taxAmount' => $this->getNetsIntValue($grossTotalAmount - $netPrice),
                'grossTotalAmount' => $this->getNetsIntValue($grossTotalAmount),
                'netTotalAmount' => $this->getNetsIntValue($netPrice));
        }

        //To prepare shipping item array
        if (!empty($this->session->data['shipping_method']['cost'])) {
            $taxRate = $taxAmount = 0;
            $quantity = 1;
            $grossShippingPrice = $shippingNetPrice = $this->formatPrice((float) $this->session->data['shipping_method']['cost']);
            $shippingRateDetails = $this->cart->tax->getRates($shippingNetPrice, $this->session->data['shipping_method']['tax_class_id']);

            $shippingRateArray = array_shift($shippingRateDetails);
            if (isset($shippingRateArray['type'])) {
                $taxRate = $this->formatAmount((float) $shippingRateArray['rate']);
                if ($shippingRateArray['type'] == 'F') {
                    $taxAmount = $this->formatPrice((float) $shippingRateArray['rate']);
                    $grossShippingPrice = $this->formatAmount(($quantity * $shippingNetPrice) + ($quantity * $taxAmount));
                } elseif ($shippingRateArray['type'] == 'P') {
                    $taxAmount = 1 + ($this->formatAmount((float) $shippingRateArray['rate']) / 100);
                    $grossShippingPrice = $this->formatAmount(($quantity * $shippingNetPrice) * ($taxAmount));
                }
            }
            $items[] = array(
                'reference' => 'Shipping',
                'name' => 'shipping',
                'quantity' => $quantity,
                'unit' => 'pcs',
                'unitPrice' => $this->getNetsIntValue($shippingNetPrice),
                'taxRate' => $this->getNetsIntValue($taxRate),
                'taxAmount' => $this->getNetsIntValue($grossShippingPrice - $shippingNetPrice),
                'grossTotalAmount' => $this->getNetsIntValue($grossShippingPrice),
                'netTotalAmount' => $this->getNetsIntValue($shippingNetPrice));
        }

        //To prepare discount item array
        $totals = $this->getTotals();
        if (isset($this->session->data['coupon'])) {
            $couponItemArr = $this->getCouponItemArray($totals);
            if (!empty($couponItemArr)) {
                $itemsArray = $items;
                $itemsArray[] = $couponItemArr;
                //delta (get only in range 0 to 0.9)= Open cart total amount - Nets total amount
                $delta = $this->getDelta($totals, $itemsArray);
                if ($delta) {
                    $couponGrossTotalAmount = -($couponItemArr['grossTotalAmount'] / 100) - ($delta);
                    $couponTaxAmount = $this->formatAmount((float) $couponGrossTotalAmount + ($couponItemArr['netTotalAmount'] / 100));

                    $couponItemArr['taxAmount'] = -$this->getNetsIntValue($couponTaxAmount);
                    $couponItemArr['grossTotalAmount'] = -$this->getNetsIntValue($couponGrossTotalAmount);
                }
                $items[] = $couponItemArr;
            }
        }

        //adjust delta for shipping
        $delta = $this->getDelta($totals, $items);
        if (!empty($delta) && !empty($this->session->data['shipping_method']['cost'])) {
            //e.g 0.01 delta for flat shipping (flat rate 5, tax rate 25%, product unit price 9.96) for currency Euro
            $shippingGrossTotalAmount = 0;
            foreach ($items as $key => $value) {
                if ($value['reference'] == 'Shipping') {
                    $shippingGrossTotalAmount = -($value['grossTotalAmount'] / 100) - ($delta);
                    $shippingTaxAmount = $this->formatAmount((float) $shippingGrossTotalAmount + ($value['netTotalAmount'] / 100));

                    if (isset($items[$key]['taxAmount'])) {
                        $items[$key]['taxAmount'] = $this->getNetsIntValue(-$shippingTaxAmount);
                    }
                    if (isset($items[$key]['grossTotalAmount'])) {
                        $items[$key]['grossTotalAmount'] = $this->getNetsIntValue(-$shippingGrossTotalAmount);
                    }
                }
            }
        }

        //adjust delta for product
        $delta = $this->getDelta($totals, $items);
        if ($delta && !empty($productItemsArray)) {
            //e.g 0.01 delta for product (unit price 9.96, tax rate 7.7%, no shipping and coupon) for currency Euro
            $productId = '';
            foreach ($productItemsArray as $productArr) {
                $productId = $productArr['reference'];
                $productGrossTotalAmount = -($productArr['grossTotalAmount'] / 100) - ($delta);
                $productTaxAmount = $this->formatAmount((float) $productGrossTotalAmount + ($productArr['netTotalAmount'] / 100));
            }
            foreach ($items as $key => $value) {
                if ($value['reference'] == $productId) {
                    if (isset($items[$key]['taxAmount']) && isset($productTaxAmount)) {
                        $items[$key]['taxAmount'] = $this->getNetsIntValue(-$productTaxAmount);
                    }
                    if (isset($items[$key]['grossTotalAmount']) && isset($productGrossTotalAmount)) {
                        $items[$key]['grossTotalAmount'] = $this->getNetsIntValue(-$productGrossTotalAmount);
                    }
                }
            }
        }

        $delta = $this->getDelta($totals, $items);
        if ($delta) {
            echo $error = "Error- Total Amount mismatch, The difference is " . $delta;
            $this->log->write($error);
            die;
        }

        return $items;
    }
     /**
     * Get available shipping methods based on shipping address
     *
     * @return array
     * @throws Exception
     */
    public function getShippingMethods() {
        $result = array();
        if (!$this->cart->hasShipping()) {
            return $result;
        }
        $this->load->language('checkout/checkout');
        if (isset($this->session->data['shipping_address'])) {
            // Shipping Methods
            $method_data = array();

            $this->load->model('extension/extension');

            $results = $this->model_extension_extension->getExtensions('shipping');

            
            foreach ($results as $result) {
                //if ($this->config->get('shipping_' . $result['code'] . '_status')) {
                    $this->load->model('extension/shipping/' . $result['code']);

                    $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

                    if (!empty($quote['quote'])) {
                        $method_data[$result['code']] = array(
                            'title' => $quote['title'],
                            'quote' => $quote['quote'],
                            'sort_order' => $quote['sort_order'],
                            'error' => $quote['error']
                        );
                    }
                //}
            }

            $sort_order = array();

            foreach ($method_data as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $method_data);

            $result = $method_data;

            if ($method_data) {
                $method = current($method_data);
                $quote = $method['quote'];
                $current = current($quote);
                $code = $current['code'];

                // Set the first available shipping method
                if (!isset($this->session->data['shipping_method'])) {
                    $this->setShippingMethod($code);
                }

                // If shipping from session is not in shippings list 
                // set the first available shipping method 
                if (isset($this->session->data['shipping_method'])) {

                    $sessinMethodIsInMethods = false;
                    foreach ($method_data as $md) {
                        $quote = $md['quote'];
                        foreach ($quote as $current) {
                            $cd = $current['code'];
                            if ($cd == $this->session->data['shipping_method']['code']) {
                                $sessinMethodIsInMethods = true;
                            }
                        }
                    }
                    if (!$sessinMethodIsInMethods) {
                        $this->setShippingMethod($code);
                    }
                }
            }
            if ($this->cart->hasShipping() && !$result) {
                throw new Exception('No shipping methods available for current address');
            }
        }
        //echo "<pre>";print($result);die;
        return $result;
    }
    /**
     * Set shipping method based on shipping code
     * 
     * @param type $shippingCode
     */
    public function setShippingMethod($shippingCode = null) {
        
        if ($this->validateCart() && $this->cart->hasShipping()) {
            $json['shipping_methods'] = array();
            $this->load->model('extension/extension');
            $shipping = explode('.', $shippingCode);
            // Shipping Methods
            $method_data = array();
            $this->load->model('extension/extension');
            $results = $this->model_extension_extension->getExtensions('shipping');
                
            foreach ($results as $result) {
                //if ($this->config->get('shipping_' . $result['code'] . '_status')) {die('k');
                    $this->load->model('extension/shipping/' . $result['code']);

                    $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

                    if ($quote) {
                        $method_data[$result['code']] = array(
                            'title' => $quote['title'],
                            'quote' => $quote['quote'],
                            'sort_order' => $quote['sort_order'],
                            'error' => $quote['error']
                        );
                    }
                //}
            }
            $sort_order = array();
            foreach ($method_data as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }
            array_multisort($sort_order, SORT_ASC, $method_data);
            $this->session->data['shipping_methods'] = $method_data;
            if ($this->session->data['shipping_methods'] && isset($shipping[0]) && isset($shipping[1])) {
                $this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
                $this->updateCart();
            }
        }
    }
    //get total amount = sum of all gross totals of items
    public  function getItemGrossTotalSum($items){
        $itemsGrossPriceSumma = 0;
        foreach ($items as $total) {
            $itemsGrossPriceSumma += $total['grossTotalAmount'];
        }
        return $itemsGrossPriceSumma;
    }
    
}
