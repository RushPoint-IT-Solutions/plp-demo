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
                </div>
                <div class="cor-header-right cog-header-right">
                    <p class="cog-form-no-header">PLPRO FORM NO. 3F REVISED 2023</p>
                </div>
            </div>

            <div class="cog-body">
                <h2 class="cog-title-main">APPLICATION FOR CHANGE OF GRADE</h2>
                <p class="cog-date">Date of Application: <span class="cog-box cog-box--lg"></span></p>

                <p class="cog-line-row">
                    <span>This is to certify that the ( ) Midterm ( ) Final grade of (student's name)</span>
                    <span class="cog-box cog-box--xl">{{ $displayName ?: optional($student)->name }}</span>
                </p>
                <p class="cog-line-row">
                    <span>with student number</span>
                    <span class="cog-box cog-box--sm">{{ $displayStudentNo }}</span>
                    <span>of (Program, Year &amp; Section)</span>
                    <span class="cog-box cog-box--md">{{ trim((optional($student)->program ?? '') . ' ' . (optional($student)->year_level ?? '')) }}</span>
                    <span>in (course code &amp; description)</span>
                    <span class="cog-box cog-box--md"></span>
                </p>
                <p class="cog-line-row">
                    <span class="cog-box cog-box--xxl"></span>
                    <span>for the</span>
                    <span class="cog-box cog-box--sm">{{ optional($student)->semester }}</span>
                    <span>Semester of Academic Year</span>
                    <span class="cog-box cog-box--sm">{{ optional($student)->school_year }}</span>
                </p>
                <p class="cog-line-row">
                    <span>Grade has been changed from</span>
                    <span class="cog-box cog-box--sm"></span>
                    <span>to</span>
                    <span class="cog-box cog-box--sm"></span>
                    <span>due to the following reason(s):</span>
                    <span class="cog-line-fill"></span>
                </p>
                <p class="cog-line-fill cog-line-fill--full"></p>

                <div class="cog-computation">
                    <p class="cog-subtitle">NEW SEMESTRAL GRADE COMPUTATION:</p>
                    <table>
                        <thead>
                            <tr><th></th><th>PERCENTAGE</th><th>GRADE POINT</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>MIDTERM GRADE</td><td></td><td></td></tr>
                            <tr><td>FINAL GRADE</td><td></td><td></td></tr>
                            <tr><td>SEMESTRAL</td><td></td><td></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="cog-signatures">
                    <div><p>Requested by:</p><p class="cog-line"></p><p><strong>Faculty</strong></p></div>
                    <div><p>Conforme:</p><p class="cog-line"></p><p><strong>Student</strong></p></div>
                    <div><p>Approved by:</p><p class="cog-line"></p><p><strong>College Dean of Student</strong></p></div>
                    <div><p>Approved by:</p><p class="cog-line"></p><p><strong>College Dean of Faculty</strong></p></div>
                    <div><p>Noted by:</p><p class="cog-line"></p><p><strong>University Registrar</strong></p></div>
                </div>

                <p class="cog-attachment">Attachment:<br>1. Class Record of the Faculty</p>
                <div class="cog-cut-divider"></div>
            </div>
        </div>
    </div>
</div>
@endsection
