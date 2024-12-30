<?php

class WC_Rocket_Client_Sites {
    private static $instance;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_item'), 55);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function add_menu_item() {
        add_submenu_page(
            'woocommerce',
            __('Client Sites', 'wc-rocket'),
            __('Client Sites', 'wc-rocket'),
            'manage_woocommerce',
            'wc-rocket-client-sites',
            array($this, 'render_page')
        );
    }

    public function render_page() {
        global $wpdb;

        // Get all sites with their allocation and order information
        $sites = $wpdb->get_results("
            SELECT
                s.*,
                a.order_id,
                u.user_email,
                u.display_name,
                o.post_status as order_status
            FROM {$wpdb->prefix}wc_rocket_sites s
            LEFT JOIN {$wpdb->prefix}wc_rocket_site_allocations a ON s.allocation_id = a.id
            LEFT JOIN {$wpdb->users} u ON s.customer_id = u.ID
            LEFT JOIN {$wpdb->posts} o ON a.order_id = o.ID
            ORDER BY s.created_at DESC
        ");

        // Debug
        error_log('Client Sites Query: ' . $wpdb->last_query);
        error_log('Found Sites: ' . print_r($sites, true));

        // Add filter by order ID if provided
        if (isset($_GET['order_id'])) {
            $order_id = intval($_GET['order_id']);
            $sites = array_filter($sites, function($site) use ($order_id) {
                return $site->order_id == $order_id;
            });
        }

        // Include the template
        if (file_exists(WC_ROCKET_FILE . 'templates/admin/client-sites.php')) {
            include WC_ROCKET_FILE . 'templates/admin/client-sites.php';
        } else {
            error_log('Template file not found: ' . WC_ROCKET_FILE . 'templates/admin/client-sites.php');
            echo '<div class="wrap"><h1>' . __('Client Sites', 'wc-rocket') . '</h1>';
            echo '<div class="notice notice-error"><p>' . __('Template file not found.', 'wc-rocket') . '</p></div></div>';
        }
    }

    public function enqueue_scripts($hook) {
        if ('woocommerce_page_wc-rocket-client-sites' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'wc-rocket-admin-client-sites',
            WC_ROCKET_URL . 'assets/css/admin/client-sites.css',
            array(),
            WC_ROCKET_VERSION
        );

        wp_enqueue_script(
            'wc-rocket-admin-client-sites',
            WC_ROCKET_URL . 'assets/js/admin/client-sites.js',
            array('jquery'),
            WC_ROCKET_VERSION,
            true
        );
    }

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

// Initialize the class
add_action('init', function() {
    WC_Rocket_Client_Sites::get_instance();
});