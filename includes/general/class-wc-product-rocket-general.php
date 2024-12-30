<?php

/**
 * WC Product Rocket General
 */
if (!class_exists('WC_Product_Rocket_General')) {

    class WC_Product_Rocket_General {

        public static $instance;

        public static $rocket_product_types = array(
            'simple',
            'variable',
            'subscription',
            'variable-subscription'
        );

        public function __construct() {
            error_log('WC_Product_Rocket_General initialized');
        }

        /**
         * Check if product is a rocket product
         */
        public function check_wc_product_is_rocket($product) {
            if (!$product) {
                error_log('Product is null');
                return false;
            }

            error_log('Checking product ID: ' . $product->get_id());

            // Check if product has sites limit meta
            $sites_limit = get_post_meta($product->get_id(), 'rocket_sites_limit', true);
            error_log('Product sites limit: ' . $sites_limit);

            return !empty($sites_limit);
        }

        /**
         * Get rocket product settings data
         */
        public function get_rocket_product_settings_data($product) {
            return array(
                'disk_space' => get_post_meta($product->get_id(), 'rocket_disk_space', true),
                'bandwidth' => get_post_meta($product->get_id(), 'rocket_bandwidth', true),
                'sites_limit' => get_post_meta($product->get_id(), 'rocket_sites_limit', true)
            );
        }

        /**
         * Check if cart has rocket product
         */
        public function wc_cart_has_rocket_site_product() {
            if (!WC()->cart) {
                return false;
            }

            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($this->check_wc_product_is_rocket($cart_item['data'])) {
                    return true;
                }
            }

            return false;
        }

        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

    }

}

WC_Product_Rocket_General::get_instance();