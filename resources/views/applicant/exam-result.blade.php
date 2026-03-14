@extends('layouts.applicant')

@section('title', 'PLP - Exam Result')
@section('page-title', 'EXAM RESULT')

@section('content')
<div class="app-process-page applicant-consistent-page">
    <div class="app-filter-bar">
        <div class="app-filter-row">
            <div class="app-filter-group" style="max-width: 260px;">
                <label class="app-filter-label">Applicant ID</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->applicant_id }}">
            </div>
            <div class="app-filter-group" style="max-width: 260px;">
                <label class="app-filter-label">Applicant Name</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->first_name }} {{ $applicant->last_name }}">
            </div>
        </div>
    </div>

    <div class="student-table-wrapper applicant-content-shell">
        @if($applicant->exam_result_status)
        <div class="applicant-result-box">
            <div class="result-status-badge result-{{ strtolower($applicant->exam_result_status) }}">
                {{ strtoupper($applicant->exam_result_status) }}
            </div>
            @if($applicant->exam_score !== null)
            <p class="result-score">Score: <strong>{{ $applicant->exam_score }}</strong></p>
            @endif
        </div>
        @else
        <div class="applicant-no-result"></div>
        @endif
    </div>
</div>
@endsection
