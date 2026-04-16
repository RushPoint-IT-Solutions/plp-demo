@extends('layouts.login')

@section('title', 'PLP - Parent Create Account')

@section('content')
<div class="login-card-wrapper">
    <div class="login-card">
        <h2 class="login-card-title">PARENT CREATE ACCOUNT</h2>

        <form onsubmit="event.preventDefault(); window.location='{{ route('module.login', ['module' => 'parent']) }}';">
            <div class="login-field-group">
                <label for="parent_name" class="login-label">FULL NAME</label>
                <input id="parent_name" type="text" class="login-input" placeholder="Enter Full Name" required>
            </div>

            <div class="login-field-group">
                <label for="parent_username" class="login-label">USERNAME</label>
                <input id="parent_username" type="text" class="login-input" placeholder="Enter Username" required>
            </div>

            <div class="login-field-group">
                <label for="parent_email" class="login-label">EMAIL</label>
                <input id="parent_email" type="email" class="login-input" placeholder="Enter Email" required>
            </div>

            <div class="login-field-group">
                <label for="parent_password" class="login-label">PASSWORD</label>
                <input id="parent_password" type="password" class="login-input" placeholder="Enter Password" required>
            </div>

            <div class="login-field-group">
                <label for="parent_password_confirmation" class="login-label">CONFIRM PASSWORD</label>
                <input id="parent_password_confirmation" type="password" class="login-input" placeholder="Confirm Password" required>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="login-submit-btn">Create Account</button>
            </div>

            <div class="text-center mt-2">
                <a href="{{ route('module.login', ['module' => 'parent']) }}" class="login-submit-btn" style="display:inline-block;background:#f5f8f6;color:#006837;border:1px solid #006837;text-decoration:none;">Back to Parent Login</a>
            </div>
        </form>
    </div>
</div>
@endsection
