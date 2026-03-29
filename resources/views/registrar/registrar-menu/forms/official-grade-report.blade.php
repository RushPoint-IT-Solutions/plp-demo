@extends('layouts.registrar')

@section('title', 'PLP - Official Grade Report')
@section('page-title', 'OFFICIAL GRADE REPORT')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ time() }}">
<style>
    .frm-action-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
    }

    .frm-search-wrap {
        margin-right: auto;
        min-width: 260px;
        max-width: 340px;
        width: 100%;
    }

    .frm-action-btn {
        min-width: 120px;
        font-weight: 700;
        flex: 0 0 auto;
    }

    @media (max-width: 991.98px) {
        .frm-action-row {
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .frm-search-wrap {
            margin-right: 0;
            min-width: 0;
            max-width: none;
            flex: 1 1 100%;
        }

        .frm-action-btn {
            flex: 1 1 calc(50% - 4px);
            min-width: 0;
        }
    }

    @media (max-width: 575.98px) {
        .frm-action-btn {
            flex-basis: 100%;
        }
    }

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
                        <option>BSN</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Year Level</label>
                    <select class="app-filter-select">
                        <option>First</option>
                        <option>Second</option>
                        <option>Third</option>
                        <option>Fourth</option>
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
                    <input type="text" class="app-filter-select" style="width:100%;" placeholder="Search student..." oninput="ogrFilterTable(this.value)">
                </div>
                <button type="button" class="req-btn-save frm-action-btn" onclick="ogrOpenBlankPreview()">Preview Form</button>
                <button type="button" class="req-btn-save frm-action-btn" onclick="ogrPrintSelected()">Print Selected</button>
                <button type="button" class="req-btn-save frm-action-btn">Set</button>
            </div>
        </div>

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
            <table id="ogrTable" class="ga-table app-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 54px; text-align: center;">
                            <input type="checkbox" id="ogrSelectAll" onchange="ogrToggleSelectAll(this)">
                        </th>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th style="text-align: center; width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody id="ogrTableBody">
                    @forelse($gradeReportRows as $row)
                    <tr data-row-id="{{ $row['row_id'] }}" data-student-id="{{ $row['student_id'] }}">
                        <td style="text-align: center;"><input type="checkbox" class="ogr-row-select" onchange="ogrSyncSelectAll()"></td>
                        <td>{{ $row['student_no'] }}</td>
                        <td><button type="button" class="doc-link-btn" onclick="ogrOpenPreview({{ $row['row_id'] }})">{{ $row['student_name'] }}</button></td>
                        <td>{{ $row['program'] }}</td>
                        <td>{{ $row['year'] }}</td>
                        <td>{{ $row['section'] }}</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-ogr-menu-toggle="ogrMenu-{{ $row['row_id'] }}" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="ogrMenu-{{ $row['row_id'] }}">
                                <button type="button" onclick="ogrOpenEdit({{ $row['row_id'] }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="ogrOpenDelete({{ $row['row_id'] }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center; color:#666;">No students available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="req-modal-overlay" id="ogrEditModal" style="display:none;" onclick="if(event.target===this) ogrCloseModal('ogrEditModal')">
    <div class="req-modal-box" style="width: 560px;">
        <h3 class="req-modal-title">EDIT GRADE REPORT RECORD</h3>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Number</label>
                <input type="text" id="ogrEditNumber" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input type="text" id="ogrEditCourse" class="req-modal-input">
            </div>
        </div>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Name</label>
                <input type="text" id="ogrEditName" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year</label>
                <input type="text" id="ogrEditYear" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="ogrCloseModal('ogrEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="ogrSaveEdit()">Save</button>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="req-modal-overlay" id="ogrDeleteModal" style="display:none;" onclick="if(event.target===this) ogrCloseModal('ogrDeleteModal')">
    <div class="req-modal-box" style="width: 440px;">
        <h3 class="req-modal-title">DELETE RECORD</h3>
        <p style="font-size:0.9rem; color:#4b5563; margin: 8px 0 0; text-align:center;">Are you sure you want to delete this Grade Report record?</p>
        <div class="req-modal-actions" style="margin-top:16px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="ogrCloseModal('ogrDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#b42318;" onclick="ogrConfirmDelete()">Delete</button>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="req-modal-overlay" id="ogrPreviewModal" style="display:none;" onclick="if(event.target===this) ogrClosePreview()">
    <div class="req-modal-box ogr-preview-modal-box">
        <div class="ogr-preview-head">
            <h3>OFFICIAL GRADE REPORT PREVIEW</h3>
        </div>
        <div class="ogr-preview-wrap">
            <div class="ogr-sheet" id="ogrPreviewSheet"></div>
        </div>
        <div class="req-modal-actions" style="padding: 0 18px 18px; justify-content:flex-end;">
            <button type="button" class="req-btn-cancel" onclick="ogrClosePreview()">Close</button>
            <button type="button" class="req-btn-save" style="min-width: 150px;" onclick="ogrPrintPreview()">Print Form</button>
        </div>
    </div>
</div>

<div id="ogrPrintContainer" aria-hidden="true"></div>
@endsection

@push('scripts')
<script>
window.ogrSubjectsByRow = @json($subjectsByRow ?? []);
window.ogrMetaByRow = @json($metaByRow ?? []);
</script>
<script src="{{ asset('js/official-grade-report.js') }}?v={{ time() }}"></script>
@endpush
