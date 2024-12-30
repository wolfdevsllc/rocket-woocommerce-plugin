<?php

/**
 * WC Order Rocket
 */
if (!class_exists('WC_Order_Rocket')) {

    class WC_Order_Rocket {

        private static $instance;

        public function __construct() {
            // Hook into both order status changes and payment complete
            add_action('woocommerce_order_status_changed', array($this, 'wc_create_rocket_site'), 10, 4);
            add_action('woocommerce_payment_complete', array($this, 'handle_payment_complete'));
        }

        public function handle_payment_complete($order_id) {
            $order = wc_get_order($order_id);
            if (!$order) return;

            $this->wc_create_rocket_site($order_id, $order->get_status(), 'completed', $order);
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
            $create_site_valid_status = $this->valid_order_status_create_site();
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
            if (empty($rocket_item_data['rocket_product_id'])) {
                return;
            }

            // Create allocation
            $this->create_site_allocation($order_id, $order_customer_id, $rocket_item_data['rocket_product_id']);
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
                do_action('woocommerce_rocket_allocation_created', $order, $sites_limit);

                // Force refresh allocation cache
                WC_Rocket_Site_Allocations::get_instance()->clear_customer_allocations_cache($customer_id);
            }
        }

        /**
         * valid wc order status to create domain on site
         * @return type
         */
        private function valid_order_status_create_site() {
            return array('completed', 'processing');
        }

        /**
         * get rocket product and site data from order
         *
         * @param object $order
         * @return int
         */
        private function get_rocket_order_item_data($order) {
            $data = array('rocket_product_id' => 0);

            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                if (WC_Product_Rocket_General::get_instance()->check_wc_product_is_rocket($product)) {
                    $data['rocket_product_id'] = $product->get_id();
                    break;
                }
            }

            return $data;
        }

        /**
         * WC_Order_Rocket instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

    }

    WC_Order_Rocket::get_instance();
}
