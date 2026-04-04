@extends('layouts.registrar')

@section('title', 'PLP - Student Grade File')
@section('page-title', 'STUDENT GRADE FILE')
@section('body-class', 'page-student-grade-file')



@section('content')
<div class="pf-page">
    <div class="sgf-page">
        <div class="sgf-toolbar-row">
            <div class="sgf-toolbar">
                <div class="sgf-search-wrap">
                    <label class="app-filter-label" for="sgfSearch">Search</label>
                    <div class="pf-search-wrap">
                        <span class="pf-search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input id="sgfSearch" type="text" class="pf-search-input" placeholder="Search Student ID / Name...">
                    </div>
                </div>
                <button type="button" class="pf-btn-new" id="sgfSearchBtn">Search</button>
            </div>
        </div>

        <section>
            <div class="sgf-table-head">
                <button type="button" class="pf-btn-new" id="sgfNewRecordBtn">+ New Record</button>
            </div>

            <div class="app-table-wrap">
                <table id="sgfTable" class="app-table cfg-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Yr. Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sgfTableBody"></tbody>
                </table>
            </div>

            <div class="app-table-pager"></div>
        </section>
    </div>
</div>

<div class="req-modal-overlay" id="sgfFormModal" style="display:none;" onclick="if(event.target===this) sgfCloseFormModal()">
    <div class="req-modal-box" style="max-width:760px;">
        <h3 class="req-modal-title" id="sgfFormTitle">ADD STUDENT GRADE FILE RECORD</h3>
        <input type="hidden" id="sgfEditingId" value="">

        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student ID</label>
                <input id="sgfIdInput" type="text" class="req-modal-input" placeholder="e.g. 2223A8137">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year Level</label>
                <select id="sgfYearInput" class="req-modal-input">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                    <option value="Fourth">Fourth</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input id="sgfCourseInput" type="text" class="req-modal-input" placeholder="Course">
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Student Name</label>
                <input id="sgfNameInput" type="text" class="req-modal-input" placeholder="Last Name, First Name">
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="sgfCloseFormModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="sgfSaveRecord()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sgfDeleteModal" style="display:none;" onclick="if(event.target===this) sgfCloseDeleteModal()">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE STUDENT GRADE RECORD</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this record?</p>
        <input type="hidden" id="sgfDeleteId" value="">
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="sgfCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="sgfConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var sgfRows = @json($sgfRows ?? []);
    var sgfCsrf = '{{ csrf_token() }}';
    var sgfApi = {
        store: '{{ route('registrar.admin-tools.master-files.student-grade-file.store') }}',
        updateTemplate: '{{ route('registrar.admin-tools.master-files.student-grade-file.update', ['masterStudentGradeFile' => '__ID__']) }}',
        destroyTemplate: '{{ route('registrar.admin-tools.master-files.student-grade-file.destroy', ['masterStudentGradeFile' => '__ID__']) }}'
    };
    var sgfCurrentPage = 1;
    var sgfPageSize = 10;

    function sgfBuildUrl(template, id) {
        return template.replace('__ID__', encodeURIComponent(String(id)));
    }

    function sgfRequest(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': sgfCsrf,
                'Accept': 'application/json'
            },
            body: payload ? JSON.stringify(payload) : null
        }).then(function(response) {
            return response.json().catch(function() { return {}; }).then(function(data) {
                if (!response.ok || data.ok === false) {
                    var message = (data && data.message) ? data.message : 'Request failed.';
                    if (data && data.errors) {
                        var firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
                            message = data.errors[firstKey][0];
                        }
                    }
                    throw new Error(message);
                }
                return data;
            });
        });
    }

    function sgfEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function sgfGetFilteredRows() {
        var query = (document.getElementById('sgfSearch').value || '').toLowerCase();
        return sgfRows.filter(function(row) {
            if (!query) return true;
            return row.studentId.toLowerCase().indexOf(query) !== -1 ||
                row.name.toLowerCase().indexOf(query) !== -1 ||
                row.course.toLowerCase().indexOf(query) !== -1 ||
                row.yearLevel.toLowerCase().indexOf(query) !== -1;
        });
    }

    function sgfBuildMenu(menuId, id) {
        return '' +
            '<div class="apst-action-btn" data-sgf-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="sgfOpenEditModal(\'' + sgfEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" onclick="sgfOpenDeleteModal(\'' + sgfEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>Delete</button>' +
            '</div>';
    }

    function sgfCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function sgfToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        sgfCloseActionMenus();
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

    function sgfRenderPager(totalRows) {
        var mount = document.querySelector('.app-table-pager');
        if (!mount) return;

        var maxPage = Math.max(1, Math.ceil(totalRows / sgfPageSize));
        if (sgfCurrentPage > maxPage) {
            sgfCurrentPage = maxPage;
        }

        if (totalRows <= sgfPageSize) {
            mount.innerHTML = '';
            return;
        }

        var start = Math.max(1, sgfCurrentPage - 2);
        var end = Math.min(maxPage, sgfCurrentPage + 2);
        if (sgfCurrentPage <= 3) {
            end = Math.min(maxPage, 5);
        } else if (sgfCurrentPage >= maxPage - 2) {
            start = Math.max(1, maxPage - 4);
        }

        var pageNums = '';
        for (var p = start; p <= end; p += 1) {
            pageNums += '<button type="button" class="rtp-page-num ' + (p === sgfCurrentPage ? 'active' : '') + '" data-sgf-page="' + p + '">' + p + '</button>';
        }

        mount.innerHTML = '' +
            '<div class="rtp-pagination">' +
                '<nav class="rtp-nav" aria-label="Student grade file pagination">' +
                    '<div class="rtp-list" role="group" aria-label="Page controls">' +
                        '<button type="button" class="rtp-page-btn" data-sgf-page-prev="1" ' + (sgfCurrentPage <= 1 ? 'disabled' : '') + '>&lt;</button>' +
                        pageNums +
                        '<button type="button" class="rtp-page-btn" data-sgf-page-next="1" ' + (sgfCurrentPage >= maxPage ? 'disabled' : '') + '>&gt;</button>' +
                    '</div>' +
                '</nav>' +
            '</div>';
    }

    function sgfRenderTable() {
        var tbody = document.getElementById('sgfTableBody');
        if (!tbody) return;

        sgfCloseActionMenus();

        var rows = sgfGetFilteredRows();
        var maxPage = Math.max(1, Math.ceil(rows.length / sgfPageSize));
        if (sgfCurrentPage > maxPage) {
            sgfCurrentPage = 1;
        }
        var startIndex = (sgfCurrentPage - 1) * sgfPageSize;
        var pageItems = rows.slice(startIndex, startIndex + sgfPageSize);

        var bodyRows = pageItems.map(function(row, idx) {
            var menuId = 'sgfMenu' + idx;
            return '' +
                '<tr>' +
                    '<td>' + (startIndex + idx + 1) + '</td>' +
                    '<td>' + sgfEscapeHtml(row.studentId) + '</td>' +
                    '<td>' + sgfEscapeHtml(row.name).toUpperCase() + '</td>' +
                    '<td>' + sgfEscapeHtml(row.course) + '</td>' +
                    '<td>' + sgfEscapeHtml(row.yearLevel) + '</td>' +
                    '<td style="text-align:center;">' + sgfBuildMenu(menuId, row.id) + '</td>' +
                '</tr>';
        }).join('');

        if (!bodyRows) {
            bodyRows = '<tr><td colspan="6" class="sc-empty-row">No student grade records found.</td></tr>';
        }

        tbody.innerHTML = bodyRows +
            '<tr class="sgf-total-row"><td colspan="6">Total Students: <strong>' + rows.length + '</strong></td></tr>';
        sgfRenderPager(rows.length);
    }

    function sgfOpenAddModal() {
        document.getElementById('sgfFormTitle').textContent = 'ADD STUDENT GRADE FILE RECORD';
        document.getElementById('sgfEditingId').value = '';
        document.getElementById('sgfIdInput').value = '';
        document.getElementById('sgfNameInput').value = '';
        document.getElementById('sgfCourseInput').value = '';
        document.getElementById('sgfYearInput').value = 'First';
        sgfCloseActionMenus();
        document.getElementById('sgfFormModal').style.display = 'flex';
    }

    function sgfOpenEditModal(id) {
        var row = sgfRows.find(function(item) { return String(item.id) === String(id); });
        if (!row) return;

        document.getElementById('sgfFormTitle').textContent = 'EDIT STUDENT GRADE FILE RECORD';
        document.getElementById('sgfEditingId').value = row.id;
        document.getElementById('sgfIdInput').value = row.studentId;
        document.getElementById('sgfNameInput').value = row.name;
        document.getElementById('sgfCourseInput').value = row.course;
        document.getElementById('sgfYearInput').value = row.yearLevel;
        sgfCloseActionMenus();
        document.getElementById('sgfFormModal').style.display = 'flex';
    }

    function sgfCloseFormModal() {
        document.getElementById('sgfFormModal').style.display = 'none';
    }

    function sgfSaveRecord() {
        var editingId = document.getElementById('sgfEditingId').value;
        var studentId = document.getElementById('sgfIdInput').value.trim();
        var name = document.getElementById('sgfNameInput').value.trim();
        var course = document.getElementById('sgfCourseInput').value.trim();
        var yearLevel = document.getElementById('sgfYearInput').value;

        if (!studentId || !name || !course) {
            alert('Please fill in Student ID, Student Name, and Course.');
            return;
        }

        var payload = {
            student_id: studentId,
            name: name,
            course: course,
            year_level: yearLevel
        };

        var request = !editingId
            ? sgfRequest(sgfApi.store, 'POST', payload)
            : sgfRequest(sgfBuildUrl(sgfApi.updateTemplate, editingId), 'PUT', payload);

        request.then(function(data) {
            if (!editingId) {
                sgfRows.unshift(data.row);
            } else {
                sgfRows = sgfRows.map(function(item) {
                    return String(item.id) === String(editingId) ? data.row : item;
                });
            }

            sgfCloseFormModal();
            sgfRenderTable();
        }).catch(function(error) {
            alert(error.message || 'Unable to save student grade record.');
        });
    }

    function sgfOpenDeleteModal(id) {
        sgfCloseActionMenus();
        document.getElementById('sgfDeleteId').value = id;
        document.getElementById('sgfDeleteModal').style.display = 'flex';
    }

    function sgfCloseDeleteModal() {
        document.getElementById('sgfDeleteModal').style.display = 'none';
    }

    function sgfConfirmDelete() {
        var id = document.getElementById('sgfDeleteId').value;
        sgfRequest(sgfBuildUrl(sgfApi.destroyTemplate, id), 'DELETE', null).then(function() {
            sgfRows = sgfRows.filter(function(item) {
                return String(item.id) !== String(id);
            });
            sgfCloseDeleteModal();
            sgfRenderTable();
        }).catch(function(error) {
            alert(error.message || 'Unable to delete student grade record.');
        });
    }

    document.getElementById('sgfSearchBtn').addEventListener('click', function() {
        sgfCurrentPage = 1;
        sgfRenderTable();
    });
    document.getElementById('sgfSearch').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            sgfCurrentPage = 1;
            sgfRenderTable();
        }
    });
    document.getElementById('sgfNewRecordBtn').addEventListener('click', sgfOpenAddModal);

    document.addEventListener('click', function(event) {
        var menuToggle = event.target.closest('[data-sgf-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            sgfToggleActionMenu(menuToggle.getAttribute('data-sgf-menu-toggle'), menuToggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            sgfCloseActionMenus();
        }
    });

    window.addEventListener('scroll', sgfCloseActionMenus, true);

    document.querySelector('.app-table-pager').addEventListener('click', function(event) {
        var prev = event.target.closest('[data-sgf-page-prev]');
        if (prev && sgfCurrentPage > 1) {
            sgfCurrentPage -= 1;
            sgfRenderTable();
            return;
        }

        var next = event.target.closest('[data-sgf-page-next]');
        if (next) {
            var total = sgfGetFilteredRows().length;
            var maxPage = Math.max(1, Math.ceil(total / sgfPageSize));
            if (sgfCurrentPage < maxPage) {
                sgfCurrentPage += 1;
                sgfRenderTable();
            }
            return;
        }

        var pageBtn = event.target.closest('[data-sgf-page]');
        if (pageBtn) {
            sgfCurrentPage = parseInt(pageBtn.getAttribute('data-sgf-page'), 10) || 1;
            sgfRenderTable();
        }
    });

    sgfRenderTable();
</script>
@endpush



