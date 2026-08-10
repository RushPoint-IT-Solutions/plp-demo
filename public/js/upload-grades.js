(function () {
    var subjects = window.UG_SUBJECTS || [];
    var selectedId = window.UG_SELECTED_SUBJECT_ID || 0;

    var courseSelect = document.getElementById('ugCourse');
    var sectionSelect = document.getElementById('ugSection');
    var pickerBody = document.getElementById('ugPickerBody');
    var pickerBlock = document.getElementById('ugPicker');
    var selectedBanner = document.getElementById('ugSelectedBanner');
    var selectedLabel = document.getElementById('ugSelectedLabel');
    var changeBtn = document.getElementById('ugChangeBtn');
    var panel = document.getElementById('ugPanel');
    var studentsBody = document.getElementById('ugStudentsBody');
    var templateLink = document.getElementById('ugTemplateLink');
    var uploadSubjectIdInput = document.getElementById('ugUploadSubjectId');
    var submitSubjectIdInput = document.getElementById('ugSubmitSubjectId');
    var submitBtn = document.getElementById('ugSubmitBtn');

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function uniqueSorted(values) {
        var seen = {};
        var out = [];
        values.forEach(function (v) {
            var key = String(v || '').trim();
            if (key === '' || seen[key]) return;
            seen[key] = true;
            out.push(key);
        });
        out.sort();
        return out;
    }

    function populateFilters() {
        if (!courseSelect || !sectionSelect) return;

        uniqueSorted(subjects.map(function (s) { return s.program; })).forEach(function (val) {
            var opt = document.createElement('option');
            opt.value = val;
            opt.textContent = val;
            courseSelect.appendChild(opt);
        });

        uniqueSorted(subjects.map(function (s) { return s.section; })).forEach(function (val) {
            var opt = document.createElement('option');
            opt.value = val;
            opt.textContent = val;
            sectionSelect.appendChild(opt);
        });
    }

    function renderPicker() {
        if (!pickerBody) return;

        var courseVal = courseSelect ? courseSelect.value : '';
        var sectionVal = sectionSelect ? sectionSelect.value : '';

        var filtered = subjects.filter(function (s) {
            if (courseVal && s.program !== courseVal) return false;
            if (sectionVal && s.section !== sectionVal) return false;
            return true;
        });

        if (!filtered.length) {
            pickerBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No matching sections found.</td></tr>';
            return;
        }

        pickerBody.innerHTML = filtered.map(function (s, index) {
            return '<tr>'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + escapeHtml(s.section) + '</td>'
                + '<td>' + escapeHtml(s.courseCode) + '</td>'
                + '<td>' + escapeHtml(s.description) + '</td>'
                + '<td>' + escapeHtml(s.faculty) + '</td>'
                + '<td>' + s.studentCount + '</td>'
                + '<td>' + escapeHtml(s.status) + '</td>'
                + '<td><button type="button" class="ug-select-btn" data-subject-id="' + s.id + '">Select</button></td>'
                + '</tr>';
        }).join('');
    }

    function findSubject(id) {
        for (var i = 0; i < subjects.length; i++) {
            if (String(subjects[i].id) === String(id)) return subjects[i];
        }
        return null;
    }

    function renderStudents(subject) {
        if (!studentsBody) return;
        var students = subject.students || [];

        if (!students.length) {
            studentsBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No students enrolled in this section.</td></tr>';
            return;
        }

        studentsBody.innerHTML = students.map(function (st, index) {
            return '<tr>'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + escapeHtml(st.studentNo) + '</td>'
                + '<td>' + escapeHtml(st.name) + '</td>'
                + '<td>' + (st.midterm !== null && st.midterm !== undefined ? st.midterm : '-') + '</td>'
                + '<td>' + (st.final !== null && st.final !== undefined ? st.final : '-') + '</td>'
                + '</tr>';
        }).join('');
    }

    function showPanelForSubject(subject) {
        if (pickerBlock) pickerBlock.style.display = 'none';
        if (selectedBanner) selectedBanner.style.display = 'flex';
        if (panel) panel.style.display = 'block';
        if (selectedLabel) selectedLabel.textContent = subject.section + ' — ' + subject.courseCode + ': ' + subject.description;
        if (uploadSubjectIdInput) uploadSubjectIdInput.value = subject.id;
        if (submitSubjectIdInput) submitSubjectIdInput.value = subject.id;
        if (templateLink) templateLink.href = (window.UG_TEMPLATE_URL_TPL || '').replace('__SUBJECT__', subject.id);
        renderStudents(subject);
    }

    function showPicker() {
        if (pickerBlock) pickerBlock.style.display = 'block';
        if (selectedBanner) selectedBanner.style.display = 'none';
        if (panel) panel.style.display = 'none';
    }

    populateFilters();
    renderPicker();

    if (courseSelect) courseSelect.addEventListener('change', renderPicker);
    if (sectionSelect) sectionSelect.addEventListener('change', renderPicker);

    if (pickerBody) {
        pickerBody.addEventListener('click', function (event) {
            var btn = event.target.closest('.ug-select-btn');
            if (!btn) return;
            var id = btn.getAttribute('data-subject-id');
            window.location.href = window.UG_LIST_URL + '?subject_id=' + encodeURIComponent(id);
        });
    }

    if (changeBtn) {
        changeBtn.addEventListener('click', function () {
            window.location.href = window.UG_LIST_URL;
        });
    }

    if (selectedId) {
        var subject = findSubject(selectedId);
        if (subject) {
            showPanelForSubject(subject);
        } else {
            showPicker();
        }
    } else {
        showPicker();
    }

    /* ─── Dropzone / file input ─────────────────────────────────────────── */

    var dropzone = document.getElementById('ugDropzone');
    var fileInput = document.getElementById('ugFileInput');
    var fileCount = document.getElementById('ugFileCount');
    var uploadButton = document.getElementById('ugUploadButton');

    function syncFileState() {
        var file = fileInput && fileInput.files ? fileInput.files[0] : null;
        if (fileCount) {
            fileCount.textContent = file ? file.name : 'No file selected.';
        }
        if (uploadButton) {
            uploadButton.disabled = !file;
        }
    }

    function setFile(files) {
        if (!files || !files.length || !fileInput) return;
        var transfer = new DataTransfer();
        transfer.items.add(files[0]);
        fileInput.files = transfer.files;
        syncFileState();
    }

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function () { fileInput.click(); });
        dropzone.addEventListener('dragover', function (event) {
            event.preventDefault();
            dropzone.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('dragover');
        });
        dropzone.addEventListener('drop', function (event) {
            event.preventDefault();
            dropzone.classList.remove('dragover');
            setFile(event.dataTransfer.files);
        });
        fileInput.addEventListener('change', syncFileState);
        syncFileState();
    }

    /* ─── Submit for Dean Review ────────────────────────────────────────── */

    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            var form = document.getElementById('ugSubmitForm');
            if (!form) return;

            if (window.Swal) {
                Swal.fire({
                    title: 'Submit for Dean Review?',
                    text: 'This will lock the uploaded grades in for Dean approval, just like a faculty submission.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#15803d',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, Submit',
                    cancelButtonText: 'Cancel',
                }).then(function (result) {
                    if (result.isConfirmed) form.submit();
                });
            } else if (window.confirm('Submit these grades for Dean review?')) {
                form.submit();
            }
        });
    }
})();
