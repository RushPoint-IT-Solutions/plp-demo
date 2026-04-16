@extends('layouts.parent')

@section('title', 'PLP - Parent Grades')
@section('page-title', 'GRADES')

@section('content')
<div class="grades-page parent-grades-snapshot-page">
    <div class="parent-grades-student-row">
        <label class="form-label-plp">STUDENT</label>
        <select class="form-select form-input-long" disabled>
            @forelse($children as $child)
                <option selected>
                    {{ $child['name'] ?: 'Current Student' }}{{ $child['student_no'] ? ' (' . $child['student_no'] . ')' : '' }}
                </option>
            @empty
                <option selected>No linked child yet</option>
            @endforelse
        </select>
    </div>

    @foreach($termSections as $section)
        <div class="parent-grades-term-block">
                <div class="parent-grades-meta-grid">
                    <div class="parent-meta-item"><span>Academic Year</span><div class="parent-meta-value">{{ $section['academic_year'] }}</div></div>
                    <div class="parent-meta-item"><span>Term</span><div class="parent-meta-value">{{ $section['term'] }}</div></div>
                    <div class="parent-meta-item"><span>Admission Status</span><div class="parent-meta-value">{{ $section['admission_status'] }}</div></div>
                    <div class="parent-meta-item"><span>Academic Status</span><div class="parent-meta-value">{{ $section['academic_status'] }}</div></div>
                    <div class="parent-meta-item"><span>Program</span><div class="parent-meta-value">{{ $section['program'] }}</div></div>
                    <div class="parent-meta-item"><span>Program Description</span><div class="parent-meta-value">{{ $section['program_description'] }}</div></div>
                    <div class="parent-meta-item"><span>GPA (excludes NSTP and subjects with non-numeric ratings)</span><div class="parent-meta-value">{{ $section['gpa'] !== null ? number_format((float) $section['gpa'], 2) : '0.00' }}</div></div>
                    <div class="parent-meta-item"><span>Reminder</span><div class="parent-meta-note">GPA computation is final. Any alter of grades is all enrolled subjects for the semester are completed.</div></div>
                </div>

                <div class="grades-scroll">
                    <table class="sched-table parent-grade-grid-table">
                        <thead>
                            <tr>
                                <th class="sched-th">#</th>
                                <th class="sched-th">Subject Code</th>
                                <th class="sched-th">Description</th>
                                <th class="sched-th">Faculty Name</th>
                                <th class="sched-th">Units</th>
                                <th class="sched-th">Section</th>
                                <th class="sched-th">Midterm</th>
                                <th class="sched-th">Finals</th>
                                <th class="sched-th">Final Grade</th>
                                <th class="sched-th">Grade Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($section['rows'] as $index => $row)
                                @php
                                    $subject = $row->subject;
                                    $remarks = trim((string) $row->remarks);
                                @endphp
                                <tr>
                                    <td class="sched-td">{{ $index + 1 }}</td>
                                    <td class="sched-td">{{ optional($subject)->code ?: 'N/A' }}</td>
                                    <td class="sched-td">{{ optional($subject)->name ?: 'N/A' }}</td>
                                    <td class="sched-td">{{ optional($subject)->faculty_name ?: 'Abela, Manuel' }}</td>
                                    <td class="sched-td">{{ number_format((float) optional($subject)->units, 0) }}</td>
                                    <td class="sched-td">{{ optional($subject)->section ?: 'BSMT 3-A' }}</td>
                                    <td class="sched-td">{{ $row->midterm !== null ? number_format((float) $row->midterm, 2) : '' }}</td>
                                    <td class="sched-td">{{ $row->final !== null ? number_format((float) $row->final, 2) : '' }}</td>
                                    <td class="sched-td">{{ $row->final_average !== null ? number_format((float) $row->final_average, 2) : '' }}</td>
                                    <td class="sched-td">{{ $remarks !== '' ? $remarks : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="sched-td" colspan="10">No grade records found for this term.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
    @endforeach
</div>
@endsection
