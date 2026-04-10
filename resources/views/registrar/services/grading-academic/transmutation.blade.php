@extends('layouts.registrar')

@section('title', 'PLP - Transmutation')
@section('page-title', 'TRANSMUTATION')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="pf-page">
    <div class="ga-page" id="tmPage">
        <style>
            .ga-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                flex-wrap: wrap;
            }

            .ga-toolbar .ga-search-wrap.sf-search-box {
                width: min(460px, 100%);
            }

            .ga-trans-table td.ga-state-pass,
            .ga-trans-table td.ga-state-fail {
                text-align: center !important;
            }

            .ga-trans-table td.ga-state-pass {
                color: #28a745 !important;
                font-weight: bold !important;
            }

            .ga-trans-table td.ga-state-fail {
                color: #dc3545 !important;
                font-weight: bold !important;
            }

            .tm-modal-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
                margin-top: 15px;
            }

            .tm-modal-grid-3 {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
                margin-top: 15px;
            }

            .tm-modal-initial-group {
                grid-column: span 2;
                background: #f8f9fa;
                padding: 12px 16px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
            }

            .tm-modal-initial-group .req-modal-label {
                margin-bottom: 12px;
                display: block;
                color: #555;
            }

            .tm-modal-initial-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .tm-modal-grid .tm-modal-remarks {
                margin-left: 0;
            }

            .tm-modal-grid .rg-listbox,
            .tm-modal-grid-3 .rg-listbox,
            .tm-modal-initial-grid .rg-listbox {
                width: 100%;
            }

            .ga-table-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 10px;
                color: #355649;
                font-size: 0.83rem;
                font-weight: 600;
            }

            @media (max-width: 720px) {
                .tm-modal-grid,
                .tm-modal-grid-3,
                .tm-modal-initial-grid {
                    grid-template-columns: 1fr;
                }
                .tm-modal-initial-group {
                    grid-column: 1 / -1;
                }

                .ga-toolbar .ga-search-wrap.sf-search-box,
                .ga-toolbar .ga-btn {
                    width: 100%;
                }
            }
        </style>
        <form method="GET" action="{{ route('registrar.services.grading-academic.transmutation') }}" class="ga-toolbar" id="tmFilterForm">
            @include('registrar.components.search-bar', [
                'id' => 'tmSearchInput',
                'name' => 'q',
                'value' => $search,
                'placeholder' => 'Search SY, Term, Program, Code, Remarks...',
                'containerClass' => 'ga-search-wrap',
                'inputClass' => 'js-tm-auto-submit-search',
                'inputAttributes' => [
                    'data-tm-auto-submit-search' => '1',
                ],
            ])
            <button type="button" class="pf-btn-new ga-btn ga-btn-primary" data-ga-modal-open="gaTransmutationNewModal">+ Add Transmutation</button>
        </form>

        <div class="ga-table-wrap app-table-wrap">
            <table class="ga-table ga-table-compact app-table ga-trans-table" id="tmTable" data-no-auto-pager="1">
                    <colgroup>
                        <col class="ga-trans-col-action">
                        <col class="ga-trans-col-sy">
                        <col class="ga-trans-col-term">
                        <col class="ga-trans-col-program">
                        <col class="ga-trans-col-initial">
                        <col class="ga-trans-col-initial">
                        <col class="ga-trans-col-grade">
                        <col class="ga-trans-col-code">
                        <col class="ga-trans-col-remarks">
                    </colgroup>
                    <thead>
                        <tr class="ga-trans-head-top">
                            <th rowspan="2" style="text-align: center;">Action</th>
                            <th rowspan="2" style="text-align: center;">SY</th>
                            <th rowspan="2" style="text-align: center;">Term</th>
                            <th rowspan="2" style="text-align: center;">Program</th>
                            <th colspan="2" style="text-align: center;">Initial Grade</th>
                            <th rowspan="2" style="text-align: center;">Transmuted Grade</th>
                            <th rowspan="2" style="text-align: center;">Code</th>
                            <th rowspan="2" style="text-align: center;">Remarks</th>
                        </tr>
                        <tr class="ga-trans-head-sub">
                            <th style="text-align: center;">From</th>
                            <th style="text-align: center;">To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transmutationRules as $index => $rule)
                        <tr
                            data-transmutation-rule-id="{{ $rule->id }}"
                            data-academic-term-id="{{ $rule->academic_term_id }}"
                            data-course-id="{{ $rule->course_id }}"
                            data-school-year="{{ $rule->resolved_school_year }}"
                            data-term="{{ $rule->resolved_term }}"
                        >
                            <td>
                                <div class="apst-action-btn" data-tm-menu-toggle="tmMenu{{ ($transmutationRules->firstItem() ?? 1) + $index }}" aria-label="Open row actions" title="Actions">
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="apst-dropdown" id="tmMenu{{ ($transmutationRules->firstItem() ?? 1) + $index }}">
                                    <button type="button" data-ga-open-action="edit" data-ga-item="{{ ($rule->resolved_program !== '' ? $rule->resolved_program : 'Program') . ' ' . number_format((float) $rule->initial_from, 2) . '-' . number_format((float) $rule->initial_to, 2) }}" data-ga-id="{{ $rule->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="{{ ($rule->resolved_program !== '' ? $rule->resolved_program : 'Program') . ' ' . number_format((float) $rule->initial_from, 2) . '-' . number_format((float) $rule->initial_to, 2) }}" data-ga-id="{{ $rule->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                            <td>{{ $rule->resolved_school_year !== '' ? $rule->resolved_school_year : 'N/A' }}</td>
                            <td>{{ $rule->resolved_term !== '' ? $rule->resolved_term : 'N/A' }}</td>
                            <td>{{ $rule->resolved_program !== '' ? $rule->resolved_program : 'N/A' }}</td>
                            <td><span class="ga-trans-chip">{{ number_format((float) $rule->initial_from, 2) }}</span></td>
                            <td><span class="ga-trans-chip">{{ number_format((float) $rule->initial_to, 2) }}</span></td>
                            <td><span class="ga-trans-chip">{{ number_format((float) $rule->transmuted_grade, 2) }}</span></td>
                            <td>{{ strtoupper((string) $rule->code) }}</td>
                            <td class="{{ (stripos((string) $rule->remarks, 'fail') !== false || strtoupper((string) $rule->code) === 'F') ? 'ga-state-fail' : 'ga-state-pass' }}">{{ $rule->remarks }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No transmutation rules found.</td></tr>
                        @endforelse
                    </tbody>
            </table>
        </div>

        <div class="ga-table-meta">
            <div>
                Showing {{ $transmutationRules->firstItem() ?? 0 }}-{{ $transmutationRules->lastItem() ?? 0 }} of {{ $transmutationRules->total() }}
            </div>
        </div>

        <div class="app-table-pager">
            {{ $transmutationRules->links() }}
        </div>

        <div class="req-modal-overlay" id="gaTransmutationNewModal" style="display:none;">
            <div class="req-modal-box" style="max-width:640px; padding: 25px;">
                <h3 class="req-modal-title" id="gaTransmutationNewTitle" style="margin-bottom: 20px;">ADD TRANSMUTATION RULE</h3>
                
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 16px;">
                    <div class="tm-modal-grid" style="margin-top: 0;">
                        <div class="req-modal-field-group">
                            <label class="req-modal-label">SY</label>
                            @include('registrar.components.listbox-select', [
                                'id' => 'tmNewSy',
                                'name' => 'tmNewSy',
                                'options' => $schoolYearOptions,
                                'selected' => '',
                                'placeholder' => 'Select school year',
                            ])
                        </div>
                        <div class="req-modal-field-group">
                            <label class="req-modal-label">TERM</label>
                            @include('registrar.components.listbox-select', [
                                'id' => 'tmNewTerm',
                                'name' => 'tmNewTerm',
                                'options' => $termOptions,
                                'selected' => '',
                                'placeholder' => 'Select term',
                            ])
                        </div>
                        
                        <div class="req-modal-field-group" style="grid-column: 1 / -1;">
                            <label class="req-modal-label">PROGRAM</label>
                            @include('registrar.components.listbox-select', [
                                'id' => 'tmNewProgram',
                                'name' => 'tmNewProgram',
                                'options' => $programOptions,
                                'selected' => '',
                                'placeholder' => 'Select program',
                            ])
                        </div>
                    </div>
                </div>

                <div style="background: #f8fbf9; border: 1px solid #cce3d6; border-radius: 8px; padding: 15px; margin-bottom: 16px;">
                    <label class="req-modal-label" style="text-align: center; display: block; margin-bottom: 12px; color: #006837;">INITIAL GRADE</label>
                    <div class="tm-modal-initial-grid">
                        <div class="req-modal-field-group"><label class="req-modal-label">FROM</label><input class="req-modal-input" id="tmNewFrom" placeholder="75.00"></div>
                        <div class="req-modal-field-group"><label class="req-modal-label">TO</label><input class="req-modal-input" id="tmNewTo" placeholder="79.99"></div>
                    </div>
                </div>

                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                    <div class="tm-modal-grid-3" style="margin-top: 0;">
                        <div class="req-modal-field-group"><label class="req-modal-label">TRANSMUTED</label><input class="req-modal-input" id="tmNewGrade" placeholder="3.00"></div>
                        <div class="req-modal-field-group"><label class="req-modal-label">CODE</label><input class="req-modal-input" id="tmNewCode" placeholder="P"></div>
                        <div class="req-modal-field-group tm-modal-remarks"><label class="req-modal-label">REMARKS</label><input class="req-modal-input" id="tmNewRemarks" placeholder="Passed"></div>
                    </div>
                </div>

                <div class="req-modal-actions" style="margin-top: 25px; gap: 12px;">
                    <button type="button" class="req-btn-cancel" data-ga-close style="padding: 10px 20px;">Cancel</button>
                    <button type="button" class="req-btn-save" data-tm-save-new style="padding: 10px 24px; background: #006837;">Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaTransmutationActionModal" style="display:none;">
            <div class="req-modal-box" style="max-width:640px; padding: 25px;">
                <h3 class="req-modal-title" id="gaTransmutationActionTitle" style="margin-bottom: 20px;">EDIT TRANSMUTATION RULE</h3>
                
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 16px;">
                    <div class="tm-modal-grid" style="margin-top: 0;">
                        <div class="req-modal-field-group">
                            <label class="req-modal-label">SY</label>
                            @include('registrar.components.listbox-select', [
                                'id' => 'tmEditSy',
                                'name' => 'tmEditSy',
                                'options' => $schoolYearOptions,
                                'selected' => '',
                                'placeholder' => 'Select school year',
                            ])
                        </div>
                        <div class="req-modal-field-group">
                            <label class="req-modal-label">TERM</label>
                            @include('registrar.components.listbox-select', [
                                'id' => 'tmEditTerm',
                                'name' => 'tmEditTerm',
                                'options' => $termOptions,
                                'selected' => '',
                                'placeholder' => 'Select term',
                            ])
                        </div>
                        
                        <div class="req-modal-field-group" style="grid-column: 1 / -1;">
                            <label class="req-modal-label">PROGRAM</label>
                            @include('registrar.components.listbox-select', [
                                'id' => 'tmEditProgram',
                                'name' => 'tmEditProgram',
                                'options' => $programOptions,
                                'selected' => '',
                                'placeholder' => 'Select program',
                            ])
                        </div>
                    </div>
                </div>
                    
                <div style="background: #f8fbf9; border: 1px solid #cce3d6; border-radius: 8px; padding: 15px; margin-bottom: 16px;">
                    <label class="req-modal-label" style="text-align: center; display: block; margin-bottom: 12px; color: #006837;">INITIAL GRADE</label>
                    <div class="tm-modal-initial-grid">
                        <div class="req-modal-field-group"><label class="req-modal-label">FROM</label><input class="req-modal-input" id="tmEditFrom"></div>
                        <div class="req-modal-field-group"><label class="req-modal-label">TO</label><input class="req-modal-input" id="tmEditTo"></div>
                    </div>
                </div>
                    
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                    <div class="tm-modal-grid-3" style="margin-top: 0;">
                        <div class="req-modal-field-group"><label class="req-modal-label">TRANSMUTED</label><input class="req-modal-input" id="tmEditGrade"></div>
                        <div class="req-modal-field-group"><label class="req-modal-label">CODE</label><input class="req-modal-input" id="tmEditCode"></div>
                        <div class="req-modal-field-group tm-modal-remarks"><label class="req-modal-label">REMARKS</label><input class="req-modal-input" id="tmEditRemarks"></div>
                    </div>
                </div>
                
                <div class="req-modal-actions" style="margin-top: 25px; gap: 12px;">
                    <button type="button" class="req-btn-cancel" data-ga-close style="padding: 10px 20px;">Cancel</button>
                    <button type="button" class="req-btn-save" data-ga-confirm-action style="padding: 10px 24px; background: #006837;">Save</button>
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
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('tmPage');
    if (!page) return;

    var filterForm = document.getElementById('tmFilterForm');
    var searchInput = filterForm ? filterForm.querySelector('[data-tm-auto-submit-search]') : null;
    var actionModal = document.getElementById('gaTransmutationActionModal');
    var deleteModal = document.getElementById('gaTransmutationDeleteModal');
    var actionTitle = document.getElementById('gaTransmutationActionTitle');
    var deleteText = document.getElementById('gaTransmutationDeleteText');
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    var tmStoreUrl = @json(route('registrar.services.grading-academic.transmutation.store'));
    var tmUpdateUrlTemplate = @json(route('registrar.services.grading-academic.transmutation.update', ['transmutationRule' => '__ID__']));
    var tmDestroyUrlTemplate = @json(route('registrar.services.grading-academic.transmutation.destroy', ['transmutationRule' => '__ID__']));
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

    function debounce(fn, delay) {
        var timer = null;

        return function () {
            var args = arguments;

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(function () {
                fn.apply(null, args);
            }, delay);
        };
    }

    function cleanNumber(value) {
        return (value || '').trim();
    }

    function setSelectValue(selectElement, value) {
        if (!selectElement) return;

        selectElement.value = value || '';
        selectElement.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function tmBuildUrl(template, id) {
        return String(template).replace('__ID__', String(id));
    }

    function tmRequest(url, method, payload) {
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

    function fillEditForm(row) {
        if (!row || row.cells.length < 9) return;
        setSelectValue(tmEditSy, (row.getAttribute('data-school-year') || '').trim());
        setSelectValue(tmEditTerm, (row.getAttribute('data-term') || '').trim());
        setSelectValue(tmEditProgram, (row.getAttribute('data-course-id') || '').trim());
        tmEditFrom.value = (row.cells[4].textContent || '').trim();
        tmEditTo.value = (row.cells[5].textContent || '').trim();
        tmEditGrade.value = (row.cells[6].textContent || '').trim();
        tmEditCode.value = (row.cells[7].textContent || '').trim();
        tmEditRemarks.value = (row.cells[8].textContent || '').trim();
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
            var courseId = (tmNewProgram.value || '').trim();
            var from = cleanNumber(tmNewFrom.value);
            var to = cleanNumber(tmNewTo.value);
            var grade = cleanNumber(tmNewGrade.value);
            var code = (tmNewCode.value || '').trim();
            var remarks = (tmNewRemarks.value || '').trim();

            if (!sy || !term || !courseId || !from || !to || !grade || !code || !remarks) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Please fill in all transmutation fields.');
                }
                return;
            }

            tmRequest(tmStoreUrl, 'POST', {
                school_year: sy,
                term: term,
                course_id: courseId,
                initial_from: from,
                initial_to: to,
                transmuted_grade: grade,
                code: code,
                remarks: remarks
            }).then(function () {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Transmutation rule added successfully.');
                }
                window.location.reload();
            }).catch(function (error) {
                alert(error.message || 'Unable to save transmutation rule.');
            });
            return;
        }

        if (event.target.matches('[data-ga-confirm-action]')) {
            if (activeAction === 'edit' && activeRow && activeRow.cells.length >= 9) {
                var id = activeRow.getAttribute('data-transmutation-rule-id');
                if (!id) {
                    alert('Missing transmutation rule id.');
                    return;
                }

                tmRequest(tmBuildUrl(tmUpdateUrlTemplate, id), 'PUT', {
                    school_year: (tmEditSy.value || '').trim(),
                    term: (tmEditTerm.value || '').trim(),
                    course_id: (tmEditProgram.value || '').trim(),
                    initial_from: cleanNumber(tmEditFrom.value),
                    initial_to: cleanNumber(tmEditTo.value),
                    transmuted_grade: cleanNumber(tmEditGrade.value),
                    code: (tmEditCode.value || '').trim(),
                    remarks: (tmEditRemarks.value || '').trim()
                }).then(function () {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Transmutation rule updated successfully.');
                    }
                    window.location.reload();
                }).catch(function (error) {
                    alert(error.message || 'Unable to update transmutation rule.');
                });
            } else {
                closeModal(actionModal);
            }
            return;
        }

        if (event.target.matches('[data-ga-confirm-delete]')) {
            if (activeRow) {
                var id = activeRow.getAttribute('data-transmutation-rule-id');
                if (!id) {
                    alert('Missing transmutation rule id.');
                    return;
                }

                tmRequest(tmBuildUrl(tmDestroyUrlTemplate, id), 'DELETE').then(function () {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Transmutation rule deleted successfully.');
                    }
                    window.location.reload();
                }).catch(function (error) {
                    alert(error.message || 'Unable to delete transmutation rule.');
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

    window.addEventListener('scroll', closeActionMenus, true);
    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-tm-menu-toggle]') && !event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });

    if (searchInput && filterForm) {
        var submitSearch = debounce(function () {
            filterForm.submit();
        }, 280);

        searchInput.addEventListener('input', function () {
            submitSearch();
        });

        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                filterForm.submit();
            }
        });
    }
});
</script>
@endpush
