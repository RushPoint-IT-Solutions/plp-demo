@extends('layouts.registrar')

@section('title', 'PLP - BED Student Status')
@section('page-title', 'BED STUDENT STATUS')
@section('body-class', 'page-bed-status')



@section('content')
<div class="pf-page">
    <div class="bs-page">
        <div class="cfg-card">
            <div class="bs-toolbar">
                <div class="bs-search-wrap">
                    <label class="bs-toolbar-label" for="bsSearch">Search</label>
                    <div class="pf-search-wrap">
                        <span class="pf-search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input id="bsSearch" type="text" class="pf-search-input" placeholder="Search Student ID / Name...">
                    </div>
                </div>
                <div class="bs-field" style="min-width: 120px; flex: 1 1 120px;">
                    <label class="bs-toolbar-label" for="bsSchoolYear">School Year</label>
                    <select id="bsSchoolYear" class="app-filter-select">
                        <option>2025-2026</option>
                        <option>2026-2027</option>
                    </select>
                </div>
                <div class="bs-field" style="min-width: 100px; flex: 1 1 100px;">
                    <label class="bs-toolbar-label" for="bsTerm">Term</label>
                    <select id="bsTerm" class="app-filter-select">
                        <option>First</option>
                        <option>Second</option>
                    </select>
                </div>
                <div class="bs-field" style="min-width: 100px; flex: 1 1 100px;">
                    <label class="bs-toolbar-label" for="bsGradeLevel">Grade</label>
                    <select id="bsGradeLevel" class="app-filter-select">
                        <option value="">-yr level-</option>
                        <option value="Fourth">Fourth</option>
                    </select>
                </div>
                <div class="bs-field" style="min-width: 90px; flex: 1 1 90px;">
                    <label class="bs-toolbar-label" for="bsSection">Section</label>
                    <select id="bsSection" class="app-filter-select">
                        <option value="">-sections-</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>
                <button type="button" class="pf-btn-new" id="bsSearchBtn">Apply</button>
            </div>

            <div class="bs-filter-foot">
                <div class="bs-filter-checks">
                    <label class="bs-check"><input id="bsNoPayment" type="checkbox" class="req-checkbox-input"> No Payment</label>
                    <label class="bs-check"><input id="bsNoSection" type="checkbox" class="req-checkbox-input"> No Section</label>
                </div>
            </div>
        </div>

        <section>
            <div class="bs-table-head">
                <div class="bs-table-export">
                    <button type="button" class="bs-export-btn" id="bsExportToggleBtn" title="Export data">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Export
                        <span class="bs-export-caret"></span>
                    </button>
                    <div class="bs-export-menu" id="bsExportMenu">
                        <button type="button" id="bsExportPdfBtn">Export as PDF</button>
                        <button type="button" id="bsExportXlsBtn">Export as XLS</button>
                    </div>
                </div>
            </div>

            <div class="app-table-wrap">
                <table id="bsTable" class="app-table cfg-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Yr. Level</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bsTableBody"></tbody>
                </table>
            </div>

            <div class="bs-pager-row">
                <div class="bs-page-list">
                    <button type="button" class="bs-page-btn" disabled aria-label="Previous page">&lsaquo;</button>
                    <button type="button" class="bs-page-num active" aria-current="page">1</button>
                    <button type="button" class="bs-page-btn" disabled aria-label="Next page">&rsaquo;</button>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="req-modal-overlay" id="bsEditModal" style="display:none;" onclick="if(event.target===this) bsCloseEditModal()">
    <div class="req-modal-box" style="max-width:760px;">
        <h3 class="req-modal-title">EDIT BED STUDENT STATUS</h3>
        <input type="hidden" id="bsEditId" value="">

        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student ID</label>
                <input id="bsEditStudentId" type="text" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Yr. Level</label>
                <select id="bsEditYearLevel" class="req-modal-input">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                    <option value="Fourth">Fourth</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Section</label>
                <select id="bsEditSection" class="req-modal-input">
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                </select>
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Student Name</label>
                <input id="bsEditName" type="text" class="req-modal-input">
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Course</label>
                <input id="bsEditCourse" type="text" class="req-modal-input">
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="bsCloseEditModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="bsSaveEdit()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="bsDeleteModal" style="display:none;" onclick="if(event.target===this) bsCloseDeleteModal()">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE BED STATUS</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this record?</p>
        <input type="hidden" id="bsDeleteId" value="">
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="bsCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="bsConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var bsRows = [
        { id: 'bs-1', studentId: '2223A8137', name: 'Bares, Mark Jay', course: 'Bachelor of Science in Computer Science', yearLevel: 'Fourth', section: 'A' },
        { id: 'bs-2', studentId: '2223A8138', name: 'Austero, Andrea Jane', course: 'Bachelor of Science in Computer Science', yearLevel: 'Fourth', section: 'A' },
        { id: 'bs-3', studentId: '2223A8141', name: 'Dela Cruz, Juan', course: 'Bachelor of Science in Information Technology', yearLevel: 'Third', section: 'B' },
        { id: 'bs-4', studentId: '2223A8148', name: 'Rivera, Angelo', course: 'Bachelor of Science in Computer Engineering', yearLevel: 'Second', section: 'C' },
        { id: 'bs-5', studentId: '2223A8154', name: 'Santos, Maria', course: 'Bachelor of Science in Information Systems', yearLevel: 'Second', section: 'B' },
        { id: 'bs-6', studentId: '2223A8160', name: 'Benedict, John', course: 'Bachelor of Science in Computer Science', yearLevel: 'First', section: 'A' }
    ];

    function bsEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function bsGetFilteredRows() {
        var query = (document.getElementById('bsSearch').value || '').toLowerCase();
        var grade = document.getElementById('bsGradeLevel').value;
        var section = document.getElementById('bsSection').value;
        return bsRows.filter(function(row) {
            var queryMatch = !query || row.studentId.toLowerCase().indexOf(query) !== -1 || row.name.toLowerCase().indexOf(query) !== -1;
            var gradeMatch = !grade || row.yearLevel === grade;
            var sectionMatch = !section || row.section === section;
            return queryMatch && gradeMatch && sectionMatch;
        });
    }

    function bsBuildMenu(menuId, id) {
        return '' +
            '<div class="apst-action-btn" data-bs-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="bsOpenEditModal(\'' + bsEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" onclick="bsOpenDeleteModal(\'' + bsEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>Delete</button>' +
            '</div>';
    }

    function bsCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function bsToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        bsCloseActionMenus();
        if (isOpen) return;

        var rect = trigger.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;

        menu.style.left = 'auto';
        menu.style.right = (window.innerWidth - rect.left + 4) + 'px';

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

    function bsRenderTable() {
        var tbody = document.getElementById('bsTableBody');
        if (!tbody) return;

        bsCloseActionMenus();

        var rows = bsGetFilteredRows();

        var html = rows.map(function(row, idx) {
            var menuId = 'bsMenu' + idx;
            return '' +
                '<tr>' +
                    '<td>' + (idx + 1) + '</td>' +
                    '<td>' + bsEscapeHtml(row.studentId) + '</td>' +
                    '<td>' + bsEscapeHtml(row.name).toUpperCase() + '</td>' +
                    '<td>' + bsEscapeHtml(row.course) + '</td>' +
                    '<td>' + bsEscapeHtml(row.yearLevel) + '</td>' +
                    '<td style="text-align:center;">' + bsBuildMenu(menuId, row.id) + '</td>' +
                '</tr>';
        }).join('');

        if (!html) {
            html = '<tr><td colspan="6" class="sc-empty-row">No student records found.</td></tr>';
        }

        tbody.innerHTML = html + '<tr class="bs-total-row"><td colspan="6">Total Students: <strong>' + rows.length + '</strong></td></tr>';
    }

    function bsGetExportRows() {
        return bsGetFilteredRows().map(function(row, idx) {
            return {
                no: idx + 1,
                studentId: row.studentId,
                name: row.name.toUpperCase(),
                course: row.course,
                yearLevel: row.yearLevel
            };
        });
    }

    function bsDownloadFile(fileName, mimeType, content) {
        var blob = new Blob([content], { type: mimeType });
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function bsExportXls() {
        var rows = bsGetExportRows();
        if (!rows.length) {
            alert('No data available to export.');
            return;
        }

        var lines = [
            ['No.', 'Student ID', 'Student Name', 'Course', 'Yr. Level'].join('\t')
        ];

        rows.forEach(function(row) {
            lines.push([
                row.no,
                row.studentId,
                row.name,
                row.course,
                row.yearLevel
            ].join('\t'));
        });

        bsDownloadFile('bed-student-status.xls', 'application/vnd.ms-excel;charset=utf-8;', lines.join('\n'));
    }

    function bsExportPdf() {
        var rows = bsGetExportRows();
        if (!rows.length) {
            alert('No data available to export.');
            return;
        }

        var win = window.open('', '_blank');
        if (!win) {
            alert('Popup blocked. Allow popups to export PDF.');
            return;
        }

        var tableRows = rows.map(function(row) {
            return '<tr>' +
                '<td style="padding:6px;border:1px solid #d0d0d0;">' + row.no + '</td>' +
                '<td style="padding:6px;border:1px solid #d0d0d0;">' + bsEscapeHtml(row.studentId) + '</td>' +
                '<td style="padding:6px;border:1px solid #d0d0d0;">' + bsEscapeHtml(row.name) + '</td>' +
                '<td style="padding:6px;border:1px solid #d0d0d0;">' + bsEscapeHtml(row.course) + '</td>' +
                '<td style="padding:6px;border:1px solid #d0d0d0;">' + bsEscapeHtml(row.yearLevel) + '</td>' +
            '</tr>';
        }).join('');

        win.document.open();
        win.document.write('<!DOCTYPE html><html><head><title>BED Student Status</title></head><body style="font-family:Arial,sans-serif;padding:16px;">' +
            '<h2 style="margin:0 0 10px;">BED Student Status</h2>' +
            '<p style="margin:0 0 12px;font-size:12px;color:#555;">Generated on ' + new Date().toLocaleString() + '</p>' +
            '<table style="width:100%;border-collapse:collapse;font-size:12px;">' +
                '<thead><tr>' +
                    '<th style="padding:6px;border:1px solid #d0d0d0;background:#f3f5f4;">No.</th>' +
                    '<th style="padding:6px;border:1px solid #d0d0d0;background:#f3f5f4;">Student ID</th>' +
                    '<th style="padding:6px;border:1px solid #d0d0d0;background:#f3f5f4;">Student Name</th>' +
                    '<th style="padding:6px;border:1px solid #d0d0d0;background:#f3f5f4;">Course</th>' +
                    '<th style="padding:6px;border:1px solid #d0d0d0;background:#f3f5f4;">Yr. Level</th>' +
                '</tr></thead>' +
                '<tbody>' + tableRows + '</tbody>' +
            '</table>' +
        '</body></html>');
        win.document.close();
        win.focus();
        win.print();
    }

    function bsCloseExportMenu() {
        var menu = document.getElementById('bsExportMenu');
        if (menu) menu.classList.remove('open');
    }

    function bsToggleExportMenu() {
        var menu = document.getElementById('bsExportMenu');
        if (!menu) return;
        menu.classList.toggle('open');
    }

    function bsOpenEditModal(id) {
        var row = bsRows.find(function(item) { return item.id === id; });
        if (!row) return;

        bsCloseActionMenus();
        document.getElementById('bsEditId').value = row.id;
        document.getElementById('bsEditStudentId').value = row.studentId;
        document.getElementById('bsEditName').value = row.name;
        document.getElementById('bsEditCourse').value = row.course;
        document.getElementById('bsEditYearLevel').value = row.yearLevel;
        document.getElementById('bsEditSection').value = row.section;
        document.getElementById('bsEditModal').style.display = 'flex';
    }

    function bsCloseEditModal() {
        document.getElementById('bsEditModal').style.display = 'none';
    }

    function bsSaveEdit() {
        var id = document.getElementById('bsEditId').value;
        var studentId = (document.getElementById('bsEditStudentId').value || '').trim();
        var name = (document.getElementById('bsEditName').value || '').trim();
        var course = (document.getElementById('bsEditCourse').value || '').trim();
        var yearLevel = document.getElementById('bsEditYearLevel').value;
        var section = document.getElementById('bsEditSection').value;

        if (!studentId || !name || !course) {
            alert('Please fill in Student ID, Student Name, and Course.');
            return;
        }

        bsRows = bsRows.map(function(item) {
            if (item.id !== id) return item;
            return {
                id: item.id,
                studentId: studentId,
                name: name,
                course: course,
                yearLevel: yearLevel,
                section: section
            };
        });

        bsCloseEditModal();
        bsRenderTable();
    }

    function bsOpenDeleteModal(id) {
        bsCloseActionMenus();
        document.getElementById('bsDeleteId').value = id;
        document.getElementById('bsDeleteModal').style.display = 'flex';
    }

    function bsCloseDeleteModal() {
        document.getElementById('bsDeleteModal').style.display = 'none';
    }

    function bsConfirmDelete() {
        var id = document.getElementById('bsDeleteId').value;
        bsRows = bsRows.filter(function(item) { return item.id !== id; });
        bsCloseDeleteModal();
        bsRenderTable();
    }

    document.getElementById('bsSearchBtn').addEventListener('click', bsRenderTable);
    document.getElementById('bsSearch').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            bsRenderTable();
        }
    });
    document.getElementById('bsGradeLevel').addEventListener('change', bsRenderTable);
    document.getElementById('bsSection').addEventListener('change', bsRenderTable);

    document.getElementById('bsExportPdfBtn').addEventListener('click', bsExportPdf);
    document.getElementById('bsExportXlsBtn').addEventListener('click', bsExportXls);
    document.getElementById('bsExportToggleBtn').addEventListener('click', function(event) {
        event.stopPropagation();
        bsToggleExportMenu();
    });

    document.getElementById('bsExportMenu').addEventListener('click', function() {
        bsCloseExportMenu();
    });

    document.addEventListener('click', function(event) {
        var menuToggle = event.target.closest('[data-bs-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            bsToggleActionMenu(menuToggle.getAttribute('data-bs-menu-toggle'), menuToggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            bsCloseActionMenus();
        }

        if (!event.target.closest('.bs-table-export')) {
            bsCloseExportMenu();
        }
    });

    window.addEventListener('scroll', bsCloseActionMenus, true);
    window.addEventListener('scroll', bsCloseExportMenu, true);

    bsRenderTable();
</script>
@endpush








