@extends('layouts.faculty')

@section('title', 'PLP - Faculty Load')
@section('page-title', 'FACULTY LOAD')

@section('content')
<div class="faculty-load-wrap">

    <div class="faculty-table-wrap">
        <table class="faculty-table">
            <thead>
                <tr>
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
                    <td class="td-code">{{ $subject->code }}</td>
                    <td>{{ $subject->name }}</td>
                    <td>{{ number_format($subject->units, 1) }}</td>
                    <td>{{ str_replace(',', ', ', $subject->days) }}</td>
                    <td>{{ $subject->formatted_time }}</td>
                    <td>{{ $subject->room }}</td>
                    <td>{{ $subject->year_section }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No subjects found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="faculty-load-actions">
        <a href="#" class="btn-faculty-download">Download Schedule</a>
    </div>

</div>
@endsection
