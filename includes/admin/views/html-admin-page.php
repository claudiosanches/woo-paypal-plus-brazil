<?php
/**
 * Admin options screen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h3><?php echo esc_html( $this->method_title ); ?></h3>

<div class="error inline">
	<p><?php _e( 'To use PayPal Plus you need to have an authorized account to this. Check this before using.', 'woo-paypal-plus-brazil' ); ?></p>
</div>

<?php
if ( 'yes' == $this->get_option( 'enabled' ) ) {
	if ( ! $this->using_supported_currency() && ! class_exists( 'woocommerce_wpml' ) ) {
		include dirname( __FILE__ ) . '/html-notice-currency-not-supported.php';
	}
	if ( '' === $this->client_id ) {
		include dirname( __FILE__ ) . '/html-notice-client-id-missing.php';
	}
	if ( '' === $this->client_secret ) {
		include dirname( __FILE__ ) . '/html-notice-client-secret-missing.php';
	}
}
?>

<?php echo wpautop( $this->method_description ); ?>

<?php include dirname( __FILE__ ) . '/html-admin-help-message.php'; ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>