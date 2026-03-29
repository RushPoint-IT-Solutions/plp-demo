@extends('layouts.registrar')

@section('title', 'PLP - User Accounts')
@section('page-title', 'USER ACCOUNTS')
@section('body-class', 'page-user-accounts')



@section('content')
<div class="pf-page">
    <div class="ua-page">
        <section class="cfg-card ua-filter-card">
            <div class="ua-filter-layout">
                <div class="ua-main-filters">
                    <div class="ua-filter-grid">
                        <div class="ua-filter-item">
                            <label class="app-filter-label" for="uaStudentId">User ID</label>
                            <input id="uaStudentId" type="text" class="app-filter-input" placeholder="Student ID">
                        </div>
                        <div class="ua-filter-item">
                            <label class="app-filter-label" for="uaLastName">Last Name</label>
                            <input id="uaLastName" type="text" class="app-filter-input" placeholder="Last Name">
                        </div>
                        <div class="ua-filter-item">
                            <label class="app-filter-label" for="uaFirstName">First Name</label>
                            <input id="uaFirstName" type="text" class="app-filter-input" placeholder="First Name">
                        </div>
                        <div class="ua-filter-item ua-user-type">
                            <label class="app-filter-label" for="uaUserType">User Type</label>
                            <select id="uaUserType" class="app-filter-select">
                                <option value="">All</option>
                                <option value="Student">Student</option>
                                <option value="Applicant">Applicant</option>
                                <option value="Registrar">Registrar</option>
                                <option value="Accounting">Accounting</option>
                                <option value="Cashier">Cashier</option>
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
                            <th style="width:60px;">#</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>User Type</th>
                            <th>Status</th>
                            <th style="width:90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="uaTableBody"></tbody>
                </table>
            </div>
            <div class="cfg-pagination ua-table-meta">
                <div class="ua-table-caption">Select a row or type a valid User ID to load Account Credentials.</div>
                <div class="ua-table-page" aria-label="Pagination">
                    <div class="ua-page-list">
                        <button type="button" class="ua-page-btn" aria-label="Previous page" disabled>&lsaquo;</button>
                        <button type="button" class="ua-page-num active" aria-current="page">1</button>
                        <button type="button" class="ua-page-btn" aria-label="Next page" disabled>&rsaquo;</button>
                    </div>
                </div>
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
            </div>

            <div class="ua-form-grid">
                <label class="ua-form-label" for="uaFormUserId">User ID</label>
                <input id="uaFormUserId" type="text" class="app-filter-input" placeholder="User ID">
                <div class="ua-form-note">Type existing User ID and press Enter to auto-fill.</div>

                <label class="ua-form-label" for="uaFormPassword">Password</label>
                <div class="ua-password-wrap">
                    <input id="uaFormPassword" type="password" class="app-filter-input" placeholder="Password">
                    <button type="button" class="ua-pass-toggle" id="uaPasswordToggle" aria-label="Show password" title="Show/Hide Password">
                        <svg class="ua-eye-on" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="ua-eye-off" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"></path><path d="M1 1l22 22"></path><path d="M9.9 4.24A10.93 10.93 0 0 1 12 4c7 0 11 8 11 8a21.72 21.72 0 0 1-3.17 4.66"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path></svg>
                    </button>
                </div>
                <div class="ua-form-note">Note: leave it blank if there is no changes in his/her password.</div>

                <label class="ua-form-label" for="uaFormName">Full Name (LN, FN MI)</label>
                <input id="uaFormName" type="text" class="app-filter-input" placeholder="Full Name">
                <div class="ua-form-note">You can also type full name to find matching account.</div>

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

<div class="req-modal-overlay" id="uaDeleteModal" style="display:none;" onclick="if(event.target===this) uaCloseDeleteModal()">
    <div class="req-modal-box req-modal-success" style="min-width:320px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE ACCOUNT</h3>
        <p id="uaDeleteModalText" style="font-size:0.88rem; color:#444; margin-bottom:20px; text-align:center;">Are you sure you want to delete this account?</p>
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="uaCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="uaConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var uaUsers = @json($accountUsers ?? []);
    var uaUpdateTemplate = '{{ route('registrar.admin-tools.access-management.user-accounts.update', ['user' => '__ID__']) }}';
    var uaDeleteTemplate = '{{ route('registrar.admin-tools.access-management.user-accounts.destroy', ['user' => '__ID__']) }}';

    var uaSelectedUserId = '';
    var uaPendingDeletePk = '';

    function uaUpdateUrl(id) {
        return uaUpdateTemplate.replace('__ID__', String(id));
    }

    function uaDeleteUrl(id) {
        return uaDeleteTemplate.replace('__ID__', String(id));
    }

    async function uaApiRequest(url, method, payload) {
        var response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: payload ? JSON.stringify(payload) : null
        });

        var json = {};
        try {
            json = await response.json();
        } catch (e) {
            json = {};
        }

        if (!response.ok || json.ok === false) {
            throw new Error(
                (json.message) ||
                (json.errors && Object.values(json.errors)[0] && Object.values(json.errors)[0][0]) ||
                'Unable to process account request.'
            );
        }

        return json;
    }

    function uaEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function uaNormalize(value) {
        return String(value || '').trim().toLowerCase();
    }

    function uaBuildDeleteAction(pk) {
        return '' +
            '<button type="button" class="doclist-action-btn doclist-delete-btn" data-ua-delete-user-pk="' + uaEscapeHtml(pk) + '" title="Delete">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>' +
            '</button>';
    }

    function uaBuildStatusBadge(isInactive) {
        if (isInactive) {
            return '<span class="ua-status-badge ua-status-inactive">Inactive</span>';
        }
        return '<span class="ua-status-badge ua-status-active">Active</span>';
    }

    function uaHighlightSelectedRow() {
        var rows = document.querySelectorAll('#uaTableBody tr[data-ua-user-id]');
        rows.forEach(function(row) {
            row.classList.remove('ua-selected-row');
            if (uaSelectedUserId && row.getAttribute('data-ua-user-id') === uaSelectedUserId) {
                row.classList.add('ua-selected-row');
            }
        });
    }

    function uaGetFilters() {
        return {
            studentId: uaNormalize(document.getElementById('uaStudentId').value),
            lastName: uaNormalize(document.getElementById('uaLastName').value),
            firstName: uaNormalize(document.getElementById('uaFirstName').value),
            userType: document.getElementById('uaUserType').value
        };
    }

    function uaGetFilteredUsers() {
        var filters = uaGetFilters();
        return uaUsers
            .map(function(user, index) {
                return { user: user, index: index };
            })
            .filter(function(item) {
                var user = item.user;
                var idMatch = !filters.studentId || uaNormalize(user.userId).indexOf(filters.studentId) !== -1;
                var lastMatch = !filters.lastName || uaNormalize(user.lastName).indexOf(filters.lastName) !== -1;
                var firstMatch = !filters.firstName || uaNormalize(user.firstName).indexOf(filters.firstName) !== -1;
                var typeMatch = !filters.userType || user.userType === filters.userType;
                return idMatch && lastMatch && firstMatch && typeMatch;
            });
    }

    function uaRenderTable() {
        var body = document.getElementById('uaTableBody');
        if (!body) return;

        var rows = uaGetFilteredUsers().map(function(item, rowIndex) {
            var user = item.user;
            var index = item.index;
            var selectedClass = user.userId === uaSelectedUserId ? ' class="ua-selected-row"' : '';
            return '' +
                '<tr data-ua-index="' + index + '" data-ua-user-id="' + uaEscapeHtml(user.userId) + '"' + selectedClass + '>' +
                    '<td>' + (rowIndex + 1) + '</td>' +
                    '<td>' + uaEscapeHtml(user.userId) + '</td>' +
                    '<td>' + uaEscapeHtml(user.fullName) + '</td>' +
                    '<td>' + uaEscapeHtml(user.userType) + '</td>' +
                    '<td>' + uaBuildStatusBadge(user.inactive) + '</td>' +
                    '<td style="text-align:center;">' + uaBuildDeleteAction(user.pk) + '</td>' +
                '</tr>';
        }).join('');

        if (!rows) {
            rows = '<tr><td colspan="6" class="sc-empty-row">No user accounts found.</td></tr>';
        }

        body.innerHTML = rows;
        uaHighlightSelectedRow();
    }

    function uaSetSelectedSummary(user) {
        document.getElementById('uaSelectedUserName').textContent = user ? user.fullName : '-';
        document.getElementById('uaSelectedUserId').textContent = user ? user.userId : '-';
        document.getElementById('uaSelectedUserType').textContent = user ? user.userType : '-';
    }

    function uaFillCredentials(user) {
        if (!user) return;
        uaSelectedUserId = user.userId;
        document.getElementById('uaFormUserId').value = user.userId;
        document.getElementById('uaFormName').value = user.fullName;
        document.getElementById('uaFormPassword').value = '';
        document.getElementById('uaInactive').checked = !!user.inactive;
        uaSetSelectedSummary(user);
        uaHighlightSelectedRow();
    }

    function uaSelectUserByIndex(index) {
        var user = uaUsers[index];
        if (!user) return;
        uaFillCredentials(user);
    }

    function uaFindByLookup() {
        var typedId = uaNormalize(document.getElementById('uaFormUserId').value);
        var typedName = uaNormalize(document.getElementById('uaFormName').value);
        var match = null;

        if (typedId) {
            match = uaUsers.find(function(user) { return uaNormalize(user.userId) === typedId; }) || null;
        }

        if (!match && typedName) {
            match = uaUsers.find(function(user) {
                return uaNormalize(user.fullName) === typedName ||
                    uaNormalize(user.lastName + ', ' + user.firstName) === typedName;
            }) || null;
        }

        if (match) {
            uaFillCredentials(match);
            return true;
        }

        return false;
    }

    function uaOpenDeleteModal(userPk) {
        var user = uaUsers.find(function(item) { return String(item.pk) === String(userPk); });
        if (!user) return;
        uaPendingDeletePk = user.pk;
        document.getElementById('uaDeleteModalText').textContent = 'Are you sure you want to delete account for ' + user.fullName + '?';
        document.getElementById('uaDeleteModal').style.display = 'flex';
    }

    function uaCloseDeleteModal() {
        uaPendingDeletePk = '';
        document.getElementById('uaDeleteModal').style.display = 'none';
    }

    async function uaConfirmDelete() {
        if (!uaPendingDeletePk) {
            uaCloseDeleteModal();
            return;
        }

        var index = uaUsers.findIndex(function(item) { return String(item.pk) === String(uaPendingDeletePk); });
        if (index === -1) {
            uaCloseDeleteModal();
            return;
        }

        var user = uaUsers[index];
        try {
            await uaApiRequest(uaDeleteUrl(user.pk), 'DELETE');
        } catch (error) {
            alert(error.message || 'Unable to delete account.');
            return;
        }

        uaUsers.splice(index, 1);
        if (uaSelectedUserId === user.userId) {
            uaSelectedUserId = '';
            uaSetSelectedSummary(null);
            document.getElementById('uaFormUserId').value = '';
            document.getElementById('uaFormName').value = '';
            document.getElementById('uaFormPassword').value = '';
            document.getElementById('uaInactive').checked = false;
        }

        document.getElementById('uaStudentId').value = '';
        document.getElementById('uaLastName').value = '';
        document.getElementById('uaFirstName').value = '';
        document.getElementById('uaUserType').value = '';

        uaCloseDeleteModal();
        uaRenderTable();
    }

    function uaResetForm() {
        if (uaSelectedUserId) {
            var user = uaUsers.find(function(item) { return item.userId === uaSelectedUserId; });
            if (user) {
                uaFillCredentials(user);
                return;
            }
        }

        document.getElementById('uaFormUserId').value = '';
        document.getElementById('uaFormName').value = '';
        document.getElementById('uaFormPassword').value = '';
        document.getElementById('uaInactive').checked = false;
        uaSetSelectedSummary(null);
        uaRenderTable();
    }

    function uaClearFilters() {
        document.getElementById('uaStudentId').value = '';
        document.getElementById('uaLastName').value = '';
        document.getElementById('uaFirstName').value = '';
        document.getElementById('uaUserType').value = '';
        uaRenderTable();
    }

    document.getElementById('uaSearchBtn').addEventListener('click', function() {
        uaRenderTable();
        var typedId = uaNormalize(document.getElementById('uaStudentId').value);
        if (typedId) {
            var match = uaUsers.find(function(user) { return uaNormalize(user.userId) === typedId; });
            if (match) uaFillCredentials(match);
        }
    });

    document.getElementById('uaClearBtn').addEventListener('click', uaClearFilters);

    document.getElementById('uaCancelBtn').addEventListener('click', uaResetForm);

    document.getElementById('uaSaveBtn').addEventListener('click', async function() {
        var typedId = uaNormalize(document.getElementById('uaFormUserId').value);
        if (!typedId) {
            alert('Please enter a User ID first.');
            return;
        }

        var user = uaUsers.find(function(item) { return uaNormalize(item.userId) === typedId; });
        if (!user) {
            alert('User ID not found in current records.');
            return;
        }

        var payload = {
            user_id: (document.getElementById('uaFormUserId').value || '').trim(),
            full_name: (document.getElementById('uaFormName').value || '').trim(),
            password: (document.getElementById('uaFormPassword').value || '').trim(),
            inactive: document.getElementById('uaInactive').checked
        };

        try {
            var response = await uaApiRequest(uaUpdateUrl(user.pk), 'PUT', payload);
            var updated = response.row || user;
            Object.assign(user, updated);
            uaFillCredentials(user);
            alert('Account credentials updated.');
        } catch (error) {
            alert(error.message || 'Unable to update account.');
        }
    });

    document.getElementById('uaFormUserId').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            uaFindByLookup();
        }
    });

    document.getElementById('uaFormName').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            uaFindByLookup();
        }
    });

    document.getElementById('uaFormUserId').addEventListener('blur', uaFindByLookup);
    document.getElementById('uaFormName').addEventListener('blur', uaFindByLookup);

    document.getElementById('uaPasswordToggle').addEventListener('click', function() {
        var passInput = document.getElementById('uaFormPassword');
        var isHidden = passInput.type === 'password';
        passInput.type = isHidden ? 'text' : 'password';
        this.classList.toggle('is-visible', isHidden);
        this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    });

    document.addEventListener('click', function(event) {
        var deleteBtn = event.target.closest('[data-ua-delete-user-pk]');
        if (deleteBtn) {
            event.stopPropagation();
            uaOpenDeleteModal(deleteBtn.getAttribute('data-ua-delete-user-pk'));
            return;
        }

        var row = event.target.closest('#uaTableBody tr[data-ua-index]');
        if (row) {
            uaSelectUserByIndex(parseInt(row.getAttribute('data-ua-index'), 10));
            return;
        }
    });

    uaSetSelectedSummary(null);
    uaRenderTable();
</script>
@endpush

