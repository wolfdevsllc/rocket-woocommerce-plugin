<?php
defined('ABSPATH') || exit;

echo '= ' . esc_html($email_heading) . " =\n\n";

echo sprintf(__('Hello %s,', 'wc-rocket'), esc_html($order->get_billing_first_name())) . "\n\n";

echo sprintf(__('Your order has been processed and you now have %d site allocation(s) available in your account.', 'wc-rocket'), $sites_limit) . "\n\n";

echo __('You can create your sites at any time by visiting the My Sites section of your account.', 'wc-rocket') . "\n\n";

echo __('Create Your Sites:', 'wc-rocket') . "\n";
echo esc_url(wc_get_endpoint_url('my-sites', '', wc_get_page_permalink('myaccount'))) . "\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));