@extends('layouts.faculty')

@section('title', 'PLP - Class List')
@section('page-title', 'CLASS LIST')

@section('content')
<div class="faculty-class-list-wrap">

    <div id="classListSubjectView">
        <p class="faculty-section-header">Kindly select a subject to view Class List. You can only select one at a time.</p>

        <div class="faculty-table-wrap">
            <table class="faculty-table" id="classListTable">
                <thead>
                    <tr>
                        <th>View List</th>
                        <th>Subject Code</th>
                        <th>Subject Description</th>
                        <th>Units</th>
                        <th>Days</th>
                        <th>Time</th>
                        <th>Room No.</th>
                        <th>Yr&amp;Section</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                    <tr class="faculty-click-row class-list-row"
                        data-subject-id="{{ $subject->id }}"
                        data-subject-name="{{ $subject->name }}"
                        data-subject-section="{{ trim(($subject->course ?: '') . ' ' . ($subject->year_section ?: '')) }}">
                        <td class="text-center">
                            <a href="#" class="grading-view-link class-list-open-link">View</a>
                        </td>
                        <td class="td-code">{{ $subject->code }}</td>
                        <td>{{ $subject->name }}</td>
                        <td>{{ number_format($subject->units, 1) }}</td>
                        <td>{{ str_replace(',', ', ', $subject->days) }}</td>
                        <td>{{ $subject->formatted_time }}</td>
                        <td>{{ $subject->room }}</td>
                        <td>{{ $subject->year_section }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No subjects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="classListDetailView" style="display:none;">
        <div class="faculty-detail-header">
            <button type="button" class="faculty-back-btn" id="classListBackBtn">Back</button>
            <div class="faculty-detail-title" id="classListDetailTitle">Class List</div>
            <div class="faculty-detail-section" id="classListDetailSection"></div>
        </div>

        <div class="faculty-table-wrap">
            <table class="faculty-table" id="classListDetailTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Program / Yr / Block</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="classListDetailBody"></tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    var subjectStudents = @json($subjectStudents);
</script>
<script src="{{ asset('js/faculty-class-list.js') }}"></script>
@endpush
@endsection
