@extends('layouts.registrar')

@section('title', 'PLP - Slot Monitoring')
@section('page-title', 'SLOT MONITORING')
@section('body-class', 'page-slot-monitoring')

@section('content')
<div
    class="pf-page"
    id="slotMonitoringPage"
    data-fetch-url="{{ route('registrar.registrar-menu.scheduling.slot-monitoring.data') }}"
    data-report-actual-size-url="{{ route('registrar.registrar-menu.scheduling.slot-monitoring.report', ['reportType' => 'actual-size']) }}"
    data-report-under20-url="{{ route('registrar.registrar-menu.scheduling.slot-monitoring.report', ['reportType' => 'under-20']) }}"
    data-report-dissolved-url="{{ route('registrar.registrar-menu.scheduling.slot-monitoring.report', ['reportType' => 'dissolved']) }}"
    data-report-closed-url="{{ route('registrar.registrar-menu.scheduling.slot-monitoring.report', ['reportType' => 'closed']) }}"
>

    <div class="sched-filter-bar">
        <div class="sched-filter-row sm-filter-row">
            <div class="sched-filter-group">
                <span class="app-filter-label">School Year</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'smSY',
                    'name' => 'school_year',
                    'options' => $schoolYearOptions ?? collect([['value' => '', 'label' => '- All -']]),
                    'selected' => '',
                    'placeholder' => '- All -',
                ])
            </div>
            <div class="sched-filter-group">
                <span class="app-filter-label">Semester</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'smSemester',
                    'name' => 'semester',
                    'options' => $semesterOptions ?? collect([['value' => '', 'label' => '- All -']]),
                    'selected' => '',
                    'placeholder' => '- All -',
                ])
            </div>
            <div class="sched-filter-group">
                <span class="app-filter-label">Section</span>
                <input type="text" class="app-filter-select" id="smSection" placeholder="Type section">
            </div>
            <div class="sched-filter-group">
                <span class="app-filter-label">Course</span>
                <input type="text" class="app-filter-select" id="smCourse" placeholder="Type course code or name">
            </div>
            <div class="sched-filter-group sm-search-group">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-select sm-search-input" id="smSearch" placeholder="Subject or schedule">
            </div>
        </div>
    </div>

    <div class="sm-action-wrap d-flex flex-wrap gap-2">
        <button type="button" class="pf-btn-new sm-report-btn" id="smActualSizeBtn">ACTUAL SIZE</button>
        <button type="button" class="pf-btn-new sm-report-btn" id="smUnder20Btn">List of Subject(s) with 20 and below student(s) enrolled</button>
        <button type="button" class="pf-btn-new sm-report-btn" id="smDissolvedBtn">List of Dissolved Subjects</button>
        <button type="button" class="pf-btn-new sm-report-btn" id="smClosedBtn">List of Closed Subjects</button>
    </div>

    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" id="smTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>SY</th>
                    <th>Semester</th>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Schedule</th>
                    <th>Total Slots</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="smBody"></tbody>
        </table>
    </div>

    <div class="sf-pagination-bar sf-pagination-compact" id="smPaginationBar">
        <div class="rtp-pagination">
            <nav class="rtp-nav" aria-label="Slot Monitoring pagination">
                <div class="rtp-list" role="group" aria-label="Page controls">
                    <button type="button" class="rtp-page-btn" id="smPrevBtn" aria-label="Previous page">&lt;</button>
                    <div class="rtp-pages" id="smPageNumbers"></div>
                    <button type="button" class="rtp-page-btn" id="smNextBtn" aria-label="Next page">&gt;</button>
                </div>
            </nav>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script src="{{ asset('js/slot-monitoring.js') }}?v={{ filemtime(public_path('js/slot-monitoring.js')) }}"></script>
@endpush
