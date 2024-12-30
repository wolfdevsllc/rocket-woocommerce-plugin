<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Rocket_Allocation_Email', false)) {

    class WC_Rocket_Allocation_Email extends WC_Email {

        public function __construct() {
            $this->id = 'rocket_allocation_email';
            $this->title = __('Site Allocation', 'wc-rocket');
            $this->description = __('This email is sent to customers when new site allocation is created', 'wc-rocket');

            $this->heading = __('Site Allocation Created', 'wc-rocket');
            $this->subject = __('Your Site Allocation is Ready', 'wc-rocket');

            $this->template_base = WC_ROCKET_FILE . 'templates/wc_rocket/';
            $this->template_html = 'emails/site-allocation.php';
            $this->template_plain = 'emails/plain/site-allocation.php';

            add_action('woocommerce_rocket_allocation_created', array($this, 'trigger'), 10, 2);

            parent::__construct();
        }

        public function trigger($order, $sites_limit) {
            $this->object = $order;
            $this->sites_limit = $sites_limit;

            if ($this->is_enabled() && $this->get_recipient()) {
                $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
            }
        }

        public function get_content_html() {
            return wc_get_template_html(
                $this->template_html,
                array(
                    'order' => $this->object,
                    'sites_limit' => $this->sites_limit,
                    'email_heading' => $this->get_heading(),
                    'sent_to_admin' => false,
                    'plain_text' => false,
                    'email' => $this,
                ),
                '',
                $this->template_base
            );
        }

        public function get_content_plain() {
            return wc_get_template_html(
                $this->template_plain,
                array(
                    'order' => $this->object,
                    'sites_limit' => $this->sites_limit,
                    'email_heading' => $this->get_heading(),
                    'sent_to_admin' => false,
                    'plain_text' => true,
                    'email' => $this,
                ),
                '',
                $this->template_base
            );
        }

        public function get_recipient() {
            if (!$this->object instanceof WC_Order) {
                return '';
            }

            return $this->object->get_billing_email();
        }
    }
}