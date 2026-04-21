@extends(!empty($applicationFormEmbedded) ? 'layouts.applicant-embedded' : 'layouts.applicant')

@section('title', 'PLP - Official Correspondence')
@section('page-title', 'OFFICIAL CORRESPONDENCE')

@section('content')
@php
    $preference = optional($applicant->applicationPreference);
    $preferredCourse = optional($preference->course)->name;
    $courseLabel = $preferredCourse ?: ($preference->apply_strand ?: 'Chosen Course');
    $applicationStatusValue = strtolower((string) ($applicant->application_status ?: 'in_process'));
    $applicationStatusLabelMap = [
        'submitted' => 'Document Submitted',
        'on_probation' => 'On Probation',
        'in_process' => 'In Process',
        'draft' => 'Incomplete',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
    ];
    $applicationStatusLabel = $applicationStatusLabelMap[$applicationStatusValue] ?? ucwords(str_replace('_', ' ', $applicationStatusValue));

    $correspondenceCopyMap = [
        'accepted' => [
            'lead' => 'Congratulations! We are pleased to inform you that your application to Pamantasan ng Lungsod ng Pasig has been accepted for the ' . $courseLabel . ' program.',
            'nextSteps' => [
                'Wait for the official enrollment instructions email.',
                'Prepare your original physical documents.',
            ],
        ],
        'rejected' => [
            'lead' => 'We regret to inform you that your application to Pamantasan ng Lungsod ng Pasig has not been accepted for the ' . $courseLabel . ' program.',
            'nextSteps' => [
                'You may contact admissions for guidance.',
                'Review the requirements before the next application cycle.',
            ],
        ],
        'on_probation' => [
            'lead' => 'Your application to Pamantasan ng Lungsod ng Pasig is currently on probation for the ' . $courseLabel . ' program.',
            'nextSteps' => [
                'Wait for the registrar\'s next update.',
                'Keep your contact information active.',
            ],
        ],
    ];

    $correspondenceCopy = $correspondenceCopyMap[$applicationStatusValue] ?? [
        'lead' => 'Your application to Pamantasan ng Lungsod ng Pasig is currently under review for the ' . $courseLabel . ' program.',
        'nextSteps' => [
            'Wait for the admissions office update.',
            'Check your portal regularly for status changes.',
        ],
    ];

    $referenceCode = 'PLP-APP-' . strtoupper((string) ($applicant->applicant_id ?: '0000'));
@endphp
<div class="app-process-page applicant-consistent-page">
    <div class="applicant-correspondence-shell">
        <div class="applicant-correspondence-head">
            <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="applicant-correspondence-logo">
            <div class="applicant-correspondence-meta">
                <h3>PLP ADMISSIONS OFFICE</h3>
                <p>Application Status Notification: {{ $applicant->first_name }} {{ $applicant->last_name }}</p>
            </div>
            <div class="applicant-correspondence-date">{{ now()->format('F d, Y, h:i A') }}</div>
        </div>

        <div class="applicant-correspondence-divider"></div>

        <div class="applicant-correspondence-body">
            <div class="applicant-ref-chip" style="margin-bottom:12px;">
                <span class="dot"></span>
                <span>APPLICATION STATUS: {{ $applicationStatusLabel }}</span>
            </div>

            <p>
                {{ $correspondenceCopy['lead'] }}
            </p>

            <p>Welcome to our community!</p>

            <p>[Admissions Officer Name]</p>

            <div class="applicant-ref-chip">
                <span class="dot"></span>
                <span>REFERENCE NUMBER: {{ $referenceCode }}</span>
            </div>

            <div class="applicant-next-steps">
                <p>Next Steps:</p>
                <ol>
                    @foreach($correspondenceCopy['nextSteps'] as $nextStep)
                    <li>{{ $nextStep }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
