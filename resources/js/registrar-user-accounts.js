(function () {
    var root = document.getElementById('uaPageRoot');
    if (!root) {
        return;
    }

    var dataEndpoint = root.getAttribute('data-data-endpoint') || '';
    var storeEndpoint = root.getAttribute('data-store-endpoint') || '';
    var updateTemplate = root.getAttribute('data-update-template') || '';
    var deleteTemplate = root.getAttribute('data-delete-template') || '';
    var accessControlModulesEndpoint = root.getAttribute('data-access-modules-endpoint') || '';
    var accessControlShowTemplate = root.getAttribute('data-access-show-template') || '';
    var accessControlUpdateTemplate = root.getAttribute('data-access-update-template') || '';
    var roleDataEndpoint = root.getAttribute('data-role-data-endpoint') || '';
    var roleStoreEndpoint = root.getAttribute('data-role-store-endpoint') || '';
    var roleUpdateTemplate = root.getAttribute('data-role-update-template') || '';
    var roleDeleteTemplate = root.getAttribute('data-role-delete-template') || '';
    var roleAccessShowTemplate = root.getAttribute('data-role-access-show-template') || '';
    var roleAccessUpdateTemplate = root.getAttribute('data-role-access-update-template') || '';
    var csrfToken = root.getAttribute('data-csrf-token') || '';

    var state = {
        selectedUser: null,
        pendingDeleteUser: null,
        rowActionMenuOpenPk: null,
        rows: [],
        currentPage: 1,
        lastPage: 1,
        perPage: 5,
        total: 0,
        from: 0,
        roles: [],
        pendingDeleteRole: null,
        editingRoleId: null,
        accessControl: {
            targetKind: 'user',
            targetId: null,
            targetUserId: null,
            targetUserLabel: '',
            source: 'explicit',
            permissionTypes: [],
            modules: [],
            persistableModuleCodes: [],
            matrix: {},
            expandedModules: {},
            quickOptionFallback: {},
        },
    };

    var quickAccessDefinitions = [
        { key: 'accept_prereq_overload', label: 'Accept Pre-requisite Subjects and Overload Units', keywords: ['prerequisite', 'pre-requisite', 'overload', 'subject'], permission: 'edit' },
        { key: 'accept_balance_ams', label: 'Accept Student with balance (AMS Registration)', keywords: ['ams registration', 'registration'], permission: 'edit' },
        { key: 'add_electives', label: 'Can Add Elective Subjects (AMS Registration / Student Enrollment)', keywords: ['elective', 'subject', 'enrollment'], permission: 'edit' },
        { key: 'accept_balance_student', label: 'Accept Student with balance (Student Enrollment)', keywords: ['student enrollment', 'enrollment'], permission: 'edit' },
        { key: 'student_enrollment_config', label: 'Student Enrollment Config', keywords: ['enrollment'], permission: 'edit' },
        { key: 'faculty_loading_config', label: 'Faculty Loading Config', keywords: ['faculty', 'loading'], permission: 'edit' },
        { key: 'override_deficiency', label: 'Can Override Deficiency', keywords: ['deficiency'], permission: 'edit' },
        { key: 'accept_conflict_schedule', label: 'Can Accept Conflict Schedule', keywords: ['schedule'], permission: 'edit' },
        { key: 'change_professor', label: 'Can Change Professor (Grading Sheet Module)', keywords: ['grading', 'grade'], permission: 'edit' },
        { key: 'dissolve_section', label: 'Can Dissolved Section', keywords: ['section'], permission: 'edit' },
        { key: 'approve_gradesheet', label: 'Can approve gradesheet', keywords: ['grading', 'grade'], permission: 'edit' },
        { key: 'email_sender_delete_edit', label: 'Can Delete/Edit Email Sender', keywords: ['email', 'sender'], permission: 'edit' },
        { key: 'student_grade_subject_delete_edit', label: 'Can delete/edit subject in student grade file', keywords: ['student grade', 'grade file', 'subject'], permission: 'edit' },
    ];

    var listRequestState = {
        controller: null,
        sequence: 0,
    };

    var accessControlRequestState = {
        controller: null,
        sequence: 0,
    };

    var credentialRequestState = {
        userId: {
            controller: null,
            sequence: 0,
        },
        name: {
            controller: null,
            sequence: 0,
        },
    };

    var credentialLookupState = {
        openField: '',
        activeIndex: {
            userId: -1,
            name: -1,
        },
        candidates: {
            userId: [],
            name: [],
        },
    };

    var inputDebounceTimers = {
        uaStudentId: null,
        uaLastName: null,
        uaFirstName: null,
    };

    var els = {
        studentId: document.getElementById('uaStudentId'),
        lastName: document.getElementById('uaLastName'),
        firstName: document.getElementById('uaFirstName'),
        userType: document.getElementById('uaUserType'),
        searchBtn: document.getElementById('uaSearchBtn'),
        clearBtn: document.getElementById('uaClearBtn'),

        tableBody: document.getElementById('uaTableBody'),
        pager: document.querySelector('.ua-table-meta .app-table-pager'),

        selectedName: document.getElementById('uaSelectedUserName'),
        selectedUserId: document.getElementById('uaSelectedUserId'),
        selectedUserType: document.getElementById('uaSelectedUserType'),
        selectedEmail: document.getElementById('uaSelectedUserEmail'),

        formUserId: document.getElementById('uaFormUserId'),
        formUserIdDropdown: document.getElementById('uaFormUserIdDropdown'),
        formName: document.getElementById('uaFormName'),
        formNameDropdown: document.getElementById('uaFormNameDropdown'),
        formEmail: document.getElementById('uaFormEmail'),
        formPassword: document.getElementById('uaFormPassword'),
        formUserType: document.getElementById('uaFormUserType'),
        formRole: document.getElementById('uaFormRole'),
        formCourseScope: document.getElementById('uaFormCourseScope'),
        inactive: document.getElementById('uaInactive'),
        formModeBadge: document.getElementById('uaFormModeBadge'),
        newAccountBtn: document.getElementById('uaNewAccountBtn'),

        saveBtn: document.getElementById('uaSaveBtn'),
        cancelBtn: document.getElementById('uaCancelBtn'),
        passwordToggle: document.getElementById('uaPasswordToggle'),

        deleteModal: document.getElementById('uaDeleteModal'),
        deleteModalText: document.getElementById('uaDeleteModalText'),
        deleteCancelBtn: document.getElementById('uaDeleteCancelBtn'),
        deleteConfirmBtn: document.getElementById('uaDeleteConfirmBtn'),

        accessModal: document.getElementById('uaAccessModal'),
        accessModalUserLabel: document.getElementById('uaAccessModalUserLabel'),
        accessModalNote: document.getElementById('uaAccessModalNote'),
        accessCopyRow: document.getElementById('uaAccessCopyRow'),
        accessTableHead: document.getElementById('uaAccessTableHead'),
        accessTableBody: document.getElementById('uaAccessTableBody'),
        accessFootnote: document.getElementById('uaAccessFootnote'),
        accessCloseX: document.getElementById('uaAccessCloseX'),
        accessCancelBtn: document.getElementById('uaAccessCancelBtn'),
        accessSaveBtn: document.getElementById('uaAccessSaveBtn'),
        accessCopySelect: document.getElementById('uaCopyAccessFrom'),
        accessCopyBtn: document.getElementById('uaCopyAccessBtn'),
        quickAccessToggle: document.getElementById('uaQuickAccessToggle'),
        quickAccessList: document.getElementById('uaAccessQuickList'),

        roleTableBody: document.getElementById('uaRoleTableBody'),
        newRoleBtn: document.getElementById('uaNewRoleBtn'),
        roleModal: document.getElementById('uaRoleModal'),
        roleModalTitle: document.getElementById('uaRoleModalTitle'),
        roleFormName: document.getElementById('uaRoleFormName'),
        roleFormDescription: document.getElementById('uaRoleFormDescription'),
        roleModalCancelBtn: document.getElementById('uaRoleModalCancelBtn'),
        roleModalSaveBtn: document.getElementById('uaRoleModalSaveBtn'),
        roleDeleteModal: document.getElementById('uaRoleDeleteModal'),
        roleDeleteModalText: document.getElementById('uaRoleDeleteModalText'),
        roleDeleteCancelBtn: document.getElementById('uaRoleDeleteCancelBtn'),
        roleDeleteConfirmBtn: document.getElementById('uaRoleDeleteConfirmBtn'),
    };

    function uaNormalize(value) {
        return String(value || '').trim().toLowerCase();
    }

    function uaEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            };
            return map[ch] || ch;
        });
    }

    function uaStatusBadge(inactive) {
        if (inactive) {
            return '<span class="ua-status-badge ua-status-inactive">Inactive</span>';
        }
        return '<span class="ua-status-badge ua-status-active">Active</span>';
    }

    function uaCanEditAccess(user) {
        var typeCode = uaNormalize(user && (user.userTypeCode || user.userType) || '');
        return typeCode === 'registrar' || typeCode.indexOf('registrar') !== -1;
    }

    function uaSlugify(value) {
        return uaNormalize(value)
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }

    function uaDesiredRootOrder(code) {
        var normalized = uaNormalize(code).replace(/\s+/g, '_');

        if (normalized === 'admissions') {
            return 0;
        }
        if (normalized === 'student_records') {
            return 1;
        }
        if (normalized === 'academics') {
            return 2;
        }
        if (normalized === 'faculty') {
            return 3;
        }
        if (normalized === 'documents_forms') {
            return 4;
        }
        if (normalized === 'reports') {
            return 5;
        }
        if (normalized === 'system' || normalized === 'admin_tools' || normalized === 'admintools' || normalized.indexOf('admin') !== -1) {
            return 6;
        }

        return 99;
    }

    function uaShouldDefaultExpandModule(moduleCode) {
        return uaDesiredRootOrder(moduleCode) === 0;
    }

    function uaUniquePush(list, value) {
        var normalizedValue = uaNormalize(value);
        if (!normalizedValue) {
            return;
        }

        var exists = list.some(function (item) {
            return uaNormalize(item) === normalizedValue;
        });

        if (!exists) {
            list.push(String(value).trim());
        }
    }

    function uaBuildSidebarModuleChildrenMap() {
        var map = {
            admissions: [],
            student_records: [],
            academics: [],
            faculty: [],
            documents_forms: [],
            reports: [],
            system: [],
        };

        var nav = document.querySelector('.sidebar-nav');
        if (!nav) {
            return map;
        }

        var toggles = nav.querySelectorAll('.sidebar-dropdown > .sidebar-link.sidebar-dropdown-toggle');
        toggles.forEach(function (toggle) {
            var titleNode = toggle.querySelector('span');
            var title = titleNode ? uaNormalize(titleNode.textContent || '') : '';
            var key = '';

            if (title === 'admissions') {
                key = 'admissions';
            } else if (title === 'student records') {
                key = 'student_records';
            } else if (title === 'academics') {
                key = 'academics';
            } else if (title === 'faculty') {
                key = 'faculty';
            } else if (title === 'documents & forms') {
                key = 'documents_forms';
            } else if (title === 'reports') {
                key = 'reports';
            } else if (title === 'system' || title === 'admin tools') {
                key = 'system';
            }

            if (!key) {
                return;
            }

            var dropdown = toggle.parentElement;
            if (!dropdown) {
                return;
            }

            var leafLinks = dropdown.querySelectorAll('.sidebar-dropdown-menu .sidebar-sublink:not(.sidebar-nested-toggle)');
            leafLinks.forEach(function (link) {
                var text = String(link.textContent || '').trim();
                uaUniquePush(map[key], text);
            });
        });

        return map;
    }

    function uaBuildDisplayModules(rawModules) {
        var modules = (rawModules || []).map(function (module) {
            return {
                id: module.id,
                code: module.code,
                name: module.name,
                parentId: module.parentId,
                actions: module.actions || {},
                persistCode: module.persistCode || module.code,
                synthetic: !!module.synthetic,
            };
        });

        if (!modules.length) {
            return [];
        }

        var roots = modules
            .filter(function (module) {
                return module.parentId === null || typeof module.parentId === 'undefined';
            })
            .sort(function (a, b) {
                var rankA = uaDesiredRootOrder(a.code || a.name);
                var rankB = uaDesiredRootOrder(b.code || b.name);
                if (rankA !== rankB) {
                    return rankA - rankB;
                }

                var nameA = uaNormalize(a.name || a.code);
                var nameB = uaNormalize(b.name || b.code);
                return nameA < nameB ? -1 : (nameA > nameB ? 1 : 0);
            });

        var nonRootModules = modules.filter(function (module) {
            return !(module.parentId === null || typeof module.parentId === 'undefined');
        });

        if (nonRootModules.length) {
            var byParent = {};
            nonRootModules.forEach(function (module) {
                var parentKey = String(module.parentId);
                if (!byParent[parentKey]) {
                    byParent[parentKey] = [];
                }
                byParent[parentKey].push(module);
            });

            var ordered = [];
            function appendBranch(parentModule) {
                ordered.push(parentModule);
                var children = byParent[String(parentModule.id)] || [];
                children.forEach(function (child) {
                    appendBranch(child);
                });
            }

            roots.forEach(function (rootModule) {
                appendBranch(rootModule);
            });

            return ordered;
        }

        var sidebarChildrenByRoot = uaBuildSidebarModuleChildrenMap();
        var expanded = [];

        roots.forEach(function (rootModule) {
            expanded.push(rootModule);

            var rootCode = uaNormalize(rootModule.code || rootModule.name).replace(/\s+/g, '_');
            var childLabels = sidebarChildrenByRoot[rootCode] || [];

            childLabels.forEach(function (label, index) {
                expanded.push({
                    id: 'virtual-' + rootCode + '-' + index,
                    code: 'virtual:' + rootCode + ':' + uaSlugify(label),
                    name: label,
                    parentId: rootModule.id,
                    actions: {},
                    persistCode: rootModule.code,
                    synthetic: true,
                });
            });
        });

        return expanded;
    }

    function uaBuildUpdateUrl(id) {
        return updateTemplate.replace('__ID__', String(id));
    }

    function uaBuildDeleteUrl(id) {
        return deleteTemplate.replace('__ID__', String(id));
    }

    function uaBuildAccessShowUrl(id) {
        return accessControlShowTemplate.replace('__ID__', String(id));
    }

    function uaBuildAccessUpdateUrl(id) {
        return accessControlUpdateTemplate.replace('__ID__', String(id));
    }

    function uaBuildRoleUpdateUrl(id) {
        return roleUpdateTemplate.replace('__ID__', String(id));
    }

    function uaBuildRoleDeleteUrl(id) {
        return roleDeleteTemplate.replace('__ID__', String(id));
    }

    function uaBuildRoleAccessShowUrl(id) {
        return roleAccessShowTemplate.replace('__ID__', String(id));
    }

    function uaBuildRoleAccessUpdateUrl(id) {
        return roleAccessUpdateTemplate.replace('__ID__', String(id));
    }

    function uaGetFilters() {
        return {
            user_id: (els.studentId ? els.studentId.value : '').trim(),
            last_name: (els.lastName ? els.lastName.value : '').trim(),
            first_name: (els.firstName ? els.firstName.value : '').trim(),
            user_type: (els.userType ? els.userType.value : '').trim(),
        };
    }

    function uaBuildDataUrl(page) {
        var url = new URL(dataEndpoint, window.location.origin);
        var filters = uaGetFilters();

        Object.keys(filters).forEach(function (key) {
            if (filters[key]) {
                url.searchParams.set(key, filters[key]);
            }
        });

        url.searchParams.set('page', String(page || 1));
        url.searchParams.set('per_page', String(state.perPage));

        return url.toString();
    }

    async function uaApiRequest(url, method, payload, requestOptions) {
        requestOptions = requestOptions || {};

        var response;
        try {
            response = await fetch(url, {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload ? JSON.stringify(payload) : null,
                credentials: 'same-origin',
                signal: requestOptions.signal || undefined,
            });
        } catch (error) {
            if (requestOptions.allowAbort && error && error.name === 'AbortError') {
                throw error;
            }

            throw new Error((error && error.message) ? error.message : 'Network request failed.');
        }

        var json = {};
        try {
            json = await response.json();
        } catch (error) {
            json = {};
        }

        if (!response.ok || json.ok === false) {
            var firstError = null;
            if (json.errors) {
                var firstField = Object.keys(json.errors)[0];
                if (firstField && json.errors[firstField] && json.errors[firstField][0]) {
                    firstError = json.errors[firstField][0];
                }
            }
            throw new Error(firstError || json.message || 'Unable to process user account request.');
        }

        return json;
    }

    function uaSetSelectedSummary(user) {
        if (els.selectedName) {
            els.selectedName.textContent = user ? user.fullName : '-';
        }
        if (els.selectedUserId) {
            els.selectedUserId.textContent = user ? user.userId : '-';
        }
        if (els.selectedUserType) {
            els.selectedUserType.textContent = user ? user.userType : '-';
        }
        if (els.selectedEmail) {
            els.selectedEmail.textContent = user && user.email ? user.email : '-';
        }
    }

    function uaClearForm() {
        uaCloseCredentialSearchDropdowns();

        if (els.formUserId) {
            els.formUserId.value = '';
        }
        if (els.formName) {
            els.formName.value = '';
        }
        if (els.formEmail) {
            els.formEmail.value = '';
        }
        if (els.formPassword) {
            els.formPassword.value = '';
            els.formPassword.type = 'password';
            els.formPassword.readOnly = true;
        }
        if (els.passwordToggle) {
            els.passwordToggle.classList.remove('is-visible');
            els.passwordToggle.setAttribute('aria-label', 'Show password');
        }
        if (els.formUserType) {
            els.formUserType.value = '';
        }
        if (els.formRole) {
            els.formRole.value = '';
        }
        if (els.formCourseScope) {
            Array.prototype.forEach.call(els.formCourseScope.options, function (option) {
                option.selected = false;
            });
        }
        if (els.inactive) {
            els.inactive.checked = false;
        }

        state.selectedUser = null;
        uaSetSelectedSummary(null);
        uaSetFormMode('create');
    }

    function uaFillForm(user) {
        if (!user) {
            uaClearForm();
            return;
        }

        uaCloseCredentialSearchDropdowns();

        state.selectedUser = user;

        if (els.formUserId) {
            els.formUserId.value = user.userId || '';
        }
        if (els.formName) {
            els.formName.value = user.fullName || '';
        }
        if (els.formEmail) {
            els.formEmail.value = user.email || '';
        }
        if (els.formPassword) {
            els.formPassword.value = '';
            els.formPassword.type = 'password';
            els.formPassword.readOnly = true;
        }
        if (els.passwordToggle) {
            els.passwordToggle.classList.remove('is-visible');
            els.passwordToggle.setAttribute('aria-label', 'Show password');
        }
        if (els.formUserType) {
            els.formUserType.value = user.userTypeCode || '';
        }
        if (els.formRole) {
            els.formRole.value = user.roleId ? String(user.roleId) : '';
        }
        if (els.formCourseScope) {
            var scopedIds = (user.courseScopeIds || []).map(String);
            Array.prototype.forEach.call(els.formCourseScope.options, function (option) {
                option.selected = scopedIds.indexOf(option.value) !== -1;
            });
        }
        if (els.inactive) {
            els.inactive.checked = !!user.inactive;
        }

        uaSetSelectedSummary(user);
        uaHighlightSelectedRow();
        uaSetFormMode('edit');
    }

    function uaSetFormMode(mode) {
        if (!els.formModeBadge) {
            return;
        }

        if (mode === 'create') {
            els.formModeBadge.textContent = 'Creating new account';
            els.formModeBadge.classList.add('is-create-mode');
        } else {
            els.formModeBadge.textContent = 'Editing selected account';
            els.formModeBadge.classList.remove('is-create-mode');
        }
    }

    function uaHighlightSelectedRow() {
        if (!els.tableBody) {
            return;
        }

        var selectedPk = state.selectedUser ? String(state.selectedUser.pk) : '';
        var rows = els.tableBody.querySelectorAll('tr[data-ua-user-pk]');

        rows.forEach(function (row) {
            var rowPk = row.getAttribute('data-ua-user-pk') || '';
            row.classList.toggle('ua-selected-row', selectedPk !== '' && rowPk === selectedPk);
        });
    }

    function uaCloseRowActionMenus(exceptPk) {
        if (!els.tableBody) {
            return;
        }

        var keepPk = exceptPk ? String(exceptPk) : '';
        var wraps = els.tableBody.querySelectorAll('.ua-row-action-menu-wrap');

        wraps.forEach(function (wrap) {
            var rowPk = String(wrap.getAttribute('data-ua-row-pk') || '');
            var shouldStayOpen = keepPk && rowPk === keepPk;
            var menu = wrap.querySelector('.ua-row-action-menu');
            var toggle = wrap.querySelector('.ua-row-menu-btn');

            if (menu) {
                menu.classList.toggle('open', !!shouldStayOpen);
                if (!shouldStayOpen) {
                    menu.classList.remove('drop-up');
                }
            }

            if (toggle) {
                toggle.setAttribute('aria-expanded', shouldStayOpen ? 'true' : 'false');
            }
        });

        state.rowActionMenuOpenPk = keepPk || null;
    }

    function uaToggleRowActionMenu(rowPk) {
        var targetPk = String(rowPk || '');
        if (!targetPk) {
            return;
        }

        if (state.rowActionMenuOpenPk && String(state.rowActionMenuOpenPk) === targetPk) {
            uaCloseRowActionMenus();
            return;
        }

        uaCloseRowActionMenus(targetPk);
    }

    function uaRenderTable() {
        if (!els.tableBody) {
            return;
        }

        if (!state.rows.length) {
            els.tableBody.innerHTML = '<tr><td colspan="7" class="sc-empty-row">No user accounts found.</td></tr>';
            return;
        }

        var html = state.rows.map(function (user, index) {
            var rowNo = (state.from || 0) + index;
            var editActionHtml = uaCanEditAccess(user)
                ? ''
                    + '<button type="button" data-ua-access-user-pk="' + uaEscapeHtml(user.pk) + '">Edit Access</button>'
                : '';

            return '' +
                '<tr data-ua-user-pk="' + uaEscapeHtml(user.pk) + '">' +
                    '<td>' + rowNo + '</td>' +
                    '<td>' + uaEscapeHtml(user.userId) + '</td>' +
                    '<td>' + uaEscapeHtml(user.fullName) + '</td>' +
                    '<td>' + uaEscapeHtml(user.email || '-') + '</td>' +
                    '<td>' + uaEscapeHtml(user.userType) + '</td>' +
                    '<td>' + uaStatusBadge(user.inactive) + '</td>' +
                    '<td class="ua-col-action-cell">' +
                        '<div class="ua-row-action-menu-wrap" data-ua-row-pk="' + uaEscapeHtml(user.pk) + '">' +
                            '<button type="button" class="apst-action-btn ua-row-menu-btn" data-ua-menu-toggle="' + uaEscapeHtml(user.pk) + '" title="Actions" aria-haspopup="menu" aria-expanded="false">' +
                                '<span></span><span></span><span></span>' +
                            '</button>' +
                            '<div class="apst-dropdown ua-row-action-menu" data-ua-menu="' + uaEscapeHtml(user.pk) + '" role="menu">' +
                                editActionHtml +
                                '<button type="button" class="apst-del-btn" data-ua-delete-user-pk="' + uaEscapeHtml(user.pk) + '">Delete</button>' +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
        }).join('');

        els.tableBody.innerHTML = html;
        uaHighlightSelectedRow();
        uaCloseRowActionMenus();
    }

    function uaRenderPager() {
        if (!els.pager) {
            return;
        }

        if (state.lastPage <= 1) {
            els.pager.innerHTML = '';
            return;
        }

        var start = Math.max(1, state.currentPage - 2);
        var end = Math.min(state.lastPage, state.currentPage + 2);

        if (state.currentPage <= 3) {
            end = Math.min(state.lastPage, 5);
        } else if (state.currentPage >= state.lastPage - 2) {
            start = Math.max(1, state.lastPage - 4);
        }

        var pageButtons = '';
        for (var p = start; p <= end; p += 1) {
            pageButtons += '<button type="button" class="rtp-page-num ' + (p === state.currentPage ? 'active' : '') + '" data-ua-page="' + p + '">' + p + '</button>';
        }

        els.pager.innerHTML = '' +
            '<div class="rtp-pagination">' +
                '<nav class="rtp-nav" aria-label="User accounts pagination">' +
                    '<div class="rtp-list" role="group" aria-label="Page controls">' +
                        '<button type="button" class="rtp-page-btn" data-ua-page-prev="1" ' + (state.currentPage <= 1 ? 'disabled' : '') + '>&lt;</button>' +
                        pageButtons +
                        '<button type="button" class="rtp-page-btn" data-ua-page-next="1" ' + (state.currentPage >= state.lastPage ? 'disabled' : '') + '>&gt;</button>' +
                    '</div>' +
                '</nav>' +
            '</div>';
    }

    function uaPickCurrentSelectionAfterRefresh() {
        if (!state.selectedUser || !state.selectedUser.pk) {
            return;
        }

        var selectedPk = String(state.selectedUser.pk);
        var fresh = state.rows.find(function (row) {
            return String(row.pk) === selectedPk;
        }) || null;

        if (fresh) {
            uaFillForm(fresh);
        } else {
            uaClearForm();
        }
    }

    function uaRenderLoading() {
        if (!els.tableBody) {
            return;
        }

        els.tableBody.innerHTML = '<tr><td colspan="7" class="sc-empty-row">Loading user accounts...</td></tr>';
    }

    async function uaFetchRows(page) {
        if (listRequestState.controller) {
            listRequestState.controller.abort();
        }

        listRequestState.controller = new AbortController();
        listRequestState.sequence += 1;
        var requestSequence = listRequestState.sequence;

        uaRenderLoading();

        try {
            var json = await uaApiRequest(uaBuildDataUrl(page), 'GET', null, {
                signal: listRequestState.controller.signal,
                allowAbort: true,
            });

            if (requestSequence !== listRequestState.sequence) {
                return;
            }

            var meta = json.meta || {};

            state.rows = json.rows || [];
            state.currentPage = parseInt(meta.currentPage, 10) || 1;
            state.lastPage = parseInt(meta.lastPage, 10) || 1;
            state.perPage = parseInt(meta.perPage, 10) || 5;
            state.total = parseInt(meta.total, 10) || 0;
            state.from = parseInt(meta.from, 10) || 0;

            uaRenderTable();
            uaRenderPager();
            uaPickCurrentSelectionAfterRefresh();
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }

            if (requestSequence !== listRequestState.sequence) {
                return;
            }

            state.rows = [];
            uaRenderTable();
            uaRenderPager();

            var message = String((error && error.message) || '').toLowerCase();
            if (message.indexOf('failed to fetch') !== -1 || message.indexOf('network') !== -1) {
                return;
            }

            alert(error.message || 'Unable to load user accounts.');
        } finally {
            if (requestSequence === listRequestState.sequence) {
                listRequestState.controller = null;
            }
        }
    }

    function uaGetCredentialFieldConfig(fieldKey) {
        if (fieldKey === 'userId') {
            return {
                input: els.formUserId,
                dropdown: els.formUserIdDropdown,
                queryParam: 'user_id',
            };
        }

        if (fieldKey === 'name') {
            return {
                input: els.formName,
                dropdown: els.formNameDropdown,
                queryParam: 'first_name',
            };
        }

        return null;
    }

    function uaScoreLookupText(term, value) {
        var normalizedTerm = uaNormalize(term);
        var normalizedValue = uaNormalize(value);

        if (!normalizedTerm) {
            return 8;
        }

        if (normalizedValue === normalizedTerm) {
            return 0;
        }

        if (normalizedValue.indexOf(normalizedTerm) === 0) {
            return 1;
        }

        if (normalizedValue.indexOf(normalizedTerm) !== -1) {
            return 2;
        }

        return 99;
    }

    function uaRankLookupCandidate(fieldKey, user, term) {
        var primary = fieldKey === 'userId' ? user.userId : user.fullName;
        var secondary = fieldKey === 'userId' ? user.fullName : user.userId;

        var primaryScore = uaScoreLookupText(term, primary);
        var secondaryScore = uaScoreLookupText(term, secondary) + 3;

        return Math.min(primaryScore, secondaryScore);
    }

    function uaSortLookupCandidates(fieldKey, candidates, term) {
        return (candidates || []).slice().sort(function (a, b) {
            var scoreA = uaRankLookupCandidate(fieldKey, a, term);
            var scoreB = uaRankLookupCandidate(fieldKey, b, term);

            if (scoreA !== scoreB) {
                return scoreA - scoreB;
            }

            var nameA = uaNormalize(a.fullName);
            var nameB = uaNormalize(b.fullName);
            if (nameA !== nameB) {
                return nameA < nameB ? -1 : 1;
            }

            var idA = uaNormalize(a.userId);
            var idB = uaNormalize(b.userId);
            if (idA !== idB) {
                return idA < idB ? -1 : 1;
            }

            return 0;
        });
    }

    function uaGetLocalLookupCandidates(fieldKey, term) {
        var normalizedTerm = uaNormalize(term);
        var localPool = (state.rows || []).slice();

        if (state.selectedUser && state.selectedUser.pk) {
            var selectedPk = String(state.selectedUser.pk);
            var alreadyIncluded = localPool.some(function (row) {
                return String(row.pk) === selectedPk;
            });

            if (!alreadyIncluded) {
                localPool.push(state.selectedUser);
            }
        }

        var ranked = uaSortLookupCandidates(fieldKey, localPool, normalizedTerm);

        if (!normalizedTerm) {
            return ranked.slice(0, 15);
        }

        return ranked.filter(function (row) {
            return uaRankLookupCandidate(fieldKey, row, normalizedTerm) < 99;
        }).slice(0, 15);
    }

    function uaCloseCredentialSearchDropdown(fieldKey) {
        var config = uaGetCredentialFieldConfig(fieldKey);
        if (!config || !config.dropdown) {
            return;
        }

        config.dropdown.classList.remove('is-open');
        credentialLookupState.activeIndex[fieldKey] = -1;
    }

    function uaCloseCredentialSearchDropdowns(exceptField) {
        ['userId', 'name'].forEach(function (fieldKey) {
            if (exceptField && fieldKey === exceptField) {
                return;
            }

            uaCloseCredentialSearchDropdown(fieldKey);
        });

        credentialLookupState.openField = exceptField || '';
    }

    function uaSetCredentialActiveOption(fieldKey, optionIndex, shouldScroll) {
        var config = uaGetCredentialFieldConfig(fieldKey);
        if (!config || !config.dropdown) {
            return;
        }

        var options = Array.prototype.slice.call(config.dropdown.querySelectorAll('[data-ua-candidate-pk]'));
        if (!options.length) {
            credentialLookupState.activeIndex[fieldKey] = -1;
            return;
        }

        var safeIndex = optionIndex;
        if (safeIndex < 0) {
            safeIndex = options.length - 1;
        }
        if (safeIndex >= options.length) {
            safeIndex = 0;
        }

        options.forEach(function (option) {
            option.classList.remove('is-active');
        });

        options[safeIndex].classList.add('is-active');
        credentialLookupState.activeIndex[fieldKey] = safeIndex;

        if (shouldScroll && typeof options[safeIndex].scrollIntoView === 'function') {
            options[safeIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    function uaRenderCredentialSearchDropdown(fieldKey, candidates, term) {
        var config = uaGetCredentialFieldConfig(fieldKey);
        if (!config || !config.dropdown) {
            return;
        }

        credentialLookupState.candidates[fieldKey] = candidates || [];

        if (!credentialLookupState.candidates[fieldKey].length) {
            var emptyLabel = term ? 'No matching user account found.' : 'No user account found.';
            config.dropdown.innerHTML = '<div class="smrg-search-empty">' + uaEscapeHtml(emptyLabel) + '</div>';
            config.dropdown.classList.add('is-open');
            credentialLookupState.activeIndex[fieldKey] = -1;
            credentialLookupState.openField = fieldKey;
            return;
        }

        var html = credentialLookupState.candidates[fieldKey].map(function (candidate, index) {
            var selectedClass = state.selectedUser && String(state.selectedUser.pk) === String(candidate.pk) ? ' is-selected' : '';

            return '' +
                '<button type="button" class="smrg-search-option ua-credential-option' + selectedClass + '" data-ua-candidate-pk="' + uaEscapeHtml(candidate.pk) + '" data-ua-option-index="' + index + '">' +
                    '<span class="ua-credential-option-main">' +
                        '<span class="ua-credential-option-id">' + uaEscapeHtml(candidate.userId) + '</span>' +
                        '<span class="ua-credential-option-name">' + uaEscapeHtml(candidate.fullName) + '</span>' +
                    '</span>' +
                '</button>';
        }).join('');

        config.dropdown.innerHTML = html;
        config.dropdown.classList.add('is-open');
        credentialLookupState.openField = fieldKey;
        uaSetCredentialActiveOption(fieldKey, 0, false);
    }

    async function uaFetchCredentialCandidates(fieldKey, term) {
        var config = uaGetCredentialFieldConfig(fieldKey);
        var requestState = credentialRequestState[fieldKey];

        if (!config || !requestState) {
            return [];
        }

        if (requestState.controller) {
            requestState.controller.abort();
        }

        requestState.controller = new AbortController();
        requestState.sequence += 1;
        var requestSequence = requestState.sequence;

        try {
            var url = new URL(dataEndpoint, window.location.origin);
            var normalizedTerm = String(term || '').trim();
            if (normalizedTerm) {
                url.searchParams.set(config.queryParam, normalizedTerm);
            }

            url.searchParams.set('page', '1');
            url.searchParams.set('per_page', '15');

            var json = await uaApiRequest(url.toString(), 'GET', null, {
                signal: requestState.controller.signal,
                allowAbort: true,
            });

            if (requestSequence !== requestState.sequence) {
                return null;
            }

            var rows = Array.isArray(json.rows) ? json.rows : [];
            return uaSortLookupCandidates(fieldKey, rows, normalizedTerm);
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return null;
            }

            return [];
        } finally {
            if (requestSequence === requestState.sequence) {
                requestState.controller = null;
            }
        }
    }

    function uaOpenCredentialSearchDropdown(fieldKey) {
        var config = uaGetCredentialFieldConfig(fieldKey);
        if (!config || !config.input || !config.dropdown) {
            return;
        }

        uaCloseCredentialSearchDropdowns(fieldKey);

        var term = config.input.value;
        var normalizedTerm = String(term || '').trim();
        var localCandidates = uaGetLocalLookupCandidates(fieldKey, normalizedTerm);

        if (localCandidates.length || normalizedTerm) {
            uaRenderCredentialSearchDropdown(fieldKey, localCandidates, normalizedTerm);
        }

        uaFetchCredentialCandidates(fieldKey, term).then(function (candidates) {
            if (credentialLookupState.openField !== fieldKey) {
                return;
            }

            if (candidates === null) {
                return;
            }

            uaRenderCredentialSearchDropdown(fieldKey, candidates, term);
        });
    }

    function uaSelectCredentialSearchCandidate(fieldKey, userPk) {
        var candidates = credentialLookupState.candidates[fieldKey] || [];
        var matched = candidates.find(function (row) {
            return String(row.pk) === String(userPk);
        }) || null;

        if (!matched) {
            return;
        }

        uaFillForm(matched);
    }

    function uaSelectActiveCredentialOption(fieldKey) {
        var config = uaGetCredentialFieldConfig(fieldKey);
        if (!config || !config.dropdown) {
            return;
        }

        var options = Array.prototype.slice.call(config.dropdown.querySelectorAll('[data-ua-candidate-pk]'));
        if (!options.length) {
            return;
        }

        var activeIndex = credentialLookupState.activeIndex[fieldKey];
        if (activeIndex < 0 || activeIndex >= options.length) {
            activeIndex = 0;
        }

        var userPk = options[activeIndex].getAttribute('data-ua-candidate-pk') || '';
        if (!userPk) {
            return;
        }

        uaSelectCredentialSearchCandidate(fieldKey, userPk);
    }

    function uaMoveCredentialActive(fieldKey, step) {
        var currentIndex = credentialLookupState.activeIndex[fieldKey];
        if (typeof currentIndex !== 'number') {
            currentIndex = -1;
        }

        uaSetCredentialActiveOption(fieldKey, currentIndex + step, true);
    }

    function uaBindCredentialSearchField(fieldKey) {
        var config = uaGetCredentialFieldConfig(fieldKey);
        if (!config || !config.input || !config.dropdown) {
            return;
        }

        config.input.addEventListener('focus', function () {
            uaOpenCredentialSearchDropdown(fieldKey);
        });

        config.input.addEventListener('click', function () {
            uaOpenCredentialSearchDropdown(fieldKey);
        });

        config.input.addEventListener('input', function () {
            uaOpenCredentialSearchDropdown(fieldKey);
        });

        config.input.addEventListener('blur', function () {
            setTimeout(function () {
                if (document.activeElement && document.activeElement.closest && document.activeElement.closest('.ua-credential-search-wrap')) {
                    return;
                }

                if (config.dropdown.matches(':hover')) {
                    return;
                }

                uaCloseCredentialSearchDropdowns();
            }, 120);
        });

        config.input.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();

                if (!config.dropdown.classList.contains('is-open')) {
                    uaOpenCredentialSearchDropdown(fieldKey);
                    return;
                }

                uaMoveCredentialActive(fieldKey, 1);
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();

                if (!config.dropdown.classList.contains('is-open')) {
                    uaOpenCredentialSearchDropdown(fieldKey);
                    return;
                }

                uaMoveCredentialActive(fieldKey, -1);
                return;
            }

            if (event.key === 'Enter') {
                event.preventDefault();

                if (!config.dropdown.classList.contains('is-open')) {
                    uaOpenCredentialSearchDropdown(fieldKey);
                    return;
                }

                uaSelectActiveCredentialOption(fieldKey);
                return;
            }

            if (event.key === 'Escape') {
                uaCloseCredentialSearchDropdowns();
            }
        });

        config.dropdown.addEventListener('mouseover', function (event) {
            var option = event.target.closest('[data-ua-option-index]');
            if (!option) {
                return;
            }

            var optionIndex = parseInt(option.getAttribute('data-ua-option-index'), 10);
            if (isNaN(optionIndex)) {
                return;
            }

            uaSetCredentialActiveOption(fieldKey, optionIndex, false);
        });

        config.dropdown.addEventListener('click', function (event) {
            var option = event.target.closest('[data-ua-candidate-pk]');
            if (!option) {
                return;
            }

            event.preventDefault();
            uaSelectCredentialSearchCandidate(fieldKey, option.getAttribute('data-ua-candidate-pk'));
        });
    }

    function uaOpenDeleteModal(userPk) {
        if (!els.deleteModal) {
            return;
        }

        var user = state.rows.find(function (row) {
            return String(row.pk) === String(userPk);
        }) || null;

        if (!user) {
            return;
        }

        state.pendingDeleteUser = user;
        if (els.deleteModalText) {
            els.deleteModalText.textContent = 'Are you sure you want to delete account for ' + (user.fullName || user.userId) + '?';
        }

        els.deleteModal.classList.remove('doclist-modal-hidden');
        els.deleteModal.setAttribute('aria-hidden', 'false');
    }

    function uaCloseDeleteModal() {
        if (!els.deleteModal) {
            return;
        }

        state.pendingDeleteUser = null;
        els.deleteModal.classList.add('doclist-modal-hidden');
        els.deleteModal.setAttribute('aria-hidden', 'true');
    }

    async function uaConfirmDelete() {
        if (!state.pendingDeleteUser) {
            uaCloseDeleteModal();
            return;
        }

        try {
            await uaApiRequest(uaBuildDeleteUrl(state.pendingDeleteUser.pk), 'DELETE');
            if (state.selectedUser && String(state.selectedUser.pk) === String(state.pendingDeleteUser.pk)) {
                uaClearForm();
            }

            uaCloseDeleteModal();
            await uaFetchRows(state.currentPage);
        } catch (error) {
            alert(error.message || 'Unable to delete user account.');
        }
    }

    function uaPopulateRoleSelectOptions() {
        if (!els.formRole) {
            return;
        }

        var previousValue = els.formRole.value;
        while (els.formRole.options.length > 0) {
            els.formRole.remove(0);
        }

        els.formRole.add(new Option('- No role assigned -', ''));

        (state.roles || []).forEach(function (role) {
            els.formRole.add(new Option(role.name, String(role.id)));
        });

        var hasPrevious = Array.prototype.some.call(els.formRole.options, function (option) {
            return option.value === previousValue;
        });
        els.formRole.value = hasPrevious ? previousValue : '';
    }

    function uaRenderRoleTable() {
        if (!els.roleTableBody) {
            return;
        }

        if (!state.roles.length) {
            els.roleTableBody.innerHTML = '<tr><td colspan="4" class="sc-empty-row">No roles defined yet.</td></tr>';
            return;
        }

        var html = state.roles.map(function (role) {
            var deleteBtn = role.isSystem
                ? ''
                : '<button type="button" class="apst-del-btn" data-ua-role-delete="' + uaEscapeHtml(role.id) + '">Delete</button>';

            return '' +
                '<tr data-ua-role-pk="' + uaEscapeHtml(role.id) + '">' +
                    '<td>' + uaEscapeHtml(role.name) + (role.isSystem ? ' <span class="ua-status-badge ua-status-active">System</span>' : '') + '</td>' +
                    '<td>' + uaEscapeHtml(role.description || '-') + '</td>' +
                    '<td>' + uaEscapeHtml(role.memberCount) + '</td>' +
                    '<td class="ua-col-action-cell">' +
                        '<button type="button" class="req-btn-cancel" data-ua-role-permissions="' + uaEscapeHtml(role.id) + '" style="margin-right:6px;">Permissions</button>' +
                        '<button type="button" class="req-btn-cancel" data-ua-role-edit="' + uaEscapeHtml(role.id) + '" style="margin-right:6px;">Rename</button>' +
                        deleteBtn +
                    '</td>' +
                '</tr>';
        }).join('');

        els.roleTableBody.innerHTML = html;
    }

    async function uaFetchRoles() {
        if (!roleDataEndpoint) {
            return;
        }

        try {
            var json = await uaApiRequest(roleDataEndpoint, 'GET', null);
            state.roles = Array.isArray(json.roles) ? json.roles : [];
            uaRenderRoleTable();
            uaPopulateRoleSelectOptions();
        } catch (error) {
            if (els.roleTableBody) {
                els.roleTableBody.innerHTML = '<tr><td colspan="4" class="sc-empty-row">Unable to load roles.</td></tr>';
            }
        }
    }

    function uaOpenRoleModal(roleId) {
        if (!els.roleModal) {
            return;
        }

        var role = roleId ? (state.roles || []).find(function (item) { return String(item.id) === String(roleId); }) : null;

        state.editingRoleId = role ? role.id : null;
        if (els.roleModalTitle) {
            els.roleModalTitle.textContent = role ? 'RENAME ROLE' : 'ADD ROLE';
        }
        if (els.roleFormName) {
            els.roleFormName.value = role ? role.name : '';
        }
        if (els.roleFormDescription) {
            els.roleFormDescription.value = role ? role.description : '';
        }

        els.roleModal.classList.remove('doclist-modal-hidden');
        els.roleModal.setAttribute('aria-hidden', 'false');
    }

    function uaCloseRoleModal() {
        if (!els.roleModal) {
            return;
        }

        state.editingRoleId = null;
        els.roleModal.classList.add('doclist-modal-hidden');
        els.roleModal.setAttribute('aria-hidden', 'true');
    }

    async function uaSaveRoleModal() {
        var name = (els.roleFormName ? els.roleFormName.value : '').trim();
        if (!name) {
            alert('Please enter a role name.');
            return;
        }

        var payload = {
            name: name,
            description: (els.roleFormDescription ? els.roleFormDescription.value : '').trim(),
        };

        try {
            if (state.editingRoleId) {
                await uaApiRequest(uaBuildRoleUpdateUrl(state.editingRoleId), 'PUT', payload);
            } else {
                await uaApiRequest(roleStoreEndpoint, 'POST', payload);
            }

            uaCloseRoleModal();
            await uaFetchRoles();
        } catch (error) {
            alert(error.message || 'Unable to save role.');
        }
    }

    function uaOpenRoleDeleteModal(roleId) {
        if (!els.roleDeleteModal) {
            return;
        }

        var role = (state.roles || []).find(function (item) { return String(item.id) === String(roleId); }) || null;
        if (!role) {
            return;
        }

        state.pendingDeleteRole = role;
        if (els.roleDeleteModalText) {
            els.roleDeleteModalText.textContent = 'Are you sure you want to delete the "' + role.name + '" role?';
        }

        els.roleDeleteModal.classList.remove('doclist-modal-hidden');
        els.roleDeleteModal.setAttribute('aria-hidden', 'false');
    }

    function uaCloseRoleDeleteModal() {
        if (!els.roleDeleteModal) {
            return;
        }

        state.pendingDeleteRole = null;
        els.roleDeleteModal.classList.add('doclist-modal-hidden');
        els.roleDeleteModal.setAttribute('aria-hidden', 'true');
    }

    async function uaConfirmRoleDelete() {
        if (!state.pendingDeleteRole) {
            uaCloseRoleDeleteModal();
            return;
        }

        try {
            await uaApiRequest(uaBuildRoleDeleteUrl(state.pendingDeleteRole.id), 'DELETE');
            uaCloseRoleDeleteModal();
            await uaFetchRoles();
        } catch (error) {
            alert(error.message || 'Unable to delete role.');
        }
    }

    function uaCloneAccessMatrix(matrix) {
        var cloned = {};

        Object.keys(matrix || {}).forEach(function (moduleCode) {
            cloned[moduleCode] = Object.assign({}, matrix[moduleCode] || {});
        });

        return cloned;
    }

    function uaBuildAccessMatrixFromModules(modules) {
        var matrix = {};

        (modules || []).forEach(function (module) {
            var moduleCode = String(module && module.code ? module.code : '');
            if (!moduleCode) {
                return;
            }

            matrix[moduleCode] = {};

            var actions = module && module.actions && typeof module.actions === 'object' ? module.actions : {};
            Object.keys(actions).forEach(function (permissionCode) {
                matrix[moduleCode][permissionCode] = !!actions[permissionCode];
            });
        });

        return matrix;
    }

    function uaEnsureAccessMatrix(modules, permissionTypes, matrix) {
        var normalized = {};
        var source = matrix || {};

        (modules || []).forEach(function (module) {
            var moduleCode = String(module && module.code ? module.code : '');
            var persistCode = String(module && module.persistCode ? module.persistCode : moduleCode);
            if (!moduleCode) {
                return;
            }

            normalized[moduleCode] = {};

            (permissionTypes || []).forEach(function (permissionType) {
                var permissionCode = String(permissionType && permissionType.code ? permissionType.code : '');
                if (!permissionCode) {
                    return;
                }

                normalized[moduleCode][permissionCode] = !!(
                    source[moduleCode] && Object.prototype.hasOwnProperty.call(source[moduleCode], permissionCode)
                        ? source[moduleCode][permissionCode]
                        : (source[persistCode] && Object.prototype.hasOwnProperty.call(source[persistCode], permissionCode)
                            ? source[persistCode][permissionCode]
                            : false)
                );
            });
        });

        return normalized;
    }

    function uaSetAccessFootnote(sourceMode) {
        if (!els.accessFootnote) {
            return;
        }

        if (sourceMode === 'role-default') {
            els.accessFootnote.textContent = 'Role defaults are currently displayed. Save to persist explicit per-user overrides.';
            return;
        }

        els.accessFootnote.textContent = 'UI-only preview for now. Access values are kept in-memory until backend mapping is wired.';
    }

    function uaResolvePermissionCode(moduleCode, requestedPermission) {
        var moduleMatrix = state.accessControl.matrix[moduleCode] || {};
        if (requestedPermission && Object.prototype.hasOwnProperty.call(moduleMatrix, requestedPermission)) {
            return requestedPermission;
        }

        if (Object.prototype.hasOwnProperty.call(moduleMatrix, 'edit')) {
            return 'edit';
        }

        if (Object.prototype.hasOwnProperty.call(moduleMatrix, 'view')) {
            return 'view';
        }

        var available = Object.keys(moduleMatrix);
        return available.length ? available[0] : '';
    }

    function uaModuleMatchesKeywords(module, keywords) {
        if (!module || !Array.isArray(keywords) || !keywords.length) {
            return false;
        }

        var source = uaNormalize((module.name || '') + ' ' + (module.code || ''));
        return keywords.some(function (keyword) {
            return source.indexOf(uaNormalize(keyword)) !== -1;
        });
    }

    function uaGetQuickOptionChecked(definition) {
        var matched = (state.accessControl.modules || []).filter(function (module) {
            return uaModuleMatchesKeywords(module, definition.keywords || []);
        });

        if (!matched.length) {
            return !!state.accessControl.quickOptionFallback[definition.key];
        }

        return matched.some(function (module) {
            var moduleCode = String(module.code || '');
            var permissionCode = uaResolvePermissionCode(moduleCode, definition.permission || 'edit');
            return !!(
                permissionCode
                && state.accessControl.matrix[moduleCode]
                && state.accessControl.matrix[moduleCode][permissionCode]
            );
        });
    }

    function uaApplyQuickOptionChange(optionKey, checked) {
        var definition = quickAccessDefinitions.find(function (item) {
            return item.key === optionKey;
        });

        if (!definition) {
            return;
        }

        var matched = (state.accessControl.modules || []).filter(function (module) {
            return uaModuleMatchesKeywords(module, definition.keywords || []);
        });

        if (!matched.length) {
            state.accessControl.quickOptionFallback[definition.key] = !!checked;
            return;
        }

        matched.forEach(function (module) {
            var moduleCode = String(module.code || '');
            if (!moduleCode || !state.accessControl.matrix[moduleCode]) {
                return;
            }

            var permissionCode = uaResolvePermissionCode(moduleCode, definition.permission || 'edit');
            if (!permissionCode) {
                return;
            }

            state.accessControl.matrix[moduleCode][permissionCode] = !!checked;

            if (permissionCode !== 'view'
                && Object.prototype.hasOwnProperty.call(state.accessControl.matrix[moduleCode], 'view')
                && checked) {
                state.accessControl.matrix[moduleCode].view = true;
            }
        });

        uaSyncAccessCheckboxes();
        uaRenderQuickAccessOptions();
    }

    function uaSyncAccessCheckboxes() {
        var table = document.querySelector('.ua-access-table');
        if (!table) return;

        var checkboxes = table.querySelectorAll('.req-checkbox-input');
        checkboxes.forEach(function(checkbox) {
            var moduleCode = checkbox.getAttribute('data-ua-ac-module');
            var permissionCode = checkbox.getAttribute('data-ua-ac-permission');
            if (moduleCode && permissionCode && state.accessControl.matrix[moduleCode]) {
                var isChecked = !!state.accessControl.matrix[moduleCode][permissionCode];
                if (checkbox.checked !== isChecked) {
                    checkbox.checked = isChecked;
                }
            }
        });
    }

    function uaShowSuccessToast(message) {
        var existing = document.querySelector('.ua-success-toast-wrap');
        if (existing && existing.parentNode) existing.parentNode.removeChild(existing);

        var modalId = 'ua-success-toast-' + Date.now();
        var html = '<div id="' + modalId + '" class="ua-success-toast-wrap" style="position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 9999; display: flex; align-items: center; background: #ffffff; border-radius: 12px; padding: 14px 24px; box-shadow: 0 12px 40px rgba(7, 46, 26, 0.15); border-left: 6px solid #1d8f4f; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); opacity: 0; margin-top: -30px;">' +
                   '<div style="margin-right: 14px; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: #eef8f2; border-radius: 50%; color: #1d8f4f;"><i class="fas fa-check" style="font-size: 1.1rem;"></i></div>' +
                   '<div style="color: #113825; font-weight: 700; font-size: 1rem; letter-spacing: -0.01em;">' + message + '</div>' +
                   '</div>';
        
        document.body.insertAdjacentHTML('beforeend', html);
        var el = document.getElementById(modalId);
        
        requestAnimationFrame(function() {
            el.style.opacity = '1';
            el.style.marginTop = '0';
        });
        
        setTimeout(function() {
            if (!el) return;
            el.style.opacity = '0';
            el.style.marginTop = '-30px';
            setTimeout(function() {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, 400);
        }, 3200);
    }

    function uaRenderQuickAccessOptions() {
        if (!els.quickAccessList) {
            return;
        }

        var html = quickAccessDefinitions.map(function (definition) {
            var checked = uaGetQuickOptionChecked(definition);

            return ''
                + '<label class="ua-quick-access-item">'
                + '<span class="ua-quick-access-label">' + uaEscapeHtml(definition.label) + '</span>'
                + '<span class="ua-quick-access-switch">'
                + '<input type="checkbox" class="req-checkbox-input" data-ua-quick-option="' + uaEscapeHtml(definition.key) + '" ' + (checked ? 'checked' : '') + '>'
                + '<span class="ua-quick-access-slider" aria-hidden="true"></span>'
                + '</span>'
                + '</label>';
        }).join('');

        els.quickAccessList.innerHTML = html;
    }

    function uaSetQuickAccessVisibility(isOpen) {
        if (!els.quickAccessToggle || !els.quickAccessList) {
            return;
        }

        var expanded = !!isOpen;
        els.quickAccessToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        els.quickAccessToggle.classList.toggle('is-open', expanded);
        els.quickAccessList.hidden = !expanded;
    }

    function uaBuildAccessTreeIndex(modules) {
        var byParent = {};
        var ids = {};
        var flat = [];

        function flatten(list) {
            (list || []).forEach(function(m) {
                flat.push(m);
                if (m && m.id !== null && typeof m.id !== 'undefined') {
                    ids[String(m.id)] = true;
                }
                if (Array.isArray(m.children)) {
                    flatten(m.children);
                }
            });
        }
        flatten(modules);

        flat.forEach(function (module) {
            var parentKey = '';
            if (module && module.parentId !== null && typeof module.parentId !== 'undefined') {
                var candidate = String(module.parentId);
                if (ids[candidate]) {
                    parentKey = candidate;
                }
            }

            if (!byParent[parentKey]) {
                byParent[parentKey] = [];
            }
            byParent[parentKey].push(module);
        });

        return byParent;
    }

    function uaPopulateAccessCopyOptions() {
        if (!els.accessCopySelect) {
            return;
        }

        var previousValue = els.accessCopySelect.value;
        while (els.accessCopySelect.options.length > 0) {
            els.accessCopySelect.remove(0);
        }

        els.accessCopySelect.add(new Option('- select user -', ''));

        var targetUserId = state.accessControl.targetUserId ? String(state.accessControl.targetUserId) : '';
        var users = (state.rows || [])
            .filter(function (row) {
                return String(row.pk) !== targetUserId;
            })
            .sort(function (a, b) {
                var nameA = uaNormalize(a.fullName || a.userId);
                var nameB = uaNormalize(b.fullName || b.userId);

                if (nameA === nameB) {
                    var idA = uaNormalize(a.userId);
                    var idB = uaNormalize(b.userId);
                    return idA < idB ? -1 : 1;
                }

                return nameA < nameB ? -1 : 1;
            });

        users.forEach(function (user) {
            var label = (user.fullName || user.userId) + ' (' + (user.userType || 'User') + ')';
            els.accessCopySelect.add(new Option(label, String(user.pk)));
        });

        var hasPrevious = Array.prototype.some.call(els.accessCopySelect.options, function (option) {
            return option.value === previousValue;
        });

        els.accessCopySelect.value = hasPrevious ? previousValue : '';
    }

    function uaRenderAccessControlTable() {
        if (!els.accessTableHead || !els.accessTableBody) {
            return;
        }

        var permissionTypes = state.accessControl.permissionTypes || [];
        var modules = state.accessControl.modules || [];

        if (!permissionTypes.length || !modules.length) {
            els.accessTableHead.innerHTML = '<tr><th>Module / Permission</th><th>View</th><th>Edit</th></tr>';
            els.accessTableBody.innerHTML = '<tr><td colspan="3" class="sc-empty-row">Access-control schema is not available yet.</td></tr>';
            return;
        }

        var headColumns = permissionTypes.map(function (permissionType) {
            return '<th class="ua-access-rw-col">' + uaEscapeHtml(permissionType.label || permissionType.code) + '</th>';
        }).join('');

        els.accessTableHead.innerHTML = '<tr><th>Module / Permission</th>' + headColumns + '</tr>';

        var treeIndex = uaBuildAccessTreeIndex(modules);

        function renderRows(parentKey, depth) {
            var rows = treeIndex[parentKey] || [];

            if (parentKey === '') {
                rows = rows.slice().sort(function (a, b) {
                    var rankA = uaDesiredRootOrder(a && (a.code || a.name) || '');
                    var rankB = uaDesiredRootOrder(b && (b.code || b.name) || '');
                    if (rankA !== rankB) {
                        return rankA - rankB;
                    }

                    var nameA = uaNormalize(a && (a.name || a.code) || '');
                    var nameB = uaNormalize(b && (b.name || b.code) || '');
                    return nameA < nameB ? -1 : (nameA > nameB ? 1 : 0);
                });
            }

            return rows.map(function (module) {
                var moduleCode = String(module && module.code ? module.code : '');
                var moduleId = module && module.id !== null && typeof module.id !== 'undefined'
                    ? String(module.id)
                    : '__' + moduleCode;
                var childRows = treeIndex[moduleId] || [];
                var hasChildren = childRows.length > 0;
                var expanded = hasChildren && !!state.accessControl.expandedModules[moduleCode];

                var cells = permissionTypes.map(function (permissionType) {
                    var permissionCode = String(permissionType && permissionType.code ? permissionType.code : '');
                    var checked = !!(
                        state.accessControl.matrix[moduleCode]
                        && Object.prototype.hasOwnProperty.call(state.accessControl.matrix[moduleCode], permissionCode)
                        && state.accessControl.matrix[moduleCode][permissionCode]
                    );

                    return ''
                        + '<td class="ua-access-rw-col">'
                        + '<label class="ua-access-switch">'
                        + '<input type="checkbox" class="req-checkbox-input" data-ua-ac-module="' + uaEscapeHtml(moduleCode) + '" data-ua-ac-permission="' + uaEscapeHtml(permissionCode) + '" ' + (checked ? 'checked' : '') + '>'
                        + '<span class="ua-access-switch-track" aria-hidden="true"></span>'
                        + '</label>'
                        + '</td>';
                }).join('');

                var labelHtml = ''
                    + '<div class="ua-access-module-label ua-access-depth-' + Math.min(depth, 5) + '">'
                    + (hasChildren
                        ? ''
                            + '<button type="button" class="ua-module-toggle" data-ua-module-toggle="' + uaEscapeHtml(moduleCode) + '" aria-expanded="' + (expanded ? 'true' : 'false') + '">'
                            + '<svg class="ua-module-caret-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"></path></svg>'
                            + '</button>'
                            + '<svg class="ua-module-folder-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#879b93" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>'
                        : '')
                    + '<span class="ua-module-name">' + uaEscapeHtml(module.name || moduleCode) + '</span>'
                    + '</div>';

                var rowHtml = ''
                    + '<tr class="ua-access-module-row' + (hasChildren ? ' ua-access-parent-row' : ' ua-access-child-row') + '">'
                    + '<td>' + labelHtml + '</td>'
                    + cells
                    + '</tr>';

                if (hasChildren && expanded) {
                    rowHtml += renderRows(moduleId, depth + 1);
                }

                return rowHtml;
            }).join('');
        }

        var bodyRows = renderRows('', 0);

        els.accessTableBody.innerHTML = bodyRows;
    }

    function uaOpenAccessModalShell() {
        if (!els.accessModal) {
            return;
        }

        els.accessModal.classList.remove('doclist-modal-hidden');
        els.accessModal.setAttribute('aria-hidden', 'false');
    }

    function uaCloseAccessModal() {
        if (!els.accessModal) {
            return;
        }

        state.accessControl.targetKind = 'user';
        state.accessControl.targetId = null;
        state.accessControl.targetUserId = null;
        state.accessControl.targetUserLabel = '';
        uaSetQuickAccessVisibility(false);

        els.accessModal.classList.add('doclist-modal-hidden');
        els.accessModal.setAttribute('aria-hidden', 'true');
    }

    async function uaFetchAccessControlPayload(userId) {
        var numericId = parseInt(userId, 10);
        if (!numericId) {
            return null;
        }

        var json = await uaApiRequest(uaBuildAccessShowUrl(numericId), 'GET', null);
        return json && json.data ? json.data : null;
    }

    async function uaPrimeAccessControlMetadata() {
        if (!accessControlModulesEndpoint) {
            return;
        }

        try {
            var json = await uaApiRequest(accessControlModulesEndpoint, 'GET', null);
            if (!json || json.ok === false) {
                return;
            }

            if (Array.isArray(json.permissionTypes) && !state.accessControl.permissionTypes.length) {
                state.accessControl.permissionTypes = json.permissionTypes;
            }

            if (Array.isArray(json.modules) && !state.accessControl.modules.length) {
                var baseModules = json.modules.map(function (module) {
                    return {
                        id: module.id,
                        code: module.code,
                        name: module.name,
                        parentId: module.parentId,
                        actions: {},
                    };
                });

                state.accessControl.persistableModuleCodes = baseModules.map(function (module) {
                    return String(module.code || '');
                }).filter(function (code) {
                    return !!code;
                });

                state.accessControl.modules = uaBuildDisplayModules(baseModules);

                state.accessControl.matrix = uaEnsureAccessMatrix(
                    state.accessControl.modules,
                    state.accessControl.permissionTypes,
                    {}
                );
            }
        } catch (error) {
            // Metadata warm-up is optional.
        }
    }

    async function uaOpenAccessModal(userPk) {
        var user = state.rows.find(function (row) {
            return String(row.pk) === String(userPk);
        }) || null;

        if (!user && state.selectedUser && String(state.selectedUser.pk) === String(userPk)) {
            user = state.selectedUser;
        }

        if (!user) {
            alert('Unable to open access control for the selected user.');
            return;
        }

        var label = (user.fullName || user.userId) + ' (' + (user.userType || 'User') + ')';
        await uaOpenAccessModalForTarget('user', user.pk, label);
    }

    async function uaOpenAccessModalForRole(rolePk) {
        var role = (state.roles || []).find(function (item) { return String(item.id) === String(rolePk); }) || null;
        if (!role) {
            alert('Unable to open permissions for the selected role.');
            return;
        }

        await uaOpenAccessModalForTarget('role', role.id, role.name + ' (Role)');
    }

    async function uaOpenAccessModalForTarget(kind, targetId, label) {
        if (!els.accessModal || !els.accessTableBody || !els.accessModalUserLabel) {
            return;
        }

        if (accessControlRequestState.controller) {
            accessControlRequestState.controller.abort();
        }

        accessControlRequestState.controller = new AbortController();
        accessControlRequestState.sequence += 1;
        var requestSequence = accessControlRequestState.sequence;

        state.accessControl.targetKind = kind;
        state.accessControl.targetId = targetId;
        state.accessControl.targetUserId = kind === 'user' ? targetId : null;
        state.accessControl.targetUserLabel = label;
        state.accessControl.quickOptionFallback = {};

        els.accessModalUserLabel.textContent = label;
        if (els.accessModalNote) {
            els.accessModalNote.textContent = kind === 'role'
                ? 'These permissions apply to every account assigned this role, unless a specific account has its own access override.'
                : 'These settings override the role defaults for this user only. Toggles are pre-filled with the role’s current permissions.';
        }
        if (els.accessCopyRow) {
            els.accessCopyRow.style.display = kind === 'role' ? 'none' : '';
        }

        uaSetAccessFootnote('explicit');
        uaSetQuickAccessVisibility(false);
        uaOpenAccessModalShell();
        els.accessTableBody.innerHTML = '<tr><td colspan="3" class="sc-empty-row">Loading access control...</td></tr>';

        var showUrl = kind === 'role' ? uaBuildRoleAccessShowUrl(targetId) : uaBuildAccessShowUrl(targetId);

        try {
            var json = await uaApiRequest(showUrl, 'GET', null, {
                signal: accessControlRequestState.controller.signal,
                allowAbort: true,
            });

            if (requestSequence !== accessControlRequestState.sequence) {
                return;
            }

            var payload = json && json.data ? json.data : null;
            if (!payload) {
                throw new Error('Unable to read access-control payload.');
            }

            state.accessControl.source = String(payload.source || 'explicit');
            state.accessControl.permissionTypes = Array.isArray(payload.permissionTypes) ? payload.permissionTypes : [];
            var payloadModules = Array.isArray(payload.modules) ? payload.modules : [];
            state.accessControl.persistableModuleCodes = payloadModules.map(function (module) {
                return String(module && module.code ? module.code : '');
            }).filter(function (code) {
                return !!code;
            });
            state.accessControl.modules = uaBuildDisplayModules(payloadModules);

            var matrix = uaBuildAccessMatrixFromModules(payloadModules);
            state.accessControl.matrix = uaEnsureAccessMatrix(
                state.accessControl.modules,
                state.accessControl.permissionTypes,
                matrix
            );

            state.accessControl.expandedModules = {};
            (state.accessControl.modules || []).forEach(function (module) {
                var moduleCode = String(module && module.code ? module.code : '');
                if (!moduleCode) {
                    return;
                }

                state.accessControl.expandedModules[moduleCode] = uaShouldDefaultExpandModule(moduleCode);
            });

            if (!state.accessControl.permissionTypes.length || !state.accessControl.modules.length) {
                throw new Error('Access-control schema is unavailable. Please run the access-control migration first.');
            }

            uaRenderAccessControlTable();
            uaRenderQuickAccessOptions();
            uaPopulateAccessCopyOptions();
            uaSetAccessFootnote(state.accessControl.source);
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }

            uaCloseAccessModal();
            alert(error.message || 'Unable to load access control.');
        } finally {
            if (requestSequence === accessControlRequestState.sequence) {
                accessControlRequestState.controller = null;
            }
        }
    }

    async function uaCopyAccessFromUser() {
        if (!els.accessCopySelect) {
            return;
        }

        var sourceUserId = parseInt(els.accessCopySelect.value, 10);
        if (!sourceUserId) {
            alert('Please select a user to copy access from.');
            return;
        }

        try {
            var payload = await uaFetchAccessControlPayload(sourceUserId);
            if (!payload) {
                throw new Error('Unable to load the source user access settings.');
            }

            var sourceModules = Array.isArray(payload.modules) ? payload.modules : [];
            var sourceMatrix = uaBuildAccessMatrixFromModules(sourceModules);

            state.accessControl.matrix = uaEnsureAccessMatrix(
                state.accessControl.modules,
                state.accessControl.permissionTypes,
                sourceMatrix
            );

            uaRenderAccessControlTable();
            uaRenderQuickAccessOptions();
            if (els.accessFootnote) {
                els.accessFootnote.textContent = 'Copied access settings from the selected user. Click Save Access to persist changes.';
            }
        } catch (error) {
            alert(error.message || 'Unable to copy access settings.');
        }
    }

    async function uaSaveAccessControl() {
        var targetId = parseInt(state.accessControl.targetId, 10);
        if (!targetId) {
            alert('Select a user or role before saving access control.');
            return;
        }

        var isRole = state.accessControl.targetKind === 'role';

        var matrix = uaCloneAccessMatrix(state.accessControl.matrix);
        var filteredMatrix = {};
        if (Array.isArray(state.accessControl.persistableModuleCodes) && state.accessControl.persistableModuleCodes.length) {
            state.accessControl.persistableModuleCodes.forEach(function (moduleCode) {
                if (matrix[moduleCode]) {
                    filteredMatrix[moduleCode] = matrix[moduleCode];
                }
            });
        } else {
            filteredMatrix = matrix;
        }

        try {
            var updateUrl = isRole ? uaBuildRoleAccessUpdateUrl(targetId) : uaBuildAccessUpdateUrl(targetId);
            var json = await uaApiRequest(updateUrl, 'PUT', {
                permissions: filteredMatrix,
            });

            var payload = json && json.data ? json.data : null;
            if (payload && Array.isArray(payload.modules) && Array.isArray(payload.permissionTypes)) {
                state.accessControl.source = String(payload.source || 'explicit');
                state.accessControl.permissionTypes = payload.permissionTypes;
                state.accessControl.persistableModuleCodes = payload.modules.map(function (module) {
                    return String(module && module.code ? module.code : '');
                }).filter(function (code) {
                    return !!code;
                });
                state.accessControl.modules = uaBuildDisplayModules(payload.modules);
                state.accessControl.matrix = uaEnsureAccessMatrix(
                    state.accessControl.modules,
                    state.accessControl.permissionTypes,
                    uaBuildAccessMatrixFromModules(payload.modules)
                );
            }

            uaCloseAccessModal();
            uaShowSuccessToast('Access control updated successfully.');
        } catch (error) {
            alert(error.message || 'Unable to save access control.');
        }
    }

    async function uaSaveSelected() {
        var isCreating = !state.selectedUser || !state.selectedUser.pk;

        var payload = {
            user_id: (els.formUserId ? els.formUserId.value : '').trim(),
            full_name: (els.formName ? els.formName.value : '').trim(),
            email: (els.formEmail ? els.formEmail.value : '').trim(),
            password: (els.formPassword ? els.formPassword.value : '').trim(),
            inactive: !!(els.inactive && els.inactive.checked),
            access_control_role_id: (els.formRole ? els.formRole.value : '').trim(),
            course_scope_ids: els.formCourseScope
                ? Array.prototype.map.call(els.formCourseScope.selectedOptions || [], function (option) {
                    return parseInt(option.value, 10);
                })
                : [],
        };

        if (!isCreating) {
            payload.user_type = (els.formUserType ? els.formUserType.value : '').trim();
        }

        if (!payload.user_id) {
            alert('Please enter a User ID.');
            return;
        }

        if (isCreating && !payload.password) {
            alert('Please enter a password for the new account.');
            return;
        }

        try {
            var json = isCreating
                ? await uaApiRequest(storeEndpoint, 'POST', payload)
                : await uaApiRequest(uaBuildUpdateUrl(state.selectedUser.pk), 'PUT', payload);

            if (json.row) {
                state.selectedUser = json.row;
                uaFillForm(json.row);
            }
            await uaFetchRows(state.currentPage);
            await uaFetchRoles();
            alert(isCreating ? 'Account created.' : 'Account credentials updated.');
        } catch (error) {
            alert(error.message || 'Unable to save account credentials.');
        }
    }

    function uaResetFilters() {
        if (els.studentId) {
            els.studentId.value = '';
        }
        if (els.lastName) {
            els.lastName.value = '';
        }
        if (els.firstName) {
            els.firstName.value = '';
        }
        if (els.userType) {
            els.userType.value = '';
        }
    }

    function uaWireEvents() {
        if (els.searchBtn) {
            els.searchBtn.addEventListener('click', function () {
                uaFetchRows(1);
            });
        }

        if (els.clearBtn) {
            els.clearBtn.addEventListener('click', function () {
                uaResetFilters();
                uaFetchRows(1);
            });
        }

        ['uaStudentId', 'uaLastName', 'uaFirstName'].forEach(function (id) {
            var input = document.getElementById(id);
            if (!input) {
                return;
            }

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    uaFetchRows(1);
                }
            });

            input.addEventListener('input', function () {
                if (inputDebounceTimers[id]) {
                    clearTimeout(inputDebounceTimers[id]);
                }
                inputDebounceTimers[id] = setTimeout(function () {
                    uaFetchRows(1);
                }, 240);
            });
        });

        if (els.userType) {
            els.userType.addEventListener('change', function () {
                uaFetchRows(1);
            });
        }

        if (els.pager) {
            els.pager.addEventListener('click', function (event) {
                var pageBtn = event.target.closest('[data-ua-page]');
                if (pageBtn) {
                    var nextPage = parseInt(pageBtn.getAttribute('data-ua-page'), 10) || 1;
                    uaFetchRows(nextPage);
                    return;
                }

                var prevBtn = event.target.closest('[data-ua-page-prev]');
                if (prevBtn && state.currentPage > 1) {
                    uaFetchRows(state.currentPage - 1);
                    return;
                }

                var nextBtn = event.target.closest('[data-ua-page-next]');
                if (nextBtn && state.currentPage < state.lastPage) {
                    uaFetchRows(state.currentPage + 1);
                }
            });
        }

        if (els.tableBody) {
            els.tableBody.addEventListener('click', function (event) {
                var menuToggleBtn = event.target.closest('[data-ua-menu-toggle]');
                if (menuToggleBtn) {
                    event.preventDefault();
                    event.stopPropagation();
                    uaToggleRowActionMenu(menuToggleBtn.getAttribute('data-ua-menu-toggle'));
                    return;
                }

                var accessBtn = event.target.closest('[data-ua-access-user-pk]');
                if (accessBtn) {
                    event.preventDefault();
                    event.stopPropagation();
                    uaCloseRowActionMenus();
                    uaOpenAccessModal(accessBtn.getAttribute('data-ua-access-user-pk'));
                    return;
                }

                var deleteBtn = event.target.closest('[data-ua-delete-user-pk]');
                if (deleteBtn) {
                    event.preventDefault();
                    event.stopPropagation();
                    uaCloseRowActionMenus();
                    uaOpenDeleteModal(deleteBtn.getAttribute('data-ua-delete-user-pk'));
                    return;
                }

                var row = event.target.closest('tr[data-ua-user-pk]');
                if (!row) {
                    return;
                }

                var userPk = row.getAttribute('data-ua-user-pk');
                var user = state.rows.find(function (item) {
                    return String(item.pk) === String(userPk);
                }) || null;

                if (user) {
                    uaCloseRowActionMenus();
                    uaFillForm(user);
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.ua-row-action-menu-wrap')) {
                uaCloseRowActionMenus();
            }
        });

        if (els.saveBtn) {
            els.saveBtn.addEventListener('click', function () {
                uaSaveSelected();
            });
        }

        if (els.cancelBtn) {
            els.cancelBtn.addEventListener('click', function () {
                if (state.selectedUser) {
                    uaFillForm(state.selectedUser);
                } else {
                    uaClearForm();
                }
            });
        }

        if (els.newAccountBtn) {
            els.newAccountBtn.addEventListener('click', function () {
                uaCloseRowActionMenus();
                uaClearForm();
                if (els.formPassword) {
                    els.formPassword.readOnly = false;
                    els.formPassword.focus();
                }
            });
        }

        if (els.newRoleBtn) {
            els.newRoleBtn.addEventListener('click', function () {
                uaOpenRoleModal(null);
            });
        }

        if (els.roleTableBody) {
            els.roleTableBody.addEventListener('click', function (event) {
                var permissionsBtn = event.target.closest('[data-ua-role-permissions]');
                if (permissionsBtn) {
                    uaOpenAccessModalForRole(permissionsBtn.getAttribute('data-ua-role-permissions'));
                    return;
                }

                var editBtn = event.target.closest('[data-ua-role-edit]');
                if (editBtn) {
                    uaOpenRoleModal(editBtn.getAttribute('data-ua-role-edit'));
                    return;
                }

                var deleteBtn = event.target.closest('[data-ua-role-delete]');
                if (deleteBtn) {
                    uaOpenRoleDeleteModal(deleteBtn.getAttribute('data-ua-role-delete'));
                }
            });
        }

        if (els.roleModalCancelBtn) {
            els.roleModalCancelBtn.addEventListener('click', uaCloseRoleModal);
        }

        if (els.roleModalSaveBtn) {
            els.roleModalSaveBtn.addEventListener('click', function () {
                uaSaveRoleModal();
            });
        }

        if (els.roleModal) {
            els.roleModal.addEventListener('click', function (event) {
                if (event.target === els.roleModal) {
                    uaCloseRoleModal();
                }
            });
        }

        if (els.roleDeleteCancelBtn) {
            els.roleDeleteCancelBtn.addEventListener('click', uaCloseRoleDeleteModal);
        }

        if (els.roleDeleteConfirmBtn) {
            els.roleDeleteConfirmBtn.addEventListener('click', uaConfirmRoleDelete);
        }

        if (els.roleDeleteModal) {
            els.roleDeleteModal.addEventListener('click', function (event) {
                if (event.target === els.roleDeleteModal) {
                    uaCloseRoleDeleteModal();
                }
            });
        }

        uaBindCredentialSearchField('userId');
        uaBindCredentialSearchField('name');

        if (els.formPassword) {
            els.formPassword.addEventListener('focus', function () {
                els.formPassword.readOnly = false;
            });
        }

        if (els.passwordToggle && els.formPassword) {
            els.passwordToggle.addEventListener('click', function () {
                els.formPassword.readOnly = false;
                var hidden = els.formPassword.type === 'password';
                els.formPassword.type = hidden ? 'text' : 'password';
                els.passwordToggle.classList.toggle('is-visible', hidden);
                els.passwordToggle.setAttribute('aria-label', hidden ? 'Hide password' : 'Show password');
            });
        }

        if (els.deleteCancelBtn) {
            els.deleteCancelBtn.addEventListener('click', uaCloseDeleteModal);
        }

        if (els.deleteConfirmBtn) {
            els.deleteConfirmBtn.addEventListener('click', uaConfirmDelete);
        }

        if (els.deleteModal) {
            els.deleteModal.addEventListener('click', function (event) {
                if (event.target === els.deleteModal) {
                    uaCloseDeleteModal();
                }
            });
        }

        if (els.accessTableBody) {
            els.accessTableBody.addEventListener('click', function (event) {
                var toggleBtn = event.target.closest('[data-ua-module-toggle]');
                if (!toggleBtn) {
                    return;
                }

                event.preventDefault();
                var moduleCode = String(toggleBtn.getAttribute('data-ua-module-toggle') || '');
                if (!moduleCode) {
                    return;
                }

                state.accessControl.expandedModules[moduleCode] = !state.accessControl.expandedModules[moduleCode];
                uaRenderAccessControlTable();
            });

            els.accessTableBody.addEventListener('change', function (event) {
                var checkbox = event.target.closest('[data-ua-ac-module][data-ua-ac-permission]');
                if (!checkbox) {
                    return;
                }

                var moduleCode = checkbox.getAttribute('data-ua-ac-module') || '';
                var permissionCode = checkbox.getAttribute('data-ua-ac-permission') || '';
                if (!moduleCode || !permissionCode) {
                    return;
                }

                if (!state.accessControl.matrix[moduleCode]) {
                    state.accessControl.matrix[moduleCode] = {};
                }

                var isChecked = !!checkbox.checked;
                state.accessControl.matrix[moduleCode][permissionCode] = isChecked;

                // Sync self Edit/View dependency
                if (isChecked && permissionCode === 'edit') {
                    state.accessControl.matrix[moduleCode]['view'] = true;
                }
                if (!isChecked && permissionCode === 'view') {
                    state.accessControl.matrix[moduleCode]['edit'] = false;
                }

                // Cascade logic to auto-toggle all descendants
                var treeIndex = uaBuildAccessTreeIndex(state.accessControl.modules);
                
                // Cascade logic to auto-toggle all descendants
                var allModulesFlat = [];
                function findInHierarchy(list) {
                    for (var i = 0; i < list.length; i++) {
                        allModulesFlat.push(list[i]);
                        if (Array.isArray(list[i].children)) findInHierarchy(list[i].children);
                    }
                }
                findInHierarchy(state.accessControl.modules);

                var treeIndex = uaBuildAccessTreeIndex(state.accessControl.modules);
                var currentModule = allModulesFlat.find(function(m) { return m.code === moduleCode; });
                
                if (currentModule) {
                    var currentModuleId = currentModule.id !== null && typeof currentModule.id !== 'undefined'
                        ? String(currentModule.id)
                        : '__' + moduleCode;

                    function cascadeToDescendants(parentId) {
                        var children = treeIndex[parentId] || [];
                        children.forEach(function(child) {
                            var childCode = String(child.code || '');
                            if (childCode) {
                                if (!state.accessControl.matrix[childCode]) {
                                    state.accessControl.matrix[childCode] = {};
                                }
                                state.accessControl.matrix[childCode][permissionCode] = isChecked;
                                
                                // If we enable Edit, we MUST enable View
                                if (isChecked && permissionCode === 'edit') {
                                    state.accessControl.matrix[childCode]['view'] = true;
                                }
                                // If we disable View, we MUST disable Edit
                                if (!isChecked && permissionCode === 'view') {
                                    state.accessControl.matrix[childCode]['edit'] = false;
                                }
                            }
                            var childId = child.id !== null && typeof child.id !== 'undefined' ? String(child.id) : '__' + childCode;
                            cascadeToDescendants(childId);
                        });
                    }
                    
                    cascadeToDescendants(currentModuleId);
                    
                    // Sync checkboxes directly in DOM to keep transitions smooth
                    uaSyncAccessCheckboxes();
                }

                uaRenderQuickAccessOptions();
            });
        }

        if (els.quickAccessToggle) {
            els.quickAccessToggle.addEventListener('click', function () {
                var isOpen = els.quickAccessToggle.getAttribute('aria-expanded') === 'true';
                uaSetQuickAccessVisibility(!isOpen);
            });
        }

        if (els.quickAccessList) {
            els.quickAccessList.addEventListener('change', function (event) {
                var input = event.target.closest('[data-ua-quick-option]');
                if (!input) {
                    return;
                }

                uaApplyQuickOptionChange(input.getAttribute('data-ua-quick-option'), !!input.checked);
            });
        }

        if (els.accessCopyBtn) {
            els.accessCopyBtn.addEventListener('click', function () {
                uaCopyAccessFromUser();
            });
        }

        if (els.accessSaveBtn) {
            els.accessSaveBtn.addEventListener('click', function () {
                uaSaveAccessControl();
            });
        }

        if (els.accessCancelBtn) {
            els.accessCancelBtn.addEventListener('click', function () {
                uaCloseAccessModal();
            });
        }

        if (els.accessCloseX) {
            els.accessCloseX.addEventListener('click', function () {
                uaCloseAccessModal();
            });
        }

        if (els.accessModal) {
            els.accessModal.addEventListener('click', function (event) {
                if (event.target === els.accessModal) {
                    uaCloseAccessModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                uaCloseRowActionMenus();
                uaCloseCredentialSearchDropdowns();
                uaCloseDeleteModal();
                uaCloseAccessModal();
            }
        });
    }

    function uaPreventAutofillArtifacts() {
        uaResetFilters();
        uaCloseCredentialSearchDropdowns();
        if (els.formPassword) {
            els.formPassword.value = '';
            els.formPassword.readOnly = true;
            els.formPassword.type = 'password';
        }
    }

    uaPrimeAccessControlMetadata();
    uaWireEvents();
    uaPreventAutofillArtifacts();
    uaSetSelectedSummary(null);
    uaSetFormMode('create');
    uaFetchRows(1);
    uaFetchRoles();

    window.addEventListener('pageshow', function (event) {
        if (!event || !event.persisted) {
            return;
        }

        uaPreventAutofillArtifacts();
        uaFetchRows(1);
    });
})();
