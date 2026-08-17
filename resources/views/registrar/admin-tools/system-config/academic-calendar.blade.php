@extends('layouts.registrar')

@section('title', 'PLP - Academic Calendar')
@section('page-title', 'ACADEMIC CALENDAR')

@section('content')
<div class="pf-page">
    <div class="sc-page">
        <div class="sc-toolbar">
            <div class="sc-search-wrap">
                <input id="acSearch" class="sc-search-input" type="text" placeholder="Search..." oninput="acRenderTable()">
                <span class="sc-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
            </div>
        </div>

        <div class="sc-table-head">
            <div class="pf-entries-control sc-show-entry">
                <label for="acShowEntries">Show</label>
                <select id="acShowEntries" class="pf-entries-select" onchange="acRenderTable()">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>Entries</span>
            </div>
            <button type="button" class="pf-btn-new" onclick="acOpenAddModal()">Add Event</button>
        </div>

        <div class="app-table-wrap">
            <table id="acTable" class="app-table" style="min-width: 1050px;">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Event</th>
                        <th>Venue</th>
                        <th>In Charge</th>
                        <th>Post Until</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody id="acTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="acEventModal" style="display:none;" onclick="if(event.target===this) acCloseEventModal()">
    <div class="req-modal-box" style="max-width:760px;">
        <h3 class="req-modal-title" id="acModalTitle">ADD NEW RECORD</h3>
        <input type="hidden" id="acEditingIndex" value="">

        <div class="sc-modal-grid-3">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Date / Post Until</label>
                <input id="acDatePostUntil" type="date" class="req-modal-input" onclick="if(this.showPicker){this.showPicker()}" onfocus="if(this.showPicker){this.showPicker()}">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Date From</label>
                <input id="acDateFrom" type="date" class="req-modal-input" onclick="if(this.showPicker){this.showPicker()}" onfocus="if(this.showPicker){this.showPicker()}">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Date To</label>
                <input id="acDateTo" type="date" class="req-modal-input" onclick="if(this.showPicker){this.showPicker()}" onfocus="if(this.showPicker){this.showPicker()}">
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group sc-modal-full">
                <label class="req-modal-label">Event</label>
                <input id="acEvent" type="text" class="req-modal-input" placeholder="Enter event title">
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Venue</label>
                <input id="acVenue" type="text" class="req-modal-input" placeholder="Venue...">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">In Charge</label>
                <input id="acInCharge" type="text" class="req-modal-input" placeholder="In-charge">
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="acCloseEventModal()">Cancel</button>
            <button type="button" class="req-btn-save" id="acSaveBtn" onclick="acSaveEvent()">Update</button>
        </div>
    </div>
</div>

@include('includes.registrar-delete-modal', [
    'id' => 'acDeleteModal',
    'title' => 'DELETE EVENT',
    'message' => 'Are you sure you want to delete this event from the academic calendar?',
    'confirmBtnText' => 'Delete',
    'cancelAction' => 'acCloseDeleteModal()',
    'confirmAction' => 'acConfirmDelete()',
    'detailId' => 'acDeleteDetail'
])
<input type="hidden" id="acDeleteIndex" value="">

<div class="req-modal-overlay ac-error-modal" id="acErrorModal" style="display:none;" aria-hidden="true" onclick="if(event.target===this) acCloseErrorModal()">
    <div class="req-modal-box ac-error-modal-box" role="dialog" aria-modal="true" aria-labelledby="acErrorModalTitle" aria-describedby="acErrorMessage">
        <div class="ac-error-badge" aria-hidden="true">!</div>
        <h3 class="ac-error-title" id="acErrorModalTitle">Event Validation Error</h3>
        <p class="ac-error-message" id="acErrorMessage">Please review the event details and try again.</p>
        <div class="ac-error-actions">
            <button type="button" class="ac-error-btn" onclick="acCloseErrorModal()">Got It</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var acEvents = @json($calendarRows ?? []);
    var acStoreUrl = '{{ route('registrar.admin-tools.system-config.academic-calendar.store') }}';
    var acUpdateTemplate = '{{ route('registrar.admin-tools.system-config.academic-calendar.update', ['academicCalendarEvent' => '__ID__']) }}';
    var acDeleteTemplate = '{{ route('registrar.admin-tools.system-config.academic-calendar.destroy', ['academicCalendarEvent' => '__ID__']) }}';

    if (!Array.isArray(acEvents)) {
        acEvents = [];
    }

    function acUpdateUrl(id) {
        return acUpdateTemplate.replace('__ID__', String(id));
    }

    function acDeleteUrl(id) {
        return acDeleteTemplate.replace('__ID__', String(id));
    }

    function acShowErrorModal(message) {
        var modal = document.getElementById('acErrorModal');
        var messageNode = document.getElementById('acErrorMessage');

        if (messageNode) {
            messageNode.textContent = message || 'Please review the event details and try again.';
        }

        if (modal) {
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        }
    }

    function acCloseErrorModal() {
        var modal = document.getElementById('acErrorModal');
        if (!modal) {
            return;
        }

        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    async function acApiRequest(url, method, payload) {
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
            throw new Error(
                (json.message) ||
                (json.errors && Object.values(json.errors)[0] && Object.values(json.errors)[0][0]) ||
                'Unable to process academic calendar event.'
            );
        }

        return json;
    }

    function acEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function acFormatDate(dateString) {
        if (!dateString) {
            return '';
        }
        var d = new Date(dateString + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'numeric', day: 'numeric', year: 'numeric' });
    }

    function acBuildMenu(menuId, index) {
        return '' +
            '<div class="apst-action-btn" data-ac-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="acOpenEditModal(' + index + ')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" onclick="acOpenDeleteModal(' + index + ')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>Delete</button>' +
            '</div>';
    }

    function acCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function acToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) {
            return;
        }
        var isOpen = menu.classList.contains('open');
        acCloseActionMenus();
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

    function acRenderTable() {
        var body = document.getElementById('acTableBody');
        var query = (document.getElementById('acSearch').value || '').toLowerCase();
        var limit = parseInt(document.getElementById('acShowEntries').value, 10) || 10;

        var filtered = acEvents.filter(function(item) {
            return (
                item.event.toLowerCase().indexOf(query) !== -1 ||
                item.venue.toLowerCase().indexOf(query) !== -1 ||
                item.inCharge.toLowerCase().indexOf(query) !== -1
            );
        });

        var rows = filtered.slice(0, limit).map(function(item, index) {
            var actualIndex = acEvents.indexOf(item);
            var fromDisplay = acFormatDate(item.dateFrom || item.date);
            var toDisplay = acFormatDate(item.dateTo || item.date);
            return '' +
                '<tr>' +
                    '<td>' + acEscapeHtml(fromDisplay) + '</td>' +
                    '<td>' + acEscapeHtml(toDisplay) + '</td>' +
                    '<td>' + acEscapeHtml(item.event) + '</td>' +
                    '<td>' + acEscapeHtml(item.venue) + '</td>' +
                    '<td>' + acEscapeHtml(item.inCharge) + '</td>' +
                    '<td>' + acEscapeHtml(acFormatDate(item.postUntil)) + '</td>' +
                    '<td style="text-align:center;">' +
                        acBuildMenu('acMenu' + actualIndex, actualIndex) +
                    '</td>' +
                '</tr>';
        }).join('');

        if (!rows) {
            rows = '<tr><td colspan="7" class="sc-empty-row">No events found.</td></tr>';
        }

        body.innerHTML = rows;
    }

    function acClearEventForm() {
        document.getElementById('acEditingIndex').value = '';
        document.getElementById('acDatePostUntil').value = '';
        document.getElementById('acDateFrom').value = '';
        document.getElementById('acDateTo').value = '';
        document.getElementById('acEvent').value = '';
        document.getElementById('acVenue').value = '';
        document.getElementById('acInCharge').value = '';
    }

    function acOpenAddModal() {
        acClearEventForm();
        acCloseErrorModal();
        document.getElementById('acModalTitle').innerText = 'ADD NEW RECORD';
        document.getElementById('acSaveBtn').innerText = 'Save';
        document.getElementById('acEventModal').style.display = 'flex';
    }

    function acOpenEditModal(index) {
        var item = acEvents[index];
        if (!item) {
            return;
        }
        document.getElementById('acEditingIndex').value = String(index);
        document.getElementById('acDatePostUntil').value = item.date || item.postUntil || '';
        document.getElementById('acDateFrom').value = item.dateFrom || item.date || '';
        document.getElementById('acDateTo').value = item.dateTo || item.date || '';
        document.getElementById('acEvent').value = item.event;
        document.getElementById('acVenue').value = item.venue;
        document.getElementById('acInCharge').value = item.inCharge;
        acCloseErrorModal();
        document.getElementById('acModalTitle').innerText = 'EDIT RECORD';
        document.getElementById('acSaveBtn').innerText = 'Update';
        document.getElementById('acEventModal').style.display = 'flex';
    }

    function acCloseEventModal() {
        document.getElementById('acEventModal').style.display = 'none';
    }

    async function acSaveEvent() {
        var datePostUntil = document.getElementById('acDatePostUntil').value;
        var payload = {
            date: datePostUntil,
            dateFrom: document.getElementById('acDateFrom').value,
            dateTo: document.getElementById('acDateTo').value,
            event: document.getElementById('acEvent').value.trim(),
            venue: document.getElementById('acVenue').value.trim(),
            inCharge: document.getElementById('acInCharge').value.trim(),
            postUntil: datePostUntil
        };

        if (!payload.date || !payload.event) {
            acShowErrorModal('Please fill in Date / Post Until and Event before saving.');
            return;
        }

        if (!payload.dateFrom || !payload.dateTo) {
            acShowErrorModal('Please add both Date From and Date To for this event.');
            return;
        }

        if (payload.dateTo < payload.dateFrom) {
            acShowErrorModal('Date To cannot be earlier than Date From.');
            return;
        }

        var indexValue = document.getElementById('acEditingIndex').value;
        try {
            if (indexValue === '') {
                var createRes = await acApiRequest(acStoreUrl, 'POST', payload);
                acEvents.unshift(createRes.row || payload);
            } else {
                var index = parseInt(indexValue, 10);
                var current = acEvents[index];
                if (!current) {
                    throw new Error('Event record not found.');
                }

                if (current.id) {
                    var updateRes = await acApiRequest(acUpdateUrl(current.id), 'PUT', payload);
                    acEvents[index] = updateRes.row || acEvents[index];
                } else {
                    acEvents[index] = payload;
                }
            }
        } catch (error) {
            acShowErrorModal(error.message || 'Unable to save event.');
            return;
        }

        acCloseEventModal();
        acRenderTable();
    }

    function acOpenDeleteModal(index) {
        acCloseActionMenus();
        document.getElementById('acDeleteIndex').value = String(index);
        document.getElementById('acDeleteModal').style.display = 'flex';
    }

    function acCloseDeleteModal() {
        document.getElementById('acDeleteModal').style.display = 'none';
    }

    async function acConfirmDelete() {
        var index = parseInt(document.getElementById('acDeleteIndex').value, 10);
        if (isNaN(index) || !acEvents[index]) {
            acCloseDeleteModal();
            return;
        }

        try {
            if (acEvents[index].id) {
                await acApiRequest(acDeleteUrl(acEvents[index].id), 'DELETE');
            }
            acEvents.splice(index, 1);
        } catch (error) {
            acShowErrorModal(error.message || 'Unable to delete event.');
            return;
        }

        acCloseDeleteModal();
        acRenderTable();
    }

    document.addEventListener('click', function(event) {
        var menuToggle = event.target.closest('[data-ac-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            acToggleActionMenu(menuToggle.getAttribute('data-ac-menu-toggle'), menuToggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            acCloseActionMenus();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            acCloseErrorModal();
        }
    });

    window.addEventListener('scroll', acCloseActionMenus, true);

    acRenderTable();
</script>
@endpush
