@extends('layouts.registrar')

@section('title', 'PLP - Faculty Loads')
@section('page-title', 'FACULTY LOADS')
@section('body-class', 'page-services-faculty-loads')

@push('scripts')
    <script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
    <script src="{{ asset('js/registrar-faculty-loads.js') }}?v={{ file_exists(public_path('js/registrar-faculty-loads.js')) ? filemtime(public_path('js/registrar-faculty-loads.js')) : time() }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ mix('css/registrar-faculty-loads.css') }}">
@endpush

@section('content')
@php
    $facultyShowRoute = request()->routeIs('registrar.registrar-menu.faculty-mgmt.*')
        ? 'registrar.registrar-menu.faculty-mgmt.faculty-list.show'
        : 'registrar.services.classroom-faculty.faculty-loads.show';
    $facultyIndexRoute = request()->routeIs('registrar.registrar-menu.faculty-mgmt.*')
        ? 'registrar.registrar-menu.faculty-mgmt.faculty-list'
        : 'registrar.services.classroom-faculty.faculty-loads.index';
    $allowedStoreRoute = request()->routeIs('registrar.registrar-menu.faculty-mgmt.*')
        ? 'registrar.registrar-menu.faculty-mgmt.faculty-list.allowed-subjects.store'
        : 'registrar.services.classroom-faculty.faculty-loads.allowed-subjects.store';
    $allowedDestroyRoute = request()->routeIs('registrar.registrar-menu.faculty-mgmt.*')
        ? 'registrar.registrar-menu.faculty-mgmt.faculty-list.allowed-subjects.destroy'
        : 'registrar.services.classroom-faculty.faculty-loads.allowed-subjects.destroy';
    $facultyProfileUpdateRoute = request()->routeIs('registrar.registrar-menu.faculty-mgmt.*')
        ? 'registrar.registrar-menu.faculty-mgmt.faculty-list.profile.update'
        : 'registrar.services.classroom-faculty.faculty-loads.profile.update';
    $facultyName = trim((string) $faculty->name) ?: 'Faculty Member';
    $facultyInitials = collect(explode(' ', strtoupper($facultyName)))->filter()->take(2)->map(function ($part) {
        return substr($part, 0, 1);
    })->implode('');
    $loadBadgeClass = $facultyProfile['load_status'] === 'Overload' ? 'warn' : '';
@endphp
<div class="rfl-wrap">
    <style>
        .ffp-hero { background:linear-gradient(135deg,#004d27 0%,#006837 55%,#1a9e54 100%); padding:26px 30px 0; color:#fff; position:relative; overflow:hidden; border-radius:0 0 8px 8px; margin-bottom:18px; }
        .ffp-hero::before { content:''; position:absolute; top:-70px; right:-50px; width:260px; height:260px; border-radius:50%; background:rgba(255,255,255,.06); }
        .ffp-back { display:inline-flex; align-items:center; gap:6px; color:rgba(255,255,255,.82); font-size:12px; text-decoration:none; margin-bottom:14px; }
        .ffp-back:hover { color:#fff; text-decoration:none; }
        .ffp-hero-top { display:flex; align-items:flex-start; gap:20px; flex-wrap:wrap; position:relative; z-index:1; }
        .ffp-avatar { width:86px; height:86px; border-radius:50%; border:3px solid rgba(255,255,255,.38); background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; font-size:30px; font-weight:800; letter-spacing:1px; }
        .ffp-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
        .ffp-info { flex:1; min-width:220px; }
        .ffp-name { font-size:23px; font-weight:800; line-height:1.2; margin-bottom:8px; text-transform:uppercase; }
        .ffp-meta { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:8px; }
        .ffp-pill { background:rgba(255,255,255,.18); border-radius:20px; padding:4px 12px; font-size:11.5px; font-weight:700; }
        .ffp-pill.warn { background:rgba(239,68,68,.38); }
        .ffp-contact { font-size:12.5px; color:rgba(255,255,255,.82); display:flex; gap:14px; flex-wrap:wrap; }
        .ffp-load-box { background:rgba(255,255,255,.13); border-radius:12px; padding:14px 18px; min-width:150px; text-align:center; }
        .ffp-load-num { font-size:32px; line-height:1; font-weight:800; }
        .ffp-load-label { margin-top:4px; color:rgba(255,255,255,.72); font-size:11px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
        .ffp-hero-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
        .ffp-edit-btn { border:1px solid rgba(255,255,255,.45); border-radius:8px; background:rgba(255,255,255,.16); color:#fff; cursor:pointer; font-size:12px; font-weight:800; padding:8px 12px; }
        .ffp-edit-btn:hover { background:rgba(255,255,255,.24); }
        .ffp-tabs { display:flex; gap:2px; padding-top:20px; overflow-x:auto; scrollbar-width:none; position:relative; z-index:1; }
        .ffp-tabs::-webkit-scrollbar { display:none; }
        .ffp-tab { padding:10px 16px; font-size:13px; font-weight:700; border-radius:10px 10px 0 0; background:rgba(255,255,255,.12); color:rgba(255,255,255,.82); white-space:nowrap; text-decoration:none; }
        .ffp-tab:hover { background:rgba(255,255,255,.2); color:#fff; text-decoration:none; }
        .ffp-tab.active { background:#fff; color:#004d27; }
        .ffp-card { background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:18px; overflow:hidden; }
        .ffp-card-head { padding:13px 18px; border-bottom:1px solid #f1f5f9; font-size:13px; font-weight:800; color:#1e293b; }
        .ffp-card-body { padding:18px; }
        .ffp-fields { display:grid; grid-template-columns:repeat(3,1fr); gap:14px 24px; }
        .ffp-field label { font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:4px; }
        .ffp-field .val { font-size:13px; color:#1e293b; padding:7px 0; border-bottom:1px dashed #e2e8f0; }
        .ffp-subject-chip { display:inline-flex; align-items:center; gap:6px; margin:0 6px 8px 0; padding:7px 10px; border-radius:999px; background:#f0fdf4; color:#065f46; font-size:12px; font-weight:800; }
        .ffp-modal-backdrop { position:fixed; inset:0; z-index:1200; display:none; align-items:center; justify-content:center; padding:18px; background:rgba(15,23,42,.48); }
        .ffp-modal-backdrop.is-open { display:flex; }
        .ffp-modal { width:min(760px, 100%); max-height:88vh; overflow:auto; background:#fff; border-radius:8px; box-shadow:0 24px 80px rgba(15,23,42,.28); }
        .ffp-modal-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:18px 20px; border-bottom:1px solid #e2e8f0; }
        .ffp-modal-title { color:#143521; font-size:1.05rem; font-weight:900; margin:0; }
        .ffp-modal-subtitle { color:#64748b; font-size:.82rem; margin-top:3px; }
        .ffp-modal-close { width:34px; height:34px; border:1px solid #d8e0da; border-radius:7px; background:#f8fafc; color:#334155; cursor:pointer; font-size:1.15rem; font-weight:900; line-height:1; }
        .ffp-modal-body { padding:18px 20px 20px; }
        .ffp-modal-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:14px; }
        .ffp-modal-field label { display:block; color:#475569; font-size:.75rem; font-weight:900; letter-spacing:.04em; text-transform:uppercase; margin-bottom:5px; }
        .ffp-modal-field input, .ffp-modal-field select { width:100%; min-height:40px; border:1px solid #cbd5e1; border-radius:7px; padding:8px 10px; background:#fff; color:#0f172a; }
        .ffp-modal-note { margin-top:10px; color:#64748b; font-size:.8rem; }
        .ffp-modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:18px; }
        .ffp-modal-cancel, .ffp-modal-save { border:0; border-radius:7px; cursor:pointer; font-weight:900; padding:10px 16px; }
        .ffp-modal-cancel { background:#e2e8f0; color:#334155; }
        .ffp-modal-save { background:#006837; color:#fff; }
        @media(max-width:800px){ .ffp-fields { grid-template-columns:1fr; } .ffp-load-box { width:100%; } }
        @media(max-width:700px){ .ffp-modal-grid { grid-template-columns:1fr; } }
    </style>

    @if (session('status'))
        <div class="alert alert-{{ session('status_type', 'success') }} rfl-alert" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rfl-alert" role="alert">
            <div class="font-weight-bold mb-1">Please check the form and try again.</div>
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ffp-hero">
        <a href="{{ route($facultyIndexRoute) }}" class="ffp-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back to Faculty Profiles
        </a>
        <div class="ffp-hero-top">
            <div class="ffp-avatar">
                @if(!empty($facultyProfile['photo_url']))
                    <img src="{{ $facultyProfile['photo_url'] }}" alt="Faculty avatar">
                @else
                    {{ $facultyInitials !== '' ? $facultyInitials : 'FM' }}
                @endif
            </div>
            <div class="ffp-info">
                <div class="ffp-name">{{ $facultyName }}</div>
                <div class="ffp-meta">
                    <span class="ffp-pill">{{ $faculty->code ?: 'No Code' }}</span>
                    <span class="ffp-pill">{{ $facultyProfile['department'] ?: 'No Department' }}</span>
                    <span class="ffp-pill">{{ $facultyProfile['college_code'] ?: 'No College' }}</span>
                    <span class="ffp-pill">{{ $facultyProfile['employment_type'] ?: 'Faculty' }}</span>
                    <span class="ffp-pill {{ $loadBadgeClass }}">{{ $facultyProfile['load_status'] }}</span>
                </div>
                <div class="ffp-contact">
                    @if(!empty($facultyProfile['mobile'])) <span>{{ $facultyProfile['mobile'] }}</span> @endif
                    @if(!empty($facultyProfile['email'])) <span>{{ $facultyProfile['email'] }}</span> @endif
                    <span>{{ number_format($facultyProfile['qualified_subject_count']) }} allowed subject(s)</span>
                </div>
                <div class="ffp-hero-actions">
                    <button type="button" class="ffp-edit-btn" id="ffpOpenEditProfile">Edit Profile</button>
                </div>
            </div>
            <div class="ffp-load-box">
                <div class="ffp-load-num">{{ number_format($facultyProfile['current_load'], 1) }}</div>
                <div class="ffp-load-label">Current Load / {{ number_format($facultyProfile['max_load'], 1) }}</div>
            </div>
        </div>

        <div class="ffp-tabs">
        <a class="ffp-tab {{ $tab === 'profile' ? 'active' : '' }}" href="{{ route($facultyShowRoute, ['faculty' => $faculty->id, 'tab' => 'profile', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}">
            Profile
        </a>
        <a class="ffp-tab {{ $tab === 'allowed' ? 'active' : '' }}" href="{{ route($facultyShowRoute, ['faculty' => $faculty->id, 'tab' => 'allowed', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}">
            Allowed Subjects
        </a>
        <a class="ffp-tab {{ $tab === 'schedule' ? 'active' : '' }}" href="{{ route($facultyShowRoute, ['faculty' => $faculty->id, 'tab' => 'schedule', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}">
            Schedule
        </a>
        <a class="ffp-tab {{ $tab === 'load' ? 'active' : '' }}" href="{{ route($facultyShowRoute, ['faculty' => $faculty->id, 'tab' => 'load', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}">
            Faculty Load
        </a>
        <a class="ffp-tab {{ $tab === 'loading' ? 'active' : '' }}" href="{{ route($facultyShowRoute, ['faculty' => $faculty->id, 'tab' => 'loading', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}">
            Faculty Loading
        </a>
        <a class="ffp-tab {{ $tab === 'history' ? 'active' : '' }}" href="{{ route($facultyShowRoute, ['faculty' => $faculty->id, 'tab' => 'history', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}">
            Load History
        </a>
        <a class="ffp-tab {{ $tab === 'grades' ? 'active' : '' }}" href="{{ route($facultyShowRoute, ['faculty' => $faculty->id, 'tab' => 'grades', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]) }}">
            Grades History
        </a>
        </div>
    </div>

    <div class="ffp-modal-backdrop" id="ffpEditProfileModal" aria-hidden="true">
        <div class="ffp-modal" role="dialog" aria-modal="true" aria-labelledby="ffpEditProfileTitle">
            <div class="ffp-modal-head">
                <div>
                    <h3 class="ffp-modal-title" id="ffpEditProfileTitle">Edit Faculty Profile</h3>
                    <div class="ffp-modal-subtitle">{{ $facultyName }}</div>
                </div>
                <button type="button" class="ffp-modal-close" id="ffpCloseEditProfile" aria-label="Close">&times;</button>
            </div>
            <form method="POST" action="{{ route($facultyProfileUpdateRoute, $faculty->id) }}" class="ffp-modal-body">
                @csrf
                @method('PUT')
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="ffp-modal-grid">
                    <div class="ffp-modal-field">
                        <label>Faculty Code</label>
                        <input type="text" name="code" value="{{ old('code', $faculty->code) }}" required>
                    </div>
                    <div class="ffp-modal-field">
                        <label>Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $faculty->name) }}" required>
                    </div>
                    <div class="ffp-modal-field">
                        <label>Department</label>
                        <select name="department_id">
                            <option value="">No Department</option>
                            @foreach($facultyDepartmentOptions as $department)
                                <option value="{{ $department->id }}" {{ (string) old('department_id', $faculty->department_id ?? '') === (string) $department->id ? 'selected' : '' }}>
                                    {{ $department->code }} - {{ $department->description }}
                                    @if(optional($department->college)->abbr)
                                        ({{ $department->college->abbr }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ffp-modal-field">
                        <label>College</label>
                        <select name="college_id">
                            <option value="">Use Department College</option>
                            @foreach($facultyCollegeOptions as $college)
                                <option value="{{ $college->id }}" {{ (string) old('college_id', $faculty->college_id ?? '') === (string) $college->id ? 'selected' : '' }}>
                                    {{ $college->abbr ?: $college->code }} - {{ $college->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ffp-modal-field">
                        <label>Employment Type</label>
                        <select name="employment_type">
                            @foreach($employmentTypeOptions as $employmentType)
                                <option value="{{ $employmentType }}" {{ old('employment_type', $facultyProfile['employment_type']) === $employmentType ? 'selected' : '' }}>{{ $employmentType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ffp-modal-field">
                        <label>Max Load Units</label>
                        <input type="number" step="0.01" min="0" max="999.99" name="max_load_units" value="{{ old('max_load_units', $faculty->max_load_units) }}" placeholder="{{ number_format($facultyProfile['max_load'], 1) }}">
                    </div>
                </div>
                <div class="ffp-modal-note">
                    Remaining load and faculty status will recalculate automatically after saving.
                </div>
                <div class="ffp-modal-actions">
                    <button type="button" class="ffp-modal-cancel" id="ffpCancelEditProfile">Cancel</button>
                    <button type="submit" class="ffp-modal-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <form method="GET" action="{{ route($facultyShowRoute, $faculty->id) }}" class="rfl-filters">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <div class="rfl-filter">
            <div class="app-filter-label">School Year:</div>
            <div class="rfl-select">
                @include('registrar.components.listbox-select', [
                    'id' => 'rflSchoolYear',
                    'name' => 'school_year',
                    'options' => $schoolYearOptions,
                    'selected' => $selectedSchoolYear,
                    'placeholder' => 'School Year',
                ])
            </div>
        </div>

        <div class="rfl-filter">
            <div class="app-filter-label">Term:</div>
            <div class="rfl-select">
                @include('registrar.components.listbox-select', [
                    'id' => 'rflSemester',
                    'name' => 'semester',
                    'options' => $semesterOptions,
                    'selected' => $selectedSemester,
                    'placeholder' => 'Term',
                ])
            </div>
        </div>

        <button class="btn btn-success rfl-set-btn" type="submit">Set</button>
    </form>

    @if ($tab === 'profile')
        <style>
            .faculty-profile-grid { display:grid; grid-template-columns:repeat(4, minmax(130px, 1fr)); gap:12px; margin-bottom:16px; }
            .faculty-profile-card { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:14px; }
            .faculty-profile-card span { display:block; color:#607264; font-size:.76rem; font-weight:800; text-transform:uppercase; }
            .faculty-profile-card strong { display:block; color:#123822; font-size:1.35rem; margin-top:4px; }
            .faculty-profile-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:16px; margin-bottom:16px; }
            .faculty-profile-table { width:100%; border-collapse:collapse; }
            .faculty-profile-table th, .faculty-profile-table td { border-bottom:1px solid #edf3ef; padding:10px 12px; text-align:left; }
            .faculty-profile-table th { width:220px; color:#607264; font-size:.78rem; text-transform:uppercase; }
            @media (max-width:900px){ .faculty-profile-grid { grid-template-columns:1fr 1fr; } }
            @media (max-width:560px){ .faculty-profile-grid { grid-template-columns:1fr; } }
        </style>
        <div class="faculty-profile-grid">
            <div class="faculty-profile-card"><span>Max Load</span><strong>{{ number_format($facultyProfile['max_load'], 1) }}</strong></div>
            <div class="faculty-profile-card"><span>Current Load</span><strong>{{ number_format($facultyProfile['current_load'], 1) }}</strong></div>
            <div class="faculty-profile-card"><span>Remaining Load</span><strong>{{ number_format($facultyProfile['remaining_load'], 1) }}</strong></div>
            <div class="faculty-profile-card"><span>Load Status</span><strong style="font-size:1rem;">{{ $facultyProfile['load_status'] }}</strong></div>
            <div class="faculty-profile-card"><span>Students Handled</span><strong>{{ number_format($facultyProfile['students']) }}</strong></div>
            <div class="faculty-profile-card"><span>Assigned Subjects</span><strong>{{ number_format($facultyProfile['assigned_subjects']) }}</strong></div>
            <div class="faculty-profile-card"><span>Sections</span><strong>{{ number_format($facultyProfile['sections']) }}</strong></div>
            <div class="faculty-profile-card"><span>Employment</span><strong style="font-size:1rem;">{{ $facultyProfile['employment_type'] ?: 'N/A' }}</strong></div>
            <div class="faculty-profile-card"><span>Department</span><strong style="font-size:1rem;">{{ $facultyProfile['department'] ?: 'N/A' }}</strong></div>
            <div class="faculty-profile-card"><span>College</span><strong style="font-size:1rem;">{{ $facultyProfile['college_code'] ?: 'N/A' }}</strong></div>
            <div class="faculty-profile-card"><span>Allowed Subjects</span><strong>{{ number_format($facultyProfile['qualified_subject_count']) }}</strong></div>
        </div>

        <div class="faculty-profile-panel">
            <h3 class="rfl-loading-header" style="margin-top:0;">Faculty Information</h3>
            <table class="faculty-profile-table">
                <tr><th>Faculty Code</th><td>{{ $faculty->code ?: 'N/A' }}</td></tr>
                <tr><th>Name</th><td>{{ $faculty->name ?: 'N/A' }}</td></tr>
                <tr><th>Department</th><td>{{ $facultyProfile['department'] ?: 'N/A' }}</td></tr>
                <tr><th>College</th><td>{{ $facultyProfile['college'] ?: ($facultyProfile['college_code'] ?: 'N/A') }}</td></tr>
                <tr><th>Employment Type</th><td>{{ $facultyProfile['employment_type'] ?: 'N/A' }}</td></tr>
                <tr><th>Position</th><td>{{ $facultyProfile['position'] ?: 'N/A' }}</td></tr>
                <tr><th>Email</th><td>{{ $facultyProfile['email'] ?: 'N/A' }}</td></tr>
                <tr><th>Mobile</th><td>{{ $facultyProfile['mobile'] ?: 'N/A' }}</td></tr>
                <tr><th>Load Status</th><td>{{ $facultyProfile['load_status'] }}</td></tr>
            </table>
        </div>
    @endif

    @if ($tab === 'allowed')
        <div class="ffp-card">
            <div class="ffp-card-head">Allowed / Qualified Subjects</div>
            <div class="ffp-card-body">
                <form method="POST" action="{{ route($allowedStoreRoute, $faculty->id) }}" style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end;margin-bottom:16px;">
                    @csrf
                    <input type="hidden" name="school_year" value="{{ $selectedSchoolYear }}">
                    <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                    <div>
                        <label class="app-filter-label" for="allowedSubjectSelect">Add Allowed Subject</label>
                        <select id="allowedSubjectSelect" name="subject_id" class="form-control" required>
                            <option value="">Select subject</option>
                            @foreach($availableQualifiedSubjectOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success" {{ $availableQualifiedSubjectOptions->count() ? '' : 'disabled' }}>Add</button>
                </form>

                @if($facultyProfile['qualified_subject_count'] > 0)
                    <div style="margin-bottom:12px;">
                        @foreach($facultyProfile['qualified_subjects'] as $subject)
                            <span class="ffp-subject-chip">{{ $subject->code }} · {{ $subject->name }}</span>
                        @endforeach
                    </div>
                    <div class="app-table-wrap rfl-assigned-table">
                        <table class="app-table" data-no-auto-pager="1">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Type</th>
                                    <th>Lec</th>
                                    <th>Lab</th>
                                    <th>Units</th>
                                    <th style="width:90px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facultyProfile['qualified_subjects'] as $subject)
                                    <tr>
                                        <td class="td-code">{{ $subject->code }}</td>
                                        <td>{{ $subject->name }}</td>
                                        <td>{{ $subject->course_type ?: 'N/A' }}</td>
                                        <td>{{ (int) ($subject->lec ?? 0) }}</td>
                                        <td>{{ (int) ($subject->lab ?? 0) }}</td>
                                        <td>{{ number_format((float) ($subject->units ?? 0), 1) }}</td>
                                        <td>
                                            <form method="POST" action="{{ route($allowedDestroyRoute, ['faculty' => $faculty->id, 'subject' => $subject->id]) }}" onsubmit="return confirm('Remove this allowed subject?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="school_year" value="{{ $selectedSchoolYear }}">
                                                <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted">
                        No explicit allowed subjects are configured for this faculty. Until subjects are added in teacher qualification setup, the scheduler treats this faculty as generally available subject to load and schedule rules.
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($tab === 'schedule')
        <style>
            .ffp-student-list-btn { background:#fff; border:1px solid #b9d6c5; border-radius:7px; color:#145c39; cursor:pointer; font-size:.78rem; font-weight:900; min-height:32px; padding:6px 10px; white-space:nowrap; }
            .ffp-student-modal-backdrop { align-items:center; background:rgba(15, 23, 42, .45); display:none; inset:0; justify-content:center; padding:18px; position:fixed; z-index:1200; }
            .ffp-student-modal-backdrop.is-open { display:flex; }
            .ffp-student-modal { background:#fff; border-radius:8px; box-shadow:0 22px 70px rgba(15, 23, 42, .24); max-height:88vh; max-width:900px; overflow:auto; width:100%; }
            .ffp-student-modal-head { align-items:flex-start; border-bottom:1px solid #e2e8f0; display:flex; gap:12px; justify-content:space-between; padding:18px 20px; }
            .ffp-student-modal-title { color:#143521; font-size:1.08rem; font-weight:900; margin:0; }
            .ffp-student-modal-subtitle { color:#64748b; font-size:.84rem; margin-top:3px; }
            .ffp-student-modal-close { background:#f8fafc; border:1px solid #d8e0da; border-radius:7px; color:#334155; cursor:pointer; font-size:1rem; font-weight:900; height:34px; line-height:1; width:34px; }
            .ffp-student-modal-body { padding:18px 20px 20px; }
            .ffp-student-summary { display:grid; grid-template-columns:repeat(5, minmax(120px, 1fr)); gap:10px; margin-bottom:14px; }
            .ffp-student-summary div { border:1px solid #e2e8f0; border-radius:8px; padding:9px 11px; }
            .ffp-student-summary span { color:#64748b; display:block; font-size:.7rem; font-weight:900; text-transform:uppercase; }
            .ffp-student-summary strong { color:#143521; display:block; font-size:.9rem; margin-top:3px; }
            .ffp-student-table { width:100%; border-collapse:collapse; }
            .ffp-student-table th, .ffp-student-table td { border-bottom:1px solid #edf3ef; padding:9px 10px; text-align:left; }
            .ffp-student-table th { color:#607264; font-size:.74rem; font-weight:900; text-transform:uppercase; }
            .ffp-student-empty { color:#64748b; padding:22px; text-align:center; }
            .ffp-profile-link { color:#146c43; font-weight:900; text-decoration:none; }
            .ffp-profile-link:hover { color:#0f5132; text-decoration:underline; }
            @media (max-width:760px){ .ffp-student-summary { grid-template-columns:1fr 1fr; } }
        </style>
        <div class="rfl-loading-header rfl-schedule-table-title">FACULTY SCHEDULE</div>
        <div class="app-table-wrap rfl-assigned-table">
            <table class="app-table" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Subject</th>
                        <th>Section</th>
                        <th>Room</th>
                        <th>Program</th>
                        <th>Students</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $facultyProfileUrl = route($facultyShowRoute, ['faculty' => $faculty->id, 'tab' => 'profile', 'school_year' => $selectedSchoolYear, 'semester' => $selectedSemester]);
                        $scheduleItems = $assignedSubjectsForSchedule->flatMap(function ($s) use ($facultyName, $facultyProfileUrl) {
                            $raw = strtoupper((string) $s->days);
                            $compact = str_replace(['TH', 'TTH'], ['R', 'TR'], preg_replace('/[^A-Z]/', '', $raw));
                            $map = ['M' => 'Monday', 'T' => 'Tuesday', 'W' => 'Wednesday', 'R' => 'Thursday', 'F' => 'Friday', 'S' => 'Saturday', 'U' => 'Sunday'];
                            $days = [];
                            foreach (str_split($compact) as $token) {
                                if (isset($map[$token])) $days[] = $map[$token];
                            }
                            if (!count($days)) $days = ['Unscheduled'];
                            return collect(array_unique($days))->map(function ($day) use ($s, $facultyName, $facultyProfileUrl) {
                                return [
                                    'day' => $day,
                                    'sort' => strtotime((string) $s->time_start) ?: 0,
                                    'time' => strtoupper((string) $s->formatted_time),
                                    'subject' => trim((string) $s->code . ' - ' . (string) $s->name),
                                    'section' => (string) $s->year_section,
                                    'room' => (string) $s->room,
                                    'program' => (string) optional($s->canonicalCourse)->code,
                                    'program_name' => (string) (optional($s->canonicalCourse)->description ?: optional($s->canonicalCourse)->name ?: optional($s->canonicalCourse)->code),
                                    'faculty_name' => $facultyName,
                                    'faculty_url' => $facultyProfileUrl,
                                    'students' => (int) ($s->students_count ?? 0),
                                    'student_rows' => $s->students->map(function ($student) {
                                        return [
                                            'student_no' => (string) ($student->student_no ?: ''),
                                            'name' => (string) ($student->name ?: ''),
                                            'program' => (string) (optional($student->canonicalCourse)->code ?: $student->program ?: ''),
                                            'year_level' => (string) ($student->year_level ?: ''),
                                            'profile_url' => route('registrar.registrar-menu.student-mgmt.student-records.profile', $student->id),
                                        ];
                                    })->values()->all(),
                                ];
                            });
                        })->sortBy(function ($item) {
                            $weights = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
                            return str_pad((string) ($weights[$item['day']] ?? 9), 2, '0', STR_PAD_LEFT) . '-' . str_pad((string) ($item['sort'] ?? 0), 6, '0', STR_PAD_LEFT);
                        })->values();
                    @endphp
                    @forelse($scheduleItems as $scheduleIndex => $item)
                        <tr>
                            <td>{{ $item['day'] }}</td>
                            <td>{{ $item['time'] ?: 'TBA' }}</td>
                            <td>{{ $item['subject'] }}</td>
                            <td>{{ $item['section'] ?: 'N/A' }}</td>
                            <td>{{ $item['room'] ?: 'TBA' }}</td>
                            <td>{{ $item['program'] ?: 'N/A' }}</td>
                            <td>{{ number_format($item['students']) }}</td>
                            <td>
                                <button type="button" class="ffp-student-list-btn" data-schedule-index="{{ (int) $scheduleIndex }}">View Students</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No schedule found for the selected term.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ffp-student-modal-backdrop" id="ffpStudentListModal" aria-hidden="true">
            <div class="ffp-student-modal" role="dialog" aria-modal="true" aria-labelledby="ffpStudentListTitle">
                <div class="ffp-student-modal-head">
                    <div>
                        <h3 class="ffp-student-modal-title" id="ffpStudentListTitle">Student List</h3>
                        <div class="ffp-student-modal-subtitle" id="ffpStudentListSubtitle"></div>
                    </div>
                    <button type="button" class="ffp-student-modal-close" id="ffpStudentListClose" aria-label="Close student list">&times;</button>
                </div>
                <div class="ffp-student-modal-body">
                    <div class="ffp-student-summary" id="ffpStudentListSummary"></div>
                    <div class="app-table-wrap">
                        <table class="ffp-student-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student No.</th>
                                    <th>Name</th>
                                    <th>Program</th>
                                    <th>Year Level</th>
                                </tr>
                            </thead>
                            <tbody id="ffpStudentListRows">
                                <tr><td colspan="5" class="ffp-student-empty">No students enrolled.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var scheduleItems = @json($scheduleItems->values());
                var modal = document.getElementById('ffpStudentListModal');
                var closeButton = document.getElementById('ffpStudentListClose');
                var title = document.getElementById('ffpStudentListTitle');
                var subtitle = document.getElementById('ffpStudentListSubtitle');
                var summary = document.getElementById('ffpStudentListSummary');
                var rows = document.getElementById('ffpStudentListRows');

                function escapeHtml(value) {
                    return String(value || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                }

                function linkHtml(label, url) {
                    if (!url) {
                        return escapeHtml(label || 'N/A');
                    }

                    return '<a class="ffp-profile-link" href="' + escapeHtml(url) + '" target="_blank" rel="noopener">' + escapeHtml(label || 'N/A') + '</a>';
                }

                function summaryItem(label, value, url) {
                    return '<div><span>' + escapeHtml(label) + '</span><strong>' + linkHtml(value || 'N/A', url || '') + '</strong></div>';
                }

                function openModal(data) {
                    if (!modal || !title || !subtitle || !summary || !rows) {
                        return;
                    }

                    title.textContent = data.subject || 'Student List';
                    subtitle.textContent = [data.day, data.time, data.room].filter(Boolean).join(' | ');
                    summary.innerHTML = [
                        summaryItem('Section', data.section),
                        summaryItem('Program', data.program_name || data.program),
                        summaryItem('Schedule', [data.day, data.time].filter(Boolean).join(' ')),
                        summaryItem('Faculty', data.faculty_name, data.faculty_url),
                        summaryItem('Students', data.students || 0)
                    ].join('');

                    var studentRows = Array.isArray(data.student_rows) ? data.student_rows : [];
                    if (!studentRows.length) {
                        rows.innerHTML = '<tr><td colspan="5" class="ffp-student-empty">No students enrolled.</td></tr>';
                    } else {
                        rows.innerHTML = studentRows.map(function (student, index) {
                            return '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + escapeHtml(student.student_no || '-') + '</td>' +
                                '<td>' + linkHtml(student.name || '-', student.profile_url || '') + '</td>' +
                                '<td>' + escapeHtml(student.program || '-') + '</td>' +
                                '<td>' + escapeHtml(student.year_level || '-') + '</td>' +
                            '</tr>';
                        }).join('');
                    }

                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                }

                function closeModal() {
                    if (!modal) {
                        return;
                    }
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                }

                document.addEventListener('click', function (event) {
                    var button = event.target.closest('[data-schedule-index]');
                    if (button) {
                        var index = parseInt(button.getAttribute('data-schedule-index') || '0', 10);
                        openModal(scheduleItems[index] || {});
                        return;
                    }

                    if (event.target && event.target.id === 'ffpStudentListModal') {
                        closeModal();
                    }
                });

                if (closeButton) {
                    closeButton.addEventListener('click', closeModal);
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeModal();
                    }
                });
            }());
        </script>
    @endif

    @if ($tab === 'load')
        <div class="rfl-load-card">
            @forelse($groupedSchedule as $courseName => $years)
                <div class="rfl-course-title">{{ strtoupper($courseName) }}</div>

                <div class="rfl-schedule-box">
                    @foreach($years as $yearLabel => $subjectsByKey)
                        <div class="rfl-year-title">{{ $yearLabel }}</div>

                        @foreach($subjectsByKey as $subjectKey => $subjectRows)
                            @php
                                $first = $subjectRows->first();
                            @endphp

                            <div class="rfl-subject-line">• {{ strtoupper($first->name) }} ( {{ $first->code }} )</div>
                            @foreach($subjectRows as $s)
                                @php
                                    $days = strtoupper(str_replace([',', ' '], ['/', ''], (string) $s->days));
                                    $start = str_replace(' ', '', (string) $s->time_start);
                                    $end = str_replace(' ', '', (string) $s->time_end);
                                    $section = trim((string) $s->year_section);
                                    $sectionText = $section !== '' ? (' ' . $section) : '';
                                @endphp
                                <div class="rfl-subject-schedule">– {{ $days }} | {{ $start }}-{{ $end }} | Room#{{ $s->room }} :{{ $sectionText }}</div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
            @empty
                <div class="text-muted">No faculty load found for the selected term.</div>
            @endforelse
        </div>
    @endif

    @if ($tab === 'loading')
        <div class="rfl-loading-wrap">

            <div class="rfl-loading-tools">
                <form method="GET" action="{{ route($facultyShowRoute, $faculty->id) }}" class="rfl-search">
                    <input type="hidden" name="tab" value="loading">
                    <input type="hidden" name="school_year" value="{{ $selectedSchoolYear }}">
                    <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                    <div class="app-filter-label">Search</div>
                    @include('registrar.components.search-bar', [
                        'id' => 'rflLoadingSearch',
                        'name' => 'loading_q',
                        'value' => $loadingSearch,
                        'placeholder' => 'Search Subject Code / Description / Section',
                        'containerClass' => 'rfl-search-form',
                        'inputClass' => 'js-rfl-auto-submit-search',
                        'inputAttributes' => [
                            'data-rfl-auto-submit-search' => '1',
                        ],
                    ])
                </form>

                <div class="rfl-loading-header">
                    <div class="rfl-loading-cols">SUBJECT CODE | DESCRIPTION | LEC | LAB | UNITS | SECTION | SCHEDULE</div>
                </div>
            </div>

            <form method="POST" action="{{ route('registrar.services.classroom-faculty.faculty-loads.assign', $faculty->id) }}" class="rfl-assign-form">
                @csrf
                <input type="hidden" name="school_year" value="{{ $selectedSchoolYear }}">
                <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                <input type="hidden" name="loading_q" value="{{ $loadingSearch }}">

                @php
                    $subjectSelectValue = (string) old('subject_id', '');
                    $subjectSelectOptions = count($availableSubjectOptions)
                        ? array_merge([
                            [
                                'value' => '',
                                'label' => 'Select available subject',
                            ],
                        ], $availableSubjectOptions)
                        : [];
                @endphp

                <div class="rfl-assign-top">
                    <div class="rfl-subject-select">
                        @include('registrar.components.listbox-select', [
                            'id' => 'rflSubjectSelect',
                            'name' => 'subject_id',
                            'options' => $subjectSelectOptions,
                            'selected' => $subjectSelectValue,
                            'placeholder' => count($availableSubjectOptions) ? 'list of available subjects' : 'No available subjects',
                        ])
                    </div>

                    <button id="rflAddSubjectBtn" type="submit" class="btn btn-secondary rfl-add-btn" {{ (count($availableSubjectOptions) && $subjectSelectValue !== '') ? '' : 'disabled' }}>Add Subject</button>
                </div>

                @if($availableSubjectsHasMore)
                    <div class="text-muted small mt-1">Showing first 200 matching subjects. Narrow the search to find more options.</div>
                @endif

                @if(!count($availableSubjectOptions))
                    <div class="text-muted small mt-1">No unassigned subjects found for the selected School Year and Term. Seed new subjects or unassign existing subjects first.</div>
                @endif

                <div class="rfl-assign-options">
                    <div class="rfl-load-type-group" role="radiogroup" aria-label="Load Type">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="load_type" id="ltRegular" value="Regular" {{ old('load_type', 'Regular') === 'Regular' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ltRegular">Regular</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="load_type" id="ltPart" value="Part-time" {{ old('load_type') === 'Part-time' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ltPart">Part-time</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="load_type" id="ltTemp" value="Temporary Substitution" {{ old('load_type') === 'Temporary Substitution' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ltTemp">Temporary Substitution</label>
                        </div>
                    </div>

                    <div class="rfl-metric-group">
                        <div class="rfl-num-wrap">
                            <label class="rfl-num-label">Credited Tuition Units:</label>
                            <input type="number" step="0.01" min="0" max="999.99" name="credited_tuition_units" class="form-control rfl-num" placeholder="Units" value="{{ old('credited_tuition_units') }}">
                        </div>

                        <div class="rfl-num-wrap">
                            <label class="rfl-num-label">Load Hours:</label>
                            <input type="number" step="0.01" min="0" max="999.99" name="load_hours" class="form-control rfl-num" placeholder="Units" value="{{ old('load_hours') }}">
                        </div>
                    </div>

                    <div class="text-muted small mt-1">Allowed range for Credited Tuition Units and Load Hours: 0.00 to 999.99 (up to 2 decimal places).</div>
                </div>
            </form>

            <div class="app-table-wrap rfl-assigned-table">
                <table class="app-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Description</th>
                            <th style="width:70px">Lec</th>
                            <th style="width:70px">Lab</th>
                            <th style="width:70px">Units</th>
                            <th style="width:120px">Section</th>
                            <th>Schedule</th>
                            <th style="width:120px">Type</th>
                            <th style="width:120px">Added by</th>
                            <th class="text-center" style="width:80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedSubjects as $s)
                            <tr>
                                <td class="td-code">{{ $s->code }}</td>
                                <td>{{ $s->name }}</td>
                                <td>{{ (int) ($s->lec ?? 0) }}</td>
                                <td>{{ (int) ($s->lab ?? 0) }}</td>
                                <td>{{ number_format($s->units, 1) }}</td>
                                <td>{{ $s->year_section }}</td>
                                <td>{{ strtoupper($s->days) }} {{ $s->formatted_time }} / {{ $s->room }}</td>
                                <td>{{ $s->load_type ?? '—' }}</td>
                                <td>{{ $s->added_by ?? '—' }}</td>
                                <td class="text-center">
                                    <button type="button" class="pf-dept-delete-btn mx-auto" title="Remove Subject" onclick="alert('Subject removal functionality will be available after backend API update.')">×</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">No subjects assigned.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="rfl-totals-row">
                            <td></td>
                            <td></td>
                            <td class="rfl-total-cell" style="color:#006837 !important;background:#f8fcf9 !important;text-align:left !important;font-weight:700 !important;">{{ (int) ($totals['lec'] ?? 0) }}</td>
                            <td class="rfl-total-cell" style="color:#006837 !important;background:#f8fcf9 !important;text-align:left !important;font-weight:700 !important;">{{ (int) ($totals['lab'] ?? 0) }}</td>
                            @php
                                $unitsTotal = (float) ($totals['units'] ?? 0);
                                $unitsDisplay = (floor($unitsTotal) == $unitsTotal)
                                    ? (string) (int) $unitsTotal
                                    : number_format($unitsTotal, 1);
                            @endphp
                            <td class="rfl-total-cell" style="color:#006837 !important;background:#f8fcf9 !important;text-align:left !important;font-weight:700 !important;">{{ $unitsDisplay }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="app-table-pager rfl-pagination">
                {{ $assignedSubjects->links() }}
            </div>

            @php
                $printGeneratedAt = now();
                $printClassification = $assignedSubjectsForSchedule->pluck('load_type')
                    ->filter(function ($value) {
                        return trim((string) $value) !== '';
                    })
                    ->unique()
                    ->values()
                    ->implode(', ');

                $printTermLabel = trim(
                    ($selectedSemester !== '' ? ($selectedSemester . ' Semester') : '') .
                    ($selectedSchoolYear !== '' ? (', SY ' . $selectedSchoolYear) : '')
                );

                if ($printTermLabel === '') {
                    $printTermLabel = 'SY -';
                }

                $printRows = $assignedSubjectsForSchedule->map(function ($subject) {
                    return [
                        'subject' => (string) $subject->name,
                        'code' => (string) $subject->code,
                        'section' => strtoupper(trim((string) $subject->year_section)),
                        'days' => strtoupper(str_replace([',', ' '], ['/', ''], (string) $subject->days)),
                        'time' => strtoupper((string) $subject->formatted_time),
                        'room' => strtoupper(trim((string) $subject->room)),
                        'units' => (float) ($subject->units ?? 0),
                        'lec' => (int) ($subject->lec ?? 0),
                        'lab' => (int) ($subject->lab ?? 0),
                        'total_hours' => is_null($subject->load_hours)
                            ? ((float) ($subject->lec ?? 0) + (float) ($subject->lab ?? 0))
                            : (float) $subject->load_hours,
                        'credited_tuition_units' => is_null($subject->credited_tuition_units)
                            ? null
                            : (float) $subject->credited_tuition_units,
                        'students' => (int) ($subject->students_count ?? 0),
                        'campus' => 'Pasig',
                        'type' => (string) ($subject->load_type ?? '—'),
                        'added_by' => (string) ($subject->added_by ?? '—'),
                    ];
                })->values();

                $printTotals = [
                    'lec' => (int) $printRows->sum('lec'),
                    'lab' => (int) $printRows->sum('lab'),
                    'units' => (float) $printRows->sum('units'),
                    'total_hours' => (float) $printRows->sum('total_hours'),
                    'credited_tuition_units' => (float) $printRows->reduce(function ($carry, $row) {
                        $value = $row['credited_tuition_units'];
                        return $carry + ($value === null ? 0 : (float) $value);
                    }, 0),
                    'students' => (int) $printRows->sum('students'),
                ];
            @endphp

            <div class="rfl-print-actions d-flex flex-wrap gap-2 mt-2 mb-3">
                <button type="button" class="btn btn-secondary rfl-add-btn" data-rfl-print-template="strength">
                    Print Strength of Classes
                </button>
                <button type="button" class="btn btn-secondary rfl-add-btn" data-rfl-print-template="assignment">
                    Print Faculty Assignment Form
                </button>
                <button type="button" class="btn btn-secondary rfl-add-btn" data-rfl-print-template="plotted">
                    Print Faculty Assignment Form - Plotted
                </button>
            </div>

            <div
                id="rflPrintConfig"
                class="d-none"
                data-favicon="{{ asset('img/logobg.png') }}"
                data-bootstrap-css="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
                data-app-css="{{ mix('css/app.css') }}"
                data-style-css="{{ mix('css/style.css') }}"
                data-print-css="{{ mix('css/registrar-faculty-loads.css') }}"
            ></div>

            <div class="rfl-loading-header rfl-schedule-table-title">FACULTY SCHEDULE</div>
            @php
                $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $scheduleByDay = array_fill_keys($weekDays, []);

                $extractDays = function ($rawDays) {
                    $raw = strtoupper((string) $rawDays);
                    $days = [];

                    $patternMap = [
                        'Monday' => '/MON(DAY)?/',
                        'Tuesday' => '/TUE(SDAY)?/',
                        'Wednesday' => '/WED(NESDAY)?/',
                        'Thursday' => '/THU(RSDAY)?/',
                        'Friday' => '/FRI(DAY)?/',
                        'Saturday' => '/SAT(URDAY)?/',
                        'Sunday' => '/SUN(DAY)?/',
                    ];

                    foreach ($patternMap as $dayName => $pattern) {
                        if (preg_match($pattern, $raw)) {
                            $days[] = $dayName;
                        }
                    }

                    if (!empty($days)) {
                        return array_values(array_unique($days));
                    }

                    $compact = preg_replace('/[^A-Z]/', '', $raw);
                    $i = 0;
                    while ($i < strlen($compact)) {
                        $next3 = substr($compact, $i, 3);
                        $next2 = substr($compact, $i, 2);
                        $next1 = substr($compact, $i, 1);

                        if ($next3 === 'THU') {
                            $days[] = 'Thursday';
                            $i += 3;
                            continue;
                        }
                        if ($next2 === 'TH') {
                            $days[] = 'Thursday';
                            $i += 2;
                            continue;
                        }
                        if ($next1 === 'M') {
                            $days[] = 'Monday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'T') {
                            $days[] = 'Tuesday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'W') {
                            $days[] = 'Wednesday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'R') {
                            $days[] = 'Thursday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'F') {
                            $days[] = 'Friday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'S') {
                            $days[] = 'Saturday';
                            $i += 1;
                            continue;
                        }
                        if ($next1 === 'U') {
                            $days[] = 'Sunday';
                            $i += 1;
                            continue;
                        }

                        $i += 1;
                    }

                    return array_values(array_unique($days));
                };

                foreach ($assignedSubjectsForSchedule as $s) {
                    $mappedDays = $extractDays($s->days ?? '');
                    $courseCode = trim((string) optional($s->canonicalCourse)->code);
                    $section = trim(($courseCode . ' ' . ($s->year_section ?? '')));
                    $entry = [
                        'time' => strtoupper((string) ($s->formatted_time ?? '')),
                        'code' => strtoupper((string) ($s->code ?? '')),
                        'section' => strtoupper($section),
                        'room' => strtoupper((string) ($s->room ?? '')),
                        'sort' => strtotime((string) ($s->time_start ?? '')) ?: 0,
                    ];

                    foreach ($mappedDays as $d) {
                        if (isset($scheduleByDay[$d])) {
                            $scheduleByDay[$d][] = $entry;
                        }
                    }
                }

                foreach ($scheduleByDay as $d => $entries) {
                    usort($entries, function ($a, $b) {
                        return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0);
                    });
                    $scheduleByDay[$d] = $entries;
                }
            @endphp

            <div class="rfl-weekly-scroll so-weekly-scroll">
                <div class="rfl-weekly-board so-weekly-grid">
                    @foreach($weekDays as $dayName)
                        <div class="rfl-weekly-col so-weekly-col">
                            <div class="rfl-weekly-day so-weekly-day">{{ strtoupper($dayName) }}</div>
                            <div class="rfl-weekly-body so-weekly-body">
                                @forelse($scheduleByDay[$dayName] as $entry)
                                    <div class="rfl-weekly-card so-weekly-card">
                                        <div class="rfl-weekly-time so-weekly-time">{{ $entry['time'] }}</div>
                                        <div class="rfl-weekly-code so-weekly-code">{{ $entry['code'] }}</div>
                                        <div class="rfl-weekly-section so-weekly-section">{{ $entry['section'] !== '' ? $entry['section'] : '—' }}</div>
                                        <div class="rfl-weekly-room so-weekly-room">{{ $entry['room'] !== '' ? $entry['room'] : 'TBA' }}</div>
                                    </div>
                                @empty
                                    <div class="rfl-weekly-empty so-weekly-empty"></div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rfl-print-sources d-none" aria-hidden="true">
                <template id="rflPrintTemplate-strength">
                    @include('registrar.services.classroom-faculty.faculty-loads.partials.print-strength', [
                        'faculty' => $faculty,
                        'printRows' => $printRows,
                        'printTotals' => $printTotals,
                        'printGeneratedAt' => $printGeneratedAt,
                        'printClassification' => $printClassification,
                        'printTermLabel' => $printTermLabel,
                    ])
                </template>

                <template id="rflPrintTemplate-assignment">
                    @include('registrar.services.classroom-faculty.faculty-loads.partials.print-assignment', [
                        'faculty' => $faculty,
                        'printRows' => $printRows,
                        'printTotals' => $printTotals,
                        'printGeneratedAt' => $printGeneratedAt,
                        'printClassification' => $printClassification,
                        'printTermLabel' => $printTermLabel,
                    ])
                </template>

                <template id="rflPrintTemplate-plotted">
                    @include('registrar.services.classroom-faculty.faculty-loads.partials.print-assignment-plotted', [
                        'faculty' => $faculty,
                        'weekDays' => $weekDays,
                        'scheduleByDay' => $scheduleByDay,
                        'printGeneratedAt' => $printGeneratedAt,
                        'printTermLabel' => $printTermLabel,
                    ])
                </template>
            </div>

            <iframe id="rflPrintFrame" class="d-none" title="Faculty Loads Print Frame" aria-hidden="true"></iframe>

        </div>
    @endif

    @if ($tab === 'history')
        <div class="rfl-loading-header rfl-schedule-table-title">LOAD HISTORY</div>
        @forelse($loadHistory as $termLabel => $subjects)
            <div class="rfl-load-card" style="margin-bottom:14px;">
                <div class="rfl-course-title">{{ strtoupper($termLabel) }}</div>
                <div class="app-table-wrap rfl-assigned-table">
                    <table class="app-table" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Program</th>
                                <th>Section</th>
                                <th>Schedule</th>
                                <th>Room</th>
                                <th>Load</th>
                                <th>Students</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $s)
                                <tr>
                                    <td><strong>{{ $s->code }}</strong><div class="text-muted">{{ $s->name }}</div></td>
                                    <td>{{ optional($s->canonicalCourse)->code ?: 'N/A' }}</td>
                                    <td>{{ $s->year_section ?: 'N/A' }}</td>
                                    <td>{{ strtoupper($s->days) }} {{ $s->formatted_time }}</td>
                                    <td>{{ $s->room ?: 'TBA' }}</td>
                                    <td>{{ number_format((float) ($s->credited_tuition_units ?? 0), 1) }}</td>
                                    <td>{{ number_format((int) ($s->students_count ?? 0)) }}</td>
                                    <td>{{ $s->grading_status ?: 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-muted">No load history found.</div>
        @endforelse
    @endif

    @if ($tab === 'grades')
        <div class="rfl-loading-header rfl-schedule-table-title">GRADES HISTORY</div>
        <div class="app-table-wrap rfl-assigned-table">
            <table class="app-table" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th>Updated</th>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Section</th>
                        <th>Term</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Final</th>
                        <th>Average</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gradeHistory as $grade)
                        <tr>
                            <td>{{ $grade->updated_at ? \Illuminate\Support\Carbon::parse($grade->updated_at)->format('Y-m-d') : 'N/A' }}</td>
                            <td><strong>{{ $grade->student_no ?: 'N/A' }}</strong><div class="text-muted">{{ $grade->student_name ?: 'N/A' }}</div></td>
                            <td><strong>{{ $grade->subject_code }}</strong><div class="text-muted">{{ $grade->subject_name }}</div></td>
                            <td>{{ trim(($grade->course_code ? $grade->course_code . ' ' : '') . (string) $grade->year_section) ?: 'N/A' }}</td>
                            <td>{{ trim((string) $grade->school_year . ' ' . (string) $grade->term) ?: 'N/A' }}</td>
                            <td>{{ $grade->prelim !== null ? number_format((float) $grade->prelim, 2) : '-' }}</td>
                            <td>{{ $grade->midterm !== null ? number_format((float) $grade->midterm, 2) : '-' }}</td>
                            <td>{{ $grade->final !== null ? number_format((float) $grade->final, 2) : '-' }}</td>
                            <td>{{ $grade->final_average !== null ? number_format((float) $grade->final_average, 2) : '-' }}</td>
                            <td>{{ $grade->remarks ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">No grade history found for this faculty.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

</div>
<script>
    (function () {
        var modal = document.getElementById('ffpEditProfileModal');
        var openBtn = document.getElementById('ffpOpenEditProfile');
        var closeBtn = document.getElementById('ffpCloseEditProfile');
        var cancelBtn = document.getElementById('ffpCancelEditProfile');

        function openModal() {
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModal();
            });
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeModal();
        });
    })();
</script>
@endsection
