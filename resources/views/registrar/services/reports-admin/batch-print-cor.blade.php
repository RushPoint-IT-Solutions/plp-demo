<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Batch COR - PLP</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #f0f0f0; margin: 0; font-family: Arial, Helvetica, sans-serif; }

        .bp-toolbar {
            background: #004d27; color: #fff; padding: 10px 24px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }
        .bp-toolbar span { font-size: 14px; font-weight: 600; }
        .bp-toolbar button {
            background: #fff; color: #004d27; border: none; border-radius: 6px;
            padding: 7px 20px; font-size: 13px; font-weight: 700; cursor: pointer;
        }

        .cor-sheet-wrap { width: 100%; display: grid; place-items: center; padding: 18px 0; }
        .cor-sheet {
            width: 816px; height: 1344px; background: #fff;
            padding: 205px 34px 18px;
            font-family: Arial, Helvetica, sans-serif; font-size: 8px; line-height: 1.08; color: #000;
            border: 1px solid #111; page-break-after: always; overflow: hidden;
        }
        .cor-sheet:last-child { page-break-after: auto; }

        .cor-header-frame { position: relative; height: 190px; margin-bottom: 4px; }
        .cor-tpl-element { position: absolute; white-space: pre-wrap; }

        .cor-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 3px; border: 1px solid #000; border-left-width: 2px; }
        .cor-table th, .cor-table td { padding: 2px 5px; vertical-align: top; }
        .cor-table thead tr:first-child th { border-bottom: 1px solid #000; font-size: 11px; padding: 3px 0 2px; text-align: center; letter-spacing: .03em; }
        .cor-table thead tr:nth-child(2) th { border-bottom: 2px solid #000; font-size: 7.5px; text-align: left; }
        .cor-table td { height: 16px; }
        .cor-num, .cor-table .cor-num, .cor-table th.cor-num { text-align: center; }
        .cor-total-row td { border-top: 1px solid #000; border-bottom: 2px solid #000; font-weight: 700; font-size: 9px; }

        .cor-lower { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 10px; align-items: start; }
        .cor-fees { border: 2px solid #000; min-height: 405px; padding: 5px 8px; }
        .cor-fees-title { text-align: center; font-size: 8px; font-weight: 800; letter-spacing: .18em; margin-bottom: 1px; }
        .cor-fees-table { width: 100%; border-collapse: collapse; }
        .cor-fees-table td { padding: 3px 3px; }
        .cor-fees-table .head td { border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 7.5px; font-weight: 800; }
        .cor-fees-table .section td { border-top: 1px solid #000; font-weight: 800; padding-top: 5px; }
        .cor-fees-table .double td { border-top: 1px solid #000; border-bottom: 1px solid #000; font-weight: 800; }
        .cor-indent { padding-left: 22px !important; }
        .cor-amount { width: 92px; text-align: right; white-space: nowrap; }
        .cor-current { font-size: 11px; font-weight: 900; }

        .cor-legend { margin: 10px 0 0 46px; font-size: 7.5px; font-weight: 700; }
        .cor-legend-title { margin: 0 0 8px; font-size: 8px; font-weight: 900; }

        .cor-side { padding: 10px 0 0; font-size: 8.5px; }
        .cor-cert-text { text-align: center; font-size: 9px; font-weight: 700; margin: 5px 22px 54px; }
        .cor-signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin: 0 8px 18px; }
        .cor-signature-name { border-bottom: 2px solid #000; text-align: center; font-size: 9px; font-weight: 800; min-height: 16px; margin: 0; white-space: nowrap; }
        .cor-signature-role { text-align: center; font-size: 8px; font-style: italic; margin: 2px 0 0; }

        .cor-stamp { width: 250px; min-height: 145px; border: 1px solid #777; margin: 0 auto 12px; text-align: center; padding: 8px 10px 10px; }
        .cor-stamp img { width: 48px; height: 48px; object-fit: contain; display: block; margin: 0 auto 7px; }
        .cor-stamp p { margin: 0; font-size: 7px; line-height: 1.25; }
        .cor-stamp strong { display: block; margin-top: 6px; font-size: 12px; letter-spacing: .03em; }
        .cor-stamp span { display: block; margin-top: 6px; font-size: 8px; font-weight: 800; }

        .cor-notice-title { font-size: 8.5px; font-weight: 800; margin: 0 0 12px; }
        .cor-notice-body { text-align: center; font-size: 8.5px; font-weight: 700; margin: 0 34px; }

        .cor-print-meta { display: flex; justify-content: space-between; gap: 10px; margin-top: 8px; font-size: 7px; color: #444; }

        @media print { .bp-toolbar { display: none; } .cor-sheet { border: none; } }
    </style>
</head>
<body>
    <div class="bp-toolbar">
        <span>{{ $entries->count() }} document(s) ready to print.</span>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <div class="cor-sheet-wrap">
    @foreach($entries as $entry)
        @php
            $student = $entry['student'];
            $subjects = $entry['subjects'];
            $totalUnits = $entry['totalUnits'];
            $assessment = $entry['assessment'];
            $headerElements = $entry['headerElements'];

            $formattedSchoolYear = trim((string) $student->school_year) ?: trim((string) optional($student->academicTerm)->school_year);
            $formattedSemester = strtoupper(trim((string) $student->semester)) ?: strtoupper(trim((string) optional($student->academicTerm)->term));
            $semesterLabel = $formattedSemester !== '' ? (strpos($formattedSemester, 'SEMESTER') !== false ? $formattedSemester : $formattedSemester . ' SEMESTER') : '-';
            $academicYearLabel = $formattedSchoolYear !== '' ? $formattedSchoolYear : '-';
            $enrolledStampTerm = trim(str_replace(' SEMESTER', ' SEM', $semesterLabel) . ' ' . $academicYearLabel);
            $studentDisplayName = strtoupper(trim((string) $student->name)) ?: '-';

            $tuitionUnits = number_format((float) $assessment['tuition_units'], 2);
            $nstpUnits = number_format((float) $assessment['nstp_units'], 2);
            $perUnitRate = number_format((float) $assessment['per_unit_rate'], 2);
            $totalTuitionFee = number_format((float) $assessment['total_tuition_fee'], 2);
            $miscellaneousFee = number_format((float) $assessment['miscellaneous_fee'], 2);
            $laboratoryFee = number_format((float) $assessment['laboratory_fee'], 2);
            $currentAccountValue = (float) $assessment['current_account'];
            $currentAccount = number_format($currentAccountValue, 2);
            $midtermDue = number_format($currentAccountValue / 2, 2);
            $finalDue = number_format($currentAccountValue / 2, 2);
        @endphp
        <article class="cor-sheet">
            <div class="cor-header-frame">
                @foreach($headerElements as $element)
                    <div class="cor-tpl-element" style="
                        top:{{ $element['top'] }}%;
                        left:{{ $element['left'] }}%;
                        width:{{ $element['width'] }}%;
                        font-family:'{{ $element['font_family'] ?? 'Arial' }}', sans-serif;
                        font-size:{{ $element['font_size'] ?? 8 }}pt;
                        font-weight:{{ $element['font_weight'] ?? 'normal' }};
                        font-style:{{ $element['font_style'] ?? 'normal' }};
                        text-decoration:{{ $element['text_decoration'] ?? 'none' }};
                        text-align:{{ $element['text_align'] ?? 'left' }};
                        line-height:{{ $element['line_height'] ?? 1.2 }};
                    ">{{ $element['resolved_text'] ?? $element['text'] ?? '' }}</div>
                @endforeach
            </div>

            <table class="cor-table" data-no-auto-pager="1">
                <colgroup>
                    <col style="width:62px"><col><col style="width:120px"><col style="width:70px">
                    <col style="width:82px"><col style="width:70px"><col style="width:140px"><col style="width:82px">
                </colgroup>
                <thead>
                    <tr><th colspan="8">CLASS SCHEDULE</th></tr>
                    <tr>
                        <th>COURSE</th><th>COURSE DESCRIPTION</th><th>SECTION</th><th class="cor-num">UNITS</th>
                        <th>ROOM</th><th>DAYS</th><th>TIME</th><th class="cor-num">PAY UNITS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        @php
                            $section = trim((string) $subject->year_section);
                            if ($section === '') { $section = trim((string) $student->program . ' ' . (string) $student->year_level); }
                            $timeText = trim((string) $subject->formatted_time) ?: trim((string) $subject->time_range);
                            $payUnits = is_numeric($subject->credited_tuition_units) ? (float) $subject->credited_tuition_units : (is_numeric($subject->units) ? (float) $subject->units : 0);
                        @endphp
                        <tr>
                            <td>{{ $subject->code ?: '-' }}</td>
                            <td>{{ $subject->name ?: '-' }}</td>
                            <td>{{ $section !== '' ? $section : '-' }}</td>
                            <td class="cor-num">{{ number_format((float) $subject->units, 2) }}</td>
                            <td>{{ $subject->room ?: '-' }}</td>
                            <td>{{ $subject->days ?: '-' }}</td>
                            <td>{{ $timeText !== '' ? $timeText : '-' }}</td>
                            <td class="cor-num">{{ number_format($payUnits, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;padding:18px;">No enrolled subjects found for this student.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="cor-total-row">
                        <td colspan="3" style="text-align:right;">TOTAL:</td>
                        <td class="cor-num">{{ number_format((float) $totalUnits, 2) }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>

            <section class="cor-lower">
                <div>
                    <div class="cor-fees">
                        <div class="cor-fees-title">ASSESSMENT OF FEES</div>
                        <table class="cor-fees-table" data-no-auto-pager="1">
                            <tbody>
                                <tr class="head"><td>PARTICULARS</td><td class="cor-amount">AMOUNT</td></tr>
                                <tr class="section"><td>TUITION FEE</td><td class="cor-amount"></td></tr>
                                <tr><td class="cor-indent">TUITION FEE</td><td class="cor-amount">{{ $tuitionUnits }} x {{ $perUnitRate }}</td></tr>
                                <tr><td class="cor-indent">CWTS/ROTC TF</td><td class="cor-amount">{{ $nstpUnits }} x {{ $perUnitRate }}</td></tr>
                                <tr class="double"><td>TOTAL TUITION FEE</td><td class="cor-amount">{{ $totalTuitionFee }}</td></tr>
                                <tr class="section"><td>MISCELLANEOUS FEE</td><td class="cor-amount"></td></tr>
                                <tr><td class="cor-indent">MISCELLANEOUS FEE</td><td class="cor-amount">{{ $miscellaneousFee }}</td></tr>
                                <tr class="double"><td>TOTAL MISCELLANEOUS FEE</td><td class="cor-amount">{{ $miscellaneousFee }}</td></tr>
                                <tr class="section"><td>LABORATORY FEE</td><td class="cor-amount"></td></tr>
                                <tr><td class="cor-indent">LABORATORY FEE</td><td class="cor-amount">{{ $laboratoryFee }}</td></tr>
                                <tr class="double"><td>TOTAL LABORATORY FEE</td><td class="cor-amount">{{ $laboratoryFee }}</td></tr>
                                <tr><td><strong>OLD ACCOUNT</strong></td><td class="cor-amount">-</td></tr>
                                <tr><td><strong>CURRENT ACCOUNT</strong></td><td class="cor-amount cor-current">{{ $currentAccount }}</td></tr>
                                <tr><td class="cor-indent"><strong>CONTRACT / PETITION SUBJECT</strong></td><td class="cor-amount">-</td></tr>
                                <tr><td class="cor-indent"><strong>MIDTERM DUE</strong></td><td class="cor-amount"><strong>{{ $midtermDue }}</strong></td></tr>
                                <tr><td class="cor-indent"><strong>FINAL DUE</strong></td><td class="cor-amount"><strong>{{ $finalDue }}</strong></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="cor-legend">
                        <p class="cor-legend-title">LEGEND</p>
                        <p>* &nbsp;-&nbsp; Added Subject/s</p>
                        <p>** -&nbsp; Officially Dropped Subject/s</p>
                    </div>
                </div>

                <div class="cor-side">
                    <p class="cor-cert-text">This is to certify that the student whose name appears on this document is officially enrolled this term with subject load listed above.</p>
                    <div class="cor-signatures">
                        <div>
                            <p class="cor-signature-name">{{ $studentDisplayName }}</p>
                            <p class="cor-signature-role">STUDENT SIGNATURE</p>
                        </div>
                        <div>
                            <p class="cor-signature-name">FEDERICO G. NUEVA, MT</p>
                            <p class="cor-signature-role">UNIVERSITY REGISTRAR</p>
                        </div>
                    </div>
                    <div class="cor-stamp">
                        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo">
                        <p>PAMANTASAN NG LUNGSOD NG PASIG</p>
                        <p>OFFICE OF THE UNIVERSITY REGISTRAR</p>
                        <strong>OFFICIALLY ENROLLED</strong>
                        <span>{{ $enrolledStampTerm }}</span>
                    </div>
                    <p class="cor-notice-title">Notice to all Students :</p>
                    <p class="cor-notice-body">Present this certificate of registration for any claim or transaction that you engage in within the University.</p>
                </div>
            </section>

            <footer class="cor-print-meta">
                <span>Printed by: {{ optional(auth()->user())->name ?: 'Registrar User' }}</span>
                <span>Time Printed: {{ now()->format('g:i:sa') }}</span>
                <span>Date Printed: {{ now()->format('m/d/Y') }}</span>
            </footer>
        </article>
    @endforeach
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
</body>
</html>
