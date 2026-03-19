var SD_STUDENTS = [
    {
        studentId: '2223A8137',
        name: 'Mark Jay Bares',
        degreeProgram: 'Bachelor of Science in Computer Science',
        birthDate: '01/01/2000',
        gender: 'Male',
        studentType: 'Old'
    },
    {
        studentId: '2223A8139',
        name: 'Andrea Jane Austero',
        degreeProgram: 'Bachelor of Science in Computer Science',
        birthDate: '01/01/2001',
        gender: 'Female',
        studentType: 'Old'
    }
];

var SD_RECORDS = {
    '2223A8137': [
        {
            incidentDate: '2026-03-02',
            walkIn: true,
            caseType: 'Minor Offense',
            calledBy: 'Prof. Dela Cruz',
            description: 'Late submission of project requirement.',
            referred: false,
            referredBy: '',
            actionDate: '2026-03-03',
            actionType: 'Counseling Session',
            counselor: 'Guidance Office',
            remarks: 'Advised student on time management.',
            completed: true
        },
        {
            incidentDate: '2026-03-11',
            walkIn: false,
            caseType: 'Counseling',
            calledBy: 'Class Adviser',
            description: 'Academic stress concern reported by adviser.',
            referred: true,
            referredBy: 'Student Affairs Office',
            actionDate: '2026-03-12',
            actionType: 'Follow-up Guidance',
            counselor: 'Ms. Santos',
            remarks: 'Follow-up after one week.',
            completed: false
        }
    ],
    '2223A8139': [
        {
            incidentDate: '2026-02-24',
            walkIn: true,
            caseType: 'Major Offense',
            calledBy: 'Discipline Office',
            description: 'Violation of dress code policy.',
            referred: true,
            referredBy: 'Department Chair',
            actionDate: '2026-02-25',
            actionType: 'Written Warning',
            counselor: 'Mr. Ramos',
            remarks: 'Parent notified.',
            completed: true
        }
    ]
};

var SD_SELECTED_STUDENT_ID = null;
var SD_PENDING_DELETE_ID = null;
var SD_EDIT_RECORD_INDEX = null;
var SD_PENDING_DELETE_RECORD_INDEX = null;
var SD_MENU_INDEX = 0;

function sdApplySystemConfig() {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('System configuration saved successfully.', 'success');
    }
}

function sdFilterStudents() {
    renderSdStudents();
}

function sdClearFilters() {
    var studentId = document.getElementById('sdStudentId');
    var fullName = document.getElementById('sdFullName');
    var studentType = document.getElementById('sdStudentTypeFilter');

    if (studentId) studentId.value = '';
    if (fullName) fullName.value = '';
    if (studentType) studentType.value = '';

    renderSdStudents();
}

function getSdFilteredStudents() {
    var studentIdInput = document.getElementById('sdStudentId');
    var fullNameInput = document.getElementById('sdFullName');
    var studentTypeInput = document.getElementById('sdStudentTypeFilter');

    var studentId = studentIdInput ? (studentIdInput.value || '').toLowerCase() : '';
    var fullNameFilter = fullNameInput ? (fullNameInput.value || '').toLowerCase() : '';
    var studentType = studentTypeInput ? studentTypeInput.value : '';

    return SD_STUDENTS.filter(function (student) {
        var nameVal = String(student.name || '').toLowerCase();
        var idVal = String(student.studentId || '').toLowerCase();
        var typeVal = String(student.studentType || ''); // Assuming SD_STUDENTS has studentType if "old"/"new" is needed. Otherwise we'll just ignore or do simple match if property exists

        var hasId = !studentId || idVal.indexOf(studentId) !== -1;
        var hasName = !fullNameFilter || nameVal.indexOf(fullNameFilter) !== -1;
        var hasType = !studentType || typeVal === studentType;

        return hasId && hasName && hasType;
    });
}

function renderSdStudents() {
    var tbody = document.getElementById('sdStudentsBody');
    if (!tbody) return;

    var students = getSdFilteredStudents();

    if (!students.length) {
        tbody.innerHTML = '<tr class="sd-empty-row"><td colspan="8">List Empty.</td></tr>' +
                          '<tr class="sd-total-row"><td colspan="8" class="sd-total-cell">Total Students: <strong>0</strong></td></tr>';
        return;
    }

    var rows = '';
    for (var i = 0; i < students.length; i++) {
        var s = students[i];
        var menuId = 'sdMenu' + (SD_MENU_INDEX++);
        rows += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + escapeHtml(s.studentId) + '</td>' +
            '<td><a href="#" class="sd-name-link" onclick="sdOpenStudentConduct(\'' + jsEscape(s.studentId) + '\'); return false;">' + escapeHtml(s.name) + '</a></td>' +
            '<td>' + escapeHtml(s.degreeProgram) + '</td>' +
            '<td>' + escapeHtml(s.birthDate) + '</td>' +
            '<td>' + escapeHtml(s.gender) + '</td>' +
            '<td>' + escapeHtml(s.studentType) + '</td>' +
            '<td>' +
                '<div class="apst-action-btn" data-sd-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="' + menuId + '">' +
                    '<button type="button" data-sd-open-action="edit" data-sd-student-id="' + escapeHtml(s.studentId) + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        'Edit' +
                    '</button>' +
                    '<button type="button" class="apst-del-btn" data-sd-open-action="delete" data-sd-student-id="' + escapeHtml(s.studentId) + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        'Delete' +
                    '</button>' +
                '</div>' +
            '</td>' +
            '</tr>';
    }

    rows += '<tr class="sd-total-row"><td colspan="8" class="sd-total-cell">Total Students: <strong>' + students.length + '</strong></td></tr>';

    tbody.innerHTML = rows;
}

function sdBackToList() {
    document.getElementById('sdDetailView').style.display = 'none';
    document.getElementById('sdListView').style.display = 'block';
}

function sdOpenStudentConduct(studentId) {
    var student = SD_STUDENTS.find(function (item) { return item.studentId === studentId; });
    if (!student) return;

    SD_SELECTED_STUDENT_ID = studentId;

    document.getElementById('sdBannerStudentId').textContent = student.studentId;
    document.getElementById('sdBannerStudentName').textContent = student.name;

    document.getElementById('sdListView').style.display = 'none';
    document.getElementById('sdDetailView').style.display = 'block';

    renderSdConductTable();
}

function renderSdConductTable() {
    var tbody = document.getElementById('sdConductBody');
    if (!tbody) return;

    var records = SD_RECORDS[SD_SELECTED_STUDENT_ID] || [];
    if (!records.length) {
        tbody.innerHTML = '<tr class="sd-empty-row"><td colspan="5">List Empty.</td></tr>';
        return;
    }

    var rows = '';
    for (var i = 0; i < records.length; i++) {
        var record = records[i];
        var menuId = 'sdConductMenu' + (SD_MENU_INDEX++);
        rows += '<tr>' +
            '<td>' + escapeHtml(formatInputDate(record.incidentDate)) + '</td>' +
            '<td>' + escapeHtml(record.caseType || '-') + '</td>' +
            '<td>' + escapeHtml(record.actionType || '-') + '</td>' +
            '<td style="text-align:center !important;">' + (record.completed ? 'Yes' : 'No') + '</td>' +
            '<td>' +
                '<div class="apst-action-btn" data-sd-conduct-menu-toggle="' + menuId + '" aria-label="Open conduct row actions" title="Actions">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="' + menuId + '">' +
                    '<button type="button" data-sd-conduct-action="edit" data-sd-record-index="' + i + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        'Edit' +
                    '</button>' +
                    '<button type="button" class="apst-del-btn" data-sd-conduct-action="delete" data-sd-record-index="' + i + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        'Delete' +
                    '</button>' +
                '</div>' +
            '</td>' +
            '</tr>';
    }

    tbody.innerHTML = rows;
}

function sdOpenRecordModal() {
    if (!SD_SELECTED_STUDENT_ID) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Select a student first.', 'warning');
        }
        return;
    }

    SD_EDIT_RECORD_INDEX = null;
    sdResetModalFields();
    var saveBtn = document.getElementById('sdRecordSaveBtn');
    if (saveBtn) saveBtn.textContent = 'Save Record';
    document.getElementById('sdRecordModal').style.display = 'flex';
}

function sdOpenEditRecordModal(index) {
    if (!SD_SELECTED_STUDENT_ID) return;

    var records = SD_RECORDS[SD_SELECTED_STUDENT_ID] || [];
    var record = records[index];
    if (!record) return;

    SD_EDIT_RECORD_INDEX = index;
    document.getElementById('sdIncidentDate').value = record.incidentDate || '';
    document.getElementById('sdWalkIn').checked = !!record.walkIn;
    document.getElementById('sdCaseType').value = record.caseType || '';
    document.getElementById('sdCalledBy').value = record.calledBy || '';
    document.getElementById('sdDescription').value = record.description || '';
    document.getElementById('sdActionDate').value = record.actionDate || '';
    document.getElementById('sdCompleted').checked = !!record.completed;
    document.getElementById('sdActionType').value = record.actionType || '';
    document.getElementById('sdCounselor').value = record.counselor || '';
    document.getElementById('sdRemarks').value = record.remarks || '';

    var saveBtn = document.getElementById('sdRecordSaveBtn');
    if (saveBtn) saveBtn.textContent = 'Save Changes';

    document.getElementById('sdRecordModal').style.display = 'flex';
}

function sdOpenDeleteRecordModal(index) {
    if (!SD_SELECTED_STUDENT_ID) return;

    var records = SD_RECORDS[SD_SELECTED_STUDENT_ID] || [];
    var record = records[index];
    if (!record) return;

    SD_PENDING_DELETE_RECORD_INDEX = index;
    var details = [record.caseType || 'Record'];
    if (record.incidentDate) details.push(formatInputDate(record.incidentDate));
    document.getElementById('sdDeleteRecordText').textContent = 'Are you sure you want to delete this conduct record (' + details.join(' - ') + ')?';
    document.getElementById('sdDeleteRecordModal').style.display = 'flex';
}

function sdCloseDeleteRecordModal() {
    SD_PENDING_DELETE_RECORD_INDEX = null;
    document.getElementById('sdDeleteRecordModal').style.display = 'none';
}

function sdConfirmDeleteRecord() {
    if (!SD_SELECTED_STUDENT_ID && SD_SELECTED_STUDENT_ID !== '') return;
    if (SD_PENDING_DELETE_RECORD_INDEX === null) return;

    var records = SD_RECORDS[SD_SELECTED_STUDENT_ID] || [];
    records.splice(SD_PENDING_DELETE_RECORD_INDEX, 1);
    SD_RECORDS[SD_SELECTED_STUDENT_ID] = records;

    sdCloseDeleteRecordModal();
    renderSdConductTable();

    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Record deleted successfully.', 'success');
    }
}

function sdOpenEditModal(studentId) {
    var student = SD_STUDENTS.find(function (item) { return item.studentId === studentId; });
    if (!student) return;

    SD_SELECTED_STUDENT_ID = studentId;

    document.getElementById('sdEditStudentId').value = student.studentId;
    document.getElementById('sdEditStudentName').value = student.name;
    document.getElementById('sdEditDegreeProgram').value = student.degreeProgram;
    document.getElementById('sdEditBirthDate').value = student.birthDate;
    document.getElementById('sdEditGender').value = student.gender;
    document.getElementById('sdEditStudentType').value = student.studentType;

    document.getElementById('sdEditStudentModal').style.display = 'flex';
}

function sdOpenAddStudentModal() {
    document.getElementById('sdAddStudentId').value = '';
    document.getElementById('sdAddStudentName').value = '';
    document.getElementById('sdAddDegreeProgram').value = 'Bachelor of Science in Computer Science';
    document.getElementById('sdAddBirthDate').value = '';
    document.getElementById('sdAddGender').value = 'Male';
    document.getElementById('sdAddStudentType').value = 'New';

    document.getElementById('sdAddStudentModal').style.display = 'flex';
}

function sdCloseAddStudentModal() {
    document.getElementById('sdAddStudentModal').style.display = 'none';
}

function sdSaveNewStudent() {
    var studentId = (document.getElementById('sdAddStudentId').value || '').trim();
    var studentName = (document.getElementById('sdAddStudentName').value || '').trim();
    var degreeProgram = (document.getElementById('sdAddDegreeProgram').value || '').trim();
    var birthDateRaw = (document.getElementById('sdAddBirthDate').value || '').trim();
    var gender = (document.getElementById('sdAddGender').value || '').trim();
    var studentType = (document.getElementById('sdAddStudentType').value || '').trim();

    if (!studentId || !studentName) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Student ID and Student Name are required.', 'warning');
        }
        return;
    }

    var exists = SD_STUDENTS.some(function (student) {
        return String(student.studentId || '').toLowerCase() === studentId.toLowerCase();
    });

    if (exists) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Student ID already exists.', 'warning');
        }
        return;
    }

    SD_STUDENTS.push({
        studentId: studentId,
        name: studentName,
        degreeProgram: degreeProgram || 'Bachelor of Science in Computer Science',
        birthDate: birthDateRaw ? formatInputDate(birthDateRaw) : '-',
        gender: gender || 'Male',
        studentType: studentType || 'New'
    });

    SD_RECORDS[studentId] = SD_RECORDS[studentId] || [];

    sdCloseAddStudentModal();
    renderSdStudents();

    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Student added successfully.', 'success');
    }
}

function sdCloseEditModal() {
    document.getElementById('sdEditStudentModal').style.display = 'none';
}

function sdSaveStudentEdit() {
    var originalStudentId = SD_SELECTED_STUDENT_ID;
    var student = SD_STUDENTS.find(function (item) { return item.studentId === originalStudentId; });
    if (!student) return;

    var editedId = (document.getElementById('sdEditStudentId').value || '').trim();
    var editedName = (document.getElementById('sdEditStudentName').value || '').trim();
    var editedProgram = (document.getElementById('sdEditDegreeProgram').value || '').trim();
    var editedBirthDate = (document.getElementById('sdEditBirthDate').value || '').trim();
    var editedGender = (document.getElementById('sdEditGender').value || '').trim();
    var editedType = (document.getElementById('sdEditStudentType').value || '').trim();

    if (!editedId || !editedName) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Student ID and Student Name are required.', 'warning');
        }
        return;
    }

    var duplicate = SD_STUDENTS.some(function (item) {
        return item.studentId === editedId && item.studentId !== originalStudentId;
    });

    if (duplicate) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Student ID already exists.', 'warning');
        }
        return;
    }

    student.studentId = editedId;
    student.name = editedName;
    student.degreeProgram = editedProgram || student.degreeProgram;
    student.birthDate = editedBirthDate || student.birthDate;
    student.gender = editedGender || student.gender;
    student.studentType = editedType || student.studentType;

    if (originalStudentId !== editedId) {
        SD_RECORDS[editedId] = SD_RECORDS[originalStudentId] || [];
        delete SD_RECORDS[originalStudentId];
        SD_SELECTED_STUDENT_ID = editedId;
    }

    sdCloseEditModal();
    renderSdStudents();

    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Student updated successfully.', 'success');
    }
}

function sdOpenDeleteModal(studentId) {
    var student = SD_STUDENTS.find(function (item) { return item.studentId === studentId; });
    if (!student) return;

    SD_PENDING_DELETE_ID = studentId;
    document.getElementById('sdDeleteText').textContent = 'Are you sure you want to delete "' + student.name + '"?';
    document.getElementById('sdDeleteStudentModal').style.display = 'flex';
}

function sdCloseDeleteModal() {
    SD_PENDING_DELETE_ID = null;
    document.getElementById('sdDeleteStudentModal').style.display = 'none';
}

function sdConfirmDeleteStudent() {
    var studentId = SD_PENDING_DELETE_ID;
    if (!studentId) return;

    SD_STUDENTS = SD_STUDENTS.filter(function (item) {
        return item.studentId !== studentId;
    });

    if (SD_RECORDS[studentId]) {
        delete SD_RECORDS[studentId];
    }

    if (SD_SELECTED_STUDENT_ID === studentId) {
        sdBackToList();
    }

    sdCloseDeleteModal();
    renderSdStudents();

    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Student removed successfully.', 'success');
    }
}

function sdCloseRecordModal() {
    var modal = document.getElementById('sdRecordModal');
    if (!modal) return;
    modal.style.display = 'none';
}

function sdResetModalFields() {
    var today = new Date();
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var day = String(today.getDate()).padStart(2, '0');
    var dateValue = today.getFullYear() + '-' + month + '-' + day;

    document.getElementById('sdIncidentDate').value = dateValue;
    document.getElementById('sdWalkIn').checked = false;
    document.getElementById('sdCaseType').value = '';
    document.getElementById('sdCalledBy').value = '';
    document.getElementById('sdDescription').value = '';

    document.getElementById('sdActionDate').value = dateValue;
    document.getElementById('sdCompleted').checked = false;
    document.getElementById('sdActionType').value = '';
    document.getElementById('sdCounselor').value = '';
    document.getElementById('sdRemarks').value = '';
}

function sdSaveRecord() {
    if (!SD_SELECTED_STUDENT_ID) return;

    var incidentDate = document.getElementById('sdIncidentDate').value;
    var walkIn = document.getElementById('sdWalkIn').checked;
    var caseType = document.getElementById('sdCaseType').value;
    var calledBy = (document.getElementById('sdCalledBy').value || '').trim();
    var description = (document.getElementById('sdDescription').value || '').trim();
    var actionDate = document.getElementById('sdActionDate').value;
    var actionType = (document.getElementById('sdActionType').value || '').trim();
    var counselor = (document.getElementById('sdCounselor').value || '').trim();
    var remarks = (document.getElementById('sdRemarks').value || '').trim();
    var completed = document.getElementById('sdCompleted').checked;

    if (!incidentDate) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Incident Date is required.', 'warning');
        }
        return;
    }

    if (!caseType) {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Case Type is required.', 'warning');
        }
        return;
    }

    var record = {
        incidentDate: incidentDate,
        walkIn: walkIn,
        caseType: caseType,
        calledBy: calledBy,
        description: description,
        actionDate: actionDate,
        actionType: actionType,
        counselor: counselor,
        remarks: remarks,
        completed: completed
    };

    if (!SD_RECORDS[SD_SELECTED_STUDENT_ID]) {
        SD_RECORDS[SD_SELECTED_STUDENT_ID] = [];
    }

    if (SD_EDIT_RECORD_INDEX !== null) {
        SD_RECORDS[SD_SELECTED_STUDENT_ID][SD_EDIT_RECORD_INDEX] = record;
    } else {
        SD_RECORDS[SD_SELECTED_STUDENT_ID].push(record);
    }

    SD_EDIT_RECORD_INDEX = null;
    sdCloseRecordModal();
    renderSdConductTable();

    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Record saved successfully.', 'success');
    }
}

function sdPrintRecord() {
    window.print();
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Downloaded Successfully.', 'success');
    }
}

function formatInputDate(value) {
    if (!value) return '-';
    var parts = value.split('-');
    if (parts.length !== 3) return value;
    return parts[1] + '/' + parts[2] + '/' + parts[0];
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function jsEscape(value) {
    return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

document.addEventListener('DOMContentLoaded', function () {
    renderSdStudents();

    function closeActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
            menu.classList.remove('open');
            menu.classList.remove('drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.bottom = '';
        });
    }

    function toggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        closeActionMenus();
        if (isOpen) return;

        var rect = trigger.getBoundingClientRect();
        var spacing = 6;
        var menuWidth = menu.offsetWidth || 110;
        var spaceBelow = window.innerHeight - rect.bottom;

        var left = rect.right + spacing;
        if (left + menuWidth > window.innerWidth - spacing) {
            left = rect.left - menuWidth - spacing;
        }
        if (left < spacing) left = spacing;

        menu.style.left = left + 'px';

        if (spaceBelow < 120) {
            menu.classList.add('drop-up');
            menu.style.top = 'auto';
            menu.style.bottom = (window.innerHeight - rect.bottom) + 'px';
        } else {
            menu.style.top = rect.top + 'px';
            menu.style.bottom = 'auto';
        }

        menu.classList.add('open');
    }

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-sd-menu-toggle]');
        if (toggle) {
            event.stopPropagation();
            toggleActionMenu(toggle.getAttribute('data-sd-menu-toggle'), toggle);
            return;
        }

        var conductToggle = event.target.closest('[data-sd-conduct-menu-toggle]');
        if (conductToggle) {
            event.stopPropagation();
            toggleActionMenu(conductToggle.getAttribute('data-sd-conduct-menu-toggle'), conductToggle);
            return;
        }

        var actionBtn = event.target.closest('[data-sd-open-action]');
        if (actionBtn) {
            closeActionMenus();
            var action = actionBtn.getAttribute('data-sd-open-action');
            var studentId = actionBtn.getAttribute('data-sd-student-id');
            if (action === 'edit') {
                sdOpenEditModal(studentId);
            } else if (action === 'delete') {
                sdOpenDeleteModal(studentId);
            }
            return;
        }

        var conductActionBtn = event.target.closest('[data-sd-conduct-action]');
        if (conductActionBtn) {
            closeActionMenus();
            var conductAction = conductActionBtn.getAttribute('data-sd-conduct-action');
            var recordIndex = parseInt(conductActionBtn.getAttribute('data-sd-record-index'), 10);
            if (isNaN(recordIndex)) return;
            if (conductAction === 'edit') {
                sdOpenEditRecordModal(recordIndex);
            } else if (conductAction === 'delete') {
                sdOpenDeleteRecordModal(recordIndex);
            }
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });

    window.addEventListener('scroll', closeActionMenus, true);

    ['sdRecordModal', 'sdEditStudentModal', 'sdAddStudentModal', 'sdDeleteStudentModal', 'sdDeleteRecordModal'].forEach(function (id) {
        var overlay = document.getElementById(id);
        if (!overlay) return;
        overlay.addEventListener('click', function (event) {
            if (event.target !== overlay) return;
            if (id === 'sdRecordModal') sdCloseRecordModal();
            if (id === 'sdEditStudentModal') sdCloseEditModal();
            if (id === 'sdAddStudentModal') sdCloseAddStudentModal();
            if (id === 'sdDeleteStudentModal') sdCloseDeleteModal();
            if (id === 'sdDeleteRecordModal') sdCloseDeleteRecordModal();
        });
    });
});