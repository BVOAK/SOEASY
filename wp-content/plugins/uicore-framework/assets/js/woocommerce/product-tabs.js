uicoreJsonp([28],{

/***/ 252:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/**
 *  Product Tabs Accordion script
 */

document.addEventListener('click', function (e) {

    if (e.target.classList.contains('ui-accordion-header')) {

        var panel = e.target.nextElementSibling;
        var isOpen = panel.style.display === 'block';

        // Close all panels
        document.querySelectorAll('.woocommerce-Tabs-panel').forEach(function (p) {
            p.style.display = 'none';
        });

        // Remove active class from all headers
        document.querySelectorAll('.ui-accordion-header').forEach(function (h) {
            h.classList.remove('ui-active');
        });

        // Open the clicked panel
        if (!isOpen) {
            panel.style.display = 'block';
            e.target.classList.add('ui-active');
        }
    }
});

/***/ })

},[252]);