/* honorable-dismissal.js - Honorable Dismissal form page logic */

var hdCurrentRowId = null;
var hdCurrentPreviewName = 'honorable-dismissal.html';
var hdPendingPrintedRowIds = [];
var hdIsMultiPreview = false;
var hdMultiPreviewRowIds = [];
var hdMultiPreviewSheets = [];

function hdCsrf() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function hdJson(url, options) {
    options = options || {};
    options.credentials = 'same-origin';
    options.headers = Object.assign({
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }, options.headers || {});

    return fetch(url, options).then(function(response) {
        return response.json().catch(function() { return {}; }).then(function(data) {
            if (!response.ok) throw data;
            if (data && data.success === false) throw data;
            return data;
        });
    });
}

function hdPost(url) {
    return hdJson(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': hdCsrf()
        }
    });
}

function hdPostJson(url, payload) {
    return hdJson(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': hdCsrf()
        },
        body: JSON.stringify(payload || {})
    });
}

function hdEsc(v) {
    return String(v || '').replace(/[&<>"']/g, function(c) {
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
}

function hdOrdinal(n) {
    n = Number(n) || 0;
    if (n <= 0) return '';
    var mod100 = n % 100;
    if (mod100 >= 11 && mod100 <= 13) return n + 'th';
    switch (n % 10) {
        case 1: return n + 'st';
        case 2: return n + 'nd';
        case 3: return n + 'rd';
        default: return n + 'th';
    }
}

function hdSheetUrl(rowId) {
    if (!rowId) return window.hdBlankSheetUrl || '';
    return String(window.hdSheetUrlTemplate || '').replace('__STUDENT__', encodeURIComponent(rowId));
}

function hdLoadSheetHtml(rowId) {
    return hdJson(hdSheetUrl(rowId)).then(function(data) {
        if (!data || !data.success || typeof data.html !== 'string') {
            throw { message: 'Unable to load Honorable Dismissal document.' };
        }
        return data.html;
    });
}

function hdRenderSheetHtml(html) {
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet) return;
    sheet.className = '';
    sheet.innerHTML = html;
}

function hdOpenPreview(rowId) {
    var data = hdGetRowData(rowId);
    if (!data) return;
    if (!data.hdNo) {
        alert('Student must be tagged For Dismissal before previewing HD.');
        return;
    }

    hdCurrentRowId = rowId;
    hdCurrentPreviewName = hdFileName(data);
    hdSetMultiMode(false);
    hdOpenPreviewModal();
    hdSetPreviewTitle('HONORABLE DISMISSAL PREVIEW');
    hdSetSheetLoading('Loading document...');

    hdLoadSheetHtml(rowId).then(function(html) {
        hdRenderSheetHtml(html);
    }).catch(function(error) {
        hdSetSheetLoading((error && error.message) || 'Unable to load Honorable Dismissal document.');
    });
}

function hdOpenBlankPreview() {
    hdCurrentRowId = null;
    hdCurrentPreviewName = 'honorable-dismissal-template.html';
    hdSetMultiMode(false);
    hdOpenPreviewModal();
    hdSetPreviewTitle('HONORABLE DISMISSAL PREVIEW (Blank Template)');
    hdSetSheetLoading('Loading blank template...');

    hdLoadSheetHtml(null).then(function(html) {
        hdRenderSheetHtml(html);
    }).catch(function(error) {
        hdSetSheetLoading((error && error.message) || 'Unable to load blank template.');
    });
}

function hdOpenPreviewSmart() {
    var rowIds = hdSelectedMonitoringRowIds();

    if (!rowIds.length) {
        hdOpenBlankPreview();
        return;
    }

    if (rowIds.length === 1) {
        hdOpenPreview(rowIds[0]);
        return;
    }

    hdOpenMultiPreview(rowIds);
}

function hdOpenMultiPreview(rowIds) {
    hdCurrentRowId = null;
    hdMultiPreviewRowIds = rowIds.slice();
    hdMultiPreviewSheets = [];
    hdCurrentPreviewName = 'honorable-dismissal-selected.html';
    hdSetMultiMode(true);
    hdOpenPreviewModal();
    hdSetPreviewTitle('HONORABLE DISMISSAL PREVIEW (' + rowIds.length + ' selected)');

    var sheet = document.getElementById('hdPreviewSheet');
    if (sheet) {
        sheet.className = 'hd-multi-stack';
        sheet.innerHTML = '<div class="hd-multi-loading">Loading ' + rowIds.length + ' record(s)...</div>';
    }

    Promise.all(rowIds.map(function(rowId) {
        return hdPrintableSheetForRow(rowId).then(function(html) {
            return { rowId: rowId, html: html, data: hdGetRowData(rowId), ok: true };
        }).catch(function(error) {
            return { rowId: rowId, error: error, ok: false };
        });
    })).then(function(results) {
        var succeeded = results.filter(function(result) { return result.ok; });
        var failed = results.filter(function(result) { return !result.ok; });

        var sheetEl = document.getElementById('hdPreviewSheet');
        if (!sheetEl) return;

        if (!succeeded.length) {
            sheetEl.innerHTML = '<div class="hd-multi-loading">Unable to load any of the selected records.</div>';
            return;
        }

        hdMultiPreviewSheets = succeeded.map(function(result) { return { rowId: result.rowId, html: result.html }; });

        sheetEl.className = 'hd-multi-stack';
        sheetEl.innerHTML = succeeded.map(function(result, index) {
            var data = result.data || {};
            var label = (index + 1) + '. ' + hdEsc(data.studentName || 'Student') +
                (data.studentNo ? ' &middot; ' + hdEsc(data.studentNo) : '');
            return '<div class="hd-multi-page">'
                + '<div class="hd-multi-page-label">' + label + '</div>'
                + result.html
                + '</div>';
        }).join('');

        if (failed.length) {
            alert(failed.length + ' of ' + rowIds.length + ' selected record(s) could not be loaded and were skipped.');
        }
    });
}

function hdSetMultiMode(isMulti) {
    hdIsMultiPreview = !!isMulti;
    if (!hdIsMultiPreview) {
        hdMultiPreviewRowIds = [];
        hdMultiPreviewSheets = [];
    }
}

function hdSetPreviewTitle(text) {
    var title = document.getElementById('hdPreviewTitle');
    if (title) title.textContent = text;
}

function hdOpenPreviewModal() {
    var modal = document.getElementById('hdPreviewModal');
    if (modal) modal.style.display = 'flex';
    document.body.classList.add('hd-preview-open');
}

function hdSetSheetLoading(message) {
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet) return;
    sheet.className = '';
    sheet.innerHTML = '<div class="hd-sheet-loading">' + hdEsc(message) + '</div>';
}

function hdClosePreview() {
    var m = document.getElementById('hdPreviewModal');
    if (m) m.style.display = 'none';
    document.body.classList.remove('hd-preview-open');
    hdSetMultiMode(false);
}

function hdCleanPrintSheet(sourceSheet) {
    return sourceSheet.innerHTML;
}

function hdPrintPreview() {
    if (hdIsMultiPreview) {
        var multiRowIds = hdMultiPreviewRowIds.slice();
        if (!multiRowIds.length || !hdMultiPreviewSheets.length) return;

        hdIssueRows(multiRowIds).then(function(ok) {
            if (!ok) return;
            hdPrintSheets(
                hdMultiPreviewSheets.map(function(item) { return item.html; }),
                hdMultiPreviewSheets.map(function(item) { return item.rowId; })
            );
        });
        return;
    }

    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet || !sheet.querySelector('.hd-sheet')) return;
    var rowIds = hdCurrentRowId ? [hdCurrentRowId] : [];
    var printNow = function() { hdPrintSheets([hdCleanPrintSheet(sheet)], rowIds); };
    if (!rowIds.length) {
        printNow();
        return;
    }
    hdIssueRows(rowIds).then(function(ok) {
        if (ok) printNow();
    });
}

function hdPrintSheets(list, rowIds) {
    var pc = document.getElementById('hdPrintContainer');
    if (!pc || !list || !list.length) return;
    hdPendingPrintedRowIds = (rowIds || []).filter(Boolean);
    pc.setAttribute('data-hd-print-count', String(list.length));
    pc.setAttribute('data-hd-print-row-ids', hdPendingPrintedRowIds.join(','));
    pc.innerHTML = list.map(function(h, i) {
        var cls = i < list.length - 1 ? ' hd-print-page-break' : '';
        return '<div class="hd-print-shell' + cls + '" data-hd-print-index="' + i + '">' + h + '</div>';
    }).join('');
    document.body.classList.add('hd-printing');
    setTimeout(function() { window.print(); }, 120);
}

function hdFileName(data) {
    var base = 'honorable-dismissal';
    if (data && data.studentNo) {
        base += '-' + data.studentNo;
    } else if (data && data.studentName) {
        base += '-' + data.studentName;
    }

    return base.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') + '.html';
}

function hdDownloadHtml(html, fileName) {
    if (!html) return;

    var css = document.querySelector('link[href*="forms.css"]');
    var documentHtml = '<!doctype html><html><head><meta charset="utf-8">'
        + '<title>Honorable Dismissal</title>'
        + '<link rel="stylesheet" href="' + hdEsc(css ? css.href : '') + '">'
        + '<style>@page{size:legal portrait;margin:0}body{background:#fff;margin:0}.hd-sheet{box-shadow:none;border:0;margin:0 auto}</style>'
        + '</head><body>' + html + '</body></html>';

    var blob = new Blob([documentHtml], { type: 'text/html;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = fileName || 'honorable-dismissal.html';
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(function() { URL.revokeObjectURL(url); }, 1000);
}

function hdDownloadPreview() {
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet || !sheet.querySelector('.hd-sheet')) return;
    hdDownloadHtml(hdCleanPrintSheet(sheet), hdCurrentPreviewName);
}

function hdDownloadRow(rowId) {
    var data = hdGetRowData(rowId);
    if (!data) return;
    if (!data.hdNo) { alert('Student must be tagged For Dismissal before downloading HD.'); return; }

    hdLoadSheetHtml(rowId).then(function(html) {
        hdDownloadHtml(html, hdFileName(data));
    }).catch(function(error) {
        alert((error && error.message) || 'Unable to download HD.');
    });
}

function hdIssueAllSelected() {
    var pendingIds = hdSelectedMonitoringRowIds();

    if (!pendingIds.length) {
        alert('Select at least one Pending for Dismissal record.');
        return;
    }

    if (!confirm('Mark ' + pendingIds.length + ' selected student(s) as Issued?')) {
        return;
    }

    hdBulkIssueRows(pendingIds).then(function(ok) {
        if (!ok) return;
        alert('Selected records have been marked as Issued.');
        window.location.reload();
    });
}

function hdOpenPreviewFromSelection() {
    var checked = document.querySelector('#hdTableBody .hd-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#hdTableBody tr[data-row-id]');
    if (!row) return;
    var rowId = row.getAttribute('data-row-id');
    hdOpenPreview(rowId);
}

function hdIssueRows(rowIds) {
    rowIds = (rowIds || []).filter(Boolean);
    if (!rowIds.length) return Promise.resolve(false);
    var template = String(window.hdIssueUrlTemplate || '');
    if (!template) return Promise.resolve(false);

    return Promise.all(rowIds.map(function(rowId) {
        return hdPost(template.replace('__STUDENT__', rowId));
    })).then(function(results) {
        var failed = results.find(function(result) { return !result || !result.success; });
        if (failed) {
            alert(failed.message || 'Student must be tagged For Dismissal before printing HD.');
            return false;
        }
        return true;
    }).catch(function() {
        alert('Network error while marking HD as issued.');
        return false;
    });
}

function hdPrintableSheetForRow(rowId) {
    return hdLoadSheetHtml(rowId);
}

function hdSelectedMonitoringRows() {
    return hdEligibleMonitoringRows().filter(function(row) {
        var checkbox = row.querySelector('.hd-row-select');
        return !!checkbox && checkbox.checked;
    });
}

function hdSelectedMonitoringRowIds() {
    return hdSelectedMonitoringRows().map(function(row) {
        return row.getAttribute('data-row-id');
    }).filter(Boolean);
}

function hdEligibleMonitoringRows() {
    return Array.from(document.querySelectorAll('#hdTableBody tr[data-row-id]')).filter(function(row) {
        var checkbox = row.querySelector('.hd-row-select');
        return !!checkbox && !checkbox.disabled && hdIsMonitoringRowPrintable(row);
    });
}

function hdSetMonitoringRowChecked(row, checked) {
    var checkbox = row ? row.querySelector('.hd-row-select') : null;
    if (!checkbox || checkbox.disabled || !hdIsMonitoringRowPrintable(row)) {
        return false;
    }

    checkbox.checked = !!checked;
    return true;
}

function hdMarkMonitoringRowsPrinted(rowIds) {
    (rowIds || []).forEach(function(rowId) {
        var row = hdGetRow(rowId);
        if (!row) return;

        row.setAttribute('data-hd-status', 'issued');
        var nextCount = (parseInt(row.getAttribute('data-hd-issuance-count'), 10) || 0) + 1;
        row.setAttribute('data-hd-issuance-count', String(nextCount));

        var cells = row.querySelectorAll('td');
        if (cells[6]) cells[6].textContent = 'Issued';
        if (cells[7]) cells[7].textContent = row.getAttribute('data-hd-date') || new Date().toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });

        if (cells[1]) {
            var badge = cells[1].querySelector('.hd-ordinal-badge');
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'hd-ordinal-badge';
                cells[1].insertBefore(badge, cells[1].firstChild);
            }
            badge.textContent = hdOrdinal(nextCount);
        }

        var checkbox = row.querySelector('.hd-row-select');
        if (checkbox) {
            checkbox.checked = false;
        }
    });

    hdSyncSelectAll();
}

function hdBulkIssueRows(rowIds) {
    rowIds = (rowIds || []).filter(Boolean);
    if (!rowIds.length) return Promise.resolve(false);
    var url = String(window.hdBulkIssueUrl || '');
    if (!url) return Promise.resolve(false);

    return hdPostJson(url, { student_ids: rowIds }).then(function(result) {
        if (!result || !result.success) {
            alert((result && result.message) || 'Unable to process selected dismissal records.');
            return false;
        }
        return true;
    }).catch(function(error) {
        var detail = error && error.errors ? Object.keys(error.errors).map(function(key) {
            return error.errors[key].join(' ');
        }).join('\n') : '';
        alert(detail || (error && error.message) || 'Network error while processing selected dismissal records.');
        return false;
    });
}

function hdFilterTable(query) {
    var q = String(query || '').toLowerCase().trim();
    document.querySelectorAll('#hdTableBody tr').forEach(function(row) {
        var text = (row.textContent || '').toLowerCase();
        row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
    });
    hdSyncSelectAll();
}

function hdCellText(cell, innerSelector) {
    if (!cell) return '';
    var inner = innerSelector ? cell.querySelector(innerSelector) : null;
    return (inner || cell).textContent.trim();
}

function hdGetRowData(rowId) {
    var row = hdGetRow(rowId);
    if (!row) return null;
    var cells = row.querySelectorAll('td');
    var studentName = (cells[2] ? cells[2].textContent : '').trim();
    var program = (cells[3] ? cells[3].textContent : '').trim();

    return {
        studentNo: hdCellText(cells[1], '.hd-student-no'),
        studentName: studentName,
        program: program,
        year: '',
        section: '',
        hdNo: (row.getAttribute('data-hd-no') || '').trim(),
        hdDate: (row.getAttribute('data-hd-date') || '').trim(),
        hdStatus: (row.getAttribute('data-hd-status') || '').trim(),
        schoolYear: (row.getAttribute('data-school-year') || '').trim(),
        semester: (row.getAttribute('data-semester') || '').trim()
    };
}

function hdIsVisibleRow(row) {
    return !!row && row.style.display !== 'none';
}

function hdIsMonitoringRowPrintable(row) {
    return !!row && hdIsVisibleRow(row);
}

function hdToggleSelectAll(s) {
    var checked = !!(s && s.checked);
    hdEligibleMonitoringRows().forEach(function(row) {
        hdSetMonitoringRowChecked(row, checked);
    });
    hdSyncSelectAll();
}
function hdSyncSelectAll() {
    var h=document.getElementById('hdSelectAll');if(!h)return;
    var items=hdEligibleMonitoringRows().map(function(row){
        var checkbox=row.querySelector('.hd-row-select');
        return checkbox || null;
    }).filter(Boolean);
    var t=items.length,c=0;items.forEach(function(x){if(x.checked)c++});h.checked=t>0&&c===t;h.indeterminate=c>0&&c<t;h.disabled=t===0;
}
function hdCloseMenus(){document.querySelectorAll('.apst-dropdown.open').forEach(function(m){m.classList.remove('open','drop-up');m.style.top='';m.style.left='';m.style.right='';m.style.bottom=''});}
function hdToggleMenu(id,trig){var m=document.getElementById(id);if(!m||!trig)return;var o=m.classList.contains('open');hdCloseMenus();if(o)return;var r=trig.getBoundingClientRect();m.style.left='auto';m.style.right=(window.innerWidth-r.left+4)+'px';if(window.innerHeight-r.bottom<120){m.classList.add('drop-up');m.style.top='auto';m.style.bottom=(window.innerHeight-r.bottom)+'px';}else{m.style.top=r.top+'px';m.style.bottom='auto';}m.classList.add('open');}
function hdOpenModal(id){hdCloseMenus();var m=document.getElementById(id);if(m)m.style.display='flex';}
function hdCloseModal(id){var m=document.getElementById(id);if(m)m.style.display='none';}
function hdGetRow(id){return document.querySelector('tr[data-row-id="'+id+'"]');}
function hdOpenEdit(id){var r=hdGetRow(id);if(!r)return;hdCurrentRowId=id;var c=r.querySelectorAll('td');document.getElementById('hdEditNumber').value=hdCellText(c[1],'.hd-student-no');document.getElementById('hdEditName').value=(c[2]?c[2].textContent:'').trim();document.getElementById('hdEditCourse').value=(c[3]?c[3].textContent:'').trim();document.getElementById('hdEditYear').value=(c[4]?c[4].textContent:'').trim();hdOpenModal('hdEditModal');}
function hdSaveEdit(){var r=hdGetRow(hdCurrentRowId);if(!r)return;var c=r.querySelectorAll('td');if(c[1]){var numEl=c[1].querySelector('.hd-student-no')||c[1];numEl.textContent=(document.getElementById('hdEditNumber').value||'').trim();}if(c[2])c[2].textContent=(document.getElementById('hdEditName').value||'').trim();if(c[3])c[3].textContent=(document.getElementById('hdEditCourse').value||'').trim();if(c[4])c[4].textContent=(document.getElementById('hdEditYear').value||'').trim();hdCloseModal('hdEditModal');}
function hdOpenDelete(id){hdCurrentRowId=id;hdOpenModal('hdDeleteModal');}
function hdConfirmDelete(){var r=hdGetRow(hdCurrentRowId);if(r)r.remove();hdSyncSelectAll();hdCloseModal('hdDeleteModal');}

function hdBindSelectionControls() {
    var table = document.getElementById('hdTable');
    if (table && !table.getAttribute('data-hd-selection-bound')) {
        table.setAttribute('data-hd-selection-bound', '1');

        table.addEventListener('change', function(event) {
            var target = event.target;
            if (!target || target.type !== 'checkbox') return;

            if (target.id === 'hdSelectAll') {
                hdToggleSelectAll(target);
                return;
            }

            if (target.classList.contains('hd-row-select')) {
                if (target.disabled) {
                    target.checked = false;
                }
                hdSyncSelectAll();
            }
        });

        table.addEventListener('click', function(event) {
            var target = event.target;
            if (!target) return;

            if (target.type === 'checkbox') {
                return;
            }

            var cell = target.closest('td, th');
            if (!cell || !table.contains(cell) || cell.cellIndex !== 0) {
                return;
            }

            var headerCheckbox = cell.querySelector('#hdSelectAll');
            if (headerCheckbox && !headerCheckbox.disabled) {
                headerCheckbox.checked = !headerCheckbox.checked;
                hdToggleSelectAll(headerCheckbox);
                event.preventDefault();
                return;
            }

            var row = cell.closest('tr[data-row-id]');
            var checkbox = row ? row.querySelector('.hd-row-select') : null;
            if (checkbox && !checkbox.disabled && hdIsMonitoringRowPrintable(row)) {
                checkbox.checked = !checkbox.checked;
                hdSyncSelectAll();
                event.preventDefault();
            }
        });
    }
}

window.addEventListener('afterprint', function() {
    document.body.classList.remove('hd-printing');

    var printedIds = hdPendingPrintedRowIds;
    hdPendingPrintedRowIds = [];

    var pc = document.getElementById('hdPrintContainer');
    if (pc) {
        pc.innerHTML = '';
        pc.removeAttribute('data-hd-print-count');
        pc.removeAttribute('data-hd-print-row-ids');
    }

    if (!printedIds.length) return;

    hdMarkMonitoringRowsPrinted(printedIds);
});

document.addEventListener('click',function(e){var t=e.target.closest('[data-hd-menu-toggle]');if(t){e.stopPropagation();hdToggleMenu(t.getAttribute('data-hd-menu-toggle'),t);return;}if(!e.target.closest('.apst-dropdown'))hdCloseMenus();});
window.addEventListener('scroll',hdCloseMenus,true);

document.addEventListener('DOMContentLoaded', function() {
    hdBindSelectionControls();
    hdSyncSelectAll();
    var tableBody = document.getElementById('hdTableBody');
    var selectedRowId = tableBody ? (tableBody.getAttribute('data-selected-row-id') || '').trim() : '';
    if (selectedRowId && hdGetRow(selectedRowId)) {
        hdOpenPreview(selectedRowId);
    }
});
