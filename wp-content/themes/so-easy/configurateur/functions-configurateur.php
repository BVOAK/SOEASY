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
 * Fonction utilitaire de vérification des nonces
 */
function soeasy_verify_nonce($nonce_value, $nonce_action) {
    if (!wp_verify_nonce($nonce_value, $nonce_action)) {
        wp_send_json_error('Sécurité : nonce invalide');
        exit;
    }
}


// ============================================================================
// CONFIGURATION GÉNÉRALE
// ============================================================================

function soeasy_set_engagement() {
    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_config_action');
    
    // LOGIQUE EXISTANTE INCHANGÉE
    soeasy_session_set('soeasy_duree_engagement', intval($_POST['duree'] ?? 0));
    wp_send_json_success('Engagement enregistré');
}
add_action('wp_ajax_soeasy_set_engagement', 'soeasy_set_engagement');
add_action('wp_ajax_nopriv_soeasy_set_engagement', 'soeasy_set_engagement');
function soeasy_set_financement() {
    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_config_action');
    
    // LOGIQUE EXISTANTE INCHANGÉE
    soeasy_session_set('soeasy_mode_financement', sanitize_text_field($_POST['mode'] ?? ''));
    wp_send_json_success('Mode financement enregistré');
}
add_action('wp_ajax_soeasy_set_financement', 'soeasy_set_financement');
add_action('wp_ajax_nopriv_soeasy_set_financement', 'soeasy_set_financement');


// ============================================================================
// CATÉGORIE 1 - GESTION DES ADRESSES (SÉCURISÉES)
// ============================================================================

function ajax_soeasy_add_adresse_configurateur(){

    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_address_action');

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

function ajax_soeasy_remove_adresse_configurateur(){

    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_address_action');

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


// ============================================================================
// CATÉGORIE 2 - ÉTAPE 2 INTERNET (SÉCURISÉES)
// ============================================================================
function soeasy_set_forfait_internet(){

    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_config_action');

    $index = intval($_POST['index'] ?? -1);
    $product_id = intval($_POST['product_id'] ?? 0);
    if ($index < 0 || $product_id <= 0) {
        wp_send_json_error('Paramètres invalides');
    }

    $session_key = 'config_internet_' . $index;
    WC()->session->set($session_key, $product_id);

    wp_send_json_success("Forfait Internet $product_id enregistré pour index $index.");
}
add_action('wp_ajax_soeasy_set_forfait_internet', 'soeasy_set_forfait_internet');
add_action('wp_ajax_nopriv_soeasy_set_forfait_internet', 'soeasy_set_forfait_internet');

// Enregistre les équipements Internet sélectionnés
function soeasy_set_equipements_internet(){

    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_config_action');

    $index = intval($_POST['index'] ?? -1);
    $product_ids = array_map('intval', $_POST['product_ids'] ?? []);
    if ($index < 0) {
        wp_send_json_error('Index invalide');
    }

    $session_key = 'equipements_internet_' . $index;
    WC()->session->set($session_key, $product_ids);

    wp_send_json_success("Équipements enregistrés pour index $index.");
}
add_action('wp_ajax_soeasy_set_equipements_internet', 'soeasy_set_equipements_internet');
add_action('wp_ajax_nopriv_soeasy_set_equipements_internet', 'soeasy_set_equipements_internet');


// ============================================================================
// CATÉGORIE 3 - ÉTAPE 3 MOBILE (SÉCURISÉES)
// ============================================================================

function soeasy_set_forfaits_mobile() {
    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_config_action');
    soeasy_set_session_items('soeasy_forfaits_mobile', intval($_POST['index'] ?? -1), $_POST['items'] ?? []);
}
add_action('wp_ajax_soeasy_set_forfaits_mobile', 'soeasy_set_forfaits_mobile');
add_action('wp_ajax_nopriv_soeasy_set_forfaits_mobile', 'soeasy_set_forfaits_mobile');

function soeasy_set_forfaits_data() {
    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_config_action');
    soeasy_set_session_items('soeasy_forfaits_data', intval($_POST['index'] ?? -1), $_POST['items'] ?? []);
}
add_action('wp_ajax_soeasy_set_forfaits_data', 'soeasy_set_forfaits_data');
add_action('wp_ajax_nopriv_soeasy_set_forfaits_data', 'soeasy_set_forfaits_data');

function soeasy_set_equipements_mobile() {
    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_config_action');
    soeasy_set_session_items('soeasy_equipements_mobile', intval($_POST['index'] ?? -1), $_POST['items'] ?? []);
}
add_action('wp_ajax_soeasy_set_equipements_mobile', 'soeasy_set_equipements_mobile');
add_action('wp_ajax_nopriv_soeasy_set_equipements_mobile', 'soeasy_set_equipements_mobile');



// ============================================================================
// CATÉGORIE 4 - ÉTAPE 4 CENTREX (SÉCURISÉES)
// ============================================================================

foreach (['licences', 'services', 'postes', 'switchs', 'accessoires'] as $type) {
    $function_name = "soeasy_set_{$type}_centrex";
    
    if (!function_exists($function_name)) {
        eval("
        function {$function_name}() {
            soeasy_verify_nonce(\$_POST['nonce'] ?? '', 'soeasy_config_action');
            soeasy_set_session_items('soeasy_{$type}_centrex', intval(\$_POST['index'] ?? -1), \$_POST['items'] ?? []);
        }
        ");
    }
    
    add_action("wp_ajax_{$function_name}", $function_name);
    add_action("wp_ajax_nopriv_{$function_name}", $function_name);
}


// ============================================================================
// CATÉGORIE 5 - ÉTAPE 5 FRAIS D'INSTALLATION (SÉCURISÉES)
// ============================================================================

function soeasy_set_frais_installation(){

    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_config_action');

    if (!isset($_POST['index']) || !isset($_POST['items']) || !is_array($_POST['items'])) {
        wp_send_json_error('Paramètres manquants.');
        return;
    }

    $index = sanitize_text_field($_POST['index']);
    $items = $_POST['items'];

    $config = WC()->session->get('soeasy_configurateur', []);

    // Initialiser l'index si besoin
    if (!isset($config[$index])) {
        $config[$index] = [];
    }

    // Injecter les frais avec validation
    $config[$index]['fraisInstallation'] = array_map(function ($item) {
        return [
            'id' => intval($item['id']),
            'nom' => sanitize_text_field($item['nom'] ?? ''),
            'quantite' => intval($item['quantite'] ?? 1),
            'type' => sanitize_text_field($item['type'] ?? 'internet'),
            'prixComptant' => floatval($item['prixComptant'] ?? 0),
            'prixLeasing24' => floatval($item['prixLeasing24'] ?? 0),
            'prixLeasing36' => floatval($item['prixLeasing36'] ?? 0),
            'prixLeasing48' => floatval($item['prixLeasing48'] ?? 0),
            'prixLeasing63' => floatval($item['prixLeasing63'] ?? 0),
        ];
    }, $items);

    // Sauvegarder en session
    WC()->session->set('soeasy_configurateur', $config);

    // Log pour debug
    error_log("Frais installation sauvegardés pour index {$index}: " . print_r($config[$index]['fraisInstallation'], true));

    wp_send_json_success([
        'message' => 'Frais d\'installation mis à jour avec succès',
        'config' => $config[$index],
        'count' => count($config[$index]['fraisInstallation'])
    ]);
}
add_action('wp_ajax_soeasy_set_frais_installation', 'soeasy_set_frais_installation');
add_action('wp_ajax_nopriv_soeasy_set_frais_installation', 'soeasy_set_frais_installation');

function soeasy_set_config_part(){

    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_config_action');

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

add_action('wp_ajax_soeasy_set_config_part', 'soeasy_set_config_part');
add_action('wp_ajax_nopriv_soeasy_set_config_part', 'soeasy_set_config_part');



// ============================================================================
// CATÉGORIE 6 - PANIER ET COMMANDE (SÉCURISÉES) - CRITIQUES
// ============================================================================

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
function soeasy_ajouter_au_panier(){

    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_cart_action');
    
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

add_action('wp_ajax_soeasy_ajouter_au_panier', 'soeasy_ajouter_au_panier');
add_action('wp_ajax_nopriv_soeasy_ajouter_au_panier', 'soeasy_ajouter_au_panier');

/**
 * Ajout au panier pour configuration multi-adresses
 */
function soeasy_ajouter_au_panier_multi(){

    soeasy_verify_nonce($_POST['nonce'] ?? '', 'soeasy_cart_action');

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

add_action('wp_ajax_soeasy_ajouter_au_panier_multi', 'soeasy_ajouter_au_panier_multi');
add_action('wp_ajax_nopriv_soeasy_ajouter_au_panier_multi', 'soeasy_ajouter_au_panier_multi');


/**
 * FONCTION DE DEBUG TEMPORAIRE - À SUPPRIMER APRÈS
 */
function debug_variations_produit($product_id) {
    $product = wc_get_product($product_id);
    if (!$product || !$product->is_type('variable')) {
        return;
    }
    
    /** @var WC_Product_Variable $product */
    $variations = $product->get_available_variations('array');
    
    error_log("=== DEBUG VARIATIONS PRODUIT #{$product_id} ===");
    foreach ($variations as $var) {
        error_log("Variation #{$var['variation_id']}:");
        error_log("  - Attributes: " . print_r($var['attributes'], true));
        error_log("  - Prix: {$var['display_price']}");
    }
    error_log("=== FIN DEBUG ===");
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

    // === GESTION DES PRODUITS VARIABLES ===
    $variation_id = 0;
    $variation_attributes = array();
    
    // ✅ VÉRIFICATION : Le produit est-il variable ?
    if ($product->is_type('variable')) {
        
        // ✅ RÉCUPÉRATION UNIQUE des variations disponibles
        /** @var WC_Product_Variable $product */
        $available_variations = $product->get_available_variations('array');

        debug_variations_produit($product_id);
        
        if (empty($available_variations)) {
            error_log("SoEasy: ⚠️ Produit #{$product_id} est variable mais n'a aucune variation disponible");
            return [
                'success' => false,
                'error' => "Produit #{$product_id} : aucune variation disponible"
            ];
        }
        
        error_log("SoEasy: 🔍 Produit #{$product_id} - " . count($available_variations) . " variations disponibles");
        
        // PRIORITÉ 1 : Utiliser les attributs envoyés par le JS
        if (!empty($produit_data['attributes']) && is_array($produit_data['attributes'])) {
            $sent_attributes = $produit_data['attributes'];
            
            error_log("SoEasy: 🎯 Recherche avec attributes JS: " . print_r($sent_attributes, true));
            
            foreach ($available_variations as $variation_data) {
                $matches = true;
                
                // Vérifier si tous les attributs correspondent
                foreach ($sent_attributes as $attr_key => $attr_value) {
                    // Normaliser le nom de l'attribut (ajouter 'attribute_' si absent)
                    $full_attr_name = (strpos($attr_key, 'attribute_') === 0) ? $attr_key : 'attribute_' . $attr_key;
                    
                    if (isset($variation_data['attributes'][$full_attr_name])) {
                        $variation_attr_value = $variation_data['attributes'][$full_attr_name];
                        
                        // Comparaison flexible : valeur vide = "any" (tous les choix acceptés)
                        if ($variation_attr_value !== '' && $variation_attr_value != $attr_value) {
                            $matches = false;
                            error_log("SoEasy:   ❌ Mismatch sur {$full_attr_name}: attendu={$attr_value}, trouvé={$variation_attr_value}");
                            break;
                        }
                    }
                }
                
                if ($matches) {
                    $variation_id = $variation_data['variation_id'];
                    
                    // Construire les attributs pour WooCommerce
                    foreach ($sent_attributes as $attr_key => $attr_value) {
                        $full_attr_name = (strpos($attr_key, 'attribute_') === 0) ? $attr_key : 'attribute_' . $attr_key;
                        $variation_attributes[$full_attr_name] = $attr_value;
                    }
                    
                    error_log("SoEasy: ✅ Variation trouvée via JS attributes : #{$variation_id}");
                    break;
                }
            }
        } else {
            error_log("SoEasy: ⚠️ Aucun attributes JS fourni pour produit #{$product_id}");
        }
        
        // PRIORITÉ 2 : Fallback sur les paramètres globaux en session
        if (!$variation_id) {
            error_log("SoEasy: 🔄 Fallback sur paramètres session");
            
            $duree_engagement = soeasy_get_selected_duree_engagement() ?: '24';
            $mode_financement = soeasy_get_selected_financement() ?: 'comptant';
            
            error_log("SoEasy: Session - engagement={$duree_engagement}, financement={$mode_financement}");
            
            foreach ($available_variations as $variation_data) {
                $variation_attributes_to_test = array();
                
                foreach ($variation_data['attributes'] as $attr_name => $attr_value) {
                    $clean_attr_name = str_replace('attribute_', '', $attr_name);
                    
                    switch ($clean_attr_name) {
                        case 'pa_duree-engagement':
                        case 'pa_engagement':
                        case 'duree_engagement':
                            $variation_attributes_to_test[$attr_name] = $duree_engagement;
                            break;
                            
                        case 'pa_financement':
                        case 'pa_mode-financement':
                        case 'mode_financement':
                            $variation_attributes_to_test[$attr_name] = $mode_financement;
                            break;
                            
                        default:
                            if (!empty($attr_value)) {
                                $variation_attributes_to_test[$attr_name] = $attr_value;
                            }
                            break;
                    }
                }
                
                $matches = true;
                foreach ($variation_attributes_to_test as $attr_name => $attr_value) {
                    if (isset($variation_data['attributes'][$attr_name]) && 
                        $variation_data['attributes'][$attr_name] !== '' && 
                        $variation_data['attributes'][$attr_name] !== $attr_value) {
                        $matches = false;
                        break;
                    }
                }
                
                if ($matches) {
                    $variation_id = $variation_data['variation_id'];
                    $variation_attributes = $variation_attributes_to_test;
                    
                    // Récupérer le prix de la variation si pas de prix custom
                    if (empty($produit_data['prixUnitaire'])) {
                        $variation = wc_get_product($variation_id);
                        if ($variation) {
                            $variation_price = $variation->get_price();
                            if ($variation_price) {
                                $cart_item_data['soeasy_prix_custom'] = floatval($variation_price);
                            }
                        }
                    }
                    
                    error_log("SoEasy: ✅ Variation trouvée via session : #{$variation_id}");
                    break;
                }
            }
        }
        
        // PRIORITÉ 3 : Dernière chance - première variation disponible
        if (!$variation_id) {
            $first_variation = reset($available_variations);
            $variation_id = $first_variation['variation_id'];
            $variation_attributes = $first_variation['attributes'];
            
            error_log("SoEasy: ⚠️ Aucune variation exacte trouvée, utilisation de la première disponible #{$variation_id}");
        }
    }
    // Si produit simple, pas besoin de variation
    else {
        error_log("SoEasy: 📦 Produit #{$product_id} est un produit simple");
    }

    // === AJOUT AU PANIER ===
    try {
        $cart_item_key = WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            $variation_id,           // 0 pour produit simple
            $variation_attributes,   // vide pour produit simple
            $cart_item_data
        );

        if ($cart_item_key) {
            $log_msg = "SoEasy: ✅ Produit ajouté au panier - ID:{$product_id}";
            if ($variation_id) {
                $log_msg .= ", Variation:{$variation_id}";
            }
            $log_msg .= ", Quantité:{$quantity}";
            error_log($log_msg);
            
            return [
                'success' => true,
                'cart_item_key' => $cart_item_key,
                'variation_id' => $variation_id,
                'product_id' => $product_id
            ];
        } else {
            error_log("SoEasy: ❌ Échec ajout au panier - Produit #{$product_id}, Variation #{$variation_id}");
            return [
                'success' => false,
                'error' => "Impossible d'ajouter le produit #{$product_id} au panier"
            ];
        }

    } catch (Exception $e) {
        error_log("SoEasy: 💥 Exception lors de l'ajout au panier - Produit #{$product_id}: " . $e->getMessage());
        return [
            'success' => false,
            'error' => "Erreur technique : " . $e->getMessage()
        ];
    }
}

/**
 * Hook pour afficher les métadonnées SoEasy dans le panier
 */
add_filter('woocommerce_get_item_data', 'soeasy_display_cart_item_data', 10, 2);

function soeasy_display_cart_item_data($cart_item_data, $cart_item)
{

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

function soeasy_apply_custom_prices($cart)
{

    if (is_admin() && !defined('DOING_AJAX'))
        return;
    if (did_action('woocommerce_before_calculate_totals') >= 2)
        return;

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