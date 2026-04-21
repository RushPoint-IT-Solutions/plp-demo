@extends('layouts.registrar')

@section('title', 'PLP - Student Discipline')
@section('page-title', 'STUDENT DISCIPLINE')
@section('body-class', 'page-student-account page-student-discipline')

@section('content')
@php
    $sdBaseStudentTypeOptions = isset($studentTypeOptions) && is_array($studentTypeOptions)
        ? $studentTypeOptions
        : [
            ['value' => 'OLD', 'label' => 'Old'],
            ['value' => 'NEW', 'label' => 'New'],
            ['value' => 'TRANSFEREE', 'label' => 'Transferee'],
        ];

    $sdStudentTypeFilterOptions = array_merge([
        ['value' => '', 'label' => 'All Types'],
    ], $sdBaseStudentTypeOptions);

    $sdSchoolYearOptions = collect(isset($schoolYears) && is_array($schoolYears) ? $schoolYears : ['2025-2026'])
        ->map(function ($year) {
            return ['value' => (string) $year, 'label' => (string) $year];
        })
        ->values()
        ->all();

    $sdTermOptions = isset($termOptions) && is_array($termOptions)
        ? $termOptions
        : [
            ['value' => 'First', 'label' => 'First'],
            ['value' => 'Second', 'label' => 'Second'],
            ['value' => 'Summer', 'label' => 'Summer'],
        ];

    $sdSemesterMap = isset($semesterMap) && is_array($semesterMap)
        ? $semesterMap
        : [];

    $sdCaseTypeOptions = isset($caseTypeOptions) && is_array($caseTypeOptions) && count($caseTypeOptions)
        ? $caseTypeOptions
        : [
            ['value' => '', 'label' => '-select type-'],
        ];

    $sdActionTypeOptions = isset($actionTypeOptions) && is_array($actionTypeOptions) && count($actionTypeOptions)
        ? $actionTypeOptions
        : [
            ['value' => '', 'label' => '-select type-'],
        ];

    $sdGenderOptions = [
        ['value' => 'Male', 'label' => 'Male'],
        ['value' => 'Female', 'label' => 'Female'],
    ];
@endphp
<div class="pf-page">
    <div
        class="ga-page sd-page"
        id="sdPage"
        data-list-url="{{ route('registrar.services.student-account.student-discipline.data') }}"
        data-student-search-url="{{ route('registrar.services.student-account.student-discipline.students.search') }}"
        data-program-search-url="{{ route('registrar.services.student-account.student-discipline.programs.search') }}"
        data-student-store-url="{{ route('registrar.services.student-account.student-discipline.students.store') }}"
        data-student-update-url-template="{{ route('registrar.services.student-account.student-discipline.students.update', ['studentDisciplineStudent' => '__DISCIPLINE_STUDENT__']) }}"
        data-student-destroy-url-template="{{ route('registrar.services.student-account.student-discipline.students.destroy', ['studentDisciplineStudent' => '__DISCIPLINE_STUDENT__']) }}"
        data-record-list-url-template="{{ route('registrar.services.student-account.student-discipline.records', ['studentDisciplineStudent' => '__DISCIPLINE_STUDENT__']) }}"
        data-record-store-url-template="{{ route('registrar.services.student-account.student-discipline.records.store', ['studentDisciplineStudent' => '__DISCIPLINE_STUDENT__']) }}"
        data-record-update-url-template="{{ route('registrar.services.student-account.student-discipline.records.update', ['studentDisciplineRecord' => '__RECORD__']) }}"
        data-record-destroy-url-template="{{ route('registrar.services.student-account.student-discipline.records.destroy', ['studentDisciplineRecord' => '__RECORD__']) }}"
        data-case-type-options='@json($sdCaseTypeOptions)'
        data-action-type-options='@json($sdActionTypeOptions)'
        data-semester-map='@json($sdSemesterMap)'
    >
        <div id="sdListView">
            <div class="ga-card ga-filter-card sched-filter-bar sd-filter-card">
                <div class="sd-top-row">
                    <div class="sd-search-card">
                        <div class="sd-filter-title">Filter Students</div>
                        <div class="sd-field-grid">
                            <div class="sd-criteria-item">
                                <label class="app-filter-label" for="sdStudentId">Student ID</label>
                                <input id="sdStudentId" type="text" class="pf-search-input" placeholder="Student ID">
                            </div>
                            <div class="sd-criteria-item">
                                <label class="app-filter-label" for="sdStudentTypeFilter">Student Type</label>
                                @include('registrar.components.listbox-select', [
                                    'id' => 'sdStudentTypeFilter',
                                    'name' => 'sdStudentTypeFilter',
                                    'options' => $sdStudentTypeFilterOptions,
                                    'selected' => '',
                                    'placeholder' => 'All Types',
                                ])
                            </div>
                            <div class="sd-criteria-item">
                                <label class="app-filter-label" for="sdFullName">Full Name</label>
                                <input id="sdFullName" type="text" class="pf-search-input" placeholder="Last Name, First Name">
                            </div>
                        </div>

                        <div class="sd-filter-actions">
                            <button type="button" class="pf-btn-new" data-sd-click="filter-students">Search</button>
                            <button type="button" class="sd-clear-btn" data-sd-click="clear-filters">Clear Entries</button>
                        </div>
                    </div>

                    <div class="sd-filter-divider"></div>

                    <div class="sd-system-card">
                        <div class="sd-filter-title">System Configuration</div>
                        <div class="sd-system-grid">
                            <div class="sd-filter-item">
                                <label class="app-filter-label" for="sdSchoolYear">School Year:</label>
                                @include('registrar.components.listbox-select', [
                                    'id' => 'sdSchoolYear',
                                    'name' => 'sdSchoolYear',
                                    'options' => $sdSchoolYearOptions,
                                    'selected' => isset($defaultSchoolYear) ? $defaultSchoolYear : ($sdSchoolYearOptions[0]['value'] ?? ''),
                                    'placeholder' => 'School Year',
                                ])
                            </div>
                            <div class="sd-filter-item">
                                <label class="app-filter-label" for="sdTerm">Term:</label>
                                @include('registrar.components.listbox-select', [
                                    'id' => 'sdTerm',
                                    'name' => 'sdTerm',
                                    'options' => $sdTermOptions,
                                    'selected' => isset($defaultTerm) ? $defaultTerm : 'First',
                                    'placeholder' => 'Term',
                                ])
                            </div>
                            <button type="button" class="pf-btn-new sd-set-btn" data-sd-click="apply-system-config">Set</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sd-table-head">
                <button type="button" class="pf-btn-new sd-add-student-btn" data-sd-click="open-add-student-modal">+ Add Student</button>
            </div>

            <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table app-table" id="sdStudentsTable">
                    <thead>
                        <tr>
                            <th class="sd-col-action">Action</th>
                            <th class="sd-col-index">#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Degree Program</th>
                            <th>Date Of Birth</th>
                            <th>Gender</th>
                            <th>Student Type</th>
                        </tr>
                    </thead>
                    <tbody id="sdStudentsBody"></tbody>
                </table>
            </div>
        </div>

        <div id="sdDetailView" class="sd-hidden">
            <div class="sd-detail-topbar">
                <button type="button" class="eval-btn-outline" data-sd-click="back-to-list">Back</button>
            </div>

            <div class="sd-student-banner">
                <div><span>Student ID:</span> <strong id="sdBannerStudentId">-</strong></div>
                <div><span>Student Name:</span> <strong id="sdBannerStudentName">-</strong></div>
            </div>

            <div class="sd-detail-head">
                <div class="sd-detail-title">STUDENT CONDUCT</div>
                <div class="sd-detail-actions">
                    <button type="button" class="sd-new-btn" data-sd-click="open-record-modal">+ New Record</button>
                    <button type="button" class="gs-view-btn" data-sd-click="print-record">Print Record</button>
                </div>
            </div>

            <p class="sd-record-hint">Click a record row to view full incident and action details.</p>

            <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table app-table" id="sdConductTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Incident Type</th>
                            <th>Action Type</th>
                            <th>Completed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="sdConductBody">
                        <tr class="sd-empty-row">
                            <td colspan="5">List Empty.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <section id="sdRecordDetailCard" class="sd-record-detail-card sd-hidden" aria-live="polite" aria-label="Record details">
                <div class="sd-record-detail-head">
                    <div class="sd-record-detail-title">Selected Record Details</div>
                    <span id="sdRecordDetailStatus" class="sd-record-detail-status">-</span>
                </div>
                <div class="sd-record-detail-grid">
                    <div class="sd-record-detail-item">
                        <span class="sd-record-detail-label">Incident Date</span>
                        <strong id="sdRecordDetailIncidentDate">-</strong>
                    </div>
                    <div class="sd-record-detail-item">
                        <span class="sd-record-detail-label">Action Date</span>
                        <strong id="sdRecordDetailActionDate">-</strong>
                    </div>
                    <div class="sd-record-detail-item">
                        <span class="sd-record-detail-label">Case Type</span>
                        <strong id="sdRecordDetailCaseType">-</strong>
                    </div>
                    <div class="sd-record-detail-item">
                        <span class="sd-record-detail-label">Action Type</span>
                        <strong id="sdRecordDetailActionType">-</strong>
                    </div>
                    <div class="sd-record-detail-item">
                        <span class="sd-record-detail-label">Called By</span>
                        <strong id="sdRecordDetailCalledBy">-</strong>
                    </div>
                    <div class="sd-record-detail-item">
                        <span class="sd-record-detail-label">Counselor</span>
                        <strong id="sdRecordDetailCounselor">-</strong>
                    </div>
                    <div class="sd-record-detail-item">
                        <span class="sd-record-detail-label">Walk In</span>
                        <strong id="sdRecordDetailWalkIn">-</strong>
                    </div>
                    <div class="sd-record-detail-item">
                        <span class="sd-record-detail-label">Updated By</span>
                        <strong id="sdRecordDetailUpdatedBy">-</strong>
                    </div>
                </div>
                <div class="sd-record-detail-body-grid">
                    <article class="sd-record-detail-block">
                        <h4>Description</h4>
                        <p id="sdRecordDetailDescription">-</p>
                    </article>
                    <article class="sd-record-detail-block">
                        <h4>Remarks</h4>
                        <p id="sdRecordDetailRemarks">-</p>
                    </article>
                </div>
            </section>

            <section id="sdPrintRecordsSheet" class="sd-print-records-sheet sd-hidden" aria-hidden="true">
                <div class="sd-print-sheet-head">
                    <h3 class="sd-print-sheet-title">Student Conduct Records</h3>
                    <div class="sd-print-sheet-student-meta">
                        <span>Student ID: <strong id="sdPrintStudentId">-</strong></span>
                        <span>Student Name: <strong id="sdPrintStudentName">-</strong></span>
                    </div>
                </div>
                <div id="sdPrintRecordsList" class="sd-print-records-list"></div>
            </section>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdRecordModal">
    <div class="req-modal-box sd-modal-box">
        <div class="sd-modal-section">
            <div class="sd-modal-section-head">
                <div class="sd-modal-title">INCIDENT</div>
                <label class="setup-checkbox-label sd-section-toggle">
                    <input id="sdWalkIn" type="checkbox" class="req-checkbox-input">
                    <span>Walk In</span>
                </label>
            </div>

            <div class="sd-modal-grid">
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdIncidentDate">Incident Date</label>
                    <input id="sdIncidentDate" type="date" class="req-modal-input sd-date-input">
                </div>
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdCaseTypeSearch">Case Type</label>
                    <input type="hidden" id="sdCaseType">
                    @include('registrar.components.search-dropdown-input', [
                        'id' => 'sdCaseTypeSearch',
                        'placeholder' => 'Search case type',
                        'wrapperClass' => 'sd-record-search-wrap',
                        'dropdownId' => 'sdCaseTypeDropdown',
                    ])
                </div>
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdCalledBy">Called By</label>
                    @include('registrar.components.search-dropdown-input', [
                        'id' => 'sdCalledBy',
                        'placeholder' => 'Search name',
                        'wrapperClass' => 'sd-record-search-wrap',
                        'dropdownId' => 'sdCalledByDropdown',
                    ])
                </div>

                <div class="sd-modal-field sd-modal-field-full">
                    <label class="req-modal-label" for="sdDescription">Description</label>
                    <textarea id="sdDescription" class="req-modal-input sd-modal-textarea" rows="2" placeholder="Purpose..."></textarea>
                </div>
            </div>
        </div>

        <div class="sd-modal-section">
            <div class="sd-modal-section-head">
                <div class="sd-modal-title sd-modal-title-action">ACTION</div>
                <label class="setup-checkbox-label sd-section-toggle">
                    <input id="sdCompleted" type="checkbox" class="req-checkbox-input">
                    <span>Completed</span>
                </label>
            </div>

            <div class="sd-modal-grid">
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdActionDate">Action Date</label>
                    <input id="sdActionDate" type="date" class="req-modal-input sd-date-input">
                </div>
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdActionTypeSearch">Action Type</label>
                    <input type="hidden" id="sdActionType">
                    @include('registrar.components.search-dropdown-input', [
                        'id' => 'sdActionTypeSearch',
                        'placeholder' => 'Search action type',
                        'wrapperClass' => 'sd-record-search-wrap',
                        'dropdownId' => 'sdActionTypeDropdown',
                    ])
                </div>
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdCounselor">Counselor</label>
                    @include('registrar.components.search-dropdown-input', [
                        'id' => 'sdCounselor',
                        'placeholder' => 'Search counselor',
                        'wrapperClass' => 'sd-record-search-wrap',
                        'dropdownId' => 'sdCounselorDropdown',
                    ])
                </div>

                <div class="sd-modal-field sd-modal-field-full">
                    <label class="req-modal-label" for="sdRemarks">Remarks</label>
                    <input id="sdRemarks" type="text" class="req-modal-input" placeholder="Purpose...">
                </div>
            </div>
        </div>

        <div class="req-modal-actions sd-modal-actions">
            <button type="button" class="req-btn-cancel" data-sd-click="close-record-modal">Cancel</button>
            <button type="button" class="req-btn-save" id="sdRecordSaveBtn" data-sd-click="save-record">Save Record</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdDeleteRecordModal">
    <div class="req-modal-box req-modal-success sd-delete-modal">
        <h3 class="req-modal-title sd-delete-title">DELETE RECORD</h3>
        <p id="sdDeleteRecordText" class="sd-delete-text">Are you sure you want to delete this conduct record?</p>
        <div class="req-modal-actions sd-delete-actions">
            <button type="button" class="req-btn-cancel" data-sd-click="close-delete-record-modal">Cancel</button>
            <button type="button" class="req-btn-save sd-delete-confirm-btn" data-sd-click="confirm-delete-record">Delete</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdEditStudentModal">
    <div class="req-modal-box sd-student-modal">
        <h3 class="req-modal-title">EDIT STUDENT</h3>
        <input type="hidden" id="sdEditDisciplineStudentId">
        <input type="hidden" id="sdEditCourseId">
        <div class="req-modal-fields">
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT ID</label>
                <input type="text" id="sdEditStudentId" class="req-modal-input" readonly>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT NAME</label>
                <input type="text" id="sdEditStudentName" class="req-modal-input" readonly>
            </div>
        </div>
        <div class="req-modal-fields sd-row-gap">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DEGREE PROGRAM</label>
                @include('registrar.components.search-dropdown-input', [
                    'id' => 'sdEditDegreeProgram',
                    'placeholder' => 'Search degree program',
                    'wrapperClass' => 'sd-program-search-wrap',
                    'dropdownId' => 'sdEditProgramDropdown',
                ])
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">DATE OF BIRTH</label>
                <input type="date" id="sdEditBirthDate" class="req-modal-input sd-date-input">
            </div>
        </div>
        <div class="req-modal-fields sd-row-gap">
            <div class="req-modal-field-group">
                <label class="req-modal-label">GENDER</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'sdEditGender',
                    'name' => 'sdEditGender',
                    'options' => $sdGenderOptions,
                    'selected' => 'Male',
                    'placeholder' => 'Gender',
                ])
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT TYPE</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'sdEditStudentType',
                    'name' => 'sdEditStudentType',
                    'options' => $sdBaseStudentTypeOptions,
                    'selected' => $sdBaseStudentTypeOptions[0]['value'] ?? '',
                    'placeholder' => 'Student Type',
                ])
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" data-sd-click="close-edit-student-modal">Cancel</button>
            <button type="button" class="req-btn-save" data-sd-click="save-student-edit">Save Changes</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdAddStudentModal">
    <div class="req-modal-box sd-student-modal">
        <h3 class="req-modal-title">ADD STUDENT</h3>
        <p class="sd-add-help">Search and pick an existing student and degree program from the dropdown list.</p>
        <input type="hidden" id="sdAddStudentDbId">
        <input type="hidden" id="sdAddCourseId">
        <div class="req-modal-fields">
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT ID</label>
                @include('registrar.components.search-dropdown-input', [
                    'id' => 'sdAddStudentId',
                    'placeholder' => 'Search student ID',
                    'wrapperClass' => 'sd-student-search-wrap',
                    'dropdownId' => 'sdAddStudentIdDropdown',
                ])
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT NAME</label>
                @include('registrar.components.search-dropdown-input', [
                    'id' => 'sdAddStudentName',
                    'placeholder' => 'Search student name',
                    'wrapperClass' => 'sd-student-search-wrap',
                    'dropdownId' => 'sdAddStudentNameDropdown',
                ])
            </div>
        </div>
        <div class="req-modal-fields sd-row-gap">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DEGREE PROGRAM</label>
                @include('registrar.components.search-dropdown-input', [
                    'id' => 'sdAddDegreeProgram',
                    'placeholder' => 'Search degree program',
                    'wrapperClass' => 'sd-program-search-wrap sd-auto-filled-search',
                    'dropdownId' => 'sdAddProgramDropdown',
                    'inputAttributes' => [
                        'readonly' => true,
                        'disabled' => true,
                        'tabindex' => '-1',
                    ],
                ])
                <p class="sd-field-note">Auto-filled from selected student record.</p>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">DATE OF BIRTH</label>
                <input type="date" id="sdAddBirthDate" class="req-modal-input sd-date-input sd-auto-filled-input" readonly disabled>
            </div>
        </div>
        <div class="req-modal-fields sd-row-gap">
            <div class="req-modal-field-group sd-auto-filled-select">
                <label class="req-modal-label">GENDER</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'sdAddGender',
                    'name' => 'sdAddGender',
                    'options' => $sdGenderOptions,
                    'selected' => 'Male',
                    'placeholder' => 'Gender',
                ])
                <p class="sd-field-note">Auto-filled from selected student record.</p>
            </div>
            <div class="req-modal-field-group sd-student-type-fixed">
                <label class="req-modal-label">STUDENT TYPE</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'sdAddStudentType',
                    'name' => 'sdAddStudentType',
                    'options' => $sdBaseStudentTypeOptions,
                    'selected' => 'OLD',
                    'placeholder' => 'Student Type',
                ])
                <p class="sd-field-note">Auto-filled for existing students.</p>
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" data-sd-click="close-add-student-modal">Cancel</button>
            <button type="button" class="req-btn-save" id="sdAddStudentSubmitBtn" data-sd-click="save-new-student" disabled>Add Student</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdDeleteStudentModal">
    <div class="req-modal-box req-modal-success sd-delete-modal">
        <h3 class="req-modal-title sd-delete-title">DELETE STUDENT</h3>
        <p id="sdDeleteText" class="sd-delete-text">Are you sure you want to delete this student?</p>
        <div class="req-modal-actions sd-delete-actions">
            <button type="button" class="req-btn-cancel" data-sd-click="close-delete-student-modal">Cancel</button>
            <button type="button" class="req-btn-save sd-delete-confirm-btn" data-sd-click="confirm-delete-student">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script src="{{ asset('js/student-discipline.js') }}"></script>
@endpush
