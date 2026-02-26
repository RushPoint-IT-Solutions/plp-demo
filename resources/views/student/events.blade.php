@extends('layouts.student')

@section('title', 'PLP - Events')
@section('page-title', 'UNIVERSITY EVENTS CALENDAR')

@section('content')
<div class="student-page-container">
    
    {{-- Calendar Header Controls --}}
    <div class="calendar-controls-wrapper">
        <div class="calendar-nav-buttons">
            <button class="cal-btn" id="prevMonthBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="cal-btn" id="todayBtn">Today</button>
            <button class="cal-btn" id="nextMonthBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>

        <div class="calendar-title">
            <h2 id="calendarMonthYear">Month 2026</h2>
        </div>

        <div class="calendar-legend">
            <strong>EVENT TYPE</strong>
            <div class="legend-items">
                <span class="legend-item"><span class="dot dot-holiday"></span> Holiday</span>
                <span class="legend-item"><span class="dot dot-event"></span> University Events</span>
            </div>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="calendar-grid-wrapper">
        <table class="calendar-table">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thurs</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody id="calendarBody">
                <!-- JS will inject days here -->
            </tbody>
        </table>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const calendarMonthYear = document.getElementById("calendarMonthYear");
        const calendarBody = document.getElementById("calendarBody");
        const prevMonthBtn = document.getElementById("prevMonthBtn");
        const nextMonthBtn = document.getElementById("nextMonthBtn");
        const todayBtn = document.getElementById("todayBtn");

        // Use current date from JS, or start at a specific one.
        // The mockups show Jan/Feb/March 2026. Let's use current actual date.
        let currentDate = new Date(); 

        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        function renderCalendar(date) {
            const year = date.getFullYear();
            const month = date.getMonth();

            calendarMonthYear.textContent = `${monthNames[month]} ${year}`;

            // First day of the month
            const firstDay = new Date(year, month, 1).getDay();
            // Number of days in the month
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            // Clear previous cells
            calendarBody.innerHTML = "";

            let dateCount = 1;
            // 6 rows max for a month
            for (let i = 0; i < 6; i++) {
                let row = document.createElement("tr");
                let hasCells = false;

                for (let j = 0; j < 7; j++) {
                    let cell = document.createElement("td");
                    
                    if (i === 0 && j < firstDay) {
                        // Empty cell before month starts
                        cell.classList.add("empty-cell");
                    } else if (dateCount > daysInMonth) {
                        // Empty cell after month ends
                        cell.classList.add("empty-cell");
                    } else {
                        // Actual day cell
                        cell.innerHTML = `<div class="day-number">${dateCount}</div>`;
                        // Highlight today if it perfectly matches
                        const today = new Date();
                        if (dateCount === today.getDate() && year === today.getFullYear() && month === today.getMonth()) {
                            cell.classList.add("today-cell");
                        }
                        dateCount++;
                        hasCells = true;
                    }
                    row.appendChild(cell);
                }
                
                // Don't append completely empty rows at the end
                if (hasCells) {
                    calendarBody.appendChild(row);
                }
            }
        }

        // Event listeners
        prevMonthBtn.addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        });

        nextMonthBtn.addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        });

        todayBtn.addEventListener("click", () => {
            currentDate = new Date();
            renderCalendar(currentDate);
        });

        // Initial render
        renderCalendar(currentDate);
    });
</script>
@endsection
