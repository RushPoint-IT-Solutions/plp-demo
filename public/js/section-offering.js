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

/***/ "./resources/js/section-offering.js":
/*!******************************************!*\
  !*** ./resources/js/section-offering.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
document.addEventListener('DOMContentLoaded', function () {
  var page = document.getElementById('sectionOfferingPage');
  if (!page) {
    return;
  }
  var FETCH_URL = page.getAttribute('data-fetch-url') || '';
  var STORE_URL = page.getAttribute('data-store-url') || '';
  var CURRICULUM_URL = page.getAttribute('data-curriculum-url') || '';
  var DEFAULT_PER_PAGE = 10;
  var soSY = document.getElementById('soSY');
  var soTerm = document.getElementById('soTerm');
  var soYearLevel = document.getElementById('soYearLevel');
  var soSection = document.getElementById('soSection');
  var soProgram = document.getElementById('soProgram');
  var soSectionSearch = document.getElementById('soSectionSearch');
  var soSectionListBody = document.getElementById('soSectionListBody');
  var soSectionPageText = document.getElementById('soSectionPageText');
  var soSectionListCard = document.getElementById('soSectionListCard');
  var soPrevBtn = document.getElementById('soPrevBtn');
  var soNextBtn = document.getElementById('soNextBtn');
  var soPageNumbers = document.getElementById('soPageNumbers');
  var soSectionPaginationBar = document.getElementById('soSectionPaginationBar');
  var soCard = document.getElementById('soCard');
  var soCardTitle = document.getElementById('soCardTitle');
  var soBody = document.getElementById('soBody');
  var soPageText = document.getElementById('soPageText');
  var soWeekly = document.getElementById('soWeekly');
  var soWeeklyGrid = document.getElementById('soWeeklyGrid');
  var soBackToDirectory = document.getElementById('soBackToDirectory');
  var soPrintButton = page.querySelector('.so-print-btn');
  var soOpenAddSection = document.getElementById('soOpenAddSection');
  var soAddSectionModal = document.getElementById('soAddSectionModal');
  var soCloseAddSection = document.getElementById('soCloseAddSection');
  var soCancelAddSection = document.getElementById('soCancelAddSection');
  var soSaveAddSection = document.getElementById('soSaveAddSection');
  var soModalProgram = document.getElementById('soModalProgram');
  var soModalSY = document.getElementById('soModalSY');
  var soModalTerm = document.getElementById('soModalTerm');
  var soModalYearLevel = document.getElementById('soModalYearLevel');
  var soModalSection = document.getElementById('soModalSection');
  var soModalSlots = document.getElementById('soModalSlots');
  var soModalAdviser = document.getElementById('soModalAdviser');
  var soModalDescription = document.getElementById('soModalDescription');
  var soCurriculumAvailable = document.getElementById('soCurriculumAvailable');
  var soCurriculumIncluded = document.getElementById('soCurriculumIncluded');
  var soCurriculumAdd = document.getElementById('soCurriculumAdd');
  var soCurriculumAddAll = document.getElementById('soCurriculumAddAll');
  var soCurriculumRemove = document.getElementById('soCurriculumRemove');
  var soCurriculumRemoveAll = document.getElementById('soCurriculumRemoveAll');
  var soCurriculumSummary = document.getElementById('soCurriculumSummary');
  var soModalFeedback = document.getElementById('soModalFeedback');
  var soSectionConfigModal = document.getElementById('soSectionConfigModal');
  var soSectionConfigClose = document.getElementById('soSectionConfigClose');
  var soSectionConfigDone = document.getElementById('soSectionConfigDone');
  var soSectionConfigTitle = document.getElementById('soSectionConfigTitle');
  var soSectionConfigMeta = document.getElementById('soSectionConfigMeta');
  var soSectionConfigBody = document.getElementById('soSectionConfigBody');
  var soSubjectScheduleModal = document.getElementById('soSubjectScheduleModal');
  var soSubjectScheduleTitle = document.getElementById('soSubjectScheduleTitle');
  var soSubjectScheduleTag = document.getElementById('soSubjectScheduleTag');
  var soSubjectScheduleClose = document.getElementById('soSubjectScheduleClose');
  var soSubjectScheduleCancel = document.getElementById('soSubjectScheduleCancel');
  var soSubjectScheduleSave = document.getElementById('soSubjectScheduleSave');
  var soSubjectScheduleRows = document.getElementById('soSubjectScheduleRows');
  var soSubjectScheduleFeedback = document.getElementById('soSubjectScheduleFeedback');
  var soSubjectAddScheduleRow = document.getElementById('soSubjectAddScheduleRow');
  var soSubjectSectionCode = document.getElementById('soSubjectSectionCode');
  var soSubjectDescription = document.getElementById('soSubjectDescription');
  var soSubjectProfessor = document.getElementById('soSubjectProfessor');
  var soSubjectSlots = document.getElementById('soSubjectSlots');
  var soSubjectFlagOpen = document.getElementById('soSubjectFlagOpen');
  var soSubjectFlagBlock = document.getElementById('soSubjectFlagBlock');
  var soSubjectFlagTutorial = document.getElementById('soSubjectFlagTutorial');
  var scheduleDays = [{
    key: 'M',
    label: 'Monday'
  }, {
    key: 'T',
    label: 'Tuesday'
  }, {
    key: 'W',
    label: 'Wednesday'
  }, {
    key: 'TH',
    label: 'Thursday'
  }, {
    key: 'F',
    label: 'Friday'
  }, {
    key: 'S',
    label: 'Saturday'
  }, {
    key: 'SU',
    label: 'Sunday'
  }];
  var state = {
    sections: [],
    selectedSectionId: null,
    requestToken: 0,
    page: 1,
    lastPage: 1,
    perPage: DEFAULT_PER_PAGE,
    totalSections: 0,
    totalSubjects: 0,
    visibleSections: 0,
    isLoading: false,
    searchTimers: {},
    lastSearchValue: '',
    curriculumRows: [],
    curriculumIncludedIds: [],
    curriculumRequestToken: 0,
    saveInProgress: false,
    sectionConfigSectionId: null,
    subjectEditor: null,
    subjectRowSeed: 0
  };
  function escapeHtml(value) {
    return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function normalizeText(value) {
    return String(value || '').trim();
  }
  function normalizeLower(value) {
    return normalizeText(value).toLowerCase();
  }
  function toInt(value, fallback) {
    var parsed = parseInt(String(value), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  }
  function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
  }
  function getPayloadErrorMessage(payload, fallbackMessage) {
    if (payload && payload.errors && _typeof(payload.errors) === 'object') {
      var firstKey = Object.keys(payload.errors)[0];
      if (firstKey && payload.errors[firstKey] && payload.errors[firstKey][0]) {
        return payload.errors[firstKey][0];
      }
    }
    if (payload && payload.message) {
      return payload.message;
    }
    return fallbackMessage;
  }
  function showMessage(message, type) {
    if (typeof showRegistrarToast === 'function') {
      showRegistrarToast(message, type || 'info');
      return;
    }
    if (type === 'success') {
      return;
    }
    alert(message);

  function isAllowedSchoolYear(value) {
    var normalized = normalizeText(value);
    if (normalized === '') {
      return false;
    }
    var selectElement = soModalSY || soSY;
    if (selectElement && selectElement.options && selectElement.options.length) {
      for (var i = 0; i < selectElement.options.length; i += 1) {
        if (normalizeText(selectElement.options[i].value) === normalized) {
          return true;
        }
      }
    }
    return /^\d{4}-\d{4}$/.test(normalized);
  }
  }
  function fetchJson(url, options) {
    var requestOptions = {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    };
    if (options && _typeof(options) === 'object') {
      requestOptions = Object.assign({}, requestOptions, options);
      requestOptions.headers = Object.assign({}, {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }, options.headers || {});
    }
    return fetch(url, requestOptions).then(function (response) {
      return response.json()["catch"](function () {
        return {};
      }).then(function (payload) {
        if (!response.ok) {
          throw payload;
        }
        return payload;
      });
    });
  }
  function emitListboxRefresh(selectElement) {
    if (!selectElement) {
      return;
    }
    var detail = {
      target: selectElement
    };
    if (typeof window.CustomEvent === 'function') {
      document.dispatchEvent(new CustomEvent('registrar:listbox:refresh', {
        detail: detail
      }));
      return;
    }
    var legacyEvent = document.createEvent('CustomEvent');
    legacyEvent.initCustomEvent('registrar:listbox:refresh', true, true, detail);
    document.dispatchEvent(legacyEvent);
  }
  function setSelectOptions(selectElement, items, placeholderLabel, valueResolver, labelResolver) {
    if (!selectElement) {
      return;
    }
    var currentValue = selectElement.value;
    var list = Array.isArray(items) ? items : [];
    var html = ['<option value="">' + escapeHtml(placeholderLabel) + '</option>'];
    list.forEach(function (item) {
      var optionValue = valueResolver(item);
      var optionLabel = labelResolver(item);
      if (!optionValue) {
        return;
      }
      html.push('<option value="' + escapeHtml(optionValue) + '">' + escapeHtml(optionLabel) + '</option>');
    });
    selectElement.innerHTML = html.join('');
    var shouldKeepValue = list.some(function (item) {
      return valueResolver(item) === currentValue;
    });
    selectElement.value = shouldKeepValue ? currentValue : '';
    emitListboxRefresh(selectElement);
  }
  function mapCourseLabel(value) {
    if (!value) {
      return '';
    }
    var label = normalizeText(value.label);
    if (label !== '') {
      return label;
    }
    var code = normalizeText(value.code);
    var name = normalizeText(value.name);
    if (code !== '' && name !== '') {
      return code + ' - ' + name;
    }
    return code !== '' ? code : name;
  }
  function syncFilterOptions(options) {
    options = options || {};
    setSelectOptions(soSY, options.school_years, 'All School Years', function (value) {
      return normalizeText(value);
    }, function (value) {
      return normalizeText(value);
    });
    setSelectOptions(soTerm, options.semesters, 'All Semesters', function (value) {
      return normalizeText(value);
    }, function (value) {
      return normalizeText(value);
    });
    var yearLevelList = Array.isArray(options.year_levels) && options.year_levels.length ? options.year_levels : ['First', 'Second', 'Third', 'Fourth'];
    setSelectOptions(soYearLevel, yearLevelList, 'All Year Levels', function (value) {
      return normalizeText(value);
    }, function (value) {
      return normalizeText(value);
    });
    setSelectOptions(soSection, options.sections, 'All Sections', function (value) {
      return normalizeText(value);
    }, function (value) {
      return normalizeText(value);
    });
    setSelectOptions(soProgram, options.courses, 'All Courses', function (value) {
      return normalizeText(value && value.id ? value.id : '');
    }, mapCourseLabel);
    setSelectOptions(soModalProgram, options.courses, 'Select Course', function (value) {
      return normalizeText(value && value.id ? value.id : '');
    }, mapCourseLabel);
  }
  function buildFetchUrl(pageValue) {
    var params = ['page=' + encodeURIComponent(String(pageValue)), 'per_page=' + encodeURIComponent(String(DEFAULT_PER_PAGE))];
    if (soSY && soSY.value) {
      params.push('school_year=' + encodeURIComponent(soSY.value));
    }
    if (soTerm && soTerm.value) {
      params.push('semester=' + encodeURIComponent(soTerm.value));
    }
    if (soYearLevel && soYearLevel.value) {
      params.push('year_level=' + encodeURIComponent(soYearLevel.value));
    }
    if (soSection && soSection.value) {
      params.push('section=' + encodeURIComponent(soSection.value));
    }
    if (soProgram && soProgram.value) {
      params.push('course_id=' + encodeURIComponent(soProgram.value));
    }
    var searchText = soSectionSearch ? normalizeText(soSectionSearch.value) : '';
    if (searchText.length >= 2) {
      params.push('search=' + encodeURIComponent(searchText));
    }
    return FETCH_URL + (FETCH_URL.indexOf('?') === -1 ? '?' : '&') + params.join('&');
  }
  function parseScheduleLine(line) {
    var text = normalizeText(line);
    var matched = text.match(/^(M|T|W|TH|F|S|SU)\s+([^\s]+)\s+(.+)$/i);
    if (!matched) {
      return {
        day: '',
        time: text,
        room: ''
      };
    }
    return {
      day: matched[1].toUpperCase(),
      time: matched[2],
      room: matched[3]
    };
  }
  function parseTimeRangeToken(value) {
    var token = normalizeText(value);
    if (token === '') {
      return {
        from: '',
        to: ''
      };
    }
    var separatorIndex = token.indexOf('-');
    if (separatorIndex === -1) {
      return {
        from: token,
        to: ''
      };
    }
    return {
      from: normalizeText(token.slice(0, separatorIndex)),
      to: normalizeText(token.slice(separatorIndex + 1))
    };
  }
  function getDayLabel(dayKey) {
    var label = dayKey;
    scheduleDays.some(function (day) {
      if (day.key === dayKey) {
        label = day.label;
        return true;
      }
      return false;
    });
    return label;
  }
  function buildSubjectScheduleLookup(subject) {
    var lookup = {};
    (subject.schedules || []).forEach(function (line) {
      var parsed = parseScheduleLine(line);
      if (!parsed.day) {
        return;
      }
      var dayKey = normalizeText(parsed.day).toUpperCase();
      var existing = lookup[dayKey] || {
        from: '',
        to: '',
        room: ''
      };
      var range = parseTimeRangeToken(parsed.time);
      if (existing.from === '' && range.from !== '') {
        existing.from = range.from;
      }
      if (existing.to === '' && range.to !== '') {
        existing.to = range.to;
      }
      if (existing.room === '' && normalizeText(parsed.room) !== '') {
        existing.room = normalizeText(parsed.room);
      }
      lookup[dayKey] = existing;
    });
    return lookup;
  }
  function getSectionLabel(section) {
    var program = normalizeText(section.program);
    var sectionCode = normalizeText(section.section);
    var yearLevel = normalizeText(section.yearLevel);
    if (!program && !sectionCode) {
      return 'N/A';
    }
    if (!yearLevel) {
      return program + ' - ' + sectionCode;
    }
    return program + ' ' + yearLevel.charAt(0) + '-' + sectionCode;
  }
  function getSectionById(sectionId) {
    var matched = null;
    state.sections.some(function (entry) {
      if (String(entry.id) === String(sectionId)) {
        matched = entry;
        return true;
      }
      return false;
    });
    return matched;
  }
  function showDirectoryView() {
    if (soSectionListCard) {
      soSectionListCard.style.display = '';
    }
  }
  function showDetailsView() {
    if (soSectionListCard) {
      soSectionListCard.style.display = 'none';
    }
  }
  function clearSectionDetails() {
    if (soCard) {
      soCard.style.display = 'none';
    }
    if (soWeekly) {
      soWeekly.style.display = 'none';
    }
    if (soBody) {
      soBody.innerHTML = '';
    }
    if (soWeeklyGrid) {
      soWeeklyGrid.innerHTML = '';
    }
    showDirectoryView();
  }
  function renderWeeklyGrid(section) {
    if (!soWeeklyGrid) {
      return;
    }
    var sectionLabel = getSectionLabel(section);
    var bucket = {
      M: [],
      T: [],
      W: [],
      TH: [],
      F: [],
      S: [],
      SU: []
    };
    (section.subjects || []).forEach(function (subjectRow) {
      (subjectRow.schedules || []).forEach(function (line) {
        var parsed = parseScheduleLine(line);
        if (!parsed.day || !bucket[parsed.day]) {
          return;
        }
        bucket[parsed.day].push({
          code: subjectRow.code || '-',
          section: sectionLabel,
          time: parsed.time || 'TBA',
          room: parsed.room || 'TBA'
        });
      });
    });
    soWeeklyGrid.innerHTML = scheduleDays.map(function (day) {
      var cards = bucket[day.key] || [];
      var bodyHtml = '';
      if (!cards.length) {
        bodyHtml = '<div class="so-weekly-empty">No class</div>';
      } else {
        bodyHtml = cards.map(function (card) {
          return '' + '<div class="so-weekly-card">' + '<div class="so-weekly-code">' + escapeHtml(card.code) + '</div>' + '<div class="so-weekly-section">' + escapeHtml(card.section) + '</div>' + '<div class="so-weekly-time">' + escapeHtml(card.time) + '</div>' + '<div class="so-weekly-room">' + escapeHtml(card.room) + '</div>' + '</div>';
        }).join('');
      }
      return '' + '<div class="so-weekly-col">' + '<div class="so-weekly-day">' + escapeHtml(day.label) + '</div>' + '<div class="so-weekly-body">' + bodyHtml + '</div>' + '</div>';
    }).join('');
  }
  function renderSectionDetails(section) {
    if (!section || !soBody || !soWeeklyGrid) {
      return;
    }
    if (soCard) {
      soCard.style.display = '';
    }
    if (soWeekly) {
      soWeekly.style.display = '';
    }
    if (soCardTitle) {
      soCardTitle.textContent = 'Section Offering: ' + (section.section || 'N/A');
    }
    showDetailsView();
    var sectionLabel = getSectionLabel(section);
    var rows = section.subjects || [];
    if (!rows.length) {
      soBody.innerHTML = '<tr><td colspan="11" class="so-empty-row">No subjects are assigned to this section yet.</td></tr>';
    } else {
      soBody.innerHTML = rows.map(function (item, index) {
        var scheduleLines = (item.schedules || []).map(function (line) {
          return '<div class="so-schedule-line">' + escapeHtml(line) + '</div>';
        }).join('');
        return '' + '<tr class="so-subject-row" data-so-subject-index="' + index + '">' + '<td>' + escapeHtml(item.code || '-') + '</td>' + '<td>' + escapeHtml(item.description || '-') + '</td>' + '<td>' + escapeHtml(item.lec || 0) + '</td>' + '<td>' + escapeHtml(item.lab || 0) + '</td>' + '<td>' + escapeHtml(item.tuitionUnits || 0) + '</td>' + '<td>' + escapeHtml(item.creditUnits || 0) + '</td>' + '<td>' + escapeHtml(sectionLabel) + '</td>' + '<td>' + escapeHtml(item.room || 'TBA') + '</td>' + '<td>' + escapeHtml(item.professor || 'TBA') + '</td>' + '<td>' + escapeHtml(item.slots || 0) + '</td>' + '<td class="so-schedule-cell">' + (scheduleLines || '<div class="so-schedule-line">-</div>') + '</td>' + '</tr>';
      }).join('');
    }
    if (soPageText) {
      soPageText.textContent = 'Showing ' + rows.length + ' subject' + (rows.length === 1 ? '' : 's');
    }
    renderWeeklyGrid(section);
  }
  function getNextSubjectRowId() {
    state.subjectRowSeed += 1;
    return String(state.subjectRowSeed);
  }
  function buildScheduleRowHtml(entry) {
    var dayValue = normalizeText(entry && entry.day || 'M').toUpperCase();
    var dayOptions = scheduleDays.map(function (day) {
      return '<option value="' + escapeHtml(day.key) + '"' + (day.key === dayValue ? ' selected' : '') + '>' + escapeHtml(day.label) + '</option>';
    }).join('');
    return '' + '<tr data-so-row-id="' + escapeHtml(getNextSubjectRowId()) + '">' + '<td><select class="app-filter-input so-subject-day">' + dayOptions + '</select></td>' + '<td class="so-subject-check-col"><input type="checkbox" class="so-subject-enabled" ' + (entry && entry.enabled !== false ? 'checked' : '') + '></td>' + '<td><input type="text" class="app-filter-input so-subject-time-from" value="' + escapeHtml(entry && entry.from || '') + '" placeholder="07:00AM"></td>' + '<td><input type="text" class="app-filter-input so-subject-time-to" value="' + escapeHtml(entry && entry.to || '') + '" placeholder="08:30AM"></td>' + '<td><input type="text" class="app-filter-input so-subject-room" value="' + escapeHtml(entry && entry.room || '') + '" placeholder="-select room-"></td>' + '<td class="so-subject-check-col"><input type="checkbox" class="so-subject-lab" ' + (entry && entry.lab ? 'checked' : '') + '></td>' + '<td class="so-subject-row-actions">' + '<button type="button" class="so-subject-row-btn so-subject-row-add" data-so-subject-row-action="add" aria-label="Add row">+</button>' + '<button type="button" class="so-subject-row-btn so-subject-row-remove" data-so-subject-row-action="remove" aria-label="Remove row">&times;</button>' + '</td>' + '</tr>';
  }
  function buildInitialSubjectRows(subject) {
    var grouped = {
      M: [],
      T: [],
      W: [],
      TH: [],
      F: [],
      S: [],
      SU: []
    };
    (subject.schedules || []).forEach(function (line) {
      var parsed = parseScheduleLine(line);
      if (!parsed.day || !grouped[parsed.day]) {
        return;
      }
      var range = parseTimeRangeToken(parsed.time || '');
      grouped[parsed.day].push({
        day: parsed.day,
        enabled: true,
        from: range.from,
        to: range.to,
        room: normalizeText(parsed.room || ''),
        lab: false
      });
    });
    var rows = [];
    scheduleDays.forEach(function (day) {
      var dayRows = grouped[day.key] || [];
      if (dayRows.length) {
        rows.push(dayRows.shift());
      } else {
        rows.push({
          day: day.key,
          enabled: false,
          from: '',
          to: '',
          room: '',
          lab: false
        });
      }
      dayRows.forEach(function (extraRow) {
        rows.push(extraRow);
      });
    });
    return rows;
  }
  function appendSubjectScheduleRow(entry) {
    if (!soSubjectScheduleRows) {
      return;
    }
    soSubjectScheduleRows.insertAdjacentHTML('beforeend', buildScheduleRowHtml(entry || {
      day: 'M',
      enabled: true,
      from: '',
      to: '',
      room: '',
      lab: false
    }));
  }
  function insertSubjectScheduleRowAfter(targetRow, entry) {
    if (!soSubjectScheduleRows) {
      return;
    }
    var html = buildScheduleRowHtml(entry || {
      day: 'M',
      enabled: true,
      from: '',
      to: '',
      room: '',
      lab: false
    });
    if (!targetRow) {
      soSubjectScheduleRows.insertAdjacentHTML('beforeend', html);
      return;
    }
    targetRow.insertAdjacentHTML('afterend', html);
  }
  function setSubjectScheduleFeedback(message, isError) {
    if (!soSubjectScheduleFeedback) {
      return;
    }
    var text = normalizeText(message);
    soSubjectScheduleFeedback.textContent = text;
    soSubjectScheduleFeedback.classList.toggle('is-error', !!isError);
    soSubjectScheduleFeedback.style.display = text !== '' ? 'block' : 'none';
  }
  function renderSectionConfigModal(section) {
    if (!section || !soSectionConfigBody) {
      return;
    }
    if (soSectionConfigTitle) {
      soSectionConfigTitle.textContent = 'Section Offering Configuration - ' + normalizeText(section.section || 'N/A');
    }
    if (soSectionConfigMeta) {
      var metaRows = [{
        label: 'Course',
        value: normalizeText(section.program) || '-'
      }, {
        label: 'Section',
        value: normalizeText(section.section) || '-'
      }, {
        label: 'School Year',
        value: normalizeText(section.schoolYear) || '-'
      }, {
        label: 'Semester',
        value: normalizeText(section.semester) || '-'
      }, {
        label: 'Year Level',
        value: normalizeText(section.yearLevel) || '-'
      }, {
        label: 'Adviser',
        value: normalizeText(section.adviser) || 'TBA'
      }, {
        label: 'Slots',
        value: String(section.slots || 0)
      }, {
        label: 'Subjects',
        value: String((section.subjects || []).length)
      }];
      soSectionConfigMeta.innerHTML = metaRows.map(function (metaItem) {
        return '' + '<div class="so-config-meta-item">' + '<div class="so-config-meta-label">' + escapeHtml(metaItem.label) + '</div>' + '<div class="so-config-meta-value">' + escapeHtml(metaItem.value) + '</div>' + '</div>';
      }).join('');
    }
    var rows = section.subjects || [];
    if (!rows.length) {
      soSectionConfigBody.innerHTML = '<tr><td colspan="7" class="so-empty-row">No subjects are assigned to this section yet.</td></tr>';
      return;
    }
    soSectionConfigBody.innerHTML = rows.map(function (item, index) {
      var scheduleLines = (item.schedules || []).map(function (line) {
        return '<div class="so-config-schedule-line">' + escapeHtml(line) + '</div>';
      }).join('');
      return '' + '<tr>' + '<td>' + escapeHtml(item.code || '-') + '</td>' + '<td>' + escapeHtml(item.description || '-') + '</td>' + '<td>' + escapeHtml(item.room || 'TBA') + '</td>' + '<td>' + escapeHtml(item.professor || 'TBA') + '</td>' + '<td>' + escapeHtml(item.slots || 0) + '</td>' + '<td class="so-config-schedule">' + (scheduleLines || '<div class="so-config-schedule-line">-</div>') + '</td>' + '<td class="so-config-actions">' + '<button type="button" class="so-config-action" data-so-config-action="configure" data-so-section-id="' + escapeHtml(section.id) + '" data-so-subject-index="' + index + '">Configure</button>' + '<button type="button" class="so-config-action so-config-action-edit" data-so-config-action="edit" data-so-section-id="' + escapeHtml(section.id) + '" data-so-subject-index="' + index + '">Edit</button>' + '</td>' + '</tr>';
    }).join('');
  }
  function openSectionConfigModal(section) {
    if (!section || !soSectionConfigModal) {
      return;
    }
    state.sectionConfigSectionId = section.id;
    renderSectionConfigModal(section);
    soSectionConfigModal.classList.add('is-open');
    soSectionConfigModal.setAttribute('aria-hidden', 'false');
  }
  function closeSectionConfigModal() {
    if (!soSectionConfigModal) {
      return;
    }
    soSectionConfigModal.classList.remove('is-open');
    soSectionConfigModal.setAttribute('aria-hidden', 'true');
    state.sectionConfigSectionId = null;
  }
  function renderSubjectScheduleRows(subject) {
    if (!soSubjectScheduleRows) {
      return;
    }
    state.subjectRowSeed = 0;
    var rows = buildInitialSubjectRows(subject || {});
    soSubjectScheduleRows.innerHTML = rows.map(function (entry) {
      return buildScheduleRowHtml(entry);
    }).join('');
  }
  function openSubjectScheduleModal(sectionId, subjectIndex) {
    if (!soSubjectScheduleModal) {
      return;
    }
    var section = getSectionById(sectionId);
    var parsedIndex = toInt(subjectIndex, -1);
    if (!section || !Array.isArray(section.subjects) || parsedIndex < 0 || parsedIndex >= section.subjects.length) {
      return;
    }
    var subject = section.subjects[parsedIndex];
    state.subjectEditor = {
      sectionId: section.id,
      subjectIndex: parsedIndex
    };
    if (soSubjectScheduleTitle) {
      soSubjectScheduleTitle.textContent = (subject.code || '-') + ' - ' + (subject.description || '-');
    }
    if (soSubjectScheduleTag) {
      soSubjectScheduleTag.textContent = (subject.code || '-') + ' - ' + (subject.description || '-') + ' (' + (section.section || 'N/A') + ')';
    }
    if (soSubjectSectionCode) {
      soSubjectSectionCode.value = normalizeText(section.section || '');
    }
    if (soSubjectDescription) {
      soSubjectDescription.value = normalizeText(subject.description || '');
    }
    if (soSubjectProfessor) {
      soSubjectProfessor.value = normalizeText(subject.professor || '');
    }
    if (soSubjectSlots) {
      soSubjectSlots.value = String(subject.slots || 0);
    }
    var flags = subject.flags || {};
    if (soSubjectFlagOpen) {
      soSubjectFlagOpen.checked = !!flags.open;
    }
    if (soSubjectFlagBlock) {
      soSubjectFlagBlock.checked = !!flags.block;
    }
    if (soSubjectFlagTutorial) {
      soSubjectFlagTutorial.checked = !!flags.tutorial;
    }
    renderSubjectScheduleRows(subject);
    setSubjectScheduleFeedback('', false);
    soSubjectScheduleModal.classList.add('is-open');
    soSubjectScheduleModal.setAttribute('aria-hidden', 'false');
  }
  function closeSubjectScheduleModal() {
    if (!soSubjectScheduleModal) {
      return;
    }
    soSubjectScheduleModal.classList.remove('is-open');
    soSubjectScheduleModal.setAttribute('aria-hidden', 'true');
    state.subjectEditor = null;
    setSubjectScheduleFeedback('', false);
  }
  function readSubjectScheduleRows() {
    var response = {
      lines: [],
      firstRoom: ''
    };
    if (!soSubjectScheduleRows) {
      return response;
    }
    var rows = soSubjectScheduleRows.querySelectorAll('tr[data-so-row-id]');
    Array.prototype.forEach.call(rows, function (row) {
      var daySelect = row.querySelector('.so-subject-day');
      var enabledInput = row.querySelector('.so-subject-enabled');
      var day = normalizeText(daySelect ? daySelect.value : '').toUpperCase() || 'M';
      var enabled = !!(enabledInput && enabledInput.checked);
      var fromInput = row.querySelector('.so-subject-time-from');
      var toInput = row.querySelector('.so-subject-time-to');
      var roomInput = row.querySelector('.so-subject-room');
      var fromValue = normalizeText(fromInput ? fromInput.value : '');
      var toValue = normalizeText(toInput ? toInput.value : '');
      var roomValue = normalizeText(roomInput ? roomInput.value : '');
      if (!enabled) {
        return;
      }
      if (fromValue === '' && toValue === '' && roomValue === '') {
        return;
      }
      var timePart = fromValue !== '' || toValue !== '' ? (fromValue || 'TBA') + '-' + (toValue || 'TBA') : 'TBA';
      var roomPart = roomValue !== '' ? roomValue : 'TBA';
      response.lines.push(day + ' ' + timePart + ' ' + roomPart);
      if (response.firstRoom === '' && roomValue !== '') {
        response.firstRoom = roomValue;
      }
    });
    return response;
  }
  function saveSubjectSchedule() {
    if (!state.subjectEditor) {
      return;
    }
    var section = getSectionById(state.subjectEditor.sectionId);
    if (!section || !Array.isArray(section.subjects)) {
      return;
    }
    var targetIndex = toInt(state.subjectEditor.subjectIndex, -1);
    if (targetIndex < 0 || targetIndex >= section.subjects.length) {
      return;
    }
    var subject = section.subjects[targetIndex];
    var collected = readSubjectScheduleRows();
    subject.description = normalizeText(soSubjectDescription ? soSubjectDescription.value : '') || subject.description || '';
    subject.professor = normalizeText(soSubjectProfessor ? soSubjectProfessor.value : '') || subject.professor || '';
    subject.slots = toInt(soSubjectSlots ? soSubjectSlots.value : '', toInt(subject.slots, 0));
    subject.flags = {
      open: !!(soSubjectFlagOpen && soSubjectFlagOpen.checked),
      block: !!(soSubjectFlagBlock && soSubjectFlagBlock.checked),
      tutorial: !!(soSubjectFlagTutorial && soSubjectFlagTutorial.checked)
    };
    subject.schedules = collected.lines;
    if (collected.firstRoom !== '') {
      subject.room = collected.firstRoom;
    }
    renderSectionConfigModal(section);
    if (String(state.selectedSectionId) === String(section.id)) {
      renderSectionDetails(section);
    }
    closeSubjectScheduleModal();
    showMessage('Subject schedule updated successfully.', 'success');
  }
  function ensureSelectedSectionVisible() {
    if (!state.selectedSectionId) {
      clearSectionDetails();
      return;
    }
    var selected = getSectionById(state.selectedSectionId);
    if (!selected) {
      state.selectedSectionId = null;
      clearSectionDetails();
      return;
    }
    renderSectionDetails(selected);
  }
  function renderSectionDirectory() {
    if (!soSectionListBody) {
      return;
    }
    if (!state.sections.length) {
      soSectionListBody.innerHTML = '<tr><td colspan="8" class="so-empty-row">No sections found for the selected filters.</td></tr>';
      return;
    }
    soSectionListBody.innerHTML = state.sections.map(function (entry) {
      var isActive = String(state.selectedSectionId) === String(entry.id);
      var rowClass = isActive ? 'so-section-row is-active' : 'so-section-row';
      var subjectCount = Number(entry.subjectCount || (entry.subjects || []).length || 0);
      return '' + '<tr class="' + rowClass + '" data-section-id="' + escapeHtml(entry.id) + '">' + '<td>' + escapeHtml(entry.program || '-') + '</td>' + '<td>' + escapeHtml(entry.section || '-') + '</td>' + '<td>' + escapeHtml(entry.schoolYear || '-') + '</td>' + '<td>' + escapeHtml(entry.semester || '-') + '</td>' + '<td>' + escapeHtml(entry.yearLevel || '-') + '</td>' + '<td>' + escapeHtml(entry.slots || 0) + '</td>' + '<td>' + escapeHtml(entry.adviser || 'TBA') + '</td>' + '<td>' + escapeHtml(subjectCount) + '</td>' + '</tr>';
    }).join('');
  }
  function getPaginationWindow(pageNumber, maxPage) {
    var start = Math.max(1, pageNumber - 2);
    var end = Math.min(maxPage, pageNumber + 2);
    if (pageNumber <= 3) {
      end = Math.min(maxPage, 5);
    } else if (pageNumber >= maxPage - 2) {
      start = Math.max(1, maxPage - 4);
    }
    return {
      start: start,
      end: end
    };
  }
  function updateDirectorySummary() {
    if (!soSectionPageText) {
      return;
    }
    if (state.totalSections <= 0) {
      soSectionPageText.textContent = 'Showing 0 sections';
      return;
    }
    var start = (state.page - 1) * state.perPage + 1;
    var end = start + state.sections.length - 1;
    if (end < start) {
      end = start;
    }
    soSectionPageText.textContent = 'Showing ' + start + '-' + end + ' of ' + state.totalSections + ' sections';
  }
  function renderPagination() {
    if (soSectionPaginationBar) {
      soSectionPaginationBar.style.display = state.totalSections > 0 ? '' : 'none';
    }
    if (soPrevBtn) {
      soPrevBtn.disabled = state.isLoading || state.page <= 1;
    }
    if (soNextBtn) {
      soNextBtn.disabled = state.isLoading || state.page >= state.lastPage;
    }
    if (!soPageNumbers) {
      return;
    }
    soPageNumbers.innerHTML = '';
    var maxPage = Math.max(1, state.lastPage);
    var windowRange = getPaginationWindow(state.page, maxPage);
    for (var pageNumber = windowRange.start; pageNumber <= windowRange.end; pageNumber += 1) {
      var pageBtn = document.createElement('button');
      pageBtn.type = 'button';
      pageBtn.className = 'rtp-page-num' + (pageNumber === state.page ? ' active' : '');
      pageBtn.setAttribute('data-so-page', String(pageNumber));
      pageBtn.setAttribute('aria-label', 'Go to page ' + pageNumber);
      pageBtn.textContent = String(pageNumber);
      if (state.isLoading || pageNumber === state.page) {
        pageBtn.disabled = true;
      }
      soPageNumbers.appendChild(pageBtn);
    }
  }
  function setDirectoryLoading(loading, message) {
    state.isLoading = !!loading;
    if (loading && soSectionListBody) {
      soSectionListBody.innerHTML = '<tr><td colspan="8" class="so-empty-row">' + escapeHtml(message || 'Loading sections...') + '</td></tr>';
    }
    renderPagination();
    updateDirectorySummary();
  }
  function pickPreferredSectionId(preferredSectionLabel, fallbackSectionId) {
    var preferred = normalizeText(preferredSectionLabel);
    if (preferred !== '') {
      for (var i = 0; i < state.sections.length; i += 1) {
        var sectionValue = normalizeText(state.sections[i].section);
        if (normalizeLower(sectionValue) === normalizeLower(preferred)) {
          return state.sections[i].id;
        }
      }
    }
    if (fallbackSectionId) {
      for (var j = 0; j < state.sections.length; j += 1) {
        if (String(state.sections[j].id) === String(fallbackSectionId)) {
          return fallbackSectionId;
        }
      }
    }
    return null;
  }
  function loadSections(pageToLoad, preferredSectionLabel) {
    if (!FETCH_URL) {
      setDirectoryLoading(true, 'Section Offering data endpoint is unavailable.');
      return;
    }
    var targetPage = toInt(pageToLoad, state.page);
    if (targetPage < 1) {
      targetPage = 1;
    }
    var token = state.requestToken + 1;
    state.requestToken = token;
    setDirectoryLoading(true, 'Loading sections...');
    var selectedBeforeLoad = state.selectedSectionId;
    fetchJson(buildFetchUrl(targetPage)).then(function (payload) {
      if (token !== state.requestToken) {
        return;
      }
      syncFilterOptions(payload && payload.options ? payload.options : {});
      var incomingSections = payload && Array.isArray(payload.sections) ? payload.sections : [];
      var meta = payload && payload.meta ? payload.meta : {};
      state.page = Math.max(1, toInt(meta.page, targetPage));
      state.lastPage = Math.max(1, toInt(meta.last_page, 1));
      state.perPage = clamp(toInt(meta.per_page, DEFAULT_PER_PAGE), 10, 100);
      state.totalSections = Math.max(0, toInt(meta.total_sections, incomingSections.length));
      state.totalSubjects = Math.max(0, toInt(meta.total_subjects, 0));
      state.visibleSections = Math.max(0, toInt(meta.visible_sections, incomingSections.length));
      state.sections = incomingSections;
      state.selectedSectionId = pickPreferredSectionId(preferredSectionLabel, selectedBeforeLoad);
      renderSectionDirectory();
      updateDirectorySummary();
      renderPagination();
      ensureSelectedSectionVisible();
    })["catch"](function (errorPayload) {
      if (token !== state.requestToken) {
        return;
      }
      console.error(errorPayload);
      state.sections = [];
      state.selectedSectionId = null;
      state.totalSections = 0;
      state.totalSubjects = 0;
      state.visibleSections = 0;
      state.page = 1;
      state.lastPage = 1;
      renderSectionDirectory();
      clearSectionDetails();
      updateDirectorySummary();
      renderPagination();
      if (soSectionListBody) {
        soSectionListBody.innerHTML = '<tr><td colspan="8" class="so-empty-row">Unable to load section data right now.</td></tr>';
      }
      showMessage(getPayloadErrorMessage(errorPayload, 'Unable to load Section Offering data.'), 'warning');
    })["finally"](function () {
      if (token !== state.requestToken) {
        return;
      }
      state.isLoading = false;
      renderPagination();
      updateDirectorySummary();
    });
  }
  function selectSection(sectionId, openConfigModal) {
    state.selectedSectionId = sectionId;
    renderSectionDirectory();
    var selected = getSectionById(sectionId);
    if (!selected) {
      clearSectionDetails();
      return;
    }
    renderSectionDetails(selected);
    if (openConfigModal) {
      openSectionConfigModal(selected);
    }
  }
  function queueSearchLoad(rawValue) {
    var nextValue = normalizeText(rawValue);
    var previousValue = normalizeText(state.lastSearchValue);
    state.lastSearchValue = nextValue;
    if (state.searchTimers.sectionSearch) {
      clearTimeout(state.searchTimers.sectionSearch);
    }
    state.searchTimers.sectionSearch = setTimeout(function () {
      state.searchTimers.sectionSearch = null;
      if (nextValue !== '' && nextValue.length < 2) {
        if (previousValue.length >= 2) {
          loadSections(1);
        }
        return;
      }
      loadSections(1);
    }, 420);
  }
  function setModalFeedback(message, isError) {
    if (!soModalFeedback) {
      return;
    }
    var text = normalizeText(message);
    soModalFeedback.textContent = text;
    soModalFeedback.classList.toggle('is-error', !!isError);
    soModalFeedback.style.display = text !== '' ? 'block' : 'none';
  }
  function updateCurriculumSummary() {
    if (!soCurriculumSummary) {
      return;
    }
    var count = state.curriculumIncludedIds.length;
    soCurriculumSummary.textContent = count + ' subject' + (count === 1 ? '' : 's') + ' selected';
  }
  function buildCurriculumOptionLabel(row) {
    var code = normalizeText(row.code);
    var description = normalizeText(row.description);
    if (code !== '' && description !== '') {
      return code + ' - ' + description;
    }
    if (code !== '') {
      return code;
    }
    return description !== '' ? description : 'Untitled Subject';
  }
  function sortRowsByLabel(rows) {
    return rows.sort(function (left, right) {
      var leftLabel = buildCurriculumOptionLabel(left).toLowerCase();
      var rightLabel = buildCurriculumOptionLabel(right).toLowerCase();
      if (leftLabel < rightLabel) {
        return -1;
      }
      if (leftLabel > rightLabel) {
        return 1;
      }
      return 0;
    });
  }
  function renderCurriculumLists() {
    if (!soCurriculumAvailable || !soCurriculumIncluded) {
      return;
    }
    var includedLookup = {};
    state.curriculumIncludedIds.forEach(function (id) {
      includedLookup[String(id)] = true;
    });
    var availableRows = [];
    var includedRows = [];
    state.curriculumRows.forEach(function (row) {
      if (includedLookup[String(row.id)]) {
        includedRows.push(row);
      } else {
        availableRows.push(row);
      }
    });
    availableRows = sortRowsByLabel(availableRows);
    includedRows = sortRowsByLabel(includedRows);
    soCurriculumAvailable.innerHTML = availableRows.map(function (row) {
      return '<option value="' + escapeHtml(row.id) + '">' + escapeHtml(buildCurriculumOptionLabel(row)) + '</option>';
    }).join('');
    soCurriculumIncluded.innerHTML = includedRows.map(function (row) {
      return '<option value="' + escapeHtml(row.id) + '">' + escapeHtml(buildCurriculumOptionLabel(row)) + '</option>';
    }).join('');
    updateCurriculumSummary();
  }
  function getSelectedListValues(selectElement) {
    if (!selectElement) {
      return [];
    }
    return Array.prototype.filter.call(selectElement.options, function (option) {
      return option.selected;
    }).map(function (option) {
      return toInt(option.value, 0);
    }).filter(function (value) {
      return value > 0;
    });
  }
  function moveCurriculumSelectedToIncluded() {
    var selected = getSelectedListValues(soCurriculumAvailable);
    if (!selected.length) {
      return;
    }
    selected.forEach(function (id) {
      if (state.curriculumIncludedIds.indexOf(id) === -1) {
        state.curriculumIncludedIds.push(id);
      }
    });
    renderCurriculumLists();
  }
  function moveCurriculumAllToIncluded() {
    state.curriculumIncludedIds = state.curriculumRows.map(function (row) {
      return toInt(row.id, 0);
    }).filter(function (id) {
      return id > 0;
    });
    renderCurriculumLists();
  }
  function moveCurriculumSelectedToAvailable() {
    var selected = getSelectedListValues(soCurriculumIncluded);
    if (!selected.length) {
      return;
    }
    state.curriculumIncludedIds = state.curriculumIncludedIds.filter(function (id) {
      return selected.indexOf(id) === -1;
    });
    renderCurriculumLists();
  }
  function moveCurriculumAllToAvailable() {
    state.curriculumIncludedIds = [];
    renderCurriculumLists();
  }
  function setCurriculumButtonsDisabled(disabled) {
    [soCurriculumAdd, soCurriculumAddAll, soCurriculumRemove, soCurriculumRemoveAll].forEach(function (button) {
      if (button) {
        button.disabled = !!disabled;
      }
    });
  }
  function loadCurriculumAssignments() {
    if (!CURRICULUM_URL) {
      return;
    }
    var courseId = normalizeText(soModalProgram && soModalProgram.value);
    var semester = normalizeText(soModalTerm && soModalTerm.value);
    var yearLevel = normalizeText(soModalYearLevel && soModalYearLevel.value);
    if (courseId === '' || semester === '' || yearLevel === '') {
      state.curriculumRows = [];
      state.curriculumIncludedIds = [];
      renderCurriculumLists();
      setModalFeedback('Please select program, term, and year level.', false);
      return;
    }
    var token = state.curriculumRequestToken + 1;
    state.curriculumRequestToken = token;
    setCurriculumButtonsDisabled(true);
    setModalFeedback('Loading curriculum subjects...', false);
    var url = CURRICULUM_URL + (CURRICULUM_URL.indexOf('?') === -1 ? '?' : '&') + ['course_id=' + encodeURIComponent(courseId), 'semester=' + encodeURIComponent(semester), 'year_level=' + encodeURIComponent(yearLevel)].join('&');
    fetchJson(url).then(function (payload) {
      if (token !== state.curriculumRequestToken) {
        return;
      }
      var rows = payload && Array.isArray(payload.rows) ? payload.rows : [];
      var previousIncluded = state.curriculumIncludedIds.slice();
      var allowedLookup = {};
      rows.forEach(function (row) {
        allowedLookup[String(row.id)] = true;
      });
      state.curriculumRows = rows;
      state.curriculumIncludedIds = previousIncluded.filter(function (id) {
        return !!allowedLookup[String(id)];
      });
      if (!state.curriculumIncludedIds.length && rows.length) {
        state.curriculumIncludedIds = rows.map(function (row) {
          return toInt(row.id, 0);
        }).filter(function (id) {
          return id > 0;
        });
      }
      renderCurriculumLists();
      if (!rows.length) {
        setModalFeedback('No curriculum subjects found for this program/year/term.', false);
        return;
      }
      setModalFeedback('', false);
    })["catch"](function (errorPayload) {
      if (token !== state.curriculumRequestToken) {
        return;
      }
      console.error(errorPayload);
      state.curriculumRows = [];
      state.curriculumIncludedIds = [];
      renderCurriculumLists();
      setModalFeedback(getPayloadErrorMessage(errorPayload, 'Unable to load curriculum subjects.'), true);
    })["finally"](function () {
      if (token !== state.curriculumRequestToken) {
        return;
      }
      setCurriculumButtonsDisabled(false);
    });
  }
  function normalizeModalSectionInput(value) {
    var normalized = normalizeText(value).toUpperCase();
    normalized = normalized.replace(/\s+/g, '');
    normalized = normalized.replace(/[^A-Z0-9-]/g, '');
    if (normalized === '') {
      return '';
    }
    if (/^[1-6]-/.test(normalized)) {
      return normalized.replace(/^[1-6]-/, '');
    }
    return normalized;
  }
  function inferModalSchoolYear() {
    if (soSY && normalizeText(soSY.value) !== '') {
      return normalizeText(soSY.value);
    }
    if (!soSY || !soSY.options) {
      return '';
    }
    for (var i = 0; i < soSY.options.length; i += 1) {
      var value = normalizeText(soSY.options[i].value);
      if (value !== '') {
        return value;
      }
    }
    return '';
  }
  function isAllowedSchoolYear(value) {
    var normalized = normalizeText(value);
    if (normalized === '') {
      return false;
    }
    var selectElement = soModalSY || soSY;
    if (selectElement && selectElement.options && selectElement.options.length) {
      for (var i = 0; i < selectElement.options.length; i += 1) {
        if (normalizeText(selectElement.options[i].value) === normalized) {
          return true;
        }
      }
    }
    return /^\d{4}-\d{4}$/.test(normalized);
  }
  function syncModalDefaultsFromFilters() {
    if (soModalProgram && soProgram && normalizeText(soProgram.value) !== '') {
      soModalProgram.value = normalizeText(soProgram.value);
      emitListboxRefresh(soModalProgram);
    }
    if (soModalSY) {
      soModalSY.value = inferModalSchoolYear();
      emitListboxRefresh(soModalSY);
    }
    if (soModalTerm) {
      var termValue = soTerm && normalizeText(soTerm.value) !== '' ? normalizeText(soTerm.value) : 'First';
      soModalTerm.value = termValue;
      emitListboxRefresh(soModalTerm);
    }
    if (soModalYearLevel) {
      var yearValue = soYearLevel && normalizeText(soYearLevel.value) !== '' ? normalizeText(soYearLevel.value) : 'First';
      soModalYearLevel.value = yearValue;
      emitListboxRefresh(soModalYearLevel);
    }
    if (soModalSection && soSection) {
      soModalSection.value = normalizeModalSectionInput(soSection.value);
    }
    if (soModalSlots && normalizeText(soModalSlots.value) === '') {
      soModalSlots.value = '30';
    }
  }
  function openAddSectionModal() {
    if (!soAddSectionModal) {
      return;
    }
    syncModalDefaultsFromFilters();
    state.curriculumRows = [];
    state.curriculumIncludedIds = [];
    renderCurriculumLists();
    setModalFeedback('', false);
    soAddSectionModal.classList.add('is-open');
    soAddSectionModal.setAttribute('aria-hidden', 'false');
    loadCurriculumAssignments();
  }
  function closeAddSectionModal() {
    if (!soAddSectionModal) {
      return;
    }
    soAddSectionModal.classList.remove('is-open');
    soAddSectionModal.setAttribute('aria-hidden', 'true');
    setModalFeedback('', false);
  }
  function setSaveState(saving) {
    state.saveInProgress = !!saving;
    if (soSaveAddSection) {
      soSaveAddSection.disabled = !!saving;
      soSaveAddSection.textContent = saving ? 'Saving...' : 'Save Section';
    }
  }
  function buildStorePayload() {
    return {
      course_id: toInt(soModalProgram ? soModalProgram.value : '', 0),
      school_year: normalizeText(soModalSY ? soModalSY.value : ''),
      semester: normalizeText(soModalTerm ? soModalTerm.value : ''),
      year_level: normalizeText(soModalYearLevel ? soModalYearLevel.value : ''),
      section: normalizeModalSectionInput(soModalSection ? soModalSection.value : ''),
      slots: toInt(soModalSlots ? soModalSlots.value : '', 0),
      adviser: normalizeText(soModalAdviser ? soModalAdviser.value : ''),
      description: normalizeText(soModalDescription ? soModalDescription.value : ''),
      curriculum_subject_ids: state.curriculumIncludedIds.slice()
    };
  }
  function validateStorePayload(payload) {
    if (!payload.course_id || payload.course_id < 1) {
      return 'Please select a course.';
    }
    if (!isAllowedSchoolYear(payload.school_year)) {
      return 'Please select a valid School Year.';
    }
    if (payload.semester === '') {
      return 'Please select a semester.';
    }
    if (payload.year_level === '') {
      return 'Please select a year level.';
    }
    if (payload.section === '') {
      return 'Please provide a section code.';
    }
    if (!payload.curriculum_subject_ids.length) {
      return 'Select at least one curriculum subject.';
    }
    return '';
  }
  function applyFilterValue(selectElement, value) {
    if (!selectElement) {
      return;
    }
    selectElement.value = value;
    emitListboxRefresh(selectElement);
  }
  function saveSectionOffering() {
    if (!STORE_URL || state.saveInProgress) {
      return;
    }
    var payload = buildStorePayload();
    var validationMessage = validateStorePayload(payload);
    if (validationMessage !== '') {
      setModalFeedback(validationMessage, true);
      return;
    }
    setSaveState(true);
    setModalFeedback('', false);
    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    fetchJson(STORE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify(payload)
    }).then(function (responsePayload) {
      closeAddSectionModal();
      showMessage('Section created successfully.', 'success');
      var createdSection = responsePayload && responsePayload.section ? responsePayload.section : null;
      applyFilterValue(soSY, payload.school_year);
      applyFilterValue(soTerm, payload.semester);
      applyFilterValue(soYearLevel, payload.year_level);
      applyFilterValue(soProgram, String(payload.course_id));
      if (soSectionSearch) {
        soSectionSearch.value = '';
        state.lastSearchValue = '';
      }
      if (soSection) {
        var createdLabel = createdSection && normalizeText(createdSection.section) !== '' ? normalizeText(createdSection.section) : '';
        soSection.value = createdLabel;
        emitListboxRefresh(soSection);
        loadSections(1, createdLabel);
        return;
      }
      loadSections(1);
    })["catch"](function (errorPayload) {
      console.error(errorPayload);
      setModalFeedback(getPayloadErrorMessage(errorPayload, 'Unable to save section right now.'), true);
    })["finally"](function () {
      setSaveState(false);
    });
  }
  if (soSectionListBody) {
    soSectionListBody.addEventListener('click', function (event) {
      var row = event.target.closest('tr[data-section-id]');
      if (!row) {
        return;
      }
      selectSection(row.getAttribute('data-section-id'));
    });
  }
  if (soBody) {
    soBody.addEventListener('click', function (event) {
      var row = event.target.closest('tr[data-so-subject-index]');
      if (!row || !state.selectedSectionId) {
        return;
      }
      openSubjectScheduleModal(state.selectedSectionId, row.getAttribute('data-so-subject-index'));
    });
  }
  [soSY, soTerm, soYearLevel, soSection, soProgram].forEach(function (input) {
    if (!input) {
      return;
    }
    input.addEventListener('change', function () {
      loadSections(1);
    });
  });
  if (soSectionSearch) {
    soSectionSearch.addEventListener('input', function () {
      queueSearchLoad(soSectionSearch.value);
    });
  }
  if (soPrevBtn) {
    soPrevBtn.addEventListener('click', function () {
      if (state.isLoading || state.page <= 1) {
        return;
      }
      loadSections(state.page - 1);
    });
  }
  if (soNextBtn) {
    soNextBtn.addEventListener('click', function () {
      if (state.isLoading || state.page >= state.lastPage) {
        return;
      }
      loadSections(state.page + 1);
    });
  }
  if (soPageNumbers) {
    soPageNumbers.addEventListener('click', function (event) {
      var button = event.target.closest('[data-so-page]');
      if (!button || state.isLoading) {
        return;
      }
      var pageValue = toInt(button.getAttribute('data-so-page'), 0);
      if (pageValue < 1 || pageValue === state.page || pageValue > state.lastPage) {
        return;
      }
      loadSections(pageValue);
    });
  }
  if (soBackToDirectory) {
    soBackToDirectory.addEventListener('click', function () {
      state.selectedSectionId = null;
      renderSectionDirectory();
      clearSectionDetails();
    });
  }
  if (soPrintButton) {
    soPrintButton.addEventListener('click', function () {
      window.print();
    });
  }
  if (soOpenAddSection) {
    soOpenAddSection.addEventListener('click', openAddSectionModal);
  }
  if (soCloseAddSection) {
    soCloseAddSection.addEventListener('click', closeAddSectionModal);
  }
  if (soCancelAddSection) {
    soCancelAddSection.addEventListener('click', closeAddSectionModal);
  }
  if (soSaveAddSection) {
    soSaveAddSection.addEventListener('click', saveSectionOffering);
  }
  if (soAddSectionModal) {
    soAddSectionModal.addEventListener('click', function (event) {
      if (event.target === soAddSectionModal) {
        closeAddSectionModal();
      }
    });
  }
  if (soSectionConfigClose) {
    soSectionConfigClose.addEventListener('click', closeSectionConfigModal);
  }
  if (soSectionConfigDone) {
    soSectionConfigDone.addEventListener('click', closeSectionConfigModal);
  }
  if (soSectionConfigModal) {
    soSectionConfigModal.addEventListener('click', function (event) {
      if (event.target === soSectionConfigModal) {
        closeSectionConfigModal();
      }
    });
  }
  if (soSectionConfigBody) {
    soSectionConfigBody.addEventListener('click', function (event) {
      var actionButton = event.target.closest('button[data-so-config-action]');
      if (!actionButton) {
        return;
      }
      var sectionId = actionButton.getAttribute('data-so-section-id');
      var subjectIndex = actionButton.getAttribute('data-so-subject-index');
      openSubjectScheduleModal(sectionId, subjectIndex);
    });
  }
  if (soSubjectScheduleClose) {
    soSubjectScheduleClose.addEventListener('click', closeSubjectScheduleModal);
  }
  if (soSubjectScheduleCancel) {
    soSubjectScheduleCancel.addEventListener('click', closeSubjectScheduleModal);
  }
  if (soSubjectScheduleSave) {
    soSubjectScheduleSave.addEventListener('click', saveSubjectSchedule);
  }
  if (soSubjectAddScheduleRow) {
    soSubjectAddScheduleRow.addEventListener('click', function () {
      appendSubjectScheduleRow({
        day: 'M',
        enabled: true,
        from: '',
        to: '',
        room: '',
        lab: false
      });
    });
  }
  if (soSubjectScheduleRows) {
    soSubjectScheduleRows.addEventListener('click', function (event) {
      var actionButton = event.target.closest('[data-so-subject-row-action]');
      if (!actionButton) {
        return;
      }
      var action = actionButton.getAttribute('data-so-subject-row-action');
      var row = actionButton.closest('tr[data-so-row-id]');
      if (action === 'add') {
        var rowDay = row ? normalizeText((row.querySelector('.so-subject-day') || {}).value).toUpperCase() : 'M';
        insertSubjectScheduleRowAfter(row, {
          day: rowDay || 'M',
          enabled: true,
          from: '',
          to: '',
          room: '',
          lab: false
        });
        return;
      }
      if (action === 'remove' && row) {
        var totalRows = soSubjectScheduleRows.querySelectorAll('tr[data-so-row-id]').length;
        if (totalRows <= 1) {
          return;
        }
        row.parentNode.removeChild(row);
      }
    });
  }
  if (soSubjectScheduleModal) {
    soSubjectScheduleModal.addEventListener('click', function (event) {
      if (event.target === soSubjectScheduleModal) {
        closeSubjectScheduleModal();
      }
    });
  }
  [soModalProgram, soModalTerm, soModalYearLevel].forEach(function (input) {
    if (!input) {
      return;
    }
    input.addEventListener('change', function () {
      loadCurriculumAssignments();
    });
  });
  if (soCurriculumAdd) {
    soCurriculumAdd.addEventListener('click', moveCurriculumSelectedToIncluded);
  }
  if (soCurriculumAddAll) {
    soCurriculumAddAll.addEventListener('click', moveCurriculumAllToIncluded);
  }
  if (soCurriculumRemove) {
    soCurriculumRemove.addEventListener('click', moveCurriculumSelectedToAvailable);
  }
  if (soCurriculumRemoveAll) {
    soCurriculumRemoveAll.addEventListener('click', moveCurriculumAllToAvailable);
  }
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }
    if (soSubjectScheduleModal && soSubjectScheduleModal.classList.contains('is-open')) {
      closeSubjectScheduleModal();
      return;
    }
    if (soSectionConfigModal && soSectionConfigModal.classList.contains('is-open')) {
      closeSectionConfigModal();
      return;
    }
    if (soAddSectionModal && soAddSectionModal.classList.contains('is-open')) {
      closeAddSectionModal();
    }
  });
  renderPagination();
  updateDirectorySummary();
  loadSections(1);
});

/***/ }),

/***/ 5:
/*!************************************************!*\
  !*** multi ./resources/js/section-offering.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\Users\Luis\Downloads\plp-demo\resources\js\section-offering.js */"./resources/js/section-offering.js");


/***/ })

/******/ });