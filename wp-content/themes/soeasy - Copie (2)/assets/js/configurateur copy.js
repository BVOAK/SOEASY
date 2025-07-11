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
      5: "Frais d'installation",
      6: 'Récapitulatif'
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
        '5': window.initStep5Events,
        '6': window.initStep6Events
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


  // Étape 2 – Internet
  window.initStep2Events = function () {
    // 🔁 Restaurer les forfaits cochés depuis localStorage
    const config = JSON.parse(localStorage.getItem('soeasyConfig') || '{}');
    Object.entries(config).forEach(([index, data]) => {
      const forfait = (data.abonnements || []).find(p => p.type === 'internet');
      if (forfait) {
        $(`input[name="forfait_internet_${index}"][value="${forfait.id}"]`).prop('checked', true);
      }
    });

    function deselectionnerEquipementsInvisibles(index) {
      $(`[data-equipement-index="${index}"]`).each(function () {
        const $bloc = $(this);
        const $checkbox = $bloc.find('.equipement-checkbox');
        if (!$bloc.is(':visible') && !$checkbox.data('obligatoire')) {
          $checkbox.prop('checked', false);
        }
      });
    }

    $(document).on('change', 'input[name^="forfait_internet_"]', function () {
      const $input = $(this);
      const index = $input.data('index');
      if (typeof index === 'undefined') return;

      const productId = $input.val();
      const equipementsIds = $input.data('equipements') || [];
      const secoursIds = $input.data('secours') || [];
      const $produit = $input.closest('.border');

      const prix = parseFloat($produit.find('.prix-affiche').data('unit')) || 0;
      const prixLeasing0 = parseFloat($input.data('prix-leasing-0')) || 0;
      const prixLeasing24 = parseFloat($input.data('prix-leasing-24')) || 0;
      const prixLeasing36 = parseFloat($input.data('prix-leasing-36')) || 0;
      const prixLeasing48 = parseFloat($input.data('prix-leasing-48')) || 0;
      const prixLeasing63 = parseFloat($input.data('prix-leasing-63')) || 0;

      $(`#tab-${index} .bloc-equipements`).removeClass('d-none');
      $(`#tab-${index} .bloc-secours`).toggle(secoursIds.length > 0);

      $(`[data-secours-index="${index}"]`).each(function () {
        $(this).toggle(secoursIds.includes(parseInt($(this).data('product-id'))));
      });

      $(`[data-equipement-index="${index}"]`).each(function () {
        $(this).toggle(equipementsIds.includes(parseInt($(this).data('product-id'))));
      });

      deselectionnerEquipementsInvisibles(index);

      $(`[data-equipement-index="${index}"] .equipement-checkbox[data-obligatoire="1"]`).each(function () {
        if ($(this).is(':visible')) $(this).trigger('change');
      });

      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_set_forfait_internet',
        index: index,
        product_id: productId
      });

      const produits = [{
        id: parseInt(productId),
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
      }];

      saveToLocalConfig(index, 'abonnements', produits, { replace: true, type: 'internet' });
    });

    $(document).on('change', '.equipement-checkbox', function () {
      const index = $(this).data('index');
      if (typeof index === 'undefined') return;

      const produits = [];
      const product_ids = [];

      $(`input[name="equipement_${index}[]"]`).each(function () {
        const $checkbox = $(this);
        const $label = $checkbox.closest('label');
        const id = $checkbox.data('id');

        const isVisible = $checkbox.closest('.equipement').is(':visible');
        const isChecked = $checkbox.is(':checked');
        if (!isVisible || !isChecked) return;

        const prixComptant = parseFloat($label.data('prix-comptant')) || 0;
        const prixLeasing0 = parseFloat($label.data('prix-leasing-0')) || 0;
        const prixLeasing24 = parseFloat($label.data('prix-leasing-24')) || 0;
        const prixLeasing36 = parseFloat($label.data('prix-leasing-36')) || 0;
        const prixLeasing48 = parseFloat($label.data('prix-leasing-48')) || 0;
        const prixLeasing63 = parseFloat($label.data('prix-leasing-63')) || 0;
        const prixUnitaire = parseFloat($label.find('.prix-affiche').data('unit')) || 0;

        produits.push({
          id: parseInt(id),
          type: 'equipement-internet',
          nom: $label.find('strong').text().trim(),
          description: $label.find('.text-muted').text().trim(),
          quantite: 1,
          prixUnitaire,
          prixComptant,
          prixLeasing0,
          prixLeasing24,
          prixLeasing36,
          prixLeasing48,
          prixLeasing63
        });

        product_ids.push(id);
      });

      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_set_equipements_internet',
        index: index,
        product_ids: product_ids
      });

      saveToLocalConfig(index, 'materiels', produits, { replace: true, type: 'equipement-internet' });
    });

    // ▶️ Déclenche automatiquement le changement
    $('input[name^="forfait_internet_"]:checked').trigger('change');
  };


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
      if (typeof index === 'undefined') return;

      const mapProduits = {
        'forfait-mobile': [],
        'forfait-data': [],
        'equipement-mobile': []
      };

      const sessionPayload = {
        forfaits_mobile: [],
        forfaits_data: [],
        equipements_mobile: []
      };

      $(`.input-qty[data-index="${index}"]`).each(function () {
        const $input = $(this);
        const qty = parseInt($input.val()) || 0;
        const id = $input.data('id');
        const type = $input.data('type');
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

        const produit = {
          id: parseInt(id),
          type,
          nom,
          description: desc,
          quantite: qty,
          prixUnitaire: prix,
          prixComptant,
          prixLeasing0,
          prixLeasing24,
          prixLeasing36,
          prixLeasing48,
          prixLeasing63
        };

        if (name.includes('forfait_mobile')) {
          if (qty > 0) {
            sessionPayload.forfaits_mobile.push({ id, qty });
            mapProduits['forfait-mobile'].push(produit);
          }
        } else if (name.includes('forfait_data')) {
          if (qty > 0) {
            sessionPayload.forfaits_data.push({ id, qty });
            mapProduits['forfait-data'].push(produit);
          }
        } else if (name.includes('equipement')) {
          if (qty > 0) {
            sessionPayload.equipements_mobile.push({ id, qty });
            mapProduits['equipement-mobile'].push(produit);
          }
        }
      });

      // Envoi AJAX côté PHP
      Object.entries(sessionPayload).forEach(([key, items]) => {
        $.post(soeasyVars.ajaxurl, {
          action: `soeasy_set_${key}`,
          index: index,
          items: items
        });
      });

      // Mise à jour du localStorage
      Object.entries(mapProduits).forEach(([type, produits]) => {
        saveToLocalConfig(index, 'materiels', produits, { replace: true, type });
      });
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

    // Fonction de filtrage dynamique des switchs réseau
    function filtrerSwitchsCentrex(index) {
      const nbLignes = $(`input[name^="quantite_licence_"][data-index="${index}"]`).toArray().reduce((acc, el) => {
        return acc + (parseInt($(el).val()) || 0);
      }, 0);

      const $blocGlobal = $(`#tab-${index} .bloc-switch`);
      const tolerance = 3;

      if (nbLignes === 0) {
        $blocGlobal.addClass('d-none');
        $blocGlobal.find('.input-qty').val('').trigger('input').trigger('change');
        $blocGlobal.find('.centrex-checkbox[data-role="switch-centrex"]').prop('checked', false);
        saveCentrexQuantites(index);
        return;
      }

      $blocGlobal.removeClass('d-none');

      let switchMinimum = null;
      let portsMin = Infinity;

      const $switches = $blocGlobal.find('.blocSwitch');

      $switches.each(function () {
        const $col = $(this);
        const $checkbox = $col.find('.centrex-checkbox[data-role="switch-centrex"]');
        const $inputQty = $col.find('.input-qty');
        const ports = parseInt($checkbox.data('nombre-ports')) || 0;

        if (ports >= nbLignes + tolerance && ports < portsMin) {
          switchMinimum = { col: $col, checkbox: $checkbox, inputQty: $inputQty };
          portsMin = ports;
        }
      });

      $switches.each(function () {
        const $col = $(this);
        const $checkbox = $col.find('.centrex-checkbox[data-role="switch-centrex"]');
        const $inputQty = $col.find('.input-qty');
        const id = $checkbox.data('id');

        if (switchMinimum && $col.is(switchMinimum.col)) {
          $col.show();
          $checkbox.prop('checked', true);

          if (!parseInt($inputQty.val())) {
            $inputQty.val(1).trigger('input').trigger('change');
          }

          // force enregistrement dans localStorage
          $inputQty.trigger('change');

        } else {
          $col.hide();
          $checkbox.prop('checked', false);
          $inputQty.val('').trigger('input').trigger('change');

          // supprime immédiatement du localStorage
          let config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
          if (config[index] && config[index].materiels) {
            config[index].materiels = config[index].materiels.filter(item => item.id != id);
            localStorage.setItem('soeasyConfig', JSON.stringify(config));
          }
        }
      });

      // Sauvegarde finale
      saveCentrexQuantites(index);
    }

    // Déclenche le filtrage à chaque modif de quantité de licence
    $(document).on('input change', 'input[name^="quantite_licence_"]', function () {
      const index = $(this).data('index');
      filtrerSwitchsCentrex(index);
    });

    // Déclenche au chargement initial
    $('[id^="tab-"]').each(function () {
      const index = $(this).attr('id').split('-')[1];
      filtrerSwitchsCentrex(index);
    });


    function saveCentrexQuantites(index) {
      if (typeof index === 'undefined') return;

      const mapProduits = {
        'forfait-centrex': [],
        'service-centrex': [],
        'poste-centrex': [],
        'switch-centrex': [],
        'accessoire-centrex': []
      };

      const sessionPayload = {
        licences_centrex: [],
        services_centrex: [],
        postes_centrex: [],
        switchs_centrex: [],
        accessoires_centrex: []
      };

      $(`.input-qty[data-index="${index}"], .input-qty[data-switch-index="${index}"]`).each(function () {
        const $input = $(this);
        if ($input.data('skip-save')) return;
        if ($input.data('forced-reset')) {
          $input.removeData('forced-reset');
          return;
        }

        const qty = parseInt($input.val()) || 0;
        const id = $input.data('id');
        const name = $input.attr('name');
        const type = $input.data('type');
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

        const produit = {
          id: parseInt(id),
          type,
          nom,
          description: desc,
          quantite: qty,
          prixUnitaire: prix,
          prixComptant,
          prixLeasing0,
          prixLeasing24,
          prixLeasing36,
          prixLeasing48,
          prixLeasing63
        };

        if (name.includes('licence')) {
          if (qty > 0) {
            sessionPayload.licences_centrex.push({ id, qty });
            mapProduits['forfait-centrex'].push(produit);
          }
        } else if (name.includes('service')) {
          if (qty > 0) {
            sessionPayload.services_centrex.push({ id, qty });
            mapProduits['service-centrex'].push(produit);
          }
        } else if (name.includes('poste')) {
          if (qty > 0) {
            sessionPayload.postes_centrex.push({ id, qty });
            mapProduits['poste-centrex'].push(produit);
          }
        } else if (name.includes('switch')) {
          if (qty > 0) {
            sessionPayload.switchs_centrex.push({ id, qty });
            mapProduits['switch-centrex'].push(produit);
          }
        } else if (name.includes('accessoire')) {
          if (qty > 0) {
            sessionPayload.accessoires_centrex.push({ id, qty });
            mapProduits['accessoire-centrex'].push(produit);
          }
        }
      });

      // Envoi vers PHP
      Object.entries(sessionPayload).forEach(([key, items]) => {
        $.post(soeasyVars.ajaxurl, {
          action: `soeasy_set_${key}`,
          index,
          items
        });
      });

      // Sauvegarde locale avec nettoyage par type
      Object.entries(mapProduits).forEach(([type, produits]) => {
        saveToLocalConfig(index, 'materiels', produits, { replace: true, type });
      });
    }



  };

  window.initStep5Events = function () {

    // Clic sur un frais d’installation
    $(document).on('change', '.frais-checkbox', function () {
      const index = $(this).data('index');
      const frais = [];

      $(`.frais-installation-list[data-index="${index}"] .frais-checkbox:checked`).each(function () {
        const $cb = $(this);
        frais.push({
          id: parseInt($cb.data('id')),
          nom: $cb.closest('li').find('label').text().trim(),
          prixComptant: parseFloat($cb.data('prix-comptant')) || 0,
          prixLeasing24: parseFloat($cb.data('prix-leasing-24')) || 0,
          prixLeasing36: parseFloat($cb.data('prix-leasing-36')) || 0,
          prixLeasing48: parseFloat($cb.data('prix-leasing-48')) || 0,
          prixLeasing63: parseFloat($cb.data('prix-leasing-63')) || 0
        });
      });

      saveToLocalConfig(index, 'fraisInstallation', frais);
    });

    // Clic sur la case "je préfère voir plus tard"
    $(document).on('change', '.report-frais-checkbox', function () {
      const index = $(this).data('index');

      if ($(this).is(':checked')) {
        // On désactive tous les frais cochés
        $(`.frais-installation-list[data-index="${index}"] .frais-checkbox`).prop('checked', false);
        saveToLocalConfig(index, 'fraisInstallation', { reporte: true });
      } else {
        // Si décochée → relancer la détection des cases cochées
        $(`.frais-installation-list[data-index="${index}"] .frais-checkbox`).first().trigger('change');
      }
    });

    // Chargement initial : recharger depuis localStorage
    Object.entries(JSON.parse(localStorage.getItem('soeasyConfig') || '{}')).forEach(([index, config]) => {
      if (!config?.fraisInstallation) return;

      const frais = config.fraisInstallation;

      if (frais.reporte) {
        $(`#report_frais_${index}`).prop('checked', true);
        $(`.frais-installation-list[data-index="${index}"] .frais-checkbox`).prop('checked', false);
      } else {
        // cocher uniquement ceux présents dans la config
        const ids = frais.map(f => parseInt(f.id));
        $(`.frais-installation-list[data-index="${index}"] .frais-checkbox`).each(function () {
          const id = parseInt($(this).data('id'));
          $(this).prop('checked', ids.includes(id));
        });
      }
    });

  };


  window.initStep6Events = function () {
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
