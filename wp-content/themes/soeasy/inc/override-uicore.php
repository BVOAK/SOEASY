<?php
/**
 * Override UICore pour pages SoEasy spécifiques
 * À ajouter dans wp-content/themes/soeasy/functions.php
 */

/**
 * Désactiver le wrapper UICore pour certaines pages WooCommerce
 */
function soeasy_override_uicore_wrapper() {
    
    // Pages où on veut désactiver UICore
    if ( is_cart() || is_checkout() || is_page_template('page-configurateur.php') ) {
        
        // Supprimer les actions UICore
        remove_action('woocommerce_before_main_content', 'uicore_wrapper_before');
        remove_action('woocommerce_after_main_content', 'uicore_wrapper_after');
        
        // Ajouter nos propres wrappers minimalistes
        add_action('woocommerce_before_main_content', 'soeasy_wrapper_before');
        add_action('woocommerce_after_main_content', 'soeasy_wrapper_after');
    }
}
add_action('init', 'soeasy_override_uicore_wrapper', 15);

/**
 * Wrapper custom pour SoEasy (minimal)
 */
function soeasy_wrapper_before() {
    echo '<div id="primary" class="content-area soeasy-content">';
    echo '<main id="main" class="site-main">';
}

function soeasy_wrapper_after() {
    echo '</main>';
    echo '</div>';
}

/**
 * Désactiver les styles UICore sur nos pages custom
 */
function soeasy_dequeue_uicore_styles() {
    
    if ( is_cart() || is_checkout() || is_page_template('page-configurateur.php') ) {
        
        // Désactiver certains styles UICore qui peuvent interférer
        wp_dequeue_style('uicore-woocommerce');
        wp_dequeue_style('uicore-shop');
        
        // Optionnel : garder seulement les styles de base
        // wp_dequeue_style('uicore-pro'); // Décommenter si problèmes de style
    }
}
add_action('wp_enqueue_scripts', 'soeasy_dequeue_uicore_styles', 20);

/**
 * Forcer l'utilisation de nos templates WooCommerce
 */
function soeasy_force_woocommerce_templates($template, $template_name, $template_path) {
    
    // Forcer notre template panier
    if ($template_name === 'cart/cart.php' && is_cart()) {
        $custom_template = get_template_directory() . '/woocommerce/cart/cart.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    // Forcer notre template checkout si on en crée un
    if ($template_name === 'checkout/form-checkout.php' && is_checkout()) {
        $custom_template = get_template_directory() . '/woocommerce/checkout/form-checkout.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    return $template;
}
add_filter('woocommerce_locate_template', 'soeasy_force_woocommerce_templates', 20, 3);

/**
 * Ajouter des classes body pour nos pages custom
 */
function soeasy_custom_body_classes($classes) {
    
    if (is_cart()) {
        $classes[] = 'soeasy-cart-page';
        $classes[] = 'no-uicore-wrapper';
    }
    
    if (is_checkout()) {
        $classes[] = 'soeasy-checkout-page';
        $classes[] = 'no-uicore-wrapper';
    }
    
    if (is_page_template('page-configurateur.php')) {
        $classes[] = 'soeasy-configurateur-page';
        $classes[] = 'no-uicore-wrapper';
    }
    
    return $classes;
}
add_filter('body_class', 'soeasy_custom_body_classes');

/**
 * CSS d'urgence pour neutraliser UICore si besoin
 */
function soeasy_emergency_css() {
    
    if ( is_cart() || is_checkout() || is_page_template('page-configurateur.php') ) {
        ?>
        <style id="soeasy-emergency-css">
            /* Neutraliser les styles UICore qui interfèrent */
            .soeasy-content .uicore-container,
            .soeasy-content .uicore-row,
            .soeasy-content .uicore-col-lg-3 {
                all: unset !important;
                display: block !important;
            }
            
            /* S'assurer que notre contenu prend toute la largeur */
            .soeasy-cart-wrapper,
            .configurateur-wrapper {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Masquer les sidebars UICore sur nos pages */
            .no-uicore-wrapper .left-widget-area,
            .no-uicore-wrapper .right-widget-area {
                display: none !important;
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'soeasy_emergency_css', 999);

?>