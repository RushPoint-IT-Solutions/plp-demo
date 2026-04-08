document.addEventListener('DOMContentLoaded', function () {
    var subjectView = document.getElementById('gradingSubjectView');
    var detailView = document.getElementById('gradingDetailForm');
    var detailTitle = document.getElementById('gradingDetailTitle');
    var detailSection = document.getElementById('gradingDetailSection');
    var detailBody = document.getElementById('gradingDetailBody');
    var inputAction = document.getElementById('gradingInputAction');
    var inputBtn = document.getElementById('gradingInputBtn');
    var viewAction = document.getElementById('gradingViewAction');
    var viewBtn = document.getElementById('gradingViewBtn');
    var backBtn = document.getElementById('gradingBackBtn');
    var subjectIdInput = document.getElementById('gradingSubjectIdInput');
    var checks = document.querySelectorAll('.grading-subject-check');
    var subjectRows = document.querySelectorAll('.grading-subject-row');

    var selectedSubjectId = null;
    var activeSubject = null;
    var isEditing = false;

    function toNumber(value) {
        var num = parseFloat(value);
        return Number.isNaN(num) ? null : num;
    }

    function computeRow(prelimEl, midtermEl, finalEl, averageEl, remarksEl) {
        var prelim = toNumber(prelimEl.value);
        var midterm = toNumber(midtermEl.value);
        var finalGrade = toNumber(finalEl.value);

        if (prelim === null || midterm === null || finalGrade === null) {
            averageEl.value = '';
            remarksEl.value = '';
            return;
        }

        var avg = (prelim + midterm + finalGrade) / 3;
        averageEl.value = avg.toFixed(2);
        remarksEl.value = avg <= 3.0 ? 'Passed' : 'Failed';
    }

    function buildReadOnlyRows(students) {
        var html = '';
        students.forEach(function (st, index) {
            html += '<tr>'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + (st.name || '') + '</td>'
                + '<td>' + (st.prelim || '') + '</td>'
                + '<td>' + (st.midterm || '') + '</td>'
                + '<td>' + (st.final || '') + '</td>'
                + '<td>' + (st.final_average || '') + '</td>'
                + '<td>' + (st.remarks || '') + '</td>'
                + '</tr>';
        });
        return html;
    }

    function buildEditableRows(students) {
        var html = '';
        students.forEach(function (st, index) {
            html += '<tr>'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + (st.name || '') + '</td>'
                + '<td><input type="number" min="1" max="5" step="0.01" class="grading-input" name="grades[' + st.id + '][prelim]" value="' + (st.prelim || '') + '"></td>'
                + '<td><input type="number" min="1" max="5" step="0.01" class="grading-input" name="grades[' + st.id + '][midterm]" value="' + (st.midterm || '') + '"></td>'
                + '<td><input type="number" min="1" max="5" step="0.01" class="grading-input" name="grades[' + st.id + '][final]" value="' + (st.final || '') + '"></td>'
                + '<td><input type="text" class="grading-input grading-readonly" name="grades[' + st.id + '][final_average]" value="' + (st.final_average || '') + '" readonly></td>'
                + '<td><input type="text" class="grading-input grading-readonly" name="grades[' + st.id + '][remarks]" value="' + (st.remarks || '') + '" readonly></td>'
                + '</tr>';
        });
        return html;
    }

    function wireAutoCompute() {
        var rows = detailBody.querySelectorAll('tr');
        rows.forEach(function (row) {
            var prelimEl = row.querySelector('input[name*="[prelim]"]');
            var midtermEl = row.querySelector('input[name*="[midterm]"]');
            var finalEl = row.querySelector('input[name*="[final]"]:not([name*="final_average"])');
            var averageEl = row.querySelector('input[name*="[final_average]"]');
            var remarksEl = row.querySelector('input[name*="[remarks]"]');

            if (!prelimEl || !midtermEl || !finalEl || !averageEl || !remarksEl) {
                return;
            }

            var recalc = function () {
                computeRow(prelimEl, midtermEl, finalEl, averageEl, remarksEl);
            };

            prelimEl.addEventListener('input', recalc);
            midtermEl.addEventListener('input', recalc);
            finalEl.addEventListener('input', recalc);
            recalc();
        });
    }

    function renderDetail(subject, editing) {
        var students = subject.students || [];
        if (!students.length) {
            detailBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No students enrolled.</td></tr>';
        } else {
            detailBody.innerHTML = editing ? buildEditableRows(students) : buildReadOnlyRows(students);
        }

        detailTitle.textContent = subject.name || '';
        detailSection.textContent = subject.section || '';
        subjectIdInput.value = subject.id || '';

        if ((subject.status || '').toLowerCase() === 'submitted' && !editing) {
            inputAction.style.display = 'none';
        } else {
            inputAction.style.display = 'flex';
        }

        if (editing) {
            inputBtn.textContent = 'Update Grades';
            wireAutoCompute();
        } else {
            inputBtn.textContent = 'Input Grades';
        }
    }

    function openGradingDetail(subjectId) {
        if (typeof gradingSubjects === 'undefined') return;

        var subject = (gradingSubjects || []).find(function (item) {
            return String(item.id) === String(subjectId);
        });

        if (!subject) return;
        activeSubject = subject;
        isEditing = false;

        renderDetail(subject, false);

        subjectView.style.display = 'none';
        detailView.style.display = 'block';
    }

    checks.forEach(function (check) {
        check.checked = false;
        check.addEventListener('change', function () {
            checks.forEach(function (other) {
                if (other !== check) {
                    other.checked = false;
                }
            });

            selectedSubjectId = check.checked ? check.getAttribute('data-subject-id') : null;
            viewAction.style.display = selectedSubjectId ? 'flex' : 'none';
        });

        check.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });

    subjectRows.forEach(function (row) {
        row.style.cursor = 'pointer';

        row.addEventListener('click', function () {
            var rowSubjectId = row.getAttribute('data-subject-id');
            if (!rowSubjectId) {
                return;
            }

            selectedSubjectId = rowSubjectId;
            var rowCheck = row.querySelector('.grading-subject-check');
            checks.forEach(function (other) {
                other.checked = !!(rowCheck && other === rowCheck);
            });
            viewAction.style.display = 'flex';

            openGradingDetail(rowSubjectId);
        });
    });

    if (viewBtn) {
        viewBtn.addEventListener('click', function () {
            if (!selectedSubjectId) return;
            openGradingDetail(selectedSubjectId);
        });
    }

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            detailView.style.display = 'none';
            subjectView.style.display = 'block';
        });
    }

    if (inputBtn) {
        inputBtn.addEventListener('click', function () {
            if (!activeSubject) {
                return;
            }

            if (!isEditing) {
                isEditing = true;
                renderDetail(activeSubject, true);
                return;
            }

            detailView.submit();
        });
    }
});
