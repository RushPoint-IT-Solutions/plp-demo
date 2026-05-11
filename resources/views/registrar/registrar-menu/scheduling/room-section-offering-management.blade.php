@extends('layouts.registrar')

@section('title', 'PLP - Room & Section Offering Management')
@section('page-title', 'ROOM & SECTION OFFERING MANAGEMENT')

@section('content')
@php
    $selectedSchoolYear = (string) ($schoolYear ?? '');
    $selectedSemester = (string) ($semester ?? '');
    $selectedCourseId = (int) ($courseId ?? 0);
@endphp

<div class="pf-page rsom-page">
    <style>
        .rsom-page .rsom-actions { display:flex; flex-wrap:wrap; gap:10px; margin:0 0 16px; }
        .rsom-page .rsom-action { align-items:center; background:#146c43; border-radius:7px; color:#fff; display:inline-flex; font-weight:800; min-height:38px; padding:8px 13px; text-decoration:none; }
        .rsom-page .rsom-action.secondary { background:#eff7f2; color:#145c39; border:1px solid #cfe1d5; }
        .rsom-page .rsom-summary { display:grid; grid-template-columns:repeat(5, minmax(130px, 1fr)); gap:12px; margin-bottom:16px; }
        .rsom-page .rsom-stat { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:12px 14px; }
        .rsom-page .rsom-stat-label { color:#607264; display:block; font-size:.78rem; font-weight:800; text-transform:uppercase; }
        .rsom-page .rsom-stat-value { color:#123822; display:block; font-size:1.45rem; font-weight:900; margin-top:4px; }
        .rsom-page .rsom-filters { background:#fff; border:1px solid #dfe8e2; border-radius:8px; margin-bottom:16px; padding:14px; }
        .rsom-page .rsom-filter-grid { display:grid; grid-template-columns:1fr 1fr 1.2fr 1fr 1.4fr auto; gap:12px; align-items:end; }
        .rsom-page .rsom-field label { color:#46564a; display:block; font-size:.78rem; font-weight:800; margin-bottom:5px; }
        .rsom-page .rsom-input, .rsom-page .rsom-select { background:#fff; border:1px solid #cfd9d2; border-radius:7px; color:#143521; min-height:38px; padding:8px 10px; width:100%; }
        .rsom-page .rsom-section-title { color:#143521; font-size:1.02rem; font-weight:900; margin:18px 0 10px; }
        .rsom-page .rsom-muted { color:#66756b; font-size:.82rem; }
        .rsom-page .rsom-pill { background:#eef7f1; border-radius:999px; color:#17633a; display:inline-flex; font-size:.76rem; font-weight:800; margin:2px 4px 2px 0; padding:4px 8px; }
        .rsom-page .rsom-pill.warn { background:#fff5d8; color:#8a5b00; }
        .rsom-page .rsom-empty { color:#607264; padding:24px; text-align:center; }
        .rsom-page .rsom-schedule-line { margin-bottom:3px; }
        @media (max-width:1180px) {
            .rsom-page .rsom-summary { grid-template-columns:repeat(2, minmax(130px, 1fr)); }
            .rsom-page .rsom-filter-grid { grid-template-columns:1fr 1fr; }
        }
        @media (max-width:640px) {
            .rsom-page .rsom-summary, .rsom-page .rsom-filter-grid { grid-template-columns:1fr; }
        }
    </style>

    <div class="rsom-actions">
        <a class="rsom-action" href="{{ route('registrar.registrar-menu.scheduling.room-file') }}">Manage Rooms</a>
        <a class="rsom-action secondary" href="{{ route('registrar.registrar-menu.scheduling.section-offering') }}">Manage Section Offering</a>
        <a class="rsom-action secondary" href="{{ route('registrar.registrar-menu.scheduling.class-schedule-preparation') }}">Prepare Class Schedule</a>
    </div>

    <div class="rsom-summary">
        <div class="rsom-stat">
            <span class="rsom-stat-label">Rooms</span>
            <span class="rsom-stat-value">{{ number_format((int) ($summary['rooms'] ?? 0)) }}</span>
        </div>
        <div class="rsom-stat">
            <span class="rsom-stat-label">Total Capacity</span>
            <span class="rsom-stat-value">{{ number_format((int) ($summary['capacity'] ?? 0)) }}</span>
        </div>
        <div class="rsom-stat">
            <span class="rsom-stat-label">Sections</span>
            <span class="rsom-stat-value">{{ number_format((int) ($summary['sections'] ?? 0)) }}</span>
        </div>
        <div class="rsom-stat">
            <span class="rsom-stat-label">Scheduled Subjects</span>
            <span class="rsom-stat-value">{{ number_format((int) ($summary['scheduled_subjects'] ?? 0)) }}</span>
        </div>
        <div class="rsom-stat">
            <span class="rsom-stat-label">Offered Subjects</span>
            <span class="rsom-stat-value">{{ number_format((int) ($summary['subjects'] ?? 0)) }}</span>
        </div>
    </div>

    <form class="rsom-filters" method="GET" action="{{ route('registrar.registrar-menu.scheduling.room-section-offering-management') }}">
        <div class="rsom-filter-grid">
            <div class="rsom-field">
                <label for="rsomSchoolYear">School Year</label>
                <select class="rsom-select" id="rsomSchoolYear" name="school_year">
                    <option value="">All School Years</option>
                    @foreach($schoolYearOptions as $option)
                        <option value="{{ $option['value'] }}" {{ $selectedSchoolYear === (string) $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rsom-field">
                <label for="rsomSemester">Semester</label>
                <select class="rsom-select" id="rsomSemester" name="semester">
                    <option value="">All Semesters</option>
                    @foreach($semesterOptions as $option)
                        <option value="{{ $option['value'] }}" {{ $selectedSemester === (string) $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rsom-field">
                <label for="rsomCourse">Program</label>
                <select class="rsom-select" id="rsomCourse" name="course_id">
                    <option value="0">All Programs</option>
                    @foreach($courseOptions as $option)
                        <option value="{{ $option['id'] }}" {{ $selectedCourseId === (int) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rsom-field">
                <label for="rsomSection">Section</label>
                <input class="rsom-input" id="rsomSection" type="text" name="section" value="{{ $sectionQuery ?? '' }}" placeholder="Section">
            </div>
            <div class="rsom-field">
                <label for="rsomSearch">Search</label>
                <input class="rsom-input" id="rsomSearch" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Subject, room, professor">
            </div>
            <button type="submit" class="pf-btn-new">Filter</button>
        </div>
    </form>

    <div class="rsom-section-title">Room Details & Program Assignment</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Floor</th>
                    <th>Capacity</th>
                    <th>Building / Hallway</th>
                    <th>Assigned Programs</th>
                    <th>Updated By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td>Room #{{ $room['room_number'] }}</td>
                        <td>{{ $room['floor_number'] }}</td>
                        <td>{{ number_format((int) $room['capacity']) }}</td>
                        <td>{{ $room['location_label'] !== '' ? $room['location_label'] : 'Unassigned' }}</td>
                        <td>{{ $room['program_label'] }}</td>
                        <td>{{ $room['updated_by'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="rsom-empty">No room records yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rsom-section-title">Section Scheduling</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Term</th>
                    <th>Program / Section</th>
                    <th>Subjects</th>
                    <th>Rooms Used</th>
                    <th>Faculty</th>
                    <th>Schedule Preview</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $section)
                    <tr>
                        <td>
                            <div>{{ $section['school_year'] !== '' ? $section['school_year'] : 'N/A' }}</div>
                            <div class="rsom-muted">{{ $section['semester'] !== '' ? $section['semester'] : 'N/A' }}</div>
                        </td>
                        <td>
                            <div>{{ $section['course_code'] !== '' ? $section['course_code'] : 'N/A' }}</div>
                            <div class="rsom-muted">{{ $section['section'] !== '' ? $section['section'] : 'No section' }}</div>
                        </td>
                        <td>
                            <span class="rsom-pill">{{ $section['scheduled_count'] }} scheduled</span>
                            <span class="rsom-pill warn">{{ max(0, $section['subject_count'] - $section['scheduled_count']) }} pending</span>
                        </td>
                        <td>
                            @forelse($section['rooms'] as $room)
                                <span class="rsom-pill">Room #{{ $room }}</span>
                            @empty
                                <span class="rsom-muted">TBA</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse($section['faculty'] as $facultyName)
                                <span class="rsom-pill">{{ $facultyName }}</span>
                            @empty
                                <span class="rsom-muted">TBA</span>
                            @endforelse
                        </td>
                        <td>
                            @foreach($section['schedule_samples'] as $sample)
                                <div class="rsom-schedule-line">
                                    <strong>{{ $sample['subject'] !== '' ? $sample['subject'] : 'Subject' }}:</strong>
                                    <span>{{ $sample['schedule'] }}</span>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="rsom-empty">No section offering records matched the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
