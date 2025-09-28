
<div class="col-lg-4 d-none d-lg-block">
  <div id="config-sidebar" class="p-4 bg-light rounded shadow-sm">
    <h5 class="mb-3">Votre configuration</h5>

    <!-- Engagement -->
    <div class="mb-3">
      <label for="engagement" class="form-label">Durée d’engagement</label>
      <select id="engagement" class="form-select">
        <option value="0">Sans engagement</option>
        <option value="24">24 mois</option>
        <option value="36">36 mois</option>
        <option value="48">48 mois</option>
        <option value="63">63 mois</option>
      </select>
    </div>

    <!-- Mode de financement -->
    <div class="mb-3">
      <label class="form-label d-block">Financement du matériel</label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="financement" id="financement_comptant" value="comptant" checked>
        <label class="form-check-label" for="financement_comptant">Achat comptant</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="financement" id="financement_leasing" value="leasing">
        <label class="form-check-label" for="financement_leasing">Location (leasing)</label>
      </div>
    </div>

    <!-- Résumé dynamique -->
    <div id="config-recapitulatif">
      <div id="accordionSidebarRecap">
        <!-- Accordéons dynamiques injectés ici via JS -->
      </div>
    </div>

    <!-- Total -->
    <div id="config-sidebar-total" class="mt-4 border-top pt-3">
      <!-- Contenu injecté dynamiquement -->
    </div>

    <!-- Réassurance -->
    <ul class="border-top small text-muted p-0 m-0 pt-3 mt-4">
      <li><strong>✅</strong> Accompagnement technique après souscription</li>
      <li><strong>🔒</strong> Paiement 100% sécurisé</li>
      <li><strong>📞</strong> Assistance client disponible</li>
    </ul>
  </div>
</div>
