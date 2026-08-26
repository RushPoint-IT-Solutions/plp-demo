/* waiver-cancellation.js - Waiver for Cancellation of Enrollment form page logic */

var wceCurrentRowId = null;
var wceConfig = window.wceConfig || {};

function wceBuildUrl(template, id) {
    return String(template || '').replace('__ID__', String(id));
}

function wceRequestJson(url, method, payload) {
    return fetch(url, {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': wceConfig.csrfToken || '',
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

function wceEsc(v) {
    return String(v || '').replace(/[&<>"']/g, function(c) {
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
}

function wceFormatSection(program, yearLevel) {
    var programCode = String(program || '').trim();
    var yearRaw = String(yearLevel || '').trim();

    var numericYear = '';
    var digitMatch = yearRaw.match(/\d+/);
    if (digitMatch && digitMatch[0]) {
        numericYear = digitMatch[0];
    } else {
        var lowerYear = yearRaw.toLowerCase();
        if (lowerYear === 'first') numericYear = '1';
        else if (lowerYear === 'second') numericYear = '2';
        else if (lowerYear === 'third') numericYear = '3';
        else if (lowerYear === 'fourth') numericYear = '4';
    }

    if (!programCode || !numericYear) {
        return ((programCode || 'PROGRAM') + ' ' + (yearRaw || 'YEAR')).trim();
    }

    return programCode + ' -' + numericYear + 'A';
}

function wceBuildTemplate(data) {
    return '' +
        '<div class="wce-header">' +
            '<img class="wce-seal" src="/img/logobg.png" alt="PLP Seal">' +
            '<div class="wce-header-text">' +
                '<div class="wce-header-school">PAMANTASAN NG LUNGSOD NG PASIG</div>' +
                '<div class="wce-header-office">REGISTRAR\'S OFFICE</div>' +
                '<div class="wce-header-address">Alkalde Jose St. Kapasigan, Pasig City</div>' +
                '<div class="wce-header-tel">Tel No. 628-1014</div>' +
            '</div>' +
        '</div>' +
        '<div class="wce-form-no">PLPRO FORM NO.</div>' +
        '<div class="wce-title">WAIVER FOR CANCELLATION OF ENROLMENT</div>' +

        '<div class="wce-address">The Registrar<br>Pamantasan ng Lungsod ng Pasig</div>' +

        '<p class="wce-p">I hereby cancel my enrolment to Pamantasan ng Lungsod ng Pasig in the College of <span class="wce-fill">' + wceEsc(data.program) + '</span> effective on the <span class="wce-fill-short">&nbsp;</span> semester of Academic Year 2<span class="wce-fill-year">&nbsp;</span>-2<span class="wce-fill-year">&nbsp;</span>.</p>' +

        '<p class="wce-p">I understand that the cancellation of my enrolment will waive my right and qualification to re-enroll in this University as my slot will be given to the students in the waiting list.</p>' +

        '<p class="wce-p">In this connection, I acknowledge the receipt of the following documents I submitted to the Registrar\'s Office:</p>' +

        '<div class="wce-grid-ack">' +
            '<div></div>' +
            '<div></div>' +
            '<div class="wce-ack-head">ACKNOWLEDGEMENT RECEIPT</div>' +
        '</div>' +
        '<div class="wce-grid-head">' +
            '<div></div>' +
            '<div></div>' +
            '<div class="wce-rec-head">RELEASED BY</div>' +
            '<div class="wce-rec-head">RECEIVED BY</div>' +
        '</div>' +

        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">2 pcs 2 x 2 picture</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Original Birth Certificate</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Original Barangay Clearance</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Original Cert. of Good Moral Character</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Photocopy of Voters ID</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Photocopy of Real Estate Tax Dec. No. or</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">2 Utility Bills</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Original Form 138</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Original Form 137</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Transcript of Records</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Honorable Dismissal</span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item-blank"></span><span class="wce-item">Certified True Copy of Grades</span><span class="wce-line"></span><span class="wce-line"></span></div>' +

        '<div class="wce-sign-area">' +
            '<div class="wce-vty">Very truly yours,</div>' +
            '<div class="wce-sign-line"></div>' +
            '<div class="wce-sign-caption">Signature above printed Name of Student</div>' +
        '</div>' +

        '<div class="wce-noted">Noted by:</div>' +
        '<div class="wce-noted-line"></div>';
}

function wceGetRowData(rowId) {
    var row = wceGetRow(rowId);
    if (!row) return null;
    var cells = row.querySelectorAll('td');
    return {
        studentNo: (cells[1] ? cells[1].textContent : '').trim(),
        studentName: (cells[2] ? cells[2].textContent : '').trim(),
        program: (cells[3] ? cells[3].textContent : '').trim(),
        year: (cells[5] ? cells[5].textContent : '').trim(),
        section: (cells[6] ? cells[6].textContent : '').trim()
    };
}

function wceOpenPreview(rowId) {
    var data = wceGetRowData(rowId);
    if (!data) return;
    var sheet = document.getElementById('wcePreviewSheet');
    if (!sheet) return;
    sheet.innerHTML = wceBuildTemplate(data);
    document.getElementById('wcePreviewModal').style.display = 'flex';
    document.body.classList.add('wce-preview-open');
}

function wceClosePreview() {
    var m = document.getElementById('wcePreviewModal');
    if (m) m.style.display = 'none';
    document.body.classList.remove('wce-preview-open');
}

function wcePrintPreview() {
    var sheet = document.getElementById('wcePreviewSheet');
    if (!sheet) return;
    wcePrintSheets([sheet.innerHTML]);
}

function wcePrintSheets(list) {
    var pc = document.getElementById('wcePrintContainer');
    if (!pc || !list || !list.length) return;

    pc.innerHTML = list.map(function(html, i) {
        var cls = i < list.length - 1 ? ' wce-print-page-break' : '';
        return '<div class="wce-sheet' + cls + '">' + html + '</div>';
    }).join('');

    document.body.classList.add('wce-printing');
    setTimeout(function() { window.print(); }, 120);
}

function wcePrintSelected() {
    var selected = Array.from(document.querySelectorAll('#wceTableBody .wce-row-select:checked'));
    if (!selected.length) {
        alert('Select at least one record to print.');
        return;
    }

    var sheets = selected.map(function(cb) {
        var row = cb.closest('tr');
        var rowId = row ? row.getAttribute('data-row-id') : null;
        var data = rowId ? wceGetRowData(rowId) : null;
        return data ? wceBuildTemplate(data) : '';
    }).filter(Boolean);

    wcePrintSheets(sheets);
}

function wceOpenPreviewFromSelection() {
    var checked = document.querySelector('#wceTableBody .wce-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#wceTableBody tr[data-row-id]');
    if (!row) return;
    var rowId = row.getAttribute('data-row-id');
    wceOpenPreview(rowId);
}

function wceOpenBlankPreview() {
    var sheet = document.getElementById('wcePreviewSheet');
    if (!sheet) return;
    sheet.innerHTML = wceBuildTemplate({
        studentNo: '',
        studentName: '',
        program: '',
        year: '',
        section: ''
    });
    document.getElementById('wcePreviewModal').style.display = 'flex';
    document.body.classList.add('wce-preview-open');
}

function wceFilterTable(query) {
    var q = String(query || '').toLowerCase().trim();
    document.querySelectorAll('#wceTableBody tr').forEach(function(row) {
        var text = (row.textContent || '').toLowerCase();
        row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
    });
    wceSyncSelectAll();
}

window.addEventListener('afterprint', function() {
    document.body.classList.remove('wce-printing');
    document.body.classList.remove('wce-preview-open');
    var pc = document.getElementById('wcePrintContainer');
    if (pc) pc.innerHTML = '';
});

function wceToggleSelectAll(source) {
    document.querySelectorAll('#wceTableBody .wce-row-select').forEach(function(cb) {
        cb.checked = !!source.checked;
    });
    wceSyncSelectAll();
}

function wceSyncSelectAll() {
    var header = document.getElementById('wceSelectAll');
    var items = document.querySelectorAll('#wceTableBody .wce-row-select');
    if (!header) return;

    var total = items.length;
    var checked = 0;
    items.forEach(function(cb) { if (cb.checked) checked++; });

    header.checked = total > 0 && checked === total;
    header.indeterminate = checked > 0 && checked < total;
}

function wceCloseMenus() {
    document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
        menu.classList.remove('open', 'drop-up');
        menu.style.top = '';
        menu.style.left = '';
        menu.style.right = '';
        menu.style.bottom = '';
    });
}

function wceToggleMenu(menuId, trigger) {
    var menu = document.getElementById(menuId);
    if (!menu || !trigger) return;

    var isOpen = menu.classList.contains('open');
    wceCloseMenus();
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

function wceOpenModal(id) {
    wceCloseMenus();
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'flex';
}

function wceCloseModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

function wceGetRow(rowId) {
    return document.querySelector('tr[data-row-id="' + rowId + '"]');
}

function wceOpenEdit(rowId) {
    var row = wceGetRow(rowId);
    if (!row) return;

    wceCurrentRowId = rowId;
    var cells = row.querySelectorAll('td');
    document.getElementById('wceEditNumber').value = (cells[1] ? cells[1].textContent : '').trim();
    document.getElementById('wceEditName').value = (cells[2] ? cells[2].textContent : '').trim();
    document.getElementById('wceEditCourse').value = (cells[3] ? cells[3].textContent : '').trim();
    document.getElementById('wceEditYear').value = (cells[5] ? cells[5].textContent : '').trim();
    var semesterSelect = document.getElementById('wceEditSemester');
    if (semesterSelect) {
        var semValue = (cells[4] ? cells[4].textContent : '').trim();
        semesterSelect.value = semValue || 'First';
    }
    var reasonInput = document.getElementById('wceEditReason');
    if (reasonInput) {
        reasonInput.value = row.getAttribute('data-cancellation-reason') || '';
    }
    wceOpenModal('wceEditModal');
}

function wceSaveEdit() {
    var row = wceGetRow(wceCurrentRowId);
    if (!row) return;
    var cells = row.querySelectorAll('td');
    var studentNo = (document.getElementById('wceEditNumber').value || '').trim();
    var studentName = (document.getElementById('wceEditName').value || '').trim();
    var program = (document.getElementById('wceEditCourse').value || '').trim();
    var yearLevel = (document.getElementById('wceEditYear').value || '').trim();
    var semesterEl = document.getElementById('wceEditSemester');
    var semester = semesterEl ? semesterEl.value : '';
    var reasonEl = document.getElementById('wceEditReason');
    var reason = reasonEl ? reasonEl.value.trim() : '';
    var sectionValue = wceFormatSection(program, yearLevel);

    var finish = function() {
        if (cells[1]) cells[1].textContent = studentNo;
        if (cells[2]) {
            cells[2].innerHTML = '<button type="button" class="doc-link-btn" onclick="wceOpenPreview(' + wceCurrentRowId + ')">' + wceEsc(studentName) + '</button>';
        }
        if (cells[3]) cells[3].textContent = program;
        if (cells[4]) cells[4].textContent = semester;
        if (cells[5]) cells[5].textContent = yearLevel;
        if (cells[6]) cells[6].textContent = sectionValue;
        row.setAttribute('data-cancellation-reason', reason);
        if (cells[8]) cells[8].textContent = reason || 'Reason not recorded';
        wceCloseModal('wceEditModal');
    };

    if (!wceConfig.updateUrlTemplate) {
        finish();
        return;
    }

    wceRequestJson(wceBuildUrl(wceConfig.updateUrlTemplate, wceCurrentRowId), 'PUT', {
        student_no: studentNo,
        name: studentName,
        program: program,
        year_level: yearLevel,
        semester: semester,
        reason: reason
    }).then(function() {
        finish();
    }).catch(function(error) {
        alert(error.message || 'Unable to update waiver record.');
    });
}

function wceOpenDelete(rowId) {
    wceCurrentRowId = rowId;
    wceOpenModal('wceDeleteModal');
}

function wceConfirmDelete() {
    var row = wceGetRow(wceCurrentRowId);
    var finish = function() {
        if (row) row.remove();
        wceSyncSelectAll();
        wceCloseModal('wceDeleteModal');
    };

    if (!wceConfig.destroyUrlTemplate) {
        finish();
        return;
    }

    wceRequestJson(wceBuildUrl(wceConfig.destroyUrlTemplate, wceCurrentRowId), 'DELETE').then(function() {
        finish();
    }).catch(function(error) {
        alert(error.message || 'Unable to delete waiver record.');
    });
}

document.addEventListener('click', function(event) {
    var toggle = event.target.closest('[data-wce-menu-toggle]');
    if (toggle) {
        event.stopPropagation();
        wceToggleMenu(toggle.getAttribute('data-wce-menu-toggle'), toggle);
        return;
    }

    if (!event.target.closest('.apst-dropdown')) {
        wceCloseMenus();
    }
});

window.addEventListener('scroll', wceCloseMenus, true);
