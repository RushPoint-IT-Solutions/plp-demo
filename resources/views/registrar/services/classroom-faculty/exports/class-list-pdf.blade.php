<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Class List Report - PLP</title>
    <style>
        @page { margin: 10mm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #334155;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 3px solid #006837;
            padding-bottom: 20px;
        }
        .university-name {
            font-size: 16pt;
            font-weight: 800;
            color: #006837;
            text-transform: uppercase;
            margin: 0;
        }
        .office-name {
            font-size: 10pt;
            font-weight: 700;
            color: #1e293b;
            margin-top: 2px;
        }
        .report-title {
            font-size: 12pt;
            font-weight: 800;
            color: #2d3748;
            margin-top: 10px;
        }
        .generated-at {
            font-size: 7pt;
            color: #64748b;
            text-align: right;
        }
        .filter-summary {
            background-color: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        .filter-label {
            font-size: 7pt;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
        }
        .filter-value {
            font-size: 9pt;
            font-weight: 700;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #006837;
            color: #ffffff;
            font-weight: 800;
            text-align: left;
            padding: 10px 8px;
            font-size: 8pt;
            text-transform: uppercase;
            border: 1px solid #006837;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .row-even {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer-signatory {
            margin-top: 40px;
            width: 100%;
        }
        .signatory-box {
            width: 250px;
            float: right;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: 800;
            font-size: 10pt;
        }
        .signature-title {
            font-size: 8pt;
            color: #64748b;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="border: none; padding: 0; width: 80px; vertical-align: middle;">
                <img src="{{ public_path('img/logobg.png') }}" style="width: 70px; height: auto;">
            </td>
            <td style="border: none; padding: 0; text-align: center; vertical-align: middle;">
                <div class="university-name">Pamantasan ng Lungsod ng Pasig</div>
                <div class="office-name">OFFICE OF THE UNIVERSITY REGISTRAR</div>
                <div class="report-title">STUDENT CLASS LIST REPORT</div>
            </td>
            <td style="border: none; padding: 0; width: 150px; text-align: right; vertical-align: top;">
                <div class="generated-at">
                    DATE: {{ date('M d, Y') }}<br>
                    TIME: {{ date('h:i A') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="filter-summary">
        <table style="width: 100%; border: none; margin-bottom: 0;">
            <tr>
                @if($selectedSubject)
                    <td style="border: none; padding: 0; width: 25%;">
                        <span class="filter-label">SECTION:</span><br>
                        <span class="filter-value">{{ $controller->sectionLabel($selectedSubject) }}</span>
                    </td>
                    <td style="border: none; padding: 0; width: 35%;">
                        <span class="filter-label">SUBJECT:</span><br>
                        <span class="filter-value">{{ $selectedSubject->code }} - {{ $selectedSubject->name }}</span>
                    </td>
                    <td style="border: none; padding: 0; width: 25%;">
                        <span class="filter-label">PROFESSOR:</span><br>
                        <span class="filter-value">{{ (string) optional($selectedSubject->facultyModel)->name ?: 'TBA' }}</span>
                    </td>
                    <td style="border: none; padding: 0; width: 15%; text-align: right;">
                        <span class="filter-label">ENROLLED:</span><br>
                        <span class="filter-value" style="color: #006837; font-size: 12pt;">{{ $sectionStudents->count() }}</span>
                    </td>
                @else
                    <td style="border: none; padding: 0; width: 30%;">
                        <span class="filter-label">SCHOOL YEAR:</span><br>
                        <span class="filter-value">{{ $state['selected_school_year'] ?: 'ALL YEARS' }}</span>
                    </td>
                    <td style="border: none; padding: 0; width: 30%;">
                        <span class="filter-label">SEMESTER:</span><br>
                        <span class="filter-value">{{ strtoupper($state['selected_semester'] ?: 'ALL SEMESTERS') }}</span>
                    </td>
                    <td style="border: none; padding: 0; width: 25%;">
                        <span class="filter-label">FILTER:</span><br>
                        <span class="filter-value">{{ $state['search'] ? 'SEARCH: "'.strtoupper($state['search']).'"' : 'NO SEARCH FILTER' }}</span>
                    </td>
                    <td style="border: none; padding: 0; width: 15%; text-align: right;">
                        <span class="filter-label">TOTAL LIST:</span><br>
                        <span class="filter-value" style="color: #006837; font-size: 12pt;">{{ $subjects->count() }}</span>
                    </td>
                @endif
            </tr>
        </table>
    </div>

    @if($selectedSubject)
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">#</th>
                    <th style="width: 15%;">STUDENT NO.</th>
                    <th style="width: 35%;">NAME</th>
                    <th style="width: 35%;">COURSE / PROGRAM</th>
                    <th style="width: 10%;" class="text-center">YEAR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sectionStudents as $index => $student)
                    <tr class="{{ $index % 2 === 0 ? '' : 'row-even' }}">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td style="font-weight: 700;">{{ $student->student_no }}</td>
                        <td style="font-weight: 700; color: #0f172a;">{{ strtoupper($student->name) }}</td>
                        <td>{{ optional($student->canonicalCourse)->name }}</td>
                        <td class="text-center">{{ optional($student->yearBlock)->label }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 30px; color: #94a3b8;">No enrolled students found for this section.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">#</th>
                    <th style="width: 15%;">SECTION</th>
                    <th style="width: 10%;">CODE</th>
                    <th style="width: 45%;">SUBJECT DESCRIPTION</th>
                    <th style="width: 25%;">SCHEDULE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $index => $subject)
                    <tr class="{{ $index % 2 === 0 ? '' : 'row-even' }}">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td style="font-weight: 700;">{{ $controller->sectionLabel($subject) }}</td>
                        <td style="font-weight: 700;">{{ $subject->code }}</td>
                        <td style="font-weight: 700; color: #0f172a;">{{ strtoupper($subject->name) }}</td>
                        <td style="font-size: 8pt;">{{ $controller->scheduleLabel($subject) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 30px; color: #94a3b8;">No subjects found matching your current filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer-signatory">
        <div class="signatory-box">
            <div class="signature-line">DR. RENATO E. SAHAGUN</div>
            <div class="signature-title">University Registrar</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div style="margin-top: 20px; font-size: 7pt; color: #94a3b8; text-align: center;">
        *** END OF REPORT - PAGE 1 OF 1 ***
    </div>
</body>
</html>
