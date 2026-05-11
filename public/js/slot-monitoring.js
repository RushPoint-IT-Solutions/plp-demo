/* Slot Monitoring Page */
(function () {
    var PAGE = document.getElementById('slotMonitoringPage');
    if (!PAGE) {
        return;
    }

    var FETCH_URL = PAGE.getAttribute('data-fetch-url') || '';
    var REPORT_ACTUAL_SIZE_URL = PAGE.getAttribute('data-report-actual-size-url') || '';
    var REPORT_UNDER20_URL = PAGE.getAttribute('data-report-under20-url') || '';
    var REPORT_DISSOLVED_URL = PAGE.getAttribute('data-report-dissolved-url') || '';
    var REPORT_CLOSED_URL = PAGE.getAttribute('data-report-closed-url') || '';

    var ROWS = [];
    var currentPage = 1;
    var lastPage = 1;
    var totalRows = 0;
    var perPage = 25;
    var isLoading = false;
    var requestToken = 0;
    var textFilterTimers = {};
    var activeListController = null;
    var hasAbortController = typeof AbortController !== 'undefined';

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
        prevBtn: document.getElementById('smPrevBtn'),
        nextBtn: document.getElementById('smNextBtn'),
        pageNumbers: document.getElementById('smPageNumbers'),
        actualSizeBtn: document.getElementById('smActualSizeBtn'),
        under20Btn: document.getElementById('smUnder20Btn'),
        dissolvedBtn: document.getElementById('smDissolvedBtn'),
        closedBtn: document.getElementById('smClosedBtn')
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

    function normalizeText(value) {
        return String(value || '').trim();
    }

    function getPaginationWindow(page, maxPage) {
        var start = Math.max(1, page - 2);
        var end = Math.min(maxPage, page + 2);

        if (page <= 3) {
            end = Math.min(maxPage, 5);
        } else if (page >= maxPage - 2) {
            start = Math.max(1, maxPage - 4);
        }

        return {
            start: start,
            end: end
        };
    }

    function showErrorMessage(message) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast(message, 'warning');
            return;
        }

        alert(message);
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

    function fetchJson(url, signal) {
        var options = {
            method: 'GET',
            headers: {
                Accept: 'application/json',
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

    function buildFetchUrl(pageValue) {
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

        return FETCH_URL + (FETCH_URL.indexOf('?') === -1 ? '?' : '&') + params.join('&');
    }

    function buildReportUrl(baseUrl) {
        var params = [];

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

        if (!params.length) {
            return baseUrl;
        }

        return baseUrl + (baseUrl.indexOf('?') === -1 ? '?' : '&') + params.join('&');
    }

    function setLoadingState(loading) {
        isLoading = !!loading;

        if (els.prevBtn) {
            els.prevBtn.disabled = isLoading || currentPage <= 1;
        }

        if (els.nextBtn) {
            els.nextBtn.disabled = isLoading || currentPage >= lastPage;
        }

        if (isLoading && els.body) {
            els.body.innerHTML = '<tr><td colspan="10">Loading slots...</td></tr>';
        }
    }

    function getStatusClass(percentage, statusLabel) {
        var normalizedStatus = normalizeText(statusLabel).toLowerCase();

        if (normalizedStatus === 'closed') {
            return 'is-critical';
        }

        if (normalizedStatus === 'dissolved') {
            return 'is-warning';
        }

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
            var statusLabel = normalizeText(row.status_label) || 'Open';
            var statusClass = getStatusClass(percentage, statusLabel);
            var tr = document.createElement('tr');
            tr.setAttribute('data-sm-id', row.id || '');
            tr.setAttribute('data-sm-total', row.total_slots || 0);
            tr.setAttribute('data-sm-enrolled', row.enrolled_slots || 0);
            tr.setAttribute('data-sm-label', escapeHtml(row.section) + ' / ' + escapeHtml(row.subject));

            tr.innerHTML =
                '<td>' + escapeHtml(row.school_year) + '</td>' +
                '<td>' + escapeHtml(row.semester) + '</td>' +
                '<td>' + escapeHtml(formatCourseLabel(row)) + '</td>' +
                '<td>' + escapeHtml(row.section) + '</td>' +
                '<td>' + escapeHtml(row.subject) + '</td>' +
                '<td>' + escapeHtml(row.schedule) + '</td>' +
                '<td class="sm-number-cell sm-total-slots">' + escapeHtml(row.total_slots) + '</td>' +
                '<td class="sm-number-cell">' + escapeHtml(row.enrolled_slots) + '</td>' +
                '<td>' +
                    '<div class="slot-status-wrap">' +
                        '<div class="slot-progress-bar">' +
                            '<div class="slot-progress-fill ' + statusClass + '" style="width:' + percentage + '%;"></div>' +
                        '</div>' +
                        '<span class="slot-pct ' + statusClass + '">' + percentage + '%</span>' +
                        '<span class="slot-status-label ' + statusClass + '">' + escapeHtml(statusLabel) + '</span>' +
                    '</div>' +
                '</td>' +
                '<td style="text-align:center;">' +
                    '<button type="button" class="sm-edit-slots-btn" data-sm-row="' + escapeHtml(row.id || '') + '" ' +
                        'style="font-size:0.72rem; font-weight:700; color:#006837; background:none; border:1px solid #006837; border-radius:4px; padding:3px 10px; cursor:pointer; white-space:nowrap;">' +
                        'Edit Slots' +
                    '</button>' +
                '</td>';

            els.body.appendChild(tr);
        });
    }

    function renderPagination() {
        if (els.prevBtn) {
            els.prevBtn.disabled = isLoading || currentPage <= 1;
        }

        if (els.nextBtn) {
            els.nextBtn.disabled = isLoading || currentPage >= lastPage;
        }

        if (!els.pageNumbers) {
            return;
        }

        els.pageNumbers.innerHTML = '';

        var windowRange = getPaginationWindow(currentPage, lastPage);
        for (var pageNumber = windowRange.start; pageNumber <= windowRange.end; pageNumber += 1) {
            var pageBtn = document.createElement('button');
            pageBtn.type = 'button';
            pageBtn.className = 'rtp-page-num' + (pageNumber === currentPage ? ' active' : '');
            pageBtn.setAttribute('data-sm-page', String(pageNumber));
            pageBtn.setAttribute('aria-label', 'Go to page ' + pageNumber);
            pageBtn.textContent = String(pageNumber);

            if (isLoading || pageNumber === currentPage) {
                pageBtn.disabled = true;
            }

            els.pageNumbers.appendChild(pageBtn);
        }
    }

    function loadSlots(pageToLoad) {
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
            buildFetchUrl(currentPage),
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
            perPage = 25;

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

    function scheduleTextFilterReload(fieldName, rawValue) {
        var normalizedValue = normalizeText(rawValue);
        var previousValue = normalizeText(filters[fieldName]);

        filters[fieldName] = normalizedValue;

        if (textFilterTimers[fieldName]) {
            clearTimeout(textFilterTimers[fieldName]);
        }

        textFilterTimers[fieldName] = setTimeout(function () {
            delete textFilterTimers[fieldName];

            if ((fieldName === 'search' || fieldName === 'course_query') && normalizedValue !== '' && normalizedValue.length < 2) {
                if (previousValue.length >= 2) {
                    loadSlots(1);
                }
                return;
            }

            loadSlots(1);
        }, 420);
    }

    function openReport(baseUrl) {
        if (!baseUrl) {
            showErrorMessage('Report is unavailable right now.');
            return;
        }

        window.open(buildReportUrl(baseUrl), '_blank', 'noopener');
    }

    function bindEvents() {
        if (els.schoolYear) {
            els.schoolYear.addEventListener('change', function () {
                filters.school_year = normalizeText(els.schoolYear.value);
                loadSlots(1);
            });
        }

        if (els.semester) {
            els.semester.addEventListener('change', function () {
                filters.semester = normalizeText(els.semester.value);
                loadSlots(1);
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

        if (els.prevBtn) {
            els.prevBtn.addEventListener('click', function () {
                if (isLoading || currentPage <= 1) {
                    return;
                }

                loadSlots(currentPage - 1);
            });
        }

        if (els.nextBtn) {
            els.nextBtn.addEventListener('click', function () {
                if (isLoading || currentPage >= lastPage) {
                    return;
                }

                loadSlots(currentPage + 1);
            });
        }

        var paginationBar = document.getElementById('smPaginationBar');
        if (paginationBar) {
            paginationBar.addEventListener('click', function (event) {
                var pageBtn = event.target.closest('[data-sm-page]');
                if (!pageBtn || isLoading) {
                    return;
                }

                var targetPage = toInt(pageBtn.getAttribute('data-sm-page'), 0);
                if (targetPage < 1 || targetPage > lastPage || targetPage === currentPage) {
                    return;
                }

                loadSlots(targetPage);
            });
        }

        if (els.actualSizeBtn) {
            els.actualSizeBtn.addEventListener('click', function () {
                openReport(REPORT_ACTUAL_SIZE_URL);
            });
        }

        if (els.under20Btn) {
            els.under20Btn.addEventListener('click', function () {
                openReport(REPORT_UNDER20_URL);
            });
        }

        if (els.dissolvedBtn) {
            els.dissolvedBtn.addEventListener('click', function () {
                openReport(REPORT_DISSOLVED_URL);
            });
        }

        if (els.closedBtn) {
            els.closedBtn.addEventListener('click', function () {
                openReport(REPORT_CLOSED_URL);
            });
        }
    }

    // Edit Slots modal wiring
    var smEditModal = document.getElementById('smEditSlotsModal');
    var smEditInput = document.getElementById('smEditSlotsInput');
    var smEditEnrolled = document.getElementById('smEditEnrolledDisplay');
    var smEditContext = document.getElementById('smEditSlotsContext');
    var smSaveBtn = document.getElementById('smEditSlotsSaveBtn');
    var smActiveRow = null;
    var UPDATE_URL_TEMPLATE = PAGE.getAttribute('data-update-url-template') || '';
    var SM_CSRF = PAGE.getAttribute('data-csrf') || '';

    if (els.body) {
        els.body.addEventListener('click', function (event) {
            var btn = event.target.closest('.sm-edit-slots-btn');
            if (!btn) return;
            var rowId = btn.getAttribute('data-sm-row');
            smActiveRow = document.querySelector('tr[data-sm-id="' + rowId + '"]');
            if (!smActiveRow) return;

            if (smEditContext) smEditContext.textContent = smActiveRow.getAttribute('data-sm-label') || '-';
            if (smEditInput) smEditInput.value = smActiveRow.getAttribute('data-sm-total') || '';
            if (smEditEnrolled) smEditEnrolled.value = smActiveRow.getAttribute('data-sm-enrolled') || '0';
            if (smEditModal) smEditModal.style.display = 'flex';
        });
    }

    if (smSaveBtn) {
        smSaveBtn.addEventListener('click', function () {
            if (!smActiveRow) return;
            var newSlots = parseInt(smEditInput ? smEditInput.value : '', 10);
            if (isNaN(newSlots) || newSlots < 0) {
                alert('Please enter a valid number of slots.');
                return;
            }
            var rowId = smActiveRow.getAttribute('data-sm-id');
            var url = UPDATE_URL_TEMPLATE.replace('__ID__', rowId);

            smSaveBtn.disabled = true;
            smSaveBtn.textContent = 'Saving...';

            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': SM_CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ total_slots: newSlots })
            })
            .then(function (res) { return res.json(); })
            .then(function () {
                smActiveRow.setAttribute('data-sm-total', newSlots);
                var totalCell = smActiveRow.querySelector('.sm-total-slots');
                if (totalCell) totalCell.textContent = newSlots;
                if (smEditModal) smEditModal.style.display = 'none';
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Slots updated successfully.');
                }
            })
            .catch(function () {
                alert('Unable to update slots. Please try again.');
            })
            .finally(function () {
                smSaveBtn.disabled = false;
                smSaveBtn.textContent = 'Save';
            });
        });
    }

    bindEvents();
    loadSlots(1);
})();
