@extends('layouts.registrar')

@section('title', 'PLP - Grading Periods')
@section('page-title', 'GRADING PERIODS')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <div class="ga-card ga-filter-card sched-filter-bar ga-periods-filter">
            <div class="ga-filter-grid ga-filter-grid-periods-lite">
                <div><label class="ga-label">SY</label><select class="app-filter-select" id="gpFilterSchoolYear"><option value="">All</option><option>2025-2026</option><option>2024-2025</option><option>2023-2024</option></select></div>
                <div><label class="ga-label">Semester</label><select class="app-filter-select" id="gpFilterSemester"><option value="">All</option><option>First</option><option>Second</option><option>Summer</option></select></div>
                <div><label class="ga-label">Grading Computation</label><select class="app-filter-select" id="gpFilterComputation"><option value="">All</option><option>Weighted</option><option>Averaging</option><option>Point-Based</option></select></div>
                <div><label class="ga-label">Faculty</label><select class="app-filter-select" id="gpFilterFaculty"><option value="">All</option><option>Marasigan</option><option>Dela Cruz</option><option>Santos</option><option>Reyes</option></select></div>
                <div><label class="ga-label">Subject</label><select class="app-filter-select" id="gpFilterSubject"><option value="">All</option><option>CS301</option><option>IT201</option><option>MATH101</option><option>ENG102</option></select></div>
                <div><label class="ga-label">Section</label><select class="app-filter-select" id="gpFilterSection"><option value="">All</option><option>BSCS 3A</option><option>BSIT 2B</option><option>BSCS 1C</option></select></div>

                <div class="ga-filter-inline-action">
                    <label class="ga-label">&nbsp;</label>
                    <button type="button" class="pf-btn-new ga-btn ga-btn-primary ga-filter-update-btn" id="gpFilterSearch">Search</button>
                </div>
            </div>
        </div>

        <div class="ga-toolbar ga-toolbar-end">
            <button type="button" class="pf-btn-new ga-btn ga-btn-primary" data-ga-modal-open="gaPeriodsAddModal">+ Add Period</button>
        </div>

        <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table ga-table-compact app-table" id="gpTable">
                    <thead>
                        <tr>
                            <th style="width:70px;">Action</th>
                            <th>Section/Subject/Faculty</th>
                            <th>Period</th>
                            <th>Description</th>
                            <th>Percentage</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Start Time</th>
                            <th>Grading Computation</th>
                            <th>Used Grades Lib</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gradingPeriods as $index => $period)
                        <tr data-grading-period-id="{{ $period->id }}" data-school-year="{{ $period->school_year }}" data-semester="{{ $period->semester }}">
                            <td>
                                <div class="apst-action-btn" data-gp-menu-toggle="gpMenu{{ $index }}" aria-label="Open row actions" title="Actions">
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="apst-dropdown" id="gpMenu{{ $index }}">
                                    <button type="button" data-ga-open-action="edit" data-ga-item="{{ $period->section_subject_faculty }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="{{ $period->section_subject_faculty }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                            <td>{{ $period->section_subject_faculty }}</td>
                            <td>{{ $period->period }}</td>
                            <td>{{ $period->description }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $period->percentage, 2, '.', ''), '0'), '.') }}</td>
                            <td>{{ optional($period->start_date)->format('m/d/Y') ?: '-' }}</td>
                            <td>{{ optional($period->end_date)->format('m/d/Y') ?: '-' }}</td>
                            <td>{{ $period->start_time ?: '-' }}</td>
                            <td>{{ $period->grading_computation }}</td>
                            <td><label class="ga-check ga-check-tight"><input type="checkbox" {{ $period->use_grades_library ? 'checked' : '' }} disabled> {{ $period->use_grades_library ? 'Yes' : 'No' }}</label></td>
                        </tr>
                        @empty
                        <tr><td colspan="10" style="text-align:center; color:#666;">No grading periods found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
        </div>

        <div class="req-modal-overlay" id="gaPeriodsAddModal" style="display:none;">
            <div class="req-modal-box" style="max-width:620px;">
                <h3 class="req-modal-title" id="gaPeriodsAddTitle">ADD PERIOD RECORD</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SECTION / SUBJECT / FACULTY</label>
                        <input type="text" class="req-modal-input" id="gaAddSectionSubjectFaculty" placeholder="e.g. BSCS 3A / CS301 / MARASIGAN">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">PERIOD</label>
                        <select class="req-modal-input" id="gaAddPeriod">
                            <option>Midterm</option>
                            <option>Finals</option>
                        </select>
                    </div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">DESCRIPTION</label>
                        <select class="req-modal-input" id="gaAddDescription">
                            <option>Written/Seatwork</option>
                            <option>Quiz</option>
                            <option>Project</option>
                            <option>Lab Activity</option>
                        </select>
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">PERCENTAGE</label>
                        <select class="req-modal-input" id="gaAddPercentage">
                            <option>10</option>
                            <option>20</option>
                            <option>30</option>
                            <option>40</option>
                            <option>50</option>
                        </select>
                    </div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">START DATE</label>
                        <input type="date" class="req-modal-input" id="gaAddStartDate">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">END DATE</label>
                        <input type="date" class="req-modal-input" id="gaAddEndDate">
                    </div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">START TIME</label>
                        <input type="time" class="req-modal-input" id="gaAddStartTime">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">GRADING COMPUTATION</label>
                        <select class="req-modal-input" id="gaAddComputation">
                            <option>Weighted</option>
                            <option>Averaging</option>
                            <option>Point-Based</option>
                        </select>
                    </div>
                </div>
                <div class="req-modal-field-group" style="margin-top:12px;">
                    <label class="ga-check"><input type="checkbox" id="gaAddUseGradesLib"> Use Grades Library</label>
                </div>
                <div class="req-modal-actions" style="justify-content:flex-end;">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-ga-add-save>Add</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaPeriodsActionModal" style="display:none;">
            <div class="req-modal-box" style="max-width:620px;">
                <h3 class="req-modal-title" id="gaPeriodsActionTitle">EDIT PERIOD RECORD</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SECTION / SUBJECT / FACULTY</label>
                        <input type="text" class="req-modal-input" id="gaEditSectionSubjectFaculty" placeholder="Section / Subject / Faculty">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">PERIOD</label>
                        <input type="text" class="req-modal-input" id="gaEditPeriod" placeholder="Period">
                    </div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">DESCRIPTION</label>
                        <input type="text" class="req-modal-input" id="gaEditDescription" placeholder="Description">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">PERCENTAGE</label>
                        <input type="text" class="req-modal-input" id="gaEditPercentage" placeholder="Percentage">
                    </div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">START DATE</label>
                        <input type="date" class="req-modal-input" id="gaEditStartDate">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">END DATE</label>
                        <input type="date" class="req-modal-input" id="gaEditEndDate">
                    </div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">START TIME</label>
                        <input type="time" class="req-modal-input" id="gaEditStartTime">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">GRADING COMPUTATION</label>
                        <input type="text" class="req-modal-input" id="gaEditComputation" placeholder="Computation">
                    </div>
                </div>
                <div class="req-modal-field-group" style="margin-top:12px;">
                    <label class="ga-check"><input type="checkbox" id="gaEditUseGradesLib"> Use Grades Library</label>
                </div>
                <div class="req-modal-actions" style="justify-content:flex-end;">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-ga-confirm-action>Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaPeriodsDeleteModal" style="display:none;">
            <div class="req-modal-box req-modal-success" style="min-width:300px;">
                <h3 class="req-modal-title" style="color:#c0392b;" id="gaPeriodsDeleteTitle">DELETE PERIOD RECORD</h3>
                <p id="gaPeriodsDeleteText" style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;">
                    Are you sure you want to delete this period record?
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

    var table = document.getElementById('gpTable');
    var gpFilterSchoolYear = document.getElementById('gpFilterSchoolYear');
    var gpFilterSemester = document.getElementById('gpFilterSemester');
    var gpFilterComputation = document.getElementById('gpFilterComputation');
    var gpFilterFaculty = document.getElementById('gpFilterFaculty');
    var gpFilterSubject = document.getElementById('gpFilterSubject');
    var gpFilterSection = document.getElementById('gpFilterSection');
    var gpFilterSearch = document.getElementById('gpFilterSearch');
    var actionModal = document.getElementById('gaPeriodsActionModal');
    var deleteModal = document.getElementById('gaPeriodsDeleteModal');
    var actionTitle = document.getElementById('gaPeriodsActionTitle');
    var deleteText = document.getElementById('gaPeriodsDeleteText');
    var addSectionSubjectFaculty = document.getElementById('gaAddSectionSubjectFaculty');
    var addPeriod = document.getElementById('gaAddPeriod');
    var addDescription = document.getElementById('gaAddDescription');
    var addPercentage = document.getElementById('gaAddPercentage');
    var addStartDate = document.getElementById('gaAddStartDate');
    var addEndDate = document.getElementById('gaAddEndDate');
    var addStartTime = document.getElementById('gaAddStartTime');
    var addComputation = document.getElementById('gaAddComputation');
    var addUseGradesLib = document.getElementById('gaAddUseGradesLib');
    var editSectionSubjectFaculty = document.getElementById('gaEditSectionSubjectFaculty');
    var editPeriod = document.getElementById('gaEditPeriod');
    var editDescription = document.getElementById('gaEditDescription');
    var editPercentage = document.getElementById('gaEditPercentage');
    var editStartDate = document.getElementById('gaEditStartDate');
    var editEndDate = document.getElementById('gaEditEndDate');
    var editStartTime = document.getElementById('gaEditStartTime');
    var editComputation = document.getElementById('gaEditComputation');
    var editUseGradesLib = document.getElementById('gaEditUseGradesLib');
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    var gpStoreUrl = @json(route('registrar.services.grading-academic.grading-periods.store'));
    var gpUpdateUrlTemplate = @json(route('registrar.services.grading-academic.grading-periods.update', ['gradingPeriod' => '__ID__']));
    var gpDestroyUrlTemplate = @json(route('registrar.services.grading-academic.grading-periods.destroy', ['gradingPeriod' => '__ID__']));
    var nextMenuIndex = page.querySelectorAll('[data-gp-menu-toggle]').length;
    var activeRow = null;
    var activeAction = 'edit';

    var pickerInputs = page.querySelectorAll('input[type="date"], input[type="time"]');
    pickerInputs.forEach(function (input) {
        input.addEventListener('click', function () {
            if (typeof input.showPicker === 'function') {
                try {
                    input.showPicker();
                } catch (err) {
                    // Ignore when picker invocation is blocked by browser.
                }
            }
        });
    });

    function formatDateForTable(value) {
        if (!value || value.indexOf('-') === -1) return value || '';
        var parts = value.split('-');
        if (parts.length !== 3) return value;
        return parts[1] + '/' + parts[2] + '/' + parts[0];
    }

    function toInputDate(value) {
        if (!value || value === '-') return '';
        if (value.indexOf('T') !== -1) {
            return value.substring(0, 10);
        }
        var parts = value.split('/');
        if (parts.length !== 3) return value;
        return parts[2] + '-' + parts[0].padStart(2, '0') + '-' + parts[1].padStart(2, '0');
    }

    function gpBuildUrl(template, id) {
        return String(template).replace('__ID__', String(id));
    }

    function normalizeText(value) {
        return String(value || '').trim().toLowerCase();
    }

    function parseSectionSubjectFaculty(text) {
        var parts = String(text || '').split('/').map(function (item) {
            return item.trim();
        });
        return {
            section: parts[0] || '',
            subject: parts[1] || '',
            faculty: parts[2] || ''
        };
    }

    function matchesOrMissing(rowValue, filterValue) {
        if (!filterValue) return true;
        if (!rowValue) return true;
        return normalizeText(rowValue) === normalizeText(filterValue);
    }

    function applyGpFilters() {
        if (!table || !table.tBodies.length) return;
        var tbody = table.tBodies[0];
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-grading-period-id]'));
        var visibleCount = 0;

        rows.forEach(function (row) {
            var rowSchoolYear = row.getAttribute('data-school-year') || '';
            var rowSemester = row.getAttribute('data-semester') || '';
            var rowSectionSubjectFaculty = row.cells[1] ? row.cells[1].textContent : '';
            var parsed = parseSectionSubjectFaculty(rowSectionSubjectFaculty);
            var rowComputation = row.cells[8] ? row.cells[8].textContent : '';

            var schoolYearOk = matchesOrMissing(rowSchoolYear, gpFilterSchoolYear ? gpFilterSchoolYear.value : '');
            var semesterOk = matchesOrMissing(rowSemester, gpFilterSemester ? gpFilterSemester.value : '');
            var computationOk = !gpFilterComputation || !gpFilterComputation.value || normalizeText(rowComputation) === normalizeText(gpFilterComputation.value);
            var facultyOk = !gpFilterFaculty || !gpFilterFaculty.value || normalizeText(parsed.faculty).indexOf(normalizeText(gpFilterFaculty.value)) !== -1;
            var subjectOk = !gpFilterSubject || !gpFilterSubject.value || normalizeText(parsed.subject).indexOf(normalizeText(gpFilterSubject.value)) !== -1;
            var sectionOk = !gpFilterSection || !gpFilterSection.value || normalizeText(parsed.section).indexOf(normalizeText(gpFilterSection.value)) !== -1;

            var visible = schoolYearOk && semesterOk && computationOk && facultyOk && subjectOk && sectionOk;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount += 1;
        });

        var emptyRow = tbody.querySelector('#gpFilterEmptyRow');
        if (!visibleCount) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'gpFilterEmptyRow';
                emptyRow.innerHTML = '<td colspan="10" style="text-align:center; color:#666;">No matching grading periods found.</td>';
                tbody.appendChild(emptyRow);
            }
        } else if (emptyRow) {
            emptyRow.parentNode.removeChild(emptyRow);
        }
    }

    function gpRequest(url, method, payload) {
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
                    var message = 'Request failed.';
                    if (data && data.errors) {
                        var keys = Object.keys(data.errors);
                        if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                            message = data.errors[keys[0]][0];
                        }
                    }
                    throw new Error(message);
                });
            }

            return response.json().catch(function () { return { ok: true }; });
        });
    }

    function buildActionCell(item, menuId) {
        return '' +
            '<td>' +
                '<div class="apst-action-btn" data-gp-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="' + menuId + '">' +
                    '<button type="button" data-ga-open-action="edit" data-ga-item="' + item + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        'Edit' +
                    '</button>' +
                    '<button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="' + item + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        'Delete' +
                    '</button>' +
                '</div>' +
            '</td>';
    }

    function resetAddForm() {
        if (addSectionSubjectFaculty) addSectionSubjectFaculty.value = '';
        if (addPeriod) addPeriod.selectedIndex = 0;
        if (addDescription) addDescription.selectedIndex = 0;
        if (addPercentage) addPercentage.selectedIndex = 0;
        if (addStartDate) addStartDate.value = '';
        if (addEndDate) addEndDate.value = '';
        if (addStartTime) addStartTime.value = '';
        if (addComputation) addComputation.selectedIndex = 0;
        if (addUseGradesLib) addUseGradesLib.checked = false;
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
        var menuToggle = event.target.closest('[data-gp-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            toggleActionMenu(menuToggle.getAttribute('data-gp-menu-toggle'), menuToggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }

        var openBtn = event.target.closest('[data-ga-modal-open]');
        if (openBtn) {
            if (openBtn.getAttribute('data-ga-modal-open') === 'gaPeriodsAddModal') {
                resetAddForm();
            }
            openModal(openBtn.getAttribute('data-ga-modal-open'));
            return;
        }

        var actionBtn = event.target.closest('[data-ga-open-action]');
        if (actionBtn) {
            closeActionMenus();
            var action = actionBtn.getAttribute('data-ga-open-action') || 'edit';
            var item = actionBtn.getAttribute('data-ga-item') || 'record';
            activeRow = actionBtn.closest('tr');
            activeAction = action;

            if (action === 'delete' || action === 'archive') {
                if (deleteText) {
                    deleteText.textContent = 'Are you sure you want to delete period record for ' + item + '?';
                }
                openModal('gaPeriodsDeleteModal');
                return;
            }

            actionTitle.textContent = 'EDIT PERIOD RECORD';
            if (activeRow && activeRow.cells.length >= 10) {
                if (editSectionSubjectFaculty) editSectionSubjectFaculty.value = (activeRow.cells[1].textContent || '').trim();
                if (editPeriod) editPeriod.value = (activeRow.cells[2].textContent || '').trim();
                if (editDescription) editDescription.value = (activeRow.cells[3].textContent || '').trim();
                if (editPercentage) editPercentage.value = (activeRow.cells[4].textContent || '').trim();
                if (editStartDate) editStartDate.value = toInputDate((activeRow.cells[5].textContent || '').trim());
                if (editEndDate) editEndDate.value = toInputDate((activeRow.cells[6].textContent || '').trim());
                if (editStartTime) editStartTime.value = (activeRow.cells[7].textContent || '').trim();
                if (editComputation) editComputation.value = (activeRow.cells[8].textContent || '').trim();
                if (editUseGradesLib) {
                    var usedLibText = (activeRow.cells[9].textContent || '').toLowerCase();
                    editUseGradesLib.checked = usedLibText.indexOf('yes') !== -1;
                }
            }
            openModal('gaPeriodsActionModal');
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

        if (event.target.matches('[data-ga-add-save]')) {
            var rowName = addSectionSubjectFaculty ? addSectionSubjectFaculty.value.trim() : '';
            if (!rowName) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Please enter Section / Subject / Faculty.');
                }
                return;
            }

            var periodValue = addPeriod ? addPeriod.value : '';
            var descriptionValue = addDescription ? addDescription.value : '';
            var percentageValue = addPercentage ? addPercentage.value : '';
            var startDateValue = formatDateForTable(addStartDate ? addStartDate.value : '');
            var endDateValue = formatDateForTable(addEndDate ? addEndDate.value : '');
            var startTimeValue = addStartTime ? addStartTime.value : '';
            var computationValue = addComputation ? addComputation.value : '';
            var useLibChecked = addUseGradesLib ? addUseGradesLib.checked : false;

            gpRequest(gpStoreUrl, 'POST', {
                section_subject_faculty: rowName,
                period: periodValue,
                description: descriptionValue,
                percentage: percentageValue,
                start_date: addStartDate ? addStartDate.value : null,
                end_date: addEndDate ? addEndDate.value : null,
                start_time: startTimeValue || null,
                grading_computation: computationValue,
                use_grades_library: useLibChecked,
                school_year: gpFilterSchoolYear && gpFilterSchoolYear.value ? gpFilterSchoolYear.value : null,
                semester: gpFilterSemester && gpFilterSemester.value ? gpFilterSemester.value : null
            }).then(function () {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Period record added successfully.');
                }
                window.location.reload();
            }).catch(function (error) {
                alert(error.message || 'Unable to save period record.');
            });
            return;
        }

        if (event.target.matches('[data-ga-save], [data-ga-confirm-action]')) {
            if (event.target.matches('[data-ga-confirm-action]') && activeAction === 'edit' && activeRow && activeRow.cells.length >= 10) {
                var recordId = activeRow.getAttribute('data-grading-period-id');
                if (!recordId) {
                    alert('Missing grading period id.');
                    return;
                }

                gpRequest(gpBuildUrl(gpUpdateUrlTemplate, recordId), 'PUT', {
                    section_subject_faculty: editSectionSubjectFaculty ? editSectionSubjectFaculty.value.trim() : '',
                    period: editPeriod ? editPeriod.value.trim() : '',
                    description: editDescription ? editDescription.value.trim() : '',
                    percentage: editPercentage ? editPercentage.value.trim() : 0,
                    start_date: editStartDate ? editStartDate.value : null,
                    end_date: editEndDate ? editEndDate.value : null,
                    start_time: editStartTime ? editStartTime.value : null,
                    grading_computation: editComputation ? editComputation.value.trim() : '',
                    use_grades_library: editUseGradesLib ? editUseGradesLib.checked : false,
                    school_year: gpFilterSchoolYear && gpFilterSchoolYear.value ? gpFilterSchoolYear.value : null,
                    semester: gpFilterSemester && gpFilterSemester.value ? gpFilterSemester.value : null
                }).then(function () {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Grading period updated successfully.');
                    }
                    window.location.reload();
                }).catch(function (error) {
                    alert(error.message || 'Unable to update grading period.');
                });
                return;
            }

            closeModal(event.target.closest('.req-modal-overlay'));
            return;
        }

        if (event.target.matches('[data-ga-confirm-delete]')) {
            if (activeRow && table) {
                var recordId = activeRow.getAttribute('data-grading-period-id');
                if (!recordId) {
                    alert('Missing grading period id.');
                    return;
                }

                gpRequest(gpBuildUrl(gpDestroyUrlTemplate, recordId), 'DELETE').then(function () {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Period record deleted successfully.');
                    }
                    window.location.reload();
                }).catch(function (error) {
                    alert(error.message || 'Unable to delete grading period.');
                });
            }
            closeModal(deleteModal);
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
    if (gpFilterSearch) {
        gpFilterSearch.addEventListener('click', applyGpFilters);
    }
    [gpFilterSchoolYear, gpFilterSemester, gpFilterComputation, gpFilterFaculty, gpFilterSubject, gpFilterSection].forEach(function (select) {
        if (select) {
            select.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    applyGpFilters();
                }
            });
        }
    });
    applyGpFilters();

    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-gp-menu-toggle]') && !event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });
});
</script>
@endpush
