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
        <div class="row justify-content-center g-4">
            {{-- Registrar --}}
            <div class="col-6 col-md-3">
                <div class="module-card">
                    <div class="module-card-icon">
                        {{-- Placeholder for future icon/image --}}
                    </div>
                    <a href="{{ route('module.login', 'registrar') }}" class="module-card-btn">Registrar</a>
                </div>
            </div>

            {{-- Accounting --}}
            <div class="col-6 col-md-3">
                <div class="module-card">
                    <div class="module-card-icon">
                        {{-- Placeholder for future icon/image --}}
                    </div>
                    <a href="{{ route('module.login', 'accounting') }}" class="module-card-btn">Accounting</a>
                </div>
            </div>

            {{-- Cashier --}}
            <div class="col-6 col-md-3">
                <div class="module-card">
                    <div class="module-card-icon">
                        {{-- Placeholder for future icon/image --}}
                    </div>
                    <a href="{{ route('module.login', 'cashier') }}" class="module-card-btn">Cashier</a>
                </div>
            </div>

            {{-- Faculty --}}
            <div class="col-6 col-md-3">
                <div class="module-card">
                    <div class="module-card-icon">
                        {{-- Placeholder for future icon/image --}}
                    </div>
                    <a href="{{ route('module.login', 'faculty') }}" class="module-card-btn">Faculty</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
