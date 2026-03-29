@extends('layouts.student')

@section('title', 'PLP - Events')
@section('page-title', 'UNIVERSITY EVENTS CALENDAR')

@section('content')
<div class="events-page">

    {{-- Calendar Controls: Nav | Month Title | Legend --}}
    <div class="cal-controls">

        {{-- Nav buttons --}}
        <div class="cal-nav">
            <button class="cal-btn" id="prevMonthBtn" aria-label="Previous month">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <button class="cal-btn" id="todayBtn">Today</button>
            <button class="cal-btn" id="nextMonthBtn" aria-label="Next month">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        </div>

        {{-- Month / Year title --}}
        <h2 class="cal-month-title" id="calendarMonthYear">Month 2026</h2>

        {{-- Legend --}}
        <div class="cal-legend">
            <strong>Event Type</strong>
            <div class="cal-legend-items">
                <span class="cal-legend-item"><span class="dot dot-holiday"></span> Holiday</span>
                <span class="cal-legend-item"><span class="dot dot-event"></span> University Events</span>
            </div>
        </div>

    </div>

    {{-- Calendar Table — reuses .sched-th for day-name headers --}}
    <div class="grades-scroll">
        <table class="cal-table">
            <thead>
                <tr>
                    <th class="sched-th">Sun</th>
                    <th class="sched-th">Mon</th>
                    <th class="sched-th">Tue</th>
                    <th class="sched-th">Wed</th>
                    <th class="sched-th">Thurs</th>
                    <th class="sched-th">Fri</th>
                    <th class="sched-th">Sat</th>
                </tr>
            </thead>
            <tbody id="calendarBody">
                {{-- JS injects day cells here --}}
            </tbody>
        </table>
    </div>

</div>

@push('scripts')
<script>
    window.calendarEventsData = @json($calendarEvents ?? []);
</script>
<script src="{{ asset('js/student-events.js') }}"></script>
@endpush
@endsection
