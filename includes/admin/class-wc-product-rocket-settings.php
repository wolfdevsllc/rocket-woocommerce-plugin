<?php

/**
 * WC Product Rocket Settings
 */
if (!class_exists('WC_Product_Rocket_Settings')) {

    class WC_Product_Rocket_Settings {

        public static $instance;

        public static $rocket_usage_mb = 1024;

        public function __construct() {
            $this->rocket_fields = array(
                'enable_rocket' => 'checkbox',
                'rocket_visitors' => 'text',
                'rocket_disk_space' => 'text',
                'rocket_bandwidth' => 'text',
                'rocket_plugins_install' => 'textarea',
                'rocket_sites_limit' => 'text'
            );
            add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
            // Add a rocket product tab for simple product only
            add_filter('woocommerce_product_data_tabs', array($this, 'wc_product_rocket_settings_tab'));
            // Add rocket settings tab in admin product page
            add_action('woocommerce_product_data_panels', array($this, 'wc_product_rocket_setting_fields'));
            // Save product rocket setting fields
            add_action('woocommerce_process_product_meta', array($this, 'wc_product_rocket_fields_save'));
        }

        public function register_scripts() {
            global $post;
            $current_screen = get_current_screen();
            if ($post && $current_screen && $current_screen->id == 'product') {
                wp_enqueue_script('admin-product-rocket', WC_ROCKET_URL . '/assets/js/admin/admin-product-rocket.js');
            }
        }

        /**
         * Add a rocket product tab for simple product only
         *
         * @param array $tabs
         * @return array
         */
        public function wc_product_rocket_settings_tab($tabs) {
            $tabs['product_rocket'] = array(
                'label' => __('Rocket Settings', 'wc-rocket'),
                'target' => 'wc_product_rocket_settings',
                'class' => array('show_if_simple', 'show_if_variable', 'show_if_subscription', 'show_if_variable-subscription')
            );

            return $tabs;
        }

        /**
         * add rocket settings tab in admin product page
         */
        public function wc_product_rocket_setting_fields() {

            include WC_ROCKET_FILE . 'templates/admin/products/wc-product-rocket-settings.php';
        }

        /**
         * Save product rocket setting fields
         * @param int $post_id
         */
        public function wc_product_rocket_fields_save($post_id) {
            do_action('wc_product_rocket_fields_before_save', $post_id);

            // if product not simple remove rocket settings
            if ( ! in_array( sanitize_title(stripslashes($_POST['product-type']) ), WC_Product_Rocket_General::$rocket_product_types) ) {
                foreach ($this->rocket_fields as $rocket_field_key => $rocket_field_type) {
                    delete_post_meta($post_id, $rocket_field_key);
                }
                return;
            }

            // if product is simple save rocket settings
            foreach ($this->rocket_fields as $rocket_field_key => $rocket_field_type) {
                switch ($rocket_field_type) {
                    case 'checkbox':
                        $rocket_field_value = isset($_POST[$rocket_field_key]) ? 'yes' : 'no';
                        break;
                    default:
                        $rocket_field_value = isset($_POST[$rocket_field_key]) ? sanitize_text_field($_POST[$rocket_field_key]) : '';
                        break;
                }
                update_post_meta($post_id, $rocket_field_key, $rocket_field_value);
            }
            do_action('wc_product_rocket_fields_after_save', $post_id);
        }

        /**
         * get product default installed plugins
         *
         * @param int $product_id
         * @return string
         */
        public static function get_rocket_product_plugins_install($product_id){
            $product_plugins_install = '';
            if ( $product_id ) {
                $product_plugins_install = get_post_meta($product_id, 'rocket_plugins_install', true);
            }

            return $product_plugins_install;
        }

        /**
         * rocket disk data in (MB, GB)
         *
         * @param int/string $disk_val
         * @return string
         */
        public static function get_rocket_disk_data($disk_val){
            $disk_val = intval($disk_val);
            if($disk_val){
                if($disk_val < self::$rocket_usage_mb){ //disk less than 1024 then disk value in MB
                    $disk_val = $disk_val . ' MB';
                } elseif ( $disk_val < pow(self::$rocket_usage_mb, 2) ) { //disk less than 1024 * 1024 then disk value in TB
                    $disk_val = round($disk_val/self::$rocket_usage_mb, 2) . ' GB';
                } else {
                    $disk_val = round( $disk_val/( pow(self::$rocket_usage_mb, 2) ), 2) . ' TB';
                }
            }else{
                $disk_val = '-';
            }

            return $disk_val;
        }

        /**
         * WC_Product_Rocket_Settings instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    WC_Product_Rocket_Settings::get_instance();
}