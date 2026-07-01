/* honorable-dismissal.js - Honorable Dismissal form page logic */

var hdCurrentRowId = null;
var hdCurrentPreviewName = 'honorable-dismissal.html';
var hdTemplateState = null;
var hdSelectedElementId = null;

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

function hdEsc(v) {
    return String(v || '').replace(/[&<>"']/g, function(c) {
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
}

function hdTemplateUrl(rowId) {
    if (!rowId) return window.hdBlankLayoutUrl || '';
    return String(window.hdLayoutUrlTemplate || '').replace('__STUDENT__', encodeURIComponent(rowId));
}

function hdLoadTemplate(rowId) {
    return hdJson(hdTemplateUrl(rowId)).then(function(data) {
        if (!data || !data.success || !data.template) {
            throw { message: 'Unable to load layout template.' };
        }
        hdTemplateState = hdNormalizeLayout(data.template.content_json || {});
        return hdTemplateState;
    });
}

function hdNormalizeLayout(layout) {
    var elements = Array.isArray(layout.elements) ? layout.elements : [];
    return {
        page: Object.assign({ width_mm: 210, height_mm: 297, orientation: 'portrait', background: '#ffffff' }, layout.page || {}),
        elements: elements.map(function(item, index) {
            return {
                id: String(item.id || ('element_' + index)),
                type: 'text',
                text: String(item.text || ''),
                resolved_text: item.resolved_text == null ? null : String(item.resolved_text),
                top: hdClampNumber(item.top, 0, 100, 10),
                left: hdClampNumber(item.left, 0, 100, 10),
                width: hdClampNumber(item.width, 1, 100, 30),
                font_family: item.font_family || 'Arial',
                font_size: hdClampNumber(item.font_size, 6, 96, 12),
                font_weight: item.font_weight === 'bold' ? 'bold' : 'normal',
                font_style: item.font_style === 'italic' ? 'italic' : 'normal',
                text_decoration: item.text_decoration === 'underline' ? 'underline' : 'none',
                text_align: ['left', 'center', 'right', 'justify'].indexOf(item.text_align) >= 0 ? item.text_align : 'left',
                line_height: hdClampNumber(item.line_height, 0.8, 3, 1.25)
            };
        })
    };
}

function hdClampNumber(value, min, max, fallback) {
    var n = parseFloat(value);
    if (isNaN(n)) return fallback;
    return Math.min(max, Math.max(min, n));
}

function hdTemplateForSave() {
    var state = hdTemplateState || hdNormalizeLayout({});
    return {
        page: state.page,
        elements: state.elements.map(function(item) {
            var copy = Object.assign({}, item);
            delete copy.resolved_text;
            return copy;
        })
    };
}

function hdHasTemplateTokens(value) {
    return /\{\{\s*[a-z0-9_]+\s*\}\}/i.test(String(value || ''));
}

function hdElementById(id) {
    if (!hdTemplateState) return null;
    return hdTemplateState.elements.find(function(item) { return item.id === id; }) || null;
}

function hdRenderTemplate(layout) {
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet) return;

    sheet.innerHTML = '';
    sheet.classList.add('hd-template-canvas');
    sheet.style.background = (layout.page && layout.page.background) || '#ffffff';
    (layout.elements || []).forEach(function(item) {
        var el = document.createElement('div');
        el.className = 'hd-template-element';
        el.setAttribute('data-hd-element-id', item.id);
        el.setAttribute('contenteditable', 'true');
        el.innerHTML = hdEsc(item.resolved_text || item.text).replace(/\n/g, '<br>');
        hdApplyElementStyle(el, item);
        sheet.appendChild(el);
    });

    hdSelectTemplateElement(hdSelectedElementId && hdElementById(hdSelectedElementId) ? hdSelectedElementId : null);
}

function hdApplyElementStyle(el, item) {
    el.style.top = item.top + '%';
    el.style.left = item.left + '%';
    el.style.width = item.width + '%';
    el.style.fontFamily = "'" + item.font_family + "', sans-serif";
    el.style.fontSize = item.font_size + 'pt';
    el.style.fontWeight = item.font_weight;
    el.style.fontStyle = item.font_style;
    el.style.textDecoration = item.text_decoration;
    el.style.textAlign = item.text_align;
    el.style.lineHeight = item.line_height;
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
    hdSelectedElementId = null;
    hdOpenPreviewModal();
    hdSetSheetLoading('Loading layout...');

    hdLoadTemplate(rowId).then(function(layout) {
        hdRenderTemplate(layout);
    }).catch(function(error) {
        hdSetSheetLoading((error && error.message) || 'Unable to load layout template.');
    });
}

function hdOpenBlankPreview() {
    hdCurrentRowId = null;
    hdCurrentPreviewName = 'honorable-dismissal-template.html';
    hdSelectedElementId = null;
    hdOpenPreviewModal();
    hdSetSheetLoading('Loading blank template...');

    hdLoadTemplate(null).then(function(layout) {
        hdRenderTemplate(layout);
    }).catch(function(error) {
        hdSetSheetLoading((error && error.message) || 'Unable to load blank template.');
    });
}

function hdOpenPreviewModal() {
    var modal = document.getElementById('hdPreviewModal');
    if (modal) modal.style.display = 'flex';
    document.body.classList.add('hd-preview-open');
    hdSyncToolbar();
}

function hdSetSheetLoading(message) {
    var sheet = document.getElementById('hdPreviewSheet');
    if (sheet) sheet.innerHTML = '<div class="hd-template-loading">' + hdEsc(message) + '</div>';
}

function hdClosePreview() {
    var m = document.getElementById('hdPreviewModal');
    if (m) m.style.display = 'none';
    document.body.classList.remove('hd-preview-open');
    hdSelectedElementId = null;
    hdSyncToolbar();
}

function hdSelectTemplateElement(id) {
    hdSelectedElementId = id;
    document.querySelectorAll('#hdPreviewSheet .hd-template-element').forEach(function(el) {
        el.classList.toggle('is-selected', el.getAttribute('data-hd-element-id') === id);
    });
    hdSyncToolbar();
}

function hdSyncToolbar() {
    var toolbar = document.getElementById('hdEditorToolbar');
    var item = hdElementById(hdSelectedElementId);
    if (!toolbar) return;

    toolbar.setAttribute('aria-hidden', item ? 'false' : 'true');
    toolbar.classList.toggle('is-visible', !!item);
    if (!item) return;

    hdSetControlValue('hdFontFamily', item.font_family);
    hdSetControlValue('hdFontSize', item.font_size);
    hdSetControlValue('hdTextAlign', item.text_align);
    hdSetControlValue('hdTopPercent', item.top);
    hdSetControlValue('hdLeftPercent', item.left);
    toolbar.querySelectorAll('[data-hd-style]').forEach(function(btn) {
        var style = btn.getAttribute('data-hd-style');
        var active = (style === 'bold' && item.font_weight === 'bold') ||
            (style === 'italic' && item.font_style === 'italic') ||
            (style === 'underline' && item.text_decoration === 'underline');
        btn.classList.toggle('is-active', active);
    });
}

function hdSetControlValue(id, value) {
    var control = document.getElementById(id);
    if (control) control.value = value;
}

function hdUpdateSelectedElement(changes) {
    var item = hdElementById(hdSelectedElementId);
    if (!item) return;
    Object.assign(item, changes);
    var el = null;
    document.querySelectorAll('#hdPreviewSheet [data-hd-element-id]').forEach(function(candidate) {
        if (candidate.getAttribute('data-hd-element-id') === item.id) el = candidate;
    });
    if (el) hdApplyElementStyle(el, item);
    hdSyncToolbar();
}

function hdSaveLayoutTemplate() {
    if (!hdTemplateState) return;
    var saveButton = document.querySelector('[data-hd-save-layout]');
    if (saveButton) {
        saveButton.disabled = true;
        saveButton.setAttribute('data-original-text', saveButton.textContent);
        saveButton.textContent = 'Saving...';
    }

    hdJson(window.hdTemplateSaveUrl || '', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': hdCsrf()
        },
        body: JSON.stringify({ content_json: hdTemplateForSave() })
    }).then(function(data) {
        return hdLoadTemplate(hdCurrentRowId).then(function(layout) {
            hdRenderTemplate(layout);
            alert((data && data.message) || 'Layout template saved.');
        });
    }).catch(function(error) {
        var detail = error && error.errors ? Object.keys(error.errors).map(function(key) {
            return error.errors[key].join(' ');
        }).join('\n') : '';
        alert(detail || (error && error.message) || 'Unable to save layout template.');
    }).then(function() {
        if (saveButton) {
            saveButton.disabled = false;
            saveButton.textContent = saveButton.getAttribute('data-original-text') || 'Save Layout Template';
            saveButton.removeAttribute('data-original-text');
        }
    });
}

function hdCleanPrintSheet(sourceSheet) {
    var clone = sourceSheet.cloneNode(true);
    clone.removeAttribute('id');
    clone.classList.remove('hd-template-canvas');
    clone.querySelectorAll('.hd-template-element').forEach(function(el) {
        el.classList.remove('is-selected');
        el.removeAttribute('contenteditable');
    });
    return clone.outerHTML;
}

function hdPrintPreview() {
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet || !sheet.querySelector('.hd-template-element')) return;
    var rowIds = hdCurrentRowId ? [hdCurrentRowId] : [];
    var printNow = function() { hdPrintSheets([hdCleanPrintSheet(sheet)]); };
    if (!rowIds.length) {
        printNow();
        return;
    }
    hdIssueRows(rowIds).then(function(ok) {
        if (ok) printNow();
    });
}

function hdPrintSheets(list) {
    var pc = document.getElementById('hdPrintContainer');
    if (!pc || !list || !list.length) return;
    pc.innerHTML = list.map(function(h, i) {
        var cls = i < list.length - 1 ? ' hd-print-page-break' : '';
        return '<div class="hd-print-shell' + cls + '">' + h + '</div>';
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
        + '<style>@page{size:A4 portrait;margin:0}body{background:#fff;margin:0}.hd-sheet{box-shadow:none;border:0;margin:0 auto}</style>'
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
    if (!sheet || !sheet.querySelector('.hd-template-element')) return;
    hdDownloadHtml(hdCleanPrintSheet(sheet), hdCurrentPreviewName);
}

function hdDownloadRow(rowId) {
    var data = hdGetRowData(rowId);
    if (!data) return;
    if (!data.hdNo) { alert('Student must be tagged For Dismissal before downloading HD.'); return; }

    hdLoadTemplate(rowId).then(function(layout) {
        var temp = document.createElement('div');
        temp.className = 'hd-sheet';
        (layout.elements || []).forEach(function(item) {
            var el = document.createElement('div');
            el.className = 'hd-template-element';
            el.innerHTML = hdEsc(item.resolved_text || item.text).replace(/\n/g, '<br>');
            hdApplyElementStyle(el, item);
            temp.appendChild(el);
        });
        hdDownloadHtml(temp.outerHTML, hdFileName(data));
    }).catch(function(error) {
        alert((error && error.message) || 'Unable to download HD.');
    });
}

function hdPrintSelected() {
    var sel = Array.from(document.querySelectorAll('#hdTableBody .hd-row-select:checked'));
    var candidateSel = Array.from(document.querySelectorAll('#hdCandidateTableBody .hd-candidate-row-select:checked'));
    if (!sel.length && !candidateSel.length) { alert('Select at least one record to print.'); return; }

    var rowIds = sel.map(function(cb) {
        var row = cb.closest('tr');
        return row ? row.getAttribute('data-row-id') : null;
    }).filter(Boolean);
    var candidateIds = candidateSel.map(function(cb) {
        var row = cb.closest('tr');
        return row ? row.getAttribute('data-candidate-id') : null;
    }).filter(Boolean);

    hdTagRows(candidateIds).then(function(tagged) {
        if (!tagged) return;
        var printIds = rowIds.concat(candidateIds).filter(function(rowId, index, list) {
            return rowId && list.indexOf(rowId) === index;
        });

        return Promise.all(printIds.map(function(rowId) {
        return hdLoadTemplate(rowId).then(function(layout) {
            var sheet = document.createElement('div');
            sheet.className = 'hd-sheet';
            (layout.elements || []).forEach(function(item) {
                var el = document.createElement('div');
                el.className = 'hd-template-element';
                el.innerHTML = hdEsc(item.resolved_text || item.text).replace(/\n/g, '<br>');
                hdApplyElementStyle(el, item);
                sheet.appendChild(el);
            });
            return sheet.outerHTML;
        });
        })).then(function(sheets) {
            return hdIssueRows(printIds).then(function(ok) {
            if (ok) hdPrintSheets(sheets);
            return ok;
        });
        });
    }).catch(function(error) {
        alert((error && error.message) || 'Unable to prepare selected records.');
    });
}

function hdOpenPreviewFromSelection() {
    var checked = document.querySelector('#hdTableBody .hd-row-select:checked');
    var row = checked ? checked.closest('tr') : document.querySelector('#hdTableBody tr[data-row-id]');
    if (!row) return;
    var rowId = row.getAttribute('data-row-id');
    hdOpenPreview(rowId);
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

function hdTagRows(rowIds) {
    rowIds = (rowIds || []).filter(Boolean);
    if (!rowIds.length) return Promise.resolve(true);
    var template = String(window.hdTagUrlTemplate || '');
    if (!template) return Promise.resolve(false);

    return Promise.all(rowIds.map(function(rowId) {
        return hdPost(template.replace('__STUDENT__', rowId));
    })).then(function(results) {
        var failed = results.find(function(result) { return !result || !result.success; });
        if (failed) {
            alert(failed.message || 'Unable to tag selected student for dismissal.');
            return false;
        }
        return true;
    }).catch(function() {
        alert('Network error while tagging selected students.');
        return false;
    });
}

function hdFilterTable(query) {
    var q = String(query || '').toLowerCase().trim();
    document.querySelectorAll('#hdCandidateTableBody tr, #hdTableBody tr').forEach(function(row) {
        var text = (row.textContent || '').toLowerCase();
        row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
    });
    hdSyncSelectAll();
    hdSyncCandidateSelectAll();
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

function hdToggleSelectAll(s) { document.querySelectorAll('#hdTableBody .hd-row-select').forEach(function(c){c.checked=!!s.checked});hdSyncSelectAll(); }
function hdSyncSelectAll() {
    var h=document.getElementById('hdSelectAll'),items=document.querySelectorAll('#hdTableBody .hd-row-select');if(!h)return;
    var t=items.length,c=0;items.forEach(function(x){if(x.checked)c++});h.checked=t>0&&c===t;h.indeterminate=c>0&&c<t;
}
function hdToggleCandidateSelectAll(s) { document.querySelectorAll('#hdCandidateTableBody .hd-candidate-row-select').forEach(function(c){c.checked=!!s.checked});hdSyncCandidateSelectAll(); }
function hdSyncCandidateSelectAll() {
    var h=document.getElementById('hdCandidateSelectAll'),items=document.querySelectorAll('#hdCandidateTableBody .hd-candidate-row-select');if(!h)return;
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

function hdBindEditor() {
    var sheet = document.getElementById('hdPreviewSheet');
    if (!sheet) return;

    sheet.addEventListener('mousedown', function(e) {
        var target = e.target.closest('.hd-template-element');
        if (!target || !sheet.contains(target)) {
            hdSelectTemplateElement(null);
            return;
        }

        var id = target.getAttribute('data-hd-element-id');
        var item = hdElementById(id);
        if (!item) return;
        hdSelectTemplateElement(id);

        var sheetRect = sheet.getBoundingClientRect();
        var targetRect = target.getBoundingClientRect();
        var startX = e.clientX;
        var startY = e.clientY;
        var startLeftPx = targetRect.left - sheetRect.left;
        var startTopPx = targetRect.top - sheetRect.top;
        var moved = false;

        function onMove(ev) {
            moved = true;
            var nextLeft = ((startLeftPx + ev.clientX - startX) / sheetRect.width) * 100;
            var nextTop = ((startTopPx + ev.clientY - startY) / sheetRect.height) * 100;
            item.left = Math.round(hdClampNumber(nextLeft, 0, 100, item.left) * 1000) / 1000;
            item.top = Math.round(hdClampNumber(nextTop, 0, 100, item.top) * 1000) / 1000;
            hdApplyElementStyle(target, item);
            hdSyncToolbar();
        }

        function onUp() {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            if (moved) target.blur();
        }

        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });

    sheet.addEventListener('input', function(e) {
        var target = e.target.closest('.hd-template-element');
        if (!target) return;
        var item = hdElementById(target.getAttribute('data-hd-element-id'));
        if (item) {
            item.resolved_text = target.innerText.trim();
            if (!hdCurrentRowId || !hdHasTemplateTokens(item.text)) {
                item.text = item.resolved_text;
            }
        }
    });

    var fontFamily = document.getElementById('hdFontFamily');
    var fontSize = document.getElementById('hdFontSize');
    var textAlign = document.getElementById('hdTextAlign');
    var topPercent = document.getElementById('hdTopPercent');
    var leftPercent = document.getElementById('hdLeftPercent');
    var deleteElement = document.getElementById('hdDeleteElement');

    if (fontFamily) fontFamily.addEventListener('change', function() { hdUpdateSelectedElement({ font_family: this.value }); });
    if (fontSize) fontSize.addEventListener('input', function() { hdUpdateSelectedElement({ font_size: hdClampNumber(this.value, 6, 96, 12) }); });
    if (textAlign) textAlign.addEventListener('change', function() { hdUpdateSelectedElement({ text_align: this.value }); });
    if (topPercent) topPercent.addEventListener('input', function() { hdUpdateSelectedElement({ top: hdClampNumber(this.value, 0, 100, 0) }); });
    if (leftPercent) leftPercent.addEventListener('input', function() { hdUpdateSelectedElement({ left: hdClampNumber(this.value, 0, 100, 0) }); });
    if (deleteElement) deleteElement.addEventListener('click', function() {
        if (!hdTemplateState || !hdSelectedElementId) return;
        hdTemplateState.elements = hdTemplateState.elements.filter(function(item) {
            return item.id !== hdSelectedElementId;
        });
        hdSelectedElementId = null;
        hdRenderTemplate(hdTemplateState);
    });

    document.querySelectorAll('[data-hd-style]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var item = hdElementById(hdSelectedElementId);
            if (!item) return;
            var style = btn.getAttribute('data-hd-style');
            if (style === 'bold') hdUpdateSelectedElement({ font_weight: item.font_weight === 'bold' ? 'normal' : 'bold' });
            if (style === 'italic') hdUpdateSelectedElement({ font_style: item.font_style === 'italic' ? 'normal' : 'italic' });
            if (style === 'underline') hdUpdateSelectedElement({ text_decoration: item.text_decoration === 'underline' ? 'none' : 'underline' });
        });
    });
}

window.addEventListener('afterprint', function() {
    document.body.classList.remove('hd-printing');
    var pc = document.getElementById('hdPrintContainer');
    if (pc) pc.innerHTML = '';
});

document.addEventListener('click',function(e){var t=e.target.closest('[data-hd-menu-toggle]');if(t){e.stopPropagation();hdToggleMenu(t.getAttribute('data-hd-menu-toggle'),t);return;}if(!e.target.closest('.apst-dropdown'))hdCloseMenus();});
window.addEventListener('scroll',hdCloseMenus,true);

document.addEventListener('DOMContentLoaded', function() {
    hdBindEditor();
    var tableBody = document.getElementById('hdTableBody');
    var selectedRowId = tableBody ? (tableBody.getAttribute('data-selected-row-id') || '').trim() : '';
    if (selectedRowId && hdGetRow(selectedRowId)) {
        hdOpenPreview(selectedRowId);
    }
});
