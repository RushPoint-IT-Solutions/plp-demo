/* ── Section Offering Page ── */

var SECTION_OFFERINGS = {
    'BSIT': {
        'First': [
            { code: 'CC101', title: 'Introduction to Computing', lec: 2, lab: 1, schedule: 'MWF 8:00-9:00 AM', instructor: 'Dr. Santos' },
            { code: 'CC102', title: 'Computer Programming 1', lec: 2, lab: 1, schedule: 'TTh 10:00-11:30 AM', instructor: 'Prof. Reyes' },
            { code: 'GE1', title: 'Understanding the Self', lec: 3, lab: 0, schedule: 'MWF 1:00-2:00 PM', instructor: 'Prof. Cruz' },
            { code: 'GE2', title: 'Readings in Philippine History', lec: 3, lab: 0, schedule: 'TTh 1:00-2:30 PM', instructor: 'Prof. Garcia' },
            { code: 'GE3', title: 'The Contemporary World', lec: 3, lab: 0, schedule: 'MWF 3:00-4:00 PM', instructor: 'Prof. Mendoza' },
            { code: 'PE1', title: 'Physical Education 1', lec: 2, lab: 0, schedule: 'Sat 8:00-10:00 AM', instructor: 'Coach Lim' }
        ],
        'Second': [
            { code: 'CC103', title: 'Computer Programming 2', lec: 2, lab: 1, schedule: 'MWF 9:00-10:00 AM', instructor: 'Prof. Reyes' },
            { code: 'IT101', title: 'Discrete Mathematics', lec: 3, lab: 0, schedule: 'TTh 8:00-9:30 AM', instructor: 'Dr. Tan' },
            { code: 'GE4', title: 'Mathematics in the Modern World', lec: 3, lab: 0, schedule: 'MWF 10:00-11:00 AM', instructor: 'Prof. Dela Cruz' }
        ]
    },
    'BSCS': {
        'First': [
            { code: 'CS101', title: 'Introduction to Computer Science', lec: 2, lab: 1, schedule: 'MWF 8:00-9:00 AM', instructor: 'Dr. Ramos' },
            { code: 'CS102', title: 'Programming Fundamentals', lec: 2, lab: 1, schedule: 'TTh 10:00-11:30 AM', instructor: 'Prof. Navarro' }
        ]
    }
};

function renderSectionOffering() {
    var program = document.getElementById('soProgram').value;
    var term = document.getElementById('soTerm').value;

    var tableWrap = document.getElementById('soTableWrap');
    var pageInfo = document.getElementById('soPageInfo');
    var tbody = document.getElementById('soBody');

    var subjects = (SECTION_OFFERINGS[program] && SECTION_OFFERINGS[program][term]) || [];

    if (subjects.length === 0) {
        tableWrap.style.display = 'none';
        pageInfo.style.display = 'none';
        return;
    }

    var html = '';
    for (var i = 0; i < subjects.length; i++) {
        var s = subjects[i];
        html += '<tr>' +
            '<td>' + s.code + '</td>' +
            '<td>' + s.title + '</td>' +
            '<td style="text-align:center;">' + s.lec + '</td>' +
            '<td style="text-align:center;">' + s.lab + '</td>' +
            '<td style="text-align:center;">' + (s.lec + s.lab) + '</td>' +
            '<td>' + s.schedule + '</td>' +
            '<td>' + s.instructor + '</td>' +
        '</tr>';
    }

    tbody.innerHTML = html;
    tableWrap.style.display = 'block';
    pageInfo.style.display = 'flex';
    document.getElementById('soPageText').textContent = 'Showing ' + subjects.length + ' subjects';
}

/* ── Auto-render on filter change ── */
['soSY', 'soTerm', 'soYearLevel', 'soSection', 'soProgram'].forEach(function(id) {
    document.getElementById(id).addEventListener('change', renderSectionOffering);
});

/* ── Init ── */
renderSectionOffering();
