<?php

/**
 * Rocket Api Login Request
 */
if (!class_exists('WC_Rocket_Api_Login_Request')) {

    class WC_Rocket_Api_Login_Request {

        public static $instance;

        /**
         * rocket login request
         * @return string
         */
        public static function rocket_api_login_request() {
            $rocket_email = WC_Rocket_Admin_Settings_Page::get_rocket_email();
            $rocket_password = WC_Rocket_Admin_Settings_Page::get_rocket_password();

            if ($rocket_email && $rocket_password) {
                $request_url = 'login';
                $request_method = 'POST';
                $request_header = ["Accept: application/json", "Content-Type: application/json"];
                $request_fields = apply_filters(
                        'wc_login_rocket',
                        [
                            "username" => $rocket_email,
                            "password" => $rocket_password
                        ]
                );

                $reponse = WC_Rocket_Api_Request::get_instance()->rocket_api_curl_request($request_url, $request_method, $request_header, json_encode($request_fields));

                if ($reponse && !$reponse['error'] && isset($reponse['response'])) {
                    $create_response = json_decode($reponse['response']);
                    if (isset($create_response->token) && $create_response->token) {
                        return $create_response->token;
                    }
                }
            }

            return "";
        }

        /**
         * refresh rocket auth token
         * @return bool/string
         */
        public static function refresh_rocket_auth_token() {
            $token = self::rocket_api_login_request();
            if ($token && $token != "") {
                $sodium_crypto_data = wc_rocket_sodium_crypto_data();
                $encryption_key = sodium_crypto_box_keypair_from_secretkey_and_publickey($sodium_crypto_data['keypair1_secret'], $sodium_crypto_data['keypair2_public']);
                $encrypted = sodium_crypto_box($token, $sodium_crypto_data['nonce'], $encryption_key);
                $token = base64_encode($encrypted);
                // save rocket password public an secret keys
                update_option('wc_rocket_token_key1', base64_encode($sodium_crypto_data['keypair1_public']));
                update_option('wc_rocket_token_key2', base64_encode($sodium_crypto_data['keypair2_secret']));
                update_option('wc_rocket_token_nonce', base64_encode($sodium_crypto_data['nonce']));
                update_option('rocket_auth_token', $token);
                return $token;
            } else {
                update_option('rocket_auth_token', '');
                delete_option('wc_rocket_token_key1');
                delete_option('wc_rocket_token_key2');
                delete_option('wc_rocket_token_nonce');
            }
            return false;
        }

        /**
         * get rocket auth token
         * @return string
         */
        public static function get_rocket_auth_token() {
            try {
                $rocket_auth_token = get_option('rocket_auth_token');
                if (!$rocket_auth_token) {
                    $rocket_auth_token = self::refresh_rocket_auth_token();
                }
                if($rocket_auth_token){
                    $rocket_auth_token = base64_decode($rocket_auth_token);
                    // decrypt saved password
                    $keypair1_public = base64_decode(get_option('wc_rocket_token_key1'));
                    $keypair2_secret = base64_decode(get_option('wc_rocket_token_key2'));
                    $nonce = base64_decode(get_option('wc_rocket_token_nonce', true));
                    if($keypair1_public && $keypair2_secret && $nonce){
                        $decryption_key = sodium_crypto_box_keypair_from_secretkey_and_publickey($keypair2_secret, $keypair1_public);
                        $rocket_password = sodium_crypto_box_open($rocket_auth_token, $nonce, $decryption_key);
                    }
                }
                return $rocket_password;
            } catch (Exception $e) {
                return '';
            }
            
        }

        /**
         * WC_Rocket_Api_Login_Request instance
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