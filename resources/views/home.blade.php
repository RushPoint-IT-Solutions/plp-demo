@extends('layouts.dashboard')

@section('title', 'PLP - Dashboard')

@section('content')
<div class="auth-page-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white fw-bold">Dashboard</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        You are logged in! <a href="{{ route('admin.access-module') }}">Go to Access Module</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
