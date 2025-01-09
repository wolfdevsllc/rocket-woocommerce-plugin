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
            // Only log start if there's an error
            $start_time = microtime(true);

            // If token is expired or missing, try to refresh it first
            if ($rocket_auth_token_is_expired) {
                $rocket_auth_token = WC_Rocket_Api_Login_Request::get_instance()->refresh_rocket_auth_token();

                if (!$rocket_auth_token) {
                    WC_Rocket_Api_Request::custom_logs('Failed to refresh auth token', true);
                    return array(
                        'error' => true,
                        'message' => 'Authentication failed. Please log in again.'
                    );
                }

                $rocket_auth_token = WC_Rocket_Api_Login_Request::get_instance()->get_rocket_auth_token(true);
            } else {
                $rocket_auth_token = WC_Rocket_Api_Login_Request::get_instance()->get_rocket_auth_token(true);
            }

            if (!$rocket_auth_token) {
                WC_Rocket_Api_Request::custom_logs('No auth token available', true);
                return array(
                    'error' => true,
                    'message' => 'Authentication failed. Please log in again.'
                );
            }

            // Prepare request data...
            $request_fields = array(
                'domain' => $site_data['domain'],
                'multisite' => false,
                'name' => $site_data['name'],
                'location' => $site_data['location'],
                'admin_username' => $site_data['admin_username'],
                'admin_password' => $site_data['admin_password'],
                'admin_email' => $site_data['admin_email'],
                'install_plugins' => $site_data['install_plugins'],
                'label' => $site_data['label']
            );

            if($site_data['quota']) {
                $request_fields['quota'] = $site_data['quota'];
            }

            if($site_data['bwlimit']) {
                $request_fields['bwlimit'] = $site_data['bwlimit'];
            }

            $request_fields = apply_filters('wc_create_site_rocket', $request_fields);

            $request_url = 'partner/sites';
            $request_method = 'POST';
            $request_header = array(
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . $rocket_auth_token
            );

            $response = WC_Rocket_Api_Request::get_instance()->rocket_api_curl_request($request_url, $request_method, $request_header, json_encode($request_fields));

            if($response && isset($response['response'])) {
                $create_response = is_string($response['response']) ? json_decode($response['response']) : $response['response'];

                // Only log token issues if they occur
                if(isset($create_response->messages) &&
                   (in_array("Login token expired, please log in again.", $create_response->messages) ||
                    in_array("Invalid token, please log in and try again.", $create_response->messages))) {
                    if(!$rocket_auth_token_is_expired) {
                        return self::create_rocket_new_site($site_data, true);
                    }
                }

                if(isset($create_response->success) && $create_response->success) {
                    return array(
                        'error' => false,
                        'response' => $create_response
                    );
                }
            }

            // Log full details only on error
            WC_Rocket_Api_Request::custom_logs('Site creation failed. Time taken: ' . (microtime(true) - $start_time) . 's', true);
            WC_Rocket_Api_Request::custom_logs('Request data: ' . print_r($request_fields, true), true);
            WC_Rocket_Api_Request::custom_logs('Response: ' . print_r($response, true), true);

            return array(
                'error' => true,
                'message' => isset($create_response->messages) ? implode(', ', $create_response->messages) : 'Failed to create site'
            );
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