@extends('layouts.registrar-help')

@section('title', 'PLP - Submit Ticket')
@section('body-class', 'page-registrar-help page-registrar-help-ticket')

@section('content')
<div class="reg-help">
    <div class="reg-help-top-nav mb-2">
        <a href="{{ $backRoute }}" class="reg-help-back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back
        </a>
    </div>

    <section class="reg-help-ticket-panel">
        <div class="reg-help-ticket-head">
            <div>
                <h3 class="reg-help-panel-title">Submit Ticket</h3>
                <p class="reg-help-ticket-sub">Your concern will be saved in the Registrar ticketing database.</p>
            </div>
            <span class="reg-help-ticket-type">{{ $helpTicketRequesterType ?? 'User' }}</span>
        </div>

        @if($errors->any())
            <div class="reg-help-ticket-error">{{ $errors->first() }}</div>
        @endif

        @include('shared.help-center-ticket-form')
    </section>
</div>
@endsection
