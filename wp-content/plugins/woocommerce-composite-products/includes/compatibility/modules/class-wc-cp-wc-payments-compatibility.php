<?php
/**
 * WC_CP_WC_Payments_Compatibility class
 *
 * @package  WooCommerce Composite Products
 * @since    9.0.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooPayments Integration.
 *
 * @version  9.0.5
 */
class WC_CP_WC_Payments_Compatibility {

	// Hide quick-pay buttons when product is a Composite product.
	public static function init() {
		add_filter( 'wcpay_payment_request_is_product_supported', array( __CLASS__, 'handle_quick_pay_buttons' ), 10, 2 );
		add_filter( 'wcpay_woopay_button_is_product_supported', array( __CLASS__, 'handle_quick_pay_buttons' ), 10, 2 );
	}

	/**
	 * Hide quick-pay buttons when product is a Composite product.
	 *
	 * @param  bool       $is_supported
	 * @param  WC_Product $product
	 * @return bool
	 */
	public static function handle_quick_pay_buttons( $is_supported, $product ) {

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

WC_CP_WC_Payments_Compatibility::init();
