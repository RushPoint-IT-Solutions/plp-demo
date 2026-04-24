{{-- ============================================================
  Form Source Upload JS — include this ONCE per page (e.g. in @stack('form-upload-scripts')).
  Also loads mammoth.js for DOCX preview and version history data.
  ============================================================ --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.21/mammoth.browser.min.js"></script>

<script>
(function () {
    // ── Open / close modal ────────────────────────────────
    window.openFormSourceUploadModal = function (formKey, formLabel, uploadId) {
        var modal = document.getElementById('formSourceUploadModal-' + formKey);
        var title = document.getElementById('formSourceUploadTitle-' + formKey);
        var fileInput = document.getElementById('formSourceFile-' + formKey);
        var notesInput = document.getElementById('formSourceNotes-' + formKey);
        if (!modal) return;

        if (title) {
            title.textContent = 'UPLOAD SOURCE — ' + String(formLabel || formKey).toUpperCase();
        }
        if (fileInput) fileInput.value = '';
        if (notesInput) notesInput.value = '';
        modal.style.display = 'flex';
    };

    window.closeFormSourceUploadModal = function (formKey) {
        var modal = document.getElementById('formSourceUploadModal-' + formKey);
        if (modal) modal.style.display = 'none';
    };

    // Close on overlay click
    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('form-source-upload-modal')) {
            // Derive formKey from id: formSourceUploadModal-{formKey}
            var id = e.target.id;
            var formKey = id.replace('formSourceUploadModal-', '');
            closeFormSourceUploadModal(formKey);
        }
    });

    // Close on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.form-source-upload-modal[style*="flex"]').forEach(function (modal) {
                var formKey = modal.id.replace('formSourceUploadModal-', '');
                closeFormSourceUploadModal(formKey);
            });
        }
    });

    // Reload page after successful POST (form submits natively)
    document.querySelectorAll('.form-source-upload-modal form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var formKey = form.closest('.form-source-upload-modal').id.replace('formSourceUploadModal-', '');
            // Close modal after short delay so user sees something happened
            setTimeout(function () {
                closeFormSourceUploadModal(formKey);
            }, 400);
        });
    });

    // ── DOCX Preview ──────────────────────────────────────
    var docxPreviewModal = document.getElementById('formSourceDocxPreviewModal');
    var docxPreviewContent = document.getElementById('formSourceDocxPreviewContent');
    var downloadBaseUrl = @json(route('registrar.registrar-menu.forms.uploads.download', ['registrarFormUpload' => '__ID__']));

    if (docxPreviewModal && docxPreviewContent) {
        // These are handled per-form; init only once
    }

    window.openFormSourceDocxPreview = function (uploadId, label) {
        var modal = document.getElementById('formSourceDocxPreviewModal');
        var content = document.getElementById('formSourceDocxPreviewContent');
        var title = document.getElementById('formSourceDocxPreviewTitle');
        if (!modal || !content) return;

        content.innerHTML = '<p style="color:#9ca3af; text-align:center; padding:40px 0;">Loading document...</p>';
        if (title) title.textContent = (label || 'DOCX') + ' — Preview';
        modal.style.display = 'flex';

        var url = downloadBaseUrl.replace('__ID__', uploadId);
        fetch(url)
            .then(function (res) {
                if (!res.ok) throw new Error('Failed to fetch file (HTTP ' + res.status + ')');
                return res.arrayBuffer();
            })
            .then(function (buf) {
                if (typeof mammoth === 'undefined') {
                    content.innerHTML = '<p style="color:#ef4444; text-align:center; padding:40px 0;">mammoth.js not loaded.</p>';
                    return;
                }
                return mammoth.convertToHtml({ arrayBuffer: buf });
            })
            .then(function (result) {
                if (!result) return;
                content.innerHTML = result.value;
                if (result.messages && result.messages.length) {
                    console.warn('mammoth.js warnings:', result.messages);
                }
            })
            .catch(function (err) {
                content.innerHTML = '<p style="color:#ef4444; text-align:center; padding:40px 0;">Could not render preview: ' + err.message + '</p>';
            });
    };

    window.closeFormSourceDocxPreview = function () {
        var modal = document.getElementById('formSourceDocxPreviewModal');
        if (modal) modal.style.display = 'none';
    };
}());
</script>

{{-- DOCX preview modal (global, rendered once per page) --}}
<div class="req-modal-overlay" id="formSourceDocxPreviewModal" style="display:none;" onclick="if(event.target===this) closeFormSourceDocxPreview()">
    <div class="req-modal-box" style="width: 850px; max-height: 90vh; display: flex; flex-direction: column;">
        <h3 class="req-modal-title" id="formSourceDocxPreviewTitle" style="color:#006837; font-size: 1rem;">DOCX PREVIEW</h3>
        <div id="formSourceDocxPreviewContent" style="flex:1; overflow:auto; padding:20px; border:1px solid #e5e7eb; border-radius:8px; margin:12px 0; background:#fff; font-family:'Times New Roman',serif; font-size:12pt; line-height:1.6; min-height:300px;">
            <p style="color:#9ca3af; text-align:center; padding:40px 0;">Loading document...</p>
        </div>
        <div class="req-modal-actions" style="margin-top:10px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="closeFormSourceDocxPreview()">Close</button>
            <button type="button" class="req-btn-save" onclick="window.open().document.write('<html><head><title>Print</title></head><body style=\'font-family:Times New Roman,serif;font-size:12pt;line-height:1.6;padding:20px;\'><div style=\'position:fixed;top:0;left:0;width:100%;height:100%;\'></head><body>\' + document.getElementById('formSourceDocxPreviewContent').innerHTML + \'</body></html>\');window.focus();window.print();void 0;">Print</button>
        </div>
    </div>
</div>