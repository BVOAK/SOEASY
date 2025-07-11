<?php
/**
 * Fonctions utilitaires pour le configurateur SoEasy
 */

// 🔁 Récupérer les adresses stockées dans la session WooCommerce
function soeasy_get_adresses_configurateur()
{
    if (!WC()->session) {
        wp_send_json_error('Session WooCommerce non active');
        return [];
    }
    return soeasy_session_get('soeasy_config_adresses', []);
}

// 💾 Ajouter une adresse (appelée depuis AJAX)
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

// ✅ Récupère les services sélectionnés pour une adresse
function soeasy_get_selected_services($adresse_index)
{
    $adresses = soeasy_get_adresses_configurateur();
    return isset($adresses[$adresse_index]['services']) ? $adresses[$adresse_index]['services'] : [];
}

// 📦 Récupère la durée d’engagement sélectionnée (stockée en session)
function soeasy_get_selected_duree_engagement()
{
    if (!WC()->session)
        return null;
    return soeasy_session_get('soeasy_duree_engagement');
}

// 💳 Récupère le mode de financement choisi (comptant ou leasing)
function soeasy_get_selected_financement()
{
    if (!WC()->session)
        return 'comptant';
    return soeasy_session_get('soeasy_mode_financement', 'comptant');
}

// 📈 Récupère le prix leasing selon produit et durée
function soeasy_get_leasing_price($product_id, $duree)
{
    $field_name = 'prix_leasing_' . $duree;
    return get_field($field_name, $product_id);
}

// 🛒 Ajouter un produit avec meta personnalisée (ex: adresse)
function soeasy_add_product_to_cart($product_id, $quantity = 1, $meta = [])
{
    $cart_item_data = [];

    foreach ($meta as $key => $value) {
        $cart_item_data[$key] = $value;
    }

    WC()->cart->add_to_cart($product_id, $quantity, 0, [], $cart_item_data);
}

// 🔁 Action AJAX – Ajout d'une adresse
function ajax_soeasy_add_adresse_configurateur() {

    if (!WC()->session) {
        WC()->session; // Force l'initialisation de la session pour les visiteurs
    }

    $adresse = $_POST['adresse'] ?? '';
    $services = $_POST['services'] ?? [];

    if (!$adresse || empty($services)) {
        wp_send_json_error('Adresse ou services manquants');
    }

    soeasy_add_adresse_configurateur($adresse, $services);
    $updated = soeasy_get_adresses_configurateur(); // ← On récupère les adresses mises à jour

    ob_start();
    foreach ($updated as $i => $adr) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<span>' . esc_html($adr['adresse']) . ' — <em>Services : ' . implode(', ', $adr['services']) . '</em></span>';
        echo '<button class="btn btn-sm btn-outline-danger btn-remove-adresse" data-index="' . $i . '">Supprimer</button>';
        echo '</li>';
    }
    $html = ob_get_clean();

    wp_send_json_success(['html' => $html]);
}


// Enregistrement des hooks AJAX
add_action('wp_ajax_soeasy_add_adresse_configurateur', 'ajax_soeasy_add_adresse_configurateur');
add_action('wp_ajax_nopriv_soeasy_add_adresse_configurateur', 'ajax_soeasy_add_adresse_configurateur');


// 🔁 Action AJAX – Suppression d'une adresse
function ajax_soeasy_remove_adresse_configurateur()
{
    $index = intval($_POST['index'] ?? -1);
    $adresses = soeasy_get_adresses_configurateur();

    if ($index >= 0 && isset($adresses[$index])) {
        unset($adresses[$index]);
        $adresses = array_values($adresses); // Réindexation
        soeasy_session_set('soeasy_config_adresses', $adresses);
        wp_send_json_success($adresses);
    }

    wp_send_json_error('Adresse non trouvée');
}
add_action('wp_ajax_soeasy_remove_adresse_configurateur', 'ajax_soeasy_remove_adresse_configurateur');
add_action('wp_ajax_nopriv_soeasy_remove_adresse_configurateur', 'ajax_soeasy_remove_adresse_configurateur');


add_action('wp_ajax_soeasy_set_engagement', function () {
    if (isset($_POST['duree'])) {
        soeasy_session_set('soeasy_duree_engagement', intval($_POST['duree']));
    }
    wp_die();
});

add_action('wp_ajax_soeasy_set_financement', function () {
    if (isset($_POST['mode'])) {
        WC()->session->set('soeasy_mode_financement', sanitize_text_field($_POST['mode']));
    }
    wp_die();
});
add_action('wp_ajax_nopriv_soeasy_set_financement', function () {
    if (isset($_POST['mode'])) {
        soeasy_session_set('soeasy_mode_financement', sanitize_text_field($_POST['mode']));
    }
    wp_die();
});

/**
 * 🔐 Initialise la session WooCommerce si elle n'existe pas
 */
function soeasy_start_session_if_needed() {
    if (!class_exists('WooCommerce')) return;

    if (!WC()->session || !WC()->session->has_session()) {
        WC()->session = WC()->session ?: WC_Session_Handler::instance();
        WC()->session->init();

        // Forcer le cookie si nécessaire
        WC()->session->set_customer_session_cookie(true);
    }
}


/**
 * 💾 Enregistre une donnée en session WooCommerce (clé, valeur)
 */
function soeasy_session_set($key, $value) {
    soeasy_start_session_if_needed();
    if (WC()->session) {
        WC()->session->set($key, $value);
    } else {
        error_log("❌ WC()->session est NULL – impossible de set $key");
    }
}

/**
 * 📦 Récupère une donnée de session avec valeur par défaut
 */
function soeasy_session_get($key, $default = null) {
    soeasy_start_session_if_needed();
    return WC()->session ? WC()->session->get($key, $default) : $default;
}

/**
 * 🧹 Supprime une donnée de session
 */
function soeasy_session_delete($key) {
    soeasy_start_session_if_needed();
    if (WC()->session) {
        WC()->session->__unset($key);
    }
}


/*** Etape 2 - Forfait Internet ***/
add_action('wp_ajax_soeasy_set_forfait_internet', 'soeasy_ajax_set_forfait_internet');
add_action('wp_ajax_nopriv_soeasy_set_forfait_internet', 'soeasy_ajax_set_forfait_internet');

function soeasy_ajax_set_forfait_internet() {
    $index = intval($_POST['index'] ?? -1);
    $product_id = intval($_POST['product_id'] ?? 0);

    if ($index < 0 || $product_id <= 0) {
        wp_send_json_error('Paramètres invalides');
    }

    $forfaits = soeasy_session_get('soeasy_config_forfaits_internet', []);
    $forfaits[$index] = $product_id;
    soeasy_session_set('soeasy_config_forfaits_internet', $forfaits);

    wp_send_json_success('Forfait enregistré');
}

/*** Etape 2 - Equipements Internet ***/
add_action('wp_ajax_soeasy_set_equipements_internet', 'soeasy_ajax_set_equipements_internet');
add_action('wp_ajax_nopriv_soeasy_set_equipements_internet', 'soeasy_ajax_set_equipements_internet');

function soeasy_ajax_set_equipements_internet() {
    $index = intval($_POST['index'] ?? -1);
    $product_ids = $_POST['product_ids'] ?? [];

    if ($index < 0 || !is_array($product_ids)) {
        wp_send_json_error('Paramètres invalides');
    }

    $product_ids = array_map('intval', $product_ids);
    $equipements = soeasy_session_get('soeasy_config_equipements_internet', []);
    $equipements[$index] = $product_ids;
    soeasy_session_set('soeasy_config_equipements_internet', $equipements);

    wp_send_json_success('Équipements enregistrés');
}

/*** Etape 3 - Forfaits mobiles ***/
add_action('wp_ajax_soeasy_set_forfaits_mobile', 'ajax_soeasy_set_forfaits_mobile');
add_action('wp_ajax_nopriv_soeasy_set_forfaits_mobile', 'ajax_soeasy_set_forfaits_mobile');

function ajax_soeasy_set_forfaits_mobile() {
    $index = intval($_POST['index'] ?? -1);
    $items = $_POST['items'] ?? [];

    if ($index < 0 || !is_array($items)) {
        wp_send_json_error('Paramètres invalides');
    }

    $forfaits = soeasy_session_get('soeasy_forfaits_mobile', []);
    $forfaits[$index] = array_map(function($item) {
        return [
            'id' => intval($item['id']),
            'qty' => intval($item['qty'])
        ];
    }, $items);

    soeasy_session_set('soeasy_forfaits_mobile', $forfaits);
    wp_send_json_success('Forfaits mobile enregistrés');
}


/*** Etape 3 - Forfaits DATA ***/
add_action('wp_ajax_soeasy_set_forfaits_data', 'ajax_soeasy_set_forfaits_data');
add_action('wp_ajax_nopriv_soeasy_set_forfaits_data', 'ajax_soeasy_set_forfaits_data');

function ajax_soeasy_set_forfaits_data() {
    $index = intval($_POST['index'] ?? -1);
    $items = $_POST['items'] ?? [];

    if ($index < 0 || !is_array($items)) {
        wp_send_json_error('Paramètres invalides');
    }

    $forfaits = soeasy_session_get('soeasy_forfaits_data', []);
    $forfaits[$index] = array_map(function($item) {
        return [
            'id' => intval($item['id']),
            'qty' => intval($item['qty'])
        ];
    }, $items);

    soeasy_session_set('soeasy_forfaits_data', $forfaits);
    wp_send_json_success('Forfaits data enregistrés');
}


/*** Etape 3 - Equipements mobiles ***/
add_action('wp_ajax_soeasy_set_equipements_mobile', 'ajax_soeasy_set_equipements_mobile');
add_action('wp_ajax_nopriv_soeasy_set_equipements_mobile', 'ajax_soeasy_set_equipements_mobile');

function ajax_soeasy_set_equipements_mobile() {
    $index = intval($_POST['index'] ?? -1);
    $items = $_POST['items'] ?? [];

    if ($index < 0 || !is_array($items)) {
        wp_send_json_error('Paramètres invalides');
    }

    $equipements = soeasy_session_get('soeasy_equipements_mobile', []);
    $equipements[$index] = array_map(function($item) {
        return [
            'id' => intval($item['id']),
            'qty' => intval($item['qty'])
        ];
    }, $items);

    soeasy_session_set('soeasy_equipements_mobile', $equipements);
    wp_send_json_success('Équipements mobile enregistrés');
}

add_action('wp_ajax_soeasy_set_licence_centrex', function () {
    soeasy_save_centrex('soeasy_licences_centrex');
});
add_action('wp_ajax_soeasy_set_service_centrex', function () {
    soeasy_save_centrex('soeasy_services_centrex');
});
add_action('wp_ajax_soeasy_set_poste_centrex', function () {
    soeasy_save_centrex('soeasy_postes_centrex');
});
add_action('wp_ajax_soeasy_set_switch_centrex', function () {
    soeasy_save_centrex('soeasy_switchs_centrex');
});
add_action('wp_ajax_soeasy_set_accessoire_centrex', function () {
    soeasy_save_centrex('soeasy_accessoires_centrex');
});

add_action('wp_ajax_nopriv_soeasy_set_licence_centrex', function () {
    soeasy_save_centrex('soeasy_licences_centrex');
});
add_action('wp_ajax_nopriv_soeasy_set_service_centrex', function () {
    soeasy_save_centrex('soeasy_services_centrex');
});
add_action('wp_ajax_nopriv_soeasy_set_poste_centrex', function () {
    soeasy_save_centrex('soeasy_postes_centrex');
});
add_action('wp_ajax_nopriv_soeasy_set_switch_centrex', function () {
    soeasy_save_centrex('soeasy_switchs_centrex');
});
add_action('wp_ajax_nopriv_soeasy_set_accessoire_centrex', function () {
    soeasy_save_centrex('soeasy_accessoires_centrex');
});

function soeasy_save_centrex($session_key) {
    $index = intval($_POST['index'] ?? -1);
    $data = $_POST['data'] ?? [];

    if ($index < 0 || !is_array($data)) {
        wp_send_json_error('Paramètres invalides');
    }

    $data = array_map(function ($item) {
        return [
            'id' => intval($item['id']),
            'qty' => intval($item['qty'])
        ];
    }, $data);

    $session_data = soeasy_session_get($session_key, []);
    $session_data[$index] = $data;
    soeasy_session_set($session_key, $session_data);

    wp_send_json_success('Données enregistrées');
}



?>