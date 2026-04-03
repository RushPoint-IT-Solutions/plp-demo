@extends('layouts.registrar')

@section('title', 'PLP - Configuration')
@section('page-title', 'CONFIGURATION')

@section('content')
<div class="pf-page">
    <div class="cfg-page">
        <div class="cfg-grid-top">
            <section class="cfg-card">
                <div class="cfg-card-head">
                    <h3>School Year and Semester</h3>
                    <button type="button" class="pf-btn-new" onclick="cfgOpenAddSchoolSem()">Add</button>
                </div>
                <div class="app-table-wrap">
                    <table id="cfgSchoolSemTable" class="app-table cfg-table cfg-table-sy">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Semester</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cfgSchoolSemBody"></tbody>
                    </table>
                </div>
                <div class="cfg-pagination" id="cfgSchoolSemPager"></div>
            </section>

            <section class="cfg-card">
                <div class="cfg-card-head">
                    <h3>Grade Posting</h3>
                    <button type="button" class="pf-btn-new" onclick="cfgOpenAddGradePosting()">Add</button>
                </div>
                <div class="app-table-wrap">
                    <table id="cfgGradePostingTable" class="app-table cfg-table cfg-table-gp">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Semester</th>
                                <th>Period</th>
                                <th>Date From</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cfgGradePostingBody"></tbody>
                    </table>
                </div>
                <div class="cfg-pagination" id="cfgGradePostingPager"></div>
            </section>
        </div>

        {{--
        <section class="cfg-card cfg-card-full">
            <div class="cfg-card-head">
                <h3>Registration Period</h3>
                <button type="button" class="pf-btn-new" onclick="cfgOpenAddRegistration()">Add</button>
            </div>
            <div class="app-table-wrap">
                <table id="cfgRegistrationTable" class="app-table cfg-table" style="min-width: 980px;">
                    <thead>
                        <tr>
                            <th>School of</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Student Status</th>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="cfgRegistrationBody"></tbody>
                </table>
            </div>
            <div class="cfg-pagination" id="cfgRegistrationPager"></div>
        </section>
        --}}

        <section class="cfg-card cfg-card-full">
            <div class="cfg-card-head">
                <h3>Names and Designation Signature</h3>
            </div>
            <div class="app-table-wrap">
                <table id="cfgSignatureTable" class="app-table cfg-table">
                    <thead>
                        <tr>
                            <th>Designation</th>
                            <th>Name</th>
                            <th>Signature</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Registrar</td>
                            <td>Test</td>
                            <td><input type="file" class="req-modal-input"></td>
                            <td style="text-align:center;">
                                <div class="apst-action-btn" data-cfg-menu-toggle="cfgMenu-signature-1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                                <div class="apst-dropdown" id="cfgMenu-signature-1"><button type="button">Edit</button><button type="button" class="apst-del-btn">Delete</button></div>
                            </td>
                        </tr>
                        <tr>
                            <td>Accounting Head</td>
                            <td>Test</td>
                            <td><input type="file" class="req-modal-input"></td>
                            <td style="text-align:center;"><div class="apst-action-btn" data-cfg-menu-toggle="cfgMenu-signature-2" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div><div class="apst-dropdown" id="cfgMenu-signature-2"><button type="button">Edit</button><button type="button" class="apst-del-btn">Delete</button></div></td>
                        </tr>
                        <tr>
                            <td>Assistant Registrar</td>
                            <td>Test</td>
                            <td><input type="file" class="req-modal-input"></td>
                            <td style="text-align:center;"><div class="apst-action-btn" data-cfg-menu-toggle="cfgMenu-signature-3" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div><div class="apst-dropdown" id="cfgMenu-signature-3"><button type="button">Edit</button><button type="button" class="apst-del-btn">Delete</button></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="cfg-card cfg-card-full">
            <div class="cfg-card-head">
                <h3>Cut Off Date</h3>
            </div>
            <div class="cfg-filter-row">
                <div class="cfg-filter-group">
                    <label class="req-modal-label">Type</label>
                    <select class="req-modal-input"><option>Faculty Loading</option><option>Enrollment</option></select>
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label">SY</label>
                    <input type="text" class="req-modal-input" value="2025-2026">
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label">Semester</label>
                    <select class="req-modal-input"><option>Second</option><option>First</option></select>
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label">Cut Off Date</label>
                    <input type="date" class="req-modal-input">
                </div>
                <div class="cfg-filter-action">
                    <button type="button" class="pf-btn-new">Save</button>
                </div>
            </div>
            <div class="app-table-wrap">
                <table id="cfgCutoffDateTable" class="app-table cfg-table">
                    <thead>
                        <tr>
                            <th>SY</th>
                            <th>Sem</th>
                            <th>Cut-off Date</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025-2026</td>
                            <td>Second</td>
                            <td>02/10/26 - 02/10/2026</td>
                            <td style="text-align:center;"><div class="apst-action-btn" data-cfg-menu-toggle="cfgMenu-cutoff-1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div><div class="apst-dropdown" id="cfgMenu-cutoff-1"><button type="button">Edit</button><button type="button" class="apst-del-btn">Delete</button></div></td>
                        </tr>
                        <tr>
                            <td>2025-2026</td>
                            <td>First</td>
                            <td>06/12/25 - 08/30/25</td>
                            <td style="text-align:center;"><div class="apst-action-btn" data-cfg-menu-toggle="cfgMenu-cutoff-2" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div><div class="apst-dropdown" id="cfgMenu-cutoff-2"><button type="button">Edit</button><button type="button" class="apst-del-btn">Delete</button></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="cfg-card cfg-card-full">
            <div class="cfg-card-head">
                <h3>Section Offering Cut Off</h3>
            </div>
            <div class="app-table-wrap">
                <table id="cfgSectionCutoffTable" class="app-table cfg-table">
                    <thead>
                        <tr>
                            <th>SY</th>
                            <th>Sem</th>
                            <th>Cut-off Date</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025-2026</td>
                            <td>Second</td>
                            <td>02/12/26 - 02/12/2026</td>
                            <td style="text-align:center;"><div class="apst-action-btn" data-cfg-menu-toggle="cfgMenu-section-1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div><div class="apst-dropdown" id="cfgMenu-section-1"><button type="button">Edit</button><button type="button" class="apst-del-btn">Delete</button></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="cfg-grid-top">
            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Cut-off Registration</h3></div>
                <div class="cfg-filter-row cfg-filter-row-tight cfg-filter-row-cutoff-reg">
                    <div class="cfg-filter-group"><label class="req-modal-label">SY</label><input type="text" class="req-modal-input" value="2025-2026"></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Semester</label><select class="req-modal-input"><option>Second</option><option>First</option></select></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Date</label><input type="date" class="req-modal-input"></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Cut Off Date</label><input type="date" class="req-modal-input"></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Student No.</label><input type="text" class="req-modal-input" placeholder="Student No."></div>
                    <div class="cfg-filter-action"><button type="button" class="pf-btn-new">Cut Off Reg</button></div>
                </div>
            </section>

            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Changing/Deleting/Adding Cut-off Config</h3></div>
                <div class="cfg-filter-row cfg-filter-row-tight">
                    <div class="cfg-filter-group"><label class="req-modal-label">SY</label><input type="text" class="req-modal-input" value="2025-2026"></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Semester</label><select class="req-modal-input"><option>Second</option><option>First</option></select></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Cut-off Date</label><input type="date" class="req-modal-input"></div>
                    <div class="cfg-filter-action"><button type="button" class="pf-btn-new">Update</button></div>
                </div>
                <div class="app-table-wrap">
                    <table id="cfgCutoffConfigTable" class="app-table cfg-table">
                        <thead><tr><th>SY</th><th>Semester</th><th>Cut-off Date</th><th style="text-align:center;">Action</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>2025-2026</td>
                                <td>Second</td>
                                <td>04/22/26</td>
                                <td style="text-align:center;"><div class="apst-action-btn" data-cfg-menu-toggle="cfgMenu-cutoffcfg-1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div><div class="apst-dropdown" id="cfgMenu-cutoffcfg-1"><button type="button">Edit</button><button type="button" class="apst-del-btn">Delete</button></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="cfg-grid-top">
            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Curriculum Evaluation Display</h3></div>
                <div class="cfg-filter-row cfg-filter-row-tight">
                    <div class="cfg-filter-group"><label class="req-modal-label">SY</label><input type="text" class="req-modal-input" value="2025-2026"></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Semester</label><select class="req-modal-input"><option>Second</option><option>First</option></select></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Status</label><select class="req-modal-input"><option>Display</option><option>Hide</option></select></div>
                    <div class="cfg-filter-action"><button type="button" class="pf-btn-new">Submit</button></div>
                </div>
                <div class="app-table-wrap">
                    <table id="cfgCurriculumDisplayTable" class="app-table cfg-table">
                        <thead><tr><th>AY</th><th>Semester</th><th>Status</th><th style="text-align:center;">Action</th></tr></thead>
                        <tbody>
                            <tr><td>2025-2026</td><td>Second</td><td>Display</td><td style="text-align:center;"><div class="apst-action-btn" data-cfg-menu-toggle="cfgMenu-curri-1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div><div class="apst-dropdown" id="cfgMenu-curri-1"><button type="button">Edit</button><button type="button" class="apst-del-btn">Delete</button></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Report Details Tab</h3></div>
                <div class="cfg-filter-row cfg-filter-row-tight">
                    <div class="cfg-filter-group"><label class="req-modal-label">Region</label><input type="text" class="req-modal-input" value="2025-2026"></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Division</label><input type="text" class="req-modal-input" value="2025-2026"></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">School ID</label><input type="text" class="req-modal-input" value="2025-2026"></div>
                    <div class="cfg-filter-group cfg-filter-group-wide"><label class="req-modal-label">School Name</label><input type="text" class="req-modal-input" value="2025-2026"></div>
                    <div class="cfg-filter-group cfg-filter-group-wide"><label class="req-modal-label">Contact Details</label><input type="text" class="req-modal-input" value="2025-2026"></div>
                    <div class="cfg-filter-action"><button type="button" class="pf-btn-new">Submit</button></div>
                </div>
            </section>
        </div>

        <div class="cfg-grid-top">
            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Overdue INC Final Grade</h3></div>
                <div class="cfg-filter-row cfg-filter-row-tight">
                    <div class="cfg-filter-group"><label class="req-modal-label">SY</label><input type="text" class="req-modal-input" value="2025-2026"></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Semester</label><select class="req-modal-input"><option>First</option><option>Second</option></select></div>
                    <div class="cfg-filter-action"><button type="button" class="pf-btn-new">Process</button></div>
                </div>
            </section>

            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Email Sender</h3></div>
                <div class="cfg-filter-row cfg-filter-row-tight">
                    <div class="cfg-filter-group"><label class="req-modal-label">Email</label><input type="email" class="req-modal-input" placeholder="test@gmail.com"></div>
                    <div class="cfg-filter-group"><label class="req-modal-label">Password</label><input type="password" class="req-modal-input" value="test"></div>
                    <div class="cfg-filter-action"><button type="button" class="pf-btn-new">Save</button></div>
                </div>
            </section>
        </div>

    </div>
</div>

<div class="req-modal-overlay" id="cfgSchoolSemModal" style="display:none;" onclick="if(event.target===this) cfgCloseModal('cfgSchoolSemModal')">
    <div class="req-modal-box cfg-modal-box">
        <h3 class="req-modal-title" id="cfgSSTitle">ADD SCHOOL YEAR AND SEMESTER</h3>
        <div class="sc-modal-grid">
            <div class="req-modal-field-group">
                <label class="req-modal-label">School Year</label>
                <input type="text" id="cfgSSYear" class="req-modal-input" placeholder="e.g. 2025-2026">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Semester</label>
                <select id="cfgSSSemester" class="req-modal-input">
                    <option value="">-Select Semester-</option>
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="cfgCloseModal('cfgSchoolSemModal')">Cancel</button>
            <button type="button" id="cfgSSSaveBtn" class="req-btn-save" onclick="cfgSaveSchoolSem()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="cfgGradePostingModal" style="display:none;" onclick="if(event.target===this) cfgCloseModal('cfgGradePostingModal')">
    <div class="req-modal-box cfg-modal-box">
        <h3 class="req-modal-title" id="cfgGPTitle">ADD GRADE POSTING</h3>
        <div class="sc-modal-grid-3">
            <div class="req-modal-field-group">
                <label class="req-modal-label">School Year</label>
                <input type="text" id="cfgGPYear" class="req-modal-input" placeholder="e.g. 2025-2026">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Semester</label>
                <select id="cfgGPSemester" class="req-modal-input">
                    <option value="">-Select Semester-</option>
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Period</label>
                <select id="cfgGPPeriod" class="req-modal-input">
                    <option value="">-Select-</option>
                    <option value="Prelim">Prelim</option>
                    <option value="Midterm">Midterm</option>
                    <option value="Pre-Final">Pre-Final</option>
                    <option value="Final">Final</option>
                </select>
            </div>
        </div>
        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Date From</label>
                <input type="date" id="cfgGPDateFrom" class="req-modal-input" onclick="if(this.showPicker){this.showPicker()}" onfocus="if(this.showPicker){this.showPicker()}">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="cfgCloseModal('cfgGradePostingModal')">Cancel</button>
            <button type="button" id="cfgGPSaveBtn" class="req-btn-save" onclick="cfgSaveGradePosting()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="cfgRegistrationModal" style="display:none;" onclick="if(event.target===this) cfgCloseModal('cfgRegistrationModal')">
    <div class="req-modal-box cfg-modal-box cfg-modal-wide">
        <h3 class="req-modal-title" id="cfgRPTitle">ADD REGISTRATION PERIOD</h3>
        <div class="sc-modal-grid-3">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Status</label>
                <select id="cfgRPStatus" class="req-modal-input">
                    <option value="">-Select Status-</option>
                    <option value="All Status">All Status</option>
                    <option value="Regular">Regular</option>
                    <option value="Irregular">Irregular</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">School Of</label>
                <input type="text" id="cfgRPSchool" class="req-modal-input" placeholder="e.g. All School">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input type="text" id="cfgRPCourse" class="req-modal-input" placeholder="e.g. All Courses">
            </div>
        </div>
        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year Level</label>
                <input type="text" id="cfgRPYearLevel" class="req-modal-input" placeholder="e.g. All Year">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Date From</label>
                <input type="date" id="cfgRPDateFrom" class="req-modal-input" onclick="if(this.showPicker){this.showPicker()}" onfocus="if(this.showPicker){this.showPicker()}">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Date To</label>
                <input type="date" id="cfgRPDateTo" class="req-modal-input" onclick="if(this.showPicker){this.showPicker()}" onfocus="if(this.showPicker){this.showPicker()}">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="cfgCloseModal('cfgRegistrationModal')">Cancel</button>
            <button type="button" id="cfgRPSaveBtn" class="req-btn-save" onclick="cfgSaveRegistration()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="cfgStaticEditModal" style="display:none;" onclick="if(event.target===this) cfgCloseModal('cfgStaticEditModal')">
    <div class="req-modal-box cfg-modal-box">
        <h3 class="req-modal-title">EDIT RECORD</h3>
        <div class="sc-modal-grid" id="cfgStaticEditFields"></div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="cfgCloseModal('cfgStaticEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="cfgSaveStaticEdit()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="cfgStaticDeleteModal" style="display:none;" onclick="if(event.target===this) cfgCloseModal('cfgStaticDeleteModal')">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE RECORD</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this record?</p>
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="cfgCloseModal('cfgStaticDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="cfgConfirmStaticDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var cfgSchoolSem = @json($schoolSemRows ?? []);
    var cfgGradePosting = @json($gradePostingRows ?? []);

    var cfgSchoolSemStoreUrl = '{{ route('registrar.admin-tools.system-config.configuration.school-sem.store') }}';
    var cfgSchoolSemUpdateTemplate = '{{ route('registrar.admin-tools.system-config.configuration.school-sem.update', ['systemSchoolSemester' => '__ID__']) }}';
    var cfgSchoolSemDeleteTemplate = '{{ route('registrar.admin-tools.system-config.configuration.school-sem.destroy', ['systemSchoolSemester' => '__ID__']) }}';
    var cfgGradePostingStoreUrl = '{{ route('registrar.admin-tools.system-config.configuration.grade-posting.store') }}';
    var cfgGradePostingUpdateTemplate = '{{ route('registrar.admin-tools.system-config.configuration.grade-posting.update', ['systemGradePosting' => '__ID__']) }}';
    var cfgGradePostingDeleteTemplate = '{{ route('registrar.admin-tools.system-config.configuration.grade-posting.destroy', ['systemGradePosting' => '__ID__']) }}';

    var cfgRegistration = [
        { school: 'All School', course: 'All Courses', yearLevel: 'All Year', status: 'All Status', dateFrom: '2025-11-15', dateTo: '2026-01-31' },
        { school: 'School of IT', course: 'BSIT', yearLevel: 'First Year', status: 'Regular', dateFrom: '2025-11-18', dateTo: '2025-12-20' },
        { school: 'School of IT', course: 'BSCS', yearLevel: 'Second Year', status: 'Regular', dateFrom: '2025-11-21', dateTo: '2025-12-22' },
        { school: 'School of Business', course: 'BSBA', yearLevel: 'Third Year', status: 'Regular', dateFrom: '2025-11-23', dateTo: '2025-12-24' },
        { school: 'School of Education', course: 'BSED', yearLevel: 'Fourth Year', status: 'Regular', dateFrom: '2025-11-25', dateTo: '2025-12-26' },
        { school: 'School of IT', course: 'BSIT', yearLevel: 'All Year', status: 'Irregular', dateFrom: '2026-01-05', dateTo: '2026-01-15' },
        { school: 'School of Business', course: 'BSBA', yearLevel: 'All Year', status: 'Irregular', dateFrom: '2026-01-08', dateTo: '2026-01-18' },
        { school: 'School of Education', course: 'BSED', yearLevel: 'All Year', status: 'Irregular', dateFrom: '2026-01-10', dateTo: '2026-01-20' },
        { school: 'School of IT', course: 'BSIT', yearLevel: 'Third Year', status: 'Regular', dateFrom: '2026-05-14', dateTo: '2026-06-18' },
        { school: 'School of IT', course: 'BSCS', yearLevel: 'Fourth Year', status: 'Regular', dateFrom: '2026-05-16', dateTo: '2026-06-20' },
        { school: 'School of Business', course: 'BSBA', yearLevel: 'Second Year', status: 'Regular', dateFrom: '2026-05-18', dateTo: '2026-06-22' },
        { school: 'School of Education', course: 'BSED', yearLevel: 'First Year', status: 'Regular', dateFrom: '2026-05-20', dateTo: '2026-06-24' },
        { school: 'School of IT', course: 'BSIT', yearLevel: 'Second Year', status: 'Irregular', dateFrom: '2026-07-02', dateTo: '2026-07-15' },
        { school: 'School of IT', course: 'BSCS', yearLevel: 'Third Year', status: 'Irregular', dateFrom: '2026-07-04', dateTo: '2026-07-17' },
        { school: 'School of Business', course: 'BSBA', yearLevel: 'Fourth Year', status: 'Irregular', dateFrom: '2026-07-06', dateTo: '2026-07-19' },
        { school: 'School of Education', course: 'BSED', yearLevel: 'Second Year', status: 'Irregular', dateFrom: '2026-07-08', dateTo: '2026-07-21' },
        { school: 'School of IT', course: 'BSIS', yearLevel: 'First Year', status: 'Regular', dateFrom: '2026-08-10', dateTo: '2026-09-12' },
        { school: 'School of IT', course: 'BSIS', yearLevel: 'Second Year', status: 'Regular', dateFrom: '2026-08-12', dateTo: '2026-09-14' },
        { school: 'School of Business', course: 'BSA', yearLevel: 'First Year', status: 'Regular', dateFrom: '2026-08-14', dateTo: '2026-09-16' },
        { school: 'School of Education', course: 'BEED', yearLevel: 'Third Year', status: 'Regular', dateFrom: '2026-08-16', dateTo: '2026-09-18' }
    ];

    var cfgPager = {
        schoolSem: { page: 1, size: 5 },
        gradePosting: { page: 1, size: 5 },
        registration: { page: 1, size: 5 }
    };

    var cfgEditState = {
        schoolSem: null,
        gradePosting: null,
        registration: null
    };

    var cfgStaticState = {
        row: null,
        editableIndexes: []
    };

    function cfgGetCsrfToken() {
        return '{{ csrf_token() }}';
    }

    function cfgSchoolSemUpdateUrl(id) {
        return cfgSchoolSemUpdateTemplate.replace('__ID__', String(id));
    }

    function cfgSchoolSemDeleteUrl(id) {
        return cfgSchoolSemDeleteTemplate.replace('__ID__', String(id));
    }

    function cfgGradePostingUpdateUrl(id) {
        return cfgGradePostingUpdateTemplate.replace('__ID__', String(id));
    }

    function cfgGradePostingDeleteUrl(id) {
        return cfgGradePostingDeleteTemplate.replace('__ID__', String(id));
    }

    async function cfgApiRequest(url, method, payload) {
        var response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': cfgGetCsrfToken()
            },
            body: payload ? JSON.stringify(payload) : null
        });

        var json = {};
        try {
            json = await response.json();
        } catch (e) {
            json = {};
        }

        if (!response.ok || json.ok === false) {
            throw new Error(
                (json.message) ||
                (json.errors && Object.values(json.errors)[0] && Object.values(json.errors)[0][0]) ||
                'Unable to process request.'
            );
        }

        return json;
    }

    function cfgEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function cfgFormatDate(dateString) {
        if (!dateString) return '';
        var d = new Date(dateString + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: '2-digit' });
    }

    function cfgOpenModal(id) {
        cfgCloseActionMenus();
        var modal = document.getElementById(id);
        if (modal) modal.style.display = 'flex';
    }

    function cfgOpenAddSchoolSem() {
        cfgEditState.schoolSem = null;
        document.getElementById('cfgSSTitle').textContent = 'ADD SCHOOL YEAR AND SEMESTER';
        document.getElementById('cfgSSSaveBtn').textContent = 'Save';
        document.getElementById('cfgSSYear').value = '';
        document.getElementById('cfgSSSemester').value = '';
        cfgOpenModal('cfgSchoolSemModal');
    }

    function cfgOpenAddGradePosting() {
        cfgEditState.gradePosting = null;
        document.getElementById('cfgGPTitle').textContent = 'ADD GRADE POSTING';
        document.getElementById('cfgGPSaveBtn').textContent = 'Save';
        document.getElementById('cfgGPYear').value = '';
        document.getElementById('cfgGPSemester').value = '';
        document.getElementById('cfgGPPeriod').value = '';
        document.getElementById('cfgGPDateFrom').value = '';
        cfgOpenModal('cfgGradePostingModal');
    }

    function cfgOpenAddRegistration() {
        cfgEditState.registration = null;
        document.getElementById('cfgRPTitle').textContent = 'ADD REGISTRATION PERIOD';
        document.getElementById('cfgRPSaveBtn').textContent = 'Save';
        document.getElementById('cfgRPStatus').value = '';
        document.getElementById('cfgRPSchool').value = '';
        document.getElementById('cfgRPCourse').value = '';
        document.getElementById('cfgRPYearLevel').value = '';
        document.getElementById('cfgRPDateFrom').value = '';
        document.getElementById('cfgRPDateTo').value = '';
        cfgOpenModal('cfgRegistrationModal');
    }

    function cfgOpenEditSchoolSem(index) {
        var item = cfgSchoolSem[index];
        if (!item) return;
        cfgEditState.schoolSem = {
            id: item.id || null,
            index: index
        };
        document.getElementById('cfgSSTitle').textContent = 'EDIT SCHOOL YEAR AND SEMESTER';
        document.getElementById('cfgSSSaveBtn').textContent = 'Update';
        document.getElementById('cfgSSYear').value = item.sy || '';
        document.getElementById('cfgSSSemester').value = item.semester || '';
        cfgOpenModal('cfgSchoolSemModal');
    }

    function cfgOpenEditGradePosting(index) {
        var item = cfgGradePosting[index];
        if (!item) return;
        cfgEditState.gradePosting = {
            id: item.id || null,
            index: index
        };
        document.getElementById('cfgGPTitle').textContent = 'EDIT GRADE POSTING';
        document.getElementById('cfgGPSaveBtn').textContent = 'Update';
        document.getElementById('cfgGPYear').value = item.sy || '';
        document.getElementById('cfgGPSemester').value = item.semester || '';
        document.getElementById('cfgGPPeriod').value = item.period || '';
        document.getElementById('cfgGPDateFrom').value = item.dateFrom || '';
        cfgOpenModal('cfgGradePostingModal');
    }

    function cfgOpenEditRegistration(index) {
        var item = cfgRegistration[index];
        if (!item) return;
        cfgEditState.registration = index;
        document.getElementById('cfgRPTitle').textContent = 'EDIT REGISTRATION PERIOD';
        document.getElementById('cfgRPSaveBtn').textContent = 'Update';
        document.getElementById('cfgRPStatus').value = item.status || '';
        document.getElementById('cfgRPSchool').value = item.school || '';
        document.getElementById('cfgRPCourse').value = item.course || '';
        document.getElementById('cfgRPYearLevel').value = item.yearLevel || '';
        document.getElementById('cfgRPDateFrom').value = item.dateFrom || '';
        document.getElementById('cfgRPDateTo').value = item.dateTo || '';
        cfgOpenModal('cfgRegistrationModal');
    }

    function cfgCloseModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    }

    function cfgDataByGroup(group) {
        if (group === 'schoolSem') return cfgSchoolSem;
        if (group === 'gradePosting') return cfgGradePosting;
        return cfgRegistration;
    }

    function cfgMaxPage(group) {
        var data = cfgDataByGroup(group);
        var size = cfgPager[group].size;
        return Math.max(1, Math.ceil(data.length / size));
    }

    function cfgSetPage(group, page) {
        var maxPage = cfgMaxPage(group);
        cfgPager[group].page = Math.min(maxPage, Math.max(1, page));
        cfgRenderAll();
    }

    function cfgBuildPageButtons(group, current, maxPage) {
        var html = '';
        var start = Math.max(1, current - 2);
        var end = Math.min(maxPage, current + 2);

        if (current <= 3) {
            end = Math.min(maxPage, 5);
        } else if (current >= maxPage - 2) {
            start = Math.max(1, maxPage - 4);
        }

        for (var p = start; p <= end; p++) {
            html += '<button type="button" class="btn cfg-page-num ' + (p === current ? 'active' : '') + '" ' +
                (p === current ? 'aria-current="page"' : '') + ' onclick="cfgSetPage(\'' + group + '\',' + p + ')">' + p + '</button>';
        }

        return html;
    }

    function cfgRenderPager(group, pagerId, totalCount) {
        var pager = document.getElementById(pagerId);
        if (!pager) return;
        var current = cfgPager[group].page;
        var maxPage = cfgMaxPage(group);
        if (totalCount <= cfgPager[group].size) {
            pager.innerHTML = '';
            return;
        }
        pager.innerHTML = '' +
            '<nav aria-label="Configuration pagination" class="cfg-page-nav-wrap">' +
                '<div class="cfg-page-list" role="group" aria-label="Page controls">' +
                    '<button type="button" class="btn cfg-page-btn" aria-label="Previous page" ' + (current <= 1 ? 'disabled' : '') + ' onclick="cfgSetPage(\'' + group + '\',' + (current - 1) + ')">' +
                        '<span aria-hidden="true">&lt;</span>' +
                    '</button>' +
                    cfgBuildPageButtons(group, current, maxPage) +
                    '<button type="button" class="btn cfg-page-btn" aria-label="Next page" ' + (current >= maxPage ? 'disabled' : '') + ' onclick="cfgSetPage(\'' + group + '\',' + (current + 1) + ')">' +
                        '<span aria-hidden="true">&gt;</span>' +
                    '</button>' +
                '</div>' +
            '</nav>';
    }

    function cfgBuildActionMenu(group, index) {
        var menuId = 'cfgMenu-' + group + '-' + index;
        var editHandler = 'cfgOpenEditRegistration';
        if (group === 'schoolSem') editHandler = 'cfgOpenEditSchoolSem';
        if (group === 'gradePosting') editHandler = 'cfgOpenEditGradePosting';
        return '' +
            '<div class="apst-action-btn" data-cfg-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="' + editHandler + '(' + index + ')">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>' +
                    'Edit' +
                '</button>' +
                '<button type="button" class="apst-del-btn" onclick="cfgDeleteRow(\'' + group + '\',' + index + ')">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                    'Delete' +
                '</button>' +
            '</div>';
    }

    function cfgCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function cfgToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;
        var isOpen = menu.classList.contains('open');
        cfgCloseActionMenus();
        if (isOpen) return;

        var rect = trigger.getBoundingClientRect();
        var estimatedWidth = 126;
        var estimatedHeight = 92;
        var left = rect.right + 8;
        var top = rect.top;

        if (left + estimatedWidth > window.innerWidth - 8) {
            left = Math.max(8, window.innerWidth - estimatedWidth - 8);
        }

        if ((window.innerHeight - rect.bottom) < estimatedHeight + 8) {
            menu.classList.add('drop-up');
            top = Math.max(8, rect.bottom - estimatedHeight);
        }

        menu.style.left = left + 'px';
        menu.style.right = 'auto';
        menu.style.top = top + 'px';
        menu.style.bottom = 'auto';
        menu.classList.add('open');
    }

    function cfgInlineEditRow(row) {
        cfgOpenStaticEditModal(row);
    }

    function cfgOpenStaticEditModal(row) {
        if (!row) return;

        var table = row.closest('table');
        var headers = table ? table.querySelectorAll('thead th') : [];
        var cells = row.querySelectorAll('td');
        var fieldsWrap = document.getElementById('cfgStaticEditFields');
        if (!fieldsWrap) return;

        cfgStaticState.row = row;
        cfgStaticState.editableIndexes = [];

        var html = '';
        for (var i = 0; i < cells.length - 1; i++) {
            if (cells[i].querySelector('input[type="file"]')) {
                continue;
            }

            var label = headers[i] ? (headers[i].textContent || '').trim() : ('Field ' + (i + 1));
            var value = (cells[i].textContent || '').trim();
            cfgStaticState.editableIndexes.push(i);

            html += '' +
                '<div class="req-modal-field-group">' +
                    '<label class="req-modal-label">' + cfgEscapeHtml(label) + '</label>' +
                    '<input type="text" class="req-modal-input" id="cfgStaticEditField' + i + '" value="' + cfgEscapeHtml(value) + '">' +
                '</div>';
        }

        if (!html) {
            html = '<p style="color:#6b7280; margin:0;">No editable fields for this row.</p>';
        }

        fieldsWrap.innerHTML = html;
        cfgOpenModal('cfgStaticEditModal');
    }

    function cfgSaveStaticEdit() {
        if (!cfgStaticState.row) {
            cfgCloseModal('cfgStaticEditModal');
            return;
        }

        var cells = cfgStaticState.row.querySelectorAll('td');
        cfgStaticState.editableIndexes.forEach(function(index) {
            var input = document.getElementById('cfgStaticEditField' + index);
            if (!input || !cells[index]) return;
            cells[index].textContent = (input.value || '').trim();
        });

        cfgCloseModal('cfgStaticEditModal');
        cfgCloseActionMenus();
    }

    function cfgOpenStaticDeleteModal(row) {
        if (!row) return;
        cfgStaticState.row = row;
        cfgOpenModal('cfgStaticDeleteModal');
    }

    function cfgConfirmStaticDelete() {
        if (cfgStaticState.row) {
            cfgStaticState.row.remove();
        }

        cfgStaticState.row = null;
        cfgCloseModal('cfgStaticDeleteModal');
        cfgCloseActionMenus();
    }

    async function cfgDeleteRow(group, index) {
        var data = cfgDataByGroup(group);
        var row = data[index];
        if (!row) {
            return;
        }

        try {
            if (group === 'schoolSem' && row.id) {
                await cfgApiRequest(cfgSchoolSemDeleteUrl(row.id), 'DELETE');
            }
            if (group === 'gradePosting' && row.id) {
                await cfgApiRequest(cfgGradePostingDeleteUrl(row.id), 'DELETE');
            }
        } catch (error) {
            alert(error.message || 'Unable to delete record.');
            return;
        }

        data.splice(index, 1);
        cfgPager[group].page = Math.min(cfgPager[group].page, cfgMaxPage(group));
        cfgCloseActionMenus();
        cfgRenderAll();
    }

    function cfgRenderSchoolSem() {
        var body = document.getElementById('cfgSchoolSemBody');
        var page = cfgPager.schoolSem.page;
        var size = cfgPager.schoolSem.size;
        var start = (page - 1) * size;
        var list = cfgSchoolSem.slice(start, start + size);
        var rows = list.map(function(item, idx) {
            var index = start + idx;
            return '' +
                '<tr>' +
                    '<td>' + cfgEscapeHtml(item.sy) + '</td>' +
                    '<td>' + cfgEscapeHtml(item.semester) + '</td>' +
                    '<td style="text-align:center;">' +
                        cfgBuildActionMenu('schoolSem', index) +
                    '</td>' +
                '</tr>';
        }).join('');
        body.innerHTML = rows || '<tr><td colspan="3" class="sc-empty-row">No records found.</td></tr>';
        cfgRenderPager('schoolSem', 'cfgSchoolSemPager', cfgSchoolSem.length);
    }

    function cfgRenderGradePosting() {
        var body = document.getElementById('cfgGradePostingBody');
        var page = cfgPager.gradePosting.page;
        var size = cfgPager.gradePosting.size;
        var start = (page - 1) * size;
        var list = cfgGradePosting.slice(start, start + size);
        var rows = list.map(function(item, idx) {
            var index = start + idx;
            return '' +
                '<tr>' +
                    '<td>' + cfgEscapeHtml(item.sy) + '</td>' +
                    '<td>' + cfgEscapeHtml(item.semester) + '</td>' +
                    '<td>' + cfgEscapeHtml(item.period) + '</td>' +
                    '<td>' + cfgEscapeHtml(cfgFormatDate(item.dateFrom)) + '</td>' +
                    '<td style="text-align:center;">' +
                        cfgBuildActionMenu('gradePosting', index) +
                    '</td>' +
                '</tr>';
        }).join('');
        body.innerHTML = rows || '<tr><td colspan="5" class="sc-empty-row">No records found.</td></tr>';
        cfgRenderPager('gradePosting', 'cfgGradePostingPager', cfgGradePosting.length);
    }

    function cfgRenderRegistration() {
        var body = document.getElementById('cfgRegistrationBody');
        var page = cfgPager.registration.page;
        var size = cfgPager.registration.size;
        var start = (page - 1) * size;
        var list = cfgRegistration.slice(start, start + size);
        var rows = list.map(function(item, idx) {
            var index = start + idx;
            return '' +
                '<tr>' +
                    '<td>' + cfgEscapeHtml(item.school) + '</td>' +
                    '<td>' + cfgEscapeHtml(item.course) + '</td>' +
                    '<td>' + cfgEscapeHtml(item.yearLevel) + '</td>' +
                    '<td>' + cfgEscapeHtml(item.status) + '</td>' +
                    '<td>' + cfgEscapeHtml(cfgFormatDate(item.dateFrom)) + '</td>' +
                    '<td>' + cfgEscapeHtml(cfgFormatDate(item.dateTo)) + '</td>' +
                    '<td style="text-align:center;">' +
                        cfgBuildActionMenu('registration', index) +
                    '</td>' +
                '</tr>';
        }).join('');
        body.innerHTML = rows || '<tr><td colspan="7" class="sc-empty-row">No records found.</td></tr>';
        cfgRenderPager('registration', 'cfgRegistrationPager', cfgRegistration.length);
    }

    function cfgRenderAll() {
        cfgRenderSchoolSem();
        cfgRenderGradePosting();
        cfgRenderRegistration();
    }

    async function cfgSaveSchoolSem() {
        var sy = (document.getElementById('cfgSSYear').value || '').trim();
        var semester = document.getElementById('cfgSSSemester').value;
        if (!sy || !semester) {
            alert('Please complete School Year and Semester.');
            return;
        }

        var payload = {
            school_year: sy,
            semester: semester
        };

        try {
            if (cfgEditState.schoolSem === null) {
                var createRes = await cfgApiRequest(cfgSchoolSemStoreUrl, 'POST', payload);
                cfgSchoolSem.unshift(createRes.row || { sy: sy, semester: semester });
                cfgPager.schoolSem.page = 1;
            } else if (cfgEditState.schoolSem.id) {
                var updateRes = await cfgApiRequest(cfgSchoolSemUpdateUrl(cfgEditState.schoolSem.id), 'PUT', payload);
                var updateIndex = cfgSchoolSem.findIndex(function(item) {
                    return String(item.id) === String(cfgEditState.schoolSem.id);
                });
                if (updateIndex >= 0) {
                    cfgSchoolSem[updateIndex] = updateRes.row || cfgSchoolSem[updateIndex];
                }
            } else {
                cfgSchoolSem[cfgEditState.schoolSem.index] = { sy: sy, semester: semester };
            }
            cfgEditState.schoolSem = null;
        } catch (error) {
            alert(error.message || 'Unable to save school year and semester.');
            return;
        }

        cfgCloseModal('cfgSchoolSemModal');
        cfgRenderAll();
    }

    async function cfgSaveGradePosting() {
        var payload = {
            sy: (document.getElementById('cfgGPYear').value || '').trim(),
            semester: document.getElementById('cfgGPSemester').value,
            period: document.getElementById('cfgGPPeriod').value,
            dateFrom: document.getElementById('cfgGPDateFrom').value
        };
        if (!payload.sy || !payload.semester || !payload.period || !payload.dateFrom) {
            alert('Please complete all Grade Posting fields.');
            return;
        }

        var requestPayload = {
            school_year: payload.sy,
            semester: payload.semester,
            period: payload.period,
            date_from: payload.dateFrom
        };

        try {
            if (cfgEditState.gradePosting === null) {
                var createRes = await cfgApiRequest(cfgGradePostingStoreUrl, 'POST', requestPayload);
                cfgGradePosting.unshift(createRes.row || payload);
                cfgPager.gradePosting.page = 1;
            } else if (cfgEditState.gradePosting.id) {
                var updateRes = await cfgApiRequest(cfgGradePostingUpdateUrl(cfgEditState.gradePosting.id), 'PUT', requestPayload);
                var updateIndex = cfgGradePosting.findIndex(function(item) {
                    return String(item.id) === String(cfgEditState.gradePosting.id);
                });
                if (updateIndex >= 0) {
                    cfgGradePosting[updateIndex] = updateRes.row || cfgGradePosting[updateIndex];
                }
            } else {
                cfgGradePosting[cfgEditState.gradePosting.index] = payload;
            }
            cfgEditState.gradePosting = null;
        } catch (error) {
            alert(error.message || 'Unable to save grade posting.');
            return;
        }

        cfgCloseModal('cfgGradePostingModal');
        cfgRenderAll();
    }

    function cfgSaveRegistration() {
        var payload = {
            status: document.getElementById('cfgRPStatus').value,
            school: (document.getElementById('cfgRPSchool').value || '').trim(),
            course: (document.getElementById('cfgRPCourse').value || '').trim(),
            yearLevel: (document.getElementById('cfgRPYearLevel').value || '').trim(),
            dateFrom: document.getElementById('cfgRPDateFrom').value,
            dateTo: document.getElementById('cfgRPDateTo').value
        };
        if (!payload.status || !payload.school || !payload.course || !payload.yearLevel || !payload.dateFrom || !payload.dateTo) {
            alert('Please complete all Registration Period fields.');
            return;
        }
        if (cfgEditState.registration === null) {
            cfgRegistration.unshift(payload);
            cfgPager.registration.page = 1;
        } else {
            cfgRegistration[cfgEditState.registration] = payload;
            cfgEditState.registration = null;
        }
        cfgCloseModal('cfgRegistrationModal');
        cfgRenderAll();
    }

    document.addEventListener('click', function(event) {
        var toggle = event.target.closest('[data-cfg-menu-toggle]');
        if (toggle) {
            event.stopPropagation();
            cfgToggleActionMenu(toggle.getAttribute('data-cfg-menu-toggle'), toggle);
            return;
        }

        var actionButton = event.target.closest('.apst-dropdown button');
        if (actionButton && !actionButton.getAttribute('onclick')) {
            event.preventDefault();
            var row = actionButton.closest('tr');
            if (actionButton.classList.contains('apst-del-btn')) {
                cfgOpenStaticDeleteModal(row);
            } else {
                cfgOpenStaticEditModal(row);
            }
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            cfgCloseActionMenus();
        }
    });

    window.addEventListener('scroll', cfgCloseActionMenus, true);

    cfgRenderAll();
</script>
@endpush
