<?php

if (!class_exists('WC_Rocket_Frontend')) {
    class WC_Rocket_Frontend {
        private static $instance;

        public function __construct() {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        }

        public function enqueue_scripts() {
            wp_enqueue_script(
                'wc-rocket-my-sites',
                WC_ROCKET_URL . 'assets/js/frontend/my-sites-main-page-script.js',
                array('jquery'),
                WC_ROCKET_VERSION,
                true
            );

            wp_localize_script('wc-rocket-my-sites', 'wc_rocket_params', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_rocket_nonce')
            ));
        }

        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}

WC_Rocket_Frontend::get_instance();