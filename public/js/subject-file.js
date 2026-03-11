/* ── Subject File JS ── */

var SUBJECTS = [
    { code: 'CP 126',  title: 'Capstone Project',                       lec: 2.0, lab: 3.0, core: false, applied: true,  specialized: false },
    { code: 'LAWR 19', title: 'Life And Works Of Rizal',                 lec: 3.0, lab: 0.0, core: true,  applied: false, specialized: false },
    { code: 'OOP 111', title: 'Object-Oriented Programming',            lec: 2.0, lab: 3.0, core: false, applied: false, specialized: true  },
    { code: 'SPI 128', title: 'Social and Professional Issues',         lec: 2.0, lab: 3.0, core: false, applied: true,  specialized: false },
    { code: 'SAM 125', title: 'System Administration and Maintenance',  lec: 2.0, lab: 3.0, core: false, applied: false, specialized: true  },
    { code: 'UTS 12',  title: 'Understanding The Self',                 lec: 3.0, lab: 0.0, core: true,  applied: false, specialized: false },
];

var editingIdx = -1;
var deletingIdx = -1;

function yn(v) { return v ? 'Y' : 'N'; }

function renderTable(filter) {
    var tbody = document.getElementById('sfTableBody');
    tbody.innerHTML = '';
    var count = 0;
    SUBJECTS.forEach(function (s, idx) {
        if (filter) {
            var f = filter.toLowerCase();
            if (s.code.toLowerCase().indexOf(f) === -1 &&
                s.title.toLowerCase().indexOf(f) === -1) return;
        }
        count++;
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td>' +
                '<div class="apst-action-btn" onclick="toggleSubjectMenu(' + idx + ', event)">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="sfMenu' + idx + '">' +
                    '<button onclick="openEditSubjectModal(' + idx + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        ' Edit' +
                    '</button>' +
                    '<button class="apst-del-btn" onclick="openDeleteSubjectModal(' + idx + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        ' Delete' +
                    '</button>' +
                '</div>' +
            '</td>' +
            '<td>' + count + '</td>' +
            '<td>' + s.code + '</td>' +
            '<td style="text-align:left;">' + s.title + '</td>' +
            '<td style="text-align:center;">' + s.lec.toFixed(1) + '</td>' +
            '<td style="text-align:center;">' + s.lab.toFixed(1) + '</td>' +
            '<td style="text-align:center;">' + yn(s.core) + '</td>' +
            '<td style="text-align:center;">' + yn(s.applied) + '</td>' +
            '<td style="text-align:center;">' + yn(s.specialized) + '</td>';
        tbody.appendChild(tr);
    });
    document.getElementById('sfTotal').textContent = 'Total Subjects: ' + count;
}

function filterSubjects() {
    renderTable(document.getElementById('sfSearchInput').value);
}

function sortSubjects() {
    var dir = document.getElementById('sfSort').value;
    SUBJECTS.sort(function (a, b) {
        var cmp = a.code.localeCompare(b.code);
        return dir === 'desc' ? -cmp : cmp;
    });
    renderTable(document.getElementById('sfSearchInput').value);
}

/* ── Dropdown menu ── */
document.addEventListener('click', function (e) {
    if (!e.target.closest('.apst-action-btn') && !e.target.closest('.apst-dropdown')) {
        document.querySelectorAll('.apst-dropdown').forEach(function (d) { d.classList.remove('open'); d.classList.remove('drop-up'); d.style.top = ''; d.style.left = ''; d.style.bottom = ''; });
    }
});

/* Close dropdown on scroll so the fixed-position menu doesn't float away */
window.addEventListener('scroll', function () {
    document.querySelectorAll('.apst-dropdown.open').forEach(function (d) { d.classList.remove('open'); d.classList.remove('drop-up'); d.style.top = ''; d.style.left = ''; d.style.bottom = ''; });
}, true);

function toggleSubjectMenu(idx, e) {
    e.stopPropagation();
    var menu = document.getElementById('sfMenu' + idx);
    var isOpen = menu.classList.contains('open');
    document.querySelectorAll('.apst-dropdown').forEach(function (d) { d.classList.remove('open'); d.classList.remove('drop-up'); d.style.top = ''; d.style.left = ''; d.style.bottom = ''; });
    if (!isOpen) {
        var btn = menu.parentElement.querySelector('.apst-action-btn');
        var rect = btn.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;
        /* Position using fixed coords relative to viewport */
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

/* ── New Subject Modal ── */
function openNewSubjectModal() {
    editingIdx = -1;
    document.getElementById('sfModalTitle').textContent = 'NEW SUBJECT';
    document.getElementById('sfInputCode').value = '';
    document.getElementById('sfInputTitle').value = '';
    document.getElementById('sfInputLec').value = '';
    document.getElementById('sfInputLab').value = '';
    document.getElementById('sfInputCore').checked = false;
    document.getElementById('sfInputApplied').checked = false;
    document.getElementById('sfInputSpecialized').checked = false;
    document.getElementById('sfModal').style.display = 'flex';
}

/* ── Edit Subject Modal ── */
function openEditSubjectModal(idx) {
    document.querySelectorAll('.apst-dropdown').forEach(function (d) { d.classList.remove('open'); });
    editingIdx = idx;
    var s = SUBJECTS[idx];
    document.getElementById('sfModalTitle').textContent = 'EDIT SUBJECT';
    document.getElementById('sfInputCode').value = s.code;
    document.getElementById('sfInputTitle').value = s.title;
    document.getElementById('sfInputLec').value = s.lec;
    document.getElementById('sfInputLab').value = s.lab;
    document.getElementById('sfInputCore').checked = s.core;
    document.getElementById('sfInputApplied').checked = s.applied;
    document.getElementById('sfInputSpecialized').checked = s.specialized;
    document.getElementById('sfModal').style.display = 'flex';
}

function closeSfModal(e) {
    if (!e || e.target.id === 'sfModal') {
        document.getElementById('sfModal').style.display = 'none';
    }
}

function saveSubject() {
    var code  = document.getElementById('sfInputCode').value.trim();
    var title = document.getElementById('sfInputTitle').value.trim();
    var lec   = parseFloat(document.getElementById('sfInputLec').value) || 0;
    var lab   = parseFloat(document.getElementById('sfInputLab').value) || 0;
    var core  = document.getElementById('sfInputCore').checked;
    var applied = document.getElementById('sfInputApplied').checked;
    var specialized = document.getElementById('sfInputSpecialized').checked;

    if (!code || !title) {
        showRegistrarToast('Subject Code and Title are required.', 'warning');
        return;
    }

    var obj = { code: code, title: title, lec: lec, lab: lab, core: core, applied: applied, specialized: specialized };

    if (editingIdx === -1) {
        SUBJECTS.push(obj);
    } else {
        SUBJECTS[editingIdx] = obj;
    }

    document.getElementById('sfModal').style.display = 'none';
    var msg = editingIdx === -1
        ? 'Subject "' + code + '" added successfully.'
        : 'Subject "' + code + '" updated successfully.';
    document.getElementById('sfSuccessMsg').textContent = msg;
    document.getElementById('sfSuccessModal').style.display = 'flex';
    renderTable(document.getElementById('sfSearchInput').value);
}

/* ── Delete Modal ── */
function openDeleteSubjectModal(idx) {
    document.querySelectorAll('.apst-dropdown').forEach(function (d) { d.classList.remove('open'); });
    deletingIdx = idx;
    document.getElementById('sfDeleteName').textContent = SUBJECTS[idx].code + ' — ' + SUBJECTS[idx].title;
    document.getElementById('sfDeleteModal').style.display = 'flex';
}

function closeSfDeleteModal(e) {
    if (!e || e.target.id === 'sfDeleteModal') {
        document.getElementById('sfDeleteModal').style.display = 'none';
    }
}

function confirmDeleteSubject() {
    if (deletingIdx > -1) {
        var code = SUBJECTS[deletingIdx].code;
        SUBJECTS.splice(deletingIdx, 1);
        deletingIdx = -1;
        document.getElementById('sfDeleteModal').style.display = 'none';
        document.getElementById('sfSuccessMsg').textContent = 'Subject "' + code + '" deleted successfully.';
        document.getElementById('sfSuccessModal').style.display = 'flex';
        renderTable(document.getElementById('sfSearchInput').value);
    }
}

/* ── Success Modal ── */
function closeSfSuccess(e) {
    if (!e || e.target.id === 'sfSuccessModal') {
        document.getElementById('sfSuccessModal').style.display = 'none';
    }
}

/* ── Initial render ── */
renderTable();
