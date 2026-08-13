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

    var termYearSubjectMap = {};
    try {
        termYearSubjectMap = JSON.parse(page.getAttribute('data-term-year-subjects') || '{}') || {};
    } catch (error) {
        console.error(error);
        termYearSubjectMap = {};
    }

    var pickerTally = document.getElementById('cfPickerTally');
    var tallyAssignedCount = document.getElementById('cfTallyAssignedCount');
    var tallyNewCount = document.getElementById('cfTallyNewCount');
    var tallyUnits = document.getElementById('cfTallyUnits');
    var selectAllVisibleButton = document.getElementById('cfSelectAllVisible');
    var clearNewSelectionButton = document.getElementById('cfClearNewSelection');

    var addCoursesModal = document.getElementById('cfAddCoursesModal');
    var openAddCoursesButton = document.getElementById('cfOpenAddCoursesBtn');
    var closeAddCoursesButton = document.getElementById('cfCloseAddCoursesBtn');
    var cancelAddCoursesButton1 = document.getElementById('cfCancelAddCoursesBtn1');
    var wizardNext1 = document.getElementById('cfWizardNext1');
    var wizardBack2 = document.getElementById('cfWizardBack2');
    var wizardNext2 = document.getElementById('cfWizardNext2');
    var wizardBack3 = document.getElementById('cfWizardBack3');
    var reviewTerm = document.getElementById('cfReviewTerm');
    var reviewYearLevel = document.getElementById('cfReviewYearLevel');
    var reviewAssignedCount = document.getElementById('cfReviewAssignedCount');
    var reviewList = document.getElementById('cfReviewList');
    var reviewNewCount = document.getElementById('cfReviewNewCount');
    var reviewUnits = document.getElementById('cfReviewUnits');

    var yearCountInput = document.getElementById('cfYearCount');
    var termToggles = document.querySelectorAll('.cf-term-toggle-input');
    var termYearGrid = document.getElementById('cfTermYearGrid');

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
            // Term and Year Level live inside the Add Courses modal, which starts
            // hidden. Select2 measures container width at init time, so
            // initializing them now (against a display:none ancestor) produces a
            // broken zero-width widget. They're initialized instead when the
            // modal actually opens, once the container has real dimensions.
            if (selectElement === setupTerm || selectElement === setupYearLevel) {
                return;
            }

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

        if (openAddCoursesButton) {
            openAddCoursesButton.disabled = !(hasSetupCourse && hasSetupYear && hasSetupDates);
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

    function syncCoursePickerForTermYear() {
        if (!coursePicker) {
            return;
        }

        var termId = setupTerm ? String(setupTerm.value || '') : '';
        var yearBlockId = setupYearLevel ? String(setupYearLevel.value || '') : '';
        var key = yearBlockId + '_' + termId;
        var assignedIds = (termId && yearBlockId && termYearSubjectMap[key]) || [];
        var assignedLookup = {};
        assignedIds.forEach(function (id) {
            assignedLookup[String(id)] = true;
        });

        coursePicker.querySelectorAll('.cf-course-option').forEach(function (option) {
            var subjectId = option.getAttribute('data-subject-id') || '';
            var checkbox = option.querySelector('input[type="checkbox"]');
            if (!checkbox) {
                return;
            }

            if (assignedLookup[subjectId]) {
                option.classList.add('is-assigned');
                checkbox.checked = true;
                checkbox.disabled = true;
            } else {
                option.classList.remove('is-assigned');
                checkbox.disabled = false;
                checkbox.checked = false;
            }
        });

        updateCoursePickerTally();
    }

    function updateCoursePickerTally() {
        if (!coursePicker || !pickerTally) {
            return;
        }

        var assignedCount = 0;
        var newCount = 0;
        var totalUnits = 0;

        coursePicker.querySelectorAll('input[type="checkbox"]:checked').forEach(function (checkbox) {
            totalUnits += parseFloat(checkbox.getAttribute('data-units') || '0') || 0;
            if (checkbox.disabled) {
                assignedCount++;
            } else {
                newCount++;
            }
        });

        if (tallyAssignedCount) {
            tallyAssignedCount.textContent = String(assignedCount);
        }
        if (tallyNewCount) {
            tallyNewCount.textContent = String(newCount);
        }
        if (tallyUnits) {
            tallyUnits.textContent = totalUnits.toFixed(1);
        }
    }

    function showWizardStep(step) {
        if (!addCoursesModal) {
            return;
        }

        addCoursesModal.querySelectorAll('[data-wizard-panel]').forEach(function (panel) {
            var panelStep = Number(panel.getAttribute('data-wizard-panel'));
            panel.hidden = panelStep !== step;
        });

        addCoursesModal.querySelectorAll('[data-wizard-step-indicator]').forEach(function (indicator) {
            var indicatorStep = Number(indicator.getAttribute('data-wizard-step-indicator'));
            indicator.classList.toggle('is-active', indicatorStep === step);
            indicator.classList.toggle('is-done', indicatorStep < step);
        });
    }

    function openAddCoursesModal(step) {
        if (!addCoursesModal) {
            return;
        }

        addCoursesModal.hidden = false;
        addCoursesModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('cf-modal-open');
        initSelect2(setupTerm);
        initSelect2(setupYearLevel);
        syncCoursePickerForTermYear();
        showWizardStep(step || 1);
    }

    function closeAddCoursesModal() {
        if (!addCoursesModal) {
            return;
        }

        addCoursesModal.hidden = true;
        addCoursesModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('cf-modal-open');
    }

    function buildWizardReview() {
        if (reviewTerm) {
            reviewTerm.textContent = setupTerm && setupTerm.selectedIndex >= 0
                ? setupTerm.options[setupTerm.selectedIndex].text
                : '—';
        }

        if (reviewYearLevel) {
            reviewYearLevel.textContent = setupYearLevel && setupYearLevel.selectedIndex >= 0
                ? setupYearLevel.options[setupYearLevel.selectedIndex].text
                : '—';
        }

        var assignedCount = 0;
        var newCount = 0;
        var totalUnits = 0;
        var items = [];

        if (coursePicker) {
            coursePicker.querySelectorAll('input[type="checkbox"]:checked').forEach(function (checkbox) {
                var units = parseFloat(checkbox.getAttribute('data-units') || '0') || 0;
                totalUnits += units;

                if (checkbox.disabled) {
                    assignedCount++;
                    return;
                }

                newCount++;
                items.push({
                    code: checkbox.getAttribute('data-code') || '',
                    title: checkbox.getAttribute('data-title') || '',
                    units: units
                });
            });
        }

        if (reviewAssignedCount) {
            reviewAssignedCount.textContent = String(assignedCount);
        }
        if (reviewNewCount) {
            reviewNewCount.textContent = String(newCount);
        }
        if (reviewUnits) {
            reviewUnits.textContent = totalUnits.toFixed(1);
        }

        if (reviewList) {
            if (!items.length) {
                reviewList.innerHTML = '<div class="cf-review-empty">No new courses selected yet. Go back to pick at least one.</div>';
            } else {
                reviewList.innerHTML = items.map(function (item) {
                    return '<div class="cf-review-item">'
                        + '<span class="cf-review-item-name">'
                        + '<span class="cf-review-item-code">' + escapeHtmlText(item.code) + '</span>'
                        + '<span class="cf-review-item-title">' + escapeHtmlText(item.title) + '</span>'
                        + '</span>'
                        + '<span class="cf-review-item-units">' + item.units.toFixed(1) + ' units</span>'
                        + '</div>';
                }).join('');
            }
        }

        return newCount;
    }

    function escapeHtmlText(value) {
        var div = document.createElement('div');
        div.textContent = String(value || '');
        return div.innerHTML;
    }

    function getSubjectUnitsById() {
        var lookup = {};
        if (!coursePicker) {
            return lookup;
        }

        coursePicker.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
            lookup[String(checkbox.value)] = parseFloat(checkbox.getAttribute('data-units') || '0') || 0;
        });

        return lookup;
    }

    function renderTermYearGrid() {
        if (!termYearGrid || !setupYearLevel) {
            return;
        }

        var yearOptions = Array.prototype.slice.call(setupYearLevel.options).filter(function (option) {
            return option.value !== '';
        });

        var yearCount = yearCountInput ? Math.max(1, parseInt(yearCountInput.value, 10) || 1) : yearOptions.length;
        var selectedYears = yearOptions.slice(0, yearCount);

        var selectedTerms = [];
        termToggles.forEach(function (toggle) {
            if (toggle.checked) {
                selectedTerms.push({
                    id: toggle.value,
                    name: toggle.getAttribute('data-term-name') || toggle.value
                });
            }
        });

        if (!selectedYears.length || !selectedTerms.length) {
            termYearGrid.innerHTML = '<div class="cf-term-year-grid-empty">Set the number of years and at least one term to see the year/term slots.</div>';
            return;
        }

        var unitsById = getSubjectUnitsById();
        var html = '';

        selectedYears.forEach(function (yearOption) {
            selectedTerms.forEach(function (term) {
                var key = yearOption.value + '_' + term.id;
                var assignedIds = termYearSubjectMap[key] || [];
                var totalUnits = 0;
                assignedIds.forEach(function (subjectId) {
                    totalUnits += unitsById[String(subjectId)] || 0;
                });

                var statusText = assignedIds.length
                    ? assignedIds.length + ' course(s) · ' + totalUnits.toFixed(1) + ' units'
                    : 'No courses yet';

                html += '<button type="button" class="cf-term-year-cell' + (assignedIds.length ? ' has-courses' : '') + '" '
                    + 'data-year-block-id="' + escapeHtmlText(yearOption.value) + '" data-term-id="' + escapeHtmlText(term.id) + '">'
                    + '<span class="cf-term-year-cell-year">' + escapeHtmlText(yearOption.text) + '</span>'
                    + '<span class="cf-term-year-cell-term">' + escapeHtmlText(term.name) + '</span>'
                    + '<span class="cf-term-year-cell-status">' + escapeHtmlText(statusText) + '</span>'
                    + '</button>';
            });
        });

        termYearGrid.innerHTML = html;
    }

    function openAddCoursesForSlot(yearBlockId, termId) {
        if (setupYearLevel) {
            setupYearLevel.value = yearBlockId;
        }
        if (setupTerm) {
            setupTerm.value = termId;
        }

        openAddCoursesModal(2);
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
    syncCoursePickerForTermYear();
    renderTermYearGrid();

    if (yearCountInput) {
        yearCountInput.addEventListener('input', renderTermYearGrid);
    }

    termToggles.forEach(function (toggle) {
        toggle.addEventListener('change', renderTermYearGrid);
    });

    if (termYearGrid) {
        termYearGrid.addEventListener('click', function (event) {
            var cell = event.target.closest('.cf-term-year-cell');
            if (!cell) {
                return;
            }

            openAddCoursesForSlot(cell.getAttribute('data-year-block-id'), cell.getAttribute('data-term-id'));
        });
    }

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

    bindChange(setupTerm, function () {
        updateActionStates();
        syncCoursePickerForTermYear();
    });

    bindChange(setupYearLevel, function () {
        updateActionStates();
        syncCoursePickerForTermYear();
    });

    if (courseSearch) {
        courseSearch.addEventListener('input', filterCourseOptions);
    }

    if (coursePicker) {
        coursePicker.addEventListener('change', function () {
            updateActionStates();
            updateCoursePickerTally();
        });
    }

    if (selectAllVisibleButton) {
        selectAllVisibleButton.addEventListener('click', function () {
            coursePicker.querySelectorAll('.cf-course-option').forEach(function (option) {
                if (option.hidden) {
                    return;
                }
                var checkbox = option.querySelector('input[type="checkbox"]');
                if (checkbox && !checkbox.disabled) {
                    checkbox.checked = true;
                }
            });
            updateActionStates();
            updateCoursePickerTally();
        });
    }

    if (clearNewSelectionButton) {
        clearNewSelectionButton.addEventListener('click', function () {
            coursePicker.querySelectorAll('.cf-course-option input[type="checkbox"]').forEach(function (checkbox) {
                if (!checkbox.disabled) {
                    checkbox.checked = false;
                }
            });
            updateActionStates();
            updateCoursePickerTally();
        });
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

    if (openAddCoursesButton) {
        openAddCoursesButton.addEventListener('click', function () {
            openAddCoursesModal(1);
        });
    }

    [closeAddCoursesButton, cancelAddCoursesButton1].forEach(function (button) {
        if (!button) {
            return;
        }
        button.addEventListener('click', closeAddCoursesModal);
    });

    if (addCoursesModal) {
        addCoursesModal.addEventListener('click', function (event) {
            if (event.target === addCoursesModal) {
                closeAddCoursesModal();
            }
        });
    }

    if (wizardNext1) {
        wizardNext1.addEventListener('click', function () {
            var hasTerm = !!(setupTerm && setupTerm.value);
            var hasYearLevel = !!(setupYearLevel && setupYearLevel.value);

            if (!hasTerm || !hasYearLevel) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Please select Term and Year Level first.', 'warning');
                }
                return;
            }

            syncCoursePickerForTermYear();
            showWizardStep(2);
        });
    }

    if (wizardBack2) {
        wizardBack2.addEventListener('click', function () {
            showWizardStep(1);
        });
    }

    if (wizardNext2) {
        wizardNext2.addEventListener('click', function () {
            var newCount = buildWizardReview();

            if (newCount <= 0) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Please check at least one course to add.', 'warning');
                }
                return;
            }

            showWizardStep(3);
        });
    }

    if (wizardBack3) {
        wizardBack3.addEventListener('click', function () {
            showWizardStep(2);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeEditSubjectModal();
            closeViewListModal();
            closeAddCoursesModal();
        }
    });

    var reopenAddCoursesStep = parseInt(page.getAttribute('data-reopen-add-courses') || '0', 10);
    if (reopenAddCoursesStep > 0) {
        openAddCoursesModal(reopenAddCoursesStep);
    }

    if (window.location.search.indexOf('open_view_list=1') !== -1) {
        openViewListModal();

        if (window.history && window.history.replaceState) {
            var cleanUrl = window.location.pathname + window.location.search.replace(/[?&]open_view_list=1/, '').replace(/^&/, '?');
            window.history.replaceState(null, '', cleanUrl);
        }
    }
})();
