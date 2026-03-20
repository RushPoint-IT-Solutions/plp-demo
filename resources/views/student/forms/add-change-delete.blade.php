@extends('layouts.student')

@section('title', 'Adding/Changing/Deleting Form - PLP')
@section('page-title', 'FORMS')

@section('content')
@php
    $displayName = trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
    $displayStudentNo = $profile->student_no ?? optional($student)->student_no;
    $displayCourse = optional($student)->program;
    $displaySection = optional($student)->year_level;
@endphp
<div class="cor-scroll-wrapper acd-page">
    <div class="acd-canvas">
        <div class="acd-actions">
            <button id="acd-print-btn" type="button" class="btn btn-sm acd-print-btn">Print Form</button>
        </div>

        <div class="cor-container acd-form">
        <div class="cor-header acd-header">
            <div class="cor-header-left form-logo-left">
                <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="cor-logo">
            </div>
            <div class="cor-header-center acd-header-center">
                <p class="acd-school-name">PAMANTASAN NG LUNGSOD NG PASIG</p>
                <p class="acd-office-name">OFFICE OF THE UNIVERSITY REGISTRAR</p>
                <p class="acd-contact">Tel. Nos. 642-83-00</p>
            </div>
            <div class="cor-header-right"></div>
        </div>

        <div class="acd-body">
            <p class="acd-form-number">PLPRO FORM NO.2</p>

            <div class="acd-meta-grid">
                <div class="acd-left-meta">
                    <p class="acd-label">Application for :</p>
                    <label class="acd-check"><input type="checkbox"> Adding of Course/s</label>
                    <label class="acd-check"><input type="checkbox"> Deleting of Course/s</label>
                    <label class="acd-check"><input type="checkbox"> Changing of Course/s</label>
                    <label class="acd-check"><input type="checkbox"> Dropping of Course/s</label>
                    <label class="acd-check"><input type="checkbox"> Changing of Section</label>
                </div>

                <div class="acd-right-meta">
                    <div class="acd-line-row"><span>Semester &amp; Academic Year:</span><input type="text" class="acd-line acd-inline-input" value="{{ optional($student)->semester }} {{ optional($student)->school_year ? ' / SY ' . $student->school_year : '' }}"></div>
                    <div class="acd-line-row"><span>Student Number:</span><input type="text" class="acd-line acd-inline-input" value="{{ $displayStudentNo }}"></div>
                    <div class="acd-line-row"><span>Student Name:</span><input type="text" class="acd-line acd-inline-input" value="{{ $displayName ?: optional($student)->name }}"></div>
                    <div class="acd-line-row"><span>Course:</span><input type="text" class="acd-line acd-inline-input" value="{{ $displayCourse }}"></div>
                    <div class="acd-line-row"><span>Section:</span><input type="text" class="acd-line acd-inline-input" value="{{ $displaySection }}"></div>
                </div>
            </div>

            <div class="acd-table-block">
                <p class="acd-table-title">COURSE/S TO BE ADDED, CHANGED, DELETED OR DROPPED</p>
                <table class="acd-table">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>DESCRIPTIVE TITLE</th>
                            <th>UNIT/s</th>
                            <th>DAY/s</th>
                            <th>TIME</th>
                            <th>ROOM</th>
                            <th>SECTION</th>
                            <th>REG VERIF</th>
                            <th>NAME and SIGNATURE of PROFESSOR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < 10; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="2" class="acd-total-label">TOTAL</td>
                            <td></td>
                            <td colspan="6"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="acd-table-block">
                <p class="acd-table-title">COURSE(S) TO BE TAKEN INSTEAD</p>
                <table class="acd-table">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>DESCRIPTIVE TITLE</th>
                            <th>UNIT/s</th>
                            <th>DAY/s</th>
                            <th>TIME</th>
                            <th>ROOM</th>
                            <th>SECTION</th>
                            <th>REG VERIF</th>
                            <th>NAME and SIGNATURE of PROFESSOR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < 8; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="2" class="acd-total-label">TOTAL</td>
                            <td></td>
                            <td colspan="6"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="acd-bottom-grid">
                <div class="acd-bottom-left">
                    <p>CHANGE IN UNIT LOAD FROM 15 TO 15</p>
                    <p>REASON: <input type="text" class="acd-inline-input acd-inline-input--lg" value=""></p>
                    <p class="acd-mt-lg">To be accomplished by the Registrar's Office.</p>
                    <p>ADDITIONAL TF: <input type="text" class="acd-inline-input acd-inline-input--md" value=""></p>
                    <p class="acd-mt-lg">To be accomplished by the Finance Office:</p>
                    <p>Payment: <input type="text" class="acd-inline-input acd-inline-input--lg" value=""></p>
                    <p>OR No: <input type="text" class="acd-inline-input acd-inline-input--lg" value=""></p>
                    <p>Payment Received By: <input type="text" class="acd-inline-input acd-inline-input--md" value=""></p>
                </div>

                <div class="acd-bottom-right">
                    <p>APPROVED BY DEAN: <input type="text" class="acd-inline-input acd-inline-input--xl" value=""> (Name and Signature)</p>
                    <p class="acd-emphasis">VALIDATION and RECORDING BY THE REGISTRAR'S OFFICE:</p>
                    <p>RECEIVED BY: <input type="text" class="acd-inline-input acd-inline-input--xl" value=""> (Registrar's Personnel)</p>
                    <p>RECORDED BY: <input type="text" class="acd-inline-input acd-inline-input--xl" value=""> (Records Officer)</p>
                    <p>COR/OSL ISSUED BY: <input type="text" class="acd-inline-input acd-inline-input--xl" value=""> (Registrar's Personnel)</p>
                    <p>NOTED BY: <input type="text" class="acd-inline-input acd-inline-input--xl" value=""> (Registrar's Personnel)</p>
                </div>
            </div>

            <p class="acd-footer-note">This office will not be held liable for the contingencies that may arise out of the student's negligence in this respect.</p>
        </div>
        </div>
    </div>
</div>
@endsection
