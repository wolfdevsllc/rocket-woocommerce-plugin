<?php

class WC_Rocket_Order_Handler {
    private static $instance;

    public function __construct() {
        add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_change'), 10, 4);
    }

    public function handle_order_status_change($order_id, $old_status, $new_status, $order) {
        error_log("Processing Rocket order status change: {$order_id} from {$old_status} to {$new_status}");

        // Check if order contains rocket hosting products
        if (!$this->has_rocket_hosting($order)) {
            return;
        }

        if ($new_status === 'cancelled') {
            $this->disable_rocket_sites($order);
        }
    }

    private function has_rocket_hosting($order) {
        foreach ($order->get_items() as $item) {
            if ($this->is_rocket_hosting_product($item->get_product_id())) {
                return true;
            }
        }
        return false;
    }

    private function is_rocket_hosting_product($product_id) {
        // Add your logic to identify rocket hosting products
        $rocket_product_ids = array(/* your rocket hosting product IDs */);
        return in_array($product_id, $rocket_product_ids);
    }

    private function disable_rocket_sites($order) {
        global $wpdb;

        $customer_id = $order->get_customer_id();
        $order_id = $order->get_id();

        error_log("Disabling Rocket sites for order {$order_id}, customer {$customer_id}");

        // Get allocations for this order
        $allocations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wc_rocket_site_allocations
            WHERE order_id = %d",
            $order_id
        ));

        if (!empty($allocations)) {
            foreach ($allocations as $allocation) {
                // Update allocation status or perform any necessary actions
                $wpdb->update(
                    $wpdb->prefix . 'wc_rocket_site_allocations',
                    array('status' => 'disabled'),
                    array('id' => $allocation->id),
                    array('%s'),
                    array('%d')
                );
            }
        }
    }

    private function disable_site_access($site) {
        // Add your logic to disable site access
        // This might involve API calls or database updates
        error_log("Disabling access for site ID: {$site->id}");

        // Update site status in database
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'wc_rocket_sites',
            array('status' => 'disabled'),
            array('id' => $site->id)
        );

        // Add any additional API calls or actions needed to disable the site
    }

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

// Initialize the handler
add_action('init', function() {
    WC_Rocket_Order_Handler::get_instance();
});