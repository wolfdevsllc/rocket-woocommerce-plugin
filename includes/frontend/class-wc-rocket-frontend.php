<?php

if (!class_exists('WC_Rocket_Frontend')) {
    class WC_Rocket_Frontend {
        private static $instance;

        public function __construct() {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 99);
        }

        public function enqueue_scripts() {
            if (!is_account_page()) {
                return;
            }

            // Register and enqueue JS
            wp_enqueue_script(
                'wc-rocket-my-sites',
                WC_ROCKET_URL . 'assets/js/frontend/my-sites-main-page-script.js',
                array('jquery'),
                WC_ROCKET_VERSION . '.' . time(),
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
}

// Initialize the class
add_action('init', function() {
    WC_Rocket_Frontend::get_instance();
});