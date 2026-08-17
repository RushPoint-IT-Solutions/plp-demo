@extends('layouts.registrar')

@section('title', 'PLP - Room File')
@section('page-title', 'ROOM FILE')

@push('styles')
<style>
.pf-btn-outline {
    background: #fff;
    color: #006837;
    border: 1px solid #006837;
}
.pf-btn-outline:hover {
    background: #eef6f1;
}
.rf-import-note {
    display: block;
    margin: 6px 0 0;
    color: #66756b;
    font-size: .78rem;
    line-height: 1.45;
}
.rf-import-link {
    color: #006837;
    font-weight: 700;
    text-decoration: underline;
}
.rf-import-summary {
    margin-top: 12px;
    font-size: .85rem;
    color: #143521;
}
.rf-import-errors {
    max-height: 160px;
    overflow-y: auto;
    margin-top: 8px;
    padding: 8px 10px;
    background: #fdecec;
    border: 1px solid #efb8b8;
    border-radius: 6px;
    font-size: .78rem;
    color: #9f1d1d;
}
.rf-import-errors div {
    padding: 2px 0;
}
</style>
@endpush

@section('content')
<div
    class="pf-page"
    id="roomFilePage"
    data-fetch-url="{{ route('registrar.registrar-menu.scheduling.room-file.data') }}"
    data-store-url="{{ route('registrar.registrar-menu.scheduling.room-file.store') }}"
    data-store-building-url="{{ route('registrar.registrar-menu.scheduling.room-file.building.store') }}"
    data-store-hallway-url="{{ route('registrar.registrar-menu.scheduling.room-file.hallway.store') }}"
    data-program-file-url="{{ route('registrar.registrar-menu.academic-master.subject-file') }}"
    data-update-url-template="{{ route('registrar.registrar-menu.scheduling.room-file.update', ['room' => '__ROOM_ID__']) }}"
    data-delete-url-template="{{ route('registrar.registrar-menu.scheduling.room-file.delete', ['room' => '__ROOM_ID__']) }}"
    data-import-url="{{ route('registrar.registrar-menu.scheduling.room-file.import') }}"
    data-import-template-url="{{ route('registrar.registrar-menu.scheduling.room-file.import.template') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-default-sort-by="floor_number"
    data-default-sort-dir="asc"
>

    {{-- Toolbar --}}
    <div class="pf-toolbar">
        <div class="pf-search-wrap">
            <svg class="pf-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="pf-search-input" placeholder="Search Room No" id="rfSearch">
        </div>
        <button type="button" class="pf-btn-new pf-btn-outline" onclick="openImportRoomModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Import
        </button>
        <button type="button" class="pf-btn-new" onclick="openNewRoomModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Room
        </button>
    </div>

    {{-- Table --}}
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" id="rfTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th aria-sort="none">
                        <button type="button" class="rf-sort-btn" data-sort="room_number">
                            <span class="rf-sort-label">Room #</span>
                            <span class="rf-sort-indicator">Sort</span>
                        </button>
                    </th>
                    <th aria-sort="none">
                        <button type="button" class="rf-sort-btn" data-sort="floor_number">
                            <span class="rf-sort-label">Floor</span>
                            <span class="rf-sort-indicator">Sort</span>
                        </button>
                    </th>
                    <th aria-sort="none">
                        <button type="button" class="rf-sort-btn" data-sort="building">
                            <span class="rf-sort-label">Building & Hallway</span>
                            <span class="rf-sort-indicator">Sort</span>
                        </button>
                    </th>
                    <th aria-sort="none">
                        <button type="button" class="rf-sort-btn" data-sort="capacity">
                            <span class="rf-sort-label">Students</span>
                            <span class="rf-sort-indicator">Sort</span>
                        </button>
                    </th>
                    <th aria-sort="none">
                        <button type="button" class="rf-sort-btn" data-sort="program">
                            <span class="rf-sort-label">Allowed Subjects</span>
                            <span class="rf-sort-indicator">Sort</span>
                        </button>
                    </th>
                    <th aria-sort="none">
                        <button type="button" class="rf-sort-btn" data-sort="updated_by">
                            <span class="rf-sort-label">Update by</span>
                            <span class="rf-sort-indicator">Sort</span>
                        </button>
                    </th>
                    <th style="text-align:center; width:70px;">Action</th>
                </tr>
            </thead>
            <tbody id="rfBody">
                {{-- JS-rendered rows --}}
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="sf-pagination-bar sf-pagination-compact" id="rfPaginationBar">
        <div class="rtp-pagination">
            <nav class="rtp-nav" aria-label="Room File pagination">
                <div class="rtp-list" role="group" aria-label="Page controls">
                    <button type="button" class="rtp-page-btn" id="rfPrevBtn" aria-label="Previous page" disabled>&lt;</button>
                    <div class="rtp-pages" id="rfPageNumbers">
                        <button type="button" class="rtp-page-num active" aria-current="page" disabled>1</button>
                    </div>
                    <button type="button" class="rtp-page-btn" id="rfNextBtn" aria-label="Next page" disabled>&gt;</button>
                </div>
            </nav>
        </div>
    </div>
</div>

{{-- ══════ IMPORT ROOMS MODAL ══════ --}}
<div class="pf-modal-overlay" id="importRoomModal" style="display:none;">
    <div class="pf-modal-box" style="max-width:520px;">
        <div class="pf-modal-title">Import Rooms</div>
        <form id="importRoomForm" onsubmit="return handleImportRoomSubmit(event)">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label">CSV File</label>
                    <input type="file" class="pf-modal-input" id="importRoomFile" accept=".csv,text/csv" required>
                    <span class="rf-import-note">
                        Columns: Building, Hallway, Room Number, Floor, Capacity.
                        <a href="{{ route('registrar.registrar-menu.scheduling.room-file.import.template') }}" class="rf-import-link">Download template</a>.
                        Buildings and hallways are created automatically if they don't already exist.
                        Imported rooms have no allowed subjects yet — assign those afterward by editing each room.
                    </span>
                </div>
                <div id="importRoomSummary" class="rf-import-summary" style="display:none;"></div>
                <div id="importRoomErrors" class="rf-import-errors" style="display:none;"></div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeImportRoomModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save" id="importRoomSubmitBtn">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════ NEW ROOM MODAL ══════ --}}
<div class="pf-modal-overlay" id="roomScheduleModal" style="display:none;">
    <div class="pf-modal-box" style="max-width:920px;">
        <div class="pf-modal-title" id="roomScheduleTitle">Room Schedule</div>
        <div id="roomScheduleSummary" style="display:grid; grid-template-columns:repeat(4, minmax(120px, 1fr)); gap:10px; margin-bottom:14px; font-size:.86rem;"></div>
        <div style="overflow:auto; border:1px solid #e5ece7; border-radius:8px;">
            <table class="student-table registrar-table" style="min-width:820px; margin:0;">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Program / Section</th>
                        <th>Schedule</th>
                        <th>Teacher</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="roomScheduleRows">
                    <tr><td colspan="5">No current schedule found.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="pf-modal-actions" style="justify-content:flex-end; margin-top:18px;">
            <button type="button" class="pf-modal-btn-cancel" onclick="closeRoomScheduleModal()">Close</button>
        </div>
    </div>
</div>

<div class="pf-modal-overlay" id="newRoomModal" style="display:none;">
    <div class="pf-modal-box">
        <div class="pf-modal-title">New Room</div>
        <form id="newRoomForm" onsubmit="return handleNewRoomSave(event)">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Room #</label>
                    <input type="number" class="pf-modal-input" id="newRoomNumber" placeholder="e.g. 1" required min="1">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Floor</label>
                    <input type="number" class="pf-modal-input" id="newRoomFloor" placeholder="e.g. 2" required min="1">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Building</label>
                    <div class="rf-building-inline">
                        <select class="pf-modal-select" id="newRoomBuilding" required>
                            <option value="">- Select Building -</option>
                        </select>
                        <button
                            type="button"
                            class="rf-setup-add-btn"
                            id="newAddBuildingBtn"
                            title="Add Building"
                            aria-label="Add building"
                        >+</button>
                    </div>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Hallway</label>
                    <div class="rf-hallway-inline">
                        <select class="pf-modal-select" id="newRoomHallway" required>
                            <option value="">- Select Hallway -</option>
                        </select>
                        <button
                            type="button"
                            class="rf-hallway-add-btn"
                            id="newAddHallwayBtn"
                            title="Add Hallway"
                            aria-label="Add hallway"
                        >+</button>
                    </div>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Students (Capacity)</label>
                    <input type="number" class="pf-modal-input" id="newRoomStudents" placeholder="e.g. 50" required min="1">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Allowed Subjects</label>
                    <div class="rf-program-inline">
                        <select class="pf-modal-select" id="newRoomProgram" required multiple size="6">
                            <option value="">- Select Program -</option>
                        </select>
                        <button
                            type="button"
                            class="rf-setup-add-btn"
                            id="newGoProgramSetupBtn"
                            title="Open Subject File"
                            aria-label="Open subject setup"
                        >+</button>
                    </div>
                    <small style="display:block; margin-top:6px; color:#66756b;">Hold Ctrl to select multiple subjects allowed in this room.</small>
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeNewRoomModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save">Add Room</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════ EDIT ROOM MODAL ══════ --}}
<div class="pf-modal-overlay" id="editRoomModal" style="display:none;">
    <div class="pf-modal-box">
        <div class="pf-modal-title">Edit Room</div>
        <form id="editRoomForm" onsubmit="return handleEditRoomSave(event)">
            <input type="hidden" id="editRoomId">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Room #</label>
                    <input type="number" class="pf-modal-input" id="editRoomNumber" required min="1">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Floor</label>
                    <input type="number" class="pf-modal-input" id="editRoomFloor" required min="1">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Building</label>
                    <div class="rf-building-inline">
                        <select class="pf-modal-select" id="editRoomBuilding" required>
                            <option value="">- Select Building -</option>
                        </select>
                        <button
                            type="button"
                            class="rf-setup-add-btn"
                            id="editAddBuildingBtn"
                            title="Add Building"
                            aria-label="Add building"
                        >+</button>
                    </div>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Hallway</label>
                    <div class="rf-hallway-inline">
                        <select class="pf-modal-select" id="editRoomHallway" required>
                            <option value="">- Select Hallway -</option>
                        </select>
                        <button
                            type="button"
                            class="rf-hallway-add-btn"
                            id="editAddHallwayBtn"
                            title="Add Hallway"
                            aria-label="Add hallway"
                        >+</button>
                    </div>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Students (Capacity)</label>
                    <input type="number" class="pf-modal-input" id="editRoomStudents" required min="1">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Allowed Subjects</label>
                    <div class="rf-program-inline">
                        <select class="pf-modal-select" id="editRoomProgram" required multiple size="6">
                            <option value="">- Select Program -</option>
                        </select>
                        <button
                            type="button"
                            class="rf-setup-add-btn"
                            id="editGoProgramSetupBtn"
                            title="Open Subject File"
                            aria-label="Open subject setup"
                        >+</button>
                    </div>
                    <small style="display:block; margin-top:6px; color:#66756b;">Only selected subjects can use this room during room generation and assignment.</small>
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeEditRoomModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════ ADD HALLWAY MODAL ══════ --}}
<div class="pf-modal-overlay" id="addHallwayModal" style="display:none;">
    <div class="pf-modal-box rf-hallway-modal-box">
        <div class="pf-modal-title">Add Hallway</div>
        <form id="addHallwayForm">
            <input type="hidden" id="addHallwayPrefix">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addHallwayBuilding">Building</label>
                    <input type="text" class="pf-modal-input" id="addHallwayBuilding" readonly>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addHallwayName">Hallway Name</label>
                    <input type="text" class="pf-modal-input" id="addHallwayName" placeholder="e.g. West Wing" maxlength="120" required>
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" id="addHallwayCancelBtn">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save" id="addHallwaySaveBtn">Add Hallway</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════ ADD BUILDING MODAL ══════ --}}
<div class="pf-modal-overlay" id="addBuildingModal" style="display:none;">
    <div class="pf-modal-box rf-building-modal-box">
        <div class="pf-modal-title">Add Building</div>
        <form id="addBuildingForm">
            <input type="hidden" id="addBuildingPrefix">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="addBuildingName">Building Name</label>
                    <input type="text" class="pf-modal-input" id="addBuildingName" placeholder="e.g. North Annex" maxlength="120" required>
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" id="addBuildingCancelBtn">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save" id="addBuildingSaveBtn">Add Building</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════ DELETE ROOM MODAL ══════ --}}
<div class="pf-modal-overlay" id="deleteRoomModal" style="display:none;">
    <div class="pf-modal-box" style="text-align:center; max-width:420px;">
        <div style="margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#c62828" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="pf-modal-title" style="color:#c62828;">Delete Room</div>
        <p style="font-size:0.9rem; color:#444; margin-bottom:6px;">Are you sure you want to delete</p>
        <p style="font-size:0.95rem; font-weight:700; color:#1a1a2e; margin-bottom:20px;" id="deleteRoomName"></p>
        <p style="font-size:0.78rem; color:#999; margin-bottom:22px;">This action cannot be undone.</p>
        <input type="hidden" id="deleteRoomId">
        <div class="pf-modal-actions" style="justify-content:center;">
            <button type="button" class="pf-modal-btn-cancel" onclick="closeDeleteRoomModal()">Cancel</button>
            <button type="button" class="pf-modal-btn-save" style="background:#c62828;" onmouseover="this.style.background='#a31f1f'" onmouseout="this.style.background='#c62828'" onclick="handleDeleteRoomConfirm()">Delete</button>
        </div>
    </div>
</div>

{{-- ══════ SUCCESS MODAL ══════ --}}
<div class="pf-modal-overlay" id="roomSuccessModal" style="display:none;">
    <div class="pf-modal-box" style="text-align:center; max-width:420px;">
        <div style="margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>
            </svg>
        </div>
        <div class="pf-modal-title" style="color:#006837;">Success</div>
        <p style="font-size:0.92rem; color:#444; margin-bottom:24px;" id="roomSuccessMsg"></p>
        <div class="pf-modal-actions" style="justify-content:center;">
            <button type="button" class="pf-modal-btn-save" style="background:#006837; min-width:100px;" onmouseover="this.style.background='#004d29'" onmouseout="this.style.background='#006837'" onclick="closeRoomSuccessModal()">OK</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/room-file.js') }}?v={{ filemtime(public_path('js/room-file.js')) }}"></script>
@endpush
