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

        // Sample events & holidays — keyed by "YYYY-MM-DD"
        const calendarEvents = {
            "2026-03-09": { type: "holiday",  label: "Araw ng Kagitingan" },
            "2026-03-17": { type: "event",    label: "University Foundation Day" },
            "2026-03-20": { type: "event",    label: "Intramurals Opening" },
            "2026-03-28": { type: "holiday",  label: "Maundy Thursday" },
            "2026-04-01": { type: "holiday",  label: "Eid al-Fitr" },
            "2026-04-09": { type: "holiday",  label: "Araw ng Kagitingan" },
            "2026-04-14": { type: "event",    label: "Midterm Exams Start" },
            "2026-04-25": { type: "event",    label: "Career Fair 2026" },
            "2026-05-01": { type: "holiday",  label: "Labor Day" },
            "2026-05-12": { type: "event",    label: "Final Exams Start" },
            "2026-06-12": { type: "holiday",  label: "Independence Day" },
        };

        function dateKey(y, m, d) {
            return `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        }

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
                    cell.classList.add("cal-td");

                    if (i === 0 && j < firstDay) {
                        cell.classList.add("empty-cell");
                    } else if (dateCount > daysInMonth) {
                        cell.classList.add("empty-cell");
                    } else {
                        let html = `<div class="day-number">${dateCount}</div>`;

                        const key = dateKey(year, month, dateCount);
                        const ev = calendarEvents[key];
                        if (ev) {
                            cell.classList.add(ev.type === 'holiday' ? 'holiday-cell' : 'event-cell');
                            html += `<span class="cal-event-label">${ev.label}</span>`;
                        }

                        cell.innerHTML = html;
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
