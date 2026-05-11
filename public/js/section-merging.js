/* Registrar > Scheduling > Section Merging */
(function () {
    var page = document.getElementById('sectionMergingPage');
    if (!page) {
        return;
    }

    var fetchUrl = page.getAttribute('data-fetch-url') || '';
    var storeUrl = page.getAttribute('data-store-url') || '';
    var csrfToken = page.getAttribute('data-csrf-token') || '';

    var state = {
        schoolYear: '',
        semester: '',
        loaded: false,
        rows: [],
        courses: [],
        source: { course: '', year: '', section: '', slot: '' },
        target: { course: '', year: '', section: '', slot: '' },
        config: {
            schoolYears: [],
            semesterMap: {},
        },
        isLoading: false,
        isMerging: false,
        fetchToken: 0,
    };

    var el = {
        schoolYear: document.getElementById('smrgSchoolYear'),
        semester: document.getElementById('smrgSemester'),
        saveBtn: document.getElementById('smrgConfigSaveBtn'),

        sourceCourse: document.getElementById('smrgSourceCourse'),
        sourceCourseSearch: document.getElementById('smrgSourceCourseSearch'),
        sourceCourseList: document.getElementById('smrgSourceCourseList'),
        sourceCourseDropdown: document.getElementById('smrgSourceCourseDropdown'),
        sourceYear: document.getElementById('smrgSourceYearLevel'),
        sourceSection: document.getElementById('smrgSourceSection'),
        sourceSubject: document.getElementById('smrgSourceSubject'),
        sourceSubjectSearch: document.getElementById('smrgSourceSubjectSearch'),
        sourceSubjectList: document.getElementById('smrgSourceSubjectList'),
        sourceSubjectDropdown: document.getElementById('smrgSourceSubjectDropdown'),
        sourceClearBtn: document.getElementById('smrgSourceClearBtn'),

        targetCourse: document.getElementById('smrgTargetCourse'),
        targetCourseSearch: document.getElementById('smrgTargetCourseSearch'),
        targetCourseList: document.getElementById('smrgTargetCourseList'),
        targetCourseDropdown: document.getElementById('smrgTargetCourseDropdown'),
        targetYear: document.getElementById('smrgTargetYearLevel'),
        targetSection: document.getElementById('smrgTargetSection'),
        targetSubject: document.getElementById('smrgTargetSubject'),
        targetSubjectSearch: document.getElementById('smrgTargetSubjectSearch'),
        targetSubjectList: document.getElementById('smrgTargetSubjectList'),
        targetSubjectDropdown: document.getElementById('smrgTargetSubjectDropdown'),
        targetClearBtn: document.getElementById('smrgTargetClearBtn'),

        cancelBtn: document.getElementById('smrgCancelBtn'),
        mergeBtn: document.getElementById('smrgMergeBtn'),

        confirmModal: document.getElementById('mergeConfirmModal'),
        successModal: document.getElementById('mergeSuccessModal'),
        confirmCancelBtn: document.getElementById('smrgConfirmCancelBtn'),
        confirmMergeBtn: document.getElementById('smrgConfirmMergeBtn'),
        successOkBtn: document.getElementById('smrgSuccessOkBtn'),
    };

    var activeSearchKey = '';

    function toInt(value, fallback) {
        var parsed = parseInt(String(value), 10);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function normalizeText(value) {
        return String(value || '').trim();
    }

    function normalizeCompare(value) {
        return normalizeText(value).replace(/\s+/g, ' ').toLowerCase();
    }

    function normalizeSemesterValue(value) {
        var normalized = normalizeCompare(value);
        if (normalized === '') {
            return '';
        }

        if (normalized.indexOf('summer') !== -1) {
            return 'Summer';
        }

        if (normalized.indexOf('second') !== -1 || normalized.indexOf('2nd') !== -1 || normalized === '2') {
            return 'Second';
        }

        if (normalized.indexOf('first') !== -1 || normalized.indexOf('1st') !== -1 || normalized === '1') {
            return 'First';
        }

        return '';
    }

    function semesterWeight(value) {
        var normalized = normalizeSemesterValue(value);
        if (normalized === 'First') {
            return 1;
        }

        if (normalized === 'Second') {
            return 2;
        }

        if (normalized === 'Summer') {
            return 3;
        }

        return 4;
    }

    function normalizeSemesterList(values) {
        var normalized = [];

        (Array.isArray(values) ? values : []).forEach(function (value) {
            var semester = normalizeSemesterValue(value);
            if (semester === '' || normalized.indexOf(semester) !== -1) {
                return;
            }

            normalized.push(semester);
        });

        normalized.sort(function (left, right) {
            return semesterWeight(left) - semesterWeight(right);
        });

        return normalized;
    }

    function notify(message, level) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast(message, level || 'info');
            return;
        }

        if (level === 'error' || level === 'warning') {
            alert(message);
            return;
        }

        console.log(message);
    }

    function setModal(modalEl, visible) {
        if (!modalEl) {
            return;
        }

        modalEl.style.display = visible ? 'flex' : 'none';
    }

    function getErrorMessage(payload, fallbackMessage) {
        if (payload && payload.errors && typeof payload.errors === 'object') {
            var firstField = Object.keys(payload.errors)[0];
            if (firstField && payload.errors[firstField] && payload.errors[firstField][0]) {
                return payload.errors[firstField][0];
            }
        }

        if (payload && payload.message) {
            return payload.message;
        }

        return fallbackMessage;
    }

    function updateButtonState() {
        var busy = state.isLoading || state.isMerging;

        if (el.saveBtn) {
            el.saveBtn.disabled = busy;
        }

        if (el.mergeBtn) {
            el.mergeBtn.disabled = busy;
        }

        if (el.confirmMergeBtn) {
            el.confirmMergeBtn.disabled = busy;
            el.confirmMergeBtn.textContent = state.isMerging ? 'Merging...' : 'Yes, Merge Same Course & Year';
        }
    }

    function jsonRequest(url, options) {
        return fetch(url, options).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (payload) {
                if (!response.ok) {
                    throw payload;
                }

                return payload;
            });
        });
    }

    function getJson(url) {
        return jsonRequest(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
    }

    function postJson(url, payload) {
        return jsonRequest(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload || {}),
            credentials: 'same-origin',
        });
    }

    function sideState(sideKey) {
        return sideKey === 'source' ? state.source : state.target;
    }

    function resetSide(sideKey) {
        var side = sideState(sideKey);
        side.course = '';
        side.year = '';
        side.section = '';
        side.slot = '';
    }

    function syncConfig() {
        state.schoolYear = el.schoolYear ? normalizeText(el.schoolYear.value) : '';
        state.semester = el.semester ? normalizeSemesterValue(el.semester.value) : '';
    }

    function getSemestersForYear(yearValue) {
        var year = normalizeText(yearValue);
        if (!year || !state.config.semesterMap || !state.config.semesterMap[year]) {
            return [];
        }

        var rawList = state.config.semesterMap[year];
        if (!Array.isArray(rawList)) {
            return [];
        }

        return normalizeSemesterList(rawList);
    }

    function renderSimpleSelect(selectEl, placeholder, values, selectedValue) {
        if (!selectEl) {
            return '';
        }

        var html = '<option value="">' + escapeHtml(placeholder) + '</option>';
        (values || []).forEach(function (value) {
            html += '<option value="' + escapeHtml(value) + '">' + escapeHtml(value) + '</option>';
        });

        selectEl.innerHTML = html;
        selectEl.value = selectedValue || '';

        if (selectEl.value !== (selectedValue || '')) {
            selectEl.value = '';
        }

        refreshListboxSelect(selectEl);

        return normalizeText(selectEl.value);
    }

    function refreshListboxSelect(selectEl) {
        if (!selectEl || !selectEl.closest) {
            return;
        }

        var wrapper = selectEl.closest('[data-listbox-select]');
        if (!wrapper || typeof document === 'undefined' || typeof document.dispatchEvent !== 'function') {
            return;
        }

        if (typeof window.CustomEvent === 'function') {
            document.dispatchEvent(new CustomEvent('registrar:listbox:refresh', {
                detail: { target: wrapper },
            }));
            return;
        }

        if (typeof document.createEvent === 'function') {
            var fallbackEvent = document.createEvent('CustomEvent');
            fallbackEvent.initCustomEvent('registrar:listbox:refresh', false, false, { target: wrapper });
            document.dispatchEvent(fallbackEvent);
        }
    }

    function renderConfigOptions(configPayload) {
        var config = configPayload && typeof configPayload === 'object' ? configPayload : {};

        var schoolYears = Array.isArray(config.school_years)
            ? config.school_years.map(function (item) {
                return normalizeText(item);
            }).filter(function (item) {
                return item !== '';
            })
            : [];

        var semesterMap = {};
        if (config.semester_map && typeof config.semester_map === 'object') {
            Object.keys(config.semester_map).forEach(function (year) {
                var normalizedYear = normalizeText(year);
                if (!normalizedYear) {
                    return;
                }

                var list = config.semester_map[year];
                if (!Array.isArray(list)) {
                    return;
                }

                semesterMap[normalizedYear] = normalizeSemesterList(list);
            });
        }

        state.config.schoolYears = schoolYears;
        state.config.semesterMap = semesterMap;

        var selectedYear = normalizeText(config.selected_school_year || state.schoolYear);
        if (!selectedYear || schoolYears.indexOf(selectedYear) === -1) {
            selectedYear = schoolYears.length ? schoolYears[0] : selectedYear;
        }

        var yearSemesters = getSemestersForYear(selectedYear);
        var selectedSemester = normalizeSemesterValue(config.selected_semester || state.semester);
        if (!selectedSemester || yearSemesters.indexOf(selectedSemester) === -1) {
            selectedSemester = yearSemesters.length ? yearSemesters[0] : selectedSemester;
        }

        state.schoolYear = renderSimpleSelect(el.schoolYear, '- Select School Year -', schoolYears, selectedYear);
        state.semester = renderSimpleSelect(el.semester, '- Select Semester -', yearSemesters, selectedSemester);
    }

    function findRow(slotId) {
        var id = toInt(slotId, 0);
        if (id <= 0) {
            return null;
        }

        for (var i = 0; i < state.rows.length; i++) {
            if (toInt(state.rows[i].id, 0) === id) {
                return state.rows[i];
            }
        }

        return null;
    }

    function candidateRows(sideKey) {
        var isSource = sideKey === 'source';
        var oppositeSlot = isSource ? toInt(state.target.slot, 0) : toInt(state.source.slot, 0);
        var oppositeSide = isSource ? state.target : state.source;
        var oppositeRow = findRow(oppositeSlot);
        var requiredCourse = oppositeRow ? toInt(oppositeRow.course_id, 0) : toInt(oppositeSide.course, 0);
        var requiredYear = oppositeRow ? toInt(oppositeRow.year_level, 0) : toInt(oppositeSide.year, 0);

        return state.rows.filter(function (row) {
            if (isSource && !row.source_available) {
                return false;
            }

            if (oppositeSlot > 0 && toInt(row.id, 0) === oppositeSlot) {
                return false;
            }

            if (requiredCourse > 0 && toInt(row.course_id, 0) !== requiredCourse) {
                return false;
            }

            if (requiredYear > 0 && toInt(row.year_level, 0) !== requiredYear) {
                return false;
            }

            return true;
        });
    }

    function rowsByCourse(sideKey) {
        var side = sideState(sideKey);
        var courseId = toInt(side.course, 0);
        var rows = candidateRows(sideKey);

        if (courseId > 0) {
            rows = rows.filter(function (row) {
                return toInt(row.course_id, 0) === courseId;
            });
        }

        return rows;
    }

    function rowsByYear(sideKey) {
        var side = sideState(sideKey);
        var yearLevel = toInt(side.year, 0);
        var rows = rowsByCourse(sideKey);

        if (yearLevel > 0) {
            rows = rows.filter(function (row) {
                return toInt(row.year_level, 0) === yearLevel;
            });
        }

        return rows;
    }

    function rowsBySection(sideKey) {
        var side = sideState(sideKey);
        var sectionLabel = String(side.section || '').trim();
        var rows = rowsByYear(sideKey);

        if (sectionLabel !== '') {
            rows = rows.filter(function (row) {
                return String(row.section || '').trim() === sectionLabel;
            });
        }

        return rows;
    }

    function uniqueMapOptions(rows, keyFn, labelFn) {
        var map = {};

        rows.forEach(function (row) {
            var key = keyFn(row);
            if (!key || map[key]) {
                return;
            }

            map[key] = {
                value: key,
                label: labelFn(row),
            };
        });

        return Object.keys(map).map(function (key) {
            return map[key];
        }).sort(function (a, b) {
            if (a.label < b.label) {
                return -1;
            }

            if (a.label > b.label) {
                return 1;
            }

            return 0;
        });
    }

    function normalizeCourseOption(item) {
        if (!item || typeof item !== 'object') {
            return null;
        }

        var id = toInt(item.id || item.value, 0);
        if (id <= 0) {
            return null;
        }

        var code = normalizeText(item.code);
        var name = normalizeText(item.name);
        var label = normalizeText(item.label);

        if (label === '') {
            label = code;
        }

        if (label === '' && name !== '') {
            label = name;
        }

        if (label === '' || label === code) {
            label = code !== '' && name !== '' ? (code + ' - ' + name) : label;
        }

        if (label === '') {
            label = 'Program #' + String(id);
        }

        return {
            value: String(id),
            label: label,
            code: code,
            name: name,
        };
    }

    function courseOptionsFromRows(sideKey) {
        return uniqueMapOptions(candidateRows(sideKey), function (row) {
            var id = toInt(row.course_id, 0);
            return id > 0 ? String(id) : '';
        }, function (row) {
            var label = String(row.course_label || '').trim();
            return label !== '' ? label : ('Program #' + String(row.course_id || ''));
        });
    }

    function courseOptions(sideKey) {
        var fallbackOptions = courseOptionsFromRows(sideKey);

        if (!Array.isArray(state.courses) || state.courses.length === 0) {
            return fallbackOptions;
        }

        var allowedCourseIds = {};
        candidateRows(sideKey).forEach(function (row) {
            var id = toInt(row.course_id, 0);
            if (id > 0) {
                allowedCourseIds[String(id)] = true;
            }
        });

        var selectedCourse = normalizeText(sideState(sideKey).course);
        if (selectedCourse !== '') {
            allowedCourseIds[selectedCourse] = true;
        }

        var catalogOptions = state.courses.filter(function (option) {
            return !!allowedCourseIds[String(option.value || '')];
        });

        if (catalogOptions.length === 0) {
            return fallbackOptions;
        }

        return catalogOptions;
    }

    function yearOptions(sideKey) {
        return uniqueMapOptions(rowsByCourse(sideKey), function (row) {
            var value = toInt(row.year_level, 0);
            return value > 0 ? String(value) : '';
        }, function (row) {
            return 'Year ' + String(row.year_level);
        });
    }

    function sectionOptions(sideKey) {
        return uniqueMapOptions(rowsByYear(sideKey), function (row) {
            return String(row.section || '').trim();
        }, function (row) {
            return String(row.section || '').trim();
        });
    }

    function subjectOptions(sideKey) {
        return rowsBySection(sideKey).map(function (row) {
            var enrolled = toInt(row.enrolled_slots, 0);
            var total = toInt(row.total_slots, 0);
            var sectionLabel = normalizeText(row.section) || 'Section';
            var subjectLabel = normalizeText(row.subject) || 'Subject';

            return {
                value: String(row.id),
                label: sectionLabel + ' | ' + subjectLabel + ' (' + enrolled + '/' + total + ')',
            };
        }).sort(function (a, b) {
            if (a.label < b.label) {
                return -1;
            }

            if (a.label > b.label) {
                return 1;
            }

            return 0;
        });
    }

    function renderSelect(selectEl, placeholder, options, selectedValue) {
        if (!selectEl) {
            return '';
        }

        var html = '<option value="">' + escapeHtml(placeholder) + '</option>';
        options.forEach(function (opt) {
            html += '<option value="' + escapeHtml(opt.value) + '">' + escapeHtml(opt.label) + '</option>';
        });

        selectEl.innerHTML = html;
        selectEl.value = selectedValue || '';

        if (selectEl.value !== (selectedValue || '')) {
            selectEl.value = '';
        }

        refreshListboxSelect(selectEl);

        return selectEl.value;
    }

    function buildDatalistFromSelect(selectEl, inputEl, listEl, selectedValue) {
        if (!selectEl || !inputEl || !listEl) {
            return;
        }

        var options = Array.prototype.slice.call(selectEl.options || []);
        var selectedText = '';
        var html = '';

        options.forEach(function (option) {
            var optionValue = normalizeText(option.value);
            if (optionValue === '') {
                return;
            }

            var optionLabel = normalizeText(option.textContent || option.innerText || '');
            html += '<option value="' + escapeHtml(optionLabel) + '"></option>';

            if (optionValue === normalizeText(selectedValue)) {
                selectedText = optionLabel;
            }
        });

        listEl.innerHTML = html;
        inputEl.value = selectedText;
    }

    function getSearchFieldElements(sideKey, fieldKey) {
        var isSource = sideKey === 'source';

        if (fieldKey === 'course') {
            return {
                key: sideKey + '-course',
                input: isSource ? el.sourceCourseSearch : el.targetCourseSearch,
                select: isSource ? el.sourceCourse : el.targetCourse,
                dropdown: isSource ? el.sourceCourseDropdown : el.targetCourseDropdown,
            };
        }

        if (fieldKey === 'subject') {
            return {
                key: sideKey + '-subject',
                input: isSource ? el.sourceSubjectSearch : el.targetSubjectSearch,
                select: isSource ? el.sourceSubject : el.targetSubject,
                dropdown: isSource ? el.sourceSubjectDropdown : el.targetSubjectDropdown,
            };
        }

        return null;
    }

    function listSearchOptions(selectEl) {
        if (!selectEl) {
            return [];
        }

        return Array.prototype.slice.call(selectEl.options || [])
            .map(function (option) {
                var value = normalizeText(option.value);
                var label = normalizeText(option.textContent || option.innerText || '');

                return {
                    value: value,
                    label: label,
                    normalized: normalizeCompare(label),
                };
            })
            .filter(function (option) {
                return option.value !== '' && option.label !== '';
            });
    }

    function setSearchDropdownState(fieldElements, shouldOpen) {
        if (!fieldElements || !fieldElements.dropdown) {
            return;
        }

        fieldElements.dropdown.classList.toggle('is-open', !!shouldOpen);

        if (shouldOpen) {
            activeSearchKey = fieldElements.key;
            return;
        }

        if (activeSearchKey === fieldElements.key) {
            activeSearchKey = '';
        }
    }

    function closeSearchDropdowns() {
        ['source', 'target'].forEach(function (sideKey) {
            ['course', 'subject'].forEach(function (fieldKey) {
                var fieldElements = getSearchFieldElements(sideKey, fieldKey);
                setSearchDropdownState(fieldElements, false);
            });
        });

        activeSearchKey = '';
    }

    function renderSearchDropdown(sideKey, fieldKey, openMenu) {
        var fieldElements = getSearchFieldElements(sideKey, fieldKey);
        if (!fieldElements || !fieldElements.input || !fieldElements.select || !fieldElements.dropdown) {
            return;
        }

        var query = normalizeCompare(fieldElements.input.value);
        var options = listSearchOptions(fieldElements.select).filter(function (option) {
            if (!query) {
                return true;
            }

            return option.normalized.indexOf(query) !== -1;
        });

        var selectedValue = normalizeText(fieldElements.select.value);
        var html = '';

        if (options.length === 0) {
            html = '<div class="smrg-search-empty">'
                + (fieldKey === 'course' ? 'No matching programs found.' : 'No matching sections found.')
                + '</div>';
        } else {
            options.forEach(function (option) {
                var selectedClass = option.value === selectedValue ? ' is-selected' : '';
                html += '<button type="button" class="smrg-search-option' + selectedClass + '" data-side="'
                    + escapeHtml(sideKey)
                    + '" data-field="'
                    + escapeHtml(fieldKey)
                    + '" data-value="'
                    + escapeHtml(option.value)
                    + '">' + escapeHtml(option.label) + '</button>';
            });
        }

        fieldElements.dropdown.innerHTML = html;

        var shouldOpen = !!openMenu || activeSearchKey === fieldElements.key;
        setSearchDropdownState(fieldElements, shouldOpen);
    }

    function applySearchSelectionByValue(sideKey, fieldKey, selectedValue) {
        var side = sideState(sideKey);
        var normalizedValue = normalizeText(selectedValue);

        if (fieldKey === 'course') {
            side.course = normalizedValue;
            side.year = '';
            side.section = '';
            side.slot = '';
            renderPage();
            return;
        }

        if (fieldKey === 'subject') {
            side.slot = normalizedValue;
            renderPage();
        }
    }

    function bindSearchField(sideKey, fieldKey) {
        var fieldElements = getSearchFieldElements(sideKey, fieldKey);
        if (!fieldElements || !fieldElements.input || !fieldElements.dropdown) {
            return;
        }

        if (fieldElements.input.dataset.smrgSearchBound === '1') {
            return;
        }

        // Disable native datalist popup so only the themed custom dropdown is shown.
        fieldElements.input.removeAttribute('list');

        fieldElements.input.dataset.smrgSearchBound = '1';

        fieldElements.input.addEventListener('focus', function () {
            renderSearchDropdown(sideKey, fieldKey, true);
        });

        fieldElements.input.addEventListener('click', function () {
            renderSearchDropdown(sideKey, fieldKey, true);
        });

        fieldElements.input.addEventListener('change', function () {
            setSearchDropdownState(fieldElements, false);
            applySearchSelection(sideKey, fieldKey, fieldElements.input.value);
        });

        fieldElements.input.addEventListener('input', function () {
            if (normalizeText(fieldElements.input.value) === '') {
                applySearchSelectionByValue(sideKey, fieldKey, '');
            }

            renderSearchDropdown(sideKey, fieldKey, true);
        });

        fieldElements.input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setSearchDropdownState(fieldElements, false);
                return;
            }

            if (event.key === 'Enter') {
                var firstOption = fieldElements.dropdown.querySelector('.smrg-search-option');
                if (firstOption) {
                    event.preventDefault();
                    applySearchSelectionByValue(sideKey, fieldKey, firstOption.getAttribute('data-value'));
                    setSearchDropdownState(fieldElements, false);
                    return;
                }

                applySearchSelection(sideKey, fieldKey, fieldElements.input.value);
            }
        });

        fieldElements.input.addEventListener('blur', function () {
            window.setTimeout(function () {
                var active = document.activeElement;
                if (active && fieldElements.dropdown.contains(active)) {
                    return;
                }

                setSearchDropdownState(fieldElements, false);
                applySearchSelection(sideKey, fieldKey, fieldElements.input.value);
            }, 120);
        });

        fieldElements.dropdown.addEventListener('mousedown', function (event) {
            event.preventDefault();
        });

        fieldElements.dropdown.addEventListener('click', function (event) {
            var optionBtn = event.target.closest('.smrg-search-option');
            if (!optionBtn) {
                return;
            }

            event.preventDefault();
            applySearchSelectionByValue(sideKey, fieldKey, optionBtn.getAttribute('data-value'));
            setSearchDropdownState(fieldElements, false);
        });
    }

    function findOptionByLabel(selectEl, typedLabel) {
        if (!selectEl) {
            return null;
        }

        var normalizedLabel = normalizeCompare(typedLabel);
        if (!normalizedLabel) {
            return null;
        }

        var options = Array.prototype.slice.call(selectEl.options || []);
        var exactMatch = null;
        var partialMatches = [];

        options.forEach(function (option) {
            var optionValue = normalizeText(option.value);
            if (optionValue === '') {
                return;
            }

            var optionLabel = normalizeCompare(option.textContent || option.innerText || '');
            if (!optionLabel) {
                return;
            }

            if (optionLabel === normalizedLabel) {
                exactMatch = option;
                return;
            }

            if (optionLabel.indexOf(normalizedLabel) !== -1) {
                partialMatches.push(option);
            }
        });

        if (exactMatch) {
            return exactMatch;
        }

        if (partialMatches.length === 1) {
            return partialMatches[0];
        }

        return null;
    }

    function applySearchSelection(sideKey, fieldKey, typedLabel) {
        var normalizedTyped = normalizeText(typedLabel);

        if (fieldKey === 'course') {
            var courseSelect = sideKey === 'source' ? el.sourceCourse : el.targetCourse;
            var courseOption = findOptionByLabel(courseSelect, normalizedTyped);

            applySearchSelectionByValue(sideKey, fieldKey, courseOption ? courseOption.value : '');
            return;
        }

        if (fieldKey === 'subject') {
            var subjectSelect = sideKey === 'source' ? el.sourceSubject : el.targetSubject;
            var subjectOption = findOptionByLabel(subjectSelect, normalizedTyped);

            applySearchSelectionByValue(sideKey, fieldKey, subjectOption ? subjectOption.value : '');
        }
    }

    function renderSide(sideKey) {
        var isSource = sideKey === 'source';
        var side = sideState(sideKey);

        var courseEl = isSource ? el.sourceCourse : el.targetCourse;
        var courseSearchEl = isSource ? el.sourceCourseSearch : el.targetCourseSearch;
        var courseListEl = isSource ? el.sourceCourseList : el.targetCourseList;
        var yearEl = isSource ? el.sourceYear : el.targetYear;
        var sectionEl = isSource ? el.sourceSection : el.targetSection;
        var subjectEl = isSource ? el.sourceSubject : el.targetSubject;
        var subjectSearchEl = isSource ? el.sourceSubjectSearch : el.targetSubjectSearch;
        var subjectListEl = isSource ? el.sourceSubjectList : el.targetSubjectList;

        side.course = renderSelect(courseEl, '-select Program-', courseOptions(sideKey), side.course);
        side.year = renderSelect(yearEl, '-select Level-', yearOptions(sideKey), side.year);
        side.section = renderSelect(sectionEl, '-select Sec-', sectionOptions(sideKey), side.section);
        side.slot = renderSelect(subjectEl, '-select Subject-', subjectOptions(sideKey), side.slot);

        buildDatalistFromSelect(courseEl, courseSearchEl, courseListEl, side.course);
        buildDatalistFromSelect(subjectEl, subjectSearchEl, subjectListEl, side.slot);

        renderSearchDropdown(sideKey, 'course', false);
        renderSearchDropdown(sideKey, 'subject', false);
    }

    function renderPage() {
        renderSide('source');
        renderSide('target');
        updateButtonState();
    }

    function validateSelection(showToast) {
        if (!state.loaded || state.rows.length === 0) {
            if (showToast) {
                notify('Please save system configuration first.', 'warning');
            }

            return false;
        }

        var sourceId = toInt(state.source.slot, 0);
        var targetId = toInt(state.target.slot, 0);

        if (sourceId <= 0 || targetId <= 0) {
            if (showToast) {
                notify('Please select both source and target subjects.', 'warning');
            }

            return false;
        }

        if (sourceId === targetId) {
            if (showToast) {
                notify('Source and target sections must be different.', 'warning');
            }

            return false;
        }

        var sourceRow = findRow(sourceId);
        var targetRow = findRow(targetId);
        if (!sourceRow || !targetRow) {
            if (showToast) {
                notify('Selected section details are no longer available. Reload configuration and try again.', 'warning');
            }

            return false;
        }

        if (toInt(sourceRow.enrolled_slots, 0) <= 0) {
            if (showToast) {
                notify('Source section has no enrolled students to merge.', 'warning');
            }

            return false;
        }

        if (toInt(sourceRow.course_id, 0) !== toInt(targetRow.course_id, 0)) {
            if (showToast) {
                notify('Source and target must belong to the same program.', 'warning');
            }

            return false;
        }

        if (toInt(sourceRow.year_level, 0) !== toInt(targetRow.year_level, 0)) {
            if (showToast) {
                notify('Source and target must belong to the same year level.', 'warning');
            }

            return false;
        }

        if (String(sourceRow.subject || '').trim().toLowerCase() !== String(targetRow.subject || '').trim().toLowerCase()) {
            if (showToast) {
                notify('Source and target must have the same subject before merging.', 'warning');
            }

            return false;
        }

        return true;
    }

    function loadConfig(showSuccessToast) {
        syncConfig();

        if (!fetchUrl) {
            notify('Section merging data endpoint is not configured.', 'error');
            return;
        }

        var token = ++state.fetchToken;
        state.isLoading = true;
        updateButtonState();

        var params = [];
        if (state.schoolYear !== '') {
            params.push('school_year=' + encodeURIComponent(state.schoolYear));
        }

        if (state.semester !== '') {
            params.push('semester=' + encodeURIComponent(state.semester));
        }

        var url = fetchUrl;
        if (params.length > 0) {
            url += (fetchUrl.indexOf('?') === -1 ? '?' : '&') + params.join('&');
        }

        getJson(url)
            .then(function (payload) {
                if (token !== state.fetchToken) {
                    return;
                }

                var configPayload = payload && payload.options ? payload.options.config : null;
                renderConfigOptions(configPayload);
                syncConfig();

                state.rows = Array.isArray(payload.rows) ? payload.rows : [];
                state.courses = payload && payload.options && Array.isArray(payload.options.courses)
                    ? payload.options.courses
                        .map(function (item) {
                            return normalizeCourseOption(item);
                        })
                        .filter(function (item) {
                            return item !== null;
                        })
                    : [];
                state.loaded = true;
                resetSide('source');
                resetSide('target');
                renderPage();

                if (showSuccessToast) {
                    notify('Configuration saved. ' + state.rows.length + ' slot entries loaded.', 'success');
                }
            })
            .catch(function (payload) {
                if (token !== state.fetchToken) {
                    return;
                }

                state.loaded = false;
                state.rows = [];
                state.courses = [];
                resetSide('source');
                resetSide('target');
                renderPage();
                notify(getErrorMessage(payload, 'Unable to load section data.'), 'error');
            })
            .finally(function () {
                if (token === state.fetchToken) {
                    state.isLoading = false;
                    updateButtonState();
                }
            });
    }

    function openConfirmModal() {
        if (!validateSelection(true)) {
            return;
        }

        setModal(el.confirmModal, true);
    }

    function closeConfirmModal() {
        setModal(el.confirmModal, false);
    }

    function closeSuccessModal() {
        setModal(el.successModal, false);
    }

    function runMerge() {
        if (state.isMerging) {
            return;
        }

        if (!validateSelection(true)) {
            closeConfirmModal();
            return;
        }

        if (!storeUrl) {
            notify('Section merging endpoint is not configured.', 'error');
            return;
        }

        state.isMerging = true;
        updateButtonState();

        postJson(storeUrl, {
            school_year: state.schoolYear,
            semester: state.semester,
            source_slot_monitoring_id: toInt(state.source.slot, 0),
            target_slot_monitoring_id: toInt(state.target.slot, 0),
        })
            .then(function (payload) {
                closeConfirmModal();
                setModal(el.successModal, true);
                notify('Section merge completed (' + toInt(payload.merged_student_count, 0) + ' students moved).', 'success');
                loadConfig(false);
            })
            .catch(function (payload) {
                notify(getErrorMessage(payload, 'Section merge failed.'), 'error');
            })
            .finally(function () {
                state.isMerging = false;
                updateButtonState();
            });
    }

    function clearSelections() {
        closeConfirmModal();
        closeSuccessModal();
        resetSide('source');
        resetSide('target');
        renderPage();
        notify('Section selections cleared.', 'info');
    }

    function bindEvents() {
        if (el.saveBtn) {
            el.saveBtn.addEventListener('click', function () {
                loadConfig(true);
            });
        }

        if (el.schoolYear) {
            el.schoolYear.addEventListener('change', function () {
                state.schoolYear = normalizeText(this.value);

                var semesters = getSemestersForYear(state.schoolYear);
                var activeSemester = normalizeSemesterValue(state.semester);
                var selectedSemester = semesters.indexOf(activeSemester) !== -1
                    ? activeSemester
                    : (semesters.length ? semesters[0] : '');

                state.semester = renderSimpleSelect(el.semester, '- Select Semester -', semesters, selectedSemester);
            });
        }

        if (el.semester) {
            el.semester.addEventListener('change', function () {
                state.semester = normalizeSemesterValue(this.value);
            });
        }

        if (el.sourceCourse) {
            el.sourceCourse.addEventListener('change', function () {
                state.source.course = normalizeText(this.value);
                state.source.year = '';
                state.source.section = '';
                state.source.slot = '';
                renderPage();
            });
        }

        bindSearchField('source', 'course');

        if (el.sourceYear) {
            el.sourceYear.addEventListener('change', function () {
                state.source.year = normalizeText(this.value);
                state.source.section = '';
                state.source.slot = '';
                renderPage();
            });
        }

        if (el.sourceSection) {
            el.sourceSection.addEventListener('change', function () {
                state.source.section = normalizeText(this.value);
                state.source.slot = '';
                renderPage();
            });
        }

        if (el.sourceSubject) {
            el.sourceSubject.addEventListener('change', function () {
                state.source.slot = normalizeText(this.value);
                renderPage();
            });
        }

        bindSearchField('source', 'subject');

        if (el.targetCourse) {
            el.targetCourse.addEventListener('change', function () {
                state.target.course = normalizeText(this.value);
                state.target.year = '';
                state.target.section = '';
                state.target.slot = '';
                renderPage();
            });
        }

        bindSearchField('target', 'course');

        if (el.targetYear) {
            el.targetYear.addEventListener('change', function () {
                state.target.year = normalizeText(this.value);
                state.target.section = '';
                state.target.slot = '';
                renderPage();
            });
        }

        if (el.targetSection) {
            el.targetSection.addEventListener('change', function () {
                state.target.section = normalizeText(this.value);
                state.target.slot = '';
                renderPage();
            });
        }

        if (el.targetSubject) {
            el.targetSubject.addEventListener('change', function () {
                state.target.slot = normalizeText(this.value);
                renderPage();
            });
        }

        bindSearchField('target', 'subject');

        if (el.sourceClearBtn) {
            el.sourceClearBtn.addEventListener('click', function () {
                resetSide('source');
                renderPage();
            });
        }

        if (el.targetClearBtn) {
            el.targetClearBtn.addEventListener('click', function () {
                resetSide('target');
                renderPage();
            });
        }

        if (el.cancelBtn) {
            el.cancelBtn.addEventListener('click', clearSelections);
        }

        if (el.mergeBtn) {
            el.mergeBtn.addEventListener('click', openConfirmModal);
        }

        if (el.confirmCancelBtn) {
            el.confirmCancelBtn.addEventListener('click', closeConfirmModal);
        }

        if (el.confirmMergeBtn) {
            el.confirmMergeBtn.addEventListener('click', runMerge);
        }

        if (el.successOkBtn) {
            el.successOkBtn.addEventListener('click', closeSuccessModal);
        }

        document.addEventListener('click', function (event) {
            var target = event && event.target;
            if (!target || typeof target.closest !== 'function' || !target.closest('.smrg-search-wrap')) {
                closeSearchDropdowns();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event && event.key === 'Escape') {
                closeSearchDropdowns();
            }
        });
    }

    function init() {
        bindEvents();
        resetSide('source');
        resetSide('target');
        renderPage();

        loadConfig(false);
    }

    init();
})();
