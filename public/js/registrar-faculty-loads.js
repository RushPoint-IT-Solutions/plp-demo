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
/******/ 	return __webpack_require__(__webpack_require__.s = 10);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/registrar-faculty-loads.js":
/*!*************************************************!*\
  !*** ./resources/js/registrar-faculty-loads.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

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
  onReady(function () {
    document.querySelectorAll('.rfl-row[data-href]').forEach(function (row) {
      row.addEventListener('click', function (e) {
        var target = e.target;
        if (target && (target.tagName === 'A' || target.closest('a'))) return;
        var href = row.getAttribute('data-href');
        if (href) window.location.href = href;
      });
    });
    document.querySelectorAll('[data-rfl-auto-submit-search]').forEach(function (input) {
      var form = input.closest('form');
      if (!form) {
        return;
      }
      var submitSearch = debounce(function () {
        form.submit();
      }, 280);
      input.addEventListener('input', function () {
        submitSearch();
      });
      input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
          event.preventDefault();
          form.submit();
        }
      });
    });
    var assignForm = document.querySelector('.rfl-assign-form');
    if (assignForm) {
      var setAddButtonState = function setAddButtonState() {
        if (!addSubjectButton || !subjectSelect) {
          return;
        }
        if (!subjectSelect.options.length) {
          addSubjectButton.disabled = true;
          return;
        }
        addSubjectButton.disabled = !subjectSelect.value;
      };
      var validateDecimalField = function validateDecimalField(field, label) {
        if (!field) {
          return true;
        }
        var rawValue = (field.value || '').trim();
        field.setCustomValidity('');
        if (rawValue === '') {
          return true;
        }
        if (!/^\d{1,3}(\.\d{1,2})?$/.test(rawValue)) {
          field.setCustomValidity(label + ' must be between 0.00 and 999.99 with up to 2 decimal places.');
          return false;
        }
        var numericValue = Number(rawValue);
        if (!isFinite(numericValue) || numericValue < 0 || numericValue > 999.99) {
          field.setCustomValidity(label + ' must be between 0.00 and 999.99.');
          return false;
        }
        return true;
      };
      var subjectSelect = assignForm.querySelector('select[name="subject_id"]');
      var addSubjectButton = assignForm.querySelector('#rflAddSubjectBtn');
      var creditedUnitsInput = assignForm.querySelector('input[name="credited_tuition_units"]');
      var loadHoursInput = assignForm.querySelector('input[name="load_hours"]');
      if (subjectSelect) {
        subjectSelect.addEventListener('change', setAddButtonState);
      }
      [creditedUnitsInput, loadHoursInput].forEach(function (field) {
        if (!field) {
          return;
        }
        field.addEventListener('input', function () {
          field.setCustomValidity('');
        });
        field.addEventListener('blur', function () {
          validateDecimalField(field, field.name === 'credited_tuition_units' ? 'Credited Tuition Units' : 'Load Hours');
          field.reportValidity();
        });
      });
      assignForm.addEventListener('submit', function (event) {
        var hasErrors = false;
        if (subjectSelect && !subjectSelect.value) {
          subjectSelect.setCustomValidity('Please select a subject from the available list before adding.');
          subjectSelect.reportValidity();
          hasErrors = true;
        } else if (subjectSelect) {
          subjectSelect.setCustomValidity('');
        }
        if (!validateDecimalField(creditedUnitsInput, 'Credited Tuition Units')) {
          if (creditedUnitsInput) {
            creditedUnitsInput.reportValidity();
          }
          hasErrors = true;
        }
        if (!validateDecimalField(loadHoursInput, 'Load Hours')) {
          if (loadHoursInput) {
            loadHoursInput.reportValidity();
          }
          hasErrors = true;
        }
        if (hasErrors) {
          event.preventDefault();
        }
      });
      setAddButtonState();
    }
    function buildPrintStylesHtml(configNode) {
      if (!configNode) {
        return '';
      }
      var cssUrls = [configNode.getAttribute('data-bootstrap-css') || '', configNode.getAttribute('data-app-css') || '', configNode.getAttribute('data-style-css') || '', configNode.getAttribute('data-print-css') || ''].filter(function (url) {
        return url !== '';
      });
      return cssUrls.map(function (url) {
        return '<link rel="stylesheet" href="' + url + '">';
      }).join('');
    }
    function buildPrintFaviconHtml(configNode) {
      if (!configNode) {
        return '';
      }
      var faviconUrl = (configNode.getAttribute('data-favicon') || '').trim();
      if (faviconUrl === '') {
        return '';
      }
      return '<link rel="icon" href="' + faviconUrl + '" type="image/png">';
    }
    function printTemplate(templateKey) {
      var template = document.getElementById('rflPrintTemplate-' + templateKey);
      var frame = document.getElementById('rflPrintFrame');
      var config = document.getElementById('rflPrintConfig');
      if (!template || !frame) {
        return;
      }
      var styles = buildPrintStylesHtml(config);
      var favicon = buildPrintFaviconHtml(config);
      var frameDoc = frame.contentDocument || frame.contentWindow && frame.contentWindow.document;
      if (!frameDoc) {
        return;
      }
      var html = '' + '<!DOCTYPE html>' + '<html lang="en">' + '<head>' + '<meta charset="utf-8">' + '<meta name="viewport" content="width=device-width, initial-scale=1">' + '<title>Faculty Loads Print</title>' + favicon + styles + '</head>' + '<body class="rfl-strength-print-page">' + template.innerHTML + '</body>' + '</html>';
      frameDoc.open();
      frameDoc.write(html);
      frameDoc.close();
      setTimeout(function () {
        if (!frame.contentWindow) {
          return;
        }
        frame.contentWindow.focus();
        frame.contentWindow.print();
      }, 400);
    }
    document.querySelectorAll('[data-rfl-print-template]').forEach(function (button) {
      button.addEventListener('click', function () {
        var templateKey = (button.getAttribute('data-rfl-print-template') || '').trim();
        if (templateKey === '') {
          return;
        }
        printTemplate(templateKey);
      });
    });
    document.querySelectorAll('[data-rfl-print-trigger]').forEach(function (button) {
      button.addEventListener('click', function () {
        window.print();
      });
    });
  });
})();

/***/ }),

/***/ 10:
/*!*******************************************************!*\
  !*** multi ./resources/js/registrar-faculty-loads.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\Users\micha\Desktop\OJT\plp-demo\resources\js\registrar-faculty-loads.js */"./resources/js/registrar-faculty-loads.js");


/***/ })

/******/ });