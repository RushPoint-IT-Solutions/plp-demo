@extends('layouts.parent')

@section('title', 'PLP - Parent Change Password')
@section('page-title', 'UPDATE PASSWORD')
@section('body-class', 'page-student-account page-student-change-password')

@section('content')
<div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: calc(100vh - 140px); padding: 40px 20px;">
    <div style="background-color: #ffffff; border-radius: 12px; padding: 40px 50px; width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.12), 0 8px 16px rgba(0,0,0,0.06); border: 1px solid #eaeaea; box-sizing: border-box;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h3 style="margin: 0; color: #222; font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px;">Update Password</h3>
            <p style="margin: 8px 0 0; color: #006837; font-size: 0.95rem; font-weight: 500; background: #e8f5e9; display: inline-block; padding: 6px 14px; border-radius: 20px;">For parent portal account</p>
        </div>

        <form style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display: block; font-weight: 600; color: #444; font-size: 0.85rem; margin-bottom: 6px;">Username</label>
                <input type="text" class="app-filter-input" placeholder="Enter Username" style="width: 100%; padding: 12px 16px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; font-size: 0.9rem; transition: border-color 0.2s;" onfocus="this.style.borderColor='#006837'" onblur="this.style.borderColor='#ccc'">
            </div>

            <div>
                <label style="display: block; font-weight: 600; color: #444; font-size: 0.85rem; margin-bottom: 6px;">Current Password</label>
                <input type="password" class="app-filter-input" placeholder="Enter Current Password" style="width: 100%; padding: 12px 16px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; font-size: 0.9rem; transition: border-color 0.2s;" onfocus="this.style.borderColor='#006837'" onblur="this.style.borderColor='#ccc'">
            </div>

            <div style="position: relative;">
                <div style="height: 1px; background: #eee; margin: 10px 0 25px 0;"></div>

                <label style="display: block; font-weight: 600; color: #444; font-size: 0.85rem; margin-bottom: 6px;">New Password</label>
                <input type="password" class="app-filter-input" placeholder="Enter New Password" style="width: 100%; padding: 12px 16px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; font-size: 0.9rem; transition: border-color 0.2s;" onfocus="this.style.borderColor='#006837'" onblur="this.style.borderColor='#ccc'">
            </div>

            <div>
                <label style="display: block; font-weight: 600; color: #444; font-size: 0.85rem; margin-bottom: 6px;">Confirm New Password</label>
                <input type="password" class="app-filter-input" placeholder="Confirm New Password" style="width: 100%; padding: 12px 16px; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; font-size: 0.9rem; transition: border-color 0.2s;" onfocus="this.style.borderColor='#006837'" onblur="this.style.borderColor='#ccc'">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 15px;">
                <button type="button" class="req-btn-cancel" style="padding: 10px 24px; font-size: 0.95rem;">Cancel</button>
                <button type="submit" class="req-btn-save" style="padding: 10px 24px; font-size: 0.95rem; background: #006837;">Update Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
