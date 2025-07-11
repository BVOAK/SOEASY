<?php
/**
 * WC_CP_Admin_Restrictions class
 *
 * @package  Woo Composite Products
 * @since    10.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return configured admin restrictions.
 *
 * @class    WC_CP_Admin_Restrictions
 * @version  10.1.1
 */
class WC_CP_Admin_Restrictions {
	/**
	 * The possible license states.
	 */
	const LICENSE_STATE_SITE_DISCONNECTED    = 'site-disconnected';
	const LICENSE_STATE_LICENSE_EXPIRED      = 'license-expired';
	const LICENSE_STATE_LICENSE_EXPIRING     = 'license-expiring';
	const LICENSE_STATE_LICENSE_UNREGISTERED = 'license-unregistered';
	const LICENSE_STATE_LICENSE_REGISTERED   = 'license-registered';
	const LICENSE_STATES                     = array(
		self::LICENSE_STATE_SITE_DISCONNECTED,
		self::LICENSE_STATE_LICENSE_EXPIRED,
		self::LICENSE_STATE_LICENSE_EXPIRING,
		self::LICENSE_STATE_LICENSE_UNREGISTERED,
		self::LICENSE_STATE_LICENSE_REGISTERED,
	);

	/**
	 * Finds the current license state.
	 *
	 * @param mixed $product_id The product ID.
	 * @return string|false The license state or false if the product ID or fetched license state is invalid.
	 */
	public static function find_license_state( $product_id ) {
		$product_id = absint( $product_id );
		if ( ! $product_id ) {
			return false;
		}

		$site_connected = false;
		if ( ! is_callable( array( 'WC_Helper', 'is_site_connected' ) ) ) {
			$site_connected = self::is_site_connected_compat();
		} else {
			$site_connected = WC_Helper::is_site_connected();
		}

		if ( ! $site_connected ) {
			return self::LICENSE_STATE_SITE_DISCONNECTED;
		}

		$license = null;
		if ( ! is_callable( array( 'WC_Helper', 'get_product_subscription_state' ) ) ) {
			$license = self::get_product_subscription_state_compat( $product_id );
		} else {
			$license = WC_Helper::get_product_subscription_state( $product_id );
		}

		return self::map_license_state_from_wc_helper( $license );
	}

	/**
	 * Returns if the product is restricted
	 *
	 * @param mixed $product_id The product ID.
	 * @return bool Returns true if there is a restriction for the product based on the license state and restriction data from WCCOM.
	 */
	public static function is_restricted( $product_id ) {
		$product_id = absint( $product_id );
		if ( ! $product_id ) {
			return false;
		}

		$license_state = self::find_license_state( $product_id );
		/**
		 * Filter the license state.
		 *
		 * @since 10.1.0
		 * @param string $license_state The license state.
		 */
		$license_state = apply_filters( 'woocommerce_composite_products_admin_restrictions_license_state', $license_state );

		// If license state is registered or expiring, bail out.
		if ( in_array( $license_state, array( self::LICENSE_STATE_LICENSE_REGISTERED, self::LICENSE_STATE_LICENSE_EXPIRING ), true ) ) {
			return false;
		}

		// If license state is invalid, bail out.
		if ( ! in_array( $license_state, self::LICENSE_STATES, true ) ) {
			return false;
		}

		$restriction_data = self::get_restriction_data( $product_id );
		/**
		 * Filter the restriction data for a given feature and action.
		 *
		 * @since 10.1.0
		 * @param array $restriction_data The restriction data for a given feature and action.
		 * @param string $feature The feature for which the restriction data is being fetched.
		 * @param string $action The action for which the restriction data is being fetched.
		 */
		$restriction_data = apply_filters( 'woocommerce_composite_products_admin_restrictions_data', $restriction_data );

		// We just need to check presence of $restriction_data (for now).
		return (bool) $restriction_data;
	}

	/**
	 * Wraps the core function `WC_Product_Usage::get_rules_for_product` to check for restrictions.
	 *
	 * @param mixed $product_id The product ID.
	 * @return array|null The restrictions array for the given feature and action, null if we can't fetch the data or if there is no restriction.
	 */
	private static function get_restriction_data( $product_id ) {
		// For WC versions older than 9.3, we don't have the helper method, so we try compatibility methods.
		if ( ! is_callable( array( 'WC_Product_Usage', 'get_rules_for_product' ) ) ) {
			return self::get_restriction_data_compat( $product_id );
		}

		$product_ruleset = WC_Product_Usage::get_rules_for_product( $product_id );

		if ( ! $product_ruleset ) {
			return null;
		}

		// The compat layer returns an array, therefore converting this rule set
		// to an array.
		return array(
			'name'          => $product_ruleset->get_rule( 'name' ),
			'regular_price' => $product_ruleset->get_rule( 'regular_price' ),
		);
	}

	/**
	 * A compatibility wrapper for WC versions > 5.6 to fetch restriction data.
	 *
	 * @param mixed $product_id The product ID.
	 * @return array|null The restrictions array for the given feature and action, null if we can't fetch the data or if there is no restriction.
	 */
	private static function get_restriction_data_compat( $product_id ) {
		if ( is_callable( array( 'WC_Helper', 'get_product_usage_notice_rules' ) ) ) {
			return self::get_restriction_data_get_product_usage_notice_rules( $product_id );
		}

		if ( is_callable( array( 'WC_Helper_API', 'get' ) ) ) {
			return self::get_restriction_data_wc_helper_api_get( $product_id );
		}

		return null;
	}


	/**
	 * Wraps the core function `WC_Helper::get_product_usage_notice_rules` to check for restrictions.
	 * This is helpful for WC 9.2, which does not have the helper SDK.
	 *
	 * @param mixed $product_id The product ID.
	 * @return array|null The restrictions array for the given feature and action, null if we can't fetch the data or if there is no restriction.
	 */
	private static function get_restriction_data_get_product_usage_notice_rules( $product_id ) {
		if ( ! is_callable( array( 'WC_Helper', 'get_product_usage_notice_rules' ) ) ) {
			return null;
		}

		$rules = WC_Helper::get_product_usage_notice_rules();
		if ( empty( $rules['restricted_products'][ $product_id ] ) ) {
			return null;
		}

		return $rules['restricted_products'][ $product_id ];
	}


	/**
	 * Get restriction data based on just the WC Helper API get. We use the same cache key here as in WC Core.
	 *
	 * @return array|null The restrictions array for the given feature and action, null if we can't fetch the data or if there is no restriction.
	 */
	private static function get_restriction_data_wc_helper_api_get() {
		if ( ! is_callable( array( 'WC_Helper_API', 'get' ) ) ) {
			return null;
		}

		$cache_key = '_woocommerce_helper_product_usage_notice_rules';
		$data      = get_transient( $cache_key );

		if ( false !== $data ) {
			return $data;
		}

		$response = WC_Helper_API::get(
			'product-usage-notice-rules',
			array(
				'authenticated' => false,
			)
		);

		// Retry in 15 minutes for non-200 response.
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			set_transient( $cache_key, array(), 15 * MINUTE_IN_SECONDS );
			return array();
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data ) || ! is_array( $data ) ) {
			$data = array();
		}

		set_transient( $cache_key, $data, 1 * HOUR_IN_SECONDS );
		return $data;
	}

	/**
	 * A compatibility layer for find product subscription state that reimplements some functions in WC Core.
	 * This is tested for WC versions > 5.6.
	 *
	 * @param mixed $product_id The product ID.
	 * @return array|null the product subscription state or null iif the site is not connected.
	 */
	private static function get_product_subscription_state_compat( $product_id ) {
		if ( ! self::is_site_connected_compat() ) {
			return null;
		}

		$product_subscriptions = wp_list_filter( self::get_installed_subscriptions_compat(), array( 'product_id' => $product_id ) );

		$subscription = ! empty( $product_subscriptions )
			? array_shift( $product_subscriptions )
			: array();

		$license_state = array(
			'unregistered' => empty( $subscription ),
			'expired'      => ( isset( $subscription['expired'] ) && $subscription['expired'] ),
			'expiring'     => ( isset( $subscription['expiring'] ) && $subscription['expiring'] ),
			'key'          => isset( $subscription['product_key'] ) ? $subscription['product_key'] : '',
			'order_id'     => isset( $subscription['order_id'] ) ? $subscription['order_id'] : '',
		);

		return $license_state;
	}

	/**
	 * A replacement for core WC_Helper::get_installed_subscriptions that reimplements some functions in WC Core.
	 * Tested with WC > 5.6
	 *
	 * @return array The installed subscriptions.
	 */
	private static function get_installed_subscriptions_compat() {
		static $installed_subscriptions = null;

		if ( ! is_callable( array( 'WC_Helper_Options', 'get' ) ) ) {
			return array();
		}

		if ( ! is_callable( array( 'WC_Helper', 'get_subscriptions' ) ) ) {
			return array();
		}

		// Cache installed_subscriptions in the current request.
		if ( is_null( $installed_subscriptions ) ) {
			$auth    = WC_Helper_Options::get( 'auth' );
			$site_id = isset( $auth['site_id'] ) ? absint( $auth['site_id'] ) : 0;
			if ( 0 === $site_id ) {
				$installed_subscriptions = array();
				return $installed_subscriptions;
			}

			$installed_subscriptions = array_filter(
				WC_Helper::get_subscriptions(),
				function ( $subscription ) use ( $site_id ) {
					return in_array( $site_id, $subscription['connections'], true );
				}
			);
		}

		return $installed_subscriptions;
	}

	/**
	 * A compatibility layer for is_site_connected that reimplements some functions in WC Core.
	 * This is tested for WC versions > 5.6.
	 *
	 * @return bool Returns true if the site is connected, false otherwise.
	 */
	private static function is_site_connected_compat() {
		if ( ! is_callable( array( 'WC_Helper_Options', 'get' ) ) ) {
			return false;
		}

		$auth = WC_Helper_Options::get( 'auth' );

		// If `access_token` is empty, there's no active connection.
		return ! empty( $auth['access_token'] );
	}

	/**
	 * Maps license state object from WC_Helper to easily understandable strings.
	 *
	 * @param mixed $license
	 *
	 * @return string|false. Returns a string representing the license state and false if the license state is invalid.
	 */
	private static function map_license_state_from_wc_helper( $license ) {
		if ( isset( $license['expired'] ) && $license['expired'] ) {
			return self::LICENSE_STATE_LICENSE_EXPIRED;
		}

		if ( isset( $license['expiring'] ) && $license['expiring'] ) {
			return self::LICENSE_STATE_LICENSE_EXPIRING;
		}

		if ( isset( $license['unregistered'] ) && $license['unregistered'] ) {
			return self::LICENSE_STATE_LICENSE_UNREGISTERED;
		}

		if ( isset( $license['key'] ) && $license['key'] ) {
			return self::LICENSE_STATE_LICENSE_REGISTERED;
		}

		// Invalid state.
		return false;
	}

	/**
	 * Get restriction message data based on WCCOM license state.
	 *
	 * @since 10.1.1
	 *
	 * @param int $product_id WCCOM product ID.
	 *
	 * @return array
	 */
	public static function get_restriction_message_data( $product_id ) {
		$state            = self::find_license_state( $product_id );
		$restriction_data = self::get_restriction_data( $product_id );

		$restriction_data['state'] = is_callable( array( 'WC_Helper', 'get_product_subscription_state' ) ) ? WC_Helper::get_product_subscription_state( $product_id ) : self::get_product_subscription_state_compat( $product_id );

		$message_data = array(
			'i18n_message'          => '',
			'i18n_message_with_cta' => '',
			'i18n_cta_text'         => '',
			'cta_url'               => '',
			'tracks_event'          => '',
		);
		switch ( $state ) {
			case self::LICENSE_STATE_LICENSE_EXPIRED:
				$message_data['i18n_message']          = self::get_expired_wccom_license_message( $product_id, $restriction_data );
				$message_data['i18n_message_with_cta'] = self::get_expired_wccom_license_message( $product_id, $restriction_data, true );
				/* translators: product price */
				$message_data['i18n_cta_text'] = ! empty( $restriction_data['regular_price'] ) ? sprintf( __( 'Renew for $%s', 'woocommerce-composite-products' ), $restriction_data['regular_price'] ) : __( 'Renew', 'woocommerce-composite-products' );
				$message_data['cta_url']       = self::get_url_to_renew_wccom_license( $product_id, $restriction_data );
				$message_data['tracks_event']  = 'product_restriction_renew_clicked';
				break;
			case self::LICENSE_STATE_LICENSE_UNREGISTERED:
				$message_data['i18n_message']          = self::get_unregistered_wccom_license_message( $product_id, $restriction_data );
				$message_data['i18n_message_with_cta'] = self::get_unregistered_wccom_license_message( $product_id, $restriction_data, true );
				/* translators: product price */
				$message_data['i18n_cta_text'] = ! empty( $restriction_data['regular_price'] ) ? sprintf( __( 'Subscribe for $%s', 'woocommerce-composite-products' ), $restriction_data['regular_price'] ) : __( 'Subscribe', 'woocommerce-composite-products' );
				$message_data['cta_url']       = self::get_url_to_subscribe_wccom_license( $product_id );
				$message_data['tracks_event']  = 'product_restriction_subscribe_clicked';
				break;
			case self::LICENSE_STATE_SITE_DISCONNECTED:
				$message_data['i18n_message']          = self::get_wccom_disconnected_message( $product_id, $restriction_data );
				$message_data['i18n_message_with_cta'] = self::get_wccom_disconnected_message( $product_id, $restriction_data, true );
				$message_data['i18n_cta_text']         = __( 'Connect your store', 'woocommerce-composite-products' );
				$message_data['cta_url']               = self::get_url_to_connect_to_wccom();
				$message_data['tracks_event']          = 'product_restriction_connect_clicked';
				break;
		}

		return $message_data;
	}

	/**
	 * Get the message when the site uses expired WCCOM license.
	 *
	 * @since 10.1.1
	 *
	 * @param int   $product_id       WCCOM product ID.
	 * @param array $restriction_data Contains data about the restriction (including product name and price).
	 * @param bool  $inline_cta       True will put a CTA link inside the message.
	 *                                Message with inline CTA is rendered as a notice.
	 *
	 * @return string
	 */
	public static function get_expired_wccom_license_message( $product_id, $restriction_data, $inline_cta = false ) {
		$message = ! empty( $restriction_data['name'] )
			/* translators: 1) product name */
			? sprintf( __( 'Your subscription for %1$s has expired. Renew to access this feature.', 'woocommerce-composite-products' ), $restriction_data['name'] )
			: __( 'Your subscription has expired. Renew to access this feature.', 'woocommerce-composite-products' );

		if ( $inline_cta ) {
			$renew_url = self::get_url_to_renew_wccom_license( $product_id, $restriction_data );
			$message   = ! empty( $restriction_data['name'] ) && ! empty( $restriction_data['regular_price'] )
				/* translators: 1) product name 2) URL to renew 3) product price */
				? sprintf( __( 'Your subscription for %1$s has expired. <a href="%2$s" target="_blank" class="cta">Renew for $%3$s</a> to continue using this feature.', 'woocommerce-composite-products' ), $restriction_data['name'], $renew_url, $restriction_data['regular_price'] )
				/* translators: 1) URL to renew */
				: sprintf( __( 'Your subscription has expired. <a href="%1$s" target="_blank" class="cta">Renew</a> to continue using this feature.', 'woocommerce-composite-products' ), $renew_url );
		}

		return $message;
	}

	/**
	 * Get the message when the site uses unregistered WCCOM license.
	 *
	 * @since 10.1.1
	 *
	 * @param int   $product_id       WCCOM product ID.
	 * @param array $restriction_data Contains data about the restriction (including product name and price).
	 * @param bool  $inline_cta       True will put a CTA link inside the message.
	 *                                Message with inline CTA is rendered as a notice.
	 *
	 * @return string
	 */
	public static function get_unregistered_wccom_license_message( $product_id, $restriction_data, $inline_cta = false ) {
		$message = ! empty( $restriction_data['name'] )
			/* translators: 1) product name */
			? sprintf( __( 'You don\'t have a subscription for %1$s. Subscribe to access this feature.', 'woocommerce-composite-products' ), $restriction_data['name'] )
			: __( 'You don\'t have a subscription. Subscribe to access this feature.', 'woocommerce-composite-products' );

		if ( $inline_cta ) {
			$subscribe_url = self::get_url_to_subscribe_wccom_license( $product_id );
			$message       = ! empty( $restriction_data['name'] ) && ! empty( $restriction_data['regular_price'] )
				/* translators: 1) product name 2) URL to subscribe 3) product price */
				? sprintf( __( 'You don\'t have a subscription for %1$s. <a href="%2$s" target="_blank" class="cta">Subscribe for $%3$s</a> to continue using this feature.', 'woocommerce-composite-products' ), $restriction_data['name'], $subscribe_url, $restriction_data['regular_price'] )
				/* translators: 1) URL to subscribe */
				: sprintf( __( 'You don\'t have a subscription. <a href="%1$s" target="_blank" class="cta">Subscribe</a> to continue using this feature.', 'woocommerce-composite-products' ), $subscribe_url );
		}

		return $message;
	}

	/**
	 * Get the message when the site is disconnected from WCCOM.
	 *
	 * @since 10.1.1
	 *
	 * @param int   $product_id       WCCOM product ID.
	 * @param array $restriction_data Contains data about the restriction (including product name and price).
	 * @param bool  $inline_cta       True will put a CTA link inside the message.
	 *                                Message with inline CTA is rendered as a notice.
	 *
	 * @return string
	 */
	public static function get_wccom_disconnected_message( $product_id, $restriction_data, $inline_cta = false ) {
		$message = ! empty( $restriction_data['name'] )
			/* translators: 1) product name */
			? sprintf( __( 'Your subscription for %1$s was not found. Connect your store to WooCommerce.com to access this feature.', 'woocommerce-composite-products' ), $restriction_data['name'] )
			: __( 'Your subscription was not found. Connect your store to WooCommerce.com to access this feature.', 'woocommerce-composite-products' );

		if ( $inline_cta ) {
			$connect_url = self::get_url_to_connect_to_wccom();
			$message     = ! empty( $restriction_data['name'] ) && ! empty( $restriction_data['regular_price'] )
				/* translators: 1) product name 2) URL to connect to WooCommerce.com */
				? sprintf( __( 'Your subscription for %1$s was not found. <a href="%2$s" class="cta">Connect your store</a> to WooCommerce.com to continue using this feature.', 'woocommerce-composite-products' ), $restriction_data['name'], $connect_url )
				/* translators: 1) URL to connect to WooCommerce.com */
				: sprintf( __( 'Your subscription was not found. <a href="#" class="cta">Connect your store</a> to WooCommerce.com to continue using this feature.', 'woocommerce-composite-products' ), $connect_url );
		}

		return $message;
	}

	/**
	 * Get URL to subscribe the CP license.
	 *
	 * @since 10.1.1
	 *
	 * @param int $product_id WCCOM product ID.
	 *
	 * @return string
	 */
	public static function get_url_to_subscribe_wccom_license( $product_id ) {
		return add_query_arg(
			array(
				'add-to-cart'  => $product_id,
				'utm_source'   => 'pu',
				'utm_campaign' => 'pu_restriction_subscribe',
			),
			'https://woocommerce.com/cart/'
		);
	}

	/**
	 * Get URL to renew the CP license.
	 *
	 * @since 10.1.1
	 *
	 * @return string
	 */
	public static function get_url_to_renew_wccom_license( $product_id, $restriction_data ) {
		$state = ! empty( $restriction_data['state'] ) ? $restriction_data['state'] : array();

		// Fallback to subscribe URL.
		if ( empty( $state['key'] ) && empty( $state['order_id'] ) ) {
			return self::get_url_to_subscribe_wccom_license( $product_id );
		}

		return add_query_arg(
			array(
				'renew_product' => $product_id,
				'product_key'   => $state['key'],
				'order_id'      => $state['order_id'],
				'utm_source'    => 'pu',
				'utm_campaign'  => 'pu_restriction_renew',
			),
			'https://woocommerce.com/cart/'
		);
	}


	/**
	 * Get URL to connect to WCCOM.
	 *
	 * @since 10.1.1
	 *
	 * @return string
	 */
	public static function get_url_to_connect_to_wccom() {
		return add_query_arg(
			array(
				'page'         => 'wc-admin',
				'tab'          => 'my-subscriptions',
				'path'         => rawurlencode( '/extensions' ),
				'utm_source'   => 'pu',
				'utm_campaign' => 'pu_restriction_notice_connect',
			),
			admin_url( 'admin.php' )
		);
	}
}
