<?php

/**
 * WC Checkout Rocket
 */
if (!class_exists('WC_Checkout_Rocket')) {

    class WC_Checkout_Rocket {

        public static $instance;

        public function __construct() {
            // wc rocket site checkout fields
            add_filter('woocommerce_checkout_fields', array($this, 'add_rocket_site_fields'));
            // WC add checkout site form fields
            add_action('woocommerce_after_checkout_billing_form', array($this, 'wc_checkout_site_form_fields'));
            // WC checkout save update rocket site on order meta data
            add_action('woocommerce_checkout_update_order_meta', array($this, 'wc_checkout_update_rocket_site_order'), 10, 2);

            // Make sure user registration is required when purchasing rocket products.
            add_filter('woocommerce_checkout_registration_required', array($this, 'require_registration_during_checkout'));
            add_action('woocommerce_before_checkout_process', array($this, 'force_registration_during_checkout'), 10);
            add_filter('woocommerce_checkout_registration_enabled', array($this, 'maybe_enable_registration'), 999);
        }

        /**
         * wc rocket site checkout fields
         * 
         * @param array $fields
         * @return array
         */
        public function add_rocket_site_fields($fields) {
            if (WC_Product_Rocket_General::get_instance()->wc_cart_has_rocket_site_product()) {
                $rocket_site_locations = WC_Rocket_Locations::get_instance()->get_rocket_site_locations();
                $fields['rocket_site'] = array(
                    'wc_rocket_site_name' => array(
                        'type' => 'text',
                        'label' => __('Site Name', 'wc-rocket'),
                        'placeholder' => _x('Site Name', 'placeholder', 'wc-rocket'),
                        'class' => array('form-row-wide', 'wc-rocket-site-field'),
                        'required' => true,
                        'priority' => 10,
                    ),
                    'wc_rocket_site_location' => array(
                        'type' => 'select',
                        'options' => $rocket_site_locations,
                        'label' => __('Site Location', 'wc-rocket'),
                        'placeholder' => _x('1', 'placeholder', 'wc-rocket'),
                        'class' => array('form-row-wide', 'wc-rocket-site-field'),
                        'required' => true,
                        'priority' => 11,
                    )
                );
            }

            return $fields;
        }

        /**
         * WC add checkout site form fields
         * 
         * @param object $checkout
         */
        public function wc_checkout_site_form_fields($checkout) {

            if (WC_Product_Rocket_General::get_instance()->wc_cart_has_rocket_site_product()) {
                wc_rocket_site_get_template(
                        'checkout/rocket-fields.php',
                        array(
                            'checkout' => $checkout
                        )
                );
            }
        }

        /**
         * WC checkout save update rocket site on order meta data
         * 
         * @param int $order_id
         * @param array $data
         */
        public function wc_checkout_update_rocket_site_order($order_id, $data) {
            if (isset($data['wc_rocket_site_name']) && $data['wc_rocket_site_name'] && isset($data['wc_rocket_site_location']) && $data['wc_rocket_site_location']) {
                $order = wc_get_order($order_id);
                if ($order && $order->get_items()) {
                    foreach ($order->get_items() as $item_id => $item) {
                        $product = $item->get_product();
                        if (WC_Product_Rocket_General::get_instance()->check_wc_product_is_rocket($product)) {
                            wc_update_order_item_meta($item_id, 'rocket_site_name', $data['wc_rocket_site_name']);
                            wc_update_order_item_meta($item_id, 'rocket_site_location', $data['wc_rocket_site_location']);
                            break;
                        }
                    }
                }
            }
        }

        /**
         * Enables the 'registeration required' (guest checkout) setting when purchasing rocket product.
         *
         * @param bool $account_required Whether an account is required to checkout.
         * @return bool
         */
        public static function require_registration_during_checkout($account_required) {
            if (WC_Product_Rocket_General::get_instance()->wc_cart_has_rocket_site_product() && !is_user_logged_in()) {
                $account_required = true;
            }

            return $account_required;
        }

        /**
         * During the checkout process, force registration when the cart contains a rocket product.
         *
         * @param $woocommerce_params This parameter is not used.
         */
        public static function force_registration_during_checkout($woocommerce_params) {
            if (WC_Product_Rocket_General::get_instance()->wc_cart_has_rocket_site_product() && !is_user_logged_in()) {
                $_POST['createaccount'] = 1;
            }
        }

        /**
         * Enables registration for carts containing rocket product.
         *
         * @param  bool $registration_enabled Whether registration is enabled on checkout by default.
         * @return bool
         */
        public static function maybe_enable_registration($registration_enabled) {

            // Exit early if regristration is already allowed.
            if ($registration_enabled) {
                return $registration_enabled;
            }

            if (is_user_logged_in() || !WC_Product_Rocket_General::get_instance()->wc_cart_has_rocket_site_product()) {
                return $registration_enabled;
            }

            if (apply_filters('wc_is_registration_enabled_for_rocket_purchases', true)) {
                $registration_enabled = true;
            }

            return true;
        }

        /**
         * WC_Checkout_Rocket instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    WC_Checkout_Rocket::get_instance();
}