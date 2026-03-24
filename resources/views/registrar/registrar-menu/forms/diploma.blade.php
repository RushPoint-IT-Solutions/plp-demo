@extends('layouts.registrar')

@section('title', 'PLP - Diploma')
@section('page-title', 'DIPLOMA')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <div class="app-filter-bar">
            <div class="app-filter-row" style="align-items: flex-end;">
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">School Year:</label>
                    <select class="app-filter-select">
                        <option>2025-2026</option>
                        <option>2024-2025</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Semester</label>
                    <select class="app-filter-select">
                        <option>First</option>
                        <option>Second</option>
                        <option>Summer</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:2;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Program</label>
                    <select class="app-filter-select">
                        <option>-Select Program-</option>
                        <option>BSCS</option>
                        <option>BSIT</option>
                        <option>BSED</option>
                        <option>BSBA</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Year Level</label>
                    <select class="app-filter-select">
                        <option>First</option>
                        <option>Second</option>
                        <option>Third</option>
                        <option>Fourth</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:15px;">
                <button type="button" class="req-btn-save" style="min-width: 120px; font-weight: 700;">Set</button>
            </div>
        </div>

        <div class="ga-table-controls" style="margin-bottom: 12px; display:flex; font-size: 0.85rem; font-weight: 600; color: #006837; align-items: center; gap: 8px;">
            <span style="letter-spacing: 0.05em;">SHOW</span>
            <select class="app-filter-select" style="width: auto; padding: 4px 28px 4px 12px; height: 32px; font-size: 0.85rem;">
                <option>10</option>
                <option>25</option>
                <option>50</option>
            </select>
            <span style="letter-spacing: 0.05em;">ENTRIES</span>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table id="diplomaTable" class="ga-table app-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Year</th>
                        <th style="text-align: center; width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody id="diplomaTableBody">
                    <tr data-row-id="1">
                        <td style="text-align: center;">1</td>
                        <td>2122B0104</td>
                        <td>Jhon Mark Samson</td>
                        <td>BSIT</td>
                        <td>Fourth</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-diploma-menu-toggle="diplomaMenu-1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="diplomaMenu-1">
                                <button type="button" onclick="diplomaOpenEdit(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="diplomaOpenDelete(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-row-id="2">
                        <td style="text-align: center;">2</td>
                        <td>2122B0115</td>
                        <td>Mary Ann dela Cruz</td>
                        <td>BSED</td>
                        <td>Fourth</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-diploma-menu-toggle="diplomaMenu-2" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="diplomaMenu-2">
                                <button type="button" onclick="diplomaOpenEdit(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="diplomaOpenDelete(2)">
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
</div>

<div class="req-modal-overlay" id="diplomaEditModal" style="display:none;" onclick="if(event.target===this) diplomaCloseModal('diplomaEditModal')">
    <div class="req-modal-box" style="width: 560px;">
        <h3 class="req-modal-title">EDIT DIPLOMA RECORD</h3>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Number</label>
                <input type="text" id="diplomaEditNumber" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input type="text" id="diplomaEditCourse" class="req-modal-input">
            </div>
        </div>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Name</label>
                <input type="text" id="diplomaEditName" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year</label>
                <input type="text" id="diplomaEditYear" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="diplomaCloseModal('diplomaEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="diplomaSaveEdit()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="diplomaDeleteModal" style="display:none;" onclick="if(event.target===this) diplomaCloseModal('diplomaDeleteModal')">
    <div class="req-modal-box" style="width: 440px;">
        <h3 class="req-modal-title">DELETE RECORD</h3>
        <p style="font-size:0.9rem; color:#4b5563; margin: 8px 0 0; text-align:center;">Are you sure you want to delete this Diploma record?</p>
        <div class="req-modal-actions" style="margin-top:16px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="diplomaCloseModal('diplomaDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#b42318;" onclick="diplomaConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var diplomaCurrentRowId = null;

    function diplomaCloseMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function diplomaToggleMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        diplomaCloseMenus();
        if (isOpen) return;

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

    function diplomaOpenModal(id) {
        diplomaCloseMenus();
        var modal = document.getElementById(id);
        if (modal) modal.style.display = 'flex';
    }

    function diplomaCloseModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    }

    function diplomaGetRow(rowId) {
        return document.querySelector('tr[data-row-id="' + rowId + '"]');
    }

    function diplomaOpenEdit(rowId) {
        var row = diplomaGetRow(rowId);
        if (!row) return;

        diplomaCurrentRowId = rowId;
        var cells = row.querySelectorAll('td');
        document.getElementById('diplomaEditNumber').value = (cells[1] ? cells[1].textContent : '').trim();
        document.getElementById('diplomaEditName').value = (cells[2] ? cells[2].textContent : '').trim();
        document.getElementById('diplomaEditCourse').value = (cells[3] ? cells[3].textContent : '').trim();
        document.getElementById('diplomaEditYear').value = (cells[4] ? cells[4].textContent : '').trim();
        diplomaOpenModal('diplomaEditModal');
    }

    function diplomaSaveEdit() {
        var row = diplomaGetRow(diplomaCurrentRowId);
        if (!row) return;
        var cells = row.querySelectorAll('td');

        if (cells[1]) cells[1].textContent = (document.getElementById('diplomaEditNumber').value || '').trim();
        if (cells[2]) cells[2].textContent = (document.getElementById('diplomaEditName').value || '').trim();
        if (cells[3]) cells[3].textContent = (document.getElementById('diplomaEditCourse').value || '').trim();
        if (cells[4]) cells[4].textContent = (document.getElementById('diplomaEditYear').value || '').trim();

        diplomaCloseModal('diplomaEditModal');
    }

    function diplomaOpenDelete(rowId) {
        diplomaCurrentRowId = rowId;
        diplomaOpenModal('diplomaDeleteModal');
    }

    function diplomaConfirmDelete() {
        var row = diplomaGetRow(diplomaCurrentRowId);
        if (row) row.remove();
        diplomaCloseModal('diplomaDeleteModal');
    }

    document.addEventListener('click', function(event) {
        var toggle = event.target.closest('[data-diploma-menu-toggle]');
        if (toggle) {
            event.stopPropagation();
            diplomaToggleMenu(toggle.getAttribute('data-diploma-menu-toggle'), toggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            diplomaCloseMenus();
        }
    });

    window.addEventListener('scroll', diplomaCloseMenus, true);
</script>
@endpush
