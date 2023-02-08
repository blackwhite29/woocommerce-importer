/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*******************************************************************************************!*\
  !*** ./platform/plugins/woocommerce-importer/resources/assets/js/woocommerce-importer.js ***!
  \*******************************************************************************************/
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
var WooCommerceImporter = /*#__PURE__*/function () {
  function WooCommerceImporter() {
    _classCallCheck(this, WooCommerceImporter);
    this.listen();
  }
  _createClass(WooCommerceImporter, [{
    key: "listen",
    value: function listen() {
      $(document).on('submit', '.import-woocommerce-form', this["import"].bind(this));
    }
  }, {
    key: "import",
    value: function _import(event) {
      var _this = this;
      event.preventDefault();
      var $form = $(event.currentTarget);
      $('.woocommerce-importer .alert').addClass('hidden');
      var $button = $form.find('button[type=submit]');
      this.call({
        type: 'POST',
        url: $form.prop('action'),
        data: new FormData($form[0]),
        beforeSend: function beforeSend() {
          $button.addClass('button-loading');
        },
        complete: function complete() {
          $button.removeClass('button-loading');
        }
      }).then(function (res) {
        if (!res.error) {
          Botble.showSuccess(res.message);
          _this.importProduct(event, res.data);
          $('.woocommerce-importer .success-message').removeClass('hidden').text(res.message);
        } else {
          Botble.showError(res.message);
          $('.woocommerce-importer .error-message').removeClass('hidden').text(res.message);
        }
      })["catch"](function (error) {
        Botble.handleError(error);
      });
    }
  }, {
    key: "importProduct",
    value: function importProduct(event, data) {
      var _this2 = this;
      event.preventDefault();
      var $form = $(event.currentTarget);
      $('.woocommerce-importer .alert').addClass('hidden');
      var $button = $form.find('button[type=submit]');
      var formData = new FormData($form[0]);
      formData.set('current_page', data.current_page);
      formData.set('products', data.products);
      formData.set('has_next', data.has_next);
      this.call({
        type: 'POST',
        url: $form.prop('action'),
        data: formData,
        beforeSend: function beforeSend() {
          $button.addClass('button-loading');
        },
        complete: function complete() {
          $button.removeClass('button-loading');
        }
      }).then(function (res) {
        if (!res.error) {
          Botble.showSuccess(res.message);
          if (res.data.has_next) {
            _this2.importProduct(event, res.data);
          }
          $('.woocommerce-importer .success-message').removeClass('hidden').text(res.message);
        } else {
          Botble.showError(res.message);
          $('.woocommerce-importer .error-message').removeClass('hidden').text(res.message);
        }
      })["catch"](function (error) {
        Botble.handleError(error);
      });
    }
  }, {
    key: "call",
    value: function call(obj) {
      return new Promise(function (resolve, reject) {
        $.ajax(_objectSpread(_objectSpread({
          type: 'GET',
          contentType: false,
          processData: false
        }, obj), {}, {
          success: function success(res) {
            resolve(res);
          },
          error: function error(res) {
            reject(res);
          }
        }));
      });
    }
  }]);
  return WooCommerceImporter;
}();
$(function () {
  new WooCommerceImporter();
});
/******/ })()
;