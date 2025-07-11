<?php

/**
 * Étape 4 – Configuration Téléphonie Centrex
 */


if (!function_exists('get_template_directory')) {
  require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}
require_once get_template_directory() . '/configurateur/functions-configurateur.php';

$adresses = soeasy_get_adresses_configurateur();
$duree = soeasy_get_selected_duree_engagement();
$mode = soeasy_get_selected_financement();
?>

<div class="config-step step-4 container py-4">
  <h2 class="mb-4">4. Téléphonie Fixe Centrex</h2>

  <?php if (!empty($adresses)): ?>
    <ul class="nav nav-tabs mb-3" id="adresseTabs" role="tablist">
      <?php foreach ($adresses as $i => $adresse): ?>
        <li class="nav-item" role="presentation">
          <button class="nav-link <?php echo $i === 0 ? 'active' : ''; ?>" id="tab-<?php echo $i; ?>" data-bs-toggle="tab"
            data-bs-target="#content-<?php echo $i; ?>" type="button"><?php echo esc_html($adresse['adresse']); ?></button>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="tab-content" id="adresseTabsContent">
      <?php foreach ($adresses as $i => $adresse): ?>
        <div class="tab-pane fade <?php echo $i === 0 ? 'show active' : ''; ?>" id="content-<?php echo $i; ?>"
          role="tabpanel">
          <?php $total_licences = 0; ?>

          <!-- LICENCES -->
          <div class="mb-4">
            <h5>Licences Centrex</h5>
            <div class="row gy-3">
              <?php
              $licences = new WP_Query([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'product_cat' => 'telephonie-fixe-centrex'
              ]);
              while ($licences->have_posts()):
                $licences->the_post();
                $product = wc_get_product(get_the_ID());
                $product_id = $product->get_id();
                $prix = $product->get_price();
                ?>
                <div class="col-md-6">
                  <label class="border p-3 d-block rounded shadow-sm h-100">
                    <input type="checkbox" name="licences_<?php echo $i; ?>[]" value="<?php echo $product_id; ?>"
                      class="me-2 licence-checkbox">
                    <strong><?php the_title(); ?></strong><br>
                    <span class="text-muted"><?php echo get_the_excerpt(); ?></span>
                    <div class="d-flex align-items-center mt-2">
                      <label class="me-2 mb-0">Quantité</label>
                      <input type="number" min="0" name="quantite_licence_<?php echo $product_id; ?>_<?php echo $i; ?>"
                        class="form-control form-control-sm w-auto" value="0">
                    </div>
                    <div class="fw-bold mt-2"><?php echo wc_price($prix); ?> / mois</div>
                  </label>
                </div>
              <?php endwhile;
              wp_reset_postdata(); ?>
            </div>
          </div>

          <!-- SERVICES COMPLÉMENTAIRES -->
          <div class="mb-4">
            <h5>Services Complémentaires</h5>
            <div class="row gy-3">
              <?php
              $services = new WP_Query([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'product_cat' => 'services-centrex'
              ]);
              while ($services->have_posts()):
                $services->the_post();
                $product = wc_get_product(get_the_ID());
                $product_id = $product->get_id();
                $prix = $product->get_price();
                ?>
                <div class="col-md-6">
                  <label class="border p-3 d-block rounded shadow-sm h-100">
                    <input type="checkbox" name="services_<?php echo $i; ?>[]" value="<?php echo $product_id; ?>"
                      class="me-2">
                    <strong><?php the_title(); ?></strong><br>
                    <span class="text-muted"><?php echo get_the_excerpt(); ?></span>
                    <div class="d-flex align-items-center mt-2">
                      <label class="me-2 mb-0">Quantité</label>
                      <input type="number" min="0" name="quantite_service_<?php echo $product_id; ?>_<?php echo $i; ?>"
                        class="form-control form-control-sm w-auto" value="0">
                    </div>
                    <div class="fw-bold mt-2"><?php echo wc_price($prix); ?> / mois</div>
                  </label>
                </div>
              <?php endwhile;
              wp_reset_postdata(); ?>
            </div>
          </div>

          <!-- POSTES TÉLÉPHONIQUES -->
          <div class="mb-4">
            <h5>Postes Téléphoniques</h5>
            <div class="row gy-3">
              <?php
              $postes = new WP_Query([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'product_cat' => 'telephonie-fixe-centrex-equipements-telecom'
              ]);
              while ($postes->have_posts()):
                $postes->the_post();
                $product = wc_get_product(get_the_ID());
                $product_id = $product->get_id();
                $prix = ($mode === 'leasing' && $duree > 0) ? get_field('prix_leasing_' . $duree, $product_id) : $product->get_regular_price();
                ?>
                <div class="col-md-6">
                  <label class="border p-3 d-block rounded shadow-sm h-100">
                    <input type="checkbox" name="postes_<?php echo $i; ?>[]" value="<?php echo $product_id; ?>" class="me-2">
                    <strong><?php the_title(); ?></strong><br>
                    <span class="text-muted"><?php echo get_the_excerpt(); ?></span>
                    <div class="d-flex align-items-center mt-2">
                      <label class="me-2 mb-0">Qté</label>
                      <input type="number" min="0" name="quantite_poste_<?php echo $product_id; ?>_<?php echo $i; ?>"
                        class="form-control form-control-sm w-auto" value="0">
                    </div>
                    <div class="fw-bold mt-2">
                      <?php echo wc_price($prix); ?>       <?php echo ($mode === 'leasing' && $duree > 0) ? ' / mois' : ''; ?>
                    </div>
                  </label>
                </div>
              <?php endwhile;
              wp_reset_postdata(); ?>
            </div>
          </div>

          <!-- SWITCHS ET BORNES -->
          <div class="mb-4">
            <h5>Switchs & Bornes Réseau</h5>
            <div class="row gy-3">
              <?php
              $reseaux = new WP_Query([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'product_cat' => 'reseau-switchs'
              ]);
              while ($reseaux->have_posts()):
                $reseaux->the_post();
                $product = wc_get_product(get_the_ID());
                $product_id = $product->get_id();
                $prix = ($mode === 'leasing' && $duree > 0) ? get_field('prix_leasing_' . $duree, $product_id) : $product->get_regular_price();
                ?>
                <div class="col-md-6">
                  <label class="border p-3 d-block rounded shadow-sm h-100">
                    <input type="checkbox" name="reseaux_<?php echo $i; ?>[]" value="<?php echo $product_id; ?>" class="me-2">
                    <strong><?php the_title(); ?></strong><br>
                    <span class="text-muted"><?php echo get_the_excerpt(); ?></span>
                    <div class="d-flex align-items-center mt-2">
                      <label class="me-2 mb-0">Qté</label>
                      <input type="number" min="0" name="quantite_reseau_<?php echo $product_id; ?>_<?php echo $i; ?>"
                        class="form-control form-control-sm w-auto" value="0">
                    </div>
                    <div class="fw-bold mt-2">
                      <?php echo wc_price($prix); ?>       <?php echo ($mode === 'leasing' && $duree > 0) ? ' / mois' : ''; ?>
                    </div>
                  </label>
                </div>
              <?php endwhile;
              wp_reset_postdata(); ?>
            </div>
          </div>

          <!-- ACCESSOIRES -->
          <div class="mb-4">
            <h5>Accessoires Télécoms</h5>
            <div class="row gy-3">
              <?php
              $accessoires = new WP_Query([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'product_cat' => 'accessoires-telecoms'
              ]);
              while ($accessoires->have_posts()):
                $accessoires->the_post();
                $product = wc_get_product(get_the_ID());
                $product_id = $product->get_id();
                $prix = ($mode === 'leasing' && $duree > 0) ? get_field('prix_leasing_' . $duree, $product_id) : $product->get_regular_price();
                ?>
                <div class="col-md-6">
                  <label class="border p-3 d-block rounded shadow-sm h-100">
                    <input type="checkbox" name="accessoires_<?php echo $i; ?>[]" value="<?php echo $product_id; ?>"
                      class="me-2">
                    <strong><?php the_title(); ?></strong><br>
                    <span class="text-muted"><?php echo get_the_excerpt(); ?></span>
                    <div class="d-flex align-items-center mt-2">
                      <label class="me-2 mb-0">Qté</label>
                      <input type="number" min="0" name="quantite_accessoire_<?php echo $product_id; ?>_<?php echo $i; ?>"
                        class="form-control form-control-sm w-auto" value="0">
                    </div>
                    <div class="fw-bold mt-2">
                      <?php echo wc_price($prix); ?>       <?php echo ($mode === 'leasing' && $duree > 0) ? ' / mois' : ''; ?>
                    </div>
                  </label>
                </div>
              <?php endwhile;
              wp_reset_postdata(); ?>
            </div>
          </div>

          <!-- FORFAITS INSTALLATION PAR NOMBRE DE POSTES -->
          <div class="mb-4">
            <h5>Installation Centrex</h5>
            <p>Choisissez le forfait d'installation selon le nombre total de postes</p>
            <div class="row gy-3">
              <?php
              $installations = new WP_Query([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'product_cat' => 'installation-centrex'
              ]);
              while ($installations->have_posts()):
                $installations->the_post();
                $product = wc_get_product(get_the_ID());
                $product_id = $product->get_id();
                $prix = ($mode === 'leasing' && $duree > 0) ? get_field('prix_leasing_' . $duree, $product_id) : $product->get_regular_price();
                ?>
                <div class="col-md-6">
                  <label class="border p-3 d-block rounded shadow-sm h-100">
                    <input type="radio" name="installation_<?php echo $i; ?>" value="<?php echo $product_id; ?>" class="me-2">
                    <strong><?php the_title(); ?></strong><br>
                    <span class="text-muted"><?php echo get_the_excerpt(); ?></span>
                    <div class="fw-bold mt-2">
                      <?php echo wc_price($prix); ?>       <?php echo ($mode === 'leasing' && $duree > 0) ? ' / mois' : ''; ?>
                    </div>
                  </label>
                </div>
              <?php endwhile;
              wp_reset_postdata(); ?>
            </div>
          </div>

          <!-- FRAIS DE MISE EN SERVICE CENTREX -->
          <div class="mb-4 border-top pt-3">
            <h5>Frais de mise en service</h5>
            <p class="frais-centrex-message">Des frais de 20 € s’appliquent par licence (moins de 20
              utilisateurs).</p>
          </div>

        </div> <!-- fin tab-pane -->
      <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button class="btn btn-outline-secondary btn-precedent" data-step="3">← Étape précédente</button>
      <button class="btn btn-primary btn-suivant" data-step="5">Étape suivante →</button>
    </div>

  <?php else: ?>
    <div class="alert alert-warning">Veuillez ajouter une adresse à l’étape 1.</div>
  <?php endif; ?>
</div>