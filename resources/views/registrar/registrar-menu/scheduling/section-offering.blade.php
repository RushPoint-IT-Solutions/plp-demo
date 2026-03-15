@extends('layouts.registrar')

@section('title', 'PLP - Section Offering')
@section('page-title', 'SECTION OFFERING')
@section('body-class', 'page-section-offering')

@section('content')
<div class="pf-page">

    {{-- Filter Bar --}}
    <div class="sched-filter-bar">
        <div class="sched-filter-row sched-filter-row-main so-filter-row">
            <div class="sched-filter-group so-filter-sy">
                <span class="app-filter-label">School Year</span>
                <select class="app-filter-select" id="soSY" style="width:100%;">
                    <option value="2025-2026">2025-2026</option>
                    <option value="2024-2025">2024-2025</option>
                    <option value="2023-2024">2023-2024</option>
                </select>
            </div>
            <div class="sched-filter-group so-filter-term">
                <span class="app-filter-label">Semester</span>
                <select class="app-filter-select" id="soTerm" style="width:100%;">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="sched-filter-group so-filter-year">
                <span class="app-filter-label">Year Level</span>
                <select class="app-filter-select" id="soYearLevel" style="width:100%;">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                    <option value="Fourth">Fourth</option>
                </select>
            </div>
            <div class="sched-filter-group so-filter-section">
                <span class="app-filter-label">Section</span>
                <select class="app-filter-select" id="soSection" style="width:100%;">
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
            <div class="sched-filter-group sched-filter-group-lg so-filter-program">
                <span class="app-filter-label">Course</span>
                <select class="app-filter-select" id="soProgram" style="width:100%;">
                    <option value="BSIT">BSIT</option>
                    <option value="BSCS">BSCS</option>
                    <option value="BSED">BSED</option>
                    <option value="BSAT">BSAT</option>
                    <option value="BSN">BSN</option>
                    <option value="BSET">BSET</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Section Offering Card --}}
    <div class="so-card" id="soCard" style="display:none;">
        <div class="so-card-header">
            <div class="so-card-title" id="soCardTitle">Section Offering: A</div>
            <button type="button" class="pf-btn-new so-print-btn">Print Class Program</button>
        </div>

        <div class="student-table-wrapper table-responsive" id="soTableWrap">
            <table class="student-table registrar-table" id="soTable">
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
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/section-offering.js') }}"></script>
@endpush
