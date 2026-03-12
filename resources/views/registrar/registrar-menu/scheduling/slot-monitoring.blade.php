@extends('layouts.registrar')

@section('title', 'PLP - Slot Monitoring')
@section('page-title', 'SLOT MONITORING')

@section('content')
<div class="pf-page">

    {{-- Filter Bar --}}
    <div class="sched-filter-bar">
        <div class="sched-filter-row">
            <div class="sched-filter-group">
                <span class="app-filter-label">School Year</span>
                <select class="app-filter-select" id="smSY" style="width:100%;">
                    <option value="2025-2026">2025-2026</option>
                    <option value="2024-2025">2024-2025</option>
                    <option value="2023-2024">2023-2024</option>
                </select>
            </div>
            <div class="sched-filter-group">
                <span class="app-filter-label">Semester</span>
                <select class="app-filter-select" id="smSemester" style="width:100%;">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="sched-filter-group">
                <span class="app-filter-label">Section</span>
                <select class="app-filter-select" id="smSection" style="width:100%;">
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
            <div class="sched-filter-group">
                <span class="app-filter-label">Course</span>
                <select class="app-filter-select" id="smCourse" style="width:100%;">
                    <option value="BSIT">BSIT</option>
                    <option value="BSCS">BSCS</option>
                    <option value="BSED">BSED</option>
                    <option value="BSAT">BSAT</option>
                    <option value="BSN">BSN</option>
                    <option value="BSET">BSET</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Add Slot Button --}}
    <div style="display:flex; justify-content:flex-end; margin-bottom:14px;">
        <button type="button" class="pf-btn-new" onclick="openAddSlotModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Slot
        </button>
    </div>

    {{-- Table --}}
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" id="smTable">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>SY</th>
                    <th>Semester</th>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Schedule</th>
                    <th>Total Slots</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="smBody">
                {{-- JS-rendered rows --}}
            </tbody>
        </table>
    </div>

    <div class="pf-pagination">
        <span class="pf-page-info" id="smPageInfo">Showing 0 slots</span>
    </div>
</div>

{{-- ══════ ADD SLOT MODAL ══════ --}}
<div class="pf-modal-overlay" id="addSlotModal" style="display:none;">
    <div class="pf-modal-box">
        <div class="pf-modal-title">Add Slot</div>
        <form id="addSlotForm" onsubmit="return handleAddSlotSave(event)">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label">School Year</label>
                    <select class="pf-modal-select" id="addSlotSY" required>
                        <option value="2025-2026">2025-2026</option>
                        <option value="2024-2025">2024-2025</option>
                    </select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Semester</label>
                    <select class="pf-modal-select" id="addSlotSemester" required>
                        <option value="First">First</option>
                        <option value="Second">Second</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Section</label>
                    <input type="text" class="pf-modal-input" id="addSlotSection" placeholder="e.g. BSIT 1-A" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Subject</label>
                    <input type="text" class="pf-modal-input" id="addSlotSubject" placeholder="e.g. CC101 (3.0)" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Schedule</label>
                    <input type="text" class="pf-modal-input" id="addSlotSchedule" placeholder="e.g. S | 10:00AM-1:00PM | RM#1" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Total Slots</label>
                    <input type="number" class="pf-modal-input" id="addSlotTotal" placeholder="e.g. 50" required min="1">
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeAddSlotModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save">Add Slot</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════ EDIT SLOT MODAL ══════ --}}
<div class="pf-modal-overlay" id="editSlotModal" style="display:none;">
    <div class="pf-modal-box">
        <div class="pf-modal-title">Edit Slot</div>
        <form id="editSlotForm" onsubmit="return handleEditSlotSave(event)">
            <input type="hidden" id="editSlotId">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Section</label>
                    <input type="text" class="pf-modal-input" id="editSlotSection" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Subject</label>
                    <input type="text" class="pf-modal-input" id="editSlotSubject" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Schedule</label>
                    <input type="text" class="pf-modal-input" id="editSlotSchedule" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Total Slots</label>
                    <input type="number" class="pf-modal-input" id="editSlotTotal" required min="1">
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Enrolled</label>
                    <input type="number" class="pf-modal-input" id="editSlotEnrolled" required min="0">
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeEditSlotModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════ DELETE SLOT MODAL ══════ --}}
<div class="pf-modal-overlay" id="deleteSlotModal" style="display:none;">
    <div class="pf-modal-box" style="text-align:center; max-width:420px;">
        <div style="margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#c62828" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="pf-modal-title" style="color:#c62828;">Delete Slot</div>
        <p style="font-size:0.9rem; color:#444; margin-bottom:6px;">Are you sure you want to delete</p>
        <p style="font-size:0.95rem; font-weight:700; color:#1a1a2e; margin-bottom:20px;" id="deleteSlotName"></p>
        <p style="font-size:0.78rem; color:#999; margin-bottom:22px;">This action cannot be undone.</p>
        <input type="hidden" id="deleteSlotId">
        <div class="pf-modal-actions" style="justify-content:center;">
            <button type="button" class="pf-modal-btn-cancel" onclick="closeDeleteSlotModal()">Cancel</button>
            <button type="button" class="pf-modal-btn-save" style="background:#c62828;" onmouseover="this.style.background='#a31f1f'" onmouseout="this.style.background='#c62828'" onclick="handleDeleteSlotConfirm()">Delete</button>
        </div>
    </div>
</div>

{{-- ══════ SUCCESS MODAL ══════ --}}
<div class="pf-modal-overlay" id="slotSuccessModal" style="display:none;">
    <div class="pf-modal-box" style="text-align:center; max-width:420px;">
        <div style="margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>
            </svg>
        </div>
        <div class="pf-modal-title" style="color:#006837;">Success</div>
        <p style="font-size:0.92rem; color:#444; margin-bottom:24px;" id="slotSuccessMsg"></p>
        <div class="pf-modal-actions" style="justify-content:center;">
            <button type="button" class="pf-modal-btn-save" style="background:#006837; min-width:100px;" onmouseover="this.style.background='#004d29'" onmouseout="this.style.background='#006837'" onclick="closeSlotSuccessModal()">OK</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/slot-monitoring.js') }}"></script>
@endpush
