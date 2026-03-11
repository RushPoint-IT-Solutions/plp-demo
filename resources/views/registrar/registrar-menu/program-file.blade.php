@extends('layouts.registrar')

@section('title', 'PLP - Program File')
@section('page-title', 'PROGRAM FILE')

@section('content')
<div class="pf-page">

    {{-- Toolbar --}}
    <div class="pf-toolbar">
        <div class="pf-search-wrap">
            <svg class="pf-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="pf-search-input" placeholder="Search programs..." id="pfSearch">
        </div>
        <button type="button" class="pf-btn-new" onclick="openNewProgramModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Program
        </button>
    </div>

    {{-- Table --}}
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" id="pfTable">
            <thead>
                <tr>
                    <th>Program Code</th>
                    <th>Program Name</th>
                    <th>Department</th>
                    <th>Accreditation Level</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>BSIT</td>
                    <td>Bachelor of Science in Information Technology</td>
                    <td>College of Information Technology Education</td>
                    <td>Level I Accredited</td>
                    <td>
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn" onclick="openEditProgramModal(1,'BSIT','Bachelor of Science in Information Technology','College of Information Technology Education','Level I Accredited')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn" onclick="openDeleteProgramModal(1,'BSIT')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>BSCS</td>
                    <td>Bachelor of Science in Computer Science</td>
                    <td>College of Information Technology Education</td>
                    <td>Level II Accredited</td>
                    <td>
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn" onclick="openEditProgramModal(2,'BSCS','Bachelor of Science in Computer Science','College of Information Technology Education','Level II Accredited')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn" onclick="openDeleteProgramModal(2,'BSCS')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>BSA</td>
                    <td>Bachelor of Science in Accountancy</td>
                    <td>College of Business and Accountancy</td>
                    <td>Level III Accredited</td>
                    <td>
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn" onclick="openEditProgramModal(3,'BSA','Bachelor of Science in Accountancy','College of Business and Accountancy','Level III Accredited')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn" onclick="openDeleteProgramModal(3,'BSA')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>BSBA</td>
                    <td>Bachelor of Science in Business Administration</td>
                    <td>College of Business and Accountancy</td>
                    <td>Level II Accredited</td>
                    <td>
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn" onclick="openEditProgramModal(4,'BSBA','Bachelor of Science in Business Administration','College of Business and Accountancy','Level II Accredited')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn" onclick="openDeleteProgramModal(4,'BSBA')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>BSN</td>
                    <td>Bachelor of Science in Nursing</td>
                    <td>College of Allied Health Sciences</td>
                    <td>Level III Accredited</td>
                    <td>
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn" onclick="openEditProgramModal(5,'BSN','Bachelor of Science in Nursing','College of Allied Health Sciences','Level III Accredited')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn" onclick="openDeleteProgramModal(5,'BSN')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>BSCE</td>
                    <td>Bachelor of Science in Civil Engineering</td>
                    <td>College of Engineering and Architecture</td>
                    <td>Level I Accredited</td>
                    <td>
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn" onclick="openEditProgramModal(6,'BSCE','Bachelor of Science in Civil Engineering','College of Engineering and Architecture','Level I Accredited')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn" onclick="openDeleteProgramModal(6,'BSCE')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>BEED</td>
                    <td>Bachelor of Elementary Education</td>
                    <td>College of Education</td>
                    <td>Level II Accredited</td>
                    <td>
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn" onclick="openEditProgramModal(7,'BEED','Bachelor of Elementary Education','College of Education','Level II Accredited')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn" onclick="openDeleteProgramModal(7,'BEED')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>BSED</td>
                    <td>Bachelor of Secondary Education</td>
                    <td>College of Education</td>
                    <td>Level II Accredited</td>
                    <td>
                        <div class="doclist-actions">
                            <button class="doclist-action-btn doclist-edit-btn" onclick="openEditProgramModal(8,'BSED','Bachelor of Secondary Education','College of Education','Level II Accredited')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button class="doclist-action-btn doclist-delete-btn" onclick="openDeleteProgramModal(8,'BSED')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pf-pagination">
        <span class="pf-page-info">Showing 1-8 of 8 programs</span>
    </div>
</div>

{{-- ══════ NEW PROGRAM MODAL ══════ --}}
<div class="pf-modal-overlay" id="newProgramModal" style="display:none;">
    <div class="pf-modal-box">
        <div class="pf-modal-title">New Program</div>
        <form id="newProgramForm" onsubmit="return handleNewProgramSave(event)">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Program Code</label>
                    <input type="text" class="pf-modal-input" id="newProgramCode" placeholder="e.g. BSIT" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Program Name</label>
                    <input type="text" class="pf-modal-input" id="newProgramName" placeholder="e.g. Bachelor of Science in Information Technology" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Department</label>
                    <select class="pf-modal-select" id="newProgramDept" required>
                        <option value="">- Select Department -</option>
                        <option value="College of Information Technology Education">College of Information Technology Education</option>
                        <option value="College of Business and Accountancy">College of Business and Accountancy</option>
                        <option value="College of Allied Health Sciences">College of Allied Health Sciences</option>
                        <option value="College of Engineering and Architecture">College of Engineering and Architecture</option>
                        <option value="College of Education">College of Education</option>
                        <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                    </select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Accreditation Level</label>
                    <select class="pf-modal-select" id="newProgramAccred" required>
                        <option value="">- Select Level -</option>
                        <option value="Level I Accredited">Level I Accredited</option>
                        <option value="Level II Accredited">Level II Accredited</option>
                        <option value="Level III Accredited">Level III Accredited</option>
                        <option value="Level IV Accredited">Level IV Accredited</option>
                        <option value="Pending Review">Pending Review</option>
                    </select>
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeNewProgramModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save">Add Program</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════ EDIT PROGRAM MODAL ══════ --}}
<div class="pf-modal-overlay" id="editProgramModal" style="display:none;">
    <div class="pf-modal-box">
        <div class="pf-modal-title">Edit Program</div>
        <form id="editProgramForm" onsubmit="return handleEditProgramSave(event)">
            <input type="hidden" id="editProgramId">
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Program Code</label>
                    <input type="text" class="pf-modal-input" id="editProgramCode" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Program Name</label>
                    <input type="text" class="pf-modal-input" id="editProgramName" required>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Department</label>
                    <select class="pf-modal-select" id="editProgramDept" required>
                        <option value="">- Select Department -</option>
                        <option value="College of Information Technology Education">College of Information Technology Education</option>
                        <option value="College of Business and Accountancy">College of Business and Accountancy</option>
                        <option value="College of Allied Health Sciences">College of Allied Health Sciences</option>
                        <option value="College of Engineering and Architecture">College of Engineering and Architecture</option>
                        <option value="College of Education">College of Education</option>
                        <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                    </select>
                </div>
                <div class="pf-modal-field">
                    <label class="pf-modal-label">Accreditation Level</label>
                    <select class="pf-modal-select" id="editProgramAccred" required>
                        <option value="">- Select Level -</option>
                        <option value="Level I Accredited">Level I Accredited</option>
                        <option value="Level II Accredited">Level II Accredited</option>
                        <option value="Level III Accredited">Level III Accredited</option>
                        <option value="Level IV Accredited">Level IV Accredited</option>
                        <option value="Pending Review">Pending Review</option>
                    </select>
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeEditProgramModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════ DELETE PROGRAM MODAL ══════ --}}
<div class="pf-modal-overlay" id="deleteProgramModal" style="display:none;">
    <div class="pf-modal-box" style="text-align:center; max-width:420px;">
        <div style="margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#c62828" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="pf-modal-title" style="color:#c62828;">Delete Program</div>
        <p style="font-size:0.9rem; color:#444; margin-bottom:6px;">Are you sure you want to delete</p>
        <p style="font-size:0.95rem; font-weight:700; color:#1a1a2e; margin-bottom:20px;" id="deleteProgramName"></p>
        <p style="font-size:0.78rem; color:#999; margin-bottom:22px;">This action cannot be undone.</p>
        <input type="hidden" id="deleteProgramId">
        <div class="pf-modal-actions" style="justify-content:center;">
            <button type="button" class="pf-modal-btn-cancel" onclick="closeDeleteProgramModal()">Cancel</button>
            <button type="button" class="pf-modal-btn-save" style="background:#c62828;" onmouseover="this.style.background='#a31f1f'" onmouseout="this.style.background='#c62828'" onclick="handleDeleteProgramConfirm()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ── Search filter ── */
document.getElementById('pfSearch').addEventListener('input', function () {
    var filter = this.value.toLowerCase();
    var rows = document.querySelectorAll('#pfTable tbody tr');
    rows.forEach(function (row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
    });
});

/* ── NEW PROGRAM MODAL ── */
function openNewProgramModal() {
    document.getElementById('newProgramForm').reset();
    document.getElementById('newProgramModal').style.display = 'flex';
}
function closeNewProgramModal() {
    document.getElementById('newProgramModal').style.display = 'none';
}
function handleNewProgramSave(e) {
    e.preventDefault();
    var code = document.getElementById('newProgramCode').value.trim();
    var name = document.getElementById('newProgramName').value.trim();
    var dept = document.getElementById('newProgramDept').value;
    var accred = document.getElementById('newProgramAccred').value;
    if (!code || !name || !dept || !accred) {
        showRegistrarToast('Please fill in all fields.', 'warning');
        return false;
    }
    closeNewProgramModal();
    showRegistrarToast('Program "' + code + '" added successfully.', 'success');
    return false;
}

/* ── EDIT PROGRAM MODAL ── */
function openEditProgramModal(id, code, name, dept, accred) {
    document.getElementById('editProgramId').value = id;
    document.getElementById('editProgramCode').value = code;
    document.getElementById('editProgramName').value = name;
    document.getElementById('editProgramDept').value = dept;
    document.getElementById('editProgramAccred').value = accred;
    document.getElementById('editProgramModal').style.display = 'flex';
}
function closeEditProgramModal() {
    document.getElementById('editProgramModal').style.display = 'none';
}
function handleEditProgramSave(e) {
    e.preventDefault();
    var code = document.getElementById('editProgramCode').value.trim();
    if (!code) {
        showRegistrarToast('Please fill in all fields.', 'warning');
        return false;
    }
    closeEditProgramModal();
    showRegistrarToast('Program "' + code + '" updated successfully.', 'success');
    return false;
}

/* ── DELETE PROGRAM MODAL ── */
function openDeleteProgramModal(id, code) {
    document.getElementById('deleteProgramId').value = id;
    document.getElementById('deleteProgramName').textContent = code;
    document.getElementById('deleteProgramModal').style.display = 'flex';
}
function closeDeleteProgramModal() {
    document.getElementById('deleteProgramModal').style.display = 'none';
}
function handleDeleteProgramConfirm() {
    var code = document.getElementById('deleteProgramName').textContent;
    closeDeleteProgramModal();
    showRegistrarToast('Program "' + code + '" deleted successfully.', 'success');
}

/* ── Close modals on overlay click ── */
document.querySelectorAll('.pf-modal-overlay').forEach(function (overlay) {
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.style.display = 'none';
    });
});
</script>
@endpush
