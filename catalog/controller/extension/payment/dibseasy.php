<?php
class ControllerExtensionPaymentDibseasy extends Controller {
	public function index() {
          	$data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['continue'] = $this->url->link('checkout/success');
   		return $this->load->view('extension/payment/dibseasy', $data);
	}
        protected $logger;
        
        public function __construct($registry) {
            parent::__construct($registry);
            $this->logger = new Log('dibs.easy.log');
         }

	public function confirm() {
               if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'dibseasy') {
                        $this->load->model('extension/payment/dibseasy');
                        $response = $this->model_extension_payment_dibseasy->getTransactionInfo($_GET['paymentId']);
                        if(isset($response->payment->consumer->company->name)) {
                            $firstName = $response->payment->consumer->company->contactDetails->firstName;
                            $lastName = $response->payment->consumer->company->contactDetails->lastName;
                            $email =  $response->payment->consumer->company->contactDetails->email;
                            $phoneNumber = $response->payment->consumer->company->contactDetails->phoneNumber->prefix .
                                           $response->payment->consumer->company->contactDetails->phoneNumber->number;
                        }else {
                            $firstName = $response->payment->consumer->privatePerson->firstName;
                            $lastName = $response->payment->consumer->privatePerson->lastName;
                            $email = $response->payment->consumer->privatePerson->email;
                            $phoneNumber = $response->payment->consumer->privatePerson->phoneNumber->prefix .
                                           $response->payment->consumer->privatePerson->phoneNumber->number;
                       }
                        $maskedCardNumber = $response->payment->paymentDetails->cardDetails->maskedPan;
                        $cardPostfix = substr($maskedCardNumber, -4);
                        if($response->payment->paymentDetails->paymentType) {
                            $_SESSION['dibseasy_transaction'] = $_GET['paymentId'];
                            $res = $this->model_extension_payment_dibseasy->getCountryByIsoCode3($response->payment->consumer->shippingAddress->country);
                            $country = $res['name'];
                            $country_id = $res['country_id'];
                            $orderUpdate = array('firstname' => $firstName,
                                    'lastname' => $lastName,
                                    'email' => $email,
                                    'telephone' => $phoneNumber,
                                    'shipping_lastname' => $lastName,
                                    'shipping_firstname' => $firstName,
                                    'shipping_address_1' => $response->payment->consumer->shippingAddress->addressLine1,
                                    'shipping_city' => $response->payment->consumer->shippingAddress->city,
                                    'shipping_country' => $country,
                                    'shipping_postcode' => $response->payment->consumer->shippingAddress->postalCode,
                                    'shipping_country_id' => $country_id,
                                    'payment_lastname' => $lastName,
                                    'payment_firstname' => $firstName,
                                    'payment_address_1' => $response->payment->consumer->shippingAddress->addressLine1,
                                    'payment_city' => $response->payment->consumer->shippingAddress->city,
                                    'payment_country' => $country,
                                    'payment_postcode' => $response->payment->consumer->shippingAddress->postalCode,
                                    'payment_country_id' => $country_id
                                    );
                            $orderid = substr($response->payment->orderDetails->reference, 4);
                            $this->model_extension_payment_dibseasy->setAddresses($orderid, $orderUpdate);
                            $this->load->model('checkout/dibseasy_order');
                            if($this->config->get('dibseasy_language') == 'sv-SE') {
                                $paymentType = 'Betalnings typ';
                                $paymentMethod = 'Betalningsmetod';
                                $transactionId = 'Betalnings ID';
                                $cardNumberPostfix = 'Kreditkort de sista 4 siffrorna';
                            } else {
                                $paymentType = 'Payment type';
                                $paymentMethod = 'Payment Method';
                                $transactionId = 'Payment ID';
                                $cardNumberPostfix = 'Credit card last 4 digits';
                            }
                            $transactDetails = "$transactionId: <b>{$response->payment->paymentId}</b> <br>"
                                             . "$paymentType:   <b>{$response->payment->paymentDetails->paymentType}</b> <br>"
                                             . "$paymentMethod: <b>{$response->payment->paymentDetails->paymentMethod}</b> <br>"
                                             . "$cardNumberPostfix: <b>$cardPostfix</b>";
                            $this->model_checkout_dibseasy_order->addOrderHistory($this->session->data['order_id'], $this->config->get('dibseasy_order_status_id'), $transactDetails, true);
                            $query = $this->db->query("SELECT count(*) as count FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$this->session->data['order_id'] . "'");
                            if( isset($query->row['count']) && $query->row['count'] > 1 ) {
                                 $this->db->query("DELETE FROM `" . DB_PREFIX . "order_history` WHERE order_id = '" . (int)(int)$this->session->data['order_id'] . "' LIMIT 1;");
                            }
                            $this->response->redirect($this->url->link('checkout/dibseasy_success', '', true));
                        } else {
                            $this->logger->write('-===============Error during finishiong order==============-----');
                            $this->logger->write('Transactionid: ' . $_GET['paymentId']);
                            $this->logger->write('Order was not registered in Opencart');
                            $this->logger->write('Orderid: ' . $this->session->data['order_id']);
                            $this->logger->write('You can fing order details in DB table: `' . DB_PREFIX . 'order`');
                            $this->logger->write('================================================================');
                            $this->response->redirect($this->url->link('checkout/dibseasy', '', true));
                        }
		} else {
                    $this->response->redirect($this->url->link('checkout/dibseasy', '', true));
                }
	}

    public function redirect() {
        $this->load->model('extension/payment/dibseasy');
        $this->load->language('checkout/dibseasy');
        $paymentid = $this->model_extension_payment_dibseasy->initCheckout();
        if(!is_null($paymentid)) {
            $transaction = $this->model_extension_payment_dibseasy->getTransactionInfo($paymentid);
            $json['redirect'] = $transaction->payment->checkout->url . '&language=' . $this->config->get('payment_dibseasy_language');
        } else {
            $this->session->data['error'] = $this->language->get('dibseasy_checkout_redirect_error');
            $json['error'] = 1;
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}