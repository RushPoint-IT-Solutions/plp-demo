@extends('layouts.applicant')

@section('title', 'PLP - Application Form')
@section('page-title', 'APPLICATION FORM')

@section('content')
<div class="profile-page">

    @if(session('success'))
    <div class="applicant-alert applicant-alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('applicant.application-form.save') }}" method="POST" enctype="multipart/form-data" id="applicationForm">
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
            <div class="setup-section">
                <div class="setup-personal-top">
                    <div class="setup-personal-left">
                        <div class="setup-section-header">
                            <h3 class="setup-section-title">Personal Information</h3>
                        </div>

                        <div class="setup-row">
                            <div class="setup-col" style="flex: 0 0 280px;">
                                <label class="setup-label">LRN</label>
                                <input type="text" class="setup-input" placeholder="LRN" name="lrn" value="{{ old('lrn') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Profile Picture --}}
                    <div class="setup-profile-photo">
                        <div class="profile-photo-square" id="profilePhotoPreview">
                            @if(isset($applicant) && $applicant->photo)
                                <img id="photoImg" src="{{ asset('storage/' . $applicant->photo) }}" alt="Photo" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <svg class="profile-photo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="12" cy="10" r="3"/>
                                    <path d="M6 21v-1a6 6 0 0 1 12 0v1"/>
                                </svg>
                                <img id="photoImg" src="" alt="" style="display:none; width:100%; height:100%; object-fit:cover;">
                            @endif
                        </div>
                        <label class="profile-photo-btn" for="photoInput">Upload Photo</label>
                        <input type="file" id="photoInput" name="photo" accept="image/*" class="d-none">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Lastname</label>
                        <input type="text" class="setup-input" placeholder="Last Name" name="last_name" value="{{ old('last_name') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">First Name</label>
                        <input type="text" class="setup-input" placeholder="First Name" name="first_name" value="{{ old('first_name') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Middle Name</label>
                        <input type="text" class="setup-input" placeholder="Middle Name" name="middle_name" value="{{ old('middle_name') }}">
                    </div>
                    <div class="setup-col setup-col-sm">
                        <label class="setup-label">Suffix</label>
                        <input type="text" class="setup-input" placeholder="Suffix" name="suffix" value="{{ old('suffix') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Nickname</label>
                        <input type="text" class="setup-input" placeholder="Nickname" name="nickname" value="{{ old('nickname') }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Gender</label>
                        <div class="setup-radio-group">
                            <label class="setup-radio"><input type="radio" name="gender" value="Male" {{ old('gender') === 'Male' ? 'checked' : '' }}> Male</label>
                            <label class="setup-radio"><input type="radio" name="gender" value="Female" {{ old('gender') === 'Female' ? 'checked' : '' }}> Female</label>
                        </div>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Nationality</label>
                        <select name="nationality" class="setup-input setup-select">
                            @foreach(['Filipino','American','Japanese','Korean','Chinese','Other'] as $nat)
                            <option value="{{ $nat }}" {{ old('nationality') === $nat ? 'selected' : '' }}>{{ $nat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Religion</label>
                        <select name="religion" class="setup-input setup-select">
                            <option value="" disabled {{ !old('religion') ? 'selected' : '' }}>Select religion</option>
                            @foreach(['Roman Catholic','Born Again Christian','Islam','Iglesia ni Cristo','Baptist','Seventh Day Adventist','Other'] as $rel)
                            <option value="{{ $rel }}" {{ old('religion') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="setup-input" id="dobField" value="{{ old('date_of_birth') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Place of Birth</label>
                        <input type="text" name="place_of_birth" class="setup-input" placeholder="Place of Birth" value="{{ old('place_of_birth') }}">
                    </div>
                    <div class="setup-col setup-col-sm">
                        <label class="setup-label">Age</label>
                        <input type="number" name="age" class="setup-input" id="ageField" placeholder="Age" value="{{ old('age') }}" readonly tabindex="-1">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Civil Status</label>
                        <select name="civil_status" class="setup-input setup-select">
                            <option value="" disabled {{ !old('civil_status') ? 'selected' : '' }}>Select status</option>
                            @foreach(['Single','Married','Widowed'] as $c)
                            <option value="{{ $c }}" {{ old('civil_status') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Mobile Number</label>
                        <input type="text" name="mobile_number" class="setup-input" placeholder="Mobile Number" value="{{ old('mobile_number') }}" maxlength="11" inputmode="numeric">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Email Address</label>
                        <input type="email" name="email_address" class="setup-input" placeholder="Email Address" value="{{ old('email_address') }}">
                    </div>
                </div>
            </div>

            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Residence Information</h3>
                </div>

                <h4 class="setup-subsection-title">Present Address</h4>

                <div class="setup-row">
                    <div class="setup-col" style="flex:3;">
                        <label class="setup-label">Street</label>
                        <input type="text" name="present_street" id="present_street" class="setup-input" placeholder="Street" value="{{ old('present_street') }}">
                    </div>
                    <div class="setup-col" style="flex:2;">
                        <label class="setup-label">Barangay</label>
                        <input type="text" name="present_barangay" id="present_barangay" class="setup-input" placeholder="Barangay" value="{{ old('present_barangay') }}">
                    </div>
                    <div class="setup-col" style="flex:1;">
                        <label class="setup-label">Zipcode</label>
                        <input type="text" name="present_zipcode" id="present_zipcode" class="setup-input" placeholder="Zipcode" value="{{ old('present_zipcode') }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Municipality/City</label>
                        <select name="present_municipality" id="present_municipality" class="setup-input setup-select">
                            <option value="">Choose City</option>
                            @foreach(['Pasig City','Makati City','Taguig City','Mandaluyong City','Marikina City','Pasay City','Paranaque City','Other'] as $city)
                            <option value="{{ $city }}" {{ old('present_municipality') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Province</label>
                        <select name="present_province" id="present_province" class="setup-input setup-select">
                            <option value="">Choose Province</option>
                            @foreach(['Metro Manila','Bulacan','Laguna','Rizal','Cavite','Other'] as $prov)
                            <option value="{{ $prov }}" {{ old('present_province') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Region</label>
                        <select name="present_region" id="present_region" class="setup-input setup-select">
                            <option value="">Choose Region</option>
                            @foreach(['NCR','Region I','Region II','Region III','Region IV-A','Region IV-B','Region V','Other'] as $reg)
                            <option value="{{ $reg }}" {{ old('present_region') === $reg ? 'selected' : '' }}>{{ $reg }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="setup-same-address">
                    <label class="setup-checkbox-label" for="sameAsPresent">
                        <input type="checkbox" id="sameAsPresent" name="same_as_present" value="1" {{ old('same_as_present') ? 'checked' : '' }}>
                        Same as Present Address
                    </label>
                </div>

                <h4 class="setup-subsection-title">Permanent Address</h4>

                <div class="setup-row" id="permanentAddressFields">
                    <div class="setup-col" style="flex:3;">
                        <label class="setup-label">Street</label>
                        <input type="text" name="permanent_street" id="permanent_street" class="setup-input" placeholder="Street" value="{{ old('permanent_street') }}">
                    </div>
                    <div class="setup-col" style="flex:2;">
                        <label class="setup-label">Barangay</label>
                        <input type="text" name="permanent_barangay" id="permanent_barangay" class="setup-input" placeholder="Barangay" value="{{ old('permanent_barangay') }}">
                    </div>
                    <div class="setup-col" style="flex:1;">
                        <label class="setup-label">Zipcode</label>
                        <input type="text" name="permanent_zipcode" id="permanent_zipcode" class="setup-input" placeholder="Zipcode" value="{{ old('permanent_zipcode') }}">
                    </div>
                </div>

                <div class="setup-row" id="permanentSelectFields">
                    <div class="setup-col">
                        <label class="setup-label">Municipality/City</label>
                        <select name="permanent_municipality" id="permanent_municipality" class="setup-input setup-select">
                            <option value="">Choose City</option>
                            @foreach(['Pasig City','Makati City','Taguig City','Mandaluyong City','Marikina City','Pasay City','Paranaque City','Other'] as $city)
                            <option value="{{ $city }}" {{ old('permanent_municipality') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Province</label>
                        <select name="permanent_province" id="permanent_province" class="setup-input setup-select">
                            <option value="">Choose Province</option>
                            @foreach(['Metro Manila','Bulacan','Laguna','Rizal','Cavite','Other'] as $prov)
                            <option value="{{ $prov }}" {{ old('permanent_province') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Region</label>
                        <select name="permanent_region" id="permanent_region" class="setup-input setup-select">
                            <option value="">Choose Region</option>
                            @foreach(['NCR','Region I','Region II','Region III','Region IV-A','Region IV-B','Region V','Other'] as $reg)
                            <option value="{{ $reg }}" {{ old('permanent_region') === $reg ? 'selected' : '' }}>{{ $reg }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="setup-nav">
                <div></div>
                <button type="button" class="btn-setup-next" data-go-step="2">Next</button>
            </div>
        </div>

        {{-- ===== STEP 2: EDUCATIONAL INFORMATION ===== --}}
        <div class="setup-form-container step-panel" id="step-2" style="display:none;">
            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Educational Information</h3>
                </div>

                <div class="setup-row">
                    <div class="setup-col" style="flex: 2;">
                        <label class="setup-label">Last School Attended</label>
                        <select class="setup-input setup-select">
                            <option value="">Select School</option>
                        </select>
                    </div>
                    <div class="setup-col" style="flex: 2.3;">
                        <label class="setup-label">School Address</label>
                        <input type="text" class="setup-input" placeholder="School Address">
                    </div>
                    <div class="setup-col" style="flex: 0.8; min-width: 110px;">
                        <label class="setup-label">School Type</label>
                        <select class="setup-input setup-select">
                            <option value="">Public</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                    <div class="setup-col setup-col-sm" style="min-width: 90px;">
                        <label class="setup-label">Year</label>
                        <input type="text" class="setup-input" placeholder="Year">
                    </div>
                    <div class="setup-col" style="flex: 0 0 34px; min-width: 34px;">
                        <label class="setup-label">&nbsp;</label>
                        <button type="button" class="setup-mini-add" aria-label="Add school">+</button>
                    </div>
                </div>
            </div>

            <div class="setup-nav">
                <button type="button" class="btn-setup-prev" data-go-step="1">Previous</button>
                <button type="button" class="btn-setup-next" data-go-step="3">Next</button>
            </div>
        </div>

        {{-- ===== STEP 3: FAMILY BACKGROUND ===== --}}
        <div class="setup-form-container step-panel" id="step-3" style="display:none;">
            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Family Background</h3>
                </div>

                <h4 class="setup-subsection-title">Mother/Guardian</h4>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Lastname</label><input type="text" class="setup-input" placeholder="Last Name"></div>
                    <div class="setup-col"><label class="setup-label">First Name</label><input type="text" class="setup-input" placeholder="First Name"></div>
                    <div class="setup-col"><label class="setup-label">Middle Name</label><input type="text" class="setup-input" placeholder="Middle Name"></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Nationality</label><select class="setup-input setup-select"><option value="">Filipino</option></select></div>
                    <div class="setup-col"><label class="setup-label">Religion</label><input type="text" class="setup-input" placeholder="Religion"></div>
                    <div class="setup-col"><label class="setup-label">Date of Birth</label><input type="date" class="setup-input"></div>
                    <div class="setup-col"><label class="setup-label">Mobile Number</label><input type="text" class="setup-input" placeholder="Mobile Number"></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Occupation</label><input type="text" class="setup-input" placeholder="Occupation"></div>
                    <div class="setup-col" style="flex: 1.4;"><label class="setup-label">Company Address</label><input type="text" class="setup-input" placeholder="Company Address"></div>
                    <div class="setup-col"><label class="setup-label">Estimated Monthly Income</label><input type="text" class="setup-input" placeholder="Estimated Monthly Income"></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col" style="flex: 1.6;"><label class="setup-label">Residence Address</label><input type="text" class="setup-input" placeholder="Residence Address"></div>
                    <div class="setup-col"><label class="setup-label">Email Address</label><input type="email" class="setup-input" placeholder="Email Address"></div>
                </div>

                <h4 class="setup-subsection-title" style="margin-top: 18px;">Father/Guardian</h4>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Lastname</label><input type="text" class="setup-input" placeholder="Last Name"></div>
                    <div class="setup-col"><label class="setup-label">First Name</label><input type="text" class="setup-input" placeholder="First Name"></div>
                    <div class="setup-col"><label class="setup-label">Middle Name</label><input type="text" class="setup-input" placeholder="Middle Name"></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Nationality</label><select class="setup-input setup-select"><option value="">Filipino</option></select></div>
                    <div class="setup-col"><label class="setup-label">Religion</label><input type="text" class="setup-input" placeholder="Religion"></div>
                    <div class="setup-col"><label class="setup-label">Date of Birth</label><input type="date" class="setup-input"></div>
                    <div class="setup-col"><label class="setup-label">Mobile Number</label><input type="text" class="setup-input" placeholder="Mobile Number"></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Occupation</label><input type="text" class="setup-input" placeholder="Occupation"></div>
                    <div class="setup-col" style="flex: 1.4;"><label class="setup-label">Company Address</label><input type="text" class="setup-input" placeholder="Company Address"></div>
                    <div class="setup-col"><label class="setup-label">Estimated Monthly Income</label><input type="text" class="setup-input" placeholder="Estimated Monthly Income"></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col" style="flex: 1.6;"><label class="setup-label">Residence Address</label><input type="text" class="setup-input" placeholder="Residence Address"></div>
                    <div class="setup-col"><label class="setup-label">Email Address</label><input type="email" class="setup-input" placeholder="Email Address"></div>
                </div>
            </div>

            <div class="setup-nav">
                <button type="button" class="btn-setup-prev" data-go-step="2">Previous</button>
                <button type="button" class="btn-setup-next" data-go-step="4">Next</button>
            </div>
        </div>

        {{-- ===== STEP 4: APPLYING FOR ===== --}}
        <div class="setup-form-container step-panel" id="step-4" style="display:none;">
            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Applying For</h3>
                </div>

                <div class="setup-row">
                    <div class="setup-col" style="flex: 1.2; min-width: 180px;">
                        <label class="setup-label">Program Type</label>
                        <div class="setup-radio-group" style="height:auto; gap:16px; margin-top: 4px;">
                            <label class="setup-radio"><input type="radio" name="apply_program" value="senior_high" checked> Senior High</label>
                            <label class="setup-radio"><input type="radio" name="apply_program" value="college"> College</label>
                        </div>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Strand</label>
                        <select class="setup-input setup-select">
                            <option value="">Select Strand</option>
                        </select>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Course</label>
                        <select class="setup-input setup-select">
                            <option value="">Select Course</option>
                        </select>
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Entry Classification</label>
                        <select class="setup-input setup-select"><option value="">Select School</option></select>
                    </div>
                    <div class="setup-col setup-col-sm" style="min-width: 130px;">
                        <label class="setup-label">Year Level</label>
                        <select class="setup-input setup-select"><option value="">Year Level</option></select>
                    </div>
                    <div class="setup-col setup-col-sm" style="min-width: 130px;">
                        <label class="setup-label">Semester</label>
                        <select class="setup-input setup-select"><option value="">First Semester</option></select>
                    </div>
                    <div class="setup-col setup-col-sm" style="min-width: 130px;">
                        <label class="setup-label">School Year</label>
                        <input type="text" class="setup-input" value="2025-2026" readonly>
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col setup-col-sm" style="min-width: 130px;">
                        <label class="setup-label">Application Date</label>
                        <input type="date" class="setup-input">
                    </div>
                    <div class="setup-col setup-col-sm" style="min-width: 130px;">
                        <label class="setup-label">Campus</label>
                        <input type="text" class="setup-input" value="Pasig" readonly>
                    </div>
                </div>
            </div>

            <div class="setup-nav">
                <button type="button" class="btn-setup-prev" data-go-step="3">Previous</button>
                <button type="submit" class="btn-setup-next">Submit</button>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script src="{{ asset('js/applicant-form.js') }}"></script>
@endpush
@endsection
