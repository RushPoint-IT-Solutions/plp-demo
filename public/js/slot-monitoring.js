/* ── Slot Monitoring Page ── */

var SLOTS = [
    { id: 1, sy: '2025-2026', semester: 'First', section: 'BSIT 1-A', subject: 'CC101 (3.0)', schedule: 'S | 10:00AM-1:00PM | RM#1', totalSlots: 50, enrolled: 48 },
    { id: 2, sy: '2025-2026', semester: 'First', section: 'BSIT 1-A', subject: 'CC102 (5.0)', schedule: 'M | 1:00PM-3:00PM | RM#5', totalSlots: 35, enrolled: 12 },
    { id: 3, sy: '2025-2026', semester: 'First', section: 'BSIT 1-A', subject: 'CC101 (3.0)', schedule: 'W | 9:00AM-3:00PM | RM#2', totalSlots: 50, enrolled: 50 }
];

function getSlotColor(pct) {
    if (pct >= 90) return '#e53935';
    if (pct >= 50) return '#f9a825';
    return '#006837';
}

function renderSlotTable() {
    var tbody = document.getElementById('smBody');
    var html = '';

    for (var i = 0; i < SLOTS.length; i++) {
        var s = SLOTS[i];
        var pct = s.totalSlots > 0 ? Math.round((s.enrolled / s.totalSlots) * 100) : 0;
        var color = getSlotColor(pct);

        html += '<tr>' +
            '<td>' +
                '<div class="apst-action-btn" onclick="toggleSlotMenu(' + s.id + ', event)">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="smMenu' + s.id + '">' +
                    '<button onclick="openEditSlotModal(' + s.id + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        ' Edit' +
                    '</button>' +
                    '<button class="apst-del-btn" onclick="openDeleteSlotModal(' + s.id + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        ' Delete' +
                    '</button>' +
                '</div>' +
            '</td>' +
            '<td>' + s.sy + '</td>' +
            '<td>' + s.semester + '</td>' +
            '<td>' + s.section + '</td>' +
            '<td>' + s.subject + '</td>' +
            '<td>' + s.schedule + '</td>' +
            '<td style="text-align:center;">' + s.totalSlots + '</td>' +
            '<td style="text-align:center;">' + s.enrolled + '</td>' +
            '<td>' +
                '<div class="slot-status-wrap">' +
                    '<div class="slot-progress-bar">' +
                        '<div class="slot-progress-fill" style="width:' + pct + '%; background:' + color + ';"></div>' +
                    '</div>' +
                    '<span class="slot-pct" style="color:' + color + ';">' + pct + '%</span>' +
                '</div>' +
            '</td>' +
        '</tr>';
    }

    tbody.innerHTML = html;
    document.getElementById('smPageInfo').textContent = 'Showing ' + SLOTS.length + ' slots';
}

/* ── Add Slot Modal ── */
function openAddSlotModal() {
    document.getElementById('addSlotForm').reset();
    document.getElementById('addSlotModal').style.display = 'flex';
}
function closeAddSlotModal() {
    document.getElementById('addSlotModal').style.display = 'none';
}
function handleAddSlotSave(e) {
    e.preventDefault();
    var sy = document.getElementById('addSlotSY').value;
    var semester = document.getElementById('addSlotSemester').value;
    var section = document.getElementById('addSlotSection').value.trim();
    var subject = document.getElementById('addSlotSubject').value.trim();
    var schedule = document.getElementById('addSlotSchedule').value.trim();
    var totalSlots = parseInt(document.getElementById('addSlotTotal').value);
    if (!section || !subject || !schedule || !totalSlots) {
        showRegistrarToast('Please fill in all fields.', 'warning');
        return false;
    }
    var newId = SLOTS.length ? Math.max.apply(null, SLOTS.map(function(s) { return s.id; })) + 1 : 1;
    SLOTS.push({ id: newId, sy: sy, semester: semester, section: section, subject: subject, schedule: schedule, totalSlots: totalSlots, enrolled: 0 });
    closeAddSlotModal();
    renderSlotTable();
    showSlotSuccessModal('Slot added successfully.');
    return false;
}

/* ── Edit Slot Modal ── */
function openEditSlotModal(id) {
    var s = SLOTS.find(function(x) { return x.id === id; });
    if (!s) return;
    document.getElementById('editSlotId').value = s.id;
    document.getElementById('editSlotSection').value = s.section;
    document.getElementById('editSlotSubject').value = s.subject;
    document.getElementById('editSlotSchedule').value = s.schedule;
    document.getElementById('editSlotTotal').value = s.totalSlots;
    document.getElementById('editSlotEnrolled').value = s.enrolled;
    document.getElementById('editSlotModal').style.display = 'flex';
}
function closeEditSlotModal() {
    document.getElementById('editSlotModal').style.display = 'none';
}
function handleEditSlotSave(e) {
    e.preventDefault();
    var id = parseInt(document.getElementById('editSlotId').value);
    var s = SLOTS.find(function(x) { return x.id === id; });
    if (!s) return false;
    s.section = document.getElementById('editSlotSection').value.trim();
    s.subject = document.getElementById('editSlotSubject').value.trim();
    s.schedule = document.getElementById('editSlotSchedule').value.trim();
    s.totalSlots = parseInt(document.getElementById('editSlotTotal').value);
    s.enrolled = parseInt(document.getElementById('editSlotEnrolled').value);
    closeEditSlotModal();
    renderSlotTable();
    showSlotSuccessModal('Slot updated successfully.');
    return false;
}

/* ── Delete Slot Modal ── */
function openDeleteSlotModal(id) {
    var s = SLOTS.find(function(x) { return x.id === id; });
    if (!s) return;
    document.getElementById('deleteSlotId').value = s.id;
    document.getElementById('deleteSlotName').textContent = s.subject + ' — ' + s.section;
    document.getElementById('deleteSlotModal').style.display = 'flex';
}
function closeDeleteSlotModal() {
    document.getElementById('deleteSlotModal').style.display = 'none';
}
function handleDeleteSlotConfirm() {
    var id = parseInt(document.getElementById('deleteSlotId').value);
    SLOTS = SLOTS.filter(function(x) { return x.id !== id; });
    closeDeleteSlotModal();
    renderSlotTable();
    showSlotSuccessModal('Slot deleted successfully.');
}

/* ── Success Modal ── */
function showSlotSuccessModal(msg) {
    document.getElementById('slotSuccessMsg').textContent = msg;
    document.getElementById('slotSuccessModal').style.display = 'flex';
}
function closeSlotSuccessModal() {
    document.getElementById('slotSuccessModal').style.display = 'none';
}

/* ── Escape key closes modals ── */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddSlotModal();
        closeEditSlotModal();
        closeDeleteSlotModal();
        closeSlotSuccessModal();
    }
});

/* ── Action Dropdown Toggle ── */
function toggleSlotMenu(id, e) {
    e.stopPropagation();
    var menu = document.getElementById('smMenu' + id);
    var isOpen = menu.classList.contains('open');
    document.querySelectorAll('#smTable .apst-dropdown').forEach(function(d) { d.classList.remove('open','drop-up'); d.style.top=''; d.style.left=''; d.style.bottom=''; });
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
        document.querySelectorAll('#smTable .apst-dropdown').forEach(function(d) { d.classList.remove('open','drop-up'); d.style.top=''; d.style.left=''; d.style.bottom=''; });
    }
});

window.addEventListener('scroll', function() {
    document.querySelectorAll('#smTable .apst-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
}, true);

/* ── Init ── */
renderSlotTable();
