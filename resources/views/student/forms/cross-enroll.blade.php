@extends('layouts.student')

@section('title', 'Application To Cross-Enroll - PLP')
@section('page-title', 'FORMS')

@section('content')
@php
    $displayName = trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
    $displayStudentNo = $profile->student_no ?? optional($student)->student_no;
    $displayNameUpper = strtoupper($displayName ?: optional($student)->name ?? '');
@endphp
<div class="cor-scroll-wrapper acd-page">
    <div class="acd-canvas">
        <div class="acd-actions">
            <button id="acd-print-btn" type="button" class="btn btn-sm acd-print-btn">Print Form</button>
        </div>

        <div class="cor-container acd-form ce-form">
            <div class="cor-header ce-header">
                <div class="cor-header-left form-logo-left">
                    <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="cor-logo">
                </div>
                <div class="cor-header-center form-header-copy">
                    <p class="ce-gov">City Government of Pasig</p>
                    <p class="ce-school">PAMANTASAN NG LUNGSOD NG PASIG</p>
                    <p class="ce-office">OFFICE OF THE UNIVERSITY REGISTRAR</p>
                    <p class="ce-contact">Alkalde Jose St. Kapasigan, Pasig City, Philippines1600</p>
                    <p class="ce-contact">Tel Nos. 8642 8300 Telcfax 642-41-00 Hotline No. (0926)2690463</p>
                </div>
                <div class="cor-header-right"></div>
            </div>

            <div class="ce-body">
                <p>PLPRO FORM NO. 1G Revised 2023</p>
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
                        <p><input type="text" class="acd-inline-input acd-inline-input--xl" value=""></p>
                        <p><input type="text" class="acd-inline-input acd-inline-input--xl" value=""></p>
                        <p><input type="text" class="acd-inline-input acd-inline-input--xl" value=""></p>
                        <p><input type="text" class="acd-inline-input acd-inline-input--xl" value=""></p>
                        <p><input type="text" class="acd-inline-input acd-inline-input--xl" value=""></p>
                    </div>
                    <div>
                        <p><input type="text" class="acd-inline-input acd-inline-input--sm" value=""> Units</p>
                        <p><input type="text" class="acd-inline-input acd-inline-input--sm" value=""> Units</p>
                        <p><input type="text" class="acd-inline-input acd-inline-input--sm" value=""> Units</p>
                        <p><input type="text" class="acd-inline-input acd-inline-input--sm" value=""> Units</p>
                        <p><input type="text" class="acd-inline-input acd-inline-input--sm" value=""> Units</p>
                    </div>
                </div>

                <p class="ce-total">TOTAL = <input type="text" class="acd-inline-input acd-inline-input--sm acd-inline-input--center" value=""> UNITS</p>
                <p>I have passed the pre-requisite to the foregoing subjects, and I will promptly submit my ratings in the course after the close of the school term.</p>

                <div class="ce-sign-grid">
                    <div>
                        <p class="ce-invalid">NOT VALID<br>AS<br>PERMIT</p>
                        <p>Approved by:</p>
                        <p class="ce-line"><input type="text" class="acd-inline-input acd-inline-input--xl acd-inline-input--center" value=""></p>
                        <p><em>Dean</em></p>
                        <p><strong>FEDERICO G. NUEVA, MT</strong><br><em>Registrar</em></p>
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

                <p class="ce-print-by">Printed by: Jerald A. Culaniban</p>
            </div>
        </div>
    </div>
</div>
@endsection
