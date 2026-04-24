@push('styles')
<link rel="stylesheet" href="{{ asset('css/medical-clearance.css') }}?v={{ time() }}">
@endpush

@php
// Load medical clearances on-demand when included as a panel (not via dedicated route)
if (!isset($medicalClearances)) {
    $medicalClearances = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('registrar_requirements')) {
        $medicalClearances = \App\RegistrarRequirement::query()
            ->where('requirement_type', 'Medical')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
@endphp

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

    <div id="mcToastReg" style="display:none; position:fixed; bottom:24px; right:24px; z-index:99999; background:#006837; color:#fff; padding:12px 20px; border-radius:8px; font-size:0.88rem; font-weight:600; box-shadow:0 4px 16px rgba(0,0,0,0.18);">
        <span id="mcToastRegMsg"></span>
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
                @forelse($medicalClearances as $item)
                @php
                    $meta = [];
                    $metaPath = storage_path('app/public/medical-clearance/meta/' . $item->id . '.json');
                    if (file_exists($metaPath)) {
                        $meta = json_decode(file_get_contents($metaPath), true) ?? [];
                    }
                    $status = $meta['status'] ?? 'Missing';
                    $remarks = $meta['remarks'] ?? '';
                    $dateSubmitted = $meta['date_submitted'] ?? null;
                    $hasFile = !empty($meta['file_path']);
                    $statusClass = 'status-missing';
                    if ($status === 'Approved') $statusClass = 'status-approved';
                    elseif ($status === 'For Review') $statusClass = 'status-review';
                    elseif ($status === 'Submitted') $statusClass = 'status-submitted';
                @endphp
                <tr class="mc-row"
                    data-id="{{ $item->id }}"
                    data-doc="{{ $item->requirement_name }}"
                    data-remarks="{{ $remarks }}"
                    data-status="{{ $status }}"
                    data-date="{{ $dateSubmitted ?? '' }}"
                    data-file="{{ $hasFile ? 'Attached' : '' }}">
                    <td>{{ $item->requirement_name }}</td>
                    <td class="mc-remarks-cell">{{ $remarks }}</td>
                    <td style="text-align: center;">
                        <span class="mc-item-status {{ $statusClass }}">{{ $status }}</span>
                    </td>
                    <td class="mc-date-cell" style="text-align: center;">{{ $dateSubmitted ? date('m/d/Y', strtotime($dateSubmitted)) : '--' }}</td>
                    <td style="text-align: center;" class="mc-file-cell">
                        @if($hasFile)
                            <button type="button" class="apst-view-link" onclick="mcViewAttachment('{{ $item->id }}')" style="background:none;border:none;padding:0;color:var(--plp-green);font-size:0.82rem;font-weight:800;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;margin:0 auto;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                Attached
                            </button>
                        @else
                            <span style="color:#999;font-size:0.85rem;">--</span>
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
                            <button type="button" onclick="mcOpenUploadById('{{ $item->id }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload File
                            </button>
                            @if($hasFile)
                            <button type="button" onclick="mcDeleteFile('{{ $item->id }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                Remove File
                            </button>
                            @endif
                            <button type="button" class="apst-del-btn" onclick="mcOpenDeleteById('{{ $item->id }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">No medical clearance requirements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('registrar.process.panels.medical-clearance-modals')
</div>

<script>
(function() {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ─── Toast ───────────────────────────────────────────────
    function showToast(msg, type) {
        const t = document.getElementById('mcToastReg');
        const m = document.getElementById('mcToastRegMsg');
        if (!t || !m) return;
        t.style.background = (type === 'error') ? '#c0392b' : '#006837';
        m.textContent = msg;
        t.style.display = 'block';
        setTimeout(() => { t.style.display = 'none'; }, 3500);
    }

    // ─── Modal helpers ────────────────────────────────────────
    window.mcCloseAllMenus = function() {
        document.querySelectorAll('.apst-dropdown.open').forEach(m => { m.classList.remove('open'); m.style.display = 'none'; });
    };
    window.mcPositionMenu = function(menu, trigger) {
        const rect = trigger.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.display  = 'block';
        menu.classList.add('open');
        let left = rect.right - 150, top = rect.bottom + 6;
        menu.style.left = left + 'px';
        menu.style.top  = top + 'px';
        menu.style.zIndex = '9999';
        const mr = menu.getBoundingClientRect();
        if (mr.right  > window.innerWidth  - 8) menu.style.left = (window.innerWidth  - mr.width  - 8) + 'px';
        if (mr.bottom > window.innerHeight - 8) menu.style.top  = (rect.top - mr.height - 6) + 'px';
    };
    window.mcCloseModal = function(id) {
        const m = document.getElementById(id);
        if (m) { m.style.display = 'none'; m.classList.add('is-hidden'); }
    };
    window.mcOpenModal = function(id) {
        window.mcCloseAllMenus();
        const m = document.getElementById(id);
        if (m) { m.style.display = 'flex'; m.classList.remove('is-hidden'); }
    };
    window.mcUpdateFileLabel = function(inputId, labelId) {
        const inp = document.getElementById(inputId), lbl = document.getElementById(labelId);
        if (inp && lbl) lbl.textContent = inp.files?.length ? `Selected: ${inp.files[0].name}` : 'Click to upload or drag file';
    };

    // ─── Add Modal ────────────────────────────────────────────
    document.getElementById('mcAddBtn')?.addEventListener('click', () => {
        document.getElementById('mcAddDocName').value = '';
        document.getElementById('mcAddRemarks').value = '';
        document.getElementById('mcAddStatus').value  = 'Missing';
        window.mcOpenModal('mcAddModal');
    });

    window.mcSaveNew = function() {
        const doc     = document.getElementById('mcAddDocName').value.trim();
        const remarks = document.getElementById('mcAddRemarks').value.trim();
        const status  = document.getElementById('mcAddStatus').value;
        if (!doc) { showAlert('Please fill in the document name.'); return; }

        const btn = document.querySelector('#mcAddModal .req-btn-save');
        if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

        fetch('{{ route("registrar.process.medical-clearance.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ document_name: doc, remarks, status })
        })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.textContent = 'Add Record'; }
            if (!data.ok) { showAlert(data.message || 'Error saving.'); return; }
            window.mcCloseModal('mcAddModal');
            appendRow(data.data);
            showToast('Medical clearance added.', 'success');
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.textContent = 'Add Record'; }
            showAlert('Network error. Please try again.');
        });
    };

    // ─── Edit Modal ───────────────────────────────────────────
    window.mcOpenEditById = function(id) {
        const row = document.querySelector(`.mc-row[data-id="${id}"]`);
        if (!row) return;
        document.getElementById('mcEditId').value         = id;
        document.getElementById('mcEditDocName').value    = row.dataset.doc    || '';
        document.getElementById('mcEditRemarks').value    = row.dataset.remarks|| '';
        document.getElementById('mcEditStatus').value     = row.dataset.status || 'Missing';
        document.getElementById('mcEditDate').value       = row.dataset.date   || '';
        window.mcOpenModal('mcEditModal');
    };

    window.mcSaveEdit = function() {
        const id      = document.getElementById('mcEditId').value;
        const doc     = document.getElementById('mcEditDocName').value.trim();
        const remarks = document.getElementById('mcEditRemarks').value.trim();
        const status  = document.getElementById('mcEditStatus').value;
        if (!doc) { showAlert('Please fill in the document name.'); return; }

        const btn = document.querySelector('#mcEditModal .req-btn-save');
        if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

        fetch(`/registrar/process/medical-clearance/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ document_name: doc, remarks, status })
        })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.textContent = 'Save Changes'; }
            if (!data.ok) { showAlert(data.message || 'Error saving.'); return; }
            const row = document.querySelector(`.mc-row[data-id="${id}"]`);
            if (row) {
                row.dataset.doc     = doc;
                row.dataset.remarks = remarks;
                row.dataset.status  = data.status || status;
                row.children[0].textContent = doc;
                row.children[1].textContent = remarks;
                const sc = statusClass(data.status || status);
                row.children[2].innerHTML = `<span class="mc-item-status ${sc}">${data.status || status}</span>`;
            }
            window.mcCloseModal('mcEditModal');
            showToast('Medical clearance updated.', 'success');
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.textContent = 'Save Changes'; }
            showAlert('Network error. Please try again.');
        });
    };

    // ─── Upload Modal ─────────────────────────────────────────
    window.mcOpenUploadById = function(id) {
        const row = document.querySelector(`.mc-row[data-id="${id}"]`);
        if (!row) return;
        document.getElementById('mcUploadId').value = id;
        document.getElementById('mcUploadDocName').textContent = row.dataset.doc || '';
        const lbl = document.getElementById('mcUploadFileLabel');
        if (lbl) lbl.textContent = 'Click to upload or drag file';
        const inp = document.getElementById('mcUploadFile');
        if (inp) inp.value = '';
        window.mcOpenModal('mcUploadModal');
    };

    window.mcProceedFileUpload = function() {
        const id  = document.getElementById('mcUploadId').value;
        const inp = document.getElementById('mcUploadFile');
        if (!inp || !inp.files || !inp.files[0]) { showAlert('Please select a file first.'); return; }

        const btn = document.querySelector('#mcUploadModal .req-btn-save');
        if (btn) { btn.disabled = true; btn.textContent = 'Uploading…'; }

        const fd = new FormData();
        fd.append('file', inp.files[0]);
        fd.append('_token', CSRF);

        fetch(`/registrar/process/medical-clearance/${id}/upload`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.textContent = 'Upload'; }
            if (!data.ok) { showAlert(data.message || 'Upload error.'); return; }
            const row = document.querySelector(`.mc-row[data-id="${id}"]`);
            if (row) {
                row.dataset.file   = 'Attached';
                row.dataset.status = data.status;
                row.dataset.date   = data.date_submitted;
                const sc = statusClass(data.status);
                row.children[2].innerHTML = `<span class="mc-item-status ${sc}">${data.status}</span>`;
                const d = new Date(data.date_submitted);
                row.children[3].textContent = (d.getMonth()+1).toString().padStart(2,'0') + '/' + d.getDate().toString().padStart(2,'0') + '/' + d.getFullYear();
                row.children[4].innerHTML = `<button type="button" class="apst-view-link" onclick="mcViewAttachment('${id}')" style="background:none;border:none;padding:0;color:var(--plp-green);font-size:0.82rem;font-weight:800;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;margin:0 auto;"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg> Attached</button>`;
            }
            window.mcCloseModal('mcUploadModal');
            showToast('File uploaded successfully.', 'success');
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.textContent = 'Upload'; }
            showAlert('Network error. Please try again.');
        });
    };

    // ─── View Attachment ──────────────────────────────────────
    window.mcViewAttachment = function(id) {
        window.open(`/registrar/process/medical-clearance/${id}/file`, '_blank');
    };

    // ─── Delete File ──────────────────────────────────────────
    window.mcDeleteFile = function(id) {
        if (!confirm('Remove the attached file for this requirement?')) return;
        fetch(`/registrar/process/medical-clearance/${id}/file`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) { showToast(data.message || 'Error.', 'error'); return; }
            const row = document.querySelector(`.mc-row[data-id="${id}"]`);
            if (row) {
                row.dataset.file   = '';
                row.dataset.status = 'Missing';
                row.children[2].innerHTML = `<span class="mc-item-status status-missing">Missing</span>`;
                row.children[3].textContent = '--';
                row.children[4].innerHTML = `<span style="color:#999;font-size:0.85rem;">--</span>`;
            }
            showToast('File removed.', 'success');
        });
    };

    // ─── Delete Row ───────────────────────────────────────────
    window.mcOpenDeleteById = function(id) {
        const row = document.querySelector(`.mc-row[data-id="${id}"]`);
        if (!row) return;
        window.mcDeleteId = id;
        document.getElementById('mcDeleteDocName').textContent = row.dataset.doc || '';
        window.mcOpenModal('mcDeleteModal');
    };

    window.mcConfirmDelete = function() {
        const id = window.mcDeleteId;
        if (!id) return;
        fetch(`/registrar/process/medical-clearance/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) { showToast(data.message || 'Error.', 'error'); return; }
            const row = document.querySelector(`.mc-row[data-id="${id}"]`);
            if (row) row.remove();
            window.mcCloseModal('mcDeleteModal');
            if (!document.querySelectorAll('#mcRegTableBody .mc-row').length) {
                document.getElementById('mcRegTableBody').innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:#999;">No medical clearance requirements yet.</td></tr>';
            }
            showToast('Record deleted.', 'success');
        });
    };

    // ─── Helpers ──────────────────────────────────────────────
    function statusClass(s) {
        if (s === 'Approved')   return 'status-approved';
        if (s === 'For Review') return 'status-review';
        if (s === 'Submitted')  return 'status-submitted';
        return 'status-missing';
    }

    function showAlert(msg) {
        const el = document.getElementById('mcAlertMessage');
        if (el) el.textContent = msg;
        window.mcOpenModal('mcAlertModal');
    }

    function appendRow(d) {
        const emptyRow = document.querySelector('#mcRegTableBody tr td[colspan="6"]');
        if (emptyRow) emptyRow.closest('tr').remove();
        const sc = statusClass(d.status);
        const tr = document.createElement('tr');
        tr.className = 'mc-row';
        tr.dataset.id      = d.id;
        tr.dataset.doc     = d.document_name;
        tr.dataset.remarks = d.remarks || '';
        tr.dataset.status  = d.status;
        tr.dataset.date    = d.date_submitted || '';
        tr.dataset.file    = d.has_file ? 'Attached' : '';
        tr.innerHTML = `
            <td>${d.document_name}</td>
            <td class="mc-remarks-cell">${d.remarks || ''}</td>
            <td style="text-align:center;"><span class="mc-item-status ${sc}">${d.status}</span></td>
            <td class="mc-date-cell" style="text-align:center;">--</td>
            <td style="text-align:center;" class="mc-file-cell"><span style="color:#999;font-size:0.85rem;">--</span></td>
            <td style="text-align:center;">
                <div class="apst-action-btn" data-mc-menu-toggle="mcRegMenu_${d.id}" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                <div class="apst-dropdown" id="mcRegMenu_${d.id}">
                    <button type="button" onclick="mcOpenEditById('${d.id}')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg> Edit</button>
                    <button type="button" onclick="mcOpenUploadById('${d.id}')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Upload File</button>
                    <button type="button" class="apst-del-btn" onclick="mcOpenDeleteById('${d.id}')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg> Delete</button>
                </div>
            </td>`;
        document.getElementById('mcRegTableBody').appendChild(tr);
    }

    // ─── Menu Toggle ──────────────────────────────────────────
    if (!window.mcRegListenerAdded) {
        document.addEventListener('click', function(e) {
            const toggle = e.target.closest('[data-mc-menu-toggle]');
            if (toggle) {
                e.preventDefault(); e.stopPropagation();
                const menuId = toggle.getAttribute('data-mc-menu-toggle');
                const menu   = document.getElementById(menuId);
                if (!menu) return;
                const isOpen = menu.classList.contains('open');
                window.mcCloseAllMenus();
                if (!isOpen) window.mcPositionMenu(menu, toggle);
                return;
            }
            if (!e.target.closest('.apst-dropdown')) window.mcCloseAllMenus();
        });
        window.addEventListener('scroll', () => window.mcCloseAllMenus(), true);
        window.addEventListener('resize', () => window.mcCloseAllMenus());
        window.mcRegListenerAdded = true;
    }

    // ─── Search & Filter ──────────────────────────────────────
    const mcSearch = document.getElementById('mcSearch');
    const mcFilter = document.getElementById('mcStatusFilter');
    function mcApplyFilters() {
        const q = (mcSearch?.value || '').toLowerCase();
        const s = mcFilter?.value || '';
        document.querySelectorAll('#mcRegTableBody .mc-row').forEach(tr => {
            const match = tr.textContent.toLowerCase().includes(q) && (!s || tr.dataset.status === s);
            tr.style.display = match ? '' : 'none';
        });
    }
    mcSearch?.addEventListener('input', mcApplyFilters);
    mcFilter?.addEventListener('change', mcApplyFilters);
})();
</script>
