uicoreJsonp([5],{

/***/ 172:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _blocksTab = __webpack_require__(433);

var _blocksTab2 = _interopRequireDefault(_blocksTab);

var _licenseTab = __webpack_require__(438);

var _licenseTab2 = _interopRequireDefault(_licenseTab);

var _pagesTab = __webpack_require__(441);

var _pagesTab2 = _interopRequireDefault(_pagesTab);

var _previewTab = __webpack_require__(444);

var _previewTab2 = _interopRequireDefault(_previewTab);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

var axios = __webpack_require__(4);
axios.defaults.headers.common['X-WP-Nonce'] = window.uicore_data.nonce;
exports.default = {
    components: {
        blocks: _blocksTab2.default,
        pages: _pagesTab2.default,
        preview: _previewTab2.default,
        license: _licenseTab2.default
    },
    data: function data() {
        return {
            tab: 'purchase',
            currentItem: null,
            backTo: 'pages',
            index: window.uicoreLibIndex,
            url: window.uicore_data.root + '/wp-admin/admin.php?page=uicore#/system',
            postType: 'page',
            isBottom: false
        };
    },
    mounted: function mounted() {
        this.setTab();
    },

    methods: {
        onScroll: function onScroll(_ref) {
            var _ref$target = _ref.target,
                scrollTop = _ref$target.scrollTop,
                clientHeight = _ref$target.clientHeight,
                scrollHeight = _ref$target.scrollHeight;

            if (scrollTop + clientHeight + 80 >= scrollHeight && !this.isBottom) {
                this.isBottom = true;
            } else {
                this.isBottom = false;
            }
        },
        isPurchase: function isPurchase(tabName) {
            if (tabName === 'purchase') {
                return { display: 'none' };
            }
        },
        getTabs: function getTabs() {
            return window.uicore_default;
        },
        purchaseDone: function purchaseDone(e) {
            window.purchase_data = e;
            this.setTab();
        },
        setTab: function setTab() {
            var tab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';

            if (window.purchase_data.token != '') {
                if (tab == 'default') {
                    var posibleTab = Object.keys(this.getTabs())[Object.keys(this.getTabs()).length - 1];
                    posibleTab = posibleTab === 'page' ? 'blocks' : posibleTab;
                    this.tab = posibleTab;
                } else {
                    this.tab = tab;
                }
            }
        },
        initPagesPreview: function initPagesPreview(item) {
            this.backTo = 'pages';
            this.currentItem = item;
            this.setTab('preview');
        },
        initBlocksPreview: function initBlocksPreview(item) {
            this.backTo = 'blocks';
            this.currentItem = item;
            this.setTab('preview');
        },
        backToLibrary: function backToLibrary() {
            if (this.backTo == 'pages') {
                this.setTab();
            } else {
                this.setTab('blocks');
            }
        },
        el: function el(data) {
            var _this = this;

            // TODO: this timeput overwrite fast response from below
            this.setTab('importing');
            setTimeout(function () {
                _this.setTab('importing2');
            }, 5000);
            setTimeout(function () {
                _this.setTab('importing3');
            }, 10000);
            var url = window.uicore_data.wp_json + '/import-library/';
            axios.post(url, { "import": data.id }).then(function (response) {
                if (response.data) {
                    if (response.data.status === 'succes') {
                        console.log(response.data.template);
                        _this.addToPreview(response.data.template);
                    } else if (response.data.status === 'retry') {
                        _this.addToWidgetsCache(response.data.new_widgets);
                        window.elementor.requestWidgetsConfig();
                        _this.el(data);
                    } else {
                        _this.tab = 'error';
                    }
                }
                //error
            }).catch(function (e) {
                _this.tab = 'error';
                console.log(e);
            });
        },
        insert: function insert() {
            this.el(this.currentItem);
        },
        insertFromList: function insertFromList(e) {
            this.el(e);
        },
        addToWidgetsCache: function addToWidgetsCache(widgets) {
            for (var widget in widgets) {
                window.elementor.widgetsCache[widget] = {};
            };
        },
        addToPreview: function addToPreview(e) {
            var i = this.index;

            console.log(i);

            for (var r = 0; r < e.length; r++) {
                var o = $e.internal("document/history/start-log", {
                    type: "add",
                    title: "Add UiCore Library Element"
                });
                var varData = {
                    container: elementor.getPreviewContainer(),
                    model: e[r],
                    options: i >= 0 ? {
                        at: i++
                    } : {}
                };
                console.log(varData);
                $e.run("document/elements/create", varData);

                $e.internal("document/history/end-log", {
                    id: o
                });
            }

            $e.internal('document/save/set-is-modified', {
                status: true
            });

            this.backToLibrary();
            this.removeAdd();
            this.closeIframe();
        },
        closeIframe: function closeIframe() {
            jQuery('#elementor-template-library-modal').remove();
        },
        removeAdd: function removeAdd() {

            var child = window.elementor.$previewContents.find(".elementor-section-wrap.ui-sortable");
            var children = jQuery(child).children();
            if (children.length && children[this.index]) {
                children[this.index].remove();
            }
        },
        adminSettings: function adminSettings(e) {
            this.setTab();
        }
    }
};

/***/ }),

/***/ 173:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _listItem = __webpack_require__(174);

var _listItem2 = _interopRequireDefault(_listItem);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
    name: 'blocks',
    components: {
        item: _listItem2.default
    },
    props: ['isBottom'],
    data: function data() {
        return {
            allBlocks: uicore_blocks,
            search: null,
            select: 'all',
            style: { opacity: 0 },
            showGrid: true,
            loading: false,
            limit: 400,
            doneScrolling: 0
        };
    },

    mounted: function mounted() {
        this.doMaso();
    },

    methods: {
        doMaso: function doMaso() {
            var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'run';

            var _this = this;
            // let _type = type;
            var masoGrid = this.$refs.masoContainer;
            if (typeof masoGrid != 'undefined') {
                var UicoreMaso = new elementorModules.utils.Masonry({
                    container: masoGrid,
                    items: masoGrid.children
                });
                imagesLoaded(masoGrid, function () {
                    UicoreMaso.run();
                    if (type === "run") {
                        _this.style = { opacity: 1, transition: 'opacity .5s' };
                        _this.loading = false;
                    }
                });
            }
        },
        refresh: function refresh() {
            var _this2 = this;

            var filtered = JSON.parse(this.allBlocks);
            //skyp this for lazyload
            if (!this.isBottom && this.limit != filtered.length) {
                this.loading = true;
                this.style = { opacity: 0, transition: 'opacity 0s' };
                setTimeout(function () {
                    _this2.showGrid = false;
                }, 100);
                setTimeout(function () {
                    _this2.showGrid = true;
                }, 102);
                setTimeout(function () {
                    _this2.doMaso('run');
                }, 104);
            } else {
                this.doMaso('lazy');
            }
        },
        emitPrev: function emitPrev(item) {
            this.$emit('preview', item);
        },
        emitIns: function emitIns(item) {
            this.$emit('insert', item);
        }
    },
    computed: {
        BlocksList: function BlocksList() {
            var _this3 = this;

            // this.style = { opacity: 0, transition: 'opacity 0s' };
            var filtered = JSON.parse(this.allBlocks);
            if (this.search) {
                filtered = JSON.parse(this.allBlocks).filter(function (m) {
                    return m.name.toLowerCase().indexOf(_this3.search.toLowerCase()) > -1;
                });
                this.refresh();
                return filtered;
            }
            if (this.select != 'all') {
                filtered = filtered.filter(function (m) {
                    return m.category.toLowerCase() == _this3.select;
                });
                this.refresh();
                return filtered;
            }

            if (this.limit > filtered.length) {
                this.limit = filtered.length;
            }

            if (filtered.length) {
                this.refresh();
            }

            return filtered.slice(0, this.limit);
        }
    },
    watch: {
        isBottom: function isBottom(newVal, oldVal) {
            if (newVal) {
                this.limit = this.limit + 400;
            }
        }
    }
}; //
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/***/ }),

/***/ 174:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_list_item_vue__ = __webpack_require__(175);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_list_item_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_list_item_vue__);
/* harmony namespace reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_list_item_vue__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_list_item_vue__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_89735eb6_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_list_item_vue__ = __webpack_require__(436);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(435)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_list_item_vue___default.a,
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_89735eb6_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_list_item_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/library/list-item.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-89735eb6", Component.options)
  } else {
    hotAPI.reload("data-v-89735eb6", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),

/***/ 175:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

var semver = __webpack_require__(86);
exports.default = {
    name: 'item',
    props: ['itemcontent', 'type'],
    methods: {
        theName: function theName() {
            if (this.itemcontent.name) {
                return this.itemcontent.name;
            }
            if (this.itemcontent.title) {
                return this.itemcontent.title;
            }
        },

        //generate the tag
        themeTag: function themeTag(v) {
            if (typeof v == 'undefined') {
                return {
                    color: 'hide'
                };
            }
            var version = v;
            var current = window.uicore_data.v;
            var tag = {};
            tag.color = 'hide';
            if (semver.gt(version, current)) {
                tag.msg = 'Require Update';
                tag.color = 'red';
            }
            if (semver.eq(version, current)) {
                tag.msg = 'New';
                tag.color = 'green';
            }
            return tag;
        },
        preview: function preview(e) {
            if (this.type === 'block') {
                this.$emit('triggerPreview', e);
            }
        },
        insert: function insert(e) {
            this.$emit('triggerInsert', e);
        },
        update: function update() {
            window.open(window.uicore_data.root + '/wp-admin/admin.php?page=uicore#/updates', '_blank');
        }
    }
};

/***/ }),

/***/ 176:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

var axios = __webpack_require__(4);
axios.defaults.headers.common['X-WP-Nonce'] = uicore_data.nonce;

exports.default = {
    name: 'license',
    data: function data() {
        return {
            uicoreSettings: window.uicore_frontend_data,
            isloading: false,
            purchase_data: window.purchase_data,
            themeName: window.ui_theme_name,
            path: window.path
        };
    },
    methods: {
        verify: function verify() {
            this.isloading = true;
            var thisTheme = this.path.startsWith('uicore-pro') ? this.path : this.themeName;
            window.open('https://my.uicore.co/connect/?ui_connect=true&ui_connect_url=' + window.uicore_data.root + "&ui_connect_product=" + thisTheme, '', '');

            this.checkLocalConnect();
        },
        checkLocalConnect: function checkLocalConnect() {
            var _this2 = this;

            var e = {
                connect: {
                    type: 'local_check'
                }
            };
            var url = uicore_data.wp_json + '/admin';
            axios.post(url, e).then(function (response) {
                if (response.data) {
                    if (response.data.status == 'success') {
                        //DONE
                        _this2.isloading = false;

                        //Update locally for the rest of components
                        _this2.purchase_data = response.data.data;
                        window.purchase_data = response.data.data;
                        _this2.$emit('admin', { connect: response.data.data });
                    } else {
                        var _this = _this2;
                        setTimeout(function () {
                            _this.checkLocalConnect();
                        }, 5000);
                    }
                } else {
                    //ERROR
                }
            }).catch(function (e) {
                console.log(e);
                //ERROR
            });
        }
    }
};

/***/ }),

/***/ 177:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _listItem = __webpack_require__(174);

var _listItem2 = _interopRequireDefault(_listItem);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
    name: 'pages',
    components: {
        item: _listItem2.default
    },
    props: ['isBottom'],
    data: function data() {
        return {
            allBlocks: uicore_extra,
            search: null,
            select: 'all',
            style: { opacity: 0 },
            showGrid: true,
            loading: false,
            limit: 400,
            doneScrolling: 0
        };
    },

    mounted: function mounted() {
        this.doMaso();
    },

    methods: {
        isPages: function isPages() {
            if (typeof window.uicore_default.pages != 'undefined') {
                return true;
            }
            return false;
        },
        doMaso: function doMaso() {
            var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'run';

            var _this = this;
            // let _type = type;
            var masoGrid = this.$refs.masoContainer;
            if (typeof masoGrid != 'undefined') {
                var UicoreMaso = new elementorModules.utils.Masonry({
                    container: masoGrid,
                    items: masoGrid.children
                });
                imagesLoaded(masoGrid, function () {
                    UicoreMaso.run();
                    if (type === "run") {
                        _this.style = { opacity: 1, transition: 'opacity .5s' };
                        _this.loading = false;
                    }
                });
            }
        },
        refresh: function refresh() {
            var _this2 = this;

            //skyp this for lazyload
            if (!this.isBottom) {
                this.loading = true;
                this.style = { opacity: 0, transition: 'opacity 0s' };
                setTimeout(function () {
                    _this2.showGrid = false;
                }, 100);
                setTimeout(function () {
                    _this2.showGrid = true;
                }, 102);
                setTimeout(function () {
                    _this2.doMaso('run');
                }, 104);
            } else {
                this.doMaso('lazy');
            }
        },
        emitPrev: function emitPrev(item) {
            this.$emit('preview', item);
        },
        emitIns: function emitIns(item) {
            this.$emit('insert', item);
        }
    },
    computed: {
        BlocksList: function BlocksList() {
            var _this3 = this;

            // this.style = { opacity: 0, transition: 'opacity 0s' };
            var filtered = JSON.parse(this.allBlocks);
            if (this.search) {
                filtered = JSON.parse(this.allBlocks).filter(function (m) {
                    return m.title.toLowerCase().indexOf(_this3.search.toLowerCase()) > -1;
                });
                this.refresh();
                return filtered;
            }
            if (this.select != 'all') {
                filtered = filtered.filter(function (m) {
                    return m.category.toLowerCase() == _this3.select;
                });
                this.refresh();
                return filtered;
            }

            if (this.limit > filtered.length) {
                this.limit = filtered.length;
            }

            if (filtered.length) {
                this.refresh();
            }

            return filtered.slice(0, this.limit);
        }
    },
    watch: {
        isBottom: function isBottom(newVal, oldVal) {
            if (newVal) {
                this.limit = this.limit + 100;
            }
        }
    }
}; //
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/***/ }),

/***/ 178:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

exports.default = {
    name: 'preview',
    props: ['item'],
    data: function data() {
        return {
            frontendSettings: window.uicore_frontend_data
        };
    },

    mounted: function mounted() {
        this.$refs.prevForm.submit();
    },

    methods: {}
};

/***/ }),

/***/ 430:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _vue = __webpack_require__(11);

var _vue2 = _interopRequireDefault(_vue);

var _library = __webpack_require__(431);

var _library2 = _interopRequireDefault(_library);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_vue2.default.config.productionTip = false;

!function ($) {
    var options;
    options = {
        init: function init() {
            window.elementor.on('preview:loaded', window._.bind(options.onPreviewLoaded, options));
        },
        onPreviewLoaded: function onPreviewLoaded() {
            var _this = this;

            var e = setInterval(function () {
                window.elementor.$previewContents.find(".elementor-add-new-section").length && (_this.addBtn(), clearInterval(e));
            }, 400);
            window.elementor.$previewContents.on("click", ".elementor-editor-element-setting.elementor-editor-element-add", this.addBtn);
        },
        addBtn: function addBtn() {
            var list = window.elementor.$previewContents.find(".elementor-add-new-section");

            var newEl = document.createElement('div');
            newEl.id = "uicore-lib-btn";
            // newEl.style.order = 3;
            newEl.classList.add("uicore-library-button");
            newEl.innerHTML = "<i class='eicon-folder'></i>";

            if (list.length) {
                [].forEach.call(list, function (item, index) {
                    if (!item.querySelector('#uicore-lib-btn')) {
                        item.querySelector(".elementor-add-section-area-button:nth-child(2)").after(newEl);
                    }
                });
                if (!window.isUiCoreLibrary) {
                    window.isUiCoreLibrary = true;
                    window.elementor.$previewContents.on('click', '#uicore-lib-btn', _.bind(options.popup, options));
                }
            }

            // console.log(window.uicore_data.first_time);
            // if(window.uicore_data.first_time === 'true'){
            //     this.popup();
            // }
        },
        popup: function popup() {
            var e = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

            if (!document.querySelector('#uicore-library-wrap')) {
                var frame = '<div class="dialog-widget dialog-lightbox-widget dialog-type-buttons dialog-type-lightbox elementor-templates-modal" id="elementor-template-library-modal" style="display: block;"> <div id="uicore-library-wrap"></div></div>';
                $(frame).appendTo('body');

                //detect position index
                window.uicoreLibIndex = undefined;
                if (e) {
                    var main = $(e.target).closest('.elementor-add-section')[0];
                    var child = window.elementor.$previewContents.find(".elementor-add-section-inline");
                    if (child.length) {
                        window.uicoreLibIndex = Array.from(main.parentNode.children).indexOf(main);
                    }
                }

                var BlocksList = [];
                var PagesList = [];

                new _vue2.default({
                    el: '#uicore-library-wrap',
                    render: function render(h) {
                        return h(_library2.default);
                    }
                });
            }
        }
    };
    var tb = {
        init: function init() {}
    };

    $(window).on('elementor:loaded', options.init);
    // $(window).on('document:loaded', tb.init);
}(jQuery);

//THEMEBUILDER EDIT WIP
// var Preview = /*#__PURE__*/function (_elementorModules$Vie) {
//     (0, _inherits2.default)(Preview, _elementorModules$Vie);

//     var _super = (0, _createSuper2.default)(Preview);

//     function Preview() {
//       var _this;

//       (0, _classCallCheck2.default)(this, Preview);
//       _this = _super.call(this);
//       elementorFrontend.on('components:init', function () {
//         return _this.onFrontendComponentsInit();
//       });
//       return _this;
//     }

//     (0, _createClass2.default)(Preview, [{
//       key: "createDocumentsHandles",
//       value: function createDocumentsHandles() {
//         var _this2 = this;

//         jQuery.each(elementorFrontend.documentsManager.documents, function (index, document) {
//           var $documentElement = document.$element;

//           if ($documentElement.hasClass('elementor-edit-mode')) {
//             return;
//           }

//           var $existingHandle = document.$element.children('.elementor-document-handle');

//           if ($existingHandle.length) {
//             return;
//           }

//           var $handle = jQuery('<div>', {
//             class: 'elementor-document-handle'
//           }),
//               $handleIcon = jQuery('<i>', {
//             class: 'eicon-edit'
//           }),
//               documentTitle = $documentElement.data('elementor-title'),
//               $handleTitle = jQuery('<div>', {
//             class: 'elementor-document-handle__title'
//           }).text(elementorPro.translate('edit_element', [documentTitle]));
//           $handle.append($handleIcon, $handleTitle);
//           $handle.on('click', function () {
//             return _this2.onDocumentHandleClick(document);
//           });
//           $documentElement.prepend($handle);
//         });
//       }
//     }, {
//       key: "onDocumentHandleClick",
//       value: function onDocumentHandleClick(document) {
//         elementorCommon.api.internal('panel/state-loading');
//         elementorCommon.api.run('editor/documents/switch', {
//           id: document.getSettings('id')
//         }).finally(function () {
//           return elementorCommon.api.internal('panel/state-ready');
//         });
//       }
//     }, {
//       key: "onFrontendComponentsInit",
//       value: function onFrontendComponentsInit() {
//         var _this3 = this;

//         this.createDocumentsHandles();
//         elementor.on('document:loaded', function () {
//           return _this3.createDocumentsHandles();
//         });
//       }
//     }]);
//     return Preview;
//   }(elementorModules.ViewModule);

//   exports.default = Preview;
//   window.elementorProPreview = new Preview();

/***/ }),

/***/ 431:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_library_vue__ = __webpack_require__(172);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_library_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_library_vue__);
/* harmony namespace reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_library_vue__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_library_vue__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_36ad5e84_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_library_vue__ = __webpack_require__(447);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(432)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_library_vue___default.a,
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_36ad5e84_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_library_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/library/library.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-36ad5e84", Component.options)
  } else {
    hotAPI.reload("data-v-36ad5e84", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),

/***/ 432:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 433:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_blocks_tab_vue__ = __webpack_require__(173);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_blocks_tab_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_blocks_tab_vue__);
/* harmony namespace reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_blocks_tab_vue__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_blocks_tab_vue__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5da3a0ca_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_blocks_tab_vue__ = __webpack_require__(437);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(434)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_blocks_tab_vue___default.a,
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5da3a0ca_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_blocks_tab_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/library/blocks-tab.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-5da3a0ca", Component.options)
  } else {
    hotAPI.reload("data-v-5da3a0ca", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),

/***/ 434:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 435:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 436:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      staticClass:
        "elementor-template-library-template elementor-template-library-template-remote",
      class: "elementor-template-library-template-block"
    },
    [
      _c(
        "div",
        {
          staticClass: "elementor-template-library-template-body",
          staticStyle: { "min-height": "20px" },
          on: {
            click: function($event) {
              return _vm.preview(_vm.itemcontent)
            }
          }
        },
        [
          _c(
            "span",
            {
              staticClass: "uicore-tag",
              class: "uicore-" + _vm.themeTag(_vm.itemcontent.v).color
            },
            [_vm._v(_vm._s(_vm.themeTag(_vm.itemcontent.v).msg))]
          ),
          _vm._v(" "),
          _c("img", { attrs: { src: _vm.itemcontent.thumb } }),
          _vm._v(" "),
          _vm.type === "block"
            ? _c(
                "div",
                { staticClass: "elementor-template-library-template-preview" },
                [
                  _c("i", {
                    staticClass: "eicon-zoom-in",
                    attrs: { "aria-hidden": "true" }
                  })
                ]
              )
            : _vm._e()
        ]
      ),
      _vm._v(" "),
      _c("div", { staticClass: "elementor-template-library-template-footer" }, [
        _c(
          "a",
          {
            staticClass:
              "elementor-template-library-template-action elementor-button uicore-insert"
          },
          [
            _vm.themeTag(_vm.itemcontent.v).color != "red"
              ? _c(
                  "span",
                  {
                    staticClass: "uicore-button-title",
                    on: {
                      click: function($event) {
                        return _vm.insert(_vm.itemcontent)
                      }
                    }
                  },
                  [_vm._v("Insert")]
                )
              : _c(
                  "span",
                  {
                    staticClass: "uicore-button-title",
                    on: {
                      click: function($event) {
                        return _vm.update()
                      }
                    }
                  },
                  [_vm._v("Update")]
                )
          ]
        ),
        _vm._v(" "),
        _c("div", { staticClass: "elementor-template-library-template-name" }, [
          _vm._v(_vm._s(_vm.theName()))
        ])
      ])
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-89735eb6", esExports)
  }
}

/***/ }),

/***/ 437:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("div", [
      _c("div", { attrs: { id: "elementor-template-library-toolbar" } }, [
        _c("div", [
          _c(
            "select",
            {
              directives: [
                {
                  name: "model",
                  rawName: "v-model",
                  value: _vm.select,
                  expression: "select"
                }
              ],
              on: {
                change: function($event) {
                  var $$selectedVal = Array.prototype.filter
                    .call($event.target.options, function(o) {
                      return o.selected
                    })
                    .map(function(o) {
                      var val = "_value" in o ? o._value : o.value
                      return val
                    })
                  _vm.select = $event.target.multiple
                    ? $$selectedVal
                    : $$selectedVal[0]
                }
              }
            },
            [
              _c("option", { attrs: { selected: "", value: "all" } }, [
                _vm._v("All Blocks")
              ]),
              _vm._v(" "),
              _c("option", { attrs: { value: "call-to-action" } }, [
                _vm._v("Call To Action")
              ]),
              _vm._v(" "),
              _c("option", { attrs: { value: "clients" } }, [
                _vm._v("Clients")
              ]),
              _vm._v(" "),
              _c("option", { attrs: { value: "contact" } }, [
                _vm._v("Contact")
              ]),
              _vm._v(" "),
              _c("option", { attrs: { value: "content" } }, [
                _vm._v("Content")
              ]),
              _vm._v(" "),
              _c("option", { attrs: { value: "counters" } }, [
                _vm._v("Counters")
              ]),
              _vm._v(" "),
              _c("option", { attrs: { value: "features" } }, [
                _vm._v("Features")
              ]),
              _vm._v(" "),
              _c("option", { attrs: { value: "faq" } }, [_vm._v("FAQ")]),
              _vm._v(" "),
              _c("option", { attrs: { value: "form" } }, [_vm._v("Form")]),
              _vm._v(" "),
              _c("option", { attrs: { value: "hero" } }, [_vm._v("Hero")]),
              _vm._v(" "),
              _c("option", { attrs: { value: "news" } }, [_vm._v("News")]),
              _vm._v(" "),
              _c("option", { attrs: { value: "pricing" } }, [
                _vm._v("Pricing")
              ]),
              _vm._v(" "),
              _c("option", { attrs: { value: "team" } }, [_vm._v("Team")]),
              _vm._v(" "),
              _c("option", { attrs: { value: "testimonials" } }, [
                _vm._v("Testimonials")
              ])
            ]
          )
        ]),
        _vm._v(" "),
        _c(
          "div",
          { attrs: { id: "elementor-template-library-filter-text-wrapper" } },
          [
            _c(
              "label",
              {
                staticClass: "elementor-screen-only",
                attrs: { for: "elementor-template-library-filter-text" }
              },
              [_vm._v("Search Templates:")]
            ),
            _vm._v(" "),
            _c("input", {
              directives: [
                {
                  name: "model",
                  rawName: "v-model",
                  value: _vm.search,
                  expression: "search"
                }
              ],
              attrs: {
                id: "elementor-template-library-filter-text",
                placeholder: "Search"
              },
              domProps: { value: _vm.search },
              on: {
                input: function($event) {
                  if ($event.target.composing) {
                    return
                  }
                  _vm.search = $event.target.value
                }
              }
            }),
            _vm._v(" "),
            _c("i", { staticClass: "eicon-search" })
          ]
        )
      ]),
      _vm._v(" "),
      _vm.showGrid
        ? _c(
            "div",
            {
              ref: "masoContainer",
              attrs: { id: "elementor-template-library-templates-container" }
            },
            _vm._l(_vm.BlocksList, function(itemcontent, index) {
              return _c("item", {
                key: index,
                style: _vm.style,
                attrs: { itemcontent: itemcontent, type: "block" },
                on: { triggerPreview: _vm.emitPrev, triggerInsert: _vm.emitIns }
              })
            }),
            1
          )
        : _vm._e(),
      _vm._v(" "),
      _c(
        "div",
        {
          directives: [
            {
              name: "show",
              rawName: "v-show",
              value: _vm.loading,
              expression: "loading"
            }
          ],
          staticStyle: { position: "absolute", top: "220px", width: "95%" },
          attrs: { id: "elementor-template-library-footer-banner" }
        },
        [
          _c("span", {
            staticClass:
              "elementor-templates-modal__header__logo__icon-wrapper uicore-library-logo"
          }),
          _vm._v(" "),
          _c("div", { staticClass: "elementor-excerpt" }, [_vm._v("Loading")])
        ]
      )
    ])
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-5da3a0ca", esExports)
  }
}

/***/ }),

/***/ 438:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_license_tab_vue__ = __webpack_require__(176);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_license_tab_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_license_tab_vue__);
/* harmony namespace reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_license_tab_vue__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_license_tab_vue__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5f20178c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_license_tab_vue__ = __webpack_require__(440);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(439)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_license_tab_vue___default.a,
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5f20178c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_license_tab_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/library/license-tab.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-5f20178c", Component.options)
  } else {
    hotAPI.reload("data-v-5f20178c", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),

/***/ 439:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 440:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { attrs: { id: "elementor-template-library-preview" } }, [
    _vm.purchase_data.token === ""
      ? _c(
          "div",
          {
            staticClass: "uicore_register",
            staticStyle: { "max-width": "666px", margin: "20px auto" }
          },
          [
            _c(
              "div",
              { staticClass: "elementor-template-library-blank-title" },
              [_vm._v("Activate your theme")]
            ),
            _vm._v(" "),
            _c("p", { staticStyle: { margin: "10px auto 50px" } }, [
              _vm._v(
                "\n            Connect your theme to enable access to UiCore Library.\n        "
              )
            ]),
            _vm._v(" "),
            _c("div", [
              _c(
                "div",
                {
                  staticClass: "uicore_key_input",
                  staticStyle: {
                    "align-items": "center",
                    display: "flex",
                    "flex-direction": "column"
                  },
                  attrs: { id: "elementor-template-library-save-template-form" }
                },
                [
                  _c(
                    "span",
                    {
                      staticClass: "elementor-button elementor-button-success",
                      class: [
                        _vm.isloading ? "uicore-loading" : null,
                        _vm.requireForce ? "uicore-disable" : null
                      ],
                      staticStyle: {
                        "min-width": "140px",
                        "margin-top": "10px",
                        "line-height": "55px",
                        padding: "0"
                      },
                      on: {
                        click: function($event) {
                          return _vm.verify()
                        }
                      }
                    },
                    [_vm._v(_vm._s(_vm.isloading ? "Processing" : "Connect"))]
                  )
                ]
              )
            ])
          ]
        )
      : _c(
          "div",
          {
            staticStyle: {
              "align-items": "center",
              display: "flex",
              "flex-direction": "column"
            }
          },
          [
            _c("p", { staticClass: "elementor-template-library-blank-title" }, [
              _vm._v("\n            Your theme is now activated!\n        ")
            ]),
            _vm._v("\n        You'll redirected to template library.\n    ")
          ]
        )
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-5f20178c", esExports)
  }
}

/***/ }),

/***/ 441:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_pages_tab_vue__ = __webpack_require__(177);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_pages_tab_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_pages_tab_vue__);
/* harmony namespace reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_pages_tab_vue__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_pages_tab_vue__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1740aecf_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_pages_tab_vue__ = __webpack_require__(443);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(442)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_pages_tab_vue___default.a,
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1740aecf_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_pages_tab_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/library/pages-tab.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-1740aecf", Component.options)
  } else {
    hotAPI.reload("data-v-1740aecf", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),

/***/ 442:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 443:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("div", [
      _c("div", { attrs: { id: "elementor-template-library-toolbar" } }, [
        _vm.isPages()
          ? _c("div", { staticStyle: { display: "flex", width: "200px" } }, [
              _c(
                "select",
                {
                  directives: [
                    {
                      name: "model",
                      rawName: "v-model",
                      value: _vm.select,
                      expression: "select"
                    }
                  ],
                  on: {
                    change: function($event) {
                      var $$selectedVal = Array.prototype.filter
                        .call($event.target.options, function(o) {
                          return o.selected
                        })
                        .map(function(o) {
                          var val = "_value" in o ? o._value : o.value
                          return val
                        })
                      _vm.select = $event.target.multiple
                        ? $$selectedVal
                        : $$selectedVal[0]
                    }
                  }
                },
                [
                  _c("option", { attrs: { selected: "", value: "all" } }, [
                    _vm._v("All Pages")
                  ]),
                  _vm._v(" "),
                  _c("option", { attrs: { value: "homepage" } }, [
                    _vm._v("Homepage")
                  ]),
                  _vm._v(" "),
                  _c("option", { attrs: { value: "inner-page" } }, [
                    _vm._v("Inner Page")
                  ])
                ]
              )
            ])
          : _vm._e(),
        _vm._v(" "),
        _c(
          "div",
          { attrs: { id: "elementor-template-library-filter-text-wrapper" } },
          [
            _c(
              "label",
              {
                staticClass: "elementor-screen-only",
                attrs: { for: "elementor-template-library-filter-text" }
              },
              [_vm._v("Search Templates:")]
            ),
            _vm._v(" "),
            _c("input", {
              directives: [
                {
                  name: "model",
                  rawName: "v-model",
                  value: _vm.search,
                  expression: "search"
                }
              ],
              attrs: {
                id: "elementor-template-library-filter-text",
                placeholder: "Search"
              },
              domProps: { value: _vm.search },
              on: {
                input: function($event) {
                  if ($event.target.composing) {
                    return
                  }
                  _vm.search = $event.target.value
                }
              }
            }),
            _vm._v(" "),
            _c("i", { staticClass: "eicon-search" })
          ]
        )
      ]),
      _vm._v(" "),
      _vm.showGrid
        ? _c(
            "div",
            {
              ref: "masoContainer",
              attrs: { id: "elementor-template-library-templates-container" }
            },
            _vm._l(_vm.BlocksList, function(itemcontent, index) {
              return _c("item", {
                key: index,
                style: _vm.style,
                attrs: { itemcontent: itemcontent, type: "extra" },
                on: { triggerPreview: _vm.emitPrev, triggerInsert: _vm.emitIns }
              })
            }),
            1
          )
        : _vm._e(),
      _vm._v(" "),
      _c(
        "div",
        {
          directives: [
            {
              name: "show",
              rawName: "v-show",
              value: _vm.loading,
              expression: "loading"
            }
          ],
          staticStyle: { position: "absolute", top: "220px", width: "95%" },
          attrs: { id: "elementor-template-library-footer-banner" }
        },
        [
          _c("span", {
            staticClass:
              "elementor-templates-modal__header__logo__icon-wrapper uicore-library-logo"
          }),
          _vm._v(" "),
          _c("div", { staticClass: "elementor-excerpt" }, [_vm._v("Loading")])
        ]
      )
    ])
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-1740aecf", esExports)
  }
}

/***/ }),

/***/ 444:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_preview_tab_vue__ = __webpack_require__(178);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_preview_tab_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_preview_tab_vue__);
/* harmony namespace reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_preview_tab_vue__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_preview_tab_vue__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3f800013_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_preview_tab_vue__ = __webpack_require__(446);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(445)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_preview_tab_vue___default.a,
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3f800013_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_preview_tab_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/library/preview-tab.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-3f800013", Component.options)
  } else {
    hotAPI.reload("data-v-3f800013", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),

/***/ 445:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 446:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { attrs: { id: "elementor-template-library-preview" } }, [
    _c(
      "form",
      {
        ref: "prevForm",
        staticStyle: { display: "none" },
        attrs: {
          target: "uicorelibrarypreview",
          action:
            "https://library.uicore.co/" +
            _vm.item.slug +
            "/?utm_source=Elementor&utm_medium=Library&utm_campaign=Preview",
          method: "POST"
        }
      },
      [
        _c("input", {
          attrs: { type: "text", name: "settings" },
          domProps: { value: JSON.stringify(_vm.frontendSettings) }
        })
      ]
    ),
    _vm._v(" "),
    _vm.item.id
      ? _c("iframe", { attrs: { name: "uicorelibrarypreview", src: "#" } })
      : _vm._e()
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-3f800013", esExports)
  }
}

/***/ }),

/***/ 447:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      staticClass:
        "dialog-widget-content dialog-lightbox-widget-content uicore-lib-dialog"
    },
    [
      _c("div", { staticClass: "dialog-header dialog-lightbox-header" }, [
        _c("div", { staticClass: "elementor-templates-modal__header" }, [
          _c(
            "div",
            { staticClass: "elementor-templates-modal__header__logo-area" },
            [
              _c(
                "div",
                { staticClass: "elementor-templates-modal__header__logo" },
                [
                  _c("span", {
                    staticClass:
                      "elementor-templates-modal__header__logo__icon-wrapper uicore-library-logo"
                  }),
                  _vm._v(" "),
                  _vm.tab != "preview"
                    ? _c(
                        "span",
                        {
                          staticClass:
                            "elementor-templates-modal__header__logo__title"
                        },
                        [_vm._v("UiCore Library")]
                      )
                    : _c(
                        "span",
                        {
                          staticClass:
                            "elementor-templates-modal__header__logo__title",
                          on: {
                            click: function($event) {
                              return _vm.backToLibrary()
                            }
                          }
                        },
                        [_vm._v("Back to Library")]
                      )
                ]
              )
            ]
          ),
          _vm._v(" "),
          _c(
            "div",
            { staticClass: "elementor-templates-modal__header__menu-area" },
            [
              _vm.tab != "preview"
                ? _c(
                    "div",
                    { attrs: { id: "elementor-template-library-header-menu" } },
                    _vm._l(_vm.getTabs(), function(title, tabName) {
                      return _c(
                        "div",
                        {
                          key: tabName,
                          class: [
                            { "elementor-active": _vm.tab == tabName },
                            "elementor-component-tab elementor-template-library-menu-item"
                          ],
                          style: _vm.isPurchase(tabName),
                          on: {
                            click: function($event) {
                              return _vm.setTab(tabName)
                            }
                          }
                        },
                        [
                          _vm._v(
                            "\n                        " +
                              _vm._s(title) +
                              "\n                    "
                          )
                        ]
                      )
                    }),
                    0
                  )
                : _vm._e()
            ]
          ),
          _vm._v(" "),
          _c(
            "div",
            {
              staticClass: "elementor-templates-modal__header__items-area",
              staticStyle: { "min-width": "167px" }
            },
            [
              _c(
                "div",
                {
                  staticClass:
                    "elementor-templates-modal__header__close elementor-templates-modal__header__close--normal elementor-templates-modal__header__item"
                },
                [
                  _c("i", {
                    staticClass: "eicon-close",
                    attrs: { "aria-hidden": "true", title: "Close" },
                    on: { click: _vm.closeIframe }
                  }),
                  _vm._v(" "),
                  _c("span", { staticClass: "elementor-screen-only" }, [
                    _vm._v("Close")
                  ])
                ]
              ),
              _vm._v(" "),
              _vm.tab == "preview"
                ? _c(
                    "div",
                    {
                      staticClass: "elementor-templates-modal__header__item",
                      attrs: {
                        id:
                          "elementor-template-library-header-preview-insert-wrapper"
                      },
                      on: { click: _vm.insert }
                    },
                    [_vm._m(0)]
                  )
                : _vm._e()
            ]
          )
        ])
      ]),
      _vm._v(" "),
      _c(
        "div",
        {
          staticClass: "dialog-message dialog-lightbox-message",
          on: { scroll: _vm.onScroll }
        },
        [
          _c(
            "div",
            {
              staticClass: "dialog-content dialog-lightbox-content",
              staticStyle: { display: "block" }
            },
            [
              _c(
                "div",
                {
                  staticStyle: { height: "100%" },
                  attrs: { id: "uicore-template-library-templates" }
                },
                [
                  _vm.tab == "purchase"
                    ? _c("license", { on: { admin: _vm.adminSettings } })
                    : _vm.tab == "blocks"
                    ? _c("blocks", {
                        attrs: { isBottom: _vm.isBottom },
                        on: {
                          preview: _vm.initBlocksPreview,
                          insert: _vm.insertFromList
                        }
                      })
                    : _vm.tab == "preview"
                    ? _c("preview", { attrs: { item: _vm.currentItem } })
                    : _vm.tab == "importing"
                    ? _c(
                        "div",
                        {
                          staticStyle: {
                            position: "absolute",
                            top: "220px",
                            width: "95%"
                          },
                          attrs: {
                            id: "elementor-template-library-footer-banner"
                          }
                        },
                        [
                          _c("span", {
                            staticClass:
                              "elementor-templates-modal__header__logo__icon-wrapper uicore-library-logo"
                          }),
                          _vm._v(" "),
                          _c("div", { staticClass: "elementor-excerpt" }, [
                            _vm._v("Downloading data from API")
                          ])
                        ]
                      )
                    : _vm.tab == "importing2"
                    ? _c(
                        "div",
                        {
                          staticStyle: {
                            position: "absolute",
                            top: "220px",
                            width: "95%"
                          },
                          attrs: {
                            id: "elementor-template-library-footer-banner"
                          }
                        },
                        [
                          _c("span", {
                            staticClass:
                              "elementor-templates-modal__header__logo__icon-wrapper uicore-library-logo"
                          }),
                          _vm._v(" "),
                          _c("div", { staticClass: "elementor-excerpt" }, [
                            _vm._v("Downloading assets")
                          ])
                        ]
                      )
                    : _vm.tab == "importing3"
                    ? _c(
                        "div",
                        {
                          staticStyle: {
                            position: "absolute",
                            top: "220px",
                            width: "95%"
                          },
                          attrs: {
                            id: "elementor-template-library-footer-banner"
                          }
                        },
                        [
                          _c("span", {
                            staticClass:
                              "elementor-templates-modal__header__logo__icon-wrapper uicore-library-logo"
                          }),
                          _vm._v(" "),
                          _c("div", { staticClass: "elementor-excerpt" }, [
                            _vm._v("Importing template")
                          ])
                        ]
                      )
                    : _vm.tab == "error"
                    ? _c(
                        "div",
                        {
                          staticStyle: {
                            position: "absolute",
                            top: "220px",
                            width: "95%"
                          },
                          attrs: {
                            id: "elementor-template-library-footer-banner"
                          }
                        },
                        [
                          _c("span", {
                            staticClass:
                              "elementor-templates-modal__header__logo__icon-wrapper uicore-library-logo"
                          }),
                          _vm._v(" "),
                          _c("div", { staticClass: "elementor-excerpt" }, [
                            _vm._v(
                              "There is an issue communicating with our API server."
                            ),
                            _c("br"),
                            _vm._v(
                              " To continue using UiCore Library, please enable "
                            ),
                            _c(
                              "a",
                              { attrs: { href: _vm.url, target: "_blank" } },
                              [_vm._v("API Connection Proxy")]
                            ),
                            _vm._v(", then try again.")
                          ])
                        ]
                      )
                    : _c("pages", {
                        on: {
                          preview: _vm.initPagesPreview,
                          insert: _vm.insertFromList
                        }
                      })
                ],
                1
              )
            ]
          )
        ]
      ),
      _vm._v(" "),
      _c("div", {
        staticClass: "dialog-buttons-wrapper dialog-lightbox-buttons-wrapper"
      })
    ]
  )
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c(
      "a",
      {
        staticClass:
          "elementor-template-library-template-action elementor-template-library-template-insert elementor-button"
      },
      [
        _c("i", {
          staticClass: "eicon-file-download",
          attrs: { "aria-hidden": "true" }
        }),
        _vm._v(" "),
        _c("span", { staticClass: "elementor-button-title" }, [
          _vm._v("Insert")
        ])
      ]
    )
  }
]
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-36ad5e84", esExports)
  }
}

/***/ })

},[430]);