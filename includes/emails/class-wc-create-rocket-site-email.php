<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WC_Create_Rocket_Site_Email', false)) {

    /**
     * create rocket site email
     *
     * This email is sent to customer when new site is created
     *
     * @class       WC_Create_Rocket_Site_Email
     * @extends     WC_Email
     */
    class WC_Create_Rocket_Site_Email extends WC_Email {

        /**
         * Site domain for the created site
         * @var string
         */
        public $site_domain;

        /**
         * Admin username for the created site
         * @var string
         */
        public $admin_username;

        /**
         * Admin password for the created site
         * @var string
         */
        public $admin_password;

        /**
         * Constructor
         */
        public function __construct() {

            $this->id = 'create_rocket_site_email';
            $this->title = __('Customer Create Site', 'wc-rocket');
            $this->description = __('This email is sent to customer when new site is created', 'wc-rocket');

            $this->heading = __('Create Site', 'wc-rocket');
            $this->subject = __('Create Site', 'wc-rocket');

            // Other settings
            $this->template_base = WC_ROCKET_FILE . 'templates/wc_rocket/';
            $this->template_html = 'emails/create-rocket-site.php';
            $this->template_plain = 'emails/plain/create-rocket-site.php';

            // Triggers for this email
            add_action('woocommerce_create_rocket_site_email_notification', array($this, 'trigger'), 10, 4);

            // Call parent constructor
            parent::__construct();

            // Initialize properties with default values
            $this->site_domain = '';
            $this->admin_username = '';
            $this->admin_password = '';
        }

        /**
         * trigger function.
         */
        public function trigger($order, $site_domain, $admin_username, $admin_password) {
            $this->setup_locale();

            $this->recipient = $order->get_billing_email();

            if (!$this->is_enabled() || !$this->get_recipient()) {
                return;
            }

            $this->object = $order;
            $this->site_domain = $site_domain;
            $this->admin_username = $admin_username;
            $this->admin_password = $admin_password;

            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());

            $this->restore_locale();
        }

        /**
         * get_content_html function.
         *
         * @access public
         * @return string
         */
        public function get_content_html() {
            return wc_get_template_html(
                    $this->template_html,
                    array(
                        'order'              => $this->object,
                        'site_domain'        => $this->site_domain,
                        'admin_username'     => $this->admin_username,
                        'admin_password'     => $this->admin_password,
                        'email_heading'      => $this->get_heading(),
                        'email'              => $this,
                    ),
                    '',
                    $this->template_base
            );
        }

        /**
         * get_content_plain function.
         *
         * @access public
         * @return string
         */
        public function get_content_plain() {
            return wc_get_template_html(
                    $this->template_plain,
                    array(
                        'order'              => $this->object,
                        'site_domain'        => $this->site_domain,
                        'admin_username'     => $this->admin_username,
                        'admin_password'     => $this->admin_password,
                        'email_heading'      => $this->get_heading(),
                        'email'              => $this,
                    ),
                    '',
                    $this->template_base
            );
        }

    }

}