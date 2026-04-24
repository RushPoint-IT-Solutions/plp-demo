{{-- Add Modal --}}
<div class="req-modal-overlay is-hidden" id="mcAddModal" style="display:none;" onclick="if(event.target===this) mcCloseModal('mcAddModal')">
    <div class="req-modal-box" style="max-width: 480px;">
        <h3 class="req-modal-title">ADD MEDICAL CLEARANCE</h3>
        <div class="req-modal-fields" style="flex-direction: column; gap: 14px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DOCUMENT NAME</label>
                <input type="text" id="mcAddDocName" class="req-modal-input" placeholder="e.g. Chest X-ray, Dental Certificate">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">REMARKS</label>
                <input type="text" id="mcAddRemarks" class="req-modal-input" placeholder="Enter remarks...">
            </div>
            @if(!($hideStatus ?? false))
            <div class="req-modal-field-group">
                <label class="req-modal-label">STATUS</label>
                <select id="mcAddStatus" class="req-modal-input">
                    <option value="Missing">Missing</option>
                    <option value="Submitted">Submitted</option>
                    <option value="For Review">For Review</option>
                    <option value="Approved">Approved</option>
                </select>
            </div>
            @endif
            @if($mcIsApplicant ?? false)
            <div class="req-modal-field-group">
                <label class="req-modal-label">DATE SUBMITTED</label>
                <input type="date" id="mcAddDate" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">ATTACHMENT</label>
                <label class="apc-upload-btn" style="width:100%;border:1px dashed #cbd5e1;border-radius:6px;padding:15px;display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer;background:transparent;">
                    <input type="file" id="mcAddFile" class="d-none" accept="image/*,.pdf" onchange="mcUpdateFileLabel('mcAddFile','mcAddFileLabel')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span id="mcAddFileLabel" style="font-size:0.85rem;color:#334155;font-weight:600;">Click to upload or drag file</span>
                </label>
            </div>
            @endif
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" onclick="mcCloseModal('mcAddModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="mcSaveNew()">Add Record</button>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="req-modal-overlay is-hidden" id="mcEditModal" style="display:none;" onclick="if(event.target===this) mcCloseModal('mcEditModal')">
    <div class="req-modal-box" style="max-width: 480px;">
        <h3 class="req-modal-title">EDIT MEDICAL CLEARANCE</h3>
        <div class="req-modal-fields" style="flex-direction: column; gap: 14px;">
            <input type="hidden" id="mcEditId">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DOCUMENT NAME</label>
                <input type="text" id="mcEditDocName" class="req-modal-input" placeholder="Enter document name..."
                    {{ ($mcIsApplicant ?? false) ? 'readonly' : '' }}
                    style="{{ ($mcIsApplicant ?? false) ? 'background-color:#f8fafc;color:#64748b;cursor:not-allowed;' : '' }}">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">REMARKS</label>
                <input type="text" id="mcEditRemarks" class="req-modal-input" placeholder="Enter remarks..."
                    {{ ($mcIsApplicant ?? false) ? 'readonly' : '' }}
                    style="{{ ($mcIsApplicant ?? false) ? 'background-color:#f8fafc;color:#64748b;cursor:not-allowed;' : '' }}">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">DATE SUBMITTED</label>
                <input type="date" id="mcEditDate" class="req-modal-input" readonly style="background-color:#f8fafc;color:#64748b;cursor:not-allowed;">
            </div>
            @if(!($hideStatus ?? false))
            <div class="req-modal-field-group">
                <label class="req-modal-label">STATUS</label>
                <select id="mcEditStatus" class="req-modal-input">
                    <option value="Missing">Missing</option>
                    <option value="Submitted">Submitted</option>
                    <option value="For Review">For Review</option>
                    <option value="Approved">Approved</option>
                </select>
            </div>
            @endif
            @if($mcIsApplicant ?? false)
            <div class="req-modal-field-group">
                <label class="req-modal-label">ATTACHMENT</label>
                <label class="apc-upload-btn" style="width:100%;border:1px dashed #cbd5e1;border-radius:6px;padding:15px;display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer;background:transparent;">
                    <input type="file" id="mcEditFile" class="d-none" accept="image/*,.pdf" onchange="mcUpdateFileLabel('mcEditFile','mcEditFileLabel')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span id="mcEditFileLabel" style="font-size:0.85rem;color:#334155;font-weight:600;">Click to upload or drag file</span>
                </label>
            </div>
            @endif
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" onclick="mcCloseModal('mcEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="mcSaveEdit()">Save Changes</button>
        </div>
    </div>
</div>

{{-- Upload File Modal (Registrar) --}}
@if(!($mcIsApplicant ?? false))
<div class="req-modal-overlay is-hidden" id="mcUploadModal" style="display:none;" onclick="if(event.target===this) mcCloseModal('mcUploadModal')">
    <div class="req-modal-box" style="max-width: 480px;">
        <h3 class="req-modal-title">UPLOAD ATTACHMENT</h3>
        <input type="hidden" id="mcUploadId">
        <div class="req-modal-fields" style="flex-direction:column;gap:14px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DOCUMENT</label>
                <span id="mcUploadDocName" style="font-weight:600;color:#333;font-size:0.9rem;"></span>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">FILE (PDF, JPG, PNG – max 10MB)</label>
                <label class="apc-upload-btn" style="width:100%;border:1px dashed #cbd5e1;border-radius:6px;padding:20px;display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer;background:transparent;">
                    <input type="file" id="mcUploadFile" class="d-none" accept="image/*,.pdf" onchange="mcUpdateFileLabel('mcUploadFile','mcUploadFileLabel')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span id="mcUploadFileLabel" style="font-size:0.85rem;color:#334155;font-weight:600;">Click to upload or drag file</span>
                </label>
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" onclick="mcCloseModal('mcUploadModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="mcProceedFileUpload()">Upload</button>
        </div>
    </div>
</div>
@endif

{{-- Delete Modal --}}
<div class="req-modal-overlay is-hidden" id="mcDeleteModal" style="display:none;" onclick="if(event.target===this) mcCloseModal('mcDeleteModal')">
    <div class="req-modal-box" style="max-width: 420px; text-align: center;">
        <h3 class="req-modal-title">DELETE MEDICAL RECORD</h3>
        <p style="color:#555;font-size:0.9rem;margin-bottom:6px;">Are you sure you want to remove this record?</p>
        <p id="mcDeleteDocName" style="font-weight:700;color:#333;font-size:0.9rem;margin-bottom:20px;"></p>
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="mcCloseModal('mcDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="mcConfirmDelete()">Delete</button>
        </div>
    </div>
</div>

{{-- Alert Modal --}}
<div class="req-modal-overlay is-hidden" id="mcAlertModal" style="display:none;z-index:10000;" onclick="if(event.target===this) mcCloseModal('mcAlertModal')">
    <div class="req-modal-box" style="max-width:360px;text-align:center;">
        <h3 class="req-modal-title" style="color:#d35400;">REQUIRED FIELDS</h3>
        <p id="mcAlertMessage" style="color:#555;font-size:0.95rem;margin-bottom:20px;margin-top:10px;">Please fill in the document name.</p>
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-save" style="background:#006837;" onclick="mcCloseModal('mcAlertModal')">OK</button>
        </div>
    </div>
</div>

{{-- Confirm Upload Modal (Applicant inline upload) --}}
<div class="req-modal-overlay is-hidden" id="mcConfirmUploadModal" style="display:none;z-index:10001;" onclick="if(event.target===this) mcCancelUpload()">
    <div class="req-modal-box" style="max-width:400px;text-align:center;">
        <h3 class="req-modal-title" style="color:#006837;">CONFIRM ATTACHMENT</h3>
        <p style="color:#555;font-size:0.95rem;margin-top:10px;">Are you sure you want to attach this file?</p>
        <p id="mcConfirmFileName" style="font-weight:700;color:#333;font-size:0.9rem;margin:10px 0 20px;word-break:break-all;padding:0 10px;"></p>
        <div class="req-modal-actions" style="justify-content:center;gap:12px;">
            <button type="button" class="req-btn-cancel" onclick="mcCancelUpload()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#006837;" onclick="mcProceedUpload()">Confirm</button>
        </div>
    </div>
</div>

{{-- View Attachment Modal --}}
<div class="req-modal-overlay is-hidden" id="mcViewModal" style="display:none;" onclick="if(event.target===this) mcCloseModal('mcViewModal')">
    <div class="req-modal-box" style="max-width:800px;width:90%;height:80vh;display:flex;flex-direction:column;">
        <h3 class="req-modal-title" id="mcViewModalTitle">VIEW ATTACHMENT</h3>
        <div style="flex:1;background:#f5f5f5;border:1px solid #ddd;border-radius:8px;margin:15px 0;display:flex;align-items:center;justify-content:center;overflow:hidden;">
            <div id="mcPreviewPlaceholder" style="text-align:center;color:#666;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:10px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                <p id="mcPreviewFilename" style="font-weight:500;">document_filename.pdf</p>
                <p style="font-size:0.9rem;">(File opens in a new tab)</p>
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" onclick="mcCloseModal('mcViewModal')">Close</button>
        </div>
    </div>
</div>
