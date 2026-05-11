@extends('layouts.registrar')

@section('title', 'PLP - Scholastic Comments')
@section('page-title', 'SCHOLASTIC COMMENTS')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="pf-page">
    <div class="ga-page">

        {{-- Filter Bar --}}
        <div class="ga-card ga-filter-card sched-filter-bar">
            <div class="ga-filter-grid" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
                <div>
                    <label class="ga-label">School Year</label>
                    <select class="app-filter-select" id="scFilterSY">
                        <option value="">All</option>
                        <option>2025-2026</option>
                        <option>2024-2025</option>
                    </select>
                </div>
                <div>
                    <label class="ga-label">Semester</label>
                    <select class="app-filter-select" id="scFilterSemester">
                        <option value="">All</option>
                        <option>First</option>
                        <option>Second</option>
                    </select>
                </div>
                <div style="flex:2;">
                    <label class="ga-label">Search Student</label>
                    <input type="text" class="app-filter-select" id="scFilterSearch" placeholder="Student name or number">
                </div>
                <div>
                    <label class="ga-label">&nbsp;</label>
                    <button type="button" class="pf-btn-new ga-btn ga-btn-primary" onclick="scApplyFilters()">Search</button>
                </div>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="ga-toolbar ga-toolbar-end" style="margin-top:12px;">
            <div style="font-size:0.78rem; color:#64748b; font-style:italic;">
                Scholastic comments track academic standing remarks (Dean's List, Academic Warning, Probation, etc.) per student per semester.
            </div>
            <button type="button" class="pf-btn-new ga-btn ga-btn-primary" onclick="scOpenAddModal()">+ Add Comment</button>
        </div>

        {{-- Table --}}
        <div class="ga-table-wrap app-table-wrap" style="margin-top:10px;">
            <table class="ga-table ga-table-compact app-table" id="scTable">
                <thead>
                    <tr>
                        <th style="width:70px;">Action</th>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Comment / Remark</th>
                        <th>Date Issued</th>
                        <th>Issued By</th>
                    </tr>
                </thead>
                <tbody id="scTableBody">
                    {{-- Placeholder demo rows --}}
                    @php
                        $scRows = [
                            ['id' => 1, 'student_no' => '2024A0012', 'name' => 'Juan Dela Cruz', 'sy' => '2024-2025', 'sem' => 'First', 'comment' => "Dean's List", 'date' => '2025-01-15', 'by' => 'Registrar'],
                            ['id' => 2, 'student_no' => '2024A0035', 'name' => 'Maria Santos',   'sy' => '2024-2025', 'sem' => 'First', 'comment' => 'Academic Warning', 'date' => '2025-01-15', 'by' => 'Registrar'],
                            ['id' => 3, 'student_no' => '2023A0089', 'name' => 'Pedro Reyes',    'sy' => '2024-2025', 'sem' => 'Second','comment' => 'Academic Probation', 'date' => '2025-06-10', 'by' => 'Registrar'],
                        ];
                    @endphp
                    @forelse($scRows as $r)
                    <tr data-sc-id="{{ $r['id'] }}">
                        <td>
                            <div class="apst-action-btn" onclick="scToggleMenu({{ $r['id'] }}, event)" aria-label="Actions" title="Actions">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="apst-dropdown" id="scMenu{{ $r['id'] }}">
                                <button type="button" onclick="scOpenEdit({{ $r['id'] }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="scOpenDelete({{ $r['id'] }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                        <td class="sc-no">{{ $r['student_no'] }}</td>
                        <td class="sc-name">{{ $r['name'] }}</td>
                        <td class="sc-sy">{{ $r['sy'] }}</td>
                        <td class="sc-sem">{{ $r['sem'] }}</td>
                        <td class="sc-comment">{{ $r['comment'] }}</td>
                        <td class="sc-date">{{ $r['date'] }}</td>
                        <td class="sc-by">{{ $r['by'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center; color:#666;">No scholastic comments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add / Edit Modal --}}
<div class="req-modal-overlay" id="scItemModal" style="display:none;" onclick="if(event.target===this)scCloseModal('scItemModal')">
    <div class="req-modal-box" style="max-width:520px;">
        <h3 class="req-modal-title" id="scItemModalTitle">ADD SCHOLASTIC COMMENT</h3>
        <div class="req-modal-fields" style="display:flex; flex-direction:column; gap:12px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Number</label>
                <input type="text" id="scInputNo" class="req-modal-input" placeholder="e.g. 2024A0012">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Name</label>
                <input type="text" id="scInputName" class="req-modal-input" placeholder="Full name">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">School Year</label>
                    <input type="text" id="scInputSY" class="req-modal-input" placeholder="e.g. 2025-2026">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Semester</label>
                    <select id="scInputSemester" class="req-modal-input">
                        <option>First</option>
                        <option>Second</option>
                    </select>
                </div>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Comment / Remark</label>
                <select id="scInputComment" class="req-modal-input">
                    <option>Dean's List</option>
                    <option>Academic Warning</option>
                    <option>Academic Probation</option>
                    <option>President's Lister</option>
                    <option>Subject Overload Approved</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="req-modal-field-group" id="scOtherWrap" style="display:none;">
                <label class="req-modal-label">Specify Remark</label>
                <input type="text" id="scInputOther" class="req-modal-input" placeholder="Enter custom remark">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Date Issued</label>
                    <input type="date" id="scInputDate" class="req-modal-input">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Issued By</label>
                    <input type="text" id="scInputBy" class="req-modal-input" placeholder="e.g. Registrar">
                </div>
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:18px;">
            <button type="button" class="req-btn-cancel" onclick="scCloseModal('scItemModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="scSaveItem()">Save</button>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="req-modal-overlay" id="scDeleteModal" style="display:none;" onclick="if(event.target===this)scCloseModal('scDeleteModal')">
    <div class="req-modal-box" style="max-width:400px; text-align:center;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE COMMENT</h3>
        <p style="font-size:0.9rem; color:#4b5563; margin:10px 0 0;">Are you sure you want to delete this scholastic comment?</p>
        <div class="req-modal-actions" style="margin-top:18px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="scCloseModal('scDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#b42318;" onclick="scConfirmDelete()">Delete</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
#scTable .apst-dropdown { position: fixed; }
</style>
<script>
(function () {
    var scCurrentId = null;
    var scNextId = 100;

    function scCloseMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function (m) {
            m.classList.remove('open');
            m.style.top = '';
            m.style.left = '';
            m.style.bottom = '';
        });
    }

    window.scToggleMenu = function (id, event) {
        event.stopPropagation();
        var menu = document.getElementById('scMenu' + id);
        if (!menu) return;
        var isOpen = menu.classList.contains('open');
        scCloseMenus();
        if (!isOpen) {
            var btn = event.currentTarget;
            var rect = btn.getBoundingClientRect();
            var spaceBelow = window.innerHeight - rect.bottom;
            menu.style.left = (rect.left - 130) + 'px';
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
    };

    window.scOpenAddModal = function () {
        scCurrentId = null;
        document.getElementById('scItemModalTitle').textContent = 'ADD SCHOLASTIC COMMENT';
        ['scInputNo','scInputName','scInputSY','scInputDate','scInputBy','scInputOther'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        document.getElementById('scInputSemester').selectedIndex = 0;
        document.getElementById('scInputComment').selectedIndex = 0;
        document.getElementById('scOtherWrap').style.display = 'none';
        document.getElementById('scItemModal').style.display = 'flex';
    };

    window.scOpenEdit = function (id) {
        scCloseMenus();
        scCurrentId = id;
        var row = document.querySelector('tr[data-sc-id="' + id + '"]');
        if (!row) return;
        document.getElementById('scItemModalTitle').textContent = 'EDIT SCHOLASTIC COMMENT';
        document.getElementById('scInputNo').value = row.querySelector('.sc-no').textContent.trim();
        document.getElementById('scInputName').value = row.querySelector('.sc-name').textContent.trim();
        document.getElementById('scInputSY').value = row.querySelector('.sc-sy').textContent.trim();
        var sem = row.querySelector('.sc-sem').textContent.trim();
        document.getElementById('scInputSemester').value = sem;
        var comment = row.querySelector('.sc-comment').textContent.trim();
        var commentEl = document.getElementById('scInputComment');
        var isCustom = Array.from(commentEl.options).every(function(o) { return o.value !== comment; });
        if (isCustom) {
            commentEl.value = 'Other';
            document.getElementById('scOtherWrap').style.display = '';
            document.getElementById('scInputOther').value = comment;
        } else {
            commentEl.value = comment;
            document.getElementById('scOtherWrap').style.display = 'none';
        }
        document.getElementById('scInputDate').value = row.querySelector('.sc-date').textContent.trim();
        document.getElementById('scInputBy').value = row.querySelector('.sc-by').textContent.trim();
        document.getElementById('scItemModal').style.display = 'flex';
    };

    window.scSaveItem = function () {
        var no = document.getElementById('scInputNo').value.trim();
        var name = document.getElementById('scInputName').value.trim();
        var sy = document.getElementById('scInputSY').value.trim();
        var sem = document.getElementById('scInputSemester').value;
        var commentSel = document.getElementById('scInputComment').value;
        var comment = commentSel === 'Other' ? document.getElementById('scInputOther').value.trim() : commentSel;
        var date = document.getElementById('scInputDate').value;
        var by = document.getElementById('scInputBy').value.trim() || 'Registrar';

        if (!no || !name || !sy || !comment) {
            alert('Please fill in Student Number, Name, School Year, and Comment.');
            return;
        }

        var tbody = document.getElementById('scTableBody');
        var emptyRow = tbody.querySelector('td[colspan]');
        if (emptyRow) emptyRow.closest('tr').remove();

        if (scCurrentId) {
            var row = document.querySelector('tr[data-sc-id="' + scCurrentId + '"]');
            if (row) {
                row.querySelector('.sc-no').textContent = no;
                row.querySelector('.sc-name').textContent = name;
                row.querySelector('.sc-sy').textContent = sy;
                row.querySelector('.sc-sem').textContent = sem;
                row.querySelector('.sc-comment').textContent = comment;
                row.querySelector('.sc-date').textContent = date;
                row.querySelector('.sc-by').textContent = by;
            }
        } else {
            var newId = scNextId++;
            var tr = document.createElement('tr');
            tr.setAttribute('data-sc-id', newId);
            tr.innerHTML =
                '<td>' +
                    '<div class="apst-action-btn" onclick="scToggleMenu(' + newId + ', event)" aria-label="Actions" title="Actions"><span></span><span></span><span></span></div>' +
                    '<div class="apst-dropdown" id="scMenu' + newId + '">' +
                        '<button type="button" onclick="scOpenEdit(' + newId + ')">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>' +
                            'Edit' +
                        '</button>' +
                        '<button type="button" class="apst-del-btn" onclick="scOpenDelete(' + newId + ')">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                            'Delete' +
                        '</button>' +
                    '</div>' +
                '</td>' +
                '<td class="sc-no">' + no + '</td>' +
                '<td class="sc-name">' + name + '</td>' +
                '<td class="sc-sy">' + sy + '</td>' +
                '<td class="sc-sem">' + sem + '</td>' +
                '<td class="sc-comment">' + comment + '</td>' +
                '<td class="sc-date">' + date + '</td>' +
                '<td class="sc-by">' + by + '</td>';
            tbody.appendChild(tr);
        }

        scCloseModal('scItemModal');
    };

    window.scOpenDelete = function (id) {
        scCloseMenus();
        scCurrentId = id;
        document.getElementById('scDeleteModal').style.display = 'flex';
    };

    window.scConfirmDelete = function () {
        if (scCurrentId) {
            var row = document.querySelector('tr[data-sc-id="' + scCurrentId + '"]');
            if (row) row.remove();
        }
        scCloseModal('scDeleteModal');
    };

    window.scCloseModal = function (id) {
        var modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    };

    window.scApplyFilters = function () {
        var sy = document.getElementById('scFilterSY').value.toLowerCase();
        var sem = document.getElementById('scFilterSemester').value.toLowerCase();
        var q = document.getElementById('scFilterSearch').value.toLowerCase();
        document.querySelectorAll('#scTableBody tr[data-sc-id]').forEach(function (row) {
            var rowSY = (row.querySelector('.sc-sy') ? row.querySelector('.sc-sy').textContent : '').toLowerCase();
            var rowSem = (row.querySelector('.sc-sem') ? row.querySelector('.sc-sem').textContent : '').toLowerCase();
            var rowNo = (row.querySelector('.sc-no') ? row.querySelector('.sc-no').textContent : '').toLowerCase();
            var rowName = (row.querySelector('.sc-name') ? row.querySelector('.sc-name').textContent : '').toLowerCase();
            var matchSY = !sy || rowSY === sy;
            var matchSem = !sem || rowSem === sem;
            var matchQ = !q || rowNo.indexOf(q) !== -1 || rowName.indexOf(q) !== -1;
            row.style.display = (matchSY && matchSem && matchQ) ? '' : 'none';
        });
    };

    document.getElementById('scInputComment').addEventListener('change', function () {
        document.getElementById('scOtherWrap').style.display = this.value === 'Other' ? '' : 'none';
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.apst-action-btn') && !e.target.closest('.apst-dropdown')) {
            scCloseMenus();
        }
    });
    window.addEventListener('scroll', scCloseMenus, true);
})();
</script>
@endpush
