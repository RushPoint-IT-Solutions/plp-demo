@extends('layouts.parent')

@section('title', 'PLP - Parent Contact Us')
@section('page-title', 'CONTACT US')

@section('content')
@php
    $selectedChildValue = old('child', (string) ($selectedChildId ?? ''));

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

    $topicOptions = collect($topics ?? [])->map(function ($topic) {
        return [
            'value' => (string) $topic->id,
            'label' => (string) $topic->name,
        ];
    })->values()->all();

    $channelOptions = collect($channels ?? [])->map(function ($channel) {
        return [
            'value' => (string) $channel->id,
            'label' => (string) $channel->name,
        ];
    })->values()->all();

    $canSubmit = count($topicOptions) > 0 && count($channelOptions) > 0;
    $showRawSupportDetails = !empty($supportContactDetails) && strpos((string) $supportContactDetails, '@') === false;
@endphp

<div class="parent-contact-page">
    @if(session('status'))
        <div class="alert alert-success parent-contact-alert" role="alert">{{ session('status') }}</div>
    @endif

    @if($errors->has('contact'))
        <div class="alert alert-danger parent-contact-alert" role="alert">{{ $errors->first('contact') }}</div>
    @endif

    <div class="parent-contact-grid">
        <article class="parent-contact-card">
            <div class="parent-contact-icon-wrap" aria-hidden="true">
                <div class="parent-contact-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="86" height="86" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                </div>
            </div>
            <p class="parent-contact-card-title">Call Support</p>
            <h3 class="parent-contact-value">{{ $supportPhone }}</h3>
            <p class="parent-contact-note">Our support team is available <strong>Monday to Friday, 8:00 AM - 5:00 PM</strong>. Keep your <strong>Student ID</strong> ready so we can verify records faster.</p>
        </article>

        <article class="parent-contact-card">
            <div class="parent-contact-icon-wrap" aria-hidden="true">
                <div class="parent-contact-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="86" height="86" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <polyline points="3 7 12 13 21 7"></polyline>
                </svg>
                </div>
            </div>
            <p class="parent-contact-card-title">Email Support</p>
            <h3 class="parent-contact-value"><a href="{{ $supportEmailComposeUrl }}" target="_blank" rel="noopener noreferrer" data-gmail-compose="1">{{ $supportEmail }}</a></h3>
            <p class="parent-contact-note">Please allow <strong>24-48 hours</strong> for a response. Include your <strong>Full Name, Student ID, and Department</strong> in the subject line for quicker routing. <a href="{{ $supportEmailComposeUrl }}" target="_blank" rel="noopener noreferrer" data-gmail-compose="1">Open Gmail Compose</a>.</p>
        </article>
    </div>

    <section class="parent-contact-guidelines" style="margin-top: 25px; border: 1px solid #cfe1d7; border-radius: 14px; background: linear-gradient(180deg, #ffffff 0%, #f8fbf9 100%); padding: 20px 25px; box-shadow: 0 10px 20px rgba(14, 41, 28, 0.06);">
        <h4 style="margin-top: 0; color: #132219; font-size: 1.1rem; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.3px;">Before You Contact Us</h4>
        <p style="color: #3e4c44; font-size: 0.9rem; line-height: 1.5; margin-bottom: 12px;">Quick tips for faster assistance:</p>
        <ul style="color: #2b3a31; font-size: 0.85rem; line-height: 1.7; padding-left: 20px; margin-bottom: 0;">
            <li><strong>Information:</strong> Have your Student ID and full name ready.</li>
            <li><strong>Expectation:</strong> Response within 1-2 business days.</li>
            <li><strong>Detail:</strong> Be specific about your concern for quicker triage.</li>
        </ul>
    </section>
</div>

@push('scripts')
<script src="{{ mix('js/applicant-select.js') }}"></script>
@endpush
@endsection
