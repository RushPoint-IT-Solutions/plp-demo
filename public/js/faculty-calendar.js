document.addEventListener('DOMContentLoaded', function () {
    var today   = new Date();
    var current = new Date(today.getFullYear(), today.getMonth(), 1);

    var MONTHS = [
        'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];

    function renderCalendar(date) {
        var year  = date.getFullYear();
        var month = date.getMonth();

        document.getElementById('fcMonthTitle').textContent = MONTHS[month] + ' ' + year;

        var firstDay = new Date(year, month, 1).getDay(); // 0=Sun
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var daysInPrev  = new Date(year, month, 0).getDate();

        var tbody = document.getElementById('fcBody');
        tbody.innerHTML = '';

        var day = 1;
        var nextDay = 1;
        var cells = firstDay + daysInMonth;
        var rows  = Math.ceil(cells / 7);

        for (var r = 0; r < rows; r++) {
            var tr = document.createElement('tr');
            for (var c = 0; c < 7; c++) {
                var td = document.createElement('td');
                td.classList.add('cal-td');
                var cellIndex = r * 7 + c;

                if (cellIndex < firstDay) {
                    // Previous month days
                    td.innerHTML = '<div class="day-number">' + (daysInPrev - firstDay + cellIndex + 1) + '</div>';
                    td.classList.add('fc-other-month');
                } else if (day > daysInMonth) {
                    // Next month days
                    td.innerHTML = '<div class="day-number">' + (nextDay++) + '</div>';
                    td.classList.add('fc-other-month');
                } else {
                    td.innerHTML = '<div class="day-number">' + day + '</div>';
                    // Highlight today
                    if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                        td.classList.add('fc-today');
                    }
                    day++;
                }
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        }
    }

    renderCalendar(current);

    document.getElementById('fcPrev').addEventListener('click', function () {
        current = new Date(current.getFullYear(), current.getMonth() - 1, 1);
        renderCalendar(current);
    });

    document.getElementById('fcNext').addEventListener('click', function () {
        current = new Date(current.getFullYear(), current.getMonth() + 1, 1);
        renderCalendar(current);
    });

    document.getElementById('fcToday').addEventListener('click', function () {
        current = new Date(today.getFullYear(), today.getMonth(), 1);
        renderCalendar(current);
    });
});
