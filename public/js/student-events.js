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

    // Build events map from DB payload when available.
    const calendarEvents = {};
    const dbEvents = Array.isArray(window.calendarEventsData) ? window.calendarEventsData : [];

    dbEvents.forEach(function (item) {
        if (!item || !item.date || !item.label) return;
        calendarEvents[item.date] = {
            type: item.type === 'holiday' ? 'holiday' : 'event',
            label: item.label
        };
    });

    // Static fallback when DB has no events yet.
    if (Object.keys(calendarEvents).length === 0) {
        calendarEvents["2026-03-09"] = { type: "holiday", label: "Araw ng Kagitingan" };
        calendarEvents["2026-03-17"] = { type: "event", label: "University Foundation Day" };
        calendarEvents["2026-03-20"] = { type: "event", label: "Intramurals Opening" };
        calendarEvents["2026-03-28"] = { type: "holiday", label: "Maundy Thursday" };
        calendarEvents["2026-04-01"] = { type: "holiday", label: "Eid al-Fitr" };
        calendarEvents["2026-04-09"] = { type: "holiday", label: "Araw ng Kagitingan" };
        calendarEvents["2026-04-14"] = { type: "event", label: "Midterm Exams Start" };
        calendarEvents["2026-04-25"] = { type: "event", label: "Career Fair 2026" };
        calendarEvents["2026-05-01"] = { type: "holiday", label: "Labor Day" };
        calendarEvents["2026-05-12"] = { type: "event", label: "Final Exams Start" };
        calendarEvents["2026-06-12"] = { type: "holiday", label: "Independence Day" };
    }

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
                        cell.dataset.eventType = ev.type;
                        cell.dataset.eventLabel = ev.label;
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

    /* ── Mobile tap-to-reveal popup ─────────────────────── */

    let activePopup = null;

    function closeEventPopup() {
        if (activePopup) {
            activePopup.remove();
            activePopup = null;
        }
    }

    function isMobileView() {
        return window.innerWidth <= 991;
    }

    calendarBody.addEventListener("click", function(e) {
        if (!isMobileView()) return;

        const cell = e.target.closest(".cal-td");
        if (!cell || !cell.dataset.eventLabel) return;

        // If tapping the same cell, just toggle off
        if (activePopup && activePopup._cell === cell) {
            closeEventPopup();
            return;
        }

        closeEventPopup();

        const type = cell.dataset.eventType;
        const label = cell.dataset.eventLabel;

        const popup = document.createElement("div");
        popup.className = "cal-event-popup";
        popup._cell = cell;
        popup.innerHTML =
            `<div class="cal-popup-type popup-${type}">` +
                `<span class="popup-dot"></span>` +
                `${type === 'holiday' ? 'Holiday' : 'University Event'}` +
            `</div>` +
            `<div class="cal-popup-label">${label}</div>`;

        document.body.appendChild(popup);

        // Position popup centered below the cell
        const rect = cell.getBoundingClientRect();
        const popupRect = popup.getBoundingClientRect();

        let left = rect.left + rect.width / 2 - popupRect.width / 2;
        let top = rect.bottom + 8;

        // Keep within viewport
        if (left < 8) left = 8;
        if (left + popupRect.width > window.innerWidth - 8) {
            left = window.innerWidth - popupRect.width - 8;
        }
        // If no room below, show above
        if (top + popupRect.height > window.innerHeight - 8) {
            top = rect.top - popupRect.height - 8;
            popup.style.transformOrigin = "bottom center";
        }

        popup.style.left = left + "px";
        popup.style.top = top + "px";

        activePopup = popup;
    });

    // Close popup on tap outside
    document.addEventListener("click", function(e) {
        if (!activePopup) return;
        if (activePopup.contains(e.target)) return;
        if (e.target.closest(".cal-td[data-event-label]")) return;
        closeEventPopup();
    });

    // Close popup on scroll or resize
    window.addEventListener("scroll", closeEventPopup, true);
    window.addEventListener("resize", closeEventPopup);
});
