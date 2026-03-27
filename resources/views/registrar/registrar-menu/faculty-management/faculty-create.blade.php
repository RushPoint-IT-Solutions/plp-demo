@extends('layouts.registrar')

@section('title', 'PLP - Create Faculty')
@section('page-title', 'CREATE FACULTY ACCOUNT')
@section('body-class', 'page-faculty-create')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="padding: 15px; margin: 15px 0; background: #d4edda; color: #155724; border-radius: 4px;">
        {{ session('success') }}
        @if(session('success_password'))
            <br><strong>Temporary Password:</strong> {{ session('success_password') }}
            <br><em>Please provide this to the faculty member. They will be forced to change it on their first login.</em>
        @endif
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" style="padding: 15px; margin: 15px 0; background: #f8d7da; color: #721c24; border-radius: 4px;">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="pf-page">
    <div class="panel-box" style="padding: 20px; max-width: 600px;">
        <h3>Add New Faculty</h3>
        <hr>
        <form action="{{ route('registrar-menu.faculty-mgmt.faculty-create.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Faculty Code (e.g. FAC-002)</label>
                <input type="text" name="code" class="pf-search-input" style="width: 100%; border: 1px solid #ccc; padding: 8px;" required>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Full Name</label>
                <input type="text" name="name" class="pf-search-input" style="width: 100%; border: 1px solid #ccc; padding: 8px;" required>
            </div>
            
            <button type="submit" class="pf-btn-new" style="padding: 10px 20px;">Create Faculty & Generate Login</button>
        </form>
    </div>
</div>
@endsection
