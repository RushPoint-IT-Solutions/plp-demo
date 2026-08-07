/* Pre-requisites JS (DB-backed) */
(function () {
    var page = document.getElementById('prereqPage');
    if (!page) {
        return;
    }

    var LIST_URL = page.getAttribute('data-list-url') || '';
    var DETAIL_URL_TEMPLATE = page.getAttribute('data-detail-url-template') || '';
    var SAVE_URL_TEMPLATE = page.getAttribute('data-save-url-template') || '';
    var CURRICULUM_URL = page.getAttribute('data-curriculum-url') || '';
    var CSRF_TOKEN = page.getAttribute('data-csrf-token') || '';

    var courseSelect = document.getElementById('prereqCourse');
    var curriculumYearSelect = document.getElementById('prereqYear');
    var viewListButton = document.getElementById('btnViewList');
    var downloadButton = document.getElementById('prereqDownloadBtn');
    var addCourseButton = document.getElementById('prereqAddCourseBtn');
    var addCourseTopButton = document.getElementById('prereqAddCourseTopBtn');
    var saveButton = document.getElementById('prereqSaveBtn');
    var backButton = document.getElementById('prereqBackBtn');
    var subjectDownloadButton = document.getElementById('prereqSubjectDownloadBtn');
    var addCourseModal = document.getElementById('prereqAddCourseModal');
    var addCourseForm = document.getElementById('prereqAddCourseForm');
    var addCourseCloseButton = document.getElementById('prereqAddCourseCloseBtn');
    var addCourseCancelButton = document.getElementById('prereqAddCourseCancelBtn');
    var addCourseSaveButton = document.getElementById('prereqAddCourseSaveBtn');
    var addCourseProgram = document.getElementById('prereqAddProgram');
    var addCourseCurriculumYear = document.getElementById('prereqAddCurriculumYear');
    var addCourseDateFrom = document.getElementById('prereqAddDateFrom');
    var addCourseDateTo = document.getElementById('prereqAddDateTo');
    var addCourseYearLevel = document.getElementById('prereqAddYearLevel');
    var addCourseTerm = document.getElementById('prereqAddTerm');
    var addCourseSearch = document.getElementById('prereqAddCourseSearch');
    var addCourseList = document.getElementById('prereqAddCourseList');

    var listView = document.getElementById('prereqListView');
    var detailView = document.getElementById('prereqDetailView');
    var listContent = document.getElementById('prereqListContent');
    var programTitle = document.getElementById('prereqProgramTitle');
    var pagination = document.getElementById('prereqPagination');
    var prevPageButton = document.getElementById('prereqPrevPage');
    var nextPageButton = document.getElementById('prereqNextPage');
    var pageInfoLabel = document.getElementById('prereqPageInfo');

    var detailCode = document.getElementById('prereqDetailCode');
    var detailName = document.getElementById('prereqDetailName');

    var searchInputs = {
        pre: document.getElementById('prereqSearchPre'),
        co: document.getElementById('prereqSearchCo'),
        equivalent: document.getElementById('prereqSearchEq')
    };

    var availableContainers = {
        pre: document.getElementById('prereqAvailPre'),
        co: document.getElementById('prereqAvailCo'),
        equivalent: document.getElementById('prereqAvailEq')
    };

    var selectedContainers = {
        pre: document.getElementById('prereqSelPre'),
        co: document.getElementById('prereqSelCo'),
        equivalent: document.getElementById('prereqSelEq')
    };

    var state = {
        selectedCourseId: page.getAttribute('data-selected-course-id') || '',
        selectedCurriculumYear: page.getAttribute('data-selected-curriculum-year') || '',
        listPage: 1,
        perPage: 100,
        listMeta: {
            page: 1,
            per_page: 100,
            total: 0,
            last_page: 1
        },
        yearsPayload: [],
        currentSubject: null,
        availableSubjects: [],
        selected: {
            pre: [],
            co: [],
            equivalent: []
        },
        subjectIndex: {}
    };

    var courseYearMap = parseCourseYearMap(page.getAttribute('data-course-years'));

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

    function initSearchableSelects() {
        if (!hasSelect2()) {
            return;
        }

        page.querySelectorAll('select.prereq-select2').forEach(function (selectElement) {
            if (selectElement === addCourseProgram) {
                return;
            }

            initSelect2(selectElement);
        });
    }

    function parseCourseYearMap(rawValue) {
        if (!rawValue) {
            return {};
        }

        try {
            var parsed = JSON.parse(rawValue);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (error) {
            console.error(error);
            return {};
        }
    }

    function renderPagination() {
        if (!pagination || !pageInfoLabel || !prevPageButton || !nextPageButton) {
            return;
        }

        var currentPage = Number(state.listMeta.page || 1);
        var lastPage = Number(state.listMeta.last_page || 1);
        var total = Number(state.listMeta.total || 0);

        pagination.hidden = total <= Number(state.perPage);
        pageInfoLabel.textContent = 'Page ' + currentPage + ' of ' + lastPage + ' (' + total + ' course(s))';
        prevPageButton.disabled = currentPage <= 1;
        nextPageButton.disabled = currentPage >= lastPage;
    }

    function getTemplateUrl(template, id) {
        if (!template) {
            return '';
        }

        return template.replace('__CURRICULUM_SUBJECT_ID__', String(id));
    }

    function getRequisiteLabel(typeCode) {
        if (typeCode === 'pre') {
            return 'Pre-Requisite';
        }

        if (typeCode === 'co') {
            return 'Co-Requisite';
        }

        return 'Equivalent Course';
    }

    function syncAddCourseModalDefaults() {
        if (addCourseProgram && courseSelect && courseSelect.value) {
            addCourseProgram.value = String(courseSelect.value || '');
        }

        if (addCourseCurriculumYear && curriculumYearSelect && curriculumYearSelect.value) {
            addCourseCurriculumYear.value = String(curriculumYearSelect.value || '');
        }
    }

    function updateAddCourseSaveState() {
        if (!addCourseSaveButton) {
            return;
        }

        var hasProgram = !!(addCourseProgram && addCourseProgram.value);
        var hasCurriculumYear = !!(addCourseCurriculumYear && String(addCourseCurriculumYear.value || '').trim());
        var hasDates = !!(addCourseDateFrom && addCourseDateFrom.value && addCourseDateTo && addCourseDateTo.value);
        var hasYearLevel = !!(addCourseYearLevel && addCourseYearLevel.value);
        var hasTerm = !!(addCourseTerm && addCourseTerm.value);
        var hasCourse = !!(addCourseList && addCourseList.querySelector('input[type="checkbox"]:checked'));

        addCourseSaveButton.disabled = !(hasProgram && hasCurriculumYear && hasDates && hasYearLevel && hasTerm && hasCourse);
    }

    function filterAddCourseOptions() {
        if (!addCourseSearch || !addCourseList) {
            return;
        }

        var query = String(addCourseSearch.value || '').trim().toLowerCase();
        addCourseList.querySelectorAll('.prereq-add-course-option').forEach(function (option) {
            var text = option.getAttribute('data-course-text') || '';
            option.hidden = !!query && text.indexOf(query) === -1;
        });
    }

    function maybeUpdateAddCurriculumYearFromDates() {
        if (!addCourseCurriculumYear || !addCourseDateFrom || !addCourseDateTo) {
            updateAddCourseSaveState();
            return;
        }

        var fromValue = String(addCourseDateFrom.value || '');
        var toValue = String(addCourseDateTo.value || '');
        if (!fromValue || !toValue) {
            updateAddCourseSaveState();
            return;
        }

        var fromYear = fromValue.slice(0, 4);
        var toYear = toValue.slice(0, 4);
        if (/^\d{4}$/.test(fromYear) && /^\d{4}$/.test(toYear)) {
            addCourseCurriculumYear.value = fromYear + '-' + toYear;
        }

        updateAddCourseSaveState();
    }

    function openAddCourseModal() {
        if (!addCourseModal) {
            if (CURRICULUM_URL) {
                window.location.href = CURRICULUM_URL;
            }
            return;
        }

        addCourseModal.hidden = false;
        addCourseModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('prereq-modal-open');

        syncAddCourseModalDefaults();
        initSelect2(addCourseProgram);
        filterAddCourseOptions();
        updateAddCourseSaveState();

        if (addCourseSearch) {
            addCourseSearch.focus();
        }
    }

    function closeAddCourseModal() {
        if (!addCourseModal) {
            return;
        }

        addCourseModal.hidden = true;
        addCourseModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('prereq-modal-open');
    }

    function escapeHtml(value) {
        var normalized = value === null || typeof value === 'undefined' ? '' : String(value);
        return normalized
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showMessage(message, level) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast(message, level || 'success');
            return;
        }

        alert(message);
    }

    function setButtonLoading(button, loading, loadingText) {
        if (!button) {
            return;
        }

        if (!button.dataset.originalText) {
            button.dataset.originalText = button.textContent;
        }

        button.disabled = !!loading;
        button.textContent = loading ? (loadingText || 'Loading...') : button.dataset.originalText;
    }

    function requestJson(url, options) {
        var requestOptions = options || {};
        requestOptions.headers = requestOptions.headers || {};
        requestOptions.headers.Accept = 'application/json';
        requestOptions.headers['X-Requested-With'] = 'XMLHttpRequest';
        requestOptions.credentials = 'same-origin';

        return fetch(url, requestOptions)
            .then(function (response) {
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

    function updateCurriculumYearOptions() {
        if (!courseSelect || !curriculumYearSelect) {
            return;
        }

        var courseId = String(courseSelect.value || '');
        var years = courseYearMap[courseId] || [];

        curriculumYearSelect.innerHTML = '';
        if (!years.length) {
            var emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = 'No Curriculum Year';
            curriculumYearSelect.appendChild(emptyOption);
            state.selectedCurriculumYear = '';
            return;
        }

        years.forEach(function (yearCode) {
            var option = document.createElement('option');
            option.value = yearCode;
            option.textContent = yearCode;
            curriculumYearSelect.appendChild(option);
        });

        var preferred = state.selectedCurriculumYear;
        if (!preferred || years.indexOf(preferred) === -1) {
            preferred = years[0];
        }

        curriculumYearSelect.value = preferred;
        state.selectedCurriculumYear = preferred;
    }

    function toggleViews(showList) {
        if (listView) {
            listView.hidden = !showList;
        }

        if (detailView) {
            detailView.hidden = !!showList;
        }
    }

    function clearPrintMode() {
        document.body.classList.remove('prereq-print-list');
        document.body.classList.remove('prereq-print-subject');
    }

    function printCurrentView(mode) {
        clearPrintMode();
        document.body.classList.add(mode === 'subject' ? 'prereq-print-subject' : 'prereq-print-list');

        var didCleanup = false;
        function cleanupPrintMode() {
            if (didCleanup) {
                return;
            }

            didCleanup = true;
            clearPrintMode();
            window.removeEventListener('afterprint', cleanupPrintMode);
        }

        window.addEventListener('afterprint', cleanupPrintMode);

        setTimeout(function () {
            window.print();

            // Some browsers do not fire afterprint reliably.
            setTimeout(cleanupPrintMode, 1200);
        }, 70);
    }

    function renderListView() {
        if (!listContent) {
            return;
        }

        if (!state.yearsPayload.length) {
            listContent.innerHTML = '<div class="prereq-year-block"><div class="prereq-empty-msg">No curriculum records found for this course and curriculum year.</div></div>';
            return;
        }

        var html = '';

        state.yearsPayload.forEach(function (yearBlock) {
            html += '<div class="prereq-year-block">';
            html += '<div class="prereq-year-label">' + escapeHtml(yearBlock.label) + '</div>';

            (yearBlock.semesters || []).forEach(function (semester) {
                html += '<div class="prereq-sem-label">' + escapeHtml(semester.label) + '</div>';
                html += '<div class="prereq-table-wrap">';
                html += '<table class="prereq-table">';
                html += '<thead><tr><th>Course Code</th><th>Description</th><th>Credited Units</th><th>Pre-requisite</th><th>Co-requisite</th><th>Equivalent Course</th><th class="prereq-action-col">Setup</th></tr></thead>';
                html += '<tbody>';

                var subjects = semester.subjects || [];
                if (!subjects.length) {
                    html += '<tr><td colspan="7" class="prereq-empty-msg">No list of course(s) yet for this year level and term. Use Add Course to build this curriculum first.</td></tr>';
                } else {
                    subjects.forEach(function (row) {
                        html += '<tr class="prereq-row" data-curriculum-subject-id="' + row.curriculum_subject_id + '">';
                        html += '<td>' + escapeHtml(row.code) + '</td>';
                        html += '<td class="prereq-desc-cell">' + escapeHtml(row.description) + '</td>';
                        html += '<td>' + escapeHtml(row.credited_units) + '</td>';
                        html += '<td>' + escapeHtml(row.pre_requisite_text || 'None') + '</td>';
                        html += '<td>' + escapeHtml(row.co_requisite_text || 'None') + '</td>';
                        html += '<td>' + escapeHtml(row.equivalent_subject_text || 'None') + '</td>';
                        html += '<td class="prereq-action-col"><button type="button" class="prereq-row-setup-btn" data-action="open-setup" data-curriculum-subject-id="' + row.curriculum_subject_id + '">Setup</button></td>';
                        html += '</tr>';
                    });
                }

                html += '</tbody></table></div>';
            });

            html += '</div>';
        });

        listContent.innerHTML = html;
    }

    function loadPrerequisiteList(pageNumber) {
        if (!LIST_URL || !courseSelect || !curriculumYearSelect) {
            return;
        }

        var courseId = String(courseSelect.value || '');
        var curriculumYear = String(curriculumYearSelect.value || '');

        if (!courseId || !curriculumYear) {
            showMessage('Please select both Course and Curriculum Year.', 'warning');
            return;
        }

        state.selectedCourseId = courseId;
        state.selectedCurriculumYear = curriculumYear;
        if (typeof pageNumber !== 'undefined' && pageNumber !== null) {
            state.listPage = Math.max(Number(pageNumber) || 1, 1);
        }

        setButtonLoading(viewListButton, true, 'Loading...');

        var url = LIST_URL
            + '?course_id=' + encodeURIComponent(courseId)
            + '&curriculum_year=' + encodeURIComponent(curriculumYear)
            + '&page=' + encodeURIComponent(String(state.listPage))
            + '&per_page=' + encodeURIComponent(String(state.perPage));
        requestJson(url)
            .then(function (payload) {
                state.yearsPayload = payload.years || [];
                state.listMeta = payload.meta || {
                    page: 1,
                    per_page: state.perPage,
                    total: 0,
                    last_page: 1
                };
                state.listPage = Number(state.listMeta.page || 1);
                programTitle.textContent = String(payload.program_title || '').toUpperCase();
                renderListView();
                renderPagination();
                toggleViews(true);
            })
            .catch(function (errorPayload) {
                console.error(errorPayload);
                showMessage(resolveErrorMessage(errorPayload, 'Unable to load pre-requisites right now.'), 'warning');
                state.yearsPayload = [];
                state.listMeta = {
                    page: 1,
                    per_page: state.perPage,
                    total: 0,
                    last_page: 1
                };
                renderListView();
                renderPagination();
            })
            .finally(function () {
                setButtonLoading(viewListButton, false);
            });
    }

    function downloadListPdf() {
        if (!state.yearsPayload.length) {
            showMessage('Please view list first before downloading.', 'warning');
            return;
        }

        toggleViews(true);
        printCurrentView('list');
    }

    function downloadSubjectPdf() {
        if (!state.currentSubject || !state.currentSubject.curriculum_subject_id) {
            showMessage('Please open a course first before downloading.', 'warning');
            return;
        }

        toggleViews(false);
        printCurrentView('subject');
    }

    function resolveErrorMessage(payload, fallback) {
        if (payload && payload.message) {
            return payload.message;
        }

        if (payload && payload.errors && typeof payload.errors === 'object') {
            var keys = Object.keys(payload.errors);
            if (keys.length && payload.errors[keys[0]] && payload.errors[keys[0]][0]) {
                return payload.errors[keys[0]][0];
            }
        }

        return fallback;
    }

    function rebuildSubjectIndex(detailPayload) {
        state.subjectIndex = {};

        (detailPayload.available_subjects || []).forEach(function (subject) {
            state.subjectIndex[String(subject.subject_id)] = {
                subject_id: Number(subject.subject_id),
                code: subject.code,
                description: subject.description
            };
        });

        ['pre', 'co', 'equivalent'].forEach(function (typeCode) {
            (detailPayload.selected[typeCode] || []).forEach(function (subject) {
                state.subjectIndex[String(subject.subject_id)] = {
                    subject_id: Number(subject.subject_id),
                    code: subject.code,
                    description: subject.description
                };
            });
        });
    }

    function openDetailView(curriculumSubjectId) {
        var detailUrl = getTemplateUrl(DETAIL_URL_TEMPLATE, curriculumSubjectId);
        if (!detailUrl) {
            return;
        }

        requestJson(detailUrl)
            .then(function (payload) {
                state.currentSubject = payload.subject || null;
                state.availableSubjects = payload.available_subjects || [];
                state.selected.pre = (payload.selected.pre || []).map(function (subject) {
                    return Number(subject.subject_id);
                });
                state.selected.co = (payload.selected.co || []).map(function (subject) {
                    return Number(subject.subject_id);
                });
                state.selected.equivalent = (payload.selected.equivalent || []).map(function (subject) {
                    return Number(subject.subject_id);
                });

                rebuildSubjectIndex(payload);

                detailCode.textContent = payload.subject ? payload.subject.code : '';
                detailName.textContent = payload.subject ? String(payload.subject.description || '').toUpperCase() : '';

                renderAllSelectionPanels();
                toggleViews(false);
            })
            .catch(function (errorPayload) {
                console.error(errorPayload);
                showMessage(resolveErrorMessage(errorPayload, 'Unable to load course detail.'), 'warning');
            });
    }

    function getSearchValue(typeCode) {
        var input = searchInputs[typeCode];
        return input ? String(input.value || '').trim().toLowerCase() : '';
    }

    function renderAvailableList(typeCode) {
        var container = availableContainers[typeCode];
        if (!container) {
            return;
        }

        var selectedIds = state.selected[typeCode] || [];
        var searchValue = getSearchValue(typeCode);

        var html = '';
        state.availableSubjects.forEach(function (subject) {
            var subjectId = Number(subject.subject_id);
            if (selectedIds.indexOf(subjectId) !== -1) {
                return;
            }

            var fullText = (String(subject.code || '') + ' ' + String(subject.description || '')).toLowerCase();
            if (searchValue && fullText.indexOf(searchValue) === -1) {
                return;
            }

            html += '<div class="prereq-avail-item">';
            html += '<span>' + escapeHtml(subject.code) + ' - ' + escapeHtml(subject.description) + '</span>';
            html += '<button type="button" class="prereq-add-btn" data-action="add-subject" data-type="' + typeCode + '" data-subject-id="' + subjectId + '">';
            html += 'Add ' + escapeHtml(getRequisiteLabel(typeCode));
            html += '</button>';
            html += '</div>';
        });

        if (!html) {
            html = '<div class="prereq-avail-empty">No available course matches this search.</div>';
        }

        container.innerHTML = html;
    }

    function renderSelectedList(typeCode) {
        var container = selectedContainers[typeCode];
        if (!container) {
            return;
        }

        var selectedIds = state.selected[typeCode] || [];
        if (!selectedIds.length) {
            container.innerHTML = '<div class="prereq-selected-empty">No ' + escapeHtml(getRequisiteLabel(typeCode).toLowerCase()) + ' selected yet.</div>';
            return;
        }

        var html = '';
        selectedIds.forEach(function (subjectId) {
            var subject = state.subjectIndex[String(subjectId)];
            if (!subject) {
                return;
            }

            html += '<div class="prereq-sel-item">';
            html += '<span>' + escapeHtml(subject.code) + ' - ' + escapeHtml(subject.description) + '</span>';
            html += '<button type="button" class="prereq-remove-btn" data-action="remove-subject" data-type="' + typeCode + '" data-subject-id="' + subjectId + '">&times;</button>';
            html += '</div>';
        });

        container.innerHTML = html;
    }

    function renderAllSelectionPanels() {
        ['pre', 'co', 'equivalent'].forEach(function (typeCode) {
            renderAvailableList(typeCode);
            renderSelectedList(typeCode);
        });
    }

    function addSelectedSubject(typeCode, subjectId) {
        if (!state.selected[typeCode]) {
            return;
        }

        var normalizedId = Number(subjectId);
        if (!normalizedId) {
            return;
        }

        if (state.selected[typeCode].indexOf(normalizedId) === -1) {
            state.selected[typeCode].push(normalizedId);
        }

        renderAvailableList(typeCode);
        renderSelectedList(typeCode);
    }

    function removeSelectedSubject(typeCode, subjectId) {
        if (!state.selected[typeCode]) {
            return;
        }

        var normalizedId = Number(subjectId);
        state.selected[typeCode] = state.selected[typeCode].filter(function (id) {
            return Number(id) !== normalizedId;
        });

        renderAvailableList(typeCode);
        renderSelectedList(typeCode);
    }

    function saveDetailMappings() {
        if (!state.currentSubject || !state.currentSubject.curriculum_subject_id) {
            return;
        }

        var saveUrl = getTemplateUrl(SAVE_URL_TEMPLATE, state.currentSubject.curriculum_subject_id);
        if (!saveUrl) {
            return;
        }

        setButtonLoading(saveButton, true, 'Saving...');

        requestJson(saveUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                pre_subject_ids: state.selected.pre,
                co_subject_ids: state.selected.co,
                equivalent_subject_ids: state.selected.equivalent
            })
        })
            .then(function (payload) {
                showMessage(payload.message || 'Pre-requisites saved successfully.', 'success');
                toggleViews(true);
                loadPrerequisiteList(state.listPage);
            })
            .catch(function (errorPayload) {
                console.error(errorPayload);
                showMessage(resolveErrorMessage(errorPayload, 'Unable to save pre/co-requisite mappings.'), 'warning');
            })
            .finally(function () {
                setButtonLoading(saveButton, false);
            });
    }

    function bindEvents() {
        if (courseSelect) {
            courseSelect.addEventListener('change', function () {
                state.selectedCourseId = String(courseSelect.value || '');
                state.selectedCurriculumYear = '';
                state.listPage = 1;
                updateCurriculumYearOptions();
                syncAddCourseModalDefaults();
            });
        }

        if (curriculumYearSelect) {
            curriculumYearSelect.addEventListener('change', function () {
                state.selectedCurriculumYear = String(curriculumYearSelect.value || '');
                state.listPage = 1;
                syncAddCourseModalDefaults();
            });
        }

        if (viewListButton) {
            viewListButton.addEventListener('click', function () {
                state.listPage = 1;
                loadPrerequisiteList(1);
            });
        }

        if (downloadButton) {
            downloadButton.addEventListener('click', downloadListPdf);
        }

        if (addCourseButton) {
            addCourseButton.addEventListener('click', openAddCourseModal);
        }

        if (addCourseTopButton) {
            addCourseTopButton.addEventListener('click', openAddCourseModal);
        }

        [addCourseCloseButton, addCourseCancelButton].forEach(function (button) {
            if (!button) {
                return;
            }

            button.addEventListener('click', closeAddCourseModal);
        });

        if (addCourseModal) {
            addCourseModal.addEventListener('click', function (event) {
                if (event.target === addCourseModal) {
                    closeAddCourseModal();
                }
            });
        }

        [addCourseProgram, addCourseCurriculumYear, addCourseYearLevel, addCourseTerm].forEach(function (input) {
            if (!input) {
                return;
            }

            input.addEventListener('change', updateAddCourseSaveState);
            input.addEventListener('input', updateAddCourseSaveState);
        });

        [addCourseDateFrom, addCourseDateTo].forEach(function (input) {
            if (!input) {
                return;
            }

            input.addEventListener('change', maybeUpdateAddCurriculumYearFromDates);
            input.addEventListener('input', maybeUpdateAddCurriculumYearFromDates);
        });

        if (addCourseSearch) {
            addCourseSearch.addEventListener('input', filterAddCourseOptions);
        }

        if (addCourseList) {
            addCourseList.addEventListener('change', updateAddCourseSaveState);
        }

        if (addCourseForm) {
            addCourseForm.addEventListener('submit', function (event) {
                updateAddCourseSaveState();
                if (addCourseSaveButton && addCourseSaveButton.disabled) {
                    event.preventDefault();
                    showMessage('Please complete Program, Curriculum Year, Date, Year Level, Term, and at least one Course.', 'warning');
                }
            });
        }

        if (saveButton) {
            saveButton.addEventListener('click', saveDetailMappings);
        }

        if (backButton) {
            backButton.addEventListener('click', function () {
                toggleViews(true);
            });
        }

        if (subjectDownloadButton) {
            subjectDownloadButton.addEventListener('click', downloadSubjectPdf);
        }

        if (prevPageButton) {
            prevPageButton.addEventListener('click', function () {
                if (state.listPage <= 1) {
                    return;
                }

                loadPrerequisiteList(state.listPage - 1);
            });
        }

        if (nextPageButton) {
            nextPageButton.addEventListener('click', function () {
                var lastPage = Number(state.listMeta.last_page || 1);
                if (state.listPage >= lastPage) {
                    return;
                }

                loadPrerequisiteList(state.listPage + 1);
            });
        }

        if (listContent) {
            listContent.addEventListener('click', function (event) {
                var setupButton = event.target.closest('[data-action="open-setup"][data-curriculum-subject-id]');
                if (setupButton) {
                    var setupSubjectId = Number(setupButton.getAttribute('data-curriculum-subject-id'));
                    if (setupSubjectId) {
                        openDetailView(setupSubjectId);
                    }

                    return;
                }

                var row = event.target.closest('.prereq-row[data-curriculum-subject-id]');
                if (!row) {
                    return;
                }

                var curriculumSubjectId = Number(row.getAttribute('data-curriculum-subject-id'));
                if (!curriculumSubjectId) {
                    return;
                }

                openDetailView(curriculumSubjectId);
            });
        }

        if (detailView) {
            detailView.addEventListener('click', function (event) {
                var addButton = event.target.closest('[data-action="add-subject"]');
                if (addButton) {
                    addSelectedSubject(
                        addButton.getAttribute('data-type'),
                        addButton.getAttribute('data-subject-id')
                    );
                    return;
                }

                var removeButton = event.target.closest('[data-action="remove-subject"]');
                if (removeButton) {
                    removeSelectedSubject(
                        removeButton.getAttribute('data-type'),
                        removeButton.getAttribute('data-subject-id')
                    );
                }
            });
        }

        Object.keys(searchInputs).forEach(function (typeCode) {
            var input = searchInputs[typeCode];
            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                renderAvailableList(typeCode);
            });
        });

        document.querySelectorAll('[data-action="search"][data-type]').forEach(function (button) {
            button.addEventListener('click', function () {
                var typeCode = button.getAttribute('data-type');
                renderAvailableList(typeCode);
            });
        });
    }

    function init() {
        if (courseSelect && state.selectedCourseId) {
            courseSelect.value = String(state.selectedCourseId);
        }

        initSearchableSelects();
        updateCurriculumYearOptions();
        syncAddCourseModalDefaults();
        updateAddCourseSaveState();
        bindEvents();

        if (state.selectedCourseId && state.selectedCurriculumYear) {
            loadPrerequisiteList(1);
        } else {
            renderPagination();
        }
    }

    init();
})();
