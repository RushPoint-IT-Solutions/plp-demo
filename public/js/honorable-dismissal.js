/* honorable-dismissal.js — Honorable Dismissal form page logic */

var hdCurrentRowId = null;
var hdCurrentPreviewName = 'honorable-dismissal';

function hdCsrf() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function hdPost(url) {
    return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': hdCsrf()
        }
    }).then(function(response) { return response.json(); });
}

function hdEsc(v) {
    return String(v || '').replace(/[&<>"']/g, function(c) {
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
}

function hdBuildTemplate(data, meta) {
    var now = new Date();
    var dateStr = meta.hdDate || (now.toLocaleDateString('en-US', { month:'long', day:'numeric', year:'numeric' }));
    var hdNo = meta.hdNo || '';

    return '<div class="hd-simple-form">' +
        '<div class="hd-simple-top">' +
            '<div><span>HD No.</span><strong>' + hdEsc(hdNo) + '</strong></div>' +
            '<div><span>Date</span><strong>' + hdEsc(dateStr) + '</strong></div>' +
        '</div>' +
        '<div class="hd-simple-grid">' +
            '<div><span>Name of Student</span><strong>' + hdEsc(meta.studentName) + '</strong></div>' +
            '<div><span>Program</span><strong>' + hdEsc(meta.program) + '</strong></div>' +
            '<div><span>Student No.</span><strong>' + hdEsc(meta.studentNo) + '</strong></div>' +
        '</div>' +
        '<div class="hd-simple-signature">' +
            '<strong>FEDERICO G. NUEVA</strong>' +
            '<span>University Registrar</span>' +
        '</div>' +
    '</div>';
}

function hdGetRowData(rowId) {
    var row = hdGetRow(rowId);
    if (!row) return null;
    var cells = row.querySelectorAll('td');
    var studentName = (cells[2] ? cells[2].textContent : '').trim();
    var program = (cells[3] ? cells[3].textContent : '').trim();

    return {
        studentNo: (cells[1] ? cells[1].textContent : '').trim(),
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

function hdOpenPreview(rowId) {
    var data = hdGetRowData(rowId);
    if (!data) return;
    if (!data.hdNo) {
        alert('Student must be tagged For Dismissal before previewing HD.');
        return;
    }
    var meta = {
        studentNo: data.studentNo,
        studentName: data.studentName.toUpperCase(),
        program: data.program.toUpperCase(),
        hdNo: data.hdNo,
        hdDate: data.hdDate
    };
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet) return;
    sheet.innerHTML = hdBuildTemplate(data, meta);
    hdCurrentRowId = rowId;
    hdCurrentPreviewName = hdFileName(data);
    document.getElementById('hdPreviewModal').style.display = 'flex';
    document.body.classList.add('hd-preview-open');
}

function hdClosePreview() {
    var m = document.getElementById('hdPreviewModal');
    if (m) m.style.display = 'none';
    document.body.classList.remove('hd-preview-open');
}

function hdPrintPreview() {
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet) return;
    hdIssueRows([hdCurrentRowId]).then(function(ok) {
        if (ok) hdPrintSheets([sheet.innerHTML]);
    });
}

function hdPrintSheets(list) {
    var pc = document.getElementById('hdPrintContainer');
    if (!pc || !list || !list.length) return;
    pc.innerHTML = list.map(function(h, i) {
        var cls = i < list.length - 1 ? ' hd-print-page-break' : '';
        return '<div class="hd-sheet' + cls + '">' + h + '</div>';
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

    var documentHtml = '<!doctype html><html><head><meta charset="utf-8">'
        + '<title>Honorable Dismissal</title>'
        + '<link rel="stylesheet" href="' + hdEsc(document.querySelector('link[href*=\"forms.css\"]') ? document.querySelector('link[href*=\"forms.css\"]').href : '') + '">'
        + '<style>@page{size:portrait;margin:8mm}body{background:#fff;margin:0}.hd-sheet{margin:0 auto}</style>'
        + '</head><body><div class="hd-sheet">' + html + '</div></body></html>';

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
    if (!sheet) return;
    hdDownloadHtml(sheet.innerHTML, hdCurrentPreviewName);
}

function hdDownloadRow(rowId) {
    var data = hdGetRowData(rowId);
    if (!data) return;
    if (!data.hdNo) { alert('Student must be tagged For Dismissal before downloading HD.'); return; }
    var meta = {
        studentNo: data.studentNo,
        studentName: data.studentName.toUpperCase(),
        program: data.program.toUpperCase(),
        hdNo: data.hdNo,
        hdDate: data.hdDate
    };
    hdDownloadHtml(hdBuildTemplate(data, meta), hdFileName(data));
}

function hdPrintSelected() {
    var sel = Array.from(document.querySelectorAll('#hdTableBody .hd-row-select:checked'));
    if (!sel.length) { alert('Select at least one record to print.'); return; }
    var rowIds = sel.map(function(cb) {
        var row = cb.closest('tr');
        return row ? row.getAttribute('data-row-id') : null;
    }).filter(Boolean);
    var sheets = sel.map(function(cb) {
        var row = cb.closest('tr');
        var rid = row ? row.getAttribute('data-row-id') : null;
        if (!rid) return '';
        var data = hdGetRowData(rid);
        var meta = data ? {
            studentNo: data.studentNo,
            studentName: data.studentName.toUpperCase(),
            program: data.program.toUpperCase(),
            hdNo: data.hdNo,
            hdDate: data.hdDate
        } : {};
        return data ? hdBuildTemplate(data, meta) : '';
    }).filter(Boolean);
    hdIssueRows(rowIds).then(function(ok) {
        if (ok) hdPrintSheets(sheets);
    });
}

function hdOpenPreviewFromSelection() {
    var checked = document.querySelector('#hdTableBody .hd-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#hdTableBody tr[data-row-id]');
    if (!row) return;
    var rowId = row.getAttribute('data-row-id');
    hdOpenPreview(rowId);
}

function hdOpenBlankPreview() {
    alert('Select a student tagged For Dismissal to preview HD.');
}

function hdTagForDismissal(studentId) {
    var url = String(window.hdTagUrlTemplate || '').replace('__STUDENT__', studentId);
    if (!url) return;
    hdPost(url).then(function(data) {
        if (!data || !data.success) {
            alert((data && data.message) || 'Unable to tag student for dismissal.');
            return;
        }
        window.location.reload();
    }).catch(function() {
        alert('Network error while tagging student.');
    });
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

function hdFilterTable(query) {
    var q = String(query || '').toLowerCase().trim();
    document.querySelectorAll('#hdTableBody tr').forEach(function(row) {
        var text = (row.textContent || '').toLowerCase();
        row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
    });
    hdSyncSelectAll();
}

window.addEventListener('afterprint', function() {
    document.body.classList.remove('hd-printing');
    document.body.classList.remove('hd-preview-open');
    var pc = document.getElementById('hdPrintContainer');
    if (pc) pc.innerHTML = '';
});

/* ── Table helpers ──────────────────────────────────────── */
function hdToggleSelectAll(s) { document.querySelectorAll('#hdTableBody .hd-row-select').forEach(function(c){c.checked=!!s.checked});hdSyncSelectAll(); }
function hdSyncSelectAll() {
    var h=document.getElementById('hdSelectAll'),items=document.querySelectorAll('#hdTableBody .hd-row-select');if(!h)return;
    var t=items.length,c=0;items.forEach(function(x){if(x.checked)c++});h.checked=t>0&&c===t;h.indeterminate=c>0&&c<t;
}
function hdCloseMenus(){document.querySelectorAll('.apst-dropdown.open').forEach(function(m){m.classList.remove('open','drop-up');m.style.top='';m.style.left='';m.style.right='';m.style.bottom=''});}
function hdToggleMenu(id,trig){var m=document.getElementById(id);if(!m||!trig)return;var o=m.classList.contains('open');hdCloseMenus();if(o)return;var r=trig.getBoundingClientRect();m.style.left='auto';m.style.right=(window.innerWidth-r.left+4)+'px';if(window.innerHeight-r.bottom<120){m.classList.add('drop-up');m.style.top='auto';m.style.bottom=(window.innerHeight-r.bottom)+'px';}else{m.style.top=r.top+'px';m.style.bottom='auto';}m.classList.add('open');}
function hdOpenModal(id){hdCloseMenus();var m=document.getElementById(id);if(m)m.style.display='flex';}
function hdCloseModal(id){var m=document.getElementById(id);if(m)m.style.display='none';}
function hdGetRow(id){return document.querySelector('tr[data-row-id="'+id+'"]');}
function hdOpenEdit(id){var r=hdGetRow(id);if(!r)return;hdCurrentRowId=id;var c=r.querySelectorAll('td');document.getElementById('hdEditNumber').value=(c[1]?c[1].textContent:'').trim();document.getElementById('hdEditName').value=(c[2]?c[2].textContent:'').trim();document.getElementById('hdEditCourse').value=(c[3]?c[3].textContent:'').trim();document.getElementById('hdEditYear').value=(c[4]?c[4].textContent:'').trim();hdOpenModal('hdEditModal');}
function hdSaveEdit(){var r=hdGetRow(hdCurrentRowId);if(!r)return;var c=r.querySelectorAll('td');if(c[1])c[1].textContent=(document.getElementById('hdEditNumber').value||'').trim();if(c[2])c[2].textContent=(document.getElementById('hdEditName').value||'').trim();if(c[3])c[3].textContent=(document.getElementById('hdEditCourse').value||'').trim();if(c[4])c[4].textContent=(document.getElementById('hdEditYear').value||'').trim();hdCloseModal('hdEditModal');}
function hdOpenDelete(id){hdCurrentRowId=id;hdOpenModal('hdDeleteModal');}
function hdConfirmDelete(){var r=hdGetRow(hdCurrentRowId);if(r)r.remove();hdSyncSelectAll();hdCloseModal('hdDeleteModal');}
document.addEventListener('click',function(e){var t=e.target.closest('[data-hd-menu-toggle]');if(t){e.stopPropagation();hdToggleMenu(t.getAttribute('data-hd-menu-toggle'),t);return;}if(!e.target.closest('.apst-dropdown'))hdCloseMenus();});
window.addEventListener('scroll',hdCloseMenus,true);

document.addEventListener('DOMContentLoaded', function() {
    var tableBody = document.getElementById('hdTableBody');
    var selectedRowId = tableBody ? (tableBody.getAttribute('data-selected-row-id') || '').trim() : '';
    if (selectedRowId && hdGetRow(selectedRowId)) {
        hdOpenPreview(selectedRowId);
    }
});
