@extends('layouts.registrar')

@section('title', 'PLP - Curriculum Year Tracking')
@section('page-title', 'CURRICULUM YEAR TRACKING')

@section('content')
<div class="cyt-page">
    <div class="cyt-header">
        <div>
            <h2>Curriculum Year Tracking</h2>
            <p>Record the year students started and monitor how many students are aligned with approved curriculum structures.</p>
        </div>
        <form method="GET" action="{{ route('registrar.registrar-menu.academic-master.curriculum-year-tracking') }}" class="cyt-filters">
            <select name="course_id">
                <option value="">All Programs</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ (string) $selectedCourseId === (string) $course->id ? 'selected' : '' }}>
                        {{ $course->code ? $course->code . ' - ' : '' }}{{ $course->name ?: $course->description }}
                    </option>
                @endforeach
            </select>
            <select name="started_year">
                <option value="">All Started Years</option>
                @foreach($startedYearOptions as $year)
                    <option value="{{ $year }}" {{ (string) $selectedStartedYear === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="cyt-stats">
        <div class="cyt-stat"><span>{{ number_format($summary['students']) }}</span><label>Students Started</label></div>
        <div class="cyt-stat"><span>{{ number_format($summary['programs']) }}</span><label>Programs</label></div>
        <div class="cyt-stat"><span>{{ number_format($summary['started_years']) }}</span><label>Started Years</label></div>
        <div class="cyt-stat"><span>{{ number_format($summary['curricula']) }}</span><label>Approved Curricula</label></div>
    </div>

    <div class="cyt-card">
        <div class="cyt-card-head">
            <div>
                <h3>Started Year by Program</h3>
                <p>{{ $trackingRows->count() }} tracking row(s)</p>
            </div>
            <div class="cyt-actions">
                <a href="{{ route('registrar.registrar-menu.academic-master.program-file') }}">Program File</a>
                <a href="{{ route('registrar.registrar-menu.academic-master.subject-file') }}">Course File</a>
                <a href="{{ route('registrar.registrar-menu.academic-master.curriculum-file') }}">Curriculum File</a>
                <a href="{{ route('registrar.registrar-menu.scheduling.section-offering') }}">Section Offering</a>
            </div>
        </div>

        @if($trackingRows->count())
            <div class="cyt-table-wrap">
                <table class="cyt-table">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Department</th>
                            <th>Year Started</th>
                            <th>Students Started</th>
                            <th>Approved Curriculum</th>
                            <th>Curriculum Subjects</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trackingRows as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row->program_code ?: 'N/A' }}</strong>
                                    <small>{{ $row->program_name }}</small>
                                </td>
                                <td>{{ $row->department_name ?: 'Unassigned' }}</td>
                                <td><span class="cyt-pill">{{ $row->started_year ?: 'Unassigned' }}</span></td>
                                <td><strong>{{ number_format((int) $row->student_count) }}</strong></td>
                                <td>
                                    @if(count($row->approved_curricula))
                                        <strong>{{ $row->active_curriculum ?: $row->approved_curricula[0] }}</strong>
                                        <small>{{ implode(', ', $row->approved_curricula) }}</small>
                                    @else
                                        <span class="cyt-muted">No approved curriculum</span>
                                    @endif
                                </td>
                                <td>{{ number_format((int) $row->curriculum_subjects) }}</td>
                                <td>
                                    @if(count($row->approved_curricula))
                                        <span class="cyt-badge good">Applied</span>
                                    @else
                                        <span class="cyt-badge warn">Needs Setup</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="cyt-empty">
                <h3>No curriculum tracking rows found</h3>
                <p>Add students and approved curriculum records to start tracking.</p>
            </div>
        @endif
    </div>
</div>

<style>
.cyt-page { max-width: 1280px; margin: 0 auto; padding-bottom: 44px; }
.cyt-header { display:flex; align-items:flex-end; justify-content:space-between; gap:18px; flex-wrap:wrap; margin-bottom:18px; }
.cyt-header h2 { margin:0 0 5px; color:#0f172a; font-size:1.35rem; font-weight:800; }
.cyt-header p { margin:0; color:#64748b; font-size:.88rem; }
.cyt-filters { display:flex; gap:8px; flex-wrap:wrap; }
.cyt-filters select { min-height:38px; border:1px solid #cbd5e1; border-radius:7px; padding:8px 10px; background:#fff; font-size:.84rem; min-width:190px; }
.cyt-filters button, .cyt-actions a { border:0; background:#004d27; color:#fff; border-radius:7px; padding:9px 14px; font-size:.8rem; font-weight:800; text-decoration:none; }
.cyt-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
.cyt-stat { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:16px; }
.cyt-stat span { display:block; color:#0f172a; font-size:1.55rem; font-weight:900; line-height:1; }
.cyt-stat label { display:block; margin-top:6px; color:#64748b; font-size:.72rem; font-weight:800; text-transform:uppercase; }
.cyt-card { background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; }
.cyt-card-head { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:16px 18px; border-bottom:1px solid #e2e8f0; flex-wrap:wrap; }
.cyt-card-head h3 { margin:0; color:#0f172a; font-size:1rem; font-weight:800; }
.cyt-card-head p { margin:4px 0 0; color:#64748b; font-size:.78rem; }
.cyt-actions { display:flex; gap:6px; flex-wrap:wrap; }
.cyt-actions a { background:#f0faf1; color:#004d27; border:1px solid #c8e6c9; padding:7px 11px; }
.cyt-table-wrap { overflow-x:auto; }
.cyt-table { width:100%; border-collapse:collapse; font-size:.83rem; }
.cyt-table th { text-align:left; background:#f8fafc; color:#64748b; font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; padding:10px 12px; white-space:nowrap; }
.cyt-table td { padding:12px; border-top:1px solid #f1f5f9; vertical-align:top; color:#334155; }
.cyt-table strong { display:block; color:#0f172a; font-weight:800; }
.cyt-table small { display:block; color:#64748b; font-size:.74rem; margin-top:2px; }
.cyt-pill { display:inline-flex; background:#dbeafe; color:#1d4ed8; border-radius:999px; padding:4px 10px; font-size:.74rem; font-weight:900; }
.cyt-badge { display:inline-flex; border-radius:999px; padding:4px 10px; font-size:.72rem; font-weight:900; }
.cyt-badge.good { background:#dcfce7; color:#166534; }
.cyt-badge.warn { background:#fef3c7; color:#92400e; }
.cyt-muted { color:#94a3b8; font-size:.78rem; }
.cyt-empty { text-align:center; padding:42px 18px; color:#64748b; }
.cyt-empty h3 { margin:0 0 5px; color:#0f172a; }
.cyt-empty p { margin:0; }
@media(max-width:900px){ .cyt-stats { grid-template-columns:repeat(2,1fr); } .cyt-filters select { min-width:100%; } }
</style>
@endsection
