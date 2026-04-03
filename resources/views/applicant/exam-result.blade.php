@extends('layouts.applicant')

@section('title', 'PLP - Exam Result')
@section('page-title', 'EXAM RESULT')

@section('content')
<div class="app-process-page applicant-consistent-page">
    <div class="app-filter-bar">
        <div class="app-filter-row">
            <div class="app-filter-group" style="max-width: 260px;">
                <label class="app-filter-label">Applicant ID</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->applicant_id }}" readonly>
            </div>
            <div class="app-filter-group" style="max-width: 260px;">
                <label class="app-filter-label">Applicant Name</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->first_name }} {{ $applicant->last_name }}" readonly>
            </div>
        </div>
    </div>

    <div class="student-table-wrapper applicant-content-shell">
        @if($applicant->exam_date)
        <div class="applicant-result-box">
            <div class="result-status-badge result-{{ strtolower($applicant->exam_result_status) }}">
                {{ strtoupper($applicant->exam_result_status) }}
            </div>
            @if($applicant->exam_score !== null)
            <p class="result-score">Score: <strong>{{ $applicant->exam_score }}</strong></p>
            @endif

            @if(strtolower((string) $applicant->exam_result_status) === 'passed')
                <p class="result-score" style="margin-top:8px;">Congratulations. Please proceed to admissions requirements processing.</p>
            @elseif(strtolower((string) $applicant->exam_result_status) === 'failed')
                <p class="result-score" style="margin-top:8px;">You may contact admissions for guidance on the next application cycle.</p>
            @elseif(strtolower((string) $applicant->exam_result_status) === 'pending')
                <p class="result-score" style="margin-top:8px;">Your exam has been recorded. Result release is still pending.</p>
            @endif
        </div>
        @else
        <div class="applicant-no-result" style="padding:20px; color:#555;">No exam result is available yet.</div>
        @endif
    </div>
</div>
@endsection
