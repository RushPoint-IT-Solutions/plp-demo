@extends('layouts.registrar')

@section('title', 'PLP - Document List')
@section('page-title', 'DOCUMENT LIST')

@section('content')
<div class="doclist-page">

    {{-- ── Add / Edit Form ── --}}
    <div class="doclist-form-card">
        <form method="POST" action="#" id="doclistForm" onsubmit="return handleDoclistSave(event)">
            @csrf

            {{-- Row 1: dropdowns + text --}}
            <div class="registrar-form-row">
                <div class="registrar-form-group">
                    <label class="registrar-form-label">Department Type</label>
                    <select class="registrar-form-input" name="department_type">
                        <option value="">-Select Type-</option>
                        <option value="2223A8137">2223A8137</option>
                    </select>
                </div>
                <div class="registrar-form-group">
                    <label class="registrar-form-label">Year Level</label>
                    <select class="registrar-form-input" name="grade_level">
                        <option value="">-Select Grade Level-</option>
                        <option value="All Year Level">All Year Level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="registrar-form-group">
                    <label class="registrar-form-label">Document/Requirements</label>
                    <input type="text" class="registrar-form-input" name="document" placeholder="Document">
                </div>
            </div>

            {{-- Row 2: radios + checkbox + save --}}
            <div class="doclist-options-row">
                <div class="doclist-radios">
                    <label class="doclist-radio-label">
                        <input type="radio" name="doc_type" value="Medical"> Medical
                    </label>
                    <label class="doclist-radio-label">
                        <input type="radio" name="doc_type" value="Document" checked> Document
                    </label>
                </div>
                <label class="doclist-checkbox-label">
                    <input type="checkbox" name="non_filipino" value="1"> Non-Filipino
                </label>
                <div class="doclist-save-wrap">
                    <button type="submit" class="btn-registrar-save">Save</button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="student-table-wrapper">
        <table class="student-table registrar-table doclist-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Department Type</th>
                    <th>Grade Level</th>
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
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn"
                                onclick="openEditModal(1, '2223A8137', 'All Year Level', '2×2 Picture', 'Document', false)" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn"
                                onclick="openDeleteModal(1, '2×2 Picture')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
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
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn"
                                onclick="openEditModal(2, '2223A8137', 'All Year Level', 'Birth Certificate', 'Document', false)" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn"
                                onclick="openDeleteModal(2, 'Birth Certificate')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
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
                    <div class="req-modal-field-group" style="flex:0 0 auto;">
                        <label class="req-modal-label">Non-Filipino</label>
                        <label class="doclist-checkbox-label" style="margin-top:4px;">
                            <input type="checkbox" id="editNonFilipino" name="non_filipino" value="1"> Yes
                        </label>
                    </div>
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

<script>
    // ── Stub handlers (no backend yet) ──
    function handleDoclistSave(e) {
        e.preventDefault();
        var btn = document.querySelector('#doclistForm .btn-registrar-save');
        var orig = btn.textContent;
        btn.textContent = 'Saved!';
        btn.disabled = true;
        setTimeout(function() { btn.textContent = orig; btn.disabled = false; }, 1500);
        return false;
    }

    function handleEditSave(e) {
        e.preventDefault();
        closeEditModal();
        return false;
    }

    function handleDeleteConfirm(e) {
        e.preventDefault();
        closeDeleteModal();
        return false;
    }

    // ── Edit Modal ──
    function openEditModal(id, dept, grade, doc, type, nonFilipino) {
        document.getElementById('editId').value = id;
        document.getElementById('editDeptType').value = dept;
        document.getElementById('editGradeLevel').value = grade;
        document.getElementById('editDocument').value = doc;

        if (type === 'Medical') {
            document.getElementById('editTypeMedical').checked = true;
        } else {
            document.getElementById('editTypeDocument').checked = true;
        }

        document.getElementById('editNonFilipino').checked = !!nonFilipino;
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // ── Delete Modal ──
    function openDeleteModal(id, docName) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteDocName').textContent = '"' + docName + '"';
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modals on overlay click
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditModal();
            closeDeleteModal();
        }
    });
</script>
@endsection