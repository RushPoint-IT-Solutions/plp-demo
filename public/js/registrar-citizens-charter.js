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
/******/ 	return __webpack_require__(__webpack_require__.s = 6);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/registrar-citizens-charter.js":
/*!****************************************************!*\
  !*** ./resources/js/registrar-citizens-charter.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

document.addEventListener('DOMContentLoaded', function () {
  var pageRoot = document.getElementById('citizens-charter-page');
  if (!pageRoot) {
    return;
  }
  var stage = document.getElementById('cc-stage');
  var pages = Array.prototype.slice.call(pageRoot.querySelectorAll('.cc-sheet'));
  var prevButton = document.getElementById('cc-prev');
  var nextButton = document.getElementById('cc-next');
  var pageNumbersWrap = document.getElementById('cc-page-numbers');
  var pageIndicator = document.getElementById('cc-page-indicator');
  if (!pages.length || !stage) {
    return;
  }
  var currentIndex = 0;
  function targetSheetWidth() {
    return 816;
  }
  function syncScale() {
    var availableWidth = stage.clientWidth;
    var scale = 1;
    if (availableWidth > 0 && availableWidth < targetSheetWidth()) {
      scale = availableWidth / targetSheetWidth();
    }
    pageRoot.style.setProperty('--cc-zoom', scale.toFixed(4));
    pageRoot.style.setProperty('--cc-scale', scale.toFixed(4));
  }
  function createPageButtons() {
    if (!pageNumbersWrap) {
      return;
    }
    pageNumbersWrap.innerHTML = '';
    pages.forEach(function (page, index) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'cc-page-btn cc-page-btn--num';
      button.textContent = String(index + 1);
      button.setAttribute('data-page-index', String(index));
      button.setAttribute('aria-label', 'Go to page ' + (index + 1));
      button.addEventListener('click', function () {
        currentIndex = index;
        updateView();
      });
      pageNumbersWrap.appendChild(button);
    });
  }
  function updateButtons() {
    if (prevButton) {
      prevButton.disabled = currentIndex === 0;
    }
    if (nextButton) {
      nextButton.disabled = currentIndex === pages.length - 1;
    }
    if (pageNumbersWrap) {
      var buttons = pageNumbersWrap.querySelectorAll('.cc-page-btn--num');
      Array.prototype.forEach.call(buttons, function (button, index) {
        var isActive = index === currentIndex;
        button.classList.toggle('is-active', isActive);
        if (isActive) {
          button.setAttribute('aria-current', 'page');
        } else {
          button.removeAttribute('aria-current');
        }
      });
    }
    if (pageIndicator) {
      pageIndicator.textContent = 'Page ' + (currentIndex + 1) + ' of ' + pages.length;
    }
  }
  function updatePages() {
    pages.forEach(function (page, index) {
      page.classList.toggle('is-active', index === currentIndex);
    });
  }
  function updateView() {
    updatePages();
    updateButtons();
    syncScale();
  }
  if (prevButton) {
    prevButton.addEventListener('click', function () {
      if (currentIndex <= 0) {
        return;
      }
      currentIndex -= 1;
      updateView();
    });
  }
  if (nextButton) {
    nextButton.addEventListener('click', function () {
      if (currentIndex >= pages.length - 1) {
        return;
      }
      currentIndex += 1;
      updateView();
    });
  }
  window.addEventListener('resize', syncScale);
  window.addEventListener('orientationchange', syncScale);
  window.addEventListener('beforeprint', function () {
    pageRoot.style.setProperty('--cc-zoom', '1');
    pageRoot.style.setProperty('--cc-scale', '1');
  });
  window.addEventListener('afterprint', syncScale);
  createPageButtons();
  updateView();
});

/***/ }),

/***/ 6:
/*!**********************************************************!*\
  !*** multi ./resources/js/registrar-citizens-charter.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\Users\micha\Desktop\OJT\plp-demo\resources\js\registrar-citizens-charter.js */"./resources/js/registrar-citizens-charter.js");


/***/ })

/******/ });