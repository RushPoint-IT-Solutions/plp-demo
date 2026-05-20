@extends('layouts.registrar')

@section('title', 'PLP - Room & Section Offering Management')
@section('page-title', 'ROOM & SECTION OFFERING MANAGEMENT')

@section('content')
@php
    $selectedSchoolYear = (string) ($schoolYear ?? '');
    $selectedSemester = (string) ($semester ?? '');
    $selectedCourseId = (int) ($courseId ?? 0);
    $selectedStatus = (string) ($statusFilter ?? '');
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
        .rsom-page .rsom-filter-grid { display:grid; grid-template-columns:1fr 1fr 1.35fr 1fr 1.15fr 1.45fr auto auto; gap:12px; align-items:end; }
        .rsom-page .rsom-field label { color:#46564a; display:block; font-size:.78rem; font-weight:800; margin-bottom:5px; }
        .rsom-page .rsom-input, .rsom-page .rsom-select { background:#fff; border:1px solid #cfd9d2; border-radius:7px; color:#143521; min-height:38px; padding:8px 10px; width:100%; }
        .rsom-page .rsom-filter-hint { color:#66756b; display:block; font-size:.72rem; line-height:1.35; margin-top:5px; }
        .rsom-page .rsom-section-title { color:#143521; font-size:1.02rem; font-weight:900; margin:18px 0 10px; }
        .rsom-page .rsom-muted { color:#66756b; font-size:.82rem; }
        .rsom-page .rsom-pill { background:#eef7f1; border-radius:999px; color:#17633a; display:inline-flex; font-size:.76rem; font-weight:800; margin:2px 4px 2px 0; padding:4px 8px; }
        .rsom-page .rsom-pill.warn { background:#fff5d8; color:#8a5b00; }
        .rsom-page .rsom-pill.danger { background:#fdecec; color:#9f1d1d; }
        .rsom-page .rsom-pill.neutral { background:#eef2f7; color:#334155; }
        .rsom-page .rsom-note { background:#f3fbf6; border:1px solid #d7eadf; border-radius:8px; color:#315a3f; font-size:.84rem; line-height:1.45; margin-bottom:16px; padding:10px 12px; }
        .rsom-page .rsom-empty { color:#607264; padding:24px; text-align:center; }
        .rsom-page .rsom-schedule-line { margin-bottom:3px; }
        .rsom-page .rsom-pager { align-items:center; display:flex; flex-wrap:wrap; gap:8px; justify-content:flex-end; margin:10px 0 16px; }
        .rsom-page .rsom-page-link { background:#fff; border:1px solid #cfd9d2; border-radius:7px; color:#145c39; font-size:.82rem; font-weight:800; padding:7px 10px; text-decoration:none; }
        .rsom-page .rsom-page-link.disabled { color:#9aa7a0; pointer-events:none; }
        .rsom-page .rsom-page-count { color:#607264; font-size:.82rem; font-weight:700; }
        .rsom-page .rsom-mini-btn { background:#fff; border:1px solid #b9d6c5; border-radius:7px; color:#145c39; cursor:pointer; font-size:.78rem; font-weight:900; min-height:32px; padding:6px 10px; }
        .rsom-page .rsom-modal-backdrop { align-items:center; background:rgba(15, 23, 42, .45); display:none; inset:0; justify-content:center; padding:18px; position:fixed; z-index:1100; }
        .rsom-page .rsom-modal-backdrop.is-open { display:flex; }
        .rsom-page .rsom-modal { background:#fff; border-radius:8px; box-shadow:0 22px 70px rgba(15, 23, 42, .24); max-height:88vh; max-width:860px; overflow:auto; width:100%; }
        .rsom-page .rsom-modal-head { align-items:flex-start; border-bottom:1px solid #e2e8f0; display:flex; gap:12px; justify-content:space-between; padding:18px 20px; }
        .rsom-page .rsom-modal-title { color:#143521; font-size:1.08rem; font-weight:900; margin:0; }
        .rsom-page .rsom-modal-close { background:#f8fafc; border:1px solid #d8e0da; border-radius:7px; color:#334155; cursor:pointer; font-size:1rem; font-weight:900; height:34px; line-height:1; width:34px; }
        .rsom-page .rsom-modal-body { padding:18px 20px 20px; }
        .rsom-page .rsom-detail-grid { display:grid; grid-template-columns:repeat(3, minmax(140px, 1fr)); gap:12px; margin-bottom:16px; }
        .rsom-page .rsom-detail-item { border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; }
        .rsom-page .rsom-detail-label { color:#64748b; display:block; font-size:.72rem; font-weight:900; text-transform:uppercase; }
        .rsom-page .rsom-detail-value { color:#143521; display:block; font-size:.92rem; font-weight:800; margin-top:4px; }
        .rsom-page .rsom-usage-list { border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; }
        .rsom-page .rsom-usage-row { display:grid; grid-template-columns:1.1fr 1.3fr 1.1fr 1.6fr .9fr; gap:10px; padding:10px 12px; }
        .rsom-page .rsom-usage-row + .rsom-usage-row { border-top:1px solid #eef2f7; }
        .rsom-page .rsom-usage-head { background:#f8fafc; color:#475569; font-size:.72rem; font-weight:900; text-transform:uppercase; }
        @media (max-width:1180px) {
            .rsom-page .rsom-summary { grid-template-columns:repeat(2, minmax(130px, 1fr)); }
            .rsom-page .rsom-filter-grid { grid-template-columns:1fr 1fr; }
        }
        @media (max-width:640px) {
            .rsom-page .rsom-summary, .rsom-page .rsom-filter-grid { grid-template-columns:1fr; }
            .rsom-page .rsom-detail-grid, .rsom-page .rsom-usage-row { grid-template-columns:1fr; }
        }
    </style>

    <div class="rsom-actions">
        <a class="rsom-action" href="{{ route('registrar.registrar-menu.scheduling.room-file') }}">Manage Rooms</a>
        <a class="rsom-action secondary" href="{{ route('registrar.registrar-menu.scheduling.room-generation-assignment') }}">Generate / Assign Rooms</a>
        <a class="rsom-action secondary" href="{{ route('registrar.registrar-menu.scheduling.section-offering') }}">Manage Section Offering</a>
        <a class="rsom-action secondary" href="{{ route('registrar.registrar-menu.scheduling.class-schedule-preparation') }}">Prepare Class Schedule</a>
        <a class="rsom-action secondary" href="#rsomCurrentSubjects">Current Course / Subject List</a>
    </div>

    <div class="rsom-note">
        This page combines room file data, allowed room subjects, section offerings, class room assignments, and schedule readiness. Rooms are available from 7:00 AM to 9:00 PM, and generated assignments should only use rooms allowed for the offering's subject.
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
        <div class="rsom-stat">
            <span class="rsom-stat-label">Ready Offerings</span>
            <span class="rsom-stat-value">{{ number_format((int) ($summary['ready_offerings'] ?? 0)) }}</span>
        </div>
        <div class="rsom-stat">
            <span class="rsom-stat-label">Needs Attention</span>
            <span class="rsom-stat-value">{{ number_format((int) ($summary['pending_offerings'] ?? 0)) }}</span>
        </div>
        <div class="rsom-stat">
            <span class="rsom-stat-label">Conflicts</span>
            <span class="rsom-stat-value">{{ number_format((int) ($summary['conflicts'] ?? 0)) }}</span>
        </div>
        <div class="rsom-stat">
            <span class="rsom-stat-label">Assignment Rows</span>
            <span class="rsom-stat-value">{{ number_format((int) ($summary['assignments'] ?? 0)) }}</span>
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
                <label for="rsomCourse">Search by Program</label>
                <select class="rsom-select" id="rsomCourse" name="course_id">
                    <option value="0">All Programs</option>
                    @foreach($courseOptions as $option)
                        <option value="{{ $option['id'] }}" {{ $selectedCourseId === (int) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <span class="rsom-filter-hint">Choose a program to show rooms that allow subjects under that program.</span>
            </div>
            <div class="rsom-field">
                <label for="rsomSection">Search by Section</label>
                <input class="rsom-input" id="rsomSection" type="text" name="section" value="{{ $sectionQuery ?? '' }}" placeholder="e.g. BSCS 1-A" list="rsomSectionOptions">
                <datalist id="rsomSectionOptions">
                    @foreach(($sectionOptions ?? []) as $sectionOption)
                        <option value="{{ $sectionOption }}"></option>
                    @endforeach
                </datalist>
                <span class="rsom-filter-hint">Type or pick a section to display its current course/subject list.</span>
            </div>
            <div class="rsom-field">
                <label for="rsomSearch">Subject / Room / Faculty</label>
                <input class="rsom-input" id="rsomSearch" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Subject, room, faculty, program">
            </div>
            <div class="rsom-field">
                <label for="rsomStatus">Validation Status</label>
                <select class="rsom-select" id="rsomStatus" name="status">
                    @foreach(($statusOptions ?? []) as $option)
                        <option value="{{ $option['value'] }}" {{ $selectedStatus === (string) $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="pf-btn-new">Filter</button>
            <a class="rsom-action secondary" href="{{ route('registrar.registrar-menu.scheduling.room-section-offering-management') }}">Reset</a>
        </div>
    </form>

    <div class="rsom-note">
        Current display: {{ number_format((int) ($summary['subjects'] ?? 0)) }} course/subject offering(s), {{ number_format((int) ($summary['sections'] ?? 0)) }} section(s), and {{ number_format((int) ($summary['rooms'] ?? 0)) }} room(s) matched by the filters.
    </div>

    <div class="rsom-section-title">Room Details, Availability & Subject Assignment</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Type / Availability</th>
                    <th>Floor</th>
                    <th>Capacity</th>
                    <th>Building / Hallway</th>
                    <th>Allowed Subjects</th>
                    <th>Current Usage</th>
                    <th>Updated By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td>
                            <strong>{{ $room['room_code'] ?: ('Room #' . $room['room_number']) }}</strong>
                            <div class="rsom-muted">Room #{{ $room['room_number'] }}</div>
                        </td>
                        <td>
                            <div>{{ $room['room_type'] ?: 'Lecture Room' }}</div>
                            <div class="rsom-muted">{{ $room['availability'] ?? '07:00 - 21:00' }}</div>
                        </td>
                        <td>{{ $room['floor_number'] }}</td>
                        <td>{{ number_format((int) $room['capacity']) }}</td>
                        <td>{{ $room['location_label'] !== '' ? $room['location_label'] : 'Unassigned' }}</td>
                        <td>{{ $room['subject_label'] }}</td>
                        <td>
                            <span class="rsom-pill neutral">{{ (int) ($room['offering_count'] ?? 0) }} offerings</span>
                            <span class="rsom-pill neutral">{{ (int) ($room['section_count'] ?? 0) }} sections</span>
                            @if(!empty($room['subject_preview']))
                                <div class="rsom-muted">{{ $room['subject_preview'] }}</div>
                            @endif
                        </td>
                        <td>{{ $room['updated_by'] }}</td>
                        <td>
                            <button type="button" class="rsom-mini-btn" data-rsom-room-id="{{ (int) $room['id'] }}">View</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="rsom-empty">No room records matched the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @php $pager = $pagination['rooms'] ?? null; @endphp
    @if($pager && (int) ($pager['last_page'] ?? 1) > 1)
        <div class="rsom-pager">
            <span class="rsom-page-count">Rooms {{ number_format((int) $pager['from']) }}-{{ number_format((int) $pager['to']) }} of {{ number_format((int) $pager['total']) }}</span>
            <a class="rsom-page-link {{ (int) $pager['page'] <= 1 ? 'disabled' : '' }}" href="{{ (int) $pager['page'] > 1 ? request()->fullUrlWithQuery([$pager['page_name'] => (int) $pager['page'] - 1]) : '#' }}">Previous</a>
            <span class="rsom-page-count">Page {{ (int) $pager['page'] }} of {{ (int) $pager['last_page'] }}</span>
            <a class="rsom-page-link {{ (int) $pager['page'] >= (int) $pager['last_page'] ? 'disabled' : '' }}" href="{{ (int) $pager['page'] < (int) $pager['last_page'] ? request()->fullUrlWithQuery([$pager['page_name'] => (int) $pager['page'] + 1]) : '#' }}">Next</a>
        </div>
    @endif

    <div class="rsom-modal-backdrop" id="rsomRoomModal" aria-hidden="true">
        <div class="rsom-modal" role="dialog" aria-modal="true" aria-labelledby="rsomRoomModalTitle">
            <div class="rsom-modal-head">
                <div>
                    <h3 class="rsom-modal-title" id="rsomRoomModalTitle">Room Details</h3>
                    <div class="rsom-muted" id="rsomRoomModalSubtitle"></div>
                </div>
                <button type="button" class="rsom-modal-close" id="rsomRoomModalClose" aria-label="Close room details">&times;</button>
            </div>
            <div class="rsom-modal-body">
                <div class="rsom-detail-grid" id="rsomRoomDetails"></div>
                <div class="rsom-section-title" style="margin-top:0;">Current Usage</div>
                <div class="rsom-usage-list" id="rsomRoomUsage"></div>
            </div>
        </div>
    </div>

    <div class="rsom-section-title">Section Scheduling</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Term</th>
                    <th>Program / Section</th>
                    <th>Students / Capacity</th>
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
                            <span class="rsom-pill neutral">{{ (int) $section['student_count'] }} students</span>
                            <span class="rsom-pill neutral">{{ (int) $section['room_capacity'] }} room cap.</span>
                        </td>
                        <td>
                            <span class="rsom-pill">{{ $section['scheduled_count'] }} scheduled</span>
                            <span class="rsom-pill warn">{{ (int) $section['pending_count'] }} needs attention</span>
                            <span class="rsom-pill neutral">{{ (int) $section['subject_count'] }} total</span>
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
                        <td colspan="7" class="rsom-empty">No section offering records matched the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @php $pager = $pagination['sections'] ?? null; @endphp
    @if($pager && (int) ($pager['last_page'] ?? 1) > 1)
        <div class="rsom-pager">
            <span class="rsom-page-count">Sections {{ number_format((int) $pager['from']) }}-{{ number_format((int) $pager['to']) }} of {{ number_format((int) $pager['total']) }}</span>
            <a class="rsom-page-link {{ (int) $pager['page'] <= 1 ? 'disabled' : '' }}" href="{{ (int) $pager['page'] > 1 ? request()->fullUrlWithQuery([$pager['page_name'] => (int) $pager['page'] - 1]) : '#' }}">Previous</a>
            <span class="rsom-page-count">Page {{ (int) $pager['page'] }} of {{ (int) $pager['last_page'] }}</span>
            <a class="rsom-page-link {{ (int) $pager['page'] >= (int) $pager['last_page'] ? 'disabled' : '' }}" href="{{ (int) $pager['page'] < (int) $pager['last_page'] ? request()->fullUrlWithQuery([$pager['page_name'] => (int) $pager['page'] + 1]) : '#' }}">Next</a>
        </div>
    @endif

    <div class="rsom-section-title" id="rsomCurrentSubjects">Current Course / Subject List and Room Readiness</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Term</th>
                    <th>Program / Section</th>
                    <th>Subject</th>
                    <th>Faculty</th>
                    <th>Schedule</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th>Validation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($offeringRows as $offering)
                    @php
                        $status = (string) ($offering['status'] ?? '');
                        $statusClass = $status === 'Ready' ? '' : ($status === 'Conflict' || $status === 'Subject Not Allowed' ? 'danger' : 'warn');
                    @endphp
                    <tr>
                        <td>
                            <div>{{ $offering['school_year'] ?: 'N/A' }}</div>
                            <div class="rsom-muted">{{ $offering['semester'] ?: 'N/A' }}</div>
                        </td>
                        <td>
                            <strong>{{ $offering['course_code'] ?: 'N/A' }}</strong>
                            <div class="rsom-muted">{{ $offering['section'] ?: 'No section' }}</div>
                        </td>
                        <td>
                            <strong>{{ $offering['subject_code'] ?: 'N/A' }}</strong>
                            <div class="rsom-muted">{{ $offering['subject_name'] ?: 'No subject name' }}</div>
                        </td>
                        <td>{{ $offering['faculty_name'] ?: 'TBA' }}</td>
                        <td>
                            @foreach($offering['schedule_lines'] as $line)
                                <div class="rsom-schedule-line">{{ $line }}</div>
                            @endforeach
                        </td>
                        <td>
                            <strong>{{ $offering['room_label'] ?: 'TBA' }}</strong>
                            @if(!empty($offering['room_type']))
                                <div class="rsom-muted">{{ $offering['room_type'] }} - {{ (int) $offering['room_capacity'] }} cap.</div>
                            @endif
                        </td>
                        <td><span class="rsom-pill {{ $statusClass }}">{{ $status ?: 'Unknown' }}</span></td>
                        <td>
                            @if(!empty($offering['status_reason']))
                                <span class="rsom-muted">{{ $offering['status_reason'] }}</span>
                            @else
                                <span class="rsom-muted">Validated</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="rsom-empty">No offering-level rows matched the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @php $pager = $pagination['offerings'] ?? null; @endphp
    @if($pager && (int) ($pager['last_page'] ?? 1) > 1)
        <div class="rsom-pager">
            <span class="rsom-page-count">Offerings {{ number_format((int) $pager['from']) }}-{{ number_format((int) $pager['to']) }} of {{ number_format((int) $pager['total']) }}</span>
            <a class="rsom-page-link {{ (int) $pager['page'] <= 1 ? 'disabled' : '' }}" href="{{ (int) $pager['page'] > 1 ? request()->fullUrlWithQuery([$pager['page_name'] => (int) $pager['page'] - 1]) : '#' }}">Previous</a>
            <span class="rsom-page-count">Page {{ (int) $pager['page'] }} of {{ (int) $pager['last_page'] }}</span>
            <a class="rsom-page-link {{ (int) $pager['page'] >= (int) $pager['last_page'] ? 'disabled' : '' }}" href="{{ (int) $pager['page'] < (int) $pager['last_page'] ? request()->fullUrlWithQuery([$pager['page_name'] => (int) $pager['page'] + 1]) : '#' }}">Next</a>
        </div>
    @endif

    <div class="rsom-section-title">Room Assignment Records</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Class Offering</th>
                    <th>Section</th>
                    <th>Component</th>
                    <th>Required Room</th>
                    <th>Assigned Room</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignmentRows as $assignment)
                    @php
                        $assignmentStatus = (string) ($assignment->assignment_status ?? '');
                        $assignmentClass = stripos($assignmentStatus, 'pending') !== false || stripos($assignmentStatus, 'conflict') !== false ? 'warn' : '';
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $assignment->course_code ?: ($assignment->subject_code ?: 'N/A') }}</strong>
                            <div class="rsom-muted">{{ $assignment->subject_name ?: 'No subject name' }}</div>
                        </td>
                        <td>{{ $assignment->section_id ?: ($assignment->section ?: 'N/A') }}</td>
                        <td>{{ $assignment->schedule_component_type ?: 'N/A' }}</td>
                        <td>{{ $assignment->room_type_required ?: 'N/A' }}</td>
                        <td>{{ $assignment->room_code ?: 'TBA' }}</td>
                        <td>{{ trim(($assignment->day ?: '') . ' ' . ($assignment->start_time ?: '') . '-' . ($assignment->end_time ?: ''), ' -') ?: 'TBA' }}</td>
                        <td><span class="rsom-pill {{ $assignmentClass }}">{{ $assignmentStatus ?: 'N/A' }}</span></td>
                        <td>{{ $assignment->remarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="rsom-empty">No room assignment records matched the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @php $pager = $pagination['assignments'] ?? null; @endphp
    @if($pager && (int) ($pager['last_page'] ?? 1) > 1)
        <div class="rsom-pager">
            <span class="rsom-page-count">Assignments {{ number_format((int) $pager['from']) }}-{{ number_format((int) $pager['to']) }} of {{ number_format((int) $pager['total']) }}</span>
            <a class="rsom-page-link {{ (int) $pager['page'] <= 1 ? 'disabled' : '' }}" href="{{ (int) $pager['page'] > 1 ? request()->fullUrlWithQuery([$pager['page_name'] => (int) $pager['page'] - 1]) : '#' }}">Previous</a>
            <span class="rsom-page-count">Page {{ (int) $pager['page'] }} of {{ (int) $pager['last_page'] }}</span>
            <a class="rsom-page-link {{ (int) $pager['page'] >= (int) $pager['last_page'] ? 'disabled' : '' }}" href="{{ (int) $pager['page'] < (int) $pager['last_page'] ? request()->fullUrlWithQuery([$pager['page_name'] => (int) $pager['page'] + 1]) : '#' }}">Next</a>
        </div>
    @endif

    <script>
        (function () {
            var rooms = @json($rooms);
            var roomMap = {};
            rooms.forEach(function (room) {
                roomMap[String(room.id)] = room;
            });

            function text(value, fallback) {
                value = value === null || typeof value === 'undefined' ? '' : String(value);
                return value.trim() !== '' ? value : fallback;
            }

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function detail(label, value) {
                return '<div class="rsom-detail-item"><span class="rsom-detail-label">' + escapeHtml(label) + '</span><span class="rsom-detail-value">' + escapeHtml(value) + '</span></div>';
            }

            function openRoomModal(room) {
                var modal = document.getElementById('rsomRoomModal');
                var title = document.getElementById('rsomRoomModalTitle');
                var subtitle = document.getElementById('rsomRoomModalSubtitle');
                var details = document.getElementById('rsomRoomDetails');
                var usage = document.getElementById('rsomRoomUsage');
                if (!modal || !title || !subtitle || !details || !usage || !room) {
                    return;
                }

                var roomTitle = text(room.room_code, 'Room #' + text(room.room_number, 'N/A'));
                title.textContent = roomTitle;
                subtitle.textContent = text(room.location_label, 'Unassigned location');
                details.innerHTML = [
                    detail('Room Number', 'Room #' + text(room.room_number, 'N/A')),
                    detail('Type', text(room.room_type, 'Lecture Room')),
                    detail('Availability', text(room.availability, '07:00 - 21:00')),
                    detail('Floor', text(room.floor_number, 'N/A')),
                    detail('Capacity', text(room.capacity, '0')),
                    detail('Allowed Subjects', text(room.subject_label, 'No subject assigned')),
                    detail('Current Offerings', text(room.offering_count, '0')),
                    detail('Current Sections', text(room.section_count, '0')),
                    detail('Updated By', text(room.updated_by, 'System'))
                ].join('');

                var rows = Array.isArray(room.usage_rows) ? room.usage_rows : [];
                if (!rows.length) {
                    usage.innerHTML = '<div class="rsom-empty">No current usage for this room under the selected filters.</div>';
                } else {
                    usage.innerHTML = '<div class="rsom-usage-row rsom-usage-head"><div>Term</div><div>Program / Section</div><div>Subject</div><div>Schedule</div><div>Status</div></div>' +
                        rows.map(function (row) {
                            return '<div class="rsom-usage-row">' +
                                '<div>' + escapeHtml(text(row.school_year, 'N/A')) + '<div class="rsom-muted">' + escapeHtml(text(row.semester, 'N/A')) + '</div></div>' +
                                '<div>' + escapeHtml(text(row.program, 'N/A')) + '<div class="rsom-muted">' + escapeHtml(text(row.section, 'No section')) + '</div></div>' +
                                '<div>' + escapeHtml(text(row.subject, 'N/A')) + '<div class="rsom-muted">' + escapeHtml(text(row.faculty, 'TBA')) + '</div></div>' +
                                '<div>' + escapeHtml(text(row.schedule, 'TBA')) + '</div>' +
                                '<div><span class="rsom-pill ' + (row.status === 'Ready' ? '' : 'warn') + '">' + escapeHtml(text(row.status, 'Unknown')) + '</span></div>' +
                            '</div>';
                        }).join('');
                }

                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }

            function closeRoomModal() {
                var modal = document.getElementById('rsomRoomModal');
                if (modal) {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                }
            }

            document.addEventListener('click', function (event) {
                var button = event.target.closest('[data-rsom-room-id]');
                if (button) {
                    openRoomModal(roomMap[String(button.getAttribute('data-rsom-room-id'))]);
                    return;
                }

                if (event.target && event.target.id === 'rsomRoomModal') {
                    closeRoomModal();
                }
            });

            var closeButton = document.getElementById('rsomRoomModalClose');
            if (closeButton) {
                closeButton.addEventListener('click', closeRoomModal);
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeRoomModal();
                }
            });
        }());
    </script>
</div>
@endsection
