/* ── Section Offering Page ── */

var SECTION_OFFERINGS = {
    'BSIT': {
        'First': [
            { code: 'CAP 102', desc: 'Capstone Project Research 2', lec: 2, lab: 1, tuitionUnits: 3, credUnits: 3, section: 'BSIT 4-A', room: 'BLDG 1-401', professor: 'Dela Cruz, Juan', slots: 45, days: ['Monday', 'Wednesday', 'Friday'], time: '7:00 AM-9:00 AM' },
            { code: 'IAS 102', desc: 'Information Assurance and Security 2', lec: 2, lab: 1, tuitionUnits: 3, credUnits: 3, section: 'BSIT 4-A', room: 'Online Class', professor: 'Dela Cruz, Juan', slots: 45, days: ['Tuesday', 'Thursday'], time: '7:00 AM-9:00 PM' },
            { code: 'IT ELEC 4', desc: 'Elective 4', lec: 2, lab: 1, tuitionUnits: 3, credUnits: 3, section: 'BSIT 4-A', room: 'Online Class', professor: 'Dela Cruz, Juan', slots: 45, days: ['Monday'], time: '5:00 PM-8:00 PM' },
            { code: 'SAM 101', desc: 'System Administration and Maintenance', lec: 2, lab: 1, tuitionUnits: 3, credUnits: 3, section: 'BSIT 4-A', room: 'Online Class', professor: 'Dela Cruz, Juan', slots: 45, days: ['Wednesday'], time: '5:00 PM-8:00 PM' },
            { code: 'SPI 101', desc: 'Social And Professional Issues', lec: 3, lab: 0, tuitionUnits: 3, credUnits: 3, section: 'BSIT 4-A', room: 'Online Class', professor: 'Dela Cruz, Juan', slots: 45, days: ['Thursday'], time: '5:00 PM-8:00 PM' }
        ],
        'Second': [
            { code: 'CC103', desc: 'Computer Programming 2', lec: 2, lab: 1, tuitionUnits: 3, credUnits: 3, section: 'BSIT 2-B', room: 'BLDG 2-301', professor: 'Prof. Reyes', slots: 40, days: ['Monday', 'Wednesday', 'Friday'], time: '9:00 AM-10:00 AM' },
            { code: 'IT101', desc: 'Discrete Mathematics', lec: 3, lab: 0, tuitionUnits: 3, credUnits: 3, section: 'BSIT 2-B', room: 'BLDG 2-305', professor: 'Dr. Tan', slots: 40, days: ['Tuesday', 'Thursday'], time: '8:00 AM-9:30 AM' },
            { code: 'GE4', desc: 'Mathematics in the Modern World', lec: 3, lab: 0, tuitionUnits: 3, credUnits: 3, section: 'BSIT 2-B', room: 'BLDG 2-306', professor: 'Prof. Dela Cruz', slots: 40, days: ['Monday', 'Wednesday', 'Friday'], time: '10:00 AM-11:00 AM' }
        ]
    },
    'BSCS': {
        'First': [
            { code: 'CS101', desc: 'Introduction to Computer Science', lec: 2, lab: 1, tuitionUnits: 3, credUnits: 3, section: 'BSCS 1-A', room: 'BLDG 3-201', professor: 'Dr. Ramos', slots: 40, days: ['Monday', 'Wednesday', 'Friday'], time: '8:00 AM-9:00 AM' },
            { code: 'CS102', desc: 'Programming Fundamentals', lec: 2, lab: 1, tuitionUnits: 3, credUnits: 3, section: 'BSCS 1-A', room: 'BLDG 3-203', professor: 'Prof. Navarro', slots: 40, days: ['Tuesday', 'Thursday'], time: '10:00 AM-11:30 AM' }
        ]
    }
};

var SO_DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

function renderSectionOffering() {
    var program = document.getElementById('soProgram').value;
    var term = document.getElementById('soTerm').value;
    var section = document.getElementById('soSection').value;

    var card = document.getElementById('soCard');
    var cardTitle = document.getElementById('soCardTitle');
    var tableWrap = document.getElementById('soTableWrap');
    var pageInfo = document.getElementById('soPageInfo');
    var tbody = document.getElementById('soBody');
    var weekly = document.getElementById('soWeekly');
    var weeklyGrid = document.getElementById('soWeeklyGrid');

    var subjects = (SECTION_OFFERINGS[program] && SECTION_OFFERINGS[program][term]) || [];

    if (subjects.length === 0) {
        card.style.display = 'none';
        weekly.style.display = 'none';
        return;
    }

    cardTitle.textContent = 'Section Offering: ' + section;

    var html = '';
    for (var i = 0; i < subjects.length; i++) {
        var s = subjects[i];
        html += '<tr>' +
            '<td>' + s.code + '</td>' +
            '<td>' + s.desc + '</td>' +
            '<td style="text-align:center;">' + s.lec + '</td>' +
            '<td style="text-align:center;">' + s.lab + '</td>' +
            '<td style="text-align:center;">' + s.tuitionUnits + '</td>' +
            '<td style="text-align:center;">' + s.credUnits + '</td>' +
            '<td>' + s.section + '</td>' +
            '<td>' + s.room + '</td>' +
            '<td>' + s.professor + '</td>' +
            '<td style="text-align:center;">' + s.slots + '</td>' +
            '<td>' + s.time + '</td>' +
        '</tr>';
    }

    tbody.innerHTML = html;
    card.style.display = 'block';
    document.getElementById('soPageText').textContent = 'Showing ' + subjects.length + ' subjects';

    var weeklyHtml = '';
    for (var d = 0; d < SO_DAYS.length; d++) {
        var day = SO_DAYS[d];
        weeklyHtml += '<div class="so-weekly-col">' +
            '<div class="so-weekly-day">' + day + '</div>' +
            '<div class="so-weekly-body">';

        var dayItems = subjects.filter(function (s) {
            return s.days.indexOf(day) !== -1;
        });

        if (dayItems.length === 0) {
            weeklyHtml += '<div class="so-weekly-empty">No class</div>';
        } else {
            for (var j = 0; j < dayItems.length; j++) {
                var item = dayItems[j];
                weeklyHtml +=
                    '<div class="so-weekly-card">' +
                        '<div class="so-weekly-code">' + item.code + '</div>' +
                        '<div class="so-weekly-section">' + item.section + '</div>' +
                        '<div class="so-weekly-time">' + item.time + '</div>' +
                        '<div class="so-weekly-room">' + item.room + '</div>' +
                    '</div>';
            }
        }

        weeklyHtml += '</div></div>';
    }

    weeklyGrid.innerHTML = weeklyHtml;
    weekly.style.display = 'block';
}

/* ── Auto-render on filter change ── */
['soSY', 'soTerm', 'soYearLevel', 'soSection', 'soProgram'].forEach(function(id) {
    document.getElementById(id).addEventListener('change', renderSectionOffering);
});

/* ── Init ── */
renderSectionOffering();
