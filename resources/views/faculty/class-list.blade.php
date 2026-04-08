@extends('layouts.faculty')

@section('title', 'PLP - Class List')
@section('page-title', 'CLASS LIST')

@section('content')
<div class="faculty-class-list-wrap faculty-class-list-page">

    @php
        $schoolYears = collect($subjects ?? [])->pluck('school_year')->filter()->unique()->values();
        $defaultSchoolYear = $schoolYears->first() ?: '2025-2026';
        $semesters = collect($subjects ?? [])->pluck('semester')->filter()->unique()->values();
        $classListSubjects = collect($subjects ?? [])->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => (string) $subject->name,
                'code' => (string) $subject->code,
                'units' => number_format((float) $subject->units, 1),
                'days' => str_replace(',', ', ', (string) $subject->days),
                'time' => (string) $subject->formatted_time,
                'room' => (string) $subject->room,
                'year_section' => (string) $subject->year_section,
                'section_display' => trim(($subject->course ?: '') . ' ' . ($subject->year_section ?: '')),
                'school_year' => (string) ($subject->school_year ?: ''),
                'semester' => (string) ($subject->semester ?: ''),
            ];
        })->values();
    @endphp

    <div class="gs-filter-bar faculty-load-filter-bar" id="fclFilterBar">
        <div class="gs-filter-row">
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">School Year</span>
                <select class="gs-filter-select" id="clSchoolYear">
                    @forelse($schoolYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @empty
                        <option value="{{ $defaultSchoolYear }}">{{ $defaultSchoolYear }}</option>
                    @endforelse
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">Semester</span>
                <select class="gs-filter-select" id="clSemester">
                    <option value="">All</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester }}">{{ $semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gs-filter-group faculty-load-display-btn-wrap">
                <button type="button" class="gs-view-btn" id="clDisplayBtn">Display</button>
            </div>
        </div>
    </div>

    <div class="faculty-gs-detail-tools-row faculty-gs-hidden" id="fclDetailToolsRow">
        <div class="gs-filter-group faculty-gs-detail-search-wrap">
            <label class="gs-filter-label" for="classListDetailSearch">Search</label>
            <input type="text" id="classListDetailSearch" class="gs-filter-select" placeholder="Search Student ID / Name">
        </div>
        <div class="faculty-cl-detail-tools-actions">
            <button type="button" class="btn-faculty-download" id="classListPrintBtn">Print List</button>
        </div>
    </div>

    <h3 class="faculty-gs-school-year" id="clYearLabel">{{ $defaultSchoolYear }}</h3>

    <div id="classListSubjectView">
        <p class="faculty-section-header">Kindly select a subject to view Class List. You can only select one at a time.</p>

        <div class="faculty-table-wrap">
            <table class="faculty-table" id="classListTable">
                <thead>
                    <tr>
                        <th>View List</th>
                        <th>Subject Code</th>
                        <th>Subject Description</th>
                        <th>Units</th>
                        <th>Days</th>
                        <th>Time</th>
                        <th>Room No.</th>
                        <th>Yr&amp;Section</th>
                    </tr>
                </thead>
                <tbody id="classListBody"></tbody>
            </table>
        </div>

        <div class="rtp-pagination faculty-cl-pager faculty-gs-hidden" id="classListSubjectPager">
            <nav class="rtp-nav" aria-label="Class list subjects pagination">
                <div class="rtp-list" id="classListSubjectPagerList"></div>
            </nav>
        </div>
    </div>

    <div id="classListDetailView" class="faculty-gs-hidden">
        <div class="faculty-gs-detail-heading">
            <button type="button" class="gs-back-btn faculty-gs-back-inline" id="classListBackBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Back to List
            </button>
            <div class="faculty-detail-title" id="classListDetailTitle">Class List</div>
            <div class="faculty-detail-section" id="classListDetailSection"></div>
        </div>

        <div class="faculty-table-wrap">
            <table class="faculty-table" id="classListDetailTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Program / Yr / Block</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="classListDetailBody"></tbody>
            </table>
        </div>

        <div class="rtp-pagination faculty-cl-pager faculty-gs-hidden" id="classListDetailPager">
            <nav class="rtp-nav" aria-label="Class list students pagination">
                <div class="rtp-list" id="classListDetailPagerList"></div>
            </nav>
        </div>

    </div>

    <div id="classListData"
         data-subjects='@json($classListSubjects)'
         data-students='@json($subjectStudents)'>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/faculty-class-list.js') }}"></script>
@endpush
@endsection
