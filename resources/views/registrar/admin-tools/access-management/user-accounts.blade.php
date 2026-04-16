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
        data-update-template="{{ $userAccountUpdateTemplate }}"
        data-delete-template="{{ $userAccountDeleteTemplate }}"
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
                                <option value="Registrar">Registrar</option>
                                <option value="Faculty">Faculty</option>
                            </select>
                        </div>
                        <button type="button" class="ua-clear-btn" id="uaClearBtn">Clear Entries</button>
                        <button type="button" class="pf-btn-new ua-search-btn" id="uaSearchBtn">Search</button>
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
            </div>

            <div class="ua-selected-user">
                <div class="ua-selected-item">
                    <span class="ua-selected-label">Selected User</span>
                    <span class="ua-selected-value" id="uaSelectedUserName">-</span>
                </div>
                <div class="ua-selected-item">
                    <span class="ua-selected-label">User ID</span>
                    <span class="ua-selected-value" id="uaSelectedUserId">-</span>
                </div>
                <div class="ua-selected-item">
                    <span class="ua-selected-label">User Type</span>
                    <span class="ua-selected-value" id="uaSelectedUserType">-</span>
                </div>
                <div class="ua-selected-item">
                    <span class="ua-selected-label">Email</span>
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
                <div class="ua-form-note">Click to browse matching users, or type to filter best matches.</div>

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
                <div class="ua-form-note">Click to browse matching users, or type to filter best matches.</div>

                <label class="setup-checkbox-label ua-inactive-row" for="uaInactive">
                    <input type="checkbox" id="uaInactive" class="req-checkbox-input">
                    Check if Inactive
                </label>
                <div></div>
                <div></div>
            </div>

            <div class="ua-bottom-actions">
                <button type="button" class="req-btn-cancel" id="uaCancelBtn">Cancel</button>
                <button type="button" class="req-btn-save" id="uaSaveBtn">Save</button>
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
@endsection

@push('scripts')
<script src="{{ mix('js/registrar-user-accounts.js') }}"></script>
@endpush

