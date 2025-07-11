uicoreJsonp([30],{

/***/ 287:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 *  Woo Single Product Gallery script for Editor screen
 */

window.addEventListener('DOMContentLoaded', function () {
    var UicoreProductGallery = function (_elementorModules$fro) {
        _inherits(UicoreProductGallery, _elementorModules$fro);

        function UicoreProductGallery() {
            _classCallCheck(this, UicoreProductGallery);

            return _possibleConstructorReturn(this, (UicoreProductGallery.__proto__ || Object.getPrototypeOf(UicoreProductGallery)).apply(this, arguments));
        }

        _createClass(UicoreProductGallery, [{
            key: 'triggerGalleryInEditor',
            value: function triggerGalleryInEditor() {
                // The `wc_single_product_params` constant is defined as inline script in the widget `render()` function
                jQuery('.woocommerce-product-gallery').each(function () {
                    jQuery(this).trigger('wc-product-gallery-before-init', [this, wc_single_product_params]);
                    jQuery(this).wc_product_gallery(wc_single_product_params);
                    jQuery(this).trigger('wc-product-gallery-after-init', [this, wc_single_product_params]);
                });

                // Disable lightbox for UX purposes
                jQuery('.woocommerce-product-gallery__image').css('pointer-events', 'none');
            }
        }, {
            key: 'bindEvents',
            value: function bindEvents() {
                this.triggerGalleryInEditor();
            }
        }, {
            key: 'onElementChange',
            value: function onElementChange(propertyName) {
                var $properties = ['gallery_style', 'post_id'];
                if ($properties.includes(propertyName)) {
                    this.triggerGalleryInEditor();
                }
            }
        }]);

        return UicoreProductGallery;
    }(elementorModules.frontend.handlers.Base);

    jQuery(window).on('elementor/frontend/init', function () {
        var addHandler = function addHandler($element) {
            elementorFrontend.elementsHandler.addHandler(UicoreProductGallery, {
                $element: $element
            });
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/uicore-woo-product-gallery.default', addHandler);
    });
});

/***/ })

},[287]);