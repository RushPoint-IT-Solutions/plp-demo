@extends('layouts.app')

@section('title', 'PLP - Access Module')

@section('content')
<section class="access-module-section">
    {{-- Background watermark logo --}}
    <div class="bg-watermark">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Background Logo">
    </div>

    {{-- Title --}}
    <h2 class="access-module-title">ACCESS MODULE</h2>

    {{-- Module Cards --}}
    <div class="module-cards-wrapper">
        <div class="row justify-content-center g-5">
            {{-- Applicant --}}
            <div class="col-6 col-md-4">
                <div class="module-card">
                    <div class="module-card-icon">
                        {{-- Placeholder for future icon/image --}}
                    </div>
                    <a href="{{ route('module.login', 'applicant') }}" class="module-card-btn">Applicant</a>
                </div>
            </div>

            {{-- Student --}}
            <div class="col-6 col-md-4">
                <div class="module-card">
                    <div class="module-card-icon">
                        {{-- Placeholder for future icon/image --}}
                    </div>
                    <a href="{{ route('module.login', 'student') }}" class="module-card-btn">Student</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection