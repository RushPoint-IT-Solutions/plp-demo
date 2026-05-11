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

            <button type="button" class="gs-submit-grades-btn" id="gradingSubmitBtn" style="display:none;">
                Submit Grades
            </button>
        </div>

        <div class="faculty-table-wrap">
            <table class="faculty-table" id="gradingDetailTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Final</th>
                        <th>Semestral Grade</th>
                        <th>Remarks</th>
                        <th style="width:88px;">Action</th>
                    </tr>
                </thead>
                <tbody id="gradingDetailBody"></tbody>
            </table>
        </div>

        <div id="gradingRowEditPayload"></div>

            <div class="rtp-pagination faculty-gs-pager faculty-gs-hidden" id="gradingDetailPager">
                <nav class="rtp-nav" aria-label="Table pagination">
                    <div class="rtp-list" id="gradingDetailPagerList"></div>
                </nav>
            </div>
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
                    <label class="req-modal-label" for="gradingRowEditPrelim">PRELIM</label>
                    <input type="number" class="req-modal-input" id="gradingRowEditPrelim" min="0" max="100" step="0.01" placeholder="0 to 100 or 1.00 to 5.00">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="gradingRowEditMidterm">MIDTERM</label>
                    <input type="number" class="req-modal-input" id="gradingRowEditMidterm" min="0" max="100" step="0.01" placeholder="0 to 100 or 1.00 to 5.00">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="gradingRowEditFinal">FINAL</label>
                    <input type="number" class="req-modal-input" id="gradingRowEditFinal" min="0" max="100" step="0.01" placeholder="0 to 100 or 1.00 to 5.00">
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

    <div id="gradingSheetData" data-subjects='@json($gradingSubjects)'></div>

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
