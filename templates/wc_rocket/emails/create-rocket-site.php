<?php
/**
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/create-rocket-site.php
 */
defined('ABSPATH') || exit;
?>

<?php do_action('woocommerce_email_header', $email_heading); ?>

<p><?php echo sprintf(__('Hello, %s', 'wc-rocket'), esc_html( $order->get_billing_first_name() )); ?> </p>

<p><?php _e('New site was created with the following details', 'wc-rocket'); ?></p>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
    <tbody>
        <tr>
            <th scope="row" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e('Site Domain', 'wc-rocket'); ?></th>
            <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html($site_domain); ?></td>
        </tr>
        <tr>
            <th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e('Admin User Name', 'wc-rocket'); ?></th>
            <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html($admin_username); ?></td>
        </tr>
        <tr>
            <th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e('Admin Password', 'wc-rocket'); ?></th>
            <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html($admin_password); ?></td>
        </tr>
    </tbody>
</table>


<?php do_action('woocommerce_email_footer', $email); ?>
