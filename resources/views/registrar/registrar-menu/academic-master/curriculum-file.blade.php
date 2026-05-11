@extends('layouts.registrar')

@section('title', 'PLP - Curriculum File')
@section('page-title', 'CURRICULUM FILE')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

@section('content')
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
                    </div>

                    <div class="cf-subtitle">2. Curriculum Year Coverage</div>

                    <div class="cf-field-split">
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupDateFrom">Date From</label>
                            <input id="cfSetupDateFrom" name="setup_date_from" type="date" class="req-modal-input" value="{{ old('setup_date_from', $selectedDateFrom) }}">
                        </div>
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupDateTo">Date To</label>
                            <input id="cfSetupDateTo" name="setup_date_to" type="date" class="req-modal-input" value="{{ old('setup_date_to', $selectedDateTo) }}">
                        </div>
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfSetupCurriculumYear">Curriculum Year Code</label>
                        <input id="cfSetupCurriculumYear" name="setup_curriculum_year" type="text" class="req-modal-input" placeholder="Auto: 2026-2027" value="{{ old('setup_curriculum_year', $selectedCurriculumYear) }}">
                    </div>

                    <div class="cf-subtitle">3. Active Year Level and Term</div>

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
                        </div>
                    </div>

                    <div class="cf-subtitle">4. Add Courses for Selected Year Level</div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCourseSearch">Course Search</label>
                        <input id="cfCourseSearch" type="text" class="req-modal-input" placeholder="Search course code or title">
                    </div>

                    <div class="cf-course-picker" id="cfCoursePicker">
                        @forelse($availableSubjects as $subject)
                            @php
                                $units = (float) ($subject->units ?: (($subject->lec ?: 0) + ($subject->lab ?: 0)));
                                $oldSubjectIds = collect(old('setup_subject_ids', []))->map(function ($id) { return (string) $id; })->all();
                            @endphp
                            <label class="cf-course-option" data-course-text="{{ strtolower(($subject->code ?? '') . ' ' . ($subject->name ?? '')) }}">
                                <input type="checkbox" name="setup_subject_ids[]" value="{{ $subject->id }}" {{ in_array((string) $subject->id, $oldSubjectIds, true) ? 'checked' : '' }}>
                                <span class="cf-course-code">{{ $subject->code }}</span>
                                <span class="cf-course-title">{{ $subject->name }}</span>
                                <span class="cf-course-units">{{ number_format($units, 1) }} units · {{ $subject->hours ? number_format((float) $subject->hours, 1) . ' hrs' : 'hrs N/A' }} · {{ $subject->course_type ?: 'Major' }}</span>
                            </label>
                        @empty
                            <div class="cf-course-empty">No course records available. Add courses in Course File first.</div>
                        @endforelse
                    </div>

                    <div class="cf-actions">
                        <button type="submit" class="pf-btn-new" id="cfSaveSetupBtn">Save Setup</button>
                        <button type="button" class="pf-btn-new" id="cfOpenPrerequisitesSetupBtn">Setup Pre/Co-Requisites</button>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/curriculum-file.js') }}"></script>
@endpush
@endsection
