<?php
namespace UiCore\WooCommerce;

use Uicore\Helper;

defined('ABSPATH') || exit();


/**
 * Woocommerce single product page Gallery Component.
 *
 * @author Lucas Marini <lucas@uicore.co
 * @since 6.0.0
 */
class ProductGallery
{

    public function __construct() {}

    /**
     * Returns the Gallery component markup or replace the Woo template for it.
     *
     * @param string $gallery_type The gallery type to be used.
     * @param bool $hook If true, replaces the Woocommerce variation template by the swatch component instead of returning the html markup. Default is false.
     * @return void/string The gallery HTML markup or void if hooking.
     */
    public static function init($gallery_type, $hook = true) {

        if($hook){
            // We only enqueue gallery assets if we're hooking. Otherwhise this is done by the widgets
            \add_action('wp_enqueue_scripts', function() use ($gallery_type) {
                self::enqueue_assets($gallery_type);
            });

        // Get theme option style if gallery is set to 'theme' and is a widget request
        } else if ( $gallery_type === 'theme' ){
            $gallery_type = Helper::get_option('woos_product_gallery');
        }

        // Return the woo gallery template
        if( empty($gallery_type) || $gallery_type === 'left_thumbs' ) {
            // If we're hooking, we don't need to do anything
            if($hook) {
                return;
            }
            if( $gallery_type === 'left_thumbs' ){
                // add the left thumbnail specific class via woocommerce_single_product_image_gallery_classes filter
                add_filter('woocommerce_single_product_image_gallery_classes', function($classes) {
                    $classes[] = 'uicore-gallery-left-thumbs';
                    return $classes;
                });
            }

            return \wc_get_template( 'single-product/product-image.php' );
        }

        // If we're hooking, add the custom gallery through hooks replacement
        if( $hook & in_array($gallery_type, ['grid_column', 'grid_column_2']) ) {
            // Disables zoom
            add_action('woocommerce_single_product_zoom_enabled', '__return_false' );
            // Remove the default gallery
            remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
            // Add the custom gallery
            add_action('woocommerce_before_single_product_summary', function() {
                self::grid_column_gallery();
            }, 20);

            return;
        }

        // Return the custom gallery markup otherwhise
        return self::grid_column_gallery($gallery_type);
    }

    /**
     * Enqueue the product gallery assets.
     *
     * @param string $gallery_style The gallery style to be used.
     *
     * @return void
     */
    public static function enqueue_assets($gallery_style)
    {
        // Woo default
        if( empty($gallery_style) ){
            return;

        // Left thumbnails
        } else if( $gallery_style === 'left_thumbs' ){
            wp_enqueue_style('uicore-product-gallery-thumbs');

        // Columns
        } else {
            wp_enqueue_script('uicore-product-gallery-columns');
            wp_enqueue_style('uicore-product-gallery-columns');
            // Disables Elementor lightbox
            \wp_add_inline_script('elementor-frontend', 'document.addEventListener("DOMContentLoaded", function() {
                    elementorFrontend.getKitSettings().global_image_lightbox = false;
            });
            ','after');
        }
    }

    /**
     * Dequeue the product gallery assets enqueue by the theme. Usefull if the page is built with a theme builder and you want to clean any assets toprevents conflicts betweeen widget assets.
     */
    public static function dequeue_theme_assets(){

        $style = Helper::get_option('woos_product_gallery');
        // Default woo style
        if( empty($style) ){
            return;

        // Left thumbnails
        } else if( $style === 'left_thumbs'){
            wp_dequeue_style('uicore-product-gallery-thumbs');

        // Column styles
        } else {
            wp_enqueue_script('uicore-product-gallery-columns');
            wp_dequeue_style('uicore-product-gallery-columns');
        }
    }

    /**
     * Render the grid column gallery. This code is based on the `product-image.php` template from Woocommerce and should keep up with future updates.
     *
     * @param string $gallery_style The gallery style to be used. Default is false.
     * @return string The gallery HTML markup.
     */
    public static function grid_column_gallery($gallery_style = false) {
        global $product;

        $columns = '';
        $post_thumbnail_id = $product->get_image_id();
        $wrapper_classes   = [
            'woocommerce-product-gallery',
            'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
            'images',
        ];
        $attachment_ids = $product->get_gallery_image_ids();

        // Since the two columns gallery is built with an css variable, from theme options, at woo-css,
        // in widget case we overwrite this variable by adding it at a lower level (theme is added on div.product)
        if($gallery_style !== false){
            $columns = $gallery_style === 'grid_column_2' ? '--uicore-gallery-columns: 2;' : '--uicore-gallery-columns: 1;';
        }

        ?>
        <div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>"
             style="opacity: 0; transition: opacity .25s ease-in-out; <?php echo esc_attr($columns);?>">
            <div class="woocommerce-product-gallery__grid-wrapper uicore-grid-gallery">

                <?php if($post_thumbnail_id) : ?>
                    <div class="woocommerce-product-gallery__image-wrap main-image">
                        <?php echo wc_get_gallery_image_html( $post_thumbnail_id, true ); ?>
                    </div>
                <?php else :
                    echo sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
                endif; ?>

                <?php
                    if($attachment_ids) :
                        foreach ( $attachment_ids as $attachment_id ) :
                ?>
                            <div class="woocommerce-product-gallery__image-wrap">
                                <?php echo wc_get_gallery_image_html( $attachment_id ); ?>
                            </div>
                <?php
                        endforeach;
                    endif;
                ?>

                <?php if ( !$post_thumbnail_id && empty($attachment_ids) ) : ?>
                    <div class="woocommerce-product-gallery__grid-item woocommerce-product-gallery__image--placeholder">
                        <?php sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) ); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <?php
    }
}
new ProductGallery();
