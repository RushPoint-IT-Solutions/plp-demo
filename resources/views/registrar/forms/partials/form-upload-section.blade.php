{{-- ============================================================
  Form Source Upload Section — reusable across all registrar form pages.
  Include with: @include('registrar.forms.partials.form-upload-section', [
      'formKey' => 'tor',
      'formLabel' => 'TOR',
  ])
  ============================================================ --}}
@php
    use App\RegistrarFormUpload;

    // Get current upload for this form key (guarded against missing table)
    $currentUpload = null;
    if (\Illuminate\Support\Facades\Schema::hasTable('registrar_form_uploads')) {
        $currentUpload = RegistrarFormUpload::query()
            ->forFormKey($formKey)
            ->current()
            ->first();
    }
    $hasUpload = !empty($currentUpload);
    $uploadId = $hasUpload ? $currentUpload->id : null;
    $versionBadge = $hasUpload ? ('v' . $currentUpload->version_number) : null;
@endphp

{{-- Toolbar chip: show current version and upload button --}}
<div class="form-upload-section" id="formUploadSection-{{ $formKey }}" data-form-key="{{ $formKey }}" data-upload-id="{{ $uploadId ?? '' }}">
    @if($hasUpload)
    <span class="form-upload-status">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        Source: {{ $currentUpload->original_filename }} ({{ number_format($currentUpload->size_bytes / 1024, 1) }} KB) — <strong>{{ $versionBadge }}</strong>
    </span>
    @else
    <span class="form-upload-status form-upload-status--empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        No source file uploaded yet
    </span>
    @endif

    <button type="button" class="form-upload-btn" onclick="openFormSourceUploadModal('{{ $formKey }}', '{{ addslashes($formLabel) }}', {{ $uploadId ? $uploadId : 'null' }})">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        {{ $hasUpload ? 'Update Source' : 'Upload Source' }}
    </button>
</div>

{{-- Upload Modal (unique ID per form key so multiple forms on one page work) --}}
<div class="req-modal-overlay form-source-upload-modal" id="formSourceUploadModal-{{ $formKey }}" style="display:none;">
    <div class="req-modal-box" style="width: 540px;">
        <h3 class="req-modal-title" id="formSourceUploadTitle-{{ $formKey }}" style="color:#006837; font-size: 1rem;">UPLOAD SOURCE — {{ strtoupper($formLabel) }}</h3>
        <p style="font-size:0.82rem; color:#6b7280; margin: 2px 0 12px;">
            Upload a DOCX or PDF source file for use as the {{ $formLabel }} form template.
        </p>
        <form method="POST" action="{{ route('registrar.registrar-menu.forms.uploads.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="form_key" value="{{ $formKey }}">

            <div style="display:flex; flex-direction:column; gap:10px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Form</label>
                    <input type="text" class="req-modal-input" value="{{ $formLabel }} — {{ $formKey }}" readonly>
                </div>

                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="formSourceFile-{{ $formKey }}">Source file</label>
                    <input type="file" class="registrar-forms-upload-file" id="formSourceFile-{{ $formKey }}" name="form_file" accept=".doc,.docx,.pdf" required>
                    <div class="registrar-forms-upload-help">DOCX for editable source. PDF for fixed published version. Max 20 MB.</div>
                </div>

                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="formSourceNotes-{{ $formKey }}">Notes</label>
                    <textarea class="req-modal-input" id="formSourceNotes-{{ $formKey }}" name="notes" rows="2" placeholder="Optional version notes"></textarea>
                </div>
            </div>

            <div class="req-modal-actions" style="margin-top:18px; justify-content:center;">
                <button type="button" class="req-btn-cancel" onclick="closeFormSourceUploadModal('{{ $formKey }}')">Cancel</button>
                <button type="submit" class="req-btn-save">Save version</button>
            </div>
        </form>
    </div>
</div>