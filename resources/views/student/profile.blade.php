@extends('layouts.student')

@section('title', 'PLP - Profile')
@section('page-title', 'PROFILE')

@section('content')
<div class="student-page-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar-wrapper">
                <img src="{{ asset('img/profile.png') }}" alt="Profile Photo" class="profile-avatar-lg">
            </div>
            <div class="profile-info">
                <h3 class="profile-name">Juan Dela Cruz</h3>
                <p class="profile-detail"><strong>Student No:</strong> 2024-00001</p>
                <p class="profile-detail"><strong>Program:</strong> BS Information Technology</p>
                <p class="profile-detail"><strong>Year Level:</strong> 4th Year</p>
                <p class="profile-detail"><strong>Section:</strong> BSIT-4A</p>
                <p class="profile-detail"><strong>Email:</strong> juan.delacruz@plp.edu.ph</p>
            </div>
        </div>
    </div>
</div>
@endsection
