@extends('layouts.parent')

@section('title', 'PLP - Parent Student Profile')
@section('page-title', 'STUDENT PROFILE')

@section('content')
@php
    $profileData = $profileData ?? [];
    $selectedChildValue = (string) ($selectedChildId ?? '');
    $studentFieldVisibility = $studentFieldVisibility ?? [];
    $showSection = !empty($studentFieldVisibility['section']);
    $showAdmissionStatus = !empty($studentFieldVisibility['admission_status']);
    $showAdmissionYear = !empty($studentFieldVisibility['admission_year']);
    $showEnrollmentStatus = !empty($studentFieldVisibility['enrollment_status']);
    $showAcademicStatus = !empty($studentFieldVisibility['academic_status']);

    $childOptions = collect($children ?? [])->map(function ($child) {
        $studentName = trim((string) ($child['name'] ?? ''));
        $studentNo = trim((string) ($child['student_no'] ?? ''));
        $label = $studentName !== '' ? $studentName : 'Linked Student';

        if ($studentNo !== '') {
            $label .= ' (' . $studentNo . ')';
        }

        return [
            'value' => (string) ($child['id'] ?? ''),
            'label' => $label,
        ];
    })->filter(function ($option) {
        return trim((string) $option['value']) !== '';
    })->values()->all();

    $display = function ($key) use ($profileData) {
        return (string) data_get($profileData, $key, 'N/A');
    };
@endphp

<div class="parent-student-profile-page">
    <form method="GET" action="{{ route('parent.student-profile') }}" class="parent-student-picker" id="parentStudentProfileFilterForm">
        <label class="app-filter-label" for="parentStudentProfileChild">STUDENT</label>
        @include('components.applicant-select', [
            'id' => 'parentStudentProfileChild',
            'name' => 'child',
            'options' => $childOptions,
            'selected' => $selectedChildValue,
            'placeholder' => 'Select linked student',
            'wrapperClass' => 'parent-student-profile-child-wrap',
            'inputClass' => 'form-input-long'
        ])
    </form>

    @if(!$student)
        <section class="parent-student-card">
            <p class="parent-empty-note">No linked student profile is available for this account yet.</p>
        </section>
    @else
        <section class="parent-student-card">
            <div class="parent-student-top-wrap">
                <div class="parent-student-profile-grid parent-student-profile-grid--top">
                    <div class="parent-student-field parent-student-field--sm">
                        <label>STUDENT NO.</label>
                        <input type="text" value="{{ $display('student_no') }}" readonly>
                    </div>
                    <div class="parent-student-field">
                        <label>STUDENT NAME</label>
                        <input type="text" value="{{ $display('student_name') }}" readonly>
                    </div>
                    <div class="parent-student-field">
                        <label>CONTACT NO.</label>
                        <input type="text" value="{{ $display('contact_no') }}" readonly>
                    </div>
                    <div class="parent-student-field parent-student-field--top-email">
                        <label>EMAIL ADDRESS</label>
                        <input type="text" value="{{ $display('email') }}" readonly>
                    </div>
                </div>

                <aside class="parent-student-profile-panel" aria-label="Student profile photo panel">
                    <span class="parent-student-profile-label">PROFILE</span>
                    @if(!empty($profileData['photo_url']))
                        <img src="{{ $profileData['photo_url'] }}" alt="Student profile photo" class="parent-student-profile-photo">
                        <p>Student Profile</p>
                    @else
                        <div class="parent-student-profile-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h3l2-2h4l2 2h3a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                        </div>
                        <p>No Photo Uploaded</p>
                    @endif
                </aside>
            </div>

            <div class="parent-student-profile-grid parent-student-profile-grid--address">
                <div class="parent-student-field parent-student-field--wide">
                    <label>RESIDENTIAL ADDRESS</label>
                    <input type="text" value="{{ $display('residential_address') }}" readonly>
                </div>
                <div class="parent-student-field">
                    <label>REGION</label>
                    <input type="text" value="{{ $display('present_region') }}" readonly>
                </div>
                <div class="parent-student-field">
                    <label>PROVINCE</label>
                    <input type="text" value="{{ $display('present_province') }}" readonly>
                </div>
                <div class="parent-student-field">
                    <label>MUNICIPALITY</label>
                    <input type="text" value="{{ $display('present_municipality') }}" readonly>
                </div>
            </div>

            <div class="parent-student-profile-grid parent-student-profile-grid--bio">
                <div class="parent-student-field parent-student-field--wide">
                    <label>PERMANENT ADDRESS</label>
                    <input type="text" value="{{ $display('permanent_address') }}" readonly>
                </div>
                <div class="parent-student-field">
                    <label>DATE OF BIRTH</label>
                    <input type="text" value="{{ $display('date_of_birth') }}" readonly>
                </div>
                <div class="parent-student-field">
                    <label>PLACE OF BIRTH</label>
                    <input type="text" value="{{ $display('place_of_birth') }}" readonly>
                </div>
                <div class="parent-student-field">
                    <label>GENDER</label>
                    <input type="text" value="{{ $display('gender') }}" readonly>
                </div>
            </div>

            <div class="parent-student-profile-grid parent-student-profile-grid--identity">
                <div class="parent-student-field">
                    <label>RELIGION</label>
                    <input type="text" value="{{ $display('religion') }}" readonly>
                </div>
                <div class="parent-student-field">
                    <label>CITIZENSHIP</label>
                    <input type="text" value="{{ $display('citizenship') }}" readonly>
                </div>
                <div class="parent-student-field">
                    <label>CIVIL STATUS</label>
                    <input type="text" value="{{ $display('civil_status') }}" readonly>
                </div>
                @if($showAdmissionStatus)
                    <div class="parent-student-field">
                        <label>ADMISSION STATUS</label>
                        <input type="text" value="{{ $display('admission_status') }}" readonly>
                    </div>
                @endif
                @if($showAdmissionYear)
                    <div class="parent-student-field">
                        <label>ADMISSION YEAR</label>
                        <input type="text" value="{{ $display('admission_year') }}" readonly>
                    </div>
                @endif
                @if($showEnrollmentStatus)
                    <div class="parent-student-field">
                        <label>ENROLLMENT STATUS</label>
                        <input type="text" value="{{ $display('enrollment_status') }}" readonly>
                    </div>
                @endif
            </div>

            <div class="parent-student-divider"></div>

            <div class="parent-student-profile-grid parent-student-profile-grid--acad2">
                <div class="parent-student-field parent-student-field--program">
                    <label>COURSE / PROGRAM</label>
                    <input type="text" value="{{ $display('program') }}" readonly>
                </div>
                <div class="parent-student-field parent-student-field--acad-compact">
                    <label>YEAR LEVEL</label>
                    <input type="text" value="{{ $display('year_level') }}" readonly>
                </div>
                @if($showSection)
                    <div class="parent-student-field parent-student-field--acad-compact">
                        <label>SECTION</label>
                        <input type="text" value="{{ $display('section') }}" readonly>
                    </div>
                @endif
                <div class="parent-student-field">
                    <label>CURRICULUM YEAR</label>
                    <input type="text" value="{{ $display('curriculum_year') }}" readonly>
                </div>
                @if($showAcademicStatus)
                    <div class="parent-student-field parent-student-field--acad-status">
                        <label>ACADEMIC STATUS</label>
                        <input type="text" value="{{ $display('academic_status') }}" readonly>
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>

@push('scripts')
<script src="{{ mix('js/applicant-select.js') }}"></script>
<script src="{{ mix('js/parent-student-profile.js') }}"></script>
@endpush
@endsection
