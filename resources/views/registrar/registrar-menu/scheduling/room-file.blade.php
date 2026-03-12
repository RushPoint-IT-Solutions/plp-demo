@extends('layouts.registrar')

@section('title', 'PLP - Room File')
@section('page-title', 'ROOM FILE')

@section('content')
<div class="pf-page">

    {{-- Toolbar --}}
    <div class="pf-toolbar">
        <div class="pf-search-wrap">
            <svg class="pf-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="pf-search-input" placeholder="Search Room No" id="rfSearch">
        </div>
        <button type="button" class="pf-btn-new" onclick="openNewRoomModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Room
        </button>
    </div>

    {{-- Table --}}
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" id="rfTable">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Room #</th>
                    <th>Floor</th>
                    <th>Building & Hallway</th>
                    <th>Students</th>
                    <th>Programs</th>
                    <th>Update by</th>
                </tr>
            </thead>
            <tbody id="rfBody">
                {{-- JS-rendered rows --}}
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pf-pagination">
        <span class="pf-page-info" id="rfPageInfo">Showing 0 rooms</span>
    </div>
</div>

{{-- ══════ NEW ROOM MODAL ══════ --}}
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
                    <label class="pf-modal-label">Building & Hallway</label>
                    <select class="pf-modal-select" id="newRoomBuilding" required>
                        <option value="">- Select Building -</option>
                        <option value="Campus 1">Campus 1</option>
                        <option value="Campus 2">Campus 2</option>
                        <option value="Campus 3">Campus 3</option>
                        <option value="Campus 4">Campus 4</option>
                    </select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Students (Capacity)</label>
                    <input type="number" class="pf-modal-input" id="newRoomStudents" placeholder="e.g. 50" required min="1">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Programs</label>
                    <select class="pf-modal-select" id="newRoomProgram" required>
                        <option value="">- Select Program -</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSED">BSED</option>
                        <option value="BSAT">BSAT</option>
                        <option value="BSIT-Animation">BSIT-Animation</option>
                        <option value="BSN">BSN</option>
                        <option value="BSET">BSET</option>
                    </select>
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
                    <label class="pf-modal-label">Building & Hallway</label>
                    <select class="pf-modal-select" id="editRoomBuilding" required>
                        <option value="">- Select Building -</option>
                        <option value="Campus 1">Campus 1</option>
                        <option value="Campus 2">Campus 2</option>
                        <option value="Campus 3">Campus 3</option>
                        <option value="Campus 4">Campus 4</option>
                    </select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Students (Capacity)</label>
                    <input type="number" class="pf-modal-input" id="editRoomStudents" required min="1">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Programs</label>
                    <select class="pf-modal-select" id="editRoomProgram" required>
                        <option value="">- Select Program -</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSED">BSED</option>
                        <option value="BSAT">BSAT</option>
                        <option value="BSIT-Animation">BSIT-Animation</option>
                        <option value="BSN">BSN</option>
                        <option value="BSET">BSET</option>
                    </select>
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeEditRoomModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save">Save Changes</button>
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
<script src="{{ asset('js/room-file.js') }}"></script>
@endpush
