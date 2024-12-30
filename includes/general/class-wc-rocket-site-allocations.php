<?php

/**
 * Rocket Site Allocations
 */
if (!class_exists('WC_Rocket_Site_Allocations')) {

    class WC_Rocket_Site_Allocations {

        public static $instance;
        public static $wc_rocket_site_allocations_table = 'wc_rocket_site_allocations';

        public function __construct() {
            // Create allocations table
            add_action('init', array($this, 'create_rocket_site_allocations_table'));
        }

        /**
         * Create rocket site allocations table
         *
         * @global object $wpdb
         */
        public function create_rocket_site_allocations_table() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $wpdb->prefix . self::$wc_rocket_site_allocations_table;

            $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));

            if (!$wpdb->get_var($query) == $table_name) {
                $sql = "CREATE TABLE $table_name (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    order_id bigint(20) NOT NULL,
                    customer_id bigint(20) NOT NULL,
                    product_id bigint(20) NOT NULL,
                    total_sites int(11) NOT NULL,
                    sites_created int(11) DEFAULT 0,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id)
                ) $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta($sql);
            }
        }

        /**
         * Get customer's available site allocations
         *
         * @param int $customer_id
         * @return int
         */
        public function get_customer_available_allocations($customer_id) {
            $cache_key = 'customer_available_allocations_' . $customer_id;
            $result = wp_cache_get($cache_key, 'wc_rocket');

            if (false === $result) {
                global $wpdb;
                $table_name = $wpdb->prefix . self::$wc_rocket_site_allocations_table;

                $sql = $wpdb->prepare(
                    "SELECT SUM(total_sites - sites_created) as available_sites
                    FROM $table_name
                    WHERE customer_id = %d",
                    $customer_id
                );

                $result = $wpdb->get_var($sql);
                wp_cache_set($cache_key, $result, 'wc_rocket', HOUR_IN_SECONDS);
            }

            return intval($result);
        }

        /**
         * Create new site allocation
         *
         * @param array $allocation_data
         * @return int|false
         */
        public function create_allocation($allocation_data) {
            global $wpdb;
            $table_name = $wpdb->prefix . self::$wc_rocket_site_allocations_table;

            $result = $wpdb->insert(
                $table_name,
                array(
                    'order_id' => $allocation_data['order_id'],
                    'customer_id' => $allocation_data['customer_id'],
                    'product_id' => $allocation_data['product_id'],
                    'total_sites' => $allocation_data['total_sites'],
                    'sites_created' => 0
                ),
                array('%d', '%d', '%d', '%d', '%d')
            );

            return $result ? $wpdb->insert_id : false;
        }

        /**
         * Get instance of WC_Rocket_Site_Allocations
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

        public function increment_sites_created($allocation_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . self::$wc_rocket_site_allocations_table;

            return $wpdb->query($wpdb->prepare(
                "UPDATE $table_name
                SET sites_created = sites_created + 1,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = %d
                AND sites_created < total_sites",
                $allocation_id
            ));
        }

        public function clear_customer_allocations_cache($customer_id) {
            wp_cache_delete('customer_allocations_' . $customer_id, 'wc_rocket');
            wp_cache_delete('customer_available_allocations_' . $customer_id, 'wc_rocket');
        }
    }
}