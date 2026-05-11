@extends('layouts.registrar')

@section('title', 'PLP - Alumni Tracker')
@section('page-title', 'ALUMNI TRACKER')
@section('body-class', 'page-alumni-tracker')

@push('styles')
<style>
.at-page { padding: 24px 28px; }
.at-hero { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin-bottom:18px; }
.at-title { margin:0; color:#173b28; font-size:24px; font-weight:900; }
.at-subtitle { margin:4px 0 0; color:#667085; font-size:13px; }
.at-actions { display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
.at-btn { height:36px; border-radius:7px; border:1px solid #d8e2dc; padding:0 13px; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:7px; text-decoration:none; cursor:pointer; background:#fff; color:#23513b; }
.at-btn:hover { color:#0f3b24; border-color:#a7c7b5; }
.at-btn-primary { background:#006837; color:#fff; border-color:#006837; }
.at-btn-primary:hover { color:#fff; background:#00562e; }
.at-summary { display:grid; grid-template-columns:repeat(5, minmax(130px, 1fr)); gap:12px; margin-bottom:16px; }
.at-stat { background:#fff; border:1px solid #e8eee9; border-radius:8px; padding:14px 15px; box-shadow:0 1px 4px rgba(15, 59, 36, .06); }
.at-stat span { display:block; color:#667085; font-size:11px; font-weight:800; text-transform:uppercase; }
.at-stat strong { display:block; color:#173b28; font-size:25px; line-height:1.1; margin-top:5px; }
.at-shell { display:grid; grid-template-columns:minmax(0, 1fr) 260px; gap:14px; }
.at-panel { background:#fff; border:1px solid #e8eee9; border-radius:8px; box-shadow:0 1px 4px rgba(15, 59, 36, .06); }
.at-filter-panel { padding:14px; margin-bottom:14px; }
.at-filter-grid { display:grid; grid-template-columns:1.5fr repeat(5, minmax(120px, 1fr)); gap:10px; align-items:end; }
.at-field label { display:block; margin-bottom:5px; color:#53645b; font-size:11px; font-weight:800; text-transform:uppercase; }
.at-input, .at-select { width:100%; height:36px; border:1px solid #d8e2dc; border-radius:7px; padding:0 10px; color:#1f2937; font-size:13px; background:#fff; }
.at-table-wrap { overflow:auto; }
.at-table { width:100%; min-width:980px; border-collapse:collapse; }
.at-table th { background:#f7faf8; color:#465a50; font-size:11px; text-transform:uppercase; letter-spacing:.02em; padding:11px 12px; border-bottom:1px solid #e8eee9; text-align:left; white-space:nowrap; }
.at-table td { padding:12px; border-bottom:1px solid #f0f3f1; color:#344054; font-size:13px; vertical-align:top; }
.at-name { color:#0f5132; font-weight:800; text-decoration:none; }
.at-muted { color:#8a988f; font-size:12px; }
.at-pill { display:inline-flex; align-items:center; height:24px; border-radius:999px; padding:0 9px; font-size:11px; font-weight:800; }
.at-pill-active { background:#e9f8ef; color:#08783d; }
.at-pill-suspended { background:#fff1f2; color:#b42318; }
.at-side { padding:14px; align-self:start; }
.at-side h3 { margin:0 0 12px; font-size:14px; color:#173b28; font-weight:900; }
.at-breakdown { display:grid; gap:9px; }
.at-breakdown-row { display:grid; grid-template-columns:1fr auto; gap:10px; align-items:center; }
.at-breakdown-name { color:#344054; font-size:12px; font-weight:800; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.at-breakdown-count { color:#0f5132; font-size:12px; font-weight:900; }
.at-bar { grid-column:1 / -1; height:7px; border-radius:999px; background:#edf4ef; overflow:hidden; }
.at-bar span { display:block; height:100%; background:#006837; border-radius:999px; }
.at-empty { padding:38px 18px; text-align:center; color:#667085; }
@media (max-width: 1180px) {
    .at-summary { grid-template-columns:repeat(3, minmax(130px, 1fr)); }
    .at-shell { grid-template-columns:1fr; }
    .at-filter-grid { grid-template-columns:repeat(2, minmax(160px, 1fr)); }
}
@media (max-width: 680px) {
    .at-page { padding:18px 14px; }
    .at-hero { display:block; }
    .at-actions { justify-content:flex-start; margin-top:12px; }
    .at-summary, .at-filter-grid { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')
<div class="at-page">
    <div class="at-hero">
        <div>
            <h1 class="at-title">Graduate Student Alumni Tracker</h1>
            <p class="at-subtitle">Monitor tagged graduates, graduation details, SO records, and account status.</p>
        </div>
        <div class="at-actions">
            <a href="{{ route('registrar.services.reports-admin.tagging-of-graduates') }}" class="at-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Tag Graduates
            </a>
            <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records', ['graduate' => 'graduates']) }}" class="at-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Graduate List
            </a>
            <button type="button" class="at-btn at-btn-primary" id="atExportBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                Export CSV
            </button>
        </div>
    </div>

    <div class="at-summary">
        <div class="at-stat"><span>Total Alumni</span><strong>{{ number_format((int) ($alumniSummary['total'] ?? 0)) }}</strong></div>
        <div class="at-stat"><span>Active Accounts</span><strong>{{ number_format((int) ($alumniSummary['active'] ?? 0)) }}</strong></div>
        <div class="at-stat"><span>Suspended</span><strong>{{ number_format((int) ($alumniSummary['suspended'] ?? 0)) }}</strong></div>
        <div class="at-stat"><span>Programs</span><strong>{{ number_format((int) ($alumniSummary['programs'] ?? 0)) }}</strong></div>
        <div class="at-stat"><span>Grad Years</span><strong>{{ number_format((int) ($alumniSummary['years'] ?? 0)) }}</strong></div>
    </div>

    <div class="at-shell">
        <div>
            <div class="at-panel at-filter-panel">
                <div class="at-filter-grid">
                    <div class="at-field">
                        <label for="atSearchInput">Search</label>
                        <input id="atSearchInput" type="search" class="at-input" placeholder="Student no, name, SO number, contact">
                    </div>
                    <div class="at-field">
                        <label for="atProgram">Program</label>
                        <select id="atProgram" class="at-select">
                            <option value="">All Programs</option>
                            @foreach(($alumniPrograms ?? []) as $program)
                                <option value="{{ $program }}">{{ $program }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="at-field">
                        <label for="atGraduationYear">Grad Year</label>
                        <select id="atGraduationYear" class="at-select">
                            <option value="">All Years</option>
                            @foreach(($alumniGraduationYears ?? []) as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="at-field">
                        <label for="atSchoolYear">School Year</label>
                        <select id="atSchoolYear" class="at-select">
                            <option value="">All SY</option>
                            @foreach(($alumniSchoolYears ?? []) as $schoolYear)
                                <option value="{{ $schoolYear }}">{{ $schoolYear }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="at-field">
                        <label for="atTerm">Term</label>
                        <select id="atTerm" class="at-select">
                            <option value="">All Terms</option>
                            @foreach(($alumniTerms ?? []) as $term)
                                <option value="{{ $term }}">{{ $term }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="at-field">
                        <label for="atStatus">Status</label>
                        <select id="atStatus" class="at-select">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="at-panel at-table-wrap">
                <table class="at-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Program</th>
                            <th>Graduated</th>
                            <th>School Year / Term</th>
                            <th>SO Details</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="atTableBody"></tbody>
                </table>
            </div>
        </div>

        <aside class="at-panel at-side">
            <h3>Program Breakdown</h3>
            <div class="at-breakdown">
                @php($maxProgramCount = max(1, collect($alumniProgramBreakdown ?? [])->max('count') ?: 1))
                @forelse(($alumniProgramBreakdown ?? []) as $row)
                    <div class="at-breakdown-row">
                        <div class="at-breakdown-name" title="{{ $row['program'] }}">{{ $row['program'] }}</div>
                        <div class="at-breakdown-count">{{ number_format((int) $row['count']) }}</div>
                        <div class="at-bar"><span style="width:{{ min(100, round(((int) $row['count'] / $maxProgramCount) * 100)) }}%;"></span></div>
                    </div>
                @empty
                    <div class="at-empty">No graduate records yet.</div>
                @endforelse
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
var atRows = @json($alumniRows ?? []);

function atEscapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(ch) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
    });
}

function atNormalize(value) {
    return String(value || '').trim().toLowerCase();
}

function atFilteredRows() {
    var search = atNormalize(document.getElementById('atSearchInput').value);
    var program = document.getElementById('atProgram').value;
    var graduationYear = document.getElementById('atGraduationYear').value;
    var schoolYear = document.getElementById('atSchoolYear').value;
    var term = document.getElementById('atTerm').value;
    var status = document.getElementById('atStatus').value;

    return atRows.filter(function(row) {
        var haystack = [
            row.studentNo, row.studentName, row.program, row.programName,
            row.soNumber, row.contact, row.dateGraduated
        ].join(' ');

        var matchesSearch = !search || atNormalize(haystack).indexOf(search) !== -1;
        var matchesProgram = !program || row.program === program;
        var matchesGradYear = !graduationYear || row.graduationYear === graduationYear;
        var matchesSchoolYear = !schoolYear || row.schoolYear === schoolYear;
        var matchesTerm = !term || row.term === term;
        var matchesStatus = !status || (status === 'suspended' ? row.isSuspended : !row.isSuspended);

        return matchesSearch && matchesProgram && matchesGradYear && matchesSchoolYear && matchesTerm && matchesStatus;
    });
}

function atRenderTable() {
    var body = document.getElementById('atTableBody');
    var rows = atFilteredRows();

    if (!rows.length) {
        body.innerHTML = '<tr><td colspan="8"><div class="at-empty">No alumni records matched the selected filters.</div></td></tr>';
        return;
    }

    body.innerHTML = rows.map(function(row) {
        var statusClass = row.isSuspended ? 'at-pill-suspended' : 'at-pill-active';
        var statusText = row.isSuspended ? 'Suspended' : 'Active';
        var soText = row.soNumber || row.soDate
            ? atEscapeHtml(row.soNumber || '-') + '<div class="at-muted">' + atEscapeHtml(row.soDate || '') + '</div>'
            : '<span class="at-muted">No SO details</span>';

        return '' +
            '<tr>' +
                '<td><a class="at-name" href="' + atEscapeHtml(row.profileUrl) + '">' + atEscapeHtml(row.studentName) + '</a><div class="at-muted">' + atEscapeHtml(row.studentNo) + '</div></td>' +
                '<td>' + atEscapeHtml(row.program) + '<div class="at-muted">' + atEscapeHtml(row.programName) + '</div></td>' +
                '<td>' + atEscapeHtml(row.dateGraduated || '-') + '<div class="at-muted">' + atEscapeHtml(row.graduationYear || '') + '</div></td>' +
                '<td>SY ' + atEscapeHtml(row.schoolYear || '-') + '<div class="at-muted">' + atEscapeHtml(row.term || '-') + '</div></td>' +
                '<td>' + soText + '</td>' +
                '<td>' + atEscapeHtml(row.contact || '-') + '</td>' +
                '<td><span class="at-pill ' + statusClass + '">' + statusText + '</span></td>' +
                '<td><a class="at-btn" href="' + atEscapeHtml(row.profileUrl) + '">Profile</a></td>' +
            '</tr>';
    }).join('');
}

function atExportCsv() {
    var rows = atFilteredRows();
    if (!rows.length) {
        alert('No alumni records to export.');
        return;
    }

    var csvRows = [['Student No', 'Student Name', 'Program', 'Program Name', 'Date Graduated', 'Graduation Year', 'School Year', 'Term', 'SO Number', 'SO Date', 'Contact', 'Status']];
    rows.forEach(function(row) {
        csvRows.push([
            row.studentNo || '', row.studentName || '', row.program || '', row.programName || '',
            row.dateGraduated || '', row.graduationYear || '', row.schoolYear || '', row.term || '',
            row.soNumber || '', row.soDate || '', row.contact || '', row.isSuspended ? 'Suspended' : 'Active'
        ]);
    });

    var csvContent = csvRows.map(function(cols) {
        return cols.map(function(value) {
            return '"' + String(value).replace(/"/g, '""') + '"';
        }).join(',');
    }).join('\n');

    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'alumni-tracker-' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

document.addEventListener('DOMContentLoaded', function() {
    var params = new URLSearchParams(window.location.search);
    var querySearch = params.get('q');
    if (querySearch && document.getElementById('atSearchInput')) {
        document.getElementById('atSearchInput').value = querySearch;
    }

    ['atSearchInput', 'atProgram', 'atGraduationYear', 'atSchoolYear', 'atTerm', 'atStatus'].forEach(function(id) {
        var element = document.getElementById(id);
        if (element) {
            element.addEventListener(id === 'atSearchInput' ? 'input' : 'change', atRenderTable);
        }
    });

    document.getElementById('atExportBtn').addEventListener('click', atExportCsv);
    atRenderTable();
});
</script>
@endpush
