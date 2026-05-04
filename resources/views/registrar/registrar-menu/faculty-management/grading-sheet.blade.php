@extends('layouts.registrar')

@section('title', 'PLP - Grading Sheet')
@section('page-title', 'GRADING SHEET')
@section('body-class', 'page-registrar-grading-sheet')

@push('styles')
    <style>
        .gs-badge {
            display: inline-block;
            padding: 0.2rem 0.75rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .gs-badge-submitted { background: #fef9c3; color: #854d0e; }

        .gs-action-btns { display: flex; gap: 0.4rem; }

        .gs-btn-approve, .gs-btn-reject {
            padding: 0.28rem 0.75rem;
            border: none;
            border-radius: 5px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
            white-space: nowrap;
        }
        .gs-btn-approve { background: #15803d; color: #fff; }
        .gs-btn-reject  { background: #dc2626; color: #fff; }
        .gs-btn-approve:hover, .gs-btn-reject:hover { opacity: 0.85; }
        .gs-badge-approved { background: #dcfce7; color: #15803d; }
        .gs-badge-rejected { background: #fee2e2; color: #dc2626; }
        .gs-btn-approve:disabled, .gs-btn-reject:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
<div class="pf-page">

    {{-- Filter Bar --}}
    <div class="gs-filter-bar">
        <div class="gs-filter-row">
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">DEPARTMENT</span>
                <select class="gs-filter-select" id="gsDept">
                    <option value="College">College</option>
                </select>
            </div>
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
                    <option value="Second" selected>Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">STATUS</span>
                <select class="gs-filter-select" id="gsStatus">
                    <option value="Status">Status</option>
                    <option value="Approved">Approved</option>
                    <option value="Pending">Pending</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
        </div>
        <div class="gs-filter-row">
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">FACULTY</span>
                <select class="gs-filter-select" id="gsFaculty">
                    <option value="">faculty</option>
                    @if(isset($faculties))
                        @foreach($faculties as $f)
                        <option value="{{ $f }}">{{ $f }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">SECTION</span>
                <select class="gs-filter-select" id="gsSection">
                    <option value="">Section</option>
                    <option value="all">* Leave this blank to view all sections</option>
                </select>
            </div>
            <div class="gs-filter-group gs-filter-even">
                <span class="gs-filter-label">PROGRAM</span>
                <select class="gs-filter-select" id="gsProgram">
                    <option value="">* Leave this blank to view all program</option>
                </select>
            </div>
            <div class="gs-filter-group" style="flex:0 0 auto; align-self:flex-end;">
                <button type="button" class="gs-view-btn" onclick="handleViewList()">Search</button>
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
                        <th rowspan="2">Status</th>
                        <th rowspan="2">Action</th>
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
        {{-- Section Info Banner --}}
        <div class="gs-section-banner-v2">
            <div class="gs-banner-grid">
                <div class="gs-banner-pair">
                    <span class="gs-banner-key">SECTION :</span>
                    <span class="gs-banner-val" id="gsBannerSection"></span>
                </div>
                <div class="gs-banner-pair">
                    <span class="gs-banner-key">COURSE :</span>
                    <span class="gs-banner-val" id="gsBannerCourse"></span>
                </div>
                <div class="gs-banner-pair">
                    <span class="gs-banner-key">PROFESSOR :</span>
                    <span class="gs-banner-val" id="gsBannerProf"></span>
                </div>
                <div class="gs-banner-pair">
                    <span class="gs-banner-key">SCHEDULE :</span>
                    <span class="gs-banner-val" id="gsBannerSched"></span>
                </div>
            </div>
        </div>

        {{-- Detail Toolbar --}}
        <div class="gs-detail-toolbar">
            <div class="gs-detail-toolbar-left">
                <button type="button" class="gs-back-btn" onclick="showListView()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    Review Grading Sheet
                </button>
            </div>
            <div class="gs-detail-toolbar-right">
                <button type="button" class="gs-print-btn" onclick="window.print()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Grading Sheet
                </button>
                <button type="button" class="gs-print-btn gs-print-secondary" onclick="window.print()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Grading Sheet OLE
                </button>
            </div>
        </div>

        {{-- Students Table --}}
        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table gs-detail-table" id="gsDetailTable">
                <thead>
                    <tr>
                        <th style="width:35px;">#</th>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th style="width:38px;">FDA</th>
                        <th style="width:38px;">NA</th>
                        <th id="gsMidtermHeader" class="gs-grade-header-cell" title="Click to open Midterm grading modal">
                            <span class="gs-grade-head-title">MIDTERM</span>
                            <span class="gs-grade-head-hint">Click to open</span>
                        </th>
                        <th id="gsFinalHeader" class="gs-grade-header-cell" title="Click to open Final grading modal">
                            <span class="gs-grade-head-title">FINALS</span>
                            <span class="gs-grade-head-hint">Click to open</span>
                        </th>
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

    {{-- ═══ Grade Entry Modal ═══ --}}
    <div class="req-modal-overlay gs-grade-modal-overlay" id="gsGradeModal" style="display:none;" aria-hidden="true">
        <div class="req-modal-box gs-grade-modal-box" role="dialog" aria-modal="true" aria-labelledby="gsGradeModalTitle">
            <div class="gs-grade-modal-head">
                <h3 class="req-modal-title gs-grade-modal-title" id="gsGradeModalTitle">Enter Grade</h3>
                <button type="button" class="rep-modal-close-x" id="gsGradeModalClose" aria-label="Close">&times;</button>
            </div>
            <div class="gs-grade-modal-subtitle" id="gsGradeModalSubtitle"></div>

            <div class="gs-grade-tabs" id="gsGradeTabs"></div>

            <div class="gs-grade-table-wrap">
                <table class="student-table registrar-table gs-grade-modal-table" id="gsGradeModalTable">
                    <thead>
                        <tr>
                            <th style="width:34px;">#</th>
                            <th style="width:120px;">Student No</th>
                            <th>Name</th>
                            <th style="width:70px;" id="gsGradeColQ1">Q1</th>
                            <th style="width:70px;" id="gsGradeColQ2">Q2</th>
                            <th style="width:120px;" id="gsGradeColPe">Percentage Equivalent</th>
                        </tr>
                    </thead>
                    <tbody id="gsGradeModalBody"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    @if(isset($gradingSections) && is_array($gradingSections))
        window.GS_SERVER_SECTIONS = @json($gradingSections);
    @else
        window.GS_SERVER_SECTIONS = null;
    @endif

    window.GS_ACTION_URL = "{{ route('registrar.registrar-menu.faculty-mgmt.grading-sheet.action') }}";
</script>
<script src="{{ asset('js/grading-sheet.js') }}?v={{ file_exists(public_path('js/grading-sheet.js')) ? filemtime(public_path('js/grading-sheet.js')) : time() }}"></script>
@endpush
