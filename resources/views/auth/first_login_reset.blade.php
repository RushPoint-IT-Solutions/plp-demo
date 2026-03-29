@extends('layouts.login')

@section('title', 'PLP - Setup First Password')

@section('content')
<div class="login-card-wrapper">
    <div class="login-card">
        <h2 class="login-card-title">SETUP NEW PASSWORD</h2>
        <p class="text-center text-muted mb-4" style="font-size: 14px;">For security reasons, please change your default password before continuing.</p>

        <form method="POST" action="{{ route('password.first_reset.update') }}">
            @csrf

            <div class="login-field-group">
                <label for="password" class="login-label">NEW PASSWORD</label>  
                <input
                    id="password"
                    type="password"
                    class="login-input{{ $errors->has('password') ? ' is-invalid' : '' }}"
                    name="password"
                    placeholder="••••••••"
                    required
                    autofocus
                >
                @if ($errors->has('password'))
                    <span class="invalid-feedback d-block" role="alert" style="color: #e3342f; font-size: 13px; margin-top: 5px;">        
                        <strong>{{ $errors->first('password') }}</strong>       
                    </span>
                @endif
            </div>

            <div class="login-field-group">
                <label for="password-confirm" class="login-label">CONFIRM PASSWORD</label>
                <input
                    id="password-confirm"
                    type="password"
                    class="login-input"
                    name="password_confirmation"
                    placeholder="••••••••"
                    required
                >
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="login-submit-btn">Save & Continue</button>
            </div>
        </form>
    </div>
</div>
@endsection
