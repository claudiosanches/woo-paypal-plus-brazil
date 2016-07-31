<?php
/**
 * Admin View: Notice - Currency not supported.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="error inline">
	<p><strong><?php _e( 'PayPal Plus Brazil Disabled', 'woo-paypal-plus-brazil' ); ?></strong>: <?php printf( __( 'Currency <code>%s</code> is not supported. Works only with Brazilian Real.', 'woo-paypal-plus-brazil' ), get_woocommerce_currency() ); ?></p>
</div>