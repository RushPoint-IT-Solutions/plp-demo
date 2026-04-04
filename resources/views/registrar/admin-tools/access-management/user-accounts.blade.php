@extends('layouts.registrar')

@section('title', 'PLP - User Accounts')
@section('page-title', 'USER ACCOUNTS')
@section('body-class', 'page-user-accounts')



@section('content')
<div class="pf-page">
    <div class="ua-page">
        <section class="cfg-card ua-filter-card">
            <div class="ua-filter-layout">
                <div class="ua-main-filters" autocomplete="off">
                    <div class="ua-filter-grid">
                        <div class="ua-filter-item">
                            <label class="app-filter-label" for="uaStudentId">User ID</label>
                            <input id="uaStudentId" type="text" class="app-filter-input" placeholder="Student ID" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false">
                        </div>
                        <div class="ua-filter-item">
                            <label class="app-filter-label" for="uaLastName">Last Name</label>
                            <input id="uaLastName" type="text" class="app-filter-input" placeholder="Last Name" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false">
                        </div>
                        <div class="ua-filter-item">
                            <label class="app-filter-label" for="uaFirstName">First Name</label>
                            <input id="uaFirstName" type="text" class="app-filter-input" placeholder="First Name" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false">
                        </div>
                        <div class="ua-filter-item ua-user-type">
                            <label class="app-filter-label" for="uaUserType">User Type</label>
                            <select id="uaUserType" class="app-filter-select" autocomplete="off">
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

            <div class="ua-autofill-trap" aria-hidden="true">
                <input type="text" tabindex="-1" autocomplete="username">
                <input type="password" tabindex="-1" autocomplete="current-password">
            </div>

            <div class="ua-form-grid">
                <label class="ua-form-label" for="uaFormUserId">User ID</label>
                <input id="uaFormUserId" type="text" class="app-filter-input" placeholder="User ID" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
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
                <input id="uaFormName" type="text" class="app-filter-input" placeholder="Full Name" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
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

        <section class="cfg-card ua-access-card" id="uaAccessCard" style="display:none;">
            <div class="cfg-card-head">
                <h3>Account Access Permissions</h3>
            </div>
            <div class="ua-access-meta">
                <div class="ua-access-user-note" id="uaAccessUserNote">No user selected. Select a row to preview/edit access permissions.</div>
                <div class="ua-access-copy-wrap">
                    <label class="app-filter-label" for="uaCopyAccessFrom">Copy Access From</label>
                    <select id="uaCopyAccessFrom" class="app-filter-select">
                        <option value="">- select user -</option>
                    </select>
                    <button type="button" class="ua-copy-access-btn" id="uaCopyAccessBtn">Copy</button>
                </div>
            </div>

            <div class="ua-access-quick-wrap">
                <div class="ua-access-quick-title">Quick Access Options</div>
                <div class="ua-access-quick-options" id="uaAccessQuickOptions"></div>
            </div>
            <div class="ua-access-grid" id="uaAccessGrid"></div>
            <div class="ua-access-footnote">UI-only preview for now. Access values are kept in-page until backend mapping is wired.</div>
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
    var uaCurrentPage = 1;
    var uaPageSize = 10;
    var uaFiltersApplied = false;
    var uaAccessStateByUserId = {};
    var uaAccessQuickToggleItems = [
        'Accept Pre-requisite Subjects and Overload Units',
        'Accept Student with balance (AMS Registration)',
        'Can Add Elective Subjects (AMS Registration / Student Enrollment)',
        'Accept Student with balance (Student Enrollment)',
        'Student Enrollment Config',
        'Faculty Loading Config',
        'Can Override Deficiency',
        'Can Accept Conflict Schedule',
        'Can Change Professor (Grading Sheet Module)',
        'Can Dissolved Section',
        'Can approve gradesheet',
        'Can delete/edit subject in student grade file',
        'Can Delete/Edit Email Sender'
    ];
    var uaAccessGroups = [
        {
            key: 'process',
            title: 'PROCESS',
            items: [
                'Application Process', 'Application Form', 'Documents Submitted', 'Schedule of Exam',
                'Medical Clearance', 'Exam Result', 'Approval', 'Applicant Status',
                'Citizenship', 'Religion', 'Exam Category', 'Exam List',
                'Approval Status', 'Document List', 'Batch Image Upload', 'Schools',
                'Admissions Report'
            ]
        },
        {
            key: 'registrar',
            title: 'REGISTRAR',
            items: [
                'Program File', 'Subject File', 'Curriculum File', 'Pre-requisites', 'Room File',
                'Section Offering', 'Pre-Registration', 'Slot Monitoring', 'Student Enrollment',
                'Section Merging', 'Grading Sheet', 'Comments', 'Conducts', 'Evaluation',
                'Clinic Records', 'Letter Grade Setup', 'STO Tracker', 'Alumni Tracker'
            ]
        },
        {
            key: 'services',
            title: 'SERVICES',
            items: [
                'Messaging', 'Class List', 'Grading System', 'Grading Periods', 'Grading Components',
                'Academic Reports', 'Faculty Loads', 'Change Password', 'Deficiency', 'Attendance',
                'Student Discipline', 'Family', 'Transmutation', 'Certifications', 'Guidance Reports',
                'Deans Reports', 'Senior High School Reports', 'Conduct Grades', 'Tagging of Graduates'
            ]
        },
        {
            key: 'admin_tools',
            title: 'ADMIN TOOLS',
            items: [
                'Configuration', 'Admission Config', 'User Accounts', 'Faculty File', 'Academic Calendar',
                'Student Profile', 'Student Grade File', 'Senior High School Enrollment', 'Report Access',
                'BED Student Status', 'BED Days', 'Announcement', 'Student Update', 'Shipboard Training Enrollment'
            ]
        }
    ];

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
            userType: uaNormalize(document.getElementById('uaUserType').value)
        };
    }

    function uaGetFilteredUsers() {
        var filters = uaGetFilters();
        var hasActiveFilters = !!(filters.studentId || filters.lastName || filters.firstName || filters.userType);

        if (!hasActiveFilters) {
            return uaUsers.map(function(user, index) {
                return { user: user, index: index };
            });
        }

        return uaUsers
            .map(function(user, index) {
                return { user: user, index: index };
            })
            .filter(function(item) {
                var user = item.user;
                var idMatch = !filters.studentId || uaNormalize(user.userId).indexOf(filters.studentId) !== -1;
                var lastMatch = !filters.lastName || uaNormalize(user.lastName).indexOf(filters.lastName) !== -1;
                var firstMatch = !filters.firstName || uaNormalize(user.firstName).indexOf(filters.firstName) !== -1;
                var typeMatch = !filters.userType || uaNormalize(user.userType) === filters.userType;
                return idMatch && lastMatch && firstMatch && typeMatch;
            });
    }

    function uaRenderPager(totalRows) {
        var mount = document.querySelector('.ua-table-meta .app-table-pager');
        if (!mount) return;

        var maxPage = Math.max(1, Math.ceil(totalRows / uaPageSize));
        if (uaCurrentPage > maxPage) {
            uaCurrentPage = maxPage;
        }

        if (totalRows <= uaPageSize) {
            mount.innerHTML = '';
            return;
        }

        var start = Math.max(1, uaCurrentPage - 2);
        var end = Math.min(maxPage, uaCurrentPage + 2);
        if (uaCurrentPage <= 3) {
            end = Math.min(maxPage, 5);
        } else if (uaCurrentPage >= maxPage - 2) {
            start = Math.max(1, maxPage - 4);
        }

        var pageNums = '';
        for (var p = start; p <= end; p += 1) {
            pageNums += '<button type="button" class="rtp-page-num ' + (p === uaCurrentPage ? 'active' : '') + '" data-ua-page="' + p + '">' + p + '</button>';
        }

        mount.innerHTML = '' +
            '<div class="rtp-pagination">' +
                '<nav class="rtp-nav" aria-label="User accounts pagination">' +
                    '<div class="rtp-list" role="group" aria-label="Page controls">' +
                        '<button type="button" class="rtp-page-btn" data-ua-page-prev="1" ' + (uaCurrentPage <= 1 ? 'disabled' : '') + '>&lt;</button>' +
                        pageNums +
                        '<button type="button" class="rtp-page-btn" data-ua-page-next="1" ' + (uaCurrentPage >= maxPage ? 'disabled' : '') + '>&gt;</button>' +
                    '</div>' +
                '</nav>' +
            '</div>';
    }

    function uaRenderTable() {
        var body = document.getElementById('uaTableBody');
        if (!body) return;

        var filtered = uaGetFilteredUsers();
        var maxPage = Math.max(1, Math.ceil(filtered.length / uaPageSize));
        if (uaCurrentPage > maxPage) {
            uaCurrentPage = 1;
        }
        var startIndex = (uaCurrentPage - 1) * uaPageSize;
        var pageItems = filtered.slice(startIndex, startIndex + uaPageSize);

        var rows = pageItems.map(function(item, rowIndex) {
            var user = item.user;
            var index = item.index;
            var selectedClass = user.userId === uaSelectedUserId ? ' class="ua-selected-row"' : '';
            return '' +
                '<tr data-ua-index="' + index + '" data-ua-user-id="' + uaEscapeHtml(user.userId) + '"' + selectedClass + '>' +
                    '<td>' + (startIndex + rowIndex + 1) + '</td>' +
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
        uaRenderPager(filtered.length);
        uaHighlightSelectedRow();
    }

    function uaSetSelectedSummary(user) {
        document.getElementById('uaSelectedUserName').textContent = user ? user.fullName : '-';
        document.getElementById('uaSelectedUserId').textContent = user ? user.userId : '-';
        document.getElementById('uaSelectedUserType').textContent = user ? user.userType : '-';
        document.getElementById('uaSelectedUserEmail').textContent = user && user.email ? user.email : '-';
    }

    function uaFillCredentials(user) {
        if (!user) return;
        uaSelectedUserId = user.userId;
        document.getElementById('uaFormUserId').value = user.userId;
        document.getElementById('uaFormName').value = user.fullName;
        document.getElementById('uaFormEmail').value = user.email || '';
        document.getElementById('uaFormPassword').value = '';
        document.getElementById('uaFormPassword').readOnly = true;
        document.getElementById('uaInactive').checked = !!user.inactive;
        uaSetSelectedSummary(user);
        uaRenderAccessView();
        uaHighlightSelectedRow();
    }

    function uaGetSelectedUser() {
        if (!uaSelectedUserId) return null;
        return uaUsers.find(function(item) {
            return item && item.userId === uaSelectedUserId;
        }) || null;
    }

    function uaCanManageAccess(user) {
        var type = user && user.userType ? String(user.userType).toLowerCase() : '';
        return type === 'registrar';
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
            document.getElementById('uaFormEmail').value = '';
            document.getElementById('uaFormPassword').value = '';
            document.getElementById('uaFormPassword').readOnly = true;
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
        document.getElementById('uaFormEmail').value = '';
        document.getElementById('uaFormPassword').value = '';
        document.getElementById('uaFormPassword').readOnly = true;
        document.getElementById('uaInactive').checked = false;
        uaSelectedUserId = '';
        uaSetSelectedSummary(null);
        uaRenderAccessView();
        uaRenderTable();
    }

    function uaClearFilters() {
        document.getElementById('uaStudentId').value = '';
        document.getElementById('uaLastName').value = '';
        document.getElementById('uaFirstName').value = '';
        document.getElementById('uaUserType').value = '';
        uaCurrentPage = 1;
        uaRenderTable();
    }

    document.getElementById('uaSearchBtn').addEventListener('click', function() {
        uaCurrentPage = 1;
        uaRenderTable();
        var typedId = uaNormalize(document.getElementById('uaStudentId').value);
        if (typedId) {
            var match = uaUsers.find(function(user) { return uaNormalize(user.userId) === typedId; });
            if (match) uaFillCredentials(match);
        }
    });

    function uaApplyFiltersLive() {
        uaCurrentPage = 1;
        uaRenderTable();
    }

    ['uaStudentId', 'uaLastName', 'uaFirstName'].forEach(function(inputId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('input', uaApplyFiltersLive);
    });

    var uaUserTypeFilter = document.getElementById('uaUserType');
    if (uaUserTypeFilter) {
        uaUserTypeFilter.addEventListener('change', uaApplyFiltersLive);
    }

    ['uaStudentId', 'uaLastName', 'uaFirstName'].forEach(function(inputId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                document.getElementById('uaSearchBtn').click();
            }
        });
    });

    function uaForceResetFilters() {
        document.getElementById('uaStudentId').value = '';
        document.getElementById('uaLastName').value = '';
        document.getElementById('uaFirstName').value = '';
        document.getElementById('uaUserType').value = '';
    }

    document.querySelector('.ua-table-meta .app-table-pager').addEventListener('click', function(event) {
        var prev = event.target.closest('[data-ua-page-prev]');
        if (prev && uaCurrentPage > 1) {
            uaCurrentPage -= 1;
            uaRenderTable();
            return;
        }

        var next = event.target.closest('[data-ua-page-next]');
        if (next) {
            var total = uaGetFilteredUsers().length;
            var maxPage = Math.max(1, Math.ceil(total / uaPageSize));
            if (uaCurrentPage < maxPage) {
                uaCurrentPage += 1;
                uaRenderTable();
            }
            return;
        }

        var pageBtn = event.target.closest('[data-ua-page]');
        if (pageBtn) {
            uaCurrentPage = parseInt(pageBtn.getAttribute('data-ua-page'), 10) || 1;
            uaRenderTable();
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
            email: (document.getElementById('uaFormEmail').value || '').trim(),
            password: (document.getElementById('uaFormPassword').value || '').trim(),
            inactive: document.getElementById('uaInactive').checked
        };

        try {
            var response = await uaApiRequest(uaUpdateUrl(user.pk), 'PUT', payload);
            var updated = response.row || user;
            if (!updated.email && payload.email) {
                updated.email = payload.email;
            }
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

    document.getElementById('uaFormPassword').addEventListener('focus', function() {
        this.readOnly = false;
    });

    document.getElementById('uaPasswordToggle').addEventListener('click', function() {
        var passInput = document.getElementById('uaFormPassword');
        passInput.readOnly = false;
        var isHidden = passInput.type === 'password';
        passInput.type = isHidden ? 'text' : 'password';
        this.classList.toggle('is-visible', isHidden);
        this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    });

    function uaCreateBlankAccessState() {
        var state = { quick: {}, groups: {} };

        uaAccessQuickToggleItems.forEach(function(item) {
            state.quick[item] = false;
        });

        uaAccessGroups.forEach(function(group) {
            state.groups[group.key] = {};
            group.items.forEach(function(itemName) {
                state.groups[group.key][itemName] = { r: false, w: false };
            });
        });

        return state;
    }

    function uaCloneAccessState(state) {
        return JSON.parse(JSON.stringify(state));
    }

    function uaGetSelectedAccessState() {
        if (!uaSelectedUserId) return null;
        if (!uaAccessStateByUserId[uaSelectedUserId]) {
            uaAccessStateByUserId[uaSelectedUserId] = uaCreateBlankAccessState();
        }
        return uaAccessStateByUserId[uaSelectedUserId];
    }

    function uaRenderCopyAccessOptions() {
        var select = document.getElementById('uaCopyAccessFrom');
        if (!select) return;

        var options = ['<option value="">- select user -</option>'];
        uaUsers.forEach(function(user) {
            if (!user || !user.userId) return;
            options.push('<option value="' + uaEscapeHtml(user.userId) + '">' + uaEscapeHtml(user.fullName + ' (' + user.userId + ')') + '</option>');
        });
        select.innerHTML = options.join('');
    }

    function uaRenderAccessQuickOptions(state, disabled) {
        var mount = document.getElementById('uaAccessQuickOptions');
        if (!mount) return;

        var html = uaAccessQuickToggleItems.map(function(itemName, index) {
            var checked = state && state.quick[itemName] ? 'checked' : '';
            return '' +
                '<label class="setup-checkbox-label ua-access-check">' +
                    '<input type="checkbox" class="req-checkbox-input" data-ua-quick-index="' + index + '" ' + checked + (disabled ? ' disabled' : '') + '>' +
                    '<span>' + uaEscapeHtml(itemName) + '</span>' +
                '</label>';
        }).join('');

        mount.innerHTML = html;
    }

    function uaRenderAccessGrid(state, disabled) {
        var mount = document.getElementById('uaAccessGrid');
        if (!mount) return;

        var html = uaAccessGroups.map(function(group) {
            var rows = group.items.map(function(itemName) {
                var itemState = state && state.groups[group.key] && state.groups[group.key][itemName] ? state.groups[group.key][itemName] : { r: false, w: false };
                return '' +
                    '<tr>' +
                        '<td>' + uaEscapeHtml(itemName) + '</td>' +
                        '<td class="ua-access-rw-col"><input type="checkbox" class="req-checkbox-input" data-ua-group="' + uaEscapeHtml(group.key) + '" data-ua-item="' + uaEscapeHtml(itemName) + '" data-ua-mode="r" ' + (itemState.r ? 'checked ' : '') + (disabled ? 'disabled' : '') + '></td>' +
                        '<td class="ua-access-rw-col"><input type="checkbox" class="req-checkbox-input" data-ua-group="' + uaEscapeHtml(group.key) + '" data-ua-item="' + uaEscapeHtml(itemName) + '" data-ua-mode="w" ' + (itemState.w ? 'checked ' : '') + (disabled ? 'disabled' : '') + '></td>' +
                    '</tr>';
            }).join('');

            return '' +
                '<div class="ua-access-group">' +
                    '<table class="ua-access-table" data-no-auto-pager="1" aria-label="' + uaEscapeHtml(group.title) + ' access table">' +
                        '<thead>' +
                            '<tr>' +
                                '<th>' + uaEscapeHtml(group.title) + '</th>' +
                                '<th class="ua-access-rw-col"><span class="ua-access-rw-head"><span class="ua-access-rw-text">R</span><input type="checkbox" class="req-checkbox-input ua-access-head-check" data-ua-group-master="' + uaEscapeHtml(group.key) + '" data-ua-mode="r" ' + (disabled ? 'disabled' : '') + '></span></th>' +
                                '<th class="ua-access-rw-col"><span class="ua-access-rw-head"><span class="ua-access-rw-text">W</span><input type="checkbox" class="req-checkbox-input ua-access-head-check" data-ua-group-master="' + uaEscapeHtml(group.key) + '" data-ua-mode="w" ' + (disabled ? 'disabled' : '') + '></span></th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>' + rows + '</tbody>' +
                    '</table>' +
                '</div>';
        }).join('');

        mount.innerHTML = html;
    }

    function uaRenderAccessView() {
        var accessCard = document.getElementById('uaAccessCard');
        var note = document.getElementById('uaAccessUserNote');
        var selectedUser = uaGetSelectedUser();
        var canManageAccess = uaCanManageAccess(selectedUser);

        if (accessCard) {
            accessCard.style.display = canManageAccess ? '' : 'none';
        }

        if (!canManageAccess) {
            return;
        }

        var state = uaGetSelectedAccessState();
        var disabled = !state;

        if (note) {
            note.textContent = disabled
                ? 'No user selected. Select a row to preview/edit access permissions.'
                : 'Editing access for ' + uaSelectedUserId + '.';
        }

        uaRenderAccessQuickOptions(state, disabled);
        uaRenderAccessGrid(state, disabled);

        // Safety cleanup in case shared auto-pager mounts were inserted before no-auto flags applied.
        document.querySelectorAll('.ua-access-card .rtp-pagination, .ua-access-card .pf-pagination, .ua-access-card .app-table-pager').forEach(function(node) {
            if (node && node.parentNode) {
                node.parentNode.removeChild(node);
            }
        });
    }

    function uaHandleAccessInteractions(event) {
        var state = uaGetSelectedAccessState();
        if (!state) return;

        var master = event.target.closest('[data-ua-group-master][data-ua-mode]');
        if (master) {
            var masterGroup = master.getAttribute('data-ua-group-master');
            var masterMode = master.getAttribute('data-ua-mode');
            var masterValue = !!master.checked;

            if (state.groups[masterGroup]) {
                Object.keys(state.groups[masterGroup]).forEach(function(itemName) {
                    if (state.groups[masterGroup][itemName]) {
                        state.groups[masterGroup][itemName][masterMode] = masterValue;
                    }
                });

                document.querySelectorAll('[data-ua-group="' + masterGroup + '"][data-ua-mode="' + masterMode + '"]').forEach(function(box) {
                    box.checked = masterValue;
                });
            }
            return;
        }

        var quick = event.target.closest('[data-ua-quick-index]');
        if (quick) {
            var quickIndex = parseInt(quick.getAttribute('data-ua-quick-index'), 10);
            if (!isNaN(quickIndex) && uaAccessQuickToggleItems[quickIndex]) {
                state.quick[uaAccessQuickToggleItems[quickIndex]] = !!quick.checked;
            }
            return;
        }

        var rw = event.target.closest('[data-ua-group][data-ua-item][data-ua-mode]');
        if (rw) {
            var groupKey = rw.getAttribute('data-ua-group');
            var itemName = rw.getAttribute('data-ua-item');
            var mode = rw.getAttribute('data-ua-mode');
            if (state.groups[groupKey] && state.groups[groupKey][itemName]) {
                state.groups[groupKey][itemName][mode] = !!rw.checked;
            }
        }
    }

    document.getElementById('uaAccessQuickOptions').addEventListener('change', uaHandleAccessInteractions);
    document.getElementById('uaAccessGrid').addEventListener('change', uaHandleAccessInteractions);

    document.getElementById('uaCopyAccessBtn').addEventListener('click', function() {
        var sourceId = document.getElementById('uaCopyAccessFrom').value;
        var targetId = uaSelectedUserId;

        if (!targetId) {
            alert('Please select a target account first.');
            return;
        }
        if (!sourceId) {
            alert('Please select a source account to copy from.');
            return;
        }
        if (sourceId === targetId) {
            alert('Source and target accounts are the same.');
            return;
        }

        var sourceState = uaAccessStateByUserId[sourceId] ? uaCloneAccessState(uaAccessStateByUserId[sourceId]) : uaCreateBlankAccessState();
        uaAccessStateByUserId[targetId] = sourceState;
        uaRenderAccessView();
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

    function uaPreventCredentialAutofill() {
        var passwordInput = document.getElementById('uaFormPassword');
        if (passwordInput) {
            passwordInput.value = '';
            passwordInput.type = 'password';
            passwordInput.readOnly = true;
        }
    }

    // Ensure browser autofill does not keep stale filter values across visits.
    uaForceResetFilters();
    uaPreventCredentialAutofill();

    // Some browsers apply credential autofill after script execution.
    window.setTimeout(uaForceResetFilters, 80);
    window.setTimeout(uaForceResetFilters, 320);
    window.setTimeout(uaForceResetFilters, 900);
    window.setTimeout(uaPreventCredentialAutofill, 80);
    window.setTimeout(uaPreventCredentialAutofill, 320);
    window.setTimeout(uaPreventCredentialAutofill, 900);
    window.addEventListener('pageshow', function() {
        uaForceResetFilters();
        uaPreventCredentialAutofill();
        uaCurrentPage = 1;
        uaRenderTable();
        uaRenderAccessView();
    });

    uaRenderCopyAccessOptions();
    uaSetSelectedSummary(null);
    uaCurrentPage = 1;
    uaRenderTable();
    uaRenderAccessView();
</script>
@endpush

