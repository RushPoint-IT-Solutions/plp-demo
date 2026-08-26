@extends('layouts.registrar')

@section('title', 'PLP - Change Password')
@section('page-title', 'UPDATE PASSWORD')
@section('body-class', 'page-student-account page-student-change-password')

@push('styles')
<style>
    .student-password-input-wrap {
        align-items: center;
        display: flex;
        position: relative;
    }

    .student-password-input-wrap .app-filter-input {
        padding-right: 48px !important;
    }

    .student-password-toggle {
        align-items: center;
        background: transparent;
        border: 0;
        color: #7a847e;
        display: flex;
        height: 40px;
        justify-content: center;
        padding: 0;
        position: absolute;
        right: 4px;
        width: 40px;
    }

    .student-password-toggle:hover,
    .student-password-toggle:focus-visible {
        color: #006837;
    }

    .student-password-toggle:focus-visible {
        border-radius: 5px;
        outline: 2px solid #006837;
        outline-offset: -2px;
    }

    .student-password-toggle .bi-eye-slash {
        display: none;
    }

    .student-password-toggle[aria-pressed="true"] .bi-eye {
        display: none;
    }

    .student-password-toggle[aria-pressed="true"] .bi-eye-slash {
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: calc(100vh - 140px); padding: 40px 20px;">
    <div style="background-color: #ffffff; border-radius: 12px; padding: 40px 50px; width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.12), 0 8px 16px rgba(0,0,0,0.06); border: 1px solid #eaeaea; box-sizing: border-box;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h3 style="margin: 0; color: #222; font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px;">Update Password</h3>
            <p style="margin: 8px 0 0; color: #006837; font-size: 0.95rem; font-weight: 500; background: #e8f5e9; display: inline-block; padding: 6px 14px; border-radius: 20px;">For the specified student account</p>
        </div>

        <form style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display: block; font-weight: 600; color: #444; font-size: 0.85rem; margin-bottom: 6px;">Student No. or Username</label>
                <input type="text" class="app-filter-input" placeholder="e.g. 2024-00101" style="width: 100%; padding: 12px 16px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; font-size: 0.9rem; transition: border-color 0.2s;" onfocus="this.style.borderColor='#006837'" onblur="this.style.borderColor='#ccc'">
            </div>

            <div>
                <label for="studentCurrentPassword" style="display: block; font-weight: 600; color: #444; font-size: 0.85rem; margin-bottom: 6px;">Current Password</label>
                <div class="student-password-input-wrap">
                    <input id="studentCurrentPassword" type="password" class="app-filter-input" placeholder="Enter Current Password" autocomplete="current-password" style="width: 100%; padding: 12px 16px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; font-size: 0.9rem; transition: border-color 0.2s;" onfocus="this.style.borderColor='#006837'" onblur="this.style.borderColor='#ccc'">
                    <button type="button" class="student-password-toggle" data-password-toggle data-target="studentCurrentPassword" aria-label="Show current password" aria-pressed="false" title="Show current password">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                        <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div style="position: relative;">
                <div style="height: 1px; background: #eee; margin: 10px 0 25px 0;"></div>
                
                <label for="studentNewPassword" style="display: block; font-weight: 600; color: #444; font-size: 0.85rem; margin-bottom: 6px;">New Password</label>
                <div class="student-password-input-wrap">
                    <input id="studentNewPassword" type="password" class="app-filter-input" placeholder="Enter New Password" autocomplete="new-password" style="width: 100%; padding: 12px 16px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; font-size: 0.9rem; transition: border-color 0.2s;" onfocus="this.style.borderColor='#006837'" onblur="this.style.borderColor='#ccc'">
                    <button type="button" class="student-password-toggle" data-password-toggle data-target="studentNewPassword" aria-label="Show new password" aria-pressed="false" title="Show new password">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                        <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div>
                <label for="studentConfirmPassword" style="display: block; font-weight: 600; color: #444; font-size: 0.85rem; margin-bottom: 6px;">Confirm New Password</label>
                <div class="student-password-input-wrap">
                    <input id="studentConfirmPassword" type="password" class="app-filter-input" placeholder="Confirm New Password" autocomplete="new-password" style="width: 100%; padding: 12px 16px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; font-size: 0.9rem; transition: border-color 0.2s;" onfocus="this.style.borderColor='#006837'" onblur="this.style.borderColor='#ccc'">
                    <button type="button" class="student-password-toggle" data-password-toggle data-target="studentConfirmPassword" aria-label="Show confirmation password" aria-pressed="false" title="Show confirmation password">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                        <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 15px;">
                <button type="button" class="req-btn-cancel" style="padding: 10px 24px; font-size: 0.95rem;">Cancel</button>
                <button type="submit" class="req-btn-save" style="padding: 10px 24px; font-size: 0.95rem; background: #006837;">Update Password</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                var input = document.getElementById(toggle.getAttribute('data-target'));
                if (!input) {
                    return;
                }

                var showPassword = input.type === 'password';
                var passwordName = toggle.getAttribute('aria-label').replace(/^(Show|Hide) /, '');

                input.type = showPassword ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
                toggle.setAttribute('aria-label', (showPassword ? 'Hide ' : 'Show ') + passwordName);
                toggle.setAttribute('title', (showPassword ? 'Hide ' : 'Show ') + passwordName);
            });
        });
    });
</script>
@endpush
