<?php
/**
 * WC_CP_Addons_Compatibility class
 *
 * @package  WooCommerce Composite Products
 * @since    3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds hooks for Product Add-Ons Compatibility.
 *
 * @version 10.2.0
 */
class WC_CP_Addons_Compatibility {
	// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

	public static $addons_prefix             = '';
	public static $compat_composited_product = '';

	private static $current_component = false;

	public static function init() {

		// Add Addons script as a dependency to the Composite Products script.
		add_filter( 'woocommerce_composite_script_dependencies', array( __CLASS__, 'add_script_dependency' ), 10, 1 );

		// Support for Product Addons.
		add_action( 'woocommerce_composited_product_add_to_cart', array( __CLASS__, 'addons_display_support' ), 10, 3 );
		add_action( 'woocommerce_composited_single_variation', array( __CLASS__, 'addons_display_support' ), 15, 3 );

		// Prefix form fields.
		add_filter( 'product_addons_field_prefix', array( __CLASS__, 'addons_cart_prefix' ), 9, 2 );

		// Validate add to cart addons.
		add_filter( 'woocommerce_composite_component_add_to_cart_validation', array( __CLASS__, 'validate_component_addons' ), 10, 7 );

		// Add addons identifier to composited item stamp.
		add_filter( 'woocommerce_composite_component_cart_item_identifier', array( __CLASS__, 'composited_item_addons_identifier' ), 10, 2 );

		// Before and after add-to-cart handling.
		add_action( 'woocommerce_composited_product_before_add_to_cart', array( __CLASS__, 'before_composited_add_to_cart' ), 10, 5 );
		add_action( 'woocommerce_composited_product_after_add_to_cart', array( __CLASS__, 'after_composited_add_to_cart' ), 10, 5 );

		// Load child addons data from the parent cart item data array.
		add_filter( 'woocommerce_composited_cart_item_data', array( __CLASS__, 'get_composited_cart_item_data_from_parent' ), 10, 2 );

		// Add option to disable Addons at component level.
		add_action( 'woocommerce_composite_component_admin_advanced_selection_details_options', array( __CLASS__, 'component_addons_disable' ), 40, 3 );

		// Save option to disable Addons at component level.
		add_filter( 'woocommerce_composite_process_component_data', array( __CLASS__, 'process_component_addons_disable' ), 10, 4 );

		// Separate support for Product Addons in the admin.
		add_action( 'woocommerce_composite_after_order_item_form', array( __CLASS__, 'admin_render_composite_addons' ), 10, 4 );
		add_action( 'woocommerce_composited_product_add_to_cart', array( __CLASS__, 'admin_render_composite_item_addons' ), 10, 3 );
		add_action( 'woocommerce_composite_component_validation_add_to_order', array( __CLASS__, 'admin_validate_composite_component_addons' ), 10, 6 );

		add_filter( 'woocommerce_editing_composite_in_order_configuration', array( __CLASS__, 'editing_composite_item_addons' ), 10, 4 );

		add_action( 'woocommerce_before_editing_composite_in_order', array( __CLASS__, 'store_composite_item_addons' ), 10, 2 );
		add_action( 'woocommerce_editing_composite_in_order', array( __CLASS__, 'copy_composite_item_addons' ), 10, 3 );

		add_action( 'woocommerce_composite_added_to_order', array( __CLASS__, 'store_component_item_addons' ), 10, 9 );
		add_action( 'woocommerce_product_addons_display_editing_in_order_button', array( __CLASS__, 'maybe_hide_edit_button' ), 10, 2 );

		// Enable edit-in-cart feature if any items have addons.
		add_filter( 'woocommerce_is_composite_container_order_item_editable', array( __CLASS__, 'addon_composite_editable_in_cart' ), 10, 3 );

		// Allow pre-populate of add-on fields based on cart key.
		add_action( 'woocommerce_product_addons_parse_cart_addons', array( __CLASS__, 'parse_composite_addons' ), 10, 3 );
		add_filter( 'woocommerce_product_addons_cart_permalink', array( __CLASS__, 'add_cart_key_to_permalink' ), 10, 2 );
		add_filter( 'woocommerce_composite_cart_permalink_args', array( __CLASS__, 'maybe_add_cart_key_to_permalink_args' ), 10, 2 );

		/*
		 * Aggregate add-on costs and calculate them after CP has applied discounts.
		 * Also, do not charge anything for add-ons if Priced Individually is disabled and the 'filters' cart pricing method is in use.
		 */
		if ( 'filters' === WC_CP_Products::get_composited_cart_item_discount_method() ) {

			// Aggregate add-ons costs and calculate them after CP has applied discounts.
			add_filter( 'woocommerce_composited_cart_item', array( __CLASS__, 'preprocess_composited_cart_item_addon_data' ), 0, 2 );

			// Do not let add-ons adjust prices when CP modifies them.
			add_filter( 'woocommerce_product_addons_adjust_price', array( __CLASS__, 'adjust_addons_price' ), 15, 2 );

			// Remove component add-on prices in composite product pages.
			add_action( 'woocommerce_composite_products_apply_product_filters', array( __CLASS__, 'add_addon_price_zero_filter' ) );
			add_action( 'woocommerce_composite_products_remove_product_filters', array( __CLASS__, 'remove_addon_price_zero_filter' ) );
		}

		// Rest API support.
		add_filter( 'woocommerce_composite_products_rest_api_product_schema', array( __CLASS__, 'rest_api_product_schema' ), 10 );
		add_filter( 'woocommerce_composite_products_rest_api_get_component_data', array( __CLASS__, 'rest_api_get_component_data' ), 10, 3 );
		add_filter( 'woocommerce_composite_products_rest_api_update_component_data', array( __CLASS__, 'rest_api_update_component_data' ), 10, 4 );
	}

	/**
	 * Used to tell if a product has (required) addons.
	 *
	 * @since  4.0.0
	 *
	 * @param  mixed $product
	 * @param  mixed $type
	 * @return boolean
	 */
	public static function has_addons( $product, $type = '' ) {

		// Backwards compatibility:
		// has_addons used to be called with a true argument to check if there were any required add-ons.
		if ( is_bool( $type ) ) {
			$type = $type ? 'required' : '';
		}

		if ( is_object( $product ) && is_a( $product, 'WC_Product' ) ) {
			$product_id = $product->get_id();
		} else {
			$product_id = absint( $product );
		}

		$has_addons = false;
		$cache_key  = 'product_addons_' . $product_id;

		$addons = WC_CP_Helpers::cache_get( $cache_key );

		if ( is_null( $addons ) ) {
			$addons = WC_Product_Addons_Helper::get_product_addons( $product_id, false, false );
			WC_CP_Helpers::cache_set( $cache_key, $addons );
		}

		if ( ! empty( $addons ) ) {

			if ( 'required' === $type ) {

				foreach ( $addons as $addon ) {

					$type = ! empty( $addon['type'] ) ? $addon['type'] : '';

					if ( 'heading' !== $type && isset( $addon['required'] ) && '1' == $addon['required'] ) {
						$has_addons = true;
						break;
					}
				}
			} elseif ( 'restricted' === $type ) {

				foreach ( $addons as $addon ) {

					$type = ! empty( $addon['type'] ) ? $addon['type'] : '';

					if ( 'custom_text' === $type && isset( $addon['restrictions_type'] ) && 'any_text' !== $addon['restrictions_type'] ) {
						$has_addons = true;
						break;
					}
				}
			} else {
				$has_addons = true;
			}
		}

		return $has_addons;
	}

	/**
	 * Add Addons script as a dependency to the Composite Products script.
	 *
	 * @since  8.7.0
	 *
	 * @param  array  add_script_dependency
	 * @return array
	 */
	public static function add_script_dependency( $dependencies ) {

		$dependencies[] = 'woocommerce-addons';

		return $dependencies;
	}

	/**
	 * Save option to disable addons at component level.
	 *
	 * @since  3.6.6
	 *
	 * @param  array  $component_data
	 * @param  array  $posted_component_data
	 * @param  string $component_id
	 * @param  string $composite_id
	 * @return array
	 */
	public static function process_component_addons_disable( $component_data, $posted_component_data, $component_id, $composite_id ) {

		$component_data['disable_addons'] = empty( $posted_component_data['show_addons'] ) ? 'yes' : 'no';

		return $component_data;
	}

	/**
	 * Show option to disable addons at Component level.
	 *
	 * @since  3.6.6
	 *
	 * @param  string $id
	 * @param  array  $data
	 * @param  string $product_id
	 * @return void
	 */
	public static function component_addons_disable( $id, $data, $product_id ) {

		$disable_addons = ( isset( $data['disable_addons'] ) && $data['disable_addons'] === 'yes' ) ? 'yes' : 'no';

		?>
		<div class="component_selection_details_option">
			<input type="checkbox" class="checkbox"<?php echo 'no' === $disable_addons ? ' checked="checked"' : ''; ?> name="bto_data[<?php echo esc_attr( $id ); ?>][show_addons]" <?php echo 'no' === $disable_addons ? 'value="1"' : ''; ?>/>
			<span class="labelspan"><?php esc_html_e( 'Product Add-Ons', 'woocommerce-composite-products' ); ?></span>
			<?php echo wc_help_tip( __( 'Enable/disable Product Add-Ons of the selected option.', 'woocommerce-composite-products' ) ); ?>
		</div>
		<?php
	}

	/**
	 * Outputs add-ons for composited products.
	 *
	 * @param  WC_Product           $product
	 * @param  int                  $component_id
	 * @param  WC_Product_Composite $composite_product
	 * @return void
	 */
	public static function addons_display_support( $composited_product, $component_id, $composite_product ) {

		global $Product_Addon_Display, $product;

		if ( ! empty( $Product_Addon_Display ) ) {

			if ( doing_action( 'wp_ajax_woocommerce_configure_composite_order_item' ) || doing_action( 'wp_ajax_woocommerce_get_composited_product_data' ) ) {
				return;
			}

			$component = $composite_product->get_component( $component_id );

			if ( ! empty( $component ) && $component->disable_addons() ) {
				return;
			}

			if ( $composited_product->is_type( 'variable' ) && doing_action( 'woocommerce_composited_product_add_to_cart' ) ) {
				return;
			}

			$product_bak = isset( $product ) ? $product : false;
			$product     = $composited_product;
			$product_id  = $product->get_id();

			self::$compat_composited_product = $composited_product;
			$Product_Addon_Display->display( $product_id, $component_id . '-' );
			self::$compat_composited_product = '';

			if ( $product_bak ) {
				$product = $product_bak;
			}
		}
	}

	/**
	 * Sets a prefix for unique add-ons.
	 *
	 * @param  string $prefix
	 * @param  int    $product_id
	 * @return string
	 */
	public static function addons_cart_prefix( $prefix, $product_id ) {

		if ( ! empty( self::$addons_prefix ) ) {
			return self::$addons_prefix . '-';
		}

		return $prefix;
	}

	/**
	 * Add some contextual info to addons validation messages.
	 *
	 * @param  string $message
	 * @return string
	 */
	public static function component_addons_error_message_context( $message ) {

		if ( false !== self::$current_component ) {
			/* translators: %1$s: Component title, %2$s: Message. */
			$message = sprintf( __( 'Please check your &quot;%1$s&quot; configuration: %2$s', 'woocommerce-composite-products' ), self::$current_component->get_title(), $message );
		}

		return $message;
	}

	/**
	 * Validate composited item addons.
	 *
	 * @param  bool                 $add
	 * @param  int                  $composite_id
	 * @param  int                  $component_id
	 * @param  int                  $product_id
	 * @param  int                  $quantity
	 * @param  array                $cart_item_data
	 * @param  WC_Product_Composite $composite
	 * @return bool
	 */
	public static function validate_component_addons( $add, $composite_id, $component_id, $product_id, $quantity, $cart_item_data, $composite ) {

		// No option selected? Nothing to see here.
		if ( '0' === $product_id ) {
			return $add;
		}

		// Ordering again? When ordering again, do not revalidate addons.
		$order_again = isset( $_GET['order_again'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wc_clean( $_GET['_wpnonce'] ), 'woocommerce-order_again' );

		if ( $order_again ) {
			return $add;
		}

		// Validate addons.
		global $Product_Addon_Cart;

		if ( ! empty( $Product_Addon_Cart ) ) {

			$component      = $composite->get_component( $component_id );
			$disable_addons = ! empty( $component ) && $component->disable_addons();

			self::$addons_prefix = $component_id;

			add_filter( 'woocommerce_add_error', array( __CLASS__, 'component_addons_error_message_context' ) );

			self::$current_component = $composite->get_component( $component_id );

			if ( false === $disable_addons && false === $Product_Addon_Cart->validate_add_cart_item( true, $product_id, $quantity ) ) {
				$add = false;
			}

			self::$current_component = false;

			remove_filter( 'woocommerce_add_error', array( __CLASS__, 'component_addons_error_message_context' ) );

			self::$addons_prefix = '';
		}

		return $add;
	}

	/**
	 * Add addons identifier to composited item stamp, in order to generate new cart ids for composites with different addons configurations.
	 *
	 * @param  array  $composited_item_identifier
	 * @param  string $composited_item_id
	 * @return array
	 */
	public static function composited_item_addons_identifier( $composited_item_identifier, $composited_item_id ) {

		global $Product_Addon_Cart;

		// Store composited item addons add-ons config in indentifier to avoid generating the same composite cart id.
		if ( ! empty( $Product_Addon_Cart ) ) {

			$addon_data = array();

			// Set addons prefix.
			self::$addons_prefix = $composited_item_id;

			$composited_product_id = $composited_item_identifier['product_id'];

			$addon_data = $Product_Addon_Cart->add_cart_item_data( $addon_data, $composited_product_id );

			// Reset addons prefix.
			self::$addons_prefix = '';

			if ( ! empty( $addon_data['addons'] ) ) {
				$composited_item_identifier['addons'] = $addon_data['addons'];
			}
		}

		return $composited_item_identifier;
	}

	/**
	 * Runs before adding a composited item to the cart.
	 *
	 * @param  int   $product_id
	 * @param  int   $quantity
	 * @param  int   $variation_id
	 * @param  array $variations
	 * @param  array $composited_item_cart_data
	 * @return void
	 */
	public static function before_composited_add_to_cart( $product_id, $quantity, $variation_id, $variations, $composited_item_cart_data ) {

		global $Product_Addon_Cart;

		// Set addons prefix.
		self::$addons_prefix = $composited_item_cart_data['composite_item'];

		// Add-ons cart item data is already stored in the composite_data array, so we can grab it from there instead of allowing Addons to re-add it
		// Not doing so results in issues with file upload validation.

		if ( ! empty( $Product_Addon_Cart ) ) {
			remove_filter( 'woocommerce_add_cart_item_data', array( $Product_Addon_Cart, 'add_cart_item_data' ), 10, 2 );
		}
	}

	/**
	 * Runs after adding a composited item to the cart.
	 *
	 * @param  int   $product_id
	 * @param  int   $quantity
	 * @param  int   $variation_id
	 * @param  array $variations
	 * @param  array $composited_item_cart_data
	 * @return void
	 */
	public static function after_composited_add_to_cart( $product_id, $quantity, $variation_id, $variations, $composited_item_cart_data ) {

		global $Product_Addon_Cart;

		// Reset addons prefix.
		self::$addons_prefix = '';

		if ( ! empty( $Product_Addon_Cart ) ) {
			add_filter( 'woocommerce_add_cart_item_data', array( $Product_Addon_Cart, 'add_cart_item_data' ), 10, 2 );
		}
	}

	/**
	 * Retrieve child cart item data from the parent cart item data array, if necessary.
	 *
	 * @param  array $composited_item_cart_data
	 * @param  array $cart_item_data
	 * @return array
	 */
	public static function get_composited_cart_item_data_from_parent( $composited_item_cart_data, $cart_item_data ) {

		// Add-ons cart item data is already stored in the composite_data array, so we can grab it from there instead of allowing Addons to re-add it.
		if ( isset( $composited_item_cart_data['composite_item'] ) && isset( $cart_item_data['composite_data'][ $composited_item_cart_data['composite_item'] ]['addons'] ) ) {
			$composited_item_cart_data['addons'] = $cart_item_data['composite_data'][ $composited_item_cart_data['composite_item'] ]['addons'];
		}

		return $composited_item_cart_data;
	}

	/**
	 * Aggregate add-ons costs and calculate them after CP has applied discounts.
	 *
	 * @since  6.0.4
	 *
	 * @param  array                $cart_item
	 * @param  WC_Product_Composite $composite
	 * @param  array|boolean        $cart_contents
	 * @return array
	 */
	public static function preprocess_composited_cart_item_addon_data( $cart_item, $composite, $cart_contents = false ) {

		if ( empty( $cart_item['addons'] ) ) {
			return $cart_item;
		}

		$component_id     = $cart_item['composite_item'];
		$component_option = $composite->get_component_option( $component_id, $cart_item['product_id'] );

		if ( ! $component_option ) {
			return $cart_item;
		}

		if ( $component_option->is_priced_individually() ) {

			// Let PAO handle things on its own.
			if ( ! $discount = $component_option->get_discount() ) {
				return $cart_item;
			}

			WC_CP()->product_data->set( $cart_item['data'], 'composited_price_offset_pct', array() );
			WC_CP()->product_data->set( $cart_item['data'], 'composited_price_offset', 0.0 );

			if ( $composite_container_item = wc_cp_get_composited_cart_item_container( $cart_item, $cart_contents ) ) {

				// Read original % values from parent item.
				$addons_data = ! empty( $composite_container_item['composite_data'][ $component_id ]['addons'] ) ? $composite_container_item['composite_data'][ $component_id ]['addons'] : array();
				$flat_fees   = 0;

				foreach ( $addons_data as $addon_key => $addon ) {

					$composited_price_offset_pct = WC_CP()->product_data->get( $cart_item['data'], 'composited_price_offset_pct' );
					$composited_price_offset     = WC_CP()->product_data->get( $cart_item['data'], 'composited_price_offset' );

					// See WC_CP_Products::filter_get_price (and Product Bundles where the code was copied from).
					if ( 'percentage_based' === $addon['price_type'] ) {
						$composited_price_offset_pct[]              = $addon['price'];
						$cart_item['addons'][ $addon_key ]['price'] = ( ( 100 - $discount ) / 100 ) * $addon['price'];

						WC_CP()->product_data->set( $cart_item['data'], 'composited_price_offset_pct', $composited_price_offset_pct );

					} elseif ( 'flat_fee' === $addon['price_type'] ) {
						$composited_price_offset += (float) $addon['price'] / $cart_item['quantity'];
						$flat_fees               += (float) $addon['price'] / $cart_item['quantity'];

						WC_CP()->product_data->set( $cart_item['data'], 'composited_price_offset', $composited_price_offset );
					} else {
						$composited_price_offset += (float) $addon['price'];
						WC_CP()->product_data->set( $cart_item['data'], 'composited_price_offset', $composited_price_offset );
					}
				}

				$cart_item['addons_flat_fees_sum'] = $flat_fees;
			}
		} else {

			// Priced Individually disabled? Give add-ons for free.
			foreach ( $cart_item['addons'] as $addon_key => $addon_data ) {
				$cart_item['addons'][ $addon_key ]['price'] = 0.0;
			}
		}

		return $cart_item;
	}

	/**
	 * Do not let add-ons adjust prices when CP modifies them.
	 *
	 * @since  6.0.4
	 *
	 * @param  bool  $adjust
	 * @param  array $cart_item
	 * @return bool
	 */
	public static function adjust_addons_price( $adjust, $cart_item ) {

		if ( $composite_container_item = wc_cp_get_composited_cart_item_container( $cart_item ) ) {

			$adjust           = false;
			$composite        = $composite_container_item['data'];
			$component_id     = $cart_item['composite_item'];
			$component_option = $composite->get_component_option( $component_id, $cart_item['product_id'] );

			// Only let add-ons adjust prices if CP doesn't modify component option prices in any way.
			if ( $component_option->is_priced_individually() && ! $component_option->get_discount() ) {
				$adjust = true;
			}
		}

		return $adjust;
	}

	/**
	 * Adds filter that discards component add-on prices in composite product pages.
	 *
	 * @since  6.0.4
	 */
	public static function add_addon_price_zero_filter() {

		$component_option = WC_CP_Products::get_filtered_component_option();

		if ( $component_option && false === $component_option->is_priced_individually() ) {
			add_filter( 'woocommerce_product_addons_price_raw', array( __CLASS__, 'option_price_raw_zero_filter' ) );
			add_filter( 'woocommerce_product_addons_option_price_raw', array( __CLASS__, 'option_price_raw_zero_filter' ) );
		}
	}

	/**
	 * Removes filter that discards component add-on prices in composite product pages.
	 *
	 * @param  WC_CP_Product $filtered_component_option
	 *
	 * @since  6.0.4
	 */
	public static function remove_addon_price_zero_filter( $component_option ) {

		if ( $component_option && false === $component_option->is_priced_individually() ) {
			remove_filter( 'woocommerce_product_addons_price_raw', array( __CLASS__, 'option_price_raw_zero_filter' ) );
			remove_filter( 'woocommerce_product_addons_option_price_raw', array( __CLASS__, 'option_price_raw_zero_filter' ) );
		}
	}

	/**
	 * Discards component add-on prices in composite product pages.
	 *
	 * @since  6.0.4
	 *
	 * @param  mixed $price
	 */
	public static function option_price_raw_zero_filter( $price ) {
		return '';
	}

	/**
	 * Add support for REST API.
	 *
	 * @since  9.0.2
	 *
	 * @param  array $schema
	 * @return array
	 */
	public static function rest_api_product_schema( $schema ) {

		if ( ! isset( $schema['composite_components']['items']['properties'] ) ) {
			return $schema;
		}

		$schema['composite_components']['items']['properties']['product_addons_visible'] = array(
			'description' => __( 'Controls the visibility of product addons in the Component Selection view.', 'woocommerce-composite-products' ),
			'type'        => 'boolean',
			'context'     => array( 'view', 'edit' ),
		);

		return $schema;
	}

	/**
	 * Expose product addons visibility in the REST API in get context.
	 *
	 * @since  9.0.2
	 *
	 * @param  array        $component_data        Component data to be passed to the REST API.
	 * @param  array|object $component             Component object.
	 * @param  int          $composite_product_id  Composite product ID.
	 *
	 * @return array
	 */
	public static function rest_api_get_component_data( $component_data, $component, $composite_product_id ) {

		$component_data['product_addons_visible'] = ( false === $component->disable_addons() );

		return $component_data;
	}

	/**
	 * Set product addons visibility in the REST API in update context.
	 *
	 * @since  9.0.2
	 *
	 * @param  array  $component_data        Component data to be passed to the REST API.
	 * @param  string $action                Create or update.
	 * @param  array  $data                  Data sent from the body of the REST API call.
	 * @param  int    $composite_product_id  Composite product ID.
	 *
	 * @return array
	 */
	public static function rest_api_update_component_data( $component_data, $action, $data, $composite_product_id ) {

		if ( 'create' === $action || 'update' === $action ) {
			$product_addons_visible = isset( $data['product_addons_visible'] ) && false === $data['product_addons_visible'] ? false : true;

			$component_data['disable_addons'] = $product_addons_visible ? 'no' : 'yes';
		}

		return $component_data;
	}

	/**
	 * Render addons for parent product.
	 *
	 * @param WC_Product_Composite $product The product.
	 * @param WC_Order_Item        $item The item.
	 * @param WC_Order             $order The order.
	 */
	public static function admin_render_composite_addons( $product, $item, $order ) {
		if ( ! doing_action( 'wp_ajax_woocommerce_configure_composite_order_item' ) ) {
			return;
		}

		// addon-start.php from versions 7.2.1 or below require the $product global.
		if ( defined( 'WC_PRODUCT_ADDONS_VERSION' ) && version_compare( WC_PRODUCT_ADDONS_VERSION, '7.2.1' ) <= 0 ) {
			$GLOBALS['product'] = $product;
		}

		$html = WC_Product_Addons_Admin_Ajax::render_form( $order, $item, $product );

		if ( false !== $html ) {
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="wc-pao-addons-container wc-composite-addons-container">';
			echo $html;
			echo '</div>';
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Support for component item addons.
	 *
	 * @param  WC_Product           $product            The product object.
	 * @param  int                  $component_id       The component ID.
	 * @param  WC_Product_Composite $composite_product  The composite product object.
	 *
	 * @return void
	 */
	public static function admin_render_composite_item_addons( $composited_product, $component_id, $composite_product ) {
		if ( ! doing_action( 'wp_ajax_woocommerce_configure_composite_order_item' ) && ! doing_action( 'wp_ajax_woocommerce_get_composited_product_data' ) ) {
			return;
		}

		$component = $composite_product->get_component( $component_id );

		if ( ! empty( $component ) && $component->disable_addons() ) {
			return;
		}

		$item_to_render = null;
		try {
			list( $order, $item, $product ) = WC_Product_Addons_Admin_Ajax::validate_request_and_fetch_data();

			$composited_order_items = wc_cp_get_composited_order_items( $item, $order );
			foreach ( $composited_order_items as $composited_order_item ) {
				if ( $composited_order_item['product_id'] === $composited_product->get_id() ) {
					$item_to_render = $composited_order_item;
					break;
				}
			}
		} catch ( Exception $e ) {
			// Ignore this exception.
			$order = null;
		}

		self::$addons_prefix             = $component_id;
		self::$compat_composited_product = $composited_product;

		$html = WC_Product_Addons_Admin_Ajax::render_form( $order, $item_to_render, $composited_product );

		self::$addons_prefix             = '';
		self::$compat_composited_product = '';

		if ( false !== $html ) {
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="wc-pao-addons-container">';
			echo $html;
			echo '</div>';
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Add addons to the posted configuration when editing in order.
	 *
	 * @param  array $configuration  The configuration.
	 * @param  array $product        The product.
	 * @param  array $item           The item.
	 * @param  array $order          The order.
	 *
	 * @return array
	 */
	public static function editing_composite_item_addons( $configuration, $product, $item, $order ) {
		if ( ! doing_action( 'wp_ajax_woocommerce_edit_composite_in_order' ) ) {
			return $configuration;
		}

		global $Product_Addon_Cart;

		if ( empty( $Product_Addon_Cart ) ) {
			return $configuration;
		}

		foreach ( $configuration as $component_id => $item_configuration ) {
			self::$addons_prefix = $component_id;

			// Pretend we're in some sort of cart so that we can reuse as much code as possible.
			$values = $Product_Addon_Cart->add_cart_item_data( array(), $item_configuration['product_id'] );

			self::$addons_prefix = '';

			if ( empty( $values['addons'] ) ) {
				continue;
			}

			$configuration[ $component_id ]['addons'] = $values['addons'];
		}

		return $configuration;
	}

	/**
	 * Store addons for parent composite item, if it has any.
	 *
	 * We need to do this in the `woocommerce_before_editing_composite_in_order` hook, because
	 * the `woocommerce_editing_composite_in_order` hook is only run if the component configuration changes, which doesn't take
	 * parent composite item addons into account.
	 *
	 * @param WC_Order_Item_Product $item  The container item.
	 * @param WC_Order              $order The order
	 *
	 * @return void
	 */
	public static function store_composite_item_addons( $item, $order ) {
		if ( ! doing_action( 'wp_ajax_woocommerce_edit_composite_in_order' ) ) {
			return;
		}

		$product        = $item->get_product();
		$product_addons = WC_Product_Addons_Helper::get_product_addons( $item['product_id'] );

		if ( empty( $product_addons ) ) {
			return;
		}

		if ( ! WC_Product_Addons_Admin_Ajax::store_product_addons( $item, $product, $order ) ) {
			throw new Exception( __( 'Failed to store product addons.', 'woocommerce-composite-products' ) );
		}
	}

	/**
	 * Copy addons from old item to new item.
	 *
	 * @param WC_Order_Item_Product $new_item The new item.
	 * @param WC_Order_Item_Product $old_item The old item.
	 * @param WC_Order              $order    The order.
	 *
	 * @return void
	 */
	public static function copy_composite_item_addons( $new_item, $old_item, $order ) {
		if ( ! doing_action( 'wp_ajax_woocommerce_edit_composite_in_order' ) ) {
			return;
		}

		global $Product_Addon_Cart;

		if ( empty( $Product_Addon_Cart ) ) {
			return;
		}

		// New item already has addons, so we're not gonna do anything.
		if ( $new_item->meta_exists( '_pao_ids' ) ) {
			return;
		}

		$pao_ids   = $old_item->get_meta( '_pao_ids', true );
		$pao_total = $old_item->get_meta( '_pao_total', true );
		if ( empty( $pao_ids ) ) {
			return;
		}

		$product = $new_item->get_product();
		foreach ( $pao_ids as $pao_id ) {
			$new_item->add_meta_data( $pao_id['key'], $pao_id['value'] );
		}
		$new_item->add_meta_data( '_pao_ids', $pao_ids );
		$new_item->add_meta_data( '_pao_total', $pao_total );
		$new_item['subtotal'] = wc_get_price_excluding_tax(
			$product,
			array(
				'price' => $product->get_price(),
				'qty'   => $new_item['qty'],
			)
		) + $pao_total;
		$new_item['total']    = $new_item['subtotal'];

		$new_item->save_meta_data();
		$new_item->save();
		// Refresh the internal cache for the order.
		$order->add_item( $new_item );
	}

	/**
	 * Store add to order Addons.
	 *
	 * @param  WC_Order_Item        $composited_order_item
	 * @param  WC_Order             $order
	 * @param  WC_Product_Composite $composite
	 * @param  int                  $quantity
	 * @param  array                $args
	 */
	public static function store_component_item_addons( $composited_order_item, $order, $composite, $quantity, $args ) {
		if ( ! doing_action( 'wp_ajax_woocommerce_edit_composite_in_order' ) ) {
			return;
		}
		global $Product_Addon_Cart;

		if ( empty( $Product_Addon_Cart ) ) {
			return;
		}

		$component_order_items = wc_cp_get_composited_order_items( $composited_order_item, $order );

		/** @var WC_Order_Item_Product $component_order_item */
		foreach ( $component_order_items as $component_order_item ) {
			$component_item_id = $component_order_item['composite_item'];
			$component_product = $component_order_item->get_product();

			$component_option = $composite->get_component_option( $component_item_id, $component_order_item['product_id'] );

			if ( empty( $args['configuration'][ $component_item_id ]['addons'] ) ) {
				continue;
			}

			$values = $args['configuration'][ $component_item_id ];

			$item_data = array(
				'composite_item'   => $component_item_id,
				'product_id'       => $component_order_item['product_id'],
				'variation_id'     => $component_order_item['variation_id'],
				'quantity'         => $component_order_item['qty'],
				'data'             => $component_product,
				'addons'           => $values['addons'],
				'composite_parent' => $component_order_item['composite_parent'],
				'composite_data'   => $args['configuration'],
				'stamp'            => $component_order_item['stamp'],
			);
			$item_data = self::preprocess_composited_cart_item_addon_data(
				$item_data,
				$composite,
				array(
					$component_order_item['composite_parent'] => $composited_order_item,
				)
			);

			// If the component is discounted, the add-ons' price offset is handled by preprocess_composited_cart_item_addon_data and
			// WC_CP_Products::filter_get_price to produce the correct price in the cart.
			// If it's not discounted, PAO code is used to correct the price by updating
			// the component product object via get_cart_item_from_session method.
			if ( $component_option->is_priced_individually() && ! isset( $item_data['addons_flat_fees_sum'] ) ) {
				$item_data = $Product_Addon_Cart->get_cart_item_from_session( $item_data, $values );
			}

			WC_CP()->product_data->set( $component_product, 'composited_cart_item', $component_option );
			$Product_Addon_Cart->order_line_item( $component_order_item, null, $item_data );

			$component_order_item['subtotal'] = wc_get_price_excluding_tax(
				$component_product,
				array(
					'price' => $component_product->get_price(),
					'qty'   => $component_order_item['qty'],
				)
			);
			$component_order_item['total']    = $component_order_item['subtotal'];

			$component_order_item->save_meta_data();
			$component_order_item->save();
			// Refresh the internal cache for the order.
			$order->add_item( $component_order_item );
		}
	}

	/**
	 * Validate add to order Addons.
	 *
	 * @param  WC_CP_Component $component
	 * @param  array           $component_validation_data
	 * @param  int             $composite_quantity
	 * @param  array           $configuration
	 * @param  string          $context
	 *
	 * @return void
	 */
	public static function admin_validate_composite_component_addons( $component, $component_validation_data, $composite_quantity, $configuration, $context ) {
		if ( ! doing_action( 'wp_ajax_woocommerce_edit_composite_in_order' ) ) {
			return;
		}

		global $Product_Addon_Cart;

		if ( empty( $Product_Addon_Cart ) ) {
			return;
		}

		$add                     = true;
		$component_id            = $component->get_id();
		$disable_addons          = $component->disable_addons();
		$component_configuration = $configuration[ $component_id ];

		self::$addons_prefix = $component_id;

		add_filter( 'woocommerce_add_error', array( __CLASS__, 'component_addons_error_message_context' ) );

		self::$current_component = $component;

		if ( false === $disable_addons && false === $Product_Addon_Cart->validate_add_cart_item( true, $component_configuration['product_id'], $component_configuration['quantity'] ) ) {
			$add = false;
		}

		self::$current_component = false;

		remove_filter( 'woocommerce_add_error', array( __CLASS__, 'component_addons_error_message_context' ) );

		self::$addons_prefix = '';

		if ( $add ) {
			return;
		}

		// Get first error message.
		$errors = wc_get_notices( 'error' );
		$error  = reset( $errors )['notice'] ?? '';
		wc_clear_notices();

		throw new Exception( wp_kses( $error, false ) );
	}

	/**
	 * Maybe hide the edit button for addons in the order editing screen.
	 *
	 * @param  boolean $display
	 * @param  array   $item
	 *
	 * @return boolean
	 */
	public static function maybe_hide_edit_button( $display, $item ) {
		return $display && ! wc_cp_is_composited_order_item( $item );
	}

	/**
	 * The composite should be editable if any items have addons.
	 *
	 * @param  boolean              $editable   Whether the composite is editable.
	 * @param  WC_Product_Composite $composite  The composite product.
	 * @param  array                $item       The cart item.
	 *
	 * @return boolean
	 */
	public static function addon_composite_editable_in_cart( $editable, $composite, $item ) {
		// phpcs:ignore
		/* @var WC_CP_Component[] $components */
		$components = $composite->get_components();
		foreach ( $components as $component ) {
			$component_options = $component->get_options();
			foreach ( $component_options as $component_option ) {
				if ( self::has_addons( $component_option ) ) {
					return true;
				}
			}
		}

		return $editable;
	}

	/**
	 * Retrieves the add-on data from a cart item for a composite product.
	 *
	 * @since 10.2.0
	 *
	 * @param  array      $parsed_addons  Parsed add-ons.
	 * @param  WC_Product $product        The product.
	 * @param  array      $cart_item      The cart item.
	 *
	 * @return array
	 */
	public static function parse_composite_addons( array $parsed_addons, WC_Product $product, array $cart_item ): array {
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		global $Product_Addon_Display;

		if ( ! isset( $cart_item['composite_data'] ) || ! $product->is_type( 'composite' ) ) {
			return $parsed_addons;
		}

		foreach ( $cart_item['composite_data'] as $component_id => $composite_data ) {
			if ( ! isset( $composite_data['addons'] ) ) {
				continue;
			}

			self::$addons_prefix = $component_id;

			$product_addons = WC_Product_Addons_Helper::get_product_addons( $composite_data['product_id'] );

			if ( empty( $product_addons ) ) {
				continue;
			}

			$parsed_addons += $Product_Addon_Display->parse_cart_addons( $product_addons, $composite_data['addons'] );
		}

		self::$addons_prefix = '';

		return $parsed_addons;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	}

	/**
	 * Filter to force add-ons to include the correct query parameters in cart item link if any products in the bundle have add-ons.
	 *
	 * @since 10.2.0
	 *
	 * @param  bool  $allow_cart_key  Whether to allow the cart key.
	 * @param  array $cart_item       The cart item.
	 *
	 * @return bool
	 */
	public static function add_cart_key_to_permalink( bool $allow_cart_key, array $cart_item ): bool {
		$product = $cart_item['data'];

		if ( ! $product || ! $product->is_type( 'composite' ) ) {
			return $allow_cart_key;
		}

		return self::addon_composite_editable_in_cart( $allow_cart_key, $product, $cart_item );
	}

	/**
	 * Adds the PAO key to edit composite links in the cart.
	 *
	 * @param array                       $args      The permalink arguments.
	 * @param array|WC_Order_Item_Product $cart_item The cart item.
	 *
	 * @return array
	 */
	public static function maybe_add_cart_key_to_permalink_args( array $args, $cart_item ): array {
		$product = $cart_item['data'];

		if ( ! $product || ! $product->is_type( 'composite' ) ) {
			return $args;
		}

		$product_addons = WC_Product_Addons_Helper::get_product_addons( $cart_item['product_id'] );

		if ( ! empty( $product_addons ) || self::addon_composite_editable_in_cart( false, $product, $cart_item ) ) {
			$args['pao_key']  = $cart_item['key'];
			$args['pao_edit'] = 1;
		}

		return $args;
	}
	// phpcs:enable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
}

WC_CP_Addons_Compatibility::init();
