<?php
defined("ABSPATH") || exit();
//INCLUDED IN CLASS JS

$toggle = $settings["header_sd_toggle"];

$js .= '
(function ($) {
    $(document).ready(function () {
        // Add plus and minus buttons to each quantity input element
        window.uicore_add_quantity_input_buttons = () => {
            $(".cart .quantity input:not([type=\'hidden\'])").each(function () {
                $(this).after(\'<div class="minus">-</div>\');
                $(this).before(\'<div class="plus">+</div>\');
            });

            // Add click event listeners to the plus and minus buttons
            $(".cart .plus").on("click", function () {
                var $quantity = $(this).siblings("input");
                var currentVal = parseFloat($quantity.val());
                var max = parseFloat($quantity.attr("max"));
                var step = parseFloat($quantity.attr("step"));

                if (!isNaN(currentVal)) {
                    if (max && currentVal + step > max) {
                        $quantity.val(max);
                    } else {
                        $quantity.val((currentVal + step).toFixed(0));
                    }
                    }
                    if (isNaN(currentVal)) {
                        $quantity.val("1");
                    }
                    $quantity.trigger("change");
            });

            $(".cart .minus").on("click", function () {
                var $quantity = $(this).siblings("input");
                var currentVal = parseFloat($quantity.val());
                var min = parseFloat($quantity.attr("min"));
                var step = parseFloat($quantity.attr("step"));

                if (!isNaN(currentVal) && currentVal > min) {
                    $quantity.val((currentVal - step).toFixed(0));
                    $quantity.trigger("change");
                }
                if (isNaN(currentVal)) {
                    $quantity.val("0");
                    $quantity.trigger("change");
                }
            });
        };

        // Run the function
        uicore_add_quantity_input_buttons();
    });
})(jQuery);
  
';