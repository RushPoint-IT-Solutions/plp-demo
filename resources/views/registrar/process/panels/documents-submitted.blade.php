<div class="apc-card">
    <div class="app-filter-bar apc-panel-filter">
        <div class="app-filter-grid">
            <div class="app-filter-item app-filter-item--search">
                <span class="app-filter-label">SEARCH DOCUMENT</span>
                <input type="text" class="app-filter-input" id="docSearch" placeholder="Search by type or remarks...">
            </div>
            <div class="app-filter-item app-filter-item--status">
                <span class="app-filter-label">STATUS FILTER</span>
                <select class="app-filter-select" id="docStatusFilter">
                    <option value="">All Documents</option>
                    <option value="Pending">Pending Review</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Verified">Verified / Approved</option>
                    <option value="To be followed up">To be followed up</option>
                    <option value="Missing">Missing / Rejected</option>
                </select>
            </div>
            <div class="app-filter-item app-filter-item--action">
                <button type="button" class="apst-new-btn" style="width: 100%;" onclick="docOpenAddModal()">+ New Document</button>
            </div>
        </div>
    </div>

    <div class="app-table-wrap table-responsive">
        <table class="app-table" id="docsTable" style="table-layout: auto; width: 100%;">
            <thead>
                <tr>
                    <th style="width: 28%; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Document Type</th>
                    <th style="width: 22%; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Remarks</th>
                    <th style="width: 140px; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Date Submitted</th>
                    <th style="width: 120px; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Status</th>
                    <th style="width: 110px; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Attachment</th>
                    <th style="width: 60px; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $docs = [
                        ['type' => '2x2 Picture', 'status' => 'Verified', 'remarks' => 'Clear background', 'date' => '2024-03-15', 'file' => '2x2_photo.jpg'],
                        ['type' => 'Birth Certificate (PSA Original)', 'status' => 'Submitted', 'remarks' => 'Original copy needed for verification', 'date' => '2024-04-10', 'file' => 'birth_cert.pdf'],
                        ['type' => 'Certificate of Good Moral Character', 'status' => 'Pending', 'remarks' => 'Verification in progress', 'date' => '2024-04-12', 'file' => 'good_moral.pdf'],
                        ['type' => 'F-137 A (JHS Permanent Record)', 'status' => 'Missing', 'remarks' => 'To follow (Request in progress)', 'date' => '', 'file' => null],
                        ['type' => 'F138 (SHS Report Card)', 'status' => 'Verified', 'remarks' => 'GWA: 92.50', 'date' => '2024-03-15', 'file' => 'shs_report_card.pdf'],
                        ['type' => 'Honorable Dismissal', 'status' => 'Pending', 'remarks' => 'Scanned copy only', 'date' => '2024-04-11', 'file' => 'honorable_dismissal.pdf'],
                        ['type' => 'Request for Permanent Record (FORM 137 A)', 'status' => 'Missing', 'remarks' => 'Waiting for official release', 'date' => '', 'file' => null],
                        ['type' => 'SHS Diploma (Photocopy Only)', 'status' => 'Submitted', 'remarks' => 'Pending physical copy', 'date' => '2024-04-10', 'file' => 'diploma_copy.pdf'],
                    ];

                    $statusClasses = [
                        'Verified' => 'status-approved',
                        'Pending' => 'status-review',
                        'Submitted' => 'status-submitted',
                        'To be followed up' => 'status-followup',
                        'Missing' => 'status-not-submitted'
                    ];
                @endphp
                @foreach($docs as $index => $doc)
                <tr data-doc-index="{{ $index }}">
                    <td class="js-doc-type">{{ $doc['type'] }}</td>
                    <td class="js-doc-remarks">{{ $doc['remarks'] ?: '--' }}</td>
                    <td class="js-doc-date">{{ $doc['date'] ?: '--' }}</td>
                    <td style="text-align: center;">
                        <span class="mc-item-status {{ $statusClasses[$doc['status']] ?? 'status-review' }} js-doc-status" style="padding: 4px 10px; font-size: 0.75rem;">{{ $doc['status'] }}</span>
                    </td>
                    <td style="text-align: center;" class="js-doc-file-status">
                        @if($doc['file'])
                            <button type="button" class="apst-view-link" onclick="docViewAttachment({{ $index }})" style="background: none; border: none; padding: 0; color: var(--plp-green); font-size: 0.82rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 5px; cursor: pointer; margin: 0 auto; text-decoration: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                Attached
                            </button>
                        @else
                            <span style="color: #cbd5e1; font-weight: 500;">--</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div class="apst-action-btn" onclick="docToggleMenu({{ $index }}, event)" aria-label="Open row actions" title="Actions">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="apst-dropdown" id="docMenu{{ $index }}">
                            <button type="button" onclick="docOpenEditModal({{ $index }})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                Edit
                            </button>
                            <button type="button" class="apst-del-btn" onclick="docOpenDeleteModal({{ $index }})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modals --}}
<div class="req-modal-overlay is-hidden" id="docViewModal" style="display:none;" onclick="if(event.target===this) docCloseModal('docViewModal')">
    <div class="req-modal-box" style="max-width:800px; width:90%; height:80vh; display:flex; flex-direction:column;">
        <h3 class="req-modal-title" id="docViewModalTitle">VIEW ATTACHMENT</h3>
        <div class="doc-preview-container" style="flex:1; background:#f5f5f5; border:1px solid #ddd; border-radius:8px; margin:15px 0; display:flex; align-items:center; justify-content:center; overflow:hidden;">
            <div id="docPreviewPlaceholder" style="text-align:center; color:#666;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:10px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                <p id="docPreviewFilename" style="font-weight:500;">document_filename.pdf</p>
                <p style="font-size:0.9rem;">(Preview not available in demo mode)</p>
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" onclick="docCloseModal('docViewModal')">Close</button>
            <button type="button" class="req-btn-save" style="background-color:#006837;">Download</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay is-hidden" id="docItemModal" style="display:none;" onclick="if(event.target===this) docCloseModal('docItemModal')">
    <div class="req-modal-box" style="max-width:500px;">
        <h3 class="req-modal-title" id="docItemModalTitle">ADD NEW DOCUMENT</h3>
        <div class="req-modal-fields" style="display: flex; flex-direction: column; gap: 15px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DOCUMENT TYPE</label>
                <input type="text" class="req-modal-input" id="docInputType" placeholder="e.g. Good Moral Certificate">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">REMARKS</label>
                <input type="text" class="req-modal-input" id="docInputRemarks" placeholder="Enter remarks...">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">DATE SUBMITTED</label>
                    <input type="date" class="req-modal-input" id="docInputDate">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">STATUS</label>
                    <select class="req-modal-input" id="docInputStatus">
                        <option value="Pending">Pending</option>
                        <option value="Submitted">Submitted</option>
                        <option value="Verified">Verified</option>
                        <option value="To be followed up">To be followed up</option>
                        <option value="Missing">Missing</option>
                    </select>
                </div>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">ATTACHMENT</label>
                <label class="apc-upload-btn" style="width: 100%; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 15px; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer; background: transparent;">
                    <input type="file" id="docInputFile" class="d-none" accept="image/*,.pdf">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span id="docInputFileLabel" style="font-size: 0.85rem; color: #334155; font-weight: 600;">Click to upload or drag file</span>
                </label>
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:20px;">
            <button type="button" class="req-btn-cancel" onclick="docCloseModal('docItemModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="docSaveItem()">Save</button>
        </div>
    </div>
</div>

@include('includes.registrar-delete-modal', [
    'id' => 'docDeleteModal',
    'title' => 'DELETE DOCUMENT',
    'message' => 'Are you sure you want to delete this document?',
    'confirmBtnText' => 'Delete',
    'cancelAction' => "docCloseModal('docDeleteModal')",
    'confirmAction' => 'docConfirmDelete()',
    'detailId' => 'docDeleteDetail'
])

@include('includes.registrar-success-modal', [
    'id' => 'docSuccessModal',
    'onClose' => "docCloseModal('docSuccessModal')"
])

@push('scripts')
<script>
    function docCloseOpenMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(menu => {
            menu.classList.remove('open');
            menu.classList.remove('drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.bottom = '';
        });
    }

    function docToggleMenu(index, event) {
        event.stopPropagation();
        const menu = document.getElementById(`docMenu${index}`);
        if (!menu) return;

        const isOpen = menu.classList.contains('open');
        docCloseOpenMenus();

        if (!isOpen) {
            const btn = event.currentTarget;
            const rect = btn.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const menuWidth = 120; // Internal width reference

            menu.style.left = (rect.left - menuWidth - 8) + 'px';

            if (spaceBelow < 120) {
                menu.classList.add('drop-up');
                menu.style.top = 'auto';
                menu.style.bottom = (window.innerHeight - rect.bottom) + 'px';
            } else {
                menu.style.top = rect.top + 'px';
                menu.style.bottom = 'auto';
            }
            menu.classList.add('open');
        }
    }

    function docOpenAddModal() {
        document.getElementById('docItemModalTitle').textContent = 'ADD NEW DOCUMENT';
        document.getElementById('docInputType').value = '';
        document.getElementById('docInputRemarks').value = '';
        document.getElementById('docInputDate').value = '';
        document.getElementById('docInputStatus').value = 'Pending';
        document.getElementById('docInputFile').value = '';
        document.getElementById('docInputFileLabel').textContent = 'Click to upload or drag file';
        document.getElementById('docItemModal').style.display = 'flex';
    }

    function docOpenEditModal(index) {
        docCloseOpenMenus();
        const row = document.querySelector(`tr[data-doc-index="${index}"]`);
        const type = row.querySelector('.js-doc-type').textContent.trim();
        const remarks = row.querySelector('.js-doc-remarks').textContent.trim();
        const dateRaw = row.querySelector('.js-doc-date').textContent.trim();
        const status = row.querySelector('.js-doc-status').textContent.trim();
        const fileStatus = row.querySelector('.js-doc-file-status').textContent.trim();
        
        const dateVal = (dateRaw === '--' || !dateRaw) ? '' : dateRaw;
        const remarksVal = (remarks === '--' || !remarks) ? '' : remarks;

        document.getElementById('docItemModalTitle').textContent = 'EDIT DOCUMENT';
        document.getElementById('docInputType').value = type;
        document.getElementById('docInputRemarks').value = remarksVal;
        document.getElementById('docInputDate').value = dateVal;
        document.getElementById('docInputStatus').value = status;
        document.getElementById('docInputFile').value = '';
        
        const label = document.getElementById('docInputFileLabel');
        if (fileStatus === 'Attached') {
            label.textContent = 'Change attachment (existing file found)';
        } else {
            label.textContent = 'Click to upload or drag file';
        }

        document.getElementById('docItemModal').style.display = 'flex';
    }

    function docOpenDeleteModal(index) {
        docCloseOpenMenus();
        const row = document.querySelector(`tr[data-doc-index="${index}"]`);
        const type = row.querySelector('.js-doc-type').textContent.trim();
        document.getElementById('docDeleteDetail').textContent = type;
        document.getElementById('docDeleteModal').style.display = 'flex';
    }

    function docViewAttachment(index) {
        const row = document.querySelector(`tr[data-doc-index="${index}"]`);
        const type = row.querySelector('.js-doc-type').textContent.trim();
        document.getElementById('docViewModalTitle').textContent = `VIEW ATTACHMENT - ${type}`;
        document.getElementById('docPreviewFilename').textContent = `${type.toLowerCase().replace(/ /g, '_')}_scanned.pdf`;
        document.getElementById('docViewModal').style.display = 'flex';
    }

    function docCloseModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    }

    function docSaveItem() {
        docCloseModal('docItemModal');
        document.getElementById('docSuccessModal').style.display = 'flex';
    }

    function docConfirmDelete() {
        docCloseModal('docDeleteModal');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.apst-action-btn') && !e.target.closest('.apst-dropdown')) {
            docCloseOpenMenus();
        }
    });

    const modalFileInp = document.getElementById('docInputFile');
    if (modalFileInp) {
        modalFileInp.addEventListener('change', function() {
            const label = document.getElementById('docInputFileLabel');
            if (this.files && this.files[0]) {
                label.textContent = `Selected: ${this.files[0].name}`;
            }
        });
    }

    window.addEventListener('scroll', docCloseOpenMenus, true);

    // Filter Logic
    function docApplyFilters() {
        const searchQuery = document.getElementById('docSearch').value.toLowerCase();
        const statusFilter = document.getElementById('docStatusFilter').value;
        const rows = document.querySelectorAll('#docsTable tbody tr');

        rows.forEach(row => {
            if (row.classList.contains('doclist-empty-row')) return;

            const type = row.querySelector('.js-doc-type').textContent.toLowerCase();
            const remarks = row.querySelector('.js-doc-remarks').textContent.toLowerCase();
            const status = row.querySelector('.js-doc-status').textContent.trim();

            const matchesSearch = type.includes(searchQuery) || remarks.includes(searchQuery);
            const matchesStatus = statusFilter === "" || status === statusFilter;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    document.getElementById('docSearch').addEventListener('input', docApplyFilters);
    document.getElementById('docStatusFilter').addEventListener('change', docApplyFilters);
</script>
@endpush
