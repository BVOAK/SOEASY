uicoreJsonp([31],{

/***/ 288:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 *  Woo Single Product Add to Cart script for Editor screen
 */

window.addEventListener('DOMContentLoaded', function () {
    var UicoreProductAddtoCart = function (_elementorModules$fro) {
        _inherits(UicoreProductAddtoCart, _elementorModules$fro);

        function UicoreProductAddtoCart() {
            _classCallCheck(this, UicoreProductAddtoCart);

            return _possibleConstructorReturn(this, (UicoreProductAddtoCart.__proto__ || Object.getPrototypeOf(UicoreProductAddtoCart)).apply(this, arguments));
        }

        _createClass(UicoreProductAddtoCart, [{
            key: 'triggerAddtoCartinEditor',
            value: function triggerAddtoCartinEditor() {

                if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                    // wc_add_to_cart_variation_params is required to continue, ensure the object exists
                    //trigger wc_variation_form
                    jQuery('.variations_form').each(function () {
                        jQuery(this).wc_variation_form();
                    });

                    //trigger our swatches script again
                    jQuery('.uicore-swatch').on('click', window.uicore_swatch_action);

                    //trigger quantity input script
                    window.uicore_add_quantity_input_buttons();
                }
            }
        }, {
            key: 'bindEvents',
            value: function bindEvents() {
                this.triggerAddtoCartinEditor();
            }
        }, {
            key: 'onElementChange',
            value: function onElementChange(propertyName) {
                var $properties = ['post_id'];
                if ($properties.includes(propertyName)) {
                    this.triggerAddtoCartinEditor();
                }
            }
        }]);

        return UicoreProductAddtoCart;
    }(elementorModules.frontend.handlers.Base);

    jQuery(window).on('elementor/frontend/init', function () {
        var addHandler = function addHandler($element) {
            elementorFrontend.elementsHandler.addHandler(UicoreProductAddtoCart, {
                $element: $element
            });
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/uicore-woo-product-add-to-cart.default', addHandler);
    });
});

/***/ })

},[288]);