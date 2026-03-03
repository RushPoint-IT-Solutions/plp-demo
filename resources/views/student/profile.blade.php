@extends('layouts.student')

@section('title', 'PLP - Profile')
@section('page-title', 'PROFILE')

@push('styles')
<style>
    /* Make footer scroll with content on profile page (not sticky) */
    .student-main-wrapper {
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    .student-content {
        overflow: visible !important;
        flex: none !important;
    }
</style>
@endpush

@section('content')
<div class="student-page-container">

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
                    <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" style="display:none;">
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
                    <select class="setup-input setup-select" name="nationality">
                        <option value="" disabled selected>Nationality</option>
                        <option value="Filipino">Filipino</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Religion</label>
                    <select class="setup-input setup-select" name="religion">
                        <option value="" disabled selected>Religion</option>
                        <option value="Roman Catholic">Roman Catholic</option>
                        <option value="Islam">Islam</option>
                        <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                        <option value="Born Again">Born Again</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Date of Birth</label>
                    <input type="date" class="setup-input" name="date_of_birth" placeholder="YYYY-MM-DD">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Place of Birth</label>
                    <input type="text" class="setup-input" placeholder="Place of Birth" name="place_of_birth">
                </div>
                <div class="setup-col setup-col-sm">
                    <label class="setup-label">Age</label>
                    <input type="text" class="setup-input" placeholder="Age" name="age" readonly>
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
                    <input type="text" class="setup-input" placeholder="Mobile Number" name="mobile_number">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Email Address</label>
                    <input type="email" class="setup-input" placeholder="Email Address" name="email">
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
                    <input type="text" class="setup-input" placeholder="Zipcode" name="present_zipcode">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Municipality/City</label>
                    <select class="setup-input setup-select" name="present_municipality">
                        <option value="" disabled selected>Choose Municipality</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Province</label>
                    <select class="setup-input setup-select" name="present_province">
                        <option value="" disabled selected>Choose Province</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Region</label>
                    <select class="setup-input setup-select" name="present_region">
                        <option value="" disabled selected>Choose Region</option>
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
                    <input type="text" class="setup-input" placeholder="Zipcode" name="permanent_zipcode">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Municipality/City</label>
                    <select class="setup-input setup-select" name="permanent_municipality">
                        <option value="" disabled selected>Choose Municipality</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Province</label>
                    <select class="setup-input setup-select" name="permanent_province">
                        <option value="" disabled selected>Choose Province</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Region</label>
                    <select class="setup-input setup-select" name="permanent_region">
                        <option value="" disabled selected>Choose Region</option>
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
                    <p class="address-preview-text" id="addressPreviewText">ESPAÑA BOULEVARD, BARANGAY 710, CITY OF MANILA, METRO MANILA (NCR)</p>
                    <p class="address-preview-format">Format: Street, Barangay, Municipality/City, Province, Region</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="setup-nav">
            <div></div>
            <button type="button" class="btn-setup-next" onclick="goToStep(2)">Next</button>
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
                    <label class="setup-label">Mother's Contact Number <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="+63 9" name="mother_contact">
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
                    <label class="setup-label">Father's Contact Number <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="+63 9" name="father_contact">
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
                    <input type="text" class="setup-input" placeholder="+63 9" name="guardian_contact">
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
            <button type="button" class="btn-setup-next" onclick="goToStep(3)">Next</button>
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
                    <label class="setup-label">Junior School <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Junior High" name="junior_school">
                </div>
            </div>

            <div class="setup-row">
                <div class="setup-col" style="flex:1;">
                    <label class="setup-label">Senior School <span class="req">*</span></label>
                    <input type="text" class="setup-input" placeholder="Senior High" name="senior_school">
                    <span class="setup-helper">If not applicable, use the same information as Junior High School</span>
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
        </div>

        {{-- Navigation --}}
        <div class="setup-nav">
            <button type="button" class="btn-setup-prev" onclick="goToStep(2)">Previous</button>
            <button type="button" class="btn-setup-next" onclick="goToStep(4)">Next</button>
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
                    <select class="setup-input setup-select" name="family_income_source">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Employment/Salary">Employment/Salary</option>
                        <option value="Business">Business</option>
                        <option value="Farming/Fishing">Farming/Fishing</option>
                        <option value="Remittance">Remittance</option>
                        <option value="Pension">Pension</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div class="setup-col">
                    <label class="setup-label">Where do you primarily live while attending college? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="living_situation">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Family Home">Family Home</option>
                        <option value="Boarding House">Boarding House</option>
                        <option value="Dormitory">Dormitory</option>
                        <option value="Relative's House">Relative's House</option>
                        <option value="Others">Others</option>
                    </select>
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
                    <select class="setup-input setup-select" name="scholarship">
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
                        <input type="text" class="setup-input setup-input-inline" placeholder="Others (Please Specify)" name="devices_other">
                    </div>
                </div>
            </div>

            <hr class="setup-divider">

            <div class="setup-row">
                <div class="setup-col">
                    <label class="setup-label">Which learning management system is most frequently used in your classes? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="lms_used">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Google Classroom">Google Classroom</option>
                        <option value="Moodle">Moodle</option>
                        <option value="Canvas">Canvas</option>
                        <option value="Microsoft Teams">Microsoft Teams</option>
                        <option value="Others">Others</option>
                    </select>
                    <input type="text" class="setup-input" placeholder="Other (Please Specify)" name="lms_used_other" style="margin-top:8px;">
                </div>
                <div class="setup-col">
                    <label class="setup-label">Which LMS do you prefer to use for your classes? <span class="req">*</span></label>
                    <select class="setup-input setup-select" name="lms_preferred">
                        <option value="" disabled selected>Select an option</option>
                        <option value="Google Classroom">Google Classroom</option>
                        <option value="Moodle">Moodle</option>
                        <option value="Canvas">Canvas</option>
                        <option value="Microsoft Teams">Microsoft Teams</option>
                        <option value="Others">Others</option>
                    </select>
                    <input type="text" class="setup-input" placeholder="Other (Please Specify)" name="lms_preferred_other" style="margin-top:8px;">
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
                        <input type="text" class="setup-input setup-input-inline" placeholder="Other (Please Specify)" name="lms_reasons_other">
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
            <button type="button" class="btn-setup-submit">Update Profile</button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function goToStep(stepNum) {
        // Hide all panels
        document.querySelectorAll('.step-panel').forEach(function(panel) {
            panel.style.display = 'none';
        });

        // Show target panel
        var target = document.getElementById('step-' + stepNum);
        if (target) target.style.display = 'block';

        // Update step indicators
        document.querySelectorAll('.step-item').forEach(function(item) {
            var itemStep = parseInt(item.getAttribute('data-step'));
            item.classList.remove('active', 'completed');
            if (itemStep === stepNum) {
                item.classList.add('active');
            } else if (itemStep < stepNum) {
                item.classList.add('completed');
            }
        });

        // Update step lines
        var lines = document.querySelectorAll('.step-line');
        lines.forEach(function(line, index) {
            if (index < stepNum - 1) {
                line.classList.add('active');
            } else {
                line.classList.remove('active');
            }
        });

        // Scroll to top of form
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Profile photo preview
    document.getElementById('profilePhotoInput').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                var img = document.getElementById('profilePhotoImg');
                img.src = ev.target.result;
                img.style.display = 'block';
                document.querySelector('.profile-photo-icon').style.display = 'none';
                document.querySelector('.profile-photo-btn').textContent = 'Change Profile Picture';
            };
            reader.readAsDataURL(file);
        }
    });

    // Same as Present Address toggle
    document.getElementById('sameAsPresent').addEventListener('change', function() {
        var fields = ['street', 'barangay', 'zipcode', 'municipality', 'province', 'region'];
        fields.forEach(function(field) {
            var present = document.querySelector('[name="present_' + field + '"]');
            var permanent = document.querySelector('[name="permanent_' + field + '"]');
            if (present && permanent) {
                permanent.value = present.value;
                permanent.disabled = document.getElementById('sameAsPresent').checked;
            }
        });
    });
</script>
@endpush
