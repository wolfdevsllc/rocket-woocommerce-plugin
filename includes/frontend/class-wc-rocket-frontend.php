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

            // Add AJAX URL to page
            add_action('wp_head', function() {
                echo '<script type="text/javascript">
                    var ajaxurl = "' . admin_url('admin-ajax.php') . '";
                </script>';
            });

            // Enqueue CSS
            wp_enqueue_style(
                'wc-rocket-my-sites',
                WC_ROCKET_URL . 'assets/css/frontend/my-sites.css',
                array(),
                WC_ROCKET_VERSION . '.' . time()
            );

            // Register and enqueue JS
            wp_enqueue_script(
                'wc-rocket-my-sites',
                WC_ROCKET_URL . 'assets/js/frontend/my-sites-main-page-script.js',
                array('jquery'),
                WC_ROCKET_VERSION . '.' . time(),
                true
            );

            // Localize the script
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

// Initialize the class
add_action('init', function() {
    WC_Rocket_Frontend::get_instance();
});