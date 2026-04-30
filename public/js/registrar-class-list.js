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
/******/ 	return __webpack_require__(__webpack_require__.s = 13);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/registrar-class-list.js":
/*!**********************************************!*\
  !*** ./resources/js/registrar-class-list.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
(function () {
  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }
  function debounce(fn, delay) {
    var timer = null;
    return function () {
      var args = arguments;
      if (timer) {
        clearTimeout(timer);
      }
      timer = setTimeout(function () {
        fn.apply(null, args);
      }, delay);
    };
  }
  function normalizeSemesterValue(value) {
    var normalized = String(value || '').trim().toLowerCase();
    if (normalized === '') {
      return '';
    }
    if (normalized.indexOf('summer') !== -1) {
      return 'Summer';
    }
    if (normalized.indexOf('second') !== -1 || normalized.indexOf('2nd') !== -1 || normalized === '2') {
      return 'Second';
    }
    if (normalized.indexOf('first') !== -1 || normalized.indexOf('1st') !== -1 || normalized === '1') {
      return 'First';
    }
    return '';
  }
  function semesterWeight(value) {
    var normalized = normalizeSemesterValue(value);
    if (normalized === 'First') {
      return 1;
    }
    if (normalized === 'Second') {
      return 2;
    }
    if (normalized === 'Summer') {
      return 3;
    }
    return 4;
  }
  function parseSemesterMap(raw) {
    if (!raw) {
      return {};
    }
    try {
      var parsed = JSON.parse(raw);
      if (parsed && _typeof(parsed) === 'object') {
        return parsed;
      }
    } catch (error) {
      return {};
    }
    return {};
  }
  function emitListboxRefresh(selectElement) {
    if (!selectElement) {
      return;
    }
    if (window.registrarListboxSelect && typeof window.registrarListboxSelect.refresh === 'function') {
      window.registrarListboxSelect.refresh(selectElement);
      return;
    }
    var detail = {
      target: selectElement
    };
    if (typeof window.CustomEvent === 'function') {
      document.dispatchEvent(new CustomEvent('registrar:listbox:refresh', {
        detail: detail
      }));
      return;
    }
    var legacyEvent = document.createEvent('CustomEvent');
    legacyEvent.initCustomEvent('registrar:listbox:refresh', true, true, detail);
    document.dispatchEvent(legacyEvent);
  }
  function uniqueSemesters(list) {
    var values = [];
    (Array.isArray(list) ? list : []).forEach(function (item) {
      var normalized = normalizeSemesterValue(item);
      if (!normalized || values.indexOf(normalized) !== -1) {
        return;
      }
      values.push(normalized);
    });
    values.sort(function (left, right) {
      return semesterWeight(left) - semesterWeight(right);
    });
    return values;
  }
  function semesterListForYear(semesterMap, selectedSchoolYear) {
    var schoolYear = String(selectedSchoolYear || '').trim();
    if (schoolYear !== '' && semesterMap && Array.isArray(semesterMap[schoolYear])) {
      return uniqueSemesters(semesterMap[schoolYear]);
    }
    var all = [];
    if (!semesterMap || _typeof(semesterMap) !== 'object') {
      return all;
    }
    Object.keys(semesterMap).forEach(function (year) {
      all = all.concat(Array.isArray(semesterMap[year]) ? semesterMap[year] : []);
    });
    return uniqueSemesters(all);
  }
  function syncSemesterOptions(schoolYearSelect, semesterSelect, semesterMap) {
    if (!schoolYearSelect || !semesterSelect) {
      return;
    }
    var activeSchoolYear = schoolYearSelect.value;
    var currentSemester = normalizeSemesterValue(semesterSelect.value);
    var semesterList = semesterListForYear(semesterMap, activeSchoolYear);
    var html = ['<option value="">All Semesters</option>'];
    semesterList.forEach(function (semester) {
      html.push('<option value="' + semester + '">' + semester + '</option>');
    });
    semesterSelect.innerHTML = html.join('');
    if (currentSemester && semesterList.indexOf(currentSemester) !== -1) {
      semesterSelect.value = currentSemester;
    } else {
      semesterSelect.value = '';
    }
    emitListboxRefresh(semesterSelect);
  }
  onReady(function () {
    var page = document.getElementById('classListPage');
    if (!page) {
      return;
    }
    var filterForm = document.getElementById('clFilterForm');
    if (!filterForm) {
      return;
    }
    var schoolYearSelect = document.getElementById('clSchoolYear');
    var semesterSelect = document.getElementById('clSemester');
    var semesterMap = parseSemesterMap(filterForm.getAttribute('data-semester-map') || '{}');
    if (schoolYearSelect && semesterSelect) {
      syncSemesterOptions(schoolYearSelect, semesterSelect, semesterMap);
      schoolYearSelect.addEventListener('change', function () {
        syncSemesterOptions(schoolYearSelect, semesterSelect, semesterMap);
      });
    }
    var searchInput = filterForm.querySelector('[data-cl-auto-submit-search]');
    if (!searchInput) {
      return;
    }
    var submitSearch = debounce(function () {
      filterForm.submit();
    }, 280);
    searchInput.addEventListener('input', function () {
      submitSearch();
    });
    searchInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        filterForm.submit();
      }
    });
  });
})();

/***/ }),

/***/ 13:
/*!****************************************************!*\
  !*** multi ./resources/js/registrar-class-list.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\xampp\htdocs\plp-demo\resources\js\registrar-class-list.js */"./resources/js/registrar-class-list.js");


/***/ })

/******/ });