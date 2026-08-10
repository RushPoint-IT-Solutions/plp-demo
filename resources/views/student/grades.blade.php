@extends('layouts.student')

@section('title', 'PLP - Grades')
@section('page-title', 'GRADES')

@push('styles')
<style>
    .grades-filter-row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .grades-gwa-badge {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
        padding: 8px 18px;
        border-radius: 8px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
    }
    .grades-gwa-badge .grades-gwa-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #15803d;
    }
    .grades-gwa-badge .grades-gwa-value {
        font-size: 1.4rem;
        font-weight: 800;
        color: #15803d;
        line-height: 1.1;
    }
</style>
@endpush

@section('content')
<div class="grades-page sched-page-container">

    {{-- Semester Filter + GWA --}}
    <div class="mb-4 grades-filter-row">
        <div>
            <label class="form-label-plp">SELECTED SEMESTER</label>
            <select class="form-select form-input-long" id="semesterFilter" onchange="if(this.value){window.location='?semester='+encodeURIComponent(this.value)}else{window.location='{{ route('student.grades') }}'}">
                <option value="">All Semesters</option>
                @foreach($semesterOptions as $option)
                    @php
                        $parts = explode('|', $option);
                        $sy = $parts[0] ?? '';
                        $sem = $parts[1] ?? '';
                    @endphp
                    <option value="{{ $option }}" {{ ($selectedSemester === $option) ? 'selected' : '' }}>
                        AY {{ $sy }} {{ $sem }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="grades-gwa-badge">
            <span class="grades-gwa-label">GWA</span>
            <span class="grades-gwa-value">{{ $gwa !== null ? number_format($gwa, 2) : '-' }}</span>
        </div>
    </div>

    {{-- Grades Table --}}
    <div class="grades-scroll">
        <table class="sched-table">
            <thead>
                <tr>
                    <th class="sched-th">Code</th>
                    <th class="sched-th">Course</th>
                    <th class="sched-th">Units</th>
                    <th class="sched-th">Lec Units</th>
                    <th class="sched-th">Lab Units</th>
                    <th class="sched-th">Midterm</th>
                    <th class="sched-th">Final Grade</th>
                    <th class="sched-th">Remarks</th>
                    <th class="sched-th">Form</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gradeRows as $row)
                    @php
                        $subject = $row->subject;
                        $remarksClass = strtolower((string) $row->remarks) === 'passed'
                            ? 'remark-passed'
                            : (strtolower((string) $row->remarks) === 'incomplete' ? 'remark-incomplete' : 'remark-nyp');
                        $lecUnits = number_format((float) optional($subject)->units, 1);
                        $labUnits = '0.0';
                        $midtermPosted = !\Illuminate\Support\Facades\Schema::hasColumn('student_subject_grades', 'midterm_posted_at') || $row->midterm_posted_at;
                        $finalPosted = !\Illuminate\Support\Facades\Schema::hasColumn('student_subject_grades', 'final_posted_at') || $row->final_posted_at;
                        $displayMidterm = $midtermPosted && $row->midterm !== null ? number_format((float) $row->midterm, 2) : 'NYP';
                        $displayFinal = $finalPosted && $row->final_average !== null ? number_format((float) $row->final_average, 2) : 'NYP';
                    @endphp
                    <tr>
                        <td class="sched-td">{{ optional($subject)->code }}</td>
                        <td class="sched-td">{{ optional($subject)->name }}</td>
                        <td class="sched-td">{{ number_format((float) optional($subject)->units, 1) }}</td>
                        <td class="sched-td">{{ $lecUnits }}</td>
                        <td class="sched-td">{{ $labUnits }}</td>
                        <td class="sched-td">{{ $displayMidterm }}</td>
                        <td class="sched-td">{{ $displayFinal }}</td>
                        <td class="sched-td {{ $remarksClass }}">{{ $finalPosted ? $row->remarks : 'NYP' }}</td>
                        <td class="sched-td">
                            @if($finalPosted)
                                <a class="btn btn-sm btn-outline-success" href="{{ route('student.forms.show', 'change-grade') }}?grade_id={{ $row->id }}">Change Grade</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="sched-td" colspan="9">No posted grade records found for this student.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
