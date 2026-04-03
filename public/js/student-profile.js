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

/***/ "./resources/js/student-profile.js":
/*!*****************************************!*\
  !*** ./resources/js/student-profile.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function showProfileToast(message) {
  var toast = document.getElementById('download-toast');
  if (!toast) {
    return;
  }
  var messageEl = toast.querySelector('.toast-message');
  var closeBtn = toast.querySelector('.toast-close');
  if (messageEl) {
    messageEl.textContent = message;
  }
  toast.classList.add('toast-error', 'show');
  if (closeBtn && !closeBtn.dataset.toastBound) {
    closeBtn.addEventListener('click', function () {
      toast.classList.remove('show');
    });
    closeBtn.dataset.toastBound = '1';
  }
  if (toast.dataset.toastTimer) {
    clearTimeout(parseInt(toast.dataset.toastTimer, 10));
  }
  var timer = setTimeout(function () {
    toast.classList.remove('show');
  }, 3000);
  toast.dataset.toastTimer = String(timer);
}
function validateStepRequired(stepNum) {
  var step = document.getElementById('step-' + stepNum);
  if (!step) {
    return {
      valid: true
    };
  }
  var fields = step.querySelectorAll('input, select, textarea');
  var firstInvalid = null;
  var radioSeen = {};
  fields.forEach(function (field) {
    if (firstInvalid) {
      return;
    }
    if (!field.required || field.disabled) {
      return;
    }
    if (field.type === 'radio') {
      if (radioSeen[field.name]) {
        return;
      }
      radioSeen[field.name] = true;
      var checked = step.querySelector('input[type="radio"][name="' + field.name + '"]:checked');
      if (!checked) {
        firstInvalid = field;
      }
      return;
    }
    if (!field.value || !field.value.trim()) {
      firstInvalid = field;
    }
  });
  return {
    valid: !firstInvalid,
    firstInvalid: firstInvalid
  };
}
function goToStep2FromStep1() {
  var result = validateStepRequired(1);
  if (result.valid) {
    goToStep(2);
    return;
  }
  showProfileToast('Please complete all required fields in Step 1 before proceeding.');
  if (result.firstInvalid) {
    result.firstInvalid.focus();
  }
}
function goToStep3FromStep2() {
  var result = validateStepRequired(2);
  if (result.valid) {
    goToStep(3);
    return;
  }
  showProfileToast('Please complete all required fields in Step 2 before proceeding.');
  if (result.firstInvalid) {
    result.firstInvalid.focus();
  }
}
function goToStep4FromStep3() {
  var result = validateStepRequired(3);
  if (result.valid) {
    goToStep(4);
    return;
  }
  showProfileToast('Please complete all required fields in Step 3 before proceeding.');
  if (result.firstInvalid) {
    result.firstInvalid.focus();
  }
}
function goToStep(stepNum) {
  document.querySelectorAll('.step-panel').forEach(function (panel) {
    panel.style.display = 'none';
  });
  var target = document.getElementById('step-' + stepNum);
  if (target) {
    target.style.display = 'block';
  }
  document.querySelectorAll('.step-item').forEach(function (item) {
    var itemStep = parseInt(item.getAttribute('data-step'), 10);
    item.classList.remove('active', 'completed');
    if (itemStep === stepNum) {
      item.classList.add('active');
    } else if (itemStep < stepNum) {
      item.classList.add('completed');
    }
  });
  var lines = document.querySelectorAll('.step-line');
  lines.forEach(function (line, index) {
    if (index < stepNum - 1) {
      line.classList.add('active');
    } else {
      line.classList.remove('active');
    }
  });
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}
window.goToStep = goToStep;
window.goToStep2FromStep1 = goToStep2FromStep1;
window.goToStep3FromStep2 = goToStep3FromStep2;
window.goToStep4FromStep3 = goToStep4FromStep3;
document.addEventListener('DOMContentLoaded', function () {
  // Draft key for localStorage persistence
  var DRAFT_KEY = 'plp_profile_draft';
  var draft = function () {
    try {
      var r = localStorage.getItem(DRAFT_KEY);
      return r ? JSON.parse(r) : null;
    } catch (e) {
      return null;
    }
  }();
  // Fall back to server-injected DB data when no localStorage draft
  if (!draft && window.savedProfile && Object.keys(window.savedProfile).length) {
    draft = window.savedProfile;
  }

  // ── Profile photo upload ──────────────────────────────────────────────────
  var profilePhotoInput = document.getElementById('profilePhotoInput');
  if (profilePhotoInput) {
    profilePhotoInput.addEventListener('change', function (e) {
      var file = e.target.files[0];
      if (!file) {
        return;
      }
      var reader = new FileReader();
      reader.onload = function (ev) {
        var img = document.getElementById('profilePhotoImg');
        var photoIcon = document.querySelector('.profile-photo-icon');
        var photoBtn = document.querySelector('.profile-photo-btn');
        if (img) {
          img.src = ev.target.result;
          img.style.display = 'block';
        }
        if (photoIcon) {
          photoIcon.style.display = 'none';
        }
        if (photoBtn) {
          photoBtn.textContent = 'Change Profile Picture';
        }
        // Persist photo in draft
        try {
          var d = {};
          try {
            d = JSON.parse(localStorage.getItem(DRAFT_KEY) || '{}');
          } catch (ex) {}
          d['__profilePhoto'] = ev.target.result;
          localStorage.setItem(DRAFT_KEY, JSON.stringify(d));
        } catch (ex) {}
      };
      reader.readAsDataURL(file);
    });
  }

  // ── Religion "Other" — swap select for text input in same space ──────────
  var religionSelect = document.getElementById('religionSelect');
  var religionOtherWrap = document.getElementById('religionOtherWrap');
  var religionOtherInput = document.getElementById('religionOtherInput');
  var religionBackBtn = document.getElementById('religionBackBtn');
  if (religionSelect && religionOtherWrap && religionOtherInput && religionBackBtn) {
    religionSelect.addEventListener('change', function () {
      if (this.value === 'Other') {
        religionSelect.style.display = 'none';
        religionOtherWrap.style.display = 'block';
        religionOtherInput.required = true;
        religionOtherInput.focus();
      }
    });
    religionBackBtn.addEventListener('click', function () {
      religionOtherWrap.style.display = 'none';
      religionOtherInput.required = false;
      religionOtherInput.value = '';
      religionSelect.style.display = 'block';
      religionSelect.value = '';
    });
  }

  // ── Nationality "Other" — same swap pattern as Religion ───────────────────
  var nationalitySelect = document.getElementById('nationalitySelect');
  var nationalityOtherWrap = document.getElementById('nationalityOtherWrap');
  var nationalityOtherInput = document.getElementById('nationalityOtherInput');
  var nationalityBackBtn = document.getElementById('nationalityBackBtn');
  if (nationalitySelect && nationalityOtherWrap && nationalityOtherInput && nationalityBackBtn) {
    nationalitySelect.addEventListener('change', function () {
      if (this.value === 'Other') {
        nationalitySelect.style.display = 'none';
        nationalityOtherWrap.style.display = 'block';
        nationalityOtherInput.required = true;
        nationalityOtherInput.focus();
      }
    });
    nationalityBackBtn.addEventListener('click', function () {
      nationalityOtherWrap.style.display = 'none';
      nationalityOtherInput.required = false;
      nationalityOtherInput.value = '';
      nationalitySelect.style.display = 'block';
      nationalitySelect.value = '';
    });
  }

  // ── Date of Birth — auto-calculate age on change ─────────────────────────
  var ageField = document.getElementById('ageField');
  var dobField = document.getElementById('dobField');
  if (dobField && ageField) {
    dobField.addEventListener('change', function () {
      var dob = new Date(this.value);
      if (isNaN(dob.getTime())) {
        ageField.value = '';
        return;
      }
      var today = new Date();
      var age = today.getFullYear() - dob.getFullYear();
      var m = today.getMonth() - dob.getMonth();
      if (m < 0 || m === 0 && today.getDate() < dob.getDate()) {
        age--;
      }
      ageField.value = age > 0 ? age : '';
    });
  }

  // ── Mobile Number — digits only, 09 prefix, max 11 digits ────────────────
  var mobileField = document.getElementById('mobileField');
  if (mobileField) {
    mobileField.addEventListener('input', function () {
      // Strip non-digits
      var digits = this.value.replace(/\D/g, '');
      // Enforce 09 prefix
      if (digits.length >= 2 && digits.substring(0, 2) !== '09') {
        digits = '09' + digits.replace(/^0*9*/, '');
      }
      if (digits.length === 1 && digits !== '0') {
        digits = '0' + digits;
      }
      // Cap at 11
      this.value = digits.substring(0, 11);
    });
    mobileField.addEventListener('blur', function () {
      if (this.value.length > 0 && this.value.length !== 11) {
        showProfileToast('Mobile number must be exactly 11 digits (09XXXXXXXXX).');
        this.focus();
      }
    });
  }

  // ── Contact number fields — same 09 prefix rule as mobile ────────────────
  function setupPhoneField(el) {
    el.addEventListener('input', function () {
      var digits = this.value.replace(/\D/g, '');
      if (digits.length >= 2 && digits.substring(0, 2) !== '09') {
        digits = '09' + digits.replace(/^0*9*/, '');
      }
      if (digits.length === 1 && digits !== '0') {
        digits = '0' + digits;
      }
      this.value = digits.substring(0, 11);
    });
    el.addEventListener('blur', function () {
      if (this.value.length > 0 && this.value.length !== 11) {
        showProfileToast('Contact number must be exactly 11 digits (09XXXXXXXXX).');
      }
    });
  }
  ['mother_contact', 'father_contact', 'guardian_contact'].forEach(function (name) {
    var el = document.querySelector('[name="' + name + '"]');
    if (el) {
      setupPhoneField(el);
    }
  });

  // ── Zipcode — digits only, max 4 ─────────────────────────────────────────
  ['present_zipcode', 'permanent_zipcode'].forEach(function (name) {
    var el = document.querySelector('[name="' + name + '"]');
    if (el) {
      el.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').substring(0, 4);
      });
    }
  });

  // ── "Others" select → in-place text input swap (reusable) ─────────────────
  function setupOtherSwap(selectEl, wrapEl, inputEl, backBtnEl) {
    if (!selectEl || !wrapEl || !inputEl || !backBtnEl) {
      return;
    }
    selectEl.addEventListener('change', function () {
      if (this.value === 'Others') {
        selectEl.style.display = 'none';
        wrapEl.style.display = 'block';
        inputEl.required = true;
        inputEl.focus();
      }
    });
    backBtnEl.addEventListener('click', function () {
      wrapEl.style.display = 'none';
      inputEl.required = false;
      inputEl.value = '';
      selectEl.style.display = 'block';
      selectEl.value = '';
    });
  }
  setupOtherSwap(document.getElementById('incomeSourceSelect'), document.getElementById('incomeSourceOtherWrap'), document.getElementById('incomeSourceOtherInput'), document.getElementById('incomeSourceBackBtn'));
  setupOtherSwap(document.getElementById('livingSituationSelect'), document.getElementById('livingSituationOtherWrap'), document.getElementById('livingSituationOtherInput'), document.getElementById('livingSituationBackBtn'));
  setupOtherSwap(document.getElementById('lmsUsedSelect'), document.getElementById('lmsUsedOtherWrap'), document.getElementById('lmsUsedOtherInput'), document.getElementById('lmsUsedBackBtn'));
  setupOtherSwap(document.getElementById('lmsPreferredSelect'), document.getElementById('lmsPreferredOtherWrap'), document.getElementById('lmsPreferredOtherInput'), document.getElementById('lmsPreferredBackBtn'));

  // ── "Others" checkbox → reveal inline text input ──────────────────────────
  function setupCheckboxOther(groupName, otherValue, inputEl) {
    if (!inputEl) {
      return;
    }
    var checkboxes = document.querySelectorAll('[name="' + groupName + '"][value="' + otherValue + '"]');
    checkboxes.forEach(function (cb) {
      cb.addEventListener('change', function () {
        inputEl.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
          inputEl.value = '';
        }
      });
    });
  }
  setupCheckboxOther('devices[]', 'Others', document.querySelector('[name="devices_other"]'));
  setupCheckboxOther('lms_reasons[]', 'Others', document.querySelector('[name="lms_reasons_other"]'));

  // ── Live address preview ──────────────────────────────────────────────────
  function updateAddressPreview() {
    var previewEl = document.getElementById('addressPreviewText');
    if (!previewEl) {
      return;
    }
    // Preview format: Street, Barangay, City, Province, Region
    var fieldNames = ['present_street', 'present_barangay', 'present_municipality', 'present_province', 'present_region'];
    var parts = [];
    fieldNames.forEach(function (name) {
      var el = document.querySelector('[name="' + name + '"]');
      var val = el ? el.value.trim() : '';
      if (val) {
        parts.push(val.toUpperCase());
      }
    });
    if (parts.length === 0) {
      previewEl.textContent = 'Fill in the address fields above to see a preview...';
      previewEl.classList.add('is-empty');
    } else {
      previewEl.textContent = parts.join(', ');
      previewEl.classList.remove('is-empty');
    }
  }

  // ── Cascading address dropdowns ───────────────────────────────────────────
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

    // Populate regions
    fillSelect(regionEl, addressData.map(function (r) {
      return r.name;
    }), 'Choose Region');
    regionEl.disabled = false;
    regionEl.addEventListener('change', function () {
      resetSelect(provinceEl, 'Choose Province');
      resetSelect(cityEl, 'Choose City/Municipality');
      updateAddressPreview();
      var region = addressData.find(function (r) {
        return r.name === regionEl.value;
      });
      if (!region) {
        return;
      }

      // NCR only has one province (Metro Manila) — auto-select it
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
      updateAddressPreview();
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
    cityEl.addEventListener('change', updateAddressPreview);
  }

  // Restore address cascade selects from draft after JSON loads
  function restoreAddressCascade(prefix) {
    if (!draft) {
      return;
    }
    var regionEl = document.querySelector('[name="' + prefix + '_region"]');
    var provinceEl = document.querySelector('[name="' + prefix + '_province"]');
    var cityEl = document.querySelector('[name="' + prefix + '_municipality"]');
    var savedRegion = draft[prefix + '_region'];
    var savedProvince = draft[prefix + '_province'];
    var savedCity = draft[prefix + '_municipality'];
    if (!savedRegion || !regionEl) {
      return;
    }
    regionEl.value = savedRegion;
    regionEl.dispatchEvent(new Event('change'));
    setTimeout(function () {
      if (savedProvince && provinceEl) {
        provinceEl.value = savedProvince;
        provinceEl.dispatchEvent(new Event('change'));
        setTimeout(function () {
          if (savedCity && cityEl) {
            cityEl.value = savedCity;
          }
          updateAddressPreview();
        }, 60);
      }
    }, 60);
  }
  fetch('/js/ph-address.json').then(function (res) {
    return res.json();
  }).then(function (addressData) {
    setupCascade('present', addressData);
    setupCascade('permanent', addressData);
    restoreAddressCascade('present');
    restoreAddressCascade('permanent');
  })["catch"](function (err) {
    console.warn('Could not load address data:', err);
  });

  // Listen to text fields for the preview as well
  ['present_street', 'present_barangay'].forEach(function (name) {
    var el = document.querySelector('[name="' + name + '"]');
    if (el) {
      el.addEventListener('input', updateAddressPreview);
    }
  });
  updateAddressPreview();

  // ── Same as Present Address ───────────────────────────────────────────────
  var sameAsPresent = document.getElementById('sameAsPresent');
  if (sameAsPresent) {
    sameAsPresent.addEventListener('change', function () {
      var textFields = ['street', 'barangay', 'zipcode'];
      textFields.forEach(function (field) {
        var present = document.querySelector('[name="present_' + field + '"]');
        var permanent = document.querySelector('[name="permanent_' + field + '"]');
        if (present && permanent) {
          permanent.value = sameAsPresent.checked ? present.value : '';
          permanent.disabled = sameAsPresent.checked;
        }
      });

      // For select cascades, copy values and re-trigger change events
      if (sameAsPresent.checked) {
        var pRegion = document.querySelector('[name="present_region"]');
        var pProvince = document.querySelector('[name="present_province"]');
        var pCity = document.querySelector('[name="present_municipality"]');
        var xRegion = document.querySelector('[name="permanent_region"]');
        var xProvince = document.querySelector('[name="permanent_province"]');
        var xCity = document.querySelector('[name="permanent_municipality"]');
        if (pRegion && xRegion && pRegion.value) {
          xRegion.value = pRegion.value;
          xRegion.dispatchEvent(new Event('change'));
          setTimeout(function () {
            if (pProvince && xProvince && pProvince.value) {
              xProvince.value = pProvince.value;
              xProvince.dispatchEvent(new Event('change'));
              setTimeout(function () {
                if (pCity && xCity && pCity.value) {
                  xCity.value = pCity.value;
                }
              }, 50);
            }
          }, 50);
        }
        if (xRegion) {
          xRegion.disabled = true;
        }
        if (xProvince) {
          xProvince.disabled = true;
        }
        if (xCity) {
          xCity.disabled = true;
        }
      } else {
        ['permanent_region', 'permanent_province', 'permanent_municipality'].forEach(function (name) {
          var el = document.querySelector('[name="' + name + '"]');
          if (el) {
            el.disabled = false;
          }
        });
      }
    });
  }
  var form = document.getElementById('studentProfileForm');
  if (!form) {
    return;
  }

  // ── localStorage draft — save on every change, restore on load ────────────
  function saveDraft() {
    // Carry forward __profilePhoto — never overwrite it with undefined
    var data = {};
    try {
      data = JSON.parse(localStorage.getItem(DRAFT_KEY) || '{}');
    } catch (e) {
      data = {};
    }
    form.querySelectorAll('input, select, textarea').forEach(function (f) {
      if (!f.name || f.type === 'file') {
        return;
      }
      if (f.type === 'checkbox' || f.type === 'radio') {
        data[f.name + '||' + f.value] = f.checked;
      } else {
        data[f.name] = f.value;
      }
    });
    data['__religionOther'] = !!(religionOtherWrap && religionOtherWrap.style.display !== 'none');
    data['__nationalityOther'] = !!(nationalityOtherWrap && nationalityOtherWrap.style.display !== 'none');
    var _isoWrap = document.getElementById('incomeSourceOtherWrap');
    var _lsWrap = document.getElementById('livingSituationOtherWrap');
    var _luWrap = document.getElementById('lmsUsedOtherWrap');
    var _lpWrap = document.getElementById('lmsPreferredOtherWrap');
    data['__incomeSourceOther'] = !!(_isoWrap && _isoWrap.style.display !== 'none');
    data['__livingSituationOther'] = !!(_lsWrap && _lsWrap.style.display !== 'none');
    data['__lmsUsedOther'] = !!(_luWrap && _luWrap.style.display !== 'none');
    data['__lmsPreferredOther'] = !!(_lpWrap && _lpWrap.style.display !== 'none');
    try {
      localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
    } catch (e) {}
  }
  function restoreDraft() {
    if (!draft) {
      return;
    }
    form.querySelectorAll('input, select, textarea').forEach(function (f) {
      if (!f.name || f.type === 'file') {
        return;
      }
      if (f.type === 'checkbox' || f.type === 'radio') {
        var key = f.name + '||' + f.value;
        if (draft[key] !== undefined) {
          f.checked = draft[key];
        }
      } else {
        if (draft[f.name] !== undefined && draft[f.name] !== '') {
          f.value = draft[f.name];
        }
      }
    });
    // Re-trigger age calculation
    if (dobField && dobField.value) {
      dobField.dispatchEvent(new Event('change'));
    }
    // Restore profile photo preview
    if (draft['__profilePhotoUrl']) {
      var pImg = document.getElementById('profilePhotoImg');
      var pIcon = document.querySelector('.profile-photo-icon');
      var pBtn = document.querySelector('.profile-photo-btn');
      if (pImg) {
        pImg.src = draft['__profilePhotoUrl'];
        pImg.style.display = 'block';
      }
      if (pIcon) {
        pIcon.style.display = 'none';
      }
      if (pBtn) {
        pBtn.textContent = 'Change Profile Picture';
      }
    } else if (draft['__profilePhoto']) {
      var pImg = document.getElementById('profilePhotoImg');
      var pIcon = document.querySelector('.profile-photo-icon');
      var pBtn = document.querySelector('.profile-photo-btn');
      if (pImg) {
        pImg.src = draft['__profilePhoto'];
        pImg.style.display = 'block';
      }
      if (pIcon) {
        pIcon.style.display = 'none';
      }
      if (pBtn) {
        pBtn.textContent = 'Change Profile Picture';
      }
    }
    // Restore religion Other state
    if (draft['__religionOther'] && religionSelect && religionOtherWrap) {
      religionSelect.style.display = 'none';
      religionOtherWrap.style.display = 'block';
      if (religionOtherInput) {
        religionOtherInput.required = true;
      }
    }
    // Restore nationality Other state
    if (draft['__nationalityOther'] && nationalitySelect && nationalityOtherWrap) {
      nationalitySelect.style.display = 'none';
      nationalityOtherWrap.style.display = 'block';
      if (nationalityOtherInput) {
        nationalityOtherInput.required = true;
      }
    }
    // Restore income source / living situation / lms Others states
    function _restoreOtherSwap(selId, wrapId, inputId, flag) {
      if (!draft[flag]) {
        return;
      }
      var sel = document.getElementById(selId);
      var wrap = document.getElementById(wrapId);
      var input = document.getElementById(inputId);
      if (sel && wrap && input) {
        sel.style.display = 'none';
        wrap.style.display = 'block';
        input.required = true;
      }
    }
    _restoreOtherSwap('incomeSourceSelect', 'incomeSourceOtherWrap', 'incomeSourceOtherInput', '__incomeSourceOther');
    _restoreOtherSwap('livingSituationSelect', 'livingSituationOtherWrap', 'livingSituationOtherInput', '__livingSituationOther');
    _restoreOtherSwap('lmsUsedSelect', 'lmsUsedOtherWrap', 'lmsUsedOtherInput', '__lmsUsedOther');
    _restoreOtherSwap('lmsPreferredSelect', 'lmsPreferredOtherWrap', 'lmsPreferredOtherInput', '__lmsPreferredOther');
    // Restore devices/lms-reasons inline inputs
    var devOtherCb = document.querySelector('[name="devices[]"][value="Others"]');
    var devOtherIn = document.querySelector('[name="devices_other"]');
    if (devOtherCb && devOtherCb.checked && devOtherIn) {
      devOtherIn.style.display = 'block';
    }
    var lmsRCb = document.querySelector('[name="lms_reasons[]"][value="Others"]');
    var lmsRIn = document.querySelector('[name="lms_reasons_other"]');
    if (lmsRCb && lmsRCb.checked && lmsRIn) {
      lmsRIn.style.display = 'block';
    }
    updateAddressPreview();
  }
  restoreDraft();
  form.addEventListener('input', saveDraft);
  form.addEventListener('change', saveDraft);
  form.addEventListener('submit', function () {
    // Do NOT clear draft here — clear only after confirmed success (done in blade).
    // This preserves in-progress data if server-side validation fails.
  });
  var optionalNames = ['suffix', 'profile_photo', 'age', 'religion_other', 'nationality_other', 'mother_contact', 'father_contact', 'lrn',
  // nullable when no_k12 is toggled
  'family_income_source_other', 'living_situation_other', 'lms_used_other', 'lms_preferred_other', 'devices_other', 'lms_reasons_other'];
  var seenRadioGroups = {};
  form.querySelectorAll('input, select, textarea').forEach(function (field) {
    if (!field.name) {
      return;
    }
    if (optionalNames.indexOf(field.name) !== -1) {
      return;
    }
    if (field.type === 'checkbox') {
      return;
    }
    if (field.type === 'radio') {
      if (!seenRadioGroups[field.name]) {
        field.required = true;
        seenRadioGroups[field.name] = true;
      }
      return;
    }
    field.required = true;
  });
  var requiredNames = {};
  form.querySelectorAll('[required]').forEach(function (field) {
    if (field.name) {
      requiredNames[field.name] = true;
    }
  });
  Object.keys(requiredNames).forEach(function (name) {
    var field = form.querySelector('[name="' + name + '"]');
    if (!field) {
      return;
    }
    var col = field.closest('.setup-col') || field.closest('.setup-col-sm') || field.closest('.setup-col-toggle');
    if (!col) {
      return;
    }
    var label = col.querySelector('.setup-label');
    if (!label || label.querySelector('.req')) {
      return;
    }
    var star = document.createElement('span');
    star.className = 'req';
    star.textContent = '*';
    label.appendChild(document.createTextNode(' '));
    label.appendChild(star);
  });
});

/***/ }),

/***/ 2:
/*!***********************************************!*\
  !*** multi ./resources/js/student-profile.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\Users\micha\Desktop\OJT\plp-demo\resources\js\student-profile.js */"./resources/js/student-profile.js");


/***/ })

/******/ });