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
/******/ 	return __webpack_require__(__webpack_require__.s = 9);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/registrar-request-form-f137a.js":
/*!******************************************************!*\
  !*** ./resources/js/registrar-request-form-f137a.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

document.addEventListener('DOMContentLoaded', function () {
  var page = document.getElementById('rf137a-page');
  var sheet = page ? page.querySelector('.rf137a-sheet') : null;
  var sentenceInputs = page ? page.querySelectorAll('.rf137a-sentence-field .rf137a-inline-input') : [];
  function autoResizeSentenceInput(input) {
    var nextWidth = Math.max((input.value.length || 1) + 2, 18);
    input.style.width = nextWidth + 'ch';
  }
  function syncA4Scale() {
    if (!page || !sheet) {
      return;
    }
    var targetWidth = 794;
    var availableWidth = page.clientWidth;
    var scale = 1;
    if (availableWidth > 0 && availableWidth < targetWidth) {
      scale = availableWidth / targetWidth;
    }
    sheet.style.setProperty('--rf137a-zoom', scale.toFixed(4));
    sheet.style.setProperty('--rf137a-scale', scale.toFixed(4));
  }
  if (sentenceInputs.length) {
    sentenceInputs.forEach(function (input) {
      autoResizeSentenceInput(input);
      input.addEventListener('input', function () {
        autoResizeSentenceInput(input);
      });
    });
  }
  var printBtn = page ? page.querySelector('#rf137a-print-btn') : null;
  if (printBtn) {
    printBtn.addEventListener('click', function () {
      try {
        // Give immediate visual feedback and rely on beforeprint/afterprint to reset
        printBtn.disabled = true;
        printBtn.classList.add('is-printing');
        window.print();
      } catch (e) {
        console.error('Print failed', e);
        try {
          printBtn.disabled = false;
          printBtn.classList.remove('is-printing');
        } catch (er) {}
      }
    });
  }
  window.addEventListener('resize', syncA4Scale);
  window.addEventListener('orientationchange', syncA4Scale);
  window.addEventListener('beforeprint', function () {
    if (!sheet) {
      return;
    }
    sheet.style.setProperty('--rf137a-zoom', '1');
    sheet.style.setProperty('--rf137a-scale', '1');
    if (printBtn) {
      try {
        printBtn.disabled = true;
        printBtn.classList.add('is-printing');
      } catch (e) {}
    }
  });
  window.addEventListener('afterprint', function () {
    try {
      syncA4Scale();
    } catch (e) {}
    if (printBtn) {
      try {
        printBtn.disabled = false;
        printBtn.classList.remove('is-printing');
      } catch (e) {}
    }
  });
  syncA4Scale();
});

/***/ }),

/***/ 9:
/*!************************************************************!*\
  !*** multi ./resources/js/registrar-request-form-f137a.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\Users\micha\Desktop\OJT\plp-demo\resources\js\registrar-request-form-f137a.js */"./resources/js/registrar-request-form-f137a.js");


/***/ })

/******/ });