@extends('layouts.student')

@section('title', 'PLP - Grades')
@section('page-title', 'GRADES')

@section('content')
<div class="student-page-container">
    {{-- Semester Filter --}}
    <div class="student-filter-section">
        <label class="filter-label">SELECTED SEMESTER</label>
        <select class="filter-select" id="semesterFilter">
            <option value="">Select Semester</option>
            <option value="1st-2024">SY 2024-2025 First Semester</option>
            <option value="2nd-2024">SY 2024-2025 Second Semester</option>
            <option value="1st-2025">SY 2025-2026 First Semester</option>
            <option value="2nd-2025" selected>SY 2025-2026 Second Semester</option>
        </select>
    </div>

    {{-- Grades Table --}}
    <div class="student-table-wrapper">
        <table class="student-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Course</th>
                    <th>Units</th>
                    <th>Lec Units</th>
                    <th>Lab Units</th>
                    <th>Grades</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>LAWR19</td>
                    <td>Life and Works of Rizal</td>
                    <td>3.0</td>
                    <td>3.0</td>
                    <td>0.0</td>
                    <td>1.5</td>
                    <td class="remark-passed">Passed</td>
                </tr>
                <tr>
                    <td>SAM125</td>
                    <td>System Administration and Maintenance</td>
                    <td>3.0</td>
                    <td>2.0</td>
                    <td>1.0</td>
                    <td>INC</td>
                    <td class="remark-incomplete">Incomplete</td>
                </tr>
                <tr>
                    <td>CP126</td>
                    <td>Capstone Project</td>
                    <td>3.0</td>
                    <td>1.0</td>
                    <td>2.0</td>
                    <td>1.8</td>
                    <td class="remark-passed">Passed</td>
                </tr>
                <tr>
                    <td>SPI128</td>
                    <td>Social and Professional Issues</td>
                    <td>3.0</td>
                    <td>3.0</td>
                    <td>0.0</td>
                    <td>0.0</td>
                    <td class="remark-nyp">NYP</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
