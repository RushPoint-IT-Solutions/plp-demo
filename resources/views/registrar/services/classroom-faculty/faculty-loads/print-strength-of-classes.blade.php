@php($schoolName = trim((string) config('app.school_name', 'University of Pasig City')) ?: 'University of Pasig City')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Strength of Classes - {{ $faculty->code }}</title>
    <link rel="icon" href="{{ asset('img/logobg.png') }}" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <link rel="stylesheet" href="{{ mix('css/registrar-faculty-loads.css') }}">
</head>
<body class="rfl-strength-print-page">
    <div class="rfl-strength-toolbar d-print-none">
        <div class="d-flex justify-content-end gap-2">
            <a
                href="{{ route('registrar.services.classroom-faculty.faculty-loads.show', ['faculty' => $faculty->id, 'tab' => 'loading', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}"
                class="btn btn-outline-secondary btn-sm"
            >
                Back to Faculty Loads
            </a>
            <button type="button" class="btn btn-success btn-sm" data-rfl-print-trigger>Print</button>
        </div>
    </div>

    <div class="a4-wrapper rfl-strength-sheet">
        <div class="rfl-strength-header text-center">
            <div class="rfl-strength-school">{{ $schoolName }}</div>
            <div class="rfl-strength-title">REPORT ON STRENGTH OF CLASSES</div>
            <div class="rfl-strength-subtitle">{{ $termLabel }}</div>
        </div>

        <div class="rfl-strength-meta-row">
            <div class="rfl-strength-meta-left">
                <div class="rfl-strength-line-row">
                    <span class="rfl-strength-line-label">NAME OF INSTRUCTOR:</span>
                    <span class="rfl-strength-line-fill">{{ strtoupper($faculty->name) }}</span>
                </div>
                <div class="rfl-strength-line-row">
                    <span class="rfl-strength-line-label">Faculty ID:</span>
                    <span class="rfl-strength-line-fill">{{ strtoupper($faculty->code) }}</span>
                </div>
            </div>

            <div class="rfl-strength-meta-right">
                <div class="rfl-strength-line-row">
                    <span class="rfl-strength-line-label">CLASSIFICATION:</span>
                    <span class="rfl-strength-line-fill">{{ strtoupper($classification) }}</span>
                </div>
                <div class="rfl-strength-line-row">
                    <span class="rfl-strength-line-label">Verified By:</span>
                    <span class="rfl-strength-line-fill"></span>
                    <span class="rfl-strength-line-label">Date:</span>
                    <span class="rfl-strength-line-fill">{{ $generatedAt->format('m/d/Y') }}</span>
                </div>
            </div>
        </div>

        <div class="student-table-wrapper rfl-strength-table-wrap">
            <table class="student-table rfl-strength-table">
                <thead>
                    <tr>
                        <th rowspan="2">Subject(s)</th>
                        <th rowspan="2">Subject Code</th>
                        <th rowspan="2">Section</th>
                        <th rowspan="2">Days</th>
                        <th rowspan="2">Time</th>
                        <th rowspan="2">Room</th>
                        <th rowspan="2">Units</th>
                        <th colspan="2">Hours / Week</th>
                        <th rowspan="2">Total Hours</th>
                        <th rowspan="2">No. of Students</th>
                        <th rowspan="2">Campus</th>
                    </tr>
                    <tr>
                        <th>LEC</th>
                        <th>LAB</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ strtoupper($row['subject']) }}</td>
                            <td>{{ strtoupper($row['code']) }}</td>
                            <td>{{ $row['section'] !== '' ? $row['section'] : '—' }}</td>
                            <td>{{ $row['days'] !== '' ? $row['days'] : '—' }}</td>
                            <td>{{ $row['time'] !== '' ? $row['time'] : '—' }}</td>
                            <td>{{ $row['room'] !== '' ? $row['room'] : '—' }}</td>
                            <td>{{ number_format((float) $row['units'], 1) }}</td>
                            <td>{{ (int) $row['lec'] }}</td>
                            <td>{{ (int) $row['lab'] }}</td>
                            <td>{{ number_format((float) $row['total_hours'], 2) }}</td>
                            <td>{{ (int) $row['students'] }}</td>
                            <td>{{ strtoupper($row['campus']) }}</td>
                        </tr>
                    @endforeach

                    @for($i = 0; $i < $blankRows; $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="rfl-strength-total-label">TOTAL</td>
                        <td>{{ (int) ($totals['lec'] ?? 0) }}</td>
                        <td>{{ (int) ($totals['lab'] ?? 0) }}</td>
                        <td>{{ number_format((float) ($totals['total_hours'] ?? 0), 2) }}</td>
                        <td>{{ (int) ($totals['students'] ?? 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="rfl-strength-signatures">
            <div class="rfl-strength-sign-block">
                <div class="rfl-strength-sign-label">APPROVED BY:</div>
                <div class="rfl-strength-sign-line"></div>
                <div class="rfl-strength-sign-role">DEAN</div>
                <div class="rfl-strength-sign-date">Date/Time</div>
            </div>

            <div class="rfl-strength-sign-block">
                <div class="rfl-strength-sign-label">VERIFIED BY:</div>
                <div class="rfl-strength-sign-line"></div>
                <div class="rfl-strength-sign-role">REGISTRAR'S OFFICE</div>
                <div class="rfl-strength-sign-date">Date/Time</div>
            </div>

            <div class="rfl-strength-sign-block">
                <div class="rfl-strength-sign-label">RECEIVED BY:</div>
                <div class="rfl-strength-sign-line"></div>
                <div class="rfl-strength-sign-role">REGISTRAR'S OFFICE</div>
                <div class="rfl-strength-sign-date">Date/Time</div>
            </div>

            <div class="rfl-strength-sign-block rfl-strength-sign-block--right">
                <div class="rfl-strength-sign-line"></div>
                <div class="rfl-strength-sign-role">SIGNATURE OF INSTRUCTOR</div>
            </div>
        </div>

        <div class="rfl-strength-footer-note">FO-REG-005; Revision 07; February 20, 2014</div>
    </div>

    <script src="{{ asset('js/registrar-faculty-loads.js') }}?v={{ file_exists(public_path('js/registrar-faculty-loads.js')) ? filemtime(public_path('js/registrar-faculty-loads.js')) : time() }}"></script>
</body>
</html>
