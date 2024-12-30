<?php

/**
 * WC Rocket Locations
 */
if (!class_exists('WC_Rocket_Locations')) {

    class WC_Rocket_Locations {

        public static $instance;

        public function get_rocket_site_locations() {
            $rocket_site_locations = apply_filters('wc_rocket_site_locations',
                    array(
                        '' => __('Select Site Location', 'wc-rocket'),
                        4  => 'GB-UKM - London',
                        7  => 'DE - Frankfurt',
                        8  => 'NL - Amsterdam',
                        12 => 'US - Dallas',
                        16 => 'AU - Sydney',
                        20 => 'SG - Singapore',
                        21 => 'US - Ashburn',
                        22 => 'US - Phoenix'
                    )
            );

            return $rocket_site_locations;
        }

        /**
         * WC_Rocket_Locations instance
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
