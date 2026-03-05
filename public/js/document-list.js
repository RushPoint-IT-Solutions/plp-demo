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
