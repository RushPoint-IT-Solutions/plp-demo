@extends('layouts.registrar')

@section('title', 'PLP - ' . $formLabel)
@section('page-title', $formLabel)

@push('styles')
<style>
    .a4-wrapper {
        width: 100%;
        background-color: #e5e7eb;
        padding: 40px 0;
        display: flex;
        justify-content: center;
        overflow-y: visible;
    }
    .a4-paper {
        width: 210mm;
        min-height: 297mm;
        padding: 25.4mm; /* exactly 1 inch margins like standard word docs */
        background: #fff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        box-sizing: border-box;
        position: relative;
    }
    .a4-paper iframe {
        width: 100%;
        height: 297mm;
        border: none;
    }
    .a4-docx-content {
        font-family: 'Times New Roman', Times, serif;
        font-size: 11pt;
        line-height: 1.5;
        color: #000;
        width: 100%;
    }
    .a4-docx-content p {
        margin-bottom: 0.5em;
    }
    .a4-docx-content table {
        width: 100% !important;
        border-collapse: collapse;
        margin-bottom: 1em;
    }
    .a4-docx-content td, .a4-docx-content th {
        border: 1px solid #000;
        padding: 6px;
    }
    @media print {
        @page { size: A4 portrait; margin: 0; }
        body * { visibility: hidden; }
        .a4-paper, .a4-paper * { visibility: visible; }
        .a4-paper { position: absolute; left: 0; top: 0; margin: 0; padding: 25.4mm; border: none; box-shadow: none; width: 100%; min-height: 100%; background: white; }
        .registrar-main, .student-main-wrapper, .pf-page, .ga-page, .content-footer-wrap > .plp-footer, .sidebar-overlay, .student-topbar, .student-page-header, .plp-sidebar, .sidebar-logout, .sidebar-brand, .sidebar-nav { display: none !important; margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        html, body { background: white !important; height: auto; }
        .ga-page { padding: 0 !important; }
    }
</style>
@endpush

@section('content')
@php
    $genericformviewerFormUpload = null;
    if (\Illuminate\Support\Facades\Schema::hasTable('registrar_form_uploads')) {
        $genericformviewerFormUpload = \App\RegistrarFormUpload::query()->forFormKey('generic-form-viewer')->current()->first();
    }
@endphp
<div class="form-upload-section" style="margin-bottom:8px;">
    @if($genericformviewerFormUpload)
        <span class="form-upload-status">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Source: {{$genericformviewerFormUpload->original_filename}} ({{ number_format($genericformviewerFormUpload->size_bytes / 1024, 1) }} KB) &mdash; <strong>v{{$genericformviewerFormUpload->version_number}}</strong>
        </span>
    @else
        <span class="form-upload-status form-upload-status--empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
            No source file uploaded
        </span>
    @endif
    <button type="button" class="form-upload-btn" onclick="openFormSourceUploadModal('generic-form-viewer', 'Generic Form Viewer', {{ $genericformviewerFormUpload ? $genericformviewerFormUpload->id : 'null' }})">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        {{ $genericformviewerFormUpload ? 'Update Source' : 'Upload Source' }}
    </button>
</div>
<div class="pf-page" id="genericFormContainer">
    <div class="ga-page">
        @include('registrar.forms.partials.upload-toolbar', ['registrarFormDefinitions' => config('registrar_forms', [])])
        
        <div style="display:flex; justify-content:flex-end; gap: 8px; margin-bottom: 20px;">
            <button class="req-btn-save" type="button" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Form
            </button>
        </div>

        <div class="a4-wrapper">
            <div class="a4-paper" id="a4PaperContent">
                <p style="text-align: center; color: #9ca3af; padding-top: 50px;">Loading form visualization...</p>
            </div>
        </div>
    </div>
</div>
@include('registrar.forms.partials.form-upload-js')

<div class="req-modal-overlay form-source-upload-modal" id="formSourceUploadModal-generic-form-viewer" style="display:none;" onclick="if(event.target===this) closeFormSourceUploadModal('generic-form-viewer')">
<div class="req-modal-box" style="width:540px;">
<h3 class="req-modal-title" id="formSourceUploadTitle-generic-form-viewer" style="color:#006837;font-size:1rem;">UPLOAD SOURCE &mdash; Form Viewer</h3>
<p style="font-size:0.82rem;color:#6b7280;margin:2px 0 12px;">Upload a DOCX or PDF source file for use as the Form Viewer template.</p>
<form method="POST" action="{{ route('registrar.registrar-menu.forms.uploads.store') }}" enctype="multipart/form-data">
@csrf
<input type="hidden" name="form_key" value="generic-form-viewer">
<div style="display:flex;flex-direction:column;gap:10px;">
<div class="req-modal-field-group">
<label class="req-modal-label">Form</label>
<input type="text" class="req-modal-input" value="Form Viewer" readonly>
</div>
<div class="req-modal-field-group">
<label class="req-modal-label" for="formSourceFile-generic-form-viewer">Source file</label>
<input type="file" class="registrar-forms-upload-file" id="formSourceFile-generic-form-viewer" name="form_file" accept=".doc,.docx,.pdf" required>
<div class="registrar-forms-upload-help">DOCX for editable source. PDF for fixed published version. Max 20 MB.</div>
</div>
<div class="req-modal-field-group">
<label class="req-modal-label" for="formSourceNotes-generic-form-viewer">Notes</label>
<textarea class="req-modal-input" id="formSourceNotes-generic-form-viewer" name="notes" rows="2" placeholder="Optional version notes"></textarea>
</div>
</div>
<div class="req-modal-actions" style="margin-top:18px;justify-content:center;">
<button type="button" class="req-btn-cancel" onclick="closeFormSourceUploadModal('generic-form-viewer')">Cancel</button>
<button type="submit" class="req-btn-save">Save version</button>
</div>
</form>
</div>
</div>

@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.21/mammoth.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var a4Content = document.getElementById('a4PaperContent');
    var currentUploadId = @json(isset($currentUpload) && $currentUpload ? $currentUpload->id : null);
    var currentFilename = @json(isset($currentUpload) && $currentUpload ? $currentUpload->original_filename : '');

    if (!currentUploadId) {
        a4Content.innerHTML = '<div style="text-align: center; padding-top: 150px;"><h3 style="color:#374151; font-weight:700;">No Template Uploaded</h3><p style="color: #6b7280; font-size:0.9rem; margin-top:8px;">There is currently no active file assigned to this form.<br>Please upload a DOCX or PDF file using the Forms Hub toolbar above.</p></div>';
        return;
    }

    var downloadUrl = @json(route('registrar.registrar-menu.forms.uploads.download', ['registrarFormUpload' => '__ID__']));
    downloadUrl = downloadUrl.replace('__ID__', currentUploadId);
    var fileExt = currentFilename.split('.').pop().toLowerCase();

    if (fileExt === 'pdf') {
        a4Content.style.padding = '0'; // IFRAME handles its own padding 
        a4Content.innerHTML = '<iframe src="' + downloadUrl + '" style="width: 100%; height: 297mm; border: none;"></iframe>';
    } else if (fileExt === 'docx') {
        fetch(downloadUrl)
            .then(function(response) {
                if (!response.ok) throw new Error('File fetching failed');
                return response.arrayBuffer();
            })
            .then(function(arrayBuffer) {
                if (typeof mammoth === 'undefined') {
                    a4Content.innerHTML = '<p style="color:#ef4444; text-align:center;">mammoth.js library not loaded. Cannot render DOCX.</p>';
                    return;
                }
                return mammoth.convertToHtml({ arrayBuffer: arrayBuffer });
            })
            .then(function(result) {
                if (!result) return;
                a4Content.innerHTML = '<div style="margin-bottom: 20px; padding: 10px; background: #fef3c7; border-left: 4px solid #f59e0b; color: #92400e; font-family: Segoe UI, sans-serif; font-size: 0.85rem;" class="d-print-none"><strong>Note on DOCX rendering:</strong> Browsers cannot natively display exact Word formatting. Upload a <strong>PDF template</strong> instead for 100% layout accuracy.</div><div class="a4-docx-content">' + result.value + '</div>';
            })
            .catch(function(err) {
                a4Content.innerHTML = '<div style="text-align:center; padding-top: 100px;"><p style="color:#ef4444; font-weight:700;">Error rendering preview</p><p style="color:#6b7280;">' + err.message + '</p><a href="' + downloadUrl + '" target="_blank" style="color:#006837; text-decoration:underline;">Download raw file instead</a></div>';
            });
    } else {
        a4Content.innerHTML = '<div style="text-align:center; padding-top: 100px;"><h3 style="color:#374151;">Unsupported File Extension</h3><p style="color: #6b7280;">Cannot visualize .' + fileExt + ' files in the browser.</p><br><a href="' + downloadUrl + '" target="_blank" style="display:inline-block; padding:10px 20px; background:#006837; color:#fff; border-radius:4px; text-decoration:none;">Download File</a></div>';
    }
});
</script>
