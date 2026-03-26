<!-- resources/views/student/section-offering.blade.php -->
@extends('layouts.student')

@section('title', 'Section Offering - PLP')

@section('page-title', 'SECTION OFFERING')

@section('content')
<div class="form-section-container section-offering-page sched-page-container">
    
    <!-- Filter Section (hidden after Download COR) -->
    <div id="filter-section" class="filter-grid">

        <!-- Row 1, Col 1: Selected Semester -->
        <div class="filter-cell">
            <label class="form-label-plp">SELECTED SEMESTER</label>
            <select class="form-select form-input-long">
                <option value="" disabled selected>Select Semester</option>
                @foreach($semesters as $semester)
                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Row 1, Col 2: View COR Button -->
        <div class="filter-cell filter-cell--action">
            <button id="cor-action-btn" class="btn btn-success btn-view-cor">View COR</button>
        </div>

        <!-- Row 2, Col 1: Course -->
        <div class="filter-cell">
            <label class="form-label-plp">COURSE</label>
            <select class="form-select form-input-long">
                <option value="" disabled selected>Select Course</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->code }}</option>
                @endforeach
            </select>
        </div>

        <!-- Row 2, Col 2: Selected Yr & Block -->
        <div class="filter-cell">
            <label class="form-label-plp">SELECTED YR & BLOCK</label>
            <select class="form-select form-input-short">
                <option value="" disabled selected>Select your block</option>
                @foreach($yearBlocks as $block)
                <option value="{{ $block->id }}">{{ $block->label }}</option>
                @endforeach
            </select>
        </div>

    </div>{{-- /#filter-section --}}

    <!-- COR Table (Hidden by Default) -->
    <div class="cor-scroll-wrapper so-cor-page">
    <div id="cor-table" class="cor-container" style="display: none; margin-top: 2in;">

        <!-- Student Information (matches printed layout, no header/green bar) -->
        <div class="cor-student-info">
            <div class="cor-info-row cor-info-row--grid">
                <div class="cor-info-col">
                    <div class="cor-info-line"><span class="cor-info-label">Enrollment No.:</span><span class="cor-info-value">{{ optional($student)->enrollment_no }}</span></div>
                    <div class="cor-info-line"><span class="cor-info-label">Student No.:</span><span class="cor-info-value">{{ optional($student)->student_no }}</span></div>
                    <div class="cor-info-line"><span class="cor-info-label">Student Name:</span><span class="cor-info-value">{{ optional($student)->name }}</span></div>
                    <div class="cor-info-line"><span class="cor-info-label">Address:</span><span class="cor-info-value">{{ optional($student)->address }}</span></div>
                    <div class="cor-info-line"><span class="cor-info-label">Course:</span><span class="cor-info-value">{{ optional($student)->program }}</span></div>
                    <div class="cor-info-line"><span class="cor-info-label">Department:</span><span class="cor-info-value">{{ optional($student)->department }}</span></div>
                </div>
                <div class="cor-info-col">
                    <div class="cor-info-line"><span class="cor-info-label">Enrollment Date:</span><span class="cor-info-value">{{ optional($student)->enrollment_date }}</span></div>
                    <div class="cor-info-line"><span class="cor-info-label">Curriculum:</span><span class="cor-info-value">{{ optional($student)->curriculum }}</span></div>
                    <div class="cor-info-line"><span class="cor-info-label">School Year:</span><span class="cor-info-value">{{ optional($student)->school_year_label }}</span></div>
                </div>
                <div class="cor-info-col">
                    <div class="cor-info-line"><span class="cor-info-label">Year Level:</span><span class="cor-info-value">{{ optional($student)->year_level }}</span></div>
                    <div class="cor-info-line"><span class="cor-info-label">Student Type:</span><span class="cor-info-value">{{ optional($student)->student_type }}</span></div>
                </div>
            </div>
            <div class="cor-info-row cor-info-row--scholar">
                <span class="cor-info-label">Scholarship/Grant:</span>
                <span class="cor-info-value cor-info-value--wide">{{ optional($student)->scholarship }}</span>
            </div>
        </div>

        <!-- Schedule Table -->
        <table class="cor-table">
            <thead>
                <tr>
                    <th class="cor-table-title cor-th" colspan="8">CLASS SCHEDULE</th>
                </tr>
                <tr>
                    <th class="cor-th col-name">Subject Name</th>
                    <th class="cor-th col-desc">Subject Description</th>
                    <th class="cor-th col-section">Section</th>
                    <th class="cor-th col-units">Units</th>
                    <th class="cor-th col-room">Room</th>
                    <th class="cor-th col-days">Days</th>
                    <th class="cor-th col-time">Time</th>
                    <th class="cor-th col-pay">Pay Units</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                <tr>
                    <td class="cor-td col-name">{{ $subject->code }}</td>
                    <td class="cor-td col-desc">{{ $subject->name }}</td>
                    <td class="cor-td col-section">{{ $subject->section ?? optional($student)->year_level }}</td>
                    <td class="cor-td col-units">{{ number_format($subject->units, 1) }}</td>
                    <td class="cor-td col-room">{{ $subject->room }}</td>
                    <td class="cor-td col-days">{{ $subject->days }}</td>
                    <td class="cor-td col-time">{{ $subject->time_range }}</td>
                    <td class="cor-td col-pay">{{ number_format($subject->pay_units ?? $subject->units, 1) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $totalUnits = $subjects->sum('units');
                @endphp
                <tr>
                    <td class="cor-td col-name"></td>
                    <td class="cor-td col-desc"></td>
                    <td class="cor-td col-section cor-total-label">TOTAL:</td>
                    <td class="cor-td col-units cor-total-value">{{ number_format($totalUnits, 2) }}</td>
                    <td class="cor-td col-room"></td>
                    <td class="cor-td col-days"></td>
                    <td class="cor-td col-time"></td>
                    <td class="cor-td col-pay"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Assessment of Fees -->
        <div class="cor-assessment">
            <div class="cor-assessment-left">
                <p class="cor-assessment-title">ASSESSMENT OF FEES</p>
                <table class="cor-assessment-table">
                    <tbody>
                        <tr><td>Tuition Fee</td><td class="cor-assess-amount"></td></tr>
                        <tr><td class="cor-indent">Tuition Fee</td><td class="cor-assess-amount"></td></tr>
                        <tr><td class="cor-indent">CW/ROTC TF</td><td class="cor-assess-amount"></td></tr>
                        <tr class="cor-double"><td>Total Tuition Fee</td><td class="cor-assess-amount"></td></tr>
                        <tr><td>Miscellaneous Fee</td><td class="cor-assess-amount"></td></tr>
                        <tr class="cor-double"><td>Total Miscellaneous Fee</td><td class="cor-assess-amount"></td></tr>
                        <tr><td>Laboratory Fee</td><td class="cor-assess-amount"></td></tr>
                        <tr class="cor-double"><td>Total Laboratory Fee</td><td class="cor-assess-amount"></td></tr>
                        <tr><td>Old Account</td><td class="cor-assess-amount"></td></tr>
                        <tr><td>Current Account</td><td class="cor-assess-amount"></td></tr>
                        <tr><td>Contract / Petition Subject</td><td class="cor-assess-amount"></td></tr>
                        <tr><td>Midterm Due</td><td class="cor-assess-amount"></td></tr>
                        <tr><td>Final Due</td><td class="cor-assess-amount"></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="cor-assessment-right">
                <p class="cor-cert-text">This is to certify that the student whose name appears on this document is officially enrolled this term with subject load listed above.</p>
                <div class="cor-signatures">
                    <div class="cor-signature-left">
                        <p class="cor-signature-name">{{ strtoupper(optional($student)->name ?? '') }}</p>
                        <p class="cor-signature-role">STUDENT SIGNATURE</p>
                    </div>
                    <div class="cor-signature-right">
                        <p class="cor-signature-name">Prof. Federico G. Nueva</p>
                        <p class="cor-signature-role">UNIVERSITY REGISTRAR</p>
                    </div>
                </div>

                <div class="cor-enrolled-box">
                    <p class="cor-enrolled-title">PAMANTASAN NG LUNGSOD NG PASIG<br>OFFICE OF THE UNIVERSITY REGISTRAR</p>
                    <p class="cor-enrolled-label">OFFICIALLY ENROLLED</p>
                    <p class="cor-enrolled-note">Present this certificate of registration for any claim or transaction that you engage in within the University.</p>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="cor-legend">
            <span class="cor-legend-item">* - Added Subjects</span>
            <span class="cor-legend-item">** - Officially Dropped Subjects</span>
        </div>

    </div>
    </div>{{-- /.cor-scroll-wrapper --}}

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/section-offering.js') }}"></script>
@endpush
