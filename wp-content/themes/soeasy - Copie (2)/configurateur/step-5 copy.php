<?php
/**
 * Étape 5 – Frais d'installation dynamiques (refonte allégée)
 */
if (!function_exists('get_template_directory')) {
  require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}
require_once get_template_directory() . '/configurateur/functions-configurateur.php';

$config = WC()->session->get('soeasy_configurateur');
$adresses = soeasy_get_adresses_configurateur();
$duree = soeasy_get_selected_duree_engagement();
$mode = soeasy_get_selected_financement();
?>

<div class="config-step step-5 container py-4">
  <h2 class="mb-4">5. Frais d'installation</h2>

  <?php if (!empty($config)): ?>
    <?php foreach ($config as $i => $data): ?>
      <?php
      $abos = $data['abonnements'] ?? [];
      $mats = $data['materiels'] ?? [];
      $produits = array_merge($abos, $mats);
      $fraisUtilises = [];
      $fraisIdsAffiches = [];

      foreach ($produits as $prod) {
        $fraisAssocies = get_field('frais_installation_associes', $prod['id']) ?: [];
        foreach ($fraisAssocies as $frais) {
          if (!in_array($frais->ID, $fraisIdsAffiches)) {
            $fraisIdsAffiches[] = $frais->ID;
            $fraisUtilises[] = [
              'id' => $frais->ID,
              'nom' => get_the_title($frais->ID),
              'prixComptant' => floatval(get_field('prix_comptant', $frais->ID)) ?: 0,
              'prixLeasing24' => floatval(get_field('prix_leasing_24', $frais->ID)) ?: 0,
              'prixLeasing36' => floatval(get_field('prix_leasing_36', $frais->ID)) ?: 0,
              'prixLeasing48' => floatval(get_field('prix_leasing_48', $frais->ID)) ?: 0,
              'prixLeasing63' => floatval(get_field('prix_leasing_63', $frais->ID)) ?: 0,
              'quantite' => 1
            ];
          }
        }
      }
      ?>

      <div class="border p-4 rounded shadow-sm mb-4">
        <h5 class="mb-3">Adresse : <?= esc_html($adresses[$i]['adresse'] ?? ('Adresse #' . ($i + 1))); ?></h5>

        <ul class="list-group frais-installation-list" data-index="<?= $i; ?>">
          <?php foreach ($fraisUtilises as $frais): ?>
            <li class="list-group-item">
              <label class="form-check-label">
                <input type="checkbox" class="form-check-input frais-checkbox"
                  data-id="<?= $frais['id']; ?>"
                  data-index="<?= $i; ?>"
                  data-quantite="<?= $frais['quantite']; ?>"
                  data-prix-comptant="<?= $frais['prixComptant']; ?>"
                  data-prix-leasing-24="<?= $frais['prixLeasing24']; ?>"
                  data-prix-leasing-36="<?= $frais['prixLeasing36']; ?>"
                  data-prix-leasing-48="<?= $frais['prixLeasing48']; ?>"
                  data-prix-leasing-63="<?= $frais['prixLeasing63']; ?>"
                >
                <?= esc_html($frais['nom']); ?>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="form-check mt-3">
          <input type="checkbox" class="form-check-input report-frais-checkbox" id="report_frais_<?= $i; ?>" data-index="<?= $i; ?>">
          <label class="form-check-label" for="report_frais_<?= $i; ?>">
            Je préfère voir cela plus tard avec le service technique SoEasy
          </label>
        </div>

        <div class="mt-3 text-end fw-bold">
          Total estimé : <span class="frais-total" data-index="<?= $i; ?>">0 €</span>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-between mt-4">
      <button class="btn btn-outline-secondary btn-precedent" data-step="4">← Étape précédente</button>
      <button class="btn btn-primary btn-suivant" data-step="6">Étape suivante →</button>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">Aucune configuration trouvée.</div>
  <?php endif; ?>
</div>
