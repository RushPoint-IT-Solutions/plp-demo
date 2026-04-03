<div class="apc-card">
    <div class="app-filter-bar apc-panel-filter">
        <div class="app-filter-row">
            <div class="app-filter-group" style="flex:2;">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-input" placeholder="Search dimension" style="width:100%;">
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">Sort By</span>
                <select class="app-filter-select" style="width:100%;">
                    <option>Dimensions</option>
                    <option>Grade</option>
                </select>
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">Show Entries</span>
                <select class="app-filter-select" style="width:100%;">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
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
