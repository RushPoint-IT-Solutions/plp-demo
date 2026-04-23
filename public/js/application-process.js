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

    function submitFilterForm() {
        if (!applicationFilterForm || filterIsSubmitting) {
            return;
        }

        filterIsSubmitting = true;

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

    // Removed outdated medical clearance functions. Logic is now unified in the blade template.
})();
