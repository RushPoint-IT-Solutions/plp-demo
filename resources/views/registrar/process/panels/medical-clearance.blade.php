@php
    $medicalPerPageOptions = [
        ['value' => '10', 'label' => '10'],
        ['value' => '25', 'label' => '25'],
        ['value' => '50', 'label' => '50'],
    ];
@endphp

<div class="apc-card" id="medicalClearancePanel" data-empty-text="No medical clearance requirements found.">
    <div class="app-filter-bar apc-panel-filter">
        <div class="app-filter-row">
            <div class="app-filter-group" style="flex:2;">
                <span class="app-filter-label">Search</span>
                <input type="text" id="medicalSearchInput" class="app-filter-input" placeholder="Search medical requirement">
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">Show Entries</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'medicalPerPage',
                    'name' => 'medicalPerPage',
                    'options' => $medicalPerPageOptions,
                    'selected' => '10',
                    'placeholder' => '10'
                ])
            </div>
        </div>
    </div>

    <div id="medicalPanelFeedback" class="schedule-exam-feedback"></div>

    <div class="app-table-wrap table-responsive">
        <table class="app-table" id="medicalTable">
            <thead>
                <tr>
                    <th class="apc-col-check"></th>
                    <th>Medical Requirement</th>
                    <th>Remarks</th>
                    <th>Date Submitted</th>
                </tr>
            </thead>
            <tbody id="medicalTableBody">
                <tr>
                    <td colspan="4" class="sc-empty-row">Select an applicant to load medical clearance requirements.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="cfg-pagination" id="medicalTablePager"></div>

    <div class="apc-actions">
        <button type="button" class="apc-btn apc-btn--save" id="medicalSaveBtn">Save</button>
    </div>
</div>
