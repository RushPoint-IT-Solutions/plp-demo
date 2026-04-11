@extends('layouts.registrar-help')

@section('title', 'PLP - Applicant Help Center - ' . strtoupper($topic['title']))
@section('body-class', 'page-registrar-help page-registrar-help-topic page-applicant-help')

@section('content')
<div class="reg-help-topic-page">
    <div class="reg-help-top-nav mb-4">
        <a href="{{ route('applicant.help.center') }}" class="reg-help-back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back
        </a>
        <div class="reg-help-breadcrumb-nav" aria-label="Breadcrumb">
            <span class="reg-help-breadcrumb-parent">Help Center</span>
            <span class="reg-help-breadcrumb-sep" aria-hidden="true">&rsaquo;</span>
            <span class="reg-help-breadcrumb-current">{{ ucwords(strtolower($topic['title'])) }}</span>
        </div>
    </div>

    <div class="reg-help-head">
        <h2 class="reg-help-topic-title">{{ strtoupper($topic['title']) }}
        <span class="reg-help-topic-title-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 12a8 8 0 0 1 16 0"/>
                <path d="M4 12v5a2 2 0 0 0 2 2h1"/>
                <path d="M20 12v5a2 2 0 0 1-2 2h-1"/>
                <rect x="3" y="11" width="4" height="6" rx="2"/>
                <rect x="17" y="11" width="4" height="6" rx="2"/>
                <path d="M12 19v2"/>
                <path d="M10 21h4"/>
            </svg>
        </span>
    </h2>
    <p class="reg-help-topic-subtitle">{{ $topic['subtitle'] }}</p>
    </div>

    <div class="reg-help-topic-layout">
        <div class="reg-help-steps-card">
            @foreach($topic['steps'] as $step)
                <div class="reg-help-step-item">
                    <div class="reg-help-step-check">✓</div>
                    <div class="reg-help-step-content">
                        <div class="reg-help-step-title">{{ $step['title'] }}</div>
                        <div class="reg-help-step-desc">{{ $step['description'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <aside class="reg-help-side-panel">
            <div class="reg-help-side-title">FAQs</div>
            <div class="reg-help-faq-list reg-help-faq-list-topic">
                @foreach($topic['faqs'] as $faq)
                    <article class="reg-help-faq-item" data-expanded="false">
                        <button type="button" class="reg-help-faq-q">
                            <span>{{ $faq['q'] }}</span>
                            <span class="reg-help-faq-caret">▾</span>
                        </button>
                        <div class="reg-help-faq-a">{{ $faq['a'] }}</div>
                    </article>
                @endforeach
            </div>

            <div class="reg-help-contact-inline">
                <div class="reg-help-contact-inline-title">Need More Help?</div>
                <div class="reg-help-contact-inline-actions">
                    <a href="mailto:info@rushpoint.com.ph">
                        <svg class="reg-help-contact-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        Email Us
                    </a>
                    <a href="{{ route('applicant.help.live-chat') }}">
                        <svg class="reg-help-contact-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="13" x2="15" y2="13"></line></svg>
                        Live Chat
                    </a>
                    <a href="tel:+6321234567">
                        <svg class="reg-help-contact-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        Call Us
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-help-center.js') }}?v={{ time() }}"></script>
@endpush