@extends('layouts.registrar')

@section('title', 'PLP - Tagging of Graduates')
@section('page-title', 'TAGGING OF GRADUATES')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <!-- Filters -->
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
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:15px;">
                <button type="button" class="req-btn-save" style="min-width: 120px; font-weight: 700;">Set</button>
            </div>
        </div>

        <!-- Table controls -->
        <div class="ga-table-controls" style="margin-bottom: 12px; display:flex; font-size: 0.85rem; font-weight: 600; color: #006837; align-items: center; gap: 8px;">
            <span style="letter-spacing: 0.05em;">SHOW</span>
            <select class="app-filter-select" style="width: auto; padding: 4px 28px 4px 12px; height: 32px; font-size: 0.85rem;">
                <option>10</option>
                <option>25</option>
                <option>50</option>
            </select>
            <span style="letter-spacing: 0.05em;">ENTRIES</span>
        </div>

        <!-- Data Table -->
        <div class="ga-table-wrap app-table-wrap">
            <table class="ga-table app-table" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th style="text-align: center;">Tag As Graduate</th>
                        <th style="text-align: center;">Date Graduated</th>
                        <th style="text-align: center;">SO Num</th>
                        <th style="text-align: center;">SO Date</th>
                        <th style="text-align: center;">Suspend Account</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td>2223A8137</td>
                        <td>Mark Jay Bares</td>
                        <td>BSCS</td>
                        <td>Fourth</td>
                        <td style="text-align: center;"><span class="ga-status-text ga-status-active">Active</span></td>
                        <td style="text-align: center; color: #333; font-size: 0.85rem;">2026-06-25</td>
                        <td style="text-align: center; color: #333; font-size: 0.85rem;">SO-2026-00123</td>
                        <td style="text-align: center; color: #333; font-size: 0.85rem;">2026-07-01</td>
                        <td style="text-align: center;"><span class="ga-status-text ga-status-suspend">Suspended</span></td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">2</td>
                        <td>2223A8139</td>
                        <td>Andrea Jane Austero</td>
                        <td>BSCS</td>
                        <td>Fourth</td>
                        <td style="text-align: center;"><span class="ga-status-text ga-status-graduated">Graduated</span></td>
                        <td style="text-align: center; color: #333; font-size: 0.85rem;">2026-05-30</td>
                        <td style="text-align: center; color: #333; font-size: 0.85rem;">SO-2026-00045</td>
                        <td style="text-align: center; color: #333; font-size: 0.85rem;">2026-06-15</td>
                        <td style="text-align: center;"><span class="ga-status-text ga-status-suspended">Suspended</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
