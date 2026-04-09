@extends('layouts.registrar')

@section('title', 'PLP - Pre-requisites')
@section('page-title', 'PRE-REQUISITES')

@section('content')
<div
    class="prereq-page"
    id="prereqPage"
    data-list-url="{{ route('registrar.registrar-menu.academic-master.pre-requisites.data') }}"
    data-detail-url-template="{{ route('registrar.registrar-menu.academic-master.pre-requisites.subject.show', ['courseCurriculumSubjectId' => '__CURRICULUM_SUBJECT_ID__']) }}"
    data-save-url-template="{{ route('registrar.registrar-menu.academic-master.pre-requisites.subject.update', ['courseCurriculumSubjectId' => '__CURRICULUM_SUBJECT_ID__']) }}"
    data-csrf-token="{{ csrf_token() }}"
    data-course-years='@json($courseYearMap)'
    data-selected-course-id="{{ $selectedCourseId ?: '' }}"
    data-selected-curriculum-year="{{ $selectedCurriculumYear }}"
>

    {{-- ═══════════ VIEW 1: Filter Bar (always visible) ═══════════ --}}
    <div class="prereq-filter-bar">
        <div class="prereq-filter-left">
            <div class="prereq-filter-group">
                <span class="app-filter-label">Course</span>
                <select class="app-filter-select" id="prereqCourse">
                    @forelse($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) $selectedCourseId === (string) $course->id ? 'selected' : '' }}>
                            {{ $course->name ?: $course->description }}
                        </option>
                    @empty
                        <option value="">No courses available</option>
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
            <h2 class="prereq-program-title" id="prereqProgramTitle"></h2>
            <button type="button" class="prereq-download-btn" id="prereqDownloadBtn">Download PDF</button>
        </div>

        <div id="prereqListContent">
            {{-- Dynamic year/semester tables injected here --}}
        </div>
    </div>

    {{-- ═══════════ VIEW 3: Subject Detail / Edit (hidden until row clicked) ═══════════ --}}
    <div class="prereq-detail-view" id="prereqDetailView" hidden>
        <div class="prereq-detail-banner">
            <div class="prereq-detail-info">
                <span class="prereq-detail-code" id="prereqDetailCode"></span>
                <span class="prereq-detail-name" id="prereqDetailName"></span>
            </div>
            <button type="button" class="prereq-download-btn" id="prereqBackBtn">Back to List</button>
            <button type="button" class="prereq-save-btn" id="prereqSaveBtn">Save</button>
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
                    <div class="prereq-selected-label">-list of pre-requisite subject(s)-</div>
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
                    <div class="prereq-selected-label">-list of co-requisite subject(s)-</div>
                    <div class="prereq-selected-list" id="prereqSelCo" data-type="co">
                    </div>
                </div>
            </div>
        </div>

        {{-- Equivalent Subject(s) --}}
        <div class="prereq-section">
            <div class="prereq-section-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3"/><path d="M16 3h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-3"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="16" x2="17" y2="16"/></svg>
                Equivalent Subject(s)
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
                    <div class="prereq-selected-label">-list of equivalent subject(s)-</div>
                    <div class="prereq-selected-list" id="prereqSelEq" data-type="equivalent">
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/pre-requisites.js') }}"></script>
@endpush
@endsection
