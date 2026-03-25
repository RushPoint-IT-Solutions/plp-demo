/* tor.js — logic for TOR form page */

var torCurrentRowId = null;

function torEscHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(ch) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return map[ch];
    });
}

function torGetRowData(rowId) {
    var row = torGetRow(rowId);
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

function torBuildPreviewTemplate(data) {
    return '' +
        '<h4>Transcript of Records</h4>' +
        '<p class="meta">Student: ' + torEscHtml(data.studentName) + ' (' + torEscHtml(data.studentNo) + ') | Program: ' + torEscHtml(data.program) + ' | Year: ' + torEscHtml(data.year) + ' | Section: ' + torEscHtml(data.section) + '</p>' +
        '<div class="tor-blank-area">' +
            '<div>' +
                '<strong>TOR Layout Placeholder</strong><br>' +
                'This preview is intentionally blank for now.<br>' +
                'Share your TOR sample, then this area can be replaced with the exact design.' +
            '</div>' +
        '</div>';
}

function torOpenPreview(rowId) {
    var data = torGetRowData(rowId);
    if (!data) return;

    var sheet = document.getElementById('torPreviewSheet');
    if (!sheet) return;

    sheet.innerHTML = torBuildPreviewTemplate(data);
    document.getElementById('torPreviewModal').style.display = 'flex';
}

function torClosePreview() {
    var modal = document.getElementById('torPreviewModal');
    if (modal) modal.style.display = 'none';
}

function torPrintPreview() {
    var sheet = document.getElementById('torPreviewSheet');
    if (!sheet) return;

    var printWindow = window.open('', '_blank', 'width=1000,height=900');
    if (!printWindow) return;

    var doc = '' +
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>TOR Preview</title>' +
        '<style>' +
        '@page{size:portrait;margin:14mm;}' +
        'body{margin:0;font-family:Georgia,\'Times New Roman\',serif;background:#fff;}' +
        '.tor-sheet{width:100%;min-height:0;box-sizing:border-box;background:#fff;border:1px solid #d4ded7;padding:28px 30px;color:#1f2937;}' +
        '.tor-sheet h4{margin:0;text-align:center;font-size:1.05rem;letter-spacing:.08em;text-transform:uppercase;font-weight:800;}' +
        '.tor-sheet .meta{margin-top:10px;text-align:center;font-size:.85rem;color:#4b5563;}' +
        '.tor-blank-area{margin-top:24px;border:2px dashed #9ca3af;min-height:920px;display:flex;align-items:center;justify-content:center;text-align:center;color:#6b7280;font-size:.95rem;line-height:1.8;padding:24px;}' +
        '</style></head><body><div class="tor-sheet">' + sheet.innerHTML + '</div></body></html>';

    printWindow.document.open();
    printWindow.document.write(doc);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() {
        printWindow.print();
    }, 250);
}

function torToggleSelectAll(source) {
    document.querySelectorAll('#torTableBody .tor-row-select').forEach(function(cb) {
        cb.checked = !!source.checked;
    });
    torSyncSelectAll();
}

function torSyncSelectAll() {
    var header = document.getElementById('torSelectAll');
    var items = document.querySelectorAll('#torTableBody .tor-row-select');
    if (!header) return;

    var total = items.length;
    var checked = 0;
    items.forEach(function(cb) {
        if (cb.checked) checked++;
    });

    header.checked = total > 0 && checked === total;
    header.indeterminate = checked > 0 && checked < total;
}

function torCloseMenus() {
    document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
        menu.classList.remove('open', 'drop-up');
        menu.style.top = '';
        menu.style.left = '';
        menu.style.right = '';
        menu.style.bottom = '';
    });
}

function torToggleMenu(menuId, trigger) {
    var menu = document.getElementById(menuId);
    if (!menu || !trigger) return;

    var isOpen = menu.classList.contains('open');
    torCloseMenus();
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

function torOpenModal(id) {
    torCloseMenus();
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'flex';
}

function torCloseModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

function torGetRow(rowId) {
    return document.querySelector('tr[data-row-id="' + rowId + '"]');
}

function torOpenEdit(rowId) {
    var row = torGetRow(rowId);
    if (!row) return;

    torCurrentRowId = rowId;
    var cells = row.querySelectorAll('td');
    document.getElementById('torEditNumber').value = (cells[1] ? cells[1].textContent : '').trim();
    document.getElementById('torEditName').value = (cells[2] ? cells[2].textContent : '').trim();
    document.getElementById('torEditCourse').value = (cells[3] ? cells[3].textContent : '').trim();
    document.getElementById('torEditYear').value = (cells[4] ? cells[4].textContent : '').trim();
    torOpenModal('torEditModal');
}

function torSaveEdit() {
    var row = torGetRow(torCurrentRowId);
    if (!row) return;
    var cells = row.querySelectorAll('td');

    if (cells[1]) cells[1].textContent = (document.getElementById('torEditNumber').value || '').trim();
    if (cells[2]) cells[2].textContent = (document.getElementById('torEditName').value || '').trim();
    if (cells[3]) cells[3].textContent = (document.getElementById('torEditCourse').value || '').trim();
    if (cells[4]) cells[4].textContent = (document.getElementById('torEditYear').value || '').trim();

    torCloseModal('torEditModal');
}

function torOpenDelete(rowId) {
    torCurrentRowId = rowId;
    torOpenModal('torDeleteModal');
}

function torConfirmDelete() {
    var row = torGetRow(torCurrentRowId);
    if (row) row.remove();
    torSyncSelectAll();
    torCloseModal('torDeleteModal');
}

document.addEventListener('click', function(event) {
    var toggle = event.target.closest('[data-tor-menu-toggle]');
    if (toggle) {
        event.stopPropagation();
        torToggleMenu(toggle.getAttribute('data-tor-menu-toggle'), toggle);
        return;
    }

    if (!event.target.closest('.apst-dropdown')) {
        torCloseMenus();
    }
});

window.addEventListener('scroll', torCloseMenus, true);
