<?php
/**
 * WC_CP_PayPal_Compatibility class
 *
 * @package  WooCommerce Composite Products
 * @since    9.0.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PayPal Compatibility.
 *
 * @version 9.0.5
 */
class WC_CP_PayPal_Compatibility {

	// Hide smart buttons in product pages when product is a Composite product.
	public static function init() {
		add_filter( 'woocommerce_paypal_payments_product_supports_payment_request_button', array( __CLASS__, 'handle_smart_buttons' ), 10, 2 );
	}

	/**
	 * Hide smart buttons in product pages when product is a Composite product.
	 *
	 * @param  bool       $is_supported
	 * @param  WC_Product $product
	 *
	 * @return bool
	 */
	public static function handle_smart_buttons( $is_supported, $product ) {
		// If the smart button is not supported by some other plugin, respect that.
		if ( ! $is_supported ) {
			return $is_supported;
		}

		if ( $product && $product->is_type( 'composite' ) ) {
			return false;
		}

		return $is_supported;
	}
}

WC_CP_PayPal_Compatibility::init();
