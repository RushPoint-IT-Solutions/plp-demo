@extends('layouts.faculty')

@section('title', 'PLP - Faculty Evaluation')
@section('page-title', 'FACULTY EVALUATION')

@section('content')
<div class="faculty-evaluation-wrap">

    <div class="faculty-eval-select-label">Select Subject:</div>
    <select class="faculty-eval-select" id="evalSubjectSelect">
        <option value="">All Subjects</option>
        @foreach($subjectOptions as $subjectOption)
            <option value="{{ $subjectOption->id }}" {{ (int) $selectedSubjectId === (int) $subjectOption->id ? 'selected' : '' }}>{{ $subjectOption->name }}</option>
        @endforeach
    </select>

    <div class="faculty-table-wrap">
        <table class="faculty-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Section</th>
                    <th>Mean Score</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if($hasEvaluationRows)
                    @foreach($subjects as $subject)
                        @foreach($subject->evaluations as $eval)
                        <tr>
                            <td>{{ $subject->name }}</td>
                            <td>{{ $eval->section }}</td>
                            <td class="avg-cell score-cell">{{ number_format($eval->mean_score, 1) }}</td>
                            <td>
                                <a href="#" class="eval-view-link faculty-eval-open" data-eval-id="{{ $eval->id }}">View Details</a>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                @else
                <tr><td colspan="4" class="text-center text-muted py-4">No evaluation data found.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

</div>

<div class="modal fade" id="facultyEvaluationModal" tabindex="-1" aria-labelledby="facultyEvaluationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="faculty-eval-result-head">
                    <button type="button" class="faculty-eval-back-btn" data-bs-dismiss="modal">Back</button>
                    <div class="faculty-eval-result-title" id="facultyEvaluationModalLabel">Evaluation Results</div>
                    <div></div>
                </div>

                <div class="faculty-table-wrap">
                    <table class="faculty-table">
                        <thead>
                            <tr>
                                <th>Evaluation Criteria</th>
                                <th>Mean Score (1.0 - 5.0)</th>
                                <th>Interpretation</th>
                            </tr>
                        </thead>
                        <tbody id="evalModalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var facultyEvaluationDetails = @json($evaluationDetails);
    var facultyEvaluationSelectedSubjectId = @json($selectedSubjectId ?? 0);
</script>
<script src="{{ asset('js/faculty-evaluation.js') }}"></script>
@endpush
