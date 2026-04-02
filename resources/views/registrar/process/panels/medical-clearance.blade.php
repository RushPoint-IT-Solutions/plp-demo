<div class="apc-card">
    <div class="app-filter-bar apc-panel-filter">
        <div class="app-filter-row">
            <div class="app-filter-group" style="flex:2;">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-input" placeholder="Search medical document" style="width:100%;">
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
