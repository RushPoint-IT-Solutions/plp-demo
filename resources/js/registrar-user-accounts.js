(function () {
    var root = document.getElementById('uaPageRoot');
    if (!root) {
        return;
    }

    var dataEndpoint = root.getAttribute('data-data-endpoint') || '';
    var updateTemplate = root.getAttribute('data-update-template') || '';
    var deleteTemplate = root.getAttribute('data-delete-template') || '';
    var accessControlModulesEndpoint = root.getAttribute('data-access-modules-endpoint') || '';
    var accessControlShowTemplate = root.getAttribute('data-access-show-template') || '';
    var accessControlUpdateTemplate = root.getAttribute('data-access-update-template') || '';
    var csrfToken = root.getAttribute('data-csrf-token') || '';

    var state = {
        selectedUser: null,
        pendingDeleteUser: null,
        rows: [],
        currentPage: 1,
        lastPage: 1,
        perPage: 10,
        total: 0,
        from: 0,
        accessControl: {
            targetUserId: null,
            targetUserLabel: '',
            source: 'explicit',
            permissionTypes: [],
            modules: [],
            matrix: {},
        },
    };

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
        inactive: document.getElementById('uaInactive'),

        saveBtn: document.getElementById('uaSaveBtn'),
        cancelBtn: document.getElementById('uaCancelBtn'),
        passwordToggle: document.getElementById('uaPasswordToggle'),

        deleteModal: document.getElementById('uaDeleteModal'),
        deleteModalText: document.getElementById('uaDeleteModalText'),
        deleteCancelBtn: document.getElementById('uaDeleteCancelBtn'),
        deleteConfirmBtn: document.getElementById('uaDeleteConfirmBtn'),

        accessModal: document.getElementById('uaAccessModal'),
        accessModalUserLabel: document.getElementById('uaAccessModalUserLabel'),
        accessTableHead: document.getElementById('uaAccessTableHead'),
        accessTableBody: document.getElementById('uaAccessTableBody'),
        accessFootnote: document.getElementById('uaAccessFootnote'),
        accessCloseX: document.getElementById('uaAccessCloseX'),
        accessCancelBtn: document.getElementById('uaAccessCancelBtn'),
        accessSaveBtn: document.getElementById('uaAccessSaveBtn'),
        accessCopySelect: document.getElementById('uaCopyAccessFrom'),
        accessCopyBtn: document.getElementById('uaCopyAccessBtn'),
        accessPresetSelect: document.getElementById('uaAccessQuickPreset'),
        accessPresetApplyBtn: document.getElementById('uaApplyAccessPresetBtn'),
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
        if (els.inactive) {
            els.inactive.checked = false;
        }

        state.selectedUser = null;
        uaSetSelectedSummary(null);
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
        if (els.inactive) {
            els.inactive.checked = !!user.inactive;
        }

        uaSetSelectedSummary(user);
        uaHighlightSelectedRow();
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

    function uaRenderTable() {
        if (!els.tableBody) {
            return;
        }

        if (!state.rows.length) {
            els.tableBody.innerHTML = '<tr><td colspan="6" class="sc-empty-row">No user accounts found.</td></tr>';
            return;
        }

        var html = state.rows.map(function (user, index) {
            var rowNo = (state.from || 0) + index;
            return '' +
                '<tr data-ua-user-pk="' + uaEscapeHtml(user.pk) + '">' +
                    '<td>' + rowNo + '</td>' +
                    '<td>' + uaEscapeHtml(user.userId) + '</td>' +
                    '<td>' + uaEscapeHtml(user.fullName) + '</td>' +
                    '<td>' + uaEscapeHtml(user.userType) + '</td>' +
                    '<td>' + uaStatusBadge(user.inactive) + '</td>' +
                    '<td class="ua-col-action-cell">' +
                        '<button type="button" class="doclist-action-btn ua-access-btn" data-ua-access-user-pk="' + uaEscapeHtml(user.pk) + '" title="Access Control">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6l7-3z"></path><path d="M9 12l2 2 4-4"></path></svg>' +
                        '</button>' +
                        '<button type="button" class="doclist-action-btn doclist-delete-btn" data-ua-delete-user-pk="' + uaEscapeHtml(user.pk) + '" title="Delete">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>' +
                        '</button>' +
                    '</td>' +
                '</tr>';
        }).join('');

        els.tableBody.innerHTML = html;
        uaHighlightSelectedRow();
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

        els.tableBody.innerHTML = '<tr><td colspan="6" class="sc-empty-row">Loading user accounts...</td></tr>';
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
            state.perPage = parseInt(meta.perPage, 10) || 10;
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
                        : false
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
            els.accessFootnote.textContent = 'No explicit rows yet. You are seeing role-default permissions and can save overrides for this user.';
            return;
        }

        els.accessFootnote.textContent = 'Changes are saved per user and can be copied from another account before saving.';
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

        var bodyRows = modules.map(function (module) {
            var moduleCode = String(module.code || '');
            var cells = permissionTypes.map(function (permissionType) {
                var permissionCode = String(permissionType.code || '');
                var checked = !!(
                    state.accessControl.matrix[moduleCode]
                    && Object.prototype.hasOwnProperty.call(state.accessControl.matrix[moduleCode], permissionCode)
                    && state.accessControl.matrix[moduleCode][permissionCode]
                );

                return '' +
                    '<td class="ua-access-rw-col">' +
                        '<input type="checkbox" class="req-checkbox-input" data-ua-ac-module="' + uaEscapeHtml(moduleCode) + '" data-ua-ac-permission="' + uaEscapeHtml(permissionCode) + '" ' + (checked ? 'checked' : '') + '>' +
                    '</td>';
            }).join('');

            return '' +
                '<tr>' +
                    '<td>' + uaEscapeHtml(module.name || moduleCode) + '</td>' +
                    cells +
                '</tr>';
        }).join('');

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

        state.accessControl.targetUserId = null;
        state.accessControl.targetUserLabel = '';

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
                state.accessControl.modules = json.modules.map(function (module) {
                    return {
                        id: module.id,
                        code: module.code,
                        name: module.name,
                        parentId: module.parentId,
                        actions: {},
                    };
                });

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
        if (!els.accessModal || !els.accessTableBody || !els.accessModalUserLabel) {
            return;
        }

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

        if (accessControlRequestState.controller) {
            accessControlRequestState.controller.abort();
        }

        accessControlRequestState.controller = new AbortController();
        accessControlRequestState.sequence += 1;
        var requestSequence = accessControlRequestState.sequence;

        state.accessControl.targetUserId = user.pk;
        state.accessControl.targetUserLabel = (user.fullName || user.userId) + ' (' + (user.userType || 'User') + ')';

        els.accessModalUserLabel.textContent = state.accessControl.targetUserLabel;
        uaSetAccessFootnote('explicit');
        uaOpenAccessModalShell();
        els.accessTableBody.innerHTML = '<tr><td colspan="3" class="sc-empty-row">Loading access control...</td></tr>';

        try {
            var json = await uaApiRequest(uaBuildAccessShowUrl(user.pk), 'GET', null, {
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
            state.accessControl.modules = Array.isArray(payload.modules) ? payload.modules : [];

            var matrix = uaBuildAccessMatrixFromModules(state.accessControl.modules);
            state.accessControl.matrix = uaEnsureAccessMatrix(
                state.accessControl.modules,
                state.accessControl.permissionTypes,
                matrix
            );

            if (!state.accessControl.permissionTypes.length || !state.accessControl.modules.length) {
                throw new Error('Access-control schema is unavailable. Please run the access-control migration first.');
            }

            uaRenderAccessControlTable();
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

    function uaApplyAccessPreset() {
        if (!els.accessPresetSelect) {
            return;
        }

        var preset = String(els.accessPresetSelect.value || '');
        if (!preset) {
            alert('Please choose a quick-access option first.');
            return;
        }

        var permissionTypes = state.accessControl.permissionTypes || [];
        var modules = state.accessControl.modules || [];

        state.accessControl.matrix = uaEnsureAccessMatrix(modules, permissionTypes, state.accessControl.matrix);

        modules.forEach(function (module) {
            var moduleCode = String(module.code || '');
            if (!moduleCode || !state.accessControl.matrix[moduleCode]) {
                return;
            }

            permissionTypes.forEach(function (permissionType) {
                var permissionCode = String(permissionType.code || '');
                if (!permissionCode) {
                    return;
                }

                if (preset === 'full_access') {
                    state.accessControl.matrix[moduleCode][permissionCode] = true;
                    return;
                }

                if (preset === 'view_only') {
                    state.accessControl.matrix[moduleCode][permissionCode] = permissionCode === 'view';
                    return;
                }

                state.accessControl.matrix[moduleCode][permissionCode] = false;
            });
        });

        uaRenderAccessControlTable();
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
            if (els.accessFootnote) {
                els.accessFootnote.textContent = 'Copied access settings from the selected user. Click Save Access to persist changes.';
            }
        } catch (error) {
            alert(error.message || 'Unable to copy access settings.');
        }
    }

    async function uaSaveAccessControl() {
        var targetUserId = parseInt(state.accessControl.targetUserId, 10);
        if (!targetUserId) {
            alert('Select a user before saving access control.');
            return;
        }

        var matrix = uaCloneAccessMatrix(state.accessControl.matrix);

        try {
            var json = await uaApiRequest(uaBuildAccessUpdateUrl(targetUserId), 'PUT', {
                permissions: matrix,
            });

            var payload = json && json.data ? json.data : null;
            if (payload && Array.isArray(payload.modules) && Array.isArray(payload.permissionTypes)) {
                state.accessControl.source = String(payload.source || 'explicit');
                state.accessControl.permissionTypes = payload.permissionTypes;
                state.accessControl.modules = payload.modules;
                state.accessControl.matrix = uaEnsureAccessMatrix(
                    state.accessControl.modules,
                    state.accessControl.permissionTypes,
                    uaBuildAccessMatrixFromModules(payload.modules)
                );
            }

            uaCloseAccessModal();
            alert('Access control saved.');
        } catch (error) {
            alert(error.message || 'Unable to save access control.');
        }
    }

    async function uaSaveSelected() {
        if (!state.selectedUser || !state.selectedUser.pk) {
            alert('Please select a user account first.');
            return;
        }

        var payload = {
            user_id: (els.formUserId ? els.formUserId.value : '').trim(),
            full_name: (els.formName ? els.formName.value : '').trim(),
            email: (els.formEmail ? els.formEmail.value : '').trim(),
            password: (els.formPassword ? els.formPassword.value : '').trim(),
            inactive: !!(els.inactive && els.inactive.checked),
            user_type: (els.formUserType ? els.formUserType.value : '').trim(),
        };

        if (!payload.user_id) {
            alert('Please enter a User ID.');
            return;
        }

        try {
            var json = await uaApiRequest(uaBuildUpdateUrl(state.selectedUser.pk), 'PUT', payload);
            if (json.row) {
                state.selectedUser = json.row;
                uaFillForm(json.row);
            }
            await uaFetchRows(state.currentPage);
            alert('Account credentials updated.');
        } catch (error) {
            alert(error.message || 'Unable to update account credentials.');
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
                var accessBtn = event.target.closest('[data-ua-access-user-pk]');
                if (accessBtn) {
                    event.preventDefault();
                    event.stopPropagation();
                    uaOpenAccessModal(accessBtn.getAttribute('data-ua-access-user-pk'));
                    return;
                }

                var deleteBtn = event.target.closest('[data-ua-delete-user-pk]');
                if (deleteBtn) {
                    event.preventDefault();
                    event.stopPropagation();
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
                    uaFillForm(user);
                }
            });
        }

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

                state.accessControl.matrix[moduleCode][permissionCode] = !!checkbox.checked;
            });
        }

        if (els.accessCopyBtn) {
            els.accessCopyBtn.addEventListener('click', function () {
                uaCopyAccessFromUser();
            });
        }

        if (els.accessPresetApplyBtn) {
            els.accessPresetApplyBtn.addEventListener('click', function () {
                uaApplyAccessPreset();
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
    uaFetchRows(1);

    window.addEventListener('pageshow', function (event) {
        if (!event || !event.persisted) {
            return;
        }

        uaPreventAutofillArtifacts();
        uaFetchRows(1);
    });
})();
