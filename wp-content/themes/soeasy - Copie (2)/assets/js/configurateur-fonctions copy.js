/**
   * Fonction centrale de récupération du mode de financement
   * 'comptant' ou 'leasing'
   */

function initGoogleAutocomplete() {
  if (!window.google || !google.maps || !google.maps.places) {
    setTimeout(initGoogleAutocomplete, 300);
    return;
  }

  const $input = document.querySelector('#adresse');
  if (!$input) return;

  const autocomplete = new google.maps.places.Autocomplete($input, {
    types: ['address'],
    componentRestrictions: { country: 'fr' }
  });

  autocomplete.addListener('place_changed', function () {
    const place = autocomplete.getPlace();
    console.log('Adresse sélectionnée :', place);
  });
}


function getSelectedFinancementMode() {
  return jQuery('input[name="financement"]:checked').val() || 'comptant';
}

/**
 * Fonction centrale de récupération de la durée d'engagement
 * retourne 24, 36, 48, 63 ou null
 */
function getSelectedEngagement() {
  return parseInt(jQuery('#engagement').val()) || null;
}

/**
 * Fonction centrale de récupération des adresses
 */
function getAdresseByIndex(index) {
  const adresses = JSON.parse(localStorage.getItem('soeasyAdresses')) || [];
  return adresses[index]?.adresse || `Adresse #${parseInt(index) + 1}`;
}

/**
* Met à jour dynamiquement le prix total affiché
*/
function updatePrixTotal($input) {

  const $prixTotal = jQuery($input).closest('.border').find('.prix-total');
  const unit = parseFloat($prixTotal.data('unit')) || 0;
  const qty = parseInt(jQuery($input).val()) || 0;
  const total = unit * qty;

  const mode = getSelectedFinancementMode();
  const typeAttr = jQuery($input).data('type');

  const prixFormatte = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(total);

  let suffix = '';
  if (typeAttr === 'forfait' || (typeAttr === 'equipement' && mode === 'leasing')) {
    suffix = ' / mois';
  }

  $prixTotal.text(prixFormatte + suffix);
}

// Calcul total global si souhaité
// Tu peux aussi mettre à jour #recap-total-mois-1 et #recap-total-mensuel ici
function saveToLocalConfig(adresseId, section, nouveauxProduits, options = {}) {
  const key = 'soeasyConfig';
  const config = JSON.parse(localStorage.getItem(key)) || {};

  if (!config[adresseId]) config[adresseId] = {};
  if (!config[adresseId][section]) config[adresseId][section] = [];

  const existants = config[adresseId][section];
  let fusionnes = [];

  if (options.replace === true && options.type) {
    fusionnes = existants.filter(p => {
      // Si un type est défini dans le produit, on le compare
      if (typeof p.type !== 'undefined') {
        return p.type !== options.type;
      }
      // Si aucun type défini, on conserve (ex: type absent → Internet mal stocké)
      return true;
    });
  } else {
    fusionnes = [...existants];
  }

  // Indexation par identifiant unique (id ou nom)
  const indexés = {};
  fusionnes.forEach(p => {
    const key = p.id || p.nom;
    indexés[key] = p;
  });
  nouveauxProduits.forEach(p => {
    const key = p.id || p.nom;
    indexés[key] = p;
  });

  // Reconstruire la liste fusionnée
  config[adresseId][section] = Object.values(indexés);

  // Enregistrer dans localStorage
  localStorage.setItem(key, JSON.stringify(config));

  // Envoi en session PHP
  jQuery.post(soeasyVars.ajaxurl, {
    action: 'soeasy_set_config_part',
    index: adresseId,
    key: section,
    items: config[adresseId][section]
  });

  if (section === 'fraisInstallation') {
    jQuery.post(soeasyVars.ajaxurl, {
      action: 'soeasy_set_frais_installation',
      index: adresseId,
      items: config[adresseId][section]
    });
  }
}

/**
   * Parcours tous les prix produits en fonction de la durée et du financement
   */
function updatePrixProduits() {
  const mode = getSelectedFinancementMode();
  const duree = getSelectedEngagement();
  const recapData = JSON.parse(localStorage.getItem('soeasyConfig')) || {};

  Object.entries(recapData).forEach(([adresseId, config]) => {
    ['abonnements', 'materiels'].forEach(section => {
      if (!Array.isArray(config[section])) return;

      config[section].forEach(produit => {
        if (!produit || typeof produit !== 'object') return;

        let prix = parseFloat(produit.prixUnitaire) || 0;

        const isAbonnement = ['internet', 'forfait-mobile', 'forfait-data', 'licence-centrex'].includes(produit.type);
        const cle = 'prixLeasing' + (duree !== null ? duree : '0');

        if (isAbonnement) {
          prix = parseFloat(produit[cle]) || produit.prixUnitaire;
        } else if (mode === 'comptant') {
          prix = parseFloat(produit.prixComptant) || produit.prixUnitaire;
        } else if (mode === 'leasing') {
          prix = parseFloat(produit[cle]) || produit.prixUnitaire;
        }

        produit.prixUnitaire = prix;
        
      });
    });
  });

  localStorage.setItem('soeasyConfig', JSON.stringify(recapData));
}

window.initGoogleAutocomplete = initGoogleAutocomplete;
window.getSelectedEngagement = getSelectedEngagement;
window.getSelectedFinancementMode = getSelectedFinancementMode;
window.updatePrixTotal = updatePrixTotal;
window.saveToLocalConfig = saveToLocalConfig;
window.updatePrixProduits = updatePrixProduits;

jQuery(document).ready(function ($) {

  /**
   * MAJ des prix affichés selon mode de financement + engagement
   */
  function updatePrices() {
    const mode = getSelectedFinancementMode();
    const duree = getSelectedEngagement();

    // Met à jour l'affichage des prix visibles dans l'interface
    $('[data-prix-comptant], [data-prix-leasing-24], [data-prix-leasing-36], [data-prix-leasing-48], [data-prix-leasing-63]').each(function () {
      const $el = $(this);
      let newPrice = '';

      if (mode === 'comptant') {
        newPrice = $el.data('prix-comptant');
      } else if (duree !== null) {
        newPrice = $el.data('prix-leasing-' + duree);
      }

      if (newPrice !== undefined) {
        $el.find('.prix-affiche').text(newPrice + (mode === 'leasing' ? ' € / mois' : ' €'));
        $el.find('.prix-affiche').data('unit', parseFloat(newPrice));
      }
    });

    // Synchronise les données dans le localStorage pour les forfaits Internet sélectionnés
    $('input[name^="forfait_internet_"]:checked').each(function () {
      const index = $(this).data('index');
      const id = parseInt($(this).data('id') || $(this).val());

      const $produit = $(this).closest('.border');
      const nom = $produit.find('strong').text().trim();
      const details = $produit.find('.text-muted').text().trim();
      const prix = parseFloat($produit.find('.prix-affiche').data('unit')) || 0;

      const prixLeasing0 = parseFloat($(this).data('prix-leasing-0')) || 0;
      const prixLeasing24 = parseFloat($(this).data('prix-leasing-24')) || 0;
      const prixLeasing36 = parseFloat($(this).data('prix-leasing-36')) || 0;
      const prixLeasing48 = parseFloat($(this).data('prix-leasing-48')) || 0;
      const prixLeasing63 = parseFloat($(this).data('prix-leasing-63')) || 0;

      const produits = [{
        id,
        type: 'internet',
        nom,
        details,
        quantite: 1,
        prixUnitaire: prix,
        prixComptant: prix,
        prixLeasing0,
        prixLeasing24,
        prixLeasing36,
        prixLeasing48,
        prixLeasing63
      }];

      saveToLocalConfig(index, 'abonnements', produits, { replace: true, type: 'internet' });
    });

    // === 🔁 Synchronisation des frais d'installation (Step-5) ===
    $('.frais-checkbox').each(function () {
      const $cb = $(this);
      const index = $cb.data('index');
      const config = JSON.parse(localStorage.getItem('soeasyConfig') || '{}');
      const frais = config[index]?.fraisInstallation || [];
      const item = frais.find(f => f.id === $cb.data('id'));
      if (!item) return;

      // Synchronise les data-* pour permettre à updateFraisTotal de lire la bonne valeur
      $cb.data('prix-comptant', item.prixComptant || 0);
      $cb.data('prix-leasing-24', item.prixLeasing24 || 0);
      $cb.data('prix-leasing-36', item.prixLeasing36 || 0);
      $cb.data('prix-leasing-48', item.prixLeasing48 || 0);
      $cb.data('prix-leasing-63', item.prixLeasing63 || 0);
    });

    // === 🔁 Mise à jour immédiate des totaux FI
    $('.frais-installation-list').each(function () {
      const index = $(this).data('index');
      updateFraisTotal(index);
    });


  }


  /* Ajout d'une adresse en session via AJAX */
  function addAdresseToSession(adresse, services) {
    $.ajax({
      url: soeasyVars.ajaxurl,
      method: 'POST',
      data: {
        action: 'soeasy_add_adresse',
        adresse: adresse,
        services: services
      },
      success: function (response) {
        console.log('Adresse enregistrée :', response);
      }
    });
  }

  // Mise en forme du récap de l'étape 5
function updateRecapitulatif() {
  const recapData = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
  const mode = getSelectedFinancementMode();
  const engagement = getSelectedEngagement();

  Object.entries(recapData).forEach(([adresseId, data]) => {
    const $adresseBlock = jQuery(`#collapse-${adresseId}`);

    // — abonnements
    const abonnementsTbody = $adresseBlock.find('.recap-abonnements tbody');
    abonnementsTbody.empty();
    (data.abonnements || []).forEach(item => {
      abonnementsTbody.append(`
        <tr>
          <td>${item.nom} <small class="d-block">${item.description || ''}</small></td>
          <td>${item.quantite}</td>
          <td>${(item.prixUnitaire ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €</td>
          <td>${((item.prixUnitaire ?? 0) * item.quantite).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €</td>
        </tr>
      `);
    });

    // — matériels
    const materielsTbody = $adresseBlock.find('.recap-materiels tbody');
    materielsTbody.empty();
    (data.materiels || []).forEach(item => {
      materielsTbody.append(`
        <tr>
          <td>${item.nom} <small class="d-block">${item.description || ''}</small></td>
          <td>${item.quantite}</td>
          <td>${(item.prixUnitaire ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €</td>
          <td>${((item.prixUnitaire ?? 0) * item.quantite).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €</td>
        </tr>
      `);
    });

    // — frais d'installation
    const fraisTbody = $adresseBlock.find('.recap-installations tbody');
    fraisTbody.empty();

    // Calcul du nombre de postes Centrex
    let nbPostesCentrex = 0;
    (data.materiels || []).forEach(prod => {
      if (prod.type === 'poste-centrex') {
        nbPostesCentrex += parseInt(prod.quantite) || 0;
      }
    });

    // Sélection des bons frais centrex
    let fraisInstallationFiltres = [];
    const centrexSeuils = [];
    const autresFrais = [];

    (data.fraisInstallation || []).forEach(frais => {
      if (frais.type === 'centrex' && typeof frais.nom === 'string' && frais.nom.toLowerCase().includes('centrex')) {
        const match = frais.nom.match(/(\d+)\s*(à|-)\s*(\d+)/);
        const maxPostes = match ? parseInt(match[3]) : 9999;
        centrexSeuils.push({ max: maxPostes, item: frais });
      } else {
        autresFrais.push(frais);
      }
    });

    // Trier par seuil croissant
    centrexSeuils.sort((a, b) => a.max - b.max);

    // Garder le premier frais centrex applicable
    let fraisCentrexApplicable = centrexSeuils.find(f => nbPostesCentrex <= f.max);
    if (fraisCentrexApplicable) fraisInstallationFiltres.push(fraisCentrexApplicable.item);

    // Réunir les frais filtrés
    fraisInstallationFiltres = [...autresFrais, ...fraisInstallationFiltres];

    // 💸 Affichage
    fraisInstallationFiltres.forEach(item => {
      const qty = parseInt(item.quantite) || 0;
      let unit = 0;

      if (mode === 'leasing') {
        const key = `prixLeasing${engagement}`;
        unit = item[key] ?? item.prixLeasing0 ?? 0;
      } else {
        unit = item.prixComptant ?? 0;
      }

      const total = unit * qty;
      const suffix = mode === 'leasing' ? ' €' : ' €';

      fraisTbody.append(`
        <tr>
          <td>${item.nom}</td>
          <td>${qty}</td>
          <td>${unit.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}${suffix}</td>
          <td>${total.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}${suffix}</td>
        </tr>
      `);
    });
  });
}



  // Mise en forme du récap de la sidebar des produits
  function updateSidebarProduitsRecap() {
    const recapData = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
    const $container = $('#config-recapitulatif');
    $container.empty();

    const engagement = getSelectedEngagement();
    const mode = getSelectedFinancementMode();

    if (Object.keys(recapData).length === 0) {
      $container.append('<p>Aucune sélection pour le moment.</p>');
      return;
    }

    Object.entries(recapData).forEach(([index, config]) => {
      const adresse = getAdresseByIndex(index);
      const abonnements = config.abonnements || [];
      const materiels = config.materiels || [];
      const frais = config.fraisInstallation || [];

      const $accordion = $(`
        <div class="accordion mb-3" id="accordion-${index}">
          <div class="accordion-item">
            <h2 class="accordion-header" id="sidebar-heading-${index}">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#sidebar-collapse-${index}" aria-expanded="false" aria-controls="sidebar-collapse-${index}">
                ${adresse}
              </button>
            </h2>
            <div id="sidebar-collapse-${index}" class="accordion-collapse collapse" aria-labelledby="heading-${index}">
              <div class="accordion-body">
  
                <h6 class="mt-2">Abonnements</h6>
                <ul class="list-unstyled small recap-abonnements"></ul>
  
                <h6 class="mt-3">Équipements</h6>
                <ul class="list-unstyled small recap-materiels"></ul>

                <h6 class="mt-3">Frais d'installation</h6>
                <ul class="list-unstyled small recap-frais-installation"></ul>
  
              </div>
            </div>
          </div>
        </div>
      `);

      const $abonnements = $accordion.find('.recap-abonnements');
      abonnements.forEach(item => {
        const total = (item.prixUnitaire || 0) * item.quantite;
        $abonnements.append(`
          <li>${item.nom} × ${item.quantite} <span class="float-end">${total.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €</span></li>
        `);
      });

      const $materiels = $accordion.find('.recap-materiels');
      materiels.forEach(item => {
        const total = (item.prixUnitaire || 0) * item.quantite;
        $materiels.append(`
          <li>${item.nom} × ${item.quantite} <span class="float-end">${total.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €</span></li>
        `);
      });

      // Frais d'installation (SEUL le total)
      const $fraisList = $accordion.find('.recap-frais-installation');
      let totalFrais = 0;
      frais.forEach(item => {
        const unit = mode === 'leasing'
          ? (item[`prixLeasing${engagement}`] ?? item.prixLeasing0)
          : item.prixComptant;
        totalFrais += unit * item.quantite;
      });
      const suffix = mode === 'leasing' ? ' € / mois' : ' €';
      $fraisList.append(`
      <li>Total frais d'installation <span class="float-end">${totalFrais.toLocaleString('fr-FR', { minimumFractionDigits: 2 })}${suffix}</span></li>
     `);

      $container.append($accordion);
    });
  }


  // Mise en forme du récap de la sidebar des totaux
  function updateSidebarTotauxRecap() {
    const mode = getSelectedFinancementMode();
    const duree = getSelectedEngagement(); // ex: 24, 36, etc.
    const recapData = JSON.parse(localStorage.getItem('soeasyConfig')) || {};

    let totalAbonnement = 0;
    let totalComptant = 0;
    let totalMensuelLeasing = 0;

    Object.values(recapData).forEach(config => {
      (config.abonnements || []).forEach(item => {
        const prix = item.prixUnitaire || 0;
        totalAbonnement += prix * item.quantite;
      });

      (config.materiels || []).forEach(item => {
        const prixComptant = item.prixComptant || 0;
        const prixLeasing = item[`prixLeasing${duree}`] || 0;

        if (mode === 'leasing') {
          totalMensuelLeasing += prixLeasing * item.quantite;
          totalComptant += prixComptant * item.quantite;
        } else {
          totalComptant += prixComptant * item.quantite;
          totalMensuelLeasing += prixLeasing * item.quantite;
        }
      });
    });

    const $container = $('#config-sidebar-total');
    $container.empty();

    if (mode === 'leasing') {
      const totalMensuel = totalAbonnement + totalMensuelLeasing;
      $container.append(`
        <div class="fw-bold mb-1">Abonnement + Leasing : ${totalMensuel.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} € / mois</div>
        <div class="text-muted small">
        ou abonnements : ${totalAbonnement.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} € / mois<br>
        + équipements : ${totalComptant.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €
      </div>
      `);
    } else {
      $container.append(`
        <div class="fw-bold mb-1">Abonnements mensuels : ${totalAbonnement.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €</div>
        <div class="fw-bold mb-1">Équipements : ${totalComptant.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €</div>
        <div class="text-muted small">ou abonnements + leasing : ${(totalAbonnement + totalMensuelLeasing).toLocaleString('fr-FR', { minimumFractionDigits: 2 })} € / mois</div>
      `);
    }
  }



  // Exposition globale
  window.updatePrices = updatePrices;
  window.addAdresseToSession = addAdresseToSession;
  window.updateRecapitulatif = updateRecapitulatif;
  window.updateSidebarProduitsRecap = updateSidebarProduitsRecap;
  window.updateSidebarTotauxRecap = updateSidebarTotauxRecap;
});