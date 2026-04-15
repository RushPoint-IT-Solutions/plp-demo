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
    <div class="so-modal" id="soEditSubjectModal" aria-hidden="true" style="display:none; z-index: 10000; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
        <div class="so-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="soEditSubjectTitle" style="max-width:900px; width:90%; position:relative; margin: 40px auto; background:#fff; border-radius:5px; box-shadow:0 5px 15px rgba(0,0,0,0.3);">
            <div class="so-modal-header" style="border-bottom:none; padding:15px 20px;">
                <h3 class="so-modal-title" id="soEditSubjectTitle" style="font-weight:bold; margin:0;">CC103 - Computer Programming 2</h3>
            </div>

            <div class="so-modal-body" style="padding:20px;">
                <div class="so-modal-grid">
                    <div class="so-modal-field so-modal-col-5" style="display:flex; align-items:center; margin-bottom:10px;">
                        <label for="soEditSectionCode" style="width:110px; margin:0;">Section Code :</label>
                        <input id="soEditSectionCode" type="text" class="app-filter-input" disabled style="background-color:#fadbd8; border:1px solid transparent; color:#333; flex:1; padding:4px;">
                    </div>
                    <div class="so-modal-field so-modal-col-7" style="display:flex; align-items:center; margin-bottom:10px;">
                        <span style="color:#d9534f; font-style:italic; font-size:0.9em; margin-left:15px;">Some students are already enrolled to this section, changing the section is not allowed.</span>
                    </div>

                    <div class="so-modal-field so-modal-col-12" style="display:flex; align-items:center; margin-bottom:10px;">
                        <label for="soEditDescription" style="width:110px; margin:0;">Description :</label>
                        <input id="soEditDescription" type="text" class="app-filter-input" style="background-color:#fadbd8; border:1px solid transparent; width:300px; padding:4px;">
                    </div>

                    <div class="so-modal-field so-modal-col-12" style="display:flex; align-items:center; margin-bottom:15px;">
                        <label for="soEditTotalSlots" style="width:110px; margin:0;">Total Slots :</label>
                        <input id="soEditTotalSlots" type="number" class="app-filter-input" value="30" style="background-color:#fadbd8; border:1px solid transparent; width:80px; padding:4px; text-align:center;">
                    </div>

                    <div class="so-modal-field so-modal-col-12" style="padding-left:115px; margin-bottom:20px;">
                        <div style="display:flex; align-items:center; margin-bottom:5px;">
                            <input type="checkbox" id="soEditOpenSection" checked style="margin:0 8px 0 0;">
                            <label for="soEditOpenSection" style="margin:0; font-weight:normal;">Open Section</label>
                        </div>
                        <div style="display:flex; align-items:center; margin-bottom:5px;">
                            <input type="checkbox" id="soEditBlockSection" style="margin:0 8px 0 0;">
                            <label for="soEditBlockSection" style="margin:0; font-weight:normal;">Block Section</label>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <input type="checkbox" id="soEditTutorial" style="margin:0 8px 0 0;">
                            <label for="soEditTutorial" style="margin:0; font-weight:normal;">Tutorial</label>
                        </div>
                    </div>
                </div>

                <div style="position:absolute; right:20px; top: 180px; z-index:10;">
                    <button type="button" id="soCloseEditSubjectBtn" style="background:#fff; border:1px solid #777; padding:4px 15px; cursor:pointer; font-weight:bold; border-radius:3px;">CLOSE</button>
                </div>

                <div class="table-responsive" style="border: 1px solid #5cb85c; border-radius: 4px; border-top-left-radius:0; border-top-right-radius:0;">
                    <table class="student-table registrar-table" style="width:100%; margin-bottom:0; background:#fff;">
                        <thead style="background-color: #5cb85c; color: white;">
                            <tr>
                                <th style="border-right:1px solid #4cae4c; text-align:left; padding:8px; width:20%;">DAY OF THE WEEK</th>
                                <th style="border-right:1px solid #4cae4c; text-align:center; padding:8px; width:45%;">
                                    SCHEDULE<br>
                                    <div style="display:flex; justify-content:center; gap:60px; font-size:0.85em; font-weight:normal;"><span>FROM</span><span>TO</span></div>
                                </th>
                                <th style="border-right:1px solid #4cae4c; text-align:left; padding:8px; width:25%;">ROOM NO.</th>
                                <th style="text-align:center; padding:8px; width:10%;">LAB</th>
                            </tr>
                        </thead>
                        <tbody id="soEditScheduleBody">
                            {{-- Example row visually identical to image. Real implementation populates dynamically. --}}
                            <tr style="border-bottom:1px solid #ddd;">
                                <td style="padding:6px;">
                                    <select class="app-filter-select" style="width:auto; padding:2px; height:26px;"><option>Monday</option></select>
                                    <input type="checkbox" style="margin-left:5px;">
                                </td>
                                <td style="padding:6px; text-align:center;">
                                    <div style="display:inline-flex; align-items:center; background-color:#fadbd8; padding:2px; border-radius:2px;">
                                        <select style="border:none; background:transparent;"><option>--</option><option>07</option></select> <b>:</b>
                                        <select style="border:none; background:transparent;"><option>--</option><option>00</option></select>
                                        <select style="border:none; background:transparent; margin-left:2px;"><option>--</option><option>AM</option><option>PM</option></select>
                                    </div>
                                    <span style="margin: 0 5px;">-</span>
                                    <div style="display:inline-flex; align-items:center; background-color:#fadbd8; padding:2px; border-radius:2px;">
                                        <select style="border:none; background:transparent;"><option>--</option><option>07</option></select> <b>:</b>
                                        <select style="border:none; background:transparent;"><option>--</option><option>00</option></select>
                                        <select style="border:none; background:transparent; margin-left:2px;"><option>--</option><option>AM</option><option>PM</option></select>
                                    </div>
                                </td>
                                <td style="padding:6px;">
                                    <select class="app-filter-select" style="width:100%; padding:2px; height:26px; background-color:#fadbd8; border:1px solid transparent;"><option>-select room-</option></select>
                                </td>
                                <td style="padding:6px; text-align:center; vertical-align:middle;">
                                    <input type="checkbox" style="margin:0;">
                                    <button style="border:none; background:none; color:#00a8e8; font-size:16px; cursor:pointer; margin-left:5px;"><i class="fa fa-plus-circle"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/section-offering.js') }}?v={{ time() }}"></script>
@endpush
