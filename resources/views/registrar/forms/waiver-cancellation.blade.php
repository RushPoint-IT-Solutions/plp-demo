@extends('layouts.registrar')

@section('title', 'PLP - Waiver for Cancellation of Enrollment')
@section('page-title', 'WAIVER FOR CANCELLATION OF ENROLLMENT')

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
                        <option>First</option>
                        <option>Second</option>
                        <option>Third</option>
                        <option>Fourth</option>
                        <option>Fifth</option>
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
                    <input type="text" class="app-filter-select frm-search-input" placeholder="Search student..." oninput="wceFilterTable(this.value)">
                </div>
                <button type="button" class="req-btn-save frm-action-btn" onclick="wceOpenBlankPreview()">Preview Form</button>
                <button type="button" class="req-btn-save frm-action-btn" onclick="wcePrintSelected()">Print Selected</button>
                <a class="req-btn-cancel frm-action-btn" href="{{ route('registrar.services.reports-admin.waiver-cancellation-reports') }}">Reports</a>
                <button type="button" class="req-btn-save frm-action-btn">Set</button>
            </div>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table id="wceTable" class="ga-table app-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 54px; text-align: center;">
                            <input type="checkbox" id="wceSelectAll" onchange="wceToggleSelectAll(this)">
                        </th>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Semester</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th>Date Requested</th>
                        <th>Reason for Cancellation</th>
                        <th style="text-align: center; width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody id="wceTableBody">
                    @forelse($waiverRows as $record)
                    @php
                        $displayProgram = $record->program ?: optional($record->student)->program ?: '';
                        $displayYear = $record->year_level ?: optional($record->student)->year_level ?: '';
                        $displaySection = $record->section ?: '-';

                        if ($displayProgram !== '' && $displayYear !== '') {
                            preg_match('/\d+/', (string) $displayYear, $yearMatches);
                            $yearNumber = isset($yearMatches[0]) ? $yearMatches[0] : '';
                            $yearLower = strtolower((string) $displayYear);

                            if ($yearNumber === '') {
                                if ($yearLower === 'first') {
                                    $yearNumber = '1';
                                } elseif ($yearLower === 'second') {
                                    $yearNumber = '2';
                                } elseif ($yearLower === 'third') {
                                    $yearNumber = '3';
                                } elseif ($yearLower === 'fourth') {
                                    $yearNumber = '4';
                                }
                            }

                            if ($yearNumber !== '') {
                                $displaySection = trim($displayProgram) . ' -' . $yearNumber . 'A';
                            }
                        }
                    @endphp
                    <tr data-row-id="{{ $record->id }}" data-cancellation-reason="{{ $record->remarks }}">
                        <td style="text-align: center;"><input type="checkbox" class="wce-row-select" onchange="wceSyncSelectAll()"></td>
                        <td>{{ optional($record->student)->student_no ?: '-' }}</td>
                        <td><button type="button" class="doc-link-btn" onclick="wceOpenPreview({{ $record->id }})">{{ optional($record->student)->name ?: '-' }}</button></td>
                        <td>{{ $displayProgram ?: '-' }}</td>
                        <td>{{ $record->semester ?: '-' }}</td>
                        <td>{{ $displayYear ?: '-' }}</td>
                        <td>{{ $displaySection }}</td>
                        <td>{{ optional($record->created_at)->format('M d, Y') ?: '-' }}</td>
                        <td>{{ $record->remarks ?: 'Reason not recorded' }}</td>
                        <td style="text-align:center;">
                            <div class="apst-action-btn" data-wce-menu-toggle="wceMenu-{{ $record->id }}" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="wceMenu-{{ $record->id }}">
                                <button type="button" onclick="wceOpenEdit({{ $record->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" onclick="wceOpenDelete({{ $record->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" style="text-align:center; color:#666;">No waiver records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="wceEditModal" style="display:none;" onclick="if(event.target===this) wceCloseModal('wceEditModal')">
    <div class="req-modal-box" style="width: 560px;">
        <h3 class="req-modal-title">EDIT WAIVER RECORD</h3>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Number</label>
                <input type="text" id="wceEditNumber" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Course</label>
                <input type="text" id="wceEditCourse" class="req-modal-input">
            </div>
        </div>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student Name</label>
                <input type="text" id="wceEditName" class="req-modal-input">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Year</label>
                <input type="text" id="wceEditYear" class="req-modal-input">
            </div>
        </div>
        <div class="req-modal-field-group" style="margin-top:10px;">
            <label class="req-modal-label" for="wceEditReason">Reason for Cancellation</label>
            <textarea id="wceEditReason" class="req-modal-input" maxlength="255" rows="3" placeholder="Enter the student's reason for cancelling enrollment"></textarea>
        </div>
        <div class="sc-modal-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Semester</label>
                <select id="wceEditSemester" class="req-modal-input">
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="wceCloseModal('wceEditModal')">Cancel</button>
            <button type="button" class="req-btn-save" onclick="wceSaveEdit()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="wceDeleteModal" style="display:none;" onclick="if(event.target===this) wceCloseModal('wceDeleteModal')">
    <div class="req-modal-box" style="width: 440px;">
        <h3 class="req-modal-title">DELETE RECORD</h3>
        <p style="font-size:0.9rem; color:#4b5563; margin: 8px 0 0; text-align:center;">Are you sure you want to delete this Waiver for Cancellation record?</p>
        <div class="req-modal-actions" style="margin-top:16px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="wceCloseModal('wceDeleteModal')">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#b42318;" onclick="wceConfirmDelete()">Delete</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="wcePreviewModal" style="display:none;" onclick="if(event.target===this) wceClosePreview()">
    <div class="req-modal-box wce-preview-modal-box">
        <div class="wce-preview-head">
            <h3>WAIVER FOR CANCELLATION OF ENROLLMENT PREVIEW</h3>
        </div>
        <div class="wce-preview-wrap">
            <div class="wce-sheet" id="wcePreviewSheet"></div>
        </div>
        <div class="req-modal-actions" style="padding: 0 18px 18px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="wceClosePreview()">Close</button>
            <button type="button" class="req-btn-save" style="min-width: 150px;" onclick="wcePrintPreview()">Print Form</button>
        </div>
    </div>
</div>

<div id="wcePrintContainer" aria-hidden="true"></div>
@endsection

@push('scripts')
<script>
window.wceConfig = {
    csrfToken: @json(csrf_token()),
    updateUrlTemplate: @json(route('registrar.registrar-menu.forms.waiver-cancellation.update', ['cancellationWaiver' => '__ID__'])),
    destroyUrlTemplate: @json(route('registrar.registrar-menu.forms.waiver-cancellation.destroy', ['cancellationWaiver' => '__ID__']))
};
</script>
<script src="{{ asset('js/waiver-cancellation.js') }}?v={{ time() }}"></script>
@endpush
