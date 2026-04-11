@extends('layouts.faculty')

@section('title', 'PLP - Faculty Load')
@section('page-title', 'FACULTY LOAD')

@section('content')
<div class="faculty-load-wrap faculty-load-page">

    @php
        $schoolYears = collect($subjects ?? [])->pluck('school_year')->filter()->unique()->values();
        $defaultSchoolYear = $schoolYears->first() ?: '2025-2026';
        $semesters = collect($subjects ?? [])->pluck('semester')->filter()->unique()->values();
    @endphp

    <div class="gs-filter-bar faculty-load-filter-bar">
        <div class="gs-filter-row">
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">School Year</span>
                <select class="gs-filter-select" id="flSchoolYear">
                    @forelse($schoolYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @empty
                        <option value="{{ $defaultSchoolYear }}">{{ $defaultSchoolYear }}</option>
                    @endforelse
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">Semester</span>
                <select class="gs-filter-select" id="flSemester">
                    <option value="">All</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester }}">{{ $semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gs-filter-group faculty-load-display-btn-wrap">
                <button type="button" class="gs-view-btn" id="flDisplayBtn">Display</button>
            </div>
        </div>
    </div>

    <h3 class="faculty-gs-school-year" id="flYearLabel">{{ $defaultSchoolYear }}</h3>

    <div class="faculty-table-wrap">
        <table class="faculty-table" id="facultyLoadTable">
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Subject Description</th>
                    <th>Units</th>
                    <th>Days</th>
                    <th>Time</th>
                    <th>Room No.</th>
                    <th>Yr&amp;Section</th>
                </tr>
            </thead>
            <tbody id="facultyLoadBody">
                @forelse($subjects as $subject)
                <tr data-school-year="{{ $subject->school_year }}" data-semester="{{ $subject->semester }}">
                    <td class="td-code">{{ $subject->code }}</td>
                    <td>{{ $subject->name }}</td>
                    <td>{{ number_format($subject->units, 1) }}</td>
                    <td>{{ str_replace(',', ', ', $subject->days) }}</td>
                    <td>{{ $subject->formatted_time }}</td>
                    <td>{{ $subject->room }}</td>
                    <td>{{ $subject->year_section }}</td>
                </tr>
                @empty
                <tr class="faculty-load-empty-row"><td colspan="7" class="text-center text-muted py-4">No subjects found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="faculty-load-actions">
        <a href="{{ route('faculty.load.download') }}" class="btn-faculty-download">Download Schedule</a>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/faculty-load.js') }}?v={{ file_exists(public_path('js/faculty-load.js')) ? filemtime(public_path('js/faculty-load.js')) : time() }}"></script>
@endpush
