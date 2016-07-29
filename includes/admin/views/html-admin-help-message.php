<?php
/**
 * Admin help message.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( apply_filters( 'woocommerce_paypal_plus_brazil_help_message', true ) ) : ?>
	<div class="updated inline woocommerce-message">
		<p><?php echo esc_html( sprintf( __( 'Help us keep the %s plugin free making a donation or rate %s on WordPress.org. Thank you in advance!', 'paypal-plus-brazil-for-woocommerce' ), __( 'PayPal Plus Brazil for WooCommerce', 'paypal-plus-brazil-for-woocommerce' ), '&#9733;&#9733;&#9733;&#9733;&#9733;' ) ); ?></p>
		<p><a href="http://eliasjrweb.com/doacoes/" target="_blank" class="button button-primary"><?php esc_html_e( 'Make a donation', 'paypal-plus-brazil-for-woocommerce' ); ?></a> <a href="https://wordpress.org/support/view/plugin-reviews/paypal-plus-brazil-for-woocommerce?filter=5#postform" target="_blank" class="button button-secondary"><?php esc_html_e( 'Make a review', 'paypal-plus-brazil-for-woocommerce' ); ?></a></p>
	</div>
<?php endif;