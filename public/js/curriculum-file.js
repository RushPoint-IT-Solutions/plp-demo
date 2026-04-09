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

    var viewListButton = document.getElementById('cfViewListBtn');
    var openPrereqButton = document.getElementById('cfOpenPrerequisitesBtn');

    var preRequisitesUrl = page.getAttribute('data-pre-requisites-url') || '';
    var selectedCourseId = String(page.getAttribute('data-selected-course-id') || '');
    var selectedCurriculumYear = String(page.getAttribute('data-selected-curriculum-year') || '');

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
            return;
        }

        years.forEach(function (yearCode) {
            var option = document.createElement('option');
            option.value = yearCode;
            option.textContent = yearCode;
            selectElement.appendChild(option);
        });

        var preferred = preferredValue && years.indexOf(preferredValue) !== -1 ? preferredValue : years[0];
        selectElement.value = preferred;
    }

    function syncYearSelectors() {
        var courseId = topCourse ? String(topCourse.value || '') : '';
        var years = yearMap[courseId] || [];

        setYearOptions(topYear, years, selectedCurriculumYear);
        setYearOptions(copyYear, years, topYear ? String(topYear.value || '') : '');
        setYearOptions(setupYear, years, topYear ? String(topYear.value || '') : '');
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

    if (topCourse && selectedCourseId) {
        topCourse.value = selectedCourseId;
    }

    syncYearSelectors();

    if (topCourse) {
        topCourse.addEventListener('change', function () {
            selectedCourseId = String(topCourse.value || '');
            selectedCurriculumYear = '';
            syncYearSelectors();

            if (copyCourse) {
                copyCourse.value = selectedCourseId;
            }
            if (setupCourse) {
                setupCourse.value = selectedCourseId;
            }
        });
    }

    if (topYear) {
        topYear.addEventListener('change', function () {
            selectedCurriculumYear = String(topYear.value || '');
            setYearOptions(copyYear, yearMap[String(topCourse.value || '')] || [], selectedCurriculumYear);
            setYearOptions(setupYear, yearMap[String(topCourse.value || '')] || [], selectedCurriculumYear);
        });
    }

    if (copyCourse) {
        copyCourse.addEventListener('change', function () {
            var years = yearMap[String(copyCourse.value || '')] || [];
            setYearOptions(copyYear, years, years.length ? years[0] : '');
        });
    }

    if (setupCourse) {
        setupCourse.addEventListener('change', function () {
            var years = yearMap[String(setupCourse.value || '')] || [];
            setYearOptions(setupYear, years, years.length ? years[0] : '');
        });
    }

    if (viewListButton) {
        viewListButton.addEventListener('click', navigateToPreRequisites);
    }

    if (openPrereqButton) {
        openPrereqButton.addEventListener('click', navigateToPreRequisites);
    }
})();
