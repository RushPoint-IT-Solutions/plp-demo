@extends('layouts.registrar')

@section('title', 'PLP - Request Form for F 137A')
@section('page-title', 'REQUEST FORM FOR F 137A')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/request-form-f-137a.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $studentName = optional($student)->name ? strtoupper(optional($student)->name) : '';
    $requestDate = now()->format('F j,');
    $requestYearSuffix = substr(now()->format('Y'), -1);
@endphp
<div class="rf137a-page" id="rf137a-page">
    <div class="rf137a-actions d-flex justify-content-end w-100 d-print-none">
        <button type="button" id="rf137a-print-btn" class="btn btn-success rf137a-print-btn" title="Print this form" aria-label="Print this form">Print</button>
    </div>
    <article class="rf137a-sheet a4-wrapper" aria-label="Request Form for F 137A">
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
            <p class="rf137a-signatory-name">MR. FEDERICO G. NUEVA</p>
            <p class="rf137a-signatory-role">University Registrar</p>
        </div>

        <p class="rf137a-request-order">1<sup>st</sup> Request</p>
    </article>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-request-form-f137a.js') }}?v={{ time() }}"></script>
@endpush
