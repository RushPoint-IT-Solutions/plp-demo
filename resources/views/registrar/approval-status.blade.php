@extends('layouts.registrar')

@section('title', 'PLP - Approval Status')
@section('page-title', 'APPROVAL STATUS')

@section('content')
<div class="apst-page">

    {{-- Top bar: search + new button --}}
    <div class="apst-topbar">
        <div class="apst-search-box">
            <input type="text" class="apst-search-input" placeholder="Search Status Code" id="apstSearchInput" oninput="filterRows()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="apst-search-icon">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>
        <button class="apst-new-btn" onclick="openNewModal()">+ New Status</button>
    </div>

    {{-- Table --}}
    <div class="app-table-wrap">
        <table class="app-table" id="apstTable">
            <thead>
                <tr>
                    <th style="width:70px;">Action</th>
                    <th style="width:130px;">Status Code</th>
                    <th style="width:180px;">Status</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody id="apstTableBody"></tbody>
        </table>
    </div>

    {{-- Edit / New Modal --}}
    <div class="req-modal-overlay" id="apstModal" style="display:none;" onclick="closeApstModal(event)">
        <div class="req-modal-box">
            <h3 class="req-modal-title" id="apstModalTitle">NEW STATUS</h3>
            <div class="req-modal-fields">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">STATUS CODE</label>
                    <input type="text" class="req-modal-input" id="apstInputCode" placeholder="e.g. D" maxlength="5">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">STATUS</label>
                    <input type="text" class="req-modal-input" id="apstInputStatus" placeholder="e.g. Document Submitted">
                </div>
            </div>
            <div class="req-modal-field-group" style="margin-top:12px;">
                <label class="req-modal-label">MESSAGE</label>
                <textarea class="req-modal-input" id="apstInputMessage" rows="3" placeholder="Enter message..."
                    style="resize:vertical; font-size:0.85rem; padding:8px 10px;"></textarea>
            </div>
            <div class="req-modal-actions">
                <button class="req-btn-cancel" onclick="closeApstModal()">Cancel</button>
                <button class="req-btn-save" onclick="saveApstRow()">Save</button>
            </div>
        </div>
    </div>

    {{-- Delete confirm modal --}}
    <div class="req-modal-overlay" id="apstDeleteModal" style="display:none;" onclick="closeDeleteModal(event)">
        <div class="req-modal-box req-modal-success" style="min-width:300px;">
            <h3 class="req-modal-title" style="color:#c0392b;">DELETE STATUS</h3>
            <p style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;">
                Are you sure you want to delete this status?
            </p>
            <div class="req-modal-actions" style="justify-content:center;">
                <button class="req-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="req-btn-save" style="background:#c0392b;" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    {{-- Success modal --}}
    <div class="req-modal-overlay" id="apstSuccessModal" style="display:none;" onclick="closeApstSuccess(event)">
        <div class="req-modal-box req-modal-success">
            <h3 class="req-modal-success-title">SUCCESSFUL!</h3>
            <p class="req-modal-success-msg" id="apstSuccessMsg">Status saved successfully.</p>
            <button class="req-btn-ok" onclick="closeApstSuccess()">OK</button>
        </div>
    </div>

</div>

@push('scripts')
<script>
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
        document.querySelectorAll('.apst-dropdown').forEach(function(d){ d.classList.remove('open'); });
    }
});

function toggleMenu(idx, e) {
    e.stopPropagation();
    var menu = document.getElementById('apstMenu' + idx);
    var isOpen = menu.classList.contains('open');
    document.querySelectorAll('.apst-dropdown').forEach(function(d){ d.classList.remove('open'); });
    if (!isOpen) menu.classList.add('open');
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
</script>
@endpush
@endsection
