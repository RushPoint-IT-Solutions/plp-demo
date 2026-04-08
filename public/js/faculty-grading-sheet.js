document.addEventListener('DOMContentLoaded', function () {
    var dataNode = document.getElementById('gradingSheetData');
    var subjectView = document.getElementById('gradingSubjectView');
    var detailView = document.getElementById('gradingDetailForm');
    var subjectBody = document.getElementById('gradingSubjectBody');
    var detailBody = document.getElementById('gradingDetailBody');
    var detailTitle = document.getElementById('gradingDetailTitle');
    var detailSection = document.getElementById('gradingDetailSection');
    var detailSearch = document.getElementById('gradingDetailSearch');
    var detailPager = document.getElementById('gradingDetailPager');
    var detailPagerList = document.getElementById('gradingDetailPagerList');
    var inputAction = document.getElementById('gradingInputAction');
    var inputBtn = document.getElementById('gradingInputBtn');
    var backBtn = document.getElementById('gradingBackBtn');
    var subjectIdInput = document.getElementById('gradingSubjectIdInput');
    var schoolYearSelect = document.getElementById('fgsSchoolYear');
    var semesterSelect = document.getElementById('fgsSemester');
    var statusSelect = document.getElementById('fgsStatus');
    var sectionSelect = document.getElementById('fgsSection');
    var subjectSelect = document.getElementById('fgsSubject');
    var searchBtn = document.getElementById('fgsSearchBtn');
    var yearLabel = document.getElementById('fgsYearLabel');
    var detailToolsRow = document.getElementById('fgsDetailToolsRow');
    var filterBar = document.getElementById('fgsFilterBar');

    if (!dataNode || !subjectView || !detailView || !subjectBody || !detailBody) {
        return;
    }

    var allSubjects = [];
    try {
        allSubjects = JSON.parse(dataNode.getAttribute('data-subjects') || '[]');
    } catch (error) {
        allSubjects = [];
    }

    var filteredSubjects = allSubjects.slice();
    var activeSubject = null;
    var isEditing = false;
    var detailPage = 1;
    var detailPageSize = 10;
    var detailButtonWindow = 5;

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

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

    function statusText(value) {
        return String(value || '').toLowerCase() === 'submitted' ? 'Submitted' : 'Open For Encoding';
    }

    function statusBadge(value) {
        if (String(value || '').toLowerCase() === 'submitted') {
            return '<span class="grading-status-submitted">Submitted</span>';
        }
        return '<span class="grading-status-open">Open For Encoding</span>';
    }

    function populateSelect(select, values) {
        if (!select) return;
        var current = select.value;
        var options = '<option value="">All</option>';
        values.forEach(function (value) {
            options += '<option value="' + escapeHtml(value) + '">' + escapeHtml(value) + '</option>';
        });
        select.innerHTML = options;
        if (current && values.indexOf(current) !== -1) {
            select.value = current;
        }
    }

    function refreshDependentFilters() {
        var year = (schoolYearSelect && schoolYearSelect.value) || '';
        var semester = (semesterSelect && semesterSelect.value) || '';
        var status = (statusSelect && statusSelect.value) || '';

        var base = allSubjects.filter(function (subject) {
            var itemStatus = statusText(subject.status);
            return (!year || String(subject.school_year || '') === year)
                && (!semester || String(subject.semester || '') === semester)
                && (!status || itemStatus === status);
        });

        var sections = [];
        var subjects = [];
        base.forEach(function (item) {
            var section = String(item.section || '').trim();
            var title = String(item.name || '').trim();
            if (section && sections.indexOf(section) === -1) sections.push(section);
            if (title && subjects.indexOf(title) === -1) subjects.push(title);
        });

        populateSelect(sectionSelect, sections);
        populateSelect(subjectSelect, subjects);
    }

    function setFilterMode(isDetailMode) {
        if (filterBar) {
            if (isDetailMode) {
                filterBar.classList.add('faculty-gs-hidden');
            } else {
                filterBar.classList.remove('faculty-gs-hidden');
            }
        }

        if (detailToolsRow) {
            if (isDetailMode) {
                detailToolsRow.classList.remove('faculty-gs-hidden');
            } else {
                detailToolsRow.classList.add('faculty-gs-hidden');
            }
        }
    }

    function applySubjectFilters() {
        var year = (schoolYearSelect && schoolYearSelect.value) || '';
        var semester = (semesterSelect && semesterSelect.value) || '';
        var status = (statusSelect && statusSelect.value) || '';
        var section = (sectionSelect && sectionSelect.value) || '';
        var subjectName = (subjectSelect && subjectSelect.value) || '';

        filteredSubjects = allSubjects.filter(function (item) {
            var itemStatus = statusText(item.status);
            return (!year || String(item.school_year || '') === year)
                && (!semester || String(item.semester || '') === semester)
                && (!status || itemStatus === status)
                && (!section || String(item.section || '') === section)
                && (!subjectName || String(item.name || '') === subjectName);
        });
    }

    function renderSubjectTable() {
        if (!subjectBody) return;

        if (!filteredSubjects.length) {
            subjectBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No subjects found.</td></tr>';
            return;
        }

        var rows = '';
        filteredSubjects.forEach(function (subject, index) {
            rows += '<tr class="grading-subject-row" data-subject-id="' + escapeHtml(subject.id) + '">'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + escapeHtml(subject.section || '-') + '</td>'
                + '<td class="td-code">' + escapeHtml(subject.code || '-') + '</td>'
                + '<td>' + escapeHtml(subject.name || '-') + '</td>'
                + '<td>' + escapeHtml(subject.days || '-') + '</td>'
                + '<td>' + statusBadge(subject.status) + '</td>'
                + '</tr>';
        });

        subjectBody.innerHTML = rows;
    }

    function getFilteredStudents(students) {
        var query = (detailSearch && detailSearch.value ? detailSearch.value : '').toLowerCase().trim();
        if (!query) return students;

        return students.filter(function (student) {
            var studentNo = String(student.student_no || '').toLowerCase();
            var name = String(student.name || '').toLowerCase();
            return studentNo.indexOf(query) !== -1 || name.indexOf(query) !== -1;
        });
    }

    function buildPagerButtons(totalPages) {
        var start = Math.max(1, detailPage - 2);
        var end = Math.min(totalPages, detailPage + 2);

        if (detailPage <= 3) {
            end = Math.min(totalPages, detailButtonWindow);
        } else if (detailPage >= totalPages - 2) {
            start = Math.max(1, totalPages - (detailButtonWindow - 1));
        }

        var html = '';
        for (var p = start; p <= end; p += 1) {
            html += '<button type="button" class="rtp-page-num ' + (p === detailPage ? 'active' : '') + '" ' + (p === detailPage ? 'aria-current="page"' : '') + ' data-detail-page="' + p + '">' + p + '</button>';
        }
        return html;
    }

    function renderDetailPager(totalPages, editing) {
        if (!detailPager || !detailPagerList) return;

        if (editing || totalPages <= 1) {
            detailPager.classList.add('faculty-gs-hidden');
            detailPagerList.innerHTML = '';
            return;
        }

        detailPager.classList.remove('faculty-gs-hidden');
        detailPagerList.innerHTML = ''
            + '<button type="button" class="rtp-page-btn" aria-label="Previous page" ' + (detailPage <= 1 ? 'disabled' : '') + ' data-detail-prev="1">&#x2039;</button>'
            + buildPagerButtons(totalPages)
            + '<button type="button" class="rtp-page-btn" aria-label="Next page" ' + (detailPage >= totalPages ? 'disabled' : '') + ' data-detail-next="1">&#x203A;</button>';
    }

    function buildReadOnlyRows(students) {
        var html = '';
        students.forEach(function (st, index) {
            html += '<tr>'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + escapeHtml(st.student_no || '-') + '</td>'
                + '<td>' + escapeHtml(st.name || '') + '</td>'
                + '<td>' + escapeHtml(st.prelim || '') + '</td>'
                + '<td>' + escapeHtml(st.midterm || '') + '</td>'
                + '<td>' + escapeHtml(st.final || '') + '</td>'
                + '<td>' + escapeHtml(st.final_average || '') + '</td>'
                + '<td>' + escapeHtml(st.remarks || '') + '</td>'
                + '</tr>';
        });
        return html;
    }

    function buildEditableRows(students) {
        var html = '';
        students.forEach(function (st, index) {
            html += '<tr>'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + escapeHtml(st.student_no || '-') + '</td>'
                + '<td>' + escapeHtml(st.name || '') + '</td>'
                + '<td><input type="number" min="1" max="5" step="0.01" class="grading-input" name="grades[' + st.id + '][prelim]" value="' + escapeHtml(st.prelim || '') + '"></td>'
                + '<td><input type="number" min="1" max="5" step="0.01" class="grading-input" name="grades[' + st.id + '][midterm]" value="' + escapeHtml(st.midterm || '') + '"></td>'
                + '<td><input type="number" min="1" max="5" step="0.01" class="grading-input" name="grades[' + st.id + '][final]" value="' + escapeHtml(st.final || '') + '"></td>'
                + '<td><input type="text" class="grading-input grading-readonly" name="grades[' + st.id + '][final_average]" value="' + escapeHtml(st.final_average || '') + '" readonly></td>'
                + '<td><input type="text" class="grading-input grading-readonly" name="grades[' + st.id + '][remarks]" value="' + escapeHtml(st.remarks || '') + '" readonly></td>'
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
        var students = getFilteredStudents((subject && subject.students) ? subject.students : []);
        var pageStudents = students;
        var total = students.length;
        var totalPages = Math.max(1, Math.ceil(total / detailPageSize));

        if (detailPage > totalPages) {
            detailPage = totalPages;
        }

        if (!editing) {
            var start = (detailPage - 1) * detailPageSize;
            pageStudents = students.slice(start, start + detailPageSize);
        }

        if (!pageStudents.length) {
            detailBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No students found.</td></tr>';
        } else {
            detailBody.innerHTML = editing ? buildEditableRows(pageStudents) : buildReadOnlyRows(pageStudents);
        }

        detailTitle.textContent = subject.name || '';
        detailSection.textContent = subject.section || '';
        subjectIdInput.value = subject.id || '';

        if (String(subject.status || '').toLowerCase() === 'submitted' && !editing) {
            inputAction.classList.add('faculty-gs-hidden');
        } else {
            inputAction.classList.remove('faculty-gs-hidden');
        }

        if (editing) {
            inputBtn.textContent = 'Update Grades';
            wireAutoCompute();
        } else {
            inputBtn.textContent = 'Input Grades';
        }

        renderDetailPager(totalPages, editing);
    }

    function openGradingDetail(subjectId) {
        var subject = allSubjects.find(function (item) {
            return String(item.id) === String(subjectId);
        });
        if (!subject) return;

        activeSubject = subject;
        isEditing = false;
        detailPage = 1;
        if (detailSearch) detailSearch.value = '';
        renderDetail(subject, false);
        subjectView.classList.add('faculty-gs-hidden');
        detailView.classList.remove('faculty-gs-hidden');
        setFilterMode(true);
    }

    if (subjectBody) {
        subjectBody.addEventListener('click', function (event) {
            var row = event.target.closest('tr[data-subject-id]');
            if (!row) return;
            openGradingDetail(row.getAttribute('data-subject-id'));
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            applySubjectFilters();
            renderSubjectTable();
            if (yearLabel && schoolYearSelect) {
                yearLabel.textContent = schoolYearSelect.value || 'All School Years';
            }
        });
    }

    if (schoolYearSelect) {
        schoolYearSelect.addEventListener('change', function () {
            refreshDependentFilters();
        });
    }

    if (semesterSelect) {
        semesterSelect.addEventListener('change', function () {
            refreshDependentFilters();
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', function () {
            refreshDependentFilters();
        });
    }

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            detailView.classList.add('faculty-gs-hidden');
            subjectView.classList.remove('faculty-gs-hidden');
            setFilterMode(false);
        });
    }

    if (detailSearch) {
        detailSearch.addEventListener('input', function () {
            if (!activeSubject) return;
            detailPage = 1;
            renderDetail(activeSubject, isEditing);
        });
    }

    if (detailPagerList) {
        detailPagerList.addEventListener('click', function (event) {
            if (!activeSubject || isEditing) return;

            var prevBtn = event.target.closest('[data-detail-prev]');
            if (prevBtn) {
                if (detailPage > 1) {
                    detailPage -= 1;
                    renderDetail(activeSubject, false);
                }
                return;
            }

            var nextBtn = event.target.closest('[data-detail-next]');
            if (nextBtn) {
                detailPage += 1;
                renderDetail(activeSubject, false);
                return;
            }

            var pageBtn = event.target.closest('[data-detail-page]');
            if (pageBtn) {
                detailPage = parseInt(pageBtn.getAttribute('data-detail-page'), 10) || 1;
                renderDetail(activeSubject, false);
            }
        });
    }

    if (inputBtn) {
        inputBtn.addEventListener('click', function () {
            if (!activeSubject) return;

            if (!isEditing) {
                isEditing = true;
                renderDetail(activeSubject, true);
                return;
            }

            detailView.submit();
        });
    }

    refreshDependentFilters();
    applySubjectFilters();
    renderSubjectTable();
    if (yearLabel && schoolYearSelect) {
        yearLabel.textContent = schoolYearSelect.value || 'All School Years';
    }
    setFilterMode(false);
});
