<?php

/**
 * Update site_name column of sites table
 */
if (!class_exists('Update_Site_Name_Col')) {

    class Update_Site_Name_Col {

        public static $instance;
        public static $wc_rocket_sites_table = 'wc_rocket_sites';

        public function __construct() {
            // create wc rocket sample product
            add_action('update_site_name_col_of_sites_table', array($this, 'update_site_name_col_of_old_rows'));
        }

        public function update_site_name_col_of_old_rows() {
            global $wpdb;
            $sites_table = $wpdb->prefix . self::$wc_rocket_sites_table;
            $order_item_table = $wpdb->prefix . 'woocommerce_order_items';
            $order_item_meta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
            $sql = "SELECT * FROM {$sites_table}";
            $sites = $wpdb->get_results( $wpdb->prepare( $sql ) );
            foreach ($sites as $key => $site) {
                $order_id = $site->order_id;
                if($order_id != null){
                    $sql = "SELECT meta_value FROM $order_item_table 
                            JOIN $order_item_meta_table 
                            ON $order_item_table.order_item_id = $order_item_meta_table.order_item_id
                            WHERE $order_item_table.order_id = '$order_id'
                            AND $order_item_meta_table.meta_key LIKE '%rocket_site_name%' ";
                    $site_name = $wpdb->get_col( $wpdb->prepare( $sql ) );
                    if(!empty($site_name)){
                        $site_name = $site_name[0];
                        $wpdb->query($wpdb->prepare("UPDATE $sites_table SET site_name='$site_name' WHERE id=$site->id"));
                    }
                }
            }
        }

        /**
         * Update_Site_Name_Col instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    Update_Site_Name_Col::get_instance();
}