@extends('layouts.registrar')

@section('title', 'PLP - Exam List')
@section('page-title', 'EXAM LIST')

@section('content')
<div class="apst-page" id="examListPage">

    <div class="apst-topbar">
        <div class="apst-search-box">
            <input type="text" class="apst-search-input" placeholder="Search Category / Item Code / Description" id="examListSearchInput">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="apst-search-icon">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>
        <button class="apst-new-btn" id="examListNewBtn">+ New Item</button>
    </div>

    <div class="app-table-wrap">
        <table class="app-table" id="examListTable">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Category</th>
                    <th>Item Code</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody id="examListTableBody"></tbody>
        </table>
    </div>

    <div class="req-modal-overlay" id="examListModal" style="display:none;">
        <div class="req-modal-box">
            <h3 class="req-modal-title" id="examListModalTitle">NEW ITEM</h3>
            <div class="req-modal-fields">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">CATEGORY</label>
                    <select class="req-modal-input" id="examListCategoryInput">
                        <option>Aptitude Test</option>
                        <option>Interview</option>
                        <option>Evaluation</option>
                    </select>
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">ITEM CODE</label>
                    <input type="text" class="req-modal-input" id="examListCodeInput" maxlength="12" placeholder="e.g. I0000">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">DESCRIPTION</label>
                    <input type="text" class="req-modal-input" id="examListDescriptionInput" placeholder="e.g. English">
                </div>
            </div>
            <div class="req-modal-actions">
                <button class="req-btn-cancel" id="examListCancelBtn">Cancel</button>
                <button class="req-btn-save" id="examListSaveBtn">Save</button>
            </div>
        </div>
    </div>

    <div class="req-modal-overlay" id="examListDeleteModal" style="display:none;">
        <div class="req-modal-box req-modal-success exam-modal-delete-box">
            <h3 class="req-modal-title exam-modal-delete-title">DELETE ITEM</h3>
            <p class="exam-modal-delete-text">
                Are you sure you want to delete this exam item?
            </p>
            <div class="req-modal-actions exam-modal-delete-actions">
                <button class="req-btn-cancel" id="examListDeleteCancelBtn">Cancel</button>
                <button class="req-btn-save exam-btn-danger" id="examListDeleteConfirmBtn">Delete</button>
            </div>
        </div>
    </div>

    <div class="req-modal-overlay" id="examListSuccessModal" style="display:none;">
        <div class="req-modal-box req-modal-success">
            <h3 class="req-modal-success-title">SUCCESSFUL!</h3>
            <p class="req-modal-success-msg" id="examListSuccessMsg">Exam item saved successfully.</p>
            <button class="req-btn-ok" id="examListOkBtn">OK</button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/exam-list.js') }}?v={{ time() }}"></script>
@endpush
