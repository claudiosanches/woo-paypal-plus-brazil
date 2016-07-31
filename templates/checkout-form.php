<?php
/**
 * Transparent checkout form.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<fieldset id="paypal-plus-brazil-payment-form" class="">
	<?php if ( $gateway->is_valid_customer_info( $order_id ) ): ?>
		<?php $payment_request = $api->do_payment_request( $gateway->get_customer_info( $order_id ) ); ?>
		<?php if ( $payment_request ): ?>
			<input type="hidden" id="paypal-plus-brazil-info" value="<?php echo $gateway->get_customer_info_json_specialchars( $order_id ); ?>">
			<input type="hidden" id="paypal-plus-brazil-approval-url" value="<?php echo $payment_request['approval_url']; ?>">
			<input type="hidden" id="paypal-plus-brazil-payment-id" name="paypal-plus-brazil-payment-id" value="<?php echo $payment_request['id']; ?>">
			<input type="hidden" id="paypal-plus-brazil-rememberedcards" name="paypal-plus-brazil-rememberedcards" value="">
			<input type="hidden" id="paypal-plus-brazil-payerid" name="paypal-plus-brazil-payerid" value="">
			<div id="ppplus"></div>
		<?php else: ?>
			<p><?php _e( 'Some error trying to generate payment field. Please refresh the page.', 'woo-paypal-plus-brazil' ); ?></p>
		<?php endif; ?>
	<?php else: ?>
		<p><?php _e( 'Please, fill all the required fields before continue payment.', 'woo-paypal-plus-brazil' ); ?></p>
		<p><a id="update_checkout"><?php _e( 'Click here', 'woo-paypal-plus-brazil' ); ?></a> <?php _e( 'in case you already filled everything.', 'woo-paypal-plus-brazil' ); ?></p>
	<?php endif; ?>
</fieldset>
