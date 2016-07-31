<?php
/**
 * Admin help message.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( apply_filters( 'woocommerce_paypal_plus_brazil_help_message', true ) ) : ?>
	<div class="updated inline woocommerce-message">
		<p><?php echo esc_html( sprintf( __( 'Help us keep the %s plugin free making a donation or rate %s on WordPress.org. Thank you in advance!', 'woo-paypal-plus-brazil' ), __( 'PayPal Plus Brazil for WooCommerce', 'woo-paypal-plus-brazil' ), '&#9733;&#9733;&#9733;&#9733;&#9733;' ) ); ?></p>
		<p><a href="http://eliasjrweb.com/doacoes/" target="_blank" class="button button-primary"><?php esc_html_e( 'Make a donation', 'woo-paypal-plus-brazil' ); ?></a> <a href="https://wordpress.org/support/view/plugin-reviews/woo-paypal-plus-brazil?filter=5#postform" target="_blank" class="button button-secondary"><?php esc_html_e( 'Make a review', 'woo-paypal-plus-brazil' ); ?></a></p>
	</div>
<?php endif;