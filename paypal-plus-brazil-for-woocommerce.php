<?php
/**
 * Plugin Name: PayPal Plus Brazil for WooCommerce
 * Plugin URI: https://github.com/eliasjnior/paypal-plus-brazil-for-woocommerce/
 * Description: Easily enable PayPal Plus Checkout (Brazil)
 * Version: 1.0.0
 * Author: Elias Júnior
 * Author URI: https://eliasjrweb.com/
 * Requires at least: 4.4
 * Tested up to: 4.5
 *
 * Text Domain: paypal-plus-brazil-for-woocommerce
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_PayPay_Plus_Brazil' ) ) {

	/**
	 * PayPal Plus Brazil for WooCommerce
	 */
	class WC_PayPay_Plus_Brazil {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '1.0.0';

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin public actions.
		 */
		private function __construct() {

			// Load plugin text domain.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Checks with WooCommerce is installed.
			if ( class_exists( 'WC_Payment_Gateway' ) ) {
				$this->includes();

				add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
				add_filter( 'woocommerce_available_payment_gateways', array( $this, 'hide_when_is_outside_brazil' ) );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
				add_action( 'woocommerce_checkout_update_order_review', array( 'WC_PayPal_Plus_Brazil_Gateway', 'save_customer_info' ) );

				if ( is_admin() ) {
					add_action( 'admin_notices', array( $this, 'ecfb_missing_notice' ) );
				}
			} else {
				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			}
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Get templates path.
		 *
		 * @return string
		 */
		public static function get_templates_path() {
			return plugin_dir_path( __FILE__ ) . 'templates/';
		}

		/**
		 * Load the plugin text domain for translation.
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'paypal-plus-brazil-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Action links.
		 *
		 * @param array $links Action links.
		 *
		 * @return array
		 */
		public function plugin_action_links( $links ) {
			$plugin_links   = array();
			$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paypal-plus-brazil' ) ) . '">' . __( 'Settings', 'paypal-plus-brazil-for-woocommerce' ) . '</a>';

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Includes.
		 */
		private function includes() {
			include_once dirname( __FILE__ ) . '/includes/class-wc-paypal-plus-brazil-gateway.php';
			include_once dirname( __FILE__ ) . '/includes/class-wc-paypal-plus-brazil-api.php';
		}

		/**
		 * Add the gateway to WooCommerce.
		 *
		 * @param  array $methods WooCommerce payment methods.
		 *
		 * @return array Payment methods with PayPal Plus Brazil.
		 */
		public function add_gateway( $methods ) {
			$methods[] = 'WC_PayPal_Plus_Brazil_Gateway';

			return $methods;
		}


		/**
		 * Hide PayPal Plus Brazil when the customer lives outside Brazil.
		 *
		 * @param array $available_gateways Default Available Gateways.
		 *
		 * @return array New Available Gateways.
		 */
		public function hide_when_is_outside_brazil( $available_gateways ) {
			// Remove PayPal Plus Brazil gateway.
			if ( isset( $_REQUEST['country'] ) && 'BR' != $_REQUEST['country'] ) {
				unset( $available_gateways['paypal-plus-brazil'] );
			}

			return $available_gateways;
		}

		/**
		 * WooCommerce Extra Checkout Fields for Brazil notice.
		 */
		public function ecfb_missing_notice() {
			if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
				include dirname( __FILE__ ) . '/includes/admin/views/html-notice-missing-ecfb.php';
			}
		}

		/**
		 * WooCommerce missing notice.
		 */
		public function woocommerce_missing_notice() {
			include dirname( __FILE__ ) . '/includes/admin/views/html-notice-missing-woocommerce.php';
		}

	}

	add_action( 'plugins_loaded', array( 'WC_PayPay_Plus_Brazil', 'get_instance' ) );

}
