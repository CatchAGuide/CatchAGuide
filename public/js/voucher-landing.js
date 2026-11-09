/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/checkout/support.js"
/*!******************************************!*\
  !*** ./resources/js/checkout/support.js ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   choice: () => (/* binding */ choice),
/* harmony export */   csrfToken: () => (/* binding */ csrfToken),
/* harmony export */   formatIsoDate: () => (/* binding */ formatIsoDate),
/* harmony export */   readConfig: () => (/* binding */ readConfig),
/* harmony export */   trans: () => (/* binding */ trans)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/**
 * Small helpers shared by the checkout-style pages (tour checkout, reschedule, guide reject).
 */

/** Boot config rendered by the page as <script type="application/json" id="...">. */
var readConfig = function readConfig(elementId) {
  try {
    var _document$getElementB;
    return JSON.parse(((_document$getElementB = document.getElementById(elementId)) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.textContent) || '{}');
  } catch (e) {
    return {};
  }
};

/** Laravel-style ":name" replacements. */
var trans = function trans(template) {
  var replacements = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  return Object.entries(replacements).reduce(function (text, _ref) {
    var _ref2 = _slicedToArray(_ref, 2),
      key = _ref2[0],
      value = _ref2[1];
    return text.split(":".concat(key)).join(String(value));
  }, String(template !== null && template !== void 0 ? template : ''));
};

/** Laravel-style "one|many" pluralisation with :count. */
var choice = function choice(template, count) {
  var replacements = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  var _String$split = String(template !== null && template !== void 0 ? template : '').split('|'),
    _String$split2 = _slicedToArray(_String$split, 2),
    one = _String$split2[0],
    _String$split2$ = _String$split2[1],
    many = _String$split2$ === void 0 ? one : _String$split2$;
  return trans(count === 1 ? one : many, _objectSpread({
    count: count
  }, replacements));
};
var csrfToken = function csrfToken() {
  var _document$querySelect;
  return ((_document$querySelect = document.querySelector('meta[name="csrf-token"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.getAttribute('content')) || '';
};

/** Format a Y-m-d string without letting the visitor's timezone shift the day. */
var formatIsoDate = function formatIsoDate(formatter, iso) {
  return formatter.format(new Date("".concat(iso, "T00:00:00Z")));
};

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**************************************************!*\
  !*** ./resources/js/vouchers/voucher-landing.js ***!
  \**************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _checkout_support__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../checkout/support */ "./resources/js/checkout/support.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _objectWithoutProperties(e, t) { if (null == e) return {}; var o, r, i = _objectWithoutPropertiesLoose(e, t); if (Object.getOwnPropertySymbols) { var n = Object.getOwnPropertySymbols(e); for (r = 0; r < n.length; r++) o = n[r], -1 === t.indexOf(o) && {}.propertyIsEnumerable.call(e, o) && (i[o] = e[o]); } return i; }
function _objectWithoutPropertiesLoose(r, e) { if (null == r) return {}; var t = {}; for (var n in r) if ({}.hasOwnProperty.call(r, n)) { if (-1 !== e.indexOf(n)) continue; t[n] = r[n]; } return t; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Gift voucher landing page (resources/views/pages/voucher-landing). Registers the
 * `voucherLanding` Alpine component: the live voucher preview, Google Places destination
 * search, region tiles, amount chips and the same checks the server runs
 * (VoucherLandingRequest). The form then posts normally. Boot data comes from
 * VoucherLandingViewModel::clientConfig().
 */

var CONFIG_ELEMENT_ID = 'voucher-landing-config';
var RECAPTCHA_SELECTOR = '#voucher-landing-recaptcha';
var PLACE_INPUT_ID = 'voucherLandingSearchPlace';
var MOBILE_MEDIA = '(max-width: 1023px)'; // include-media "<desktop"
var HERO_SLIDE_MS = 7000;
var PHONE_PATTERN = /^[0-9][0-9 ()/.-]{2,24}$/;
var EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
var ERROR_ORDER = ['country', 'custom_amount', 'name', 'phone', 'email', 'consent'];
function voucherLanding() {
  var config = (0,_checkout_support__WEBPACK_IMPORTED_MODULE_0__.readConfig)(CONFIG_ELEMENT_ID);
  var i18n = config.i18n || {};
  var messages = i18n.errors || {};
  var open = config.open || '_open';
  var destinations = config.destinations || [];
  var values = config.values || {};
  var money = function money(amount) {
    return config.locale === 'de' ? "".concat(Number(amount).toLocaleString('de-DE'), " \u20AC") : "\u20AC".concat(Number(amount).toLocaleString('en-GB'));
  };
  return {
    destinations: destinations,
    place: values.place || '',
    country: values.country || '',
    region: values.region || '',
    city: values.city || '',
    amount: values.amount || '150',
    customAmount: values.customAmount || '',
    recipient: values.recipient || '',
    name: values.name || '',
    countryCode: values.countryCode || '+49',
    phone: values.phone || '',
    email: values.email || '',
    consent: Boolean(values.consent),
    errors: _objectSpread({}, config.errors || {}),
    submitting: false,
    pulses: 0,
    showBar: false,
    init: function init() {
      var _this = this;
      this.watchViewport();
      this.watchNav();
      this.watchPlaceSearch();
      this.startHeroCarousel();
      if (this.place && document.getElementById(PLACE_INPUT_ID)) {
        document.getElementById(PLACE_INPUT_ID).value = this.place;
      }
      var firstError = ERROR_ORDER.find(function (field) {
        return _this.errors[field];
      });
      if (firstError) {
        this.$nextTick(function () {
          return _this.reveal(firstError === 'country' ? PLACE_INPUT_ID : firstError);
        });
      }
    },
    isOpen: function isOpen(value) {
      return value === open;
    },
    get amountLabel() {
      if (this.amount !== 'custom') {
        return money(this.amount);
      }
      return Number(this.customAmount) > 0 ? money(this.customAmount) : config.locale === 'de' ? '— €' : '€ —';
    },
    get whereText() {
      if (!this.country || this.isOpen(this.country)) {
        return i18n.whereOpen;
      }
      if (!this.region || this.isOpen(this.region)) {
        return (0,_checkout_support__WEBPACK_IMPORTED_MODULE_0__.trans)(i18n.whereCountry, {
          country: this.country
        });
      }
      return (0,_checkout_support__WEBPACK_IMPORTED_MODULE_0__.trans)(i18n.whereRegion, {
        region: this.region,
        country: this.country
      });
    },
    get cityText() {
      return this.city ? (0,_checkout_support__WEBPACK_IMPORTED_MODULE_0__.trans)(i18n.city, {
        city: this.city
      }) : '';
    },
    get forText() {
      return this.recipient.trim();
    },
    get pulseClass() {
      if (!this.pulses) {
        return '';
      }
      return this.pulses % 2 ? 'is-pulse-a' : 'is-pulse-b';
    },
    bumpPreview: function bumpPreview() {
      this.pulses += 1;
    },
    clearError: function clearError(field) {
      if (this.errors[field]) {
        var _this$errors = this.errors,
          _removed = _this$errors[field],
          rest = _objectWithoutProperties(_this$errors, [field].map(_toPropertyKey));
        this.errors = rest;
      }
    },
    formatPlaceLabel: function formatPlaceLabel(country, region, city) {
      return [city, region, country].filter(Boolean).join(', ');
    },
    setPlaceInput: function setPlaceInput(value) {
      var input = document.getElementById(PLACE_INPUT_ID);
      if (input) {
        input.value = value;
      }
      this.place = value;
    },
    applyDestination: function applyDestination(country) {
      var region = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
      var city = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
      this.country = country;
      this.region = region;
      this.city = city;
      this.setPlaceInput(this.isOpen(country) ? i18n.placeOpen || '' : this.formatPlaceLabel(country, region, city));
      this.clearError('country');
      this.bumpPreview();
    },
    onPlaceInput: function onPlaceInput(event) {
      var _ref, _event$target$value, _event$target, _document$getElementB;
      this.place = (_ref = (_event$target$value = event === null || event === void 0 || (_event$target = event.target) === null || _event$target === void 0 ? void 0 : _event$target.value) !== null && _event$target$value !== void 0 ? _event$target$value : (_document$getElementB = document.getElementById(PLACE_INPUT_ID)) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.value) !== null && _ref !== void 0 ? _ref : '';
      if (!this.isOpen(this.country)) {
        this.country = '';
        this.region = '';
        this.city = '';
      }
      this.clearError('country');
      this.bumpPreview();
    },
    syncPlaceFromHidden: function syncPlaceFromHidden() {
      var _document$getElementB2, _document$getElementB3, _document$getElementB4;
      var country = ((_document$getElementB2 = document.getElementById('LocationCountryVoucherLanding')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.value) || '';
      var region = ((_document$getElementB3 = document.getElementById('LocationRegionVoucherLanding')) === null || _document$getElementB3 === void 0 ? void 0 : _document$getElementB3.value) || '';
      var city = ((_document$getElementB4 = document.getElementById('LocationCityVoucherLanding')) === null || _document$getElementB4 === void 0 ? void 0 : _document$getElementB4.value) || '';
      var input = document.getElementById(PLACE_INPUT_ID);
      if (!country) {
        return;
      }
      this.country = country;
      this.region = region;
      this.city = city;
      this.place = (input === null || input === void 0 ? void 0 : input.value) || this.formatPlaceLabel(country, region, city);
      this.clearError('country');
      this.bumpPreview();
    },
    watchPlaceSearch: function watchPlaceSearch() {
      var _this2 = this;
      document.addEventListener('cag:place-search-changed', function () {
        return _this2.syncPlaceFromHidden();
      });
    },
    pickAmount: function pickAmount(value) {
      this.amount = value;
      this.clearError('custom_amount');
      this.bumpPreview();
    },
    /** A region tile, or the "no idea yet" tile (null, null): fill the form and scroll to it. */pickTile: function pickTile(country, region) {
      var _document$getElementB5;
      if (country) {
        this.applyDestination(country, region || '', '');
      } else {
        this.pickOpenDestination();
      }
      (_document$getElementB5 = document.getElementById('gutschein')) === null || _document$getElementB5 === void 0 || _document$getElementB5.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    },
    pickOpenDestination: function pickOpenDestination() {
      this.applyDestination(open, open, '');
    },
    validate: function validate() {
      var errors = {};
      var amount = Number(this.customAmount);
      if (!this.country) {
        errors.country = messages.country;
      }
      if (this.amount === 'custom') {
        if (!(amount > 0)) {
          errors.custom_amount = messages.custom_amount;
        } else if (amount < config.minAmount || amount > config.maxAmount || !Number.isInteger(amount)) {
          errors.custom_amount = i18n.customRange;
        }
      }
      if (!this.name.trim()) {
        errors.name = messages.name;
      }
      if (!this.phone.trim()) {
        errors.phone = messages.phone;
      } else if (!PHONE_PATTERN.test(this.phone.trim()) || this.phone.replace(/\D/g, '').length < 6) {
        errors.phone = messages.phone_invalid;
      }
      if (!this.email.trim()) {
        errors.email = messages.email;
      } else if (!EMAIL_PATTERN.test(this.email.trim())) {
        errors.email = messages.email_invalid;
      }
      if (!this.consent) {
        errors.consent = messages.consent;
      }
      return errors;
    },
    submit: function submit() {
      var _this3 = this;
      return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var firstError, captcha;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              if (!_this3.submitting) {
                _context.n = 1;
                break;
              }
              return _context.a(2);
            case 1:
              _this3.errors = _this3.validate();
              firstError = ERROR_ORDER.find(function (field) {
                return _this3.errors[field];
              });
              if (!firstError) {
                _context.n = 2;
                break;
              }
              _this3.reveal(firstError === 'country' ? PLACE_INPUT_ID : firstError);
              return _context.a(2);
            case 2:
              _this3.submitting = true;
              captcha = typeof window.RecaptchaWidget === 'function' && document.querySelector(RECAPTCHA_SELECTOR) ? new window.RecaptchaWidget(RECAPTCHA_SELECTOR) : null;
              if (!captcha) {
                _context.n = 3;
                break;
              }
              _context.n = 3;
              return captcha.execute();
            case 3:
              _this3.$refs.form.submit();
            case 4:
              return _context.a(2);
          }
        }, _callee);
      }))();
    },
    reveal: function reveal(field) {
      var _input$closest;
      var input = field === PLACE_INPUT_ID ? document.getElementById(PLACE_INPUT_ID) : this.$root.querySelector("[name=\"".concat(field, "\"]"));
      input === null || input === void 0 || (_input$closest = input.closest('.vl-field')) === null || _input$closest === void 0 || _input$closest.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
      input === null || input === void 0 || input.focus({
        preventScroll: true
      });
    },
    /** Mobile bar: only between the hero and the form, and never once the request is sent. */watchViewport: function watchViewport() {
      var _this4 = this,
        _mobile$addEventListe;
      var hero = document.querySelector('[data-vl-hero]');
      var form = document.querySelector('[data-vl-form]');
      var mobile = window.matchMedia(MOBILE_MEDIA);
      if (config.submitted || !hero || !form || !('IntersectionObserver' in window)) {
        return;
      }
      var pastHero = false;
      var formIn = false;
      var update = function update() {
        _this4.showBar = mobile.matches && pastHero && !formIn;
      };
      new IntersectionObserver(function (_ref2) {
        var _ref3 = _slicedToArray(_ref2, 1),
          entry = _ref3[0];
        pastHero = !entry.isIntersecting && entry.boundingClientRect.bottom < 0;
        update();
      }).observe(hero);
      new IntersectionObserver(function (_ref4) {
        var _ref5 = _slicedToArray(_ref4, 1),
          entry = _ref5[0];
        formIn = entry.isIntersecting;
        update();
      }, {
        threshold: 0.02
      }).observe(form);
      (_mobile$addEventListe = mobile.addEventListener) === null || _mobile$addEventListe === void 0 || _mobile$addEventListe.call(mobile, 'change', update);
    },
    /** Overlay nav turns solid once the hero has scrolled away (as on the catalog pages). */watchNav: function watchNav() {
      var shell = document.querySelector('[data-vl-nav-shell]');
      var hero = document.querySelector('[data-vl-hero]');
      var nav = shell === null || shell === void 0 ? void 0 : shell.querySelector('.cag-site-nav');
      if (!nav || !hero) {
        return;
      }
      var sync = function sync() {
        return nav.classList.toggle('is-solid', hero.getBoundingClientRect().bottom <= nav.offsetHeight + 12);
      };
      sync();
      window.addEventListener('scroll', sync, {
        passive: true
      });
      window.addEventListener('resize', sync);
    },
    startHeroCarousel: function startHeroCarousel() {
      var slides = _toConsumableArray(document.querySelectorAll('[data-vl-hero-slide]'));
      if (slides.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
      }
      var index = 0;
      window.setInterval(function () {
        slides[index].classList.remove('is-active');
        index = (index + 1) % slides.length;
        slides[index].classList.add('is-active');
      }, HERO_SLIDE_MS);
    }
  };
}
document.addEventListener('alpine:init', function () {
  window.Alpine.data('voucherLanding', voucherLanding);
});
})();

/******/ })()
;