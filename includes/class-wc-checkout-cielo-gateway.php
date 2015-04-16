<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Checkout Cielo Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class   WC_Checkout_Cielo_Gateway
 * @extends WC_Payment_Gateway
 * @version 1.0.0
 * @author  Claudio Sanches
 */
class WC_Checkout_Cielo_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                   = 'checkout-cielo';
		$this->icon                 = apply_filters( 'wc_checkout_cielo_icon', plugins_url( 'assets/images/cielo.png', plugin_dir_path( __FILE__ ) ) );
		$this->method_title         = __( 'Checkout Cielo', 'woocommerce-checkout-cielo' );
		$this->method_description   = __( 'Accept payments by credit card, debit card, online debit or banking billet using the Checkout Cielo.', 'woocommerce-checkout-cielo' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Optins.
		$this->title           = $this->get_option( 'title' );
		$this->description     = $this->get_option( 'description' );
		$this->merchant_id     = $this->get_option( 'merchant_id' );
		$this->antifraud       = $this->get_option( 'antifraud' );
		$this->cc_authorized   = $this->get_option( 'cc_authorized' );
		$this->send_only_total = $this->get_option( 'send_only_total' );
		$this->debug           = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			$this->log = new WC_Logger();
		}

		$this->api = new WC_Checkout_Cielo_API( $this->merchant_id, $this->antifraud, $this->send_only_total );

		// Actions.
		add_action( 'woocommerce_api_wc_checkout_cielo_gateway', array( $this, 'notification_handler' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		add_action( 'wc_checkout_cielo_update_order_status', array( $this, 'update_order_status' ), 10, 2 );
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	protected function using_supported_currency() {
		return apply_filters( 'wc_checkout_cielo_using_supported_currency', 'BRL' == get_woocommerce_currency() );
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
		$api = ! empty( $this->merchant_id );

		$available = 'yes' == $this->get_option( 'enabled' ) && $api && $this->using_supported_currency();

		return $available;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-checkout-cielo' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Checkout Cielo', 'woocommerce-checkout-cielo' ),
				'default' => 'no'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce-checkout-cielo' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-checkout-cielo' ),
				'desc_tip'    => true,
				'default'     => __( 'Cielo', 'woocommerce-checkout-cielo' )
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce-checkout-cielo' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-checkout-cielo' ),
				'default'     => __( 'Pay with credit card, debit card, online debit or banking billet.', 'woocommerce-checkout-cielo' )
			),
			'integration' => array(
				'title'       => __( 'Integration Settings', 'woocommerce-checkout-cielo' ),
				'type'        => 'title',
				'description' => sprintf( __( 'For the integration work you need to set the following urls in the %s:', 'woocommerce-checkout-cielo' ), '<a href="https://cieloecommerce.cielo.com.br/Backoffice/Merchant/Configuration/Show#paymentMethods" target="_blank">' . __( 'Cielo Backoffice > Settings > Store Settings', 'woocommerce-checkout-cielo' ) . '</a>' ) . '<br /><br /><strong>' . __( 'Return URL:', 'woocommerce-checkout-cielo' ) . '</strong> <code>' . esc_url( wc_get_endpoint_url( 'order-received', '', get_permalink( wc_get_page_id( 'checkout' ) ) ) ) . '</code><br /><strong>' . __( 'Notification URL:', 'woocommerce-checkout-cielo' ) . '</strong> <code>' . esc_url( WC()->api_request_url( 'WC_Checkout_Cielo_Gateway' ) ) . '</code><br /><strong>' . __( 'Status Change URL:', 'woocommerce-checkout-cielo' ) . '</strong> <code>' . esc_url( WC()->api_request_url( 'WC_Checkout_Cielo_Gateway' ) ) . '</code>'
			),
			'merchant_id' => array(
				'title'             => __( 'Merchant ID', 'woocommerce-checkout-cielo' ),
				'type'              => 'text',
				'description'       => sprintf( __( 'Please enter your Merchant ID. This is needed in order to take payment. Is possible found the Merchant ID in %s.', 'woocommerce-checkout-cielo' ), '<a href="https://cieloecommerce.cielo.com.br/Backoffice/Merchant/Account/Details" target="_blank">' . __( 'Cielo Backoffice > Settings > Account Data', 'woocommerce-checkout-cielo' ) . '</a>' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required'
				)
			),
			'behavior' => array(
				'title'       => __( 'Integration Behavior', 'woocommerce-checkout-cielo' ),
				'type'        => 'title',
				'description' => ''
			),
			'antifraud' => array(
				'title'   => __( 'Enable Antifraud', 'woocommerce-checkout-cielo' ),
				'type'    => 'checkbox',
				'label'   => __( 'If this option is enabled the payments will be processed by the Antifraud.', 'woocommerce-checkout-cielo' ),
				'default' => 'no'
			),
			'cc_authorized' => array(
				'title'   => __( 'Complete payment when Credit Card is Authorized', 'woocommerce-checkout-cielo' ),
				'type'    => 'checkbox',
				'label'   => __( 'If this option is enabled the payment is completed when the credit card has been authorized and before the value is captured.', 'woocommerce-checkout-cielo' ),
				'default' => 'no'
			),
			'send_only_total' => array(
				'title'   => __( 'Send only the order total', 'woocommerce-checkout-cielo' ),
				'type'    => 'checkbox',
				'label'   => __( 'If this option is enabled will only send the order total, not the list of items.', 'woocommerce-checkout-cielo' ),
				'default' => 'no'
			),
			'testing' => array(
				'title'       => __( 'Gateway Testing', 'woocommerce-checkout-cielo' ),
				'type'        => 'title',
				'description' => ''
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'woocommerce-checkout-cielo' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce-checkout-cielo' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Checkout Cielo events, such as API requests, you can check this log in %s.', 'woocommerce-checkout-cielo' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'woocommerce-checkout-cielo' ) . '</a>' )
			)
		);
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		include 'views/html-admin-page.php';
	}

	/**
	 * Generate the Cielo button link.
	 *
	 * @param  int $order_id
	 *
	 * @return string
	 */
	public function generate_cielo_form( $order_id ) {
		$order = wc_get_order( $order_id );
		$args  = $this->api->get_transaction_data( $order );

		if ( 'yes' == $this->debug ) {
			$this->log->add( $this->id, 'Transaction arguments for order ' . $order->get_order_number() . ': ' . print_r( $args, true ) );
		}

		$args_array = array();

		foreach ( $args as $key => $value ) {
			$args_array[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		wc_enqueue_js( '
			$.blockUI({
					message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to Cielo to make payment.', 'woocommerce-checkout-cielo' ) ) . '",
					baseZ: 99999,
					overlayCSS: {
						background: "#fff",
						opacity: 0.6
					},
					css: {
						padding:         "20px",
						zindex:          "9999999",
						textAlign:       "center",
						color:           "#555",
						border:          "3px solid #aaa",
						backgroundColor: "#fff",
						cursor:          "wait",
						lineHeight:      "24px",
					}
				});
			$( "#submit-cielo-payment-form" ).click();
		' );

		return '<form action="' . esc_url( $this->api->get_transaction_url() ) . '" method="post" id="cielo_payment_form" target="_top">
				' . implode( '', $args_array ) . '
				<!-- Button Fallback -->
				<div class="payment_buttons">
					<input type="submit" class="button alt" id="submit-cielo-payment-form" value="' . __( 'Pay via Cielo', 'woocommerce-checkout-cielo' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce-checkout-cielo' ) . '</a>
				</div>
				<script type="text/javascript">
					jQuery( ".payment_buttons" ).hide();
				</script>
			</form>';
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		return array(
			'result' 	=> 'success',
			'redirect'	=> $order->get_checkout_payment_url( true )
		);
	}

	/**
	 * Output for the order received page.
	 *
	 * @param int $order_id
	 */
	public function receipt_page( $order_id ) {
		echo '<p>' . __( 'Thank you - your order is now pending payment. You should be automatically redirected to Cielo to make payment.', 'woocommerce-checkout-cielo' ) . '</p>';

		echo $this->generate_cielo_form( $order_id );
	}

	/**
	 * Notification handler.
	 */
	public function notification_handler() {
		@ob_clean();

		if ( isset( $_POST['checkout_cielo_order_number'] ) && isset( $_POST['amount'] ) && isset( $_POST['order_number'] ) && isset( $_POST['payment_status'] ) ) {
			$data           = $_POST;
			$transaction_id = sanitize_text_field( $data['checkout_cielo_order_number'] );
			$amount         = intval( $data['amount'] );
			$order_number   = intval( $data['order_number'] );
			$order          = wc_get_order( $order_number );

			// Remove sensitive data.
			if ( isset( $data['customer_identity'] ) ) {
				unset( $data['customer_identity'] );
			}
			if ( isset( $data['payment_maskedcreditcard'] ) ) {
				unset( $data['payment_maskedcreditcard'] );
			}

			// Test if the notification is valid.
			if ( is_object( $order ) && $amount === intval( $order->get_total() * 100 ) && $order_number === $order->id ) {
				header( 'HTTP/1.1 200 OK' );

				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'Received status change for order ' . $order->get_order_number() . ': ' . print_r( $data, true ) );
				}

				add_post_meta( $order->id, '_transaction_id', $transaction_id, true );
				do_action( 'wc_checkout_cielo_update_order_status', $order, $data );
				exit;
			} else {
				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'Invalid status change: ' . print_r( $data, true ) );
				}
			}
		}

		wp_die( __( 'Cielo Request Failure', 'woocommerce-checkout-cielo' ) );
	}

	/**
	 * Update order status
	 *
	 * @param  WC_Order $order
	 * @param  array    $data
	 */
	public function update_order_status( $order, $data ) {
		$status = intval( $data['payment_status'] );

		if ( isset( $data['tid'] ) ) {
			update_post_meta(
				$order->id,
				__( 'Cielo TID', 'woocommerce-checkout-cielo' ),
				sanitize_text_field( $data['tid'] )
			);
		}
		if ( isset( $data['customer_email'] ) ) {
			update_post_meta(
				$order->id,
				__( 'Payer email', 'woocommerce-checkout-cielo' ),
				sanitize_text_field( $data['customer_email'] )
			);
		}
		if ( isset( $data['customer_name'] ) ) {
			update_post_meta(
				$order->id,
				__( 'Payer name', 'woocommerce-checkout-cielo' ),
				sanitize_text_field( $data['customer_name'] )
			);
		}
		if ( isset( $data['payment_method_type'] ) ) {
			update_post_meta(
				$order->id,
				__( 'Payment type', 'woocommerce-checkout-cielo' ),
				$this->api->get_payment_method_type( intval( $data['payment_method_type'] ) )
			);
		}
		if ( isset( $data['payment_method_brand'] ) ) {
			update_post_meta(
				$order->id,
				__( 'Payment method', 'woocommerce-checkout-cielo' ),
				$this->api->get_payment_method_brand( intval( $data['payment_method_brand'] ) )
			);
		}
		if ( isset( $data['payment_method_bank'] ) ) {
			update_post_meta(
				$order->id,
				__( 'Payment bank', 'woocommerce-checkout-cielo' ),
				$this->api->get_payment_method_bank( intval( $data['payment_method_bank'] ) )
			);
		}
		if ( isset( $data['payment_installments'] ) ) {
			update_post_meta(
				$order->id,
				__( 'Installments', 'woocommerce-checkout-cielo' ),
				sanitize_text_field( $data['payment_installments'] )
			);
		}
		if ( isset( $data['payment_antifrauderesult'] ) ) {
			update_post_meta(
				$order->id,
				__( 'Antifraud result', 'woocommerce-checkout-cielo' ),
				$this->api->get_payment_antifraud_result( intval( $data['payment_antifrauderesult'] ) )
			);
		}

		switch ( $status ) {
			case 1 :
				$order->update_status( 'on-hold', __( 'Cielo: Payment pending.', 'woocommerce-checkout-cielo' ) );

				break;
			case 2 :
				$order->add_order_note( __( 'Cielo: Order paid.', 'woocommerce-checkout-cielo' ) );

				// Changing the order for processing and reduces the stock.
				$order->payment_complete();

				break;
			case 3 :
				$order->update_status( 'failed', __( 'Cielo: Credit card denied.', 'woocommerce-checkout-cielo' ) );

				break;
			case 5 :
				$order->update_status( 'failed', __( 'Cielo: Credit card payment canceled.', 'woocommerce-checkout-cielo' ) );

				break;
			case 6 :
				$order->update_status( 'on-hold', __( 'Cielo: Payment not finished.', 'woocommerce-checkout-cielo' ) );

				break;
			case 7 :
				$message = __( 'Cielo: Credit card authorized. Need to be capatured in the Cielo Backoffice.', 'woocommerce-checkout-cielo' );
				if ( 'yes' === $this->cc_authorized ) {
					$order->add_order_note( $message );

					// Changing the order for processing and reduces the stock.
					$order->payment_complete();
				} else {
					$order->update_status( 'on-hold', $message );
				}

				break;
			case 8 :
				$order->update_status( 'refunded', __( 'Cielo: Payment refunded.', 'woocommerce-checkout-cielo' ) );

				break;

			default :
				// No action xD.
				break;
		}
	}
}
