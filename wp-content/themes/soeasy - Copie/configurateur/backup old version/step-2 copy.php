<?php
/**
 * Étape 2 – Configuration Internet par adresse
 */

if (!function_exists('get_template_directory')) {
  require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}

require_once get_template_directory() . '/configurateur/functions-configurateur.php';

$forfaits = soeasy_session_get('soeasy_config_forfaits_internet', []);
$equipements = soeasy_session_get('soeasy_config_equipements_internet', []);

$adresses = soeasy_get_adresses_configurateur();
$duree = soeasy_get_selected_duree_engagement() ?? 0;
$mode = soeasy_get_selected_financement();

?>

<div class="config-step step-2 container py-4">
  <h2 class="mb-4">2. Choix de la connexion Internet</h2>

  <?php if (!empty($adresses)): ?>
    <!-- Onglets par adresse -->
    <ul class="nav nav-tabs mb-3" id="adresseTabs" role="tablist">
      <?php foreach ($adresses as $i => $adresse): ?>
        <li class="nav-item" role="presentation">
          <button class="nav-link <?php echo $i === 0 ? 'active' : ''; ?>" id="tab-<?php echo $i; ?>" data-bs-toggle="tab"
            data-bs-target="#content-<?php echo $i; ?>" type="button"
            role="tab"><?php echo esc_html($adresse['adresse']); ?></button>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="tab-content" id="adresseTabsContent">
      <?php foreach ($adresses as $i => $adresse): ?>
        <div class="tab-pane fade <?php echo $i === 0 ? 'show active' : ''; ?>" id="content-<?php echo $i; ?>"
          role="tabpanel">

          <?php //if (in_array($duree, [36, 63])): ?>
            <!-- Forfaits Internet uniquement si engagement 36 ou 63 mois -->
            <div class="mb-4">
              <h5>Choisissez votre forfait Internet</h5>
              <div class="row gy-3">
                <?php
                $args = array(
                  'post_type' => 'product',
                  'posts_per_page' => -1,
                  'product_cat' => 'forfait-internet'
                );
                $loop = new WP_Query($args);
                while ($loop->have_posts()):
                  $loop->the_post();
                  $product = wc_get_product(get_the_ID());
                  $product_id = $product->get_id();

                  // Gérer produit variable avec ses variations
                  if ($product->is_type('variable')) {
                    $variation_id = null;
                    $variations = $product->get_available_variations();

                    foreach ($variations as $v) {
                      $attributes = $v['attributes'];
                      if (isset($attributes['attribute_duree-dengagement']) && intval($attributes['attribute_duree-dengagement']) === intval($duree)) {
                        $variation_id = $v['variation_id'];
                        break;
                      }
                    }

                    if (!empty($variations)) {
                      $variation = new WC_Product_Variation($variation_id);
                      $prix = $variation->get_price();
                    } else {
                      $prix = 0;
                    }
                  } else {
                    $prix = $product->get_price();
                  }
                  ?>
                  <div class="col-md-6">
                    <label class="border p-3 d-block rounded shadow-sm h-100">
                      <input type="radio" name="forfait_internet_<?php echo $i; ?>" value="<?php echo $product_id; ?>"
                        class="me-2" <?php checked($forfaits[$i] ?? '', $product_id); ?>>
                      <strong><?php the_title(); ?></strong><br>
                      <span class="text-muted"><?php echo get_the_excerpt(); ?></span><br>
                      <span class="fw-bold mt-2 d-block"><?php echo wc_price($prix); ?> / mois</span>
                    </label>
                  </div>
                <?php endwhile;
                wp_reset_postdata(); ?>
              </div>
            </div>
          <?php //endif; ?>

          <!-- Matériel Internet -->
          <div class="mb-4">
            <h5>Choisissez votre matériel</h5>
            <div class="row gy-4">
              <?php
              $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'product_cat' => 'internet-routeurs'
              );
              $loop = new WP_Query($args);
              while ($loop->have_posts()):
                $loop->the_post();
                $product = wc_get_product(get_the_ID());
                $product_id = $product->get_id();
                $prix_comptant = $product->get_regular_price();
                $prix_leasing = get_field('prix_leasing_' . $duree, $product_id);
                ?>
                <div class="col-md-6">
                  <div class="border p-3 rounded h-100">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <strong><?php the_title(); ?></strong><br>
                        <span class="text-muted"><?php echo get_the_excerpt(); ?></span>
                      </div>
                      <input type="checkbox" name="equipement_<?php echo $i; ?>[]" value="<?php echo $product_id; ?>"
                      <?php checked(in_array($product_id, $equipements[$i] ?? [])); ?>>
                    </div>
                    <div class="mt-2 small">
                      <?php if ($mode === 'leasing' && $duree > 0): ?>
                        <?php echo wc_price($prix_leasing); ?> / mois
                      <?php else: ?>
                        <?php echo wc_price($prix_comptant); ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endwhile;
              wp_reset_postdata(); ?>
            </div>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button class="btn btn-outline-secondary btn-precedent" data-step="1">← Étape précédente</button>
      <button class="btn btn-primary btn-suivant" data-step="3">Étape suivante →</button>
    </div>

  <?php else: ?>
    <div class="alert alert-warning">Aucune adresse n’a été ajoutée. Veuillez commencer par l’étape 1.</div>
  <?php endif; ?>
</div>