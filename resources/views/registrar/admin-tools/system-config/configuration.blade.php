@extends('layouts.registrar')

@section('title', 'PLP - Configuration')
@section('page-title', 'CONFIGURATION')
@section('body-class', 'page-system-config-configuration')

@php
    $defaultTerm = collect($academicTermRows ?? [])->first();
    $defaultSchoolYear = $defaultTerm['schoolYear'] ?? (date('Y') . '-' . (date('Y') + 1));
    $defaultSemester = $defaultTerm['semester'] ?? 'First';

    $semesterOptions = [
        ['value' => '', 'label' => '-Select Semester-'],
        ['value' => 'First', 'label' => 'First'],
        ['value' => 'Second', 'label' => 'Second'],
        ['value' => 'Summer', 'label' => 'Summer'],
    ];

    $statusOptions = [
        ['value' => '', 'label' => '-Select Status-'],
        ['value' => 'Display', 'label' => 'Display'],
        ['value' => 'Hide', 'label' => 'Hide'],
    ];

    $gradePeriodOptions = [
        ['value' => '', 'label' => '-Select Period-'],
        ['value' => 'Prelim', 'label' => 'Prelim'],
        ['value' => 'Midterm', 'label' => 'Midterm'],
        ['value' => 'Pre-Final', 'label' => 'Pre-Final'],
        ['value' => 'Final', 'label' => 'Final'],
    ];

    $signatureDesignationOptions = collect($signatureDesignationRows ?? [])->map(function ($row) {
        return [
            'value' => (string) ($row['id'] ?? ''),
            'label' => (string) ($row['name'] ?? ''),
        ];
    })->prepend(['value' => '', 'label' => '-Select Designation-'])->values()->all();

    $cutoffDateTypeOptions = collect($cutoffTypeRows ?? [])->filter(function ($row) {
        return in_array((string) ($row['code'] ?? ''), ['ENROLLMENT', 'FACULTY_LOADING'], true);
    })->map(function ($row) {
        return [
            'value' => (string) ($row['code'] ?? ''),
            'label' => (string) ($row['name'] ?? ''),
        ];
    })->prepend(['value' => '', 'label' => '-Select Type-'])->values()->all();

    $cfgRouteMap = [
        'schoolSemStore' => route('registrar.admin-tools.system-config.configuration.school-sem.store'),
        'schoolSemUpdateTemplate' => route('registrar.admin-tools.system-config.configuration.school-sem.update', ['systemSchoolSemester' => '__ID__']),
        'schoolSemDeleteTemplate' => route('registrar.admin-tools.system-config.configuration.school-sem.destroy', ['systemSchoolSemester' => '__ID__']),
        'gradePostingStore' => route('registrar.admin-tools.system-config.configuration.grade-posting.store'),
        'gradePostingUpdateTemplate' => route('registrar.admin-tools.system-config.configuration.grade-posting.update', ['systemGradePosting' => '__ID__']),
        'gradePostingDeleteTemplate' => route('registrar.admin-tools.system-config.configuration.grade-posting.destroy', ['systemGradePosting' => '__ID__']),
        'signatureStore' => route('registrar.admin-tools.system-config.configuration.signature.store'),
        'signatureUpdateTemplate' => route('registrar.admin-tools.system-config.configuration.signature.update', ['systemConfigNameSignature' => '__ID__']),
        'signatureDeleteTemplate' => route('registrar.admin-tools.system-config.configuration.signature.destroy', ['systemConfigNameSignature' => '__ID__']),
        'cutoffStore' => route('registrar.admin-tools.system-config.configuration.cutoff.store'),
        'cutoffUpdateTemplate' => route('registrar.admin-tools.system-config.configuration.cutoff.update', ['systemCutoffEntry' => '__ID__']),
        'cutoffDeleteTemplate' => route('registrar.admin-tools.system-config.configuration.cutoff.destroy', ['systemCutoffEntry' => '__ID__']),
        'curriculumDisplayStore' => route('registrar.admin-tools.system-config.configuration.curriculum-display.store'),
        'curriculumDisplayUpdateTemplate' => route('registrar.admin-tools.system-config.configuration.curriculum-display.update', ['systemCurriculumDisplaySetting' => '__ID__']),
        'curriculumDisplayDeleteTemplate' => route('registrar.admin-tools.system-config.configuration.curriculum-display.destroy', ['systemCurriculumDisplaySetting' => '__ID__']),
        'reportDetailsSave' => route('registrar.admin-tools.system-config.configuration.report-details.save'),
        'emailSenderSave' => route('registrar.admin-tools.system-config.configuration.email-sender.save'),
        'overdueIncProcess' => route('registrar.admin-tools.system-config.configuration.overdue-inc.process'),
    ];
@endphp

@section('content')
<div class="pf-page">
    <div class="cfg-page">
        <div class="cfg-grid-top">
            <section class="cfg-card">
                <div class="cfg-card-head">
                    <h3>School Year and Semester</h3>
                    <button type="button" class="pf-btn-new" data-cfg-action="open-school-sem-modal">Add</button>
                </div>
                <div class="app-table-wrap">
                    <table id="cfgSchoolSemTable" class="app-table cfg-table cfg-table-sy" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Semester</th>
                                <th class="cfg-col-action">Action</th>
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
                    <button type="button" class="pf-btn-new" data-cfg-action="open-grade-posting-modal">Add</button>
                </div>
                <div class="app-table-wrap">
                    <table id="cfgGradePostingTable" class="app-table cfg-table cfg-table-gp" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Semester</th>
                                <th>Period</th>
                                <th>Date From</th>
                                <th class="cfg-col-action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cfgGradePostingBody"></tbody>
                    </table>
                </div>
                <div class="cfg-pagination" id="cfgGradePostingPager"></div>
            </section>
        </div>

        <section class="cfg-card cfg-card-full">
            <div class="cfg-card-head">
                <h3>Names and Designation Signature</h3>
            </div>
            <form id="cfgSignatureForm" class="cfg-filter-row cfg-filter-row-tight" enctype="multipart/form-data">
                <input type="hidden" id="cfgSignatureEditId" value="">
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgSignatureDesignation">Designation</label>
                    @include('registrar.components.listbox-select', [
                        'id' => 'cfgSignatureDesignation',
                        'name' => 'cfgSignatureDesignation',
                        'options' => $signatureDesignationOptions,
                        'selected' => '',
                        'placeholder' => '-Select Designation-'
                    ])
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgSignatureName">Name</label>
                    <input type="text" id="cfgSignatureName" class="req-modal-input" placeholder="Enter signer name">
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgSignatureFile">Signature</label>
                    <input type="file" id="cfgSignatureFile" class="req-modal-input" accept=".jpg,.jpeg,.png,image/png,image/jpeg">
                </div>
                <div class="cfg-filter-action">
                    <button type="submit" id="cfgSignatureSaveBtn" class="pf-btn-new">Save</button>
                </div>
            </form>
            <div class="app-table-wrap">
                <table id="cfgSignatureTable" class="app-table cfg-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>Designation</th>
                            <th>Name</th>
                            <th>Signature</th>
                            <th class="cfg-col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody id="cfgSignatureBody"></tbody>
                </table>
            </div>
            <div class="cfg-pagination" id="cfgSignaturePager"></div>
        </section>

        <section class="cfg-card cfg-card-full">
            <div class="cfg-card-head">
                <h3>Cut Off Date</h3>
            </div>
            <form id="cfgCutoffDateForm" class="cfg-filter-row">
                <input type="hidden" id="cfgCutoffDateEditId" value="">
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgCutoffType">Type</label>
                    @include('registrar.components.listbox-select', [
                        'id' => 'cfgCutoffType',
                        'name' => 'cfgCutoffType',
                        'options' => $cutoffDateTypeOptions,
                        'selected' => '',
                        'placeholder' => '-Select Type-'
                    ])
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgCutoffSy">SY</label>
                    <input type="text" id="cfgCutoffSy" class="req-modal-input" value="{{ $defaultSchoolYear }}">
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgCutoffSemester">Semester</label>
                    @include('registrar.components.listbox-select', [
                        'id' => 'cfgCutoffSemester',
                        'name' => 'cfgCutoffSemester',
                        'options' => $semesterOptions,
                        'selected' => $defaultSemester,
                        'placeholder' => '-Select Semester-'
                    ])
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgCutoffDate">Cut Off Date</label>
                    <input type="date" id="cfgCutoffDate" class="req-modal-input">
                </div>
                <div class="cfg-filter-action">
                    <button type="submit" id="cfgCutoffSaveBtn" class="pf-btn-new">Save</button>
                </div>
            </form>
            <div class="app-table-wrap">
                <table id="cfgCutoffDateTable" class="app-table cfg-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>SY</th>
                            <th>Sem</th>
                            <th>Cut-off Date</th>
                            <th class="cfg-col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody id="cfgCutoffDateBody"></tbody>
                </table>
            </div>
            <div class="cfg-pagination" id="cfgCutoffDatePager"></div>
        </section>

        <section class="cfg-card cfg-card-full">
            <div class="cfg-card-head">
                <h3>Section Offering Cut Off</h3>
            </div>
            <form id="cfgSectionCutoffForm" class="cfg-filter-row cfg-filter-row-tight">
                <input type="hidden" id="cfgSectionCutoffEditId" value="">
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgSectionCutoffSy">SY</label>
                    <input type="text" id="cfgSectionCutoffSy" class="req-modal-input" value="{{ $defaultSchoolYear }}">
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgSectionCutoffSemester">Semester</label>
                    @include('registrar.components.listbox-select', [
                        'id' => 'cfgSectionCutoffSemester',
                        'name' => 'cfgSectionCutoffSemester',
                        'options' => $semesterOptions,
                        'selected' => $defaultSemester,
                        'placeholder' => '-Select Semester-'
                    ])
                </div>
                <div class="cfg-filter-group">
                    <label class="req-modal-label" for="cfgSectionCutoffDate">Cut-off Date</label>
                    <input type="date" id="cfgSectionCutoffDate" class="req-modal-input">
                </div>
                <div class="cfg-filter-action">
                    <button type="submit" id="cfgSectionCutoffSaveBtn" class="pf-btn-new">Save</button>
                </div>
            </form>
            <div class="app-table-wrap">
                <table id="cfgSectionCutoffTable" class="app-table cfg-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>SY</th>
                            <th>Sem</th>
                            <th>Cut-off Date</th>
                            <th class="cfg-col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody id="cfgSectionCutoffBody"></tbody>
                </table>
            </div>
            <div class="cfg-pagination" id="cfgSectionCutoffPager"></div>
        </section>

        <div class="cfg-grid-top">
            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Cut-off Registration</h3></div>
                <form id="cfgCutoffRegistrationForm" class="cfg-filter-row cfg-filter-row-tight cfg-filter-row-cutoff-reg">
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCutoffRegSy">SY</label>
                        <input type="text" id="cfgCutoffRegSy" class="req-modal-input" value="{{ $defaultSchoolYear }}">
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCutoffRegSemester">Semester</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'cfgCutoffRegSemester',
                            'name' => 'cfgCutoffRegSemester',
                            'options' => $semesterOptions,
                            'selected' => $defaultSemester,
                            'placeholder' => '-Select Semester-'
                        ])
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCutoffRegDate">Date</label>
                        <input type="date" id="cfgCutoffRegDate" class="req-modal-input">
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCutoffRegCutoffDate">Cut Off Date</label>
                        <input type="date" id="cfgCutoffRegCutoffDate" class="req-modal-input">
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCutoffRegStudentNo">Student No.</label>
                        <input type="text" id="cfgCutoffRegStudentNo" class="req-modal-input" placeholder="Student No.">
                    </div>
                    <div class="cfg-filter-action">
                        <button type="submit" id="cfgCutoffRegBtn" class="pf-btn-new">Cut Off Reg</button>
                    </div>
                </form>
                <p class="cfg-inline-message" id="cfgCutoffRegMessage"></p>
            </section>

            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Changing/Deleting/Adding Cut-off Config</h3></div>
                <form id="cfgCutoffConfigForm" class="cfg-filter-row cfg-filter-row-tight">
                    <input type="hidden" id="cfgCutoffConfigEditId" value="">
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCutoffConfigSy">SY</label>
                        <input type="text" id="cfgCutoffConfigSy" class="req-modal-input" value="{{ $defaultSchoolYear }}">
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCutoffConfigSemester">Semester</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'cfgCutoffConfigSemester',
                            'name' => 'cfgCutoffConfigSemester',
                            'options' => $semesterOptions,
                            'selected' => $defaultSemester,
                            'placeholder' => '-Select Semester-'
                        ])
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCutoffConfigDate">Cut-off Date</label>
                        <input type="date" id="cfgCutoffConfigDate" class="req-modal-input">
                    </div>
                    <div class="cfg-filter-action">
                        <button type="submit" id="cfgCutoffConfigSaveBtn" class="pf-btn-new">Update</button>
                    </div>
                </form>
                <div class="app-table-wrap">
                    <table id="cfgCutoffConfigTable" class="app-table cfg-table" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Semester</th>
                                <th>Cut-off Date</th>
                                <th class="cfg-col-action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cfgCutoffConfigBody"></tbody>
                    </table>
                </div>
                <div class="cfg-pagination" id="cfgCutoffConfigPager"></div>
            </section>
        </div>

        <div class="cfg-grid-top">
            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Curriculum Evaluation Display</h3></div>
                <form id="cfgCurriculumDisplayForm" class="cfg-filter-row cfg-filter-row-tight">
                    <input type="hidden" id="cfgCurriculumEditId" value="">
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCurriculumSy">SY</label>
                        <input type="text" id="cfgCurriculumSy" class="req-modal-input" value="{{ $defaultSchoolYear }}">
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCurriculumSemester">Semester</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'cfgCurriculumSemester',
                            'name' => 'cfgCurriculumSemester',
                            'options' => $semesterOptions,
                            'selected' => $defaultSemester,
                            'placeholder' => '-Select Semester-'
                        ])
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgCurriculumStatus">Status</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'cfgCurriculumStatus',
                            'name' => 'cfgCurriculumStatus',
                            'options' => $statusOptions,
                            'selected' => '',
                            'placeholder' => '-Select Status-'
                        ])
                    </div>
                    <div class="cfg-filter-action">
                        <button type="submit" id="cfgCurriculumSaveBtn" class="pf-btn-new">Submit</button>
                    </div>
                </form>
                <div class="app-table-wrap">
                    <table id="cfgCurriculumDisplayTable" class="app-table cfg-table" data-no-auto-pager="1">
                        <thead>
                            <tr>
                                <th>AY</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th class="cfg-col-action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cfgCurriculumDisplayBody"></tbody>
                    </table>
                </div>
                <div class="cfg-pagination" id="cfgCurriculumDisplayPager"></div>
            </section>

            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Report Details Tab</h3></div>
                <form id="cfgReportDetailsForm" class="cfg-filter-row cfg-filter-row-tight">
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgReportRegion">Region</label>
                        <input type="text" id="cfgReportRegion" class="req-modal-input" value="{{ $reportDetails['region'] ?? '' }}">
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgReportDivision">Division</label>
                        <input type="text" id="cfgReportDivision" class="req-modal-input" value="{{ $reportDetails['division'] ?? '' }}">
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgReportSchoolId">School ID</label>
                        <input type="text" id="cfgReportSchoolId" class="req-modal-input" value="{{ $reportDetails['schoolId'] ?? '' }}">
                    </div>
                    <div class="cfg-filter-group cfg-filter-group-wide">
                        <label class="req-modal-label" for="cfgReportSchoolName">School Name</label>
                        <input type="text" id="cfgReportSchoolName" class="req-modal-input" value="{{ $reportDetails['schoolName'] ?? '' }}">
                    </div>
                    <div class="cfg-filter-group cfg-filter-group-wide">
                        <label class="req-modal-label" for="cfgReportContactDetails">Contact Details</label>
                        <input type="text" id="cfgReportContactDetails" class="req-modal-input" value="{{ $reportDetails['contactDetails'] ?? '' }}">
                    </div>
                    <div class="cfg-filter-action">
                        <button type="submit" class="pf-btn-new">Submit</button>
                    </div>
                </form>
            </section>
        </div>

        <div class="cfg-grid-top">
            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Overdue INC Final Grade</h3></div>
                <form id="cfgOverdueIncForm" class="cfg-filter-row cfg-filter-row-tight">
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgIncSy">SY</label>
                        <input type="text" id="cfgIncSy" class="req-modal-input" value="{{ $defaultSchoolYear }}">
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgIncSemester">Semester</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'cfgIncSemester',
                            'name' => 'cfgIncSemester',
                            'options' => $semesterOptions,
                            'selected' => $defaultSemester,
                            'placeholder' => '-Select Semester-'
                        ])
                    </div>
                    <div class="cfg-filter-action">
                        <button type="submit" id="cfgIncProcessBtn" class="pf-btn-new">Process</button>
                    </div>
                </form>
                <p class="cfg-inline-message" id="cfgIncProcessMessage"></p>
            </section>

            <section class="cfg-card">
                <div class="cfg-card-head"><h3>Email Sender</h3></div>
                <form id="cfgEmailSenderForm" class="cfg-filter-row cfg-filter-row-tight">
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgEmailSenderAddress">Email</label>
                        <input type="email" id="cfgEmailSenderAddress" class="req-modal-input" value="{{ $emailSender['email'] ?? '' }}" placeholder="test@gmail.com">
                    </div>
                    <div class="cfg-filter-group">
                        <label class="req-modal-label" for="cfgEmailSenderPassword">Password</label>
                        <input type="password" id="cfgEmailSenderPassword" class="req-modal-input" placeholder="{{ !empty($emailSender['passwordMasked']) ? 'Saved password' : 'Enter password' }}">
                    </div>
                    <div class="cfg-filter-action">
                        <button type="submit" class="pf-btn-new">Save</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<div class="req-modal-overlay cfg-modal-overlay is-hidden" id="cfgSchoolSemModal" data-cfg-modal="school-sem">
    <div class="req-modal-box cfg-modal-box">
        <h3 class="req-modal-title" id="cfgSSTitle">ADD SCHOOL YEAR AND SEMESTER</h3>
        <input type="hidden" id="cfgSSEditId" value="">
        <div class="sc-modal-grid">
            <div class="req-modal-field-group">
                <label class="req-modal-label" for="cfgSSYear">School Year</label>
                <input type="text" id="cfgSSYear" class="req-modal-input" placeholder="e.g. 2025-2026">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label" for="cfgSSSemester">Semester</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'cfgSSSemester',
                    'name' => 'cfgSSSemester',
                    'options' => $semesterOptions,
                    'selected' => '',
                    'placeholder' => '-Select Semester-'
                ])
            </div>
        </div>
        <div class="req-modal-actions cfg-modal-actions">
            <button type="button" class="req-btn-cancel" data-cfg-action="close-modal" data-cfg-modal-target="cfgSchoolSemModal">Cancel</button>
            <button type="button" id="cfgSSSaveBtn" class="req-btn-save" data-cfg-action="save-school-sem">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay cfg-modal-overlay is-hidden" id="cfgGradePostingModal" data-cfg-modal="grade-posting">
    <div class="req-modal-box cfg-modal-box">
        <h3 class="req-modal-title" id="cfgGPTitle">ADD GRADE POSTING</h3>
        <input type="hidden" id="cfgGPEditId" value="">
        <div class="sc-modal-grid-3">
            <div class="req-modal-field-group">
                <label class="req-modal-label" for="cfgGPYear">School Year</label>
                <input type="text" id="cfgGPYear" class="req-modal-input" placeholder="e.g. 2025-2026">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label" for="cfgGPSemester">Semester</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'cfgGPSemester',
                    'name' => 'cfgGPSemester',
                    'options' => $semesterOptions,
                    'selected' => '',
                    'placeholder' => '-Select Semester-'
                ])
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label" for="cfgGPPeriod">Period</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'cfgGPPeriod',
                    'name' => 'cfgGPPeriod',
                    'options' => $gradePeriodOptions,
                    'selected' => '',
                    'placeholder' => '-Select Period-'
                ])
            </div>
        </div>
        <div class="sc-modal-grid cfg-modal-grid-gap">
            <div class="req-modal-field-group">
                <label class="req-modal-label" for="cfgGPDateFrom">Date From</label>
                <input type="date" id="cfgGPDateFrom" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-actions cfg-modal-actions">
            <button type="button" class="req-btn-cancel" data-cfg-action="close-modal" data-cfg-modal-target="cfgGradePostingModal">Cancel</button>
            <button type="button" id="cfgGPSaveBtn" class="req-btn-save" data-cfg-action="save-grade-posting">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay cfg-modal-overlay is-hidden" id="cfgDeleteModal" data-cfg-modal="confirm-delete">
    <div class="req-modal-box req-modal-success cfg-delete-modal-box">
        <h3 class="req-modal-title cfg-delete-modal-title">DELETE RECORD</h3>
        <p class="cfg-delete-modal-message" id="cfgDeleteMessage">Are you sure you want to delete this record?</p>
        <div class="req-modal-actions cfg-delete-modal-actions">
            <button type="button" class="req-btn-cancel" data-cfg-action="close-modal" data-cfg-modal-target="cfgDeleteModal">Cancel</button>
            <button type="button" class="req-btn-save cfg-delete-btn" data-cfg-action="confirm-delete">Delete</button>
        </div>
    </div>
</div>

<div
    id="cfgBootstrap"
    class="cfg-bootstrap-data"
    data-school-sem='@json($schoolSemRows ?? [])'
    data-grade-posting='@json($gradePostingRows ?? [])'
    data-signatures='@json($signatureRows ?? [])'
    data-cutoff-date='@json($cutoffDateRows ?? [])'
    data-section-cutoff='@json($sectionCutoffRows ?? [])'
    data-cutoff-config='@json($cutoffConfigRows ?? [])'
    data-curriculum-display='@json($curriculumDisplayRows ?? [])'
    data-signature-designations='@json($signatureDesignationRows ?? [])'
    data-cutoff-types='@json($cutoffTypeRows ?? [])'
    data-academic-terms='@json($academicTermRows ?? [])'
    data-report-details='@json($reportDetails ?? [])'
    data-email-sender='@json($emailSender ?? [])'
    data-latest-inc-run='@json($latestIncRun ?? null)'
    data-routes='@json($cfgRouteMap)'
></div>
@endsection

@push('scripts')
@php
    $cfgScriptAssets = [
        'js/registrar-listbox-select.js',
        'js/registrar-system-config-configuration.js',
    ];
@endphp
@foreach ($cfgScriptAssets as $cfgScriptAsset)
    @php
        $cfgScriptSrc = null;
        try {
            $cfgScriptSrc = mix($cfgScriptAsset);
        } catch (\Throwable $exception) {
            if (file_exists(public_path($cfgScriptAsset))) {
                $cfgScriptSrc = asset($cfgScriptAsset);
            }
        }
    @endphp
    @if (!empty($cfgScriptSrc))
        <script src="{{ $cfgScriptSrc }}"></script>
    @endif
@endforeach
@endpush
