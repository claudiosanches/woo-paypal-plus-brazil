<?php
/**
 * Admin View: Notice - Currency not supported.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="error inline">
	<p><strong><?php _e( 'PayPal Plus Brazil Disabled', 'paypal-plus-brazil-for-woocommerce' ); ?></strong>: <?php printf( __( 'Currency <code>%s</code> is not supported. Works only with Brazilian Real.', 'paypal-plus-brazil-for-woocommerce' ), get_woocommerce_currency() ); ?></p>
</div>