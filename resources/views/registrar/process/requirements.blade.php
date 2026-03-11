@extends('layouts.registrar')

@section('title', 'PLP - Requirements')
@section('page-title', 'REQUIREMENTS')

@section('content')
<div class="req-page" id="reqPage">

    {{-- ══════════════════════════════════════════════
         VIEW 1 — STUDENT CARD LIST
    ══════════════════════════════════════════════ --}}
    <div id="reqListView">
        {{-- Search bar --}}
        <div class="req-search-wrap">
            <div class="req-search-box">
                <input type="text" class="req-search-input" placeholder="Search Name/ID" id="reqSearchInput" oninput="filterCards()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="req-search-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
        </div>

        {{-- Student cards grid --}}
        <div class="req-cards-grid" id="reqCardsGrid">
            {{-- Cards generated dynamically by JS --}}
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         VIEW 2 — STUDENT DETAIL TABLE
    ══════════════════════════════════════════════ --}}
    <div id="reqDetailView" style="display:none;">
        <div class="req-detail-topbar">
            <button class="req-back-btn" onclick="showListView()">&#8592; Back</button>
            <div class="req-search-box req-search-box-sm">
                <input type="text" class="req-search-input" placeholder="Search Requirement" id="reqDetailSearch" oninput="filterDetailRows()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="req-search-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
        </div>

        <div class="req-detail-header">
            <span class="req-detail-name-label">NAME:<span id="detailName"></span></span>
            <span class="req-detail-id-label">ID: <span id="detailId"></span></span>
        </div>

        <div class="app-table-wrap">
            <table class="app-table">
                <thead>
                    <tr>
                        <th style="width:80px;">Submit</th>
                        <th>Requirement Name</th>
                        <th>Remarks</th>
                        <th style="width:160px;">Date Verified</th>
                    </tr>
                </thead>
                <tbody id="reqDetailTableBody"></tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         EDIT MODAL
    ══════════════════════════════════════════════ --}}
    <div class="req-modal-overlay" id="reqEditModal" style="display:none;" onclick="closeEditModal(event)">
        <div class="req-modal-box">
            <h3 class="req-modal-title" id="modalTitle">LIBRARY CLEARANCE</h3>
            <div class="req-modal-meta">
                <span>NAME: <strong id="modalStudentName"></strong></span>
                <span>STUDENT NO.: <strong id="modalStudentNo"></strong></span>
            </div>
            <div class="req-modal-fields">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">REQUIREMENT NAME</label>
                    <input type="text" class="req-modal-input" id="modalReqName" placeholder="Library Clearance">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">REMARKS:</label>
                    <input type="text" class="req-modal-input" id="modalRemarks" placeholder="Write Remarks...">
                </div>
            </div>
            <div class="req-modal-field-group" style="margin-top:12px;">
                <label class="req-modal-label">DATE VERIFIED</label>
                <input type="text" class="req-modal-input" id="modalDateVerified" placeholder="mm/dd/yy" style="max-width:200px;">
                <small class="req-modal-hint">Format: mm/dd/yy &nbsp;&#8226;&nbsp; Leave blank to use today's date</small>
            </div>
            <div class="req-modal-field-group req-photo-group" id="modalPhotoGroup" style="display:none; margin-top:12px;">
                <label class="req-modal-label">UPLOAD PHOTO</label>
                <input type="file" class="req-modal-file" id="modalPhotoUpload" accept="image/*" onchange="previewPhoto(this)">
                <div id="modalPhotoPreview"></div>
            </div>
            <div class="req-modal-actions">
                <button class="req-btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="req-btn-save" onclick="saveRequirement()">Save</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         SUCCESS MODAL
    ══════════════════════════════════════════════ --}}
    <div class="req-modal-overlay" id="reqSuccessModal" style="display:none;" onclick="closeSuccessModal(event)">
        <div class="req-modal-box req-modal-success">
            <h3 class="req-modal-success-title">SUCCESSFUL!</h3>
            <p class="req-modal-success-msg" id="successMsg">Requirement updated successfully.</p>
            <button class="req-btn-ok" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/requirements.js') }}"></script>
@endpush
@endsection
