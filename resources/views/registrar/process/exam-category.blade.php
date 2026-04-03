@extends('layouts.registrar')

@section('title', 'PLP - Exam Category')
@section('page-title', 'EXAM CATEGORY')

@section('content')
<div class="apst-page" id="examCategoryPage">

    <div class="apst-topbar">
        <div class="apst-search-box">
            <input type="text" class="apst-search-input" placeholder="Search Code / Description" id="examCategorySearchInput">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="apst-search-icon">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>
        <button class="apst-new-btn" id="examCategoryNewBtn">+ New Category</button>
    </div>

    <div class="app-table-wrap">
        <table class="app-table" id="examCategoryTable">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Category Code</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody id="examCategoryTableBody"></tbody>
        </table>
    </div>

    <div class="req-modal-overlay" id="examCategoryModal" style="display:none;">
        <div class="req-modal-box">
            <h3 class="req-modal-title" id="examCategoryModalTitle">NEW CATEGORY</h3>
            <div class="req-modal-fields">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">CATEGORY CODE</label>
                    <input type="text" class="req-modal-input" id="examCategoryCodeInput" maxlength="10" placeholder="e.g. C0000">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">DESCRIPTION</label>
                    <input type="text" class="req-modal-input" id="examCategoryDescriptionInput" placeholder="e.g. Aptitude Test">
                </div>
            </div>
            <div class="req-modal-actions">
                <button class="req-btn-cancel" id="examCategoryCancelBtn">Cancel</button>
                <button class="req-btn-save" id="examCategorySaveBtn">Save</button>
            </div>
        </div>
    </div>

    <div class="req-modal-overlay" id="examCategoryDeleteModal" style="display:none;">
        <div class="req-modal-box req-modal-success exam-modal-delete-box">
            <h3 class="req-modal-title exam-modal-delete-title">DELETE CATEGORY</h3>
            <p class="exam-modal-delete-text">
                Are you sure you want to delete this category?
            </p>
            <div class="req-modal-actions exam-modal-delete-actions">
                <button class="req-btn-cancel" id="examCategoryDeleteCancelBtn">Cancel</button>
                <button class="req-btn-save exam-btn-danger" id="examCategoryDeleteConfirmBtn">Delete</button>
            </div>
        </div>
    </div>

    <div class="req-modal-overlay" id="examCategorySuccessModal" style="display:none;">
        <div class="req-modal-box req-modal-success">
            <h3 class="req-modal-success-title">SUCCESSFUL!</h3>
            <p class="req-modal-success-msg" id="examCategorySuccessMsg">Category saved successfully.</p>
            <button class="req-btn-ok" id="examCategoryOkBtn">OK</button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/exam-category.js') }}?v={{ time() }}"></script>
@endpush
