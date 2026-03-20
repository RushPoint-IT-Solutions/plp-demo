@extends('layouts.student')

@section('title', 'Application For Completion Of Grade - PLP')
@section('page-title', 'FORMS')

@section('content')
@php
    $displayName = trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
    $displayStudentNo = $profile->student_no ?? optional($student)->student_no;
@endphp
<div class="cor-scroll-wrapper acd-page">
    <div class="acd-canvas">
        <div class="acd-actions">
            <button id="acd-print-btn" type="button" class="btn btn-sm acd-print-btn">Print Form</button>
        </div>

        <div class="cor-container acd-form cog-form">
            <div class="cor-header cog-header">
                <div class="cor-header-left form-logo-left">
                    <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="cor-logo">
                </div>
                <div class="cor-header-center form-header-copy">
                    <p class="loa-school">PAMANTASAN NG LUNGSOD NG PASIG</p>
                    <p class="loa-office">OFFICE OF THE UNIVERSITY REGISTRAR</p>
                    <h2 class="cog-title-main">APPLICATION FOR COMPLETION OF GRADE</h2>
                </div>
                <div class="cor-header-right cog-header-right"></div>
            </div>

            <div class="cog-body comp-body">
                <p class="cog-form-no-header" style="text-align: left; margin: 0 0 8px;">PLPRO FORM NO. 3C</p>
                <div class="comp-date-wrap">
                    <p class="cog-date">Date of Application: <input type="text" class="cog-box cog-box--lg acd-inline-input no-print-underline" value="{{ now()->format('F d, Y') }}"></p>
                </div>

                <div class="comp-info-grid">
                    <div class="comp-info-col">
                        <p class="comp-info-title">PERSONAL INFORMATION</p>
                        <div class="comp-info-row">
                            <span>LAST NAME:</span>
                            <input type="text" class="acd-inline-input" value="{{ $profile->last_name ?? optional($student)->last_name }}">
                        </div>
                        <div class="comp-info-row">
                            <span>FIRST NAME:</span>
                            <input type="text" class="acd-inline-input" value="{{ $profile->first_name ?? optional($student)->first_name }}">
                        </div>
                        <div class="comp-info-row">
                            <span>MIDDLE NAME:</span>
                            <input type="text" class="acd-inline-input" value="{{ $profile->middle_name ?? optional($student)->middle_name }}">
                        </div>
                    </div>
                    <div class="comp-info-col">
                        <p class="comp-info-title">ACADEMIC INFORMATION</p>
                        <div class="comp-info-row">
                            <span>STUDENT NUMBER:</span>
                            <input type="text" class="acd-inline-input" value="{{ $displayStudentNo }}">
                        </div>
                        <div class="comp-info-row">
                            <span>PROGRAM:</span>
                            <input type="text" class="acd-inline-input" value="{{ optional($student)->program }}">
                        </div>
                        <div class="comp-info-row">
                            <span>YEAR &amp; SECTION:</span>
                            <input type="text" class="acd-inline-input" value="{{ optional($student)->year_level ?? '   ' }} - {{ optional($student)->section ?? '   ' }}">
                        </div>
                    </div>
                </div>

                <p class="comp-cert">
                    I AM HEREBY APPLYING FOR THE COMPLETION OF MY INCOMPLETE GRADE(S) IN THE FOLLOWING COURSE(S) FOR THE<br>
                    <input type="text" class="acd-inline-input acd-inline-input--md" style="text-align:center;" value="{{ optional($student)->semester }}"> Semester, A.Y. 
                    <input type="text" class="acd-inline-input acd-inline-input--md" style="text-align:center;" value="{{ optional($student)->school_year }}">
                </p>

                <div class="comp-table">
                    <table>
                        <thead>
                            <tr>
                                <th>COURSE<br>CODE</th>
                                <th>COURSE DESCRIPTION</th>
                                <th>UNITS</th>
                                <th>NAME OF FACULTY</th>
                                <th>SIGNATURE</th>
                                <th>MID<br>GRD</th>
                                <th>FIN<br>GRD</th>
                                <th>SEM<br>GRD</th>
                                <th>SECTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" class="acd-cell-input" aria-label="Course code row 1"></td>
                                <td><input type="text" class="acd-cell-input acd-cell-input--left" aria-label="Course description row 1"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Units row 1"></td>
                                <td><input type="text" class="acd-cell-input acd-cell-input--left" aria-label="Faculty name row 1"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Signature row 1"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Midterm grade row 1"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Final grade row 1"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Semestral grade row 1"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Section row 1"></td>
                            </tr>
                            <tr>
                                <td><input type="text" class="acd-cell-input" aria-label="Course code row 2"></td>
                                <td><input type="text" class="acd-cell-input acd-cell-input--left" aria-label="Course description row 2"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Units row 2"></td>
                                <td><input type="text" class="acd-cell-input acd-cell-input--left" aria-label="Faculty name row 2"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Signature row 2"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Midterm grade row 2"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Final grade row 2"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Semestral grade row 2"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Section row 2"></td>
                            </tr>
                            <tr>
                                <td><input type="text" class="acd-cell-input" aria-label="Course code row 3"></td>
                                <td><input type="text" class="acd-cell-input acd-cell-input--left" aria-label="Course description row 3"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Units row 3"></td>
                                <td><input type="text" class="acd-cell-input acd-cell-input--left" aria-label="Faculty name row 3"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Signature row 3"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Midterm grade row 3"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Final grade row 3"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Semestral grade row 3"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Section row 3"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="comp-sigs">
                    <div class="comp-sig-col">
                        <p>Requested:</p>
                        <div class="comp-sig-line">
                            <p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p>
                            <p>Student's Signature</p>
                        </div>
                        <div class="comp-sig-line">
                            <p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p>
                            <p>College Dean</p>
                        </div>
                    </div>
                    <div class="comp-sig-col">
                        <p>Approved:</p>
                        <div class="comp-sig-line" style="margin-top: 35px;">
                            <p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p>
                            <p>University Registrar</p>
                        </div>
                    </div>
                    <div class="comp-sig-col">
                        <p>Date Approved:</p>
                        <div class="comp-sig-line" style="margin-top: 35px;">
                            <p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p>
                        </div>
                    </div>
                </div>

                <div class="comp-validation">
                    <p class="comp-val-title">VALIDATION</p>
                    <div class="comp-val-grid">
                        <div class="comp-val-item">
                            <span>RECEIVED BY THE CIO:</span>
                            <input type="text" class="acd-inline-input" value="">
                        </div>
                        <div class="comp-val-item">
                            <span>RECORDED BY :</span>
                            <input type="text" class="acd-inline-input" value="">
                        </div>
                    </div>
                </div>

                <div class="cog-cut-divider" style="margin-top: 30px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection
