<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Checkout Cielo API class.
 *
 * Checkout Cielo API handler.
 *
 * @class   WC_Checkout_Cielo_API
 * @version 1.0.0
 * @author  Claudio Sanches
 */
class WC_Checkout_Cielo_API {

	/**
	 * API URL.
	 *
	 * @var string
	 */
	protected $transaction_url = 'https://cieloecommerce.cielo.com.br/Transactional/Order/Index';

	/**
	 * Merchant ID
	 *
	 * @var string
	 */
	protected $merchant_id = '';

	/**
	 * Antifraud ID
	 *
	 * @var string
	 */
	protected $antifraud = 'no';

	/**
	 * Send only the order total (yes/no).
	 *
	 * @var string
	 */
	protected $send_only_total = 'no';

	/**
	 * API constructor.
	 *
	 * @param string $merchant_id
	 * @param string $antifraud
	 * @param string $send_only_total
	 */
	public function __construct( $merchant_id, $antifraud, $send_only_total ) {
		$this->merchant_id     = $merchant_id;
		$this->antifraud       = $antifraud;
		$this->send_only_total = $send_only_total;
	}

	/**
	 * Get transaction URL.
	 *
	 * @return string
	 */
	public function get_transaction_url() {
		return $this->transaction_url;
	}

	/**
	 * Only numbers.
	 *
	 * @param  string|int $string
	 *
	 * @return string|int
	 */
	protected function only_numbers( $string ) {
		return preg_replace( '([^0-9])', '', $string );
	}

	/**
	 * Get CPF or CNPJ.
	 *
	 * @param  WC_Order $order
	 *
	 * @return string
	 */
	protected function get_cpf_cnpj( $order ) {
		$wcbcf_settings = get_option( 'wcbcf_settings' );

		if ( 0 != $wcbcf_settings['person_type'] ) {
			if ( ( 1 == $wcbcf_settings['person_type'] && 1 == $order->billing_persontype ) || 2 == $wcbcf_settings['person_type'] ) {
				return $this->only_numbers( $order->billing_cpf );
			}

			if ( ( 1 == $wcbcf_settings['person_type'] && 2 == $order->billing_persontype ) || 3 == $wcbcf_settings['person_type'] ) {
				return $this->only_numbers( $order->billing_cnpj );
			}
		}

		return '';
	}

	/**
	 * Sanitize string.
	 *
	 * @param  string $string
	 *
	 * @return string
	 */
	protected function sanitize_string( $string, $length = 128 ) {
		return sanitize_text_field( substr( $string, 0, $length ) );
	}

	/**
	 * Get price.
	 *
	 * @param  float $price
	 *
	 * @return int
	 */
	protected function get_price( $price ) {
		$price_in_cents = round( $price, 2 ) * 100;

		return apply_filters( 'wc_checkout_cielo_get_price', $price_in_cents, $price );
	}

	/**
	 * Get discount.
	 *
	 * @param  WC_Order $order
	 *
	 * @return int
	 */
	protected function get_discount( $order ) {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '<' ) ) {
			return $this->get_price( $order->get_order_discount() );
		} else {
			return $this->get_price( $order->get_total_discount() );
		}
	}

	/**
	 * Get customer data.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function get_customer_data( $order ) {
		$data = array(
			'customer_name'  => $order->billing_first_name . ' ' . $order->billing_last_name,
			'customer_email' => $order->billing_email,
			'customer_phone' => $this->only_numbers( $order->billing_phone )
		);

		if ( class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
			$data['customer_identity'] = $this->get_cpf_cnpj( $order );
		}

		return $data;
	}

	/**
	 * Get shipping data.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function get_shipping_data( $order ) {
		$data = array(
			'shipping_address_name'       => $order->shipping_address_1,
			'shipping_address_number'     => $order->shipping_number,
			'shipping_address_complement' => $order->shipping_address_2,
			'shipping_address_district'   => $order->shipping_neighborhood,
			'shipping_address_city'       => $order->shipping_city,
			'shipping_address_state'      => $order->shipping_state,
			'shipping_zipcode'            => $this->only_numbers( $order->shipping_postcode ),
		);

		if ( 0 < $order->get_total_shipping() ) {
			$data['shipping_type']    = '2'; // Flat rate.
			$data['shipping_1_name']  = $order->get_shipping_method();
			$data['shipping_1_price'] = $this->get_price( $order->get_total_shipping() );
		} else {
			$data['shipping_type'] = '3';
		}

		return $data;
	}

	/**
	 * Get products data.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function get_products_data( $order ) {
		$i = 1;
		$data  = array();

		// Force only one item.
		if ( 'yes' == $this->send_only_total || 'yes' == get_option( 'woocommerce_prices_include_tax' ) ) {
			$data['cart_' . $i . '_name']        = $this->sanitize_string( sprintf( __( 'Order %s', 'woocommerce-checkout-cielo' ), $order->get_order_number() ) );
			// $data['cart_' . $i . '_description'] = '';
			$data['cart_' . $i . '_unitprice']   = $this->get_price( $order->get_total() ) - $this->get_price( $order->get_total_shipping() ) + $this->get_discount( $order );
			$data['cart_' . $i . '_quantity']    = '1';
			$data['cart_' . $i . '_type']        = '1';
			// $data['cart_' . $i . '_code']        = '';
			$data['cart_' . $i . '_weight']      = '0';
		} else {
			if ( 0 < sizeof( $order->get_items() ) ) {
				foreach ( $order->get_items() as $order_item ) {
					if ( $order_item['qty'] ) {
						$item_total = $this->get_price( $order->get_item_subtotal( $order_item, false ) );

						if ( 0 > $item_total ) {
							continue;
						}

						// Get product data.
						$_product  = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $order_item ), $order_item );
						$item_meta = new WC_Order_Item_Meta( $order_item['item_meta'], $_product );

						$data['cart_' . $i . '_name'] = $this->sanitize_string( $order_item['name'] );
						if ( $meta = $item_meta->display( true, true ) ) {
							$data['cart_' . $i . '_description'] = $this->sanitize_string( $meta, 256 );
						}

						$data['cart_' . $i . '_unitprice']   = $item_total;
						$data['cart_' . $i . '_quantity']    = $order_item['qty'];
						$data['cart_' . $i . '_type']        = '1';
						if ( $sku = $_product->get_sku() ) {
							$data['cart_' . $i . '_code'] = $sku;
						}
						$data['cart_' . $i . '_weight'] = wc_get_weight( $_product->get_weight(), 'g' );
						$i++;
					}
				}
			}

			// Fees.
			if ( 0 < sizeof( $order->get_fees() ) ) {
				foreach ( $order->get_fees() as $fee ) {
					$fee_total = $this->get_price( $fee['line_total'] );

					if ( 0 > $fee_total ) {
						continue;
					}

					$data['cart_' . $i . '_name']      = $this->sanitize_string( $fee['name'] );
					$data['cart_' . $i . '_unitprice'] = $fee_total;
					$data['cart_' . $i . '_quantity']  = '1';
					$data['cart_' . $i . '_type']      = '4';
					$i++;
				}
			}

			// Taxes.
			if ( 0 < sizeof( $order->get_taxes() ) ) {
				foreach ( $order->get_taxes() as $tax ) {
					$tax_total = $this->get_price( $tax['tax_amount'] + $tax['shipping_tax_amount'] );

					if ( 0 > $tax_total ) {
						continue;
					}

					$data['cart_' . $i . '_name']      = $this->sanitize_string( $tax['label'] );
					$data['cart_' . $i . '_unitprice'] = $tax_total;
					$data['cart_' . $i . '_quantity']  = '1';
					$data['cart_' . $i . '_type']      = '4';
					$i++;
				}
			}
		}

		return $data;
	}

	/**
	 * Get discount data.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	public function get_discount_data( $order ) {
		$data     = array();
		$discount = $this->get_discount( $order );

		if ( 0 < $discount ) {
			$data['discount_type']  = '1';
			$data['discount_value'] = $discount;
		}

		return $data;
	}

	/**
	 * Get the transaction data.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	public function get_transaction_data( $order ) {
		$items = array();

		// Order data.
		$data = array(
			'merchant_id'       => $this->merchant_id,
			'order_number'      => $order->id,
			'antifraud_enabled' => ( 'yes' == $this->antifraud ) ? 'TRUE' : 'FALSE'
		);

		$customer_data = $this->get_customer_data( $order );
		$shipping_data = $this->get_shipping_data( $order );
		$products_data = $this->get_products_data( $order );
		$discount_data = $this->get_discount_data( $order );
		$data          = apply_filters( 'wc_checkout_cielo_transaction_data', array_merge( $data, $customer_data, $shipping_data, $products_data, $discount_data ) );

		return $data;
	}

	/**
	 * Get the payment method type.
	 *
	 * @param  int $value
	 *
	 * @return string
	 */
	public function get_payment_method_type( $value ) {
		switch ( $value ) {
			case 1 :
				$type = __( 'Credit Card', 'woocommerce-checkout-cielo' );
				break;
			case 2 :
				$type = __( 'Banking Billet', 'woocommerce-checkout-cielo' );
				break;
			case 3 :
				$type = __( 'Online Debit', 'woocommerce-checkout-cielo' );
				break;
			case 4 :
				$type = __( 'Debit Card', 'woocommerce-checkout-cielo' );
				break;

			default :
				$type = '';
				break;
		}

		return $type;
	}

	/**
	 * Get the payment method brand name.
	 *
	 * @param  int $value
	 *
	 * @return string
	 */
	public function get_payment_method_brand( $value ) {
		switch ( $value ) {
			case 1 :
				$type = __( 'Visa', 'woocommerce-checkout-cielo' );
				break;
			case 2 :
				$type = __( 'Mastercad', 'woocommerce-checkout-cielo' );
				break;
			case 3 :
				$type = __( 'AmericanExpress', 'woocommerce-checkout-cielo' );
				break;
			case 4 :
				$type = __( 'Diners', 'woocommerce-checkout-cielo' );
				break;
			case 5 :
				$type = __( 'Elo', 'woocommerce-checkout-cielo' );
				break;
			case 6 :
				$type = __( 'Aura', 'woocommerce-checkout-cielo' );
				break;
			case 7 :
				$type = __( 'JCB', 'woocommerce-checkout-cielo' );
				break;

			default :
				$type = '';
				break;
		}

		return $type;
	}

	/**
	 * Get the payment method bank name.
	 *
	 * @param  int $value
	 *
	 * @return string
	 */
	public function get_payment_method_bank( $value ) {
		switch ( $value ) {
			case 1 :
				$type = __( 'Banco do Brasil', 'woocommerce-checkout-cielo' );
				break;
			case 2 :
				$type = __( 'Bradesco', 'woocommerce-checkout-cielo' );
				break;

			default :
				$type = '';
				break;
		}

		return $type;
	}

	/**
	 * Get the payment antifraud result.
	 *
	 * @param  int $value
	 *
	 * @return string
	 */
	public function get_payment_antifraud_result( $value ) {
		switch ( $value ) {
			case 1 :
				$type = __( 'Low Risk', 'woocommerce-checkout-cielo' );
				break;
			case 2 :
				$type = __( 'High Risk', 'woocommerce-checkout-cielo' );
				break;
			case 3 :
				$type = __( 'Not Finished', 'woocommerce-checkout-cielo' );
				break;
			case 4 :
				$type = __( 'Moderate Risk', 'woocommerce-checkout-cielo' );
				break;

			default :
				$type = '';
				break;
		}

		return $type;
	}
}
