@extends('layouts.registrar')

@section('title', 'PLP - Section Merging')
@section('page-title', 'SECTION MERGING')
@section('body-class', 'page-section-merging')

@section('content')
<div
    class="pf-page"
    id="sectionMergingPage"
    data-fetch-url="{{ route('registrar.registrar-menu.scheduling.section-merging.data') }}"
    data-store-url="{{ route('registrar.registrar-menu.scheduling.section-merging.store') }}"
    data-csrf-token="{{ csrf_token() }}"
>
    <div class="smrg-content">
        <div class="smrg-card">
            <div class="smrg-card-header">
                <h3 class="smrg-card-title">System Configuration</h3>
            </div>
            <div class="smrg-card-body">
                <div class="smrg-config-form">
                    <div class="smrg-form-group smrg-config-field">
                        <label>SCHOOL YEAR</label>
                        <select class="app-filter-select" id="smrgSchoolYear">
                            <option value="">- Select School Year -</option>
                        </select>
                    </div>
                    <div class="smrg-form-group smrg-config-field">
                        <label>TERM</label>
                        <select class="app-filter-select" id="smrgSemester">
                            <option value="">- Select Semester -</option>
                        </select>
                    </div>
                    <div class="smrg-form-group smrg-config-save">
                        <button type="button" class="pf-btn pf-btn-primary" id="smrgConfigSaveBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="smrg-card">
            <div class="smrg-card-header">
                <h3 class="smrg-card-title">Section Merging</h3>
            </div>
            <div class="smrg-card-body">
                <div class="smrg-split-layout">
                    <div class="smrg-side smrg-side-source">
                        <h4 class="smrg-side-title">Source Section</h4>

                        <div class="smrg-form-group full-width">
                            <label>PROGRAM</label>
                            <div class="smrg-search-wrap">
                                <input type="text" class="app-filter-select smrg-search-input" id="smrgSourceCourseSearch" placeholder="Search program" autocomplete="off">
                                <datalist id="smrgSourceCourseList"></datalist>
                                <div class="smrg-search-dropdown" id="smrgSourceCourseDropdown"></div>
                                <select class="app-filter-select smrg-hidden-select" id="smrgSourceCourse" aria-hidden="true" tabindex="-1">
                                    <option value="">-select Program-</option>
                                </select>
                            </div>
                        </div>

                        <div class="smrg-form-row">
                            <div class="smrg-form-group flex-1">
                                <label>YEAR LEVEL</label>
                                <select class="app-filter-select" id="smrgSourceYearLevel">
                                    <option value="">-select Level-</option>
                                </select>
                            </div>
                            <div class="smrg-form-group flex-1">
                                <label>SECTION</label>
                                <select class="app-filter-select" id="smrgSourceSection">
                                    <option value="">-select Sec-</option>
                                </select>
                            </div>
                        </div>

                        <div class="smrg-form-group full-width">
                            <label>SUBJECT</label>
                            <div class="smrg-search-wrap">
                                <input type="text" class="app-filter-select smrg-search-input" id="smrgSourceSubjectSearch" placeholder="Search section or subject" autocomplete="off">
                                <datalist id="smrgSourceSubjectList"></datalist>
                                <div class="smrg-search-dropdown" id="smrgSourceSubjectDropdown"></div>
                                <select class="app-filter-select smrg-hidden-select" id="smrgSourceSubject" aria-hidden="true" tabindex="-1">
                                    <option value="">-select Subject-</option>
                                </select>
                            </div>
                        </div>

                        <div class="smrg-clear-wrap">
                            <button type="button" class="pf-btn pf-btn-danger" id="smrgSourceClearBtn">Clear Entries</button>
                        </div>
                    </div>

                    <div class="smrg-side smrg-side-target">
                        <h4 class="smrg-side-title">Target Section</h4>

                        <div class="smrg-form-group full-width">
                            <label>PROGRAM</label>
                            <div class="smrg-search-wrap">
                                <input type="text" class="app-filter-select smrg-search-input" id="smrgTargetCourseSearch" placeholder="Search program" autocomplete="off">
                                <datalist id="smrgTargetCourseList"></datalist>
                                <div class="smrg-search-dropdown" id="smrgTargetCourseDropdown"></div>
                                <select class="app-filter-select smrg-hidden-select" id="smrgTargetCourse" aria-hidden="true" tabindex="-1">
                                    <option value="">-select Program-</option>
                                </select>
                            </div>
                        </div>

                        <div class="smrg-form-row">
                            <div class="smrg-form-group flex-1">
                                <label>YEAR LEVEL</label>
                                <select class="app-filter-select" id="smrgTargetYearLevel">
                                    <option value="">-select Level-</option>
                                </select>
                            </div>
                            <div class="smrg-form-group flex-1">
                                <label>SECTION</label>
                                <select class="app-filter-select" id="smrgTargetSection">
                                    <option value="">-select Sec-</option>
                                </select>
                            </div>
                        </div>

                        <div class="smrg-form-group full-width">
                            <label>SUBJECT</label>
                            <div class="smrg-search-wrap">
                                <input type="text" class="app-filter-select smrg-search-input" id="smrgTargetSubjectSearch" placeholder="Search section or subject" autocomplete="off">
                                <datalist id="smrgTargetSubjectList"></datalist>
                                <div class="smrg-search-dropdown" id="smrgTargetSubjectDropdown"></div>
                                <select class="app-filter-select smrg-hidden-select" id="smrgTargetSubject" aria-hidden="true" tabindex="-1">
                                    <option value="">-select Subject-</option>
                                </select>
                            </div>
                        </div>

                        <div class="smrg-clear-wrap">
                            <button type="button" class="pf-btn pf-btn-danger" id="smrgTargetClearBtn">Clear Entries</button>
                        </div>
                    </div>
                </div>

                <div class="smrg-bottom-actions">
                    <button type="button" class="pf-btn pf-btn-secondary" id="smrgCancelBtn">Cancel</button>
                    <button type="button" class="pf-btn pf-btn-primary" id="smrgMergeBtn">Merge</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="pf-modal-overlay" id="mergeConfirmModal" style="display:none;">
        <div class="pf-modal-box" style="text-align:center; max-width:480px; padding:30px;">
            <div class="pf-modal-title" style="color:#006837; font-size: 1.25rem; font-weight:700; margin-bottom: 20px; letter-spacing: 1px;">CONFIRM SECTION INTEGRATION</div>
            <p style="font-size:0.95rem; color:#444; margin-bottom:30px; line-height: 1.5;">Are you sure you want to merge these sections? This action will consolidate all associated documents and metadata into a single repository. This process cannot be undone.</p>
            <div class="pf-modal-actions" style="justify-content:center; gap: 16px;">
                <button type="button" class="pf-modal-btn-cancel" id="smrgConfirmCancelBtn">Cancel</button>
                <button type="button" class="pf-modal-btn-save" style="background:#006837;" id="smrgConfirmMergeBtn">Yes, Merge Sections</button>
            </div>
        </div>
    </div>
    
    <div class="pf-modal-overlay" id="mergeSuccessModal" style="display:none;">
        <div class="pf-modal-box" style="text-align:center; max-width:420px; padding: 30px;">
            <div class="pf-modal-title" style="color:#006837; font-size: 1.3rem; font-weight:700; margin-bottom: 16px; letter-spacing: 1px;">SUCCESSFUL!</div>
            <p style="font-size:0.95rem; color:#444; margin-bottom:30px;">Sections Successfully Integrated</p>
            <div class="pf-modal-actions" style="justify-content:center;">
                <button type="button" class="pf-modal-btn-save" style="background:#006837; min-width:120px;" id="smrgSuccessOkBtn">Okay</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/section-merging.js') }}?v={{ filemtime(public_path('js/section-merging.js')) }}"></script>
@endpush
