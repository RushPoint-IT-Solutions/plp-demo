/* Curriculum File JS connection helpers */
(function () {
    var page = document.getElementById('curriculumFilePage');
    if (!page) {
        return;
    }

    var topCourse = document.getElementById('cfProgram');
    var topYear = document.getElementById('cfCurriculumYear');
    var setupCourse = document.getElementById('cfSetupProgram');
    var setupYear = document.getElementById('cfSetupCurriculumYear');
    var setupDateFrom = document.getElementById('cfSetupDateFrom');
    var setupDateTo = document.getElementById('cfSetupDateTo');
    var setupTerm = document.getElementById('cfSetupTerm');
    var setupYearLevel = document.getElementById('cfSetupYearLevel');
    var courseSearch = document.getElementById('cfCourseSearch');
    var coursePicker = document.getElementById('cfCoursePicker');
    var setupButton = document.getElementById('cfSaveSetupBtn');
    var viewListButton = document.getElementById('cfViewListBtn');
    var openPrereqButton = document.getElementById('cfOpenPrerequisitesBtn');
    var openPrereqSetupButton = document.getElementById('cfOpenPrerequisitesSetupBtn');

    var preRequisitesUrl = page.getAttribute('data-pre-requisites-url') || '';
    var selectedCourseId = String(page.getAttribute('data-selected-course-id') || '');
    var selectedCurriculumYear = String(page.getAttribute('data-selected-curriculum-year') || '');
    var successMessage = String(page.getAttribute('data-success') || '').trim();
    var errorMessage = String(page.getAttribute('data-error') || '').trim();

    var yearMap = {};
    try {
        yearMap = JSON.parse(page.getAttribute('data-course-years') || '{}') || {};
    } catch (error) {
        console.error(error);
        yearMap = {};
    }

    function hasSelect2() {
        return !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2);
    }

    function initSelect2(selectElement) {
        if (!selectElement || !hasSelect2()) {
            return;
        }

        var $select = window.jQuery(selectElement);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            width: '100%',
            placeholder: selectElement.getAttribute('data-placeholder') || 'Select option',
            allowClear: false,
            dropdownAutoWidth: true
        });
    }

    function refreshSelect2(selectElement) {
        if (!selectElement || !hasSelect2()) {
            return;
        }

        initSelect2(selectElement);
        window.jQuery(selectElement).trigger('change.select2');
    }

    function initSearchableSelects() {
        if (!hasSelect2()) {
            return;
        }

        page.querySelectorAll('select.cf-select2').forEach(function (selectElement) {
            initSelect2(selectElement);
        });
    }

    function setYearOptions(selectElement, years, preferredValue) {
        if (!selectElement) {
            return;
        }

        selectElement.innerHTML = '';

        if (!years.length) {
            var empty = document.createElement('option');
            empty.value = '';
            empty.textContent = 'No Curriculum Year';
            selectElement.appendChild(empty);
            selectElement.value = '';
            selectElement.disabled = true;
            refreshSelect2(selectElement);
            return;
        }

        selectElement.disabled = false;

        years.forEach(function (yearCode) {
            var option = document.createElement('option');
            option.value = yearCode;
            option.textContent = yearCode;
            selectElement.appendChild(option);
        });

        var preferred = preferredValue && years.indexOf(preferredValue) !== -1 ? preferredValue : years[0];
        selectElement.value = preferred;
        refreshSelect2(selectElement);
    }

    function syncYearSelector(courseSelect, yearSelect, preferredValue) {
        var courseId = courseSelect ? String(courseSelect.value || '') : '';
        setYearOptions(yearSelect, yearMap[courseId] || [], preferredValue);
    }

    function updateActionStates() {
        var hasTopYear = !!(topYear && topYear.value && !topYear.disabled);
        var hasSetupCourse = !!(setupCourse && setupCourse.value);
        var hasSetupYear = !!(setupYear && String(setupYear.value || '').trim());
        var hasSetupDates = !!(setupDateFrom && setupDateFrom.value && setupDateTo && setupDateTo.value);
        var hasSetupTerm = !!(setupTerm && setupTerm.value);
        var hasSetupYearLevel = !!(setupYearLevel && setupYearLevel.value);
        var hasSelectedCourses = !!(coursePicker && coursePicker.querySelector('input[type="checkbox"]:checked'));

        if (setupButton) {
            setupButton.disabled = !(hasSetupCourse && hasSetupYear && hasSetupDates && hasSetupTerm && hasSetupYearLevel && hasSelectedCourses);
        }

        if (viewListButton) {
            viewListButton.disabled = !hasTopYear;
        }

        if (openPrereqButton) {
            openPrereqButton.disabled = !hasTopYear;
        }

        if (openPrereqSetupButton) {
            openPrereqSetupButton.disabled = !(hasSetupCourse && hasSetupYear);
        }
    }

    function buildPrerequisiteUrl() {
        if (!preRequisitesUrl) {
            return '';
        }

        var courseId = topCourse ? String(topCourse.value || '') : '';
        var curriculumYear = topYear ? String(topYear.value || '') : '';

        if (!courseId || !curriculumYear) {
            return '';
        }

        return preRequisitesUrl + '?course_id=' + encodeURIComponent(courseId) + '&curriculum_year=' + encodeURIComponent(curriculumYear);
    }

    function navigateToPreRequisites() {
        var targetUrl = buildPrerequisiteUrl();
        if (!targetUrl) {
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Please select Program and Curriculum Year first.', 'warning');
            }
            return;
        }

        window.location.href = targetUrl;
    }

    function buildSetupPrerequisiteUrl() {
        if (!preRequisitesUrl || !setupCourse || !setupYear) {
            return '';
        }

        var courseId = String(setupCourse.value || '');
        var curriculumYear = String(setupYear.value || '').trim();
        if (!courseId || !curriculumYear) {
            return '';
        }

        return preRequisitesUrl + '?course_id=' + encodeURIComponent(courseId) + '&curriculum_year=' + encodeURIComponent(curriculumYear);
    }

    function navigateToSetupPreRequisites() {
        var targetUrl = buildSetupPrerequisiteUrl();
        if (!targetUrl) {
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Please select Program and Curriculum Year first.', 'warning');
            }
            return;
        }

        window.location.href = targetUrl;
    }

    function maybeUpdateCurriculumYearFromDates() {
        if (!setupYear || !setupDateFrom || !setupDateTo) {
            return;
        }

        var fromValue = String(setupDateFrom.value || '');
        var toValue = String(setupDateTo.value || '');
        if (!fromValue || !toValue) {
            updateActionStates();
            return;
        }

        var fromYear = fromValue.slice(0, 4);
        var toYear = toValue.slice(0, 4);
        if (/^\d{4}$/.test(fromYear) && /^\d{4}$/.test(toYear)) {
            setupYear.value = fromYear + '-' + toYear;
        }

        updateActionStates();
    }

    function filterCourseOptions() {
        if (!courseSearch || !coursePicker) {
            return;
        }

        var query = String(courseSearch.value || '').trim().toLowerCase();
        coursePicker.querySelectorAll('.cf-course-option').forEach(function (option) {
            var text = option.getAttribute('data-course-text') || '';
            option.hidden = !!query && text.indexOf(query) === -1;
        });
    }

    if (successMessage && typeof showRegistrarToast === 'function') {
        showRegistrarToast(successMessage, 'success');
    }

    if (errorMessage && typeof showRegistrarToast === 'function') {
        showRegistrarToast(errorMessage, 'error');
    }

    if (topCourse && selectedCourseId) {
        topCourse.value = selectedCourseId;
    }

    if (setupCourse && !setupCourse.value && selectedCourseId) {
        setupCourse.value = selectedCourseId;
    }

    syncYearSelector(topCourse, topYear, selectedCurriculumYear);
    initSearchableSelects();

    selectedCurriculumYear = topYear ? String(topYear.value || '') : selectedCurriculumYear;
    updateActionStates();

    if (topCourse) {
        topCourse.addEventListener('change', function () {
            selectedCourseId = String(topCourse.value || '');
            selectedCurriculumYear = '';
            syncYearSelector(topCourse, topYear, '');
            selectedCurriculumYear = topYear ? String(topYear.value || '') : '';
            updateActionStates();
        });
    }

    if (topYear) {
        topYear.addEventListener('change', function () {
            selectedCurriculumYear = String(topYear.value || '');
            updateActionStates();
        });
    }

    if (setupCourse) {
        setupCourse.addEventListener('change', function () {
            updateActionStates();
        });
    }

    if (setupYear) {
        setupYear.addEventListener('input', updateActionStates);
        setupYear.addEventListener('change', updateActionStates);
    }

    if (setupDateFrom) {
        setupDateFrom.addEventListener('change', maybeUpdateCurriculumYearFromDates);
        setupDateFrom.addEventListener('input', maybeUpdateCurriculumYearFromDates);
    }

    if (setupDateTo) {
        setupDateTo.addEventListener('change', maybeUpdateCurriculumYearFromDates);
        setupDateTo.addEventListener('input', maybeUpdateCurriculumYearFromDates);
    }

    if (setupTerm) {
        setupTerm.addEventListener('change', updateActionStates);
    }

    if (setupYearLevel) {
        setupYearLevel.addEventListener('change', updateActionStates);
    }

    if (courseSearch) {
        courseSearch.addEventListener('input', filterCourseOptions);
    }

    if (coursePicker) {
        coursePicker.addEventListener('change', updateActionStates);
    }

    if (viewListButton) {
        viewListButton.addEventListener('click', navigateToPreRequisites);
    }

    if (openPrereqButton) {
        openPrereqButton.addEventListener('click', navigateToPreRequisites);
    }

    if (openPrereqSetupButton) {
        openPrereqSetupButton.addEventListener('click', navigateToSetupPreRequisites);
    }
})();
