<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Batch COR - PLP</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #111; margin: 0; }
        .bp-toolbar { padding: 10px 14px; background: #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
        .bp-toolbar button { border: 0; border-radius: 6px; background: #146c43; color: #fff; font-weight: 700; padding: 8px 16px; cursor: pointer; }
        .cor-sheet { width: 100%; max-width: 940px; margin: 0 auto 0; padding: 16px 20px; page-break-after: always; }
        .cor-sheet:last-child { page-break-after: auto; }
        .cor-head { text-align: center; margin-bottom: 10px; }
        .cor-head img { height: 54px; }
        .cor-head h1 { font-size: 15px; margin: 4px 0 0; }
        .cor-head p { font-size: 11px; margin: 1px 0; }
        .cor-title { text-align: center; font-size: 14px; font-weight: 800; margin: 10px 0; text-decoration: underline; }
        .cor-meta { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .cor-meta td { padding: 2px 4px; font-size: 12px; }
        .cor-meta .label { font-weight: 700; width: 130px; }
        table.cor-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.cor-table th, table.cor-table td { border: 1px solid #333; padding: 4px 6px; font-size: 11px; }
        table.cor-table th { background: #eef6f1; text-align: center; }
        .cor-num { text-align: right; }
        .cor-total-row td { font-weight: 800; text-align: right; }
        .cor-lower { display: flex; gap: 20px; }
        .cor-fees { flex: 1; }
        .cor-fees-title { font-weight: 800; margin-bottom: 4px; }
        table.cor-fees-table { width: 100%; border-collapse: collapse; }
        table.cor-fees-table td { padding: 2px 4px; font-size: 11px; border-bottom: 1px dotted #ccc; }
        table.cor-fees-table .cor-amount { text-align: right; }
        table.cor-fees-table tr.head td { font-weight: 800; border-bottom: 2px solid #333; }
        table.cor-fees-table tr.double td { font-weight: 800; border-top: 1px solid #333; }
        .cor-side { flex: 1; }
        .cor-cert-text { font-size: 11px; margin-bottom: 20px; }
        .cor-signatures { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .cor-signatures p { margin: 2px 0; font-size: 11px; }
        .cor-signature-name { font-weight: 800; border-top: 1px solid #333; padding-top: 3px; }
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
            $subjects = $entry['subjects'];
            $totalUnits = $entry['totalUnits'];
            $assessment = $entry['assessment'];
            $sectionLabel = trim((string) optional($student->canonicalCourse)->code . ' ' . (string) optional($student->yearBlock)->label);
        @endphp
        <article class="cor-sheet">
            <div class="cor-head">
                <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo">
                <h1>PAMANTASAN NG LUNGSOD NG PASIG</h1>
                <p>Alcalde Jose Street, Kapasigan, Pasig City</p>
                <p>OFFICE OF THE UNIVERSITY REGISTRAR</p>
            </div>
            <div class="cor-title">CERTIFICATE OF REGISTRATION</div>

            <table class="cor-meta">
                <tr>
                    <td class="label">Student No.:</td><td>{{ $student->student_no }}</td>
                    <td class="label">Course/Section:</td><td>{{ $sectionLabel !== '' ? $sectionLabel : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Name:</td><td>{{ strtoupper($student->name) }}</td>
                    <td class="label">School Year:</td><td>{{ optional($student->academicTerm)->school_year ?: $student->school_year ?: 'N/A' }}</td>
                </tr>
            </table>

            <table class="cor-table">
                <thead>
                    <tr>
                        <th>Code</th><th>Description</th><th>Section</th><th>Units</th><th>Room</th><th>Days</th><th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr>
                            <td>{{ $subject->code ?: '-' }}</td>
                            <td>{{ $subject->name ?: '-' }}</td>
                            <td>{{ $subject->year_section ?: '-' }}</td>
                            <td class="cor-num">{{ number_format((float) $subject->units, 2) }}</td>
                            <td>{{ $subject->room ?: '-' }}</td>
                            <td>{{ $subject->days ?: '-' }}</td>
                            <td>{{ trim((string) $subject->formatted_time) ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;">No enrolled subjects found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="cor-total-row">
                        <td colspan="3">TOTAL:</td>
                        <td class="cor-num">{{ number_format((float) $totalUnits, 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>

            <div class="cor-lower">
                <div class="cor-fees">
                    <div class="cor-fees-title">ASSESSMENT OF FEES</div>
                    <table class="cor-fees-table">
                        <tr class="head"><td>PARTICULARS</td><td class="cor-amount">AMOUNT</td></tr>
                        <tr><td>Tuition Fee ({{ number_format($assessment['tuition_units'], 2) }} x {{ number_format($assessment['per_unit_rate'], 2) }})</td><td class="cor-amount">{{ number_format($assessment['tuition_fee'], 2) }}</td></tr>
                        <tr><td>CWTS/ROTC TF ({{ number_format($assessment['nstp_units'], 2) }} x {{ number_format($assessment['per_unit_rate'], 2) }})</td><td class="cor-amount">{{ number_format($assessment['cwts_fee'], 2) }}</td></tr>
                        <tr class="double"><td>TOTAL TUITION FEE</td><td class="cor-amount">{{ number_format($assessment['total_tuition_fee'], 2) }}</td></tr>
                        <tr><td>Miscellaneous Fee</td><td class="cor-amount">{{ number_format($assessment['miscellaneous_fee'], 2) }}</td></tr>
                        <tr><td>Laboratory Fee</td><td class="cor-amount">{{ number_format($assessment['laboratory_fee'], 2) }}</td></tr>
                        <tr class="double"><td>CURRENT ACCOUNT</td><td class="cor-amount">{{ number_format($assessment['current_account'], 2) }}</td></tr>
                    </table>
                </div>
                <div class="cor-side">
                    <p class="cor-cert-text">This is to certify that the student whose name appears on this document is officially enrolled this term with the subject load listed above.</p>
                    <div class="cor-signatures">
                        <div><p class="cor-signature-name">{{ strtoupper($student->name) }}</p><p>STUDENT SIGNATURE</p></div>
                        <div><p class="cor-signature-name">UNIVERSITY REGISTRAR</p><p>&nbsp;</p></div>
                    </div>
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
