@extends('layouts.registrar')

@section('title', 'PLP - Honorable Dismissal')
@section('page-title', 'HONORABLE DISMISSAL')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ time() }}">
<style>
    @media print {
        @page { size: portrait; margin: 8mm; }
    }
</style>
@endpush

@section('content')
@php
    $hdTagUrlTemplate = route('registrar.registrar-menu.forms.honorable-dismissal.tag', ['student' => '__STUDENT__']);
    $hdIssueUrlTemplate = route('registrar.registrar-menu.forms.honorable-dismissal.issue', ['student' => '__STUDENT__']);
    $hdBulkIssueUrl = route('registrar.registrar-menu.forms.honorable-dismissal.bulk-issue');
    $hdLayoutUrlTemplate = route('registrar.registrar-menu.forms.honorable-dismissal.template.layout', ['student' => '__STUDENT__']);
    $hdBlankLayoutUrl = route('registrar.registrar-menu.forms.honorable-dismissal.template.layout');
    $hdTemplateSaveUrl = route('registrar.registrar-menu.forms.honorable-dismissal.template.save');
@endphp
<div class="pf-page">
    <div class="ga-page">
        @if(!empty($hdRecordsUnavailable))
            <div class="alert alert-warning" role="alert">
                Honorable Dismissal monitoring is not available yet. Please run the latest migrations.
            </div>
        @endif
        <div class="app-filter-bar">
            <div class="app-filter-row" style="align-items: flex-end;">
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">School Year:</label>
                    <select class="app-filter-select">
                        <option>2025-2026</option>
                        <option>2024-2025</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Semester</label>
                    <select class="app-filter-select">
                        <option>First</option>
                        <option>Second</option>
                        <option>Summer</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:2;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Program</label>
                    <select class="app-filter-select">
                        <option>-Select Program-</option>
                        <option>BSCS</option>
                        <option>BSIT</option>
                        <option>BSED</option>
                        <option>BSBA</option>
                        <option>BSN</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Year Level</label>
                    <select class="app-filter-select">
                        @include('registrar.forms.partials.fourth-fifth-year-options')
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Section</label>
                    <select class="app-filter-select">
                        <option>-Select Section-</option>
                        <option>BSIT 1B</option>
                        <option>BSN 1-BENNER</option>
                        <option>BSCS 4A</option>
                    </select>
                </div>
            </div>
            <div class="frm-action-row">
                <div class="frm-search-wrap">
                    <input type="text" class="app-filter-select frm-search-input" placeholder="Search student..." oninput="hdFilterTable(this.value)">
                </div>
                <button type="button" class="req-btn-save frm-action-btn" onclick="hdOpenBlankPreview()">Preview Form</button>
                @if(\App\Support\UserAccessGate::currentUserAllows('documents_forms_honorable_dismissal', 'print'))
                <button type="button" class="req-btn-save frm-action-btn" onclick="hdPrintSelected()">Print Selected</button>
                <button type="button" class="req-btn-save frm-action-btn" onclick="hdDismissAllSelected()">Dismiss All</button>
                @endif
                <button type="button" class="req-btn-save frm-action-btn">Set</button>
            </div>
        </div>

        <h3 style="margin: 4px 0 10px; color:#006837; font-size:1rem; font-weight:800;">TAG STUDENT FOR DISMISSAL</h3>
        <div class="ga-table-wrap app-table-wrap" style="margin-bottom:24px;">
            <table class="ga-table app-table" id="hdCandidateTable" style="min-width: 820px;">
                <thead>
                    <tr>
                        <th style="width: 54px; text-align: center;">
                            <input type="checkbox" id="hdCandidateSelectAll" onchange="hdToggleCandidateSelectAll(this)">
                        </th>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th style="text-align: center; width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody id="hdCandidateTableBody">
                    @forelse($honorableDismissalCandidates as $student)
                        @php
                            $program = trim((string) ($student->program ?: optional($student->canonicalCourse)->code ?: optional($student->canonicalCourse)->name));
                            $yearLevel = trim((string) ($student->year_level ?: optional($student->yearBlock)->label));
                            $isGraduateCandidate = $student->relationLoaded('graduateTagging') && $student->graduateTagging && $student->graduateTagging->is_graduate;
                            $isTransferredCandidate = (bool) ($student->is_withdrawn ?? false);
                            $candidateStatus = $isGraduateCandidate ? 'Graduated' : ($isTransferredCandidate ? 'Transferred' : 'Eligible');
                        @endphp
                        <tr data-candidate-id="{{ $student->id }}">
                            <td style="text-align: center;"><input type="checkbox" class="hd-candidate-row-select" onchange="hdSyncCandidateSelectAll()"></td>
                            <td>{{ $student->student_no ?: '-' }}</td>
                            <td>{{ $student->name ?: '-' }}</td>
                            <td>{{ $program ?: '-' }}</td>
                            <td>{{ $yearLevel ?: '-' }}</td>
                            <td>{{ $candidateStatus }}</td>
                            <td style="text-align:center;">
                                <button type="button" class="req-btn-save" style="min-width:130px;" onclick="hdTagForDismissal({{ $student->id }})">For Dismissal</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; color:#666;">No graduated or transferred students pending dismissal tagging.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h3 style="margin: 4px 0 10px; color:#006837; font-size:1rem; font-weight:800;">HONORABLE DISMISSAL MONITORING</h3>
        <div class="ga-table-controls" style="margin-bottom: 12px; display:flex; font-size: 0.85rem; font-weight: 600; color: #006837; align-items: center; gap: 8px;">
            <span style="letter-spacing: 0.05em;">SHOW</span>
            <select class="app-filter-select" style="width: auto; padding: 4px 28px 4px 12px; height: 32px; font-size: 0.85rem;">
                <option>10</option>
                <option>25</option>
                <option>50</option>
            </select>
            <span style="letter-spacing: 0.05em;">ENTRIES</span>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table id="hdTable" class="ga-table app-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>HD No.</th>
                        <th>Status</th>
                        <th>Date Issued</th>
                        <th style="text-align: center; width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody id="hdTableBody" data-selected-row-id="{{ $selectedStudentId }}">
                    @forelse($honorableDismissalRows as $student)
                    @php
                        $program = trim((string) ($student->program ?: optional($student->canonicalCourse)->code ?: optional($student->canonicalCourse)->name));
                        $yearLevel = trim((string) ($student->year_level ?: optional($student->yearBlock)->label));
                        $schoolYear = trim((string) ($student->school_year ?: optional($student->academicTerm)->school_year));
                        $semester = trim((string) ($student->semester ?: optional($student->academicTerm)->term));
                        $hdStatus = trim((string) ($student->hd_status ?? 'for_dismissal'));
                        $hdStatusLabel = $hdStatus === 'issued' ? 'Issued' : 'Pending for Dismissal';
                        $hdIssuedAt = $student->hd_issued_at ? \Carbon\Carbon::parse($student->hd_issued_at)->format('F d, Y') : '-';
                    @endphp
                    <tr data-row-id="{{ $student->id }}"
                        data-school-year="{{ $schoolYear }}"
                        data-semester="{{ $semester }}"
                        data-hd-no="{{ $student->hd_no ?: ($student->student_no ? 'HD-' . $student->student_no : '') }}"
                        data-hd-date="{{ $student->hd_issued_at ? \Carbon\Carbon::parse($student->hd_issued_at)->format('F d, Y') : now()->format('F d, Y') }}"
                        data-hd-status="{{ $hdStatus }}">
                        <td>{{ $student->student_no ?: '-' }}</td>
                        <td><button type="button" class="doc-link-btn" onclick="hdOpenPreview({{ $student->id }})">{{ $student->name ?: '-' }}</button></td>
                        <td>{{ $program ?: '-' }}</td>
                        <td>{{ $student->hd_no ?: '-' }}</td>
                        <td>{{ $hdStatusLabel }}</td>
                        <td>{{ $hdIssuedAt }}</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-hd-menu-toggle="hdMenu-{{ $student->id }}" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="hdMenu-{{ $student->id }}">
                                <button type="button" onclick="hdOpenPreview({{ $student->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                                    Preview
                                </button>
                                <button type="button" onclick="hdDownloadRow({{ $student->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                                    Download
                                </button>
                                <button type="button" onclick="hdOpenEdit({{ $student->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="hdOpenDelete({{ $student->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Remove
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center; color:#666;">No student records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="req-modal-overlay" id="hdEditModal" style="display:none;" onclick="if(event.target===this) hdCloseModal('hdEditModal')">
    <div class="req-modal-box" style="width: 560px;">
        <h3 class="req-modal-title">EDIT HONORABLE DISMISSAL RECORD</h3>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Number</label>
                <input type="text" id="hdEditNumber" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input type="text" id="hdEditCourse" class="req-modal-input">
            </div>
        </div>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Name</label>
                <input type="text" id="hdEditName" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year</label>
                <input type="text" id="hdEditYear" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="hdCloseModal('hdEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="hdSaveEdit()">Save</button>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="req-modal-overlay" id="hdDeleteModal" style="display:none;" onclick="if(event.target===this) hdCloseModal('hdDeleteModal')">
    <div class="req-modal-box" style="width: 440px;">
        <h3 class="req-modal-title">DELETE RECORD</h3>
        <p style="font-size:0.9rem; color:#4b5563; margin: 8px 0 0; text-align:center;">Are you sure you want to delete this Honorable Dismissal record?</p>
        <div class="req-modal-actions" style="margin-top:16px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="hdCloseModal('hdDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#b42318;" onclick="hdConfirmDelete()">Delete</button>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="req-modal-overlay" id="hdPreviewModal" style="display:none;" onclick="if(event.target===this) hdClosePreview()">
    <div class="req-modal-box hd-preview-modal-box">
        <div class="hd-preview-head">
            <h3>HONORABLE DISMISSAL PREVIEW</h3>
        </div>
        <div class="hd-preview-wrap">
            <div class="hd-editor-toolbar" id="hdEditorToolbar" aria-hidden="true">
                <select id="hdFontFamily" title="Font family">
                    <option value="Arial">Arial</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Courier New">Courier New</option>
                    <option value="Georgia">Georgia</option>
                </select>
                <input type="number" id="hdFontSize" title="Font size" min="6" max="96" step="1">
                <button type="button" data-hd-style="bold" title="Bold">B</button>
                <button type="button" data-hd-style="italic" title="Italic"><em>I</em></button>
                <button type="button" data-hd-style="underline" title="Underline"><u>U</u></button>
                <select id="hdTextAlign" title="Text alignment">
                    <option value="left">Left</option>
                    <option value="center">Center</option>
                    <option value="right">Right</option>
                    <option value="justify">Justify</option>
                </select>
                <label>Top <input type="number" id="hdTopPercent" min="0" max="100" step="0.1"></label>
                <label>Left <input type="number" id="hdLeftPercent" min="0" max="100" step="0.1"></label>
                <button type="button" id="hdDeleteElement" title="Delete selected element">Delete Element</button>
            </div>
            <div class="hd-sheet" id="hdPreviewSheet"></div>
        </div>
        <div class="req-modal-actions" style="padding: 0 18px 18px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="hdClosePreview()">Close</button>
            <button type="button" class="req-btn-save" data-hd-save-layout style="min-width: 180px;" onclick="hdSaveLayoutTemplate()">Save Layout Template</button>
            <button type="button" class="req-btn-save" style="min-width: 150px;" onclick="hdDownloadPreview()">Download Form</button>
            <button type="button" class="req-btn-save" style="min-width: 150px;" onclick="hdPrintPreview()">Print Form</button>
        </div>
    </div>
</div>

<div id="hdPrintContainer" aria-hidden="true"></div>
@endsection

@push('scripts')
<script>
window.hdTagUrlTemplate = @json($hdTagUrlTemplate);
window.hdIssueUrlTemplate = @json($hdIssueUrlTemplate);
window.hdBulkIssueUrl = @json($hdBulkIssueUrl);
window.hdLayoutUrlTemplate = @json($hdLayoutUrlTemplate);
window.hdBlankLayoutUrl = @json($hdBlankLayoutUrl);
window.hdTemplateSaveUrl = @json($hdTemplateSaveUrl);
</script>
<script src="{{ asset('js/honorable-dismissal.js') }}?v={{ time() }}"></script>
@endpush
