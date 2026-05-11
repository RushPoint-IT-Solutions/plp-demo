@extends('layouts.registrar')

@section('title', 'PLP - Coordination with Deans & Faculty')
@section('page-title', 'COORDINATION WITH DEANS & FACULTY')

@section('content')
<div class="pf-page cdf-page">
    <style>
        .cdf-page .cdf-summary { display:grid; grid-template-columns:repeat(5, minmax(130px, 1fr)); gap:12px; margin-bottom:16px; }
        .cdf-page .cdf-stat { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:12px 14px; }
        .cdf-page .cdf-stat-label { color:#607264; display:block; font-size:.78rem; font-weight:800; text-transform:uppercase; }
        .cdf-page .cdf-stat-value { color:#123822; display:block; font-size:1.45rem; font-weight:900; margin-top:4px; }
        .cdf-page .cdf-filters { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:14px; margin-bottom:16px; }
        .cdf-page .cdf-filter-grid { display:grid; grid-template-columns:1fr 1fr 1.3fr auto; gap:12px; align-items:end; }
        .cdf-page label { color:#46564a; display:block; font-size:.78rem; font-weight:800; margin-bottom:5px; }
        .cdf-page select { background:#fff; border:1px solid #cfd9d2; border-radius:7px; color:#143521; min-height:38px; padding:8px 10px; width:100%; }
        .cdf-page .cdf-title { color:#143521; font-size:1.02rem; font-weight:900; margin:18px 0 10px; }
        .cdf-page .cdf-pill { background:#eef7f1; border-radius:999px; color:#17633a; display:inline-flex; font-size:.76rem; font-weight:800; padding:4px 8px; }
        .cdf-page .cdf-pill.warn { background:#fff5d8; color:#8a5b00; }
        .cdf-page .cdf-empty { color:#607264; padding:24px; text-align:center; }
        @media (max-width:1180px) {
            .cdf-page .cdf-summary { grid-template-columns:repeat(2, minmax(130px, 1fr)); }
            .cdf-page .cdf-filter-grid { grid-template-columns:1fr; }
        }
    </style>

    <div class="cdf-summary">
        <div class="cdf-stat"><span class="cdf-stat-label">Programs</span><span class="cdf-stat-value">{{ number_format((int) $summary['programs']) }}</span></div>
        <div class="cdf-stat"><span class="cdf-stat-label">Sections</span><span class="cdf-stat-value">{{ number_format((int) $summary['sections']) }}</span></div>
        <div class="cdf-stat"><span class="cdf-stat-label">Subjects</span><span class="cdf-stat-value">{{ number_format((int) $summary['subjects']) }}</span></div>
        <div class="cdf-stat"><span class="cdf-stat-label">Pending Schedule</span><span class="cdf-stat-value">{{ number_format((int) $summary['pending']) }}</span></div>
        <div class="cdf-stat"><span class="cdf-stat-label">Faculty</span><span class="cdf-stat-value">{{ number_format((int) $summary['faculty']) }}</span></div>
    </div>

    <form class="cdf-filters" method="GET" action="{{ route('registrar.registrar-menu.scheduling.coordination-deans-faculty') }}">
        <div class="cdf-filter-grid">
            <div>
                <label for="cdfSchoolYear">School Year</label>
                <select id="cdfSchoolYear" name="school_year">
                    <option value="">All School Years</option>
                    @foreach($schoolYearOptions as $option)
                        <option value="{{ $option['value'] }}" {{ $schoolYear === (string) $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="cdfSemester">Semester</label>
                <select id="cdfSemester" name="semester">
                    <option value="">All Semesters</option>
                    @foreach($semesterOptions as $option)
                        <option value="{{ $option['value'] }}" {{ $semester === (string) $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="cdfCourse">Program</label>
                <select id="cdfCourse" name="course_id">
                    <option value="0">All Programs</option>
                    @foreach($courseOptions as $option)
                        <option value="{{ $option['id'] }}" {{ (int) $courseId === (int) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="pf-btn-new">Filter</button>
        </div>
    </form>

    <div class="cdf-title">Academic Planning Alignment</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Sections</th>
                    <th>Subjects</th>
                    <th>Scheduled</th>
                    <th>Pending</th>
                    <th>Faculty Assigned</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                    <tr>
                        <td>{{ $program['course_code'] !== '' ? $program['course_code'] : 'N/A' }} · {{ $program['course_name'] }}</td>
                        <td>{{ number_format((int) $program['sections']) }}</td>
                        <td>{{ number_format((int) $program['subjects']) }}</td>
                        <td><span class="cdf-pill">{{ number_format((int) $program['scheduled']) }}</span></td>
                        <td><span class="cdf-pill warn">{{ number_format((int) $program['pending']) }}</span></td>
                        <td>{{ number_format((int) $program['faculty']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="cdf-empty">No planning records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="cdf-title">Faculty Load Signals</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Faculty</th>
                    <th>Subjects</th>
                    <th>Sections</th>
                    <th>Rooms</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facultyLoads as $load)
                    <tr>
                        <td>{{ $load['faculty'] }}</td>
                        <td>{{ number_format((int) $load['subjects']) }}</td>
                        <td>{{ number_format((int) $load['sections']) }}</td>
                        <td>{{ number_format((int) $load['rooms']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="cdf-empty">No faculty assignments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="cdf-title">Slot Pressure for Dean/Faculty Review</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Total Slots</th>
                    <th>Enrolled</th>
                    <th>Utilization</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slotPressure as $slot)
                    <tr>
                        <td>{{ $slot['course'] !== '' ? $slot['course'] : 'N/A' }}</td>
                        <td>{{ $slot['section'] }}</td>
                        <td>{{ $slot['subject'] }}</td>
                        <td>{{ number_format((int) $slot['total']) }}</td>
                        <td>{{ number_format((int) $slot['enrolled']) }}</td>
                        <td><span class="cdf-pill {{ $slot['percent'] >= 90 ? 'warn' : '' }}">{{ $slot['percent'] }}%</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="cdf-empty">No slot monitoring data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
