<?php

if (!class_exists('WC_Rocket_Allocation_Manager')) {

    class WC_Rocket_Allocation_Manager {
        private static $instance;

        public function __construct() {
            add_action('admin_menu', array($this, 'add_allocations_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_action('wp_ajax_adjust_site_allocation', array($this, 'ajax_adjust_site_allocation'));
        }

        public function add_allocations_menu() {
            add_submenu_page(
                'woocommerce',
                __('Site Allocations', 'wc-rocket'),
                __('Site Allocations', 'wc-rocket'),
                'manage_woocommerce',
                'wc-rocket-allocations',
                array($this, 'render_allocations_page')
            );
        }

        public function render_allocations_page() {
            // Get allocations with customer and order details
            global $wpdb;
            $table_name = $wpdb->prefix . WC_Rocket_Site_Allocations::$wc_rocket_site_allocations_table;

            $allocations = $wpdb->get_results("
                SELECT a.*,
                       u.display_name as customer_name,
                       p.post_title as product_name,
                       o.post_status as order_status
                FROM {$table_name} a
                LEFT JOIN {$wpdb->users} u ON a.customer_id = u.ID
                LEFT JOIN {$wpdb->posts} p ON a.product_id = p.ID
                LEFT JOIN {$wpdb->posts} o ON a.order_id = o.ID
                ORDER BY a.created_at DESC
            ");

            include WC_ROCKET_FILE . '/templates/admin/allocations/allocations-page.php';
        }

        public function enqueue_admin_scripts($hook) {
            if ('woocommerce_page_wc-rocket-allocations' !== $hook) {
                return;
            }

            wp_enqueue_style(
                'wc-rocket-admin-allocations',
                WC_ROCKET_URL . 'assets/css/admin/allocations.css',
                array(),
                WC_ROCKET_VERSION
            );

            wp_enqueue_script(
                'wc-rocket-admin-allocations',
                WC_ROCKET_URL . 'assets/js/admin/allocations.js',
                array('jquery'),
                WC_ROCKET_VERSION,
                true
            );

            wp_localize_script('wc-rocket-admin-allocations', 'wcRocketAdmin', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_rocket_admin_nonce'),
                'strings' => array(
                    'confirmAdjust' => __('Are you sure you want to adjust this allocation?', 'wc-rocket'),
                    'success' => __('Allocation updated successfully.', 'wc-rocket'),
                    'error' => __('Error updating allocation.', 'wc-rocket')
                )
            ));
        }

        public function ajax_adjust_site_allocation() {
            check_ajax_referer('wc_rocket_admin_nonce', 'nonce');

            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error(__('Permission denied.', 'wc-rocket'));
            }

            $allocation_id = intval($_POST['allocation_id']);
            $new_total = intval($_POST['total_sites']);

            global $wpdb;
            $table_name = $wpdb->prefix . WC_Rocket_Site_Allocations::$wc_rocket_site_allocations_table;

            // Get current allocation
            $current = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $allocation_id
            ));

            if (!$current) {
                wp_send_json_error(__('Allocation not found.', 'wc-rocket'));
            }

            // Ensure new total is not less than sites already created
            if ($new_total < $current->sites_created) {
                wp_send_json_error(__('New total cannot be less than sites already created.', 'wc-rocket'));
            }

            // Update allocation
            $result = $wpdb->update(
                $table_name,
                array(
                    'total_sites' => $new_total,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $allocation_id),
                array('%d', '%s'),
                array('%d')
            );

            if ($result !== false) {
                wp_send_json_success();
            } else {
                wp_send_json_error(__('Database error.', 'wc-rocket'));
            }
        }

        private function get_row_actions($allocation) {
            $actions = array();

            return $actions;
        }

        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}

WC_Rocket_Allocation_Manager::get_instance();