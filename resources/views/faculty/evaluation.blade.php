@extends('layouts.faculty')

@section('title', 'PLP - Faculty Evaluation')
@section('page-title', 'FACULTY EVALUATION')

@section('content')
<div class="faculty-evaluation-wrap">

    <div class="faculty-eval-select-label">Select Subject:</div>
    <select class="faculty-eval-select" id="evalSubjectSelect">
        @foreach($subjects as $subject)
            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
        @endforeach
    </select>

    <div class="app-table-wrap">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Section</th>
                    <th>Mean Score</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                    @foreach($subject->evaluations as $eval)
                    <tr>
                        <td>{{ $subject->name }}</td>
                        <td>{{ $eval->section }}</td>
                        <td>{{ number_format($eval->mean_score, 1) }}</td>
                        <td><a href="#" class="eval-view-link">View Details</a></td>
                    </tr>
                    @endforeach
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No evaluation data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
