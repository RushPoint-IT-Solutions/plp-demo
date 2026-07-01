@extends('layouts.registrar')

@section('title', 'PLP - Evaluation')
@section('page-title', 'EVALUATION')

@section('content')
<div class="pf-page">
    <div id="evalPageData"
         data-library='@json($evaluationLibrary)'
         data-store-url="{{ route('registrar.registrar-menu.faculty-mgmt.evaluation.store') }}"
         data-publish-url-template="{{ route('registrar.registrar-menu.faculty-mgmt.evaluation.publish', ['evaluationForm' => '__ID__']) }}"
         data-csrf="{{ csrf_token() }}">
    </div>

    {{-- Search Bar --}}
    <div style="margin-bottom:18px;">
        <div class="pf-search-wrap" style="max-width:380px;">
            <svg class="pf-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="pf-search-input" placeholder="Search Subject/Faculty Name" id="evalSearch">
        </div>
    </div>

    <div class="eval-layout">

        {{-- ═══ LEFT: Evaluation Builder Canvas ═══ --}}
        <div class="eval-builder">
            <h2 class="eval-section-title">EVALUATION BUILDER CANVAS</h2>

            {{-- Step Tabs --}}
            <div class="eval-steps">
                <button type="button" class="eval-step active" id="stepDefineTab" onclick="goToStep('define')">
                    <span class="eval-step-text">Define</span>
                    <svg class="eval-step-check" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
                <button type="button" class="eval-step" id="stepBuildTab" onclick="goToStep('build')">
                    <span class="eval-step-text">Build</span>
                    <svg class="eval-step-check" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
                <button type="button" class="eval-step" id="stepPreviewTab" onclick="goToStep('preview')">
                    <span class="eval-step-text">Preview</span>
                    <svg class="eval-step-check" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </div>

            {{-- ── STEP 1: Define ── --}}
            <div class="eval-step-panel" id="stepDefine">
                <div class="eval-define-form">
                    <div class="eval-define-row">
                        <div class="eval-define-field">
                            <label class="eval-define-label">ACADEMIC YEAR</label>
                            <select class="eval-define-select" id="evalAY">
                                <option value="2025-2026">2025-2026</option>
                                <option value="2024-2025">2024-2025</option>
                            </select>
                        </div>
                        <div class="eval-define-field eval-define-field-lg">
                            <label class="eval-define-label">PROGRAM</label>
                            <select class="eval-define-select" id="evalProgram">
                                <option value="">Select Course</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCS">BSCS</option>
                                <option value="BSED">BSED</option>
                                <option value="BSN">BSN</option>
                            </select>
                        </div>
                    </div>
                    <div class="eval-define-row">
                        <div class="eval-define-field" style="flex:1;">
                            <label class="eval-define-label">SUBJECT SELECTION</label>
                            <select class="eval-define-select" id="evalSubject">
                                <option value="">Select Subject</option>
                                <option value="CAP102">Capstone Project 2</option>
                                <option value="CC101">CC101 - Intro to Computing</option>
                                <option value="OOP113">OOP 113</option>
                                <option value="SAM125">SAM 125</option>
                            </select>
                        </div>
                    </div>
                    <div class="eval-define-row">
                        <div class="eval-define-field" style="flex:1;">
                            <label class="eval-define-label">INSTRUCTOR/FACULTY NAME</label>
                            <select class="eval-define-select" id="evalFaculty">
                                <option value="">Select Faculty</option>
                                <option value="Diaz, Jonnel Mark">Diaz, Jonnel Mark</option>
                                <option value="Santos, Maria">Santos, Maria</option>
                                <option value="Reyes, Carlo">Reyes, Carlo</option>
                            </select>
                        </div>
                    </div>
                    <div class="eval-define-row">
                        <div class="eval-define-field">
                            <label class="eval-define-label">EVALUATION PERIOD (FROM)</label>
                            <input type="date" class="eval-define-input" id="evalPeriodFrom">
                        </div>
                        <div class="eval-define-field">
                            <label class="eval-define-label">EVALUATION PERIOD (TO)</label>
                            <input type="date" class="eval-define-input" id="evalPeriodTo">
                        </div>
                        <div class="eval-define-field">
                            <label class="eval-define-label">TARGET RESPONDENTS</label>
                            <select class="eval-define-select" id="evalRespondents">
                                <option value="">Select Course</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCS">BSCS</option>
                                <option value="All">All Courses</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:flex-end; margin-top:18px;">
                        <button type="button" class="gs-view-btn" onclick="goToStep('build')">Proceed</button>
                    </div>
                </div>
            </div>

            {{-- ── STEP 2: Build ── --}}
            <div class="eval-step-panel" id="stepBuild" style="display:none;">
                <div class="eval-build-header">
                    <span class="eval-build-info" id="evalBuildInfo">BSCS 4-A | Capstone Project 2</span>
                    <button type="button" class="eval-new-block-btn" onclick="addNewBlock()">+ New Block</button>
                </div>

                <div id="evalBlocksContainer">
                    {{-- JS-rendered blocks --}}
                </div>

                <div class="eval-build-actions">
                    <button type="button" class="eval-btn-outline" onclick="goToStep('define')">Back</button>
                    <button type="button" class="gs-view-btn" onclick="goToStep('preview')">Proceed</button>
                </div>
            </div>

            {{-- ── STEP 3: Preview ── --}}
            <div class="eval-step-panel" id="stepPreview" style="display:none;">
                <div class="eval-preview-info" id="evalPreviewInfo">BSCS 4-A | Capstone Project 2 | Prof. Jonnel Mark Diaz</div>

                <div id="evalPreviewContainer">
                    {{-- JS-rendered preview --}}
                </div>

                <div class="eval-build-actions" id="evalPreviewActions">
                    <button type="button" class="eval-btn-outline" id="evalEditBtn" onclick="goToStep('build')">Edit</button>
                    <button type="button" class="gs-view-btn" id="evalSaveBtn" onclick="handleSaveEval()">Save</button>
                    <button type="button" class="gs-view-btn" id="evalPublishBtn" onclick="handlePublishEval()" style="display:none;">Publish</button>
                </div>
            </div>
        </div>

        {{-- ═══ RIGHT: Evaluation Dashboard & Library ═══ --}}
        <div class="eval-dashboard">
            <h2 class="eval-section-title">EVALUATION DASHBOARD &amp; LIBRARY</h2>
            <h3 class="eval-dash-subtitle">Recent Evaluations</h3>
            <div id="evalLibraryList">
                {{-- JS-rendered cards --}}
            </div>
        </div>
    </div>
</div>

{{-- ══════ SUCCESS MODAL ══════ --}}
<div class="pf-modal-overlay" id="evalSuccessModal" style="display:none;">
    <div class="pf-modal-box" style="text-align:center; max-width:420px;">
        <div style="margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>
            </svg>
        </div>
        <div class="pf-modal-title" style="color:#006837;">Success</div>
        <p style="font-size:0.92rem; color:#444; margin-bottom:24px;" id="evalSuccessMsg"></p>
        <div class="pf-modal-actions" style="justify-content:center;">
            <button type="button" class="pf-modal-btn-save" style="background:#006837; min-width:100px;" onmouseover="this.style.background='#004d29'" onmouseout="this.style.background='#006837'" onclick="closeEvalSuccessModal()">OK</button>
        </div>
    </div>
</div>

{{-- ══════ DELETE CONFIRM MODAL ══════ --}}
<div class="eval-crud-overlay" id="evalDeleteModal" style="display:none;">
    <div class="eval-crud-modal" style="text-align:center; max-width:400px;">
        <div style="margin-bottom:14px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
        <h3 class="eval-crud-title" style="text-align:center;">Delete Question</h3>
        <p style="font-size:0.88rem; color:#555; margin-bottom:20px;" id="evalDeleteMsg">Are you sure you want to delete this question?</p>
        <div class="eval-crud-actions" style="justify-content:center;">
            <button type="button" class="eval-btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="gs-view-btn" style="background:#dc3545;" onmouseover="this.style.background='#b02a37'" onmouseout="this.style.background='#dc3545'" onclick="confirmDeleteModal()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/evaluation.js') }}"></script>
@endpush
