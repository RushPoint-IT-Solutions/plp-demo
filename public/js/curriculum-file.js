/* Curriculum File JS connection helpers */
(function () {
    var page = document.getElementById('curriculumFilePage');
    if (!page) {
        return;
    }

    var topCourse = document.getElementById('cfProgram');
    var topYear = document.getElementById('cfCurriculumYear');
    var copyCourse = document.getElementById('cfCopyCourse');
    var copyYear = document.getElementById('cfCopyCurriculumYear');
    var setupCourse = document.getElementById('cfSetupProgram');
    var setupYear = document.getElementById('cfSetupCurriculumYear');
    var copySourceCourse = document.getElementById('cfCopySourceCourseId');
    var copySourceYear = document.getElementById('cfCopySourceCurriculumYear');
    var copyButton = document.getElementById('cfCopySubmitBtn');
    var setupButton = document.getElementById('cfSaveSetupBtn');
    var viewListButton = document.getElementById('cfViewListBtn');
    var openPrereqButton = document.getElementById('cfOpenPrerequisitesBtn');

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
    }

    function syncYearSelector(courseSelect, yearSelect, preferredValue) {
        var courseId = courseSelect ? String(courseSelect.value || '') : '';
        setYearOptions(yearSelect, yearMap[courseId] || [], preferredValue);
    }

    function updateSourceSelection() {
        if (copySourceCourse && topCourse) {
            copySourceCourse.value = String(topCourse.value || selectedCourseId || '');
        }

        if (copySourceYear && topYear) {
            copySourceYear.value = String(topYear.value || selectedCurriculumYear || '');
        }
    }

    function updateActionStates() {
        var hasTopYear = !!(topYear && topYear.value && !topYear.disabled);
        var hasCopyCourse = !!(copyCourse && copyCourse.value);
        var hasCopyYear = !!(copyYear && String(copyYear.value || '').trim());
        var hasSetupCourse = !!(setupCourse && setupCourse.value);
        var hasSetupYear = !!(setupYear && setupYear.value && !setupYear.disabled);

        if (copyButton) {
            copyButton.disabled = !(hasTopYear && hasCopyCourse && hasCopyYear);
        }

        if (setupButton) {
            setupButton.disabled = !(hasSetupCourse && hasSetupYear);
        }

        if (viewListButton) {
            viewListButton.disabled = !hasTopYear;
        }

        if (openPrereqButton) {
            openPrereqButton.disabled = !hasTopYear;
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

    if (successMessage && typeof showRegistrarToast === 'function') {
        showRegistrarToast(successMessage, 'success');
    }

    if (errorMessage && typeof showRegistrarToast === 'function') {
        showRegistrarToast(errorMessage, 'error');
    }

    if (topCourse && selectedCourseId) {
        topCourse.value = selectedCourseId;
    }

    if (copyCourse && !copyCourse.value && selectedCourseId) {
        copyCourse.value = selectedCourseId;
    }

    if (setupCourse && !setupCourse.value && selectedCourseId) {
        setupCourse.value = selectedCourseId;
    }

    syncYearSelector(topCourse, topYear, selectedCurriculumYear);

    if (setupYear) {
        syncYearSelector(setupCourse, setupYear, String(setupYear.getAttribute('data-initial-year') || ''));
    }

    selectedCurriculumYear = topYear ? String(topYear.value || '') : selectedCurriculumYear;
    updateSourceSelection();
    updateActionStates();

    if (topCourse) {
        topCourse.addEventListener('change', function () {
            selectedCourseId = String(topCourse.value || '');
            selectedCurriculumYear = '';
            syncYearSelector(topCourse, topYear, '');
            selectedCurriculumYear = topYear ? String(topYear.value || '') : '';
            updateSourceSelection();
            updateActionStates();
        });
    }

    if (topYear) {
        topYear.addEventListener('change', function () {
            selectedCurriculumYear = String(topYear.value || '');
            updateSourceSelection();
            updateActionStates();
        });
    }

    if (copyCourse) {
        copyCourse.addEventListener('change', function () {
            updateActionStates();
        });
    }

    if (copyYear) {
        copyYear.addEventListener('input', updateActionStates);
        copyYear.addEventListener('change', updateActionStates);
    }

    if (setupCourse) {
        setupCourse.addEventListener('change', function () {
            syncYearSelector(setupCourse, setupYear, setupYear ? String(setupYear.value || setupYear.getAttribute('data-initial-year') || '') : '');
            updateActionStates();
        });
    }

    if (setupYear) {
        setupYear.addEventListener('change', updateActionStates);
    }

    if (viewListButton) {
        viewListButton.addEventListener('click', navigateToPreRequisites);
    }

    if (openPrereqButton) {
        openPrereqButton.addEventListener('click', navigateToPreRequisites);
    }
})();
