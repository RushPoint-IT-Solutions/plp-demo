@extends('layouts.registrar')

@section('title', 'PLP - Approval Status')
@section('page-title', 'APPROVAL STATUS')

@section('content')
<div class="apst-page">

    {{-- Top bar: search + new button --}}
    <div class="apst-topbar">
        <div class="apst-search-box">
            <input type="text" class="apst-search-input" placeholder="Search Status Code" id="apstSearchInput" oninput="filterRows()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="apst-search-icon">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>
        <button class="apst-new-btn" onclick="openNewModal()">+ New Status</button>
    </div>

    {{-- Table --}}
    <div class="app-table-wrap">
        <table class="app-table" id="apstTable">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Status Code</th>
                    <th>Status</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody id="apstTableBody"></tbody>
        </table>
    </div>

    {{-- Edit / New Modal --}}
    <div class="req-modal-overlay" id="apstModal" style="display:none;" onclick="closeApstModal(event)">
        <div class="req-modal-box">
            <h3 class="req-modal-title" id="apstModalTitle">NEW STATUS</h3>
            <div class="req-modal-fields">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">STATUS CODE</label>
                    <input type="text" class="req-modal-input" id="apstInputCode" placeholder="e.g. D" maxlength="5">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">STATUS</label>
                    <input type="text" class="req-modal-input" id="apstInputStatus" placeholder="e.g. Document Submitted">
                </div>
            </div>
            <div class="req-modal-field-group" style="margin-top:12px;">
                <label class="req-modal-label">MESSAGE</label>
                <textarea class="req-modal-input" id="apstInputMessage" rows="3" placeholder="Enter message..."
                    style="resize:vertical; font-size:0.85rem; padding:8px 10px;"></textarea>
            </div>
            <div class="req-modal-actions">
                <button class="req-btn-cancel" onclick="closeApstModal()">Cancel</button>
                <button class="req-btn-save" onclick="saveApstRow()">Save</button>
            </div>
        </div>
    </div>

    {{-- Delete confirm modal --}}
    <div class="req-modal-overlay" id="apstDeleteModal" style="display:none;" onclick="closeDeleteModal(event)">
        <div class="req-modal-box req-modal-success" style="min-width:300px;">
            <h3 class="req-modal-title" style="color:#c0392b;">DELETE STATUS</h3>
            <p style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;">
                Are you sure you want to delete this status?
            </p>
            <div class="req-modal-actions" style="justify-content:center;">
                <button class="req-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="req-btn-save" style="background:#c0392b;" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    {{-- Success modal --}}
    <div class="req-modal-overlay" id="apstSuccessModal" style="display:none;" onclick="closeApstSuccess(event)">
        <div class="req-modal-box req-modal-success">
            <h3 class="req-modal-success-title">SUCCESSFUL!</h3>
            <p class="req-modal-success-msg" id="apstSuccessMsg">Status saved successfully.</p>
            <button class="req-btn-ok" onclick="closeApstSuccess()">OK</button>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/approval-status.js') }}"></script>
@endpush
@endsection
