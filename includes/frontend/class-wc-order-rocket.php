<?php

/**
 * WC Order Rocket
 */
if (!class_exists('WC_Order_Rocket')) {

    class WC_Order_Rocket {

        public static $instance;

        public function __construct() {
            // wc create rocket site when order is processed or completed
            add_action('woocommerce_order_status_changed', array($this, 'wc_create_rocket_site'), 10, 4);
            // delete rocket site when order status change to cancelled
            add_action( 'woocommerce_order_status_changed', [$this,'wc_delete_rocket_site'],10, 4);
            // change wc rocket order formatted meta data
            add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'wc_rocket_order_item_meta_data'), 10, 2);
        }

        /**
         *  delete rocket site when order status change to cancelled
         *
         * @param int $order_id
         * @param string $previous_status
         * @param string $next_status
         * @param object $order
         */
        public function wc_delete_rocket_site($order_id, $previous_status, $next_status, $order) {
            // Check if this is a cancellation status
            $delete_site_valid_status = self::valid_order_status_delete_site();
            if (!in_array($next_status, $delete_site_valid_status)) {
                return;
            }

            // Get allocation ID
            $allocation_id = get_post_meta($order_id, 'rocket_allocation_id', true);
            if (!$allocation_id) {
                return;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . WC_Rocket_Site_Allocations::$wc_rocket_site_allocations_table;

            // Delete the allocation
            $wpdb->delete(
                $table_name,
                array('id' => $allocation_id),
                array('%d')
            );

            // Remove allocation meta from order
            delete_post_meta($order_id, 'rocket_allocation_created');
            delete_post_meta($order_id, 'rocket_allocation_id');
        }

        /**
         * wc create rocket site when order is processed or completed
         *
         * @param int $order_id
         * @param string $previous_status
         * @param string $next_status
         * @param object $order
         */
        public function wc_create_rocket_site($order_id, $previous_status, $next_status, $order) {
            // Check if allocation already created for this order
            if (get_post_meta($order_id, 'rocket_allocation_created', true)) {
                return;
            }

            // Get valid status to create rocket allocation
            $create_site_valid_status = self::valid_order_status_create_site();
            if (!in_array($next_status, $create_site_valid_status)) {
                return;
            }

            // Get order customer id
            $order_customer_id = $order->get_customer_id();
            if (!$order_customer_id) {
                return;
            }

            // Get rocket product id from order
            $rocket_item_data = $this->get_rocket_order_item_data($order);
            $rocket_product_id = $rocket_item_data['rocket_product_id'];

            if (!$rocket_product_id) {
                return;
            }

            // Create allocation without needing site details from checkout
            $this->create_site_allocation($order_id, $order_customer_id, $rocket_product_id);
        }

        private function create_site_allocation($order_id, $customer_id, $product_id) {
            // Get product
            $product = wc_get_product($product_id);
            if (!$product) {
                return;
            }

            // Get number of sites allowed
            $sites_limit = get_post_meta($product_id, 'rocket_sites_limit', true);
            $sites_limit = !empty($sites_limit) ? intval($sites_limit) : 1;

            // Create allocation
            $allocation_data = array(
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'product_id' => $product_id,
                'total_sites' => $sites_limit
            );

            $allocation_id = WC_Rocket_Site_Allocations::get_instance()->create_allocation($allocation_data);

            if ($allocation_id) {
                // Mark order as allocated
                update_post_meta($order_id, 'rocket_allocation_created', true);
                update_post_meta($order_id, 'rocket_allocation_id', $allocation_id);

                // Notify customer about their allocation
                do_action('woocommerce_rocket_allocation_created', wc_get_order($order_id), $sites_limit);
            }
        }

        /**
         * change wc rocket order formatted meta data
         *
         * @param array $formatted_meta
         * @param object $order_item
         * @return array
         */
        public function wc_rocket_order_item_meta_data($formatted_meta, $order_item) {
            // display rocket site name and location
            $order_rocket_meta_data = array(
                'rocket_site_name' => __('Site Name', 'wc-rocket'),
                'rocket_site_location' => __('Site Location', 'wc-rocket')
            );

            foreach ($formatted_meta as $meta_id => $meta_data) {
                if (isset($meta_data->display_key) && isset($order_rocket_meta_data[$meta_data->display_key])) {
                    $formatted_meta[$meta_id]->display_key = $order_rocket_meta_data[$meta_data->display_key];
                    // set site location
                    if ($meta_data->key == 'rocket_site_location') {
                        $rocket_site_locations = WC_Rocket_Locations::get_instance()->get_rocket_site_locations();
                        $rocket_site_location = intval($meta_data->value);
                        if (isset($rocket_site_locations[$rocket_site_location]))
                            $formatted_meta[$meta_id]->display_value = $rocket_site_locations[$rocket_site_location];
                    }
                }
            }

            return $formatted_meta;
        }

        /**
         * valid wc order status to create domain on site
         * @return type
         */
        public static function valid_order_status_create_site() {
            return apply_filters('valid_rocket_site_order_status', array(
                'completed'
            ));
        }

        /**
         * valid wc order status to delete site
         * @return type
         */
        public static function valid_order_status_delete_site() {
            return apply_filters('valid_rocket_delete_site_order_status', array(
                WC_CANCELLED_ORDER_STATUS
            ));
        }

        /**
         * get rocket product and site data from order
         *
         * @param object $order
         * @return int
         */
        public function get_rocket_order_item_data($order) {
            $rocket_item_data = array(
                'rocket_product_id' => '',
                'rocket_site_name' => '',
                'rocket_site_location' => ''
            );

            if ($order && $order->get_items()) {
                foreach ($order->get_items() as $item_id => $item) {
                    $product = $item->get_product();
                    if (WC_Product_Rocket_General::get_instance()->check_wc_product_is_rocket($product)) {
                        $rocket_item_data['rocket_product_id'] = $product->get_id();
                        $rocket_item_data['rocket_site_name'] = wc_get_order_item_meta($item_id, 'rocket_site_name', true);
                        $rocket_item_data['rocket_site_location'] = wc_get_order_item_meta($item_id, 'rocket_site_location', true);
                        break;
                    }
                }
            }

            return $rocket_item_data;
        }

        /**
         * generate random admin user name
         *
         * @param type $length
         * @return string
         */
        public function generate_random_admin_name($length = 10) {
            $characters = 'abcdefghijklmnopqrstuvwxyz';
            $charactersLength = strlen($characters);
            $random_admin_name = '';
            for ($i = 0; $i < $length; $i++) {
                $random_admin_name .= $characters[rand(0, $charactersLength - 1)];
            }
            return $random_admin_name;
        }

        /**
         * get site default installed plugins
         *
         * @param int $product_id
         * @return string
         */
        public function get_site_installed_plugins($product_id) {

            $site_installed_plugins = WC_Product_Rocket_Settings::get_rocket_product_plugins_install($product_id);
            // get installed plugins from product

            if ( !$site_installed_plugins ) {
                $site_installed_plugins = WC_Rocket_Admin_Settings_Page::get_default_plugins_install();
            }

            return $site_installed_plugins;
        }

        /**
         * WC_Order_Rocket instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    WC_Order_Rocket::get_instance();
}
