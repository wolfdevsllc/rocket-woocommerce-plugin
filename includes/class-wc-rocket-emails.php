<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Rocket_Emails', false)) {

    class WC_Rocket_Emails {

        public function __construct() {
            add_filter('woocommerce_email_classes', array($this, 'register_emails'));
        }

        public function register_emails($emails) {
            $emails['WC_Create_Rocket_Site_Email'] = include 'emails/class-wc-create-rocket-site-email.php';
            $emails['WC_Rocket_Allocation_Email'] = include 'emails/class-wc-rocket-allocation-email.php';
            return $emails;
        }
    }
}