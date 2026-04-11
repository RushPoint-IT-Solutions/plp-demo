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
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/applicant-form.js":
/*!****************************************!*\
  !*** ./resources/js/applicant-form.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
// applicant-form.js - Applicant 4-step wizard interactions

(function () {
  function initSubmittedCalendar() {
    var calendarRoot = document.getElementById('applicationStatusCalendar');
    if (!calendarRoot) {
      return;
    }
    var monthSelect = document.getElementById('statusCalendarMonth');
    var yearSelect = document.getElementById('statusCalendarYear');
    var daysWrap = document.getElementById('statusCalendarDays');
    if (!monthSelect || !yearSelect || !daysWrap) {
      return;
    }
    var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    function parseLocalYmd(value) {
      if (!value || typeof value !== 'string') {
        return null;
      }
      var parts = value.split('-');
      if (parts.length !== 3) {
        return null;
      }
      var year = parseInt(parts[0], 10);
      var month = parseInt(parts[1], 10) - 1;
      var day = parseInt(parts[2], 10);
      if (isNaN(year) || isNaN(month) || isNaN(day)) {
        return null;
      }
      return new Date(year, month, day);
    }
    var selectedDateValue = calendarRoot.getAttribute('data-selected-date');
    var selectedDate = parseLocalYmd(selectedDateValue) || new Date();
    if (isNaN(selectedDate.getTime())) {
      selectedDate = new Date();
    }
    var viewDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
    function toYmd(date) {
      var m = String(date.getMonth() + 1);
      var d = String(date.getDate());
      if (m.length < 2) {
        m = '0' + m;
      }
      if (d.length < 2) {
        d = '0' + d;
      }
      return date.getFullYear() + '-' + m + '-' + d;
    }
    function buildMonthOptions() {
      monthSelect.innerHTML = '';
      monthNames.forEach(function (name, index) {
        var opt = document.createElement('option');
        opt.value = String(index);
        opt.textContent = name;
        monthSelect.appendChild(opt);
      });
    }
    function buildYearOptions() {
      var currentYear = new Date().getFullYear();
      var minYear = currentYear - 10;
      var maxYear = currentYear + 10;
      yearSelect.innerHTML = '';
      for (var year = minYear; year <= maxYear; year++) {
        var opt = document.createElement('option');
        opt.value = String(year);
        opt.textContent = String(year);
        yearSelect.appendChild(opt);
      }
    }
    function renderCalendar() {
      monthSelect.value = String(viewDate.getMonth());
      yearSelect.value = String(viewDate.getFullYear());
      daysWrap.innerHTML = '';
      var year = viewDate.getFullYear();
      var month = viewDate.getMonth();
      var firstDay = new Date(year, month, 1);
      var startWeekday = firstDay.getDay();
      var daysInMonth = new Date(year, month + 1, 0).getDate();
      for (var i = 0; i < startWeekday; i++) {
        var blank = document.createElement('button');
        blank.type = 'button';
        blank.className = 'submitted-calendar-day is-muted';
        blank.disabled = true;
        blank.textContent = '';
        daysWrap.appendChild(blank);
      }
      for (var day = 1; day <= daysInMonth; day++) {
        var date = new Date(year, month, day);
        var dayBtn = document.createElement('button');
        dayBtn.type = 'button';
        dayBtn.className = 'submitted-calendar-day';
        dayBtn.textContent = String(day);
        var ymd = toYmd(date);
        if (ymd === toYmd(selectedDate)) {
          dayBtn.classList.add('is-selected');
        }
        dayBtn.addEventListener('click', function (event) {
          var clickedDay = parseInt(event.currentTarget.textContent || '0', 10);
          if (!clickedDay) {
            return;
          }
          selectedDate = new Date(viewDate.getFullYear(), viewDate.getMonth(), clickedDay);
          renderCalendar();
        });
        daysWrap.appendChild(dayBtn);
      }
    }
    buildMonthOptions();
    buildYearOptions();
    renderCalendar();
    monthSelect.addEventListener('change', function () {
      viewDate = new Date(viewDate.getFullYear(), parseInt(this.value, 10), 1);
      renderCalendar();
    });
    yearSelect.addEventListener('change', function () {
      viewDate = new Date(parseInt(this.value, 10), viewDate.getMonth(), 1);
      renderCalendar();
    });
    document.querySelectorAll('[data-calendar-nav]').forEach(function (button) {
      button.addEventListener('click', function () {
        var delta = parseInt(this.getAttribute('data-calendar-nav') || '0', 10);
        viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + delta, 1);
        renderCalendar();
      });
    });
  }
  initSubmittedCalendar();
  var resetApplicationProgressForm = document.getElementById('resetApplicationProgressForm');
  if (resetApplicationProgressForm) {
    resetApplicationProgressForm.addEventListener('submit', function (event) {
      var confirmMessage = this.getAttribute('data-confirm-message') || 'Reset application progress?';
      if (!window.confirm(confirmMessage)) {
        event.preventDefault();
      }
    });
  }
  var form = document.getElementById('applicationForm');
  if (!form) {
    return;
  }
  var skipStepValidation = form.getAttribute('data-preview-skip-validation') === '1';
  var stepSaveInProgress = false;
  var finalSubmitInProgress = false;
  function hasPendingSubmission() {
    return stepSaveInProgress || finalSubmitInProgress;
  }
  function toggleActionButtons(isBusy) {
    form.querySelectorAll('.btn-setup-next, .btn-setup-prev, button[type="submit"]').forEach(function (button) {
      if (isBusy) {
        button.setAttribute('data-prev-disabled', button.disabled ? '1' : '0');
        button.disabled = true;
        button.classList.add('is-loading');
        return;
      }
      button.classList.remove('is-loading');
      if (button.getAttribute('data-prev-disabled') === '0') {
        button.disabled = false;
      }
      button.removeAttribute('data-prev-disabled');
    });
  }
  function findRequiredLabel(field) {
    var container = field.closest('.setup-col, .setup-col-sm, .setup-col-toggle, .setup-col--full');
    if (!container) {
      container = field.closest('.setup-row');
    }
    if (!container) {
      return null;
    }
    return container.querySelector('.setup-label');
  }
  function syncRequiredIndicators() {
    form.querySelectorAll('.setup-label.is-required').forEach(function (label) {
      label.classList.remove('is-required');
    });
    var grouped = {};
    form.querySelectorAll('input[required], select[required], textarea[required]').forEach(function (field) {
      if (field.disabled) {
        return;
      }
      var type = (field.type || '').toLowerCase();
      if ((type === 'radio' || type === 'checkbox') && field.name) {
        if (grouped[field.name]) {
          return;
        }
        grouped[field.name] = true;
      }
      var label = findRequiredLabel(field);
      if (label) {
        label.classList.add('is-required');
      }
    });
  }
  function bindDigitsOnly(selector, maxLength) {
    document.querySelectorAll(selector).forEach(function (input) {
      function normalize() {
        var digits = (input.value || '').replace(/\D+/g, '');
        if (typeof maxLength === 'number') {
          digits = digits.slice(0, maxLength);
        }
        if (input.value !== digits) {
          input.value = digits;
        }
      }
      input.addEventListener('input', normalize);
      input.addEventListener('blur', normalize);
      normalize();
    });
  }
  function validateStep(step) {
    if (skipStepValidation) {
      return true;
    }
    var panel = document.getElementById('step-' + step);
    if (!panel) {
      return true;
    }
    var fields = panel.querySelectorAll('input, select, textarea');
    for (var i = 0; i < fields.length; i++) {
      var field = fields[i];
      if (field.disabled) {
        continue;
      }
      if (!field.checkValidity()) {
        field.reportValidity();
        return false;
      }
    }
    return true;
  }
  bindDigitsOnly('input[name="mobile_number"], input[name="mother_mobile_number"], input[name="father_mobile_number"]', 11);
  bindDigitsOnly('input[name="learner_reference_number"]', 12);
  function getActiveStep() {
    var activeStep = parseInt(form.getAttribute('data-active-step') || '1', 10);
    if (isNaN(activeStep) || activeStep < 1) {
      return 1;
    }
    return activeStep > 4 ? 4 : activeStep;
  }
  var maxUnlockedStep = getActiveStep();
  function setActiveStepInput(step) {
    var activeStepInput = document.getElementById('activeStepInput');
    if (activeStepInput) {
      activeStepInput.value = String(step);
    }
  }
  function showFeedback(message, isError) {
    var feedback = document.getElementById('stepSaveFeedback');
    if (!feedback) {
      return;
    }
    feedback.textContent = message;
    feedback.classList.remove('step-hidden', 'applicant-alert-success', 'applicant-alert-error');
    feedback.classList.add(isError ? 'applicant-alert-error' : 'applicant-alert-success');
    if (feedback.dataset.timerId) {
      clearTimeout(parseInt(feedback.dataset.timerId, 10));
    }
    var timerId = setTimeout(function () {
      feedback.classList.add('step-hidden');
    }, 3000);
    feedback.dataset.timerId = String(timerId);
  }
  function goToStep(step, bypassLock) {
    if (!bypassLock && step > maxUnlockedStep) {
      showFeedback('Please complete and save the previous step first.', true);
      return false;
    }
    var panels = document.querySelectorAll('.step-panel');
    var items = document.querySelectorAll('.step-item');
    var lines = document.querySelectorAll('.step-line');
    panels.forEach(function (panel) {
      panel.classList.add('step-hidden');
    });
    var activePanel = document.getElementById('step-' + step);
    if (activePanel) {
      activePanel.classList.remove('step-hidden');
    }
    items.forEach(function (item) {
      var itemStep = parseInt(item.getAttribute('data-step') || '0', 10);
      item.classList.toggle('active', itemStep === step);
      item.classList.toggle('completed', itemStep < step);
    });
    lines.forEach(function (line, index) {
      line.classList.toggle('active', index < step - 1);
    });
    setActiveStepInput(step);
    form.setAttribute('data-active-step', String(step));
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
    return true;
  }
  function getRequestErrorMessage(payload) {
    if (!payload || _typeof(payload) !== 'object') {
      return 'Unable to save step. Please review your fields.';
    }
    if (payload.message) {
      return payload.message;
    }
    if (payload.errors && _typeof(payload.errors) === 'object') {
      var firstField = Object.keys(payload.errors)[0];
      if (firstField && payload.errors[firstField] && payload.errors[firstField][0]) {
        return payload.errors[firstField][0];
      }
    }
    return 'Unable to save step. Please review your fields.';
  }
  function saveStep(step) {
    var url = form.getAttribute('data-step' + step + '-url');
    if (!url) {
      return Promise.resolve(true);
    }
    var payload = new FormData(form);
    payload.set('active_step', String(step));
    return fetch(url, {
      method: 'POST',
      body: payload,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    }).then(function (response) {
      return response.json()["catch"](function () {
        return {};
      }).then(function (data) {
        if (!response.ok) {
          throw {
            payload: data
          };
        }
        return data;
      });
    }).then(function (data) {
      showFeedback(data.message || 'Step saved successfully.', false);
      return true;
    })["catch"](function (error) {
      var message = getRequestErrorMessage(error.payload || error);
      showFeedback(message, true);
      return false;
    });
  }
  window.goToStep = goToStep;
  document.querySelectorAll('.step-item .step-pill').forEach(function (pill) {
    pill.addEventListener('click', function () {
      if (hasPendingSubmission()) {
        return;
      }
      var parent = this.closest('.step-item');
      if (!parent) {
        return;
      }
      var step = parseInt(parent.getAttribute('data-step') || '1', 10);
      goToStep(step, false);
    });
  });
  document.querySelectorAll('button[data-save-step][data-go-step]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (hasPendingSubmission()) {
        showFeedback('Please wait. Your previous action is still processing.', true);
        return;
      }
      var thisButton = this;
      var stepToSave = parseInt(thisButton.getAttribute('data-save-step') || '1', 10);
      var stepToGo = parseInt(thisButton.getAttribute('data-go-step') || '1', 10);
      var panelToSave = document.getElementById('step-' + stepToSave);
      if (panelToSave && panelToSave.classList.contains('step-hidden')) {
        return;
      }
      if (!validateStep(stepToSave)) {
        return;
      }
      stepSaveInProgress = true;
      toggleActionButtons(true);
      saveStep(stepToSave).then(function (ok) {
        stepSaveInProgress = false;
        toggleActionButtons(false);
        if (ok) {
          maxUnlockedStep = Math.max(maxUnlockedStep, stepToGo);
          goToStep(stepToGo, true);
        }
      });
    });
  });
  document.querySelectorAll('button[data-go-step]:not([data-save-step])').forEach(function (button) {
    button.addEventListener('click', function () {
      if (hasPendingSubmission()) {
        return;
      }
      var stepToGo = parseInt(this.getAttribute('data-go-step') || '1', 10);
      goToStep(stepToGo, false);
    });
  });
  form.addEventListener('submit', function (event) {
    if (hasPendingSubmission()) {
      event.preventDefault();
      showFeedback('Please wait. Your previous submission is still processing.', true);
      return;
    }
    finalSubmitInProgress = true;
    toggleActionButtons(true);
    setActiveStepInput(4);
  });
  var nativeSubmit = form.submit;
  if (typeof nativeSubmit === 'function') {
    form.submit = function () {
      if (hasPendingSubmission()) {
        return;
      }
      finalSubmitInProgress = true;
      toggleActionButtons(true);
      setActiveStepInput(4);
      nativeSubmit.call(form);
    };
  }

  // Photo upload preview
  var photoInput = document.getElementById('photoInput');
  var photoImg = document.getElementById('photoImg');
  if (photoInput && photoImg) {
    photoInput.addEventListener('change', function () {
      if (!this.files || !this.files[0]) {
        return;
      }
      var reader = new FileReader();
      reader.onload = function (e) {
        photoImg.src = e.target.result;
        photoImg.classList.remove('setup-photo-img--hidden');
        var icon = document.querySelector('.profile-photo-icon');
        if (icon) {
          icon.style.display = 'none';
        }
      };
      reader.readAsDataURL(this.files[0]);
    });
  }

  // Same as present address
  var sameCheck = document.getElementById('sameAsPresent');
  var permAddressRows = [document.getElementById('permanentAddressFields'), document.getElementById('permanentSelectFields')];
  var presentFields = ['present_street', 'present_barangay', 'present_zipcode', 'present_municipality', 'present_province', 'present_region'];
  var permanentFields = ['permanent_street', 'permanent_barangay', 'permanent_zipcode', 'permanent_municipality', 'permanent_province', 'permanent_region'];
  function copyAddressValues() {
    presentFields.forEach(function (presentName, i) {
      var source = document.querySelector('[name="' + presentName + '"]');
      var target = document.querySelector('[name="' + permanentFields[i] + '"]');
      if (source && target) {
        target.value = source.value;
      }
    });
  }
  function syncPermanent(enabled) {
    permAddressRows.forEach(function (row) {
      if (!row) {
        return;
      }
      row.querySelectorAll('input, select').forEach(function (el) {
        el.disabled = enabled;
        if (!enabled) {
          return;
        }
        el.removeAttribute('required');
      });
      row.style.opacity = enabled ? '0.45' : '1';
    });
    if (enabled) {
      copyAddressValues();
    }
    syncRequiredIndicators();
  }
  if (sameCheck) {
    syncPermanent(sameCheck.checked);
    sameCheck.addEventListener('change', function () {
      syncPermanent(this.checked);
    });
    presentFields.forEach(function (presentName) {
      var source = document.querySelector('[name="' + presentName + '"]');
      if (!source) {
        return;
      }
      source.addEventListener('input', function () {
        if (sameCheck.checked) {
          copyAddressValues();
        }
      });
      source.addEventListener('change', function () {
        if (sameCheck.checked) {
          copyAddressValues();
        }
      });
    });
  }

  // Age auto-fill from Date of Birth
  var dobInput = document.querySelector('[name="date_of_birth"]');
  var ageInput = document.querySelector('[name="age"]');
  function calcAge(dob) {
    var today = new Date();
    var birth = new Date(dob);
    var age = today.getFullYear() - birth.getFullYear();
    var monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || monthDiff === 0 && today.getDate() < birth.getDate()) {
      age--;
    }
    return age >= 0 ? age : 0;
  }
  if (dobInput && ageInput) {
    dobInput.addEventListener('change', function () {
      ageInput.value = this.value ? calcAge(this.value) : '';
    });
  }

  // Step 2 no_k12 behavior
  var noK12Toggle = document.getElementById('noK12Toggle');
  var juniorSchoolField = document.querySelector('[name="junior_school"]');
  var seniorSchoolField = document.getElementById('seniorSchoolField');
  function syncNoK12State() {
    if (!noK12Toggle || !seniorSchoolField) {
      return;
    }
    if (!noK12Toggle.checked) {
      seniorSchoolField.removeAttribute('readonly');
      seniorSchoolField.setAttribute('required', 'required');
      syncRequiredIndicators();
      return;
    }
    seniorSchoolField.value = juniorSchoolField ? juniorSchoolField.value : '';
    seniorSchoolField.setAttribute('readonly', 'readonly');
    seniorSchoolField.removeAttribute('required');
    syncRequiredIndicators();
  }
  if (noK12Toggle && seniorSchoolField) {
    noK12Toggle.addEventListener('change', syncNoK12State);
    if (juniorSchoolField) {
      juniorSchoolField.addEventListener('input', function () {
        if (noK12Toggle.checked) {
          seniorSchoolField.value = juniorSchoolField.value;
        }
      });
    }
    syncNoK12State();
  }

  // Step 4 program type behavior
  var applyProgramInputs = document.querySelectorAll('input[name="apply_program"]');
  var applyCourseSelect = document.getElementById('applyCourseSelect');
  function hasSelectableOption(selectEl) {
    if (!selectEl) {
      return false;
    }
    return Array.prototype.some.call(selectEl.options, function (option) {
      return option.value !== '';
    });
  }
  function syncProgramMode() {
    var selectedProgram = 'college';
    applyProgramInputs.forEach(function (input) {
      var type = (input.type || '').toLowerCase();
      if (type === 'radio' && input.checked || type === 'hidden') {
        selectedProgram = input.value || 'college';
      }
    });
    if (applyCourseSelect) {
      var courseEnabled = selectedProgram === 'college';
      applyCourseSelect.disabled = !courseEnabled;
      if (courseEnabled && hasSelectableOption(applyCourseSelect)) {
        applyCourseSelect.setAttribute('required', 'required');
      } else {
        applyCourseSelect.removeAttribute('required');
        applyCourseSelect.value = '';
      }
    }
    syncRequiredIndicators();
  }
  if (applyProgramInputs.length) {
    applyProgramInputs.forEach(function (input) {
      if ((input.type || '').toLowerCase() === 'radio') {
        input.addEventListener('change', syncProgramMode);
      }
    });
    syncProgramMode();
  }
  syncRequiredIndicators();

  // Address cascading (region > province > municipality)
  function fillSelect(selectEl, items, placeholder) {
    selectEl.innerHTML = '<option value="" disabled selected>' + placeholder + '</option>';
    items.forEach(function (item) {
      var opt = document.createElement('option');
      opt.value = item;
      opt.textContent = item;
      selectEl.appendChild(opt);
    });
    selectEl.disabled = false;
  }
  function resetSelect(selectEl, placeholder) {
    selectEl.innerHTML = '<option value="" disabled selected>' + placeholder + '</option>';
    selectEl.disabled = true;
    selectEl.value = '';
  }
  function setupCascade(prefix, addressData) {
    var regionEl = document.querySelector('[name="' + prefix + '_region"]');
    var provinceEl = document.querySelector('[name="' + prefix + '_province"]');
    var cityEl = document.querySelector('[name="' + prefix + '_municipality"]');
    if (!regionEl || !provinceEl || !cityEl) {
      return;
    }
    fillSelect(regionEl, addressData.map(function (r) {
      return r.name;
    }), 'Choose Region');
    regionEl.disabled = false;
    regionEl.addEventListener('change', function () {
      resetSelect(provinceEl, 'Choose Province');
      resetSelect(cityEl, 'Choose City/Municipality');
      var region = addressData.find(function (r) {
        return r.name === regionEl.value;
      });
      if (!region) {
        return;
      }
      if (region.provinces.length === 1) {
        fillSelect(provinceEl, [region.provinces[0].name], 'Choose Province');
        provinceEl.value = region.provinces[0].name;
        provinceEl.dispatchEvent(new Event('change'));
      } else {
        fillSelect(provinceEl, region.provinces.map(function (p) {
          return p.name;
        }), 'Choose Province');
      }
    });
    provinceEl.addEventListener('change', function () {
      resetSelect(cityEl, 'Choose City/Municipality');
      var region = addressData.find(function (r) {
        return r.name === regionEl.value;
      });
      if (!region) {
        return;
      }
      var province = region.provinces.find(function (p) {
        return p.name === provinceEl.value;
      });
      if (!province) {
        return;
      }
      fillSelect(cityEl, province.cities, 'Choose City/Municipality');
    });
  }
  function getAddressDraft() {
    var draftInput = document.getElementById('applicantAddressDraft');
    if (!draftInput || !draftInput.value) {
      return {};
    }
    function decodeHtml(value) {
      var parser = document.createElement('textarea');
      parser.innerHTML = value;
      return parser.value;
    }
    try {
      return JSON.parse(draftInput.value);
    } catch (err) {
      try {
        return JSON.parse(decodeHtml(draftInput.value));
      } catch (decodeErr) {
        return {};
      }
    }
  }
  function restoreCascade(prefix, draft) {
    var regionEl = document.querySelector('[name="' + prefix + '_region"]');
    var provinceEl = document.querySelector('[name="' + prefix + '_province"]');
    var cityEl = document.querySelector('[name="' + prefix + '_municipality"]');
    if (!regionEl || !provinceEl || !cityEl) {
      return;
    }
    var savedRegion = draft[prefix + '_region'];
    var savedProvince = draft[prefix + '_province'];
    var savedCity = draft[prefix + '_municipality'];
    if (!savedRegion) {
      return;
    }
    var hasRegion = Array.prototype.some.call(regionEl.options, function (opt) {
      return opt.value === savedRegion;
    });
    if (!hasRegion) {
      var regionOption = document.createElement('option');
      regionOption.value = savedRegion;
      regionOption.textContent = savedRegion;
      regionEl.appendChild(regionOption);
    }
    regionEl.value = savedRegion;
    regionEl.dispatchEvent(new Event('change'));
    setTimeout(function () {
      if (savedProvince) {
        var hasProvince = Array.prototype.some.call(provinceEl.options, function (opt) {
          return opt.value === savedProvince;
        });
        if (!hasProvince) {
          var provinceOption = document.createElement('option');
          provinceOption.value = savedProvince;
          provinceOption.textContent = savedProvince;
          provinceEl.appendChild(provinceOption);
        }
        provinceEl.disabled = false;
        provinceEl.value = savedProvince;
        provinceEl.dispatchEvent(new Event('change'));
      }
    }, 50);
    setTimeout(function () {
      if (savedCity) {
        var hasCity = Array.prototype.some.call(cityEl.options, function (opt) {
          return opt.value === savedCity;
        });
        if (!hasCity) {
          var cityOption = document.createElement('option');
          cityOption.value = savedCity;
          cityOption.textContent = savedCity;
          cityEl.appendChild(cityOption);
        }
        cityEl.disabled = false;
        cityEl.value = savedCity;
      }
    }, 120);
  }
  fetch('/js/ph-address.json').then(function (res) {
    return res.json();
  }).then(function (addressData) {
    setupCascade('present', addressData);
    setupCascade('permanent', addressData);
    var draft = getAddressDraft();
    restoreCascade('present', draft);
    restoreCascade('permanent', draft);
    if (sameCheck && sameCheck.checked) {
      copyAddressValues();
    }
  })["catch"](function () {
    showFeedback('Could not load address data. Please refresh and try again.', true);
  });
  goToStep(getActiveStep());
})();

/***/ }),

/***/ 2:
/*!**********************************************!*\
  !*** multi ./resources/js/applicant-form.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\Users\Luis\Downloads\plp-demo\resources\js\applicant-form.js */"./resources/js/applicant-form.js");


/***/ })

/******/ });