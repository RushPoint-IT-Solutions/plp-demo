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

    var detailApplicantPk = document.getElementById('detailApplicantPk');
    var detailApplicantId = document.getElementById('detailApplicantId');
    var detailApplicantName = document.getElementById('detailApplicantName');

    var scheduleExamDate = document.getElementById('scheduleExamDate');
    var scheduleExamTime = document.getElementById('scheduleExamTime');
    var scheduleExamVenue = document.getElementById('scheduleExamVenue');
    var scheduleExamFeedback = document.getElementById('scheduleExamFeedback');
    var saveExamScheduleBtn = document.getElementById('saveExamScheduleBtn');
    var printExamScheduleBtn = document.getElementById('printExamScheduleBtn');

    var examResultStatus = document.getElementById('examResultStatus');
    var examResultScore = document.getElementById('examResultScore');
    var examResultFeedback = document.getElementById('examResultFeedback');
    var saveExamResultBtn = document.getElementById('saveExamResultBtn');

    var selectedApplicantRow = null;

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
        node.style.color = isError ? '#b42318' : '#14532d';
    }

    function normalizeStatus(status) {
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

        var normalizedStatus = normalizeStatus(status);
        var statusClass = 'app-status-pending';
        if (normalizedStatus === 'Passed') {
            statusClass = 'app-status-accepted';
        }
        if (normalizedStatus === 'Failed') {
            statusClass = 'app-status-rejected';
        }

        cell.innerHTML = '<span class="' + statusClass + '"><span class="app-status-dot"></span>' + normalizedStatus.toUpperCase() + '</span>';
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

        if (detailApplicantPk) {
            detailApplicantPk.value = row.getAttribute('data-pk') || '';
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

        if (examResultStatus) {
            examResultStatus.value = normalizeStatus(row.getAttribute('data-exam-result-status'));
        }
        if (examResultScore) {
            examResultScore.value = row.getAttribute('data-exam-score') || '';
        }

        setFeedback(scheduleExamFeedback, '', false);
        setFeedback(examResultFeedback, '', false);
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

                renderStatusChip(selectedApplicantRow, rowData.exam_result_status || 'Pending');
                setFeedback(scheduleExamFeedback, (data && data.message) ? data.message : 'Exam schedule saved successfully.', false);
            }).catch(function (error) {
                saveExamScheduleBtn.disabled = false;
                setFeedback(scheduleExamFeedback, getPayloadErrorMessage(error.payload || error), true);
            });
        });
    }

    if (saveExamResultBtn) {
        saveExamResultBtn.addEventListener('click', function () {
            var applicantPk = detailApplicantPk ? detailApplicantPk.value : '';
            if (!selectedApplicantRow || !applicantPk) {
                setFeedback(examResultFeedback, 'Select an applicant first.', true);
                return;
            }

            var resultStatus = examResultStatus ? examResultStatus.value : 'Pending';
            var scoreValue = examResultScore ? examResultScore.value : '';

            if (scoreValue !== '') {
                var scoreNumber = parseFloat(scoreValue);
                if (isNaN(scoreNumber) || scoreNumber < 0 || scoreNumber > 100) {
                    setFeedback(examResultFeedback, 'Score must be between 0 and 100.', true);
                    return;
                }
            }

            var endpoint = getUrlFromTemplate(examResultUrlTemplate, applicantPk);
            if (!endpoint) {
                setFeedback(examResultFeedback, 'Result endpoint is not configured.', true);
                return;
            }

            saveExamResultBtn.disabled = true;
            sendPut(endpoint, {
                exam_result_status: resultStatus,
                exam_score: scoreValue === '' ? null : scoreValue
            }).then(function (data) {
                saveExamResultBtn.disabled = false;

                var rowData = data && data.row ? data.row : {};
                var normalized = normalizeStatus(rowData.exam_result_status || resultStatus);
                selectedApplicantRow.setAttribute('data-exam-result-status', normalized);
                selectedApplicantRow.setAttribute('data-exam-score', rowData.exam_score === null || typeof rowData.exam_score === 'undefined' ? '' : String(rowData.exam_score));

                renderStatusChip(selectedApplicantRow, normalized);
                setFeedback(examResultFeedback, (data && data.message) ? data.message : 'Exam result saved successfully.', false);
            }).catch(function (error) {
                saveExamResultBtn.disabled = false;
                setFeedback(examResultFeedback, getPayloadErrorMessage(error.payload || error), true);
            });
        });
    }

    if (printExamScheduleBtn) {
        printExamScheduleBtn.addEventListener('click', function () {
            window.print();
        });
    }
})();
