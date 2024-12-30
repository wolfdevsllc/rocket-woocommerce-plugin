<?php

/**
 * WC Product Rocket General
 */
if (!class_exists('WC_Product_Rocket_General')) {

    class WC_Product_Rocket_General {

        public static $instance;
        public static $rocket_product_types = array('simple', 'variable', 'subscription', 'variable-subscription');
        public static $rocket_product_variable_types = array('variation', 'subscription_variation');

        /**
         * check if rocket is enabled for product 
         * @param type $product
         * @return boolean
         */
        public function check_wc_product_is_rocket($product) {
            $enable_rocket = false;

            if ($product && is_a($product, 'WC_Product') && in_array($product->get_type(), $this->wc_rocket_applied_product_types())) {

                if( in_array($product->get_type(), self::$rocket_product_variable_types) )
                    $product_id =  $product->get_parent_id();
                else
                    $product_id = $product->get_id();
                
                $enable_rocket = get_post_meta($product_id, 'enable_rocket', true);
                $enable_rocket = ( $enable_rocket && $enable_rocket == 'yes' ) ? true : false;
            }
            return $enable_rocket;
        }

        /**
         * get product rocket visitors
         * 
         * @param object $product
         * @return string
         */
        public function get_rocket_product_visitors($product) {
            $rocket_visitors = '';
            if ($product && is_a($product, 'WC_Product') && in_array($product->get_type(), $this->wc_rocket_applied_product_types())) {
                if( in_array($product->get_type(), self::$rocket_product_variable_types) )
                    $product_id =  $product->get_parent_id();
                else
                    $product_id = $product->get_id();
                $rocket_visitors = get_post_meta($product_id, 'rocket_visitors', true);
            }
            
            return $rocket_visitors;
        }

        /**
         * get product rocket disk space
         * 
         * @param object $product
         * @return string
         */
        public function get_rocket_product_disk_space($product) {
            $rocket_disk_space = '';
            if ($product && is_a($product, 'WC_Product') && in_array($product->get_type(), $this->wc_rocket_applied_product_types())) {
                if( in_array($product->get_type(), self::$rocket_product_variable_types) )
                    $product_id =  $product->get_parent_id();
                else
                    $product_id = $product->get_id();
                $rocket_disk_space = get_post_meta($product_id, 'rocket_disk_space', true);
            }
            
            return $rocket_disk_space;
        }

        /**
         * get product rocket bandwidth
         * 
         * @param object $product
         * @return string
         */
        public function get_rocket_product_bandwidth($product) {
            
            $rocket_bandwidth = '';
            if ($product && is_a($product, 'WC_Product') && in_array($product->get_type(), $this->wc_rocket_applied_product_types())) {
                if( in_array($product->get_type(), self::$rocket_product_variable_types) )
                    $product_id =  $product->get_parent_id();
                else
                    $product_id = $product->get_id();
                $rocket_bandwidth = get_post_meta($product_id, 'rocket_bandwidth', true);
            }

            return $rocket_bandwidth;
        }

        /**
         * 
         * @param object $product
         * @return array
         */
        public function get_rocket_product_settings_data($product) {
            $rocket_product_data = array(
                'visitors' => '',
                'disk_space' => '',
                'bandwidth' => ''
            );

            if ($this->check_wc_product_is_rocket($product)) {
                $rocket_product_data = array(
                    'visitors' => $this->get_rocket_product_visitors($product),
                    'disk_space' => $this->get_rocket_product_disk_space($product),
                    'bandwidth' => $this->get_rocket_product_bandwidth($product)
                );
            }

            return $rocket_product_data;
        }

        /**
         * check cart has rocket site product
         * @return boolean
         */
        public function wc_cart_has_rocket_site_product() {
            $has_rocket_product = false;

            foreach (WC()->cart->cart_contents as $item_key => $item) {
                $cart_product = wc_get_product($item['product_id']);
                if ($this->check_wc_product_is_rocket($cart_product)) {
                    $has_rocket_product = true;
                    break;
                }
            }

            return $has_rocket_product;
        }
        
        /**
         * get wc rocket applied product types
         * 
         * @return array
         */
        public function wc_rocket_applied_product_types(){
            $applied_product_types = array_merge(self::$rocket_product_types, self::$rocket_product_variable_types);
            
            return $applied_product_types;
        }

        /**
         * WC_Product_Rocket instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

}