@extends('layouts.registrar')

@section('title', 'PLP - Attendance')
@section('page-title', 'ATTENDANCE')
@section('body-class', 'page-services-attendance')

@section('content')
<div class="pf-page">
    <div class="svc-filter-panel">
        <div class="svc-filter-grid">
            <div class="svc-filter-item">
                <span class="app-filter-label">School Year</span>
                <select id="attSchoolYear" class="app-filter-select" style="width:100%;">
                    <option value="2025-2026" selected>2025-2026</option>
                    <option value="2024-2025">2024-2025</option>
                    <option value="2023-2024">2023-2024</option>
                </select>
            </div>

            <div class="svc-filter-item">
                <span class="app-filter-label">Semester</span>
                <select id="attSemester" class="app-filter-select" style="width:100%;">
                    <option value="First" selected>First</option>
                    <option value="Second">Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>

            <div class="svc-filter-item svc-filter-professor">
                <span class="app-filter-label">Professor</span>
                <select id="attProfessor" class="app-filter-select" style="width:100%;">
                    <option value="">Select professor...</option>
                    <option value="Dela Cruz, Juan">Dela Cruz, Juan</option>
                    <option value="Santos, Maria">Santos, Maria</option>
                </select>
            </div>

            <div class="svc-filter-item">
                <span class="app-filter-label">Year Level</span>
                <select id="attYearLevel" class="app-filter-select" style="width:100%;">
                    <option value="">Select year...</option>
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                    <option value="Fourth" selected>Fourth</option>
                </select>
            </div>

            <div class="svc-filter-item">
                <span class="app-filter-label">Section</span>
                <select id="attSection" class="app-filter-select" style="width:100%;">
                    <option value="">Select section...</option>
                    <option value="A" selected>A</option>
                    <option value="B">B</option>
                </select>
            </div>

            <div class="svc-filter-action">
                <button type="button" class="pf-btn-new" onclick="runAttendanceSearch()">Search</button>
            </div>
        </div>
    </div>

    <div id="attResult">
        <div class="att-summary-row">
            <div><strong>Section:</strong> A</div>
            <div><strong>Adviser:</strong> Dela Cruz, Juan</div>
        </div>

        <div class="student-table-wrapper table-responsive">
            <table class="att-table">
                <colgroup>
                    <col class="att-col-index">
                    <col class="att-col-name">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-summary">
                </colgroup>
                <thead>
                    <tr class="att-thead-top">
                        <th rowspan="2">#</th>
                        <th rowspan="2" class="att-name-head">Name</th>
                        <th colspan="2">Jan</th>
                        <th colspan="2">Feb</th>
                        <th colspan="2">Mar</th>
                        <th colspan="2">Nov</th>
                        <th colspan="2">Dec</th>
                        <th rowspan="2">Total No. Of Days</th>
                    </tr>
                    <tr class="att-thead-sub">
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Bares, Mark Jay</td>
                        <td>0</td><td>0</td>
                        <td>0</td><td>0</td>
                        <td>0</td><td>0</td>
                        <td>0</td><td>0</td>
                        <td>0</td><td>0</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Austero, Andrea Jone</td>
                        <td>0</td><td>0</td>
                        <td>0</td><td>0</td>
                        <td>0</td><td>0</td>
                        <td>0</td><td>0</td>
                        <td>0</td><td>0</td>
                        <td>0</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function runAttendanceSearch() {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Attendance loaded successfully.', 'success');
    }
}
</script>
@endpush
