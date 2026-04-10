@extends('layouts.registrar')

@section('title', 'PLP - Grading Components')
@section('page-title', 'GRADING COMPONENTS')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <div class="ga-card ga-filter-card sched-filter-bar ga-periods-filter">
            <div class="ga-filter-grid ga-filter-grid-periods-lite">
                <div><label class="ga-label">School Year</label><select class="app-filter-select" id="gcFilterSchoolYear"><option value="">All</option><option>2025-2026</option><option>2024-2025</option><option>2023-2024</option></select></div>
                <div><label class="ga-label">Semester</label><select class="app-filter-select" id="gcFilterSemester"><option value="">All</option><option>First</option><option>Second</option><option>Summer</option></select></div>
                <div><label class="ga-label">Section</label><select class="app-filter-select" id="gcFilterSection"><option value="">All</option><option>A</option><option>B</option><option>BSCS 3A</option><option>BSIT 2B</option></select></div>
                <div><label class="ga-label">Period</label><select class="app-filter-select" id="gcFilterPeriod"><option value="">All</option><option>1</option><option>2</option><option>3</option></select></div>
                <div><label class="ga-label">Subject Type</label><select class="app-filter-select" id="gcFilterSubjectType"><option value="">All</option><option>Core</option><option>Applied</option><option>Specialized</option><option>No</option></select></div>
                <div><label class="ga-label">Course Code</label><select class="app-filter-select" id="gcFilterCourseCode"><option value="">All</option><option>CS 301</option><option>IT 4102</option><option>CS301</option><option>IT201</option><option>MATH101</option></select></div>
                <div class="ga-filter-inline-action">
                    <label class="ga-label">&nbsp;</label>
                    <button type="button" class="pf-btn-new ga-btn ga-btn-primary ga-filter-update-btn" id="gcFilterSearch">Search</button>
                </div>
            </div>
        </div>

        <div class="ga-toolbar ga-toolbar-end">
            <button type="button" class="pf-btn-new ga-btn ga-btn-primary" onclick="document.getElementById('gaComponentsNewModal').style.display='flex'">+ New Entry</button>
        </div>

        <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table ga-table-compact app-table" id="gcTable">
                    <thead>
                        <tr>
                            <th style="padding: 10px 15px; text-align: center; width: 80px;">Action</th>
                            <th style="padding: 10px 15px; text-align: left;">SY</th>
                            <th style="padding: 10px 15px; text-align: left;">Period</th>
                            <th style="padding: 10px 15px; text-align: left;">Semester</th>
                            <th style="padding: 10px 15px; text-align: left;">Section</th>
                            <th style="padding: 10px 15px; text-align: left;">Course Code</th>
                            <th style="padding: 10px 15px; text-align: left;">Components Title</th>
                            <th style="padding: 10px 15px; text-align: left;">Sequence #</th>
                            <th style="padding: 10px 15px; text-align: left;">Percentage</th>
                            <th style="padding: 10px 15px; text-align: left;">Lab/No Lab</th>
                            <th style="padding: 10px 15px; text-align: left;">CAP</th>
                            <th style="padding: 10px 15px; text-align: left;">User</th>
                            <th style="padding: 10px 15px; text-align: left;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gradingComponents as $index => $component)
                        <tr data-grading-component-id="{{ $component->id }}">
                            <td style="text-align: center; vertical-align: middle;">
                                <div class="apst-action-btn" data-gc-menu-toggle="gcMenu{{ $index }}" aria-label="Open row actions" title="Actions" style="margin: 0 auto;">
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="apst-dropdown" id="gcMenu{{ $index }}">
                                    <button type="button" data-ga-open-action="view" data-ga-item="{{ $component->title }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View
                                    </button>
                                    <button type="button" data-ga-open-action="edit" data-ga-item="{{ $component->title }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="{{ $component->title }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                            <td>{{ $component->school_year }}</td>
                            <td>{{ $component->period }}</td>
                            <td>{{ $component->semester }}</td>
                            <td>{{ $component->section }}</td>
                            <td>{{ $component->course_code }}</td>
                            <td>{{ $component->title }}</td>
                            <td>{{ $component->sequence_no }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $component->percentage, 2, '.', ''), '0'), '.') }}</td>
                            <td>{{ $component->lab_mode ?: '-' }}</td>
                            <td>{{ $component->cap ?: '-' }}</td>
                            <td>{{ $component->updated_by ?: '-' }}</td>
                            <td>{{ optional($component->effective_date)->format('Y-m-d') ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="13" style="text-align:center; color:#666;">No grading components found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
        </div>

        <div class="req-modal-overlay" id="gaComponentsNewModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="max-width:800px; width: 100%; margin: 0; padding: 20px; text-align: left;">
                <h3 class="req-modal-title">ADD GRADING COMPONENTS</h3>
                
                <div class="req-modal-fields" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SCHOOL YEAR:</label>
                        <select class="req-modal-input" id="gaNewCompSy">
                            <option value="">SY</option>
                            <option>2025-2026</option>
                            <option>2024-2025</option>
                            <option>2023-2024</option>
                        </select>
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SEMESTER:</label>
                        <select class="req-modal-input" id="gaNewCompSemester">
                            <option value="">select semester</option>
                            <option>First</option>
                            <option>Second</option>
                            <option>Summer</option>
                        </select>
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">PERIOD ID:</label>
                        <select class="req-modal-input" id="gaNewCompPeriod">
                            <option value="">select all</option>
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                        </select>
                    </div>
                </div>

                <div class="req-modal-fields" style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; margin-top: 15px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SUBJECT TYPE</label>
                        <select class="req-modal-input" id="gaNewCompLab">
                            <option value="">Type</option>
                            <option>Core</option>
                            <option>Applied</option>
                            <option>Specialized</option>
                        </select>
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">COURSE:</label>
                        <select class="req-modal-input" id="gaNewCompCourseCode">
                            <option value="">Select Course</option>
                            <option>CS301</option>
                            <option>IT201</option>
                            <option>MATH101</option>
                        </select>
                    </div>
                </div>

                <div class="req-modal-fields" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SECTION:</label>
                        <select class="req-modal-input" id="gaNewCompSection">
                            <option value="">Select Section</option>
                            <option>A</option>
                            <option>B</option>
                            <option>BSCS 3A</option>
                            <option>BSIT 2B</option>
                        </select>
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">PERCENTAGE</label>
                        <input class="req-modal-input" id="gaNewCompPercentage" placeholder="%">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SEQUENCE</label>
                        <input class="req-modal-input" id="gaNewCompSequence" placeholder="#">
                    </div>
                </div>
                
                <div class="req-modal-fields" style="margin-top: 15px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">COMPONENT'S TITLE:</label>
                        <input class="req-modal-input" id="gaNewCompTitle" placeholder="Title">
                    </div>
                </div>
                
                <div class="req-modal-actions" style="margin-top: 25px; justify-content: space-between;">
                    <button type="button" class="req-btn-save" style="background:#ff4d4f;" data-gc-clear-new>Clear Entries</button>
                    <div>
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaComponentsNewModal').style.display='none'">Cancel</button>
                    <button type="button" class="req-btn-save" style="background:#006837;" data-gc-save-new>Save</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaComponentsActionModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="max-width:800px; width: 100%; margin: 0; padding: 20px; text-align: left;">
                <h3 class="req-modal-title" id="gaComponentsActionTitle">ACTION</h3>
                
                <div class="req-modal-fields" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">SCHOOL YEAR</label><input class="req-modal-input" id="gaCompSy"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">PERIOD</label><input class="req-modal-input" id="gaCompPeriod"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">SEMESTER</label><input class="req-modal-input" id="gaCompSemester"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">SECTION</label><input class="req-modal-input" id="gaCompSection"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COURSE CODE</label><input class="req-modal-input" id="gaCompCourseCode"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPONENT TITLE</label><input class="req-modal-input" id="gaCompTitle"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">SEQUENCE #</label><input class="req-modal-input" id="gaCompSequence"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">PERCENTAGE</label><input class="req-modal-input" id="gaCompPercentage"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">LAB/NO LAB</label><input class="req-modal-input" id="gaCompLab"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">CAP</label><input class="req-modal-input" id="gaCompCap"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">USER</label><input class="req-modal-input" id="gaCompUser"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">DATE</label><input class="req-modal-input" id="gaCompDate" type="date" onclick="this.showPicker()"></div>
                </div>

                <div class="req-modal-actions" style="margin-top: 25px;">
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaComponentsActionModal').style.display='none'">Close</button>
                    <button type="button" class="req-btn-save" style="background:#006837;" data-ga-confirm-action>Confirm</button>
                </div>
            </div>
        </div>
        
        <div class="req-modal-overlay" id="gaComponentsDeleteModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="margin: 0; padding: 20px; max-width: 400px; width: 100%; text-align: center;">
                <h3 class="req-modal-title" style="color: #d93025; font-size: 1.1rem; text-align: center;">DELETE COMPONENT</h3>
                <p style="font-size: 0.9rem; color: #444; margin: 15px 0 25px;">Are you sure you want to delete this grading component?</p>
                <div class="req-modal-actions" style="justify-content: center;">
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaComponentsDeleteModal').style.display='none'">Cancel</button>
                    <button type="button" class="req-btn-save" style="background: #d93025;" data-gc-confirm-delete>Delete</button>
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

    var table = document.getElementById('gcTable');
    var gcFilterSchoolYear = document.getElementById('gcFilterSchoolYear');
    var gcFilterSemester = document.getElementById('gcFilterSemester');
    var gcFilterSection = document.getElementById('gcFilterSection');
    var gcFilterPeriod = document.getElementById('gcFilterPeriod');
    var gcFilterSubjectType = document.getElementById('gcFilterSubjectType');
    var gcFilterCourseCode = document.getElementById('gcFilterCourseCode');
    var gcFilterSearch = document.getElementById('gcFilterSearch');
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    var gcStoreUrl = @json(route('registrar.services.grading-academic.grading-components.store'));
    var gcUpdateUrlTemplate = @json(route('registrar.services.grading-academic.grading-components.update', ['gradingComponent' => '__ID__']));
    var gcDestroyUrlTemplate = @json(route('registrar.services.grading-academic.grading-components.destroy', ['gradingComponent' => '__ID__']));

    function closeActionMenus() {
        page.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
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
        var leftPosition = rect.right + 4;

        menu.style.right = 'auto';
        if ((leftPosition + 180) > window.innerWidth) {
            leftPosition = Math.max(8, rect.left - 184);
        }
        menu.style.left = leftPosition + 'px';
        
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

    page.addEventListener('click', function(e) {
        var toggleBtn = e.target.closest('[data-gc-menu-toggle]');
        if (toggleBtn) {
            e.stopPropagation();
            toggleActionMenu(toggleBtn.getAttribute('data-gc-menu-toggle'), toggleBtn);
            return;
        }

        if (!e.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });

    // Handle view/edit/delete actions
    var newCompInputs = {
        sy: document.getElementById('gaNewCompSy'),
        period: document.getElementById('gaNewCompPeriod'),
        semester: document.getElementById('gaNewCompSemester'),
        section: document.getElementById('gaNewCompSection'),
        courseCode: document.getElementById('gaNewCompCourseCode'),
        title: document.getElementById('gaNewCompTitle'),
        sequence: document.getElementById('gaNewCompSequence'),
        percentage: document.getElementById('gaNewCompPercentage'),
        lab: document.getElementById('gaNewCompLab')
    };
    var actionConfirmBtn = page.querySelector('#gaComponentsActionModal [data-ga-confirm-action]');
    var compInputs = {
        sy: document.getElementById('gaCompSy'),
        period: document.getElementById('gaCompPeriod'),
        semester: document.getElementById('gaCompSemester'),
        section: document.getElementById('gaCompSection'),
        courseCode: document.getElementById('gaCompCourseCode'),
        title: document.getElementById('gaCompTitle'),
        sequence: document.getElementById('gaCompSequence'),
        percentage: document.getElementById('gaCompPercentage'),
        lab: document.getElementById('gaCompLab'),
        cap: document.getElementById('gaCompCap'),
        user: document.getElementById('gaCompUser'),
        date: document.getElementById('gaCompDate')
    };
    var activeRow = null;
    var activeAction = 'view';

    function gcBuildUrl(template, id) {
        return String(template).replace('__ID__', String(id));
    }

    function normalizeText(value) {
        return String(value || '').trim().toLowerCase();
    }

    function applyGcFilters() {
        if (!table || !table.tBodies.length) return;
        var tbody = table.tBodies[0];
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-grading-component-id]'));
        var visibleCount = 0;

        rows.forEach(function (row) {
            if (row.cells.length < 13) return;

            var rowSchoolYear = normalizeText(row.cells[1].textContent);
            var rowPeriod = normalizeText(row.cells[2].textContent);
            var rowSemester = normalizeText(row.cells[3].textContent);
            var rowSection = normalizeText(row.cells[4].textContent);
            var rowCourseCode = normalizeText(row.cells[5].textContent);
            var rowSubjectType = normalizeText(row.cells[9].textContent);

            var schoolYearOk = !gcFilterSchoolYear || !gcFilterSchoolYear.value || rowSchoolYear === normalizeText(gcFilterSchoolYear.value);
            var semesterOk = !gcFilterSemester || !gcFilterSemester.value || rowSemester === normalizeText(gcFilterSemester.value);
            var sectionOk = !gcFilterSection || !gcFilterSection.value || rowSection.indexOf(normalizeText(gcFilterSection.value)) !== -1;
            var periodOk = !gcFilterPeriod || !gcFilterPeriod.value || rowPeriod === normalizeText(gcFilterPeriod.value);
            var subjectTypeOk = !gcFilterSubjectType || !gcFilterSubjectType.value || rowSubjectType.indexOf(normalizeText(gcFilterSubjectType.value)) !== -1;
            var courseCodeOk = !gcFilterCourseCode || !gcFilterCourseCode.value || rowCourseCode.indexOf(normalizeText(gcFilterCourseCode.value)) !== -1;

            var visible = schoolYearOk && semesterOk && sectionOk && periodOk && subjectTypeOk && courseCodeOk;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount += 1;
        });

        var emptyRow = tbody.querySelector('#gcFilterEmptyRow');
        if (!visibleCount) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'gcFilterEmptyRow';
                emptyRow.innerHTML = '<td colspan="13" style="text-align:center; color:#666;">No matching grading components found.</td>';
                tbody.appendChild(emptyRow);
            }
        } else if (emptyRow) {
            emptyRow.parentNode.removeChild(emptyRow);
        }
    }

    function gcRequest(url, method, payload) {
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

    function getPayloadFromInputs(inputs) {
        return {
            school_year: inputs.sy && inputs.sy.value ? inputs.sy.value.trim() : '',
            period: inputs.period && inputs.period.value ? inputs.period.value.trim() : '',
            semester: inputs.semester && inputs.semester.value ? inputs.semester.value.trim() : '',
            section: inputs.section && inputs.section.value ? inputs.section.value.trim() : '',
            course_code: inputs.courseCode && inputs.courseCode.value ? inputs.courseCode.value.trim() : '',
            title: inputs.title && inputs.title.value ? inputs.title.value.trim() : '',
            sequence_no: inputs.sequence && inputs.sequence.value ? inputs.sequence.value.trim() : '',
            percentage: inputs.percentage && inputs.percentage.value ? inputs.percentage.value.trim() : '',
            lab_mode: inputs.lab && inputs.lab.value ? inputs.lab.value.trim() : null,
            cap: inputs.cap && inputs.cap.value ? inputs.cap.value.trim() : null,
            updated_by: inputs.user && inputs.user.value ? inputs.user.value.trim() : 'Registrar',
            effective_date: inputs.date && inputs.date.value ? inputs.date.value : null
        };
    }

    function resetNewForm() {
        Object.keys(newCompInputs).forEach(function (key) {
            if (newCompInputs[key]) {
                newCompInputs[key].value = '';
            }
        });
    }

    function setInputsDisabled(disabled) {
        Object.keys(compInputs).forEach(function (key) {
            if (compInputs[key]) {
                compInputs[key].disabled = disabled;
            }
        });
    }

    function fillInputsFromRow(row) {
        if (!row || row.cells.length < 13) return;
        if (compInputs.sy) compInputs.sy.value = (row.cells[1].textContent || '').trim();
        if (compInputs.period) compInputs.period.value = (row.cells[2].textContent || '').trim();
        if (compInputs.semester) compInputs.semester.value = (row.cells[3].textContent || '').trim();
        if (compInputs.section) compInputs.section.value = (row.cells[4].textContent || '').trim();
        if (compInputs.courseCode) compInputs.courseCode.value = (row.cells[5].textContent || '').trim();
        if (compInputs.title) compInputs.title.value = (row.cells[6].textContent || '').trim();
        if (compInputs.sequence) compInputs.sequence.value = (row.cells[7].textContent || '').trim();
        if (compInputs.percentage) compInputs.percentage.value = (row.cells[8].textContent || '').trim();
        if (compInputs.lab) compInputs.lab.value = (row.cells[9].textContent || '').trim();
        if (compInputs.cap) compInputs.cap.value = (row.cells[10].textContent || '').trim();
        if (compInputs.user) compInputs.user.value = (row.cells[11].textContent || '').trim();
        if (compInputs.date) compInputs.date.value = (row.cells[12].textContent || '').trim();
    }

    function saveInputsToRow(row) {
        if (!row || row.cells.length < 13) return;
        row.cells[1].textContent = compInputs.sy && compInputs.sy.value ? compInputs.sy.value.trim() : row.cells[1].textContent;
        row.cells[2].textContent = compInputs.period && compInputs.period.value ? compInputs.period.value.trim() : row.cells[2].textContent;
        row.cells[3].textContent = compInputs.semester && compInputs.semester.value ? compInputs.semester.value.trim() : row.cells[3].textContent;
        row.cells[4].textContent = compInputs.section && compInputs.section.value ? compInputs.section.value.trim() : row.cells[4].textContent;
        row.cells[5].textContent = compInputs.courseCode && compInputs.courseCode.value ? compInputs.courseCode.value.trim() : row.cells[5].textContent;
        row.cells[6].textContent = compInputs.title && compInputs.title.value ? compInputs.title.value.trim() : row.cells[6].textContent;
        row.cells[7].textContent = compInputs.sequence && compInputs.sequence.value ? compInputs.sequence.value.trim() : row.cells[7].textContent;
        row.cells[8].textContent = compInputs.percentage && compInputs.percentage.value ? compInputs.percentage.value.trim() : row.cells[8].textContent;
        row.cells[9].textContent = compInputs.lab && compInputs.lab.value ? compInputs.lab.value.trim() : row.cells[9].textContent;
        row.cells[10].textContent = compInputs.cap && compInputs.cap.value ? compInputs.cap.value.trim() : row.cells[10].textContent;
        row.cells[11].textContent = compInputs.user && compInputs.user.value ? compInputs.user.value.trim() : row.cells[11].textContent;
        row.cells[12].textContent = compInputs.date && compInputs.date.value ? compInputs.date.value.trim() : row.cells[12].textContent;
    }

    page.addEventListener('click', function (event) {
        var actionBtn = event.target.closest('[data-ga-open-action]');
        if (actionBtn) {
            closeActionMenus();
            
            var action = actionBtn.getAttribute('data-ga-open-action') || 'view';
            activeRow = actionBtn.closest('tr');
            activeAction = action;

            if (action === 'delete') {
                document.getElementById('gaComponentsDeleteModal').style.display = 'flex';
                return;
            }

            fillInputsFromRow(activeRow);
            setInputsDisabled(action === 'view');

            var actionTitle = document.getElementById('gaComponentsActionTitle');
            if (actionTitle) {
                actionTitle.textContent = (action === 'view' ? 'VIEW COMPONENT' : 'EDIT COMPONENT');
            }

            if (actionConfirmBtn) {
                if (action === 'view') {
                    actionConfirmBtn.style.display = 'none';
                } else {
                    actionConfirmBtn.style.display = 'inline-block';
                    actionConfirmBtn.textContent = 'Save';
                }
            }

            document.getElementById('gaComponentsActionModal').style.display = 'flex';
            return;
        }

        if (event.target.matches('[data-ga-confirm-action]')) {
            if (activeAction === 'edit' && activeRow && table) {
                var recordId = activeRow.getAttribute('data-grading-component-id');
                if (!recordId) {
                    alert('Missing grading component id.');
                    return;
                }

                gcRequest(gcBuildUrl(gcUpdateUrlTemplate, recordId), 'PUT', getPayloadFromInputs(compInputs)).then(function () {
                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Component updated successfully.');
                    }
                    window.location.reload();
                }).catch(function (error) {
                    alert(error.message || 'Unable to update component.');
                });
                return;
            }
            document.getElementById('gaComponentsActionModal').style.display = 'none';
            return;
        }

        if (event.target.matches('[data-gc-save-new]')) {
            gcRequest(gcStoreUrl, 'POST', getPayloadFromInputs(newCompInputs)).then(function () {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Component added successfully.');
                }
                document.getElementById('gaComponentsNewModal').style.display = 'none';
                resetNewForm();
                window.location.reload();
            }).catch(function (error) {
                alert(error.message || 'Unable to save component.');
            });
            return;
        }

        if (event.target.matches('[data-gc-clear-new]')) {
            resetNewForm();
            return;
        }

        if (event.target.matches('[data-gc-confirm-delete]')) {
            if (!activeRow) {
                document.getElementById('gaComponentsDeleteModal').style.display = 'none';
                return;
            }

            var recordId = activeRow.getAttribute('data-grading-component-id');
            if (!recordId) {
                alert('Missing grading component id.');
                return;
            }

            gcRequest(gcBuildUrl(gcDestroyUrlTemplate, recordId), 'DELETE').then(function () {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Component deleted successfully.');
                }
                document.getElementById('gaComponentsDeleteModal').style.display = 'none';
                window.location.reload();
            }).catch(function (error) {
                alert(error.message || 'Unable to delete component.');
            });
        }
    });

    // Close modals on outside click
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('req-modal-overlay')) {
            e.target.style.display = 'none';
        }
    });

    window.addEventListener('scroll', closeActionMenus, true);
    if (gcFilterSearch) {
        gcFilterSearch.addEventListener('click', applyGcFilters);
    }
    [gcFilterSchoolYear, gcFilterSemester, gcFilterSection, gcFilterPeriod, gcFilterSubjectType, gcFilterCourseCode].forEach(function (select) {
        if (select) {
            select.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    applyGcFilters();
                }
            });
        }
    });
    applyGcFilters();

    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-gc-menu-toggle]') && !event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });
});
</script>
@endpush
