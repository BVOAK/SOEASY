<?php
/**
 * Étape 1 – Saisie des adresses de mise en service
 */

if (!function_exists('get_template_directory')) {
  require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}

require_once get_template_directory() . '/configurateur/functions-configurateur.php';
$adresses = soeasy_get_adresses_configurateur();
?>

<div class="config-step step-1 container py-4">
  <h2 class="mb-4">1. Adresse de mise en service</h2>

  <!-- Formulaire d'ajout d'une adresse -->
  <form id="form-ajout-adresse" class="row g-3 mb-4">
    <div class="col-12">
      <label for="adresse" class="form-label">Adresse</label>
      <input type="text" class="form-control" id="adresse" name="adresse" placeholder="Entrez l'adresse">
    </div>
    <div class="col-12">
      <label for="services" class="form-label">Services souhaités</label><br>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" id="internet" name="services[]" value="internet">
        <label class="form-check-label" for="internet">Internet</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" id="mobile" name="services[]" value="mobile">
        <label class="form-check-label" for="mobile">Mobile</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" id="centrex" name="services[]" value="centrex">
        <label class="form-check-label" for="centrex">Centrex</label>
      </div>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Ajouter cette adresse</button>
    </div>
  </form>

  <!-- Liste des adresses enregistrées -->
  <div id="liste-adresses">
    <?php if (!empty($adresses)) : ?>
      <h5>Adresses enregistrées :</h5>
      <ul class="list-group mb-4">
        <?php foreach ($adresses as $i => $adresse) : ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>
              <?php echo esc_html($adresse['adresse']); ?> —
              <em>Services : <?php echo implode(', ', $adresse['services']); ?></em>
            </span>
            <button class="btn btn-sm btn-outline-danger btn-remove-adresse" data-index="<?php echo $i; ?>">Supprimer</button>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="d-flex justify-content-between mt-4">
      <button class="btn btn-primary btn-suivant float-end" data-step="2">Étape suivante →</button>
  </div>
</div>
