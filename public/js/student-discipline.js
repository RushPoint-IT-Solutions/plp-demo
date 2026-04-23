(function () {
    var SD_STATE = {
        students: [],
        records: [],
        selectedDisciplineStudentId: null,
        selectedRecordId: null,
        activeStudent: null,
        editRecordId: null,
        pendingDeleteRecordId: null,
        pendingDeleteStudentId: null,
        editStudentTargetId: null,
        addSelectedStudent: null,
        addSelectedProgram: null,
        editSelectedProgram: null,
        caseTypeOptions: [],
        actionTypeOptions: [],
        semesterMap: {},
        studentLookupToken: 0,
        programLookupToken: 0
    };

    var SD_SEARCH_DROPDOWN_IDS = [
        'sdAddStudentIdDropdown',
        'sdAddStudentNameDropdown',
        'sdAddProgramDropdown',
        'sdEditProgramDropdown',
        'sdCaseTypeDropdown',
        'sdActionTypeDropdown',
        'sdCalledByDropdown',
        'sdCounselorDropdown'
    ];

    function byId(id) {
        return document.getElementById(id);
    }

    function getPage() {
        return byId('sdPage');
    }

    function getApiConfig() {
        var page = getPage();
        if (!page) {
            return null;
        }

        return {
            listUrl: page.getAttribute('data-list-url') || '',
            studentSearchUrl: page.getAttribute('data-student-search-url') || '',
            programSearchUrl: page.getAttribute('data-program-search-url') || '',
            studentStoreUrl: page.getAttribute('data-student-store-url') || '',
            studentUpdateUrlTemplate: page.getAttribute('data-student-update-url-template') || '',
            studentDestroyUrlTemplate: page.getAttribute('data-student-destroy-url-template') || '',
            recordListUrlTemplate: page.getAttribute('data-record-list-url-template') || '',
            recordStoreUrlTemplate: page.getAttribute('data-record-store-url-template') || '',
            recordUpdateUrlTemplate: page.getAttribute('data-record-update-url-template') || '',
            recordDestroyUrlTemplate: page.getAttribute('data-record-destroy-url-template') || '',
            caseTypeOptions: page.getAttribute('data-case-type-options') || '[]',
            actionTypeOptions: page.getAttribute('data-action-type-options') || '[]',
            semesterMap: page.getAttribute('data-semester-map') || '{}'
        };
    }

    function parseJsonArray(raw) {
        if (!raw) {
            return [];
        }

        try {
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function parseJsonObject(raw) {
        if (!raw) {
            return {};
        }

        try {
            var parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
        } catch (error) {
            return {};
        }
    }

    function normalizeLookupOptions(rawOptions) {
        return rawOptions.map(function (item) {
            var value = normalizeText(item && item.value ? item.value : '');
            var label = normalizeText(item && item.label ? item.label : value);
            return {
                value: value,
                label: label
            };
        }).filter(function (item) {
            return item.value !== '' && item.label !== '';
        });
    }

    function csrfToken() {
        var token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    function normalizeText(value) {
        return String(value || '').trim();
    }

    function normalizeCompare(value) {
        return normalizeText(value).replace(/\s+/g, ' ').toLowerCase();
    }

    function showToast(message, tone) {
        if (typeof window.showRegistrarToast === 'function') {
            window.showRegistrarToast(message, tone || 'success');
            return;
        }

        window.alert(message);
    }

    function hasSqlInjectionPattern(value) {
        var text = normalizeText(value);
        if (text === '') {
            return false;
        }

        var patterns = [
            /\bunion\s+select\b/i,
            /\bor\s+1\s*=\s*1\b/i,
            /\b(and|or)\b\s+['\"]?[0-9a-z_]+['\"]?\s*=\s*['\"]?[0-9a-z_]+['\"]?/i,
            /;\s*(drop|truncate|alter|insert|update|delete|create)\b/i,
            /--|#|\/\*/,
            /\b(information_schema|sleep\s*\(|benchmark\s*\()\b/i
        ];

        for (var i = 0; i < patterns.length; i++) {
            if (patterns[i].test(text)) {
                return true;
            }
        }

        return false;
    }

    function closeSearchDropdown(dropdownId, shouldClear) {
        var dropdown = byId(dropdownId);
        if (!dropdown) {
            return;
        }

        dropdown.classList.remove('is-open');
        if (shouldClear) {
            dropdown.innerHTML = '';
        }
    }

    function closeAllSearchDropdowns(exceptId, shouldClear) {
        SD_SEARCH_DROPDOWN_IDS.forEach(function (dropdownId) {
            if (exceptId && dropdownId === exceptId) {
                return;
            }

            closeSearchDropdown(dropdownId, !!shouldClear);
        });
    }

    function openSearchDropdown(dropdownId, html) {
        var dropdown = byId(dropdownId);
        if (!dropdown) {
            return;
        }

        closeAllSearchDropdowns(dropdownId, false);
        dropdown.innerHTML = html;
        dropdown.classList.add('is-open');
    }

    function requestJson(url, method, payload) {
        var requestMethod = (method || 'GET').toUpperCase();
        var options = {
            method: requestMethod,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };

        if (requestMethod !== 'GET') {
            options.headers['X-CSRF-TOKEN'] = csrfToken();
        }

        if (payload && requestMethod !== 'GET') {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }

        return fetch(url, options).then(function (response) {
            return response.text().then(function (text) {
                var json = {};
                if (text) {
                    try {
                        json = JSON.parse(text);
                    } catch (error) {
                        json = { message: 'Unexpected server response.' };
                    }
                }

                if (!response.ok) {
                    var fallback = json && json.message ? json.message : ('Request failed (' + response.status + ').');
                    var errors = json && json.errors ? json.errors : null;
                    if (errors) {
                        var firstKey = Object.keys(errors)[0];
                        if (firstKey && errors[firstKey] && errors[firstKey][0]) {
                            fallback = errors[firstKey][0];
                        }
                    }

                    throw new Error(fallback);
                }

                return json;
            });
        });
    }

    function toQueryString(values) {
        var parts = [];
        Object.keys(values).forEach(function (key) {
            var value = values[key];
            if (value === null || value === undefined || value === '') {
                return;
            }
            parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
        });
        return parts.join('&');
    }

    function buildUrl(template, token, value) {
        return String(template || '').replace(token, String(value || ''));
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function jsEscape(value) {
        return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    function formatInputDate(value) {
        var normalized = normalizeText(value);
        if (!normalized) {
            return '-';
        }

        var parts = normalized.split('-');
        if (parts.length === 3) {
            return parts[1] + '/' + parts[2] + '/' + parts[0];
        }

        return normalized;
    }

    function todayInputDate() {
        var now = new Date();
        var month = String(now.getMonth() + 1);
        var day = String(now.getDate());
        var year = String(now.getFullYear());

        if (month.length < 2) {
            month = '0' + month;
        }
        if (day.length < 2) {
            day = '0' + day;
        }

        return year + '-' + month + '-' + day;
    }

    function setSelectValue(selectEl, value) {
        if (!selectEl) {
            return;
        }

        selectEl.value = value || '';
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
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
                detail: { target: wrapper }
            }));
            return;
        }

        if (typeof document.createEvent === 'function') {
            var fallbackEvent = document.createEvent('CustomEvent');
            fallbackEvent.initCustomEvent('registrar:listbox:refresh', false, false, { target: wrapper });
            document.dispatchEvent(fallbackEvent);
        }
    }

    function normalizeSemesterTerm(value) {
        var normalized = normalizeCompare(value);
        var aliases = {
            'first': 'First',
            '1st': 'First',
            '1st semester': 'First',
            'first semester': 'First',
            'second': 'Second',
            '2nd': 'Second',
            '2nd semester': 'Second',
            'second semester': 'Second',
            'summer': 'Summer',
            'summer semester': 'Summer'
        };

        return aliases[normalized] || '';
    }

    function configuredTermsForYear(schoolYear) {
        var yearKey = normalizeText(schoolYear);
        var map = SD_STATE.semesterMap || {};
        var rawTerms = yearKey && Array.isArray(map[yearKey])
            ? map[yearKey]
            : [];

        var terms = rawTerms.map(function (item) {
            return normalizeSemesterTerm(item);
        }).filter(function (item, index, list) {
            return item !== '' && list.indexOf(item) === index;
        });

        if (!terms.length) {
            terms = ['First', 'Second', 'Summer'];
        }

        return terms;
    }

    function syncTermOptionsForSchoolYear(preferredTerm) {
        var schoolYearSelect = byId('sdSchoolYear');
        var termSelect = byId('sdTerm');

        if (!schoolYearSelect || !termSelect) {
            return;
        }

        var terms = configuredTermsForYear(schoolYearSelect.value);
        var selectedTerm = normalizeSemesterTerm(preferredTerm || termSelect.value);

        var optionsHtml = '';
        terms.forEach(function (term) {
            optionsHtml += '<option value="' + escapeHtml(term) + '">' + escapeHtml(term) + '</option>';
        });

        termSelect.innerHTML = optionsHtml;

        if (selectedTerm && terms.indexOf(selectedTerm) !== -1) {
            termSelect.value = selectedTerm;
        }

        if (!termSelect.value && terms.length) {
            termSelect.value = terms[0];
        }

        refreshListboxSelect(termSelect);
    }

    function getFilters() {
        return {
            student_id: normalizeText(byId('sdStudentId') ? byId('sdStudentId').value : ''),
            full_name: normalizeText(byId('sdFullName') ? byId('sdFullName').value : ''),
            student_type: normalizeText(byId('sdStudentTypeFilter') ? byId('sdStudentTypeFilter').value : ''),
            school_year: normalizeText(byId('sdSchoolYear') ? byId('sdSchoolYear').value : ''),
            term: normalizeText(byId('sdTerm') ? byId('sdTerm').value : '')
        };
    }

    function closeActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
            menu.classList.remove('open');
            menu.classList.remove('drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.bottom = '';
        });
    }

    function toggleActionMenu(menuId, trigger) {
        var menu = byId(menuId);
        if (!menu || !trigger) {
            return;
        }

        var isOpen = menu.classList.contains('open');
        closeActionMenus();
        if (isOpen) {
            return;
        }

        var rect = trigger.getBoundingClientRect();
        var spacing = 6;
        var menuWidth = menu.offsetWidth || 130;
        var spaceBelow = window.innerHeight - rect.bottom;

        var left = rect.right + spacing;
        if (left + menuWidth > window.innerWidth - spacing) {
            left = rect.left - menuWidth - spacing;
        }
        if (left < spacing) {
            left = spacing;
        }

        menu.style.left = left + 'px';

        if (spaceBelow < 140) {
            menu.classList.add('drop-up');
            menu.style.top = 'auto';
            menu.style.bottom = (window.innerHeight - rect.bottom) + 'px';
        } else {
            menu.style.top = rect.top + 'px';
            menu.style.bottom = 'auto';
        }

        menu.classList.add('open');
    }

    function openOverlay(id) {
        var modal = byId(id);
        if (!modal) {
            return;
        }

        modal.classList.add('is-open');
    }

    function closeOverlay(id) {
        var modal = byId(id);
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
    }

    function renderStudents() {
        var tbody = byId('sdStudentsBody');
        if (!tbody) {
            return;
        }

        var students = SD_STATE.students || [];
        if (!students.length) {
            tbody.innerHTML = '' +
                '<tr class="sd-empty-row"><td colspan="8">List Empty.</td></tr>' +
                '<tr class="sd-total-row"><td colspan="8" class="sd-total-cell">Total Students: <strong>0</strong></td></tr>';
            return;
        }

        var rows = '';
        students.forEach(function (student, index) {
            var menuId = 'sdMenu' + student.discipline_student_id;

            rows += '' +
                '<tr data-sd-discipline-student-id="' + student.discipline_student_id + '">' +
                    '<td>' +
                        '<button type="button" class="apst-action-btn" data-sd-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions">' +
                            '<span></span><span></span><span></span>' +
                        '</button>' +
                        '<div class="apst-dropdown" id="' + menuId + '">' +
                            '<button type="button" data-sd-open-action="edit" data-sd-discipline-student-id="' + student.discipline_student_id + '">' +
                                '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                                'Edit' +
                            '</button>' +
                            '<button type="button" class="apst-del-btn" data-sd-open-action="delete" data-sd-discipline-student-id="' + student.discipline_student_id + '">' +
                                '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                                'Delete' +
                            '</button>' +
                        '</div>' +
                    '</td>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + escapeHtml(student.student_no || '-') + '</td>' +
                    '<td><button type="button" class="sd-name-link sd-name-link-btn" data-sd-open-conduct="' + escapeHtml(student.discipline_student_id) + '">' + escapeHtml(student.name || '-') + '</button></td>' +
                    '<td>' + escapeHtml(student.degree_program || '-') + '</td>' +
                    '<td>' + escapeHtml(student.birth_date || '-') + '</td>' +
                    '<td>' + escapeHtml(student.gender || '-') + '</td>' +
                    '<td>' + escapeHtml(student.student_type || '-') + '</td>' +
                '</tr>';
        });

        rows += '<tr class="sd-total-row"><td colspan="8" class="sd-total-cell">Total Students: <strong>' + students.length + '</strong></td></tr>';
        tbody.innerHTML = rows;
    }

    function fetchStudents() {
        var config = getApiConfig();
        if (!config || !config.listUrl) {
            return Promise.resolve();
        }

        var query = toQueryString(getFilters());
        var url = config.listUrl + (query ? ('?' + query) : '');

        return requestJson(url, 'GET').then(function (payload) {
            SD_STATE.students = payload && payload.data ? payload.data : [];
            renderStudents();

            if (SD_STATE.selectedDisciplineStudentId) {
                var selected = findStudentByDisciplineId(SD_STATE.selectedDisciplineStudentId);
                if (!selected) {
                    SD_STATE.selectedDisciplineStudentId = null;
                    SD_STATE.activeStudent = null;
                    window.sdBackToList();
                } else {
                    SD_STATE.activeStudent = selected;
                    updateStudentBanner(selected);
                }
            }
        }).catch(function (error) {
            showToast(error.message || 'Unable to load students.', 'error');
        });
    }

    function findStudentByDisciplineId(disciplineStudentId) {
        var target = String(disciplineStudentId || '');
        for (var i = 0; i < SD_STATE.students.length; i++) {
            if (String(SD_STATE.students[i].discipline_student_id) === target) {
                return SD_STATE.students[i];
            }
        }
        return null;
    }

    function updateStudentBanner(student) {
        if (!student) {
            return;
        }

        var idEl = byId('sdBannerStudentId');
        var nameEl = byId('sdBannerStudentName');

        if (idEl) {
            idEl.textContent = student.student_no || '-';
        }
        if (nameEl) {
            nameEl.textContent = student.name || '-';
        }
    }

    function findRecordById(recordId) {
        var target = String(recordId || '');
        for (var i = 0; i < SD_STATE.records.length; i++) {
            if (String(SD_STATE.records[i].id) === target) {
                return SD_STATE.records[i];
            }
        }

        return null;
    }

    function setRecordDetailValue(elementId, value) {
        var element = byId(elementId);
        if (!element) {
            return;
        }

        var normalized = normalizeText(value);
        element.textContent = normalized !== '' ? normalized : '-';
    }

    function recordStatusLabel(record) {
        return record && record.is_completed ? 'Completed' : 'Open';
    }

    function printFieldValue(value) {
        var normalized = normalizeText(value);
        return normalized !== '' ? normalized : '-';
    }

    function buildPrintRecordCard(record, index) {
        return '' +
            '<article class="sd-record-detail-card sd-print-record-card">' +
                '<div class="sd-record-detail-head">' +
                    '<div class="sd-record-detail-title">Record #' + (index + 1) + '</div>' +
                    '<span class="sd-record-detail-status">' + escapeHtml(recordStatusLabel(record)) + '</span>' +
                '</div>' +
                '<div class="sd-record-detail-grid sd-print-record-grid">' +
                    '<div class="sd-record-detail-item"><span class="sd-record-detail-label">Incident Date</span><strong>' + escapeHtml(printFieldValue(formatInputDate(record.incident_date))) + '</strong></div>' +
                    '<div class="sd-record-detail-item"><span class="sd-record-detail-label">Action Date</span><strong>' + escapeHtml(printFieldValue(formatInputDate(record.action_date))) + '</strong></div>' +
                    '<div class="sd-record-detail-item"><span class="sd-record-detail-label">Case Type</span><strong>' + escapeHtml(printFieldValue(record.case_type)) + '</strong></div>' +
                    '<div class="sd-record-detail-item"><span class="sd-record-detail-label">Action Type</span><strong>' + escapeHtml(printFieldValue(record.action_type)) + '</strong></div>' +
                    '<div class="sd-record-detail-item"><span class="sd-record-detail-label">Called By</span><strong>' + escapeHtml(printFieldValue(record.called_by)) + '</strong></div>' +
                    '<div class="sd-record-detail-item"><span class="sd-record-detail-label">Counselor</span><strong>' + escapeHtml(printFieldValue(record.counselor)) + '</strong></div>' +
                    '<div class="sd-record-detail-item"><span class="sd-record-detail-label">Walk In</span><strong>' + (record.walk_in ? 'Yes' : 'No') + '</strong></div>' +
                    '<div class="sd-record-detail-item"><span class="sd-record-detail-label">Updated By</span><strong>' + escapeHtml(printFieldValue(record.updated_by)) + '</strong></div>' +
                '</div>' +
                '<div class="sd-record-detail-body-grid sd-print-record-body-grid">' +
                    '<article class="sd-record-detail-block"><h4>Description</h4><p>' + escapeHtml(printFieldValue(record.description)) + '</p></article>' +
                    '<article class="sd-record-detail-block"><h4>Remarks</h4><p>' + escapeHtml(printFieldValue(record.remarks)) + '</p></article>' +
                '</div>' +
            '</article>';
    }

    function renderPrintRecordsSheet() {
        var sheet = byId('sdPrintRecordsSheet');
        var list = byId('sdPrintRecordsList');
        var printStudentId = byId('sdPrintStudentId');
        var printStudentName = byId('sdPrintStudentName');

        if (!sheet || !list) {
            return;
        }

        var activeStudent = SD_STATE.activeStudent || findStudentByDisciplineId(SD_STATE.selectedDisciplineStudentId);
        if (printStudentId) {
            printStudentId.textContent = activeStudent && activeStudent.student_no ? activeStudent.student_no : '-';
        }
        if (printStudentName) {
            printStudentName.textContent = activeStudent && activeStudent.name ? activeStudent.name : '-';
        }

        var records = SD_STATE.records || [];
        if (!records.length) {
            list.innerHTML = '';
            sheet.classList.add('sd-hidden');
            return;
        }

        var cards = '';
        records.forEach(function (record, index) {
            cards += buildPrintRecordCard(record, index);
        });

        list.innerHTML = cards;
        sheet.classList.remove('sd-hidden');
    }

    function hideRecordDetails() {
        SD_STATE.selectedRecordId = null;
        var card = byId('sdRecordDetailCard');
        if (card) {
            card.classList.add('sd-hidden');
        }
    }

    function showRecordDetails(record) {
        if (!record) {
            hideRecordDetails();
            return;
        }

        SD_STATE.selectedRecordId = record.id;
        setRecordDetailValue('sdRecordDetailIncidentDate', formatInputDate(record.incident_date));
        setRecordDetailValue('sdRecordDetailActionDate', formatInputDate(record.action_date));
        setRecordDetailValue('sdRecordDetailCaseType', record.case_type || '-');
        setRecordDetailValue('sdRecordDetailActionType', record.action_type || '-');
        setRecordDetailValue('sdRecordDetailCalledBy', record.called_by || '-');
        setRecordDetailValue('sdRecordDetailCounselor', record.counselor || '-');
        setRecordDetailValue('sdRecordDetailWalkIn', record.walk_in ? 'Yes' : 'No');
        setRecordDetailValue('sdRecordDetailUpdatedBy', record.updated_by || '-');
        setRecordDetailValue('sdRecordDetailDescription', record.description || '-');
        setRecordDetailValue('sdRecordDetailRemarks', record.remarks || '-');
        setRecordDetailValue('sdRecordDetailStatus', recordStatusLabel(record));

        var card = byId('sdRecordDetailCard');
        if (card) {
            card.classList.remove('sd-hidden');
        }
    }

    function initializeRecordLookupOptions() {
        var config = getApiConfig();
        SD_STATE.caseTypeOptions = normalizeLookupOptions(parseJsonArray(config ? config.caseTypeOptions : '[]'));
        SD_STATE.actionTypeOptions = normalizeLookupOptions(parseJsonArray(config ? config.actionTypeOptions : '[]'));
    }

    function getRecordLookupOptions(type) {
        if (type === 'action') {
            return SD_STATE.actionTypeOptions || [];
        }

        return SD_STATE.caseTypeOptions || [];
    }

    function closeRecordSearchDropdowns() {
        ['sdCaseTypeDropdown', 'sdActionTypeDropdown', 'sdCalledByDropdown', 'sdCounselorDropdown'].forEach(function (id) {
            closeSearchDropdown(id, true);
        });
    }

    function setRecordLookupValue(type, value, label) {
        var isAction = type === 'action';
        var hidden = byId(isAction ? 'sdActionType' : 'sdCaseType');
        var input = byId(isAction ? 'sdActionTypeSearch' : 'sdCaseTypeSearch');
        var lookupValue = normalizeText(value);
        var lookupLabel = normalizeText(label);

        if (hidden) {
            hidden.value = lookupValue;
        }

        if (!input) {
            return;
        }

        if (lookupLabel !== '') {
            input.value = lookupLabel;
            return;
        }

        if (lookupValue === '') {
            input.value = '';
            return;
        }

        var options = getRecordLookupOptions(type);
        for (var i = 0; i < options.length; i++) {
            if (options[i].value === lookupValue) {
                input.value = options[i].label;
                return;
            }
        }

        input.value = '';
    }

    function renderLookupDropdown(type, term) {
        var isAction = type === 'action';
        var dropdown = byId(isAction ? 'sdActionTypeDropdown' : 'sdCaseTypeDropdown');
        if (!dropdown) {
            return;
        }

        var compareTerm = normalizeCompare(term);
        var options = getRecordLookupOptions(type).filter(function (option) {
            if (!compareTerm) {
                return true;
            }

            return normalizeCompare(option.label).indexOf(compareTerm) !== -1;
        });

        if (!options.length) {
            openSearchDropdown(dropdown.id, '<div class="smrg-search-empty">No matching option found.</div>');
            return;
        }

        var html = '';
        options.forEach(function (option) {
            html += '' +
                '<button type="button" class="smrg-search-option" ' +
                    'data-sd-record-lookup-option="1" ' +
                    'data-sd-record-lookup-type="' + escapeHtml(type) + '" ' +
                    'data-sd-record-lookup-value="' + escapeHtml(option.value) + '" ' +
                    'data-sd-record-lookup-label="' + escapeHtml(option.label) + '">' +
                    escapeHtml(option.label) +
                '</button>';
        });

        openSearchDropdown(dropdown.id, html);
    }

    function collectRecordTextSuggestions(field) {
        var values = [];
        var defaults = field === 'counselor'
            ? ['Guidance Office', 'Ms. Santos', 'Mr. Ramos', 'Guidance Team A', 'Guidance Team B']
            : ['Guidance Office', 'Class Adviser', 'Discipline Office', 'Program Chair', 'Dean\'s Office'];

        SD_STATE.records.forEach(function (record) {
            var raw = field === 'counselor' ? record.counselor : record.called_by;
            var value = normalizeText(raw);
            if (value !== '') {
                values.push(value);
            }
        });

        defaults.forEach(function (item) {
            values.push(item);
        });

        return values.filter(function (value, index, source) {
            return value !== '' && source.indexOf(value) === index;
        });
    }

    function renderRecordTextDropdown(field, term) {
        var isCounselor = field === 'counselor';
        var dropdown = byId(isCounselor ? 'sdCounselorDropdown' : 'sdCalledByDropdown');
        if (!dropdown) {
            return;
        }

        var compareTerm = normalizeCompare(term);
        var options = collectRecordTextSuggestions(field).filter(function (value) {
            if (!compareTerm) {
                return true;
            }

            return normalizeCompare(value).indexOf(compareTerm) !== -1;
        }).slice(0, 12);

        if (!options.length) {
            openSearchDropdown(dropdown.id, '<div class="smrg-search-empty">No matching name found.</div>');
            return;
        }

        var html = '';
        options.forEach(function (option) {
            html += '' +
                '<button type="button" class="smrg-search-option" ' +
                    'data-sd-record-text-option="1" ' +
                    'data-sd-record-text-field="' + escapeHtml(field) + '" ' +
                    'data-sd-record-text-value="' + escapeHtml(option) + '">' +
                    escapeHtml(option) +
                '</button>';
        });

        openSearchDropdown(dropdown.id, html);
    }

    function renderConductRows() {
        var tbody = byId('sdConductBody');
        if (!tbody) {
            return;
        }

        var records = SD_STATE.records || [];
        if (!records.length) {
            tbody.innerHTML = '<tr class="sd-empty-row"><td colspan="5">List Empty.</td></tr>';
            return;
        }

        var rows = '';
        records.forEach(function (record) {
            var menuId = 'sdConductMenu' + record.id;
            var rowClass = String(SD_STATE.selectedRecordId) === String(record.id) ? ' class="sd-record-row-selected"' : '';
            rows += '' +
                '<tr data-sd-record-row="' + escapeHtml(record.id) + '"' + rowClass + '>' +
                    '<td>' + escapeHtml(formatInputDate(record.incident_date)) + '</td>' +
                    '<td>' + escapeHtml(record.case_type || '-') + '</td>' +
                    '<td>' + escapeHtml(record.action_type || '-') + '</td>' +
                    '<td>' + (record.is_completed ? 'Yes' : 'No') + '</td>' +
                    '<td>' +
                        '<button type="button" class="apst-action-btn" data-sd-conduct-menu-toggle="' + menuId + '" aria-label="Open conduct row actions" title="Actions">' +
                            '<span></span><span></span><span></span>' +
                        '</button>' +
                        '<div class="apst-dropdown" id="' + menuId + '">' +
                            '<button type="button" data-sd-conduct-action="edit" data-sd-record-id="' + record.id + '">' +
                                '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                                'Edit' +
                            '</button>' +
                            '<button type="button" class="apst-del-btn" data-sd-conduct-action="delete" data-sd-record-id="' + record.id + '">' +
                                '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                                'Delete' +
                            '</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
        });

        tbody.innerHTML = rows;
    }

    function fetchRecords() {
        var config = getApiConfig();
        if (!config || !config.recordListUrlTemplate || !SD_STATE.selectedDisciplineStudentId) {
            return Promise.resolve();
        }

        var url = buildUrl(config.recordListUrlTemplate, '__DISCIPLINE_STUDENT__', SD_STATE.selectedDisciplineStudentId);

        return requestJson(url, 'GET').then(function (payload) {
            SD_STATE.records = payload && payload.data ? payload.data : [];
            if (!SD_STATE.records.length) {
                hideRecordDetails();
            } else {
                var selected = findRecordById(SD_STATE.selectedRecordId);
                if (!selected) {
                    selected = SD_STATE.records[0];
                }

                showRecordDetails(selected);
            }
            renderConductRows();
            renderPrintRecordsSheet();
        }).catch(function (error) {
            SD_STATE.records = [];
            hideRecordDetails();
            renderConductRows();
            renderPrintRecordsSheet();
            closeRecordSearchDropdowns();
            showToast(error.message || 'Unable to load student conduct records.', 'error');
        });
    }

    function resetRecordModal() {
        SD_STATE.editRecordId = null;

        var incidentDate = byId('sdIncidentDate');
        var walkIn = byId('sdWalkIn');
        var caseType = byId('sdCaseType');
        var calledBy = byId('sdCalledBy');
        var description = byId('sdDescription');
        var actionDate = byId('sdActionDate');
        var completed = byId('sdCompleted');
        var actionType = byId('sdActionType');
        var counselor = byId('sdCounselor');
        var remarks = byId('sdRemarks');
        var saveBtn = byId('sdRecordSaveBtn');

        if (incidentDate) {
            incidentDate.value = todayInputDate();
        }
        if (walkIn) {
            walkIn.checked = false;
        }
        if (caseType) {
            caseType.value = '';
            setRecordLookupValue('case', '', '');
        }
        if (calledBy) {
            calledBy.value = '';
        }
        if (description) {
            description.value = '';
        }
        if (actionDate) {
            actionDate.value = todayInputDate();
        }
        if (completed) {
            completed.checked = false;
        }
        if (actionType) {
            actionType.value = '';
            setRecordLookupValue('action', '', '');
        }
        if (counselor) {
            counselor.value = '';
        }
        if (remarks) {
            remarks.value = '';
        }
        if (saveBtn) {
            saveBtn.textContent = 'Save Record';
        }

        closeRecordSearchDropdowns();
    }

    function fillRecordModal(record) {
        if (!record) {
            return;
        }

        SD_STATE.editRecordId = record.id;

        var incidentDate = byId('sdIncidentDate');
        var walkIn = byId('sdWalkIn');
        var caseType = byId('sdCaseType');
        var calledBy = byId('sdCalledBy');
        var description = byId('sdDescription');
        var actionDate = byId('sdActionDate');
        var completed = byId('sdCompleted');
        var actionType = byId('sdActionType');
        var counselor = byId('sdCounselor');
        var remarks = byId('sdRemarks');
        var saveBtn = byId('sdRecordSaveBtn');

        if (incidentDate) {
            incidentDate.value = record.incident_date || '';
        }
        if (walkIn) {
            walkIn.checked = !!record.walk_in;
        }
        if (caseType) {
            caseType.value = record.case_type_id ? String(record.case_type_id) : '';
            setRecordLookupValue('case', caseType.value, record.case_type || '');
        }
        if (calledBy) {
            calledBy.value = record.called_by || '';
        }
        if (description) {
            description.value = record.description || '';
        }
        if (actionDate) {
            actionDate.value = record.action_date || '';
        }
        if (completed) {
            completed.checked = !!record.is_completed;
        }
        if (actionType) {
            actionType.value = record.action_type_id ? String(record.action_type_id) : '';
            setRecordLookupValue('action', actionType.value, record.action_type || '');
        }
        if (counselor) {
            counselor.value = record.counselor || '';
        }
        if (remarks) {
            remarks.value = record.remarks || '';
        }
        if (saveBtn) {
            saveBtn.textContent = 'Save Changes';
        }
    }

    function closeStudentSearchDropdowns() {
        ['sdAddStudentIdDropdown', 'sdAddStudentNameDropdown'].forEach(function (id) {
            closeSearchDropdown(id, true);
        });
    }

    function closeProgramDropdowns() {
        ['sdAddProgramDropdown', 'sdEditProgramDropdown'].forEach(function (id) {
            closeSearchDropdown(id, true);
        });
    }

    function setAddStudentButtonState(enabled) {
        var button = byId('sdAddStudentSubmitBtn');
        if (!button) {
            return;
        }

        button.disabled = !enabled;
    }

    function updateAddStudentState() {
        setAddStudentButtonState(!!(SD_STATE.addSelectedStudent && SD_STATE.addSelectedProgram));
    }

    function normalizeGender(value) {
        var normalized = normalizeText(value).toLowerCase();
        if (normalized === 'female' || normalized === 'f') {
            return 'Female';
        }

        return 'Male';
    }

    function lockAddStudentAutofilledFields() {
        var programInput = byId('sdAddDegreeProgram');
        var birthDateInput = byId('sdAddBirthDate');
        var genderSelect = byId('sdAddGender');
        var genderWrapper = genderSelect ? genderSelect.closest('.rg-listbox') : null;
        var genderTrigger = genderWrapper ? genderWrapper.querySelector('[data-select-trigger]') : null;

        if (programInput) {
            programInput.readOnly = true;
            programInput.disabled = true;
            programInput.setAttribute('tabindex', '-1');
        }

        if (birthDateInput) {
            birthDateInput.readOnly = true;
            birthDateInput.disabled = true;
        }

        if (genderSelect) {
            genderSelect.disabled = true;
        }

        if (genderTrigger) {
            genderTrigger.setAttribute('tabindex', '-1');
            genderTrigger.setAttribute('aria-disabled', 'true');
        }

        closeSearchDropdown('sdAddProgramDropdown', true);
    }

    function resetAddStudentModalState() {
        SD_STATE.addSelectedStudent = null;
        SD_STATE.addSelectedProgram = null;

        var idInput = byId('sdAddStudentId');
        var nameInput = byId('sdAddStudentName');
        var programInput = byId('sdAddDegreeProgram');
        var birthDateInput = byId('sdAddBirthDate');
        var genderInput = byId('sdAddGender');
        var studentDbId = byId('sdAddStudentDbId');
        var courseId = byId('sdAddCourseId');
        var studentType = byId('sdAddStudentType');

        if (idInput) {
            idInput.value = '';
        }
        if (nameInput) {
            nameInput.value = '';
        }
        if (programInput) {
            programInput.value = '';
        }
        if (birthDateInput) {
            birthDateInput.value = '';
        }
        if (genderInput) {
            setSelectValue(genderInput, 'Male');
        }
        if (studentDbId) {
            studentDbId.value = '';
        }
        if (courseId) {
            courseId.value = '';
        }
        if (studentType) {
            setSelectValue(studentType, 'OLD');
            studentType.disabled = true;
        }

        lockAddStudentAutofilledFields();

        closeStudentSearchDropdowns();
        closeProgramDropdowns();
        updateAddStudentState();
    }

    function applyAddStudentCandidate(candidate) {
        if (!candidate) {
            return;
        }

        SD_STATE.addSelectedStudent = candidate;

        var idInput = byId('sdAddStudentId');
        var nameInput = byId('sdAddStudentName');
        var birthDateInput = byId('sdAddBirthDate');
        var genderInput = byId('sdAddGender');
        var studentDbId = byId('sdAddStudentDbId');

        if (idInput) {
            idInput.value = candidate.student_no || '';
        }
        if (nameInput) {
            nameInput.value = candidate.name || '';
        }
        if (birthDateInput) {
            birthDateInput.value = candidate.birth_date_iso || '';
        }
        if (genderInput) {
            setSelectValue(genderInput, normalizeGender(candidate.gender || 'Male'));
        }
        if (studentDbId) {
            studentDbId.value = candidate.id || '';
        }

        lockAddStudentAutofilledFields();

        if (candidate.course_id) {
            applyProgramCandidate({
                id: candidate.course_id,
                label: candidate.course_label || ''
            }, 'add');
        }

        updateAddStudentState();
    }

    function applyProgramCandidate(candidate, target) {
        if (!candidate) {
            return;
        }

        if (target === 'edit') {
            SD_STATE.editSelectedProgram = candidate;
            var editProgramInput = byId('sdEditDegreeProgram');
            var editCourseId = byId('sdEditCourseId');

            if (editProgramInput) {
                editProgramInput.value = candidate.label || '';
            }
            if (editCourseId) {
                editCourseId.value = candidate.id || '';
            }

            return;
        }

        SD_STATE.addSelectedProgram = candidate;

        var addProgramInput = byId('sdAddDegreeProgram');
        var addCourseId = byId('sdAddCourseId');

        if (addProgramInput) {
            addProgramInput.value = candidate.label || '';
        }
        if (addCourseId) {
            addCourseId.value = candidate.id || '';
        }

        updateAddStudentState();
    }

    function renderStudentSearchDropdown(dropdownId, candidates) {
        var dropdown = byId(dropdownId);
        if (!dropdown) {
            return;
        }

        if (!candidates.length) {
            openSearchDropdown(dropdown.id, '<div class="smrg-search-empty">No matching student found.</div>');
            return;
        }

        var html = '';
        candidates.forEach(function (candidate) {
            html += '' +
                '<button type="button" class="smrg-search-option" ' +
                    'data-sd-student-option="1" ' +
                    'data-sd-student-id="' + escapeHtml(candidate.id) + '" ' +
                    'data-sd-student-no="' + escapeHtml(candidate.student_no) + '" ' +
                    'data-sd-student-name="' + escapeHtml(candidate.name) + '" ' +
                    'data-sd-student-course-id="' + escapeHtml(candidate.course_id || '') + '" ' +
                    'data-sd-student-course-label="' + escapeHtml(candidate.course_label || '') + '" ' +
                    'data-sd-student-birth-date="' + escapeHtml(candidate.birth_date || '-') + '" ' +
                    'data-sd-student-birth-date-iso="' + escapeHtml(candidate.birth_date_iso || '') + '" ' +
                    'data-sd-student-gender="' + escapeHtml(candidate.gender || '-') + '">' +
                    '<strong>' + escapeHtml(candidate.student_no) + '</strong> - ' + escapeHtml(candidate.name) +
                '</button>';
        });

        openSearchDropdown(dropdown.id, html);
    }

    function renderProgramSearchDropdown(dropdownId, candidates, target) {
        var dropdown = byId(dropdownId);
        if (!dropdown) {
            return;
        }

        if (!candidates.length) {
            openSearchDropdown(dropdown.id, '<div class="smrg-search-empty">No matching degree program found.</div>');
            return;
        }

        var html = '';
        candidates.forEach(function (candidate) {
            html += '' +
                '<button type="button" class="smrg-search-option" ' +
                    'data-sd-program-option="1" ' +
                    'data-sd-program-target="' + escapeHtml(target) + '" ' +
                    'data-sd-program-id="' + escapeHtml(candidate.id) + '" ' +
                    'data-sd-program-label="' + escapeHtml(candidate.label || '') + '">' +
                    escapeHtml(candidate.label || '') +
                '</button>';
        });

        openSearchDropdown(dropdown.id, html);
    }

    function requestStudentCandidates(term, done) {
        var config = getApiConfig();
        if (!config || !config.studentSearchUrl) {
            done([]);
            return;
        }

        SD_STATE.studentLookupToken += 1;
        var token = SD_STATE.studentLookupToken;
        var url = config.studentSearchUrl + '?q=' + encodeURIComponent(term || '') + '&limit=12';

        requestJson(url, 'GET').then(function (payload) {
            if (token !== SD_STATE.studentLookupToken) {
                return;
            }

            done(payload && payload.data ? payload.data : []);
        }).catch(function () {
            if (token !== SD_STATE.studentLookupToken) {
                return;
            }

            done([]);
        });
    }

    function requestProgramCandidates(term, done) {
        var config = getApiConfig();
        if (!config || !config.programSearchUrl) {
            done([]);
            return;
        }

        SD_STATE.programLookupToken += 1;
        var token = SD_STATE.programLookupToken;
        var url = config.programSearchUrl + '?q=' + encodeURIComponent(term || '') + '&limit=12';

        requestJson(url, 'GET').then(function (payload) {
            if (token !== SD_STATE.programLookupToken) {
                return;
            }

            done(payload && payload.data ? payload.data : []);
        }).catch(function () {
            if (token !== SD_STATE.programLookupToken) {
                return;
            }

            done([]);
        });
    }

    function bindSearchInputs() {
        var addStudentId = byId('sdAddStudentId');
        var addStudentName = byId('sdAddStudentName');
        var addDegreeProgram = byId('sdAddDegreeProgram');
        var editDegreeProgram = byId('sdEditDegreeProgram');
        var caseTypeSearch = byId('sdCaseTypeSearch');
        var actionTypeSearch = byId('sdActionTypeSearch');
        var calledByInput = byId('sdCalledBy');
        var counselorInput = byId('sdCounselor');

        if (addStudentId) {
            addStudentId.addEventListener('focus', function () {
                closeAllSearchDropdowns('sdAddStudentIdDropdown', false);
                requestStudentCandidates(addStudentId.value, function (candidates) {
                    renderStudentSearchDropdown('sdAddStudentIdDropdown', candidates);
                });
            });

            addStudentId.addEventListener('input', function () {
                SD_STATE.addSelectedStudent = null;
                updateAddStudentState();

                requestStudentCandidates(addStudentId.value, function (candidates) {
                    renderStudentSearchDropdown('sdAddStudentIdDropdown', candidates);
                });
            });
        }

        if (addStudentName) {
            addStudentName.addEventListener('focus', function () {
                closeAllSearchDropdowns('sdAddStudentNameDropdown', false);
                requestStudentCandidates(addStudentName.value, function (candidates) {
                    renderStudentSearchDropdown('sdAddStudentNameDropdown', candidates);
                });
            });

            addStudentName.addEventListener('input', function () {
                SD_STATE.addSelectedStudent = null;
                updateAddStudentState();

                requestStudentCandidates(addStudentName.value, function (candidates) {
                    renderStudentSearchDropdown('sdAddStudentNameDropdown', candidates);
                });
            });
        }

        if (addDegreeProgram) {
            addDegreeProgram.addEventListener('focus', function () {
                closeAllSearchDropdowns('sdAddProgramDropdown', false);
                requestProgramCandidates(addDegreeProgram.value, function (candidates) {
                    renderProgramSearchDropdown('sdAddProgramDropdown', candidates, 'add');
                });
            });

            addDegreeProgram.addEventListener('input', function () {
                SD_STATE.addSelectedProgram = null;
                updateAddStudentState();

                requestProgramCandidates(addDegreeProgram.value, function (candidates) {
                    renderProgramSearchDropdown('sdAddProgramDropdown', candidates, 'add');
                });
            });
        }

        if (editDegreeProgram) {
            editDegreeProgram.addEventListener('focus', function () {
                closeAllSearchDropdowns('sdEditProgramDropdown', false);
                requestProgramCandidates(editDegreeProgram.value, function (candidates) {
                    renderProgramSearchDropdown('sdEditProgramDropdown', candidates, 'edit');
                });
            });

            editDegreeProgram.addEventListener('input', function () {
                SD_STATE.editSelectedProgram = null;
                var courseId = byId('sdEditCourseId');
                if (courseId) {
                    courseId.value = '';
                }

                requestProgramCandidates(editDegreeProgram.value, function (candidates) {
                    renderProgramSearchDropdown('sdEditProgramDropdown', candidates, 'edit');
                });
            });
        }

        if (caseTypeSearch) {
            caseTypeSearch.addEventListener('focus', function () {
                closeAllSearchDropdowns('sdCaseTypeDropdown', false);
                renderLookupDropdown('case', caseTypeSearch.value);
            });

            caseTypeSearch.addEventListener('input', function () {
                setRecordLookupValue('case', '', '');
                renderLookupDropdown('case', caseTypeSearch.value);
            });
        }

        if (actionTypeSearch) {
            actionTypeSearch.addEventListener('focus', function () {
                closeAllSearchDropdowns('sdActionTypeDropdown', false);
                renderLookupDropdown('action', actionTypeSearch.value);
            });

            actionTypeSearch.addEventListener('input', function () {
                setRecordLookupValue('action', '', '');
                renderLookupDropdown('action', actionTypeSearch.value);
            });
        }

        if (calledByInput) {
            calledByInput.addEventListener('focus', function () {
                closeAllSearchDropdowns('sdCalledByDropdown', false);
                renderRecordTextDropdown('called_by', calledByInput.value);
            });

            calledByInput.addEventListener('input', function () {
                renderRecordTextDropdown('called_by', calledByInput.value);
            });
        }

        if (counselorInput) {
            counselorInput.addEventListener('focus', function () {
                closeAllSearchDropdowns('sdCounselorDropdown', false);
                renderRecordTextDropdown('counselor', counselorInput.value);
            });

            counselorInput.addEventListener('input', function () {
                renderRecordTextDropdown('counselor', counselorInput.value);
            });
        }
    }

    function openEditStudentModal(disciplineStudentId) {
        var student = findStudentByDisciplineId(disciplineStudentId);
        if (!student) {
            return;
        }

        SD_STATE.editStudentTargetId = disciplineStudentId;
        SD_STATE.editSelectedProgram = {
            id: student.course_id,
            label: student.degree_program || ''
        };

        var editDisciplineId = byId('sdEditDisciplineStudentId');
        var editStudentId = byId('sdEditStudentId');
        var editStudentName = byId('sdEditStudentName');
        var editDegreeProgram = byId('sdEditDegreeProgram');
        var editCourseId = byId('sdEditCourseId');
        var editBirthDate = byId('sdEditBirthDate');
        var editGender = byId('sdEditGender');
        var editStudentType = byId('sdEditStudentType');

        if (editDisciplineId) {
            editDisciplineId.value = disciplineStudentId;
        }
        if (editStudentId) {
            editStudentId.value = student.student_no || '';
        }
        if (editStudentName) {
            editStudentName.value = student.name || '';
        }
        if (editDegreeProgram) {
            editDegreeProgram.value = student.degree_program || '';
        }
        if (editCourseId) {
            editCourseId.value = student.course_id || '';
        }
        if (editBirthDate) {
            editBirthDate.value = student.birth_date_iso || '';
        }
        if (editGender) {
            setSelectValue(editGender, normalizeGender(student.gender || 'Male'));
        }
        if (editStudentType) {
            setSelectValue(editStudentType, student.student_type_code || 'OLD');
        }

        closeProgramDropdowns();
        openOverlay('sdEditStudentModal');
    }

    function openDeleteStudentModal(disciplineStudentId) {
        var student = findStudentByDisciplineId(disciplineStudentId);
        if (!student) {
            return;
        }

        SD_STATE.pendingDeleteStudentId = disciplineStudentId;
        var text = byId('sdDeleteText');
        if (text) {
            text.textContent = 'Are you sure you want to delete "' + (student.name || 'this student') + '"?';
        }

        openOverlay('sdDeleteStudentModal');
    }

    function openDeleteRecordModal(recordId) {
        var record = null;
        for (var i = 0; i < SD_STATE.records.length; i++) {
            if (String(SD_STATE.records[i].id) === String(recordId)) {
                record = SD_STATE.records[i];
                break;
            }
        }

        if (!record) {
            return;
        }

        SD_STATE.pendingDeleteRecordId = record.id;
        var text = byId('sdDeleteRecordText');
        if (text) {
            text.textContent = 'Are you sure you want to delete this conduct record (' + (record.case_type || 'Record') + ' - ' + formatInputDate(record.incident_date) + ')?';
        }

        openOverlay('sdDeleteRecordModal');
    }

    function openEditRecordModal(recordId) {
        var record = findRecordById(recordId);

        if (!record) {
            return;
        }

        showRecordDetails(record);
        renderConductRows();

        fillRecordModal(record);
        closeAllSearchDropdowns(null, true);
        openOverlay('sdRecordModal');
    }

    window.sdApplySystemConfig = function () {
        window.sdFilterStudents();
        showToast('System configuration applied.', 'success');
    };

    window.sdFilterStudents = function () {
        fetchStudents();
    };

    window.sdClearFilters = function () {
        var studentId = byId('sdStudentId');
        var fullName = byId('sdFullName');
        var studentType = byId('sdStudentTypeFilter');

        if (studentId) {
            studentId.value = '';
        }
        if (fullName) {
            fullName.value = '';
        }
        if (studentType) {
            setSelectValue(studentType, '');
        }

        fetchStudents();
    };

    window.sdOpenStudentConduct = function (disciplineStudentId) {
        var student = findStudentByDisciplineId(disciplineStudentId);
        if (!student) {
            return;
        }

        SD_STATE.selectedDisciplineStudentId = student.discipline_student_id;
        SD_STATE.activeStudent = student;

        updateStudentBanner(student);

        var listView = byId('sdListView');
        var detailView = byId('sdDetailView');

        if (listView) {
            listView.classList.add('sd-hidden');
        }
        if (detailView) {
            detailView.classList.remove('sd-hidden');
        }

        closeActionMenus();
        fetchRecords();
    };

    window.sdBackToList = function () {
        var listView = byId('sdListView');
        var detailView = byId('sdDetailView');

        if (detailView) {
            detailView.classList.add('sd-hidden');
        }
        if (listView) {
            listView.classList.remove('sd-hidden');
        }

        closeActionMenus();
        closeAllSearchDropdowns(null, true);
    };

    window.sdOpenRecordModal = function () {
        if (!SD_STATE.selectedDisciplineStudentId) {
            showToast('Select a student first.', 'warning');
            return;
        }

        resetRecordModal();
        closeAllSearchDropdowns(null, true);
        openOverlay('sdRecordModal');
    };

    window.sdCloseRecordModal = function () {
        closeOverlay('sdRecordModal');
        closeRecordSearchDropdowns();
    };

    window.sdSaveRecord = function () {
        if (!SD_STATE.selectedDisciplineStudentId) {
            showToast('Select a student first.', 'warning');
            return;
        }

        var incidentDate = normalizeText(byId('sdIncidentDate') ? byId('sdIncidentDate').value : '');
        var caseTypeId = normalizeText(byId('sdCaseType') ? byId('sdCaseType').value : '');
        var actionDate = normalizeText(byId('sdActionDate') ? byId('sdActionDate').value : '');
        var actionTypeId = normalizeText(byId('sdActionType') ? byId('sdActionType').value : '');

        if (!incidentDate) {
            showToast('Incident Date is required.', 'warning');
            return;
        }
        if (!caseTypeId) {
            showToast('Case Type is required.', 'warning');
            return;
        }

        var calledBy = normalizeText(byId('sdCalledBy') ? byId('sdCalledBy').value : '');
        var description = normalizeText(byId('sdDescription') ? byId('sdDescription').value : '');
        var counselor = normalizeText(byId('sdCounselor') ? byId('sdCounselor').value : '');
        var remarks = normalizeText(byId('sdRemarks') ? byId('sdRemarks').value : '');

        if (hasSqlInjectionPattern(calledBy)) {
            showToast('Called By contains disallowed SQL-like input.', 'warning');
            return;
        }
        if (hasSqlInjectionPattern(description)) {
            showToast('Description contains disallowed SQL-like input.', 'warning');
            return;
        }
        if (hasSqlInjectionPattern(counselor)) {
            showToast('Counselor contains disallowed SQL-like input.', 'warning');
            return;
        }
        if (hasSqlInjectionPattern(remarks)) {
            showToast('Remarks contains disallowed SQL-like input.', 'warning');
            return;
        }

        var payload = {
            incident_date: incidentDate,
            case_type_id: caseTypeId,
            walk_in: byId('sdWalkIn') ? byId('sdWalkIn').checked : false,
            called_by: calledBy,
            description: description,
            action_date: actionDate || null,
            action_type_id: actionTypeId || null,
            counselor: counselor,
            remarks: remarks,
            is_completed: byId('sdCompleted') ? byId('sdCompleted').checked : false
        };

        var config = getApiConfig();
        if (!config) {
            return;
        }

        var url = '';
        var method = 'POST';

        if (SD_STATE.editRecordId) {
            url = buildUrl(config.recordUpdateUrlTemplate, '__RECORD__', SD_STATE.editRecordId);
            method = 'PUT';
        } else {
            url = buildUrl(config.recordStoreUrlTemplate, '__DISCIPLINE_STUDENT__', SD_STATE.selectedDisciplineStudentId);
        }

        requestJson(url, method, payload).then(function () {
            closeOverlay('sdRecordModal');
            closeRecordSearchDropdowns();
            resetRecordModal();
            fetchRecords();
            showToast('Record saved successfully.', 'success');
        }).catch(function (error) {
            showToast(error.message || 'Unable to save record.', 'error');
        });
    };

    window.sdPrintRecord = function () {
        if (!SD_STATE.selectedDisciplineStudentId) {
            showToast('Select a student first.', 'warning');
            return;
        }

        renderPrintRecordsSheet();
        if (!SD_STATE.records.length) {
            showToast('No records available to print.', 'warning');
            return;
        }

        window.print();
    };

    window.sdOpenEditModal = function (disciplineStudentId) {
        openEditStudentModal(disciplineStudentId);
    };

    window.sdCloseEditModal = function () {
        closeOverlay('sdEditStudentModal');
        closeProgramDropdowns();
        closeRecordSearchDropdowns();
        SD_STATE.editStudentTargetId = null;
    };

    window.sdSaveStudentEdit = function () {
        var disciplineStudentId = SD_STATE.editStudentTargetId;
        if (!disciplineStudentId) {
            return;
        }

        var studentTypeCode = normalizeText(byId('sdEditStudentType') ? byId('sdEditStudentType').value : '');
        var courseId = normalizeText(byId('sdEditCourseId') ? byId('sdEditCourseId').value : '');
        var gender = normalizeText(byId('sdEditGender') ? byId('sdEditGender').value : '');
        var birthDate = normalizeText(byId('sdEditBirthDate') ? byId('sdEditBirthDate').value : '');

        if (!studentTypeCode) {
            showToast('Student Type is required.', 'warning');
            return;
        }
        if (!courseId) {
            showToast('Degree Program is required.', 'warning');
            return;
        }
        if (!gender) {
            showToast('Gender is required.', 'warning');
            return;
        }

        var config = getApiConfig();
        if (!config) {
            return;
        }

        var url = buildUrl(config.studentUpdateUrlTemplate, '__DISCIPLINE_STUDENT__', disciplineStudentId);
        requestJson(url, 'PUT', {
            student_type_code: studentTypeCode,
            course_id: courseId,
            gender: normalizeGender(gender),
            birth_date: birthDate || null
        }).then(function () {
            closeOverlay('sdEditStudentModal');
            closeProgramDropdowns();
            fetchStudents().then(function () {
                if (SD_STATE.selectedDisciplineStudentId) {
                    var updated = findStudentByDisciplineId(SD_STATE.selectedDisciplineStudentId);
                    if (updated) {
                        SD_STATE.activeStudent = updated;
                        updateStudentBanner(updated);
                    }
                }
            });
            showToast('Student updated successfully.', 'success');
        }).catch(function (error) {
            showToast(error.message || 'Unable to update student.', 'error');
        });
    };

    window.sdOpenAddStudentModal = function () {
        resetAddStudentModalState();
        openOverlay('sdAddStudentModal');
    };

    window.sdCloseAddStudentModal = function () {
        closeOverlay('sdAddStudentModal');
        closeAllSearchDropdowns(null, true);
    };

    window.sdSaveNewStudent = function () {
        var selectedStudent = SD_STATE.addSelectedStudent;
        var selectedProgram = SD_STATE.addSelectedProgram;
        var studentTypeCode = 'OLD';
        var gender = normalizeText(byId('sdAddGender') ? byId('sdAddGender').value : '');
        var birthDate = normalizeText(byId('sdAddBirthDate') ? byId('sdAddBirthDate').value : '');

        if (!selectedStudent || !selectedStudent.id) {
            showToast('Please select a student from the search list.', 'warning');
            return;
        }

        if (!selectedProgram || !selectedProgram.id) {
            showToast('Please select a degree program from the search list.', 'warning');
            return;
        }

        if (!studentTypeCode) {
            showToast('Student Type is required.', 'warning');
            return;
        }
        if (!gender) {
            showToast('Gender is required.', 'warning');
            return;
        }

        var config = getApiConfig();
        if (!config || !config.studentStoreUrl) {
            return;
        }

        requestJson(config.studentStoreUrl, 'POST', {
            student_id: selectedStudent.id,
            course_id: selectedProgram.id,
            student_type_code: studentTypeCode,
            gender: normalizeGender(gender),
            birth_date: birthDate || null
        }).then(function () {
            closeOverlay('sdAddStudentModal');
            fetchStudents();
            showToast('Student added successfully.', 'success');
        }).catch(function (error) {
            showToast(error.message || 'Unable to add student.', 'error');
        });
    };

    window.sdOpenDeleteModal = function (disciplineStudentId) {
        openDeleteStudentModal(disciplineStudentId);
    };

    window.sdCloseDeleteModal = function () {
        closeOverlay('sdDeleteStudentModal');
        SD_STATE.pendingDeleteStudentId = null;
        closeRecordSearchDropdowns();
    };

    window.sdConfirmDeleteStudent = function () {
        if (!SD_STATE.pendingDeleteStudentId) {
            return;
        }

        var config = getApiConfig();
        if (!config) {
            return;
        }

        var targetId = SD_STATE.pendingDeleteStudentId;
        var url = buildUrl(config.studentDestroyUrlTemplate, '__DISCIPLINE_STUDENT__', targetId);

        requestJson(url, 'DELETE').then(function () {
            if (String(SD_STATE.selectedDisciplineStudentId) === String(targetId)) {
                SD_STATE.selectedDisciplineStudentId = null;
                SD_STATE.activeStudent = null;
                window.sdBackToList();
            }

            SD_STATE.pendingDeleteStudentId = null;
            closeOverlay('sdDeleteStudentModal');
            fetchStudents();
            showToast('Student deleted successfully.', 'success');
        }).catch(function (error) {
            showToast(error.message || 'Unable to delete student.', 'error');
        });
    };

    window.sdOpenDeleteRecordModal = function (recordId) {
        openDeleteRecordModal(recordId);
    };

    function handlePageAction(action) {
        if (action === 'filter-students') {
            window.sdFilterStudents();
            return;
        }

        if (action === 'clear-filters') {
            window.sdClearFilters();
            return;
        }

        if (action === 'apply-system-config') {
            window.sdApplySystemConfig();
            return;
        }

        if (action === 'open-add-student-modal') {
            window.sdOpenAddStudentModal();
            return;
        }

        if (action === 'back-to-list') {
            window.sdBackToList();
            return;
        }

        if (action === 'open-record-modal') {
            window.sdOpenRecordModal();
            return;
        }

        if (action === 'print-record') {
            window.sdPrintRecord();
            return;
        }

        if (action === 'close-record-modal') {
            window.sdCloseRecordModal();
            return;
        }

        if (action === 'save-record') {
            window.sdSaveRecord();
            return;
        }

        if (action === 'close-delete-record-modal') {
            window.sdCloseDeleteRecordModal();
            return;
        }

        if (action === 'confirm-delete-record') {
            window.sdConfirmDeleteRecord();
            return;
        }

        if (action === 'close-edit-student-modal') {
            window.sdCloseEditModal();
            return;
        }

        if (action === 'save-student-edit') {
            window.sdSaveStudentEdit();
            return;
        }

        if (action === 'close-add-student-modal') {
            window.sdCloseAddStudentModal();
            return;
        }

        if (action === 'save-new-student') {
            window.sdSaveNewStudent();
            return;
        }

        if (action === 'close-delete-student-modal') {
            window.sdCloseDeleteModal();
            return;
        }

        if (action === 'confirm-delete-student') {
            window.sdConfirmDeleteStudent();
        }
    }

    window.sdCloseDeleteRecordModal = function () {
        closeOverlay('sdDeleteRecordModal');
        SD_STATE.pendingDeleteRecordId = null;
        closeRecordSearchDropdowns();
    };

    window.sdConfirmDeleteRecord = function () {
        if (!SD_STATE.pendingDeleteRecordId) {
            return;
        }

        var config = getApiConfig();
        if (!config) {
            return;
        }

        var recordId = SD_STATE.pendingDeleteRecordId;
        var url = buildUrl(config.recordDestroyUrlTemplate, '__RECORD__', recordId);

        requestJson(url, 'DELETE').then(function () {
            SD_STATE.pendingDeleteRecordId = null;
            closeOverlay('sdDeleteRecordModal');
            closeRecordSearchDropdowns();
            fetchRecords();
            showToast('Record deleted successfully.', 'success');
        }).catch(function (error) {
            showToast(error.message || 'Unable to delete record.', 'error');
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (!getPage()) {
            return;
        }

        var config = getApiConfig();
        SD_STATE.semesterMap = config ? parseJsonObject(config.semesterMap) : {};

        syncTermOptionsForSchoolYear(byId('sdTerm') ? byId('sdTerm').value : '');

        var schoolYearSelect = byId('sdSchoolYear');
        if (schoolYearSelect) {
            schoolYearSelect.addEventListener('change', function () {
                syncTermOptionsForSchoolYear('');
            });
        }

        initializeRecordLookupOptions();
        bindSearchInputs();
        fetchStudents();

        ['sdStudentId', 'sdFullName'].forEach(function (id) {
            var input = byId(id);
            if (!input) {
                return;
            }

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    window.sdFilterStudents();
                }
            });
        });

        document.addEventListener('click', function (event) {
            var namedAction = event.target.closest('[data-sd-click]');
            if (namedAction) {
                event.preventDefault();
                handlePageAction(namedAction.getAttribute('data-sd-click') || '');
                return;
            }

            var openConductBtn = event.target.closest('[data-sd-open-conduct]');
            if (openConductBtn) {
                event.preventDefault();
                window.sdOpenStudentConduct(openConductBtn.getAttribute('data-sd-open-conduct') || '');
                return;
            }

            var menuToggle = event.target.closest('[data-sd-menu-toggle]');
            if (menuToggle) {
                event.stopPropagation();
                toggleActionMenu(menuToggle.getAttribute('data-sd-menu-toggle'), menuToggle);
                return;
            }

            var conductToggle = event.target.closest('[data-sd-conduct-menu-toggle]');
            if (conductToggle) {
                event.stopPropagation();
                toggleActionMenu(conductToggle.getAttribute('data-sd-conduct-menu-toggle'), conductToggle);
                return;
            }

            var actionBtn = event.target.closest('[data-sd-open-action]');
            if (actionBtn) {
                closeActionMenus();
                var action = actionBtn.getAttribute('data-sd-open-action');
                var disciplineId = actionBtn.getAttribute('data-sd-discipline-student-id');
                if (action === 'edit') {
                    window.sdOpenEditModal(disciplineId);
                } else if (action === 'delete') {
                    window.sdOpenDeleteModal(disciplineId);
                }
                return;
            }

            var conductActionBtn = event.target.closest('[data-sd-conduct-action]');
            if (conductActionBtn) {
                closeActionMenus();
                var conductAction = conductActionBtn.getAttribute('data-sd-conduct-action');
                var recordId = conductActionBtn.getAttribute('data-sd-record-id');
                if (conductAction === 'edit') {
                    openEditRecordModal(recordId);
                } else if (conductAction === 'delete') {
                    window.sdOpenDeleteRecordModal(recordId);
                }
                return;
            }

            var recordRow = event.target.closest('[data-sd-record-row]');
            if (recordRow && !event.target.closest('[data-sd-conduct-menu-toggle], .apst-action-btn, .apst-dropdown')) {
                var selectedRecordId = recordRow.getAttribute('data-sd-record-row') || '';
                var selectedRecord = findRecordById(selectedRecordId);
                if (selectedRecord) {
                    showRecordDetails(selectedRecord);
                    renderConductRows();
                }
                return;
            }

            var studentOption = event.target.closest('[data-sd-student-option]');
            if (studentOption) {
                applyAddStudentCandidate({
                    id: studentOption.getAttribute('data-sd-student-id') || '',
                    student_no: studentOption.getAttribute('data-sd-student-no') || '',
                    name: studentOption.getAttribute('data-sd-student-name') || '',
                    course_id: studentOption.getAttribute('data-sd-student-course-id') || '',
                    course_label: studentOption.getAttribute('data-sd-student-course-label') || '',
                    birth_date: studentOption.getAttribute('data-sd-student-birth-date') || '-',
                    birth_date_iso: studentOption.getAttribute('data-sd-student-birth-date-iso') || '',
                    gender: studentOption.getAttribute('data-sd-student-gender') || '-'
                });
                closeStudentSearchDropdowns();
                return;
            }

            var programOption = event.target.closest('[data-sd-program-option]');
            if (programOption) {
                applyProgramCandidate({
                    id: programOption.getAttribute('data-sd-program-id') || '',
                    label: programOption.getAttribute('data-sd-program-label') || ''
                }, programOption.getAttribute('data-sd-program-target') || 'add');
                closeProgramDropdowns();
                return;
            }

            var lookupOption = event.target.closest('[data-sd-record-lookup-option]');
            if (lookupOption) {
                setRecordLookupValue(
                    lookupOption.getAttribute('data-sd-record-lookup-type') || 'case',
                    lookupOption.getAttribute('data-sd-record-lookup-value') || '',
                    lookupOption.getAttribute('data-sd-record-lookup-label') || ''
                );
                closeRecordSearchDropdowns();
                return;
            }

            var textOption = event.target.closest('[data-sd-record-text-option]');
            if (textOption) {
                var field = textOption.getAttribute('data-sd-record-text-field') || 'called_by';
                var value = textOption.getAttribute('data-sd-record-text-value') || '';
                var targetInput = byId(field === 'counselor' ? 'sdCounselor' : 'sdCalledBy');
                if (targetInput) {
                    targetInput.value = value;
                }
                closeRecordSearchDropdowns();
                return;
            }

            if (!event.target.closest('.apst-dropdown')) {
                closeActionMenus();
            }

            if (!event.target.closest('.smrg-search-wrap')) {
                closeAllSearchDropdowns(null, true);
            }
        });

        window.addEventListener('scroll', closeActionMenus, true);

        ['sdRecordModal', 'sdEditStudentModal', 'sdAddStudentModal', 'sdDeleteStudentModal', 'sdDeleteRecordModal'].forEach(function (id) {
            var overlay = byId(id);
            if (!overlay) {
                return;
            }

            overlay.addEventListener('click', function (event) {
                if (event.target !== overlay) {
                    return;
                }

                if (id === 'sdRecordModal') {
                    window.sdCloseRecordModal();
                } else if (id === 'sdEditStudentModal') {
                    window.sdCloseEditModal();
                } else if (id === 'sdAddStudentModal') {
                    window.sdCloseAddStudentModal();
                } else if (id === 'sdDeleteStudentModal') {
                    window.sdCloseDeleteModal();
                } else if (id === 'sdDeleteRecordModal') {
                    window.sdCloseDeleteRecordModal();
                }
            });
        });
    });
})();
