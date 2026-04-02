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
/******/ 	return __webpack_require__(__webpack_require__.s = 5);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/registrar-loa-enrolled.js":
/*!************************************************!*\
  !*** ./resources/js/registrar-loa-enrolled.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

document.addEventListener('DOMContentLoaded', function () {
  var studentSelect = document.getElementById('loae-student-id');
  var printButton = document.getElementById('loae-print-btn');
  var stage = document.querySelector('.loae-a4-stage');
  var sheet = document.querySelector('.a4-wrapper');
  var sentenceInputs = document.querySelectorAll('.loae-sentence-field .loae-inline');
  function autoResizeSentenceInput(input) {
    input.style.width = (input.value.length || 1) + 1 + 'ch';
  }
  function syncA4Scale() {
    if (!stage || !sheet) {
      return;
    }
    var targetWidth = 794;
    var availableWidth = stage.clientWidth;
    var scale = 1;
    if (availableWidth > 0 && availableWidth < targetWidth) {
      scale = availableWidth / targetWidth;
    }
    sheet.style.setProperty('--loae-zoom', scale.toFixed(4));
    sheet.style.setProperty('--loae-scale', scale.toFixed(4));
  }
  if (studentSelect) {
    studentSelect.addEventListener('change', function () {
      if (!studentSelect.value) {
        return;
      }
      var form = studentSelect.form;
      if (form) {
        form.submit();
      }
    });
  }
  if (printButton) {
    printButton.addEventListener('click', function () {
      window.print();
    });
  }
  if (sentenceInputs.length) {
    sentenceInputs.forEach(function (input) {
      autoResizeSentenceInput(input);
      input.addEventListener('input', function () {
        autoResizeSentenceInput(input);
      });
    });
  }
  window.addEventListener('resize', syncA4Scale);
  window.addEventListener('orientationchange', syncA4Scale);
  window.addEventListener('beforeprint', function () {
    if (!sheet) {
      return;
    }
    sheet.style.setProperty('--loae-zoom', '1');
    sheet.style.setProperty('--loae-scale', '1');
  });
  window.addEventListener('afterprint', syncA4Scale);
  syncA4Scale();
});

/***/ }),

/***/ 5:
/*!******************************************************!*\
  !*** multi ./resources/js/registrar-loa-enrolled.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\Users\micha\Desktop\OJT\plp-demo\resources\js\registrar-loa-enrolled.js */"./resources/js/registrar-loa-enrolled.js");


/***/ })

/******/ });