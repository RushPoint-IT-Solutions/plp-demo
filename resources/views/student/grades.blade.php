@extends('layouts.student')

@section('title', 'PLP - Grades')
@section('page-title', 'GRADES')

@section('content')
<div class="grades-page">

    {{-- Semester Filter --}}
    <div class="mb-4">
        <label class="form-label-plp">SELECTED SEMESTER</label>
        <select class="form-select form-input-long" id="semesterFilter">
            <option value="">Select Semester</option>
            <option value="1st-2024">SY 2024-2025 First Semester</option>
            <option value="2nd-2024">SY 2024-2025 Second Semester</option>
            <option value="1st-2025">SY 2025-2026 First Semester</option>
            <option value="2nd-2025" selected>SY 2025-2026 Second Semester</option>
        </select>
    </div>

    {{-- Grades Table --}}
    <div class="grades-scroll">
        <table class="sched-table">
            <thead>
                <tr>
                    <th class="sched-th">Code</th>
                    <th class="sched-th">Course</th>
                    <th class="sched-th">Units</th>
                    <th class="sched-th">Lec Units</th>
                    <th class="sched-th">Lab Units</th>
                    <th class="sched-th">Grades</th>
                    <th class="sched-th">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="sched-td">LAWR19</td>
                    <td class="sched-td">Life and Works of Rizal</td>
                    <td class="sched-td">3.0</td>
                    <td class="sched-td">3.0</td>
                    <td class="sched-td">0.0</td>
                    <td class="sched-td">1.5</td>
                    <td class="sched-td remark-passed">Passed</td>
                </tr>
                <tr>
                    <td class="sched-td">SAM125</td>
                    <td class="sched-td">System Administration and Maintenance</td>
                    <td class="sched-td">3.0</td>
                    <td class="sched-td">2.0</td>
                    <td class="sched-td">1.0</td>
                    <td class="sched-td">INC</td>
                    <td class="sched-td remark-incomplete">Incomplete</td>
                </tr>
                <tr>
                    <td class="sched-td">CP126</td>
                    <td class="sched-td">Capstone Project</td>
                    <td class="sched-td">3.0</td>
                    <td class="sched-td">1.0</td>
                    <td class="sched-td">2.0</td>
                    <td class="sched-td">1.8</td>
                    <td class="sched-td remark-passed">Passed</td>
                </tr>
                <tr>
                    <td class="sched-td">SPI128</td>
                    <td class="sched-td">Social and Professional Issues</td>
                    <td class="sched-td">3.0</td>
                    <td class="sched-td">3.0</td>
                    <td class="sched-td">0.0</td>
                    <td class="sched-td">0.0</td>
                    <td class="sched-td remark-nyp">NYP</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
@endsection
