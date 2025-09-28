<?php

/**
 * uicore functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package uicore-theme
 */
defined('ABSPATH') || exit;

//Global Constants
define('UICORE_THEME_VERSION', '2.0.4');
define('UICORE_THEME_NAME', 'UiCore Pro');
define('UICORE_FRAMEWORK_VERSION', '6.0.4');

$uicore_includes = array(
    '/setup.php',
    '/default.php',
    '/template-tags.php',
    '/plugin-activation.php'
);

foreach ($uicore_includes as $file) {
    require_once get_template_directory() . '/inc' . $file;
}

//Required
if (!isset($content_width)) {
    $content_width = 1000;
}
if (is_singular() && !class_exists('\UiCore\Core')) {
    wp_enqueue_script("comment-reply");
}


// Ajouter FontAwesome
function soeasy_enqueue_fontawesome() {
    wp_enqueue_style(
        'fontawesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css',
        array(),
        '7.0.0'
    );
}
add_action('wp_enqueue_scripts', 'soeasy_enqueue_fontawesome');


//disable element pack self update
function uicore_disable_plugin_updates($value)
{

    $pluginsToDisable = [
        'bdthemes-element-pack/bdthemes-element-pack.php',
        'metform-pro/metform-pro.php'
    ];

    if (isset($value) && is_object($value)) {
        foreach ($pluginsToDisable as $plugin) {
            if (isset($value->response[$plugin])) {
                unset($value->response[$plugin]);
            }
        }
    }
    return $value;
}
add_filter('site_transient_update_plugins', 'uicore_disable_plugin_updates');

function custom_acf_admin_script()
{
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            // Vérifier si les éléments existent avant d'ajouter les écouteurs
            var serviceInternet = document.querySelector('[name="acf[services_selectionnes][internet]"]');
            var engagementField = document.querySelector('[name="acf[duree_engagement]"]');
            var financementField = document.querySelector('[name="acf[mode_financement_materiel]"]');

            if (!serviceInternet || !engagementField || !financementField) {
                return;
            }

            // Fonction pour mettre à jour les options du champ engagement
            function updateEngagementOptions() {
                if (serviceInternet.checked) {
                    // Désactiver les options interdites si Internet est sélectionné
                    Array.from(engagementField.options).forEach(function (option) {
                        if (option.value === "Sans engagement" || option.value === "24 mois") {
                            option.disabled = true;
                        }
                    });

                    // Forcer automatiquement à 36 mois si Internet est sélectionné
                    if (engagementField.value === "Sans engagement" || engagementField.value === "24 mois") {
                        engagementField.value = "36 mois";
                    }
                } else {
                    // Réactiver toutes les options si Internet n'est PAS sélectionné
                    Array.from(engagementField.options).forEach(function (option) {
                        option.disabled = false;
                    });
                }
            }

            // Fonction pour mettre à jour les options du champ mode de financement
            function updateFinancementOptions() {
                if (engagementField.value === "Sans engagement") {
                    // Désactiver l'option "Leasing"
                    Array.from(financementField.options).forEach(function (option) {
                        if (option.value === "Leasing") {
                            option.disabled = true;
                        }
                    });

                    // Forcer l'utilisateur à choisir "Achat comptant"
                    financementField.value = "Achat comptant";
                } else {
                    // Réactiver l'option "Leasing" si engagement est >= 24 mois
                    Array.from(financementField.options).forEach(function (option) {
                        option.disabled = false;
                    });
                }
            }

            // Ajouter des écouteurs d'événements pour détecter les changements
            serviceInternet.addEventListener('change', updateEngagementOptions);
            engagementField.addEventListener('change', updateFinancementOptions);

            // Exécuter au chargement de la page
            updateEngagementOptions();
            updateFinancementOptions();
        });
    </script>
    <?php
}

add_action('admin_footer', 'custom_acf_admin_script');

require_once get_template_directory() . '/configurateur/functions-configurateur.php';
require_once get_template_directory() . '/inc/functions-cart.php';
require_once get_template_directory() . '/inc/override-uicore.php';

function soeasy_enqueue_configurateur_assets_conditionnel() {
    if (is_page_template('page-configurateur.php')) {
        
        wp_enqueue_script('jquery');

        // Bootstrap
        wp_enqueue_style(
            'bootstrap-css',
            get_template_directory_uri() . '/assets/bootstrap/css/bootstrap.min.css',
            array(),
            '5.3.0'
        );
        wp_enqueue_script(
            'bootstrap-js',
            get_template_directory_uri() . '/assets/bootstrap/js/bootstrap.bundle.min.js',
            array('jquery'),
            '5.3.0',
            true
        );

        wp_enqueue_style(
            'configurateur',
            get_template_directory_uri() . '/assets/css/configurateur.css',
        );

        // Scripts JS configurateur
        wp_enqueue_script(
            'soeasy-configurateur-fonctions',
            get_template_directory_uri() . '/assets/js/configurateur-fonctions.js',
            array('jquery'),
            filemtime(get_template_directory() . '/assets/js/configurateur-fonctions.js'),
            true
        );

        wp_enqueue_script(
            'soeasy-configurateur',
            get_template_directory_uri() . '/assets/js/configurateur.js',
            array('jquery', 'soeasy-configurateur-fonctions'),
            filemtime(get_template_directory() . '/assets/js/configurateur.js'),
            true
        );

        wp_enqueue_script(
            'google-maps-api',
            'https://maps.googleapis.com/maps/api/js?key=AIzaSyBeIvkJPtLGSviPdBoluEUR0SI1M7eeK00&libraries=places',
            [],
            null,
            true
        );

        wp_localize_script('soeasy-configurateur', 'soeasyVars', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'themeUrl' => get_template_directory_uri(),
            'security' => wp_create_nonce('soeasy_nonce'),
            'nonce_config' => wp_create_nonce('soeasy_config_action'),
            'nonce_cart' => wp_create_nonce('soeasy_cart_action'),
            'nonce_address' => wp_create_nonce('soeasy_address_action')
        ));

        add_action('init', function () {
            if (class_exists('WooCommerce') && is_checkout() === false) {
                WC()->session; // Initialise la session WC
            }
        });
    }
}
add_action('wp_enqueue_scripts', 'soeasy_enqueue_configurateur_assets_conditionnel');


/**
 * Enqueue assets pour pages panier/checkout
 */
function soeasy_enqueue_cart_assets() {
    
    if ( is_cart() || is_checkout() ) {
        
        // CSS panier
        wp_enqueue_style( 
            'soeasy-cart', 
            get_template_directory_uri() . '/assets/css/cart.css',
            array( 'woocommerce-general' ),
            filemtime( get_template_directory() . '/assets/css/cart.css' )
        );
        
        // JS panier
        wp_enqueue_script( 
            'soeasy-cart', 
            get_template_directory_uri() . '/assets/js/cart.js', 
            array( 'jquery', 'woocommerce' ), 
            filemtime( get_template_directory() . '/assets/js/cart.js' ), 
            true 
        );
        
        // Variables JS
        wp_localize_script( 'soeasy-cart', 'soeasyCartVars', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'security' => wp_create_nonce( 'soeasy_cart_action' ),
            'configurateur_url' => get_permalink( get_page_by_path( 'configurateur' ) ),
            'messages' => array(
                'confirm_remove_address' => 'Êtes-vous sûr de vouloir supprimer tous les produits pour cette adresse ?',
                'error_generic' => 'Une erreur est survenue. Veuillez réessayer.',
                'success_updated' => 'Panier mis à jour avec succès'
            )
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'soeasy_enqueue_cart_assets' );
