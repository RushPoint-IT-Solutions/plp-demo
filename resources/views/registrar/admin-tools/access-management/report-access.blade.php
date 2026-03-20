@extends('layouts.registrar')

@section('title', 'PLP - Report Access')
@section('page-title', 'REPORT ACCESS')
@section('body-class', 'page-report-access')



@section('content')
<div class="pf-page">
    <div class="ra-page">
        <section class="cfg-card">
            <div class="ra-toolbar">
                <div class="ra-search-wrap">
                    <label class="app-filter-label" for="raSearch">Search</label>
                    <div class="pf-search-wrap">
                        <span class="pf-search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input id="raSearch" type="text" class="pf-search-input" placeholder="Search Name / Email / User Type">
                    </div>
                </div>

                <div class="ra-type-filter">
                    <label class="app-filter-label" for="raReportType">Report Type</label>
                    <select id="raReportType" class="app-filter-select">
                        <option value="">All Report Types</option>
                        <option value="Academics Report">Academics Report</option>
                        <option value="Enrollment Report">Enrollment Report</option>
                        <option value="Student Report">Student Report</option>
                        <option value="Faculty Report">Faculty Report</option>
                    </select>
                </div>

                <button type="button" class="pf-btn-new" id="raSearchBtn">Search</button>
            </div>
        </section>

        <section>
            <div class="app-table-wrap ra-table-wrap">
                <table id="raTable" class="app-table cfg-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="raTableBody"></tbody>
                </table>
            </div>

            <div class="cfg-pagination ra-table-meta">
                <div class="ra-page-list">
                    <button type="button" class="ra-page-btn" disabled aria-label="Previous page">&lsaquo;</button>
                    <button type="button" class="ra-page-num active" aria-current="page">1</button>
                    <button type="button" class="ra-page-btn" disabled aria-label="Next page">&rsaquo;</button>
                </div>
                <div class="ra-caption">Click the access icon to configure report permissions for a user.</div>
            </div>
        </section>
    </div>
</div>

<div class="req-modal-overlay" id="raAccessModal" style="display:none;" onclick="if(event.target===this) raCloseAccessModal()">
    <div class="req-modal-box ra-modal-box">
        <div class="ra-modal-head">
            <h3 class="ra-modal-title">Access Control</h3>
            <div class="ra-modal-user" id="raModalUserMeta">User: -</div>
        </div>

        <div class="ra-modal-tools">
            <p class="ra-section-title">Assign report permissions</p>
            <label class="ra-check ra-check-master"><input id="raCheckAllReports" type="checkbox" class="req-checkbox-input"> Select All Reports</label>
        </div>

        <div class="ra-groups">
            <section class="ra-group">
                <div class="ra-group-head">
                    <div class="ra-group-title">
                        <h4>Enrollment Reports</h4>
                    </div>
                    <label class="ra-check ra-check-master"><input id="raCheckEnrollment" type="checkbox" class="req-checkbox-input" checked> Select All</label>
                </div>
                <div class="ra-group-list ra-group-list-single" id="raEnrollmentList">
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Enrollment Reports</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Number of Students Enrolled (By Religion)</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Enrollment Statistics Summary by Department</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Summary Enrollment Report</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Enrollment List Per Section</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Total Daily Enrollment Statistic</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Enrollment List and Report of Grades</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> List of Registered Students</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Masterlist Enrollment Report</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> List of Enrolled Students (CSV)</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Number of Students Enrolled (By Gender)</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Chart of Registered Students</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Number of Students Enrolled (By Year Level)</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> List of Freshmen and Transfer Students And Last School Attended</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Unenrolled Students</label>
                </div>
            </section>

            <section class="ra-group">
                <div class="ra-group-head">
                    <div class="ra-group-title">
                        <h4>Other Reports</h4>
                    </div>
                    <label class="ra-check ra-check-master"><input id="raCheckOther" type="checkbox" class="req-checkbox-input" checked> Select All</label>
                </div>
                <div class="ra-group-list ra-group-list-single" id="raOtherList">
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Changing of Grades</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Class Room Assignment</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Schedule of Classes by Section</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Schedule of Classes by Subject</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> User Accounts Report</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> List of Users</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Summary of Adding/Changing History</label>
                </div>
            </section>

            <section class="ra-group">
                <div class="ra-group-head">
                    <div class="ra-group-title">
                        <h4>Student Reports</h4>
                    </div>
                    <label class="ra-check ra-check-master"><input id="raCheckStudent" type="checkbox" class="req-checkbox-input" checked> Select All</label>
                </div>
                <div class="ra-group-list ra-group-list-single" id="raStudentList">
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> List of Candidates for Graduation</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> List of Students with Failing Grades</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input" checked> Scholastic Delinquency Report</label>
                </div>
            </section>

            <section class="ra-group">
                <div class="ra-group-head">
                    <div class="ra-group-title">
                        <h4>Faculty Reports</h4>
                    </div>
                    <label class="ra-check ra-check-master"><input id="raCheckFaculty" type="checkbox" class="req-checkbox-input"> Select All</label>
                </div>
                <div class="ra-group-list ra-group-list-single" id="raFacultyList">
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input"> Faculty Data</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input"> Faculty Loading Summary</label>
                    <label class="ra-check"><input type="checkbox" class="req-checkbox-input"> Faculty Evaluation Summary</label>
                </div>
            </section>
        </div>

        <div class="ra-modal-actions">
            <button type="button" class="req-btn-cancel" onclick="raCloseAccessModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="raSaveAccess()">Save</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var raUsers = [
        { name: 'Bares, Mark Jay', email: 'test@gmail.com', userType: 'Registrar', reportType: 'Academics Report' },
        { name: 'Austero, Andrea Jane', email: 'test@gmail.com', userType: 'Registrar', reportType: 'Academics Report' },
        { name: 'Santos, Maria', email: 'maria.santos@plp.edu.ph', userType: 'Faculty', reportType: 'Faculty Report' },
        { name: 'Dela Cruz, Juan', email: 'juan.delacruz@plp.edu.ph', userType: 'Accounting', reportType: 'Enrollment Report' }
    ];

    var raActiveIndex = null;

    function raEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function raGetFilteredUsers() {
        var query = (document.getElementById('raSearch').value || '').toLowerCase();
        var reportType = document.getElementById('raReportType').value;

        return raUsers
            .map(function(item, index) {
                return { item: item, index: index };
            })
            .filter(function(record) {
                var user = record.item;
                var queryMatch = !query ||
                    user.name.toLowerCase().indexOf(query) !== -1 ||
                    user.email.toLowerCase().indexOf(query) !== -1 ||
                    user.userType.toLowerCase().indexOf(query) !== -1;
                var typeMatch = !reportType || user.reportType === reportType;
                return queryMatch && typeMatch;
            });
    }

    function raRenderTable() {
        var tbody = document.getElementById('raTableBody');
        if (!tbody) return;

        var rows = raGetFilteredUsers().map(function(record) {
            var user = record.item;
            return '' +
                '<tr>' +
                    '<td>' + raEscapeHtml(user.name) + '</td>' +
                    '<td>' + raEscapeHtml(user.email) + '</td>' +
                    '<td>' + raEscapeHtml(user.userType) + '</td>' +
                    '<td style="text-align:center;">' +
                        '<button type="button" class="ra-action-btn" data-ra-index="' + record.index + '" title="Configure Access" aria-label="Configure Access">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6l7-3z"></path><path d="M9 12l2 2 4-4"></path></svg>' +
                        '</button>' +
                    '</td>' +
                '</tr>';
        }).join('');

        if (!rows) {
            rows = '<tr><td colspan="4" class="sc-empty-row">No users found.</td></tr>';
        }

        tbody.innerHTML = rows;
    }

    function raSetGroupToggle(masterId, listId) {
        var master = document.getElementById(masterId);
        var list = document.getElementById(listId);
        if (!master || !list) return;

        function syncMaster() {
            var items = list.querySelectorAll('input[type="checkbox"]');
            var checkedCount = 0;
            items.forEach(function(item) {
                if (item.checked) checkedCount += 1;
            });
            master.checked = checkedCount === items.length && items.length > 0;
            master.indeterminate = checkedCount > 0 && checkedCount < items.length;
        }

        master.addEventListener('change', function() {
            list.querySelectorAll('input[type="checkbox"]').forEach(function(check) {
                check.checked = master.checked;
            });
            master.indeterminate = false;
        });

        list.addEventListener('change', function(event) {
            if (event.target && event.target.matches('input[type="checkbox"]')) {
                syncMaster();
                raSyncGlobalToggle();
            }
        });

        syncMaster();
    }

    function raSyncGlobalToggle() {
        var global = document.getElementById('raCheckAllReports');
        if (!global) return;
        var allItems = document.querySelectorAll('#raEnrollmentList input[type="checkbox"], #raOtherList input[type="checkbox"], #raStudentList input[type="checkbox"], #raFacultyList input[type="checkbox"]');
        var checked = 0;
        allItems.forEach(function(item) {
            if (item.checked) checked += 1;
        });
        global.checked = allItems.length > 0 && checked === allItems.length;
        global.indeterminate = checked > 0 && checked < allItems.length;
    }

    function raSetGlobalToggle() {
        var global = document.getElementById('raCheckAllReports');
        if (!global) return;

        global.addEventListener('change', function() {
            var allItems = document.querySelectorAll('#raEnrollmentList input[type="checkbox"], #raOtherList input[type="checkbox"], #raStudentList input[type="checkbox"], #raFacultyList input[type="checkbox"]');
            allItems.forEach(function(item) {
                item.checked = global.checked;
            });
            global.indeterminate = false;
            ['raCheckEnrollment', 'raCheckOther', 'raCheckStudent', 'raCheckFaculty'].forEach(function(id) {
                var master = document.getElementById(id);
                if (master) {
                    master.checked = global.checked;
                    master.indeterminate = false;
                }
            });
        });

        raSyncGlobalToggle();
    }

    function raOpenAccessModal(index) {
        var user = raUsers[index];
        if (!user) return;
        raActiveIndex = index;
        document.getElementById('raModalUserMeta').textContent = 'User: ' + user.name + ' | Type: ' + user.userType;
        document.getElementById('raAccessModal').style.display = 'flex';
    }

    function raCloseAccessModal() {
        document.getElementById('raAccessModal').style.display = 'none';
        raActiveIndex = null;
    }

    function raSaveAccess() {
        raCloseAccessModal();
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Report access updated successfully.');
        }
    }

    document.getElementById('raSearchBtn').addEventListener('click', raRenderTable);
    document.getElementById('raSearch').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            raRenderTable();
        }
    });
    document.getElementById('raReportType').addEventListener('change', raRenderTable);

    document.addEventListener('click', function(event) {
        var actionBtn = event.target.closest('[data-ra-index]');
        if (actionBtn) {
            raOpenAccessModal(parseInt(actionBtn.getAttribute('data-ra-index'), 10));
        }
    });

    raSetGroupToggle('raCheckEnrollment', 'raEnrollmentList');
    raSetGroupToggle('raCheckOther', 'raOtherList');
    raSetGroupToggle('raCheckStudent', 'raStudentList');
    raSetGroupToggle('raCheckFaculty', 'raFacultyList');
    raSetGlobalToggle();

    raRenderTable();
</script>
@endpush

