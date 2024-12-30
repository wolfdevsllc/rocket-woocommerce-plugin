<?php

if (!class_exists('WC_Rocket_Product_Admin')) {
    class WC_Rocket_Product_Admin {
        private static $instance;

        public function __construct() {
            // Add product data tabs
            add_filter('woocommerce_product_data_tabs', array($this, 'add_rocket_product_data_tab'));

            // Add product data fields
            add_action('woocommerce_product_data_panels', array($this, 'add_rocket_product_data_fields'));

            // Save product data
            add_action('woocommerce_process_product_meta', array($this, 'save_rocket_product_data'));
        }

        public function add_rocket_product_data_tab($tabs) {
            $tabs['rocket'] = array(
                'label' => __('Rocket Settings', 'wc-rocket'),
                'target' => 'rocket_product_data',
                'class' => array('show_if_simple', 'show_if_variable'),
            );
            return $tabs;
        }

        public function add_rocket_product_data_fields() {
            global $post;
            ?>
            <div id="rocket_product_data" class="panel woocommerce_options_panel">
                <?php
                woocommerce_wp_text_input(array(
                    'id' => 'rocket_sites_limit',
                    'label' => __('Sites Limit', 'wc-rocket'),
                    'description' => __('Number of sites allowed for this product', 'wc-rocket'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'min' => '0',
                        'step' => '1'
                    ),
                    'desc_tip' => true,
                ));

                woocommerce_wp_text_input(array(
                    'id' => 'rocket_disk_space',
                    'label' => __('Disk Space (MB)', 'wc-rocket'),
                    'description' => __('Disk space limit in megabytes', 'wc-rocket'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'min' => '0',
                        'step' => '1'
                    ),
                    'desc_tip' => true,
                ));

                woocommerce_wp_text_input(array(
                    'id' => 'rocket_bandwidth',
                    'label' => __('Bandwidth (MB)', 'wc-rocket'),
                    'description' => __('Monthly bandwidth limit in megabytes', 'wc-rocket'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'min' => '0',
                        'step' => '1'
                    ),
                    'desc_tip' => true,
                ));
                ?>
            </div>
            <?php
        }

        public function save_rocket_product_data($post_id) {
            $fields = array('rocket_sites_limit', 'rocket_disk_space', 'rocket_bandwidth');

            foreach ($fields as $field) {
                $value = isset($_POST[$field]) ? absint($_POST[$field]) : '';
                update_post_meta($post_id, $field, $value);
            }
        }

        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}

WC_Rocket_Product_Admin::get_instance();