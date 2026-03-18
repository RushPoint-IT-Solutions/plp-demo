<!-- resources/views/student/schedule.blade.php -->
@extends('layouts.student')

@section('title', 'Schedule - PLP')

@section('page-title', 'Schedule')

@section('content')
<div class="sched-page-container">

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
        <tbody>
            @foreach($subjects as $subject)
            <tr>
                <td class="sched-td" data-label="Code">{{ $subject->code }}</td>
                <td class="sched-td" data-label="Subject">{{ $subject->name }}</td>
                <td class="sched-td" data-label="Units">{{ number_format($subject->units, 1) }}</td>
                <td class="sched-td" data-label="Days">{{ $subject->days }}</td>
                <td class="sched-td" data-label="Time">{{ $subject->time_range }}</td>
                <td class="sched-td" data-label="Room">{{ $subject->room }}</td>
                <td class="sched-td" data-label="Faculty">{{ $subject->faculty }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>{{-- /.sched-scroll-wrapper --}}

    <!-- ===== Weekly Schedule Card ===== -->
    <div class="so-weekly">
        <h2 class="so-weekly-title">My Weekly Schedule</h2>

        <div class="so-weekly-scroll">
        <div class="so-weekly-grid">

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
