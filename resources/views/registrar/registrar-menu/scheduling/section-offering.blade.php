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
@endphp

@section('content')
<div
    class="pf-page"
    id="sectionOfferingPage"
    data-fetch-url="{{ route('registrar.registrar-menu.scheduling.section-offering.data') }}"
    data-store-url="{{ route('registrar.registrar-menu.scheduling.section-offering.store') }}"
    data-curriculum-url="{{ route('registrar.registrar-menu.scheduling.section-offering.curriculum-subjects') }}"
>

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
                @include('registrar.components.listbox-select', [
                    'id' => 'soProgram',
                    'name' => 'course_id',
                    'options' => [['value' => '', 'label' => 'All Courses']],
                    'selected' => '',
                    'placeholder' => 'All Courses',
                ])
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

    {{-- Section Offering Card --}}
    <div class="so-card" id="soCard" style="display:none;">
        <div class="so-card-header">
            <div class="so-card-title" id="soCardTitle">Section Offering: A</div>
            <div class="so-card-header-actions">
                <button type="button" class="pf-btn-clear so-back-btn" id="soBackToDirectory">Back to Directory</button>
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
                        @include('registrar.components.listbox-select', [
                            'id' => 'soModalProgram',
                            'name' => 'modal_course_id',
                            'options' => [['value' => '', 'label' => 'Select Course']],
                            'selected' => '',
                            'placeholder' => 'Select Course',
                        ])
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
                        <label>Curriculum Subjects</label>
                        <div class="so-curriculum-picker">
                            <div class="so-curriculum-list-wrap">
                                <div class="so-curriculum-list-title">Available Subjects</div>
                                <select id="soCurriculumAvailable" class="so-curriculum-list" multiple size="8" aria-label="Available curriculum subjects"></select>
                            </div>

                            <div class="so-curriculum-actions" aria-label="Move curriculum subjects">
                                <button type="button" class="so-curriculum-btn" id="soCurriculumAdd" title="Add selected">Add &gt;</button>
                                <button type="button" class="so-curriculum-btn" id="soCurriculumAddAll" title="Add all">Add All &gt;&gt;</button>
                                <button type="button" class="so-curriculum-btn" id="soCurriculumRemove" title="Remove selected">&lt; Remove</button>
                                <button type="button" class="so-curriculum-btn" id="soCurriculumRemoveAll" title="Remove all">&lt;&lt; Remove All</button>
                            </div>

                            <div class="so-curriculum-list-wrap">
                                <div class="so-curriculum-list-title">Subjects Included</div>
                                <select id="soCurriculumIncluded" class="so-curriculum-list" multiple size="8" aria-label="Curriculum subjects included in section"></select>
                            </div>
                        </div>
                        <div class="so-curriculum-summary" id="soCurriculumSummary">0 subjects selected</div>
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
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script src="{{ asset('js/section-offering.js') }}?v={{ file_exists(public_path('js/section-offering.js')) ? filemtime(public_path('js/section-offering.js')) : time() }}"></script>
@endpush
