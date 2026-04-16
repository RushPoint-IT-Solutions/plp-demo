@extends('layouts.registrar-help')

@section('title', 'PLP - Parent Help Center - Live Chat')
@section('body-class', 'page-registrar-help page-registrar-help-chat page-parent-help')

@section('content')
<div class="reg-help-chat-page">
    <div class="reg-help-top-nav mb-2">
        <a href="{{ route('parent.help.center') }}" class="reg-help-chat-back reg-help-back-btn" title="Back to Help Center">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back
        </a>
    </div>

    <div class="reg-help-chat-head text-center justify-content-center">
        <h2 class="justify-content-center w-100">Live Chat</h2>
    </div>

    <div class="reg-help-chat-window" id="regHelpChatWindow" aria-live="polite">
        <div class="reg-help-chat-row bot">
            <div class="reg-help-chat-bubble">Hello! How can I assist you today?</div>
        </div>
    </div>

    <div class="reg-help-chat-input-wrap">
        <input type="text" id="regHelpChatInput" placeholder="Type a message..." maxlength="220" aria-label="Chat message">
        <button type="button" id="regHelpChatSendBtn">Send</button>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-help-chat.js') }}?v={{ time() }}"></script>
@endpush
