<?php
/**
 * Étape 5 – Frais d'installation
 */
if (!function_exists('get_template_directory')) {
  require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}
require_once get_template_directory() . '/configurateur/functions-configurateur.php';

$config = WC()->session->get('soeasy_configurateur');
$duree = soeasy_get_selected_duree_engagement();
$mode = soeasy_get_selected_financement();
$adresses = soeasy_get_adresses_configurateur();

?>

<div class="config-step step-5 container py-4">
  <h2 class="mb-4">5. Frais d'installation</h2>

  <?php if (!empty($config)): ?>
    <?php foreach ($config as $i => $data): ?>
      <?php

      $frais_lignes = $data['fraisInstallation'] ?? [];
      $frais_groupes = [];

      foreach ($frais_lignes as $frais) {
        $id = $frais['id'];
        $nom_frais = strtolower($frais['nom']);
        $isCentrexUnique = str_contains($nom_frais, 'installation centrex');

        if (!isset($frais_groupes[$id])) {
          $frais_groupes[$id] = $frais;
          $frais_groupes[$id]['quantite'] = intval($frais['quantite']) ?: 1;
        } else {
          if (!$isCentrexUnique) {
            $frais_groupes[$id]['quantite'] += intval($frais['quantite']) ?: 1;
          }
        }
      }
      ?>

      <div class="border p-4 rounded shadow-sm mb-4">
        <h5 class="mb-3">Adresse : <?= esc_html($adresses[$i]['adresse'] ?? ('Adresse #' . ($i + 1))); ?></h5>

        <ul class="list-group frais-installation-list" data-index="<?= $i; ?>">
          <?php foreach ($frais_groupes as $frais): ?>
            <?php
            $prix_affiche = floatval($frais['prixComptant']);

            if ($mode === 'leasing') {
              $duree_int = intval($duree);
              $key = 'prix_leasing_' . $duree_int;
              if (!empty($frais[$key])) {
                $prix_affiche = floatval($frais[$key]);
              }
            }
            $prix_total = $prix_affiche * $frais['quantite'];
            ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <label class="form-check-label flex-grow-1">
                <input type="checkbox" class="form-check-input me-2 frais-checkbox" data-id="<?= esc_attr($frais['id']); ?>"
                  data-index="<?= esc_attr($i); ?>" data-quantite="<?= esc_attr($frais['quantite']); ?>"
                  data-prix-comptant="<?= esc_attr($frais['prix_comptant']); ?>"
                  data-prix-leasing-24="<?= esc_attr($frais['prixLeasing24'] ?? 0); ?>"
                  data-prix-leasing-36="<?= esc_attr($frais['prixLeasing36'] ?? 0); ?>"
                  data-prix-leasing-48="<?= esc_attr($frais['prixLeasing48'] ?? 0); ?>"
                  data-prix-leasing-63="<?= esc_attr($frais['prixLeasing48'] ?? 0); ?>" checked>
                <?= esc_html($frais['nom']); ?>
                <?php
                  $nom_frais = strtolower($frais['nom']);
                  $isCentrexUnique = str_contains($nom_frais, 'installation centrex');
                ?>
                <?php if ($frais['quantite'] > 1 && !$isCentrexUnique): ?>
                  <small class="text-muted">(×<?= $frais['quantite']; ?>)</small>
                <?php endif; ?>
                <span class="fw-bold float-end"><?= number_format($prix_total, 2, ',', ' ') ?> €</span>
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

        <?php
        $total_adresse = 0;
        foreach ($frais_groupes as $frais) {
          $prix_unitaire = floatval($frais['prix_comptant']);
          if ($mode === 'leasing') {
            $duree_int = intval($duree);
            $key = 'prix_leasing_' . $duree_int;
            if (!empty($frais[$key])) {
              $prix_unitaire = floatval($frais[$key]);
            }
          }
          $total_adresse += $prix_unitaire * intval($frais['quantite']);
        }
        ?>

        <div class="mt-3 text-end fw-bold">
          Total : <span class="frais-total" data-index="<?= $i; ?>">
            <?php
            $prix_str = number_format($total_adresse, 2, ',', ' ') . ' €';
            if ($mode === 'leasing') {
              $prix_str .= ' / mois';
            }
            ?>
            <span class="fw-bold"><?= $prix_str; ?></span>
          </span>
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
