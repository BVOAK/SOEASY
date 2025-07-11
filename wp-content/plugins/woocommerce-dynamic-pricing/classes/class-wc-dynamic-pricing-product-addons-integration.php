<?php


class WC_Dynamic_Pricing_Product_Addons_Integration {

	private static $instance;

	public static function register() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
	}

	public static function is_product_addon( $product ) {
		// Be sure to check if the product is a WC_Product object.
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return false;
		}

		$value = isset( $product->is_product_addon ) ? $product->is_product_addon : false;
		return ! empty( $value );
	}

	private function __construct() {
		add_filter( 'woocommerce_addons_cloned_product_with_filtered_price', array( $this, 'register_flags_for_dynamic_pricing' ), 10, 2 );
	}

	/*
	* @param WC_Product|WC_Product_Variation $cloned_product
	* @param WC_Product|WC_Product_Variation $original_product
	*/
	public function register_flags_for_dynamic_pricing( $cloned_product, $original_product ) {
		$cloned_product->is_product_addon = true;
		return $cloned_product;
	}
}
