<?php
/**
 * Plugin Name: WooCommerce Checkout Cielo
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-checkout-cielo
 * Description: Checkout Cielo payment gateway for WooCommerce.
 * Author: Claudio Sanches, Gabriel Reguly
 * Author URI: http://claudiosmweb.com/
 * Version: 1.0.4
 * License: GPLv2 or later
 * Text Domain: woocommerce-checkout-cielo
 * Domain Path: languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Checkout_Cielo' ) ) :

/**
 * WooCommerce Checkout Cielo main class.
 */
class WC_Checkout_Cielo {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.4';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin actions.
	 */
	public function __construct() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce and WooCommerce is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2', '>=' ) ) {
			$this->includes();

			// Hook to add Checkout Cielo Gateway to WooCommerce.
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'dependencies_notices' ) );
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
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-checkout-cielo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		include_once 'includes/class-wc-checkout-cielo-api.php';
		include_once 'includes/class-wc-checkout-cielo-gateway.php';
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param  array $methods WooCommerce payment methods.
	 *
	 * @return array          Payment methods with Checkout Cielo.
	 */
	public function add_gateway( $methods ) {
		$methods[] = 'WC_Checkout_Cielo_Gateway';

		return $methods;
	}

	/**
	 * Dependencies notices.
	 */
	public function dependencies_notices() {
		include_once 'includes/views/html-notice-woocommerce-missing.php';
	}

	/**
	 * Action links.
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links   = array();
		$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_checkout_cielo_gateway' ) ) . '">' . __( 'Settings', 'woocommerce-checkout-cielo' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}
}

add_action( 'plugins_loaded', array( 'WC_Checkout_Cielo', 'get_instance' ) );

endif;
