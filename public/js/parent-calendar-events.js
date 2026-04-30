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
/******/ 	return __webpack_require__(__webpack_require__.s = 18);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/parent-calendar-events.js":
/*!************************************************!*\
  !*** ./resources/js/parent-calendar-events.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

document.addEventListener('DOMContentLoaded', function () {
  var calendarRoot = document.getElementById('parentCalendarPage');
  var calendarMonthYear = document.getElementById('calendarMonthYear');
  var calendarBody = document.getElementById('calendarBody');
  var prevMonthBtn = document.getElementById('prevMonthBtn');
  var nextMonthBtn = document.getElementById('nextMonthBtn');
  var todayBtn = document.getElementById('todayBtn');
  if (!calendarRoot || !calendarMonthYear || !calendarBody || !prevMonthBtn || !nextMonthBtn || !todayBtn) {
    return;
  }
  var currentDate = new Date();
  var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  function parseEventsPayload() {
    var payload = calendarRoot.getAttribute('data-calendar-events');
    if (!payload) {
      return [];
    }
    try {
      var parsed = JSON.parse(payload);
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      return [];
    }
  }
  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (ch) {
      var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      };
      return map[ch] || ch;
    });
  }
  var dbEvents = parseEventsPayload();
  var calendarEvents = {};
  dbEvents.forEach(function (item) {
    if (!item || !item.date || !item.label) {
      return;
    }
    var key = String(item.date);
    if (!Array.isArray(calendarEvents[key])) {
      calendarEvents[key] = [];
    }
    calendarEvents[key].push({
      type: item.type === 'holiday' ? 'holiday' : 'event',
      label: String(item.label)
    });
  });
  function dateKey(year, month, day) {
    return year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
  }
  function renderCalendar(date) {
    var year = date.getFullYear();
    var month = date.getMonth();
    calendarMonthYear.textContent = monthNames[month] + ' ' + year;
    var firstDay = new Date(year, month, 1).getDay();
    var daysInMonth = new Date(year, month + 1, 0).getDate();
    calendarBody.innerHTML = '';
    var dateCount = 1;
    for (var i = 0; i < 6; i++) {
      var row = document.createElement('tr');
      var hasCells = false;
      for (var j = 0; j < 7; j++) {
        var cell = document.createElement('td');
        cell.classList.add('cal-td');
        if (i === 0 && j < firstDay || dateCount > daysInMonth) {
          cell.classList.add('empty-cell');
        } else {
          var html = '<div class="day-number">' + dateCount + '</div>';
          var key = dateKey(year, month, dateCount);
          var eventsForDate = Array.isArray(calendarEvents[key]) ? calendarEvents[key] : [];
          if (eventsForDate.length > 0) {
            var hasHoliday = eventsForDate.some(function (eventItem) {
              return eventItem.type === 'holiday';
            });
            var hasRegularEvent = eventsForDate.some(function (eventItem) {
              return eventItem.type === 'event';
            });
            if (hasHoliday && hasRegularEvent) {
              cell.classList.add('mixed-event-cell');
            } else if (hasHoliday) {
              cell.classList.add('holiday-cell');
            } else {
              cell.classList.add('event-cell');
            }
            html += '<span class="cal-event-label">' + escapeHtml(eventsForDate[0].label) + '</span>';
            if (eventsForDate.length > 1) {
              html += '<span class="cal-event-more">+' + (eventsForDate.length - 1) + ' more</span>';
            }
            cell.dataset.eventItems = JSON.stringify(eventsForDate);
          }
          cell.innerHTML = html;
          var today = new Date();
          if (dateCount === today.getDate() && year === today.getFullYear() && month === today.getMonth()) {
            cell.classList.add('today-cell');
          }
          dateCount++;
          hasCells = true;
        }
        row.appendChild(cell);
      }
      if (hasCells) {
        calendarBody.appendChild(row);
      }
    }
  }
  prevMonthBtn.addEventListener('click', function () {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar(currentDate);
  });
  nextMonthBtn.addEventListener('click', function () {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar(currentDate);
  });
  todayBtn.addEventListener('click', function () {
    currentDate = new Date();
    renderCalendar(currentDate);
  });
  renderCalendar(currentDate);
  var activePopup = null;
  function closeEventPopup() {
    if (activePopup) {
      activePopup.remove();
      activePopup = null;
    }
  }
  function isMobileView() {
    return window.innerWidth <= 991;
  }
  calendarBody.addEventListener('click', function (event) {
    if (!isMobileView()) {
      return;
    }
    var cell = event.target.closest('.cal-td');
    if (!cell || !cell.dataset.eventItems) {
      return;
    }
    if (activePopup && activePopup._cell === cell) {
      closeEventPopup();
      return;
    }
    closeEventPopup();
    var items = [];
    try {
      items = JSON.parse(cell.dataset.eventItems || '[]');
    } catch (error) {
      items = [];
    }
    if (!Array.isArray(items) || items.length === 0) {
      return;
    }
    var eventRows = items.map(function (item) {
      var eventType = item && item.type === 'holiday' ? 'holiday' : 'event';
      var eventTypeLabel = eventType === 'holiday' ? 'Holiday' : 'University Event';
      return '<div class="cal-popup-item">' + '<div class="cal-popup-type popup-' + eventType + '">' + '<span class="popup-dot"></span>' + eventTypeLabel + '</div>' + '<div class="cal-popup-label">' + escapeHtml(item && item.label ? item.label : '') + '</div>' + '</div>';
    }).join('');
    var popup = document.createElement('div');
    popup.className = 'cal-event-popup';
    popup._cell = cell;
    popup.innerHTML = '<div class="cal-popup-list">' + eventRows + '</div>';
    document.body.appendChild(popup);
    var rect = cell.getBoundingClientRect();
    var popupRect = popup.getBoundingClientRect();
    var left = rect.left + rect.width / 2 - popupRect.width / 2;
    var top = rect.bottom + 8;
    if (left < 8) {
      left = 8;
    }
    if (left + popupRect.width > window.innerWidth - 8) {
      left = window.innerWidth - popupRect.width - 8;
    }
    if (top + popupRect.height > window.innerHeight - 8) {
      top = rect.top - popupRect.height - 8;
      popup.style.transformOrigin = 'bottom center';
    }
    popup.style.left = left + 'px';
    popup.style.top = top + 'px';
    activePopup = popup;
  });
  document.addEventListener('click', function (event) {
    if (!activePopup) {
      return;
    }
    if (activePopup.contains(event.target)) {
      return;
    }
    if (event.target.closest('.cal-td[data-event-items]')) {
      return;
    }
    closeEventPopup();
  });
  window.addEventListener('scroll', closeEventPopup, true);
  window.addEventListener('resize', closeEventPopup);
});

/***/ }),

/***/ 18:
/*!******************************************************!*\
  !*** multi ./resources/js/parent-calendar-events.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\xampp\htdocs\plp-demo\resources\js\parent-calendar-events.js */"./resources/js/parent-calendar-events.js");


/***/ })

/******/ });