<?php

if (!class_exists('WC_Order_Item_Rocket')) {
    class WC_Order_Item_Rocket {
        private static $instance;

        public function __construct() {
            // Remove old site details display
            remove_action('woocommerce_order_item_meta_end', array($this, 'display_rocket_site_details'));

            // Add allocation info instead
            add_action('woocommerce_order_item_meta_end', array($this, 'display_rocket_allocation_info'), 10, 3);
        }

        public function display_rocket_allocation_info($item_id, $item, $order) {
            if (!WC_Product_Rocket_General::get_instance()->check_wc_product_is_rocket($item->get_product())) {
                return;
            }

            $allocation_id = get_post_meta($order->get_id(), 'rocket_allocation_id', true);
            if (!$allocation_id) {
                return;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . WC_Rocket_Site_Allocations::$wc_rocket_site_allocations_table;

            $allocation = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $allocation_id
            ));

            if ($allocation) {
                echo '<br><strong>' . __('Site Allocation:', 'wc-rocket') . '</strong> ';
                echo sprintf(
                    __('%d sites available (%d used)', 'wc-rocket'),
                    $allocation->total_sites,
                    $allocation->sites_created
                );
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

WC_Order_Item_Rocket::get_instance();