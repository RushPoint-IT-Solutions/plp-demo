@php
    $isActualSize = ($reportType ?? '') === 'actual-size';
    $schoolName = trim((string) config('app.school_name', 'University of Pasig City'));
    if ($schoolName === '') {
        $schoolName = 'University of Pasig City';
    }

    $courseGroups = collect($rows ?? [])->groupBy(function ($row) {
        $courseCode = trim((string) ($row['course_code'] ?? ''));
        $courseName = trim((string) ($row['course_name'] ?? ''));

        if ($courseCode !== '') {
            return $courseCode;
        }

        if ($courseName !== '') {
            return $courseName;
        }

        return 'N/A';
    });
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reportTitle }}</title>
    <link rel="icon" href="{{ asset('img/logobg.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
</head>
<body class="slot-monitoring-report-page report-{{ $reportType }}">
    <div class="sm-report-shell-viewport">
        <div class="pf-page sm-report-shell">
            <header class="sm-report-header{{ $isActualSize ? ' is-actual-size' : '' }}">
                <p class="sm-report-school">{{ $schoolName }}</p>
                <h1 class="sm-report-title">{{ $reportTitle }}</h1>
                <p class="sm-report-meta">SY {{ $selectedSchoolYear }} - {{ $selectedSemester }}</p>
            </header>

            <div class="student-table-wrapper sm-report-table-wrap">
                @if ($isActualSize)
                    <table class="student-table sm-report-table sm-report-table-actual-size">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Faculty Name</th>
                                <th>Total Number of Students Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($courseGroups as $courseGroupKey => $groupRows)
                                @php
                                    $groupRows = collect($groupRows)->values();
                                    $first = $groupRows->first();
                                    $courseDisplay = trim((string) ($first['course_name'] ?? ''));
                                    if ($courseDisplay === '') {
                                        $courseDisplay = trim((string) ($first['course_code'] ?? ''));
                                    }
                                    if ($courseDisplay === '') {
                                        $courseDisplay = 'N/A';
                                    }

                                    $totalLabel = trim((string) ($first['course_code'] ?? ''));
                                    if ($totalLabel === '') {
                                        $totalLabel = $courseGroupKey;
                                    }

                                    $groupTotal = $groupRows->sum(function ($row) {
                                        return (int) ($row['enrolled_slots'] ?? 0);
                                    });
                                @endphp

                                @foreach ($groupRows as $index => $row)
                                    <tr>
                                        <td class="sm-course-col">{{ $index === 0 ? $courseDisplay : '' }}</td>
                                        <td>{{ $row['section'] ?: '-' }}</td>
                                        <td>{{ $row['subject'] ?: '-' }}</td>
                                        <td>{{ $row['faculty_name'] ?: 'TBA' }}</td>
                                        <td class="sm-number-col">{{ (int) ($row['enrolled_slots'] ?? 0) }}</td>
                                    </tr>
                                @endforeach

                                <tr class="sm-report-total-row">
                                    <td colspan="4">Total {{ $totalLabel }}</td>
                                    <td class="sm-number-col">{{ $groupTotal }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="sm-empty-cell"><em>Empty</em></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="student-table sm-report-table sm-report-table-standard">
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Schedule</th>
                                <th>Total Slots</th>
                                <th>Slots Taken</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row['section'] ?: '-' }}</td>
                                    <td>{{ $row['subject'] ?: '-' }}</td>
                                    <td>{{ $row['schedule'] ?: '-' }}</td>
                                    <td class="sm-number-col">{{ (int) ($row['total_slots'] ?? 0) }}</td>
                                    <td class="sm-number-col">{{ (int) ($row['enrolled_slots'] ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="sm-empty-cell"><em>Empty</em></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>

            <p class="sm-report-generated">Generated: {{ $generatedAt->format('Y-m-d h:i A') }}</p>
        </div>
    </div>

    <script src="{{ mix('js/slot-monitoring-report.js') }}"></script>
</body>
</html>
