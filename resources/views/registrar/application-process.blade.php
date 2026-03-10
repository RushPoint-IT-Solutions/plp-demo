@extends('layouts.registrar')

@section('title', 'PLP - Application Process')
@section('page-title', 'APPLICATION PROCESS')



{{-- ====== Applicant Sidebar (injected into layout's extra-sidebar slot) ====== --}}
@push('extra-sidebar')
@include('registrar.applicant.sidebar')
@endpush

@section('content')

{{-- ========== MAIN APPLICATION PROCESS VIEW ========== --}}
<div class="app-process-page" id="appProcessPage">

    {{-- Filter Bar --}}
    <div class="app-filter-bar">
        {{-- Row 1: Date range + Course --}}
        <div class="app-filter-row">
            <div class="app-filter-group">
                <span class="app-filter-label">From Date</span>
                <select class="app-filter-select">
                    <option>January 1, 2026</option>
                    <option>February 1, 2026</option>
                    <option>March 1, 2026</option>
                </select>
            </div>
            <div class="app-filter-group">
                <span class="app-filter-label">From To</span>
                <select class="app-filter-select">
                    <option>January 1, 2026</option>
                    <option>February 1, 2026</option>
                    <option>March 1, 2026</option>
                </select>
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">Course</span>
                <select class="app-filter-select app-filter-select-wide" style="width:100%;">
                    <option value="">Select Course</option>
                    <option>BSCS</option>
                    <option>BSIT</option>
                    <option>BSED</option>
                    <option>BSBA</option>
                </select>
            </div>
        </div>

        {{-- Row 2: Search + Sort By + Show Entries --}}
        <div class="app-filter-row">
            <div class="app-filter-group">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-input app-filter-input-search" placeholder="Search">
            </div>
            <div class="app-filter-group">
                <span class="app-filter-label">Sort By</span>
                <input type="text" class="app-filter-input app-filter-input-sort" placeholder="Applicant ID">
            </div>
            <div class="app-filter-group">
                <span class="app-filter-label">Show Entries</span>
                <select class="app-filter-select app-filter-select-sm">
                    <option>100</option>
                    <option>50</option>
                    <option>25</option>
                    <option>10</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="app-table-wrap table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Applicant ID</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Date Applied</th>
                    <th>Date Last Update</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr data-id="2223A8137" data-name="Mark Jay Bares">
                    <td>1</td>
                    <td>2223A8137</td>
                    <td>Mark Jay Bares</td>
                    <td>BSCS</td>
                    <td>June 07, 2025</td>
                    <td>June 07, 2025</td>
                    <td><span class="app-status-accepted"><span class="app-status-dot"></span>Accepted</span></td>
                </tr>
                <tr data-id="2223A8138" data-name="Andrea Jane Austero">
                    <td>2</td>
                    <td>2223A8138</td>
                    <td>Andrea Jane Austero</td>
                    <td>BSCS</td>
                    <td>June 07, 2025</td>
                    <td>June 07, 2025</td>
                    <td><span class="app-status-accepted"><span class="app-status-dot"></span>Accepted</span></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

{{-- ========== APPLICANT DETAIL VIEW ========== --}}
<div id="applicantDetailView">

    {{-- Applicant ID + Name --}}
    <div class="appl-detail-header">
        <div class="appl-detail-field">
            <label>Applicant ID</label>
            <input type="text" id="detailApplicantId" readonly>
        </div>
        <div class="appl-detail-field wide">
            <label>Applicant Name</label>
            <input type="text" id="detailApplicantName" readonly>
        </div>
    </div>

    {{-- Application Form --}}
    <div class="applicant-panel" id="panel-application-form">
        @include('registrar.applicant.application-form')
    </div>

    {{-- Documents Submitted --}}
    <div class="applicant-panel" id="panel-documents-submitted">
        @include('registrar.applicant.documents-submitted')
    </div>

    {{-- Schedule of Exam --}}
    <div class="applicant-panel" id="panel-schedule-exam">
        @include('registrar.applicant.schedule-exam')
    </div>

    {{-- Medical Clearance --}}
    <div class="applicant-panel" id="panel-medical-clearance">
        @include('registrar.applicant.medical-clearance')
    </div>

    {{-- Exam Result --}}
    <div class="applicant-panel" id="panel-exam-result">
        @include('registrar.applicant.exam-result')
    </div>

    {{-- Approval --}}
    <div class="applicant-panel" id="panel-approval">
        @include('registrar.applicant.approval')
    </div>

    {{-- Application Status --}}
    <div class="applicant-panel" id="panel-application-status">
        @include('registrar.applicant.application-status')
    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('js/application-process.js') }}?v={{ time() }}"></script>
@endpush
