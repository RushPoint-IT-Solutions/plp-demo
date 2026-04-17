@extends('layouts.app')

@section('title', 'PLP - Parent Dashboard')

@section('content')
<section class="container py-4 py-md-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 mb-1">Parent Dashboard</h1>
            <p class="text-muted mb-4">Welcome, {{ $parent ? ($parent->full_name ?: $user->name) : $user->name }}.</p>

            @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block mb-1">Parent Number</small>
                        <strong>{{ $parent->parent_no ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block mb-1">Email</small>
                        <strong>{{ $parent->email ?? $user->email ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block mb-1">Mobile Number</small>
                        <strong>{{ $parent->mobile_number ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>

            <h2 class="h5 mb-3">Linked Students</h2>

            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Student Number</th>
                            <th scope="col">Name</th>
                            <th scope="col">Relationship</th>
                            <th scope="col">Primary Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($linkedStudents as $link)
                        <tr>
                            <td>{{ optional($link->student)->student_no ?: 'N/A' }}</td>
                            <td>{{ optional($link->student)->name ?: 'N/A' }}</td>
                            <td>{{ optional($link->relationshipType)->name ?: 'Unspecified' }}</td>
                            <td>{{ $link->is_primary_contact ? 'Yes' : 'No' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-muted">No linked students found for this parent account.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
