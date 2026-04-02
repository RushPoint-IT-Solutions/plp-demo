@extends('layouts.registrar')

@section('title', 'PLP - Diploma')
@section('page-title', 'DIPLOMA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ time() }}">
<style>
    @media print {
        @page { size: landscape; margin: 12mm; }
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
                        <option>BSCS 4A</option>
                        <option>BSCS 4B</option>
                        <option>BSIT 4A</option>
                        <option>BSED 4A</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:15px; gap: 8px;">
                <div style="margin-right:auto; min-width:260px; max-width:340px; width:100%;">
                    <input type="text" class="app-filter-select" style="width:100%;" placeholder="Search student..." oninput="diplomaFilterTable(this.value)">
                </div>
                <button type="button" class="req-btn-save" style="min-width: 160px; font-weight: 700;" onclick="diplomaPrintSelected()">Print Selected</button>
                <button type="button" class="req-btn-save" style="min-width: 120px; font-weight: 700;">Set</button>
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
            <table id="diplomaTable" class="ga-table app-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 54px; text-align: center;">
                            <input type="checkbox" id="diplomaSelectAll" onchange="diplomaToggleSelectAll(this)">
                        </th>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th style="width: 140px;">Status</th>
                        <th style="text-align: center; width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody id="diplomaTableBody">
                    <tr data-row-id="1">
                        <td style="text-align: center;"><input type="checkbox" class="diploma-row-select" onchange="diplomaSyncSelectAll()"></td>
                        <td class="diploma-cell-number">2122B0104</td>
                        <td class="diploma-cell-name"><button type="button" class="doc-link-btn" onclick="diplomaOpenPreview(1)">Jhon Mark Samson</button></td>
                        <td class="diploma-cell-program">BSIT</td>
                        <td class="diploma-cell-year">Fourth</td>
                        <td class="diploma-cell-section">BSIT 4A</td>
                        <td>
                            <select class="app-filter-select diploma-copy-select" onchange="diplomaHandleCopyChange(this)">
                                <option value="print-1">Print 1</option>
                                <option value="print-2" selected>Print 2</option>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-diploma-menu-toggle="diplomaMenu-1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="diplomaMenu-1">
                                <button type="button" onclick="diplomaOpenEdit(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="diplomaOpenDelete(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-row-id="2">
                        <td style="text-align: center;"><input type="checkbox" class="diploma-row-select" onchange="diplomaSyncSelectAll()"></td>
                        <td class="diploma-cell-number">2122B0115</td>
                        <td class="diploma-cell-name"><button type="button" class="doc-link-btn" onclick="diplomaOpenPreview(2)">Mary Ann dela Cruz</button></td>
                        <td class="diploma-cell-program">BSED</td>
                        <td class="diploma-cell-year">Fourth</td>
                        <td class="diploma-cell-section">BSED 4A</td>
                        <td>
                            <select class="app-filter-select diploma-copy-select" onchange="diplomaHandleCopyChange(this)">
                                <option value="print-1" selected>Print 1</option>
                                <option value="print-2">Print 2</option>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-diploma-menu-toggle="diplomaMenu-2" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="diplomaMenu-2">
                                <button type="button" onclick="diplomaOpenEdit(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="diplomaOpenDelete(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-row-id="3">
                        <td style="text-align: center;"><input type="checkbox" class="diploma-row-select" onchange="diplomaSyncSelectAll()"></td>
                        <td class="diploma-cell-number">2324E0012</td>
                        <td class="diploma-cell-name"><button type="button" class="doc-link-btn" onclick="diplomaOpenPreview(3)">Analyn Marbibi Rebosora</button></td>
                        <td class="diploma-cell-program">BS Entrepreneurship</td>
                        <td class="diploma-cell-year">Fourth</td>
                        <td class="diploma-cell-section">BSENT 4A</td>
                        <td>
                            <select class="app-filter-select diploma-copy-select" onchange="diplomaHandleCopyChange(this)">
                                <option value="print-1">Print 1</option>
                                <option value="print-2" selected>Print 2</option>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-diploma-menu-toggle="diplomaMenu-3" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="diplomaMenu-3">
                                <button type="button" onclick="diplomaOpenEdit(3)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="diplomaOpenDelete(3)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<div class="req-modal-overlay" id="diplomaEditModal" style="display:none;" onclick="if(event.target===this) diplomaCloseModal('diplomaEditModal')">
    <div class="req-modal-box" style="width: 560px;">
        <h3 class="req-modal-title">EDIT DIPLOMA RECORD</h3>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Number</label>
                <input type="text" id="diplomaEditNumber" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input type="text" id="diplomaEditCourse" class="req-modal-input">
            </div>
        </div>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Name</label>
                <input type="text" id="diplomaEditName" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year</label>
                <input type="text" id="diplomaEditYear" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="diplomaCloseModal('diplomaEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="diplomaSaveEdit()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="diplomaDeleteModal" style="display:none;" onclick="if(event.target===this) diplomaCloseModal('diplomaDeleteModal')">
    <div class="req-modal-box" style="width: 440px;">
        <h3 class="req-modal-title">DELETE RECORD</h3>
        <p style="font-size:0.9rem; color:#4b5563; margin: 8px 0 0; text-align:center;">Are you sure you want to delete this Diploma record?</p>
        <div class="req-modal-actions" style="margin-top:16px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="diplomaCloseModal('diplomaDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#b42318;" onclick="diplomaConfirmDelete()">Delete</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="diplomaPreviewModal" style="display:none;" onclick="if(event.target===this) diplomaClosePreview()">
    <div class="req-modal-box dpl-preview-modal-box">
        <div class="dpl-preview-head">
            <h3>DIPLOMA PREVIEW</h3>
        </div>
        <div class="dpl-preview-wrap">
            <div class="dpl-sheet" id="diplomaPreviewSheet"></div>
        </div>
        <div class="req-modal-actions" style="padding: 0 18px 18px; justify-content:flex-end;">
            <button type="button" class="req-btn-cancel" onclick="diplomaClosePreview()">Close</button>
            <button type="button" class="req-btn-save" style="min-width: 150px;" onclick="diplomaPrintPreview()">Print Form</button>
        </div>
    </div>
</div>

<div id="diplomaPrintContainer" aria-hidden="true"></div>
@endsection

@push('scripts')
<script src="{{ asset('js/diploma.js') }}?v={{ time() }}"></script>
@endpush
