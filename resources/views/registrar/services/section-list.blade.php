@extends('layouts.registrar')

@section('title', 'PLP - Section List')
@section('page-title', 'SECTION LIST')
@section('body-class', 'page-services-section-list')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.min.css">
<style>
    .at-config-item .chosen-container { width: 100% !important; font-size: 0.85rem; }
    .at-config-item .chosen-container-single .chosen-single {
        height: 36px;
        line-height: 34px;
        padding: 0 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        box-shadow: none;
        background: #fff;
    }
    .at-config-item .chosen-container-single .chosen-single div b {
        background-position: 0 4px;
    }
    .at-config-item .chosen-container-active.chosen-with-drop .chosen-single {
        border-color: #15803d;
    }
    .at-config-item .chosen-container .chosen-results li.highlighted {
        background-color: #15803d;
    }
    .at-config-item .chosen-container-single .chosen-search input[type="text"] {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<div class="pf-page cl-page" id="sectionListPage">
    @php
        $baseQuery = array_filter([
            'school_year' => $state['school_year'],
            'semester' => $state['semester'],
            'course_id' => $state['course_id'] ?: null,
            'year_block_id' => $state['year_block_id'] ?: null,
            'student_search' => $state['student_search'],
        ], function ($value) {
            return !($value === null || $value === '');
        });
    @endphp

    <form method="GET" action="{{ route('registrar.services.section-list') }}" class="sched-filter-bar at-top-row">
        <div class="at-search-block at-search-card">
            <span class="app-filter-label">Student Search</span>
            @include('registrar.components.search-bar', [
                'id' => 'slStudentSearch',
                'name' => 'student_search',
                'value' => $state['student_search'],
                'placeholder' => 'Search Student No. / Name',
                'containerClass' => 'at-search-wrap',
            ])
        </div>

        <div class="at-config-card">
            <div class="at-config-title">Section Filter</div>
            <div class="at-config-grid">
                <div class="at-config-item">
                    <span class="at-config-inline-label">School Year:</span>
                    <select name="school_year" class="form-control sl-chosen">
                        <option value="">All School Years</option>
                        @foreach($schoolYearOptions as $schoolYear)
                            <option value="{{ $schoolYear }}" {{ $state['school_year'] === (string) $schoolYear ? 'selected' : '' }}>{{ $schoolYear }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="at-config-item">
                    <span class="at-config-inline-label">Semester:</span>
                    <select name="semester" class="form-control sl-chosen">
                        <option value="">All Semesters</option>
                        @foreach($semesterOptions as $semester)
                            <option value="{{ $semester }}" {{ $state['semester'] === (string) $semester ? 'selected' : '' }}>{{ $semester }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="at-config-item">
                    <span class="at-config-inline-label">Course:</span>
                    <select name="course_id" class="form-control sl-chosen">
                        <option value="">All Courses</option>
                        @foreach($courseOptions as $course)
                            <option value="{{ $course->id }}" {{ (int) $state['course_id'] === (int) $course->id ? 'selected' : '' }}>
                                {{ trim((string) $course->code . ' - ' . (string) $course->name, ' -') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="at-config-item">
                    <span class="at-config-inline-label">Year Level:</span>
                    <select name="year_block_id" class="form-control sl-chosen">
                        <option value="">All Year Levels</option>
                        @foreach($yearBlockOptions as $yearBlock)
                            <option value="{{ $yearBlock->id }}" {{ (int) $state['year_block_id'] === (int) $yearBlock->id ? 'selected' : '' }}>{{ $yearBlock->label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="at-config-item">
                    <span class="at-config-inline-label">Section:</span>
                    <select name="section" class="form-control sl-chosen">
                        <option value="">Choose Section</option>
                        @foreach($sectionOptions as $section)
                            <option value="{{ $section }}" {{ $state['section'] === (string) $section ? 'selected' : '' }}>{{ $section }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="at-config-action">
                    <button type="submit" class="pf-btn-new at-btn-set">Set</button>
                    <a href="{{ route('registrar.services.section-list') }}" class="sr-btn-clear" style="margin-left:8px;">Clear</a>
                </div>
            </div>
        </div>
    </form>

    @if($selectedSection)
        <div class="svc-selected-info">
            <div><strong>Course:</strong> {{ trim((string) $selectedSection->course_code . ' - ' . (string) $selectedSection->course_name, ' -') ?: 'N/A' }}</div>
            <div><strong>Year Level:</strong> {{ $selectedSection->year_level ?: 'N/A' }}</div>
            <div><strong>Section:</strong> {{ $selectedSection->section }}</div>
            <div><strong>Term:</strong> {{ trim((string) $selectedSection->school_year . ' ' . (string) $selectedSection->semester) ?: 'N/A' }}</div>
        </div>

        <div class="cl-back-row detail-actions-row">
            <button type="button" class="gs-back-btn" onclick="window.location.href='{{ route('registrar.services.section-list', array_diff_key($baseQuery, ['student_search' => true])) }}'">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Back to Section List
            </button>
        </div>

        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table svc-table" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                        <tr>
                            <td>{{ ($students->firstItem() ?? 0) + $index }}</td>
                            <td>{{ $student->student_no }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ optional($student->canonicalCourse)->code ?: (optional($student->canonicalCourse)->name ?: 'N/A') }}</td>
                            <td>{{ optional($student->yearBlock)->label ?: 'N/A' }}</td>
                            <td>{{ $student->is_withdrawn ? 'Withdrawn' : 'Enrolled' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No students found for this section.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="svc-table-tfoot">
                    <tr>
                        <td colspan="6">
                            <div class="svc-table-stats">
                                Total Students: <strong>{{ $students ? $students->total() : 0 }}</strong>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="app-table-pager cl-pagination">
            {{ $students->links() }}
        </div>
    @else
        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table svc-table" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Section</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Students</th>
                        <th>Subjects</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sections as $index => $section)
                        @php
                            $sectionQuery = array_filter(array_merge($baseQuery, [
                                'course_id' => (int) $section->course_id ?: null,
                                'year_block_id' => (int) $section->year_block_id ?: null,
                                'section' => $section->section,
                                'school_year' => $section->school_year ?: ($state['school_year'] ?: null),
                                'semester' => $section->semester ?: ($state['semester'] ?: null),
                            ]), function ($value) {
                                return !($value === null || $value === '');
                            });
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ trim((string) $section->course_code . ' - ' . (string) $section->course_name, ' -') ?: 'N/A' }}</td>
                            <td>{{ $section->year_level ?: 'N/A' }}</td>
                            <td><a href="{{ route('registrar.services.section-list', $sectionQuery) }}" class="svc-link">{{ $section->section }}</a></td>
                            <td>{{ $section->school_year ?: 'N/A' }}</td>
                            <td>{{ $section->semester ?: 'N/A' }}</td>
                            <td>{{ (int) $section->student_count }}</td>
                            <td>{{ (int) $section->subject_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No sections found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="svc-table-tfoot">
                    <tr>
                        <td colspan="8">
                            <div class="svc-table-stats">
                                Total Sections: <strong>{{ $sections->count() }}</strong>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.jquery.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.chosen) {
            window.jQuery('.sl-chosen').chosen({
                width: '100%',
                search_contains: true,
                disable_search_threshold: 6,
            });
        }
    });
</script>
@endpush
