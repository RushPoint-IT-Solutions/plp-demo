@extends('layouts.registrar')

@section('title', 'PLP - Grading System')
@section('page-title', 'GRADING SYSTEM')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <div class="apst-topbar">
            <div class="apst-search-box">
                <input type="text" class="apst-search-input" placeholder="Search grade code or remarks" id="gsSearchInput">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="apst-search-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <button type="button" class="apst-new-btn" data-ga-modal-open="gaNewPeriodModal">+ New Grading Period</button>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table class="ga-table app-table" id="gsTable">
                <thead>
                    <tr>
                        <th style="text-align:center;">Action</th>
                        <th>Grade Code</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gradeRules as $index => $rule)
                    <tr data-grade-rule-id="{{ $rule->id }}">
                        <td>
                            <div class="apst-action-btn" data-gs-menu-toggle="gsMenu{{ $index }}" aria-label="Open row actions" title="Actions">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="apst-dropdown" id="gsMenu{{ $index }}">
                                <button type="button" data-ga-open-action="edit" data-ga-item="{{ $rule->code }}" data-ga-id="{{ $rule->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="{{ $rule->code }}" data-ga-id="{{ $rule->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                        <td>{{ $rule->code }}</td>
                        <td>{{ $rule->grade ?: 'N/A' }}</td>
                        <td>{{ $rule->remarks }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No grading rules found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="req-modal-overlay" id="gaNewPeriodModal" style="display:none;">
            <div class="req-modal-box" style="max-width:620px;">
                <h3 class="req-modal-title" id="gaNewPeriodTitle">ADD GRADING PERIOD</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">GRADE CODE</label>
                        <input type="text" class="req-modal-input" id="gaNewGradeCode" placeholder="Code">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">GRADE</label>
                        <input type="text" class="req-modal-input" id="gaNewGradeValue" placeholder="Grade">
                    </div>
                </div>
                <div class="req-modal-field-group" style="margin-top:12px;">
                    <label class="req-modal-label">REMARKS</label>
                    <textarea class="req-modal-input" id="gaNewGradeRemarks" rows="3" placeholder="Remarks" style="resize:vertical; font-size:0.85rem; padding:8px 10px;"></textarea>
                </div>
                <div class="req-modal-field-group" style="margin-top:12px;">
                    <label class="req-modal-label">INCLUDE IN PERIODS</label>
                    <div class="ga-choice-group ga-choice-group-periods">
                        <label class="ga-check"><input type="checkbox" class="ga-new-period-check" value="Prelim" checked> Prelim</label>
                        <label class="ga-check"><input type="checkbox" class="ga-new-period-check" value="Midterm" checked> Midterm</label>
                        <label class="ga-check"><input type="checkbox" class="ga-new-period-check" value="Pre-Final" checked> Pre-Final</label>
                        <label class="ga-check"><input type="checkbox" class="ga-new-period-check" value="Finals" checked> Finals</label>
                    </div>
                </div>
                <div class="req-modal-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-ga-save>Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaActionModal" style="display:none;">
            <div class="req-modal-box">
                <h3 class="req-modal-title" id="gaActionTitle">EDIT GRADE RULE</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">GRADE CODE</label>
                        <input type="text" id="gaActionGradeCode" class="req-modal-input" placeholder="Code">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">GRADE</label>
                        <input type="text" id="gaActionGradeValue" class="req-modal-input" placeholder="Grade">
                    </div>
                </div>
                <div class="req-modal-field-group" style="margin-top:12px;">
                    <label class="req-modal-label">REMARKS</label>
                    <textarea id="gaActionRemarks" class="req-modal-input" rows="3" placeholder="Remarks" style="resize:vertical; font-size:0.85rem; padding:8px 10px;"></textarea>
                </div>
                <div class="req-modal-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-ga-confirm-action>Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeleteModal" style="display:none;">
            <div class="req-modal-box req-modal-success" style="min-width:300px;">
                <h3 class="req-modal-title" style="color:#c0392b;" id="gaDeleteTitle">DELETE GRADE RULE</h3>
                <p style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;" id="gaDeleteText">
                    Are you sure you want to delete this grade rule?
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

    var searchInput = document.getElementById('gsSearchInput');
    var table = document.getElementById('gsTable');
    var actionModal = document.getElementById('gaActionModal');
    var deleteModal = document.getElementById('gaDeleteModal');
    var actionTitle = document.getElementById('gaActionTitle');
    var deleteTitle = document.getElementById('gaDeleteTitle');
    var deleteText = document.getElementById('gaDeleteText');
    var actionGradeCode = document.getElementById('gaActionGradeCode');
    var actionGradeValue = document.getElementById('gaActionGradeValue');
    var actionRemarks = document.getElementById('gaActionRemarks');
    var newGradeCode = document.getElementById('gaNewGradeCode');
    var newGradeValue = document.getElementById('gaNewGradeValue');
    var newGradeRemarks = document.getElementById('gaNewGradeRemarks');
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    var gsStoreUrl = @json(route('registrar.services.grading-academic.grading-system.store'));
    var gsUpdateUrlTemplate = @json(route('registrar.services.grading-academic.grading-system.update', ['gradeRule' => '__ID__']));
    var gsDestroyUrlTemplate = @json(route('registrar.services.grading-academic.grading-system.destroy', ['gradeRule' => '__ID__']));
    var nextMenuIndex = page.querySelectorAll('[data-gs-menu-toggle]').length;
    var activeRow = null;
    var activeAction = 'edit';

    function gsBuildUrl(template, id) {
        return String(template).replace('__ID__', String(id));
    }

    function gsRequest(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: payload ? JSON.stringify(payload) : null
        }).then(function (response) {
            if (!response.ok) {
                return response.json().catch(function () { return {}; }).then(function (data) {
                    var firstError = 'Request failed.';
                    if (data && data.errors) {
                        var keys = Object.keys(data.errors);
                        if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                            firstError = data.errors[keys[0]][0];
                        }
                    }
                    throw new Error(firstError);
                });
            }

            return response.json().catch(function () { return { ok: true }; });
        });
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

    function filterRows() {
        if (!table || !searchInput) return;
        var q = (searchInput.value || '').toLowerCase().trim();
        table.querySelectorAll('tbody tr').forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
        });
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

    function buildActionCell(item, menuId, id) {
        return '' +
            '<td>' +
                '<div class="apst-action-btn" data-gs-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="' + menuId + '">' +
                    '<button type="button" data-ga-open-action="edit" data-ga-item="' + item + '" data-ga-id="' + id + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        'Edit' +
                    '</button>' +
                    '<button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="' + item + '" data-ga-id="' + id + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        'Delete' +
                    '</button>' +
                '</div>' +
            '</td>';
    }

    function resetNewPeriodForm() {
        if (newGradeCode) newGradeCode.value = '';
        if (newGradeValue) newGradeValue.value = '';
        if (newGradeRemarks) newGradeRemarks.value = '';
        page.querySelectorAll('.ga-new-period-check').forEach(function (checkbox) {
            checkbox.checked = true;
        });
    }

    page.addEventListener('click', function (event) {
        var menuToggle = event.target.closest('[data-gs-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            toggleActionMenu(menuToggle.getAttribute('data-gs-menu-toggle'), menuToggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }

        var openBtn = event.target.closest('[data-ga-modal-open]');
        if (openBtn) {
            if (openBtn.getAttribute('data-ga-modal-open') === 'gaNewPeriodModal') {
                resetNewPeriodForm();
            }
            openModal(openBtn.getAttribute('data-ga-modal-open'));
            return;
        }

        var actionBtn = event.target.closest('[data-ga-open-action]');
        if (actionBtn) {
            closeActionMenus();
            var action = actionBtn.getAttribute('data-ga-open-action') || 'edit';
            var item = actionBtn.getAttribute('data-ga-item') || 'record';
            var row = actionBtn.closest('tr');
            activeRow = row;
            activeAction = action;

            if (row && row.cells.length >= 4) {
                if (actionGradeCode) actionGradeCode.value = (row.cells[1].textContent || '').trim();
                if (actionGradeValue) actionGradeValue.value = (row.cells[2].textContent || '').trim();
                if (actionRemarks) actionRemarks.value = (row.cells[3].textContent || '').trim();
            }

            if (action === 'delete' || action === 'archive') {
                if (deleteTitle) deleteTitle.textContent = 'DELETE GRADE RULE';
                if (deleteText) deleteText.textContent = 'Are you sure you want to delete grade rule "' + item + '"?';
                openModal('gaDeleteModal');
                return;
            }

            actionTitle.textContent = 'EDIT GRADE RULE';
            openModal('gaActionModal');
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

        if (event.target.matches('[data-ga-save], [data-ga-confirm-action]')) {
            if (event.target.matches('[data-ga-save]')) {
                var code = newGradeCode ? newGradeCode.value.trim() : '';
                var grade = newGradeValue ? newGradeValue.value.trim() : '';
                var remarks = newGradeRemarks ? newGradeRemarks.value.trim() : '';
                var selectedPeriods = [];
                page.querySelectorAll('.ga-new-period-check:checked').forEach(function (checkbox) {
                    selectedPeriods.push(checkbox.value);
                });

                if (!code || !remarks) {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Please fill in Grade Code and Remarks.');
                    }
                    return;
                }

                gsRequest(gsStoreUrl, 'POST', {
                    code: code,
                    grade: grade || null,
                    remarks: remarks,
                    periods: selectedPeriods
                }).then(function () {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Grading rule added successfully.');
                    }
                    window.location.reload();
                }).catch(function (error) {
                    alert(error.message || 'Unable to save grading rule.');
                });
                return;
            }

            if (event.target.matches('[data-ga-confirm-action]') && actionModal && actionModal.style.display === 'flex') {
                if (activeAction === 'edit' && activeRow && activeRow.cells.length >= 4) {
                    var id = activeRow.getAttribute('data-grade-rule-id');
                    if (!id) {
                        alert('Missing grade rule id.');
                        return;
                    }

                    gsRequest(gsBuildUrl(gsUpdateUrlTemplate, id), 'PUT', {
                        code: actionGradeCode ? actionGradeCode.value.trim() : '',
                        grade: actionGradeValue ? actionGradeValue.value.trim() : '',
                        remarks: actionRemarks ? actionRemarks.value.trim() : ''
                    }).then(function () {
                        if (typeof showRegistrarToast === 'function') {
                            showRegistrarToast('Grading rule updated successfully.');
                        }
                        window.location.reload();
                    }).catch(function (error) {
                        alert(error.message || 'Unable to update grading rule.');
                    });
                    return;
                }
            }

            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Demo action completed successfully.');
            }
            closeModal(event.target.closest('.req-modal-overlay'));
        }

        if (event.target.matches('[data-ga-confirm-delete]')) {
            if (activeRow) {
                var id = activeRow.getAttribute('data-grade-rule-id');
                if (!id) {
                    alert('Missing grade rule id.');
                    return;
                }

                gsRequest(gsBuildUrl(gsDestroyUrlTemplate, id), 'DELETE').then(function () {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Grading rule deleted successfully.');
                    }
                    window.location.reload();
                }).catch(function (error) {
                    alert(error.message || 'Unable to delete grading rule.');
                });
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

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }
    window.addEventListener('scroll', closeActionMenus, true);
    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-gs-menu-toggle]') && !event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });
});
</script>
@endpush
