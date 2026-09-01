@extends('layouts.registrar')

@section('title', 'PLP - Form 137-A Monitoring')
@section('page-title', 'FORM 137-A MONITORING')

@push('styles')
<style>
    .f137r-page { display:flex; flex-direction:column; gap:16px; }
    .f137r-head { align-items:flex-start; display:flex; gap:16px; justify-content:space-between; }
    .f137r-head h2 { color:#173c29; font-size:1.15rem; font-weight:800; margin:0 0 4px; }
    .f137r-head p { color:#64748b; font-size:.82rem; margin:0; }
    .f137r-actions { display:flex; flex:0 0 auto; gap:8px; }
    .f137r-filter,
    .f137r-panel { background:#fff; border:1px solid #dce7e0; border-radius:10px; box-shadow:0 4px 14px rgba(15,81,50,.05); }
    .f137r-filter { padding:16px; }
    .f137r-filter-grid { align-items:end; display:grid; gap:12px; grid-template-columns:160px 160px 165px 165px minmax(220px,1fr); }
    .f137r-field label { color:#425b4b; display:block; font-size:.7rem; font-weight:800; margin-bottom:5px; text-transform:uppercase; }
    .f137r-field input,
    .f137r-field select { background:#fff; border:1px solid #cfdcd4; border-radius:6px; color:#243b2e; font-size:.8rem; height:36px; padding:0 10px; width:100%; }
    .f137r-filter-buttons { display:flex; gap:8px; justify-content:flex-end; margin-top:12px; }
    .f137r-summary { display:grid; gap:12px; grid-template-columns:repeat(4,1fr); }
    .f137r-card { background:#fff; border:1px solid #dce7e0; border-left:4px solid var(--accent,#006837); border-radius:9px; padding:15px 16px; }
    .f137r-card-label { color:#64748b; font-size:.7rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .f137r-card-value { color:#173c29; font-size:1.6rem; font-weight:900; line-height:1.15; margin-top:4px; }
    .f137r-panel { overflow:hidden; }
    .f137r-panel-title { align-items:center; background:#f5faf7; border-bottom:1px solid #dce7e0; color:#17452e; display:flex; font-size:.82rem; font-weight:800; justify-content:space-between; padding:11px 13px; }
    .f137r-table-wrap { overflow:auto; }
    .f137r-table { border-collapse:collapse; min-width:1080px; width:100%; }
    .f137r-table th { background:#006837; color:#fff; font-size:.68rem; font-weight:800; padding:9px 10px; text-align:left; text-transform:uppercase; white-space:nowrap; }
    .f137r-table td { border-bottom:1px solid #e7eee9; color:#33463a; font-size:.76rem; padding:10px; vertical-align:middle; }
    .f137r-table tbody tr:last-child td { border-bottom:0; }
    .f137r-student-name { font-weight:700; min-width:170px; }
    .f137r-date { white-space:nowrap; }
    .f137r-status { border-radius:999px; display:inline-flex; font-size:.68rem; font-weight:800; padding:4px 9px; white-space:nowrap; }
    .f137r-status.is-pending { background:#fff4d6; color:#8a5a00; }
    .f137r-status.is-printed { background:#e1f4e8; color:#116334; }
    .f137r-empty { color:#718079; padding:30px 16px !important; text-align:center; }
    .f137r-print-meta { color:#64748b; display:none; font-size:9pt; margin-top:4px; }
    @media (max-width:1050px) {
        .f137r-filter-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .f137r-field:last-child { grid-column:1/-1; }
    }
    @media (max-width:720px) {
        .f137r-head { flex-direction:column; }
        .f137r-summary { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width:520px) {
        .f137r-filter-grid,
        .f137r-summary { grid-template-columns:1fr; }
        .f137r-field:last-child { grid-column:auto; }
        .f137r-filter-buttons { flex-direction:column-reverse; }
        .f137r-filter-buttons .req-btn-cancel,
        .f137r-filter-buttons .req-btn-save { justify-content:center; text-align:center; width:100%; }
    }
    @media print {
        @page { size:A4 landscape; margin:9mm; }
        .f137r-actions,
        .f137r-filter { display:none !important; }
        .f137r-print-meta { display:block; }
        .f137r-card,
        .f137r-panel { box-shadow:none; }
        .f137r-card { padding:8px 10px; }
        .f137r-card-value { font-size:1.25rem; }
        .f137r-table { min-width:0; }
        .f137r-table th { background:#e7f2eb !important; color:#173c29 !important; font-size:7pt; padding:6px; }
        .f137r-table td { font-size:7pt; padding:6px; }
    }
</style>
@endpush

@section('content')
<div class="f137r-page">
    <header class="f137r-head">
        <div>
            <h2>Form 137-A Request and Issuance Report</h2>
            <p>Monitor first and second requests from recording through printing.</p>
            <div class="f137r-print-meta">
                Requested: {{ $filters['startDate'] ?: 'Beginning' }} to {{ $filters['endDate'] ?: 'Present' }}
                | Status: {{ $filters['status'] ? ucwords(str_replace('_', ' ', $filters['status'])) : 'All' }}
                | Generated: {{ now()->format('F d, Y h:i A') }}
            </div>
        </div>
        <div class="f137r-actions">
            <button type="button" class="req-btn-cancel" onclick="f137rExportCsv()">
                <i class="bi bi-download" aria-hidden="true"></i> Export CSV
            </button>
            <button type="button" class="req-btn-save" onclick="window.print()">
                <i class="bi bi-printer" aria-hidden="true"></i> Print Report
            </button>
        </div>
    </header>

    <form class="f137r-filter" method="GET" action="{{ route('registrar.services.reports-admin.form-137a-monitoring') }}">
        <div class="f137r-filter-grid">
            <div class="f137r-field">
                <label for="f137rStartDate">Requested From</label>
                <input id="f137rStartDate" type="date" name="start_date" value="{{ $filters['startDate'] }}">
            </div>
            <div class="f137r-field">
                <label for="f137rEndDate">Requested To</label>
                <input id="f137rEndDate" type="date" name="end_date" value="{{ $filters['endDate'] }}">
            </div>
            <div class="f137r-field">
                <label for="f137rStatus">Status</label>
                <select id="f137rStatus" name="status">
                    <option value="">All statuses</option>
                    <option value="awaiting_print" {{ $filters['status'] === 'awaiting_print' ? 'selected' : '' }}>Awaiting print</option>
                    <option value="printed" {{ $filters['status'] === 'printed' ? 'selected' : '' }}>Printed</option>
                </select>
            </div>
            <div class="f137r-field">
                <label for="f137rIssuance">Issuance</label>
                <select id="f137rIssuance" name="issuance">
                    <option value="">All issuances</option>
                    <option value="1" {{ $filters['issuance'] === 1 ? 'selected' : '' }}>First issuance</option>
                    <option value="2" {{ $filters['issuance'] === 2 ? 'selected' : '' }}>Second issuance</option>
                </select>
            </div>
            <div class="f137r-field">
                <label for="f137rSearch">Search</label>
                <input id="f137rSearch" type="search" name="q" value="{{ $filters['search'] }}" placeholder="Student number, name, or staff">
            </div>
        </div>
        <div class="f137r-filter-buttons">
            <a class="req-btn-cancel" href="{{ route('registrar.services.reports-admin.form-137a-monitoring') }}">Clear</a>
            <button type="submit" class="req-btn-save">Apply Filters</button>
        </div>
    </form>

    <div class="f137r-summary">
        <div class="f137r-card" style="--accent:#006837;">
            <div class="f137r-card-label">Total Requests</div>
            <div class="f137r-card-value">{{ number_format($summary['total']) }}</div>
        </div>
        <div class="f137r-card" style="--accent:#2563eb;">
            <div class="f137r-card-label">Unique Students</div>
            <div class="f137r-card-value">{{ number_format($summary['unique_students']) }}</div>
        </div>
        <div class="f137r-card" style="--accent:#d97706;">
            <div class="f137r-card-label">Awaiting Print</div>
            <div class="f137r-card-value">{{ number_format($summary['awaiting_print']) }}</div>
        </div>
        <div class="f137r-card" style="--accent:#16803d;">
            <div class="f137r-card-label">Printed</div>
            <div class="f137r-card-value">{{ number_format($summary['printed']) }}</div>
        </div>
    </div>

    <section class="f137r-panel">
        <div class="f137r-panel-title">
            <span>Form 137-A Monitoring List</span>
            <span>{{ number_format($records->count()) }} records</span>
        </div>
        <div class="f137r-table-wrap">
            <table class="f137r-table" id="f137rReportTable">
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Issuance</th>
                        <th>Date Requested</th>
                        <th>Requested By</th>
                        <th>Date Printed</th>
                        <th>Printed By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $record->student_no ?: '-' }}</td>
                            <td class="f137r-student-name">{{ $record->student_name ?: '-' }}</td>
                            <td>{{ $record->program ?: '-' }}</td>
                            <td>{{ (int) $record->issuance_number === 1 ? 'First (1st)' : 'Second (2nd)' }}</td>
                            <td class="f137r-date">{{ $record->requested_at ? \Carbon\Carbon::parse($record->requested_at)->format('M d, Y') : '-' }}</td>
                            <td>{{ $record->requested_by ?: '-' }}</td>
                            <td class="f137r-date">{{ $record->printed_at ? \Carbon\Carbon::parse($record->printed_at)->format('M d, Y') : '-' }}</td>
                            <td>{{ $record->printed_by ?: '-' }}</td>
                            <td>
                                @if($record->printed_at)
                                    <span class="f137r-status is-printed">Printed</span>
                                @else
                                    <span class="f137r-status is-pending">Awaiting print</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="f137r-empty">No Form 137-A requests match the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function f137rExportCsv() {
    var table = document.getElementById('f137rReportTable');
    if (!table) return;

    var rows = Array.prototype.map.call(table.querySelectorAll('tr'), function (row) {
        return Array.prototype.map.call(row.querySelectorAll('th,td'), function (cell) {
            return '"' + String(cell.textContent || '').trim().replace(/"/g, '""') + '"';
        }).join(',');
    });

    var blob = new Blob(['\uFEFF' + rows.join('\r\n')], { type:'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'form-137a-monitoring-report.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}
</script>
@endpush
