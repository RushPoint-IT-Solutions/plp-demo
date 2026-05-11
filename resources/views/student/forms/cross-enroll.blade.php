@extends('layouts.student')

@section('title', 'Application To Cross-Enroll - PLP')
@section('page-title', 'FORMS')
@section('body-class', 'page-student-forms')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-cross-enroll-overrides.css') }}">
@endpush

@section('content')
@php
    $displayName = trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
    $displayStudentNo = $profile->student_no ?? optional($student)->student_no;
    $displayNameUpper = strtoupper($displayName ?: optional($student)->name ?? '');
    $crossEnrollSubjects = $student ? $student->subjects->take(5)->values() : collect();
    $crossEnrollTotalUnits = $crossEnrollSubjects->sum(function ($subject) {
        return is_numeric($subject->units) ? (float) $subject->units : 0;
    });
@endphp
<div class="cor-scroll-wrapper acd-page">
    <div class="acd-canvas">
        <div class="acd-actions">
            <button id="acd-print-btn" type="button" class="btn btn-sm acd-print-btn">Print Form</button>
        </div>

        <div class="cor-container acd-form ce-form">
            <div class="ce-body">
                <p style="margin: 0 0 8px; font-size: 0.72rem; font-weight: 700; text-align: left;">PLPRO FORM NO. 1G Revised 2023</p>
                <h2>APPLICATION TO CROSS-ENROLL</h2>

                <p class="ce-date"><input type="text" class="acd-inline-input acd-inline-input--md acd-inline-input--center no-print-underline" value="{{ now()->format('F d, Y') }}"></p>
                <p>THE REGISTRAR<br>Pamantasan ng Lungsod ng Pasig<br>Pasig City</p>
                <p>Sir/Madam:</p>

                <p>
                    I wish to enroll/cross-enroll the following subjects at the
                    <input type="text" class="acd-inline-input acd-inline-input--md" value="">
                    located at
                    <input type="text" class="acd-inline-input acd-inline-input--md" value="">
                    in the
                    <input type="text" class="acd-inline-input acd-inline-input--sm" value="{{ optional($student)->semester ?? '' }}">
                    of Academic Year
                    <input type="text" class="acd-inline-input acd-inline-input--sm" value="{{ optional($student)->school_year ?? '' }}">.
                </p>

                <div class="ce-subject-grid">
                    <div>
                        @for($i = 0; $i < 5; $i++)
                            @php($subject = $crossEnrollSubjects->get($i))
                            <p><input type="text" class="acd-inline-input acd-inline-input--xl" value="{{ $subject ? trim(($subject->code ?: '') . ' - ' . ($subject->name ?: '')) : '' }}"></p>
                        @endfor
                    </div>
                    <div>
                        @for($i = 0; $i < 5; $i++)
                            @php($subject = $crossEnrollSubjects->get($i))
                            <p><input type="text" class="acd-inline-input acd-inline-input--sm" value="{{ $subject && $subject->units !== null ? rtrim(rtrim(number_format((float) $subject->units, 2, '.', ''), '0'), '.') : '' }}"> Units</p>
                        @endfor
                    </div>
                </div>

                <p class="ce-total">TOTAL = <input type="text" class="acd-inline-input acd-inline-input--sm acd-inline-input--center" value="{{ $crossEnrollTotalUnits > 0 ? rtrim(rtrim(number_format($crossEnrollTotalUnits, 2, '.', ''), '0'), '.') : '' }}"> UNITS</p>
                <p>I have passed the pre-requisite to the foregoing subjects, and I will promptly submit my ratings in the course after the close of the school term.</p>

                <div class="ce-sign-grid">
                    <div>
                        <p class="ce-invalid">NOT VALID<br>AS<br>PERMIT</p>
                        <p>Approved by:</p>
                        <p class="ce-line"><input type="text" class="acd-inline-input acd-inline-input--xl acd-inline-input--center" value=""></p>
                        <p><em>Dean</em></p>
                        <p class="ce-line"><input type="text" class="acd-inline-input acd-inline-input--xl acd-inline-input--center" value="FEDERICO G. NUEVA, MT"></p>
                        <p><em>Registrar</em></p>
                    </div>
                    <div>
                        <p>Very respectfully yours,</p>
                        <br>
                        <p class="ce-sign-name-row" style="text-align: center;">
                            <input type="text" class="acd-inline-input acd-inline-input--xl acd-inline-input--center acd-inline-input--caps" value="{{ $displayNameUpper }}" style="font-weight: bold;">
                        </p>
                        <p style="text-align: center; margin-top: 0; font-size: 0.8rem;">Signature over printed name</p>
                        <p style="margin-top: 15px;">Student Number: <input type="text" class="acd-inline-input acd-inline-input--md" value="{{ $displayStudentNo ?? '' }}"><br><input type="text" class="acd-inline-input acd-inline-input--md" value="{{ trim((optional($student)->program ?? 'Program') . ' & ' . (optional($student)->year_level ?? 'Year')) }}"></p>
                    </div>
                </div>

                <p class="ce-print-by">Printed by: <input type="text" class="acd-inline-input acd-inline-input--md" value="Jerald A. Culaniban"></p>
            </div>
        </div>
    </div>
</div>
@endsection
