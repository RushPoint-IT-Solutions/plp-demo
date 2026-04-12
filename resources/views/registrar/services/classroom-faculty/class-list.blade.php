@extends('layouts.registrar')

@section('title', 'PLP - Class List')
@section('page-title', 'CLASS LIST')
@section('body-class', 'page-services-class-list')

@push('scripts')
    <script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
    <script src="{{ asset('js/registrar-class-list.js') }}?v={{ file_exists(public_path('js/registrar-class-list.js')) ? filemtime(public_path('js/registrar-class-list.js')) : time() }}"></script>
@endpush

@section('content')
<div class="pf-page cl-page" id="classListPage">
    @if (session('status'))
        <div class="alert alert-{{ session('status_type', 'success') }} cl-alert" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger cl-alert" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @php
        $exportQuery = $queryBase;
        if ($selectedSubject) {
            $exportQuery['subject_id'] = $selectedSubject->id;
        }

        $selectedSectionLabel = 'N/A';
        $selectedScheduleLabel = 'TBA';
        $selectedProfessorLabel = 'TBA';

        if ($selectedSubject) {
            $selectedSectionLabel = trim((string) optional($selectedSubject->canonicalCourse)->code . ' ' . (string) $selectedSubject->year_section);
            if ($selectedSectionLabel === '') {
                $selectedSectionLabel = 'N/A';
            }

            $scheduleParts = [];
            $selectedDays = strtoupper(str_replace(',', '/', trim((string) $selectedSubject->days)));
            $selectedTime = trim((string) $selectedSubject->formatted_time);
            $selectedRoom = trim((string) $selectedSubject->room);

            if ($selectedDays !== '') {
                $scheduleParts[] = $selectedDays;
            }

            if ($selectedTime !== '') {
                $scheduleParts[] = $selectedTime;
            }

            if ($selectedRoom !== '') {
                $scheduleParts[] = 'Room#' . $selectedRoom;
            }

            if (count($scheduleParts)) {
                $selectedScheduleLabel = implode(' | ', $scheduleParts);
            }

            $selectedProfessorLabel = trim((string) optional($selectedSubject->facultyModel)->name);
            if ($selectedProfessorLabel === '') {
                $selectedProfessorLabel = 'TBA';
            }
        }
    @endphp

    <form method="GET" action="{{ route('registrar.services.classroom-faculty.class-list') }}" class="sched-filter-bar at-top-row" id="clFilterForm">
        @if($selectedSubject)
            <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
        @endif

        <div class="at-search-block at-search-card">
            <span class="app-filter-label">Search</span>
            @include('registrar.components.search-bar', [
                'id' => 'clSearchInput',
                'name' => 'q',
                'value' => $search,
                'placeholder' => 'Search Course / Section / Subject / Faculty / Student',
                'containerClass' => 'at-search-wrap',
                'inputClass' => 'js-cl-auto-submit-search',
                'inputAttributes' => [
                    'data-cl-auto-submit-search' => '1',
                ],
            ])
        </div>

        <div class="at-config-card">
            <div class="at-config-title">System Configuration</div>
            <div class="at-config-grid">
                <div class="at-config-item">
                    <span class="at-config-inline-label">School Year:</span>
                    @include('registrar.components.listbox-select', [
                        'id' => 'clSchoolYear',
                        'name' => 'school_year',
                        'options' => $schoolYearOptions,
                        'selected' => $selectedSchoolYear,
                        'placeholder' => 'All School Years',
                    ])
                </div>

                <div class="at-config-item">
                    <span class="at-config-inline-label">Semester:</span>
                    @include('registrar.components.listbox-select', [
                        'id' => 'clSemester',
                        'name' => 'semester',
                        'options' => $semesterOptions,
                        'selected' => $selectedSemester,
                        'placeholder' => 'All Semesters',
                    ])
                </div>

                <div class="at-config-action">
                    <button type="submit" class="pf-btn-new at-btn-set">Set</button>
                </div>
            </div>
        </div>
    </form>

    @if(!$selectedSubject)
    <div class="svc-actions-row">
        <a href="{{ route('registrar.services.classroom-faculty.class-list.export', array_merge(['format' => 'pdf'], $exportQuery)) }}" class="svc-btn-pdf">Print Class List (PDF)</a>
        <a href="{{ route('registrar.services.classroom-faculty.class-list.export', array_merge(['format' => 'excel'], $exportQuery)) }}" class="svc-btn-excel">Print Class List (Excel)</a>
    </div>
    @endif

    @if($selectedSubject)
        <div class="svc-selected-info">
            <div><strong>Section:</strong> {{ $selectedSectionLabel }}</div>
            <div><strong>Subject:</strong> {{ $selectedSubject->code }} ({{ strtoupper($selectedSubject->name) }})</div>
            <div><strong>Schedule:</strong> {{ $selectedScheduleLabel }}</div>
            <div><strong>Professor:</strong> {{ strtoupper($selectedProfessorLabel) }}</div>
        </div>

        <div class="cl-back-row detail-actions-row">
            <button type="button" class="gs-back-btn" onclick="window.location.href='{{ route('registrar.services.classroom-faculty.class-list', $queryBase) }}'">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Back to Subject List
            </button>
            <div class="cl-print-group">
                <a href="{{ route('registrar.services.classroom-faculty.class-list.export', array_merge(['format' => 'pdf'], $exportQuery)) }}" class="svc-btn-pdf">Print Class List (PDF)</a>
                <a href="{{ route('registrar.services.classroom-faculty.class-list.export', array_merge(['format' => 'excel'], $exportQuery)) }}" class="svc-btn-excel">Print Class List (Excel)</a>
            </div>
        </div>

        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table svc-table" id="clSectionStudentsTable" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sectionStudents as $index => $student)
                        <tr>
                            <td>{{ ($sectionStudents->firstItem() ?? 0) + $index }}</td>
                            <td>{{ $student->student_no }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ optional($student->canonicalCourse)->name ?: (optional($student->canonicalCourse)->code ?: 'N/A') }}</td>
                            <td>{{ optional($student->yearBlock)->label ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No students found for this section.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="svc-table-tfoot">
                    <tr>
                        <td colspan="5">
                            <div class="svc-table-stats">
                                Total Students: <strong>{{ $sectionStudents ? $sectionStudents->total() : 0 }}</strong>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="app-table-pager cl-pagination">
            {{ $sectionStudents->links() }}
        </div>
    @else
        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table svc-table" id="clSubjectTable" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Section</th>
                        <th>Subject Code</th>
                        <th>Description</th>
                        <th>Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classRows as $index => $subject)
                        @php
                            $sectionLabel = trim((string) optional($subject->canonicalCourse)->code . ' ' . (string) $subject->year_section);
                            $scheduleParts = [];
                            $days = strtoupper(str_replace(',', '/', trim((string) $subject->days)));
                            $time = trim((string) $subject->formatted_time);
                            $room = trim((string) $subject->room);

                            if ($days !== '') {
                                $scheduleParts[] = $days;
                            }

                            if ($time !== '') {
                                $scheduleParts[] = $time;
                            }

                            if ($room !== '') {
                                $scheduleParts[] = 'Room#' . $room;
                            }

                            $scheduleLabel = count($scheduleParts) ? implode(' | ', $scheduleParts) : 'TBA';
                            $subjectQuery = array_merge($queryBase, ['subject_id' => $subject->id]);
                        @endphp
                        <tr>
                            <td>{{ ($classRows->firstItem() ?? 0) + $index }}</td>
                            <td>
                                <a href="{{ route('registrar.services.classroom-faculty.class-list', $subjectQuery) }}" class="svc-link">{{ $sectionLabel !== '' ? $sectionLabel : 'N/A' }}</a>
                            </td>
                            <td>{{ $subject->code }}</td>
                            <td>{{ $subject->name }}</td>
                            <td>{{ $scheduleLabel }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No class list data found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="svc-table-tfoot">
                    <tr>
                        <td colspan="5">
                            <div class="svc-table-stats">
                                Total Subjects: <strong>{{ $classRows->total() }}</strong>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="app-table-pager cl-pagination">
            {{ $classRows->links() }}
        </div>
    @endif
</div>
@endsection
