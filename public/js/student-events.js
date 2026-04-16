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

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            };

            return map[ch] || ch;
        });
    }

    dbEvents.forEach(function (item) {
        if (!item || !item.date || !item.label) {
            return;
        }

        const key = String(item.date);
        if (!Array.isArray(calendarEvents[key])) {
            calendarEvents[key] = [];
        }

        calendarEvents[key].push({
            type: item.type === 'holiday' ? 'holiday' : 'event',
            label: String(item.label)
        });
    });

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
                    const eventsForDate = Array.isArray(calendarEvents[key]) ? calendarEvents[key] : [];
                    if (eventsForDate.length > 0) {
                        const hasHoliday = eventsForDate.some(function(eventItem) {
                            return eventItem.type === 'holiday';
                        });
                        const hasRegularEvent = eventsForDate.some(function(eventItem) {
                            return eventItem.type === 'event';
                        });

                        if (hasHoliday && hasRegularEvent) {
                            cell.classList.add('mixed-event-cell');
                        } else if (hasHoliday) {
                            cell.classList.add('holiday-cell');
                        } else {
                            cell.classList.add('event-cell');
                        }

                        html += `<span class="cal-event-label">${escapeHtml(eventsForDate[0].label)}</span>`;
                        if (eventsForDate.length > 1) {
                            html += `<span class="cal-event-more">+${eventsForDate.length - 1} more</span>`;
                        }

                        cell.dataset.eventItems = JSON.stringify(eventsForDate);
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
        if (!cell || !cell.dataset.eventItems) return;

        // If tapping the same cell, just toggle off
        if (activePopup && activePopup._cell === cell) {
            closeEventPopup();
            return;
        }

        closeEventPopup();

        let items = [];
        try {
            items = JSON.parse(cell.dataset.eventItems || '[]');
        } catch (error) {
            items = [];
        }

        if (!Array.isArray(items) || items.length === 0) {
            return;
        }

        const eventRows = items.map(function(item) {
            const eventType = item && item.type === 'holiday' ? 'holiday' : 'event';
            const eventTypeLabel = eventType === 'holiday' ? 'Holiday' : 'University Event';

            return (
                `<div class="cal-popup-item">` +
                    `<div class="cal-popup-type popup-${eventType}">` +
                        `<span class="popup-dot"></span>` +
                        `${eventTypeLabel}` +
                    `</div>` +
                    `<div class="cal-popup-label">${escapeHtml(item && item.label ? item.label : '')}</div>` +
                `</div>`
            );
        }).join('');

        const popup = document.createElement("div");
        popup.className = "cal-event-popup";
        popup._cell = cell;
        popup.innerHTML = `<div class="cal-popup-list">${eventRows}</div>`;

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
        if (e.target.closest(".cal-td[data-event-items]")) return;
        closeEventPopup();
    });

    // Close popup on scroll or resize
    window.addEventListener("scroll", closeEventPopup, true);
    window.addEventListener("resize", closeEventPopup);
});
