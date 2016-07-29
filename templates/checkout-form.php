<?php
/**
 * Transparent checkout form.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<fieldset id="paypal-plus-brazil-payment-form" class="">
	<?php if ( $gateway->is_valid_customer_info() ): ?>
		<?php $payment_request = $api->do_payment_request( $gateway->get_customer_info() ); ?>
		<?php if ( $payment_request ): ?>
			<input type="hidden" id="paypal-plus-brazil-info" value="<?php echo $gateway->get_customer_info_json_specialchars(); ?>">
			<input type="hidden" id="paypal-plus-brazil-approval-url" value="<?php echo $payment_request['approval_url']; ?>">
			<input type="hidden" id="paypal-plus-brazil-payment-id" name="paypal-plus-brazil-payment-id" value="<?php echo $payment_request['id']; ?>">
			<input type="hidden" id="paypal-plus-brazil-rememberedcards" name="paypal-plus-brazil-rememberedcards" value="">
			<input type="hidden" id="paypal-plus-brazil-payerid" name="paypal-plus-brazil-payerid" value="">
			<div id="ppplus"></div>
		<?php else: ?>
			<p><?php _e( 'Some error trying to generate payment field. Please refresh the page.', 'paypal-plus-brazil-for-woocommerce' ); ?></p>
		<?php endif; ?>
	<?php else: ?>
		<p><?php _e( 'Please, fill all the required fields before continue payment.', 'paypal-plus-brazil-for-woocommerce' ); ?></p>
		<p><a id="update_checkout"><?php _e( 'Click here', 'paypal-plus-brazil-for-woocommerce' ); ?></a> <?php _e( 'in case you already filled everything.', 'paypal-plus-brazil-for-woocommerce' ); ?></p>
	<?php endif; ?>
</fieldset>
