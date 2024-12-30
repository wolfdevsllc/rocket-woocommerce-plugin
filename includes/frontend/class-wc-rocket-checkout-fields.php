<?php

if (!class_exists('WC_Rocket_Checkout_Fields')) {
    class WC_Rocket_Checkout_Fields {
        private static $instance;

        public function __construct() {
            // Remove the fields completely by filtering them out
            add_filter('woocommerce_checkout_fields', array($this, 'remove_rocket_site_fields'));

            // Ensure old actions are removed
            remove_action('woocommerce_before_order_notes', array($this, 'add_rocket_site_fields'));
            remove_action('woocommerce_checkout_process', array($this, 'validate_rocket_site_fields'));
            remove_action('woocommerce_checkout_update_order_meta', array($this, 'save_rocket_site_fields'));
        }

        public function remove_rocket_site_fields($fields) {
            // Remove WordPress Site Details fields if they exist
            if (isset($fields['order']['rocket_site_name'])) {
                unset($fields['order']['rocket_site_name']);
            }
            if (isset($fields['order']['rocket_site_location'])) {
                unset($fields['order']['rocket_site_location']);
            }
            return $fields;
        }

        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}

WC_Rocket_Checkout_Fields::get_instance();