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
            $paymentId = $this->extractPaymentId();
            $response = $this->model_extension_payment_dibseasy->getTransactionInfo($paymentId);

            if (isset($response->payment->paymentDetails->paymentType) && $response->payment->paymentDetails->paymentType) {

                $this->load->model('checkout/dibseasy_order');
                $this->model_checkout_dibseasy_order->updateOrder($this->session->data['order_id']);

                $_SESSION['dibseasy_transaction'] = $paymentId;
                if (isset($response->payment->consumer->company->name)) {
                    $firstName = (isset($response->payment->consumer->company->contactDetails->firstName)) ? $response->payment->consumer->company->contactDetails->firstName : '';
                    $lastName = (isset($response->payment->consumer->company->contactDetails->lastName)) ? $response->payment->consumer->company->contactDetails->lastName : '';
                    $email = (isset($response->payment->consumer->company->contactDetails->email)) ? $response->payment->consumer->company->contactDetails->email : '';
                    $phoneNumber = (isset($response->payment->consumer->company->contactDetails->phoneNumber->number)) ? $response->payment->consumer->company->contactDetails->phoneNumber->prefix .
                            $response->payment->consumer->company->contactDetails->phoneNumber->number : '';
                } else {
                    $firstName = (isset($response->payment->consumer->privatePerson->firstName)) ? $response->payment->consumer->privatePerson->firstName : '';
                    $lastName = (isset($response->payment->consumer->privatePerson->lastName)) ? $response->payment->consumer->privatePerson->lastName : '';
                    $email = (isset($response->payment->consumer->privatePerson->email)) ? $response->payment->consumer->privatePerson->email : '';
                    $phoneNumber = (isset($response->payment->consumer->privatePerson->phoneNumber->number)) ? $response->payment->consumer->privatePerson->phoneNumber->prefix .
                            $response->payment->consumer->privatePerson->phoneNumber->number : '';
                }

                if ($this->config->get('dibseasy_language') == 'sv-SE') {
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

                $cardLast4digits = null;
                if (isset($response->payment->paymentDetails->cardDetails->maskedPan) && $response->payment->paymentDetails->cardDetails->maskedPan) {
                    $cardLast4digits = substr($response->payment->paymentDetails->cardDetails->maskedPan, -4);
                }
                $transactDetails = "$transactionId: <b>{$response->payment->paymentId}</b> <br>"
                        . "$paymentType:   <b>{$response->payment->paymentDetails->paymentType}</b> <br>"
                        . "$paymentMethod: <b>{$response->payment->paymentDetails->paymentMethod}</b> <br>";
                if ($cardLast4digits) {
                    $transactDetails .= "$cardNumberPostfix: <b>$cardLast4digits</b>";
                }

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
                $this->model_checkout_dibseasy_order->addOrderHistory($this->session->data['order_id'], $this->config->get('dibseasy_order_status_id'), $transactDetails, true);
                //save payment id into order table.custom_field
                $this->db->query("UPDATE " . DB_PREFIX . "order SET custom_field = '" . $this->session->data['dibseasy']['paymentid'] . "' WHERE order_id = '" . $this->session->data['order_id'] . "'");
                unset($this->session->data['dibseasy']['paymentid']);
                
                $query = $this->db->query("SELECT count(*) as count FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int) $this->session->data['order_id'] . "'");
                if (isset($query->row['count']) && $query->row['count'] > 1) {
                    $this->db->query("DELETE FROM `" . DB_PREFIX . "order_history` WHERE order_id = '" . (int) (int) $this->session->data['order_id'] . "' LIMIT 1;");
                }
                $this->response->redirect($this->url->link('checkout/dibseasy_success', '', true));
            } else {
                $this->response->redirect($this->url->link('checkout/dibseasy', '', true));
            }
        } else {
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }
    }

    /**
     * confirm payment it case of hosted solution
     *
     */
    public function confirmHosted() {
        if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'dibseasy') {

            $this->load->model('extension/payment/dibseasy');
            $paymentId = $this->extractPaymentId();
            $response = $this->model_extension_payment_dibseasy->getTransactionInfo($paymentId);

            if (isset($response->payment->paymentDetails->paymentType) && $response->payment->paymentDetails->paymentType) {

                $this->load->model('checkout/dibseasy_order');
                $this->model_checkout_dibseasy_order->updateOrder($this->session->data['order_id']);

                $_SESSION['dibseasy_transaction'] = $paymentId;
                if ($this->config->get('dibseasy_language') == 'sv-SE') {
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
                $cardLast4digits = null;
                if (isset($response->payment->paymentDetails->cardDetails->maskedPan) && $response->payment->paymentDetails->cardDetails->maskedPan) {
                    $cardLast4digits = substr($response->payment->paymentDetails->cardDetails->maskedPan, -4);
                }
                $transactDetails = "$transactionId: <b>{$response->payment->paymentId}</b> <br>"
                        . "$paymentType:   <b>{$response->payment->paymentDetails->paymentType}</b> <br>"
                        . "$paymentMethod: <b>{$response->payment->paymentDetails->paymentMethod}</b> <br>";
                if ($cardLast4digits) {
                    $transactDetails .= "$cardNumberPostfix: <b>$cardLast4digits</b>";
                }
                $this->load->model('checkout/dibseasy_order');

                $orderId = $this->session->data['order_id'];

                $orderStatus = $this->config->get('dibseasy_order_status_id');

                $this->model_checkout_dibseasy_order->addOrderHistory($orderId, $orderStatus, $transactDetails, true);
                //save payment id into order table.custom_field
                $this->db->query("UPDATE " . DB_PREFIX . "order SET custom_field = '" . $this->session->data['dibseasy']['paymentid'] . "' WHERE order_id = '" . $this->session->data['order_id'] . "'");
                unset($this->session->data['dibseasy']['paymentid']);
                $this->response->redirect($this->url->link('checkout/dibseasy_success', '', true));
            } else {
                $this->response->redirect($this->url->link('checkout/checkout', '', true));
            }
        }
    }

    public function redirect() {
        $this->load->model('extension/payment/dibseasy');

        $this->load->language('extension/payment/dibseasy');

        $paymentid = $this->model_extension_payment_dibseasy->initCheckout();
        if (!empty($paymentid)) {
            $transaction = $this->model_extension_payment_dibseasy->getTransactionInfo($paymentid);
            $json['redirect'] = $transaction->payment->checkout->url . '&language=' . $this->config->get('payment_dibseasy_language');
        } else {
            $this->session->data['error'] = $this->language->get('dibseasy_checkout_redirect_error');
            $json['error'] = 1;
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function extractPaymentId() {
        $result = null;
        if (isset($this->request->get['paymentid'])) {
            $result = $this->request->get['paymentid'];
        }
        if (isset($this->request->get['paymentId'])) {
            $result = $this->request->get['paymentId'];
        }
        return $result;
    }

}
