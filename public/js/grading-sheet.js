/* Grading Sheet Page – Midterm & Final only */

var GS_SECTIONS = [];
var GS_ALL_SECTIONS = [];
var GS_ACTIVE_SECTION_ID = null;
var GS_GRADE_MODAL_STATE = {
    phase: 'midterm',
    componentIndex: 0
};

var GS_PHASES = [
    { key: 'midterm', label: 'MIDTERM' },
    { key: 'final', label: 'FINAL' }
];

function gsSeedSections(serverSections) {
    if (serverSections && serverSections.length) {
        GS_SECTIONS = serverSections.map(function(sec, idx) {
            var students = (sec.students || []).map(function(st) {
                return {
                    id: st.id || 0,
                    studentNo: st.studentNo || '',
                    name: st.name || '',
                    fda: !!st.fda,
                    na: !!st.na,
                    midterm: st.midterm !== null && st.midterm !== undefined ? Number(st.midterm) : null,
                    final: st.final !== null && st.final !== undefined ? Number(st.final) : null,
                    cRating: st.cRating !== null && st.cRating !== undefined ? Number(st.cRating) : null,
                    fRating: st.fRating !== null && st.fRating !== undefined ? Number(st.fRating) : null,
                    status: st.status || '',
                    remarks: st.remarks || ''
                };
            });
            return {
                id: sec.id || (idx + 1),
                section: sec.section || '-',
                courseCode: sec.courseCode || '-',
                description: sec.description || '-',
                faculty: sec.faculty || '-',
                department: sec.department || '',
                schoolYear: sec.schoolYear || '',
                term: sec.term || '',
                program: sec.program || '',
                midterm: sec.midterm || '-',
                final: sec.final || '-',
                units: sec.units || '-',
                transmutationRules: sec.transmutationRules || [],
                approvedBy: sec.approvedBy || '-',
                courseFull: sec.courseFull || '-',
                schedule: sec.schedule || 'Room No. : TBA',
                status: sec.status || 'Submitted',
                statusCode: String(sec.statusCode || '').toUpperCase(),
                deanApprovedByName: sec.deanApprovedByName || '',
                deanApprovedAtIso: sec.deanApprovedAtIso || '',
                registrarFinalizedByName: sec.registrarFinalizedByName || '',
                registrarFinalizedAtIso: sec.registrarFinalizedAtIso || '',
                students: students
            };
        });
        GS_ALL_SECTIONS = GS_SECTIONS.slice();
        return;
    }

    // Dummy data
    var dummyStudents = [
        { studentNo: '202310078', name: 'ALFARO, WHIELY PAULO GARCIA', fda: false, na: false, midterm: 92.47, final: 91.50, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202211037', name: 'CIELO, MICA ELLA MATIENZO', fda: false, na: false, midterm: 85.17, final: 88.00, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310118', name: 'DE LEON, JON RICHARD CALMA', fda: false, na: false, midterm: 88.27, final: 90.25, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202110227', name: 'ESPARZA JR., DANILO MARING', fda: false, na: false, midterm: 88.13, final: 90.42, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202110243', name: 'DIZON, KEVIN LACANILAR', fda: false, na: false, midterm: 89.93, final: 90.42, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310194', name: 'FERNANDEZ, LIMARIE JEWEL MANULIT', fda: false, na: false, midterm: 91.88, final: 89.55, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202110066', name: 'FLORES, GIANNA ANGELIA GALVAN', fda: false, na: false, midterm: 92.87, final: 91.23, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202110169', name: 'GABRIEL, ARISHERINA REGEH SANTOS', fda: false, na: false, midterm: 91.50, final: 88.90, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202209832', name: 'LINSON, STRINON MATTHEW TESALONA', fda: false, na: false, midterm: 82.55, final: 93.92, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310245', name: 'MAMAD, JAMAICA MALLARI', fda: false, na: false, midterm: 93.21, final: 95.25, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310210', name: 'MAHARAT, JUSTINE TRICIA CRUZ', fda: false, na: false, midterm: 97.48, final: 95.25, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310310', name: 'MANDAR, ALWIN LOYOLA', fda: false, na: false, midterm: 90.57, final: 95.32, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310408', name: 'MEDINA, KYLE JANZEN CABRAL', fda: false, na: false, midterm: 90.03, final: 88.30, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310066', name: 'MENDOZA, TRISTAN JOHN CORTEZ', fda: false, na: false, midterm: 87.70, final: 95.70, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202210142', name: 'NUÑEZ, ARMHAMIDGE GARAPA', fda: false, na: false, midterm: 96.47, final: 96.25, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310124', name: 'SAMSON, ASHLEY JOKIS SANJOS', fda: false, na: false, midterm: 89.37, final: 90.15, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310405', name: 'TAVAS, JOSHUA CORDERO', fda: false, na: false, midterm: 91.03, final: 87.55, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310150', name: 'TOLENTINO, KRISTINE JADE CURA', fda: false, na: false, midterm: 96.43, final: 96.63, cRating: null, fRating: null, remarks: '' }
    ];

    GS_SECTIONS = [
        {
            id: 1, section: 'BSIM 3-A1 P19', courseCode: 'BMT2', description: 'STRATEGIC MANAGEMENT',
            faculty: 'GORDANCE, AIRA TOLET', midterm: '02/21/2026', final: '-',
            department: 'College of Information Technology',
            schoolYear: '2025-2026',
            term: 'Second',
            program: 'BSIM',
            approvedBy: 'Registrar', courseFull: 'STRATEGIC MANAGEMENT', schedule: 'Room No. : BLDG. 3-102',
            status: 'Submitted for Dean Review',
            students: dummyStudents
        },
        {
            id: 2, section: 'BSIT 4-A', courseCode: 'CAP102', description: 'CAPSTONE PROJECT AND RESEARCH 2',
            faculty: 'DIAZ, JONNEL MARK', midterm: '02/21/2025', final: '02/21/2026',
            department: 'College of Information Technology',
            schoolYear: '2025-2026',
            term: 'First',
            program: 'BSIT',
            approvedBy: 'Admin 1', courseFull: 'CAPSTONE PROJECT AND RESEARCH 2', schedule: 'Room No. : TBA',
            status: 'Submitted for Dean Review',
            students: dummyStudents.slice(0, 8)
        },
        {
            id: 3, section: 'BSCS 4-A', courseCode: 'CAP102', description: 'CAPSTONE PROJECT AND RESEARCH 2',
            faculty: 'DIAZ, JONNEL MARK', midterm: '02/21/2026', final: '02/21/2026',
            department: 'College of Computer Science',
            schoolYear: '2024-2025',
            term: 'Second',
            program: 'BSCS',
            approvedBy: 'Admin 1', courseFull: 'CAPSTONE PROJECT AND RESEARCH 2', schedule: 'Room No. : TBA',
            status: 'Submitted for Dean Review',
            students: dummyStudents.slice(0, 5)
        }
    ];
    GS_ALL_SECTIONS = GS_SECTIONS.slice();
}

function gsNormalizeFilterValue(value) {
    return String(value || '').trim().toLowerCase();
}

function gsSetSelectOptions(elementId, values, placeholder) {
    var select = document.getElementById(elementId);
    if (!select) return;

    var unique = [];
    var seen = {};
    for (var i = 0; i < values.length; i++) {
        var value = String(values[i] || '').trim();
        if (!value || seen[gsNormalizeFilterValue(value)]) continue;
        seen[gsNormalizeFilterValue(value)] = true;
        unique.push(value);
    }
    unique.sort(function (a, b) { return a.localeCompare(b); });

    var current = select.value;
    var keepAll = '';
    if (select.options && select.options.length && select.options[0] && select.options[0].value === '') {
        keepAll = select.options[0].text;
    }
    var html = '<option value="">' + keepAll + '</option>';

    for (var j = 0; j < unique.length; j++) {
        var optionValue = unique[j];
        html += '<option value="' + gsEscapeHtml(optionValue) + '">' + gsEscapeHtml(optionValue) + '</option>';
    }

    select.innerHTML = html;
    if (current && seen[gsNormalizeFilterValue(current)]) {
        select.value = current;
    }
}

function gsApplySectionFilters() {
    var deptSelect = document.getElementById('gsDept');
    var aySelect = document.getElementById('gsAY');
    var termSelect = document.getElementById('gsTerm');
    var facultySelect = document.getElementById('gsFaculty');
    var sectionSelect = document.getElementById('gsSection');
    var programSelect = document.getElementById('gsProgram');
    var statusSelect = document.getElementById('gsStatus');

    var dept = gsNormalizeFilterValue(deptSelect ? deptSelect.value : '');
    var ay = gsNormalizeFilterValue(aySelect ? aySelect.value : '');
    var term = gsNormalizeFilterValue(termSelect ? termSelect.value : '');
    var faculty = gsNormalizeFilterValue(facultySelect ? facultySelect.value : '');
    var section = gsNormalizeFilterValue(sectionSelect ? sectionSelect.value : '');
    var program = gsNormalizeFilterValue(programSelect ? programSelect.value : '');
    var status = gsNormalizeFilterValue(statusSelect ? statusSelect.value : '');

    GS_SECTIONS = GS_ALL_SECTIONS.filter(function (sectionObj) {
        if (dept && gsNormalizeFilterValue(sectionObj.department) !== dept) return false;
        if (ay && gsNormalizeFilterValue(sectionObj.schoolYear) !== ay) return false;
        if (term && gsNormalizeFilterValue(sectionObj.term) !== term) return false;
        if (faculty && gsNormalizeFilterValue(sectionObj.faculty) !== faculty) return false;
        if (section && gsNormalizeFilterValue(sectionObj.section) !== section) return false;
        if (program && gsNormalizeFilterValue(sectionObj.program) !== program) return false;
        if (status && gsNormalizeFilterValue(sectionObj.status) !== status) return false;
        return true;
    });
    GS_ACTIVE_SECTION_ID = null;
    return GS_SECTIONS;
}

function gsPopulateFilterOptions() {
    gsSetSelectOptions('gsDept', GS_ALL_SECTIONS.map(function (sec) { return sec.department || ''; }), 'Department');
    gsSetSelectOptions('gsAY', GS_ALL_SECTIONS.map(function (sec) { return sec.schoolYear || ''; }), 'Academic Year');
    gsSetSelectOptions('gsTerm', GS_ALL_SECTIONS.map(function (sec) { return sec.term || ''; }), 'Term');
    gsSetSelectOptions('gsProgram', GS_ALL_SECTIONS.map(function (sec) { return sec.program || ''; }), '* Leave this blank to view all program');

    if (document.getElementById('gsFaculty')) {
        var selected = document.getElementById('gsFaculty').value;
        gsSetSelectOptions('gsFaculty', GS_ALL_SECTIONS.map(function (sec) { return sec.faculty || ''; }), 'Faculty');
        if (selected && selected !== '') {
            document.getElementById('gsFaculty').value = selected;
        }
    }

    if (document.getElementById('gsSection')) {
        var current = document.getElementById('gsSection').value;
        gsSetSelectOptions('gsSection', GS_ALL_SECTIONS.map(function (sec) { return sec.section || ''; }), '* Leave this blank to view all sections');
        if (current) {
            document.getElementById('gsSection').value = current;
        }
    }

    gsSetSelectOptions('gsStatus', [
        'Submitted for Dean Review',
        'Dean Approved',
        'Registrar Finalized',
        'Returned for Revision'
    ], 'Status');
}

function findSectionById(id) {
    for (var i = 0; i < GS_SECTIONS.length; i++) {
        if (String(GS_SECTIONS[i].id) === String(id)) return GS_SECTIONS[i];
    }
    return null;
}


function gsEscapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (ch) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return map[ch];
    });
}

function gsGetCsrf() {
    return (document.querySelector('meta[name="csrf-token"]') || {}).content
        || (document.querySelector('input[name="_token"]') || {}).value
        || '';
}

/* ─── Report of Grade printing (mirrors faculty grading sheet print) ─────── */

function gsFormatGradeNumber(value) {
    var num = parseFloat(value);
    if (isNaN(num)) return '';
    return (Math.round(num * 100) / 100).toFixed(2);
}

function gsComputeRawAverage(midterm, finalGrade) {
    var midtermNum = parseFloat(midterm);
    var finalNum = parseFloat(finalGrade);
    if (isNaN(midtermNum) || isNaN(finalNum)) return '';
    return (Math.round(((midtermNum + finalNum) / 2) * 100) / 100).toFixed(2);
}

function gsComputeEquivalentGrade(sec, midterm, finalGrade) {
    var rawAverage = gsComputeRawAverage(midterm, finalGrade);
    var rawNum = parseFloat(rawAverage);
    if (isNaN(rawNum)) return '';

    var rules = (sec && sec.transmutationRules) ? sec.transmutationRules : [];
    for (var i = 0; i < rules.length; i += 1) {
        var from = parseFloat(rules[i].from);
        var to = parseFloat(rules[i].to);
        if (!isNaN(from) && !isNaN(to) && rawNum >= from && rawNum <= to) {
            return gsFormatGradeNumber(rules[i].grade);
        }
    }

    return gsFormatGradeNumber(rawNum);
}

function gsFormatReportSemester(value) {
    var text = String(value || '').trim();
    var lower = text.toLowerCase();
    if (lower.indexOf('first') !== -1 || lower.indexOf('1st') !== -1) return '1ST SEMESTER';
    if (lower.indexOf('second') !== -1 || lower.indexOf('2nd') !== -1) return '2ND SEMESTER';
    if (lower.indexOf('summer') !== -1) return 'SUMMER';
    return text ? text.toUpperCase() : '';
}

function gsReportAcadStat(student) {
    var status = String(student.status || '').toUpperCase();
    if (['INC', 'UD', 'OD', 'NA', 'GNA'].indexOf(status) !== -1) return status;
    return '';
}

function gsReportRemarks(student, equivalent, average, isMidterm) {
    if (isMidterm) return '';
    var specialCode = gsReportAcadStat(student);
    if (['INC', 'UD', 'OD', 'NA', 'GNA'].indexOf(specialCode) !== -1) return specialCode;
    var explicit = String(student.remarks || '').trim();
    if (explicit) return explicit.toUpperCase();
    var equivalentNum = parseFloat(equivalent);
    var averageNum = parseFloat(average);
    if (!isNaN(equivalentNum)) return equivalentNum <= 3 ? 'PASSED' : 'FAILED';
    if (!isNaN(averageNum)) return averageNum >= 74.5 ? 'PASSED' : 'FAILED';
    return '';
}

function gsPrintGradeList(phase) {
    var sec = findSectionById(GS_ACTIVE_SECTION_ID);
    if (!sec) return;

    var isMidterm = phase === 'midterm';
    var students = (sec.students || []).slice();
    var now = new Date();
    var printedDate = now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    var semesterLabel = gsFormatReportSemester(sec.term);
    var schoolYearTerm = [sec.schoolYear || '-', semesterLabel].filter(Boolean).join(' / ');
    var professorName = sec.faculty || '-';
    var description = sec.description || '-';

    var rows = students.map(function (student, index) {
        var midterm = student.midterm !== null && student.midterm !== undefined ? student.midterm : '';
        var finalGrade = isMidterm ? '' : (student.final !== null && student.final !== undefined ? student.final : '');
        var average = isMidterm ? '' : (student.cRating || gsComputeRawAverage(student.midterm, student.final));
        var equivalent = isMidterm ? '' : gsComputeEquivalentGrade(sec, student.midterm, student.final);
        var acadStat = gsReportAcadStat(student);
        var remarks = gsReportRemarks(student, equivalent, average, isMidterm);

        return '<tr>'
            + '<td class="num">' + (index + 1) + '.</td>'
            + '<td class="name">' + gsEscapeHtml(student.name || '-') + '</td>'
            + '<td>' + gsEscapeHtml(sec.section || '-') + '</td>'
            + '<td>' + gsEscapeHtml(acadStat) + '</td>'
            + '<td>' + gsEscapeHtml(midterm || '') + '</td>'
            + '<td>' + gsEscapeHtml(finalGrade || '') + '</td>'
            + '<td>' + gsEscapeHtml(average || '') + '</td>'
            + '<td>' + gsEscapeHtml(equivalent || '') + '</td>'
            + '<td>' + gsEscapeHtml(remarks || '') + '</td>'
            + '</tr>';
    }).join('');

    var gradeLegendRows = [
        ['97.5 - 100', '1.00', 'PASSED'],
        ['94.5 - 97.4', '1.25', 'PASSED'],
        ['91.5-94.4', '1.50', 'PASSED'],
        ['88.5 - 91.4', '1.75', 'PASSED'],
        ['85.5 - 88.4', '2.00', 'PASSED'],
        ['82.5 - 85.4', '2.25', 'PASSED'],
        ['79.5 - 82.4', '2.50', 'PASSED'],
        ['76.5 - 79.4', '2.75', 'PASSED'],
        ['74.5 76.4', '3.00', 'PASSED'],
        ['BELOW 74.4', '5.00', 'FAILED']
    ].map(function (row) {
        return '<tr><td>' + row[0] + '</td><td>' + row[1] + '</td><td>' + row[2] + '</td></tr>';
    }).join('');

    var remarksLegendRows = [
        ['INC', 'INCOMPLETE'],
        ['UD', 'UNOFFICIALLY DROPPED'],
        ['OD', 'OFFICIALLY DROPPED'],
        ['NA', 'NOT ATTENDING'],
        ['GNA', 'GRADE NOT AVAILABLE']
    ].map(function (row) {
        return '<tr><td>' + row[0] + '</td><td>' + row[1] + '</td></tr>';
    }).join('');

    var html = '<!doctype html><html><head><meta charset="utf-8"><title>Report of Grade</title>'
        + '<style>'
        + '@page{size:letter portrait;margin:0.28in;}'
        + '*{box-sizing:border-box;}'
        + 'body{font-family:Arial,Helvetica,sans-serif;color:#000;margin:0;font-size:10px;line-height:1.18;}'
        + '.rog-page{width:100%;}'
        + '.rog-head{text-align:center;margin:0 0 28px;}'
        + '.rog-school{font-size:14px;font-weight:800;text-transform:uppercase;margin-top:8px;}'
        + '.rog-city{font-size:12px;font-weight:800;margin-top:5px;}'
        + '.rog-address{font-size:10px;font-weight:700;margin-top:5px;}'
        + '.rog-office{font-size:20px;font-weight:900;letter-spacing:.02em;margin-top:16px;}'
        + '.rog-title{font-size:15px;font-weight:900;margin-top:6px;}'
        + '.rog-meta{display:grid;grid-template-columns:1fr 1fr;column-gap:78px;row-gap:14px;margin-bottom:28px;}'
        + '.rog-meta-row{display:grid;grid-template-columns:112px 1fr;align-items:start;font-size:11px;}'
        + '.rog-meta-label{font-weight:800;}'
        + '.rog-main{width:100%;border-collapse:collapse;table-layout:fixed;font-size:7.2px;}'
        + '.rog-main th,.rog-main td{border:2px solid #000;padding:1px 5px;text-align:center;vertical-align:middle;}'
        + '.rog-main th{height:29px;font-size:10px;font-weight:900;}'
        + '.rog-main td{height:12px;}'
        + '.rog-main .num{width:44px;text-align:left;}'
        + '.rog-main .name{text-align:left;padding-left:8px;}'
        + '.rog-bottom{display:grid;grid-template-columns:43% 28% 1fr;gap:2px 10px;margin-top:8px;align-items:start;}'
        + '.rog-legend{width:100%;border-collapse:collapse;font-size:10px;}'
        + '.rog-legend th,.rog-legend td{border:2px solid #000;padding:3px 7px;text-align:center;}'
        + '.rog-legend th{font-size:11px;font-weight:900;}'
        + '.rog-release{padding:22px 10px 0;font-size:10px;}'
        + '.rog-release div{margin-bottom:16px;}'
        + '.rog-release strong{display:block;margin-top:4px;}'
        + '.rog-sign{padding-left:0;font-size:10px;text-align:center;}'
        + '.rog-sign-line{border-top:2px solid #000;margin-top:22px;padding-top:7px;}'
        + '.rog-sign-name{font-weight:900;margin-bottom:20px;}'
        + '.rog-sign-date{font-weight:900;margin:20px 0 0;}'
        + '.rog-page-no{margin:18px 0 32px;}'
        + '.rog-chair{border-top:2px solid #000;padding-top:7px;}'
        + '@media print{button{display:none;}}'
        + '</style></head><body>'
        + '<div class="rog-page">'
        + '<div class="rog-head">'
        + '<div class="rog-school">PAMANTASAN NG LUNGSOD NG PASIG</div>'
        + '<div class="rog-city">(University of Pasig City)</div>'
        + '<div class="rog-address">Alcalde Jose Street, Kapasigan, Pasig City</div>'
        + '<div class="rog-office">OFFICE OF THE REGISTRAR</div>'
        + '<div class="rog-title">REPORT OF GRADE</div>'
        + '</div>'
        + '<div class="rog-meta">'
        + '<div class="rog-meta-row"><span class="rog-meta-label">Prof. Name :</span><span>' + gsEscapeHtml(professorName) + '</span></div>'
        + '<div class="rog-meta-row"><span class="rog-meta-label">School Year:</span><span>' + gsEscapeHtml(schoolYearTerm) + '</span></div>'
        + '<div class="rog-meta-row"><span class="rog-meta-label">Subject Code:</span><span>' + gsEscapeHtml(sec.courseCode || '-') + '</span></div>'
        + '<div class="rog-meta-row"><span class="rog-meta-label">Course YrSec:</span><span>' + gsEscapeHtml(sec.section || '-') + '</span></div>'
        + '<div class="rog-meta-row"><span class="rog-meta-label">Description Title:</span><span>' + gsEscapeHtml(description) + '</span></div>'
        + '<div class="rog-meta-row"><span class="rog-meta-label">Units:</span><span>' + gsEscapeHtml(sec.units || '-') + '</span></div>'
        + '</div>'
        + '<table class="rog-main"><colgroup><col style="width:5.4%;"><col style="width:30.8%;"><col style="width:10%;"><col style="width:8.5%;"><col style="width:7.4%;"><col style="width:8.4%;"><col style="width:8.4%;"><col style="width:8.4%;"><col style="width:12.7%;"></colgroup>'
        + '<thead><tr><th></th><th>Student Name</th><th>CYS</th><th>AcadStat</th><th>MidGrd</th><th>FinGrd</th><th>SemGrd</th><th>Pt Eqv</th><th>Remarks</th></tr></thead>'
        + '<tbody>' + (rows || '<tr><td colspan="9">No students found.</td></tr>') + '</tbody></table>'
        + '<div class="rog-bottom">'
        + '<table class="rog-legend"><thead><tr><th>Grades</th><th>Pt. Equivalent</th><th>Remarks</th></tr></thead><tbody>' + gradeLegendRows + '</tbody></table>'
        + '<div><table class="rog-legend"><thead><tr><th>REMARKS</th><th>DESCRIPTION</th></tr></thead><tbody>' + remarksLegendRows + '</tbody></table>'
        + '<div class="rog-release"><div>Released By:</div><div><strong>Date Printed:</strong></div><strong>Ms. Jay Anne I. Santos</strong></div></div>'
        + '<div class="rog-sign">'
        + '<div class="rog-sign-line rog-sign-name">' + gsEscapeHtml(professorName) + '</div>'
        + '<div>Prof./Instructor</div>'
        + '<div class="rog-sign-line rog-sign-date">' + gsEscapeHtml(printedDate) + '</div>'
        + '<div>Date Submitted:</div>'
        + '<div class="rog-page-no">Page 1 of 1</div>'
        + '<div class="rog-chair">Dean / Department Chair</div>'
        + '</div></div></div>'
        + '<script>window.onload=function(){window.print();};<\/script>'
        + '</body></html>';

    var printWindow = window.open('', '_blank');
    if (!printWindow) {
        Swal.fire({
            title: 'Popup Blocked',
            text: 'Please allow popups to print the grade list.',
            icon: 'warning',
            confirmButtonColor: '#15803d',
        });
        return;
    }
    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
}

function gsStatusBadge(status) {
    var label = status || 'Submitted for Dean Review';
    var normalized = String(label).toLowerCase();
    var className = 'gs-badge-submitted';
    if (normalized.indexOf('approved') !== -1 || normalized.indexOf('finalized') !== -1) className = 'gs-badge-approved';
    if (normalized.indexOf('returned') !== -1 || normalized.indexOf('rejected') !== -1) className = 'gs-badge-rejected';
    return '<span class="gs-badge ' + className + '">' + gsEscapeHtml(label) + '</span>';
}

function gsNowLocalInputValue() {
    var d = new Date();
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
}

function gsEditApprovalButton(sec) {
    var statusCode = String(sec.statusCode || '').toUpperCase();
    if (statusCode !== 'DEAN_APPROVED' && statusCode !== 'REGISTRAR_FINALIZED') return '';

    var stage = statusCode === 'REGISTRAR_FINALIZED' ? 'registrar' : 'dean';
    var name = stage === 'registrar' ? sec.registrarFinalizedByName : sec.deanApprovedByName;
    var iso = stage === 'registrar' ? sec.registrarFinalizedAtIso : sec.deanApprovedAtIso;

    return '<button type="button" class="gs-edit-approval-btn" title="Edit approver / date approved" '
        + 'data-id="' + sec.id + '" '
        + 'data-stage="' + stage + '" '
        + 'data-approver="' + gsEscapeHtml(name || '') + '" '
        + 'data-approved-at="' + gsEscapeHtml(iso || gsNowLocalInputValue()) + '">'
        + '&#9998;'
        + '</button>';
}

function gsActionButtons(sec) {
    var statusCode = String(sec.statusCode || '').toUpperCase();
    if (statusCode === 'DEAN_APPROVED') {
        return '<div class="gs-action-btns">'
            + '<button type="button" class="gs-btn-approve" data-gs-action="finalized" '
            +   'data-id="' + sec.id + '" '
            +   'data-name="' + gsEscapeHtml(sec.description) + '" '
            +   'data-section="' + gsEscapeHtml(sec.section) + '" '
            +   'data-faculty="' + gsEscapeHtml(sec.faculty) + '">'
            +   'Registrar Finalize'
            + '</button>'
            + '</div>';
    }
    if (statusCode === 'REGISTRAR_FINALIZED' || statusCode === 'REJECTED') {
        return '<span class="text-muted">-</span>';
    }

    return '<div class="gs-action-btns">'
        + '<button type="button" class="gs-btn-approve" data-gs-action="dean_approved" '
        +   'data-id="' + sec.id + '" '
        +   'data-name="' + gsEscapeHtml(sec.description) + '" '
        +   'data-section="' + gsEscapeHtml(sec.section) + '" '
        +   'data-faculty="' + gsEscapeHtml(sec.faculty) + '">'
        +   'Dean Approve'
        + '</button>'
        + '<button type="button" class="gs-btn-reject" data-gs-action="rejected" '
        +   'data-id="' + sec.id + '" '
        +   'data-name="' + gsEscapeHtml(sec.description) + '" '
        +   'data-section="' + gsEscapeHtml(sec.section) + '" '
        +   'data-faculty="' + gsEscapeHtml(sec.faculty) + '">'
        +   'Reject'
        + '</button>'
        + '</div>';
}


function renderSectionList() {
    var tbody = document.getElementById('gsListBody');
    if (!tbody) return;

    var html = '';
    for (var i = 0; i < GS_SECTIONS.length; i++) {
        var s = GS_SECTIONS[i];
        html += '<tr class="gs-section-row" data-section-id="' + s.id + '" style="cursor:pointer;">' +
            '<td>' + (i + 1) + '</td>' +
            '<td class="gs-section-link">' + s.section + '</td>' +
            '<td>' + s.courseCode + '</td>' +
            '<td>' + s.description + '</td>' +
            '<td>' + s.faculty + '</td>' +
            '<td class="gs-date-cell">' + s.midterm + '</td>' +
            '<td class="gs-date-cell">' + s.final + '</td>' +
            '<td>' + s.approvedBy + gsEditApprovalButton(s) + '</td>' +
            '<td>' + gsStatusBadge(s.status) + '</td>' +
            '<td>' + gsActionButtons(s) + '</td>' +
        '</tr>';
    }
    tbody.innerHTML = html;

    var pageInfo = document.getElementById('gsListPageInfo');
    if (pageInfo) pageInfo.textContent = 'Showing ' + GS_SECTIONS.length + ' sections';
}

function gsComputeFinalResult(student) {
    if (student.cRating !== null && student.cRating !== undefined && !isNaN(Number(student.cRating))) {
        return Number(student.cRating);
    }
    if (student.midterm === null || student.midterm === undefined || isNaN(Number(student.midterm))) {
        return null;
    }
    if (student.final === null || student.final === undefined || isNaN(Number(student.final))) {
        return null;
    }
    return (Number(student.midterm) + Number(student.final)) / 2;
}

function gsGetStatus(finalResult, explicitStatus) {
    if (explicitStatus) return explicitStatus;
    if (finalResult === null) return '';
    return finalResult >= 75 ? 'Passed' : 'Failed';
}

function gsFormatGrade(v) {
    if (v === null || v === undefined) return '<span class="gs-grade-na">N/A</span>';
    return Number(v).toFixed(2);
}

function showDetailView(id) {
    var sec = findSectionById(id);
    if (!sec) return;
    GS_ACTIVE_SECTION_ID = sec.id;

    document.getElementById('gsBannerSection').textContent = sec.section;
    document.getElementById('gsBannerCourse').textContent = sec.courseFull;
    document.getElementById('gsBannerProf').textContent = sec.faculty;
    document.getElementById('gsBannerSched').textContent = sec.schedule;

    var detailBody = document.getElementById('gsDetailBody');
    var html = '';
    for (var i = 0; i < sec.students.length; i++) {
        var st = sec.students[i];
        var finalResult = gsComputeFinalResult(st);
        var status = gsGetStatus(finalResult, st.status);
        var remarks = st.remarks || status;
        var statusClass = status === 'Passed' ? 'gs-remarks-passed' : (status === 'Failed' ? 'gs-remarks-failed' : '');

        html += '<tr>' +
            '<td class="gs-col-num">' + (i + 1) + '</td>' +
            '<td class="gs-col-studno">' + st.studentNo + '</td>' +
            '<td class="gs-col-name">' + st.name + '</td>' +
            '<td class="gs-col-flag"><input type="checkbox" class="gs-checkbox"' + (st.fda ? ' checked' : '') + '></td>' +
            '<td class="gs-col-flag"><input type="checkbox" class="gs-checkbox"' + (st.na ? ' checked' : '') + '></td>' +
            '<td class="gs-col-grade gs-phase-cell" data-phase="midterm">' + gsFormatGrade(st.midterm) + '</td>' +
            '<td class="gs-col-grade gs-phase-cell" data-phase="final">' + gsFormatGrade(st.final) + '</td>' +
            '<td class="gs-col-grade">' + (finalResult !== null ? finalResult.toFixed(2) : '<span class="gs-grade-na">N/A</span>') + '</td>' +
            '<td class="gs-col-remarks ' + statusClass + '">' + status + '</td>' +
            '<td>' + gsEscapeHtml(remarks) + '</td>' +
        '</tr>';
    }
    detailBody.innerHTML = html;
    var detailPageInfo = document.getElementById('gsDetailPageInfo');
    if (detailPageInfo) detailPageInfo.textContent = 'Showing ' + sec.students.length + ' students';

    document.getElementById('gsListView').style.display = 'none';
    document.getElementById('gsDetailView').style.display = 'block';
}

function showListView() {
    closeGradeModal();
    document.getElementById('gsDetailView').style.display = 'none';
    document.getElementById('gsListView').style.display = 'block';
}

function handleViewList() {
    gsApplySectionFilters();
    renderSectionList();
    showListView();
}

/* Grading Components */
function getDefaultGradeComponents(phase) {
    return [
        { label: 'WRITTEN/SEATWORK', weight: 20 },
        { label: 'PERFORMANCE TASK', weight: 25 },
        { label: 'SUMMATIVE MAJOR EXAM', weight: 55 }
    ];
}

function buildGradeTabHtml(components, activeIndex) {
    var html = '';
    for (var i = 0; i < components.length; i++) {
        var c = components[i];
        html += '<button type="button" class="gs-grade-tab' + (i === activeIndex ? ' is-active' : '') + '" data-component-index="' + i + '">' +
            c.label + ' (' + c.weight + '%)</button>';
    }
    return html;
}

function buildGradeRowsHtml(students, phase, componentIndex) {
    var html = '';
    for (var i = 0; i < students.length; i++) {
        var st = students[i];
        var base = st[phase];
        if (base === null || base === undefined || isNaN(Number(base))) base = 0;
        else base = Number(base);

        var q1 = Math.max(0, Math.min(20, Math.round((base / 100) * 20)));
        var variance = ((i + componentIndex) % 3) - 1;
        var q2 = Math.max(0, Math.min(20, q1 + variance));
        var pe = ((q1 + q2) / 40) * 100;

        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + st.studentNo + '</td>' +
            '<td>' + st.name + '</td>' +
            '<td><input type="number" class="gs-grade-input" min="0" max="100" step="1" value="' + q1 + '"></td>' +
            '<td><input type="number" class="gs-grade-input" min="0" max="100" step="1" value="' + q2 + '"></td>' +
            '<td><input type="text" class="gs-grade-input gs-grade-input-readonly" value="' + pe.toFixed(2) + '" readonly></td>' +
        '</tr>';
    }
    return html;
}

function renderGradeModalContent() {
    var section = findSectionById(GS_ACTIVE_SECTION_ID);
    if (!section) return;

    var phase = GS_GRADE_MODAL_STATE.phase;
    var components = getDefaultGradeComponents(phase);
    var componentIndex = GS_GRADE_MODAL_STATE.componentIndex;

    if (componentIndex < 0 || componentIndex >= components.length) {
        componentIndex = 0;
        GS_GRADE_MODAL_STATE.componentIndex = 0;
    }

    var subtitle = document.getElementById('gsGradeModalSubtitle');
    var tabs = document.getElementById('gsGradeTabs');
    var tbody = document.getElementById('gsGradeModalBody');
    var q1Head = document.getElementById('gsGradeColQ1');
    var q2Head = document.getElementById('gsGradeColQ2');
    var phaseLabel = '';
    for (var p = 0; p < GS_PHASES.length; p++) {
        if (GS_PHASES[p].key === phase) { phaseLabel = GS_PHASES[p].label; break; }
    }

    if (subtitle) subtitle.textContent = section.section + ' - ' + section.courseCode + ' — ' + phaseLabel;
    if (tabs) tabs.innerHTML = buildGradeTabHtml(components, componentIndex);
    if (tbody) tbody.innerHTML = buildGradeRowsHtml(section.students, phase, componentIndex);

    if (q1Head) q1Head.textContent = 'Q1 (20)';
    if (q2Head) q2Head.textContent = 'Q2 (20)';

    bindGradeTabClick();
}

function openGradeModal(phase) {
    var modal = document.getElementById('gsGradeModal');
    if (!modal || !GS_ACTIVE_SECTION_ID) return;

    GS_GRADE_MODAL_STATE.phase = phase;
    GS_GRADE_MODAL_STATE.componentIndex = 0;
    renderGradeModalContent();

    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
}

function closeGradeModal() {
    var modal = document.getElementById('gsGradeModal');
    if (!modal) return;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

function bindGradeTabClick() {
    var tabsWrap = document.getElementById('gsGradeTabs');
    if (!tabsWrap) return;
    var buttons = tabsWrap.querySelectorAll('.gs-grade-tab');
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function(event) {
            var index = parseInt(event.currentTarget.getAttribute('data-component-index'), 10);
            if (!isNaN(index)) {
                GS_GRADE_MODAL_STATE.componentIndex = index;
                renderGradeModalContent();
            }
        });
    }
}

function bindGradeModalEvents() {
    var midtermHeader = document.getElementById('gsMidtermHeader');
    var finalHeader = document.getElementById('gsFinalHeader');

    if (midtermHeader) {
        midtermHeader.addEventListener('click', function() { openGradeModal('midterm'); });
    }
    if (finalHeader) {
        finalHeader.addEventListener('click', function() { openGradeModal('final'); });
    }

    var closeBtn = document.getElementById('gsGradeModalClose');
    if (closeBtn) closeBtn.addEventListener('click', closeGradeModal);

    var modal = document.getElementById('gsGradeModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) closeGradeModal();
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeGradeModal();
    });
}


function doGradingAction(subjectId, action, name, section, faculty, btn) {
    if (action === 'dean_approved' || action === 'finalized') {
        var isFinalize = action === 'finalized';
        Swal.fire({
            title: isFinalize ? 'Finalize Grading Sheet?' : 'Dean Approve Grading Sheet?',
            html: 'You are about to <strong>' + (isFinalize ? 'finalize' : 'approve') + '</strong> the grades submitted by:<br><br>'
                + '<strong>' + gsEscapeHtml(name) + '</strong><br>'
                + '<span style="color:#6b7280;font-size:0.9rem;">'
                + gsEscapeHtml(section) + ' &bull; ' + gsEscapeHtml(faculty)
                + '</span>'
                + '<div style="margin-top:14px;text-align:left;">'
                + '<label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">Approver Name</label>'
                + '<input id="swalApproverName" class="swal2-input" style="margin:0 0 10px;" '
                + 'value="' + gsEscapeHtml(window.GS_CURRENT_USER_NAME || '') + '">'
                + '<label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">Date Approved</label>'
                + '<input id="swalApprovedAt" type="datetime-local" class="swal2-input" style="margin:0;" '
                + 'value="' + gsNowLocalInputValue() + '">'
                + '</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#15803d',
            cancelButtonColor: '#6b7280',
            confirmButtonText: isFinalize ? 'Yes, Finalize' : 'Yes, Approve',
            cancelButtonText: 'Cancel',
            preConfirm: function () {
                var approverName = (document.getElementById('swalApproverName').value || '').trim();
                var approvedAt = document.getElementById('swalApprovedAt').value || '';
                if (!approverName) {
                    Swal.showValidationMessage('Approver name is required');
                    return false;
                }
                if (!approvedAt) {
                    Swal.showValidationMessage('Date approved is required');
                    return false;
                }
                return { approverName: approverName, approvedAt: approvedAt };
            },
        }).then(function (result) {
            if (!result.isConfirmed) return;
            gsPostAction(subjectId, action, '', name, section, btn, result.value.approverName, result.value.approvedAt);
        });

    } else {
        Swal.fire({
            title: 'Reject Grading Sheet?',
            html: 'The grades for <strong>' + gsEscapeHtml(name) + '</strong> '
                + '(<span style="color:#6b7280;">' + gsEscapeHtml(section) + '</span>) '
                + 'will be <strong>sent back</strong> to <strong>' + gsEscapeHtml(faculty)
                + '</strong> for revision.<br><br>'
                + '<label style="font-size:0.85rem;font-weight:600;display:block;text-align:left;margin-bottom:4px;">'
                + 'Reason (optional)</label>'
                + '<textarea id="swalRejectReason" class="swal2-textarea" '
                + 'placeholder="Enter reason for rejection..." style="font-size:0.88rem;"></textarea>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel',
            preConfirm: function () {
                return document.getElementById('swalRejectReason').value.trim();
            },
        }).then(function (result) {
            if (!result.isConfirmed) return;
            gsPostAction(subjectId, 'rejected', result.value || '', name, section, btn);
        });
    }
}

function gsPostAction(subjectId, action, remarks, name, section, btn, approverName, approvedAt) {
    btn.disabled = true;

    fetch(window.GS_ACTION_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': gsGetCsrf(),
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            subject_id: subjectId,
            action:     action,
            remarks:    remarks,
            approver_name: approverName || '',
            approved_at:   approvedAt || '',
        }),
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (!data.ok) {
            Swal.fire({
                title: 'Error',
                text: data.message || 'Action failed. Please try again.',
                icon: 'error',
                confirmButtonColor: '#15803d',
            });
            btn.disabled = false;
            return;
        }

        // Remove from local list — it's no longer Submitted
        GS_SECTIONS.forEach(function (s) {
            if (String(s.id) === String(subjectId)) {
                s.statusCode = String(data.status || '').toUpperCase();
                s.status = data.label || s.status;
                s.approvedBy = data.approvedBy || (action === 'rejected' ? 'Returned' : 'Updated');
                if (action === 'dean_approved') {
                    s.deanApprovedAt = data.approvedAt || '';
                    s.deanApprovedAtIso = data.approvedAtIso || '';
                    s.deanApprovedByName = data.approvedBy || '';
                }
                if (action === 'finalized') {
                    s.registrarFinalizedAt = data.approvedAt || '';
                    s.registrarFinalizedAtIso = data.approvedAtIso || '';
                    s.registrarFinalizedByName = data.approvedBy || '';
                }
            }
        });

        // Also sync window.GS_SERVER_SECTIONS if present
        if (window.GS_SERVER_SECTIONS) {
            window.GS_SERVER_SECTIONS.forEach(function (s) {
                if (String(s.id) === String(subjectId)) {
                    s.statusCode = String(data.status || '').toUpperCase();
                    s.status = data.label || s.status;
                }
            });
        }

        if (GS_ALL_SECTIONS && GS_ALL_SECTIONS.length) {
            GS_ALL_SECTIONS.forEach(function (s) {
                if (String(s.id) === String(subjectId)) {
                    s.statusCode = String(data.status || '').toUpperCase();
                    s.status = data.label || s.status;
                }
            });
        }

        // Re-render the list
        renderSectionList();

        var isApproved = action === 'dean_approved' || action === 'finalized';
        Swal.fire({
            title: action === 'finalized' ? 'Grades Finalized!' : (isApproved ? 'Grades Approved!' : 'Grades Rejected'),
            html: '<strong>' + gsEscapeHtml(name) + '</strong> '
                + '(<span style="color:#6b7280;">' + gsEscapeHtml(section) + '</span>)<br><br>'
                + (isApproved
                    ? (action === 'finalized' ? 'Grades have been <strong>finalized</strong> successfully.' : 'Grades have been <strong>approved</strong> successfully.')
                    : 'Grades have been <strong>sent back</strong> to faculty for revision.')
                + (isApproved
                    ? '<div style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;text-align:left;font-size:0.9rem;">'
                        + '<div><strong>Approved By:</strong> ' + gsEscapeHtml(data.approvedBy || '-') + '</div>'
                        + '<div><strong>Date Approved:</strong> ' + gsEscapeHtml(data.approvedAt || '-') + '</div>'
                        + '</div>'
                    : ''),
            icon: isApproved ? 'success' : 'info',
            confirmButtonColor: '#15803d',
            confirmButtonText: 'Done',
        });
    })
    .catch(function () {
        Swal.fire({
            title: 'Network Error',
            text: 'Something went wrong. Please try again.',
            icon: 'error',
            confirmButtonColor: '#15803d',
        });
        btn.disabled = false;
    });
}

function bindListBodyActions(listBody) {
    listBody.addEventListener('click', function (event) {

        var approveBtn = event.target.closest('.gs-btn-approve');
        if (approveBtn) {
            event.stopPropagation();
            doGradingAction(
                approveBtn.getAttribute('data-id'),
                approveBtn.getAttribute('data-gs-action') || 'dean_approved',
                approveBtn.getAttribute('data-name'),
                approveBtn.getAttribute('data-section'),
                approveBtn.getAttribute('data-faculty'),
                approveBtn
            );
            return;
        }

        var rejectBtn = event.target.closest('.gs-btn-reject');
        if (rejectBtn) {
            event.stopPropagation();
            doGradingAction(
                rejectBtn.getAttribute('data-id'),
                rejectBtn.getAttribute('data-gs-action') || 'rejected',
                rejectBtn.getAttribute('data-name'),
                rejectBtn.getAttribute('data-section'),
                rejectBtn.getAttribute('data-faculty'),
                rejectBtn
            );
            return;
        }

        var editBtn = event.target.closest('.gs-edit-approval-btn');
        if (editBtn) {
            event.stopPropagation();
            openApprovalEditModal(
                editBtn.getAttribute('data-id'),
                editBtn.getAttribute('data-stage'),
                editBtn.getAttribute('data-approver'),
                editBtn.getAttribute('data-approved-at')
            );
            return;
        }

        var row = event.target.closest('tr[data-section-id]');
        if (row && !event.target.closest('button')) {
            showDetailView(row.getAttribute('data-section-id'));
        }
    });
}

function openApprovalEditModal(subjectId, stage, currentName, currentAtIso) {
    Swal.fire({
        title: 'Edit Approval Details',
        html: '<div style="text-align:left;">'
            + '<label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">Approver Name</label>'
            + '<input id="swalEditApproverName" class="swal2-input" style="margin:0 0 10px;" value="' + gsEscapeHtml(currentName || '') + '">'
            + '<label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">Date Approved</label>'
            + '<input id="swalEditApprovedAt" type="datetime-local" class="swal2-input" style="margin:0;" value="' + gsEscapeHtml(currentAtIso || gsNowLocalInputValue()) + '">'
            + '</div>',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#15803d',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Save Changes',
        cancelButtonText: 'Cancel',
        preConfirm: function () {
            var nameVal = (document.getElementById('swalEditApproverName').value || '').trim();
            var dateVal = document.getElementById('swalEditApprovedAt').value || '';
            if (!nameVal) {
                Swal.showValidationMessage('Approver name is required');
                return false;
            }
            if (!dateVal) {
                Swal.showValidationMessage('Date approved is required');
                return false;
            }
            return { name: nameVal, date: dateVal };
        },
    }).then(function (result) {
        if (!result.isConfirmed) return;
        gsPostApprovalEdit(subjectId, stage, result.value.name, result.value.date);
    });
}

function gsPostApprovalEdit(subjectId, stage, approverName, approvedAt) {
    fetch(window.GS_UPDATE_APPROVAL_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': gsGetCsrf(),
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            subject_id: subjectId,
            stage: stage,
            approver_name: approverName,
            approved_at: approvedAt,
        }),
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (!data.ok) {
            Swal.fire({
                title: 'Error',
                text: data.message || 'Update failed. Please try again.',
                icon: 'error',
                confirmButtonColor: '#15803d',
            });
            return;
        }

        [GS_SECTIONS, GS_ALL_SECTIONS, window.GS_SERVER_SECTIONS].forEach(function (list) {
            if (!list) return;
            list.forEach(function (s) {
                if (String(s.id) !== String(subjectId)) return;
                if (stage === 'dean') {
                    s.deanApprovedByName = data.approvedBy || s.deanApprovedByName;
                    s.deanApprovedAt = data.approvedAt || s.deanApprovedAt;
                    s.deanApprovedAtIso = data.approvedAtIso || s.deanApprovedAtIso;
                } else {
                    s.registrarFinalizedByName = data.approvedBy || s.registrarFinalizedByName;
                    s.registrarFinalizedAt = data.approvedAt || s.registrarFinalizedAt;
                    s.registrarFinalizedAtIso = data.approvedAtIso || s.registrarFinalizedAtIso;
                }
                // "Current Owner" always reflects the latest stage reached (registrar overrides dean)
                s.approvedBy = s.registrarFinalizedByName || s.deanApprovedByName || s.approvedBy;
            });
        });

        renderSectionList();

        Swal.fire({
            title: 'Updated!',
            html: 'Approval details have been updated.<br><br>'
                + '<strong>Approved By:</strong> ' + gsEscapeHtml(data.approvedBy || '-') + '<br>'
                + '<strong>Date Approved:</strong> ' + gsEscapeHtml(data.approvedAt || '-'),
            icon: 'success',
            confirmButtonColor: '#15803d',
            confirmButtonText: 'Done',
        });
    })
    .catch(function () {
        Swal.fire({
            title: 'Network Error',
            text: 'Something went wrong. Please try again.',
            icon: 'error',
            confirmButtonColor: '#15803d',
        });
    });
}


function initGradingSheetPage() {
    var serverData = window.GS_SERVER_SECTIONS || null;
    gsSeedSections(serverData);
    gsPopulateFilterOptions();
    gsApplySectionFilters();
    renderSectionList();
    bindGradeModalEvents();

    var listBody = document.getElementById('gsListBody');
    if (listBody) {
        bindListBodyActions(listBody);
    }
}

window.showDetailView = showDetailView;
window.showListView = showListView;
window.handleViewList = handleViewList;
window.closeGradeModal = closeGradeModal;
window.openGradeModal = openGradeModal;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGradingSheetPage);
} else {
    initGradingSheetPage();
}
