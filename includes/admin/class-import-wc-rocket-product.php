<?php

/**
 * WC Import Rocket Product
 */
if (!class_exists('WC_Import_Rocket_Product')) {

    class WC_Import_Rocket_Product {

        public static $instance;
        public static $import_rocket_sample_product_option = 'import_rocket_sample_product';

        public function __construct() {
            add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
            // create wc rocket sample product
            add_action('admin_notices', array($this, 'create_wc_rocket_sample_product_notice'));
            // hide wc rocket import product notice
            add_action('wp_loaded', array($this, 'hide_notices'));
            // ajax to import wc rocket product
            add_action('wp_ajax_import_wc_rocket_product', array($this, 'import_wc_rocket_product'));
        }

        public function register_scripts() {
            if (!get_option(self::$import_rocket_sample_product_option) && current_user_can(WC_Rocket_Admin_Settings_Page::$rocket_setting_capability)) {
                wp_enqueue_style('rocket-admin-loader');
                wp_enqueue_style('rocket-admin-notices');
                wp_enqueue_script('import-rocket-product', WC_ROCKET_URL . 'assets/js/admin/import-rocket-product.js', array('jquery'), false, true);
                wp_localize_script('import-rocket-product', 'import_rocket_product', array(
                    'ajax_url' => admin_url('admin-ajax.php')
                ));
            }
        }

        /**
         * create wc rocket sample product
         */
        public function create_wc_rocket_sample_product_notice() {
            if (!get_option(self::$import_rocket_sample_product_option) && current_user_can(WC_Rocket_Admin_Settings_Page::$rocket_setting_capability)) {
                $hide_notice_url = esc_url(wp_nonce_url(add_query_arg('wc-rocket-hide-notice', 'hide'), 'wc_rocket_hide_notices_nonce', '_wc_rocket_notice_nonce'));
                $import_nonce = wp_create_nonce('wc_import_rocket_product_nonce');
                require_once WC_ROCKET_FILE . 'templates/admin/notice/import-product-notice.php';
            }
        }

        /**
         * hide wc rocket import product notice
         */
        public function hide_notices() {

            if (isset($_GET['wc-rocket-hide-notice']) && isset($_GET['_wc_rocket_notice_nonce'])) {
                if (!wp_verify_nonce(wc_clean(wp_unslash($_GET['_wc_rocket_notice_nonce'])), 'wc_rocket_hide_notices_nonce')) {
                    wp_die(__('Action failed. Please refresh the page and retry.', 'wc-rocket'));
                }

                if (!current_user_can(WC_Rocket_Admin_Settings_Page::$rocket_setting_capability)) {
                    wp_die(__('Cheatin&#8217; huh?', 'wc-rocket'));
                }

                $notice = wc_clean(wp_unslash($_GET['wc-rocket-hide-notice']));

                if ($notice == 'hide') {
                    update_option(self::$import_rocket_sample_product_option, 'no');
                }
            }
        }

        public function import_wc_rocket_product() {
            $sucess = false;
            $response = array(
                'message' => __('Action failed. Please refresh the page and retry.', 'wc-rocket')
            );
            if (isset($_POST['_wc_rocket_notice_nonce'])) {
                if (!wp_verify_nonce(wc_clean(wp_unslash($_POST['_wc_rocket_notice_nonce'])), 'wc_import_rocket_product_nonce')) {
                    echo wp_send_json_error($response);
                    wp_die();
                }

                if (!current_user_can(WC_Rocket_Admin_Settings_Page::$rocket_setting_capability)) {
                    $response['message'] = __('Cheatin&#8217; huh?', 'wc-rocket');
                    echo wp_send_json_error($response);
                    wp_die();
                }

                // import sample product
                $product_data = array(
                    'title' => __('WordPress Hosting', 'wc-rocket')
                );
                $product_id = $this->create_wc_rocket_product($product_data);
                if ($product_id) {
                    update_option(self::$import_rocket_sample_product_option, 'yes');
                    $response = array(
                        'message' => __('Product imported successfully.', 'wc-rocket'),
                        'product_page' => get_edit_post_link($product_id, 'ajax')
                    );

                    echo wp_send_json_success($response);
                    wp_die();
                }
            }

            echo wp_send_json_error($response);
            wp_die();
        }

        /**
         * create wc rocket product
         * 
         * @param array $data
         * @return int/null
         */
        public function create_wc_rocket_product($data = null) {
            $product_id = null;
            if ($data && class_exists('WC_Product_Simple')) {
                $product = new WC_Product_Simple();
                $product->set_name( sanitize_text_field($data['title']) );
                $product->set_status('publish');
                $product_id = $product->save();

                // If the post was created okay, let's try update the rocket data
                if (!empty($product_id) && function_exists('wc_get_product')) {
                    $rocket_fields = WC_Product_Rocket_Settings::get_instance()->rocket_fields;

                    foreach ($rocket_fields as $rocket_field_key => $rocket_field_type) {
                        switch ($rocket_field_key) {
                            case 'enable_rocket':
                                $rocket_field_value = 'yes';
                                break;
                            case 'rocket_visitors':
                                $rocket_field_value = 1000;
                                break;
                            case 'rocket_disk_space':
                                $rocket_field_value = '1048576';
                                break;
                            case 'rocket_bandwidth':
                                $rocket_field_value = '51200';
                                break;
                            default:
                                $rocket_field_value = '';
                                break;
                        }
                        update_post_meta($product_id, $rocket_field_key, $rocket_field_value);
                    }
                }
            }

            return $product_id;
        }

        /**
         * WC_Import_Rocket_Product instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    WC_Import_Rocket_Product::get_instance();
}