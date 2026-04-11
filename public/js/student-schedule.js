(function () {

    function pad2(value) {
        return value < 10 ? '0' + value : String(value);
    }

    function formatPrintDate(date) {
        var month = pad2(date.getMonth() + 1);
        var day = pad2(date.getDate());
        var year = date.getFullYear();
        return month + '/' + day + '/' + year;
    }

    function toDayNames(daysText) {
        var map = {
            sun: 'Sunday',
            sunday: 'Sunday',
            m: 'Monday',
            mon: 'Monday',
            monday: 'Monday',
            t: 'Tuesday',
            tue: 'Tuesday',
            tues: 'Tuesday',
            tuesday: 'Tuesday',
            w: 'Wednesday',
            wed: 'Wednesday',
            wednesday: 'Wednesday',
            th: 'Thursday',
            thu: 'Thursday',
            thur: 'Thursday',
            thurs: 'Thursday',
            thursday: 'Thursday',
            f: 'Friday',
            fri: 'Friday',
            friday: 'Friday',
            sat: 'Saturday',
            saturday: 'Saturday'
        };

        return String(daysText || '')
            .split(',')
            .map(function (token) { return token.replace(/\./g, '').trim().toLowerCase(); })
            .map(function (token) { return map[token] || null; })
            .filter(function (item) { return !!item; });
    }

    function splitTimeRange(timeRange) {
        var parts = String(timeRange || '').split('-');
        if (parts.length < 2) {
            return { start: String(timeRange || ''), end: '' };
        }
        return {
            start: parts[0].trim(),
            end: parts[1].trim()
        };
    }

    function buildWeekly(items) {
        var dayList = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        var weekly = {};

        dayList.forEach(function (day) {
            weekly[day] = [];
        });

        items.forEach(function (item) {
            var times = splitTimeRange(item.time_range);
            toDayNames(item.days).forEach(function (dayName) {
                weekly[dayName].push({
                    code: item.code,
                    name: item.name,
                    time_start: times.start,
                    time_end: times.end,
                    room: String(item.room || '').toUpperCase()
                });
            });
        });

        return { dayList: dayList, weekly: weekly };
    }

    var sampleSchedules = {
        '2025-2026|Second': [
            { code: 'GEC 19', name: 'Life and Works of Rizal', units: 2.0, days: 'Sat', time_range: '04:00PM-07:00PM', room: 'RM 1', faculty: 'Abelo, M.' },
            { code: 'SAMI25', name: 'System Administration and Maintenance', units: 3.0, days: 'W, Th', time_range: '01:00PM-02:00PM', room: 'RM 5', faculty: 'Abelo, M.' },
            { code: 'CPI26', name: 'Capstone Project', units: 3.0, days: 'M, F', time_range: '04:00PM-07:00PM', room: 'RM 3', faculty: 'Reyes, A.' }
        ],
        '2025-2026|First': [
            { code: 'IT 123', name: 'Data Structures and Algorithms', units: 3.0, days: 'M, W', time_range: '10:00AM-11:30AM', room: 'LAB 2', faculty: 'Santos, R.' },
            { code: 'IT 124', name: 'Human Computer Interaction', units: 3.0, days: 'T, Th', time_range: '01:00PM-02:30PM', room: 'RM 7', faculty: 'Cruz, L.' },
            { code: 'NSTP 2', name: 'Civic Welfare Training Service 2', units: 3.0, days: 'Sat', time_range: '08:00AM-11:00AM', room: 'GYM', faculty: 'Garcia, P.' }
        ],
        '2024-2025|Second': [
            { code: 'IT 118', name: 'Database Management Systems', units: 3.0, days: 'M, Th', time_range: '09:00AM-10:30AM', room: 'LAB 1', faculty: 'Dela Cruz, J.' },
            { code: 'GEC 7', name: 'Ethics', units: 3.0, days: 'T, F', time_range: '02:00PM-03:30PM', room: 'RM 9', faculty: 'Ramos, E.' },
            { code: 'PE 4', name: 'Team Sports', units: 2.0, days: 'W', time_range: '03:00PM-05:00PM', room: 'COURT', faculty: 'Villanueva, K.' }
        ],
        '2024-2025|First': [
            { code: 'IT 115', name: 'Object-Oriented Programming', units: 3.0, days: 'M, W, F', time_range: '07:30AM-09:00AM', room: 'LAB 3', faculty: 'Lopez, N.' },
            { code: 'GEC 5', name: 'Purposive Communication', units: 3.0, days: 'T, Th', time_range: '10:30AM-12:00PM', room: 'RM 4', faculty: 'Torres, M.' },
            { code: 'MATH 203', name: 'Discrete Mathematics', units: 3.0, days: 'Sat', time_range: '10:00AM-01:00PM', room: 'RM 2', faculty: 'Bautista, F.' }
        ]
    };

    function renderSubjectsTable(items) {
        var tbody = document.getElementById('schedSubjectTableBody');
        if (!tbody) {
            return;
        }

        if (!items.length) {
            tbody.innerHTML = '<tr><td class="sched-td" colspan="7">No schedule found for selected filters.</td></tr>';
            return;
        }

        var html = items.map(function (item) {
            return '' +
                '<tr>' +
                    '<td class="sched-td" data-label="Code">' + item.code + '</td>' +
                    '<td class="sched-td" data-label="Subject">' + item.name + '</td>' +
                    '<td class="sched-td" data-label="Units">' + Number(item.units).toFixed(1) + '</td>' +
                    '<td class="sched-td" data-label="Days">' + item.days + '</td>' +
                    '<td class="sched-td" data-label="Time">' + item.time_range + '</td>' +
                    '<td class="sched-td" data-label="Room">' + item.room + '</td>' +
                    '<td class="sched-td" data-label="Faculty">' + item.faculty + '</td>' +
                '</tr>';
        }).join('');

        tbody.innerHTML = html;
    }

    function renderWeeklySchedule(items) {
        var weeklyRoot = document.getElementById('schedWeeklyGrid');
        if (!weeklyRoot) {
            return;
        }

        var weeklyData = buildWeekly(items);
        var dayList = weeklyData.dayList;
        var weekly = weeklyData.weekly;

        var html = dayList.map(function (day) {
            var cards = weekly[day] || [];
            var bodyHtml = cards.length ? cards.map(function (subject) {
                return '' +
                    '<div class="so-weekly-card">' +
                        '<p class="so-weekly-code">' + subject.code + '</p>' +
                        '<p class="so-weekly-section">' + subject.name + '</p>' +
                        '<p class="so-weekly-time">' + subject.time_start + '-' + subject.time_end + '</p>' +
                        '<p class="so-weekly-room">' + subject.room + '</p>' +
                    '</div>';
            }).join('') : '<div class="so-weekly-empty">No class</div>';

            return '' +
                '<div class="so-weekly-col">' +
                    '<div class="so-weekly-day">' + day + '</div>' +
                    '<div class="so-weekly-body">' + bodyHtml + '</div>' +
                '</div>';
        }).join('');

        weeklyRoot.innerHTML = html;
    }

    function updatePrintMeta(schoolYear, semester) {
        var text = 'School Year: ' + schoolYear + ' | Semester: ' + semester + ' Semester | Generated: ' + formatPrintDate(new Date());
        var mainMeta = document.getElementById('schedPrintMeta');
        var topMeta = document.getElementById('schedPrintMetaTop');

        if (mainMeta) {
            mainMeta.textContent = text;
        }

        if (topMeta) {
            topMeta.textContent = text;
        }
    }

    function renderCurrentSelection() {
        var schoolYearEl = document.getElementById('schedSchoolYear');
        var semesterEl = document.getElementById('schedSemester');
        if (!schoolYearEl || !semesterEl) {
            return;
        }

        var key = schoolYearEl.value + '|' + semesterEl.value;
        var items = sampleSchedules[key] || [];

        renderSubjectsTable(items);
        renderWeeklySchedule(items);
        updatePrintMeta(schoolYearEl.value, semesterEl.value);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var schoolYearEl = document.getElementById('schedSchoolYear');
        var semesterEl = document.getElementById('schedSemester');
        var downloadBtn = document.getElementById('schedDownloadBtn');

        if (schoolYearEl) {
            schoolYearEl.addEventListener('change', renderCurrentSelection);
        }

        if (semesterEl) {
            semesterEl.addEventListener('change', renderCurrentSelection);
        }

        if (downloadBtn) {
            downloadBtn.addEventListener('click', function () {
                window.print();
            });
        }

        renderCurrentSelection();
    });
})();
