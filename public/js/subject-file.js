/* Subject File JS */

var SF_PAGE = document.getElementById('subjectFilePage');
var SF_FETCH_URL = SF_PAGE ? SF_PAGE.getAttribute('data-fetch-url') : '';
var SF_STORE_URL = SF_PAGE ? SF_PAGE.getAttribute('data-store-url') : '';
var SF_UPDATE_URL_TEMPLATE = SF_PAGE ? SF_PAGE.getAttribute('data-update-url-template') : '';
var SF_DELETE_URL_TEMPLATE = SF_PAGE ? SF_PAGE.getAttribute('data-delete-url-template') : '';
var SF_CSRF_TOKEN = SF_PAGE ? SF_PAGE.getAttribute('data-csrf-token') : '';

var SUBJECTS = [];
var editingId = null;
var deletingId = null;
var searchTimer = null;
var currentPage = 1;
var lastPage = 1;
var totalRows = 0;
var perPage = 25;
var activeSearch = '';
var isLoading = false;

function yn(v) {
    return v ? 'Y' : 'N';
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function closeOpenMenus() {
    document.querySelectorAll('.apst-dropdown').forEach(function (dropdown) {
        dropdown.classList.remove('open');
        dropdown.classList.remove('drop-up');
        dropdown.style.top = '';
        dropdown.style.left = '';
        dropdown.style.bottom = '';
    });
}

function getSearchValue() {
    var searchInput = document.getElementById('sfSearchInput');
    return searchInput ? String(searchInput.value || '').trim() : '';
}

function getSortDirection() {
    var sortSelect = document.getElementById('sfSort');
    return sortSelect ? sortSelect.value : 'asc';
}

function getUrlFromTemplate(template, id) {
    if (!template || !id) {
        return '';
    }

    return template.replace('__SUBJECT_ID__', String(id));
}

function getPayloadErrorMessage(payload, fallbackMessage) {
    if (payload && payload.message) {
        return payload.message;
    }

    if (payload && payload.errors && typeof payload.errors === 'object') {
        var firstErrorKey = Object.keys(payload.errors)[0];
        if (firstErrorKey && payload.errors[firstErrorKey] && payload.errors[firstErrorKey][0]) {
            return payload.errors[firstErrorKey][0];
        }
    }

    return fallbackMessage;
}

function showErrorMessage(message) {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast(message, 'warning');
        return;
    }

    alert(message);
}

function removeDuplicateAutoPagers() {
    var table = document.getElementById('sfTable');
    if (!table) {
        return;
    }

    table.dataset.noAutoPager = '1';

    var pageRoot = document.getElementById('subjectFilePage');
    if (!pageRoot) {
        return;
    }

    pageRoot.querySelectorAll('.rtp-pagination').forEach(function (pager) {
        if (pager.closest('#sfPaginationBar')) {
            return;
        }

        if (pager.parentNode) {
            pager.parentNode.removeChild(pager);
        }
    });
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

function bindSubjectFileControls() {
    var searchInput = document.getElementById('sfSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', filterSubjects);
    }

    var sortSelect = document.getElementById('sfSort');
    if (sortSelect) {
        sortSelect.addEventListener('change', sortSubjects);
    }

    var newBtn = document.getElementById('sfNewBtn');
    if (newBtn) {
        newBtn.addEventListener('click', openNewSubjectModal);
    }

    var prevBtn = document.getElementById('sfPrevBtn');
    if (prevBtn) {
        prevBtn.addEventListener('click', goToPrevPage);
    }

    var nextBtn = document.getElementById('sfNextBtn');
    if (nextBtn) {
        nextBtn.addEventListener('click', goToNextPage);
    }

    var paginationBar = document.getElementById('sfPaginationBar');
    if (paginationBar) {
        paginationBar.addEventListener('click', function (event) {
            var pageBtn = event.target.closest('[data-sf-page]');
            if (!pageBtn || isLoading) {
                return;
            }

            var targetPage = parseInt(pageBtn.getAttribute('data-sf-page'), 10);
            if (isNaN(targetPage) || targetPage < 1 || targetPage > lastPage || targetPage === currentPage) {
                return;
            }

            loadSubjects(activeSearch, targetPage);
        });
    }
}

function setLoadingState(loading) {
    isLoading = !!loading;

    var tbody = document.getElementById('sfTableBody');
    if (isLoading && tbody) {
        tbody.innerHTML = '<tr><td colspan="12">Loading courses...</td></tr>';
    }

    renderPagination();
}

function sendJsonRequest(url, method, payload) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': SF_CSRF_TOKEN
        },
        body: JSON.stringify(payload || {}),
        credentials: 'same-origin'
    }).then(function (response) {
        return response.json().catch(function () {
            return {};
        }).then(function (payloadData) {
            if (!response.ok) {
                throw payloadData;
            }

            return payloadData;
        });
    });
}

function buildFetchUrl(searchValue, pageValue) {
    var params = [];
    if (searchValue !== '') {
        params.push('search=' + encodeURIComponent(searchValue));
    }

    params.push('sort=' + encodeURIComponent(getSortDirection()));
    params.push('page=' + encodeURIComponent(String(pageValue)));
    params.push('per_page=' + encodeURIComponent(String(perPage)));

    return SF_FETCH_URL + (SF_FETCH_URL.indexOf('?') === -1 ? '?' : '&') + params.join('&');
}

function loadSubjects(search, page) {
    if (!SF_FETCH_URL) {
        return;
    }

    if (typeof search === 'string') {
        activeSearch = String(search || '').trim();
    }

    if (typeof page === 'number' && page > 0) {
        currentPage = page;
    }

    var targetPage = currentPage;
    var url = buildFetchUrl(activeSearch, targetPage);
    setLoadingState(true);

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    }).then(function (response) {
        return response.json().catch(function () {
            return {};
        }).then(function (payloadData) {
            if (!response.ok) {
                throw payloadData;
            }

            return payloadData;
        });
    }).then(function (payloadData) {
        SUBJECTS = payloadData && payloadData.rows ? payloadData.rows : [];
        var meta = payloadData && payloadData.meta ? payloadData.meta : {};
        totalRows = Number(meta.total || 0);
        lastPage = Number(meta.last_page || 1);
        if (lastPage < 1) {
            lastPage = 1;
        }

        currentPage = Number(meta.page || targetPage);
        if (currentPage < 1) {
            currentPage = 1;
        }

        if (currentPage > lastPage) {
            currentPage = lastPage;
        }

        perPage = Number(meta.per_page || perPage);
        if (perPage < 10) {
            perPage = 10;
        }

        perPage = 25;

        if (SUBJECTS.length === 0 && totalRows > 0 && currentPage > 1) {
            loadSubjects(activeSearch, currentPage - 1);
            return;
        }

        renderTable();
        renderPagination();
    }).catch(function (errorPayload) {
        console.error(errorPayload);
        showErrorMessage(getPayloadErrorMessage(errorPayload, 'Unable to load courses right now.'));
    }).finally(function () {
        setLoadingState(false);
    });
}

function renderTable() {
    var tbody = document.getElementById('sfTableBody');
    if (!tbody) {
        return;
    }

    tbody.innerHTML = '';

    if (!SUBJECTS.length) {
        tbody.innerHTML = '<tr><td colspan="12">No courses found.</td></tr>';
        return;
    }

    var rowNumberOffset = (currentPage - 1) * perPage;

    SUBJECTS.forEach(function (subject, idx) {
        var lec = Number(subject.lec || 0);
        var lab = Number(subject.lab || 0);
        var totalUnits = lec + lab;
        var hours = Number(subject.hours || 0);

        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td>' + (rowNumberOffset + idx + 1) + '</td>' +
            '<td>' + escapeHtml(subject.code) + '</td>' +
            '<td style="text-align:left;">' + escapeHtml(subject.title) + '</td>' +
            '<td style="text-align:center;">' + lec.toFixed(1) + '</td>' +
            '<td style="text-align:center;">' + lab.toFixed(1) + '</td>' +
            '<td style="text-align:center;">' + totalUnits.toFixed(1) + '</td>' +
            '<td style="text-align:center;">' + hours.toFixed(1) + '</td>' +
            '<td style="text-align:center;">' + escapeHtml(subject.course_type || 'Major') + '</td>' +
            '<td style="text-align:center;">' + yn(subject.core) + '</td>' +
            '<td style="text-align:center;">' + yn(subject.applied) + '</td>' +
            '<td style="text-align:center;">' + yn(subject.specialized) + '</td>' +
            '<td>' +
                '<div class="apst-action-btn" onclick="toggleSubjectMenu(' + idx + ', event)">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="sfMenu' + idx + '">' +
                    '<button onclick="openEditSubjectModal(' + idx + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        ' Edit' +
                    '</button>' +
                    '<button class="apst-del-btn" onclick="openDeleteSubjectModal(' + idx + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        ' Delete' +
                    '</button>' +
                '</div>' +
            '</td>';
        tbody.appendChild(tr);
    });

    var totalRow = document.createElement('tr');
    totalRow.className = 'sf-total-row';
    var rangeStart = rowNumberOffset + 1;
    var rangeEnd = rowNumberOffset + SUBJECTS.length;
    totalRow.innerHTML = '<td colspan="12" class="sf-total-cell">Showing <strong>' + rangeStart + '-' + rangeEnd + '</strong> of <strong>' + totalRows + '</strong> courses</td>';
    tbody.appendChild(totalRow);
}

function renderPagination() {
    var prevBtn = document.getElementById('sfPrevBtn');
    var nextBtn = document.getElementById('sfNextBtn');
    var pageNumbers = document.getElementById('sfPageNumbers');

    if (prevBtn) {
        prevBtn.disabled = isLoading || currentPage <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = isLoading || currentPage >= lastPage;
    }

    if (pageNumbers) {
        pageNumbers.innerHTML = '';

        var windowRange = getPaginationWindow(currentPage, lastPage);
        for (var pageNumber = windowRange.start; pageNumber <= windowRange.end; pageNumber += 1) {
            var pageBtn = document.createElement('button');
            pageBtn.type = 'button';
            pageBtn.className = 'rtp-page-num' + (pageNumber === currentPage ? ' active' : '');
            pageBtn.setAttribute('data-sf-page', String(pageNumber));
            pageBtn.setAttribute('aria-label', 'Go to page ' + pageNumber);
            pageBtn.textContent = String(pageNumber);

            if (isLoading || pageNumber === currentPage) {
                pageBtn.disabled = true;
            }

            pageNumbers.appendChild(pageBtn);
        }
    }
}

function filterSubjects() {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(function () {
        loadSubjects(getSearchValue(), 1);
    }, 250);
}

function sortSubjects() {
    loadSubjects(activeSearch, 1);
}

function goToPrevPage() {
    if (currentPage <= 1 || isLoading) {
        return;
    }

    loadSubjects(activeSearch, currentPage - 1);
}

function goToNextPage() {
    if (currentPage >= lastPage || isLoading) {
        return;
    }

    loadSubjects(activeSearch, currentPage + 1);
}

document.addEventListener('click', function (event) {
    if (!event.target.closest('.apst-action-btn') && !event.target.closest('.apst-dropdown')) {
        closeOpenMenus();
    }
});

window.addEventListener('scroll', function () {
    closeOpenMenus();
}, true);

function toggleSubjectMenu(idx, event) {
    event.stopPropagation();

    var menu = document.getElementById('sfMenu' + idx);
    if (!menu) {
        return;
    }

    var isOpen = menu.classList.contains('open');
    closeOpenMenus();

    if (!isOpen) {
        var btn = menu.parentElement.querySelector('.apst-action-btn');
        var rect = btn.getBoundingClientRect();
        var spacing = 4;
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

        if (spaceBelow < 120) {
            menu.classList.add('drop-up');
            menu.style.top = 'auto';
            menu.style.bottom = (window.innerHeight - rect.bottom) + 'px';
        } else {
            menu.style.top = rect.top + 'px';
            menu.style.bottom = 'auto';
        }

        menu.classList.add('open');
    }
}

function openNewSubjectModal() {
    editingId = null;
    document.getElementById('sfModalTitle').textContent = 'NEW COURSE';
    document.getElementById('sfInputCode').value = '';
    document.getElementById('sfInputTitle').value = '';
    document.getElementById('sfInputLec').value = '';
    document.getElementById('sfInputLab').value = '';
    document.getElementById('sfInputHours').value = '';
    document.getElementById('sfInputCourseType').value = 'Major';
    document.getElementById('sfInputCore').checked = false;
    document.getElementById('sfInputApplied').checked = false;
    document.getElementById('sfInputSpecialized').checked = false;
    document.getElementById('sfModal').style.display = 'flex';
}

function openEditSubjectModal(idx) {
    closeOpenMenus();
    var subject = SUBJECTS[idx];
    if (!subject) {
        return;
    }

    editingId = subject.id;
    document.getElementById('sfModalTitle').textContent = 'EDIT COURSE';
    document.getElementById('sfInputCode').value = subject.code || '';
    document.getElementById('sfInputTitle').value = subject.title || '';
    document.getElementById('sfInputLec').value = Number(subject.lec || 0).toFixed(0);
    document.getElementById('sfInputLab').value = Number(subject.lab || 0).toFixed(0);
    document.getElementById('sfInputHours').value = Number(subject.hours || 0).toFixed(1);
    document.getElementById('sfInputCourseType').value = subject.course_type || 'Major';
    document.getElementById('sfInputCore').checked = !!subject.core;
    document.getElementById('sfInputApplied').checked = !!subject.applied;
    document.getElementById('sfInputSpecialized').checked = !!subject.specialized;
    document.getElementById('sfModal').style.display = 'flex';
}

function closeSfModal(event) {
    if (!event || event.target.id === 'sfModal') {
        document.getElementById('sfModal').style.display = 'none';
    }
}

function saveSubject() {
    var code = String(document.getElementById('sfInputCode').value || '').trim();
    var title = String(document.getElementById('sfInputTitle').value || '').trim();
    var lec = parseInt(document.getElementById('sfInputLec').value, 10);
    var lab = parseInt(document.getElementById('sfInputLab').value, 10);
    var hours = parseFloat(document.getElementById('sfInputHours').value);
    var courseType = String(document.getElementById('sfInputCourseType').value || 'Major');
    var core = !!document.getElementById('sfInputCore').checked;
    var applied = !!document.getElementById('sfInputApplied').checked;
    var specialized = !!document.getElementById('sfInputSpecialized').checked;

    if (!code || !title) {
        showErrorMessage('Course Code and Title are required.');
        return;
    }

    if (isNaN(lec) || lec < 0) {
        lec = 0;
    }

    if (isNaN(lab) || lab < 0) {
        lab = 0;
    }
    if (isNaN(hours) || hours <= 0) {
        showErrorMessage('Hours is required and must be greater than 0. This is used for room schedule duration.');
        return;
    }

    var isEditing = !!editingId;
    var method = editingId ? 'PUT' : 'POST';
    var url = editingId ? getUrlFromTemplate(SF_UPDATE_URL_TEMPLATE, editingId) : SF_STORE_URL;
    if (!url) {
        showErrorMessage('Unable to save course right now.');
        return;
    }

    sendJsonRequest(url, method, {
        code: code,
        title: title,
        lec: lec,
        lab: lab,
        hours: hours,
        course_type: courseType,
        core: core,
        applied: applied,
        specialized: specialized
    }).then(function (payloadData) {
        editingId = null;
        document.getElementById('sfModal').style.display = 'none';
        document.getElementById('sfSuccessMsg').textContent = payloadData.message || ('Course "' + code + '" saved successfully.');
        document.getElementById('sfSuccessModal').style.display = 'flex';
        loadSubjects(activeSearch, isEditing ? currentPage : 1);
    }).catch(function (errorPayload) {
        showErrorMessage(getPayloadErrorMessage(errorPayload, 'Unable to save course right now.'));
    });
}

function openDeleteSubjectModal(idx) {
    closeOpenMenus();
    var subject = SUBJECTS[idx];
    if (!subject) {
        return;
    }

    deletingId = subject.id;
    document.getElementById('sfDeleteName').textContent = subject.code + ' - ' + subject.title;
    document.getElementById('sfDeleteModal').style.display = 'flex';
}

function closeSfDeleteModal(event) {
    if (!event || event.target.id === 'sfDeleteModal') {
        document.getElementById('sfDeleteModal').style.display = 'none';
    }
}

function confirmDeleteSubject() {
    var deleteUrl = getUrlFromTemplate(SF_DELETE_URL_TEMPLATE, deletingId);
    if (!deleteUrl) {
        showErrorMessage('Unable to delete course right now.');
        return;
    }

    sendJsonRequest(deleteUrl, 'DELETE', {}).then(function (payloadData) {
        deletingId = null;
        document.getElementById('sfDeleteModal').style.display = 'none';
        document.getElementById('sfSuccessMsg').textContent = payloadData.message || 'Course deleted successfully.';
        document.getElementById('sfSuccessModal').style.display = 'flex';
        loadSubjects(activeSearch, currentPage);
    }).catch(function (errorPayload) {
        showErrorMessage(getPayloadErrorMessage(errorPayload, 'Unable to delete course right now.'));
    });
}

function closeSfSuccess(event) {
    if (!event || event.target.id === 'sfSuccessModal') {
        document.getElementById('sfSuccessModal').style.display = 'none';
    }
}

if (SF_PAGE) {
    bindSubjectFileControls();
    removeDuplicateAutoPagers();
    window.setTimeout(removeDuplicateAutoPagers, 200);
    window.setTimeout(removeDuplicateAutoPagers, 900);
    loadSubjects('', 1);
}
