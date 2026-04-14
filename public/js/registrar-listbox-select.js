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

/***/ "./resources/js/registrar-listbox-select.js":
/*!**************************************************!*\
  !*** ./resources/js/registrar-listbox-select.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function () {
  function getWrappers() {
    return Array.prototype.slice.call(document.querySelectorAll('[data-listbox-select]'));
  }
  var wrappers = getWrappers();
  if (!wrappers.length) {
    return;
  }
  function getEnabledButtons(menu) {
    return Array.prototype.filter.call(menu.querySelectorAll('.rg-listbox-option-btn'), function (button) {
      return !button.disabled;
    });
  }
  function setActiveOption(menu, optionButton, shouldFocus) {
    menu.querySelectorAll('.rg-listbox-option-btn').forEach(function (button) {
      button.classList.remove('is-active');
      button.setAttribute('tabindex', '-1');
    });
    if (!optionButton || optionButton.disabled) {
      return;
    }
    optionButton.classList.add('is-active');
    optionButton.setAttribute('tabindex', '0');
    if (shouldFocus) {
      optionButton.focus();
      if (typeof optionButton.scrollIntoView === 'function') {
        optionButton.scrollIntoView({
          block: 'nearest'
        });
      }
    }
  }
  function moveActiveOption(menu, step) {
    var buttons = getEnabledButtons(menu);
    if (!buttons.length) {
      return null;
    }
    var active = menu.querySelector('.rg-listbox-option-btn.is-active');
    var currentIndex = buttons.indexOf(active);
    if (currentIndex < 0) {
      currentIndex = 0;
    }
    var nextIndex = currentIndex + step;
    if (nextIndex < 0) {
      nextIndex = buttons.length - 1;
    }
    if (nextIndex >= buttons.length) {
      nextIndex = 0;
    }
    setActiveOption(menu, buttons[nextIndex], true);
    return buttons[nextIndex];
  }
  function closeSelect(wrapper) {
    wrapper.classList.remove('is-open');
    var trigger = wrapper.querySelector('[data-select-trigger]');
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'false');
    }
  }
  function closeAll(exceptWrapper) {
    wrappers.forEach(function (wrapper) {
      if (exceptWrapper && wrapper === exceptWrapper) {
        return;
      }
      closeSelect(wrapper);
    });
  }
  function openSelect(wrapper, trigger, menu, select) {
    closeAll(wrapper);
    wrapper.classList.add('is-open');
    trigger.setAttribute('aria-expanded', 'true');
    var buttons = getEnabledButtons(menu);
    if (!buttons.length) {
      return;
    }
    var selectedButton = buttons.find(function (button) {
      return button.getAttribute('data-option-value') === select.value;
    }) || buttons[0];
    setActiveOption(menu, selectedButton, true);
  }
  function commitOption(optionButton, select, wrapper) {
    if (!optionButton || optionButton.disabled) {
      return;
    }
    select.value = optionButton.getAttribute('data-option-value') || '';
    select.dispatchEvent(new Event('change', {
      bubbles: true
    }));
    closeAll();
    var trigger = wrapper.querySelector('[data-select-trigger]');
    if (trigger) {
      trigger.focus();
    }
  }
  function refreshSelection(select, menu, currentText) {
    var selectedOption = select.options[select.selectedIndex];
    var fallback = select.getAttribute('data-placeholder') || '';
    currentText.textContent = selectedOption ? selectedOption.textContent : fallback;
    menu.querySelectorAll('.rg-listbox-option-btn').forEach(function (button) {
      var value = button.getAttribute('data-option-value');
      button.classList.toggle('is-selected', value === select.value);
    });
  }
  function buildOptionButton(option, select, menu, currentText, wrapper, index) {
    var item = document.createElement('li');
    item.className = 'rg-listbox-option';
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'rg-listbox-option-btn';
    button.textContent = option.textContent || '';
    button.id = select.id + '-option-' + index;
    button.setAttribute('role', 'option');
    button.setAttribute('data-option-value', option.value);
    if (option.disabled) {
      button.disabled = true;
    }
    if (option.selected) {
      button.classList.add('is-selected');
      currentText.textContent = option.textContent || '';
    }
    button.addEventListener('mouseenter', function () {
      setActiveOption(menu, button, false);
    });
    button.addEventListener('focus', function () {
      setActiveOption(menu, button, false);
    });
    button.addEventListener('click', function () {
      commitOption(button, select, wrapper);
    });
    item.appendChild(button);
    menu.appendChild(item);
  }
  wrappers.forEach(function (wrapper) {
    var select = wrapper.querySelector('select.js-rg-listbox-native');
    var trigger = wrapper.querySelector('[data-select-trigger]');
    var currentText = wrapper.querySelector('[data-select-current]');
    var menu = wrapper.querySelector('[data-select-menu]');
    if (!select || !trigger || !currentText || !menu) {
      return;
    }
    select.classList.add('is-enhanced');
    menu.innerHTML = '';
    trigger.setAttribute('aria-controls', select.id + '-custom-menu');
    menu.id = select.id + '-custom-menu';
    Array.prototype.forEach.call(select.options, function (option, index) {
      buildOptionButton(option, select, menu, currentText, wrapper, index);
    });
    refreshSelection(select, menu, currentText);
    trigger.addEventListener('click', function () {
      if (wrapper.classList.contains('is-open')) {
        closeSelect(wrapper);
        return;
      }
      openSelect(wrapper, trigger, menu, select);
    });
    trigger.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        if (!wrapper.classList.contains('is-open')) {
          openSelect(wrapper, trigger, menu, select);
        }
        moveActiveOption(menu, event.key === 'ArrowDown' ? 1 : -1);
        return;
      }
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        if (!wrapper.classList.contains('is-open')) {
          openSelect(wrapper, trigger, menu, select);
          return;
        }
        commitOption(menu.querySelector('.rg-listbox-option-btn.is-active'), select, wrapper);
      }
      if (event.key === 'Escape') {
        closeSelect(wrapper);
      }
    });
    select.addEventListener('change', function () {
      refreshSelection(select, menu, currentText);
    });
    menu.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        moveActiveOption(menu, event.key === 'ArrowDown' ? 1 : -1);
        return;
      }
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        commitOption(menu.querySelector('.rg-listbox-option-btn.is-active'), select, wrapper);
        return;
      }
      if (event.key === 'Escape') {
        closeSelect(wrapper);
        trigger.focus();
      }
    });
  });
  document.addEventListener('click', function (event) {
    if (!event.target.closest('[data-listbox-select]')) {
      closeAll();
    }
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeAll();
    }
  });
})();

/***/ }),

/***/ 10:
/*!********************************************************!*\
  !*** multi ./resources/js/registrar-listbox-select.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! c:\Users\micha\Desktop\OJT\plp-demo\resources\js\registrar-listbox-select.js */"./resources/js/registrar-listbox-select.js");


/***/ })

/******/ });