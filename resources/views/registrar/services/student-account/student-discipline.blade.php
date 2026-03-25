@extends('layouts.registrar')

@section('title', 'PLP - Student Discipline')
@section('page-title', 'STUDENT DISCIPLINE')
@section('body-class', 'page-student-account page-student-discipline')

@section('content')
<div class="pf-page">
    <div class="ga-page sd-page">
        <div id="sdListView">
            <div class="ga-card ga-filter-card sched-filter-bar sd-filter-card">
                <div class="sd-top-row">
                    <div class="sd-search-card">
                        <div class="sd-filter-title">Filter Students</div>
                        <div class="sd-field-grid">
                            <div class="sd-criteria-item">
                                <label class="app-filter-label" for="sdStudentId">Student ID</label>
                                <input id="sdStudentId" type="text" class="pf-search-input" placeholder="Student ID">
                            </div>
                            <div class="sd-criteria-item">
                                <label class="app-filter-label" for="sdStudentTypeFilter">Student Type</label>
                                <select id="sdStudentTypeFilter" class="app-filter-select">
                                    <option value="">All Types</option>
                                    <option value="Old">Old</option>
                                    <option value="New">New</option>
                                    <option value="Transferee">Transferee</option>
                                </select>
                            </div>
                            <div class="sd-criteria-item">
                                <label class="app-filter-label" for="sdFullName">Full Name</label>
                                <input id="sdFullName" type="text" class="pf-search-input" placeholder="Last Name, First Name">
                            </div>
                        </div>

                        <div class="sd-filter-actions">
                            <button type="button" class="pf-btn-new" onclick="sdFilterStudents()">Search</button>
                            <button type="button" class="sd-clear-btn" onclick="sdClearFilters()">Clear Entries</button>
                        </div>
                    </div>

                    <div class="sd-filter-divider"></div>

                    <div class="sd-system-card">
                        <div class="sd-filter-title">System Configuration</div>
                        <div class="sd-system-grid">
                            <div class="sd-filter-item">
                                <label class="app-filter-label" for="sdSchoolYear">School Year:</label>
                                <select id="sdSchoolYear" class="app-filter-select">
                                    <option value="2025-2026" selected>2025-2026</option>
                                    <option value="2024-2025">2024-2025</option>
                                </select>
                            </div>
                            <div class="sd-filter-item">
                                <label class="app-filter-label" for="sdTerm">Term:</label>
                                <select id="sdTerm" class="app-filter-select">
                                    <option value="First" selected>First</option>
                                    <option value="Second">Second</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                            <button type="button" class="pf-btn-new sd-set-btn" onclick="sdApplySystemConfig()">Set</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sd-table-head">
                <button type="button" class="pf-btn-new sd-add-student-btn" onclick="sdOpenAddStudentModal()">+ Add Student</button>
            </div>

            <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table app-table" id="sdStudentsTable">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Degree Program</th>
                            <th>Date Of Birth</th>
                            <th>Gender</th>
                            <th>Student Type</th>
                            <th style="width:70px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="sdStudentsBody"></tbody>
                </table>
            </div>
        </div>

        <div id="sdDetailView" style="display:none;">
            <div class="sd-detail-topbar">
                <button type="button" class="eval-btn-outline" onclick="sdBackToList()">Back</button>
            </div>

            <div class="sd-student-banner">
                <div><span>Student ID:</span> <strong id="sdBannerStudentId">-</strong></div>
                <div><span>Student Name:</span> <strong id="sdBannerStudentName">-</strong></div>
            </div>

            <div class="sd-detail-head">
                <div class="sd-detail-title">STUDENT CONDUCT</div>
                <div class="sd-detail-actions">
                    <button type="button" class="sd-new-btn" onclick="sdOpenRecordModal()">+ New Record</button>
                    <button type="button" class="gs-view-btn" onclick="sdPrintRecord()">Print Record</button>
                </div>
            </div>

            <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table app-table" id="sdConductTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Incident Type</th>
                            <th>Action Type</th>
                            <th>Completed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="sdConductBody">
                        <tr class="sd-empty-row">
                            <td colspan="5">List Empty.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdRecordModal" style="display:none;">
    <div class="req-modal-box sd-modal-box">
        <div class="sd-modal-section">
            <div class="sd-modal-section-head">
                <div class="sd-modal-title">INCIDENT</div>
                <label class="setup-checkbox-label sd-section-toggle">
                    <input id="sdWalkIn" type="checkbox" class="req-checkbox-input">
                    <span>Walk In</span>
                </label>
            </div>

            <div class="sd-modal-grid">
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdIncidentDate">Incident Date</label>
                    <input id="sdIncidentDate" type="date" class="req-modal-input" onclick="this.showPicker()">
                </div>
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdCaseType">Case Type</label>
                    <select id="sdCaseType" class="req-modal-input">
                        <option value="">-select type-</option>
                        <option value="Minor Offense">Minor Offense</option>
                        <option value="Major Offense">Major Offense</option>
                        <option value="Counseling">Counseling</option>
                    </select>
                </div>
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdCalledBy">Called By</label>
                    <input id="sdCalledBy" type="text" class="req-modal-input" placeholder="Name">
                </div>

                <div class="sd-modal-field sd-modal-field-full">
                    <label class="req-modal-label" for="sdDescription">Description</label>
                    <textarea id="sdDescription" class="req-modal-input sd-modal-textarea" rows="2" placeholder="Purpose..."></textarea>
                </div>
            </div>
        </div>

        <div class="sd-modal-section">
            <div class="sd-modal-section-head">
                <div class="sd-modal-title sd-modal-title-action">ACTION</div>
                <label class="setup-checkbox-label sd-section-toggle">
                    <input id="sdCompleted" type="checkbox" class="req-checkbox-input">
                    <span>Completed</span>
                </label>
            </div>

            <div class="sd-modal-grid">
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdActionDate">Action Date</label>
                    <input id="sdActionDate" type="date" class="req-modal-input" onclick="this.showPicker()">
                </div>
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdActionType">Action Type</label>
                    <input id="sdActionType" type="text" class="req-modal-input" placeholder="Purpose...">
                </div>
                <div class="sd-modal-field">
                    <label class="req-modal-label" for="sdCounselor">Counselor</label>
                    <input id="sdCounselor" type="text" class="req-modal-input" placeholder="Name">
                </div>

                <div class="sd-modal-field sd-modal-field-full">
                    <label class="req-modal-label" for="sdRemarks">Remarks</label>
                    <input id="sdRemarks" type="text" class="req-modal-input" placeholder="Purpose...">
                </div>
            </div>
        </div>

        <div class="req-modal-actions sd-modal-actions">
                <button type="button" class="req-btn-cancel" onclick="sdCloseRecordModal()">Cancel</button>
                <button type="button" class="req-btn-save" id="sdRecordSaveBtn" onclick="sdSaveRecord()">Save Record</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdDeleteRecordModal" style="display:none;">
    <div class="req-modal-box req-modal-success" style="min-width:320px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE RECORD</h3>
        <p id="sdDeleteRecordText" style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;">Are you sure you want to delete this conduct record?</p>
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="sdCloseDeleteRecordModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="sdConfirmDeleteRecord()">Delete</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdEditStudentModal" style="display:none;">
    <div class="req-modal-box" style="max-width:640px;">
        <h3 class="req-modal-title">EDIT STUDENT</h3>
        <div class="req-modal-fields">
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT ID</label>
                <input type="text" id="sdEditStudentId" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT NAME</label>
                <input type="text" id="sdEditStudentName" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-fields" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DEGREE PROGRAM</label>
                <input type="text" id="sdEditDegreeProgram" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">DATE OF BIRTH</label>
                <input type="text" id="sdEditBirthDate" class="req-modal-input" placeholder="mm/dd/yyyy">
            </div>
        </div>
        <div class="req-modal-fields" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">GENDER</label>
                <select id="sdEditGender" class="req-modal-input">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT TYPE</label>
                <input type="text" id="sdEditStudentType" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" onclick="sdCloseEditModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="sdSaveStudentEdit()">Save Changes</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdAddStudentModal" style="display:none;">
    <div class="req-modal-box" style="max-width:640px;">
        <h3 class="req-modal-title">ADD STUDENT</h3>
        <div class="req-modal-fields">
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT ID</label>
                <input type="text" id="sdAddStudentId" class="req-modal-input" placeholder="e.g. 2526A0001">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT NAME</label>
                <input type="text" id="sdAddStudentName" class="req-modal-input" placeholder="Lastname, Firstname">
            </div>
        </div>
        <div class="req-modal-fields" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DEGREE PROGRAM</label>
                <input type="text" id="sdAddDegreeProgram" class="req-modal-input" value="Bachelor of Science in Computer Science">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">DATE OF BIRTH</label>
                <input type="date" id="sdAddBirthDate" class="req-modal-input" onclick="this.showPicker()">
            </div>
        </div>
        <div class="req-modal-fields" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">GENDER</label>
                <select id="sdAddGender" class="req-modal-input">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">STUDENT TYPE</label>
                <select id="sdAddStudentType" class="req-modal-input">
                    <option value="New">New</option>
                    <option value="Old">Old</option>
                    <option value="Transferee">Transferee</option>
                </select>
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" onclick="sdCloseAddStudentModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="sdSaveNewStudent()">Add Student</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="sdDeleteStudentModal" style="display:none;">
    <div class="req-modal-box req-modal-success" style="min-width:320px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE STUDENT</h3>
        <p id="sdDeleteText" style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;">Are you sure you want to delete this student?</p>
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="sdCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="sdConfirmDeleteStudent()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/student-discipline.js') }}"></script>
@endpush
