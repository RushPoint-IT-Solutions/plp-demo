@extends('layouts.registrar')

@section('title', 'PLP - Waiver Cancellation Reports')
@section('page-title', 'WAIVER CANCELLATION REPORTS')

@push('styles')
<style>
    .wcr-page { display:flex; flex-direction:column; gap:16px; }
    .wcr-head { align-items:flex-start; display:flex; gap:16px; justify-content:space-between; }
    .wcr-head h2 { color:#173c29; font-size:1.15rem; font-weight:800; margin:0 0 4px; }
    .wcr-head p { color:#64748b; font-size:.82rem; margin:0; }
    .wcr-actions { display:flex; gap:8px; }
    .wcr-filter,
    .wcr-panel { background:#fff; border:1px solid #dce7e0; border-radius:10px; box-shadow:0 4px 14px rgba(15,81,50,.05); }
    .wcr-filter { padding:16px; }
    .wcr-filter-grid { align-items:end; display:grid; grid-template-columns:180px 180px minmax(240px,1fr) auto; gap:12px; }
    .wcr-field label { color:#425b4b; display:block; font-size:.7rem; font-weight:800; margin-bottom:5px; text-transform:uppercase; }
    .wcr-field input { border:1px solid #cfdcd4; border-radius:6px; color:#243b2e; font-size:.8rem; height:36px; padding:0 10px; width:100%; }
    .wcr-filter-buttons { display:flex; gap:8px; }
    .wcr-summary { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
    .wcr-card { background:#fff; border:1px solid #dce7e0; border-left:4px solid var(--accent,#006837); border-radius:9px; padding:15px 16px; }
    .wcr-card-label { color:#64748b; font-size:.7rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .wcr-card-value { color:#173c29; font-size:1.6rem; font-weight:900; line-height:1.15; margin-top:4px; }
    .wcr-panel { overflow:hidden; }
    .wcr-panel-title { align-items:center; background:#f5faf7; border-bottom:1px solid #dce7e0; color:#17452e; display:flex; font-size:.82rem; font-weight:800; justify-content:space-between; padding:11px 13px; }
    .wcr-table-wrap { overflow:auto; }
    .wcr-table { border-collapse:collapse; width:100%; }
    .wcr-table th { background:#006837; color:#fff; font-size:.7rem; font-weight:800; padding:9px 10px; text-align:left; text-transform:uppercase; white-space:nowrap; }
    .wcr-table td { border-bottom:1px solid #e7eee9; color:#33463a; font-size:.77rem; padding:10px; vertical-align:top; }
    .wcr-table tbody tr:last-child td { border-bottom:0; }
    .wcr-reason { line-height:1.45; min-width:280px; white-space:pre-line; }
    .wcr-missing { color:#9a6700; font-style:italic; }
    .wcr-empty { color:#718079; padding:28px 16px !important; text-align:center; }
    .wcr-print-meta { color:#64748b; display:none; font-size:9pt; margin-top:4px; }
    @media (max-width:900px) {
        .wcr-filter-grid { grid-template-columns:1fr 1fr; }
        .wcr-filter-buttons { grid-column:1/-1; justify-content:flex-end; }
    }
    @media (max-width:620px) {
        .wcr-head { flex-direction:column; }
        .wcr-filter-grid,
        .wcr-summary { grid-template-columns:1fr; }
    }
    @media print {
        @page { size:A4 landscape; margin:10mm; }
        .wcr-actions,
        .wcr-filter { display:none !important; }
        .wcr-print-meta { display:block; }
        .wcr-card,
        .wcr-panel { box-shadow:none; }
        .wcr-table th { background:#e7f2eb !important; color:#173c29 !important; }
    }
</style>
@endpush

@section('content')
<div class="wcr-page">
    <div class="wcr-head">
        <div>
            <h2>Cancellation of Enrollment Requests</h2>
            <p>Students who requested cancellation, including the request date and recorded reason.</p>
            <div class="wcr-print-meta">
                Date Range: {{ $filters['startDate'] ?: 'Beginning' }} to {{ $filters['endDate'] ?: 'Present' }} | Generated: {{ now()->format('F d, Y h:i A') }}
            </div>
        </div>
        <div class="wcr-actions">
            <button type="button" class="req-btn-cancel" onclick="wcrExportCsv()">Export CSV</button>
            <button type="button" class="req-btn-save" onclick="window.print()">Print Report</button>
        </div>
    </div>

    <form class="wcr-filter" method="GET" action="{{ route('registrar.services.reports-admin.waiver-cancellation-reports') }}">
        <div class="wcr-filter-grid">
            <div class="wcr-field">
                <label for="wcrStartDate">Requested From</label>
                <input id="wcrStartDate" type="date" name="start_date" value="{{ $filters['startDate'] }}">
            </div>
            <div class="wcr-field">
                <label for="wcrEndDate">Requested To</label>
                <input id="wcrEndDate" type="date" name="end_date" value="{{ $filters['endDate'] }}">
            </div>
            <div class="wcr-field">
                <label for="wcrSearch">Search</label>
                <input id="wcrSearch" type="search" name="q" value="{{ $filters['search'] }}" placeholder="Student number, student name, or reason">
            </div>
            <div class="wcr-filter-buttons">
                <a class="req-btn-cancel" href="{{ route('registrar.services.reports-admin.waiver-cancellation-reports') }}">Clear</a>
                <button type="submit" class="req-btn-save">Apply Filters</button>
            </div>
        </div>
    </form>

    <div class="wcr-summary">
        <div class="wcr-card" style="--accent:#006837;"><div class="wcr-card-label">Cancellation Requests</div><div class="wcr-card-value">{{ number_format($summary['total_requests']) }}</div></div>
        <div class="wcr-card" style="--accent:#2563eb;"><div class="wcr-card-label">Unique Students</div><div class="wcr-card-value">{{ number_format($summary['unique_students']) }}</div></div>
        <div class="wcr-card" style="--accent:#d97706;"><div class="wcr-card-label">Reasons Recorded</div><div class="wcr-card-value">{{ number_format($summary['with_reason']) }}</div></div>
    </div>

    <section class="wcr-panel">
        <div class="wcr-panel-title"><span>Waiver Cancellation Request List</span><span>{{ number_format($records->count()) }} records</span></div>
        <div class="wcr-table-wrap">
            <table class="wcr-table" id="wcrReportTable">
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Date Requested</th>
                        <th>Reason for Cancellation</th>
                        <th>Program</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ optional($record->student)->student_no ?: '-' }}</td>
                            <td>{{ optional($record->student)->name ?: '-' }}</td>
                            <td>{{ optional($record->created_at)->format('F d, Y') ?: '-' }}</td>
                            <td class="wcr-reason">
                                @if(trim((string) $record->remarks) !== '')
                                    {{ $record->remarks }}
                                @else
                                    <span class="wcr-missing">Reason not recorded</span>
                                @endif
                            </td>
                            <td>{{ $record->program ?: optional($record->canonicalCourse)->code ?: optional($record->canonicalCourse)->name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="wcr-empty">No cancellation requests match the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function wcrExportCsv() {
    var table = document.getElementById('wcrReportTable');
    if (!table) return;

    var rows = Array.prototype.map.call(table.querySelectorAll('tr'), function(row) {
        return Array.prototype.map.call(row.querySelectorAll('th,td'), function(cell) {
            return '"' + String(cell.textContent || '').trim().replace(/"/g, '""') + '"';
        }).join(',');
    });

    var blob = new Blob(['\uFEFF' + rows.join('\r\n')], { type:'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'waiver-cancellation-report.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}
</script>
@endpush
