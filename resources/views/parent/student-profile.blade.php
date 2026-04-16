@extends('layouts.parent')

@section('title', 'PLP - Parent Student Profile')
@section('page-title', 'STUDENT PROFILE')

@section('content')
<div class="parent-student-profile-page">
    @php
        $studentNo = $student && !empty($student->student_no) ? $student->student_no : '232418456';
        $studentName = $student && !empty($student->name) ? $student->name : trim(($nameParts['first'] ?? 'Aleya Mae') . ' ' . ($nameParts['middle'] ?? 'T.') . ' ' . ($nameParts['last'] ?? 'Garma'));
        $contactNo = $student && !empty($student->contact_no) ? $student->contact_no : '09123456789';
        $email = $student && !empty($student->email) ? $student->email : (optional($user)->email ?: 'test@gmail.com');
        $residentialAddress = $student && !empty($student->address) ? $student->address : 'Travesia, Guinobatan, Albay';
        $permanentAddress = $student && !empty($student->permanent_address) ? $student->permanent_address : $residentialAddress;
        $region = $student && !empty($student->region) ? $student->region : 'V-Bicol Region';
        $province = $student && !empty($student->province) ? $student->province : 'Albay';
        $municipality = $student && !empty($student->municipality) ? $student->municipality : 'Guinobatan';
        $birthDate = $student && !empty($student->birthdate) ? $student->birthdate : 'July 28,2004';
        $birthPlace = $student && !empty($student->birthplace) ? $student->birthplace : 'Daraga, Albay';
        $gender = $student && !empty($student->gender) ? $student->gender : 'Female';
        $religion = $student && !empty($student->religion) ? $student->religion : 'Roman Catholic';
        $citizenship = $student && !empty($student->citizenship) ? $student->citizenship : 'Filipino';
        $civilStatus = $student && !empty($student->civil_status) ? $student->civil_status : 'Single';
        $height = $student && !empty($student->height) ? $student->height : '5\'4"';
        $weight = $student && !empty($student->weight) ? $student->weight : '50';
        $bloodType = $student && !empty($student->blood_type) ? $student->blood_type : 'B+';
        $program = $student && !empty($student->program) ? $student->program : 'Bachelor Of Science Marine Transportation';
        $yearLevel = $student && !empty($student->year_level) ? $student->year_level : 'Fourth Year';
        $section = $student && !empty($student->section) ? $student->section : 'A';
        $curriculumYear = $student && !empty($student->curriculum_year) ? $student->curriculum_year : '2023-2024';
        $admissionStatus = $student && !empty($student->admission_status) ? $student->admission_status : 'OLD';
        $admissionYear = $student && !empty($student->admission_year) ? $student->admission_year : '2023-2024';
        $enrollmentStatus = $student && !empty($student->enrollment_status) ? $student->enrollment_status : 'Regular';
        $academicStatus = $student && !empty($student->academic_status) ? $student->academic_status : 'Regular';
    @endphp

    <div class="parent-student-picker">
        <label class="app-filter-label">STUDENT</label>
        <select class="form-select form-input-long" disabled>
            @if(!empty($children) && count($children) > 0)
                @foreach($children as $child)
                    <option{{ $child['student_no'] === $studentNo ? ' selected' : '' }}>
                        {{ $child['name'] ?: $studentName }}{{ $child['student_no'] ? ' (' . $child['student_no'] . ')' : '' }}
                    </option>
                @endforeach
            @else
                <option selected>{{ $studentName }} ({{ $studentNo }})</option>
            @endif
        </select>
    </div>

    <section class="parent-student-card">
        <div class="parent-student-profile-grid parent-student-profile-grid--top">
            <div class="parent-student-field parent-student-field--sm">
                <label>STUDENT NO.</label>
                <input type="text" value="{{ $studentNo }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>STUDENT NAME</label>
                <input type="text" value="{{ $studentName }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>CONTACT NO.</label>
                <input type="text" value="{{ $contactNo }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>EMAIL ADDRESS</label>
                <input type="email" value="{{ $email }}" readonly>
            </div>
        </div>

        <div class="parent-student-profile-grid parent-student-profile-grid--address">
            <div class="parent-student-field parent-student-field--wide">
                <label>RESIDENTIAL ADDRESS</label>
                <input type="text" value="{{ $residentialAddress }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>REGION</label>
                <input type="text" value="{{ $region }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>PROVINCE</label>
                <input type="text" value="{{ $province }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>MUNICIPALITY</label>
                <input type="text" value="{{ $municipality }}" readonly>
            </div>
        </div>

        <div class="parent-student-profile-grid parent-student-profile-grid--bio">
            <div class="parent-student-field parent-student-field--wide">
                <label>PERMANENT ADDRESS</label>
                <input type="text" value="{{ $permanentAddress }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>DATE OF BIRTH</label>
                <input type="text" value="{{ $birthDate }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>PLACE OF BIRTH</label>
                <input type="text" value="{{ $birthPlace }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>GENDER</label>
                <input type="text" value="{{ $gender }}" readonly>
            </div>
        </div>

        <div class="parent-student-profile-grid parent-student-profile-grid--identity">
            <div class="parent-student-field">
                <label>RELIGION</label>
                <input type="text" value="{{ $religion }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>CITIZENSHIP</label>
                <input type="text" value="{{ $citizenship }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>CIVIL STATUS</label>
                <input type="text" value="{{ $civilStatus }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>HEIGHT (IN FEET &amp; INCHES)</label>
                <input type="text" value="{{ $height }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>WEIGHT (IN POUNDS)</label>
                <input type="text" value="{{ $weight }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>BLOOD TYPE</label>
                <input type="text" value="{{ $bloodType }}" readonly>
            </div>
        </div>

        <div class="parent-student-divider"></div>

        <div class="parent-student-profile-grid parent-student-profile-grid--acad1">
            <div class="parent-student-field parent-student-field--wide">
                <label>COURSE / PROGRAM</label>
                <input type="text" value="{{ $program }}" readonly>
            </div>
        </div>

        <div class="parent-student-profile-grid parent-student-profile-grid--acad2">
            <div class="parent-student-field">
                <label>YEAR LEVEL</label>
                <input type="text" value="{{ $yearLevel }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>SECTION</label>
                <input type="text" value="{{ $section }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>CURRICULUM YEAR</label>
                <input type="text" value="{{ $curriculumYear }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>ADMISSION STATUS</label>
                <input type="text" value="{{ $admissionStatus }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>ADMISSION YEAR</label>
                <input type="text" value="{{ $admissionYear }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>ENROLLMENT STATUS</label>
                <input type="text" value="{{ $enrollmentStatus }}" readonly>
            </div>
            <div class="parent-student-field">
                <label>ACADEMIC STATUS</label>
                <input type="text" value="{{ $academicStatus }}" readonly>
            </div>
        </div>

        <div class="parent-student-divider"></div>

        <div class="parent-student-transfer-row">
            <label class="parent-student-transfer-check">
                <input type="checkbox" disabled>
                <span>TAG THIS STUDENT AS TRANSFER</span>
            </label>
        </div>

        <div class="parent-student-profile-grid parent-student-profile-grid--transfer">
            <div class="parent-student-field parent-student-field--wide">
                <label>NAME OF SCHOOL</label>
                <input type="text" value="Regular" readonly>
            </div>
            <div class="parent-student-field">
                <label>DATE TRANSFER</label>
                <input type="text" value="mm/dd/yy" readonly>
            </div>
            <div class="parent-student-field parent-student-field--wide">
                <label>REASON</label>
                <input type="text" value="" readonly>
            </div>
        </div>
    </section>
</div>
@endsection
