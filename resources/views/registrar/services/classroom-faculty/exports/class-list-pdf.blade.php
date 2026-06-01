<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Class List - PLP</title>
    <style>
        @page { margin: 8mm; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            line-height: 1.05;
            color: #111;
            margin: 0;
        }
        .cl-print-sheet {
            width: auto;
            background: #fff;
            color: #111;
            border: 2px solid #111;
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
        .summary-title {
            font-family: Arial, sans-serif;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10pt;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 8.5pt;
        }
        .summary-table th,
        .summary-table td {
            border: .8pt solid #111;
            padding: 4pt;
        }
        .summary-table th {
            background: #f1f5f9;
            font-weight: bold;
        }
    </style>
</head>
<body>
@if($selectedSubject)
    @include('registrar.services.classroom-faculty.partials.class-list-print-sheet')
@else
    <div class="summary-title">CLASS LIST SUMMARY</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th style="width: 30pt;">#</th>
                <th>Section</th>
                <th>Subject Code</th>
                <th>Description</th>
                <th>Schedule</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subjects as $index => $subject)
                <tr>
                    <td style="text-align:center;">{{ $index + 1 }}</td>
                    <td>{{ $controller->sectionLabel($subject) }}</td>
                    <td>{{ $subject->code }}</td>
                    <td>{{ strtoupper($subject->name) }}</td>
                    <td>{{ $controller->scheduleLabel($subject) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:18pt;">No class list data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif
</body>
</html>
