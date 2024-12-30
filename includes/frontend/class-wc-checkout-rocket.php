<?php

/**
 * WC Checkout Rocket
 */
if (!class_exists('WC_Checkout_Rocket')) {

    class WC_Checkout_Rocket {

        private static $instance;

        public function __construct() {
            // Remove these hooks to prevent fields from being added
            remove_action('woocommerce_checkout_fields', array($this, 'add_rocket_site_fields'));
            remove_action('woocommerce_before_order_notes', array($this, 'wc_checkout_site_form_fields'));
        }

        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

    }

    WC_Checkout_Rocket::get_instance();
}