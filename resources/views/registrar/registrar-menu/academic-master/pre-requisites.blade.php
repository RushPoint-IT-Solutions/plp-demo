@extends('layouts.registrar')

@section('title', 'PLP - Pre-requisites')
@section('page-title', 'PRE-REQUISITES')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

@section('content')
<div
    class="prereq-page"
    id="prereqPage"
    data-list-url="{{ route('registrar.registrar-menu.academic-master.pre-requisites.data') }}"
    data-download-url="{{ route('registrar.registrar-menu.academic-master.pre-requisites.download') }}"
    data-detail-url-template="{{ route('registrar.registrar-menu.academic-master.pre-requisites.subject.show', ['courseCurriculumSubjectId' => '__CURRICULUM_SUBJECT_ID__']) }}"
    data-save-url-template="{{ route('registrar.registrar-menu.academic-master.pre-requisites.subject.update', ['courseCurriculumSubjectId' => '__CURRICULUM_SUBJECT_ID__']) }}"
    data-subject-download-url-template="{{ route('registrar.registrar-menu.academic-master.pre-requisites.subject.download', ['courseCurriculumSubjectId' => '__CURRICULUM_SUBJECT_ID__']) }}"
    data-curriculum-url="{{ route('registrar.registrar-menu.academic-master.curriculum-file') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-course-years='@json($courseYearMap)'
    data-selected-course-id="{{ $selectedCourseId ?: '' }}"
    data-selected-curriculum-year="{{ $selectedCurriculumYear }}"
>

    {{-- ═══════════ VIEW 1: Filter Bar (always visible) ═══════════ --}}
    @if(session('prereq_success'))
        <div class="alert alert-success mb-3" role="alert">
            {{ session('prereq_success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-3" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="prereq-hero">
        <div>
            <p class="prereq-eyebrow">Academic Master</p>
            <h2>Pre/Co-Requisite Setup</h2>
            <p>Select a program curriculum, open a course, then assign required courses before or alongside enrollment.</p>
        </div>
    </section>

    <div class="prereq-filter-bar">
        <div class="prereq-filter-left">
            <div class="prereq-filter-group">
                <span class="app-filter-label">Program</span>
                <select class="app-filter-select prereq-select2" id="prereqCourse" data-placeholder="Search program">
                    @forelse($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) $selectedCourseId === (string) $course->id ? 'selected' : '' }}>
                            {{ $course->name ?: $course->description }}
                        </option>
                    @empty
                        <option value="">No programs available</option>
                    @endforelse
                </select>
            </div>
            <div class="prereq-filter-group prereq-filter-group-sm">
                <span class="app-filter-label">Curriculum Year</span>
                <select class="app-filter-select" id="prereqYear"></select>
            </div>
        </div>
        <div class="prereq-filter-right">
            <button type="button" class="prereq-view-btn" id="btnViewList">View List</button>
        </div>
    </div>

    {{-- ═══════════ VIEW 2: Subject List (hidden until View List clicked) ═══════════ --}}
    <div class="prereq-list-view" id="prereqListView" hidden>
        <div class="prereq-list-header">
            <div>
                <h2 class="prereq-program-title" id="prereqProgramTitle"></h2>
                <div class="prereq-program-subtitle">Click Setup on a course row to add pre-requisite and co-requisite courses.</div>
            </div>
            <div class="prereq-list-actions">
                <button type="button" class="prereq-download-btn" id="prereqAddCourseBtn">Add Course to Curriculum</button>
                <button type="button" class="prereq-download-btn" id="prereqDownloadBtn">Download PDF</button>
            </div>
        </div>

        <div id="prereqListContent">
            {{-- Dynamic year/semester tables injected here --}}
        </div>

        <div class="prereq-pagination" id="prereqPagination" hidden>
            <button type="button" class="prereq-pagination-btn" id="prereqPrevPage">Previous</button>
            <span class="prereq-pagination-info" id="prereqPageInfo">Page 1 of 1</span>
            <button type="button" class="prereq-pagination-btn" id="prereqNextPage">Next</button>
        </div>
    </div>

    {{-- ═══════════ VIEW 3: Subject Detail / Edit (hidden until row clicked) ═══════════ --}}
    <div class="prereq-detail-view" id="prereqDetailView" hidden>
        <div class="prereq-detail-banner">
            <div class="prereq-detail-info">
                <span class="prereq-detail-code" id="prereqDetailCode"></span>
                <span class="prereq-detail-name" id="prereqDetailName"></span>
            </div>
            <div class="prereq-detail-actions">
                <button type="button" class="prereq-download-btn" id="prereqBackBtn">Back to List</button>
                <button type="button" class="prereq-download-btn" id="prereqSubjectDownloadBtn">Download Course PDF</button>
                <button type="button" class="prereq-save-btn" id="prereqSaveBtn">Save Setup</button>
            </div>
        </div>

        {{-- Pre-requisite(s) --}}
        <div class="prereq-section">
            <div class="prereq-section-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
                Pre-requisite(s)
            </div>
            <div class="prereq-section-body">
                <div class="prereq-col">
                    <div class="prereq-search-row">
                        <input type="text" class="prereq-search-input" id="prereqSearchPre" data-type="pre" placeholder="Search...">
                        <button type="button" class="prereq-search-btn" data-action="search" data-type="pre">Search</button>
                    </div>
                    <div class="prereq-available-list" id="prereqAvailPre" data-type="pre">
                        {{-- Dynamic available subjects --}}
                    </div>
                </div>
                <div class="prereq-col">
                    <div class="prereq-selected-label">-list of pre-requisite course(s)-</div>
                    <div class="prereq-selected-list" id="prereqSelPre" data-type="pre">
                        {{-- Dynamic selected subjects --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Co-requisite(s) --}}
        <div class="prereq-section">
            <div class="prereq-section-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                Co-requisite(s)
            </div>
            <div class="prereq-section-body">
                <div class="prereq-col">
                    <div class="prereq-search-row">
                        <input type="text" class="prereq-search-input" id="prereqSearchCo" data-type="co" placeholder="Search...">
                        <button type="button" class="prereq-search-btn" data-action="search" data-type="co">Search</button>
                    </div>
                    <div class="prereq-available-list" id="prereqAvailCo" data-type="co">
                    </div>
                </div>
                <div class="prereq-col">
                    <div class="prereq-selected-label">-list of co-requisite course(s)-</div>
                    <div class="prereq-selected-list" id="prereqSelCo" data-type="co">
                    </div>
                </div>
            </div>
        </div>

        {{-- Equivalent Subject(s) --}}
        <div class="prereq-section">
            <div class="prereq-section-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3"/><path d="M16 3h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-3"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="16" x2="17" y2="16"/></svg>
                Equivalent Course(s)
            </div>
            <div class="prereq-section-body">
                <div class="prereq-col">
                    <div class="prereq-search-row">
                        <input type="text" class="prereq-search-input" id="prereqSearchEq" data-type="equivalent" placeholder="Search...">
                        <button type="button" class="prereq-search-btn" data-action="search" data-type="equivalent">Search</button>
                    </div>
                    <div class="prereq-available-list" id="prereqAvailEq" data-type="equivalent">
                    </div>
                </div>
                <div class="prereq-col">
                    <div class="prereq-selected-label">-list of equivalent course(s)-</div>
                    <div class="prereq-selected-list" id="prereqSelEq" data-type="equivalent">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="prereq-add-modal" id="prereqAddCourseModal" hidden aria-hidden="true">
        <div class="prereq-add-modal-panel" role="dialog" aria-modal="true" aria-labelledby="prereqAddCourseTitle">
            <div class="prereq-add-modal-head">
                <div>
                    <h3 id="prereqAddCourseTitle">Add Course to Curriculum</h3>
                    <p>Choose where the course will be reflected in the curriculum structure.</p>
                </div>
                <button type="button" class="prereq-add-modal-close" id="prereqAddCourseCloseBtn" aria-label="Close">&times;</button>
            </div>

            <form method="POST" action="{{ route('registrar.registrar-menu.academic-master.curriculum-file.setup') }}" id="prereqAddCourseForm">
                @csrf
                <input type="hidden" name="return_to_pre_requisites" value="1">

                <div class="prereq-add-grid">
                    <div class="prereq-add-field">
                        <label for="prereqAddProgram">Program</label>
                        <select id="prereqAddProgram" name="setup_course_id" class="prereq-add-input prereq-select2" data-placeholder="Search program">
                            @forelse($courses as $course)
                                <option value="{{ $course->id }}" {{ (string) old('setup_course_id', $selectedCourseId ?: '') === (string) $course->id ? 'selected' : '' }}>
                                    {{ $course->name ?: $course->description }}
                                </option>
                            @empty
                                <option value="">No Program Available</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="prereq-add-field">
                        <label for="prereqAddCurriculumYear">Curriculum Year</label>
                        <input id="prereqAddCurriculumYear" name="setup_curriculum_year" type="text" class="prereq-add-input" value="{{ old('setup_curriculum_year', $selectedCurriculumYear) }}" placeholder="Example: 2026-2027">
                    </div>

                    <div class="prereq-add-field">
                        <label for="prereqAddDateFrom">Date From</label>
                        <input id="prereqAddDateFrom" name="setup_date_from" type="date" class="prereq-add-input" value="{{ old('setup_date_from', $selectedDateFrom) }}">
                    </div>

                    <div class="prereq-add-field">
                        <label for="prereqAddDateTo">Date To</label>
                        <input id="prereqAddDateTo" name="setup_date_to" type="date" class="prereq-add-input" value="{{ old('setup_date_to', $selectedDateTo) }}">
                    </div>

                    <div class="prereq-add-field">
                        <label for="prereqAddYearLevel">Year Level to Reflect</label>
                        <select id="prereqAddYearLevel" name="setup_year_block_id" class="prereq-add-input">
                            <option value="">Select Year Level</option>
                            @foreach($yearBlocks as $yearBlock)
                                <option value="{{ $yearBlock->id }}" {{ (string) old('setup_year_block_id') === (string) $yearBlock->id ? 'selected' : '' }}>
                                    {{ $yearBlock->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="prereq-add-field">
                        <label for="prereqAddTerm">Term / Semester</label>
                        <select id="prereqAddTerm" name="setup_term_id" class="prereq-add-input">
                            <option value="">Select Term</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ (string) old('setup_term_id') === (string) $semester->id ? 'selected' : '' }}>
                                    {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="prereq-add-course-tools">
                    <label for="prereqAddCourseSearch">Course Search</label>
                    <input id="prereqAddCourseSearch" type="text" class="prereq-add-input" placeholder="Search course code or title">
                </div>

                <div class="prereq-add-course-list" id="prereqAddCourseList">
                    @forelse($availableSubjects as $subject)
                        @php
                            $units = (float) ($subject->units ?: (($subject->lec ?: 0) + ($subject->lab ?: 0)));
                            $oldSubjectIds = collect(old('setup_subject_ids', []))->map(function ($id) { return (string) $id; })->all();
                        @endphp
                        <label class="prereq-add-course-option" data-course-text="{{ strtolower(($subject->code ?? '') . ' ' . ($subject->name ?? '')) }}">
                            <input type="checkbox" name="setup_subject_ids[]" value="{{ $subject->id }}" {{ in_array((string) $subject->id, $oldSubjectIds, true) ? 'checked' : '' }}>
                            <span class="prereq-add-course-code">{{ $subject->code }}</span>
                            <span class="prereq-add-course-title">{{ $subject->name }}</span>
                            <span class="prereq-add-course-meta">{{ number_format($units, 1) }} units &middot; {{ $subject->course_type ?: 'Major' }}</span>
                        </label>
                    @empty
                        <div class="prereq-add-empty">No Course File records yet. Add courses in Course File first.</div>
                    @endforelse
                </div>

                <div class="prereq-add-modal-actions">
                    <button type="button" class="prereq-secondary-btn" id="prereqAddCourseCancelBtn">Cancel</button>
                    <button type="submit" class="prereq-view-btn" id="prereqAddCourseSaveBtn">Save Course Placement</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/pre-requisites.js') }}"></script>
@endpush
@endsection
