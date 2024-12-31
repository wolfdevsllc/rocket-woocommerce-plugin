<?php

/**
 * Rocket Site Allocations
 */
if (!class_exists('WC_Rocket_Site_Allocations')) {

    class WC_Rocket_Site_Allocations {

        public static $instance;
        public static $wc_rocket_site_allocations_table = 'wc_rocket_site_allocations';

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

            error_log('Creating allocation with data: ' . print_r($allocation_data, true));

            $result = $wpdb->insert(
                $table_name,
                array(
                    'order_id' => $allocation_data['order_id'],
                    'customer_id' => $allocation_data['customer_id'],
                    'product_id' => $allocation_data['product_id'],
                    'total_sites' => $allocation_data['total_sites'],
                    'sites_created' => 0,
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%d', '%d', '%d', '%d', '%s')
            );

            if ($result === false) {
                error_log('Database error: ' . $wpdb->last_error);
                return false;
            }

            $insert_id = $wpdb->insert_id;
            error_log('Created allocation with ID: ' . $insert_id);
            return $insert_id;
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

        public function get_customer_allocations($customer_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . self::$wc_rocket_site_allocations_table;

            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE customer_id = %d ORDER BY created_at DESC",
                $customer_id
            ));
        }

        public function get_customer_total_allocations($customer_id) {
            $cache_key = 'customer_total_allocations_' . $customer_id;
            $result = wp_cache_get($cache_key, 'wc_rocket');

            if (false === $result) {
                global $wpdb;
                $table_name = $wpdb->prefix . self::$wc_rocket_site_allocations_table;

                $sql = $wpdb->prepare(
                    "SELECT SUM(total_sites) as total_sites
                    FROM $table_name
                    WHERE customer_id = %d",
                    $customer_id
                );

                $result = (int) $wpdb->get_var($sql);
                wp_cache_set($cache_key, $result, 'wc_rocket', HOUR_IN_SECONDS);
            }

            return (int) $result;
        }
    }
}