<?php

function wc_dynamic_pricing_escape_id( $id, $name ) {
	echo esc_attr( $id . '-' . $name );
}

function wc_dynamic_pricing_is_within_date_range( $from = '', $to = '' ) {
	// Check date range
	$from_date = empty( $from ) ? false : wc_dynamic_pricing_wp_strtotime( $from . ' ' . '00:00:00' );
	$to_date   = empty( $to ) ? false : wc_dynamic_pricing_wp_strtotime( $to . ' ' . '23:59:00' );
	$now       = current_datetime()->format( 'U' );

	$from_date = intval( $from_date );
	$to_date   = intval( $to_date );
	$now       = intval( $now );

	$execute_rules = true;
	if ( $from_date && $to_date && ! ( $now >= $from_date && $now <= $to_date ) ) {
		$execute_rules = false;
	} elseif ( $from_date && ! $to_date && ! ( $now >= $from_date ) ) {
		$execute_rules = false;
	} elseif ( $to_date && ! $from_date && ! ( $now <= $to_date ) ) {
		$execute_rules = false;
	}

	return $execute_rules;
}

function wc_dynamic_pricing_wp_strtotime( $str ) {
	// This function behaves a bit like PHP's StrToTime() function, but taking into account the WordPress site's timezone
	// CAUTION: It will throw an exception when it receives invalid input - please catch it accordingly
	// From https://mediarealm.com.au/
	$tz_string = get_option( 'timezone_string' );
	$tz_offset = get_option( 'gmt_offset', 0 );
	if ( ! empty( $tz_string ) ) {
		// If site timezone option string exists, use it
		$timezone = $tz_string;
	} elseif ( $tz_offset == 0 ) {
		// get UTC offset, if it isn’t set then return UTC
		$timezone = 'UTC';
	} else {
		$timezone = $tz_offset;
		if ( substr( $tz_offset, 0, 1 ) != '-' && substr( $tz_offset, 0, 1 ) != '+' && substr( $tz_offset, 0, 1 ) != 'U' ) {
			$timezone = '+' . $tz_offset;
		}
	}
	$datetime = new DateTime( $str, new DateTimeZone( $timezone ) );

	return $datetime->format( 'U' );
}

function wc_dynamic_pricing_is_groups_active() {
	$result = false;
	$result = in_array( 'groups/groups.php', (array) get_option( 'active_plugins', array() ) );
	if ( ! $result && is_multisite() ) {
		$plugins = get_site_option( 'active_sitewide_plugins' );
		$result  = isset( $plugins['groups/groups.php'] );
	}

	return $result;
}

function wc_dynamic_pricing_is_memberships_active() {
	$result = false;
	$result = in_array( 'woocommerce-memberships/woocommerce-memberships.php', (array) get_option( 'active_plugins', array() ) );
	if ( ! $result && is_multisite() ) {
		$plugins = get_site_option( 'active_sitewide_plugins' );
		$result  = isset( $plugins['woocommerce-memberships/woocommerce-memberships.php'] );
	}

	return $result;
}

function wc_dynamic_pricing_is_brands_active() {
		// Check if the taxonomy is registered.
	if ( taxonomy_exists( 'product_brand' ) ) {
		return true;
	} else {
		$result = false;
		$result = in_array( 'woocommerce-brands/woocommerce-brands.php', (array) get_option( 'active_plugins', array() ) );
		if ( ! $result && is_multisite() ) {
			$plugins = get_site_option( 'active_sitewide_plugins' );
			$result  = isset( $plugins['woocommerce-brands/woocommerce-brands.php'] );
		}

		return $result;
	}
}
