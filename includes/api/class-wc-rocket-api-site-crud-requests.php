<?php

/**
 * WC Rocket Api Site Crud Requests
 */
if (!class_exists('WC_Rocket_Api_Site_Crud_Requests')) {

    class WC_Rocket_Api_Site_Crud_Requests {

        public static $instance;
        public static $unauthorized_status_code = '401';

        /**
         * create new site on rocket
         *
         * @param array $site_data
         * @return array
         */
        public static function create_rocket_new_site($site_data, $rocket_auth_token_is_expired = false) {
            WC_Rocket_Api_Request::custom_logs('=== Starting API request for site creation ===', false);

            // get rocket auth token
            $rocket_auth_token = WC_Rocket_Api_Login_Request::get_instance()->get_rocket_auth_token();

            // If token is expired or missing, try to refresh it
            if (!$rocket_auth_token || $rocket_auth_token_is_expired) {
                WC_Rocket_Api_Request::custom_logs('Token expired or missing - attempting refresh', false);
                $rocket_auth_token = WC_Rocket_Api_Login_Request::get_instance()->refresh_rocket_auth_token();

                if (!$rocket_auth_token) {
                    WC_Rocket_Api_Request::custom_logs('Failed to refresh auth token', true);
                    return array(
                        'error' => true,
                        'message' => 'Authentication failed. Please log in again.'
                    );
                }
            }

            WC_Rocket_Api_Request::custom_logs('Auth token present: ' . ($rocket_auth_token ? 'Yes' : 'No'), false);

            // change the static param with database options
            $rocket_ttl = 400;

            $request_url = 'partner/sites';
            $request_method = 'POST';
            $request_header = array(
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer $rocket_auth_token"
            );

            // prepare site data
            $request_fields = array(
                'domain' => $site_data['domain'],
                'multisite' => false,
                'name' => $site_data['name'],
                'location' => $site_data['location'],
                'admin_username' => $site_data['admin_username'],
                'admin_password' => $site_data['admin_password'],
                'admin_email' => $site_data['admin_email'],
                'install_plugins' => $site_data['install_plugins']
            );

            // add site quota
            if($site_data['quota']) {
                $request_fields['quota'] = $site_data['quota'];
            }

            // add site bandwidth
            if($site_data['bwlimit']) {
                $request_fields['bwlimit'] = $site_data['bwlimit'];
            }

            $request_fields = apply_filters(
                'wc_create_site_rocket',
                $request_fields
            );

            // Log request details
            WC_Rocket_Api_Request::custom_logs('Request URL: partner/sites', false);
            WC_Rocket_Api_Request::custom_logs('Request Fields: ' . print_r($request_fields, true), false);

            return WC_Rocket_Api_Request::get_instance()->curl_request($request_url, $request_method, $request_header, $request_fields);
        }

       /**
         * delete rocket site request
         * @return array
         */
        public static function delete_rocket_site($site_id, $rocket_auth_token_is_expired = false) {
            $rocket_auth_token = WC_Rocket_Api_Login_Request::get_instance()->get_rocket_auth_token();
            if (!$rocket_auth_token)
                return [];

            $request_url = "sites/$site_id";
            $request_method = "DELETE";
            $request_header = ["Accept: application/json",
                               "Authorization: Bearer $rocket_auth_token"];

            $reponse = WC_Rocket_Api_Request::get_instance()->rocket_api_curl_request($request_url, $request_method, $request_header);

            if($reponse && !$reponse['error'] && isset( $reponse['response'] ) ){
                $create_response = json_decode($reponse['response']);
                if(isset($create_response->success) && $create_response->success){
                    return (array) $create_response->result;

                }else if(isset($create_response->status) && $create_response->status == self::$unauthorized_status_code && !$rocket_auth_token_is_expired){
                    $token_is_refreshed = WC_Rocket_Api_Login_Request::get_instance()->refresh_rocket_auth_token();
                    if($token_is_refreshed){
                        return self::delete_rocket_site($site_id,true);
                    }
                }
            }
            return [];
        }


        /**
         * WC_Rocket_Api_Site_Crud_Requests instance
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