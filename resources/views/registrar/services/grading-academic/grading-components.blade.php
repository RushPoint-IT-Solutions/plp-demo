@extends('layouts.registrar')

@section('title', 'PLP - Grading Components')
@section('page-title', 'GRADING COMPONENTS')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <style>
            #gcTable th, #gcTable td { white-space: nowrap; vertical-align: middle; }
            #gcTable .apst-dropdown { position: fixed; }
        </style>
        <div class="ga-card ga-filter-card sched-filter-bar">
            <div class="ga-filter-grid ga-filter-grid-compact">
                <div><label class="ga-label">School Year</label><select class="app-filter-select"><option>2025-2026</option></select></div>
                <div><label class="ga-label">Semester</label><select class="app-filter-select"><option>First</option></select></div>
                <div><label class="ga-label">Section</label><select class="app-filter-select"><option>A</option></select></div>
                <div><label class="ga-label">Period</label><select class="app-filter-select"><option>-select-</option></select></div>
                <div><label class="ga-label">Subject Type</label><select class="app-filter-select"><option>-select-</option></select></div>
                <div class="ga-filter-search"><button type="button" class="pf-btn-new ga-btn ga-btn-primary">Search</button></div>
            </div>
        </div>

        <div class="ga-toolbar ga-toolbar-end">
            <button type="button" class="pf-btn-new ga-btn ga-btn-primary" onclick="document.getElementById('gaComponentsNewModal').style.display='flex'">+ New Entry</button>
        </div>

        <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table ga-table-compact app-table" id="gcTable">
                    <thead>
                        <tr>
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
                            <th style="padding: 10px 15px; text-align: center; width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025</td><td>Prelim</td><td>First</td><td>BSIT-4A</td><td>IT 4102</td>
                            <td>Written/Seatwork</td><td>1</td><td>30</td><td>No</td><td>No</td><td>Registrar</td><td>2026-03-07</td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div class="apst-action-btn" data-gc-menu-toggle="gcMenu0" aria-label="Open row actions" title="Actions" style="margin: 0 auto;">
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="apst-dropdown" id="gcMenu0">
                                    <button type="button" data-ga-open-action="view" data-ga-item="Written/Seatwork">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View
                                    </button>
                                    <button type="button" data-ga-open-action="edit" data-ga-item="Written/Seatwork">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="Written/Seatwork">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>2025</td><td>Prelim</td><td>First</td><td>BSCS-3A</td><td>CS 301</td>
                            <td>Quiz</td><td>2</td><td>30</td><td>No</td><td>No</td><td>Registrar</td><td>2026-03-07</td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div class="apst-action-btn" data-gc-menu-toggle="gcMenu1" aria-label="Open row actions" title="Actions" style="margin: 0 auto;">
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="apst-dropdown" id="gcMenu1">
                                    <button type="button" data-ga-open-action="view" data-ga-item="Quiz">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View
                                    </button>
                                    <button type="button" data-ga-open-action="edit" data-ga-item="Quiz">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="Quiz">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
        </div>

        <div class="req-modal-overlay" id="gaComponentsNewModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="max-width:800px; width: 100%; margin: 0; padding: 20px; text-align: left;">
                <h3 class="req-modal-title">ADD GRADING COMPONENTS</h3>
                
                <div class="req-modal-fields" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SCHOOL YEAR</label>
                        <input class="req-modal-input" placeholder="e.g. 2025">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SEMESTER</label>
                        <input class="req-modal-input" placeholder="e.g. First">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">PERIOD ID</label>
                        <input class="req-modal-input" placeholder="e.g. Prelim">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">CODE</label>
                        <input class="req-modal-input" placeholder="e.g. IT 4102">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SUBJECT TYPE</label>
                        <input class="req-modal-input" placeholder="Select Course">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SECTION</label>
                        <input class="req-modal-input" placeholder="e.g. BSIT-4A">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">PERCENTAGE</label>
                        <input class="req-modal-input" placeholder="%">
                    </div>
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">SEQUENCE</label>
                        <input class="req-modal-input" placeholder="#">
                    </div>
                </div>
                
                <div class="req-modal-fields" style="margin-top: 15px;">
                    <div class="req-modal-field-group">
                        <label class="req-modal-label">COMPONENT'S TITLE</label>
                        <input class="req-modal-input" placeholder="e.g. Written/Seatwork">
                    </div>
                </div>
                
                <div class="req-modal-actions" style="margin-top: 25px;">
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaComponentsNewModal').style.display='none'">Cancel</button>
                    <button type="button" class="req-btn-save" style="background:#006837;" onclick="document.getElementById('gaComponentsNewModal').style.display='none'">Save</button>
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
                    <button type="button" class="req-btn-save" style="background: #d93025;" onclick="document.getElementById('gaComponentsDeleteModal').style.display='none'">Delete</button>
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
        
        // Position menu to the left of the action button so it doesn't overflow screen
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

    function setInputsDisabled(disabled) {
        Object.keys(compInputs).forEach(function (key) {
            if (compInputs[key]) {
                compInputs[key].disabled = disabled;
            }
        });
    }

    function fillInputsFromRow(row) {
        if (!row || row.cells.length < 13) return;
        if (compInputs.sy) compInputs.sy.value = (row.cells[0].textContent || '').trim();
        if (compInputs.period) compInputs.period.value = (row.cells[1].textContent || '').trim();
        if (compInputs.semester) compInputs.semester.value = (row.cells[2].textContent || '').trim();
        if (compInputs.section) compInputs.section.value = (row.cells[3].textContent || '').trim();
        if (compInputs.courseCode) compInputs.courseCode.value = (row.cells[4].textContent || '').trim();
        if (compInputs.title) compInputs.title.value = (row.cells[5].textContent || '').trim();
        if (compInputs.sequence) compInputs.sequence.value = (row.cells[6].textContent || '').trim();
        if (compInputs.percentage) compInputs.percentage.value = (row.cells[7].textContent || '').trim();
        if (compInputs.lab) compInputs.lab.value = (row.cells[8].textContent || '').trim();
        if (compInputs.cap) compInputs.cap.value = (row.cells[9].textContent || '').trim();
        if (compInputs.user) compInputs.user.value = (row.cells[10].textContent || '').trim();
        if (compInputs.date) compInputs.date.value = (row.cells[11].textContent || '').trim();
    }

    function saveInputsToRow(row) {
        if (!row || row.cells.length < 13) return;
        row.cells[0].textContent = compInputs.sy && compInputs.sy.value ? compInputs.sy.value.trim() : row.cells[0].textContent;
        row.cells[1].textContent = compInputs.period && compInputs.period.value ? compInputs.period.value.trim() : row.cells[1].textContent;
        row.cells[2].textContent = compInputs.semester && compInputs.semester.value ? compInputs.semester.value.trim() : row.cells[2].textContent;
        row.cells[3].textContent = compInputs.section && compInputs.section.value ? compInputs.section.value.trim() : row.cells[3].textContent;
        row.cells[4].textContent = compInputs.courseCode && compInputs.courseCode.value ? compInputs.courseCode.value.trim() : row.cells[4].textContent;
        row.cells[5].textContent = compInputs.title && compInputs.title.value ? compInputs.title.value.trim() : row.cells[5].textContent;
        row.cells[6].textContent = compInputs.sequence && compInputs.sequence.value ? compInputs.sequence.value.trim() : row.cells[6].textContent;
        row.cells[7].textContent = compInputs.percentage && compInputs.percentage.value ? compInputs.percentage.value.trim() : row.cells[7].textContent;
        row.cells[8].textContent = compInputs.lab && compInputs.lab.value ? compInputs.lab.value.trim() : row.cells[8].textContent;
        row.cells[9].textContent = compInputs.cap && compInputs.cap.value ? compInputs.cap.value.trim() : row.cells[9].textContent;
        row.cells[10].textContent = compInputs.user && compInputs.user.value ? compInputs.user.value.trim() : row.cells[10].textContent;
        row.cells[11].textContent = compInputs.date && compInputs.date.value ? compInputs.date.value.trim() : row.cells[11].textContent;
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
                saveInputsToRow(activeRow);
            }
            document.getElementById('gaComponentsActionModal').style.display = 'none';
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Component updated successfully.');
            }
        }
    });

    // Close modals on outside click
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('req-modal-overlay')) {
            e.target.style.display = 'none';
        }
    });

    window.addEventListener('scroll', closeActionMenus, true);
    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-gc-menu-toggle]') && !event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });
});
</script>
@endpush
