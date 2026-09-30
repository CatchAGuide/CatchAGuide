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

/***/ "./resources/js/checkout/calendar-state.js"
/*!*************************************************!*\
  !*** ./resources/js/checkout/calendar-state.js ***!
  \*************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   calendarNavigation: () => (/* binding */ calendarNavigation),
/* harmony export */   composeState: () => (/* binding */ composeState)
/* harmony export */ });
/* harmony import */ var _calendar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./calendar */ "./resources/js/checkout/calendar.js");
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
/**
 * Month navigation for the shared checkout calendar partial
 * (resources/views/pages/modern-checkout/partials/calendar.blade.php).
 *
 * The partial binds to: monthGrid, monthLabel, canGoPrev, prevMonth(), nextMonth(),
 * isSelected(iso), selectDate(iso), selectedLabel and errors.date. This part provides the
 * navigation; each page component adds its own selection rules (single date for the
 * checkout, several dates for the guide's reject form). Combine with composeState().
 */


/**
 * @param {TourCalendar} calendar
 * @param {{ minDate: string, months: string[] }} options
 */
function calendarNavigation(calendar, _ref) {
  var minDate = _ref.minDate,
    _ref$months = _ref.months,
    months = _ref$months === void 0 ? [] : _ref$months;
  return {
    viewYear: 0,
    viewMonth: 0,
    showMonthOf: function showMonthOf(iso) {
      var _TourCalendar$parts = _calendar__WEBPACK_IMPORTED_MODULE_0__.TourCalendar.parts(iso || minDate);
      this.viewYear = _TourCalendar$parts.year;
      this.viewMonth = _TourCalendar$parts.month;
    },
    get monthGrid() {
      return calendar.month(this.viewYear, this.viewMonth);
    },
    get monthLabel() {
      return "".concat(months[this.viewMonth] || '', " ").concat(this.viewYear);
    },
    get canGoPrev() {
      var min = _calendar__WEBPACK_IMPORTED_MODULE_0__.TourCalendar.parts(minDate);
      return this.viewYear > min.year || this.viewYear === min.year && this.viewMonth > min.month;
    },
    prevMonth: function prevMonth() {
      if (this.canGoPrev) this.shiftMonth(-1);
    },
    nextMonth: function nextMonth() {
      this.shiftMonth(1);
    },
    shiftMonth: function shiftMonth(delta) {
      var date = new Date(Date.UTC(this.viewYear, this.viewMonth + delta, 1));
      this.viewYear = date.getUTCFullYear();
      this.viewMonth = date.getUTCMonth();
    }
  };
}

/**
 * Merges component parts keeping getters as getters (object spread would freeze their values).
 */
function composeState() {
  for (var _len = arguments.length, parts = new Array(_len), _key = 0; _key < _len; _key++) {
    parts[_key] = arguments[_key];
  }
  return Object.defineProperties({}, Object.assign.apply(Object, [{}].concat(_toConsumableArray(parts.map(function (part) {
    return Object.getOwnPropertyDescriptors(part);
  })))));
}

/***/ },

/***/ "./resources/js/checkout/calendar.js"
/*!*******************************************!*\
  !*** ./resources/js/checkout/calendar.js ***!
  \*******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   TourCalendar: () => (/* binding */ TourCalendar),
/* harmony export */   isoDate: () => (/* binding */ isoDate)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
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
 * Month grid for the tour checkout calendar. Works on Y-m-d strings only, so no timezone can
 * shift a day: availability is "on or after minDate and outside every blocked range", which
 * mirrors the server rule (after:today + Guiding::isDateBlocked). On the reschedule page an
 * `allowed` list additionally limits it to the guide's suggested dates.
 */
var pad = function pad(n) {
  return String(n).padStart(2, '0');
};
var isoDate = function isoDate(year, month, day) {
  return "".concat(year, "-").concat(pad(month + 1), "-").concat(pad(day));
};
var TourCalendar = /*#__PURE__*/function () {
  /**
   * @param {{ minDate: string, blocked: Array<{from: string, due: string}>, allowed?: string[]|null }} options
   */
  function TourCalendar(_ref) {
    var minDate = _ref.minDate,
      blocked = _ref.blocked,
      _ref$allowed = _ref.allowed,
      allowed = _ref$allowed === void 0 ? null : _ref$allowed;
    _classCallCheck(this, TourCalendar);
    this.minDate = minDate;
    this.blocked = Array.isArray(blocked) ? blocked : [];
    this.allowed = Array.isArray(allowed) ? _toConsumableArray(allowed).sort() : null;
  }
  return _createClass(TourCalendar, [{
    key: "isAllowed",
    value: function isAllowed(iso) {
      return this.allowed === null || this.allowed.includes(iso);
    }
  }, {
    key: "isBlocked",
    value: function isBlocked(iso) {
      return this.blocked.some(function (range) {
        return iso >= range.from && iso <= range.due;
      });
    }
  }, {
    key: "isPast",
    value: function isPast(iso) {
      return iso < this.minDate;
    }
  }, {
    key: "isAvailable",
    value: function isAvailable(iso) {
      return !this.isPast(iso) && this.isAllowed(iso) && !this.isBlocked(iso);
    }

    /**
     * First bookable day on or after `from`, jumping over blocked ranges; null when none
     * is found within `maxDays`.
     */
  }, {
    key: "firstAvailable",
    value: function firstAvailable() {
      var _this = this;
      var from = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.minDate;
      var maxDays = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 730;
      if (this.allowed !== null) {
        return this.allowed.find(function (iso) {
          return iso >= from && _this.isAvailable(iso);
        }) || null;
      }
      var cursor = from < this.minDate ? this.minDate : from;
      for (var i = 0; i < maxDays; i++) {
        var range = this.blocked.find(function (r) {
          return cursor >= r.from && cursor <= r.due;
        });
        if (!range) {
          return cursor;
        }
        cursor = TourCalendar.addDays(range.due, 1);
      }
      return null;
    }

    /**
     * @returns {{ leadingBlanks: number, days: Array<{iso: string, day: number, past: boolean, blocked: boolean, available: boolean}> }}
     */
  }, {
    key: "month",
    value: function month(year, _month) {
      // Sunday-first grid, matching the weekday header.
      var leadingBlanks = new Date(Date.UTC(year, _month, 1)).getUTCDay();
      var length = new Date(Date.UTC(year, _month + 1, 0)).getUTCDate();
      var days = [];
      for (var day = 1; day <= length; day++) {
        var iso = isoDate(year, _month, day);
        var past = this.isPast(iso);
        var blocked = !past && (!this.isAllowed(iso) || this.isBlocked(iso));
        days.push({
          iso: iso,
          day: day,
          past: past,
          blocked: blocked,
          available: !past && !blocked
        });
      }
      return {
        leadingBlanks: leadingBlanks,
        days: days
      };
    }
  }], [{
    key: "addDays",
    value: function addDays(iso, amount) {
      var _iso$split$map = iso.split('-').map(Number),
        _iso$split$map2 = _slicedToArray(_iso$split$map, 3),
        y = _iso$split$map2[0],
        m = _iso$split$map2[1],
        d = _iso$split$map2[2];
      var date = new Date(Date.UTC(y, m - 1, d + amount));
      return isoDate(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate());
    }
  }, {
    key: "parts",
    value: function parts(iso) {
      var _iso$split$map3 = iso.split('-').map(Number),
        _iso$split$map4 = _slicedToArray(_iso$split$map3, 2),
        y = _iso$split$map4[0],
        m = _iso$split$map4[1];
      return {
        year: y,
        month: m - 1
      };
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
/*!*************************************************!*\
  !*** ./resources/js/checkout/booking-reject.js ***!
  \*************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _calendar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./calendar */ "./resources/js/checkout/calendar.js");
/* harmony import */ var _calendar_state__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./calendar-state */ "./resources/js/checkout/calendar-state.js");
/* harmony import */ var _booking_client__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./booking-client */ "./resources/js/checkout/booking-client.js");
/* harmony import */ var _support__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./support */ "./resources/js/checkout/support.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _objectWithoutProperties(e, t) { if (null == e) return {}; var o, r, i = _objectWithoutPropertiesLoose(e, t); if (Object.getOwnPropertySymbols) { var n = Object.getOwnPropertySymbols(e); for (r = 0; r < n.length; r++) o = n[r], -1 === t.indexOf(o) && {}.propertyIsEnumerable.call(e, o) && (i[o] = e[o]); } return i; }
function _objectWithoutPropertiesLoose(r, e) { if (null == r) return {}; var t = {}; for (var n in r) if ({}.hasOwnProperty.call(r, n)) { if (-1 !== e.indexOf(n)) continue; t[n] = r[n]; } return t; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Guide "decline request" form (resources/views/pages/modern-checkout/reject.blade.php).
 * Registers the `bookingReject` Alpine component: pick 1..maxDates alternative dates on the
 * shared checkout calendar, write a message, send. Rules mirror App\Http\Requests\RejectionRequest,
 * which stays authoritative. Boot data comes from BookingRejectViewModel::clientConfig().
 */




var CONFIG_ELEMENT_ID = 'booking-reject-config';
var ERROR_ORDER = ['date', 'message'];
function bookingReject() {
  var config = (0,_support__WEBPACK_IMPORTED_MODULE_3__.readConfig)(CONFIG_ELEMENT_ID);
  var i18n = config.i18n || {};
  var errorsText = i18n.errors || {};
  var maxDates = config.maxDates || 5;
  var minMessage = config.minMessage || 50;
  var calendar = new _calendar__WEBPACK_IMPORTED_MODULE_0__.TourCalendar({
    minDate: config.minDate,
    blocked: config.blocked
  });
  var client = new _booking_client__WEBPACK_IMPORTED_MODULE_2__.BookingClient(config.submitUrl, {
    alternative_dates: 'date',
    reason: 'message'
  });
  var chipFormat = new Intl.DateTimeFormat(config.locale || 'de', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    timeZone: 'UTC'
  });
  var longFormat = new Intl.DateTimeFormat(config.locale || 'de', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'UTC'
  });
  return (0,_calendar_state__WEBPACK_IMPORTED_MODULE_1__.composeState)((0,_calendar_state__WEBPACK_IMPORTED_MODULE_1__.calendarNavigation)(calendar, {
    minDate: config.minDate,
    months: i18n.months
  }), {
    dates: [],
    message: '',
    errors: {},
    formError: '',
    loading: false,
    init: function init() {
      // Open near the requested date: alternatives are usually close to it.
      var start = config.startDate && config.startDate > config.minDate ? config.startDate : config.minDate;
      this.showMonthOf(calendar.firstAvailable(start) || start);
    },
    // Calendar selection: several dates, tap again to remove.
    isSelected: function isSelected(iso) {
      return this.dates.includes(iso);
    },
    selectDate: function selectDate(iso) {
      if (this.isSelected(iso)) {
        this.dates = this.dates.filter(function (d) {
          return d !== iso;
        });
        return;
      }
      if (!calendar.isAvailable(iso)) return;
      if (this.dates.length >= maxDates) {
        this.errors = _objectSpread(_objectSpread({}, this.errors), {}, {
          date: errorsText.datesMax
        });
        return;
      }
      this.dates = [].concat(_toConsumableArray(this.dates), [iso]).sort();
      this.clearError('date');
    },
    get selectedLabel() {
      var _this = this;
      return this.dates.map(function (iso) {
        return _this.shortLabel(iso);
      }).join(', ') || i18n.noDates;
    },
    shortLabel: function shortLabel(iso) {
      return (0,_support__WEBPACK_IMPORTED_MODULE_3__.formatIsoDate)(chipFormat, iso);
    },
    removeLabel: function removeLabel(iso) {
      return (0,_support__WEBPACK_IMPORTED_MODULE_3__.trans)(i18n.removeDate || ':date', {
        date: (0,_support__WEBPACK_IMPORTED_MODULE_3__.formatIsoDate)(longFormat, iso)
      });
    },
    get countLabel() {
      return this.dates.length ? (0,_support__WEBPACK_IMPORTED_MODULE_3__.trans)(i18n.datesCount, {
        count: this.dates.length,
        max: maxDates
      }) : i18n.noDates;
    },
    // Message
    get messageOk() {
      return this.message.trim().length >= minMessage;
    },
    get charsLabel() {
      return (0,_support__WEBPACK_IMPORTED_MODULE_3__.trans)(i18n.chars, {
        count: this.message.trim().length,
        min: minMessage
      });
    },
    // Validation + submission
    clearError: function clearError(field) {
      if (!this.errors[field]) return;
      var _this$errors = this.errors,
        removed = _this$errors[field],
        rest = _objectWithoutProperties(_this$errors, [field].map(_toPropertyKey));
      this.errors = rest;
    },
    validate: function validate() {
      var errors = {};
      if (!this.dates.length) errors.date = errorsText.datesRequired;
      if (!this.messageOk) errors.message = errorsText.message;
      this.errors = errors;
      return ERROR_ORDER.find(function (field) {
        return errors[field];
      }) || null;
    },
    revealError: function revealError(field) {
      var target = this.$refs["field_".concat(field)];
      if (!target) return;
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
      var focusable = target.matches('textarea, button') ? target : target.querySelector('textarea');
      focusable === null || focusable === void 0 || focusable.focus({
        preventScroll: true
      });
    },
    submit: function submit() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var firstInvalid, result, firstServerError;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              if (!_this2.loading) {
                _context.n = 1;
                break;
              }
              return _context.a(2);
            case 1:
              _this2.formError = '';
              firstInvalid = _this2.validate();
              if (!firstInvalid) {
                _context.n = 2;
                break;
              }
              _this2.$nextTick(function () {
                return _this2.revealError(firstInvalid);
              });
              return _context.a(2);
            case 2:
              _this2.loading = true;
              _context.n = 3;
              return client.submit({
                alternative_dates: _this2.dates,
                reason: _this2.message.trim()
              });
            case 3:
              result = _context.v;
              if (!result.ok) {
                _context.n = 4;
                break;
              }
              // Replace the form entry so Back returns to the bookings list.
              // The form URL is no-store, so a history entry would be refetched
              // and show a second confirmation after the request is no longer pending.
              window.location.replace(result.redirectUrl);
              return _context.a(2);
            case 4:
              _this2.loading = false;
              _this2.errors = result.fieldErrors;
              if (result.status === 429) {
                _this2.formError = errorsText.tooManyRequests;
              } else if (result.message || result.status !== 422) {
                _this2.formError = result.message || errorsText.unexpected;
              }
              firstServerError = ERROR_ORDER.find(function (field) {
                return _this2.errors[field];
              });
              _this2.$nextTick(function () {
                return _this2.revealError(firstServerError || 'submit');
              });
            case 5:
              return _context.a(2);
          }
        }, _callee);
      }))();
    }
  });
}
document.addEventListener('alpine:init', function () {
  window.Alpine.data('bookingReject', bookingReject);
});
})();

/******/ })()
;