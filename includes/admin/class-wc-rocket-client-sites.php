<?php

class WC_Rocket_Client_Sites {
    private static $instance;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_item'), 55);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Add table creation on plugin activation
        add_action('init', array($this, 'create_tables'));
    }

    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Sites table
        $table_name = $wpdb->prefix . 'wc_rocket_sites';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            site_name varchar(255) NOT NULL,
            customer_id bigint(20) NOT NULL,
            allocation_id bigint(20) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY customer_id (customer_id),
            KEY allocation_id (allocation_id)
        ) $charset_collate;";

        // Allocations table
        $table_allocations = $wpdb->prefix . 'wc_rocket_site_allocations';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_allocations (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            customer_id bigint(20) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            used int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY customer_id (customer_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Log table creation
        error_log('Creating/Updating WC Rocket tables');
        error_log('Last DB Error: ' . $wpdb->last_error);
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

        // Debug table structure
        $table_structure = $wpdb->get_results("DESCRIBE {$wpdb->prefix}wc_rocket_sites");
        error_log('Sites table structure: ' . print_r($table_structure, true));

        // Updated query without allocation_id
        $sites = $wpdb->get_results("
            SELECT
                s.*,
                a.order_id,
                u.user_email,
                u.display_name,
                o.post_status as order_status
            FROM {$wpdb->prefix}wc_rocket_sites s
            LEFT JOIN {$wpdb->prefix}wc_rocket_site_allocations a ON s.site_allocation_id = a.id
            LEFT JOIN {$wpdb->users} u ON s.customer_id = u.ID
            LEFT JOIN {$wpdb->posts} o ON a.order_id = o.ID
            ORDER BY s.id DESC
        ");

        error_log('Query executed: ' . $wpdb->last_query);
        error_log('Query error if any: ' . $wpdb->last_error);
        error_log('Results: ' . print_r($sites, true));

        // Add filter by order ID if provided
        if (isset($_GET['order_id'])) {
            $order_id = intval($_GET['order_id']);
            $sites = array_filter($sites, function($site) use ($order_id) {
                return $site->order_id == $order_id;
            });
        }

        // Include the template
        include WC_ROCKET_FILE . 'templates/admin/client-sites.php';
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