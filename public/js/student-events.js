document.addEventListener("DOMContentLoaded", function() {
    const calendarMonthYear = document.getElementById("calendarMonthYear");
    const calendarBody = document.getElementById("calendarBody");
    const prevMonthBtn = document.getElementById("prevMonthBtn");
    const nextMonthBtn = document.getElementById("nextMonthBtn");
    const todayBtn = document.getElementById("todayBtn");

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

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        calendarBody.innerHTML = "";

        let dateCount = 1;
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

            if (hasCells) {
                calendarBody.appendChild(row);
            }
        }
    }

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

    renderCalendar(currentDate);
});
