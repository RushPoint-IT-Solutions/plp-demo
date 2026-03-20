@extends('layouts.student')

@section('title', 'Forms - PLP')
@section('page-title', 'FORMS')

@section('content')
<div class="forms-page-container">
    <h3 class="mb-3">{{ $categories[$category] ?? 'Forms' }}</h3>
    <p class="text-muted">Upload files to <code>public/forms/{{ $category }}</code> to make them available for download.</p>

    @if($forms->isEmpty())
        <div class="alert alert-info">
            No forms are currently available for this sub-module.
        </div>
    @else
        <div class="forms-list">
            <ul class="list-group">
                @foreach($forms as $form)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $form }}</span>
                        <a href="{{ asset('forms/' . $category . '/' . $form) }}" class="btn btn-sm btn-primary" download>
                            Download
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
