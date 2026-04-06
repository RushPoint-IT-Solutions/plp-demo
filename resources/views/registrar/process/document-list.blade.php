@extends('layouts.registrar')

@section('title', 'PLP - Document List')
@section('page-title', 'DOCUMENT LIST')

@section('content')
<div class="doclist-page">

    {{-- ── Filter Bar ── --}}
    <div class="doclist-filter-bar">
        <div class="doclist-filter-left">
            <div class="doclist-filter-group">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-input" id="doclistSearch" placeholder="Search document..." style="width:100%;" oninput="filterDoclistTable()">
            </div>
            <div class="doclist-filter-group">
                <span class="app-filter-label">Sort By</span>
                <select class="app-filter-select" id="doclistSort" style="width:100%;" onchange="sortDoclistTable()">
                    <option value="asc">A – Z</option>
                    <option value="desc">Z – A</option>
                </select>
            </div>
        </div>
        <div class="doclist-filter-right">
            <button type="button" class="doclist-add-btn" onclick="openAddDocModal()">+ Add</button>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="student-table-wrapper table-responsive">
        <table id="doclistTable" class="student-table registrar-table doclist-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Department Type</th>
                    <th>Year Level</th>
                    <th>Document/ Requirements</th>
                    <th>Type</th>
                    <th>Non Filipino</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>2223A8137</td>
                    <td>All Year Level</td>
                    <td>2×2 Picture</td>
                    <td>Document</td>
                    <td>False</td>
                    <td>
                        <div class="apst-action-btn" data-doc-menu-toggle="docMenu1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                        <div class="apst-dropdown" id="docMenu1">
                            <button type="button" onclick="openEditModal(1, '2223A8137', 'All Year Level', '2×2 Picture', 'Document', false)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button type="button" class="apst-del-btn" onclick="openDeleteModal(1, '2×2 Picture')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>2223A8137</td>
                    <td>All Year Level</td>
                    <td>Birth Certificate</td>
                    <td>Document</td>
                    <td>False</td>
                    <td>
                        <div class="apst-action-btn" data-doc-menu-toggle="docMenu2" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                        <div class="apst-dropdown" id="docMenu2">
                            <button type="button" onclick="openEditModal(2, '2223A8137', 'All Year Level', 'Birth Certificate', 'Document', false)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button type="button" class="apst-del-btn" onclick="openDeleteModal(2, 'Birth Certificate')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

{{-- ══════ ADD DOCUMENT MODAL ══════ --}}
<div class="req-modal-overlay" id="addDocModal" style="display:none;" onclick="if(event.target===this) closeAddDocModal()">
    <div class="req-modal-box">
        <div class="req-modal-title">ADD DOCUMENT</div>
        <form id="addDocForm" onsubmit="return handleAddDocSave(event)">
            <div class="req-modal-fields" style="flex-direction:column; gap:14px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Department Type</label>
                    <select class="req-modal-input" id="addDeptType" name="department_type">
                        <option value="">-Select Type-</option>
                        <option value="2223A8137">2223A8137</option>
                    </select>
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Year Level</label>
                    <select class="req-modal-input" id="addGradeLevel" name="grade_level">
                        <option value="">-Select Grade Level-</option>
                        <option value="All Year Level">All Year Level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Document / Requirements</label>
                    <input type="text" class="req-modal-input" id="addDocument" name="document" placeholder="Enter document name">
                </div>
                <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                    <div class="req-modal-field-group" style="flex:0 0 auto;">
                        <label class="req-modal-label">Type</label>
                        <div style="display:flex; gap:14px; margin-top:4px;">
                            <label class="doclist-radio-label">
                                <input type="radio" name="add_doc_type" value="Medical"> Medical
                            </label>
                            <label class="doclist-radio-label">
                                <input type="radio" name="add_doc_type" value="Document" checked> Document
                            </label>
                        </div>
                    </div>
                    {{-- <div class="req-modal-field-group" style="flex:0 0 auto;">
                        <label class="req-modal-label">Non-Filipino</label>
                        <label class="doclist-checkbox-label" style="margin-top:4px;">
                            <input type="checkbox" id="addNonFilipino" name="non_filipino" value="1"> Yes
                        </label>
                    </div> --}}
                </div>
            </div>
            <div class="req-modal-actions">
                <button type="button" class="req-btn-cancel" onclick="closeAddDocModal()">Cancel</button>
                <button type="submit" class="req-btn-save">Add Document</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════ EDIT MODAL ══════ --}}
<div class="req-modal-overlay" id="editModal" style="display:none;">
    <div class="req-modal-box">
        <div class="req-modal-title">Edit Document</div>
        <form id="editForm" method="POST" action="#" onsubmit="return handleEditSave(event)">
            @csrf
            <input type="hidden" id="editId" name="id">

            <div class="req-modal-fields" style="flex-direction:column; gap:14px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Department Type</label>
                    <select class="req-modal-input" id="editDeptType" name="department_type">
                        <option value="2223A8137">2223A8137</option>
                    </select>
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Grade Level</label>
                    <select class="req-modal-input" id="editGradeLevel" name="grade_level">
                        <option value="All Year Level">All Year Level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">Document / Requirements</label>
                    <input type="text" class="req-modal-input" id="editDocument" name="document">
                </div>
                <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                    <div class="req-modal-field-group" style="flex:0 0 auto;">
                        <label class="req-modal-label">Type</label>
                        <div style="display:flex; gap:14px; margin-top:4px;">
                            <label class="doclist-radio-label">
                                <input type="radio" name="edit_doc_type" id="editTypeMedical" value="Medical"> Medical
                            </label>
                            <label class="doclist-radio-label">
                                <input type="radio" name="edit_doc_type" id="editTypeDocument" value="Document"> Document
                            </label>
                        </div>
                    </div>
                    {{-- <div class="req-modal-field-group" style="flex:0 0 auto;">
                        <label class="req-modal-label">Non-Filipino</label>
                        <label class="doclist-checkbox-label" style="margin-top:4px;">
                            <input type="checkbox" id="editNonFilipino" name="non_filipino" value="1"> Yes
                        </label>
                    </div> --}}
                </div>
            </div>

            <div class="req-modal-actions">
                <button type="button" class="req-btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="req-btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════ DELETE MODAL ══════ --}}
<div class="req-modal-overlay" id="deleteModal" style="display:none;">
    <div class="req-modal-box" style="text-align:center; max-width:420px;">
        <div style="margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#c62828" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="req-modal-title" style="color:#c62828;">Delete Document</div>
        <p style="font-size:0.9rem; color:#444; margin-bottom:6px;">Are you sure you want to delete</p>
        <p style="font-size:0.95rem; font-weight:700; color:#1a1a2e; margin-bottom:20px;" id="deleteDocName"></p>
        <p style="font-size:0.78rem; color:#999; margin-bottom:22px;">This action cannot be undone.</p>
        <form id="deleteForm" method="POST" action="#" onsubmit="return handleDeleteConfirm(event)">
            @csrf
            @method('DELETE')
            <input type="hidden" id="deleteId" name="id">
            <div class="req-modal-actions" style="justify-content:center;">
                <button type="button" class="req-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="req-btn-save" style="background:#c62828;" onmouseover="this.style.background='#a31f1f'" onmouseout="this.style.background='#c62828'">Delete</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/document-list.js') }}?v={{ time() }}"></script>
@endpush
@endsection