@extends('layouts.registrar')

@section('title', 'PLP - TOR')
@section('page-title', 'TOR')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ time() }}">
<style>
    @php($torEmbedded = in_array(strtolower((string) request()->query('embedded', '0')), ['1', 'true', 'yes'], true))
    @php($torPreviewOnly = in_array(strtolower((string) request()->query('preview_only', '0')), ['1', 'true', 'yes'], true))
    @if($torEmbedded)
    .sidebar-overlay,
    .student-topbar,
    .student-page-header,
    .content-footer-wrap > .plp-footer,
    .plp-sidebar,
    .registrar-topbar,
    .plp-footer,
    .sidebar-logout,
    .sidebar-brand,
    .sidebar-nav {
        display: none !important;
    }

    .registrar-layout,
    .student-layout,
    .student-main-wrapper,
    .registrar-main,
    .pf-page,
    .ga-page {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    @endif

    @if($torPreviewOnly)
    .student-topbar,
    .student-page-header,
    .plp-sidebar,
    .sidebar-overlay,
    .content-footer-wrap > .plp-footer {
        display: none !important;
    }

    .student-content {
        padding: 0 !important;
    }

    .app-filter-bar,
    .ga-table-controls,
    .ga-table-wrap {
        display: none !important;
    }

    .ga-page {
        padding: 0 !important;
        min-height: auto !important;
    }

    #torPreviewModal {
        display: flex !important;
        position: static !important;
        background: transparent !important;
        padding: 0 !important;
    }

    #torPreviewModal .tor-preview-modal-box {
        width: 100% !important;
        max-width: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    #torPreviewModal .tor-preview-head {
        display: none !important;
    }

    #torPreviewModal button[onclick="torClosePreview()"] {
        display: none !important;
    }

    #torPreviewModal .req-modal-actions {
        padding-top: 10px !important;
        justify-content: center !important;
    }
    @endif

    @media print {
        @page { size: A4 portrait; margin: 10mm; }
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
            <div style="display:flex; justify-content:flex-end; margin-top:15px;">
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
            <table id="torTable" class="ga-table app-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 54px; text-align: center;">
                            <input type="checkbox" id="torSelectAll" onchange="torToggleSelectAll(this)">
                        </th>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th style="text-align: center; width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody id="torTableBody">
                    <tr data-row-id="1">
                        <td style="text-align: center;"><input type="checkbox" class="tor-row-select" onchange="torSyncSelectAll()"></td>
                        <td>2223A8137</td>
                        <td><button type="button" class="doc-link-btn" onclick="torOpenPreview(1)">Mark Jay Bares</button></td>
                        <td>BSCS</td>
                        <td>Fourth</td>
                        <td>BSCS 4A</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-tor-menu-toggle="torMenu-1" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="torMenu-1">
                                <button type="button" onclick="torOpenEdit(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="torOpenDelete(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-row-id="2">
                        <td style="text-align: center;"><input type="checkbox" class="tor-row-select" onchange="torSyncSelectAll()"></td>
                        <td>2223A8139</td>
                        <td><button type="button" class="doc-link-btn" onclick="torOpenPreview(2)">Andrea Jane Austero</button></td>
                        <td>BSCS</td>
                        <td>Fourth</td>
                        <td>BSCS 4B</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-tor-menu-toggle="torMenu-2" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="torMenu-2">
                                <button type="button" onclick="torOpenEdit(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="torOpenDelete(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-row-id="3">
                        <td style="text-align: center;"><input type="checkbox" class="tor-row-select" onchange="torSyncSelectAll()"></td>
                        <td>2324E0012</td>
                        <td><button type="button" class="doc-link-btn" onclick="torOpenPreview(3)">Analyn Marbibi Rebosora</button></td>
                        <td>BS Entrepreneurship</td>
                        <td>Fourth</td>
                        <td>BSENT 4A</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-tor-menu-toggle="torMenu-3" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="torMenu-3">
                                <button type="button" onclick="torOpenEdit(3)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="torOpenDelete(3)">
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

<div class="req-modal-overlay" id="torEditModal" style="display:none;" onclick="if(event.target===this) torCloseModal('torEditModal')">
    <div class="req-modal-box" style="width: 560px;">
        <h3 class="req-modal-title">EDIT TOR RECORD</h3>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Number</label>
                <input type="text" id="torEditNumber" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input type="text" id="torEditCourse" class="req-modal-input">
            </div>
        </div>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Name</label>
                <input type="text" id="torEditName" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year</label>
                <input type="text" id="torEditYear" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="torCloseModal('torEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="torSaveEdit()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="torDeleteModal" style="display:none;" onclick="if(event.target===this) torCloseModal('torDeleteModal')">
    <div class="req-modal-box" style="width: 440px;">
        <h3 class="req-modal-title">DELETE RECORD</h3>
        <p style="font-size:0.9rem; color:#4b5563; margin: 8px 0 0; text-align:center;">Are you sure you want to delete this TOR record?</p>
        <div class="req-modal-actions" style="margin-top:16px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="torCloseModal('torDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#b42318;" onclick="torConfirmDelete()">Delete</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="torPreviewModal" style="display:none;" onclick="if(event.target===this) torClosePreview()">
    <div class="req-modal-box tor-preview-modal-box">
        <div class="tor-preview-head">
            <h3>TOR PREVIEW</h3>
        </div>
        <div class="tor-preview-wrap">
            <div class="tor-sheet" id="torPreviewSheet"></div>
        </div>
        <div class="req-modal-actions" style="padding: 0 18px 18px; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:8px;">
                <button type="button" class="req-btn-cancel" id="torPrevPageBtn" onclick="torPrevPage()">Prev</button>
                <span id="torPageIndicator" style="font-size:0.84rem; font-weight:700; color:#111827; min-width:84px; text-align:center;">1 / 4</span>
                <button type="button" class="req-btn-cancel" id="torNextPageBtn" onclick="torNextPage()">Next</button>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
            <button type="button" class="req-btn-cancel" onclick="torClosePreview()">Close</button>
            <button type="button" class="req-btn-save" style="min-width: 150px;" onclick="torPrintPreview()">Print Form</button>
            </div>
        </div>
    </div>
</div>

<div id="torPrintContainer" aria-hidden="true"></div>
@endsection

@push('scripts')
<script src="{{ asset('js/tor.js') }}?v={{ time() }}"></script>
@if($torPreviewOnly)
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof torOpenPreview === 'function') {
        torOpenPreview(1);
    }
});
</script>
@endif
@endpush
