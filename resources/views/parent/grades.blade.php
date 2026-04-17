@extends('layouts.parent')

@section('title', 'PLP - Parent Grades')
@section('page-title', 'GRADES')

@section('content')
<div class="grades-page parent-grades-snapshot-page">
    <form method="GET" action="{{ route('parent.grades') }}" class="parent-grades-student-row">
        <label class="form-label-plp" for="parentGradesChild">STUDENT</label>
        <select id="parentGradesChild" name="child" class="form-select form-input-long" onchange="this.form.submit()">
            @forelse($children as $child)
                <option value="{{ $child['id'] }}" {{ (string) ($child['id'] ?? '') === (string) ($selectedChildId ?: data_get($selectedChild, 'id')) ? 'selected' : '' }}>
                    {{ $child['name'] ?: 'Current Student' }}{{ !empty($child['student_no']) ? ' (' . $child['student_no'] . ')' : '' }}
                </option>
            @empty
                <option selected>No linked child yet</option>
            @endforelse
        </select>
    </form>

    @if(!empty($hasDeficiencies))
        <div class="parent-grades-deficiency-shell">
            <section class="parent-grades-deficiency-alert" role="alert" aria-live="polite">
                <h2>
                    <span>ALERT</span>
                    <svg class="parent-grades-alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.72 3h16.92a2 2 0 0 0 1.72-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </h2>
                <p>
                    You currently have <strong>deficiencies</strong>. Viewing of grades has been denied.
                    You must settle your deficiencies before you can view your grades again.
                    Below are the deficiencies currently recorded:
                </p>
                <ul>
                    @foreach($deficiencyItems as $deficiency)
                        <li>{{ $deficiency }}</li>
                    @endforeach
                </ul>
            </section>
        </div>
    @else

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
    @endif
</div>
@endsection
