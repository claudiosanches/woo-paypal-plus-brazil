<?php
/**
 * PayPal Plus Brazil API.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_PayPal_Plus_Brazil_API {

	/**
	 * Gateway class.
	 *
	 * @var WC_PayPal_Plus_Brazil_Gateway
	 */
	protected $gateway;

	/**
	 * WC_PayPal_Plus_Brazil_API constructor.
	 *
	 * @param $gateway WC_PayPal_Plus_Brazil_Gateway
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * Get the API environment.
	 *
	 * @return string
	 */
	protected function get_environment() {
		return ( 'yes' == $this->gateway->sandbox ) ? 'sandbox.' : '';
	}

	/**
	 * Get the payment URL.
	 *
	 * @return string.
	 */
	protected function get_payment_url() {
		return 'https://api.' . $this->get_environment() . 'paypal.com/v1/payments';
	}

	/**
	 * Get the token URL.
	 *
	 * @return string
	 */
	protected function get_token_url() {
		return 'https://api.' . $this->get_environment() . 'paypal.com/v1/oauth2/token';
	}

	/**
	 * Get the payment experience URL.
	 *
	 * @return string
	 */
	protected function get_payment_experience_url() {
		return 'https://api.' . $this->get_environment() . 'paypal.com/v1/payment-experience';
	}

	/**
	 * Make a request to API
	 *
	 * @param $url
	 * @param string $method
	 * @param array $data
	 * @param array $headers
	 *
	 * @return array|WP_Error
	 */
	protected function do_request( $url, $method = 'POST', $data = array(), $headers = array() ) {
		$params = array(
			'method'      => $method,
			'timeout'     => 60,
			'httpversion' => '1.1',
		);

		if ( 'POST' == $method && ! empty( $data ) ) {
			$params['body'] = $data;
		}

		if ( ! empty( $headers ) ) {
			$params['headers'] = $headers;
		}

		return wp_safe_remote_post( $url, $params );
	}

	/**
	 * Make a request to API with automatic access token.
	 *
	 * @param $url
	 * @param string $method
	 * @param array $data
	 * @param array $headers
	 * @param bool $bearer
	 *
	 * @return array|WP_Error
	 */
	protected function do_request_bearer( $url, $method = 'POST', $data = array(), $headers = array() ) {
		// Default headers.
		$default_headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $this->get_access_token(),
		);
		$headers         = wp_parse_args( $headers, $default_headers );

		// Check if data is serialized
		if ( is_array( $data ) ) {
			$data = json_encode( $data );
		}

		return $this->do_request( $url, $method, $data, $headers );
	}

	/**
	 * Get basic auth base64 encoded.
	 *
	 * @return string
	 */
	protected function get_basic_auth() {
		$auth = base64_encode( $this->gateway->client_id . ':' . $this->gateway->client_secret );

		return $auth;
	}

	/**
	 * Get access token to make requests.
	 *
	 * @return null|string
	 */
	public function get_access_token() {
		$headers  = array( 'Authorization' => 'Basic ' . $this->get_basic_auth() );
		$data     = array( 'grant_type' => 'client_credentials' );
		$response = $this->do_request( $this->get_token_url(), 'POST', $data, $headers );

		$this->gateway->log( 'Requesting to ' . $this->get_token_url() . ': ' . print_r( $data, true ) );

		if ( is_wp_error( $response ) ) {
			$this->gateway->log( 'WP_Error trying to get access token: ' . $response->get_error_message() );
		} else {
			$response_body = json_decode( $response['body'], true );
			$this->gateway->log( 'Response: ' . print_r( $response_body, true ) );

			if ( 200 == $response['response']['code'] ) {
				$this->gateway->log( 'Success getting access token.' );

				return $response_body['access_token'];
			} else if ( 401 === $response['response']['code'] ) {
				$this->gateway->log( 'Failed to authenticate with the cretentials.' );
			} else {
				$this->gateway->log( 'Error trying to get access token.' );
			}
		}

		return false;
	}

	/**
	 * Do payment request.
	 *
	 * @return bool|array
	 */
	public function do_payment_request( $user_info ) {
		$cart = WC()->cart;
		$url  = $this->get_payment_url() . '/payment';
		$data = array(
			'intent'                => 'sale',
			'payer'                 => array(
				'payment_method' => 'paypal',
			),
			'experience_profile_id' => 'XP-EPBM-FB9K-CLLV-C86Q',
			'transactions'          => array(
				array(
					'amount'          => array(
						'currency' => 'BRL',
						'total'    => $this->money_format( $cart->cart_contents_total ),
						'details'  => array(
							'shipping'          => $this->money_format( $cart->shipping_total ),
							'subtotal'          => $this->money_format( $cart->subtotal ),
							'shipping_discount' => $this->money_format( 0 ),
							'insurance'         => $this->money_format( 0 ),
							'handling_fee'      => $this->money_format( $cart->fee_total ),
							'tax'               => $this->money_format( $cart->tax_total ),
						),
					),
					'payment_options' => array(
						'allowed_payment_method' => 'IMMEDIATE_PAY',
					),
					'item_list'       => array(
						'shipping_address' => array(
							'recipient_name' => $user_info['first_name'] . ' ' . $user_info['last_name'],
							'line1'          => $user_info['shipping_address'],
							'line2'          => $user_info['shipping_address_2'],
							'city'           => WC()->customer->get_shipping_city(),
							'country_code'   => WC()->customer->get_shipping_country(),
							'postal_code'    => WC()->customer->get_shipping_postcode(),
							'state'          => WC()->customer->get_shipping_state(),
						),
						'items'            => array(),
					),
				),
			),
			'redirect_urls'         => array(
				'return_url' => home_url(),
				'cancel_url' => home_url(),
			),
		);
		foreach ( $cart->get_cart() as $item ) {
			$data['transactions'][0]['item_list']['items'][] = array(
				'name'     => $item['data']->post->post_title,
				'sku'      => $item['product_id'],
				'price'    => $this->money_format( $item['line_total'] / $item['quantity'] ),
				'currency' => 'BRL',
				'quantity' => $item['quantity'],
			);
		}
		$response = $this->do_request_bearer( $url, 'POST', $data );

		$this->gateway->log( 'Requesting to ' . $url . ': ' . print_r( $data, true ) );

		if ( is_wp_error( $response ) ) {
			$this->gateway->log( 'WP_Error trying to create order: ' . $response->get_error_message() );
		} else {
			$response_body = json_decode( $response['body'], true );
			$this->gateway->log( 'Response: ' . print_r( $response_body, true ) );

			if ( 201 == $response['response']['code'] ) {
				$this->gateway->log( 'Success creating order.' );

				return array(
					'id'           => $response_body['id'],
					'approval_url' => $response_body['links'][1]['href'],
				);
			} else if ( 401 === $response['response']['code'] ) {
				$this->gateway->log( 'Failed to authenticate with the cretentials.' );
			} else {
				$this->gateway->log( 'Error trying to create order.' );
			}
		}

		return false;
	}

	/**
	 * Process payment.
	 *
	 * @param $order WC_Order
	 * @param $payment_id
	 * @param $payer_id
	 * @param string $remembercards
	 *
	 * @return bool|array
	 */
	public function process_payment( $order, $payment_id, $payer_id, $remembercards ) {
		$url      = $this->get_payment_url() . '/payment/' . $payment_id . '/execute/';
		$data     = array( 'payer_id' => $payer_id );
		$response = $this->do_request_bearer( $url, 'POST', $data );

		$this->gateway->log( 'Requesting to ' . $url . ': ' . print_r( $data, true ) );

		if ( is_wp_error( $response ) ) {
			$this->gateway->log( 'WP_Error trying to execute payment: ' . $response->get_error_message() );
		} else {
			$response_body = json_decode( $response['body'], true );
			$this->gateway->log( 'Response: ' . print_r( $response_body, true ) );

			if ( 200 == $response['response']['code'] ) {
				$this->gateway->log( 'Success executing payment.' );
				if ( $response_body['state'] === 'approved' ) {
					$this->gateway->log( 'Payment approved.' );
					$payment_data = array_map(
						'sanitize_text_field',
						array(
							'id'          => $response_body['id'],
							'intent'      => $response_body['intent'],
							'state'       => $response_body['state'],
							'cart'        => $response_body['cart'],
							'payer'       => array(
								'payment_method' => $response_body['payer']['payment_method'],
								'status'         => $response_body['payer']['status'],
							),
							'create_time' => $response_body['create_time'],
						)
					);
					update_post_meta( $order->id, '_wc_paypal_plus_payment_data', $payment_data );
					update_post_meta( $order->id, '_wc_paypal_plus_payment_id', $payment_data['id'] );
					if ( $user_id = $order->get_user_id() ) {
						update_user_meta( $user_id, 'paypal_plus_remembered_cards', $remembercards );
					}

					return $response_body;
				} else {
					$this->gateway->log( 'The payment could not be processed.' );
				}
			} else if ( 401 === $response['response']['code'] ) {
				$this->gateway->log( 'Failed to authenticate with the cretentials.' );
			} else {
				$this->gateway->log( 'Error trying to process order.' );
			}
		}

		WC()->cart->empty_cart();

		return false;
	}

	/**
	 * Create Web Profile Experience
	 *
	 * @param array $args Arguments to create Web Experience Profile.
	 *
	 * @return array|bool
	 */
	public function create_web_experience( $args = array() ) {
		$default_args = array(
			'name'         => get_bloginfo( 'name' ),
			'presentation' => array(
				'brand_name' => get_bloginfo( 'name' ),
				'locale'     => get_locale(),
			),
			'input_fields' => array(
				'allow_note'       => false,
				'no_shipping'      => false,
				'address_override' => true,
			),
		);
		$data         = wp_parse_args( $args, $default_args );
		$url          = $this->get_payment_experience_url();
		$response     = $this->do_request_bearer( $url, 'POST', $data );

		$this->gateway->log( 'Requesting to ' . $url . ': ' . print_r( $data, true ) );

		if ( is_wp_error( $response ) ) {
			$this->gateway->log( 'WP_Error trying to create web experience profile: ' . $response->get_error_message() );
		} else {
			$response_body = json_decode( $response['body'], true );
			$this->gateway->log( 'Response: ' . print_r( $response_body, true ) );

			if ( 201 == $response['response']['code'] ) {
				$this->gateway->log( 'Success creating web experience profile.' );

				return $response_body;
			} else if ( 401 === $response['response']['code'] ) {
				$this->gateway->log( 'Failed to authenticate with the cretentials.' );
			} else {
				$this->gateway->log( 'Error trying to create web experience profile.' );
			}
		}

		return false;
	}

	/**
	 * Delete web experience profile.
	 *
	 * @param string $profile_id Web Experience Profile ID
	 *
	 * @return bool|array
	 */
	public function delete_web_experience_profile( $profile_id ) {
		$url      = $this->get_payment_experience_url() . '/' . $profile_id;
		$response = $this->do_request_bearer( $url, 'DELETE' );

		$this->gateway->log( 'Requesting to ' . $url );

		if ( is_wp_error( $response ) ) {
			$this->gateway->log( 'WP_Error trying to delete web experience profile: ' . $response->get_error_message() );
		} else {
			$response_body = json_decode( $response['body'], true );
			$this->gateway->log( 'Response: ' . print_r( $response_body, true ) );

			if ( 204 == $response['response']['code'] ) {
				$this->gateway->log( 'Success deleting web experience profile.' );

				return $response_body;
			} else if ( 401 === $response['response']['code'] ) {
				$this->gateway->log( 'Failed to authenticate with the cretentials.' );
			} else {
				$this->gateway->log( 'Error trying to delete web experience profile.' );
			}
		}

		return false;
	}

	/**
	 * Money format.
	 *
	 * @param  int /float $value Value to fix.
	 *
	 * @return float            Fixed value.
	 */
	protected function money_format( $value ) {
		return number_format( $value, 2, '.', '' );
	}

}