@php
    $examSortOptions = [
        ['value' => 'dimensions', 'label' => 'Dimensions'],
        ['value' => 'grade', 'label' => 'Grade'],
    ];

    $examPerPageOptions = [
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
                <input type="text" class="app-filter-input" placeholder="Search dimension">
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">Sort By</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'examResultSortBy',
                    'name' => 'examResultSortBy',
                    'options' => $examSortOptions,
                    'selected' => 'dimensions',
                    'placeholder' => 'Dimensions'
                ])
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">Show Entries</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'examResultShowEntries',
                    'name' => 'examResultShowEntries',
                    'options' => $examPerPageOptions,
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
                    <th>Dimensions</th>
                    <th>Grade</th>
                    <th>Quality Index</th>
                    <th>User</th>
                    <th>Time Created</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1. Aptitude Test</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="apc-dimension-subrow">
                    <td>1.1 English</td>
                    <td><input type="text" class="apc-input apc-input--grade" /></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="apc-dimension-subrow">
                    <td>1.2 Mathematics</td>
                    <td><input type="text" class="apc-input apc-input--grade" /></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="apc-dimension-subrow">
                    <td>1.3 Science</td>
                    <td><input type="text" class="apc-input apc-input--grade" /></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="apc-actions apc-actions--between">
        <button type="button" class="apc-btn apc-btn--ghost">Cancel</button>
        <button type="button" class="apc-btn apc-btn--save">Save</button>
    </div>
</div>
