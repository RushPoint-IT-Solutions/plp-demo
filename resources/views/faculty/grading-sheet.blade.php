@extends('layouts.faculty')

@section('title', 'PLP - Grading Sheet')
@section('page-title', 'GRADING SHEET')

@section('content')
<div class="grading-sheet-wrap">

    <p class="faculty-section-header">Select a subject to encode grades.</p>

    <div class="app-table-wrap">
        <table class="app-table">
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
                <tr>
                    <td class="text-center">
                        @if($subject->grading_status === 'Submitted')
                            <a href="#" class="grading-view-link">View</a>
                        @else
                            <input type="checkbox" class="faculty-subject-checkbox">
                        @endif
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

</div>
@endsection
