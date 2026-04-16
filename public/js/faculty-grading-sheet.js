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
    var backBtn = document.getElementById('gradingBackBtn');
    var subjectIdInput = document.getElementById('gradingSubjectIdInput');
    var rowEditPayload = document.getElementById('gradingRowEditPayload');
    var rowEditModal = document.getElementById('gradingRowEditModal');
    var rowEditStudent = document.getElementById('gradingRowEditStudent');
    var rowEditMidterm = document.getElementById('gradingRowEditMidterm');
    var rowEditFinal = document.getElementById('gradingRowEditFinal');
    var rowEditRemarks = document.getElementById('gradingRowEditRemarks');
    var rowEditClose = document.getElementById('gradingRowEditClose');
    var rowEditCancel = document.getElementById('gradingRowEditCancel');
    var rowEditSave = document.getElementById('gradingRowEditSave');
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
    var detailPage = 1;
    var detailPageSize = 10;
    var detailButtonWindow = 5;
    var activeStudentId = null;

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

    function renderDetailPager(totalPages) {
        if (!detailPager || !detailPagerList) return;

        if (totalPages <= 1) {
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

    function buildReadOnlyRows(students, canEdit) {
        var html = '';
        students.forEach(function (st, index) {
            var editButton = canEdit
                ? '<button type="button" class="fgs-row-edit-btn" data-student-id="' + escapeHtml(st.id) + '">Edit</button>'
                : '<span class="text-muted">-</span>';

            html += '<tr>'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + escapeHtml(st.student_no || '-') + '</td>'
                + '<td>' + escapeHtml(st.name || '') + '</td>'
                + '<td>' + escapeHtml(st.prelim || '') + '</td>'
                + '<td>' + escapeHtml(st.midterm || '') + '</td>'
                + '<td>' + escapeHtml(st.final || '') + '</td>'
                + '<td>' + escapeHtml(st.final_average || '') + '</td>'
                + '<td>' + escapeHtml(st.remarks || '') + '</td>'
                + '<td class="fgs-col-action">' + editButton + '</td>'
                + '</tr>';
        });
        return html;
    }

    function renderDetail(subject) {
        var students = getFilteredStudents((subject && subject.students) ? subject.students : []);
        var pageStudents = students;
        var total = students.length;
        var totalPages = Math.max(1, Math.ceil(total / detailPageSize));
        var canEdit = String(subject.status || '').toLowerCase() !== 'submitted';

        if (detailPage > totalPages) {
            detailPage = totalPages;
        }

        var start = (detailPage - 1) * detailPageSize;
        pageStudents = students.slice(start, start + detailPageSize);

        if (!pageStudents.length) {
            detailBody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">No students found.</td></tr>';
        } else {
            detailBody.innerHTML = buildReadOnlyRows(pageStudents, canEdit);
        }

        detailTitle.textContent = subject.name || '';
        detailSection.textContent = subject.section || '';
        subjectIdInput.value = subject.id || '';

        renderDetailPager(totalPages);
    }

    function getStudentById(subject, studentId) {
        var students = (subject && subject.students) ? subject.students : [];
        for (var i = 0; i < students.length; i += 1) {
            if (String(students[i].id) === String(studentId)) {
                return students[i];
            }
        }
        return null;
    }

    function closeRowEditModal() {
        if (!rowEditModal) return;
        rowEditModal.style.display = 'none';
        rowEditModal.setAttribute('aria-hidden', 'true');
        activeStudentId = null;
    }

    function openRowEditModal(studentId) {
        if (!activeSubject || !rowEditModal) return;
        var student = getStudentById(activeSubject, studentId);
        if (!student) return;

        activeStudentId = String(student.id);
        if (rowEditStudent) {
            rowEditStudent.textContent = String(student.student_no || '') + ' - ' + String(student.name || '');
        }
        if (rowEditMidterm) rowEditMidterm.value = student.midterm || '';
        if (rowEditFinal) rowEditFinal.value = student.final || '';
        if (rowEditRemarks) rowEditRemarks.value = student.remarks || '';

        rowEditModal.style.display = 'flex';
        rowEditModal.setAttribute('aria-hidden', 'false');
    }

    function saveRowEdit() {
        if (!activeSubject || !activeStudentId || !rowEditPayload) return;

        var student = getStudentById(activeSubject, activeStudentId);
        if (!student) return;

        var midterm = rowEditMidterm ? rowEditMidterm.value.trim() : '';
        var finalGrade = rowEditFinal ? rowEditFinal.value.trim() : '';
        var remarks = rowEditRemarks ? rowEditRemarks.value.trim() : '';

        var prelim = String(student.prelim || '').trim();
        if (!midterm) {
            midterm = String(student.midterm || '').trim();
        }
        if (!finalGrade) {
            finalGrade = String(student.final || '').trim();
        }

        if (!prelim || !midterm || !finalGrade) {
            alert('Missing grade values. Midterm and Final can be edited, but Prelim must already exist in the record.');
            return;
        }

        var prelimNum = parseFloat(prelim);
        var midtermNum = parseFloat(midterm);
        var finalNum = parseFloat(finalGrade);
        if (Number.isNaN(prelimNum) || Number.isNaN(midtermNum) || Number.isNaN(finalNum)
            || prelimNum < 1 || prelimNum > 5
            || midtermNum < 1 || midtermNum > 5
            || finalNum < 1 || finalNum > 5) {
            alert('Grades must be valid values from 1.00 to 5.00.');
            return;
        }

        rowEditPayload.innerHTML = '';
        rowEditPayload.innerHTML = ''
            + '<input type="hidden" name="grades[' + activeStudentId + '][prelim]" value="' + escapeHtml(prelim) + '">'
            + '<input type="hidden" name="grades[' + activeStudentId + '][midterm]" value="' + escapeHtml(midterm) + '">'
            + '<input type="hidden" name="grades[' + activeStudentId + '][final]" value="' + escapeHtml(finalGrade) + '">'
            + '<input type="hidden" name="grades[' + activeStudentId + '][remarks]" value="' + escapeHtml(remarks) + '">';

        detailView.submit();
    }

    function openGradingDetail(subjectId) {
        var subject = allSubjects.find(function (item) {
            return String(item.id) === String(subjectId);
        });
        if (!subject) return;

        activeSubject = subject;
        detailPage = 1;
        if (detailSearch) detailSearch.value = '';
        renderDetail(subject);
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
            renderDetail(activeSubject);
        });
    }

    if (detailPagerList) {
        detailPagerList.addEventListener('click', function (event) {
            if (!activeSubject) return;

            var prevBtn = event.target.closest('[data-detail-prev]');
            if (prevBtn) {
                if (detailPage > 1) {
                    detailPage -= 1;
                    renderDetail(activeSubject);
                }
                return;
            }

            var nextBtn = event.target.closest('[data-detail-next]');
            if (nextBtn) {
                detailPage += 1;
                renderDetail(activeSubject);
                return;
            }

            var pageBtn = event.target.closest('[data-detail-page]');
            if (pageBtn) {
                detailPage = parseInt(pageBtn.getAttribute('data-detail-page'), 10) || 1;
                renderDetail(activeSubject);
            }
        });
    }

    if (detailBody) {
        detailBody.addEventListener('click', function (event) {
            var editBtn = event.target.closest('.fgs-row-edit-btn');
            if (!editBtn) return;
            openRowEditModal(editBtn.getAttribute('data-student-id'));
        });
    }

    if (rowEditClose) rowEditClose.addEventListener('click', closeRowEditModal);
    if (rowEditCancel) rowEditCancel.addEventListener('click', closeRowEditModal);
    if (rowEditSave) rowEditSave.addEventListener('click', saveRowEdit);
    if (rowEditModal) {
        rowEditModal.addEventListener('click', function (event) {
            if (event.target === rowEditModal) {
                closeRowEditModal();
            }
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
