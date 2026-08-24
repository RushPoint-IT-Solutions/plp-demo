@extends('layouts.faculty')

@section('title', 'PLP - Faculty Profile')
@section('page-title', 'FACULTY PROFILE')
@section('body-class', 'page-profile-view')

@section('content')
@php
    $state = is_array($formState ?? null) ? $formState : [];
    $rows = is_array($detailRows ?? null) ? $detailRows : [];

    $displayName = strtoupper((string) (isset($state['name']) && $state['name'] !== '' ? $state['name'] : optional($faculty)->name));
    $code = optional($profileRow)->code ?? optional($faculty)->code;
    $photoPath = isset($state['profile_photo_path']) ? $state['profile_photo_path'] : null;
    $photoUrl = $photoPath ? asset('storage/' . $photoPath) : null;
    $employmentTypeValue = isset($state['employment_type']) ? $state['employment_type'] : (optional($faculty)->employment_type ?? 'Full Time');
    $employmentType = stripos((string) $employmentTypeValue, 'part') !== false ? 'Part Time' : 'Full Time';

    $employment = [
        'Employment Type' => $employmentType,
        'Position' => isset($state['position']) ? $state['position'] : '—',
        'Office Department' => isset($state['department']) ? $state['department'] : '—',
        'Parent College' => isset($state['parent_college']) ? $state['parent_college'] : '—',
        'Accreditation' => isset($state['accreditation']) ? $state['accreditation'] : '—',
        'Date of Employment' => isset($state['date_of_employment']) ? $state['date_of_employment'] : '—',
        'Date of Permanent Status' => isset($state['date_of_permanent_status']) ? $state['date_of_permanent_status'] : '—',
        'Parent Course' => isset($state['parent_course']) ? $state['parent_course'] : '—',
        'Regular Load' => isset($state['regular_load']) ? $state['regular_load'] : '—',
        'Over Load' => isset($state['over_load']) ? $state['over_load'] : '—',
        'Professional Licensure Passed' => isset($state['professional_licensure_passed']) ? $state['professional_licensure_passed'] : '—',
        'Annual Salary' => isset($state['annual_salary']) ? $state['annual_salary'] : '—',
    ];

    $personal = [
        'Faculty Code' => $code ?: '—',
        'Full Name' => isset($state['name']) ? $state['name'] : '—',
        'Phone No.' => isset($state['mobile_no']) ? $state['mobile_no'] : '—',
        'Email Address' => isset($state['email_address']) ? $state['email_address'] : '—',
        'Gender' => isset($state['gender']) ? $state['gender'] : '—',
        'Tel No.' => isset($state['tel_no']) ? $state['tel_no'] : '—',
        'Date of Birth' => isset($state['date_of_birth']) ? $state['date_of_birth'] : '—',
        'Place of Birth' => isset($state['place_of_birth']) ? $state['place_of_birth'] : '—',
        'Religion' => isset($state['religion']) ? $state['religion'] : '—',
        'Citizenship' => isset($state['citizenship']) ? $state['citizenship'] : '—',
        'Civil Status' => isset($state['civil_status']) ? $state['civil_status'] : '—',
        'Address' => isset($state['complete_address']) ? $state['complete_address'] : '—',
        'Spouse Name' => isset($state['spouse_name']) ? $state['spouse_name'] : '—',
        'Spouse Occupation' => isset($state['spouse_occupation']) ? $state['spouse_occupation'] : '—',
        'Dependents' => isset($state['dependents']) ? $state['dependents'] : '—',
        'Father\'s Name' => isset($state['father_name']) ? $state['father_name'] : '—',
        'Father\'s Occupation' => isset($state['father_occupation']) ? $state['father_occupation'] : '—',
        'Mother\'s Name' => isset($state['mother_name']) ? $state['mother_name'] : '—',
        'Mother\'s Occupation' => isset($state['mother_occupation']) ? $state['mother_occupation'] : '—',
        'SSS #' => isset($state['sss_no']) ? $state['sss_no'] : '—',
        'TIN #' => isset($state['tin_no']) ? $state['tin_no'] : '—',
        'PhilHealth #' => isset($state['philhealth_no']) ? $state['philhealth_no'] : '—',
        'PAG-IBIG #' => isset($state['pagibig_no']) ? $state['pagibig_no'] : '—',
    ];

    $educationRows = isset($rows['education']) && is_array($rows['education']) ? $rows['education'] : [];
    $registrationRows = isset($rows['registration']) && is_array($rows['registration']) ? $rows['registration'] : [];
    $organizationRows = isset($rows['organization']) && is_array($rows['organization']) ? $rows['organization'] : [];
    $workRows = isset($rows['work']) && is_array($rows['work']) ? $rows['work'] : [];
    $trainingRows = isset($rows['training']) && is_array($rows['training']) ? $rows['training'] : [];
@endphp

<div class="pv-page">
    <div class="pv-layout">
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
                    <h2 class="pv-name">{{ $displayName !== '' ? $displayName : 'FACULTY MEMBER' }}</h2>
                    <p class="pv-student-id">{{ $code ?: '—' }}</p>
                </div>
            </div>

            <hr class="pv-divider">

            <div class="pv-meta-grid">
                <div class="pv-meta-row">
                    <span class="pv-meta-label">EMPLOYMENT TYPE</span>
                    <span class="pv-meta-value pv-meta-status">{{ $employmentType }}</span>
                </div>
                <div class="pv-meta-row">
                    <span class="pv-meta-label">DEPARTMENT</span>
                    <span class="pv-meta-value pv-meta-department" title="{{ isset($state['department']) ? $state['department'] : '—' }}">{{ isset($state['department']) ? $state['department'] : '—' }}</span>
                </div>
                <div class="pv-meta-row">
                    <span class="pv-meta-label">POSITION</span>
                    <span class="pv-meta-value pv-meta-position">{{ isset($state['position']) ? $state['position'] : '—' }}</span>
                </div>
            </div>

            <div class="pv-btn-group">
                <a href="{{ route('faculty.profile.edit') }}" class="pv-btn pv-btn-edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </a>
                <a href="{{ route('faculty.class-list') }}" class="pv-btn pv-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back
                </a>
            </div>
        </div>

        <div class="pv-body">
            <h2 class="pv-body-title">PROFILE INFORMATION</h2>

            <div class="pv-tabs" role="tablist">
                <button class="pv-tab active" data-target="pv-personal" role="tab">Personal Information</button>
                <button class="pv-tab" data-target="pv-employment" role="tab">Employment Data</button>
                <button class="pv-tab" data-target="pv-educational" role="tab">Educational &amp; Academic Background</button>
            </div>

            <div class="pv-panel active" id="pv-personal">
                <div class="pv-panel-card">
                    @foreach($personal as $label => $value)
                    <div class="pv-row">
                        <span class="pv-label">{{ strtoupper($label) }}</span>
                        <span class="pv-sep">:</span>
                        <span class="pv-value">{{ $value !== '' ? $value : '—' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="pv-panel" id="pv-employment">
                <div class="pv-panel-card">
                    @foreach($employment as $label => $value)
                    <div class="pv-row">
                        <span class="pv-label">{{ strtoupper($label) }}</span>
                        <span class="pv-sep">:</span>
                        <span class="pv-value">{{ $value !== '' ? $value : '—' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="pv-panel" id="pv-educational">
                <div class="pv-panel-card">
                    @php
                        $academicSections = [
                            [
                                'title' => 'Educational Background',
                                'rows' => $educationRows,
                                'fields' => [
                                    'Level' => 'level',
                                    'School' => 'schoolName',
                                    'Course/Degree' => 'courseDegree',
                                    'Graduated' => 'dateGraduated',
                                ],
                            ],
                            [
                                'title' => 'Professional Registration',
                                'rows' => $registrationRows,
                                'fields' => [
                                    'Registration' => 'name',
                                    'Rating' => 'rating',
                                    'Date' => 'date',
                                ],
                            ],
                            [
                                'title' => 'Professional Organization',
                                'rows' => $organizationRows,
                                'fields' => [
                                    'Position' => 'position',
                                    'Organization' => 'name',
                                    'Date' => 'date',
                                ],
                            ],
                            [
                                'title' => 'Work Experience',
                                'rows' => $workRows,
                                'fields' => [
                                    'Position' => 'position',
                                    'Company' => 'company',
                                    'Date' => 'date',
                                ],
                            ],
                            [
                                'title' => 'Trainings/Seminar Attended',
                                'rows' => $trainingRows,
                                'fields' => [
                                    'Training/Seminar' => 'title',
                                    'Place' => 'place',
                                    'Date' => 'date',
                                ],
                            ],
                        ];
                    @endphp

                    @foreach($academicSections as $section)
                        <div class="pv-sub-heading">{{ $section['title'] }}</div>

                        @forelse($section['rows'] as $index => $row)
                            <div class="pv-edu-item">
                                @foreach($section['fields'] as $label => $key)
                                    <div class="pv-row pv-row--edu">
                                        <span class="pv-label">{{ strtoupper($label) }}</span>
                                        <span class="pv-sep">:</span>
                                        <span class="pv-value">{{ isset($row[$key]) && $row[$key] !== '' ? $row[$key] : '—' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <div class="pv-edu-item pv-edu-item--empty">
                                <div class="pv-value">No List Found.</div>
                            </div>
                        @endforelse
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    document.addEventListener('click', function (event) {
        var tab = event.target.closest('.pv-tab');
        if (!tab) return;

        var target = tab.getAttribute('data-target');
        if (!target) return;

        document.querySelectorAll('.pv-tab').forEach(function (button) {
            button.classList.remove('active');
        });
        tab.classList.add('active');

        document.querySelectorAll('.pv-panel').forEach(function (panel) {
            panel.classList.remove('active');
        });

        var activePanel = document.getElementById(target);
        if (activePanel) {
            activePanel.classList.add('active');
        }
    });
})();

@if(session('success'))
(function () {
    var toast = document.getElementById('download-toast');
    if (!toast) return;

    var msg = toast.querySelector('.toast-message');
    var closeBtn = toast.querySelector('.toast-close');

    if (msg) msg.textContent = {!! json_encode(session('success')) !!};
    toast.classList.remove('toast-error');
    toast.classList.add('show');

    if (closeBtn && !closeBtn.dataset.pvBound) {
        closeBtn.addEventListener('click', function () { toast.classList.remove('show'); });
        closeBtn.dataset.pvBound = '1';
    }

    setTimeout(function () { toast.classList.remove('show'); }, 3000);
})();
@endif
</script>
@endpush
