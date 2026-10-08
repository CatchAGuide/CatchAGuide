/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/checkout/booking-client.js"
/*!*************************************************!*\
  !*** ./resources/js/checkout/booking-client.js ***!
  \*************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   BookingClient: () => (/* binding */ BookingClient),
/* harmony export */   CHECKOUT_FIELDS: () => (/* binding */ CHECKOUT_FIELDS)
/* harmony export */ });
/* harmony import */ var _support__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./support */ "./resources/js/checkout/support.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Posts a checkout-style form as JSON (web stack: session + CSRF) and normalizes the outcome
 * into { ok, redirectUrl } | { ok: false, status, fieldErrors, message, retryAfter }.
 * Used by the tour checkout, the reschedule page and the guide's reject form.
 */


/** Server field name → client error key (checkout + reschedule). */
var CHECKOUT_FIELDS = {
  first_name: 'firstName',
  last_name: 'lastName',
  email: 'email',
  phone: 'phone',
  country_code: 'phone',
  selected_date: 'date',
  'g-recaptcha-response': 'captcha'
};

/** Only follow redirects that stay on this site. */
var sameOriginUrl = function sameOriginUrl(url) {
  try {
    var parsed = new URL(url, window.location.origin);
    return parsed.origin === window.location.origin ? parsed.href : null;
  } catch (e) {
    return null;
  }
};
var BookingClient = /*#__PURE__*/function () {
  /**
   * @param {string} url
   * @param {Object<string, string>} fieldMap server field (or its prefix before ".") → client error key
   */
  function BookingClient(url) {
    var fieldMap = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : CHECKOUT_FIELDS;
    _classCallCheck(this, BookingClient);
    this.url = url;
    this.fieldMap = fieldMap;
  }
  return _createClass(BookingClient, [{
    key: "submit",
    value: function () {
      var _submit = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(payload) {
        var _this = this,
          _ref3,
          _body$retry_after;
        var response, body, redirectUrl, fieldErrors, otherErrors, _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              _context.p = 0;
              _context.n = 1;
              return fetch(this.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                  'Content-Type': 'application/json',
                  Accept: 'application/json',
                  'X-Requested-With': 'XMLHttpRequest',
                  'X-CSRF-TOKEN': (0,_support__WEBPACK_IMPORTED_MODULE_0__.csrfToken)()
                },
                body: JSON.stringify(payload)
              });
            case 1:
              response = _context.v;
              _context.n = 3;
              break;
            case 2:
              _context.p = 2;
              _t = _context.v;
              return _context.a(2, {
                ok: false,
                status: 0,
                fieldErrors: {},
                message: null
              });
            case 3:
              _context.n = 4;
              return response.json()["catch"](function () {
                return {};
              });
            case 4:
              body = _context.v;
              if (!(response.ok && body.success)) {
                _context.n = 5;
                break;
              }
              redirectUrl = sameOriginUrl(body.redirect_url);
              return _context.a(2, redirectUrl ? {
                ok: true,
                redirectUrl: redirectUrl
              } : {
                ok: false,
                status: 500,
                fieldErrors: {},
                message: null
              });
            case 5:
              fieldErrors = {};
              otherErrors = [];
              Object.entries(body.errors || {}).forEach(function (_ref) {
                var _this$fieldMap$key;
                var _ref2 = _slicedToArray(_ref, 2),
                  key = _ref2[0],
                  messages = _ref2[1];
                var message = Array.isArray(messages) ? messages[0] : String(messages);
                // "alternative_dates.2" maps like "alternative_dates".
                var field = (_this$fieldMap$key = _this.fieldMap[key]) !== null && _this$fieldMap$key !== void 0 ? _this$fieldMap$key : _this.fieldMap[key.split('.')[0]];
                if (field && !fieldErrors[field]) {
                  fieldErrors[field] = message;
                } else if (!field) {
                  otherErrors.push(message);
                }
              });
              return _context.a(2, {
                ok: false,
                status: response.status,
                fieldErrors: fieldErrors,
                message: otherErrors[0] || (response.status === 422 ? null : body.message || null),
                retryAfter: parseInt((_ref3 = (_body$retry_after = body.retry_after) !== null && _body$retry_after !== void 0 ? _body$retry_after : response.headers.get('Retry-After')) !== null && _ref3 !== void 0 ? _ref3 : 0, 10) || 0
              });
          }
        }, _callee, this, [[0, 2]]);
      }));
      function submit(_x) {
        return _submit.apply(this, arguments);
      }
      return submit;
    }()
  }]);
}();

/***/ },

/***/ "./resources/js/checkout/contact-validator.js"
/*!****************************************************!*\
  !*** ./resources/js/checkout/contact-validator.js ***!
  \****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CONTACT_FIELDS: () => (/* binding */ CONTACT_FIELDS),
/* harmony export */   ContactValidator: () => (/* binding */ ContactValidator)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Client-side contact checks for instant feedback. The same rules are enforced by
 * App\Http\Requests\TourCheckoutRequest, which stays authoritative.
 */
var EMAIL_PATTERN = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
var PHONE_PATTERN = /^[0-9][0-9 ()/.-]{2,24}$/;
var CONTACT_FIELDS = ['firstName', 'lastName', 'email', 'phone'];
var ContactValidator = /*#__PURE__*/function () {
  /**
   * @param {Object<string, string>} messages checkout.tour.errors translations
   */
  function ContactValidator(messages) {
    _classCallCheck(this, ContactValidator);
    this.messages = messages || {};
  }

  /**
   * @param {{ firstName: string, lastName: string, email: string, phone: string }} contact
   * @returns {Object<string, string>} field => message, empty when valid
   */
  return _createClass(ContactValidator, [{
    key: "validate",
    value: function validate(contact) {
      var errors = {};
      var value = function value(key) {
        var _contact$key;
        return String((_contact$key = contact[key]) !== null && _contact$key !== void 0 ? _contact$key : '').trim();
      };
      if (!value('firstName')) errors.firstName = this.messages.firstName;
      if (!value('lastName')) errors.lastName = this.messages.lastName;
      if (!value('email')) errors.email = this.messages.emailRequired;else if (!EMAIL_PATTERN.test(value('email'))) errors.email = this.messages.emailInvalid;
      if (!value('phone')) errors.phone = this.messages.phoneRequired;else if (!PHONE_PATTERN.test(value('phone'))) errors.phone = this.messages.phoneInvalid;
      return errors;
    }
  }]);
}();

/***/ },

/***/ "./resources/js/checkout/pricing.js"
/*!******************************************!*\
  !*** ./resources/js/checkout/pricing.js ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   MoneyFormatter: () => (/* binding */ MoneyFormatter),
/* harmony export */   TourPricing: () => (/* binding */ TourPricing)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Client-side mirror of TourCheckoutPricing for instant totals. It only looks up the
 * server-computed price table; the server re-quotes on submission, so these numbers are
 * display-only.
 */
var round2 = function round2(value) {
  return Math.round(value * 100) / 100;
};
var TourPricing = /*#__PURE__*/function () {
  /**
   * @param {{ perPerson: boolean, table: Object<string, number>, extras: Array<{index: number, name: string, price: number, unit: string}> }} config
   */
  function TourPricing(_ref) {
    var perPerson = _ref.perPerson,
      table = _ref.table,
      extras = _ref.extras;
    _classCallCheck(this, TourPricing);
    this.perPerson = Boolean(perPerson);
    this.table = table || {};
    this.extras = Array.isArray(extras) ? extras : [];
  }
  return _createClass(TourPricing, [{
    key: "basePrice",
    value: function basePrice(persons) {
      var _this$table$persons;
      return Number((_this$table$persons = this.table[persons]) !== null && _this$table$persons !== void 0 ? _this$table$persons : 0);
    }

    /**
     * Mirrors TourExtraUnit::checkoutQuantity(): per-person extras scale with guests, booking
     * fees and items are charged once.
     */
  }, {
    key: "extraQuantity",
    value: function extraQuantity(extra, persons) {
      var _extra$unit;
      return ((_extra$unit = extra.unit) !== null && _extra$unit !== void 0 ? _extra$unit : 'per_person') === 'per_person' ? persons : 1;
    }
  }, {
    key: "extraTotal",
    value: function extraTotal(index, persons) {
      var extra = this.extras.find(function (candidate) {
        return candidate.index === index;
      });
      return extra ? round2(extra.price * this.extraQuantity(extra, persons)) : 0;
    }
  }, {
    key: "extrasTotal",
    value: function extrasTotal(persons, selected) {
      var _this = this;
      return round2(this.extras.filter(function (extra) {
        return selected.includes(extra.index);
      }).reduce(function (sum, extra) {
        return sum + extra.price * _this.extraQuantity(extra, persons);
      }, 0));
    }
  }, {
    key: "total",
    value: function total(persons, selected) {
      return round2(this.basePrice(persons) + this.extrasTotal(persons, selected));
    }
  }]);
}();
var MoneyFormatter = /*#__PURE__*/function () {
  function MoneyFormatter(locale) {
    var currency = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'EUR';
    _classCallCheck(this, MoneyFormatter);
    var options = {
      style: 'currency',
      currency: currency
    };
    this.whole = new Intl.NumberFormat(locale, _objectSpread(_objectSpread({}, options), {}, {
      maximumFractionDigits: 0
    }));
    this.cents = new Intl.NumberFormat(locale, _objectSpread(_objectSpread({}, options), {}, {
      minimumFractionDigits: 2
    }));
  }
  return _createClass(MoneyFormatter, [{
    key: "format",
    value: function format(amount) {
      var value = round2(Number(amount) || 0);
      return (Number.isInteger(value) ? this.whole : this.cents).format(value);
    }
  }]);
}();

/***/ },

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
/*!************************************************!*\
  !*** ./resources/js/checkout/trip-checkout.js ***!
  \************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _pricing__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pricing */ "./resources/js/checkout/pricing.js");
/* harmony import */ var _contact_validator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./contact-validator */ "./resources/js/checkout/contact-validator.js");
/* harmony import */ var _booking_client__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./booking-client */ "./resources/js/checkout/booking-client.js");
/* harmony import */ var _support__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./support */ "./resources/js/checkout/support.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
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
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
/**
 * Trip checkout page (resources/views/pages/trip-checkout). Registers the `tripCheckout`
 * Alpine component; Alpine itself ships with the Livewire bundle in the layout.
 *
 * Boot data comes from the JSON config rendered by TripCheckoutViewModel::clientConfig(), so no
 * server values are interpolated into this script. All dynamic text is rendered with x-text.
 * The estimate (price per person × party size) is display-only; the server re-estimates it.
 */




var CONFIG_ELEMENT_ID = 'trip-checkout-config';
var RECAPTCHA_SELECTOR = '#checkout-recaptcha';
var ERROR_ORDER = ['date', 'wish', 'persons'].concat(_toConsumableArray(_contact_validator__WEBPACK_IMPORTED_MODULE_1__.CONTACT_FIELDS), ['message', 'captcha']);

/** Server field → client error key (TripCheckoutRequest). */
var FIELD_MAP = _objectSpread(_objectSpread({}, _booking_client__WEBPACK_IMPORTED_MODULE_2__.CHECKOUT_FIELDS), {}, {
  departure_date: 'date',
  wish_start: 'wish',
  wish_end: 'wish',
  persons: 'persons',
  message: 'message'
});

/** "03.10.2026" / "10/3/2026" for a Y-m-d string, without timezone shifts. */
var shortDate = function shortDate(locale, iso) {
  return new Intl.DateTimeFormat(locale, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    timeZone: 'UTC'
  }).format(new Date("".concat(iso, "T00:00:00Z")));
};
function tripCheckout() {
  var config = (0,_support__WEBPACK_IMPORTED_MODULE_3__.readConfig)(CONFIG_ELEMENT_ID);
  var i18n = config.i18n || {};
  var locale = config.locale || 'de';
  // Service objects stay outside Alpine's reactive state.
  var money = new _pricing__WEBPACK_IMPORTED_MODULE_0__.MoneyFormatter(locale, config.currency || 'EUR');
  var validator = new _contact_validator__WEBPACK_IMPORTED_MODULE_1__.ContactValidator(i18n.errors);
  var client = new _booking_client__WEBPACK_IMPORTED_MODULE_2__.BookingClient(config.submitUrl, FIELD_MAP);
  var departures = Array.isArray(config.departures) ? config.departures : [];
  var pricePerPerson = Number(config.pricePerPerson) || 0;
  var submitAttempt = 0;
  return {
    i18n: i18n,
    fixedDates: Boolean(config.fixedDates),
    departureDate: config.departureDate || '',
    datesOpen: false,
    wishStart: '',
    wishEnd: '',
    persons: config.persons || 2,
    maxPersons: config.maxPersons || 20,
    contact: _objectSpread({}, config.contact || {}),
    message: '',
    errors: {},
    formError: '',
    loading: false,
    // Date
    get selected() {
      var _this = this;
      return departures.find(function (departure) {
        return departure.date === _this.departureDate;
      }) || null;
    },
    get hasDate() {
      return !this.fixedDates || Boolean(this.selected);
    },
    toggleDates: function toggleDates() {
      if (this.datesOpen) {
        this.closeDates();
      } else {
        this.openDates();
      }
    },
    openDates: function openDates() {
      var _this2 = this;
      this.datesOpen = true;
      this.$nextTick(function () {
        var _ref;
        var options = _this2.dateOptions();
        (_ref = options.find(function (option) {
          return option.dataset.date === _this2.departureDate;
        }) || options[0]) === null || _ref === void 0 || _ref.focus();
      });
    },
    closeDates: function closeDates() {
      var _this$$refs$datesTrig;
      if (!this.datesOpen) return;
      this.datesOpen = false;
      (_this$$refs$datesTrig = this.$refs.datesTrigger) === null || _this$$refs$datesTrig === void 0 || _this$$refs$datesTrig.focus();
    },
    dateOptions: function dateOptions() {
      var _this$$refs$datesList;
      return Array.from(((_this$$refs$datesList = this.$refs.datesList) === null || _this$$refs$datesList === void 0 ? void 0 : _this$$refs$datesList.querySelectorAll('[role="option"]')) || []);
    },
    moveDateFocus: function moveDateFocus(step) {
      var options = this.dateOptions();
      if (!options.length) return;
      var index = options.indexOf(document.activeElement);
      options[(index + step + options.length) % options.length].focus();
    },
    pickDate: function pickDate(date) {
      this.departureDate = date;
      this.clearError('date');
      this.closeDates();
    },
    openPicker: function openPicker(ref) {
      var input = this.$refs[ref];
      if (typeof (input === null || input === void 0 ? void 0 : input.showPicker) !== 'function') return;
      try {
        input.showPicker();
      } catch (error) {
        // This phone already opened its picker from the tap.
      }
    },
    // Both inputs are marked when one is missing (that one) or the order is wrong (both).
    wishInvalid: function wishInvalid(field) {
      if (!this.errors.wish) return false;
      return !this[field] || Boolean(this.wishStart) && Boolean(this.wishEnd);
    },
    get wishRange() {
      if (this.wishStart && this.wishEnd) {
        return "".concat(shortDate(locale, this.wishStart), " \u2013 ").concat(shortDate(locale, this.wishEnd));
      }
      return this.wishStart ? (0,_support__WEBPACK_IMPORTED_MODULE_3__.trans)(i18n.wishFrom, {
        date: shortDate(locale, this.wishStart)
      }) : '';
    },
    get sideDate() {
      if (!this.fixedDates) {
        return this.wishRange ? (0,_support__WEBPACK_IMPORTED_MODULE_3__.trans)(i18n.wishRange, {
          range: this.wishRange
        }) : i18n.wishOpen;
      }
      return this.selected ? this.selected.label : i18n.noDate;
    },
    get barSub() {
      var when = this.fixedDates ? this.selected ? this.selected["short"] : i18n.chooseDate : i18n.wish;
      return "".concat(when, " \xB7 ").concat(this.personsLabel);
    },
    // Party
    changePersons: function changePersons(delta) {
      this.persons = Math.max(1, Math.min(this.maxPersons, this.persons + delta));
      this.clearError('persons');
    },
    get personsLabel() {
      return (0,_support__WEBPACK_IMPORTED_MODULE_3__.choice)(i18n.personsCount, this.persons);
    },
    get capacityHint() {
      var _this$selected;
      var spots = (_this$selected = this.selected) === null || _this$selected === void 0 ? void 0 : _this$selected.spots;
      return spots !== null && spots !== undefined && this.persons > spots ? (0,_support__WEBPACK_IMPORTED_MODULE_3__.choice)(i18n.capacity, spots) : '';
    },
    // Estimate
    get total() {
      return pricePerPerson * this.persons;
    },
    // A fixed departure gets "approx."; a preferred window only "from" until the offer.
    get exact() {
      return this.fixedDates && Boolean(this.selected);
    },
    get lineLabel() {
      var price = pricePerPerson ? money.format(pricePerPerson) : i18n.onRequest;
      return (0,_support__WEBPACK_IMPORTED_MODULE_3__.trans)(this.exact || !pricePerPerson ? i18n.line : i18n.lineFrom, {
        persons: this.personsLabel,
        price: price
      });
    },
    get lineAmount() {
      if (!pricePerPerson) return i18n.onRequest;
      return this.exact ? money.format(this.total) : (0,_support__WEBPACK_IMPORTED_MODULE_3__.trans)(i18n.from, {
        amount: money.format(this.total)
      });
    },
    get totalLabel() {
      if (!pricePerPerson) return i18n.onRequest;
      return (0,_support__WEBPACK_IMPORTED_MODULE_3__.trans)(this.exact ? i18n.approx : i18n.from, {
        amount: money.format(this.total)
      });
    },
    get note() {
      return this.exact ? i18n.noteFixed : i18n.noteRequest;
    },
    // Validation + submission
    get hasErrors() {
      return Object.keys(this.errors).length > 0;
    },
    clearError: function clearError(field) {
      if (!this.errors[field]) return;
      var _this$errors = this.errors,
        removed = _this$errors[field],
        rest = _objectWithoutProperties(_this$errors, [field].map(_toPropertyKey));
      this.errors = rest;
    },
    recaptcha: function recaptcha() {
      return typeof window.RecaptchaWidget === 'function' && document.querySelector(RECAPTCHA_SELECTOR) ? new window.RecaptchaWidget(RECAPTCHA_SELECTOR) : null;
    },
    validate: function validate(captcha) {
      var _captcha$isInvisible, _i18n$errors4;
      var errors = validator.validate(this.contact);
      if (this.fixedDates) {
        var _i18n$errors;
        if (!this.selected) errors.date = (_i18n$errors = i18n.errors) === null || _i18n$errors === void 0 ? void 0 : _i18n$errors.date;
      } else if (!this.wishStart || !this.wishEnd) {
        var _i18n$errors2;
        errors.wish = (_i18n$errors2 = i18n.errors) === null || _i18n$errors2 === void 0 ? void 0 : _i18n$errors2.wishRequired;
      } else if (this.wishEnd < this.wishStart) {
        var _i18n$errors3;
        errors.wish = (_i18n$errors3 = i18n.errors) === null || _i18n$errors3 === void 0 ? void 0 : _i18n$errors3.wishOrder;
      }

      // Invisible widgets fetch their token on submit; only a checkbox must be solved first.
      if (captcha && !((_captcha$isInvisible = captcha.isInvisible) !== null && _captcha$isInvisible !== void 0 && _captcha$isInvisible.call(captcha)) && !captcha.getResponse()) errors.captcha = (_i18n$errors4 = i18n.errors) === null || _i18n$errors4 === void 0 ? void 0 : _i18n$errors4.captcha;
      this.errors = errors;
      return ERROR_ORDER.find(function (field) {
        return errors[field];
      }) || null;
    },
    // Mobile book bar covers the bottom of the viewport. A checkbox that still
    // sits under that bar (or further down the page) has to be scrolled up.
    captchaSitsBelow: function captchaSitsBelow(target) {
      if (!window.matchMedia('(max-width: 1023px)').matches) {
        return false;
      }
      var dock = document.querySelector('.cc-dock');
      var limit = dock ? dock.getBoundingClientRect().top : window.innerHeight;
      return target.getBoundingClientRect().bottom > limit - 8;
    },
    scrollCaptchaIntoView: function scrollCaptchaIntoView(target) {
      var dock = document.querySelector('.cc-dock');
      var dockHeight = dock ? dock.getBoundingClientRect().height : 0;
      var rect = target.getBoundingClientRect();
      var room = window.innerHeight - dockHeight - 24;
      var top = rect.height > room ? window.scrollY + rect.top - 16 : window.scrollY + rect.bottom - (window.innerHeight - dockHeight - 24);
      window.scrollTo({
        top: Math.max(0, top),
        behavior: 'smooth'
      });
    },
    revealError: function revealError(field) {
      var target = this.$refs["field_".concat(field)];
      if (!target) return;
      if (field === 'captcha' && this.captchaSitsBelow(target)) {
        this.scrollCaptchaIntoView(target);
      } else {
        // Keep the field clear of the sticky site nav and the mobile total bar.
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      }

      // The first empty date input, else the first control in the block.
      var focusable = target.matches('input, select, textarea, button') ? target : (field === 'wish' && !this.wishStart ? this.$refs.wishStart : null) || (field === 'wish' && !this.wishEnd ? this.$refs.wishEnd : null) || target.querySelector('input, select, textarea, button');
      focusable === null || focusable === void 0 || focusable.focus({
        preventScroll: true
      });
    },
    captchaToken: function captchaToken(captcha) {
      return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var _captcha$isInvisible2;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              if (captcha) {
                _context.n = 1;
                break;
              }
              return _context.a(2, '');
            case 1:
              if ((_captcha$isInvisible2 = captcha.isInvisible) !== null && _captcha$isInvisible2 !== void 0 && _captcha$isInvisible2.call(captcha)) {
                _context.n = 2;
                break;
              }
              return _context.a(2, captcha.getResponse());
            case 2:
              return _context.a(2, captcha.execute());
          }
        }, _callee);
      }))();
    },
    payload: function payload(captchaToken) {
      var dates = this.fixedDates ? {
        departure_date: this.departureDate
      } : {
        wish_start: this.wishStart,
        wish_end: this.wishEnd
      };
      return _objectSpread(_objectSpread({}, dates), {}, {
        persons: this.persons,
        first_name: this.contact.firstName,
        last_name: this.contact.lastName,
        email: this.contact.email,
        country_code: this.contact.countryCode,
        phone: this.contact.phone,
        message: this.message,
        'g-recaptcha-response': captchaToken
      });
    },
    submit: function submit() {
      var _this3 = this;
      return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
        var captcha, firstInvalid, attempt, captchaToken, _i18n$errors5, result, _i18n$errors6, _i18n$errors7, _i18n$errors8, firstServerError;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.n) {
            case 0:
              if (!_this3.loading) {
                _context2.n = 1;
                break;
              }
              return _context2.a(2);
            case 1:
              _this3.formError = '';
              captcha = _this3.recaptcha();
              firstInvalid = _this3.validate(captcha);
              if (!firstInvalid) {
                _context2.n = 2;
                break;
              }
              _this3.$nextTick(function () {
                return _this3.revealError(firstInvalid);
              });
              return _context2.a(2);
            case 2:
              attempt = ++submitAttempt;
              _context2.n = 3;
              return _this3.captchaToken(captcha);
            case 3:
              captchaToken = _context2.v;
              if (!(attempt !== submitAttempt || _this3.loading)) {
                _context2.n = 4;
                break;
              }
              return _context2.a(2);
            case 4:
              if (!(captcha && !captchaToken)) {
                _context2.n = 5;
                break;
              }
              _this3.errors = {
                captcha: (_i18n$errors5 = i18n.errors) === null || _i18n$errors5 === void 0 ? void 0 : _i18n$errors5.captcha
              };
              _this3.$nextTick(function () {
                return _this3.revealError('captcha');
              });
              return _context2.a(2);
            case 5:
              _this3.loading = true;
              _context2.n = 6;
              return client.submit(_this3.payload(captchaToken));
            case 6:
              result = _context2.v;
              if (!result.ok) {
                _context2.n = 7;
                break;
              }
              window.location.assign(result.redirectUrl);
              return _context2.a(2);
            case 7:
              _this3.loading = false;
              _this3.errors = result.fieldErrors;
              if (result.status === 429) {
                _this3.formError = result.retryAfter > 0 ? "".concat((_i18n$errors6 = i18n.errors) === null || _i18n$errors6 === void 0 ? void 0 : _i18n$errors6.tooManyRequests, " (").concat(result.retryAfter, "s)") : (_i18n$errors7 = i18n.errors) === null || _i18n$errors7 === void 0 ? void 0 : _i18n$errors7.tooManyRequests;
              } else if (result.message || result.status !== 422) {
                _this3.formError = result.message || ((_i18n$errors8 = i18n.errors) === null || _i18n$errors8 === void 0 ? void 0 : _i18n$errors8.unexpected);
              }

              // Only a request that reached validation consumed the captcha token.
              if (result.status === 422) {
                captcha === null || captcha === void 0 || captcha.reset();
              }
              firstServerError = ERROR_ORDER.find(function (field) {
                return _this3.errors[field];
              });
              _this3.$nextTick(function () {
                return firstServerError ? _this3.revealError(firstServerError) : _this3.revealError('submit');
              });
            case 8:
              return _context2.a(2);
          }
        }, _callee2);
      }))();
    }
  };
}
document.addEventListener('alpine:init', function () {
  window.Alpine.data('tripCheckout', tripCheckout);
});
})();

/******/ })()
;