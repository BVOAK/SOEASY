jQuery(document).ready(function ($) {

  // === Étape suivante ===
  $(document).on('click', '.btn-suivant', function (e) {
    e.preventDefault();
    let step = $(this).data('step');

    $.get(soeasyVars.themeUrl + '/configurateur/step-' + step + '.php', function (data) {
      $('#config-step-content').html(data);
      $('.config-steps li').removeClass('active');
      $('.config-steps li[data-step="' + step + '"]').addClass('active');
      togglePrevButton(step);
      updatePrices();
      updateRecapitulatif();
      updateSidebarProduitsRecap();
      updateSidebarTotauxRecap();
    });
  });

  // === Étape précédente ===
  $(document).on('click', '.btn-prev', function (e) {
    e.preventDefault();
    let currentStep = parseInt($('.config-steps li.active').data('step'));
    let prevStep = currentStep - 1;
    if (prevStep < 1) return;

    $.get(soeasyVars.themeUrl + '/configurateur/step-' + prevStep + '.php', function (data) {
      $('#config-step-content').html(data);
      $('.config-steps li').removeClass('active');
      $('.config-steps li[data-step="' + prevStep + '"]').addClass('active');
      togglePrevButton(prevStep);
      updatePrices();
      updateRecapitulatif();
      updateSidebarProduitsRecap();
      updateSidebarTotauxRecap();
    });
  });

  // === Navigation onglets adresse ===
  $(document).on('click', '.onglet-nav li', function () {
    const adresse = $(this).data('adresse');
    $('.onglet-nav li').removeClass('active');
    $(this).addClass('active');
    $('.contenu-adresse').removeClass('active');
    $('.contenu-adresse[data-adresse="' + adresse + '"]').addClass('active');
  });

  // === Désactivation du bouton précédent si étape 1
  function togglePrevButton(step) {
    if (step <= 1) {
      $('.btn-prev').hide();
    } else {
      $('.btn-prev').show();
    }
  }

  // === Rendu du nav-pills / affichage des étapes
  function renderNavPills(currentStep) {
    const etapes = {
      1: 'Adresses',
      2: 'Internet',
      3: 'Téléphonie Mobile',
      4: 'Téléphonie Fixe',
      5: 'Récapitulatif'
    };

    const $ul = $('<ul class="config-steps nav nav-pills justify-content-center mb-4"></ul>');

    Object.entries(etapes).forEach(([step, label]) => {
      step = parseInt(step);
      const $li = $('<li class="nav-item"></li>').attr('data-step', step);

      if (step < currentStep) {
        const $a = $('<a class="nav-link completed" href="#"></a>')
          .attr('data-go-step', step)
          .text(`${step}. ${label}`);
        $li.append($a);
      } else {
        const $span = $('<span class="nav-link"></span>')
          .toggleClass('active', step === currentStep)
          .text(`${step}. ${label}`);
        $li.append($span);
      }

      $ul.append($li);
    });

    $('.config-steps-container').empty().append($ul); // <div class="config-steps-container"></div> dans le HTML
  }

  // Navigation vers une étape précédente via les onglets validés
  $(document).on('click', '.config-steps [data-go-step]', function (e) {
    e.preventDefault();
    const targetStep = parseInt($(this).data('go-step'));
    if (!isNaN(targetStep)) {
      loadStep(targetStep);
    }
  });



  // Rechargement automatique de l'étape mémorisée
  const currentStep = localStorage.getItem('soeasyCurrentStep') || '1';
  loadStep(currentStep);

  // Fonction de chargement des étapes
  function loadStep(step) {

    renderNavPills(parseInt(step));

    $('#config-step-content').load(soeasyVars.themeUrl + '/configurateur/step-' + step + '.php?step=' + step, function () {

      // Réinitialisation sélection engagement/financement
      initFinancementSelection();
      initEngagementSelection();

      // Appel des fonctions d'initialisation spécifiques à chaque étape
      const stepInitializers = {
        '1': window.initStep1Events,
        '2': window.initStep2Events,
        '3': window.initStep3Events,
        '4': window.initStep4Events,
        '5': window.initStep5Events
      };
      if (stepInitializers[step]) {
        stepInitializers[step](); // Appel de la fonction
      }

      // Mise à jour des prix et totaux
      updatePrices();
      $('.input-qty').each(function () {
        updatePrixTotal($(this));
      });

      updatePrixProduits();
      updateRecapitulatif();
      updateSidebarProduitsRecap();
      updateSidebarTotauxRecap();
    });
  }


  // Navigation entre les étapes
  $(document).on('click', '.btn-suivant, .btn-precedent', function () {
    const nextStep = $(this).data('step');
    localStorage.setItem('soeasyCurrentStep', nextStep);
    loadStep(nextStep);
  });


  // === Changement du mode de durée d'engagement (radio) ===
  $('#engagement').on('change', function () {
    const duree = $(this).val();
    localStorage.setItem('soeasyEngagement', duree);

    $.post(soeasyVars.ajaxurl, {
      action: 'soeasy_set_engagement',
      duree: duree
    }, function () {
      const activeStep = localStorage.getItem('soeasyCurrentStep') || '1';
      loadStep(activeStep);
    });
  });


  // === Changement du mode de financement (radio) ===
  $('input[name="financement"]').on('change', function () {
    const financement = $(this).val();
    localStorage.setItem('soeasyFinancement', financement);

    $.post(soeasyVars.ajaxurl, {
      action: 'soeasy_set_financement',
      mode: financement
    }, function () {
      const activeStep = localStorage.getItem('soeasyCurrentStep') || '1';
      loadStep(activeStep);
    });
  });


  function initFinancementSelection() {
    const financement = localStorage.getItem('soeasyFinancement') || 'comptant';
    $(`input[name="financement"][value="${financement}"]`).prop('checked', true);
  }

  function initEngagementSelection() {
    const engagement = localStorage.getItem('soeasyEngagement') || '0';
    $('#engagement').val(engagement);
  }

  $(document).on('input change', 'input[type="number"]', function () {
    let total = 0;
    $('input[name^="quantite_forfait_"], input[name^="quantite_data_"]').each(function () {
      const qty = parseInt($(this).val()) || 0;
      total += qty;
    });
    $('[id^="nb-lignes-"]').text(total);
  });

  // Actualise nombre de lignes pour l’option 5G
  function update5GQuantite(index) {
    let total = 0;
    $(`input[name^="quantite_forfait_"][name$="_${index}"], input[name^="quantite_data_"][name$="_${index}"]`).each(function () {
      total += parseInt($(this).val()) || 0;
    });

    $(`#nb-lignes-${index}`).text(total);

    // Bloquer la case à cocher si total = 0
    const $checkbox = $(`#option5g_${index}`);
    const $alert = $(`#alert-5g-${index}`);
    const $prix = $(`#prix-5g-block-${index}`);

    if (total === 0) {
      $checkbox.prop('checked', false);
      $checkbox.prop('disabled', false); // On laisse cochable mais bloqué par erreur
      $prix.addClass('d-none');
    }

    // Gérer affichage prix si coché
    if ($checkbox.is(':checked') && total > 0) {
      $prix.removeClass('d-none');
    } else {
      $prix.addClass('d-none');
    }
  }

  // Empêche activation de 5G sans forfaits
  $(document).on('change', '.option-5g-checkbox', function () {
    const index = $(this).data('index');
    let total = 0;

    $(`input[name^="quantite_forfait_"][name$="_${index}"], input[name^="quantite_data_"][name$="_${index}"]`).each(function () {
      total += parseInt($(this).val()) || 0;
    });

    if (total === 0) {
      $(`#alert-5g-${index}`).removeClass('d-none');
      $(this).prop('checked', false);
    } else {
      $(`#alert-5g-${index}`).addClass('d-none');
      update5GQuantite(index);
    }
  });

  // Quand on modifie des quantités
  $(document).on('input change', 'input[type="number"]', function () {
    $('[id^="nb-lignes-"]').each(function () {
      const index = $(this).attr('id').split('-')[2];
      update5GQuantite(index);
    });
  });

  function updateFraisCentrex(index) {
    let total = 0;
    $(`input[name^="quantite_licence_"][name$="_${index}"]`).each(function () {
      const qty = parseInt($(this).val()) || 0;
      total += qty;
    });

    const $message = $(`#content-${index} .frais-centrex-message`);
    if (total >= 20) {
      $message.removeClass('text-danger').addClass('text-success').text("Frais de mise en service offerts ✅");
    } else {
      $message.removeClass('text-success').addClass('text-danger').text("Des frais de 20 € s’appliquent par licence (moins de 20 utilisateurs).");
    }
  }

  // Mise à jour en temps réel des quantités
  $(document).on('input change', 'input[name^="quantite_licence_"]', function () {
    $('[id^="content-"]').each(function () {
      const index = $(this).attr('id').split('-')[1];
      updateFraisCentrex(index);
    });
  });

  window.initStep1Events = function () {

    initGoogleAutocomplete();
    
    // Ajout d’adresse
    $('#form-ajout-adresse').on('submit', function (e) {
      e.preventDefault();

      const adresse = $('#adresse').val();
      const services = [];
      $('input[name="services[]"]:checked').each(function () {
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
        success: function (response) {
          if (response.success) {
            // Insérer le HTML des adresses mises à jour dans la liste
            $('#liste-adresses').html(`
              <h5>Adresses enregistrées :</h5>
              <ul class="list-group mb-4">
                ${response.data.html}
              </ul>
            `);
            $('#adresse').val('');
            $('input[name="services[]"]').prop('checked', false);
          } else {
            alert("Erreur : " + response.data);
          }
        },
        error: function () {
          alert("Une erreur technique est survenue.");
        }
      });
    });

    // Supprimer une adresse
    $(document).on('click', '.btn-remove-adresse', function () {
      const index = $(this).data('index');

      $.ajax({
        url: soeasyVars.ajaxurl,
        type: 'POST',
        data: {
          action: 'soeasy_remove_adresse_configurateur',
          index: index
        },
        success: function () {
          location.reload(); // ou actualisation dynamique
        }
      });
    });
  }

  window.initStep2Events = function () {

    // Radio – Forfait Internet
    $(document).on('change', 'input[name^="forfait_internet_"]', function () {
      const index = $(this).attr('name').split('_')[2];
      const productId = $(this).val();

      const $input = $(this);
      const $produit = $input.closest('.border');
      let prix = parseFloat($produit.find('.prix-affiche').data('unit'));

      const prixLeasing0 = parseFloat($input.data('prix-leasing-0')) || 0;
      const prixLeasing24 = parseFloat($input.data('prix-leasing-24')) || 0;
      const prixLeasing36 = parseFloat($input.data('prix-leasing-36')) || 0;
      const prixLeasing48 = parseFloat($input.data('prix-leasing-48')) || 0;
      const prixLeasing63 = parseFloat($input.data('prix-leasing-63')) || 0;

      if (isNaN(prix)) prix = 0;

      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_set_forfait_internet',
        index: index,
        product_id: productId
      });

      const produits = [];
      produits.push({
        type: 'internet',
        nom: $produit.find('strong').text().trim(),
        details: $produit.find('.text-muted').text().trim(),
        quantite: 1,
        prixUnitaire: prix,
        prixComptant: prix,
        prixLeasing0,
        prixLeasing24,
        prixLeasing36,
        prixLeasing48,
        prixLeasing63
      });

      saveToLocalConfig(index, 'abonnements', produits, { replace: true, type: 'internet' });
    });


    // Checkbox – Équipements Internet
    $(document).on('change', '.equipement-checkbox', function () {
      const index = $(this).data('index');
      const produits = [];
      const product_ids = [];

      $(`input[name="equipement_${index}[]"]:checked`).each(function () {
        const $checkbox = $(this);
        const $label = $checkbox.closest('label');

        const prixComptant = parseFloat($label.data('prix-comptant')) || 0;
        const prixLeasing0 = parseFloat($label.data('prix-leasing-0')) || 0;
        const prixLeasing24 = parseFloat($label.data('prix-leasing-24')) || 0;
        const prixLeasing36 = parseFloat($label.data('prix-leasing-36')) || 0;
        const prixLeasing48 = parseFloat($label.data('prix-leasing-48')) || 0;
        const prixLeasing63 = parseFloat($label.data('prix-leasing-63')) || 0;

        let prixUnitaire = parseFloat($label.find('.prix-affiche').data('unit')) || 0;

        produits.push({
          type: 'equipement-internet',
          nom: $label.find('strong').text().trim(),
          description: $label.find('.text-muted').text().trim(),
          quantite: 1,
          prixUnitaire: prixUnitaire,
          prixComptant: prixComptant,
          prixLeasing0: prixLeasing0,
          prixLeasing24: prixLeasing24,
          prixLeasing36: prixLeasing36,
          prixLeasing48: prixLeasing48,
          prixLeasing63: prixLeasing63
        });

        product_ids.push($checkbox.val());
      });

      // Envoi côté PHP
      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_set_equipements_internet',
        index: index,
        product_ids: product_ids
      });

      // Sauvegarde localStorage
      saveToLocalConfig(index, 'materiels', produits, { replace: true, type: 'equipement-internet' });
    });
  }


  window.initStep3Events = function () {

    // Quantité modifiée
    $(document).on('input change', '.step-3 .input-qty', function () {
      const $input = $(this);
      const qty = parseInt($input.val()) || 0;
      const index = $input.data('index');
      const id = $input.data('id');

      const $checkbox = $(`.forfait-checkbox[data-id="${id}"][data-index="${index}"]`);
      $checkbox.prop('checked', qty > 0);

      updatePrixTotal($input);
      saveMobileQuantites(index);
    });

    // Checkbox modifiée
    $(document).on('change', '.step-3 .forfait-checkbox', function () {
      const index = $(this).data('index');
      const id = $(this).data('id');
      const $input = $(`.input-qty[data-id="${id}"][data-index="${index}"]`);

      if ($(this).is(':checked')) {
        if (parseInt($input.val()) === 0) $input.val(1).trigger('change');
      } else {
        $input.val(0).trigger('change');
      }
    });

    function saveMobileQuantites(index) {
      const data = {
        forfaits_mobile: [],
        forfaits_data: [],
        equipements_mobile: []
      };

      const abonnements = [];
      const materiels = [];

      $(`.input-qty[data-index="${index}"]`).each(function () {
        const $input = $(this);
        const qty = parseInt($input.val()) || 0;
        const id = $input.data('id');
        const type = $input.data('type');
        if (qty <= 0) return;

        const name = $input.attr('name');
        const $produit = $input.closest('.border');
        const prix = parseFloat($produit.find('.prix-total').data('unit')) || 0;

        const nom = $produit.find('label').text().trim();
        const desc = $produit.find('.text-muted').first().text().trim();

        // Récupération des prix pour leasing
        const prixComptant = parseFloat($produit.data('prix-comptant')) || 0;
        const prixLeasing0 = parseFloat($produit.data('prix-leasing-0')) || 0;
        const prixLeasing24 = parseFloat($produit.data('prix-leasing-24')) || 0;
        const prixLeasing36 = parseFloat($produit.data('prix-leasing-36')) || 0;
        const prixLeasing48 = parseFloat($produit.data('prix-leasing-48')) || 0;
        const prixLeasing63 = parseFloat($produit.data('prix-leasing-63')) || 0;

        if (name.includes('forfait_mobile')) {
          data.forfaits_mobile.push({ id, qty });
          abonnements.push({
            type: 'forfait-mobile',
            nom: nom,
            details: desc,
            quantite: qty,
            prixUnitaire: prix,
            prixComptant,
            prixLeasing0,
            prixLeasing24,
            prixLeasing36,
            prixLeasing48,
            prixLeasing63
          });

        } else if (name.includes('forfait_data')) {
          data.forfaits_data.push({ id, qty });
          abonnements.push({
            type: 'forfait-data',
            nom: nom,
            details: desc,
            quantite: qty,
            prixUnitaire: prix,
            prixComptant,
            prixLeasing0,
            prixLeasing24,
            prixLeasing36,
            prixLeasing48,
            prixLeasing63
          });

        } else if (name.includes('equipement')) {
          data.equipements_mobile.push({ id, qty });
          materiels.push({
            type: 'equipement-mobile',
            nom: nom,
            description: desc,
            quantite: qty,
            prixUnitaire: prix,
            prixComptant,
            prixLeasing0,
            prixLeasing24,
            prixLeasing36,
            prixLeasing48,
            prixLeasing63
          });
        }
      });

      // Envoi AJAX
      Object.entries(data).forEach(([key, items]) => {
        if (items.length > 0) {
          $.post(soeasyVars.ajaxurl, {
            action: `soeasy_set_${key}`,
            index: index,
            items: items
          });
        }
      });

      // Enregistrement dans le localStorage
      saveToLocalConfig(index, 'abonnements', abonnements);
      saveToLocalConfig(index, 'materiels', materiels);
    }
  };


  window.initStep4Events = function () {

    // Quantité modifiée
    $(document).on('input change', '.step-4 .input-qty', function () {
      const $input = $(this);
      const qty = parseInt($input.val()) || 0;
      const index = $input.data('index');
      const id = $input.data('id');

      const $checkbox = $(`.centrex-checkbox[data-id="${id}"][data-index="${index}"]`);
      $checkbox.prop('checked', qty > 0);

      updatePrixTotal($input);
      saveCentrexQuantites(index);
    });

    // Checkbox modifiée
    $(document).on('change', '.step-4 .centrex-checkbox', function () {
      const index = $(this).data('index');
      const id = $(this).data('id');
      const $input = $(`.input-qty[data-id="${id}"][data-index="${index}"]`);

      if ($(this).is(':checked')) {
        if (parseInt($input.val()) === 0) $input.val(1).trigger('change');
      } else {
        $input.val(0).trigger('change');
      }
    });

    function saveCentrexQuantites(index) {
      const data = {
        licences_centrex: [],
        services_centrex: [],
        postes_centrex: [],
        switchs_centrex: [],
        accessoires_centrex: []
      };

      const abonnements = [];
      const materiels = [];

      $(`.input-qty[data-index="${index}"]`).each(function () {
        const $input = $(this);
        const qty = parseInt($input.val()) || 0;
        const id = $input.data('id');
        const type = $input.data('type');
        if (qty <= 0) return;

        const name = $input.attr('name');
        const $produit = $input.closest('.border');
        const prix = parseFloat($produit.find('.prix-total').data('unit')) || 0;

        const nom = $produit.find('label').text().trim();
        const desc = $produit.find('.text-muted').first().text().trim();

        const prixComptant = parseFloat($produit.data('prix-comptant')) || 0;
        const prixLeasing0 = parseFloat($produit.data('prix-leasing-0')) || 0;
        const prixLeasing24 = parseFloat($produit.data('prix-leasing-24')) || 0;
        const prixLeasing36 = parseFloat($produit.data('prix-leasing-36')) || 0;
        const prixLeasing48 = parseFloat($produit.data('prix-leasing-48')) || 0;
        const prixLeasing63 = parseFloat($produit.data('prix-leasing-63')) || 0;

        if (name.includes('licence')) {
          data.licences_centrex.push({ id, qty });
          abonnements.push({
            type: 'forfait-centrex',
            nom: nom,
            details: desc,
            quantite: qty,
            prixUnitaire: prix,
            prixComptant,
            prixLeasing0,
            prixLeasing24,
            prixLeasing36,
            prixLeasing48,
            prixLeasing63
          });
        } else if (name.includes('service')) {
          data.services_centrex.push({ id, qty });
          materiels.push({
            type: 'service-centrex',
            nom: nom,
            description: desc,
            quantite: qty,
            prixUnitaire: prix,
            prixComptant,
            prixLeasing0,
            prixLeasing24,
            prixLeasing36,
            prixLeasing48,
            prixLeasing63
          });
        } else if (name.includes('poste')) {
          data.postes_centrex.push({ id, qty });
          materiels.push({
            type: 'poste-centrex',
            nom: nom,
            description: desc,
            quantite: qty,
            prixUnitaire: prix,
            prixComptant,
            prixLeasing0,
            prixLeasing24,
            prixLeasing36,
            prixLeasing48,
            prixLeasing63
          });
        } else if (name.includes('switch')) {
          data.switchs_centrex.push({ id, qty });
          materiels.push({
            type: 'switch-centrex',
            nom: nom,
            description: desc,
            quantite: qty,
            prixUnitaire: prix,
            prixComptant,
            prixLeasing0,
            prixLeasing24,
            prixLeasing36,
            prixLeasing48,
            prixLeasing63
          });
        } else if (name.includes('accessoire')) {
          data.accessoires_centrex.push({ id, qty });
          materiels.push({
            type: 'accessoire-centrex',
            nom: nom,
            description: desc,
            quantite: qty,
            prixUnitaire: prix,
            prixComptant,
            prixLeasing0,
            prixLeasing24,
            prixLeasing36,
            prixLeasing48,
            prixLeasing63
          });
        }
      });

      Object.entries(data).forEach(([key, items]) => {
        if (items.length > 0) {
          $.post(soeasyVars.ajaxurl, {
            action: `soeasy_set_${key}`,
            index: index,
            items: items
          });
        }
      });

      saveToLocalConfig(index, 'abonnements', abonnements);
      saveToLocalConfig(index, 'materiels', materiels);
    }
  };

  window.initStep5Events = function () {
    const mode = getSelectedFinancementMode();
    $('[id^="th-prix-unitaire"]').text(mode === 'leasing' ? 'Prix unitaire / mois' : 'Prix unitaire');
    $('[id^="th-prix-total"]').text(mode === 'leasing' ? 'Total / mois' : 'Total');

    $('#btn-commander').on('click', function () {
      const $btn = $(this);
      $btn.prop('disabled', true).text('Chargement...');

      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_ajouter_au_panier'
      }).done(function (response) {
        if (response.success) {
          window.location.href = '/panier';
        } else {
          showToastError(response.data?.message || 'Vérifiez votre configuration.');
          $btn.prop('disabled', false).text('Commander');
        }
      }).fail(function () {
        showToastError('Erreur serveur. Veuillez réessayer.');
        $btn.prop('disabled', false).text('Commander');
      });
    });

    function showToastError(message) {
      const toastEl = document.getElementById('toast-error');
      if (toastEl) {
        toastEl.querySelector('.toast-body').textContent = message;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
      }
    }
  };


});
