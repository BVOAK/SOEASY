<?php
/**
 * Fonctions utilitaires pour le configurateur SoEasy
 */


/**
 * Gestion des sessions WooCommerce
 */
function soeasy_start_session_if_needed()
{
    if (!class_exists('WooCommerce'))
        return;
    if (!WC()->session || !WC()->session->has_session()) {
        WC()->session = WC()->session ?: WC_Session_Handler::instance();
        WC()->session->init();
        WC()->session->set_customer_session_cookie(true);
    }
}

function soeasy_session_set($key, $value)
{
    soeasy_start_session_if_needed();
    if (WC()->session)
        WC()->session->set($key, $value);
}

function soeasy_session_get($key, $default = null)
{
    soeasy_start_session_if_needed();
    return WC()->session ? WC()->session->get($key, $default) : $default;
}

function soeasy_session_delete($key)
{
    soeasy_start_session_if_needed();
    if (WC()->session)
        WC()->session->__unset($key);
}

/**
 * Fonctions générales
 */
function soeasy_get_adresses_configurateur()
{
    return soeasy_session_get('soeasy_config_adresses', []);
}

function soeasy_add_adresse_configurateur($adresse, $services)
{
    $adresses = soeasy_get_adresses_configurateur();
    $adresses[] = [
        'adresse' => sanitize_text_field($adresse),
        'services' => array_map('sanitize_text_field', $services)
    ];
    soeasy_session_set('soeasy_config_adresses', $adresses);
    return $adresses;
}

function soeasy_get_selected_services($index)
{
    $adresses = soeasy_get_adresses_configurateur();
    return $adresses[$index]['services'] ?? [];
}

function soeasy_get_selected_duree_engagement()
{
    return soeasy_session_get('soeasy_duree_engagement');
}

function soeasy_get_selected_financement()
{
    return soeasy_session_get('soeasy_mode_financement', 'comptant');
}

function soeasy_get_leasing_price($product_id, $duree)
{
    return get_field('prix_leasing_' . $duree, $product_id);
}

function soeasy_add_product_to_cart($product_id, $quantity = 1, $meta = [])
{
    WC()->cart->add_to_cart($product_id, $quantity, 0, [], $meta);
}

function soeasy_get_config_produits()
{
    $config = WC()->session->get('soeasy_configurateur', []);
    $resultat = [];

    foreach ($config as $index => $data) {
        $resultat[$index] = [];

        foreach (['abonnements', 'materiels', 'fraisInstallation'] as $cle) {
            if (!isset($data[$cle]))
                continue;

            foreach ($data[$cle] as $item) {
                $id = isset($item['id']) ? $item['id'] : null;
                if (!$id)
                    continue;

                $type = $item['type'] ?? 'inconnu';

                if (!isset($resultat[$index][$type])) {
                    $resultat[$index][$type] = [];
                }

                $resultat[$index][$type][] = [
                    'id' => $id,
                    'type' => $type
                ];
            }
        }
    }

    return $resultat;
}

/**
 * Generic session array setter (for step 2, 3, 4...)
 */
function soeasy_set_session_items($key, $index, $items)
{
    if ($index < 0 || !is_array($items))
        wp_send_json_error('Paramètres invalides');
    $session_data = soeasy_session_get($key, []);
    $session_data[$index] = array_map(fn($item) => ['id' => intval($item['id']), 'qty' => intval($item['qty'])], $items);
    soeasy_session_set($key, $session_data);
    wp_send_json_success('Données enregistrées');
}

/**
 * AJAX handlers
 */
function ajax_soeasy_add_adresse_configurateur()
{
    $adresse = $_POST['adresse'] ?? '';
    $services = $_POST['services'] ?? [];
    if (!$adresse /* || empty($services) */)
        wp_send_json_error('Adresse ou services manquants');
    $updated = soeasy_add_adresse_configurateur($adresse, $services);

    ob_start();
    foreach ($updated as $i => $adr) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        //echo '<span>' . esc_html($adr['adresse']) . ' — <em>Services : ' . implode(', ', $adr['services']) . '</em></span>';
        echo '<span>' . esc_html($adr['adresse']) . '</span>';
        echo '<button class="btn btn-sm btn-outline-danger btn-remove-adresse" data-index="' . $i . '">Supprimer</button>';
        echo '</li>';
    }
    wp_send_json_success(['html' => ob_get_clean()]);
}

function ajax_soeasy_remove_adresse_configurateur()
{
    $index = intval($_POST['index'] ?? -1);
    $adresses = soeasy_get_adresses_configurateur();
    if ($index >= 0 && isset($adresses[$index])) {
        unset($adresses[$index]);
        soeasy_session_set('soeasy_config_adresses', array_values($adresses));
        wp_send_json_success($adresses);
    }
    wp_send_json_error('Adresse non trouvée');
}

/**
 * Enregistrement des hooks AJAX
 */
add_action('wp_ajax_soeasy_add_adresse_configurateur', 'ajax_soeasy_add_adresse_configurateur');
add_action('wp_ajax_nopriv_soeasy_add_adresse_configurateur', 'ajax_soeasy_add_adresse_configurateur');
add_action('wp_ajax_soeasy_remove_adresse_configurateur', 'ajax_soeasy_remove_adresse_configurateur');
add_action('wp_ajax_nopriv_soeasy_remove_adresse_configurateur', 'ajax_soeasy_remove_adresse_configurateur');

add_action('wp_ajax_soeasy_set_engagement', fn() => soeasy_session_set('soeasy_duree_engagement', intval($_POST['duree'] ?? 0)));
add_action('wp_ajax_nopriv_soeasy_set_engagement', fn() => soeasy_session_set('soeasy_duree_engagement', intval($_POST['duree'] ?? 0)));
add_action('wp_ajax_soeasy_set_financement', fn() => soeasy_session_set('soeasy_mode_financement', sanitize_text_field($_POST['mode'] ?? '')));
add_action('wp_ajax_nopriv_soeasy_set_financement', fn() => soeasy_session_set('soeasy_mode_financement', sanitize_text_field($_POST['mode'] ?? '')));

// Étape 2
/* add_action('wp_ajax_soeasy_set_forfait_internet', fn() => soeasy_session_set('soeasy_forfaits_internet', array_replace(soeasy_session_get('soeasy_config_forfaits_internet', []), [intval($_POST['index'] ?? -1) => intval($_POST['product_id'] ?? 0)])));
add_action('wp_ajax_nopriv_soeasy_set_forfait_internet', fn() => soeasy_session_set('soeasy_forfaits_internet', array_replace(soeasy_session_get('soeasy_config_forfaits_internet', []), [intval($_POST['index'] ?? -1) => intval($_POST['product_id'] ?? 0)])));
add_action('wp_ajax_soeasy_set_equipements_internet', fn() => soeasy_session_set('soeasy_equipements_internet', array_replace(soeasy_session_get('soeasy_config_equipements_internet', []), [intval($_POST['index'] ?? -1) => array_map('intval', $_POST['product_ids'] ?? [])])));
add_action('wp_ajax_nopriv_soeasy_set_equipements_internet', fn() => soeasy_session_set('soeasy_equipements_internet', array_replace(soeasy_session_get('soeasy_config_equipements_internet', []), [intval($_POST['index'] ?? -1) => array_map('intval', $_POST['product_ids'] ?? [])]))); */

// Enregistre le forfait Internet sélectionné
add_action('wp_ajax_soeasy_set_forfait_internet', 'soeasy_set_forfait_internet');
add_action('wp_ajax_nopriv_soeasy_set_forfait_internet', 'soeasy_set_forfait_internet');
function soeasy_set_forfait_internet()
{
    $index = intval($_POST['index'] ?? -1);
    $product_id = intval($_POST['product_id'] ?? 0);
    if ($index < 0 || $product_id <= 0) {
        wp_send_json_error('Paramètres invalides');
    }

    $session_key = 'config_internet_' . $index;
    WC()->session->set($session_key, $product_id);

    wp_send_json_success("Forfait Internet $product_id enregistré pour index $index.");
}

// Enregistre les équipements Internet sélectionnés
add_action('wp_ajax_soeasy_set_equipements_internet', 'soeasy_set_equipements_internet');
add_action('wp_ajax_nopriv_soeasy_set_equipements_internet', 'soeasy_set_equipements_internet');
function soeasy_set_equipements_internet()
{
    $index = intval($_POST['index'] ?? -1);
    $product_ids = array_map('intval', $_POST['product_ids'] ?? []);
    if ($index < 0) {
        wp_send_json_error('Index invalide');
    }

    $session_key = 'equipements_internet_' . $index;
    WC()->session->set($session_key, $product_ids);

    wp_send_json_success("Équipements enregistrés pour index $index.");
}

add_action('wp_ajax_soeasy_set_frais_installation', 'soeasy_set_frais_installation');
add_action('wp_ajax_nopriv_soeasy_set_frais_installation', 'soeasy_set_frais_installation');

function soeasy_set_frais_installation()
{
    if (!isset($_POST['index']) || !isset($_POST['items']) || !is_array($_POST['items'])) {
        wp_send_json_error('Paramètres manquants.');
        return;
    }

    $index = sanitize_text_field($_POST['index']);
    $items = $_POST['items'];

    $config = WC()->session->get('soeasy_configurateur', []);

    // Initialiser l’index si besoin
    if (!isset($config[$index])) {
        $config[$index] = [];
    }

    // Injecter les frais
    $config[$index]['fraisInstallation'] = array_map(function ($item) {
        return [
            'id' => intval($item['id']),
            'nom' => sanitize_text_field($item['nom'] ?? ''),
            'quantite' => intval($item['quantite']),
            'type' => sanitize_text_field($item['type'] ?? 'internet'),
            'prixComptant' => floatval($item['prixComptant'] ?? 0),
            'prixLeasing24' => floatval($item['prixLeasing24'] ?? 0),
            'prixLeasing36' => floatval($item['prixLeasing36'] ?? 0),
            'prixLeasing48' => floatval($item['prixLeasing48'] ?? 0),
            'prixLeasing63' => floatval($item['prixLeasing63'] ?? 0),
        ];
    }, $items);

    WC()->session->set('soeasy_configurateur', $config);

    wp_send_json_success(['config' => $config[$index]]);
}



// Étape 3
add_action('wp_ajax_soeasy_set_forfaits_mobile', fn() => soeasy_set_session_items('soeasy_forfaits_mobile', intval($_POST['index'] ?? -1), $_POST['items'] ?? []));
add_action('wp_ajax_nopriv_soeasy_set_forfaits_mobile', fn() => soeasy_set_session_items('soeasy_forfaits_mobile', intval($_POST['index'] ?? -1), $_POST['items'] ?? []));
add_action('wp_ajax_soeasy_set_forfaits_data', fn() => soeasy_set_session_items('soeasy_forfaits_data', intval($_POST['index'] ?? -1), $_POST['items'] ?? []));
add_action('wp_ajax_nopriv_soeasy_set_forfaits_data', fn() => soeasy_set_session_items('soeasy_forfaits_data', intval($_POST['index'] ?? -1), $_POST['items'] ?? []));
add_action('wp_ajax_soeasy_set_equipements_mobile', fn() => soeasy_set_session_items('soeasy_equipements_mobile', intval($_POST['index'] ?? -1), $_POST['items'] ?? []));
add_action('wp_ajax_nopriv_soeasy_set_equipements_mobile', fn() => soeasy_set_session_items('soeasy_equipements_mobile', intval($_POST['index'] ?? -1), $_POST['items'] ?? []));

// Étape 4
foreach (['licences', 'services', 'postes', 'switchs', 'accessoires'] as $type) {
    $key = "soeasy_{$type}_centrex";
    add_action("wp_ajax_soeasy_set_{$type}_centrex", fn() => soeasy_set_session_items($key, intval($_POST['index'] ?? -1), $_POST['items'] ?? []));
    add_action("wp_ajax_nopriv_soeasy_set_{$type}_centrex", fn() => soeasy_set_session_items($key, intval($_POST['index'] ?? -1), $_POST['items'] ?? []));
}

// Étape 5
add_action('wp_ajax_soeasy_set_config_part', 'soeasy_set_config_part');
add_action('wp_ajax_nopriv_soeasy_set_config_part', 'soeasy_set_config_part');
function soeasy_set_config_part()
{
    $index = sanitize_text_field($_POST['index']);
    $key = sanitize_text_field($_POST['key']);
    $items = $_POST['items'];

    if ($index === '' || $key === '') {
        wp_send_json_error('Paramètres manquants');
    }

    $config = WC()->session->get('soeasy_configurateur', []);
    if (!isset($config[$index])) {
        $config[$index] = [];
    }

    $config[$index][$key] = $items;
    WC()->session->set('soeasy_configurateur', $config);
    wp_send_json_success('Mise à jour OK');
}

function soeasy_resolve_product($input)
{
    if ($input instanceof WC_Product) {
        return $input;
    }
    if ($input instanceof WP_Post) {
        return wc_get_product($input->ID);
    }
    if (is_numeric($input)) {
        return wc_get_product(intval($input));
    }
    return null;
}

// Ajout au panier
add_action('wp_ajax_soeasy_ajouter_au_panier', 'soeasy_ajouter_au_panier');
add_action('wp_ajax_nopriv_soeasy_ajouter_au_panier', 'soeasy_ajouter_au_panier');
function soeasy_ajouter_au_panier()
{
    if (!function_exists('WC')) {
        wp_send_json_error(['message' => 'WooCommerce non disponible']);
    }

    WC()->cart->empty_cart();

    $adresse_index = intval($_POST['index'] ?? 0);

    // Mapping clair des types par étape
    $config_map = [
        'step2' => [
            'soeasy_forfaits_internet',
            'soeasy_equipements_internet',
        ],
        'step3' => [
            'soeasy_forfaits_mobile',
            'soeasy_forfaits_data',
            'soeasy_equipements_mobile',
        ],
        'step4' => [
            'soeasy_licences_centrex',
            'soeasy_services_centrex',
            'soeasy_postes_centrex',
            'soeasy_switchs_centrex',
            'soeasy_accessoires_centrex',
        ]
    ];

    // Ajout au panier pour chaque type de produit
    foreach ($config_map as $step => $types) {
        foreach ($types as $type) {
            $produits = soeasy_session_get($type, []);
            foreach ($produits[$adresse_index] ?? [] as $item) {
                if (!empty($item['id']) && !empty($item['qty'])) {
                    WC()->cart->add_to_cart($item['id'], $item['qty']);
                }
            }
        }
    }

    $fake_product_id = wc_get_product_id_by_sku('soeasy-config-preview');
    if ($fake_product_id) {
        WC()->cart->add_to_cart($fake_product_id);
    }

    wp_send_json_success(['redirect' => wc_get_checkout_url()]);
    wp_send_json_error(['message' => 'Votre configuration est vide.']);


    error_log(print_r([
        'internet' => soeasy_session_get('soeasy_forfaits_internet'),
        'centrex' => soeasy_session_get('soeasy_licences_centrex'),
        'mobile' => soeasy_session_get('soeasy_forfaits_mobile'),
        'frais' => soeasy_session_get('soeasy_frais_installation'),
    ], true));
}

?>


<?php
/**
 * Ajout au panier pour configuration multi-adresses
 */

// Hook AJAX pour la nouvelle fonction
add_action('wp_ajax_soeasy_ajouter_au_panier_multi', 'soeasy_ajouter_au_panier_multi');
add_action('wp_ajax_nopriv_soeasy_ajouter_au_panier_multi', 'soeasy_ajouter_au_panier_multi');

function soeasy_ajouter_au_panier_multi() {
    if (!function_exists('WC')) {
        wp_send_json_error(['message' => 'WooCommerce non disponible']);
        return;
    }
    
    // 1. Récupération des données
    $config = $_POST['config'] ?? [];
    $adresses = $_POST['adresses'] ?? [];
    
    if (empty($config)) {
        wp_send_json_error(['message' => 'Configuration vide']);
        return;
    }
    
    // 2. Vider le panier existant
    WC()->cart->empty_cart();
    
    // 3. Compteurs pour debug
    $produits_ajoutes = 0;
    $erreurs = [];
    
    // 4. Traitement de chaque adresse
    foreach ($config as $adresse_index => $data_adresse) {
        
        $nom_adresse = $adresses[$adresse_index]['adresse'] ?? "Adresse #" . ($adresse_index + 1);
        
        // 4a. Abonnements
        if (!empty($data_adresse['abonnements'])) {
            foreach ($data_adresse['abonnements'] as $produit) {
                $resultat = ajouter_produit_au_panier($produit, $nom_adresse, 'Abonnement');
                if ($resultat['success']) {
                    $produits_ajoutes++;
                } else {
                    $erreurs[] = $resultat['error'];
                }
            }
        }
        
        // 4b. Matériels
        if (!empty($data_adresse['materiels'])) {
            foreach ($data_adresse['materiels'] as $produit) {
                $resultat = ajouter_produit_au_panier($produit, $nom_adresse, 'Matériel');
                if ($resultat['success']) {
                    $produits_ajoutes++;
                } else {
                    $erreurs[] = $resultat['error'];
                }
            }
        }
        
        // 4c. Frais d'installation
        if (!empty($data_adresse['fraisInstallation'])) {
            foreach ($data_adresse['fraisInstallation'] as $frais) {
                $resultat = ajouter_produit_au_panier($frais, $nom_adresse, 'Frais d\'installation');
                if ($resultat['success']) {
                    $produits_ajoutes++;
                } else {
                    $erreurs[] = $resultat['error'];
                }
            }
        }
    }
    
    // 5. Résultat final
    if ($produits_ajoutes > 0) {
        
        // Sauvegarder la config dans la session pour le checkout
        WC()->session->set('soeasy_configuration_complete', [
            'config' => $config,
            'adresses' => $adresses,
            'timestamp' => current_time('timestamp')
        ]);
        
        $message = sprintf(
            '%d produit%s ajouté%s au panier',
            $produits_ajoutes,
            $produits_ajoutes > 1 ? 's' : '',
            $produits_ajoutes > 1 ? 's' : ''
        );
        
        if (!empty($erreurs)) {
            $message .= '. Quelques erreurs : ' . implode(', ', array_slice($erreurs, 0, 3));
        }
        
        wp_send_json_success([
            'message' => $message,
            'produits_ajoutes' => $produits_ajoutes,
            'erreurs' => $erreurs,
            'redirect_url' => wc_get_cart_url()
        ]);
        
    } else {
        wp_send_json_error([
            'message' => 'Aucun produit n\'a pu être ajouté au panier.',
            'erreurs' => $erreurs
        ]);
    }
}

/**
 * Fonction helper pour ajouter un produit au panier WC
 */
function ajouter_produit_au_panier($produit_data, $nom_adresse, $categorie) {
    
    if (empty($produit_data['id']) || empty($produit_data['quantite'])) {
        return [
            'success' => false,
            'error' => "Données produit invalides (ID: {$produit_data['id']}, Qty: {$produit_data['quantite']})"
        ];
    }
    
    $product_id = intval($produit_data['id']);
    $quantity = intval($produit_data['quantite']);
    
    // Vérifier que le produit existe
    $product = wc_get_product($product_id);
    if (!$product) {
        return [
            'success' => false,
            'error' => "Produit #{$product_id} introuvable"
        ];
    }
    
    // Métadonnées pour identifier la configuration dans le panier
    $cart_item_data = [
        'soeasy_adresse' => $nom_adresse,
        'soeasy_categorie' => $categorie,
        'soeasy_config_id' => uniqid('soeasy_')
    ];
    
    // Si prix custom (pour gestion engagement/leasing)
    if (!empty($produit_data['prixUnitaire'])) {
        $cart_item_data['soeasy_prix_custom'] = floatval($produit_data['prixUnitaire']);
    }
    
    try {
        $cart_item_key = WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            0, // variation_id
            [], // variation
            $cart_item_data
        );
        
        if ($cart_item_key) {
            return [
                'success' => true,
                'cart_item_key' => $cart_item_key
            ];
        } else {
            return [
                'success' => false,
                'error' => "Échec ajout produit #{$product_id} (WC error)"
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => "Exception produit #{$product_id}: " . $e->getMessage()
        ];
    }
}

/**
 * Hook pour afficher les métadonnées SoEasy dans le panier
 */
add_filter('woocommerce_get_item_data', 'soeasy_display_cart_item_data', 10, 2);

function soeasy_display_cart_item_data($cart_item_data, $cart_item) {
    
    if (isset($cart_item['soeasy_adresse'])) {
        $cart_item_data[] = [
            'name' => 'Adresse de service',
            'value' => esc_html($cart_item['soeasy_adresse'])
        ];
    }
    
    if (isset($cart_item['soeasy_categorie'])) {
        $cart_item_data[] = [
            'name' => 'Type',
            'value' => esc_html($cart_item['soeasy_categorie'])
        ];
    }
    
    return $cart_item_data;
}

/**
 * Hook pour appliquer les prix custom dans le panier
 */
add_action('woocommerce_before_calculate_totals', 'soeasy_apply_custom_prices');

function soeasy_apply_custom_prices($cart) {
    
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (did_action('woocommerce_before_calculate_totals') >= 2) return;
    
    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['soeasy_prix_custom'])) {
            $custom_price = floatval($cart_item['soeasy_prix_custom']);
            if ($custom_price > 0) {
                $cart_item['data']->set_price($custom_price);
            }
        }
    }
}
?>