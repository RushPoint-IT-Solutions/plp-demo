@extends('layouts.registrar')

@section('title', 'PLP - Request Form for F 137A')
@section('page-title', 'REQUEST FORM FOR F 137A')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/request-form-f-137a.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $studentName = optional($student)->name ? strtoupper(optional($student)->name) : '';
    $studentNumber = trim((string) optional($student)->student_no);
    $studentSearchLabel = trim($studentNumber . ' - ' . (string) optional($student)->name, ' -');
    $formRequestDate = $formRequestDate ?? now();
    $requestDate = $formRequestDate->format('F j,');
    $requestYearSuffix = substr($formRequestDate->format('Y'), -1);
    $requestNumber = max(1, min(2, (int) ($requestNumber ?? 1)));
    $requestSuffixes = [1 => 'st', 2 => 'nd'];
    $requestWords = [1 => 'First', 2 => 'Second'];
    $requestHistory = $requestHistory ?? collect();
    $historyByIssuance = $requestHistory->keyBy('issuance_number');
    $formatTrackedDate = function ($value) {
        return $value ? \Carbon\Carbon::parse($value)->format('M j, Y') : '—';
    };
@endphp
<div class="rf137a-page"
     id="rf137a-page"
     data-print-url="{{ $student ? route('registrar.registrar-menu.forms.request-form-f-137a.print', ['student' => $student->id]) : '' }}">
    @if(session('success'))
        <div class="alert alert-success rf137a-feedback d-print-none" role="status">{{ session('success') }}</div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger rf137a-feedback d-print-none" role="alert">{{ $errors->first() }}</div>
    @endif
    <div class="rf137a-actions d-print-none">
        <form method="GET" action="{{ route('registrar.registrar-menu.forms.request-form-f-137a') }}" class="rf137a-student-filter-form">
            <label for="rf137a-student-search-input" class="rf137a-student-filter-label">Student</label>
            <div class="rf137a-student-search"
                 data-rf137a-student-search
                 data-search-url="{{ route('registrar.registrar-menu.forms.request-form-f-137a.students.search') }}">
                <input type="hidden" name="student_id" id="rf137a-student-id" value="{{ optional($student)->id ?: '' }}">
                <input type="text"
                       id="rf137a-student-search-input"
                       value="{{ $studentSearchLabel }}"
                       placeholder="Search student name or number"
                       autocomplete="off"
                       role="combobox"
                       aria-autocomplete="list"
                       aria-expanded="false"
                       aria-controls="rf137a-student-results">
                <div class="rf137a-student-results" id="rf137a-student-results" role="listbox"></div>
            </div>
            <span class="rf137a-request-filter-label">Request</span>
            <span class="rf137a-request-auto" aria-live="polite">
                <strong id="rf137a-request-auto-label">{{ $requestNumber }}{{ $requestSuffixes[$requestNumber] }} Request</strong>
                <small>{{ $activeRequest ? 'Awaiting print' : ($requestHistory->count() >= 2 ? 'Both issued' : 'Next issuance') }}</small>
            </span>
        </form>
        <button type="button" id="rf137a-print-btn" class="btn btn-success rf137a-print-btn" title="{{ $canPrint ? 'Print this issuance' : ($student ? 'Record a request before printing' : 'Select a student before printing') }}" aria-label="Print this issuance" {{ $canPrint ? '' : 'disabled' }}>Print {{ $requestNumber }}{{ $requestSuffixes[$requestNumber] }} Issuance</button>
    </div>

    @if($student)
        <section class="rf137a-tracking d-print-none" aria-labelledby="rf137a-tracking-title">
            <div class="rf137a-tracking-head">
                <div>
                    <h2 id="rf137a-tracking-title">Form 137 Request Tracking</h2>
                    <p>Only the first and second issuances are recorded.</p>
                </div>
                @if($canRecordRequest)
                    <form method="POST" action="{{ route('registrar.registrar-menu.forms.request-form-f-137a.request', ['student' => $student->id]) }}" class="rf137a-record-form">
                        @csrf
                        <label for="rf137a-requested-on">Date requested</label>
                        <input type="date" id="rf137a-requested-on" name="requested_on" value="{{ old('requested_on', now()->toDateString()) }}" required>
                        <button type="submit" class="btn btn-primary">Record {{ $requestWords[$requestNumber] }} Request</button>
                    </form>
                @elseif($activeRequest)
                    <span class="rf137a-tracking-note">{{ $requestWords[$requestNumber] }} request is ready to print.</span>
                @else
                    <span class="rf137a-tracking-note is-complete">First and second issuances completed.</span>
                @endif
            </div>
            <div class="rf137a-history-wrap">
                <table class="rf137a-history-table">
                    <thead>
                        <tr>
                            <th scope="col">Issuance</th>
                            <th scope="col">Date Requested</th>
                            <th scope="col">Date Printed</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([1, 2] as $issuance)
                            @php $historyItem = $historyByIssuance->get($issuance); @endphp
                            <tr data-issuance-row="{{ $issuance }}">
                                <td>{{ $requestWords[$issuance] }} ({{ $issuance }}{{ $requestSuffixes[$issuance] }})</td>
                                <td data-requested-at>{{ $formatTrackedDate(optional($historyItem)->requested_at) }}</td>
                                <td data-printed-at>{{ $formatTrackedDate(optional($historyItem)->printed_at) }}</td>
                                <td data-request-status>
                                    @if(!$historyItem)
                                        Not requested
                                    @elseif(!$historyItem->printed_at)
                                        Awaiting print
                                    @else
                                        Printed
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
    <article class="rf137a-sheet a4-wrapper" aria-label="Request Form for F 137A">
        <header class="rf137a-letterhead">
            <div class="rf137a-letterhead-seal" aria-hidden="true">
                <img src="{{ asset('img/plplogo2000.png') }}" alt="">
            </div>
            <div class="rf137a-letterhead-copy">
                <div class="rf137a-letterhead-city">City Government of Pasig</div>
                <div class="rf137a-letterhead-school">PAMANTASAN NG LUNGSOD NG PASIG</div>
                <div class="rf137a-letterhead-office">OFFICE OF THE UNIVERSITY REGISTRAR</div>
                <div class="rf137a-letterhead-address">Alkalde Jose St. Kapasigan Pasig City, Philippines 1600</div>
                <div class="rf137a-letterhead-contact">Tel No. 8642-8300 Email: registrar@plpasig.edu.ph</div>
            </div>
        </header>

        <div class="rf137a-topline">
            <p class="rf137a-form-no">PLPRO FORM NO.4A</p>
            <label class="rf137a-date-line">
                <span class="rf137a-date-label">Date</span>
                <input type="text" class="rf137a-inline-input rf137a-inline-input--date" value="{{ old('request_date', $requestDate) }}">
                <span class="rf137a-date-year">202</span>
                <input type="text" class="rf137a-inline-input rf137a-inline-input--year" value="{{ old('request_year_suffix', $requestYearSuffix) }}" maxlength="1">
            </label>
        </div>

        <p class="rf137a-line-label">The Registrar</p>
        <p class="rf137a-line-wrap">
            <input type="text" class="rf137a-inline-input rf137a-inline-input--full" value="{{ old('registrar_school_name') }}">
        </p>

        <p class="rf137a-line-label">Dear Sir/Madam:</p>

        <p class="rf137a-paragraph">
            This is to request from your good office to furnish Pamantasan ng Lungsod ng Pasig the original copy of Form 137A of
            <span class="rf137a-sentence-field">
                <input type="text" class="rf137a-inline-input rf137a-inline-input--sentence" value="{{ old('student_name', $studentName) }}">
            </span>,
            who has been temporarily admitted in this university upon presentation of his/her credentials, which shows he/she have attended your school and is eligible for transfer.
        </p>

        <p class="rf137a-paragraph">
            Kindly include the remark "Copy for Pamantasan ng Lungsod ng Pasig".
        </p>

        <p class="rf137a-paragraph">
            Please entrust the requested document to the bearer.
        </p>

        <p class="rf137a-paragraph">
            Thank you.
        </p>

        <div class="rf137a-signature-block">
            <p class="rf137a-signature-intro">Very truly yours,</p>
            <img src="{{ asset('img/deans-honors-signature.png') }}" class="rf137a-signature-image" alt="Registrar signature">
            <p class="rf137a-signatory-name">FEDERICO G. NUEVA, MT</p>
            <p class="rf137a-signatory-role">University Registrar</p>
        </div>

        <p class="rf137a-request-order" id="rf137a-request-order"><span>{{ $requestNumber }}</span><sup>{{ $requestSuffixes[$requestNumber] }}</sup> Request</p>
    </article>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-request-form-f137a.js') }}?v={{ time() }}"></script>
@endpush
