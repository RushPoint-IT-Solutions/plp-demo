@extends(!empty($applicationFormEmbedded) ? 'layouts.applicant-embedded' : 'layouts.applicant')

@section('title', 'PLP - Medical Clearance')
@section('page-title', 'MEDICAL CLEARANCE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/medical-clearance.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="app-process-page applicant-consistent-page">
    <div class="app-filter-bar">
        <div class="app-filter-row applicant-identity-row">
            <div class="app-filter-group app-filter-group--compact">
                <label class="app-filter-label">Applicant ID</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->applicant_id }}" readonly>
            </div>
            <div class="app-filter-group app-filter-group--compact app-filter-group--wide">
                <label class="app-filter-label">Applicant Name</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->first_name }} {{ $applicant->last_name }}" readonly>
            </div>
        </div>
    </div>

    <div id="mcToastApp" style="display:none;position:fixed;bottom:24px;right:24px;z-index:99999;background:#006837;color:#fff;padding:12px 20px;border-radius:8px;font-size:0.88rem;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,0.18);">
        <span id="mcToastAppMsg"></span>
    </div>

    <div class="student-table-wrapper applicant-content-shell">
        <div class="applicant-portal-content-header mb-3" style="display:flex;justify-content:space-between;align-items:center;">
            <h3 class="m-0" style="color:#2d3436;font-weight:700;font-size:1rem;text-transform:uppercase;letter-spacing:0.5px;">Medical Records</h3>
            <small style="color:#64748b;font-size:0.78rem;">Upload your required medical documents below.</small>
        </div>

        <div class="app-table-wrap table-responsive" style="overflow: visible;">
            <table class="app-table" id="mcTable" style="table-layout: auto; width: 100%;">
                <thead>
                    <tr>
                        <th style="width:28%; font-weight:800; text-transform:uppercase; font-size:0.75rem;">Documents</th>
                        <th style="width:22%; font-weight:800; text-transform:uppercase; font-size:0.75rem;">Remarks</th>
                        <th style="width:15%; text-align:center; font-weight:800; text-transform:uppercase; font-size:0.75rem;">Status</th>
                        <th style="width:15%; text-align:center; font-weight:800; text-transform:uppercase; font-size:0.75rem;">Date Submitted</th>
                        <th style="width:20%; text-align:center; font-weight:800; text-transform:uppercase; font-size:0.75rem;">Attachment</th>
                        @if(!empty($mcRegistrarMode))
                        <th style="width:60px; text-align:center; font-weight:800; text-transform:uppercase; font-size:0.75rem;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="mcTableBody">
                    @forelse($medicalRequirements as $index => $item)
                    @php
                        $status      = $item['status']         ?? 'Missing';
                        $remarks     = $item['remarks']        ?? '';
                        $dateSub     = $item['date_submitted'] ?? null;
                        $hasFile     = !empty($item['has_file']);
                        $reqId       = $item['id'];
                        $sc = 'status-missing';
                        if ($status === 'Approved')   $sc = 'status-approved';
                        elseif ($status === 'For Review') $sc = 'status-review';
                        elseif ($status === 'Submitted')  $sc = 'status-submitted';
                    @endphp
                    <tr class="mc-row"
                        data-id="{{ $reqId }}"
                        data-doc="{{ $item['document_name'] }}"
                        data-remarks="{{ $remarks }}"
                        data-status="{{ $status }}"
                        data-date="{{ $dateSub ?? '' }}"
                        data-file="{{ $hasFile ? 'Attached' : '' }}">
                        <td class="mc-doc-name-cell">{{ $item['document_name'] }}</td>
                        <td class="mc-remarks-cell">{{ $remarks }}</td>
                        <td class="mc-status-cell" style="text-align:center;">
                            <span class="mc-item-status {{ $sc }}">{{ $status }}</span>
                        </td>
                        <td class="mc-date-cell" style="text-align:center;">
                            {{ $dateSub ? date('m/d/Y', strtotime($dateSub)) : '--' }}
                        </td>
                        <td style="text-align:center;" class="mc-file-cell">
                            @if($hasFile)
                                @if(!empty($mcRegistrarMode))
                                    <button type="button" onclick="mcViewFile('{{ $reqId }}')" style="background:none;border:none;padding:0;color:#006837;font-size:0.82rem;font-weight:800;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;margin:0 auto;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                        Attached
                                    </button>
                                @else
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                                        <button type="button" onclick="mcViewFile('{{ $reqId }}')"
                                            style="background:none;border:none;padding:0;color:#006837;font-size:0.82rem;font-weight:800;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                            Attached
                                        </button>
                                        <button type="button" onclick="mcTriggerUpload('{{ $reqId }}')"
                                            style="background:none;border:1px dashed #94a3b8;border-radius:4px;padding:3px 8px;color:#64748b;font-size:0.73rem;cursor:pointer;">
                                            Replace
                                        </button>
                                    </div>
                                @endif
                            @else
                                @if(!empty($mcRegistrarMode))
                                    <span style="color: #cbd5e1; font-weight: 500;">--</span>
                                @else
                                    <button type="button" onclick="mcTriggerUpload('{{ $reqId }}')"
                                        style="background:none;border:1px dashed #cbd5e1;border-radius:4px;padding:5px 10px;color:#64748b;font-size:0.75rem;display:inline-flex;align-items:center;gap:4px;cursor:pointer;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        Upload
                                    </button>
                                @endif
                            @endif
                            <input type="file" class="mc-row-file-input d-none" data-req-id="{{ $reqId }}" accept="image/*,.pdf" onchange="mcHandleFileSelect(this)">
                        </td>
                        @if(!empty($mcRegistrarMode))
                        <td style="text-align: center;">
                            <div class="apst-action-btn" onclick="mcToggleMenu('{{ $reqId }}', event)" aria-label="Open row actions" title="Actions" style="display: inline-flex; flex-direction: column; gap: 3px; cursor: pointer; padding: 6px 4px; border: 1px solid #ddd; border-radius: 4px; background: #fff;">
                                <span style="width: 4px; height: 4px; background: #555; border-radius: 50%;"></span>
                                <span style="width: 4px; height: 4px; background: #555; border-radius: 50%;"></span>
                                <span style="width: 4px; height: 4px; background: #555; border-radius: 50%;"></span>
                            </div>
                            <div class="apst-dropdown mc-dropdown-menu" id="mcMenu{{ $reqId }}" style="display:none; position: absolute; right: 0; background: #fff; border: 1px solid #ddd; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 100; padding: 5px 0; min-width: 120px;">
                                <button type="button" onclick="mcOpenEditModal('{{ $reqId }}')" style="width: 100%; text-align: left; background: none; border: none; padding: 8px 15px; font-size: 0.85rem; color: #333; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit Status
                                </button>
                                <button type="button" class="apst-del-btn" onclick="mcOpenDeleteModal('{{ $reqId }}')" style="width: 100%; text-align: left; background: none; border: none; padding: 8px 15px; font-size: 0.85rem; color: #dc3545; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete File
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:30px;color:#999;">
                            No medical clearance requirements have been set yet. Please check back later.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Confirm Upload Modal --}}
<div class="req-modal-overlay is-hidden" id="mcConfirmUploadModal" style="display:none;z-index:10001; @if(!empty($mcRegistrarMode)) background: transparent; @endif" onclick="if(event.target===this) mcCancelUpload()">
    <div class="req-modal-box" style="max-width:400px;text-align:center; @if(!empty($mcRegistrarMode)) box-shadow: 0 4px 24px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; @endif">
        <h3 class="req-modal-title" style="color:#006837;">CONFIRM UPLOAD</h3>
        <p style="color:#555;font-size:0.95rem;margin-top:10px;">Upload this file for your medical requirement?</p>
        <p id="mcConfirmFileName" style="font-weight:700;color:#333;font-size:0.9rem;margin:10px 0 4px;word-break:break-all;padding:0 10px;"></p>
        <p id="mcConfirmDocName" style="color:#64748b;font-size:0.82rem;margin-bottom:20px;"></p>
        <div class="req-modal-actions" style="justify-content:center;gap:12px;">
            <button type="button" class="req-btn-cancel" onclick="mcCancelUpload()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#006837;" onclick="mcProceedUpload()">Confirm Upload</button>
        </div>
    </div>
</div>

{{-- Alert Modal --}}
<div class="req-modal-overlay is-hidden" id="mcAlertModal" style="display:none;z-index:10000; @if(!empty($mcRegistrarMode)) background: transparent; @endif">
    <div class="req-modal-box" style="max-width:360px;text-align:center; @if(!empty($mcRegistrarMode)) box-shadow: 0 4px 24px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; @endif">
        <h3 class="req-modal-title" style="color:#d35400;">NOTICE</h3>
        <p id="mcAlertMessage" style="color:#555;font-size:0.95rem;margin-bottom:20px;margin-top:10px;"></p>
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-save" style="background:#006837;" onclick="document.getElementById('mcAlertModal').style.display='none'">OK</button>
        </div>
    </div>
</div>

@if(!empty($mcRegistrarMode))
{{-- Edit Status Modal for Registrar --}}
<div class="req-modal-overlay is-hidden" id="mcEditStatusModal" style="display:none;z-index:10001; background: transparent;" onclick="if(event.target===this) mcCloseModal('mcEditStatusModal')">
    <div class="req-modal-box" style="max-width:450px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); border: 1px solid #e2e8f0;">
        <h3 class="req-modal-title">UPDATE STATUS</h3>
        <div class="req-modal-fields" style="display: flex; flex-direction: column; gap: 15px; text-align: left; margin-top: 15px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">STATUS</label>
                <select class="req-modal-input" id="mcEditInputStatus">
                    <option value="Approved">Approved</option>
                    <option value="For Review">For Review</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Missing">Missing</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">REMARKS</label>
                <input type="text" class="req-modal-input" id="mcEditInputRemarks" placeholder="Enter remarks...">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:20px;">
            <button type="button" class="req-btn-cancel" onclick="mcCloseModal('mcEditStatusModal')">Cancel</button>
            <button type="button" class="req-btn-save" id="mcSaveStatusBtn" onclick="mcProceedSaveStatus()">Save Changes</button>
        </div>
        <input type="hidden" id="mcEditReqId">
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
(function() {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    // ─── Toast ────────────────────────────────────────────────
    function showToast(msg, type) {
        const t = document.getElementById('mcToastApp');
        const m = document.getElementById('mcToastAppMsg');
        if (!t || !m) return;
        t.style.background = type === 'error' ? '#c0392b' : '#006837';
        m.textContent = msg;
        t.style.display = 'block';
        setTimeout(() => { t.style.display = 'none'; }, 3500);
    }

    // ─── Modal helpers ─────────────────────────────────────────
    window.mcCloseModal = function(id) {
        const m = document.getElementById(id);
        if (m) { m.style.display = 'none'; m.classList.add('is-hidden'); }
    };
    window.mcOpenModal = function(id) {
        const m = document.getElementById(id);
        if (m) { m.style.display = 'flex'; m.classList.remove('is-hidden'); }
    };

    // ─── Trigger file input ────────────────────────────────────
    window.mcTriggerUpload = function(reqId) {
        const inp = document.querySelector(`.mc-row-file-input[data-req-id="${reqId}"]`);
        if (inp) inp.click();
    };

    // ─── Handle file selected → show confirm modal ─────────────
    let pendingInput = null;
    window.mcHandleFileSelect = function(input) {
        if (!input.files || !input.files[0]) return;
        pendingInput = input;
        const reqId   = input.getAttribute('data-req-id');
        const row     = document.querySelector(`.mc-row[data-id="${reqId}"]`);
        const docName = row ? row.dataset.doc : '';
        document.getElementById('mcConfirmFileName').textContent = input.files[0].name;
        document.getElementById('mcConfirmDocName').textContent  = docName ? `For: ${docName}` : '';
        window.mcOpenModal('mcConfirmUploadModal');
    };

    window.mcCancelUpload = function() {
        if (pendingInput) pendingInput.value = '';
        pendingInput = null;
        window.mcCloseModal('mcConfirmUploadModal');
    };

    window.mcProceedUpload = function() {
        if (!pendingInput || !pendingInput.files || !pendingInput.files[0]) return;
        const reqId = pendingInput.getAttribute('data-req-id');
        const btn   = document.querySelector('#mcConfirmUploadModal .req-btn-save');
        if (btn) { btn.disabled = true; btn.textContent = 'Uploading…'; }

        const fd = new FormData();
        fd.append('file', pendingInput.files[0]);
        fd.append('_token', CSRF);

        @if($mcRegistrarMode ?? false)
            const uploadUrl = `/registrar/process/application/{{ $applicant->id ?? '' }}/medical-clearance/${reqId}/upload`;
        @else
            const uploadUrl = `/applicant/medical-clearance/${reqId}/upload`;
        @endif

        fetch(uploadUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.textContent = 'Confirm Upload'; }
            if (!data.ok) {
                window.mcCloseModal('mcConfirmUploadModal');
                showToast(data.message || 'Upload failed.', 'error');
                return;
            }
            // Update row UI
            const row = document.querySelector(`.mc-row[data-id="${reqId}"]`);
            if (row) {
                row.dataset.file   = 'Attached';
                row.dataset.status = data.status;
                row.dataset.date   = data.date_submitted;
                row.querySelector('.mc-status-cell').innerHTML = `<span class="mc-item-status status-submitted">Submitted</span>`;
                const d = new Date(data.date_submitted);
                row.querySelector('.mc-date-cell').textContent = (d.getMonth()+1).toString().padStart(2,'0') + '/' + d.getDate().toString().padStart(2,'0') + '/' + d.getFullYear();
                row.querySelector('.mc-file-cell').innerHTML = `
                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <button type="button" onclick="mcViewFile('${reqId}')" style="background:none;border:none;padding:0;color:#006837;font-size:0.82rem;font-weight:800;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                            Attached
                        </button>
                        <button type="button" onclick="mcTriggerUpload('${reqId}')" style="background:none;border:1px dashed #94a3b8;border-radius:4px;padding:3px 8px;color:#64748b;font-size:0.73rem;cursor:pointer;">Replace</button>
                    </div>
                    <input type="file" class="mc-row-file-input d-none" data-req-id="${reqId}" accept="image/*,.pdf" onchange="mcHandleFileSelect(this)">`;
            }
            pendingInput = null;
            window.mcCloseModal('mcConfirmUploadModal');
            showToast('File uploaded successfully!', 'success');
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.textContent = 'Confirm Upload'; }
            window.mcCloseModal('mcConfirmUploadModal');
            showToast('Network error. Please try again.', 'error');
        });
    };

    // ─── View file (opens in new tab) ─────────────────────────
    window.mcViewFile = function(reqId) {
        @if($mcRegistrarMode ?? false)
            window.open(`/registrar/process/application/{{ $applicant->id ?? '' }}/medical-clearance/${reqId}/file`, '_blank');
        @else
            window.open(`/applicant/medical-clearance/${reqId}/file`, '_blank');
        @endif
    };
    // ─── Registrar Action Menu ────────────────────────────────
    window.mcCloseOpenMenus = function() {
        document.querySelectorAll('.mc-dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    };

    window.mcToggleMenu = function(reqId, event) {
        event.stopPropagation();
        const menu = document.getElementById(`mcMenu${reqId}`);
        if (!menu) return;
        
        const isVisible = menu.style.display === 'block';
        window.mcCloseOpenMenus();
        
        if (!isVisible) {
            menu.style.display = 'block';
        }
    };

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.apst-action-btn') && !e.target.closest('.mc-dropdown-menu')) {
            window.mcCloseOpenMenus();
        }
    });

    // ─── Edit Status ──────────────────────────────────────────
    window.mcOpenEditModal = function(reqId) {
        window.mcCloseOpenMenus();
        const row = document.querySelector(`.mc-row[data-id="${reqId}"]`);
        if (!row) return;

        document.getElementById('mcEditReqId').value = reqId;
        document.getElementById('mcEditInputStatus').value = row.dataset.status || 'Missing';
        document.getElementById('mcEditInputRemarks').value = row.dataset.remarks || '';
        
        window.mcOpenModal('mcEditStatusModal');
    };

    window.mcProceedSaveStatus = function() {
        const reqId = document.getElementById('mcEditReqId').value;
        const status = document.getElementById('mcEditInputStatus').value;
        const remarks = document.getElementById('mcEditInputRemarks').value;
        const btn = document.getElementById('mcSaveStatusBtn');

        if (!reqId) return;
        if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }

        @if(!empty($mcRegistrarMode))
            const updateUrl = `/registrar/process/application/{{ $applicant->id ?? '' }}/medical-clearance/${reqId}/status`;
        @else
            const updateUrl = '';
        @endif

        if (!updateUrl) return;

        fetch(updateUrl, {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF 
            },
            body: JSON.stringify({ status: status, remarks: remarks })
        })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.textContent = 'Save Changes'; }
            if (!data.ok) {
                window.mcCloseModal('mcEditStatusModal');
                showToast(data.message || 'Update failed.', 'error');
                return;
            }

            // Update row UI
            const row = document.querySelector(`.mc-row[data-id="${reqId}"]`);
            if (row) {
                row.dataset.status = data.status;
                row.dataset.remarks = data.remarks;
                row.querySelector('.mc-remarks-cell').textContent = data.remarks;
                
                let sc = 'status-missing';
                if (data.status === 'Approved') sc = 'status-approved';
                else if (data.status === 'For Review') sc = 'status-review';
                else if (data.status === 'Submitted') sc = 'status-submitted';
                
                row.querySelector('.mc-status-cell').innerHTML = `<span class="mc-item-status ${sc}">${data.status}</span>`;
            }

            window.mcCloseModal('mcEditStatusModal');
            showToast('Status updated successfully!', 'success');
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.textContent = 'Save Changes'; }
            window.mcCloseModal('mcEditStatusModal');
            showToast('Network error. Please try again.', 'error');
        });
    };

    // ─── Delete File ──────────────────────────────────────────
    window.mcOpenDeleteModal = function(reqId) {
        window.mcCloseOpenMenus();
        if(!confirm('Are you sure you want to delete this file? This action cannot be undone.')) return;

        @if(!empty($mcRegistrarMode))
            const deleteUrl = `/registrar/process/application/{{ $applicant->id ?? '' }}/medical-clearance/${reqId}/file`;
        @else
            const deleteUrl = '';
        @endif

        if (!deleteUrl) return;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: { 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF 
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) {
                showToast(data.message || 'Delete failed.', 'error');
                return;
            }

            // Update row UI
            const row = document.querySelector(`.mc-row[data-id="${reqId}"]`);
            if (row) {
                row.dataset.file = '';
                row.dataset.status = data.status || 'Missing';
                row.dataset.date = '';
                row.querySelector('.mc-status-cell').innerHTML = `<span class="mc-item-status status-missing">Missing</span>`;
                row.querySelector('.mc-date-cell').textContent = '--';
                row.querySelector('.mc-file-cell').innerHTML = `<span style="color: #cbd5e1; font-weight: 500;">--</span>`;
            }

            showToast('File deleted successfully!', 'success');
        })
        .catch(() => {
            showToast('Network error. Please try again.', 'error');
        });
    };
})();
</script>
@endpush
