<?php

/**
 * WC Rocket Debug Helper
 * Easily enable/disable debugging for token generation
 */
if (!class_exists('WC_Rocket_Debug')) {
    class WC_Rocket_Debug {

        // Set to false to disable all debugging
        private static $debug_enabled = true;

        // Specific debug categories
        private static $debug_categories = [
            'token_generation' => true,
            'api_requests' => true,
            'site_validation' => true,
        ];

        /**
         * Log debug message if debugging is enabled
         */
        public static function log($message, $category = 'general') {
            if (!self::$debug_enabled) {
                return;
            }

            if (isset(self::$debug_categories[$category]) && !self::$debug_categories[$category]) {
                return;
            }

            error_log("WC_ROCKET_DEBUG [{$category}]: " . $message);
        }

        /**
         * Log variable dump
         */
        public static function log_var($var, $label = 'Variable', $category = 'general') {
            if (!self::$debug_enabled) {
                return;
            }

            self::log($label . ': ' . print_r($var, true), $category);
        }

        /**
         * Enable/disable debugging
         */
        public static function set_debug_enabled($enabled) {
            self::$debug_enabled = $enabled;
        }

        /**
         * Enable/disable specific category
         */
        public static function set_category_enabled($category, $enabled) {
            self::$debug_categories[$category] = $enabled;
        }
    }
}