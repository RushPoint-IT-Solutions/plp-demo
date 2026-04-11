var APST_PAGE = document.getElementById('approvalStatusPage');
var APST_FETCH_URL = APST_PAGE ? APST_PAGE.getAttribute('data-fetch-url') : '';
var APST_STORE_URL = APST_PAGE ? APST_PAGE.getAttribute('data-store-url') : '';
var APST_UPDATE_URL_TEMPLATE = APST_PAGE ? APST_PAGE.getAttribute('data-update-url-template') : '';
var APST_DELETE_URL_TEMPLATE = APST_PAGE ? APST_PAGE.getAttribute('data-delete-url-template') : '';
var APST_CSRF_TOKEN = APST_PAGE ? APST_PAGE.getAttribute('data-csrf-token') : '';
var APST_STATUSES = [];
var APST_EDITING_ID = null;
var APST_DELETING_ID = null;
var APST_SEARCH_TIMER = null;

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
    var searchInput = document.getElementById('apstSearchInput');
    return searchInput ? String(searchInput.value || '').trim() : '';
}

function getUrlFromTemplate(template, id) {
    if (!template || !id) {
        return '';
    }

    return template.replace('__STATUS_ID__', String(id));
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

function sendJsonRequest(url, method, payload) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': APST_CSRF_TOKEN
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

function loadStatuses(search) {
    if (!APST_FETCH_URL) {
        return;
    }

    var searchTerm = String(search || '').trim();
    var url = APST_FETCH_URL;

    if (searchTerm !== '') {
        url += (url.indexOf('?') === -1 ? '?' : '&') + 'search=' + encodeURIComponent(searchTerm);
    }

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
        APST_STATUSES = payloadData && payloadData.rows ? payloadData.rows : [];
        renderTable();
    }).catch(function (errorPayload) {
        console.error(errorPayload);
        alert(getPayloadErrorMessage(errorPayload, 'Unable to load approval statuses right now.'));
    });
}

function renderTable() {
    var tbody = document.getElementById('apstTableBody');
    if (!tbody) {
        return;
    }

    tbody.innerHTML = '';

    if (!APST_STATUSES.length) {
        tbody.innerHTML = '<tr><td colspan="4">No approval statuses found.</td></tr>';
        return;
    }

    APST_STATUSES.forEach(function (statusRow, idx) {
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td>' +
                '<div class="apst-action-btn" onclick="toggleMenu(' + idx + ', event)">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="apstMenu' + idx + '">' +
                    '<button onclick="openEditModal(' + idx + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        ' Edit' +
                    '</button>' +
                    '<button class="apst-del-btn" onclick="openDeleteModal(' + idx + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        ' Delete' +
                    '</button>' +
                '</div>' +
            '</td>' +
            '<td>' + escapeHtml(statusRow.code) + '</td>' +
            '<td>' + escapeHtml(statusRow.status) + '</td>' +
            '<td>' + escapeHtml(statusRow.message) + '</td>';
        tbody.appendChild(tr);
    });
}

function filterRows() {
    if (APST_SEARCH_TIMER) {
        clearTimeout(APST_SEARCH_TIMER);
    }

    APST_SEARCH_TIMER = setTimeout(function () {
        loadStatuses(getSearchValue());
    }, 250);
}

document.addEventListener('click', function (event) {
    if (!event.target.closest('.apst-action-btn') && !event.target.closest('.apst-dropdown')) {
        closeOpenMenus();
    }
});

window.addEventListener('scroll', function () {
    closeOpenMenus();
}, true);

function toggleMenu(idx, event) {
    event.stopPropagation();
    var menu = document.getElementById('apstMenu' + idx);
    if (!menu) {
        return;
    }

    var isOpen = menu.classList.contains('open');
    closeOpenMenus();

    if (!isOpen) {
        var btn = menu.parentElement.querySelector('.apst-action-btn');
        var rect = btn.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;

        menu.style.left = (rect.right + 4) + 'px';
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

function openNewModal() {
    APST_EDITING_ID = null;
    document.getElementById('apstModalTitle').textContent = 'NEW STATUS';
    document.getElementById('apstInputCode').value = '';
    document.getElementById('apstInputStatus').value = '';
    document.getElementById('apstInputMessage').value = '';
    document.getElementById('apstModal').style.display = 'flex';
}

function openEditModal(idx) {
    closeOpenMenus();
    var selectedStatus = APST_STATUSES[idx];
    if (!selectedStatus) {
        return;
    }

    APST_EDITING_ID = selectedStatus.id;
    document.getElementById('apstModalTitle').textContent = 'EDIT STATUS';
    document.getElementById('apstInputCode').value = selectedStatus.code || '';
    document.getElementById('apstInputStatus').value = selectedStatus.status || '';
    document.getElementById('apstInputMessage').value = selectedStatus.message || '';
    document.getElementById('apstModal').style.display = 'flex';
}

function closeApstModal(event) {
    if (!event || event.target.id === 'apstModal') {
        document.getElementById('apstModal').style.display = 'none';
    }
}

function saveApstRow() {
    var code = String(document.getElementById('apstInputCode').value || '').trim();
    var status = String(document.getElementById('apstInputStatus').value || '').trim();
    var message = String(document.getElementById('apstInputMessage').value || '').trim();

    if (!code || !status) {
        alert('Status Code and Status are required.');
        return;
    }

    var method = APST_EDITING_ID ? 'PUT' : 'POST';
    var url = APST_EDITING_ID ? getUrlFromTemplate(APST_UPDATE_URL_TEMPLATE, APST_EDITING_ID) : APST_STORE_URL;
    if (!url) {
        alert('Unable to save approval status right now.');
        return;
    }

    sendJsonRequest(url, method, {
        status_code: code,
        status: status,
        message: message
    }).then(function (payloadData) {
        document.getElementById('apstModal').style.display = 'none';
        document.getElementById('apstSuccessMsg').textContent = payloadData.message || ('Status "' + code + '" saved successfully.');
        document.getElementById('apstSuccessModal').style.display = 'flex';
        loadStatuses(getSearchValue());
    }).catch(function (errorPayload) {
        alert(getPayloadErrorMessage(errorPayload, 'Unable to save approval status right now.'));
    });
}

function openDeleteModal(idx) {
    closeOpenMenus();
    var selectedStatus = APST_STATUSES[idx];
    if (!selectedStatus) {
        return;
    }

    APST_DELETING_ID = selectedStatus.id;
    document.getElementById('apstDeleteModal').style.display = 'flex';
}

function closeDeleteModal(event) {
    if (!event || event.target.id === 'apstDeleteModal') {
        document.getElementById('apstDeleteModal').style.display = 'none';
    }
}

function confirmDelete() {
    var deleteUrl = getUrlFromTemplate(APST_DELETE_URL_TEMPLATE, APST_DELETING_ID);
    if (!deleteUrl) {
        alert('Unable to delete approval status right now.');
        return;
    }

    sendJsonRequest(deleteUrl, 'DELETE', {}).then(function () {
        APST_DELETING_ID = null;
        document.getElementById('apstDeleteModal').style.display = 'none';
        loadStatuses(getSearchValue());
    }).catch(function (errorPayload) {
        alert(getPayloadErrorMessage(errorPayload, 'Unable to delete approval status right now.'));
    });
}

function closeApstSuccess(event) {
    if (!event || event.target.id === 'apstSuccessModal') {
        document.getElementById('apstSuccessModal').style.display = 'none';
    }
}

if (APST_PAGE) {
    loadStatuses('');
}
