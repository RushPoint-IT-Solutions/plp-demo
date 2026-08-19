@extends('layouts.registrar')

@section('title', 'PLP - Student Enrollment')
@section('page-title', 'STUDENT ENROLLMENT')
@section('body-class', 'page-student-enrollment')

@section('content')
@php
    $studentRows = $students;
    $studentTotal = method_exists($students, 'total') ? $students->total() : collect($students ?? [])->count();
    $studentSearch = (string) ($search ?? request('q', ''));
    $studentSortDirection = (string) ($sortDirection ?? request('sort', 'asc'));
    $applicantRows = collect($applicants ?? [])->map(function ($applicant) {
        $fullName = trim(preg_replace('/\s+/', ' ', trim((string) $applicant->full_name)));

        return [
            'id' => (int) $applicant->id,
            'applicant_id' => (string) $applicant->applicant_id,
            'name' => $fullName,
            'label' => trim(((string) $applicant->applicant_id) . ' - ' . $fullName),
        ];
    })->values();
    $studentSortOptions = [
        ['value' => 'asc', 'label' => 'Ascending'],
        ['value' => 'desc', 'label' => 'Descending'],
    ];
    $studentProgramOptions = collect($courses ?? [])->map(function ($course) {
        $label = trim(((string) ($course->code ?: '')) . ' - ' . ((string) ($course->name ?: '')));

        return [
            'value' => (string) $course->code,
            'label' => $label !== '-' ? $label : ((string) $course->name ?: ('Course #' . $course->id)),
        ];
    })->prepend([
        'value' => '',
        'label' => 'Select Program',
    ])->values()->all();
    $studentYearOptions = [
        ['value' => 'First', 'label' => 'First'],
        ['value' => 'Second', 'label' => 'Second'],
        ['value' => 'Third', 'label' => 'Third'],
        ['value' => 'Fourth', 'label' => 'Fourth'],
    ];
@endphp

@if(session('success'))
    <div class="alert alert-success" style="padding: 15px; margin: 15px 0; background: #d4edda; color: #155724; border-radius: 4px;">
        {{ session('success') }}
        @if(session('success_password'))
            <br><strong>Temporary Password:</strong> {{ session('success_password') }}
            <br><em>Please provide this to the student. They will be forced to change it on their first login.</em>
        @endif
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" style="padding: 15px; margin: 15px 0; background: #f8d7da; color: #721c24; border-radius: 4px;">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="pf-page">
    <div id="seListView">
        <div class="se-toolbar">
            <form class="se-toolbar-left" id="seFilterForm" method="GET" action="{{ route('registrar.registrar-menu.student-mgmt.student-enrollment') }}">
                <div class="se-search-wrap">
                    <span class="app-filter-label">Search</span>
                    <div class="pf-search-wrap" style="max-width:100%;">
                        <span class="pf-search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="text" id="seSearch" name="q" class="pf-search-input" placeholder="Search Student ID / Name" value="{{ $studentSearch }}" oninput="filterEnrollmentRows()" autocomplete="off">
                    </div>
                </div>
                <div class="se-sort-wrap">
                    <span class="app-filter-label">Sort</span>
                    @include('registrar.components.listbox-select', [
                        'id' => 'seSort',
                            'name' => 'sort',
                        'options' => $studentSortOptions,
                            'selected' => $studentSortDirection,
                        'placeholder' => 'Ascending',
                    ])
                </div>
                </form>
            <div class="se-view-toggle" role="group" aria-label="Student enrollment view">
                <button type="button" id="seViewListBtn" class="se-view-toggle-btn is-active" onclick="setEnrollmentViewMode('list')" title="List View" aria-pressed="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
                <button type="button" id="seViewCardBtn" class="se-view-toggle-btn" onclick="setEnrollmentViewMode('card')" title="Card View" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </button>
            </div>
            <div class="se-toolbar-right">
                <button type="button" class="pf-btn-new" onclick="openImportCsvModal()">Import CSV</button>
                <button type="button" class="pf-btn-new" onclick="openAddStudentModal()">+Add Student</button>
            </div>
        </div>

        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table" id="seTable" data-no-auto-pager="1" data-total-students="{{ $studentTotal }}">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Year Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="seTableBody">
                    @forelse($studentRows as $index => $student)
                        <tr data-student-id="{{ $student->student_no }}" data-student-row-id="{{ $student->id }}" data-student-program-value="{{ $student->program }}" data-student-year-level-value="{{ $student->year_level }}">
                            <td>{{ ($studentRows->firstItem() ?? 0) + $index }}</td>
                            <td>{{ $student->student_no }}</td>
                            <td><a href="#" class="se-name-link" onclick='openEnrollmentDetail(@json($student->student_no), @json($student->name)); return false;'>{{ $student->name }}</a></td>
                            <td>{{ trim((string) (optional($student->canonicalCourse)->name ?: $student->program ?: 'N/A')) }}</td>
                            <td>{{ $student->year_level ?: 'N/A' }}</td>
                            <td>
                                <div class="se-row-actions">
                                    <button type="button" class="doclist-action-btn" onclick='openEnrollmentDetail(@json($student->student_no), @json($student->name))' title="View Enrollment" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;color:#004d27;background:#d1fae5;border-radius:5px;width:28px;height:28px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                    </button>
                                    <button type="button" class="doclist-action-btn doclist-edit-btn" onclick="openEditStudentModal('{{ $student->id }}')" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </button>
                                    <button type="button" class="doclist-action-btn doclist-delete-btn" onclick="openDeleteStudentModal('{{ $student->id }}')" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="se-empty-row">
                            <td colspan="6">No students found.</td>
                        </tr>
                    @endforelse
                    <tr class="se-total-row">
                        <td colspan="6" class="se-total-cell">Total Students: <strong id="seTotalCount">{{ $studentTotal }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="se-card-view" id="seCardView" style="display:none;">
            <div class="se-card-grid" id="seCardGrid">
                @forelse($studentRows as $student)
                    <article class="se-student-mini-card" data-student-card-id="{{ $student->student_no }}" data-student-card-row-id="{{ $student->id }}">
                        <div class="se-card-main">
                            <button type="button" class="se-card-name" onclick='openEnrollmentDetail(@json($student->student_no), @json($student->name))'>{{ $student->name }}</button>
                            <div class="se-card-id">{{ $student->student_no ?: 'N/A' }}</div>
                        </div>
                        <div class="se-card-meta">
                            <div><span>Program</span><strong>{{ trim((string) (optional($student->canonicalCourse)->name ?: $student->program ?: 'N/A')) }}</strong></div>
                            <div><span>Year Level</span><strong>{{ $student->year_level ?: 'N/A' }}</strong></div>
                        </div>
                        <div class="se-card-actions">
                            <button type="button" class="doclist-action-btn" onclick='openEnrollmentDetail(@json($student->student_no), @json($student->name))' title="View Enrollment" style="color:#004d27;background:#d1fae5;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            </button>
                            <button type="button" class="doclist-action-btn doclist-edit-btn" onclick="openEditStudentModal('{{ $student->id }}')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                            <button type="button" class="doclist-action-btn doclist-delete-btn" onclick="openDeleteStudentModal('{{ $student->id }}')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="se-card-empty">No students found.</div>
                @endforelse
            </div>
        </div>

        <div class="app-table-pager se-pagination">
            {{ $studentRows->links() }}
        </div>
    </div>

    <div id="seDetailView" style="display:none;">
        <div class="se-detail-topbar">
            <button type="button" class="se-btn se-btn-danger" onclick="withdrawEnrollment()">Withdraw Enrollment</button>
        </div>

        <div class="se-student-card">
            <div class="se-student-grid">
                <div><span class="se-meta-label">Student Name:</span> <span id="seMetaName">BARES, MARK JAY</span></div>
                <div><span class="se-meta-label">Student No.:</span> <span id="seMetaId">2223A8137</span></div>
                <div><span class="se-meta-label">Program:</span> <span id="seMetaProgram">Bachelor Of Science In Computer Science</span></div>
                <div><span class="se-meta-label">Year Level:</span> <span id="seMetaYearLevel">Fourth Year</span></div>
                <div><span class="se-meta-label">Status:</span> OLD (Regular) 3 Unit(s) Allowed (CY2223)</div>
                <div><span class="se-meta-label">Section:</span> A</div>
                <div><span class="se-meta-label">Academic Year:</span> 2025-2026</div>
                <div><span class="se-meta-label">Term:</span> First</div>
            </div>
        </div>

        <div class="se-section">
            <div class="se-subject-title">Current Enrolled Subjects:</div>
            <div class="se-subject-tools">
                <div class="se-current-section-inline">Section: <strong id="seCurrentSectionLabel">BSIT 4-A</strong></div>
                <button type="button" id="seBtnCurrentEdit" class="se-tool-btn se-tool-btn-edit" onclick="openSubjectRowModal('edit', 'seChangeFromTable')">Edit Selected</button>
                <button type="button" id="seBtnReplaceCatalog" class="pf-btn-new se-tool-btn" onclick="changeSelectedFromCatalog()" title="Replace one selected enrolled row using one selected catalog row">Replace With Catalog</button>
                <button type="button" id="seBtnDropCurrent" class="se-tool-btn se-tool-btn-danger" onclick="dropSelectedCurrentSubjects()">Drop Selected</button>
            </div>
            <div class="se-action-note">For replace: select exactly 1 row in Current Enrolled and 1 row in Subject Catalog.</div>
            <div class="student-table-wrapper table-responsive">
                <table class="student-table registrar-table se-subject-table" id="seChangeFromTable">
                    <colgroup>
                        <col style="width:54px;">
                        <col style="width:130px;">
                        <col style="width:240px;">
                        <col style="width:96px;">
                        <col style="width:72px;">
                        <col style="width:390px;">
                        <col style="width:110px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject Code</th>
                            <th>Description</th>
                            <th>Adjustment</th>
                            <th>Units</th>
                            <th>Schedule</th>
                            <th>Enrolled by</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>CAP 102</td>
                            <td>CAPSTONE PROJECT AND RESEARCH 2</td>
                            <td></td>
                            <td>3</td>
                            <td>A - BSIT 4-A - F 07:00AM-12:00PM BLDG.1 - 401</td>
                            <td>Admin 1</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>IT ELEC 4</td>
                            <td>ELECTIVE 4</td>
                            <td></td>
                            <td>3</td>
                            <td>A - BSIT 4-A - S 05:00PM-08:00PM ONLINE CLASS</td>
                            <td>Admin 1</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>IAS 102</td>
                            <td>INFORMATION ASSURANCE AND SECURITY 2</td>
                            <td></td>
                            <td>3</td>
                            <td>A - BSIT 4-A - T 07:00PM-09:00PM ONLINE CLASS/S 09:00PM-10:00PM ONLINE CLASS</td>
                            <td>Admin 1</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>SPI 101</td>
                            <td>SOCIAL AND PROFESSIONAL ISSUES</td>
                            <td></td>
                            <td>3</td>
                            <td>A - BSIT 4-A - W 02:00PM-05:00PM BLDG.1 - 401</td>
                            <td>Admin 1</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>SAM 101</td>
                            <td>SYSTEM ADMINISTRATION AND MAINTENANCE</td>
                            <td></td>
                            <td>3</td>
                            <td>A - BSIT 4-A - W 07:00PM-10:00PM ONLINE CLASS/F 07:00PM-09:00PM ONLINE CLASS/S 08:00PM-09:00PM ONLINE CLASS</td>
                            <td>Admin 1</td>
                        </tr>
                        <tr class="se-total-units-row">
                            <td colspan="7" class="se-units-cell">Total Units Taken: <strong>15</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="se-section-actions">
                <button type="button" class="se-btn se-btn-save" onclick="saveDetailSelection()">Save Enrollment Changes</button>
            </div>
        </div>

        <div class="se-section">
            <div class="se-subject-title">Subject Catalog:</div>
            <div class="se-catalog-filters-row">
                <div class="se-mini-field">
                    <span class="app-filter-label">Program</span>
                    @include('registrar.components.listbox-select', [
                        'id' => 'seCatalogProgramFilter',
                        'name' => 'seCatalogProgramFilter',
                        'options' => [
                            ['value' => '', 'label' => 'All Programs'],
                            ['value' => 'BSIT', 'label' => 'BSIT'],
                            ['value' => 'BSCS', 'label' => 'BSCS'],
                            ['value' => 'BSBA', 'label' => 'BSBA'],
                        ],
                        'selected' => 'BSIT',
                        'placeholder' => 'All Programs',
                    ])
                </div>
                <div class="se-mini-field">
                    <span class="app-filter-label">Year Level</span>
                    @include('registrar.components.listbox-select', [
                        'id' => 'seCatalogYearFilter',
                        'name' => 'seCatalogYearFilter',
                        'options' => [
                            ['value' => '', 'label' => 'All Years'],
                            ['value' => '1', 'label' => '1st'],
                            ['value' => '2', 'label' => '2nd'],
                            ['value' => '3', 'label' => '3rd'],
                            ['value' => '4', 'label' => '4th'],
                        ],
                        'selected' => '4',
                        'placeholder' => 'All Years',
                    ])
                </div>
            </div>
            <div class="se-subject-tools">
                <div class="se-subject-tools-search">
                    <span class="app-filter-label">Search Subjects</span>
                    <div class="pf-search-wrap" style="max-width:100%;">
                        <span class="pf-search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="text" id="seCatalogSearch" class="pf-search-input" placeholder="Search Subject Code / Description" oninput="filterCatalogRows()">
                    </div>
                </div>
                <button type="button" id="seBtnCatalogEdit" class="se-tool-btn se-tool-btn-edit" onclick="openSubjectRowModal('edit', 'seAddSubjectsTable')">Edit Selected</button>
                <button type="button" id="seBtnAddCatalogRow" class="se-tool-btn se-tool-btn-edit" onclick="openSubjectRowModal('add', 'seAddSubjectsTable')">+Add Catalog Row</button>
                <button type="button" id="seBtnAddToEnrolled" class="pf-btn-new se-tool-btn" onclick="addSelectedCatalogSubjects()">Add Selected To Enrolled</button>
            </div>
            <div class="se-action-note">Edit Selected requires exactly 1 checked catalog row. You can edit the Schedule field in the popup modal.</div>
            <div class="student-table-wrapper table-responsive">
                <table class="student-table registrar-table se-subject-table" id="seAddSubjectsTable">
                    <colgroup>
                        <col style="width:54px;">
                        <col style="width:270px;">
                        <col style="width:390px;">
                        <col style="width:86px;">
                        <col style="width:320px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject Code/Equivalent Subject</th>
                            <th>Description</th>
                            <th>Units</th>
                            <th>Schedule (Slots|CourseCode-SectionCode-Schedule)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-program="BSIT" data-year="4">
                            <td><input type="checkbox"></td>
                            <td>PATHFIT1</td>
                            <td>BSTMOVEMENT ENHANCEMENT/ SCRIMFUNDAMENTALS OF MARTIALS ARTS</td>
                            <td>2</td>
                            <td>35 | BSIT 4-A | M 01:00PM-03:00PM GYM</td>
                        </tr>
                        <tr data-program="BSIT" data-year="4">
                            <td><input type="checkbox"></td>
                            <td>NSTP101B</td>
                            <td>NSTP-CWTS1</td>
                            <td>2</td>
                            <td>40 | BSIT 4-A | S 08:00AM-11:00AM FIELD</td>
                        </tr>
                        <tr data-program="BSCS" data-year="3">
                            <td><input type="checkbox"></td>
                            <td>CS ELEC 2</td>
                            <td>MACHINE LEARNING FUNDAMENTALS</td>
                            <td>3</td>
                            <td>28 | BSCS 3-B | TH 01:00PM-04:00PM LAB 2</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="se-section se-schedule-section">
            <div class="se-schedule-head">
                <div class="se-subject-title" style="margin-bottom:0;">Schedule:</div>
                <button type="button" class="pf-btn-new se-download-btn" onclick="downloadSchedulePdf()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
            </div>
            <div class="so-weekly" style="display:block; margin-top:0;">
                <div class="so-weekly-scroll">
                    <div class="so-weekly-grid">
                        <div class="so-weekly-col"><div class="so-weekly-day">MONDAY</div><div class="so-weekly-body"><div class="so-weekly-empty"></div></div></div>
                        <div class="so-weekly-col"><div class="so-weekly-day">TUESDAY</div><div class="so-weekly-body"><div class="so-weekly-card"><div class="so-weekly-time">7:00PM-8:30PM</div><div class="so-weekly-code">IAS 102</div><div class="so-weekly-section">INFORMATION ASSURANCE AND SECURITY 2</div><div class="so-weekly-room">[Online Class]</div></div></div></div>
                        <div class="so-weekly-col"><div class="so-weekly-day">WEDNESDAY</div><div class="so-weekly-body"><div class="so-weekly-card"><div class="so-weekly-time">2:00PM-4:00PM</div><div class="so-weekly-code">SPI 101</div><div class="so-weekly-section">SOCIAL AND PROFESSIONAL ISSUES</div><div class="so-weekly-room">[BLDG. 1 - 401]</div></div><div class="so-weekly-card"><div class="so-weekly-time">7:00PM-8:30PM</div><div class="so-weekly-code">SAM 101</div><div class="so-weekly-section">SYSTEM ADMINISTRATION AND MAINTENANCE</div><div class="so-weekly-room">[Online Class]</div></div></div></div>
                        <div class="so-weekly-col"><div class="so-weekly-day">THURSDAY</div><div class="so-weekly-body"><div class="so-weekly-empty"></div></div></div>
                        <div class="so-weekly-col"><div class="so-weekly-day">FRIDAY</div><div class="so-weekly-body"><div class="so-weekly-card"><div class="so-weekly-time">7:00AM-11:30AM</div><div class="so-weekly-code">CAP 102</div><div class="so-weekly-section">CAPSTONE PROJECT AND RESEARCH 2</div><div class="so-weekly-room">[BLDG. 1 - 401]</div></div><div class="so-weekly-card"><div class="so-weekly-time">7:00PM-8:30PM</div><div class="so-weekly-code">SAM 101</div><div class="so-weekly-section">SYSTEM ADMINISTRATION AND MAINTENANCE</div><div class="so-weekly-room">[Online Class]</div></div></div></div>
                        <div class="so-weekly-col"><div class="so-weekly-day">SATURDAY</div><div class="so-weekly-body"><div class="so-weekly-card"><div class="so-weekly-time">5:00PM-7:30PM</div><div class="so-weekly-code">IT ELEC 4</div><div class="so-weekly-section">ELECTIVE 4</div><div class="so-weekly-room">[Online Class]</div></div><div class="so-weekly-card"><div class="so-weekly-time">8:00PM-8:30PM</div><div class="so-weekly-code">SAM 101</div><div class="so-weekly-section">SYSTEM ADMINISTRATION AND MAINTENANCE</div><div class="so-weekly-room">[Online Class]</div></div><div class="so-weekly-card"><div class="so-weekly-time">9:00PM-9:30PM</div><div class="so-weekly-code">IAS 102</div><div class="so-weekly-section">INFORMATION ASSURANCE AND SECURITY 2</div><div class="so-weekly-room">[Online Class]</div></div></div></div>
                        <div class="so-weekly-col"><div class="so-weekly-day">SUNDAY</div><div class="so-weekly-body"><div class="so-weekly-empty"></div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pf-modal-overlay" id="seAddStudentModal" style="display:none;">
        <div class="pf-modal-box" style="max-width:520px;">
            <div class="pf-modal-title">Add Student</div>
            <p class="se-modal-note">Search an applicant to auto-fill details, or leave Student ID blank to generate the next YY-00001 sequence.</p>
            <form action="{{ route('registrar.registrar-menu.student-mgmt.student-enrollment.store') }}" method="POST">
                @csrf
                <div class="se-modal-grid">
                    <div class="se-modal-field">
                        <label class="pf-modal-label">Student ID</label>
                        @include('registrar.components.search-dropdown-input', [
                            'id' => 'seAddStudentId',
                            'name' => 'student_no',
                            'placeholder' => 'Auto-generate if blank',
                            'wrapperClass' => 'se-student-search-wrap',
                            'dropdownId' => 'seAddStudentIdDropdown',
                            'inputAttributes' => [
                            ],
                        ])
                    </div>
                    <div class="se-modal-field">
                        <label class="pf-modal-label">Student Name</label>
                        @include('registrar.components.search-dropdown-input', [
                            'id' => 'seAddStudentName',
                            'name' => 'name',
                            'placeholder' => 'Search applicant name',
                            'wrapperClass' => 'se-student-search-wrap',
                            'dropdownId' => 'seAddStudentNameDropdown',
                            'inputAttributes' => [
                                'required' => true,
                            ],
                        ])
                    </div>
                    <div class="se-modal-field">
                        <label class="pf-modal-label">Program</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'seAddStudentProgram',
                            'name' => 'program',
                            'options' => $studentProgramOptions,
                            'selected' => '',
                            'placeholder' => 'Select Program',
                        ])
                    </div>
                    <div class="se-modal-field">
                        <label class="pf-modal-label">Year Level</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'seAddStudentYear',
                            'name' => 'year_level',
                            'options' => $studentYearOptions,
                            'selected' => '',
                            'placeholder' => 'Select Year Level',
                        ])
                    </div>
                    <div class="se-modal-field">
                        <label class="pf-modal-label">Student Type</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'seAddStudentType',
                            'name' => 'student_type',
                            'options' => ['New', 'Transferee', 'Returnee'],
                            'selected' => 'New',
                            'placeholder' => 'Select Student Type',
                        ])
                    </div>
                    <!-- Hidden requirements based on typical registrar input defaults -->
                    <input type="hidden" name="school_year" value="{{ date('Y') }}-{{ date('Y')+1 }}">
                    <input type="hidden" name="semester" value="First">
                </div>
                <div class="pf-modal-actions" style="margin-top:14px;">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeAddStudentModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <div class="pf-modal-overlay" id="seImportCsvModal" style="display:none;">
        <div class="pf-modal-box" style="max-width:540px;">
            <div class="pf-modal-title">Import Students via CSV</div>
            <div class="se-import-help">
                Supported columns: <strong>student_id, student_name, program, year_level</strong><br>
                You can also upload without header using this order: ID, Name, Program, Year Level.
            </div>
            <div class="se-modal-field" style="margin-top:12px;">
                <button type="button" class="pf-modal-btn-cancel" onclick="downloadEnrollmentCsvTemplate()">Download Template</button>
            </div>
            <div class="se-modal-field" style="margin-top:12px;">
                <label class="pf-modal-label">CSV File</label>
                <input type="file" id="seCsvFile" class="pf-modal-input" accept=".csv,text/csv">
            </div>
            <div class="pf-modal-actions" style="margin-top:14px;">
                <button type="button" class="pf-modal-btn-cancel" onclick="closeImportCsvModal()">Cancel</button>
                <button type="button" class="pf-modal-btn-save" onclick="importCsvFromModal()">Import</button>
            </div>
        </div>
    </div>

    <div class="pf-modal-overlay" id="seEditStudentModal" style="display:none;">
        <div class="pf-modal-box" style="max-width:520px;">
            <div class="pf-modal-title">Edit Student</div>
            <div class="se-modal-grid">
                <div class="se-modal-field">
                    <label class="pf-modal-label">Student ID</label>
                    <input type="text" id="seEditStudentId" class="pf-modal-input">
                </div>
                <div class="se-modal-field">
                    <label class="pf-modal-label">Student Name</label>
                    <input type="text" id="seEditStudentName" class="pf-modal-input">
                </div>
                <div class="se-modal-field">
                    <label class="pf-modal-label">Program</label>
                    @include('registrar.components.listbox-select', [
                        'id' => 'seEditStudentProgram',
                        'name' => 'program',
                        'options' => $studentProgramOptions,
                        'selected' => '',
                        'placeholder' => 'Select Program',
                    ])
                </div>
                <div class="se-modal-field">
                    <label class="pf-modal-label">Year Level</label>
                    @include('registrar.components.listbox-select', [
                        'id' => 'seEditStudentYear',
                        'name' => 'year_level',
                        'options' => $studentYearOptions,
                        'selected' => '',
                        'placeholder' => 'Select Year Level',
                    ])
                </div>
            </div>
            <div class="pf-modal-actions" style="margin-top:14px;">
                <button type="button" class="pf-modal-btn-cancel" onclick="closeEditStudentModal()">Cancel</button>
                <button type="button" class="pf-modal-btn-save" onclick="saveEditedStudent()">Save Changes</button>
            </div>
        </div>
    </div>

    <div class="pf-modal-overlay" id="seDeleteStudentModal" style="display:none;">
        <div class="pf-modal-box" style="max-width:420px; text-align:center;">
            <div class="pf-modal-title" style="color:#c62828;">Delete Student</div>
            <p style="font-size:0.9rem; color:#444; margin:6px 0 2px;">Are you sure you want to delete</p>
            <p style="font-size:0.95rem; font-weight:700; color:#1a1a2e; margin-bottom:16px;" id="seDeleteStudentLabel"></p>
            <div class="pf-modal-actions" style="justify-content:center; margin-top:8px;">
                <button type="button" class="pf-modal-btn-cancel" onclick="closeDeleteStudentModal()">Cancel</button>
                <button type="button" class="pf-modal-btn-save" style="background:#c62828;" onclick="confirmDeleteStudent()">Delete</button>
            </div>
        </div>
    </div>

    <div class="pf-modal-overlay" id="seSubjectRowModal" style="display:none;">
        <div class="pf-modal-box" style="max-width:640px;">
            <div class="pf-modal-title" id="seSubjectRowModalTitle">Edit Subject Row</div>
            <div id="seSubjectRowModalFields" class="se-modal-grid"></div>
            <div class="pf-modal-actions" style="margin-top:14px;">
                <button type="button" class="pf-modal-btn-cancel" onclick="closeSubjectRowModal()">Cancel</button>
                <button type="button" class="pf-modal-btn-save" onclick="saveSubjectRowModal()">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.page-student-enrollment .se-view-toggle {
    display:inline-flex;
    align-items:center;
    gap:2px;
    padding:3px;
    border:1px solid #c8e6c9;
    border-radius:7px;
    background:#f8fafc;
}
.page-student-enrollment .se-view-toggle-btn {
    width:32px;
    height:30px;
    border:0;
    border-radius:5px;
    background:transparent;
    color:#64748b;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
}
.page-student-enrollment .se-view-toggle-btn:hover {
    color:#004d27;
    background:#eef8ef;
}
.page-student-enrollment .se-view-toggle-btn.is-active {
    color:#fff;
    background:#004d27;
}
.page-student-enrollment .se-card-view {
    margin-top:14px;
}
.page-student-enrollment .se-card-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));
    gap:12px;
}
.page-student-enrollment .se-student-mini-card {
    border:1px solid #dfe8e3;
    border-radius:8px;
    background:#fff;
    padding:14px;
    display:grid;
    gap:12px;
    min-height:178px;
}
.page-student-enrollment .se-card-main {
    display:flex;
    flex-direction:column;
    gap:4px;
    min-width:0;
}
.page-student-enrollment .se-card-name {
    border:0;
    background:transparent;
    padding:0;
    text-align:left;
    color:#004d27;
    font-weight:800;
    font-size:0.94rem;
    line-height:1.25;
    cursor:pointer;
}
.page-student-enrollment .se-card-name:hover {
    text-decoration:underline;
}
.page-student-enrollment .se-card-id {
    color:#64748b;
    font-size:0.8rem;
    font-weight:700;
}
.page-student-enrollment .se-card-meta {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px;
}
.page-student-enrollment .se-card-meta div {
    min-width:0;
    border:1px solid #eef2f0;
    border-radius:6px;
    background:#f8fafc;
    padding:8px;
}
.page-student-enrollment .se-card-meta span {
    display:block;
    color:#7b8b84;
    font-size:0.68rem;
    font-weight:800;
    text-transform:uppercase;
    margin-bottom:3px;
}
.page-student-enrollment .se-card-meta strong {
    display:block;
    color:#17211b;
    font-size:0.82rem;
    line-height:1.25;
    overflow-wrap:anywhere;
}
.page-student-enrollment .se-card-actions {
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:6px;
}
.page-student-enrollment .se-card-empty {
    grid-column:1/-1;
    border:1px dashed #cbd5e1;
    border-radius:8px;
    padding:24px;
    text-align:center;
    color:#64748b;
    background:#f8fafc;
}
@media (max-width: 720px) {
    .page-student-enrollment .se-toolbar {
        align-items:stretch;
    }
    .page-student-enrollment .se-view-toggle {
        align-self:flex-start;
    }
    .page-student-enrollment .se-card-grid {
        grid-template-columns:1fr;
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script>
var seEditingStudentId = null;
var seDeletingStudentId = null;
var seSubjectRowState = {
    mode: '',
    tableId: '',
    row: null,
    columns: []
};

var seStudentUpdateUrlTemplate = @json(route('registrar.registrar-menu.student-mgmt.student-enrollment.update', ['student' => '__STUDENT__']));
var seStudentDestroyUrlTemplate = @json(route('registrar.registrar-menu.student-mgmt.student-enrollment.destroy', ['student' => '__STUDENT__']));

function getRegistrarCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? String(meta.getAttribute('content') || '') : '';
}

function buildStudentEndpoint(template, studentRef) {
    return String(template || '').replace('__STUDENT__', encodeURIComponent(String(studentRef || '').trim()));
}

function getEnrollmentViewMode() {
    try {
        return localStorage.getItem('studentEnrollmentViewMode') === 'card' ? 'card' : 'list';
    } catch (err) {
        return 'list';
    }
}

function setEnrollmentViewMode(mode) {
    var selectedMode = mode === 'card' ? 'card' : 'list';
    var tableWrap = document.querySelector('#seListView .student-table-wrapper');
    var cardView = document.getElementById('seCardView');
    var listBtn = document.getElementById('seViewListBtn');
    var cardBtn = document.getElementById('seViewCardBtn');

    if (tableWrap) {
        tableWrap.style.display = selectedMode === 'card' ? 'none' : '';
    }
    if (cardView) {
        cardView.style.display = selectedMode === 'card' ? 'block' : 'none';
    }
    if (listBtn) {
        listBtn.classList.toggle('is-active', selectedMode === 'list');
        listBtn.setAttribute('aria-pressed', selectedMode === 'list' ? 'true' : 'false');
    }
    if (cardBtn) {
        cardBtn.classList.toggle('is-active', selectedMode === 'card');
        cardBtn.setAttribute('aria-pressed', selectedMode === 'card' ? 'true' : 'false');
    }

    try {
        localStorage.setItem('studentEnrollmentViewMode', selectedMode);
    } catch (err) {}
}

function refreshListboxSelect(selectEl) {
    if (!selectEl) return;
    if (window.registrarListboxSelect && typeof window.registrarListboxSelect.refresh === 'function') {
        window.registrarListboxSelect.refresh(selectEl);
    }
}

function setListboxValue(selectId, value) {
    var selectEl = document.getElementById(selectId);
    if (!selectEl) return;
    selectEl.value = value == null ? '' : String(value);
    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
    refreshListboxSelect(selectEl);
}

var seApplicantRows = @json($applicantRows);
var seApplicantSearchState = {
    selectedApplicantId: ''
};

function normalizeSearchText(value) {
    return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
}

function getApplicantSearchRefs(sourceType) {
    return sourceType === 'name'
        ? {
            input: document.getElementById('seAddStudentName'),
            dropdown: document.getElementById('seAddStudentNameDropdown')
        }
        : {
            input: document.getElementById('seAddStudentId'),
            dropdown: document.getElementById('seAddStudentIdDropdown')
        };
}

function closeApplicantSearchDropdown(dropdown) {
    if (!dropdown) return;
    dropdown.classList.remove('is-open');
    dropdown.innerHTML = '';
}

function closeApplicantSearchDropdowns() {
    closeApplicantSearchDropdown(document.getElementById('seAddStudentIdDropdown'));
    closeApplicantSearchDropdown(document.getElementById('seAddStudentNameDropdown'));
}

function getApplicantCandidates(term) {
    var query = normalizeSearchText(term);

    if (!query) {
        return seApplicantRows.slice(0, 12);
    }

    return seApplicantRows.filter(function (candidate) {
        var applicantId = normalizeSearchText(candidate.applicant_id);
        var applicantName = normalizeSearchText(candidate.name);
        var applicantLabel = normalizeSearchText(candidate.label);

        return applicantId.indexOf(query) !== -1 || applicantName.indexOf(query) !== -1 || applicantLabel.indexOf(query) !== -1;
    }).slice(0, 12);
}

function renderApplicantSearchDropdown(dropdown, candidates) {
    if (!dropdown) return;

    if (!candidates.length) {
        dropdown.innerHTML = '<div class="smrg-search-empty">No matching applicant found.</div>';
        dropdown.classList.add('is-open');
        return;
    }

    var html = '';
    candidates.forEach(function (candidate) {
        html += '<button type="button" class="smrg-search-option" data-se-applicant-id="' + escapeHtml(candidate.applicant_id) + '" data-se-applicant-name="' + escapeHtml(candidate.name) + '">' +
            '<strong>' + escapeHtml(candidate.applicant_id) + '</strong> - ' + escapeHtml(candidate.name) +
            '</button>';
    });

    dropdown.innerHTML = html;
    dropdown.classList.add('is-open');
}

function openApplicantSearchDropdown(sourceType) {
    var refs = getApplicantSearchRefs(sourceType);
    if (!refs.input || !refs.dropdown) return;

    renderApplicantSearchDropdown(refs.dropdown, getApplicantCandidates(refs.input.value));
}

function selectApplicantCandidate(candidate) {
    if (!candidate) return;

    var studentIdInput = document.getElementById('seAddStudentId');
    var studentNameInput = document.getElementById('seAddStudentName');

    if (studentIdInput) {
        studentIdInput.value = candidate.applicant_id || '';
    }
    if (studentNameInput) {
        studentNameInput.value = candidate.name || '';
    }

    seApplicantSearchState.selectedApplicantId = candidate.applicant_id || '';
    closeApplicantSearchDropdowns();
}

function bindApplicantSearchInput(input, dropdown, sourceType) {
    if (!input || !dropdown) return;

    input.addEventListener('focus', function () {
        openApplicantSearchDropdown(sourceType);
    });

    input.addEventListener('input', function () {
        seApplicantSearchState.selectedApplicantId = '';
        openApplicantSearchDropdown(sourceType);
    });

    input.addEventListener('blur', function () {
        setTimeout(function () {
            if (!dropdown.matches(':hover')) {
                closeApplicantSearchDropdown(dropdown);
            }
        }, 120);
    });

    dropdown.addEventListener('mousedown', function (event) {
        var option = event.target.closest('.smrg-search-option');
        if (!option) return;

        event.preventDefault();
        selectApplicantCandidate({
            applicant_id: option.getAttribute('data-se-applicant-id') || '',
            name: option.getAttribute('data-se-applicant-name') || ''
        });
    });
}

function sendStudentJsonRequest(url, method, payload) {
    var headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    var csrfToken = getRegistrarCsrfToken();
    if (csrfToken) {
        headers['X-CSRF-TOKEN'] = csrfToken;
    }

    var options = {
        method: method,
        credentials: 'same-origin',
        headers: headers
    };

    if (payload !== undefined && payload !== null) {
        headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(payload);
    }

    return fetch(url, options).then(function (response) {
        return response.text().then(function (text) {
            var data = null;
            if (text) {
                try {
                    data = JSON.parse(text);
                } catch (error) {
                    data = null;
                }
            }

            return {
                response: response,
                data: data
            };
        });
    });
}

function getResponseMessage(payload, fallbackMessage) {
    if (payload && payload.message) {
        return String(payload.message);
    }

    if (payload && payload.errors) {
        var errorKeys = Object.keys(payload.errors);
        if (errorKeys.length) {
            var firstError = payload.errors[errorKeys[0]];
            if (firstError && firstError.length) {
                return String(firstError[0]);
            }
        }
    }

    return fallbackMessage;
}

function isPersistedStudentRow(row) {
    return !!(row && /^\d+$/.test(String(row.getAttribute('data-student-row-id') || '').trim()));
}

function replaceStudentRow(row, studentData, fallbackRowRef) {
    if (!row || !studentData || !row.parentNode) return;

    var studentNo = String(studentData.student_no || '').trim();
    var studentName = String(studentData.name || '').trim();
    var programLabel = String(studentData.program_label || studentData.program || 'N/A').trim() || 'N/A';
    var programValue = String(studentData.program_value || studentData.program_code || '').trim();
    var yearLevel = String(studentData.year_level_label || studentData.year_level || 'N/A').trim() || 'N/A';
    var rowRef = String(studentData.id || fallbackRowRef || row.getAttribute('data-student-row-id') || '').trim();
    var replacementRow = createEnrollmentRow(studentNo, studentName, programLabel, yearLevel, rowRef, programValue);

    row.parentNode.replaceChild(replacementRow, row);
    replaceStudentCard(studentNo, studentName, programLabel, yearLevel, rowRef);
}

function openEnrollmentDetail(studentId, studentName) {
    var row = getStudentRowById(studentId);
    var metaId = document.getElementById('seMetaId');
    var metaName = document.getElementById('seMetaName');
    var metaProgram = document.getElementById('seMetaProgram');
    var metaYearLevel = document.getElementById('seMetaYearLevel');
    var listView = document.getElementById('seListView');
    var detailView = document.getElementById('seDetailView');

    if (!metaId || !metaName || !metaProgram || !metaYearLevel || !listView || !detailView) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Enrollment details are not available on this page.', 'error');
        }
        return;
    }

    metaId.textContent = studentId || 'N/A';
    metaName.textContent = studentName || 'N/A';
    metaProgram.textContent = 'N/A';
    metaYearLevel.textContent = 'N/A';

    if (row) {
        metaId.textContent = String(row.getAttribute('data-student-id') || studentId || 'N/A');
        metaName.textContent = row.children[2] ? String(row.children[2].innerText || '').trim().toUpperCase() : (studentName || 'N/A');
        metaProgram.textContent = row.children[3] ? String(row.children[3].innerText || '').trim() : 'N/A';
        metaYearLevel.textContent = row.children[4] ? String(row.children[4].innerText || '').trim() : 'N/A';
    }

    listView.style.display = 'none';
    detailView.style.display = 'block';
    updateCurrentUnitsTotal();
}

function getStudentRowById(studentId) {
    var lookup = String(studentId || '').trim();
    if (!lookup) return null;

    var rows = document.querySelectorAll('#seTableBody tr');
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        if (row.classList.contains('se-total-row')) continue;

        if (String(row.getAttribute('data-student-row-id') || '').trim() === lookup || String(row.getAttribute('data-student-id') || '').trim() === lookup) {
            return row;
        }
    }

    return null;
}

function openEditStudentModal(studentId) {
    var row = getStudentRowById(studentId);
    if (!row) return;

    seEditingStudentId = studentId;
    document.getElementById('seEditStudentId').value = String(row.getAttribute('data-student-id') || '').trim();
    document.getElementById('seEditStudentName').value = row.children[2] ? String(row.children[2].innerText || '').trim() : '';
    setListboxValue('seEditStudentProgram', row.getAttribute('data-student-program-value') || '');
    setListboxValue('seEditStudentYear', row.getAttribute('data-student-year-level-value') || (row.children[4] ? String(row.children[4].innerText || '').trim() : ''));
    document.getElementById('seEditStudentModal').style.display = 'flex';
}

function closeEditStudentModal() {
    seEditingStudentId = null;
    document.getElementById('seEditStudentModal').style.display = 'none';
}

function saveEditedStudent() {
    if (!seEditingStudentId) return;
    var row = getStudentRowById(seEditingStudentId);
    if (!row) return;

    var studentId = (document.getElementById('seEditStudentId').value || '').trim();
    var studentName = (document.getElementById('seEditStudentName').value || '').trim();
    var program = (document.getElementById('seEditStudentProgram').value || '').trim() || 'N/A';
    var yearLevel = (document.getElementById('seEditStudentYear').value || '').trim() || 'N/A';
    var studentRowId = String(row.getAttribute('data-student-row-id') || '').trim();
    var previousStudentNo = String(row.getAttribute('data-student-id') || '').trim();

    if (!studentId || !studentName) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Student ID and Student Name are required.', 'warning');
        }
        return;
    }

    var payload = {
        student_no: studentId,
        name: studentName,
        program: program === 'N/A' ? null : program,
        year_level: yearLevel === 'N/A' ? null : yearLevel
    };

    var applyUpdate = function (studentData, successMessage) {
        replaceStudentRow(row, studentData, studentId);
        closeEditStudentModal();

        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast(successMessage || 'Student updated successfully.', 'success');
        }

        if (document.getElementById('seDetailView').style.display === 'block' && document.getElementById('seMetaId').textContent === previousStudentNo) {
            openEnrollmentDetail(studentData.student_no || studentId, studentData.name || studentName);
        }
    };

    if (isPersistedStudentRow(row)) {
        sendStudentJsonRequest(buildStudentEndpoint(seStudentUpdateUrlTemplate, studentRowId), 'PUT', payload).then(function (result) {
            if (!result.response.ok) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(getResponseMessage(result.data, 'Unable to update student profile.'), 'error');
                }
                return;
            }

            applyUpdate(result.data && result.data.student ? result.data.student : {
                id: studentRowId,
                student_no: studentId,
                name: studentName,
                program_label: program,
                program_value: program === 'N/A' ? '' : program,
                year_level: yearLevel
            }, result.data && result.data.message ? result.data.message : 'Student updated successfully.');
        }).catch(function () {
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Unable to update student profile.', 'error');
            }
        });
        return;
    }

    applyUpdate({
        id: '',
        student_no: studentId,
        name: studentName,
        program_label: program,
        program_value: program === 'N/A' ? '' : program,
        year_level: yearLevel
    }, 'Student updated successfully.');
}

function openDeleteStudentModal(studentId) {
    var row = getStudentRowById(studentId);
    if (!row) return;
    seDeletingStudentId = studentId;
    document.getElementById('seDeleteStudentLabel').textContent = row.children[2].innerText.trim();
    document.getElementById('seDeleteStudentModal').style.display = 'flex';
}

function closeDeleteStudentModal() {
    seDeletingStudentId = null;
    document.getElementById('seDeleteStudentModal').style.display = 'none';
}

function confirmDeleteStudent() {
    if (!seDeletingStudentId) return;
    var row = getStudentRowById(seDeletingStudentId);
    if (!row) return;

    var studentRowId = String(row.getAttribute('data-student-row-id') || '').trim();
    var previousStudentNo = String(row.getAttribute('data-student-id') || '').trim();

    var finalizeDelete = function (successMessage) {
        var card = getStudentCardById(studentRowId) || getStudentCardById(previousStudentNo);
        if (card && card.parentNode) {
            card.parentNode.removeChild(card);
        }

        if (row.parentNode) {
            row.parentNode.removeChild(row);
        }

        closeDeleteStudentModal();
        renumberEnrollmentRows();
        adjustEnrollmentTotal(-1);

        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast(successMessage || 'Student deleted successfully.', 'success');
        }

        if (document.getElementById('seDetailView').style.display === 'block' && document.getElementById('seMetaId').textContent === previousStudentNo) {
            document.getElementById('seDetailView').style.display = 'none';
            document.getElementById('seListView').style.display = 'block';
        }
    };

    if (isPersistedStudentRow(row)) {
        sendStudentJsonRequest(buildStudentEndpoint(seStudentDestroyUrlTemplate, studentRowId), 'DELETE').then(function (result) {
            if (!result.response.ok) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(getResponseMessage(result.data, 'Unable to delete student profile.'), 'error');
                }
                return;
            }

            finalizeDelete(result.data && result.data.message ? result.data.message : 'Student deleted successfully.');
        }).catch(function () {
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Unable to delete student profile.', 'error');
            }
        });
        return;
    }

    finalizeDelete('Student deleted successfully.');
}

function buildRowActionCell(studentId) {
    var tdAction = document.createElement('td');
    var actionRef = String(studentId || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    tdAction.innerHTML = '<div class="se-row-actions"><button type="button" class="doclist-action-btn" onclick="openEnrollmentDetail(\'' + actionRef + '\', \'\')" title="View Enrollment" style="color:#004d27;background:#d1fae5;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></button><button type="button" class="doclist-action-btn doclist-edit-btn" onclick="openEditStudentModal(\'' + actionRef + '\')" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></button><button type="button" class="doclist-action-btn doclist-delete-btn" onclick="openDeleteStudentModal(\'' + actionRef + '\')" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button></div>';
    return tdAction;
}

function getStudentCardById(studentId) {
    var lookup = String(studentId || '').trim();
    if (!lookup) return null;

    var cards = document.querySelectorAll('#seCardGrid .se-student-mini-card');
    for (var i = 0; i < cards.length; i++) {
        var card = cards[i];
        if (String(card.getAttribute('data-student-card-row-id') || '').trim() === lookup || String(card.getAttribute('data-student-card-id') || '').trim() === lookup) {
            return card;
        }
    }

    return null;
}

function createEnrollmentCard(studentId, studentName, program, yearLevel, studentRowId) {
    var card = document.createElement('article');
    var actionRef = String(studentRowId || studentId || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    var detailRef = String(studentId || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    var nameRef = String(studentName || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");

    card.className = 'se-student-mini-card';
    card.setAttribute('data-student-card-id', studentId || '');
    if (studentRowId) {
        card.setAttribute('data-student-card-row-id', studentRowId);
    }

    card.innerHTML =
        '<div class="se-card-main">' +
            '<button type="button" class="se-card-name" onclick="openEnrollmentDetail(\'' + detailRef + '\', \'' + nameRef + '\')">' + escapeHtml(studentName || 'N/A') + '</button>' +
            '<div class="se-card-id">' + escapeHtml(studentId || 'N/A') + '</div>' +
        '</div>' +
        '<div class="se-card-meta">' +
            '<div><span>Program</span><strong>' + escapeHtml(program || 'N/A') + '</strong></div>' +
            '<div><span>Year Level</span><strong>' + escapeHtml(yearLevel || 'N/A') + '</strong></div>' +
        '</div>' +
        '<div class="se-card-actions">' +
            '<button type="button" class="doclist-action-btn" onclick="openEnrollmentDetail(\'' + detailRef + '\', \'' + nameRef + '\')" title="View Enrollment" style="color:#004d27;background:#d1fae5;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></button>' +
            '<button type="button" class="doclist-action-btn doclist-edit-btn" onclick="openEditStudentModal(\'' + actionRef + '\')" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></button>' +
            '<button type="button" class="doclist-action-btn doclist-delete-btn" onclick="openDeleteStudentModal(\'' + actionRef + '\')" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>' +
        '</div>';

    return card;
}

function replaceStudentCard(studentId, studentName, program, yearLevel, studentRowId) {
    var grid = document.getElementById('seCardGrid');
    if (!grid) return;

    var empty = grid.querySelector('.se-card-empty');
    if (empty && empty.parentNode) {
        empty.parentNode.removeChild(empty);
    }

    var existing = getStudentCardById(studentRowId) || getStudentCardById(studentId);
    var card = createEnrollmentCard(studentId, studentName, program, yearLevel, studentRowId);

    if (existing && existing.parentNode) {
        existing.parentNode.replaceChild(card, existing);
    } else {
        grid.appendChild(card);
    }
}

function withdrawEnrollment() {
    document.getElementById('seDetailView').style.display = 'none';
    document.getElementById('seListView').style.display = 'block';
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Enrollment withdrawn.', 'warning');
    }
}

function openAddStudentModal() {
    var modal = document.getElementById('seAddStudentModal');
    var studentIdInput = document.getElementById('seAddStudentId');
    var studentNameInput = document.getElementById('seAddStudentName');

    if (studentIdInput) studentIdInput.value = '';
    if (studentNameInput) studentNameInput.value = '';
    setListboxValue('seAddStudentProgram', '');
    setListboxValue('seAddStudentYear', '');
    setListboxValue('seAddStudentType', 'New');
    seApplicantSearchState.selectedApplicantId = '';
    closeApplicantSearchDropdowns();

    if (modal) {
        modal.style.display = 'flex';
    }

    setTimeout(function () {
        if (studentIdInput) {
            studentIdInput.focus();
        }
    }, 0);
}

function closeAddStudentModal() {
    seApplicantSearchState.selectedApplicantId = '';
    closeApplicantSearchDropdowns();
    document.getElementById('seAddStudentModal').style.display = 'none';
}

function saveAddedStudent() {
    var studentId = (document.getElementById('seAddStudentId').value || '').trim();
    var studentName = (document.getElementById('seAddStudentName').value || '').trim();
    var program = (document.getElementById('seAddStudentProgram').value || '').trim();
    var yearLevel = (document.getElementById('seAddStudentYear').value || '').trim();

    if (!studentId || !studentName) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Student ID and Student Name are required.', 'warning');
        }
        return;
    }

    if (!program) program = 'N/A';
    if (!yearLevel) yearLevel = 'N/A';

    var tbody = document.getElementById('seTableBody');
    var totalRow = tbody.querySelector('.se-total-row');
    var row = createEnrollmentRow(studentId, studentName, program, yearLevel);
    tbody.insertBefore(row, totalRow);
    replaceStudentCard(studentId, studentName, program, yearLevel);
    renumberEnrollmentRows();
    adjustEnrollmentTotal(1);

    document.getElementById('seAddStudentId').value = '';
    document.getElementById('seAddStudentName').value = '';
    document.getElementById('seAddStudentProgram').value = '';
    document.getElementById('seAddStudentYear').value = '';
    closeAddStudentModal();

    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Student added successfully.', 'success');
    }
}

function openImportCsvModal() {
    document.getElementById('seImportCsvModal').style.display = 'flex';
}

function downloadEnrollmentCsvTemplate() {
    var csvContent = 'student_id,student_name,program,year_level\n'
        + '2023-00001,Juan Dela Cruz,BSIT,1st Year\n';
    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'student_enrollment_import_template.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function closeImportCsvModal() {
    document.getElementById('seImportCsvModal').style.display = 'none';
}

function importCsvFromModal() {
    var csvInput = document.getElementById('seCsvFile');
    var file = csvInput && csvInput.files && csvInput.files[0];
    if (!file) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Please choose a CSV file first.', 'warning');
        }
        return;
    }

    var reader = new FileReader();
    reader.onload = function (e) {
        var csvText = String((e && e.target && e.target.result) || '');
        var imported = importEnrollmentCsv(csvText);
        if (typeof showRegistrarToast === 'function') {
            if (imported > 0) {
                showRegistrarToast(imported + ' student(s) imported from CSV.', 'success');
                closeImportCsvModal();
            } else {
                showRegistrarToast('No valid student rows found in CSV.', 'warning');
            }
        }
    };
    reader.onerror = function () {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Unable to read CSV file.', 'error');
        }
    };
    reader.readAsText(file);
}

function parseCsvLine(line) {
    var out = [];
    var cur = '';
    var inQuotes = false;
    for (var i = 0; i < line.length; i++) {
        var ch = line[i];
        if (ch === '"') {
            if (inQuotes && line[i + 1] === '"') {
                cur += '"';
                i++;
            } else {
                inQuotes = !inQuotes;
            }
        } else if (ch === ',' && !inQuotes) {
            out.push(cur.trim());
            cur = '';
        } else {
            cur += ch;
        }
    }
    out.push(cur.trim());
    return out;
}

function normalizeHeaderKey(key) {
    return String(key || '').toLowerCase().replace(/[^a-z0-9]/g, '');
}

function createEnrollmentRow(studentId, studentName, program, yearLevel, studentRowId, programValue) {
    var tr = document.createElement('tr');
    tr.setAttribute('data-student-id', studentId);
    if (studentRowId) {
        tr.setAttribute('data-student-row-id', studentRowId);
    }
    tr.setAttribute('data-student-program-value', programValue || '');
    tr.setAttribute('data-student-year-level-value', yearLevel || '');

    var tdNo = document.createElement('td');
    tdNo.textContent = '0';
    tr.appendChild(tdNo);

    var tdId = document.createElement('td');
    tdId.textContent = studentId;
    tr.appendChild(tdId);

    var tdName = document.createElement('td');
    var nameLink = document.createElement('a');
    nameLink.href = '#';
    nameLink.className = 'se-name-link';
    nameLink.textContent = studentName;
    nameLink.addEventListener('click', function (evt) {
        evt.preventDefault();
        openEnrollmentDetail(studentId, studentName.toUpperCase());
    });
    tdName.appendChild(nameLink);
    tr.appendChild(tdName);

    var tdProgram = document.createElement('td');
    tdProgram.textContent = program;
    tr.appendChild(tdProgram);

    var tdYear = document.createElement('td');
    tdYear.textContent = yearLevel;
    tr.appendChild(tdYear);

    tr.appendChild(buildRowActionCell(studentRowId || studentId));

    return tr;
}

function importEnrollmentCsv(csvText) {
    var tbody = document.getElementById('seTableBody');
    var totalRow = tbody ? tbody.querySelector('.se-total-row') : null;
    if (!tbody || !totalRow) return 0;

    var lines = String(csvText || '').split(/\r?\n/).filter(function (line) {
        return line.trim() !== '';
    });
    if (!lines.length) return 0;

    var firstCols = parseCsvLine(lines[0]);
    var headers = firstCols.map(normalizeHeaderKey);
    var hasHeader = headers.some(function (h) {
        return ['studentid', 'studentname', 'program', 'yearlevel', 'course', 'name', 'id'].indexOf(h) !== -1;
    });

    var startIdx = hasHeader ? 1 : 0;
    var imported = 0;

    for (var i = startIdx; i < lines.length; i++) {
        var cols = parseCsvLine(lines[i]);
        if (!cols.length) continue;

        var studentId = '';
        var studentName = '';
        var program = '';
        var yearLevel = '';

        if (hasHeader) {
            var rowMap = {};
            headers.forEach(function (h, idx) {
                rowMap[h] = cols[idx] || '';
            });

            studentId = rowMap.studentid || rowMap.studentnumber || rowMap.studentno || rowMap.id || '';
            studentName = rowMap.studentname || rowMap.name || '';
            program = rowMap.program || rowMap.course || '';
            yearLevel = rowMap.yearlevel || rowMap.year || rowMap.level || '';
        } else {
            studentId = cols[0] || '';
            studentName = cols[1] || '';
            program = cols[2] || '';
            yearLevel = cols[3] || '';
        }

        if (!studentId || !studentName) continue;
        if (!program) program = 'N/A';
        if (!yearLevel) yearLevel = 'N/A';

        var newRow = createEnrollmentRow(studentId, studentName, program, yearLevel);
        tbody.insertBefore(newRow, totalRow);
        replaceStudentCard(studentId, studentName, program, yearLevel);
        imported++;
    }

    if (imported > 0) {
        renumberEnrollmentRows();
        adjustEnrollmentTotal(imported);
    }

    return imported;
}

function renumberEnrollmentRows() {
    var tbody = document.getElementById('seTableBody');
    if (!tbody) return;
    var rows = Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
        return !row.classList.contains('se-total-row');
    });
    rows.forEach(function (row, idx) {
        row.children[0].textContent = idx + 1;
    });
}

function updateEnrollmentTotal() {
    var table = document.getElementById('seTable');
    var totalEl = document.getElementById('seTotalCount');
    if (!table || !totalEl) return;
    var total = parseInt(table.getAttribute('data-total-students') || totalEl.textContent || '0', 10);
    if (isNaN(total)) {
        total = 0;
    }
    totalEl.textContent = String(total);
}

function saveDetailSelection() {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Enrollment changes saved successfully.', 'success');
    }
}

function resetDetailSelection() {
    var checks = document.querySelectorAll('.se-subject-table input[type="checkbox"]');
    checks.forEach(function (cb) { cb.checked = false; });
    updateSubjectActionStates();
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Selection has been reset.', 'warning');
    }
}

function getSubjectTable(tableId) {
    return document.getElementById(tableId);
}

function getSelectedSubjectRow(table) {
    if (!table) return null;
    var checked = table.querySelector('tbody input[type="checkbox"]:checked');
    return checked ? checked.closest('tr') : null;
}

function normalizeSubjectModalLabel(label) {
    var text = String(label || '').trim();
    var lower = text.toLowerCase();
    if (lower.indexOf('subject code') !== -1) return 'Subject Code';
    if (lower.indexOf('schedule') !== -1) return 'Schedule';
    if (lower.indexOf('description') !== -1) return 'Description';
    if (lower.indexOf('adjustment') !== -1) return 'Adjustment';
    if (lower.indexOf('units') !== -1) return 'Units';
    if (lower.indexOf('enrolled by') !== -1) return 'Enrolled by';
    return text;
}

function openSubjectRowModal(mode, tableId) {
    var table = getSubjectTable(tableId);
    if (!table) return;

    var selectedRows = mode === 'edit' ? getSelectedRows(tableId) : [];
    var row = mode === 'edit' ? (selectedRows[0] || null) : null;
    if (mode === 'edit' && selectedRows.length !== 1) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast(selectedRows.length > 1 ? 'Please select only one row to edit.' : 'Select one row first, then click Edit Selected.', 'warning');
        }
        return;
    }

    var headers = Array.from(table.querySelectorAll('thead th')).map(function (th) {
        return (th.innerText || '').trim();
    });
    var columns = [];

    headers.forEach(function (header, index) {
        var normalized = header.toLowerCase();
        if (index === 0 || normalized === '#') return;

        var type = normalized.indexOf('schedule') !== -1 ? 'schedule' : 'text';
        var value = '';

        if (mode === 'edit' && row) {
            var cell = row.children[index];
            if (cell) {
                value = type === 'schedule' ? getScheduleTextFromCell(cell) : (cell.innerText || '').trim();
            }
        } else {
            if (normalized.indexOf('subject code') !== -1) value = 'NEW 101';
            else if (normalized.indexOf('description') !== -1) value = 'NEW SUBJECT';
            else if (normalized.indexOf('unit') !== -1) value = '3';
            else if (normalized.indexOf('enrolled by') !== -1) value = 'Admin 1';
            else if (normalized.indexOf('adjustment') !== -1) value = '';
            else if (type === 'schedule') value = 'Set schedule';
            else value = '-';
        }

        columns.push({
            index: index,
            label: header,
            type: type,
            value: value
        });
    });

    seSubjectRowState.mode = mode;
    seSubjectRowState.tableId = tableId;
    seSubjectRowState.row = row;
    seSubjectRowState.columns = columns;

    var modalTitle = document.getElementById('seSubjectRowModalTitle');
    modalTitle.textContent = mode === 'edit' ? 'Edit Subject Row' : 'Add Subject Row';

    var fieldsWrap = document.getElementById('seSubjectRowModalFields');
    fieldsWrap.innerHTML = '';

    columns.forEach(function (column, idx) {
        var field = document.createElement('div');
        field.className = 'se-modal-field';

        var label = document.createElement('label');
        label.className = 'pf-modal-label';
        label.textContent = normalizeSubjectModalLabel(column.label);
        field.appendChild(label);

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'pf-modal-input';
        input.id = 'seSubjectField' + idx;
        input.value = column.value;
        if (column.type === 'schedule') {
            input.placeholder = 'e.g. 35 | BSIT 4-A | M 01:00PM-03:00PM GYM';
        }
        field.appendChild(input);

        fieldsWrap.appendChild(field);
    });

    document.getElementById('seSubjectRowModal').style.display = 'flex';
}

function closeSubjectRowModal() {
    document.getElementById('seSubjectRowModal').style.display = 'none';
    seSubjectRowState.mode = '';
    seSubjectRowState.tableId = '';
    seSubjectRowState.row = null;
    seSubjectRowState.columns = [];
}

function splitScheduleEntries(rawValue) {
    var clean = String(rawValue || '').replace(/\s+/g, ' ').trim();
    if (!clean || clean === '-') return [];
    return clean.split(/\s*\/\s*/).map(function (entry) {
        return entry.trim();
    }).filter(function (entry) {
        return entry !== '';
    });
}

function parseEnrolledScheduleEntry(entry) {
    var clean = String(entry || '').replace(/\s+/g, ' ').trim();
    var section = '';
    var schedule = clean;

    if (clean.indexOf('|') !== -1) {
        var pipeParts = clean.split('|').map(function (part) { return part.trim(); });
        if (pipeParts.length >= 3) {
            section = pipeParts[1] || '';
            schedule = pipeParts.slice(2).join(' | ').trim();
        }
    } else {
        var dashParts = clean.split(/\s-\s/);
        if (dashParts.length >= 3) {
            section = (dashParts[1] || '').trim();
            schedule = dashParts.slice(2).join(' - ').trim();
        }
    }

    return {
        section: section,
        schedule: schedule || clean
    };
}

function formatScheduleDisplayText(value) {
    var text = String(value || '').replace(/\s+/g, ' ').trim();
    if (!text) return text;

    // Format day token as "DAY - schedule" for cleaner readability.
    if (/^[A-Z]{1,3}\s*-\s*/.test(text)) {
        return text;
    }

    var m = text.match(/^([A-Z]{1,3})\s+(.+)$/);
    if (m) {
        return m[1] + ' - ' + m[2];
    }

    return text;
}

function updateEnrolledSectionBanner() {
    var sectionLabel = document.getElementById('seCurrentSectionLabel');
    if (!sectionLabel) return;

    var rows = Array.from(document.querySelectorAll('#seChangeFromTable tbody tr')).filter(function (row) {
        return !row.classList.contains('se-total-units-row') && !row.classList.contains('se-section-info-row');
    });

    var sectionText = 'N/A';

    for (var i = 0; i < rows.length; i++) {
        var scheduleCell = rows[i].children[5];
        var raw = getScheduleTextFromCell(scheduleCell);
        var entries = splitScheduleEntries(raw);
        if (!entries.length) continue;

        var parsed = parseEnrolledScheduleEntry(entries[0]);
        if (parsed.section) {
            sectionText = parsed.section;
            break;
        }
    }

    sectionLabel.textContent = sectionText;
}

function renderEnrolledScheduleCell(cell, rawValue) {
    if (!cell) return;

    var clean = String(rawValue || '').replace(/\s+/g, ' ').trim();
    var entries = splitScheduleEntries(clean);
    cell.classList.add('se-schedule-cell');
    cell.setAttribute('data-raw-schedule', clean || '-');
    cell.innerHTML = '';

    if (!entries.length) {
        cell.textContent = '-';
        return;
    }

    entries.forEach(function (entry) {
        var parsed = parseEnrolledScheduleEntry(entry);
        var line = document.createElement('div');
        line.className = 'se-schedule-line';
        line.textContent = formatScheduleDisplayText(parsed.schedule);
        cell.appendChild(line);
    });

    updateEnrolledSectionBanner();
}

function renderCatalogScheduleCell(cell, rawValue) {
    if (!cell) return;

    var clean = String(rawValue || '').replace(/\s+/g, ' ').trim();
    var entries = splitScheduleEntries(clean);
    cell.classList.add('se-catalog-schedule-cell');
    cell.setAttribute('data-raw-schedule', clean || '-');
    cell.innerHTML = '';

    if (!entries.length) {
        cell.textContent = '-';
        return;
    }

    entries.forEach(function (entry) {
        var line = document.createElement('div');
        line.className = 'se-catalog-schedule-line';
        line.textContent = entry;
        cell.appendChild(line);
    });
}

function setSubjectCellValue(row, column, value) {
    var cell = row.children[column.index];
    if (!cell) return;

    var table = row.closest('table');
    var tableId = table ? table.id : '';

    if (column.type === 'schedule' && tableId === 'seChangeFromTable') {
        renderEnrolledScheduleCell(cell, value);
    } else if (column.type === 'schedule') {
        renderCatalogScheduleCell(cell, value);
    } else {
        cell.textContent = value;
    }
}

function getScheduleTextFromCell(cell) {
    if (!cell) return '';
    var raw = cell.getAttribute('data-raw-schedule');
    if (raw) return raw;
    var select = cell.querySelector('select');
    if (select && select.options.length) {
        return (select.options[select.selectedIndex] && select.options[select.selectedIndex].text) || '';
    }
    return (cell.innerText || '').replace(/\s*\n+\s*/g, ' / ').trim();
}

function createCurrentSubjectRow(data) {
    var row = document.createElement('tr');
    row.innerHTML =
        '<td><input type="checkbox"></td>' +
        '<td>' + (data.code || '-') + '</td>' +
        '<td>' + (data.description || '-') + '</td>' +
        '<td></td>' +
        '<td>' + (data.units || '0') + '</td>' +
        '<td class="se-schedule-cell"></td>' +
        '<td>Admin 1</td>';
    renderEnrolledScheduleCell(row.children[5], data.schedule || '-');
    return row;
}

function getSelectedRows(tableId) {
    var table = getSubjectTable(tableId);
    if (!table) return [];
    return Array.from(table.querySelectorAll('tbody tr')).filter(function (row) {
        if (row.classList.contains('se-total-units-row') || row.classList.contains('se-section-info-row')) return false;
        var cb = row.querySelector('input[type="checkbox"]');
        return cb && cb.checked;
    });
}

function setButtonDisabled(buttonId, disabled) {
    var button = document.getElementById(buttonId);
    if (!button) return;
    button.disabled = !!disabled;
    button.classList.toggle('se-tool-btn-disabled', !!disabled);
}

function updateSubjectActionStates() {
    var currentSelectedCount = getSelectedRows('seChangeFromTable').length;
    var catalogSelectedCount = getSelectedRows('seAddSubjectsTable').length;

    setButtonDisabled('seBtnCurrentEdit', currentSelectedCount !== 1);
    setButtonDisabled('seBtnReplaceCatalog', !(currentSelectedCount === 1 && catalogSelectedCount === 1));
    setButtonDisabled('seBtnDropCurrent', currentSelectedCount < 1);
    setButtonDisabled('seBtnCatalogEdit', catalogSelectedCount !== 1);
    setButtonDisabled('seBtnAddToEnrolled', catalogSelectedCount < 1);
}

function addSelectedCatalogSubjects() {
    var selectedCatalogRows = getSelectedRows('seAddSubjectsTable');
    if (!selectedCatalogRows.length) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Select catalog row(s) to add.', 'warning');
        }
        return;
    }

    var currentTable = getSubjectTable('seChangeFromTable');
    var currentBody = currentTable ? currentTable.querySelector('tbody') : null;
    var totalUnitsRow = currentBody ? currentBody.querySelector('.se-total-units-row') : null;
    if (!currentBody || !totalUnitsRow) return;

    selectedCatalogRows.forEach(function (catalogRow) {
        var cells = catalogRow.children;
        var data = {
            code: (cells[1] && cells[1].innerText || '').trim(),
            description: (cells[2] && cells[2].innerText || '').trim(),
            units: (cells[3] && cells[3].innerText || '').trim(),
            schedule: getScheduleTextFromCell(cells[4])
        };
        currentBody.insertBefore(createCurrentSubjectRow(data), totalUnitsRow);
    });

    updateCurrentUnitsTotal();
    updateSubjectActionStates();
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast(selectedCatalogRows.length + ' subject(s) added to enrolled list.', 'success');
    }
}

function changeSelectedFromCatalog() {
    var selectedCurrentRows = getSelectedRows('seChangeFromTable');
    var selectedCatalogRows = getSelectedRows('seAddSubjectsTable');

    if (selectedCurrentRows.length !== 1 || selectedCatalogRows.length !== 1) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Select 1 enrolled row and 1 catalog row to change subject.', 'warning');
        }
        return;
    }

    var currentRow = selectedCurrentRows[0];
    var catalogCells = selectedCatalogRows[0].children;

    currentRow.children[1].textContent = (catalogCells[1] && catalogCells[1].innerText || '').trim();
    currentRow.children[2].textContent = (catalogCells[2] && catalogCells[2].innerText || '').trim();
    currentRow.children[4].textContent = (catalogCells[3] && catalogCells[3].innerText || '').trim();
    renderEnrolledScheduleCell(currentRow.children[5], getScheduleTextFromCell(catalogCells[4]));

    updateCurrentUnitsTotal();
    updateSubjectActionStates();
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Subject changed using selected catalog row.', 'success');
    }
}

function dropSelectedCurrentSubjects() {
    var selectedCurrentRows = getSelectedRows('seChangeFromTable');
    if (!selectedCurrentRows.length) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Select enrolled row(s) to drop.', 'warning');
        }
        return;
    }

    selectedCurrentRows.forEach(function (row) { row.remove(); });
    updateCurrentUnitsTotal();
    updateSubjectActionStates();
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast(selectedCurrentRows.length + ' subject(s) dropped.', 'success');
    }
}

function filterCatalogRows() {
    var table = getSubjectTable('seAddSubjectsTable');
    if (!table) return;

    var term = (document.getElementById('seCatalogSearch').value || '').toLowerCase().trim();
    var program = (document.getElementById('seCatalogProgramFilter').value || '').toUpperCase().trim();
    var year = (document.getElementById('seCatalogYearFilter').value || '').trim();
    var rows = table.querySelectorAll('tbody tr');

    rows.forEach(function (row) {
        var text = (row.innerText || '').toLowerCase();
        var rowProgram = String(row.getAttribute('data-program') || '').toUpperCase();
        var rowYear = String(row.getAttribute('data-year') || '');

        var matchSearch = !term || text.indexOf(term) > -1;
        var matchProgram = !program || rowProgram === program;
        var matchYear = !year || rowYear === year;

        row.style.display = (matchSearch && matchProgram && matchYear) ? '' : 'none';
    });
}

function updateCurrentUnitsTotal() {
    var table = getSubjectTable('seChangeFromTable');
    if (!table) return;

    var total = 0;
    var rows = table.querySelectorAll('tbody tr');
    rows.forEach(function (row) {
        if (row.classList.contains('se-total-units-row') || row.classList.contains('se-section-info-row')) return;
        var unitsText = (row.children[4] && row.children[4].innerText || '0').trim();
        var units = parseFloat(unitsText);
        if (!isNaN(units)) total += units;
    });

    var totalEl = table.querySelector('.se-units-cell strong');
    if (totalEl) totalEl.textContent = String(total);
}

function escapeHtml(text) {
    return String(text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function downloadSchedulePdf() {
    var studentName = (document.getElementById('seMetaName') && document.getElementById('seMetaName').textContent) || 'Student';
    var studentId = (document.getElementById('seMetaId') && document.getElementById('seMetaId').textContent) || '';
    var cols = document.querySelectorAll('#seDetailView .so-weekly-col');
    var rowsHtml = '';

    cols.forEach(function (col) {
        var dayEl = col.querySelector('.so-weekly-day');
        var cards = col.querySelectorAll('.so-weekly-card');
        var day = dayEl ? dayEl.textContent.trim() : '';

        if (!cards.length) {
            rowsHtml += '<tr><td>' + escapeHtml(day) + '</td><td>-</td><td>-</td><td>-</td></tr>';
            return;
        }

        cards.forEach(function (card) {
            var time = card.querySelector('.so-weekly-time');
            var code = card.querySelector('.so-weekly-code');
            var room = card.querySelector('.so-weekly-room');
            rowsHtml += '<tr>' +
                '<td>' + escapeHtml(day) + '</td>' +
                '<td>' + escapeHtml(time ? time.textContent.trim() : '-') + '</td>' +
                '<td>' + escapeHtml(code ? code.textContent.trim() : '-') + '</td>' +
                '<td>' + escapeHtml(room ? room.textContent.trim() : '-') + '</td>' +
                '</tr>';
        });
    });

    var printable = '<!doctype html><html><head><meta charset="utf-8"><title>Schedule PDF</title>' +
        '<style>body{font-family:Arial,sans-serif;padding:24px;color:#1f2937;}h1{font-size:18px;margin:0 0 4px;}p{margin:0 0 16px;font-size:12px;color:#4b5563;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #d1d5db;padding:8px;font-size:12px;text-align:left;}th{background:#f3f4f6;}@page{size:A4;margin:14mm;}</style>' +
        '</head><body>' +
        '<h1>Student Weekly Schedule</h1>' +
        '<p><strong>Name:</strong> ' + escapeHtml(studentName) + ' &nbsp; <strong>ID:</strong> ' + escapeHtml(studentId) + '</p>' +
        '<table><thead><tr><th>Day</th><th>Time</th><th>Subject</th><th>Room/Mode</th></tr></thead><tbody>' + rowsHtml + '</tbody></table>' +
        '<script>window.onload=function(){window.print();};<\/script>' +
        '</body></html>';

    var win = window.open('', '_blank');
    if (!win) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Popup blocked. Allow popups to download schedule as PDF.', 'warning');
        }
        return;
    }
    win.document.open();
    win.document.write(printable);
    win.document.close();
}

function createEmptySubjectRow(table, columns) {
    var row = document.createElement('tr');
    for (var i = 0; i < table.querySelectorAll('thead th').length; i++) {
        var td = document.createElement('td');
        if (i === 0) {
            td.innerHTML = '<input type="checkbox">';
        } else {
            td.textContent = '';
        }
        row.appendChild(td);
    }
    return row;
}

function saveSubjectRowModal() {
    var table = getSubjectTable(seSubjectRowState.tableId);
    if (!table) return;
    var mode = seSubjectRowState.mode;

    var values = seSubjectRowState.columns.map(function (column, idx) {
        var field = document.getElementById('seSubjectField' + idx);
        if (!field) return column.value || '';
        return (field.value || '').trim();
    });

    var row = seSubjectRowState.row;
    if (seSubjectRowState.mode === 'add') {
        row = createEmptySubjectRow(table, seSubjectRowState.columns);
        var tbody = table.querySelector('tbody');
        var unitsRow = tbody.querySelector('.se-total-units-row');
        if (unitsRow) tbody.insertBefore(row, unitsRow);
        else tbody.appendChild(row);

        if (seSubjectRowState.tableId === 'seAddSubjectsTable') {
            row.setAttribute('data-program', (document.getElementById('seCatalogProgramFilter').value || 'BSIT').toUpperCase());
            row.setAttribute('data-year', (document.getElementById('seCatalogYearFilter').value || '4'));
        }
    }

    seSubjectRowState.columns.forEach(function (column, idx) {
        setSubjectCellValue(row, column, values[idx] || '');
    });

    var firstCheckbox = row.querySelector('input[type="checkbox"]');
    if (firstCheckbox) firstCheckbox.checked = true;

    if (seSubjectRowState.tableId === 'seChangeFromTable') {
        updateCurrentUnitsTotal();
    }

    updateSubjectActionStates();

    closeSubjectRowModal();
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast(mode === 'add' ? 'Subject row added successfully.' : 'Subject row updated successfully.', 'success');
    }
}

function filterEnrollmentRows() {
    if (seFilterSubmitTimer) {
        clearTimeout(seFilterSubmitTimer);
    }

    seFilterSubmitTimer = setTimeout(function () {
        seFilterSubmitTimer = null;
        submitEnrollmentFilterForm();
    }, 280);
}

function sortEnrollmentRows() {
    submitEnrollmentFilterForm();
}

var seFilterSubmitTimer = null;

function submitEnrollmentFilterForm() {
    var form = document.getElementById('seFilterForm');
    if (!form) return;
    if (seFilterSubmitTimer) {
        clearTimeout(seFilterSubmitTimer);
        seFilterSubmitTimer = null;
    }
    form.submit();
}

function adjustEnrollmentTotal(delta) {
    var table = document.getElementById('seTable');
    var totalEl = document.getElementById('seTotalCount');
    if (!table || !totalEl) return;

    var currentTotal = parseInt(table.getAttribute('data-total-students') || totalEl.textContent || '0', 10);
    if (isNaN(currentTotal)) {
        currentTotal = 0;
    }

    currentTotal = Math.max(0, currentTotal + (parseInt(delta, 10) || 0));
    table.setAttribute('data-total-students', String(currentTotal));
    totalEl.textContent = String(currentTotal);
}

updateEnrollmentTotal();
setEnrollmentViewMode(getEnrollmentViewMode());
filterCatalogRows();
updateSubjectActionStates();
updateEnrolledSectionBanner();
bindApplicantSearchInput(document.getElementById('seAddStudentId'), document.getElementById('seAddStudentIdDropdown'), 'id');
bindApplicantSearchInput(document.getElementById('seAddStudentName'), document.getElementById('seAddStudentNameDropdown'), 'name');

var seFilterForm = document.getElementById('seFilterForm');
if (seFilterForm) {
    seFilterForm.addEventListener('submit', function () {
        if (seFilterSubmitTimer) {
            clearTimeout(seFilterSubmitTimer);
            seFilterSubmitTimer = null;
        }
    });
}

var seSortSelect = document.getElementById('seSort');
if (seSortSelect) {
    seSortSelect.addEventListener('change', sortEnrollmentRows);
}

(function formatInitialCurrentSchedules() {
    var rows = document.querySelectorAll('#seChangeFromTable tbody tr:not(.se-total-units-row)');
    rows.forEach(function (row) {
        var cell = row.children[5];
        if (!cell) return;
        renderEnrolledScheduleCell(cell, getScheduleTextFromCell(cell));
    });
})();

(function formatInitialCatalogSchedules() {
    var rows = document.querySelectorAll('#seAddSubjectsTable tbody tr');
    rows.forEach(function (row) {
        var cell = row.children[4];
        if (!cell) return;
        renderCatalogScheduleCell(cell, getScheduleTextFromCell(cell));
    });
})();

document.addEventListener('change', function (event) {
    if (event.target && event.target.matches('.se-subject-table input[type="checkbox"]')) {
        updateSubjectActionStates();
    }
});
</script>
@endpush
