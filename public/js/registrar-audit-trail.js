/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 16);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/registrar-audit-trail.js":
/*!***********************************************!*\
  !*** ./resources/js/registrar-audit-trail.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
(function () {
  var root = document.getElementById('auditTrailRoot');
  if (!root) {
    return;
  }
  var dataEndpoint = root.getAttribute('data-data-endpoint') || '';
  var state = {
    currentPage: 1,
    lastPage: 1,
    perPage: 20,
    total: 0,
    from: 0,
    rows: []
  };
  var listRequestState = {
    controller: null,
    sequence: 0
  };
  var searchDebounceTimer = null;
  var els = {
    search: document.getElementById('auditSearchInput'),
    user: document.getElementById('auditUserFilter'),
    module: document.getElementById('auditModuleFilter'),
    dateFrom: document.getElementById('auditDateFrom'),
    dateTo: document.getElementById('auditDateTo'),
    searchBtn: document.getElementById('auditSearchBtn'),
    clearBtn: document.getElementById('auditClearBtn'),
    tableBody: document.getElementById('auditTrailBody'),
    pager: document.getElementById('auditTrailPager')
  };
  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (ch) {
      var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      };
      return map[ch] || ch;
    });
  }
  function getFilters() {
    return {
      search: (els.search ? els.search.value : '').trim(),
      user_id: (els.user ? els.user.value : '').trim(),
      module: (els.module ? els.module.value : '').trim(),
      date_from: (els.dateFrom ? els.dateFrom.value : '').trim(),
      date_to: (els.dateTo ? els.dateTo.value : '').trim()
    };
  }
  function buildDataUrl(page) {
    var url = new URL(dataEndpoint, window.location.origin);
    var filters = getFilters();
    Object.keys(filters).forEach(function (key) {
      if (filters[key]) {
        url.searchParams.set(key, filters[key]);
      }
    });
    url.searchParams.set('page', String(page || 1));
    url.searchParams.set('per_page', String(state.perPage));
    return url.toString();
  }
  function renderLoading() {
    if (!els.tableBody) {
      return;
    }
    els.tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading audit logs...</td></tr>';
  }
  function renderTable() {
    if (!els.tableBody) {
      return;
    }
    if (!state.rows.length) {
      els.tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No audit logs found.</td></tr>';
      return;
    }
    var html = state.rows.map(function (log) {
      return '' + '<tr>' + '<td><span class="text-muted">' + escapeHtml(log.timestamp) + '</span></td>' + '<td><strong>' + escapeHtml(log.user) + '</strong></td>' + '<td><span class="badge badge-soft-info">' + escapeHtml(log.module) + '</span></td>' + '<td>' + escapeHtml(log.action) + '</td>' + '<td><small class="text-muted">' + escapeHtml(log.details) + '</small></td>' + '</tr>';
    }).join('');
    els.tableBody.innerHTML = html;
  }
  function renderPager() {
    if (!els.pager) {
      return;
    }
    if (state.lastPage <= 1) {
      els.pager.innerHTML = '';
      return;
    }
    var start = Math.max(1, state.currentPage - 2);
    var end = Math.min(state.lastPage, state.currentPage + 2);
    if (state.currentPage <= 3) {
      end = Math.min(state.lastPage, 5);
    } else if (state.currentPage >= state.lastPage - 2) {
      start = Math.max(1, state.lastPage - 4);
    }
    var pageButtons = '';
    for (var p = start; p <= end; p += 1) {
      pageButtons += '<button type="button" class="rtp-page-num ' + (p === state.currentPage ? 'active' : '') + '" data-audit-page="' + p + '">' + p + '</button>';
    }
    els.pager.innerHTML = '' + '<div class="rtp-pagination">' + '<nav class="rtp-nav" aria-label="Audit Trail pagination">' + '<div class="rtp-list" role="group" aria-label="Page controls">' + '<button type="button" class="rtp-page-btn" data-audit-page-prev="1" ' + (state.currentPage <= 1 ? 'disabled' : '') + '>&lt;</button>' + '<div class="rtp-pages">' + pageButtons + '</div>' + '<button type="button" class="rtp-page-btn" data-audit-page-next="1" ' + (state.currentPage >= state.lastPage ? 'disabled' : '') + '>&gt;</button>' + '</div>' + '</nav>' + '</div>';
  }
  function fetchRows(_x) {
    return _fetchRows.apply(this, arguments);
  }
  function _fetchRows() {
    _fetchRows = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(page) {
      var requestSequence, response, json, meta, _t, _t2;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.p = _context.n) {
          case 0:
            if (dataEndpoint) {
              _context.n = 1;
              break;
            }
            return _context.a(2);
          case 1:
            if (listRequestState.controller) {
              listRequestState.controller.abort();
            }
            listRequestState.controller = new AbortController();
            listRequestState.sequence += 1;
            requestSequence = listRequestState.sequence;
            renderLoading();
            _context.p = 2;
            _context.n = 3;
            return fetch(buildDataUrl(page), {
              method: 'GET',
              headers: {
                'Accept': 'application/json'
              },
              signal: listRequestState.controller.signal
            });
          case 3:
            response = _context.v;
            json = {};
            _context.p = 4;
            _context.n = 5;
            return response.json();
          case 5:
            json = _context.v;
            _context.n = 7;
            break;
          case 6:
            _context.p = 6;
            _t = _context.v;
            json = {};
          case 7:
            if (!(requestSequence !== listRequestState.sequence)) {
              _context.n = 8;
              break;
            }
            return _context.a(2);
          case 8:
            if (!(!response.ok || json.ok === false)) {
              _context.n = 9;
              break;
            }
            throw new Error(json.message || 'Unable to load audit logs.');
          case 9:
            meta = json.meta || {};
            state.rows = json.rows || [];
            state.currentPage = parseInt(meta.currentPage, 10) || 1;
            state.lastPage = parseInt(meta.lastPage, 10) || 1;
            state.perPage = parseInt(meta.perPage, 10) || 20;
            state.total = parseInt(meta.total, 10) || 0;
            state.from = parseInt(meta.from, 10) || 0;
            renderTable();
            renderPager();
            _context.n = 13;
            break;
          case 10:
            _context.p = 10;
            _t2 = _context.v;
            if (!(_t2 && _t2.name === 'AbortError')) {
              _context.n = 11;
              break;
            }
            return _context.a(2);
          case 11:
            if (!(requestSequence !== listRequestState.sequence)) {
              _context.n = 12;
              break;
            }
            return _context.a(2);
          case 12:
            state.rows = [];
            renderTable();
            renderPager();
          case 13:
            _context.p = 13;
            if (requestSequence === listRequestState.sequence) {
              listRequestState.controller = null;
            }
            return _context.f(13);
          case 14:
            return _context.a(2);
        }
      }, _callee, null, [[4, 6], [2, 10, 13, 14]]);
    }));
    return _fetchRows.apply(this, arguments);
  }
  function wireEvents() {
    if (els.search) {
      els.search.addEventListener('input', function () {
        if (searchDebounceTimer) {
          clearTimeout(searchDebounceTimer);
        }
        searchDebounceTimer = setTimeout(function () {
          fetchRows(1);
        }, 300);
      });
      els.search.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
          event.preventDefault();
          if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
          }
          fetchRows(1);
        }
      });
    }
    [els.user, els.module, els.dateFrom, els.dateTo].forEach(function (el) {
      if (el) {
        el.addEventListener('change', function () {
          fetchRows(1);
        });
      }
    });
    if (els.searchBtn) {
      els.searchBtn.addEventListener('click', function () {
        fetchRows(1);
      });
    }
    if (els.clearBtn) {
      els.clearBtn.addEventListener('click', function () {
        if (els.search) els.search.value = '';
        if (els.user) els.user.value = '';
        if (els.module) els.module.value = '';
        if (els.dateFrom) els.dateFrom.value = '';
        if (els.dateTo) els.dateTo.value = '';
        fetchRows(1);
      });
    }
    if (els.pager) {
      els.pager.addEventListener('click', function (event) {
        var pageBtn = event.target.closest('[data-audit-page]');
        if (pageBtn) {
          fetchRows(parseInt(pageBtn.getAttribute('data-audit-page'), 10) || 1);
          return;
        }
        var prevBtn = event.target.closest('[data-audit-page-prev]');
        if (prevBtn && state.currentPage > 1) {
          fetchRows(state.currentPage - 1);
          return;
        }
        var nextBtn = event.target.closest('[data-audit-page-next]');
        if (nextBtn && state.currentPage < state.lastPage) {
          fetchRows(state.currentPage + 1);
        }
      });
    }
  }
  wireEvents();
  fetchRows(1);
})();

/***/ }),

/***/ 16:
/*!*****************************************************!*\
  !*** multi ./resources/js/registrar-audit-trail.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\xampp\htdocs\plp-demo\resources\js\registrar-audit-trail.js */"./resources/js/registrar-audit-trail.js");


/***/ })

/******/ });