document.addEventListener('DOMContentLoaded', function () {
    var dataNode = document.getElementById('classListData');
    var subjectView = document.getElementById('classListSubjectView');
    var detailView = document.getElementById('classListDetailView');
    var subjectBody = document.getElementById('classListBody');
    var detailTitle = document.getElementById('classListDetailTitle');
    var detailSection = document.getElementById('classListDetailSection');
    var detailBody = document.getElementById('classListDetailBody');
    var detailSearch = document.getElementById('classListDetailSearch');
    var backBtn = document.getElementById('classListBackBtn');
    var printBtn = document.getElementById('classListPrintBtn');
    var filterBar = document.getElementById('fclFilterBar');
    var detailToolsRow = document.getElementById('fclDetailToolsRow');

    var yearSelect = document.getElementById('clSchoolYear');
    var semesterSelect = document.getElementById('clSemester');
    var displayBtn = document.getElementById('clDisplayBtn');
    var yearLabel = document.getElementById('clYearLabel');

    var subjectPager = document.getElementById('classListSubjectPager');
    var subjectPagerList = document.getElementById('classListSubjectPagerList');
    var detailPager = document.getElementById('classListDetailPager');
    var detailPagerList = document.getElementById('classListDetailPagerList');

    if (!dataNode || !subjectView || !detailView || !subjectBody || !detailBody) {
        return;
    }

    var allSubjects = [];
    var studentsBySubject = {};

    try {
        allSubjects = JSON.parse(dataNode.getAttribute('data-subjects') || '[]');
    } catch (error) {
        allSubjects = [];
    }

    try {
        studentsBySubject = JSON.parse(dataNode.getAttribute('data-students') || '{}');
    } catch (error2) {
        studentsBySubject = {};
    }

    var pageSize = 10;
    var buttonWindow = 5;
    var subjectPage = 1;
    var detailPage = 1;
    var filteredSubjects = allSubjects.slice();
    var activeSubject = null;

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function buildPagerButtons(currentPage, totalPages, pageAttr) {
        var start = Math.max(1, currentPage - 2);
        var end = Math.min(totalPages, currentPage + 2);

        if (currentPage <= 3) {
            end = Math.min(totalPages, buttonWindow);
        } else if (currentPage >= totalPages - 2) {
            start = Math.max(1, totalPages - (buttonWindow - 1));
        }

        var html = '';
        for (var p = start; p <= end; p += 1) {
            html += '<button type="button" class="rtp-page-num ' + (p === currentPage ? 'active' : '') + '" ' + (p === currentPage ? 'aria-current="page"' : '') + ' ' + pageAttr + '="' + p + '">' + p + '</button>';
        }
        return html;
    }

    function renderSubjectPager(totalPages) {
        if (!subjectPager || !subjectPagerList) return;

        if (totalPages <= 1) {
            subjectPager.classList.add('faculty-gs-hidden');
            subjectPagerList.innerHTML = '';
            return;
        }

        subjectPager.classList.remove('faculty-gs-hidden');
        subjectPagerList.innerHTML = ''
            + '<button type="button" class="rtp-page-btn" aria-label="Previous page" ' + (subjectPage <= 1 ? 'disabled' : '') + ' data-subject-prev="1">&#x2039;</button>'
            + buildPagerButtons(subjectPage, totalPages, 'data-subject-page')
            + '<button type="button" class="rtp-page-btn" aria-label="Next page" ' + (subjectPage >= totalPages ? 'disabled' : '') + ' data-subject-next="1">&#x203A;</button>';
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
            + buildPagerButtons(detailPage, totalPages, 'data-detail-page')
            + '<button type="button" class="rtp-page-btn" aria-label="Next page" ' + (detailPage >= totalPages ? 'disabled' : '') + ' data-detail-next="1">&#x203A;</button>';
    }

    function applySubjectFilters() {
        var year = yearSelect ? (yearSelect.value || '') : '';
        var semester = semesterSelect ? (semesterSelect.value || '') : '';

        filteredSubjects = allSubjects.filter(function (subject) {
            return (!year || String(subject.school_year || '') === year)
                && (!semester || String(subject.semester || '') === semester);
        });

        subjectPage = 1;
    }

    function renderSubjectTable() {
        var total = filteredSubjects.length;
        var totalPages = Math.max(1, Math.ceil(total / pageSize));
        if (subjectPage > totalPages) subjectPage = totalPages;

        if (!total) {
            subjectBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No subjects found.</td></tr>';
            renderSubjectPager(1);
            return;
        }

        var start = (subjectPage - 1) * pageSize;
        var pageRows = filteredSubjects.slice(start, start + pageSize);
        var html = '';

        pageRows.forEach(function (subject) {
            html += '<tr class="faculty-click-row class-list-row"'
                + ' data-subject-id="' + escapeHtml(subject.id) + '"'
                + ' data-subject-name="' + escapeHtml(subject.name) + '"'
                + ' data-subject-section="' + escapeHtml(subject.section_display || '') + '">'
                + '<td class="text-center"><button type="button" class="grading-view-link class-list-open-link class-list-eye-btn" aria-label="View class list" title="View class list">'
                + '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
                + '</button></td>'
                + '<td class="td-code">' + escapeHtml(subject.code || '-') + '</td>'
                + '<td>' + escapeHtml(subject.name || '-') + '</td>'
                + '<td>' + escapeHtml(subject.units || '-') + '</td>'
                + '<td>' + escapeHtml(subject.days || '-') + '</td>'
                + '<td>' + escapeHtml(subject.time || '-') + '</td>'
                + '<td>' + escapeHtml(subject.room || '-') + '</td>'
                + '<td>' + escapeHtml(subject.year_section || '-') + '</td>'
                + '</tr>';
        });

        subjectBody.innerHTML = html;
        renderSubjectPager(totalPages);
    }

    function getFilteredStudents() {
        if (!activeSubject) return [];
        var students = studentsBySubject[String(activeSubject.id)] || [];
        var query = detailSearch ? (detailSearch.value || '').toLowerCase().trim() : '';

        if (!query) return students;

        return students.filter(function (student) {
            var no = String(student.student_no || '').toLowerCase();
            var name = String(student.name || '').toLowerCase();
            return no.indexOf(query) !== -1 || name.indexOf(query) !== -1;
        });
    }

    function buildPrintRows(students) {
        var rows = '';
        students.forEach(function (student, index) {
            rows += '<tr>'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + escapeHtml(student.student_no || '-') + '</td>'
                + '<td>' + escapeHtml(student.name || '-') + '</td>'
                + '<td>' + escapeHtml(student.program_block || '-') + '</td>'
                + '<td>' + escapeHtml(student.status || 'Enrolled') + '</td>'
                + '</tr>';
        });
        return rows;
    }

    function printCurrentList() {
        if (!activeSubject) return;

        var students = getFilteredStudents();
        var title = detailTitle ? detailTitle.textContent : 'Class List';
        var section = detailSection ? detailSection.textContent : '';
        var searchText = detailSearch ? (detailSearch.value || '').trim() : '';
        var subtitle = searchText ? ('Search: ' + searchText) : '';

        var printWindow = window.open('', '_blank', 'width=980,height=700');
        if (!printWindow) return;

        var tableRows = students.length
            ? buildPrintRows(students)
            : '<tr><td colspan="5" style="text-align:center;color:#666;">No students found.</td></tr>';

        var html = ''
            + '<!doctype html><html><head><meta charset="utf-8"><title>Print Class List</title>'
            + '<style>'
            + 'body{font-family:Arial,sans-serif;color:#222;padding:24px;}'
            + '.head{margin-bottom:14px;}'
            + '.title{font-size:20px;font-weight:700;color:#006837;margin:0 0 4px;}'
            + '.section{font-size:14px;font-weight:600;margin:0 0 4px;}'
            + '.meta{font-size:12px;color:#666;margin:0;}'
            + 'table{width:100%;border-collapse:collapse;font-size:12px;}'
            + 'th,td{border:1px solid #cfd8d3;padding:7px 8px;text-align:left;}'
            + 'th{background:#006837;color:#fff;font-weight:700;}'
            + 'td:first-child,th:first-child{text-align:center;width:48px;}'
            + '@page{size:auto;margin:12mm;}'
            + '</style></head><body>'
            + '<div class="head">'
            + '<p class="title">' + escapeHtml(title || 'Class List') + '</p>'
            + '<p class="section">' + escapeHtml(section || '') + '</p>'
            + '<p class="meta">' + escapeHtml(subtitle) + '</p>'
            + '</div>'
            + '<table>'
            + '<thead><tr><th>No.</th><th>Student ID</th><th>Name</th><th>Program / Yr / Block</th><th>Status</th></tr></thead>'
            + '<tbody>' + tableRows + '</tbody>'
            + '</table>'
            + '</body></html>';

        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }

    function renderDetailTable() {
        var students = getFilteredStudents();
        var total = students.length;
        var totalPages = Math.max(1, Math.ceil(total / pageSize));
        if (detailPage > totalPages) detailPage = totalPages;

        if (!total) {
            detailBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No students enrolled.</td></tr>';
            renderDetailPager(1);
            return;
        }

        var start = (detailPage - 1) * pageSize;
        var pageRows = students.slice(start, start + pageSize);
        var html = '';

        pageRows.forEach(function (student, index) {
            html += '<tr>'
                + '<td>' + (start + index + 1) + '</td>'
                + '<td>' + escapeHtml(student.student_no || '-') + '</td>'
                + '<td>' + escapeHtml(student.name || '-') + '</td>'
                + '<td>' + escapeHtml(student.program_block || '-') + '</td>'
                + '<td>' + escapeHtml(student.status || 'Enrolled') + '</td>'
                + '</tr>';
        });

        detailBody.innerHTML = html;
        renderDetailPager(totalPages);
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

    function openDetail(row) {
        if (!row) return;

        var subjectId = row.getAttribute('data-subject-id');
        var subjectName = row.getAttribute('data-subject-name') || 'Class List';
        var section = row.getAttribute('data-subject-section') || '';
        activeSubject = allSubjects.find(function (subject) {
            return String(subject.id) === String(subjectId);
        });
        detailPage = 1;
        if (detailSearch) detailSearch.value = '';

        if (detailTitle) detailTitle.textContent = subjectName;
        if (detailSection) detailSection.textContent = section;
        renderDetailTable();
        if (subjectView) subjectView.classList.add('faculty-gs-hidden');
        if (detailView) detailView.classList.remove('faculty-gs-hidden');
        setFilterMode(true);
    }

    if (subjectBody) {
        subjectBody.addEventListener('click', function (event) {
            var row = event.target.closest('tr.class-list-row');
            if (!row) return;
            event.preventDefault();
            openDetail(row);
        });
    }

    if (displayBtn) {
        displayBtn.addEventListener('click', function () {
            applySubjectFilters();
            renderSubjectTable();
            if (yearLabel && yearSelect) {
                yearLabel.textContent = yearSelect.value || 'All School Years';
            }
        });
    }

    if (yearSelect) {
        yearSelect.addEventListener('change', function () {
            if (yearLabel) {
                yearLabel.textContent = yearSelect.value || 'All School Years';
            }
        });
    }

    if (subjectPagerList) {
        subjectPagerList.addEventListener('click', function (event) {
            var prevBtn = event.target.closest('[data-subject-prev]');
            if (prevBtn) {
                if (subjectPage > 1) {
                    subjectPage -= 1;
                    renderSubjectTable();
                }
                return;
            }

            var nextBtn = event.target.closest('[data-subject-next]');
            if (nextBtn) {
                subjectPage += 1;
                renderSubjectTable();
                return;
            }

            var pageBtn = event.target.closest('[data-subject-page]');
            if (pageBtn) {
                subjectPage = parseInt(pageBtn.getAttribute('data-subject-page'), 10) || 1;
                renderSubjectTable();
            }
        });
    }

    if (detailSearch) {
        detailSearch.addEventListener('input', function () {
            detailPage = 1;
            renderDetailTable();
        });
    }

    if (detailPagerList) {
        detailPagerList.addEventListener('click', function (event) {
            var prevBtn = event.target.closest('[data-detail-prev]');
            if (prevBtn) {
                if (detailPage > 1) {
                    detailPage -= 1;
                    renderDetailTable();
                }
                return;
            }

            var nextBtn = event.target.closest('[data-detail-next]');
            if (nextBtn) {
                detailPage += 1;
                renderDetailTable();
                return;
            }

            var pageBtn = event.target.closest('[data-detail-page]');
            if (pageBtn) {
                detailPage = parseInt(pageBtn.getAttribute('data-detail-page'), 10) || 1;
                renderDetailTable();
            }
        });
    }

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            if (detailView) detailView.classList.add('faculty-gs-hidden');
            if (subjectView) subjectView.classList.remove('faculty-gs-hidden');
            setFilterMode(false);
        });
    }

    if (printBtn) {
        printBtn.addEventListener('click', function () {
            printCurrentList();
        });
    }

    applySubjectFilters();
    renderSubjectTable();
    setFilterMode(false);
    if (yearLabel && yearSelect) {
        yearLabel.textContent = yearSelect.value || 'All School Years';
    }
});
