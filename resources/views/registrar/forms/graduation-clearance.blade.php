@extends('layouts.registrar')

@section('title', 'PLP - Graduation Clearance')
@section('page-title', 'GRADUATION CLEARANCE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ time() }}">
<style>
    /* The form occupies the upper half of a portrait Legal sheet. */
    .gc-sheet {
        width: 215.9mm;
        min-height: 177.8mm;
        padding: 7mm 10mm 6mm;
        font-size: 9.5pt;
        line-height: 1.2;
    }

    .gc-header {
        margin-bottom: 2mm;
    }

    .gc-header-school,
    .gc-header-office {
        font-size: 10.5pt;
        line-height: 1.15;
    }

    .gc-header-title {
        font-size: 11.5pt;
        margin-top: 2mm;
    }

    .gc-title {
        font-size: 10.5pt;
        margin-bottom: 1px;
    }

    .gc-line-row,
    .gc-sign-row {
        margin-bottom: 1px;
    }

    .gc-label,
    .gc-check-text,
    .gc-check-office,
    .gc-office-colon {
        font-size: 9.2pt;
    }

    .gc-subhint {
        font-size: 7.2pt;
        margin-bottom: 2px;
    }

    .gc-req-title {
        margin-top: 2px;
    }

    .gc-req-sub {
        font-size: 8.8pt;
        margin: 0 0 2px;
    }

    .gc-checklist {
        margin-bottom: 2px;
    }

    .gc-check-row {
        grid-template-columns: minmax(0, 1.5fr) 6px 155px minmax(0, 0.75fr);
        min-height: 18px;
        margin-bottom: 1px;
    }

    .gc-check-box {
        width: 14px;
        height: 11px;
        border-width: 1.5px;
        margin-right: 6px;
    }

    .gc-check-text,
    .gc-check-office {
        line-height: 1.2;
    }

    .gc-declaration-title {
        margin: 2px 0 1px;
    }

    .gc-declaration {
        font-size: 9.2pt;
        line-height: 1.2;
        margin-bottom: 2px;
    }

    .gc-sign-row {
        margin-top: 1px;
    }

    @media print {
        @page {
            size: 8.5in 14in;
            margin: 0;
        }

        body.gc-printing .gc-sheet {
            width: 8.5in;
            height: 7in;
            min-height: 7in;
            max-height: 7in;
            margin: 0;
            padding: 7mm 10mm 6mm;
            box-sizing: border-box;
            font-size: 9.5pt;
            line-height: 1.2 !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        body.gc-printing .gc-check-row {
            grid-template-columns: minmax(0, 1.5fr) 6px 155px minmax(0, 0.75fr) !important;
            min-height: 18px !important;
            margin-bottom: 1px !important;
        }

        body.gc-printing .gc-check-text,
        body.gc-printing .gc-check-office {
            line-height: 1.2 !important;
        }
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
            <div class="gc-action-row">
                <div class="gc-search-wrap">
                    <input type="text" class="app-filter-select frm-search-input" placeholder="Search student..." oninput="gcFilterTable(this.value)">
                </div>
                <button type="button" class="req-btn-save gc-action-btn" onclick="gcOpenBlankPreview()">Preview Form</button>
                <button type="button" class="req-btn-save gc-action-btn" onclick="gcPrintSelected()">Print Selected</button>
                <button type="button" class="req-btn-save gc-action-btn" onclick="gcPrintForm()">Print Form</button>
            </div>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table id="gcTable" class="ga-table app-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 54px; text-align: center;">
                            <input type="checkbox" id="gcSelectAll" onchange="gcToggleSelectAll(this)">
                        </th>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th style="width: 140px;">Form Type</th>
                        <th>Year</th>
                        <th>Section</th>
                    </tr>
                </thead>
                <tbody id="gcTableBody" data-selected-row-id="{{ $selectedStudentId }}">
                    @forelse($graduationClearanceRows as $student)
                    @php
                        $program = trim((string) ($student->program ?: optional($student->canonicalCourse)->code ?: optional($student->canonicalCourse)->name));
                        $programName = trim((string) (optional($student->canonicalCourse)->name ?: $program));
                        $college = trim((string) ($student->college ?: ''));
                        $yearLevel = trim((string) ($student->year_level ?: optional($student->yearBlock)->label));
                        $section = trim((string) (($program ?: 'PROGRAM') . ' ' . ($yearLevel ?: 'YEAR')));
                        $email = trim((string) optional($student->profile)->student_email);
                        $phone = trim((string) optional($student->profile)->mobile_number);
                        $boardProgram = preg_match('/\b(BSED|BEED|BSN|BS\s*NURSING|PSYCHOLOGY|CRIMINOLOGY|ACCOUNTANCY)\b/i', $program . ' ' . $programName);
                        $formType = $boardProgram ? 'board' : 'non-board';
                    @endphp
                    <tr data-row-id="{{ $student->id }}"
                        data-form-type="{{ $formType }}"
                        data-college="{{ $college }}"
                        data-email="{{ $email }}"
                        data-phone="{{ $phone }}"
                        data-date="{{ now()->format('F d, Y') }}">
                        <td style="text-align: center;"><input type="checkbox" class="gc-row-select" onchange="gcSyncSelectAll()"></td>
                        <td>{{ $student->student_no ?: '-' }}</td>
                        <td><button type="button" class="doc-link-btn" onclick="gcOpenPreviewFromRow(this)">{{ $student->name ?: '-' }}</button></td>
                        <td>{{ $program ?: '-' }}</td>
                        <td>{{ $formType === 'board' ? 'Board' : 'Non-Board' }}</td>
                        <td>{{ $yearLevel ?: '-' }}</td>
                        <td>{{ $section }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center; color:#666;">No student records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="gcPreviewModal" style="display:none;" onclick="if(event.target===this) gcClosePreview()">
    <div class="req-modal-box gc-preview-modal-box">
        <div class="gc-preview-head">
            <h3>GRADUATION CLEARANCE PREVIEW</h3>
        </div>
        <div class="gc-preview-wrap">
            <div class="gc-sheet" id="gcPreviewSheet"></div>
        </div>
        <div class="req-modal-actions" style="padding: 0 18px 18px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="gcClosePreview()">Close</button>
            <button type="button" class="req-btn-save" style="min-width: 150px;" onclick="gcPrintPreview()">Print Form</button>
        </div>
    </div>
</div>

<div id="gcPrintContainer" aria-hidden="true"></div>
@endsection

@push('scripts')
<script src="{{ asset('js/graduation-clearance.js') }}?v={{ time() }}"></script>
@endpush
