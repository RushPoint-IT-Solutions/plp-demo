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
    <div class="weekly-box">
        <h2 class="weekly-title">My Weekly Schedule</h2>

        <div class="weekly-scroll-wrapper">
        <div class="weekly-grid">

            @foreach($days as $day)
            <div class="weekly-col">
                <div class="weekly-day-header">{{ $day }}</div>
                <div class="weekly-day-body">
                    @foreach($weekly[$day] as $subject)
                    <div class="weekly-card">
                        <p class="wc-code">{{ $subject->code }}</p>
                        <p class="wc-name">{{ $subject->name }}</p>
                        <p class="wc-time">{{ $subject->time_start }}–{{ $subject->time_end }}</p>
                        <p class="wc-room">{{ strtoupper($subject->room) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

        </div>{{-- /.weekly-grid --}}
        </div>{{-- /.weekly-scroll-wrapper --}}
    </div>{{-- /.weekly-box --}}

</div>{{-- /.sched-page-container --}}
@endsection
