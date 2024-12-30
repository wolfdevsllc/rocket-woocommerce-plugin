<?php

if (!class_exists('WC_Rocket_Order_Admin')) {
    class WC_Rocket_Order_Admin {
        private static $instance;

        public function __construct() {
            add_action('woocommerce_admin_order_data_after_order_details', array($this, 'add_allocation_info'));
        }

        public function add_allocation_info($order) {
            global $wpdb;
            $table_name = $wpdb->prefix . WC_Rocket_Site_Allocations::$wc_rocket_site_allocations_table;

            $allocations = $wpdb->get_results($wpdb->prepare("
                SELECT * FROM $table_name WHERE order_id = %d
            ", $order->get_id()));

            if (!empty($allocations)) {
                include WC_ROCKET_FILE . '/templates/admin/order/allocation-info.php';
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

WC_Rocket_Order_Admin::get_instance();