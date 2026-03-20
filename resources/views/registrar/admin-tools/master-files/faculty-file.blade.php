@extends('layouts.registrar')

@section('title', 'PLP - Faculty File')
@section('page-title', 'FACULTY FILE')
@section('body-class', request('view') === 'config' ? 'page-faculty-file page-faculty-file-config' : 'page-faculty-file')

@php
    $isConfigMode = request('view') === 'config';
    $cfgCode = request('code', '01A');
    $cfgName = request('name', 'Dela Cruz, Juan');
    $cfgDepartment = request('department', 'Computer Studies');
    $cfgStatus = request('status', 'Active');
@endphp


@section('content')
@if(!$isConfigMode)
<div class="pf-page">
    <div class="ff-page">
        <div class="ff-toolbar-row">
            <div class="ff-toolbar">
                <div class="ff-search-wrap">
                    <label class="app-filter-label" for="ffSearch">Search</label>
                    <div class="pf-search-wrap">
                        <span class="pf-search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input id="ffSearch" type="text" class="pf-search-input" placeholder="Search Faculty Name/Code/Status...">
                    </div>
                </div>
                <button type="button" class="pf-btn-new" id="ffSearchBtn">Search</button>
            </div>
        </div>

        <section>
            <div class="ff-table-head">
                <button type="button" class="pf-btn-new" id="ffNewRecordBtn">+ New Record</button>
            </div>

            <div class="app-table-wrap">
        <table id="ffTable" class="app-table cfg-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Faculty Code</th>
                            <th>Faculty Name</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="ffTableBody"></tbody>
                </table>
            </div>

            <div class="ff-pager-row">
                <div class="ff-page-list">
                    <button type="button" class="ff-page-btn" disabled aria-label="Previous page">&lsaquo;</button>
                    <button type="button" class="ff-page-num active" aria-current="page">1</button>
                    <button type="button" class="ff-page-btn" disabled aria-label="Next page">&rsaquo;</button>
                </div>
            </div>
        </section>
    </div>
</div>
@else
<div class="pf-page">
    <div class="ffc-page">
        <div class="ffc-head-row">
            <a href="{{ route('registrar.admin-tools.master-files.faculty-file') }}" class="req-btn-cancel ffc-back-link">Back to Faculty List</a>
        </div>

        <section class="cfg-card ffc-card">
            <div class="ffc-section-title">Add Faculty</div>

            <div class="ffc-grid-top">
                <div class="ffc-field"><label class="app-filter-label" for="ffcFacultyName">Faculty Name</label><input id="ffcFacultyName" class="app-filter-input" type="text" value="{{ $cfgName }}"></div>
                <div class="ffc-field"><label class="app-filter-label" for="ffcFacultyCode">Faculty Code</label><input id="ffcFacultyCode" class="app-filter-input" type="text" value="{{ $cfgCode }}"></div>
                <div class="ffc-field"><label class="app-filter-label" for="ffcFacultyStatusTop">Status</label><select id="ffcFacultyStatusTop" class="app-filter-select"><option value="Active" @if($cfgStatus === 'Active') selected @endif>Active</option><option value="Inactive" @if($cfgStatus === 'Inactive') selected @endif>Inactive</option></select></div>
            </div>

            <div class="ffc-grid-profile">
                <div class="ffc-field"><label class="app-filter-label" for="ffcPosition">Position</label><select id="ffcPosition" class="app-filter-select"><option value="">-select-</option><option>Instructor</option><option>Assistant Professor</option><option>Associate Professor</option><option>Professor</option></select></div>
                <div class="ffc-field"><label class="app-filter-label" for="ffcOffice">Office Department</label><select id="ffcOffice" class="app-filter-select"><option>{{ $cfgDepartment }}</option><option>Computer Studies</option><option>Engineering</option><option>Business Administration</option><option>Education</option></select></div>
                <div class="ffc-field"><label class="app-filter-label" for="ffcEmploymentStatus">Employment Status</label><select id="ffcEmploymentStatus" class="app-filter-select"><option>-select status-</option><option>Regular</option><option>Probationary</option></select></div>
            </div>

            <div class="ffc-subtitle">Employment Data</div>
            <div class="ffc-grid-main">
                <div class="ffc-field ffc-field-grow"><label class="app-filter-label">Degree/Title</label><input class="app-filter-input" type="text" placeholder="Input Degree"></div>
                <div class="ffc-field"><label class="app-filter-label">Years of Experience</label><input class="app-filter-input" type="text" placeholder="No. of Years"></div>
                <div class="ffc-field"><label class="app-filter-label">Employment Classification</label><select class="app-filter-select"><option>-select classification-</option><option>Permanent</option><option>Contractual</option><option>Part-time</option></select></div>
                <div class="ffc-field"><label class="app-filter-label">Employment Status</label><select class="app-filter-select"><option>-select status-</option><option>Regular</option><option>Probationary</option></select></div>
                <div class="ffc-field"><label class="app-filter-label">Accreditation</label><select class="app-filter-select"><option>-select accreditation-</option><option>Level 1</option><option>Level 2</option><option>Level 3</option></select></div>
                <div class="ffc-field"><label class="app-filter-label">Regular Load</label><input class="app-filter-input" type="text" placeholder="Input"></div>
                <div class="ffc-field"><label class="app-filter-label">Overload</label><input class="app-filter-input" type="text" placeholder="Input"></div>
                <div class="ffc-field"><label class="app-filter-label">Professional Licensure Passed</label><input class="app-filter-input" type="text" placeholder="Licensure"></div>
                <div class="ffc-field"><label class="app-filter-label">Annual Salary</label><select class="app-filter-select"><option>-select annual salary-</option></select></div>
                <div class="ffc-field"><label class="app-filter-label">Date of Employment</label><input class="app-filter-input" type="text" placeholder="mm/dd/yy"></div>
                <div class="ffc-field"><label class="app-filter-label">Date of Regular Status</label><input class="app-filter-input" type="text" placeholder="mm/dd/yy"></div>
            </div>

            <div class="ffc-divider"></div>

            <div class="ffc-subtitle">Personal Data</div>
            <div class="ffc-grid-personal">
                <div class="ffc-field"><label class="app-filter-label">Contact Number</label><input class="app-filter-input" type="text" placeholder="+63 9"></div>
                <div class="ffc-field ffc-field-grow"><label class="app-filter-label">Current Address</label><input class="app-filter-input" type="text" placeholder="Address"></div>
                <div class="ffc-field"><label class="app-filter-label">Date of Birth</label><input class="app-filter-input" type="text" placeholder="mm/dd/yy"></div>
                <div class="ffc-field"><label class="app-filter-label">Gender</label><select class="app-filter-select"><option></option><option>Male</option><option>Female</option></select></div>
                <div class="ffc-field"><label class="app-filter-label">Citizenship</label><input class="app-filter-input" type="text" placeholder="-nationality-"></div>
                <div class="ffc-field"><label class="app-filter-label">Email Address</label><input class="app-filter-input" type="text" placeholder="Enter Email"></div>
                <div class="ffc-field"><label class="app-filter-label">Place of Birth</label><input class="app-filter-input" type="text" placeholder="Birthplace"></div>
                <div class="ffc-field"><label class="app-filter-label">Religion</label><select class="app-filter-select"><option>-select religion-</option></select></div>
                <div class="ffc-field"><label class="app-filter-label">Civil Status</label><select class="app-filter-select"><option>-select status-</option><option>Single</option><option>Married</option></select></div>
                <div class="ffc-field"><label class="app-filter-label">SSS#</label><input class="app-filter-input" type="text" placeholder="SSS #"></div>
                <div class="ffc-field"><label class="app-filter-label">PhilHealth#</label><input class="app-filter-input" type="text" placeholder="Philhealth #"></div>
                <div class="ffc-field"><label class="app-filter-label">TIN #</label><input class="app-filter-input" type="text" placeholder="TIN #"></div>
                <div class="ffc-field"><label class="app-filter-label">PAG-IBIG #</label><input class="app-filter-input" type="text" placeholder="Pag-ibig #"></div>
                <div class="ffc-field"><label class="app-filter-label">LET Expiration Date</label><input class="app-filter-input" type="text" placeholder="mm/dd/yy"></div>
                <div class="ffc-field"><label class="app-filter-label">Name of Spouse</label><input class="app-filter-input" type="text" placeholder="Name"></div>
                <div class="ffc-field"><label class="app-filter-label">Spouse's Occupation</label><input class="app-filter-input" type="text" placeholder="His/Her Occupation"></div>
                <div class="ffc-field"><label class="app-filter-label">No. of Dependents</label><input class="app-filter-input" type="text" placeholder="Dependents"></div>
                <div class="ffc-field ffc-field-grow"><label class="app-filter-label">Address</label><input class="app-filter-input" type="text" placeholder="Complete Address"></div>
            </div>

            <div class="ffc-subtitle">Parent's Information</div>
            <div class="ffc-grid-parent">
                <div class="ffc-field"><label class="app-filter-label">Father's Name</label><input class="app-filter-input" type="text" placeholder="Name"></div>
                <div class="ffc-field"><label class="app-filter-label">Occupation</label><input class="app-filter-input" type="text" placeholder="occupation"></div>
                <div class="ffc-field"><label class="app-filter-label">Address</label><input class="app-filter-input" type="text" placeholder="occupation"></div>
                <div class="ffc-field"><label class="app-filter-label">Mother's Name</label><input class="app-filter-input" type="text" placeholder="Name"></div>
                <div class="ffc-field"><label class="app-filter-label">Occupation</label><input class="app-filter-input" type="text" placeholder="occupation"></div>
                <div class="ffc-field"><label class="app-filter-label">Address</label><input class="app-filter-input" type="text" placeholder="occupation"></div>
            </div>

            <div class="ffc-divider"></div>

            <div class="ffc-list-head">
                <h4 class="ffc-list-title">Educational Background</h4>
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="education">+ Add</button>
            </div>

            <div class="app-table-wrap ffc-table-wrap">
                <table id="ffcEducationTable" class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>School Level</th>
                            <th>School Name</th>
                            <th>Course/Degree</th>
                            <th>Date Graduated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="ffcEducationBody"></tbody>
                </table>
            </div>

            <div class="ffc-list-head">
                <h4 class="ffc-list-title">Professional Registration</h4>
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="registration">+ Add</button>
            </div>

            <div class="app-table-wrap ffc-table-wrap">
                <table id="ffcRegistrationTable" class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>Name of Professional Registration</th>
                            <th>Rating</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="ffcRegistrationBody"></tbody>
                </table>
            </div>

            <div class="ffc-list-head">
                <h4 class="ffc-list-title">Professional Organization</h4>
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="organization">+ Add</button>
            </div>

            <div class="app-table-wrap ffc-table-wrap">
                <table id="ffcOrganizationTable" class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>Name of Professional Org.</th>
                            <th>Name of Org.</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="ffcOrganizationBody"></tbody>
                </table>
            </div>

            <div class="ffc-list-head">
                <h4 class="ffc-list-title">Work Experience</h4>
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="work">+ Add</button>
            </div>

            <div class="app-table-wrap ffc-table-wrap">
                <table id="ffcWorkTable" class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>Work Experience (Position)</th>
                            <th>Company Name</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="ffcWorkBody"></tbody>
                </table>
            </div>

            <div class="ffc-list-head">
                <h4 class="ffc-list-title">Trainings/Seminar Attended</h4>
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="training">+ Add</button>
            </div>

            <div class="app-table-wrap ffc-table-wrap">
                <table id="ffcTrainingTable" class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>Training(s)/Seminar(s)</th>
                            <th>Place</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="ffcTrainingBody"></tbody>
                </table>
            </div>

            <div class="ffc-actions">
                <button type="button" class="req-btn-cancel">Cancel</button>
                <button type="button" class="pf-btn-new">Save</button>
            </div>
        </section>
    </div>
</div>
@endif

@if(!$isConfigMode)
<div class="req-modal-overlay" id="ffFormModal" style="display:none;" onclick="if(event.target===this) ffCloseFormModal()">
    <div class="req-modal-box" style="max-width:640px;">
        <h3 class="req-modal-title" id="ffFormTitle">ADD FACULTY RECORD</h3>
        <input type="hidden" id="ffEditingId" value="">

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Faculty Code</label>
                <input id="ffCodeInput" type="text" class="req-modal-input" placeholder="e.g. 01A">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Status</label>
                <select id="ffStatusInput" class="req-modal-input">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Faculty Name</label>
                <input id="ffNameInput" type="text" class="req-modal-input" placeholder="Last Name, First Name">
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Department</label>
                <input id="ffDepartmentInput" type="text" class="req-modal-input" placeholder="e.g. College of Computer Studies">
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="ffCloseFormModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="ffSaveRecord()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="ffDeleteModal" style="display:none;" onclick="if(event.target===this) ffCloseDeleteModal()">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE FACULTY RECORD</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this record?</p>
        <input type="hidden" id="ffDeleteId" value="">
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="ffCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="ffConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@else
<div class="req-modal-overlay" id="ffcItemModal" style="display:none;" onclick="if(event.target===this) ffcCloseItemModal()">
    <div class="req-modal-box" style="max-width:700px;">
        <h3 class="req-modal-title" id="ffcItemModalTitle">ADD ITEM</h3>
        <input type="hidden" id="ffcItemSection" value="">
        <input type="hidden" id="ffcItemEditIndex" value="">

        <div id="ffcItemFields" class="sc-modal-grid" style="margin-top:10px;"></div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="ffcCloseItemModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="ffcSaveItem()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="ffcDeleteModal" style="display:none;" onclick="if(event.target===this) ffcCloseDeleteModal()">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE DETAIL ITEM</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this item?</p>
        <input type="hidden" id="ffcDeleteSection" value="">
        <input type="hidden" id="ffcDeleteIndex" value="">
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="ffcCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="ffcConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if(!$isConfigMode)
<script>
    var ffRows = [
        { id: 'ff-1', code: '01A', name: 'Dela Cruz, Juan', department: 'Computer Studies', status: 'Active' },
        { id: 'ff-2', code: '02A', name: 'Benedict, John', department: 'Computer Studies', status: 'Inactive' },
        { id: 'ff-3', code: '03A', name: 'Rivera, Angelo', department: 'Engineering', status: 'Active' },
        { id: 'ff-4', code: '04A', name: 'Austero, Andrea Jane', department: 'Engineering', status: 'Active' },
        { id: 'ff-5', code: '05A', name: 'Santos, Maria', department: 'Information Systems', status: 'Inactive' },
        { id: 'ff-6', code: '06A', name: 'Bares, Mark Jay', department: 'Computer Studies', status: 'Active' }
    ];

    function ffEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function ffGetFilteredRows() {
        var query = (document.getElementById('ffSearch').value || '').toLowerCase();
        return ffRows.filter(function(row) {
            if (!query) return true;
            return row.code.toLowerCase().indexOf(query) !== -1 ||
                row.name.toLowerCase().indexOf(query) !== -1 ||
                (row.department || '').toLowerCase().indexOf(query) !== -1 ||
                row.status.toLowerCase().indexOf(query) !== -1;
        });
    }

    function ffBuildMenu(menuId, id) {
        return '' +
            '<div class="apst-action-btn" data-ff-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="ffOpenEditModal(\'' + ffEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" onclick="ffOpenDeleteModal(\'' + ffEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>Delete</button>' +
            '</div>';
    }

    function ffCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function ffToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        ffCloseActionMenus();
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

    function ffRenderTable() {
        var tbody = document.getElementById('ffTableBody');
        if (!tbody) return;

        ffCloseActionMenus();

        var rows = ffGetFilteredRows();
        var bodyRows = rows.map(function(row, idx) {
            var statusClass = row.status === 'Active' ? 'ff-status-active' : 'ff-status-inactive';
            var menuId = 'ffMenu' + idx;
            return '' +
                '<tr data-ff-id="' + ffEscapeHtml(row.id) + '">' +
                    '<td>' + (idx + 1) + '</td>' +
                    '<td>' + ffEscapeHtml(row.code) + '</td>' +
                    '<td>' + ffEscapeHtml(row.name).toUpperCase() + '</td>' +
                    '<td>' + ffEscapeHtml(row.department) + '</td>' +
                    '<td><span class="ff-status ' + statusClass + '">' + ffEscapeHtml(row.status) + '</span></td>' +
                    '<td style="text-align:center;">' + ffBuildMenu(menuId, row.id) + '</td>' +
                '</tr>';
        }).join('');

        if (!bodyRows) {
            bodyRows = '<tr><td colspan="6" class="sc-empty-row">No faculty records found.</td></tr>';
        }

        tbody.innerHTML = bodyRows +
            '<tr class="ff-total-row"><td colspan="6">Total Records: <strong>' + rows.length + '</strong></td></tr>';
    }

    function ffOpenAddModal() {
        document.getElementById('ffFormTitle').textContent = 'ADD FACULTY RECORD';
        document.getElementById('ffEditingId').value = '';
        document.getElementById('ffCodeInput').value = '';
        document.getElementById('ffNameInput').value = '';
        document.getElementById('ffDepartmentInput').value = '';
        document.getElementById('ffStatusInput').value = 'Active';
        ffCloseActionMenus();
        document.getElementById('ffFormModal').style.display = 'flex';
    }

    function ffOpenEditModal(id) {
        var row = ffRows.find(function(item) { return item.id === id; });
        if (!row) return;

        document.getElementById('ffFormTitle').textContent = 'EDIT FACULTY RECORD';
        document.getElementById('ffEditingId').value = row.id;
        document.getElementById('ffCodeInput').value = row.code;
        document.getElementById('ffNameInput').value = row.name;
        document.getElementById('ffDepartmentInput').value = row.department || '';
        document.getElementById('ffStatusInput').value = row.status;
        ffCloseActionMenus();
        document.getElementById('ffFormModal').style.display = 'flex';
    }

    function ffCloseFormModal() {
        document.getElementById('ffFormModal').style.display = 'none';
    }

    function ffSaveRecord() {
        var editingId = document.getElementById('ffEditingId').value;
        var code = document.getElementById('ffCodeInput').value.trim();
        var name = document.getElementById('ffNameInput').value.trim();
        var department = document.getElementById('ffDepartmentInput').value.trim();
        var status = document.getElementById('ffStatusInput').value;

        if (!code || !name || !department) {
            alert('Please fill in Faculty Code, Faculty Name, and Department.');
            return;
        }

        if (!editingId) {
            ffRows.unshift({
                id: 'ff-' + Date.now(),
                code: code,
                name: name,
                department: department,
                status: status
            });
        } else {
            ffRows = ffRows.map(function(item) {
                if (item.id !== editingId) return item;
                return { id: item.id, code: code, name: name, department: department, status: status };
            });
        }

        ffCloseFormModal();
        ffRenderTable();
    }

    function ffOpenDeleteModal(id) {
        ffCloseActionMenus();
        document.getElementById('ffDeleteId').value = id;
        document.getElementById('ffDeleteModal').style.display = 'flex';
    }

    function ffCloseDeleteModal() {
        document.getElementById('ffDeleteModal').style.display = 'none';
    }

    function ffConfirmDelete() {
        var id = document.getElementById('ffDeleteId').value;
        ffRows = ffRows.filter(function(item) { return item.id !== id; });
        ffCloseDeleteModal();
        ffRenderTable();
    }

    document.getElementById('ffSearchBtn').addEventListener('click', ffRenderTable);
    document.getElementById('ffSearch').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            ffRenderTable();
        }
    });
    document.getElementById('ffNewRecordBtn').addEventListener('click', ffOpenAddModal);

    document.addEventListener('click', function(event) {
        var menuToggle = event.target.closest('[data-ff-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            ffToggleActionMenu(menuToggle.getAttribute('data-ff-menu-toggle'), menuToggle);
            return;
        }

        var row = event.target.closest('#ffTableBody tr[data-ff-id]');
        if (row && !event.target.closest('.apst-dropdown') && !event.target.closest('.apst-action-btn')) {
            var rowId = row.getAttribute('data-ff-id');
            var selected = ffRows.find(function(item) { return item.id === rowId; });
            if (selected) {
                var url = '{{ route('registrar.admin-tools.master-files.faculty-file') }}' +
                    '?view=config' +
                    '&code=' + encodeURIComponent(selected.code || '') +
                    '&name=' + encodeURIComponent(selected.name || '') +
                    '&department=' + encodeURIComponent(selected.department || '') +
                    '&status=' + encodeURIComponent(selected.status || '');
                window.location.href = url;
            }
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            ffCloseActionMenus();
        }
    });

    window.addEventListener('scroll', ffCloseActionMenus, true);

    ffRenderTable();
</script>
@else
<script>
    var ffcRows = {
        education: [
            {
                level: 'College',
                schoolName: 'Pamantasan ng Lungsod ng Pasig',
                courseDegree: 'BS Computer Science',
                dateGraduated: '04/15/2022'
            },
            {
                level: 'Graduate Studies',
                schoolName: 'University of Makati',
                courseDegree: 'MIT',
                dateGraduated: '06/20/2025'
            }
        ],
        registration: [
            {
                name: 'LET Professional Teacher',
                rating: '84.60',
                date: '09/24/2023'
            }
        ],
        organization: [
            {
                position: 'Member',
                name: 'Philippine Society of IT Educators',
                date: '01/10/2024'
            }
        ],
        work: [
            {
                position: 'IT Instructor',
                company: 'PLP Senior High Department',
                date: '08/01/2024'
            }
        ],
        training: [
            {
                title: 'Outcomes-Based Education Seminar',
                place: 'Pasig City',
                date: '11/12/2024'
            }
        ]
    };

    var ffcSections = {
        education: {
            label: 'Educational Background',
            bodyId: 'ffcEducationBody',
            columns: ['School Level', 'School Name', 'Course/Degree', 'Date Graduated'],
            fields: [
                { key: 'level', label: 'School Level', type: 'select', options: ['Elementary', 'High School', 'College', 'Graduate Studies'] },
                { key: 'schoolName', label: 'Name of School', type: 'text', placeholder: 'School Name' },
                { key: 'courseDegree', label: 'Course/Degree', type: 'text', placeholder: 'Course/Degree' },
                { key: 'dateGraduated', label: 'Date Graduated', type: 'text', placeholder: 'mm/dd/yy' }
            ]
        },
        registration: {
            label: 'Professional Registration',
            bodyId: 'ffcRegistrationBody',
            columns: ['Name of Professional Registration', 'Rating', 'Registration Date'],
            fields: [
                { key: 'name', label: 'Professional Registration', type: 'text', placeholder: 'Name of Professional Registration' },
                { key: 'rating', label: 'Rating', type: 'text', placeholder: '-select-' },
                { key: 'date', label: 'Registration Date', type: 'text', placeholder: 'mm/dd/yy' }
            ]
        },
        organization: {
            label: 'Professional Organization',
            bodyId: 'ffcOrganizationBody',
            columns: ['Name of Professional Org.', 'Name of Org.', 'Date'],
            fields: [
                { key: 'position', label: 'Professional Organization Position', type: 'text', placeholder: 'Position' },
                { key: 'name', label: 'Name of Organization', type: 'text', placeholder: 'Name of Org.' },
                { key: 'date', label: 'Date', type: 'text', placeholder: 'mm/dd/yy' }
            ]
        },
        work: {
            label: 'Work Experience',
            bodyId: 'ffcWorkBody',
            columns: ['Work Experience (Position)', 'Company Name', 'Date'],
            fields: [
                { key: 'position', label: 'Work Experience', type: 'text', placeholder: 'Position' },
                { key: 'company', label: 'Company Name', type: 'text', placeholder: 'Name of Org.' },
                { key: 'date', label: 'Date', type: 'text', placeholder: 'mm/dd/yy' }
            ]
        },
        training: {
            label: 'Trainings/Seminar Attended',
            bodyId: 'ffcTrainingBody',
            columns: ['Training(s)/Seminar(s)', 'Place', 'Date'],
            fields: [
                { key: 'title', label: 'Trainings/Seminars', type: 'text', placeholder: 'Trainings/Seminars' },
                { key: 'place', label: 'Place', type: 'text', placeholder: 'Place taken' },
                { key: 'date', label: 'Date', type: 'text', placeholder: 'mm/dd/yy' }
            ]
        }
    };

    function ffcEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function ffcBuildActionMenu(section, index) {
        var menuId = 'ffcMenu_' + section + '_' + index;
        return '' +
            '<div class="apst-action-btn" data-ffc-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" data-ffc-edit="' + section + '" data-ffc-index="' + index + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" data-ffc-delete="' + section + '" data-ffc-index="' + index + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>Delete</button>' +
            '</div>';
    }

    function ffcRenderSection(section) {
        var config = ffcSections[section];
        var tbody = document.getElementById(config.bodyId);
        if (!tbody) return;

        var rows = ffcRows[section] || [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="' + (config.columns.length + 1) + '" class="sc-empty-row">No List Found.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(function(row, index) {
            var dataColumns = config.fields.map(function(field) {
                return '<td>' + ffcEscapeHtml(row[field.key]) + '</td>';
            }).join('');

            return '<tr>' +
                dataColumns +
                '<td style="text-align:center;">' + ffcBuildActionMenu(section, index) + '</td>' +
            '</tr>';
        }).join('');
    }

    function ffcRenderAllSections() {
        Object.keys(ffcSections).forEach(function(section) {
            ffcRenderSection(section);
        });
        ffcCloseActionMenus();
    }

    function ffcCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function ffcToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        ffcCloseActionMenus();
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

    function ffcBuildFields(section, rowData) {
        var config = ffcSections[section];
        var fieldsWrap = document.getElementById('ffcItemFields');
        if (!config || !fieldsWrap) return;

        fieldsWrap.innerHTML = config.fields.map(function(field) {
            var value = rowData && rowData[field.key] ? rowData[field.key] : '';
            if (field.type === 'select') {
                var options = ['<option value="">-select-</option>'].concat((field.options || []).map(function(option) {
                    var selected = value === option ? ' selected' : '';
                    return '<option value="' + ffcEscapeHtml(option) + '"' + selected + '>' + ffcEscapeHtml(option) + '</option>';
                })).join('');
                return '' +
                    '<div class="req-modal-field-group">' +
                        '<label class="req-modal-label">' + ffcEscapeHtml(field.label) + '</label>' +
                        '<select class="req-modal-input" data-ffc-field="' + ffcEscapeHtml(field.key) + '">' + options + '</select>' +
                    '</div>';
            }

            return '' +
                '<div class="req-modal-field-group">' +
                    '<label class="req-modal-label">' + ffcEscapeHtml(field.label) + '</label>' +
                    '<input type="text" class="req-modal-input" data-ffc-field="' + ffcEscapeHtml(field.key) + '" placeholder="' + ffcEscapeHtml(field.placeholder || '') + '" value="' + ffcEscapeHtml(value) + '">' +
                '</div>';
        }).join('');
    }

    function ffcOpenItemModal(section, editIndex) {
        var config = ffcSections[section];
        if (!config) return;

        var numericIndex = typeof editIndex === 'number' ? editIndex : -1;
        var rowData = numericIndex >= 0 ? (ffcRows[section][numericIndex] || null) : null;
        var isEdit = numericIndex >= 0;

        document.getElementById('ffcItemModalTitle').textContent = (isEdit ? 'EDIT ' : 'ADD ') + config.label.toUpperCase();
        document.getElementById('ffcItemSection').value = section;
        document.getElementById('ffcItemEditIndex').value = isEdit ? String(numericIndex) : '';

        ffcBuildFields(section, rowData);
        ffcCloseActionMenus();
        document.getElementById('ffcItemModal').style.display = 'flex';
    }

    function ffcCloseItemModal() {
        document.getElementById('ffcItemModal').style.display = 'none';
    }

    function ffcSaveItem() {
        var section = document.getElementById('ffcItemSection').value;
        var editIndexValue = document.getElementById('ffcItemEditIndex').value;
        var config = ffcSections[section];
        if (!config) return;

        var row = {};
        var hasBlank = false;

        config.fields.forEach(function(field) {
            var input = document.querySelector('[data-ffc-field="' + field.key + '"]');
            var value = (input ? input.value : '').trim();
            row[field.key] = value;
            if (!value) hasBlank = true;
        });

        if (hasBlank) {
            alert('Please complete all fields before saving.');
            return;
        }

        if (editIndexValue === '') {
            ffcRows[section].push(row);
        } else {
            ffcRows[section][Number(editIndexValue)] = row;
        }

        ffcCloseItemModal();
        ffcRenderSection(section);
    }

    function ffcOpenDeleteModal(section, index) {
        document.getElementById('ffcDeleteSection').value = section;
        document.getElementById('ffcDeleteIndex').value = String(index);
        ffcCloseActionMenus();
        document.getElementById('ffcDeleteModal').style.display = 'flex';
    }

    function ffcCloseDeleteModal() {
        document.getElementById('ffcDeleteModal').style.display = 'none';
    }

    function ffcConfirmDelete() {
        var section = document.getElementById('ffcDeleteSection').value;
        var index = Number(document.getElementById('ffcDeleteIndex').value);
        if (!ffcRows[section]) return;

        ffcRows[section].splice(index, 1);
        ffcCloseDeleteModal();
        ffcRenderSection(section);
    }

    document.addEventListener('click', function(event) {
        var addBtn = event.target.closest('[data-ffc-add]');
        if (addBtn) {
            ffcOpenItemModal(addBtn.getAttribute('data-ffc-add'));
            return;
        }

        var menuToggle = event.target.closest('[data-ffc-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            ffcToggleActionMenu(menuToggle.getAttribute('data-ffc-menu-toggle'), menuToggle);
            return;
        }

        var editBtn = event.target.closest('[data-ffc-edit]');
        if (editBtn) {
            var editSection = editBtn.getAttribute('data-ffc-edit');
            var editIndex = Number(editBtn.getAttribute('data-ffc-index'));
            ffcOpenItemModal(editSection, editIndex);
            return;
        }

        var deleteBtn = event.target.closest('[data-ffc-delete]');
        if (deleteBtn) {
            var deleteSection = deleteBtn.getAttribute('data-ffc-delete');
            var deleteIndex = Number(deleteBtn.getAttribute('data-ffc-index'));
            ffcOpenDeleteModal(deleteSection, deleteIndex);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            ffcCloseActionMenus();
        }
    });

    window.addEventListener('scroll', ffcCloseActionMenus, true);

    ffcRenderAllSections();
</script>
@endif
@endpush




