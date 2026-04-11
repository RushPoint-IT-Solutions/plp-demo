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
/******/ 	return __webpack_require__(__webpack_require__.s = 4);
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
  var sectionPage = document.querySelector('.page-section-offering .pf-page');
  if (!sectionPage) {
    return;
  }
  var soSY = document.getElementById('soSY');
  var soTerm = document.getElementById('soTerm');
  var soYearLevel = document.getElementById('soYearLevel');
  var soSection = document.getElementById('soSection');
  var soProgram = document.getElementById('soProgram');
  var soSectionSearch = document.getElementById('soSectionSearch');
  var soSectionListBody = document.getElementById('soSectionListBody');
  var soSectionPageText = document.getElementById('soSectionPageText');
  var soSectionListCard = document.getElementById('soSectionListCard');
  var soCard = document.getElementById('soCard');
  var soCardTitle = document.getElementById('soCardTitle');
  var soBody = document.getElementById('soBody');
  var soPageText = document.getElementById('soPageText');
  var soWeekly = document.getElementById('soWeekly');
  var soWeeklyGrid = document.getElementById('soWeeklyGrid');
  var soBackToDirectory = document.getElementById('soBackToDirectory');
  var soOpenAddSection = document.getElementById('soOpenAddSection');
  var soAddSectionModal = document.getElementById('soAddSectionModal');
  var soCloseAddSection = document.getElementById('soCloseAddSection');
  var soCancelAddSection = document.getElementById('soCancelAddSection');
  var soSaveAddSection = document.getElementById('soSaveAddSection');
  var soModalFeedback = document.getElementById('soModalFeedback');
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
  var sectionState = {
    selectedSectionId: null,
    allSections: buildInitialSections(),
    filteredSections: []
  };
  var soCurriculumPool = [];
  var soCurriculumIncludedKeys = [];
  function buildInitialSections() {
    return [{
      id: 'BSIT-2025-2026-SECOND-FIRST-A',
      program: 'BSIT',
      schoolYear: '2025-2026',
      semester: 'Second',
      yearLevel: 'First',
      section: 'A',
      slots: 30,
      adviser: 'CAYA JR., DOMINGO M.',
      description: 'Encoding of section is closed for this course.',
      subjects: [subject('CC103', 'Computer Programming 2', 2, 3, 5, 3, 'BLDG. 1 -401/ONLINE CLASS', 'CAYA JR., DOMINGO M.', 30, ['W 07:00PM-09:00PM ONLINE CLASS', 'F 07:00AM-10:00AM BLDG. 1 -401']), subject('GE5', 'Purposive Communication', 3, 0, 3, 3, 'BLDG. 2 -201', 'DAVID, ALEXANDER L.', 30, ['M 08:00AM-11:00AM BLDG. 2 -201']), subject('GE6', 'Arts Appreciation', 3, 0, 3, 3, 'BLDG. 2 -101', 'CASTRO, JAMES CARLO C.', 30, ['M 12:00PM-03:00PM BLDG. 2 -101']), subject('IM101', 'Fundamentals of Database Systems', 2, 3, 5, 3, 'BLDG. 1 -401', 'VINUYA, LINCOLN V', 30, ['S 07:00AM-12:00PM BLDG. 1 -401']), subject('MS121', 'Discrete Mathematics', 3, 0, 3, 3, 'BLDG. 1 -305/BLDG. 2 -201', 'BANSIL, CESAR RIVO', 30, ['T 10:00AM-11:30AM BLDG. 1 -305', 'W 07:30AM-09:00AM BLDG. 2 -201']), subject('MT102', 'Multimedia Technology', 2, 3, 5, 3, 'BLDG. 1 -401', 'VINUYA, LINCOLN V', 30, ['S 12:00PM-05:00PM BLDG. 1 -401']), subject('NSTP2102B', 'NSTP-CWTS 2', 3, 0, 3, 3, 'BLDG. 2 -LIBRARY', 'BELARDO, FRAULEIN S.', 30, ['W 01:00PM-04:00PM BLDG. 2 -LIBRARY']), subject('PATHFIT2', 'Fitness Activity and Exercise', 2, 0, 2, 2, 'BLDG. 1 -201', 'GUIAO, JOHN PAUL S.', 30, ['TH 10:00AM-12:00PM BLDG. 1 -201']), subject('WS101', 'Web System and Technologies', 2, 3, 5, 3, 'BLDG. 2 -101/BLDG. 1 -401', 'BANSIL, CESAR RIVO', 30, ['M 03:00PM-05:00PM BLDG. 2 -101', 'T 02:30PM-05:30PM BLDG. 1 -401'])]
    }, {
      id: 'BSCS-2025-2026-SECOND-FIRST-B',
      program: 'BSCS',
      schoolYear: '2025-2026',
      semester: 'Second',
      yearLevel: 'First',
      section: 'B',
      slots: 35,
      adviser: 'SANTOS, MARY JOY P.',
      description: 'Open for section updates.',
      subjects: [subject('CS103', 'Programming Fundamentals II', 2, 3, 5, 3, 'BLDG. 2 -301', 'RIVERA, PATRICK M.', 35, ['M 01:00PM-04:00PM BLDG. 2 -301']), subject('MATH201', 'Calculus for Computing', 3, 0, 3, 3, 'BLDG. 1 -204', 'DELA CRUZ, ANA', 35, ['W 10:00AM-01:00PM BLDG. 1 -204']), subject('GE7', 'Science, Technology and Society', 3, 0, 3, 3, 'BLDG. 2 -104', 'REYES, KIM L.', 35, ['F 08:00AM-11:00AM BLDG. 2 -104'])]
    }, {
      id: 'BSED-2024-2025-FIRST-SECOND-C',
      program: 'BSED',
      schoolYear: '2024-2025',
      semester: 'First',
      yearLevel: 'Second',
      section: 'C',
      slots: 40,
      adviser: 'LOPEZ, ERIC G.',
      description: 'Active section.',
      subjects: [subject('EDU201', 'Child and Adolescent Development', 3, 0, 3, 3, 'BLDG. 3 -103', 'RAMOS, MELISSA P.', 40, ['TH 01:00PM-04:00PM BLDG. 3 -103']), subject('ENG202', 'Campus Journalism', 3, 0, 3, 3, 'BLDG. 3 -108', 'DE GUZMAN, KATE', 40, ['T 08:00AM-11:00AM BLDG. 3 -108'])]
    }, {
      id: 'BSIT-2025-2026-SECOND-FIRST-D',
      program: 'BSIT',
      schoolYear: '2025-2026',
      semester: 'Second',
      yearLevel: 'First',
      section: 'D',
      slots: 32,
      adviser: 'ORTEGA, RAYMOND B.',
      description: 'Demo section with alternate schedule.',
      subjects: [subject('CC104', 'Object Oriented Programming', 2, 3, 5, 3, 'BLDG. 2 -204', 'ORTEGA, RAYMOND B.', 32, ['T 01:00PM-04:00PM BLDG. 2 -204']), subject('GE1', 'Understanding the Self', 3, 0, 3, 3, 'BLDG. 1 -103', 'TAN, LOURDES M.', 32, ['TH 07:30AM-10:30AM BLDG. 1 -103']), subject('NSTP2102B', 'NSTP-CWTS 2', 3, 0, 3, 3, 'BLDG. 2 -LIBRARY', 'BELARDO, FRAULEIN S.', 32, ['S 08:00AM-11:00AM BLDG. 2 -LIBRARY'])]
    }];
  }
  function subject(code, description, lec, lab, tuitionUnits, creditUnits, room, professor, slots, schedules) {
    return {
      code: code,
      description: description,
      lec: lec,
      lab: lab,
      tuitionUnits: tuitionUnits,
      creditUnits: creditUnits,
      room: room,
      professor: professor,
      slots: slots,
      schedules: schedules || []
    };
  }
  function escapeHtml(value) {
    return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function normalize(value) {
    return String(value || '').toLowerCase().trim();
  }
  function getSubjectKey(subjectRow) {
    return normalize(subjectRow.code) + '|' + normalize(subjectRow.description);
  }
  function toCurriculumTemplate(subjectRow) {
    return {
      key: getSubjectKey(subjectRow),
      code: subjectRow.code,
      description: subjectRow.description,
      lec: subjectRow.lec,
      lab: subjectRow.lab,
      tuitionUnits: subjectRow.tuitionUnits,
      creditUnits: subjectRow.creditUnits
    };
  }
  function buildCurriculumPool(program, yearLevel, semester) {
    var strictMatches = [];
    var fallbackMatches = [];
    sectionState.allSections.forEach(function (entry) {
      var sameProgram = normalize(entry.program) === normalize(program);
      var sameYearLevel = normalize(entry.yearLevel) === normalize(yearLevel);
      var sameSemester = normalize(entry.semester) === normalize(semester);
      (entry.subjects || []).forEach(function (subjectRow) {
        var template = toCurriculumTemplate(subjectRow);
        if (sameProgram && sameYearLevel && sameSemester) {
          strictMatches.push(template);
        }
        if (sameProgram) {
          fallbackMatches.push(template);
        }
      });
    });
    var rawList = strictMatches.length ? strictMatches : fallbackMatches;
    if (!rawList.length) {
      sectionState.allSections.forEach(function (entry) {
        (entry.subjects || []).forEach(function (subjectRow) {
          rawList.push(toCurriculumTemplate(subjectRow));
        });
      });
    }
    var deduped = [];
    var seen = {};
    rawList.forEach(function (item) {
      if (!seen[item.key]) {
        seen[item.key] = true;
        deduped.push(item);
      }
    });
    deduped.sort(function (left, right) {
      var leftText = (left.code + ' ' + left.description).toUpperCase();
      var rightText = (right.code + ' ' + right.description).toUpperCase();
      if (leftText < rightText) {
        return -1;
      }
      if (leftText > rightText) {
        return 1;
      }
      return 0;
    });
    return deduped;
  }
  function findCurriculumItemByKey(key) {
    var found = null;
    soCurriculumPool.some(function (item) {
      if (item.key === key) {
        found = item;
        return true;
      }
      return false;
    });
    return found;
  }
  function formatCurriculumOption(item) {
    return item.code + ' - ' + item.description + ' (' + item.creditUnits + ')';
  }
  function renderCurriculumLists() {
    if (!soCurriculumAvailable || !soCurriculumIncluded) {
      return;
    }
    var includedMap = {};
    soCurriculumIncludedKeys.forEach(function (key) {
      includedMap[key] = true;
    });
    var availableItems = soCurriculumPool.filter(function (item) {
      return !includedMap[item.key];
    });
    if (!availableItems.length) {
      soCurriculumAvailable.innerHTML = '<option value="" disabled>- No Subject -</option>';
    } else {
      soCurriculumAvailable.innerHTML = availableItems.map(function (item) {
        return '<option value="' + escapeHtml(item.key) + '">' + escapeHtml(formatCurriculumOption(item)) + '</option>';
      }).join('');
    }
    var includedItems = [];
    soCurriculumIncludedKeys.forEach(function (key) {
      var matched = findCurriculumItemByKey(key);
      if (matched) {
        includedItems.push(matched);
      }
    });
    if (!includedItems.length) {
      soCurriculumIncluded.innerHTML = '<option value="" disabled>- No Subject -</option>';
    } else {
      soCurriculumIncluded.innerHTML = includedItems.map(function (item) {
        return '<option value="' + escapeHtml(item.key) + '">' + escapeHtml(formatCurriculumOption(item)) + '</option>';
      }).join('');
    }
    if (soCurriculumSummary) {
      var total = includedItems.length;
      soCurriculumSummary.textContent = total + ' subject' + (total === 1 ? '' : 's') + ' selected';
    }
  }
  function refreshCurriculumPool() {
    var program = soModalProgram && soModalProgram.value ? soModalProgram.value : '';
    var yearLevelRaw = soModalYearLevel && soModalYearLevel.value ? soModalYearLevel.value : '';
    var semester = soModalTerm && soModalTerm.value ? soModalTerm.value : '';
    var yearLevel = yearLevelRaw;
    if (yearLevelRaw === 'First Year') {
      yearLevel = 'First';
    } else if (yearLevelRaw === 'Second Year') {
      yearLevel = 'Second';
    } else if (yearLevelRaw === 'Third Year') {
      yearLevel = 'Third';
    } else if (yearLevelRaw === 'Fourth Year') {
      yearLevel = 'Fourth';
    }
    soCurriculumPool = buildCurriculumPool(program, yearLevel, semester);
    var validMap = {};
    soCurriculumPool.forEach(function (item) {
      validMap[item.key] = true;
    });
    soCurriculumIncludedKeys = soCurriculumIncludedKeys.filter(function (key) {
      return !!validMap[key];
    });
    renderCurriculumLists();
  }
  function resetCurriculumPicker() {
    soCurriculumIncludedKeys = [];
    refreshCurriculumPool();
  }
  function getSelectedOptionValues(selectElement) {
    if (!selectElement) {
      return [];
    }
    return Array.prototype.slice.call(selectElement.options || []).filter(function (option) {
      return option.selected && option.value;
    }).map(function (option) {
      return option.value;
    });
  }
  function includeCurriculumKeys(keys) {
    var existingMap = {};
    soCurriculumIncludedKeys.forEach(function (key) {
      existingMap[key] = true;
    });
    keys.forEach(function (key) {
      if (!key || existingMap[key]) {
        return;
      }
      if (findCurriculumItemByKey(key)) {
        soCurriculumIncludedKeys.push(key);
        existingMap[key] = true;
      }
    });
    renderCurriculumLists();
  }
  function removeCurriculumKeys(keys) {
    var removeMap = {};
    keys.forEach(function (key) {
      if (key) {
        removeMap[key] = true;
      }
    });
    soCurriculumIncludedKeys = soCurriculumIncludedKeys.filter(function (key) {
      return !removeMap[key];
    });
    renderCurriculumLists();
  }
  function buildSubjectsFromSelectedCurriculum(slots) {
    var selectedSubjects = [];
    soCurriculumIncludedKeys.forEach(function (key) {
      var item = findCurriculumItemByKey(key);
      if (!item) {
        return;
      }
      selectedSubjects.push(subject(item.code, item.description, item.lec, item.lab, item.tuitionUnits, item.creditUnits, 'TBA', 'TBA', slots, []));
    });
    return selectedSubjects;
  }
  function parseScheduleLine(line) {
    var text = String(line || '').trim();
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
  function getSectionLabel(section) {
    return section.program + ' ' + section.yearLevel.charAt(0) + '-' + section.section;
  }
  function applyFilters() {
    var filterSY = normalize(soSY ? soSY.value : '');
    var filterTerm = normalize(soTerm ? soTerm.value : '');
    var filterYearLevel = normalize(soYearLevel ? soYearLevel.value : '');
    var filterSection = normalize(soSection ? soSection.value : '');
    var filterProgram = normalize(soProgram ? soProgram.value : '');
    var searchValue = normalize(soSectionSearch ? soSectionSearch.value : '');
    sectionState.filteredSections = sectionState.allSections.filter(function (entry) {
      var matchesSY = !filterSY || normalize(entry.schoolYear) === filterSY;
      var matchesTerm = !filterTerm || normalize(entry.semester) === filterTerm;
      var matchesYear = !filterYearLevel || normalize(entry.yearLevel) === filterYearLevel;
      var matchesProgram = !filterProgram || normalize(entry.program) === filterProgram;
      var matchesSection = !filterSection || normalize(entry.section).indexOf(filterSection) !== -1;
      var searchable = [entry.program, entry.section, entry.schoolYear, entry.semester, entry.yearLevel, entry.adviser].join(' ');
      var matchesSearch = !searchValue || normalize(searchable).indexOf(searchValue) !== -1;
      return matchesSY && matchesTerm && matchesYear && matchesProgram && matchesSection && matchesSearch;
    });
    renderSectionDirectory();
    ensureSelectedSectionVisible();
  }
  function renderSectionDirectory() {
    if (!soSectionListBody) {
      return;
    }
    if (!sectionState.filteredSections.length) {
      soSectionListBody.innerHTML = '<tr><td colspan="8" class="so-empty-row">No sections found for the selected filters.</td></tr>';
      if (soSectionPageText) {
        soSectionPageText.textContent = 'Showing 0 sections';
      }
      return;
    }
    soSectionListBody.innerHTML = sectionState.filteredSections.map(function (entry) {
      var isActive = sectionState.selectedSectionId === entry.id;
      var rowClass = isActive ? 'so-section-row is-active' : 'so-section-row';
      return '' + '<tr class="' + rowClass + '" data-section-id="' + escapeHtml(entry.id) + '">' + '<td>' + escapeHtml(entry.program) + '</td>' + '<td>' + escapeHtml(entry.section) + '</td>' + '<td>' + escapeHtml(entry.schoolYear) + '</td>' + '<td>' + escapeHtml(entry.semester) + '</td>' + '<td>' + escapeHtml(entry.yearLevel) + '</td>' + '<td>' + escapeHtml(entry.slots) + '</td>' + '<td>' + escapeHtml(entry.adviser || 'N/A') + '</td>' + '<td>' + escapeHtml((entry.subjects || []).length) + '</td>' + '</tr>';
    }).join('');
    if (soSectionPageText) {
      soSectionPageText.textContent = 'Showing ' + sectionState.filteredSections.length + ' section' + (sectionState.filteredSections.length === 1 ? '' : 's');
    }
  }
  function ensureSelectedSectionVisible() {
    if (!sectionState.selectedSectionId) {
      clearSectionDetails();
      return;
    }
    var selected = getSectionById(sectionState.selectedSectionId);
    if (!selected) {
      sectionState.selectedSectionId = null;
      clearSectionDetails();
      return;
    }
    var isStillVisible = sectionState.filteredSections.some(function (entry) {
      return entry.id === sectionState.selectedSectionId;
    });
    if (!isStillVisible) {
      clearSectionDetails();
      return;
    }
    renderSectionDetails(selected);
  }
  function getSectionById(sectionId) {
    var matched = null;
    sectionState.allSections.some(function (entry) {
      if (entry.id === sectionId) {
        matched = entry;
        return true;
      }
      return false;
    });
    return matched;
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
      soCardTitle.textContent = 'Section Offering: ' + section.section;
    }
    showDetailsView();
    var sectionLabel = getSectionLabel(section);
    var rows = section.subjects || [];
    if (!rows.length) {
      soBody.innerHTML = '<tr><td colspan="11" class="so-empty-row">No subjects are assigned to this section yet.</td></tr>';
    } else {
      soBody.innerHTML = rows.map(function (item) {
        var scheduleLines = (item.schedules || []).map(function (line) {
          return '<div class="so-schedule-line">' + escapeHtml(line) + '</div>';
        }).join('');
        return '' + '<tr>' + '<td>' + escapeHtml(item.code) + '</td>' + '<td>' + escapeHtml(item.description) + '</td>' + '<td>' + escapeHtml(item.lec) + '</td>' + '<td>' + escapeHtml(item.lab) + '</td>' + '<td>' + escapeHtml(item.tuitionUnits) + '</td>' + '<td>' + escapeHtml(item.creditUnits) + '</td>' + '<td>' + escapeHtml(sectionLabel) + '</td>' + '<td>' + escapeHtml(item.room) + '</td>' + '<td>' + escapeHtml(item.professor) + '</td>' + '<td>' + escapeHtml(item.slots) + '</td>' + '<td class="so-schedule-cell">' + (scheduleLines || '<div class="so-schedule-line">-</div>') + '</td>' + '</tr>';
      }).join('');
    }
    if (soPageText) {
      soPageText.textContent = 'Showing ' + rows.length + ' subject' + (rows.length === 1 ? '' : 's');
    }
    renderWeeklyGrid(section);
  }
  function renderWeeklyGrid(section) {
    var sectionLabel = getSectionLabel(section);
    var bucket = {
      'M': [],
      'T': [],
      'W': [],
      'TH': [],
      'F': [],
      'S': [],
      'SU': []
    };
    (section.subjects || []).forEach(function (subjectRow) {
      (subjectRow.schedules || []).forEach(function (line) {
        var parsed = parseScheduleLine(line);
        if (!parsed.day || !bucket[parsed.day]) {
          return;
        }
        bucket[parsed.day].push({
          code: subjectRow.code,
          section: sectionLabel,
          time: parsed.time,
          room: parsed.room
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
  function selectSection(sectionId, silentDirectoryRender) {
    sectionState.selectedSectionId = sectionId;
    if (!silentDirectoryRender) {
      renderSectionDirectory();
    }
    var selected = getSectionById(sectionId);
    if (!selected) {
      clearSectionDetails();
      return;
    }
    renderSectionDetails(selected);
  }
  function populateSectionFilterOptions() {
    if (!soSection) {
      return;
    }
    var existingValue = soSection.value;
    var sectionNames = sectionState.allSections.map(function (item) {
      return item.section;
    }).filter(function (name, index, list) {
      return list.indexOf(name) === index;
    }).sort();
    var options = ['<option value="">All Sections</option>'];
    sectionNames.forEach(function (name) {
      options.push('<option value="' + escapeHtml(name) + '">' + escapeHtml(name) + '</option>');
    });
    soSection.innerHTML = options.join('');
    if (sectionNames.indexOf(existingValue) !== -1) {
      soSection.value = existingValue;
    }
  }
  function openModal() {
    if (!soAddSectionModal) {
      return;
    }
    soAddSectionModal.classList.add('is-open');
    soAddSectionModal.setAttribute('aria-hidden', 'false');
    if (soModalFeedback) {
      soModalFeedback.textContent = '';
      soModalFeedback.className = 'so-modal-feedback';
    }
    if (soModalSY && soSY && soSY.value) {
      soModalSY.value = soSY.value;
    }
    if (soModalTerm && soTerm && soTerm.value) {
      soModalTerm.value = soTerm.value;
    }
    if (soModalYearLevel && soYearLevel && soYearLevel.value) {
      soModalYearLevel.value = soYearLevel.value;
    }
    if (soModalProgram && soProgram && soProgram.value) {
      soModalProgram.value = soProgram.value;
    }
    resetCurriculumPicker();
  }
  function closeModal() {
    if (!soAddSectionModal) {
      return;
    }
    soAddSectionModal.classList.remove('is-open');
    soAddSectionModal.setAttribute('aria-hidden', 'true');
  }
  function showModalError(message) {
    if (!soModalFeedback) {
      return;
    }
    soModalFeedback.textContent = message;
    soModalFeedback.className = 'so-modal-feedback is-error';
  }
  function createSectionFromModal() {
    var program = soModalProgram && soModalProgram.value ? soModalProgram.value : '';
    var schoolYear = soModalSY && soModalSY.value ? soModalSY.value.trim() : '';
    var semester = soModalTerm && soModalTerm.value ? soModalTerm.value : '';
    var yearLevelRaw = soModalYearLevel && soModalYearLevel.value ? soModalYearLevel.value : '';
    var section = soModalSection && soModalSection.value ? soModalSection.value.trim().toUpperCase() : '';
    var slots = parseInt(soModalSlots && soModalSlots.value ? soModalSlots.value : '0', 10);
    var adviser = soModalAdviser && soModalAdviser.value ? soModalAdviser.value.trim() : '';
    var description = soModalDescription && soModalDescription.value ? soModalDescription.value.trim() : '';
    var selectedSubjects = buildSubjectsFromSelectedCurriculum(slots);
    if (!program || !schoolYear || !semester || !yearLevelRaw || !section || !slots || slots < 1) {
      showModalError('Program, school year, term, year level, section, and valid slots are required.');
      return;
    }
    if (!/^\d{4}-\d{4}$/.test(schoolYear)) {
      showModalError('School year must be in the format YYYY-YYYY (example: 2026-2027).');
      return;
    }
    var yearLevel = yearLevelRaw;
    if (yearLevelRaw === 'First Year') {
      yearLevel = 'First';
    } else if (yearLevelRaw === 'Second Year') {
      yearLevel = 'Second';
    } else if (yearLevelRaw === 'Third Year') {
      yearLevel = 'Third';
    } else if (yearLevelRaw === 'Fourth Year') {
      yearLevel = 'Fourth';
    }
    var duplicate = sectionState.allSections.some(function (entry) {
      return normalize(entry.program) === normalize(program) && normalize(entry.schoolYear) === normalize(schoolYear) && normalize(entry.semester) === normalize(semester) && normalize(entry.yearLevel) === normalize(yearLevel) && normalize(entry.section) === normalize(section);
    });
    if (duplicate) {
      showModalError('This section already exists under the selected term and course.');
      return;
    }
    var sectionRecord = {
      id: [program, schoolYear, semester, yearLevel, section].join('-').replace(/\s+/g, '-').toUpperCase(),
      program: program,
      schoolYear: schoolYear,
      semester: semester,
      yearLevel: yearLevel,
      section: section,
      slots: slots,
      adviser: adviser || 'TBA',
      description: description || 'New section created from Section Offering.',
      subjects: selectedSubjects
    };
    sectionState.allSections.push(sectionRecord);
    populateSectionFilterOptions();
    if (soSY) {
      soSY.value = schoolYear;
    }
    if (soTerm) {
      soTerm.value = semester;
    }
    if (soYearLevel) {
      soYearLevel.value = yearLevel;
    }
    if (soSection) {
      soSection.value = section;
    }
    if (soProgram) {
      soProgram.value = program;
    }
    if (soSectionSearch) {
      soSectionSearch.value = '';
    }
    closeModal();
    applyFilters();
    selectSection(sectionRecord.id);
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
  [soSY, soTerm, soYearLevel, soSection, soProgram].forEach(function (input) {
    if (!input) {
      return;
    }
    input.addEventListener('change', applyFilters);
  });
  if (soSectionSearch) {
    soSectionSearch.addEventListener('input', applyFilters);
  }
  if (soOpenAddSection) {
    soOpenAddSection.addEventListener('click', openModal);
  }
  if (soCloseAddSection) {
    soCloseAddSection.addEventListener('click', closeModal);
  }
  if (soCancelAddSection) {
    soCancelAddSection.addEventListener('click', closeModal);
  }
  if (soSaveAddSection) {
    soSaveAddSection.addEventListener('click', createSectionFromModal);
  }
  [soModalProgram, soModalYearLevel, soModalTerm].forEach(function (field) {
    if (!field) {
      return;
    }
    field.addEventListener('change', function () {
      refreshCurriculumPool();
    });
  });
  if (soCurriculumAdd) {
    soCurriculumAdd.addEventListener('click', function () {
      includeCurriculumKeys(getSelectedOptionValues(soCurriculumAvailable));
    });
  }
  if (soCurriculumAddAll) {
    soCurriculumAddAll.addEventListener('click', function () {
      includeCurriculumKeys(soCurriculumPool.map(function (item) {
        return item.key;
      }));
    });
  }
  if (soCurriculumRemove) {
    soCurriculumRemove.addEventListener('click', function () {
      removeCurriculumKeys(getSelectedOptionValues(soCurriculumIncluded));
    });
  }
  if (soCurriculumRemoveAll) {
    soCurriculumRemoveAll.addEventListener('click', function () {
      soCurriculumIncludedKeys = [];
      renderCurriculumLists();
    });
  }
  if (soCurriculumAvailable) {
    soCurriculumAvailable.addEventListener('dblclick', function () {
      includeCurriculumKeys(getSelectedOptionValues(soCurriculumAvailable));
    });
  }
  if (soCurriculumIncluded) {
    soCurriculumIncluded.addEventListener('dblclick', function () {
      removeCurriculumKeys(getSelectedOptionValues(soCurriculumIncluded));
    });
  }
  if (soAddSectionModal) {
    soAddSectionModal.addEventListener('click', function (event) {
      if (event.target === soAddSectionModal) {
        closeModal();
      }
    });
  }
  if (soBackToDirectory) {
    soBackToDirectory.addEventListener('click', function () {
      sectionState.selectedSectionId = null;
      renderSectionDirectory();
      clearSectionDetails();
    });
  }
  populateSectionFilterOptions();
  applyFilters();
});

/***/ }),

/***/ 4:
/*!************************************************!*\
  !*** multi ./resources/js/section-offering.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\Users\Luis\Downloads\plp-demo\resources\js\section-offering.js */"./resources/js/section-offering.js");


/***/ })

/******/ });