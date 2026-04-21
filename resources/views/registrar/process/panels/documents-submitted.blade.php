@php
    $docsStatusOptions = [
        ['value' => '', 'label' => 'All'],
        ['value' => 'pending', 'label' => 'Pending'],
        ['value' => 'completed', 'label' => 'Completed'],
    ];

    $docsPerPageOptions = [
        ['value' => '10', 'label' => '10'],
        ['value' => '25', 'label' => '25'],
        ['value' => '50', 'label' => '50'],
    ];

    $docsRequirementLevelOptions = [
        ['value' => 'All Year Level', 'label' => 'All Year Level'],
        ['value' => '1st Year', 'label' => '1st Year'],
        ['value' => '2nd Year', 'label' => '2nd Year'],
        ['value' => '3rd Year', 'label' => '3rd Year'],
        ['value' => '4th Year', 'label' => '4th Year'],
    ];
@endphp

<div class="apc-card" id="documentsPanel" data-empty-text="No document requirements found.">
    <div class="app-filter-bar apc-panel-filter">
        <div class="app-filter-row">
            <div class="app-filter-group">
                <span class="app-filter-label">Search</span>
                <input type="text" id="docsSearchInput" class="app-filter-input" placeholder="Search document">
            </div>
            <div class="app-filter-group">
                <span class="app-filter-label">Status</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'docsStatusFilter',
                    'name' => 'docsStatusFilter',
                    'options' => $docsStatusOptions,
                    'selected' => '',
                    'placeholder' => 'All'
                ])
            </div>
            <div class="app-filter-group app-filter-select-sm">
                <span class="app-filter-label">Show Entries</span>
                @include('registrar.components.listbox-select', [
                    'id' => 'docsPerPage',
                    'name' => 'docsPerPage',
                    'options' => $docsPerPageOptions,
                    'selected' => '10',
                    'placeholder' => '10'
                ])
            </div>
        </div>
    </div>

    <div id="docsPanelFeedback" class="schedule-exam-feedback"></div>

    <div class="docs-toolbar">
        <button type="button" class="apc-btn apc-btn--save" id="addRequirementBtn" aria-label="Add Document Requirement">Add Requirement</button>
    </div>

    <div class="app-table-wrap table-responsive">
        <table class="app-table docs-table" id="docsTable">
            <thead>
                <tr>
                    <th class="apc-col-check apc-col-check--tor">
                        <input type="checkbox" id="docsSelectAll" aria-label="Select all documents">
                    </th>
                    <th class="docs-col-type">Document Type</th>
                    <th class="docs-col-remarks">Remarks</th>
                    <th class="docs-col-date">Date Submitted</th>
                    <th class="docs-col-file">File Upload</th>
                    <th class="docs-col-status">Status</th>
                    <th class="docs-col-action">Action</th>
                </tr>
            </thead>
            <tbody id="docsTableBody">
                <tr>
                    <td colspan="7" class="sc-empty-row">Select an applicant to load document requirements.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="cfg-pagination" id="docsTablePager"></div>
</div>

<div class="modal fade" id="addRequirementModal" tabindex="-1" role="dialog" aria-labelledby="addRequirementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRequirementModalLabel">Add Document Requirements</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="addRequirementFeedback"></div>

                <div class="docs-requirement-entry">
                    <div class="docs-requirement-grid">
                        <div class="app-filter-group docs-requirement-name-group">
                            <span class="app-filter-label">New Requirement</span>
                            <input type="text" id="newRequirementInput" class="app-filter-input" placeholder="e.g. 2x2 ID Picture">
                        </div>
                        <div class="app-filter-group docs-requirement-level-group">
                            <span class="app-filter-label">Level</span>
                            @include('registrar.components.listbox-select', [
                                'id' => 'newRequirementLevel',
                                'name' => 'newRequirementLevel',
                                'options' => $docsRequirementLevelOptions,
                                'selected' => 'All Year Level',
                                'placeholder' => 'All Year Level'
                            ])
                        </div>
                    </div>
                    <p class="docs-requirement-help">Enter the requirement details, then click Add Requirement.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="apc-btn apc-btn--cancel" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                <button type="button" class="apc-btn apc-btn--save" id="createRequirementBtn">Add Requirement</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="docsConfirmModal" tabindex="-1" role="dialog" aria-labelledby="docsConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="docsConfirmTitle">Confirm Action</h5>
                <button type="button" class="close" id="docsConfirmCloseBtn" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="docsConfirmMessage" class="mb-0">Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="apc-btn apc-btn--cancel" id="docsConfirmCancelBtn" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                <button type="button" class="apc-btn apc-btn--save" id="docsConfirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>
