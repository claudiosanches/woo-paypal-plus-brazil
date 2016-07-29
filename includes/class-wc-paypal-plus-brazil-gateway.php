<?php
/**
 * WooCommerce PayPal Plus Brazil Gateway class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_PayPal_Plus_Brazil_Gateway class.
 */
class WC_PayPal_Plus_Brazil_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'paypal-plus-brazil';
		$this->icon               = apply_filters( 'woocommerce_paypal_plus_brazil_icon', plugins_url( 'assets/images/paypal-plus.png', plugin_dir_path( __FILE__ ) ) );
		$this->method_title       = __( 'PayPal Plus Brazil', 'paypal-plus-brazil-for-woocommerce' );
		$this->method_description = __( 'Accept payments by credit card, bank debit or banking ticket using PayPal Plus.', 'paypal-plus-brazil-for-woocommerce' );
		$this->order_button_text  = __( 'Confirm payment', 'paypal-plus-brazil-for-woocommerce' );
		$this->has_fields         = true;

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title                 = $this->get_option( 'title' );
		$this->description           = $this->get_option( 'description' );
		$this->client_id             = $this->get_option( 'client_id' );
		$this->client_secret         = $this->get_option( 'client_secret' );
		$this->experience_profile_id = $this->get_option( 'experience_profile_id' );
		$this->sandbox               = $this->get_option( 'sandbox', 'no' );
		$this->debug                 = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			$this->log = new WC_Logger();
		}

		// Set the API.
		$this->api = new WC_PayPal_Plus_Brazil_API( $this );

		// Main actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_scripts' ) );
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	public function using_supported_currency() {
		return 'BRL' === get_woocommerce_currency();
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$available = 'yes' === $this->get_option( 'enabled' ) && '' !== $this->client_secret && '' !== $this->client_id && $this->using_supported_currency();

		return $available;
	}

	/**
	 * Checkout scripts.
	 */
	public function checkout_scripts() {
		if ( is_checkout() && $this->is_available() ) {
			if ( ! get_query_var( 'order-received' ) ) {
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

				wp_enqueue_style( 'paypal-plus-brazil-checkout', plugins_url( 'assets/css/checkout.css', plugin_dir_path( __FILE__ ) ), array(), WC_PayPay_Plus_Brazil::VERSION, 'all' );
				wp_enqueue_script( 'paypal-plus-library', '//www.paypalobjects.com/webstatic/ppplusdcc/ppplusdcc.min.js', array(), null, true );
				wp_enqueue_script( 'paypal-plus-brazil-checkout', plugins_url( 'assets/js/checkout' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery', 'paypal-plus-library' ), WC_PayPay_Plus_Brazil::VERSION, true );

				wp_localize_script(
					'paypal-plus-brazil-checkout',
					'wc_ppb_params',
					array(
						'mode'                      => 'yes' === $this->sandbox ? 'sandbox' : 'live',
						'remembered_cards'          => $this->get_customer_cards(),
						'paypal_loading_bg_color'   => $this->filter_hex_color( $this->get_option( 'loading_bg_color' ) ),
						'paypal_loading_bg_opacity' => $this->filter_opacity( $this->get_option( 'loading_bg_opacity' ) ),
						'paypal_loading_message'    => __( 'Loading PayPal...', 'paypal-plus-brazil-for-woocommerce' ),
						'paypal_plus_not_available' => __( 'PayPal Plus is not active for this PayPal account. Please contact us and try another payment method.', 'paypal-plus-brazil-for-woocommerce' ),
						'check_entry'               => __( 'Please fill all required fields.', 'paypal-plus-brazil-for-woocommerce' ),
						'unknown_error'             => __( 'Unknown error. Please contact us and try another payment method.', 'paypal-plus-brazil-for-woocommerce' ),
						'unknown_error_json'        => __( 'Unknown error in PayPal response. Please contact us and try another payment method.', 'paypal-plus-brazil-for-woocommerce' ),
					)
				);
			}
		}
	}

	/**
	 * Get log.
	 *
	 * @return string
	 */
	protected function get_log_view() {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2', '>=' ) ) {
			return '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'paypal-plus-brazil-for-woocommerce' ) . '</a>';
		}

		return '<code>woocommerce/logs/' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.txt</code>';
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'               => array(
				'title'   => __( 'Enable/Disable', 'paypal-plus-brazil-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable PayPal Plus Brazil', 'paypal-plus-brazil-for-woocommerce' ),
				'default' => 'yes',
			),
			'title'                 => array(
				'title'       => __( 'Title', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'paypal-plus-brazil-for-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'PayPal Plus', 'paypal-plus-brazil-for-woocommerce' ),
			),
			'description'           => array(
				'title'       => __( 'Description', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'paypal-plus-brazil-for-woocommerce' ),
				'default'     => __( 'Pay via PayPal Plus', 'paypal-plus-brazil-for-woocommerce' ),
			),
			'sandbox'               => array(
				'title'       => __( 'PayPal Plus Sandbox', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable PayPal Plus Sandbox', 'paypal-plus-brazil-for-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'no',
				'description' => __( 'PayPal Plus Sandbox can be used to test the payments.', 'paypal-plus-brazil-for-woocommerce' ),
			),
			'client_id'             => array(
				'title'       => __( 'PayPal Plus Client ID', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Please enter your PayPal Plus client ID.', 'paypal-plus-brazil-for-woocommerce' ),
				'default'     => '',
			),
			'client_secret'         => array(
				'title'       => __( 'PayPal Plus Client Secret', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Please enter your PayPal Plus Client Secret.', 'paypal-plus-brazil-for-woocommerce' ),
				'default'     => '',
			),
			'experience_profile_id' => array(
				'title'       => __( 'PayPal Plus Experience Profile ID', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( "Please enter your PayPal Plus Experience Profile ID. Leave empty if you don't have, the API will get one.", 'paypal-plus-brazil-for-woocommerce' ),
				'default'     => '',
			),
			'design'                => array(
				'title'       => __( 'Design', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'loading_bg_color'      => array(
				'title'       => __( 'Loading background color', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Please enter hex color to loading background. Eg.: #CCCCCC', 'paypal-plus-brazil-for-woocommerce' ),
				'default'     => '#CCCCCC',
			),
			'loading_bg_opacity'    => array(
				'title'       => __( 'Loading background opacity', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Please enter a percentage value to background opacity. Eg.: 50%.', 'paypal-plus-brazil-for-woocommerce' ),
				'default'     => '60',
			),
			'testing'               => array(
				'title'       => __( 'Gateway Testing', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'debug'                 => array(
				'title'       => __( 'Debug Log', 'paypal-plus-brazil-for-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'paypal-plus-brazil-for-woocommerce' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log PayPal Plus Brazil events, such as API requests, inside %s', 'paypal-plus-brazil-for-woocommerce' ), $this->get_log_view() ),
			),
		);
	}

	/**
	 * Remove # from the color and return correct value.
	 *
	 * @param $color
	 *
	 * @return string
	 */
	public function filter_hex_color( $color ) {
		return '#' . str_replace( '#', '', $color );
	}

	/**
	 * Return opacity from percentage to decimal.
	 *
	 * @param $value
	 *
	 * @return float
	 */
	public function filter_opacity( $value ) {
		$value = str_replace( '%', '', $value );

		return $value / 100;
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'paypay-plus-brazil-admin', plugins_url( 'assets/js/admin' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_PayPay_Plus_Brazil::VERSION, true );
		include dirname( __FILE__ ) . '/admin/views/html-admin-page.php';
	}

	/**
	 * Payment fields.
	 */
	public function payment_fields() {
		wp_enqueue_script( 'wc-credit-card-form' );
		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}
		$cart_total = $this->get_order_total();
		wc_get_template( 'checkout-form.php', array(
			'api'        => $this->api,
			'gateway'    => $this,
			'cart_total' => $cart_total,
		), 'woocommerce/paypal-plus-brazil/', WC_PayPay_Plus_Brazil::get_templates_path() );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id Order ID.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order  = new WC_Order( $order_id );
		$result = array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);

		// Check first if is missing data.
		if ( empty( $_POST['paypal-plus-brazil-rememberedcards'] ) || empty( $_POST['paypal-plus-brazil-payerid'] ) || empty( $_POST['paypal-plus-brazil-payment-id'] ) ) {
			$order->update_status( 'cancelled', __( 'Missing PayPal payment data.', 'paypal-plus-brazil-for-woocommerce' ) );
		} else {
			$payment_id    = $_POST['paypal-plus-brazil-payment-id'];
			$remembercards = $_POST['paypal-plus-brazil-rememberedcards'];
			$payer_id      = $_POST['paypal-plus-brazil-payerid'];
			$execute       = $this->api->process_payment( $order, $payment_id, $payer_id, $remembercards );

			// Check if success.
			if ( $execute ) {
				$result['result'] = 'success';
				$order->update_status( 'processing', __( 'Payment received and confirmed by PayPal.', 'paypal-plus-brazil-for-woocommerce' ) );
			} else {
				$order->update_status( 'cancelled', __( 'Could not execute the payment.', 'paypal-plus-brazil-for-woocommerce' ) );
			}
		}

		return $result;
	}

	/**
	 * Store customer data to retrive later.
	 *
	 * @param $data Posted data encoded and not parsed.
	 */
	public static function save_customer_info( $data ) {
		$decoded = urldecode( $data );
		parse_str( $decoded, $posted );

		$customer = array(
			'billing_first_name'  => '',
			'billing_last_name'   => '',
			'billing_person_type' => '',
			'billing_person_id'   => '',
			'billing_email'       => '',
			'shipping_address'    => '',
			'shipping_address_2'  => '',
			'data'                => $posted,
		);

		// Set the first name.
		if ( isset( $posted['billing_first_name'] ) ) {
			$customer['billing_first_name'] = sanitize_text_field( $posted['billing_first_name'] );
		}

		// Set the last name.
		if ( isset( $posted['billing_last_name'] ) ) {
			$customer['billing_last_name'] = sanitize_text_field( $posted['billing_last_name'] );
		}

		// Set the person type.
		if ( isset( $posted['billing_persontype'] ) ) {
			$customer['billing_person_type'] = sanitize_text_field( $posted['billing_persontype'] );
		}

		// Set the person type.
		if ( isset( $posted['billing_persontype'] ) ) {
			switch ( $posted['billing_persontype'] ) {
				case '1':
					$customer['billing_person_type'] = 'BR_CPF';
					break;
				case '2':
					$customer['billing_person_type'] = 'BR_CNPJ';
					break;
			}
		}

		// Set the person id.
		if ( $customer['billing_person_type'] ) {
			switch ( $customer['billing_person_type'] ) {
				case 'BR_CPF':
					if ( isset( $posted['billing_cpf'] ) ) {
						$customer['billing_person_id'] = sanitize_text_field( $posted['billing_cpf'] );
					}
					break;
				case 'BR_CNPJ':
					if ( isset( $posted['billing_cnpj'] ) ) {
						$customer['billing_person_id'] = sanitize_text_field( $posted['billing_cnpj'] );
					}
					break;
			}
		}

		// Set the first name.
		if ( isset( $posted['billing_email'] ) ) {
			$customer['billing_email'] = sanitize_text_field( $posted['billing_email'] );
		}

		// Set the address.
		{
			$fields = array(
				's_country',
				's_state',
				's_postcode',
				's_city',
				's_address',
			);

			$field_empty = false;

			foreach ( $fields as $field ) {
				if ( ! isset( $_POST[ $field ] ) || empty( $field ) ) {
					$field_empty = true;
				}
			}

			$ships_diff = isset( $posted['ship_to_different_address'] ) && $posted['ship_to_different_address'] == '1';

			if ( $ships_diff && ( ! isset( $posted['shipping_number'] ) || empty( $posted['shipping_number'] ) ) ) {
				$field_empty = true;
			} else if ( $ships_diff && ( ! isset( $posted['billing_number'] ) || empty( $posted['billing_number'] ) ) ) {
				$field_empty = true;
			}

			if ( ! $field_empty ) {
				$address = $_POST['s_address'];
				if ( $ships_diff ) {
					$customer['shipping_address_2'] = $posted['shipping_neighborhood'];
					$address .= ', ' . $posted['shipping_number'];
					if ( $posted['shipping_address_2'] ) {
						$address .= ', ' . $posted['shipping_address_2'];
					}
				} else {
					$customer['shipping_address_2'] = $posted['billing_neighborhood'];
					$address .= ', ' . $posted['billing_number'];
					if ( $posted['billing_address_2'] ) {
						$address .= ', ' . $posted['billing_address_2'];
					}
				}
				$customer['shipping_address'] = $address;
			}

		}

		// Store data in a session to retrive later.
		WC()->session->set( 'paypal_plus_customer_info', $customer );
	}

	/**
	 * Get customer info.
	 * @return array
	 */
	public function get_customer_info() {
		$customer_info = WC()->session->get( 'paypal_plus_customer_info' );

		return $customer_info;
	}

	/**
	 * Get customer credit card.
	 *
	 * @return mixed|string
	 */
	public function get_customer_cards() {
		$current_user_id = get_current_user_id();
		if ( $current_user_id ) {
			return get_user_meta( $current_user_id, 'paypal_plus_remembered_cards', true );
		}

		return '';
	}

	/**
	 * Get customer info json encoded and special chars.
	 *
	 * @return string
	 */
	public function get_customer_info_json_specialchars() {
		$customer_info = $this->get_customer_info();

		return htmlspecialchars( json_encode( $customer_info ) );
	}

	/**
	 * Check if user info in the form is valid.
	 *
	 * @return bool
	 */
	public function is_valid_customer_info() {
		$customer_info = $this->get_customer_info();

		return $this->validate_user_fields( $customer_info );
	}

	/**
	 * Validate user fields.
	 */
	public function validate_user_fields( $fields ) {
		$customer = WC()->customer;
		if ( ! is_array( $fields ) ) {
			$fields = array();
		}
		$default_fields = array(
			'billing_first_name'    => '',
			'billing_last_name'     => '',
			'billing_person_type'   => '',
			'billing_person_id'     => '',
			'billing_email'         => '',
			'shipping_address'      => '',
			'shipping_city'         => $customer->get_shipping_city(),
			'shipping_country_code' => $customer->get_shipping_country(),
			'shipping_postal_code'  => $customer->get_shipping_postcode(),
			'shipping_state'        => $customer->get_shipping_state(),
		);
		$fields         = wp_parse_args( $fields, $default_fields );
		foreach ( $fields as $id => $field ) {
			if ( empty( $field ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Register log to WooCommerce logs.
	 *
	 * @param $log
	 */
	public function log( $log ) {
		if ( 'yes' == $this->debug ) {
			$this->log->add( $this->id, $log );
		}
	}

}