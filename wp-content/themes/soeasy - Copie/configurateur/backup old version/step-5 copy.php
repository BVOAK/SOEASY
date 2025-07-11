<?php
/**
 * Étape 5 – Récapitulatif de la configuration et validation commande
 */

 if (!function_exists('get_template_directory')) {
  require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}

require_once get_template_directory() . '/configurateur/functions-configurateur.php';
$adresses = soeasy_get_adresses_configurateur();
?>

<div class="config-step step-5 container py-4">
  <h2 class="mb-4">5. Récapitulatif de votre configuration</h2>

  <?php if (!empty($adresses)) : ?>
    <div class="accordion" id="recapAccordion">
      <?php foreach ($adresses as $i => $adresse) : ?>
        <div class="accordion-item">
          <h2 class="accordion-header" id="heading-<?php echo $i; ?>">
            <button class="accordion-button <?php echo $i !== 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $i; ?>" aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>" aria-controls="collapse-<?php echo $i; ?>">
              <?php echo esc_html($adresse['adresse']); ?>
            </button>
          </h2>
          <div id="collapse-<?php echo $i; ?>" class="accordion-collapse collapse <?php echo $i === 0 ? 'show' : ''; ?>" aria-labelledby="heading-<?php echo $i; ?>" data-bs-parent="#recapAccordion">
            <div class="accordion-body">
              <div class="recap-abonnements mb-4">
                <h5>Abonnements</h5>
                <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Produit</th>
                        <th>Détails</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- À remplir dynamiquement via JS -->
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="recap-materiels mb-4">
                <h5>Matériels & Accessoires</h5>
                <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Produit</th>
                        <th>Description</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- À remplir dynamiquement via JS -->
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="recap-installations mb-4">
                <h5>Frais de mise en service / Installation</h5>
                <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Service</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- À remplir dynamiquement via JS -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else : ?>
    <p class="text-danger">Aucune configuration trouvée. Veuillez recommencer votre configuration.</p>
  <?php endif; ?>

  <!-- Récap général -->
  <div class="recap-global mt-5">
    <h4>Total de la commande</h4>
    <div class="d-flex flex-column gap-2">
      <div><strong>Total abonnements :</strong> <span id="total-abonnements">0 €</span></div>
      <div><strong>Total équipements :</strong> <span id="total-equipements">0 €</span></div>
      <div><strong>Total installation :</strong> <span id="total-installation">0 €</span></div>
      <div><strong>Total TTC :</strong> <span id="total-ttc">0 €</span></div>
    </div>
    <div class="mt-3">
      <label for="coupon">Code promo :</label>
      <input type="text" id="coupon" class="form-control w-50" placeholder="Entrez votre code promo">
    </div>
    <div class="mt-4 d-flex flex-column flex-md-row justify-content-between gap-2">  
      <button class="btn btn-outline-secondary" id="sauvegarder-panier">Sauvegarder ma configuration</button>
      <button class="btn btn-outline-secondary btn-precedent" data-step="4">← Étape précédente</button>
      <a href="/commande" class="btn btn-success">Commander</a>
    </div>
  </div>

</div>
