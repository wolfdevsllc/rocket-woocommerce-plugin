<?php

if (!class_exists('WC_Rocket_Installer')) {
    class WC_Rocket_Installer {
        private static $instance;

        public function __construct() {
            // Run installer on plugin activation
            register_activation_hook(WC_ROCKET_FILE, array($this, 'install'));

            // Check if we need to run updates
            add_action('plugins_loaded', array($this, 'check_version'));
        }

        public function install() {
            error_log('Running WC Rocket installer');
            $this->create_tables();

            // Store current version
            update_option('wc_rocket_version', WC_ROCKET_VERSION);
        }

        public function check_version() {
            if (get_option('wc_rocket_version') != WC_ROCKET_VERSION) {
                $this->install();
            }
        }

        private function create_tables() {
            global $wpdb;

            $wpdb->hide_errors();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $collate = $wpdb->has_cap('collation') ? $wpdb->get_charset_collate() : '';

            $tables = "
            CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wc_rocket_site_allocations (
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
            ) $collate;";

            error_log('Creating tables with SQL: ' . $tables);

            // Run the SQL
            dbDelta($tables);

            // Check if table was created
            $table_name = $wpdb->prefix . 'wc_rocket_site_allocations';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                error_log('Failed to create table: ' . $table_name);
            } else {
                error_log('Successfully created table: ' . $table_name);
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