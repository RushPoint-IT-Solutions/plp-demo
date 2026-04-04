@extends('layouts.registrar')

@section('title', 'PLP - Student Profile')
@section('page-title', 'STUDENT PROFILE')
@section('body-class', request('view') === 'config' ? 'page-student-profile page-student-profile-config' : (request('view') === 'application' ? 'page-student-profile page-student-profile-app-preview' : 'page-student-profile'))

@php
    $isConfigMode = request('view') === 'config';
    $isApplicationMode = request('view') === 'application';
    $p = $previewProfile ?? null;

    $cfgStudentId = request('student_id', optional($p)->student_no ?? '');
    $cfgCourse = request('course', '');
    $cfgYearLevel = request('year_level', '');
    $cfgLastName = optional($p)->last_name ?? trim(explode(',', request('name', ''))[0] ?? '');
    $cfgFirstName = optional($p)->first_name ?? trim(explode(',', request('name', ''))[1] ?? '');
    $cfgMiddleName = optional($p)->middle_name ?? '';
    $cfgSuffix = optional($p)->suffix ?? '';
    $cfgSex = optional($p)->gender ?? '';
    $cfgContact = optional($p)->mobile_number ?? '';
    $cfgEmail = optional($p)->student_email ?? '';
    $cfgAddress = trim(implode(', ', array_filter([optional($p)->present_street ?? '', optional($p)->present_barangay ?? '', optional($p)->present_municipality ?? '', optional($p)->present_province ?? '', optional($p)->present_region ?? ''])));
    $cfgRegion = optional($p)->present_region ?? '';
    $cfgProvince = optional($p)->present_province ?? '';
    $cfgMunicipality = optional($p)->present_municipality ?? '';
    $cfgBirthDate = $p && $p->date_of_birth ? $p->date_of_birth->format('m/d/Y') : '';
    $cfgBirthPlace = optional($p)->place_of_birth ?? '';
    $cfgCitizenship = optional($p)->nationality ?? '';
    $cfgReligion = optional($p)->religion ?? '';
    $cfgBloodType = '';
    $cfgCivilStatus = optional($p)->civil_status ?? '';
    $cfgSection = '';
    $cfgCurriculumYear = '';
    $cfgAdmissionYear = '';
    $cfgAdmissionStatus = '';
    $cfgEnrollmentStatus = '';
    $cfgAcademicStatus = '';
    $cfgIsTransfer = false;
    $cfgSchoolName = optional($p)->senior_school ?? '';
    $cfgDateTransferred = '';
    $cfgTransferReason = '';

    $cfgStudentName = trim(($cfgLastName ? $cfgLastName . ', ' : '') . $cfgFirstName);
    $cfgDevices = $p && $p->devices ? (array) json_decode($p->devices, true) : [];
    $cfgLmsReasons = $p && $p->lms_reasons ? (array) json_decode($p->lms_reasons, true) : [];

    $cfgPhotoSvg = "<svg xmlns='http://www.w3.org/2000/svg' width='280' height='280' viewBox='0 0 280 280'>"
        . "<defs><linearGradient id='cfgG' x1='0' y1='0' x2='0' y2='1'><stop offset='0%' stop-color='#f2f6f3'/><stop offset='100%' stop-color='#e2e9e4'/></linearGradient></defs>"
        . "<rect width='280' height='280' rx='14' fill='url(%23cfgG)'/>"
        . "<rect x='20' y='20' width='240' height='240' rx='14' fill='none' stroke='#2f7a4f' stroke-width='4'/>"
        . "</svg>";
    $cfgPhotoDataUri = 'data:image/svg+xml;utf8,' . rawurlencode($cfgPhotoSvg);
    if ($p && !empty($p->profile_photo_path)) {
        $cfgPhotoDataUri = asset('storage/' . ltrim($p->profile_photo_path, '/'));
    }
@endphp


@section('content')
@if(!$isConfigMode && !$isApplicationMode)
<div class="pf-page">
    <div class="sp-page">
        <div class="sp-toolbar-row">
            <div class="sp-toolbar">
                <div class="sp-search-wrap">
                    <label class="app-filter-label" for="spSearch">Search</label>
                    <div class="pf-search-wrap">
                        <span class="pf-search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input id="spSearch" type="text" class="pf-search-input" placeholder="Search Student ID / Name...">
                    </div>
                </div>
                <button type="button" class="pf-btn-new" id="spSearchBtn">Search</button>
            </div>
        </div>

        <section>
            <div class="sp-table-head">
                <button type="button" class="pf-btn-new" id="spNewRecordBtn">+ New Record</button>
            </div>

            <div class="app-table-wrap">
                <table id="spTable" class="app-table cfg-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Yr. Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="spTableBody"></tbody>
                </table>
            </div>

            <div class="app-table-pager"></div>
        </section>
    </div>
</div>
@elseif($isApplicationMode)
<div class="pf-page">
    <div class="spc-page">
        <div class="spc-head-row">
            <a href="{{ route('registrar.admin-tools.master-files.student-profile') }}" class="pf-btn-new spc-view-form-btn">Back to Student Profile List</a>
        </div>

        <div class="profile-page">
            <div class="setup-steps">
                <div class="step-item active" data-step="1"><div class="step-pill">Step 1</div></div>
                <div class="step-line"></div>
                <div class="step-item" data-step="2"><div class="step-pill">Step 2</div></div>
                <div class="step-line"></div>
                <div class="step-item" data-step="3"><div class="step-pill">Step 3</div></div>
                <div class="step-line"></div>
                <div class="step-item" data-step="4"><div class="step-pill">Step 4</div></div>
            </div>

            <div class="setup-form-container step-panel" id="step-1">
                <fieldset disabled>
                    <div class="setup-section">
                        <div class="setup-personal-top">
                            <div class="setup-personal-left">
                                <div class="setup-section-header"><h3 class="setup-section-title">Personal Information</h3></div>
                                <div class="setup-row">
                                    <div class="setup-col" style="flex: 0 0 280px;">
                                        <label class="setup-label">Student Number</label>
                                        <input type="text" class="setup-input" value="{{ $cfgStudentId }}">
                                    </div>
                                </div>
                            </div>
                            <div class="setup-profile-photo">
                                <div class="profile-photo-square">
                                    <img src="{{ $cfgPhotoDataUri }}" alt="Student photo" style="width:100%;height:100%;object-fit:cover;display:block;">
                                </div>
                            </div>
                        </div>

                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Lastname</label><input type="text" class="setup-input" value="{{ $cfgLastName }}"></div>
                            <div class="setup-col"><label class="setup-label">First Name</label><input type="text" class="setup-input" value="{{ $cfgFirstName }}"></div>
                            <div class="setup-col"><label class="setup-label">Middle Name</label><input type="text" class="setup-input" value="{{ $cfgMiddleName }}"></div>
                            <div class="setup-col setup-col-sm"><label class="setup-label">Suffix</label><input type="text" class="setup-input" value="{{ $cfgSuffix }}"></div>
                        </div>

                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Gender</label><input type="text" class="setup-input" value="{{ $cfgSex }}"></div>
                            <div class="setup-col"><label class="setup-label">Nationality</label><input type="text" class="setup-input" value="{{ $cfgCitizenship }}"></div>
                            <div class="setup-col"><label class="setup-label">Religion</label><input type="text" class="setup-input" value="{{ $cfgReligion }}"></div>
                            <div class="setup-col"><label class="setup-label">Date of Birth</label><input type="text" class="setup-input" value="{{ $cfgBirthDate }}"></div>
                            <div class="setup-col"><label class="setup-label">Mobile Number</label><input type="text" class="setup-input" value="{{ $cfgContact }}"></div>
                        </div>

                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Nickname</label><input type="text" class="setup-input" value="{{ $p->nickname ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Email Address</label><input type="text" class="setup-input" value="{{ $cfgEmail }}"></div>
                            <div class="setup-col"><label class="setup-label">Place of Birth</label><input type="text" class="setup-input" value="{{ $cfgBirthPlace }}"></div>
                            <div class="setup-col"><label class="setup-label">Civil Status</label><input type="text" class="setup-input" value="{{ $cfgCivilStatus }}"></div>
                        </div>

                        <h4 class="setup-subsection-title">Present Address</h4>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Street</label><input type="text" class="setup-input" value="{{ $p->present_street ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Barangay</label><input type="text" class="setup-input" value="{{ $p->present_barangay ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Zipcode</label><input type="text" class="setup-input" value="{{ $p->present_zipcode ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Region</label><input type="text" class="setup-input" value="{{ $p->present_region ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Province</label><input type="text" class="setup-input" value="{{ $p->present_province ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Municipality/City</label><input type="text" class="setup-input" value="{{ $p->present_municipality ?? '' }}"></div>
                        </div>

                        <h4 class="setup-subsection-title">Permanent Address</h4>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Street</label><input type="text" class="setup-input" value="{{ $p->permanent_street ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Barangay</label><input type="text" class="setup-input" value="{{ $p->permanent_barangay ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Zipcode</label><input type="text" class="setup-input" value="{{ $p->permanent_zipcode ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Region</label><input type="text" class="setup-input" value="{{ $p->permanent_region ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Province</label><input type="text" class="setup-input" value="{{ $p->permanent_province ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Municipality/City</label><input type="text" class="setup-input" value="{{ $p->permanent_municipality ?? '' }}"></div>
                        </div>

                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-checkbox"><input type="checkbox" {{ !empty($p->same_as_present) ? 'checked' : '' }}> Same as Present Address</label></div>
                            <div class="setup-col"><label class="setup-checkbox"><input type="checkbox" {{ !empty($p->is_orphan) ? 'checked' : '' }}> Orphan</label></div>
                            <div class="setup-col"><label class="setup-checkbox"><input type="checkbox" {{ !empty($p->is_first_gen) ? 'checked' : '' }}> First Generation</label></div>
                            <div class="setup-col"><label class="setup-checkbox"><input type="checkbox" {{ !empty($p->is_4ps) ? 'checked' : '' }}> 4Ps Beneficiary</label></div>
                            <div class="setup-col"><label class="setup-checkbox"><input type="checkbox" {{ !empty($p->has_disability) ? 'checked' : '' }}> Has Disability</label></div>
                        </div>
                    </div>
                </fieldset>
                <div class="setup-nav"><div></div><button type="button" class="btn-setup-next" data-go-step="2">Next</button></div>
            </div>

            <div class="setup-form-container step-panel step-hidden" id="step-2">
                <fieldset disabled>
                    <div class="setup-section">
                        <div class="setup-section-header"><h3 class="setup-section-title">Family/Guardian Information</h3></div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Mother's Firstname</label><input type="text" class="setup-input" value="{{ $p->mother_firstname ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Mother's Middlename</label><input type="text" class="setup-input" value="{{ $p->mother_middlename ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Mother's Lastname</label><input type="text" class="setup-input" value="{{ $p->mother_lastname ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Mother's Contact</label><input type="text" class="setup-input" value="{{ $p->mother_contact ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Mother's Occupation</label><input type="text" class="setup-input" value="{{ $p->mother_occupation ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-checkbox"><input type="checkbox" {{ !empty($p->mother_pensioner) ? 'checked' : '' }}> Mother is Pensioner</label></div>
                        </div>

                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Father's Firstname</label><input type="text" class="setup-input" value="{{ $p->father_firstname ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Father's Middlename</label><input type="text" class="setup-input" value="{{ $p->father_middlename ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Father's Lastname</label><input type="text" class="setup-input" value="{{ $p->father_lastname ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Father's Contact</label><input type="text" class="setup-input" value="{{ $p->father_contact ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Father's Occupation</label><input type="text" class="setup-input" value="{{ $p->father_occupation ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-checkbox"><input type="checkbox" {{ !empty($p->father_pensioner) ? 'checked' : '' }}> Father is Pensioner</label></div>
                        </div>

                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Guardian's Firstname</label><input type="text" class="setup-input" value="{{ $p->guardian_firstname ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Guardian's Middlename</label><input type="text" class="setup-input" value="{{ $p->guardian_middlename ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Guardian's Lastname</label><input type="text" class="setup-input" value="{{ $p->guardian_lastname ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Guardian Contact</label><input type="text" class="setup-input" value="{{ $p->guardian_contact ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Guardian Occupation</label><input type="text" class="setup-input" value="{{ $p->guardian_occupation ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Guardian Address</label><input type="text" class="setup-input" value="{{ $p->guardian_address ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Parent Marital Status</label><input type="text" class="setup-input" value="{{ $p->parent_marital_status ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Monthly Family Income</label><input type="text" class="setup-input" value="{{ $p->monthly_family_income ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Number of Siblings</label><input type="text" class="setup-input" value="{{ $p->number_of_siblings ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Household Members</label><input type="text" class="setup-input" value="{{ $p->household_members ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Dependents</label><input type="text" class="setup-input" value="{{ $p->dependents ?? '' }}"></div>
                        </div>
                    </div>
                </fieldset>
                <div class="setup-nav"><button type="button" class="btn-setup-prev" data-go-step="1">Previous</button><button type="button" class="btn-setup-next" data-go-step="3">Next</button></div>
            </div>

            <div class="setup-form-container step-panel step-hidden" id="step-3">
                <fieldset disabled>
                    <div class="setup-section">
                        <div class="setup-section-header"><h3 class="setup-section-title">Educational Information</h3></div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Junior School</label><input type="text" class="setup-input" value="{{ $p->junior_school ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Senior School</label><input type="text" class="setup-input" value="{{ $p->senior_school ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">SHS Track Strand</label><input type="text" class="setup-input" value="{{ $p->shs_track_strand ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">LRN</label><input type="text" class="setup-input" value="{{ $p->lrn ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Program</label><input type="text" class="setup-input" value="{{ $cfgCourse }}"></div>
                            <div class="setup-col"><label class="setup-label">Year Level</label><input type="text" class="setup-input" value="{{ $cfgYearLevel }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-checkbox"><input type="checkbox" {{ !empty($p->no_k12) ? 'checked' : '' }}> Did not go through K-12 curriculum</label></div>
                        </div>
                    </div>
                </fieldset>
                <div class="setup-nav"><button type="button" class="btn-setup-prev" data-go-step="2">Previous</button><button type="button" class="btn-setup-next" data-go-step="4">Next</button></div>
            </div>

            <div class="setup-form-container step-panel step-hidden" id="step-4">
                <fieldset disabled>
                    <div class="setup-section">
                        <div class="setup-section-header"><h3 class="setup-section-title">Other Information</h3></div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Family Income Source</label><input type="text" class="setup-input" value="{{ $p->family_income_source ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Family Income Source (Other)</label><input type="text" class="setup-input" value="{{ $p->family_income_source_other ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Living Situation</label><input type="text" class="setup-input" value="{{ $p->living_situation ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Living Situation (Other)</label><input type="text" class="setup-input" value="{{ $p->living_situation_other ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Working Student</label><input type="text" class="setup-input" value="{{ $p->working_student ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Has Scholarship</label><input type="text" class="setup-input" value="{{ $p->has_scholarship ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">First in Family College</label><input type="text" class="setup-input" value="{{ $p->first_in_family_college ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Internet Access</label><input type="text" class="setup-input" value="{{ $p->internet_access ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">IT Tools Access</label><input type="text" class="setup-input" value="{{ $p->it_tools_access ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">LMS Used</label><input type="text" class="setup-input" value="{{ $p->lms_used ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">LMS Used (Other)</label><input type="text" class="setup-input" value="{{ $p->lms_used_other ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">LMS Preferred</label><input type="text" class="setup-input" value="{{ $p->lms_preferred ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">LMS Preferred (Other)</label><input type="text" class="setup-input" value="{{ $p->lms_preferred_other ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col"><label class="setup-label">Preferred Class Time</label><input type="text" class="setup-input" value="{{ $p->preferred_class_time ?? '' }}"></div>
                            <div class="setup-col"><label class="setup-label">Evening Classes</label><input type="text" class="setup-input" value="{{ $p->evening_classes ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col" style="flex:1;">
                                <label class="setup-label">Devices Used (multiple)</label>
                                <input type="text" class="setup-input" value="{{ implode(', ', $cfgDevices) }}">
                            </div>
                            <div class="setup-col"><label class="setup-label">Devices Other</label><input type="text" class="setup-input" value="{{ $p->devices_other ?? '' }}"></div>
                        </div>
                        <div class="setup-row">
                            <div class="setup-col" style="flex:1;">
                                <label class="setup-label">LMS Reasons (multiple)</label>
                                <input type="text" class="setup-input" value="{{ implode(', ', $cfgLmsReasons) }}">
                            </div>
                            <div class="setup-col"><label class="setup-label">LMS Reasons Other</label><input type="text" class="setup-input" value="{{ $p->lms_reasons_other ?? '' }}"></div>
                        </div>
                    </div>
                </fieldset>
                <div class="setup-nav"><button type="button" class="btn-setup-prev" data-go-step="3">Previous</button><button type="button" class="btn-setup-next" onclick="window.location.href='{{ route('registrar.admin-tools.master-files.student-profile') }}?view=config&student_id={{ urlencode($cfgStudentId) }}&name={{ urlencode($cfgStudentName) }}&course={{ urlencode($cfgCourse) }}&year_level={{ urlencode($cfgYearLevel) }}'">Back to Profile</button></div>
            </div>
        </div>
    </div>
</div>
@else
<div class="pf-page">
    <div class="spc-page">
        <div class="spc-head-row">
            <a href="{{ route('registrar.admin-tools.master-files.student-profile') }}" class="pf-btn-new spc-view-form-btn">Back to Student Profile List</a>
            <a href="{{ route('registrar.admin-tools.master-files.student-profile') }}?view=application&student_id={{ urlencode($cfgStudentId) }}&name={{ urlencode($cfgStudentName) }}&course={{ urlencode($cfgCourse) }}&year_level={{ urlencode($cfgYearLevel) }}" class="pf-btn-new spc-view-form-btn">View Student's Application Form</a>
        </div>

        <section class="cfg-card spc-card spc-layout-lock">
            <div class="spc-grid-top">
                <div class="spc-field">
                    <label class="app-filter-label" for="spcStudentNo">Student No.</label>
                    <input id="spcStudentNo" type="text" class="app-filter-input" value="{{ $cfgStudentId }}">
                </div>
                <div class="spc-field">
                    <label class="app-filter-label" for="spcLastName">Last Name</label>
                    <input id="spcLastName" type="text" class="app-filter-input" value="{{ $cfgLastName }}">
                </div>
                <div class="spc-field">
                    <label class="app-filter-label" for="spcFirstName">First Name</label>
                    <input id="spcFirstName" type="text" class="app-filter-input" value="{{ $cfgFirstName }}">
                </div>
                <div class="spc-field">
                    <label class="app-filter-label" for="spcMiddleName">Middle Name</label>
                    <input id="spcMiddleName" type="text" class="app-filter-input" value="{{ $cfgMiddleName }}">
                </div>
                <div class="spc-field spc-field-sm">
                    <label class="app-filter-label" for="spcSuffix">Suffix</label>
                    <input id="spcSuffix" type="text" class="app-filter-input" value="{{ $cfgSuffix }}">
                </div>
                <label class="spc-photo-box" for="spcPhotoInput" title="Upload student photo">
                    <input id="spcPhotoInput" type="file" accept="image/*" class="spc-photo-input">
                    <div class="spc-photo-icon" id="spcPhotoIcon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h4l2-2h4l2 2h4v12H4z"></path><circle cx="12" cy="13" r="3"></circle></svg>
                    </div>
                    <img id="spcPhotoPreview" class="spc-photo-preview" alt="Student photo preview" src="{{ $cfgPhotoDataUri }}" style="display:block;">
                    
                </label>
            </div>

            <div class="spc-grid-contact">
                <div class="spc-field"><label class="app-filter-label" for="spcContact">Contact No.</label><input id="spcContact" type="text" class="app-filter-input" value="{{ $cfgContact }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcEmail">Email Add</label><input id="spcEmail" type="email" class="app-filter-input" value="{{ $cfgEmail }}"></div>
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcResidential">Residential Address</label><input id="spcResidential" type="text" class="app-filter-input" value="{{ $cfgAddress }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcRegion">Region</label><input id="spcRegion" type="text" class="app-filter-input" value="{{ $cfgRegion }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcProvince">Province</label><input id="spcProvince" type="text" class="app-filter-input" value="{{ $cfgProvince }}"></div>
            </div>

            <div class="spc-grid-birth">
                <div class="spc-field"><label class="app-filter-label" for="spcMunicipality">Municipality</label><input id="spcMunicipality" type="text" class="app-filter-input" value="{{ $cfgMunicipality }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcBirthDate">Date of Birth</label><input id="spcBirthDate" type="text" class="app-filter-input" value="{{ $cfgBirthDate }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcBirthPlace">Place of Birth</label><input id="spcBirthPlace" type="text" class="app-filter-input" value="{{ $cfgBirthPlace }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcCitizenship">Citizenship</label><input id="spcCitizenship" type="text" class="app-filter-input" value="{{ $cfgCitizenship }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcReligion">Religion</label><input id="spcReligion" type="text" class="app-filter-input" value="{{ $cfgReligion }}"></div>
            </div>

            <div class="spc-grid-address">
                <div class="spc-field"><label class="app-filter-label" for="spcBlood">Blood Type</label><input id="spcBlood" type="text" class="app-filter-input" value="{{ $cfgBloodType }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcCivil">Civil Status</label><input id="spcCivil" type="text" class="app-filter-input" value="{{ $cfgCivilStatus }}"></div>
                <label class="spc-checkline"><input id="spcSameAddress" type="checkbox" class="req-checkbox-input" checked> Same as Residential Address</label>
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcPermanent">Permanent Address</label><input id="spcPermanent" type="text" class="app-filter-input" value="{{ $cfgAddress }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcPermRegion">Region</label><input id="spcPermRegion" type="text" class="app-filter-input" value="{{ $cfgRegion }}"></div>
            </div>

            <div class="spc-divider"></div>

            <div class="spc-grid-acad">
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcProgram">Program</label><input id="spcProgram" type="text" class="app-filter-input" value="{{ $cfgCourse }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcYear">Year Level</label><input id="spcYear" type="text" class="app-filter-input" value="{{ $cfgYearLevel }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcSection">Section</label><input id="spcSection" type="text" class="app-filter-input" value="{{ $cfgSection }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcCurriculum">Curriculum Year</label><input id="spcCurriculum" type="text" class="app-filter-input" value="{{ $cfgCurriculumYear }}"></div>
            </div>

            <div class="spc-grid-status">
                <div class="spc-field"><label class="app-filter-label" for="spcAdmissionYear">Admission Year</label><input id="spcAdmissionYear" type="text" class="app-filter-input" value="{{ $cfgAdmissionYear }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcAdmissionStatus">Admission Status</label><input id="spcAdmissionStatus" type="text" class="app-filter-input" value="{{ $cfgAdmissionStatus }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcEnrollmentStatus">Enrollment Status</label><input id="spcEnrollmentStatus" type="text" class="app-filter-input" value="{{ $cfgEnrollmentStatus }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcAcademicStatus">Academic Status</label><input id="spcAcademicStatus" type="text" class="app-filter-input" value="{{ $cfgAcademicStatus }}"></div>
            </div>

            <div class="spc-transfer-row">
                <label class="spc-checkline"><input id="spcTransferTag" type="checkbox" class="req-checkbox-input" {{ $cfgIsTransfer ? 'checked' : '' }}> Tag this Student as Transfer.</label>
            </div>

            <div id="spcTransferDetails" class="spc-grid-transfer" style="display: {{ $cfgIsTransfer ? 'grid' : 'none' }};">
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcSchoolName">Name of School</label><input id="spcSchoolName" type="text" class="app-filter-input" value="{{ $cfgSchoolName }}"></div>
                <div class="spc-field"><label class="app-filter-label" for="spcDateTransferred">Date Transferred</label><input id="spcDateTransferred" type="text" class="app-filter-input" value="{{ $cfgDateTransferred }}"></div>
                <div class="spc-field spc-field-grow"><label class="app-filter-label" for="spcReasons">Reasons</label><input id="spcReasons" type="text" class="app-filter-input" value="{{ $cfgTransferReason }}"></div>
            </div>
        </section>
    </div>
</div>
@endif

<div class="req-modal-overlay" id="spFormModal" style="display:none;" onclick="if(event.target===this) spCloseFormModal()">
    <div class="req-modal-box" style="max-width:760px;">
        <h3 class="req-modal-title" id="spFormTitle">ADD STUDENT PROFILE</h3>
        <input type="hidden" id="spEditingId" value="">

        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student ID</label>
                <input id="spIdInput" type="text" class="req-modal-input" placeholder="e.g. 2223A8137">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year Level</label>
                <select id="spYearInput" class="req-modal-input">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                    <option value="Fourth">Fourth</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input id="spCourseInput" type="text" class="req-modal-input" placeholder="Course">
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Student Name</label>
                <input id="spNameInput" type="text" class="req-modal-input" placeholder="Last Name, First Name">
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="spCloseFormModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="spSaveRecord()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="spDeleteModal" style="display:none;" onclick="if(event.target===this) spCloseDeleteModal()">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE STUDENT PROFILE</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this record?</p>
        <input type="hidden" id="spDeleteId" value="">
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="spCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="spConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@if(!$isConfigMode && !$isApplicationMode)
@push('scripts')
<script>
    var spRows = @json($spRows ?? []);
    var spCsrf = '{{ csrf_token() }}';
    var spApi = {
        store: '{{ route('registrar.admin-tools.master-files.student-profile.store') }}',
        updateTemplate: '{{ route('registrar.admin-tools.master-files.student-profile.update', ['masterStudentProfile' => '__ID__']) }}',
        destroyTemplate: '{{ route('registrar.admin-tools.master-files.student-profile.destroy', ['masterStudentProfile' => '__ID__']) }}'
    };
    var spCurrentPage = 1;
    var spPageSize = 10;

    function spBuildUrl(template, id) {
        return template.replace('__ID__', encodeURIComponent(String(id)));
    }

    function spRequest(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': spCsrf,
                'Accept': 'application/json'
            },
            body: payload ? JSON.stringify(payload) : null
        }).then(function(response) {
            return response.json().catch(function() { return {}; }).then(function(data) {
                if (!response.ok || data.ok === false) {
                    var message = (data && data.message) ? data.message : 'Request failed.';
                    if (data && data.errors) {
                        var firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
                            message = data.errors[firstKey][0];
                        }
                    }
                    throw new Error(message);
                }
                return data;
            });
        });
    }

    function spEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function spGetFilteredRows() {
        var query = (document.getElementById('spSearch').value || '').toLowerCase();
        return spRows.filter(function(row) {
            if (!query) return true;
            return row.studentId.toLowerCase().indexOf(query) !== -1 ||
                row.name.toLowerCase().indexOf(query) !== -1 ||
                row.course.toLowerCase().indexOf(query) !== -1 ||
                row.yearLevel.toLowerCase().indexOf(query) !== -1;
        });
    }

    function spBuildMenu(menuId, id) {
        return '' +
            '<div class="apst-action-btn" data-sp-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="spOpenEditModal(\'' + spEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" onclick="spOpenDeleteModal(\'' + spEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>Delete</button>' +
            '</div>';
    }

    function spCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function spToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        spCloseActionMenus();
        if (isOpen) return;

        var rect = trigger.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;

        menu.style.left = 'auto';
        menu.style.right = (window.innerWidth - rect.left + 4) + 'px';

        if (spaceBelow < 120) {
            menu.classList.add('drop-up');
            menu.style.top = 'auto';
            menu.style.bottom = (window.innerHeight - rect.bottom) + 'px';
        } else {
            menu.style.top = rect.top + 'px';
            menu.style.bottom = 'auto';
        }

        menu.classList.add('open');
    }

    function spRenderPager(totalRows) {
        var mount = document.querySelector('.app-table-pager');
        if (!mount) return;

        var maxPage = Math.max(1, Math.ceil(totalRows / spPageSize));
        if (spCurrentPage > maxPage) {
            spCurrentPage = maxPage;
        }

        if (totalRows <= spPageSize) {
            mount.innerHTML = '';
            return;
        }

        var start = Math.max(1, spCurrentPage - 2);
        var end = Math.min(maxPage, spCurrentPage + 2);
        if (spCurrentPage <= 3) {
            end = Math.min(maxPage, 5);
        } else if (spCurrentPage >= maxPage - 2) {
            start = Math.max(1, maxPage - 4);
        }

        var pageNums = '';
        for (var p = start; p <= end; p += 1) {
            pageNums += '<button type="button" class="rtp-page-num ' + (p === spCurrentPage ? 'active' : '') + '" data-sp-page="' + p + '">' + p + '</button>';
        }

        mount.innerHTML = '' +
            '<div class="rtp-pagination">' +
                '<nav class="rtp-nav" aria-label="Student profile pagination">' +
                    '<div class="rtp-list" role="group" aria-label="Page controls">' +
                        '<button type="button" class="rtp-page-btn" data-sp-page-prev="1" ' + (spCurrentPage <= 1 ? 'disabled' : '') + '>&lt;</button>' +
                        pageNums +
                        '<button type="button" class="rtp-page-btn" data-sp-page-next="1" ' + (spCurrentPage >= maxPage ? 'disabled' : '') + '>&gt;</button>' +
                    '</div>' +
                '</nav>' +
            '</div>';
    }

    function spRenderTable() {
        var tbody = document.getElementById('spTableBody');
        if (!tbody) return;

        spCloseActionMenus();

        var rows = spGetFilteredRows();
        var maxPage = Math.max(1, Math.ceil(rows.length / spPageSize));
        if (spCurrentPage > maxPage) {
            spCurrentPage = 1;
        }
        var startIndex = (spCurrentPage - 1) * spPageSize;
        var pageItems = rows.slice(startIndex, startIndex + spPageSize);

        var bodyRows = pageItems.map(function(row, idx) {
            var menuId = 'spMenu' + idx;
            return '' +
                '<tr data-sp-id="' + spEscapeHtml(row.id) + '">' +
                    '<td>' + (startIndex + idx + 1) + '</td>' +
                    '<td>' + spEscapeHtml(row.studentId) + '</td>' +
                    '<td>' + spEscapeHtml(row.name).toUpperCase() + '</td>' +
                    '<td>' + spEscapeHtml(row.course) + '</td>' +
                    '<td>' + spEscapeHtml(row.yearLevel) + '</td>' +
                    '<td style="text-align:center;">' + spBuildMenu(menuId, row.id) + '</td>' +
                '</tr>';
        }).join('');

        if (!bodyRows) {
            bodyRows = '<tr><td colspan="6" class="sc-empty-row">No student profiles found.</td></tr>';
        }

        tbody.innerHTML = bodyRows +
            '<tr class="sp-total-row"><td colspan="6">Total Students: <strong>' + rows.length + '</strong></td></tr>';
        spRenderPager(rows.length);
    }

    function spOpenAddModal() {
        document.getElementById('spFormTitle').textContent = 'ADD STUDENT PROFILE';
        document.getElementById('spEditingId').value = '';
        document.getElementById('spIdInput').value = '';
        document.getElementById('spNameInput').value = '';
        document.getElementById('spCourseInput').value = '';
        document.getElementById('spYearInput').value = 'First';
        spCloseActionMenus();
        document.getElementById('spFormModal').style.display = 'flex';
    }

    function spOpenEditModal(id) {
        var row = spRows.find(function(item) { return String(item.id) === String(id); });
        if (!row) return;

        document.getElementById('spFormTitle').textContent = 'EDIT STUDENT PROFILE';
        document.getElementById('spEditingId').value = row.id;
        document.getElementById('spIdInput').value = row.studentId;
        document.getElementById('spNameInput').value = row.name;
        document.getElementById('spCourseInput').value = row.course;
        document.getElementById('spYearInput').value = row.yearLevel;
        spCloseActionMenus();
        document.getElementById('spFormModal').style.display = 'flex';
    }

    function spCloseFormModal() {
        document.getElementById('spFormModal').style.display = 'none';
    }

    function spSaveRecord() {
        var editingId = document.getElementById('spEditingId').value;
        var studentId = document.getElementById('spIdInput').value.trim();
        var name = document.getElementById('spNameInput').value.trim();
        var course = document.getElementById('spCourseInput').value.trim();
        var yearLevel = document.getElementById('spYearInput').value;

        if (!studentId || !name || !course) {
            alert('Please fill in Student ID, Student Name, and Course.');
            return;
        }

        var payload = {
            student_id: studentId,
            name: name,
            course: course,
            year_level: yearLevel
        };

        var request = !editingId
            ? spRequest(spApi.store, 'POST', payload)
            : spRequest(spBuildUrl(spApi.updateTemplate, editingId), 'PUT', payload);

        request.then(function(data) {
            if (!editingId) {
                spRows.unshift(data.row);
            } else {
                spRows = spRows.map(function(item) {
                    return String(item.id) === String(editingId) ? data.row : item;
                });
            }

            spCloseFormModal();
            spRenderTable();
        }).catch(function(error) {
            alert(error.message || 'Unable to save student profile.');
        });
    }

    function spOpenDeleteModal(id) {
        spCloseActionMenus();
        document.getElementById('spDeleteId').value = id;
        document.getElementById('spDeleteModal').style.display = 'flex';
    }

    function spCloseDeleteModal() {
        document.getElementById('spDeleteModal').style.display = 'none';
    }

    function spConfirmDelete() {
        var id = document.getElementById('spDeleteId').value;
        spRequest(spBuildUrl(spApi.destroyTemplate, id), 'DELETE', null).then(function() {
            spRows = spRows.filter(function(item) {
                return String(item.id) !== String(id);
            });
            spCloseDeleteModal();
            spRenderTable();
        }).catch(function(error) {
            alert(error.message || 'Unable to delete student profile.');
        });
    }

    document.getElementById('spSearchBtn').addEventListener('click', function() {
        spCurrentPage = 1;
        spRenderTable();
    });
    document.getElementById('spSearch').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            spCurrentPage = 1;
            spRenderTable();
        }
    });
    document.getElementById('spNewRecordBtn').addEventListener('click', spOpenAddModal);

    document.addEventListener('click', function(event) {
        var menuToggle = event.target.closest('[data-sp-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            spToggleActionMenu(menuToggle.getAttribute('data-sp-menu-toggle'), menuToggle);
            return;
        }

        var row = event.target.closest('#spTableBody tr[data-sp-id]');
        if (row && !event.target.closest('.apst-dropdown') && !event.target.closest('.apst-action-btn')) {
            var rowId = row.getAttribute('data-sp-id');
            var selected = spRows.find(function(item) { return String(item.id) === String(rowId); });
            if (selected) {
                var url = '{{ route('registrar.admin-tools.master-files.student-profile') }}' +
                    '?view=config' +
                    '&student_id=' + encodeURIComponent(selected.studentId || '') +
                    '&name=' + encodeURIComponent(selected.name || '') +
                    '&course=' + encodeURIComponent(selected.course || '') +
                    '&year_level=' + encodeURIComponent(selected.yearLevel || '');
                window.location.href = url;
            }
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            spCloseActionMenus();
        }
    });

    window.addEventListener('scroll', spCloseActionMenus, true);

    document.querySelector('.app-table-pager').addEventListener('click', function(event) {
        var prev = event.target.closest('[data-sp-page-prev]');
        if (prev && spCurrentPage > 1) {
            spCurrentPage -= 1;
            spRenderTable();
            return;
        }

        var next = event.target.closest('[data-sp-page-next]');
        if (next) {
            var total = spGetFilteredRows().length;
            var maxPage = Math.max(1, Math.ceil(total / spPageSize));
            if (spCurrentPage < maxPage) {
                spCurrentPage += 1;
                spRenderTable();
            }
            return;
        }

        var pageBtn = event.target.closest('[data-sp-page]');
        if (pageBtn) {
            spCurrentPage = parseInt(pageBtn.getAttribute('data-sp-page'), 10) || 1;
            spRenderTable();
        }
    });

    spRenderTable();
</script>
@endpush
@elseif($isApplicationMode)
@push('scripts')
<script>
    window.applicantAddressDraft = {};
</script>
<script src="{{ asset('js/applicant-form.js') }}"></script>
@endpush
@else
@push('scripts')
<script>
    (function () {
        var transferToggle = document.getElementById('spcTransferTag');
        var transferDetails = document.getElementById('spcTransferDetails');
        if (!transferToggle || !transferDetails) return;

        function syncTransferDetails() {
            transferDetails.style.display = transferToggle.checked ? 'grid' : 'none';
        }

        transferToggle.addEventListener('change', syncTransferDetails);
        syncTransferDetails();
    })();
</script>
@endpush
@endif



