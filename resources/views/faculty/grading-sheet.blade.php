@extends('layouts.faculty')

@section('title', 'PLP - Grading Sheet')
@section('page-title', 'GRADING SHEET')

@section('content')
<div class="grading-sheet-wrap">

    <div id="gradingSubjectView">
        <p class="faculty-section-header">Select a subject to encode grades.</p>

        <div class="faculty-table-wrap">
            <table class="faculty-table" id="gradingSubjectTable">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Subject Code</th>
                        <th>Subject Description</th>
                        <th>Units</th>
                        <th>Days</th>
                        <th>CourseYr&amp;Section</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                    <tr class="grading-subject-row" data-subject-id="{{ $subject->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="faculty-subject-checkbox grading-subject-check" data-subject-id="{{ $subject->id }}">
                        </td>
                        <td class="td-code">{{ $subject->code }}</td>
                        <td>{{ $subject->name }}</td>
                        <td>{{ number_format($subject->units, 1) }}</td>
                        <td>{{ str_replace(',', ', ', $subject->days) }}</td>
                        <td>{{ $subject->course }} {{ $subject->year_section }}</td>
                        <td>
                            @if($subject->grading_status === 'Submitted')
                                <span class="grading-status-submitted">Submitted</span>
                            @else
                                <span class="grading-status-open">Open For Encoding</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No subjects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="faculty-detail-actions" id="gradingViewAction" style="display:none; margin-top: 12px;">
            <button type="button" class="btn-view-list" id="gradingViewBtn">View Selected</button>
        </div>
    </div>

    <form id="gradingDetailForm" method="POST" action="{{ route('faculty.grading-sheet.update') }}" style="display:none;">
        @csrf
        <input type="hidden" name="subject_id" id="gradingSubjectIdInput" value="">

        <div class="faculty-detail-header">
            <button type="button" class="faculty-back-btn" id="gradingBackBtn">Back</button>
            <div class="faculty-detail-title" id="gradingDetailTitle"></div>
            <div class="faculty-detail-section" id="gradingDetailSection"></div>
        </div>

        <div class="faculty-table-wrap">
            <table class="faculty-table" id="gradingDetailTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Final</th>
                        <th>Final Average</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="gradingDetailBody"></tbody>
            </table>
        </div>

        <div class="faculty-detail-actions" id="gradingInputAction" style="display:none; margin-top: 12px;">
            <button type="button" class="btn-view-list" id="gradingInputBtn">Input Grades</button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    var gradingSubjects = @json($gradingSubjects);
</script>
<script src="{{ asset('js/faculty-grading-sheet.js') }}"></script>
@endpush
