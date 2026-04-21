@extends('layouts.registrar')

@section('title', 'PLP - Student Grade File')
@section('page-title', 'STUDENT GRADE FILE')
@section('body-class', 'page-student-grade-file')

@section('content')
<div class="pf-page">
    <div class="sgf-page">
        {{-- LIST VIEW --}}
        <div id="sgfListView">
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

                <div class="app-table-pager" id="sgfListPager"></div>
            </section>
        </div>

        {{-- DETAIL VIEW --}}
        <div id="sgfDetailView" style="display:none;">
            <div class="sgf-detail-header">
                <div class="sgf-detail-info-panel">
                    <div class="sgf-info-row">
                        <div><strong>STUDENT NAME:</strong> <span id="sgfDetName">-</span></div>
                        <div><strong>STUDENT NO:</strong> <span id="sgfDetStudentNo">-</span></div>
                    </div>
                    <div class="sgf-info-row">
                        <div><strong>PROGRAM:</strong> <span id="sgfDetProgram">-</span></div>
                        <div><strong>YEAR LEVEL:</strong> <span id="sgfDetYearLevel">-</span></div>
                    </div>
                    <div class="sgf-info-row">
                        <div><strong>STATUS:</strong> <span id="sgfDetStatus">OLD</span></div>
                        <div><strong>SCHOOL YEAR:</strong> <span id="sgfDetSchoolYear">2025-2026</span></div>
                    </div>
                </div>

                <div class="sgf-detail-tabs">
                    <button class="sgf-tab active" data-tab="grades">Grade File</button>
                    <button class="sgf-tab" data-tab="history">View History</button>
                   {{--   <button class="sgf-tab" data-tab="evaluation">Evaluation</button> --}}
                    <button class="sgf-tab" data-tab="tor">Transcript of Records</button>
                    <button class="sgf-tab" data-tab="permanent">Permanent Record</button>
                  {{--    <button class="sgf-tab" data-tab="grade-report">Grade Report</button>  --}}
                </div>
            </div>

            {{-- Tab Content: Grades (main) --}}
            <div class="sgf-tab-content" id="sgfTabGrades">
                <div class="sgf-detail-actions">
                    <button type="button" class="gs-back-btn" id="sgfBackBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Back to List
                    </button>
                    <div class="sgf-detail-btns">
                        <button type="button" class="pf-btn-new sgf-btn-action sgf-btn-add" id="sgfAddSubjectBtn">+ Add Subject</button>
                        <button type="button" class="pf-btn-new sgf-btn-action sgf-btn-transferee" id="sgfAddTransfereeBtn">+ Add Transferee Subjects</button>
                        <button type="button" class="pf-btn-new sgf-btn-action sgf-btn-old" id="sgfAddOldBtn">+ Add Old Records</button>
                        <button type="button" class="pf-btn-new sgf-btn-action sgf-btn-delete" id="sgfDeleteSelectedBtn">Delete Selected</button>
                    </div>
                </div>

                <div id="sgfGradeTermGroups"></div>
            </div>

            {{-- Tab Content: Placeholders --}}
            <div class="sgf-tab-content" id="sgfTabHistory" style="display:none;">
                <div class="sgf-history-sheet" id="sgfHistorySheet">
                    <div class="sgf-history-title">STUDENT SUBJECTS TRACKING</div>

                    <div id="sgfHistoryGroups"></div>
                </div>
            </div>
            <div class="sgf-tab-content" id="sgfTabEvaluation" style="display:none;">
                <div class="sgf-placeholder-panel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#b0b8c1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <h3>Evaluation</h3>
                    <p>Curriculum evaluation and subject checklist will be displayed here.</p>
                </div>
            </div>
            <div class="sgf-tab-content" id="sgfTabTor" style="display:none;">
                <div class="sgf-tor-embed-wrap">
                    <iframe
                        id="sgfTorEmbedFrame"
                        class="sgf-tor-embed-frame"
                        src="{{ route('registrar.registrar-menu.forms.tor', ['embedded' => 1, 'preview_only' => 1]) }}"
                        title="TOR Preview"
                        loading="eager"
                    ></iframe>
                </div>
            </div>

            <div class="sgf-tab-content" id="sgfTabPermanent" style="display:none;">
                <div class="sgf-perm-tools">
                    <button type="button" class="pf-btn-new" id="sgfPrintPermanentBtn">Print Permanent Record</button>
                </div>

                <div class="sgf-perm-sheet-wrap" id="sgfPermanentPrintArea">
                    <section class="sgf-perm-page sgf-perm-page-1">
                        <div class="sgf-perm-school">Central Luzon College of Science and Technology, Inc.</div>
                        <div class="sgf-perm-office">OFFICE OF THE REGISTRAR</div>
                        <div class="sgf-perm-form-title">STUDENT'S PERMANENT RECORD</div>
                        <div class="sgf-perm-course-row">
                            <div class="sgf-perm-course-label">COURSE :</div>
                            <div class="sgf-perm-course"><strong id="sgfPermCourse">-</strong></div>
                        </div>

                        <div class="sgf-perm-personal">
                            <h4>Personal Records:</h4>
                            <div class="sgf-perm-personal-lines">
                                <div class="sgf-perm-personal-line sgf-perm-personal-line-3col">
                                    <div class="sgf-perm-personal-head-label sgf-perm-no-gap" style="grid-column: 3;">Student Number: <span class="sgf-perm-short-underline sgf-perm-val-no-bold" id="sgfPermStudentNo">TRG2600001</span></div>
                                </div>
                                <div class="sgf-perm-personal-line sgf-perm-personal-line-3col">
                                    <div class="sgf-perm-no-gap"><span>Name:</span><strong id="sgfPermName">-</strong></div>
                                    <div class="sgf-perm-no-gap"><span>Sex:</span><span class="sgf-perm-short-underline" id="sgfPermSex">-</span></div>
                                    <div class="sgf-perm-no-gap"><span>Citizenship:</span><span class="sgf-perm-short-underline" id="sgfPermCitizenship">-</span></div>
                                </div>

                                <div class="sgf-perm-personal-line sgf-perm-personal-line-3col">
                                    <div class="sgf-perm-dob-field sgf-perm-no-gap">
                                        <span>Date of Birth: Year:</span><strong id="sgfPermBirthYear">-</strong>
                                        <span class="sgf-perm-inline-label">Month:</span><strong id="sgfPermBirthMonth">-</strong>
                                    </div>
                                    <div class="sgf-perm-no-gap"><span>Day:</span><strong id="sgfPermBirthDay">-</strong></div>
                                    <div class="sgf-perm-no-gap"><span>Civil Status:</span><span class="sgf-perm-short-underline" id="sgfPermCivilStatus">-</span></div>
                                </div>

                                <div class="sgf-perm-personal-line sgf-perm-personal-line-3col">
                                    <div class="sgf-perm-span-2 sgf-perm-no-gap"><span>Place of Birth: Province:</span><strong id="sgfPermBirthProvince">-</strong></div>
                                    <div class="sgf-perm-no-gap"><span>Municipality:</span><strong id="sgfPermMunicipality">-</strong></div>
                                </div>

                                <div class="sgf-perm-personal-line sgf-perm-personal-line-3col">
                                    <div class="sgf-perm-span-2 sgf-perm-no-gap"><span>High School Completed at:</span><strong id="sgfPermHighSchool">-</strong></div>
                                    <div class="sgf-perm-no-gap"><span>School Year:</span><strong id="sgfPermSchoolYear">-</strong></div>
                                </div>

                                <div class="sgf-perm-personal-line sgf-perm-personal-line-3col">
                                    <div class="sgf-perm-no-gap"><span>Other School Attended. If Any:</span><strong id="sgfPermOtherSchool">-</strong></div>
                                    <div class="sgf-perm-no-gap"><span>SY:</span><strong id="sgfPermOtherSchoolYear">-</strong></div>
                                    <div class="sgf-perm-no-gap"><span>Course Taken:</span><strong id="sgfPermCourseTaken">-</strong></div>
                                </div>
                            </div>
                        </div>

                        <table class="sgf-perm-table" data-no-auto-pager="1">
                            <thead>
                                <tr>
                                    <th>COURSE NUMBER</th>
                                    <th>DESCRIPTIVE TITLE</th>
                                    <th>FINAL GRADE</th>
                                    <th>CREDIT EARNED</th>
                                </tr>
                            </thead>
                            <tbody id="sgfPermTableBody"></tbody>
                        </table>

                    </section>

                    <section class="sgf-perm-page sgf-perm-page-2">
                        <div class="sgf-perm-cont-head">
                            <strong>Name:</strong> <span id="sgfPermNamePage2">-</span>
                        </div>

                        <table class="sgf-perm-table sgf-perm-table-blank" data-no-auto-pager="1">
                            <thead>
                                <tr>
                                    <th>COURSE NUMBER</th>
                                    <th>DESCRIPTIVE TITLE</th>
                                    <th>FINAL GRADE</th>
                                    <th>CREDIT EARNED</th>
                                </tr>
                            </thead>
                            <tbody id="sgfPermTableBodyPage2"></tbody>
                        </table>

                        <div class="sgf-perm-certification">
                            <h5><u>CERTIFICATION</u></h5>
                            <p>I hereby certify that the above are true official records of</p>

                            <div class="sgf-perm-signatures">
                                <div>
                                    <strong>Ms. Maila D. Masangcay</strong>
                                    <span class="sgf-perm-sign-line"></span>
                                    <small>Registrar</small>
                                </div>
                                <div>
                                    <strong id="sgfPermSigName">-</strong>
                                    <span class="sgf-perm-sign-line"></span>
                                    <small>kept in the file of this college.</small>
                                </div>
                            </div>
                        </div>

                    </section>
                </div>
            </div>
            <div class="sgf-tab-content" id="sgfTabGradeReport" style="display:none;">
                <div class="sgf-placeholder-panel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#b0b8c1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <h3>Grade Report</h3>
                    <p>Official grade report for the student will be generated here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Student Modal --}}
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

{{-- Delete Student Modal --}}
@include('includes.registrar-delete-modal', [
    'id' => 'sgfDeleteModal',
    'title' => 'DELETE RECORD',
    'message' => 'Are you sure you want to delete this specific student grade record? All associated grades will be removed.',
    'confirmBtnText' => 'Delete',
    'cancelAction' => 'sgfCloseDeleteModal()',
    'confirmAction' => 'sgfConfirmDelete()',
    'detailId' => 'sgfDeleteDetail'
])
<input type="hidden" id="sgfDeleteId" value="">

{{-- Add Subject Modal --}}
<div class="req-modal-overlay" id="sgfSubjectModal" style="display:none;" onclick="if(event.target===this) sgfCloseSubjectModal()">
    <div class="req-modal-box" style="max-width:820px;">
        <h3 class="req-modal-title" id="sgfSubjectModalTitle">ADD SUBJECT</h3>
        <input type="hidden" id="sgfSubjectEditingId" value="">
        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">School Year</label>
                <input id="sgfSubjSY" type="text" class="req-modal-input" placeholder="e.g. 2023-2024">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Term</label>
                <select id="sgfSubjTerm" class="req-modal-input">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Subject Code</label>
                <input id="sgfSubjCode" type="text" class="req-modal-input" placeholder="e.g. CRIM111">
            </div>
        </div>
        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Equiv. Subject Code</label>
                <input id="sgfSubjEquiv" type="text" class="req-modal-input" placeholder="Equiv. code">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Units</label>
                <input id="sgfSubjUnits" type="number" class="req-modal-input" placeholder="3" min="0" max="99" step="0.5">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Section Code</label>
                <input id="sgfSubjSection" type="text" class="req-modal-input" placeholder="e.g. BSCRIM">
            </div>
        </div>
        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Description</label>
                <input id="sgfSubjDesc" type="text" class="req-modal-input" placeholder="Subject description">
            </div>
        </div>
        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Professor</label>
                <input id="sgfSubjProf" type="text" class="req-modal-input" placeholder="Last Name, First Name">
            </div>
        </div>
        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Final Grade</label>
                <input id="sgfSubjGrade" type="number" class="req-modal-input" placeholder="e.g. 1.75" step="0.01">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Grade Status</label>
                <select id="sgfSubjStatus" class="req-modal-input">
                    <option value="P">P (Passed)</option>
                    <option value="F">F (Failed)</option>
                    <option value="">N/A</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">INC</label>
                <select id="sgfSubjInc" class="req-modal-input">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
        </div>
        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Remarks</label>
                <input id="sgfSubjRemarks" type="text" class="req-modal-input" placeholder="Optional remarks">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="sgfCloseSubjectModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="sgfSaveSubject()">Save</button>
        </div>
    </div>
</div>

{{-- Delete Subject Modal --}}
@include('includes.registrar-delete-modal', [
    'id' => 'sgfSubjectDeleteModal',
    'title' => 'DELETE SUBJECT',
    'message' => 'Are you sure you want to delete this subject?',
    'confirmBtnText' => 'Delete',
    'cancelAction' => 'sgfCloseSubjectDeleteModal()',
    'confirmAction' => 'sgfConfirmSubjectDelete()',
    'detailId' => 'sgfSubjectDeleteDetail'
])
<input type="hidden" id="sgfSubjectDeleteId" value="">
@endsection

@push('scripts')
<script>
    var sgfRows = @json($sgfRows ?? []);
    var sgfCsrf = '{{ csrf_token() }}';
    var sgfApi = {
        store: '{{ route('registrar.admin-tools.master-files.student-grade-file.store') }}',
        updateTemplate: '{{ route('registrar.admin-tools.master-files.student-grade-file.update', ['masterStudentGradeFile' => '__ID__']) }}',
        destroyTemplate: '{{ route('registrar.admin-tools.master-files.student-grade-file.destroy', ['masterStudentGradeFile' => '__ID__']) }}',
        records: '{{ route('registrar.admin-tools.master-files.student-grade-file.records') }}',
        recordStore: '{{ route('registrar.admin-tools.master-files.student-grade-file.records.store') }}',
        recordUpdateTemplate: '{{ route('registrar.admin-tools.master-files.student-grade-file.records.update', ['studentGradeRecord' => '__ID__']) }}',
        recordDestroyTemplate: '{{ route('registrar.admin-tools.master-files.student-grade-file.records.destroy', ['studentGradeRecord' => '__ID__']) }}'
    };
    var sgfCurrentPage = 1;
    var sgfPageSize = 10;
    var sgfActiveStudent = null;
    var sgfGradeRecords = [];
    var sgfSelectedRecordIds = [];

    function sgfBuildUrl(template, id) { return template.replace('__ID__', encodeURIComponent(String(id))); }

    function sgfRequest(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': sgfCsrf, 'Accept': 'application/json' },
            body: payload ? JSON.stringify(payload) : null
        }).then(function(response) {
            return response.json().catch(function() { return {}; }).then(function(data) {
                if (!response.ok || data.ok === false) {
                    var message = (data && data.message) ? data.message : 'Request failed.';
                    if (data && data.errors) { var k = Object.keys(data.errors)[0]; if (k && data.errors[k] && data.errors[k][0]) message = data.errors[k][0]; }
                    throw new Error(message);
                }
                return data;
            });
        });
    }

    function sgfEscapeHtml(v) { return String(v||'').replace(/[&<>"']/g, function(c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

    function sgfGetFilteredRows() {
        var q = (document.getElementById('sgfSearch').value || '').toLowerCase();
        return sgfRows.filter(function(r) {
            if (!q) return true;
            return r.studentId.toLowerCase().indexOf(q) !== -1 || r.name.toLowerCase().indexOf(q) !== -1 || r.course.toLowerCase().indexOf(q) !== -1 || r.yearLevel.toLowerCase().indexOf(q) !== -1;
        });
    }

    function sgfBuildMenu(menuId, id) {
        return '<div class="apst-action-btn" data-sgf-menu-toggle="'+menuId+'" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="'+menuId+'">' +
                '<button type="button" onclick="sgfOpenStudentDetail(\''+sgfEscapeHtml(id)+'\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>View Grades</button>' +
                '<button type="button" onclick="sgfOpenEditModal(\''+sgfEscapeHtml(id)+'\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" onclick="sgfOpenDeleteModal(\''+sgfEscapeHtml(id)+'\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>Delete</button>' +
            '</div>';
    }

    function sgfCloseActionMenus() { document.querySelectorAll('.apst-dropdown.open').forEach(function(m) { m.classList.remove('open','drop-up'); m.style.top=''; m.style.left=''; m.style.right=''; m.style.bottom=''; }); }

    function sgfToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId); if (!menu || !trigger) return;
        var isOpen = menu.classList.contains('open'); sgfCloseActionMenus(); if (isOpen) return;
        var rect = trigger.getBoundingClientRect(); menu.style.left='auto'; menu.style.right=(window.innerWidth-rect.left+4)+'px';
        if (window.innerHeight-rect.bottom < 120) { menu.classList.add('drop-up'); menu.style.top='auto'; menu.style.bottom=(window.innerHeight-rect.bottom)+'px'; } else { menu.style.top=rect.top+'px'; menu.style.bottom='auto'; }
        menu.classList.add('open');
    }

    function sgfRenderPager(totalRows) {
        var mount = document.getElementById('sgfListPager'); if (!mount) return;
        var maxPage = Math.max(1, Math.ceil(totalRows / sgfPageSize));
        if (sgfCurrentPage > maxPage) sgfCurrentPage = maxPage;
        if (totalRows <= sgfPageSize) { mount.innerHTML = ''; return; }
        var start = Math.max(1, sgfCurrentPage - 2), end = Math.min(maxPage, sgfCurrentPage + 2);
        if (sgfCurrentPage <= 3) end = Math.min(maxPage, 5); else if (sgfCurrentPage >= maxPage - 2) start = Math.max(1, maxPage - 4);
        var nums = ''; for (var p = start; p <= end; p++) nums += '<button type="button" class="rtp-page-num '+(p===sgfCurrentPage?'active':'')+'" data-sgf-page="'+p+'">'+p+'</button>';
        mount.innerHTML = '<div class="rtp-pagination"><nav class="rtp-nav"><div class="rtp-list" role="group">' +
            '<button type="button" class="rtp-page-btn" data-sgf-page-prev="1" '+(sgfCurrentPage<=1?'disabled':'')+'>&lt;</button>' + nums +
            '<button type="button" class="rtp-page-btn" data-sgf-page-next="1" '+(sgfCurrentPage>=maxPage?'disabled':'')+'>&gt;</button></div></nav></div>';
    }

    function sgfRenderTable() {
        var tbody = document.getElementById('sgfTableBody'); if (!tbody) return;
        sgfCloseActionMenus();
        var rows = sgfGetFilteredRows();
        var maxPage = Math.max(1, Math.ceil(rows.length / sgfPageSize));
        if (sgfCurrentPage > maxPage) sgfCurrentPage = 1;
        var si = (sgfCurrentPage - 1) * sgfPageSize;
        var page = rows.slice(si, si + sgfPageSize);

        var html = page.map(function(r, i) {
            var mid = 'sgfMenu' + i;
            return '<tr class="sgf-student-row" data-student-id="'+sgfEscapeHtml(r.id)+'" data-student-no="'+sgfEscapeHtml(r.studentId)+'" data-name="'+sgfEscapeHtml(r.name)+'" data-course="'+sgfEscapeHtml(r.course)+'" data-year="'+sgfEscapeHtml(r.yearLevel)+'">' +
                '<td>'+(si+i+1)+'</td><td>'+sgfEscapeHtml(r.studentId)+'</td><td><a href="#" class="sgf-name-link" data-sgf-open-detail="'+sgfEscapeHtml(r.id)+'">'+sgfEscapeHtml(r.name).toUpperCase()+'</a></td><td>'+sgfEscapeHtml(r.course)+'</td><td>'+sgfEscapeHtml(r.yearLevel)+'</td>' +
                '<td style="text-align:center;">'+sgfBuildMenu(mid, r.id)+'</td></tr>';
        }).join('');

        if (!html) html = '<tr><td colspan="6" class="sc-empty-row">No student grade records found.</td></tr>';
        tbody.innerHTML = html + '<tr class="sgf-total-row"><td colspan="6">Total Students: <strong>'+rows.length+'</strong></td></tr>';
        sgfRenderPager(rows.length);
    }

    /* Student CRUD */
    function sgfOpenAddModal() { document.getElementById('sgfFormTitle').textContent='ADD STUDENT GRADE FILE RECORD'; document.getElementById('sgfEditingId').value=''; document.getElementById('sgfIdInput').value=''; document.getElementById('sgfNameInput').value=''; document.getElementById('sgfCourseInput').value=''; document.getElementById('sgfYearInput').value='First'; sgfCloseActionMenus(); document.getElementById('sgfFormModal').style.display='flex'; }
    function sgfOpenEditModal(id) { var r = sgfRows.find(function(item){return String(item.id)===String(id);}); if(!r) return; document.getElementById('sgfFormTitle').textContent='EDIT STUDENT GRADE FILE RECORD'; document.getElementById('sgfEditingId').value=r.id; document.getElementById('sgfIdInput').value=r.studentId; document.getElementById('sgfNameInput').value=r.name; document.getElementById('sgfCourseInput').value=r.course; document.getElementById('sgfYearInput').value=r.yearLevel; sgfCloseActionMenus(); document.getElementById('sgfFormModal').style.display='flex'; }
    function sgfCloseFormModal() { document.getElementById('sgfFormModal').style.display='none'; }

    function sgfSaveRecord() {
        var editingId = document.getElementById('sgfEditingId').value;
        var payload = { student_id: document.getElementById('sgfIdInput').value.trim(), name: document.getElementById('sgfNameInput').value.trim(), course: document.getElementById('sgfCourseInput').value.trim(), year_level: document.getElementById('sgfYearInput').value };
        if (!payload.student_id || !payload.name || !payload.course) { alert('Please fill in Student ID, Student Name, and Course.'); return; }
        var req = !editingId ? sgfRequest(sgfApi.store, 'POST', payload) : sgfRequest(sgfBuildUrl(sgfApi.updateTemplate, editingId), 'PUT', payload);
        req.then(function(data) { if (!editingId) sgfRows.unshift(data.row); else sgfRows = sgfRows.map(function(item) { return String(item.id) === String(editingId) ? data.row : item; }); sgfCloseFormModal(); sgfRenderTable(); }).catch(function(err) { alert(err.message || 'Unable to save.'); });
    }

    function sgfOpenDeleteModal(id) { sgfCloseActionMenus(); document.getElementById('sgfDeleteId').value=id; document.getElementById('sgfDeleteModal').style.display='flex'; }
    function sgfCloseDeleteModal() { document.getElementById('sgfDeleteModal').style.display='none'; }
    function sgfConfirmDelete() { var id=document.getElementById('sgfDeleteId').value; sgfRequest(sgfBuildUrl(sgfApi.destroyTemplate,id),'DELETE',null).then(function(){sgfRows=sgfRows.filter(function(i){return String(i.id)!==String(id);});sgfCloseDeleteModal();sgfRenderTable();}).catch(function(e){alert(e.message||'Unable to delete.');}); }

    /* Detail View */
    function sgfOpenStudentDetail(id) {
        sgfCloseActionMenus();
        var row = sgfRows.find(function(r) { return String(r.id) === String(id); });
        if (!row) return;
        sgfActiveStudent = row;
        document.getElementById('sgfDetName').textContent = row.name.toUpperCase();
        document.getElementById('sgfDetStudentNo').textContent = row.studentId;
        document.getElementById('sgfDetProgram').textContent = row.course;
        document.getElementById('sgfDetYearLevel').textContent = row.yearLevel;
        document.getElementById('sgfDetSchoolYear').textContent = '-';
        document.getElementById('sgfListView').style.display = 'none';
        document.getElementById('sgfDetailView').style.display = 'block';
        sgfSwitchTab('grades');
        sgfLoadGradeRecords(row.studentId);
    }

    function sgfBackToList() {
        document.getElementById('sgfDetailView').style.display = 'none';
        document.getElementById('sgfListView').style.display = 'block';
        sgfActiveStudent = null;
        sgfGradeRecords = [];
    }

    function sgfSwitchTab(tabName) {
        document.querySelectorAll('.sgf-tab').forEach(function(tab) { tab.classList.toggle('active', tab.getAttribute('data-tab') === tabName); });
        var tabMap = { grades: 'sgfTabGrades', history: 'sgfTabHistory', evaluation: 'sgfTabEvaluation', tor: 'sgfTabTor', permanent: 'sgfTabPermanent', 'grade-report': 'sgfTabGradeReport' };
        Object.keys(tabMap).forEach(function(k) { var el = document.getElementById(tabMap[k]); if (el) el.style.display = (k === tabName) ? 'block' : 'none'; });

        if (tabName === 'history') {
            sgfRenderHistorySheet();
        }

        if (tabName === 'permanent') {
            sgfRenderPermanentRecord();
        }
    }

    function sgfLoadGradeRecords(studentNo) {
        sgfRequest(sgfApi.records + '?student_no=' + encodeURIComponent(studentNo), 'GET', null).then(function(data) {
            sgfGradeRecords = data.records || [];
            sgfRenderGradeGroups();
            sgfRenderHistorySheet();
            sgfRenderPermanentRecord();
        }).catch(function(err) { alert(err.message || 'Unable to load grade records.'); });
    }

    function sgfRenderGradeGroups() {
        var container = document.getElementById('sgfGradeTermGroups');
        if (!container) return;
        sgfSelectedRecordIds = [];

        var groups = {};
        sgfGradeRecords.forEach(function(r) {
            var key = r.school_year + '|' + r.term;
            if (!groups[key]) groups[key] = { sy: r.school_year, term: r.term, records: [] };
            groups[key].records.push(r);
        });

        var keys = Object.keys(groups).sort(function(a, b) { return a < b ? -1 : a > b ? 1 : 0; });

        if (!keys.length) {
            container.innerHTML = '<div class="sgf-placeholder-panel"><p>No grade records found for this student.</p></div>';
            return;
        }

        var html = '';
        keys.forEach(function(key) {
            var g = groups[key];
            var totalUnits = 0, weightedSum = 0, numericCount = 0;
            g.records.forEach(function(r) {
                totalUnits += r.units;
                if (r.final_grade !== null && r.final_grade > 0) { weightedSum += r.units * r.final_grade; numericCount += r.units; }
            });
            var wga = numericCount > 0 ? (weightedSum / numericCount).toFixed(2) : '-';

            html += '<div class="sgf-term-group">';
            html += '<div class="sgf-term-header"><span class="sgf-term-label">SCHOOL YEAR: <strong>' + sgfEscapeHtml(g.sy) + '</strong></span><span class="sgf-term-sep">Term: <strong>' + sgfEscapeHtml(g.term) + '</strong></span></div>';
            html += '<div class="app-table-wrap"><table class="app-table sgf-grade-table" data-no-auto-pager="1"><thead><tr>' +
                '<th style="width:30px;"><input type="checkbox" class="sgf-check-all" data-group-key="'+sgfEscapeHtml(key)+'"></th>' +
                '<th>Subject Code</th><th>Equiv. Code</th><th>Professor</th><th>Description</th><th>Units</th><th>Status</th><th>Section Code</th><th>Final Grade</th><th>INC</th><th>Grade Status</th><th>Remarks</th><th>Action</th></tr></thead><tbody>';

            g.records.forEach(function(r, idx) {
                html += '<tr data-record-id="'+r.id+'">' +
                    '<td><input type="checkbox" class="sgf-record-check" value="'+r.id+'"></td>' +
                    '<td>'+sgfEscapeHtml(r.subject_code)+'</td>' +
                    '<td>'+sgfEscapeHtml(r.equiv_subject_code||'')+'</td>' +
                    '<td>'+sgfEscapeHtml(r.professor||'')+'</td>' +
                    '<td>'+sgfEscapeHtml(r.description||'')+'</td>' +
                    '<td>'+r.units+'</td>' +
                    '<td>'+(r.status||'')+'</td>' +
                    '<td>'+sgfEscapeHtml(r.section_code||'')+'</td>' +
                    '<td>'+(r.final_grade !== null ? r.final_grade.toFixed(2) : '')+'</td>' +
                    '<td>'+(r.inc ? 'Yes' : '')+'</td>' +
                    '<td>'+sgfEscapeHtml(r.grade_status||'')+'</td>' +
                    '<td>'+sgfEscapeHtml(r.remarks||'')+'</td>' +
                    '<td style="text-align:center;"><button type="button" class="sgf-row-edit-btn" data-edit-record="'+r.id+'" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>' +
                    '<button type="button" class="sgf-row-del-btn" data-delete-record="'+r.id+'" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg></button></td></tr>';
            });

            html += '</tbody></table></div>';
            html += '<div class="sgf-wga-row">WGA (excludes NSTP and subjects with non numeric ratings): <span class="sgf-wga-total">'+totalUnits+'</span> <span class="sgf-wga-label">GWA: <strong>'+wga+'</strong></span></div>';
            html += '</div>';
        });

        container.innerHTML = html;
    }

    function sgfEscapeAttr(v) {
        return String(v || '').replace(/"/g, '&quot;');
    }

    function sgfTermOrder(v) {
        var t = String(v || '').toLowerCase();
        if (t.indexOf('first') !== -1) return 1;
        if (t.indexOf('second') !== -1) return 2;
        if (t.indexOf('summer') !== -1) return 3;
        return 9;
    }

    function sgfNumericSchoolYearStart(v) {
        var m = String(v || '').match(/\d{4}/);
        return m ? parseInt(m[0], 10) : 0;
    }

    function sgfFormatDateTime(value) {
        if (!value) return '-';
        var d = new Date(value.replace(' ', 'T'));
        if (isNaN(d.getTime())) return String(value);
        return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    }

    function sgfRenderHistorySheet() {
        var host = document.getElementById('sgfHistoryGroups');
        if (!host) return;

        if (!sgfGradeRecords.length) {
            host.innerHTML = '<div class="sgf-history-empty">No subject activity found for this student.</div>';
            return;
        }

        var groups = {};
        sgfGradeRecords.forEach(function (r) {
            var key = String(r.school_year || '-') + '|' + String(r.term || '-');
            if (!groups[key]) {
                groups[key] = {
                    school_year: r.school_year || '-',
                    term: r.term || '-',
                    records: []
                };
            }
            groups[key].records.push(r);
        });

        var keys = Object.keys(groups).sort(function (a, b) {
            var ga = groups[a], gb = groups[b];
            var ya = sgfNumericSchoolYearStart(ga.school_year);
            var yb = sgfNumericSchoolYearStart(gb.school_year);
            if (ya !== yb) return yb - ya;
            return sgfTermOrder(ga.term) - sgfTermOrder(gb.term);
        });

        var html = '';
        keys.forEach(function (key) {
            var g = groups[key];
            html += '<section class="sgf-history-group">';
            html += '<div class="sgf-history-group-head"><span>School Year : <strong>' + sgfEscapeHtml(g.school_year) + '</strong></span><span>Semester : <strong>' + sgfEscapeHtml(g.term) + '</strong></span></div>';
            html += '<div class="sgf-history-action-title">ADD SUBJECT:</div>';

            g.records.forEach(function (r) {
                var actor = r.professor ? r.professor : 'registrar';
                var txDate = sgfFormatDateTime(r.created_at || r.updated_at);
                html += '<div class="sgf-history-row">';
                html += '<div class="sgf-history-main">';
                html += '<div class="sgf-history-code">' + sgfEscapeHtml(r.subject_code || '-') + '</div>';
                html += '<div class="sgf-history-desc">' + sgfEscapeHtml(r.description || '-') + '</div>';
                html += '</div>';
                html += '<div class="sgf-history-meta-right">';
                html += '<div><span>User ID</span><strong>' + sgfEscapeHtml(actor) + '</strong></div>';
                html += '<div><span>Transaction Date</span><strong>' + sgfEscapeHtml(txDate) + '</strong></div>';
                html += '</div>';
                html += '</div>';
            });

            var changedRows = g.records.filter(function (r) {
                if (!r.created_at || !r.updated_at) return false;
                return String(r.created_at) !== String(r.updated_at);
            });

            if (changedRows.length) {
                html += '<div class="sgf-history-action-title">CHANGE GRADES:</div>';
                changedRows.forEach(function (r) {
                    var changedDate = sgfFormatDateTime(r.updated_at);
                    html += '<div class="sgf-history-row sgf-history-row-grade">';
                    html += '<div class="sgf-history-main">';
                    html += '<div class="sgf-history-code">' + sgfEscapeHtml(r.subject_code || '-') + '</div>';
                    html += '<div class="sgf-history-desc">' + sgfEscapeHtml(r.description || '-') + '</div>';
                    html += '<div class="sgf-history-grade-flow">Final Grade: <strong>' + (r.final_grade !== null ? Number(r.final_grade).toFixed(2) : '-') + '</strong></div>';
                    html += '</div>';
                    html += '<div class="sgf-history-meta-right">';
                    html += '<div><span>User ID</span><strong>' + sgfEscapeHtml(r.professor || 'registrar') + '</strong></div>';
                    html += '<div><span>Transaction Date</span><strong>' + sgfEscapeHtml(changedDate) + '</strong></div>';
                    html += '</div>';
                    html += '</div>';
                });
            }

            html += '</section>';
        });

        host.innerHTML = html;
    }

    function sgfBuildPermanentRows() {
        var rows = (sgfGradeRecords || []).slice().sort(function (a, b) {
            var ya = sgfNumericSchoolYearStart(a.school_year);
            var yb = sgfNumericSchoolYearStart(b.school_year);
            if (ya !== yb) return ya - yb;
            var ta = sgfTermOrder(a.term);
            var tb = sgfTermOrder(b.term);
            if (ta !== tb) return ta - tb;
            return String(a.subject_code || '').localeCompare(String(b.subject_code || ''));
        });

        var result = [];
        var currentKey = '';
        rows.forEach(function (r) {
            var sectionKey = String(r.school_year || '-') + '|' + String(r.term || '-');
            if (sectionKey !== currentKey) {
                currentKey = sectionKey;
                result.push({
                    kind: 'term',
                    title: String(r.term || '-').toUpperCase() + ', ' + String(r.school_year || '-')
                });
            }

            result.push({
                kind: 'record',
                subject_code: r.subject_code || '',
                description: r.description || '',
                final_grade: r.final_grade,
                units: r.units
            });
        });

        var termCount = result.filter(function (r) { return r.kind === 'term'; }).length;
        if (termCount < 6) {
            var dummySubjects = [
                { code: 'CDI311', title: 'TRAFFIC MANAGEMENT AND ACCIDENT INVEST.', grade: 3.00, units: 3 },
                { code: 'CLI211', title: 'CRIMINAL LAW BOOK 1', grade: 2.75, units: 3 },
                { code: 'CRIS411', title: 'LEGAL MEDICINE', grade: 2.50, units: 3 },
                { code: 'DT211', title: 'FIRST AID AND WATER SURVIVAL', grade: 1.75, units: 2 }
            ];
            var baseYear = rows.length ? sgfNumericSchoolYearStart(rows[0].school_year) : 2018;

            for (var i = termCount; i < 6; i++) {
                var yearOffset = Math.floor(i / 2);
                var termName = (i % 2 === 0) ? 'FIRST' : 'SECOND';
                var syStart = baseYear + yearOffset;
                var syText = syStart + '-' + (syStart + 1);

                result.push({
                    kind: 'term',
                    title: termName + ', ' + syText
                });

                dummySubjects.forEach(function (s) {
                    result.push({
                        kind: 'record',
                        subject_code: s.code,
                        description: s.title,
                        final_grade: s.grade,
                        units: s.units
                    });
                });
            }
        }

        return result;
    }

    function sgfPermanentBlankRows(count) {
        var html = '';
        for (var i = 0; i < count; i++) {
            html += '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
        }
        return html;
    }

    function sgfRenderPermanentRecord() {
        var body1 = document.getElementById('sgfPermTableBody');
        var body2 = document.getElementById('sgfPermTableBodyPage2');
        if (!body1 || !body2) return;

        var studentName = sgfActiveStudent ? String(sgfActiveStudent.name || '').toUpperCase() : '-';
        var studentNo = sgfActiveStudent ? sgfActiveStudent.studentId : '-';
        var course = sgfActiveStudent ? sgfActiveStudent.course : '-';
        if (course === 'BSIT') course = 'Bachelor of Science in Information Technology';
        if (course === 'BSCRIM') course = 'Bachelor of Science in Criminology';
        if (course === 'BSBA') course = 'Bachelor of Science in Business Administration';
        if (course === 'BSED') course = 'Bachelor of Secondary Education';
        if (course === 'BEED') course = 'Bachelor of Elementary Education';

        document.getElementById('sgfPermCourse').textContent = course;
        document.getElementById('sgfPermName').textContent = studentName;
        document.getElementById('sgfPermNamePage2').textContent = studentName;
        document.getElementById('sgfPermStudentNo').textContent = studentNo;
        document.getElementById('sgfPermSigName').textContent = studentName;

        document.getElementById('sgfPermSex').textContent = '';
        document.getElementById('sgfPermBirthYear').textContent = '';
        document.getElementById('sgfPermBirthMonth').textContent = '';
        document.getElementById('sgfPermBirthDay').textContent = '';
        document.getElementById('sgfPermCivilStatus').textContent = '';
        document.getElementById('sgfPermCitizenship').textContent = '';
        document.getElementById('sgfPermBirthProvince').textContent = '';
        document.getElementById('sgfPermMunicipality').textContent = '';
        document.getElementById('sgfPermHighSchool').textContent = '';
        document.getElementById('sgfPermOtherSchool').textContent = '';
        document.getElementById('sgfPermOtherSchoolYear').textContent = '';
        document.getElementById('sgfPermCourseTaken').textContent = '';

        var years = (sgfGradeRecords || []).map(function (r) { return r.school_year || ''; }).filter(function (v) { return v !== ''; });
        document.getElementById('sgfPermSchoolYear').textContent = years.length ? years[0] : '-';

        var rows = sgfBuildPermanentRows();
        var maxPage1Rows = 32;

        var html1 = '';
        var html2 = '';
        rows.forEach(function (row, index) {
            var target = index < maxPage1Rows ? 'page1' : 'page2';
            var chunk = '';

            if (row.kind === 'term') {
                chunk = '<tr class="sgf-perm-term-row"><td></td><td>' + sgfEscapeHtml(row.title) + '</td><td></td><td></td></tr>';
            } else {
                chunk = '<tr>' +
                    '<td>' + sgfEscapeHtml(row.subject_code) + '</td>' +
                    '<td>' + sgfEscapeHtml(row.description) + '</td>' +
                    '<td>' + (row.final_grade !== null ? Number(row.final_grade).toFixed(2) : '-') + '</td>' +
                    '<td>' + (row.units !== null && row.units !== undefined ? Number(row.units).toFixed(1).replace('.0', '') : '-') + '</td>' +
                '</tr>';
            }

            if (target === 'page1') {
                html1 += chunk;
            } else {
                html2 += chunk;
            }
        });

        if (!html1) {
            html1 = '<tr><td colspan="4" class="sgf-perm-empty">No grade records available.</td></tr>';
        }

        var page2RowCount = rows.length > maxPage1Rows ? (rows.length - maxPage1Rows) : 0;
        if (page2RowCount < 40) {
            html2 += sgfPermanentBlankRows(40 - page2RowCount);
        }

        body1.innerHTML = html1;
        body2.innerHTML = html2;
    }


    /* Subject CRUD */
    function sgfOpenAddSubjectModal() {
        if (!sgfActiveStudent) return;
        document.getElementById('sgfSubjectModalTitle').textContent = 'ADD SUBJECT';
        document.getElementById('sgfSubjectEditingId').value = '';
        document.getElementById('sgfSubjSY').value = '2025-2026';
        document.getElementById('sgfSubjTerm').value = 'First';
        document.getElementById('sgfSubjCode').value = '';
        document.getElementById('sgfSubjEquiv').value = '';
        document.getElementById('sgfSubjUnits').value = '3';
        document.getElementById('sgfSubjSection').value = '';
        document.getElementById('sgfSubjDesc').value = '';
        document.getElementById('sgfSubjProf').value = '';
        document.getElementById('sgfSubjGrade').value = '';
        document.getElementById('sgfSubjStatus').value = 'P';
        document.getElementById('sgfSubjInc').value = '0';
        document.getElementById('sgfSubjRemarks').value = '';
        document.getElementById('sgfSubjectModal').style.display = 'flex';
    }

    function sgfOpenEditSubjectModal(recordId) {
        var rec = sgfGradeRecords.find(function(r) { return String(r.id) === String(recordId); });
        if (!rec) return;
        document.getElementById('sgfSubjectModalTitle').textContent = 'EDIT SUBJECT';
        document.getElementById('sgfSubjectEditingId').value = rec.id;
        document.getElementById('sgfSubjSY').value = rec.school_year;
        document.getElementById('sgfSubjTerm').value = rec.term;
        document.getElementById('sgfSubjCode').value = rec.subject_code;
        document.getElementById('sgfSubjEquiv').value = rec.equiv_subject_code || '';
        document.getElementById('sgfSubjUnits').value = rec.units;
        document.getElementById('sgfSubjSection').value = rec.section_code || '';
        document.getElementById('sgfSubjDesc').value = rec.description || '';
        document.getElementById('sgfSubjProf').value = rec.professor || '';
        document.getElementById('sgfSubjGrade').value = rec.final_grade !== null ? rec.final_grade : '';
        document.getElementById('sgfSubjStatus').value = rec.grade_status || '';
        document.getElementById('sgfSubjInc').value = rec.inc ? '1' : '0';
        document.getElementById('sgfSubjRemarks').value = rec.remarks || '';
        document.getElementById('sgfSubjectModal').style.display = 'flex';
    }

    function sgfCloseSubjectModal() { document.getElementById('sgfSubjectModal').style.display = 'none'; }

    function sgfSaveSubject() {
        var editingId = document.getElementById('sgfSubjectEditingId').value;
        var payload = {
            student_no: sgfActiveStudent ? sgfActiveStudent.studentId : '',
            school_year: document.getElementById('sgfSubjSY').value.trim(),
            term: document.getElementById('sgfSubjTerm').value,
            subject_code: document.getElementById('sgfSubjCode').value.trim(),
            equiv_subject_code: document.getElementById('sgfSubjEquiv').value.trim() || null,
            professor: document.getElementById('sgfSubjProf').value.trim() || null,
            description: document.getElementById('sgfSubjDesc').value.trim() || null,
            units: parseFloat(document.getElementById('sgfSubjUnits').value) || 0,
            status: null,
            section_code: document.getElementById('sgfSubjSection').value.trim() || null,
            final_grade: document.getElementById('sgfSubjGrade').value !== '' ? parseFloat(document.getElementById('sgfSubjGrade').value) : null,
            inc: document.getElementById('sgfSubjInc').value === '1',
            grade_status: document.getElementById('sgfSubjStatus').value || null,
            remarks: document.getElementById('sgfSubjRemarks').value.trim() || null
        };
        if (!payload.subject_code || !payload.school_year) { alert('Please fill in Subject Code and School Year.'); return; }

        var req = !editingId ? sgfRequest(sgfApi.recordStore, 'POST', payload) : sgfRequest(sgfBuildUrl(sgfApi.recordUpdateTemplate, editingId), 'PUT', payload);
        req.then(function() {
            sgfCloseSubjectModal();
            sgfLoadGradeRecords(sgfActiveStudent.studentId);
        }).catch(function(err) { alert(err.message || 'Unable to save subject.'); });
    }

    function sgfOpenSubjectDeleteModal(id) { document.getElementById('sgfSubjectDeleteId').value = id; document.getElementById('sgfSubjectDeleteModal').style.display = 'flex'; }
    function sgfCloseSubjectDeleteModal() { document.getElementById('sgfSubjectDeleteModal').style.display = 'none'; }
    function sgfConfirmSubjectDelete() {
        var id = document.getElementById('sgfSubjectDeleteId').value;
        sgfRequest(sgfBuildUrl(sgfApi.recordDestroyTemplate, id), 'DELETE', null).then(function() {
            sgfCloseSubjectDeleteModal();
            sgfLoadGradeRecords(sgfActiveStudent.studentId);
        }).catch(function(err) { alert(err.message || 'Unable to delete.'); });
    }

    function sgfDeleteSelectedRecords() {
        var checks = document.querySelectorAll('.sgf-record-check:checked');
        if (!checks.length) { alert('Please select at least one subject to delete.'); return; }
        if (!confirm('Delete ' + checks.length + ' selected subject(s)?')) return;
        var ids = []; checks.forEach(function(c) { ids.push(c.value); });
        var chain = Promise.resolve();
        ids.forEach(function(id) { chain = chain.then(function() { return sgfRequest(sgfBuildUrl(sgfApi.recordDestroyTemplate, id), 'DELETE', null); }); });
        chain.then(function() { sgfLoadGradeRecords(sgfActiveStudent.studentId); }).catch(function(err) { alert(err.message || 'Unable to delete.'); sgfLoadGradeRecords(sgfActiveStudent.studentId); });
    }

    /* Event listeners */
    document.getElementById('sgfSearchBtn').addEventListener('click', function() { sgfCurrentPage = 1; sgfRenderTable(); });
    document.getElementById('sgfSearch').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); sgfCurrentPage = 1; sgfRenderTable(); } });
    document.getElementById('sgfNewRecordBtn').addEventListener('click', sgfOpenAddModal);
    document.getElementById('sgfBackBtn').addEventListener('click', sgfBackToList);
    document.getElementById('sgfAddSubjectBtn').addEventListener('click', sgfOpenAddSubjectModal);
    document.getElementById('sgfDeleteSelectedBtn').addEventListener('click', sgfDeleteSelectedRecords);
    document.getElementById('sgfAddTransfereeBtn').addEventListener('click', function() {
        sgfOpenAddSubjectModal();
        document.getElementById('sgfSubjectModalTitle').textContent = 'ADD TRANSFEREE SUBJECT';
    });
    document.getElementById('sgfAddOldBtn').addEventListener('click', function() {
        sgfOpenAddSubjectModal();
        document.getElementById('sgfSubjectModalTitle').textContent = 'ADD OLD RECORD';
    });
    var sgfPrintPermanentBtn = document.getElementById('sgfPrintPermanentBtn');
    if (sgfPrintPermanentBtn) {
        sgfPrintPermanentBtn.addEventListener('click', function () {
            sgfRenderPermanentRecord();
            document.body.classList.remove('sgf-print-tor');
            document.body.classList.add('sgf-print-permanent');
            window.print();
        });
    }
    window.addEventListener('beforeprint', function () {
        var activeTab = document.querySelector('.sgf-tab.active');
        var activeName = activeTab ? activeTab.getAttribute('data-tab') : '';

        document.body.classList.remove('sgf-print-permanent');
        document.body.classList.remove('sgf-print-tor');

        if (activeName === 'permanent') {
            sgfRenderPermanentRecord();
            document.body.classList.add('sgf-print-permanent');
        }
    });
    window.addEventListener('afterprint', function () {
        document.body.classList.remove('sgf-print-permanent');
        document.body.classList.remove('sgf-print-tor');
    });

    document.addEventListener('click', function(e) {
        var menuToggle = e.target.closest('[data-sgf-menu-toggle]');
        if (menuToggle) { e.stopPropagation(); sgfToggleActionMenu(menuToggle.getAttribute('data-sgf-menu-toggle'), menuToggle); return; }
        if (!e.target.closest('.apst-dropdown')) sgfCloseActionMenus();

        var nameLink = e.target.closest('[data-sgf-open-detail]');
        if (nameLink) { e.preventDefault(); sgfOpenStudentDetail(nameLink.getAttribute('data-sgf-open-detail')); return; }

        var tab = e.target.closest('.sgf-tab');
        if (tab) { sgfSwitchTab(tab.getAttribute('data-tab')); return; }

        var editBtn = e.target.closest('[data-edit-record]');
        if (editBtn) { sgfOpenEditSubjectModal(editBtn.getAttribute('data-edit-record')); return; }

        var delBtn = e.target.closest('[data-delete-record]');
        if (delBtn) { sgfOpenSubjectDeleteModal(delBtn.getAttribute('data-delete-record')); return; }

        var checkAll = e.target.closest('.sgf-check-all');
        if (checkAll) {
            var table = checkAll.closest('table');
            if (table) table.querySelectorAll('.sgf-record-check').forEach(function(c) { c.checked = checkAll.checked; });
        }
    });

    window.addEventListener('scroll', sgfCloseActionMenus, true);

    document.getElementById('sgfListPager').addEventListener('click', function(e) {
        var prev = e.target.closest('[data-sgf-page-prev]');
        if (prev && sgfCurrentPage > 1) { sgfCurrentPage -= 1; sgfRenderTable(); return; }
        var next = e.target.closest('[data-sgf-page-next]');
        if (next) { var total = sgfGetFilteredRows().length; var mp = Math.max(1, Math.ceil(total / sgfPageSize)); if (sgfCurrentPage < mp) { sgfCurrentPage += 1; sgfRenderTable(); } return; }
        var pageBtn = e.target.closest('[data-sgf-page]');
        if (pageBtn) { sgfCurrentPage = parseInt(pageBtn.getAttribute('data-sgf-page'), 10) || 1; sgfRenderTable(); }
    });

    sgfRenderTable();
</script>
@endpush
