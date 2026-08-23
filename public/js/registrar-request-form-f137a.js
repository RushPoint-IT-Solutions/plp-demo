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
  var studentSearch = page ? page.querySelector('[data-rf137a-student-search]') : null;
  var studentIdInput = page ? page.querySelector('#rf137a-student-id') : null;
  var studentSearchInput = page ? page.querySelector('#rf137a-student-search-input') : null;
  var studentResults = page ? page.querySelector('#rf137a-student-results') : null;
  var studentSearchRequest = null;
  var studentSearchTimer = null;
  var activeStudentIndex = -1;
  var requestAutoLabel = page ? page.querySelector('#rf137a-request-auto-label') : null;
  var requestOrder = page ? page.querySelector('#rf137a-request-order') : null;
  function renderRequestNumber(number) {
    var safeNumber = Math.max(1, Math.min(4, parseInt(number, 10) || 1));
    var suffixes = {
      '1': 'st',
      '2': 'nd',
      '3': 'rd',
      '4': 'th'
    };
    var label = safeNumber + suffixes[safeNumber] + ' Request';
    if (requestAutoLabel) {
      requestAutoLabel.textContent = label;
    }
    if (requestOrder) {
      requestOrder.innerHTML = '<span>' + safeNumber + '</span><sup>' + suffixes[safeNumber] + '</sup> Request';
    }
  }
  function autoResizeSentenceInput(input) {
    var nextWidth = Math.max((input.value.length || 1) + 2, 18);
    input.style.width = nextWidth + 'ch';
  }
  function syncA4Scale() {
    if (!page || !sheet) {
      return;
    }
    var targetWidth = 1056;
    var availableWidth = page.clientWidth;
    var scale = 1;
    if (availableWidth > 0 && availableWidth < targetWidth) {
      scale = availableWidth / targetWidth;
    }
    sheet.style.setProperty('--rf137a-zoom', scale.toFixed(4));
    sheet.style.setProperty('--rf137a-scale', scale.toFixed(4));
  }
  function getStudentOptions() {
    return studentResults ? Array.prototype.slice.call(studentResults.querySelectorAll('.rf137a-student-option')) : [];
  }
  function setStudentSearchOpen(isOpen) {
    if (!studentSearch || !studentSearchInput) {
      return;
    }
    studentSearch.classList.toggle('is-open', isOpen);
    studentSearchInput.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }
  function setActiveStudentOption(index) {
    var options = getStudentOptions();
    options.forEach(function (option) {
      option.classList.remove('is-active');
      option.setAttribute('aria-selected', 'false');
    });
    if (!options.length) {
      activeStudentIndex = -1;
      return;
    }
    if (index < 0) {
      index = options.length - 1;
    }
    if (index >= options.length) {
      index = 0;
    }
    activeStudentIndex = index;
    options[index].classList.add('is-active');
    options[index].setAttribute('aria-selected', 'true');
    options[index].scrollIntoView({
      block: 'nearest'
    });
  }
  function chooseStudent(option) {
    if (!option || !studentIdInput || !studentSearchInput) {
      return;
    }
    studentIdInput.value = option.getAttribute('data-student-id') || '';
    studentSearchInput.value = option.getAttribute('data-student-label') || option.textContent.trim();
    setStudentSearchOpen(false);
    if (studentIdInput.value && studentIdInput.form) {
      studentIdInput.form.submit();
    }
  }
  function renderStudentResults(items, message) {
    if (!studentResults) {
      return;
    }
    studentResults.innerHTML = '';
    activeStudentIndex = -1;
    if (!items.length) {
      var empty = document.createElement('div');
      empty.className = 'rf137a-student-empty';
      empty.textContent = message || 'No matching students found';
      studentResults.appendChild(empty);
      setStudentSearchOpen(true);
      return;
    }
    items.forEach(function (item, index) {
      var option = document.createElement('button');
      option.type = 'button';
      option.className = 'rf137a-student-option';
      option.setAttribute('role', 'option');
      option.setAttribute('aria-selected', 'false');
      option.setAttribute('data-student-id', item.id);
      option.setAttribute('data-student-label', item.label || '');
      var number = document.createElement('strong');
      number.textContent = item.student_no || 'No student number';
      var name = document.createElement('span');
      name.textContent = item.name || 'Unnamed student';
      option.appendChild(number);
      option.appendChild(name);
      option.addEventListener('mouseenter', function () {
        setActiveStudentOption(index);
      });
      option.addEventListener('mousedown', function (event) {
        event.preventDefault();
      });
      option.addEventListener('click', function () {
        chooseStudent(option);
      });
      studentResults.appendChild(option);
    });
    setStudentSearchOpen(true);
    setActiveStudentOption(0);
  }
  function searchStudents() {
    if (!studentSearch || !studentSearchInput) {
      return;
    }
    var searchUrl = studentSearch.getAttribute('data-search-url');
    if (!searchUrl) {
      return;
    }
    if (studentSearchRequest && typeof studentSearchRequest.abort === 'function') {
      studentSearchRequest.abort();
    }
    studentSearchRequest = window.AbortController ? new AbortController() : null;
    renderStudentResults([], 'Searching students...');
    fetch(searchUrl + '?q=' + encodeURIComponent(studentSearchInput.value.trim()) + '&limit=25', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      signal: studentSearchRequest ? studentSearchRequest.signal : undefined
    }).then(function (response) {
      if (!response.ok) {
        throw new Error('Student search failed');
      }
      return response.json();
    }).then(function (payload) {
      renderStudentResults(payload.results || []);
    })["catch"](function (error) {
      if (error && error.name === 'AbortError') {
        return;
      }
      renderStudentResults([], 'Unable to load student matches');
    });
  }
  if (studentSearch && studentIdInput && studentSearchInput && studentResults) {
    studentSearchInput.addEventListener('input', function () {
      studentIdInput.value = '';
      window.clearTimeout(studentSearchTimer);
      studentSearchTimer = window.setTimeout(searchStudents, 180);
    });
    studentSearchInput.addEventListener('focus', searchStudents);
    studentSearchInput.addEventListener('keydown', function (event) {
      var options = getStudentOptions();
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        setActiveStudentOption(activeStudentIndex + 1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        setActiveStudentOption(activeStudentIndex - 1);
      } else if (event.key === 'Enter' && options[activeStudentIndex]) {
        event.preventDefault();
        chooseStudent(options[activeStudentIndex]);
      } else if (event.key === 'Escape') {
        setStudentSearchOpen(false);
      }
    });
    document.addEventListener('click', function (event) {
      if (!event.target.closest('[data-rf137a-student-search]')) {
        setStudentSearchOpen(false);
      }
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
  var printBtn = page ? page.querySelector('#rf137a-print-btn') : null;
  if (printBtn) {
    printBtn.addEventListener('click', function () {
      var printUrl = page.getAttribute('data-print-url');
      var csrfMeta = document.querySelector('meta[name="csrf-token"]');
      if (!printUrl) {
        window.alert('Please select a student before printing.');
        return;
      }
      printBtn.disabled = true;
      printBtn.classList.add('is-printing');
      fetch(printUrl, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : ''
        }
      }).then(function (response) {
        return response.json().then(function (payload) {
          if (!response.ok || !payload.ok) {
            throw new Error(payload.message || 'Unable to record print count');
          }
          return payload;
        });
      }).then(function (payload) {
        renderRequestNumber(payload.request_number);
        window.print();
      })["catch"](function (error) {
        console.error('Print failed', error);
        window.alert(error.message || 'Unable to print the form. Please try again.');
      }).then(function () {
        printBtn.disabled = false;
        printBtn.classList.remove('is-printing');
      });
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

module.exports = __webpack_require__(/*! C:\xampp\htdocs\plp-demo\resources\js\registrar-request-form-f137a.js */"./resources/js/registrar-request-form-f137a.js");


/***/ })

/******/ });