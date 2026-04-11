@extends('layouts.student')

@section('title', 'Certificate of Registration - PLP')

@section('page-title', 'CERTIFICATE OF REGISTRATION')

@section('content')
<div class="section-offering-page d-flex flex-column align-items-center" style="width: 100%;">

    <div class="d-flex justify-content-center mb-2 no-print" style="max-width: 210mm; margin: 0 auto;">
        <button onclick="window.print()" class="btn btn-view-cor" style="width: auto; padding: 0 50px;">Print / Save as PDF</button>
    </div>

    <!-- COR Table -->
    <div class="cor-scroll-wrapper so-cor-page" style="width: 100%; display: grid; place-items: center; padding: 20px 0;">
        <div id="cor-table" class="cor-container" style="margin: 0 !important; display: block; float: none;">

            <!-- Student Information -->
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
                            <tr><td><strong>PARTICULARS</strong></td><td class="cor-assess-amount"><strong>AMOUNT</strong></td></tr>
                            <tr><td>Tuition Fee</td><td class="cor-assess-amount"></td></tr>
                            <tr><td class="cor-indent">Tuition Fee</td><td class="cor-assess-amount">17.00 x 50.00</td></tr>
                            <tr><td class="cor-indent">CW/ROTC TF</td><td class="cor-assess-amount">3.00 x 50.00</td></tr>
                            <tr class="cor-double"><td>Total Tuition Fee</td><td class="cor-assess-amount">1,000.00</td></tr>
                            <tr><td>Miscellaneous Fee</td><td class="cor-assess-amount"></td></tr>
                            <tr><td class="cor-indent">Miscellaneous Fee</td><td class="cor-assess-amount">300.00</td></tr>
                            <tr class="cor-double"><td>Total Miscellaneous Fee</td><td class="cor-assess-amount">300.00</td></tr>
                            <tr><td>Laboratory Fee</td><td class="cor-assess-amount"></td></tr>
                            <tr><td class="cor-indent">Laboratory Fee</td><td class="cor-assess-amount">500.00</td></tr>
                            <tr class="cor-double"><td>Total Laboratory Fee</td><td class="cor-assess-amount">500.00</td></tr>
                            <tr><td>Old Account</td><td class="cor-assess-amount">-</td></tr>
                            <tr><td>Current Account</td><td class="cor-assess-amount">1,800.00</td></tr>
                            <tr><td class="cor-indent"><strong>Contract / Petition Subject</strong></td><td class="cor-assess-amount">.</td></tr>
                            <tr><td class="cor-indent"><strong>Midterm Due</strong></td><td class="cor-assess-amount">.</td></tr>
                            <tr><td class="cor-indent"><strong>Final Due</strong></td><td class="cor-assess-amount">.</td></tr>
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
                        <div class="cor-enrolled-logo">
                            <img src="{{ asset('img/logobg.png') }}" alt="School Logo">
                        </div>
                        <p class="cor-enrolled-title">PAMANTASAN NG LUNGSOD NG PASIG<br>OFFICE OF THE UNIVERSITY REGISTRAR</p>
                        <p class="cor-enrolled-label">OFFICIALLY ENROLLED</p>
                        <p class="cor-enrolled-note">Present this certificate of registration for any claim or transaction that you engage in within the University.</p>
                    </div>
                    <!-- Row 1: Notice Text -->
                    <p class='cor-notice-text'>Notice to all students</p>

                    <!-- Row 2: Semester Text -->
                    <p class='cor-semester-text'>1st Sem 2021 - 2022</p>
                </div>
            </div>

            <!-- Legend -->
            <div class="cor-legend">
                <p class="cor-legend-title">LEGEND</p>
                <span class="cor-legend-item">* - Added Subjects</span>
                <span class="cor-legend-item">** - Officially Dropped Subjects</span>
            </div>

        </div>
    </div>{{-- /.cor-scroll-wrapper --}}

</div>
@endsection

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .sidebar, .topbar, .footer { display: none !important; }
        .content { margin: 0 !important; padding: 0 !important; }
        .cor-container { margin-top: 2in !important; margin-left: auto !important; margin-right: auto !important; }
    }
</style>
@endpush
