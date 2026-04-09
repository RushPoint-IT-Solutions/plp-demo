@extends('layouts.registrar')

@section('title', 'PLP - Slot Monitoring')
@section('page-title', 'SLOT MONITORING')
@section('body-class', 'page-slot-monitoring')

@section('content')
<div
    class="pf-page"
    id="slotMonitoringPage"
    data-fetch-url="{{ route('registrar.registrar-menu.scheduling.slot-monitoring.data') }}"
    data-store-url="{{ route('registrar.registrar-menu.scheduling.slot-monitoring.store') }}"
    data-update-url-template="{{ route('registrar.registrar-menu.scheduling.slot-monitoring.update', ['slotMonitoring' => '__SLOT_ID__']) }}"
    data-delete-url-template="{{ route('registrar.registrar-menu.scheduling.slot-monitoring.delete', ['slotMonitoring' => '__SLOT_ID__']) }}"
    data-csrf-token="{{ csrf_token() }}"
>

    <div class="sched-filter-bar">
        <div class="sched-filter-row sm-filter-row">
            <div class="sched-filter-group">
                <span class="app-filter-label">School Year</span>
                <select class="app-filter-select" id="smSY">
                    <option value="">- All -</option>
                </select>
            </div>
            <div class="sched-filter-group">
                <span class="app-filter-label">Semester</span>
                <select class="app-filter-select" id="smSemester">
                    <option value="">- All -</option>
                </select>
            </div>
            <div class="sched-filter-group">
                <span class="app-filter-label">Section</span>
                <input type="text" class="app-filter-select" id="smSection" placeholder="Type section">
            </div>
            <div class="sched-filter-group">
                <span class="app-filter-label">Course</span>
                <input type="text" class="app-filter-select" id="smCourse" placeholder="Type course code or name">
            </div>
            <div class="sched-filter-group sm-search-group">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-select sm-search-input" id="smSearch" placeholder="Subject or schedule">
            </div>
        </div>
    </div>

    <div class="sm-action-wrap">
        <button type="button" class="pf-btn-new" id="smAddBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Slot
        </button>
    </div>

    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" id="smTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>SY</th>
                    <th>Semester</th>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Schedule</th>
                    <th>Total Slots</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="smBody"></tbody>
        </table>
    </div>

    <div class="sf-pagination-bar" id="smPaginationBar">
        <div class="sf-pagination-left">
            <label for="smPerPage">Rows per page</label>
            <select id="smPerPage">
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="sf-pagination-right">
            <span id="smPageInfo">Page 1 of 1</span>
            <button type="button" class="sf-page-btn" id="smPrevBtn">Previous</button>
            <button type="button" class="sf-page-btn" id="smNextBtn">Next</button>
            <span id="smTotalInfo">0 total slots</span>
        </div>
    </div>

</div>

<div class="pf-modal-overlay is-hidden" id="addSlotModal">
    <div class="pf-modal-box sm-slot-modal-box">
        <div class="pf-modal-title">Add Slot</div>
        <form id="addSlotForm">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addSlotSchoolYear">School Year</label>
                    <select class="pf-modal-select" id="addSlotSchoolYear" required></select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addSlotSemester">Semester</label>
                    <select class="pf-modal-select" id="addSlotSemester" required></select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addSlotCourse">Course</label>
                    <select class="pf-modal-select" id="addSlotCourse" required></select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addSlotSection">Section</label>
                    <input type="text" class="pf-modal-input" id="addSlotSection" placeholder="e.g. BSIT 1-A" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addSlotSubject">Subject</label>
                    <input type="text" class="pf-modal-input" id="addSlotSubject" placeholder="e.g. CC101" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addSlotSchedule">Schedule</label>
                    <input type="text" class="pf-modal-input" id="addSlotSchedule" placeholder="e.g. M | 09:00-11:00 | RM#12" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addSlotTotal">Total Slots</label>
                    <input type="number" class="pf-modal-input" id="addSlotTotal" required min="1" max="999">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addSlotEnrolled">Enrolled</label>
                    <input type="number" class="pf-modal-input" id="addSlotEnrolled" min="0" max="999" value="0">
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" id="addSlotCancelBtn">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save" id="addSlotSaveBtn">Add Slot</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="pf-modal-overlay is-hidden" id="editSlotModal">
    <div class="pf-modal-box sm-slot-modal-box">
        <div class="pf-modal-title">Edit Slot</div>
        <form id="editSlotForm">
            <input type="hidden" id="editSlotId">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="editSlotSchoolYear">School Year</label>
                    <select class="pf-modal-select" id="editSlotSchoolYear" required></select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="editSlotSemester">Semester</label>
                    <select class="pf-modal-select" id="editSlotSemester" required></select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="editSlotCourse">Course</label>
                    <select class="pf-modal-select" id="editSlotCourse" required></select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="editSlotSection">Section</label>
                    <input type="text" class="pf-modal-input" id="editSlotSection" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="editSlotSubject">Subject</label>
                    <input type="text" class="pf-modal-input" id="editSlotSubject" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="editSlotSchedule">Schedule</label>
                    <input type="text" class="pf-modal-input" id="editSlotSchedule" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="editSlotTotal">Total Slots</label>
                    <input type="number" class="pf-modal-input" id="editSlotTotal" required min="1" max="999">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="editSlotEnrolled">Enrolled</label>
                    <input type="number" class="pf-modal-input" id="editSlotEnrolled" required min="0" max="999">
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" id="editSlotCancelBtn">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save" id="editSlotSaveBtn">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="pf-modal-overlay is-hidden" id="deleteSlotModal">
    <div class="pf-modal-box sm-confirm-modal-box">
        <div class="sm-confirm-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="pf-modal-title sm-danger-title">Delete Slot</div>
        <p class="sm-confirm-text">Are you sure you want to delete</p>
        <p class="sm-confirm-name" id="deleteSlotName"></p>
        <p class="sm-confirm-note">This action cannot be undone.</p>
        <input type="hidden" id="deleteSlotId">
        <div class="pf-modal-actions sm-center-actions">
            <button type="button" class="pf-modal-btn-cancel" id="deleteSlotCancelBtn">Cancel</button>
            <button type="button" class="pf-modal-btn-save sm-danger-btn" id="deleteSlotConfirmBtn">Delete</button>
        </div>
    </div>
</div>

<div class="pf-modal-overlay is-hidden" id="slotSuccessModal">
    <div class="pf-modal-box sm-confirm-modal-box">
        <div class="sm-confirm-icon-wrap sm-success-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>
            </svg>
        </div>
        <div class="pf-modal-title sm-success-title">Success</div>
        <p class="sm-success-message" id="slotSuccessMsg"></p>
        <div class="pf-modal-actions sm-center-actions">
            <button type="button" class="pf-modal-btn-save" id="slotSuccessOkBtn">OK</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/slot-monitoring.js') }}?v={{ filemtime(public_path('js/slot-monitoring.js')) }}"></script>
@endpush
