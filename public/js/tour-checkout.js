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
   * @param {{ perPerson: boolean, table: Object<string, number>, extras: Array<{index: number, name: string, price: number}> }} config
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
  }, {
    key: "extrasTotal",
    value: function extrasTotal(persons, selected) {
      return round2(this.extras.filter(function (extra) {
        return selected.includes(extra.index);
      }).reduce(function (sum, extra) {
        return sum + extra.price * persons;
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

/***/ "./resources/js/checkout/sticky-bar.js"
/*!*********************************************!*\
  !*** ./resources/js/checkout/sticky-bar.js ***!
  \*********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   StickyBarWatcher: () => (/* binding */ StickyBarWatcher)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Mobile total + reserve bar: shown once the product card has scrolled away and hidden again
 * while the in-page call to action is on screen. Desktop keeps it hidden via CSS, and the
 * observers only report while the mobile media query matches.
 */
var CTA_REVEAL = 60;
var StickyBarWatcher = /*#__PURE__*/function () {
  /**
   * @param {{ anchor: Element, cta: Element, media: string, onChange: (visible: boolean) => void }} options
   */
  function StickyBarWatcher(_ref) {
    var _this = this;
    var anchor = _ref.anchor,
      cta = _ref.cta,
      media = _ref.media,
      onChange = _ref.onChange;
    _classCallCheck(this, StickyBarWatcher);
    this.anchor = anchor;
    this.cta = cta;
    this.onChange = onChange;
    this.mediaQuery = window.matchMedia(media);
    this.pastAnchor = false;
    this.ctaVisible = false;
    this.observers = [];
    this.handleMedia = function () {
      return _this.emit();
    };
  }
  return _createClass(StickyBarWatcher, [{
    key: "start",
    value: function start() {
      var _this2 = this;
      if (!this.anchor || !this.cta || !('IntersectionObserver' in window)) {
        return this;
      }
      this.observers = [new IntersectionObserver(function (_ref2) {
        var _ref3 = _slicedToArray(_ref2, 1),
          entry = _ref3[0];
        _this2.pastAnchor = !entry.isIntersecting && entry.boundingClientRect.top < 0;
        _this2.emit();
      }),
      // The CTA only counts as visible once it is CTA_REVEAL px into the viewport, so a
      // sliver of it at the bottom edge doesn't hide the bar (as in the mobile design).
      new IntersectionObserver(function (_ref4) {
        var _ref5 = _slicedToArray(_ref4, 1),
          entry = _ref5[0];
        _this2.ctaVisible = entry.isIntersecting;
        _this2.emit();
      }, {
        rootMargin: "0px 0px -".concat(CTA_REVEAL, "px 0px")
      })];
      this.observers[0].observe(this.anchor);
      this.observers[1].observe(this.cta);
      this.mediaQuery.addEventListener('change', this.handleMedia);
      return this;
    }
  }, {
    key: "stop",
    value: function stop() {
      this.observers.forEach(function (observer) {
        return observer.disconnect();
      });
      this.mediaQuery.removeEventListener('change', this.handleMedia);
    }
  }, {
    key: "emit",
    value: function emit() {
      this.onChange(this.mediaQuery.matches && this.pastAnchor && !this.ctaVisible);
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
  !*** ./resources/js/checkout/tour-checkout.js ***!
  \************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _calendar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./calendar */ "./resources/js/checkout/calendar.js");
/* harmony import */ var _calendar_state__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./calendar-state */ "./resources/js/checkout/calendar-state.js");
/* harmony import */ var _pricing__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./pricing */ "./resources/js/checkout/pricing.js");
/* harmony import */ var _contact_validator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./contact-validator */ "./resources/js/checkout/contact-validator.js");
/* harmony import */ var _booking_client__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./booking-client */ "./resources/js/checkout/booking-client.js");
/* harmony import */ var _sticky_bar__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./sticky-bar */ "./resources/js/checkout/sticky-bar.js");
/* harmony import */ var _support__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./support */ "./resources/js/checkout/support.js");
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
 * Tour checkout page (resources/views/pages/modern-checkout). Registers the `tourCheckout`
 * Alpine component; Alpine itself ships with the Livewire bundle in the layout.
 *
 * Boot data comes from the JSON config rendered by TourCheckoutViewModel::clientConfig(), so no
 * server values are interpolated into this script. All dynamic text is rendered with x-text.
 */







var CONFIG_ELEMENT_ID = 'tour-checkout-config';
var MOBILE_MEDIA = '(max-width: 1023px)'; // include-media "<desktop"
var RECAPTCHA_SELECTOR = '#checkout-recaptcha';
var ERROR_ORDER = ['date'].concat(_toConsumableArray(_contact_validator__WEBPACK_IMPORTED_MODULE_3__.CONTACT_FIELDS), ['captcha']);
function tourCheckout() {
  var config = (0,_support__WEBPACK_IMPORTED_MODULE_6__.readConfig)(CONFIG_ELEMENT_ID);
  var i18n = config.i18n || {};
  // Service objects stay outside Alpine's reactive state.
  var calendar = new _calendar__WEBPACK_IMPORTED_MODULE_0__.TourCalendar({
    minDate: config.minDate,
    blocked: config.blocked,
    allowed: config.allowedDates
  });
  // Reschedule sends the edited contact with the new date, and no guiding id or captcha.
  var reschedule = Boolean(config.reschedule);
  var pricing = new _pricing__WEBPACK_IMPORTED_MODULE_2__.TourPricing(config.pricing || {});
  var _money = new _pricing__WEBPACK_IMPORTED_MODULE_2__.MoneyFormatter(config.locale || 'de');
  var validator = new _contact_validator__WEBPACK_IMPORTED_MODULE_3__.ContactValidator(i18n.errors);
  var client = new _booking_client__WEBPACK_IMPORTED_MODULE_4__.BookingClient(config.submitUrl);
  var dateFormat = new Intl.DateTimeFormat(config.locale || 'de', {
    weekday: 'short',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'UTC'
  });
  var shortDateFormat = new Intl.DateTimeFormat(config.locale || 'de', {
    day: 'numeric',
    month: 'short',
    timeZone: 'UTC'
  });
  var stickyBar = null;
  var submitAttempt = 0;
  return (0,_calendar_state__WEBPACK_IMPORTED_MODULE_1__.composeState)((0,_calendar_state__WEBPACK_IMPORTED_MODULE_1__.calendarNavigation)(calendar, {
    minDate: config.minDate,
    months: i18n.months
  }), {
    persons: config.persons || 1,
    maxGuests: config.maxGuests || 1,
    selectedDate: config.selectedDate || null,
    selectedExtras: Array.isArray(config.initialExtras) ? _toConsumableArray(config.initialExtras) : [],
    contact: _objectSpread({}, config.contact || {}),
    errors: {},
    formError: '',
    loading: false,
    showBar: false,
    init: function init() {
      var _this = this;
      this.showMonthOf(this.selectedDate || calendar.firstAvailable());
      stickyBar = new _sticky_bar__WEBPACK_IMPORTED_MODULE_5__.StickyBarWatcher({
        anchor: this.$refs.product,
        cta: this.$refs.cta,
        media: MOBILE_MEDIA,
        onChange: function onChange(visible) {
          _this.showBar = visible;
        }
      }).start();
    },
    destroy: function destroy() {
      var _stickyBar;
      (_stickyBar = stickyBar) === null || _stickyBar === void 0 || _stickyBar.stop();
    },
    // Calendar selection (navigation comes from calendarNavigation)
    isSelected: function isSelected(iso) {
      return iso === this.selectedDate;
    },
    selectDate: function selectDate(iso) {
      if (!calendar.isAvailable(iso)) return;
      this.selectedDate = iso;
      this.clearError('date');
    },
    get selectedLabel() {
      return this.selectedDate ? (0,_support__WEBPACK_IMPORTED_MODULE_6__.formatIsoDate)(dateFormat, this.selectedDate) : i18n.noDate;
    },
    // Guests
    get atMax() {
      return this.persons >= this.maxGuests;
    },
    changePersons: function changePersons(delta) {
      this.persons = Math.max(1, Math.min(this.maxGuests, this.persons + delta));
    },
    get unitLabel() {
      return this.persons === 1 ? i18n.person : i18n.persons;
    },
    get participantsLabel() {
      return (0,_support__WEBPACK_IMPORTED_MODULE_6__.choice)(i18n.participants, this.persons);
    },
    // Pricing
    isExtraSelected: function isExtraSelected(index) {
      return this.selectedExtras.includes(index);
    },
    toggleExtra: function toggleExtra(index) {
      this.selectedExtras = this.isExtraSelected(index) ? this.selectedExtras.filter(function (i) {
        return i !== index;
      }) : [].concat(_toConsumableArray(this.selectedExtras), [index]);
    },
    extraTotal: function extraTotal(price) {
      return this.money(price * this.persons);
    },
    get basePrice() {
      return pricing.basePrice(this.persons);
    },
    get baseLine() {
      return pricing.perPerson ? (0,_support__WEBPACK_IMPORTED_MODULE_6__.trans)(i18n.perPersonLine, {
        price: this.money(this.basePrice / this.persons),
        count: this.persons,
        unit: this.unitLabel
      }) : (0,_support__WEBPACK_IMPORTED_MODULE_6__.trans)(i18n.fixedLine, {
        count: this.persons,
        unit: this.unitLabel
      });
    },
    get total() {
      return pricing.total(this.persons, this.selectedExtras);
    },
    get barSubline() {
      var date = this.selectedDate ? (0,_support__WEBPACK_IMPORTED_MODULE_6__.formatIsoDate)(shortDateFormat, this.selectedDate) : i18n.noDate;
      return "".concat(this.participantsLabel, " \xB7 ").concat(date);
    },
    money: function money(amount) {
      return _money.format(amount);
    },
    // Validation + submission
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
      if (!this.selectedDate) errors.date = (_i18n$errors = i18n.errors) === null || _i18n$errors === void 0 ? void 0 : _i18n$errors.date;
      // Invisible widgets fetch their token on submit; only a checkbox must be solved first.
      if (captcha && !((_captcha$isInvisible = captcha.isInvisible) !== null && _captcha$isInvisible !== void 0 && _captcha$isInvisible.call(captcha)) && !captcha.getResponse()) errors.captcha = (_i18n$errors2 = i18n.errors) === null || _i18n$errors2 === void 0 ? void 0 : _i18n$errors2.captcha;
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
      var focusable = target.matches('input, select, button') ? target : target.querySelector('input, select, button');
      focusable === null || focusable === void 0 || focusable.focus({
        preventScroll: true
      });
    },
    /**
     * Token to send with the request. An invisible widget is run now; if the visitor closes a
     * challenge, the next click simply starts a new run (the unanswered one resolves empty).
     */
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
      var booking = {
        persons: this.persons,
        selected_date: this.selectedDate,
        extras: this.selectedExtras,
        first_name: this.contact.firstName,
        last_name: this.contact.lastName,
        email: this.contact.email,
        country_code: this.contact.countryCode,
        phone: this.contact.phone
      };
      if (reschedule) {
        return booking;
      }
      return _objectSpread(_objectSpread({}, booking), {}, {
        guiding_id: config.guidingId,
        'g-recaptcha-response': captchaToken
      });
    },
    submit: function submit() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
        var captcha, firstInvalid, attempt, captchaToken, _i18n$errors3, result, _i18n$errors4, _i18n$errors5, _i18n$errors6, firstServerError;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.n) {
            case 0:
              if (!_this2.loading) {
                _context2.n = 1;
                break;
              }
              return _context2.a(2);
            case 1:
              _this2.formError = '';
              captcha = _this2.recaptcha();
              firstInvalid = _this2.validate(captcha);
              if (!firstInvalid) {
                _context2.n = 2;
                break;
              }
              _this2.$nextTick(function () {
                return _this2.revealError(firstInvalid);
              });
              return _context2.a(2);
            case 2:
              attempt = ++submitAttempt;
              _context2.n = 3;
              return _this2.captchaToken(captcha);
            case 3:
              captchaToken = _context2.v;
              if (!(attempt !== submitAttempt || _this2.loading)) {
                _context2.n = 4;
                break;
              }
              return _context2.a(2);
            case 4:
              if (!(captcha && !captchaToken)) {
                _context2.n = 5;
                break;
              }
              _this2.errors = {
                captcha: (_i18n$errors3 = i18n.errors) === null || _i18n$errors3 === void 0 ? void 0 : _i18n$errors3.captcha
              };
              _this2.$nextTick(function () {
                return _this2.revealError('captcha');
              });
              return _context2.a(2);
            case 5:
              _this2.loading = true;
              _context2.n = 6;
              return client.submit(_this2.payload(captchaToken));
            case 6:
              result = _context2.v;
              if (!result.ok) {
                _context2.n = 7;
                break;
              }
              window.location.assign(result.redirectUrl);
              return _context2.a(2);
            case 7:
              _this2.loading = false;
              _this2.errors = result.fieldErrors;
              if (result.status === 429) {
                _this2.formError = result.retryAfter > 0 ? "".concat((_i18n$errors4 = i18n.errors) === null || _i18n$errors4 === void 0 ? void 0 : _i18n$errors4.tooManyRequests, " (").concat(result.retryAfter, "s)") : (_i18n$errors5 = i18n.errors) === null || _i18n$errors5 === void 0 ? void 0 : _i18n$errors5.tooManyRequests;
              } else if (result.message || result.status !== 422) {
                _this2.formError = result.message || ((_i18n$errors6 = i18n.errors) === null || _i18n$errors6 === void 0 ? void 0 : _i18n$errors6.unexpected);
              }

              // Only a request that reached validation consumed the captcha token.
              if (result.status === 422) {
                captcha === null || captcha === void 0 || captcha.reset();
              }
              firstServerError = ERROR_ORDER.find(function (field) {
                return _this2.errors[field];
              });
              _this2.$nextTick(function () {
                return firstServerError ? _this2.revealError(firstServerError) : _this2.revealError('submit');
              });
            case 8:
              return _context2.a(2);
          }
        }, _callee2);
      }))();
    }
  });
}
document.addEventListener('alpine:init', function () {
  window.Alpine.data('tourCheckout', tourCheckout);
});
})();

/******/ })()
;