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

            // Enqueue JS
            $script_url = WC_ROCKET_URL . 'assets/js/frontend/my-sites-main-page-script.js';
            error_log('Script URL: ' . $script_url);

            wp_register_script(
                'wc-rocket-my-sites',
                $script_url,
                array('jquery'),
                WC_ROCKET_VERSION . '.' . time(),
                true
            );

            $script_data = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_rocket_nonce')
            );

            wp_localize_script('wc-rocket-my-sites', 'wc_rocket_params', $script_data);
            wp_enqueue_script('wc-rocket-my-sites');

            // Verify enqueued assets
            if (wp_script_is('wc-rocket-my-sites', 'enqueued')) {
                error_log('Script successfully enqueued');
            }
            if (wp_style_is('wc-rocket-my-sites', 'enqueued')) {
                error_log('Styles successfully enqueued');
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

// Initialize the class
add_action('init', function() {
    WC_Rocket_Frontend::get_instance();
});