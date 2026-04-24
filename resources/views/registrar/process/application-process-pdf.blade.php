<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Applicant List</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #1a202c;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #006837;
            padding-bottom: 25px;
        }
        .university-name {
            font-size: 18pt;
            font-weight: 900;
            color: #006837;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .office-name {
            font-size: 11pt;
            font-weight: 700;
            color: #4a5568;
            margin-top: 5px;
        }
        .report-title {
            font-size: 13pt;
            font-weight: 800;
            color: #2d3748;
            margin-top: 10px;
            text-decoration: underline;
        }
        .filter-summary {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
        }
        .filter-label {
            font-weight: 700;
            color: #718096;
            text-transform: uppercase;
            font-size: 7pt;
        }
        .filter-value {
            font-weight: 700;
            color: #2d3748;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #006837;
            color: #ffffff;
            font-weight: 800;
            text-align: left;
            padding: 10px 8px;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.05em;
        }
        td {
            padding: 9px 8px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }
        .row-even {
            background-color: #f8fafc;
        }
        .status-badge {
            font-weight: 800;
            font-size: 7.5pt;
            padding: 3px 8px;
            border-radius: 3px;
        }
        .status-ACCEPTED { color: #15803d; }
        .status-PENDING { color: #d97706; }
        .status-REJECTED { color: #b91c1c; }
        .status-INCOMPLETE { color: #b91c1c; }
        .status-SUBMITTED { color: #1d4ed8; }
        
        .footer-table {
            width: 100%;
            margin-top: 40px;
            font-size: 8pt;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            margin-top: 50px;
        }
        .generated-at {
            color: #718096;
            font-style: italic;
        }
    </style>
</head>
<body>
    <table class="header-table" style="border-bottom: 2px solid #006837;">
        <tr>
            <td style="border: none; padding: 0; width: 80px; vertical-align: middle;">
                <img src="{{ public_path('img/logobg.png') }}" style="width: 70px; height: auto;">
            </td>
            <td style="border: none; padding: 0; text-align: center; vertical-align: middle;">
                <div class="university-name" style="font-size: 16pt;">Pamantasan ng Lungsod ng Pasig</div>
                <div class="office-name" style="font-size: 10pt; color: #1a202c; letter-spacing: 0.1em;">OFFICE OF THE UNIVERSITY REGISTRAR</div>
                <div style="font-size: 12pt; font-weight: 800; color: #2d3748; margin-top: 8px;">APPLICANT PROCESS REPORT</div>
            </td>
            <td style="border: none; padding: 0; width: 120px; text-align: right; vertical-align: middle;">
                <div class="generated-at" style="font-size: 7pt; font-style: normal; font-weight: 700;">
                    DATE: {{ date('M d, Y') }}<br>
                    TIME: {{ date('h:i A') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="filter-summary" style="margin-top: 15px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; padding: 0; width: 30%;">
                    <span class="filter-label">PERIOD:</span><br>
                    <span class="filter-value">{{ $filters['from_date'] ?: 'START' }} TO {{ $filters['to_date'] ?: 'END' }}</span>
                </td>
                <td style="border: none; padding: 0; width: 35%;">
                    <span class="filter-label">COURSE / PROGRAM:</span><br>
                    <span class="filter-value">{{ strtoupper($courseName) }}</span>
                </td>
                <td style="border: none; padding: 0; width: 20%;">
                    <span class="filter-label">STATUS:</span><br>
                    <span class="filter-value">{{ strtoupper($filters['application_status'] ?: 'ALL') }}</span>
                </td>
                <td style="border: none; padding: 0; width: 15%; text-align: right;">
                    <span class="filter-label">TOTAL:</span><br>
                    <span class="filter-value" style="font-size: 12pt; color: #006837;">{{ count($applicants) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
        <tr>
            <th style="width: 25px; text-align: center;">#</th>
            <th style="width: 100px;">Applicant ID</th>
            <th style="width: 22%;">Full Name</th>
            <th style="width: 30%;">Program / Strand</th>
            <th style="width: 80px; text-align: center;">Applied</th>
            <th style="width: 80px; text-align: center;">Updated</th>
            <th style="width: 110px; text-align: center;">Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($applicants as $index => $applicant)
            @php
                $displayName = strtoupper(trim((string) $applicant->last_name . ', ' . (string) $applicant->first_name . ' ' . (string) $applicant->middle_name));
                $preference = optional($applicant->applicationPreference);
                $preferredCourse = optional($preference->course);
                $programLabel = 'N/A';
                if ($preference->apply_program === 'college') {
                    $programLabel = $preferredCourse->name ?: ($preferredCourse->code ?: 'College');
                } elseif ($preference->apply_program === 'senior_high') {
                    $programLabel = $preference->apply_strand ?: 'Senior High';
                }

                $rawStatus = strtolower(trim((string) ($applicant->application_status ?: 'in process')));
                $statusText = 'IN PROCESS';
                
                if ($rawStatus === 'accepted') { $statusText = 'ACCEPTED'; }
                elseif ($rawStatus === 'rejected') { $statusText = 'REJECTED'; }
                elseif ($rawStatus === 'incomplete') { $statusText = 'INCOMPLETE'; }
                elseif ($rawStatus === 'submitted' || $rawStatus === 'document submitted') { $statusText = 'SUBMITTED'; }
                
                $dateApplied = $applicant->application_submitted_at ?: $applicant->created_at;
            @endphp
            <tr class="{{ $index % 2 === 0 ? '' : 'row-even' }}">
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="font-weight: 700; color: #4a5568;">{{ $applicant->applicant_id }}</td>
                <td style="font-weight: 700;">{{ $displayName }}</td>
                <td>{{ $programLabel }}</td>
                <td style="text-align: center;">{{ optional($dateApplied)->format('m/d/Y') }}</td>
                <td style="text-align: center;">{{ optional($applicant->updated_at)->format('m/d/Y') }}</td>
                <td style="text-align: center;">
                    <span class="status-badge status-{{ $statusText }}">{{ $statusText }}</span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="footer-table" style="border: none;">
        <tr>
            <td style="border: none; width: 50%;">
                <div class="generated-at" style="margin-top: 50px;">
                    * This is a system-generated report.
                </div>
            </td>
            <td style="border: none; width: 50%; text-align: right;">
                <div class="signature-line" style="margin-left: auto;"></div>
                <div style="font-weight: 700; text-transform: uppercase; margin-right: 35px;">University Registrar</div>
            </td>
        </tr>
    </table>
</body>
</html>
