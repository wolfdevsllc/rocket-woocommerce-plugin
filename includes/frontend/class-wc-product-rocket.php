<?php

/**
 * WC Product Rocket
 */
if (!class_exists('WC_Product_Rocket')) {

    class WC_Product_Rocket {

        public static $instance;

        public function __construct() {
            // add product rocket custom tabs
            add_filter('woocommerce_product_tabs', array($this, 'woocommerce_rocket_product_tabs'), 99);
            // mark rocket product as sold individual
            add_filter('woocommerce_is_sold_individually', array($this, 'wc_rocket_product_sold_individually'), 10, 2);
            // cart must have only one rocket product 
            add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_wc_rocket_product'), 10, 2);
        }

        /**
         * add product rocket custom tabs
         * 
         * @global object $product
         * @return array
         */
        public function woocommerce_rocket_product_tabs($tabs) {
            global $product;
            // check is wc product rocket is enabled
            if (!WC_Product_Rocket_General::get_instance()->check_wc_product_is_rocket($product))
                return $tabs;
            // add product rocket tab
            $tabs['product_rocket'] = array(
                'title' => __('Hosting Details', 'wc-rocket'),
                'priority' => 10,
                'callback' => array($this, 'woocommerce_product_rocket_tab'),
            );
            return $tabs;
        }

        /**
         * display product rocket tab content 
         */
        public function woocommerce_product_rocket_tab() {
            global $product;
            $rocket_product_data = WC_Product_Rocket_General::get_instance()->get_rocket_product_settings_data($product);
            // get product rocket visitors
            $rocket_visitors = $rocket_product_data['visitors'];
            // get product rocket disk space
            $rocket_disk_space = WC_Product_Rocket_Settings::get_rocket_disk_data($rocket_product_data['disk_space']);
            // get product rocket disk space
            $rocket_bandwidth = WC_Product_Rocket_Settings::get_rocket_disk_data($rocket_product_data['bandwidth']);
            
            wc_rocket_site_get_template(
                    'single-product/tabs/product-rocket.php',
                    array(
                            'rocket_visitors' => $rocket_visitors,
                            'rocket_disk_space' => $rocket_disk_space,
                            'rocket_bandwidth' => $rocket_bandwidth
                        )
            );
        }

        /**
         * mark rocket product as sold individual
         * 
         * @param boolean $sold_individually
         * @param object $product
         * @return boolean
         */
        public function wc_rocket_product_sold_individually($sold_individually, $product) {
            if ( WC_Product_Rocket_General::get_instance()->check_wc_product_is_rocket($product) )
                $sold_individually = true;

            return $sold_individually;
        }

        /**
         * cart must have only one rocket product 
         * 
         * @param boolean $passed
         * @param int $product_id
         * @return boolean
         */
        public function validate_wc_rocket_product( $passed, $product_id ) {
            $product = wc_get_product($product_id);
            if ( $product && WC_Product_Rocket_General::get_instance()->check_wc_product_is_rocket($product)) {
                    // get cart items
                    $items = WC()->cart->get_cart();

                    foreach ( $items as $item_key => $item ) {
                        $item_product_id = $item['product_id'];
                        $item_product = wc_get_product($item_product_id);
                        if ( $item_product && WC_Product_Rocket_General::get_instance()->check_wc_product_is_rocket($item_product) ) {
                            WC()->cart->remove_cart_item( $item_key );
                            wc_add_notice( __( 'Must have one rocket product in cart', 'wc-rocket' ), 'notice' );

                        }
                    }
            }

            return $passed;
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

    WC_Product_Rocket::get_instance();
}