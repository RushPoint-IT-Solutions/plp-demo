@extends('layouts.registrar')

@section('title', 'PLP - Announcement')
@section('page-title', 'ANNOUNCEMENTS')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
<div class="pf-page">
    <div class="sc-page">
        <div class="sc-toolbar">
            <div class="sc-search-wrap">
                <input id="anSearch" class="sc-search-input" type="text" placeholder="Search..." oninput="anRenderTable()">
                <span class="sc-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
            </div>
            <button type="button" class="pf-btn-new" onclick="anOpenAddModal()">Add Announcements</button>
        </div>

        <div class="app-table-wrap">
            <table id="anTable" class="app-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Title</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody id="anTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="anModal" style="display:none;" onclick="if(event.target===this) anCloseModal()">
    <div class="req-modal-box" style="max-width:860px;">
        <h3 class="req-modal-title" id="anModalTitle">ADD ANNOUNCEMENT</h3>
        <input type="hidden" id="anEditingIndex" value="">

        <div class="sc-modal-grid">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Title</label>
                <input id="anTitle" type="text" class="req-modal-input" placeholder="Enter title">
            </div>
        </div>

        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">From</label>
                <input id="anFrom" type="text" class="req-modal-input" placeholder="Select date" autocomplete="off">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">To</label>
                <input id="anTo" type="text" class="req-modal-input" placeholder="Select date" autocomplete="off">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Type</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'anType',
                    'name' => 'type',
                    'options' => [
                        ['value' => 'everyone', 'label' => 'Everyone'],
                        ['value' => 'students', 'label' => 'Students'],
                        ['value' => 'faculty', 'label' => 'Faculty'],
                        ['value' => 'staff', 'label' => 'Staff'],
                        ['value' => 'applicant', 'label' => 'Applicant'],
                    ],
                    'selected' => 'everyone',
                    'placeholder' => 'Select audience',
                ])
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Program</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'anProgram',
                    'name' => 'program',
                    'options' => [
                        ['value' => 'All Programs', 'label' => 'All Programs'],
                        ['value' => 'BSCS', 'label' => 'BSCS'],
                        ['value' => 'BSIT', 'label' => 'BSIT'],
                        ['value' => 'BSED', 'label' => 'BSED'],
                        ['value' => 'BSBA', 'label' => 'BSBA'],
                    ],
                    'selected' => 'All Programs',
                    'placeholder' => 'Select program',
                ])
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Content</label>
                <textarea id="anContent" class="req-modal-input sc-modal-textarea" placeholder="Type announcement content..."></textarea>
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="anCloseModal()">Cancel</button>
            <button type="button" class="req-btn-save" id="anSaveBtn" onclick="anSaveAnnouncement()">Update</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="anViewModal" style="display:none;" onclick="if(event.target===this) anCloseViewModal()">
    <div class="req-modal-box" style="max-width:760px;">
        <h3 class="req-modal-title">ANNOUNCEMENT SUMMARY</h3>

        <div class="sc-modal-grid">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Title</label>
                <input id="anViewTitle" type="text" class="req-modal-input" readonly>
            </div>
        </div>

        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">From</label>
                <input id="anViewFrom" type="text" class="req-modal-input" readonly>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">To</label>
                <input id="anViewTo" type="text" class="req-modal-input" readonly>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Audience</label>
                <input id="anViewType" type="text" class="req-modal-input" readonly>
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Program</label>
                <input id="anViewProgram" type="text" class="req-modal-input" readonly>
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Content</label>
                <textarea id="anViewContent" class="req-modal-input sc-modal-textarea" readonly></textarea>
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:14px; justify-content:center;">
            <button type="button" class="req-btn-save" onclick="anCloseViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="anDeleteModal" style="display:none;" onclick="if(event.target===this) anCloseDeleteModal()">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE ANNOUNCEMENT</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this announcement?</p>
        <input type="hidden" id="anDeleteIndex" value="">
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="anCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="anConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('js/registrar-announcement-datepicker.js') }}?v={{ file_exists(public_path('js/registrar-announcement-datepicker.js')) ? filemtime(public_path('js/registrar-announcement-datepicker.js')) : time() }}"></script>
<script>
    var anItems = @json($announcementRows ?? []);
    var anStoreUrl = '{{ route('registrar.admin-tools.system-config.announcement.store') }}';
    var anUpdateTemplate = '{{ route('registrar.admin-tools.system-config.announcement.update', ['systemAnnouncement' => '__ID__']) }}';
    var anDeleteTemplate = '{{ route('registrar.admin-tools.system-config.announcement.destroy', ['systemAnnouncement' => '__ID__']) }}';
    var anSaveInFlight = false;

    function anUpdateUrl(id) {
        return anUpdateTemplate.replace('__ID__', String(id));
    }

    function anDeleteUrl(id) {
        return anDeleteTemplate.replace('__ID__', String(id));
    }

    function anSetSelectValue(id, value) {
        var select = document.getElementById(id);
        if (!select) {
            return;
        }

        select.value = value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function anSetDateValue(id, value) {
        if (window.registrarAnnouncementDatePicker && typeof window.registrarAnnouncementDatePicker.setValue === 'function') {
            window.registrarAnnouncementDatePicker.setValue(id, value);
            return;
        }

        var input = document.getElementById(id);
        if (input) {
            input.value = value || '';
        }
    }

    function anClearDateValue(id) {
        if (window.registrarAnnouncementDatePicker && typeof window.registrarAnnouncementDatePicker.clearValue === 'function') {
            window.registrarAnnouncementDatePicker.clearValue(id);
            return;
        }

        var input = document.getElementById(id);
        if (input) {
            input.value = '';
        }
    }

    function anSaveButton() {
        return document.getElementById('anSaveBtn');
    }

    function anSetSaveButtonLoading(isLoading) {
        var saveBtn = anSaveButton();
        if (!saveBtn) {
            return;
        }

        if (!saveBtn.dataset.defaultText) {
            saveBtn.dataset.defaultText = saveBtn.textContent || 'Save';
        }

        if (isLoading) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            return;
        }

        saveBtn.disabled = false;
        saveBtn.textContent = saveBtn.dataset.defaultText || 'Save';
    }

    function anShowToast(message, type) {
        var normalizedType = (type === 'error' || type === 'warning') ? type : undefined;

        if (typeof window.showRegistrarToast === 'function') {
            window.showRegistrarToast(String(message || ''), normalizedType);
            return;
        }

        if (normalizedType === 'error') {
            console.error(String(message || ''));
            return;
        }

        console.info(String(message || ''));
    }

    function anSanitizeErrorMessage(message, fallback) {
        var text = String(message || '').trim();
        var safeFallback = String(fallback || 'Unable to process announcement. Please try again.');

        if (!text) {
            return safeFallback;
        }

        if (/SQLSTATE|Unknown column|Syntax error|Stack trace|QueryException|PDOException| in .+\.php/i.test(text)) {
            return safeFallback;
        }

        return text;
    }

    async function anApiRequest(url, method, payload) {
        var response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: payload ? JSON.stringify(payload) : null
        });

        var json = {};
        try {
            json = await response.json();
        } catch (e) {
            json = {};
        }

        if (!response.ok || json.ok === false) {
            var firstFieldError = '';
            if (json.errors && typeof json.errors === 'object') {
                var firstKey = Object.keys(json.errors)[0] || '';
                if (firstKey && Array.isArray(json.errors[firstKey]) && json.errors[firstKey][0]) {
                    firstFieldError = String(json.errors[firstKey][0]);
                }
            }

            var rawMessage = firstFieldError || json.message || 'Unable to process announcement.';
            throw new Error(
                anSanitizeErrorMessage(rawMessage, 'Unable to process announcement. Please try again.')
            );
        }

        return json;
    }

    function anEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function anFormatDate(dateString) {
        if (!dateString) {
            return '';
        }
        var d = new Date(dateString + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'numeric', day: 'numeric', year: 'numeric' });
    }

    function anBuildMenu(menuId, index) {
        return '' +
            '<div class="apst-action-btn" data-an-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="anOpenViewModal(' + index + ')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>View</button>' +
                '<button type="button" onclick="anOpenEditModal(' + index + ')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" onclick="anOpenDeleteModal(' + index + ')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>Delete</button>' +
            '</div>';
    }

    function anCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function anToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) {
            return;
        }
        var isOpen = menu.classList.contains('open');
        anCloseActionMenus();
        if (isOpen) {
            return;
        }

        var rect = trigger.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;

        menu.style.left = 'auto';
        menu.style.right = (window.innerWidth - rect.left + 4) + 'px';

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

    function anRenderTable() {
        var body = document.getElementById('anTableBody');
        var query = (document.getElementById('anSearch').value || '').toLowerCase();

        var rows = anItems.filter(function(item) {
            return (
                item.title.toLowerCase().indexOf(query) !== -1 ||
                item.content.toLowerCase().indexOf(query) !== -1
            );
        }).map(function(item) {
            var actualIndex = anItems.indexOf(item);
            return '' +
                '<tr>' +
                    '<td>' + anEscapeHtml(anFormatDate(item.from)) + '</td>' +
                    '<td>' + anEscapeHtml(anFormatDate(item.to)) + '</td>' +
                    '<td>' + anEscapeHtml(item.title) + '</td>' +
                    '<td style="text-align:center;">' +
                        anBuildMenu('anMenu' + actualIndex, actualIndex) +
                    '</td>' +
                '</tr>';
        }).join('');

        if (!rows) {
            rows = '<tr><td colspan="4" class="sc-empty-row">No announcements found.</td></tr>';
        }

        body.innerHTML = rows;
    }

    function anClearForm() {
        document.getElementById('anEditingIndex').value = '';
        document.getElementById('anTitle').value = '';
        anClearDateValue('anFrom');
        anClearDateValue('anTo');
        anSetSelectValue('anType', 'everyone');
        anSetSelectValue('anProgram', 'All Programs');
        document.getElementById('anContent').value = '';
    }

    function anOpenAddModal() {
        anClearForm();
        document.getElementById('anModalTitle').innerText = 'ADD ANNOUNCEMENT';
        var saveBtn = anSaveButton();
        if (saveBtn) {
            saveBtn.dataset.defaultText = 'Save';
            saveBtn.textContent = 'Save';
            saveBtn.disabled = false;
        }
        anCloseActionMenus();
        document.getElementById('anModal').style.display = 'flex';
    }

    function anAnnouncementAudienceLabel(item) {
        if (item && item.typeLabel) {
            return String(item.typeLabel);
        }

        var labels = {
            everyone: 'Everyone',
            students: 'Students',
            faculty: 'Faculty',
            staff: 'Staff',
            applicant: 'Applicant'
        };

        var code = item && item.type ? String(item.type).toLowerCase() : 'everyone';
        return labels[code] || labels.everyone;
    }

    function anOpenViewModal(index) {
        var item = anItems[index];
        if (!item) {
            return;
        }

        anCloseActionMenus();
        document.getElementById('anViewTitle').value = item.title || '';
        document.getElementById('anViewFrom').value = anFormatDate(item.from) || '';
        document.getElementById('anViewTo').value = anFormatDate(item.to) || '';
        document.getElementById('anViewType').value = anAnnouncementAudienceLabel(item);
        document.getElementById('anViewProgram').value = item.program || 'All Programs';
        document.getElementById('anViewContent').value = item.content || '';
        document.getElementById('anViewModal').style.display = 'flex';
    }

    function anOpenEditModal(index) {
        var item = anItems[index];
        if (!item) {
            return;
        }
        anCloseActionMenus();
        document.getElementById('anEditingIndex').value = String(index);
        document.getElementById('anTitle').value = item.title;
        anSetDateValue('anFrom', item.from);
        anSetDateValue('anTo', item.to);
        anSetSelectValue('anType', item.type);
        anSetSelectValue('anProgram', item.program);
        document.getElementById('anContent').value = item.content;
        document.getElementById('anModalTitle').innerText = 'EDIT ANNOUNCEMENT';
        var saveBtn = anSaveButton();
        if (saveBtn) {
            saveBtn.dataset.defaultText = 'Update';
            saveBtn.textContent = 'Update';
            saveBtn.disabled = false;
        }
        document.getElementById('anModal').style.display = 'flex';
    }

    function anCloseModal() {
        if (window.registrarAnnouncementDatePicker && typeof window.registrarAnnouncementDatePicker.closeAll === 'function') {
            window.registrarAnnouncementDatePicker.closeAll();
        }

        document.getElementById('anModal').style.display = 'none';
    }

    function anCloseViewModal() {
        document.getElementById('anViewModal').style.display = 'none';
    }

    async function anSaveAnnouncement() {
        if (anSaveInFlight) {
            return;
        }

        var payload = {
            title: document.getElementById('anTitle').value.trim(),
            from: document.getElementById('anFrom').value,
            to: document.getElementById('anTo').value,
            type: document.getElementById('anType').value,
            program: document.getElementById('anProgram').value,
            content: document.getElementById('anContent').value.trim()
        };

        if (!payload.title || !payload.from || !payload.to || !payload.content) {
            anShowToast('Please fill in Title, From, To, and Content.', 'warning');
            return;
        }

        var indexValue = document.getElementById('anEditingIndex').value;
        anSaveInFlight = true;
        anSetSaveButtonLoading(true);

        try {
            if (indexValue === '') {
                var createRes = await anApiRequest(anStoreUrl, 'POST', payload);
                var createdRow = createRes.row || payload;

                if (createRes.already_exists) {
                    var duplicateIndex = anItems.findIndex(function (row) {
                        return Number(row && row.id ? row.id : 0) === Number(createdRow && createdRow.id ? createdRow.id : 0);
                    });

                    if (duplicateIndex >= 0) {
                        anItems[duplicateIndex] = createdRow;
                    } else {
                        anItems.unshift(createdRow);
                    }

                    anShowToast('A matching announcement already exists. Showing the existing record.', 'warning');
                } else {
                    anItems.unshift(createdRow);
                    anShowToast('Announcement saved successfully.');
                }
            } else {
                var index = parseInt(indexValue, 10);
                var current = anItems[index];
                if (!current) {
                    throw new Error('Announcement record not found.');
                }

                if (current.id) {
                    var updateRes = await anApiRequest(anUpdateUrl(current.id), 'PUT', payload);
                    anItems[index] = updateRes.row || anItems[index];
                    anShowToast('Announcement updated successfully.');
                } else {
                    anItems[index] = payload;
                    anShowToast('Announcement updated successfully.');
                }
            }
        } catch (error) {
            anShowToast(anSanitizeErrorMessage(error.message, 'Unable to save announcement. Please try again.'), 'error');
            return;
        } finally {
            anSaveInFlight = false;
            anSetSaveButtonLoading(false);
        }

        anCloseModal();
        anRenderTable();
    }

    function anOpenDeleteModal(index) {
        anCloseActionMenus();
        document.getElementById('anDeleteIndex').value = String(index);
        document.getElementById('anDeleteModal').style.display = 'flex';
    }

    function anCloseDeleteModal() {
        document.getElementById('anDeleteModal').style.display = 'none';
    }

    async function anConfirmDelete() {
        var index = parseInt(document.getElementById('anDeleteIndex').value, 10);
        if (isNaN(index) || !anItems[index]) {
            anCloseDeleteModal();
            return;
        }

        try {
            if (anItems[index].id) {
                await anApiRequest(anDeleteUrl(anItems[index].id), 'DELETE');
            }
            anItems.splice(index, 1);
            anShowToast('Announcement deleted successfully.');
        } catch (error) {
            anShowToast(anSanitizeErrorMessage(error.message, 'Unable to delete announcement. Please try again.'), 'error');
            return;
        }

        anCloseDeleteModal();
        anRenderTable();
    }

    document.addEventListener('click', function(event) {
        var menuToggle = event.target.closest('[data-an-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            anToggleActionMenu(menuToggle.getAttribute('data-an-menu-toggle'), menuToggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            anCloseActionMenus();
        }
    });

    window.addEventListener('scroll', anCloseActionMenus, true);

    anRenderTable();
</script>
@endpush
