@extends('layouts.applicant')

@section('title', 'PLP - Exam Result')
@section('page-title', 'EXAM RESULT')

@section('content')
<div class="applicant-exam-wrap">

    {{-- Applicant ID + Name --}}
    <div class="applicant-info-row">
        <div class="applicant-info-field">
            <label class="applicant-field-label">APPLICANT ID</label>
            <input type="text" class="applicant-input applicant-readonly" value="{{ $applicant->applicant_id }}" readonly>
        </div>
        <div class="applicant-info-field flex-grow-1">
            <label class="applicant-field-label">APPLICANT NAME</label>
            <input type="text" class="applicant-input applicant-readonly" value="{{ $applicant->first_name }} {{ $applicant->last_name }}" readonly>
        </div>
    </div>

    {{-- Result display --}}
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
    <div class="applicant-no-result">
        {{-- No result yet – blank area matches the screenshot --}}
    </div>
    @endif

</div>
@endsection
