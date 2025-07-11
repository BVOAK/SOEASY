<?php
/**
 * Étape 2 – Configuration Internet par adresse
 */
if (!function_exists('get_template_directory')) {
  require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}
require_once get_template_directory() . '/configurateur/functions-configurateur.php';

$forfaits = soeasy_session_get('soeasy_forfaits_internet', []);
$equipements = soeasy_session_get('soeasy_equipements_internet', []);

$adresses = soeasy_get_adresses_configurateur();
$duree = soeasy_get_selected_duree_engagement() ?? 0;
$mode = soeasy_get_selected_financement();
?>

<div class="config-step step-2 container py-4">
  <h2 class="mb-4">2. Choix de la connexion Internet</h2>

  <?php if (!empty($adresses)): ?>
    <ul class="nav nav-tabs mb-3">
      <?php foreach ($adresses as $i => $adresse): ?>
        <li class="nav-item">
          <button class="nav-link <?php echo $i === 0 ? 'active' : ''; ?>" data-bs-toggle="tab"
            data-bs-target="#tab-<?php echo $i; ?>">
            <?php echo esc_html($adresse['adresse']); ?>
          </button>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="tab-content">
      <?php foreach ($adresses as $i => $adresse): ?>
        <div class="tab-pane fade <?php echo $i === 0 ? 'show active' : ''; ?>" id="tab-<?php echo $i; ?>">

          <!-- Forfait Internet -->
          <h5 class="mt-4">Forfait Internet</h5>
          <div class="row gy-3">
            <?php
            $args = ['post_type' => 'product', 'posts_per_page' => -1, 'product_cat' => 'forfait-internet'];
            $loop = new WP_Query($args);
            while ($loop->have_posts()):
              $loop->the_post();
              $product = wc_get_product(get_the_ID());
              $product_id = $product->get_id();

              $variations = $product->get_available_variations();
              $data_attrs = '';
              $prix_affiche = 0;

              // Construction des data-prix-leasing-XX
              foreach ($variations as $variation) {
                $attr = $variation['attributes']['attribute_pa_duree-dengagement'] ?? '';
                $duree_var = null;

                if (stripos($attr, 'sans') !== false || stripos($attr, 'engagement') !== false || empty($attr)) {
                  $duree_var = 0;
                } else {
                  $duree_var = intval(preg_replace('/[^0-9]/', '', $attr));
                }

                if (!is_null($duree_var)) {
                  $prix_var = $variation['display_price'];
                  $data_attrs .= ' data-prix-leasing-' . $duree_var . '="' . esc_attr($prix_var) . '"';

                  // Prix affiché correspondant à la durée sélectionnée
                  if ($duree == $duree_var) {
                    $prix_affiche = $prix_var;
                  }
                }
              }

              $checked = isset($forfaits[$i]) && $forfaits[$i] == $product_id ? 'checked' : '';
              ?>
              <div class="col-md-6">
                <label class="border p-3 d-block rounded shadow-sm h-100">
                  <input type="radio" name="forfait_internet_<?php echo $i; ?>" value="<?php echo $product_id; ?>"
                    class="me-2" <?php echo $checked; ?> data-index="<?php echo $i; ?>" <?php echo $data_attrs; ?>>
                  <strong><?php the_title(); ?></strong><br>
                  <span class="text-muted"><?php echo get_the_excerpt(); ?></span><br>
                  <div class="fw-bold mt-2 prix-affiche" data-unit="<?php echo esc_attr($prix_affiche); ?>">
                    <?php echo wc_price($prix_affiche); ?> / mois
                  </div>
                </label>
              </div>
            <?php endwhile;
            wp_reset_postdata(); ?>
          </div>


          <!-- Matériel Internet -->
          <h5 class="mt-5">Matériel Internet</h5>
          <div class="row gy-3">
            <?php
            $args = ['post_type' => 'product', 'posts_per_page' => -1, 'product_cat' => 'internet-routeurs'];
            $loop = new WP_Query($args);
            while ($loop->have_posts()):
              $loop->the_post();
              $product = wc_get_product(get_the_ID());
              $product_id = $product->get_id();

              $prix_comptant = floatval($product->get_regular_price());
              $prix_leasing_map = [];

              // Récupération des prix leasing pour toutes les durées (y compris 0 = sans engagement)
              foreach ([0, 24, 36, 48, 63] as $d) {
                $prix_leasing_map[$d] = floatval(get_field("prix_leasing_$d", $product_id)) ?: 0;
              }

              // Calcul du prix affiché selon mode et durée
              if ($mode === 'leasing') {
                $prix = $prix_leasing_map[$duree] ?? $prix_leasing_map[0];
              } else {
                $prix = $prix_comptant;
              }

              $is_selected = isset($equipements[$i]) && in_array($product_id, $equipements[$i]);
              $checked = $is_selected ? 'checked' : '';
              ?>
              <div class="col-md-6">
                <label class="border p-3 d-block rounded shadow-sm h-100"
                  data-prix-comptant="<?php echo esc_attr($prix_comptant); ?>" <?php foreach ([0, 24, 36, 48, 63] as $d): ?>
                    data-prix-leasing-<?php echo $d; ?>="<?php echo esc_attr($prix_leasing_map[$d]); ?>" <?php endforeach; ?>>
                  <input type="checkbox" name="equipement_<?php echo $i; ?>[]" value="<?php echo $product_id; ?>"
                    class="me-2 equipement-checkbox" data-index="<?php echo $i; ?>" data-id="<?php echo $product_id; ?>" <?php echo $checked; ?>>

                  <strong><?php the_title(); ?></strong><br>
                  <span class="text-muted"><?php echo get_the_excerpt(); ?></span><br>
                  <span class="fw-bold d-block prix-affiche" data-unit="<?php echo esc_attr($prix); ?>"
                    data-type="equipement">
                    <?php echo wc_price($prix) . ($mode === 'leasing' ? ' / mois' : ''); ?>
                  </span>
                </label>
              </div>
            <?php endwhile;
            wp_reset_postdata(); ?>
          </div>


        </div>
      <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button class="btn btn-outline-secondary btn-precedent" data-step="1">← Étape précédente</button>
      <button class="btn btn-primary btn-suivant" data-step="3">Étape suivante →</button>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">Veuillez ajouter une adresse à l’étape 1.</div>
  <?php endif; ?>
</div>