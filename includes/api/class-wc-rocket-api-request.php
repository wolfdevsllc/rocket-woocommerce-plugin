<?php

/**
 * Rocket Api Request
 */
if (!class_exists('WC_Rocket_Api_Request')) {

    class WC_Rocket_Api_Request {

        public static $instance;

        public static function rocket_api_curl_request($request_url, $request_method = 'GET', $request_header = array(), $post_fields = array()) {
            try {
                $url = 'https://api.rocket.net/v1/' . $request_url;
                // init curl request
                $curl = curl_init();
                // set curl options
                $curl_options = array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => "",
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => $request_method,
                    CURLOPT_HTTPHEADER     => $request_header,
                );
                if ($request_method == 'POST')
                    $curl_options[CURLOPT_POSTFIELDS] = $post_fields;

                curl_setopt_array($curl, $curl_options);
                // excute curl request
                $response = curl_exec($curl);
                // check curl error
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    return array(
                        'error' => true,
                        'message' => $err
                    );
                    self::custom_logs($err);
                } else {
                    $message = 'Curl request url ' . $url . ' With method ' . $request_method . ' Response '. serialize($response);
                    self::custom_logs($message, false);
                    return array(
                        'error' => false,
                        'response' => $response
                    );
                }
            } catch (Exception $ex) {
                self::custom_logs($ex->getMessage());
            }
        }

        /**
         * function to create log file for rocket api request error messages
         * 
         * @param string $message
         * @param bool $messageSent
         */
        public static function custom_logs($message, $error = true) {

            $logger = wc_get_logger();
            $log_source_text = 'rocket-api-log';
            if (!$error) {
                $logger->info(trim(preg_replace('/\s\s+/', ' ', $message)) . "\n", array('source' => $log_source_text));
            } else {
                $logger->warning(trim(preg_replace('/\s\s+/', ' ', $message)) . "\n", array('source' => $log_source_text));
            }
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
}