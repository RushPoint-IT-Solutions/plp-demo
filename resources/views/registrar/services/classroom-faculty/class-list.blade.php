@extends('layouts.registrar')

@section('title', 'PLP - Class List')
@section('page-title', 'CLASS LIST')
@section('body-class', 'page-services-class-list')

@push('scripts')
    <script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
    <script src="{{ asset('js/registrar-class-list.js') }}?v={{ file_exists(public_path('js/registrar-class-list.js')) ? filemtime(public_path('js/registrar-class-list.js')) : time() }}"></script>
    <script src="{{ asset('js/reports-student-autocomplete.js') }}?v={{ file_exists(public_path('js/reports-student-autocomplete.js')) ? filemtime(public_path('js/reports-student-autocomplete.js')) : time() }}"></script>
    <script>
        var clAddStudentPoll = null;

        function openClAddStudentModal() {
            var modal = document.getElementById('clAddStudentModal');
            if (!modal) return;
            document.getElementById('clAddStudentSearch').value = '';
            document.getElementById('clAddStudentId').value = '';
            document.getElementById('clAddStudentSubmit').disabled = true;
            modal.style.display = 'flex';

            var idField = document.getElementById('clAddStudentId');
            var submitBtn = document.getElementById('clAddStudentSubmit');
            clearInterval(clAddStudentPoll);
            clAddStudentPoll = setInterval(function () {
                submitBtn.disabled = idField.value.trim() === '';
            }, 250);
        }

        function closeClAddStudentModal() {
            var modal = document.getElementById('clAddStudentModal');
            if (modal) modal.style.display = 'none';
            clearInterval(clAddStudentPoll);
        }
    </script>
@endpush

@push('styles')
<style>
    .cl-print-preview {
        display: flex;
        justify-content: center;
        padding: 18px 0 28px;
        background: #f3f4f6;
        overflow-x: auto;
    }
    .cl-print-sheet {
        width: 8.5in;
        min-height: 11in;
        background: #fff;
        color: #111;
        border: 2px solid #111;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .18);
        padding: .14in .2in .2in;
        font-family: "Times New Roman", Times, serif;
        font-size: 12px;
        line-height: 1.05;
    }
    .cl-print-school {
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .2px;
        margin-top: 4px;
    }
    .cl-print-address {
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        margin-top: 1px;
    }
    .cl-print-title {
        text-align: center;
        font-size: 17px;
        font-weight: 700;
        margin: 7px 0 3px;
    }
    .cl-print-meta {
        width: 100%;
        border-collapse: collapse;
        border-top: 2px solid #222;
        margin-bottom: 7px;
    }
    .cl-print-meta td {
        border: 0;
        padding: 2px 5px 1px;
        vertical-align: top;
        font-size: 12px;
    }
    .cl-print-meta .label {
        width: 90px;
        font-weight: 700;
    }
    .cl-print-meta .value {
        font-weight: 700;
    }
    .cl-print-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .cl-print-table th,
    .cl-print-table td {
        border: 1px solid #222;
        padding: 2px 5px;
        font-size: 12px;
        line-height: 1.0;
    }
    .cl-print-table th {
        text-align: center;
        font-weight: 700;
    }
    .cl-print-no { width: 44px; text-align: left; }
    .cl-print-student-no { width: 108px; text-align: center; }
    .cl-print-name { width: auto; }
    .cl-print-course { width: 114px; text-align: center; }
    .cl-print-year { width: 66px; text-align: center; }
    .cl-print-sex { width: 54px; text-align: center; }
    @media print {
        body { background: #fff; }
        body * { visibility: hidden; }
        .cl-print-preview, .cl-print-preview * { visibility: visible; }
        .cl-print-preview {
            position: absolute;
            inset: 0;
            display: block;
            padding: 0;
            background: #fff;
        }
        .cl-print-sheet {
            width: auto;
            min-height: auto;
            box-shadow: none;
            border: 2px solid #111;
            margin: 0;
        }
    }
</style>
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

    <form method="GET" action="{{ route('registrar.services.classroom-faculty.class-list') }}" class="sched-filter-bar at-top-row" id="clFilterForm" data-semester-map='@json($semesterMap)'>
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
        <a href="{{ route('registrar.services.classroom-faculty.class-list.export', array_merge(['format' => 'pdf'], $exportQuery)) }}" target="_blank" class="svc-btn-pdf">Print Class List (PDF)</a>
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
                <button type="button" class="pf-btn-new" onclick="openClAddStudentModal()">+ Add Student</button>
                <a href="{{ route('registrar.services.classroom-faculty.class-list.export', array_merge(['format' => 'pdf'], $exportQuery)) }}" target="_blank" class="svc-btn-pdf">Print Class List (PDF)</a>
                <a href="{{ route('registrar.services.classroom-faculty.class-list.export', array_merge(['format' => 'excel'], $exportQuery)) }}" class="svc-btn-excel">Print Class List (Excel)</a>
            </div>
        </div>

        <div class="cl-print-preview">
            @include('registrar.services.classroom-faculty.partials.class-list-print-sheet')
        </div>

        <div class="pf-modal-overlay" id="clAddStudentModal" style="display:none;">
            <div class="pf-modal-box" style="max-width:480px;">
                <div class="pf-modal-title">Add Student to Class List</div>
                <p style="font-size:.8rem;color:#666;margin:0 0 12px;">Search by student number or name, then select the student to add to this section.</p>
                <form action="{{ route('registrar.services.classroom-faculty.class-list.students.store', $selectedSubject->id) }}" method="POST" id="clAddStudentForm">
                    @csrf
                    <input type="hidden" name="school_year" value="{{ $selectedSchoolYear }}">
                    <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                    <input type="hidden" name="q" value="{{ $search }}">
                    <input type="hidden" name="student_id" id="clAddStudentId">

                    <label class="pf-modal-label">Student ID / Name</label>
                    <div class="rsa-wrap">
                        <input type="text" class="req-modal-input" id="clAddStudentSearch" placeholder="Type student no. or name" autocomplete="off"
                            data-student-autocomplete="reports"
                            data-student-id-target="clAddStudentId"
                            data-student-search-url="{{ route('registrar.services.classroom-faculty.class-list.students.search', $selectedSubject->id) }}">
                    </div>

                    <div class="pf-modal-actions" style="margin-top:14px;">
                        <button type="button" class="pf-modal-btn-cancel" onclick="closeClAddStudentModal()">Cancel</button>
                        <button type="submit" class="pf-modal-btn-save" id="clAddStudentSubmit" disabled>Add Student</button>
                    </div>
                </form>
            </div>
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
