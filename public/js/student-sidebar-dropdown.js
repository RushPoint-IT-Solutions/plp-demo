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
/******/ 	return __webpack_require__(__webpack_require__.s = 3);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/student-sidebar-dropdown.js":
/*!**************************************************!*\
  !*** ./resources/js/student-sidebar-dropdown.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

document.addEventListener('DOMContentLoaded', function () {
  function syncLaoPrintCheckboxState() {
    document.querySelectorAll('.loa-check').forEach(function (label) {
      var checkbox = label.querySelector('input[type="checkbox"]');
      if (!checkbox) {
        return;
      }
      label.setAttribute('data-print-checked', checkbox.checked ? 'true' : 'false');
    });
  }
  function syncPrintableInputState() {
    document.querySelectorAll('.acd-inline-input').forEach(function (input) {
      var hasValue = input.value && input.value.trim().length > 0;
      input.classList.toggle('has-value', hasValue);
    });
  }
  function fitAcdFormCanvas() {
    var page = document.querySelector('.acd-page');
    var canvas = document.querySelector('.acd-canvas');
    var form = document.querySelector('.acd-form');
    if (!page || !canvas || !form) {
      return;
    }
    var baseWidth = form.offsetWidth;
    var baseHeight = form.offsetHeight;
    var actions = canvas.querySelector('.acd-actions');
    var actionsHeight = actions ? actions.offsetHeight + 8 : 0;
    var availableWidth = Math.max(page.clientWidth - 12, 320);
    var scale = Math.min(1, availableWidth / baseWidth);
    canvas.style.setProperty('--acd-scale', scale.toFixed(4));
    canvas.style.width = baseWidth * scale + 'px';
    canvas.style.minWidth = baseWidth * scale + 'px';
    canvas.style.height = baseHeight * scale + actionsHeight + 'px';
  }
  document.querySelectorAll('.sidebar-dropdown-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      var dropdown = this.closest('.sidebar-dropdown');
      if (!dropdown) {
        return;
      }
      dropdown.classList.toggle('open');
    });
  });
  var printBtn = document.getElementById('acd-print-btn');
  if (printBtn) {
    printBtn.addEventListener('click', function () {
      syncLaoPrintCheckboxState();
      syncPrintableInputState();
      window.print();
    });
  }
  window.addEventListener('beforeprint', function () {
    syncLaoPrintCheckboxState();
    syncPrintableInputState();
  });
  document.addEventListener('input', function (event) {
    var target = event.target;
    if (!target || target.matches('.acd-inline-input') === false) {
      return;
    }
    syncPrintableInputState();
  });
  document.addEventListener('change', function (event) {
    var target = event.target;
    if (!target || target.matches('.loa-check input[type="checkbox"]') === false) {
      if (target && target.matches('.acd-inline-input')) {
        syncPrintableInputState();
      }
      return;
    }
    syncLaoPrintCheckboxState();
  });
  fitAcdFormCanvas();
  window.addEventListener('resize', fitAcdFormCanvas);
  syncLaoPrintCheckboxState();
  syncPrintableInputState();
});

/***/ }),

/***/ 3:
/*!********************************************************!*\
  !*** multi ./resources/js/student-sidebar-dropdown.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\Users\micha\Desktop\OJT\plp-demo\resources\js\student-sidebar-dropdown.js */"./resources/js/student-sidebar-dropdown.js");


/***/ })

/******/ });