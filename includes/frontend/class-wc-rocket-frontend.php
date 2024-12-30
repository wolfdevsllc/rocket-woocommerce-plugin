<?php

if (!class_exists('WC_Rocket_Frontend')) {
    class WC_Rocket_Frontend {
        private static $instance;

        public function __construct() {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 99);
        }

        public function enqueue_scripts() {
            error_log('WC_Rocket_Frontend::enqueue_scripts called');

            // Always enqueue on my-account pages
            if (!is_account_page()) {
                error_log('Not on account page, skipping script enqueue');
                return;
            }

            error_log('Enqueueing WC Rocket scripts and styles');

            // Enqueue CSS
            wp_enqueue_style(
                'wc-rocket-my-sites',
                WC_ROCKET_URL . 'assets/css/frontend/my-sites.css',
                array(),
                WC_ROCKET_VERSION . '.' . time()
            );

            // Register JS first
            wp_register_script(
                'wc-rocket-my-sites',
                WC_ROCKET_URL . 'assets/js/frontend/my-sites-main-page-script.js',
                array('jquery'),
                WC_ROCKET_VERSION . '.' . time(),
                true
            );

            // Then localize
            wp_localize_script('wc-rocket-my-sites', 'wc_rocket_params', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_rocket_nonce'),
                'strings' => array(
                    'no_allocation' => __('No allocation available', 'wc-rocket'),
                    'error_loading' => __('Error loading allocations', 'wc-rocket'),
                    'error_creating' => __('Error creating site', 'wc-rocket')
                )
            ));

            // Finally enqueue
            wp_enqueue_script('wc-rocket-my-sites');

            // Debug output
            error_log('Script URL: ' . WC_ROCKET_URL . 'assets/js/frontend/my-sites-main-page-script.js');
            error_log('Script params: ' . print_r(wp_scripts()->get_data('wc-rocket-my-sites', 'data'), true));
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