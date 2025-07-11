uicoreJsonp([29],{

/***/ 289:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 *  Woo Single Product Tabs script for Editor screen
 *  TODO: not working on editor. After fixing it, we can remove the 'render_type' => 'template' from the tab style control
 */

window.addEventListener('DOMContentLoaded', function () {
    var UicoreProductTabs = function (_elementorModules$fro) {
        _inherits(UicoreProductTabs, _elementorModules$fro);

        function UicoreProductTabs() {
            _classCallCheck(this, UicoreProductTabs);

            return _possibleConstructorReturn(this, (UicoreProductTabs.__proto__ || Object.getPrototypeOf(UicoreProductTabs)).apply(this, arguments));
        }

        _createClass(UicoreProductTabs, [{
            key: 'triggerTabsInEditor',
            value: function triggerTabsInEditor() {
                jQuery('.wc-tabs-wrapper, .woocommerce-tabs, #rating').trigger('init');
            }
        }, {
            key: 'bindEvents',
            value: function bindEvents() {
                if (elementorFrontend.isEditMode()) {
                    this.triggerTabsInEditor();
                }
            }
        }, {
            key: 'onElementChange',
            value: function onElementChange(propertyName) {
                console.log('Triggered on editor');
                var $properties = ['post_id', 'tabs'];
                if ($properties.includes(propertyName)) {
                    this.triggerTabsInEditor();
                }
            }
        }]);

        return UicoreProductTabs;
    }(elementorModules.frontend.handlers.Base);

    jQuery(window).on('elementor/frontend/init', function () {
        var addHandler = function addHandler($element) {
            elementorFrontend.elementsHandler.addHandler(UicoreProductTabs, {
                $element: $element
            });
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/uicore-woo-product-tabs.default', addHandler);
    });
});

/***/ })

},[289]);