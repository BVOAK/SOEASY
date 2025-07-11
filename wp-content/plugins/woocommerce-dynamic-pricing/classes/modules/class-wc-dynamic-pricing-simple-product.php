<?php

class WC_Dynamic_Pricing_Simple_Product extends WC_Dynamic_Pricing_Simple_Base {

	private static $instance;

	public static function instance(): WC_Dynamic_Pricing_Simple_Product {
		if ( empty( self::$instance ) ) {
			self::$instance = new WC_Dynamic_Pricing_Simple_Product( 'simple_product' );
		}
		return self::$instance;
	}

	public function is_applied_to_product( $product ): bool {
		return false;
	}

	public function get_discounted_price_for_shop( $product, $working_price ): bool {
		return false;
	}

	public function adjust_cart( $cart ) {
		// Empty on purpose.
	}

	public function initialize_rules() {
		// Empty on purpose.
	}
}
