<?php

if (!class_exists('WC_Rocket_Checkout_Fields')) {
    class WC_Rocket_Checkout_Fields {
        private static $instance;

        public function __construct() {
            // No need for any actions or filters since fields aren't being added
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