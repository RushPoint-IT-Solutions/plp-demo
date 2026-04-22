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

    <div class="student-table-wrapper applicant-content-shell">
        <div class="applicant-portal-content-header mb-3" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="m-0" style="color: #2d3436; font-weight: 700; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px;">Medical Records</h3>
            <button type="button" class="apst-new-btn" id="mcAddBtn" style="border-radius: 4px;">+ Add New</button>
        </div>

        <div class="app-table-wrap table-responsive">
            <table class="app-table" id="mcTable">
                <thead>
                    <tr>
                        <th style="width: 30%;">Documents</th>
                        <th style="width: 30%;">Remarks</th>
                        <th style="width: 15%; text-align: center;">Date Submitted</th>
                        <th style="width: 15%; text-align: center;">Attachment</th>
                        <th style="width: 60px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="mcTableBody">
                    @php
                        // Sample data for demonstration, replace with real data when backend is ready
                        $mcApplicantSampleData = [
                            (object)['id' => 1, 'document_name' => 'Chest X-ray', 'remarks' => 'Normal / Cleared', 'date_submitted' => '2026-04-15', 'file' => 'chest_xray.pdf'],
                            (object)['id' => 2, 'document_name' => 'Dental Certificate', 'remarks' => 'Needs cleaning', 'date_submitted' => '2026-04-18', 'file' => null],
                        ];
                        $mcApplicantDisplayData = (!empty($medicalClearances) && count($medicalClearances) > 0) ? $medicalClearances : $mcApplicantSampleData;
                    @endphp
                    @foreach($mcApplicantDisplayData as $index => $item)
                    @php
                        $hasFile = isset($item->file) && $item->file;
                    @endphp
                    <tr class="mc-row" data-id="{{ $item->id }}" data-doc="{{ $item->document_name }}" data-remarks="{{ $item->remarks }}" data-date="{{ $item->date_submitted }}" data-file="{{ $hasFile ? 'Attached' : '' }}">
                        <td>{{ $item->document_name }}</td>
                        <td class="mc-remarks-cell">{{ $item->remarks }}</td>
                        <td class="mc-date-cell" style="text-align: center;">{{ date('m/d/Y', strtotime($item->date_submitted)) }}</td>
                        <td style="text-align: center;" class="mc-file-cell">
                            @if($hasFile)
                                <button type="button" class="apst-view-link" onclick="window.mcViewAttachment('{{ $item->id }}')" style="background: none; border: none; padding: 0; color: #006837; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; margin: 0 auto;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                    Attached
                                </button>
                            @else
                                <span style="color: #999; font-size: 0.85rem;">--</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="apst-action-btn" data-mc-menu-toggle="mcMenu{{ $index }}" aria-label="Open row actions" title="Actions">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="apst-dropdown" id="mcMenu{{ $index }}">
                                <button type="button" onclick="mcOpenEdit({{ $index }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="mcOpenDelete({{ $index }})">
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
</div>

{{-- Standard Modals --}}
@include('registrar.process.panels.medical-clearance-modals')

@endsection

@push('scripts')
<script>
    // Action Menu Positioning and Toggling
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

    // Shared Click Listener for MC Menus
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

    // Modal Helpers
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
        document.getElementById('mcAddDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('mcAddFile').value = '';
        document.getElementById('mcAddFileLabel').textContent = 'Click to upload or drag file';
        window.mcOpenModal('mcAddModal');
    });

    // ID-based lookup for reliability
    window.mcOpenEditById = function(id) {
        const row = document.querySelector(`.mc-row[data-id="${id}"]`);
        if (row) {
            document.getElementById('mcEditId').value = id;
            document.getElementById('mcEditDocName').value = row.dataset.doc || '';
            document.getElementById('mcEditRemarks').value = row.dataset.remarks || '';
            document.getElementById('mcEditDate').value = row.dataset.date || '';
            document.getElementById('mcEditFile').value = '';
            
            const fileStatus = row.getAttribute('data-file');
            const label = document.getElementById('mcEditFileLabel');
            if (fileStatus === 'Attached') {
                label.textContent = 'Change attachment (existing file found)';
            } else {
                label.textContent = 'Click to upload or drag file';
            }
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

    // Index-based calls from Blade
    window.mcOpenEdit = function(index) {
        const rows = document.querySelectorAll('#mcTableBody .mc-row');
        if (rows[index]) window.mcOpenEditById(rows[index].dataset.id);
    };

    window.mcOpenDelete = function(index) {
        const rows = document.querySelectorAll('#mcTableBody .mc-row');
        if (rows[index]) window.mcOpenDeleteById(rows[index].dataset.id);
    };

    window.mcConfirmDelete = function() {
        if (window.mcDeleteId) {
            const row = document.querySelector(`.mc-row[data-id="${window.mcDeleteId}"]`);
            if (row) {
                row.remove();
                window.mcCloseModal('mcDeleteModal');
                if (document.querySelectorAll('#mcTableBody .mc-row').length === 0) {
                    document.getElementById('mcTableBody').innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px; color:#999;">No medical records found.</td></tr>';
                }
            }
        }
    };

    window.mcSaveNew = function() {
        const doc = document.getElementById('mcAddDocName').value;
        const remarks = document.getElementById('mcAddRemarks').value;
        const date = document.getElementById('mcAddDate').value;
        const fileInput = document.getElementById('mcAddFile');

        if (!doc || !date) {
            const alertMsg = document.getElementById('mcAlertMessage');
            if (alertMsg) alertMsg.textContent = 'Please fill in the document name and date.';
            window.mcOpenModal('mcAlertModal');
            return;
        }

        const id = 'temp_' + Date.now();
        const emptyRow = document.querySelector('#mcTableBody tr td[colspan="5"]');
        if (emptyRow) emptyRow.closest('tr').remove();

        const newRow = document.createElement('tr');
        newRow.className = 'mc-row';
        newRow.setAttribute('data-id', id);
        newRow.setAttribute('data-doc', doc);
        newRow.setAttribute('data-remarks', remarks);
        newRow.setAttribute('data-date', date);

        const formattedDate = new Date(date).toLocaleDateString('en-US');
        
        let fileStatusHtml = '<span style="color: #999; font-size: 0.85rem;">--</span>';
        let fileAttr = '';
        if (fileInput.files && fileInput.files.length > 0) {
            fileStatusHtml = `
                <button type="button" class="apst-view-link" onclick="window.mcViewAttachment('${id}')" style="background: none; border: none; padding: 0; color: #006837; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; margin: 0 auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                    Attached
                </button>`;
            fileAttr = 'Attached';
        }
        newRow.setAttribute('data-file', fileAttr);

        newRow.innerHTML = `
            <td>${doc}</td>
            <td class="mc-remarks-cell">${remarks}</td>
            <td class="mc-date-cell" style="text-align: center;">${formattedDate}</td>
            <td style="text-align: center;" class="mc-file-cell">${fileStatusHtml}</td>
            <td style="text-align: center;">
                <div class="apst-action-btn" data-mc-menu-toggle="mcMenu_${id}" aria-label="Open row actions" title="Actions">
                    <span></span><span></span><span></span>
                </div>
                <div class="apst-dropdown" id="mcMenu_${id}">
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

        document.getElementById('mcTableBody').appendChild(newRow);
        window.mcCloseModal('mcAddModal');
        document.getElementById('mcAddDocName').value = '';
        document.getElementById('mcAddRemarks').value = '';
        document.getElementById('mcAddDate').value = '';
    };

    window.mcSaveEdit = function() {
        const id = document.getElementById('mcEditId').value;
        const doc = document.getElementById('mcEditDocName').value;
        const remarks = document.getElementById('mcEditRemarks').value;
        const date = document.getElementById('mcEditDate').value;
        const fileInput = document.getElementById('mcEditFile');

        if (!doc || !date) {
            const alertMsg = document.getElementById('mcAlertMessage');
            if (alertMsg) alertMsg.textContent = 'Please fill in the document name and date.';
            window.mcOpenModal('mcAlertModal');
            return;
        }

        const row = document.querySelector(`.mc-row[data-id="${id}"]`);
        if (row) {
            row.setAttribute('data-doc', doc);
            row.setAttribute('data-remarks', remarks);
            row.setAttribute('data-date', date);
            row.children[0].textContent = doc;
            row.children[1].textContent = remarks;
            row.children[2].textContent = new Date(date).toLocaleDateString('en-US');
            
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                row.setAttribute('data-file', 'Attached');
                const fileCell = row.querySelector('.mc-file-cell');
                if (fileCell) {
                    fileCell.innerHTML = `
                        <button type="button" class="apst-view-link" onclick="window.mcViewAttachment('${id}')" style="background: none; border: none; padding: 0; color: #006837; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; margin: 0 auto;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                            Attached
                        </button>`;
                }
            }
            window.mcCloseModal('mcEditModal');
        }
    };
</script>
@endpush
