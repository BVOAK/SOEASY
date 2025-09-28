<?php
/**
 * Étape 5 – Frais d'installation - AFFICHAGE LOCALSTORAGE
 * Refonte complète pour éliminer les race conditions
 */
if (!function_exists('get_template_directory')) {
  require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}
require_once get_template_directory() . '/configurateur/functions-configurateur.php';

// Récupérer les données de session comme fallback uniquement
$session_config = WC()->session->get('soeasy_configurateur', []);
$duree = soeasy_get_selected_duree_engagement();
$mode = soeasy_get_selected_financement();
$adresses = soeasy_get_adresses_configurateur();
?>

<div class="config-step step-5 container py-4">
  <h2 class="mb-4">5. Frais d'installation</h2>
  
  <!-- Loader pendant génération JavaScript -->
  <div id="step5-loader">
    <div class="d-flex flex-column align-items-center justify-content-center py-5">
      <div class="spinner-border text-primary mb-3" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <p class="text-muted">Génération des frais d'installation...</p>
    </div>
  </div>
  
  <!-- Contenu généré par JavaScript -->
  <div id="step5-content" style="display: none;"></div>
  
  <!-- Navigation -->
  <div id="step5-navigation" class="d-flex justify-content-between mt-4" style="display: none;">
    <button class="btn btn-outline-secondary btn-precedent" data-step="4">← Étape précédente</button>
    <button class="btn btn-primary btn-suivant" data-step="6">Étape suivante →</button>
  </div>
</div>

<!-- Variables pour JavaScript -->
<script>
window.step5Data = {
  sessionConfig: <?php echo json_encode($session_config); ?>,
  duree: <?php echo json_encode($duree); ?>,
  mode: <?php echo json_encode($mode); ?>,
  adresses: <?php echo json_encode($adresses); ?>
};
</script>