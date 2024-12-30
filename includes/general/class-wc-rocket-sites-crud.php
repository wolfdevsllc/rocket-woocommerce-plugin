<?php

/**
 * Rocket Site Crud
 */
if (!class_exists('WC_Rocket_Sites_Crud')) {

    class WC_Rocket_Sites_Crud {

        public static $instance;
        public static $wc_rocket_sites_table = 'wc_rocket_sites';

        /**
         * insert mobile booking buffer data
         *
         * @global object $wpdb
         * @param object $booking
         */
        public static function insert_rocket_new_site_data($site_data) {
            global $wpdb;
            $table_name = $wpdb->prefix . self::$wc_rocket_sites_table;
            $id = $wpdb->insert(
                    $table_name,
                    array(
                        'site_id'     => $site_data['site_id'],
                        'customer_id' => $site_data['customer_id'],
                        'product_id'  => $site_data['product_id'],
                        'order_id'    => $site_data['order_id'],
                        'status'      => WC_ROCKET_ACTIVE_SITE_STATUS,
                        'domain'      => $site_data['domain'],
                        'site_name'      => $site_data['site_name'],
                        'admin_email' => $site_data['admin_email'],
                        'created_at'  => current_time('mysql'),
                        'updated_at'  => current_time('mysql'),
                    ),
                    array(
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    )
            );

        }

        /**
         * get sites from database
         *
         * @global object $wpdb
         * @param int $user_id
         * @param int $status
         * @param int/bool $product_id
         * @param int/bool $order_id
         * @return array
         */
        public static function get_sites_from_rocket_sites_table($user_id = 1, $status = WC_ROCKET_ACTIVE_SITE_STATUS, $product_id= false, $order_id = false ) {
            global $wpdb;
            $table_name = $wpdb->prefix . self::$wc_rocket_sites_table;
            $value = [
                $user_id,
                $status,
            ];

            if($status == WC_ROCKET_ACTIVE_SITE_STATUS){
                $sql = "SELECT * FROM {$table_name} WHERE `customer_id` = %d and `status` = %d";

                if($product_id){
                    $sql .= " and `product_id` = {$product_id}";
                }

                if($order_id){
                    $sql .= " and `order_id` = {$order_id}";
                }


                $sql .= " and deleted_at is null;";

            }else if($status == WC_ROCKET_DELETED_SITE_STATUS){
                $sql = "SELECT * FROM {$table_name} WHERE `customer_id` = %d and `status` = %d and deleted_at is not null;";
            }else{
                return [];
            }


            return $wpdb->get_results( $wpdb->prepare( $sql, $value ) );
        }

        /**
         * (soft) delete site from database
         *
         * @global object $wpdb
         * @param int $site_id
         * @param int $user_id
         * @return int/bool
         */
        public static function delete_site_from_rocket_sites_table($user_id, $site_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . self::$wc_rocket_sites_table;
            return $wpdb->update($table_name,
                        ['deleted_at' => current_time('mysql') ,'status' => WC_ROCKET_DELETED_SITE_STATUS],
                        ['site_id' => $site_id, 'customer_id' => $user_id]
                    );
        }

        /**
         * update rocket site data in database
         *
         * @global object $wpdb
         * @param array $site_data
         * @return boolean
         */
        public static function update_site_from_rocket_sites_table($site_data){
            global $wpdb;
            if( isset($site_data['site_id']) && $site_data['site_id'] ){
                $table_name = $wpdb->prefix . self::$wc_rocket_sites_table;
                $updated_data = [];
                $updated_data_condition = array(
                    'site_id' => $site_data['site_id']
                );

                if(isset($site_data['site_name']) && $site_data['site_name'])
                    $updated_data['site_name'] = $site_data['site_name'];
                if(isset($site_data['user_id']) && $site_data['user_id'])
                    $updated_data_condition['customer_id'] = $site_data['user_id'];

                return $wpdb->update($table_name, $updated_data , $updated_data_condition);
            }

            return false;
        }

        /**
         * check if a user own a site
         *
         * @global object $wpdb
         * @param int $site_id
         * @param int $user_id
         * @param int $status
         * @return int/bool
         */
        public static function is_a_user_own_specific_site($user_id, $site_id, $status = WC_ROCKET_ACTIVE_SITE_STATUS) {
            global $wpdb;
            $table_name = $wpdb->prefix . self::$wc_rocket_sites_table;
            $value = [
                $user_id,
                $site_id,
                $status,
            ];
            $sql = "SELECT * FROM {$table_name} WHERE `customer_id` = %d and `site_id` = %d and `status` = %d";
            $results = $wpdb->get_results( $wpdb->prepare( $sql, $value ) );
            if(isset($results) && !empty($results)){
                return true;
            }
            return false;
        }

        /**
         * WC_Rocket_Sites_Crud instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    WC_Rocket_Sites_Crud::get_instance();
}