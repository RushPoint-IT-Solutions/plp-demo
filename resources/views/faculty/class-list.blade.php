@extends('layouts.faculty')

@section('title', 'PLP - Class List')
@section('page-title', 'CLASS LIST')

@section('content')
<div class="faculty-class-list-wrap">

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
                <tr>
                    <td class="text-center">
                        <input type="checkbox"
                               class="faculty-subject-checkbox class-list-check"
                               data-subject-id="{{ $subject->id }}"
                               data-subject-name="{{ $subject->name }}">
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

    <div class="faculty-view-btn" id="viewListBtn">
        <button class="btn-view-list" id="openClassListModal">View List</button>
    </div>

</div>

{{-- Class List Modal --}}
<div class="modal fade" id="classListModal" tabindex="-1" aria-labelledby="classListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#006837;color:#fff;">
                <h5 class="modal-title" id="classListModalLabel">Class List</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="faculty-table" id="classListModalTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student No.</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Year Level</th>
                        </tr>
                    </thead>
                    <tbody id="classListModalBody">
                    </tbody>
                </table>
            </div>
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
