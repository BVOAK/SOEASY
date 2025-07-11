<?php
/**
 * WC_CP_Admin_Tracks class
 *
 * @package  Woo Composite Products
 * @since    10.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles sending Tracks events.
 *
 * @class    WC_CP_Admin_Tracks
 * @version  10.1.1
 */
class WC_CP_Admin_Tracks {

	public static function record_event( $event, $properties = array() ) {
		if ( ! is_callable( array( 'WC_Tracks', 'record_event' ) ) ) {
			return;
		}
		$default_properties = self::get_default_properties();

		$properties = wp_parse_args( $properties, $default_properties );

		$properties = apply_filters( 'wc_cp_admin_tracks_properties', $properties );

		WC_Tracks::record_event( $event, $properties );
	}

	public static function get_default_properties() {
		$properties = array(
			'wccom_product_id' => WC_CP()->wccom_product_id,
		);

		return $properties;
	}
}
