/* honorable-dismissal.js — Honorable Dismissal form page logic */

var hdCurrentRowId = null;

function hdEsc(v) {
    return String(v || '').replace(/[&<>"']/g, function(c) {
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
}

var hdDemoMeta = {
    '1': { studentNo:'17-0501', studentName:'LIBO-ON, KAREN MARIE SITCHON', program:'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY', hdNo:'1st 17 FD-221094', hdDate:'October 7, 2022' },
    '2': { studentNo:'21-00010', studentName:'CERADO, ROILEEN I.', program:'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY', hdNo:'', hdDate:'' }
};

function hdBuildTemplate(data, meta) {
    var now = new Date();
    var dateStr = meta.hdDate || (now.toLocaleDateString('en-US', { month:'long', day:'numeric', year:'numeric' }));
    var hdNo = meta.hdNo || '';

    /* Space for pre-printed header on yellow paper */
    var headerSpace = '<div class="hd-header-space"></div>';

    /* HD NO / Date */
    var hdInfo = '<div class="hd-info-right">' +
        '<div class="hd-info-row"><span class="hd-info-label">HD NO:</span><span class="hd-info-val-borderless">' + hdEsc(hdNo) + '</span></div>' +
        '<div class="hd-info-row"><span class="hd-info-label">Date:</span><span class="hd-info-val-borderless">' + hdEsc(dateStr) + '</span></div>' +
    '</div>';

    /* Body */
    var body = '<div class="hd-body">' +
        '<p class="hd-concern">TO WHOM IT MAY CONCERN:</p>' +
        '<div class="hd-flex-line" style="margin-top:14px;"><span style="margin-left: 40px; margin-right: 10px;">This certifies that</span><span class="hd-fill">' + hdEsc(meta.studentName) + '</span></div>' +
        '<div class="hd-flex-line"><span style="margin-right: 10px;">a student at the program of</span><span class="hd-fill">' + hdEsc(meta.program) + '</span></div>' +
        '<div class="hd-flex-line">is hereby granted permission to transfer from this university.</div>' +
        '<div class="hd-flex-line" style="margin-top:18px;"><span style="margin-left: 40px;">Official Transcript of Record shall be forwarded upon receipt of the Request Slip below.</span></div>' +
    '</div>';

    /* Registrar signature */
    var sig = '<div class="hd-registrar-sig">' +
        '<div class="hd-sig-name">FEDERICO G. NUEVA, MT</div>' +
        '<div class="hd-sig-title">University Registrar</div>' +
    '</div>';

    /* Dashed cut line */
    var cutLine = '<div class="hd-cut-line">' +
        '<span>(To be accomplished by the requesting school. Cut here and send the lower part to PLP)</span>' +
    '</div>';

    /* Bottom section: Request for Official Transcript of Records */
    var bottom = '<div class="hd-request-section">' +
        '<div class="hd-request-title">REQUEST FOR OFFICIAL TRANSCRIPT OF RECORDS</div>' +
        '<div class="hd-request-hdno">' +
            '<div class="hd-info-right">' +
                '<div class="hd-info-row"><span class="hd-info-label">HD NO:</span><span style="min-width:140px; text-align:left;">' + hdEsc(hdNo) + '</span></div>' +
                '<div class="hd-info-row"><span class="hd-info-label">Date:</span><span class="hd-info-val">' + hdEsc(dateStr) + '</span></div>' +
            '</div>' +
        '</div>' +
        '<div class="hd-request-body">' +
            '<div style="margin-bottom:8px;">' +
                '<div style="display:inline-block;">' +
                    '<div style="font-weight:700;">THE REGISTRAR</div>' +
                    '<div style="border-bottom:1px solid #333; margin-top:2px; min-width:300px;"></div>' +
                '</div>' +
            '</div>' +
            '<div style="margin-bottom:8px;">' +
                '<div style="display:inline-block;">' +
                    '<div>Pamantasan ng Lungsod ng Pasig</div>' +
                    '<div style="border-bottom:1px solid #333; margin-top:2px; min-width:300px;"></div>' +
                '</div>' +
            '</div>' +
            '<div style="margin-bottom:8px;">' +
                '<div style="display:inline-block;">' +
                    '<div>Alkalde Jose St. Kapasigan, Pasig City</div>' +
                    '<div style="border-bottom:1px solid #333; margin-top:2px; min-width:300px;"></div>' +
                '</div>' +
            '</div>' +
            '<p style="margin-top:14px;">Dear Sir/Madam:</p>' +
            '<div class="hd-flex-line" style="margin-top:10px;"><span style="margin-left: 40px; margin-right: 10px;">Please send us the Official Transcript of Records of the student</span><span class="hd-fill">' + hdEsc(meta.studentName) + '</span></div>' +
            '<p style="margin-top:6px; margin-bottom:0;">whose conforming signature appears below.</p>' +
        '</div>' +
        '<div style="margin-top:30px; text-align:right; font-size:0.75rem;">' +
            '<div style="border-bottom:1px solid #333; width:280px; display:inline-block; margin-bottom:4px;"></div><br>' +
            'Signature over Printed Name and Position of School Official' +
        '</div>' +
        '<div class="hd-request-fields" style="padding-right:20%;">' +
            '<div class="hd-field-row"><span class="hd-field-label">Student\'s Signature</span><span class="hd-field-line"></span></div>' +
            '<div class="hd-field-row"><span class="hd-field-label">Student Number</span><span class="hd-field-val">' + hdEsc(meta.studentNo) + '</span></div>' +
            '<div class="hd-field-row"><span class="hd-field-label">Program</span><span class="hd-field-val">' + hdEsc(meta.program) + '</span></div>' +
            '<div class="hd-field-row"><span class="hd-field-label">School Requesting</span><span class="hd-field-line"></span></div>' +
            '<div class="hd-field-row"><span class="hd-field-label">Mailing Address</span><span class="hd-field-line"></span></div>' +
            '<div class="hd-field-row"><span class="hd-field-label">School Contact Nos.</span><span class="hd-field-line"></span></div>' +
        '</div>' +
        '<div class="hd-mail-options">' +
            '<div>( &nbsp; ) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail</div>' +
            '<div>( &nbsp; ) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Entrust to bearer <span style="float:right; font-size:0.8rem;">Not Valid Without University Seal</span></div>' +
        '</div>' +
    '</div>';

    return headerSpace + hdInfo + body + sig + cutLine + bottom;
}

function hdGetRowData(rowId) {
    var row = hdGetRow(rowId);
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

function hdOpenPreview(rowId) {
    var data = hdGetRowData(rowId);
    if (!data) return;
    var meta = hdDemoMeta[String(rowId)] || hdDemoMeta['1'];
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet) return;
    sheet.innerHTML = hdBuildTemplate(data, meta);
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
    hdPrintSheets([sheet.innerHTML]);
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

function hdPrintSelected() {
    var sel = Array.from(document.querySelectorAll('#hdTableBody .hd-row-select:checked'));
    if (!sel.length) { alert('Select at least one record to print.'); return; }
    var sheets = sel.map(function(cb) {
        var row = cb.closest('tr');
        var rid = row ? row.getAttribute('data-row-id') : null;
        if (!rid) return '';
        var data = hdGetRowData(rid);
        var meta = hdDemoMeta[rid] || hdDemoMeta['1'];
        return data ? hdBuildTemplate(data, meta) : '';
    }).filter(Boolean);
    hdPrintSheets(sheets);
}

function hdOpenPreviewFromSelection() {
    var checked = document.querySelector('#hdTableBody .hd-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#hdTableBody tr[data-row-id]');
    if (!row) return;
    var rowId = row.getAttribute('data-row-id');
    hdOpenPreview(rowId);
}

function hdOpenBlankPreview() {
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet) return;

    sheet.innerHTML = hdBuildTemplate(
        { studentNo:'', studentName:'', program:'', year:'', section:'' },
        { studentNo:'', studentName:'', program:'', hdNo:'', hdDate:'' }
    );

    document.getElementById('hdPreviewModal').style.display = 'flex';
    document.body.classList.add('hd-preview-open');
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
