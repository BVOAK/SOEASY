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
                        position: 'relative',
                        top: 'auto',
                        width: 'auto'
                    });
                }
            });
            
            // Recalculer lors du resize
            $window.on('resize.stickySidebar', function() {
                if (window.innerWidth <= 991) {
                    $sidebar.css({
                        position: 'relative',
                        top: 'auto',
                        width: 'auto'
                    });
                    $window.off('scroll.stickySidebar');
                } else {
                    initStickySidebar();
                }
            });
        }
    }
    
    // Initialiser après chargement complet
    setTimeout(initStickySidebar, 100);
    
    // === GESTION RETOUR CONFIGURATEUR ===
    
    /**
     * Vérifier si on revient du configurateur
     */
    function checkConfiguratorReturn() {
        
        const editState = localStorage.getItem('soeasy_edit_cart_address');
        
        if (editState) {
            try {
                const state = JSON.parse(editState);
                
                // Vérifier que c'est récent (moins de 1h)
                if (Date.now() - state.timestamp < 3600000) {
                    
                    showNotification(
                        `Configuration mise à jour pour l'adresse "${state.address}"`, 
                        'success'
                    );
                }
                
            } catch (e) {
                console.warn('Erreur parsing edit state:', e);
            }
            
            // Nettoyer le localStorage
            localStorage.removeItem('soeasy_edit_cart_address');
        }
    }
    
    checkConfiguratorReturn();
    
    // === VALIDATION CHECKOUT ===
    
    /**
     * Validation avant checkout
     */
    $(document).on('click', '.checkout-actions .btn-success', function(e) {
        
        const $btn = $(this);
        const cartCount = parseInt($('.soeasy-cart-widget .cart-count').text().match(/\d+/)?.[0] || 0);
        
        if (cartCount === 0) {
            e.preventDefault();
            showNotification('Votre panier est vide', 'warning');
            return false;
        }
        
        // Feedback visuel pendant redirection
        showLoading($btn.closest('.checkout-actions'));
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Redirection...');
    });
    
    // === ANIMATIONS ENTRÉE ===
    
    /**
     * Animation des sections adresses au chargement
     */
    function animateAddressSections() {
        $('.address-section').each(function(index) {
            const $section = $(this);
            
            setTimeout(() => {
                $section.addClass('animate__animated animate__fadeInUp');
            }, index * 100);
        });
    }
    
    // Lancer animations si bibliothèque animate.css disponible
    if (typeof $.fn.addClass !== 'undefined' && $('.animate__animated').length === 0) {
        // Fallback si animate.css pas disponible
        setTimeout(animateAddressSections, 300);
    }
    
    // === GESTURES MOBILES ===
    
    /**
     * Swipe pour supprimer sur mobile
     */
    if ('ontouchstart' in window) {
        
        let startX, startY, isScrolling;
        
        $(document).on('touchstart', '.table-cart tbody tr', function(e) {
            const touch = e.originalEvent.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
            isScrolling = undefined;
        });
        
        $(document).on('touchmove', '.table-cart tbody tr', function(e) {
            
            if (e.originalEvent.touches.length > 1) return;
            
            const touch = e.originalEvent.touches[0];
            const deltaX = touch.clientX - startX;
            const deltaY = touch.clientY - startY;
            
            if (isScrolling === undefined) {
                isScrolling = Math.abs(deltaX) < Math.abs(deltaY);
            }
            
            if (!isScrolling && Math.abs(deltaX) > 30) {
                $(this).addClass('swiping');
                
                if (deltaX < -50) {
                    // Swipe gauche - afficher action suppression
                    $(this).addClass('swipe-delete');
                } else {
                    $(this).removeClass('swipe-delete');
                }
            }
        });
        
        $(document).on('touchend', '.table-cart tbody tr', function(e) {
            const $row = $(this);
            
            setTimeout(() => {
                if ($row.hasClass('swipe-delete')) {
                    const $removeLink = $row.find('.product-remove a');
                    if ($removeLink.length) {
                        $removeLink.trigger('click');
                    }
                }
                
                $row.removeClass('swiping swipe-delete');
            }, 100);
        });
    }
    
    // === RACCOURCIS CLAVIER ===
    
    /**
     * Raccourcis clavier pour navigation rapide
     */
    $(document).on('keydown', function(e) {
        
        // Échapper pour fermer les modales/alertes
        if (e.key === 'Escape') {
            $('.alert-dismissible .btn-close').trigger('click');
        }
        
        // Ctrl+Enter pour aller au checkout
        if (e.ctrlKey && e.key === 'Enter') {
            $('.checkout-actions .btn-success').trigger('click');
        }
        
        // Suppr pour supprimer le premier élément sélectionné
        if (e.key === 'Delete' && $('.table-cart tbody tr.selected').length) {
            $('.table-cart tbody tr.selected').first().find('.product-remove a').trigger('click');
        }
    });
    
    // Sélection des lignes au clic (pour raccourcis clavier)
    $(document).on('click', '.table-cart tbody tr', function(e) {
        
        if ($(e.target).closest('.product-quantity, .product-remove').length) {
            return; // Ne pas sélectionner si clic sur quantité ou suppression
        }
        
        $('.table-cart tbody tr').removeClass('selected');
        $(this).addClass('selected');
    });
    
    // === FONCTIONS PUBLIQUES ===
    
    /**
     * API publique pour interaction externe
     */
    window.SoEasyCart = {
        
        updateTotals: updateCartTotals,
        
        showNotification: showNotification,
        
        removeAddress: function(address) {
            window.supprimerAdresse(address);
        },
        
        editAddress: function(address) {
            window.modifierConfiguration(address);
        },
        
        getCartState: function() {
            return {
                addressCount: $('.address-section').length,
                totalItems: parseInt($('.soeasy-cart-widget .cart-count').text().match(/\d+/)?.[0] || 0),
                addresses: $('.address-section .address-header h4').map(function() {
                    return $(this).text().trim();
                }).get()
            };
        }
    };
    
    // === DEBUG MODE ===
    
    if (window.location.search.includes('debug=cart')) {
        console.log('SoEasy Cart Debug Mode activé');
        console.log('Cart State:', window.SoEasyCart.getCartState());
        console.log('Variables disponibles:', vars);
        
        // Ajouter bouton debug
        $('body').append(`
            <div id="soeasy-debug" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; background: #333; color: white; padding: 10px; border-radius: 5px; font-size: 12px;">
                <strong>Debug Cart</strong><br>
                <button onclick="console.log(window.SoEasyCart.getCartState())">Log State</button>
                <button onclick="window.SoEasyCart.updateTotals()">Update Totals</button>
            </div>
        `);
    }
    
    // === INITIALISATION FINALE ===
    
    console.log('✅ SoEasy Cart JavaScript initialisé');
    
    // Déclencher événement custom pour autres scripts
    $(document).trigger('soeasy:cart:ready', [window.SoEasyCart]);
    
});