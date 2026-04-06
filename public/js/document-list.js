function closeDoclistActionMenus() {
    document.querySelectorAll('#doclistTable .apst-dropdown.open').forEach(function(menu) {
        menu.classList.remove('open', 'drop-up');
        menu.style.top = '';
        menu.style.left = '';
        menu.style.right = '';
        menu.style.bottom = '';
    });
}

function toggleDoclistActionMenu(menuId, trigger) {
    var menu = document.getElementById(menuId);
    if (!menu || !trigger) {
        return;
    }

    var isOpen = menu.classList.contains('open');
    closeDoclistActionMenus();
    if (isOpen) {
        return;
    }

    var rect = trigger.getBoundingClientRect();
    var spaceBelow = window.innerHeight - rect.bottom;
    menu.style.left = 'auto';
    menu.style.right = (window.innerWidth - rect.left + 4) + 'px';

    if (spaceBelow < 120) {
        menu.classList.add('drop-up');
        menu.style.top = 'auto';
        menu.style.bottom = (window.innerHeight - rect.bottom) + 'px';
    } else {
        menu.style.top = rect.top + 'px';
        menu.style.bottom = 'auto';
    }

    menu.classList.add('open');
}

// ── Add Document Modal ──
function openAddDocModal() {
    closeDoclistActionMenus();
    document.getElementById('addDeptType').value = '';
    document.getElementById('addGradeLevel').value = '';
    document.getElementById('addDocument').value = '';
    document.querySelector('#addDocForm input[name="add_doc_type"][value="Document"]').checked = true;
    var addNonFilipino = document.getElementById('addNonFilipino');
    if (addNonFilipino) {
        addNonFilipino.checked = false;
    }
    document.getElementById('addDocModal').style.display = 'flex';
}

function closeAddDocModal() {
    document.getElementById('addDocModal').style.display = 'none';
}

function handleAddDocSave(e) {
    e.preventDefault();
    var deptType   = document.getElementById('addDeptType').value;
    var gradeLevel = document.getElementById('addGradeLevel').value;
    var doc        = document.getElementById('addDocument').value.trim();
    if (!deptType || !gradeLevel || !doc) {
        showRegistrarToast('Please fill in all required fields.', 'warning');
        return false;
    }
    closeAddDocModal();
    showRegistrarToast('Document added successfully.', 'success');
    return false;
}

// Close add modal on overlay click
document.getElementById('addDocModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddDocModal();
});

// ── Filter & Sort ──
function filterDoclistTable() {
    var query = document.getElementById('doclistSearch').value.toLowerCase();
    var rows  = document.querySelectorAll('.doclist-table tbody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
    closeDoclistActionMenus();
}

function sortDoclistTable() {
    var dir   = document.getElementById('doclistSort').value;
    var tbody = document.querySelector('.doclist-table tbody');
    var rows  = Array.from(tbody.querySelectorAll('tr'));
    rows.sort(function(a, b) {
        var aText = a.cells[3] ? a.cells[3].textContent.trim().toLowerCase() : '';
        var bText = b.cells[3] ? b.cells[3].textContent.trim().toLowerCase() : '';
        var cmp = aText.localeCompare(bText);
        return dir === 'desc' ? -cmp : cmp;
    });
    rows.forEach(function(row) { tbody.appendChild(row); });
    closeDoclistActionMenus();
}

function handleEditSave(e) {
    e.preventDefault();
    var doc = document.getElementById('editDocument').value.trim();
    if (!doc) {
        showRegistrarToast('Please fill in the document name.', 'warning');
        return false;
    }
    closeEditModal();
    showRegistrarToast('Document updated successfully.', 'success');
    return false;
}

function handleDeleteConfirm(e) {
    e.preventDefault();
    closeDeleteModal();
    showRegistrarToast('Document deleted successfully.', 'success');
    return false;
}

// ── Edit Modal ──
function openEditModal(id, dept, grade, doc, type, nonFilipino) {
    closeDoclistActionMenus();
    document.getElementById('editId').value = id;
    document.getElementById('editDeptType').value = dept;
    document.getElementById('editGradeLevel').value = grade;
    document.getElementById('editDocument').value = doc;

    if (type === 'Medical') {
        document.getElementById('editTypeMedical').checked = true;
    } else {
        document.getElementById('editTypeDocument').checked = true;
    }

    var editNonFilipino = document.getElementById('editNonFilipino');
    if (editNonFilipino) {
        editNonFilipino.checked = !!nonFilipino;
    }
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// ── Delete Modal ──
function openDeleteModal(id, docName) {
    closeDoclistActionMenus();
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
        closeDoclistActionMenus();
        closeAddDocModal();
        closeEditModal();
        closeDeleteModal();
    }
});

document.addEventListener('click', function(e) {
    var toggle = e.target.closest('[data-doc-menu-toggle]');
    if (toggle) {
        e.stopPropagation();
        toggleDoclistActionMenu(toggle.getAttribute('data-doc-menu-toggle'), toggle);
        return;
    }

    if (!e.target.closest('#doclistTable .apst-dropdown')) {
        closeDoclistActionMenus();
    }
});

window.addEventListener('scroll', closeDoclistActionMenus, true);
