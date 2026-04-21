@php
    $applicationStatusCourseOptions = [
        ['value' => 'BSCS', 'label' => 'Bachelor of Science in Computer Science'],
        ['value' => 'BSIT', 'label' => 'Bachelor of Science in Information Technology'],
    ];

    $applicationStatusStateOptions = [
        ['value' => 'ACCEPTED', 'label' => 'ACCEPTED'],
        ['value' => 'PENDING', 'label' => 'PENDING'],
        ['value' => 'REJECTED', 'label' => 'REJECTED'],
    ];

    $applicationStatusSectionOptions = [
        ['value' => 'A', 'label' => 'A'],
        ['value' => 'B', 'label' => 'B'],
        ['value' => 'C', 'label' => 'C'],
    ];

    $applicationStatusCurriculumOptions = [
        ['value' => '2025-2026', 'label' => '2025-2026'],
        ['value' => '2024-2025', 'label' => '2024-2025'],
    ];

    $applicationStatusEnrollmentOptions = [
        ['value' => 'Regular', 'label' => 'Regular'],
        ['value' => 'Irregular', 'label' => 'Irregular'],
    ];

    $applicationStatusAdmissionOptions = [
        ['value' => 'New', 'label' => 'New'],
        ['value' => 'Old', 'label' => 'Old'],
        ['value' => 'Transferee', 'label' => 'Transferee'],
    ];
@endphp

<div class="apc-card">
    <div class="apc-grid apc-grid--three">
        <div class="apc-field apc-field--wide">
            <label class="apc-label">Course</label>
            @include('registrar.components.listbox-select', [
                'id' => 'appStatusCourse',
                'name' => 'appStatusCourse',
                'options' => $applicationStatusCourseOptions,
                'selected' => 'BSCS',
                'placeholder' => 'Select Course'
            ])
        </div>
        <div class="apc-field">
            <label class="apc-label">Status</label>
            @include('registrar.components.listbox-select', [
                'id' => 'appStatusStatus',
                'name' => 'appStatusStatus',
                'options' => $applicationStatusStateOptions,
                'selected' => 'ACCEPTED',
                'placeholder' => 'Select Status'
            ])
        </div>
        <div class="apc-field">
            <label class="apc-label">Section</label>
            @include('registrar.components.listbox-select', [
                'id' => 'appStatusSection',
                'name' => 'appStatusSection',
                'options' => $applicationStatusSectionOptions,
                'selected' => 'A',
                'placeholder' => 'Select Section'
            ])
        </div>
    </div>

    <div class="apc-grid apc-grid--three">
        <div class="apc-field">
            <label class="apc-label">Curriculum Year</label>
            @include('registrar.components.listbox-select', [
                'id' => 'appStatusCurriculumYear',
                'name' => 'appStatusCurriculumYear',
                'options' => $applicationStatusCurriculumOptions,
                'selected' => '2025-2026',
                'placeholder' => 'Select Year'
            ])
        </div>
        <div class="apc-field">
            <label class="apc-label">Enrollment Status</label>
            @include('registrar.components.listbox-select', [
                'id' => 'appStatusEnrollmentStatus',
                'name' => 'appStatusEnrollmentStatus',
                'options' => $applicationStatusEnrollmentOptions,
                'selected' => 'Regular',
                'placeholder' => 'Select Enrollment Status'
            ])
        </div>
        <div class="apc-field apc-field--save-only">
            <label class="apc-label">Admission Status</label>
            <div class="apc-save-row">
                @include('registrar.components.listbox-select', [
                    'id' => 'appStatusAdmissionStatus',
                    'name' => 'appStatusAdmissionStatus',
                    'options' => $applicationStatusAdmissionOptions,
                    'selected' => 'New',
                    'placeholder' => 'Select Admission Status'
                ])
                <button type="button" class="apc-btn apc-btn--save">Save</button>
            </div>
        </div>
    </div>
</div>
