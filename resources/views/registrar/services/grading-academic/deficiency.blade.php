@extends('layouts.registrar')

@section('title', 'PLP - Deficiency')
@section('page-title', 'DEFICIENCY')
@section('body-class', 'page-services-grading-academic')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <div id="dfStudentsListView">
            <div class="ga-toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div class="pf-search-wrap ga-search-wrap ga-search-wrap-wide" style="width: 100%; max-width: 400px; margin: 0; background: #fff; border: 1px solid #ccc; border-radius: 6px; overflow: hidden;">
                    <span class="pf-search-icon" aria-hidden="true" style="padding-left: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <input type="text" class="pf-search-input" placeholder="Search Name, Student ID, Course, Year Level..." style="border: none; background: transparent; padding-left: 42px;">
                </div>
                <button type="button" class="pf-btn-new ga-btn ga-btn-primary" onclick="document.getElementById('gaAddStudentModal').style.display='flex'">+ Add Row</button>
            </div>

            <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table ga-table-compact app-table">
                    <thead>
                        <tr>
                            <th style="padding: 12px 14px; text-align:center;">#</th>
                            <th style="padding: 12px 14px;">Student ID</th>
                            <th style="padding: 12px 14px;">Student Name</th>
                            <th style="padding: 12px 14px; text-align:center;">Course</th>
                            <th style="padding: 12px 14px; text-align:center;">Year Level</th>
                            <th style="padding: 12px 14px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <style>
                        .df-student-link { color: inherit; text-decoration: none; transition: color 0.1s; }
                        .df-student-link:hover { color: #006837; }
                    </style>
                    <tbody id="dfTbody">
                        <tr>
                            <td style="text-align:center;">1</td>
                            <td>2223A8137</td>
                            <td><a href="#" onclick="document.getElementById('dfStudentsListView').style.display='none'; document.getElementById('dfStudentDetailView').style.display='block'; return false;" class="df-student-link">Mark Jay Bares</a></td>
                            <td style="text-align:center;">BSCS</td>
                            <td style="text-align:center;">Fourth</td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:6px; justify-content:center;">
                                    <button type="button" class="ga-btn" style="padding: 4px 10px; background: #d4eedb; border-radius: 4px; color: #006837; border:none; cursor:pointer;" title="View Deficiencies Summary" onclick="document.getElementById('gaDeficiencyEmptyViewModal').style.display='flex'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">2</td>
                            <td>2223A8139</td>
                            <td><a href="#" onclick="document.getElementById('dfStudentsListView').style.display='none'; document.getElementById('dfStudentDetailView').style.display='block'; return false;" class="df-student-link">Andrea Jane Austero</a></td>
                            <td style="text-align:center;">BSCS</td>
                            <td style="text-align:center;">Fourth</td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:6px; justify-content:center;">
                                    <button type="button" class="ga-btn" style="padding: 4px 10px; background: #d4eedb; border-radius: 4px; color: #006837; border:none; cursor:pointer;" title="View Deficiencies Summary" onclick="document.getElementById('gaDeficiencyQuickViewModal').style.display='flex'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="dfStudentDetailView" style="display:none;">
            <div class="ga-toolbar ga-toolbar-start" style="margin-bottom: 20px;">
                <button type="button" class="ga-btn" onclick="document.getElementById('dfStudentDetailView').style.display='none'; document.getElementById('dfStudentsListView').style.display='block';" style="display:flex; align-items:center; gap:6px; color:#444; background:#f0f0f0; border:none; padding:8px 16px; border-radius:6px; cursor:pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back to Deficiencies
                </button>
            </div>

            <div class="ga-card ga-filter-card sched-filter-bar">
                <div class="ga-filter-grid ga-filter-grid-deficiency">
                    <div class="ga-filter-search ga-filter-search-deficiency">
                        <div class="pf-search-wrap ga-search-wrap ga-search-wrap-wide">
                            <span class="pf-search-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                            <input type="text" class="pf-search-input" placeholder="Search by student no, name, or department">
                        </div>
                    </div>
                <div>
                    <label class="ga-label">Department</label>
                    <select class="app-filter-select">
                        <option>All Departments</option>
                        <option>Library</option>
                        <option>Cashier</option>
                        <option>Registrar</option>
                    </select>
                </div>
                <div>
                    <label class="ga-label">Status</label>
                    <select class="app-filter-select">
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Completed</option>
                    </select>
                </div>
                <div>
                    <label class="ga-label">Submission Date</label>
                    <input class="ga-input" type="date" onclick="this.showPicker()">
                </div>
            </div>
        </div>

        <div class="ga-toolbar ga-toolbar-end">
            <button type="button" class="pf-btn-new ga-btn ga-btn-primary" data-ga-modal-open="gaDeficiencyNewModal">+ Add Deficiency</button>
        </div>

        <div class="svc-selected-info" style="margin-top: 10px; margin-bottom: 20px;">
            <div><strong>Section:</strong> <span id="dfInfoSection">BSIT 2-A</span></div>
            <div><strong>Subject:</strong> CC 105 (INFORMATION MANAGEMENT)</div>
            <div><strong>Schedule:</strong> T | 11:30AM-02:00PM BLDG.1 - 401 / TH | 11:30AM-02:00PM BLDG.1 - 401</div>
            <div><strong>Professor:</strong> DELA CRUZ, JUAN</div>
            <div><strong>Pre-Req:</strong> (CC104) DATA STRUCTURES AND ALGORITHMS</div>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table class="ga-table ga-table-compact app-table" id="dfTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Department</th>
                        <th>Remarks</th>
                        <th>Date Today</th>
                        <th>Submission Date</th>
                        <th>Completed</th>
                        <th>Compliance Date</th>
                        <th>Updated By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Library</td>
                        <td>Damaged Item (Math book)</td>
                        <td>03/01/2026</td>
                        <td>03/10/2026</td>
                        <td><label class="ga-check ga-check-tight"><input type="checkbox"> Cleared</label></td>
                        <td>03/10/2026</td>
                        <td>Admin 1</td>
                        <td>
                            <div class="apst-action-btn" data-df-menu-toggle="dfMenu0" aria-label="Open row actions" title="Actions">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="apst-dropdown" id="dfMenu0">
                                <button type="button" data-ga-open-action="edit" data-ga-item="Library deficiency">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="Library deficiency">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Cashier</td>
                        <td>Unsettled fee balance</td>
                        <td>03/01/2026</td>
                        <td>03/15/2026</td>
                        <td><label class="ga-check ga-check-tight"><input type="checkbox"> Cleared</label></td>
                        <td>-</td>
                        <td>Admin 1</td>
                        <td>
                            <div class="apst-action-btn" data-df-menu-toggle="dfMenu1" aria-label="Open row actions" title="Actions">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="apst-dropdown" id="dfMenu1">
                                <button type="button" data-ga-open-action="edit" data-ga-item="Cashier deficiency">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="Cashier deficiency">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        </div> <!-- End dfStudentDetailView -->

        <div class="req-modal-overlay" id="gaAddStudentModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="max-width:500px; width: 100%; margin: 0; padding: 20px; text-align: left;">
                <h3 class="req-modal-title">ADD STUDENT</h3>
                <div class="req-modal-fields" style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
                    <div class="req-modal-field-group" style="width: 100%;">
                        <label class="req-modal-label">STUDENT ID / NUMBER</label>
                        <input class="req-modal-input" type="text" id="addStudentIdInput" placeholder="Enter Student ID">
                    </div>
                    <div class="req-modal-field-group" style="width: 100%;">
                        <label class="req-modal-label">STUDENT NAME</label>
                        <input class="req-modal-input" type="text" id="addStudentNameInput" placeholder="Enter Full Name">
                    </div>
                </div>
                <div class="req-modal-actions" style="margin-top: 25px;">
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaAddStudentModal').style.display='none'">Cancel</button>
                    <button type="button" class="req-btn-save" style="background:#006837;" onclick="addNewStudentRow()">Add</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyEmptyViewModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="max-width:500px; width: 100%; margin: 0; padding: 20px; text-align: center;">
                <h3 class="req-modal-title" style="margin-bottom: 5px; text-align: center;">ACTIVE DEFICIENCIES</h3>
                <p style="color:#666; font-size: 0.95rem; margin-top: 0; margin-bottom: 20px;">Mark Jay Bares</p>
                <div style="background: #fafafa; border: 1px solid #eee; border-radius: 8px; padding: 30px; margin-bottom: 20px;">
                    <p style="margin: 0; color: #888; font-style: italic;">No active deficiencies on record.</p>
                </div>
                <div class="req-modal-actions" style="justify-content: center;">
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaDeficiencyEmptyViewModal').style.display='none'">Close</button>
                    <button type="button" class="req-btn-save" style="background:#006837;" onclick="document.getElementById('gaDeficiencyEmptyViewModal').style.display='none'; document.getElementById('dfStudentsListView').style.display='none'; document.getElementById('dfStudentDetailView').style.display='block';">Manage Records</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyQuickViewModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div class="req-modal-box" style="max-width:500px; width: 100%; margin: 0; padding: 20px; text-align: center;">
                <h3 class="req-modal-title" style="margin-bottom: 5px; text-align: center;">ACTIVE DEFICIENCIES</h3>
                <p style="color:#666; font-size: 0.95rem; margin-top: 0; margin-bottom: 20px;">Andrea Jane Austero</p>
                <div style="background: #fafafa; border: 1px solid #eee; border-radius: 8px; padding: 20px; margin-bottom: 20px; text-align: left;">
                    <ul style="margin: 0; padding: 0; list-style: none; font-size: 0.9rem; color: #444;">
                        <li style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #eaeaea; display: flex; justify-content: space-between; align-items: center;">
                            <div><strong style="color: #222;">Library:</strong> Damaged Item (Math book)</div>
                            <span style="color:#d93025; font-size:0.75rem; font-weight:600; background: #ffe5e5; padding: 3px 10px; border-radius: 12px;">Pending</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; align-items: center;">
                            <div><strong style="color: #222;">Cashier:</strong> Unsettled fee balance</div>
                            <span style="color:#d93025; font-size:0.75rem; font-weight:600; background: #ffe5e5; padding: 3px 10px; border-radius: 12px;">Pending</span>
                        </li>
                    </ul>
                </div>
                <div class="req-modal-actions" style="justify-content: center;">
                    <button type="button" class="req-btn-cancel" onclick="document.getElementById('gaDeficiencyQuickViewModal').style.display='none'">Close</button>
                    <button type="button" class="req-btn-save" style="background:#006837;" onclick="document.getElementById('gaDeficiencyQuickViewModal').style.display='none'; document.getElementById('dfStudentsListView').style.display='none'; document.getElementById('dfStudentDetailView').style.display='block';">Manage Records</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyNewModal" style="display:none;">
            <div class="req-modal-box" style="max-width:620px;">
                <h3 class="req-modal-title" id="gaDeficiencyNewTitle">ADD DEFICIENCY</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group"><label class="req-modal-label">DEPARTMENT</label><select class="req-modal-input" id="dfNewDepartment"><option>Library</option><option>Cashier</option><option>Registrar</option></select></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">SUBMISSION DATE</label><input class="req-modal-input" type="date" id="dfNewSubmissionDate" onclick="this.showPicker()"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">DATE TODAY</label><input class="req-modal-input" type="date" id="dfNewDateToday" onclick="this.showPicker()"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLIANCE DATE</label><input class="req-modal-input" type="date" id="dfNewComplianceDate" onclick="this.showPicker()"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">UPDATED BY</label><input class="req-modal-input" id="dfNewUpdatedBy" placeholder="Admin"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLETED</label><label class="ga-check"><input type="checkbox" id="dfNewCompleted"> Cleared</label></div>
                </div>
                <div class="req-modal-field-group" style="margin-top:10px;">
                    <label class="req-modal-label">REMARKS</label>
                    <input class="req-modal-input" id="dfNewRemarks" placeholder="Reason for deficiency">
                </div>
                <div class="req-modal-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-df-save-new>Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyActionModal" style="display:none;">
            <div class="req-modal-box" style="max-width:620px;">
                <h3 class="req-modal-title" id="gaDeficiencyActionTitle">EDIT DEFICIENCY</h3>
                <div class="req-modal-fields">
                    <div class="req-modal-field-group"><label class="req-modal-label">DEPARTMENT</label><select class="req-modal-input" id="dfEditDepartment"><option>Library</option><option>Cashier</option><option>Registrar</option></select></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">SUBMISSION DATE</label><input class="req-modal-input" type="date" id="dfEditSubmissionDate" onclick="this.showPicker()"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">DATE TODAY</label><input class="req-modal-input" type="date" id="dfEditDateToday" onclick="this.showPicker()"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLIANCE DATE</label><input class="req-modal-input" type="date" id="dfEditComplianceDate" onclick="this.showPicker()"></div>
                </div>
                <div class="req-modal-fields" style="margin-top:10px;">
                    <div class="req-modal-field-group"><label class="req-modal-label">UPDATED BY</label><input class="req-modal-input" id="dfEditUpdatedBy"></div>
                    <div class="req-modal-field-group"><label class="req-modal-label">COMPLETED</label><label class="ga-check"><input type="checkbox" id="dfEditCompleted"> Cleared</label></div>
                </div>
                <div class="req-modal-field-group" style="margin-top:10px;">
                    <label class="req-modal-label">REMARKS</label>
                    <input class="req-modal-input" id="dfEditRemarks">
                </div>
                <div class="req-modal-actions">
                    <button type="button" class="req-btn-cancel" data-ga-close>Cancel</button>
                    <button type="button" class="req-btn-save" data-ga-confirm-action>Save</button>
                </div>
            </div>
        </div>

        <div class="req-modal-overlay" id="gaDeficiencyDeleteModal" style="display:none;">
            <div class="req-modal-box req-modal-success" style="min-width:300px;">
                <h3 class="req-modal-title" style="color:#c0392b;" id="gaDeficiencyDeleteTitle">DELETE DEFICIENCY</h3>
                <p id="gaDeficiencyActionText" style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;">Are you sure you want to delete this deficiency?</p>
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
function addNewStudentRow() {
    var idInput = document.getElementById('addStudentIdInput');
    var nameInput = document.getElementById('addStudentNameInput');
    var id = idInput.value.trim();
    var name = nameInput.value.trim();
    
    if (!id || !name) {
        alert('Please enter both Student ID and Name.');
        return;
    }
    
    var tbody = document.getElementById('dfTbody');
    var rowCount = tbody.querySelectorAll('tr').length + 1;
    
    var tr = document.createElement('tr');
    tr.innerHTML = `
        <td style="text-align:center;">${rowCount}</td>
        <td>${id}</td>
        <td><a href="#" onclick="document.getElementById('dfStudentsListView').style.display='none'; document.getElementById('dfStudentDetailView').style.display='block'; return false;" class="df-student-link">${name}</a></td>
        <td style="text-align:center;">-</td>
        <td style="text-align:center;">-</td>
        <td style="text-align:center;">
            <div style="display:flex; gap:6px; justify-content:center;">
                <button type="button" class="ga-btn" style="padding: 4px 10px; background: #d4eedb; border-radius: 4px; color: #006837; border:none; cursor:pointer;" title="View Deficiencies Summary" onclick="document.getElementById('gaDeficiencyEmptyViewModal').style.display='flex'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </td>
    `;
    
    tbody.appendChild(tr);
    document.getElementById('gaAddStudentModal').style.display = 'none';
    idInput.value = '';
    nameInput.value = '';
}

document.addEventListener('DOMContentLoaded', function () {
    var page = document.querySelector('.ga-page');
    if (!page) return;

    var table = page.querySelector('#dfTable');
    var actionModal = document.getElementById('gaDeficiencyActionModal');
    var deleteModal = document.getElementById('gaDeficiencyDeleteModal');
    var actionTitle = document.getElementById('gaDeficiencyActionTitle');
    var actionText = document.getElementById('gaDeficiencyActionText');
    var dfEditDepartment = document.getElementById('dfEditDepartment');
    var dfEditRemarks = document.getElementById('dfEditRemarks');
    var dfEditDateToday = document.getElementById('dfEditDateToday');
    var dfEditSubmissionDate = document.getElementById('dfEditSubmissionDate');
    var dfEditCompleted = document.getElementById('dfEditCompleted');
    var dfEditComplianceDate = document.getElementById('dfEditComplianceDate');
    var dfEditUpdatedBy = document.getElementById('dfEditUpdatedBy');
    var nextMenuIndex = page.querySelectorAll('[data-df-menu-toggle]').length;
    var activeRow = null;
    var activeAction = 'edit';

    function toInputDate(value) {
        if (!value || value === '-') return '';
        var parts = value.split('/');
        if (parts.length !== 3) return '';
        return parts[2] + '-' + parts[0].padStart(2, '0') + '-' + parts[1].padStart(2, '0');
    }

    function toDisplayDate(value) {
        if (!value) return '-';
        var parts = value.split('-');
        if (parts.length !== 3) return value;
        return parts[1] + '/' + parts[2] + '/' + parts[0];
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
        var left = rect.right + spacing;
        if (left + menuWidth > window.innerWidth - spacing) {
            left = rect.left - menuWidth - spacing;
        }
        if (left < spacing) left = spacing;
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

    function fillEditForm(row) {
        if (!row || row.cells.length < 9) return;
        if (dfEditDepartment) dfEditDepartment.value = (row.cells[1].textContent || '').trim();
        if (dfEditRemarks) dfEditRemarks.value = (row.cells[2].textContent || '').trim();
        if (dfEditDateToday) dfEditDateToday.value = toInputDate((row.cells[3].textContent || '').trim());
        if (dfEditSubmissionDate) dfEditSubmissionDate.value = toInputDate((row.cells[4].textContent || '').trim());
        if (dfEditCompleted) dfEditCompleted.checked = (row.cells[5].textContent || '').toLowerCase().indexOf('cleared') !== -1;
        if (dfEditComplianceDate) dfEditComplianceDate.value = toInputDate((row.cells[6].textContent || '').trim());
        if (dfEditUpdatedBy) dfEditUpdatedBy.value = (row.cells[7].textContent || '').trim();
    }

    function saveEditForm(row) {
        if (!row || row.cells.length < 9) return;
        row.cells[1].textContent = dfEditDepartment ? dfEditDepartment.value.trim() : row.cells[1].textContent;
        row.cells[2].textContent = dfEditRemarks ? dfEditRemarks.value.trim() : row.cells[2].textContent;
        row.cells[3].textContent = dfEditDateToday ? toDisplayDate(dfEditDateToday.value) : row.cells[3].textContent;
        row.cells[4].textContent = dfEditSubmissionDate ? toDisplayDate(dfEditSubmissionDate.value) : row.cells[4].textContent;
        row.cells[5].innerHTML = '<label class="ga-check ga-check-tight"><input type="checkbox"' + ((dfEditCompleted && dfEditCompleted.checked) ? ' checked' : '') + '> Cleared</label>';
        row.cells[6].textContent = dfEditComplianceDate ? toDisplayDate(dfEditComplianceDate.value) : row.cells[6].textContent;
        row.cells[7].textContent = dfEditUpdatedBy ? dfEditUpdatedBy.value.trim() : row.cells[7].textContent;
    }

    function buildActionCell(item, menuId) {
        return '' +
            '<td>' +
                '<div class="apst-action-btn" data-df-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
                '<div class="apst-dropdown" id="' + menuId + '">' +
                    '<button type="button" data-ga-open-action="edit" data-ga-item="' + item + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>' +
                    '<button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="' + item + '"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>Delete</button>' +
                '</div>' +
            '</td>';
    }

    function reindexRows() {
        if (!table) return;
        table.querySelectorAll('tbody tr').forEach(function (row, index) {
            row.cells[0].textContent = index + 1;
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

    page.addEventListener('click', function (event) {
        var menuToggle = event.target.closest('[data-df-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            toggleActionMenu(menuToggle.getAttribute('data-df-menu-toggle'), menuToggle);
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
            var item = actionBtn.getAttribute('data-ga-item') || 'deficiency row';
            activeRow = actionBtn.closest('tr');
            activeAction = action;

            if (action === 'delete') {
                actionText.textContent = 'Are you sure you want to delete ' + item + '?';
                openModal('gaDeficiencyDeleteModal');
                return;
            }

            actionTitle.textContent = 'EDIT DEFICIENCY';
            fillEditForm(activeRow);
            openModal('gaDeficiencyActionModal');
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

        if (event.target.matches('[data-df-save-new]')) {
            var dfNewDepartment = document.getElementById('dfNewDepartment');
            var dfNewRemarks = document.getElementById('dfNewRemarks');
            var dfNewDateToday = document.getElementById('dfNewDateToday');
            var dfNewSubmissionDate = document.getElementById('dfNewSubmissionDate');
            var dfNewCompleted = document.getElementById('dfNewCompleted');
            var dfNewComplianceDate = document.getElementById('dfNewComplianceDate');
            var dfNewUpdatedBy = document.getElementById('dfNewUpdatedBy');

            var dept = dfNewDepartment ? dfNewDepartment.value.trim() : '';
            var remarks = dfNewRemarks ? dfNewRemarks.value.trim() : '';
            if (!dept || !remarks) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Please complete Department and Remarks.');
                }
                return;
            }

            var menuId = 'dfMenu' + nextMenuIndex;
            nextMenuIndex += 1;
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>0</td>' +
                '<td>' + dept + '</td>' +
                '<td>' + remarks + '</td>' +
                '<td>' + toDisplayDate(dfNewDateToday ? dfNewDateToday.value : '') + '</td>' +
                '<td>' + toDisplayDate(dfNewSubmissionDate ? dfNewSubmissionDate.value : '') + '</td>' +
                '<td><label class="ga-check ga-check-tight"><input type="checkbox"' + ((dfNewCompleted && dfNewCompleted.checked) ? ' checked' : '') + '> Cleared</label></td>' +
                '<td>' + toDisplayDate(dfNewComplianceDate ? dfNewComplianceDate.value : '') + '</td>' +
                '<td>' + ((dfNewUpdatedBy && dfNewUpdatedBy.value.trim()) ? dfNewUpdatedBy.value.trim() : 'Admin') + '</td>' +
                buildActionCell(dept + ' deficiency', menuId);
            table.querySelector('tbody').appendChild(tr);
            reindexRows();
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Deficiency added successfully.');
            }
            closeModal(document.getElementById('gaDeficiencyNewModal'));
            return;
        }

        if (event.target.matches('[data-ga-confirm-action]')) {
            if (activeAction === 'edit' && activeRow) {
                saveEditForm(activeRow);
            }
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Deficiency updated successfully.');
            }
            closeModal(actionModal);
            return;
        }

        if (event.target.matches('[data-ga-confirm-delete]')) {
            if (activeRow) {
                activeRow.remove();
                reindexRows();
            }
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Deficiency deleted successfully.');
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
    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-df-menu-toggle]') && !event.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }
    });
});
</script>
@endpush
