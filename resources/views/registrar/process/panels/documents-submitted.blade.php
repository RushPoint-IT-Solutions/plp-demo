<div class="apc-card">
    <div class="app-filter-bar apc-panel-filter" style="display: flex; justify-content: space-between; align-items: flex-end; gap: 20px;">
        <div class="app-filter-row" style="flex: 1; margin: 0;">
            <div class="app-filter-group" style="flex:2;">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-input" placeholder="Search document" style="width:100%;">
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">Status</span>
                <select class="app-filter-select" style="width:100%;">
                    <option value="">All</option>
                    <option>Pending</option>
                    <option>Completed</option>
                </select>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 5px;">
            <button type="button" class="apst-new-btn" style="background-color: #006837;" onclick="docSaveChanges()">Save Changes</button>
            <button type="button" class="apst-new-btn" style="min-width: 150px;" onclick="docOpenAddModal()">+ New Document</button>
        </div>
    </div>

    <div class="app-table-wrap table-responsive">
        <table class="app-table" id="docsTable">
            <thead>
                <tr>
                    <th class="apc-col-check apc-col-check--tor">
                        <input type="checkbox" id="docsSelectAll" aria-label="Select all documents">
                    </th>
                    <th>Document Type</th>
                    <th style="width: 150px; text-align: center;">Attachment</th>
                    <th>Remarks</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <th style="width: 80px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $docs = [
                        ['type' => '2x2 Picture', 'status' => 'Pending'],
                        ['type' => 'Birth Certificate (PSA Original)', 'status' => 'Pending'],
                        ['type' => 'Certificate of Good Moral Character', 'status' => 'Pending'],
                        ['type' => 'F-137 A (JHS Permanent Record)', 'status' => 'Pending'],
                        ['type' => 'F138 (SHS Report Card)', 'status' => 'Pending'],
                        ['type' => 'Honorable Dismissal', 'status' => 'Pending'],
                        ['type' => 'Request for Permanent Record (FORM 137 A) / Transcript of Records', 'status' => 'Pending'],
                        ['type' => 'SHS Diploma (Photocopy Only)', 'status' => 'Pending'],
                    ];
                @endphp
                @foreach($docs as $index => $doc)
                <tr data-doc-index="{{ $index }}">
                    <td class="apc-check-cell"><input type="checkbox" class="docs-row-checkbox"></td>
                    <td class="js-doc-type">{{ $doc['type'] }}</td>
                    <td style="text-align: center;">
                        <label class="apc-upload-btn" title="Upload scanned copy">
                            <input type="file" class="js-doc-file-input d-none" accept="image/*,.pdf">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span>Upload</span>
                        </label>
                    </td>
                    <td><input type="text" class="apc-input" placeholder="Type..." /></td>
                    <td><input type="date" class="apc-input apc-input--date" /></td>
                    <td><span class="apc-pill apc-pill--pending">{{ $doc['status'] }}</span></td>
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
<div class="req-modal-overlay is-hidden" id="docItemModal" style="display:none;" onclick="if(event.target===this) docCloseModal('docItemModal')">
    <div class="req-modal-box" style="max-width:500px;">
        <h3 class="req-modal-title" id="docItemModalTitle">ADD NEW DOCUMENT</h3>
        <div class="req-modal-fields" style="flex-direction:column; gap:15px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DOCUMENT TYPE</label>
                <input type="text" class="req-modal-input" id="docInputType" placeholder="e.g. Good Moral Certificate">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">STATUS</label>
                <select class="req-modal-input" id="docInputStatus">
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                </select>
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
        document.getElementById('docInputStatus').value = 'Pending';
        document.getElementById('docItemModal').style.display = 'flex';
    }

    function docOpenEditModal(index) {
        docCloseOpenMenus();
        const row = document.querySelector(`tr[data-doc-index="${index}"]`);
        const type = row.querySelector('.js-doc-type').textContent.trim();
        const status = row.querySelector('.apc-pill').textContent.trim();
        
        document.getElementById('docItemModalTitle').textContent = 'EDIT DOCUMENT';
        document.getElementById('docInputType').value = type;
        document.getElementById('docInputStatus').value = status;
        document.getElementById('docItemModal').style.display = 'flex';
    }

    function docOpenDeleteModal(index) {
        docCloseOpenMenus();
        const row = document.querySelector(`tr[data-doc-index="${index}"]`);
        const type = row.querySelector('.js-doc-type').textContent.trim();
        document.getElementById('docDeleteDetail').textContent = type;
        document.getElementById('docDeleteModal').style.display = 'flex';
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

    function docSaveChanges() {
        document.getElementById('docSuccessModal').style.display = 'flex';
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.apst-action-btn') && !e.target.closest('.apst-dropdown')) {
            docCloseOpenMenus();
        }
    });

    window.addEventListener('scroll', docCloseOpenMenus, true);
</script>
@endpush
