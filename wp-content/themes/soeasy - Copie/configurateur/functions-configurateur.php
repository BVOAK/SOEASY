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
    if (!$adresse || empty($services))
        wp_send_json_error('Adresse ou services manquants');
    $updated = soeasy_add_adresse_configurateur($adresse, $services);

    ob_start();
    foreach ($updated as $i => $adr) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<span>' . esc_html($adr['adresse']) . ' — <em>Services : ' . implode(', ', $adr['services']) . '</em></span>';
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
add_action('wp_ajax_soeasy_set_forfait_internet', fn() => soeasy_session_set('soeasy_forfaits_internet', array_replace(soeasy_session_get('soeasy_config_forfaits_internet', []), [intval($_POST['index'] ?? -1) => intval($_POST['product_id'] ?? 0)])));
add_action('wp_ajax_nopriv_soeasy_set_forfait_internet', fn() => soeasy_session_set('soeasy_forfaits_internet', array_replace(soeasy_session_get('soeasy_config_forfaits_internet', []), [intval($_POST['index'] ?? -1) => intval($_POST['product_id'] ?? 0)])));
add_action('wp_ajax_soeasy_set_equipements_internet', fn() => soeasy_session_set('soeasy_equipements_internet', array_replace(soeasy_session_get('soeasy_config_equipements_internet', []), [intval($_POST['index'] ?? -1) => array_map('intval', $_POST['product_ids'] ?? [])])));
add_action('wp_ajax_nopriv_soeasy_set_equipements_internet', fn() => soeasy_session_set('soeasy_equipements_internet', array_replace(soeasy_session_get('soeasy_config_equipements_internet', []), [intval($_POST['index'] ?? -1) => array_map('intval', $_POST['product_ids'] ?? [])])));

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
}

?>