document.addEventListener('DOMContentLoaded', function () {
    var checks      = document.querySelectorAll('.class-list-check');
    var viewBtnWrap = document.getElementById('viewListBtn');
    var openBtn     = document.getElementById('openClassListModal');
    var modalBody   = document.getElementById('classListModalBody');
    var modalLabel  = document.getElementById('classListModalLabel');
    var selectedId  = null;
    var selectedName = null;

    checks.forEach(function (cb) {
        cb.addEventListener('change', function () {
            // Radio-like behaviour: uncheck others
            checks.forEach(function (other) {
                if (other !== cb) other.checked = false;
            });

            if (cb.checked) {
                selectedId   = cb.dataset.subjectId;
                selectedName = cb.dataset.subjectName;
                viewBtnWrap.classList.add('visible');
            } else {
                selectedId   = null;
                selectedName = null;
                viewBtnWrap.classList.remove('visible');
            }
        });
    });

    if (openBtn) {
        openBtn.addEventListener('click', function () {
            if (!selectedId || typeof subjectStudents === 'undefined') return;

            modalLabel.textContent = 'Class List – ' + selectedName;
            var students = subjectStudents[selectedId] || [];
            var rows = '';

            if (students.length === 0) {
                rows = '<tr><td colspan="5" class="text-center text-muted py-3">No students enrolled.</td></tr>';
            } else {
                students.forEach(function (st, i) {
                    rows += '<tr>'
                        + '<td>' + (i + 1) + '</td>'
                        + '<td>' + (st.student_no || '') + '</td>'
                        + '<td>' + (st.name || '') + '</td>'
                        + '<td>' + (st.program || '') + '</td>'
                        + '<td>' + (st.year_level || '') + '</td>'
                        + '</tr>';
                });
            }

            modalBody.innerHTML = rows;
            var modal = new bootstrap.Modal(document.getElementById('classListModal'));
            modal.show();
        });
    }
});
