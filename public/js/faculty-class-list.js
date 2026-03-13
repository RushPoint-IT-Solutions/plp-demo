document.addEventListener('DOMContentLoaded', function () {
    var subjectView = document.getElementById('classListSubjectView');
    var detailView = document.getElementById('classListDetailView');
    var detailTitle = document.getElementById('classListDetailTitle');
    var detailSection = document.getElementById('classListDetailSection');
    var detailBody = document.getElementById('classListDetailBody');
    var backBtn = document.getElementById('classListBackBtn');
    var rows = document.querySelectorAll('.class-list-row');

    function openDetail(row) {
        if (!row || typeof subjectStudents === 'undefined') return;

        var subjectId = row.getAttribute('data-subject-id');
        var subjectName = row.getAttribute('data-subject-name') || 'Class List';
        var section = row.getAttribute('data-subject-section') || '';
        var students = subjectStudents[subjectId] || [];
        var html = '';

        if (!students.length) {
            html = '<tr><td colspan="5" class="text-center text-muted py-3">No students enrolled.</td></tr>';
        } else {
            students.forEach(function (st, i) {
                html += '<tr>'
                    + '<td>' + (i + 1) + '</td>'
                    + '<td>' + (st.student_no || '') + '</td>'
                    + '<td>' + (st.name || '') + '</td>'
                    + '<td>' + (st.program_block || '') + '</td>'
                    + '<td>' + (st.status || 'Enrolled') + '</td>'
                    + '</tr>';
            });
        }

        if (detailTitle) detailTitle.textContent = subjectName;
        if (detailSection) detailSection.textContent = section;
        if (detailBody) detailBody.innerHTML = html;
        if (subjectView) subjectView.style.display = 'none';
        if (detailView) detailView.style.display = 'block';
    }

    rows.forEach(function (row) {
        row.addEventListener('click', function (event) {
            event.preventDefault();
            openDetail(row);
        });
    });

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            if (detailView) detailView.style.display = 'none';
            if (subjectView) subjectView.style.display = 'block';
        });
    }
});
