<?php
/**
 * WC_CP_Core_Compatibility class
 *
 * @package  WooCommerce Composite Products
 * @since    3.5.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions related to WC core backwards compatibility.
 *
 * @class    WC_CP_Core_Compatibility
 * @version  10.2.0
 */
class WC_CP_Core_Compatibility {

	/*
	|--------------------------------------------------------------------------
	| Version check methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Cache 'gte' comparison results.
	 *
	 * @var array
	 */
	private static $is_wc_version_gte = array();

	/**
	 * Cache 'gt' comparison results.
	 *
	 * @var array
	 */
	private static $is_wc_version_gt = array();

	/**
	 * Cache 'gt' comparison results for WP version.
	 *
	 * @since  5.0.5
	 * @var    array
	 */
	private static $is_wp_version_gt = array();

	/**
	 * Cache 'gte' comparison results for WP version.
	 *
	 * @since  5.0.5
	 * @var    array
	 */
	private static $is_wp_version_gte = array();

	/**
	 * Cache 'is_wc_admin_active' result.
	 *
	 * @since 8.3.0
	 * @var   bool
	 */
	private static $is_wc_admin_active;

	/**
	 * Current REST request stack.
	 * An array containing WP_REST_Request instances.
	 *
	 * @since 9.0.3
	 *
	 * @var array
	 */
	private static $requests = array();

	/**
	 * Constructor.
	 */
	public static function init() {
		// Save current rest request. Is there a better way to get it?
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'save_rest_request' ), 10, 3 );
		add_filter( 'woocommerce_hydration_dispatch_request', array( __CLASS__, 'save_hydration_request' ), 10, 2 );
		add_filter( 'rest_request_after_callbacks', array( __CLASS__, 'pop_rest_request' ), PHP_INT_MAX );
		add_filter( 'woocommerce_hydration_request_after_callbacks', array( __CLASS__, 'pop_rest_request' ), PHP_INT_MAX );

		// Refactoring of Analytics in WC 9.3 deprecated Query class (https://github.com/woocommerce/woocommerce/pull/49425).
		if ( version_compare( WC_VERSION, '9.3', '<' ) && ! class_exists( 'Automattic\WooCommerce\Admin\API\Reports\GenericQuery' ) ) {
			class_alias( 'Automattic\WooCommerce\Admin\API\Reports\Query', 'Automattic\WooCommerce\Admin\API\Reports\GenericQuery' );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Callbacks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Pops the current request from the execution stack.
	 *
	 * @since  9.0.3
	 *
	 * @param  WP_REST_Response     $response
	 * @param  WP_REST_Server|array $handler
	 * @param  WP_REST_Request      $request
	 * @return mixed
	 */
	public static function pop_rest_request( $response ) {
		if ( ! empty( self::$requests ) && is_array( self::$requests ) ) {
			array_pop( self::$requests );
		}

		return $response;
	}

	/**
	 * Saves the current hydration request.
	 *
	 * @since  9.0.3
	 *
	 * @param  mixed           $result
	 * @param  WP_REST_Request $request
	 * @return mixed
	 */
	public static function save_hydration_request( $result, $request ) {
		if ( ! is_array( self::$requests ) ) {
			self::$requests = array();
		}

		self::$requests[] = $request;
		return $result;
	}

	/**
	 * Saves the current rest request.
	 *
	 * @since  8.4.0
	 *
	 * @param  mixed           $result
	 * @param  WP_REST_Server  $server
	 * @param  WP_REST_Request $request
	 * @return mixed
	 */
	public static function save_rest_request( $result, $server, $request ) {
		if ( ! is_array( self::$requests ) ) {
			self::$requests = array();
		}

		self::$requests[] = $request;
		return $result;
	}

	/*
	|--------------------------------------------------------------------------
	| Utilities.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @since  3.2.0
	 *
	 * @return string
	 */
	public static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.5 or greater.
	 *
	 * @since  3.2.0
	 *
	 * @return boolean
	 */
	public static function use_wc_ajax() {
		return apply_filters( 'woocommerce_composite_use_wc_ajax', true );
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @since  3.9.0
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_gte( $version ) {
		if ( ! isset( self::$is_wc_version_gte[ $version ] ) ) {
			self::$is_wc_version_gte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
		}
		return self::$is_wc_version_gte[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @since  3.0.0
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_gt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @since  5.0.5
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wp_version_gt( $version ) {
		if ( ! isset( self::$is_wp_version_gt[ $version ] ) ) {
			global $wp_version;
			self::$is_wp_version_gt[ $version ] = $wp_version && version_compare( WC_CP()->plugin_version( true, $wp_version ), $version, '>' );
		}
		return self::$is_wp_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @since  5.0.5
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wp_version_gte( $version ) {
		if ( ! isset( self::$is_wp_version_gte[ $version ] ) ) {
			global $wp_version;
			self::$is_wp_version_gte[ $version ] = $wp_version && version_compare( WC_CP()->plugin_version( true, $wp_version ), $version, '>=' );
		}
		return self::$is_wp_version_gte[ $version ];
	}

	/**
	 * Whether this is a Store/REST API request.
	 *
	 * @since  8.4.0
	 *
	 * @return boolean
	 */
	public static function is_api_request() {
		return self::is_store_api_request() || self::is_rest_api_request();
	}

	/**
	 * Returns the current Store/REST API request or false.
	 *
	 * @since  8.4.0
	 *
	 * @return WP_REST_Request|false
	 */
	public static function get_api_request() {
		if ( empty( self::$requests ) || ! is_array( self::$requests ) ) {
			return false;
		}

		return end( self::$requests );
	}

	/**
	 * Whether this is a Store API request.
	 *
	 * @since  8.4.0
	 *
	 * @param  string $route
	 * @return boolean
	 */
	public static function is_store_api_request( $route = '' ) {

		// Check the request URI.
		$request = self::get_api_request();

		if ( false !== $request && strpos( $request->get_route(), 'wc/store' ) !== false ) {
			if ( '' === $route || strpos( $request->get_route(), $route ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/*
	|--------------------------------------------------------------------------
	| Compatibility wrappers.
	|--------------------------------------------------------------------------
	*/
	/**
	 * Backwards compatible logging using 'WC_Logger' class.
	 *
	 * @since  3.9.0
	 *
	 * @param  string $message
	 * @param  string $level
	 * @param  string $context
	 */
	public static function log( $message, $level, $context ) {
		$logger = wc_get_logger();
		$logger->log( $level, $message, array( 'source' => $context ) );
	}

	/**
	 * Back-compat wrapper for 'get_parent_id' with fallback to 'get_id'.
	 *
	 * @since  3.9.3
	 *
	 * @param  WC_Product $product
	 * @return mixed
	 */
	public static function get_product_id( $product ) {
		$parent_id = $product->get_parent_id();
		return $parent_id ? $parent_id : $product->get_id();
	}

	/**
	 * Back-compat wrapper for 'is_rest_api_request'.
	 *
	 * @since  4.1.1
	 *
	 * @return boolean
	 */
	public static function is_rest_api_request() {

		if ( ! isset( $_SERVER['REQUEST_URI'] ) || false === strpos( wc_clean( wp_unslash( $_SERVER['REQUEST_URI'] ) ), rest_get_url_prefix() ) ) {
			return false;
		}

		if ( false !== self::get_api_request() ) {
			return true;
		}

		return method_exists( WC(), 'is_rest_api_request' ) ? WC()->is_rest_api_request() : defined( 'REST_REQUEST' );
	}

	/**
	 *
	 * Whether this is a Store Editor REST API request.
	 *
	 * @since  8.10.3
	 *
	 * @return boolean
	 */
	public static function is_block_editor_api_request( $route = '' ) {

		if ( ! self::is_rest_api_request() ) {
			return false;
		}

		$request = self::get_api_request();

		if ( false !== $request && strpos( $request->get_route(), '/pages/' ) !== false ) {
			if ( '' === $route || strpos( $request->get_route(), $route ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * True if 'wc-admin' is active.
	 *
	 * @since  8.3.0
	 *
	 * @return boolean
	 */
	public static function is_wc_admin_active() {

		if ( ! isset( self::$is_wc_admin_active ) ) {

			$enabled = defined( 'WC_ADMIN_VERSION_NUMBER' ) && version_compare( WC_ADMIN_VERSION_NUMBER, '1.0.0', '>=' );
			if ( $enabled && version_compare( WC_ADMIN_VERSION_NUMBER, '2.3.0', '>=' ) && true === apply_filters( 'woocommerce_admin_disabled', false ) ) {
				$enabled = false;
			}

			self::$is_wc_admin_active = $enabled;
		}

		return self::$is_wc_admin_active;
	}

	/**
	 * Returns true if is a react based admin page.
	 *
	 * @since  8.4.2
	 *
	 * @return boolean
	 */
	public static function is_admin_or_embed_page() {

		if ( class_exists( '\Automattic\WooCommerce\Admin\PageController' ) && method_exists( '\Automattic\WooCommerce\Admin\PageController', 'is_admin_or_embed_page' ) ) {

			return \Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page();

		} elseif ( class_exists( '\Automattic\WooCommerce\Admin\Loader' ) && method_exists( '\Automattic\WooCommerce\Admin\Loader', 'is_admin_or_embed_page' ) ) {

			return \Automattic\WooCommerce\Admin\Loader::is_admin_or_embed_page();
		}

		return false;
	}

	/**
	 * Returns true if site is using block theme.
	 *
	 * @since  8.10.0
	 *
	 * @return boolean
	 */
	public static function wc_current_theme_is_fse_theme() {
		return function_exists( 'wc_current_theme_is_fse_theme' ) ? wc_current_theme_is_fse_theme() : false;
	}
}

WC_CP_Core_Compatibility::init();
