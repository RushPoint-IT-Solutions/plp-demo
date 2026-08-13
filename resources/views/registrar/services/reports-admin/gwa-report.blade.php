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

    .gwa-head-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
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

    .gwa-alert {
        margin-bottom: 12px;
        padding: 11px 14px;
        border: 1px solid #bfdfcc;
        border-radius: 8px;
        background: #e8f6ee;
        color: #17633a;
        font-size: 0.86rem;
        font-weight: 800;
    }

    @media (max-width: 980px) {
        .gwa-filter,
        .gwa-summary {
            grid-template-columns: 1fr;
        }

        .gwa-head {
            flex-direction: column;
        }
    }

    .gwa-print {
        display: none;
    }

    @media print {
        @page {
            size: legal landscape;
            margin: 0.4in;
        }

        body * {
            visibility: hidden !important;
        }

        .gwa-print,
        .gwa-print * {
            visibility: visible !important;
        }

        .gwa-print {
            display: block !important;
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            color: #111;
        }

        .gwa-print-head {
            display: grid;
            grid-template-columns: 70px 1fr;
            align-items: center;
            gap: 12px;
            width: 9in;
            margin: 0 auto 10px;
        }

        .gwa-print-logo {
            width: 62px;
            height: 62px;
            object-fit: contain;
            justify-self: center;
        }

        .gwa-print-school {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 0.03em;
        }

        .gwa-print-address,
        .gwa-print-phone {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            margin-top: 2px;
            color: #333;
        }

        .gwa-print-title {
            text-align: center;
            font-weight: 800;
            font-size: 14px;
            letter-spacing: 0.04em;
            margin: 6px 0 2px;
        }

        .gwa-print-subtitle {
            text-align: center;
            font-size: 11px;
            color: #333;
            margin-bottom: 10px;
        }

        .gwa-print-table {
            width: 9in;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 9px;
        }

        .gwa-print-table th,
        .gwa-print-table td {
            border: 1px solid #333;
            padding: 3px 5px;
            text-align: left;
        }

        .gwa-print-table th {
            background: #f0f0f0;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
        }

        .gwa-print-table td.num {
            text-align: center;
        }

        .gwa-print-footer {
            width: 9in;
            margin: 10px auto 0;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #333;
        }
    }
</style>
@endpush

@section('content')
<div class="gwa-page">
    <div class="gwa-head">
        <div>
            <h1 class="gwa-title">General Weighted Average Report</h1>
            <p class="gwa-lead">Every student's overall GWA (grade equivalent, weighted by subject units) across all their semesters{{ ($schoolYear || $semester) ? ', scoped to the filter below' : '' }}.</p>
        </div>
        <div class="gwa-head-actions">
            <button type="button" class="gwa-btn soft" onclick="window.print()">Print</button>
        </div>
    </div>

    @if(session('success'))
        <div class="gwa-alert">{{ session('success') }}</div>
    @endif

    <div class="gwa-summary">
        <div class="gwa-card gwa-summary-item">
            <span class="gwa-summary-label">Students</span>
            <span class="gwa-summary-value">{{ number_format((int) ($summary['students'] ?? 0)) }}</span>
        </div>
        <div class="gwa-card gwa-summary-item">
            <span class="gwa-summary-label">Graded Subjects</span>
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
                            <td>{{ number_format((int) $row['subjects_count']) }}</td>
                            <td>{{ number_format((float) $row['total_units'], 1) }}</td>
                            <td class="gwa-grade">{{ $row['gwa'] !== null ? number_format((float) $row['gwa'], 2) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<section class="gwa-print" aria-label="GWA Report Print">
    <header class="gwa-print-head">
        <img class="gwa-print-logo" src="{{ asset('img/logobg.png') }}" alt="PLP Logo">
        <div>
            <div class="gwa-print-school">PAMANTASAN NG LUNGSOD NG PASIG</div>
            <div class="gwa-print-address">Alkalde Jose St. Kapasigan, Pasig City, Philippines 1600</div>
            <div class="gwa-print-phone">8628-1014</div>
        </div>
    </header>

    <div class="gwa-print-title">GENERAL WEIGHTED AVERAGE REPORT</div>
    @php
        $gwaPrintScope = collect([
            $schoolYear ? 'A.Y. ' . $schoolYear : null,
            $semester ?: null,
            $program ?: null,
        ])->filter()->implode(' &middot; ');
    @endphp
    <div class="gwa-print-subtitle">{!! $gwaPrintScope !== '' ? $gwaPrintScope : 'All Programs &middot; All School Years &middot; All Semesters' !!}</div>

    <table class="gwa-print-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Student No.</th>
                <th>Student Name</th>
                <th>Program</th>
                <th>Year Level</th>
                <th>Subjects</th>
                <th>Total Units</th>
                <th>GWA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td class="num">{{ $index + 1 }}</td>
                    <td>{{ $row['student_no'] ?: '-' }}</td>
                    <td>{{ $row['student_name'] ?: '-' }}</td>
                    <td>{{ $row['program'] ?: '-' }}</td>
                    <td>{{ $row['year_level'] ?: '-' }}</td>
                    <td class="num">{{ number_format((int) $row['subjects_count']) }}</td>
                    <td class="num">{{ number_format((float) $row['total_units'], 1) }}</td>
                    <td class="num">{{ $row['gwa'] !== null ? number_format((float) $row['gwa'], 2) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="num">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="gwa-print-footer">
        <div>Generated on {{ now()->format('F j, Y g:i A') }}</div>
        <div>Total: {{ number_format((int) ($summary['students'] ?? 0)) }} student(s) &middot; Average GWA: {{ $summary['average_gwa'] !== null ? number_format((float) $summary['average_gwa'], 2) : '-' }}</div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/reports-student-autocomplete.js') }}?v={{ file_exists(public_path('js/reports-student-autocomplete.js')) ? filemtime(public_path('js/reports-student-autocomplete.js')) : time() }}"></script>
@endpush
