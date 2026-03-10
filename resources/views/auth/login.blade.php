@extends('layouts.login')

@section('title', 'PLP - ' . ucfirst($module) . ' Login')

@section('content')
<div class="login-card-wrapper">
    <div class="login-card">
        {{-- Module Title --}}
        <h2 class="login-card-title">{{ strtoupper($module) }} LOGIN</h2>

        <form method="POST" action="{{ route('demo.login') }}">
            @csrf

            {{-- Pass the module through so we redirect to the right pages --}}
            <input type="hidden" name="module" value="{{ $module }}">

            {{-- Username / Student Number / Applicant Number --}}
            <div class="login-field-group">
                @if($module === 'applicant')
                    <label for="username" class="login-label">APPLICANT NUMBER</label>
                @elseif($module === 'student')
                    <label for="username" class="login-label">STUDENT NUMBER</label>
                @else
                    <label for="username" class="login-label">USERNAME</label>
                @endif
                <input
                    id="username"
                    type="text"
                    class="login-input{{ $errors->has('username') ? ' is-invalid' : '' }}"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="{{ $module === 'applicant' ? 'Enter Applicant Number' : ($module === 'student' ? 'Enter Student Number' : 'Enter Username') }}"
                    required
                    autofocus
                >
                @if ($errors->has('username'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('username') }}</strong>
                    </span>
                @endif
            </div>

            {{-- Password --}}
            <div class="login-field-group">
                <label for="password" class="login-label">PASSWORD</label>
                <div class="login-password-wrapper">
                    <input
                        id="password"
                        type="password"
                        class="login-input{{ $errors->has('password') ? ' is-invalid' : '' }}"
                        name="password"
                        placeholder="••••••••"
                        required
                    >
                    <button type="button" class="password-toggle-btn" onclick="togglePassword()" tabindex="-1">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                        </svg>
                        <svg id="eye-slash-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16" style="display:none;">
                            <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
                            <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299l.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/>
                            <path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884l-12-12 .708-.708 12 12-.708.708z"/>
                        </svg>
                    </button>
                </div>
                @if ($errors->has('password'))
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
            </div>

            {{-- Remember Me & Forgot Password --}}
            <div class="login-options">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                @if (Route::has('password.request'))
                    <div class="login-forgot">
                        Forgot Password? <a href="{{ route('password.request') }}">Click HERE</a>
                    </div>
                @endif
            </div>

            {{-- Sign In Button --}}
            <div class="text-center mt-4">
                <button type="submit" class="login-submit-btn">Sign In</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/login.js') }}"></script>
@endpush
