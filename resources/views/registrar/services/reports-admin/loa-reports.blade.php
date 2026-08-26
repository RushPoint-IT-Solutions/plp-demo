@extends('layouts.registrar')

@section('title', 'PLP - LOA Reports')
@section('page-title', 'LEAVE OF ABSENCE REPORTS')

@push('styles')
<style>
    .loa-report-page { display:flex; flex-direction:column; gap:16px; }
    .loa-report-head { align-items:flex-start; display:flex; justify-content:space-between; gap:16px; }
    .loa-report-head h2 { color:#163c29; font-size:1.15rem; font-weight:800; margin:0 0 4px; }
    .loa-report-head p { color:#64748b; font-size:.82rem; margin:0; }
    .loa-report-actions { display:flex; gap:8px; }
    .loa-filter-panel,
    .loa-report-panel { background:#fff; border:1px solid #dce7e0; border-radius:10px; box-shadow:0 4px 14px rgba(15,81,50,.05); }
    .loa-filter-panel { padding:16px; }
    .loa-filter-grid { display:grid; grid-template-columns:repeat(3,minmax(150px,1fr)); gap:12px; }
    .loa-filter-field label { color:#425b4b; display:block; font-size:.7rem; font-weight:800; margin-bottom:5px; text-transform:uppercase; }
    .loa-filter-field input,
    .loa-filter-field select { background:#fff; border:1px solid #cfdcd4; border-radius:6px; color:#243b2e; font-size:.8rem; height:36px; padding:0 10px; width:100%; }
    .loa-filter-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:12px; }
    .loa-summary-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
    .loa-summary-card { background:#fff; border:1px solid #dce7e0; border-left:4px solid var(--accent,#006837); border-radius:9px; padding:15px 16px; }
    .loa-summary-label { color:#64748b; font-size:.7rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .loa-summary-value { color:#173c29; font-size:1.6rem; font-weight:900; line-height:1.15; margin-top:4px; }
    .loa-breakdown-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .loa-report-panel { overflow:hidden; }
    .loa-panel-title { align-items:center; background:#f5faf7; border-bottom:1px solid #dce7e0; color:#17452e; display:flex; font-size:.82rem; font-weight:800; justify-content:space-between; padding:11px 13px; }
    .loa-table-scroll { overflow:auto; }
    .loa-report-table { border-collapse:collapse; margin:0; width:100%; }
    .loa-report-table th { background:#006837; color:#fff; font-size:.68rem; font-weight:800; padding:8px 9px; text-align:left; text-transform:uppercase; white-space:nowrap; }
    .loa-report-table td { border-bottom:1px solid #e7eee9; color:#33463a; font-size:.74rem; padding:8px 9px; vertical-align:top; }
    .loa-report-table tbody tr:last-child td { border-bottom:0; }
    .loa-count-cell { font-weight:800; text-align:center; }
    .loa-case-badge { border-radius:999px; display:inline-block; font-size:.65rem; font-weight:800; padding:3px 8px; white-space:nowrap; }
    .loa-case-enrolled { background:#dcfce7; color:#166534; }
    .loa-case-non_enrolled { background:#dbeafe; color:#1e40af; }
    .loa-case-late { background:#fef3c7; color:#92400e; }
    .loa-empty { color:#718079; padding:28px 16px; text-align:center; }
    .loa-print-meta { color:#64748b; display:none; font-size:9pt; margin-top:3px; }
    @media (max-width:1050px) {
        .loa-filter-grid { grid-template-columns:repeat(2,minmax(150px,1fr)); }
        .loa-breakdown-grid { grid-template-columns:1fr; }
    }
    @media (max-width:720px) {
        .loa-report-head { flex-direction:column; }
        .loa-filter-grid { grid-template-columns:1fr; }
        .loa-summary-grid { grid-template-columns:repeat(2,1fr); }
    }
    @media print {
        @page { size:A4 landscape; margin:10mm; }
        .loa-report-actions,
        .loa-filter-panel { display:none !important; }
        .loa-report-page { gap:9px; }
        .loa-print-meta { display:block; }
        .loa-summary-card,
        .loa-report-panel { box-shadow:none; break-inside:avoid; }
        .loa-breakdown-grid { grid-template-columns:repeat(3,1fr); }
        .loa-report-table th { background:#e7f2eb !important; color:#173c29 !important; }
    }
</style>
@endpush

@section('content')
<div class="loa-report-page">
    <div class="loa-report-head">
        <div>
            <h2>Semester LOA Summary</h2>
            <p>Enrolled, non-enrolled, and late filing cases with reason, program, and college/department breakdowns.</p>
            <div class="loa-print-meta">
                School Year: {{ $filters['schoolYear'] ?: 'All' }} | Semester: {{ $filters['semester'] ?: 'All' }} | Generated: {{ now()->format('F d, Y h:i A') }}
            </div>
        </div>
        <div class="loa-report-actions">
            <button type="button" class="req-btn-cancel" onclick="loaExportCsv()">Export CSV</button>
            <button type="button" class="req-btn-save" onclick="window.print()">Print Report</button>
        </div>
    </div>

    <form method="GET" action="{{ route('registrar.services.reports-admin.loa-reports') }}" class="loa-filter-panel">
        <div class="loa-filter-grid">
            <div class="loa-filter-field">
                <label for="loaReportSchoolYear">School Year</label>
                <select id="loaReportSchoolYear" name="school_year">
                    <option value="">All School Years</option>
                    @foreach($schoolYearOptions as $option)
                        <option value="{{ $option }}" {{ $filters['schoolYear'] === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="loa-filter-field">
                <label for="loaReportSemester">Semester</label>
                <select id="loaReportSemester" name="semester">
                    <option value="">All Semesters</option>
                    @foreach($semesterOptions as $option)
                        <option value="{{ $option }}" {{ $filters['semester'] === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="loa-filter-field">
                <label for="loaReportType">Case Type</label>
                <select id="loaReportType" name="filing_type">
                    <option value="">All Cases</option>
                    @foreach($filingTypes as $value => $label)
                        <option value="{{ $value }}" {{ $filters['filingType'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="loa-filter-field">
                <label for="loaReportReason">Reason</label>
                <select id="loaReportReason" name="reason">
                    <option value="">All Reasons</option>
                    @foreach($reasonOptions as $option)
                        <option value="{{ $option }}" {{ $filters['reason'] === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="loa-filter-field">
                <label for="loaReportProgram">Program</label>
                <select id="loaReportProgram" name="program">
                    <option value="">All Programs</option>
                    @foreach($programOptions as $option)
                        <option value="{{ $option }}" {{ $filters['program'] === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="loa-filter-field">
                <label for="loaReportCollege">College / Department</label>
                <select id="loaReportCollege" name="college_department">
                    <option value="">All Colleges / Departments</option>
                    @foreach($collegeDepartmentOptions as $option)
                        <option value="{{ $option }}" {{ $filters['collegeDepartment'] === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="loa-filter-field" style="grid-column:1/-1;">
                <label for="loaReportSearch">Search Student or Details</label>
                <input id="loaReportSearch" name="q" type="search" value="{{ $filters['search'] }}" placeholder="Student number, name, reason, program, or college/department">
            </div>
        </div>
        <div class="loa-filter-actions">
            <a class="req-btn-cancel" href="{{ route('registrar.services.reports-admin.loa-reports', ['school_year' => '', 'semester' => '']) }}">Clear</a>
            <button type="submit" class="req-btn-save">Apply Filters</button>
        </div>
    </form>

    <div class="loa-summary-grid">
        <div class="loa-summary-card" style="--accent:#006837;"><div class="loa-summary-label">Total LOA Cases</div><div class="loa-summary-value">{{ number_format($summary['total']) }}</div></div>
        <div class="loa-summary-card" style="--accent:#16a34a;"><div class="loa-summary-label">Enrolled</div><div class="loa-summary-value">{{ number_format($summary['enrolled']) }}</div></div>
        <div class="loa-summary-card" style="--accent:#2563eb;"><div class="loa-summary-label">Non-Enrolled</div><div class="loa-summary-value">{{ number_format($summary['non_enrolled']) }}</div></div>
        <div class="loa-summary-card" style="--accent:#d97706;"><div class="loa-summary-label">Late Filing</div><div class="loa-summary-value">{{ number_format($summary['late']) }}</div></div>
    </div>

    @php
        $breakdownSets = [
            ['title' => 'By Reason for LOA', 'rows' => $reasonBreakdown],
            ['title' => 'By Program', 'rows' => $programBreakdown],
            ['title' => 'By College / Department', 'rows' => $collegeDepartmentBreakdown],
        ];
    @endphp
    <div class="loa-breakdown-grid">
        @foreach($breakdownSets as $breakdown)
            <section class="loa-report-panel">
                <div class="loa-panel-title"><span>{{ $breakdown['title'] }}</span><span>{{ number_format($breakdown['rows']->count()) }} groups</span></div>
                <div class="loa-table-scroll">
                    <table class="loa-report-table">
                        <thead><tr><th>Group</th><th>Total</th><th>Enr.</th><th>Non-Enr.</th><th>Late</th></tr></thead>
                        <tbody>
                            @forelse($breakdown['rows'] as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="loa-count-cell">{{ $row['total'] }}</td>
                                    <td class="loa-count-cell">{{ $row['enrolled'] }}</td>
                                    <td class="loa-count-cell">{{ $row['non_enrolled'] }}</td>
                                    <td class="loa-count-cell">{{ $row['late'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="loa-empty">No LOA records for the selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>

    <section class="loa-report-panel">
        <div class="loa-panel-title"><span>Detailed LOA Cases</span><span>{{ number_format($records->count()) }} records</span></div>
        <div class="loa-table-scroll">
            <table class="loa-report-table" id="loaReportDetailTable">
                <thead>
                    <tr><th>Date</th><th>Student No.</th><th>Student</th><th>School Year</th><th>Semester</th><th>Case Type</th><th>Reason</th><th>Reason Details</th><th>Program</th><th>College / Department</th></tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ optional($record->application_date)->format('Y-m-d') }}</td>
                            <td>{{ optional($record->student)->student_no ?: '-' }}</td>
                            <td>{{ optional($record->student)->name ?: '-' }}</td>
                            <td>{{ $record->school_year }}</td>
                            <td>{{ $record->semester }}</td>
                            <td><span class="loa-case-badge loa-case-{{ $record->filing_type }}">{{ $record->filing_type_label }}</span></td>
                            <td>{{ $record->reason }}</td>
                            <td>{{ $record->reason_details ?: '-' }}</td>
                            <td>{{ $record->program ?: 'Not Recorded' }}</td>
                            <td>{{ $record->college_department ?: 'Not Recorded' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="loa-empty">No LOA records for the selected semester and filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function loaExportCsv() {
    var table = document.getElementById('loaReportDetailTable');
    if (!table) return;

    var rows = Array.prototype.map.call(table.querySelectorAll('tr'), function(row) {
        return Array.prototype.map.call(row.querySelectorAll('th,td'), function(cell) {
            return '"' + String(cell.textContent || '').trim().replace(/"/g, '""') + '"';
        }).join(',');
    });

    var blob = new Blob(['\uFEFF' + rows.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'loa-report-{{ preg_replace('/[^A-Za-z0-9_-]+/', '-', ($filters['schoolYear'] ?: 'all') . '-' . ($filters['semester'] ?: 'semesters')) }}.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}
</script>
@endpush
