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
                            <div class="am-card-box"></div>
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
                            <div class="am-card-box"></div>
                            <a href="{{ route('module.login', 'faculty') }}" class="btn am-btn w-100">FACULTY</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
