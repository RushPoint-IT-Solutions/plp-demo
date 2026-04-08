@extends('layouts.registrar')

@section('title', 'PLP - Grading Sheet')
@section('page-title', 'GRADING SHEET')

@section('content')
<div class="pf-page">

    {{-- Filter Bar --}}
    <div class="gs-filter-bar">
        <div class="gs-filter-row">
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">ACADEMIC YEAR</span>
                <select class="gs-filter-select" id="gsAY">
                    <option value="2025-2026">2025-2026</option>
                    <option value="2024-2025">2024-2025</option>
                    <option value="2023-2024">2023-2024</option>
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">TERM</span>
                <select class="gs-filter-select" id="gsTerm">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">STATUS</span>
                <select class="gs-filter-select" id="gsStatus">
                    <option value="Approved">Approved</option>
                    <option value="Pending">Pending</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">FACULTY</span>
                <select class="gs-filter-select" id="gsFaculty">
                    <option value="">-Select Faculty-</option>
                    <option value="Diaz, Jonnel Mark">Diaz, Jonnel Mark</option>
                    <option value="Santos, Maria">Santos, Maria</option>
                    <option value="Reyes, Carlo">Reyes, Carlo</option>
                </select>
            </div>
        </div>
        <div class="gs-filter-row">
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">SECTION</span>
                <select class="gs-filter-select" id="gsSection">
                    <option value="">Section</option>
                    <option value="all">All</option>
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">PROGRAM</span>
                <select class="gs-filter-select" id="gsProgram">
                    <option value="">Section</option>
                    <option value="BSIT">BSIT</option>
                    <option value="BSCS">BSCS</option>
                    <option value="BSED">BSED</option>
                    <option value="BSAT">BSAT</option>
                    <option value="BSN">BSN</option>
                </select>
            </div>
            <div class="gs-filter-group" style="flex:0 0 auto; align-self:flex-end;">
                <button type="button" class="gs-view-btn" onclick="handleViewList()">View List</button>
            </div>
        </div>
    </div>

    {{-- ═══ VIEW 1: Section List Table ═══ --}}
    <div id="gsListView">
        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table gs-list-table" id="gsListTable">
                <thead>
                    <tr class="gs-thead-top">
                        <th rowspan="2" style="width:40px;">#</th>
                        <th rowspan="2">Section</th>
                        <th rowspan="2">Course Code</th>
                        <th rowspan="2">Description</th>
                        <th rowspan="2">Faculty</th>
                        <th colspan="2" class="gs-date-group-header">Date Posted</th>
                        <th rowspan="2">Approved By</th>
                    </tr>
                    <tr class="gs-thead-sub">
                        <th>Midterm</th>
                        <th>Final</th>
                    </tr>
                </thead>
                <tbody id="gsListBody">
                    {{-- JS-rendered --}}
                </tbody>
            </table>
        </div>
        <div class="pf-pagination">
            <span class="pf-page-info" id="gsListPageInfo">Showing 0 sections</span>
        </div>
    </div>

    {{-- ═══ VIEW 2: Student Grades Detail ═══ --}}
    <div id="gsDetailView" style="display:none;">
        {{-- Back link --}}
        <div style="margin-bottom:12px;">
            <button type="button" class="gs-back-btn" onclick="showListView()">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Back to List
            </button>
        </div>

        {{-- Section Info Banner --}}
        <div class="gs-section-banner-v2">
            <div class="gs-banner-grid">
                <div class="gs-banner-pair">
                    <span class="gs-banner-key">Section:</span>
                    <span class="gs-banner-val" id="gsBannerSection"></span>
                </div>
                <div class="gs-banner-pair">
                    <span class="gs-banner-key">Course:</span>
                    <span class="gs-banner-val" id="gsBannerCourse"></span>
                </div>
                <div class="gs-banner-pair">
                    <span class="gs-banner-key">Professor:</span>
                    <span class="gs-banner-val" id="gsBannerProf"></span>
                </div>
                <div class="gs-banner-pair">
                    <span class="gs-banner-key">Schedule:</span>
                    <span class="gs-banner-val" id="gsBannerSched"></span>
                </div>
            </div>
        </div>

        {{-- Students Table --}}
        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table" id="gsDetailTable">
                <thead>
                    <tr>
                        <th style="width:35px;">#</th>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th style="width:38px;">FDA</th>
                        <th style="width:38px;">NA</th>
                        <th>MIDTERM</th>
                        <th>FINAL</th>
                        <th>C Rating</th>
                        <th>F Rating</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="gsDetailBody">
                    {{-- JS-rendered --}}
                </tbody>
            </table>
        </div>
        <div class="pf-pagination">
            <span class="pf-page-info" id="gsDetailPageInfo">Showing 0 students</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/grading-sheet.js') }}?v={{ file_exists(public_path('js/grading-sheet.js')) ? filemtime(public_path('js/grading-sheet.js')) : time() }}"></script>
@endpush
