@extends('layouts.applicant')

@section('title', 'PLP - Application Form')

@section('content')
<div class="applicant-form-wrap">

    @if(session('success'))
    <div class="applicant-alert applicant-alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('applicant.application-form.save') }}" method="POST" enctype="multipart/form-data" id="applicationForm">
        @csrf

        {{-- =========================================================
             PERSONAL INFORMATION
        ========================================================= --}}
        <div class="applicant-section-card">
            <div class="applicant-section-title-row">
                <h2 class="applicant-section-title">Personal Information</h2>
                <div class="applicant-photo-upload">
                    <label for="photoInput" class="photo-upload-label">
                        @if($applicant->photo)
                            <img src="{{ asset('storage/' . $applicant->photo) }}" alt="Photo" class="photo-preview-img">
                        @else
                            <div class="photo-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                    <circle cx="12" cy="13" r="4"/>
                                </svg>
                                <span>Upload Photo</span>
                            </div>
                        @endif
                    </label>
                    <input type="file" id="photoInput" name="photo" accept="image/*" class="d-none">
                </div>
            </div>

            {{-- LRN --}}
            <div class="applicant-field-row">
                <div class="applicant-field-group full-width">
                    <label class="applicant-field-label">LRN</label>
                    <input type="text" name="lrn" class="applicant-input" placeholder="LRN" value="{{ old('lrn') }}">
                </div>
            </div>

            {{-- Name row --}}
            <div class="applicant-field-row">
                <div class="applicant-field-group">
                    <label class="applicant-field-label">LASTNAME</label>
                    <input type="text" name="last_name" class="applicant-input" placeholder="Last Name" value="{{ old('last_name') }}">
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">FIRST NAME</label>
                    <input type="text" name="first_name" class="applicant-input" placeholder="First Name" value="{{ old('first_name') }}">
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">MIDDLE NAME</label>
                    <input type="text" name="middle_name" class="applicant-input" placeholder="Middle Name" value="{{ old('middle_name') }}">
                </div>
                <div class="applicant-field-group narrow">
                    <label class="applicant-field-label">SUFFIX</label>
                    <input type="text" name="suffix" class="applicant-input" placeholder="Suffix" value="{{ old('suffix') }}">
                </div>
            </div>

            {{-- Nickname / Gender / Nationality / Religion / DOB --}}
            <div class="applicant-field-row">
                <div class="applicant-field-group">
                    <label class="applicant-field-label">NICKNAME</label>
                    <input type="text" name="nickname" class="applicant-input" placeholder="Nickname" value="{{ old('nickname') }}">
                </div>
                <div class="applicant-field-group" style="flex: 0 0 150px;">
                    <label class="applicant-field-label">GENDER</label>
                    <div class="applicant-gender-row">
                        <label class="applicant-radio-label">
                            <input type="radio" name="gender" value="Male" {{ old('gender') === 'Male' ? 'checked' : '' }}>
                            Male
                        </label>
                        <label class="applicant-radio-label">
                            <input type="radio" name="gender" value="Female" {{ old('gender') === 'Female' ? 'checked' : '' }}>
                            Female
                        </label>
                    </div>
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">NATIONALITY</label>
                    <select name="nationality" class="applicant-select">
                        @foreach(['Filipino','American','Japanese','Korean','Chinese','Other'] as $nat)
                        <option value="{{ $nat }}" {{ old('nationality') === $nat ? 'selected' : '' }}>{{ $nat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">RELIGION</label>
                    <select name="religion" class="applicant-select">
                        <option value="">Select religion</option>
                        @foreach(['Roman Catholic','Born Again Christian','Islam','Iglesia ni Cristo','Baptist','Seventh Day Adventist','Other'] as $rel)
                        <option value="{{ $rel }}" {{ old('religion') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">DATE OF BIRTH</label>
                    <input type="date" name="date_of_birth" class="applicant-input" value="{{ old('date_of_birth') }}">
                </div>
            </div>

            {{-- Place of birth / Age / Civil Status / Mobile / Email --}}
            <div class="applicant-field-row">
                <div class="applicant-field-group">
                    <label class="applicant-field-label">PLACE OF BIRTH</label>
                    <input type="text" name="place_of_birth" class="applicant-input" placeholder="Place of Birth" value="{{ old('place_of_birth') }}">
                </div>
                <div class="applicant-field-group narrow">
                    <label class="applicant-field-label">AGE</label>
                    <input type="number" name="age" class="applicant-input" placeholder="Age" value="{{ old('age') }}">
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">CIVIL STATUS</label>
                    <input type="text" name="civil_status" class="applicant-input" placeholder="Number of Siblings" value="{{ old('civil_status') }}">
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">MOBILE NUMBER</label>
                    <input type="text" name="mobile_number" class="applicant-input" placeholder="Mobile Number" value="{{ old('mobile_number') }}">
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">EMAIL ADDRESS</label>
                    <input type="email" name="email_address" class="applicant-input" placeholder="Email Address" value="{{ old('email_address') }}">
                </div>
            </div>
        </div>

        {{-- =========================================================
             RESIDENCE INFORMATION
        ========================================================= --}}
        <div class="applicant-section-card">
            <h2 class="applicant-section-title">Residence Information</h2>

            {{-- Present Address --}}
            <h3 class="applicant-subsection-title">Present Address</h3>

            <div class="applicant-field-row">
                <div class="applicant-field-group flex-3">
                    <label class="applicant-field-label">STREET</label>
                    <input type="text" name="present_street" class="applicant-input" placeholder="Last Name" value="{{ old('present_street') }}">
                </div>
                <div class="applicant-field-group flex-2">
                    <label class="applicant-field-label">BARANGAY</label>
                    <input type="text" name="present_barangay" class="applicant-input" placeholder="First Name" value="{{ old('present_barangay') }}">
                </div>
                <div class="applicant-field-group flex-1">
                    <label class="applicant-field-label">ZIPCODE</label>
                    <input type="text" name="present_zipcode" class="applicant-input" placeholder="Zipcode" value="{{ old('present_zipcode') }}">
                </div>
            </div>

            <div class="applicant-field-row">
                <div class="applicant-field-group">
                    <label class="applicant-field-label">MUNICIPALITY/CITY</label>
                    <select name="present_municipality" class="applicant-select">
                        <option value="">Choose your status</option>
                        @foreach(['Pasig City','Makati City','Taguig City','Mandaluyong City','Marikina City','Pasay City','Paranaque City','Other'] as $city)
                        <option value="{{ $city }}" {{ old('present_municipality') === $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">PROVINCE</label>
                    <select name="present_province" class="applicant-select">
                        <option value="">Department</option>
                        @foreach(['Metro Manila','Bulacan','Laguna','Rizal','Cavite','Other'] as $prov)
                        <option value="{{ $prov }}" {{ old('present_province') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">REGION</label>
                    <select name="present_region" class="applicant-select">
                        <option value="">Program</option>
                        @foreach(['NCR','Region I','Region II','Region III','Region IV-A','Region IV-B','Region V','Other'] as $reg)
                        <option value="{{ $reg }}" {{ old('present_region') === $reg ? 'selected' : '' }}>{{ $reg }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Same as present checkbox --}}
            <div class="applicant-same-check">
                <input type="checkbox" id="sameAsPresent" name="same_as_present" value="1"
                    {{ old('same_as_present') ? 'checked' : '' }}>
                <label for="sameAsPresent">Same as Present Address</label>
            </div>

            {{-- Permanent Address --}}
            <h3 class="applicant-subsection-title">Permanent Address</h3>

            <div class="applicant-field-row" id="permanentAddressFields">
                <div class="applicant-field-group flex-3">
                    <label class="applicant-field-label">STREET</label>
                    <input type="text" name="permanent_street" class="applicant-input" placeholder="Last Name" value="{{ old('permanent_street') }}">
                </div>
                <div class="applicant-field-group flex-2">
                    <label class="applicant-field-label">BARANGAY</label>
                    <input type="text" name="permanent_barangay" class="applicant-input" placeholder="First Name" value="{{ old('permanent_barangay') }}">
                </div>
                <div class="applicant-field-group flex-1">
                    <label class="applicant-field-label">ZIPCODE</label>
                    <input type="text" name="permanent_zipcode" class="applicant-input" placeholder="Zipcode" value="{{ old('permanent_zipcode') }}">
                </div>
            </div>

            <div class="applicant-field-row" id="permanentSelectFields">
                <div class="applicant-field-group">
                    <label class="applicant-field-label">MUNICIPALITY/CITY</label>
                    <select name="permanent_municipality" class="applicant-select">
                        <option value="">Choose your status</option>
                        @foreach(['Pasig City','Makati City','Taguig City','Mandaluyong City','Marikina City','Pasay City','Paranaque City','Other'] as $city)
                        <option value="{{ $city }}" {{ old('permanent_municipality') === $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">PROVINCE</label>
                    <select name="permanent_province" class="applicant-select">
                        <option value="">Department</option>
                        @foreach(['Metro Manila','Bulacan','Laguna','Rizal','Cavite','Other'] as $prov)
                        <option value="{{ $prov }}" {{ old('permanent_province') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="applicant-field-group">
                    <label class="applicant-field-label">REGION</label>
                    <select name="permanent_region" class="applicant-select">
                        <option value="">Program</option>
                        @foreach(['NCR','Region I','Region II','Region III','Region IV-A','Region IV-B','Region V','Other'] as $reg)
                        <option value="{{ $reg }}" {{ old('permanent_region') === $reg ? 'selected' : '' }}>{{ $reg }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Next / Submit --}}
        <div class="applicant-form-footer">
            <button type="submit" class="btn-applicant-next">Next</button>
        </div>

    </form>
</div>

@push('scripts')
<script src="{{ asset('js/applicant-form.js') }}"></script>
@endpush
@endsection
