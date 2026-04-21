@php
    $medicalPerPageOptions = [
        ['value' => '10', 'label' => '10'],
        ['value' => '25', 'label' => '25'],
        ['value' => '50', 'label' => '50'],
    ];
@endphp

<div class="apc-card">
    <div class="app-filter-bar apc-panel-filter">
        <div class="app-filter-row">
            <div class="app-filter-group" style="flex:2;">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-input" placeholder="Search medical document">
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">Show Entries</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'medicalShowEntries',
                    'name' => 'medicalShowEntries',
                    'options' => $medicalPerPageOptions,
                    'selected' => '10',
                    'placeholder' => '10'
                ])
            </div>
        </div>
    </div>

    <div class="app-table-wrap table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th class="apc-col-check"></th>
                    <th>Documents</th>
                    <th>Remarks</th>
                    <th>Date Submitted</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="app-row-checkbox"></td>
                    <td>Dental</td>
                    <td><input type="text" class="apc-input" placeholder="Type..." /></td>
                    <td><input type="date" class="apc-input apc-input--date" /></td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="app-row-checkbox"></td>
                    <td>Medical</td>
                    <td><input type="text" class="apc-input" placeholder="Type..." /></td>
                    <td><input type="date" class="apc-input apc-input--date" /></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="apc-actions">
        <button type="button" class="apc-btn apc-btn--save">Save</button>
    </div>
</div>
