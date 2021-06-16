<?php

class ModelCheckoutDibseasyOrder extends Model {

    public function addOrder($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', store_id = '" . (int) $data['store_id'] . "', store_name = '" . $this->db->escape($data['store_name']) . "', store_url = '" . $this->db->escape($data['store_url']) . "', customer_id = '" . (int) $data['customer_id'] . "', customer_group_id = '" . (int) $data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', payment_company = '" . $this->db->escape($data['payment_company']) . "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', payment_city = '" . $this->db->escape($data['payment_city']) . "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', payment_country = '" . $this->db->escape($data['payment_country']) . "', payment_country_id = '" . (int) $data['payment_country_id'] . "', payment_zone = '" . $this->db->escape($data['payment_zone']) . "', payment_zone_id = '" . (int) $data['payment_zone_id'] . "', payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "', payment_custom_field = '" . $this->db->escape(isset($data['payment_custom_field']) ? json_encode($data['payment_custom_field']) : '') . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', shipping_company = '" . $this->db->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($data['shipping_country']) . "', shipping_country_id = '" . (int) $data['shipping_country_id'] . "', shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "', shipping_zone_id = '" . (int) $data['shipping_zone_id'] . "', shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', shipping_custom_field = '" . $this->db->escape(isset($data['shipping_custom_field']) ? json_encode($data['shipping_custom_field']) : '') . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', comment = '" . $this->db->escape($data['comment']) . "', total = '" . (float) $data['total'] . "', affiliate_id = '" . (int) $data['affiliate_id'] . "', commission = '" . (float) $data['commission'] . "', marketing_id = '" . (int) $data['marketing_id'] . "', tracking = '" . $this->db->escape($data['tracking']) . "', language_id = '" . (int) $data['language_id'] . "', currency_id = '" . (int) $data['currency_id'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', currency_value = '" . (float) $data['currency_value'] . "', ip = '" . $this->db->escape($data['ip']) . "', forwarded_ip = '" . $this->db->escape($data['forwarded_ip']) . "', user_agent = '" . $this->db->escape($data['user_agent']) . "', accept_language = '" . $this->db->escape($data['accept_language']) . "', date_added = NOW(), date_modified = NOW()");

        $order_id = $this->db->getLastId();

        // Products
        if (isset($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int) $order_id . "', product_id = '" . (int) $product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int) $product['quantity'] . "', price = '" . (float) $product['price'] . "', total = '" . (float) $product['total'] . "', tax = '" . (float) $product['tax'] . "', reward = '" . (int) $product['reward'] . "'");

                $order_product_id = $this->db->getLastId();

                foreach ($product['option'] as $option) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int) $order_id . "', order_product_id = '" . (int) $order_product_id . "', product_option_id = '" . (int) $option['product_option_id'] . "', product_option_value_id = '" . (int) $option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
                }
            }
        }

        // Gift Voucher
        $this->load->model('extension/total/voucher');

        // Vouchers
        if (isset($data['vouchers'])) {
            foreach ($data['vouchers'] as $voucher) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int) $order_id . "', description = '" . $this->db->escape($voucher['description']) . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int) $voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float) $voucher['amount'] . "'");

                $order_voucher_id = $this->db->getLastId();

                $voucher_id = $this->model_extension_total_voucher->addVoucher($order_id, $voucher);

                $this->db->query("UPDATE " . DB_PREFIX . "order_voucher SET voucher_id = '" . (int) $voucher_id . "' WHERE order_voucher_id = '" . (int) $order_voucher_id . "'");
            }
        }

        // Totals
        if (isset($data['totals'])) {
            foreach ($data['totals'] as $total) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int) $order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float) $total['value'] . "', sort_order = '" . (int) $total['sort_order'] . "'");
            }
        }

        return $order_id;
    }

    public function editOrder($order_id, $data) {
        // Void the order first
        $this->addOrderHistory($order_id, 0);

        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', store_id = '" . (int) $data['store_id'] . "', store_name = '" . $this->db->escape($data['store_name']) . "', store_url = '" . $this->db->escape($data['store_url']) . "', customer_id = '" . (int) $data['customer_id'] . "', customer_group_id = '" . (int) $data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', custom_field = '" . $this->db->escape(json_encode($data['custom_field'])) . "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', payment_company = '" . $this->db->escape($data['payment_company']) . "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', payment_city = '" . $this->db->escape($data['payment_city']) . "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', payment_country = '" . $this->db->escape($data['payment_country']) . "', payment_country_id = '" . (int) $data['payment_country_id'] . "', payment_zone = '" . $this->db->escape($data['payment_zone']) . "', payment_zone_id = '" . (int) $data['payment_zone_id'] . "', payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "', payment_custom_field = '" . $this->db->escape(json_encode($data['payment_custom_field'])) . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', shipping_company = '" . $this->db->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($data['shipping_country']) . "', shipping_country_id = '" . (int) $data['shipping_country_id'] . "', shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "', shipping_zone_id = '" . (int) $data['shipping_zone_id'] . "', shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', shipping_custom_field = '" . $this->db->escape(json_encode($data['shipping_custom_field'])) . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', comment = '" . $this->db->escape($data['comment']) . "', total = '" . (float) $data['total'] . "', affiliate_id = '" . (int) $data['affiliate_id'] . "', commission = '" . (float) $data['commission'] . "', date_modified = NOW() WHERE order_id = '" . (int) $order_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int) $order_id . "'");

        // Products
        if (isset($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int) $order_id . "', product_id = '" . (int) $product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int) $product['quantity'] . "', price = '" . (float) $product['price'] . "', total = '" . (float) $product['total'] . "', tax = '" . (float) $product['tax'] . "', reward = '" . (int) $product['reward'] . "'");

                $order_product_id = $this->db->getLastId();

                foreach ($product['option'] as $option) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int) $order_id . "', order_product_id = '" . (int) $order_product_id . "', product_option_id = '" . (int) $option['product_option_id'] . "', product_option_value_id = '" . (int) $option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
                }
            }
        }

        // Gift Voucher
        $this->load->model('extension/total/voucher');

        $this->model_extension_total_voucher->disableVoucher($order_id);

        // Vouchers
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int) $order_id . "'");

        if (isset($data['vouchers'])) {
            foreach ($data['vouchers'] as $voucher) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int) $order_id . "', description = '" . $this->db->escape($voucher['description']) . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int) $voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float) $voucher['amount'] . "'");

                $order_voucher_id = $this->db->getLastId();

                $voucher_id = $this->model_extension_total_voucher->addVoucher($order_id, $voucher);

                $this->db->query("UPDATE " . DB_PREFIX . "order_voucher SET voucher_id = '" . (int) $voucher_id . "' WHERE order_voucher_id = '" . (int) $order_voucher_id . "'");
            }
        }

        // Totals
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int) $order_id . "'");

        if (isset($data['totals'])) {
            foreach ($data['totals'] as $total) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int) $order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float) $total['value'] . "', sort_order = '" . (int) $total['sort_order'] . "'");
            }
        }
    }

    public function deleteOrder($order_id) {
        // Void the order first
        $this->addOrderHistory($order_id, 0);

        $this->db->query("DELETE FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int) $order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int) $order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_option` WHERE order_id = '" . (int) $order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int) $order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int) $order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_history` WHERE order_id = '" . (int) $order_id . "'");
        $this->db->query("DELETE `or`, ort FROM `" . DB_PREFIX . "order_recurring` `or`, `" . DB_PREFIX . "order_recurring_transaction` `ort` WHERE order_id = '" . (int) $order_id . "' AND ort.order_recurring_id = `or`.order_recurring_id");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "affiliate_transaction` WHERE order_id = '" . (int) $order_id . "'");

        // Gift Voucher
        $this->load->model('extension/total/voucher');

        $this->model_extension_total_voucher->disableVoucher($order_id);
    }

    public function getOrder($order_id) {
        $order_query = $this->db->query("SELECT *, (SELECT os.name FROM `" . DB_PREFIX . "order_status` os WHERE os.order_status_id = o.order_status_id AND os.language_id = o.language_id) AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int) $order_id . "'");

        if ($order_query->num_rows) {
            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int) $order_query->row['payment_country_id'] . "'");

            if ($country_query->num_rows) {
                $payment_iso_code_2 = $country_query->row['iso_code_2'];
                $payment_iso_code_3 = $country_query->row['iso_code_3'];
            } else {
                $payment_iso_code_2 = '';
                $payment_iso_code_3 = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int) $order_query->row['payment_zone_id'] . "'");

            if ($zone_query->num_rows) {
                $payment_zone_code = $zone_query->row['code'];
            } else {
                $payment_zone_code = '';
            }

            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int) $order_query->row['shipping_country_id'] . "'");

            if ($country_query->num_rows) {
                $shipping_iso_code_2 = $country_query->row['iso_code_2'];
                $shipping_iso_code_3 = $country_query->row['iso_code_3'];
            } else {
                $shipping_iso_code_2 = '';
                $shipping_iso_code_3 = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int) $order_query->row['shipping_zone_id'] . "'");

            if ($zone_query->num_rows) {
                $shipping_zone_code = $zone_query->row['code'];
            } else {
                $shipping_zone_code = '';
            }

            $this->load->model('localisation/language');

            $language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

            if ($language_info) {
                $language_code = $language_info['code'];
            } else {
                $language_code = $this->config->get('config_language');
            }

            return array(
                'order_id' => $order_query->row['order_id'],
                'invoice_no' => $order_query->row['invoice_no'],
                'invoice_prefix' => $order_query->row['invoice_prefix'],
                'store_id' => $order_query->row['store_id'],
                'store_name' => $order_query->row['store_name'],
                'store_url' => $order_query->row['store_url'],
                'customer_id' => $order_query->row['customer_id'],
                'firstname' => $order_query->row['firstname'],
                'lastname' => $order_query->row['lastname'],
                'email' => $order_query->row['email'],
                'telephone' => $order_query->row['telephone'],
                'fax' => $order_query->row['fax'],
                'custom_field' => json_decode($order_query->row['custom_field'], true),
                'payment_firstname' => $order_query->row['payment_firstname'],
                'payment_lastname' => $order_query->row['payment_lastname'],
                'payment_company' => $order_query->row['payment_company'],
                'payment_address_1' => $order_query->row['payment_address_1'],
                'payment_address_2' => $order_query->row['payment_address_2'],
                'payment_postcode' => $order_query->row['payment_postcode'],
                'payment_city' => $order_query->row['payment_city'],
                'payment_zone_id' => $order_query->row['payment_zone_id'],
                'payment_zone' => $order_query->row['payment_zone'],
                'payment_zone_code' => $payment_zone_code,
                'payment_country_id' => $order_query->row['payment_country_id'],
                'payment_country' => $order_query->row['payment_country'],
                'payment_iso_code_2' => $payment_iso_code_2,
                'payment_iso_code_3' => $payment_iso_code_3,
                'payment_address_format' => $order_query->row['payment_address_format'],
                'payment_custom_field' => json_decode($order_query->row['payment_custom_field'], true),
                'payment_method' => $order_query->row['payment_method'],
                'payment_code' => $order_query->row['payment_code'],
                'shipping_firstname' => $order_query->row['shipping_firstname'],
                'shipping_lastname' => $order_query->row['shipping_lastname'],
                'shipping_company' => $order_query->row['shipping_company'],
                'shipping_address_1' => $order_query->row['shipping_address_1'],
                'shipping_address_2' => $order_query->row['shipping_address_2'],
                'shipping_postcode' => $order_query->row['shipping_postcode'],
                'shipping_city' => $order_query->row['shipping_city'],
                'shipping_zone_id' => $order_query->row['shipping_zone_id'],
                'shipping_zone' => $order_query->row['shipping_zone'],
                'shipping_zone_code' => $shipping_zone_code,
                'shipping_country_id' => $order_query->row['shipping_country_id'],
                'shipping_country' => $order_query->row['shipping_country'],
                'shipping_iso_code_2' => $shipping_iso_code_2,
                'shipping_iso_code_3' => $shipping_iso_code_3,
                'shipping_address_format' => $order_query->row['shipping_address_format'],
                'shipping_custom_field' => json_decode($order_query->row['shipping_custom_field'], true),
                'shipping_method' => $order_query->row['shipping_method'],
                'shipping_code' => $order_query->row['shipping_code'],
                'comment' => $order_query->row['comment'],
                'total' => $order_query->row['total'],
                'order_status_id' => $order_query->row['order_status_id'],
                'order_status' => $order_query->row['order_status'],
                'affiliate_id' => $order_query->row['affiliate_id'],
                'commission' => $order_query->row['commission'],
                'language_id' => $order_query->row['language_id'],
                'language_code' => $language_code,
                'currency_id' => $order_query->row['currency_id'],
                'currency_code' => $order_query->row['currency_code'],
                'currency_value' => $order_query->row['currency_value'],
                'ip' => $order_query->row['ip'],
                'forwarded_ip' => $order_query->row['forwarded_ip'],
                'user_agent' => $order_query->row['user_agent'],
                'accept_language' => $order_query->row['accept_language'],
                'date_added' => $order_query->row['date_added'],
                'date_modified' => $order_query->row['date_modified']
            );
        } else {
            return false;
        }
    }

    public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false, $override = false) {
        $order_info = $this->getOrder($order_id);

        if ($order_info) {
            // Fraud Detection
            $this->load->model('account/customer');

            $customer_info = $this->model_account_customer->getCustomer($order_info['customer_id']);

            if ($customer_info && $customer_info['safe']) {
                $safe = true;
            } else {
                $safe = false;
            }

            // Only do the fraud check if the customer is not on the safe list and the order status is changing into the complete or process order status
            if (!$safe && !$override && in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
                // Anti-Fraud
                $this->load->model('extension/extension');

                $extensions = $this->model_extension_extension->getExtensions('fraud');

                foreach ($extensions as $extension) {
                    if ($this->config->get($extension['code'] . '_status')) {
                        $this->load->model('extension/fraud/' . $extension['code']);

                        $fraud_status_id = $this->{'model_fraud_' . $extension['code']}->check($order_info);

                        if ($fraud_status_id) {
                            $order_status_id = $fraud_status_id;
                        }
                    }
                }
            }

            // If current order status is not processing or complete but new status is processing or complete then commence completing the order
            if (!in_array($order_info['order_status_id'], array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status'))) && in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
                // Redeem coupon, vouchers and reward points
                $order_total_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int) $order_id . "' ORDER BY sort_order ASC");

                foreach ($order_total_query->rows as $order_total) {
                    $this->load->model('extension/total/' . $order_total['code']);

                    if (property_exists($this->{'model_extension_total_' . $order_total['code']}, 'confirm')) {
                        // Confirm coupon, vouchers and reward points
                        $fraud_status_id = $this->{'model_extension_total_' . $order_total['code']}->confirm($order_info, $order_total);

                        // If the balance on the coupon, vouchers and reward points is not enough to cover the transaction or has already been used then the fraud order status is returned.
                        if ($fraud_status_id) {
                            $order_status_id = $fraud_status_id;
                        }
                    }
                }

                // Add commission if sale is linked to affiliate referral.
                if ($order_info['affiliate_id'] && $this->config->get('config_affiliate_auto')) {
                    $this->load->model('affiliate/affiliate');

                    $this->model_affiliate_affiliate->addTransaction($order_info['affiliate_id'], $order_info['commission'], $order_id);
                }

                // Stock subtraction
                $order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_id . "'");

                foreach ($order_product_query->rows as $order_product) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int) $order_product['quantity'] . ") WHERE product_id = '" . (int) $order_product['product_id'] . "' AND subtract = '1'");

                    $order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int) $order_id . "' AND order_product_id = '" . (int) $order_product['order_product_id'] . "'");

                    foreach ($order_option_query->rows as $option) {
                        $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int) $order_product['quantity'] . ") WHERE product_option_value_id = '" . (int) $option['product_option_value_id'] . "' AND subtract = '1'");
                    }
                }
            }

            // Update the DB with the new statuses
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int) $order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int) $order_id . "'");

            $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int) $order_id . "', order_status_id = '" . (int) $order_status_id . "', notify = '" . (int) $notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");

            // If old order status is the processing or complete status but new status is not then commence restock, and remove coupon, voucher and reward history
            if (in_array($order_info['order_status_id'], array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status'))) && !in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
                // Restock
                $product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_id . "'");

                foreach ($product_query->rows as $product) {
                    $this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int) $product['quantity'] . ") WHERE product_id = '" . (int) $product['product_id'] . "' AND subtract = '1'");

                    $option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int) $order_id . "' AND order_product_id = '" . (int) $product['order_product_id'] . "'");

                    foreach ($option_query->rows as $option) {
                        $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int) $product['quantity'] . ") WHERE product_option_value_id = '" . (int) $option['product_option_value_id'] . "' AND subtract = '1'");
                    }
                }

                // Remove coupon, vouchers and reward points history
                $this->load->model('account/order');

                $order_total_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int) $order_id . "' ORDER BY sort_order ASC");

                foreach ($order_total_query->rows as $order_total) {
                    $this->load->model('extension/total/' . $order_total['code']);

                    if (property_exists($this->{'model_extension_total_' . $order_total['code']}, 'unconfirm')) {
                        $this->{'model_extension_total_' . $order_total['code']}->unconfirm($order_id);
                    }
                }

                // Remove commission if sale is linked to affiliate referral.
                if ($order_info['affiliate_id']) {
                    $this->load->model('affiliate/affiliate');

                    $this->model_affiliate_affiliate->deleteTransaction($order_id);
                }
            }

            $this->cache->delete('product');

            // If order status is 0 then becomes greater than 0 send main html email
            if (!$order_info['order_status_id'] && $order_status_id) {
                // Check for any downloadable products
                $download_status = false;

                $order_product_query = $this->db->query("SELECT pr.tax_class_id, op.order_product_id,  op.order_id, op.product_id, op.name, op.model, op.quantity, op.price, op.total, op.tax, op.reward FROM " . DB_PREFIX . "order_product op join " . DB_PREFIX . "product pr on op.product_id = pr.product_id  WHERE order_id = '" . (int) $order_id . "'");

                foreach ($order_product_query->rows as $order_product) {
                    // Check if there are any linked downloads
                    $product_download_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_download` WHERE product_id = '" . (int) $order_product['product_id'] . "'");

                    if ($product_download_query->row['total']) {
                        $download_status = true;
                    }
                }

                // Load the language for any mails that might be required to be sent out
                $language = new Language($order_info['language_code']);
                $language->load($order_info['language_code']);
                $language->load('mail/order');

                if ($this->config->get('dibseasy_language') == 'sv-SE') {
                    $language->set('text_new_order_detail', 'Orderdetaljer');
                    $language->set('text_new_payment_address', 'Betalningsadress');
                    $language->set('text_new_shipping_address', 'Leveransadress');
                    $language->set('text_new_product', 'Produkt');
                    $language->set('text_new_model', 'Modell');
                    $language->set('text_new_quantity', 'Kvantitet');
                    $language->set('text_new_order_status', 'Orderstatus');
                    $language->set('text_new_date_added', 'Datum Tillagt');
                    $language->set('text_new_shipping_method', 'Frakt metod');
                    $language->set('text_new_email', 'E-post');
                    $language->set('text_new_order_status', 'Orderstatus');
                    $language->set('text_new_telephone', 'Telefon');
                    $language->set('text_new_footer', 'Vänligen svara på det här e-postmeddelandet om du har några frågor.');
                    $language->set('text_new_greeting', 'Vänligen svara på det här e-postmeddelandet om du har några frågor.');
                    $language->set('text_greeting', 'Tack för ditt intresse för dina butiksprodukter. Din beställning har mottagits '
                            . 'och kommer att behandlas när betalningen har bekräftats.');
                    $instructions = 'Betalningsinformation';
                } else {
                    $instructions = 'Payment Details';
                }

                $order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int) $order_status_id . "' AND language_id = '" . (int) $order_info['language_id'] . "'");

                if ($order_status_query->num_rows) {
                    $order_status = $order_status_query->row['name'];
                } else {
                    $order_status = '';
                }

                $subject = sprintf($language->get('text_new_subject'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'), $order_id);

                // HTML Mail
                $data = array();

                $data['title'] = sprintf($language->get('text_new_subject'), $order_info['store_name'], $order_id);

                $data['text_greeting'] = sprintf($language->get('text_new_greeting'), $order_info['store_name']);
                $data['text_link'] = $language->get('text_new_link');
                $data['text_download'] = $language->get('text_new_download');
                $data['text_order_detail'] = $language->get('text_new_order_detail');
                $data['text_instruction'] = $instructions;
                $data['text_order_id'] = $language->get('text_new_order_id');
                $data['text_date_added'] = $language->get('text_new_date_added');
                $data['text_payment_method'] = $language->get('text_new_payment_method');
                $data['text_shipping_method'] = $language->get('text_new_shipping_method');
                $data['text_email'] = $language->get('text_new_email');
                $data['text_telephone'] = $language->get('text_new_telephone');
                $data['text_ip'] = $language->get('text_new_ip');
                $data['text_order_status'] = $language->get('text_new_order_status');
                $data['text_payment_address'] = $language->get('text_new_payment_address');
                $data['text_shipping_address'] = $language->get('text_new_shipping_address');
                $data['text_product'] = $language->get('text_new_product');
                $data['text_model'] = $language->get('text_new_model');
                $data['text_quantity'] = $language->get('text_new_quantity');
                $data['text_price'] = $language->get('text_new_price');
                $data['text_net_price'] = 'Net Price';
                $data['text_vat_p'] = 'Vat %';
                $data['text_vat_f'] = 'Vat (amount)';
                $data['text_total'] = $language->get('text_new_total');
                $data['text_footer'] = $language->get('text_new_footer');

                $data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
                $data['store_name'] = $order_info['store_name'];
                $data['store_url'] = $order_info['store_url'];
                $data['customer_id'] = $order_info['customer_id'];
                $data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id;

                if ($download_status) {
                    $data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
                } else {
                    $data['download'] = '';
                }

                $data['order_id'] = $order_id;
                $data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
                $data['payment_method'] = $order_info['payment_method'];
                $data['shipping_method'] = $order_info['shipping_method'];
                $data['email'] = $order_info['email'];
                $data['telephone'] = $order_info['telephone'];
                $data['ip'] = $order_info['ip'];
                $data['order_status'] = $order_status;

                if ($comment && $notify) {
                    $data['comment'] = nl2br($comment);
                } else {
                    $data['comment'] = '';
                }

                if ($order_info['payment_address_format']) {
                    $format = $order_info['payment_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_info['payment_firstname'],
                    'lastname' => $order_info['payment_lastname'],
                    'company' => $order_info['payment_company'],
                    'address_1' => $order_info['payment_address_1'],
                    'address_2' => $order_info['payment_address_2'],
                    'city' => $order_info['payment_city'],
                    'postcode' => $order_info['payment_postcode'],
                    'zone' => $order_info['payment_zone'],
                    'zone_code' => $order_info['payment_zone_code'],
                    'country' => $order_info['payment_country']
                );

                $data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                if ($order_info['shipping_address_format']) {
                    $format = $order_info['shipping_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_info['shipping_firstname'],
                    'lastname' => $order_info['shipping_lastname'],
                    'company' => $order_info['shipping_company'],
                    'address_1' => $order_info['shipping_address_1'],
                    'address_2' => $order_info['shipping_address_2'],
                    'city' => $order_info['shipping_city'],
                    'postcode' => $order_info['shipping_postcode'],
                    'zone' => $order_info['shipping_zone'],
                    'zone_code' => $order_info['shipping_zone_code'],
                    'country' => $order_info['shipping_country']
                );

                $data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                $this->load->model('tool/upload');

                // Products
                $data['products'] = array();

                foreach ($order_product_query->rows as $product) {
                    $option_data = array();

                    $order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int) $order_id . "' AND order_product_id = '" . (int) $product['order_product_id'] . "'");

                    foreach ($order_option_query->rows as $option) {
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

                    $data['products'][] = array(
                        'name' => $product['name'],
                        'model' => $product['model'],
                        'option' => $option_data,
                        'quantity' => $product['quantity'],
                        'taxproduct' => $this->getTotalTaxRate($product['tax_class_id'], $order_info),
                        'netprice' => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value']) * $product['quantity'],
                        'price' => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                        'total' => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
                    );
                }




                foreach ($data['products'] as $product) {
                    $taxProduct = $product['taxproduct'];
                    if ($taxProduct['P'] > 0) {
                        $data['taxpercent'] = 1;
                    }
                    if ($taxProduct['F'] > 0) {
                        $data['taxfixed'] = 1;
                    }
                }
                // Vouchers
                $data['vouchers'] = array();

                $order_voucher_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int) $order_id . "'");

                foreach ($order_voucher_query->rows as $voucher) {
                    $data['vouchers'][] = array(
                        'description' => $voucher['description'],
                        'amount' => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
                    );
                }

                // Order Totals
                $data['totals'] = array();

                $order_total_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int) $order_id . "' ORDER BY sort_order ASC");

                foreach ($order_total_query->rows as $total) {
                    $data['totals'][] = array(
                        'title' => $total['title'],
                        'text' => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
                    );
                }

                // Text Mail
                $text = sprintf($language->get('text_new_greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8')) . "\n\n";
                $text .= $language->get('text_new_order_id') . ' ' . $order_id . "\n";
                $text .= $language->get('text_new_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n";
                $text .= $language->get('text_new_order_status') . ' ' . $order_status . "\n\n";

                if ($comment && $notify) {
                    $text .= $language->get('text_new_instruction') . "\n\n";
                    $text .= $comment . "\n\n";
                }

                // Products
                $text .= $language->get('text_new_products') . "\n";

                foreach ($order_product_query->rows as $product) {
                    $text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' . html_entity_decode($this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

                    $order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int) $order_id . "' AND order_product_id = '" . $product['order_product_id'] . "'");

                    foreach ($order_option_query->rows as $option) {
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

                        $text .= chr(9) . '-' . $option['name'] . ' ' . (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value) . "\n";
                    }
                }

                foreach ($order_voucher_query->rows as $voucher) {
                    $text .= '1x ' . $voucher['description'] . ' ' . $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']);
                }

                $text .= "\n";

                $text .= $language->get('text_new_order_total') . "\n";

                foreach ($order_total_query->rows as $total) {
                    $text .= $total['title'] . ': ' . html_entity_decode($this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";
                }

                $text .= "\n";

                if ($order_info['customer_id']) {
                    $text .= $language->get('text_new_link') . "\n";
                    $text .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . "\n\n";
                }

                if ($download_status) {
                    $text .= $language->get('text_new_download') . "\n";
                    $text .= $order_info['store_url'] . 'index.php?route=account/download' . "\n\n";
                }

                // Comment
                if ($order_info['comment']) {
                    $text .= $language->get('text_new_comment') . "\n\n";
                    $text .= $order_info['comment'] . "\n\n";
                }

                $text .= $language->get('text_new_footer') . "\n\n";



                $data['store_name'] = $this->config->get('config_name');
                $data['store_address'] = $this->config->get('config_address');
                $data['store_email'] = $this->config->get('config_email');
                $data['store_phone'] = $this->config->get('config_telephone');

                if ($this->config->get('dibseasy_language') == 'sv-SE') {
                    $data['store_name_title'] = 'Affärsnamn';
                    $data['store_address_title'] = 'Butik adress';
                    $data['store_email_title'] = 'Spara e-post';
                    $data['store_phone_title'] = 'Spara telefon';
                    $data['store_details'] = 'Butik detaljer';
                } else {
                    $data['store_name_title'] = 'Store name';
                    $data['store_address_title'] = 'Store address';
                    $data['store_email_title'] = 'Store email';
                    $data['store_phone_title'] = 'Store phone';
                    $data['store_details'] = 'Store details';
                }


                if ($order_info && !$order_info['order_status_id'] && $order_status_id && in_array('order', (array) $this->config->get('config_mail_alert'))) {

                    $mail = new Mail();
                    $mail->protocol = $this->config->get('config_mail_protocol');
                    $mail->parameter = $this->config->get('config_mail_parameter');
                    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                    $mail->setTo($order_info['email']);
                    $mail->setFrom($this->config->get('config_email'));
                    $mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
                    $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
                    $mail->setHtml($this->load->view('mail/dibseasy_order', $data));
                    $mail->setText($text);
                    $mail->send();
                }
                // Admin Alert Mail
                if (in_array('order', (array) $this->config->get('config_mail_alert'))) {
                    $subject = sprintf($language->get('text_new_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $order_id);

                    // HTML Mail
                    $data['text_greeting'] = $language->get('text_new_received');

                    if ($comment) {
                        if ($order_info['comment']) {
                            $data['comment'] = nl2br($comment) . '<br/><br/>' . $order_info['comment'];
                        } else {
                            $data['comment'] = nl2br($comment);
                        }
                    } else {
                        if ($order_info['comment']) {
                            $data['comment'] = $order_info['comment'];
                        } else {
                            $data['comment'] = '';
                        }
                    }
                    $data['text_download'] = '';
                    $data['text_footer'] = '';
                    $data['text_link'] = '';
                    $data['link'] = '';
                    $data['download'] = '';

                    // Text
                    $text = $language->get('text_new_received') . "\n\n";
                    $text .= $language->get('text_new_order_id') . ' ' . $order_id . "\n";
                    $text .= $language->get('text_new_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n";
                    $text .= $language->get('text_new_order_status') . ' ' . $order_status . "\n\n";
                    $text .= $language->get('text_new_products') . "\n";

                    foreach ($order_product_query->rows as $product) {
                        $text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' . html_entity_decode($this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

                        $order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int) $order_id . "' AND order_product_id = '" . $product['order_product_id'] . "'");

                        foreach ($order_option_query->rows as $option) {
                            if ($option['type'] != 'file') {
                                $value = $option['value'];
                            } else {
                                $value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
                            }

                            $text .= chr(9) . '-' . $option['name'] . ' ' . (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value) . "\n";
                        }
                    }

                    foreach ($order_voucher_query->rows as $voucher) {
                        $text .= '1x ' . $voucher['description'] . ' ' . $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']);
                    }

                    $text .= "\n";

                    $text .= $language->get('text_new_order_total') . "\n";

                    foreach ($order_total_query->rows as $total) {
                        $text .= $total['title'] . ': ' . html_entity_decode($this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";
                    }

                    $text .= "\n";

                    if ($order_info['comment']) {
                        $text .= $language->get('text_new_comment') . "\n\n";
                        $text .= $order_info['comment'] . "\n\n";
                    }
                    if ($order_info && !$order_info['order_status_id'] && $order_status_id && in_array('order', (array) $this->config->get('config_mail_alert'))) {
                        $mail = new Mail();
                        $mail->protocol = $this->config->get('config_mail_protocol');
                        $mail->parameter = $this->config->get('config_mail_parameter');
                        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                        $mail->setTo($this->config->get('config_email'));
                        $mail->setFrom($this->config->get('config_email'));
                        $mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
                        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
                        $mail->setHtml($this->load->view('mail/order', $data));
                        $mail->setText($text);
                        $mail->send();
                    }
                    // Send to additional alert emails
                    $emails = explode(',', $this->config->get('config_alert_email'));

                    foreach ($emails as $email) {
                        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $mail->setTo($email);
                            $mail->send();
                        }
                    }
                }
            }

            // If order status is not 0 then send update text email
            if ($order_info['order_status_id'] && $order_status_id && $notify) {
                $language = new Language($order_info['language_code']);
                $language->load($order_info['language_code']);
                $language->load('mail/order');

                $subject = sprintf($language->get('text_update_subject'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'), $order_id);

                $message = $language->get('text_update_order') . ' ' . $order_id . "\n";
                $message .= $language->get('text_update_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";

                $order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int) $order_status_id . "' AND language_id = '" . (int) $order_info['language_id'] . "'");

                if ($order_status_query->num_rows) {
                    $message .= $language->get('text_update_order_status') . "\n\n";
                    $message .= $order_status_query->row['name'] . "\n\n";
                }

                if ($order_info['customer_id']) {
                    $message .= $language->get('text_update_link') . "\n";
                    $message .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . "\n\n";
                }

                if ($comment) {
                    $message .= $language->get('text_update_comment') . "\n\n";
                    $message .= strip_tags($comment) . "\n\n";
                }

                $message .= $language->get('text_update_footer');
                if ($order_info && !$order_info['order_status_id'] && $order_status_id && in_array('order', (array) $this->config->get('config_mail_alert'))) {
                    $mail = new Mail();
                    $mail->protocol = $this->config->get('config_mail_protocol');
                    $mail->parameter = $this->config->get('config_mail_parameter');
                    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                    $mail->setTo($order_info['email']);
                    $mail->setFrom($this->config->get('config_email'));
                    $mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
                    $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
                    $mail->setText($message);
                    $mail->send();
                }
            }
        }
    }

    protected function getTotalTaxRate($tax_class_id, $order_info) {
        $percentTotal = 0;
        $fixedTotal = 0;
        foreach ($this->tax->getRates(0, $tax_class_id) as $tax) {
            if ('P' == $tax['type']) {
                $percentTotal += $tax['rate'];
            }
            if ('F' == $tax['type']) {
                $fixedTotal += $this->currency->format($tax['rate'], $order_info['currency_code'], $order_info['currency_value']);
            }
        }
        return array('P' => $percentTotal,
            'F' => $fixedTotal);
    }

//update order if customer change the shipping method in checkout page
    public function updateOrder($order_id) {
        $this->load->language('api/order');
        $json = array();

        $this->load->model('checkout/order');

        if (isset($order_id)) {
            $order_id = $order_id;
        } else {
            $order_id = 0;
        }

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            // Customer
            if (!isset($this->session->data['customer'])) {
                $json['error'] = $this->language->get('error_customer');
            }

            // Payment Address
            if (!isset($this->session->data['payment_address'])) {
                $json['error'] = $this->language->get('error_payment_address');
            }

            // Payment Method

            if (!isset($this->session->data['payment_method'])) {
                $json['error'] = $this->language->get('error_payment_method');
            }

            // Shipping
            if ($this->cart->hasShipping()) {
                // Shipping Address
                if (!isset($this->session->data['shipping_address'])) {
                    $json['error'] = $this->language->get('error_shipping_address');
                }
                // Shipping Method
                if (!isset($this->session->data['shipping_method'])) {
                    $json['error'] = $this->language->get('error_shipping_method');
                }
            } else {
                unset($this->session->data['shipping_address']);
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
            }

            // Cart
            if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
                $json['error'] = $this->language->get('error_stock');
            }
            //echo "<pre>";print_r($json);die;
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
                    $json['error'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);

                    break;
                }
            }

            //if (!$json) {
            $json['success'] = $this->language->get('text_success');

            $order_data = array();

            // Store Details
            $order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
            $order_data['store_id'] = $this->config->get('config_store_id');
            $order_data['store_name'] = $this->config->get('config_name');
            $order_data['store_url'] = $this->config->get('config_url');

            // Customer Details
            $order_data['customer_id'] = '';
            $order_data['customer_group_id'] = '';
            $order_data['firstname'] = '';
            $order_data['lastname'] = '';
            $order_data['email'] = '';
            $order_data['telephone'] = '';
            $order_data['fax'] = '';
            $order_data['custom_field'] = '';

            if (isset($this->session->data['customer']['customer_id'])) {
                $order_data['customer_id'] = $this->session->data['customer']['customer_id'];
                $order_data['customer_group_id'] = $this->session->data['customer']['customer_group_id'];
                $order_data['firstname'] = $this->session->data['customer']['firstname'];
                $order_data['lastname'] = $this->session->data['customer']['lastname'];
                $order_data['email'] = $this->session->data['customer']['email'];
                $order_data['telephone'] = $this->session->data['customer']['telephone'];
                $order_data['fax'] = $this->session->data['customer']['fax'];
                $order_data['custom_field'] = $this->session->data['customer']['custom_field'];
            }
            // Payment Details
            $order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
            $order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
            $order_data['payment_company'] = $this->session->data['payment_address']['company'];
            $order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
            $order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
            $order_data['payment_city'] = $this->session->data['payment_address']['city'];
            $order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
            $order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
            $order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
            $order_data['payment_country'] = $this->session->data['payment_address']['country'];
            $order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
            $order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
            $order_data['payment_custom_field'] = $this->session->data['payment_address']['custom_field'];

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

            // Shipping Details
            if ($this->cart->hasShipping()) {
                $order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
                $order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
                $order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
                $order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
                $order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
                $order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
                $order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
                $order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
                $order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
                $order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
                $order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
                $order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
                $order_data['shipping_custom_field'] = $this->session->data['shipping_address']['custom_field'];

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
            } else {
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
            }

            // Products
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

            // Order Totals
            $this->load->model('extension/extension');

            $totals = array();
            $taxes = $this->cart->getTaxes();
            $total = 0;

            // Because __call can not keep var references so we put them into an array. 
            $total_data = array(
                'totals' => &$totals,
                'taxes' => &$taxes,
                'total' => &$total
            );

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

            foreach ($total_data['totals'] as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $total_data['totals']);

            $order_data = array_merge($order_data, $total_data);

            if (isset($this->session->data['comment'])) {
                $order_data['comment'] = $this->session->data['comment'];
            } else {
                $order_data['comment'] = '';
            }

            if (isset($this->request->cookie['tracking'])) {
                $subtotal = $this->cart->getSubTotal();

                // Affiliate
                $this->load->model('affiliate/affiliate');

                $affiliate_info = $this->model_affiliate_affiliate->getAffiliate($this->request->post['affiliate_id']);

                if ($affiliate_info) {
                    $order_data['affiliate_id'] = $affiliate_info['affiliate_id'];
                    $order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
                } else {
                    $order_data['affiliate_id'] = 0;
                    $order_data['commission'] = 0;
                }
            } else {
                $order_data['affiliate_id'] = 0;
                $order_data['commission'] = 0;
            }

            $this->editOrder($order_id, $order_data);
            //}
        }
    }

}
