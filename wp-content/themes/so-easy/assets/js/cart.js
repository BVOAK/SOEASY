/**
 * JavaScript pour le panier SoEasy adapté multi-adresses
 * Fichier: wp-content/themes/soeasy/assets/js/cart.js
 */

jQuery(document).ready(function($) {
    
    'use strict';
    
    // === VARIABLES GLOBALES ===
    const $body = $('body');
    const vars = window.soeasyCartVars || {};
    
    // === FONCTIONS UTILITAIRES ===
    
    /**
     * Afficher un loading sur un élément
     */
    function showLoading($element) {
        $element.addClass('cart-loading');
    }
    
    /**
     * Masquer le loading
     */
    function hideLoading($element) {
        $element.removeClass('cart-loading');
    }
    
    /**
     * Afficher une notification
     */
    function showNotification(message, type = 'success') {
        const $notification = $(`
            <div class="alert alert-${type} alert-dismissible fade show cart-notification" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('.soeasy-cart-wrapper .container').prepend($notification);
        
        // Auto-hide après 5 secondes
        setTimeout(() => {
            $notification.fadeOut(() => $notification.remove());
        }, 5000);
    }
    
    /**
     * Mettre à jour les totaux affichés
     */
    function updateCartTotals() {
        // Déclencher le recalcul WooCommerce
        $body.trigger('update_checkout');
        
        // Optionnel: mettre à jour via AJAX
        $.post(vars.ajaxurl, {
            action: 'soeasy_get_cart_totals',
            security: vars.security
        }, function(response) {
            if (response.success) {
                // Mettre à jour les éléments affichés
                $('.cart-summary .woocommerce-Price-amount').text(response.data.total);
                $('.soeasy-cart-widget .cart-total').text(response.data.total);
                $('.soeasy-cart-widget .cart-count').html(`<i class="fas fa-shopping-cart"></i> ${response.data.count} produit(s)`);
            }
        });
    }
    
    // === GESTION SUPPRESSION ADRESSE ===
    
    /**
     * Supprimer tous les produits d'une adresse
     */
    window.supprimerAdresse = function(address) {
        
        if (!confirm(vars.messages.confirm_remove_address || 'Êtes-vous sûr de vouloir supprimer tous les produits pour cette adresse ?')) {
            return;
        }
        
        const $addressSection = $(`.address-section:contains("${address}")`).first();
        showLoading($addressSection);
        
        $.post(vars.ajaxurl, {
            action: 'soeasy_remove_address_from_cart',
            address: address,
            security: vars.security
        })
        .done(function(response) {
            if (response.success) {
                showNotification(response.data.message, 'success');
                
                // Animation de suppression
                $addressSection.slideUp(400, function() {
                    $(this).remove();
                    
                    // Vérifier si le panier est vide
                    if ($('.address-section').length === 0) {
                        location.reload(); // Recharger pour afficher le panier vide
                    } else {
                        updateCartTotals();
                    }
                });
                
            } else {
                hideLoading($addressSection);
                showNotification(response.data.message || vars.messages.error_generic, 'danger');
            }
        })
        .fail(function() {
            hideLoading($addressSection);
            showNotification(vars.messages.error_generic || 'Une erreur est survenue', 'danger');
        });
    };
    
    // === GESTION MODIFICATION CONFIGURATION ===
    
    /**
     * Rediriger vers configurateur pour modifier une adresse
     */
    window.modifierConfiguration = function(address) {
        
        // Sauvegarder l'état du panier dans localStorage pour récupération
        const cartState = {
            address: address,
            return_to_cart: true,
            timestamp: Date.now()
        };
        
        localStorage.setItem('soeasy_edit_cart_address', JSON.stringify(cartState));
        
        // Construire l'URL du configurateur
        const configUrl = vars.configurateur_url || '/configurateur/';
        const separator = configUrl.includes('?') ? '&' : '?';
        
        window.location.href = configUrl + separator + 'edit_address=' + encodeURIComponent(address);
    };
    
    // === GESTION QUANTITÉS ===
    
    /**
     * Mise à jour quantité produit
     */
    $(document).on('change', '.product-quantity input[type="number"]', function() {
        
        const $input = $(this);
        const cartItemKey = $input.attr('name').match(/cart\[([a-f0-9]+)\]/)[1];
        const quantity = parseInt($input.val()) || 0;
        const $row = $input.closest('tr');
        
        if (!cartItemKey) {
            showNotification('Erreur: clé produit introuvable', 'danger');
            return;
        }
        
        showLoading($row);
        
        $.post(vars.ajaxurl, {
            action: 'soeasy_update_cart_item_quantity',
            cart_item_key: cartItemKey,
            quantity: quantity,
            security: vars.security
        })
        .done(function(response) {
            if (response.success) {
                
                if (quantity === 0) {
                    // Supprimer la ligne avec animation
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        updateCartTotals();
                    });
                } else {
                    hideLoading($row);
                    
                    // Mettre à jour le sous-total de la ligne
                    // Note: WooCommerce fait normalement ça automatiquement
                    updateCartTotals();
                }
                
                showNotification(response.data.message, 'success');
                
            } else {
                hideLoading($row);
                $input.val($input.data('original-value') || 1); // Remettre l'ancienne valeur
                showNotification(response.data.message || vars.messages.error_generic, 'danger');
            }
        })
        .fail(function() {
            hideLoading($row);
            $input.val($input.data('original-value') || 1);
            showNotification(vars.messages.error_generic, 'danger');
        });
    });
    
    // Sauvegarder la valeur originale avant changement
    $(document).on('focus', '.product-quantity input[type="number"]', function() {
        $(this).data('original-value', $(this).val());
    });
    
    // === GESTION COUPONS ===
    
    /**
     * Application coupon avec feedback amélioré
     */
    $(document).on('submit', '.coupon form, form.woocommerce-cart-form', function(e) {
        
        const $form = $(this);
        const $couponInput = $form.find('input[name="coupon_code"]');
        const $submitBtn = $form.find('button[name="apply_coupon"], input[name="apply_coupon"]');
        
        if ($submitBtn.length && $couponInput.length && $couponInput.val().trim()) {
            
            const $couponSection = $couponInput.closest('.coupon');
            showLoading($couponSection);
            
            // Laisser WooCommerce gérer la soumission normale
            // mais ajouter un feedback visuel
            setTimeout(() => {
                hideLoading($couponSection);
            }, 2000);
        }
    });
    
    // === AMÉLIORATION UX ===
    
    /**
     * Confirmation avant suppression produit individuel
     */
    $(document).on('click', '.product-remove a', function(e) {
        
        const $link = $(this);
        const productName = $link.closest('tr').find('.product-name a').text().trim() || 'ce produit';
        
        if (!confirm(`Êtes-vous sûr de vouloir supprimer "${productName}" ?`)) {
            e.preventDefault();
            return false;
        }
        
        // Ajouter loading sur la ligne
        const $row = $link.closest('tr');
        showLoading($row);
    });
    
    /**
     * Smooth scroll vers les erreurs/messages
     */
    function scrollToMessages() {
        const $messages = $('.woocommerce-message, .woocommerce-error, .woocommerce-info, .cart-notification');
        if ($messages.length) {
            $('html, body').animate({
                scrollTop: $messages.first().offset().top - 100
            }, 500);
        }
    }
    
    // Auto-scroll après chargement si messages présents
    setTimeout(scrollToMessages, 500);
    
    // === STICKY SIDEBAR ===
    
    /**
     * Sidebar sticky avec responsive
     */
    function initStickySidebar() {
        
        const $sidebar = $('.cart-summary');
        
        if ($sidebar.length && window.innerWidth > 991) {
            
            const $window = $(window);
            const $container = $('.soeasy-cart-wrapper');
            const sidebarHeight = $sidebar.outerHeight();
            const containerTop = $container.offset().top;
            const containerBottom = containerTop + $container.outerHeight();
            
            $window.on('scroll.stickySidebar', function() {
                const scrollTop = $window.scrollTop();
                const windowHeight = $window.height();
                
                if (scrollTop + 20 > containerTop && scrollTop + sidebarHeight + 40 < containerBottom) {
                    $sidebar.css({
                        position: 'fixed',
                        top: '20px',
                        width: $sidebar.parent().width() + 'px'
                    });
                } else {
                    $sidebar.css({
                        position: 'relative