@extends('layouts.student')

@section('title', 'PLP - Profile')
@section('page-title', 'PROFILE')
@section('body-class', 'page-profile-view')

@section('content')
@php
    $fullName      = trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? '') . ($profile->suffix ? ', '.$profile->suffix : ''));
    $displayName   = strtoupper($fullName);
    $studentNo     = $profile->student_no ?? '—';
    $photoUrl      = $profile->profile_photo_path ? asset('storage/' . $profile->profile_photo_path) : null;

    $presentAddr   = collect([
        $profile->present_street,
        $profile->present_barangay,
        $profile->present_municipality,
        $profile->present_province,
        $profile->present_region,
    ])->filter()->implode(', ');

    $permanentAddr = collect([
        $profile->permanent_street,
        $profile->permanent_barangay,
        $profile->permanent_municipality,
        $profile->permanent_province,
        $profile->permanent_region,
    ])->filter()->implode(', ');

    $nationality   = $profile->nationality === 'Other'   ? ($profile->nationality_other  ?? '—') : ($profile->nationality  ?? '—');
    $religion      = $profile->religion    === 'Other'   ? ($profile->religion_other     ?? '—') : ($profile->religion     ?? '—');
    $incomeSource  = $profile->family_income_source  === 'Others' ? ($profile->family_income_source_other  ?? '—') : ($profile->family_income_source  ?? '—');
    $livingSit     = $profile->living_situation      === 'Others' ? ($profile->living_situation_other      ?? '—') : ($profile->living_situation      ?? '—');
    $lmsUsed       = $profile->lms_used       === 'Others' ? ($profile->lms_used_other      ?? '—') : ($profile->lms_used      ?? '—');
    $lmsPreferred  = $profile->lms_preferred  === 'Others' ? ($profile->lms_preferred_other ?? '—') : ($profile->lms_preferred  ?? '—');

    $devsList      = $profile->devices     ? collect(json_decode($profile->devices, true))->map(function($d) use ($profile) { return $d === 'Others' && $profile->devices_other ? $profile->devices_other : $d; })->implode(', ') : '—';
    $lmsReasons    = $profile->lms_reasons ? collect(json_decode($profile->lms_reasons, true))->map(function($r) use ($profile) { return $r === 'Others' && $profile->lms_reasons_other ? $profile->lms_reasons_other : $r; })->implode(', ') : '—';

    $motherName    = trim(($profile->mother_firstname ?? '') . ' ' . ($profile->mother_middlename ?? '') . ' ' . ($profile->mother_lastname ?? ''));
    $fatherName    = trim(($profile->father_firstname ?? '') . ' ' . ($profile->father_middlename ?? '') . ' ' . ($profile->father_lastname ?? ''));
    $guardianName  = trim(($profile->guardian_firstname ?? '') . ' ' . ($profile->guardian_middlename ?? '') . ' ' . ($profile->guardian_lastname ?? ''));

    $courseBlock    = trim((optional($student)->program ?? '') . ' ' . (optional($student)->year_level ?? ''));
    $departmentRaw  = optional($student)->college ?? '—';
    $departmentTrim = preg_replace('/^College of\s+/i', '', (string) $departmentRaw);
    $department     = trim((string) ($departmentTrim !== '' ? $departmentTrim : $departmentRaw));
@endphp

<div class="pv-page">

    <div class="pv-layout">

        {{-- ===== LEFT SIDEBAR ===== --}}
        <div class="pv-sidebar">
            <div class="pv-sidebar-header">
                <div class="pv-avatar-wrap">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Profile Photo" class="pv-avatar">
                    @else
                        <div class="pv-avatar-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="pv-sidebar-info">
                    <h2 class="pv-name">{{ $displayName }}</h2>
                    <p class="pv-student-id">{{ $studentNo }}</p>
                </div>
            </div>

            <hr class="pv-divider">

            <div class="pv-meta-grid">
                <div class="pv-meta-row">
                    <span class="pv-meta-label">DEPARTMENT</span>
                    <span class="pv-meta-value pv-meta-department" title="{{ $department }}">{{ $department }}</span>
                </div>
                <div class="pv-meta-row">
                    <span class="pv-meta-label">UNIVERSITY EMAIL</span>
                    <span class="pv-meta-value pv-meta-email" title="{{ $profile->student_email ?? '—' }}">{{ $profile->student_email ?? '—' }}</span>
                </div>
                <div class="pv-meta-row">
                    <span class="pv-meta-label">COURSE, BLOCK &amp; YR</span>
                    <span class="pv-meta-value pv-meta-course" title="{{ $courseBlock }}">{{ $courseBlock }}</span>
                </div>
            </div>

            <div class="pv-btn-group">
                <a href="{{ route('student.profile.edit') }}" class="pv-btn pv-btn-edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </a>
                <a href="{{ route('student.section-offering') }}" class="pv-btn pv-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back
                </a>
            </div>
        </div>

        {{-- ===== RIGHT CONTENT ===== --}}
        <div class="pv-body">
            <h2 class="pv-body-title">PROFILE INFORMATION</h2>

            {{-- Tabs --}}
            <div class="pv-tabs" role="tablist">
                <button class="pv-tab active" data-target="pv-personal" role="tab">Personal Information</button>
                <button class="pv-tab" data-target="pv-family" role="tab">Family/Guardian Information</button>
                <button class="pv-tab" data-target="pv-educational" role="tab">Educational Information</button>
            </div>

            {{-- ===== TAB: PERSONAL ===== --}}
            <div class="pv-panel active" id="pv-personal">
              <div class="pv-panel-card">
                @if($profile->lrn)
                <div class="pv-row">
                    <span class="pv-label">LRN</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->lrn }}</span>
                </div>
                @endif
                <div class="pv-row">
                    <span class="pv-label">NAME</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $fullName ?: '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">NICKNAME</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->nickname ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">GENDER</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->gender ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">NATIONALITY</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $nationality }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">RELIGION</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $religion }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">DATE OF BIRTH</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->date_of_birth ? $profile->date_of_birth->format('F d, Y') : '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">PLACE OF BIRTH</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->place_of_birth ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">CIVIL STATUS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->civil_status ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">MOBILE NUMBER</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->mobile_number ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">EMAIL ADDRESS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->student_email ?? '—' }}</span>
                </div>

                <hr class="pv-section-rule">

                <div class="pv-row pv-row--multiline">
                    <span class="pv-label">PRESENT ADDRESS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ strtoupper($presentAddr ?: '—') }}</span>
                </div>
                <div class="pv-row pv-row--multiline">
                    <span class="pv-label">PERMANENT ADDRESS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ strtoupper($permanentAddr ?: '—') }}</span>
                </div>
              </div>
            </div>

            {{-- ===== TAB: FAMILY/GUARDIAN ===== --}}
            <div class="pv-panel" id="pv-family">
              <div class="pv-panel-card">
                <div class="pv-sub-heading">Mother's Information</div>
                <div class="pv-row">
                    <span class="pv-label">FULL NAME</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $motherName ?: '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">CONTACT NUMBER</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->mother_contact ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">OCCUPATION</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->mother_occupation ?? '—' }}{{ $profile->mother_pensioner ? ' (Pensioner)' : '' }}</span>
                </div>

                <hr class="pv-section-rule">
                <div class="pv-sub-heading">Father's Information</div>
                <div class="pv-row">
                    <span class="pv-label">FULL NAME</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $fatherName ?: '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">CONTACT NUMBER</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->father_contact ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">OCCUPATION</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->father_occupation ?? '—' }}{{ $profile->father_pensioner ? ' (Pensioner)' : '' }}</span>
                </div>

                <hr class="pv-section-rule">
                <div class="pv-sub-heading">Guardian's Information</div>
                <div class="pv-row">
                    <span class="pv-label">FULL NAME</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $guardianName ?: '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">CONTACT NUMBER</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->guardian_contact ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">OCCUPATION</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->guardian_occupation ?? '—' }}</span>
                </div>
                <div class="pv-row pv-row--multiline">
                    <span class="pv-label">ADDRESS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->guardian_address ?? '—' }}</span>
                </div>

                <hr class="pv-section-rule">
                <div class="pv-sub-heading">Household Information</div>
                <div class="pv-row">
                    <span class="pv-label">PARENT MARITAL STATUS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->parent_marital_status ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">MONTHLY FAMILY INCOME</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->monthly_family_income ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">FAMILY INCOME SOURCE</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $incomeSource }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">LIVING SITUATION</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $livingSit }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">NO. OF SIBLINGS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->number_of_siblings ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">HOUSEHOLD MEMBERS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->household_members ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">DEPENDENTS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->dependents ?? '—' }}</span>
                </div>
              </div>
            </div>

            {{-- ===== TAB: EDUCATIONAL ===== --}}
            <div class="pv-panel" id="pv-educational">
              <div class="pv-panel-card">
                <div class="pv-row">
                    <span class="pv-label">JUNIOR HIGH SCHOOL</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->junior_school ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">SENIOR HIGH SCHOOL</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->senior_school ?? '—' }}</span>
                </div>

                @if(!$profile->no_k12)
                <div class="pv-row">
                    <span class="pv-label">TRACK / STRAND</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->shs_track_strand ?? '—' }}</span>
                </div>
                @endif

                <hr class="pv-section-rule">
                <div class="pv-sub-heading">Technology &amp; LMS Access</div>
                <div class="pv-row">
                    <span class="pv-label">WORKING STUDENT</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->working_student ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">HAS SCHOLARSHIP</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->has_scholarship ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">INTERNET ACCESS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->internet_access ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">IT TOOLS ACCESS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->it_tools_access ?? '—' }}</span>
                </div>
                <div class="pv-row pv-row--multiline">
                    <span class="pv-label">DEVICES USED</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $devsList }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">LMS CURRENTLY USED</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $lmsUsed }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">PREFERRED LMS</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $lmsPreferred }}</span>
                </div>
                <div class="pv-row pv-row--multiline">
                    <span class="pv-label">REASON FOR LMS PREFERENCE</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $lmsReasons }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">PREFERRED CLASS TIME</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->preferred_class_time ?? '—' }}</span>
                </div>
                <div class="pv-row">
                    <span class="pv-label">EVENING CLASSES</span>
                    <span class="pv-sep">:</span>
                    <span class="pv-value">{{ $profile->evening_classes ?? '—' }}</span>
                </div>
              </div>
            </div>

        </div>{{-- /.pv-body --}}
    </div>{{-- /.pv-layout --}}
</div>{{-- /.pv-page --}}
@endsection

@push('scripts')
<script src="{{ asset('js/profile-view.js') }}"></script>
@if(session('success'))
<script>
(function() {
    var toast = document.getElementById('download-toast');
    if (!toast) return;
    var msg = toast.querySelector('.toast-message');
    var closeBtn = toast.querySelector('.toast-close');
    if (msg) msg.textContent = {!! json_encode(session('success')) !!};
    toast.classList.remove('toast-error');
    toast.classList.add('show');
    if (closeBtn && !closeBtn.dataset.pvBound) {
        closeBtn.addEventListener('click', function() { toast.classList.remove('show'); });
        closeBtn.dataset.pvBound = '1';
    }
    setTimeout(function() { toast.classList.remove('show'); }, 3000);
})();
</script>
@endif
@endpush
