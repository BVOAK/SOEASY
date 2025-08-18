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

    // Cas particulier : injection des FI AVANT affichage de l'étape 5
    if (parseInt(step) === 5) {
      const config = JSON.parse(localStorage.getItem('soeasyConfig') || '{}');
      const ajaxCalls = [];

      Object.entries(config).forEach(([index, data]) => {
        const frais = data.fraisInstallation || [];
        if (frais.length > 0) {
          ajaxCalls.push(
            $.post(soeasyVars.ajaxurl, {
              action: 'soeasy_set_frais_installation',
              index,
              items: frais
            })
          );
        }
      });

      Promise.all(ajaxCalls).then(() => {
        renderStep(step);
      });

    } else {
      renderStep(step);
    }

    function renderStep(step) {
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
      /* $('input[name="services[]"]:checked').each(function () {
        services.push($(this).val());
      }); */

      if (adresse.length === 0 /* || services.length === 0 */) {
        alert("Merci de renseigner une adresse.");
        return;
      }

      $.ajax({
        url: soeasyVars.ajaxurl,
        type: 'POST',
        data: {
          action: 'soeasy_add_adresse_configurateur',
          adresse: adresse
          //services: services
        },
        success: function (response) {
          if (response.success) {
            // Mise à jour HTML
            $('#liste-adresses').html(`
            <h5>Adresses enregistrées :</h5>
            <ul class="list-group mb-4">
              ${response.data.html}
            </ul>
          `);

            $('#adresse').val('');
            $('input[name="services[]"]').prop('checked', false);

            // MàJ du localStorage
            const adresses = JSON.parse(localStorage.getItem('soeasyAdresses')) || [];
            adresses.push({ adresse: adresse });
            localStorage.setItem('soeasyAdresses', JSON.stringify(adresses));
            updateSidebarProduitsRecap();

            $(".btn-suivant").removeClass("disabled");

          } else {
            alert("Erreur : " + response.data);
          }
        },
        error: function () {
          alert("Une erreur technique est survenue.");
        }
      });

    });

    // Suppression d’une adresse
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
          // Mise à jour du localStorage aussi
          const adresses = JSON.parse(localStorage.getItem('soeasyAdresses')) || [];
          adresses.splice(index, 1);
          localStorage.setItem('soeasyAdresses', JSON.stringify(adresses));

          location.reload();
        },
        error: function () {
          alert("Une erreur technique est survenue.");
        }
      });
    });
  };



  // Étape 2 – Internet
  window.initStep2Events = function () {
    // Reset des anciens événements
    $(document).off('input change', '.step-2 .forfait-internet-checkbox');
    $(document).off('input change', '.step-2 .equipement-checkbox');
    $('.forfait-internet-checkbox, .equipement-checkbox').off();

    $('.forfait-internet-checkbox').prop('checked', false);
    $('.equipement-checkbox').prop('checked', false);

    function deselectionnerEquipementsInvisibles(index) {
      $(`[data-equipement-index="${index}"]`).each(function () {
        const $bloc = $(this);
        const $checkbox = $bloc.find('.equipement-checkbox');
        if (!$bloc.is(':visible') && !$checkbox.data('obligatoire')) {
          $checkbox.prop('checked', false);
        }
      });
    }

    function updateFraisInstallationFromInternet(index) {
      const config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
      const data = config[index];
      if (!data) return;

      const equipementsIds = (data.materiels || []).map(p => p.id);
      const tousFrais = window.fraisInstallationInternetParProduit || {};

      const fraisMap = {}; // clé = id du frais

      equipementsIds.forEach(id => {
        if (Array.isArray(tousFrais[id])) {
          tousFrais[id].forEach(f => {
            const fid = f.id;
            if (!fraisMap[fid]) {
              fraisMap[fid] = { ...f, type: 'internet' };
            } else {
              fraisMap[fid].quantite += f.quantite || 1;
            }
          });
        }
      });

      config[index].fraisInstallation = Object.values(fraisMap);
      localStorage.setItem('soeasyConfig', JSON.stringify(config));

      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_set_frais_installation',
        index,
        items: Object.values(fraisMap)
      });
    }


    $(document).on('change', '.forfait-internet-checkbox', function () {
      const $clicked = $(this);
      const index = $clicked.data('index');

      if ($clicked.is(':checked')) {
        $(`.forfait-internet-checkbox[data-index="${index}"]`).not($clicked).prop('checked', false);
        $clicked.prop('checked', true).trigger('soeasy.selectForfaitInternet');
      } else {
        // suppression du forfait Internet
        const config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};

        if (config[index]) {
          // Supprimer les abonnements de type 'internet'
          if (Array.isArray(config[index].abonnements)) {
            config[index].abonnements = config[index].abonnements.filter(p => p.type !== 'internet');
          }

          // Supprimer les abonnements de type 'internet'
          if (Array.isArray(config[index].materiels)) {
            config[index].materiels = config[index].materiels.filter(p => p.type !== 'equipement-internet');
          }

          // Supprimer les frais d’installation de type 'internet'
          if (Array.isArray(config[index].fraisInstallation)) {
            config[index].fraisInstallation = config[index].fraisInstallation.filter(f => f.type !== 'internet');
          }

          localStorage.setItem('soeasyConfig', JSON.stringify(config));
        }

        // AJAX : suppression côté session
        $.post(soeasyVars.ajaxurl, {
          action: 'soeasy_set_forfait_internet',
          index: index,
          product_id: 0
        });
        $.post(soeasyVars.ajaxurl, {
          action: 'soeasy_set_equipements_internet',
          index: index,
          product_id: 0
        });
        $.post(soeasyVars.ajaxurl, {
          action: 'soeasy_set_frais_installation',
          index: index,
          items: []
        });

        // Cache les blocs
        $(`#tab-${index} .bloc-equipements, #tab-${index} .bloc-secours`).addClass('d-none').hide();

        // Réinitialise sidebar
        updateSidebarProduitsRecap();
        updateSidebarTotauxRecap();
      }
    });


    $(document).on('soeasy.selectForfaitInternet', '.forfait-internet-checkbox', function () {
      const $input = $(this);
      const index = $input.data('index');
      if (typeof index === 'undefined') return;

      const prdID = parseInt($input.data('id') || $input.val());
      if (!prdID || isNaN(prdID)) return;

      const equipementsIds = $input.data('equipements') || [];
      const secoursIds = $input.data('secours') || [];
      const $produit = $input.closest('.border');

      const prix = parseFloat($produit.find('.prix-affiche').data('unit')) || 0;
      const prixLeasing0 = parseFloat($input.data('prix-leasing-0')) || 0;
      const prixLeasing24 = parseFloat($input.data('prix-leasing-24')) || 0;
      const prixLeasing36 = parseFloat($input.data('prix-leasing-36')) || 0;
      const prixLeasing48 = parseFloat($input.data('prix-leasing-48')) || 0;
      const prixLeasing63 = parseFloat($input.data('prix-leasing-63')) || 0;

      $(`#tab-${index} .bloc-equipements`).toggleClass('d-none', equipementsIds.length === 0).toggle(equipementsIds.length > 0);
      $(`#tab-${index} .bloc-secours`).toggleClass('d-none', secoursIds.length === 0).toggle(secoursIds.length > 0);

      $(`[data-secours-index="${index}"]`).each(function () {
        $(this).toggle(secoursIds.includes(parseInt($(this).data('id'))));
      });

      $(`[data-equipement-index="${index}"]`).each(function () {
        $(this).toggle(equipementsIds.includes(parseInt($(this).data('id'))));
      });

      deselectionnerEquipementsInvisibles(index);

      setTimeout(() => {
        $(`[data-equipement-index="${index}"] .equipement-checkbox[data-obligatoire="1"]`).each(function () {
          if ($(this).is(':visible')) {
            $(this).prop('checked', true).trigger('change');
          }
        });
      }, 100);

      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_set_forfait_internet',
        index: index,
        product_id: prdID
      });

      const produits = [{
        id: prdID,
        type: 'internet',
        nom: $produit.find('strong').text().trim(),
        description: $produit.find('.text-muted').text().trim(),
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

      updateSidebarProduitsRecap();
      updateEngagementVisibility();

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
      updateFraisInstallationFromInternet(index);

      if (produits.length === 0) {
        saveToLocalConfig(index, 'fraisInstallation', [], { replace: true });
      }

      Object.keys(config).forEach(index => {
        updateSidebarProduitsRecap();
      });
    });

    // Restauration au chargement
    const config = JSON.parse(localStorage.getItem('soeasyConfig') || '{}');
    Object.entries(config).forEach(([index, data]) => {
      if (!data || typeof data !== 'object') return;

      const forfait = (data.abonnements || []).find(p => p.type === 'internet');
      if (forfait && forfait.id) {
        const $input = $(`.forfait-internet-checkbox[data-index="${index}"][value="${forfait.id}"]`);
        if ($input.length) {
          $input.prop('checked', true).trigger('soeasy.selectForfaitInternet');
        }
      }

      (data.materiels || []).forEach(p => {
        const $checkbox = $(`.equipement-checkbox[data-index="${index}"][data-id="${p.id}"]`);
        if ($checkbox.length) {
          $checkbox.prop('checked', true);
        }
      });
    });

  };

  window.initStep3Events = function () {

    // Supprimer les anciens événements pour éviter les empilements
    $(document).off('input change', '.step-3 .input-qty');
    $(document).off('mouseup', '.step-3 .input-qty');
    $(document).off('change', '.step-3 .forfait-checkbox');

    resetAllStep3CheckboxesAndInputs();
    restoreStep3FromStorage();

    $(document).on('input change', '.step-3 .input-qty', function () {
      const $input = $(this);
      const qty = parseInt($input.val()) || 0;
      const index = $input.data('index');
      const id = $input.data('id');
      const $checkbox = $(`.forfait-checkbox[data-id="${id}"][data-index="${index}"]`);

      $checkbox.prop('checked', qty > 0);

      if (qty === 0) {
        removeProductFromLocalStorage(index, id);
      } else {
        if (typeof saveMobileQuantites === 'function') saveMobileQuantites(index);
      }
    });

    $(document).on('mouseup', '.step-3 .input-qty', function () {
      const $input = $(this);
      setTimeout(() => {
        $input.trigger('change');
      }, 100);
    });

    $(document).on('change', '.step-3 .forfait-checkbox', function () {
      const index = $(this).data('index');
      const id = $(this).data('id');
      const $input = $(`.input-qty[data-id="${id}"][data-index="${index}"]`);

      if ($(this).is(':checked')) {
        if (parseInt($input.val()) === 0) {
          $input.val(1).trigger('change');
        }
      } else {
        $input.val(0).trigger('change');
      }
    });

    function resetAllStep3CheckboxesAndInputs() {
      $('.step-3 .forfait-checkbox').prop('checked', false);
      $('.step-3 .input-qty').val(0);
    }


    function restoreStep3FromStorage() {
      const config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};

      Object.entries(config).forEach(([index, data]) => {
        const allProduits = [
          ...(data.abonnements || []),
          ...(data.materiels || [])
        ];

        allProduits.forEach(prod => {
          const $input = $(`.step-3 .input-qty[data-index="${index}"][data-id="${prod.id}"]`);
          const $checkbox = $(`.step-3 .forfait-checkbox[data-index="${index}"][data-id="${prod.id}"]`);

          if ($input.length) $input.val(prod.quantite);
          if ($checkbox.length) $checkbox.prop('checked', prod.quantite > 0);
        });
      });
    }

    function removeProductFromLocalStorage(index, id) {
      const config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
      if (!config[index]) return;

      ['abonnements', 'materiels'].forEach(section => {
        if (Array.isArray(config[index][section])) {
          config[index][section] = config[index][section].filter(p => p.id !== id);
        }
      });

      localStorage.setItem('soeasyConfig', JSON.stringify(config));
    }

    function saveMobileQuantites(index) {
      if (typeof index === 'undefined') return;

      const sessionPayload = {
        forfaits_mobile: [],
        forfaits_data: [],
        equipements_mobile: []
      };

      const produitsParSection = {
        abonnements: [],
        materiels: []
      };

      $(`.input-qty[data-index="${index}"]`).each(function () {
        const $input = $(this);
        const qty = parseInt($input.val()) || 0;
        const id = $input.data('id');
        const name = $input.attr('name');
        const typeAttr = $input.data('type');
        if (!typeAttr || qty <= 0) return;

        const $produit = $input.closest('.border');
        const nom = $produit.find('label').text().trim();
        const desc = $produit.find('.text-muted').first().text().trim();
        const prix = parseFloat($produit.find('.prix-total').data('unit')) || 0;

        const prixComptant = parseFloat($produit.data('prix-comptant')) || 0;
        const prixLeasing0 = parseFloat($produit.data('prix-leasing-0')) || 0;
        const prixLeasing24 = parseFloat($produit.data('prix-leasing-24')) || 0;
        const prixLeasing36 = parseFloat($produit.data('prix-leasing-36')) || 0;
        const prixLeasing48 = parseFloat($produit.data('prix-leasing-48')) || 0;
        const prixLeasing63 = parseFloat($produit.data('prix-leasing-63')) || 0;

        const produit = {
          id: parseInt(id),
          type: $input.data('type-complet'),
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
          sessionPayload.forfaits_mobile.push({ id, qty });
        } else if (name.includes('forfait_data')) {
          sessionPayload.forfaits_data.push({ id, qty });
        } else if (name.includes('equipement')) {
          sessionPayload.equipements_mobile.push({ id, qty });
        }

        const section = (typeAttr === 'forfait') ? 'abonnements' : 'materiels';
        produitsParSection[section].push(produit);
      });

      Object.entries(sessionPayload).forEach(([key, items]) => {
        $.post(soeasyVars.ajaxurl, {
          action: `soeasy_set_${key}`,
          index,
          items
        });
      });

      Object.entries(produitsParSection).forEach(([section, produits]) => {
        const types = new Set(produits.map(p => p.type));
        types.forEach(type => {
          const produitsFiltres = produits.filter(p => p.type === type);
          saveToLocalConfig(index, section, produitsFiltres, { replace: true, type });
        });
      });
    }
  };

  window.initStep4Events = function () {
    $(document).off('input change', '.step-4 .input-qty');
    $(document).off('mouseup', '.step-4 .input-qty');
    $(document).off('change', '.step-4 .centrex-checkbox');
    $(document).off('input change', 'input[name^="quantite_poste_"]');

    resetAllStep4CheckboxesAndInputs();
    restoreStep4FromStorage();

    $(document).on('input change', '.step-4 .input-qty', function () {
      const $input = $(this);
      const qty = parseInt($input.val()) || 0;
      const index = $input.data('index') ?? $input.data('switch-index');
      const id = $input.data('id');
      const $checkbox = $(`.centrex-checkbox[data-id="${id}"][data-index="${index}"]`);

      $checkbox.prop('checked', qty > 0);

      if (qty === 0) removeProductFromLocalStorage(index, id);
      saveCentrexQuantites(index);
    });

    $(document).on('mouseup', '.step-4 .input-qty', function () {
      const $input = $(this);
      setTimeout(() => $input.trigger('change'), 100);
    });

    $(document).on('change', '.step-4 .centrex-checkbox', function () {
      const index = $(this).data('index');
      const id = $(this).data('id');
      const $input = $(`.input-qty[data-id="${id}"][data-index="${index}"], .input-qty[data-id="${id}"][data-switch-index="${index}"]`);
      if ($(this).is(':checked')) {
        if (parseInt($input.val()) === 0) $input.val(1).trigger('change');
      } else {
        $input.val(0).trigger('change');
      }
    });

    function resetAllStep4CheckboxesAndInputs() {
      $('.step-4 .centrex-checkbox').prop('checked', false);
      $('.step-4 .input-qty').val(0);
    }

    function restoreStep4FromStorage() {
      const config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
      Object.entries(config).forEach(([index, data]) => {
        const allProduits = [...(data.abonnements || []), ...(data.materiels || [])];
        allProduits.forEach(prod => {
          const inputSelector = `.step-4 .input-qty[data-index="${index}"][data-id="${prod.id}"], .step-4 .input-qty[data-switch-index="${index}"][data-id="${prod.id}"]`;
          const checkboxSelector = `.step-4 .centrex-checkbox[data-index="${index}"][data-id="${prod.id}"]`;

          const $input = $(inputSelector);
          const $checkbox = $(checkboxSelector);

          if ($input.length) {
            $input.val(prod.quantite).trigger('input'); // force mise à jour
          }

          if ($checkbox.length) {
            $checkbox.prop('checked', prod.quantite > 0);
          }
        });
      });
    }

    function removeProductFromLocalStorage(index, id) {
      const config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
      if (!config[index]) return;

      ['abonnements', 'materiels'].forEach(section => {
        if (Array.isArray(config[index][section])) {
          config[index][section] = config[index][section].filter(p => p.id !== id);
        }
      });

      // Ne supprime que les frais liés à 'centrex'
      if (Array.isArray(config[index].fraisInstallation)) {
        config[index].fraisInstallation = config[index].fraisInstallation.filter(f => f.type !== 'centrex');
      }

      localStorage.setItem('soeasyConfig', JSON.stringify(config));
    }

    function filtrerSwitchsCentrex(index) {
      const nbLignes = $(`input[name^="quantite_poste_"][data-index="${index}"]`)
        .toArray()
        .reduce((acc, el) => acc + (parseInt($(el).val()) || 0), 0);

      const $blocGlobal = $(`#tab-${index} .bloc-switch`);
      const tolerance = 3;

      if (nbLignes === 0) {
        $blocGlobal.addClass('d-none');
        $blocGlobal.find('.input-qty').val('').trigger('input');
        $blocGlobal.find('.centrex-checkbox[data-role="switch-centrex"]').prop('checked', false);
        saveCentrexQuantites(index);
        return;
      }

      $blocGlobal.removeClass('d-none');

      let switchMinimum = null;
      let portsMin = Infinity;

      $blocGlobal.find('.blocSwitch').each(function () {
        const $col = $(this);
        const $checkbox = $col.find('.centrex-checkbox[data-role="switch-centrex"]');
        const $inputQty = $col.find('.input-qty');
        const ports = parseInt($checkbox.data('nombre-ports')) || 0;

        if (ports >= nbLignes + tolerance && ports < portsMin) {
          switchMinimum = { col: $col, checkbox: $checkbox, inputQty: $inputQty };
          portsMin = ports;
        }
      });

      $blocGlobal.find('.blocSwitch').each(function () {
        const $col = $(this);
        const $checkbox = $col.find('.centrex-checkbox[data-role="switch-centrex"]');
        const $inputQty = $col.find('.input-qty');
        const id = $checkbox.data('id');

        if (switchMinimum && $col.is(switchMinimum.col)) {
          $col.show();
          $checkbox.prop('checked', true);
          if (!parseInt($inputQty.val())) $inputQty.val(1).trigger('input');
          else $inputQty.trigger('input');
        } else {
          $col.hide();
          $checkbox.prop('checked', false);
          $inputQty.val('').trigger('input');
          const config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
          if (config[index] && config[index].materiels) {
            config[index].materiels = config[index].materiels.filter(item => item.id != id);
            localStorage.setItem('soeasyConfig', JSON.stringify(config));
          }
        }
      });
    }

    $(document).on('input change', 'input[name^="quantite_poste_"]', function () {
      const index = $(this).data('index');
      filtrerSwitchsCentrex(index);
    });

    $('[id^="tab-"]').each(function () {
      const index = $(this).attr('id').split('-')[1];
      filtrerSwitchsCentrex(index);
    });

    function saveCentrexQuantites(index) {
      if (typeof index === 'undefined') return;

      const sessionPayload = {
        licences_centrex: [],
        services_centrex: [],
        postes_centrex: [],
        switchs_centrex: [],
        accessoires_centrex: []
      };

      const produitsParSection = { abonnements: [], materiels: [] };

      const normalise = str => (str || '').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, '');

      // — 1. Lecture de tous les champs actifs
      $(`.input-qty[data-index="${index}"], .input-qty[data-switch-index="${index}"]`).each(function () {
        const $input = $(this);
        const qty = parseInt($input.val()) || 0;
        const id = parseInt($input.data('id'));
        const typeAttr = $input.data('type');
        const sousType = $input.data('sous-type') || typeAttr;
        const name = $input.attr('name');

        if (!typeAttr || qty <= 0) return;

        const $produit = $input.closest('.border');
        const nom = $produit.find('label').text().trim();
        const desc = $produit.find('.text-muted').first().text().trim();
        const prix = parseFloat($produit.find('.prix-total').data('unit')) || 0;

        const prixComptant = parseFloat($produit.data('prix-comptant')) || 0;
        const prixLeasing0 = parseFloat($produit.data('prix-leasing-0')) || 0;
        const prixLeasing24 = parseFloat($produit.data('prix-leasing-24')) || 0;
        const prixLeasing36 = parseFloat($produit.data('prix-leasing-36')) || 0;
        const prixLeasing48 = parseFloat($produit.data('prix-leasing-48')) || 0;
        const prixLeasing63 = parseFloat($produit.data('prix-leasing-63')) || 0;

        const produit = {
          id,
          type: sousType,
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

        if (name.includes('licence')) sessionPayload.licences_centrex.push({ id, qty });
        if (name.includes('service')) sessionPayload.services_centrex.push({ id, qty });
        if (name.includes('poste')) sessionPayload.postes_centrex.push({ id, qty });
        if (name.includes('switch')) sessionPayload.switchs_centrex.push({ id, qty });
        if (name.includes('accessoire')) sessionPayload.accessoires_centrex.push({ id, qty });

        const section = (typeAttr === 'forfait') ? 'abonnements' : 'materiels';
        produitsParSection[section].push(produit);
      });

      // — 2. Envoi AJAX (session PHP)
      Object.entries(sessionPayload).forEach(([key, items]) => {
        if (items.length > 0) {
          $.post(soeasyVars.ajaxurl, {
            action: `soeasy_set_${key}`,
            index,
            items
          });
        }
      });

      // — 3. Sauvegarde dans le localStorage (abonnements / matériels)
      Object.entries(produitsParSection).forEach(([section, produits]) => {
        const types = new Set(produits.map(p => p.type));
        types.forEach(type => {
          const produitsFiltres = produits.filter(p => p.type === type);
          saveToLocalConfig(index, section, produitsFiltres, { replace: true, type });
        });
      });

      // — 4. Frais d’installation Centrex (postes + licences + matériel)
      const tousFrais = window.fraisInstallationCentrexParProduit || {};
      const config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
      config[index] = config[index] || {};

      const fraisParId = {};
      const postesCentrex = sessionPayload.postes_centrex.reduce((sum, item) => sum + item.qty, 0);
      const licencesCentrex = sessionPayload.licences_centrex.reduce((sum, item) => sum + item.qty, 0);

      const idsLicences = sessionPayload.licences_centrex.map(p => p.id);
      const idsPostes = sessionPayload.postes_centrex.map(p => p.id);
      const idsMateriels = [
        ...sessionPayload.services_centrex,
        ...sessionPayload.switchs_centrex,
        ...sessionPayload.accessoires_centrex
      ].map(p => p.id);

      // a. FI licences
      idsLicences.forEach(id => {
        const fraisAssocies = tousFrais[id] || [];
        fraisAssocies.forEach(f => {
          const nomNorm = normalise(f.nom);
          const isFraisLicence = nomNorm.includes('licence') && nomNorm.includes('centrex');
          if (!isFraisLicence || fraisParId[f.id]) return;

          const seuil = parseInt(f.offertAPartirDe) || 9999;
          const fi = { ...f, type: 'centrex', quantite: 1 };

          if (licencesCentrex >= seuil) {
            fi.prixComptant = 0;
            fi.prixLeasing24 = 0;
            fi.prixLeasing36 = 0;
            fi.prixLeasing48 = 0;
            fi.prixLeasing63 = 0;
          }

          fraisParId[f.id] = fi;
        });
      });

      // b. FI postes (palier min/max)
      let meilleurFraisPoste = null;
      idsPostes.forEach(id => {
        const fraisAssocies = tousFrais[id] || [];
        fraisAssocies.forEach(f => {
          const nomNorm = normalise(f.nom);
          const isFraisPoste = nomNorm.includes('poste') && nomNorm.includes('centrex');
          if (!isFraisPoste) return;

          const min = parseInt(f.minPostes) || 0;
          const max = parseInt(f.maxPostes) || Infinity;

          if (postesCentrex >= min && postesCentrex <= max) {
            if (!meilleurFraisPoste || max < meilleurFraisPoste.maxPostes) {
              meilleurFraisPoste = { ...f, type: 'centrex', quantite: 1, maxPostes: max };
            }
          }
        });
      });
      if (meilleurFraisPoste) {
        delete meilleurFraisPoste.maxPostes;
        fraisParId[meilleurFraisPoste.id] = meilleurFraisPoste;
      }

      // c. FI matériel (fusionner tous les équipements mappés au même frais)
      const compteurFraisMateriel = {}; // { [f.id]: quantite cumulée }

      idsMateriels.forEach(id => {
        const fraisAssocies = tousFrais[id] || [];
        const equipement = sessionPayload.services_centrex.concat(
          sessionPayload.switchs_centrex,
          sessionPayload.accessoires_centrex
        ).find(e => e.id === id);

        const qtyEquipement = equipement?.qty || 1;

        fraisAssocies.forEach(f => {
          const nomNorm = normalise(f.nom);
          const isLicence = nomNorm.includes('licence') && nomNorm.includes('centrex');
          const isPoste = nomNorm.includes('poste') && nomNorm.includes('centrex');
          if (isLicence || isPoste) return;

          if (!compteurFraisMateriel[f.id]) {
            compteurFraisMateriel[f.id] = 0;
          }

          compteurFraisMateriel[f.id] += qtyEquipement;

          fraisParId[f.id] = {
            ...f,
            type: 'centrex',
            quantite: compteurFraisMateriel[f.id]
          };
        });
      });


      // 5. Enregistrement final LS + AJAX
      const autresFrais = (config[index].fraisInstallation || []).filter(f => f.type !== 'centrex');
      config[index].fraisInstallation = [...autresFrais, ...Object.values(fraisParId)];
      localStorage.setItem('soeasyConfig', JSON.stringify(config));

      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_set_frais_installation',
        index,
        items: config[index].fraisInstallation
      });
    }

    // Forcer le recalcul des FI Centrex dès le chargement de Step-4
    $('[id^="tab-"]').each(function () {
      const index = $(this).attr('id').split('-')[1];
      saveCentrexQuantites(index); // Envoi vers localStorage + session PHP
    });

  };



window.initStep5Events = function () {

  $(document).on('change', '.frais-checkbox', function () {
    const index = $(this).data('index');
    $(`#report_frais_${index}`).prop('checked', false); // décocher case report

    const frais = [];

    $(`.frais-installation-list[data-index="${index}"] .frais-checkbox:checked`).each(function () {
      const $cb = $(this);
      frais.push({
        id: parseInt($cb.data('id')),
        nom: $cb.closest('label').clone().children().remove().end().text().trim(),
        quantite: parseInt($cb.data('quantite')) || 1,
        prixComptant: parseFloat($cb.data('prix-comptant')) || 0,
        prixLeasing24: parseFloat($cb.data('prix-leasing-24')) || 0,
        prixLeasing36: parseFloat($cb.data('prix-leasing-36')) || 0,
        prixLeasing48: parseFloat($cb.data('prix-leasing-48')) || 0,
        prixLeasing63: parseFloat($cb.data('prix-leasing-63')) || 0
      });
    });

    saveToLocalConfig(index, 'fraisInstallation', frais, { replace: true });
    updatePrices();
    updateFraisTotal(index);
    updateSidebarProduitsRecap();
    updateSidebarTotauxRecap();
  });

  $(document).on('change', '.report-frais-checkbox', function () {
    const index = $(this).data('index');
    const $fraisCheckboxes = $(`.frais-installation-list[data-index="${index}"] .frais-checkbox`);

    if ($(this).is(':checked')) {
      $fraisCheckboxes.prop('checked', false);
      saveToLocalConfig(index, 'fraisInstallation', []); // ⬅️ rollback ici
      $(`.frais-total[data-index="${index}"]`).text('0 €');
    } else {
      $fraisCheckboxes.first().trigger('change');
    }

    updateFraisTotal(index);
    updateSidebarProduitsRecap();
    updateSidebarTotauxRecap();
  });

  $('.frais-installation-list').each(function () {
    const $list = $(this);
    const index = $list.data('index');
    const config = JSON.parse(localStorage.getItem('soeasyConfig') || '{}');
    const frais = config[index]?.fraisInstallation;

    if (Array.isArray(frais) && frais.length > 0) {
      frais.forEach(item => {
        const $cb = $list.find(`.frais-checkbox[data-id="${item.id}"]`);
        $cb.prop('checked', true);
        $cb.data('quantite', item.quantite || 1);
        $cb.data('prix-comptant', item.prixComptant || 0);
        $cb.data('prix-leasing-24', item.prixLeasing24 || 0);
        $cb.data('prix-leasing-36', item.prixLeasing36 || 0);
        $cb.data('prix-leasing-48', item.prixLeasing48 || 0);
        $cb.data('prix-leasing-63', item.prixLeasing63 || 0);
      });
    } else {
      $list.find('.frais-checkbox:checked').first().trigger('change');
    }

    updateFraisTotal(index);
  });
};






  window.initStep6Events = function () {
    const mode = getSelectedFinancementMode();
    const engagement = getSelectedEngagement();

    // Mettre à jour les en-têtes des colonnes
    $('[id^="th-prix-unitaire"]').text(mode === 'leasing' ? 'Prix unitaire / mois' : 'Prix unitaire');
    $('[id^="th-prix-total"]').text(mode === 'leasing' ? 'Total / mois' : 'Total');

    // Recharger le contenu des tableaux
    updateRecapitulatif();

    // Recalculer les totaux par adresse
    const config = JSON.parse(localStorage.getItem('soeasyConfig')) || {};
    Object.entries(config).forEach(([index, data]) => {
      const $card = $(`#recapitulatif-final .card`).eq(index);
      let totalComptant = 0;
      let totalMensuel = 0;

      // Abonnements
      (data.abonnements || []).forEach(item => {
        totalMensuel += (item.prixUnitaire || 0) * item.quantite;
      });

      // Matériels
      (data.materiels || []).forEach(item => {
        totalComptant += (item.prixComptant || 0) * item.quantite;
        if (mode === 'leasing') {
          totalMensuel += (item[`prixLeasing${engagement}`] || 0) * item.quantite;
        }
      });

      // Frais d'installation
      (data.fraisInstallation || []).forEach(item => {
        totalComptant += (item.prixComptant || 0) * item.quantite;
        if (mode === 'leasing') {
          totalMensuel += (item[`prixLeasing${engagement}`] || 0) * item.quantite;
        }
      });

      $card.find('.total-comptant')
        .text(`Total comptant : ${totalComptant.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €`);
      $card.find('.total-mensuel')
        .text(`Total mensuel : ${totalMensuel.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} € / mois`);
    });

    // Bouton Commander
    $('#btn-commander').on('click', function () {
      const $btn = $(this).prop('disabled', true).text('Chargement...');
      $.post(soeasyVars.ajaxurl, { action: 'soeasy_ajouter_au_panier' })
        .done(response => {
          if (response.success) {
            window.location.href = '/panier';
          } else {
            showToastError(response.data?.message || 'Vérifiez votre configuration.');
            $btn.prop('disabled', false).text('Commander');
          }
        })
        .fail(() => {
          showToastError('Erreur serveur. Veuillez réessayer.');
          $btn.prop('disabled', false).text('Commander');
        });
    });

    function showToastError(message) {
      const toastEl = document.getElementById('toast-error');
      if (toastEl) {
        toastEl.querySelector('.toast-body').textContent = message;
        new bootstrap.Toast(toastEl).show();
      }
    }
  };

});
