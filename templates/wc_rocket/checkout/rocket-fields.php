<?php
/**
 * Checkout rocket site fields
 *
 * This template can be overridden by copying it to yourtheme/wc_rocket/checkout/rocket-fields.php.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-rocket-site-fields">
	<h3><?php esc_html_e( 'WordPress Site Details', 'wc-rocket' ); ?></h3>

	<?php do_action( 'woocommerce_before_checkout_rocket_site_form', $checkout ); ?>

	<div class="woocommerce-rocket_site-fields__field-wrapper">
		<?php
		$fields = $checkout->get_checkout_fields( 'rocket_site' );

		foreach ( $fields as $key => $field ) {
			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		}
		?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_rocket_site_form', $checkout ); ?>
</div>
