<?php

/**
 * WC Rocket Admin General
 */
if (!class_exists('WC_Rocket_Admin_General')) {

    class WC_Rocket_Admin_General {

        public static $instance;

        public function __construct() {
            add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
        }

        public function register_scripts() {
            wp_register_style('rocket-admin-loader', WC_ROCKET_URL . 'assets/css/admin/admin-loader.css');
            wp_register_style('rocket-admin-notices', WC_ROCKET_URL . 'assets/css/admin/admin-notices.css');
        }

        /**
         * WC_Rocket_Admin_General instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    WC_Rocket_Admin_General::get_instance();
}