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
      $abos = $data['abonnements'] ?? [];
      $mats = $data['materiels'] ?? [];

      $nb_licences = 0;
      $nb_postes = 0;
      $ids_frais_deja_affiches = [];
      $frais_lignes = [];

      // Analyse des produits
      foreach ($abos as $abo) {
        if (($abo['type'] ?? '') === 'forfait-centrex') {
          $nb_licences += intval($abo['quantite'] ?? 0);
        }
      }

      foreach ($mats as $mat) {
        if (($mat['type'] ?? '') === 'poste-centrex') {
          $nb_postes += intval($mat['quantite'] ?? 0);
        }
      }

      // Préparer tous les frais depuis les produits
      $produits = array_merge($abos, $mats);
      foreach ($produits as $prod) {
        $prod_id = $prod['id'] ?? null;
        if (!$prod_id)
          continue;
        $frais_associes = get_field('frais_installation_associes', $prod_id);
        if (!$frais_associes) continue;

        foreach ($frais_associes as $frais_id) {
          $prixComptant = get_post_meta($frais_id, '_regular_price', true);
          $frais_quantite = $prod['quantite'] ?? 1;

          if ($prod['type'] === 'poste-centrex') {
              $frais_par_poste = [];

              foreach ($frais_associes as $frais_id) {
                $seuil = intval(get_field('max_postes', $frais_id)) ?: PHP_INT_MAX;
                $frais_par_poste[] = [
                  'seuil' => $seuil,
                  'ligne' => [
                    'id' => $frais_id,
                    'nom' => get_the_title($frais_id),
                    'quantite' => 1,
                    'prix_comptant' => get_post_meta($frais_id, '_regular_price', true),
                    'prix_leasing_24' => floatval(get_field('prix_leasing_24', $frais_id)) ?: 0,
                    'prix_leasing_36' => floatval(get_field('prix_leasing_36', $frais_id)) ?: 0,
                    'prix_leasing_48' => floatval(get_field('prix_leasing_48', $frais_id)) ?: 0,
                    'prix_leasing_63' => floatval(get_field('prix_leasing_63', $frais_id)) ?: 0,
                  ]
                ];
              }

              // Trier par seuil croissant
              usort($frais_par_poste, function ($a, $b) {
                return $a['seuil'] - $b['seuil'];
              });

              // Ajouter le 1er frais correspondant au nombre de postes
              foreach ($frais_par_poste as $frais) {
                if ($nb_postes <= $frais['seuil']) {
                  $frais_lignes[] = $frais['ligne'];
                  break;
                }
              }

            } if ($prod['type'] === 'forfait-centrex') {
            $seuil = intval(get_field('offert_a_partir_de_licences', $frais_id)) ?: 0;
            if ($nb_licences < $seuil && !in_array($frais_id, $ids_frais_deja_affiches)) {
              $ids_frais_deja_affiches[] = $frais_id;
              $isGratuit = $nb_licences >= $seuil;
              $prixComptant = $isGratuit ? 0 : get_post_meta($frais_id, '_regular_price', true);
              $frais_lignes[] = [
                'id' => $frais_id,
                'nom' => get_the_title($frais_id),
                'quantite' => 1,
                'prix_comptant' => $prixComptant,
                'prix_leasing_24' => floatval(get_field('prix_leasing_24', $frais_id)) ?: 0,
                'prix_leasing_36' => floatval(get_field('prix_leasing_36', $frais_id)) ?: 0,
                'prix_leasing_48' => floatval(get_field('prix_leasing_48', $frais_id)) ?: 0,
                'prix_leasing_63' => floatval(get_field('prix_leasing_63', $frais_id)) ?: 0,
              ];
            }

          } else {
            $frais_lignes[] = [
              'id' => $frais_id,
              'nom' => get_the_title($frais_id),
              'quantite' => $frais_quantite,
              'prix_comptant' => get_post_meta($frais_id, '_regular_price', true),
              'prix_leasing_24' => floatval(get_field('prix_leasing_24', $frais_id)) ?: 0,
              'prix_leasing_36' => floatval(get_field('prix_leasing_36', $frais_id)) ?: 0,
              'prix_leasing_48' => floatval(get_field('prix_leasing_48', $frais_id)) ?: 0,
              'prix_leasing_63' => floatval(get_field('prix_leasing_63', $frais_id)) ?: 0,
            ];
          }
        }
      }

      ?>

      <div class="border p-4 rounded shadow-sm mb-4">
        <h5 class="mb-3">Adresse : <?= esc_html($adresses[$i]['adresse'] ?? ('Adresse #' . ($i + 1))); ?></h5>

        <?php
        $frais_groupes = [];
        foreach ($frais_lignes as $frais) {
          $id = $frais['id'];
          $nom_frais = strtolower($frais['nom']);
          $isCentrexUnique = str_contains($nom_frais, 'installation centrex');

          if (!isset($frais_groupes[$id])) {
            $frais_groupes[$id] = $frais;
            $frais_groupes[$id]['quantite'] = intval($frais['quantite']) ?: 1;
          } else {
            // On évite de cumuler la quantité pour les frais "Installation Centrex"
            if (!$isCentrexUnique) {
              $frais_groupes[$id]['quantite'] += intval($frais['quantite']) ?: 1;
            }
          }
        }
        ?>

        <ul class="list-group frais-installation-list" data-index="<?= $i; ?>">
          <?php foreach ($frais_groupes as $frais): ?>
            <?php
            $prix_affiche = floatval($frais['prix_comptant']);
            if ($mode === 'leasing') {
              $duree_int = intval($duree);
              $key = 'prix_leasing_' . $duree_int;
              if (isset($frais[$key]) && is_numeric($frais[$key])) {
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
                  data-prix-leasing-24="<?= esc_attr($frais['prix_leasing_24'] ?? 0); ?>"
                  data-prix-leasing-36="<?= esc_attr($frais['prix_leasing_36'] ?? 0); ?>"
                  data-prix-leasing-48="<?= esc_attr($frais['prix_leasing_48'] ?? 0); ?>"
                  data-prix-leasing-63="<?= esc_attr($frais['prix_leasing_63'] ?? 0); ?>" checked>
                <?= esc_html($frais['nom']); ?>
                <?php
                  $nom_frais = strtolower($frais['nom']);
                  $isCentrexUnique = str_contains($nom_frais, 'installation centrex');
                ?>
                <?php if ($frais['quantite'] > 1 && !$isCentrexUnique): ?>
                  <small class="text-muted">(×<?= $frais['quantite']; ?>)</small>
                <?php endif; ?>
              </label>
              <span class="fw-bold"><?= number_format($prix_total, 2, ',', ' ') ?> €</span>
            </li>
          <?php endforeach; ?>
        </ul>


        <div class="form-check mt-3">
          <input type="checkbox" class="form-check-input report-frais-checkbox" id="report_frais_<?= $i; ?>"
            data-index="<?= $i; ?>">
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
            $prix_str = number_format($prix_total, 2, ',', ' ') . ' €';
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