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

  // === Changement du mode de financement (radio) ===
  $(document).on('change', 'input[name="financement"]', function () {

    updatePrices();
    $('.input-qty').each(function () {
      updatePrixTotal($(this));
    });

    updateRecapitulatif();
  });

  // === Changement de la durée d'engagement ===
  $(document).on('change', '#engagement', function () {

    updatePrices();
    $('.input-qty').each(function () {
      updatePrixTotal($(this));
    });

    updateRecapitulatif();
  });

  // === Désactivation du bouton précédent si étape 1
  function togglePrevButton(step) {
    if (step <= 1) {
      $('.btn-prev').hide();
    } else {
      $('.btn-prev').show();
    }
  }


  // Rechargement automatique de l'étape mémorisée
  const currentStep = localStorage.getItem('soeasyCurrentStep') || '1';
  loadStep(currentStep);

  // Fonction de chargement des étapes
  function loadStep(step) {
    $('#config-step-content').load(soeasyVars.themeUrl + '/configurateur/step-' + step + '.php', function () {
      
      // Mise à jour des onglets
      $('.config-steps .nav-link').removeClass('active');
      $('.config-steps [data-step="' + step + '"] .nav-link').addClass('active');
  
      // Réinitialisation sélection engagement/financement
      initFinancementSelection();
      initEngagementSelection();
  
      // Appel des fonctions d'initialisation spécifiques à chaque étape
      const stepInitializers = {
        '1': window.initStep1Events,
        '2': window.initStep2Events,
        '3': window.initStep3Events,
        '4': window.initStep4Events,
        '5': window.initStep5Events // si tu l’ajoutes plus tard
      };
      if (stepInitializers[step]) {
        stepInitializers[step](); // Appel de la fonction
      }
  
      // Mise à jour des prix et totaux
      updatePrices();
      $('.input-qty').each(function () {
        updatePrixTotal($(this));
      });
  
      // Résumé à droite
      updateRecapitulatif();
    });
  }
  

  // Navigation entre les étapes
  $(document).on('click', '.btn-suivant, .btn-precedent', function () {
    const nextStep = $(this).data('step');
    localStorage.setItem('soeasyCurrentStep', nextStep);
    loadStep(nextStep);
  });


  $('#engagement').on('change', function () {
    const duree = $(this).val();
    localStorage.setItem('soeasyEngagement', duree);

    $.post(soeasyVars.ajaxurl, {
      action: 'soeasy_set_engagement',
      duree: duree
    }, function () {
      const activeStep = localStorage.getItem('soeasyCurrentStep') || '1';
      $('#config-step-content').load(
        soeasyVars.themeUrl + '/configurateur/step-' + activeStep + '.php'
      );
    });
  });


  $('input[name="financement"]').on('change', function () {
    const financement = $(this).val();
    localStorage.setItem('soeasyFinancement', financement);

    $.post(soeasyVars.ajaxurl, {
      action: 'soeasy_set_financement',
      mode: financement
    }, function () {
      const activeStep = localStorage.getItem('soeasyCurrentStep') || '1';
      $('#config-step-content').load(
        soeasyVars.themeUrl + '/configurateur/step-' + activeStep + '.php'
      );
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

      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_set_forfait_internet',
        index: index,
        product_id: productId
      });
    });

    // Checkbox – Équipements Internet
    $(document).on('change', 'input[name^="equipement_"]', function () {
      const index = $(this).attr('name').split('_')[1];
      const selected = $(`input[name="equipement_${index}[]"]:checked`)
        .map(function () {
          return $(this).val();
        }).get();

      $.post(soeasyVars.ajaxurl, {
        action: 'soeasy_set_equipements_internet',
        index: index,
        product_ids: selected
      });
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
      saveQuantites(index);
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

    function saveQuantites(index) {
      const data = {
        forfaits_mobile: [],
        forfaits_data: [],
        equipements_mobile: []
      };

      $(`.input-qty[data-index="${index}"]`).each(function () {
        const $input = $(this);
        const qty = parseInt($input.val()) || 0;
        const id = $input.data('id');
        if (qty <= 0) return;

        const name = $input.attr('name');
        const key = name.includes('forfait_mobile') ? 'forfaits_mobile'
          : name.includes('forfait_data') ? 'forfaits_data'
            : 'equipements_mobile';

        data[key].push({ id, qty });
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
    }
  }


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

      const type = $input.attr('name').split('_')[1]; // quantite_licence_X
      saveCentrex(type, index);
    });

    // Checkbox modifiée
    $(document).on('change', '.step-4 .centrex-checkbox', function () {
      const index = $(this).data('index');
      const id = $(this).data('id');
      const $input = $(`input.input-qty[data-id="${id}"][data-index="${index}"]`);

      if ($(this).is(':checked')) {
        if (parseInt($input.val()) === 0) $input.val(1).trigger('change');
      } else {
        $input.val(0).trigger('change');
      }
    });

    // Init prix total
    $('.step-4 .input-qty').each(function () {
      updatePrixTotal($(this));
    });

    function saveCentrex(type, index) {
      const data = [];

      $(`input[name^="quantite_${type}_${index}"]`).each(function () {
        const qty = parseInt($(this).val()) || 0;
        const id = $(this).data('id');
        if (qty > 0) data.push({ id, qty });
      });

      $.post(soeasyVars.ajaxurl, {
        action: `soeasy_set_${type}_centrex`,
        index: index,
        data: data
      });
    }
  }


});
