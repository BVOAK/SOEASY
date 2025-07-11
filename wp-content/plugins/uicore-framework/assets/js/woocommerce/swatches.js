uicoreJsonp([27],{

/***/ 251:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


window.addEventListener('DOMContentLoaded', function () {

    window.uicore_swatch_action = function (event) {
        //get the current attribute's name, value and store it
        var attribute_name = jQuery(event.currentTarget).data('attribute-name');

        //get the value of the clicked swatch child
        var value = jQuery(event.currentTarget).data('value');

        //set the select with the id of the clicked swatch attribute to same value
        jQuery('select[id="' + attribute_name + '"]').val(value).trigger('change');

        //set the active class to the clicked swatch and remove from siblings
        jQuery(event.currentTarget).addClass('selected').siblings().removeClass('selected');
    };

    // Listen to click on swatches list
    jQuery('.uicore-swatch').on('click', uicore_swatch_action);
});

/***/ })

},[251]);