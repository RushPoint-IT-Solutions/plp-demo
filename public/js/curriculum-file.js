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
    var viewListModal = document.getElementById('cfViewListModal');
    var editSubjectModal = document.getElementById('cfEditSubjectModal');
    var editSubjectForm = document.getElementById('cfEditSubjectForm');
    var editSubjectLabel = document.getElementById('cfEditSubjectLabel');
    var editSubjectTerm = document.getElementById('cfEditTerm');
    var editSubjectYearLevel = document.getElementById('cfEditYearLevel');
    var editSubjectUnits = document.getElementById('cfEditUnits');

    var preRequisitesUrl = page.getAttribute('data-pre-requisites-url') || '';
    var curriculumFileUrl = page.getAttribute('data-curriculum-file-url') || '';
    var selectedCourseId = String(page.getAttribute('data-selected-course-id') || '');
    var selectedCurriculumYear = String(page.getAttribute('data-selected-curriculum-year') || '');
    var loadedCourseId = selectedCourseId;
    var loadedCurriculumYear = selectedCurriculumYear;
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

    function openViewListModal() {
        if (!viewListModal) {
            return;
        }
        viewListModal.style.display = 'flex';
        document.body.classList.add('cf-modal-open');
    }

    function closeViewListModal() {
        if (!viewListModal) {
            return;
        }
        viewListModal.style.display = 'none';
        document.body.classList.remove('cf-modal-open');
    }

    window.closeCfViewListModal = closeViewListModal;

    function openEditSubjectModal(button) {
        if (!editSubjectModal || !editSubjectForm) {
            return;
        }

        editSubjectForm.action = button.getAttribute('data-edit-url') || '';

        if (editSubjectLabel) {
            var code = button.getAttribute('data-code') || '';
            var title = button.getAttribute('data-title') || '';
            editSubjectLabel.textContent = (code ? code + ' - ' : '') + title;
        }

        if (editSubjectTerm) {
            editSubjectTerm.value = button.getAttribute('data-term-id') || '';
        }

        if (editSubjectYearLevel) {
            editSubjectYearLevel.value = button.getAttribute('data-year-block-id') || '';
        }

        if (editSubjectUnits) {
            editSubjectUnits.value = button.getAttribute('data-units') || '';
        }

        editSubjectModal.style.display = 'flex';
        document.body.classList.add('cf-modal-open');
    }

    function closeEditSubjectModal() {
        if (!editSubjectModal) {
            return;
        }
        editSubjectModal.style.display = 'none';
        document.body.classList.remove('cf-modal-open');
    }

    window.closeCfEditSubjectModal = closeEditSubjectModal;

    function navigateToViewList() {
        var courseId = topCourse ? String(topCourse.value || '') : '';
        var curriculumYear = topYear ? String(topYear.value || '') : '';

        if (!courseId || !curriculumYear) {
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Please select Program and Curriculum Year first.', 'warning');
            }
            return;
        }

        if (courseId === loadedCourseId && curriculumYear === loadedCurriculumYear) {
            openViewListModal();
            return;
        }

        if (!curriculumFileUrl) {
            return;
        }

        window.location.href = curriculumFileUrl
            + '?course_id=' + encodeURIComponent(courseId)
            + '&curriculum_year=' + encodeURIComponent(curriculumYear)
            + '&open_view_list=1';
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

    function bindChange(element, handler) {
        if (!element) {
            return;
        }

        // Select2 updates the underlying <select> through jQuery's synthetic
        // event system, which does not dispatch a native 'change' event for
        // <select> elements. A plain addEventListener('change', ...) never
        // sees those updates, so bind through jQuery when it's available.
        if (hasSelect2()) {
            window.jQuery(element).on('change', handler);
        } else {
            element.addEventListener('change', handler);
        }
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

    bindChange(topCourse, function () {
        selectedCourseId = String(topCourse.value || '');
        selectedCurriculumYear = '';
        syncYearSelector(topCourse, topYear, '');
        selectedCurriculumYear = topYear ? String(topYear.value || '') : '';
        updateActionStates();
    });

    bindChange(topYear, function () {
        selectedCurriculumYear = String(topYear.value || '');
        updateActionStates();
    });

    bindChange(setupCourse, function () {
        updateActionStates();
    });

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

    bindChange(setupTerm, updateActionStates);

    bindChange(setupYearLevel, updateActionStates);

    if (courseSearch) {
        courseSearch.addEventListener('input', filterCourseOptions);
    }

    if (coursePicker) {
        coursePicker.addEventListener('change', updateActionStates);
    }

    if (viewListButton) {
        viewListButton.addEventListener('click', navigateToViewList);
    }

    if (openPrereqButton) {
        openPrereqButton.addEventListener('click', navigateToPreRequisites);
    }

    if (openPrereqSetupButton) {
        openPrereqSetupButton.addEventListener('click', navigateToSetupPreRequisites);
    }

    if (viewListModal) {
        viewListModal.addEventListener('click', function (event) {
            var editButton = event.target.closest('.cf-row-edit-btn');
            if (editButton) {
                openEditSubjectModal(editButton);
            }
        });
    }

    if (editSubjectModal) {
        editSubjectModal.addEventListener('click', function (event) {
            if (event.target === editSubjectModal) {
                closeEditSubjectModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeEditSubjectModal();
            closeViewListModal();
        }
    });

    if (window.location.search.indexOf('open_view_list=1') !== -1) {
        openViewListModal();

        if (window.history && window.history.replaceState) {
            var cleanUrl = window.location.pathname + window.location.search.replace(/[?&]open_view_list=1/, '').replace(/^&/, '?');
            window.history.replaceState(null, '', cleanUrl);
        }
    }
})();
