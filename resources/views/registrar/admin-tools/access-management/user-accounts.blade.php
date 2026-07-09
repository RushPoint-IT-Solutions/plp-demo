@extends('layouts.registrar')

@section('title', 'PLP - User Accounts')
@section('page-title', 'USER ACCOUNTS')
@section('body-class', 'page-user-accounts')

@section('content')
<div class="pf-page">
    <div
        class="ua-page"
        id="uaPageRoot"
        data-data-endpoint="{{ $userAccountDataUrl }}"
        data-store-endpoint="{{ $userAccountStoreUrl }}"
        data-update-template="{{ $userAccountUpdateTemplate }}"
        data-delete-template="{{ $userAccountDeleteTemplate }}"
        data-access-modules-endpoint="{{ $userAccessControlModulesUrl }}"
        data-access-show-template="{{ $userAccessControlShowTemplate }}"
        data-access-update-template="{{ $userAccessControlUpdateTemplate }}"
        data-role-data-endpoint="{{ $accessControlRolesDataUrl }}"
        data-role-store-endpoint="{{ $accessControlRolesStoreUrl }}"
        data-role-update-template="{{ $accessControlRoleUpdateTemplate }}"
        data-role-delete-template="{{ $accessControlRoleDeleteTemplate }}"
        data-role-access-show-template="{{ $accessControlRoleAccessControlShowTemplate }}"
        data-role-access-update-template="{{ $accessControlRoleAccessControlUpdateTemplate }}"
        data-csrf-token="{{ csrf_token() }}"
    >
        <section class="cfg-card ua-filter-card">
            <div class="ua-filter-layout">
                <div class="ua-main-filters" autocomplete="off">
                    <div class="ua-filter-grid">
                        <div class="ua-filter-item">
                            <label class="app-filter-label" for="uaStudentId">User ID</label>
                            <input id="uaStudentId" type="text" class="app-filter-input smrg-search-input" placeholder="User ID" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false">
                        </div>
                        <div class="ua-filter-item">
                            <label class="app-filter-label" for="uaLastName">Last Name</label>
                            <input id="uaLastName" type="text" class="app-filter-input smrg-search-input" placeholder="Last Name" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false">
                        </div>
                        <div class="ua-filter-item">
                            <label class="app-filter-label" for="uaFirstName">First Name</label>
                            <input id="uaFirstName" type="text" class="app-filter-input smrg-search-input" placeholder="First Name" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false">
                        </div>
                        <div class="ua-filter-item ua-user-type">
                            <label class="app-filter-label" for="uaUserType">User Type</label>
                            <select id="uaUserType" class="app-filter-select ua-filter-select" autocomplete="off">
                                <option value="">All</option>
                                <option value="Student">Student</option>
                                <option value="Applicant">Applicant</option>
                                <option value="Parent">Parent</option>
                                <option value="Registrar">Registrar</option>
                                <option value="Faculty">Faculty</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <button type="button" class="ua-clear-btn" id="uaClearBtn">Clear Entries</button>
                        <button type="button" class="pf-btn-new ua-search-btn" id="uaSearchBtn">Search</button>
                        <button type="button" class="pf-btn-new" id="uaNewAccountBtn">Add Account</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="cfg-card">
            <div class="cfg-card-head">
                <h3>User Accounts List</h3>
            </div>
            <div class="app-table-wrap">
                <table class="app-table cfg-table" id="uaTable" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th class="ua-col-index">#</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Status</th>
                            <th class="ua-col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody id="uaTableBody"></tbody>
                </table>
            </div>
            <div class="ua-table-meta">
                <div class="ua-table-caption">Select a row or type a valid User ID to load Account Credentials.</div>
                <div class="app-table-pager"></div>
            </div>
        </section>

        <section class="cfg-card">
            <div class="cfg-card-head">
                <h3>Account Credentials</h3>
                <span class="ua-form-mode-badge" id="uaFormModeBadge">Editing selected account</span>
            </div>

            <div class="ua-selected-user">
                <div class="ua-selected-item">
                    <span class="ua-selected-label">SELECTED USER</span>
                    <span class="ua-selected-value" id="uaSelectedUserName">-</span>
                </div>
                <div class="ua-selected-item">
                    <span class="ua-selected-label">USER ID</span>
                    <span class="ua-selected-value" id="uaSelectedUserId">-</span>
                </div>
                <div class="ua-selected-item">
                    <span class="ua-selected-label">USER TYPE</span>
                    <span class="ua-selected-value" id="uaSelectedUserType">-</span>
                </div>
                <div class="ua-selected-item">
                    <span class="ua-selected-label">EMAIL</span>
                    <span class="ua-selected-value" id="uaSelectedUserEmail">-</span>
                </div>
            </div>

            <input id="uaFormUserType" type="hidden" value="">

            <div class="ua-autofill-trap" aria-hidden="true">
                <input type="text" tabindex="-1" autocomplete="username">
                <input type="password" tabindex="-1" autocomplete="current-password">
            </div>

            <div class="ua-form-grid">
                <label class="ua-form-label" for="uaFormUserId">User ID</label>
                <div class="smrg-search-wrap ua-credential-search-wrap">
                    <input id="uaFormUserId" type="text" class="app-filter-input smrg-search-input" placeholder="Search by User ID" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                    <div class="smrg-search-dropdown ua-credential-dropdown" id="uaFormUserIdDropdown"></div>
                </div>
                <div class="ua-form-note">Type existing User ID and press Enter to auto-fill.</div>

                <label class="ua-form-label" for="uaFormPassword">Password</label>
                <div class="ua-password-wrap">
                    <input id="uaFormPassword" type="password" class="app-filter-input" placeholder="Password" name="ua_new_password_manual" autocomplete="new-password" readonly data-lpignore="true" data-1p-ignore="true">
                    <button type="button" class="ua-pass-toggle" id="uaPasswordToggle" aria-label="Show password" title="Show/Hide Password">
                        <svg class="ua-eye-on" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="ua-eye-off" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"></path><path d="M1 1l22 22"></path><path d="M9.9 4.24A10.93 10.93 0 0 1 12 4c7 0 11 8 11 8a21.72 21.72 0 0 1-3.17 4.66"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path></svg>
                    </button>
                </div>
                <div class="ua-form-note">Note: leave it blank if there is no changes in his/her password.</div>

                <label class="ua-form-label" for="uaFormEmail">Email</label>
                <input id="uaFormEmail" type="email" class="app-filter-input" placeholder="Email" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                <div class="ua-form-note">Email will auto-preview when you select a user account.</div>

                <label class="ua-form-label" for="uaFormName">Full Name (LN, FN MI)</label>
                <div class="smrg-search-wrap ua-credential-search-wrap">
                    <input id="uaFormName" type="text" class="app-filter-input smrg-search-input" placeholder="Search by Full Name" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                    <div class="smrg-search-dropdown ua-credential-dropdown" id="uaFormNameDropdown"></div>
                </div>
                <div class="ua-form-note">You can also type full name to find matching account.</div>

                <label class="setup-checkbox-label ua-inactive-row" for="uaInactive">
                    <input type="checkbox" id="uaInactive" class="req-checkbox-input">
                    Check if Inactive
                </label>
                <div></div>

                <label class="ua-form-label" for="uaFormRole">Role</label>
                <select id="uaFormRole" class="app-filter-select">
                    <option value="">- No role assigned -</option>
                </select>
                <div class="ua-form-note">Determines which modules and actions this account can access. Per-account access overrides (Edit Access) still take priority over the role.</div>
            </div>

            <div class="ua-bottom-actions">
                <button type="button" class="req-btn-cancel" id="uaCancelBtn">Cancel</button>
                <button type="button" class="req-btn-save" id="uaSaveBtn">Save</button>
            </div>
        </section>

        <section class="cfg-card">
            <div class="cfg-card-head">
                <h3>Registrar Staff Roles</h3>
                <button type="button" class="pf-btn-new" id="uaNewRoleBtn">Add Role</button>
            </div>
            <p class="ua-form-note" style="margin: 0 0 10px;">Define reusable roles with their own module/action permissions, then assign a role to each staff account above. Per-account access overrides always take priority over the assigned role.</p>
            <div class="app-table-wrap">
                <table class="app-table cfg-table" id="uaRoleTable" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Accounts</th>
                            <th class="ua-col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody id="uaRoleTableBody"></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div class="req-modal-overlay doclist-modal-hidden ua-delete-modal" id="uaDeleteModal" aria-hidden="true">
    <div class="req-modal-box req-modal-success ua-delete-modal-box" role="dialog" aria-modal="true" aria-labelledby="uaDeleteModalTitle">
        <h3 class="req-modal-title ua-delete-modal-title" id="uaDeleteModalTitle">DELETE ACCOUNT</h3>
        <p id="uaDeleteModalText" class="ua-delete-modal-text">Are you sure you want to delete this account?</p>
        <div class="req-modal-actions ua-delete-modal-actions">
            <button type="button" class="req-btn-cancel" id="uaDeleteCancelBtn">Cancel</button>
            <button type="button" class="req-btn-save ua-delete-modal-confirm" id="uaDeleteConfirmBtn">Delete</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay doclist-modal-hidden ua-role-modal" id="uaRoleModal" aria-hidden="true">
    <div class="req-modal-box" style="width: 480px;" role="dialog" aria-modal="true" aria-labelledby="uaRoleModalTitle">
        <h3 class="req-modal-title" id="uaRoleModalTitle">ADD ROLE</h3>
        <div class="req-modal-field-group">
            <label class="req-modal-label">Role Name</label>
            <input type="text" id="uaRoleFormName" class="req-modal-input" placeholder="e.g. Admissions Clerk">
        </div>
        <div class="req-modal-field-group" style="margin-top:10px;">
            <label class="req-modal-label">Description</label>
            <input type="text" id="uaRoleFormDescription" class="req-modal-input" placeholder="Optional short description">
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" id="uaRoleModalCancelBtn">Cancel</button>
            <button type="button" class="req-btn-save" id="uaRoleModalSaveBtn">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay doclist-modal-hidden ua-role-delete-modal" id="uaRoleDeleteModal" aria-hidden="true">
    <div class="req-modal-box req-modal-success" role="dialog" aria-modal="true" aria-labelledby="uaRoleDeleteModalTitle">
        <h3 class="req-modal-title" id="uaRoleDeleteModalTitle">DELETE ROLE</h3>
        <p id="uaRoleDeleteModalText" class="ua-delete-modal-text">Are you sure you want to delete this role?</p>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" id="uaRoleDeleteCancelBtn">Cancel</button>
            <button type="button" class="req-btn-save" id="uaRoleDeleteConfirmBtn">Delete</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay doclist-modal-hidden ua-access-modal" id="uaAccessModal" aria-hidden="true">
    <div class="req-modal-box ua-access-modal-box" role="dialog" aria-modal="true" aria-labelledby="uaAccessModalTitle">
        <div class="ua-access-modal-head">
            <h3 class="ua-access-modal-title" id="uaAccessModalTitle">Access Control &mdash; <span id="uaAccessModalUserLabel">-</span></h3>
            <button type="button" class="rep-modal-close-x" id="uaAccessCloseX" aria-label="Close access control">&times;</button>
        </div>

        <p class="ua-access-modal-note" id="uaAccessModalNote">These settings override the role defaults for this user only. Toggles are pre-filled with the role&rsquo;s current permissions.</p>

        <div class="ua-access-meta" id="uaAccessCopyRow">
            <div class="ua-access-copy-wrap">
                <label class="app-filter-label" for="uaCopyAccessFrom">Copy Access From:</label>
                <select id="uaCopyAccessFrom" class="app-filter-select">
                    <option value="">- select user -</option>
                </select>
                <button type="button" class="ua-copy-access-btn" id="uaCopyAccessBtn">Copy Settings</button>
            </div>

            <div class="ua-access-quick-wrap">
                <button type="button" class="ua-access-quick-toggle" id="uaQuickAccessToggle" aria-expanded="false" aria-controls="uaAccessQuickList">
                    <span>Quick Access Options</span>
                    <span class="ua-access-quick-caret" aria-hidden="true">
                        <svg class="ua-access-quick-caret-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 6l6 6-6 6"></path>
                        </svg>
                    </span>
                </button>
                <div class="ua-access-quick-grid" id="uaAccessQuickList" hidden></div>
            </div>
        </div>

        <div class="ua-access-table-wrap">
            <table class="ua-access-table" id="uaAccessTable" data-no-auto-pager="1">
                <thead id="uaAccessTableHead"></thead>
                <tbody id="uaAccessTableBody"></tbody>
            </table>
        </div>

        <p class="ua-access-footnote" id="uaAccessFootnote">Access settings are saved for this user and override the role defaults.</p>

        <div class="ua-bottom-actions">
            <button type="button" class="req-btn-cancel" id="uaAccessCancelBtn">Cancel</button>
            <button type="button" class="req-btn-save" id="uaAccessSaveBtn">Save Access</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ mix('js/registrar-user-accounts.js') }}"></script>
@endpush

