@php
    $studentFullName = $studentFullName ?? '';
    $studentNo = $studentNo ?? '';
    $programLabel = $programLabel ?? '';
    $hdNo = $hdNo ?? '';
    $dateIssued = $dateIssued ?? '';
@endphp
<div class="hd-sheet">
    <div class="hd-header">
        <img class="hd-seal" src="{{ asset('img/logobg.png') }}" alt="PLP Seal">
        <div class="hd-header-text">
            <div class="hd-header-city">City Government of Pasig</div>
            <div class="hd-header-school">PAMANTASAN NG LUNGSOD NG PASIG</div>
            <div class="hd-header-office">OFFICE OF THE UNIVERSITY REGISTRAR</div>
            <div class="hd-header-address">Alkalde Jose St. Kapasigan, Pasig City, Philippines 1600</div>
            <div class="hd-header-tel">Tel Nos. 628-1014 loc 110 &nbsp; email address: plpasigregistrar@yahoo.com</div>
        </div>
    </div>

    <div class="hd-bar">CERTIFICATE OF ELIGIBILITY TO TRANSFER/HONORABLE DISMISSAL</div>

    <div class="hd-no-block">
        <div>HD NO: <strong class="hd-value">{{ $hdNo }}</strong></div>
        <div>Date: <strong class="hd-value">{{ $dateIssued }}</strong></div>
    </div>

    <div class="hd-label">TO WHOM IT MAY CONCERN:</div>

    <div class="hd-line-row hd-line-row--right">
        <span class="hd-line-label">This certifies that</span>
        <span class="hd-line-value">{{ $studentFullName }}</span>
    </div>
    <div class="hd-line-row">
        <span class="hd-line-label">a student at the program of</span>
        <span class="hd-line-value">{{ $programLabel }}</span>
    </div>
    <p class="hd-body-text">is hereby granted permission to transfer from this university.</p>
    <p class="hd-body-text hd-indent">
        Official Transcript of Records shall be forwarded upon receipt of the Request Slip below.
    </p>

    <div class="hd-sig-block">
        <div class="hd-sig-line"></div>
        <div class="hd-sig-caption">University Registrar</div>
    </div>

    <div class="hd-cut-line">(To be accomplished by the requesting school. Cut here and send the lower part to PLP)</div>

    <div class="hd-bar hd-bar-plain">REQUEST FOR OFFICIAL TRANSCRIPT OF RECORDS</div>

    <div class="hd-no-block">
        <div>HD NO: <strong class="hd-value">{{ $hdNo }}</strong></div>
        <div>Date: <strong class="hd-value">{{ $dateIssued }}</strong></div>
    </div>

    <div class="hd-address-box">
        <div class="hd-address-line hd-address-line--title">THE REGISTRAR</div>
        <div class="hd-address-line">Pamantasan ng Lungsod ng Pasig</div>
        <div class="hd-address-line">Alkalde Jose St. Kapasigan, Pasig City</div>
    </div>

    <p class="hd-body-text" style="margin-top:3mm;">Dear Sir/Madam:</p>

    <div class="hd-line-row hd-line-row--right">
        <span class="hd-line-label">Please send us the Official Transcript of Records of the student</span>
        <span class="hd-line-value">{{ $studentFullName }}</span>
    </div>
    <p class="hd-body-text">whose conforming signature appears below.</p>

    <div class="hd-school-sig-block">
        <div class="hd-sig-line"></div>
        <div class="hd-sig-caption">Signature over Printed Name and Position of School Official</div>
    </div>

    <div class="hd-fields">
        <div class="hd-field-row">
            <div class="hd-field-label">Student's Signature</div>
            <div class="hd-field-value hd-field-value--line"></div>
        </div>
        <div class="hd-field-row">
            <div class="hd-field-label">Student Number</div>
            <div class="hd-field-value hd-field-value--line hd-value">{{ $studentNo }}</div>
        </div>
        <div class="hd-field-row">
            <div class="hd-field-label">Program</div>
            <div class="hd-field-value hd-field-value--line hd-value">{{ $programLabel }}</div>
        </div>
        <div class="hd-field-row">
            <div class="hd-field-label">School Requesting</div>
            <div class="hd-field-value hd-field-value--box"></div>
        </div>
        <div class="hd-field-row">
            <div class="hd-field-label">Mailing Address</div>
            <div class="hd-field-value hd-field-value--box"></div>
        </div>
        <div class="hd-field-row">
            <div class="hd-field-label">School Contact Nos.</div>
            <div class="hd-field-value hd-field-value--box"></div>
        </div>
    </div>

    <div class="hd-footer-row">
        <div class="hd-copy-options">
            <div>(&nbsp;&nbsp;&nbsp;) Mail</div>
            <div>(&nbsp;&nbsp;&nbsp;) Entrust to bearer</div>
        </div>
        <div class="hd-seal-note">Not Valid Without University Seal</div>
    </div>
</div>
