@extends('layouts.registrar')

@section('title', 'PLP - Deficiency')
@section('page-title', 'DEFICIENCY')
@section('body-class', 'page-services-grading-academic page-deficiency')

@section('content')
@php
    $dfDepartmentFilterOptions = [
        ['value' => '', 'label' => 'All Departments'],
        ['value' => 'Library', 'label' => 'Library'],
        ['value' => 'Cashier', 'label' => 'Cashier'],
        ['value' => 'Registrar', 'label' => 'Registrar'],
    ];

    $dfStatusFilterOptions = [
        ['value' => '', 'label' => 'All Status'],
        ['value' => 'Pending', 'label' => 'Pending'],
        ['value' => 'Completed', 'label' => 'Completed'],
    ];

    $dfDepartmentFormOptions = [
        ['value' => 'Library', 'label' => 'Library'],
        ['value' => 'Cashier', 'label' => 'Cashier'],
        ['value' => 'Registrar', 'label' => 'Registrar'],
    ];
@endphp
<div class="pf-page">
    <div class="ga-page">
        <div id="dfStudentsListView">
            <form method="GET" action="{{ route('registrar.services.grading-academic.deficiency') }}" class="ga-toolbar" id="dfFilterForm">
                @include('registrar.components.search-bar', [
                    'id' => 'dfStudentSearchInput',
                    'name' => 'q',
                    'value' => $search,
                    'placeholder' => 'Search Name, Student ID, Course, Year Level...',
                    'containerClass' => 'ga-search-wrap ga-search-wrap-wide',
                ])
                <button type="button" class="pf-btn-new ga-btn ga-btn-primary" data-ga-modal-open="gaAddStudentModal">+ Add Row</button>
            </form>

            <div class="ga-table-wrap app-table-wrap df-list-table-wrap">
                <table class="ga-table ga-table-compact app-table df-students-table" id="dfStudentsTable" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th class="df-col-action">Action</th>
                            <th class="df-col-index">#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th class="df-col-program">Course</th>
                            <th class="df-col-year">Year Level</th>
                        </tr>
                    </thead>
                    <tbody id="dfTbody">
                        @forelse($students as $index => $student)
                        <tr
                            data-student-pk="{{ $student->id }}"
                            data-student-id="{{ $student->student_no ?: 'N/A' }}"
                            data-student-name="{{ $student->name }}"
                            data-student-program="{{ $student->resolved_program ?: 'N/A' }}"
                            data-student-year="{{ $student->resolved_year_level ?: 'N/A' }}"
                        >
                            <td class="df-col-action">
                                <button type="button" class="apst-action-btn apst-action-btn-eye" data-df-open-summary data-df-student-id="{{ $student->id }}" data-ga-item="{{ $student->name }}" aria-label="View student deficiency summary" title="View Summary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </td>
                            <td class="df-col-index">{{ ($students->firstItem() ?? 1) + $index }}</td>
                            <td>{{ $student->student_no ?: 'N/A' }}</td>
                            <td><a href="#" class="df-student-link" data-df-open-detail data-df-student-id="{{ $student->id }}">{{ $student->name }}</a></td>
                            <td class="df-col-program">{{ $student->resolved_program ?: 'N/A' }}</td>
                            <td class="df-col-year">{{ $student->resolved_year_level ?: 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="df-empty-row">No students found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="svc-table-tfoot">
                        <tr>
                            <td colspan="6">
                                <div class="svc-table-stats">
                                    Total Students: <strong>{{ $students->total() }}</strong>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="app-table-pager">
                {{ $students->links() }}
            </div>
        </div>

        <div id="dfStudentDetailView" style="display:none;">
            <div class="ga-toolbar ga-toolbar-start df-detail-toolbar">
                <button type="button" class="ga-btn ga-btn-muted df-back-btn" data-df-back-list>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back
                </button>
            </div>

            <div class="ga-card ga-filter-card sched-filter-bar">
                <div class="ga-filter-grid ga-filter-grid-deficiency">
                    <div class="ga-filter-search ga-filter-search-deficiency">
                        @include('registrar.components.search-bar', [
                            'id' => 'dfDetailSearchInput',
                            'name' => 'dfDetailSearchInput',
                            'value' => '',
                            'placeholder' => 'Search department, remarks, date, or updated by...',
                            'containerClass' => 'ga-search-wrap ga-search-wrap-wide',
                        ])
                    </div>
                <div>
                    <label class="ga-label">Department</label>
                    @include('registrar.components.listbox-select', [
                        'id' => 'dfDepartmentFilter',
                        'name' => 'dfDepartmentFilter',
                        'options' => $dfDepartmentFilterOptions,
                        'selected' => '',
                        'placeholder' => 'All Departments',
                    ])
                </div>
                <div>
                    <label class="ga-label">Status</label>
                    @include('registrar.components.listbox-select', [
                        'id' => 'dfStatusFilter',
                        'name' => 'dfStatusFilter',
                        'options' => $dfStatusFilterOptions,
                        'selected' => '',
                        'placeholder' => 'All Status',
                    ])
                </div>
                <div>
                    <label class="ga-label">Submission Date</label>
                    <input class="ga-input" id="dfSubmissionDateFilter" type="date">
                </div>
            </div>
        </div>

        <div class="ga-toolbar ga-toolbar-end">
            <button type="button" class="pf-btn-new ga-btn ga-btn-primary" data-ga-modal-open="gaDeficiencyNewModal">+ Add Deficiency</button>
        </div>

        <div class="svc-selected-info df-selected-info">
            <div><strong>Student ID:</strong> <span id="dfInfoStudentId">{{ optional($selectedStudent)->student_no ?: '-' }}</span></div>
            <div><strong>Student Name:</strong> <span id="dfInfoStudentName">{{ optional($selectedStudent)->name ?: '-' }}</span></div>
            <div><strong>Program:</strong> <span id="dfInfoProgram">{{ optional($selectedStudent)->resolved_program ?: optional($selectedStudent)->program ?: '-' }}</span></div>
            <div><strong>Year Level:</strong> <span id="dfInfoYearLevel">{{ optional($selectedStudent)->resolved_year_level ?: optional($selectedStudent)->year_level ?: '-' }}</span></div>
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
                        <th class="df-col-completed">Completed</th>
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
                        <td class="df-col-completed"><label class="ga-check ga-check-tight df-check-center"><input type="checkbox" data-df-toggle-complete {{ $record->is_completed ? 'checked' : '' }}> </label></td>
                        <td>{{ optional($record->compliance_date)->format('m/d/Y') ?: '-' }}</td>
                        <td>{{ $record->updated_by ?: '-' }}</td>
                        <td class="df-col-action-detail">
                            <button type="button" class="apst-action-btn" data-df-menu-toggle="dfMenu{{ $index }}" aria-label="Open row actions" title="Actions">
                                <span></span><span></span><span></span>
                            </button>
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
                    <tr><td colspan="9" class="df-empty-row">No deficiency records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="app-table-pager"></div>

        </div> <!-- End dfStudentDetailView -->

        <div class="req-modal-overlay" id="gaAddStudentModal" style="display:none;">
            <div class="req-modal-box df-student-modal-box">
                <h3 class="req-modal-title">ADD STUDENT</h3>
                <p class="df-modal-help">Search and select an existing student from the database. Add is enabled only after selecting from the list.</p>
                <div class="req-modal-fields df-student-modal-fields">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">STUDENT ID / NUMBER</label>
                        <div class="smrg-search-wrap df-student-search-wrap">
                            <input class="req-modal-input smrg-search-input" type="text" id="addStudentIdInput" placeholder="Search or enter Student ID" autocomplete="off">
                            <div class="smrg-search-dropdown" id="dfStudentIdDropdown"></div>
                        </div>
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">STUDENT NAME</label>
                        <div class="smrg-search-wrap df-student-search-wrap">
                            <input class="req-modal-input smrg-search-input" type="text" id="addStudentNameInput" placeholder="Search or enter Full Name" autocomplete="off">
                            <div class="smrg-search-dropdown" id="dfStudentNameDropdown"></div>
                        </div>
                    </div>
                </div>
                <div class="req-modal-actions df-student-modal-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" id="dfAddStudentSubmitBtn" data-df-add-student disabled>Add</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyEmptyViewModal" style="display:none;">
            <div class="req-modal-box df-summary-modal-box">
                <h3 class="req-modal-title df-summary-title">ACTIVE DEFICIENCIES</h3>
                <p id="dfSummaryStudentName" class="df-summary-student">-</p>
                <div id="dfSummaryBody" class="df-summary-body">
                    <p class="df-summary-empty">No active deficiencies on record.</p>
                </div>
                <div class="req-modal-actions df-summary-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Close</button>
                    <button type="button" class="req-btn-save df-summary-manage-btn" id="dfManageRecordsFromSummary">Manage Records</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyNewModal" style="display:none;">
            <div class="req-modal-box df-deficiency-modal-box">
                <h3 class="req-modal-title" id="gaDeficiencyNewTitle">ADD DEFICIENCY</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">DEPARTMENT</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'dfNewDepartment',
                            'name' => 'dfNewDepartment',
                            'options' => $dfDepartmentFormOptions,
                            'selected' => 'Library',
                            'placeholder' => 'Select department',
                        ])
                    </div>
                    <div class="req-modal-field-group"><label class="req-modal-label">SUBMISSION DATE</label><input class="req-modal-input" type="date" id="dfNewSubmissionDate"></div>
                </div>
                <div class="req-modal-fields df-modal-stack-gap">
                    <div class="req-modal-field-group"><label class="req-modal-label">DATE TODAY</label><input class="req-modal-input" type="date" id="dfNewDateToday"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLIANCE DATE</label><input class="req-modal-input" type="date" id="dfNewComplianceDate"></div>
                </div>
                <div class="req-modal-fields df-modal-stack-gap">
                    <div class="req-modal-field-group"><label class="req-modal-label">UPDATED BY</label><input class="req-modal-input" id="dfNewUpdatedBy" value="{{ $currentUserName }}" readonly></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLETED</label><label class="ga-check"><input type="checkbox" id="dfNewCompleted"> </label></div>
                </div>
                <div class="req-modal-field-group df-modal-stack-gap">
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
            <div class="req-modal-box df-deficiency-modal-box">
                <h3 class="req-modal-title" id="gaDeficiencyActionTitle">EDIT DEFICIENCY</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">DEPARTMENT</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'dfEditDepartment',
                            'name' => 'dfEditDepartment',
                            'options' => $dfDepartmentFormOptions,
                            'selected' => 'Library',
                            'placeholder' => 'Select department',
                        ])
                    </div>
                    <div class="req-modal-field-group"><label class="req-modal-label">SUBMISSION DATE</label><input class="req-modal-input" type="date" id="dfEditSubmissionDate"></div>
                </div>
                <div class="req-modal-fields df-modal-stack-gap">
                    <div class="req-modal-field-group"><label class="req-modal-label">DATE TODAY</label><input class="req-modal-input" type="date" id="dfEditDateToday"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLIANCE DATE</label><input class="req-modal-input" type="date" id="dfEditComplianceDate"></div>
                </div>
                <div class="req-modal-fields df-modal-stack-gap">
                    <div class="req-modal-field-group"><label class="req-modal-label">UPDATED BY</label><input class="req-modal-input" id="dfEditUpdatedBy" value="{{ $currentUserName }}" readonly></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLETED</label><label class="ga-check"><input type="checkbox" id="dfEditCompleted"> </label></div>
                </div>
                <div class="req-modal-field-group df-modal-stack-gap">
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
            <div class="req-modal-box req-modal-success df-delete-modal-box">
                <h3 class="req-modal-title df-delete-modal-title" id="gaDeficiencyDeleteTitle">DELETE DEFICIENCY</h3>
                <p id="gaDeficiencyActionText" class="df-delete-modal-text">Are you sure you want to delete this deficiency?</p>
                <div class="req-modal-actions df-delete-modal-actions">
                    <button class="req-btn-cancel" type="button" data-ga-close-delete>Cancel</button>
                    <button class="req-btn-save df-delete-modal-confirm" type="button" data-ga-confirm-delete>Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.querySelector('.ga-page');
    if (!page) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    var deficiencyPageUrl = @json(route('registrar.services.grading-academic.deficiency'));
    var currentUserName = @json($currentUserName);
    var filterForm = document.getElementById('dfFilterForm');
    var studentSearchInput = document.getElementById('dfStudentSearchInput');
    var detailSearchInput = document.getElementById('dfDetailSearchInput');
    var departmentFilter = document.getElementById('dfDepartmentFilter');
    var statusFilter = document.getElementById('dfStatusFilter');
    var submissionDateFilter = document.getElementById('dfSubmissionDateFilter');
    var addStudentIdInput = document.getElementById('addStudentIdInput');
    var addStudentNameInput = document.getElementById('addStudentNameInput');
    var addStudentSubmitBtn = document.getElementById('dfAddStudentSubmitBtn');
    var studentIdDropdown = document.getElementById('dfStudentIdDropdown');
    var studentNameDropdown = document.getElementById('dfStudentNameDropdown');
    var studentSearchUrl = @json(route('registrar.services.grading-academic.deficiency.students.search'));
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
    var currentStudentRecords = [];
    var selectedLookupStudent = null;
    var studentLookupRequestToken = 0;

    function debounce(fn, delay) {
        var timer = null;

        return function () {
            var args = arguments;
            var context = this;

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(function () {
                fn.apply(context, args);
            }, delay);
        };
    }

    function normalizeText(value) {
        return String(value || '').trim();
    }

    function normalizeCompare(value) {
        return normalizeText(value).replace(/\s+/g, ' ').toLowerCase();
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

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
        return currentUserName || 'Registrar';
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
            '<td class="df-col-action-detail">' +
                '<button type="button" class="apst-action-btn" data-df-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></button>' +
                '<div class="apst-dropdown" id="' + menuId + '">' +
                    '<button type="button" data-ga-open-action="edit" data-ga-item="' + item + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>' +
                    '<button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="' + item + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>Delete</button>' +
                '</div>' +
            '</td>';
    }

    function renderSummary(records) {
        if (!summaryBody) return;
        if (!records || !records.length) {
            summaryBody.innerHTML = '<p class="df-summary-empty">No active deficiencies on record.</p>';
            return;
        }

        var html = '<ul class="df-summary-list">';
        records.forEach(function (record, index) {
            var status = record.is_completed ? 'Completed' : 'Pending';
            var statusClass = record.is_completed ? 'is-completed' : 'is-pending';
            html += '<li class="df-summary-item' + (index ? ' has-top-border' : '') + '">' +
                '<div><strong>' + escapeHtml(record.department || '-') + ':</strong> ' + escapeHtml(record.remarks || '-') + '</div>' +
                '<span class="df-summary-status ' + statusClass + '">' + status + '</span>' +
                '</li>';
        });
        html += '</ul>';
        summaryBody.innerHTML = html;
    }

    function reindexStudentRows() {
        if (!studentListBody) return;
        studentListBody.querySelectorAll('tr').forEach(function (row, index) {
            if (row.cells && row.cells.length) {
                row.cells[1].textContent = index + 1;
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
            '<td class="df-col-action">' +
                '<button type="button" class="apst-action-btn apst-action-btn-eye" data-df-open-summary data-df-student-id="' + student.id + '" data-ga-item="' + (student.name || 'N/A') + '" aria-label="View student deficiency summary" title="View Summary"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>' +
            '</td>' +
            '<td class="df-col-index">0</td>' +
            '<td>' + (student.student_no || 'N/A') + '</td>' +
            '<td><a href="#" class="df-student-link" data-df-open-detail data-df-student-id="' + student.id + '">' + (student.name || 'N/A') + '</a></td>' +
            '<td class="df-col-program">' + ((student.resolved_program || student.program) || 'N/A') + '</td>' +
            '<td class="df-col-year">' + ((student.resolved_year_level || student.year_level) || 'N/A') + '</td>';

        studentListBody.appendChild(tr);
        reindexStudentRows();
        return tr;
    }

    function filteredDetailRecords(records) {
        var keyword = normalizeCompare(detailSearchInput ? detailSearchInput.value : '');
        var selectedDepartment = normalizeCompare(departmentFilter ? departmentFilter.value : '');
        var selectedStatus = normalizeText(statusFilter ? statusFilter.value : '');
        var selectedDate = normalizeText(submissionDateFilter ? submissionDateFilter.value : '');
        var currentStudentId = normalizeCompare(infoStudentId ? infoStudentId.textContent : '');
        var currentStudentName = normalizeCompare(infoStudentName ? infoStudentName.textContent : '');

        return (records || []).filter(function (record) {
            var department = normalizeCompare(record.department || '');
            var status = record.is_completed ? 'Completed' : 'Pending';
            var submissionDateInput = toInputDate(record.submission_date || '');

            if (selectedDepartment && department !== selectedDepartment) {
                return false;
            }

            if (selectedStatus && status !== selectedStatus) {
                return false;
            }

            if (selectedDate && submissionDateInput !== selectedDate) {
                return false;
            }

            if (!keyword) {
                return true;
            }

            var haystack = [
                department,
                normalizeCompare(record.remarks || ''),
                normalizeCompare(record.updated_by || ''),
                normalizeCompare(toDisplayDate(record.submission_date || '')),
                normalizeCompare(toDisplayDate(record.compliance_date || '')),
                normalizeCompare(status),
                currentStudentId,
                currentStudentName,
            ].join(' ');

            return haystack.indexOf(keyword) !== -1;
        });
    }

    function refreshDetailRows() {
        renderDetailRows(filteredDetailRecords(currentStudentRecords));
    }

    function renderDetailRows(records) {
        if (!tableBody) return;

        if (!records || !records.length) {
            tableBody.innerHTML = '<tr><td colspan="9" class="df-empty-row">No deficiency records found.</td></tr>';
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
                '<td class="df-col-completed"><label class="ga-check ga-check-tight df-check-center"><input type="checkbox" data-df-toggle-complete ' + (record.is_completed ? 'checked' : '') + '> </label></td>' +
                '<td>' + toDisplayDate(record.compliance_date) + '</td>' +
                '<td>' + (record.updated_by || '-') + '</td>' +
                buildActionCell((record.department || 'Department') + ' deficiency', menuId) +
                '</tr>';
        });

        tableBody.innerHTML = rowsHtml;
    }

    function loadDeficiencyRecords(studentPk, openDetailAfterLoad, openSummaryAfterLoad) {
        if (!studentPk) {
            currentStudentRecords = [];
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
            currentStudentRecords = (data && data.data) ? data.data : [];
            refreshDetailRows();
            renderSummary(currentStudentRecords);
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
        });
    }

    function toggleActionMenu(menuId) {
        var menu = document.getElementById(menuId);
        if (!menu) return;
        var isOpen = menu.classList.contains('open');
        closeActionMenus();
        if (isOpen) return;
        menu.classList.add('open');
    }

    function setSelectValue(selectElement, value) {
        if (!selectElement) return;
        selectElement.value = value || '';
        selectElement.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function fillEditForm(row) {
        if (!row || row.cells.length < 9) return;
        setSelectValue(dfEditDepartment, (row.cells[1].textContent || '').trim());
        if (dfEditRemarks) dfEditRemarks.value = (row.cells[2].textContent || '').trim();
        if (dfEditDateToday) dfEditDateToday.value = toInputDate((row.cells[3].textContent || '').trim());
        if (dfEditSubmissionDate) dfEditSubmissionDate.value = toInputDate((row.cells[4].textContent || '').trim());
        if (dfEditCompleted) { const cb = row.cells[5].querySelector('input[type="checkbox"]'); dfEditCompleted.checked = cb ? cb.checked : false; }
        if (dfEditComplianceDate) dfEditComplianceDate.value = toInputDate((row.cells[6].textContent || '').trim());
        if (dfEditUpdatedBy) dfEditUpdatedBy.value = (row.cells[7].textContent || '').trim();
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

    function setAddStudentSubmitState(enabled) {
        if (!addStudentSubmitBtn) return;
        addStudentSubmitBtn.disabled = !enabled;
    }

    function resetStudentLookupSelection() {
        selectedLookupStudent = null;
        setAddStudentSubmitState(false);
    }

    function applyStudentCandidate(candidate) {
        if (!candidate) {
            resetStudentLookupSelection();
            return;
        }

        var idValue = candidate.id || candidate.student_id;
        var studentNoValue = candidate.student_no || candidate.student_id;
        var studentNameValue = candidate.name || candidate.student_name;

        if (!idValue || !studentNoValue || !studentNameValue) {
            resetStudentLookupSelection();
            return;
        }

        selectedLookupStudent = {
            id: String(idValue),
            student_no: String(studentNoValue),
            name: String(studentNameValue)
        };

        if (addStudentIdInput) addStudentIdInput.value = selectedLookupStudent.student_no;
        if (addStudentNameInput) addStudentNameInput.value = selectedLookupStudent.name;
        setAddStudentSubmitState(true);
    }

    function closeStudentSearchDropdowns() {
        [studentIdDropdown, studentNameDropdown].forEach(function (dropdown) {
            if (!dropdown) return;
            dropdown.classList.remove('is-open');
            dropdown.innerHTML = '';
        });
    }

    function renderStudentSearchDropdown(dropdown, candidates) {
        if (!dropdown) return;

        if (!candidates.length) {
            dropdown.innerHTML = '<div class="smrg-search-empty">No matching student found.</div>';
            dropdown.classList.add('is-open');
            return;
        }

        var html = '';
        candidates.forEach(function (candidate) {
            html += '<button type="button" class="smrg-search-option" data-df-candidate-db-id="' + escapeHtml(candidate.id) + '" data-df-candidate-id="' + escapeHtml(candidate.student_no) + '" data-df-candidate-name="' + escapeHtml(candidate.name) + '">' +
                '<strong>' + escapeHtml(candidate.student_no) + '</strong> - ' + escapeHtml(candidate.name) +
                '</button>';
        });

        dropdown.innerHTML = html;
        dropdown.classList.add('is-open');
    }

    function requestStudentCandidates(term, done) {
        var requestToken = ++studentLookupRequestToken;
        var url = studentSearchUrl + '?q=' + encodeURIComponent(term || '') + '&limit=12';

        requestJson(url, 'GET').then(function (payload) {
            if (requestToken !== studentLookupRequestToken) {
                return;
            }

            var candidates = payload && Array.isArray(payload.data) ? payload.data : [];
            done(candidates);
        }).catch(function () {
            if (requestToken !== studentLookupRequestToken) {
                return;
            }
            done([]);
        });
    }

    function openStudentSearchDropdown(sourceType) {
        var dropdown = sourceType === 'id' ? studentIdDropdown : studentNameDropdown;
        var input = sourceType === 'id' ? addStudentIdInput : addStudentNameInput;
        if (!dropdown || !input) return;

        requestStudentCandidates(normalizeText(input.value), function (candidates) {
            renderStudentSearchDropdown(dropdown, candidates);
        });
    }

    function bindStudentSearchInput(input, dropdown, sourceType) {
        if (!input || !dropdown) return;

        input.addEventListener('focus', function () {
            openStudentSearchDropdown(sourceType);
        });

        input.addEventListener('input', function () {
            resetStudentLookupSelection();
            openStudentSearchDropdown(sourceType);
        });

        input.addEventListener('blur', function () {
            setTimeout(function () {
                if (!dropdown.matches(':hover')) {
                    dropdown.classList.remove('is-open');
                    dropdown.innerHTML = '';
                }
            }, 120);
        });
    }

    function submitAddStudent() {
        var studentNo = addStudentIdInput ? normalizeText(addStudentIdInput.value) : '';
        var studentName = addStudentNameInput ? normalizeText(addStudentNameInput.value) : '';

        if (!selectedLookupStudent || !selectedLookupStudent.id) {
            alert('Please select a student from the search list.');
            return;
        }

        if (!studentNo || !studentName || normalizeCompare(studentNo) !== normalizeCompare(selectedLookupStudent.student_no) || normalizeCompare(studentName) !== normalizeCompare(selectedLookupStudent.name)) {
            alert('Please select a student from the list and do not edit the values manually.');
            return;
        }

        requestJson(studentStoreUrl, 'POST', {
            student_id: selectedLookupStudent.id,
            student_no: selectedLookupStudent.student_no,
            name: selectedLookupStudent.name
        }).then(function (payload) {
            var student = payload && payload.data ? payload.data : null;
            if (!student || !student.id) {
                throw new Error('Unable to save student.');
            }

            if (addStudentIdInput) addStudentIdInput.value = '';
            if (addStudentNameInput) addStudentNameInput.value = '';
            resetStudentLookupSelection();
            closeStudentSearchDropdowns();
            closeModal(document.getElementById('gaAddStudentModal'));
            window.location.href = deficiencyPageUrl;
        }).catch(function (error) {
            alert(error.message || 'Unable to add student.');
        });
    }

    bindStudentSearchInput(addStudentIdInput, studentIdDropdown, 'id');
    bindStudentSearchInput(addStudentNameInput, studentNameDropdown, 'name');

    document.addEventListener('df-add-student', submitAddStudent);

    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;

        if (id === 'gaAddStudentModal') {
            if (addStudentIdInput) addStudentIdInput.value = '';
            if (addStudentNameInput) addStudentNameInput.value = '';
            resetStudentLookupSelection();
            closeStudentSearchDropdowns();
        }

        modal.style.display = 'flex';
        document.body.classList.add('ga-modal-open');

        if (id === 'gaAddStudentModal' && addStudentIdInput) {
            setTimeout(function () {
                addStudentIdInput.focus();
            }, 0);
        }
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
            closeActionMenus();
            var detailRow = openDetailLink.closest('tr');
            populateSelectedStudent(detailRow);
            loadDeficiencyRecords(currentStudentPk, true, false);
            return;
        }

        var openSummaryBtn = event.target.closest('[data-df-open-summary]');
        if (openSummaryBtn) {
            event.preventDefault();
            closeActionMenus();
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
            toggleActionMenu(menuToggle.getAttribute('data-df-menu-toggle'));
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }

        if (!event.target.closest('.df-student-search-wrap')) {
            closeStudentSearchDropdowns();
        }

        if (event.target.matches('[data-df-add-student]')) {
            document.dispatchEvent(new CustomEvent('df-add-student'));
            return;
        }

        if (event.target.closest('[data-df-back-list]')) {
            showStudentList();
            return;
        }

        var studentOption = event.target.closest('[data-df-candidate-id]');
        if (studentOption) {
            applyStudentCandidate({
                id: studentOption.getAttribute('data-df-candidate-db-id') || '',
                student_no: studentOption.getAttribute('data-df-candidate-id') || '',
                name: studentOption.getAttribute('data-df-candidate-name') || ''
            });
            closeStudentSearchDropdowns();
            return;
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
                compliance_date: dfNewComplianceDate ? dfNewComplianceDate.value : null
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
                    compliance_date: dfEditComplianceDate ? dfEditComplianceDate.value : null
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
                compliance_date: complianceDate
            }).then(function () {
                row.cells[6].textContent = complianceDate ? toDisplayDate(complianceDate) : '-';
                row.cells[7].textContent = updatedBy;
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

    if (filterForm && studentSearchInput) {
        var submitStudentSearch = debounce(function () {
            filterForm.submit();
        }, 250);

        studentSearchInput.addEventListener('input', function () {
            submitStudentSearch();
        });
    }

    if (detailSearchInput) {
        var submitDetailSearch = debounce(function () {
            refreshDetailRows();
        }, 180);

        detailSearchInput.addEventListener('input', submitDetailSearch);
    }

    if (departmentFilter) {
        departmentFilter.addEventListener('change', refreshDetailRows);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', refreshDetailRows);
    }

    if (submissionDateFilter) {
        submissionDateFilter.addEventListener('change', refreshDetailRows);
        submissionDateFilter.addEventListener('input', refreshDetailRows);
    }

    [addStudentIdInput, addStudentNameInput].forEach(function (input) {
        if (!input) return;
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                document.dispatchEvent(new CustomEvent('df-add-student'));
            }
        });
    });

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
