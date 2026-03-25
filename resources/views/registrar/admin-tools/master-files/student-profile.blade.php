@extends('layouts.registrar')

@section('title', 'PLP - Student Profile')
@section('page-title', 'STUDENT PROFILE')
@section('body-class', request('view') === 'config' ? 'page-student-profile page-student-profile-config' : 'page-student-profile')

@php
    $isConfigMode = request('view') === 'config';
    $cfgStudentId = request('student_id', '2223A8137');
    $cfgStudentName = request('name', 'Bares, Mark Jay');
    $cfgCourse = request('course', 'Bachelor of Science in Computer Science');
    $cfgYearLevel = request('year_level', 'Fourth');
    $cfgLastName = trim(explode(',', $cfgStudentName)[0] ?? '');
    $cfgFirstName = trim(explode(',', $cfgStudentName)[1] ?? '');
@endphp


@section('content')
@if(!$isConfigMode)
<div class="pf-page">
    <div class="sp-page">
        <div class="sp-toolbar-row">
            <div class="sp-toolbar">
                <div class="sp-search-wrap">
                    <label class="app-filter-label" for="spSearch">Search</label>
                    <div class="pf-search-wrap">
                        <span class="pf-search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input id="spSearch" type="text" class="pf-search-input" placeholder="Search Student ID / Name...">
                    </div>
                </div>
                <button type="button" class="pf-btn-new" id="spSearchBtn">Search</button>
            </div>
        </div>

        <section>
            <div class="sp-table-head">
                <button type="button" class="pf-btn-new" id="spNewRecordBtn">+ New Record</button>
            </div>

            <div class="app-table-wrap">
                <table id="spTable" class="app-table cfg-table" data-no-auto-pager="1">
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
                    <tbody id="spTableBody"></tbody>
                </table>
            </div>

            <div class="sp-pager-row">
                <div class="sp-page-list">
                    <button type="button" class="sp-page-btn" disabled aria-label="Previous page">&lsaquo;</button>
                    <button type="button" class="sp-page-num active" aria-current="page">1</button>
                    <button type="button" class="sp-page-btn" disabled aria-label="Next page">&rsaquo;</button>
                </div>
            </div>
        </section>
    </div>
</div>
@else
<div class="pf-page">
    <div class="spc-page">
        <div class="spc-head-row">
            <a href="{{ route('registrar.admin-tools.master-files.student-profile') }}" class="req-btn-cancel spc-back-link">Back to Student Profile List</a>
            <button type="button" class="pf-btn-new spc-view-form-btn">View Student's Application Form</button>
        </div>

        <section class="cfg-card spc-card">
            <div class="spc-grid-top">
                <div class="spc-field">
                    <label class="app-filter-label" for="spcStudentNo">Student No.</label>
                    <input id="spcStudentNo" type="text" class="app-filter-input" value="{{ $cfgStudentId }}">
                </div>
                <div class="spc-field">
                    <label class="app-filter-label" for="spcLastName">Last Name</label>
                    <input id="spcLastName" type="text" class="app-filter-input" value="{{ $cfgLastName }}">
                </div>
                <div class="spc-field">
                    <label class="app-filter-label" for="spcFirstName">First Name</label>
                    <input id="spcFirstName" type="text" class="app-filter-input" value="{{ $cfgFirstName }}">
                </div>
                <div class="spc-field">
                    <label class="app-filter-label" for="spcMiddleName">Middle Name</label>
                    <input id="spcMiddleName" type="text" class="app-filter-input" placeholder="Middle Name">
                </div>
                <div class="spc-field spc-field-sm">
                    <label class="app-filter-label" for="spcSuffix">Suffix</label>
                    <input id="spcSuffix" type="text" class="app-filter-input" placeholder="Suffix">
                </div>
                <label class="spc-photo-box" for="spcPhotoInput" title="Upload student photo">
                    <input id="spcPhotoInput" type="file" accept="image/*" class="spc-photo-input">
                    <div class="spc-photo-icon" id="spcPhotoIcon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h4l2-2h4l2 2h4v12H4z"></path><circle cx="12" cy="13" r="3"></circle></svg>
                    </div>
                    <img id="spcPhotoPreview" class="spc-photo-preview" alt="Student photo preview" style="display:none;">
                    <div class="spc-photo-text" id="spcPhotoText">Upload Photo</div>
                </label>
            </div>

            <div class="spc-grid-contact">
                <div class="spc-field"><label class="app-filter-label" for="spcContact">Contact No.</label><input id="spcContact" type="text" class="app-filter-input" placeholder="+63"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcEmail">Email Add</label><input id="spcEmail" type="email" class="app-filter-input" placeholder="test@gmail.com"></div>
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcResidential">Residential Address</label><input id="spcResidential" type="text" class="app-filter-input" placeholder="House No., Street, Barangay, Municipality, Province"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcRegion">Region</label><input id="spcRegion" type="text" class="app-filter-input" placeholder="Region"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcProvince">Province</label><input id="spcProvince" type="text" class="app-filter-input" placeholder="Province"></div>
            </div>

            <div class="spc-grid-birth">
                <div class="spc-field"><label class="app-filter-label" for="spcMunicipality">Municipality</label><input id="spcMunicipality" type="text" class="app-filter-input" placeholder="Municipality"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcBirthDate">Date of Birth</label><input id="spcBirthDate" type="text" class="app-filter-input" placeholder="mm/dd/yy"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcBirthPlace">Place of Birth</label><input id="spcBirthPlace" type="text" class="app-filter-input" placeholder="Birthplace"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcCitizenship">Citizenship</label><input id="spcCitizenship" type="text" class="app-filter-input" value="Filipino"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcReligion">Religion</label><input id="spcReligion" type="text" class="app-filter-input" value="Roman Catholic"></div>
            </div>

            <div class="spc-grid-address">
                <div class="spc-field"><label class="app-filter-label" for="spcBlood">Blood Type</label><input id="spcBlood" type="text" class="app-filter-input" placeholder="A"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcCivil">Civil Status</label><input id="spcCivil" type="text" class="app-filter-input" placeholder="Single"></div>
                <label class="spc-checkline"><input id="spcSameAddress" type="checkbox" class="req-checkbox-input"> Same as Residential Address</label>
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcPermanent">Permanent Address</label><input id="spcPermanent" type="text" class="app-filter-input" placeholder="House No., Street, Barangay, Municipality, Province"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcPermRegion">Region</label><input id="spcPermRegion" type="text" class="app-filter-input" placeholder="Region"></div>
            </div>

            <div class="spc-divider"></div>

            <div class="spc-grid-acad">
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcProgram">Program</label><input id="spcProgram" type="text" class="app-filter-input" value="{{ $cfgCourse }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcYear">Year Level</label><input id="spcYear" type="text" class="app-filter-input" value="{{ $cfgYearLevel }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcSection">Section</label><input id="spcSection" type="text" class="app-filter-input" value="A"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcCurriculum">Curriculum Year</label><input id="spcCurriculum" type="text" class="app-filter-input" value="Fourth"></div>
            </div>

            <div class="spc-grid-status">
                <div class="spc-field"><label class="app-filter-label" for="spcAdmissionYear">Admission Year</label><input id="spcAdmissionYear" type="text" class="app-filter-input" value="Fourth"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcAdmissionStatus">Admission Status</label><input id="spcAdmissionStatus" type="text" class="app-filter-input" value="Old"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcEnrollmentStatus">Enrollment Status</label><input id="spcEnrollmentStatus" type="text" class="app-filter-input" value="Old"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcAcademicStatus">Academic Status</label><input id="spcAcademicStatus" type="text" class="app-filter-input" value="Regular"></div>
            </div>

            <div class="spc-transfer-row">
                <label class="spc-checkline"><input type="checkbox" class="req-checkbox-input"> Tag this Student as Transfer.</label>
            </div>

            <div class="spc-grid-transfer">
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcSchoolName">Name of School</label><input id="spcSchoolName" type="text" class="app-filter-input" placeholder="School Name"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcDateTransferred">Date Transferred</label><input id="spcDateTransferred" type="text" class="app-filter-input" placeholder="mm/dd/yy"></div>
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcReasons">Reasons</label><input id="spcReasons" type="text" class="app-filter-input" placeholder="-Reasons-"></div>
            </div>

            <div class="spc-actions">
                <button type="button" class="pf-btn-new">Save</button>
            </div>
        </section>
    </div>
</div>
@endif

<div class="req-modal-overlay" id="spFormModal" style="display:none;" onclick="if(event.target===this) spCloseFormModal()">
    <div class="req-modal-box" style="max-width:760px;">
        <h3 class="req-modal-title" id="spFormTitle">ADD STUDENT PROFILE</h3>
        <input type="hidden" id="spEditingId" value="">

        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student ID</label>
                <input id="spIdInput" type="text" class="req-modal-input" placeholder="e.g. 2223A8137">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year Level</label>
                <select id="spYearInput" class="req-modal-input">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                    <option value="Fourth">Fourth</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input id="spCourseInput" type="text" class="req-modal-input" placeholder="Course">
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Student Name</label>
                <input id="spNameInput" type="text" class="req-modal-input" placeholder="Last Name, First Name">
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="spCloseFormModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="spSaveRecord()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="spDeleteModal" style="display:none;" onclick="if(event.target===this) spCloseDeleteModal()">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE STUDENT PROFILE</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this record?</p>
        <input type="hidden" id="spDeleteId" value="">
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="spCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="spConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@if(!$isConfigMode)
@push('scripts')
<script>
    var spRows = [
        { id: 'sp-1', studentId: '2223A8137', name: 'Bares, Mark Jay', course: 'Bachelor of Science in Computer Science', yearLevel: 'Fourth' },
        { id: 'sp-2', studentId: '2223A8138', name: 'Austero, Andrea Jane', course: 'Bachelor of Science in Computer Science', yearLevel: 'Fourth' },
        { id: 'sp-3', studentId: '2223A8141', name: 'Dela Cruz, Juan', course: 'Bachelor of Science in Information Technology', yearLevel: 'Third' },
        { id: 'sp-4', studentId: '2223A8148', name: 'Rivera, Angelo', course: 'Bachelor of Science in Computer Engineering', yearLevel: 'Second' },
        { id: 'sp-5', studentId: '2223A8154', name: 'Santos, Maria', course: 'Bachelor of Science in Information Systems', yearLevel: 'Second' },
        { id: 'sp-6', studentId: '2223A8160', name: 'Benedict, John', course: 'Bachelor of Science in Computer Science', yearLevel: 'First' }
    ];

    function spEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function spGetFilteredRows() {
        var query = (document.getElementById('spSearch').value || '').toLowerCase();
        return spRows.filter(function(row) {
            if (!query) return true;
            return row.studentId.toLowerCase().indexOf(query) !== -1 ||
                row.name.toLowerCase().indexOf(query) !== -1 ||
                row.course.toLowerCase().indexOf(query) !== -1 ||
                row.yearLevel.toLowerCase().indexOf(query) !== -1;
        });
    }

    function spBuildMenu(menuId, id) {
        return '' +
            '<div class="apst-action-btn" data-sp-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="spOpenEditModal(\'' + spEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" onclick="spOpenDeleteModal(\'' + spEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>Delete</button>' +
            '</div>';
    }

    function spCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function spToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        spCloseActionMenus();
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

    function spRenderTable() {
        var tbody = document.getElementById('spTableBody');
        if (!tbody) return;

        spCloseActionMenus();

        var rows = spGetFilteredRows();
        var bodyRows = rows.map(function(row, idx) {
            var menuId = 'spMenu' + idx;
            return '' +
                '<tr data-sp-id="' + spEscapeHtml(row.id) + '">' +
                    '<td>' + (idx + 1) + '</td>' +
                    '<td>' + spEscapeHtml(row.studentId) + '</td>' +
                    '<td>' + spEscapeHtml(row.name).toUpperCase() + '</td>' +
                    '<td>' + spEscapeHtml(row.course) + '</td>' +
                    '<td>' + spEscapeHtml(row.yearLevel) + '</td>' +
                    '<td style="text-align:center;">' + spBuildMenu(menuId, row.id) + '</td>' +
                '</tr>';
        }).join('');

        if (!bodyRows) {
            bodyRows = '<tr><td colspan="6" class="sc-empty-row">No student profiles found.</td></tr>';
        }

        tbody.innerHTML = bodyRows +
            '<tr class="sp-total-row"><td colspan="6">Total Students: <strong>' + rows.length + '</strong></td></tr>';
    }

    function spOpenAddModal() {
        document.getElementById('spFormTitle').textContent = 'ADD STUDENT PROFILE';
        document.getElementById('spEditingId').value = '';
        document.getElementById('spIdInput').value = '';
        document.getElementById('spNameInput').value = '';
        document.getElementById('spCourseInput').value = '';
        document.getElementById('spYearInput').value = 'First';
        spCloseActionMenus();
        document.getElementById('spFormModal').style.display = 'flex';
    }

    function spOpenEditModal(id) {
        var row = spRows.find(function(item) { return item.id === id; });
        if (!row) return;

        document.getElementById('spFormTitle').textContent = 'EDIT STUDENT PROFILE';
        document.getElementById('spEditingId').value = row.id;
        document.getElementById('spIdInput').value = row.studentId;
        document.getElementById('spNameInput').value = row.name;
        document.getElementById('spCourseInput').value = row.course;
        document.getElementById('spYearInput').value = row.yearLevel;
        spCloseActionMenus();
        document.getElementById('spFormModal').style.display = 'flex';
    }

    function spCloseFormModal() {
        document.getElementById('spFormModal').style.display = 'none';
    }

    function spSaveRecord() {
        var editingId = document.getElementById('spEditingId').value;
        var studentId = document.getElementById('spIdInput').value.trim();
        var name = document.getElementById('spNameInput').value.trim();
        var course = document.getElementById('spCourseInput').value.trim();
        var yearLevel = document.getElementById('spYearInput').value;

        if (!studentId || !name || !course) {
            alert('Please fill in Student ID, Student Name, and Course.');
            return;
        }

        if (!editingId) {
            spRows.unshift({
                id: 'sp-' + Date.now(),
                studentId: studentId,
                name: name,
                course: course,
                yearLevel: yearLevel
            });
        } else {
            spRows = spRows.map(function(item) {
                if (item.id !== editingId) return item;
                return {
                    id: item.id,
                    studentId: studentId,
                    name: name,
                    course: course,
                    yearLevel: yearLevel
                };
            });
        }

        spCloseFormModal();
        spRenderTable();
    }

    function spOpenDeleteModal(id) {
        spCloseActionMenus();
        document.getElementById('spDeleteId').value = id;
        document.getElementById('spDeleteModal').style.display = 'flex';
    }

    function spCloseDeleteModal() {
        document.getElementById('spDeleteModal').style.display = 'none';
    }

    function spConfirmDelete() {
        var id = document.getElementById('spDeleteId').value;
        spRows = spRows.filter(function(item) { return item.id !== id; });
        spCloseDeleteModal();
        spRenderTable();
    }

    document.getElementById('spSearchBtn').addEventListener('click', spRenderTable);
    document.getElementById('spSearch').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            spRenderTable();
        }
    });
    document.getElementById('spNewRecordBtn').addEventListener('click', spOpenAddModal);

    document.addEventListener('click', function(event) {
        var menuToggle = event.target.closest('[data-sp-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            spToggleActionMenu(menuToggle.getAttribute('data-sp-menu-toggle'), menuToggle);
            return;
        }

        var row = event.target.closest('#spTableBody tr[data-sp-id]');
        if (row && !event.target.closest('.apst-dropdown') && !event.target.closest('.apst-action-btn')) {
            var rowId = row.getAttribute('data-sp-id');
            var selected = spRows.find(function(item) { return item.id === rowId; });
            if (selected) {
                var url = '{{ route('registrar.admin-tools.master-files.student-profile') }}' +
                    '?view=config' +
                    '&student_id=' + encodeURIComponent(selected.studentId || '') +
                    '&name=' + encodeURIComponent(selected.name || '') +
                    '&course=' + encodeURIComponent(selected.course || '') +
                    '&year_level=' + encodeURIComponent(selected.yearLevel || '');
                window.location.href = url;
            }
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            spCloseActionMenus();
        }
    });

    window.addEventListener('scroll', spCloseActionMenus, true);

    spRenderTable();
</script>
@endpush
@else
@push('scripts')
<script>
    (function () {
        var input = document.getElementById('spcPhotoInput');
        var preview = document.getElementById('spcPhotoPreview');
        var text = document.getElementById('spcPhotoText');
        var icon = document.getElementById('spcPhotoIcon');
        if (!input || !preview || !text || !icon) return;

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) {
                preview.style.display = 'none';
                preview.removeAttribute('src');
                icon.style.display = '';
                text.textContent = 'Upload Photo';
                return;
            }

            if (!/^image\//.test(file.type)) {
                alert('Please select a valid image file.');
                input.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
                text.textContent = 'Change Photo';
            };
            reader.readAsDataURL(file);
        });
    })();
</script>
@endpush
@endif



