@extends('layouts.login')

@section('title', 'PLP - Reset Password')

@section('content')
<div class="login-card-wrapper">
    <div class="login-card">
        <h2 class="login-card-title">RESET PASSWORD</h2>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="login-field-group">
                <label for="email" class="login-label">EMAIL ADDRESS</label>
                <input
                    id="email"
                    type="email"
                    class="login-input{{ $errors->has('email') ? ' is-invalid' : '' }}"
                    name="email"
                    value="{{ $email ?? old('email') }}"
                    placeholder="Enter Email Address"
                    required
                    autofocus
                >
                @if ($errors->has('email'))
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
            </div>

            <div class="login-field-group">
                <label for="password" class="login-label">NEW PASSWORD</label>
                <input
                    id="password"
                    type="password"
                    class="login-input{{ $errors->has('password') ? ' is-invalid' : '' }}"
                    name="password"
                    placeholder="••••••••"
                    required
                >
                @if ($errors->has('password'))
                    <span class="invalid-feedback d-block" role="alert">
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
                <button type="submit" class="login-submit-btn">Reset Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
