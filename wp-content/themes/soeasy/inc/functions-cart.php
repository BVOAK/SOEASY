<?php
/**
 * Fonctions complémentaires pour le panier SoEasy
 * À ajouter dans functions.php ou dans configurateur/functions-configurateur.php
 */

/**
 * AJAX - Supprimer tous les produits d'une adresse spécifique
 */
function soeasy_remove_address_from_cart() {
    
    // Vérification nonce
    if ( ! wp_verify_nonce( $_POST['security'], 'soeasy_cart_action' ) ) {
        wp_send_json_error( array( 'message' => 'Sécurité non vérifiée' ) );
        return;
    }
    
    $address_to_remove = sanitize_text_field( $_POST['address'] );
    
    if ( empty( $address_to_remove ) ) {
        wp_send_json_error( array( 'message' => 'Adresse non spécifiée' ) );
        return;
    }
    
    $removed_items = 0;
    $cart = WC()->cart;
    
    // Parcourir le panier et identifier les items à supprimer
    $items_to_remove = array();
    
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        if ( isset( $cart_item['soeasy_adresse'] ) && $cart_item['soeasy_adresse'] === $address_to_remove ) {
            $items_to_remove[] = $cart_item_key;
        }
    }
    
    // Supprimer les items identifiés
    foreach ( $items_to_remove as $cart_item_key ) {
        if ( $cart->remove_cart_item( $cart_item_key ) ) {
            $removed_items++;
        }
    }
    
    if ( $removed_items > 0 ) {
        wp_send_json_success( array( 
            'message' => sprintf( '%d produit(s) supprimé(s) pour l\'adresse "%s"', $removed_items, $address_to_remove ),
            'removed_items' => $removed_items 
        ) );
    } else {
        wp_send_json_error( array( 'message' => 'Aucun produit trouvé pour cette adresse' ) );
    }
}
add_action( 'wp_ajax_soeasy_remove_address_from_cart', 'soeasy_remove_address_from_cart' );
add_action( 'wp_ajax_nopriv_soeasy_remove_address_from_cart', 'soeasy_remove_address_from_cart' );

/**
 * AJAX - Modifier la quantité d'un produit spécifique
 */
function soeasy_update_cart_item_quantity() {
    
    if ( ! wp_verify_nonce( $_POST['security'], 'soeasy_cart_action' ) ) {
        wp_send_json_error( array( 'message' => 'Sécurité non vérifiée' ) );
        return;
    }
    
    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
    $quantity = intval( $_POST['quantity'] );
    
    if ( empty( $cart_item_key ) ) {
        wp_send_json_error( array( 'message' => 'Clé produit manquante' ) );
        return;
    }
    
    $cart = WC()->cart;
    
    if ( $quantity <= 0 ) {
        // Supprimer l'item si quantité = 0
        $success = $cart->remove_cart_item( $cart_item_key );
        $message = $success ? 'Produit supprimé du panier' : 'Erreur lors de la suppression';
    } else {
        // Mettre à jour la quantité
        $success = $cart->set_quantity( $cart_item_key, $quantity );
        $message = $success ? 'Quantité mise à jour' : 'Erreur lors de la mise à jour';
    }
    
    if ( $success ) {
        wp_send_json_success( array( 
            'message' => $message,
            'cart_count' => $cart->get_cart_contents_count(),
            'cart_total' => wc_price( $cart->get_cart_total() )
        ) );
    } else {
        wp_send_json_error( array( 'message' => $message ) );
    }
}
add_action( 'wp_ajax_soeasy_update_cart_item_quantity', 'soeasy_update_cart_item_quantity' );
add_action( 'wp_ajax_nopriv_soeasy_update_cart_item_quantity', 'soeasy_update_cart_item_quantity' );

/**
 * Hook pour redirection après ajout au panier depuis configurateur
 */
function soeasy_redirect_to_cart_after_configurateur( $url ) {
    
    // Vérifier si on vient du configurateur
    $referer = wp_get_referer();
    if ( $referer && strpos( $referer, 'configurateur' ) !== false ) {
        return wc_get_cart_url();
    }
    
    return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'soeasy_redirect_to_cart_after_configurateur' );

/**
 * Fonction helper - Calculer total par adresse
 */
function soeasy_get_cart_total_by_address( $target_address ) {
    
    $total = 0;
    
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( isset( $cart_item['soeasy_adresse'] ) && $cart_item['soeasy_adresse'] === $target_address ) {
            $total += $cart_item['line_total'];
        }
    }
    
    return $total;
}

/**
 * Fonction helper - Compter items par adresse
 */
function soeasy_get_cart_count_by_address( $target_address ) {
    
    $count = 0;
    
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( isset( $cart_item['soeasy_adresse'] ) && $cart_item['soeasy_adresse'] === $target_address ) {
            $count += $cart_item['quantity'];
        }
    }
    
    return $count;
}

/**
 * Fonction helper - Obtenir la liste des adresses dans le panier
 */
function soeasy_get_cart_addresses() {
    
    $addresses = array();
    
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( isset( $cart_item['soeasy_adresse'] ) && !empty( $cart_item['soeasy_adresse'] ) ) {
            $address = $cart_item['soeasy_adresse'];
            if ( !in_array( $address, $addresses ) ) {
                $addresses[] = $address;
            }
        }
    }
    
    return $addresses;
}

/**
 * Widget panier adapté pour header
 */
function soeasy_cart_widget_content() {
    
    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_total = WC()->cart->get_cart_total();
    $addresses = soeasy_get_cart_addresses();
    
    ob_start();
    ?>
    
    <div class="soeasy-cart-widget">
        <div class="cart-header d-flex justify-content-between align-items-center">
            <span class="cart-count">
                <i class="fas fa-shopping-cart"></i>
                <?php echo $cart_count; ?> produit(s)
            </span>
            <span class="cart-total"><?php echo $cart_total; ?></span>
        </div>
        
        <?php if ( !empty( $addresses ) ) : ?>
            <div class="cart-addresses mt-2">
                <small class="text-muted">
                    <?php echo count( $addresses ); ?> adresse(s) configurée(s)
                </small>
                <ul class="list-unstyled small">
                    <?php foreach ( array_slice( $addresses, 0, 3 ) as $address ) : ?>
                        <li><i class="fas fa-map-marker-alt me-1"></i><?php echo esc_html( $address ); ?></li>
                    <?php endforeach; ?>
                    <?php if ( count( $addresses ) > 3 ) : ?>
                        <li><em>... et <?php echo count( $addresses ) - 3; ?> autre(s)</em></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="cart-actions mt-3">
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="btn btn-outline-primary btn-sm w-100 mb-2">
                Voir le panier
            </a>
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn-success btn-sm w-100">
                Commander
            </a>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Shortcode pour afficher le widget panier
 */
function soeasy_cart_widget_shortcode( $atts ) {
    
    if ( ! function_exists( 'WC' ) || WC()->cart->is_empty() ) {
        return '<div class="soeasy-cart-widget empty">Panier vide</div>';
    }
    
    return soeasy_cart_widget_content();
}
add_shortcode( 'soeasy_cart_widget', 'soeasy_cart_widget_shortcode' );

/**
 * Hook pour enqueue CSS/JS du panier si nécessaire
 */
function soeasy_enqueue_cart_assets() {
    
    if ( is_cart() || is_checkout() ) {
        
        wp_enqueue_script( 
            'soeasy-cart', 
            get_template_directory_uri() . '/assets/js/cart.js', 
            array( 'jquery' ), 
            filemtime( get_template_directory() . '/assets/js/cart.js' ), 
            true 
        );
        
        wp_localize_script( 'soeasy-cart', 'soeasyCartVars', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'security' => wp_create_nonce( 'soeasy_cart_action' ),
            'messages' => array(
                'confirm_remove_address' => 'Êtes-vous sûr de vouloir supprimer tous les produits pour cette adresse ?',
                'error_generic' => 'Une erreur est survenue. Veuillez réessayer.',
                'success_updated' => 'Panier mis à jour avec succès'
            )
        ) );
        
        wp_enqueue_style( 
            'soeasy-cart', 
            get_template_directory_uri() . '/assets/css/cart.css',
            array(),
            filemtime( get_template_directory() . '/assets/css/cart.css' )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'soeasy_enqueue_cart_assets' );

/**
 * Notice personnalisée pour panier configurateur
 */
function soeasy_cart_configurateur_notice() {
    
    if ( is_cart() && !empty( soeasy_get_cart_addresses() ) ) {
        
        wc_print_notice( 
            'Votre configuration télécom est prête ! Vous pouvez modifier chaque adresse ou procéder au paiement.', 
            'notice' 
        );
    }
}
add_action( 'woocommerce_before_cart', 'soeasy_cart_configurateur_notice' );

?>