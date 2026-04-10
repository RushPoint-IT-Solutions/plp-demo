@extends('layouts.registrar')

@section('title', 'PLP - Faculty Loads')
@section('page-title', 'FACULTY LOADS')
@section('body-class', 'page-services-faculty-loads')

@push('scripts')
    <script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
    <script src="{{ asset('js/registrar-faculty-loads.js') }}?v={{ file_exists(public_path('js/registrar-faculty-loads.js')) ? filemtime(public_path('js/registrar-faculty-loads.js')) : time() }}"></script>
@endpush

@section('content')
<div class="rfl-wrap">

    @if (session('status'))
        <div class="alert alert-{{ session('status_type', 'success') }} rfl-alert" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rfl-alert" role="alert">
            Please check the form and try again.
        </div>
    @endif

    <div class="rfl-tabs">
        <a class="rfl-tab {{ $tab === 'load' ? 'active' : '' }}" href="{{ route('registrar.services.classroom-faculty.faculty-loads.show', ['faculty' => $faculty->id, 'tab' => 'load', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}">
            Faculty Load
        </a>
        <a class="rfl-tab {{ $tab === 'loading' ? 'active' : '' }}" href="{{ route('registrar.services.classroom-faculty.faculty-loads.show', ['faculty' => $faculty->id, 'tab' => 'loading', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}">
            Faculty Loading
        </a>
    </div>

    <div class="rfl-back-row">
        <a href="{{ route('registrar.services.classroom-faculty.faculty-loads.index') }}" class="svc-link">Back to Faculty List</a>
    </div>

    <form method="GET" action="{{ route('registrar.services.classroom-faculty.faculty-loads.show', $faculty->id) }}" class="rfl-filters">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <div class="rfl-filter">
            <div class="app-filter-label">School Year:</div>
            <div class="rfl-select">
                @include('registrar.components.listbox-select', [
                    'id' => 'rflSchoolYear',
                    'name' => 'school_year',
                    'options' => $schoolYearOptions,
                    'selected' => $selectedSchoolYear,
                    'placeholder' => 'School Year',
                ])
            </div>
        </div>

        <div class="rfl-filter">
            <div class="app-filter-label">Term:</div>
            <div class="rfl-select">
                @include('registrar.components.listbox-select', [
                    'id' => 'rflSemester',
                    'name' => 'semester',
                    'options' => $semesterOptions,
                    'selected' => $selectedSemester,
                    'placeholder' => 'Term',
                ])
            </div>
        </div>

        <button class="btn btn-success rfl-set-btn" type="submit">Set</button>
    </form>

    <div class="rfl-faculty-pill">
        <span class="rfl-faculty-name">{{ strtoupper($faculty->name) }}</span>
        <span class="rfl-faculty-code">({{ $faculty->code }})</span>
    </div>

    @if ($tab === 'load')
        <div class="rfl-load-card">
            @forelse($groupedSchedule as $courseName => $years)
                <div class="rfl-course-title">{{ strtoupper($courseName) }}</div>

                <div class="rfl-schedule-box">
                    @foreach($years as $yearLabel => $subjectsByKey)
                        <div class="rfl-year-title">{{ $yearLabel }}</div>

                        @foreach($subjectsByKey as $subjectKey => $subjectRows)
                            @php
                                $first = $subjectRows->first();
                            @endphp

                            <div class="rfl-subject-line">• {{ strtoupper($first->name) }} ( {{ $first->code }} )</div>
                            @foreach($subjectRows as $s)
                                @php
                                    $days = strtoupper(str_replace([',', ' '], ['/', ''], (string) $s->days));
                                    $start = str_replace(' ', '', (string) $s->time_start);
                                    $end = str_replace(' ', '', (string) $s->time_end);
                                    $section = trim((string) $s->year_section);
                                    $sectionText = $section !== '' ? (' ' . $section) : '';
                                @endphp
                                <div class="rfl-subject-schedule">– {{ $days }} | {{ $start }}-{{ $end }} | Room#{{ $s->room }} :{{ $sectionText }}</div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
            @empty
                <div class="text-muted">No faculty load found for the selected term.</div>
            @endforelse
        </div>
    @endif

    @if ($tab === 'loading')
        <div class="rfl-loading-wrap">

            <div class="rfl-loading-tools">
                <form method="GET" action="{{ route('registrar.services.classroom-faculty.faculty-loads.show', $faculty->id) }}" class="rfl-search">
                    <input type="hidden" name="tab" value="loading">
                    <input type="hidden" name="school_year" value="{{ $selectedSchoolYear }}">
                    <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                    <div class="app-filter-label">Search</div>
                    @include('registrar.components.search-bar', [
                        'id' => 'rflLoadingSearch',
                        'name' => 'loading_q',
                        'value' => $loadingSearch,
                        'placeholder' => 'Search Subject Code / Description / Section',
                        'containerClass' => 'rfl-search-form',
                        'inputClass' => 'js-rfl-auto-submit-search',
                        'inputAttributes' => [
                            'data-rfl-auto-submit-search' => '1',
                        ],
                    ])
                </form>

                <div class="rfl-loading-header">
                    <div class="rfl-loading-cols">SUBJECT CODE | DESCRIPTION | LEC | LAB | UNITS | SECTION | SCHEDULE</div>
                </div>
            </div>

            <form method="POST" action="{{ route('registrar.services.classroom-faculty.faculty-loads.assign', $faculty->id) }}" class="rfl-assign-form">
                @csrf
                <input type="hidden" name="school_year" value="{{ $selectedSchoolYear }}">
                <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                <input type="hidden" name="loading_q" value="{{ $loadingSearch }}">

                <div class="rfl-assign-top">
                    <div class="rfl-subject-select">
                        @include('registrar.components.listbox-select', [
                            'id' => 'rflSubjectSelect',
                            'name' => 'subject_id',
                            'options' => $availableSubjectOptions,
                            'selected' => (string) old('subject_id', ''),
                            'placeholder' => count($availableSubjectOptions) ? 'list of available subjects' : 'No available subjects',
                        ])
                    </div>

                    <button type="submit" class="btn btn-secondary rfl-add-btn" {{ count($availableSubjectOptions) ? '' : 'disabled' }}>Add Subject</button>
                </div>

                @if($availableSubjectsHasMore)
                    <div class="text-muted small mt-1">Showing first 200 matching subjects. Narrow the search to find more options.</div>
                @endif

                <div class="rfl-assign-options">
                    <div class="rfl-load-type-group" role="radiogroup" aria-label="Load Type">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="load_type" id="ltRegular" value="Regular" {{ old('load_type', 'Regular') === 'Regular' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ltRegular">Regular</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="load_type" id="ltPart" value="Part-time" {{ old('load_type') === 'Part-time' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ltPart">Part-time</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="load_type" id="ltTemp" value="Temporary Substitution" {{ old('load_type') === 'Temporary Substitution' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ltTemp">Temporary Substitution</label>
                        </div>
                    </div>

                    <div class="rfl-metric-group">
                        <div class="rfl-num-wrap">
                            <label class="rfl-num-label">Credited Tuition Units:</label>
                            <input type="number" step="0.01" min="0" name="credited_tuition_units" class="form-control rfl-num" placeholder="Units" value="{{ old('credited_tuition_units') }}">
                        </div>

                        <div class="rfl-num-wrap">
                            <label class="rfl-num-label">Load Hours:</label>
                            <input type="number" step="0.01" min="0" name="load_hours" class="form-control rfl-num" placeholder="Units" value="{{ old('load_hours') }}">
                        </div>
                    </div>
                </div>
            </form>

            <div class="app-table-wrap rfl-assigned-table">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Description</th>
                            <th style="width:70px">Lec</th>
                            <th style="width:70px">Lab</th>
                            <th style="width:70px">Units</th>
                            <th style="width:120px">Section</th>
                            <th>Schedule</th>
                            <th style="width:120px">Type</th>
                            <th style="width:120px">Added by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedSubjects as $s)
                            <tr>
                                <td class="td-code">{{ $s->code }}</td>
                                <td>{{ $s->name }}</td>
                                <td>{{ (int) ($s->lec ?? 0) }}</td>
                                <td>{{ (int) ($s->lab ?? 0) }}</td>
                                <td>{{ number_format($s->units, 1) }}</td>
                                <td>{{ $s->year_section }}</td>
                                <td>{{ strtoupper($s->days) }} {{ $s->formatted_time }} / {{ $s->room }}</td>
                                <td>{{ $s->load_type ?? '—' }}</td>
                                <td>{{ $s->added_by ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No subjects assigned.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="rfl-totals-row">
                            <td></td>
                            <td></td>
                            <td class="rfl-total-cell" style="color:#006837 !important;background:#f8fcf9 !important;text-align:left !important;font-weight:700 !important;">{{ (int) ($totals['lec'] ?? 0) }}</td>
                            <td class="rfl-total-cell" style="color:#006837 !important;background:#f8fcf9 !important;text-align:left !important;font-weight:700 !important;">{{ (int) ($totals['lab'] ?? 0) }}</td>
                            @php
                                $unitsTotal = (float) ($totals['units'] ?? 0);
                                $unitsDisplay = (floor($unitsTotal) == $unitsTotal)
                                    ? (string) (int) $unitsTotal
                                    : number_format($unitsTotal, 1);
                            @endphp
                            <td class="rfl-total-cell" style="color:#006837 !important;background:#f8fcf9 !important;text-align:left !important;font-weight:700 !important;">{{ $unitsDisplay }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="app-table-pager rfl-pagination">
                {{ $assignedSubjects->links() }}
            </div>

            <div class="rfl-loading-header rfl-schedule-table-title">FACULTY SCHEDULE</div>
            @php
                $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $scheduleByDay = array_fill_keys($weekDays, []);

                $extractDays = function ($rawDays) {
                    $raw = strtoupper((string) $rawDays);
                    $days = [];

                    $patternMap = [
                        'Monday' => '/MON(DAY)?/',
                        'Tuesday' => '/TUE(SDAY)?/',
                        'Wednesday' => '/WED(NESDAY)?/',
                        'Thursday' => '/THU(RSDAY)?/',
                        'Friday' => '/FRI(DAY)?/',
                        'Saturday' => '/SAT(URDAY)?/',
                        'Sunday' => '/SUN(DAY)?/',
                    ];

                    foreach ($patternMap as $dayName => $pattern) {
                        if (preg_match($pattern, $raw)) {
                            $days[] = $dayName;
                        }
                    }

                    if (!empty($days)) {
                        return array_values(array_unique($days));
                    }

                    $compact = preg_replace('/[^A-Z]/', '', $raw);
                    $i = 0;
                    while ($i < strlen($compact)) {
                        $next3 = substr($compact, $i, 3);
                        $next2 = substr($compact, $i, 2);
                        $next1 = substr($compact, $i, 1);

                        if ($next3 === 'THU') {
                            $days[] = 'Thursday';
                            $i += 3;
                            continue;
                        }
                        if ($next2 === 'TH') {
                            $days[] = 'Thursday';
                            $i += 2;
                            continue;
                        }
                        if ($next1 === 'M') {
                            $days[] = 'Monday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'T') {
                            $days[] = 'Tuesday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'W') {
                            $days[] = 'Wednesday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'R') {
                            $days[] = 'Thursday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'F') {
                            $days[] = 'Friday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'S') {
                            $days[] = 'Saturday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'U') {
                            $days[] = 'Sunday';
                            $i += 1;
                            continue;
                        }

                        $i += 1;
                    }

                    return array_values(array_unique($days));
                };

                foreach ($assignedSubjectsForSchedule as $s) {
                    $mappedDays = $extractDays($s->days ?? '');
                    $courseCode = trim((string) optional($s->canonicalCourse)->code);
                    $section = trim(($courseCode . ' ' . ($s->year_section ?? '')));
                    $entry = [
                        'time' => strtoupper((string) ($s->formatted_time ?? '')),
                        'code' => strtoupper((string) ($s->code ?? '')),
                        'section' => strtoupper($section),
                        'room' => strtoupper((string) ($s->room ?? '')),
                        'sort' => strtotime((string) ($s->time_start ?? '')) ?: 0,
                    ];

                    foreach ($mappedDays as $d) {
                        if (isset($scheduleByDay[$d])) {
                            $scheduleByDay[$d][] = $entry;
                        }
                    }
                }

                foreach ($scheduleByDay as $d => $entries) {
                    usort($entries, function ($a, $b) {
                        return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0);
                    });
                    $scheduleByDay[$d] = $entries;
                }
            @endphp

            <div class="rfl-weekly-scroll so-weekly-scroll">
                <div class="rfl-weekly-board so-weekly-grid">
                    @foreach($weekDays as $dayName)
                        <div class="rfl-weekly-col so-weekly-col">
                            <div class="rfl-weekly-day so-weekly-day">{{ strtoupper($dayName) }}</div>
                            <div class="rfl-weekly-body so-weekly-body">
                                @forelse($scheduleByDay[$dayName] as $entry)
                                    <div class="rfl-weekly-card so-weekly-card">
                                        <div class="rfl-weekly-time so-weekly-time">{{ $entry['time'] }}</div>
                                        <div class="rfl-weekly-code so-weekly-code">{{ $entry['code'] }}</div>
                                        <div class="rfl-weekly-section so-weekly-section">{{ $entry['section'] !== '' ? $entry['section'] : '—' }}</div>
                                        <div class="rfl-weekly-room so-weekly-room">{{ $entry['room'] !== '' ? $entry['room'] : 'TBA' }}</div>
                                    </div>
                                @empty
                                    <div class="rfl-weekly-empty so-weekly-empty"></div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
