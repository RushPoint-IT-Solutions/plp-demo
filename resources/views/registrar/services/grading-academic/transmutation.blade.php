@extends('layouts.registrar')

@section('title', 'PLP - Transmutation')
@section('page-title', 'TRANSMUTATION')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <div class="ga-toolbar ga-toolbar-end">
            <button type="button" class="pf-btn-new ga-btn ga-btn-primary" data-ga-modal-open="gaTransmutationNewModal">+ Add Transmutation</button>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table class="ga-table ga-table-compact app-table ga-trans-table" id="tmTable">
                    <colgroup>
                        <col class="ga-trans-col-sy">
                        <col class="ga-trans-col-term">
                        <col class="ga-trans-col-program">
                        <col class="ga-trans-col-initial">
                        <col class="ga-trans-col-initial">
                        <col class="ga-trans-col-grade">
                        <col class="ga-trans-col-code">
                        <col class="ga-trans-col-remarks">
                        <col class="ga-trans-col-action">
                    </colgroup>
                    <thead>
                        <tr class="ga-trans-head-top">
                            <th rowspan="2" style="text-align: center;">SY</th>
                            <th rowspan="2" style="text-align: center;">Term</th>
                            <th rowspan="2" style="text-align: center;">Program</th>
                            <th colspan="2" style="text-align: center;">Initial Grade</th>
                            <th rowspan="2" style="text-align: center;">Transmuted Grade</th>
                            <th rowspan="2" style="text-align: center;">Code</th>
                            <th rowspan="2" style="text-align: left;">Remarks</th>
                            <th rowspan="2" style="text-align: center;">Action</th>
                        </tr>
                        <tr class="ga-trans-head-sub">
                            <th style="text-align: center;">From</th>
                            <th style="text-align: center;">To</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025-2026</td><td>First</td><td>BSIT</td><td><span class="ga-trans-chip">75.00</span></td><td><span class="ga-trans-chip">79.99</span></td><td><span class="ga-trans-chip">3.00</span></td><td>P</td><td class="ga-state-pass">Passed</td>
                            <td>
                                <div class="apst-action-btn" data-tm-menu-toggle="tmMenu0" aria-label="Open row actions" title="Actions">
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="apst-dropdown" id="tmMenu0">
                                    <button type="button" data-ga-open-action="edit" data-ga-item="BSIT 75-79.99" data-ga-row="0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="BSIT 75-79.99" data-ga-row="0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>2025-2026</td><td>First</td><td>BSIT</td><td><span class="ga-trans-chip">80.00</span></td><td><span class="ga-trans-chip">84.99</span></td><td><span class="ga-trans-chip">2.50</span></td><td>P</td><td class="ga-state-pass">Passed</td>
                            <td>
                                <div class="apst-action-btn" data-tm-menu-toggle="tmMenu1" aria-label="Open row actions" title="Actions">
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="apst-dropdown" id="tmMenu1">
                                    <button type="button" data-ga-open-action="edit" data-ga-item="BSIT 80-84.99" data-ga-row="1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="BSIT 80-84.99" data-ga-row="1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>2025-2026</td><td>First</td><td>BSIT</td><td><span class="ga-trans-chip">74.99</span></td><td><span class="ga-trans-chip">0.00</span></td><td><span class="ga-trans-chip">5.00</span></td><td>F</td><td class="ga-state-fail">Failed</td>
                            <td>
                                <div class="apst-action-btn" data-tm-menu-toggle="tmMenu2" aria-label="Open row actions" title="Actions">
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="apst-dropdown" id="tmMenu2">
                                    <button type="button" data-ga-open-action="edit" data-ga-item="BSIT below 75" data-ga-row="2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="BSIT below 75" data-ga-row="2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
            </table>
        </div>

        <div class="req-modal-overlay" id="gaTransmutationNewModal" style="display:none;">
            <div class="req-modal-box" style="max-width:620px;">
                <h3 class="req-modal-title" id="gaTransmutationNewTitle">ADD TRANSMUTATION RULE</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group"><label class="req-modal-label">SY</label><input class="req-modal-input" id="tmNewSy" placeholder="2025-2026"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">TERM</label><input class="req-modal-input" id="tmNewTerm" placeholder="First"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">PROGRAM</label><input class="req-modal-input" id="tmNewProgram" placeholder="BSIT"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">CODE</label><input class="req-modal-input" id="tmNewCode" placeholder="P"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">INITIAL GRADE FROM</label><input class="req-modal-input" id="tmNewFrom" placeholder="75.00"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">TO</label><input class="req-modal-input" id="tmNewTo" placeholder="79.99"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">TRANSMUTED GRADE</label><input class="req-modal-input" id="tmNewGrade" placeholder="3.00"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">REMARKS</label><input class="req-modal-input" id="tmNewRemarks" placeholder="Passed"></div>
                </div>
                <div class="req-modal-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-tm-save-new>Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaTransmutationActionModal" style="display:none;">
            <div class="req-modal-box" style="max-width:620px;">
                <h3 class="req-modal-title" id="gaTransmutationActionTitle">EDIT TRANSMUTATION RULE</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group"><label class="req-modal-label">SY</label><input class="req-modal-input" id="tmEditSy"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">TERM</label><input class="req-modal-input" id="tmEditTerm"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">PROGRAM</label><input class="req-modal-input" id="tmEditProgram"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">CODE</label><input class="req-modal-input" id="tmEditCode"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">INITIAL GRADE FROM</label><input class="req-modal-input" id="tmEditFrom"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">TO</label><input class="req-modal-input" id="tmEditTo"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">TRANSMUTED GRADE</label><input class="req-modal-input" id="tmEditGrade"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">REMARKS</label><input class="req-modal-input" id="tmEditRemarks"></div>
                </div>
                <div class="req-modal-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-ga-confirm-action>Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaTransmutationDeleteModal" style="display:none;">
            <div class="req-modal-box req-modal-success" style="min-width:300px;">
                <h3 class="req-modal-title" style="color:#c0392b;" id="gaTransmutationDeleteTitle">DELETE TRANSMUTATION RULE</h3>
                <p id="gaTransmutationDeleteText" style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;">
                    Are you sure you want to delete this transmutation rule?
                </p>
                <div class="req-modal-actions" style="justify-content:center;">
                    <button class="req-btn-cancel" type="button" data-ga-close-delete>Cancel</button>
                    <button class="req-btn-save" type="button" style="background:#c0392b;" data-ga-confirm-delete>Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.querySelector('.ga-page');
    if (!page) return;

    var table = document.getElementById('tmTable');
    var actionModal = document.getElementById('gaTransmutationActionModal');
    var deleteModal = document.getElementById('gaTransmutationDeleteModal');
    var actionTitle = document.getElementById('gaTransmutationActionTitle');
    var deleteText = document.getElementById('gaTransmutationDeleteText');
    var nextMenuIndex = page.querySelectorAll('[data-tm-menu-toggle]').length;
    var activeRow = null;
    var activeAction = 'edit';

    var tmNewSy = document.getElementById('tmNewSy');
    var tmNewTerm = document.getElementById('tmNewTerm');
    var tmNewProgram = document.getElementById('tmNewProgram');
    var tmNewFrom = document.getElementById('tmNewFrom');
    var tmNewTo = document.getElementById('tmNewTo');
    var tmNewGrade = document.getElementById('tmNewGrade');
    var tmNewCode = document.getElementById('tmNewCode');
    var tmNewRemarks = document.getElementById('tmNewRemarks');

    var tmEditSy = document.getElementById('tmEditSy');
    var tmEditTerm = document.getElementById('tmEditTerm');
    var tmEditProgram = document.getElementById('tmEditProgram');
    var tmEditFrom = document.getElementById('tmEditFrom');
    var tmEditTo = document.getElementById('tmEditTo');
    var tmEditGrade = document.getElementById('tmEditGrade');
    var tmEditCode = document.getElementById('tmEditCode');
    var tmEditRemarks = document.getElementById('tmEditRemarks');

    function cleanNumber(value) {
        return (value || '').trim();
    }

    function setChip(cell, value) {
        cell.innerHTML = '<span class="ga-trans-chip">' + value + '</span>';
    }

    function fillEditForm(row) {
        if (!row || row.cells.length < 9) return;
        tmEditSy.value = (row.cells[0].textContent || '').trim();
        tmEditTerm.value = (row.cells[1].textContent || '').trim();
        tmEditProgram.value = (row.cells[2].textContent || '').trim();
        tmEditFrom.value = (row.cells[3].textContent || '').trim();
        tmEditTo.value = (row.cells[4].textContent || '').trim();
        tmEditGrade.value = (row.cells[5].textContent || '').trim();
        tmEditCode.value = (row.cells[6].textContent || '').trim();
        tmEditRemarks.value = (row.cells[7].textContent || '').trim();
    }

    function closeActionMenus() {
        page.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
            menu.classList.remove('open');
            menu.classList.remove('drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.bottom = '';
        });
    }

    function toggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;
        var isOpen = menu.classList.contains('open');
        closeActionMenus();
        if (isOpen) return;

        var rect = trigger.getBoundingClientRect();
        var menuWidth = menu.offsetWidth || 120;
        var spacing = 6;
        var spaceBelow = window.innerHeight - rect.bottom;

        // Prefer right side, fallback to left side, and clamp to viewport.
        var left = rect.right + spacing;
        if (left + menuWidth > window.innerWidth - spacing) {
            left = rect.left - menuWidth - spacing;
        }
        if (left < spacing) {
            left = spacing;
        }
        menu.style.left = left + 'px';
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

    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = 'flex';
        document.body.classList.add('ga-modal-open');
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.style.display = 'none';
        if (!Array.prototype.some.call(page.querySelectorAll('.req-modal-overlay'), function (item) {
            return item.style.display === 'flex';
        })) {
            document.body.classList.remove('ga-modal-open');
        }
    }

    page.addEventListener('click', function (event) {
        var menuToggle = event.target.closest('[data-tm-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            toggleActionMenu(menuToggle.getAttribute('data-tm-menu-toggle'), menuToggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }

        var openBtn = event.target.closest('[data-ga-modal-open]');
        if (openBtn) {
            openModal(openBtn.getAttribute('data-ga-modal-open'));
            return;
        }

        var actionBtn = event.target.closest('[data-ga-open-action]');
        if (actionBtn) {
            closeActionMenus();
            var action = actionBtn.getAttribute('data-ga-open-action') || 'edit';
            var item = actionBtn.getAttribute('data-ga-item') || 'rule';
            activeRow = actionBtn.closest('tr');
            activeAction = action;

            if (action === 'delete' || action === 'archive') {
                if (deleteText) {
                    deleteText.textContent = 'Are you sure you want to delete transmutation rule for ' + item + '?';
                }
                openModal('gaTransmutationDeleteModal');
                return;
            }

            actionTitle.textContent = 'EDIT TRANSMUTATION RULE';
            fillEditForm(activeRow);
            openModal('gaTransmutationActionModal');
            return;
        }

        if (event.target.matches('[data-ga-close]')) {
            closeModal(event.target.closest('.req-modal-overlay'));
            return;
        }

        if (event.target.matches('[data-ga-close-delete]')) {
            closeModal(deleteModal);
            return;
        }

        if (event.target.matches('[data-tm-save-new]')) {
            var sy = (tmNewSy.value || '').trim();
            var term = (tmNewTerm.value || '').trim();
            var program = (tmNewProgram.value || '').trim();
            var from = cleanNumber(tmNewFrom.value);
            var to = cleanNumber(tmNewTo.value);
            var grade = cleanNumber(tmNewGrade.value);
            var code = (tmNewCode.value || '').trim();
            var remarks = (tmNewRemarks.value || '').trim();

            if (!sy || !term || !program || !from || !to || !grade || !code || !remarks) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Please fill in all transmutation fields.');
                }
                return;
            }

            var menuId = 'tmMenu' + nextMenuIndex;
            nextMenuIndex += 1;
            var tr = document.createElement('tr');
            tr.innerHTML = '' +
                '<td>' + sy + '</td>' +
                '<td>' + term + '</td>' +
                '<td>' + program + '</td>' +
                '<td><span class="ga-trans-chip">' + from + '</span></td>' +
                '<td><span class="ga-trans-chip">' + to + '</span></td>' +
                '<td><span class="ga-trans-chip">' + grade + '</span></td>' +
                '<td>' + code + '</td>' +
                '<td class="' + ((remarks.toLowerCase().indexOf('fail') !== -1 || code.toUpperCase() === 'F') ? 'ga-state-fail' : 'ga-state-pass') + '">' + remarks + '</td>' +
                '<td>' +
                    '<div class="apst-action-btn" data-tm-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
                    '<div class="apst-dropdown" id="' + menuId + '">' +
                        '<button type="button" data-ga-open-action="edit" data-ga-item="' + program + ' ' + from + '-' + to + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>' +
                        '<button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="' + program + ' ' + from + '-' + to + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>Delete</button>' +
                    '</div>' +
                '</td>';

            table.querySelector('tbody').appendChild(tr);
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Transmutation rule added successfully.');
            }
            closeModal(document.getElementById('gaTransmutationNewModal'));
            return;
        }

        if (event.target.matches('[data-ga-confirm-action]')) {
            if (activeAction === 'edit' && activeRow && activeRow.cells.length >= 9) {
                activeRow.cells[0].textContent = (tmEditSy.value || '').trim();
                activeRow.cells[1].textContent = (tmEditTerm.value || '').trim();
                activeRow.cells[2].textContent = (tmEditProgram.value || '').trim();
                setChip(activeRow.cells[3], cleanNumber(tmEditFrom.value));
                setChip(activeRow.cells[4], cleanNumber(tmEditTo.value));
                setChip(activeRow.cells[5], cleanNumber(tmEditGrade.value));
                activeRow.cells[6].textContent = (tmEditCode.value || '').trim();
                var remarks = (tmEditRemarks.value || '').trim();
                activeRow.cells[7].textContent = remarks;
                activeRow.cells[7].className = (remarks.toLowerCase().indexOf('fail') !== -1 || (tmEditCode.value || '').trim().toUpperCase() === 'F') ? 'ga-state-fail' : 'ga-state-pass';
            }

            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Transmutation action completed.');
            }
            closeModal(actionModal);
            return;
        }

        if (event.target.matches('[data-ga-confirm-delete]')) {
            if (activeRow) {
                activeRow.remove();
            }
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Transmutation rule deleted successfully.');
            }
            closeModal(deleteModal);
            return;
        }
    });

    page.querySelectorAll('.req-modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeModal(overlay);
            }
        });
    });

    window.addEventListener('scroll', closeActionMenus, true);
    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-tm-menu-toggle]') && !event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });
});
</script>
@endpush
