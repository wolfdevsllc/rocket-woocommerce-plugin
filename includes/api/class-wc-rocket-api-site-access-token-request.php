<?php

/**
 * Rocket Api Site Access Token Request
 */
if (!class_exists('WC_Rocket_Api_Site_Access_Token_Request')) {

    class WC_Rocket_Api_Site_Access_Token_Request {

        public static $instance;
        public static $unauthorized_status_code = '401';

        /**
         * generate site access token
         * @param int $site_id
         * @return string
         */
        public static function generate_site_access_token($site_id){
            WC_Rocket_Debug::log("Starting token generation for site_id: {$site_id}", 'token_generation');

            $result = self::rocket_api_site_access_token_request($site_id);

            WC_Rocket_Debug::log_var($result, 'API result', 'token_generation');

            if(!empty($result)){
                $token = isset($result["token"]) ? $result["token"] : "";
                WC_Rocket_Debug::log("Token extracted: " . (!empty($token) ? 'YES' : 'NO'), 'token_generation');
                return $token;
            }

            WC_Rocket_Debug::log("Empty result from API request", 'token_generation');
            return "";
        }

        /**
         * rocket site access token request
         * @return array
         */
        public static function rocket_api_site_access_token_request($site_id, $rocket_auth_token_is_expired = false) {
            WC_Rocket_Debug::log("Starting API request for site_id: {$site_id}", 'api_requests');

            $rocket_auth_token = WC_Rocket_Api_Login_Request::get_instance()->get_rocket_auth_token();

            WC_Rocket_Debug::log("Auth token exists: " . (!empty($rocket_auth_token) ? 'YES' : 'NO'), 'api_requests');

            if (!$rocket_auth_token) {
                WC_Rocket_Debug::log("No auth token found - returning empty array", 'api_requests');
                return [];
            }

            $rocket_ttl = 400;

            $request_url = "sites/$site_id/access_token";
            $request_method = "POST";
            $request_header = ["Accept: application/json",
                               "Content-Type: application/json" ,
                               "Authorization: Bearer $rocket_auth_token"];
            $request_fields = apply_filters(
                "wc_site_access_token_rocket",
                [
                    "ttl" => $rocket_ttl,
                ]
            );

            WC_Rocket_Debug::log("Making request to: {$request_url}", 'api_requests');
            WC_Rocket_Debug::log_var($request_fields, 'Request fields', 'api_requests');

            $reponse = WC_Rocket_Api_Request::get_instance()->rocket_api_curl_request($request_url, $request_method, $request_header, json_encode($request_fields));

            WC_Rocket_Debug::log_var($reponse, 'Raw response', 'api_requests');

            if($reponse && !$reponse['error'] && isset( $reponse['response'] ) ){
                $create_response = json_decode($reponse['response']);
                WC_Rocket_Debug::log_var($create_response, 'Decoded response', 'api_requests');

                if(isset($create_response->success) && $create_response->success){
                    WC_Rocket_Debug::log("Success response - returning result", 'api_requests');
                    return (array) $create_response->result;
                }else if(isset($create_response->status) && $create_response->status == self::$unauthorized_status_code && !$rocket_auth_token_is_expired){
                    WC_Rocket_Debug::log("401 Unauthorized - attempting token refresh", 'api_requests');
                    $token_is_refreshed = WC_Rocket_Api_Login_Request::get_instance()->refresh_rocket_auth_token();
                    if($token_is_refreshed){
                        WC_Rocket_Debug::log("Token refreshed - retrying request", 'api_requests');
                        return self::rocket_api_site_access_token_request($site_id,true);
                    }
                } else {
                    WC_Rocket_Debug::log("API returned error or unexpected response structure", 'api_requests');
                }
            } else {
                WC_Rocket_Debug::log("Request failed or returned error", 'api_requests');
            }

            WC_Rocket_Debug::log("Returning empty array", 'api_requests');
            return [];
        }

        /**
         * WC_Rocket_Api_Site_Access_Token_Request instance
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