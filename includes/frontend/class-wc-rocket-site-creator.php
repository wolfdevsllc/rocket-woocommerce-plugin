<?php

if (!class_exists('WC_Rocket_Site_Creator')) {

    class WC_Rocket_Site_Creator {
        private static $instance;

        public function __construct() {
            add_action('wp_ajax_create_rocket_site', array($this, 'ajax_create_rocket_site'));
            add_action('wp_ajax_get_available_allocations', array($this, 'ajax_get_available_allocations'));
            add_action('template_redirect', array($this, 'check_site_access'));
        }

        public function ajax_get_available_allocations() {
            check_ajax_referer('wc_rocket_nonce', 'nonce');

            if (!is_user_logged_in()) {
                wp_send_json_error(array('message' => __('You must be logged in.', 'wc-rocket')));
            }

            $customer_id = get_current_user_id();

            $allocations = WC_Rocket_Site_Allocations::get_instance()->get_customer_allocations($customer_id);

            if (empty($allocations)) {
                wp_send_json_error(array('message' => __('No allocations found.', 'wc-rocket')));
                return;
            }

            // Calculate total available sites across all allocations
            $total_sites = 0;
            $sites_used = 0;
            $allocation_details = array();

            foreach ($allocations as $alloc) {
                $total_sites += $alloc->total_sites;
                $sites_used += $alloc->sites_created;
                $allocation_details[] = array(
                    'order_id' => $alloc->order_id,
                    'total' => $alloc->total_sites,
                    'used' => $alloc->sites_created
                );
            }

            if ($total_sites <= $sites_used) {
                wp_send_json_error(array('message' => __('No available site allocations found.', 'wc-rocket')));
                return;
            }

            // Response data with combined allocation info
            $response_data = array(
                'html' => $this->get_allocation_html($allocation_details, $total_sites, $sites_used),
                'allocation_ids' => wp_json_encode(array_column($allocations, 'id'))  // Send all allocation IDs
            );

            wp_send_json_success($response_data);
        }

        private function get_allocation_html($allocation_details, $total_sites, $sites_used) {
            $html = '<div class="allocation-info">';

            // Show total summary only
            $html .= sprintf(
                '<div class="allocation-summary"><p>%s</p></div>',
                sprintf(
                    __('Total Sites Available: %d/%d used', 'wc-rocket'),
                    $sites_used,
                    $total_sites
                )
            );

            $html .= '</div>';

            return $html;
        }

        public function ajax_create_rocket_site() {
            check_ajax_referer('wc_rocket_nonce', 'nonce');

            if (!is_user_logged_in()) {
                wp_send_json_error(array('message' => __('You must be logged in.', 'wc-rocket')));
            }

            $customer_id = get_current_user_id();
            $site_name = sanitize_text_field($_POST['site_name']);
            $site_location = intval($_POST['site_location']);

            // Validate site name
            if (!$this->validate_site_name($site_name)) {
                wp_send_json_error(array('message' => __('Invalid site name. Please use only letters, numbers, spaces, and hyphens.', 'wc-rocket')));
                return;
            }

            // Get next available allocation
            $allocation = $this->get_next_available_allocation($customer_id);
            if (!$allocation) {
                wp_send_json_error(array('message' => __('No available site quota.', 'wc-rocket')));
                return;
            }

            // Create the site
            $result = $this->create_site($allocation, $site_name, $site_location);

            if (is_wp_error($result)) {
                wp_send_json_error(array('message' => $result->get_error_message()));
                return;
            }

            // Get updated total available sites
            $available_sites = WC_Rocket_Site_Allocations::get_instance()->get_customer_available_allocations($customer_id);

            $result['remaining_sites'] = $available_sites;
            wp_send_json_success($result);
        }

        private function validate_site_name($site_name) {
            return preg_match('/^[a-zA-Z0-9- ]+$/', $site_name);
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

            // error_log('Looking up allocation ID: ' . $allocation_id);

            $query = $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wc_rocket_site_allocations WHERE id = %d",
                $allocation_id
            );

            // error_log('Running query: ' . $query);

            $allocation = $wpdb->get_row($query);

            // error_log('Query result: ' . print_r($allocation, true));

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

        public function check_site_access() {
            // Only check on my-sites page
            if (!is_wc_endpoint_url('my-sites')) {
                return;
            }

            $user_id = get_current_user_id();
            $has_access = get_user_meta($user_id, 'wc_rocket_site_access', true);

            if ($has_access === 'disabled') {
                wp_redirect(wc_get_account_endpoint_url('dashboard'));
                exit;
            }
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