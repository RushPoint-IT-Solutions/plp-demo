@extends('layouts.login')

@section('title', 'PLP - Forgot Password')

@section('content')
<div class="login-card-wrapper">
    <div class="login-card">
        <h2 class="login-card-title">FORGOT PASSWORD</h2>

        @if (session('status'))
            <div class="alert alert-success" role="alert" style="font-size:0.85rem; border-radius:10px; margin-bottom:14px;">
                {{ session('status') }}
            </div>
        @endif

        <p style="font-size:0.82rem; color:#4f6058; margin-bottom:14px;">
            Enter your account email and we will send a password reset link.
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="login-field-group">
                <label for="email" class="login-label">EMAIL ADDRESS</label>
                <input
                    id="email"
                    type="email"
                    class="login-input{{ $errors->has('email') ? ' is-invalid' : '' }}"
                    name="email"
                    value="{{ old('email') }}"
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

            <div class="text-center mt-4">
                <button type="submit" class="login-submit-btn">Send Reset Link</button>
            </div>

            <div class="text-center" style="margin-top:10px;">
                <a href="{{ route('admin.access-module') }}" style="font-size:0.82rem; color:#0f7b43; text-decoration:none;">Back to Login</a>
            </div>
        </form>
    </div>
</div>
@endsection
