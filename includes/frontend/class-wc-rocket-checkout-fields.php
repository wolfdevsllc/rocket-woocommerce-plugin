<?php

if (!class_exists('WC_Rocket_Checkout_Fields')) {
    class WC_Rocket_Checkout_Fields {
        private static $instance;

        public function __construct() {
            // Remove the old site creation fields from checkout
            remove_action('woocommerce_before_order_notes', array($this, 'add_rocket_site_fields'));
            remove_action('woocommerce_checkout_process', array($this, 'validate_rocket_site_fields'));
            remove_action('woocommerce_checkout_update_order_meta', array($this, 'save_rocket_site_fields'));
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