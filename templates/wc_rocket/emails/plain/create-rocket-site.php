<?php
/**
 * customer reate new rocket site
 *
 * @since   1.0.0
 * @version 1.1.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

echo '= ' . esc_html($email_heading) . " =\n\n";

echo esc_html(sprintf(__('Hello %s', 'wc-rocket'), esc_html($order->get_billing_first_name()))) . "\n\n";

echo esc_html(__('New site was created with the following details', 'wc-rocket')) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: 1: site domain*/
echo esc_html( sprintf( __( 'Site Domain: %s', 'wc-rocket'), esc_html($site_domain) ) ) . "\n";
/* translators: 1: admin user name */
echo esc_html( sprintf( __( 'Admin User Name: %s', 'wc-rocket'), esc_html($admin_username) ) ) . "\n";
/* translators: 1: admin password */
echo esc_html( sprintf( __( 'Admin Password: %s', 'wc-rocket'), esc_html($admin_password) ) ) . "\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html(apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')));

