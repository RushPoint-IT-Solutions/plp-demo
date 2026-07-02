@extends('layouts.registrar')

@section('title', 'PLP - GWA Report')
@section('page-title', 'GWA REPORT')

@push('styles')
<style>
    .gwa-page {
        padding: 18px;
    }

    .gwa-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .gwa-title {
        margin: 0;
        color: #143521;
        font-size: 1.35rem;
        font-weight: 800;
    }

    .gwa-lead {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 0.86rem;
        font-weight: 600;
    }

    .gwa-card {
        border: 1px solid #dfe7e2;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(20, 53, 33, 0.05);
    }

    .gwa-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }

    .gwa-summary-item {
        padding: 13px 14px;
    }

    .gwa-summary-label {
        display: block;
        color: #6b7280;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .gwa-summary-value {
        display: block;
        margin-top: 3px;
        color: #143521;
        font-size: 1.28rem;
        font-weight: 900;
    }

    .gwa-filter {
        display: grid;
        grid-template-columns: 1.4fr repeat(3, minmax(130px, 0.7fr)) auto auto;
        gap: 10px;
        align-items: end;
        padding: 14px;
        margin-bottom: 12px;
    }

    .gwa-field label {
        display: block;
        margin-bottom: 4px;
        color: #374151;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .gwa-input {
        width: 100%;
        height: 38px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0 10px;
        color: #111827;
        font-size: 0.86rem;
        font-weight: 600;
        background: #fff;
    }

    .gwa-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 38px;
        padding: 0 16px;
        border: 1px solid #15803d;
        border-radius: 6px;
        background: #15803d;
        color: #fff;
        font-size: 0.84rem;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .gwa-btn.soft {
        border-color: #d1d5db;
        background: #fff;
        color: #374151;
    }

    .gwa-table-wrap {
        overflow-x: auto;
        padding: 0 14px 14px;
    }

    .gwa-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    .gwa-table th {
        background: #f3f6f4;
        color: #143521;
        font-size: 0.76rem;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
        border-bottom: 1px solid #d1d5db;
        padding: 10px;
    }

    .gwa-table td {
        color: #1f2937;
        font-size: 0.84rem;
        font-weight: 600;
        border-bottom: 1px solid #edf1ee;
        padding: 10px;
        vertical-align: middle;
    }

    .gwa-grade {
        color: #14532d;
        font-size: 0.96rem;
        font-weight: 900;
    }

    .gwa-profile-link {
        color: #15803d;
        font-weight: 900;
        text-decoration: none;
    }

    .gwa-profile-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 980px) {
        .gwa-filter,
        .gwa-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="gwa-page">
    <div class="gwa-head">
        <div>
            <h1 class="gwa-title">General Weighted Average Report</h1>
            <p class="gwa-lead">Computes every student GWA per school year and semester using final grade weighted by subject units.</p>
        </div>
        <button type="button" class="gwa-btn soft" onclick="window.print()">Print</button>
    </div>

    <div class="gwa-summary">
        <div class="gwa-card gwa-summary-item">
            <span class="gwa-summary-label">Students</span>
            <span class="gwa-summary-value">{{ number_format((int) ($summary['students'] ?? 0)) }}</span>
        </div>
        <div class="gwa-card gwa-summary-item">
            <span class="gwa-summary-label">Semester Records</span>
            <span class="gwa-summary-value">{{ number_format((int) ($summary['records'] ?? 0)) }}</span>
        </div>
        <div class="gwa-card gwa-summary-item">
            <span class="gwa-summary-label">Average GWA</span>
            <span class="gwa-summary-value">{{ $summary['average_gwa'] !== null ? number_format((float) $summary['average_gwa'], 2) : '-' }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('registrar.services.reports-admin.gwa-report') }}" class="gwa-card gwa-filter">
        <div class="gwa-field">
            <label for="gwaSearch">Search Student</label>
            <div class="rsa-wrap">
                <input id="gwaSearch" class="gwa-input" type="text" name="q" value="{{ $search }}" placeholder="Type student no. or name"
                    data-student-autocomplete="reports"
                    data-student-fill-key="student_no"
                    data-student-search-url="{{ route('registrar.services.reports-admin.students.search') }}">
            </div>
        </div>
        <div class="gwa-field">
            <label for="gwaSchoolYear">School Year</label>
            <select id="gwaSchoolYear" class="gwa-input" name="school_year">
                <option value="">All</option>
                @foreach($schoolYears as $year)
                    <option value="{{ $year }}" {{ $schoolYear === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="gwa-field">
            <label for="gwaSemester">Semester</label>
            <select id="gwaSemester" class="gwa-input" name="semester">
                <option value="">All</option>
                @foreach($semesters as $sem)
                    <option value="{{ $sem }}" {{ $semester === (string) $sem ? 'selected' : '' }}>{{ $sem }}</option>
                @endforeach
            </select>
        </div>
        <div class="gwa-field">
            <label for="gwaProgram">Program</label>
            <select id="gwaProgram" class="gwa-input" name="program">
                <option value="">All</option>
                @foreach($programs as $item)
                    <option value="{{ $item }}" {{ $program === (string) $item ? 'selected' : '' }}>{{ $item }}</option>
                @endforeach
            </select>
        </div>
        <button class="gwa-btn" type="submit">Filter</button>
        <a class="gwa-btn soft" href="{{ route('registrar.services.reports-admin.gwa-report') }}">Clear</a>
    </form>

    <div class="gwa-card">
        <div class="gwa-table-wrap">
            <table class="gwa-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student No.</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Year Level</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Subjects</th>
                        <th>Total Units</th>
                        <th>GWA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($row['student_id'])
                                    <a class="gwa-profile-link" href="{{ route('registrar.registrar-menu.student-mgmt.student-records.profile', $row['student_id']) }}" target="_blank">
                                        {{ $row['student_no'] ?: '-' }}
                                    </a>
                                @else
                                    {{ $row['student_no'] ?: '-' }}
                                @endif
                            </td>
                            <td>{{ $row['student_name'] ?: '-' }}</td>
                            <td>{{ $row['program'] ?: '-' }}</td>
                            <td>{{ $row['year_level'] ?: '-' }}</td>
                            <td>{{ $row['school_year'] ?: '-' }}</td>
                            <td>{{ $row['semester'] ?: '-' }}</td>
                            <td>{{ number_format((int) $row['subjects_count']) }}</td>
                            <td>{{ number_format((float) $row['total_units'], 1) }}</td>
                            <td class="gwa-grade">{{ $row['gwa'] !== null ? number_format((float) $row['gwa'], 2) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">No posted final grade records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/reports-student-autocomplete.js') }}?v={{ file_exists(public_path('js/reports-student-autocomplete.js')) ? filemtime(public_path('js/reports-student-autocomplete.js')) : time() }}"></script>
@endpush
