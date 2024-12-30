<?php

if (!class_exists('WC_Rocket_Installer')) {
    class WC_Rocket_Installer {
        private static $instance;

        public function __construct() {
            add_action('plugins_loaded', array($this, 'check_version'));
        }

        public static function install() {
            error_log('Running WC Rocket installer');
            self::create_tables();
            update_option('wc_rocket_version', WC_ROCKET_VERSION);
        }

        public function check_version() {
            if (get_option('wc_rocket_version') != WC_ROCKET_VERSION) {
                self::install();
            }
        }

        private static function create_tables() {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $charset_collate = $wpdb->get_charset_collate();

            // Site Allocations Table
            $allocations_table = $wpdb->prefix . 'wc_rocket_site_allocations';
            $sql_allocations = "CREATE TABLE IF NOT EXISTS $allocations_table (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id BIGINT UNSIGNED NOT NULL,
                customer_id BIGINT UNSIGNED NOT NULL,
                product_id BIGINT UNSIGNED NOT NULL,
                total_sites INT UNSIGNED NOT NULL DEFAULT 0,
                sites_created INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY order_id (order_id),
                KEY customer_id (customer_id),
                KEY product_id (product_id)
            ) $charset_collate;";

            // Sites Table
            $sites_table = $wpdb->prefix . 'wc_rocket_sites';
            $sql_sites = "CREATE TABLE IF NOT EXISTS $sites_table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                site_id INT NOT NULL,
                customer_id INT NOT NULL,
                product_id INT NOT NULL,
                order_id INT NULL,
                domain VARCHAR(255) NOT NULL,
                site_name VARCHAR(255) NULL,
                status INT NOT NULL,
                admin_email VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            // Run the SQL queries
            dbDelta($sql_allocations);
            dbDelta($sql_sites);

            // Verify tables were created
            self::verify_table_creation();
        }

        private static function verify_table_creation() {
            global $wpdb;
            $tables = array(
                'wc_rocket_site_allocations',
                'wc_rocket_sites'
            );

            foreach ($tables as $table) {
                $table_name = $wpdb->prefix . $table;
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                    error_log("Failed to create table: $table_name");
                } else {
                    error_log("Successfully created table: $table_name");
                }
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

WC_Rocket_Installer::get_instance();