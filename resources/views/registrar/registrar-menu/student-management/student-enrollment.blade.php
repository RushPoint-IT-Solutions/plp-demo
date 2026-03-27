@extends('layouts.registrar')

@section('title', 'PLP - Student Enrollment')
@section('page-title', 'STUDENT ENROLLMENT')
@section('body-class', 'page-student-enrollment')

@section('content')

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
            <div class="se-toolbar-left">
                <div class="se-search-wrap">
                    <span class="app-filter-label">Search</span>
                    <div class="pf-search-wrap" style="max-width:100%;">
                        <span class="pf-search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="text" id="seSearch" class="pf-search-input" placeholder="Search Student ID / Name" oninput="filterEnrollmentRows()">
                    </div>
                </div>
                <div class="se-sort-wrap">
                    <span class="app-filter-label">Sort</span>
                    <select id="seSort" class="app-filter-select" onchange="sortEnrollmentRows()" style="width:100%;">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>
            </div>
            <div class="se-toolbar-right">
                <button type="button" class="pf-btn-new" onclick="openImportCsvModal()">Import CSV</button>
                <button type="button" class="pf-btn-new" onclick="openAddStudentModal()">+Add Student</button>
            </div>
        </div>

        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table" id="seTable">
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
                    <tr data-student-id="2223A8137">
                        <td>1</td>
                        <td>2223A8137</td>
                        <td><a href="#" class="se-name-link" onclick="openEnrollmentDetail('2223A8137', 'BARES, MARK JAY'); return false;">Bares, Mark Jay</a></td>
                        <td>Bachelor Of Science In Computer Science</td>
                        <td>Fourth</td>
                        <td>
                            <div class="se-row-actions">
                                <button type="button" class="doclist-action-btn doclist-delete-btn" onclick="openDeleteStudentModal('2223A8137')" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-student-id="2223A8139">
                        <td>2</td>
                        <td>2223A8139</td>
                        <td><a href="#" class="se-name-link" onclick="openEnrollmentDetail('2223A8139', 'DELA CRUZ, JUAN'); return false;">Dela Cruz, Juan</a></td>
                        <td>Bachelor Of Science In Computer Science</td>
                        <td>Fourth</td>
                        <td>
                            <div class="se-row-actions">
                                <button type="button" class="doclist-action-btn doclist-delete-btn" onclick="openDeleteStudentModal('2223A8139')" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-student-id="2223A8140">
                        <td>3</td>
                        <td>2223A8140</td>
                        <td><a href="#" class="se-name-link" onclick="openEnrollmentDetail('2223A8140', 'AUSTERO, ANDREA JANE'); return false;">Austero, Andrea Jane</a></td>
                        <td>Bachelor Of Science In Computer Science</td>
                        <td>Fourth</td>
                        <td>
                            <div class="se-row-actions">
                                <button type="button" class="doclist-action-btn doclist-delete-btn" onclick="openDeleteStudentModal('2223A8140')" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-student-id="2223A8141">
                        <td>4</td>
                        <td>2223A8141</td>
                        <td><a href="#" class="se-name-link" onclick="openEnrollmentDetail('2223A8141', 'SANTOS, MARIA'); return false;">Santos, Maria</a></td>
                        <td>Bachelor Of Science In Computer Science</td>
                        <td>Fourth</td>
                        <td>
                            <div class="se-row-actions">
                                <button type="button" class="doclist-action-btn doclist-delete-btn" onclick="openDeleteStudentModal('2223A8141')" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="se-total-row">
                        <td colspan="6" class="se-total-cell">Total Students: <strong id="seTotalCount">4</strong></td>
                    </tr>
                </tbody>
            </table>
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
                <div><span class="se-meta-label">Program:</span> Bachelor Of Science In Computer Science</div>
                <div><span class="se-meta-label">Year Level:</span> Fourth Year</div>
                <div><span class="se-meta-label">Status:</span> OLD (Regular) 3 Unit(s) Allowed (CY2223)</div>
                <div><span class="se-meta-label">Section:</span> A</div>
                <div><span class="se-meta-label">School Year:</span> 2025-2026</div>
                <div><span class="se-meta-label">Term:</span> First</div>
            </div>
        </div>

        <div class="se-section">
            <div class="se-subject-title">Current Enrolled Subjects:</div>
            <div class="se-subject-tools">
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
                            <th>Schedule (BlockSection-SectionCode-Schedule)</th>
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
                    <select id="seCatalogProgramFilter" class="app-filter-select" onchange="filterCatalogRows()">
                        <option value="">All Programs</option>
                        <option value="BSIT" selected>BSIT</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSBA">BSBA</option>
                    </select>
                </div>
                <div class="se-mini-field">
                    <span class="app-filter-label">Year Level</span>
                    <select id="seCatalogYearFilter" class="app-filter-select" onchange="filterCatalogRows()">
                        <option value="">All Years</option>
                        <option value="1">1st</option>
                        <option value="2">2nd</option>
                        <option value="3">3rd</option>
                        <option value="4" selected>4th</option>
                    </select>
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
            <form action="{{ route('registrar-menu.student-mgmt.student-enrollment.store') }}" method="POST">
                @csrf
                <div class="se-modal-grid">
                    <div class="se-modal-field">
                        <label class="pf-modal-label">Student ID</label>
                        <input type="text" name="student_no" id="seAddStudentId" class="pf-modal-input" placeholder="e.g. 2223A9001" required>
                    </div>
                    <div class="se-modal-field">
                        <label class="pf-modal-label">Student Name</label>
                        <input type="text" name="name" id="seAddStudentName" class="pf-modal-input" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="se-modal-field">
                        <label class="pf-modal-label">Program</label>
                        <select name="program" id="seAddStudentProgram" class="pf-modal-select" required>
                            <option value="">Select Program</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSCS">BSCS</option>
                            <option value="BSED">BSED</option>
                            <option value="BSBA">BSBA</option>
                            <option value="BSN">BSN</option>
                        </select>
                    </div>
                    <div class="se-modal-field">
                        <label class="pf-modal-label">Year Level</label>
                        <select name="year_level" id="seAddStudentYear" class="pf-modal-select" required>
                            <option value="">Select Year Level</option>
                            <option value="First">First</option>
                            <option value="Second">Second</option>
                            <option value="Third">Third</option>
                            <option value="Fourth">Fourth</option>
                        </select>
                    </div>
                    <!-- Hidden requirements based on typical registrar input defaults -->
                    <input type="hidden" name="school_year" value="{{ date('Y') }}-{{ date('Y')+1 }}">
                    <input type="hidden" name="semester" value="1st Semester">
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
                    <input type="text" id="seEditStudentProgram" class="pf-modal-input">
                </div>
                <div class="se-modal-field">
                    <label class="pf-modal-label">Year Level</label>
                    <input type="text" id="seEditStudentYear" class="pf-modal-input">
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

@push('scripts')
<script>
var seEditingStudentId = null;
var seDeletingStudentId = null;
var seSubjectRowState = {
    mode: '',
    tableId: '',
    row: null,
    columns: []
};

function openEnrollmentDetail(studentId, studentName) {
    document.getElementById('seMetaId').textContent = studentId;
    document.getElementById('seMetaName').textContent = studentName;
    document.getElementById('seListView').style.display = 'none';
    document.getElementById('seDetailView').style.display = 'block';
    updateCurrentUnitsTotal();
}

function getStudentRowById(studentId) {
    return document.querySelector('#seTableBody tr[data-student-id="' + studentId + '"]');
}

function openEditStudentModal(studentId) {
    var row = getStudentRowById(studentId);
    if (!row) return;

    seEditingStudentId = studentId;
    document.getElementById('seEditStudentId').value = row.children[1].innerText.trim();
    document.getElementById('seEditStudentName').value = row.children[2].innerText.trim();
    document.getElementById('seEditStudentProgram').value = row.children[3].innerText.trim();
    document.getElementById('seEditStudentYear').value = row.children[4].innerText.trim();
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

    if (!studentId || !studentName) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Student ID and Student Name are required.', 'warning');
        }
        return;
    }

    row.setAttribute('data-student-id', studentId);
    row.children[1].textContent = studentId;
    var editedLink = document.createElement('a');
    editedLink.href = '#';
    editedLink.className = 'se-name-link';
    editedLink.textContent = studentName;
    editedLink.addEventListener('click', function (evt) {
        evt.preventDefault();
        openEnrollmentDetail(studentId, studentName.toUpperCase());
    });
    row.children[2].innerHTML = '';
    row.children[2].appendChild(editedLink);
    row.children[3].textContent = program;
    row.children[4].textContent = yearLevel;
    row.children[5].innerHTML = '';
    row.children[5].appendChild(buildRowActionCell(studentId).firstChild);

    closeEditStudentModal();
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Student updated successfully.', 'success');
    }
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
    if (row) row.remove();
    closeDeleteStudentModal();
    renumberEnrollmentRows();
    updateEnrollmentTotal();
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Student deleted successfully.', 'success');
    }
}

function buildRowActionCell(studentId) {
    var tdAction = document.createElement('td');
    tdAction.innerHTML = '<div class="se-row-actions"><button type="button" class="doclist-action-btn doclist-delete-btn" onclick="openDeleteStudentModal(\'' + studentId + '\')" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button></div>';
    return tdAction;
}

function withdrawEnrollment() {
    document.getElementById('seDetailView').style.display = 'none';
    document.getElementById('seListView').style.display = 'block';
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Enrollment withdrawn.', 'warning');
    }
}

function openAddStudentModal() {
    document.getElementById('seAddStudentModal').style.display = 'flex';
}

function closeAddStudentModal() {
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
    renumberEnrollmentRows();
    updateEnrollmentTotal();

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

function createEnrollmentRow(studentId, studentName, program, yearLevel) {
    var tr = document.createElement('tr');
    tr.setAttribute('data-student-id', studentId);

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

    tr.appendChild(buildRowActionCell(studentId));

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
        imported++;
    }

    if (imported > 0) {
        renumberEnrollmentRows();
        updateEnrollmentTotal();
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
    var tbody = document.getElementById('seTableBody');
    var totalEl = document.getElementById('seTotalCount');
    if (!tbody || !totalEl) return;
    var total = Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
        return !row.classList.contains('se-total-row');
    }).length;
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
        var line = document.createElement('div');
        line.className = 'se-schedule-line';
        line.textContent = entry;
        cell.appendChild(line);
    });
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
        if (row.classList.contains('se-total-units-row')) return false;
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
        if (row.classList.contains('se-total-units-row')) return;
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
    var term = (document.getElementById('seSearch').value || '').toLowerCase().trim();
    var rows = Array.from(document.querySelectorAll('#seTableBody tr'));
    rows.forEach(function (row) {
        if (row.classList.contains('se-total-row')) return;
        var text = row.innerText.toLowerCase();
        row.style.display = text.indexOf(term) > -1 ? '' : 'none';
    });
}

function sortEnrollmentRows() {
    var tbody = document.getElementById('seTableBody');
    var totalRow = tbody.querySelector('.se-total-row');
    var rows = Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
        return !row.classList.contains('se-total-row');
    });
    var mode = document.getElementById('seSort').value;
    rows.sort(function (a, b) {
        var nameA = a.children[2].innerText.toLowerCase();
        var nameB = b.children[2].innerText.toLowerCase();
        return mode === 'desc' ? nameB.localeCompare(nameA) : nameA.localeCompare(nameB);
    });
    rows.forEach(function (row, idx) {
        tbody.insertBefore(row, totalRow);
    });
    renumberEnrollmentRows();
}

updateEnrollmentTotal();
filterCatalogRows();
updateSubjectActionStates();

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
