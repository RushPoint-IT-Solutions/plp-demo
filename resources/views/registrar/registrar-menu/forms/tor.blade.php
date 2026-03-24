@extends('layouts.registrar')

@section('title', 'PLP - TOR')
@section('page-title', 'TOR')

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
            <table id="torTable" class="ga-table app-table" style="min-width: 900px;">
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
                <tbody id="torTableBody">
                    <tr data-row-id="1">
                        <td style="text-align: center;">1</td>
                        <td>2223A8137</td>
                        <td>Mark Jay Bares</td>
                        <td>BSCS</td>
                        <td>Fourth</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-tor-menu-toggle="torMenu-1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="torMenu-1">
                                <button type="button" onclick="torOpenEdit(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="torOpenDelete(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-row-id="2">
                        <td style="text-align: center;">2</td>
                        <td>2223A8139</td>
                        <td>Andrea Jane Austero</td>
                        <td>BSCS</td>
                        <td>Fourth</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-tor-menu-toggle="torMenu-2" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="torMenu-2">
                                <button type="button" onclick="torOpenEdit(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="torOpenDelete(2)">
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

<div class="req-modal-overlay" id="torEditModal" style="display:none;" onclick="if(event.target===this) torCloseModal('torEditModal')">
    <div class="req-modal-box" style="width: 560px;">
        <h3 class="req-modal-title">EDIT TOR RECORD</h3>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Number</label>
                <input type="text" id="torEditNumber" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input type="text" id="torEditCourse" class="req-modal-input">
            </div>
        </div>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Name</label>
                <input type="text" id="torEditName" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year</label>
                <input type="text" id="torEditYear" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="torCloseModal('torEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="torSaveEdit()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="torDeleteModal" style="display:none;" onclick="if(event.target===this) torCloseModal('torDeleteModal')">
    <div class="req-modal-box" style="width: 440px;">
        <h3 class="req-modal-title">DELETE RECORD</h3>
        <p style="font-size:0.9rem; color:#4b5563; margin: 8px 0 0; text-align:center;">Are you sure you want to delete this TOR record?</p>
        <div class="req-modal-actions" style="margin-top:16px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="torCloseModal('torDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#b42318;" onclick="torConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var torCurrentRowId = null;

    function torCloseMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function torToggleMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        torCloseMenus();
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

    function torOpenModal(id) {
        torCloseMenus();
        var modal = document.getElementById(id);
        if (modal) modal.style.display = 'flex';
    }

    function torCloseModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    }

    function torGetRow(rowId) {
        return document.querySelector('tr[data-row-id="' + rowId + '"]');
    }

    function torOpenEdit(rowId) {
        var row = torGetRow(rowId);
        if (!row) return;

        torCurrentRowId = rowId;
        var cells = row.querySelectorAll('td');
        document.getElementById('torEditNumber').value = (cells[1] ? cells[1].textContent : '').trim();
        document.getElementById('torEditName').value = (cells[2] ? cells[2].textContent : '').trim();
        document.getElementById('torEditCourse').value = (cells[3] ? cells[3].textContent : '').trim();
        document.getElementById('torEditYear').value = (cells[4] ? cells[4].textContent : '').trim();
        torOpenModal('torEditModal');
    }

    function torSaveEdit() {
        var row = torGetRow(torCurrentRowId);
        if (!row) return;
        var cells = row.querySelectorAll('td');

        if (cells[1]) cells[1].textContent = (document.getElementById('torEditNumber').value || '').trim();
        if (cells[2]) cells[2].textContent = (document.getElementById('torEditName').value || '').trim();
        if (cells[3]) cells[3].textContent = (document.getElementById('torEditCourse').value || '').trim();
        if (cells[4]) cells[4].textContent = (document.getElementById('torEditYear').value || '').trim();

        torCloseModal('torEditModal');
    }

    function torOpenDelete(rowId) {
        torCurrentRowId = rowId;
        torOpenModal('torDeleteModal');
    }

    function torConfirmDelete() {
        var row = torGetRow(torCurrentRowId);
        if (row) row.remove();
        torCloseModal('torDeleteModal');
    }

    document.addEventListener('click', function(event) {
        var toggle = event.target.closest('[data-tor-menu-toggle]');
        if (toggle) {
            event.stopPropagation();
            torToggleMenu(toggle.getAttribute('data-tor-menu-toggle'), toggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            torCloseMenus();
        }
    });

    window.addEventListener('scroll', torCloseMenus, true);
</script>
@endpush
