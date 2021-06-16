<?php

class ControllerExtensionPaymentDibseasy extends Controller {

    private $error = array();

    public function __construct($registry) {
        parent::__construct($registry);
        //create updatePaymentStatus event only once onclick of Easy Checkout setting page
        $order_query = $this->db->query("SELECT event_id FROM `" . DB_PREFIX . "event`  WHERE code = 'update_payment_status'");
        if (empty($order_query->num_rows)) {
            $this->addEvent('update_payment_status', 'admin/controller/sale/order/info/before', 'extension/payment/dibseasy/updatePaymentStatus');
        }
        //add easy payment statuses in order_status table
        $order_query = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order_status`  WHERE name = 'Reserved'");
        if (empty($order_query->num_rows)) {
            $statusArr = array("Partial Charged", "Partial Refunded", "Refund Pending", "Reserved", "Charged");
            foreach ($statusArr as $status) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET language_id = 1, name= '" . $status . "'");
            }
            $this->clearOrderStatusCache();
        }
    }

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
        $data['entry_checkout_type'] = $this->language->get('entry_checkout_type');
        $data['text_checkout_type_hosted'] = $this->language->get('text_checkout_type_hosted');
        $data['text_checkout_type_embedded'] = $this->language->get('text_checkout_type_embedded');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_allowed_customer_type'] = $this->language->get('entry_allowed_customer_type');
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
        $data['entry_testmode_description'] = $this->language->get('entry_testmode_description');
        $data['entry_debug_description'] = $this->language->get('entry_debug_description');
        $data['entry_language'] = $this->language->get('entry_language');
        $data['entry_dibseasy_terms_and_conditions'] = $this->language->get('entry_dibseasy_terms_and_conditions');

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

        if (isset($this->request->post['dibseasy_terms_and_conditions'])) {
            $data['dibseasy_terms_and_conditions'] = $this->request->post['dibseasy_terms_and_conditions'];
        } else {
            $data['dibseasy_terms_and_conditions'] = $this->config->get('dibseasy_terms_and_conditions');
        }
        //merchant_term_and_conditions
        $data['entry_dibseasy_merchant_terms_and_conditions'] = $this->language->get('entry_dibseasy_merchant_terms_and_conditions');

        $data['error_easy_merchant_term_and_conditions'] = '';
        if (isset($this->error['easy_merchant_term_and_conditions'])) {
            $data['error_easy_merchant_term_and_conditions'] = $this->error['easy_merchant_term_and_conditions'];
        }

        if (isset($this->request->post['dibseasy_merchant_terms_and_conditions'])) {
            $data['dibseasy_merchant_terms_and_conditions'] = $this->request->post['dibseasy_merchant_terms_and_conditions'];
        } else {
            $data['dibseasy_merchant_terms_and_conditions'] = $this->config->get('dibseasy_merchant_terms_and_conditions');
        }
        //Auto Capture
        $data['entry_autocapture'] = $this->language->get('entry_autocapture');
        if (isset($this->request->post['dibseasy_autocapture'])) {
            $data['dibseasy_autocapture'] = $this->request->post['dibseasy_autocapture'];
        } else {
            $data['dibseasy_autocapture'] = $this->config->get('dibseasy_autocapture');
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

        if (isset($this->request->post['dibseasy_checkout_type'])) {
            $data['dibseasy_checkout_type'] = $this->request->post['dibseasy_checkout_type'];
        } else {
            $data['dibseasy_checkout_type'] = $this->config->get('dibseasy_checkout_type');
        }

        $data['error_merchant'] = '';
        if (isset($this->error['error_merchant'])) {
            $data['error_merchant'] = $this->error['error_merchant'];
        }

        $data['free_shipping_disabled'] = '';
        if (isset($this->error['free_shipping_disabled'])) {
            $data['free_shipping_disabled'] = $this->error['free_shipping_disabled'];
        }

        $data['checkout_key_test'] = '';
        if (isset($this->error['checkout_key_test'])) {
            $data['checkout_key_test'] = $this->error['checkout_key_test'];
        }

        $data['checkout_key_live'] = '';
        if (isset($this->error['checkout_key_live'])) {
            $data['checkout_key_live'] = $this->error['checkout_key_live'];
        }

        $data['dibseasy_livekey_error'] = '';
        if (isset($this->error['dibseasy_livekey'])) {
            $data['dibseasy_livekey_error'] = $this->error['dibseasy_livekey'];
        }

        $data['dibseasy_testkey_error'] = '';
        if (isset($this->error['dibseasy_testkey'])) {
            $data['dibseasy_testkey_error'] = $this->error['dibseasy_testkey'];
        }

        $data['text_english'] = $this->language->get('text_english');
        $data['text_swedish'] = $this->language->get('text_swedish');
        $data['text_norwegian'] = $this->language->get('text_norwegian');
        $data['text_danish'] = $this->language->get('text_danish');

        $data['text_b2c'] = $this->language->get('text_b2c');
        $data['text_b2b'] = $this->language->get('text_b2b');
        $data['text_b2c_b2b_b2c'] = $this->language->get('text_b2c_b2b_b2c');
        $data['text_b2b_b2c_b2b'] = $this->language->get('text_b2b_b2c_b2b');

        $data['english_selected'] = '';
        $data['swedish_selected'] = '';
        $data['norwegian_select'] = '';
        $data['danish_select'] = '';

        $data['b2b_selected'] = '';
        $data['b2c_selected'] = '';
        $data['b2c_b2b_b2c_selected'] = '';
        $data['b2b_b2c_b2b_selected'] = '';

        if ($this->config->get('dibseasy_language') == 'sv-SE') {
            $data['swedish_selected'] = 'selected="selected"';
        }

        if ($this->config->get('dibseasy_language') == 'en-GB') {
            $data['english_selected'] = 'selected="selected"';
        }

        if ($this->config->get('dibseasy_language') == 'nb-NO') {
            $data['norwegian_select'] = 'selected="selected"';
        }

        if ($this->config->get('dibseasy_language') == 'da-DK') {
            $data['danish_select'] = 'selected="selected"';
        }

        if ($this->config->get('dibseasy_allowed_customer_type') == 'b2c') {
            $data['b2c_selected'] = 'selected="selected"';
        }
        if ($this->config->get('dibseasy_allowed_customer_type') == 'b2b') {
            $data['b2b_selected'] = 'selected="selected"';
        }
        if ($this->config->get('dibseasy_allowed_customer_type') == 'b2c_b2b_b2c') {
            $data['b2c_b2b_b2c_selected'] = 'selected="selected"';
        }
        if ($this->config->get('dibseasy_allowed_customer_type') == 'b2b_b2c_b2b') {
            $data['b2b_b2c_b2b_selected'] = 'selected="selected"';
        }

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

        if (isset($this->request->post['dibseasy_shipping_method'])) {
            if ($this->request->post['dibseasy_shipping_method'] == 'free') {
                $this->load->model('setting/setting');
                $settings = $this->model_setting_setting->getSetting('free');
                if (!$settings) {
                    $this->error['free_shipping_disabled'] = $this->language->get('free_shipping_disabled');
                } else {
                    if (isset($settings['free_status']) && $settings['free_status'] != 1) {
                        $this->error['free_shipping_disabled'] = $this->language->get('free_shipping_disabled');
                    }
                }
            }
        }

        if (isset($this->request->post['dibseasy_livekey']) && strlen(trim($this->request->post['dibseasy_livekey'])) == 0) {
            $this->error['dibseasy_livekey'] = $this->language->get('entry_dibseasy_livekey_error');
        }

        if (isset($this->request->post['dibseasy_testkey']) && strlen(trim($this->request->post['dibseasy_testkey'])) == 0) {
            $this->error['dibseasy_testkey'] = $this->language->get('entry_dibseasy_testkey_error');
        }

        if (isset($this->request->post['dibseasy_merchant_terms_and_conditions']) && !$this->request->post['dibseasy_merchant_terms_and_conditions']) {
            $this->error['easy_merchant_term_and_conditions'] = $this->language->get('entry_merchant_term_and_conditions_error');
        }

        return !$this->error ? true : false;
    }

    //update payment status on order view page based on API get request
    public function updatePaymentStatus() {

        if ($this->config->get('dibseasy_testmode')) {
            $secretKey = $this->config->get('dibseasy_testkey');
            $apiUrl = "https://test.api.dibspayment.eu/v1/payments/";
        } else {
            $secretKey = $this->config->get('dibseasy_livekey');
            $apiUrl = 'https://api.dibspayment.eu/v1/payments/';
        }
        $secretKeyArr = explode("-", $secretKey);
        if (isset($secretKeyArr['3'])) {
            $secretKey = $secretKeyArr['3'];
        }

        if (isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];
            $order_query = $this->db->query("SELECT custom_field FROM `" . DB_PREFIX . "order`  WHERE order_id = '" . (int) $order_id . "'");
            if ($order_query->num_rows) {
                //get generated ids of easy payment statuses
                $netsOrderStatuses = array();
                $query = $this->db->query("SELECT order_status_id,name FROM `" . DB_PREFIX . "order_status`  WHERE language_id = 1 group by name");
                if (!empty($query->num_rows)) {
                    foreach ($query->rows as $key => $values) {
                        $netsOrderStatuses[$values['name']] = $values['order_status_id'];
                    }
                }
                //get payment responce
                $payId = $order_query->row['custom_field'];
                $ch = curl_init($apiUrl . $payId);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: " . $secretKey) // secret-key
                );
                $response = json_decode(curl_exec($ch));

                $chargeid = $pending = $refunded = $charged = $reserved = $cancelled = $paymentStatus = $dbPayStatus = '';
                if (isset($response->payment->summary->cancelledAmount)) {
                    $cancelled = $response->payment->summary->cancelledAmount;
                }
                if (isset($response->payment->summary->reservedAmount)) {
                    $reserved = $response->payment->summary->reservedAmount;
                }
                if (isset($response->payment->summary->chargedAmount)) {
                    $charged = $response->payment->summary->chargedAmount;
                }
                if (isset($response->payment->summary->refundedAmount)) {
                    $refunded = $response->payment->summary->refundedAmount;
                }
                if (isset($response->payment->refunds[0]->state)) {
                    $pending = $response->payment->refunds[0]->state == "Pending";
                }
                if (isset($response->payment->charges[0]->chargeId)) {
                    $chargeid = $response->payment->charges[0]->chargeId;
                }


                if ($reserved) {
                    if ($cancelled) {
                        $paymentStatus = $netsOrderStatuses["Canceled"]; //7 cancelled
                    } else if ($charged) {
                        if ($reserved != $charged) {
                            $paymentStatus = $netsOrderStatuses["Partial Charged"]; // Partial Charged
                        } else if ($refunded) {
                            if ($reserved != $refunded) {
                                $paymentStatus = $netsOrderStatuses["Partial Refunded"]; // Partial Refunded
                            } else {
                                $paymentStatus = $netsOrderStatuses["Refunded"]; //11 Refunded
                            }
                        } else if ($pending) {
                            $paymentStatus = $netsOrderStatuses["Refund Pending"]; //Refund Pending
                        } else {
                            $paymentStatus = $netsOrderStatuses["Charged"]; // Charged
                        }
                    } else if ($pending) {
                        $paymentStatus = $netsOrderStatuses["Refund Pending"]; //Refund Pending
                    } else {
                        $paymentStatus = $netsOrderStatuses["Reserved"]; // Reserved
                    }
                } else {
                    $paymentStatus = $netsOrderStatuses["Failed"]; // 10 Failed
                }

                //update payment status of charged payment  
                $query = $this->db->query("SELECT order_id FROM `" . DB_PREFIX . "order`  WHERE order_status_id = $paymentStatus AND order_id = '" . $_GET['order_id'] . "' ");
                if (isset($paymentStatus) && empty($query->num_rows) && !empty($response)) {
                    $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = $paymentStatus where order_id = '" . $_GET['order_id'] . "' ");
                }
            }
        }
    }

    public function addEvent($code, $trigger, $action, $status = 1, $sort_order = 0) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `code` = '" . $this->db->escape($code) . "', `trigger` = '" . $this->db->escape($trigger) . "', `action` = '" . $this->db->escape($action) . "', `status` = '" . (int) $status . "'");

        return $this->db->getLastId();
    }

    public function clearOrderStatusCache() {
        $files = array();
        // Make path into an array
        $path = array(DIR_CACHE . '*');
        // While the path array is still populated keep looping through
        while (count($path) != 0) {
            $next = array_shift($path);
            foreach (glob($next) as $file) {
                // If directory add to path array
                if (is_dir($file)) {
                    $path[] = $file . '/*';
                }
                // Add the file to the files to be deleted array
                $files[] = $file;
            }
        }
        // Reverse sort the file array
        rsort($files);
        // Clear all modification files
        foreach ($files as $file) {
            if ($file != DIR_CACHE . 'index.html') {
                // If file just delete
                if (is_file($file) && strpos($file, 'order_status') !== false) {
                    unlink($file);
                    // If directory use the remove directory function
                }
            }
        }
    }

}
