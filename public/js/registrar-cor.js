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
/******/ 	return __webpack_require__(__webpack_require__.s = 4);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/registrar-cor.js":
/*!***************************************!*\
  !*** ./resources/js/registrar-cor.js ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports) {

document.addEventListener('DOMContentLoaded', function () {
  var printButton = document.getElementById('cor-registrar-print');
  var printedByInput = document.getElementById('cor-printed-by-input');
  var printedByValue = document.getElementById('cor-printed-by-value');
  var timePrintedValue = document.getElementById('cor-time-printed');
  var datePrintedValue = document.getElementById('cor-date-printed');
  function pad(value) {
    return value < 10 ? '0' + value : String(value);
  }
  function formatDate(date) {
    return pad(date.getMonth() + 1) + '/' + pad(date.getDate()) + '/' + date.getFullYear();
  }
  function formatTime(date) {
    var hours = date.getHours();
    var meridian = hours >= 12 ? 'pm' : 'am';
    var displayHour = hours % 12;
    if (displayHour === 0) {
      displayHour = 12;
    }
    return displayHour + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds()) + meridian;
  }
  function syncPrintedBy() {
    if (!printedByValue) {
      return;
    }
    var currentValue = printedByInput ? printedByInput.value : '';
    currentValue = currentValue ? currentValue.trim() : '';
    printedByValue.textContent = currentValue !== '' ? currentValue : 'Registrar User';
  }
  function updatePrintedTimestamp() {
    var now = new Date();
    if (timePrintedValue) {
      timePrintedValue.textContent = formatTime(now);
    }
    if (datePrintedValue) {
      datePrintedValue.textContent = formatDate(now);
    }
  }
  syncPrintedBy();
  updatePrintedTimestamp();
  if (printedByInput) {
    printedByInput.addEventListener('input', syncPrintedBy);
    printedByInput.addEventListener('change', syncPrintedBy);
  }
  window.addEventListener('beforeprint', function () {
    syncPrintedBy();
    updatePrintedTimestamp();
  });
  if (window.matchMedia) {
    var mediaQueryList = window.matchMedia('print');
    if (mediaQueryList && mediaQueryList.addListener) {
      mediaQueryList.addListener(function (event) {
        var isPrintMode = event && typeof event.matches === 'boolean' ? event.matches : mediaQueryList.matches;
        if (isPrintMode) {
          syncPrintedBy();
          updatePrintedTimestamp();
        }
      });
    }
  }
  if (printButton) {
    printButton.addEventListener('click', function () {
      syncPrintedBy();
      updatePrintedTimestamp();
      window.print();
    });
  }

  // Auto-submit COR student selector when user picks a student
  var studentSelect = document.getElementById('cor-student-id');
  if (studentSelect) {
    studentSelect.addEventListener('change', function () {
      // don't submit when no value selected
      if (!studentSelect.value) {
        return;
      }

      // If the select is inside a form, submit that form (GET reload)
      var form = studentSelect.form;
      if (form) {
        form.submit();
      }
    });
  }
});

/***/ }),

/***/ 4:
/*!*********************************************!*\
  !*** multi ./resources/js/registrar-cor.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\Users\Luis\Downloads\plp-demo\resources\js\registrar-cor.js */"./resources/js/registrar-cor.js");


/***/ })

/******/ });