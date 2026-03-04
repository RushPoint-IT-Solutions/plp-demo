@extends('layouts.registrar')

@section('title', 'PLP - Application Process')
@section('page-title', 'APPLICATION PROCESS')

@section('content')
<div class="app-process-page">

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
    <div class="app-table-wrap">
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
                <tr>
                    <td>1</td>
                    <td>2223A8137</td>
                    <td>Mark Jay Bares</td>
                    <td>BSCS</td>
                    <td>June 07, 2025</td>
                    <td>June 07, 2025</td>
                    <td><span class="app-status-accepted"><span class="app-status-dot"></span>Accepted</span></td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>2223A8137</td>
                    <td>Mark Jay Bares</td>
                    <td>BSCS</td>
                    <td>June 07, 2025</td>
                    <td>June 07, 2025</td>
                    <td><span class="app-status-accepted"><span class="app-status-dot"></span>Accepted</span></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
@endsection
