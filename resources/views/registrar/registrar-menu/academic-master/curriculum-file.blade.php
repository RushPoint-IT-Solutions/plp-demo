@extends('layouts.registrar')

@section('title', 'PLP - Curriculum File')
@section('page-title', 'CURRICULUM FILE')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .cf-setup-note {
        margin-top: 6px;
        color: #64748b;
        font-size: .78rem;
        line-height: 1.45;
    }
    .cf-setup-guide {
        margin: 0 0 14px;
        border: 1px solid #d7eadf;
        border-radius: 8px;
        background: #f3fbf6;
        padding: 10px 12px;
        color: #315a3f;
        font-size: .82rem;
        line-height: 1.45;
    }
    .cf-masterlist-print {
        display: none;
    }
    @media print {
        @page {
            size: legal portrait;
            margin: 0.35in;
        }
        body * {
            visibility: hidden !important;
        }
        .cf-masterlist-print,
        .cf-masterlist-print * {
            visibility: visible !important;
        }
        .cf-masterlist-print {
            display: block !important;
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            color: #111;
            font-family: "Times New Roman", Times, serif;
            font-size: 9px;
            line-height: 1.12;
        }
        .cf-masterlist-print-page {
            width: 100%;
        }
        .cf-masterlist-head {
            display: grid;
            grid-template-columns: 88px 1fr;
            align-items: center;
            width: 6.1in;
            margin: 0 auto 8px;
        }
        .cf-masterlist-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            justify-self: center;
        }
        .cf-masterlist-school {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 800;
            font-size: 18px;
            letter-spacing: .03em;
        }
        .cf-masterlist-address,
        .cf-masterlist-phone {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin-top: 2px;
        }
        .cf-masterlist-title {
            text-align: center;
            font-weight: 800;
            font-size: 16px;
            margin: 3px 0;
            letter-spacing: .02em;
        }
        .cf-masterlist-program {
            text-align: center;
            font-weight: 800;
            font-size: 17px;
            margin: 2px 0 8px;
            text-transform: uppercase;
        }
        .cf-masterlist-year-title {
            text-align: center;
            font-weight: 800;
            font-size: 13px;
            margin: 6px 0 3px;
        }
        .cf-masterlist-term-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            margin-bottom: 5px;
            break-inside: avoid;
        }
        .cf-masterlist-term-title {
            text-align: center;
            font-weight: 800;
            font-size: 12px;
            margin-bottom: 2px;
        }
        .cf-masterlist-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .cf-masterlist-table th,
        .cf-masterlist-table td {
            border: 1px solid #333;
            padding: 1px 3px;
            vertical-align: top;
        }
        .cf-masterlist-table th {
            text-align: center;
            font-weight: 800;
            font-size: 8px;
            white-space: nowrap;
        }
        .cf-masterlist-table td {
            font-size: 8px;
        }
        .cf-masterlist-table .num,
        .cf-masterlist-table .pre {
            text-align: center;
        }
        .cf-masterlist-table tfoot td {
            border: 0;
            font-weight: 800;
            text-align: center;
            padding-top: 2px;
        }
        .cf-masterlist-signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.1in;
            margin-top: 24px;
            padding: 0 6px;
            break-inside: avoid;
        }
        .cf-masterlist-sign-block {
            min-height: 82px;
            font-size: 11px;
        }
        .cf-masterlist-sign-block strong {
            display: block;
            margin-top: 30px;
            font-weight: 800;
        }
        .cf-masterlist-sign-block span {
            display: block;
        }
        .cf-masterlist-approved {
            margin-top: 18px;
        }
    }
</style>
@endpush

@section('content')
@php
    $curriculumMasterlist = $curriculumSummary['masterlist'] ?? ['curriculum_year' => '', 'program_name' => 'PROGRAM', 'years' => []];
    $formatCfNumber = function ($value) {
        $number = (float) $value;
        return abs($number - round($number)) < 0.01 ? (string) (int) round($number) : number_format($number, 1);
    };
    $formatCfYear = function ($label) {
        $text = strtolower((string) $label);
        if (strpos($text, '1') !== false || strpos($text, 'first') !== false) return 'FIRST YEAR';
        if (strpos($text, '2') !== false || strpos($text, 'second') !== false) return 'SECOND YEAR';
        if (strpos($text, '3') !== false || strpos($text, 'third') !== false) return 'THIRD YEAR';
        if (strpos($text, '4') !== false || strpos($text, 'fourth') !== false) return 'FOURTH YEAR';
        return strtoupper((string) $label);
    };
    $formatCfTerm = function ($label) {
        $text = strtolower((string) $label);
        if (strpos($text, '1') !== false || strpos($text, 'first') !== false) return 'FIRST SEMESTER';
        if (strpos($text, '2') !== false || strpos($text, 'second') !== false) return 'SECOND SEMESTER';
        if (strpos($text, 'summer') !== false) return 'SUMMER';
        return strtoupper((string) $label);
    };
@endphp
<div class="pf-page">
    @if(session('curriculum_file_success'))
        <div class="alert alert-success mb-3" role="alert">
            {{ session('curriculum_file_success') }}
        </div>
    @endif

    @if(session('curriculum_file_error'))
        <div class="alert alert-danger mb-3" role="alert">
            {{ session('curriculum_file_error') }}
        </div>
    @endif

    <div
        class="cf-page"
        id="curriculumFilePage"
        data-course-years='@json($courseYearMap)'
        data-selected-course-id="{{ $selectedCourseId ?: '' }}"
        data-selected-curriculum-year="{{ $selectedCurriculumYear }}"
        data-success="{{ session('curriculum_file_success', '') }}"
        data-error="{{ session('curriculum_file_error', '') }}"
        data-pre-requisites-url="{{ route('registrar.registrar-menu.academic-master.pre-requisites') }}"
        data-curriculum-file-url="{{ route('registrar.registrar-menu.academic-master.curriculum-file') }}"
        data-term-year-subjects='@json($termYearSubjectMap)'
    >
        <section class="cf-hero-card">
            <div>
                <p class="cf-eyebrow">Academic Master</p>
                <h2>Curriculum File</h2>
                <p>Build approved program curricula by date coverage, active year level, term, and courses. Published curricula feed Section Offering for applicant and enrollment preparation.</p>
            </div>
            <div class="cf-hero-steps" aria-label="Curriculum workflow">
                <span>Setup</span>
                <span>Validate</span>
                <span>Approve</span>
                <span>Publish</span>
            </div>
        </section>

        <section class="cfg-card cf-toolbar-card">
            <div class="cf-toolbar-grid">
                <div class="cf-filter-field">
                    <label class="req-modal-label" for="cfProgram">Program</label>
                    <select id="cfProgram" class="req-modal-input cf-select2" data-placeholder="Search program">
                        @forelse($courses as $course)
                            <option value="{{ $course->id }}" {{ (string) $selectedCourseId === (string) $course->id ? 'selected' : '' }}>
                                {{ $course->name ?: $course->description }}
                            </option>
                        @empty
                            <option value="">No Program Available</option>
                        @endforelse
                    </select>
                </div>
                <div class="cf-filter-field">
                    <label class="req-modal-label" for="cfCurriculumYear">Curriculum Year</label>
                    <select id="cfCurriculumYear" class="req-modal-input cf-select2" data-placeholder="Search curriculum year">
                        <option value="">Curriculum Year</option>
                    </select>
                </div>
                <div class="cf-toolbar-action">
                    <button type="button" class="pf-btn-new" id="cfViewListBtn">View List</button>
                    <button type="button" class="pf-btn-new" onclick="window.print()" {{ empty($curriculumMasterlist['years']) ? 'disabled' : '' }}>Print Masterlist</button>
                    <button type="button" class="pf-btn-new" id="cfOpenPrerequisitesBtn">Open Pre-Requisites</button>
                </div>
            </div>
        </section>

        <div class="cf-curriculum-layout">
            <section class="cfg-card cf-panel-card">
                <div class="cf-panel-heading">
                    <div>
                        <h3 class="cf-panel-title">Create / Update Curriculum Courses</h3>
                        <p>Select the active year level and term, then add the official courses that belong to that curriculum period.</p>
                    </div>
                </div>
                <div class="cf-setup-guide">
                    Follow the setup steps in order. Program and curriculum year define the curriculum record, term and year level define where the courses belong, and selected courses become the official curriculum subjects for that period.
                </div>
                <form class="cf-panel-form" id="cfSetupForm" method="POST" action="{{ route('registrar.registrar-menu.academic-master.curriculum-file.setup') }}">
                    @csrf

                    @if($errors->has('setup_course_id') || $errors->has('setup_curriculum_year') || $errors->has('setup_date_from') || $errors->has('setup_date_to') || $errors->has('setup_term_id') || $errors->has('setup_year_block_id') || $errors->has('setup_subject_ids'))
                        <div class="pf-form-error-box">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfSetupProgram">1. Select Program</label>
                        <select id="cfSetupProgram" name="setup_course_id" class="req-modal-input cf-select2" data-placeholder="Search program">
                            @forelse($courses as $course)
                                <option value="{{ $course->id }}" {{ (string) old('setup_course_id', $selectedCourseId ?: '') === (string) $course->id ? 'selected' : '' }}>
                                    {{ $course->name ?: $course->description }}
                                </option>
                            @empty
                                <option value="">No Program Available</option>
                            @endforelse
                        </select>
                        <div class="cf-setup-note">Choose the program that owns this curriculum. The saved courses will appear under this program in curriculum, prerequisites, and section offering workflows.</div>
                    </div>

                    <div class="cf-subtitle">2. Curriculum Year Coverage</div>
                    <div class="cf-setup-note">Set the date range for when this curriculum version is valid. If you leave the code blank, the system can derive it from these dates.</div>

                    <div class="cf-field-split">
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupDateFrom">Date From</label>
                            <input id="cfSetupDateFrom" name="setup_date_from" type="date" class="req-modal-input" value="{{ old('setup_date_from', $selectedDateFrom) }}">
                            <div class="cf-setup-note">Use the first effective date of this curriculum version.</div>
                        </div>
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupDateTo">Date To</label>
                            <input id="cfSetupDateTo" name="setup_date_to" type="date" class="req-modal-input" value="{{ old('setup_date_to', $selectedDateTo) }}">
                            <div class="cf-setup-note">Use the last effective date. It must be the same as or later than Date From.</div>
                        </div>
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfSetupCurriculumYear">Curriculum Year Code</label>
                        <input id="cfSetupCurriculumYear" name="setup_curriculum_year" type="text" class="req-modal-input" placeholder="Auto: 2026-2027" value="{{ old('setup_curriculum_year', $selectedCurriculumYear) }}">
                        <div class="cf-setup-note">This label groups curriculum courses, prerequisite setup, and section offering choices. Example: 2026-2027.</div>
                    </div>

                    <div class="cf-subtitle">3. Active Year Level and Term</div>
                    <div class="cf-setup-note">These selections decide the exact curriculum bucket where the selected courses will be placed.</div>

                    <div class="cf-field-split">
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupTerm">Term</label>
                            <select id="cfSetupTerm" name="setup_term_id" class="req-modal-input cf-select2" data-placeholder="Search term">
                                <option value="">Term</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ (string) old('setup_term_id') === (string) $semester->id ? 'selected' : '' }}>
                                        {{ $semester->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="cf-setup-note">Select the semester or term where these courses should be taken.</div>
                        </div>
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupYearLevel">Year Level</label>
                            <select id="cfSetupYearLevel" name="setup_year_block_id" class="req-modal-input cf-select2" data-placeholder="Search year level">
                                <option value="">Year Level</option>
                                @foreach($yearBlocks as $yearBlock)
                                    <option value="{{ $yearBlock->id }}" {{ (string) old('setup_year_block_id') === (string) $yearBlock->id ? 'selected' : '' }}>
                                        {{ $yearBlock->label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="cf-setup-note">Select the student year level for this group of curriculum courses.</div>
                        </div>
                    </div>

                    <div class="cf-subtitle">4. Add Courses for Selected Year Level</div>
                    <div class="cf-setup-note">Pick the Term and Year Level above first — courses already saved there will show as <strong>Already Added</strong> and pre-check automatically. Check more courses below to add them to the same term, then save.</div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCourseSearch">Course Search</label>
                        <div class="cf-course-search-row">
                            <input id="cfCourseSearch" type="text" class="req-modal-input" placeholder="Search course code or title">
                            <button type="button" class="cf-picker-quick-btn" id="cfSelectAllVisible">Select All Shown</button>
                            <button type="button" class="cf-picker-quick-btn cf-btn-outline" id="cfClearNewSelection">Clear New</button>
                        </div>
                        <div class="cf-setup-note">Filtering only changes what is visible in the picker; checked courses remain selected until you uncheck them.</div>
                    </div>

                    <div class="cf-picker-tally" id="cfPickerTally">
                        <div class="cf-picker-tally-item">
                            <span class="cf-picker-tally-value" id="cfTallyAssignedCount">0</span>
                            <span class="cf-picker-tally-label">already in this term</span>
                        </div>
                        <div class="cf-picker-tally-item">
                            <span class="cf-picker-tally-value" id="cfTallyNewCount">0</span>
                            <span class="cf-picker-tally-label">new course(s) to add</span>
                        </div>
                        <div class="cf-picker-tally-item cf-picker-tally-total">
                            <span class="cf-picker-tally-value" id="cfTallyUnits">0.0</span>
                            <span class="cf-picker-tally-label">total units for this term</span>
                        </div>
                    </div>

                    <div class="cf-course-picker" id="cfCoursePicker">
                        @forelse($availableSubjects as $subject)
                            @php
                                $units = (float) ($subject->units ?: (($subject->lec ?: 0) + ($subject->lab ?: 0)));
                                $oldSubjectIds = collect(old('setup_subject_ids', []))->map(function ($id) { return (string) $id; })->all();
                            @endphp
                            <label class="cf-course-option" data-course-text="{{ strtolower(($subject->code ?? '') . ' ' . ($subject->name ?? '')) }}" data-subject-id="{{ $subject->id }}">
                                <input type="checkbox" name="setup_subject_ids[]" value="{{ $subject->id }}" data-units="{{ $units }}" {{ in_array((string) $subject->id, $oldSubjectIds, true) ? 'checked' : '' }}>
                                <span class="cf-course-info">
                                    <span class="cf-course-code">{{ $subject->code }} <span class="cf-course-assigned-badge">Already Added</span></span>
                                    <span class="cf-course-title">{{ $subject->name }}</span>
                                    <span class="cf-course-units">{{ number_format($units, 1) }} units · {{ $subject->hours ? number_format((float) $subject->hours, 1) . ' hrs' : 'hrs N/A' }} · {{ $subject->course_type ?: 'Major' }}</span>
                                </span>
                            </label>
                        @empty
                            <div class="cf-course-empty">No course records available. Add courses in Course File first.</div>
                        @endforelse
                    </div>

                    <div class="cf-actions">
                        <button type="submit" class="pf-btn-new" id="cfSaveSetupBtn">Save Setup</button>
                        <button type="button" class="pf-btn-new" id="cfOpenPrerequisitesSetupBtn">Setup Pre/Co-Requisites</button>
                        <span class="cf-setup-note">After saving courses, open Pre/Co-Requisites to connect prerequisite, co-requisite, and equivalent course rules.</span>
                    </div>
                </form>
            </section>
        </div>

        <section class="cfg-card cf-panel-card cf-summary-card">
            <div class="cf-summary-head">
                <div>
                    <h3 class="cf-panel-title cf-summary-title">Review Curriculum Summary</h3>
                    <div class="cf-summary-sub">Validation, unit totals, approval workflow, and publishing readiness</div>
                </div>
                <div class="cf-status-stack">
                    <span class="cf-status-pill">{{ $curriculumSummary['approval_status'] }}</span>
                    <span class="cf-status-pill {{ $curriculumSummary['is_published'] ? 'is-published' : '' }}">
                        {{ $curriculumSummary['is_published'] ? 'Published' : 'Not Published' }}
                    </span>
                </div>
            </div>

            <div class="cf-summary-grid">
                <div class="cf-summary-panel">
                    <div class="cf-summary-label">Total Program Units</div>
                    <div class="cf-summary-value">{{ number_format((float) $curriculumSummary['program_total_units'], 1) }}</div>
                    <div class="cf-summary-note">Expected: {{ (float) $curriculumSummary['expected_total_units'] > 0 ? number_format((float) $curriculumSummary['expected_total_units'], 1) : 'Not set' }}</div>
                </div>

                <div class="cf-summary-panel">
                    <div class="cf-summary-label">Validation Rules</div>
                    @if(count($curriculumSummary['issues']))
                        <ul class="cf-validation-list">
                            @foreach($curriculumSummary['issues'] as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="cf-valid-message">No duplicate courses, missing units, invalid requisites, term conflicts, or unit-total issues detected.</div>
                    @endif
                </div>

                <div class="cf-summary-panel">
                    <div class="cf-summary-label">Approval Workflow</div>
                    <div class="cf-workflow-list">
                        @foreach($curriculumSummary['workflow'] as $step)
                            <div class="cf-workflow-step {{ $step['done'] ? 'is-done' : '' }}">
                                <span></span>
                                <div>
                                    <strong>{{ $step['label'] }}</strong>
                                    <small>{{ $step['done'] ? $step['date'] : 'Pending' }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="cf-unit-grid">
                <div>
                    <div class="cf-summary-label">Total Units Per Term</div>
                    @forelse($curriculumSummary['term_totals'] as $row)
                        <div class="cf-unit-row"><span>{{ $row['label'] }}</span><strong>{{ number_format((float) $row['units'], 1) }}</strong></div>
                    @empty
                        <div class="cf-unit-empty">No term totals yet.</div>
                    @endforelse
                </div>
                <div>
                    <div class="cf-summary-label">Total Units Per Year</div>
                    @forelse($curriculumSummary['year_totals'] as $row)
                        <div class="cf-unit-row"><span>{{ $row['label'] }}</span><strong>{{ number_format((float) $row['units'], 1) }}</strong></div>
                    @empty
                        <div class="cf-unit-empty">No year totals yet.</div>
                    @endforelse
                </div>
            </div>

            @if($selectedCurriculum)
                <div class="cf-workflow-actions">
                    @foreach([
                        'department_head' => 'Department Head',
                        'dean' => 'Dean',
                        'registrar' => 'Registrar',
                        'academic_council' => 'Academic Council',
                    ] as $action => $label)
                        <form method="POST" action="{{ route('registrar.registrar-menu.academic-master.curriculum-file.workflow', $selectedCurriculum) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="action" value="{{ $action }}">
                            <button type="submit" class="pf-btn-new">{{ $label }} Approval</button>
                        </form>
                    @endforeach
                    <form method="POST" action="{{ route('registrar.registrar-menu.academic-master.curriculum-file.workflow', $selectedCurriculum) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="publish">
                        <button type="submit" class="pf-btn-new">Publish Curriculum</button>
                    </form>
                </div>
            @endif
        </section>
    </div>
</div>

<div class="req-modal-overlay cf-viewlist-overlay" id="cfViewListModal" style="display:none;" onclick="if(event.target===this){window.closeCfViewListModal();}">
    <div class="req-modal-box cf-viewlist-box">
        <button type="button" class="rep-modal-close-x" onclick="window.closeCfViewListModal()" aria-label="Close">&times;</button>

        <div class="cf-viewlist-head">
            <p class="cf-eyebrow">Curriculum Masterlist</p>
            <h3>{{ $curriculumMasterlist['program_name'] ?? 'PROGRAM' }}</h3>
            <p class="cf-viewlist-year">A.Y. {{ $curriculumMasterlist['curriculum_year'] ?: $selectedCurriculumYear ?: '—' }}</p>
        </div>

        <div class="cf-viewlist-body">
            @forelse($curriculumMasterlist['years'] as $year)
                <div class="cf-viewlist-year-block">
                    <div class="cf-viewlist-year-title">{{ $formatCfYear($year['label'] ?? '') }}</div>
                    @foreach(($year['semesters'] ?? []) as $semester)
                        <div class="cf-viewlist-term">
                            <div class="cf-viewlist-term-title">{{ $formatCfTerm($semester['label'] ?? '') }}</div>
                            <div class="cf-viewlist-table-wrap">
                                <table class="cf-viewlist-table">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Subject Description</th>
                                            <th>Prereq</th>
                                            <th>Lec</th>
                                            <th>Lab</th>
                                            <th>Units</th>
                                            <th>Hrs</th>
                                            <th class="cf-viewlist-actions-col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(($semester['subjects'] ?? []) as $subject)
                                            <tr>
                                                <td>{{ $subject['code'] ?: '-' }}</td>
                                                <td class="cf-viewlist-title-col">{{ $subject['title'] ?: '-' }}</td>
                                                <td>{{ $subject['prereq'] ?: 'None' }}</td>
                                                <td>{{ $formatCfNumber($subject['lec'] ?? 0) }}</td>
                                                <td>{{ $formatCfNumber($subject['lab'] ?? 0) }}</td>
                                                <td>{{ $formatCfNumber($subject['units'] ?? 0) }}</td>
                                                <td>{{ $formatCfNumber($subject['hours'] ?? 0) }}</td>
                                                <td class="cf-viewlist-actions-col">
                                                    @if(!empty($subject['id']))
                                                        <div class="cf-viewlist-row-actions">
                                                            <button
                                                                type="button"
                                                                class="cf-row-edit-btn"
                                                                data-edit-url="{{ route('registrar.registrar-menu.academic-master.curriculum-file.subject.update', $subject['id']) }}"
                                                                data-code="{{ $subject['code'] }}"
                                                                data-title="{{ $subject['title'] }}"
                                                                data-term-id="{{ $subject['semester_id'] ?? '' }}"
                                                                data-year-block-id="{{ $subject['year_block_id'] ?? '' }}"
                                                                data-units="{{ $subject['units'] ?? 0 }}"
                                                            >Edit</button>
                                                            <form
                                                                method="POST"
                                                                action="{{ route('registrar.registrar-menu.academic-master.curriculum-file.subject.delete', $subject['id']) }}"
                                                                class="cf-row-remove-form"
                                                                onsubmit="return confirm('Remove {{ addslashes($subject['code'] ?: 'this course') }} from this curriculum?');"
                                                            >
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="cf-row-remove-btn">Remove</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">No courses assigned.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">Total</td>
                                            <td>{{ $formatCfNumber($semester['totals']['lec'] ?? 0) }}</td>
                                            <td>{{ $formatCfNumber($semester['totals']['lab'] ?? 0) }}</td>
                                            <td>{{ $formatCfNumber($semester['totals']['units'] ?? 0) }}</td>
                                            <td>{{ $formatCfNumber($semester['totals']['hours'] ?? 0) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="cf-viewlist-empty">No curriculum courses saved yet for this program and curriculum year. Use the setup form to add courses first.</div>
            @endforelse
        </div>

        <div class="cf-viewlist-actions">
            <button type="button" class="pf-btn-new" onclick="window.print()" {{ empty($curriculumMasterlist['years']) ? 'disabled' : '' }}>Print</button>
            <button type="button" class="pf-btn-new cf-btn-outline" onclick="window.closeCfViewListModal()">Close</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay cf-edit-overlay" id="cfEditSubjectModal" style="display:none;" onclick="if(event.target===this){window.closeCfEditSubjectModal();}">
    <div class="req-modal-box cf-edit-box">
        <button type="button" class="rep-modal-close-x" onclick="window.closeCfEditSubjectModal()" aria-label="Close">&times;</button>

        <h3 class="cf-edit-title">Edit Curriculum Course</h3>
        <p class="cf-edit-subtitle" id="cfEditSubjectLabel"></p>

        <form method="POST" id="cfEditSubjectForm">
            @csrf
            @method('PUT')

            <div class="cf-field-row">
                <label class="req-modal-label" for="cfEditTerm">Term</label>
                <select id="cfEditTerm" name="edit_term_id" class="req-modal-input">
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="cf-field-row">
                <label class="req-modal-label" for="cfEditYearLevel">Year Level</label>
                <select id="cfEditYearLevel" name="edit_year_block_id" class="req-modal-input">
                    @foreach($yearBlocks as $yearBlock)
                        <option value="{{ $yearBlock->id }}">{{ $yearBlock->label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="cf-field-row">
                <label class="req-modal-label" for="cfEditUnits">Credited Units</label>
                <input id="cfEditUnits" name="edit_credited_units" type="number" step="0.5" min="0" class="req-modal-input">
            </div>

            <div class="cf-viewlist-actions">
                <button type="submit" class="pf-btn-new">Save Changes</button>
                <button type="button" class="pf-btn-new cf-btn-outline" onclick="window.closeCfEditSubjectModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<section class="cf-masterlist-print" aria-label="Curriculum Masterlist">
    <div class="cf-masterlist-print-page">
        <header class="cf-masterlist-head">
            <img class="cf-masterlist-logo" src="{{ asset('img/logobg.png') }}" alt="PLP Logo">
            <div>
                <div class="cf-masterlist-school">PAMANTASAN NG LUNGSOD NG PASIG</div>
                <div class="cf-masterlist-address">Alkalde Jose St. Kapasigan, Pasig City, Philippines 1600</div>
                <div class="cf-masterlist-phone">8628-1014</div>
            </div>
        </header>

        <div class="cf-masterlist-title">A.Y. {{ $curriculumMasterlist['curriculum_year'] ?: $selectedCurriculumYear }} CURRICULUM</div>
        <div class="cf-masterlist-program">{{ $curriculumMasterlist['program_name'] ?? 'PROGRAM' }}</div>

        @forelse($curriculumMasterlist['years'] as $year)
            <div class="cf-masterlist-year-title">{{ $formatCfYear($year['label'] ?? '') }}</div>
            <div class="cf-masterlist-term-grid">
                @foreach(($year['semesters'] ?? []) as $semester)
                    <div class="cf-masterlist-term">
                        <div class="cf-masterlist-term-title">{{ $formatCfTerm($semester['label'] ?? '') }}</div>
                        <table class="cf-masterlist-table">
                            <colgroup>
                                <col style="width: 14%;">
                                <col style="width: 46%;">
                                <col style="width: 12%;">
                                <col style="width: 7%;">
                                <col style="width: 7%;">
                                <col style="width: 7%;">
                                <col style="width: 7%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>COURSES</th>
                                    <th>SUBJECT DESCRIPTION</th>
                                    <th>PREREQ</th>
                                    <th>LEC.</th>
                                    <th>LAB.</th>
                                    <th>UNITS</th>
                                    <th>HRS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($semester['subjects'] ?? []) as $subject)
                                    <tr>
                                        <td>{{ $subject['code'] ?: '-' }}</td>
                                        <td>{{ $subject['title'] ?: '-' }}</td>
                                        <td class="pre">{{ $subject['prereq'] ?: 'None' }}</td>
                                        <td class="num">{{ $formatCfNumber($subject['lec'] ?? 0) }}</td>
                                        <td class="num">{{ $formatCfNumber($subject['lab'] ?? 0) }}</td>
                                        <td class="num">{{ $formatCfNumber($subject['units'] ?? 0) }}</td>
                                        <td class="num">{{ $formatCfNumber($subject['hours'] ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="num">No courses assigned.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"></td>
                                    <td>{{ $formatCfNumber($semester['totals']['lec'] ?? 0) }}</td>
                                    <td>{{ $formatCfNumber($semester['totals']['lab'] ?? 0) }}</td>
                                    <td>{{ $formatCfNumber($semester['totals']['units'] ?? 0) }}</td>
                                    <td>{{ $formatCfNumber($semester['totals']['hours'] ?? 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endforeach
            </div>
        @empty
            <p style="text-align:center;">No curriculum subjects available for printing.</p>
        @endforelse

        <div class="cf-masterlist-signatures">
            <div>
                <div class="cf-masterlist-sign-block">
                    <span>Prepared by:</span>
                    <strong>MAILA N. UNSAY, Ph.D</strong>
                    <span>Dean, College of Education</span>
                </div>
                <div class="cf-masterlist-sign-block cf-masterlist-approved">
                    <span>Approved by:</span>
                    <strong>GLICERIO M. MANINGAS, Ph.D</strong>
                    <span>University President</span>
                </div>
            </div>
            <div class="cf-masterlist-sign-block">
                <span>Noted by:</span>
                <strong>JIMMY C. CATANES, Ph.D., CESE</strong>
                <span>Director IV, CHED-NCR</span>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/curriculum-file.js') }}"></script>
@endpush
@endsection
