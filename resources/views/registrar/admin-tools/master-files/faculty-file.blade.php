@extends('layouts.registrar')

@section('title', 'PLP - Faculty File')
@section('page-title', 'FACULTY FILE')
@section('body-class', request('view') === 'config' ? 'page-faculty-file page-faculty-file-config' : 'page-faculty-file')

@php
    $isConfigMode = request('view') === 'config';
    $cfgRow = $cfgFaculty ?? null;
    $cfgId = request('id', optional($cfgRow)->id);
    $cfgCode = request('code', optional($cfgRow)->code ?? '');
    $cfgName = request('name', optional($cfgRow)->name ?? 'Dela Cruz, Juan');
    $cfgDepartment = request('department', optional($cfgRow)->department ?? 'Computer Studies');
    $cfgStatus = request('status', optional($cfgRow)->status ?? 'Active');
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

            <div class="app-table-pager"></div>
        </section>
    </div>
</div>
@else
<style>
    .ffc-combo { position:relative; }
    .ffc-combo-menu { display:none; position:absolute; z-index:40; top:calc(100% + 4px); left:0; right:0; max-height:220px; overflow-y:auto; background:#fff; border:1px solid #cfd9d2; border-radius:7px; box-shadow:0 8px 20px rgba(0,0,0,.10); }
    .ffc-combo-menu.show { display:block; }
    .ffc-combo-option { padding:8px 12px; font-size:.86rem; cursor:pointer; color:#143521; }
    .ffc-combo-option:hover, .ffc-combo-option.is-active { background:#eef6f1; }
    .ffc-combo-option.is-others { border-top:1px solid #edf3ef; font-weight:700; color:#146c43; }
    .ffc-combo-empty { padding:8px 12px; font-size:.82rem; color:#66756b; }
</style>
<div class="pf-page">
    <div class="ffc-page">
        <div class="ffc-head-row">
            <a href="{{ route('registrar.admin-tools.master-files.faculty-file') }}" class="req-btn-cancel ffc-back-link">Back to Faculty List</a>
        </div>

        <section class="cfg-card ffc-card">
            <div class="ffc-section-title">Add Faculty</div>

            <div class="ffc-grid-top">
                <div class="ffc-field"><label class="app-filter-label" for="ffcFacultyName">Faculty Name</label><input id="ffcFacultyName" class="app-filter-input" type="text" value="{{ $cfgName }}"></div>
                <div class="ffc-field"><label class="app-filter-label" for="ffcFacultyCode">Faculty Code</label><input id="ffcFacultyCode" class="app-filter-input" type="text" value="{{ $cfgCode }}" placeholder="Auto-generated if blank"></div>
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
                <div class="ffc-field">
                    <label class="app-filter-label" for="ffcReligionInput">Religion</label>
                    <div class="ffc-combo" id="ffcReligionCombo">
                        <input type="text" class="app-filter-input" id="ffcReligionInput" placeholder="Search or select religion..." autocomplete="off" role="combobox" aria-expanded="false">
                        <div class="ffc-combo-menu" id="ffcReligionMenu"></div>
                    </div>
                    <input type="text" class="app-filter-input" id="ffcReligionOtherInput" placeholder="Enter new religion" style="display:none; margin-top:6px;">
                </div>
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
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="education" onclick="ffcOpenItemModal('education')">+ Add</button>
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
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="registration" onclick="ffcOpenItemModal('registration')">+ Add</button>
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
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="organization" onclick="ffcOpenItemModal('organization')">+ Add</button>
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
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="work" onclick="ffcOpenItemModal('work')">+ Add</button>
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
                <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="training" onclick="ffcOpenItemModal('training')">+ Add</button>
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
                <button type="button" class="req-btn-cancel" id="ffcCancelBtn">Cancel</button>
                <button type="button" class="pf-btn-new" id="ffcSaveBtn">Save</button>
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
                <input id="ffCodeInput" type="text" class="req-modal-input" placeholder="Auto-generated if blank">
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

@include('includes.registrar-delete-modal', [
    'id' => 'ffDeleteModal',
    'title' => 'DELETE RECORD',
    'message' => 'Are you sure you want to delete this faculty record?',
    'confirmBtnText' => 'Delete',
    'cancelAction' => 'ffCloseDeleteModal()',
    'confirmAction' => 'ffConfirmDelete()',
    'detailId' => 'ffDeleteDetail'
])
<input type="hidden" id="ffDeleteId" value="">
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

@include('includes.registrar-delete-modal', [
    'id' => 'ffcDeleteModal',
    'title' => 'DELETE DETAIL ITEM',
    'message' => 'Are you sure you want to delete this item?',
    'confirmBtnText' => 'Delete',
    'cancelAction' => 'ffcCloseDeleteModal()',
    'confirmAction' => 'ffcConfirmDelete()',
    'detailId' => 'ffcDeleteDetail'
])
<input type="hidden" id="ffcDeleteSection" value="">
<input type="hidden" id="ffcDeleteIndex" value="">
@endif
@endsection

@push('scripts')
@if(!$isConfigMode)
<script>
    var ffRows = @json($ffRows ?? []);
    var ffCsrf = '{{ csrf_token() }}';
    var ffApi = {
        store: '{{ route('registrar.admin-tools.master-files.faculty-file.store') }}',
        updateTemplate: '{{ route('registrar.admin-tools.master-files.faculty-file.update', ['masterFacultyFile' => '__ID__']) }}',
        destroyTemplate: '{{ route('registrar.admin-tools.master-files.faculty-file.destroy', ['masterFacultyFile' => '__ID__']) }}'
    };
    var ffCurrentPage = 1;
    var ffPageSize = 10;

    function ffBuildUrl(template, id) {
        return template.replace('__ID__', encodeURIComponent(String(id)));
    }

    function ffRequest(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': ffCsrf,
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

    function ffRenderPager(totalRows) {
        var mount = document.querySelector('.app-table-pager');
        if (!mount) return;

        var maxPage = Math.max(1, Math.ceil(totalRows / ffPageSize));
        if (ffCurrentPage > maxPage) {
            ffCurrentPage = maxPage;
        }

        if (totalRows <= ffPageSize) {
            mount.innerHTML = '';
            return;
        }

        var start = Math.max(1, ffCurrentPage - 2);
        var end = Math.min(maxPage, ffCurrentPage + 2);
        if (ffCurrentPage <= 3) {
            end = Math.min(maxPage, 5);
        } else if (ffCurrentPage >= maxPage - 2) {
            start = Math.max(1, maxPage - 4);
        }

        var pageNums = '';
        for (var p = start; p <= end; p += 1) {
            pageNums += '<button type="button" class="rtp-page-num ' + (p === ffCurrentPage ? 'active' : '') + '" data-ff-page="' + p + '">' + p + '</button>';
        }

        mount.innerHTML = '' +
            '<div class="rtp-pagination">' +
                '<nav class="rtp-nav" aria-label="Faculty file pagination">' +
                    '<div class="rtp-list" role="group" aria-label="Page controls">' +
                        '<button type="button" class="rtp-page-btn" data-ff-page-prev="1" ' + (ffCurrentPage <= 1 ? 'disabled' : '') + '>&lt;</button>' +
                        pageNums +
                        '<button type="button" class="rtp-page-btn" data-ff-page-next="1" ' + (ffCurrentPage >= maxPage ? 'disabled' : '') + '>&gt;</button>' +
                    '</div>' +
                '</nav>' +
            '</div>';
    }

    function ffRenderTable() {
        var tbody = document.getElementById('ffTableBody');
        if (!tbody) return;

        ffCloseActionMenus();

        var rows = ffGetFilteredRows();
        var maxPage = Math.max(1, Math.ceil(rows.length / ffPageSize));
        if (ffCurrentPage > maxPage) {
            ffCurrentPage = 1;
        }
        var startIndex = (ffCurrentPage - 1) * ffPageSize;
        var pageItems = rows.slice(startIndex, startIndex + ffPageSize);

        var bodyRows = pageItems.map(function(row, idx) {
            var statusClass = row.status === 'Active' ? 'ff-status-active' : 'ff-status-inactive';
            var menuId = 'ffMenu' + idx;
            return '' +
                '<tr data-ff-id="' + ffEscapeHtml(row.id) + '">' +
                    '<td>' + (startIndex + idx + 1) + '</td>' +
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
        ffRenderPager(rows.length);
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
        var row = ffRows.find(function(item) { return String(item.id) === String(id); });
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

        if (!name || !department) {
            alert('Please fill in Faculty Name and Department. Faculty Code is generated automatically when blank.');
            return;
        }

        var payload = {
            code: code,
            name: name,
            department: department,
            status: status
        };

        var request = !editingId
            ? ffRequest(ffApi.store, 'POST', payload)
            : ffRequest(ffBuildUrl(ffApi.updateTemplate, editingId), 'PUT', payload);

        request.then(function(data) {
            if (!editingId) {
                ffRows.unshift(data.row);
            } else {
                ffRows = ffRows.map(function(item) {
                    return String(item.id) === String(editingId) ? data.row : item;
                });
            }

            ffCloseFormModal();
            ffRenderTable();
        }).catch(function(error) {
            alert(error.message || 'Unable to save faculty record.');
        });
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
        ffRequest(ffBuildUrl(ffApi.destroyTemplate, id), 'DELETE', null).then(function() {
            ffRows = ffRows.filter(function(item) {
                return String(item.id) !== String(id);
            });
            ffCloseDeleteModal();
            ffRenderTable();
        }).catch(function(error) {
            alert(error.message || 'Unable to delete faculty record.');
        });
    }

    document.getElementById('ffSearchBtn').addEventListener('click', function() {
        ffCurrentPage = 1;
        ffRenderTable();
    });
    document.getElementById('ffSearch').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            ffCurrentPage = 1;
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
            var selected = ffRows.find(function(item) { return String(item.id) === String(rowId); });
            if (selected) {
                var url = '{{ route('registrar.admin-tools.master-files.faculty-file') }}' +
                    '?view=config' +
                    '&id=' + encodeURIComponent(selected.id || '') +
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

    document.querySelector('.app-table-pager').addEventListener('click', function(event) {
        var prev = event.target.closest('[data-ff-page-prev]');
        if (prev && ffCurrentPage > 1) {
            ffCurrentPage -= 1;
            ffRenderTable();
            return;
        }

        var next = event.target.closest('[data-ff-page-next]');
        if (next) {
            var total = ffGetFilteredRows().length;
            var maxPage = Math.max(1, Math.ceil(total / ffPageSize));
            if (ffCurrentPage < maxPage) {
                ffCurrentPage += 1;
                ffRenderTable();
            }
            return;
        }

        var pageBtn = event.target.closest('[data-ff-page]');
        if (pageBtn) {
            ffCurrentPage = parseInt(pageBtn.getAttribute('data-ff-page'), 10) || 1;
            ffRenderTable();
        }
    });

    ffRenderTable();
</script>
@else
<script>
    var ffcFacultyId = @json($cfgId ?? null);
    var ffcCsrf = '{{ csrf_token() }}';
    var ffcUpdateTemplate = '{{ route('registrar.admin-tools.master-files.faculty-file.update', ['masterFacultyFile' => '__ID__']) }}';
    var ffcReligionStoreUrl = '{{ route('registrar.admin-tools.master-files.faculty-file.religion.store') }}';
    var ffcFormState = @json($cfgFormState ?? []);
    var ffcRows = @json($cfgDetailRows ?? []);
    var ffcReligionOptions = @json($religions ?? []);

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

    function ffcBuildUrl(template, id) {
        return template.replace('__ID__', encodeURIComponent(String(id)));
    }

    function ffcRequest(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': ffcCsrf,
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

    function ffcGetPersistableElements() {
        return Array.prototype.slice.call(document.querySelectorAll('.ffc-card input, .ffc-card select, .ffc-card textarea'));
    }

    function ffcApplyFormState() {
        var state = ffcFormState || {};
        ffcGetPersistableElements().forEach(function(element, index) {
            var key = element.id ? ('id:' + element.id) : ('idx:' + index);
            if (!(key in state)) return;

            if (element.type === 'checkbox' || element.type === 'radio') {
                element.checked = !!state[key];
            } else {
                element.value = state[key] == null ? '' : String(state[key]);
            }
        });
    }

    function ffcCollectFormState() {
        var state = {};
        ffcGetPersistableElements().forEach(function(element, index) {
            var key = element.id ? ('id:' + element.id) : ('idx:' + index);
            if (element.type === 'checkbox' || element.type === 'radio') {
                state[key] = !!element.checked;
            } else {
                state[key] = element.value == null ? '' : String(element.value);
            }
        });

        return state;
    }

    function ffcBuildActionMenu(section, index) {
        var menuId = 'ffcMenu_' + section + '_' + index;
        return '' +
            '<div class="apst-action-btn" data-ffc-menu-toggle="' + menuId + '" onclick="ffcToggleActionMenu(\'' + menuId + '\', this); event.stopPropagation();" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" data-ffc-edit="' + section + '" data-ffc-index="' + index + '" onclick="ffcOpenItemModal(\'' + section + '\',' + index + ')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" data-ffc-delete="' + section + '" data-ffc-index="' + index + '" onclick="ffcOpenDeleteModal(\'' + section + '\',' + index + ')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>Delete</button>' +
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

    var ffcReligionOthersValue = '__others__';

    function ffcInitReligionCombo() {
        var input = document.getElementById('ffcReligionInput');
        var menu = document.getElementById('ffcReligionMenu');
        var otherInput = document.getElementById('ffcReligionOtherInput');
        if (!input || !menu || !otherInput) return;

        function renderMenu(filter) {
            var q = (filter || '').trim().toLowerCase();
            var matches = ffcReligionOptions.filter(function (name) {
                return name.toLowerCase().indexOf(q) !== -1;
            });

            var html = matches.length
                ? matches.map(function (name) {
                    return '<div class="ffc-combo-option" data-value="' + ffcEscapeHtml(name) + '">' + ffcEscapeHtml(name) + '</div>';
                }).join('')
                : '<div class="ffc-combo-empty">No matches</div>';

            html += '<div class="ffc-combo-option is-others" data-value="' + ffcReligionOthersValue + '">+ Others (enter new religion)</div>';
            menu.innerHTML = html;
            menu.classList.add('show');
            input.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            menu.classList.remove('show');
            input.setAttribute('aria-expanded', 'false');
        }

        function selectValue(value) {
            if (value === ffcReligionOthersValue) {
                input.value = '';
                otherInput.style.display = 'block';
                otherInput.focus();
            } else {
                input.value = value;
                otherInput.style.display = 'none';
                otherInput.value = '';
            }
            closeMenu();
        }

        input.addEventListener('focus', function () { renderMenu(input.value); });
        input.addEventListener('input', function () { renderMenu(input.value); });
        menu.addEventListener('mousedown', function (event) {
            var option = event.target.closest('.ffc-combo-option');
            if (!option || !option.dataset.value) return;
            event.preventDefault();
            selectValue(option.dataset.value);
        });
        document.addEventListener('click', function (event) {
            if (!event.target.closest('#ffcReligionCombo')) {
                closeMenu();
            }
        });

        // If a religion was restored that isn't in the known list, treat it as a
        // previously-entered "Others" value and keep the field visible.
        if (otherInput.value.trim() !== '') {
            otherInput.style.display = 'block';
        } else if (input.value.trim() !== '' && ffcReligionOptions.indexOf(input.value.trim()) === -1) {
            otherInput.value = input.value.trim();
            otherInput.style.display = 'block';
        }
    }

    function ffcResolveReligion() {
        var input = document.getElementById('ffcReligionInput');
        var otherInput = document.getElementById('ffcReligionOtherInput');
        var otherValue = otherInput && otherInput.style.display !== 'none' ? otherInput.value.trim() : '';

        if (!otherValue) {
            return Promise.resolve();
        }

        return ffcRequest(ffcReligionStoreUrl, 'POST', { name: otherValue }).then(function (data) {
            var savedName = (data && data.religion && data.religion.name) ? data.religion.name : otherValue;
            if (ffcReligionOptions.indexOf(savedName) === -1) {
                ffcReligionOptions.push(savedName);
            }
            input.value = savedName;
            otherInput.value = '';
            otherInput.style.display = 'none';
        });
    }

    function ffcSaveFacultyConfig() {
        if (!ffcFacultyId) {
            alert('Faculty record not found. Please return to list and open a record again.');
            return;
        }

        ffcResolveReligion().then(function () {
            var payload = {
                code: (document.getElementById('ffcFacultyCode').value || '').trim(),
                name: (document.getElementById('ffcFacultyName').value || '').trim(),
                department: (document.getElementById('ffcOffice').value || '').trim(),
                status: document.getElementById('ffcFacultyStatusTop').value || 'Active',
                config_payload: {
                    form_state: ffcCollectFormState(),
                    sections: ffcRows
                }
            };

            if (!payload.name || !payload.department) {
                alert('Faculty Name and Office Department are required. Faculty Code is generated automatically when blank.');
                return;
            }

            return ffcRequest(ffcBuildUrl(ffcUpdateTemplate, ffcFacultyId), 'PUT', payload).then(function(data) {
                ffcFacultyId = data.row.id;
                ffcFormState = payload.config_payload.form_state;
                alert('Faculty profile saved successfully.');
            });
        }).catch(function(error) {
            alert(error.message || 'Unable to save faculty profile.');
        });
    }

    document.addEventListener('click', function(event) {
        var target = event.target && event.target.nodeType === 3 ? event.target.parentElement : event.target;
        if (!target || !target.closest) return;

        var addBtn = target.closest('[data-ffc-add]');
        if (addBtn) {
            ffcOpenItemModal(addBtn.getAttribute('data-ffc-add'));
            return;
        }

        var menuToggle = target.closest('[data-ffc-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            ffcToggleActionMenu(menuToggle.getAttribute('data-ffc-menu-toggle'), menuToggle);
            return;
        }

        var editBtn = target.closest('[data-ffc-edit]');
        if (editBtn) {
            var editSection = editBtn.getAttribute('data-ffc-edit');
            var editIndex = Number(editBtn.getAttribute('data-ffc-index'));
            ffcOpenItemModal(editSection, editIndex);
            return;
        }

        var deleteBtn = target.closest('[data-ffc-delete]');
        if (deleteBtn) {
            var deleteSection = deleteBtn.getAttribute('data-ffc-delete');
            var deleteIndex = Number(deleteBtn.getAttribute('data-ffc-index'));
            ffcOpenDeleteModal(deleteSection, deleteIndex);
            return;
        }

        if (!target.closest('.apst-dropdown')) {
            ffcCloseActionMenus();
        }
    });

    var ffcSaveBtn = document.getElementById('ffcSaveBtn');
    if (ffcSaveBtn) {
        ffcSaveBtn.addEventListener('click', ffcSaveFacultyConfig);
    }

    var ffcCancelBtn = document.getElementById('ffcCancelBtn');
    if (ffcCancelBtn) {
        ffcCancelBtn.addEventListener('click', function() {
            window.location.href = '{{ route('registrar.admin-tools.master-files.faculty-file') }}';
        });
    }

    window.addEventListener('scroll', ffcCloseActionMenus, true);

    ffcRenderAllSections();
    ffcApplyFormState();
    ffcInitReligionCombo();
</script>
@endif
@endpush




