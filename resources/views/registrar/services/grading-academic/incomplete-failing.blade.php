@extends('layouts.registrar')

@section('title', 'PLP - Incomplete & Failing Grades')
@section('page-title', 'INCOMPLETE & FAILING GRADES')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="ifg-page">
    <div class="ifg-header">
        <div>
            <h2>Monitoring Incomplete and Failing Grades</h2>
            <p>Students with incomplete or failing marks are grouped here for timely action and intervention.</p>
        </div>
        <form class="ifg-search" method="GET" action="{{ route('registrar.services.grading-academic.incomplete-failing') }}">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search student, subject, section">
            <select name="type">
                <option value="">All flagged grades</option>
                <option value="incomplete" {{ $type === 'incomplete' ? 'selected' : '' }}>Incomplete only</option>
                <option value="failing" {{ $type === 'failing' ? 'selected' : '' }}>Failing only</option>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="ifg-stats">
        <div class="ifg-stat">
            <span>{{ number_format($summary['total']) }}</span>
            <label>Total Flags</label>
        </div>
        <div class="ifg-stat warn">
            <span>{{ number_format($summary['incomplete']) }}</span>
            <label>Incomplete</label>
        </div>
        <div class="ifg-stat danger">
            <span>{{ number_format($summary['failing']) }}</span>
            <label>Failing</label>
        </div>
        <div class="ifg-stat">
            <span>{{ number_format($summary['students']) }}</span>
            <label>Students Affected</label>
        </div>
    </div>

    <div class="ifg-card">
        <div class="ifg-card-head">
            <div>
                <h3>Intervention Queue</h3>
                <p>{{ $rows->count() }} record(s) shown</p>
            </div>
            <div class="ifg-filter-links">
                <a class="{{ $type === '' ? 'active' : '' }}" href="{{ route('registrar.services.grading-academic.incomplete-failing', ['q' => $search]) }}">All</a>
                <a class="{{ $type === 'incomplete' ? 'active' : '' }}" href="{{ route('registrar.services.grading-academic.incomplete-failing', ['q' => $search, 'type' => 'incomplete']) }}">Incomplete</a>
                <a class="{{ $type === 'failing' ? 'active' : '' }}" href="{{ route('registrar.services.grading-academic.incomplete-failing', ['q' => $search, 'type' => 'failing']) }}">Failing</a>
            </div>
        </div>

        @if($rows->count())
            <div class="ifg-table-wrap">
                <table class="ifg-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Term</th>
                            <th>Grade</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row->student_name }}</strong>
                                    <small>{{ $row->student_no ?: 'No student no.' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $row->subject_code ?: 'Subject' }}</strong>
                                    <small>{{ $row->subject_name ?: 'No description' }}</small>
                                    @if($row->section)<small>Section: {{ $row->section }}</small>@endif
                                </td>
                                <td>
                                    <strong>{{ $row->school_year ?: 'N/A' }}</strong>
                                    <small>{{ $row->semester ?: 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="ifg-grade">{{ $row->grade ?? 'N/A' }}</span>
                                    @if($row->prelim !== null || $row->midterm !== null || $row->final !== null)
                                        <small>P {{ $row->prelim ?? '-' }} | M {{ $row->midterm ?? '-' }} | F {{ $row->final ?? '-' }}</small>
                                    @endif
                                </td>
                                <td><span class="ifg-badge {{ $row->risk_type === 'Incomplete' ? 'warn' : 'danger' }}">{{ $row->risk_type }}</span></td>
                                <td>
                                    <span>{{ $row->source }}</span>
                                    @if($row->faculty)<small>{{ $row->faculty }}</small>@endif
                                    @if($row->remarks)<small>{{ $row->remarks }}</small>@endif
                                </td>
                                <td>
                                    @if($row->student_id)
                                        <a class="ifg-action" href="{{ route('registrar.registrar-menu.student-mgmt.academic-record.show', ['student' => $row->student_id]) }}">Open Record</a>
                                    @else
                                        <span class="ifg-muted">No linked student</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="ifg-empty">
                <h3>No flagged grades found</h3>
                <p>There are no incomplete or failing marks for the current filter.</p>
            </div>
        @endif
    </div>
</div>

<style>
.ifg-page { max-width: 1280px; margin: 0 auto; padding-bottom: 44px; }
.ifg-header {
    display: flex; justify-content: space-between; align-items: flex-end; gap: 18px;
    margin-bottom: 18px; flex-wrap: wrap;
}
.ifg-header h2 { margin: 0 0 5px; font-size: 1.35rem; color: #0f172a; font-weight: 800; }
.ifg-header p { margin: 0; color: #64748b; font-size: .88rem; }
.ifg-search { display: flex; gap: 8px; flex-wrap: wrap; }
.ifg-search input, .ifg-search select {
    border: 1px solid #cbd5e1; border-radius: 7px; padding: 9px 11px;
    font-size: .84rem; background: #fff; min-height: 38px;
}
.ifg-search input { min-width: 250px; }
.ifg-search button, .ifg-action {
    border: 0; background: #004d27; color: #fff; border-radius: 7px;
    padding: 9px 14px; font-size: .8rem; font-weight: 800; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center;
}
.ifg-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
.ifg-stat { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; }
.ifg-stat span { display: block; font-size: 1.55rem; font-weight: 900; color: #0f172a; line-height: 1; }
.ifg-stat label { display: block; margin-top: 6px; color: #64748b; font-size: .74rem; font-weight: 800; text-transform: uppercase; }
.ifg-stat.warn span { color: #92400e; }
.ifg-stat.danger span { color: #991b1b; }
.ifg-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
.ifg-card-head {
    display: flex; justify-content: space-between; align-items: center; gap: 12px;
    padding: 16px 18px; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap;
}
.ifg-card-head h3 { margin: 0; color: #0f172a; font-size: 1rem; font-weight: 800; }
.ifg-card-head p { margin: 4px 0 0; color: #64748b; font-size: .78rem; }
.ifg-filter-links { display: flex; gap: 6px; }
.ifg-filter-links a {
    border: 1px solid #cbd5e1; color: #475569; border-radius: 999px;
    padding: 6px 12px; font-size: .76rem; font-weight: 800; text-decoration: none;
}
.ifg-filter-links a.active { background: #004d27; border-color: #004d27; color: #fff; }
.ifg-table-wrap { overflow-x: auto; }
.ifg-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.ifg-table th {
    text-align: left; background: #f8fafc; color: #64748b; font-size: .72rem;
    text-transform: uppercase; letter-spacing: .04em; padding: 10px 12px; white-space: nowrap;
}
.ifg-table td { padding: 12px; border-top: 1px solid #f1f5f9; vertical-align: top; color: #334155; }
.ifg-table strong { display: block; color: #0f172a; font-weight: 800; }
.ifg-table small { display: block; color: #64748b; font-size: .74rem; margin-top: 2px; }
.ifg-grade { font-weight: 900; color: #0f172a; }
.ifg-badge {
    display: inline-flex; align-items: center; border-radius: 999px; padding: 4px 10px;
    font-size: .72rem; font-weight: 900;
}
.ifg-badge.warn { background: #fef3c7; color: #92400e; }
.ifg-badge.danger { background: #fee2e2; color: #991b1b; }
.ifg-muted { color: #94a3b8; font-size: .78rem; }
.ifg-empty { text-align: center; padding: 42px 18px; color: #64748b; }
.ifg-empty h3 { margin: 0 0 5px; color: #0f172a; }
.ifg-empty p { margin: 0; }
@media (max-width: 900px) {
    .ifg-stats { grid-template-columns: repeat(2, 1fr); }
    .ifg-search input { min-width: 100%; }
}
</style>
@endsection
