@extends('layouts.registrar')

@section('title', 'PLP - Class List')
@section('page-title', 'CLASS LIST')
@section('body-class', 'page-services-class-list')

@section('content')
<div class="pf-page">
    <div class="sched-filter-bar at-top-row">
        <div class="at-search-block at-search-card">
            <span class="app-filter-label">Search</span>
            <div class="pf-search-wrap at-search-wrap">
                <span class="pf-search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" id="clSearchInput" class="pf-search-input" placeholder="Search Course / Year Level / Section / Subject / Faculty">
            </div>
        </div>

        <div class="at-config-card">
            <div class="at-config-title">System Configuration</div>
            <div class="at-config-grid">
                <div class="at-config-item">
                    <span class="at-config-inline-label">School Year:</span>
                    <select id="clSchoolYear" class="app-filter-select" style="width:100%;">
                        <option value="2025-2026" selected>2025-2026</option>
                        <option value="2024-2025">2024-2025</option>
                        <option value="2023-2024">2023-2024</option>
                    </select>
                </div>
                <div class="at-config-item">
                    <span class="at-config-inline-label">Semester:</span>
                    <select id="clSemester" class="app-filter-select" style="width:100%;">
                        <option value="First" selected>First</option>
                        <option value="Second">Second</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
                <div class="at-config-action">
                    <button type="button" class="pf-btn-new at-btn-set" onclick="saveClassListConfig()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div id="clStateInitial">
        <div class="svc-actions-row">
            <button type="button" class="svc-btn-pdf" onclick="printClassListPdf()">Print Class List (PDF)</button>
            <button type="button" class="svc-btn-excel" onclick="printClassListExcel()">Print Class List (Excel)</button>
        </div>

        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table svc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Section</th>
                        <th>Subject Code</th>
                        <th>Description</th>
                        <th>Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><a href="#" class="svc-link" onclick="showClassListSection('BSIT 2-A'); return false;">BSCS 4-A</a></td>
                        <td>CAP 102</td>
                        <td>Capstone Project and Research 2</td>
                        <td>M | 7:00AM-12:00PM | Room#BLDG. 1 - 401</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><a href="#" class="svc-link" onclick="showClassListSection('BSIT 3-A'); return false;">BSIT 3-A</a></td>
                        <td>CAP 101</td>
                        <td>Capstone Project and Research 1</td>
                        <td>M | 7:00AM-12:00PM | Room#BLDG. 1 - 401</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><a href="#" class="svc-link" onclick="showClassListSection('BSIT 2-A'); return false;">BSIT 2-A</a></td>
                        <td>CC105</td>
                        <td>Information Management</td>
                        <td>TH/F | 11:30AM-2:00PM | Room#BLDG. 1 - 401</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><a href="#" class="svc-link" onclick="showClassListSection('BSIT 1-A'); return false;">BSIT 1-A</a></td>
                        <td>CC103</td>
                        <td>Computer Programming 2</td>
                        <td>W/F | 7:00PM-9:00PM / 7:00AM-10:00AM | Room#ONLINE CLASS/BLDG. 1 - 401</td>
                    </tr>
                    <tr class="svc-total-row">
                        <td colspan="5" class="svc-total-cell">Total Subjects: <strong>4</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="clStateSection" style="display:none;">
        <div class="svc-selected-info">
            <div><strong>Section:</strong> <span id="clInfoSection">BSIT 2-A</span></div>
            <div><strong>Subject:</strong> CC 105 (INFORMATION MANAGEMENT)</div>
            <div><strong>Schedule:</strong> T | 11:30AM-02:00PM BLDG.1 - 401 / TH | 11:30AM-02:00PM BLDG.1 - 401</div>
            <div><strong>Professor:</strong> DELA CRUZ, JUAN</div>
            <div><strong>Pre-Req:</strong> (CC104) DATA STRUCTURES AND ALGORITHMS</div>
        </div>

        <div class="svc-actions-row">
            <button type="button" class="svc-btn-pdf" onclick="printClassListPdf()">Print Class List (PDF)</button>
            <button type="button" class="svc-btn-excel" onclick="printClassListExcel()">Print Class List (Excel)</button>
        </div>

        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table svc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>2223A8137</td>
                        <td>Mark Jay Bares</td>
                        <td>Bachelor Of Science in Computer Science</td>
                        <td>Fourth</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>2223A8139</td>
                        <td>Andrea Austero</td>
                        <td>Bachelor Of Science in Computer Science</td>
                        <td>Fourth</td>
                    </tr>
                    <tr class="svc-total-row">
                        <td colspan="5" class="svc-total-cell">Total Subjects: <strong>4</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function saveClassListConfig() {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('System configuration saved successfully.', 'success');
    }
}

function showClassListSection(sectionName) {
    document.getElementById('clInfoSection').textContent = sectionName;
    document.getElementById('clStateInitial').style.display = 'none';
    document.getElementById('clStateSection').style.display = 'block';
}

function printClassListPdf() {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Preparing Class List PDF...', 'success');
    }
}

function printClassListExcel() {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Preparing Class List Excel...', 'success');
    }
}
</script>
@endpush
