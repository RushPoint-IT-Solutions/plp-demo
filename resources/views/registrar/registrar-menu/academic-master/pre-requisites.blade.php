@extends('layouts.registrar')

@section('title', 'PLP - Pre-requisites')
@section('page-title', 'PRE-REQUISITES')

@section('content')
<div class="prereq-page">

    {{-- ═══════════ VIEW 1: Filter Bar (always visible) ═══════════ --}}
    <div class="prereq-filter-bar">
        <div class="prereq-filter-left">
            <div class="prereq-filter-group">
                <span class="app-filter-label">Course</span>
                <select class="app-filter-select" id="prereqCourse" style="width:100%;">
                    <option value="BSIT">Bachelor of Science in Information Technology</option>
                    <option value="BSCS">Bachelor of Science in Computer Science</option>
                    <option value="BSED">Bachelor of Secondary Education</option>
                    <option value="BSBA">Bachelor of Science in Business Administration</option>
                </select>
            </div>
            <div class="prereq-filter-group prereq-filter-group-sm">
                <span class="app-filter-label">Curriculum Year</span>
                <select class="app-filter-select" id="prereqYear" style="width:100%;">
                    <option value="1920">1920</option>
                    <option value="2021">2021</option>
                    <option value="2122">2122</option>
                    <option value="2223">2223</option>
                </select>
            </div>
        </div>
        <div class="prereq-filter-right">
            <button type="button" class="prereq-view-btn" id="btnViewList" onclick="loadPrereqList()">View List</button>
        </div>
    </div>

    {{-- ═══════════ VIEW 2: Subject List (hidden until View List clicked) ═══════════ --}}
    <div class="prereq-list-view" id="prereqListView" style="display:none;">
        <div class="prereq-list-header">
            <h2 class="prereq-program-title" id="prereqProgramTitle"></h2>
            <button type="button" class="prereq-download-btn" onclick="downloadPrereqPDF()">Download PDF</button>
        </div>

        <div id="prereqListContent">
            {{-- Dynamic year/semester tables injected here --}}
        </div>
    </div>

    {{-- ═══════════ VIEW 3: Subject Detail / Edit (hidden until row clicked) ═══════════ --}}
    <div class="prereq-detail-view" id="prereqDetailView" style="display:none;">
        <div class="prereq-detail-banner">
            <div class="prereq-detail-info">
                <span class="prereq-detail-code" id="prereqDetailCode"></span>
                <span class="prereq-detail-name" id="prereqDetailName"></span>
            </div>
            <button type="button" class="prereq-save-btn" onclick="savePrereqDetail()">Save</button>
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
                        <input type="text" class="prereq-search-input" id="prereqSearchPre" placeholder="Search..." oninput="filterPrereqAvailable('pre')">
                        <button type="button" class="prereq-search-btn" onclick="filterPrereqAvailable('pre')">Search</button>
                    </div>
                    <div class="prereq-available-list" id="prereqAvailPre">
                        {{-- Dynamic available subjects --}}
                    </div>
                </div>
                <div class="prereq-col">
                    <div class="prereq-selected-label">-list of pre-requisite subject(s)-</div>
                    <div class="prereq-selected-list" id="prereqSelPre">
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
                        <input type="text" class="prereq-search-input" id="prereqSearchCo" placeholder="Search..." oninput="filterPrereqAvailable('co')">
                        <button type="button" class="prereq-search-btn" onclick="filterPrereqAvailable('co')">Search</button>
                    </div>
                    <div class="prereq-available-list" id="prereqAvailCo">
                    </div>
                </div>
                <div class="prereq-col">
                    <div class="prereq-selected-label">-list of co-requisite subject(s)-</div>
                    <div class="prereq-selected-list" id="prereqSelCo">
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
                        <input type="text" class="prereq-search-input" id="prereqSearchEq" placeholder="Search..." oninput="filterPrereqAvailable('eq')">
                        <button type="button" class="prereq-search-btn" onclick="filterPrereqAvailable('eq')">Search</button>
                    </div>
                    <div class="prereq-available-list" id="prereqAvailEq">
                    </div>
                </div>
                <div class="prereq-col">
                    <div class="prereq-selected-label">-list of equivalent subject(s)-</div>
                    <div class="prereq-selected-list" id="prereqSelEq">
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
