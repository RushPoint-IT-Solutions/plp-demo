<!-- resources/views/student/schedule.blade.php -->
@extends('layouts.student')

@section('title', 'Schedule - PLP')

@section('page-title', 'Schedule')

@section('content')
<div class="sched-page-container">

    <div class="sched-print-head d-none d-print-flex" id="schedPrintHead">
        <div class="sched-print-head-left">
            <span><strong>NAME:</strong> {{ optional($student)->name ?: 'Student' }}</span>
            <span><strong>SECTION:</strong> {{ optional($student)->year_level ?: 'N/A' }}</span>
        </div>
        <div class="sched-print-head-right" id="schedPrintMetaTop">School Year: 2025-2026 | Semester: Second Semester | Generated: 04/10/2026</div>
    </div>

    <div class="sched-filter-bar">
        <div class="sched-filter-row-main d-flex align-items-end justify-content-between w-100 flex-nowrap" style="gap: 20px;">
            <div class="d-flex align-items-end">
                <div class="sched-filter-group mr-3">
                    <label class="app-filter-label" for="schedSchoolYear">School Year</label>
                    <select id="schedSchoolYear" class="app-filter-select">
                        <option value="2025-2026">2025-2026</option>
                        <option value="2024-2025">2024-2025</option>
                    </select>
                </div>
                <div class="sched-filter-group">
                    <label class="app-filter-label" for="schedSemester">Semester</label>
                    <select id="schedSemester" class="app-filter-select">
                        <option value="Second">Second</option>
                        <option value="First">First</option>
                    </select>
                </div>
            </div>
            <div class="sched-filter-actions d-flex align-items-end">
                <a href="{{ route('student.cor') }}" class="btn btn-view-cor mr-3" style="width: auto; padding: 0 30px; display: flex; align-items: center; justify-content: center; height: 42px;">View COR</a>
                <button type="button" id="schedDownloadBtn" class="btn btn-view-cor sched-download-btn" style="width: auto; padding: 0 30px; height: 42px;">Download Schedule</button>
            </div>
        </div>
    </div>

    <!-- ===== Subject List Table ===== -->
    <div class="sched-scroll-wrapper">
    <table class="sched-table">
        <thead>
            <tr>
                <th class="sched-th">Code</th>
                <th class="sched-th">Subject</th>
                <th class="sched-th">Units</th>
                <th class="sched-th">Days</th>
                <th class="sched-th">Time</th>
                <th class="sched-th">Room</th>
                <th class="sched-th">Faculty</th>
            </tr>
        </thead>
        <tbody id="schedSubjectTableBody">
            @foreach($subjects as $subject)
            <tr>
                <td class="sched-td" data-label="Code">{{ $subject->code }}</td>
                <td class="sched-td" data-label="Subject">{{ $subject->name }}</td>
                <td class="sched-td" data-label="Units">{{ number_format($subject->units, 1) }}</td>
                <td class="sched-td" data-label="Days">{{ $subject->days }}</td>
                <td class="sched-td" data-label="Time">{{ $subject->time_range }}</td>
                <td class="sched-td" data-label="Room">{{ $subject->room }}</td>
                <td class="sched-td" data-label="Faculty">{{ $subject->faculty ?: 'Abelo, M.' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>{{-- /.sched-scroll-wrapper --}}

    <!-- ===== Weekly Schedule Card ===== -->
    <div class="so-weekly">
        <h2 class="so-weekly-title">My Weekly Schedule</h2>

        <div class="sched-print-meta" id="schedPrintMeta">School Year: 2025-2026 | Semester: Second Semester | Generated: 04/10/2026</div>

        <div class="so-weekly-scroll">
        <div class="so-weekly-grid" id="schedWeeklyGrid">

            @php($dayList = is_iterable($days ?? null) ? $days : ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])
            @foreach($dayList as $day)
            <div class="so-weekly-col">
                <div class="so-weekly-day">{{ $day }}</div>
                <div class="so-weekly-body">
                    @php($dayItems = data_get($weekly, $day, []))
                    @forelse($dayItems as $subject)
                    <div class="so-weekly-card">
                        <p class="so-weekly-code">{{ $subject->code }}</p>
                        <p class="so-weekly-section">{{ $subject->name }}</p>
                        <p class="so-weekly-time">{{ $subject->time_start }}–{{ $subject->time_end }}</p>
                        <p class="so-weekly-room">{{ strtoupper($subject->room) }}</p>
                    </div>
                    @empty
                    <div class="so-weekly-empty">No class</div>
                    @endforelse
                </div>
            </div>
            @endforeach

        </div>{{-- /.so-weekly-grid --}}
        </div>{{-- /.so-weekly-scroll --}}
    </div>{{-- /.so-weekly --}}

</div>{{-- /.sched-page-container --}}
@endsection

@push('scripts')
<script src="{{ asset('js/student-schedule.js') }}"></script>
@endpush
