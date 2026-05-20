@extends('layouts.faculty')

@section('title', 'PLP - Grading Sheet')
@section('page-title', 'GRADING SHEET')

@push('styles')
    <style>
        .gs-submit-grades-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 1.2rem;
            background: #15803d;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            margin-left: auto;
            transition: background 0.2s;
        }

        .gs-submit-grades-btn:hover:not(:disabled) {
            background: #166534;
        }

        .gs-submit-grades-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .fgs-quick-submit-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.9rem;
            background: #15803d;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .fgs-quick-submit-btn:hover:not(:disabled) {
            background: #166534;
        }

        .fgs-quick-submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .fgs-submitted-label {
            color: #15803d;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .faculty-grading-sheet-page .fgs-grade-input,
        .faculty-grading-sheet-page .fgs-remarks-input,
        .faculty-grading-sheet-page .fgs-grade-rule-select {
            width: 100%;
            min-width: 72px;
            padding: 0.38rem 0.45rem;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 0.86rem;
            color: #111827;
            background: #fff;
            appearance: textfield;
            -moz-appearance: textfield;
        }

        .faculty-grading-sheet-page .fgs-grade-input::-webkit-outer-spin-button,
        .faculty-grading-sheet-page .fgs-grade-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .faculty-grading-sheet-page .fgs-remarks-input {
            min-width: 95px;
            max-width: 120px;
        }

        .faculty-grading-sheet-page .fgs-grade-rule-select {
            min-width: 145px;
        }

        .faculty-grading-sheet-page .fgs-row-submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 104px;
            padding: 0.38rem 0.7rem;
            background: #15803d;
            color: #fff;
            border: 0;
            border-radius: 5px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .faculty-grading-sheet-page .fgs-row-submit-btn:hover:not(:disabled) {
            background: #166534;
        }

        .faculty-grading-sheet-page .fgs-row-submit-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .faculty-grading-sheet-page .fgs-cell-muted {
            color: #6b7280;
        }

        .faculty-grading-sheet-page .fgs-autosave-status {
            display: block;
            margin-top: 0.25rem;
            color: #15803d;
            font-size: 0.74rem;
            font-weight: 600;
        }

        .faculty-grading-sheet-page .fgs-subject-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.65rem;
            margin: 0.8rem 0 1rem;
            padding: 0.85rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #fff;
        }

        .faculty-grading-sheet-page .fgs-summary-item {
            min-width: 0;
        }

        .faculty-grading-sheet-page .fgs-summary-label {
            display: block;
            margin-bottom: 0.12rem;
            color: #6b7280;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .faculty-grading-sheet-page .fgs-summary-value {
            color: #111827;
            font-size: 0.88rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .faculty-grading-sheet-page .fgs-phase-status {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
            margin-bottom: 1rem;
        }

        .faculty-grading-sheet-page .fgs-phase-box {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 0.75rem;
            background: #f9fafb;
        }

        .faculty-grading-sheet-page .fgs-phase-title {
            margin: 0 0 0.45rem;
            color: #111827;
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .faculty-grading-sheet-page .fgs-phase-line {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.18rem 0;
            color: #374151;
            font-size: 0.82rem;
        }

        .faculty-grading-sheet-page .fgs-phase-line strong {
            color: #111827;
        }

        .faculty-grading-sheet-page .fgs-transmutation-box {
            margin-bottom: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            overflow: hidden;
        }

        .faculty-grading-sheet-page .fgs-transmutation-head {
            padding: 0.65rem 0.8rem;
            color: #111827;
            font-size: 0.86rem;
            font-weight: 900;
            text-transform: uppercase;
            background: #f3f6f4;
            border-bottom: 1px solid #d1d5db;
        }

        .faculty-grading-sheet-page .fgs-transmutation-scroll {
            max-height: 180px;
            overflow: auto;
        }

        .faculty-grading-sheet-page .fgs-transmutation-table {
            width: 100%;
            border-collapse: collapse;
        }

        .faculty-grading-sheet-page .fgs-transmutation-table th,
        .faculty-grading-sheet-page .fgs-transmutation-table td {
            padding: 0.42rem 0.65rem;
            border-bottom: 1px solid #edf1ee;
            color: #1f2937;
            font-size: 0.78rem;
            font-weight: 700;
            text-align: left;
        }

        .faculty-grading-sheet-page .fgs-transmutation-table th {
            color: #374151;
            font-size: 0.72rem;
            text-transform: uppercase;
            background: #fafafa;
        }

        @media (max-width: 900px) {
            .faculty-grading-sheet-page .fgs-subject-summary,
            .faculty-grading-sheet-page .fgs-phase-status {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
<div class="grading-sheet-wrap faculty-grading-sheet-page">

    <div class="gs-filter-bar" id="fgsFilterBar">
        <div class="gs-filter-row faculty-gs-main-tools-row" id="fgsMainToolsRow">
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">School Year</span>
                <select class="gs-filter-select" id="fgsSchoolYear">
                    @php
                        $schoolYears = collect($subjects ?? [])->pluck('school_year')->filter()->unique()->values();
                        $defaultSchoolYear = $schoolYears->first() ?: '2025-2026';
                    @endphp
                    @forelse($schoolYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @empty
                        <option value="{{ $defaultSchoolYear }}">{{ $defaultSchoolYear }}</option>
                    @endforelse
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">Semester</span>
                <select class="gs-filter-select" id="fgsSemester">
                    @php
                        $semesters = collect($subjects ?? [])->pluck('semester')->filter()->unique()->values();
                    @endphp
                    <option value="">All</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester }}">{{ $semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">Status</span>
                <select class="gs-filter-select" id="fgsStatus">
                    <option value="">All</option>
                    <option value="Open For Encoding">Open For Encoding</option>
                    <option value="Returned for Revision">Returned for Revision</option>
                    <option value="Submitted for Dean Review">Submitted for Dean Review</option>
                    <option value="Dean Approved">Dean Approved</option>
                    <option value="Registrar Finalized">Registrar Finalized</option>
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">Section</span>
                <select class="gs-filter-select" id="fgsSection">
                    <option value="">All</option>
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">Subject</span>
                <select class="gs-filter-select" id="fgsSubject">
                    <option value="">All</option>
                </select>
            </div>
            <div class="gs-filter-group faculty-gs-search-btn-wrap">
                <button type="button" class="gs-view-btn" id="fgsSearchBtn">Search</button>
            </div>
        </div>

    </div>

    <div class="faculty-gs-detail-tools-row faculty-gs-hidden" id="fgsDetailToolsRow">
        <div class="gs-filter-group faculty-gs-detail-search-wrap">
            <label class="gs-filter-label" for="gradingDetailSearch">Search</label>
            <input type="text" id="gradingDetailSearch" class="gs-filter-select" placeholder="Search Student ID / Name">
        </div>
    </div>

    <div id="gradingSubjectView" class="faculty-gs-subject-view">
        <h3 class="faculty-gs-school-year" id="fgsYearLabel">{{ $defaultSchoolYear }}</h3>

        <div class="faculty-table-wrap">
            <table class="faculty-table" id="gradingSubjectTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Section</th>
                        <th>Subject Code</th>
                        <th>Subject Description</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="gradingSubjectBody"></tbody>
            </table>
        </div>
    </div>

    <form id="gradingDetailForm" method="POST" action="{{ route('faculty.grading-sheet.update') }}" class="faculty-gs-hidden">
        @csrf
        <input type="hidden" name="subject_id" id="gradingSubjectIdInput" value="">

        <div class="faculty-gs-detail-heading">
            <button type="button" class="gs-back-btn faculty-gs-back-inline" id="gradingBackBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Back to List
            </button>
            <div class="faculty-detail-title" id="gradingDetailTitle"></div>
            <div class="faculty-detail-section" id="gradingDetailSection"></div>

            <button type="button" class="gs-submit-grades-btn" id="gradingPostMidtermBtn" style="display:none;">
                Post Midterm
            </button>
            <button type="button" class="gs-submit-grades-btn" id="gradingPostFinalBtn" style="display:none;">
                Post Final
            </button>
        </div>

        <div class="fgs-subject-summary">
            <div class="fgs-summary-item">
                <span class="fgs-summary-label">Subject Name</span>
                <span class="fgs-summary-value" id="gradingMetaSubject">-</span>
            </div>
            <div class="fgs-summary-item">
                <span class="fgs-summary-label">Subject Description</span>
                <span class="fgs-summary-value" id="gradingMetaDescription">-</span>
            </div>
            <div class="fgs-summary-item">
                <span class="fgs-summary-label">Professor</span>
                <span class="fgs-summary-value" id="gradingMetaProfessor">-</span>
            </div>
            <div class="fgs-summary-item">
                <span class="fgs-summary-label">Schedule</span>
                <span class="fgs-summary-value" id="gradingMetaSchedule">-</span>
            </div>
        </div>

        <div class="fgs-phase-status">
            <div class="fgs-phase-box">
                <h4 class="fgs-phase-title">Midterm Status</h4>
                <div class="fgs-phase-line"><span>Faculty Post</span><strong id="gradingMidtermPostStatus">-</strong></div>
                <div class="fgs-phase-line"><span>Dean</span><strong id="gradingMidtermDeanStatus">-</strong></div>
                <div class="fgs-phase-line"><span>Registrar</span><strong id="gradingMidtermRegistrarStatus">-</strong></div>
            </div>
            <div class="fgs-phase-box">
                <h4 class="fgs-phase-title">Finals Status</h4>
                <div class="fgs-phase-line"><span>Faculty Post</span><strong id="gradingFinalPostStatus">-</strong></div>
                <div class="fgs-phase-line"><span>Dean</span><strong id="gradingFinalDeanStatus">-</strong></div>
                <div class="fgs-phase-line"><span>Registrar</span><strong id="gradingFinalRegistrarStatus">-</strong></div>
            </div>
        </div>

        <div class="fgs-transmutation-box" id="gradingTransmutationBox" style="display:none;">
            <div class="fgs-transmutation-head">Transmutation Table</div>
            <div class="fgs-transmutation-scroll">
                <table class="fgs-transmutation-table">
                    <thead>
                        <tr>
                            <th>Initial From</th>
                            <th>Initial To</th>
                            <th>Eq. Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="gradingTransmutationBody"></tbody>
                </table>
            </div>
        </div>

        <div class="faculty-table-wrap">
            <table class="faculty-table" id="gradingDetailTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th>Midterm</th>
                        <th>Final</th>
                        <th>Average</th>
                        <th>Eq. Grade</th>
                        <th>Status</th>
                        <th style="width:120px;">Remarks</th>
                        <th style="width:128px;">Submission of Grade</th>
                    </tr>
                </thead>
                <tbody id="gradingDetailBody"></tbody>
            </table>
        </div>

        <div id="gradingRowEditPayload"></div>
    </form>

    <div class="req-modal-overlay fgs-row-edit-overlay" id="gradingRowEditModal" style="display:none;" aria-hidden="true">
        <div class="req-modal-box fgs-row-edit-box" role="dialog" aria-modal="true" aria-labelledby="gradingRowEditTitle">
            <div class="fgs-row-edit-head">
                <h3 class="req-modal-title fgs-row-edit-title" id="gradingRowEditTitle">Edit Student Grades</h3>
                <button type="button" class="rep-modal-close-x" id="gradingRowEditClose" aria-label="Close">&times;</button>
            </div>
            <div class="fgs-row-edit-student" id="gradingRowEditStudent"></div>
            <div class="fgs-row-edit-fields">
                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="gradingRowEditMidterm">MIDTERM</label>
                    <input type="number" class="req-modal-input" id="gradingRowEditMidterm" min="50" max="100" step="0.01" placeholder="50 to 100">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="gradingRowEditFinal">FINAL</label>
                    <input type="number" class="req-modal-input" id="gradingRowEditFinal" min="50" max="100" step="0.01" placeholder="50 to 100">
                </div>
                <div class="req-modal-field-group fgs-row-edit-field-remarks">
                    <label class="req-modal-label" for="gradingRowEditRemarks">REMARKS</label>
                    <textarea class="req-modal-input" id="gradingRowEditRemarks" rows="2" placeholder="Type remarks"></textarea>
                </div>
            </div>
            <div class="req-modal-actions">
                <button type="button" class="req-btn-cancel" id="gradingRowEditCancel">Cancel</button>
                <button type="button" class="req-btn-save" id="gradingRowEditSave">Save</button>
            </div>
        </div>
    </div>

    <div id="gradingSheetData" data-subjects='@json($gradingSubjects)' data-grade-rules='@json($gradeRules ?? [])'></div>

</div>

<div class="req-modal-overlay" id="gradingMissingModal" style="display:none;" aria-hidden="true">
    <div class="req-modal-box" role="dialog" aria-modal="true">
        <div class="fgs-row-edit-head">
            <h3 class="req-modal-title">Cannot Submit Grades</h3>
            <button type="button" class="rep-modal-close-x" id="gradingMissingClose">&times;</button>
        </div>
        <p style="padding: 0 1.2rem; color:#b91c1c; font-weight:600;" id="gradingMissingMessage"></p>
        <ul id="gradingMissingList" style="padding: 0.5rem 2rem 1rem; max-height:220px; overflow-y:auto; color:#374151; font-size:0.9rem;"></ul>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-save" id="gradingMissingOk">OK</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/faculty-grading-sheet.js') }}?v={{ file_exists(public_path('js/faculty-grading-sheet.js')) ? filemtime(public_path('js/faculty-grading-sheet.js')) : time() }}"></script>
@endpush
