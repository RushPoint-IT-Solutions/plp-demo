@extends('layouts.faculty')

@section('title', 'PLP - Faculty Evaluation')
@section('page-title', 'FACULTY EVALUATION')

@section('content')
<div class="faculty-evaluation-wrap faculty-eval-page">
    <div class="eval-layout faculty-eval-layout">
        <div class="eval-builder faculty-eval-results-panel">
            <h2 class="faculty-eval-panel-title">Evaluation Results</h2>

            <div class="faculty-eval-tabs" role="tablist" aria-label="Evaluation result tabs">
                <button type="button" class="faculty-eval-tab active" id="facultyEvalTabScores" data-tab="scores" aria-selected="true">Scores</button>
                <button type="button" class="faculty-eval-tab" id="facultyEvalTabComments" data-tab="comments" aria-selected="false">Comments</button>
            </div>

            <div class="faculty-eval-results-body" id="facultyEvalResultsBody">
                <div class="faculty-eval-empty-message">
                    Select a subject from the Evaluation List to view its evaluation results. You can only view one subject at a time.
                </div>
            </div>
        </div>

        <div class="eval-dashboard faculty-eval-list-panel">
            <h2 class="faculty-eval-panel-title">Evaluation List</h2>
            <h3 class="faculty-eval-list-subtitle">Recent Evaluations</h3>
            <div id="facultyEvalList"></div>
        </div>
    </div>

    <div id="facultyEvalData"
         data-library='@json($evaluationLibrary)'
         data-panels='@json($evaluationPanels)'
         data-selected='{{ (int) $selectedSubjectId }}'>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/faculty-evaluation.js') }}"></script>
@endpush
