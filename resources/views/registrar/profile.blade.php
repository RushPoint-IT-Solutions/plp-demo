@extends('layouts.registrar')

@section('title', 'PLP - Registrar Profile')
@section('page-title', 'MY PROFILE')
@section('body-class', 'page-registrar-profile')

@push('styles')
<style>
    .registrar-profile-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
        margin: 0 auto;
        max-width: 980px;
        width: 100%;
    }
    .registrar-profile-panel {
        background: #fff;
        border: 1px solid #e8eee9;
        border-radius: 8px;
        box-shadow: 0 12px 28px rgba(20, 53, 33, 0.08);
        overflow: hidden;
    }
    .registrar-profile-hero {
        align-items: center;
        background: linear-gradient(135deg, #0f5f36 0%, #1f7a4a 100%);
        color: #fff;
        display: flex;
        gap: 18px;
        justify-content: space-between;
        padding: 24px;
    }
    .registrar-profile-identity {
        align-items: center;
        display: flex;
        gap: 16px;
        min-width: 0;
    }
    .registrar-profile-avatar {
        align-items: center;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.32);
        border-radius: 50%;
        display: flex;
        flex: 0 0 auto;
        height: 72px;
        justify-content: center;
        width: 72px;
    }
    .registrar-profile-avatar i {
        font-size: 2.2rem;
    }
    .registrar-profile-name {
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.15;
        margin: 0;
        overflow-wrap: anywhere;
    }
    .registrar-profile-subtitle {
        color: rgba(255, 255, 255, 0.82);
        font-size: 0.86rem;
        font-weight: 600;
        margin-top: 6px;
    }
    .registrar-profile-status {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.26);
        border-radius: 999px;
        color: #fff;
        flex: 0 0 auto;
        font-size: 0.76rem;
        font-weight: 800;
        padding: 8px 13px;
        text-transform: uppercase;
    }
    .registrar-profile-body {
        padding: 24px;
    }
    .registrar-profile-section-title {
        color: #143521;
        font-size: 0.9rem;
        font-weight: 800;
        margin: 0 0 14px;
        text-transform: uppercase;
    }
    .registrar-profile-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .registrar-profile-field {
        background: #f8fbf9;
        border: 1px solid #e5ece7;
        border-radius: 8px;
        padding: 13px 14px;
    }
    .registrar-profile-label {
        color: #6b7a70;
        display: block;
        font-size: 0.72rem;
        font-weight: 800;
        margin-bottom: 6px;
        text-transform: uppercase;
    }
    .registrar-profile-value {
        color: #1c3327;
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.35;
        min-height: 21px;
        overflow-wrap: anywhere;
    }
    .registrar-profile-actions {
        border-top: 1px solid #edf2ef;
        display: flex;
        justify-content: flex-end;
        padding: 18px 24px 24px;
    }
    .registrar-password-btn {
        align-items: center;
        background: #006837;
        border: 0;
        border-radius: 7px;
        color: #fff;
        display: inline-flex;
        font-size: 0.9rem;
        font-weight: 800;
        gap: 8px;
        padding: 10px 18px;
    }
    .registrar-password-btn:hover,
    .registrar-password-btn:focus {
        background: #00542d;
        color: #fff;
    }
    .registrar-password-hint {
        color: #6b7a70;
        font-size: 0.8rem;
        line-height: 1.45;
        margin: 0 0 16px;
    }
    @media (max-width: 720px) {
        .registrar-profile-hero {
            align-items: flex-start;
            flex-direction: column;
        }
        .registrar-profile-grid {
            grid-template-columns: 1fr;
        }
        .registrar-profile-actions {
            justify-content: stretch;
        }
        .registrar-password-btn {
            justify-content: center;
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $display = function ($value) {
        $text = trim((string) $value);
        return $text === '' ? 'N/A' : $text;
    };

    $name = $display(optional($user)->name ?: optional($registrar)->name);
    $email = $display(optional($user)->email ?: optional($registrar)->email);
    $username = $display(optional($user)->username);
    $module = $display(optional($user)->module ?: 'Registrar');
    $registrarCode = $display(optional($registrar)->code);
    $accountStatusLabel = optional($accountStatus)->is_inactive ? 'Inactive' : 'Active';
@endphp

<div class="registrar-profile-page">
    @if(session('status'))
        <div class="alert alert-success mb-0" role="alert">{{ session('status') }}</div>
    @endif

    <section class="registrar-profile-panel">
        <div class="registrar-profile-hero">
            <div class="registrar-profile-identity">
                <div class="registrar-profile-avatar" aria-hidden="true">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <h2 class="registrar-profile-name">{{ $name }}</h2>
                    <div class="registrar-profile-subtitle">Registrar Portal Account</div>
                </div>
            </div>
            <span class="registrar-profile-status">{{ $accountStatusLabel }}</span>
        </div>

        <div class="registrar-profile-body">
            <h3 class="registrar-profile-section-title">Account Details</h3>
            <div class="registrar-profile-grid">
                <div class="registrar-profile-field">
                    <span class="registrar-profile-label">Full Name</span>
                    <div class="registrar-profile-value">{{ $name }}</div>
                </div>
                <div class="registrar-profile-field">
                    <span class="registrar-profile-label">Email</span>
                    <div class="registrar-profile-value">{{ $email }}</div>
                </div>
                <div class="registrar-profile-field">
                    <span class="registrar-profile-label">Username</span>
                    <div class="registrar-profile-value">{{ $username }}</div>
                </div>
                <div class="registrar-profile-field">
                    <span class="registrar-profile-label">Module</span>
                    <div class="registrar-profile-value">{{ $module }}</div>
                </div>
                <div class="registrar-profile-field">
                    <span class="registrar-profile-label">Registrar Code</span>
                    <div class="registrar-profile-value">{{ $registrarCode }}</div>
                </div>
                <div class="registrar-profile-field">
                    <span class="registrar-profile-label">Password Reset Required</span>
                    <div class="registrar-profile-value">{{ optional($user)->force_password_reset ? 'Yes' : 'No' }}</div>
                </div>
            </div>
        </div>

        <div class="registrar-profile-actions">
            <button type="button" class="registrar-password-btn" data-bs-toggle="modal" data-bs-target="#registrarChangePasswordModal">
                <i class="bi bi-shield-lock"></i>
                <span>Change Password</span>
            </button>
        </div>
    </section>
</div>

<div class="modal fade" id="registrarChangePasswordModal" tabindex="-1" aria-labelledby="registrarChangePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('registrar.profile.password.update') }}" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="registrarChangePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="registrar-password-hint">Use at least 8 characters with uppercase, lowercase, and a number.</p>

                    <div class="mb-3">
                        <label for="registrarCurrentPassword" class="form-label fw-semibold">Current Password</label>
                        <input id="registrarCurrentPassword" type="password" name="current_password" class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}" autocomplete="current-password" required>
                        @if($errors->has('current_password'))
                            <div class="invalid-feedback">{{ $errors->first('current_password') }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="registrarNewPassword" class="form-label fw-semibold">New Password</label>
                        <input id="registrarNewPassword" type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" autocomplete="new-password" required>
                        @if($errors->has('password'))
                            <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                        @endif
                    </div>

                    <div class="mb-0">
                        <label for="registrarConfirmPassword" class="form-label fw-semibold">Confirm New Password</label>
                        <input id="registrarConfirmPassword" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="registrar-password-btn">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($errors->any() || session('open_change_password_modal'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalElement = document.getElementById('registrarChangePasswordModal');
        if (modalElement && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    });
</script>
@endif
@endpush
