<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PLP - Application Process Print List</title>

    <link rel="icon" href="{{ asset('img/logobg.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
</head>
<body class="registrar-body app-process-print-page">
@php
    $filters = isset($filters) && is_array($filters) ? $filters : [
        'from_date' => null,
        'to_date' => null,
        'course_id' => 0,
        'search' => '',
        'sort_by' => 'date_updated',
        'sort_direction' => 'desc',
        'per_page' => 10,
    ];
@endphp

<div class="container-fluid py-4">
    <div class="app-process-print-toolbar no-print">
        <div>
            <h1 class="h4 mb-1">Application Process - Applicant List</h1>
            <p class="text-muted mb-0">Generated on {{ now()->format('M d, Y h:i A') }}</p>
        </div>
        <div class="d-flex align-items-center">
            <button id="printNowBtn" type="button" class="btn btn-sm btn-success mr-2">Print</button>
            <a href="{{ route('registrar.process.application', request()->except('autoprint')) }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="app-process-print-meta mb-3">
        <span class="badge badge-light">From: {{ $filters['from_date'] ?: 'Any' }}</span>
        <span class="badge badge-light">To: {{ $filters['to_date'] ?: 'Any' }}</span>
        <span class="badge badge-light">Search: {{ $filters['search'] !== '' ? $filters['search'] : 'None' }}</span>
        <span class="badge badge-light">Sort: {{ strtoupper(str_replace('_', ' ', (string) $filters['sort_by'])) }} ({{ strtoupper((string) $filters['sort_direction']) }})</span>
    </div>

    <div class="app-table-wrap table-responsive">
        <table class="app-table" data-no-auto-pager="1">
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
            @forelse($applicants as $index => $applicant)
                @php
                    $displayName = trim((string) $applicant->first_name . ' ' . (string) $applicant->last_name);
                    $preference = optional($applicant->applicationPreference);
                    $preferredCourse = optional($preference->course);
                    $programLabel = 'N/A';
                    if ($preference->apply_program === 'college') {
                        $programLabel = $preferredCourse->name ?: ($preferredCourse->code ?: 'College');
                    } elseif ($preference->apply_program === 'senior_high') {
                        $programLabel = $preference->apply_strand ?: 'Senior High';
                    }

                    $rawApplicationStatus = strtolower(trim((string) ($applicant->application_status ?: 'in process')));
                    $statusRaw = 'In Process';
                    if ($rawApplicationStatus === 'submitted' || $rawApplicationStatus === 'document submitted') {
                        $statusRaw = 'Document Submitted';
                    } elseif ($rawApplicationStatus === 'on probation' || $rawApplicationStatus === 'on_probation') {
                        $statusRaw = 'On Probation';
                    } elseif ($rawApplicationStatus === 'in process' || $rawApplicationStatus === 'in_process') {
                        $statusRaw = 'In Process';
                    } elseif ($rawApplicationStatus === 'rejected') {
                        $statusRaw = 'Rejected';
                    } elseif ($rawApplicationStatus === 'incomplete' || $rawApplicationStatus === 'draft') {
                        $statusRaw = 'Incomplete';
                    } elseif ($rawApplicationStatus === 'accepted') {
                        $statusRaw = 'Accepted';
                    }

                    $statusClass = 'app-status-pending';
                    if ($statusRaw === 'Accepted' || $statusRaw === 'Document Submitted' || $statusRaw === 'In Process') {
                        $statusClass = 'app-status-accepted';
                    }
                    if ($statusRaw === 'Rejected') {
                        $statusClass = 'app-status-rejected';
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $applicant->applicant_id }}</td>
                    <td>{{ $displayName ?: 'N/A' }}</td>
                    <td>{{ $programLabel }}</td>
                    <td>{{ optional($applicant->created_at)->format('M d, Y') ?: 'N/A' }}</td>
                    <td>{{ optional($applicant->updated_at)->format('M d, Y') ?: 'N/A' }}</td>
                    <td><span class="{{ $statusClass }}"><span class="app-status-dot"></span>{{ strtoupper($statusRaw) }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No applicants found for the selected filters.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="{{ asset('js/application-process-print.js') }}?v={{ time() }}"></script>
</body>
</html>
