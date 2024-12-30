<?php

/**
 * Rocket Api Sites List Request
 */
if (!class_exists('WC_Rocket_Api_Sites_List_Request')) {

    class WC_Rocket_Api_Sites_List_Request {

        public static $instance;
        public static $unauthorized_status_code = '401';

        /**
         * rocket login request
         * @return array
         */
        public static function rocket_api_sites_list_request($rocket_auth_token_is_expired = false) {
            $rocket_auth_token = WC_Rocket_Api_Login_Request::get_instance()->get_rocket_auth_token();
            if (!$rocket_auth_token)
                return [];
                                
            $rocket_sites_page_no = '1';
            $rocket_no_of_sites_per_page = '10';
            $rocket_sites_domain = '';
            $rocket_sites_sort_key = '';
            $rocket_direction = '';

            // $request_url = "sites?domain={$rocket_sites_domain}&page={$rocket_sites_page_no}&per_page={$rocket_no_of_sites_per_page}&sort={$rocket_sites_sort_key}&direction={$rocket_direction}";
            $request_url = "sites?page={$rocket_sites_page_no}&per_page={$rocket_no_of_sites_per_page}";
            $request_method = 'GET';
            $request_header = ["Accept: application/json",
                               "Content-Type: application/json" ,
                               "Authorization: Bearer $rocket_auth_token"];

            $reponse = WC_Rocket_Api_Request::get_instance()->rocket_api_curl_request($request_url, $request_method, $request_header);

            if($reponse && !$reponse['error'] && isset( $reponse['response'] ) ){
                $create_response = json_decode($reponse['response']);
                if(isset($create_response->success) && $create_response->success){
                    return $create_response->result;

                }else if(isset($create_response->status) && $create_response->status == self::$unauthorized_status_code && !$rocket_auth_token_is_expired){
                    $token_is_refreshed = WC_Rocket_Api_Login_Request::get_instance()->refresh_rocket_auth_token();
                    if($token_is_refreshed){
                        return self::rocket_api_sites_list_request(true);
                    }
                }
            }
            return [];
        }

        /**
         * get my sites list
         * @return array
         */
        public static function get_my_sites_list(){
            $sites_list = self::rocket_api_sites_list_request();
            return $sites_list;
        }

        /**
         * WC_Rocket_Api_Sites_List_Request instance
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