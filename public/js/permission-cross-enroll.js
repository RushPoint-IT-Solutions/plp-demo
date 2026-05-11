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
        list.push({ code: '', description: '', units: '' });
    }

    list.forEach(function(subject) {
        var units = pceFormatUnits(subject.units);
        var numericUnits = parseFloat(subject.units);
        if (!Number.isNaN(numericUnits)) totalUnits += numericUnits;

        rows += '<tr>'
            + '<td>' + (subject.code ? pceEsc(subject.code) : '<span class="pce-underline"></span>') + '</td>'
            + '<td>' + (subject.description ? pceEsc(subject.description) : '<span class="pce-underline"></span>') + '</td>'
            + '<td>' + (units ? pceEsc(units) : '<span class="pce-underline"></span>') + '</td>'
            + '</tr>';
    });

    return {
        rows: rows,
        totalUnits: totalUnits > 0 ? pceFormatUnits(totalUnits) : ''
    };
}

function pceBuildTemplate(data) {
    var now = new Date();
    var ay = data.schoolYear || '__________';
    var semester = pceCleanSemester(data.semester) || '__________';
    var issueDate = now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    var subjectTable = pceSubjectRows(data.subjects);

    return '' +
        '<div class="pce-header-space"></div>' +
        '<div class="pce-form-no">PLP/RO FORM NO. 1H Revised 2023</div>' +
        '<div class="pce-title">PERMIT TO CROSS-ENROLL</div>' +

        '<div class="pce-date-row">' +
            '<span class="pce-date-line"><span class="pce-date-value">' + pceEsc(issueDate) + '</span></span>' +
            '<span class="pce-date-label">Date</span>' +
        '</div>' +

        '<div class="pce-registrar-label">THE REGISTRAR</div>' +
        '<div class="pce-registrar-lines">' +
            '<div class="pce-line"></div>' +
            '<div class="pce-line"></div>' +
        '</div>' +

        '<div class="pce-body">' +
            '<p>Sir/Madam:</p>' +
            '<p style="text-indent: 28px; margin-top: 8px;">' +
                'This is to authorize <span class="pce-fill">' + pceEsc(data.studentName) + '</span> with student number <span class="pce-fill-short">' + pceEsc(data.studentNo) + '</span> and a student from our university to cross-enroll the following subjects in your institution this <span class="pce-fill-short">' + pceEsc(semester) + '</span> (Summer/Semester) AY <span class="pce-fill-short">' + pceEsc(ay) + '</span>.' +
            '</p>' +
        '</div>' +

        '<table class="pce-subject-table">' +
            '<thead><tr><th>SUBJECT CODE</th><th>SUBJECT DESCRIPTION</th><th>UNITS</th></tr></thead>' +
            '<tbody>' + subjectTable.rows + '</tbody>' +
        '</table>' +

        '<div class="pce-total-row">TOTAL <span class="pce-total-line">' + pceEsc(subjectTable.totalUnits) + '</span> UNITS</div>' +

        '<div class="pce-sign-wrap">' +
            '<div class="pce-respect">Respectfully yours,</div>' +
            '<div class="pce-sign-name">FEDERICO G. NUEVA</div>' +
            '<div class="pce-sign-title">University Registrar</div>' +
        '</div>' +

        '<div class="pce-foot">Not Valid without seal</div>' +
        '<div class="pce-print-date">Generated: ' + pceEsc(issueDate) + '</div>';
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
        subjects: subjects
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
        subjects: []
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
