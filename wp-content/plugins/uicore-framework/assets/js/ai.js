uicoreJsonp([6],{

/***/ 182:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _popupBase = __webpack_require__(460);

var _popupBase2 = _interopRequireDefault(_popupBase);

var _vueMultiselect = __webpack_require__(3);

var _vueMultiselect2 = _interopRequireDefault(_vueMultiselect);

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
exports.default = {
    components: {
        uibase: _popupBase2.default,
        multiselect: _vueMultiselect2.default
    },
    data: function data() {
        return {
            mode: '',
            abortController: null,
            writing: false,
            tone: [],
            keywords: '',
            topic: '',
            length: 500,
            active: true,
            openaiApiKey: '',
            toneList: ['Informative', 'Conversational', 'Persuasive', 'Educational', 'Humorous', 'Authoritative', 'Instructional', 'Personal', 'Thoughtful', 'Inspirational', 'Analytical', 'Entertaining', 'Professional', 'Narrative', 'Motivational', 'Controversial', 'Chatty', 'Casual', 'Opinionated', 'Helpful', 'Friendly', 'Sarcastic', 'Sincere', 'Relatable', 'Candid', 'Analytic', 'Emotional', 'Serious', 'Explanatory', 'Instructional', 'Encouraging', 'Critical', 'Honest', 'Engaging', 'Descriptive', 'Inspiring', 'Witty', 'Unbiased', 'Expository', 'Inquisitive', 'Factual', 'Informal', 'Analyzing', 'Comparative', 'Reflective', 'Argumentative', 'Compelling', 'Insightful', 'Practical', 'Inspired', 'Clear', 'Expressive']
        };
    },
    beforeMount: function beforeMount() {
        if (window.uicore_ai.key == '' || window.uicore_ai.key == undefined) {
            this.mode = '';
        } else {
            this.openaiApiKey = window.uicore_ai.key;
            this.mode = 'write';
        }
    },


    // This method generates a response by creating a new block with the text block and then sending a stream request to the OpenAI API
    methods: {
        close: function close() {
            document.querySelector('.ui-ai-writer .uicore-base_close').click();
        },
        start: function start() {
            this.writing = true;
            this.abortController = new AbortController();
            jQuery('#uicore-writing-assistant-trigger').hide();
        },
        stop: function stop(_stop) {
            if (!_stop) {
                this.mode = 'done';
            } else {
                this.mode = 'write';
            }
            this.writing = false;
            this.abortController.abort();
            this.convertToBlocks();
            jQuery('#uicore-writing-assistant-trigger').show();
        },
        saveKey: async function saveKey() {
            if (await this.storeValidKey(this.openaiApiKey)) {
                window.uicore_ai.key = this.openaiApiKey;
                this.mode = 'write';

                // Save the key to the database
                var url = uicore_ai.api;
                axios.post(url, { aiKey: this.openaiApiKey });
            } else {
                alert('Invalid Api Key');
            }
        },
        storeValidKey: async function storeValidKey(key) {
            var url = 'https://api.openai.com/v1/chat/completions';
            var options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Authorization: 'Bearer ' + key
                },
                body: JSON.stringify({
                    model: 'gpt-3.5-turbo',
                    temperature: 0.02,
                    max_tokens: 1,
                    messages: [{
                        role: 'user',
                        content: '?'
                    }]
                })
            };

            try {
                var response = await fetch(url, options);
                var data = await response.json();
                if (response.ok) {
                    return true; // API key is valid
                } else {
                    throw new Error('API key error: ' + data.error);
                }
            } catch (error) {
                console.error(error.message);
                return false; // API key is invalid
            }
        },
        generateResponse: async function generateResponse() {
            var _this = this;

            try {
                var blockInitiated = false;

                // Define a function to update the content of the block
                var updateContent = function updateContent(newContent, blockId) {
                    wp.data.dispatch('core/block-editor').updateBlockAttributes(blockId, { content: newContent });
                };

                // Define a function to add text from the stream to the block content
                var addTextToBlock = function addTextToBlock(text) {
                    //if block was not initiated initiate it first
                    if (!blockInitiated) {
                        // Create a new block with the text block
                        wp.data.dispatch('core/block-editor').insertBlock(wp.blocks.createBlock('core/freeform', { content: '' }));
                        blockInitiated = true;
                    }
                    // Get the ID of the last inserted block
                    var blockId = wp.data.select('core/block-editor').getSelectedBlockClientId();

                    // Get the current content of the block
                    var content = wp.data.select('core/block-editor').getBlock(blockId);

                    var oldContent = content && content.attributes ? content.attributes.content : '';
                    var newContent = oldContent + text;
                    updateContent(newContent, blockId);
                };

                this.start();

                // Send a stream request to the OpenAI API
                var response = await fetch('https://api.openai.com/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Authorization: 'Bearer ' + this.openaiApiKey
                    },
                    body: JSON.stringify({
                        model: 'gpt-3.5-turbo',
                        temperature: 0.03,
                        stream: true,
                        stop: ['[STOP]'],
                        messages: [{
                            role: 'system',
                            content: 'You are a profesional blog content writer. You write the article in a gutenberg html format.'
                        }, {
                            role: 'user',
                            content: this.prompt()
                        }]
                    }),
                    signal: this.abortController.signal // pass the signal option with the AbortController instance
                });

                // Define a function to read the stream data and update the block content
                var readStreamData = async function readStreamData(reader) {
                    while (true) {
                        var _ref = await reader.read(),
                            done = _ref.done,
                            value = _ref.value;

                        if (done) {
                            //scroll to top
                            var objDiv = document.querySelector('.interface-interface-skeleton__content');
                            objDiv.scrollTop = 0;
                            _this.stop();
                            break;
                        }

                        var text = _this.getStreamText(new TextDecoder('utf-8').decode(value));
                        if (text) {
                            addTextToBlock(text);
                        }
                    }
                };

                // Start reading the stream data if the response is successful
                if (!response.ok) {
                    if (response.status === 401) {
                        this.openaiApiKey = '';
                        this.mode = '';
                        this.writing = false;
                        this.abortController.abort();
                        jQuery('#uicore-writing-assistant-trigger').show();
                        alert('Invalid Api Key');
                    } else {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                } else {
                    var reader = response.body.getReader();
                    readStreamData(reader);
                }
            } catch (error) {
                console.error(error);
                console.log('Error: ', error.message);
                //if is 401 error then the key is invalid
                if (error.message === 'Unauthorized') {
                    alert('Invalid Api Key');
                }
            }
        },


        // This method gets the text from the stream data
        getStreamText: function getStreamText(data) {
            var _this2 = this;

            // Split text by new line
            var splitData = data.split('\n\n');
            if (splitData.length > 1) {
                var content = '';
                splitData.forEach(function (element) {
                    if (element.startsWith('data: ')) {
                        content = content + _this2.getStreamText(element);
                    }
                });
                return content;
            }
            var cleanData = splitData[0].substring(6); // Removes "data: " prefix

            var obj = null;
            try {
                obj = JSON.parse(cleanData);
            } catch (err) {
                return '';
            }

            if (obj.choices[0].finish_reason === 'stop') {
                return '';
            }
            if (!obj.choices[0]) {
                return '';
            }
            if (typeof obj.choices[0].delta.content !== 'undefined') {
                return obj.choices[0].delta.content;
            }
            return '';
        },
        prompt: function prompt() {
            return 'Create a blog post about \u201C' + this.topic + '\u201D. Write it in a \u201C' + this.tone.join(', ') + '\u201D tone. \n            Use transition words. Use active voice. Write aproximative ' + this.length + ' words. \n            Include the following keywords: \u201C' + this.keywords + '\u201D.\n            Start with a compelling headline and introduction.\n            Use subheadings to break up the content and make it easy to scan.\n            Use short paragraphs and sentences to improve readability.\n            Use bullet points and numbered lists to highlight important information (using ul and li).\n            Use a conversational tone and avoid jargon.\n            End with a clear call-to-action or conclusion.\n\n            When it comes to structure, a good format to follow is:\n            Grab the reader\'s attention and introduce the topic.\n            Develop your ideas and provide supporting evidence.\n            Summarize your main points and provide a call-to-action or conclusion.\n\n            Format the response as wysiwyg html.\n            !!DO NOT INCLUDE OTHER HTML TAGS THAN THE ONES USED FOR TEXT FORMATING eg: <section>, <heade> <html> etc!!\n            !!WRITE THE TEXT IN THE LANGUAGE OF THE TOPIC: "' + this.topic + '"!!\n            ';
        },
        convertToBlocks: function convertToBlocks() {
            var createBlock = wp.blocks.createBlock;
            var dispatch = wp.data.dispatch;


            var freeformBlock = wp.data.select('core/block-editor').getSelectedBlock();

            var freeformHtml = freeformBlock.attributes.content; // Get the HTML content of the freeform block

            var blocks = wp.blocks.rawHandler({
                HTML: freeformHtml,
                mode: 'BLOCKS'
            }); // Convert the HTML to individual block objects

            //reverse blocks order
            blocks.reverse();

            // Add the new blocks to the editor
            blocks.forEach(function (block) {
                var name = block.name,
                    attributes = block.attributes,
                    innerBlocks = block.innerBlocks;

                var newBlock = createBlock(name, attributes, innerBlocks);
                dispatch('core/block-editor').insertBlock(newBlock, freeformBlock.clientId);
            });

            // Remove the freeform block from the editor
            dispatch('core/block-editor').removeBlock(freeformBlock.clientId);
        }
    },

    computed: {
        getMainColor: function getMainColor() {
            var color = '#532df5'; //to_color
            // if (this.uicoreSettings.admin_customizer === 'true' && this.uicoreSettings.to_color) {
            //     color = this.uicoreSettings.to_color;
            // }
            return '--uicore-primary:' + color;
        }
    }
};

/***/ }),

/***/ 183:
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

exports.default = {
    name: 'uibase',
    props: ['title', 'scroll'],
    methods: {
        close: function close() {
            this.$emit('close');
        }
    }

};

/***/ }),

/***/ 457:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _vue = __webpack_require__(11);

var _vue2 = _interopRequireDefault(_vue);

var _app = __webpack_require__(458);

var _app2 = _interopRequireDefault(_app);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// import PerfectScrollbar from 'vue2-perfect-scrollbar';
// import 'vue2-perfect-scrollbar/dist/vue2-perfect-scrollbar.css';
// // import store from 'admin/utils/store';

// Vue.use(PerfectScrollbar);


(function (window, wp) {

    // just to keep it cleaner - we refer to our link by id for speed of lookup on DOM.
    var link_id = 'uicore-writing-assistant-trigger';

    // prepare our custom link's html.
    var link_html = '<a id="' + link_id + '" type="button" class="button button-primary button-large uicore-ai-assistant-btn" href="#" >AI Writing Assistant</a>';

    // check if gutenberg's editor root element is present.
    var editorEl = document.getElementById('editor');
    if (!editorEl) {
        // do nothing if there's no gutenberg root element on page.
        return;
    }

    var unsubscribe = wp.data.subscribe(function () {
        setTimeout(function () {
            if ( /*!document.getElementById( link_id )*/document.getElementsByClassName("uicore-ai-assistant-btn")[0] == undefined) {
                var toolbalEl = editorEl.querySelector('.edit-post-header-toolbar');
                if (toolbalEl instanceof HTMLElement) {
                    toolbalEl.insertAdjacentHTML('beforeend', link_html);
                    toolbalEl.insertAdjacentHTML('beforeend', '<div id="uicore-assistant-wrapper"><div id="ui-ai-app"></div></div>');
                    // add click event listener to our custom link.
                    document.getElementById(link_id).addEventListener('click', function (event) {
                        var uicore_ai_vue = new _vue2.default({
                            el: '#ui-ai-app',
                            destroyed: function destroyed() {},
                            render: function render(h) {
                                return h(_app2.default);
                            }
                        });

                        // Add a click event listener to the close button
                        setTimeout(function () {
                            jQuery('.ui-ai-writer .uicore-base_close').on("click", function () {
                                uicore_ai_vue.$destroy();
                                document.getElementById('uicore-assistant-wrapper').innerHTML = '<div id="ui-ai-app"></div>';
                            });
                        }, 400);
                        event.preventDefault();
                    });
                }
            }
        }, 1);
    });
})(window, wp);

/***/ }),

/***/ 458:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_app_vue__ = __webpack_require__(182);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_app_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_app_vue__);
/* harmony namespace reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_app_vue__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_app_vue__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6dfaf433_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_app_vue__ = __webpack_require__(463);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(459)
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_app_vue___default.a,
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6dfaf433_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_app_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/ai/app.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-6dfaf433", Component.options)
  } else {
    hotAPI.reload("data-v-6dfaf433", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),

/***/ 459:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 460:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_popup_base_vue__ = __webpack_require__(183);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_popup_base_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_popup_base_vue__);
/* harmony namespace reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_popup_base_vue__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_popup_base_vue__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_4257a0a0_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_popup_base_vue__ = __webpack_require__(462);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(461)
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_popup_base_vue___default.a,
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_4257a0a0_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_popup_base_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/ai/popup-base.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-4257a0a0", Component.options)
  } else {
    hotAPI.reload("data-v-4257a0a0", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),

/***/ 461:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 462:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "uicore-base" }, [
    _c("div", { staticClass: "uicore-base_header" }, [
      _c("div", {
        staticClass: "uicore-base_title",
        domProps: { innerHTML: _vm._s(_vm.title) }
      }),
      _vm._v(" "),
      _c(
        "div",
        { staticClass: "uicore-base_close", on: { click: _vm.close } },
        [
          _c(
            "svg",
            {
              attrs: {
                xmlns: "http://www.w3.org/2000/svg",
                width: "24",
                height: "24",
                viewBox: "0 0 24 24",
                fill: "none"
              }
            },
            [
              _c("path", {
                attrs: {
                  d: "M18 6L6 18",
                  stroke: "#0F172A",
                  "stroke-width": "2",
                  "stroke-linecap": "round",
                  "stroke-linejoin": "round"
                }
              }),
              _vm._v(" "),
              _c("path", {
                attrs: {
                  d: "M6 6L18 18",
                  stroke: "#0F172A",
                  "stroke-width": "2",
                  "stroke-linecap": "round",
                  "stroke-linejoin": "round"
                }
              })
            ]
          )
        ]
      )
    ]),
    _vm._v(" "),
    _c(
      "div",
      { staticClass: "uicore-base_content" },
      [
        _vm.scroll
          ? _c("perfect-scrollbar", [_vm._t("default")], 2)
          : _vm._t("default")
      ],
      2
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
    require("vue-hot-reload-api")      .rerender("data-v-4257a0a0", esExports)
  }
}

/***/ }),

/***/ 463:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "ui-ai-writer" },
    [
      _c(
        "a",
        {
          directives: [
            {
              name: "show",
              rawName: "v-show",
              value: _vm.writing,
              expression: "writing"
            }
          ],
          staticClass:
            "button button-primary button-large uicore-ai-assistant-working",
          attrs: { type: "button", href: "#none" },
          on: {
            click: function($event) {
              return _vm.stop(true)
            }
          }
        },
        [_vm._v("Stop")]
      ),
      _vm._v(" "),
      _c(
        "uibase",
        {
          directives: [
            {
              name: "show",
              rawName: "v-show",
              value: !_vm.writing,
              expression: "!writing"
            }
          ],
          style: _vm.getMainColor,
          attrs: {
            title: "AI Writing Assistant<span>BETA</span>",
            scroll: false
          }
        },
        [
          _vm.mode == ""
            ? _c("div", { staticClass: "uianim-content" }, [
                _c(
                  "p",
                  {
                    staticStyle: {
                      padding: "0 20px",
                      "font-size": "14px",
                      margin: "0"
                    }
                  },
                  [
                    _vm._v(
                      "\n                We need to connect to your OpenAl account so you have full control and ownership of your data. You\n                can find your Secret API key in your\n                "
                    ),
                    _c(
                      "a",
                      {
                        attrs: {
                          href: "https://beta.openai.com/account/api-keys",
                          target: "_blank"
                        }
                      },
                      [_vm._v("User settings")]
                    ),
                    _vm._v(
                      "\n                (inside your OpenAI Account).\n            "
                    )
                  ]
                ),
                _vm._v(" "),
                _c("div", { staticClass: "uicore-core-setting" }, [
                  _c(
                    "label",
                    {
                      staticClass: "uicore_h2",
                      attrs: { for: "openaiApiKey" }
                    },
                    [_vm._v("OpenAI Key")]
                  ),
                  _vm._v(" "),
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.openaiApiKey,
                        expression: "openaiApiKey"
                      }
                    ],
                    staticStyle: { width: "100%", "max-width": "100%" },
                    attrs: {
                      id: "openaiApiKey",
                      type: "text",
                      placeholder: "sk-<your key>"
                    },
                    domProps: { value: _vm.openaiApiKey },
                    on: {
                      input: function($event) {
                        if ($event.target.composing) {
                          return
                        }
                        _vm.openaiApiKey = $event.target.value
                      }
                    }
                  })
                ]),
                _vm._v(" "),
                _c(
                  "div",
                  { staticClass: "uicore-btn", on: { click: _vm.saveKey } },
                  [_vm._v("Save OpenAI Key")]
                )
              ])
            : _vm._e(),
          _vm._v(" "),
          _vm.mode == "done"
            ? _c("div", { staticClass: "uianim-content" }, [
                _c(
                  "p",
                  {
                    staticStyle: { margin: "0 20px 15px", "font-size": "16px" }
                  },
                  [
                    _vm._v(
                      "\n                Your article has been generated successfully. Please note that this is a "
                    ),
                    _c("b", [_vm._v("beta")]),
                    _vm._v(
                      " feature and we\n                are constantly working on improving it. "
                    ),
                    _c("br"),
                    _vm._v(" "),
                    _c(
                      "i",
                      {
                        staticStyle: {
                          "font-size": "13px",
                          opacity: "0.7",
                          "line-height": "19px",
                          "margin-top": "14px",
                          display: "block"
                        }
                      },
                      [
                        _vm._v(
                          "Your feedback is important to us, so if you have any suggestions please don’t hesitate to let\n                    us know."
                        )
                      ]
                    )
                  ]
                ),
                _vm._v(" "),
                _c("div", { staticClass: "ui-ai-feedback" }, [
                  _c(
                    "a",
                    {
                      staticClass: "uicore-btn",
                      staticStyle: { background: "#f1f5f9", color: "#0f172a" },
                      attrs: {
                        href: "https://feedback.uicore.co/",
                        target: "_blank"
                      },
                      on: { click: _vm.close }
                    },
                    [_vm._v("Suggest an idea")]
                  ),
                  _vm._v(" "),
                  _c(
                    "div",
                    { staticClass: "uicore-btn", on: { click: _vm.close } },
                    [_vm._v("Dismiss")]
                  )
                ])
              ])
            : _vm._e(),
          _vm._v(" "),
          _vm.mode == "write"
            ? _c("div", { staticClass: "uianim-content" }, [
                _c("div", { staticClass: "uicore-core-setting" }, [
                  _c(
                    "label",
                    { staticClass: "uicore_h2", attrs: { for: "ui-ai-topic" } },
                    [_vm._v("Topic")]
                  ),
                  _vm._v(" "),
                  _c("textarea", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.topic,
                        expression: "topic"
                      }
                    ],
                    attrs: {
                      id: "ui-ai-topic",
                      type: "text",
                      placeholder:
                        "Write 1-2 sentences about what your article is about."
                    },
                    domProps: { value: _vm.topic },
                    on: {
                      input: function($event) {
                        if ($event.target.composing) {
                          return
                        }
                        _vm.topic = $event.target.value
                      }
                    }
                  })
                ]),
                _vm._v(" "),
                _c("div", { staticClass: "uicore-core-setting" }, [
                  _c(
                    "label",
                    {
                      staticClass: "uicore_h2",
                      attrs: { for: "ui-ai-keywords" }
                    },
                    [_vm._v("Keywords to include")]
                  ),
                  _vm._v(" "),
                  _c("textarea", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.keywords,
                        expression: "keywords"
                      }
                    ],
                    attrs: {
                      id: "ui-ai-keywords",
                      type: "text",
                      placeholder: "List of keywords separated by coma."
                    },
                    domProps: { value: _vm.keywords },
                    on: {
                      input: function($event) {
                        if ($event.target.composing) {
                          return
                        }
                        _vm.keywords = $event.target.value
                      }
                    }
                  })
                ]),
                _vm._v(" "),
                _c(
                  "div",
                  { staticClass: "uicore-core-setting" },
                  [
                    _c("label", { staticClass: "uicore_h2" }, [
                      _vm._v("Tone Of Voice")
                    ]),
                    _vm._v(" "),
                    _c("multiselect", {
                      attrs: {
                        options: _vm.toneList,
                        "allow-empty": false,
                        "show-labels": false,
                        searchable: true,
                        "clear-on-select": false,
                        "preserve-search": true,
                        multiple: true,
                        placeholder: "Select One or More"
                      },
                      model: {
                        value: _vm.tone,
                        callback: function($$v) {
                          _vm.tone = $$v
                        },
                        expression: "tone"
                      }
                    })
                  ],
                  1
                ),
                _vm._v(" "),
                _c("div", { staticClass: "uicore-core-setting" }, [
                  _c("label", { staticClass: "uicore_h2" }, [_vm._v("Length")]),
                  _vm._v(" "),
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.length,
                        expression: "length"
                      }
                    ],
                    attrs: { type: "range", min: "200", max: "1500" },
                    domProps: { value: _vm.length },
                    on: {
                      __r: function($event) {
                        _vm.length = $event.target.value
                      }
                    }
                  }),
                  _vm._v(" "),
                  _c("div", { staticClass: "data" }, [
                    _vm._v(_vm._s(_vm.length) + " Words")
                  ])
                ]),
                _vm._v(" "),
                _c(
                  "div",
                  {
                    staticClass: "uicore-btn",
                    on: { click: _vm.generateResponse }
                  },
                  [_vm._v("Write Article")]
                )
              ])
            : _vm._e()
        ]
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-6dfaf433", esExports)
  }
}

/***/ })

},[457]);