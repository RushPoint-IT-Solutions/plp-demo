/* ── Clinic Record / Infirmary Log ── */

var CR_CURRENT_VIEW = 'list';
var CR_SELECTED_STUDENT = null;

/* ── Sample student data ── */
var CR_STUDENTS = [
    {
        id: 1,
        studentId: '2223A8137',
        name: 'Bares, Mark Jay',
        program: 'Bachelor Of Science in Computer Science',
        course: 'BSIT',
        yearLevel: 'Fourth',
        section: 'A',
        semester: 'First',
        schoolYear: '2025-2026',
        status: 'Old',
        age: 22,
        birthDate: '2004-02-25',
        religion: 'Roman Catholic',
        gender: 'Male',
        homeAddress: 'Test',
        birthplace: 'Test',
        contactNo: 'Test',
        mobileNo: '09xxxxxxxxx'
    },
    {
        id: 2,
        studentId: '2223A8139',
        name: 'Dela Cruz, Juan',
        program: 'Bachelor Of Science in Computer Science',
        course: 'BSIT',
        yearLevel: 'Fourth',
        section: 'A',
        semester: 'First',
        schoolYear: '2025-2026',
        status: 'Old',
        age: 21,
        birthDate: '2005-05-10',
        religion: 'Roman Catholic',
        gender: 'Male',
        homeAddress: 'Manila',
        birthplace: 'Manila',
        contactNo: '1234567',
        mobileNo: '09xxxxxxxxx'
    },
    {
        id: 3,
        studentId: '2223A8139',
        name: 'Austero, Andrea Jane',
        program: 'Bachelor Of Science in Computer Science',
        course: 'BSIT',
        yearLevel: 'Fourth',
        section: 'A',
        semester: 'First',
        schoolYear: '2025-2026',
        status: 'Old',
        age: 22,
        birthDate: '2004-08-15',
        religion: 'Christian',
        gender: 'Female',
        homeAddress: 'Pasig',
        birthplace: 'Pasig',
        contactNo: '7654321',
        mobileNo: '09xxxxxxxxx'
    },
    {
        id: 4,
        studentId: '2223A8140',
        name: 'Santos, Maria',
        program: 'Bachelor Of Science in Computer Science',
        course: 'BSIT',
        yearLevel: 'Fourth',
        section: 'A',
        semester: 'First',
        schoolYear: '2025-2026',
        status: 'Old',
        age: 21,
        birthDate: '2005-03-20',
        religion: 'Roman Catholic',
        gender: 'Female',
        homeAddress: 'Quezon City',
        birthplace: 'Quezon City',
        contactNo: '9876543',
        mobileNo: '09xxxxxxxxx'
    }
];

/* ── Default medical history rows ── */
var CR_MED_HISTORY = [
    { label: 'Medical', age: '', date: '', mxdx: '', hospital: '' },
    { label: 'Surgical', age: '', date: '', mxdx: '', hospital: '' },
    { label: 'Allergies', age: '', date: '', mxdx: '', hospital: '' },
    { label: 'Medication', age: '', date: '', mxdx: '', hospital: '' }
];

var CR_CONDITIONS = [
    { label: 'Sight', findings: '' },
    { label: 'Hearing', findings: '' },
    { label: 'Speech', findings: '' },
    { label: 'Others', findings: '' }
];

/* ════════════════════════════════════
   STUDENT LIST
   ════════════════════════════════════ */
function renderStudentList() {
    var tbody = document.getElementById('crTableBody');
    var search = document.getElementById('crSearch').value.toLowerCase();
    var course = document.getElementById('crCourse').value;
    var year = document.getElementById('crYearLevel').value;

    var filtered = CR_STUDENTS.filter(function(s) {
        var matchSearch = !search || s.name.toLowerCase().indexOf(search) > -1 || s.studentId.toLowerCase().indexOf(search) > -1;
        var matchCourse = !course || s.course === course;
        var matchYear = !year || s.yearLevel === year;
        return matchSearch && matchCourse && matchYear;
    });

    var html = '';
    for (var i = 0; i < filtered.length; i++) {
        var s = filtered[i];
        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td>' + s.studentId + '</td>';
        html += '<td><a href="javascript:void(0)" class="cr-name-link" onclick="selectStudent(' + s.id + ')">' + s.name + '</a></td>';
        html += '<td>' + s.program + '</td>';
        html += '</tr>';
    }
    tbody.innerHTML = html;
    document.getElementById('crTotalStudents').innerHTML = 'Total Students: &nbsp;<span style="color:#006837; font-weight:700;">' + filtered.length + '</span>';
}

function filterClinicList() {
    renderStudentList();
}

function selectStudent(id) {
    var student = CR_STUDENTS.find(function(s) { return s.id === id; });
    if (!student) return;
    CR_SELECTED_STUDENT = student;
    renderPersonalInfo();
    goToStep('personal');
}

/* ════════════════════════════════════
   PERSONAL INFORMATION
   ════════════════════════════════════ */
function renderPersonalInfo() {
    var s = CR_SELECTED_STUDENT;
    if (!s) return;
    var grid = document.getElementById('crPersonalGrid');
    var fields = [
        ['STUDENT NAME', s.name, 'STUDENT NO.', s.studentId],
        ['COURSE', s.program, 'YEAR LEVEL', s.yearLevel],
        ['STATUS', s.status, 'SECTION', s.section],
        ['SCHOOL YEAR', s.schoolYear, 'SEMESTER', s.semester],
        ['AGE', s.age, 'RELIGION', s.religion],
        ['BIRTH DATE', s.birthDate, 'GENDER', s.gender],
        ['HOME ADDRESS', s.homeAddress, 'BIRTHPLACE', s.birthplace],
        ['CONTACT NO.', s.contactNo, 'MOBILE NO.', s.mobileNo]
    ];
    var html = '';
    for (var i = 0; i < fields.length; i++) {
        html += '<div class="cr-info-row">';
        html += '<div class="cr-info-cell">';
        html += '<span class="cr-info-label">' + fields[i][0] + '</span>';
        html += '<span class="cr-info-value">: ' + fields[i][1] + '</span>';
        html += '</div>';
        html += '<div class="cr-info-cell">';
        html += '<span class="cr-info-label">' + fields[i][2] + '</span>';
        html += '<span class="cr-info-value">: ' + fields[i][3] + '</span>';
        html += '</div>';
        html += '</div>';
    }
    grid.innerHTML = html;
}

/* ════════════════════════════════════
   MEDICAL HISTORY
   ════════════════════════════════════ */
function renderMedHistory() {
    var container = document.getElementById('crMedHistoryRows');
    var html = '';
    for (var i = 0; i < CR_MED_HISTORY.length; i++) {
        var m = CR_MED_HISTORY[i];
        html += '<div class="cr-med-row" data-index="' + i + '">';
        html += '<div class="cr-med-col cr-med-col-item"><label class="cr-med-check"><input type="checkbox"> <strong>' + m.label + '</strong></label></div>';
        html += '<div class="cr-med-col"><input type="text" class="cr-med-input" placeholder="Age" value="' + m.age + '"></div>';
        html += '<div class="cr-med-col"><input type="text" class="cr-med-input" placeholder="dd/mm/yy" value="' + m.date + '"></div>';
        html += '<div class="cr-med-col"><input type="text" class="cr-med-input" placeholder="MX/DX" value="' + m.mxdx + '"></div>';
        html += '<div class="cr-med-col"><input type="text" class="cr-med-input" placeholder="Hospital/Clinic" value="' + m.hospital + '"></div>';
        html += '<div class="cr-med-col cr-med-col-actions">';
        html += '<button type="button" class="cr-med-btn cr-med-btn-add" onclick="addMedRow(' + i + ')" title="Add row">+</button>';
        html += '<button type="button" class="cr-med-btn cr-med-btn-del" onclick="removeMedRow(' + i + ')" title="Remove row">&times;</button>';
        html += '</div>';
        html += '</div>';
    }
    container.innerHTML = html;

    /* Conditions */
    var condContainer = document.getElementById('crConditionList');
    var condHtml = '';
    for (var c = 0; c < CR_CONDITIONS.length; c++) {
        var cond = CR_CONDITIONS[c];
        condHtml += '<div class="cr-cond-row">';
        condHtml += '<label class="cr-med-check"><input type="checkbox"> <strong>' + cond.label + '</strong></label>';
        condHtml += '<input type="text" class="cr-cond-input" placeholder="Findings..." value="' + cond.findings + '">';
        condHtml += '</div>';
    }
    condContainer.innerHTML = condHtml;
}

function addMedRow(afterIndex) {
    CR_MED_HISTORY.splice(afterIndex + 1, 0, { label: 'New Item', age: '', date: '', mxdx: '', hospital: '' });
    renderMedHistory();
}

function removeMedRow(index) {
    if (CR_MED_HISTORY.length <= 1) {
        showRegistrarToast('At least one row is required.', 'warning');
        return;
    }
    CR_MED_HISTORY.splice(index, 1);
    renderMedHistory();
}

/* ════════════════════════════════════
   STEP NAVIGATION
   ════════════════════════════════════ */
function goToStep(step) {
    document.getElementById('crListView').style.display = 'none';
    document.getElementById('crPersonalView').style.display = 'none';
    document.getElementById('crMedicalView').style.display = 'none';
    document.getElementById('crImmunizationView').style.display = 'none';

    if (step === 'list') {
        document.getElementById('crListView').style.display = 'block';
        CR_CURRENT_VIEW = 'list';
    } else if (step === 'personal') {
        document.getElementById('crPersonalView').style.display = 'block';
        CR_CURRENT_VIEW = 'personal';
    } else if (step === 'medical') {
        document.getElementById('crMedicalView').style.display = 'block';
        renderMedHistory();
        CR_CURRENT_VIEW = 'medical';
    } else if (step === 'immunization') {
        document.getElementById('crImmunizationView').style.display = 'block';
        CR_CURRENT_VIEW = 'immunization';
    }
    window.scrollTo(0, 0);
}

/* ════════════════════════════════════
   SUBMIT & PRINT
   ════════════════════════════════════ */
function handleSubmitClinic() {
    document.getElementById('crSuccessMsg').textContent = 'Infirmary record submitted successfully!';
    document.getElementById('crSuccessModal').style.display = 'flex';
}

function closeCrSuccessModal() {
    document.getElementById('crSuccessModal').style.display = 'none';
    goToStep('list');
}

function printRecords() {
    window.print();
}

/* ── Init ── */
renderStudentList();
