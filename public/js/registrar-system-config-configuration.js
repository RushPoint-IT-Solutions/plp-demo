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
/******/ 	return __webpack_require__(__webpack_require__.s = 14);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/registrar-system-config-configuration.js":
/*!***************************************************************!*\
  !*** ./resources/js/registrar-system-config-configuration.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
(function () {
  'use strict';

  function byId(id) {
    return document.getElementById(id);
  }
  function decodeHtmlEntities(value) {
    if (typeof value !== 'string' || value.indexOf('&') === -1) {
      return value;
    }
    var parser = document.createElement('textarea');
    parser.innerHTML = value;
    return parser.value;
  }
  function parseDataJson(element, key, fallback) {
    if (!element) {
      return fallback;
    }
    var attrName = 'data-' + key.replace(/([A-Z])/g, '-$1').toLowerCase();
    var raw = element.getAttribute(attrName);
    if (!raw) {
      return fallback;
    }
    var normalized = decodeHtmlEntities(raw);
    try {
      return JSON.parse(normalized);
    } catch (error) {
      return fallback;
    }
  }
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
  function formatDate(value) {
    if (!value) {
      return '';
    }
    var date = new Date(value + 'T00:00:00');
    if (isNaN(date.getTime())) {
      return '';
    }
    return date.toLocaleDateString('en-US', {
      month: '2-digit',
      day: '2-digit',
      year: '2-digit'
    });
  }
  function normalizeDateInputValue(value) {
    if (!value) {
      return '';
    }
    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
      return value;
    }
    var parsed = new Date(value);
    if (isNaN(parsed.getTime())) {
      return '';
    }
    var month = String(parsed.getMonth() + 1).padStart(2, '0');
    var day = String(parsed.getDate()).padStart(2, '0');
    return parsed.getFullYear() + '-' + month + '-' + day;
  }
  function routeFromTemplate(template, id) {
    return String(template || '').replace('__ID__', String(id));
  }
  function csrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') || '' : '';
  }
  function resolveErrorMessage(payload, fallback) {
    if (!payload) {
      return fallback;
    }
    if (payload.errors) {
      var fields = Object.keys(payload.errors);
      if (fields.length && payload.errors[fields[0]] && payload.errors[fields[0]][0]) {
        return payload.errors[fields[0]][0];
      }
    }
    if (payload.message) {
      return payload.message;
    }
    return fallback;
  }
  function showMessage(message, type) {
    var text = String(message || '').trim();
    if (!text) {
      return;
    }
    if (typeof window.showRegistrarToast === 'function') {
      try {
        window.showRegistrarToast(text, type || 'success');
        return;
      } catch (error) {
        console.error('Registrar toast render failed:', error);
      }
    }
    window.alert(text);
  }
  function requestJson(_x, _x2, _x3) {
    return _requestJson.apply(this, arguments);
  }
  function _requestJson() {
    _requestJson = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(url, method, payload) {
      var response, data, _t;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.p = _context.n) {
          case 0:
            _context.n = 1;
            return fetch(url, {
              method: method,
              headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
              },
              body: payload ? JSON.stringify(payload) : null
            });
          case 1:
            response = _context.v;
            data = {};
            _context.p = 2;
            _context.n = 3;
            return response.json();
          case 3:
            data = _context.v;
            _context.n = 5;
            break;
          case 4:
            _context.p = 4;
            _t = _context.v;
            data = {};
          case 5:
            if (!(!response.ok || data.ok === false)) {
              _context.n = 6;
              break;
            }
            throw new Error(resolveErrorMessage(data, 'Unable to process request.'));
          case 6:
            return _context.a(2, data);
        }
      }, _callee, null, [[2, 4]]);
    }));
    return _requestJson.apply(this, arguments);
  }
  function requestForm(_x4, _x5, _x6) {
    return _requestForm.apply(this, arguments);
  }
  function _requestForm() {
    _requestForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(url, method, formData) {
      var transportMethod, body, response, data, _t2;
      return _regenerator().w(function (_context2) {
        while (1) switch (_context2.p = _context2.n) {
          case 0:
            transportMethod = method;
            body = formData;
            if (method !== 'POST') {
              transportMethod = 'POST';
              body.append('_method', method);
            }
            _context2.n = 1;
            return fetch(url, {
              method: transportMethod,
              headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
              },
              body: body
            });
          case 1:
            response = _context2.v;
            data = {};
            _context2.p = 2;
            _context2.n = 3;
            return response.json();
          case 3:
            data = _context2.v;
            _context2.n = 5;
            break;
          case 4:
            _context2.p = 4;
            _t2 = _context2.v;
            data = {};
          case 5:
            if (!(!response.ok || data.ok === false)) {
              _context2.n = 6;
              break;
            }
            throw new Error(resolveErrorMessage(data, 'Unable to process request.'));
          case 6:
            return _context2.a(2, data);
        }
      }, _callee2, null, [[2, 4]]);
    }));
    return _requestForm.apply(this, arguments);
  }
  function refreshListboxes(target) {
    if (!window.registrarListboxSelect || _typeof(window.registrarListboxSelect) !== 'object') {
      return;
    }
    if (target && typeof window.registrarListboxSelect.refresh === 'function') {
      window.registrarListboxSelect.refresh(target);
      return;
    }
    if (typeof window.registrarListboxSelect.refreshAll === 'function') {
      window.registrarListboxSelect.refreshAll();
      return;
    }
    if (typeof window.registrarListboxSelect.refresh === 'function') {
      window.registrarListboxSelect.refresh();
    }
  }
  function syncSelectUI(id) {
    var node = byId(id);
    if (!node) {
      return;
    }
    node.dispatchEvent(new Event('change', {
      bubbles: true
    }));
  }
  function setInlineMessage(id, text, isError) {
    var node = byId(id);
    if (!node) {
      return;
    }
    node.textContent = text || '';
    node.classList.toggle('is-error', !!isError);
  }
  var bootstrap = byId('cfgBootstrap');
  if (!bootstrap) {
    return;
  }
  var routes = parseDataJson(bootstrap, 'routes', {});
  var state = {
    schoolSem: parseDataJson(bootstrap, 'schoolSem', []),
    gradePosting: parseDataJson(bootstrap, 'gradePosting', []),
    signatures: parseDataJson(bootstrap, 'signatures', []),
    cutoffDate: parseDataJson(bootstrap, 'cutoffDate', []),
    sectionCutoff: parseDataJson(bootstrap, 'sectionCutoff', []),
    cutoffConfig: parseDataJson(bootstrap, 'cutoffConfig', []),
    curriculumDisplay: parseDataJson(bootstrap, 'curriculumDisplay', []),
    latestIncRun: parseDataJson(bootstrap, 'latestIncRun', null),
    pager: {
      schoolSem: {
        page: 1,
        size: 5
      },
      gradePosting: {
        page: 1,
        size: 5
      },
      signatures: {
        page: 1,
        size: 5
      },
      cutoffDate: {
        page: 1,
        size: 5
      },
      sectionCutoff: {
        page: 1,
        size: 5
      },
      cutoffConfig: {
        page: 1,
        size: 5
      },
      curriculumDisplay: {
        page: 1,
        size: 5
      }
    },
    deleteTarget: null
  };
  var requestLocks = {
    schoolSem: false
  };
  var pagerMeta = {
    schoolSem: {
      id: 'cfgSchoolSemPager'
    },
    gradePosting: {
      id: 'cfgGradePostingPager'
    },
    signatures: {
      id: 'cfgSignaturePager'
    },
    cutoffDate: {
      id: 'cfgCutoffDatePager'
    },
    sectionCutoff: {
      id: 'cfgSectionCutoffPager'
    },
    cutoffConfig: {
      id: 'cfgCutoffConfigPager'
    },
    curriculumDisplay: {
      id: 'cfgCurriculumDisplayPager'
    }
  };
  function listFor(group) {
    return state[group] || [];
  }
  function upsertRow(list, row) {
    var index = list.findIndex(function (item) {
      return String(item.id) === String(row.id);
    });
    if (index >= 0) {
      list[index] = row;
      return;
    }
    list.unshift(row);
  }
  function removeRow(list, id) {
    var index = list.findIndex(function (item) {
      return String(item.id) === String(id);
    });
    if (index >= 0) {
      list.splice(index, 1);
    }
  }
  function maxPage(group) {
    var data = listFor(group);
    var size = state.pager[group].size;
    return Math.max(1, Math.ceil(data.length / size));
  }
  function pagedSlice(group) {
    var data = listFor(group);
    var page = state.pager[group].page;
    var size = state.pager[group].size;
    var start = (page - 1) * size;
    return {
      start: start,
      rows: data.slice(start, start + size)
    };
  }
  function setPage(group, page) {
    var max = maxPage(group);
    state.pager[group].page = Math.max(1, Math.min(max, page));
    renderGroup(group);
  }
  function renderPager(group) {
    var mount = byId((pagerMeta[group] || {}).id || '');
    if (!mount) {
      return;
    }
    var list = listFor(group);
    var size = state.pager[group].size;
    if (list.length <= size) {
      mount.innerHTML = '';
      return;
    }
    var current = state.pager[group].page;
    var max = maxPage(group);
    var start = Math.max(1, current - 2);
    var end = Math.min(max, current + 2);
    if (current <= 3) {
      end = Math.min(max, 5);
    } else if (current >= max - 2) {
      start = Math.max(1, max - 4);
    }
    var html = '' + '<nav class="cfg-page-nav-wrap" aria-label="Configuration pagination">' + '<div class="cfg-page-list" role="group" aria-label="Page controls">' + '<button type="button" class="btn cfg-page-btn" data-cfg-action="set-page" data-cfg-group="' + group + '" data-cfg-page="' + (current - 1) + '" ' + (current <= 1 ? 'disabled' : '') + ' aria-label="Previous page">&lt;</button>';
    for (var p = start; p <= end; p += 1) {
      html += '<button type="button" class="btn cfg-page-num ' + (p === current ? 'active' : '') + '" data-cfg-action="set-page" data-cfg-group="' + group + '" data-cfg-page="' + p + '" ' + (p === current ? 'aria-current="page"' : '') + '>' + p + '</button>';
    }
    html += '' + '<button type="button" class="btn cfg-page-btn" data-cfg-action="set-page" data-cfg-group="' + group + '" data-cfg-page="' + (current + 1) + '" ' + (current >= max ? 'disabled' : '') + ' aria-label="Next page">&gt;</button>' + '</div>' + '</nav>';
    mount.innerHTML = html;
  }
  function actionMenuHtml(group, index) {
    var menuId = 'cfgMenu-' + group + '-' + index;
    return '' + '<div class="apst-action-btn" data-cfg-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' + '<div class="apst-dropdown" id="' + menuId + '">' + '<button type="button" data-cfg-action="edit-row" data-cfg-group="' + group + '" data-cfg-index="' + index + '">Edit</button>' + '<button type="button" class="apst-del-btn" data-cfg-action="delete-row" data-cfg-group="' + group + '" data-cfg-index="' + index + '">Delete</button>' + '</div>';
  }
  function renderSchoolSem() {
    var body = byId('cfgSchoolSemBody');
    if (!body) {
      return;
    }
    var paged = pagedSlice('schoolSem');
    var rows = paged.rows.map(function (row, idx) {
      var index = paged.start + idx;
      return '' + '<tr>' + '<td>' + escapeHtml(row.sy) + '</td>' + '<td>' + escapeHtml(row.semester) + '</td>' + '<td class="cfg-col-action">' + actionMenuHtml('schoolSem', index) + '</td>' + '</tr>';
    }).join('');
    body.innerHTML = rows || '<tr><td colspan="3" class="sc-empty-row">No records found.</td></tr>';
    renderPager('schoolSem');
  }
  function renderGradePosting() {
    var body = byId('cfgGradePostingBody');
    if (!body) {
      return;
    }
    var paged = pagedSlice('gradePosting');
    var rows = paged.rows.map(function (row, idx) {
      var index = paged.start + idx;
      return '' + '<tr>' + '<td>' + escapeHtml(row.sy) + '</td>' + '<td>' + escapeHtml(row.semester) + '</td>' + '<td>' + escapeHtml(row.period) + '</td>' + '<td>' + escapeHtml(formatDate(row.dateFrom)) + '</td>' + '<td class="cfg-col-action">' + actionMenuHtml('gradePosting', index) + '</td>' + '</tr>';
    }).join('');
    body.innerHTML = rows || '<tr><td colspan="5" class="sc-empty-row">No records found.</td></tr>';
    renderPager('gradePosting');
  }
  function renderSignatures() {
    var body = byId('cfgSignatureBody');
    if (!body) {
      return;
    }
    var paged = pagedSlice('signatures');
    var rows = paged.rows.map(function (row, idx) {
      var index = paged.start + idx;
      var signatureCell = row.signatureUrl ? '<a href="' + escapeHtml(row.signatureUrl) + '" target="_blank" rel="noopener">View</a>' : '<span class="cfg-muted">No file</span>';
      return '' + '<tr>' + '<td>' + escapeHtml(row.designation) + '</td>' + '<td>' + escapeHtml(row.name) + '</td>' + '<td>' + signatureCell + '</td>' + '<td class="cfg-col-action">' + actionMenuHtml('signatures', index) + '</td>' + '</tr>';
    }).join('');
    body.innerHTML = rows || '<tr><td colspan="4" class="sc-empty-row">No records found.</td></tr>';
    renderPager('signatures');
  }
  function renderCutoffDate() {
    var body = byId('cfgCutoffDateBody');
    if (!body) {
      return;
    }
    var paged = pagedSlice('cutoffDate');
    var rows = paged.rows.map(function (row, idx) {
      var index = paged.start + idx;
      return '' + '<tr>' + '<td>' + escapeHtml(row.type) + '</td>' + '<td>' + escapeHtml(row.sy) + '</td>' + '<td>' + escapeHtml(row.semester) + '</td>' + '<td>' + escapeHtml(formatDate(row.cutoffDate)) + '</td>' + '<td class="cfg-col-action">' + actionMenuHtml('cutoffDate', index) + '</td>' + '</tr>';
    }).join('');
    body.innerHTML = rows || '<tr><td colspan="5" class="sc-empty-row">No records found.</td></tr>';
    renderPager('cutoffDate');
  }
  function renderSectionCutoff() {
    var body = byId('cfgSectionCutoffBody');
    if (!body) {
      return;
    }
    var paged = pagedSlice('sectionCutoff');
    var rows = paged.rows.map(function (row, idx) {
      var index = paged.start + idx;
      return '' + '<tr>' + '<td>' + escapeHtml(row.sy) + '</td>' + '<td>' + escapeHtml(row.semester) + '</td>' + '<td>' + escapeHtml(formatDate(row.cutoffDate)) + '</td>' + '<td class="cfg-col-action">' + actionMenuHtml('sectionCutoff', index) + '</td>' + '</tr>';
    }).join('');
    body.innerHTML = rows || '<tr><td colspan="4" class="sc-empty-row">No records found.</td></tr>';
    renderPager('sectionCutoff');
  }
  function renderCutoffConfig() {
    var body = byId('cfgCutoffConfigBody');
    if (!body) {
      return;
    }
    var paged = pagedSlice('cutoffConfig');
    var rows = paged.rows.map(function (row, idx) {
      var index = paged.start + idx;
      return '' + '<tr>' + '<td>' + escapeHtml(row.sy) + '</td>' + '<td>' + escapeHtml(row.semester) + '</td>' + '<td>' + escapeHtml(formatDate(row.cutoffDate)) + '</td>' + '<td class="cfg-col-action">' + actionMenuHtml('cutoffConfig', index) + '</td>' + '</tr>';
    }).join('');
    body.innerHTML = rows || '<tr><td colspan="4" class="sc-empty-row">No records found.</td></tr>';
    renderPager('cutoffConfig');
  }
  function renderCurriculumDisplay() {
    var body = byId('cfgCurriculumDisplayBody');
    if (!body) {
      return;
    }
    var paged = pagedSlice('curriculumDisplay');
    var rows = paged.rows.map(function (row, idx) {
      var index = paged.start + idx;
      return '' + '<tr>' + '<td>' + escapeHtml(row.sy) + '</td>' + '<td>' + escapeHtml(row.semester) + '</td>' + '<td>' + escapeHtml(row.status) + '</td>' + '<td class="cfg-col-action">' + actionMenuHtml('curriculumDisplay', index) + '</td>' + '</tr>';
    }).join('');
    body.innerHTML = rows || '<tr><td colspan="4" class="sc-empty-row">No records found.</td></tr>';
    renderPager('curriculumDisplay');
  }
  function renderGroup(group) {
    if (group === 'schoolSem') {
      renderSchoolSem();
      return;
    }
    if (group === 'gradePosting') {
      renderGradePosting();
      return;
    }
    if (group === 'signatures') {
      renderSignatures();
      return;
    }
    if (group === 'cutoffDate') {
      renderCutoffDate();
      return;
    }
    if (group === 'sectionCutoff') {
      renderSectionCutoff();
      return;
    }
    if (group === 'cutoffConfig') {
      renderCutoffConfig();
      return;
    }
    if (group === 'curriculumDisplay') {
      renderCurriculumDisplay();
    }
  }
  function renderAll() {
    renderSchoolSem();
    renderGradePosting();
    renderSignatures();
    renderCutoffDate();
    renderSectionCutoff();
    renderCutoffConfig();
    renderCurriculumDisplay();
  }
  function closeActionMenus() {
    document.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
      menu.classList.remove('open', 'drop-up');
      menu.style.top = '';
      menu.style.left = '';
      menu.style.right = '';
      menu.style.bottom = '';
    });
  }
  function toggleActionMenu(menuId, trigger) {
    var menu = byId(menuId);
    if (!menu || !trigger) {
      return;
    }
    var wasOpen = menu.classList.contains('open');
    closeActionMenus();
    if (wasOpen) {
      return;
    }
    var rect = trigger.getBoundingClientRect();
    var width = 130;
    var height = 96;
    var left = rect.right + 8;
    var top = rect.top;
    if (left + width > window.innerWidth - 8) {
      left = Math.max(8, window.innerWidth - width - 8);
    }
    if (window.innerHeight - rect.bottom < height + 8) {
      menu.classList.add('drop-up');
      top = Math.max(8, rect.bottom - height);
    }
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
    menu.style.right = 'auto';
    menu.style.bottom = 'auto';
    menu.classList.add('open');
  }
  function openModal(id) {
    var modal = byId(id);
    if (!modal) {
      return;
    }
    closeActionMenus();
    modal.classList.remove('is-hidden');
    refreshListboxes(modal);
  }
  function closeModal(id) {
    var modal = byId(id);
    if (!modal) {
      return;
    }
    modal.classList.add('is-hidden');
  }
  function resetSchoolSemModal() {
    byId('cfgSSEditId').value = '';
    byId('cfgSSTitle').textContent = 'ADD SCHOOL YEAR AND SEMESTER';
    byId('cfgSSSaveBtn').textContent = 'Save';
    byId('cfgSSSaveBtn').disabled = false;
    byId('cfgSSYear').value = '';
    byId('cfgSSSemester').value = '';
    syncSelectUI('cfgSSSemester');
  }
  function resetGradePostingModal() {
    byId('cfgGPEditId').value = '';
    byId('cfgGPTitle').textContent = 'ADD GRADE POSTING';
    byId('cfgGPSaveBtn').textContent = 'Save';
    byId('cfgGPYear').value = '';
    byId('cfgGPSemester').value = '';
    byId('cfgGPPeriod').value = '';
    byId('cfgGPDateFrom').value = '';
    syncSelectUI('cfgGPSemester');
    syncSelectUI('cfgGPPeriod');
  }
  function resetSignatureForm() {
    byId('cfgSignatureEditId').value = '';
    byId('cfgSignatureDesignation').value = '';
    byId('cfgSignatureName').value = '';
    byId('cfgSignatureFile').value = '';
    byId('cfgSignatureSaveBtn').textContent = 'Save';
    syncSelectUI('cfgSignatureDesignation');
  }
  function resetCutoffDateForm() {
    byId('cfgCutoffDateEditId').value = '';
    byId('cfgCutoffType').value = '';
    byId('cfgCutoffDate').value = '';
    byId('cfgCutoffSaveBtn').textContent = 'Save';
    syncSelectUI('cfgCutoffType');
  }
  function resetSectionCutoffForm() {
    byId('cfgSectionCutoffEditId').value = '';
    byId('cfgSectionCutoffDate').value = '';
    byId('cfgSectionCutoffSaveBtn').textContent = 'Save';
  }
  function resetCutoffConfigForm() {
    byId('cfgCutoffConfigEditId').value = '';
    byId('cfgCutoffConfigDate').value = '';
    byId('cfgCutoffConfigSaveBtn').textContent = 'Update';
  }
  function resetCurriculumForm() {
    byId('cfgCurriculumEditId').value = '';
    byId('cfgCurriculumStatus').value = '';
    byId('cfgCurriculumSaveBtn').textContent = 'Submit';
    syncSelectUI('cfgCurriculumStatus');
  }
  function saveSchoolSem() {
    return _saveSchoolSem.apply(this, arguments);
  }
  function _saveSchoolSem() {
    _saveSchoolSem = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3() {
      var schoolYear, semester, editId, saveButton, payload, response, _t3;
      return _regenerator().w(function (_context3) {
        while (1) switch (_context3.p = _context3.n) {
          case 0:
            if (!requestLocks.schoolSem) {
              _context3.n = 1;
              break;
            }
            return _context3.a(2);
          case 1:
            schoolYear = (byId('cfgSSYear').value || '').trim();
            semester = byId('cfgSSSemester').value;
            editId = byId('cfgSSEditId').value;
            saveButton = byId('cfgSSSaveBtn');
            if (!(!schoolYear || !semester)) {
              _context3.n = 2;
              break;
            }
            showMessage('Please complete School Year and Semester.', 'error');
            return _context3.a(2);
          case 2:
            payload = {
              school_year: schoolYear,
              semester: semester
            };
            requestLocks.schoolSem = true;
            if (saveButton) {
              saveButton.disabled = true;
            }
            _context3.p = 3;
            if (!editId) {
              _context3.n = 5;
              break;
            }
            _context3.n = 4;
            return requestJson(routeFromTemplate(routes.schoolSemUpdateTemplate, editId), 'PUT', payload);
          case 4:
            response = _context3.v;
            _context3.n = 7;
            break;
          case 5:
            _context3.n = 6;
            return requestJson(routes.schoolSemStore, 'POST', payload);
          case 6:
            response = _context3.v;
          case 7:
            if (response.row) {
              upsertRow(state.schoolSem, response.row);
            }
            state.pager.schoolSem.page = 1;
            renderSchoolSem();
            closeModal('cfgSchoolSemModal');
            resetSchoolSemModal();
            showMessage('School year and semester saved.', 'success');
            _context3.n = 9;
            break;
          case 8:
            _context3.p = 8;
            _t3 = _context3.v;
            showMessage(_t3.message || 'Unable to save school year and semester.', 'error');
          case 9:
            _context3.p = 9;
            requestLocks.schoolSem = false;
            if (saveButton) {
              saveButton.disabled = false;
            }
            return _context3.f(9);
          case 10:
            return _context3.a(2);
        }
      }, _callee3, null, [[3, 8, 9, 10]]);
    }));
    return _saveSchoolSem.apply(this, arguments);
  }
  function saveGradePosting() {
    return _saveGradePosting.apply(this, arguments);
  }
  function _saveGradePosting() {
    _saveGradePosting = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4() {
      var schoolYear, semester, period, dateFrom, editId, payload, response, _t4;
      return _regenerator().w(function (_context4) {
        while (1) switch (_context4.p = _context4.n) {
          case 0:
            schoolYear = (byId('cfgGPYear').value || '').trim();
            semester = byId('cfgGPSemester').value;
            period = byId('cfgGPPeriod').value;
            dateFrom = byId('cfgGPDateFrom').value;
            editId = byId('cfgGPEditId').value;
            if (!(!schoolYear || !semester || !period || !dateFrom)) {
              _context4.n = 1;
              break;
            }
            showMessage('Please complete all Grade Posting fields.', 'error');
            return _context4.a(2);
          case 1:
            payload = {
              school_year: schoolYear,
              semester: semester,
              period: period,
              date_from: dateFrom
            };
            _context4.p = 2;
            if (!editId) {
              _context4.n = 4;
              break;
            }
            _context4.n = 3;
            return requestJson(routeFromTemplate(routes.gradePostingUpdateTemplate, editId), 'PUT', payload);
          case 3:
            response = _context4.v;
            _context4.n = 6;
            break;
          case 4:
            _context4.n = 5;
            return requestJson(routes.gradePostingStore, 'POST', payload);
          case 5:
            response = _context4.v;
          case 6:
            if (response.row) {
              upsertRow(state.gradePosting, response.row);
            }
            state.pager.gradePosting.page = 1;
            renderGradePosting();
            closeModal('cfgGradePostingModal');
            resetGradePostingModal();
            showMessage('Grade posting saved.', 'success');
            _context4.n = 8;
            break;
          case 7:
            _context4.p = 7;
            _t4 = _context4.v;
            showMessage(_t4.message || 'Unable to save grade posting.', 'error');
          case 8:
            return _context4.a(2);
        }
      }, _callee4, null, [[2, 7]]);
    }));
    return _saveGradePosting.apply(this, arguments);
  }
  function submitSignatureForm(_x7) {
    return _submitSignatureForm.apply(this, arguments);
  }
  function _submitSignatureForm() {
    _submitSignatureForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5(event) {
      var designationId, signerName, fileInput, editId, formData, response, _t5;
      return _regenerator().w(function (_context5) {
        while (1) switch (_context5.p = _context5.n) {
          case 0:
            event.preventDefault();
            designationId = byId('cfgSignatureDesignation').value;
            signerName = (byId('cfgSignatureName').value || '').trim();
            fileInput = byId('cfgSignatureFile');
            editId = byId('cfgSignatureEditId').value;
            if (!(!designationId || !signerName)) {
              _context5.n = 1;
              break;
            }
            showMessage('Please complete designation and name for signature.', 'error');
            return _context5.a(2);
          case 1:
            formData = new FormData();
            formData.append('designation_id', designationId);
            formData.append('signer_name', signerName);
            if (fileInput.files && fileInput.files[0]) {
              formData.append('signature_file', fileInput.files[0]);
            }
            _context5.p = 2;
            if (!editId) {
              _context5.n = 4;
              break;
            }
            _context5.n = 3;
            return requestForm(routeFromTemplate(routes.signatureUpdateTemplate, editId), 'PUT', formData);
          case 3:
            response = _context5.v;
            _context5.n = 6;
            break;
          case 4:
            _context5.n = 5;
            return requestForm(routes.signatureStore, 'POST', formData);
          case 5:
            response = _context5.v;
          case 6:
            if (response.row) {
              upsertRow(state.signatures, response.row);
            }
            state.pager.signatures.page = 1;
            renderSignatures();
            resetSignatureForm();
            showMessage('Signature configuration saved.', 'success');
            _context5.n = 8;
            break;
          case 7:
            _context5.p = 7;
            _t5 = _context5.v;
            showMessage(_t5.message || 'Unable to save signature configuration.', 'error');
          case 8:
            return _context5.a(2);
        }
      }, _callee5, null, [[2, 7]]);
    }));
    return _submitSignatureForm.apply(this, arguments);
  }
  function submitCutoffDateForm(_x8) {
    return _submitCutoffDateForm.apply(this, arguments);
  }
  function _submitCutoffDateForm() {
    _submitCutoffDateForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee6(event) {
      var typeCode, schoolYear, semester, cutoffDate, editId, payload, response, _t6;
      return _regenerator().w(function (_context6) {
        while (1) switch (_context6.p = _context6.n) {
          case 0:
            event.preventDefault();
            typeCode = byId('cfgCutoffType').value;
            schoolYear = (byId('cfgCutoffSy').value || '').trim();
            semester = byId('cfgCutoffSemester').value;
            cutoffDate = byId('cfgCutoffDate').value;
            editId = byId('cfgCutoffDateEditId').value;
            if (!(!typeCode || !schoolYear || !semester || !cutoffDate)) {
              _context6.n = 1;
              break;
            }
            showMessage('Please complete all Cut Off Date fields.', 'error');
            return _context6.a(2);
          case 1:
            payload = {
              type_code: typeCode,
              school_year: schoolYear,
              semester: semester,
              cutoff_date: cutoffDate
            };
            _context6.p = 2;
            if (!editId) {
              _context6.n = 4;
              break;
            }
            _context6.n = 3;
            return requestJson(routeFromTemplate(routes.cutoffUpdateTemplate, editId), 'PUT', payload);
          case 3:
            response = _context6.v;
            _context6.n = 6;
            break;
          case 4:
            _context6.n = 5;
            return requestJson(routes.cutoffStore, 'POST', payload);
          case 5:
            response = _context6.v;
          case 6:
            if (response.row) {
              upsertRow(state.cutoffDate, response.row);
            }
            state.pager.cutoffDate.page = 1;
            renderCutoffDate();
            resetCutoffDateForm();
            showMessage('Cut-off date saved.', 'success');
            _context6.n = 8;
            break;
          case 7:
            _context6.p = 7;
            _t6 = _context6.v;
            showMessage(_t6.message || 'Unable to save cut-off date.', 'error');
          case 8:
            return _context6.a(2);
        }
      }, _callee6, null, [[2, 7]]);
    }));
    return _submitCutoffDateForm.apply(this, arguments);
  }
  function submitSectionCutoffForm(_x9) {
    return _submitSectionCutoffForm.apply(this, arguments);
  }
  function _submitSectionCutoffForm() {
    _submitSectionCutoffForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee7(event) {
      var schoolYear, semester, cutoffDate, editId, payload, response, _t7;
      return _regenerator().w(function (_context7) {
        while (1) switch (_context7.p = _context7.n) {
          case 0:
            event.preventDefault();
            schoolYear = (byId('cfgSectionCutoffSy').value || '').trim();
            semester = byId('cfgSectionCutoffSemester').value;
            cutoffDate = byId('cfgSectionCutoffDate').value;
            editId = byId('cfgSectionCutoffEditId').value;
            if (!(!schoolYear || !semester || !cutoffDate)) {
              _context7.n = 1;
              break;
            }
            showMessage('Please complete all Section Offering Cut Off fields.', 'error');
            return _context7.a(2);
          case 1:
            payload = {
              type_code: 'SECTION_OFFERING',
              school_year: schoolYear,
              semester: semester,
              cutoff_date: cutoffDate
            };
            _context7.p = 2;
            if (!editId) {
              _context7.n = 4;
              break;
            }
            _context7.n = 3;
            return requestJson(routeFromTemplate(routes.cutoffUpdateTemplate, editId), 'PUT', payload);
          case 3:
            response = _context7.v;
            _context7.n = 6;
            break;
          case 4:
            _context7.n = 5;
            return requestJson(routes.cutoffStore, 'POST', payload);
          case 5:
            response = _context7.v;
          case 6:
            if (response.row) {
              upsertRow(state.sectionCutoff, response.row);
            }
            state.pager.sectionCutoff.page = 1;
            renderSectionCutoff();
            resetSectionCutoffForm();
            showMessage('Section offering cut-off saved.', 'success');
            _context7.n = 8;
            break;
          case 7:
            _context7.p = 7;
            _t7 = _context7.v;
            showMessage(_t7.message || 'Unable to save section offering cut-off.', 'error');
          case 8:
            return _context7.a(2);
        }
      }, _callee7, null, [[2, 7]]);
    }));
    return _submitSectionCutoffForm.apply(this, arguments);
  }
  function submitCutoffConfigForm(_x0) {
    return _submitCutoffConfigForm.apply(this, arguments);
  }
  function _submitCutoffConfigForm() {
    _submitCutoffConfigForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee8(event) {
      var schoolYear, semester, cutoffDate, editId, payload, response, _t8;
      return _regenerator().w(function (_context8) {
        while (1) switch (_context8.p = _context8.n) {
          case 0:
            event.preventDefault();
            schoolYear = (byId('cfgCutoffConfigSy').value || '').trim();
            semester = byId('cfgCutoffConfigSemester').value;
            cutoffDate = byId('cfgCutoffConfigDate').value;
            editId = byId('cfgCutoffConfigEditId').value;
            if (!(!schoolYear || !semester || !cutoffDate)) {
              _context8.n = 1;
              break;
            }
            showMessage('Please complete all Changing/Deleting/Adding Cut-off fields.', 'error');
            return _context8.a(2);
          case 1:
            payload = {
              type_code: 'CHANGING_DELETING_ADDING',
              school_year: schoolYear,
              semester: semester,
              cutoff_date: cutoffDate
            };
            _context8.p = 2;
            if (!editId) {
              _context8.n = 4;
              break;
            }
            _context8.n = 3;
            return requestJson(routeFromTemplate(routes.cutoffUpdateTemplate, editId), 'PUT', payload);
          case 3:
            response = _context8.v;
            _context8.n = 6;
            break;
          case 4:
            _context8.n = 5;
            return requestJson(routes.cutoffStore, 'POST', payload);
          case 5:
            response = _context8.v;
          case 6:
            if (response.row) {
              upsertRow(state.cutoffConfig, response.row);
            }
            state.pager.cutoffConfig.page = 1;
            renderCutoffConfig();
            resetCutoffConfigForm();
            showMessage('Cut-off configuration saved.', 'success');
            _context8.n = 8;
            break;
          case 7:
            _context8.p = 7;
            _t8 = _context8.v;
            showMessage(_t8.message || 'Unable to save cut-off configuration.', 'error');
          case 8:
            return _context8.a(2);
        }
      }, _callee8, null, [[2, 7]]);
    }));
    return _submitCutoffConfigForm.apply(this, arguments);
  }
  function submitCutoffRegistrationForm(_x1) {
    return _submitCutoffRegistrationForm.apply(this, arguments);
  }
  function _submitCutoffRegistrationForm() {
    _submitCutoffRegistrationForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee9(event) {
      var schoolYear, semester, eventDate, cutoffDate, studentNo, payload, _t9;
      return _regenerator().w(function (_context9) {
        while (1) switch (_context9.p = _context9.n) {
          case 0:
            event.preventDefault();
            schoolYear = (byId('cfgCutoffRegSy').value || '').trim();
            semester = byId('cfgCutoffRegSemester').value;
            eventDate = byId('cfgCutoffRegDate').value;
            cutoffDate = byId('cfgCutoffRegCutoffDate').value;
            studentNo = (byId('cfgCutoffRegStudentNo').value || '').trim();
            if (!(!schoolYear || !semester || !eventDate || !cutoffDate || !studentNo)) {
              _context9.n = 1;
              break;
            }
            setInlineMessage('cfgCutoffRegMessage', 'Please complete all Cut-off Registration fields.', true);
            return _context9.a(2);
          case 1:
            payload = {
              type_code: 'CUT_OFF_REGISTRATION',
              school_year: schoolYear,
              semester: semester,
              event_date: eventDate,
              cutoff_date: cutoffDate,
              student_no: studentNo
            };
            _context9.p = 2;
            _context9.n = 3;
            return requestJson(routes.cutoffStore, 'POST', payload);
          case 3:
            setInlineMessage('cfgCutoffRegMessage', 'Cut-off registration saved for student ' + studentNo + '.', false);
            byId('cfgCutoffRegDate').value = '';
            byId('cfgCutoffRegCutoffDate').value = '';
            byId('cfgCutoffRegStudentNo').value = '';
            _context9.n = 5;
            break;
          case 4:
            _context9.p = 4;
            _t9 = _context9.v;
            setInlineMessage('cfgCutoffRegMessage', _t9.message || 'Unable to save cut-off registration.', true);
          case 5:
            return _context9.a(2);
        }
      }, _callee9, null, [[2, 4]]);
    }));
    return _submitCutoffRegistrationForm.apply(this, arguments);
  }
  function submitCurriculumForm(_x10) {
    return _submitCurriculumForm.apply(this, arguments);
  }
  function _submitCurriculumForm() {
    _submitCurriculumForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee0(event) {
      var schoolYear, semester, status, editId, payload, response, _t0;
      return _regenerator().w(function (_context0) {
        while (1) switch (_context0.p = _context0.n) {
          case 0:
            event.preventDefault();
            schoolYear = (byId('cfgCurriculumSy').value || '').trim();
            semester = byId('cfgCurriculumSemester').value;
            status = byId('cfgCurriculumStatus').value;
            editId = byId('cfgCurriculumEditId').value;
            if (!(!schoolYear || !semester || !status)) {
              _context0.n = 1;
              break;
            }
            showMessage('Please complete all Curriculum Evaluation Display fields.', 'error');
            return _context0.a(2);
          case 1:
            payload = {
              school_year: schoolYear,
              semester: semester,
              display_status: status
            };
            _context0.p = 2;
            if (!editId) {
              _context0.n = 4;
              break;
            }
            _context0.n = 3;
            return requestJson(routeFromTemplate(routes.curriculumDisplayUpdateTemplate, editId), 'PUT', payload);
          case 3:
            response = _context0.v;
            _context0.n = 6;
            break;
          case 4:
            _context0.n = 5;
            return requestJson(routes.curriculumDisplayStore, 'POST', payload);
          case 5:
            response = _context0.v;
          case 6:
            if (response.row) {
              upsertRow(state.curriculumDisplay, response.row);
            }
            state.pager.curriculumDisplay.page = 1;
            renderCurriculumDisplay();
            resetCurriculumForm();
            showMessage('Curriculum display saved.', 'success');
            _context0.n = 8;
            break;
          case 7:
            _context0.p = 7;
            _t0 = _context0.v;
            showMessage(_t0.message || 'Unable to save curriculum display.', 'error');
          case 8:
            return _context0.a(2);
        }
      }, _callee0, null, [[2, 7]]);
    }));
    return _submitCurriculumForm.apply(this, arguments);
  }
  function submitReportDetailsForm(_x11) {
    return _submitReportDetailsForm.apply(this, arguments);
  }
  function _submitReportDetailsForm() {
    _submitReportDetailsForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee1(event) {
      var payload, _t1;
      return _regenerator().w(function (_context1) {
        while (1) switch (_context1.p = _context1.n) {
          case 0:
            event.preventDefault();
            payload = {
              region: (byId('cfgReportRegion').value || '').trim(),
              division: (byId('cfgReportDivision').value || '').trim(),
              school_id: (byId('cfgReportSchoolId').value || '').trim(),
              school_name: (byId('cfgReportSchoolName').value || '').trim(),
              contact_details: (byId('cfgReportContactDetails').value || '').trim()
            };
            if (!(!payload.region || !payload.division || !payload.school_id || !payload.school_name || !payload.contact_details)) {
              _context1.n = 1;
              break;
            }
            showMessage('Please complete all Report Details fields.', 'error');
            return _context1.a(2);
          case 1:
            _context1.p = 1;
            _context1.n = 2;
            return requestJson(routes.reportDetailsSave, 'POST', payload);
          case 2:
            showMessage('Report details saved.', 'success');
            _context1.n = 4;
            break;
          case 3:
            _context1.p = 3;
            _t1 = _context1.v;
            showMessage(_t1.message || 'Unable to save report details.', 'error');
          case 4:
            return _context1.a(2);
        }
      }, _callee1, null, [[1, 3]]);
    }));
    return _submitReportDetailsForm.apply(this, arguments);
  }
  function submitEmailSenderForm(_x12) {
    return _submitEmailSenderForm.apply(this, arguments);
  }
  function _submitEmailSenderForm() {
    _submitEmailSenderForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee10(event) {
      var payload, response, _t10;
      return _regenerator().w(function (_context10) {
        while (1) switch (_context10.p = _context10.n) {
          case 0:
            event.preventDefault();
            payload = {
              email: (byId('cfgEmailSenderAddress').value || '').trim(),
              password: (byId('cfgEmailSenderPassword').value || '').trim()
            };
            if (payload.email) {
              _context10.n = 1;
              break;
            }
            showMessage('Please provide an email sender address.', 'error');
            return _context10.a(2);
          case 1:
            _context10.p = 1;
            _context10.n = 2;
            return requestJson(routes.emailSenderSave, 'POST', payload);
          case 2:
            response = _context10.v;
            if (response.row && response.row.email) {
              byId('cfgEmailSenderAddress').value = response.row.email;
            }
            byId('cfgEmailSenderPassword').value = '';
            showMessage('Email sender saved.', 'success');
            _context10.n = 4;
            break;
          case 3:
            _context10.p = 3;
            _t10 = _context10.v;
            showMessage(_t10.message || 'Unable to save email sender.', 'error');
          case 4:
            return _context10.a(2);
        }
      }, _callee10, null, [[1, 3]]);
    }));
    return _submitEmailSenderForm.apply(this, arguments);
  }
  function submitOverdueIncForm(_x13) {
    return _submitOverdueIncForm.apply(this, arguments);
  }
  function _submitOverdueIncForm() {
    _submitOverdueIncForm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee11(event) {
      var payload, response, message, _t11;
      return _regenerator().w(function (_context11) {
        while (1) switch (_context11.p = _context11.n) {
          case 0:
            event.preventDefault();
            payload = {
              school_year: (byId('cfgIncSy').value || '').trim(),
              semester: byId('cfgIncSemester').value
            };
            if (!(!payload.school_year || !payload.semester)) {
              _context11.n = 1;
              break;
            }
            setInlineMessage('cfgIncProcessMessage', 'Please provide School Year and Semester to process.', true);
            return _context11.a(2);
          case 1:
            _context11.p = 1;
            _context11.n = 2;
            return requestJson(routes.overdueIncProcess, 'POST', payload);
          case 2:
            response = _context11.v;
            message = response && response.message ? response.message : 'Processed ' + (response.processedCount || 0) + ' INC record(s).';
            setInlineMessage('cfgIncProcessMessage', message, false);
            if (response && response.run) {
              state.latestIncRun = response.run;
            }
            _context11.n = 4;
            break;
          case 3:
            _context11.p = 3;
            _t11 = _context11.v;
            setInlineMessage('cfgIncProcessMessage', _t11.message || 'Unable to process overdue INC records.', true);
          case 4:
            return _context11.a(2);
        }
      }, _callee11, null, [[1, 3]]);
    }));
    return _submitOverdueIncForm.apply(this, arguments);
  }
  function openDeleteModal(group, index) {
    var row = listFor(group)[index];
    if (!row) {
      return;
    }
    state.deleteTarget = {
      group: group,
      id: row.id
    };
    byId('cfgDeleteMessage').textContent = 'Are you sure you want to delete this record?';
    openModal('cfgDeleteModal');
  }
  function confirmDelete() {
    return _confirmDelete.apply(this, arguments);
  }
  function _confirmDelete() {
    _confirmDelete = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee12() {
      var group, id, endpoint, _t12;
      return _regenerator().w(function (_context12) {
        while (1) switch (_context12.p = _context12.n) {
          case 0:
            if (!(!state.deleteTarget || !state.deleteTarget.id)) {
              _context12.n = 1;
              break;
            }
            closeModal('cfgDeleteModal');
            return _context12.a(2);
          case 1:
            group = state.deleteTarget.group;
            id = state.deleteTarget.id;
            endpoint = '';
            if (group === 'schoolSem') {
              endpoint = routeFromTemplate(routes.schoolSemDeleteTemplate, id);
            } else if (group === 'gradePosting') {
              endpoint = routeFromTemplate(routes.gradePostingDeleteTemplate, id);
            } else if (group === 'signatures') {
              endpoint = routeFromTemplate(routes.signatureDeleteTemplate, id);
            } else if (group === 'cutoffDate' || group === 'sectionCutoff' || group === 'cutoffConfig') {
              endpoint = routeFromTemplate(routes.cutoffDeleteTemplate, id);
            } else if (group === 'curriculumDisplay') {
              endpoint = routeFromTemplate(routes.curriculumDisplayDeleteTemplate, id);
            }
            if (endpoint) {
              _context12.n = 2;
              break;
            }
            closeModal('cfgDeleteModal');
            return _context12.a(2);
          case 2:
            _context12.p = 2;
            _context12.n = 3;
            return requestJson(endpoint, 'DELETE');
          case 3:
            if (group === 'cutoffDate' || group === 'sectionCutoff' || group === 'cutoffConfig') {
              removeRow(state.cutoffDate, id);
              removeRow(state.sectionCutoff, id);
              removeRow(state.cutoffConfig, id);
              renderCutoffDate();
              renderSectionCutoff();
              renderCutoffConfig();
            } else {
              removeRow(listFor(group), id);
              renderGroup(group);
            }
            state.deleteTarget = null;
            closeModal('cfgDeleteModal');
            showMessage('Record deleted.', 'success');
            _context12.n = 5;
            break;
          case 4:
            _context12.p = 4;
            _t12 = _context12.v;
            showMessage(_t12.message || 'Unable to delete record.', 'error');
          case 5:
            return _context12.a(2);
        }
      }, _callee12, null, [[2, 4]]);
    }));
    return _confirmDelete.apply(this, arguments);
  }
  function editRow(group, index) {
    var row = listFor(group)[index];
    if (!row) {
      return;
    }
    if (group === 'schoolSem') {
      byId('cfgSSEditId').value = row.id || '';
      byId('cfgSSTitle').textContent = 'EDIT SCHOOL YEAR AND SEMESTER';
      byId('cfgSSSaveBtn').textContent = 'Update';
      byId('cfgSSYear').value = row.sy || '';
      byId('cfgSSSemester').value = row.semester || '';
      syncSelectUI('cfgSSSemester');
      openModal('cfgSchoolSemModal');
      return;
    }
    if (group === 'gradePosting') {
      byId('cfgGPEditId').value = row.id || '';
      byId('cfgGPTitle').textContent = 'EDIT GRADE POSTING';
      byId('cfgGPSaveBtn').textContent = 'Update';
      byId('cfgGPYear').value = row.sy || '';
      byId('cfgGPSemester').value = row.semester || '';
      byId('cfgGPPeriod').value = row.period || '';
      byId('cfgGPDateFrom').value = normalizeDateInputValue(row.dateFrom);
      syncSelectUI('cfgGPSemester');
      syncSelectUI('cfgGPPeriod');
      openModal('cfgGradePostingModal');
      return;
    }
    if (group === 'signatures') {
      byId('cfgSignatureEditId').value = row.id || '';
      byId('cfgSignatureDesignation').value = row.designationId || '';
      byId('cfgSignatureName').value = row.name || '';
      byId('cfgSignatureFile').value = '';
      byId('cfgSignatureSaveBtn').textContent = 'Update';
      syncSelectUI('cfgSignatureDesignation');
      return;
    }
    if (group === 'cutoffDate') {
      byId('cfgCutoffDateEditId').value = row.id || '';
      byId('cfgCutoffType').value = row.typeCode || '';
      byId('cfgCutoffSy').value = row.sy || '';
      byId('cfgCutoffSemester').value = row.semester || '';
      byId('cfgCutoffDate').value = normalizeDateInputValue(row.cutoffDate);
      byId('cfgCutoffSaveBtn').textContent = 'Update';
      syncSelectUI('cfgCutoffType');
      syncSelectUI('cfgCutoffSemester');
      return;
    }
    if (group === 'sectionCutoff') {
      byId('cfgSectionCutoffEditId').value = row.id || '';
      byId('cfgSectionCutoffSy').value = row.sy || '';
      byId('cfgSectionCutoffSemester').value = row.semester || '';
      byId('cfgSectionCutoffDate').value = normalizeDateInputValue(row.cutoffDate);
      byId('cfgSectionCutoffSaveBtn').textContent = 'Update';
      syncSelectUI('cfgSectionCutoffSemester');
      return;
    }
    if (group === 'cutoffConfig') {
      byId('cfgCutoffConfigEditId').value = row.id || '';
      byId('cfgCutoffConfigSy').value = row.sy || '';
      byId('cfgCutoffConfigSemester').value = row.semester || '';
      byId('cfgCutoffConfigDate').value = normalizeDateInputValue(row.cutoffDate);
      byId('cfgCutoffConfigSaveBtn').textContent = 'Update';
      syncSelectUI('cfgCutoffConfigSemester');
      return;
    }
    if (group === 'curriculumDisplay') {
      byId('cfgCurriculumEditId').value = row.id || '';
      byId('cfgCurriculumSy').value = row.sy || '';
      byId('cfgCurriculumSemester').value = row.semester || '';
      byId('cfgCurriculumStatus').value = row.status || '';
      byId('cfgCurriculumSaveBtn').textContent = 'Update';
      syncSelectUI('cfgCurriculumSemester');
      syncSelectUI('cfgCurriculumStatus');
    }
  }
  function bindForms() {
    var schoolSemButton = byId('cfgSSSaveBtn');
    if (schoolSemButton) {
      schoolSemButton.addEventListener('click', saveSchoolSem);
    }
    var gradePostingButton = byId('cfgGPSaveBtn');
    if (gradePostingButton) {
      gradePostingButton.addEventListener('click', saveGradePosting);
    }
    var signatureForm = byId('cfgSignatureForm');
    if (signatureForm) {
      signatureForm.addEventListener('submit', submitSignatureForm);
    }
    var cutoffDateForm = byId('cfgCutoffDateForm');
    if (cutoffDateForm) {
      cutoffDateForm.addEventListener('submit', submitCutoffDateForm);
    }
    var sectionCutoffForm = byId('cfgSectionCutoffForm');
    if (sectionCutoffForm) {
      sectionCutoffForm.addEventListener('submit', submitSectionCutoffForm);
    }
    var cutoffConfigForm = byId('cfgCutoffConfigForm');
    if (cutoffConfigForm) {
      cutoffConfigForm.addEventListener('submit', submitCutoffConfigForm);
    }
    var cutoffRegistrationForm = byId('cfgCutoffRegistrationForm');
    if (cutoffRegistrationForm) {
      cutoffRegistrationForm.addEventListener('submit', submitCutoffRegistrationForm);
    }
    var curriculumForm = byId('cfgCurriculumDisplayForm');
    if (curriculumForm) {
      curriculumForm.addEventListener('submit', submitCurriculumForm);
    }
    var reportForm = byId('cfgReportDetailsForm');
    if (reportForm) {
      reportForm.addEventListener('submit', submitReportDetailsForm);
    }
    var emailForm = byId('cfgEmailSenderForm');
    if (emailForm) {
      emailForm.addEventListener('submit', submitEmailSenderForm);
    }
    var overdueIncForm = byId('cfgOverdueIncForm');
    if (overdueIncForm) {
      overdueIncForm.addEventListener('submit', submitOverdueIncForm);
    }
  }
  function bindGlobalEvents() {
    document.addEventListener('click', function (event) {
      var openSchool = event.target.closest('[data-cfg-action="open-school-sem-modal"]');
      if (openSchool) {
        resetSchoolSemModal();
        openModal('cfgSchoolSemModal');
        return;
      }
      var openGrade = event.target.closest('[data-cfg-action="open-grade-posting-modal"]');
      if (openGrade) {
        resetGradePostingModal();
        openModal('cfgGradePostingModal');
        return;
      }
      var closeModalButton = event.target.closest('[data-cfg-action="close-modal"]');
      if (closeModalButton) {
        var modalTarget = closeModalButton.getAttribute('data-cfg-modal-target');
        if (modalTarget) {
          closeModal(modalTarget);
        }
        return;
      }
      var menuToggle = event.target.closest('[data-cfg-menu-toggle]');
      if (menuToggle) {
        event.stopPropagation();
        toggleActionMenu(menuToggle.getAttribute('data-cfg-menu-toggle'), menuToggle);
        return;
      }
      var pageButton = event.target.closest('[data-cfg-action="set-page"]');
      if (pageButton) {
        var pageGroup = pageButton.getAttribute('data-cfg-group');
        var pageValue = parseInt(pageButton.getAttribute('data-cfg-page'), 10) || 1;
        if (pageGroup && state.pager[pageGroup]) {
          setPage(pageGroup, pageValue);
        }
        return;
      }
      var editButton = event.target.closest('[data-cfg-action="edit-row"]');
      if (editButton) {
        var editGroup = editButton.getAttribute('data-cfg-group');
        var editIndex = parseInt(editButton.getAttribute('data-cfg-index'), 10) || 0;
        closeActionMenus();
        editRow(editGroup, editIndex);
        return;
      }
      var deleteButton = event.target.closest('[data-cfg-action="delete-row"]');
      if (deleteButton) {
        var deleteGroup = deleteButton.getAttribute('data-cfg-group');
        var deleteIndex = parseInt(deleteButton.getAttribute('data-cfg-index'), 10) || 0;
        closeActionMenus();
        openDeleteModal(deleteGroup, deleteIndex);
        return;
      }
      var confirmDeleteButton = event.target.closest('[data-cfg-action="confirm-delete"]');
      if (confirmDeleteButton) {
        confirmDelete();
        return;
      }
      if (!event.target.closest('.apst-dropdown')) {
        closeActionMenus();
      }
    });
    document.querySelectorAll('[data-cfg-modal]').forEach(function (overlay) {
      overlay.addEventListener('click', function (event) {
        if (event.target === overlay) {
          overlay.classList.add('is-hidden');
        }
      });
    });
    window.addEventListener('scroll', closeActionMenus, true);
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeActionMenus();
      }
    });
  }
  function applyInitialMessages() {
    if (!state.latestIncRun || state.latestIncRun.processedCount === undefined) {
      return;
    }
    var message = 'Last run: ' + (state.latestIncRun.schoolYear || '-') + ' ' + (state.latestIncRun.semester || '-') + ' | Processed ' + state.latestIncRun.processedCount + ' record(s) on ' + (state.latestIncRun.createdAt || '-');
    setInlineMessage('cfgIncProcessMessage', message, false);
  }
  bindForms();
  bindGlobalEvents();
  renderAll();
  refreshListboxes();
  applyInitialMessages();
})();

/***/ }),

/***/ 14:
/*!*********************************************************************!*\
  !*** multi ./resources/js/registrar-system-config-configuration.js ***!
  \*********************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\Users\micha\Desktop\OJT\plp-demo\resources\js\registrar-system-config-configuration.js */"./resources/js/registrar-system-config-configuration.js");


/***/ })

/******/ });