@extends('layouts.registrar')

@section('title', 'PLP - Course File')
@section('page-title', 'COURSE FILE')

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
        @include('registrar.components.search-bar', [
            'id' => 'sfSearchInput',
            'placeholder' => 'Search Course Code',
        ])
        <div class="sf-sort-wrap">
            <select class="sf-sort-select" id="sfSort">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </div>
        <button class="sf-new-btn" id="sfNewBtn">+New Course</button>
    </div>

    {{-- Table --}}
    <div class="app-table-wrap">
        <table class="app-table" id="sfTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th style="width:5%;">Action</th>
                    <th style="width:4%;">#</th>
                    <th style="width:14%;">Course Code</th>
                    <th style="width:25%;">Descriptive Title</th>
                    <th style="width:8%; text-align:center;">Lec</th>
                    <th style="width:8%; text-align:center;">Lab</th>
                    <th style="width:8%; text-align:center;">Total Units</th>
                    <th style="width:8%; text-align:center;">Hours</th>
                    <th style="width:10%; text-align:center;">Course Type</th>
                    <th style="width:8%; text-align:center;">Core</th>
                    <th style="width:10%; text-align:center;">Applied</th>
                    <th style="width:10%; text-align:center;">Specialized</th>
                </tr>
            </thead>
            <tbody id="sfTableBody"></tbody>
        </table>
    </div>

    <div class="sf-pagination-bar sf-pagination-compact" id="sfPaginationBar">
        <div class="rtp-pagination">
            <nav class="rtp-nav" aria-label="Course File pagination">
                <div class="rtp-list" role="group" aria-label="Page controls">
                    <button type="button" class="rtp-page-btn" id="sfPrevBtn" aria-label="Previous page" disabled>&lt;</button>
                    <div class="rtp-pages" id="sfPageNumbers">
                        <button type="button" class="rtp-page-num active" aria-current="page" disabled>1</button>
                    </div>
                    <button type="button" class="rtp-page-btn" id="sfNextBtn" aria-label="Next page" disabled>&gt;</button>
                </div>
            </nav>
        </div>
    </div>

    {{-- ══════ NEW / EDIT SUBJECT MODAL ══════ --}}
    <div class="req-modal-overlay" id="sfModal" style="display:none;" onclick="closeSfModal(event)">
        <div class="req-modal-box" style="max-width:520px;">
            <h3 class="req-modal-title" id="sfModalTitle">NEW COURSE</h3>
            <div class="req-modal-fields" style="flex-direction:column; gap:14px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Course Code</label>
                    <input type="text" class="req-modal-input" id="sfInputCode" placeholder="e.g. CP 126">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Course Title</label>
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
                <div style="display:flex; gap:12px;">
                    <div class="req-modal-field-group" style="flex:1;">
                        <label class="req-modal-label">Hours</label>
                        <input type="number" step="0.5" min="0.5" class="req-modal-input" id="sfInputHours" placeholder="e.g. 3.0">
                        <small style="display:block; margin-top:5px; color:#64748b; font-size:.78rem;">Used as the schedule duration for room generation.</small>
                    </div>
                    <div class="req-modal-field-group" style="flex:1;">
                        <label class="req-modal-label">Course Type</label>
                        <select class="req-modal-input" id="sfInputCourseType">
                            <option value="Major">Major</option>
                            <option value="Minor">Minor</option>
                            <option value="GE">GE</option>
                            <option value="Elective">Elective</option>
                        </select>
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
    @include('includes.registrar-delete-modal', [
        'id' => 'sfDeleteModal',
        'title' => 'DELETE COURSE',
        'message' => 'Are you sure you want to delete',
        'confirmBtnText' => 'Delete',
        'cancelAction' => 'closeSfDeleteModal()',
        'confirmAction' => 'confirmDeleteSubject()',
        'detailId' => 'sfDeleteName'
    ])

    {{-- ══════ SUCCESS MODAL ══════ --}}
    @include('includes.registrar-success-modal', [
        'id' => 'sfSuccessModal',
        'msgId' => 'sfSuccessMsg',
        'onClose' => 'closeSfSuccess()'
    ])

</div>

@push('scripts')
<script src="{{ asset('js/subject-file.js') }}?v={{ file_exists(public_path('js/subject-file.js')) ? filemtime(public_path('js/subject-file.js')) : time() }}"></script>
@endpush
@endsection
