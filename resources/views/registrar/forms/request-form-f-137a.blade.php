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
    $requestDate = now()->format('F j,');
    $requestYearSuffix = substr(now()->format('Y'), -1);
    $requestNumber = max(1, min(4, (int) ($requestNumber ?? 1)));
    $requestSuffixes = [1 => 'st', 2 => 'nd', 3 => 'rd', 4 => 'th'];
@endphp
<div class="rf137a-page"
     id="rf137a-page"
     data-print-url="{{ $student ? route('registrar.registrar-menu.forms.request-form-f-137a.print', ['student' => $student->id]) : '' }}">
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
                <small>Auto-detected</small>
            </span>
        </form>
        <button type="button" id="rf137a-print-btn" class="btn btn-success rf137a-print-btn" title="{{ $student ? 'Print this form' : 'Select a student before printing' }}" aria-label="Print this form" {{ $student ? '' : 'disabled' }}>Print</button>
    </div>
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
