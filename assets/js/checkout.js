jQuery( function ( $ ) {

	var ppb_checkout = {

		$checkout_form: $( 'form.checkout' ),
		$order_review: $( 'form#order_review' ),
		ppp: null,
		form_confirmed: false,

		// ppb_checkout constructor.
		init: function () {
			// Handle checkout update events.
			$( document.body ).on( 'update_checkout', this.update_checkout );
			$( document.body ).on( 'updated_checkout', this.updated_checkout );
			// Handle submit form action.
			$( 'form.checkout' ).on( 'submit', '#place_order', this.form_checkout_submit );
			// Handle event listener for iframe.
			this.window_event_listener();
			// Inputs/selects which update totals
			this.$checkout_form.on( 'submit', this.place_order );
			this.$checkout_form.on( 'change', '#billing_persontype', this.trigger_update_checkout );
			this.$checkout_form.on( 'change', '#billing_first_name, #billing_last_name, #billing_cpf, #billing_cnpj, #billing_email, #billing_address, #billing_number, #billing_address2, #billing_neighborhood, #billing_city, #billing_state, #billing_country', this.trigger_update_checkout );
			this.$checkout_form.on( 'change', '#shipping_first_name, #shipping_last_name, #shipping_address, #shipping_number, #shipping_address2, #shipping_neighborhood, #shipping_city, #shipping_state, #shipping_country', this.trigger_update_checkout );
			this.$checkout_form.on( 'click', '#place_order', this.openCheckout );
			this.$order_review.on( 'submit', this.openCheckout );
		},

		// When update checkout start.
		update_checkout: function () {
			ppb_checkout.ppp = null;
		},

		// When update checkout ends.
		updated_checkout: function () {
			ppb_checkout.form_confirmed = false;
			var info = $( '#paypal-plus-brazil-info' ),
				approval_url = $( '#paypal-plus-brazil-approval-url' ).val();
			if ( info.length ) {
				var info_json = JSON.parse( info.val() );
				ppb_checkout.overlay_css();
				ppb_checkout.ppp = PAYPAL.apps.PPP( {
					approvalUrl: approval_url,
					placeholder: 'ppplus',
					mode: wc_ppb_params.mode,
					payerFirstName: info_json.billing_first_name,
					payerLastName: info_json.billing_last_name,
					payerEmail: info_json.billing_email,
					payerTaxId: info_json.billing_person_id,
					payerTaxIdType: info_json.billing_person_type,
					language: 'pt_BR',
					country: 'BR',
					iframeHeight: '400',
					buttonLocation: 'inside',
					disallowRememberedCards: false,
					rememberedCards: wc_ppb_params.remembered_cards
				} );
			}
		},

		// Start listening to iframe.
		window_event_listener: function () {
			if ( window.addEventListener ) {
				window.addEventListener( 'message', ppb_checkout.message_listener, false );
			} else if ( window.attachEvent ) {
				window.attachEvent( 'onmessage', ppb_checkout.message_listener );
			} else {
				throw new Error( "Can't attach message listener" );
			}
		},

		// Listener to iframe.
		message_listener: function ( event ) {
			try {
				var data = JSON.parse( event.data );
				if ( data.action === 'resizeHeightOfTheIframe' ) {
					// Resize actions
				} else if ( data.action === 'loaded' ) {
					ppb_checkout.disable_overlay_css();
				} else if ( data.action === 'enableContinueButton' ) {
					var $button = $( '#place_order' );
					if ( ppb_checkout.is_paypal_selected() ) {
						$button.prop( 'disabled', false );
					}
					if ( data.result === 'error' ) {
						ppb_checkout.resize_ppplus();
					}
					$button.prop( 'disabled', false );
				} else if ( data.action === 'disableContinueButton' ) {
					var $button = $( '#place_order' );
					$button.prop( 'disabled', true );
				} else if ( data.action === 'checkout' ) {
					var payment_approved = data.result.payment_approved;
					if ( payment_approved === true ) {
						var rememberedcards = data.result.rememberedCards,
							payerid = data.result.payer.payer_info.payer_id,
							field_rememberedcards = $( '#paypal-plus-brazil-rememberedcards' ),
							field_payerid = $( '#paypal-plus-brazil-payerid' );
						field_rememberedcards.val( rememberedcards );
						field_payerid.val( payerid );
					}
					ppb_checkout.finish_submit_form();
				} else if ( data.action === 'onError' ) {
					ppb_checkout.resize_ppplus();
					if ( data.cause === '"NO_VALID_FUNDING_SOURCE_OR_RISK_REFUSED"' || data.cause === '"RISK_N_DECLINE"' || data.cause === '"TRY_ANOTHER_CARD"' || data.cause === '"NO_VALID_FUNDING_INSTRUMENT"' ) {
						ppb_checkout.finish_submit_form();
					} else if ( data.cause === '"PPPLUS_NOT_AVAILABLE_FOR_MERCHANT"' ) {
						ppb_checkout.add_error_message( wc_ppb_params.paypal_plus_not_available );
					} else if ( data.cause === '"CHECK_ENTRY"' ) {
						ppb_checkout.add_error_message( wc_ppb_params.check_entry );
					} else if ( data.cause === '"SOCKET_HANG_UP"' || data.cause === '"UNKNOWN_INTERNAL_ERROR"' || data.cause === '"INTERNAL_SERVICE_ERROR"' || data.cause === '"INTERNAL_SERVER_ERROR"' ) {
						ppb_checkout.trigger_update_checkout();
						ppb_checkout.force_reload_iframe();
					} else {
						ppb_checkout.add_error_message( wc_ppb_params.unknown_error );
					}
				}
			} catch ( err ) {
				ppb_checkout.add_error_message( wc_ppb_params.unknown_error_json );
			}
		},

		// Add an error message to screen.
		add_error_message: function ( msg ) {
			var $container = $( '.payment_box.payment_method_paypal-plus-brazil' ),
				html = '<div class="woocommerce-error woocommerce-error-paypal-plus-brazil">' + msg + '</div>';
			$container.prepend( html );
		},

		// Clear all error messages in screen.
		clear_error_messages: function () {
			$( '.woocommerce-error-paypal-plus-brazil' ).remove();
		},

		// Finish submitting form.
		finish_submit_form: function () {
			ppb_checkout.form_confirmed = true;
			ppb_checkout.$checkout_form.submit();
		},

		// Reload iframe.
		force_reload_iframe: function () {

		},

		// Resize ppplus container.
		resize_ppplus: function () {
			$( '#ppplus, #ppplus iframe' )
				.css( 'height', '440px' )
				.css( 'min-height', '440px' )
				.css( 'max-height', '440px' );
		},

		// Return if PayPal payment method is selected.
		is_paypal_selected: function () {
			return $( '#payment_method_paypal-plus-brazil' ).is( ':checked' );
		},

		// When form checkout is submited.
		form_checkout_submit: function () {
			return false;
		},

		// Trigger update checkout for custom fields.
		trigger_update_checkout: function () {
			$( document.body ).trigger( 'update_checkout' );
		},

		// Adds an overlay to PayPal iframe.
		overlay_css: function () {
			$( '#paypal-plus-brazil-payment-form' ).block( {
				message: wc_ppb_params.paypal_loading_message,
				overlayCSS: {
					background: wc_ppb_params.paypal_loading_bg_color,
					opacity: wc_ppb_params.paypal_loading_bg_opacity
				}
			} );
		},

		// Disable overlay in PayPal iframe.
		disable_overlay_css: function () {
			$( '#paypal-plus-brazil-payment-form' ).unblock();
		},

		// When checkout as PayPal Plus.
		openCheckout: function () {
			ppb_checkout.clear_error_messages();
			if ( ! ppb_checkout.form_confirmed && ppb_checkout.is_paypal_selected() ) {
				var checkout, customer, params,
					form = $( 'form.checkout, form#order_review' );
				ppb_checkout.ppp.doContinue();
				return false;
			}
		}
	};

	// Init ppb_checkout.
	ppb_checkout.init();

} );