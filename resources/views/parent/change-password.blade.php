@extends('layouts.parent')

@section('title', 'PLP - Parent Change Password')
@section('page-title', 'UPDATE PASSWORD')
@section('body-class', 'page-student-account page-student-change-password')

@section('content')
<div class="parent-password-page">
    <section class="parent-password-card">
        <header class="parent-password-head">
            <h2>Update Password</h2>
            <p>For parent portal account security</p>
        </header>

        @if(session('status'))
            <div class="alert alert-success" role="alert">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('parent.change-password.update') }}" class="parent-password-form" novalidate>
            @csrf

            <div class="parent-password-field">
                <label for="parentChangePasswordUsername">Username</label>
                <input
                    type="text"
                    id="parentChangePasswordUsername"
                    value="{{ (string) optional($user)->username }}"
                    class="app-filter-input"
                    readonly
                >
            </div>

            <div class="parent-password-field">
                <label for="parentCurrentPassword">Current Password</label>
                <div class="login-password-wrapper parent-password-input-wrap">
                    <input
                        type="password"
                        id="parentCurrentPassword"
                        name="current_password"
                        class="app-filter-input login-input"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="password-toggle-btn" id="parentCurrentPasswordToggleBtn" aria-label="Show current password" aria-pressed="false">
                        <svg id="parentCurrentPasswordEyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                        </svg>
                        <svg id="parentCurrentPasswordEyeSlashIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16" style="display:none;">
                            <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
                            <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299l.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/>
                            <path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884l-12-12 .708-.708 12 12-.708.708z"/>
                        </svg>
                    </button>
                </div>
                @if($errors->has('current_password'))
                    <p class="parent-password-error">{{ $errors->first('current_password') }}</p>
                @endif
            </div>

            <div class="parent-password-field">
                <label for="parentNewPassword">New Password</label>
                <div class="login-password-wrapper parent-password-input-wrap">
                    <input
                        type="password"
                        id="parentNewPassword"
                        name="password"
                        class="app-filter-input login-input"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="password-toggle-btn" id="parentNewPasswordToggleBtn" aria-label="Show new password" aria-pressed="false">
                        <svg id="parentNewPasswordEyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                        </svg>
                        <svg id="parentNewPasswordEyeSlashIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16" style="display:none;">
                            <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
                            <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299l.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/>
                            <path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884l-12-12 .708-.708 12 12-.708.708z"/>
                        </svg>
                    </button>
                </div>
                <p class="parent-password-hint">Use at least 8 characters with uppercase, lowercase, and numbers.</p>
                @if($errors->has('password'))
                    <p class="parent-password-error">{{ $errors->first('password') }}</p>
                @endif
            </div>

            <div class="parent-password-field">
                <label for="parentConfirmPassword">Confirm New Password</label>
                <div class="login-password-wrapper parent-password-input-wrap">
                    <input
                        type="password"
                        id="parentConfirmPassword"
                        name="password_confirmation"
                        class="app-filter-input login-input"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="password-toggle-btn" id="parentConfirmPasswordToggleBtn" aria-label="Show confirm password" aria-pressed="false">
                        <svg id="parentConfirmPasswordEyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                        </svg>
                        <svg id="parentConfirmPasswordEyeSlashIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16" style="display:none;">
                            <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
                            <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299l.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/>
                            <path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884l-12-12 .708-.708 12 12-.708.708z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="parent-password-actions">
                <a href="{{ route('parent.grades') }}" class="req-btn-cancel">Cancel</a>
                <button type="submit" class="req-btn-save">Update Password</button>
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script src="{{ mix('js/parent-change-password.js') }}"></script>
@endpush
@endsection
