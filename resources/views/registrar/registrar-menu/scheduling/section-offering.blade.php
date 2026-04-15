@extends('layouts.registrar')

@section('title', 'PLP - Section Offering')
@section('page-title', 'SECTION OFFERING')
@section('body-class', 'page-section-offering')

@section('content')
<div class="pf-page">

    {{-- Filter Bar --}}
    <div class="sched-filter-bar">
        <div class="sched-filter-row sched-filter-row-main so-filter-row">
            <div class="sched-filter-group so-filter-search">
                <span class="app-filter-label">Search Section</span>
                <input type="text" class="app-filter-input" id="soSectionSearch" placeholder="Type section, adviser, or course" style="width:100%;">
            </div>
            <div class="sched-filter-group so-filter-sy">
                <span class="app-filter-label">School Year</span>
                <select class="app-filter-select" id="soSY" style="width:100%;">
                    <option value="2025-2026">2025-2026</option>
                    <option value="2024-2025">2024-2025</option>
                    <option value="2023-2024">2023-2024</option>
                </select>
            </div>
            <div class="sched-filter-group so-filter-term">
                <span class="app-filter-label">Semester</span>
                <select class="app-filter-select" id="soTerm" style="width:100%;">
                    <option value="First">First</option>
                    <option value="Second" selected>Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="sched-filter-group so-filter-year">
                <span class="app-filter-label">Year Level</span>
                <select class="app-filter-select" id="soYearLevel" style="width:100%;">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                    <option value="Fourth">Fourth</option>
                </select>
            </div>
            <div class="sched-filter-group so-filter-section">
                <span class="app-filter-label">Section</span>
                <select class="app-filter-select" id="soSection" style="width:100%;">
                    <option value="">All Sections</option>
                </select>
            </div>
            <div class="sched-filter-group sched-filter-group-lg so-filter-program">
                <span class="app-filter-label">Course</span>
                <select class="app-filter-select" id="soProgram" style="width:100%;">
                    <option value="">All Courses</option>
                    <option value="BSIT">BSIT</option>
                    <option value="BSCS">BSCS</option>
                    <option value="BSED">BSED</option>
                    <option value="BSAT">BSAT</option>
                    <option value="BSN">BSN</option>
                    <option value="BSET">BSET</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Sections Directory --}}
    <div class="so-card so-directory-card" id="soSectionListCard">
        <div class="so-card-header">
            <div class="so-card-title">Section Directory</div>
            <div class="so-directory-header-actions">
                <div class="so-directory-note" id="soDirectoryNote">Select a section to view subjects and weekly schedule.</div>
                <button type="button" class="pf-btn-new so-add-btn" id="soOpenAddSection">Add Section</button>
            </div>
        </div>

        <div class="student-table-wrapper table-responsive" id="soSectionTableWrap">
            <table class="student-table registrar-table" id="soSectionTable">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Section</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Year Level</th>
                        <th>Slots</th>
                        <th>Adviser</th>
                        <th>Subjects</th>
                    </tr>
                </thead>
                <tbody id="soSectionListBody">
                    {{-- JS-rendered rows --}}
                </tbody>
            </table>
        </div>

        <div class="pf-pagination" id="soSectionPageInfo">
            <span class="pf-page-info" id="soSectionPageText">Showing 0 sections</span>
        </div>
    </div>

    {{-- Section Offering Card --}}
    <div class="so-card" id="soCard" style="display:none;">
        <div class="so-card-header">
            <div class="so-card-title" id="soCardTitle">Section Offering: A</div>
            <div class="so-card-header-actions">
                <button type="button" class="pf-btn-clear so-back-btn" id="soBackToDirectory">Back to Directory</button>
                <button type="button" class="pf-btn-new so-print-btn">Print Class Program</button>
            </div>
        </div>

        <div class="student-table-wrapper table-responsive" id="soTableWrap">
            <table class="student-table registrar-table" id="soTable">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Description</th>
                        <th>Lec</th>
                        <th>Lab</th>
                        <th>Tuition Units</th>
                        <th>Cred. Units</th>
                        <th>Section</th>
                        <th>Room No</th>
                        <th>Professor</th>
                        <th>Slots</th>
                        <th>Schedule</th>
                    </tr>
                </thead>
                <tbody id="soBody">
                    {{-- JS-rendered rows --}}
                </tbody>
            </table>
        </div>

        <div class="so-subject-hint" style="padding:8px 12px; font-size:12px; color:#2f4f3e;">
            Click a subject code or row to open the schedule editor modal.
        </div>

        <div class="pf-pagination" id="soPageInfo">
            <span class="pf-page-info" id="soPageText">Showing 0 subjects</span>
        </div>
    </div>

    {{-- Schedule Grid --}}
    <div class="so-weekly" id="soWeekly" style="display:none;">
        <div class="so-weekly-title">Class Schedule</div>
        <div class="so-weekly-scroll">
            <div class="so-weekly-grid" id="soWeeklyGrid"></div>
        </div>
    </div>

    {{-- Add Section Modal --}}
    <div class="so-modal" id="soAddSectionModal" aria-hidden="true">
        <div class="so-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="soAddSectionTitle">
            <div class="so-modal-header">
                <h3 class="so-modal-title" id="soAddSectionTitle">Add Section</h3>
                <button type="button" class="so-modal-close" id="soCloseAddSection" aria-label="Close">&times;</button>
            </div>

            <div class="so-modal-body">
                <div class="so-modal-grid">
                    <div class="so-modal-field so-modal-col-6">
                        <label for="soModalProgram">Program</label>
                        <select id="soModalProgram" class="app-filter-select" style="width:100%;">
                            <option value="BSIT">BSIT - Bachelor of Science in Information Technology</option>
                            <option value="BSCS">BSCS - Bachelor of Science in Computer Science</option>
                            <option value="BSED">BSED - Bachelor of Secondary Education</option>
                            <option value="BSAT">BSAT - Bachelor of Science in Accounting Technology</option>
                            <option value="BSN">BSN - Bachelor of Science in Nursing</option>
                            <option value="BSET">BSET - Bachelor of Science in Engineering Technology</option>
                        </select>
                    </div>

                    <div class="so-modal-field so-modal-col-3">
                        <label for="soModalSY">School Year</label>
                        <input id="soModalSY" type="text" class="app-filter-input" placeholder="2026-2027">
                    </div>

                    <div class="so-modal-field so-modal-col-3">
                        <label for="soModalTerm">Term</label>
                        <select id="soModalTerm" class="app-filter-select" style="width:100%;">
                            <option value="First">First</option>
                            <option value="Second" selected>Second</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>

                    <div class="so-modal-field so-modal-col-4">
                        <label for="soModalYearLevel">Year Level</label>
                        <select id="soModalYearLevel" class="app-filter-select" style="width:100%;">
                            <option value="First">First Year</option>
                            <option value="Second">Second Year</option>
                            <option value="Third">Third Year</option>
                            <option value="Fourth">Fourth Year</option>
                        </select>
                    </div>

                    <div class="so-modal-field so-modal-col-4">
                        <label for="soModalSection">Section</label>
                        <input id="soModalSection" type="text" class="app-filter-input" placeholder="A / B / C / D">
                    </div>

                    <div class="so-modal-field so-modal-col-4">
                        <label for="soModalSlots">Slots</label>
                        <input id="soModalSlots" type="number" class="app-filter-input" min="1" max="80" value="30">
                    </div>

                    <div class="so-modal-field so-modal-col-6">
                        <label for="soModalAdviser">Adviser</label>
                        <input id="soModalAdviser" type="text" class="app-filter-input" placeholder="Adviser name">
                    </div>

                    <div class="so-modal-field so-modal-col-6">
                        <label for="soModalDescription">Description</label>
                        <input id="soModalDescription" type="text" class="app-filter-input" placeholder="Optional section notes">
                    </div>

                    <div class="so-modal-field so-modal-col-12">
                        <label>Curriculum Subjects</label>
                        <div class="so-curriculum-picker">
                            <div class="so-curriculum-list-wrap">
                                <div class="so-curriculum-list-title">Available Subjects</div>
                                <select id="soCurriculumAvailable" class="so-curriculum-list" multiple size="8" aria-label="Available curriculum subjects"></select>
                            </div>

                            <div class="so-curriculum-actions" aria-label="Move curriculum subjects">
                                <button type="button" class="so-curriculum-btn" id="soCurriculumAdd" title="Add selected">Add &gt;</button>
                                <button type="button" class="so-curriculum-btn" id="soCurriculumAddAll" title="Add all">Add All &gt;&gt;</button>
                                <button type="button" class="so-curriculum-btn" id="soCurriculumRemove" title="Remove selected">&lt; Remove</button>
                                <button type="button" class="so-curriculum-btn" id="soCurriculumRemoveAll" title="Remove all">&lt;&lt; Remove All</button>
                            </div>

                            <div class="so-curriculum-list-wrap">
                                <div class="so-curriculum-list-title">Subjects Included</div>
                                <select id="soCurriculumIncluded" class="so-curriculum-list" multiple size="8" aria-label="Curriculum subjects included in section"></select>
                            </div>
                        </div>
                        <div class="so-curriculum-summary" id="soCurriculumSummary">0 subjects selected</div>
                    </div>
                </div>

                <div class="so-modal-feedback" id="soModalFeedback"></div>
            </div>

            <div class="so-modal-footer">
                <button type="button" class="so-modal-btn so-modal-btn-cancel" id="soCancelAddSection">Cancel</button>
                <button type="button" class="so-modal-btn so-modal-btn-primary" id="soSaveAddSection">Save Section</button>
            </div>
        </div>
    </div>

    {{-- Edit Subject Schedule Modal --}}
    <div class="so-modal" id="soEditSubjectModal" aria-hidden="true">
        <div class="so-modal-dialog so-edit-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="soEditSubjectTitle">
            <div class="so-modal-header so-edit-modal-header">
                <h3 class="so-modal-title" id="soEditSubjectTitle">CC103 - Computer Programming 2</h3>
                <button type="button" class="so-modal-close" id="soCloseEditSubjectTop" aria-label="Close">&times;</button>
            </div>

            <div class="so-modal-body">
                <section class="so-edit-section so-edit-section-details">
                    <div class="so-edit-section-title">Section Details</div>

                    <div class="so-edit-warning" role="status">
                        Some students are already enrolled to this section, changing the section is not allowed.
                    </div>

                    <div class="so-modal-grid so-edit-modal-grid">
                        <div class="so-modal-field so-modal-col-8">
                            <label for="soEditSectionCode">Section Code</label>
                            <input id="soEditSectionCode" type="text" class="app-filter-input so-edit-readonly" disabled>
                        </div>

                        <div class="so-modal-field so-modal-col-4">
                            <label for="soEditTotalSlots">Total Slots</label>
                            <input id="soEditTotalSlots" type="number" class="app-filter-input so-edit-readonly so-edit-slots" value="30">
                        </div>

                        <div class="so-modal-field so-modal-col-12">
                            <label for="soEditDescription">Description</label>
                            <input id="soEditDescription" type="text" class="app-filter-input so-edit-readonly">
                        </div>

                        <div class="so-modal-field so-modal-col-12">
                            <div class="so-edit-flags">
                                <label for="soEditOpenSection" class="so-edit-check">
                                    <input type="checkbox" class="req-checkbox-input" id="soEditOpenSection" checked>
                                    <span>Open Section</span>
                                </label>
                                <label for="soEditBlockSection" class="so-edit-check">
                                    <input type="checkbox" class="req-checkbox-input" id="soEditBlockSection">
                                    <span>Block Section</span>
                                </label>
                                <label for="soEditTutorial" class="so-edit-check">
                                    <input type="checkbox" class="req-checkbox-input" id="soEditTutorial">
                                    <span>Tutorial</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="so-edit-section-schedule">
                    <div class="so-edit-section-head">
                        <div class="so-edit-section-title">Schedule Assignment</div>
                        <button type="button" class="so-modal-btn so-modal-btn-primary" id="soSaveEditSubjectBtn">Save</button>
                    </div>

                    <div class="table-responsive so-edit-schedule-wrap">
                    <table class="student-table registrar-table so-edit-schedule-table">
                        <thead>
                            <tr>
                                <th rowspan="2">Day of the Week</th>
                                <th colspan="2" class="so-edit-schedule-main-head">Schedule</th>
                                <th rowspan="2">Room No.</th>
                                <th rowspan="2">Lab</th>
                            </tr>
                            <tr class="so-edit-schedule-subhead-row">
                                <th>From</th>
                                <th>To</th>
                            </tr>
                        </thead>
                        <tbody id="soEditScheduleBody">
                            <tr>
                                <td>
                                    <div class="so-edit-day-cell">
                                        <select id="soEditDay" class="app-filter-select">
                                            <option>Monday</option>
                                            <option>Tuesday</option>
                                            <option>Wednesday</option>
                                            <option>Thursday</option>
                                            <option>Friday</option>
                                            <option>Saturday</option>
                                            <option>Sunday</option>
                                        </select>
                                        <input type="checkbox" id="soEditDayEnabled" class="req-checkbox-input" aria-label="Enable day row">
                                    </div>
                                </td>
                                <td>
                                    <div class="so-edit-time-inline">
                                        <select id="soEditFromHour" class="so-edit-time-select"><option>--</option><option>07</option><option>08</option></select>
                                        <span>:</span>
                                        <select id="soEditFromMinute" class="so-edit-time-select"><option>--</option><option>00</option><option>30</option></select>
                                        <select id="soEditFromMeridiem" class="so-edit-time-select"><option>--</option><option>AM</option><option>PM</option></select>
                                    </div>
                                </td>
                                <td>
                                    <div class="so-edit-time-inline">
                                        <select id="soEditToHour" class="so-edit-time-select"><option>--</option><option>09</option><option>10</option></select>
                                        <span>:</span>
                                        <select id="soEditToMinute" class="so-edit-time-select"><option>--</option><option>00</option><option>30</option></select>
                                        <select id="soEditToMeridiem" class="so-edit-time-select"><option>--</option><option>AM</option><option>PM</option></select>
                                    </div>
                                </td>
                                <td>
                                    <select id="soEditRoom" class="app-filter-select so-edit-room-select">
                                        <option>-select room-</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="so-edit-lab-cell">
                                        <input type="checkbox" id="soEditLab" class="req-checkbox-input" aria-label="Lab subject">
                                        <button type="button" class="so-edit-add-row-btn" aria-label="Add schedule row"><i class="fa fa-plus-circle"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/section-offering.js') }}?v={{ time() }}"></script>
@endpush
