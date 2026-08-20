/* official-grade-report.js — Official Grade Report form page logic */

var ogrCurrentRowId = null;
var ogrFilterInFlight = false;

function ogrEsc(v) {
    return String(v || '').replace(/[&<>"']/g, function(c) {
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
}

/* ── Demo subject data per student row ──────────────────── */
var ogrDemoSubjects = {
    '1': [
        { code:'GE 001', desc:'Understanding the Self', section:'BSIT 1A', prof:'Marra Mae F. Catapangan', grade:'1.25', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'GE 002', desc:'Readings in Philippine History with Indigenous Peoples Education', section:'BSIT 1A', prof:'Jefferson A. Victorino', grade:'1.50', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'GE 004', desc:'Mathematics in the Modern World', section:'BSIT 1A', prof:'Alberto A. Hobrero', grade:'1.25', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'NCM 100', desc:'Theoretical Foundations in Nursing', section:'BSIT 1A', prof:'Michelle E. Flores', grade:'1.75', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'NSTP 101-C', desc:'National Service Training Program (Civic Welfare Training Service)', section:'BSIT 1A', prof:'Jefferson A. Victorino', grade:'1.50', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'NUR 101A', desc:'Anatomy and Physiology', section:'BSIT 1A', prof:'Justin Remund D. Toole', grade:'1.50', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'NUR 101B', desc:'Anatomy and Physiology (102 hours)', section:'BSIT 1A', prof:'Maria Concepcion N. Paz', grade:'1.25', remarks:'PSD', reexam:'', units:'2.00' },
        { code:'NUR 102A', desc:'Biochemistry', section:'BSIT 1A', prof:'Charito P. Corsiga', grade:'1.50', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'NUR 102B', desc:'Biochemistry (102 hours)', section:'BSIT 1A', prof:'Charito P. Corsiga', grade:'1.75', remarks:'PSD', reexam:'', units:'2.00' },
        { code:'PE 101', desc:'Physical Activities Toward Health and Fitness 1 (PATHFit 1)', section:'BSIT 1A', prof:'Charito C. Areglo', grade:'1.25', remarks:'PSD', reexam:'', units:'2.00' }
    ],
    '2': [
        { code:'COMP 101', desc:'Introduction to Computing', section:'BSIT 1B', prof:'Allan A. Burgos', grade:'1.75', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'COMP 102', desc:'Fundamentals of Programming (C++)', section:'BSIT 1B', prof:'Marthea Andrea O. Daluyen', grade:'1.25', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'GE 003', desc:'The Contemporary World with Peace Education', section:'BSIT 1B', prof:'Jonathan T. Pascual', grade:'1.50', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'GE 004', desc:'Mathematics in the Modern World', section:'BSIT 1B', prof:'Alberto A. Hobrero', grade:'1.50', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'GE 006', desc:'Art Appreciation', section:'BSIT 1B', prof:'Lea S. Velasco', grade:'1.00', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'NSTP 101-C', desc:'National Service Training Program (Civic Welfare Training Service)', section:'BSIT 1B', prof:'Paulo O. Moita', grade:'1.00', remarks:'PSD', reexam:'', units:'3.00' },
        { code:'PE 101', desc:'Wellness and Fitness', section:'BSIT 1B', prof:'George L. Dacumos', grade:'1.25', remarks:'PSD', reexam:'', units:'2.00' }
    ]
};

var ogrDemoMeta = {
    '1': { studentNo:'25-00414', studentName:'ABENES, CRISTINE GRACE BERNALDEZ', address:'1011 VILLA MUNSOD 1, SAN JOAQUIN, PASIG CITY', birthday:'August 3, 2007', section:'BSN 1-BENNER', course:'BSN : BACHELOR OF SCIENCE IN NURSING', schoolYear:'2025-2026 / 1ST SEMESTER', curriculum:'2025', studentType:'NEW', yearLevel:'1', residency:'PR', cwa:'1.45' },
    '2': { studentNo:'21-00010', studentName:'CERADO, ROILEEN I.', address:'326 CAPTAIN HENRY JAVIER ST. ORANBO PASIG CITY', birthday:'JUN 29 2002 10:27AM', section:'BSIT 1B', course:'BSIT : BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY', schoolYear:'2021-2022 / 1ST SEMESTER', curriculum:'2021', studentType:'OLD', yearLevel:'4', residency:'PR', cwa:'1.40' }
};

if (window.ogrSubjectsByRow && typeof window.ogrSubjectsByRow === 'object') {
    ogrDemoSubjects = window.ogrSubjectsByRow;
}

if (window.ogrMetaByRow && typeof window.ogrMetaByRow === 'object') {
    ogrDemoMeta = window.ogrMetaByRow;
}

function ogrResolveCurriculum(meta) {
    var curriculumRaw = String((meta && meta.curriculum) || '').trim();
    var schoolYearRaw = String((meta && meta.schoolYear) || '').trim();

    if (curriculumRaw && curriculumRaw.toUpperCase() !== 'CURRENT') {
        return curriculumRaw;
    }

    var yearMatch = schoolYearRaw.match(/\b(20\d{2})\b/);
    if (yearMatch && yearMatch[1]) {
        return yearMatch[1];
    }

    return String(new Date().getFullYear());
}

function ogrGetFilterValue(selector) {
    var el = document.getElementById(selector);
    if (!el) return '';
    var value = String(el.value || '').trim();
    if (value === '' || value.toLowerCase() === 'all' || value.indexOf('Select') !== -1 || value.indexOf('ALL') === 0) {
        return '';
    }
    return value;
}

function ogrBuildFilterPayload() {
    return {
        school_year: ogrGetFilterValue('ogrSchoolYear'),
        semester: ogrGetFilterValue('ogrSemester'),
        program: ogrGetFilterValue('ogrProgram'),
        year_level: ogrGetFilterValue('ogrYearLevel'),
        section: ogrGetFilterValue('ogrSection'),
    };
}

function ogrApplyFilters() {
    var baseUrl = window.ogrFilterUrl;
    if (!baseUrl || ogrFilterInFlight) return;

    var payload = ogrBuildFilterPayload();
    var query = [];
    var keys = Object.keys(payload);
    for (var i = 0; i < keys.length; i++) {
        var key = keys[i];
        if (payload[key] !== '') {
            query.push(encodeURIComponent(key) + '=' + encodeURIComponent(payload[key]));
        }
    }

    ogrFilterInFlight = true;
    fetch(baseUrl + (query.length ? ('?' + query.join('&')) : ''), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
    })
    .then(function (response) {
        if (!response.ok) {
            throw new Error('Unable to fetch grade report rows.');
        }
        return response.json();
    })
    .then(function (data) {
        if (!data || !data.ok) {
            throw new Error((data && data.message) ? data.message : 'Unable to fetch grade report rows.');
        }
        ogrDemoSubjects = data.subjectsByRow || {};
        ogrDemoMeta = data.metaByRow || {};
        ogrRenderRows(Array.isArray(data.rows) ? data.rows : []);
    })
    .catch(function (err) {
        alert(err && err.message ? err.message : 'Unable to update filters. Please try again.');
    })
    .finally(function () {
        ogrFilterInFlight = false;
    });
}

function ogrRenderRows(rows) {
    var tbody = document.getElementById('ogrTableBody');
    if (!tbody) return;

    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#666;">No students available.</td></tr>';
        ogrSyncSelectAll();
        return;
    }

    var html = '';
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i] || {};
        var rowId = (row.row_id || (i + 1));
        html += '<tr data-row-id="' + rowId + '" data-student-id="' + (row.student_id || '') + '">' +
            '<td style="text-align: center;"><input type="checkbox" class="ogr-row-select" onchange="ogrSyncSelectAll()"></td>' +
            '<td>' + ogrEsc(row.student_no || '-') + '</td>' +
            '<td><button type="button" class="doc-link-btn" onclick="ogrOpenPreview(\'' + rowId + '\')">' + ogrEsc(row.student_name || '-') + '</button></td>' +
            '<td>' + ogrEsc(row.program || '-') + '</td>' +
            '<td>' + ogrEsc(row.year || '-') + '</td>' +
            '<td>' + ogrEsc(row.section || '-') + '</td>' +
            '<td style="text-align:center;">' +
                '<div class="apst-action-btn" data-ogr-menu-toggle="ogrMenu-' + rowId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
                '<div class="apst-dropdown" id="ogrMenu-' + rowId + '">' +
                    '<button type="button" onclick="ogrOpenEdit(\'' + rowId + '\')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>' +
                        'Edit' +
                    '</button>' +
                    '<button type="button" class="apst-del-btn" onclick="ogrOpenDelete(\'' + rowId + '\')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        'Delete' +
                    '</button>' +
                '</div>' +
            '</td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
    ogrSyncSelectAll();
}

function ogrBuildTemplate(data, subjects, meta) {
    var curriculumDisplay = ogrResolveCurriculum(meta);

    /* space at top for pre-printed header on yellow paper */
    var headerSpace = '<div class="ogr-header-space"></div>';

    /* CWA box upper-right */
    var cwaBox = '<div class="ogr-cwa-box">' +
        '<div class="ogr-cwa-label">Cumulative Weighted Average (CWA)</div>' +
        '<div class="ogr-cwa-value">' + ogrEsc(meta.cwa) + '</div>' +
        '<div class="ogr-cwa-note">PE and NSTP subjects are not computed</div>' +
        '</div>';

    /* Student info grid */
    var info = '<div class="ogr-info-grid">' +
        '<div class="ogr-info-col">' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Student No</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(data.studentNo) + '</span></div>' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Student Name</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(data.studentName) + '</span></div>' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Address</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(meta.address) + '</span></div>' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Birthday</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(meta.birthday) + '</span></div>' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Section</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(data.section) + '</span></div>' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Course</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(meta.course) + '</span></div>' +
        '</div>' +
        '<div class="ogr-info-col">' +
            '<div class="ogr-info-row"><span class="ogr-info-label">School Year</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(meta.schoolYear) + '</span></div>' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Curriculum</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(curriculumDisplay) + '</span></div>' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Student Type</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(meta.studentType) + '</span></div>' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Year Level</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(meta.yearLevel) + '</span></div>' +
            '<div class="ogr-info-row"><span class="ogr-info-label">Residency</span><span class="ogr-info-sep">:</span><span class="ogr-info-val">' + ogrEsc(meta.residency) + '</span></div>' +
        '</div>' +
    '</div>';

    /* Grades table */
    var tHead = '<tr>' +
        '<th>COURSE</th><th>Course DESCRIPTION</th><th>SECTION</th>' +
        '<th>PROFESSOR</th><th>SEM GRADE</th><th>REMARKS</th><th>RE-EXAM</th><th>UNITS EARNED</th>' +
    '</tr>';

    var tBody = '';
    for (var i = 0; i < subjects.length; i++) {
        var s = subjects[i];
        tBody += '<tr>' +
            '<td>' + ogrEsc(s.code) + '</td>' +
            '<td>' + ogrEsc(s.desc) + '</td>' +
            '<td>' + ogrEsc(s.section) + '</td>' +
            '<td>' + ogrEsc(s.prof) + '</td>' +
            '<td style="text-align:center;">' + ogrEsc(s.grade) + '</td>' +
            '<td style="text-align:center;">' + ogrEsc(s.remarks) + '</td>' +
            '<td style="text-align:center;">' + ogrEsc(s.reexam) + '</td>' +
            '<td style="text-align:center;">' + ogrEsc(s.units) + '</td>' +
        '</tr>';
    }

    var table = '<table class="ogr-grades-table" data-no-auto-pager="1"><thead>' + tHead + '</thead><tbody>' + tBody + '</tbody></table>';

    /* Footer */
    var now = new Date();
    var footer = '<div class="ogr-footer" style="gap: 2px;">' +
        '<div style="position:relative;">' +
            '<div style="position:absolute; left:0; top:0;"><span class="ogr-footer-label">ROG NO:</span></div>' +
            '<div style="text-align:center; font-weight: 700; font-size: 0.65rem; width:100%;">***PLP*PLP*PLP*PLP*PLP*PLP*PLP*PLP*PLP*PLP****NOTHING FOLLOWS****PLP*PLP*PLP*PLP*PLP*PLP*PLP*PLP*PLP***</div>' +
        '</div>' +
        '<div class="ogr-footer-row"><span class="ogr-footer-label" style="min-width: 90px;">Date Printed:</span><span class="ogr-footer-val">' + (now.getMonth()+1) + '/' + now.getDate() + '/' + now.getFullYear() + '</span></div>' +
        '<div class="ogr-footer-row"><span class="ogr-footer-label" style="min-width: 90px;">Printed By:</span><span class="ogr-footer-val">MS. Julie Ruth C. Malabanan</span></div>' +
    '</div>' +
    '<div class="ogr-registrar-sig" style="text-align:right; margin-top:28px; margin-right:30px;">' +
        '<div style="display:inline-block; text-align:center;">' +
            '<div class="ogr-sig-name" style="font-weight:700; font-size:0.85rem; border-bottom:1px solid #000; padding-bottom:2px; margin-bottom:2px; white-space:nowrap; text-decoration:none;">FEDERICO G. NUEVA, MT</div>' +
            '<div class="ogr-sig-title" style="font-size:0.65rem; white-space:nowrap;">PLP-UNIVERSITY REGISTRAR</div>' +
        '</div>' +
    '</div>';

    return headerSpace + cwaBox + info + table + footer;
}

function ogrGetRowData(rowId) {
    var row = ogrGetRow(rowId);
    if (!row) return null;
    var cells = row.querySelectorAll('td');
    return {
        studentNo: (cells[1] ? cells[1].textContent : '').trim(),
        studentName: (cells[2] ? cells[2].textContent : '').trim(),
        program: (cells[3] ? cells[3].textContent : '').trim(),
        year: (cells[4] ? cells[4].textContent : '').trim(),
        section: (cells[5] ? cells[5].textContent : '').trim()
    };
}

function ogrOpenPreview(rowId) {
    var data = ogrGetRowData(rowId);
    if (!data) return;

    var subjects = ogrDemoSubjects[String(rowId)] || ogrDemoSubjects['1'];
    var meta = ogrDemoMeta[String(rowId)] || ogrDemoMeta['1'];

    var row = ogrGetRow(rowId);
    var studentId = row ? row.getAttribute('data-student-id') : '';
    if ((!subjects || !meta) && studentId) {
        fetch('/registrar/forms/official-grade-report/' + encodeURIComponent(studentId) + '/data', {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
        .then(function (response) { return response.json(); })
        .then(function (payload) {
            if (payload && payload.ok && payload.subjects && payload.subjects.length) {
                var mapKey = String(rowId);
                ogrDemoSubjects[mapKey] = payload.subjects;
                subjects = payload.subjects;
                meta = ogrDemoMeta[mapKey] || ogrDemoMeta['1'] || {
                    address:'-', birthday:'-', section:data.section || '-', course:'-', schoolYear:'', curriculum:'CURRENT',
                    studentType:'REGULAR', yearLevel:data.year || '-', residency:'PR', cwa:'-'
                };
                meta.studentNo = data.studentNo || '';
                meta.studentName = data.studentName || '';
                meta.course = (payload.student ? (payload.student.program || '-') : '-') + ' : ' + (payload.student ? (payload.student.program || '-') : '-');
            }
            var sheet = document.getElementById('ogrPreviewSheet');
            if (!sheet) return;
            sheet.innerHTML = ogrBuildTemplate(data, subjects || [], meta || {});
            document.getElementById('ogrPreviewModal').style.display = 'flex';
        })
        .catch(function () {
            var sheet = document.getElementById('ogrPreviewSheet');
            if (!sheet) return;
            sheet.innerHTML = ogrBuildTemplate(data, subjects || [], meta || {});
            document.getElementById('ogrPreviewModal').style.display = 'flex';
        });
        return;
    }

    var sheet = document.getElementById('ogrPreviewSheet');
    if (!sheet) return;
    sheet.innerHTML = ogrBuildTemplate(data, subjects, meta);
    document.getElementById('ogrPreviewModal').style.display = 'flex';
}

function ogrClosePreview() {
    var m = document.getElementById('ogrPreviewModal');
    if (m) m.style.display = 'none';
}

function ogrPrintPreview() {
    var sheet = document.getElementById('ogrPreviewSheet');
    if (!sheet) return;
    ogrPrintSheets([sheet.innerHTML]);
}

function ogrPrintSheets(list) {
    var pc = document.getElementById('ogrPrintContainer');
    if (!pc || !list || !list.length) return;
    pc.innerHTML = list.map(function(h, i) {
        var cls = i < list.length - 1 ? ' ogr-print-page-break' : '';
        return '<div class="ogr-sheet' + cls + '">' + h + '</div>';
    }).join('');
    document.body.classList.add('ogr-printing');
    setTimeout(function() { window.print(); }, 120);
}

function ogrPrintSelected() {
    var sel = Array.from(document.querySelectorAll('#ogrTableBody .ogr-row-select:checked'));
    if (!sel.length) { alert('Select at least one record to print.'); return; }
    var sheets = sel.map(function(cb) {
        var row = cb.closest('tr');
        var rid = row ? row.getAttribute('data-row-id') : null;
        if (!rid) return '';
        var data = ogrGetRowData(rid);
        var subjects = ogrDemoSubjects[rid] || ogrDemoSubjects['1'];
        var meta = ogrDemoMeta[rid] || ogrDemoMeta['1'];
        return data ? ogrBuildTemplate(data, subjects, meta) : '';
    }).filter(Boolean);
    ogrPrintSheets(sheets);
}

function ogrOpenPreviewFromSelection() {
    var checked = document.querySelector('#ogrTableBody .ogr-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#ogrTableBody tr[data-row-id]');
    if (!row) return;
    var rowId = row.getAttribute('data-row-id');
    ogrOpenPreview(rowId);
}

function ogrOpenBlankPreview() {
    var sheet = document.getElementById('ogrPreviewSheet');
    if (!sheet) return;

    var blankData = { studentNo:'', studentName:'', program:'', year:'', section:'' };
    var blankMeta = {
        address:'', birthday:'', section:'', course:'', schoolYear:'',
        curriculum:'', studentType:'', yearLevel:'', residency:'', cwa:''
    };

    sheet.innerHTML = ogrBuildTemplate(blankData, [], blankMeta);
    document.getElementById('ogrPreviewModal').style.display = 'flex';
}

function ogrClearRowsToDemo() {
    if (Object.keys(window.ogrSubjectsByRow || {}).length === 0 && Object.keys(window.ogrMetaByRow || {}).length === 0) {
        window.ogrSubjectsByRow = ogrDemoSubjects;
        window.ogrMetaByRow = ogrDemoMeta;
    }
}

function ogrFilterTable(query) {
    var q = String(query || '').toLowerCase().trim();
    document.querySelectorAll('#ogrTableBody tr').forEach(function(row) {
        var text = (row.textContent || '').toLowerCase();
        row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
    });
    ogrSyncSelectAll();
}

window.addEventListener('afterprint', function() {
    document.body.classList.remove('ogr-printing');
    var pc = document.getElementById('ogrPrintContainer');
    if (pc) pc.innerHTML = '';
});

/* ── Table helpers ──────────────────────────────────────── */
function ogrToggleSelectAll(s) { document.querySelectorAll('#ogrTableBody .ogr-row-select').forEach(function(c){c.checked=!!s.checked});ogrSyncSelectAll(); }
function ogrSyncSelectAll() {
    var h=document.getElementById('ogrSelectAll'),items=document.querySelectorAll('#ogrTableBody .ogr-row-select');if(!h)return;
    var t=items.length,c=0;items.forEach(function(x){if(x.checked)c++});h.checked=t>0&&c===t;h.indeterminate=c>0&&c<t;
}
function ogrCloseMenus(){document.querySelectorAll('.apst-dropdown.open').forEach(function(m){m.classList.remove('open','drop-up');m.style.top='';m.style.left='';m.style.right='';m.style.bottom=''});}
function ogrToggleMenu(id,trig){var m=document.getElementById(id);if(!m||!trig)return;var o=m.classList.contains('open');ogrCloseMenus();if(o)return;var r=trig.getBoundingClientRect();m.style.left='auto';m.style.right=(window.innerWidth-r.left+4)+'px';if(window.innerHeight-r.bottom<120){m.classList.add('drop-up');m.style.top='auto';m.style.bottom=(window.innerHeight-r.bottom)+'px';}else{m.style.top=r.top+'px';m.style.bottom='auto';}m.classList.add('open');}
function ogrOpenModal(id){ogrCloseMenus();var m=document.getElementById(id);if(m)m.style.display='flex';}
function ogrCloseModal(id){var m=document.getElementById(id);if(m)m.style.display='none';}
function ogrGetRow(id){return document.querySelector('tr[data-row-id="'+id+'"]');}
function ogrOpenEdit(id){var r=ogrGetRow(id);if(!r)return;ogrCurrentRowId=id;var c=r.querySelectorAll('td');document.getElementById('ogrEditNumber').value=(c[1]?c[1].textContent:'').trim();document.getElementById('ogrEditName').value=(c[2]?c[2].textContent:'').trim();document.getElementById('ogrEditCourse').value=(c[3]?c[3].textContent:'').trim();document.getElementById('ogrEditYear').value=(c[4]?c[4].textContent:'').trim();ogrOpenModal('ogrEditModal');}
function ogrSaveEdit(){var r=ogrGetRow(ogrCurrentRowId);if(!r)return;var c=r.querySelectorAll('td');if(c[1])c[1].textContent=(document.getElementById('ogrEditNumber').value||'').trim();if(c[2])c[2].textContent=(document.getElementById('ogrEditName').value||'').trim();if(c[3])c[3].textContent=(document.getElementById('ogrEditCourse').value||'').trim();if(c[4])c[4].textContent=(document.getElementById('ogrEditYear').value||'').trim();ogrCloseModal('ogrEditModal');}
function ogrOpenDelete(id){ogrCurrentRowId=id;ogrOpenModal('ogrDeleteModal');}
function ogrConfirmDelete(){var r=ogrGetRow(ogrCurrentRowId);if(r)r.remove();ogrSyncSelectAll();ogrCloseModal('ogrDeleteModal');}
document.addEventListener('click',function(e){var t=e.target.closest('[data-ogr-menu-toggle]');if(t){e.stopPropagation();ogrToggleMenu(t.getAttribute('data-ogr-menu-toggle'),t);return;}if(!e.target.closest('.apst-dropdown'))ogrCloseMenus();});
window.addEventListener('scroll',ogrCloseMenus,true);
