@extends('layouts.faculty')

@section('title', 'PLP - University Events Calendar')
@section('page-title', 'UNIVERSITY EVENTS CALENDAR')

@section('content')
<div class="faculty-calendar-wrap">
    <div class="fc-nav">
        <div class="fc-nav-left">
            <button class="fc-btn" id="fcPrev">&#8249;</button>
            <button class="fc-btn" id="fcToday">Today</button>
            <button class="fc-btn" id="fcNext">&#8250;</button>
        </div>
        <div class="fc-month-title" id="fcMonthTitle"></div>
        <div class="fc-legend">
            <div class="fc-legend-title">EVENT TYPE</div>
            <div class="fc-legend-item"><span class="fc-dot holiday"></span> Holiday</div>
            <div class="fc-legend-item"><span class="fc-dot university"></span> University Events</div>
        </div>
    </div>

    <table class="fc-grid" id="fcGrid">
        <thead>
            <tr>
                <th>Sun</th><th>Mon</th><th>Tue</th>
                <th>Wed</th><th>Thurs</th><th>Fri</th><th>Sat</th>
            </tr>
        </thead>
        <tbody id="fcBody"></tbody>
    </table>
</div>

@push('scripts')
<script src="{{ asset('js/faculty-calendar.js') }}"></script>
@endpush
@endsection
