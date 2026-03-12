@extends('layouts.registrar')

@section('title', 'PLP - Clinic Record')
@section('page-title', 'INFIRMARY LOG')

@section('content')
<div class="pf-page">

    {{-- ═══ VIEW 1: Student List ═══ --}}
    <div id="crListView">
        {{-- Filter Bar --}}
        <div class="cr-filter-bar">
            <div class="cr-filter-row">
                <div class="cr-filter-group cr-filter-grow">
                    <span class="cr-filter-label">SEARCH</span>
                    <div class="pf-search-wrap" style="max-width:100%;">
                        <svg class="pf-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" class="pf-search-input" placeholder="Search Student ID/ Name" id="crSearch" oninput="filterClinicList()">
                    </div>
                </div>
                <div class="cr-filter-group">
                    <span class="cr-filter-label">COURSE</span>
                    <select class="cr-filter-select" id="crCourse" onchange="filterClinicList()">
                        <option value="">All</option>
                        <option value="BSIT" selected>BSIT</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSED">BSED</option>
                        <option value="BSN">BSN</option>
                    </select>
                </div>
                <div class="cr-filter-group">
                    <span class="cr-filter-label">YEAR LEVEL</span>
                    <select class="cr-filter-select" id="crYearLevel" onchange="filterClinicList()">
                        <option value="">All</option>
                        <option value="First">First</option>
                        <option value="Second">Second</option>
                        <option value="Third">Third</option>
                        <option value="Fourth" selected>Fourth</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                <button type="button" class="gs-view-btn" onclick="printRecords()">Print Records</button>
            </div>
        </div>

        {{-- Student Table --}}
        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Program</th>
                    </tr>
                </thead>
                <tbody id="crTableBody">
                    {{-- JS-rendered --}}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" id="crTotalStudents" class="cr-total-cell">Total Students: 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═══ VIEW 2: Personal Information ═══ --}}
    <div id="crPersonalView" style="display:none;">
        <div class="cr-card">
            <h2 class="cr-card-title">PERSONAL INFORMATION</h2>
            <div class="cr-info-grid" id="crPersonalGrid">
                {{-- JS-rendered --}}
            </div>
            <div class="cr-card-actions">
                <button type="button" class="gs-view-btn" onclick="goToStep('medical')">Next</button>
            </div>
        </div>
    </div>

    {{-- ═══ VIEW 3: Medical History ═══ --}}
    <div id="crMedicalView" style="display:none;">
        <div class="cr-card">
            <h2 class="cr-card-title">MEDICAL HISTORY</h2>

            {{-- Medical History Table --}}
            <div class="cr-med-table-wrap">
                <div class="cr-med-header">
                    <span class="cr-med-hcol cr-med-hcol-item">ITEM<br>/CONDITION</span>
                    <span class="cr-med-hcol">AGE</span>
                    <span class="cr-med-hcol">DATE</span>
                    <span class="cr-med-hcol">MX/DX</span>
                    <span class="cr-med-hcol">HOSPITAL/CLINIC</span>
                    <span class="cr-med-hcol cr-med-hcol-actions"></span>
                </div>
                <div id="crMedHistoryRows">
                    {{-- JS-rendered --}}
                </div>
            </div>

            {{-- Medical Condition --}}
            <h3 class="cr-subtitle">MEDICAL CONDITION:</h3>
            <div class="cr-condition-list" id="crConditionList">
                {{-- JS-rendered --}}
            </div>

            <div class="cr-card-actions">
                <button type="button" class="eval-btn-outline" onclick="goToStep('personal')">Previous</button>
                <button type="button" class="gs-view-btn" onclick="goToStep('immunization')">Next</button>
            </div>
        </div>
    </div>

    {{-- ═══ VIEW 4: Immunization ═══ --}}
    <div id="crImmunizationView" style="display:none;">
        <div class="cr-card">
            <h2 class="cr-card-title">IMMUNIZATION</h2>

            <div class="cr-imm-grid">
                {{-- Row 1 --}}
                <div class="cr-imm-box">
                    <label class="cr-imm-check"><input type="checkbox"> <strong>BCG</strong></label>
                </div>
                <div class="cr-imm-box">
                    <label class="cr-imm-check"><input type="checkbox"> <strong>DPT</strong></label>
                    <div class="cr-imm-doses">
                        <div class="cr-imm-dose"><span>1st Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>2nd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>3rd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>Booster 1:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>Booster 2:</span><input type="text" placeholder="dd/mm/yy"></div>
                    </div>
                </div>
                <div class="cr-imm-box">
                    <label class="cr-imm-check"><input type="checkbox"> <strong>H. Influenza B</strong></label>
                    <div class="cr-imm-doses">
                        <div class="cr-imm-dose"><span>1st Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>2nd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>3rd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>Booster:</span><input type="text" placeholder="dd/mm/yy"></div>
                    </div>
                </div>
                <div class="cr-imm-box">
                    <label class="cr-imm-check"><input type="checkbox"> <strong>Hepatitis A</strong></label>
                    <div class="cr-imm-doses">
                        <div class="cr-imm-dose"><span>1st Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>2nd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>3rd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                    </div>
                </div>

                {{-- Row 2 --}}
                <div class="cr-imm-box">
                    <label class="cr-imm-check"><input type="checkbox"> <strong>Hepatitis B</strong></label>
                    <div class="cr-imm-doses">
                        <div class="cr-imm-dose"><span>1st Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>2nd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>3rd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>Booster:</span><input type="text" placeholder="dd/mm/yy"></div>
                    </div>
                </div>
                <div class="cr-imm-box">
                    <label class="cr-imm-check"><input type="checkbox"> <strong>Measles</strong></label>
                    <div class="cr-imm-doses">
                        <label class="cr-imm-sub-check"><input type="checkbox"> MMR 1</label>
                        <input type="text" placeholder="dd/mm/yy" class="cr-imm-sub-input">
                        <label class="cr-imm-sub-check"><input type="checkbox"> MMR 2</label>
                        <input type="text" placeholder="dd/mm/yy" class="cr-imm-sub-input">
                        <label class="cr-imm-sub-check"><input type="checkbox"> Chicken Pox 1</label>
                        <label class="cr-imm-sub-check"><input type="checkbox"> Chicken Pox 2</label>
                    </div>
                </div>
                <div class="cr-imm-box">
                    <label class="cr-imm-check"><input type="checkbox"> <strong>OPV / IPV</strong></label>
                    <div class="cr-imm-doses">
                        <div class="cr-imm-dose"><span>1st Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>2nd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>3rd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>Booster 1:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>Booster 2:</span><input type="text" placeholder="dd/mm/yy"></div>
                    </div>
                </div>
                <div class="cr-imm-box">
                    <strong class="cr-imm-section-label">OPTIONAL:</strong>
                    <div class="cr-imm-doses">
                        <label class="cr-imm-sub-check"><input type="checkbox"> Flu</label>
                        <label class="cr-imm-sub-check"><input type="checkbox"> Meningococcal A + C</label>
                        <label class="cr-imm-sub-check"><input type="checkbox"> Pneumococcal</label>
                        <label class="cr-imm-sub-check"><input type="checkbox"> Tuberculin Test</label>
                    </div>
                </div>

                {{-- Row 3 --}}
                <div class="cr-imm-box">
                    <label class="cr-imm-check"><input type="checkbox"> <strong>Typhoid 1</strong></label>
                    <div class="cr-imm-doses">
                        <div class="cr-imm-dose"><span>1st Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>2nd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                        <div class="cr-imm-dose"><span>3rd Dose:</span><input type="text" placeholder="dd/mm/yy"></div>
                    </div>
                </div>
                <div class="cr-imm-box">
                    <label class="cr-imm-check"><input type="checkbox"> <strong>OTHERS, SPECIFY:</strong></label>
                    <div class="cr-imm-doses">
                        <input type="text" placeholder="Others" class="cr-imm-sub-input">
                    </div>
                </div>

                {{-- Doctor Info (inside grid) --}}
                <div class="cr-imm-box cr-doctor-box">
                    <strong class="cr-imm-section-label" style="color:#0a813c; font-size:0.84rem;">DOCTOR'S INFORMATION</strong>
                    <div class="cr-doctor-fields">
                        <div class="cr-doctor-field">
                            <label class="cr-doctor-field-label">Doctor's Name:</label>
                            <input type="text" class="cr-imm-sub-input" placeholder="Name">
                        </div>
                        <div class="cr-doctor-field">
                            <label class="cr-doctor-field-label">Active License:</label>
                            <input type="text" class="cr-imm-sub-input" placeholder="License No.">
                        </div>
                        <div class="cr-doctor-field">
                            <label class="cr-doctor-field-label">Professional Address:</label>
                            <input type="text" class="cr-imm-sub-input" placeholder="Address">
                        </div>
                        <div class="cr-doctor-field">
                            <label class="cr-doctor-field-label">Contact No.:</label>
                            <input type="text" class="cr-imm-sub-input" placeholder="09xxxxxxxxxxx">
                        </div>
                    </div>
                </div>
            </div>

            <div class="cr-card-actions">
                <button type="button" class="eval-btn-outline" onclick="goToStep('medical')">Previous</button>
                <button type="button" class="gs-view-btn" onclick="handleSubmitClinic()">Submit</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════ SUCCESS MODAL ══════ --}}
<div class="pf-modal-overlay" id="crSuccessModal" style="display:none;">
    <div class="pf-modal-box" style="text-align:center; max-width:420px;">
        <div style="margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>
            </svg>
        </div>
        <div class="pf-modal-title" style="color:#006837;">Success</div>
        <p style="font-size:0.92rem; color:#444; margin-bottom:24px;" id="crSuccessMsg">Record submitted successfully!</p>
        <div class="pf-modal-actions" style="justify-content:center;">
            <button type="button" class="pf-modal-btn-save" style="background:#006837; min-width:100px;" onmouseover="this.style.background='#004d29'" onmouseout="this.style.background='#006837'" onclick="closeCrSuccessModal()">OK</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/clinic-record.js') }}"></script>
@endpush
