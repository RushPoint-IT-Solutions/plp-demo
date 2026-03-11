var STATUSES = [
    { code: 'D', status: 'Document Submitted', message: 'Document Submitted' },
    { code: 'O', status: 'On Probation',        message: 'Process On Probation' },
    { code: 'P', status: 'On Process',          message: 'Application, On Process' },
    { code: 'A', status: 'Accepted',            message: 'Congratulations! We would like to inform you that your application is accepted! Thank You!' },
    { code: 'R', status: 'Rejected',            message: 'Sorry, your application did not meet the Pamantasan ng Lungsod Ng Pasig requirements.' },
    { code: 'I', status: 'In Progress',         message: 'Application In Progress' },
];

var editingIdx = -1;
var deletingIdx = -1;

function renderTable(filter) {
    var tbody = document.getElementById('apstTableBody');
    tbody.innerHTML = '';
    STATUSES.forEach(function(s, idx) {
        if (filter) {
            var f = filter.toLowerCase();
            if (s.code.toLowerCase().indexOf(f) === -1 &&
                s.status.toLowerCase().indexOf(f) === -1 &&
                s.message.toLowerCase().indexOf(f) === -1) return;
        }
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
            '<td>' + s.code + '</td>' +
            '<td>' + s.status + '</td>' +
            '<td>' + s.message + '</td>';
        tbody.appendChild(tr);
    });
}

function filterRows() {
    renderTable(document.getElementById('apstSearchInput').value);
}

// Close any open dropdown when clicking elsewhere
document.addEventListener('click', function(e) {
    if (!e.target.closest('.apst-action-btn') && !e.target.closest('.apst-dropdown')) {
        document.querySelectorAll('.apst-dropdown').forEach(function(d){ d.classList.remove('open'); d.classList.remove('drop-up'); d.style.top = ''; d.style.left = ''; d.style.bottom = ''; });
    }
});

// Close dropdown on scroll so the fixed-position menu doesn't float away
window.addEventListener('scroll', function () {
    document.querySelectorAll('.apst-dropdown.open').forEach(function (d) { d.classList.remove('open'); d.classList.remove('drop-up'); d.style.top = ''; d.style.left = ''; d.style.bottom = ''; });
}, true);

function toggleMenu(idx, e) {
    e.stopPropagation();
    var menu = document.getElementById('apstMenu' + idx);
    var isOpen = menu.classList.contains('open');
    document.querySelectorAll('.apst-dropdown').forEach(function(d){ d.classList.remove('open'); d.classList.remove('drop-up'); d.style.top = ''; d.style.left = ''; d.style.bottom = ''; });
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

// ── New modal ─────────────────────────────────────────────────────────
function openNewModal() {
    editingIdx = -1;
    document.getElementById('apstModalTitle').textContent = 'NEW STATUS';
    document.getElementById('apstInputCode').value = '';
    document.getElementById('apstInputStatus').value = '';
    document.getElementById('apstInputMessage').value = '';
    document.getElementById('apstModal').style.display = 'flex';
}

function openEditModal(idx) {
    document.querySelectorAll('.apst-dropdown').forEach(function(d){ d.classList.remove('open'); });
    editingIdx = idx;
    var s = STATUSES[idx];
    document.getElementById('apstModalTitle').textContent = 'EDIT STATUS';
    document.getElementById('apstInputCode').value = s.code;
    document.getElementById('apstInputStatus').value = s.status;
    document.getElementById('apstInputMessage').value = s.message;
    document.getElementById('apstModal').style.display = 'flex';
}

function closeApstModal(e) {
    if (!e || e.target.id === 'apstModal') {
        document.getElementById('apstModal').style.display = 'none';
    }
}

function saveApstRow() {
    var code    = document.getElementById('apstInputCode').value.trim();
    var status  = document.getElementById('apstInputStatus').value.trim();
    var message = document.getElementById('apstInputMessage').value.trim();
    if (!code || !status) { alert('Status Code and Status are required.'); return; }
    if (editingIdx === -1) {
        STATUSES.push({ code: code, status: status, message: message });
    } else {
        STATUSES[editingIdx] = { code: code, status: status, message: message };
    }
    document.getElementById('apstModal').style.display = 'none';
    document.getElementById('apstSuccessMsg').textContent = 'Status \'' + code + '\' saved successfully.';
    document.getElementById('apstSuccessModal').style.display = 'flex';
    renderTable(document.getElementById('apstSearchInput').value);
}

// ── Delete modal ──────────────────────────────────────────────────────
function openDeleteModal(idx) {
    document.querySelectorAll('.apst-dropdown').forEach(function(d){ d.classList.remove('open'); });
    deletingIdx = idx;
    document.getElementById('apstDeleteModal').style.display = 'flex';
}

function closeDeleteModal(e) {
    if (!e || e.target.id === 'apstDeleteModal') {
        document.getElementById('apstDeleteModal').style.display = 'none';
    }
}

function confirmDelete() {
    if (deletingIdx > -1) { STATUSES.splice(deletingIdx, 1); deletingIdx = -1; }
    document.getElementById('apstDeleteModal').style.display = 'none';
    renderTable(document.getElementById('apstSearchInput').value);
}

// ── Success modal ─────────────────────────────────────────────────────
function closeApstSuccess(e) {
    if (!e || e.target.id === 'apstSuccessModal') {
        document.getElementById('apstSuccessModal').style.display = 'none';
    }
}

renderTable();
