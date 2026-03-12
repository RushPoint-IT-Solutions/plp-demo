/* ── Room File Page ── */

var ROOMS = [
    { id: 1, room: 1, floor: 1, building: 'Campus 3', students: 30, program: 'BSIT', updatedBy: 'Admin 1' },
    { id: 2, room: 2, floor: 2, building: 'Campus 3', students: 20, program: 'BSCS', updatedBy: 'Admin 1' },
    { id: 3, room: 3, floor: 3, building: 'Campus 2', students: 50, program: 'BSED', updatedBy: 'Admin 1' },
    { id: 4, room: 4, floor: 1, building: 'Campus 1', students: 40, program: 'BSAT', updatedBy: 'Admin 1' },
    { id: 5, room: 5, floor: 2, building: 'Campus 3', students: 35, program: 'BSIT-Animation', updatedBy: 'Admin 1' },
    { id: 6, room: 6, floor: 3, building: 'Campus 4', students: 50, program: 'BSN', updatedBy: 'Admin 1' },
    { id: 7, room: 7, floor: 1, building: 'Campus 2', students: 50, program: 'BSET', updatedBy: 'Admin 1' }
];

function renderRoomTable(filter) {
    var tbody = document.getElementById('rfBody');
    var list = ROOMS;
    if (filter) {
        var f = filter.toLowerCase();
        list = ROOMS.filter(function(r) {
            return String(r.room).indexOf(f) > -1 ||
                   r.building.toLowerCase().indexOf(f) > -1 ||
                   r.program.toLowerCase().indexOf(f) > -1;
        });
    }

    var html = '';
    for (var i = 0; i < list.length; i++) {
        var r = list[i];
        html += '<tr>' +
            '<td>' +
                '<div class="apst-action-btn" onclick="toggleRoomMenu(' + r.id + ', event)">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="rfMenu' + r.id + '">' +
                    '<button onclick="openEditRoomModal(' + r.id + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        ' Edit' +
                    '</button>' +
                    '<button class="apst-del-btn" onclick="openDeleteRoomModal(' + r.id + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        ' Delete' +
                    '</button>' +
                '</div>' +
            '</td>' +
            '<td>' + r.room + '</td>' +
            '<td>' + r.floor + '</td>' +
            '<td>' + r.building + '</td>' +
            '<td>' + r.students + '</td>' +
            '<td>' + r.program + '</td>' +
            '<td>' + r.updatedBy + '</td>' +
        '</tr>';
    }

    tbody.innerHTML = html;
    document.getElementById('rfPageInfo').textContent = 'Showing ' + list.length + ' of ' + ROOMS.length + ' rooms';
}

/* ── Search ── */
document.getElementById('rfSearch').addEventListener('input', function() {
    renderRoomTable(this.value.trim());
});

/* ── New Room Modal ── */
function openNewRoomModal() {
    document.getElementById('newRoomForm').reset();
    document.getElementById('newRoomModal').style.display = 'flex';
}
function closeNewRoomModal() {
    document.getElementById('newRoomModal').style.display = 'none';
}
function handleNewRoomSave(e) {
    e.preventDefault();
    var room = document.getElementById('newRoomNumber').value.trim();
    var floor = document.getElementById('newRoomFloor').value.trim();
    var building = document.getElementById('newRoomBuilding').value;
    var students = document.getElementById('newRoomStudents').value.trim();
    var program = document.getElementById('newRoomProgram').value;
    if (!room || !floor || !building || !students || !program) {
        showRegistrarToast('Please fill in all fields.', 'warning');
        return false;
    }
    var newId = ROOMS.length ? Math.max.apply(null, ROOMS.map(function(r) { return r.id; })) + 1 : 1;
    ROOMS.push({ id: newId, room: parseInt(room), floor: parseInt(floor), building: building, students: parseInt(students), program: program, updatedBy: 'Admin 1' });
    closeNewRoomModal();
    renderRoomTable();
    showRoomSuccessModal('Room #' + room + ' added successfully.');
    return false;
}

/* ── Edit Room Modal ── */
function openEditRoomModal(id) {
    var r = ROOMS.find(function(x) { return x.id === id; });
    if (!r) return;
    document.getElementById('editRoomId').value = r.id;
    document.getElementById('editRoomNumber').value = r.room;
    document.getElementById('editRoomFloor').value = r.floor;
    document.getElementById('editRoomBuilding').value = r.building;
    document.getElementById('editRoomStudents').value = r.students;
    document.getElementById('editRoomProgram').value = r.program;
    document.getElementById('editRoomModal').style.display = 'flex';
}
function closeEditRoomModal() {
    document.getElementById('editRoomModal').style.display = 'none';
}
function handleEditRoomSave(e) {
    e.preventDefault();
    var id = parseInt(document.getElementById('editRoomId').value);
    var r = ROOMS.find(function(x) { return x.id === id; });
    if (!r) return false;
    r.room = parseInt(document.getElementById('editRoomNumber').value);
    r.floor = parseInt(document.getElementById('editRoomFloor').value);
    r.building = document.getElementById('editRoomBuilding').value;
    r.students = parseInt(document.getElementById('editRoomStudents').value);
    r.program = document.getElementById('editRoomProgram').value;
    closeEditRoomModal();
    renderRoomTable();
    showRoomSuccessModal('Room #' + r.room + ' updated successfully.');
    return false;
}

/* ── Delete Room Modal ── */
function openDeleteRoomModal(id) {
    var r = ROOMS.find(function(x) { return x.id === id; });
    if (!r) return;
    document.getElementById('deleteRoomId').value = r.id;
    document.getElementById('deleteRoomName').textContent = 'Room #' + r.room + ' — ' + r.building;
    document.getElementById('deleteRoomModal').style.display = 'flex';
}
function closeDeleteRoomModal() {
    document.getElementById('deleteRoomModal').style.display = 'none';
}
function handleDeleteRoomConfirm() {
    var id = parseInt(document.getElementById('deleteRoomId').value);
    ROOMS = ROOMS.filter(function(x) { return x.id !== id; });
    closeDeleteRoomModal();
    renderRoomTable();
    showRoomSuccessModal('Room deleted successfully.');
}

/* ── Success Modal ── */
function showRoomSuccessModal(msg) {
    document.getElementById('roomSuccessMsg').textContent = msg;
    document.getElementById('roomSuccessModal').style.display = 'flex';
}
function closeRoomSuccessModal() {
    document.getElementById('roomSuccessModal').style.display = 'none';
}

/* ── Escape key closes modals ── */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewRoomModal();
        closeEditRoomModal();
        closeDeleteRoomModal();
        closeRoomSuccessModal();
    }
});

/* ── Action Dropdown Toggle ── */
function toggleRoomMenu(id, e) {
    e.stopPropagation();
    var menu = document.getElementById('rfMenu' + id);
    var isOpen = menu.classList.contains('open');
    document.querySelectorAll('#rfTable .apst-dropdown').forEach(function(d) { d.classList.remove('open','drop-up'); d.style.top=''; d.style.left=''; d.style.bottom=''; });
    if (!isOpen) {
        var btn = menu.parentElement.querySelector('.apst-action-btn');
        var rect = btn.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;
        menu.style.left = (rect.right + 4) + 'px';
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
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.apst-action-btn') && !e.target.closest('.apst-dropdown')) {
        document.querySelectorAll('#rfTable .apst-dropdown').forEach(function(d) { d.classList.remove('open','drop-up'); d.style.top=''; d.style.left=''; d.style.bottom=''; });
    }
});

window.addEventListener('scroll', function() {
    document.querySelectorAll('#rfTable .apst-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
}, true);

/* ── Init ── */
renderRoomTable();
