@extends('layouts.registrar')

@section('title', 'PLP - Permission to Cross-Enroll')
@section('page-title', 'PERMISSION TO CROSS-ENROLL')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ time() }}">
<style>
    @media print {
        @page { size: portrait; margin: 8mm; }
    }
</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="ga-page">
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
                        <option>BSENT</option>
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
                        <option>BSIT 4A</option>
                        <option>BSCS 4A</option>
                        <option>BSED 4A</option>
                    </select>
                </div>
            </div>
            <div class="frm-action-row">
                <div class="frm-search-wrap">
                    <input type="text" class="app-filter-select frm-search-input" placeholder="Search student..." oninput="pceFilterTable(this.value)">
                </div>
                <button type="button" class="req-btn-save frm-action-btn" onclick="pceOpenBlankPreview()">Preview Form</button>
                <button type="button" class="req-btn-save frm-action-btn" onclick="pcePrintSelected()">Print Selected</button>
                <button type="button" class="req-btn-save frm-action-btn">Set</button>
            </div>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table id="pceTable" class="ga-table app-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 54px; text-align: center;">
                            <input type="checkbox" id="pceSelectAll" onchange="pceToggleSelectAll(this)">
                        </th>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th style="text-align: center; width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody id="pceTableBody">
                    @forelse($crossEnrollRows as $record)
                    @php
                        $studentSubjects = optional($record->student)->subjects ?: collect();
                        $subjectPayload = $studentSubjects->map(function ($subject) {
                            return [
                                'code' => (string) ($subject->code ?: ''),
                                'description' => (string) ($subject->name ?: ''),
                                'units' => $subject->units !== null ? (string) $subject->units : '',
                            ];
                        })->values();
                    @endphp
                    <tr data-row-id="{{ $record->id }}"
                        data-school-year="{{ $record->school_year ?: optional($record->student)->school_year }}"
                        data-semester="{{ $record->semester ?: optional($record->student)->semester }}"
                        data-subjects="{{ e($subjectPayload->toJson()) }}">
                        <td style="text-align: center;"><input type="checkbox" class="pce-row-select" onchange="pceSyncSelectAll()"></td>
                        <td>{{ optional($record->student)->student_no ?: '-' }}</td>
                        <td><button type="button" class="doc-link-btn" onclick="pceOpenPreview({{ $record->id }})">{{ optional($record->student)->name ?: '-' }}</button></td>
                        <td>{{ $record->program ?: optional($record->student)->program ?: '-' }}</td>
                        <td>{{ $record->year_level ?: optional($record->student)->year_level ?: '-' }}</td>
                        <td>{{ $record->section ?: '-' }}</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-pce-menu-toggle="pceMenu-{{ $record->id }}" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="pceMenu-{{ $record->id }}">
                                <button type="button" onclick="pceOpenEdit({{ $record->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="pceOpenDelete({{ $record->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center; color:#666;">No cross-enroll records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="pceEditModal" style="display:none;" onclick="if(event.target===this) pceCloseModal('pceEditModal')">
    <div class="req-modal-box" style="width: 560px;">
        <h3 class="req-modal-title">EDIT CROSS-ENROLL RECORD</h3>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Number</label>
                <input type="text" id="pceEditNumber" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input type="text" id="pceEditCourse" class="req-modal-input">
            </div>
        </div>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Name</label>
                <input type="text" id="pceEditName" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year</label>
                <input type="text" id="pceEditYear" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="pceCloseModal('pceEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="pceSaveEdit()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="pceDeleteModal" style="display:none;" onclick="if(event.target===this) pceCloseModal('pceDeleteModal')">
    <div class="req-modal-box" style="width: 440px;">
        <h3 class="req-modal-title">DELETE RECORD</h3>
        <p style="font-size:0.9rem; color:#4b5563; margin: 8px 0 0; text-align:center;">Are you sure you want to delete this Cross-Enroll Permit record?</p>
        <div class="req-modal-actions" style="margin-top:16px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="pceCloseModal('pceDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#b42318;" onclick="pceConfirmDelete()">Delete</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="pcePreviewModal" style="display:none;" onclick="if(event.target===this) pceClosePreview()">
    <div class="req-modal-box pce-preview-modal-box">
        <div class="pce-preview-head">
            <h3>PERMISSION TO CROSS-ENROLL PREVIEW</h3>
        </div>
        <div class="pce-preview-wrap">
            <div class="pce-sheet" id="pcePreviewSheet"></div>
        </div>
        <div class="req-modal-actions" style="padding: 0 18px 18px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="pceClosePreview()">Close</button>
            <button type="button" class="req-btn-save" style="min-width: 150px;" onclick="pcePrintPreview()">Print Form</button>
        </div>
    </div>
</div>

<div id="pcePrintContainer" aria-hidden="true"></div>
@endsection

@push('scripts')
<script>
window.pceConfig = {
    csrfToken: @json(csrf_token()),
    updateUrlTemplate: @json(route('registrar.registrar-menu.forms.permission-cross-enroll.update', ['crossEnrollmentRequest' => '__ID__'])),
    destroyUrlTemplate: @json(route('registrar.registrar-menu.forms.permission-cross-enroll.destroy', ['crossEnrollmentRequest' => '__ID__']))
};
</script>
<script src="{{ asset('js/permission-cross-enroll.js') }}?v={{ time() }}"></script>
@endpush
