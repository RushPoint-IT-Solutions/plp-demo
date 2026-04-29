@extends(!empty($applicationFormEmbedded) ? 'layouts.applicant-embedded' : 'layouts.applicant')

@section('title', 'PLP - Documents Submitted')
@section('page-title', 'DOCUMENTS SUBMITTED')

@push('styles')
<style>
/* Re-use medical clearance CSS for general table styling if applicable, or generic classes */
.mc-item-status { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; }
.status-approved { background: #e6f7ef; color: #006837; }
.status-review { background: #fff8e6; color: #b7791f; }
.status-submitted { background: #e6f4ff; color: #0056b3; }
.status-not-submitted, .status-missing { background: #fff5f5; color: #c53030; }
.apst-dropdown { display: none; position: absolute; background: white; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); padding: 4px; min-width: 120px; z-index: 1000; border: 1px solid #e2e8f0; }
.apst-dropdown.open { display: block; }
.apst-dropdown button { width: 100%; text-align: left; padding: 8px 12px; background: none; border: none; font-size: 0.85rem; color: #334155; display: flex; align-items: center; gap: 8px; cursor: pointer; border-radius: 4px; }
.apst-dropdown button:hover { background: #f1f5f9; color: #0f172a; }
.apst-action-btn { width: 28px; height: 28px; border-radius: 4px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; cursor: pointer; margin: 0 auto; transition: background 0.2s; }
.apst-action-btn:hover { background: #f1f5f9; }
.apst-action-btn span { width: 4px; height: 4px; background: #64748b; border-radius: 50%; }
</style>
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

    <div class="student-table-wrapper applicant-content-shell">
        <div class="applicant-portal-content-header mb-3" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="m-0" style="color: #2d3436; font-weight: 700; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px;">Application Requirements</h3>
        </div>

        <div class="app-table-wrap table-responsive">
            <table class="app-table" id="docTable">
                <thead>
                    <tr>
                        <th style="width: 25%;">Documents</th>
                        <th style="width: 25%;">Remarks</th>
                        <th style="width: 15%; text-align: center;">Status</th>
                        <th style="width: 15%; text-align: center;">Date Submitted</th>
                        <th style="width: 15%; text-align: center;">Attachment</th>
                        <th style="width: 60px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="docTableBody">
                    @php
                        $docApplicantSampleData = [
                            (object)['id' => 1, 'document_name' => '2x2 Picture', 'remarks' => 'Clear background', 'date_submitted' => '2024-03-15', 'status' => 'Approved', 'file' => '2x2_photo.jpg'],
                            (object)['id' => 2, 'document_name' => 'Birth Certificate (PSA)', 'remarks' => 'Original copy needed', 'date_submitted' => '2024-04-10', 'status' => 'For Review', 'file' => 'birth_cert.pdf'],
                            (object)['id' => 3, 'document_name' => 'F138 (SHS Report Card)', 'remarks' => 'GWA: 92.50', 'date_submitted' => '2024-03-15', 'status' => 'Approved', 'file' => 'shs_report_card.pdf'],
                            (object)['id' => 4, 'document_name' => 'Good Moral Character', 'remarks' => 'Requirement from High School', 'date_submitted' => null, 'status' => 'Missing', 'file' => null],
                        ];
                        $docApplicantDisplayData = (!empty($applicantDocuments) && count($applicantDocuments) > 0) ? $applicantDocuments : $docApplicantSampleData;
                    @endphp
                    @foreach($docApplicantDisplayData as $index => $item)
                    @php
                        $hasFile = isset($item->file) && $item->file;
                        $status = $item->status ?? 'Missing';
                        $statusClass = 'status-missing';
                        if ($status === 'Approved' || $status === 'Verified') $statusClass = 'status-approved';
                        if ($status === 'For Review' || $status === 'Pending') $statusClass = 'status-review';
                        if ($status === 'Submitted') $statusClass = 'status-submitted';
                    @endphp
                    <tr class="doc-row" data-id="{{ $item->id }}" data-doc="{{ $item->document_name }}" data-remarks="{{ $item->remarks }}" data-date="{{ $item->date_submitted }}" data-status="{{ $status }}" data-file="{{ $hasFile ? 'Attached' : '' }}">
                        <td>{{ $item->document_name }}</td>
                        <td class="doc-remarks-cell">{{ $item->remarks ?: '--' }}</td>
                        <td class="doc-status-cell" style="text-align: center;">
                            <span class="mc-item-status {{ $statusClass }}">{{ $status }}</span>
                        </td>
                        <td class="doc-date-cell" style="text-align: center;">{{ $item->date_submitted ? date('m/d/Y', strtotime($item->date_submitted)) : '--' }}</td>
                        <td style="text-align: center;" class="doc-file-cell">
                            <input type="file" class="doc-row-file-input d-none" accept="image/*,.pdf" onchange="docAutoUpload(this, '{{ $item->id }}')">
                            @if($hasFile)
                                <div class="doc-attachment-wrapper" style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <button type="button" class="apst-view-link" onclick="window.docViewAttachment('{{ $item->id }}')" style="background: none; border: none; padding: 0; color: #006837; font-size: 0.82rem; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 5px; cursor: pointer; margin: 0 auto; text-decoration: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                        Attached
                                    </button>
                                </div>
                            @else
                                <button type="button" onclick="this.parentElement.querySelector('.doc-row-file-input').click()" style="background: none; border: 1px dashed #cbd5e1; border-radius: 4px; padding: 4px 8px; color: #64748b; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; margin: 0 auto;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    Upload
                                </button>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="apst-action-btn" data-doc-menu-toggle="docMenu{{ $index }}" aria-label="Open row actions" title="Actions">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="apst-dropdown" id="docMenu{{ $index }}">
                                <button type="button" onclick="docOpenEdit({{ $index }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit Record
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modals --}}
<div class="req-modal-overlay is-hidden" id="docViewModal" style="display:none;" onclick="if(event.target===this) window.docCloseModal('docViewModal')">
    <div class="req-modal-box" style="max-width:800px; width:90%; height:80vh; display:flex; flex-direction:column;">
        <h3 class="req-modal-title" id="docViewModalTitle">VIEW ATTACHMENT</h3>
        <div class="doc-preview-container" style="flex:1; background:#f5f5f5; border:1px solid #ddd; border-radius:8px; margin:15px 0; display:flex; align-items:center; justify-content:center; overflow:hidden;">
            <div id="docPreviewPlaceholder" style="text-align:center; color:#666;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:10px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                <p id="docPreviewFilename" style="font-weight:500;">document_filename.pdf</p>
                <p style="font-size:0.9rem;">(Preview not available in demo mode)</p>
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top: 20px;">
            <button type="button" class="cfg-btn-secondary" onclick="window.docCloseModal('docViewModal')" style="padding: 8px 16px; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; background: #fff;">Close</button>
            <button type="button" style="background-color:#006837; padding: 8px 16px; color: #fff; border: none; border-radius: 4px; font-weight: 600;">Download</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay is-hidden" id="docEditModal" style="display:none;" onclick="if(event.target===this) window.docCloseModal('docEditModal')">
    <div class="req-modal-box" style="max-width:500px;">
        <h3 class="req-modal-title" id="docItemModalTitle" style="color: #006837; margin-top:0;">EDIT RECORD</h3>
        <input type="hidden" id="docEditId">
        <div class="req-modal-fields" style="display: flex; flex-direction: column; gap: 15px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label" style="font-weight: 600;">DOCUMENT</label>
                <input type="text" class="req-modal-input" id="docEditDocName" style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 8px;" readonly>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label" style="font-weight: 600;">REMARKS</label>
                <input type="text" class="req-modal-input" id="docEditRemarks" style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 8px;" readonly>
            </div>
            <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label" style="font-weight: 600;">DATE SUBMITTED</label>
                    <input type="date" class="req-modal-input" id="docEditDate" style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 8px;">
                </div>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label" style="font-weight: 600;">ATTACHMENT</label>
                <label class="apc-upload-btn" style="width: 100%; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 15px; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer; background: transparent;">
                    <input type="file" id="docEditFile" class="d-none" accept="image/*,.pdf" style="display: none;" onchange="window.docUpdateFileLabel('docEditFile', 'docEditFileLabel')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span id="docEditFileLabel" style="font-size: 0.85rem; color: #334155; font-weight: 600;">Click to upload or drag file</span>
                </label>
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="cfg-btn-secondary" onclick="window.docCloseModal('docEditModal')" style="padding: 8px 16px; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; background: #fff;">Cancel</button>
            <button type="button" onclick="window.docSaveEdit()" style="background-color:#006837; padding: 8px 16px; color: #fff; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay is-hidden" id="docConfirmUploadModal" style="display:none;" onclick="if(event.target===this) window.docCancelUpload()">
    <div class="req-modal-box" style="max-width:400px;">
        <h3 class="req-modal-title" style="color: #006837; margin-top:0;">CONFIRM UPLOAD</h3>
        <p>Are you sure you want to attach <strong id="docConfirmFileName"></strong> to this requirement?</p>
        <div class="req-modal-actions" style="margin-top:20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="cfg-btn-secondary" onclick="window.docCancelUpload()" style="padding: 8px 16px; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; background: #fff;">Cancel</button>
            <button type="button" onclick="window.docProceedUpload()" style="background-color:#006837; padding: 8px 16px; color: #fff; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">Upload</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Action Menu Positioning and Toggling
    window.docCloseAllMenus = function() {
        document.querySelectorAll('.apst-dropdown.open').forEach(menu => {
            menu.classList.remove('open');
            menu.style.display = 'none';
        });
    };

    window.docPositionMenu = function(menu, trigger) {
        const rect = trigger.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.display = 'block';
        menu.classList.add('open');
        
        let left = rect.right - 150;
        let top = rect.bottom + 6;

        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        menu.style.zIndex = '9999';

        const menuRect = menu.getBoundingClientRect();
        if (menuRect.right > window.innerWidth - 8) {
            menu.style.left = (window.innerWidth - menuRect.width - 8) + 'px';
        }
        if (menuRect.bottom > window.innerHeight - 8) {
            menu.style.top = (rect.top - menuRect.height - 6) + 'px';
        }
    };

    document.addEventListener('click', function(event) {
        const toggle = event.target.closest('[data-doc-menu-toggle]');
        if (toggle) {
            event.preventDefault();
            event.stopPropagation();
            const menuId = toggle.getAttribute('data-doc-menu-toggle');
            const menu = document.getElementById(menuId);
            if (!menu) return;

            const isOpen = menu.classList.contains('open');
            window.docCloseAllMenus();
            
            if (!isOpen) {
                window.docPositionMenu(menu, toggle);
            }
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            window.docCloseAllMenus();
        }
    });

    window.addEventListener('scroll', () => window.docCloseAllMenus(), true);
    window.addEventListener('resize', () => window.docCloseAllMenus());

    window.docCloseModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('is-hidden');
        }
    };

    window.docOpenModal = function(id) {
        window.docCloseAllMenus();
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('is-hidden');
        }
    };

    window.docUpdateFileLabel = function(inputId, labelId) {
        const input = document.getElementById(inputId);
        const label = document.getElementById(labelId);
        if (input && label) {
            if (input.files && input.files.length > 0) {
                label.textContent = `Selected: ${input.files[0].name}`;
            } else {
                label.textContent = 'Click to upload or drag file';
            }
        }
    };

    window.docViewAttachment = function(id) {
        const row = document.querySelector(`.doc-row[data-id="${id}"]`);
        if (row) {
            const docName = row.getAttribute('data-doc');
            document.getElementById('docViewModalTitle').textContent = `VIEW ATTACHMENT - ${docName}`;
            document.getElementById('docPreviewFilename').textContent = `${docName.toLowerCase().replace(/ /g, '_')}_scanned.pdf`;
            window.docOpenModal('docViewModal');
        }
    };

    let pendingUploadInput = null;
    let pendingUploadId = null;

    window.docAutoUpload = function(input, id) {
        if (input.files && input.files[0]) {
            pendingUploadInput = input;
            pendingUploadId = id;
            
            const fileName = input.files[0].name;
            const fileNameEl = document.getElementById('docConfirmFileName');
            if (fileNameEl) fileNameEl.textContent = fileName;
            
            window.docOpenModal('docConfirmUploadModal');
        }
    };

    window.docCancelUpload = function() {
        if (pendingUploadInput) {
            pendingUploadInput.value = '';
        }
        pendingUploadInput = null;
        pendingUploadId = null;
        window.docCloseModal('docConfirmUploadModal');
    };

    window.docProceedUpload = function() {
        const input = pendingUploadInput;
        const id = pendingUploadId;
        
        if (input && input.files && input.files[0]) {
            const fileName = input.files[0].name;
            const row = document.querySelector(`.doc-row[data-id="${id}"]`);
            if (row) {
                const today = new Date().toISOString().split('T')[0];
                row.setAttribute('data-date', today);
                row.setAttribute('data-status', 'Submitted');
                
                const statusCell = row.querySelector('.doc-status-cell');
                if (statusCell) {
                    statusCell.innerHTML = `<span class="mc-item-status status-submitted">Submitted</span>`;
                }

                const dateCell = row.querySelector('.doc-date-cell');
                if (dateCell) dateCell.textContent = new Date(today).toLocaleDateString('en-US');
                
                const fileCell = row.querySelector('.doc-file-cell');
                if (fileCell) {
                    fileCell.innerHTML = `
                        <input type="file" class="doc-row-file-input d-none" accept="image/*,.pdf" onchange="docAutoUpload(this, '${id}')">
                        <div class="doc-attachment-wrapper" style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                            <button type="button" class="apst-view-link" onclick="window.docViewAttachment('${id}')" style="background: none; border: none; padding: 0; color: #006837; font-size: 0.82rem; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 5px; cursor: pointer; margin: 0 auto; text-decoration: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                Attached
                            </button>
                        </div>`;
                }
            }
        }
        
        pendingUploadInput = null;
        pendingUploadId = null;
        window.docCloseModal('docConfirmUploadModal');
    };

    window.docOpenEditById = function(id) {
        const row = document.querySelector(`.doc-row[data-id="${id}"]`);
        if (row) {
            document.getElementById('docEditId').value = id;
            document.getElementById('docEditDocName').value = row.dataset.doc || '';
            document.getElementById('docEditRemarks').value = row.dataset.remarks || '';
            document.getElementById('docEditDate').value = row.dataset.date || '';
            document.getElementById('docEditFile').value = '';
            
            const fileStatus = row.getAttribute('data-file');
            const label = document.getElementById('docEditFileLabel');
            if (fileStatus === 'Attached') {
                label.textContent = 'Change attachment (existing file found)';
            } else {
                label.textContent = 'Click to upload or drag file';
            }
            window.docOpenModal('docEditModal');
        }
    };

    window.docOpenEdit = function(index) {
        const rows = document.querySelectorAll('#docTableBody .doc-row');
        if (rows[index]) window.docOpenEditById(rows[index].dataset.id);
    };

    window.docSaveEdit = function() {
        const id = document.getElementById('docEditId').value;
        const date = document.getElementById('docEditDate').value;
        const fileInput = document.getElementById('docEditFile');

        if (!date && (!fileInput || !fileInput.files || fileInput.files.length === 0)) {
            alert('Please select a date or upload a file.');
            return;
        }

        const row = document.querySelector(`.doc-row[data-id="${id}"]`);
        if (row) {
            if (date) {
                row.setAttribute('data-date', date);
                row.children[3].textContent = new Date(date).toLocaleDateString('en-US');
            }
            
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                row.setAttribute('data-status', 'Submitted');
                row.setAttribute('data-file', 'Attached');
                
                const statusCell = row.querySelector('.doc-status-cell');
                if (statusCell) {
                    statusCell.innerHTML = `<span class="mc-item-status status-submitted">Submitted</span>`;
                }

                const fileCell = row.querySelector('.doc-file-cell');
                if (fileCell) {
                    fileCell.innerHTML = `
                        <input type="file" class="doc-row-file-input d-none" accept="image/*,.pdf" onchange="docAutoUpload(this, '${id}')">
                        <div class="doc-attachment-wrapper" style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                            <button type="button" class="apst-view-link" onclick="window.docViewAttachment('${id}')" style="background: none; border: none; padding: 0; color: #006837; font-size: 0.82rem; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 5px; cursor: pointer; margin: 0 auto; text-decoration: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                Attached
                            </button>
                        </div>`;
                }

                if (!date) {
                    const today = new Date().toISOString().split('T')[0];
                    row.setAttribute('data-date', today);
                    row.children[3].textContent = new Date(today).toLocaleDateString('en-US');
                }
            }
            window.docCloseModal('docEditModal');
        }
    };
</script>
@endpush
