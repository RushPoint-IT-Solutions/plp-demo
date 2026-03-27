/* waiver-cancellation.js - Waiver for Cancellation of Enrollment form page logic */

var wceCurrentRowId = null;

function wceEsc(v) {
    return String(v || '').replace(/[&<>"']/g, function(c) {
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
}

function wceBuildTemplate(data) {
    var now = new Date();
    var ay = '2025-2026';
    var sem = 'Second';
    var monthDayYear = now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

    return '' +
        '<div class="wce-header-space"></div>' +
        '<div class="wce-form-no">PLP/RO FORM NO.</div>' +
        '<div class="wce-title">WAIVER FOR CANCELLATION OF ENROLLMENT</div>' +

        '<div class="wce-address">The Registrar<br>Pamantasan ng Lungsod ng Pasig</div>' +

        '<p class="wce-p">I hereby cancel my enrollment to Pamantasan ng Lungsod ng Pasig in the College of <span class="wce-fill">' + wceEsc(data.program) + '</span> effective on the <span class="wce-fill-short">' + wceEsc(monthDayYear) + '</span> semester of Academic Year <span class="wce-fill-short">' + wceEsc(ay) + '</span>.</p>' +

        '<p class="wce-p">I understand that the cancellation of my enrollment will waive my right and qualification to re-enroll in this University as my slot will be given to the students in the waiting list.</p>' +

        '<p class="wce-p">In this connection, I acknowledge the receipt of the following documents I submitted to the Registrar\'s Office:</p>' +

        '<div class="wce-grid-head">' +
            '<div></div>' +
            '<div class="wce-rec-head">ACKNOWLEDGEMENT RECEIPT</div>' +
            '<div class="wce-rec-head">RELEASED BY</div>' +
            '<div class="wce-rec-head">RECEIVED BY</div>' +
        '</div>' +

        '<div class="wce-item-row"><span class="wce-item">____ 2 pcs 2 x 2 picture</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Original Birth Certificate</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Original Barangay Clearance</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Original Cert. of Good Moral Character</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Photocopy of Voters ID</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Photocopy of Real Estate Tax Dec. No. or</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ 2 Utility Bills</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Original Form 138</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Original Form 137</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Transcript of Records</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Honorable Dismissal</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +
        '<div class="wce-item-row"><span class="wce-item">____ Certified True Copy of Grades</span><span class="wce-line"></span><span class="wce-line"></span><span class="wce-line"></span></div>' +

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
        year: (cells[4] ? cells[4].textContent : '').trim(),
        section: (cells[5] ? cells[5].textContent : '').trim()
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
    document.getElementById('wceEditYear').value = (cells[4] ? cells[4].textContent : '').trim();
    wceOpenModal('wceEditModal');
}

function wceSaveEdit() {
    var row = wceGetRow(wceCurrentRowId);
    if (!row) return;
    var cells = row.querySelectorAll('td');

    if (cells[1]) cells[1].textContent = (document.getElementById('wceEditNumber').value || '').trim();
    if (cells[2]) cells[2].textContent = (document.getElementById('wceEditName').value || '').trim();
    if (cells[3]) cells[3].textContent = (document.getElementById('wceEditCourse').value || '').trim();
    if (cells[4]) cells[4].textContent = (document.getElementById('wceEditYear').value || '').trim();

    wceCloseModal('wceEditModal');
}

function wceOpenDelete(rowId) {
    wceCurrentRowId = rowId;
    wceOpenModal('wceDeleteModal');
}

function wceConfirmDelete() {
    var row = wceGetRow(wceCurrentRowId);
    if (row) row.remove();
    wceSyncSelectAll();
    wceCloseModal('wceDeleteModal');
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
