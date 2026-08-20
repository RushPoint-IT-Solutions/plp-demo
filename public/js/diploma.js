/* diploma.js — logic for Diploma form page */

var diplomaCurrentRowId = null;

function diplomaEscHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(ch) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return map[ch];
    });
}

function diplomaGetCellText(row, selector) {
    var cell = row ? row.querySelector(selector) : null;
    return (cell ? cell.textContent : '').trim();
}

function diplomaGetCsrf() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function diplomaSaveSignatoriesToServer(row) {
    if (!row || !window.diplomaSignatoriesSaveUrl) return;

    var studentNo = row.getAttribute('data-student-no') || diplomaGetCellText(row, '.diploma-cell-number');
    if (!studentNo) return;

    fetch(window.diplomaSignatoriesSaveUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': diplomaGetCsrf(),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            student_no: studentNo,
            registrar_name: row.getAttribute('data-registrar-name') || '',
            registrar_title: row.getAttribute('data-registrar-title') || '',
            president_name: row.getAttribute('data-president-name') || '',
            president_title: row.getAttribute('data-president-title') || '',
            chairman_name: row.getAttribute('data-chairman-name') || '',
            chairman_title: row.getAttribute('data-chairman-title') || ''
        })
    }).catch(function() {
        /* silent — row keeps working from local DOM state even if the save call fails */
    });
}

function diplomaBuildCopyOneBottom() {
    return '' +
        '<div class="dpl-bottom dpl-bottom-copy1">' +
            '<div class="dpl-copy1-grid">' +
                '<div class="dpl-copy1-sign dpl-copy1-sign-left">' +
                    '<div class="dpl-copy1-line"></div>' +
                    '<div class="dpl-copy1-name">PROF. MARIANO L. CHING</div>' +
                    '<div class="dpl-copy1-title">University Registrar</div>' +
                '</div>' +
                '<div class="dpl-copy1-sign dpl-copy1-sign-right">' +
                    '<div class="dpl-copy1-line"></div>' +
                    '<div class="dpl-copy1-name">AMB. ROSALINDA V. TIRONA</div>' +
                    '<div class="dpl-copy1-title">University President</div>' +
                '</div>' +
                '<div class="dpl-copy1-sign dpl-copy1-sign-center">' +
                    '<div class="dpl-copy1-line"></div>' +
                    '<div class="dpl-copy1-name">HON. ROBERT C. EUSEBIO</div>' +
                    '<div class="dpl-copy1-title">Chairman, Board of Regents</div>' +
                '</div>' +
            '</div>' +
        '</div>';
}

var DIPLOMA_DEFAULT_SIGNATORIES = {
    registrarName: 'PROF. MARIANO L. CHING',
    registrarTitle: 'University Registrar',
    presidentName: 'AMB. ROSALINDA V. TIRONA',
    presidentTitle: 'University President',
    chairmanName: 'HON. ROBERT C. EUSEBIO',
    chairmanTitle: 'Chairman, Board of Regents'
};

function diplomaBuildCopyTwoBottom(data) {
    data = data || {};
    var registrarName = diplomaEscHtml((data.registrarName || '').trim() || DIPLOMA_DEFAULT_SIGNATORIES.registrarName);
    var registrarTitle = diplomaEscHtml((data.registrarTitle || '').trim() || DIPLOMA_DEFAULT_SIGNATORIES.registrarTitle);
    var presidentName = diplomaEscHtml((data.presidentName || '').trim() || DIPLOMA_DEFAULT_SIGNATORIES.presidentName);
    var presidentTitle = diplomaEscHtml((data.presidentTitle || '').trim() || DIPLOMA_DEFAULT_SIGNATORIES.presidentTitle);
    var chairmanName = diplomaEscHtml((data.chairmanName || '').trim() || DIPLOMA_DEFAULT_SIGNATORIES.chairmanName);
    var chairmanTitle = diplomaEscHtml((data.chairmanTitle || '').trim() || DIPLOMA_DEFAULT_SIGNATORIES.chairmanTitle);

    return '' +
        '<div class="dpl-bottom dpl-bottom-copy2">' +
            '<div class="dpl-left-copy">' +
                '<div class="dpl-left-note">Certified text of the original:</div>' +
                '<div class="dpl-left-name">' + registrarName + '</div>' +
                '<div class="dpl-left-role">' + registrarTitle + '</div>' +
            '</div>' +
            '<div class="dpl-right-stack">' +
                '<div class="dpl-signatures">' +
                    '<div class="dpl-sign-item"><div class="name"><span class="sgd">(Sgd.)</span> <span class="person">' + registrarName + '</span></div><div class="title">' + registrarTitle + '</div></div>' +
                    '<div class="dpl-sign-item"><div class="name"><span class="sgd">(Sgd.)</span> <span class="person">' + presidentName + '</span></div><div class="title">' + presidentTitle + '</div></div>' +
                    '<div class="dpl-sign-item"><div class="name"><span class="sgd">(Sgd.)</span> <span class="person">' + chairmanName + '</span></div><div class="title">' + chairmanTitle + '</div></div>' +
                '</div>' +
            '</div>' +
        '</div>';
}

function diplomaBuildPreviewTemplate(data) {
    var programLabel = (data.program || '').toUpperCase().replace(/\s+/g, ' ').trim();
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

    if (programLabel === 'BSIT' || programLabel.indexOf('INFORMATION TECHNOLOGY') !== -1) {
        degreeLine = 'Bachelor of Science in Information Technology';
    }
    if (programLabel === 'BSCS' || programLabel.indexOf('COMPUTER SCIENCE') !== -1) {
        degreeLine = 'Bachelor of Science in Computer Science';
    }
    if (programLabel === 'BSED' || programLabel.indexOf('SECONDARY EDUCATION') !== -1) {
        degreeLine = 'Bachelor of Secondary Education';
    }
    if (programLabel === 'BSBA' || programLabel.indexOf('BUSINESS ADMINISTRATION') !== -1) {
        degreeLine = 'Bachelor of Science in Business Administration';
    }
    if (programLabel === 'BS ENTREPRENEURSHIP' || programLabel === 'BSENT' || programLabel.indexOf('ENTREPRENEURSHIP') !== -1) {
        degreeLine = 'Bachelor of Science in Entrepreneurship';
    }

    var copyType = data.copyType === 'print-1' ? 'print-1' : 'print-2';
    var bottomMarkup = copyType === 'print-1' ? diplomaBuildCopyOneBottom() : diplomaBuildCopyTwoBottom(data);
    var govHeaderLines = copyType === 'print-1'
        ? 'Republic of the Philippines<br>City Government of Pasig'
        : 'City Government of Pasig<br>Republic of the Philippines';

    return '' +
        '<div class="dpl-header-row">' +
            '<div>' +
                '<div class="dpl-gov-text">' + govHeaderLines + '</div>' +
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
        bottomMarkup;
}

function diplomaGetRowData(rowId) {
    var row = diplomaGetRow(rowId);
    if (!row) return null;

    var copySelect = row.querySelector('.diploma-copy-select');
    return {
        studentNo: diplomaGetCellText(row, '.diploma-cell-number'),
        studentName: diplomaGetCellText(row, '.diploma-cell-name'),
        program: diplomaGetCellText(row, '.diploma-cell-program'),
        year: diplomaGetCellText(row, '.diploma-cell-year'),
        section: diplomaGetCellText(row, '.diploma-cell-section'),
        copyType: copySelect ? copySelect.value : 'print-2',
        registrarName: row.getAttribute('data-registrar-name') || '',
        registrarTitle: row.getAttribute('data-registrar-title') || '',
        presidentName: row.getAttribute('data-president-name') || '',
        presidentTitle: row.getAttribute('data-president-title') || '',
        chairmanName: row.getAttribute('data-chairman-name') || '',
        chairmanTitle: row.getAttribute('data-chairman-title') || '',
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
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function() {
            setTimeout(function() {
                window.print();
            }, 120);
        }).catch(function() {
            setTimeout(function() {
                window.print();
            }, 200);
        });
        return;
    }

    setTimeout(function() {
        window.print();
    }, 220);
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

function diplomaOpenPreviewFromSelection() {
    var checked = document.querySelector('#diplomaTableBody .diploma-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#diplomaTableBody tr[data-row-id]');
    if (!row) return;
    var rowId = row.getAttribute('data-row-id');
    diplomaOpenPreview(rowId);
}

function diplomaOpenBlankPreview() {
    var sheet = document.getElementById('diplomaPreviewSheet');
    if (!sheet) return;

    sheet.innerHTML = diplomaBuildPreviewTemplate({
        studentNo: '',
        studentName: '',
        program: '',
        year: '',
        section: ''
    });

    document.getElementById('diplomaPreviewModal').style.display = 'flex';
    document.body.classList.add('diploma-preview-open');
}

function diplomaFilterTable(query) {
    var q = String(query || '').toLowerCase().trim();
    document.querySelectorAll('#diplomaTableBody tr').forEach(function(row) {
        var text = (row.textContent || '').toLowerCase();
        row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
    });
    diplomaSyncSelectAll();
}

function diplomaHandleCopyChange(selectEl) {
    if (!selectEl) return;
    var row = selectEl.closest('tr');
    if (!row) return;

    row.setAttribute('data-copy-type', selectEl.value || 'print-2');
    diplomaUpdateStatusCounts();
}

function diplomaUpdateStatusCounts() {
    var rows = Array.from(document.querySelectorAll('#diplomaTableBody tr[data-row-id]'));
    var print1 = 0;
    var print2 = 0;

    rows.forEach(function(row) {
        var selectEl = row.querySelector('.diploma-copy-select');
        var value = selectEl ? selectEl.value : row.getAttribute('data-copy-type');
        if (value === 'print-1') print1++;
        else if (value === 'print-2') print2++;
    });

    var print1El = document.getElementById('diplomaPrint1Count');
    var print2El = document.getElementById('diplomaPrint2Count');
    var totalEl = document.getElementById('diplomaStatusTotal');
    if (print1El) print1El.textContent = String(print1);
    if (print2El) print2El.textContent = String(print2);
    if (totalEl) totalEl.textContent = String(rows.length);
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
    document.getElementById('diplomaEditNumber').value = diplomaGetCellText(row, '.diploma-cell-number');
    document.getElementById('diplomaEditName').value = diplomaGetCellText(row, '.diploma-cell-name');
    document.getElementById('diplomaEditCourse').value = diplomaGetCellText(row, '.diploma-cell-program');
    document.getElementById('diplomaEditYear').value = diplomaGetCellText(row, '.diploma-cell-year');

    var registrarField = document.getElementById('diplomaEditRegistrar');
    var registrarTitleField = document.getElementById('diplomaEditRegistrarTitle');
    var presidentField = document.getElementById('diplomaEditPresident');
    var presidentTitleField = document.getElementById('diplomaEditPresidentTitle');
    var chairmanField = document.getElementById('diplomaEditChairman');
    var chairmanTitleField = document.getElementById('diplomaEditChairmanTitle');
    if (registrarField) registrarField.value = row.getAttribute('data-registrar-name') || DIPLOMA_DEFAULT_SIGNATORIES.registrarName;
    if (registrarTitleField) registrarTitleField.value = row.getAttribute('data-registrar-title') || DIPLOMA_DEFAULT_SIGNATORIES.registrarTitle;
    if (presidentField) presidentField.value = row.getAttribute('data-president-name') || DIPLOMA_DEFAULT_SIGNATORIES.presidentName;
    if (presidentTitleField) presidentTitleField.value = row.getAttribute('data-president-title') || DIPLOMA_DEFAULT_SIGNATORIES.presidentTitle;
    if (chairmanField) chairmanField.value = row.getAttribute('data-chairman-name') || DIPLOMA_DEFAULT_SIGNATORIES.chairmanName;
    if (chairmanTitleField) chairmanTitleField.value = row.getAttribute('data-chairman-title') || DIPLOMA_DEFAULT_SIGNATORIES.chairmanTitle;

    diplomaOpenModal('diplomaEditModal');
}

function diplomaSaveEdit() {
    var row = diplomaGetRow(diplomaCurrentRowId);
    if (!row) return;
    var numberCell = row.querySelector('.diploma-cell-number');
    var nameCell = row.querySelector('.diploma-cell-name');
    var programCell = row.querySelector('.diploma-cell-program');
    var yearCell = row.querySelector('.diploma-cell-year');

    if (numberCell) numberCell.textContent = (document.getElementById('diplomaEditNumber').value || '').trim();
    if (nameCell) {
        var updatedName = (document.getElementById('diplomaEditName').value || '').trim();
        nameCell.innerHTML = '<button type="button" class="doc-link-btn" onclick="diplomaOpenPreview(' + diplomaEscHtml(diplomaCurrentRowId) + ')">' + diplomaEscHtml(updatedName) + '</button>';
    }
    if (programCell) programCell.textContent = (document.getElementById('diplomaEditCourse').value || '').trim();
    if (yearCell) yearCell.textContent = (document.getElementById('diplomaEditYear').value || '').trim();

    var registrarField = document.getElementById('diplomaEditRegistrar');
    var registrarTitleField = document.getElementById('diplomaEditRegistrarTitle');
    var presidentField = document.getElementById('diplomaEditPresident');
    var presidentTitleField = document.getElementById('diplomaEditPresidentTitle');
    var chairmanField = document.getElementById('diplomaEditChairman');
    var chairmanTitleField = document.getElementById('diplomaEditChairmanTitle');
    if (registrarField) row.setAttribute('data-registrar-name', (registrarField.value || '').trim());
    if (registrarTitleField) row.setAttribute('data-registrar-title', (registrarTitleField.value || '').trim());
    if (presidentField) row.setAttribute('data-president-name', (presidentField.value || '').trim());
    if (presidentTitleField) row.setAttribute('data-president-title', (presidentTitleField.value || '').trim());
    if (chairmanField) row.setAttribute('data-chairman-name', (chairmanField.value || '').trim());
    if (chairmanTitleField) row.setAttribute('data-chairman-title', (chairmanTitleField.value || '').trim());

    diplomaSaveSignatoriesToServer(row);
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
    diplomaUpdateStatusCounts();
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

document.querySelectorAll('#diplomaTableBody .diploma-copy-select').forEach(function(selectEl) {
    diplomaHandleCopyChange(selectEl);
});
diplomaUpdateStatusCounts();
