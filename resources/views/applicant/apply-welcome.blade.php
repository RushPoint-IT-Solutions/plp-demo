@extends('layouts.login')

@section('title', 'PLP - Applicant Application Start')

@section('content')
<div class="app-apply-shell">
    <div class="app-apply-card app-apply-intro-card">
        <h2 class="app-apply-title">Welcome, Applicant</h2>
        <p class="app-apply-subtitle">
            This wizard will guide you through the application process. Please read the reminders before continuing.
        </p>

        <div class="app-apply-reminder-group">
            <h3>Before you begin</h3>
            <ul>
                <li>Use your legal name exactly as it appears on official records.</li>
                <li>Use an active email address where you can receive updates.</li>
                <li>Prepare your basic profile details before proceeding to the form wizard.</li>
                <li>Your credentials will be generated after initial account creation.</li>
            </ul>
        </div>

        <form action="{{ route('applicant.apply.start') }}" method="POST" class="app-apply-start-form">
            @csrf

            @if($errors->any())
            <div class="app-apply-errors">
                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <label class="app-apply-check">
                <input class="app-apply-check-input" type="checkbox" name="ack_notices" value="1" {{ old('ack_notices') ? 'checked' : '' }}>
                <span>I have read and understood the important notices.</span>
            </label>

            <label class="app-apply-check">
                <input class="app-apply-check-input" type="checkbox" name="ack_terms" value="1" {{ old('ack_terms') ? 'checked' : '' }}>
                <span>I agree to the Privacy Policy and Terms of Service.</span>
            </label>

            <div class="app-apply-actions">
                <button type="submit" class="pf-btn-new app-apply-primary-btn">Apply Now</button>
            </div>
        </form>
    </div>
</div>
@endsection
