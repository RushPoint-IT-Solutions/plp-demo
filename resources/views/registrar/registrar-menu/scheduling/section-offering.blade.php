@extends('layouts.registrar')

@section('title', 'PLP - Section Offering')
@section('page-title', 'SECTION OFFERING')
@section('body-class', 'page-section-offering')

@php
    $schoolYearFilterOptions = array_merge([
        ['value' => '', 'label' => 'All School Years'],
    ], array_values($schoolYearOptions ?? []));

    $semesterFilterOptions = array_merge([
        ['value' => '', 'label' => 'All Semesters'],
    ], array_values($semesterOptions ?? []));

    $modalSchoolYearOptions = array_values($schoolYearOptions ?? []);
    $modalSemesterOptions = array_values($semesterOptions ?? [
        ['value' => 'First', 'label' => 'First'],
        ['value' => 'Second', 'label' => 'Second'],
        ['value' => 'Summer', 'label' => 'Summer'],
    ]);

    $activePrograms = array_values($activeProgramDirectory ?? []);
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .page-section-offering .so-program-select2 + .select2-container .select2-selection--single { min-height:38px; border:1px solid #cfd9d2; border-radius:8px; display:flex; align-items:center; padding:0 10px; }
    .page-section-offering .so-program-select2 + .select2-container .select2-selection__rendered { padding:0; line-height:36px; color:#143521; font-size:.85rem; }
    .page-section-offering .so-program-select2 + .select2-container .select2-selection__arrow { height:36px; }
    .page-section-offering .select2-dropdown { z-index:1300; }
    .so-tabs { display:flex; flex-wrap:wrap; gap:8px; margin:0 0 14px; }
    .so-tab-btn { background:#f8fafc; border:1px solid #d7e2dc; border-radius:8px; color:#315a3f; cursor:pointer; font-weight:900; min-height:38px; padding:8px 13px; }
    .so-tab-btn.is-active { background:#146c43; border-color:#146c43; color:#fff; }
    .so-tab-panel { display:none; }
    .so-tab-panel.is-active { display:block; }
    .so-active-program-card { background:#fff; border:1px solid #dfe8e2; border-radius:8px; margin-bottom:16px; padding:14px; }
    .so-active-program-grid { display:grid; grid-template-columns:1.1fr 1fr auto; gap:12px; align-items:end; }
    .so-active-program-results { display:grid; grid-template-columns:repeat(3, minmax(180px, 1fr)); gap:12px; margin-top:14px; }
    .so-active-program-panel { border:1px solid #e2e8f0; border-radius:8px; padding:12px; }
    .so-active-program-label { color:#64748b; display:block; font-size:.74rem; font-weight:900; text-transform:uppercase; }
    .so-active-program-value { color:#143521; display:block; font-size:1rem; font-weight:900; margin-top:4px; }
    .so-chip-list { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
    .so-chip { background:#eef7f1; border-radius:999px; color:#17633a; display:inline-flex; font-size:.78rem; font-weight:900; padding:5px 9px; }
    .so-chip.neutral { background:#eef2f7; color:#334155; }
    .so-available-section-row { border-top:1px solid #eef2f7; display:flex; justify-content:space-between; gap:10px; padding:8px 0; }
    .so-section-view-btn { background:#fff; border:1px solid #b9d6c5; border-radius:7px; color:#145c39; cursor:pointer; font-size:.78rem; font-weight:900; min-height:30px; padding:5px 10px; }
    @media (max-width:900px) {
        .so-active-program-grid, .so-active-program-results { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')
<div
    class="pf-page"
    id="sectionOfferingPage"
    data-fetch-url="{{ route('registrar.registrar-menu.scheduling.section-offering.data') }}"
    data-store-url="{{ route('registrar.registrar-menu.scheduling.section-offering.store') }}"
    data-curriculum-url="{{ route('registrar.registrar-menu.scheduling.section-offering.curriculum-subjects') }}"
    data-active-programs='@json($activePrograms)'
>
    <div class="so-tabs" role="tablist" aria-label="Section Offering views">
        <button type="button" class="so-tab-btn is-active" data-so-tab="directory">Section Directory</button>
        <button type="button" class="so-tab-btn" data-so-tab="active-programs">Active Program Search</button>
    </div>

    <div class="so-tab-panel is-active" id="soTabDirectory">
        {{-- Filter Bar --}}
        <div class="sched-filter-bar">
        <div class="sched-filter-row sched-filter-row-main so-filter-row">
            <div class="sched-filter-group so-filter-search">
                <span class="app-filter-label">Search Section</span>
                <input type="text" class="app-filter-input" id="soSectionSearch" placeholder="Type section, professor, or course">
            </div>
            <div class="sched-filter-group so-filter-sy">
                <span class="app-filter-label">School Year</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'soSY',
                    'name' => 'school_year',
                    'options' => $schoolYearFilterOptions,
                    'selected' => '',
                    'placeholder' => 'All School Years',
                ])
            </div>
            <div class="sched-filter-group so-filter-term">
                <span class="app-filter-label">Semester</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'soTerm',
                    'name' => 'semester',
                    'options' => $semesterFilterOptions,
                    'selected' => '',
                    'placeholder' => 'All Semesters',
                ])
            </div>
            <div class="sched-filter-group so-filter-year">
                <span class="app-filter-label">Year Level</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'soYearLevel',
                    'name' => 'year_level',
                    'options' => [
                        ['value' => '', 'label' => 'All Year Levels'],
                        ['value' => 'First', 'label' => 'First'],
                        ['value' => 'Second', 'label' => 'Second'],
                        ['value' => 'Third', 'label' => 'Third'],
                        ['value' => 'Fourth', 'label' => 'Fourth'],
                    ],
                    'selected' => '',
                    'placeholder' => 'All Year Levels',
                ])
            </div>
            <div class="sched-filter-group so-filter-section">
                <span class="app-filter-label">Section</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'soSection',
                    'name' => 'section',
                    'options' => [['value' => '', 'label' => 'All Sections']],
                    'selected' => '',
                    'placeholder' => 'All Sections',
                ])
            </div>
            <div class="sched-filter-group sched-filter-group-lg so-filter-program">
                <span class="app-filter-label">Course</span>
                <select id="soProgram" name="course_id" class="app-filter-select so-program-select2" data-placeholder="All Courses">
                    <option value="">All Courses</option>
                </select>
            </div>
        </div>
        </div>

        {{-- Sections Directory --}}
        <div class="so-card so-directory-card" id="soSectionListCard">
        <div class="so-card-header">
            <div class="so-card-title">Section Directory</div>
            <div class="so-directory-header-actions">
                <div class="so-directory-note" id="soDirectoryNote">Select a section to view subjects and weekly schedule.</div>
                <button type="button" class="pf-btn-new so-add-btn" id="soOpenAddSection">Add Section</button>
            </div>
        </div>

        <div class="student-table-wrapper table-responsive" id="soSectionTableWrap">
            <table class="student-table registrar-table" id="soSectionTable" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Section</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Year Level</th>
                        <th>Slots</th>
                        <th>Professor</th>
                        <th>Subjects</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="soSectionListBody">
                    {{-- JS-rendered rows --}}
                </tbody>
            </table>
        </div>

        <div class="sf-pagination-bar" id="soSectionPaginationBar">
            <div class="sf-pagination-left">
                <span class="pf-page-info" id="soSectionPageText">Showing 0 sections</span>
            </div>
            <div class="rtp-pagination">
                <nav class="rtp-nav" aria-label="Section Offering pagination">
                    <div class="rtp-list" role="group" aria-label="Page controls">
                        <button type="button" class="rtp-page-btn" id="soPrevBtn" aria-label="Previous page">&lt;</button>
                        <div class="rtp-pages" id="soPageNumbers"></div>
                        <button type="button" class="rtp-page-btn" id="soNextBtn" aria-label="Next page">&gt;</button>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    </div>

    <div class="so-tab-panel" id="soTabActivePrograms">
        <div class="so-active-program-card">
            <div class="so-active-program-grid">
                <div>
                    <label class="app-filter-label" for="soActiveProgramSearch">Search Active Program</label>
                    <input type="text" class="app-filter-input" id="soActiveProgramSearch" placeholder="Type program code or name">
                </div>
                <div>
                    <label class="app-filter-label" for="soActiveProgramSelect">Active Program</label>
                    <select class="app-filter-input" id="soActiveProgramSelect">
                        @forelse($activePrograms as $program)
                            <option value="{{ $program['course_id'] }}">{{ $program['label'] }}</option>
                        @empty
                            <option value="">No active programs found</option>
                        @endforelse
                    </select>
                </div>
                <button type="button" class="pf-btn-new" id="soActiveProgramApply">View Availability</button>
            </div>
            <div class="so-active-program-results" id="soActiveProgramResults">
                <div class="so-active-program-panel">
                    <span class="so-active-program-label">Program</span>
                    <span class="so-active-program-value">Select an active program</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Section Offering Card --}}
    <div class="so-card" id="soCard" style="display:none;">
        <div class="so-card-header">
            <div class="so-card-title" id="soCardTitle">Section Offering: A</div>
            <div class="so-card-header-actions">
                <button type="button" class="pf-back-btn so-back-btn" id="soBackToDirectory">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back to Directory
                </button>
                <button type="button" class="pf-btn-new so-print-btn">Print Class Program</button>
            </div>
        </div>

        <div class="student-table-wrapper table-responsive" id="soTableWrap">
            <table class="student-table registrar-table" id="soTable" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Description</th>
                        <th>Lec</th>
                        <th>Lab</th>
                        <th>Hours</th>
                        <th>Tuition Units</th>
                        <th>Cred. Units</th>
                        <th>Section</th>
                        <th>Room No</th>
                        <th>Professor</th>
                        <th>Slots</th>
                        <th>Schedule</th>
                    </tr>
                </thead>
                <tbody id="soBody">
                    {{-- JS-rendered rows --}}
                </tbody>
            </table>
        </div>

        <div class="pf-pagination" id="soPageInfo">
            <span class="pf-page-info" id="soPageText">Showing 0 subjects</span>
        </div>
    </div>

    {{-- Schedule Grid --}}
    <div class="so-weekly" id="soWeekly" style="display:none;">
        <div class="so-weekly-title">Class Schedule</div>
        <div class="so-weekly-scroll">
            <div class="so-weekly-grid" id="soWeeklyGrid"></div>
        </div>
    </div>

    {{-- Add Section Modal --}}
    <div class="so-modal" id="soAddSectionModal" aria-hidden="true">
        <div class="so-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="soAddSectionTitle">
            <div class="so-modal-header">
                <h3 class="so-modal-title" id="soAddSectionTitle">Add Section</h3>
                <button type="button" class="so-modal-close" id="soCloseAddSection" aria-label="Close">&times;</button>
            </div>

            <div class="so-modal-body">
                <div class="so-modal-grid">
                    <div class="so-modal-field so-modal-col-6">
                        <label for="soModalProgram">Program</label>
                        <select id="soModalProgram" name="modal_course_id" class="app-filter-input so-program-select2" data-placeholder="Select Course">
                            <option value="">Select Course</option>
                        </select>
                    </div>

                    <div class="so-modal-field so-modal-col-3">
                        <label for="soModalSY">School Year</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'soModalSY',
                            'name' => 'modal_school_year',
                            'options' => $modalSchoolYearOptions,
                            'selected' => (string) ($defaultSchoolYear ?? ''),
                            'placeholder' => 'Select School Year',
                        ])
                    </div>

                    <div class="so-modal-field so-modal-col-3">
                        <label for="soModalTerm">Term</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'soModalTerm',
                            'name' => 'modal_semester',
                            'options' => $modalSemesterOptions,
                            'selected' => (string) ($defaultSemester ?? 'First'),
                            'placeholder' => 'Select Term',
                        ])
                    </div>

                    <div class="so-modal-field so-modal-col-4">
                        <label for="soModalYearLevel">Year Level</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'soModalYearLevel',
                            'name' => 'modal_year_level',
                            'options' => [
                                ['value' => 'First', 'label' => 'First Year'],
                                ['value' => 'Second', 'label' => 'Second Year'],
                                ['value' => 'Third', 'label' => 'Third Year'],
                                ['value' => 'Fourth', 'label' => 'Fourth Year'],
                            ],
                            'selected' => 'First',
                            'placeholder' => 'Select Year Level',
                        ])
                    </div>

                    <div class="so-modal-field so-modal-col-4">
                        <label for="soModalSection">Section</label>
                        <input id="soModalSection" type="text" class="app-filter-input" placeholder="A / B / C / D">
                    </div>

                    <div class="so-modal-field so-modal-col-4">
                        <label for="soModalSlots">Slots</label>
                        <input id="soModalSlots" type="number" class="app-filter-input" min="1" max="80" value="30">
                    </div>

                    <div class="so-modal-field so-modal-col-6">
                        <label for="soModalAdviser">Professor</label>
                        <input id="soModalAdviser" type="text" class="app-filter-input" placeholder="Professor name">
                    </div>

                    <div class="so-modal-field so-modal-col-6">
                        <label for="soModalDescription">Description</label>
                        <input id="soModalDescription" type="text" class="app-filter-input" placeholder="Optional section notes">
                    </div>

                    <div class="so-modal-field so-modal-col-12">
                        <label class="setup-checkbox-label" for="soModalAutoSchedule">
                            <input type="checkbox" id="soModalAutoSchedule" class="req-checkbox-input" checked>
                            Auto-generate room and schedule for included courses
                        </label>
                    </div>

                    <div class="so-modal-field so-modal-col-12">
                        <label>Published Curriculum Courses</label>
                        <div class="so-curriculum-picker">
                            <div class="so-curriculum-list-wrap">
                                <div class="so-curriculum-list-title">Available Courses</div>
                                <select id="soCurriculumAvailable" class="so-curriculum-list" multiple size="8" aria-label="Available published curriculum courses"></select>
                            </div>

                            <div class="so-curriculum-actions" aria-label="Move curriculum subjects">
                                <button type="button" class="so-curriculum-btn" id="soCurriculumAdd" title="Add selected">Add &gt;</button>
                                <button type="button" class="so-curriculum-btn" id="soCurriculumAddAll" title="Add all">Add All &gt;&gt;</button>
                                <button type="button" class="so-curriculum-btn" id="soCurriculumRemove" title="Remove selected">&lt; Remove</button>
                                <button type="button" class="so-curriculum-btn" id="soCurriculumRemoveAll" title="Remove all">&lt;&lt; Remove All</button>
                            </div>

                            <div class="so-curriculum-list-wrap">
                                <div class="so-curriculum-list-title">Courses Included</div>
                                <select id="soCurriculumIncluded" class="so-curriculum-list" multiple size="8" aria-label="Published curriculum courses included in section"></select>
                            </div>
                        </div>
                        <div class="so-curriculum-summary" id="soCurriculumSummary">0 courses selected</div>
                    </div>
                </div>

                <div class="so-modal-feedback" id="soModalFeedback"></div>
            </div>

            <div class="so-modal-footer">
                <button type="button" class="so-modal-btn so-modal-btn-cancel" id="soCancelAddSection">Cancel</button>
                <button type="button" class="so-modal-btn so-modal-btn-primary" id="soSaveAddSection">Save Section</button>
            </div>
        </div>
    </div>

    {{-- Section Schedule Configuration Modal --}}
    <div class="so-modal so-config-modal" id="soSectionConfigModal" aria-hidden="true">
        <div class="so-modal-dialog so-config-dialog" role="dialog" aria-modal="true" aria-labelledby="soSectionConfigTitle">
            <div class="so-modal-header so-config-header">
                <h3 class="so-modal-title so-config-title" id="soSectionConfigTitle">Section Offering Configuration</h3>
                <button type="button" class="so-modal-close" id="soSectionConfigClose" aria-label="Close">&times;</button>
            </div>

            <div class="so-modal-body so-config-body">
                <div class="so-config-meta" id="soSectionConfigMeta"></div>

                <div class="so-config-table-wrap">
                    <table class="student-table registrar-table so-config-table" id="soSectionConfigTable" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Description</th>
                                <th>Room</th>
                                <th>Professor</th>
                                <th>Slots</th>
                                <th>Schedule</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="soSectionConfigBody"></tbody>
                    </table>
                </div>

                <p class="so-config-note">Configure or edit each subject schedule below. Changes update the current page view.</p>
            </div>

            <div class="so-modal-footer so-config-footer">
                <button type="button" class="so-modal-btn so-modal-btn-cancel" id="soSectionConfigDone">Done</button>
            </div>
        </div>
    </div>

    {{-- Subject Schedule Modal --}}
    <div class="so-modal so-subject-modal" id="soSubjectScheduleModal" aria-hidden="true">
        <div class="so-modal-dialog so-subject-dialog" role="dialog" aria-modal="true" aria-labelledby="soSubjectScheduleTitle">
            <div class="so-modal-header so-subject-header">
                <h3 class="so-modal-title" id="soSubjectScheduleTitle">Configure Subject Schedule</h3>
                <button type="button" class="so-modal-close" id="soSubjectScheduleClose" aria-label="Close">&times;</button>
            </div>

            <div class="so-modal-body so-subject-body">
                <div class="so-subject-summary" id="soSubjectScheduleTag">-</div>

                <div class="so-subject-meta-grid">
                    <div class="so-modal-field so-subject-meta-field">
                        <label for="soSubjectSectionCode">Section Code</label>
                        <input id="soSubjectSectionCode" type="text" class="app-filter-input" readonly>
                    </div>

                    <div class="so-modal-field so-subject-meta-field">
                        <label for="soSubjectDescription">Description</label>
                        <input id="soSubjectDescription" type="text" class="app-filter-input" placeholder="Subject description">
                    </div>

                    <div class="so-modal-field so-subject-meta-field">
                        <label for="soSubjectSlots">Total Slots</label>
                        <input id="soSubjectSlots" type="number" class="app-filter-input" min="0" max="80" value="0">
                    </div>

                    <div class="so-modal-field so-subject-meta-field">
                        <label for="soSubjectProfessor">Professor</label>
                        <input id="soSubjectProfessor" type="text" class="app-filter-input" placeholder="Professor name">
                    </div>

                    <div class="so-modal-field so-subject-meta-field so-subject-meta-note-wrap">
                        <p class="so-subject-meta-note" id="soSubjectScheduleWarning">Some students are already enrolled to this section; changing section code is not allowed.</p>
                    </div>
                </div>

                <div class="so-modal-grid">
                    <div class="so-modal-field so-modal-col-12">
                        <label>Section Flags</label>
                        <div class="so-subject-flags">
                            <label class="setup-checkbox-label" for="soSubjectFlagOpen"><input type="checkbox" id="soSubjectFlagOpen" class="req-checkbox-input"> Open</label>
                            <label class="setup-checkbox-label" for="soSubjectFlagBlock"><input type="checkbox" id="soSubjectFlagBlock" class="req-checkbox-input"> Block</label>
                            <label class="setup-checkbox-label" for="soSubjectFlagTutorial"><input type="checkbox" id="soSubjectFlagTutorial" class="req-checkbox-input"> Tutorial</label>
                        </div>
                    </div>

                    <div class="so-modal-field so-modal-col-12">
                        <label>Weekly Schedule</label>
                        <div class="so-subject-table-wrap">
                            <table class="student-table registrar-table so-subject-table" data-no-auto-pager="1">
                                <thead>
                                    <tr>
                                        <th>Day Of Week</th>
                                        <th>Use</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Room No.</th>
                                        <th>Lab</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="soSubjectScheduleRows"></tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="so-modal-feedback" id="soSubjectScheduleFeedback"></div>
            </div>

            <div class="so-modal-footer">
                <button type="button" class="so-modal-btn so-modal-btn-cancel" id="soSubjectScheduleCancel">Cancel</button>
                <button type="button" class="so-modal-btn so-modal-btn-primary" id="soSubjectScheduleSave">Save Schedule</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/section-offering.js') }}?v={{ file_exists(public_path('js/section-offering.js')) ? filemtime(public_path('js/section-offering.js')) : time() }}"></script>
<script>
    (function () {
        var root = document.getElementById('sectionOfferingPage');
        if (!root) {
            return;
        }

        var tabs = root.querySelectorAll('[data-so-tab]');
        var directoryPanel = document.getElementById('soTabDirectory');
        var activePanel = document.getElementById('soTabActivePrograms');
        tabs.forEach(function (button) {
            button.addEventListener('click', function () {
                var target = button.getAttribute('data-so-tab');
                tabs.forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });
                if (directoryPanel) {
                    directoryPanel.classList.toggle('is-active', target === 'directory');
                }
                if (activePanel) {
                    activePanel.classList.toggle('is-active', target === 'active-programs');
                }
            });
        });

        var programs = [];
        try {
            programs = JSON.parse(root.getAttribute('data-active-programs') || '[]');
        } catch (error) {
            programs = [];
        }

        var searchInput = document.getElementById('soActiveProgramSearch');
        var select = document.getElementById('soActiveProgramSelect');
        var apply = document.getElementById('soActiveProgramApply');
        var results = document.getElementById('soActiveProgramResults');

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function chips(items, emptyText) {
            if (!items || !items.length) {
                return '<span class="so-chip neutral">' + escapeHtml(emptyText) + '</span>';
            }
            return items.map(function (item) {
                return '<span class="so-chip">' + escapeHtml(item) + '</span>';
            }).join('');
        }

        function findSelectedProgram() {
            var id = select ? String(select.value || '') : '';
            var matched = null;
            programs.some(function (program) {
                if (String(program.course_id) === id) {
                    matched = program;
                    return true;
                }
                return false;
            });
            return matched || programs[0] || null;
        }

        function renderProgram(program) {
            if (!results) {
                return;
            }
            if (!program) {
                results.innerHTML = '<div class="so-active-program-panel"><span class="so-active-program-label">Program</span><span class="so-active-program-value">No active program found</span></div>';
                return;
            }
            var sections = Array.isArray(program.sections) ? program.sections : [];
            var sectionHtml = sections.length
                ? sections.map(function (section) {
                    return '<div class="so-available-section-row"><div><strong>' + escapeHtml(section.section || '-') + '</strong><div class="so-muted">' + escapeHtml(section.year || 'N/A') + '</div></div><span class="so-chip neutral">' + escapeHtml(section.subject_count || 0) + ' subjects</span></div>';
                }).join('')
                : '<div class="so-available-section-row"><span class="so-muted">No generated sections yet for this active program.</span></div>';

            results.innerHTML =
                '<div class="so-active-program-panel"><span class="so-active-program-label">Program</span><span class="so-active-program-value">' + escapeHtml(program.label || '-') + '</span><div class="so-chip-list"><span class="so-chip neutral">' + escapeHtml(program.subject_count || 0) + ' curriculum subjects</span><span class="so-chip neutral">' + escapeHtml(program.curriculum_count || 0) + ' active curriculum</span></div></div>' +
                '<div class="so-active-program-panel"><span class="so-active-program-label">Available Year Levels</span><div class="so-chip-list">' + chips(program.years || [], 'No year levels') + '</div><span class="so-active-program-label" style="margin-top:12px;">Terms</span><div class="so-chip-list">' + chips(program.terms || [], 'No terms') + '</div></div>' +
                '<div class="so-active-program-panel"><span class="so-active-program-label">Available Sections</span>' + sectionHtml + '</div>';
        }

        function filterProgramOptions() {
            if (!select) {
                return;
            }
            var query = searchInput ? String(searchInput.value || '').toLowerCase().trim() : '';
            var selected = String(select.value || '');
            select.innerHTML = '';
            programs.filter(function (program) {
                if (query === '') {
                    return true;
                }
                return String(program.label || '').toLowerCase().indexOf(query) !== -1;
            }).forEach(function (program) {
                var option = document.createElement('option');
                option.value = String(program.course_id);
                option.textContent = program.label || ('Program #' + program.course_id);
                select.appendChild(option);
            });
            if (selected && select.querySelector('option[value="' + selected + '"]')) {
                select.value = selected;
            }
            renderProgram(findSelectedProgram());
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterProgramOptions);
        }
        if (select) {
            select.addEventListener('change', function () {
                renderProgram(findSelectedProgram());
            });
        }
        if (apply) {
            apply.addEventListener('click', function () {
                renderProgram(findSelectedProgram());
            });
        }
        renderProgram(findSelectedProgram());
    }());
</script>
@endpush
