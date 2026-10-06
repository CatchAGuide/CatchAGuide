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

/***/ "./resources/js/checkout/camp-pricing.js"
/*!***********************************************!*\
  !*** ./resources/js/checkout/camp-pricing.js ***!
  \***********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CampPricing: () => (/* binding */ CampPricing),
/* harmony export */   StayRate: () => (/* binding */ StayRate)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Client-side mirror of App\Services\Checkout\Camp\CampCheckoutPricing (+ StayRate) for
 * instant estimates. It only works on server-provided prices; the server re-quotes on
 * submission, so these numbers are display-only.
 */
var round2 = function round2(value) {
  return Math.round(value * 100) / 100;
};
var sameId = function sameId(a, b) {
  return String(a) === String(b);
};
var StayRate = /*#__PURE__*/function () {
  function StayRate() {
    var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref$daily = _ref.daily,
      daily = _ref$daily === void 0 ? null : _ref$daily,
      _ref$weekly = _ref.weekly,
      weekly = _ref$weekly === void 0 ? null : _ref$weekly;
    _classCallCheck(this, StayRate);
    this.daily = daily > 0 ? Number(daily) : null;
    this.weekly = weekly > 0 ? Number(weekly) : null;
  }

  /** Whole weeks at the weekly rate plus remaining nights, never above paying every night. */
  return _createClass(StayRate, [{
    key: "total",
    value: function total(units) {
      var n = Math.max(0, units);
      if (this.daily === null) {
        return this.weekly === null ? 0 : round2(Math.ceil(n / 7) * this.weekly);
      }
      var allDaily = n * this.daily;
      if (this.weekly === null || n < 7) return round2(allDaily);
      return round2(Math.min(allDaily, Math.floor(n / 7) * this.weekly + n % 7 * this.daily));
    }
  }]);
}();
var CampPricing = /*#__PURE__*/function () {
  /**
   * @param {{ accommodations: Array, boats: Array, tours: Array, specials: Array }} config
   */
  function CampPricing() {
    var _ref2 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref2$accommodations = _ref2.accommodations,
      accommodations = _ref2$accommodations === void 0 ? [] : _ref2$accommodations,
      _ref2$boats = _ref2.boats,
      boats = _ref2$boats === void 0 ? [] : _ref2$boats,
      _ref2$tours = _ref2.tours,
      tours = _ref2$tours === void 0 ? [] : _ref2$tours,
      _ref2$specials = _ref2.specials,
      specials = _ref2$specials === void 0 ? [] : _ref2$specials;
    _classCallCheck(this, CampPricing);
    this.accommodations = accommodations;
    this.boats = boats;
    this.tours = tours;
    this.specials = specials;
  }
  return _createClass(CampPricing, [{
    key: "find",
    value: function find(list, id) {
      return id === '' || id === null ? null : list.find(function (item) {
        return sameId(item.id, id);
      }) || null;
    }

    /** Tier for exactly that many guests, else the largest tier below, else the smallest. */
  }, {
    key: "accommodationRate",
    value: function accommodationRate(unit, persons) {
      var tiers = (unit === null || unit === void 0 ? void 0 : unit.tiers) || [];
      var match = tiers[0] || {};
      tiers.forEach(function (tier) {
        if (tier.persons <= persons) match = tier;
      });
      return new StayRate(match);
    }
  }, {
    key: "tourPrice",
    value: function tourPrice(tour, persons) {
      var counts = Object.keys((tour === null || tour === void 0 ? void 0 : tour.prices) || {}).map(Number);
      if (!counts.length) return 0;
      return Number(tour.prices[Math.min(Math.max(1, persons), Math.max.apply(Math, _toConsumableArray(counts)))] || 0);
    }

    /**
     * @returns {Array<{ key: string, type: string, name: string, quantity: number, unitPrice: ?number, amount: number }>}
     */
  }, {
    key: "quote",
    value: function quote(_ref3) {
      var nights = _ref3.nights,
        persons = _ref3.persons,
        accommodationId = _ref3.accommodationId,
        boatId = _ref3.boatId,
        tourId = _ref3.tourId,
        specialId = _ref3.specialId;
      var lines = [];
      var unit = this.find(this.accommodations, accommodationId);
      var boat = this.find(this.boats, boatId);
      var tour = this.find(this.tours, tourId);
      var special = this.find(this.specials, specialId);
      if (unit) {
        var rate = this.accommodationRate(unit, persons);
        // Mirrors AccommodationPriceUnit::guestFactor(): per-person-night units charge every guest.
        var factor = unit.unit === 'per_person_night' ? Math.max(1, persons) : 1;
        lines.push({
          key: "a".concat(unit.id),
          type: 'accommodation',
          name: unit.name,
          quantity: nights,
          unitPrice: rate.daily === null ? null : rate.daily * factor,
          amount: rate.total(nights) * factor
        });
      }
      if (boat) {
        var _rate = new StayRate(boat);
        lines.push({
          key: "b".concat(boat.id),
          type: 'boat',
          name: boat.name,
          quantity: nights,
          unitPrice: _rate.daily,
          amount: _rate.total(nights)
        });
      }
      if (tour) {
        var price = this.tourPrice(tour, persons);
        lines.push({
          key: "t".concat(tour.id),
          type: 'tour',
          name: tour.name,
          quantity: 1,
          unitPrice: price,
          amount: price
        });
      }
      if (special) {
        lines.push({
          key: "s".concat(special.id),
          type: 'special',
          name: special.name,
          quantity: 1,
          unitPrice: special.price,
          amount: Number(special.price) || 0
        });
      }
      return lines;
    }
  }, {
    key: "total",
    value: function total(lines) {
      return round2(lines.reduce(function (sum, line) {
        return sum + line.amount;
      }, 0));
    }
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
  !*** ./resources/js/checkout/camp-checkout.js ***!
  \************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _camp_pricing__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./camp-pricing */ "./resources/js/checkout/camp-pricing.js");
/* harmony import */ var _pricing__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./pricing */ "./resources/js/checkout/pricing.js");
/* harmony import */ var _contact_validator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./contact-validator */ "./resources/js/checkout/contact-validator.js");
/* harmony import */ var _booking_client__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./booking-client */ "./resources/js/checkout/booking-client.js");
/* harmony import */ var _support__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./support */ "./resources/js/checkout/support.js");
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
 * Camp checkout page (resources/views/pages/camp-checkout). Registers the `campCheckout`
 * Alpine component; Alpine itself ships with the Livewire bundle in the layout.
 *
 * Boot data comes from the JSON config rendered by CampCheckoutViewModel::clientConfig(), so no
 * server values are interpolated into this script. All dynamic text is rendered with x-text.
 */





var CONFIG_ELEMENT_ID = 'camp-checkout-config';
var RECAPTCHA_SELECTOR = '#checkout-recaptcha';
var ERROR_ORDER = ['date', 'nights', 'persons', 'accommodation', 'boat', 'tour', 'special'].concat(_toConsumableArray(_contact_validator__WEBPACK_IMPORTED_MODULE_2__.CONTACT_FIELDS), ['message', 'captcha']);

/** Server field → client error key (CampCheckoutRequest). */
var FIELD_MAP = _objectSpread(_objectSpread({}, _booking_client__WEBPACK_IMPORTED_MODULE_3__.CHECKOUT_FIELDS), {}, {
  arrival_date: 'date',
  nights: 'nights',
  persons: 'persons',
  accommodation_id: 'accommodation',
  rental_boat_id: 'boat',
  guiding_id: 'tour',
  special_offer_id: 'special',
  message: 'message'
});
var idOrEmpty = function idOrEmpty(value) {
  return value === null || value === undefined ? '' : String(value);
};
var idOrNull = function idOrNull(value) {
  return value === '' ? null : Number(value);
};
function campCheckout() {
  var config = (0,_support__WEBPACK_IMPORTED_MODULE_4__.readConfig)(CONFIG_ELEMENT_ID);
  var i18n = config.i18n || {};
  // Service objects stay outside Alpine's reactive state.
  var pricing = new _camp_pricing__WEBPACK_IMPORTED_MODULE_0__.CampPricing(config.pricing || {});
  var _money = new _pricing__WEBPACK_IMPORTED_MODULE_1__.MoneyFormatter(config.locale || 'de', config.currency || 'EUR');
  var validator = new _contact_validator__WEBPACK_IMPORTED_MODULE_2__.ContactValidator(i18n.errors);
  var client = new _booking_client__WEBPACK_IMPORTED_MODULE_3__.BookingClient(config.submitUrl, FIELD_MAP);
  var submitAttempt = 0;
  return {
    options: config.pricing || {
      accommodations: [],
      boats: [],
      tours: [],
      specials: []
    },
    arrivalDate: config.arrivalDate || '',
    nights: config.nights || 1,
    persons: config.persons || 1,
    maxNights: config.maxNights || 30,
    maxPersons: config.maxPersons || 20,
    accommodationId: idOrEmpty(config.accommodationId),
    boatId: '',
    tourId: '',
    specialId: '',
    contact: _objectSpread({}, config.contact || {}),
    message: '',
    errors: {},
    formError: '',
    loading: false,
    // Stay
    get selectedUnit() {
      return pricing.find(pricing.accommodations, this.accommodationId);
    },
    get minNights() {
      var _this$selectedUnit;
      return Math.max(1, ((_this$selectedUnit = this.selectedUnit) === null || _this$selectedUnit === void 0 ? void 0 : _this$selectedUnit.minNights) || 1);
    },
    get minNightsLabel() {
      return (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.minNights, {
        count: this.minNights
      });
    },
    openArrivalPicker: function openArrivalPicker() {
      var input = this.$refs.arrivalInput;
      if (typeof (input === null || input === void 0 ? void 0 : input.showPicker) !== 'function') {
        return;
      }
      try {
        input.showPicker();
      } catch (error) {
        // This phone already opened its picker from the tap.
      }
    },
    changeNights: function changeNights(delta) {
      this.nights = Math.max(this.minNights, Math.min(this.maxNights, this.nights + delta));
      this.clearError('nights');
    },
    changePersons: function changePersons(delta) {
      this.persons = Math.max(1, Math.min(this.maxPersons, this.persons + delta));
      this.clearError('persons');
    },
    onAccommodationChange: function onAccommodationChange() {
      this.clearError('accommodation');
      if (this.nights < this.minNights) {
        this.nights = this.minNights;
        this.clearError('nights');
      }
    },
    get overCapacity() {
      return Boolean(this.selectedUnit) && this.persons > this.selectedUnit.capacity;
    },
    get overCapacityLabel() {
      var _this$selectedUnit$ca, _this$selectedUnit2;
      return (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.overCapacity, {
        cap: (_this$selectedUnit$ca = (_this$selectedUnit2 = this.selectedUnit) === null || _this$selectedUnit2 === void 0 ? void 0 : _this$selectedUnit2.capacity) !== null && _this$selectedUnit$ca !== void 0 ? _this$selectedUnit$ca : ''
      });
    },
    get stayShort() {
      return "".concat((0,_support__WEBPACK_IMPORTED_MODULE_4__.choice)(i18n.nightsCount, this.nights), " \xB7 ").concat((0,_support__WEBPACK_IMPORTED_MODULE_4__.choice)(i18n.personsCount, this.persons));
    },
    // Option labels (prices follow the party size)
    unitLabel: function unitLabel(unit) {
      var _rate$daily;
      var rate = pricing.accommodationRate(unit, this.persons);
      var price = (_rate$daily = rate.daily) !== null && _rate$daily !== void 0 ? _rate$daily : rate.weekly ? rate.weekly / 7 : null;
      return price ? (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.unitOption, {
        name: unit.name,
        cap: unit.capacity,
        price: this.money(price)
      }) : (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.optionOnRequest, {
        name: unit.name,
        cap: unit.capacity
      });
    },
    boatLabel: function boatLabel(boat) {
      var _boat$daily;
      var price = (_boat$daily = boat.daily) !== null && _boat$daily !== void 0 ? _boat$daily : boat.weekly ? boat.weekly / 7 : null;
      return price ? (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.boatOption, {
        name: boat.name,
        cap: boat.capacity,
        price: this.money(price)
      }) : (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.optionOnRequest, {
        name: boat.name,
        cap: boat.capacity
      });
    },
    tourLabel: function tourLabel(tour) {
      var price = pricing.tourPrice(tour, this.persons);
      return price ? (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.tourOption, {
        name: tour.name,
        cap: tour.capacity,
        price: this.money(price)
      }) : (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.optionOnRequest, {
        name: tour.name,
        cap: tour.capacity
      });
    },
    specialLabel: function specialLabel(special) {
      return special.price ? (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.specialOption, {
        name: special.name,
        price: this.money(special.price)
      }) : (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.specialOnRequest, {
        name: special.name
      });
    },
    // Estimate
    get quoteLines() {
      return pricing.quote({
        nights: this.nights,
        persons: this.persons,
        accommodationId: this.accommodationId,
        boatId: this.boatId,
        tourId: this.tourId,
        specialId: this.specialId
      });
    },
    // Name can wrap; the "· 3 Nächte × 55 €" tail stays one piece so its amount shares that baseline.
    lineParts: function lineParts(line) {
      var _this = this;
      var exact = line.unitPrice && Math.abs(line.unitPrice * line.quantity - line.amount) < 0.01;
      var withRate = function withRate(text) {
        return exact ? (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.lineRate, {
          label: text,
          price: _this.money(line.unitPrice)
        }) : text;
      };
      if (line.type === 'accommodation') {
        return {
          name: line.name,
          detail: withRate((0,_support__WEBPACK_IMPORTED_MODULE_4__.choice)(i18n.nightsCount, line.quantity))
        };
      }
      if (line.type === 'boat') {
        var days = (0,_support__WEBPACK_IMPORTED_MODULE_4__.choice)(i18n.daysCount, line.quantity);
        var full = (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.lineBoat, {
          days: days
        });
        var name = full.endsWith(days) ? full.slice(0, -days.length).replace(/[\t-\r \xA0\xB7\u1680\u2000-\u200A\u2028\u2029\u202F\u205F\u3000\uFEFF]+$/, '') : full;
        return {
          name: name,
          detail: withRate(days)
        };
      }
      if (line.type === 'tour') {
        return {
          name: (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.lineTour, {
            name: line.name
          }),
          detail: ''
        };
      }
      return {
        name: line.name,
        detail: ''
      };
    },
    get lines() {
      var _this2 = this;
      return this.quoteLines.map(function (line) {
        return _objectSpread(_objectSpread({
          key: line.key
        }, _this2.lineParts(line)), {}, {
          amount: line.amount > 0 ? _this2.money(line.amount) : i18n.onRequest
        });
      });
    },
    get total() {
      return pricing.total(this.quoteLines);
    },
    get totalLabel() {
      return this.total > 0 ? (0,_support__WEBPACK_IMPORTED_MODULE_4__.trans)(i18n.approx, {
        amount: this.money(this.total)
      }) : i18n.onRequest;
    },
    money: function money(amount) {
      return _money.format(amount);
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
      var _i18n$errors, _captcha$isInvisible, _i18n$errors2;
      var errors = validator.validate(this.contact);
      if (pricing.accommodations.length && !this.selectedUnit) errors.accommodation = (_i18n$errors = i18n.errors) === null || _i18n$errors === void 0 ? void 0 : _i18n$errors.accommodation;
      // Invisible widgets fetch their token on submit; only a checkbox must be solved first.
      if (captcha && !((_captcha$isInvisible = captcha.isInvisible) !== null && _captcha$isInvisible !== void 0 && _captcha$isInvisible.call(captcha)) && !captcha.getResponse()) errors.captcha = (_i18n$errors2 = i18n.errors) === null || _i18n$errors2 === void 0 ? void 0 : _i18n$errors2.captcha;
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
      var focusable = target.matches('input, select, textarea, button') ? target : target.querySelector('input, select, textarea, button');
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
      return {
        arrival_date: this.arrivalDate || null,
        nights: this.nights,
        persons: this.persons,
        accommodation_id: idOrNull(this.accommodationId),
        rental_boat_id: idOrNull(this.boatId),
        guiding_id: idOrNull(this.tourId),
        special_offer_id: idOrNull(this.specialId),
        first_name: this.contact.firstName,
        last_name: this.contact.lastName,
        email: this.contact.email,
        country_code: this.contact.countryCode,
        phone: this.contact.phone,
        message: this.message,
        'g-recaptcha-response': captchaToken
      };
    },
    submit: function submit() {
      var _this3 = this;
      return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
        var captcha, firstInvalid, attempt, captchaToken, _i18n$errors3, result, _i18n$errors4, _i18n$errors5, _i18n$errors6, firstServerError;
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
                captcha: (_i18n$errors3 = i18n.errors) === null || _i18n$errors3 === void 0 ? void 0 : _i18n$errors3.captcha
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
                _this3.formError = result.retryAfter > 0 ? "".concat((_i18n$errors4 = i18n.errors) === null || _i18n$errors4 === void 0 ? void 0 : _i18n$errors4.tooManyRequests, " (").concat(result.retryAfter, "s)") : (_i18n$errors5 = i18n.errors) === null || _i18n$errors5 === void 0 ? void 0 : _i18n$errors5.tooManyRequests;
              } else if (result.message || result.status !== 422) {
                _this3.formError = result.message || ((_i18n$errors6 = i18n.errors) === null || _i18n$errors6 === void 0 ? void 0 : _i18n$errors6.unexpected);
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
  window.Alpine.data('campCheckout', campCheckout);
});
})();

/******/ })()
;