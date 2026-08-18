<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Batch TOR - PLP</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 10pt; color: #000; margin: 0; }
        .bp-toolbar { padding: 10px 14px; background: #f3f4f6; display: flex; justify-content: space-between; align-items: center; font-family: Arial, sans-serif; }
        .bp-toolbar button { border: 0; border-radius: 6px; background: #146c43; color: #fff; font-weight: 700; padding: 8px 16px; cursor: pointer; }
        .tor-sheet { max-width: 960px; margin: 0 auto; padding: 16px 20px; page-break-after: always; }
        .tor-sheet:last-child { page-break-after: auto; }
        .tor-head { text-align: center; margin-bottom: 8px; }
        .tor-head img { height: 50px; }
        .tor-head h1 { font-size: 14px; margin: 4px 0 0; }
        .tor-head p { font-size: 10px; margin: 1px 0; }
        .tor-title { text-align: center; font-size: 13px; font-weight: 800; margin: 8px 0; text-decoration: underline; }
        .tor-meta { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .tor-meta td { padding: 2px 4px; font-size: 10pt; }
        .tor-meta .label { font-weight: 700; width: 130px; }
        table.tor-tbl { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.tor-tbl th, table.tor-tbl td { border: 1px solid #333; padding: 3px 5px; font-size: 9pt; }
        table.tor-tbl th { background: #eef6f1; }
        tr.tor-sem-row td { background: #f5f5f5; font-weight: 800; }
        .grade-cell { text-align: center; }
        .tor-summary { margin-top: 10px; display: flex; gap: 20px; }
        .tor-summary-box { border: 2px solid #000; padding: 4px 10px; display: inline-flex; gap: 16px; align-items: center; font-size: 9.5pt; }
        .tor-summary-box .label { font-size: 8pt; }
        .tor-summary-box .value { font-size: 13pt; font-weight: 700; }
        @media print { .bp-toolbar { display: none; } }
    </style>
</head>
<body>
    <div class="bp-toolbar">
        <span>{{ $entries->count() }} document(s) ready to print.</span>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    @foreach($entries as $entry)
        @php
            $student = $entry['student'];
            $gradesBySyTerm = $entry['gradesBySyTerm'];
            $gwa = $entry['gwa'];
            $totalUnitsEarned = $entry['totalUnitsEarned'];
        @endphp
        <article class="tor-sheet">
            <div class="tor-head">
                <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo">
                <h1>PAMANTASAN NG LUNGSOD NG PASIG</h1>
                <p>Alcalde Jose Street, Kapasigan, Pasig City</p>
                <p>OFFICE OF THE UNIVERSITY REGISTRAR</p>
            </div>
            <div class="tor-title">TRANSCRIPT OF RECORDS</div>

            <table class="tor-meta">
                <tr>
                    <td class="label">Name:</td><td>{{ strtoupper($student->name) }}</td>
                    <td class="label">Student No.:</td><td>{{ $student->student_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Course:</td><td>{{ optional($student->canonicalCourse)->code ?: ($student->program ?: 'N/A') }}</td>
                    <td class="label">Purpose:</td><td>{{ $entry['purpose'] }}</td>
                </tr>
            </table>

            @if($gradesBySyTerm->isEmpty())
                <p style="text-align:center;color:#888;margin:8mm 0;">No grade records on file.</p>
            @else
                <table class="tor-tbl">
                    <thead>
                        <tr>
                            <th style="width:100px;">Course Number</th>
                            <th>Descriptive Title of the Course</th>
                            <th style="width:60px;">Grade</th>
                            <th style="width:55px;">Credits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gradesBySyTerm as $syTerm => $rows)
                            @php [$sy, $term] = explode('|||', $syTerm); @endphp
                            <tr class="tor-sem-row"><td colspan="4">{{ $sy }}, {{ strtoupper($term) }} SEMESTER</td></tr>
                            @foreach($rows as $r)
                                <tr>
                                    <td>{{ $r->subject_code }}</td>
                                    <td>{{ $r->description ?: $r->equiv_subject_code ?: '-' }}</td>
                                    <td class="grade-cell">{{ $r->inc ? 'INC' : (is_numeric($r->final_grade) ? number_format((float) $r->final_grade, 2) : ($r->final_grade ?: '-')) }}</td>
                                    <td class="grade-cell">{{ is_numeric($r->units) ? number_format((float) $r->units, 2) : '-' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="tor-summary">
                <div class="tor-summary-box">
                    <div><div class="label">GENERAL WEIGHTED AVERAGE</div><div class="value">{{ $gwa !== null ? number_format($gwa, 4) : 'N/A' }}</div></div>
                    <div><div class="label">UNITS EARNED</div><div class="value">{{ number_format($totalUnitsEarned, 1) }}</div></div>
                </div>
            </div>
        </article>
    @endforeach

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
</body>
</html>
