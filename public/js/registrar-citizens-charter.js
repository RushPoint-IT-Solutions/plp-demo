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
/******/ 	return __webpack_require__(__webpack_require__.s = 8);
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
  function buildVisiblePageTokens(totalPages, activePage) {
    if (totalPages <= 11) {
      var all = [];
      for (var i = 1; i <= totalPages; i += 1) {
        all.push(i);
      }
      return all;
    }
    var visibleMap = {};
    function addRange(start, end) {
      for (var n = start; n <= end; n += 1) {
        if (n >= 1 && n <= totalPages) {
          visibleMap[n] = true;
        }
      }
    }
    addRange(1, 2);
    addRange(totalPages - 3, totalPages);
    addRange(activePage - 2, activePage + 2);
    var pagesSorted = Object.keys(visibleMap).map(function (value) {
      return parseInt(value, 10);
    }).sort(function (a, b) {
      return a - b;
    });
    var tokens = [];
    var previous = 0;
    pagesSorted.forEach(function (pageNumber) {
      if (previous && pageNumber - previous > 1) {
        if (pageNumber - previous === 2) {
          tokens.push(previous + 1);
        } else {
          tokens.push('ellipsis');
        }
      }
      tokens.push(pageNumber);
      previous = pageNumber;
    });
    return tokens;
  }
  function renderPageButtons() {
    if (!pageNumbersWrap) {
      return;
    }
    var activePage = currentIndex + 1;
    var tokens = buildVisiblePageTokens(pages.length, activePage);
    pageNumbersWrap.innerHTML = '';
    tokens.forEach(function (token) {
      var item = document.createElement('li');
      if (token === 'ellipsis') {
        item.className = 'page-item disabled cc-page-item cc-page-item--ellipsis';
        var ellipsisSpan = document.createElement('span');
        ellipsisSpan.className = 'page-link cc-page-link';
        ellipsisSpan.textContent = '...';
        item.appendChild(ellipsisSpan);
        pageNumbersWrap.appendChild(item);
        return;
      }
      var targetIndex = token - 1;
      var isActive = targetIndex === currentIndex;
      item.className = 'page-item cc-page-item cc-page-item--num' + (isActive ? ' active' : '');
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'page-link cc-page-link';
      button.textContent = String(token);
      button.setAttribute('data-page-index', String(targetIndex));
      button.setAttribute('aria-label', 'Go to page ' + token);
      if (isActive) {
        button.setAttribute('aria-current', 'page');
      }
      button.addEventListener('click', function () {
        currentIndex = targetIndex;
        updateView();
      });
      item.appendChild(button);
      pageNumbersWrap.appendChild(item);
    });
  }
  function updateButtons() {
    if (prevButton) {
      var prevDisabled = currentIndex === 0;
      prevButton.disabled = prevDisabled;
      prevButton.classList.toggle('disabled', prevDisabled);
    }
    if (nextButton) {
      var nextDisabled = currentIndex === pages.length - 1;
      nextButton.disabled = nextDisabled;
      nextButton.classList.toggle('disabled', nextDisabled);
    }
  }
  function updatePages() {
    pages.forEach(function (page, index) {
      page.classList.toggle('is-active', index === currentIndex);
    });
  }
  function updateView() {
    updatePages();
    renderPageButtons();
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
  updateView();
});

/***/ }),

/***/ 8:
/*!**********************************************************!*\
  !*** multi ./resources/js/registrar-citizens-charter.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\Users\Luis\Downloads\plp-demo\resources\js\registrar-citizens-charter.js */"./resources/js/registrar-citizens-charter.js");


/***/ })

/******/ });