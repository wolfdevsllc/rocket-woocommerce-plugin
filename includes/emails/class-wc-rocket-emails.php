<?php

/**
 * class of emails staff 
 */
if (!class_exists('WC_Rocket_Emails')){

    class WC_Rocket_Emails {

        public static $instance;

        /**
         * construct
         */
        function __construct() {
            // Emails.
            add_filter('woocommerce_email_classes', array($this, 'edd_rocket_email_classes'));
            // add rocket email actions
            add_filter('woocommerce_email_actions', [$this, 'add_wc_rocket_email_actions'], 100);
        }

        /**
         * Add wc rocket email classes
         * 
         * @param array $emails
         * @return array
         */
        public function edd_rocket_email_classes($emails) {
            //create rocket site customer email
            require_once 'class-wc-create-rocket-site-email.php';
            $emails['WC_Create_Rocket_Site_Email'] = new WC_Create_Rocket_Site_Email();

            return $emails;
        }

        /**
         * add rocket email actions
         * 
         * @param array $email_actions
         * @return array
         */
        public function add_wc_rocket_email_actions($email_actions) {
            $email_actions[] = 'woocommerce_create_rocket_site_email';
            return $email_actions;
        }

        /**
         * WC_Rocket_Emails instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance)) {

                self::$instance = new self();
            }
            return self::$instance;
        }

    }

    WC_Rocket_Emails::get_instance();

}
