@extends('layouts.student')

@section('title', 'Application For Change Of Grade - PLP')
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
                    <h2 class="cog-title-main">APPLICATION FOR CHANGE OF GRADE</h2>
                </div>
                <div class="cor-header-right cog-header-right"></div>
            </div>

            <div class="cog-body">
                <p class="cog-form-no-header" style="text-align: left; margin: 0 0 8px;">PLPRO FORM NO. 3F REVISED 2023</p>
                <div class="comp-date-wrap">
                    <p class="cog-date">Date of Application: <input type="text" class="cog-box cog-box--lg acd-inline-input no-print-underline" value="{{ now()->format('F d, Y') }}"></p>
                </div>

                <p class="cog-line-row">
                    <span>This is to certify that the ( ) Midterm ( ) Final grade of (student's name)</span>
                    <input type="text" class="cog-box cog-box--xl acd-inline-input" value="{{ $displayName ?: optional($student)->name }}">
                </p>
                <p class="cog-line-row cog-line-row--details">
                    <span>with student number</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="{{ $displayStudentNo }}">
                    <span>of (Program, Year &amp; Section)</span>
                    <input type="text" class="cog-box cog-box--prog acd-inline-input" value="{{ trim((optional($student)->program ?? '') . ' ' . (optional($student)->year_level ?? '')) }}">
                    <span>in (course code &amp; description)</span>
                    <span class="cog-course-combo">
                        <input type="text" class="cog-box cog-box--course-short acd-inline-input" value="">
                        <input type="text" class="cog-box cog-box--course-long acd-inline-input" value="">
                    </span>
                    <span>for the</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="{{ trim(preg_replace('/\bsemester\b/i', '', (string) optional($student)->semester)) }}">
                    <span>Semester of Academic Year</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="{{ optional($student)->school_year }}">
                </p>
                <p class="cog-line-row">
                    <span>Grade has been changed from</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="">
                    <span>to</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="">
                    <span>due to the following reason(s):</span>
                    <input type="text" class="acd-inline-input cog-line-fill" value="">
                </p>
                <p><input type="text" class="acd-inline-input cog-line-fill cog-line-fill--full" value=""></p>

                <div class="cog-computation">
                    <p class="cog-subtitle">NEW SEMESTRAL GRADE COMPUTATION:</p>
                    <table>
                        <thead>
                            <tr><th></th><th>PERCENTAGE</th><th>GRADE POINT</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>MIDTERM GRADE</td>
                                <td><input type="text" class="acd-cell-input" aria-label="Midterm percentage"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Midterm grade point"></td>
                            </tr>
                            <tr>
                                <td>FINAL GRADE</td>
                                <td><input type="text" class="acd-cell-input" aria-label="Final percentage"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Final grade point"></td>
                            </tr>
                            <tr>
                                <td>SEMESTRAL</td>
                                <td><input type="text" class="acd-cell-input" aria-label="Semestral percentage"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Semestral grade point"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="cog-signatures">
                    <div><p>Requested by:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p><strong>Faculty</strong></p></div>
                    <div><p>Conforme:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p><strong>Student</strong></p></div>
                    <div><p>Approved by:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p><strong>College Dean of Student</strong></p></div>
                    <div><p>Approved by:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p><strong>College Dean of Faculty</strong></p></div>
                    <div><p>Noted by:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p><strong>University Registrar</strong></p></div>
                </div>

                <p class="cog-attachment">Attachment:<br>1. Class Record of the Faculty</p>
                <div class="cog-cut-divider"></div>
            </div>
        </div>
    </div>
</div>
@endsection
