@extends('layouts.registrar')

@section('title', 'PLP - Subject File')
@section('page-title', 'SUBJECT FILE')

@section('content')
<div
    class="sf-page"
    id="subjectFilePage"
    data-fetch-url="{{ route('registrar.registrar-menu.academic-master.subject-file.data') }}"
    data-store-url="{{ route('registrar.registrar-menu.academic-master.subject-file.store') }}"
    data-update-url-template="{{ route('registrar.registrar-menu.academic-master.subject-file.update', ['subjectId' => '__SUBJECT_ID__']) }}"
    data-delete-url-template="{{ route('registrar.registrar-menu.academic-master.subject-file.delete', ['subjectId' => '__SUBJECT_ID__']) }}"
    data-csrf-token="{{ csrf_token() }}"
>

    {{-- Toolbar --}}
    <div class="sf-topbar">
        <div class="sf-search-box">
            <input type="text" class="sf-search-input" placeholder="Search Subject Code" id="sfSearchInput">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sf-search-icon">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>
        <div class="sf-sort-wrap">
            <select class="sf-sort-select" id="sfSort">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </div>
        <button class="sf-new-btn" id="sfNewBtn">+New Subject</button>
    </div>

    {{-- Table --}}
    <div class="app-table-wrap">
        <table class="app-table" id="sfTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th style="width:5%;">Action</th>
                    <th style="width:4%;">#</th>
                    <th style="width:14%;">Subject Code</th>
                    <th style="width:33%;">Subject Title</th>
                    <th style="width:8%; text-align:center;">Lec</th>
                    <th style="width:8%; text-align:center;">Lab</th>
                    <th style="width:8%; text-align:center;">Core</th>
                    <th style="width:10%; text-align:center;">Applied</th>
                    <th style="width:10%; text-align:center;">Specialized</th>
                </tr>
            </thead>
            <tbody id="sfTableBody"></tbody>
        </table>
    </div>

    <div class="sf-pagination-bar" id="sfPaginationBar">
        <div class="sf-pagination-left">
            <label for="sfPerPage">Rows per page</label>
            <select id="sfPerPage">
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="sf-pagination-right">
            <span id="sfPageInfo">Page 1 of 1</span>
            <button type="button" class="sf-page-btn" id="sfPrevBtn">Previous</button>
            <button type="button" class="sf-page-btn" id="sfNextBtn">Next</button>
            <span id="sfTotalInfo">0 total subjects</span>
        </div>
    </div>

    {{-- ══════ NEW / EDIT SUBJECT MODAL ══════ --}}
    <div class="req-modal-overlay" id="sfModal" style="display:none;" onclick="closeSfModal(event)">
        <div class="req-modal-box" style="max-width:520px;">
            <h3 class="req-modal-title" id="sfModalTitle">NEW SUBJECT</h3>
            <div class="req-modal-fields" style="flex-direction:column; gap:14px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Subject Code</label>
                    <input type="text" class="req-modal-input" id="sfInputCode" placeholder="e.g. CP 126">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Subject Title</label>
                    <input type="text" class="req-modal-input" id="sfInputTitle" placeholder="e.g. Capstone Project">
                </div>
                <div style="display:flex; gap:12px;">
                    <div class="req-modal-field-group" style="flex:1;">
                        <label class="req-modal-label">Lec</label>
                        <input type="number" step="1" min="0" class="req-modal-input" id="sfInputLec" placeholder="0">
                    </div>
                    <div class="req-modal-field-group" style="flex:1;">
                        <label class="req-modal-label">Lab</label>
                        <input type="number" step="1" min="0" class="req-modal-input" id="sfInputLab" placeholder="0">
                    </div>
                </div>
                <div style="display:flex; gap:20px; flex-wrap:wrap; align-items:center;">
                    <label class="sf-check-label"><input type="checkbox" id="sfInputCore"> Core</label>
                    <label class="sf-check-label"><input type="checkbox" id="sfInputApplied"> Applied</label>
                    <label class="sf-check-label"><input type="checkbox" id="sfInputSpecialized"> Specialized</label>
                </div>
            </div>
            <div class="req-modal-actions">
                <button class="req-btn-cancel" onclick="closeSfModal()">Cancel</button>
                <button class="req-btn-save" onclick="saveSubject()">Save</button>
            </div>
        </div>
    </div>

    {{-- ══════ DELETE CONFIRM MODAL ══════ --}}
    <div class="req-modal-overlay" id="sfDeleteModal" style="display:none;" onclick="closeSfDeleteModal(event)">
        <div class="req-modal-box req-modal-success" style="min-width:300px;">
            <h3 class="req-modal-title" style="color:#c0392b;">DELETE SUBJECT</h3>
            <p style="font-size:0.88rem; color:#444; margin-bottom:6px; text-align:center;">Are you sure you want to delete</p>
            <p style="font-size:0.95rem; font-weight:700; color:#1a1a2e; margin-bottom:20px; text-align:center;" id="sfDeleteName"></p>
            <p style="font-size:0.78rem; color:#999; margin-bottom:22px; text-align:center;">This action cannot be undone.</p>
            <div class="req-modal-actions" style="justify-content:center;">
                <button class="req-btn-cancel" onclick="closeSfDeleteModal()">Cancel</button>
                <button class="req-btn-save" style="background:#c0392b;" onclick="confirmDeleteSubject()">Delete</button>
            </div>
        </div>
    </div>

    {{-- ══════ SUCCESS MODAL ══════ --}}
    <div class="req-modal-overlay" id="sfSuccessModal" style="display:none;" onclick="closeSfSuccess(event)">
        <div class="req-modal-box req-modal-success">
            <h3 class="req-modal-success-title">SUCCESSFUL!</h3>
            <p class="req-modal-success-msg" id="sfSuccessMsg">Subject saved successfully.</p>
            <button class="req-btn-ok" onclick="closeSfSuccess()">OK</button>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/subject-file.js') }}"></script>
@endpush
@endsection
