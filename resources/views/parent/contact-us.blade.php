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

    <section class="parent-contact-guidelines">
        <h4>Before You Contact Us</h4>
        <p>For faster assistance, submit inquiries through the form below so your concern is tracked and routed to the right office.</p>
        @if($showRawSupportDetails)
            <p><strong>System Contact Details:</strong> {{ $supportContactDetails }}</p>
        @endif
        <ul>
            <li>For calls: prepare Student ID, full name, and contact number for verification.</li>
            <li>For email: expect a response within 1-2 business days during office hours.</li>
            <li>For urgent concerns, include complete details so school personnel can triage quickly.</li>
        </ul>
    </section>

    <section class="parent-contact-form-shell">
        <h4 class="parent-contact-section-title">Submit an Inquiry</h4>

        @if(!$canSubmit)
            <p class="parent-empty-note">Contact request options are not configured yet. Please call or email support.</p>
        @endif

        <form method="POST" action="{{ route('parent.contact-us.submit') }}" class="parent-contact-form" id="parentContactForm">
            @csrf

            <div class="parent-contact-form-grid">
                <div class="parent-contact-field">
                    <label class="app-filter-label" for="parentContactChild">Linked Student (Optional)</label>
                    @include('components.applicant-select', [
                        'id' => 'parentContactChild',
                        'name' => 'child',
                        'options' => $childOptions,
                        'selected' => $selectedChildValue,
                        'placeholder' => 'All linked students',
                        'wrapperClass' => 'parent-contact-select-wrap',
                        'inputClass' => 'form-input-long'
                    ])
                    @if($errors->has('child'))
                        <p class="parent-contact-error">{{ $errors->first('child') }}</p>
                    @endif
                </div>

                <div class="parent-contact-field">
                    <label class="app-filter-label" for="parentContactTopic">Concern Type</label>
                    @include('components.applicant-select', [
                        'id' => 'parentContactTopic',
                        'name' => 'topic_id',
                        'options' => $topicOptions,
                        'selected' => (string) old('topic_id', ''),
                        'placeholder' => 'Select concern type',
                        'wrapperClass' => 'parent-contact-select-wrap',
                        'inputClass' => 'form-input-long',
                        'required' => true
                    ])
                    @if($errors->has('topic_id'))
                        <p class="parent-contact-error">{{ $errors->first('topic_id') }}</p>
                    @endif
                </div>

                <div class="parent-contact-field">
                    <label class="app-filter-label" for="parentContactChannel">Preferred Response Channel</label>
                    @include('components.applicant-select', [
                        'id' => 'parentContactChannel',
                        'name' => 'channel_id',
                        'options' => $channelOptions,
                        'selected' => (string) old('channel_id', ''),
                        'placeholder' => 'Select response channel',
                        'wrapperClass' => 'parent-contact-select-wrap',
                        'inputClass' => 'form-input-long',
                        'required' => true
                    ])
                    @if($errors->has('channel_id'))
                        <p class="parent-contact-error">{{ $errors->first('channel_id') }}</p>
                    @endif
                </div>

                <div class="parent-contact-field parent-contact-field--full">
                    <label class="app-filter-label" for="parentContactSubject">Subject</label>
                    <input
                        type="text"
                        id="parentContactSubject"
                        name="subject"
                        class="app-filter-input"
                        maxlength="190"
                        value="{{ old('subject', '') }}"
                        placeholder="Enter a short summary of your concern"
                        required
                    >
                    @if($errors->has('subject'))
                        <p class="parent-contact-error">{{ $errors->first('subject') }}</p>
                    @endif
                </div>

                <div class="parent-contact-field parent-contact-field--full">
                    <label class="app-filter-label" for="parentContactMessage">Message</label>
                    <textarea
                        id="parentContactMessage"
                        name="message"
                        rows="5"
                        class="parent-contact-textarea"
                        maxlength="4000"
                        placeholder="Describe your concern in detail"
                        required
                    >{{ old('message', '') }}</textarea>
                    @if($errors->has('message'))
                        <p class="parent-contact-error">{{ $errors->first('message') }}</p>
                    @endif
                </div>
            </div>

            <div class="parent-contact-actions">
                <button type="submit" class="req-btn-save" {{ $canSubmit ? '' : 'disabled' }}>Submit Inquiry</button>
            </div>
        </form>
    </section>

    <section class="parent-contact-history-shell">
        <h4 class="parent-contact-section-title">Recent Inquiries</h4>

        <div class="table-scroll parent-contact-history-scroll">
            <table class="table-schedule parent-contact-history-table">
                <thead>
                    <tr>
                        <th class="sched-th">Reference No.</th>
                        <th class="sched-th">Student</th>
                        <th class="sched-th">Concern Type</th>
                        <th class="sched-th">Channel</th>
                        <th class="sched-th">Status</th>
                        <th class="sched-th">Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRequests as $requestItem)
                        @php
                            $statusCode = strtolower(str_replace('_', '-', (string) optional($requestItem->status)->code));
                            $statusName = (string) optional($requestItem->status)->name;
                            $studentName = trim((string) optional($requestItem->student)->name);
                            $studentNo = trim((string) optional($requestItem->student)->student_no);
                            $submittedAt = optional($requestItem->submitted_at ?: $requestItem->created_at)->format('M d, Y h:i A');
                        @endphp
                        <tr>
                            <td class="sched-td">{{ $requestItem->reference_no }}</td>
                            <td class="sched-td">
                                @if($studentName !== '')
                                    {{ $studentName }}{{ $studentNo !== '' ? ' (' . $studentNo . ')' : '' }}
                                @else
                                    All linked students
                                @endif
                            </td>
                            <td class="sched-td">{{ optional($requestItem->topic)->name ?: 'N/A' }}</td>
                            <td class="sched-td">{{ optional($requestItem->channel)->name ?: 'N/A' }}</td>
                            <td class="sched-td">
                                <span class="parent-contact-status-badge parent-contact-status-{{ $statusCode !== '' ? $statusCode : 'new' }}">{{ $statusName !== '' ? $statusName : 'N/A' }}</span>
                            </td>
                            <td class="sched-td">{{ $submittedAt ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="sched-td" colspan="6">No inquiries submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($recentRequests, 'hasPages') && $recentRequests->hasPages())
            <div class="parent-contact-pagination">
                {{ $recentRequests->links() }}
            </div>
        @endif
    </section>
</div>

@push('scripts')
<script src="{{ mix('js/applicant-select.js') }}"></script>
@endpush
@endsection
