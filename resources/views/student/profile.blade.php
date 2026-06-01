@extends('layouts.student')

@section('title', 'PLP - Profile')
@section('page-title', 'PROFILE')

@section('body-class', 'page-profile-edit')

@section('content')
<div class="profile-page">
@php
    $s    = $profile ?? null;
    $devs = ($s && $s->devices)     ? (array)json_decode($s->devices, true)     : [];
    $lmsR = ($s && $s->lms_reasons) ? (array)json_decode($s->lms_reasons, true) : [];
@endphp

@if(session('success'))
<div class="profile-save-banner" id="profileSaveBanner">
    <span>{{ session('success') }}</span>
    <button type="button" onclick="this.closest('.profile-save-banner').remove()">&#x2715;</button>
</div>
@endif

@if($errors->any())
<div class="profile-save-banner profile-error-banner" id="profileErrorBanner">
    <span>Please fix the following: {{ implode(' | ', $errors->all()) }}</span>
    <button type="button" onclick="this.closest('.profile-save-banner').remove()">&#x2715;</button>
</div>
@endif

    <form id="studentProfileForm" method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data" novalidate>
        @csrf

    {{-- ===== STEP INDICATOR ===== --}}
    <div class="setup-steps">
        <div class="step-item active" data-step="1">
            <div class="step-pill">Step 1</div>
        </div>
        <div class="step-line"></div>
        <div class="step-item" data-step="2">
            <div class="step-pill">Step 2</div>
        </div>
        <div class="step-line"></div>
        <div class="step-item" data-step="3">
            <div class="step-pill">Step 3</div>
        </div>
        <div class="step-line"></div>
        <div class="step-item" data-step="4">
            <div class="step-pill">Step 4</div>
        </div>
    </div>

    {{-- ===== STEP 1: PERSONAL INFORMATION ===== --}}
    <div class="setup-form-container step-panel" id="step-1">

        {{-- Personal Information --}}
        <div class="setup-section">
            <div class="setup-personal-top">
                <div class="setup-personal-left">
                    <div class="setup-section-header">
                        <h3 class="setup-section-title">Personal Information</h3>
                    </div>

                    <div class="setup-row">
                        <div class="setup-col" style="flex: 0 0 280px;">
                            <label class="setup-label">Student Number</label>
                            <input type="text" class="setup-input" placeholder="Student No." name="student_number">
                        </div>
                    </div>
                </div>

                {{-- Profile Picture --}}
                <div class="setup-profile-photo">
                    <div class="profile-photo-square" id="profilePhotoPreview">
                        <svg class="profile-photo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="12" cy="10" r="3"/>
                            <path d="M6 21v-1a6 6 0 0 1 12 0v1"/>
                        </svg>
                        <img id="profilePhotoImg" src="" alt="" style="display:none;">
                    </div>
                    <label class="profile-photo-btn" for="profilePhotoInput">Upload Photo</label>
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Lastname</label>
                    <input type="text" class="setup-input" placeholder="Last Name" name="last_name">
                </div>
                <div class="setup-col">
                    <label class="setup-label">First Name</label>
                    <input type="text" class="setup-input" placeholder="First Name" name="first_name">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Middle Name</label>
                    <input type="text" class="setup-input" placeholder="Middle Name" name="middle_name">
                </div>
                <div class="setup-col setup-col-sm">
                    <label class="setup-label">Suffix</label>
                    <input type="text" class="setup-input" placeholder="Suffix" name="suffix">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Nickname</label>
                    <input type="text" class="setup-input" placeholder="Nickname" name="nickname">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Gender</label>
                    <div class="setup-radio-group">
                        <label class="setup-radio"><input type="radio" name="gender" value="Male"> Male</label>
                        <label class="setup-radio"><input type="radio" name="gender" value="Female"> Female</label>
                    </div>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Nationality</label>
                    <select class="setup-input setup-select" name="nationality" id="nationalitySelect">
                        <option value="" disabled selected>Nationality</option>
                        <option value="Filipino">Filipino</option>
                        <option value="Other">Other</option>
                    </select>
                    <div id="nationalityOtherWrap" class="religion-input-wrap" style="display:none;">
                        <input type="text" class="setup-input" id="nationalityOtherInput" name="nationality_other" placeholder="Specify your nationality">
                        <button type="button" id="nationalityBackBtn" class="religion-back-arrow" title="Back to list">&#8592;</button>
                    </div>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Religion</label>
                    <select class="setup-input setup-select" name="religion" id="religionSelect">
                        <option value="" disabled selected>Religion</option>
                        <option value="Roman Catholic">Roman Catholic</option>
                        <option value="Islam">Islam</option>
                        <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                        <option value="Born Again">Born Again</option>
                        <option value="Adventist">Adventist</option>
                        <option value="Other">Other</option>
                    </select>
                    <div id="religionOtherWrap" class="religion-input-wrap" style="display:none;">
                        <input type="text" class="setup-input" id="religionOtherInput" name="religion_other" placeholder="Specify your religion">
                        <button type="button" id="religionBackBtn" class="religion-back-arrow" title="Back to list">&#8592;</button>
                    </div>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Date of Birth</label>
                    <input type="date" class="setup-input" name="date_of_birth" id="dobField">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Place of Birth</label>
                    <input type="text" class="setup-input" placeholder="Place of Birth" name="place_of_birth">
                </div>
                <div class="setup-col setup-col-sm">
                    <label class="setup-label">Age</label>
                    <input type="text" class="setup-input" placeholder="Auto" name="age" id="ageField" readonly tabindex="-1">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Civil Status</label>
                    <select class="setup-input setup-select" name="civil_status">
                        <option value="" disabled selected>Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Mobile Number</label>
                    <input type="text" class="setup-input" placeholder="09XXXXXXXXX" name="mobile_number" id="mobileField" maxlength="11" inputmode="numeric">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Email Address</label>
                    <input type="email" class="setup-input" placeholder="Email Address" name="student_email">
                </div>
            </div>
        </div>

        {{-- Residence Information --}}
        <div class="setup-section">
            <div class="setup-section-header">
                <h3 class="setup-section-title">Residence Information</h3>
            </div>

            {{-- Present Address --}}
            <h4 class="setup-subsection-title">Present Address</h4>

            <div class="setup-row">
                <div class="setup-col" style="flex:2;">
                    <label class="setup-label">Street</label>
                    <input type="text" class="setup-input" placeholder="Street" name="present_street">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Barangay</label>
                    <input type="text" class="setup-input" placeholder="Barangay" name="present_barangay">
                </div>
                <div class="setup-col setup-col-sm">
                    <label class="setup-label">Zipcode</label>
                    <input type="text" class="setup-input" placeholder="Zipcode" name="present_zipcode" maxlength="4" inputmode="numeric">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Region</label>
                    <select class="setup-input setup-select" name="present_region">
                        <option value="" disabled selected>Choose Region</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Province</label>
                    <select class="setup-input setup-select" name="present_province" disabled>
                        <option value="" disabled selected>Choose Province</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Municipality/City</label>
                    <select class="setup-input setup-select" name="present_municipality" disabled>
                        <option value="" disabled selected>Choose City/Municipality</option>
                    </select>
                </div>
            </div>

            {{-- Permanent Address --}}
            <div class="setup-same-address">
                <label class="setup-checkbox-label">
                    <input type="checkbox" id="sameAsPresent" name="same_as_present"> Same as Present Address
                </label>
            </div>

            <h4 class="setup-subsection-title">Permanent Address</h4>

            <div class="setup-row">
                <div class="setup-col" style="flex:2;">
                    <label class="setup-label">Street</label>
                    <input type="text" class="setup-input" placeholder="Street" name="permanent_street">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Barangay</label>
                    <input type="text" class="setup-input" placeholder="Barangay" name="permanent_barangay">
                </div>
                <div class="setup-col setup-col-sm">
                    <label class="setup-label">Zipcode</label>
                    <input type="text" class="setup-input" placeholder="Zipcode" name="permanent_zipcode" maxlength="4" inputmode="numeric">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Region</label>
                    <select class="setup-input setup-select" name="permanent_region">
                        <option value="" disabled selected>Choose Region</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Province</label>
                    <select class="setup-input setup-select" name="permanent_province" disabled>
                        <option value="" disabled selected>Choose Province</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Municipality/City</label>
                    <select class="setup-input setup-select" name="permanent_municipality" disabled>
                        <option value="" disabled selected>Choose City/Municipality</option>
                    </select>
                </div>
            </div>

            {{-- Toggle Questions --}}
            <div class="setup-toggles">
                <div class="setup-toggle-row">
                    <div class="setup-toggle-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_orphan">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-text">Are you an <strong>ORPHAN?</strong></span>
                    </div>
                    <div class="setup-toggle-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_first_gen">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-text">Are you the <strong>FIRST GENERATION</strong> in your family to pursue higher education?</span>
                    </div>
                </div>
                <div class="setup-toggle-row">
                    <div class="setup-toggle-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_4ps">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-text">Are you a beneficiary of the <strong>4Ps Program</strong>?</span>
                    </div>
                    <div class="setup-toggle-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="has_disability">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-text">Do you have any <strong>DISABILITY</strong> or <strong>CONDITION</strong> that would make it difficult for you to take a regular test?</span>
                    </div>
                </div>
                <div class="setup-toggle-row">
                    <div class="setup-toggle-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_foreign">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-text">I am a <strong>Foreign Student</strong></span>
                    </div>
                </div>
            </div>

            {{-- Address Preview --}}
            <div class="setup-address-preview">
                <h4 class="address-preview-title">ADDRESS PREVIEW</h4>
                <div class="address-preview-box">
                    <p class="address-preview-text is-empty" id="addressPreviewText">Fill in the address fields above to see a preview...</p>
                    <p class="address-preview-format">Format: Street, Barangay, Municipality/City, Province, Region</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="setup-nav">
            <div></div>
            <button type="button" class="btn-setup-next" onclick="goToStep2FromStep1()">Next</button>
        </div>
    </div>

    {{-- ===== STEP 2: FAMILY/GUARDIAN INFORMATION ===== --}}
    <div class="setup-form-container step-panel" id="step-2" style="display:none;">
        <div class="setup-section">
            <div class="setup-section-header">
                <h3 class="setup-section-title">Family/Guardian Information</h3>
            </div>

            {{-- Mother's Information --}}
            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Mother's Firstname <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="First Name" name="mother_firstname">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Mother's Middlename <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Middle Name" name="mother_middlename">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Mother's Lastname <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Last Name" name="mother_lastname">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Mother's Contact Number</label>
                    <input type="text" class="setup-input" placeholder="09XXXXXXXXX" name="mother_contact" maxlength="11" inputmode="numeric">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Mother's Occupation <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Occupation" name="mother_occupation">
                </div>
                <div class="setup-col setup-col-toggle">
                    <div class="setup-toggle-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="mother_pensioner">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-text">If retiree, is your mother a pensioner?</span>
                    </div>
                </div>
            </div>

            {{-- Father's Information --}}
            <div class="setup-row" style="margin-top: 28px;">
                <div class="setup-col">
                    <label class="setup-label">Father's Firstname <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="First Name" name="father_firstname">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Father's Middlename <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Middle Name" name="father_middlename">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Father's Lastname <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Last Name" name="father_lastname">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Father's Contact Number</label>
                    <input type="text" class="setup-input" placeholder="09XXXXXXXXX" name="father_contact" maxlength="11" inputmode="numeric">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Father's Occupation <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Occupation" name="father_occupation">
                </div>
                <div class="setup-col setup-col-toggle">
                    <div class="setup-toggle-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="father_pensioner">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-text">If retiree, is your father a pensioner?</span>
                    </div>
                </div>
            </div>

            {{-- Guardian's Information --}}
            <div class="setup-row" style="margin-top: 28px;">
                <div class="setup-col">
                    <label class="setup-label">Guardian's Firstname <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="First Name" name="guardian_firstname">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Guardian's Middlename <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Middle Name" name="guardian_middlename">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Guardian's Lastname <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Last Name" name="guardian_lastname">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Guardian's Contact Number <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="09XXXXXXXXX" name="guardian_contact" maxlength="11" inputmode="numeric">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Guardian's Occupation <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Occupation" name="guardian_occupation">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">Guardian's Address <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Complete Address" name="guardian_address">
                    <span class="setup-helper">House Number, Street, Barangay, Municipality/City, and Province</span>
                </div>
            </div>

            {{-- Household Details --}}
            <div class="setup-row" style="margin-top: 28px;">
                <div class="setup-col">
                    <label class="setup-label">Parent Marital Status <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="parent_marital_status">
                        <option value="" disabled selected>Select Parent Marital Status</option>
                        <option value="Married">Married</option>
                        <option value="Living Together">Living Together</option>
                        <option value="Separated">Separated</option>
                        <option value="Widow/Widower">Widow/Widower</option>
                        <option value="Single Parent">Single Parent</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Monthly Family Income <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="monthly_family_income">
                        <option value="" disabled selected>Select Monthly Family Income</option>
                        <option value="Below 10,000">Below ₱10,000</option>
                        <option value="10,000 - 20,000">₱10,000 - ₱20,000</option>
                        <option value="20,001 - 40,000">₱20,001 - ₱40,000</option>
                        <option value="40,001 - 60,000">₱40,001 - ₱60,000</option>
                        <option value="60,001 - 100,000">₱60,001 - ₱100,000</option>
                        <option value="Above 100,000">Above ₱100,000</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Number of Siblings <span class="req">*</span></label>
                    <input type="number" class="setup-input" placeholder="Number Of Siblings" name="number_of_siblings" min="0">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Number of Household Members <span class="req">*</span></label>
                    <input type="number" class="setup-input" placeholder="Household Members" name="household_members" min="1">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Number of Dependents <span class="req">*</span></label>
                    <input type="number" class="setup-input" placeholder="Dependents" name="dependents" min="0">
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="setup-nav">
            <button type="button" class="btn-setup-prev" onclick="goToStep(1)">Previous</button>
            <button type="button" class="btn-setup-next" onclick="goToStep3FromStep2()">Next</button>
        </div>
    </div>

    {{-- ===== STEP 3: EDUCATIONAL INFORMATION ===== --}}
    <div class="setup-form-container step-panel" id="step-3" style="display:none;">
        <div class="setup-section">
            <div class="setup-section-header">
                <h3 class="setup-section-title">Educational Information</h3>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">Elementary</label>
                    <input type="text" class="setup-input" placeholder="Elementary school" name="elementary_school">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">High School</label>
                    <input type="text" class="setup-input" placeholder="High school" name="high_school">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">Junior School <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Junior High or N/A" name="junior_school">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">Senior School <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Senior High or N/A" name="senior_school">
                    <span class="setup-helper">If not applicable, enter N/A.</span>
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">SHS Track Strand <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="SHS Strand" name="shs_track_strand">
                </div>
            </div>

            <div class="setup-toggle-item" style="margin: 20px 0 28px;">
                <label class="toggle-switch">
                    <input type="checkbox" name="no_k12">
                    <span class="toggle-slider"></span>
                </label>
                <span class="toggle-text" style="flex:1;">Select this toggle if the student did not go through the K-12 Basic Education Curriculum implemented starting 2012 (e.g. old curriculum graduates, foreign students, ALS completers without LRN or students educated prior to LRN implementation). For more information, see <a href="#" style="color:#006837; text-decoration:underline;">DepEd Order No. 22, s. 2012</a>.</span>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:0 0 50%;">
                    <label class="setup-label">Learner's Reference Number (LRN)</label>
                    <input type="text" class="setup-input" placeholder="Must be exactly 12 digits (e.g., 123456789012)" name="lrn" maxlength="12">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">School Last Attended</label>
                    <input type="text" class="setup-input" placeholder="For transferee; enter N/A if not applicable" name="school_last_attended">
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="setup-nav">
            <button type="button" class="btn-setup-prev" onclick="goToStep(2)">Previous</button>
            <button type="button" class="btn-setup-next" onclick="goToStep4FromStep3()">Next</button>
        </div>
    </div>

    {{-- ===== STEP 4: OTHER INFORMATION ===== --}}
    <div class="setup-form-container step-panel" id="step-4" style="display:none;">
        <div class="setup-section">
            <div class="setup-section-header">
                <h3 class="setup-section-title">Other Information</h3>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">What is your family main source of income? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="family_income_source" id="incomeSourceSelect">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Employment/Salary">Employment/Salary</option>
                        <option value="Business">Business</option>
                        <option value="Farming/Fishing">Farming/Fishing</option>
                        <option value="Remittance">Remittance</option>
                        <option value="Pension">Pension</option>
                        <option value="Others">Others</option>
                    </select>
                    <div id="incomeSourceOtherWrap" class="religion-input-wrap" style="display:none;">
                        <input type="text" class="setup-input" id="incomeSourceOtherInput" name="family_income_source_other" placeholder="Specify income source">
                        <button type="button" id="incomeSourceBackBtn" class="religion-back-arrow" title="Back to list">&#8592;</button>
                    </div>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Where do you primarily live while attending college? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="living_situation" id="livingSituationSelect">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Family Home">Family Home</option>
                        <option value="Boarding House">Boarding House</option>
                        <option value="Dormitory">Dormitory</option>
                        <option value="Relative's House">Relative's House</option>
                        <option value="Others">Others</option>
                    </select>
                    <div id="livingSituationOtherWrap" class="religion-input-wrap" style="display:none;">
                        <input type="text" class="setup-input" id="livingSituationOtherInput" name="living_situation_other" placeholder="Specify living situation">
                        <button type="button" id="livingSituationBackBtn" class="religion-back-arrow" title="Back to list">&#8592;</button>
                    </div>
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Are you currently a working student? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="working_student">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Do you receive any scholarship/financial assistance? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="has_scholarship">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:0 0 50%;">
                    <label class="setup-label">Are you the first person in your immediate family to attend college at PLP? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="first_in_family_college">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>

            <hr class="setup-divider">

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Do you have reliable access to the internet at home or a location you can use for classes? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="internet_access">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                        <option value="Sometimes">Sometimes</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Do you have access to the necessary IT tools for the online classes? (e.g., webcam, microphone, headphones) <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="it_tools_access">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                        <option value="Some">Some</option>
                    </select>
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">What services do you primarily use for online learning? (Select all that apply) <span class="req">*</span></label>
                    <div class="setup-checkbox-group">
                        <label class="setup-checkbox"><input type="checkbox" name="devices[]" value="Smartphone"> Smartphone</label>
                        <label class="setup-checkbox"><input type="checkbox" name="devices[]" value="Tablet"> Tablet</label>
                        <label class="setup-checkbox"><input type="checkbox" name="devices[]" value="Laptop/Notebook Computer"> Laptop/ Notebook Computer</label>
                        <label class="setup-checkbox"><input type="checkbox" name="devices[]" value="Desktop Computer"> Desktop Computer</label>
                        <label class="setup-checkbox"><input type="checkbox" name="devices[]" value="Others"> Others</label>
                        <input type="text" class="setup-input setup-input-inline" placeholder="Others (Please Specify)" name="devices_other" style="display:none;">
                    </div>
                </div>
            </div>

            <hr class="setup-divider">

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Which learning management system is most frequently used in your classes? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="lms_used" id="lmsUsedSelect">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Google Classroom">Google Classroom</option>
                        <option value="Moodle">Moodle</option>
                        <option value="Canvas">Canvas</option>
                        <option value="Microsoft Teams">Microsoft Teams</option>
                        <option value="Others">Others</option>
                    </select>
                    <div id="lmsUsedOtherWrap" class="religion-input-wrap" style="display:none;">
                        <input type="text" class="setup-input" id="lmsUsedOtherInput" name="lms_used_other" placeholder="Specify LMS">
                        <button type="button" id="lmsUsedBackBtn" class="religion-back-arrow" title="Back to list">&#8592;</button>
                    </div>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Which LMS do you prefer to use for your classes? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="lms_preferred" id="lmsPreferredSelect">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Google Classroom">Google Classroom</option>
                        <option value="Moodle">Moodle</option>
                        <option value="Canvas">Canvas</option>
                        <option value="Microsoft Teams">Microsoft Teams</option>
                        <option value="Others">Others</option>
                    </select>
                    <div id="lmsPreferredOtherWrap" class="religion-input-wrap" style="display:none;">
                        <input type="text" class="setup-input" id="lmsPreferredOtherInput" name="lms_preferred_other" placeholder="Specify LMS">
                        <button type="button" id="lmsPreferredBackBtn" class="religion-back-arrow" title="Back to list">&#8592;</button>
                    </div>
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">What are your reasons for preferring this LMS? Check all that apply <span class="req">*</span></label>
                    <div class="setup-checkbox-group">
                        <label class="setup-checkbox"><input type="checkbox" name="lms_reasons[]" value="Reliable"> Reliable</label>
                        <label class="setup-checkbox"><input type="checkbox" name="lms_reasons[]" value="User Friendly"> User Friendly</label>
                        <label class="setup-checkbox"><input type="checkbox" name="lms_reasons[]" value="Better Communication Feature"> Better Communication Feature</label>
                        <label class="setup-checkbox"><input type="checkbox" name="lms_reasons[]" value="Accessible using mobile devices"> Accessible using mobile devices</label>
                        <label class="setup-checkbox"><input type="checkbox" name="lms_reasons[]" value="Fast Feedback on Assessments"> Fast Feedback on Assessments</label>
                        <label class="setup-checkbox"><input type="checkbox" name="lms_reasons[]" value="Others"> Others</label>
                        <input type="text" class="setup-input setup-input-inline" placeholder="Other (Please Specify)" name="lms_reasons_other" style="display:none;">
                    </div>
                </div>
            </div>

            <hr class="setup-divider">

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">What time of the day do you prefer most for attending classes? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="preferred_class_time">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Morning (7AM-12PM)">Morning (7AM-12PM)</option>
                        <option value="Afternoon (12PM-5PM)">Afternoon (12PM-5PM)</option>
                        <option value="Evening (5PM-9PM)">Evening (5PM-9PM)</option>
                        <option value="No Preference">No Preference</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Would you prefer to take evening classes/night classes if they fit your schedule? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="evening_classes">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                        <option value="Maybe">Maybe</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="setup-nav">
            <button type="button" class="btn-setup-prev" onclick="goToStep(3)">Previous</button>
            <button type="submit" class="btn-setup-submit">Update Profile</button>
        </div>
    </div>

    {{-- File input lives outside all step panels so it is always included in the form submission --}}
    <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" style="display:none;">

    </form>

</div>
@endsection

@push('scripts')
@php
    // Build window.savedProfile — all form field keys used by restoreDraft()
    $sp_devs = ($s && $s->devices)     ? (array)json_decode($s->devices, true)     : [];
    $sp_lmsR = ($s && $s->lms_reasons) ? (array)json_decode($s->lms_reasons, true) : [];
@endphp
<script>
window.savedProfile = {
    "student_number":         {{ json_encode($s ? $s->student_no       : '') }},
    "last_name":              {{ json_encode($s ? $s->last_name         : '') }},
    "first_name":             {{ json_encode($s ? $s->first_name        : '') }},
    "middle_name":            {{ json_encode($s ? $s->middle_name       : '') }},
    "suffix":                 {{ json_encode($s ? $s->suffix            : '') }},
    "nickname":               {{ json_encode($s ? $s->nickname          : '') }},
    "nationality":            {{ json_encode($s ? $s->nationality       : '') }},
    "nationality_other":      {{ json_encode($s ? $s->nationality_other : '') }},
    "religion":               {{ json_encode($s ? $s->religion          : '') }},
    "religion_other":         {{ json_encode($s ? $s->religion_other    : '') }},
    "date_of_birth":          {{ json_encode($s && $s->date_of_birth ? $s->date_of_birth->format('Y-m-d') : '') }},
    "place_of_birth":         {{ json_encode($s ? $s->place_of_birth   : '') }},
    "civil_status":           {{ json_encode($s ? $s->civil_status      : '') }},
    "mobile_number":          {{ json_encode($s ? $s->mobile_number     : '') }},
    "student_email":          {{ json_encode($s ? $s->student_email     : '') }},

    "present_street":         {{ json_encode($s ? $s->present_street       : '') }},
    "present_barangay":       {{ json_encode($s ? $s->present_barangay     : '') }},
    "present_zipcode":        {{ json_encode($s ? $s->present_zipcode      : '') }},
    "present_region":         {{ json_encode($s ? $s->present_region       : '') }},
    "present_province":       {{ json_encode($s ? $s->present_province     : '') }},
    "present_municipality":   {{ json_encode($s ? $s->present_municipality : '') }},

    "permanent_street":       {{ json_encode($s ? $s->permanent_street       : '') }},
    "permanent_barangay":     {{ json_encode($s ? $s->permanent_barangay     : '') }},
    "permanent_zipcode":      {{ json_encode($s ? $s->permanent_zipcode      : '') }},
    "permanent_region":       {{ json_encode($s ? $s->permanent_region       : '') }},
    "permanent_province":     {{ json_encode($s ? $s->permanent_province     : '') }},
    "permanent_municipality": {{ json_encode($s ? $s->permanent_municipality : '') }},

    "mother_firstname":       {{ json_encode($s ? $s->mother_firstname  : '') }},
    "mother_middlename":      {{ json_encode($s ? $s->mother_middlename : '') }},
    "mother_lastname":        {{ json_encode($s ? $s->mother_lastname   : '') }},
    "mother_contact":         {{ json_encode($s ? $s->mother_contact    : '') }},
    "mother_occupation":      {{ json_encode($s ? $s->mother_occupation : '') }},
    "father_firstname":       {{ json_encode($s ? $s->father_firstname  : '') }},
    "father_middlename":      {{ json_encode($s ? $s->father_middlename : '') }},
    "father_lastname":        {{ json_encode($s ? $s->father_lastname   : '') }},
    "father_contact":         {{ json_encode($s ? $s->father_contact    : '') }},
    "father_occupation":      {{ json_encode($s ? $s->father_occupation : '') }},
    "guardian_firstname":     {{ json_encode($s ? $s->guardian_firstname  : '') }},
    "guardian_middlename":    {{ json_encode($s ? $s->guardian_middlename : '') }},
    "guardian_lastname":      {{ json_encode($s ? $s->guardian_lastname   : '') }},
    "guardian_contact":       {{ json_encode($s ? $s->guardian_contact    : '') }},
    "guardian_occupation":    {{ json_encode($s ? $s->guardian_occupation : '') }},
    "guardian_address":       {{ json_encode($s ? $s->guardian_address    : '') }},
    "parent_marital_status":  {{ json_encode($s ? $s->parent_marital_status : '') }},
    "monthly_family_income":  {{ json_encode($s ? $s->monthly_family_income : '') }},
    "number_of_siblings":     {{ json_encode($s ? (string)$s->number_of_siblings : '') }},
    "household_members":      {{ json_encode($s ? (string)$s->household_members  : '') }},
    "dependents":             {{ json_encode($s ? (string)$s->dependents          : '') }},

    "elementary_school":      {{ json_encode($s ? $s->elementary_school      : '') }},
    "high_school":            {{ json_encode($s ? $s->high_school            : '') }},
    "junior_school":          {{ json_encode($s ? $s->junior_school          : '') }},
    "senior_school":          {{ json_encode($s ? $s->senior_school          : '') }},
    "shs_track_strand":       {{ json_encode($s ? $s->shs_track_strand       : '') }},
    "lrn":                    {{ json_encode($s ? $s->lrn                    : '') }},
    "school_last_attended":   {{ json_encode($s ? $s->school_last_attended   : '') }},

    "family_income_source":       {{ json_encode($s ? $s->family_income_source       : '') }},
    "family_income_source_other": {{ json_encode($s ? $s->family_income_source_other : '') }},
    "living_situation":           {{ json_encode($s ? $s->living_situation           : '') }},
    "living_situation_other":     {{ json_encode($s ? $s->living_situation_other     : '') }},
    "working_student":            {{ json_encode($s ? $s->working_student            : '') }},
    "has_scholarship":            {{ json_encode($s ? $s->has_scholarship            : '') }},
    "first_in_family_college":    {{ json_encode($s ? $s->first_in_family_college    : '') }},
    "internet_access":            {{ json_encode($s ? $s->internet_access            : '') }},
    "it_tools_access":            {{ json_encode($s ? $s->it_tools_access            : '') }},
    "devices_other":              {{ json_encode($s ? $s->devices_other              : '') }},
    "lms_used":                   {{ json_encode($s ? $s->lms_used                   : '') }},
    "lms_used_other":             {{ json_encode($s ? $s->lms_used_other             : '') }},
    "lms_preferred":              {{ json_encode($s ? $s->lms_preferred              : '') }},
    "lms_preferred_other":        {{ json_encode($s ? $s->lms_preferred_other        : '') }},
    "lms_reasons_other":          {{ json_encode($s ? $s->lms_reasons_other          : '') }},
    "preferred_class_time":       {{ json_encode($s ? $s->preferred_class_time       : '') }},
    "evening_classes":            {{ json_encode($s ? $s->evening_classes            : '') }},

    {{-- Boolean toggles: key format is name||on (default checkbox value) --}}
    "same_as_present||on":  {{ json_encode($s ? (bool)$s->same_as_present : false) }},
    "is_orphan||on":        {{ json_encode($s ? (bool)$s->is_orphan       : false) }},
    "is_first_gen||on":     {{ json_encode($s ? (bool)$s->is_first_gen    : false) }},
    "is_4ps||on":           {{ json_encode($s ? (bool)$s->is_4ps          : false) }},
    "has_disability||on":   {{ json_encode($s ? (bool)$s->has_disability  : false) }},
    "is_foreign||on":       {{ json_encode($s ? (bool)$s->is_foreign      : false) }},
    "mother_pensioner||on": {{ json_encode($s ? (bool)$s->mother_pensioner : false) }},
    "father_pensioner||on": {{ json_encode($s ? (bool)$s->father_pensioner : false) }},
    "no_k12||on":           {{ json_encode($s ? (bool)$s->no_k12          : false) }},

    {{-- Radio buttons --}}
    "gender||Male":   {{ json_encode($s ? $s->gender === 'Male'   : false) }},
    "gender||Female": {{ json_encode($s ? $s->gender === 'Female' : false) }},

    {{-- Devices checkboxes --}}
    "devices[]||Smartphone":              {{ json_encode(in_array('Smartphone',               $sp_devs)) }},
    "devices[]||Tablet":                  {{ json_encode(in_array('Tablet',                   $sp_devs)) }},
    "devices[]||Laptop/Notebook Computer":{{ json_encode(in_array('Laptop/Notebook Computer', $sp_devs)) }},
    "devices[]||Desktop Computer":        {{ json_encode(in_array('Desktop Computer',          $sp_devs)) }},
    "devices[]||Others":                  {{ json_encode(in_array('Others',                   $sp_devs)) }},

    {{-- LMS Reasons checkboxes --}}
    "lms_reasons[]||Reliable":                      {{ json_encode(in_array('Reliable',                      $sp_lmsR)) }},
    "lms_reasons[]||User Friendly":                 {{ json_encode(in_array('User Friendly',                 $sp_lmsR)) }},
    "lms_reasons[]||Better Communication Feature":  {{ json_encode(in_array('Better Communication Feature',  $sp_lmsR)) }},
    "lms_reasons[]||Accessible using mobile devices": {{ json_encode(in_array('Accessible using mobile devices', $sp_lmsR)) }},
    "lms_reasons[]||Fast Feedback on Assessments":  {{ json_encode(in_array('Fast Feedback on Assessments',  $sp_lmsR)) }},
    "lms_reasons[]||Others":                        {{ json_encode(in_array('Others',                        $sp_lmsR)) }},

    {{-- Other-swap visibility flags --}}
    "__religionOther":       {{ json_encode($s && $s->religion          === 'Other')  }},
    "__nationalityOther":    {{ json_encode($s && $s->nationality       === 'Other')  }},
    "__incomeSourceOther":   {{ json_encode($s && $s->family_income_source   === 'Others') }},
    "__livingSituationOther":{{ json_encode($s && $s->living_situation        === 'Others') }},
    "__lmsUsedOther":        {{ json_encode($s && $s->lms_used               === 'Others') }},
    "__lmsPreferredOther":   {{ json_encode($s && $s->lms_preferred           === 'Others') }},

    {{-- Profile photo URL from DB --}}
    "__profilePhotoUrl": {{ json_encode($s && $s->profile_photo_path ? asset('storage/' . $s->profile_photo_path) : '') }}
};
@if(session('success'))
// Clear localStorage draft after successful save
try { localStorage.removeItem('plp_profile_draft'); } catch(e) {}
@endif
</script>
<script src="{{ asset('js/student-profile.js') }}?v={{ time() }}"></script>
@if($errors->any())
<script>window.profileErrorKeys = @json(array_keys($errors->messages()));</script>
<script src="{{ asset('js/student-profile-errors.js') }}"></script>
@endif
@endpush
