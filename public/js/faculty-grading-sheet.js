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
    var rowEditPrelim = document.getElementById('gradingRowEditPrelim');
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
    var submitBtn = document.getElementById('gradingSubmitBtn');

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

    // ─── Helpers ────────────────────────────────────────────────────────────────

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function getCsrf() {
        return (document.querySelector('meta[name="csrf-token"]') || {}).content
            || (document.querySelector('input[name="_token"]') || {}).value
            || '';
    }

    function toNumber(value) {
        var num = parseFloat(value);
        return Number.isNaN(num) ? null : num;
    }

    function statusText(value) {
        var normalized = String(value || '').toLowerCase();
        if (normalized === 'submitted') return 'Submitted for Dean Review';
        if (normalized === 'dean approved') return 'Dean Approved';
        if (normalized === 'registrar finalized') return 'Registrar Finalized';
        if (normalized === 'rejected' || normalized === 'returned for revision') return 'Returned for Revision';
        return value || 'Open For Encoding';
    }

    function statusBadge(value) {
        var label = statusText(value);
        var normalized = String(label || '').toLowerCase();
        if (normalized === 'submitted for dean review' || normalized === 'dean approved' || normalized === 'registrar finalized') {
            return '<span class="grading-status-submitted">' + escapeHtml(label) + '</span>';
        }
        return '<span class="grading-status-open">' + escapeHtml(label) + '</span>';
    }

    // ─── SweetAlert2 Helpers ─────────────────────────────────────────────────────

    function swalSuccess(title, html) {
        return Swal.fire({
            title: title,
            html: html,
            icon: 'success',
            confirmButtonColor: '#15803d',
            confirmButtonText: 'Done',
        });
    }

    function swalError(title, html) {
        return Swal.fire({
            title: title,
            html: html,
            icon: 'error',
            confirmButtonColor: '#15803d',
            confirmButtonText: 'OK',
        });
    }

    function swalWarning(title, text) {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            confirmButtonColor: '#15803d',
            confirmButtonText: 'OK',
        });
    }

    function swalConfirm(title, html) {
        return Swal.fire({
            title: title,
            html: html,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#15803d',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Submit',
            cancelButtonText: 'Cancel',
        });
    }

    function swalMissingGrades(message, missing) {
        var missingHtml = (missing || []).map(function (item) {
            return '<li style="text-align:left;">' + escapeHtml(item) + '</li>';
        }).join('');

        return swalError(
            'Cannot Submit Grades',
            '<p style="color:#b91c1c;font-weight:600;margin-bottom:0.5rem;">'
                + escapeHtml(message || 'Some students have missing grades.')
                + '</p>'
                + '<ul style="max-height:200px;overflow-y:auto;padding-left:1.2rem;color:#374151;font-size:0.88rem;">'
                + missingHtml
                + '</ul>'
        );
    }

    // ─── Filters ─────────────────────────────────────────────────────────────────

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

    function setFilterMode(isDetailMode) {
        if (filterBar) {
            filterBar.classList.toggle('faculty-gs-hidden', isDetailMode);
        }
        if (detailToolsRow) {
            detailToolsRow.classList.toggle('faculty-gs-hidden', !isDetailMode);
        }
    }

    // ─── Subject Table ────────────────────────────────────────────────────────────

    function renderSubjectTable() {
        if (!subjectBody) return;

        if (!filteredSubjects.length) {
            subjectBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No subjects found.</td></tr>';
            return;
        }

        var rows = '';
        filteredSubjects.forEach(function (subject, index) {
            var normalizedStatus = String(statusText(subject.status)).toLowerCase();
            var canSubmit = normalizedStatus === 'open for encoding' || normalizedStatus === 'returned for revision';

            var actionBtn = !canSubmit
                ? '<span class="fgs-submitted-label">&#10004; ' + escapeHtml(statusText(subject.status)) + '</span>'
                : '<button type="button" class="fgs-quick-submit-btn" data-subject-id="' + escapeHtml(subject.id) + '">Submit Grades</button>';

            rows += '<tr class="grading-subject-row" data-subject-id="' + escapeHtml(subject.id) + '">'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + escapeHtml(subject.section || '-') + '</td>'
                + '<td class="td-code">' + escapeHtml(subject.code || '-') + '</td>'
                + '<td>' + escapeHtml(subject.name || '-') + '</td>'
                + '<td>' + escapeHtml(subject.days || '-') + '</td>'
                + '<td>' + statusBadge(subject.status) + '</td>'
                + '<td class="fgs-col-action">' + actionBtn + '</td>'
                + '</tr>';
        });

        subjectBody.innerHTML = rows;
    }

    // ─── Quick Submit (from subject list) ─────────────────────────────────────────

    function doQuickSubmit(subjectId, quickSubmitBtn) {
        var subject = allSubjects.find(function (item) {
            return String(item.id) === String(subjectId);
        });
        if (!subject) return;

        swalConfirm(
            'Submit Grades?',
            'You are about to submit grades for <br>'
                + '<strong>' + escapeHtml(subject.name) + '</strong><br>'
                + '<span style="color:#6b7280;font-size:0.9rem;">' + escapeHtml(subject.section) + '</span><br><br>'
                + 'This <strong>cannot be undone</strong>.'
        ).then(function (result) {
            if (!result.isConfirmed) return;

            quickSubmitBtn.disabled = true;
            quickSubmitBtn.textContent = 'Submitting...';

            fetch('/faculty/grading-sheet/submit-grades', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ subject_id: subjectId }),
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.ok) {
                    swalMissingGrades(data.message, data.missing);
                    quickSubmitBtn.disabled = false;
                    quickSubmitBtn.textContent = 'Submit Grades';
                    return;
                }

                // Update local data
                allSubjects.forEach(function (s) {
                    if (String(s.id) === String(subjectId)) {
                        s.status = 'Submitted for Dean Review';
                    }
                });
                applySubjectFilters();
                renderSubjectTable();

                swalSuccess(
                    'Grades Submitted!',
                    '<strong>' + escapeHtml(subject.name) + '</strong><br>'
                        + '<span style="color:#6b7280;font-size:0.9rem;">' + escapeHtml(subject.section) + '</span><br><br>'
                        + 'Grades have been submitted successfully.'
                );
            })
            .catch(function () {
                swalError('Network Error', 'Something went wrong. Please try again.');
                quickSubmitBtn.disabled = false;
                quickSubmitBtn.textContent = 'Submit Grades';
            });
        });
    }

    // ─── Detail View ──────────────────────────────────────────────────────────────

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
            html += '<button type="button" class="rtp-page-num ' + (p === detailPage ? 'active' : '') + '" '
                + (p === detailPage ? 'aria-current="page"' : '')
                + ' data-detail-page="' + p + '">' + p + '</button>';
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
            + '<button type="button" class="rtp-page-btn" aria-label="Previous page" '
            + (detailPage <= 1 ? 'disabled' : '') + ' data-detail-prev="1">&#x2039;</button>'
            + buildPagerButtons(totalPages)
            + '<button type="button" class="rtp-page-btn" aria-label="Next page" '
            + (detailPage >= totalPages ? 'disabled' : '') + ' data-detail-next="1">&#x203A;</button>';
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
        var total = students.length;
        var totalPages = Math.max(1, Math.ceil(total / detailPageSize));
        var normalizedStatus = String(statusText(subject.status)).toLowerCase();
        var canEdit = normalizedStatus === 'open for encoding' || normalizedStatus === 'returned for revision';

        if (detailPage > totalPages) {
            detailPage = totalPages;
        }

        var start = (detailPage - 1) * detailPageSize;
        var pageStudents = students.slice(start, start + detailPageSize);

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

        // Show/hide submit button based on status
        if (submitBtn) {
            var normalizedSubjectStatus = String(statusText(subject.status)).toLowerCase();
            var canSubmit = normalizedSubjectStatus === 'open for encoding' || normalizedSubjectStatus === 'returned for revision';
            submitBtn.style.display = canSubmit ? 'inline-flex' : 'none';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Grades';
        }
    }

    // ─── Submit Grades (from detail view) ─────────────────────────────────────────

    function submitGrades() {
        if (!activeSubject) return;

        swalConfirm(
            'Submit Grades?',
            'You are about to submit grades for <br>'
                + '<strong>' + escapeHtml(activeSubject.name) + '</strong><br>'
                + '<span style="color:#6b7280;font-size:0.9rem;">' + escapeHtml(activeSubject.section) + '</span><br><br>'
                + 'This <strong>cannot be undone</strong>.'
        ).then(function (result) {
            if (!result.isConfirmed) return;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            fetch('/faculty/grading-sheet/submit-grades', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ subject_id: activeSubject.id }),
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.ok) {
                    swalMissingGrades(data.message, data.missing);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Grades';
                    return;
                }

                // Update local data
                activeSubject.status = 'Submitted for Dean Review';
                allSubjects.forEach(function (s) {
                    if (String(s.id) === String(activeSubject.id)) {
                        s.status = 'Submitted for Dean Review';
                    }
                });

                submitBtn.style.display = 'none';
                renderDetail(activeSubject);

                swalSuccess(
                    'Grades Submitted!',
                    '<strong>' + escapeHtml(activeSubject.name) + '</strong><br>'
                        + '<span style="color:#6b7280;font-size:0.9rem;">' + escapeHtml(activeSubject.section) + '</span><br><br>'
                        + 'Grades have been submitted successfully.'
                );
            })
            .catch(function () {
                swalError('Network Error', 'Something went wrong. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Grades';
            });
        });
    }

    // ─── Row Edit Modal ───────────────────────────────────────────────────────────

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
        if (rowEditPrelim) rowEditPrelim.value = student.prelim || '';
        if (rowEditMidterm) rowEditMidterm.value = student.midterm || '';
        if (rowEditFinal) rowEditFinal.value = student.final || '';
        if (rowEditRemarks) rowEditRemarks.value = student.remarks || '';

        rowEditModal.style.display = 'flex';
        rowEditModal.setAttribute('aria-hidden', 'false');
    }

    function saveRowEdit() {
        if (!activeSubject || !activeStudentId) return;

        var student = getStudentById(activeSubject, activeStudentId);
        if (!student) return;

        var prelim     = rowEditPrelim ? rowEditPrelim.value.trim() : '';
        var midterm    = rowEditMidterm ? rowEditMidterm.value.trim() : '';
        var finalGrade = rowEditFinal   ? rowEditFinal.value.trim()   : '';
        var remarks    = rowEditRemarks ? rowEditRemarks.value.trim() : '';

        // Only midterm is required — final is optional (may not be done yet)
        if (!prelim || !midterm) {
            closeRowEditModal(); // close first so SweetAlert appears on top
            swalWarning('Missing Grade', 'Please enter Prelim and Midterm grades.');
            return;
        }

        var prelimNum = parseFloat(prelim);
        var midtermNum = parseFloat(midterm);
        var finalNum   = finalGrade !== '' ? parseFloat(finalGrade) : null;

        if (isNaN(prelimNum) || prelimNum < 0 || prelimNum > 100) {
            closeRowEditModal();
            swalWarning('Invalid Prelim Grade', 'Prelim grade must be between 0 and 100. Grade-point values from 1.00 to 5.00 are still accepted.');
            return;
        }

        if (isNaN(midtermNum) || midtermNum < 0 || midtermNum > 100) {
            closeRowEditModal();
            swalWarning('Invalid Midterm Grade', 'Midterm grade must be between 0 and 100. Grade-point values from 1.00 to 5.00 are still accepted.');
            return;
        }

        if (finalNum !== null && (isNaN(finalNum) || finalNum < 0 || finalNum > 100)) {
            closeRowEditModal();
            swalWarning('Invalid Final Grade', 'Final grade must be between 0 and 100. Grade-point values from 1.00 to 5.00 are still accepted.');
            return;
        }

        rowEditSave.disabled = true;
        rowEditSave.textContent = 'Saving...';

        fetch('/faculty/grading-sheet/update-row', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                subject_id: activeSubject.id,
                student_id: activeStudentId,
                prelim:     prelimNum,
                midterm:    midtermNum,
                final:      finalNum,   // null if not yet entered
                remarks:    remarks,
            }),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.ok) {
                closeRowEditModal();
                swalError('Save Failed', 'Failed to save grades. Please try again.');
                return;
            }

            student.prelim        = data.prelim;
            student.midterm       = data.midterm;
            student.final         = data.final;
            student.final_average = data.final_average;
            student.remarks       = data.remarks;

            closeRowEditModal();
            renderDetail(activeSubject);

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Grade saved successfully',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
            });
        })
        .catch(function () {
            closeRowEditModal();
            swalError('Network Error', 'Something went wrong. Please try again.');
        })
        .finally(function () {
            rowEditSave.disabled = false;
            rowEditSave.textContent = 'Save';
        });
    }

    // ─── Event Listeners ──────────────────────────────────────────────────────────

    // Subject table: row click → open detail, button click → quick submit
    if (subjectBody) {
        subjectBody.addEventListener('click', function (event) {
            var quickSubmitBtn = event.target.closest('.fgs-quick-submit-btn');
            if (quickSubmitBtn) {
                event.stopPropagation();
                doQuickSubmit(quickSubmitBtn.getAttribute('data-subject-id'), quickSubmitBtn);
                return;
            }

            var row = event.target.closest('tr[data-subject-id]');
            if (row && !event.target.closest('button')) {
                openGradingDetail(row.getAttribute('data-subject-id'));
            }
        });
    }

    // Search button
    if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            applySubjectFilters();
            renderSubjectTable();
            if (yearLabel && schoolYearSelect) {
                yearLabel.textContent = schoolYearSelect.value || 'All School Years';
            }
        });
    }

    // Filter dropdowns
    if (schoolYearSelect) schoolYearSelect.addEventListener('change', refreshDependentFilters);
    if (semesterSelect)   semesterSelect.addEventListener('change', refreshDependentFilters);
    if (statusSelect)     statusSelect.addEventListener('change', refreshDependentFilters);

    // Back button
    if (backBtn) {
        backBtn.addEventListener('click', function () {
            detailView.classList.add('faculty-gs-hidden');
            subjectView.classList.remove('faculty-gs-hidden');
            setFilterMode(false);
        });
    }

    // Detail search
    if (detailSearch) {
        detailSearch.addEventListener('input', function () {
            if (!activeSubject) return;
            detailPage = 1;
            renderDetail(activeSubject);
        });
    }

    // Detail pager
    if (detailPagerList) {
        detailPagerList.addEventListener('click', function (event) {
            if (!activeSubject) return;

            if (event.target.closest('[data-detail-prev]')) {
                if (detailPage > 1) { detailPage -= 1; renderDetail(activeSubject); }
                return;
            }
            if (event.target.closest('[data-detail-next]')) {
                detailPage += 1; renderDetail(activeSubject);
                return;
            }
            var pageBtn = event.target.closest('[data-detail-page]');
            if (pageBtn) {
                detailPage = parseInt(pageBtn.getAttribute('data-detail-page'), 10) || 1;
                renderDetail(activeSubject);
            }
        });
    }

    // Detail body: edit button
    if (detailBody) {
        detailBody.addEventListener('click', function (event) {
            var editBtn = event.target.closest('.fgs-row-edit-btn');
            if (!editBtn) return;
            openRowEditModal(editBtn.getAttribute('data-student-id'));
        });
    }

    // Submit button (detail view)
    if (submitBtn) {
        submitBtn.addEventListener('click', submitGrades);
    }

    // Row edit modal controls
    if (rowEditClose)  rowEditClose.addEventListener('click', closeRowEditModal);
    if (rowEditCancel) rowEditCancel.addEventListener('click', closeRowEditModal);
    if (rowEditSave)   rowEditSave.addEventListener('click', saveRowEdit);
    if (rowEditModal) {
        rowEditModal.addEventListener('click', function (event) {
            if (event.target === rowEditModal) closeRowEditModal();
        });
    }

    // ─── Init ─────────────────────────────────────────────────────────────────────

    refreshDependentFilters();
    applySubjectFilters();
    renderSubjectTable();
    if (yearLabel && schoolYearSelect) {
        yearLabel.textContent = schoolYearSelect.value || 'All School Years';
    }
    setFilterMode(false);
});
