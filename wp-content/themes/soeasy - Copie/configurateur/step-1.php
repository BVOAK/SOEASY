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

  <!-- Navigation -->
  <div class="d-flex justify-content-end">
    <button class="btn btn-primary btn-suivant" data-step="2">Passer à l’étape suivante</button>
  </div>
</div>

<script>
jQuery(document).ready(function($) {
  let autocomplete = new google.maps.places.Autocomplete(document.getElementById('adresse'));

  // Ajout d'adresse
  $('#form-ajout-adresse').on('submit', function(e) {
    e.preventDefault();
    const adresse = $('#adresse').val();
    const services = [];
    $('input[name="services[]"]:checked').each(function() {
      services.push($(this).val());
    });

    if (adresse.length === 0 || services.length === 0) {
      alert("Merci de renseigner une adresse et de sélectionner au moins un service.");
      return;
    }

    $.ajax({
      url: soeasyVars.ajaxurl,
      type: 'POST',
      data: {
        action: 'soeasy_add_adresse_configurateur',
        adresse: adresse,
        services: services
      },
      success: function(response) {
        location.reload(); // Recharge pour afficher la nouvelle adresse
      },
      error: function() {
        alert("Une erreur est survenue.");
      }
    });
  });

  // Suppression d'adresse
  $(document).on('click', '.btn-remove-adresse', function() {
    const index = $(this).data('index');
    $.ajax({
      url: soeasyVars.ajaxurl,
      type: 'POST',
      data: {
        action: 'soeasy_remove_adresse_configurateur',
        index: index
      },
      success: function() {
        location.reload();
      }
    });
  });

  // Navigation vers l’étape suivante
  $('.btn-suivant').on('click', function() {
    const step = $(this).data('step');
    $('#config-step-content').load(soeasyVars.themeUrl + '/configurateur/step-' + step + '.php');
    $('.config-steps .nav-link').removeClass('active');
    $('.config-steps [data-step="' + step + '"] .nav-link').addClass('active');
  });
});
</script>
