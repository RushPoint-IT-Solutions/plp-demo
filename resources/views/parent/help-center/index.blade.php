@extends('layouts.registrar-help')

@section('title', 'PLP - Parent Help Center')
@section('body-class', 'page-registrar-help page-registrar-help-home page-parent-help')

@section('content')
<div class="reg-help">
    <div class="reg-help-top-nav mb-2">
        <a href="{{ route('parent.grades') }}" class="reg-help-back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back
        </a>
    </div>

    <div class="reg-help-head text-center">
        <h2 class="reg-help-title justify-content-center">
            Help Center
            <span class="reg-help-title-icon" aria-hidden="true">
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
        <p class="reg-help-subtitle">How can we assist you today?</p>

        <div class="reg-help-search mx-auto">
            <input type="text" id="regHelpSearchInput" placeholder="Search for answers..." aria-label="Search help topics">
            <span class="reg-help-search-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
        </div>
    </div>

    <div class="reg-help-topic-grid" id="regHelpTopicGrid">
        @foreach($topics as $item)
            <a href="{{ route('parent.help.topic', ['topic' => $item['slug']]) }}" class="reg-help-topic-card" data-help-keywords="{{ strtolower($item['title'] . ' ' . $item['subtitle']) }}">
                <div class="reg-help-topic-icon">
                    @if($item['icon'] === 'account')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2" ry="2"/><circle cx="8" cy="10" r="3"/><path d="M4 16h8"/><line x1="16" y1="10" x2="20" y2="10"/><line x1="16" y1="14" x2="20" y2="14"/></svg>
                    @elseif($item['icon'] === 'application')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" fill="none" stroke="currentColor" stroke-width="2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="17.5" r="5" fill="currentColor" stroke="none"/><path d="M15.5 17.5l1.5 1.5 2.5-2.5" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @elseif($item['icon'] === 'technical')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M2 20h20" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="4.5" fill="currentColor" stroke="none"/><line x1="12" y1="8" x2="12" y2="10.5" stroke="#fff" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="12.5" x2="12.01" y2="12.5" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    @elseif($item['icon'] === 'forms')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="17" cy="17" r="5" fill="currentColor" stroke="none"/><path d="M17 19v-4m-2 2l2-2 2 2" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @elseif($item['icon'] === 'module')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M5 21v-2a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v2"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6-8 10-8 10z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><rect x="9.5" y="10" width="5" height="4" rx="1" fill="currentColor" stroke="none"/><path d="M9.5 10V8.5a2.5 2.5 0 0 1 5 0V10" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
                    @endif
                </div>
                <div class="reg-help-topic-name">{{ $item['title'] }}</div>
            </a>
        @endforeach
    </div>

    @include('shared.help-center-ticketing')

    <div class="reg-help-bottom">
        <div class="reg-help-panel">
            <h3 class="reg-help-panel-title">Popular Questions</h3>
            <div class="reg-help-faq-list" id="regHelpPopularFaqs">
                @foreach($popularQuestions as $faq)
                    <article class="reg-help-faq-item" data-expanded="false">
                        <button type="button" class="reg-help-faq-q">
                            <span>{{ $faq['question'] }}</span>
                            <span class="reg-help-faq-caret">▾</span>
                        </button>
                        <div class="reg-help-faq-a">{{ $faq['answer'] }}</div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="reg-help-panel">
            <h3 class="reg-help-panel-title">Need More Help?</h3>
            <div class="reg-help-contact-grid">
                <a href="mailto:info@rushpoint.com.ph" class="reg-help-contact-card">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2.5"/><path d="M3 7l9 6 9-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><circle cx="19" cy="6" r="5" fill="currentColor" stroke="none"/></svg>
                    <span>Email Us</span>
                </a>
                <a href="{{ route('parent.help.live-chat') }}" class="reg-help-contact-card">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><path d="M9 12h6" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span>Live Chat</span>
                </a>
                <a href="tel:+6321234567" class="reg-help-contact-card">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>Call Us</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-help-center.js') }}?v={{ time() }}"></script>
@endpush
