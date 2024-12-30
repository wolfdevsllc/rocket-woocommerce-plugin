<?php

if (!class_exists('WC_Rocket_Site_Creator')) {

    class WC_Rocket_Site_Creator {
        private static $instance;

        public function __construct() {
            add_action('wp_ajax_create_rocket_site', array($this, 'ajax_create_rocket_site'));
            add_action('wp_ajax_get_available_allocations', array($this, 'ajax_get_available_allocations'));
        }

        public function ajax_get_available_allocations() {
            check_ajax_referer('wc_rocket_nonce', 'nonce');

            if (!is_user_logged_in()) {
                error_log('User not logged in');
                wp_send_json_error(array('message' => __('You must be logged in.', 'wc-rocket')));
            }

            $customer_id = get_current_user_id();
            error_log('Getting allocations for customer: ' . $customer_id);

            $allocations = WC_Rocket_Site_Allocations::get_instance()->get_customer_allocations($customer_id);
            error_log('Raw allocations: ' . print_r($allocations, true));

            if (empty($allocations)) {
                error_log('No allocations found');
                wp_send_json_error(array('message' => __('No allocations found.', 'wc-rocket')));
                return;
            }

            // Get the first available allocation
            $allocation = null;
            foreach ($allocations as $alloc) {
                error_log("Checking allocation {$alloc->id}: {$alloc->sites_created}/{$alloc->total_sites}");
                if ($alloc->total_sites > $alloc->sites_created) {
                    $allocation = $alloc;
                    break;
                }
            }

            if (!$allocation) {
                error_log('No available allocations found');
                wp_send_json_error(array('message' => __('No available site allocations found.', 'wc-rocket')));
                return;
            }

            error_log('Selected allocation: ' . print_r($allocation, true));

            // Only send the HTML for allocation details display, not the hidden input
            $response_data = array(
                'html' => sprintf(
                    '<div class="allocation-info">
                        <p>%s</p>
                        <p>%s</p>
                    </div>',
                    sprintf(
                        __('Using allocation from order #%s', 'wc-rocket'),
                        $allocation->order_id
                    ),
                    sprintf(
                        __('Sites: %d/%d used', 'wc-rocket'),
                        $allocation->sites_created,
                        $allocation->total_sites
                    )
                ),
                'allocation_id' => $allocation->id  // This will be used by JavaScript to set the hidden input value
            );

            error_log('Sending response: ' . print_r($response_data, true));
            wp_send_json_success($response_data);
        }

        private function get_allocation_html($allocation) {
            return sprintf(
                '<div class="allocation-info">
                    <p>%s</p>
                    <p>%s</p>
                </div>',
                sprintf(
                    __('Using allocation from order #%s', 'wc-rocket'),
                    $allocation->order_id
                ),
                sprintf(
                    __('Sites: %d/%d used', 'wc-rocket'),
                    $allocation->sites_created,
                    $allocation->total_sites
                )
            );
        }

        public function ajax_create_rocket_site() {
            check_ajax_referer('wc_rocket_nonce', 'nonce');

            if (!is_user_logged_in()) {
                wp_send_json_error(array('message' => __('You must be logged in.', 'wc-rocket')));
            }

            $customer_id = get_current_user_id();
            $site_name = sanitize_text_field($_POST['site_name']);
            $site_location = intval($_POST['site_location']);
            $allocation_id = isset($_POST['allocation_id']) ? intval($_POST['allocation_id']) : 0;

            error_log('Creating site with:');
            error_log('Customer ID: ' . $customer_id);
            error_log('Site Name: ' . $site_name);
            error_log('Site Location: ' . $site_location);
            error_log('Allocation ID: ' . $allocation_id);

            if (!$allocation_id) {
                wp_send_json_error(array('message' => __('No allocation selected.', 'wc-rocket')));
            }

            // Debug allocation lookup
            $allocation = $this->get_allocation_by_id($allocation_id);
            error_log('Found allocation: ' . print_r($allocation, true));

            if (!$allocation || $allocation->customer_id != $customer_id) {
                error_log('Invalid allocation - allocation: ' . ($allocation ? 'exists' : 'null'));
                error_log('Customer ID match: ' . ($allocation && $allocation->customer_id == $customer_id ? 'yes' : 'no'));
                wp_send_json_error(array('message' => __('Invalid allocation.', 'wc-rocket')));
                return;
            }

            // Validate site name
            if (!$this->validate_site_name($site_name)) {
                wp_send_json_error(array('message' => __('Invalid site name. Please use only letters, numbers, and hyphens.', 'wc-rocket')));
                return;
            }

            // Get allocation
            $allocation = $allocation_id > 0 ?
                $this->get_allocation_by_id($allocation_id) :
                $this->get_next_available_allocation($customer_id);

            if (!$allocation) {
                wp_send_json_error(array('message' => __('No available allocations found.', 'wc-rocket')));
                return;
            }

            // Create the site
            $result = $this->create_site($allocation, $site_name, $site_location);

            if (is_wp_error($result)) {
                wp_send_json_error(array('message' => $result->get_error_message()));
                return;
            }

            wp_send_json_success($result);
        }

        private function validate_site_name($site_name) {
            return preg_match('/^[a-zA-Z0-9-]+$/', $site_name);
        }

        private function create_site($allocation, $site_name, $site_location) {
            global $wpdb;

            // Start transaction
            $wpdb->query('START TRANSACTION');

            try {
                // Update allocation count
                $updated = WC_Rocket_Site_Allocations::get_instance()->increment_sites_created($allocation->id);
                if (!$updated) {
                    throw new Exception(__('Failed to update allocation.', 'wc-rocket'));
                }

                $admin_username = $this->generate_random_admin_name();
                $admin_password = wp_generate_password();
                $admin_email = wp_get_current_user()->user_email;

                // Get product settings
                $product = wc_get_product($allocation->product_id);
                $rocket_product_data = WC_Product_Rocket_General::get_instance()->get_rocket_product_settings_data($product);

                $site_data = array(
                    'multisite' => false,
                    'domain' => 'wpdns.site',
                    'name' => $site_name,
                    'location' => $site_location,
                    'admin_username' => $admin_username,
                    'admin_password' => $admin_password,
                    'admin_email' => $admin_email,
                    'install_plugins' => WC_Product_Rocket_Settings::get_rocket_product_plugins_install($allocation->product_id),
                    'quota' => intval($rocket_product_data['disk_space']),
                    'bwlimit' => intval($rocket_product_data['bandwidth'])
                );

                $response = WC_Rocket_Api_Site_Crud_Requests::get_instance()->create_rocket_new_site($site_data);

                if (!$response || $response['error'] || !isset($response['response'])) {
                    throw new Exception(__('Failed to create site via API.', 'wc-rocket'));
                }

                $create_response = json_decode($response['response']);
                if (!$create_response->success) {
                    throw new Exception(__('API returned error.', 'wc-rocket'));
                }

                // Save site data
                $site_domain = $create_response->result->domain;
                $site_data = array(
                    'site_id' => $create_response->result->id,
                    'customer_id' => $allocation->customer_id,
                    'product_id' => $allocation->product_id,
                    'order_id' => $allocation->order_id,
                    'domain' => $site_domain,
                    'site_name' => $site_name,
                    'admin_email' => $admin_email,
                    'allocation_id' => $allocation->id
                );

                WC_Rocket_Sites_Crud::get_instance()->insert_rocket_new_site_data($site_data);

                // Send email
                $order = wc_get_order($allocation->order_id);
                do_action('woocommerce_create_rocket_site_email', $order, $site_domain, $admin_username, $admin_password);

                $wpdb->query('COMMIT');

                return array(
                    'site_id' => $create_response->result->id,
                    'domain' => $site_domain,
                    'remaining_sites' => ($allocation->total_sites - ($allocation->sites_created + 1))
                );

            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('site_creation_failed', $e->getMessage());
            }
        }

        private function get_allocation_by_id($allocation_id) {
            global $wpdb;

            error_log('Looking up allocation ID: ' . $allocation_id);

            $query = $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wc_rocket_site_allocations WHERE id = %d",
                $allocation_id
            );

            error_log('Running query: ' . $query);

            $allocation = $wpdb->get_row($query);

            error_log('Query result: ' . print_r($allocation, true));

            return $allocation;
        }

        private function generate_random_admin_name() {
            return 'admin_' . wp_generate_password(8, false);
        }

        private function get_next_available_allocation($customer_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . WC_Rocket_Site_Allocations::$wc_rocket_site_allocations_table;

            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name
                WHERE customer_id = %d
                AND sites_created < total_sites
                ORDER BY created_at ASC
                LIMIT 1",
                $customer_id
            ));
        }

        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}

WC_Rocket_Site_Creator::get_instance();