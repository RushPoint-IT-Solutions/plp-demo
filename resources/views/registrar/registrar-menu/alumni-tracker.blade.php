@extends('layouts.registrar')

@section('title', 'PLP - Alumni Tracker')
@section('page-title', 'ALUMNI TRACKER')
@section('body-class', 'page-alumni-tracker')

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
                <input type="text" class="pf-search-input" placeholder="Search Student ID / Name">
            </div>
        </div>

        <div class="at-config-card">
            <div class="at-config-title">System Configuration</div>
            <div class="at-config-grid">
                <div class="at-config-item">
                    <span class="at-config-inline-label">School Year:</span>
                    <select class="app-filter-select" style="width:100%;">
                        <option value="2025-2026">2025-2026</option>
                        <option value="2024-2025">2024-2025</option>
                        <option value="2023-2024">2023-2024</option>
                    </select>
                </div>
                <div class="at-config-item">
                    <span class="at-config-inline-label">Term:</span>
                    <select class="app-filter-select" style="width:100%;">
                        <option value="First">First</option>
                        <option value="Second" selected>Second</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
                <div class="at-config-action">
                    <button type="button" class="pf-btn-new at-btn-set" onclick="saveAlumniConfig()">Set</button>
                </div>
            </div>
        </div>
    </div>

    <div class="sched-filter-bar">
        <div class="sched-filter-row at-filter-row">
            <div class="sched-filter-group at-filter-group at-program-group">
                <span class="app-filter-label">Program</span>
                <select class="app-filter-select" style="width:100%;">
                    <option value="">Select Course</option>
                    <option>BSIT</option>
                    <option>BSCS</option>
                    <option>BSED</option>
                    <option>BSBA</option>
                    <option>BSN</option>
                </select>
            </div>

            <div class="sched-filter-group at-filter-group">
                <span class="app-filter-label">Year Level</span>
                <select class="app-filter-select" style="width:100%;">
                    <option value="">Select Year Level</option>
                    <option>First</option>
                    <option>Second</option>
                    <option>Third</option>
                    <option>Fourth</option>
                </select>
            </div>

            <div class="sched-filter-group at-filter-group at-sort-group">
                <span class="app-filter-label">Sort By</span>
                <div class="at-sort-row">
                    <select class="app-filter-select" style="width:100%;">
                        <option>Student ID</option>
                        <option>Student Name</option>
                        <option>Program</option>
                        <option>Year Level</option>
                    </select>
                    <select class="app-filter-select" style="width:100%;">
                        <option>Ascending</option>
                        <option>Descending</option>
                    </select>
                </div>
            </div>

            <div class="at-filter-action">
                <button type="button" class="pf-btn-new">Generate Report</button>
            </div>
        </div>
    </div>

    <div class="student-table-wrapper table-responsive at-table-wrap">
        <table class="student-table registrar-table at-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Program</th>
                    <th>Year Level</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>2223A8137</td>
                    <td><a href="#">Bares, Mark Jay</a></td>
                    <td>Bachelor of Science in Computer Science</td>
                    <td>Fourth</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>2223A8139</td>
                    <td><a href="#">Dela Cruz, Juan</a></td>
                    <td>Bachelor of Science in Computer Science</td>
                    <td>Fourth</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>2223A8139</td>
                    <td><a href="#">Austero, Andrea Jane</a></td>
                    <td>Bachelor of Science in Computer Science</td>
                    <td>Fourth</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>2223A8140</td>
                    <td><a href="#">Santos, Maria</a></td>
                    <td>Bachelor of Science in Computer Science</td>
                    <td>Fourth</td>
                </tr>
                <tr class="at-total-row">
                    <td colspan="5" class="at-total-cell">
                        <span>Total Students:</span>
                        <strong>4</strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function saveAlumniConfig() {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('System configuration saved successfully.', 'success');
    }
}
</script>
@endpush
