(function () {
    var originalSidebar = document.querySelector('.plp-sidebar:not(#applicantSidebar)');
    var applicantSidebar = document.getElementById('applicantSidebar');
    var appProcessPage = document.getElementById('appProcessPage');
    var applicantDetailView = document.getElementById('applicantDetailView');
    var pageHeader = document.querySelector('.student-page-header');

    if (!appProcessPage || !applicantDetailView || !applicantSidebar || !pageHeader) {
        return;
    }

    var csrfToken = appProcessPage.getAttribute('data-csrf-token') || '';
    if (!csrfToken) {
        var meta = document.querySelector('meta[name="csrf-token"]');
        csrfToken = meta ? meta.getAttribute('content') : '';
    }

    var examScheduleUrlTemplate = appProcessPage.getAttribute('data-exam-schedule-url-template') || '';
    var examResultUrlTemplate = appProcessPage.getAttribute('data-exam-result-url-template') || '';
    var approvalStatusUrlTemplate = appProcessPage.getAttribute('data-approval-status-url-template') || '';
    var documentsDataUrlTemplate = appProcessPage.getAttribute('data-documents-data-url-template') || '';
    var documentsUpsertUrlTemplate = appProcessPage.getAttribute('data-documents-upsert-url-template') || '';
    var medicalDataUrlTemplate = appProcessPage.getAttribute('data-medical-data-url-template') || documentsDataUrlTemplate;
    var medicalUpsertUrlTemplate = appProcessPage.getAttribute('data-medical-upsert-url-template') || documentsUpsertUrlTemplate;
    var formUrlTemplate = appProcessPage.getAttribute('data-form-url-template') || '';

    var applicationFilterForm = document.getElementById('applicationFilterForm');
    var appSearchInput = document.getElementById('appSearch');
    var appSearchSubmitBtn = document.getElementById('searchApplicantListBtn');
    var applicantTableBody = document.getElementById('applicantTableBody');
    var applicantTablePager = document.getElementById('applicantTablePager');
    var printApplicantListBtn = document.getElementById('printApplicantListBtn');
    var applicantPrintBaseUrl = printApplicantListBtn ? printApplicantListBtn.href.split('?')[0] : '';
    var searchSubmitTimer = null;
    var filterIsSubmitting = false;
    var applicantTableRequestId = 0;

    var detailApplicantPk = document.getElementById('detailApplicantPk');
    var detailApplicantId = document.getElementById('detailApplicantId');
    var detailApplicantName = document.getElementById('detailApplicantName');

    var scheduleExamDate = document.getElementById('scheduleExamDate');
    var scheduleExamTime = document.getElementById('scheduleExamTime');
    var scheduleExamVenue = document.getElementById('scheduleExamVenue');
    var scheduleExamFeedback = document.getElementById('scheduleExamFeedback');
    var saveExamScheduleBtn = document.getElementById('saveExamScheduleBtn');
    var printExamScheduleBtn = document.getElementById('printExamScheduleBtn');
    var scheduleExamSuccessModalEl = document.getElementById('scheduleExamSuccessModal');
    var scheduleExamSuccessMessageEl = document.getElementById('scheduleExamSuccessMessage');

    var examResultCard = document.getElementById('examResultCard');
    var examResultNoData = document.getElementById('examResultNoData');
    var examResultBadge = document.getElementById('examResultBadge');
    var examResultScoreLine = document.getElementById('examResultScoreLine');
    var examResultScoreText = document.getElementById('examResultScoreText');
    var examResultMessage = document.getElementById('examResultMessage');

    var appFormEditorEmpty = document.getElementById('registrarAppFormEditorEmpty');
    var appFormEditorFrame = document.getElementById('registrarAppFormEditorFrame');

    var approvalProgramInput = document.getElementById('approvalProgramInput');
    var approvalStatusSelect = document.getElementById('approvalStatusSelect');
    var approvalDateAccepted = document.getElementById('approvalDateAccepted');
    var approvalBanner = document.getElementById('approvalBanner');
    var approvalSaveBtn = document.getElementById('approvalSaveBtn');
    var approvalStatusWrapper = approvalStatusSelect ? approvalStatusSelect.closest('[data-listbox-select]') : null;
    var approvalStatusTrigger = approvalStatusWrapper ? approvalStatusWrapper.querySelector('[data-select-trigger]') : null;
    var approvalStatusMenu = approvalStatusWrapper ? approvalStatusWrapper.querySelector('[data-select-menu]') : null;

    var docsPanel = document.getElementById('documentsPanel');
    var docsSearchInput = document.getElementById('docsSearchInput');
    var docsStatusFilter = document.getElementById('docsStatusFilter');
    var docsPerPage = document.getElementById('docsPerPage');
    var docsSelectAll = document.getElementById('docsSelectAll');
    var docsTableBody = document.getElementById('docsTableBody');
    var docsTablePager = document.getElementById('docsTablePager');
    var docsPanelFeedback = document.getElementById('docsPanelFeedback');

    var addRequirementBtn = document.getElementById('addRequirementBtn');
    var addRequirementModalEl = document.getElementById('addRequirementModal');
    var addRequirementFeedback = document.getElementById('addRequirementFeedback');
    var newRequirementInput = document.getElementById('newRequirementInput');
    var newRequirementLevel = document.getElementById('newRequirementLevel');
    var createRequirementBtn = document.getElementById('createRequirementBtn');
    var docsConfirmModalEl = document.getElementById('docsConfirmModal');
    var docsConfirmTitleEl = document.getElementById('docsConfirmTitle');
    var docsConfirmMessageEl = document.getElementById('docsConfirmMessage');
    var docsConfirmActionBtn = document.getElementById('docsConfirmActionBtn');
    var docsConfirmCancelBtn = document.getElementById('docsConfirmCancelBtn');
    var docsConfirmCloseBtn = document.getElementById('docsConfirmCloseBtn');

    var medicalPanel = document.getElementById('medicalClearancePanel');
    var medicalSearchInput = document.getElementById('medicalSearchInput');
    var medicalPerPage = document.getElementById('medicalPerPage');
    var medicalTableBody = document.getElementById('medicalTableBody');
    var medicalTablePager = document.getElementById('medicalTablePager');
    var medicalPanelFeedback = document.getElementById('medicalPanelFeedback');
    var medicalSaveBtn = document.getElementById('medicalSaveBtn');

    var docsSearchTimer = null;
    var docsState = {
        rows: [],
        page: 1,
        lastPage: 1,
        perPage: 10,
        total: 0,
        search: '',
        status: ''
    };

    var medicalSearchTimer = null;
    var medicalState = {
        rows: [],
        page: 1,
        lastPage: 1,
        perPage: 10,
        total: 0,
        search: ''
    };

    var selectedApplicantRow = null;

    function submitFilterForm() {
        if (!applicationFilterForm) {
            return;
        }

        loadApplicantTable();
    }

    function getApplicantFilterQueryString() {
        if (!applicationFilterForm) {
            return '';
        }

        var params = new URLSearchParams();
        applicationFilterForm.querySelectorAll('input[name], select[name]').forEach(function (field) {
            if (!field.name || field.disabled) {
                return;
            }

            if (field.type === 'button' || field.type === 'submit' || field.type === 'reset') {
                return;
            }

            if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) {
                return;
            }

            var value = String(field.value || '').trim();
            if (value === '') {
                return;
            }

            params.set(field.name, value);
        });

        return params.toString();
    }

    function buildApplicantTableUrl(pageUrl) {
        if (pageUrl) {
            return pageUrl;
        }

        var baseUrl = (applicationFilterForm && applicationFilterForm.getAttribute('action')) || window.location.pathname;
        var queryString = getApplicantFilterQueryString();

        return queryString ? baseUrl + '?' + queryString : baseUrl;
    }

    function syncApplicantPrintLink() {
        if (!printApplicantListBtn || !applicantPrintBaseUrl) {
            return;
        }

        var queryString = getApplicantFilterQueryString();
        printApplicantListBtn.href = queryString ? applicantPrintBaseUrl + '?' + queryString + '&autoprint=1' : applicantPrintBaseUrl + '?autoprint=1';
    }

    function setApplicantListLoading(isLoading) {
        filterIsSubmitting = isLoading;

        if (appSearchSubmitBtn) {
            appSearchSubmitBtn.disabled = isLoading;
        }
    }

    function renderApplicantList(payload) {
        if (!payload) {
            return;
        }

        if (applicantTableBody && typeof payload.rows_html === 'string') {
            applicantTableBody.innerHTML = payload.rows_html;
        }

        if (applicantTablePager && typeof payload.pager_html === 'string') {
            applicantTablePager.innerHTML = payload.pager_html;
        }

        syncApplicantPrintLink();
    }

    function loadApplicantTable(pageUrl) {
        if (!applicationFilterForm || (!applicantTableBody && !applicantTablePager)) {
            return;
        }

        var requestUrl = buildApplicantTableUrl(pageUrl);
        var requestId = ++applicantTableRequestId;

        syncApplicantPrintLink();
        setApplicantListLoading(true);

        window.fetch(requestUrl, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Unable to load applicants.');
                }

                return response.json();
            })
            .then(function (payload) {
                if (requestId !== applicantTableRequestId) {
                    return;
                }

                renderApplicantList(payload);

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', requestUrl);
                }
            })
            .catch(function () {
                if (requestId !== applicantTableRequestId) {
                    return;
                }

                window.location.assign(requestUrl);
            })
            .finally(function () {
                if (requestId === applicantTableRequestId) {
                    setApplicantListLoading(false);
                }
            });
    }

    function bindApplicantPagerLinks() {
        if (!applicantTablePager || applicantTablePager.getAttribute('data-ajax-bound') === '1') {
            return;
        }

        applicantTablePager.setAttribute('data-ajax-bound', '1');
        applicantTablePager.addEventListener('click', function (event) {
            var link = event.target.closest ? event.target.closest('a') : null;
            if (!link || !applicantTablePager.contains(link)) {
                return;
            }

            event.preventDefault();
            loadApplicantTable(link.href);
        });
    }

    function bindAutoFilterForm() {
        if (!applicationFilterForm) {
            return;
        }

        applicationFilterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            submitFilterForm();
        });

        applicationFilterForm.querySelectorAll('select, input[type="date"]').forEach(function (field) {
            field.addEventListener('change', function () {
                if (searchSubmitTimer) {
                    clearTimeout(searchSubmitTimer);
                    searchSubmitTimer = null;
                }
                submitFilterForm();
            });
        });

        if (!appSearchInput) {
            return;
        }

        appSearchInput.addEventListener('input', function () {
            if (searchSubmitTimer) {
                clearTimeout(searchSubmitTimer);
            }

            searchSubmitTimer = setTimeout(function () {
                searchSubmitTimer = null;
                submitFilterForm();
            }, 280);
        });

        appSearchInput.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            if (searchSubmitTimer) {
                clearTimeout(searchSubmitTimer);
                searchSubmitTimer = null;
            }
            submitFilterForm();
        });
    }

    if (appSearchSubmitBtn) {
        appSearchSubmitBtn.addEventListener('click', function () {
            if (searchSubmitTimer) {
                clearTimeout(searchSubmitTimer);
                searchSubmitTimer = null;
            }

            submitFilterForm();
        });
    }

    bindApplicantPagerLinks();

    function getUrlFromTemplate(template, applicantPk) {
        if (!template || !applicantPk) {
            return '';
        }

        return template.replace('__APPLICANT_ID__', String(applicantPk));
    }

    function getDocumentUpsertUrl(applicantPk, requirementId) {
        if (!documentsUpsertUrlTemplate || !applicantPk || !requirementId) {
            return '';
        }

        return documentsUpsertUrlTemplate
            .replace('__APPLICANT_ID__', String(applicantPk))
            .replace('__REQUIREMENT_ID__', String(requirementId));
    }

    function getDocumentAvailableUrl(applicantPk) {
        var base = getUrlFromTemplate(documentsDataUrlTemplate, applicantPk);
        return base ? base.replace(/\/$/, '') + '/available' : '';
    }

    function getDocumentAssignUrl(applicantPk) {
        var base = getUrlFromTemplate(documentsDataUrlTemplate, applicantPk);
        return base ? base.replace(/\/$/, '') + '/assign' : '';
    }

    function getDocumentCreateRequirementUrl(applicantPk) {
        var base = getUrlFromTemplate(documentsDataUrlTemplate, applicantPk);
        return base ? base.replace(/\/$/, '') + '/requirement/new' : '';
    }

    function getMedicalDataUrl(applicantPk) {
        var base = getUrlFromTemplate(medicalDataUrlTemplate, applicantPk);
        return base ? base.replace(/\/$/, '') : '';
    }

    function getMedicalUpsertUrl(applicantPk, requirementId) {
        if (!medicalUpsertUrlTemplate || !applicantPk || !requirementId) {
            return '';
        }

        return medicalUpsertUrlTemplate
            .replace('__APPLICANT_ID__', String(applicantPk))
            .replace('__REQUIREMENT_ID__', String(requirementId));
    }

    function getDocumentDeleteFileUrl(applicantPk, requirementId) {
        var base = getDocumentUpsertUrl(applicantPk, requirementId);
        return base ? base.replace(/\/$/, '') + '/file' : '';
    }

    function getDocumentUnassignUrl(applicantPk, requirementId) {
        var base = getDocumentUpsertUrl(applicantPk, requirementId);
        return base ? base.replace(/\/$/, '') + '/assignment' : '';
    }

    function setFeedback(node, message, isError) {
        if (!node) {
            return;
        }

        node.textContent = message || '';
        node.classList.remove('is-error', 'is-success');

        if (!message) {
            return;
        }

        node.classList.add(isError ? 'is-error' : 'is-success');
    }

    function getMedicalDateDisplayInput(dateInput) {
        if (!dateInput) {
            return null;
        }

        if (dateInput._flatpickr && dateInput._flatpickr.altInput) {
            return dateInput._flatpickr.altInput;
        }

        return dateInput;
    }

    function setMedicalDateInvalidState(dateInput, isInvalid) {
        var visibleInput = getMedicalDateDisplayInput(dateInput);
        if (!visibleInput) {
            return;
        }

        visibleInput.classList.toggle('is-invalid', !!isInvalid);
    }

    function initMedicalDatePickers() {
        if (!medicalTableBody) {
            return;
        }

        var dateInputs = medicalTableBody.querySelectorAll('.js-medical-flatpickr-date');
        if (!dateInputs.length) {
            return;
        }

        if (typeof window.flatpickr !== 'function') {
            dateInputs.forEach(function (input) {
                if (input.dataset.medicalDateFallbackBound === '1') {
                    return;
                }

                input.dataset.medicalDateFallbackBound = '1';
                input.addEventListener('input', function () {
                    setMedicalDateInvalidState(input, false);
                });
            });
            return;
        }

        dateInputs.forEach(function (input) {
            if (input._flatpickr) {
                return;
            }

            window.flatpickr(input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                altInputClass: 'apc-input apc-input--date medical-date-input medical-date-display',
                disableMobile: true,
                allowInput: false,
                prevArrow: '&#8249;',
                nextArrow: '&#8250;',
                onReady: function onReady(_, __, instance) {
                    instance.input.setAttribute('autocomplete', 'off');
                    instance.calendarContainer.classList.add('an-flatpickr-calendar', 'app-form-flatpickr-theme');

                    if (instance.altInput) {
                        instance.altInput.setAttribute('placeholder', 'Select date');
                        instance.altInput.setAttribute('autocomplete', 'off');
                    }
                },
                onChange: function onChange(_, __, instance) {
                    instance.input.dispatchEvent(new Event('input', { bubbles: true }));
                    instance.input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }

    function setMedicalFeedback(message, isError) {
        setFeedback(medicalPanelFeedback, message, isError);
    }

    function renderMedicalPager() {
        if (!medicalTablePager) {
            return;
        }

        if (medicalState.lastPage <= 1) {
            medicalTablePager.innerHTML = '';
            return;
        }

        var current = medicalState.page;
        var lastPage = medicalState.lastPage;
        var start = Math.max(1, current - 2);
        var end = Math.min(lastPage, current + 2);

        if (current <= 3) {
            end = Math.min(lastPage, 5);
        } else if (current >= lastPage - 2) {
            start = Math.max(1, lastPage - 4);
        }

        var html = '' +
            '<nav class="cfg-page-nav-wrap" aria-label="Medical clearance pagination">' +
                '<div class="cfg-page-list" role="group" aria-label="Medical clearance page controls">' +
                    '<button type="button" class="btn cfg-page-btn" data-medical-page="' + (current - 1) + '" ' + (current <= 1 ? 'disabled' : '') + ' aria-label="Previous page">&lt;</button>';

        for (var page = start; page <= end; page += 1) {
            html += '<button type="button" class="btn cfg-page-num ' + (page === current ? 'active' : '') + '" data-medical-page="' + page + '" ' + (page === current ? 'aria-current="page"' : '') + '>' + page + '</button>';
        }

        html += '' +
                    '<button type="button" class="btn cfg-page-btn" data-medical-page="' + (current + 1) + '" ' + (current >= lastPage ? 'disabled' : '') + ' aria-label="Next page">&gt;</button>' +
                '</div>' +
            '</nav>';

        medicalTablePager.innerHTML = html;
    }

    function renderMedicalRows() {
        if (!medicalTableBody) {
            return;
        }

        if (!medicalState.rows.length) {
            var emptyText = medicalPanel ? medicalPanel.getAttribute('data-empty-text') : 'No medical clearance requirements found.';
            medicalTableBody.innerHTML = '<tr><td colspan="4" class="sc-empty-row">' + escapeHtml(emptyText || 'No medical clearance requirements found.') + '</td></tr>';
            if (medicalSaveBtn) {
                medicalSaveBtn.disabled = true;
            }
            renderMedicalPager();
            return;
        }

        var rowsHtml = medicalState.rows.map(function (row, index) {
            var requirementId = Number(row.requirement_id || 0);
            var fileInputId = 'medicalFileInput_' + requirementId + '_' + index;

            return '' +
                '<tr data-requirement-id="' + requirementId + '">' +
                    '<td class="apc-check-cell"><input type="checkbox" class="medical-row-checkbox" ' + (row.is_submitted ? 'checked' : '') + '></td>' +
                    '<td>' + escapeHtml(row.document_type || '') + '</td>' +
                    '<td><input type="text" class="apc-input medical-remarks-input" value="' + escapeHtml(row.remarks || '') + '" placeholder="Type remarks"></td>' +
                    '<td><input type="text" class="apc-input apc-input--date medical-date-input js-medical-flatpickr-date" value="' + escapeHtml(row.date_submitted || '') + '" placeholder="Select date" autocomplete="off"></td>' +
                '</tr>';
        }).join('');

        medicalTableBody.innerHTML = rowsHtml;
        initMedicalDatePickers();
        renderMedicalPager();

        if (medicalSaveBtn) {
            medicalSaveBtn.disabled = false;
        }
    }

    function showScheduleExamSuccessModal(message) {
        if (scheduleExamSuccessMessageEl) {
            scheduleExamSuccessMessageEl.textContent = message || 'Exam schedule saved successfully.';
        }

        if (!scheduleExamSuccessModalEl) {
            return;
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(scheduleExamSuccessModalEl).show();
            return;
        }

        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery(scheduleExamSuccessModalEl).modal('show');
        }
    }

    function showDocsConfirmation(options) {
        var config = options || {};
        var title = config.title || 'Confirm Action';
        var message = config.message || 'Are you sure you want to continue?';
        var confirmText = config.confirmText || 'Confirm';
        var useDangerStyle = config.danger !== false;

        var hasModalApi = (typeof bootstrap !== 'undefined' && bootstrap.Modal)
            || (window.jQuery && typeof window.jQuery.fn.modal === 'function');

        if (!hasModalApi || !docsConfirmModalEl || !docsConfirmTitleEl || !docsConfirmMessageEl || !docsConfirmActionBtn || !docsConfirmCancelBtn || !docsConfirmCloseBtn) {
            return Promise.resolve(window.confirm(message));
        }

        docsConfirmTitleEl.textContent = title;
        docsConfirmMessageEl.textContent = message;
        docsConfirmActionBtn.textContent = confirmText;
        docsConfirmActionBtn.classList.toggle('is-danger', useDangerStyle);

        return new Promise(function (resolve) {
            var settled = false;

            function hideConfirmModal() {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var modalInstance = bootstrap.Modal.getInstance(docsConfirmModalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    return;
                }

                if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                    window.jQuery(docsConfirmModalEl).modal('hide');
                }
            }

            function cleanup(result) {
                if (settled) {
                    return;
                }

                settled = true;
                docsConfirmActionBtn.removeEventListener('click', onConfirmClick);
                docsConfirmCancelBtn.removeEventListener('click', onCancelClick);
                docsConfirmCloseBtn.removeEventListener('click', onCancelClick);

                if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                    window.jQuery(docsConfirmModalEl).off('hidden.bs.modal.docsConfirm');
                }

                docsConfirmModalEl.removeEventListener('hidden.bs.modal', onHiddenNative);
                resolve(result);
            }

            function onConfirmClick() {
                cleanup(true);
                hideConfirmModal();
            }

            function onCancelClick() {
                cleanup(false);
            }

            function onHiddenNative() {
                cleanup(false);
            }

            docsConfirmActionBtn.addEventListener('click', onConfirmClick);
            docsConfirmCancelBtn.addEventListener('click', onCancelClick);
            docsConfirmCloseBtn.addEventListener('click', onCancelClick);

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                docsConfirmModalEl.addEventListener('hidden.bs.modal', onHiddenNative, { once: true });
                bootstrap.Modal.getOrCreateInstance(docsConfirmModalEl).show();
                return;
            }

            if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery(docsConfirmModalEl)
                    .one('hidden.bs.modal.docsConfirm', onHiddenNative)
                    .modal('show');
                return;
            }

            cleanup(window.confirm(message));
        });
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeApplicationStatus(status) {
        var lowered = String(status || 'In Process').toLowerCase().trim();
        if (lowered === 'submitted' || lowered === 'document submitted') {
            return 'Document Submitted';
        }
        if (lowered === 'on probation' || lowered === 'on_probation') {
            return 'On Probation';
        }
        if (lowered === 'in process' || lowered === 'in_process') {
            return 'In Process';
        }
        if (lowered === 'rejected') {
            return 'Rejected';
        }
        if (lowered === 'incomplete' || lowered === 'draft') {
            return 'Incomplete';
        }
        if (lowered === 'accepted') {
            return 'Accepted';
        }
        return 'In Process';
    }

    function getApplicationStatusClass(status) {
        if (status === 'Rejected') {
            return 'app-status-rejected';
        }
        if (status === 'Accepted' || status === 'Document Submitted' || status === 'In Process') {
            return 'app-status-accepted';
        }
        return 'app-status-pending';
    }

    function normalizeExamResultStatus(status) {
        var lowered = String(status || 'Pending').toLowerCase();
        if (lowered === 'passed') {
            return 'Passed';
        }
        if (lowered === 'failed') {
            return 'Failed';
        }
        return 'Pending';
    }

    function renderStatusChip(row, status) {
        if (!row) {
            return;
        }

        var cell = row.querySelector('.js-application-status');
        if (!cell) {
            return;
        }

        var normalizedStatus = normalizeApplicationStatus(status);
        var statusClass = getApplicationStatusClass(normalizedStatus);

        cell.innerHTML = '<span class="' + statusClass + '"><span class="app-status-dot"></span>' + normalizedStatus.toUpperCase() + '</span>';
    }

    function formatDate(date) {
        var mm = String(date.getMonth() + 1).padStart(2, '0');
        var dd = String(date.getDate()).padStart(2, '0');
        var yyyy = date.getFullYear();
        return mm + '/' + dd + '/' + yyyy;
    }

    function syncApprovalUi() {
        if (!approvalStatusSelect || !approvalBanner || !approvalDateAccepted) {
            return;
        }

        var status = normalizeApplicationStatus(approvalStatusSelect.value);
        approvalStatusSelect.value = status;
        approvalBanner.textContent = 'Application, ' + status;
        if (status === 'Accepted') {
            approvalDateAccepted.value = approvalDateAccepted.value || formatDate(new Date());
        } else {
            approvalDateAccepted.value = '';
        }
    }

    function setApprovalPanelFromRow(row) {
        if (!row) {
            return;
        }

        if (approvalProgramInput) {
            approvalProgramInput.value = row.getAttribute('data-program') || '';
        }

        if (approvalStatusSelect) {
            approvalStatusSelect.value = normalizeApplicationStatus(row.getAttribute('data-application-status'));
        }

        syncApprovalUi();
    }

    function clearApprovalStatusMenuPosition() {
        if (!approvalStatusMenu) {
            return;
        }

        approvalStatusMenu.style.position = '';
        approvalStatusMenu.style.top = '';
        approvalStatusMenu.style.right = '';
        approvalStatusMenu.style.bottom = '';
        approvalStatusMenu.style.left = '';
        approvalStatusMenu.style.width = '';
        approvalStatusMenu.style.maxHeight = '';
        approvalStatusMenu.style.zIndex = '';
        approvalStatusMenu.style.margin = '';
        approvalStatusMenu.style.pointerEvents = '';
    }

    function positionApprovalStatusMenu() {
        if (!approvalStatusWrapper || !approvalStatusMenu) {
            return;
        }

        if (!approvalStatusWrapper.classList.contains('is-open')) {
            clearApprovalStatusMenuPosition();
            return;
        }

        var rect = approvalStatusWrapper.getBoundingClientRect();
        var viewportPadding = 12;
        var availableBelow = window.innerHeight - rect.bottom - viewportPadding;
        var availableAbove = rect.top - viewportPadding;
        var openUpwards = availableBelow < 220 && availableAbove > availableBelow;
        var menuWidth = rect.width;
        var left = rect.left;
        var maxLeft = Math.max(viewportPadding, window.innerWidth - menuWidth - viewportPadding);

        if (left > maxLeft) {
            left = maxLeft;
        }
        if (left < viewportPadding) {
            left = viewportPadding;
        }

        approvalStatusMenu.style.position = 'fixed';
        approvalStatusMenu.style.left = left + 'px';
        approvalStatusMenu.style.width = menuWidth + 'px';
        approvalStatusMenu.style.zIndex = '1300';
        approvalStatusMenu.style.margin = '0';
        approvalStatusMenu.style.pointerEvents = 'auto';
        approvalStatusMenu.style.overflowY = 'auto';
        approvalStatusMenu.style.maxHeight = Math.max(140, Math.min(220, openUpwards ? availableAbove : availableBelow)) + 'px';

        if (openUpwards) {
            approvalStatusMenu.style.top = 'auto';
            approvalStatusMenu.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
        } else {
            approvalStatusMenu.style.bottom = 'auto';
            approvalStatusMenu.style.top = (rect.bottom + 6) + 'px';
        }
    }

    function scheduleApprovalStatusMenuPosition() {
        if (!window.requestAnimationFrame) {
            positionApprovalStatusMenu();
            return;
        }

        window.requestAnimationFrame(positionApprovalStatusMenu);
    }

    function getPayloadErrorMessage(payload, statusCode) {
        var resolvedStatus = Number(statusCode || (payload && payload.status) || 0);

        if (resolvedStatus === 419) {
            return 'Your session has expired. Please refresh the page and try again.';
        }

        if (resolvedStatus === 401 || resolvedStatus === 423) {
            return 'Your session has expired. Please sign in again and retry.';
        }

        if (!payload || typeof payload !== 'object') {
            return 'Unable to save changes right now.';
        }

        if (payload.errors && typeof payload.errors === 'object') {
            var firstKey = Object.keys(payload.errors)[0];
            if (firstKey && payload.errors[firstKey] && payload.errors[firstKey][0]) {
                return payload.errors[firstKey][0];
            }
        }

        if (payload.message) {
            return payload.message;
        }

        return 'Unable to save changes right now.';
    }

    function parseJsonResponseStrict(response, fallbackMessage) {
        var responseUrl = String(response.url || '');
        var contentType = String(response.headers.get('content-type') || '').toLowerCase();
        var isJson = contentType.indexOf('application/json') !== -1 || contentType.indexOf('+json') !== -1;
        var redirectedToLogin = response.redirected && /\/login(?:[/?#]|$)/i.test(responseUrl);

        if (redirectedToLogin) {
            throw {
                status: response.status || 401,
                payload: {
                    status: response.status || 401,
                    message: 'Your session has expired. Please sign in again and retry.'
                }
            };
        }

        function getMedicalDateDisplayInput(dateInput) {
            if (!dateInput) {
                return null;
            }

            if (dateInput._flatpickr && dateInput._flatpickr.altInput) {
                return dateInput._flatpickr.altInput;
            }

            return dateInput;
        }

        function setMedicalDateInvalidState(dateInput, isInvalid) {
            var visibleInput = getMedicalDateDisplayInput(dateInput);
            if (!visibleInput) {
                return;
            }

            visibleInput.classList.toggle('is-invalid', !!isInvalid);
        }

        function initMedicalDatePickers() {
            if (!medicalTableBody) {
                return;
            }

            var dateInputs = medicalTableBody.querySelectorAll('.js-medical-flatpickr-date');
            if (!dateInputs.length) {
                return;
            }

            if (typeof window.flatpickr !== 'function') {
                dateInputs.forEach(function (input) {
                    if (input.dataset.medicalDateFallbackBound === '1') {
                        return;
                    }

                    input.dataset.medicalDateFallbackBound = '1';
                    input.addEventListener('input', function () {
                        setMedicalDateInvalidState(input, false);
                    });
                });
                return;
            }

            dateInputs.forEach(function (input) {
                if (input._flatpickr) {
                    return;
                }

                window.flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'F j, Y',
                    altInputClass: 'apc-input apc-input--date medical-date-input medical-date-display',
                    disableMobile: true,
                    allowInput: false,
                    prevArrow: '&#8249;',
                    nextArrow: '&#8250;',
                    onReady: function onReady(_, __, instance) {
                        instance.input.setAttribute('autocomplete', 'off');
                        instance.calendarContainer.classList.add('an-flatpickr-calendar', 'app-form-flatpickr-theme');

                        if (instance.altInput) {
                            instance.altInput.setAttribute('placeholder', 'Select date');
                            instance.altInput.setAttribute('autocomplete', 'off');
                        }
                    },
                    onChange: function onChange(_, __, instance) {
                        instance.input.dispatchEvent(new Event('input', { bubbles: true }));
                        instance.input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
        }

        function setMedicalFeedback(message, isError) {
            setFeedback(medicalPanelFeedback, message, isError);
        }

        function renderMedicalPager() {
            if (!medicalTablePager) {
                return;
            }

            if (medicalState.lastPage <= 1) {
                medicalTablePager.innerHTML = '';
                return;
            }

            var current = medicalState.page;
            var lastPage = medicalState.lastPage;
            var start = Math.max(1, current - 2);
            var end = Math.min(lastPage, current + 2);

            if (current <= 3) {
                end = Math.min(lastPage, 5);
            } else if (current >= lastPage - 2) {
                start = Math.max(1, lastPage - 4);
            }

            var html = '' +
                '<nav class="cfg-page-nav-wrap" aria-label="Medical clearance pagination">' +
                    '<div class="cfg-page-list" role="group" aria-label="Medical clearance page controls">' +
                        '<button type="button" class="btn cfg-page-btn" data-medical-page="' + (current - 1) + '" ' + (current <= 1 ? 'disabled' : '') + ' aria-label="Previous page">&lt;</button>';

            for (var page = start; page <= end; page += 1) {
                html += '<button type="button" class="btn cfg-page-num ' + (page === current ? 'active' : '') + '" data-medical-page="' + page + '" ' + (page === current ? 'aria-current="page"' : '') + '>' + page + '</button>';
            }

            html += '' +
                        '<button type="button" class="btn cfg-page-btn" data-medical-page="' + (current + 1) + '" ' + (current >= lastPage ? 'disabled' : '') + ' aria-label="Next page">&gt;</button>' +
                    '</div>' +
                '</nav>';

            medicalTablePager.innerHTML = html;
        }

        function renderMedicalRows() {
            if (!medicalTableBody) {
                return;
            }

            if (!medicalState.rows.length) {
                var emptyText = medicalPanel ? medicalPanel.getAttribute('data-empty-text') : 'No medical clearance requirements found.';
                medicalTableBody.innerHTML = '<tr><td colspan="4" class="sc-empty-row">' + escapeHtml(emptyText || 'No medical clearance requirements found.') + '</td></tr>';
                if (medicalSaveBtn) {
                    medicalSaveBtn.disabled = true;
                }
                renderMedicalPager();
                return;
            }

            var rowsHtml = medicalState.rows.map(function (row, index) {
                var requirementId = Number(row.requirement_id || 0);
                var fileInputId = 'medicalFileInput_' + requirementId + '_' + index;

                return '' +
                    '<tr data-requirement-id="' + requirementId + '">' +
                        '<td class="apc-check-cell"><input type="checkbox" class="medical-row-checkbox" ' + (row.is_submitted ? 'checked' : '') + '></td>' +
                        '<td>' + escapeHtml(row.document_type || '') + '</td>' +
                        '<td><input type="text" class="apc-input medical-remarks-input" value="' + escapeHtml(row.remarks || '') + '" placeholder="Type remarks"></td>' +
                        '<td><input type="text" class="apc-input apc-input--date medical-date-input js-medical-flatpickr-date" value="' + escapeHtml(row.date_submitted || '') + '" placeholder="Select date" autocomplete="off"></td>' +
                    '</tr>';
            }).join('');

            medicalTableBody.innerHTML = rowsHtml;
            initMedicalDatePickers();
            renderMedicalPager();

            if (medicalSaveBtn) {
                medicalSaveBtn.disabled = false;
            }
        }

        if (!isJson) {
            return response.text().then(function (rawText) {
                var normalizedBody = String(rawText || '').trim().toLowerCase();
                var looksHtml = normalizedBody.indexOf('<!doctype') === 0 || normalizedBody.indexOf('<html') === 0;

                throw {
                    status: response.status,
                    payload: {
                        status: response.status,
                        message: looksHtml
                            ? 'Request returned an HTML response. Please refresh and sign in again.'
                            : (fallbackMessage || 'Unexpected server response. Please try again.')
                    }
                };
            });
        }

        return response.json().then(function (data) {
            if (!response.ok || (data && (data.ok === false || data.success === false))) {
                throw {
                    status: response.status,
                    payload: data || {
                        status: response.status,
                        message: fallbackMessage || 'Unable to save changes right now.'
                    }
                };
            }

            return data || {};
        }).catch(function (error) {
            if (error && (Object.prototype.hasOwnProperty.call(error, 'payload') || Object.prototype.hasOwnProperty.call(error, 'status'))) {
                throw error;
            }

            throw {
                status: response.status,
                payload: {
                    status: response.status,
                    message: fallbackMessage || 'Unexpected server response. Please try again.'
                }
            };
        });
    }

    function renderExamResultCardFromRow(row) {
        if (!row || !examResultCard || !examResultNoData || !examResultBadge || !examResultScoreLine || !examResultScoreText || !examResultMessage) {
            return;
        }

        var examDate = String(row.getAttribute('data-exam-date') || '').trim();
        var status = normalizeExamResultStatus(row.getAttribute('data-exam-result-status'));
        var score = String(row.getAttribute('data-exam-score') || '').trim();

        if (!examDate) {
            examResultCard.classList.add('is-hidden');
            examResultNoData.classList.remove('is-hidden');
            examResultNoData.textContent = 'No exam result is available yet.';
            return;
        }

        examResultNoData.classList.add('is-hidden');
        examResultCard.classList.remove('is-hidden');

        examResultBadge.className = 'result-status-badge result-' + status.toLowerCase();
        examResultBadge.textContent = status.toUpperCase();

        if (score !== '') {
            examResultScoreLine.classList.remove('is-hidden');
            examResultScoreText.textContent = score;
        } else {
            examResultScoreLine.classList.add('is-hidden');
            examResultScoreText.textContent = '';
        }

        if (status === 'Passed') {
            examResultMessage.textContent = 'Congratulations. Please proceed to admissions requirements processing.';
        } else if (status === 'Failed') {
            examResultMessage.textContent = 'You may contact admissions for guidance on the next application cycle.';
        } else {
            examResultMessage.textContent = 'Your exam has been recorded. Result release is still pending.';
        }
    }

    function sendPut(url, payload) {
        return fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        }).then(function (response) {
            return parseJsonResponseStrict(response, 'Unable to save changes right now.');
        });
    }

    function fillApplicantPanelsFromRow(row) {
        if (!row) {
            return;
        }

        var applicantPk = row.getAttribute('data-pk') || '';

        if (detailApplicantPk) {
            detailApplicantPk.value = applicantPk;
        }
        if (detailApplicantId) {
            detailApplicantId.value = row.getAttribute('data-id') || '';
        }
        if (detailApplicantName) {
            detailApplicantName.value = row.getAttribute('data-name') || '';
        }

        if (scheduleExamDate) {
            scheduleExamDate.value = row.getAttribute('data-exam-date') || '';
        }
        if (scheduleExamTime) {
            scheduleExamTime.value = row.getAttribute('data-exam-time') || '';
        }
        if (scheduleExamVenue) {
            scheduleExamVenue.value = row.getAttribute('data-exam-room') || '';
        }

        if (scheduleExamFeedback) {
            scheduleExamFeedback.textContent = '';
        }

        setApprovalPanelFromRow(row);

        if (appFormEditorFrame) {
            var formUrl = getUrlFromTemplate(formUrlTemplate, applicantPk);
            if (formUrl) {
                appFormEditorFrame.setAttribute('src', formUrl);
                appFormEditorFrame.style.display = 'block';
                if (appFormEditorEmpty) {
                    appFormEditorEmpty.style.display = 'none';
                }
            }
        }

        loadApplicantDocuments(true);
        loadApplicantMedicalClearance(true);

        renderExamResultCardFromRow(row);
    }

    function switchPanel(panelId, title, linkEl) {
        document.querySelectorAll('.applicant-panel').forEach(function (panel) {
            panel.classList.remove('active');
        });
        document.querySelectorAll('.applicant-nav-link').forEach(function (link) {
            link.classList.remove('active');
        });
        var panel = document.getElementById('panel-' + panelId);
        if (panel) {
            panel.classList.add('active');
        }
        if (linkEl) {
            linkEl.classList.add('active');
        }

        pageHeader.textContent = title;

        if (panelId !== 'approval') {
            clearApprovalStatusMenuPosition();
        }

        if (panelId === 'documents-submitted' && selectedApplicantRow) {
            loadApplicantDocuments(false);
        }

        if (panelId === 'medical-clearance' && selectedApplicantRow) {
            loadApplicantMedicalClearance(false);
        }
    }

    function openApplicant(row) {
        selectedApplicantRow = row;
        fillApplicantPanelsFromRow(row);

        if (originalSidebar) {
            originalSidebar.style.display = 'none';
        }
        applicantSidebar.style.display = 'flex';
        appProcessPage.style.display = 'none';
        applicantDetailView.style.display = 'block';

        switchPanel(
            'application-form',
            'APPLICATION FORM',
            document.querySelector('.applicant-nav-link[data-panel="application-form"]')
        );
    }

    function closeApplicant() {
        selectedApplicantRow = null;

        if (detailApplicantPk) {
            detailApplicantPk.value = '';
        }
        if (detailApplicantId) {
            detailApplicantId.value = '';
        }
        if (detailApplicantName) {
            detailApplicantName.value = '';
        }
        if (scheduleExamDate) {
            scheduleExamDate.value = '';
        }
        if (scheduleExamTime) {
            scheduleExamTime.value = '';
        }
        if (scheduleExamVenue) {
            scheduleExamVenue.value = '';
        }
        if (scheduleExamFeedback) {
            setFeedback(scheduleExamFeedback, '', false);
        }

        if (originalSidebar) {
            originalSidebar.style.display = '';
        }
        applicantSidebar.style.display = 'none';
        appProcessPage.style.display = '';
        applicantDetailView.style.display = 'none';
        clearApprovalStatusMenuPosition();
        pageHeader.textContent = 'APPLICATION PROCESS';
    }

    document.querySelectorAll('.applicant-nav-link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            switchPanel(this.getAttribute('data-panel'), this.getAttribute('data-title'), this);
        });
    });

    var backToMainBtn = document.getElementById('backToMainBtn');
    if (backToMainBtn) {
        backToMainBtn.addEventListener('click', closeApplicant);
    }

    if (applicantTableBody) {
        applicantTableBody.addEventListener('click', function (event) {
            var row = event.target.closest ? event.target.closest('tr[data-pk]') : null;
            if (!row || !applicantTableBody.contains(row)) {
                return;
            }

            openApplicant(row);
        });
    }

    bindAutoFilterForm();
    syncApplicantPrintLink();

    initDocumentsSubmittedPanel();
    initMedicalClearancePanel();
    initAddRequirementPanel();
    initApprovalPanel();

    if (saveExamScheduleBtn) {
        saveExamScheduleBtn.addEventListener('click', function () {
            var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
            if (!selectedApplicantRow || !applicantPk) {
                setFeedback(scheduleExamFeedback, 'Select an applicant first.', true);
                return;
            }

            var examDate = scheduleExamDate ? scheduleExamDate.value : '';
            var examTime = scheduleExamTime ? scheduleExamTime.value : '';
            var examRoom = scheduleExamVenue ? scheduleExamVenue.value : '';

            if (!examDate || !examTime || !examRoom) {
                setFeedback(scheduleExamFeedback, 'Date, time, and venue are required.', true);
                return;
            }

            var endpoint = getUrlFromTemplate(examScheduleUrlTemplate, applicantPk);
            if (!endpoint) {
                setFeedback(scheduleExamFeedback, 'Schedule endpoint is not configured.', true);
                return;
            }

            saveExamScheduleBtn.disabled = true;
            sendPut(endpoint, {
                exam_date: examDate,
                exam_time: examTime,
                exam_room: examRoom
            }).then(function (data) {
                saveExamScheduleBtn.disabled = false;

                if (!data || !data.row || typeof data.row !== 'object') {
                    setFeedback(scheduleExamFeedback, 'Unexpected response from the server. Please refresh and try again.', true);
                    return;
                }

                var rowData = data.row;
                selectedApplicantRow.setAttribute('data-exam-date', rowData.exam_date || '');
                selectedApplicantRow.setAttribute('data-exam-time', rowData.exam_time || '');
                selectedApplicantRow.setAttribute('data-exam-room', rowData.exam_room || '');
                selectedApplicantRow.setAttribute('data-exam-result-status', rowData.exam_result_status || 'Pending');
                var successMessage = (data && data.message) ? data.message : 'Exam schedule saved successfully.';
                setFeedback(scheduleExamFeedback, successMessage, false);
                showScheduleExamSuccessModal(successMessage);
            }).catch(function (error) {
                saveExamScheduleBtn.disabled = false;
                setFeedback(scheduleExamFeedback, getPayloadErrorMessage(error.payload || error, error && error.status), true);
            });
        });
    }

    // Exam result panel intentionally mirrors applicant-side display content.

    if (printExamScheduleBtn) {
        printExamScheduleBtn.addEventListener('click', function () {
            window.print();
        });
    }

    function initApprovalPanel() {
        if (!approvalStatusSelect || !approvalDateAccepted || !approvalBanner) {
            return;
        }

        if (approvalStatusTrigger) {
            approvalStatusTrigger.addEventListener('click', scheduleApprovalStatusMenuPosition);
            approvalStatusTrigger.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp' || event.key === 'Enter' || event.key === ' ') {
                    scheduleApprovalStatusMenuPosition();
                }
            });
        }

        if (approvalStatusSelect) {
            approvalStatusSelect.addEventListener('change', clearApprovalStatusMenuPosition);
        }

        document.addEventListener('click', function (event) {
            if (!approvalStatusWrapper || approvalStatusWrapper.contains(event.target)) {
                return;
            }

            clearApprovalStatusMenuPosition();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                clearApprovalStatusMenuPosition();
            }
        });

        var approvalScrollContainer = document.querySelector('main.student-content');
        if (approvalScrollContainer) {
            approvalScrollContainer.addEventListener('scroll', function () {
                if (approvalStatusWrapper && approvalStatusWrapper.classList.contains('is-open')) {
                    scheduleApprovalStatusMenuPosition();
                }
            });
        }

        window.addEventListener('resize', function () {
            if (approvalStatusWrapper && approvalStatusWrapper.classList.contains('is-open')) {
                scheduleApprovalStatusMenuPosition();
            }
        });

        approvalStatusSelect.addEventListener('change', syncApprovalUi);
        syncApprovalUi();

        if (!approvalSaveBtn) {
            return;
        }

        approvalSaveBtn.addEventListener('click', function () {
            var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
            if (!selectedApplicantRow || !applicantPk) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Select an applicant first.', 'warning');
                }
                return;
            }

            var endpoint = getUrlFromTemplate(approvalStatusUrlTemplate, applicantPk);
            if (!endpoint) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Approval endpoint is not configured.', 'error');
                }
                return;
            }

            var selectedStatus = normalizeApplicationStatus(approvalStatusSelect.value);
            approvalSaveBtn.disabled = true;

            sendPut(endpoint, {
                application_status: selectedStatus
            }).then(function (data) {
                approvalSaveBtn.disabled = false;

                var rowData = data && data.row ? data.row : {};
                var nextStatus = normalizeApplicationStatus(rowData.application_status || selectedStatus);
                selectedApplicantRow.setAttribute('data-application-status', nextStatus);
                renderStatusChip(selectedApplicantRow, nextStatus);

                approvalStatusSelect.value = nextStatus;
                syncApprovalUi();

                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast((data && data.message) ? data.message : 'Application status updated successfully.', 'success');
                }
            }).catch(function (error) {
                approvalSaveBtn.disabled = false;
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(getPayloadErrorMessage(error.payload || error, error && error.status), 'error');
                }
            });
        });
    }

    function docsStatusChipHtml(isSubmitted) {
        var statusClass = isSubmitted ? 'app-status-accepted' : 'app-status-pending';
        var statusLabel = isSubmitted ? 'COMPLETED' : 'PENDING';

        return '<span class="' + statusClass + '"><span class="app-status-dot"></span>' + statusLabel + '</span>';
    }

    function docsActionIcon(action) {
        if (action === 'delete') {
            return '<svg class="docs-btn-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 2.5H9.5L12 5V13.5H5Z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M9.5 2.5V5H12" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M7 8.5L10 11.5M10 8.5L7 11.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path></svg>';
        }

        if (action === 'remove') {
            return '<svg class="docs-btn-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M5.5 3.5H10.5M6.5 3.5V2.5H9.5V3.5M4 4.5H12M5 4.5L5.6 13H10.4L11 4.5M7 7V11M9 7V11" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
        }

        return '<svg class="docs-btn-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M3.5 8.5L6.5 11.5L12.5 4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
    }

    function resolveDocsDateInput(dateInput) {
        if (!dateInput) {
            return null;
        }

        if (dateInput.classList && dateInput.classList.contains('js-docs-flatpickr-date')) {
            return dateInput;
        }

        if (dateInput.classList && dateInput.classList.contains('docs-date-display')) {
            var hiddenInput = dateInput.previousElementSibling;
            if (hiddenInput && hiddenInput.classList && hiddenInput.classList.contains('js-docs-flatpickr-date')) {
                return hiddenInput;
            }
        }

        return dateInput;
    }

    function getDocsDateDisplayInput(dateInput) {
        var resolvedInput = resolveDocsDateInput(dateInput);
        if (!resolvedInput) {
            return null;
        }

        if (resolvedInput._flatpickr && resolvedInput._flatpickr.altInput) {
            return resolvedInput._flatpickr.altInput;
        }

        return resolvedInput;
    }

    function setDocsDateInvalidState(dateInput, isInvalid) {
        var visibleInput = getDocsDateDisplayInput(dateInput);
        if (!visibleInput) {
            return;
        }

        visibleInput.classList.toggle('is-invalid', !!isInvalid);
    }

    function syncDocsSaveButtonState(rowNode) {
        if (!rowNode) {
            return;
        }

        var saveButton = rowNode.querySelector('.docs-save-btn');
        if (!saveButton) {
            return;
        }

        var requirementId = Number(rowNode.getAttribute('data-requirement-id') || 0);
        var dateInput = rowNode.querySelector('.js-docs-flatpickr-date') || rowNode.querySelector('.docs-date-input');
        var dateValue = dateInput ? String(dateInput.value || '').trim() : '';

        saveButton.disabled = !(requirementId > 0 && dateValue !== '');
    }

    function initDocsDatePickers() {
        if (!docsTableBody) {
            return;
        }

        var dateInputs = docsTableBody.querySelectorAll('.js-docs-flatpickr-date');
        if (!dateInputs.length) {
            return;
        }

        if (typeof window.flatpickr !== 'function') {
            dateInputs.forEach(function (input) {
                if (input.dataset.docsDateFallbackBound === '1') {
                    return;
                }

                input.dataset.docsDateFallbackBound = '1';
                input.addEventListener('input', function () {
                    setDocsDateInvalidState(input, false);
                    syncDocsSaveButtonState(input.closest('tr'));
                });
            });
            return;
        }

        dateInputs.forEach(function (input) {
            if (input._flatpickr) {
                return;
            }

            window.flatpickr(input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                altInputClass: 'apc-input apc-input--date docs-date-input docs-date-display',
                disableMobile: true,
                allowInput: false,
                prevArrow: '&#8249;',
                nextArrow: '&#8250;',
                onReady: function onReady(_, __, instance) {
                    instance.input.setAttribute('autocomplete', 'off');
                    instance.calendarContainer.classList.add('an-flatpickr-calendar', 'app-form-flatpickr-theme');

                    if (instance.altInput) {
                        instance.altInput.setAttribute('placeholder', 'Select date');
                        instance.altInput.setAttribute('autocomplete', 'off');
                    }
                },
                onChange: function onChange(_, __, instance) {
                    instance.input.dispatchEvent(new Event('input', { bubbles: true }));
                    instance.input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }

    function setDocsFeedback(message, isError) {
        setFeedback(docsPanelFeedback, message, isError);
    }

    function setAddRequirementFeedback(message, isError) {
        setFeedback(addRequirementFeedback, message, isError);
    }

    function syncDocsHeaderCheckbox() {
        if (!docsSelectAll || !docsTableBody) {
            return;
        }

        var rowChecks = docsTableBody.querySelectorAll('.docs-row-checkbox');
        if (!rowChecks.length) {
            docsSelectAll.checked = false;
            docsSelectAll.indeterminate = false;
            return;
        }

        var checkedCount = 0;
        rowChecks.forEach(function (checkbox) {
            if (checkbox.checked) {
                checkedCount += 1;
            }
        });

        docsSelectAll.checked = checkedCount === rowChecks.length;
        docsSelectAll.indeterminate = checkedCount > 0 && checkedCount < rowChecks.length;
    }

    function renderDocsPager() {
        if (!docsTablePager) {
            return;
        }

        if (docsState.lastPage <= 1) {
            docsTablePager.innerHTML = '';
            return;
        }

        var current = docsState.page;
        var lastPage = docsState.lastPage;
        var start = Math.max(1, current - 2);
        var end = Math.min(lastPage, current + 2);

        if (current <= 3) {
            end = Math.min(lastPage, 5);
        } else if (current >= lastPage - 2) {
            start = Math.max(1, lastPage - 4);
        }

        var html = '' +
            '<nav class="cfg-page-nav-wrap" aria-label="Documents pagination">' +
                '<div class="cfg-page-list" role="group" aria-label="Documents page controls">' +
                    '<button type="button" class="btn cfg-page-btn" data-docs-page="' + (current - 1) + '" ' + (current <= 1 ? 'disabled' : '') + ' aria-label="Previous page">&lt;</button>';

        for (var page = start; page <= end; page += 1) {
            html += '<button type="button" class="btn cfg-page-num ' + (page === current ? 'active' : '') + '" data-docs-page="' + page + '" ' + (page === current ? 'aria-current="page"' : '') + '>' + page + '</button>';
        }

        html += '' +
                    '<button type="button" class="btn cfg-page-btn" data-docs-page="' + (current + 1) + '" ' + (current >= lastPage ? 'disabled' : '') + ' aria-label="Next page">&gt;</button>' +
                '</div>' +
            '</nav>';

        docsTablePager.innerHTML = html;
    }

    function renderDocsRows() {
        if (!docsTableBody) {
            return;
        }

        if (!docsState.rows.length) {
            var emptyText = docsPanel ? docsPanel.getAttribute('data-empty-text') : 'No document requirements found.';
            docsTableBody.innerHTML = '<tr><td colspan="7" class="sc-empty-row">' + escapeHtml(emptyText || 'No document requirements found.') + '</td></tr>';
            renderDocsPager();
            syncDocsHeaderCheckbox();
            return;
        }

        var rowsHtml = docsState.rows.map(function (row, index) {
            var isSubmitted = !!row.is_submitted;
            var requirementId = Number(row.requirement_id || 0);
            var hasSubmittedDate = String(row.date_submitted || '').trim() !== '';
            var fileInputId = 'docFileInput_' + requirementId + '_' + index;
            var hasUploadedFile = !!row.file_url;
            var fileLinkHtml = row.file_url
                ? '<a href="' + escapeHtml(row.file_url) + '" target="_blank" rel="noopener">' + escapeHtml(row.file_name || 'View file') + '</a>'
                : '<span>-</span>';
            var saveDisabledAttr = (requirementId > 0 && hasSubmittedDate) ? '' : 'disabled';

            var fileCellHtml = hasUploadedFile
                ? '' +
                    '<div class="docs-file-upload docs-file-upload--readonly">' +
                        '<div class="docs-file-link">' + fileLinkHtml + '</div>' +
                        '<button type="button" class="apc-btn apc-btn--cancel docs-delete-file-btn docs-action-btn" title="Delete uploaded file" aria-label="Delete uploaded file">' + docsActionIcon('delete') + '</button>' +
                    '</div>'
                : '' +
                    '<div class="docs-file-upload">' +
                        '<input type="file" id="' + fileInputId + '" class="doc-file-input" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">' +
                        '<label class="apc-btn apc-btn--ghost docs-upload-btn" for="' + fileInputId + '">Upload File</label>' +
                        '<span class="docs-upload-filename" data-default-text="No file selected">No file selected</span>' +
                    '</div>';

            return '' +
                '<tr data-requirement-id="' + requirementId + '">' +
                    '<td class="apc-check-cell"><input type="checkbox" class="docs-row-checkbox" ' + (isSubmitted ? 'checked' : '') + '></td>' +
                    '<td>' + escapeHtml(row.document_type || '') + '</td>' +
                    '<td><input type="text" class="apc-input docs-remarks-input" value="' + escapeHtml(row.remarks || '') + '" placeholder="Type remarks"></td>' +
                    '<td><input type="text" class="apc-input apc-input--date docs-date-input js-docs-flatpickr-date" value="' + escapeHtml(row.date_submitted || '') + '" placeholder="Select date" autocomplete="off"></td>' +
                    '<td>' + fileCellHtml + '</td>' +
                    '<td class="docs-status-cell">' + docsStatusChipHtml(isSubmitted) + '</td>' +
                    '<td>' +
                        '<div class="docs-row-actions">' +
                            '<button type="button" class="apc-btn apc-btn--save docs-save-btn docs-action-btn" title="Save row" aria-label="Save row" ' + saveDisabledAttr + '>' + docsActionIcon('save') + '</button>' +
                            '<button type="button" class="apc-btn apc-btn--cancel docs-remove-requirement-btn docs-action-btn" title="Remove requirement" aria-label="Remove requirement" ' + (requirementId > 0 ? '' : 'disabled') + '>' + docsActionIcon('remove') + '</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
        }).join('');

        docsTableBody.innerHTML = rowsHtml;
        initDocsDatePickers();
        docsTableBody.querySelectorAll('tr[data-requirement-id]').forEach(function (rowNode) {
            syncDocsSaveButtonState(rowNode);
        });
        renderDocsPager();
        syncDocsHeaderCheckbox();
    }

    function docsRequest(url, options) {
        return fetch(url, options).then(function (response) {
            return parseJsonResponseStrict(response, 'Unable to process the request right now.');
        });
    }

    function loadApplicantDocuments(resetToFirstPage) {
        if (!docsPanel || !docsTableBody) {
            return;
        }

        if (resetToFirstPage) {
            docsState.page = 1;
        }

        var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
        if (!applicantPk) {
            docsState.rows = [];
            docsState.lastPage = 1;
            docsState.total = 0;
            renderDocsRows();
            return;
        }

        var endpoint = getUrlFromTemplate(documentsDataUrlTemplate, applicantPk);
        if (!endpoint) {
            setDocsFeedback('Documents endpoint is not configured.', true);
            return;
        }

        var query = [
            'search=' + encodeURIComponent(docsState.search || ''),
            'status=' + encodeURIComponent(docsState.status || ''),
            'per_page=' + encodeURIComponent(String(docsState.perPage || 10)),
            'page=' + encodeURIComponent(String(docsState.page || 1))
        ].join('&');

        setDocsFeedback('Loading document requirements...', false);

        docsRequest(endpoint + '?' + query, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        }).then(function (payload) {
            docsState.rows = Array.isArray(payload.rows) ? payload.rows : [];

            var meta = payload.meta || {};
            docsState.page = Number(meta.page || docsState.page || 1);
            docsState.lastPage = Number(meta.last_page || 1);
            docsState.perPage = Number(meta.per_page || docsState.perPage || 10);
            docsState.total = Number(meta.total || docsState.rows.length || 0);

            renderDocsRows();
            setDocsFeedback('', false);
        }).catch(function (error) {
            docsState.rows = [];
            docsState.page = 1;
            docsState.lastPage = 1;
            docsState.total = 0;
            renderDocsRows();
            setDocsFeedback(getPayloadErrorMessage(error.payload || error, error && error.status), true);
        });
    }

    function loadApplicantMedicalClearance(resetToFirstPage) {
        if (!medicalPanel || !medicalTableBody) {
            return;
        }

        if (resetToFirstPage) {
            medicalState.page = 1;
        }

        var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
        if (!applicantPk) {
            medicalState.rows = [];
            medicalState.lastPage = 1;
            medicalState.total = 0;
            if (medicalTableBody) {
                medicalTableBody.innerHTML = '<tr><td colspan="4" class="sc-empty-row">Select an applicant to load medical clearance requirements.</td></tr>';
            }
            if (medicalSaveBtn) {
                medicalSaveBtn.disabled = true;
            }
            renderMedicalPager();
            return;
        }

        var endpoint = getMedicalDataUrl(applicantPk);
        if (!endpoint) {
            setMedicalFeedback('Medical clearance endpoint is not configured.', true);
            return;
        }

        var query = [
            'search=' + encodeURIComponent(medicalState.search || ''),
            'requirement_type=Medical',
            'per_page=' + encodeURIComponent(String(medicalState.perPage || 10)),
            'page=' + encodeURIComponent(String(medicalState.page || 1))
        ].join('&');

        setMedicalFeedback('Loading medical clearance requirements...', false);

        docsRequest(endpoint + '?' + query, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        }).then(function (payload) {
            medicalState.rows = Array.isArray(payload.rows) ? payload.rows : [];

            var meta = payload.meta || {};
            medicalState.page = Number(meta.page || medicalState.page || 1);
            medicalState.lastPage = Number(meta.last_page || 1);
            medicalState.perPage = Number(meta.per_page || medicalState.perPage || 10);
            medicalState.total = Number(meta.total || medicalState.rows.length || 0);

            renderMedicalRows();
            setMedicalFeedback('', false);
        }).catch(function (error) {
            medicalState.rows = [];
            medicalState.page = 1;
            medicalState.lastPage = 1;
            medicalState.total = 0;
            renderMedicalRows();
            setMedicalFeedback(getPayloadErrorMessage(error.payload || error, error && error.status), true);
        });
    }

    function saveApplicantMedicalRow(rowNode) {
        var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
        var requirementId = Number(rowNode ? rowNode.getAttribute('data-requirement-id') : 0);

        if (!applicantPk || !requirementId) {
            throw {
                status: 422,
                payload: {
                    message: 'Select an applicant and a medical clearance row before saving.'
                }
            };
        }

        var endpoint = getMedicalUpsertUrl(applicantPk, requirementId);
        if (!endpoint) {
            throw {
                status: 422,
                payload: {
                    message: 'Medical clearance save endpoint is not configured.'
                }
            };
        }

        var checkbox = rowNode.querySelector('.medical-row-checkbox');
        var remarksInput = rowNode.querySelector('.medical-remarks-input');
        var dateInput = rowNode.querySelector('.js-medical-flatpickr-date') || rowNode.querySelector('.medical-date-input');
        var dateSubmitted = dateInput ? String(dateInput.value || '').trim() : '';
        var isSubmitted = !!(checkbox && checkbox.checked);

        if (isSubmitted && !dateSubmitted) {
            setMedicalDateInvalidState(dateInput, true);
            throw {
                status: 422,
                payload: {
                    message: 'Pick a submitted date before saving completed rows.'
                }
            };
        }

        setMedicalDateInvalidState(dateInput, false);

        var formData = new FormData();
        formData.append('is_submitted', isSubmitted ? '1' : '0');
        formData.append('remarks', remarksInput ? remarksInput.value : '');
        formData.append('date_submitted', dateSubmitted);

        return docsRequest(endpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData,
            credentials: 'same-origin'
        });
    }

    function saveMedicalClearanceRows() {
        if (!medicalPanel || !medicalTableBody) {
            return;
        }

        var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
        if (!selectedApplicantRow || !applicantPk) {
            setMedicalFeedback('Select an applicant first.', true);
            return;
        }

        var rowNodes = Array.prototype.slice.call(medicalTableBody.querySelectorAll('tr[data-requirement-id]'));
        if (!rowNodes.length) {
            setMedicalFeedback('No medical clearance requirements to save.', true);
            return;
        }

        var invalidRow = null;
        rowNodes.some(function (rowNode) {
            var checkbox = rowNode.querySelector('.medical-row-checkbox');
            var dateInput = rowNode.querySelector('.js-medical-flatpickr-date') || rowNode.querySelector('.medical-date-input');
            var dateValue = dateInput ? String(dateInput.value || '').trim() : '';

            if (checkbox && checkbox.checked && !dateValue) {
                invalidRow = rowNode;
                setMedicalDateInvalidState(dateInput, true);
                return true;
            }

            setMedicalDateInvalidState(dateInput, false);
            return false;
        });

        if (invalidRow) {
            setMedicalFeedback('Pick a submitted date before saving completed rows.', true);
            var invalidDateInput = invalidRow.querySelector('.js-medical-flatpickr-date') || invalidRow.querySelector('.medical-date-input');
            if (invalidDateInput && invalidDateInput._flatpickr) {
                invalidDateInput._flatpickr.open();
                if (invalidDateInput._flatpickr.altInput) {
                    invalidDateInput._flatpickr.altInput.focus();
                }
            } else if (invalidDateInput) {
                invalidDateInput.focus();
            }
            return;
        }

        if (medicalSaveBtn) {
            medicalSaveBtn.disabled = true;
            medicalSaveBtn.textContent = 'Saving...';
        }

        var saveQueue = Promise.resolve();
        rowNodes.forEach(function (rowNode) {
            saveQueue = saveQueue.then(function () {
                return saveApplicantMedicalRow(rowNode);
            });
        });

        saveQueue.then(function () {
            loadApplicantMedicalClearance(false);
            setMedicalFeedback('Medical clearance records saved successfully.', false);
        }).catch(function (error) {
            setMedicalFeedback(getPayloadErrorMessage(error.payload || error, error && error.status), true);
        }).finally(function () {
            if (medicalSaveBtn) {
                medicalSaveBtn.disabled = false;
                medicalSaveBtn.textContent = 'Save';
            }
        });
    }

    function initMedicalClearancePanel() {
        if (!medicalPanel || !medicalTableBody) {
            return;
        }

        if (medicalSearchInput) {
            medicalSearchInput.addEventListener('input', function () {
                medicalState.search = (medicalSearchInput.value || '').trim();
                medicalState.page = 1;

                if (medicalSearchTimer) {
                    clearTimeout(medicalSearchTimer);
                }

                medicalSearchTimer = setTimeout(function () {
                    loadApplicantMedicalClearance(false);
                }, 350);
            });
        }

        if (medicalPerPage) {
            medicalPerPage.addEventListener('change', function () {
                medicalState.perPage = Number(medicalPerPage.value || 10) || 10;
                medicalState.page = 1;
                loadApplicantMedicalClearance(false);
            });
        }

        if (medicalSaveBtn) {
            medicalSaveBtn.addEventListener('click', function () {
                saveMedicalClearanceRows();
            });
        }

        medicalTableBody.addEventListener('change', function (event) {
            if (event.target && event.target.classList.contains('medical-row-checkbox')) {
                var rowNode = event.target.closest('tr');
                if (!rowNode) {
                    return;
                }

                var dateInput = rowNode.querySelector('.js-medical-flatpickr-date') || rowNode.querySelector('.medical-date-input');
                if (!event.target.checked) {
                    setMedicalDateInvalidState(dateInput, false);
                }
            }

            if (event.target && (event.target.classList.contains('medical-date-input') || event.target.classList.contains('medical-remarks-input'))) {
                if (event.target.classList.contains('medical-date-input')) {
                    setMedicalDateInvalidState(event.target, false);
                }
            }
        });

        medicalTableBody.addEventListener('input', function (event) {
            if (!(event.target && event.target.classList.contains('medical-date-input'))) {
                return;
            }

            setMedicalDateInvalidState(event.target, false);
        });

        if (medicalTablePager) {
            medicalTablePager.addEventListener('click', function (event) {
                var pageButton = event.target.closest('[data-medical-page]');
                if (!pageButton) {
                    return;
                }

                var requestedPage = Number(pageButton.getAttribute('data-medical-page') || 1);
                if (requestedPage < 1 || requestedPage > medicalState.lastPage || requestedPage === medicalState.page) {
                    return;
                }

                medicalState.page = requestedPage;
                loadApplicantMedicalClearance(false);
            });
        }

        renderMedicalRows();
    }

    function saveApplicantDocumentRow(rowNode, saveButton) {
        var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
        var requirementId = Number(rowNode ? rowNode.getAttribute('data-requirement-id') : 0);
        if (!applicantPk || !requirementId) {
            setDocsFeedback('Select an applicant and a document row before saving.', true);
            return;
        }

        var endpoint = getDocumentUpsertUrl(applicantPk, requirementId);
        if (!endpoint) {
            setDocsFeedback('Document save endpoint is not configured.', true);
            return;
        }

        var checkbox = rowNode.querySelector('.docs-row-checkbox');
        var remarksInput = rowNode.querySelector('.docs-remarks-input');
        var dateInput = rowNode.querySelector('.js-docs-flatpickr-date') || rowNode.querySelector('.docs-date-input');
        var fileInput = rowNode.querySelector('.doc-file-input');
        var dateSubmitted = dateInput ? String(dateInput.value || '').trim() : '';

        if (!dateSubmitted) {
            setDocsDateInvalidState(dateInput, true);
            setDocsFeedback('Pick a submitted date before saving this row.', true);

            if (dateInput && dateInput._flatpickr) {
                dateInput._flatpickr.open();
                if (dateInput._flatpickr.altInput) {
                    dateInput._flatpickr.altInput.focus();
                }
            } else if (dateInput) {
                dateInput.focus();
            }

            return;
        }

        setDocsDateInvalidState(dateInput, false);

        var formData = new FormData();
        formData.append('is_submitted', checkbox && checkbox.checked ? '1' : '0');
        formData.append('remarks', remarksInput ? remarksInput.value : '');
        formData.append('date_submitted', dateSubmitted);

        if (fileInput && fileInput.files && fileInput.files[0]) {
            formData.append('document_file', fileInput.files[0]);
        }

        if (saveButton) {
            saveButton.disabled = true;
            saveButton.innerHTML = '<span class="docs-btn-busy">...</span>';
        }

        docsRequest(endpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData,
            credentials: 'same-origin'
        }).then(function (payload) {
            if (payload && payload.row) {
                var updated = false;
                docsState.rows = docsState.rows.map(function (row) {
                    if (Number(row.requirement_id || 0) === Number(payload.row.requirement_id || 0)) {
                        updated = true;
                        return payload.row;
                    }

                    return row;
                });

                if (!updated) {
                    docsState.rows.unshift(payload.row);
                }
            }

            if (fileInput) {
                fileInput.value = '';
            }

            var filenameNode = rowNode ? rowNode.querySelector('.docs-upload-filename') : null;
            if (filenameNode) {
                filenameNode.textContent = filenameNode.getAttribute('data-default-text') || 'No file selected';
            }

            renderDocsRows();
            setDocsFeedback((payload && payload.message) ? payload.message : 'Document record saved successfully.', false);
        }).catch(function (error) {
            setDocsFeedback(getPayloadErrorMessage(error.payload || error, error && error.status), true);
        }).finally(function () {
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.innerHTML = docsActionIcon('save');
            }
        });
    }

    function deleteApplicantDocumentFile(rowNode, deleteButton) {
        var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
        var requirementId = Number(rowNode ? rowNode.getAttribute('data-requirement-id') : 0);
        if (!applicantPk || !requirementId) {
            setDocsFeedback('Select an applicant and a document row before deleting a file.', true);
            return;
        }

        showDocsConfirmation({
            title: 'Delete Uploaded File',
            message: 'This will remove the uploaded document file from this requirement row. Continue?',
            confirmText: 'Delete File',
            danger: true
        }).then(function (confirmed) {
            if (!confirmed) {
                return;
            }

            var endpoint = getDocumentDeleteFileUrl(applicantPk, requirementId);
            if (!endpoint) {
                setDocsFeedback('File delete endpoint is not configured.', true);
                return;
            }

            if (deleteButton) {
                deleteButton.disabled = true;
                deleteButton.innerHTML = '<span class="docs-btn-busy">...</span>';
            }

            docsRequest(endpoint, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            }).then(function (payload) {
                loadApplicantDocuments(false);
                setDocsFeedback((payload && payload.message) ? payload.message : 'Uploaded file deleted successfully.', false);
            }).catch(function (error) {
                setDocsFeedback(getPayloadErrorMessage(error.payload || error, error && error.status), true);
            }).finally(function () {
                if (deleteButton) {
                    deleteButton.disabled = false;
                    deleteButton.innerHTML = docsActionIcon('delete');
                }
            });
        });
    }

    function removeApplicantDocumentRequirement(rowNode, removeButton) {
        var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
        var requirementId = Number(rowNode ? rowNode.getAttribute('data-requirement-id') : 0);
        if (!applicantPk || !requirementId) {
            setDocsFeedback('Select an applicant and a document row before removing a requirement.', true);
            return;
        }

        showDocsConfirmation({
            title: 'Remove Requirement',
            message: 'This will remove this requirement row from the selected applicant. Continue?',
            confirmText: 'Remove Requirement',
            danger: true
        }).then(function (confirmed) {
            if (!confirmed) {
                return;
            }

            var endpoint = getDocumentUnassignUrl(applicantPk, requirementId);
            if (!endpoint) {
                setDocsFeedback('Remove requirement endpoint is not configured.', true);
                return;
            }

            if (removeButton) {
                removeButton.disabled = true;
                removeButton.innerHTML = '<span class="docs-btn-busy">...</span>';
            }

            docsRequest(endpoint, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            }).then(function (payload) {
                loadApplicantDocuments(false);
                setDocsFeedback((payload && payload.message) ? payload.message : 'Requirement removed successfully.', false);
            }).catch(function (error) {
                setDocsFeedback(getPayloadErrorMessage(error.payload || error, error && error.status), true);
            }).finally(function () {
                if (removeButton) {
                    removeButton.disabled = false;
                    removeButton.innerHTML = docsActionIcon('remove');
                }
            });
        });
    }

    function initAddRequirementPanel() {
        if (!addRequirementBtn || !addRequirementModalEl || !newRequirementInput || !newRequirementLevel || !createRequirementBtn) {
            return;
        }

        function openAddRequirementModal() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(addRequirementModalEl).show();
                return;
            }

            if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery(addRequirementModalEl).modal('show');
            }
        }

        function hideAddRequirementModal() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modal = bootstrap.Modal.getInstance(addRequirementModalEl);
                if (modal) {
                    modal.hide();
                }
                return;
            }

            if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery(addRequirementModalEl).modal('hide');
            }
        }

        addRequirementBtn.addEventListener('click', function () {
            var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
            if (!selectedApplicantRow || !applicantPk) {
                setFeedback(docsPanelFeedback, 'Select an applicant first.', true);
                return;
            }

            addRequirementBtn.disabled = true;
            setAddRequirementFeedback('', false);
            newRequirementInput.value = '';
            newRequirementLevel.value = 'All Year Level';
            newRequirementLevel.dispatchEvent(new Event('change'));

            openAddRequirementModal();
            addRequirementBtn.disabled = false;
        });

        createRequirementBtn.addEventListener('click', function () {
            var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
            if (!selectedApplicantRow || !applicantPk) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Select an applicant first.', 'warning');
                }
                return;
            }

            var requirementName = (newRequirementInput.value || '').trim();
            var gradeLevel = String(newRequirementLevel.value || 'All Year Level');

            if (!requirementName) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Enter a requirement name before adding.', 'warning');
                }
                newRequirementInput.focus();
                return;
            }

            var endpoint = getDocumentCreateRequirementUrl(applicantPk);
            if (!endpoint) {
                setAddRequirementFeedback('Create requirement endpoint is not configured.', true);
                return;
            }

            createRequirementBtn.disabled = true;
            var originalCreateText = createRequirementBtn.textContent;
            createRequirementBtn.textContent = 'Adding...';

            docsRequest(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    document: requirementName,
                    grade_level: gradeLevel,
                    doc_type: 'Document'
                }),
                credentials: 'same-origin'
            }).then(function (payload) {
                hideAddRequirementModal();
                loadApplicantDocuments(false);
                setAddRequirementFeedback('', false);

                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast((payload && payload.message) ? payload.message : 'Requirement added successfully.', 'success');
                }
            }).catch(function (error) {
                var msg = getPayloadErrorMessage(error.payload || error, error && error.status);
                setAddRequirementFeedback(msg, true);
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(msg, 'error');
                }
            }).finally(function () {
                createRequirementBtn.disabled = false;
                createRequirementBtn.textContent = originalCreateText || 'Add Requirement';
            });
        });
    }

    function initDocumentsSubmittedPanel() {
        if (!docsPanel || !docsTableBody) {
            return;
        }

        if (docsPerPage) {
            docsState.perPage = Number(docsPerPage.value || 10) || 10;
        }
        if (docsStatusFilter) {
            docsState.status = String(docsStatusFilter.value || '').toLowerCase();
        }

        if (docsSearchInput) {
            docsSearchInput.addEventListener('input', function () {
                docsState.search = (docsSearchInput.value || '').trim();
                docsState.page = 1;

                if (docsSearchTimer) {
                    clearTimeout(docsSearchTimer);
                }

                docsSearchTimer = setTimeout(function () {
                    loadApplicantDocuments(false);
                }, 350);
            });
        }

        if (docsStatusFilter) {
            docsStatusFilter.addEventListener('change', function () {
                docsState.status = String(docsStatusFilter.value || '').toLowerCase();
                docsState.page = 1;
                loadApplicantDocuments(false);
            });
        }

        if (docsPerPage) {
            docsPerPage.addEventListener('change', function () {
                docsState.perPage = Number(docsPerPage.value || 10) || 10;
                docsState.page = 1;
                loadApplicantDocuments(false);
            });
        }

        if (docsSelectAll) {
            docsSelectAll.addEventListener('change', function () {
                docsTableBody.querySelectorAll('.docs-row-checkbox').forEach(function (checkbox) {
                    checkbox.checked = docsSelectAll.checked;
                    var rowNode = checkbox.closest('tr');
                    if (rowNode) {
                        var statusCell = rowNode.querySelector('.docs-status-cell');
                        if (statusCell) {
                            statusCell.innerHTML = docsStatusChipHtml(checkbox.checked);
                        }
                    }
                });

                syncDocsHeaderCheckbox();
            });
        }

        docsTableBody.addEventListener('change', function (event) {
            if (event.target && event.target.classList.contains('docs-row-checkbox')) {
                var rowNode = event.target.closest('tr');
                if (!rowNode) {
                    return;
                }

                var statusCell = rowNode.querySelector('.docs-status-cell');
                if (statusCell) {
                    statusCell.innerHTML = docsStatusChipHtml(event.target.checked);
                }

                syncDocsHeaderCheckbox();
                return;
            }

            if (event.target && event.target.classList.contains('docs-date-input')) {
                var dateRowNode = event.target.closest('tr');
                setDocsDateInvalidState(event.target, false);
                syncDocsSaveButtonState(dateRowNode);
                return;
            }

            if (event.target && event.target.classList.contains('doc-file-input')) {
                var fileRowNode = event.target.closest('tr');
                if (!fileRowNode) {
                    return;
                }

                var filenameNode = fileRowNode.querySelector('.docs-upload-filename');
                if (!filenameNode) {
                    return;
                }

                var defaultText = filenameNode.getAttribute('data-default-text') || 'No file selected';
                var selectedFile = event.target.files && event.target.files[0] ? event.target.files[0].name : '';
                filenameNode.textContent = selectedFile || defaultText;
            }
        });

        docsTableBody.addEventListener('input', function (event) {
            if (!(event.target && event.target.classList.contains('docs-date-input'))) {
                return;
            }

            setDocsDateInvalidState(event.target, false);
            syncDocsSaveButtonState(event.target.closest('tr'));
        });

        docsTableBody.addEventListener('click', function (event) {
            if (!event.target) {
                return;
            }

            var saveBtn = event.target.closest('.docs-save-btn');
            if (saveBtn) {
                var saveRowNode = saveBtn.closest('tr');
                if (!saveRowNode) {
                    return;
                }
                saveApplicantDocumentRow(saveRowNode, saveBtn);
                return;
            }

            var deleteBtn = event.target.closest('.docs-delete-file-btn');
            if (deleteBtn) {
                var deleteRowNode = deleteBtn.closest('tr');
                if (!deleteRowNode) {
                    return;
                }
                deleteApplicantDocumentFile(deleteRowNode, deleteBtn);
                return;
            }

            var removeBtn = event.target.closest('.docs-remove-requirement-btn');
            if (removeBtn) {
                var removeRowNode = removeBtn.closest('tr');
                if (!removeRowNode) {
                    return;
                }
                removeApplicantDocumentRequirement(removeRowNode, removeBtn);
            }
        });

        if (docsTablePager) {
            docsTablePager.addEventListener('click', function (event) {
                var pageButton = event.target.closest('[data-docs-page]');
                if (!pageButton) {
                    return;
                }

                var requestedPage = Number(pageButton.getAttribute('data-docs-page') || 1);
                if (requestedPage < 1 || requestedPage > docsState.lastPage || requestedPage === docsState.page) {
                    return;
                }

                docsState.page = requestedPage;
                loadApplicantDocuments(false);
            });
        }

        renderDocsRows();
    }
})();
