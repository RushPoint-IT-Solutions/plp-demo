@extends('layouts.faculty')

@section('title', 'PLP - Faculty Profile')
@section('page-title', 'FACULTY PROFILE')
@section('body-class', 'page-profile-edit page-faculty-file-config page-faculty-profile-edit')

@section('content')
@php
    $state = is_array($formState ?? null) ? $formState : [];
    $row = $profileRow ?? null;
    $facultyName = old('name', isset($state['name']) ? $state['name'] : (optional($row)->name ?? optional($faculty)->name));
    $department = old('department', isset($state['department']) ? $state['department'] : (optional($row)->department ?? 'Computer Studies'));
    $employmentTypeValue = old('employment_type', isset($state['employment_type']) ? $state['employment_type'] : (optional($faculty)->employment_type ?? 'Full Time'));
    $employmentType = stripos((string) $employmentTypeValue, 'part') !== false ? 'Part Time' : 'Full Time';
@endphp

<div class="profile-page">
    @if(session('success'))
    <div class="profile-save-banner" id="facultyProfileSaveBanner">
        <span>{{ session('success') }}</span>
        <button type="button" onclick="this.closest('.profile-save-banner').remove()">&#x2715;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="profile-save-banner profile-error-banner" id="facultyProfileErrorBanner">
        <span>Please fix the following: {{ implode(' | ', $errors->all()) }}</span>
        <button type="button" onclick="this.closest('.profile-save-banner').remove()">&#x2715;</button>
    </div>
    @endif

    <form id="facultyProfileForm" class="faculty-profile-form" method="POST" action="{{ route('faculty.profile.update') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <input type="hidden" name="sections_json" id="facultySectionsJson" value="">

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
            <div class="setup-section">
                <div class="setup-personal-top">
                    <div class="setup-personal-left">
                        <div class="setup-section-header">
                            <h3 class="setup-section-title">Faculty Profile</h3>
                        </div>

                        <div class="setup-row">
                            <div class="setup-col setup-col--w-280">
                                <label class="setup-label">Faculty Code</label>
                                <input type="text" class="setup-input" name="code_display" value="{{ old('code_display', optional($row)->code ?? optional($faculty)->code ?? '') }}" readonly>
                            </div>
                            <div class="setup-col setup-col--w-280">
                                <label class="setup-label">Employment Type</label>
                                <select class="setup-input setup-select" name="employment_type" required>
                                    <option value="Full-time Teacher" @if($employmentType === 'Full Time') selected @endif>Full Time</option>
                                    <option value="Part-time Teacher" @if($employmentType === 'Part Time') selected @endif>Part Time</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="setup-profile-photo">
                        <div class="profile-photo-square" id="facultyPhotoPreview">
                            @if(!empty($state['profile_photo_path']))
                                <img id="facultyPhotoImg" src="{{ asset('storage/' . $state['profile_photo_path']) }}" alt="Faculty photo" style="display:block; width:100%; height:100%; object-fit:cover;">
                            @else
                                <svg class="profile-photo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="12" cy="10" r="3"/>
                                    <path d="M6 21v-1a6 6 0 0 1 12 0v1"/>
                                </svg>
                                <img id="facultyPhotoImg" src="" alt="" style="display:none; width:100%; height:100%; object-fit:cover;">
                            @endif
                        </div>
                        <label class="profile-photo-btn" for="facultyPhotoInput">Upload Photo</label>
                        <input type="file" id="facultyPhotoInput" name="profile_photo" accept="image/*" style="display:none;">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Last Name</label>
                        <input type="text" class="setup-input" name="last_name" placeholder="Last Name" value="{{ old('last_name', isset($state['last_name']) ? $state['last_name'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">First Name</label>
                        <input type="text" class="setup-input" name="first_name" placeholder="First Name" value="{{ old('first_name', isset($state['first_name']) ? $state['first_name'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Middle Name</label>
                        <input type="text" class="setup-input" name="middle_name" placeholder="Middle Name" value="{{ old('middle_name', isset($state['middle_name']) ? $state['middle_name'] : '') }}">
                    </div>
                    <div class="setup-col setup-col-sm">
                        <label class="setup-label">Suffix</label>
                        <input type="text" class="setup-input" name="extension_name" placeholder="N/A" value="{{ old('extension_name', isset($state['extension_name']) ? $state['extension_name'] : '') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col setup-col--flex-2">
                        <label class="setup-label">Faculty Name</label>
                        <input type="text" class="setup-input" name="name" id="facultyNameInput" required value="{{ $facultyName }}" placeholder="Last Name, First Name">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Position</label>
                        <input type="text" class="setup-input" name="position" placeholder="Position" value="{{ old('position', isset($state['position']) ? $state['position'] : '') }}">
                    </div>
                </div>
            </div>

            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Employment Data</h3>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Office Department</label>
                        <select class="setup-input setup-select" name="department" id="officeDepartment" required>
                            <option value="">Select Department</option>
                            <option value="Computer Studies" @if($department === 'Computer Studies') selected @endif>Computer Studies</option>
                            <option value="College of Information Technology" @if($department === 'College of Information Technology') selected @endif>College of Information Technology</option>
                            <option value="Engineering" @if($department === 'Engineering') selected @endif>Engineering</option>
                            <option value="Business Administration" @if($department === 'Business Administration') selected @endif>Business Administration</option>
                            <option value="Education" @if($department === 'Education') selected @endif>Education</option>
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Parent College</label>
                        <input type="text" class="setup-input" name="parent_college" placeholder="Parent College" value="{{ old('parent_college', isset($state['parent_college']) ? $state['parent_college'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Accreditation</label>
                        <input type="text" class="setup-input" name="accreditation" placeholder="Accreditation" value="{{ old('accreditation', isset($state['accreditation']) ? $state['accreditation'] : '') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Date of Employment</label>
                        <input type="date" class="setup-input" name="date_of_employment" value="{{ old('date_of_employment', isset($state['date_of_employment']) ? $state['date_of_employment'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Permanent Status Date</label>
                        <input type="date" class="setup-input" name="date_of_permanent_status" value="{{ old('date_of_permanent_status', isset($state['date_of_permanent_status']) ? $state['date_of_permanent_status'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Parent Course</label>
                        <input type="text" class="setup-input" name="parent_course" placeholder="Parent Course" value="{{ old('parent_course', isset($state['parent_course']) ? $state['parent_course'] : '') }}">
                    </div>
                    <div class="setup-col setup-col-sm">
                        <label class="setup-label">Regular Load</label>
                        <input type="text" class="setup-input" name="regular_load" placeholder="0" value="{{ old('regular_load', isset($state['regular_load']) ? $state['regular_load'] : '') }}">
                    </div>
                    <div class="setup-col setup-col-sm">
                        <label class="setup-label">Over Load</label>
                        <input type="text" class="setup-input" name="over_load" placeholder="0" value="{{ old('over_load', isset($state['over_load']) ? $state['over_load'] : '') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Professional License</label>
                        <input type="text" class="setup-input" name="professional_licensure_passed" placeholder="Licensure Passed" value="{{ old('professional_licensure_passed', isset($state['professional_licensure_passed']) ? $state['professional_licensure_passed'] : '') }}">
                    </div>
                    <div class="setup-col setup-col--w-220">
                        <label class="setup-label">Annual Salary</label>
                        @php
                            $annualSalaryValue = old('annual_salary', isset($state['annual_salary']) ? $state['annual_salary'] : '');
                        @endphp
                        <select class="setup-input setup-select" name="annual_salary">
                            <option value="" @if($annualSalaryValue === '') selected @endif>Select Salary Range</option>
                            <option value="5,000 and up" @if($annualSalaryValue === '5,000 and up') selected @endif>5,000 and up</option>
                            <option value="10,000 and up" @if($annualSalaryValue === '10,000 and up') selected @endif>10,000 and up</option>
                            <option value="15,000 and up" @if($annualSalaryValue === '15,000 and up') selected @endif>15,000 and up</option>
                            <option value="20,000 and up" @if($annualSalaryValue === '20,000 and up') selected @endif>20,000 and up</option>
                            <option value="30,000 and up" @if($annualSalaryValue === '30,000 and up') selected @endif>30,000 and up</option>
                            <option value="40,000 and up" @if($annualSalaryValue === '40,000 and up') selected @endif>40,000 and up</option>
                            <option value="50,000 and up" @if($annualSalaryValue === '50,000 and up') selected @endif>50,000 and up</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="setup-nav">
                <div></div>
                <button type="button" class="btn-setup-next" data-next="2">Next</button>
            </div>
        </div>

        <div class="setup-form-container step-panel" id="step-2" style="display:none;">
            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Personal Data</h3>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Mobile No.</label>
                        <input type="text" class="setup-input" name="mobile_no" placeholder="+639" value="{{ old('mobile_no', isset($state['mobile_no']) ? $state['mobile_no'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Email Address</label>
                        <input type="email" class="setup-input" name="email_address" placeholder="email@example.com" value="{{ old('email_address', isset($state['email_address']) ? $state['email_address'] : '') }}">
                    </div>
                    <div class="setup-col" style="flex:2;">
                        <label class="setup-label">Complete Address</label>
                        <input type="text" class="setup-input" name="complete_address" placeholder="Complete Address" value="{{ old('complete_address', isset($state['complete_address']) ? $state['complete_address'] : '') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Tel. No.</label>
                        <input type="text" class="setup-input" name="tel_no" placeholder="+63" value="{{ old('tel_no', isset($state['tel_no']) ? $state['tel_no'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Date of Birth</label>
                        <input type="date" class="setup-input" name="date_of_birth" value="{{ old('date_of_birth', isset($state['date_of_birth']) ? $state['date_of_birth'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Place of Birth</label>
                        <input type="text" class="setup-input" name="place_of_birth" placeholder="Place of Birth" value="{{ old('place_of_birth', isset($state['place_of_birth']) ? $state['place_of_birth'] : '') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Religion</label>
                        <input type="text" class="setup-input" name="religion" placeholder="Religion" value="{{ old('religion', isset($state['religion']) ? $state['religion'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Citizenship</label>
                        <input type="text" class="setup-input" name="citizenship" placeholder="Citizenship" value="{{ old('citizenship', isset($state['citizenship']) ? $state['citizenship'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Civil Status</label>
                        <select class="setup-input setup-select" name="civil_status">
                            <option value="">Select Status</option>
                            <option value="Single" @if(old('civil_status', isset($state['civil_status']) ? $state['civil_status'] : '') === 'Single') selected @endif>Single</option>
                            <option value="Married" @if(old('civil_status', isset($state['civil_status']) ? $state['civil_status'] : '') === 'Married') selected @endif>Married</option>
                            <option value="Widowed" @if(old('civil_status', isset($state['civil_status']) ? $state['civil_status'] : '') === 'Widowed') selected @endif>Widowed</option>
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Gender</label>
                        <div class="setup-radio-group">
                            <label class="setup-radio"><input type="radio" name="gender" value="Male" @if(old('gender', isset($state['gender']) ? $state['gender'] : '') === 'Male') checked @endif> Male</label>
                            <label class="setup-radio"><input type="radio" name="gender" value="Female" @if(old('gender', isset($state['gender']) ? $state['gender'] : '') === 'Female') checked @endif> Female</label>
                        </div>
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Spouse Name</label>
                        <input type="text" class="setup-input" name="spouse_name" placeholder="Name of Husband/Wife" value="{{ old('spouse_name', isset($state['spouse_name']) ? $state['spouse_name'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Occupation</label>
                        <input type="text" class="setup-input" name="spouse_occupation" placeholder="Occupation" value="{{ old('spouse_occupation', isset($state['spouse_occupation']) ? $state['spouse_occupation'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">No. of Dependents</label>
                        <input type="text" class="setup-input" name="dependents" placeholder="Dependents" value="{{ old('dependents', isset($state['dependents']) ? $state['dependents'] : '') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Father's Name</label>
                        <input type="text" class="setup-input" name="father_name" placeholder="Father's Name" value="{{ old('father_name', isset($state['father_name']) ? $state['father_name'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Occupation</label>
                        <input type="text" class="setup-input" name="father_occupation" placeholder="Occupation" value="{{ old('father_occupation', isset($state['father_occupation']) ? $state['father_occupation'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Address</label>
                        <input type="text" class="setup-input" name="father_address" placeholder="Address" value="{{ old('father_address', isset($state['father_address']) ? $state['father_address'] : '') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Mother's Name</label>
                        <input type="text" class="setup-input" name="mother_name" placeholder="Mother's Name" value="{{ old('mother_name', isset($state['mother_name']) ? $state['mother_name'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Occupation</label>
                        <input type="text" class="setup-input" name="mother_occupation" placeholder="Occupation" value="{{ old('mother_occupation', isset($state['mother_occupation']) ? $state['mother_occupation'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Address</label>
                        <input type="text" class="setup-input" name="mother_address" placeholder="Address" value="{{ old('mother_address', isset($state['mother_address']) ? $state['mother_address'] : '') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">SSS #</label>
                        <input type="text" class="setup-input" name="sss_no" placeholder="SSS No." value="{{ old('sss_no', isset($state['sss_no']) ? $state['sss_no'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">TIN #</label>
                        <input type="text" class="setup-input" name="tin_no" placeholder="TIN No." value="{{ old('tin_no', isset($state['tin_no']) ? $state['tin_no'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">PhilHealth #</label>
                        <input type="text" class="setup-input" name="philhealth_no" placeholder="PhilHealth No." value="{{ old('philhealth_no', isset($state['philhealth_no']) ? $state['philhealth_no'] : '') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">PAG-IBIG #</label>
                        <input type="text" class="setup-input" name="pagibig_no" placeholder="PAG-IBIG No." value="{{ old('pagibig_no', isset($state['pagibig_no']) ? $state['pagibig_no'] : '') }}">
                    </div>
                </div>
            </div>

            <div class="setup-nav">
                <button type="button" class="btn-setup-prev" data-prev="1">Previous</button>
                <button type="button" class="btn-setup-next" data-next="3">Next</button>
            </div>
        </div>

        <div class="setup-form-container step-panel faculty-step-consistent" id="step-3" style="display:none;">
            <div class="faculty-step-content">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Educational Background</h3>
                </div>

                <div class="ffc-list-head">
                    <h4 class="ffc-list-title">Educational Background</h4>
                    <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="education">+ Add</button>
                </div>

                <div class="app-table-wrap ffc-table-wrap">
                    <table class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>School Level</th>
                                <th>School Name</th>
                                <th>Course/Degree</th>
                                <th>Date Graduated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="facultyEducationBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="setup-nav">
                <button type="button" class="btn-setup-prev" data-prev="2">Previous</button>
                <button type="button" class="btn-setup-next" data-next="4">Next</button>
            </div>
        </div>

        <div class="setup-form-container step-panel faculty-step-consistent" id="step-4" style="display:none;">
            <div class="faculty-step-content">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Academic Background</h3>
                </div>

                <div class="ffc-list-head">
                    <h4 class="ffc-list-title">Professional Registration</h4>
                    <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="registration">+ Add</button>
                </div>
                <div class="app-table-wrap ffc-table-wrap">
                    <table class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>Professional Registration</th>
                                <th>Rating</th>
                                <th>Registration Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="facultyRegistrationBody"></tbody>
                    </table>
                </div>

                <div class="ffc-list-head">
                    <h4 class="ffc-list-title">Professional Organization</h4>
                    <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="organization">+ Add</button>
                </div>
                <div class="app-table-wrap ffc-table-wrap">
                    <table class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>Name of Professional Org.</th>
                                <th>Name of Org.</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="facultyOrganizationBody"></tbody>
                    </table>
                </div>

                <div class="ffc-list-head">
                    <h4 class="ffc-list-title">Work Experience</h4>
                    <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="work">+ Add</button>
                </div>
                <div class="app-table-wrap ffc-table-wrap">
                    <table class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>Work Experience</th>
                                <th>Company Name</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="facultyWorkBody"></tbody>
                    </table>
                </div>

                <div class="ffc-list-head">
                    <h4 class="ffc-list-title">Trainings/Seminar Attended</h4>
                    <button type="button" class="pf-btn-new ffc-add-btn" data-ffc-add="training">+ Add</button>
                </div>
                <div class="app-table-wrap ffc-table-wrap">
                    <table class="app-table cfg-table ffc-table" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>Training(s)/Seminar(s)</th>
                                <th>Place</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="facultyTrainingBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="setup-nav">
                <button type="button" class="btn-setup-prev" data-prev="3">Previous</button>
                <button type="submit" class="btn-setup-submit">Save Profile</button>
            </div>
        </div>
    </form>
</div>

<div class="req-modal-overlay" id="facultyItemModal" style="display:none;" onclick="if(event.target===this) closeFacultyItemModal()">
    <div class="req-modal-box" style="max-width:700px;">
        <h3 class="req-modal-title" id="facultyItemModalTitle">ADD ITEM</h3>
        <input type="hidden" id="facultyItemSection" value="">
        <input type="hidden" id="facultyItemEditIndex" value="">

        <div id="facultyItemFields" class="sc-modal-grid" style="margin-top:10px;"></div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="closeFacultyItemModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="saveFacultyItem()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="facultyDeleteModal" style="display:none;" onclick="if(event.target===this) closeFacultyDeleteModal()">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE DETAIL ITEM</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this item?</p>
        <input type="hidden" id="facultyDeleteSection" value="">
        <input type="hidden" id="facultyDeleteIndex" value="">
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="closeFacultyDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="confirmFacultyDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var stepItems = Array.prototype.slice.call(document.querySelectorAll('.step-item'));
    var stepPanels = {
        1: document.getElementById('step-1'),
        2: document.getElementById('step-2'),
        3: document.getElementById('step-3'),
        4: document.getElementById('step-4')
    };

    function setStep(stepNumber) {
        Object.keys(stepPanels).forEach(function (key) {
            var panel = stepPanels[key];
            if (!panel) return;
            panel.style.display = Number(key) === stepNumber ? '' : 'none';
        });

        stepItems.forEach(function (item) {
            var itemStep = Number(item.getAttribute('data-step'));
            item.classList.toggle('active', itemStep === stepNumber);
            item.classList.toggle('done', itemStep < stepNumber);
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('click', function (event) {
        var nextBtn = event.target.closest('[data-next]');
        if (nextBtn) {
            setStep(Number(nextBtn.getAttribute('data-next')));
            return;
        }

        var prevBtn = event.target.closest('[data-prev]');
        if (prevBtn) {
            setStep(Number(prevBtn.getAttribute('data-prev')));
            return;
        }
    });

    var photoInput = document.getElementById('facultyPhotoInput');
    var photoImg = document.getElementById('facultyPhotoImg');
    var photoPreview = document.getElementById('facultyPhotoPreview');
    if (photoInput) {
        photoInput.addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;

            var reader = new FileReader();
            reader.onload = function (e) {
                if (photoImg) {
                    photoImg.src = e.target.result;
                    photoImg.style.display = 'block';
                }
                if (photoPreview) {
                    var icon = photoPreview.querySelector('.profile-photo-icon');
                    if (icon) {
                        icon.style.display = 'none';
                    }
                }
            };
            reader.readAsDataURL(this.files[0]);
        });
    }

    var rows = @json($detailRows ?? []);
    var sections = {
        education: {
            bodyId: 'facultyEducationBody',
            label: 'Educational Background',
            fields: [
                { key: 'level', label: 'School Level', type: 'select', options: ['Elementary', 'High School', 'College', 'Graduate Studies'] },
                { key: 'schoolName', label: 'School Name', type: 'text', placeholder: 'School Name' },
                { key: 'courseDegree', label: 'Course/Degree', type: 'text', placeholder: 'Course/Degree' },
                { key: 'dateGraduated', label: 'Date Graduated', type: 'date', placeholder: 'mm/dd/yy' }
            ]
        },
        registration: {
            bodyId: 'facultyRegistrationBody',
            label: 'Professional Registration',
            fields: [
                { key: 'name', label: 'Professional Registration', type: 'text', placeholder: 'Professional Registration' },
                { key: 'rating', label: 'Rating', type: 'text', placeholder: 'Rating' },
                { key: 'date', label: 'Registration Date', type: 'date', placeholder: 'mm/dd/yy' }
            ]
        },
        organization: {
            bodyId: 'facultyOrganizationBody',
            label: 'Professional Organization',
            fields: [
                { key: 'position', label: 'Name of Professional Org.', type: 'text', placeholder: 'Position' },
                { key: 'name', label: 'Name of Org.', type: 'text', placeholder: 'Organization Name' },
                { key: 'date', label: 'Date', type: 'date', placeholder: 'mm/dd/yy' }
            ]
        },
        work: {
            bodyId: 'facultyWorkBody',
            label: 'Work Experience',
            fields: [
                { key: 'position', label: 'Work Experience', type: 'text', placeholder: 'Position' },
                { key: 'company', label: 'Company Name', type: 'text', placeholder: 'Company Name' },
                { key: 'date', label: 'Date', type: 'date', placeholder: 'mm/dd/yy' }
            ]
        },
        training: {
            bodyId: 'facultyTrainingBody',
            label: 'Trainings/Seminar Attended',
            fields: [
                { key: 'title', label: 'Training(s)/Seminar(s)', type: 'text', placeholder: 'Training/Seminar' },
                { key: 'place', label: 'Place', type: 'text', placeholder: 'Place' },
                { key: 'date', label: 'Date', type: 'date', placeholder: 'mm/dd/yy' }
            ]
        }
    };

    function normalizeRows() {
        Object.keys(sections).forEach(function (sectionKey) {
            if (!Array.isArray(rows[sectionKey])) {
                rows[sectionKey] = [];
            }
        });
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function buildActionButtons(section, index) {
        return '' +
            '<button type="button" class="faculty-action-btn faculty-action-btn--edit" data-edit-section="' + section + '" data-edit-index="' + index + '">Edit</button>' +
            ' <button type="button" class="faculty-action-btn faculty-action-btn--delete" data-delete-section="' + section + '" data-delete-index="' + index + '">Delete</button>';
    }

    function renderSection(section) {
        var config = sections[section];
        var tbody = document.getElementById(config.bodyId);
        if (!tbody) return;

        var sectionRows = rows[section] || [];
        if (!sectionRows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="sc-empty-row">No List Found.</td></tr>';
            return;
        }

        tbody.innerHTML = sectionRows.map(function (row, index) {
            var cells = config.fields.map(function (field) {
                return '<td>' + escapeHtml(row[field.key]) + '</td>';
            }).join('');

            return '<tr>' + cells + '<td class="faculty-action-cell">' + buildActionButtons(section, index) + '</td></tr>';
        }).join('');
    }

    function renderAllSections() {
        Object.keys(sections).forEach(function (sectionKey) {
            renderSection(sectionKey);
        });
    }

    function buildModalFields(section, rowData) {
        var config = sections[section];
        var fieldsWrap = document.getElementById('facultyItemFields');
        if (!config || !fieldsWrap) return;

        fieldsWrap.innerHTML = config.fields.map(function (field) {
            var value = rowData && rowData[field.key] ? rowData[field.key] : '';
            if (field.type === 'select') {
                var options = ['<option value="">-select-</option>'].concat((field.options || []).map(function (option) {
                    var selected = value === option ? ' selected' : '';
                    return '<option value="' + escapeHtml(option) + '"' + selected + '>' + escapeHtml(option) + '</option>';
                })).join('');

                return '' +
                    '<div class="req-modal-field-group">' +
                        '<label class="req-modal-label">' + escapeHtml(field.label) + '</label>' +
                        '<select class="req-modal-input" data-faculty-field="' + escapeHtml(field.key) + '">' + options + '</select>' +
                    '</div>';
            }

            if (field.type === 'date') {
                return '' +
                    '<div class="req-modal-field-group">' +
                        '<label class="req-modal-label">' + escapeHtml(field.label) + '</label>' +
                        '<input type="date" class="req-modal-input" data-faculty-field="' + escapeHtml(field.key) + '" value="' + escapeHtml(value) + '">' +
                    '</div>';
            }

            return '' +
                '<div class="req-modal-field-group">' +
                    '<label class="req-modal-label">' + escapeHtml(field.label) + '</label>' +
                    '<input type="text" class="req-modal-input" data-faculty-field="' + escapeHtml(field.key) + '" placeholder="' + escapeHtml(field.placeholder || '') + '" value="' + escapeHtml(value) + '">' +
                '</div>';
        }).join('');
    }

    function openFacultyItemModal(section, editIndex) {
        var config = sections[section];
        if (!config) return;

        var isEdit = typeof editIndex === 'number' && editIndex >= 0;
        var rowData = isEdit ? ((rows[section] || [])[editIndex] || null) : null;

        document.getElementById('facultyItemModalTitle').textContent = (isEdit ? 'EDIT ' : 'ADD ') + config.label.toUpperCase();
        document.getElementById('facultyItemSection').value = section;
        document.getElementById('facultyItemEditIndex').value = isEdit ? String(editIndex) : '';
        buildModalFields(section, rowData);
        document.getElementById('facultyItemModal').style.display = 'flex';
    }

    window.closeFacultyItemModal = function () {
        document.getElementById('facultyItemModal').style.display = 'none';
    };

    window.saveFacultyItem = function () {
        var section = document.getElementById('facultyItemSection').value;
        var editIndexValue = document.getElementById('facultyItemEditIndex').value;
        var config = sections[section];
        if (!config) return;

        var row = {};
        var hasBlank = false;

        config.fields.forEach(function (field) {
            var input = document.querySelector('[data-faculty-field="' + field.key + '"]');
            var value = (input ? input.value : '').trim();
            row[field.key] = value;
            if (!value) hasBlank = true;
        });

        if (hasBlank) {
            alert('Please complete all fields before saving.');
            return;
        }

        if (editIndexValue === '') {
            rows[section].push(row);
        } else {
            rows[section][Number(editIndexValue)] = row;
        }

        closeFacultyItemModal();
        renderSection(section);
    };

    function openFacultyDeleteModal(section, index) {
        document.getElementById('facultyDeleteSection').value = section;
        document.getElementById('facultyDeleteIndex').value = String(index);
        document.getElementById('facultyDeleteModal').style.display = 'flex';
    }

    window.closeFacultyDeleteModal = function () {
        document.getElementById('facultyDeleteModal').style.display = 'none';
    };

    window.confirmFacultyDelete = function () {
        var section = document.getElementById('facultyDeleteSection').value;
        var index = Number(document.getElementById('facultyDeleteIndex').value);

        if (!rows[section]) return;
        rows[section].splice(index, 1);
        closeFacultyDeleteModal();
        renderSection(section);
    };

    document.addEventListener('click', function (event) {
        var addButton = event.target.closest('[data-ffc-add]');
        if (addButton) {
            openFacultyItemModal(addButton.getAttribute('data-ffc-add'));
            return;
        }

        var editButton = event.target.closest('[data-edit-section]');
        if (editButton) {
            openFacultyItemModal(editButton.getAttribute('data-edit-section'), Number(editButton.getAttribute('data-edit-index')));
            return;
        }

        var deleteButton = event.target.closest('[data-delete-section]');
        if (deleteButton) {
            openFacultyDeleteModal(deleteButton.getAttribute('data-delete-section'), Number(deleteButton.getAttribute('data-delete-index')));
        }
    });

    var form = document.getElementById('facultyProfileForm');
    if (form) {
        form.addEventListener('submit', function () {
            var fullNameInput = document.getElementById('facultyNameInput');
            if (fullNameInput && !fullNameInput.value.trim()) {
                var lastName = (document.querySelector('input[name="last_name"]') || {}).value || '';
                var firstName = (document.querySelector('input[name="first_name"]') || {}).value || '';
                var middleName = (document.querySelector('input[name="middle_name"]') || {}).value || '';
                var extensionName = (document.querySelector('input[name="extension_name"]') || {}).value || '';

                var generated = [lastName, firstName, middleName, extensionName].filter(function (part) {
                    return String(part).trim() !== '';
                }).join(', ');
                fullNameInput.value = generated;
            }

            document.getElementById('facultySectionsJson').value = JSON.stringify(rows);
        });
    }

    normalizeRows();
    renderAllSections();
    setStep(1);
})();
</script>
@endpush
