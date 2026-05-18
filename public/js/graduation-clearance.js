/* graduation-clearance.js - fixed blank form preview/print */

function gcEsc(v) {
    return String(v || '').replace(/[&<>"']/g, function(c) {
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
}

function gcGetRowDataFromRow(row) {
    if (!row) return null;
    var cells = row.querySelectorAll('td');
    var rowType = String(row.getAttribute('data-form-type') || '').trim().toLowerCase();
    var typeFromCell = (cells[4] ? cells[4].textContent : '').trim().toLowerCase();
    var normalizedType = rowType || (typeFromCell.indexOf('board') !== -1 && typeFromCell.indexOf('non') === -1 ? 'board' : 'non-board');
    return {
        studentNo: (cells[1] ? cells[1].textContent : '').trim(),
        studentName: (cells[2] ? cells[2].textContent : '').trim(),
        program: (cells[3] ? cells[3].textContent : '').trim(),
        college: (row.getAttribute('data-college') || '').trim(),
        email: (row.getAttribute('data-email') || '').trim(),
        phone: (row.getAttribute('data-phone') || '').trim(),
        date: (row.getAttribute('data-date') || '').trim(),
        year: (cells[5] ? cells[5].textContent : '').trim(),
        section: (cells[6] ? cells[6].textContent : '').trim(),
        formType: normalizedType
    };
}

function gcBuildTemplate(data) {
    var model = data || {};
    var isBoardType = String(model.formType || 'non-board') === 'board';

    var requirements = [
        {
            text: 'Completed all required course work<br>(NSTP, PE, All other mandated courses)',
            office: 'College Dean'
        },
        {
            text: 'Returned all borrowed library materials',
            office: 'University Library'
        },
        {
            text: 'Returned borrowed equipment',
            office: 'MIS Office'
        },
        {
            text: 'Cleared all financial obligations',
            office: 'Finance Office'
        },
        {
            text: 'Settled any outstanding disciplinary issues',
            office: 'Student Success Office'
        },
        {
            text: 'Settled all obligations',
            office: 'Supreme Student Council'
        },
        {
            text: 'Cleared research requirements',
            office: 'Research Department'
        },
        {
            text: 'Accomplished and Submitted Student\'s<br>Evaluation of Implemented Intervention Form<br>For AY 2024 - 2025',
            office: 'University Research Office',
            showCheck: isBoardType
        },
        {
            text: 'Submitted all required documents',
            office: 'Office of the University Registrar'
        }
    ];

    var rows = requirements.map(function(item) {
        var withCheck = item.showCheck !== false;
        var isResearchDepartment = item.office === 'Research Department';
        var rowClass = withCheck ? 'gc-check-row' : 'gc-check-row gc-check-row-office-only';
        if (isResearchDepartment) {
            rowClass += ' gc-check-row-research';
        }
        var leftHtml = withCheck
            ? '<span class="gc-check-box"></span><span class="gc-check-text">' + item.text + '</span>'
            : '';

        return '' +
            '<div class="' + rowClass + '">' +
                '<div class="gc-check-left">' +
                    leftHtml +
                '</div>' +
                '<div class="gc-check-gap"></div>' +
                '<div class="gc-check-office">' + item.office + '</div>' +
                '<div class="gc-check-line-wrap"><span class="gc-office-colon">:</span><span class="gc-check-line"></span></div>' +
            '</div>';
    }).join('');

    return '' +
        '<div class="gc-header-space"></div>' +
        '<div class="gc-title">Student Information</div>' +

        '<div class="gc-line-row gc-line-row-main">' +
            '<span class="gc-label gc-label-fullname">Full Name :</span>' +
            '<span class="gc-blank gc-blank-name">' + gcEsc(model.studentName) + '</span>' +
            '<span class="gc-label gc-label-id">Student ID Number:</span>' +
            '<span class="gc-blank gc-blank-id">' + gcEsc(model.studentNo) + '</span>' +
        '</div>' +
        '<div class="gc-subhint gc-subhint-name">(Surname, Given Name, Middle Name, Extension Name)</div>' +

        '<div class="gc-line-row gc-line-row-dual">' +
            '<span class="gc-label">College :</span>' +
            '<span class="gc-blank gc-blank-college">' + gcEsc(model.college) + '</span>' +
            '<span class="gc-label gc-label-right">Program:</span>' +
            '<span class="gc-blank gc-blank-program">' + gcEsc(model.program) + '</span>' +
        '</div>' +

        '<div class="gc-line-row gc-line-row-dual">' +
            '<span class="gc-label">Contact Email:</span>' +
            '<span class="gc-blank gc-blank-email">' + gcEsc(model.email) + '</span>' +
            '<span class="gc-label gc-label-right">Contact Phone Number:</span>' +
            '<span class="gc-blank gc-blank-phone">' + gcEsc(model.phone) + '</span>' +
        '</div>' +

        '<div class="gc-req-title">Checklist of Graduation Requirements:</div>' +
        '<div class="gc-req-sub">Please check the box next to each requirement and sign once it has been completed and verified:</div>' +

        '<div class="gc-checklist">' + rows + '</div>' +

        '<div class="gc-declaration-title">Student Declaration:</div>' +
        '<div class="gc-declaration">' +
            '<span>I, </span><span class="gc-blank gc-blank-decl">' + gcEsc(model.studentName) + '</span>' +
            '<span> confirm that I have met all the graduation requirements as outlined above and request clearance to graduate. I understand that my graduation status is contingent upon successfully meeting these requirements</span>' +
        '</div>' +

        '<div class="gc-sign-row">' +
            '<span class="gc-label">Student\'s Signature:</span><span class="gc-blank gc-blank-sign"></span>' +
            '<span class="gc-label gc-label-right">Date Submitted:</span><span class="gc-blank gc-blank-date">' + gcEsc(model.date) + '</span>' +
        '</div>' +
        '<div class="gc-sign-row">' +
            '<span class="gc-label">Received by:</span><span class="gc-blank gc-blank-sign"></span>' +
            '<span class="gc-label gc-label-right">Date Received:</span><span class="gc-blank gc-blank-date"></span>' +
        '</div>';
}

function gcOpenPreview() {
    var firstRow = document.querySelector('#gcTableBody tr');
    var firstData = gcGetRowDataFromRow(firstRow);
    var previewData = firstData || { formType: 'non-board' };

    var sheet = document.getElementById('gcPreviewSheet');
    if (!sheet) return;
    sheet.innerHTML = gcBuildTemplate(previewData);

    var wrap = document.querySelector('#gcPreviewModal .gc-preview-wrap');
    if (wrap) {
        wrap.scrollTop = 0;
        wrap.scrollLeft = 0;
    }

    var modal = document.getElementById('gcPreviewModal');
    if (modal) modal.style.display = 'flex';
    document.body.classList.add('gc-preview-open');
}

function gcOpenPreviewFromRow(trigger) {
    var row = trigger ? trigger.closest('tr') : null;
    var rowData = gcGetRowDataFromRow(row);
    var sheet = document.getElementById('gcPreviewSheet');
    if (!sheet) return;

    sheet.innerHTML = gcBuildTemplate(rowData || { formType: 'non-board' });

    var wrap = document.querySelector('#gcPreviewModal .gc-preview-wrap');
    if (wrap) {
        wrap.scrollTop = 0;
        wrap.scrollLeft = 0;
    }

    var modal = document.getElementById('gcPreviewModal');
    if (modal) modal.style.display = 'flex';
    document.body.classList.add('gc-preview-open');
}

function gcOpenPreviewFromSelection() {
    var checked = document.querySelector('#gcTableBody .gc-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#gcTableBody tr');
    var rowData = gcGetRowDataFromRow(row);
    var sheet = document.getElementById('gcPreviewSheet');
    if (!sheet) return;

    sheet.innerHTML = gcBuildTemplate(rowData || { formType: 'non-board' });

    var wrap = document.querySelector('#gcPreviewModal .gc-preview-wrap');
    if (wrap) {
        wrap.scrollTop = 0;
        wrap.scrollLeft = 0;
    }

    var modal = document.getElementById('gcPreviewModal');
    if (modal) modal.style.display = 'flex';
    document.body.classList.add('gc-preview-open');
}

function gcOpenBlankPreview() {
    var sheet = document.getElementById('gcPreviewSheet');
    if (!sheet) return;

    sheet.innerHTML = gcBuildTemplate({ formType: 'non-board' });

    var wrap = document.querySelector('#gcPreviewModal .gc-preview-wrap');
    if (wrap) {
        wrap.scrollTop = 0;
        wrap.scrollLeft = 0;
    }

    var modal = document.getElementById('gcPreviewModal');
    if (modal) modal.style.display = 'flex';
    document.body.classList.add('gc-preview-open');
}

function gcClosePreview() {
    var modal = document.getElementById('gcPreviewModal');
    if (modal) modal.style.display = 'none';
    document.body.classList.remove('gc-preview-open');
}

function gcPrintSheet(html) {
    var container = document.getElementById('gcPrintContainer');
    if (!container) return;
    container.innerHTML = '<div class="gc-sheet">' + html + '</div>';
    document.body.classList.add('gc-printing');
    setTimeout(function() { window.print(); }, 120);
}

function gcPrintPreview() {
    var sheet = document.getElementById('gcPreviewSheet');
    var html = sheet ? sheet.innerHTML : gcBuildTemplate({ formType: 'non-board' });
    if (sheet) {
        sheet.innerHTML = html;
    }
    gcPrintSheet(html);
}

function gcPrintSelected() {
    var selected = document.querySelectorAll('#gcTableBody .gc-row-select:checked');
    if (!selected.length) {
        alert('Select at least one record to print.');
        return;
    }

    var html = '';
    selected.forEach(function(cb, i) {
        var row = cb.closest('tr');
        var rowData = gcGetRowDataFromRow(row) || { formType: 'non-board' };
        var pageBreak = i < selected.length - 1 ? ' gc-print-page-break' : '';
        html += '<div class="gc-sheet' + pageBreak + '">' + gcBuildTemplate(rowData) + '</div>';
    });

    var container = document.getElementById('gcPrintContainer');
    if (!container) return;
    container.innerHTML = html;
    document.body.classList.add('gc-printing');
    setTimeout(function() { window.print(); }, 120);
}

function gcToggleSelectAll(source) {
    var checked = !!(source && source.checked);
    document.querySelectorAll('#gcTableBody .gc-row-select').forEach(function(cb) {
        cb.checked = checked;
    });
    gcSyncSelectAll();
}

function gcSyncSelectAll() {
    var header = document.getElementById('gcSelectAll');
    if (!header) return;
    var rows = document.querySelectorAll('#gcTableBody .gc-row-select');
    var total = rows.length;
    var checked = 0;

    rows.forEach(function(cb) {
        if (cb.checked) checked++;
    });

    header.checked = total > 0 && checked === total;
    header.indeterminate = checked > 0 && checked < total;
}

function gcPrintForm() {
    var checked = document.querySelector('#gcTableBody .gc-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#gcTableBody tr');
    var rowData = gcGetRowDataFromRow(row) || { formType: 'non-board' };
    gcPrintSheet(gcBuildTemplate(rowData));
}

function gcFilterTable(query) {
    var q = String(query || '').toLowerCase().trim();
    document.querySelectorAll('#gcTableBody tr').forEach(function(row) {
        var text = (row.textContent || '').toLowerCase();
        row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
    });
    gcSyncSelectAll();
}

window.addEventListener('afterprint', function() {
    document.body.classList.remove('gc-printing');
    var container = document.getElementById('gcPrintContainer');
    if (container) container.innerHTML = '';
});

document.addEventListener('DOMContentLoaded', function() {
    var tableBody = document.getElementById('gcTableBody');
    var selectedRowId = tableBody ? (tableBody.getAttribute('data-selected-row-id') || '').trim() : '';
    var selectedRow = selectedRowId ? document.querySelector('#gcTableBody tr[data-row-id="' + selectedRowId + '"]') : null;
    if (selectedRow) {
        gcOpenPreviewFromRow(selectedRow.querySelector('.doc-link-btn'));
    }
});
