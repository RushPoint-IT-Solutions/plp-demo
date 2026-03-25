/* diploma.js — logic for Diploma form page */

var diplomaCurrentRowId = null;

function diplomaEscHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(ch) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return map[ch];
    });
}

function diplomaBuildPreviewTemplate(data) {
    var programLabel = (data.program || '').toUpperCase();
    var degreeLine = 'Bachelor of Science in Entrepreneurship';
    var now = new Date();
    var day = now.getDate();
    var month = now.toLocaleDateString('en-US', { month: 'long' });
    var year = now.getFullYear();
    var suffix = 'th';
    if (day % 10 === 1 && day % 100 !== 11) suffix = 'st';
    else if (day % 10 === 2 && day % 100 !== 12) suffix = 'nd';
    else if (day % 10 === 3 && day % 100 !== 13) suffix = 'rd';
    var formalIssueDate = day + suffix + ' of ' + month + ' ' + year;

    if (programLabel === 'BSIT') degreeLine = 'Bachelor of Science in Information Technology';
    if (programLabel === 'BSCS') degreeLine = 'Bachelor of Science in Computer Science';
    if (programLabel === 'BSED') degreeLine = 'Bachelor of Secondary Education';
    if (programLabel === 'BSBA') degreeLine = 'Bachelor of Science in Business Administration';

    return '' +
        '<div class="dpl-header-row">' +
            '<div>' +
                '<div class="dpl-gov-text">City Government of Pasig<br>Republic of the Philippines</div>' +
                '<div class="dpl-school-name">Pamantasan ng Lungsod ng Pasig</div>' +
            '</div>' +
        '</div>' +

        '<p class="dpl-presnts">TO ALL PERSONS TO WHOM THESE PRESENTS:</p>' +
        '<p class="dpl-script-line">Be it known, that the Board of Regents of this University, by authority granted by the Republic</p>' +
        '<p class="dpl-script-line" style="margin-top:0;">of the Philippines and on the recommendation of the Academic Council, has conferred upon</p>' +

        '<div class="dpl-name">' + diplomaEscHtml(data.studentName) + '</div>' +

        '<p class="dpl-script-line">who has fulfilled all the requirements for the degree of</p>' +
        '<div class="dpl-degree">' + diplomaEscHtml(degreeLine) + '</div>' +
        '<p class="dpl-script-line">with all the rights, honors, and privileges as well as the obligations and responsibilities thereto appertaining.</p>' +
        '<p class="dpl-script-line" style="margin-top:0;">In testimony thereof, the seal of the University and the signatures of the Chairman of the</p>' +
        '<p class="dpl-script-line" style="margin-top:0;">Board of Regents, the University President, and the Registrar are hereunto affixed.</p>' +
        '<p class="dpl-footer-line">Given in Pasig City, Philippines this ' + diplomaEscHtml(formalIssueDate) + '.</p>' +

        '<div class="dpl-bottom">' +
            '<div class="dpl-left-copy">' +
                '<div class="dpl-left-note">Certified text of the original:</div>' +
                '<div class="dpl-left-name">FEDERICO C. NUEVA</div>' +
                '<div class="dpl-left-role">University Registrar</div>' +
            '</div>' +
            '<div class="dpl-right-stack">' +
                '<div class="dpl-signatures">' +
                    '<div class="dpl-sign-item"><div class="name"><span class="sgd">(Sgd.)</span> <span class="person">PROF. MARIANO L. CHING</span></div><div class="title">University Registrar</div></div>' +
                    '<div class="dpl-sign-item"><div class="name"><span class="sgd">(Sgd.)</span> <span class="person">AMB. ROSALINDA V. TIRONA</span></div><div class="title">University President</div></div>' +
                    '<div class="dpl-sign-item"><div class="name"><span class="sgd">(Sgd.)</span> <span class="person">HON. ROBERT C. EUSEBIO</span></div><div class="title">Chairman, Board of Regents</div></div>' +
                '</div>' +
            '</div>' +
        '</div>';
}

function diplomaGetRowData(rowId) {
    var row = diplomaGetRow(rowId);
    if (!row) return null;

    var cells = row.querySelectorAll('td');
    return {
        studentNo: (cells[1] ? cells[1].textContent : '').trim(),
        studentName: (cells[2] ? cells[2].textContent : '').trim(),
        program: (cells[3] ? cells[3].textContent : '').trim(),
        year: (cells[4] ? cells[4].textContent : '').trim(),
        section: (cells[5] ? cells[5].textContent : '').trim(),
        issueDate: new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
    };
}

function diplomaOpenPreview(rowId) {
    var data = diplomaGetRowData(rowId);
    if (!data) return;

    var sheet = document.getElementById('diplomaPreviewSheet');
    if (!sheet) return;

    sheet.innerHTML = diplomaBuildPreviewTemplate(data);
    document.getElementById('diplomaPreviewModal').style.display = 'flex';
    document.body.classList.add('diploma-preview-open');
}

function diplomaClosePreview() {
    var modal = document.getElementById('diplomaPreviewModal');
    if (modal) modal.style.display = 'none';
    document.body.classList.remove('diploma-preview-open');
}

function diplomaPrintPreview() {
    var sheet = document.getElementById('diplomaPreviewSheet');
    if (!sheet) return;
    diplomaPrintSheets([sheet.innerHTML]);
}

function diplomaPrintSheets(sheetHtmlList) {
    var printContainer = document.getElementById('diplomaPrintContainer');
    if (!printContainer || !sheetHtmlList || !sheetHtmlList.length) return;

    printContainer.innerHTML = sheetHtmlList.map(function(sheetHtml, index) {
        var pageClass = index < sheetHtmlList.length - 1 ? ' dpl-print-page-break' : '';
        return '<div class="dpl-sheet' + pageClass + '">' + sheetHtml + '</div>';
    }).join('');

    document.body.classList.add('diploma-printing');
    setTimeout(function() {
        window.print();
    }, 120);
}

function diplomaPrintSelected() {
    var selectedRows = Array.from(document.querySelectorAll('#diplomaTableBody .diploma-row-select:checked'));
    if (!selectedRows.length) {
        alert('Select at least one record to print.');
        return;
    }

    var sheets = selectedRows.map(function(cb) {
        var row = cb.closest('tr');
        var rowId = row ? row.getAttribute('data-row-id') : null;
        var data = rowId ? diplomaGetRowData(rowId) : null;
        return data ? diplomaBuildPreviewTemplate(data) : '';
    }).filter(function(html) {
        return !!html;
    });

    diplomaPrintSheets(sheets);
}

window.addEventListener('afterprint', function() {
    document.body.classList.remove('diploma-printing');
    document.body.classList.remove('diploma-preview-open');
    var printContainer = document.getElementById('diplomaPrintContainer');
    if (printContainer) printContainer.innerHTML = '';
});

function diplomaToggleSelectAll(source) {
    document.querySelectorAll('#diplomaTableBody .diploma-row-select').forEach(function(cb) {
        cb.checked = !!source.checked;
    });
    diplomaSyncSelectAll();
}

function diplomaSyncSelectAll() {
    var header = document.getElementById('diplomaSelectAll');
    var items = document.querySelectorAll('#diplomaTableBody .diploma-row-select');
    if (!header) return;

    var total = items.length;
    var checked = 0;
    items.forEach(function(cb) {
        if (cb.checked) checked++;
    });

    header.checked = total > 0 && checked === total;
    header.indeterminate = checked > 0 && checked < total;
}

function diplomaCloseMenus() {
    document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
        menu.classList.remove('open', 'drop-up');
        menu.style.top = '';
        menu.style.left = '';
        menu.style.right = '';
        menu.style.bottom = '';
    });
}

function diplomaToggleMenu(menuId, trigger) {
    var menu = document.getElementById(menuId);
    if (!menu || !trigger) return;

    var isOpen = menu.classList.contains('open');
    diplomaCloseMenus();
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

function diplomaOpenModal(id) {
    diplomaCloseMenus();
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'flex';
}

function diplomaCloseModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

function diplomaGetRow(rowId) {
    return document.querySelector('tr[data-row-id="' + rowId + '"]');
}

function diplomaOpenEdit(rowId) {
    var row = diplomaGetRow(rowId);
    if (!row) return;

    diplomaCurrentRowId = rowId;
    var cells = row.querySelectorAll('td');
    document.getElementById('diplomaEditNumber').value = (cells[1] ? cells[1].textContent : '').trim();
    document.getElementById('diplomaEditName').value = (cells[2] ? cells[2].textContent : '').trim();
    document.getElementById('diplomaEditCourse').value = (cells[3] ? cells[3].textContent : '').trim();
    document.getElementById('diplomaEditYear').value = (cells[4] ? cells[4].textContent : '').trim();
    diplomaOpenModal('diplomaEditModal');
}

function diplomaSaveEdit() {
    var row = diplomaGetRow(diplomaCurrentRowId);
    if (!row) return;
    var cells = row.querySelectorAll('td');

    if (cells[1]) cells[1].textContent = (document.getElementById('diplomaEditNumber').value || '').trim();
    if (cells[2]) cells[2].textContent = (document.getElementById('diplomaEditName').value || '').trim();
    if (cells[3]) cells[3].textContent = (document.getElementById('diplomaEditCourse').value || '').trim();
    if (cells[4]) cells[4].textContent = (document.getElementById('diplomaEditYear').value || '').trim();

    diplomaCloseModal('diplomaEditModal');
}

function diplomaOpenDelete(rowId) {
    diplomaCurrentRowId = rowId;
    diplomaOpenModal('diplomaDeleteModal');
}

function diplomaConfirmDelete() {
    var row = diplomaGetRow(diplomaCurrentRowId);
    if (row) row.remove();
    diplomaSyncSelectAll();
    diplomaCloseModal('diplomaDeleteModal');
}

document.addEventListener('click', function(event) {
    var toggle = event.target.closest('[data-diploma-menu-toggle]');
    if (toggle) {
        event.stopPropagation();
        diplomaToggleMenu(toggle.getAttribute('data-diploma-menu-toggle'), toggle);
        return;
    }

    if (!event.target.closest('.apst-dropdown')) {
        diplomaCloseMenus();
    }
});

window.addEventListener('scroll', diplomaCloseMenus, true);
