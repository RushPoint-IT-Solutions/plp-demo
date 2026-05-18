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
    var formUrlTemplate = appProcessPage.getAttribute('data-form-url-template') || '';

    var applicationFilterForm = document.getElementById('applicationFilterForm');
    var appSearchInput = document.getElementById('appSearch');
    var filterSubmitTimer = null;
    var filterIsSubmitting = false;

    var detailApplicantPk = document.getElementById('detailApplicantPk');
    var detailApplicantId = document.getElementById('detailApplicantId');
    var detailApplicantName = document.getElementById('detailApplicantName');

    var scheduleExamDate = document.getElementById('scheduleExamDate');
    var scheduleExamTime = document.getElementById('scheduleExamTime');
    var scheduleExamVenue = document.getElementById('scheduleExamVenue');
    var scheduleExamFeedback = document.getElementById('scheduleExamFeedback');
    var saveExamScheduleBtn = document.getElementById('saveExamScheduleBtn');
    var printExamScheduleBtn = document.getElementById('printExamScheduleBtn');

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

    var selectedApplicantRow = null;
    var isSyncingApprovalUi = false;

    function submitFilterForm() {
        if (!applicationFilterForm || filterIsSubmitting) {
            return;
        }

        filterIsSubmitting = true;

        // Show centered loading circle overlay
        const tableLoadingOverlay = document.getElementById('tableLoadingOverlay');
        if (tableLoadingOverlay) {
            tableLoadingOverlay.style.display = 'flex';
        }

        var pageInput = applicationFilterForm.querySelector('input[name="page"]');
        if (pageInput) {
            pageInput.value = '1';
        }

        applicationFilterForm.submit();
    }

    function bindAutoFilterForm() {
        if (!applicationFilterForm) {
            return;
        }

        applicationFilterForm.querySelectorAll('select, input[type="date"]').forEach(function (field) {
            field.addEventListener('change', function () {
                if (filterSubmitTimer) {
                    clearTimeout(filterSubmitTimer);
                    filterSubmitTimer = null;
                }
                submitFilterForm();
            });
        });

        if (!appSearchInput) {
            return;
        }

        appSearchInput.addEventListener('input', function () {
            if (filterSubmitTimer) {
                clearTimeout(filterSubmitTimer);
            }

            filterSubmitTimer = setTimeout(function () {
                submitFilterForm();
            }, 550);
        });

        appSearchInput.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            if (filterSubmitTimer) {
                clearTimeout(filterSubmitTimer);
                filterSubmitTimer = null;
            }
            submitFilterForm();
        });
    }

    function getUrlFromTemplate(template, applicantPk) {
        if (!template || !applicantPk) {
            return '';
        }

        return template.replace('__APPLICANT_ID__', String(applicantPk));
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

        if (isSyncingApprovalUi) {
            return;
        }

        isSyncingApprovalUi = true;

        var status = normalizeApplicationStatus(approvalStatusSelect.value);
        approvalStatusSelect.value = status;
        
        // Dispatch change event to notify the custom listbox component to update its trigger text
        approvalStatusSelect.dispatchEvent(new Event('change', { bubbles: true }));

        var bannerText = document.getElementById('approvalBannerText');
        if (bannerText) {
            bannerText.textContent = 'Application status set to: ' + status;
        } else {
            approvalBanner.textContent = 'Application, ' + status;
        }

        if (status === 'Accepted') {
            approvalDateAccepted.value = approvalDateAccepted.value || formatDate(new Date());
        } else {
            approvalDateAccepted.value = '';
        }

        isSyncingApprovalUi = false;
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

    function getPayloadErrorMessage(payload) {
        if (!payload || typeof payload !== 'object') {
            return 'Unable to save changes right now.';
        }

        if (payload.message) {
            return payload.message;
        }

        if (payload.errors && typeof payload.errors === 'object') {
            var firstKey = Object.keys(payload.errors)[0];
            if (firstKey && payload.errors[firstKey] && payload.errors[firstKey][0]) {
                return payload.errors[firstKey][0];
            }
        }

        return 'Unable to save changes right now.';
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
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                if (!response.ok) {
                    throw { payload: data };
                }
                return data;
            });
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
        if (originalSidebar) {
            originalSidebar.style.display = '';
        }
        applicantSidebar.style.display = 'none';
        appProcessPage.style.display = '';
        applicantDetailView.style.display = 'none';
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

    document.querySelectorAll('#appProcessPage .app-table tbody tr[data-pk]').forEach(function (row) {
        row.addEventListener('click', function () {
            openApplicant(this);
        });
    });

    bindAutoFilterForm();

    initDocumentsSubmittedPanel();
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

                var rowData = data && data.row ? data.row : {};
                selectedApplicantRow.setAttribute('data-exam-date', rowData.exam_date || examDate);
                selectedApplicantRow.setAttribute('data-exam-time', rowData.exam_time || examTime);
                selectedApplicantRow.setAttribute('data-exam-room', rowData.exam_room || examRoom);
                selectedApplicantRow.setAttribute('data-exam-result-status', rowData.exam_result_status || 'Pending');
                setFeedback(scheduleExamFeedback, (data && data.message) ? data.message : 'Exam schedule saved successfully.', false);
            }).catch(function (error) {
                saveExamScheduleBtn.disabled = false;
                setFeedback(scheduleExamFeedback, getPayloadErrorMessage(error.payload || error), true);
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

        approvalStatusSelect.addEventListener('change', syncApprovalUi);
        syncApprovalUi();

        // Initialize Flatpickr for the decision date
        if (typeof flatpickr === 'function' && document.querySelector('.js-flatpickr-approval')) {
            flatpickr('.js-flatpickr-approval', {
                dateFormat: 'm/d/Y',
                allowInput: true,
                monthSelectorType: 'static'
            });
        }

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
                    showRegistrarToast(getPayloadErrorMessage(error.payload || error), 'error');
                }
            });
        });
    }

    function initDocumentsSubmittedPanel() {
        var header = document.getElementById('docsSelectAll');
        var items = document.querySelectorAll('.docs-row-checkbox');
        if (!header || !items.length) return;

        function syncHeader() {
            var checked = 0;
            items.forEach(function (cb) {
                if (cb.checked) checked++;
            });
            header.checked = checked === items.length;
            header.indeterminate = checked > 0 && checked < items.length;
        }

        header.addEventListener('change', function () {
            items.forEach(function (cb) {
                cb.checked = header.checked;
            });
            syncHeader();
        });

        items.forEach(function (cb) {
            cb.addEventListener('change', syncHeader);
        });

        syncHeader();

        // File upload feedback
        document.querySelectorAll('.js-doc-file-input').forEach(function(input) {
            input.addEventListener('change', function() {
                var label = this.closest('.apc-upload-btn');
                var span = label.querySelector('span');
                if (this.files && this.files.length > 0) {
                    var fileName = this.files[0].name;
                    span.textContent = fileName.length > 12 ? fileName.substring(0, 10) + '...' : fileName;
                    label.style.backgroundColor = '#006837';
                    label.style.color = '#fff';
                    label.title = fileName;
                } else {
                    span.textContent = 'Upload';
                    label.style.backgroundColor = '';
                    label.style.color = '';
                    label.title = 'Upload scanned copy';
                }
            });
        });
    }

    // Application Status Management Interactivity
    function initStatusManagement() {
        var updateStatusSelect = document.getElementById('updateStatusSelect');
        var currentStatusBadge = document.getElementById('currentStatusBadge');
        var saveStatusBtn = document.querySelector('.status-management-card .apc-btn--save');

        if (!updateStatusSelect || !currentStatusBadge) return;

        updateStatusSelect.addEventListener('change', function() {
            var status = this.value;
            var statusClass = 'status-submitted';
            
            if (status === 'ACCEPTED') statusClass = 'status-approved';
            else if (status === 'REJECTED') statusClass = 'status-not-submitted';
            else if (status === 'ON PROCESS') statusClass = 'status-review';

            // Optional: Update badge preview in real-time for demo
            currentStatusBadge.textContent = status;
            currentStatusBadge.className = 'mc-item-status ' + statusClass;
        });

        if (saveStatusBtn) {
            saveStatusBtn.addEventListener('click', function() {
                var status = updateStatusSelect.value;
                var remarks = document.getElementById('statusRemarksInput').value;
                
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Application status updated to ' + status + ' successfully.', 'success');
                } else {
                    alert('Status updated to ' + status + (remarks ? ' with remarks: ' + remarks : ''));
                }
            });
        }
    }

    initStatusManagement();

    function initPanelFilters() {
        // Medical Clearance Filters
        var mcSearch = document.getElementById('mcSearch');
        var mcStatus = document.getElementById('mcStatusFilter');
        var mcTable = document.getElementById('mcTable');

        if (mcTable) {
            var mcRows = mcTable.querySelectorAll('tbody tr');
            var filterMc = function() {
                var query = mcSearch.value.toLowerCase();
                var status = mcStatus.value.toLowerCase();
                
                mcRows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    var rowStatus = row.getAttribute('data-status') || '';
                    if (!rowStatus) {
                        var statusCell = row.querySelector('.js-mc-status');
                        rowStatus = statusCell ? statusCell.textContent.trim().toLowerCase() : '';
                    } else {
                        rowStatus = rowStatus.toLowerCase();
                    }

                    var matchesSearch = text.indexOf(query) > -1;
                    var matchesStatus = status === '' || rowStatus === status || (status === 'verified' && rowStatus === 'approved');

                    row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
                });
            };

            if (mcSearch) mcSearch.addEventListener('input', filterMc);
            if (mcStatus) mcStatus.addEventListener('change', filterMc);
        }

        // Documents Submitted Filters
        var docSearch = document.getElementById('docSearch');
        var docStatus = document.getElementById('docStatusFilter');
        
        // Let's re-query based on panel
        var docPanel = document.getElementById('panel-documents-submitted');
        if (docPanel) {
            var docRows = docPanel.querySelectorAll('.app-table tbody tr');
            var filterDoc = function() {
                var query = docSearch.value.toLowerCase();
                var status = docStatus.value.toLowerCase();

                docRows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    var rowStatus = row.getAttribute('data-status') || '';
                    if (!rowStatus) {
                        var statusCell = row.querySelector('.app-status-pill, span[class*="status-"]');
                        rowStatus = statusCell ? statusCell.textContent.trim().toLowerCase() : '';
                    } else {
                        rowStatus = rowStatus.toLowerCase();
                    }

                    var matchesSearch = text.indexOf(query) > -1;
                    var matchesStatus = status === '' || rowStatus.indexOf(status) > -1;

                    row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
                });
            };

            if (docSearch) docSearch.addEventListener('input', filterDoc);
            if (docStatus) docStatus.addEventListener('change', filterDoc);
        }
    }

    initPanelFilters();

    // --- Application List Enhancements (Bulk Selection & Exports) ---

    const selectAllApplicants = document.getElementById('selectAllApplicants');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const bulkSelectCount = document.getElementById('bulkSelectCount');
    const tableLoadingIndicator = document.getElementById('tableLoadingIndicator');
    const exportExcelBtn = document.getElementById('exportExcelBtn');
    const exportPdfBtn = document.getElementById('exportPdfBtn');

    const bulkUpdateStatusBtn = document.getElementById('bulkUpdateStatusBtn');
    const bulkStatusSelect = document.getElementById('bulkStatusSelect');
    const bulkCancelBtn = document.getElementById('bulkCancelBtn');

    function updateBulkBar() {
        if (!bulkActionBar || !bulkSelectCount) return;
        const checkedBoxes = document.querySelectorAll('.applicant-row-checkbox:checked');
        const count = checkedBoxes.length;
        
        bulkSelectCount.textContent = count;
        if (count > 0) {
            bulkActionBar.style.display = 'inline-flex';
        } else {
            bulkActionBar.style.display = 'none';
            if (selectAllApplicants) selectAllApplicants.checked = false;
            if (bulkStatusSelect) {
                bulkStatusSelect.value = '';
                // Sync custom listbox UI if available
                if (window.registrarListboxSelect) {
                    window.registrarListboxSelect.refresh(bulkStatusSelect);
                }
            }
        }
    }

    if (bulkCancelBtn) {
        bulkCancelBtn.addEventListener('click', function() {
            if (selectAllApplicants) {
                selectAllApplicants.checked = false;
                selectAllApplicants.indeterminate = false;
            }
            document.querySelectorAll('.applicant-row-checkbox').forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    if (bulkUpdateStatusBtn) {
        bulkUpdateStatusBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.applicant-row-checkbox:checked');
            const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);
            const newStatus = bulkStatusSelect ? bulkStatusSelect.value : '';

            if (selectedIds.length === 0) return;
            if (!newStatus) {
                alert('Please select a new status to apply to the selected applicants.');
                return;
            }

            // Show Confirmation Modal
            const confirmCount = document.getElementById('confirmCount');
            const confirmStatus = document.getElementById('confirmStatus');
            
            if (confirmCount && confirmStatus) {
                confirmCount.textContent = selectedIds.length;
                confirmStatus.textContent = newStatus;
                
                // Store pending data for execution on the modal element itself
                const modalEl = document.getElementById('statusConfirmModal');
                modalEl._pendingIds = selectedIds;
                modalEl._pendingStatus = newStatus;

                // Show Bootstrap Modal
                if (window.jQuery) {
                    $(modalEl).modal('show');
                } else if (window.bootstrap) {
                    new bootstrap.Modal(modalEl).show();
                }
            }
        });
    }

    if (executeBulkUpdate) {
        executeBulkUpdate.addEventListener('click', function() {
            const statusConfirmModal = document.getElementById('statusConfirmModal');
            const ids = statusConfirmModal._pendingIds;
            const status = statusConfirmModal._pendingStatus;

            executeBulkUpdate.disabled = true;
            executeBulkUpdate.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const bulkUpdateUrl = appProcessPage.getAttribute('data-bulk-update-url');

            fetch(bulkUpdateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids, status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide confirmation modal
                    if (window.jQuery) {
                        $(statusConfirmModal).modal('hide');
                    } else if (window.bootstrap) {
                        const modalInstance = bootstrap.Modal.getInstance(statusConfirmModal);
                        if (modalInstance) modalInstance.hide();
                    }

                    // Show success modal
                    const successModalEl = document.getElementById('statusSuccessModal');
                    if (window.jQuery) {
                        $(successModalEl).modal('show');
                    } else if (window.bootstrap) {
                        new bootstrap.Modal(successModalEl).show();
                    }
                } else {
                    alert('Error: ' + data.message);
                    executeBulkUpdate.disabled = false;
                    executeBulkUpdate.textContent = 'Confirm Update';
                }
            })
            .catch(error => {
                console.error('Bulk Update Error:', error);
                alert('An unexpected error occurred. Please try again.');
                executeBulkUpdate.disabled = false;
                executeBulkUpdate.textContent = 'Confirm Update';
            });
        });
    }

    if (reloadAfterSuccess) {
        reloadAfterSuccess.addEventListener('click', () => {
            window.location.reload();
        });
    }

    // --- Convert to Student ---
    const bulkConvertToStudentBtn = document.getElementById('bulkConvertToStudentBtn');
    const executeConvertToStudent = document.getElementById('executeConvertToStudent');
    const reloadAfterConvert      = document.getElementById('reloadAfterConvert');
    const copyAllEmailsBtn        = document.getElementById('copyAllEmailsBtn');

    function showModal(el) {
        if (!el) return;
        if (window.jQuery) { $(el).modal('show'); }
        else if (window.bootstrap) { new bootstrap.Modal(el).show(); }
    }
    function hideModal(el) {
        if (!el) return;
        if (window.jQuery) { $(el).modal('hide'); }
        else if (window.bootstrap) { const m = bootstrap.Modal.getInstance(el); if (m) m.hide(); }
    }

    /** Render the results table inside the result modal */
    function renderConvertResults(data) {
        const summary = document.getElementById('convertResultSummary');
        const tbody   = document.getElementById('convertResultBody');
        if (!summary || !tbody) return;

        summary.textContent = data.message || 'Conversion complete.';
        tbody.innerHTML = '';

        const results = Array.isArray(data.results) ? data.results : [];
        if (results.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="padding:24px; text-align:center; color:#94a3b8; font-size:0.84rem;">No new students were created.</td></tr>';
            return;
        }

        results.forEach(function (r, idx) {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f0f4f2';
            tr.innerHTML =
                '<td style="padding:10px 20px; font-size:0.82rem; font-weight:600; color:#1e293b;">' + escHtml(r.name) + '</td>' +
                '<td style="padding:10px 14px;">' +
                    '<code style="font-size:0.78rem; background:#f1f5f9; border-radius:4px; padding:2px 7px; color:#334155;">' + escHtml(r.student_no) + '</code>' +
                '</td>' +
                '<td style="padding:10px 14px;">' +
                    '<span style="font-size:0.8rem; color:#15803d; font-weight:600;">' + escHtml(r.email) + '</span>' +
                '</td>' +
                '<td style="padding:10px 14px;">' +
                    '<button type="button" data-email="' + escHtml(r.email) + '" class="ctr-copy-email-btn" title="Copy email" ' +
                    'style="border:none; background:#f0fdf4; border-radius:6px; padding:4px 8px; cursor:pointer; color:#15803d; font-size:0.72rem; font-weight:700; white-space:nowrap;">' +
                    'Copy</button>' +
                '</td>';
            tbody.appendChild(tr);
        });

        // Wire individual copy buttons
        tbody.querySelectorAll('.ctr-copy-email-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                copyText(btn.getAttribute('data-email'), btn);
            });
        });
    }

    function copyText(text, triggerEl) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function () { flashCopied(triggerEl); });
        } else {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            flashCopied(triggerEl);
        }
    }

    function flashCopied(el) {
        if (!el) return;
        const orig = el.textContent;
        el.textContent = 'Copied!';
        el.style.background = '#dcfce7';
        setTimeout(function () { el.textContent = orig; el.style.background = ''; }, 1500);
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    if (bulkConvertToStudentBtn) {
        bulkConvertToStudentBtn.addEventListener('click', function () {
            const checkedBoxes = document.querySelectorAll('.applicant-row-checkbox:checked');
            const selectedIds  = Array.from(checkedBoxes).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            const countEl = document.getElementById('convertCount');
            if (countEl) countEl.textContent = selectedIds.length;

            const convertModal = document.getElementById('convertToStudentModal');
            if (!convertModal) return;
            convertModal._pendingIds = selectedIds;
            showModal(convertModal);
        });
    }

    if (executeConvertToStudent) {
        executeConvertToStudent.addEventListener('click', function () {
            const convertModal = document.getElementById('convertToStudentModal');
            const ids = convertModal ? convertModal._pendingIds : null;
            if (!ids || ids.length === 0) return;

            executeConvertToStudent.disabled = true;
            executeConvertToStudent.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:14px;height:14px;border-width:2px;"></span> Converting...';

            const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const convertUrl = appProcessPage.getAttribute('data-bulk-convert-url');

            fetch(convertUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ ids: ids })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                hideModal(convertModal);

                renderConvertResults(data);

                const resultModal = document.getElementById('convertToStudentResultModal');
                showModal(resultModal);
            })
            .catch(function () {
                alert('An unexpected error occurred during conversion. Please try again.');
                executeConvertToStudent.disabled = false;
                executeConvertToStudent.textContent = 'Confirm Convert';
            });
        });
    }

    if (copyAllEmailsBtn) {
        copyAllEmailsBtn.addEventListener('click', function () {
            const emails = Array.from(document.querySelectorAll('#convertResultBody .ctr-copy-email-btn'))
                .map(function (btn) { return btn.getAttribute('data-email'); })
                .filter(Boolean)
                .join('\n');
            if (emails) copyText(emails, copyAllEmailsBtn);
        });
    }

    if (reloadAfterConvert) {
        reloadAfterConvert.addEventListener('click', function () {
            window.location.reload();
        });
    }

    if (selectAllApplicants) {
        selectAllApplicants.addEventListener('click', function(e) {
            e.stopPropagation();
            const isChecked = this.checked;
            document.querySelectorAll('.applicant-row-checkbox').forEach(cb => {
                cb.checked = isChecked;
            });
            updateBulkBar();
        });
    }

    // Delegation for checkboxes to handle dynamic content (if any)
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('applicant-row-checkbox')) {
            updateBulkBar();
            
            // Update Select All state based on individual checkboxes
            if (selectAllApplicants) {
                const total = document.querySelectorAll('.applicant-row-checkbox').length;
                const checked = document.querySelectorAll('.applicant-row-checkbox:checked').length;
                selectAllApplicants.checked = total > 0 && total === checked;
                selectAllApplicants.indeterminate = checked > 0 && checked < total;
            }
        }
    });

    function handleExport(btn, format) {
        if (!btn || !applicationFilterForm) return;
        
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Generating...`;
        
        const formData = new FormData(applicationFilterForm);
        const params = new URLSearchParams(formData);
        params.set('format', format);
        params.set('export', '1');
        
        // Use the print route as the basis for the export
        const printUrl = appProcessPage.getAttribute('data-print-url');
        const exportUrl = `${printUrl}?${params.toString()}`;
        
        if (format === 'pdf') {
            // Open PDF in a new tab for display/preview
            window.open(exportUrl, '_blank');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            return;
        }

        // Trigger download using a hidden iframe for Excel/CSV
        let exportFrame = document.getElementById('export_frame');
        if (!exportFrame) {
            exportFrame = document.createElement('iframe');
            exportFrame.id = 'export_frame';
            exportFrame.style.display = 'none';
            document.body.appendChild(exportFrame);
        }
        exportFrame.src = exportUrl;
        
        // Restore button state after a delay
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }, 2500);
    }

    if (exportExcelBtn) exportExcelBtn.addEventListener('click', () => handleExport(exportExcelBtn, 'excel'));
    if (exportPdfBtn) exportPdfBtn.addEventListener('click', () => handleExport(exportPdfBtn, 'pdf'));

    // --- Saved Views System ---
    const savedViewsContainer = document.getElementById('savedViewsContainer');
    const saveCurrentViewBtn = document.getElementById('saveCurrentViewBtn');
    const VIEWS_STORAGE_KEY = 'plp_registrar_app_views';

    function getSavedViews() {
        const stored = localStorage.getItem(VIEWS_STORAGE_KEY);
        return stored ? JSON.parse(stored) : [];
    }

    function saveView(name, filters) {
        const views = getSavedViews();
        views.push({ id: Date.now(), name: name, filters: filters });
        localStorage.setItem(VIEWS_STORAGE_KEY, JSON.stringify(views));
        renderSavedViews();
    }

    function deleteView(id) {
        const views = getSavedViews().filter(v => v.id !== id);
        localStorage.setItem(VIEWS_STORAGE_KEY, JSON.stringify(views));
        renderSavedViews();
    }

    function renderSavedViews() {
        const views = getSavedViews();
        if (!savedViewsContainer) return;

        if (views.length === 0) {
            savedViewsContainer.style.display = 'none';
            return;
        }

        savedViewsContainer.style.display = 'flex';
        savedViewsContainer.innerHTML = '';

        views.forEach(view => {
            const chip = document.createElement('div');
            chip.className = 'view-chip';
            chip.innerHTML = `
                <span>${view.name}</span>
                <span class="view-chip-delete" data-id="${view.id}">&times;</span>
            `;

            chip.addEventListener('click', (e) => {
                if (e.target.classList.contains('view-chip-delete')) {
                    e.stopPropagation();
                    deleteView(view.id);
                    return;
                }
                applyView(view.filters);
            });

            savedViewsContainer.appendChild(chip);
        });
    }

    function applyView(filters) {
        if (!applicationFilterForm) return;
        
        // Reset form first
        applicationFilterForm.reset();

        // Apply saved values
        for (const [key, value] of Object.entries(filters)) {
            const field = applicationFilterForm.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = value;
                // Trigger change for custom listboxes
                if (window.registrarListboxSelect) {
                    window.registrarListboxSelect.refresh(field);
                }
            }
        }

        submitFilterForm();
    }

    if (saveCurrentViewBtn) {
        saveCurrentViewBtn.addEventListener('click', function() {
            const modalEl = document.getElementById('saveViewModal');
            if (!modalEl) return;
            
            const inputEl = document.getElementById('saveViewNameInput');
            if (inputEl) inputEl.value = '';

            modalEl.style.display = 'flex';
        });
    }

    const confirmSaveViewBtn = document.getElementById('confirmSaveViewBtn');
    if (confirmSaveViewBtn) {
        confirmSaveViewBtn.addEventListener('click', function() {
            const inputEl = document.getElementById('saveViewNameInput');
            const viewName = inputEl ? inputEl.value.trim() : '';
            if (!viewName) {
                alert('Please enter a view name.');
                return;
            }

            const formData = new FormData(applicationFilterForm);
            const filters = {};
            formData.forEach((value, key) => {
                filters[key] = value;
            });

            saveView(viewName, filters);

            const modalEl = document.getElementById('saveViewModal');
            if (modalEl) {
                modalEl.style.display = 'none';
            }
        });
    }

    // Initialize Views
    renderSavedViews();
})();
