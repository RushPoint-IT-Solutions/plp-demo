/* Slot Monitoring Page */
(function () {
    var PAGE = document.getElementById('slotMonitoringPage');
    if (!PAGE) {
        return;
    }

    var FETCH_URL = PAGE.getAttribute('data-fetch-url') || '';
    var STORE_URL = PAGE.getAttribute('data-store-url') || '';
    var UPDATE_URL_TEMPLATE = PAGE.getAttribute('data-update-url-template') || '';
    var DELETE_URL_TEMPLATE = PAGE.getAttribute('data-delete-url-template') || '';
    var CSRF_TOKEN = PAGE.getAttribute('data-csrf-token') || '';

    var ROWS = [];
    var currentPage = 1;
    var lastPage = 1;
    var totalRows = 0;
    var perPage = 25;
    var isLoading = false;
    var requestToken = 0;
    var textFilterTimers = {};
    var pendingDeleteId = null;
    var activeListController = null;
    var modalOptionsPromise = null;
    var hasAbortController = typeof AbortController !== 'undefined';

    var optionsCache = {
        school_years: [],
        semesters: [],
        courses: []
    };

    var filters = {
        school_year: '',
        semester: '',
        section: '',
        course_query: '',
        search: ''
    };

    var els = {
        body: document.getElementById('smBody'),
        schoolYear: document.getElementById('smSY'),
        semester: document.getElementById('smSemester'),
        section: document.getElementById('smSection'),
        course: document.getElementById('smCourse'),
        search: document.getElementById('smSearch'),
        perPage: document.getElementById('smPerPage'),
        prevBtn: document.getElementById('smPrevBtn'),
        nextBtn: document.getElementById('smNextBtn'),
        pageInfo: document.getElementById('smPageInfo'),
        totalInfo: document.getElementById('smTotalInfo'),
        addBtn: document.getElementById('smAddBtn'),

        addModal: document.getElementById('addSlotModal'),
        addForm: document.getElementById('addSlotForm'),
        addCancelBtn: document.getElementById('addSlotCancelBtn'),
        addSchoolYear: document.getElementById('addSlotSchoolYear'),
        addSemester: document.getElementById('addSlotSemester'),
        addCourse: document.getElementById('addSlotCourse'),
        addSection: document.getElementById('addSlotSection'),
        addSubject: document.getElementById('addSlotSubject'),
        addSchedule: document.getElementById('addSlotSchedule'),
        addTotal: document.getElementById('addSlotTotal'),
        addEnrolled: document.getElementById('addSlotEnrolled'),

        editModal: document.getElementById('editSlotModal'),
        editForm: document.getElementById('editSlotForm'),
        editCancelBtn: document.getElementById('editSlotCancelBtn'),
        editId: document.getElementById('editSlotId'),
        editSchoolYear: document.getElementById('editSlotSchoolYear'),
        editSemester: document.getElementById('editSlotSemester'),
        editCourse: document.getElementById('editSlotCourse'),
        editSection: document.getElementById('editSlotSection'),
        editSubject: document.getElementById('editSlotSubject'),
        editSchedule: document.getElementById('editSlotSchedule'),
        editTotal: document.getElementById('editSlotTotal'),
        editEnrolled: document.getElementById('editSlotEnrolled'),

        deleteModal: document.getElementById('deleteSlotModal'),
        deleteName: document.getElementById('deleteSlotName'),
        deleteCancelBtn: document.getElementById('deleteSlotCancelBtn'),
        deleteConfirmBtn: document.getElementById('deleteSlotConfirmBtn'),

        successModal: document.getElementById('slotSuccessModal'),
        successMessage: document.getElementById('slotSuccessMsg'),
        successOkBtn: document.getElementById('slotSuccessOkBtn')
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function toInt(value, fallback) {
        var parsed = parseInt(String(value), 10);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function closeOpenMenus() {
        document.querySelectorAll('#smTable .apst-dropdown').forEach(function (dropdown) {
            dropdown.classList.remove('open');
            dropdown.classList.remove('drop-up');
            dropdown.style.top = '';
            dropdown.style.left = '';
            dropdown.style.bottom = '';
        });
    }

    function showErrorMessage(message) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast(message, 'warning');
            return;
        }

        alert(message);
    }

    function showSuccessMessage(message) {
        if (els.successMessage) {
            els.successMessage.textContent = message;
        }

        if (els.successModal) {
            setModalVisible(els.successModal, true);
            return;
        }

        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast(message, 'success');
            return;
        }

        console.log(message);
    }

    function closeSuccessModal() {
        setModalVisible(els.successModal, false);
    }

    function getPayloadErrorMessage(payload, fallbackMessage) {
        if (payload && payload.errors && typeof payload.errors === 'object') {
            var firstKey = Object.keys(payload.errors)[0];
            if (firstKey && payload.errors[firstKey] && payload.errors[firstKey][0]) {
                return payload.errors[firstKey][0];
            }
        }

        if (payload && payload.message) {
            return payload.message;
        }

        return fallbackMessage;
    }

    function getUrlFromTemplate(template, id) {
        if (!template || !id) {
            return '';
        }

        return template.replace('__SLOT_ID__', String(id));
    }

    function sendJsonRequest(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: payload ? JSON.stringify(payload) : null,
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (responsePayload) {
                if (!response.ok) {
                    throw responsePayload;
                }

                return responsePayload;
            });
        });
    }

    function fetchJson(url, signal) {
        var options = {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };

        if (signal) {
            options.signal = signal;
        }

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

    function buildFetchUrl(pageValue, includeOptions, optionsMode) {
        var params = [
            'page=' + encodeURIComponent(String(pageValue)),
            'per_page=' + encodeURIComponent(String(perPage))
        ];

        if (filters.school_year) {
            params.push('school_year=' + encodeURIComponent(filters.school_year));
        }

        if (filters.semester) {
            params.push('semester=' + encodeURIComponent(filters.semester));
        }

        if (filters.section) {
            params.push('section=' + encodeURIComponent(filters.section));
        }

        if (filters.course_query && filters.course_query.length >= 2) {
            params.push('course_query=' + encodeURIComponent(filters.course_query));
        }

        if (filters.search && filters.search.length >= 2) {
            params.push('search=' + encodeURIComponent(filters.search));
        }

        if (includeOptions) {
            params.push('include_options=1');

            if (optionsMode) {
                params.push('options_mode=' + encodeURIComponent(String(optionsMode)));
            }
        }

        return FETCH_URL + (FETCH_URL.indexOf('?') === -1 ? '?' : '&') + params.join('&');
    }

    function setLoadingState(loading) {
        isLoading = !!loading;

        if (els.prevBtn) {
            els.prevBtn.disabled = isLoading || currentPage <= 1;
        }

        if (els.nextBtn) {
            els.nextBtn.disabled = isLoading || currentPage >= lastPage;
        }

        if (els.perPage) {
            els.perPage.disabled = isLoading;
        }

        if (isLoading && els.body) {
            els.body.innerHTML = '<tr><td colspan="10">Loading slots...</td></tr>';
        }
    }

    function getStatusClass(percentage) {
        if (percentage >= 90) {
            return 'is-critical';
        }

        if (percentage >= 50) {
            return 'is-warning';
        }

        return 'is-open';
    }

    function formatCourseLabel(row) {
        var code = String(row.course_code || '').trim();
        var name = String(row.course_name || '').trim();

        if (code && name) {
            return code + ' - ' + name;
        }

        if (code) {
            return code;
        }

        if (name) {
            return name;
        }

        return '-';
    }

    function renderTable() {
        if (!els.body) {
            return;
        }

        els.body.innerHTML = '';

        if (!ROWS.length) {
            els.body.innerHTML = '<tr><td colspan="10">No slots found.</td></tr>';
            return;
        }

        ROWS.forEach(function (row) {
            var percentage = clamp(toInt(row.utilization_pct, 0), 0, 100);
            var statusClass = getStatusClass(percentage);
            var tr = document.createElement('tr');

            tr.innerHTML =
                '<td>' +
                    '<div class="apst-action-btn" data-sm-menu-toggle="' + Number(row.id) + '">' +
                        '<span></span><span></span><span></span>' +
                    '</div>' +
                    '<div class="apst-dropdown" id="smMenu' + Number(row.id) + '">' +
                        '<button type="button" data-sm-action="edit" data-id="' + Number(row.id) + '">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                            ' Edit' +
                        '</button>' +
                        '<button type="button" class="apst-del-btn" data-sm-action="delete" data-id="' + Number(row.id) + '">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                            ' Delete' +
                        '</button>' +
                    '</div>' +
                '</td>' +
                '<td>' + escapeHtml(row.school_year) + '</td>' +
                '<td>' + escapeHtml(row.semester) + '</td>' +
                '<td>' + escapeHtml(formatCourseLabel(row)) + '</td>' +
                '<td>' + escapeHtml(row.section) + '</td>' +
                '<td>' + escapeHtml(row.subject) + '</td>' +
                '<td>' + escapeHtml(row.schedule) + '</td>' +
                '<td class="sm-number-cell">' + escapeHtml(row.total_slots) + '</td>' +
                '<td class="sm-number-cell">' + escapeHtml(row.enrolled_slots) + '</td>' +
                '<td>' +
                    '<div class="slot-status-wrap">' +
                        '<div class="slot-progress-bar">' +
                            '<div class="slot-progress-fill ' + statusClass + '" style="width:' + percentage + '%;"></div>' +
                        '</div>' +
                        '<span class="slot-pct ' + statusClass + '">' + percentage + '%</span>' +
                        '<span class="slot-status-label ' + statusClass + '">' + escapeHtml(row.status_label || 'Open') + '</span>' +
                    '</div>' +
                '</td>';

            els.body.appendChild(tr);
        });
    }

    function renderPagination() {
        if (els.pageInfo) {
            els.pageInfo.textContent = 'Page ' + currentPage + ' of ' + lastPage;
        }

        if (els.totalInfo) {
            els.totalInfo.textContent = totalRows + ' total slots';
        }

        if (els.prevBtn) {
            els.prevBtn.disabled = isLoading || currentPage <= 1;
        }

        if (els.nextBtn) {
            els.nextBtn.disabled = isLoading || currentPage >= lastPage;
        }
    }

    function renderSchoolYearSelect(select, includeAllOption, selectedValue) {
        if (!select) {
            return;
        }

        var optionsHtml = '';
        if (includeAllOption) {
            optionsHtml += '<option value="">- All -</option>';
        } else {
            optionsHtml += '<option value="">- Select School Year -</option>';
        }

        optionsCache.school_years.forEach(function (year) {
            optionsHtml += '<option value="' + escapeHtml(year) + '">' + escapeHtml(year) + '</option>';
        });

        select.innerHTML = optionsHtml;
        select.value = selectedValue || '';

        if (select.value !== (selectedValue || '')) {
            select.value = '';
        }
    }

    function renderSemesterSelect(select, includeAllOption, selectedValue) {
        if (!select) {
            return;
        }

        var optionsHtml = '';
        if (includeAllOption) {
            optionsHtml += '<option value="">- All -</option>';
        } else {
            optionsHtml += '<option value="">- Select Semester -</option>';
        }

        optionsCache.semesters.forEach(function (semester) {
            optionsHtml += '<option value="' + escapeHtml(semester) + '">' + escapeHtml(semester) + '</option>';
        });

        select.innerHTML = optionsHtml;
        select.value = selectedValue || '';

        if (select.value !== (selectedValue || '')) {
            select.value = '';
        }
    }

    function renderCourseSelect(select, includeAllOption, selectedValue) {
        if (!select) {
            return;
        }

        var optionsHtml = '';
        if (includeAllOption) {
            optionsHtml += '<option value="">- All -</option>';
        } else {
            optionsHtml += '<option value="">- Select Course -</option>';
        }

        optionsCache.courses.forEach(function (course) {
            var label = String(course.label || course.code || course.name || '').trim();
            if (!label) {
                label = 'Course #' + String(course.id || '');
            }

            optionsHtml += '<option value="' + Number(course.id) + '">' + escapeHtml(label) + '</option>';
        });

        select.innerHTML = optionsHtml;
        select.value = selectedValue || '';

        if (select.value !== (selectedValue || '')) {
            select.value = '';
        }
    }

    function renderFilterOptions() {
        renderSchoolYearSelect(els.schoolYear, true, filters.school_year);
        renderSemesterSelect(els.semester, true, filters.semester);
    }

    function renderModalOptions(addCourseSelectedValue, editCourseSelectedValue) {
        var addSchoolYearValue = filters.school_year || (optionsCache.school_years[0] || '');
        var addSemesterValue = filters.semester || (optionsCache.semesters[0] || '');
        var addCourseValue = addCourseSelectedValue || (optionsCache.courses[0] ? String(optionsCache.courses[0].id) : '');
        var editCourseValue = editCourseSelectedValue || (els.editCourse ? els.editCourse.value : '');

        renderSchoolYearSelect(els.addSchoolYear, false, addSchoolYearValue);
        renderSemesterSelect(els.addSemester, false, addSemesterValue);
        renderCourseSelect(els.addCourse, false, addCourseValue);

        renderSchoolYearSelect(els.editSchoolYear, false, els.editSchoolYear ? els.editSchoolYear.value : '');
        renderSemesterSelect(els.editSemester, false, els.editSemester ? els.editSemester.value : '');
        renderCourseSelect(els.editCourse, false, editCourseValue);
    }

    function syncOptions(options) {
        if (!options || typeof options !== 'object') {
            return;
        }

        if (Array.isArray(options.school_years)) {
            optionsCache.school_years = options.school_years;
        }

        if (Array.isArray(options.semesters)) {
            optionsCache.semesters = options.semesters;
        }

        if (Array.isArray(options.courses)) {
            optionsCache.courses = options.courses;
        }

        renderFilterOptions();
    }

    function loadSlots(pageToLoad, includeOptions, optionsMode) {
        if (!FETCH_URL) {
            return;
        }

        var safePage = toInt(pageToLoad, currentPage);
        if (safePage < 1) {
            safePage = 1;
        }

        currentPage = safePage;
        var token = ++requestToken;

        if (hasAbortController && activeListController) {
            activeListController.abort();
        }

        if (hasAbortController) {
            activeListController = new AbortController();
        }

        setLoadingState(true);

        fetchJson(
            buildFetchUrl(currentPage, !!includeOptions, optionsMode || ''),
            hasAbortController && activeListController ? activeListController.signal : null
        ).then(function (payload) {
            if (token !== requestToken) {
                return;
            }

            ROWS = payload && Array.isArray(payload.rows) ? payload.rows : [];

            var meta = payload && payload.meta ? payload.meta : {};
            currentPage = toInt(meta.page, currentPage);
            lastPage = Math.max(1, toInt(meta.last_page, 1));
            totalRows = Math.max(0, toInt(meta.total, 0));
            perPage = clamp(toInt(meta.per_page, perPage), 10, 100);

            if (els.perPage) {
                els.perPage.value = String(perPage);
            }

            syncOptions(payload ? payload.options : null);

            if (ROWS.length === 0 && totalRows > 0 && currentPage > 1) {
                loadSlots(currentPage - 1);
                return;
            }

            renderTable();
            renderPagination();
        }).catch(function (payload) {
            if (token !== requestToken) {
                return;
            }

            if (payload && payload.name === 'AbortError') {
                return;
            }

            console.error(payload);
            showErrorMessage(getPayloadErrorMessage(payload, 'Unable to load slot data right now.'));
        }).finally(function () {
            if (token !== requestToken) {
                return;
            }

            setLoadingState(false);
            renderPagination();
        });
    }

    function ensureModalOptionsLoaded() {
        if (optionsCache.courses.length > 0) {
            return Promise.resolve();
        }

        if (modalOptionsPromise) {
            return modalOptionsPromise;
        }

        modalOptionsPromise = fetchJson(
            buildFetchUrl(1, true, 'modal'),
            null
        ).then(function (payload) {
            syncOptions(payload ? payload.options : null);
        }).catch(function (payload) {
            console.error(payload);
            showErrorMessage(getPayloadErrorMessage(payload, 'Unable to load course options right now.'));
            throw payload;
        }).finally(function () {
            modalOptionsPromise = null;
        });

        return modalOptionsPromise;
    }

    function setModalVisible(modalElement, visible) {
        if (!modalElement) {
            return;
        }

        modalElement.classList.toggle('is-hidden', !visible);
        modalElement.style.display = visible ? 'flex' : 'none';
    }

    function closeAllModals() {
        setModalVisible(els.addModal, false);
        setModalVisible(els.editModal, false);
        setModalVisible(els.deleteModal, false);
        closeSuccessModal();
    }

    function normalizeText(value) {
        return String(value || '').trim();
    }

    function buildPayload(prefix) {
        var schoolYear = normalizeText(prefix === 'add' ? els.addSchoolYear.value : els.editSchoolYear.value);
        var semester = normalizeText(prefix === 'add' ? els.addSemester.value : els.editSemester.value);
        var courseId = toInt(prefix === 'add' ? els.addCourse.value : els.editCourse.value, 0);
        var section = normalizeText(prefix === 'add' ? els.addSection.value : els.editSection.value);
        var subject = normalizeText(prefix === 'add' ? els.addSubject.value : els.editSubject.value);
        var schedule = normalizeText(prefix === 'add' ? els.addSchedule.value : els.editSchedule.value);
        var totalSlots = toInt(prefix === 'add' ? els.addTotal.value : els.editTotal.value, 0);
        var enrolledSlots = toInt(prefix === 'add' ? els.addEnrolled.value : els.editEnrolled.value, 0);

        return {
            school_year: schoolYear,
            semester: semester,
            course_id: courseId,
            section: section,
            subject: subject,
            schedule: schedule,
            total_slots: totalSlots,
            enrolled_slots: enrolledSlots
        };
    }

    function validatePayload(payload) {
        if (!payload.school_year) {
            return 'School year is required.';
        }

        if (!payload.semester) {
            return 'Semester is required.';
        }

        if (!payload.course_id) {
            return 'Course is required.';
        }

        if (!payload.section) {
            return 'Section is required.';
        }

        if (!payload.subject) {
            return 'Subject is required.';
        }

        if (!payload.schedule) {
            return 'Schedule is required.';
        }

        if (!Number.isFinite(payload.total_slots) || payload.total_slots < 1) {
            return 'Total slots must be at least 1.';
        }

        if (!Number.isFinite(payload.enrolled_slots) || payload.enrolled_slots < 0) {
            return 'Enrolled slots must be zero or greater.';
        }

        if (payload.enrolled_slots > payload.total_slots) {
            return 'Enrolled slots cannot be greater than total slots.';
        }

        return '';
    }

    function openAddModal() {
        if (!els.addForm) {
            return;
        }

        els.addForm.reset();

        ensureModalOptionsLoaded().then(function () {
            renderModalOptions('', '');

            if (filters.section && els.addSection) {
                els.addSection.value = filters.section;
            }

            if (els.addEnrolled) {
                els.addEnrolled.value = '0';
            }

            setModalVisible(els.addModal, true);
        }).catch(function () {
            return;
        });
    }

    function openEditModal(id) {
        var row = ROWS.find(function (item) {
            return Number(item.id) === Number(id);
        });

        if (!row || !els.editForm) {
            return;
        }

        ensureModalOptionsLoaded().then(function () {
            renderModalOptions('', row.course_id ? String(row.course_id) : '');

            els.editId.value = String(row.id);
            els.editSchoolYear.value = row.school_year || '';
            els.editSemester.value = row.semester || '';
            els.editCourse.value = row.course_id ? String(row.course_id) : '';
            els.editSection.value = row.section || '';
            els.editSubject.value = row.subject || '';
            els.editSchedule.value = row.schedule || '';
            els.editTotal.value = String(toInt(row.total_slots, 0));
            els.editEnrolled.value = String(toInt(row.enrolled_slots, 0));

            setModalVisible(els.editModal, true);
        }).catch(function () {
            return;
        });
    }

    function openDeleteModal(id) {
        var row = ROWS.find(function (item) {
            return Number(item.id) === Number(id);
        });

        if (!row) {
            return;
        }

        pendingDeleteId = Number(row.id);

        if (els.deleteName) {
            els.deleteName.textContent = (row.subject || '-') + ' - ' + (row.section || '-');
        }

        setModalVisible(els.deleteModal, true);
    }

    function handleAddSubmit(event) {
        event.preventDefault();

        if (isLoading) {
            return;
        }

        var payload = buildPayload('add');
        var validationMessage = validatePayload(payload);
        if (validationMessage) {
            showErrorMessage(validationMessage);
            return;
        }

        sendJsonRequest(STORE_URL, 'POST', payload).then(function () {
            setModalVisible(els.addModal, false);
            showSuccessMessage('Slot added successfully.');
            loadSlots(1, false, '');
        }).catch(function (responsePayload) {
            console.error(responsePayload);
            showErrorMessage(getPayloadErrorMessage(responsePayload, 'Unable to add slot right now.'));
        });
    }

    function handleEditSubmit(event) {
        event.preventDefault();

        if (isLoading) {
            return;
        }

        var id = toInt(els.editId ? els.editId.value : '', 0);
        if (!id) {
            showErrorMessage('Unable to update slot right now.');
            return;
        }

        var payload = buildPayload('edit');
        var validationMessage = validatePayload(payload);
        if (validationMessage) {
            showErrorMessage(validationMessage);
            return;
        }

        var updateUrl = getUrlFromTemplate(UPDATE_URL_TEMPLATE, id);
        sendJsonRequest(updateUrl, 'PUT', payload).then(function () {
            setModalVisible(els.editModal, false);
            showSuccessMessage('Slot updated successfully.');
            loadSlots(currentPage, false, '');
        }).catch(function (responsePayload) {
            console.error(responsePayload);
            showErrorMessage(getPayloadErrorMessage(responsePayload, 'Unable to update slot right now.'));
        });
    }

    function handleDeleteConfirm() {
        if (isLoading || !pendingDeleteId) {
            return;
        }

        var deleteUrl = getUrlFromTemplate(DELETE_URL_TEMPLATE, pendingDeleteId);
        sendJsonRequest(deleteUrl, 'DELETE', null).then(function () {
            pendingDeleteId = null;
            setModalVisible(els.deleteModal, false);
            showSuccessMessage('Slot deleted successfully.');
            loadSlots(currentPage, false, '');
        }).catch(function (responsePayload) {
            console.error(responsePayload);
            showErrorMessage(getPayloadErrorMessage(responsePayload, 'Unable to delete slot right now.'));
        });
    }

    function scheduleTextFilterReload(fieldName, rawValue) {
        var normalizedValue = normalizeText(rawValue);
        var previousValue = normalizeText(filters[fieldName]);

        filters[fieldName] = normalizedValue;

        if (textFilterTimers[fieldName]) {
            clearTimeout(textFilterTimers[fieldName]);
        }

        textFilterTimers[fieldName] = setTimeout(function () {
            delete textFilterTimers[fieldName];

            if ((fieldName === 'search' || fieldName === 'course_query')
                && normalizedValue !== ''
                && normalizedValue.length < 2) {
                if (previousValue.length >= 2) {
                    loadSlots(1, false, '');
                }

                return;
            }

            loadSlots(1, false, '');
        }, 420);
    }

    function positionMenu(menu, trigger) {
        var triggerRect = trigger.getBoundingClientRect();

        menu.style.position = 'fixed';
        menu.style.left = (triggerRect.right + 4) + 'px';
        menu.style.top = triggerRect.top + 'px';
        menu.style.bottom = 'auto';
        menu.classList.remove('drop-up');

        var menuRect = menu.getBoundingClientRect();
        if (menuRect.right > (window.innerWidth - 8)) {
            menu.style.left = Math.max(8, window.innerWidth - menuRect.width - 8) + 'px';
        }

        if (menuRect.bottom > (window.innerHeight - 8)) {
            menu.classList.add('drop-up');
            menu.style.top = 'auto';
            menu.style.bottom = (window.innerHeight - triggerRect.bottom) + 'px';
        }
    }

    function bindEvents() {
        if (els.schoolYear) {
            els.schoolYear.addEventListener('change', function () {
                filters.school_year = normalizeText(els.schoolYear.value);
                loadSlots(1, false, '');
            });
        }

        if (els.semester) {
            els.semester.addEventListener('change', function () {
                filters.semester = normalizeText(els.semester.value);
                loadSlots(1, false, '');
            });
        }

        if (els.section) {
            els.section.addEventListener('input', function () {
                scheduleTextFilterReload('section', els.section.value);
            });
        }

        if (els.course) {
            els.course.addEventListener('input', function () {
                scheduleTextFilterReload('course_query', els.course.value);
            });
        }

        if (els.search) {
            els.search.addEventListener('input', function () {
                scheduleTextFilterReload('search', els.search.value);
            });
        }

        if (els.perPage) {
            perPage = clamp(toInt(els.perPage.value, 25), 10, 100);
            els.perPage.addEventListener('change', function () {
                perPage = clamp(toInt(els.perPage.value, 25), 10, 100);
                loadSlots(1, false, '');
            });
        }

        if (els.prevBtn) {
            els.prevBtn.addEventListener('click', function () {
                if (isLoading || currentPage <= 1) {
                    return;
                }

                loadSlots(currentPage - 1, false, '');
            });
        }

        if (els.nextBtn) {
            els.nextBtn.addEventListener('click', function () {
                if (isLoading || currentPage >= lastPage) {
                    return;
                }

                loadSlots(currentPage + 1, false, '');
            });
        }

        if (els.addBtn) {
            els.addBtn.addEventListener('click', openAddModal);
        }

        if (els.addCancelBtn) {
            els.addCancelBtn.addEventListener('click', function () {
                setModalVisible(els.addModal, false);
            });
        }

        if (els.editCancelBtn) {
            els.editCancelBtn.addEventListener('click', function () {
                setModalVisible(els.editModal, false);
            });
        }

        if (els.deleteCancelBtn) {
            els.deleteCancelBtn.addEventListener('click', function () {
                pendingDeleteId = null;
                setModalVisible(els.deleteModal, false);
            });
        }

        if (els.deleteConfirmBtn) {
            els.deleteConfirmBtn.addEventListener('click', handleDeleteConfirm);
        }

        if (els.successOkBtn) {
            els.successOkBtn.addEventListener('click', closeSuccessModal);
        }

        if (els.addForm) {
            els.addForm.addEventListener('submit', handleAddSubmit);
        }

        if (els.editForm) {
            els.editForm.addEventListener('submit', handleEditSubmit);
        }

        PAGE.addEventListener('click', function (event) {
            var toggleBtn = event.target.closest('[data-sm-menu-toggle]');
            if (toggleBtn) {
                event.preventDefault();

                var slotId = toggleBtn.getAttribute('data-sm-menu-toggle');
                var menu = document.getElementById('smMenu' + String(slotId));
                if (!menu) {
                    return;
                }

                var willOpen = !menu.classList.contains('open');
                closeOpenMenus();

                if (willOpen) {
                    menu.classList.add('open');
                    positionMenu(menu, toggleBtn);
                }

                return;
            }

            var actionBtn = event.target.closest('[data-sm-action]');
            if (actionBtn) {
                event.preventDefault();
                closeOpenMenus();

                var action = actionBtn.getAttribute('data-sm-action');
                var id = toInt(actionBtn.getAttribute('data-id'), 0);
                if (!id) {
                    return;
                }

                if (action === 'edit') {
                    openEditModal(id);
                }

                if (action === 'delete') {
                    openDeleteModal(id);
                }

                return;
            }

            if (!event.target.closest('.apst-dropdown')) {
                closeOpenMenus();
            }
        });

        window.addEventListener('resize', function () {
            var openMenu = document.querySelector('#smTable .apst-dropdown.open');
            if (!openMenu) {
                return;
            }

            var row = openMenu.closest('tr');
            var trigger = row ? row.querySelector('[data-sm-menu-toggle]') : null;
            if (trigger) {
                positionMenu(openMenu, trigger);
            }
        });

        window.addEventListener('scroll', function () {
            closeOpenMenus();
        }, true);

        document.addEventListener('click', function (event) {
            if (!event.target.closest('[data-sm-menu-toggle]') && !event.target.closest('.apst-dropdown')) {
                closeOpenMenus();
            }

            var overlays = [els.addModal, els.editModal, els.deleteModal, els.successModal];
            overlays.forEach(function (overlay) {
                if (overlay && event.target === overlay) {
                    setModalVisible(overlay, false);
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllModals();
                closeOpenMenus();
            }
        });
    }

    bindEvents();
    loadSlots(1, true, 'filters');
})();
