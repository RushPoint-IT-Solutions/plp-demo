(function () {
    'use strict';

    function byId(id) {
        return document.getElementById(id);
    }

    function decodeHtmlEntities(value) {
        if (typeof value !== 'string' || value.indexOf('&') === -1) {
            return value;
        }

        var parser = document.createElement('textarea');
        parser.innerHTML = value;
        return parser.value;
    }

    function parseDataJson(element, key, fallback) {
        if (!element) {
            return fallback;
        }

        var attrName = 'data-' + key.replace(/([A-Z])/g, '-$1').toLowerCase();
        var raw = element.getAttribute(attrName);
        if (!raw) {
            return fallback;
        }

        var normalized = decodeHtmlEntities(raw);

        try {
            return JSON.parse(normalized);
        } catch (error) {
            return fallback;
        }
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            };

            return map[ch] || ch;
        });
    }

    function formatDate(value) {
        if (!value) {
            return '';
        }

        var date = new Date(value + 'T00:00:00');
        if (isNaN(date.getTime())) {
            return '';
        }

        return date.toLocaleDateString('en-US', {
            month: '2-digit',
            day: '2-digit',
            year: '2-digit'
        });
    }

    function normalizeDateInputValue(value) {
        if (!value) {
            return '';
        }

        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return value;
        }

        var parsed = new Date(value);
        if (isNaN(parsed.getTime())) {
            return '';
        }

        var month = String(parsed.getMonth() + 1).padStart(2, '0');
        var day = String(parsed.getDate()).padStart(2, '0');
        return parsed.getFullYear() + '-' + month + '-' + day;
    }

    function setDateInputValue(inputId, value) {
        var input = byId(inputId);
        if (!input) {
            return;
        }

        var normalized = normalizeDateInputValue(value);

        if (input._flatpickr) {
            if (normalized) {
                input._flatpickr.setDate(normalized, true, 'Y-m-d');
            } else {
                input._flatpickr.clear();
            }
            return;
        }

        input.value = normalized;
    }

    function initFlatpickrDateInputs() {
        if (typeof window.flatpickr !== 'function') {
            return;
        }

        [
            'cfgCutoffDate',
            'cfgSectionCutoffDate',
            'cfgCutoffRegDate',
            'cfgCutoffRegCutoffDate',
            'cfgCutoffConfigDate',
            'cfgGPDateFrom'
        ].forEach(function (inputId) {
            var input = byId(inputId);
            if (!input) {
                return;
            }

            if (input._flatpickr) {
                input._flatpickr.destroy();
            }

            window.flatpickr(input, {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
                onReady: function (_selectedDates, _dateStr, instance) {
                    if (!instance || !instance.calendarContainer) {
                        return;
                    }

                    instance.calendarContainer.classList.add('an-flatpickr-calendar');
                    instance.calendarContainer.classList.add('app-form-flatpickr-theme');
                }
            });

            setDateInputValue(inputId, input.value);
        });
    }

    function routeFromTemplate(template, id) {
        return String(template || '').replace('__ID__', String(id));
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? (meta.getAttribute('content') || '') : '';
    }

    function resolveErrorMessage(payload, fallback) {
        if (!payload) {
            return fallback;
        }

        if (payload.errors) {
            var fields = Object.keys(payload.errors);
            if (fields.length && payload.errors[fields[0]] && payload.errors[fields[0]][0]) {
                return payload.errors[fields[0]][0];
            }
        }

        if (payload.message) {
            return payload.message;
        }

        return fallback;
    }

    function showMessage(message, type) {
        var text = String(message || '').trim();
        if (!text) {
            return;
        }

        if (typeof window.showRegistrarToast === 'function') {
            try {
                window.showRegistrarToast(text, type || 'success');
                return;
            } catch (error) {
                console.error('Registrar toast render failed:', error);
            }
        }

        window.alert(text);
    }

    async function requestJson(url, method, payload) {
        var response = await fetch(url, {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: payload ? JSON.stringify(payload) : null
        });

        var data = {};
        try {
            data = await response.json();
        } catch (error) {
            data = {};
        }

        if (!response.ok || data.ok === false) {
            throw new Error(resolveErrorMessage(data, 'Unable to process request.'));
        }

        return data;
    }

    async function requestForm(url, method, formData) {
        var transportMethod = method;
        var body = formData;

        if (method !== 'POST') {
            transportMethod = 'POST';
            body.append('_method', method);
        }

        var response = await fetch(url, {
            method: transportMethod,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: body
        });

        var data = {};
        try {
            data = await response.json();
        } catch (error) {
            data = {};
        }

        if (!response.ok || data.ok === false) {
            throw new Error(resolveErrorMessage(data, 'Unable to process request.'));
        }

        return data;
    }

    function refreshListboxes(target) {
        if (!window.registrarListboxSelect || typeof window.registrarListboxSelect !== 'object') {
            return;
        }

        if (target && typeof window.registrarListboxSelect.refresh === 'function') {
            window.registrarListboxSelect.refresh(target);
            return;
        }

        if (typeof window.registrarListboxSelect.refreshAll === 'function') {
            window.registrarListboxSelect.refreshAll();
            return;
        }

        if (typeof window.registrarListboxSelect.refresh === 'function') {
            window.registrarListboxSelect.refresh();
        }
    }

    function syncSelectUI(id) {
        var node = byId(id);
        if (!node) {
            return;
        }

        node.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setInlineMessage(id, text, isError) {
        var node = byId(id);
        if (!node) {
            return;
        }

        node.textContent = text || '';
        node.classList.toggle('is-error', !!isError);
    }

    var bootstrap = byId('cfgBootstrap');
    if (!bootstrap) {
        return;
    }

    var routes = parseDataJson(bootstrap, 'routes', {});

    var state = {
        schoolSem: parseDataJson(bootstrap, 'schoolSem', []),
        gradePosting: parseDataJson(bootstrap, 'gradePosting', []),
        signatures: parseDataJson(bootstrap, 'signatures', []),
        cutoffDate: parseDataJson(bootstrap, 'cutoffDate', []),
        sectionCutoff: parseDataJson(bootstrap, 'sectionCutoff', []),
        cutoffConfig: parseDataJson(bootstrap, 'cutoffConfig', []),
        curriculumDisplay: parseDataJson(bootstrap, 'curriculumDisplay', []),
        latestIncRun: parseDataJson(bootstrap, 'latestIncRun', null),
        pager: {
            schoolSem: { page: 1, size: 5 },
            gradePosting: { page: 1, size: 5 },
            signatures: { page: 1, size: 5 },
            cutoffDate: { page: 1, size: 5 },
            sectionCutoff: { page: 1, size: 5 },
            cutoffConfig: { page: 1, size: 5 },
            curriculumDisplay: { page: 1, size: 5 }
        },
        deleteTarget: null,
        deleteBusy: false
    };

    var requestLocks = {
        schoolSem: false
    };

    var pagerMeta = {
        schoolSem: { id: 'cfgSchoolSemPager' },
        gradePosting: { id: 'cfgGradePostingPager' },
        signatures: { id: 'cfgSignaturePager' },
        cutoffDate: { id: 'cfgCutoffDatePager' },
        sectionCutoff: { id: 'cfgSectionCutoffPager' },
        cutoffConfig: { id: 'cfgCutoffConfigPager' },
        curriculumDisplay: { id: 'cfgCurriculumDisplayPager' }
    };

    function listFor(group) {
        return state[group] || [];
    }

    function upsertRow(list, row) {
        var index = list.findIndex(function (item) {
            return String(item.id) === String(row.id);
        });

        if (index >= 0) {
            list[index] = row;
            return;
        }

        list.unshift(row);
    }

    function removeRow(list, id) {
        var index = list.findIndex(function (item) {
            return String(item.id) === String(id);
        });

        if (index >= 0) {
            list.splice(index, 1);
        }
    }

    function maxPage(group) {
        var data = listFor(group);
        var size = state.pager[group].size;
        return Math.max(1, Math.ceil(data.length / size));
    }

    function pagedSlice(group) {
        var data = listFor(group);
        var page = state.pager[group].page;
        var size = state.pager[group].size;
        var start = (page - 1) * size;

        return {
            start: start,
            rows: data.slice(start, start + size)
        };
    }

    function setPage(group, page) {
        var max = maxPage(group);
        state.pager[group].page = Math.max(1, Math.min(max, page));
        renderGroup(group);
    }

    function renderPager(group) {
        var mount = byId((pagerMeta[group] || {}).id || '');
        if (!mount) {
            return;
        }

        var list = listFor(group);
        var size = state.pager[group].size;
        if (list.length <= size) {
            mount.innerHTML = '';
            return;
        }

        var current = state.pager[group].page;
        var max = maxPage(group);
        var start = Math.max(1, current - 2);
        var end = Math.min(max, current + 2);

        if (current <= 3) {
            end = Math.min(max, 5);
        } else if (current >= max - 2) {
            start = Math.max(1, max - 4);
        }

        var html = '' +
            '<nav class="cfg-page-nav-wrap" aria-label="Configuration pagination">' +
                '<div class="cfg-page-list" role="group" aria-label="Page controls">' +
                    '<button type="button" class="btn cfg-page-btn" data-cfg-action="set-page" data-cfg-group="' + group + '" data-cfg-page="' + (current - 1) + '" ' + (current <= 1 ? 'disabled' : '') + ' aria-label="Previous page">&lt;</button>';

        for (var p = start; p <= end; p += 1) {
            html += '<button type="button" class="btn cfg-page-num ' + (p === current ? 'active' : '') + '" data-cfg-action="set-page" data-cfg-group="' + group + '" data-cfg-page="' + p + '" ' + (p === current ? 'aria-current="page"' : '') + '>' + p + '</button>';
        }

        html += '' +
                    '<button type="button" class="btn cfg-page-btn" data-cfg-action="set-page" data-cfg-group="' + group + '" data-cfg-page="' + (current + 1) + '" ' + (current >= max ? 'disabled' : '') + ' aria-label="Next page">&gt;</button>' +
                '</div>' +
            '</nav>';

        mount.innerHTML = html;
    }

    function actionMenuHtml(group, index) {
        var menuId = 'cfgMenu-' + group + '-' + index;

        return '' +
            '<div class="apst-action-btn" data-cfg-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" data-cfg-action="edit-row" data-cfg-group="' + group + '" data-cfg-index="' + index + '">Edit</button>' +
                '<button type="button" class="apst-del-btn" data-cfg-action="delete-row" data-cfg-group="' + group + '" data-cfg-index="' + index + '">Delete</button>' +
            '</div>';
    }

    function renderSchoolSem() {
        var body = byId('cfgSchoolSemBody');
        if (!body) {
            return;
        }

        var paged = pagedSlice('schoolSem');
        var rows = paged.rows.map(function (row, idx) {
            var index = paged.start + idx;

            return '' +
                '<tr>' +
                    '<td>' + escapeHtml(row.sy) + '</td>' +
                    '<td>' + escapeHtml(row.semester) + '</td>' +
                    '<td class="cfg-col-action">' + actionMenuHtml('schoolSem', index) + '</td>' +
                '</tr>';
        }).join('');

        body.innerHTML = rows || '<tr><td colspan="3" class="sc-empty-row">No records found.</td></tr>';
        renderPager('schoolSem');
    }

    function renderGradePosting() {
        var body = byId('cfgGradePostingBody');
        if (!body) {
            return;
        }

        var paged = pagedSlice('gradePosting');
        var rows = paged.rows.map(function (row, idx) {
            var index = paged.start + idx;

            return '' +
                '<tr>' +
                    '<td>' + escapeHtml(row.sy) + '</td>' +
                    '<td>' + escapeHtml(row.semester) + '</td>' +
                    '<td>' + escapeHtml(row.period) + '</td>' +
                    '<td>' + escapeHtml(formatDate(row.dateFrom)) + '</td>' +
                    '<td class="cfg-col-action">' + actionMenuHtml('gradePosting', index) + '</td>' +
                '</tr>';
        }).join('');

        body.innerHTML = rows || '<tr><td colspan="5" class="sc-empty-row">No records found.</td></tr>';
        renderPager('gradePosting');
    }

    function renderSignatures() {
        var body = byId('cfgSignatureBody');
        if (!body) {
            return;
        }

        var paged = pagedSlice('signatures');
        var rows = paged.rows.map(function (row, idx) {
            var index = paged.start + idx;
            var signatureCell = row.signatureUrl
                ? '<span class="cfg-signature-actions">' +
                    '<a href="' + escapeHtml(row.signatureUrl) + '" target="_blank" rel="noopener">View</a>' +
                    '<button type="button" class="apst-del-btn cfg-link-btn" data-cfg-action="delete-signature-file" data-cfg-index="' + index + '">Delete</button>' +
                '</span>'
                : '<span class="cfg-muted">No file</span>';

            var programsHtml = '';
            if (row.programs && Array.isArray(row.programs) && row.programs.length > 0) {
                programsHtml = row.programs.map(function(p) {
                    if (p === 'ALL') return '<span class="sc-general-tag">General</span>';
                    return '<span class="sc-prog-tag">' + escapeHtml(p) + '</span>';
                }).join('');
            } else {
                programsHtml = '<span class="sc-general-tag">General</span>';
            }

            return '' +
                '<tr>' +
                    '<td>' + escapeHtml(row.designation) + '</td>' +
                    '<td>' + escapeHtml(row.name) + '</td>' +
                    '<td>' + programsHtml + '</td>' +
                    '<td>' + signatureCell + '</td>' +
                    '<td class="cfg-col-action">' + actionMenuHtml('signatures', index) + '</td>' +
                '</tr>';
        }).join('');

        body.innerHTML = rows || '<tr><td colspan="5" class="sc-empty-row">No records found.</td></tr>';
        renderPager('signatures');
    }

    function renderCutoffDate() {
        var body = byId('cfgCutoffDateBody');
        if (!body) {
            return;
        }

        var paged = pagedSlice('cutoffDate');
        var rows = paged.rows.map(function (row, idx) {
            var index = paged.start + idx;

            return '' +
                '<tr>' +
                    '<td>' + escapeHtml(row.type) + '</td>' +
                    '<td>' + escapeHtml(row.sy) + '</td>' +
                    '<td>' + escapeHtml(row.semester) + '</td>' +
                    '<td>' + escapeHtml(formatDate(row.cutoffDate)) + '</td>' +
                    '<td class="cfg-col-action">' + actionMenuHtml('cutoffDate', index) + '</td>' +
                '</tr>';
        }).join('');

        body.innerHTML = rows || '<tr><td colspan="5" class="sc-empty-row">No records found.</td></tr>';
        renderPager('cutoffDate');
    }

    function renderSectionCutoff() {
        var body = byId('cfgSectionCutoffBody');
        if (!body) {
            return;
        }

        var paged = pagedSlice('sectionCutoff');
        var rows = paged.rows.map(function (row, idx) {
            var index = paged.start + idx;

            return '' +
                '<tr>' +
                    '<td>' + escapeHtml(row.sy) + '</td>' +
                    '<td>' + escapeHtml(row.semester) + '</td>' +
                    '<td>' + escapeHtml(formatDate(row.cutoffDate)) + '</td>' +
                    '<td class="cfg-col-action">' + actionMenuHtml('sectionCutoff', index) + '</td>' +
                '</tr>';
        }).join('');

        body.innerHTML = rows || '<tr><td colspan="4" class="sc-empty-row">No records found.</td></tr>';
        renderPager('sectionCutoff');
    }

    function renderCutoffConfig() {
        var body = byId('cfgCutoffConfigBody');
        if (!body) {
            return;
        }

        var paged = pagedSlice('cutoffConfig');
        var rows = paged.rows.map(function (row, idx) {
            var index = paged.start + idx;

            return '' +
                '<tr>' +
                    '<td>' + escapeHtml(row.sy) + '</td>' +
                    '<td>' + escapeHtml(row.semester) + '</td>' +
                    '<td>' + escapeHtml(formatDate(row.cutoffDate)) + '</td>' +
                    '<td class="cfg-col-action">' + actionMenuHtml('cutoffConfig', index) + '</td>' +
                '</tr>';
        }).join('');

        body.innerHTML = rows || '<tr><td colspan="4" class="sc-empty-row">No records found.</td></tr>';
        renderPager('cutoffConfig');
    }

    function renderCurriculumDisplay() {
        var body = byId('cfgCurriculumDisplayBody');
        if (!body) {
            return;
        }

        var paged = pagedSlice('curriculumDisplay');
        var rows = paged.rows.map(function (row, idx) {
            var index = paged.start + idx;

            return '' +
                '<tr>' +
                    '<td>' + escapeHtml(row.sy) + '</td>' +
                    '<td>' + escapeHtml(row.semester) + '</td>' +
                    '<td>' + escapeHtml(row.status) + '</td>' +
                    '<td class="cfg-col-action">' + actionMenuHtml('curriculumDisplay', index) + '</td>' +
                '</tr>';
        }).join('');

        body.innerHTML = rows || '<tr><td colspan="4" class="sc-empty-row">No records found.</td></tr>';
        renderPager('curriculumDisplay');
    }

    function renderGroup(group) {
        if (group === 'schoolSem') {
            renderSchoolSem();
            return;
        }

        if (group === 'gradePosting') {
            renderGradePosting();
            return;
        }

        if (group === 'signatures') {
            renderSignatures();
            return;
        }

        if (group === 'cutoffDate') {
            renderCutoffDate();
            return;
        }

        if (group === 'sectionCutoff') {
            renderSectionCutoff();
            return;
        }

        if (group === 'cutoffConfig') {
            renderCutoffConfig();
            return;
        }

        if (group === 'curriculumDisplay') {
            renderCurriculumDisplay();
        }
    }

    function renderAll() {
        renderSchoolSem();
        renderGradePosting();
        renderSignatures();
        renderCutoffDate();
        renderSectionCutoff();
        renderCutoffConfig();
        renderCurriculumDisplay();
    }

    function closeActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function toggleActionMenu(menuId, trigger) {
        var menu = byId(menuId);
        if (!menu || !trigger) {
            return;
        }

        var wasOpen = menu.classList.contains('open');
        closeActionMenus();

        if (wasOpen) {
            return;
        }

        var rect = trigger.getBoundingClientRect();
        var width = 130;
        var height = 96;
        var left = rect.right + 8;
        var top = rect.top;

        if (left + width > window.innerWidth - 8) {
            left = Math.max(8, window.innerWidth - width - 8);
        }

        if (window.innerHeight - rect.bottom < height + 8) {
            menu.classList.add('drop-up');
            top = Math.max(8, rect.bottom - height);
        }

        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        menu.style.right = 'auto';
        menu.style.bottom = 'auto';
        menu.classList.add('open');
    }

    function openModal(id) {
        var modal = byId(id);
        if (!modal) {
            return;
        }

        closeActionMenus();
        modal.classList.remove('is-hidden');
        refreshListboxes(modal);
    }

    function closeModal(id) {
        var modal = byId(id);
        if (!modal) {
            return;
        }

        modal.classList.add('is-hidden');
    }

    function resetSchoolSemModal() {
        byId('cfgSSEditId').value = '';
        byId('cfgSSTitle').textContent = 'ADD SCHOOL YEAR AND SEMESTER';
        byId('cfgSSSaveBtn').textContent = 'Save';
        byId('cfgSSSaveBtn').disabled = false;
        byId('cfgSSYear').value = '';
        byId('cfgSSSemester').value = '';
        syncSelectUI('cfgSSSemester');
    }

    function resetGradePostingModal() {
        byId('cfgGPEditId').value = '';
        byId('cfgGPTitle').textContent = 'ADD GRADE POSTING';
        byId('cfgGPSaveBtn').textContent = 'Save';
        byId('cfgGPYear').value = '';
        byId('cfgGPSemester').value = '';
        byId('cfgGPPeriod').value = '';
        setDateInputValue('cfgGPDateFrom', '');
        syncSelectUI('cfgGPSemester');
        syncSelectUI('cfgGPPeriod');
    }

    function setSignatureSaveButtonState(isSaving) {
        var saveButton = byId('cfgSignatureSaveBtn');
        if (!saveButton) {
            return;
        }

        var editNode = byId('cfgSignatureEditId');
        var hasEditId = !!(editNode && String(editNode.value || '').trim() !== '');

        saveButton.disabled = !!isSaving;
        saveButton.textContent = isSaving ? 'Saving...' : (hasEditId ? 'Update' : 'Save');
    }

    function resetSignatureForm() {
        var editNode = byId('cfgSignatureEditId');
        var designationNode = byId('cfgSignatureDesignation');
        var nameNode = byId('cfgSignatureName');
        var fileNode = byId('cfgSignatureFile');
        var titleNode = byId('cfgSignatureTitle');

        if (editNode) {
            editNode.value = '';
        }
        if (designationNode) {
            designationNode.value = '';
        }
        if (nameNode) {
            nameNode.value = '';
        }
        if (fileNode) {
            fileNode.value = '';
        }
        if (titleNode) {
            titleNode.textContent = 'ADD SIGNATURE';
        }

        setInlineMessage('cfgSignatureModalMessage', '', false);
        setSignatureSaveButtonState(false);
        syncSelectUI('cfgSignatureDesignation');
    }

    function resetCutoffDateForm() {
        byId('cfgCutoffDateEditId').value = '';
        byId('cfgCutoffType').value = '';
        setDateInputValue('cfgCutoffDate', '');
        byId('cfgCutoffSaveBtn').textContent = 'Save';
        syncSelectUI('cfgCutoffType');
    }

    function resetSectionCutoffForm() {
        byId('cfgSectionCutoffEditId').value = '';
        setDateInputValue('cfgSectionCutoffDate', '');
        byId('cfgSectionCutoffSaveBtn').textContent = 'Save';
    }

    function resetCutoffConfigForm() {
        byId('cfgCutoffConfigEditId').value = '';
        setDateInputValue('cfgCutoffConfigDate', '');
        byId('cfgCutoffConfigSaveBtn').textContent = 'Update';
    }

    function resetCurriculumForm() {
        byId('cfgCurriculumEditId').value = '';
        byId('cfgCurriculumStatus').value = '';
        byId('cfgCurriculumSaveBtn').textContent = 'Submit';
        syncSelectUI('cfgCurriculumStatus');
    }

    async function saveSchoolSem() {
        if (requestLocks.schoolSem) {
            return;
        }

        var schoolYear = (byId('cfgSSYear').value || '').trim();
        var semester = byId('cfgSSSemester').value;
        var editId = byId('cfgSSEditId').value;
        var saveButton = byId('cfgSSSaveBtn');

        if (!schoolYear || !semester) {
            showMessage('Please complete School Year and Semester.', 'error');
            return;
        }

        var payload = {
            school_year: schoolYear,
            semester: semester
        };

        requestLocks.schoolSem = true;
        if (saveButton) {
            saveButton.disabled = true;
        }

        try {
            var response;
            if (editId) {
                response = await requestJson(routeFromTemplate(routes.schoolSemUpdateTemplate, editId), 'PUT', payload);
            } else {
                response = await requestJson(routes.schoolSemStore, 'POST', payload);
            }

            if (response.row) {
                upsertRow(state.schoolSem, response.row);
            }

            state.pager.schoolSem.page = 1;
            renderSchoolSem();
            closeModal('cfgSchoolSemModal');
            resetSchoolSemModal();
            showMessage('School year and semester saved.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to save school year and semester.', 'error');
        } finally {
            requestLocks.schoolSem = false;
            if (saveButton) {
                saveButton.disabled = false;
            }
        }
    }

    async function saveGradePosting() {
        var schoolYear = (byId('cfgGPYear').value || '').trim();
        var semester = byId('cfgGPSemester').value;
        var period = byId('cfgGPPeriod').value;
        var dateFrom = byId('cfgGPDateFrom').value;
        var editId = byId('cfgGPEditId').value;

        if (!schoolYear || !semester || !period || !dateFrom) {
            showMessage('Please complete all Grade Posting fields.', 'error');
            return;
        }

        var payload = {
            school_year: schoolYear,
            semester: semester,
            period: period,
            date_from: dateFrom
        };

        try {
            var response;
            if (editId) {
                response = await requestJson(routeFromTemplate(routes.gradePostingUpdateTemplate, editId), 'PUT', payload);
            } else {
                response = await requestJson(routes.gradePostingStore, 'POST', payload);
            }

            if (response.row) {
                upsertRow(state.gradePosting, response.row);
            }

            state.pager.gradePosting.page = 1;
            renderGradePosting();
            closeModal('cfgGradePostingModal');
            resetGradePostingModal();
            showMessage('Grade posting saved.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to save grade posting.', 'error');
        }
    }

    async function submitSignatureForm(event) {
        event.preventDefault();

        var designationId = byId('cfgSignatureDesignation').value;
        var signerName = (byId('cfgSignatureName').value || '').trim();
        var fileInput = byId('cfgSignatureFile');
        var editId = byId('cfgSignatureEditId').value;

        if (!designationId || !signerName) {
            setInlineMessage('cfgSignatureModalMessage', 'Please complete designation and name for signature.', true);
            showMessage('Please complete designation and name for signature.', 'error');
            return;
        }

        var formData = new FormData();
        formData.append('designation_id', designationId);
        formData.append('signer_name', signerName);

        if (fileInput.files && fileInput.files[0]) {
            formData.append('signature_file', fileInput.files[0]);
        }

        setInlineMessage('cfgSignatureModalMessage', 'Saving signature configuration...', false);
        setSignatureSaveButtonState(true);

        try {
            var response;
            if (editId) {
                response = await requestForm(routeFromTemplate(routes.signatureUpdateTemplate, editId), 'PUT', formData);
            } else {
                response = await requestForm(routes.signatureStore, 'POST', formData);
            }

            if (response.row) {
                upsertRow(state.signatures, response.row);
            }

            state.pager.signatures.page = 1;
            renderSignatures();
            closeModal('cfgSignatureModal');
            resetSignatureForm();
            showMessage('Signature configuration saved.', 'success');
        } catch (error) {
            setInlineMessage('cfgSignatureModalMessage', error.message || 'Unable to save signature configuration.', true);
            showMessage(error.message || 'Unable to save signature configuration.', 'error');
        } finally {
            setSignatureSaveButtonState(false);
        }
    }

    async function submitCutoffDateForm(event) {
        event.preventDefault();

        var typeCode = byId('cfgCutoffType').value;
        var schoolYear = (byId('cfgCutoffSy').value || '').trim();
        var semester = byId('cfgCutoffSemester').value;
        var cutoffDate = byId('cfgCutoffDate').value;
        var editId = byId('cfgCutoffDateEditId').value;

        if (!typeCode || !schoolYear || !semester || !cutoffDate) {
            showMessage('Please complete all Cut Off Date fields.', 'error');
            return;
        }

        var payload = {
            type_code: typeCode,
            school_year: schoolYear,
            semester: semester,
            cutoff_date: cutoffDate
        };

        try {
            var response;
            if (editId) {
                response = await requestJson(routeFromTemplate(routes.cutoffUpdateTemplate, editId), 'PUT', payload);
            } else {
                response = await requestJson(routes.cutoffStore, 'POST', payload);
            }

            if (response.row) {
                upsertRow(state.cutoffDate, response.row);
            }

            state.pager.cutoffDate.page = 1;
            renderCutoffDate();
            resetCutoffDateForm();
            showMessage('Cut-off date saved.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to save cut-off date.', 'error');
        }
    }

    async function submitSectionCutoffForm(event) {
        event.preventDefault();

        var schoolYear = (byId('cfgSectionCutoffSy').value || '').trim();
        var semester = byId('cfgSectionCutoffSemester').value;
        var cutoffDate = byId('cfgSectionCutoffDate').value;
        var editId = byId('cfgSectionCutoffEditId').value;

        if (!schoolYear || !semester || !cutoffDate) {
            showMessage('Please complete all Section Offering Cut Off fields.', 'error');
            return;
        }

        var payload = {
            type_code: 'SECTION_OFFERING',
            school_year: schoolYear,
            semester: semester,
            cutoff_date: cutoffDate
        };

        try {
            var response;
            if (editId) {
                response = await requestJson(routeFromTemplate(routes.cutoffUpdateTemplate, editId), 'PUT', payload);
            } else {
                response = await requestJson(routes.cutoffStore, 'POST', payload);
            }

            if (response.row) {
                upsertRow(state.sectionCutoff, response.row);
            }

            state.pager.sectionCutoff.page = 1;
            renderSectionCutoff();
            resetSectionCutoffForm();
            showMessage('Section offering cut-off saved.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to save section offering cut-off.', 'error');
        }
    }

    async function submitCutoffConfigForm(event) {
        event.preventDefault();

        var schoolYear = (byId('cfgCutoffConfigSy').value || '').trim();
        var semester = byId('cfgCutoffConfigSemester').value;
        var cutoffDate = byId('cfgCutoffConfigDate').value;
        var editId = byId('cfgCutoffConfigEditId').value;

        if (!schoolYear || !semester || !cutoffDate) {
            showMessage('Please complete all Changing/Deleting/Adding Cut-off fields.', 'error');
            return;
        }

        var payload = {
            type_code: 'CHANGING_DELETING_ADDING',
            school_year: schoolYear,
            semester: semester,
            cutoff_date: cutoffDate
        };

        try {
            var response;
            if (editId) {
                response = await requestJson(routeFromTemplate(routes.cutoffUpdateTemplate, editId), 'PUT', payload);
            } else {
                response = await requestJson(routes.cutoffStore, 'POST', payload);
            }

            if (response.row) {
                upsertRow(state.cutoffConfig, response.row);
            }

            state.pager.cutoffConfig.page = 1;
            renderCutoffConfig();
            resetCutoffConfigForm();
            showMessage('Cut-off configuration saved.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to save cut-off configuration.', 'error');
        }
    }

    async function submitCutoffRegistrationForm(event) {
        event.preventDefault();

        var schoolYear = (byId('cfgCutoffRegSy').value || '').trim();
        var semester = byId('cfgCutoffRegSemester').value;
        var eventDate = byId('cfgCutoffRegDate').value;
        var cutoffDate = byId('cfgCutoffRegCutoffDate').value;
        var studentNo = (byId('cfgCutoffRegStudentNo').value || '').trim();

        if (!schoolYear || !semester || !eventDate || !cutoffDate || !studentNo) {
            setInlineMessage('cfgCutoffRegMessage', 'Please complete all Cut-off Registration fields.', true);
            return;
        }

        var payload = {
            type_code: 'CUT_OFF_REGISTRATION',
            school_year: schoolYear,
            semester: semester,
            event_date: eventDate,
            cutoff_date: cutoffDate,
            student_no: studentNo
        };

        try {
            await requestJson(routes.cutoffStore, 'POST', payload);
            setInlineMessage('cfgCutoffRegMessage', 'Cut-off registration saved for student ' + studentNo + '.', false);
            setDateInputValue('cfgCutoffRegDate', '');
            setDateInputValue('cfgCutoffRegCutoffDate', '');
            byId('cfgCutoffRegStudentNo').value = '';
        } catch (error) {
            setInlineMessage('cfgCutoffRegMessage', error.message || 'Unable to save cut-off registration.', true);
        }
    }

    async function submitCurriculumForm(event) {
        event.preventDefault();

        var schoolYear = (byId('cfgCurriculumSy').value || '').trim();
        var semester = byId('cfgCurriculumSemester').value;
        var status = byId('cfgCurriculumStatus').value;
        var editId = byId('cfgCurriculumEditId').value;

        if (!schoolYear || !semester || !status) {
            showMessage('Please complete all Curriculum Evaluation Display fields.', 'error');
            return;
        }

        var payload = {
            school_year: schoolYear,
            semester: semester,
            display_status: status
        };

        try {
            var response;
            if (editId) {
                response = await requestJson(routeFromTemplate(routes.curriculumDisplayUpdateTemplate, editId), 'PUT', payload);
            } else {
                response = await requestJson(routes.curriculumDisplayStore, 'POST', payload);
            }

            if (response.row) {
                upsertRow(state.curriculumDisplay, response.row);
            }

            state.pager.curriculumDisplay.page = 1;
            renderCurriculumDisplay();
            resetCurriculumForm();
            showMessage('Curriculum display saved.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to save curriculum display.', 'error');
        }
    }

    async function submitReportDetailsForm(event) {
        event.preventDefault();

        var payload = {
            region: (byId('cfgReportRegion').value || '').trim(),
            division: (byId('cfgReportDivision').value || '').trim(),
            school_id: (byId('cfgReportSchoolId').value || '').trim(),
            school_name: (byId('cfgReportSchoolName').value || '').trim(),
            contact_details: (byId('cfgReportContactDetails').value || '').trim()
        };

        if (!payload.region || !payload.division || !payload.school_id || !payload.school_name || !payload.contact_details) {
            showMessage('Please complete all Report Details fields.', 'error');
            return;
        }

        try {
            await requestJson(routes.reportDetailsSave, 'POST', payload);
            showMessage('Report details saved.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to save report details.', 'error');
        }
    }

    async function submitEmailSenderForm(event) {
        event.preventDefault();

        var payload = {
            email: (byId('cfgEmailSenderAddress').value || '').trim(),
            password: (byId('cfgEmailSenderPassword').value || '').trim()
        };

        if (!payload.email) {
            showMessage('Please provide an email sender address.', 'error');
            return;
        }

        try {
            var response = await requestJson(routes.emailSenderSave, 'POST', payload);
            if (response.row && response.row.email) {
                byId('cfgEmailSenderAddress').value = response.row.email;
            }

            byId('cfgEmailSenderPassword').value = '';
            showMessage('Email sender saved.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to save email sender.', 'error');
        }
    }

    async function submitOverdueIncForm(event) {
        event.preventDefault();

        var payload = {
            school_year: (byId('cfgIncSy').value || '').trim(),
            semester: byId('cfgIncSemester').value
        };

        if (!payload.school_year || !payload.semester) {
            setInlineMessage('cfgIncProcessMessage', 'Please provide School Year and Semester to process.', true);
            return;
        }

        try {
            var response = await requestJson(routes.overdueIncProcess, 'POST', payload);
            var message = response && response.message
                ? response.message
                : ('Processed ' + (response.processedCount || 0) + ' INC record(s).');

            setInlineMessage('cfgIncProcessMessage', message, false);

            if (response && response.run) {
                state.latestIncRun = response.run;
            }
        } catch (error) {
            setInlineMessage('cfgIncProcessMessage', error.message || 'Unable to process overdue INC records.', true);
        }
    }

    function openDeleteModal(group, index) {
        var row = listFor(group)[index];
        if (!row) {
            return;
        }

        state.deleteTarget = {
            group: group,
            id: row.id
        };

        var detail = byId('cfgDeleteDetail');
        if (detail) {
            detail.textContent = row.name || row.designation || row.sy || row.type || '';
        }

        openModal('cfgDeleteModal');
    }

    async function confirmDelete() {
        if (state.deleteBusy) {
            return;
        }

        if (!state.deleteTarget || !state.deleteTarget.id) {
            closeModal('cfgDeleteModal');
            return;
        }

        var group = state.deleteTarget.group;
        var id = state.deleteTarget.id;
        var endpoint = '';

        if (group === 'schoolSem') {
            endpoint = routeFromTemplate(routes.schoolSemDeleteTemplate, id);
        } else if (group === 'gradePosting') {
            endpoint = routeFromTemplate(routes.gradePostingDeleteTemplate, id);
        } else if (group === 'signatures') {
            endpoint = routeFromTemplate(routes.signatureDeleteTemplate, id);
        } else if (group === 'cutoffDate' || group === 'sectionCutoff' || group === 'cutoffConfig') {
            endpoint = routeFromTemplate(routes.cutoffDeleteTemplate, id);
        } else if (group === 'curriculumDisplay') {
            endpoint = routeFromTemplate(routes.curriculumDisplayDeleteTemplate, id);
        }

        if (!endpoint) {
            closeModal('cfgDeleteModal');
            return;
        }

        var confirmButton = document.querySelector('[data-cfg-action="confirm-delete"]');
        state.deleteBusy = true;
        if (confirmButton) {
            confirmButton.disabled = true;
        }

        try {
            await requestJson(endpoint, 'DELETE');

            if (group === 'cutoffDate' || group === 'sectionCutoff' || group === 'cutoffConfig') {
                removeRow(state.cutoffDate, id);
                removeRow(state.sectionCutoff, id);
                removeRow(state.cutoffConfig, id);
                renderCutoffDate();
                renderSectionCutoff();
                renderCutoffConfig();
            } else {
                removeRow(listFor(group), id);
                renderGroup(group);
            }

            state.deleteTarget = null;
            closeModal('cfgDeleteModal');
            showMessage('Record deleted.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to delete record.', 'error');
        } finally {
            state.deleteBusy = false;
            if (confirmButton) {
                confirmButton.disabled = false;
            }
        }
    }

    async function deleteSignatureFile(index) {
        var row = state.signatures[index];
        if (!row || !row.signatureDeleteUrl) {
            showMessage('No signature file is available to delete.', 'error');
            return;
        }

        if (!window.confirm('Delete the uploaded signature file?')) {
            return;
        }

        try {
            var response = await requestJson(row.signatureDeleteUrl, 'DELETE');
            if (response && response.row) {
                upsertRow(state.signatures, response.row);
            } else {
                row.signaturePath = '';
                row.signatureUrl = '';
            }
            renderSignatures();
            showMessage((response && response.message) || 'Signature file deleted.', 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to delete signature file.', 'error');
        }
    }

    function editRow(group, index) {
        var row = listFor(group)[index];
        if (!row) {
            return;
        }

        if (group === 'schoolSem') {
            byId('cfgSSEditId').value = row.id || '';
            byId('cfgSSTitle').textContent = 'EDIT SCHOOL YEAR AND SEMESTER';
            byId('cfgSSSaveBtn').textContent = 'Update';
            byId('cfgSSYear').value = row.sy || '';
            byId('cfgSSSemester').value = row.semester || '';
            syncSelectUI('cfgSSSemester');
            openModal('cfgSchoolSemModal');
            return;
        }

        if (group === 'gradePosting') {
            byId('cfgGPEditId').value = row.id || '';
            byId('cfgGPTitle').textContent = 'EDIT GRADE POSTING';
            byId('cfgGPSaveBtn').textContent = 'Update';
            byId('cfgGPYear').value = row.sy || '';
            byId('cfgGPSemester').value = row.semester || '';
            byId('cfgGPPeriod').value = row.period || '';
            setDateInputValue('cfgGPDateFrom', row.dateFrom);
            syncSelectUI('cfgGPSemester');
            syncSelectUI('cfgGPPeriod');
            openModal('cfgGradePostingModal');
            return;
        }

        if (group === 'signatures') {
            byId('cfgSignatureEditId').value = row.id || '';
            byId('cfgSignatureDesignation').value = row.designationId || '';
            byId('cfgSignatureName').value = row.name || '';
            byId('cfgSignatureFile').value = '';
            byId('cfgSigTitle').textContent = 'EDIT SIGNATURE';
            setInlineMessage('cfgSignatureModalMessage', '', false);
            setSignatureSaveButtonState(false);
            syncSelectUI('cfgSignatureDesignation');
            openModal('cfgSignatureModal');
            return;
        }

        if (group === 'cutoffDate') {
            byId('cfgCutoffDateEditId').value = row.id || '';
            byId('cfgCutoffType').value = row.typeCode || '';
            byId('cfgCutoffSy').value = row.sy || '';
            byId('cfgCutoffSemester').value = row.semester || '';
            setDateInputValue('cfgCutoffDate', row.cutoffDate);
            byId('cfgCutoffSaveBtn').textContent = 'Update';
            syncSelectUI('cfgCutoffType');
            syncSelectUI('cfgCutoffSemester');
            return;
        }

        if (group === 'sectionCutoff') {
            byId('cfgSectionCutoffEditId').value = row.id || '';
            byId('cfgSectionCutoffSy').value = row.sy || '';
            byId('cfgSectionCutoffSemester').value = row.semester || '';
            setDateInputValue('cfgSectionCutoffDate', row.cutoffDate);
            byId('cfgSectionCutoffSaveBtn').textContent = 'Update';
            syncSelectUI('cfgSectionCutoffSemester');
            return;
        }

        if (group === 'cutoffConfig') {
            byId('cfgCutoffConfigEditId').value = row.id || '';
            byId('cfgCutoffConfigSy').value = row.sy || '';
            byId('cfgCutoffConfigSemester').value = row.semester || '';
            setDateInputValue('cfgCutoffConfigDate', row.cutoffDate);
            byId('cfgCutoffConfigSaveBtn').textContent = 'Update';
            syncSelectUI('cfgCutoffConfigSemester');
            return;
        }

        if (group === 'curriculumDisplay') {
            byId('cfgCurriculumEditId').value = row.id || '';
            byId('cfgCurriculumSy').value = row.sy || '';
            byId('cfgCurriculumSemester').value = row.semester || '';
            byId('cfgCurriculumStatus').value = row.status || '';
            byId('cfgCurriculumSaveBtn').textContent = 'Update';
            syncSelectUI('cfgCurriculumSemester');
            syncSelectUI('cfgCurriculumStatus');
        }
    }

    function bindForms() {
        var schoolSemButton = byId('cfgSSSaveBtn');
        if (schoolSemButton) {
            schoolSemButton.addEventListener('click', saveSchoolSem);
        }

        var gradePostingButton = byId('cfgGPSaveBtn');
        if (gradePostingButton) {
            gradePostingButton.addEventListener('click', saveGradePosting);
        }

        var signatureForm = byId('cfgSignatureForm');
        if (signatureForm) {
            signatureForm.addEventListener('submit', submitSignatureForm);
        }

        var cutoffDateForm = byId('cfgCutoffDateForm');
        if (cutoffDateForm) {
            cutoffDateForm.addEventListener('submit', submitCutoffDateForm);
        }

        var sectionCutoffForm = byId('cfgSectionCutoffForm');
        if (sectionCutoffForm) {
            sectionCutoffForm.addEventListener('submit', submitSectionCutoffForm);
        }

        var cutoffConfigForm = byId('cfgCutoffConfigForm');
        if (cutoffConfigForm) {
            cutoffConfigForm.addEventListener('submit', submitCutoffConfigForm);
        }

        var cutoffRegistrationForm = byId('cfgCutoffRegistrationForm');
        if (cutoffRegistrationForm) {
            cutoffRegistrationForm.addEventListener('submit', submitCutoffRegistrationForm);
        }

        var curriculumForm = byId('cfgCurriculumDisplayForm');
        if (curriculumForm) {
            curriculumForm.addEventListener('submit', submitCurriculumForm);
        }

        var reportForm = byId('cfgReportDetailsForm');
        if (reportForm) {
            reportForm.addEventListener('submit', submitReportDetailsForm);
        }

        var emailForm = byId('cfgEmailSenderForm');
        if (emailForm) {
            emailForm.addEventListener('submit', submitEmailSenderForm);
        }

        var overdueIncForm = byId('cfgOverdueIncForm');
        if (overdueIncForm) {
            overdueIncForm.addEventListener('submit', submitOverdueIncForm);
        }
    }

    function bindGlobalEvents() {
        document.addEventListener('click', function (event) {
            var openSchool = event.target.closest('[data-cfg-action="open-school-sem-modal"]');
            if (openSchool) {
                resetSchoolSemModal();
                openModal('cfgSchoolSemModal');
                return;
            }

            var openGrade = event.target.closest('[data-cfg-action="open-grade-posting-modal"]');
            if (openGrade) {
                resetGradePostingModal();
                openModal('cfgGradePostingModal');
                return;
            }

            var openSignature = event.target.closest('[data-cfg-action="open-signature-modal"]');
            if (openSignature) {
                resetSignatureForm();
                openModal('cfgSignatureModal');
                return;
            }

            var closeModalButton = event.target.closest('[data-cfg-action="close-modal"]');
            if (closeModalButton) {
                var modalTarget = closeModalButton.getAttribute('data-cfg-modal-target');
                if (modalTarget) {
                    closeModal(modalTarget);
                    if (modalTarget === 'cfgSignatureModal') {
                        resetSignatureForm();
                    }
                }
                return;
            }

            var menuToggle = event.target.closest('[data-cfg-menu-toggle]');
            if (menuToggle) {
                event.stopPropagation();
                toggleActionMenu(menuToggle.getAttribute('data-cfg-menu-toggle'), menuToggle);
                return;
            }

            var pageButton = event.target.closest('[data-cfg-action="set-page"]');
            if (pageButton) {
                var pageGroup = pageButton.getAttribute('data-cfg-group');
                var pageValue = parseInt(pageButton.getAttribute('data-cfg-page'), 10) || 1;
                if (pageGroup && state.pager[pageGroup]) {
                    setPage(pageGroup, pageValue);
                }
                return;
            }

            var editButton = event.target.closest('[data-cfg-action="edit-row"]');
            if (editButton) {
                var editGroup = editButton.getAttribute('data-cfg-group');
                var editIndex = parseInt(editButton.getAttribute('data-cfg-index'), 10) || 0;
                closeActionMenus();
                editRow(editGroup, editIndex);
                return;
            }

            var deleteButton = event.target.closest('[data-cfg-action="delete-row"]');
            if (deleteButton) {
                var deleteGroup = deleteButton.getAttribute('data-cfg-group');
                var deleteIndex = parseInt(deleteButton.getAttribute('data-cfg-index'), 10) || 0;
                closeActionMenus();
                openDeleteModal(deleteGroup, deleteIndex);
                return;
            }

            var deleteSignatureFileButton = event.target.closest('[data-cfg-action="delete-signature-file"]');
            if (deleteSignatureFileButton) {
                var signatureIndex = parseInt(deleteSignatureFileButton.getAttribute('data-cfg-index'), 10) || 0;
                deleteSignatureFile(signatureIndex);
                return;
            }

            var confirmDeleteButton = event.target.closest('[data-cfg-action="confirm-delete"]');
            if (confirmDeleteButton) {
                confirmDelete();
                return;
            }

            if (!event.target.closest('.apst-dropdown')) {
                closeActionMenus();
            }
        });

        document.querySelectorAll('[data-cfg-modal]').forEach(function (overlay) {
            overlay.addEventListener('click', function (event) {
                if (event.target === overlay) {
                    overlay.classList.add('is-hidden');
                    if (overlay.id === 'cfgSignatureModal') {
                        resetSignatureForm();
                    }
                }
            });
        });

        window.addEventListener('scroll', closeActionMenus, true);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeActionMenus();
            }
        });
    }

    function applyInitialMessages() {
        if (!state.latestIncRun || state.latestIncRun.processedCount === undefined) {
            return;
        }

        var message = 'Last run: ' +
            (state.latestIncRun.schoolYear || '-') + ' ' +
            (state.latestIncRun.semester || '-') +
            ' | Processed ' + state.latestIncRun.processedCount +
            ' record(s) on ' + (state.latestIncRun.createdAt || '-');

        setInlineMessage('cfgIncProcessMessage', message, false);
    }

    function resetSignatureFormSimple() {
        byId('cfgSignatureEditId').value = '';
        byId('cfgSignatureName').value = '';
        byId('cfgSignatureFile').value = '';
        byId('cfgSignatureDesignation').value = '';
        refreshListboxes(byId('cfgSignatureDesignation'));
        byId('cfgSigTitle').textContent = 'ADD SIGNATORY';
    }

    function initDesignationFeatures() {
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-cfg-action="open-designation-modal"]');
            if (btn) openModal('cfgDesignationModal');

            var sigBtn = e.target.closest('[data-cfg-action="open-signature-modal"]');
            if (sigBtn) {
                resetSignatureFormSimple();
                openModal('cfgSignatureModal');
            }
        });

        var progSearchInput = byId('progSearchInput');
        var progItems = document.querySelectorAll('.sc-multi-select-item');
        if (progSearchInput) {
            progSearchInput.addEventListener('input', function() {
                var query = (this.value || '').toLowerCase().trim();
                progItems.forEach(function(item) {
                    var text = (item.getAttribute('data-search-text') || '').toLowerCase();
                    item.style.display = text.indexOf(query) !== -1 ? 'flex' : 'none';
                });
            });
        }

        var designationSaveBtn = byId('cfgDesignationSaveBtn');
        if (designationSaveBtn) {
            designationSaveBtn.addEventListener('click', function() {
                var name = (byId('newDesignationName').value || '').trim();
                if (!name) {
                    showMessage('Please provide a designation name.', 'error');
                    return;
                }
                showMessage('New designation "' + name + '" created successfully.', 'success');

                // Add to signatory dropdown list (UI Preview)
                var designationSelect = byId('cfgSignatureDesignation');
                if (designationSelect) {
                    var opt = document.createElement('option');
                    opt.value = String(Date.now()); // Mock Integer ID
                    opt.textContent = name;
                    designationSelect.appendChild(opt);

                    // Force refresh all custom listboxes
                    if (window.registrarListboxSelect && typeof window.registrarListboxSelect.refreshAll === 'function') {
                        window.registrarListboxSelect.refreshAll();
                    }
                }

                closeModal('cfgDesignationModal');
                byId('newDesignationName').value = '';
                if (progSearchInput) progSearchInput.value = '';
                progItems.forEach(function(item) { item.style.display = 'flex'; });
                document.querySelectorAll('input[name="target_programs[]"]').forEach(function(cb) { cb.checked = false; });
            });
        }

        var desAllPrograms = byId('cfgDesignationAll');
        if (desAllPrograms) {
            desAllPrograms.addEventListener('change', function() {
                var progList = byId('designationProgListContainer');
                if (progList) {
                    progList.style.opacity = this.checked ? '0.5' : '1';
                    progList.style.pointerEvents = this.checked ? 'none' : 'auto';
                }
            });
        }
    }

    // UI-Only: Mock existing data to show program tags
    state.signatures = state.signatures.map(function(sig) {
        if (sig.designation === 'University Registrar' || sig.designation === 'Registrar') {
            sig.programs = ['ALL'];
        } else if (sig.designation === 'Assistant Registrar') {
            sig.programs = ['BSHM', 'BSN', 'MAN'];
        } else if (sig.designation === 'Accounting Head') {
            sig.programs = ['BSA', 'BSENT'];
        }
        return sig;
    });

    bindForms();
    bindGlobalEvents();
    initDesignationFeatures();
    renderAll();
    initFlatpickrDateInputs();
    refreshListboxes();
    applyInitialMessages();
})();
