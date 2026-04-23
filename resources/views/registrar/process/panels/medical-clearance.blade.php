@push('styles')
<link rel="stylesheet" href="{{ asset('css/medical-clearance.css') }}?v={{ time() }}">
@endpush

<div class="apc-card mc-panel-card">
    <div class="app-filter-bar apc-panel-filter">
        <div class="app-filter-grid">
            <div class="app-filter-item app-filter-item--search">
                <span class="app-filter-label">SEARCH MEDICAL DOCUMENT</span>
                <input type="text" class="app-filter-input" id="mcSearch" placeholder="Search by name or remarks...">
            </div>
            <div class="app-filter-item app-filter-item--status">
                <span class="app-filter-label">STATUS FILTER</span>
                <select class="app-filter-select" id="mcStatusFilter">
                    <option value="">All Documents</option>
                    <option value="Submitted">Submitted</option>
                    <option value="For Review">For Review</option>
                    <option value="Approved">Approved</option>
                    <option value="Missing">Missing</option>
                </select>
            </div>
            <div class="app-filter-item app-filter-item--entries">
                <span class="app-filter-label">SHOW ENTRIES</span>
                <select class="app-filter-select" id="mcEntries">
                    <option value="10">10 Entries</option>
                    <option value="25">25 Entries</option>
                    <option value="50">50 Entries</option>
                </select>
            </div>
            <div class="app-filter-item app-filter-item--action">
                <button type="button" class="apst-new-btn" id="mcAddBtn">+ Add New</button>
            </div>
        </div>
    </div>

    <div class="app-table-wrap table-responsive">
        <table class="app-table" id="mcTable">
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
            <tbody id="mcRegTableBody">
                @php
                    $sampleData = [
                        (object)['id' => 1, 'document_name' => 'Chest X-ray', 'remarks' => 'Normal / Cleared', 'status' => 'Approved', 'date_submitted' => '2026-04-15', 'file' => 'chest_xray.pdf'],
                        (object)['id' => 2, 'document_name' => 'Dental Certificate', 'remarks' => 'Needs cleaning', 'status' => 'For Review', 'date_submitted' => '2026-04-18', 'file' => null],
                        (object)['id' => 3, 'document_name' => 'Drug Test Result', 'remarks' => 'Negative', 'status' => 'Submitted', 'date_submitted' => '2026-04-20', 'file' => 'drug_test.pdf'],
                        (object)['id' => 4, 'document_name' => 'Good Moral', 'remarks' => '', 'status' => 'Missing', 'date_submitted' => null, 'file' => null],
                    ];
                    $displayData = (isset($medicalClearances) && $medicalClearances->count() > 0) ? $medicalClearances : $sampleData;
                @endphp
                @foreach($displayData as $index => $item)
                @php
                    $hasFile = isset($item->file) && $item->file;
                    $status = $item->status ?? 'Missing';
                    $statusClass = 'status-missing';
                    if ($status === 'Approved') $statusClass = 'status-approved';
                    if ($status === 'For Review') $statusClass = 'status-review';
                    if ($status === 'Submitted') $statusClass = 'status-submitted';
                @endphp
                <tr class="mc-row" data-id="{{ $item->id }}" data-doc="{{ $item->document_name }}" data-remarks="{{ $item->remarks }}" data-status="{{ $status }}" data-date="{{ $item->date_submitted ?? '' }}" data-file="{{ $hasFile ? 'Attached' : '' }}">
                    <td>{{ $item->document_name }}</td>
                    <td class="mc-remarks-cell">{{ $item->remarks }}</td>
                    <td style="text-align: center;">
                        <span class="mc-item-status {{ $statusClass }}">{{ $status }}</span>
                    </td>
                    <td class="mc-date-cell" style="text-align: center;">{{ $item->date_submitted ? date('m/d/Y', strtotime($item->date_submitted)) : '--' }}</td>
                    <td style="text-align: center;" class="mc-file-cell">
                        @if($hasFile)
                            <button type="button" class="apst-view-link" onclick="window.mcViewAttachment('{{ $item->id }}')" style="background: none; border: none; padding: 0; color: var(--plp-green); font-size: 0.82rem; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 5px; cursor: pointer; margin: 0 auto; text-decoration: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                Attached
                            </button>
                        @else
                            <span style="color: #999; font-size: 0.85rem;">--</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div class="apst-action-btn" data-mc-menu-toggle="mcRegMenu_{{ $item->id }}" aria-label="Open row actions" title="Actions">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="apst-dropdown" id="mcRegMenu_{{ $item->id }}">
                            <button type="button" onclick="mcOpenEditById('{{ $item->id }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                Edit
                            </button>
                            <button type="button" class="apst-del-btn" onclick="mcOpenDeleteById('{{ $item->id }}')">
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

    @include('registrar.process.panels.medical-clearance-modals')
</div>

<script>
    // Unified Action Menu Logic for Registrar
    window.mcCloseAllMenus = function() {
        document.querySelectorAll('.apst-dropdown.open').forEach(menu => {
            menu.classList.remove('open');
            menu.style.display = 'none';
        });
    };

    window.mcPositionMenu = function(menu, trigger) {
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

    window.mcCloseModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('is-hidden');
        }
    };

    window.mcOpenModal = function(id) {
        window.mcCloseAllMenus();
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('is-hidden');
        }
    };

    window.mcUpdateFileLabel = function(inputId, labelId) {
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

    window.mcViewAttachment = function(id) {
        const row = document.querySelector(`.mc-row[data-id="${id}"]`);
        if (row) {
            const doc = row.getAttribute('data-doc');
            document.getElementById('mcViewModalTitle').textContent = `VIEW ATTACHMENT - ${doc}`;
            document.getElementById('mcPreviewFilename').textContent = `${doc.toLowerCase().replace(/ /g, '_')}_scanned.pdf`;
            window.mcOpenModal('mcViewModal');
        }
    };

    document.getElementById('mcAddBtn')?.addEventListener('click', () => {
        document.getElementById('mcAddDocName').value = '';
        document.getElementById('mcAddRemarks').value = '';
        document.getElementById('mcAddDate').value = ''; 
        document.getElementById('mcAddStatus').value = 'Missing';
        window.mcOpenModal('mcAddModal');
    });

    window.mcOpenEditById = function(id) {
        const row = document.querySelector(`.mc-row[data-id="${id}"]`);
        if (row) {
            document.getElementById('mcEditId').value = id;
            document.getElementById('mcEditDocName').value = row.dataset.doc || '';
            document.getElementById('mcEditRemarks').value = row.dataset.remarks || '';
            document.getElementById('mcEditDate').value = row.dataset.date || '';
            document.getElementById('mcEditStatus').value = row.dataset.status || 'Missing';
            window.mcOpenModal('mcEditModal');
        }
    };

    window.mcOpenDeleteById = function(id) {
        const row = document.querySelector(`.mc-row[data-id="${id}"]`);
        if (row) {
            window.mcDeleteId = id;
            document.getElementById('mcDeleteDocName').textContent = row.dataset.doc || '';
            window.mcOpenModal('mcDeleteModal');
        }
    };

    window.mcConfirmDelete = function() {
        if (window.mcDeleteId) {
            const row = document.querySelector(`.mc-row[data-id="${window.mcDeleteId}"]`);
            if (row) {
                row.remove();
                window.mcCloseModal('mcDeleteModal');
                if (document.querySelectorAll('#mcRegTableBody .mc-row').length === 0) {
                    document.getElementById('mcRegTableBody').innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color:#999;">No medical records found.</td></tr>';
                }
            }
        }
    };

    window.mcSaveNew = function() {
        const doc = document.getElementById('mcAddDocName').value;
        const remarks = document.getElementById('mcAddRemarks').value;
        const status = document.getElementById('mcAddStatus').value;
        const date = ''; 

        if (!doc) {
            const alertMsg = document.getElementById('mcAlertMessage');
            if (alertMsg) alertMsg.textContent = 'Please fill in the document name.';
            window.mcOpenModal('mcAlertModal');
            return;
        }

        const id = 'temp_' + Date.now();
        const emptyRow = document.querySelector('#mcRegTableBody tr td[colspan="6"]');
        if (emptyRow) emptyRow.closest('tr').remove();

        const newRow = document.createElement('tr');
        newRow.className = 'mc-row';
        newRow.setAttribute('data-id', id);
        newRow.setAttribute('data-doc', doc);
        newRow.setAttribute('data-remarks', remarks);
        newRow.setAttribute('data-status', status);
        newRow.setAttribute('data-date', date);
        newRow.setAttribute('data-file', '');

        let statusClass = 'status-missing';
        if (status === 'Approved') statusClass = 'status-approved';
        if (status === 'For Review') statusClass = 'status-review';
        if (status === 'Submitted') statusClass = 'status-submitted';

        newRow.innerHTML = `
            <td>${doc}</td>
            <td class="mc-remarks-cell">${remarks}</td>
            <td style="text-align: center;">
                <span class="mc-item-status ${statusClass}">${status}</span>
            </td>
            <td class="mc-date-cell" style="text-align: center;">--</td>
            <td style="text-align: center;" class="mc-file-cell"><span style="color: #999; font-size: 0.85rem;">--</span></td>
            <td style="text-align: center;">
                <div class="apst-action-btn" data-mc-menu-toggle="mcRegMenu_${id}" aria-label="Open row actions" title="Actions">
                    <span></span><span></span><span></span>
                </div>
                <div class="apst-dropdown" id="mcRegMenu_${id}">
                    <button type="button" onclick="mcOpenEditById('${id}')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                        Edit
                    </button>
                    <button type="button" class="apst-del-btn" onclick="mcOpenDeleteById('${id}')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        Delete
                    </button>
                </div>
            </td>
        `;

        document.getElementById('mcRegTableBody').appendChild(newRow);
        window.mcCloseModal('mcAddModal');
        document.getElementById('mcAddDocName').value = '';
        document.getElementById('mcAddRemarks').value = '';
        document.getElementById('mcAddDate').value = '';
        document.getElementById('mcAddStatus').value = 'Missing';
    };

    window.mcSaveEdit = function() {
        const id = document.getElementById('mcEditId').value;
        const doc = document.getElementById('mcEditDocName').value;
        const remarks = document.getElementById('mcEditRemarks').value;
        const status = document.getElementById('mcEditStatus').value;
        const date = document.getElementById('mcEditDate').value;

        if (!doc) {
            const alertMsg = document.getElementById('mcAlertMessage');
            if (alertMsg) alertMsg.textContent = 'Please fill in the document name.';
            window.mcOpenModal('mcAlertModal');
            return;
        }

        const row = document.querySelector(`.mc-row[data-id="${id}"]`);
        if (row) {
            row.setAttribute('data-doc', doc);
            row.setAttribute('data-remarks', remarks);
            row.setAttribute('data-status', status);
            row.children[0].textContent = doc;
            row.children[1].textContent = remarks;
            
            let statusClass = 'status-missing';
            if (status === 'Approved') statusClass = 'status-approved';
            if (status === 'For Review') statusClass = 'status-review';
            if (status === 'Submitted') statusClass = 'status-submitted';
            
            row.children[2].innerHTML = `<span class="mc-item-status ${statusClass}">${status}</span>`;
            window.mcCloseModal('mcEditModal');
        }
    };

    // Shared Click Listener for MC Registrar
    if (!window.mcListenerAdded) {
        document.addEventListener('click', function(event) {
            const toggle = event.target.closest('[data-mc-menu-toggle]');
            if (toggle) {
                event.preventDefault();
                event.stopPropagation();
                const menuId = toggle.getAttribute('data-mc-menu-toggle');
                const menu = document.getElementById(menuId);
                if (!menu) return;

                const isOpen = menu.classList.contains('open');
                window.mcCloseAllMenus();
                
                if (!isOpen) {
                    window.mcPositionMenu(menu, toggle);
                }
                return;
            }

            if (!event.target.closest('.apst-dropdown')) {
                window.mcCloseAllMenus();
            }
        });
        window.addEventListener('scroll', () => window.mcCloseAllMenus(), true);
        window.addEventListener('resize', () => window.mcCloseAllMenus());
        window.mcListenerAdded = true;
    }

    // Search and Filter Logic
    const mcSearchInput = document.getElementById('mcSearch');
    const mcStatusFilter = document.getElementById('mcStatusFilter');
    const mcRegTableBody = document.getElementById('mcRegTableBody');

    function mcApplyFilters() {
        const q = mcSearchInput.value.toLowerCase();
        const status = mcStatusFilter.value;
        const rows = mcRegTableBody.querySelectorAll('.mc-row');

        rows.forEach(function(tr) {
            const rowText = tr.textContent.toLowerCase();
            const rowStatus = tr.getAttribute('data-status');
            
            const matchesSearch = rowText.includes(q);
            const matchesStatus = status === "" || rowStatus === status;

            tr.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    if (mcSearchInput) mcSearchInput.addEventListener('input', mcApplyFilters);
    if (mcStatusFilter) mcStatusFilter.addEventListener('change', mcApplyFilters);
</script>
