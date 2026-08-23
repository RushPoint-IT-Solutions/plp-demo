/* permission-cross-enroll.js - Permission to Cross-Enroll form page logic */

var pceCurrentRowId = null;
var pceConfig = window.pceConfig || {};

function pceBuildUrl(template, id) {
    return String(template || '').replace('__ID__', String(id));
}

function pceRequestJson(url, method, payload) {
    return fetch(url, {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': pceConfig.csrfToken || '',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: payload ? JSON.stringify(payload) : null
    }).then(function(response) {
        if (!response.ok) {
            return response.json().catch(function() { return {}; }).then(function(data) {
                var message = 'Request failed.';
                if (data && data.errors) {
                    var firstKey = Object.keys(data.errors)[0];
                    if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
                        message = data.errors[firstKey][0];
                    }
                }
                throw new Error(message);
            });
        }
        return response.json().catch(function() { return { ok: true }; });
    });
}

function pceEsc(v) {
    return String(v || '').replace(/[&<>"']/g, function(c) {
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
}

function pceCleanSemester(value) {
    return String(value || '')
        .replace(/\bsemester\b/ig, '')
        .trim();
}

function pceFormatUnits(value) {
    var num = parseFloat(value);
    if (Number.isNaN(num)) return '';
    return String(num).replace(/\.0+$/, '').replace(/(\.\d*[1-9])0+$/, '$1');
}

function pceSubjectRows(subjects) {
    var rows = '';
    var totalUnits = 0;
    var list = Array.isArray(subjects) ? subjects.slice(0, 5) : [];

    while (list.length < 5) {
        list.push({ units: '' });
    }

    list.forEach(function(subject) {
        var units = pceFormatUnits(subject.units);
        var numericUnits = parseFloat(subject.units);
        if (!Number.isNaN(numericUnits)) totalUnits += numericUnits;

        var lineText = '';
        if (subject.code || subject.description) {
            lineText = [subject.code, subject.description].filter(Boolean).join(' - ');
        }

        rows += '<div class="pce-app-subject-row">'
            + '<span class="pce-app-subject-line">' + (lineText ? pceEsc(lineText) : '') + '</span>'
            + '<span class="pce-app-units-blank">' + (units ? pceEsc(units) : '') + '</span>'
            + '<span class="pce-app-units-label">Units</span>'
            + '</div>';
    });

    return {
        rows: rows,
        totalUnits: totalUnits > 0 ? pceFormatUnits(totalUnits) : ''
    };
}

function pceBuildTemplate(data) {
    var ay = data.schoolYear || '';
    var ayParts = String(ay).split(/[-\u2013\u2014]/).map(function(p) { return p.trim().replace(/^2/, ''); });
    var ayStart = ayParts[0] || '';
    var ayEnd = ayParts[1] || '';
    var semester = pceCleanSemester(data.semester) || '';
    var subjectTable = pceSubjectRows(data.subjects);
    var printedBy = data.printedBy || '';
    var studentName = String(data.studentName || '');
    var studentNameClass = studentName.length > 38
        ? ' pce-student-signature--extra-small'
        : (studentName.length > 28 ? ' pce-student-signature--small' : '');

    return '' +
        '<div class="pce-header">' +
            '<img class="pce-seal" src="/img/logobg.png" alt="PLP Seal">' +
            '<div class="pce-header-text">' +
                '<div class="pce-header-city">City Government of Pasig</div>' +
                '<div class="pce-header-school">PAMANTASAN NG LUNGSOD NG PASIG</div>' +
                '<div class="pce-header-office">OFFICE OF THE UNIVERSITY REGISTRAR</div>' +
                '<div class="pce-header-address">Alkalde Jose St. Kapasigan, Pasig City, Philippines 1600</div>' +
                '<div class="pce-header-tel">Tel Nos. 8642 8300 Telefax 642-41-00 Hotline No. (0926)2690463</div>' +
            '</div>' +
        '</div>' +

        '<div class="pce-form-content">' +
        '<div class="pce-form-no">PLPRO FORM NO. 1G Revised 2023</div>' +
        '<div class="pce-title">APPLICATION TO CROSS-ENROLL</div>' +

        '<div class="pce-app-date"><span class="pce-date-line">&nbsp;</span>, 2<span class="pce-date-year">&nbsp;</span></div>' +

        '<div class="pce-registrar-block">' +
            'THE REGISTRAR<br>' +
            'Pamantasan ng Lungsod ng Pasig<br>' +
            'Pasig City' +
        '</div>' +

        '<p class="pce-salutation">Sir/Madam:</p>' +

        '<p class="pce-app-body">' +
            'I wish to enroll/cross-enroll the following subjects at the <span class="pce-fill pce-fill-school">&nbsp;</span> located at <span class="pce-fill pce-fill-location">&nbsp;</span> in the <span class="pce-fill-short">' + pceEsc(semester) + '</span> Semester of Academic Year 2<span class="pce-fill-year">' + pceEsc(ayStart) + '</span>,2<span class="pce-fill-year">' + pceEsc(ayEnd) + '</span>.' +
        '</p>' +

        '<div class="pce-app-subjects">' + subjectTable.rows + '</div>' +

        '<div class="pce-app-total">TOTAL = <span class="pce-total-line">' + pceEsc(subjectTable.totalUnits) + '</span> UNITS</div>' +

        '<p class="pce-app-body">I have passed the pre-requisite to the foregoing subjects, and I will promptly submit my ratings in the course after the close of the school term.</p>' +

        '<div class="pce-app-sign-grid">' +
            '<div class="pce-app-sign-left">' +
                '<div class="pce-app-notvalid">NOT VALID<br>AS<br>PERMIT</div>' +
                '<div class="pce-approved-label">Approved by:</div>' +
                '<div class="pce-sign-line-blank pce-dean-line"></div>' +
                '<div class="pce-sign-caption">Dean</div>' +
                '<div class="pce-registrar-name">FEDERICO G. NUEVA, MT</div>' +
                '<div class="pce-sign-caption">Registrar</div>' +
                '<div class="pce-app-footer">Printed By: ' + pceEsc(printedBy) + '</div>' +
            '</div>' +
            '<div class="pce-app-sign-right">' +
                '<div class="pce-respect">Very respectfully yours,</div>' +
                '<div class="pce-sign-line-blank pce-student-signature' + studentNameClass + '">' + pceEsc(studentName) + '</div>' +
                '<div class="pce-sign-caption">Signature over printed name</div>' +
                '<div class="pce-app-kv">Student Number: <span class="pce-inline-line">' + pceEsc(data.studentNo) + '</span></div>' +
                '<div class="pce-sign-line-blank pce-program-line">' + pceEsc((data.program || '') + (data.year ? (' - ' + data.year) : '')) + '</div>' +
                '<div class="pce-sign-caption">Program &amp; Year</div>' +
            '</div>' +
        '</div>' +
        '</div>';
}

function pceGetRowData(rowId) {
    var row = pceGetRow(rowId);
    if (!row) return null;
    var cells = row.querySelectorAll('td');
    var subjects = [];
    try {
        subjects = JSON.parse(row.getAttribute('data-subjects') || '[]');
    } catch (error) {
        subjects = [];
    }

    return {
        studentNo: (cells[1] ? cells[1].textContent : '').trim(),
        studentName: (cells[2] ? cells[2].textContent : '').trim(),
        program: (cells[3] ? cells[3].textContent : '').trim(),
        year: (cells[4] ? cells[4].textContent : '').trim(),
        section: (cells[5] ? cells[5].textContent : '').trim(),
        schoolYear: (row.getAttribute('data-school-year') || '').trim(),
        semester: (row.getAttribute('data-semester') || '').trim(),
        subjects: subjects,
        printedBy: pceConfig.printedBy || ''
    };
}

function pceOpenPreview(rowId) {
    var data = pceGetRowData(rowId);
    if (!data) return;
    var sheet = document.getElementById('pcePreviewSheet');
    if (!sheet) return;
    sheet.innerHTML = pceBuildTemplate(data);
    document.getElementById('pcePreviewModal').style.display = 'flex';
    document.body.classList.add('pce-preview-open');
}

function pceClosePreview() {
    var m = document.getElementById('pcePreviewModal');
    if (m) m.style.display = 'none';
    document.body.classList.remove('pce-preview-open');
}

function pcePrintPreview() {
    var sheet = document.getElementById('pcePreviewSheet');
    if (!sheet) return;
    pcePrintSheets([sheet.innerHTML]);
}

function pcePrintSheets(list) {
    var pc = document.getElementById('pcePrintContainer');
    if (!pc || !list || !list.length) return;

    pc.innerHTML = list.map(function(html, i) {
        var cls = i < list.length - 1 ? ' pce-print-page-break' : '';
        return '<div class="pce-sheet' + cls + '">' + html + '</div>';
    }).join('');

    document.body.classList.add('pce-printing');
    setTimeout(function() { window.print(); }, 120);
}

function pcePrintSelected() {
    var selected = Array.from(document.querySelectorAll('#pceTableBody .pce-row-select:checked'));
    if (!selected.length) {
        alert('Select at least one record to print.');
        return;
    }

    var sheets = selected.map(function(cb) {
        var row = cb.closest('tr');
        var rowId = row ? row.getAttribute('data-row-id') : null;
        var data = rowId ? pceGetRowData(rowId) : null;
        return data ? pceBuildTemplate(data) : '';
    }).filter(Boolean);

    pcePrintSheets(sheets);
}

function pceOpenPreviewFromSelection() {
    var checked = document.querySelector('#pceTableBody .pce-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#pceTableBody tr[data-row-id]');
    if (!row) return;
    var rowId = row.getAttribute('data-row-id');
    pceOpenPreview(rowId);
}

function pceOpenBlankPreview() {
    var sheet = document.getElementById('pcePreviewSheet');
    if (!sheet) return;
    sheet.innerHTML = pceBuildTemplate({
        studentNo: '',
        studentName: '',
        program: '',
        year: '',
        section: '',
        schoolYear: '',
        semester: '',
        subjects: [],
        printedBy: pceConfig.printedBy || ''
    });
    document.getElementById('pcePreviewModal').style.display = 'flex';
    document.body.classList.add('pce-preview-open');
}

function pceFilterTable(query) {
    var q = String(query || '').toLowerCase().trim();
    document.querySelectorAll('#pceTableBody tr').forEach(function(row) {
        var text = (row.textContent || '').toLowerCase();
        row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
    });
    pceSyncSelectAll();
}

window.addEventListener('afterprint', function() {
    document.body.classList.remove('pce-printing');
    document.body.classList.remove('pce-preview-open');
    var pc = document.getElementById('pcePrintContainer');
    if (pc) pc.innerHTML = '';
});

function pceToggleSelectAll(source) {
    document.querySelectorAll('#pceTableBody .pce-row-select').forEach(function(cb) {
        cb.checked = !!source.checked;
    });
    pceSyncSelectAll();
}

function pceSyncSelectAll() {
    var header = document.getElementById('pceSelectAll');
    var items = document.querySelectorAll('#pceTableBody .pce-row-select');
    if (!header) return;

    var total = items.length;
    var checked = 0;
    items.forEach(function(cb) { if (cb.checked) checked++; });

    header.checked = total > 0 && checked === total;
    header.indeterminate = checked > 0 && checked < total;
}

function pceCloseMenus() {
    document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
        menu.classList.remove('open', 'drop-up');
        menu.style.top = '';
        menu.style.left = '';
        menu.style.right = '';
        menu.style.bottom = '';
    });
}

function pceToggleMenu(menuId, trigger) {
    var menu = document.getElementById(menuId);
    if (!menu || !trigger) return;

    var isOpen = menu.classList.contains('open');
    pceCloseMenus();
    if (isOpen) return;

    var rect = trigger.getBoundingClientRect();
    var spaceBelow = window.innerHeight - rect.bottom;
    menu.style.left = 'auto';
    menu.style.right = (window.innerWidth - rect.left + 4) + 'px';

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

function pceOpenModal(id) {
    pceCloseMenus();
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'flex';
}

function pceCloseModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

function pceGetRow(rowId) {
    return document.querySelector('tr[data-row-id="' + rowId + '"]');
}

function pceOpenEdit(rowId) {
    var row = pceGetRow(rowId);
    if (!row) return;

    pceCurrentRowId = rowId;
    var cells = row.querySelectorAll('td');
    document.getElementById('pceEditNumber').value = (cells[1] ? cells[1].textContent : '').trim();
    document.getElementById('pceEditName').value = (cells[2] ? cells[2].textContent : '').trim();
    document.getElementById('pceEditCourse').value = (cells[3] ? cells[3].textContent : '').trim();
    document.getElementById('pceEditYear').value = (cells[4] ? cells[4].textContent : '').trim();
    pceOpenModal('pceEditModal');
}

function pceSaveEdit() {
    var row = pceGetRow(pceCurrentRowId);
    if (!row) return;
    var cells = row.querySelectorAll('td');
    var studentNo = (document.getElementById('pceEditNumber').value || '').trim();
    var studentName = (document.getElementById('pceEditName').value || '').trim();
    var program = (document.getElementById('pceEditCourse').value || '').trim();
    var yearLevel = (document.getElementById('pceEditYear').value || '').trim();

    var finish = function() {
        if (cells[1]) cells[1].textContent = studentNo;
        if (cells[2]) {
            cells[2].innerHTML = '<button type="button" class="doc-link-btn" onclick="pceOpenPreview(' + pceCurrentRowId + ')">' + pceEsc(studentName) + '</button>';
        }
        if (cells[3]) cells[3].textContent = program;
        if (cells[4]) cells[4].textContent = yearLevel;
        if (cells[5]) cells[5].textContent = ((program || 'PROGRAM') + ' ' + (yearLevel || 'YEAR')).trim();
        pceCloseModal('pceEditModal');
    };

    if (!pceConfig.updateUrlTemplate) {
        finish();
        return;
    }

    pceRequestJson(pceBuildUrl(pceConfig.updateUrlTemplate, pceCurrentRowId), 'PUT', {
        student_no: studentNo,
        name: studentName,
        program: program,
        year_level: yearLevel
    }).then(function() {
        finish();
    }).catch(function(error) {
        alert(error.message || 'Unable to update cross-enroll record.');
    });
}

function pceOpenDelete(rowId) {
    pceCurrentRowId = rowId;
    pceOpenModal('pceDeleteModal');
}

function pceConfirmDelete() {
    var row = pceGetRow(pceCurrentRowId);
    var finish = function() {
        if (row) row.remove();
        pceSyncSelectAll();
        pceCloseModal('pceDeleteModal');
    };

    if (!pceConfig.destroyUrlTemplate) {
        finish();
        return;
    }

    pceRequestJson(pceBuildUrl(pceConfig.destroyUrlTemplate, pceCurrentRowId), 'DELETE').then(function() {
        finish();
    }).catch(function(error) {
        alert(error.message || 'Unable to delete cross-enroll record.');
    });
}

document.addEventListener('click', function(event) {
    var toggle = event.target.closest('[data-pce-menu-toggle]');
    if (toggle) {
        event.stopPropagation();
        pceToggleMenu(toggle.getAttribute('data-pce-menu-toggle'), toggle);
        return;
    }

    if (!event.target.closest('.apst-dropdown')) {
        pceCloseMenus();
    }
});

window.addEventListener('scroll', pceCloseMenus, true);
