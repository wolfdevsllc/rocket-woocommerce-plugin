<?php

/**
 * Rocket Api Site Access Token Request
 */
if (!class_exists('WC_Rocket_Api_Site_Access_Token_Request')) {

    class WC_Rocket_Api_Site_Access_Token_Request {

        public static $instance;
        public static $unauthorized_status_code = '401';

        /**
         * rocket site access token request
         * @return array
         */
        public static function rocket_api_site_access_token_request($site_id, $rocket_auth_token_is_expired = false) {
            $rocket_auth_token = WC_Rocket_Api_Login_Request::get_instance()->get_rocket_auth_token();
            if (!$rocket_auth_token)
                return [];

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

            $reponse = WC_Rocket_Api_Request::get_instance()->rocket_api_curl_request($request_url, $request_method, $request_header, json_encode($request_fields));

            if($reponse && !$reponse['error'] && isset( $reponse['response'] ) ){
                $create_response = json_decode($reponse['response']);
                if(isset($create_response->success) && $create_response->success){
                    return (array) $create_response->result;

                }else if(isset($create_response->status) && $create_response->status == self::$unauthorized_status_code && !$rocket_auth_token_is_expired){
                    $token_is_refreshed = WC_Rocket_Api_Login_Request::get_instance()->refresh_rocket_auth_token();
                    if($token_is_refreshed){
                        return self::rocket_api_site_access_token_request($site_id,true);
                    }
                }
            }
            return [];
        }

        /**
         * generate site access token
         * @param int $site_id
         * @return string
         */
        public static function generate_site_access_token($site_id){
            $result = self::rocket_api_site_access_token_request($site_id);
            if(!empty($result)){
                return isset($result["token"]) ? $result["token"] : "";
            }
            return "";
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