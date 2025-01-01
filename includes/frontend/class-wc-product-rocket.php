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
         * Add rocket product tab
         */
        public function woocommerce_rocket_product_tabs($tabs) {
            global $product;

            if (!WC_Product_Rocket_General::get_instance()->check_wc_product_is_rocket($product)) {
                return $tabs;
            }

            $tabs['rocket'] = array(
                'title' => __('Rocket Details', 'wc-rocket'),
                'priority' => 50,
                'callback' => array($this, 'wc_rocket_product_tab_content')
            );

            return $tabs;
        }

        /**
         * Rocket product tab content
         */
        public function wc_rocket_product_tab_content() {
            global $product;

            // Get rocket product settings
            $rocket_settings = array(
                'rocket_visitors' => get_post_meta($product->get_id(), 'rocket_visitors', true),
                'rocket_disk_space' => get_post_meta($product->get_id(), 'rocket_disk_space', true),
                'rocket_bandwidth' => get_post_meta($product->get_id(), 'rocket_bandwidth', true)
            );

            // Format the values for display
            $rocket_visitors = !empty($rocket_settings['rocket_visitors']) ? number_format($rocket_settings['rocket_visitors']) : '0';
            $rocket_disk_space = !empty($rocket_settings['rocket_disk_space']) ? size_format($rocket_settings['rocket_disk_space'] * MB_IN_BYTES) : '0 MB';
            $rocket_bandwidth = !empty($rocket_settings['rocket_bandwidth']) ? size_format($rocket_settings['rocket_bandwidth'] * MB_IN_BYTES) : '0 MB';

            // Include the template
            include WC_ROCKET_FILE . 'templates/wc_rocket/single-product/tabs/product-rocket.php';
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
                            $notice_message = apply_filters('wc_rocket_single_product_notice', __( 'Must have one rocket product in cart', 'wc-rocket' ));
                            wc_add_notice($notice_message, 'notice');

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
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

    }

    WC_Product_Rocket::get_instance();
}