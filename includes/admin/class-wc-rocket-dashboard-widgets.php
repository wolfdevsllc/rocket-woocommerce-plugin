<?php

if (!class_exists('WC_Rocket_Dashboard_Widgets')) {
    class WC_Rocket_Dashboard_Widgets {
        private static $instance;

        public function __construct() {
            add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
        }

        public function add_dashboard_widgets() {
            if (current_user_can('manage_woocommerce')) {
                wp_add_dashboard_widget(
                    'wc_rocket_allocation_stats',
                    __('Rocket Site Allocations', 'wc-rocket'),
                    array($this, 'allocation_stats_widget')
                );
            }
        }

        public function allocation_stats_widget() {
            global $wpdb;
            $table_name = $wpdb->prefix . WC_Rocket_Site_Allocations::$wc_rocket_site_allocations_table;

            // Get overall stats
            $stats = $wpdb->get_row("
                SELECT
                    COUNT(DISTINCT customer_id) as total_customers,
                    SUM(total_sites) as total_allocated,
                    SUM(sites_created) as total_created,
                    SUM(total_sites - sites_created) as total_remaining
                FROM $table_name
            ");

            // Get recent allocations
            $recent = $wpdb->get_results("
                SELECT a.*, u.display_name as customer_name
                FROM $table_name a
                LEFT JOIN {$wpdb->users} u ON a.customer_id = u.ID
                ORDER BY a.created_at DESC
                LIMIT 5
            ");

            include WC_ROCKET_FILE . '/templates/admin/dashboard/allocation-stats-widget.php';
        }

        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}

WC_Rocket_Dashboard_Widgets::get_instance();