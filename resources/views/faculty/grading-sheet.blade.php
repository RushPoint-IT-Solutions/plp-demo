@extends('layouts.faculty')

@section('title', 'PLP - Grading Sheet')
@section('page-title', 'GRADING SHEET')

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
                    <option value="Submitted">Submitted</option>
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
                        <th>Final Average</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="gradingDetailBody"></tbody>
            </table>
        </div>

            <div class="rtp-pagination faculty-gs-pager faculty-gs-hidden" id="gradingDetailPager">
                <nav class="rtp-nav" aria-label="Table pagination">
                    <div class="rtp-list" id="gradingDetailPagerList"></div>
                </nav>
            </div>

        <div class="faculty-detail-actions faculty-gs-hidden" id="gradingInputAction">
            <button type="button" class="btn-view-list" id="gradingInputBtn">Input Grades</button>
        </div>
    </form>

    <div id="gradingSheetData" data-subjects='@json($gradingSubjects)'></div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/faculty-grading-sheet.js') }}?v={{ file_exists(public_path('js/faculty-grading-sheet.js')) ? filemtime(public_path('js/faculty-grading-sheet.js')) : time() }}"></script>
@endpush
