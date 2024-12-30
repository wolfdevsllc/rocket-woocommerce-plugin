<?php

if (!class_exists('WC_Rocket_Frontend')) {
    class WC_Rocket_Frontend {
        private static $instance;

        public function __construct() {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

            // Add version string to all scripts and styles
            add_filter('script_loader_src', array($this, 'add_cache_busting'), 10, 2);
            add_filter('style_loader_src', array($this, 'add_cache_busting'), 10, 2);
        }

        public function enqueue_scripts() {
            // Register and enqueue main script
            wp_register_script(
                'wc-rocket-my-sites',
                WC_ROCKET_URL . 'assets/js/frontend/my-sites-main-page-script.js',
                array('jquery'),
                WC_ROCKET_VERSION,
                true
            );

            // Localize script
            wp_localize_script('wc-rocket-my-sites', 'wc_rocket_params', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_rocket_nonce')
            ));

            wp_enqueue_script('wc-rocket-my-sites');

            // Register and enqueue other scripts if needed
            wp_register_script(
                'wc-rocket-manage-site',
                WC_ROCKET_URL . 'assets/js/frontend/manage-site-second-script.js',
                array('jquery'),
                WC_ROCKET_VERSION,
                true
            );
        }

        /**
         * Add timestamp to script and style URLs to prevent caching during development
         */
        public function add_cache_busting($src, $handle) {
            if (strpos($handle, 'wc-rocket') !== false) {
                $src = add_query_arg('ver', time(), $src);
            }
            return $src;
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