@extends('layouts.registrar')

@section('title', 'PLP - Deficiency')
@section('page-title', 'DEFICIENCY')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <div id="dfStudentsListView">
            <div class="ga-toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div class="pf-search-wrap ga-search-wrap ga-search-wrap-wide" style="width: 100%; max-width: 400px; margin: 0; background: #fff; border: 1px solid #ccc; border-radius: 6px; overflow: hidden;">
                    <span class="pf-search-icon" aria-hidden="true" style="padding-left: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <input type="text" class="pf-search-input" id="dfStudentSearchInput" placeholder="Search Name, Student ID, Course, Year Level..." style="border: none; background: transparent; padding-left: 42px;">
                </div>
                <button type="button" class="pf-btn-new ga-btn ga-btn-primary" onclick="document.getElementById('gaAddStudentModal').style.display='flex'">+ Add Row</button>
            </div>

            <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table ga-table-compact app-table">
                    <thead>
                        <tr>
                            <th style="padding: 12px 14px; text-align:center;">#</th>
                            <th style="padding: 12px 14px;">Student ID</th>
                            <th style="padding: 12px 14px;">Student Name</th>
                            <th style="padding: 12px 14px; text-align:center;">Course</th>
                            <th style="padding: 12px 14px; text-align:center;">Year Level</th>
                            <th style="padding: 12px 14px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <style>
                        .df-student-link { color: inherit; text-decoration: none; transition: color 0.1s; }
                        .df-student-link:hover { color: #006837; }
                    </style>
                    <tbody id="dfTbody">
                        @forelse($students as $index => $student)
                        <tr
                            data-student-pk="{{ $student->id }}"
                            data-student-id="{{ $student->student_no ?: 'N/A' }}"
                            data-student-name="{{ $student->name }}"
                            data-student-program="{{ $student->program ?: 'N/A' }}"
                            data-student-year="{{ $student->year_level ?: 'N/A' }}"
                        >
                            <td style="text-align:center;">{{ $index + 1 }}</td>
                            <td>{{ $student->student_no ?: 'N/A' }}</td>
                            <td><a href="#" class="df-student-link" data-df-open-detail>{{ $student->name }}</a></td>
                            <td style="text-align:center;">{{ $student->program ?: 'N/A' }}</td>
                            <td style="text-align:center;">{{ $student->year_level ?: 'N/A' }}</td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:6px; justify-content:center;">
                                    <button type="button" class="ga-btn" style="padding: 4px 10px; background: #d4eedb; border-radius: 4px; color: #006837; border:none; cursor:pointer;" title="View Deficiencies Summary" data-df-open-summary>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center; color:#666;">No students found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="app-table-pager"></div>
        </div>

        <div id="dfStudentDetailView" style="display:none;">
            <div class="ga-toolbar ga-toolbar-start" style="margin-bottom: 20px;">
                <button type="button" class="ga-btn" onclick="document.getElementById('dfStudentDetailView').style.display='none'; document.getElementById('dfStudentsListView').style.display='block';" style="display:flex; align-items:center; gap:6px; color:#444; background:transparent; border:1px solid #ccc; padding:8px 16px; border-radius:6px; cursor:pointer;" onmouseover="this.style.background='#f0f0f0';" onmouseout="this.style.background='transparent';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back
                </button>
            </div>

            <div class="ga-card ga-filter-card sched-filter-bar">
                <div class="ga-filter-grid ga-filter-grid-deficiency">
                    <div class="ga-filter-search ga-filter-search-deficiency">
                        <div class="pf-search-wrap ga-search-wrap ga-search-wrap-wide">
                            <span class="pf-search-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                            <input type="text" class="pf-search-input" placeholder="Search by student no, name, or department">
                        </div>
                    </div>
                <div>
                    <label class="ga-label">Department</label>
                    <select class="app-filter-select">
                        <option>All Departments</option>
                        <option>Library</option>
                        <option>Cashier</option>
                        <option>Registrar</option>
                    </select>
                </div>
                <div>
                    <label class="ga-label">Status</label>
                    <select class="app-filter-select">
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Completed</option>
                    </select>
                </div>
                <div>
                    <label class="ga-label">Submission Date</label>
                    <input class="ga-input" type="date" onclick="this.showPicker()">
                </div>
            </div>
        </div>

        <div class="ga-toolbar ga-toolbar-end">
            <button type="button" class="pf-btn-new ga-btn ga-btn-primary" data-ga-modal-open="gaDeficiencyNewModal">+ Add Deficiency</button>
        </div>

        <div class="svc-selected-info" style="margin-top: 10px; margin-bottom: 20px;">
            <div><strong>Student ID:</strong> <span id="dfInfoStudentId">{{ optional($selectedStudent)->student_no ?: '-' }}</span></div>
            <div><strong>Student Name:</strong> <span id="dfInfoStudentName">{{ optional($selectedStudent)->name ?: '-' }}</span></div>
            <div><strong>Program:</strong> <span id="dfInfoProgram">{{ optional($selectedStudent)->program ?: '-' }}</span></div>
            <div><strong>Year Level:</strong> <span id="dfInfoYearLevel">{{ optional($selectedStudent)->year_level ?: '-' }}</span></div>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table class="ga-table ga-table-compact app-table" id="dfTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Department</th>
                        <th>Remarks</th>
                        <th>Date Today</th>
                        <th>Submission Date</th>
                        <th style="text-align: center;">Completed</th>
                        <th>Compliance Date</th>
                        <th>Updated By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentDeficiencies as $index => $record)
                    <tr data-deficiency-id="{{ $record->id }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $record->department }}</td>
                        <td>{{ $record->remarks }}</td>
                        <td>{{ optional($record->date_today)->format('m/d/Y') ?: '-' }}</td>
                        <td>{{ optional($record->submission_date)->format('m/d/Y') ?: '-' }}</td>
                        <td style="text-align: center;"><label class="ga-check ga-check-tight" style="justify-content: center;"><input type="checkbox" data-df-toggle-complete {{ $record->is_completed ? 'checked' : '' }}> </label></td>
                        <td>{{ optional($record->compliance_date)->format('m/d/Y') ?: '-' }}</td>
                        <td>{{ $record->updated_by ?: '-' }}</td>
                        <td>
                            <div class="apst-action-btn" data-df-menu-toggle="dfMenu{{ $index }}" aria-label="Open row actions" title="Actions">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="apst-dropdown" id="dfMenu{{ $index }}">
                                <button type="button" data-ga-open-action="edit" data-ga-item="{{ $record->department }} deficiency">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="{{ $record->department }} deficiency">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align:center; color:#666;">No deficiency records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="app-table-pager"></div>

        </div> <!-- End dfStudentDetailView -->

        <div class="req-modal-overlay" id="gaAddStudentModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="max-width:500px; width: 100%; margin: 0; padding: 20px; text-align: left;">
                <h3 class="req-modal-title">ADD STUDENT</h3>
                <div class="req-modal-fields" style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
                    <div class="req-modal-field-group" style="width: 100%;">
                        <label class="req-modal-label">STUDENT ID / NUMBER</label>
                        <input class="req-modal-input" type="text" id="addStudentIdInput" placeholder="Enter Student ID">
                    </div>
                    <div class="req-modal-field-group" style="width: 100%;">
                        <label class="req-modal-label">STUDENT NAME</label>
                        <input class="req-modal-input" type="text" id="addStudentNameInput" placeholder="Enter Full Name">
                    </div>
                </div>
                <div class="req-modal-actions" style="margin-top: 25px;">
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaAddStudentModal').style.display='none'">Cancel</button>
                    <button type="button" class="req-btn-save" style="background:#006837;" onclick="addNewStudentRow()">Add</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyEmptyViewModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="max-width:500px; width: 100%; margin: 0; padding: 20px; text-align: center;">
                <h3 class="req-modal-title" style="margin-bottom: 5px; text-align: center;">ACTIVE DEFICIENCIES</h3>
                <p id="dfSummaryStudentName" style="color:#666; font-size: 0.95rem; margin-top: 0; margin-bottom: 20px;">-</p>
                <div id="dfSummaryBody" style="background: #fafafa; border: 1px solid #eee; border-radius: 8px; padding: 20px; margin-bottom: 20px; text-align:left;">
                    <p style="margin: 0; color: #888; font-style: italic;">No active deficiencies on record.</p>
                </div>
                <div class="req-modal-actions" style="justify-content: center;">
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaDeficiencyEmptyViewModal').style.display='none'">Close</button>
                    <button type="button" class="req-btn-save" style="background:#006837;" id="dfManageRecordsFromSummary">Manage Records</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyQuickViewModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="max-width:500px; width: 100%; margin: 0; padding: 20px; text-align: center;">
                <h3 class="req-modal-title" style="margin-bottom: 5px; text-align: center;">ACTIVE DEFICIENCIES</h3>
                <p style="color:#666; font-size: 0.95rem; margin-top: 0; margin-bottom: 20px;">Andrea Jane Austero</p>
                <div style="background: #fafafa; border: 1px solid #eee; border-radius: 8px; padding: 20px; margin-bottom: 20px; text-align: left;">
                    <ul style="margin: 0; padding: 0; list-style: none; font-size: 0.9rem; color: #444;">
                        <li style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #eaeaea; display: flex; justify-content: space-between; align-items: center;">
                            <div><strong style="color: #222;">Library:</strong> Damaged Item (Math book)</div>
                            <span style="color:#d93025; font-size:0.75rem; font-weight:600; background: #ffe5e5; padding: 3px 10px; border-radius: 12px;">Pending</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; align-items: center;">
                            <div><strong style="color: #222;">Cashier:</strong> Unsettled fee balance</div>
                            <span style="color:#d93025; font-size:0.75rem; font-weight:600; background: #ffe5e5; padding: 3px 10px; border-radius: 12px;">Pending</span>
                        </li>
                    </ul>
                </div>
                <div class="req-modal-actions" style="justify-content: center;">
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaDeficiencyQuickViewModal').style.display='none'">Close</button>
                    <button type="button" class="req-btn-save" style="background:#006837;" onclick="document.getElementById('gaDeficiencyQuickViewModal').style.display='none'; document.getElementById('dfStudentsListView').style.display='none'; document.getElementById('dfStudentDetailView').style.display='block';">Manage Records</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyNewModal" style="display:none;">
            <div class="req-modal-box" style="max-width:620px;">
                <h3 class="req-modal-title" id="gaDeficiencyNewTitle">ADD DEFICIENCY</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group"><label class="req-modal-label">DEPARTMENT</label><select class="req-modal-input" id="dfNewDepartment"><option>Library</option><option>Cashier</option><option>Registrar</option></select></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">SUBMISSION DATE</label><input class="req-modal-input" type="date" id="dfNewSubmissionDate" onclick="this.showPicker()"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">DATE TODAY</label><input class="req-modal-input" type="date" id="dfNewDateToday" onclick="this.showPicker()"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLIANCE DATE</label><input class="req-modal-input" type="date" id="dfNewComplianceDate" onclick="this.showPicker()"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">UPDATED BY</label><input class="req-modal-input" id="dfNewUpdatedBy" placeholder="Admin"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLETED</label><label class="ga-check"><input type="checkbox" id="dfNewCompleted" onchange="syncCompletedState(this, 'dfNewComplianceDate', 'dfNewUpdatedBy')"> </label></div>
                </div>
                <div class="req-modal-field-group" style="margin-top:10px;">
                    <label class="req-modal-label">REMARKS</label>
                    <input class="req-modal-input" id="dfNewRemarks" placeholder="Reason for deficiency">
                </div>
                <div class="req-modal-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-df-save-new>Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyActionModal" style="display:none;">
            <div class="req-modal-box" style="max-width:620px;">
                <h3 class="req-modal-title" id="gaDeficiencyActionTitle">EDIT DEFICIENCY</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group"><label class="req-modal-label">DEPARTMENT</label><select class="req-modal-input" id="dfEditDepartment"><option>Library</option><option>Cashier</option><option>Registrar</option></select></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">SUBMISSION DATE</label><input class="req-modal-input" type="date" id="dfEditSubmissionDate" onclick="this.showPicker()"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">DATE TODAY</label><input class="req-modal-input" type="date" id="dfEditDateToday" onclick="this.showPicker()"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLIANCE DATE</label><input class="req-modal-input" type="date" id="dfEditComplianceDate" onclick="this.showPicker()"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">UPDATED BY</label><input class="req-modal-input" id="dfEditUpdatedBy"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLETED</label><label class="ga-check"><input type="checkbox" id="dfEditCompleted" onchange="syncCompletedState(this, 'dfEditComplianceDate', 'dfEditUpdatedBy')"> </label></div>
                </div>
                <div class="req-modal-field-group" style="margin-top:10px;">
                    <label class="req-modal-label">REMARKS</label>
                    <input class="req-modal-input" id="dfEditRemarks">
                </div>
                <div class="req-modal-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-ga-confirm-action>Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyDeleteModal" style="display:none;">
            <div class="req-modal-box req-modal-success" style="min-width:300px;">
                <h3 class="req-modal-title" style="color:#c0392b;" id="gaDeficiencyDeleteTitle">DELETE DEFICIENCY</h3>
                <p id="gaDeficiencyActionText" style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;">Are you sure you want to delete this deficiency?</p>
                <div class="req-modal-actions" style="justify-content:center;">
                    <button class="req-btn-cancel" type="button" data-ga-close-delete>Cancel</button>
                    <button class="req-btn-save" type="button" style="background:#c0392b;" data-ga-confirm-delete>Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addNewStudentRow() {
    document.dispatchEvent(new CustomEvent('df-add-student'));
}

document.addEventListener('DOMContentLoaded', function () {
    var page = document.querySelector('.ga-page');
    if (!page) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    var studentStoreUrl = @json(route('registrar.services.grading-academic.deficiency.students.store'));
    var recordsUrlTemplate = @json(route('registrar.services.grading-academic.deficiency.records', ['student' => '__STUDENT__']));
    var storeUrlTemplate = @json(route('registrar.services.grading-academic.deficiency.store', ['student' => '__STUDENT__']));
    var updateUrlTemplate = @json(route('registrar.services.grading-academic.deficiency.update', ['studentDeficiency' => '__DEF__']));
    var destroyUrlTemplate = @json(route('registrar.services.grading-academic.deficiency.destroy', ['studentDeficiency' => '__DEF__']));

    var table = page.querySelector('#dfTable');
    var tableBody = table ? table.querySelector('tbody') : null;
    var actionModal = document.getElementById('gaDeficiencyActionModal');
    var deleteModal = document.getElementById('gaDeficiencyDeleteModal');
    var actionTitle = document.getElementById('gaDeficiencyActionTitle');
    var actionText = document.getElementById('gaDeficiencyActionText');
    var dfEditDepartment = document.getElementById('dfEditDepartment');
    var dfEditRemarks = document.getElementById('dfEditRemarks');
    var dfEditDateToday = document.getElementById('dfEditDateToday');
    var dfEditSubmissionDate = document.getElementById('dfEditSubmissionDate');
    var dfEditCompleted = document.getElementById('dfEditCompleted');
    var dfEditComplianceDate = document.getElementById('dfEditComplianceDate');
    var dfEditUpdatedBy = document.getElementById('dfEditUpdatedBy');
    var studentsListView = document.getElementById('dfStudentsListView');
    var studentListBody = document.getElementById('dfTbody');
    var studentDetailView = document.getElementById('dfStudentDetailView');
    var infoStudentId = document.getElementById('dfInfoStudentId');
    var infoStudentName = document.getElementById('dfInfoStudentName');
    var infoProgram = document.getElementById('dfInfoProgram');
    var infoYearLevel = document.getElementById('dfInfoYearLevel');
    var summaryStudentName = document.getElementById('dfSummaryStudentName');
    var summaryBody = document.getElementById('dfSummaryBody');
    var nextMenuIndex = page.querySelectorAll('[data-df-menu-toggle]').length;
    var activeRow = null;
    var activeAction = 'edit';
    var selectedStudentRow = null;
    var currentStudentPk = @json(optional($selectedStudent)->id);

    function toInputDate(value) {
        if (!value || value === '-') return '';
        if (value.indexOf('T') !== -1) {
            return value.substring(0, 10);
        }
        var parts = value.split('/');
        if (parts.length !== 3) return '';
        return parts[2] + '-' + parts[0].padStart(2, '0') + '-' + parts[1].padStart(2, '0');
    }

    function toDisplayDate(value) {
        if (!value) return '-';
        if (value.indexOf('T') !== -1) {
            value = value.substring(0, 10);
        }
        var parts = value.split('-');
        if (parts.length !== 3) return value;
        return parts[1] + '/' + parts[2] + '/' + parts[0];
    }

    function getTodayInputDate() {
        var now = new Date();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        return now.getFullYear() + '-' + month + '-' + day;
    }

    function getUpdatedByName() {
        var possibleNameEl = document.querySelector('[data-registrar-user-name]') ||
            document.querySelector('meta[name="user-name"]') ||
            document.querySelector('[name="updated_by"]');
        if (!possibleNameEl) return 'Admin';
        var value = (possibleNameEl.getAttribute('content') || possibleNameEl.value || possibleNameEl.textContent || '').trim();
        return value || 'Admin';
    }

    function resolveElementRef(ref) {
        if (!ref) return null;
        if (typeof ref === 'string') {
            return document.getElementById(ref) || null;
        }
        return ref;
    }

    function buildUrl(template, token, value) {
        return String(template).replace(token, String(value));
    }

    function requestJson(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: payload ? JSON.stringify(payload) : null
        }).then(function (response) {
            if (!response.ok) {
                return response.json().catch(function () { return {}; }).then(function (data) {
                    var message = 'Request failed.';
                    if (data && data.errors) {
                        var keys = Object.keys(data.errors);
                        if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                            message = data.errors[keys[0]][0];
                        }
                    }
                    throw new Error(message);
                });
            }

            return response.json().catch(function () { return { ok: true }; });
        });
    }

    function syncCompletedState(checkbox, dateInput, updatedByInput) {
        if (!checkbox) return;
        var dateField = resolveElementRef(dateInput);
        var updatedByField = resolveElementRef(updatedByInput);

        if (checkbox.checked) {
            if (dateField) dateField.value = getTodayInputDate();
            if (updatedByField && !updatedByField.value.trim()) {
                updatedByField.value = getUpdatedByName();
            }
            return;
        }
        if (dateField) dateField.value = '';
    }

    window.syncCompletedState = syncCompletedState;

    function showStudentList() {
        if (studentDetailView) studentDetailView.style.display = 'none';
        if (studentsListView) studentsListView.style.display = 'block';
    }

    function showStudentDetail() {
        if (studentsListView) studentsListView.style.display = 'none';
        if (studentDetailView) studentDetailView.style.display = 'block';
    }

    function populateSelectedStudent(row) {
        if (!row) return;
        selectedStudentRow = row;
        currentStudentPk = row.getAttribute('data-student-pk') || null;
        if (infoStudentId) infoStudentId.textContent = row.getAttribute('data-student-id') || 'N/A';
        if (infoStudentName) infoStudentName.textContent = row.getAttribute('data-student-name') || 'N/A';
        if (infoProgram) infoProgram.textContent = row.getAttribute('data-student-program') || 'N/A';
        if (infoYearLevel) infoYearLevel.textContent = row.getAttribute('data-student-year') || 'N/A';
        if (summaryStudentName) summaryStudentName.textContent = row.getAttribute('data-student-name') || 'N/A';
    }

    function buildActionCell(item, menuId) {
        return '' +
            '<td>' +
                '<div class="apst-action-btn" data-df-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
                '<div class="apst-dropdown" id="' + menuId + '">' +
                    '<button type="button" data-ga-open-action="edit" data-ga-item="' + item + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>' +
                    '<button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="' + item + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>Delete</button>' +
                '</div>' +
            '</td>';
    }

    function renderSummary(records) {
        if (!summaryBody) return;
        if (!records || !records.length) {
            summaryBody.innerHTML = '<p style="margin: 0; color: #888; font-style: italic;">No active deficiencies on record.</p>';
            return;
        }

        var html = '<ul style="margin: 0; padding: 0; list-style: none; font-size: 0.9rem; color: #444;">';
        records.forEach(function (record, index) {
            var status = record.is_completed ? 'Completed' : 'Pending';
            var statusColor = record.is_completed ? '#0b7a39' : '#d93025';
            var statusBg = record.is_completed ? '#ddf8e8' : '#ffe5e5';
            html += '<li style="' + (index ? 'margin-top: 12px; padding-top: 12px; border-top: 1px solid #eaeaea;' : '') + ' display: flex; justify-content: space-between; align-items: center; gap: 12px;">' +
                '<div><strong style="color: #222;">' + record.department + ':</strong> ' + record.remarks + '</div>' +
                '<span style="color:' + statusColor + '; font-size:0.75rem; font-weight:600; background:' + statusBg + '; padding: 3px 10px; border-radius: 12px;">' + status + '</span>' +
                '</li>';
        });
        html += '</ul>';
        summaryBody.innerHTML = html;
    }

    function reindexStudentRows() {
        if (!studentListBody) return;
        studentListBody.querySelectorAll('tr').forEach(function (row, index) {
            if (row.cells && row.cells.length) {
                row.cells[0].textContent = index + 1;
            }
        });
    }

    function createStudentRow(student) {
        if (!studentListBody || !student) return null;

        var tr = document.createElement('tr');
        tr.setAttribute('data-student-pk', student.id);
        tr.setAttribute('data-student-id', student.student_no || 'N/A');
        tr.setAttribute('data-student-name', student.name || 'N/A');
        tr.setAttribute('data-student-program', student.program || 'N/A');
        tr.setAttribute('data-student-year', student.year_level || 'N/A');
        tr.innerHTML = '' +
            '<td style="text-align:center;">0</td>' +
            '<td>' + (student.student_no || 'N/A') + '</td>' +
            '<td><a href="#" class="df-student-link" data-df-open-detail>' + (student.name || 'N/A') + '</a></td>' +
            '<td style="text-align:center;">' + (student.program || 'N/A') + '</td>' +
            '<td style="text-align:center;">' + (student.year_level || 'N/A') + '</td>' +
            '<td style="text-align:center;">' +
                '<div style="display:flex; gap:6px; justify-content:center;">' +
                    '<button type="button" class="ga-btn" style="padding: 4px 10px; background: #d4eedb; border-radius: 4px; color: #006837; border:none; cursor:pointer;" title="View Deficiencies Summary" data-df-open-summary>' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>' +
                    '</button>' +
                '</div>' +
            '</td>';

        studentListBody.appendChild(tr);
        reindexStudentRows();
        return tr;
    }

    function renderDetailRows(records) {
        if (!tableBody) return;

        if (!records || !records.length) {
            tableBody.innerHTML = '<tr><td colspan="9" style="text-align:center; color:#666;">No deficiency records found.</td></tr>';
            return;
        }

        nextMenuIndex = 0;
        var rowsHtml = '';
        records.forEach(function (record, index) {
            var menuId = 'dfMenu' + nextMenuIndex;
            nextMenuIndex += 1;
            rowsHtml += '<tr data-deficiency-id="' + record.id + '">' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + (record.department || '-') + '</td>' +
                '<td>' + (record.remarks || '-') + '</td>' +
                '<td>' + toDisplayDate(record.date_today) + '</td>' +
                '<td>' + toDisplayDate(record.submission_date) + '</td>' +
                '<td style="text-align: center;"><label class="ga-check ga-check-tight" style="justify-content: center;"><input type="checkbox" data-df-toggle-complete ' + (record.is_completed ? 'checked' : '') + '> </label></td>' +
                '<td>' + toDisplayDate(record.compliance_date) + '</td>' +
                '<td>' + (record.updated_by || '-') + '</td>' +
                buildActionCell((record.department || 'Department') + ' deficiency', menuId) +
                '</tr>';
        });

        tableBody.innerHTML = rowsHtml;
    }

    function loadDeficiencyRecords(studentPk, openDetailAfterLoad, openSummaryAfterLoad) {
        if (!studentPk) {
            renderDetailRows([]);
            renderSummary([]);
            if (openSummaryAfterLoad) {
                openModal('gaDeficiencyEmptyViewModal');
            }
            if (openDetailAfterLoad) {
                showStudentDetail();
            }
            return;
        }

        requestJson(buildUrl(recordsUrlTemplate, '__STUDENT__', studentPk), 'GET').then(function (data) {
            var records = (data && data.data) ? data.data : [];
            renderDetailRows(records);
            renderSummary(records);
            if (openSummaryAfterLoad) {
                openModal('gaDeficiencyEmptyViewModal');
            }
            if (openDetailAfterLoad) {
                showStudentDetail();
            }
        }).catch(function (error) {
            alert(error.message || 'Unable to load deficiency records.');
        });
    }

    function closeActionMenus() {
        page.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
            menu.classList.remove('open');
            menu.classList.remove('drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.bottom = '';
        });
    }

    function toggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;
        var isOpen = menu.classList.contains('open');
        closeActionMenus();
        if (isOpen) return;

        var rect = trigger.getBoundingClientRect();
        var menuWidth = menu.offsetWidth || 120;
        var spacing = 6;
        var spaceBelow = window.innerHeight - rect.bottom;
        var left = rect.right + spacing;
        if (left + menuWidth > window.innerWidth - spacing) {
            left = rect.left - menuWidth - spacing;
        }
        if (left < spacing) left = spacing;
        menu.style.left = left + 'px';

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

    function fillEditForm(row) {
        if (!row || row.cells.length < 9) return;
        if (dfEditDepartment) dfEditDepartment.value = (row.cells[1].textContent || '').trim();
        if (dfEditRemarks) dfEditRemarks.value = (row.cells[2].textContent || '').trim();
        if (dfEditDateToday) dfEditDateToday.value = toInputDate((row.cells[3].textContent || '').trim());
        if (dfEditSubmissionDate) dfEditSubmissionDate.value = toInputDate((row.cells[4].textContent || '').trim());
        if (dfEditCompleted) { const cb = row.cells[5].querySelector('input[type="checkbox"]'); dfEditCompleted.checked = cb ? cb.checked : false; }
        if (dfEditComplianceDate) dfEditComplianceDate.value = toInputDate((row.cells[6].textContent || '').trim());
        if (dfEditUpdatedBy) dfEditUpdatedBy.value = (row.cells[7].textContent || '').trim();
    }

    function saveEditForm(row) {
        if (!row || row.cells.length < 9) return;
        row.cells[1].textContent = dfEditDepartment ? dfEditDepartment.value.trim() : row.cells[1].textContent;
        row.cells[2].textContent = dfEditRemarks ? dfEditRemarks.value.trim() : row.cells[2].textContent;
        row.cells[3].textContent = dfEditDateToday ? toDisplayDate(dfEditDateToday.value) : row.cells[3].textContent;
        row.cells[4].textContent = dfEditSubmissionDate ? toDisplayDate(dfEditSubmissionDate.value) : row.cells[4].textContent;
        row.cells[5].innerHTML = '<label class="ga-check ga-check-tight" style="justify-content: center;"><input type="checkbox" onchange="syncCompletedState(this, null, null, this.closest(\'tr\'))"' + ((dfEditCompleted && dfEditCompleted.checked) ? ' checked' : '') + '> </label>';
        row.cells[5].style.textAlign = 'center';
        row.cells[6].textContent = dfEditComplianceDate ? toDisplayDate(dfEditComplianceDate.value) : row.cells[6].textContent;
        row.cells[7].textContent = dfEditUpdatedBy ? dfEditUpdatedBy.value.trim() : row.cells[7].textContent;
    }

    if (dfEditCompleted) {
        dfEditCompleted.addEventListener('change', function () {
            syncCompletedState(dfEditCompleted, dfEditComplianceDate, dfEditUpdatedBy);
        });
    }

    var dfNewCompleted = document.getElementById('dfNewCompleted');
    var dfNewComplianceDate = document.getElementById('dfNewComplianceDate');
    var dfNewUpdatedBy = document.getElementById('dfNewUpdatedBy');
    if (dfNewCompleted) {
        dfNewCompleted.addEventListener('change', function () {
            syncCompletedState(dfNewCompleted, dfNewComplianceDate, dfNewUpdatedBy);
        });
    }

    function reindexRows() {
        if (!tableBody) return;
        tableBody.querySelectorAll('tr').forEach(function (row, index) {
            if (row.cells && row.cells.length) {
                row.cells[0].textContent = index + 1;
            }
        });
    }

    document.addEventListener('df-add-student', function () {
        var idInput = document.getElementById('addStudentIdInput');
        var nameInput = document.getElementById('addStudentNameInput');
        var studentNo = idInput ? idInput.value.trim() : '';
        var studentName = nameInput ? nameInput.value.trim() : '';

        if (!studentNo || !studentName) {
            alert('Please enter both Student ID and Name.');
            return;
        }

        requestJson(studentStoreUrl, 'POST', {
            student_no: studentNo,
            name: studentName
        }).then(function (payload) {
            var student = payload && payload.data ? payload.data : null;
            if (!student || !student.id) {
                throw new Error('Unable to save student.');
            }

            var existingRow = studentListBody ? studentListBody.querySelector('tr[data-student-pk="' + student.id + '"]') : null;
            var row = existingRow || createStudentRow(student);
            if (row) {
                row.setAttribute('data-student-id', student.student_no || 'N/A');
                row.setAttribute('data-student-name', student.name || 'N/A');
                row.setAttribute('data-student-program', student.program || 'N/A');
                row.setAttribute('data-student-year', student.year_level || 'N/A');
                row.cells[1].textContent = student.student_no || 'N/A';
                row.cells[2].innerHTML = '<a href="#" class="df-student-link" data-df-open-detail>' + (student.name || 'N/A') + '</a>';
                row.cells[3].textContent = student.program || 'N/A';
                row.cells[4].textContent = student.year_level || 'N/A';
            }

            if (idInput) idInput.value = '';
            if (nameInput) nameInput.value = '';
            closeModal(document.getElementById('gaAddStudentModal'));

            if (row) {
                populateSelectedStudent(row);
            }
            loadDeficiencyRecords(student.id, true, false);
        }).catch(function (error) {
            alert(error.message || 'Unable to add student.');
        });
    });

    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = 'flex';
        document.body.classList.add('ga-modal-open');
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.style.display = 'none';
        if (!Array.prototype.some.call(page.querySelectorAll('.req-modal-overlay'), function (item) {
            return item.style.display === 'flex';
        })) {
            document.body.classList.remove('ga-modal-open');
        }
    }

    page.addEventListener('click', function (event) {
        var openDetailLink = event.target.closest('[data-df-open-detail]');
        if (openDetailLink) {
            event.preventDefault();
            var detailRow = openDetailLink.closest('tr');
            populateSelectedStudent(detailRow);
            loadDeficiencyRecords(currentStudentPk, true, false);
            return;
        }

        var openSummaryBtn = event.target.closest('[data-df-open-summary]');
        if (openSummaryBtn) {
            event.preventDefault();
            var summaryRow = openSummaryBtn.closest('tr');
            populateSelectedStudent(summaryRow);
            loadDeficiencyRecords(currentStudentPk, false, true);
            return;
        }

        if (event.target.matches('#dfManageRecordsFromSummary')) {
            closeModal(document.getElementById('gaDeficiencyEmptyViewModal'));
            if (selectedStudentRow) {
                showStudentDetail();
            }
            return;
        }

        var menuToggle = event.target.closest('[data-df-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            toggleActionMenu(menuToggle.getAttribute('data-df-menu-toggle'), menuToggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }

        var openBtn = event.target.closest('[data-ga-modal-open]');
        if (openBtn) {
            openModal(openBtn.getAttribute('data-ga-modal-open'));
            return;
        }

        var actionBtn = event.target.closest('[data-ga-open-action]');
        if (actionBtn) {
            closeActionMenus();
            var action = actionBtn.getAttribute('data-ga-open-action') || 'edit';
            var item = actionBtn.getAttribute('data-ga-item') || 'deficiency row';
            activeRow = actionBtn.closest('tr');
            activeAction = action;

            if (action === 'delete') {
                actionText.textContent = 'Are you sure you want to delete ' + item + '?';
                openModal('gaDeficiencyDeleteModal');
                return;
            }

            actionTitle.textContent = 'EDIT DEFICIENCY';
            fillEditForm(activeRow);
            openModal('gaDeficiencyActionModal');
            return;
        }

        if (event.target.matches('[data-ga-close]')) {
            closeModal(event.target.closest('.req-modal-overlay'));
            return;
        }

        if (event.target.matches('[data-ga-close-delete]')) {
            closeModal(deleteModal);
            return;
        }

        if (event.target.matches('[data-df-save-new]')) {
            if (!currentStudentPk) {
                alert('Please select a student first.');
                return;
            }

            var dfNewDepartment = document.getElementById('dfNewDepartment');
            var dfNewRemarks = document.getElementById('dfNewRemarks');
            var dfNewDateToday = document.getElementById('dfNewDateToday');
            var dfNewSubmissionDate = document.getElementById('dfNewSubmissionDate');
            var dfNewCompleted = document.getElementById('dfNewCompleted');
            var dfNewComplianceDate = document.getElementById('dfNewComplianceDate');
            var dfNewUpdatedBy = document.getElementById('dfNewUpdatedBy');

            syncCompletedState(dfNewCompleted, dfNewComplianceDate, dfNewUpdatedBy);

            var dept = dfNewDepartment ? dfNewDepartment.value.trim() : '';
            var remarks = dfNewRemarks ? dfNewRemarks.value.trim() : '';
            if (!dept || !remarks) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Please complete Department and Remarks.');
                }
                return;
            }

            requestJson(buildUrl(storeUrlTemplate, '__STUDENT__', currentStudentPk), 'POST', {
                department: dept,
                remarks: remarks,
                date_today: dfNewDateToday ? dfNewDateToday.value : null,
                submission_date: dfNewSubmissionDate ? dfNewSubmissionDate.value : null,
                is_completed: dfNewCompleted ? dfNewCompleted.checked : false,
                compliance_date: dfNewComplianceDate ? dfNewComplianceDate.value : null,
                updated_by: (dfNewUpdatedBy && dfNewUpdatedBy.value.trim()) ? dfNewUpdatedBy.value.trim() : getUpdatedByName()
            }).then(function () {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Deficiency added successfully.');
                }
                closeModal(document.getElementById('gaDeficiencyNewModal'));
                loadDeficiencyRecords(currentStudentPk, false, false);
            }).catch(function (error) {
                alert(error.message || 'Unable to save deficiency record.');
            });
            return;
        }

        if (event.target.matches('[data-ga-confirm-action]')) {
            if (activeAction === 'edit' && activeRow) {
                var recordId = activeRow.getAttribute('data-deficiency-id');
                if (!recordId) {
                    alert('Missing deficiency id.');
                    return;
                }

                requestJson(buildUrl(updateUrlTemplate, '__DEF__', recordId), 'PUT', {
                    department: dfEditDepartment ? dfEditDepartment.value.trim() : '',
                    remarks: dfEditRemarks ? dfEditRemarks.value.trim() : '',
                    date_today: dfEditDateToday ? dfEditDateToday.value : null,
                    submission_date: dfEditSubmissionDate ? dfEditSubmissionDate.value : null,
                    is_completed: dfEditCompleted ? dfEditCompleted.checked : false,
                    compliance_date: dfEditComplianceDate ? dfEditComplianceDate.value : null,
                    updated_by: (dfEditUpdatedBy && dfEditUpdatedBy.value.trim()) ? dfEditUpdatedBy.value.trim() : getUpdatedByName()
                }).then(function () {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Deficiency updated successfully.');
                    }
                    closeModal(actionModal);
                    loadDeficiencyRecords(currentStudentPk, false, false);
                }).catch(function (error) {
                    alert(error.message || 'Unable to update deficiency record.');
                });
                return;
            }
            closeModal(actionModal);
            return;
        }

        if (event.target.matches('[data-ga-confirm-delete]')) {
            if (activeRow) {
                var recordId = activeRow.getAttribute('data-deficiency-id');
                if (!recordId) {
                    alert('Missing deficiency id.');
                    return;
                }

                requestJson(buildUrl(destroyUrlTemplate, '__DEF__', recordId), 'DELETE').then(function () {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Deficiency deleted successfully.');
                    }
                    loadDeficiencyRecords(currentStudentPk, false, false);
                }).catch(function (error) {
                    alert(error.message || 'Unable to delete deficiency record.');
                });
            }
            closeModal(deleteModal);
            return;
        }

        if (event.target.matches('#dfTable tbody input[data-df-toggle-complete]')) {
            var row = event.target.closest('tr');
            if (!row || row.cells.length < 8) return;
            var recordId = row.getAttribute('data-deficiency-id');
            if (!recordId) return;

            var completed = event.target.checked;
            var complianceDate = completed ? getTodayInputDate() : null;
            var updatedBy = getUpdatedByName();

            requestJson(buildUrl(updateUrlTemplate, '__DEF__', recordId), 'PUT', {
                department: (row.cells[1].textContent || '').trim(),
                remarks: (row.cells[2].textContent || '').trim(),
                date_today: toInputDate((row.cells[3].textContent || '').trim()),
                submission_date: toInputDate((row.cells[4].textContent || '').trim()),
                is_completed: completed,
                compliance_date: complianceDate,
                updated_by: updatedBy
            }).then(function () {
                row.cells[6].textContent = complianceDate ? toDisplayDate(complianceDate) : '-';
                row.cells[7].textContent = updatedBy;
                renderSummary([]);
                loadDeficiencyRecords(currentStudentPk, false, false);
            }).catch(function (error) {
                event.target.checked = !completed;
                alert(error.message || 'Unable to update completion status.');
            });
        }
    });

    page.querySelectorAll('.req-modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeModal(overlay);
            }
        });
    });

    window.addEventListener('scroll', closeActionMenus, true);
    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-df-menu-toggle]') && !event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });

    var studentSearchInput = document.getElementById('dfStudentSearchInput');
    if (studentSearchInput && studentListBody) {
        studentSearchInput.addEventListener('input', function () {
            var query = (this.value || '').toLowerCase().trim();
            studentListBody.querySelectorAll('tr').forEach(function (row) {
                var rowText = (row.textContent || '').toLowerCase();
                row.style.display = rowText.indexOf(query) > -1 ? '' : 'none';
            });
        });
    }

    if (currentStudentPk) {
        var activeStudentRow = studentListBody ? studentListBody.querySelector('tr[data-student-pk="' + currentStudentPk + '"]') : null;
        if (activeStudentRow) {
            populateSelectedStudent(activeStudentRow);
        }
        loadDeficiencyRecords(currentStudentPk, false, false);
    }
});
</script>
@endpush
