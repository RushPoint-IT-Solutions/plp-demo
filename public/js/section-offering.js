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

/***/ "./resources/js/section-offering.js":
/*!******************************************!*\
  !*** ./resources/js/section-offering.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

document.addEventListener('DOMContentLoaded', function () {
  var lines = document.querySelectorAll('.section-offering-page .cor-info-line');
  if (!lines || !lines.length) return;
  lines.forEach(function (line) {
    var labelEl = line.querySelector('.cor-info-label');
    if (!labelEl) return;
    var labelText = labelEl.textContent.trim().toLowerCase();
    if (!labelText.startsWith('school year')) return;
    var valueEl = line.querySelector('.cor-info-value');
    if (!valueEl) return;
    var raw = valueEl.textContent.trim();

    // Extract school year range (e.g. 2025-2026)
    var yearMatch = raw.match(/(\d{4}-\d{4})/);

    // Extract semester token (1st, 2nd, 3rd, first, second, etc., or plain digits)
    var semMatch = raw.match(/(1st|1|first|one|2nd|2|second|two|3rd|3|third|three)/i);
    var result = '';
    if (yearMatch) result = yearMatch[1];
    if (semMatch) {
      var token = semMatch[1].toLowerCase();
      var num = null;
      if (/1|first|one/.test(token)) num = 1;else if (/2|second|two/.test(token)) num = 2;else if (/3|third|three/.test(token)) num = 3;
      if (num !== null) {
        var suffix = num === 1 ? 'ST' : num === 2 ? 'ND' : num === 3 ? 'RD' : 'TH';
        result = (result ? result + ' / ' : '') + "".concat(num).concat(suffix, " SEMESTER");
      }
    }

    // Fallback: if nothing parsed, strip leading 'SY' and trim
    if (!result) {
      var fallback = raw.replace(/^\s*SY\s*/i, '').trim();
      valueEl.textContent = fallback;
    } else {
      valueEl.textContent = result;
    }
  });
});

/***/ }),

/***/ 3:
/*!************************************************!*\
  !*** multi ./resources/js/section-offering.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\Users\micha\Desktop\OJT\plp-demo\resources\js\section-offering.js */"./resources/js/section-offering.js");


/***/ })

/******/ });