@extends('layouts.registrar')

@section('title', 'PLP - Requirements')
@section('page-title', 'REQUIREMENTS')

@section('content')
<div class="req-page" id="reqPage">

    {{-- ══════════════════════════════════════════════
         VIEW 1 — STUDENT CARD LIST
    ══════════════════════════════════════════════ --}}
    <div id="reqListView">
        {{-- Search bar --}}
        <div class="req-search-wrap">
            <div class="req-search-box">
                <input type="text" class="req-search-input" placeholder="Search Name/ID" id="reqSearchInput" oninput="filterCards()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="req-search-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
        </div>

        {{-- Student cards grid --}}
        <div class="req-cards-grid" id="reqCardsGrid">
            {{-- Cards generated dynamically by JS --}}
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         VIEW 2 — STUDENT DETAIL TABLE
    ══════════════════════════════════════════════ --}}
    <div id="reqDetailView" style="display:none;">
        <div class="req-detail-topbar">
            <button class="req-back-btn" onclick="showListView()">&#8592; Back</button>
            <div class="req-search-box req-search-box-sm">
                <input type="text" class="req-search-input" placeholder="Search Requirement" id="reqDetailSearch" oninput="filterDetailRows()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="req-search-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
        </div>

        <div class="req-detail-header">
            <span class="req-detail-name-label">NAME:<span id="detailName"></span></span>
            <span class="req-detail-id-label">ID: <span id="detailId"></span></span>
        </div>

        <div class="app-table-wrap">
            <table class="app-table">
                <thead>
                    <tr>
                        <th style="width:80px;">Submit</th>
                        <th>Requirement Name</th>
                        <th>Remarks</th>
                        <th style="width:160px;">Date Verified</th>
                    </tr>
                </thead>
                <tbody id="reqDetailTableBody"></tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         EDIT MODAL
    ══════════════════════════════════════════════ --}}
    <div class="req-modal-overlay" id="reqEditModal" style="display:none;" onclick="closeEditModal(event)">
        <div class="req-modal-box">
            <h3 class="req-modal-title" id="modalTitle">LIBRARY CLEARANCE</h3>
            <div class="req-modal-meta">
                <span>NAME: <strong id="modalStudentName"></strong></span>
                <span>STUDENT NO.: <strong id="modalStudentNo"></strong></span>
            </div>
            <div class="req-modal-fields">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">REQUIREMENT NAME</label>
                    <input type="text" class="req-modal-input" id="modalReqName" placeholder="Library Clearance">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">REMARKS:</label>
                    <input type="text" class="req-modal-input" id="modalRemarks" placeholder="Write Remarks...">
                </div>
            </div>
            <div class="req-modal-field-group" style="margin-top:12px;">
                <label class="req-modal-label">DATE VERIFIED</label>
                <input type="text" class="req-modal-input" id="modalDateVerified" placeholder="mm/dd/yy" style="max-width:200px;">
                <small class="req-modal-hint">Format: mm/dd/yy &nbsp;&#8226;&nbsp; Leave blank to use today's date</small>
            </div>
            <div class="req-modal-field-group req-photo-group" id="modalPhotoGroup" style="display:none; margin-top:12px;">
                <label class="req-modal-label">UPLOAD PHOTO</label>
                <input type="file" class="req-modal-file" id="modalPhotoUpload" accept="image/*" onchange="previewPhoto(this)">
                <div id="modalPhotoPreview"></div>
            </div>
            <div class="req-modal-actions">
                <button class="req-btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="req-btn-save" onclick="saveRequirement()">Save</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         SUCCESS MODAL
    ══════════════════════════════════════════════ --}}
    <div class="req-modal-overlay" id="reqSuccessModal" style="display:none;" onclick="closeSuccessModal(event)">
        <div class="req-modal-box req-modal-success">
            <h3 class="req-modal-success-title">SUCCESSFUL!</h3>
            <p class="req-modal-success-msg" id="successMsg">Requirement updated successfully.</p>
            <button class="req-btn-ok" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>

</div>

@push('scripts')
<script>
// ── Date helpers ─────────────────────────────────────────────────────────────
var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

// Today → "mm/dd/yy"
function todayAsMMDDYY() {
    var d = new Date();
    var mm = String(d.getMonth() + 1).padStart(2, '0');
    var dd = String(d.getDate()).padStart(2, '0');
    var yy = String(d.getFullYear()).slice(-2);
    return mm + '/' + dd + '/' + yy;
}

// "mm/dd/yy" → "Feb 20, 2026"
function formatDateDisplay(str) {
    if (!str) return '';
    var parts = str.split('/');
    if (parts.length !== 3) return str;
    var mo = parseInt(parts[0], 10) - 1;
    var dd = parseInt(parts[1], 10);
    var yy = parseInt(parts[2], 10);
    var year = yy < 100 ? 2000 + yy : yy;
    if (isNaN(mo) || isNaN(dd) || isNaN(year)) return str;
    return MONTHS[mo] + ' ' + dd + ', ' + year;
}

// "Feb 20, 2026" → "02/20/26"  (for pre-filling the modal input)
function displayToMMDDYY(displayStr) {
    if (!displayStr) return '';
    var monthMap = {};
    MONTHS.forEach(function(m, i){ monthMap[m] = i; });
    var match = displayStr.match(/^([A-Za-z]{3})\s+(\d+),\s+(\d{4})$/);
    if (match) {
        var mo = String(monthMap[match[1]] + 1).padStart(2, '0');
        var dd = String(parseInt(match[2])).padStart(2, '0');
        var yy = match[3].slice(-2);
        return mo + '/' + dd + '/' + yy;
    }
    return displayStr; // already mm/dd/yy or unknown
}

// ── Sample data ─────────────────────────────────────────────────────────────
var STUDENTS = [
    {
        id: '2223A8137',
        name: 'MARK JAY BARES',
        requirements: [
            { name: 'OJT Certificate',          done: true,  remarks: 'Finished 486 hours at Tech Corp',          dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Thesis Defense Clearance', done: true,  remarks: 'Passed Final Defense (Grade: 1.25)',        dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Evaluation of Grades',     done: true,  remarks: 'No Back Subjects / Deficiencies',           dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Library Clearance',        done: true,  remarks: 'No Outstanding Book Loans',                 dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Graduation Application',   done: true,  remarks: 'Form Submitted (AY 2025-2026)',             dateVerified: 'Feb 20, 2026', action: null },
        ]
    },
    {
        id: '2223A8138',
        name: 'ANDREA JANE AUSTERO',
        requirements: [
            { name: 'OJT Certificate',          done: true,  remarks: 'Finished 486 hours at Tech Corp',          dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Thesis Defense Clearance', done: true,  remarks: 'Passed Final Defense (Grade: 1.25)',        dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Evaluation of Grades',     done: true,  remarks: 'No Back Subjects / Deficiencies',           dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Library Clearance',        done: false, remarks: 'Pending: 1 unreturned book (Calculus)',     dateVerified: '',             action: 'Return Book' },
            { name: 'Graduation Application',   done: false, remarks: 'Missing: Needs 2\u00d72 ID picture',        dateVerified: '',             action: 'Submit Photo' },
        ]
    },
    {
        id: '2223A8139',
        name: 'JUAN DELA CRUZ',
        requirements: [
            { name: 'OJT Certificate',          done: true,  remarks: 'Finished 500 hours at ABC Corp',           dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Thesis Defense Clearance', done: false, remarks: 'Pending: Schedule not yet set',            dateVerified: '',             action: 'Submit Form' },
            { name: 'Evaluation of Grades',     done: false, remarks: 'Has 1 incomplete subject',                 dateVerified: '',             action: 'Submit Form' },
            { name: 'Library Clearance',        done: false, remarks: 'Pending: 2 unreturned books',              dateVerified: '',             action: 'Return Book' },
            { name: 'Graduation Application',   done: false, remarks: 'Not yet submitted',                        dateVerified: '',             action: 'Submit Photo' },
        ]
    },
    {
        id: '2223A8140',
        name: 'MARIA SANTOS',
        requirements: [
            { name: 'OJT Certificate',          done: true,  remarks: 'Finished 520 hours at XYZ Corp',          dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Thesis Defense Clearance', done: true,  remarks: 'Passed Final Defense (Grade: 1.50)',       dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Evaluation of Grades',     done: true,  remarks: 'No Back Subjects',                        dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Library Clearance',        done: true,  remarks: 'All books returned',                      dateVerified: 'Feb 20, 2026', action: null },
            { name: 'Graduation Application',   done: true,  remarks: 'Form Submitted (AY 2025-2026)',            dateVerified: 'Feb 22, 2026', action: null },
        ]
    }
];

var currentStudentIdx = 0;

// ── Render card list ─────────────────────────────────────────────────────────
function renderCards(filter) {
    var grid = document.getElementById('reqCardsGrid');
    grid.innerHTML = '';
    STUDENTS.forEach(function(s, idx) {
        if (filter) {
            var f = filter.toLowerCase();
            if (s.name.toLowerCase().indexOf(f) === -1 && s.id.toLowerCase().indexOf(f) === -1) return;
        }
        var done = s.requirements.filter(function(r){ return r.done; }).length;
        var total = s.requirements.length;
        var verified = done === total;
        var card = document.createElement('div');
        card.className = 'req-card';
        card.setAttribute('data-idx', idx);
        card.onclick = function(){ showDetailView(parseInt(this.getAttribute('data-idx'))); };

        var blocks = '';
        for (var i = 0; i < total; i++) {
            blocks += '<div class="req-progress-block ' + (s.requirements[i].done ? 'done' : '') + '"></div>';
        }

        card.innerHTML =
            '<div class="req-card-name">' + s.name + '</div>' +
            '<div class="req-card-id">ID: &nbsp;' + s.id + '</div>' +
            '<div class="req-progress-row">' +
                '<div class="req-progress-blocks">' + blocks + '</div>' +
                '<span class="req-progress-count">(' + done + '/' + total + ' Done)</span>' +
            '</div>' +
            '<div class="req-card-status ' + (verified ? 'status-verified' : 'status-inprogress') + '">' +
                'STATUS: ' + (verified ? 'VERIFIED' : 'IN PROGRESS') +
            '</div>';
        grid.appendChild(card);
    });
}

function filterCards() {
    renderCards(document.getElementById('reqSearchInput').value);
}

// ── Detail view ───────────────────────────────────────────────────────────────
function showDetailView(idx) {
    currentStudentIdx = idx;
    var s = STUDENTS[idx];
    document.getElementById('detailName').textContent = ' ' + s.name;
    document.getElementById('detailId').textContent = s.id;
    renderDetailTable(s.requirements);
    document.getElementById('reqListView').style.display = 'none';
    document.getElementById('reqDetailView').style.display = 'block';
}

function showListView() {
    document.getElementById('reqDetailView').style.display = 'none';
    document.getElementById('reqListView').style.display = 'block';
    document.getElementById('reqDetailSearch').value = '';
}

function renderDetailTable(reqs, filter) {
    var tbody = document.getElementById('reqDetailTableBody');
    tbody.innerHTML = '';
    reqs.forEach(function(r, i) {
        if (filter) {
            var f = filter.toLowerCase();
            if (r.name.toLowerCase().indexOf(f) === -1 && r.remarks.toLowerCase().indexOf(f) === -1) return;
        }
        var checkHtml = r.done
            ? '<div class="req-checkbox checked" onclick="toggleCheck(' + currentStudentIdx + ',' + i + ')"><svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#fff\' stroke-width=\'3\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'20 6 9 17 4 12\'/></svg></div>'
            : '<div class="req-checkbox" onclick="toggleCheck(' + currentStudentIdx + ',' + i + ')"></div>';

        // Action link goes in the Date Verified cell (only when not done)
        var dateCell = '';
        if (r.done) {
            dateCell = r.dateVerified || '';
        } else if (r.action) {
            dateCell = '<a href="#" class="req-action-link" onclick="openEditModal(' + currentStudentIdx + ',' + i + ');return false;">' + r.action + '</a>';
        }

        var tr = document.createElement('tr');
        tr.setAttribute('data-req-idx', i);
        tr.innerHTML =
            '<td style="text-align:center;">' + checkHtml + '</td>' +
            '<td>' + r.name + '</td>' +
            '<td>' + r.remarks + '</td>' +
            '<td>' + dateCell + '</td>';
        tbody.appendChild(tr);
    });
}

function filterDetailRows() {
    renderDetailTable(STUDENTS[currentStudentIdx].requirements, document.getElementById('reqDetailSearch').value);
}

function toggleCheck(sIdx, rIdx) {
    STUDENTS[sIdx].requirements[rIdx].done = !STUDENTS[sIdx].requirements[rIdx].done;
    renderDetailTable(STUDENTS[sIdx].requirements);
    renderCards(document.getElementById('reqSearchInput').value);
}

// ── Edit modal ────────────────────────────────────────────────────────────────
var editingStudent = 0, editingReq = 0;

function openEditModal(sIdx, rIdx) {
    editingStudent = sIdx;
    editingReq = rIdx;
    var s = STUDENTS[sIdx];
    var r = s.requirements[rIdx];

    document.getElementById('modalTitle').textContent = r.name.toUpperCase();
    document.getElementById('modalStudentName').textContent = s.name;
    document.getElementById('modalStudentNo').textContent = s.id;
    document.getElementById('modalReqName').value = r.name;
    document.getElementById('modalRemarks').value = r.remarks;

    // Pre-fill date: convert stored "Feb 20, 2026" → "02/20/26", or default to today
    var existingDate = r.dateVerified ? displayToMMDDYY(r.dateVerified) : '';
    document.getElementById('modalDateVerified').value = existingDate || todayAsMMDDYY();

    // Show photo upload only for Submit Photo action
    var isPhoto = (r.action === 'Submit Photo');
    document.getElementById('modalPhotoGroup').style.display = isPhoto ? 'block' : 'none';
    document.getElementById('modalPhotoUpload').value = '';
    document.getElementById('modalPhotoPreview').innerHTML = '';

    document.getElementById('reqEditModal').style.display = 'flex';
}

function previewPhoto(input) {
    var preview = document.getElementById('modalPhotoPreview');
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="req-photo-preview">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function closeEditModal(e) {
    if (!e || e.target.id === 'reqEditModal') closeModal();
}

function closeModal() {
    document.getElementById('reqEditModal').style.display = 'none';
}

function saveRequirement() {
    var r = STUDENTS[editingStudent].requirements[editingReq];
    r.name    = document.getElementById('modalReqName').value;
    r.remarks = document.getElementById('modalRemarks').value;

    // Date: use entered value or default to today; convert mm/dd/yy → "Feb 20, 2026"
    var rawDate = document.getElementById('modalDateVerified').value.trim();
    if (!rawDate) rawDate = todayAsMMDDYY();
    r.dateVerified = formatDateDisplay(rawDate);

    r.done   = true;
    r.action = null;

    closeModal();
    document.getElementById('successMsg').textContent = r.name + ' updated successfully.';
    document.getElementById('reqSuccessModal').style.display = 'flex';
    renderDetailTable(STUDENTS[editingStudent].requirements);
    renderCards(document.getElementById('reqSearchInput').value);
}

// ── Success modal ─────────────────────────────────────────────────────────────
function closeSuccessModal(e) {
    if (!e || e.target.id === 'reqSuccessModal') {
        document.getElementById('reqSuccessModal').style.display = 'none';
    }
}

// ── Init ──────────────────────────────────────────────────────────────────────
renderCards();
</script>
@endpush
@endsection
