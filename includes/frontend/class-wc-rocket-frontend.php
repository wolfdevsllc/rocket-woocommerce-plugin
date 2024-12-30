<?php

if (!class_exists('WC_Rocket_Frontend')) {
    class WC_Rocket_Frontend {
        private static $instance;

        public function __construct() {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        }

        public function enqueue_scripts() {
            // Only enqueue on my-sites page
            if (!is_page('my-sites')) {
                return;
            }

            // First register the script
            wp_register_script(
                'wc-rocket-my-sites',
                WC_ROCKET_URL . 'assets/js/frontend/my-sites-main-page-script.js',
                array('jquery'),
                WC_ROCKET_VERSION . '.' . time(),
                true
            );

            // Then localize it with the required data
            wp_localize_script('wc-rocket-my-sites', 'wc_rocket_params', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_rocket_nonce'),
                'strings' => array(
                    'no_allocation' => __('No allocation available', 'wc-rocket'),
                    'error_loading' => __('Error loading allocations', 'wc-rocket'),
                    'error_creating' => __('Error creating site', 'wc-rocket')
                )
            ));

            // Finally enqueue the script
            wp_enqueue_script('wc-rocket-my-sites');

            // Debug output
            error_log('WC Rocket scripts enqueued with params: ' . print_r(array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_rocket_nonce')
            ), true));
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
WC_Rocket_Frontend::get_instance();