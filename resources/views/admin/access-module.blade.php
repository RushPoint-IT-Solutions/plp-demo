@extends('layouts.app')

@section('title', 'PLP - Access Module')

@section('content')
<section class="am-section py-4 py-md-5">
    <div class="am-watermark" aria-hidden="true">
        <img src="{{ asset('img/logobg.png') }}" alt="">
    </div>

    <div class="container-fluid h-100">
        <div class="row justify-content-center h-100">
            <div class="col-12 col-xl-10 d-flex flex-column align-items-center justify-content-center">
                <h2 class="am-title text-center mb-4 mb-md-5">ACCESS MODULE</h2>

                <div class="row justify-content-center g-3 g-md-4 w-100 am-cards-row">
                    {{-- Registrar --}}
                    <div class="col-6 col-sm-5 col-md-4 col-lg-3">
                        <div class="am-card mx-auto">
                            <div class="am-card-box">
                                <svg class="am-card-icon" viewBox="0 0 24 24" fill="none" stroke="#0a7d3f" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="5" y="3.5" width="14" height="18" rx="1.6"></rect>
                                    <path d="M9 3.5V3a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 3v.5"></path>
                                    <rect x="9" y="1.5" width="6" height="3" rx="0.8"></rect>
                                    <line x1="8" y1="10" x2="16" y2="10"></line>
                                    <line x1="8" y1="13.2" x2="16" y2="13.2"></line>
                                    <line x1="8" y1="16.4" x2="12.5" y2="16.4"></line>
                                </svg>
                            </div>
                            <a href="{{ route('module.login', 'registrar') }}" class="btn am-btn w-100">REGISTRAR</a>
                        </div>
                    </div>

                    {{-- Accounting --}}
                    {{-- Accounting (temporarily hidden) --}}
                    {{--
                    <div class="col-6 col-sm-5 col-md-4 col-lg-3">
                        <div class="am-card mx-auto">
                            <div class="am-card-box"></div>
                            <a href="{{ route('module.login', 'accounting') }}" class="btn am-btn w-100">ACCOUNTING</a>
                        </div>
                    </div>
                    --}}

                    {{-- Cashier --}}
                    {{-- Cashier (temporarily hidden) --}}
                    {{--
                    <div class="col-6 col-sm-5 col-md-4 col-lg-3">
                        <div class="am-card mx-auto">
                            <div class="am-card-box"></div>
                            <a href="{{ route('module.login', 'cashier') }}" class="btn am-btn w-100">CASHIER</a>
                        </div>
                    </div>
                    --}}

                    {{-- Faculty --}}
                    <div class="col-6 col-sm-5 col-md-4 col-lg-3">
                        <div class="am-card mx-auto">
                            <div class="am-card-box">
                                <svg class="am-card-icon" viewBox="0 0 24 24" fill="none" stroke="#0a7d3f" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 5.5 21 9.5 12 13.5 3 9.5 12 5.5Z"></path>
                                    <path d="M7 10.9v4.1c0 1.5 2.24 2.8 5 2.8s5-1.3 5-2.8v-4.1"></path>
                                    <path d="M21 9.5v5"></path>
                                </svg>
                            </div>
                            <a href="{{ route('module.login', 'faculty') }}" class="btn am-btn w-100">FACULTY</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
