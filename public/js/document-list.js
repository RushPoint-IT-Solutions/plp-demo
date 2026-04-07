(function () {
    var config = document.getElementById('doclistConfig');
    var tableBody = document.getElementById('doclistTableBody');

    if (!config || !tableBody) {
        return;
    }

    var EDIT_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
    var DELETE_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>';

    var state = {
        rows: []
    };

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    var storeUrl = config.getAttribute('data-store-url') || '';
    var updateUrlTemplate = config.getAttribute('data-update-url-template') || '';
    var deleteUrlTemplate = config.getAttribute('data-delete-url-template') || '';

    var searchInput = document.getElementById('doclistSearch');
    var sortSelect = document.getElementById('doclistSort');
    var addButton = document.getElementById('doclistAddBtn');

    var addModal = document.getElementById('addDocModal');
    var editModal = document.getElementById('editModal');
    var deleteModal = document.getElementById('deleteModal');

    var addForm = document.getElementById('addDocForm');
    var editForm = document.getElementById('editForm');
    var deleteForm = document.getElementById('deleteForm');

    var addCancelButton = document.getElementById('addDocCancelBtn');
    var editCancelButton = document.getElementById('editDocCancelBtn');
    var deleteCancelButton = document.getElementById('deleteDocCancelBtn');

    var addGradeLevel = document.getElementById('addGradeLevel');
    var addDocument = document.getElementById('addDocument');
    var addNonFilipino = document.getElementById('addNonFilipino');

    var editId = document.getElementById('editId');
    var editGradeLevel = document.getElementById('editGradeLevel');
    var editDocument = document.getElementById('editDocument');
    var editTypeMedical = document.getElementById('editTypeMedical');
    var editTypeDocument = document.getElementById('editTypeDocument');
    var editNonFilipino = document.getElementById('editNonFilipino');

    var deleteId = document.getElementById('deleteId');
    var deleteDocName = document.getElementById('deleteDocName');

    function normalizeSearchText(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/x/g, 'x')
            .replace(/\u00d7/g, 'x');
    }

    function toBoolean(value) {
        if (typeof value === 'boolean') {
            return value;
        }

        if (typeof value === 'number') {
            return value === 1;
        }

        if (typeof value === 'string') {
            return ['1', 'true', 'yes', 'on'].indexOf(value.toLowerCase()) !== -1;
        }

        return false;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function resolveUrl(template, id) {
        return template.replace('__ID__', String(id));
    }

    function getErrorMessage(payload) {
        if (payload && payload.errors) {
            for (var key in payload.errors) {
                if (!Object.prototype.hasOwnProperty.call(payload.errors, key)) {
                    continue;
                }

                var messages = payload.errors[key];
                if (messages && messages.length) {
                    return messages[0];
                }
            }
        }

        if (payload && typeof payload.message === 'string' && payload.message.trim() !== '') {
            return payload.message;
        }

        return 'Something went wrong. Please try again.';
    }

    function sendJson(url, method, payload) {
        var headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        };

        var options = {
            method: method,
            headers: headers,
            credentials: 'same-origin'
        };

        if (payload) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }

        return fetch(url, options)
            .then(function (response) {
                return response.text().then(function (text) {
                    var data = {};

                    if (text) {
                        try {
                            data = JSON.parse(text);
                        } catch (error) {
                            data = { message: text };
                        }
                    }

                    if (!response.ok) {
                        var requestError = new Error(getErrorMessage(data));
                        requestError.payload = data;
                        throw requestError;
                    }

                    return data;
                });
            });
    }

    function parseRowFromElement(row) {
        var id = parseInt(row.getAttribute('data-row-id'), 10);

        if (!id) {
            return null;
        }

        return {
            id: id,
            year_level: row.getAttribute('data-row-year-level') || 'All Year Level',
            document: row.getAttribute('data-row-document') || '',
            type: row.getAttribute('data-row-type') || 'Document',
            non_filipino: toBoolean(row.getAttribute('data-row-non-filipino'))
        };
    }

    function normalizeServerRow(row) {
        return {
            id: parseInt(row && row.id, 10),
            year_level: String((row && row.year_level) || 'All Year Level'),
            document: String((row && row.document) || ''),
            type: String((row && row.type) || 'Document'),
            non_filipino: toBoolean(row && row.non_filipino)
        };
    }

    function setRowsFromDom() {
        var parsedRows = [];
        var domRows = tableBody.querySelectorAll('tr[data-row-id]');

        for (var i = 0; i < domRows.length; i++) {
            var parsed = parseRowFromElement(domRows[i]);
            if (parsed) {
                parsedRows.push(parsed);
            }
        }

        state.rows = parsedRows;
    }

    function getRowById(id) {
        for (var i = 0; i < state.rows.length; i++) {
            if (state.rows[i].id === id) {
                return state.rows[i];
            }
        }

        return null;
    }

    function upsertRow(row) {
        for (var i = 0; i < state.rows.length; i++) {
            if (state.rows[i].id === row.id) {
                state.rows[i] = row;
                return;
            }
        }

        state.rows.push(row);
    }

    function removeRowById(id) {
        var nextRows = [];

        for (var i = 0; i < state.rows.length; i++) {
            if (state.rows[i].id !== id) {
                nextRows.push(state.rows[i]);
            }
        }

        state.rows = nextRows;
    }

    function closeDoclistActionMenus() {
        var openMenus = document.querySelectorAll('#doclistTable .apst-dropdown.open');

        for (var i = 0; i < openMenus.length; i++) {
            openMenus[i].classList.remove('open', 'drop-up');
            openMenus[i].style.top = '';
            openMenus[i].style.left = '';
            openMenus[i].style.right = '';
            openMenus[i].style.bottom = '';
        }
    }

    function toggleDoclistActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) {
            return;
        }

        var isOpen = menu.classList.contains('open');
        closeDoclistActionMenus();
        if (isOpen) {
            return;
        }

        var rect = trigger.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;

        menu.style.left = 'auto';
        menu.style.right = (window.innerWidth - rect.left + 4) + 'px';

        if (spaceBelow < 120) {
            menu.classList.add('drop-up');
            menu.style.top = 'auto';
            menu.style.bottom = (window.innerHeight - rect.bottom) + 'px';
        } else {
            menu.style.top = rect.top + 'px';
            menu.style.bottom = 'auto';
        }

        menu.classList.add('open');
    }

    function showModal(modal) {
        if (modal) {
            modal.classList.remove('doclist-modal-hidden');
        }
    }

    function hideModal(modal) {
        if (modal) {
            modal.classList.add('doclist-modal-hidden');
        }
    }

    function closeAllModals() {
        hideModal(addModal);
        hideModal(editModal);
        hideModal(deleteModal);
    }

    function resetAddForm() {
        addForm.reset();
        if (addGradeLevel) {
            addGradeLevel.value = '';
        }
        if (addDocument) {
            addDocument.value = '';
        }
        if (addNonFilipino) {
            addNonFilipino.checked = false;
        }

        var defaultType = addForm.querySelector('input[name="doc_type"][value="Document"]');
        if (defaultType) {
            defaultType.checked = true;
        }
    }

    function openAddDocModal() {
        closeDoclistActionMenus();
        resetAddForm();
        showModal(addModal);
    }

    function closeAddDocModal() {
        hideModal(addModal);
    }

    function openEditModalById(id) {
        var row = getRowById(id);
        if (!row) {
            return;
        }

        closeDoclistActionMenus();

        editId.value = String(row.id);
        editGradeLevel.value = row.year_level;
        editDocument.value = row.document;
        editNonFilipino.checked = !!row.non_filipino;

        if (row.type === 'Medical') {
            editTypeMedical.checked = true;
        } else {
            editTypeDocument.checked = true;
        }

        showModal(editModal);
    }

    function closeEditModal() {
        hideModal(editModal);
    }

    function openDeleteModalById(id) {
        var row = getRowById(id);
        if (!row) {
            return;
        }

        closeDoclistActionMenus();

        deleteId.value = String(row.id);
        deleteDocName.textContent = '"' + row.document + '"';

        showModal(deleteModal);
    }

    function closeDeleteModal() {
        hideModal(deleteModal);
    }

    function rowMatchesSearch(row, query) {
        if (query === '') {
            return true;
        }

        var rowText = normalizeSearchText([
            row.id,
            row.year_level,
            row.document,
            row.type,
            row.non_filipino ? 'true' : 'false'
        ].join(' '));

        if (rowText.indexOf(query) !== -1) {
            return true;
        }

        if (/^-?\d+$/.test(query)) {
            var numbers = rowText.match(/-?\d+/g);
            if (!numbers) {
                return false;
            }

            for (var i = 0; i < numbers.length; i++) {
                if (numbers[i] === query) {
                    return true;
                }
            }
        }

        return false;
    }

    function renderRows() {
        closeDoclistActionMenus();

        var query = normalizeSearchText(searchInput ? searchInput.value.trim() : '');
        var sortDirection = sortSelect ? sortSelect.value : 'asc';
        var visibleRows = [];

        for (var i = 0; i < state.rows.length; i++) {
            if (rowMatchesSearch(state.rows[i], query)) {
                visibleRows.push(state.rows[i]);
            }
        }

        visibleRows.sort(function (a, b) {
            var aText = normalizeSearchText(a.document);
            var bText = normalizeSearchText(b.document);
            var comparison = aText.localeCompare(bText);
            return sortDirection === 'desc' ? (-comparison) : comparison;
        });

        if (!visibleRows.length) {
            tableBody.innerHTML = '<tr class="doclist-empty-row"><td colspan="6">No requirements found.</td></tr>';
            return;
        }

        var html = '';

        for (var j = 0; j < visibleRows.length; j++) {
            var row = visibleRows[j];
            var menuId = 'docMenu' + row.id;
            var escapedYearLevel = escapeHtml(row.year_level);
            var escapedDocument = escapeHtml(row.document);
            var escapedType = escapeHtml(row.type);
            var nonFilipinoLabel = row.non_filipino ? 'True' : 'False';

            html += '<tr data-row-id="' + row.id + '" data-row-year-level="' + escapedYearLevel + '" data-row-document="' + escapedDocument + '" data-row-type="' + escapedType + '" data-row-non-filipino="' + (row.non_filipino ? '1' : '0') + '">';
            html += '<td>' + (j + 1) + '</td>';
            html += '<td>' + escapedYearLevel + '</td>';
            html += '<td>' + escapedDocument + '</td>';
            html += '<td>' + escapedType + '</td>';
            html += '<td>' + nonFilipinoLabel + '</td>';
            html += '<td>';
            html += '<div class="apst-action-btn" data-doc-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>';
            html += '<div class="apst-dropdown" id="' + menuId + '">';
            html += '<button type="button" class="js-doc-edit-btn" data-doc-id="' + row.id + '">' + EDIT_ICON + 'Edit</button>';
            html += '<button type="button" class="apst-del-btn js-doc-delete-btn" data-doc-id="' + row.id + '">' + DELETE_ICON + 'Delete</button>';
            html += '</div>';
            html += '</td>';
            html += '</tr>';
        }

        tableBody.innerHTML = html;
    }

    function buildPayload(form, yearLevelField, documentField, nonFilipinoField) {
        var typeInput = form.querySelector('input[name="doc_type"]:checked');

        return {
            grade_level: yearLevelField.value,
            document: documentField.value.trim(),
            doc_type: typeInput ? typeInput.value : 'Document',
            non_filipino: nonFilipinoField && nonFilipinoField.checked ? 1 : 0
        };
    }

    function handleAddSubmit(event) {
        event.preventDefault();

        var payload = buildPayload(addForm, addGradeLevel, addDocument, addNonFilipino);

        if (!payload.grade_level || !payload.document) {
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Please fill in all required fields.', 'warning');
            }
            return;
        }

        sendJson(storeUrl, 'POST', payload)
            .then(function (response) {
                var row = normalizeServerRow(response.row || {});

                if (!row.id) {
                    throw new Error('Unable to add requirement. Please refresh and try again.');
                }

                upsertRow(row);
                renderRows();
                closeAddDocModal();

                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(response.message || 'Requirement added successfully.', 'success');
                }
            })
            .catch(function (error) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(getErrorMessage(error.payload || { message: error.message }), 'error');
                }
            });
    }

    function handleEditSubmit(event) {
        event.preventDefault();

        var id = parseInt(editId.value, 10);
        if (!id) {
            return;
        }

        var payload = buildPayload(editForm, editGradeLevel, editDocument, editNonFilipino);

        if (!payload.grade_level || !payload.document) {
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast('Please fill in all required fields.', 'warning');
            }
            return;
        }

        sendJson(resolveUrl(updateUrlTemplate, id), 'PUT', payload)
            .then(function (response) {
                var row = normalizeServerRow(response.row || {});

                if (!row.id) {
                    throw new Error('Unable to update requirement. Please refresh and try again.');
                }

                upsertRow(row);
                renderRows();
                closeEditModal();

                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(response.message || 'Requirement updated successfully.', 'success');
                }
            })
            .catch(function (error) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(getErrorMessage(error.payload || { message: error.message }), 'error');
                }
            });
    }

    function handleDeleteSubmit(event) {
        event.preventDefault();

        var id = parseInt(deleteId.value, 10);
        if (!id) {
            return;
        }

        sendJson(resolveUrl(deleteUrlTemplate, id), 'DELETE')
            .then(function (response) {
                removeRowById(id);
                renderRows();
                closeDeleteModal();

                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(response.message || 'Requirement deleted successfully.', 'success');
                }
            })
            .catch(function (error) {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast(getErrorMessage(error.payload || { message: error.message }), 'error');
                }
            });
    }

    if (searchInput) {
        searchInput.addEventListener('input', renderRows);
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', renderRows);
    }

    if (addButton) {
        addButton.addEventListener('click', openAddDocModal);
    }

    if (addForm) {
        addForm.addEventListener('submit', handleAddSubmit);
    }

    if (editForm) {
        editForm.addEventListener('submit', handleEditSubmit);
    }

    if (deleteForm) {
        deleteForm.addEventListener('submit', handleDeleteSubmit);
    }

    if (addCancelButton) {
        addCancelButton.addEventListener('click', closeAddDocModal);
    }

    if (editCancelButton) {
        editCancelButton.addEventListener('click', closeEditModal);
    }

    if (deleteCancelButton) {
        deleteCancelButton.addEventListener('click', closeDeleteModal);
    }

    if (addModal) {
        addModal.addEventListener('click', function (event) {
            if (event.target === addModal) {
                closeAddDocModal();
            }
        });
    }

    if (editModal) {
        editModal.addEventListener('click', function (event) {
            if (event.target === editModal) {
                closeEditModal();
            }
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('click', function (event) {
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        });
    }

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-doc-menu-toggle]');
        if (toggle) {
            event.preventDefault();
            event.stopPropagation();
            toggleDoclistActionMenu(toggle.getAttribute('data-doc-menu-toggle'), toggle);
            return;
        }

        var editButton = event.target.closest('.js-doc-edit-btn');
        if (editButton) {
            event.preventDefault();
            openEditModalById(parseInt(editButton.getAttribute('data-doc-id'), 10));
            return;
        }

        var deleteButton = event.target.closest('.js-doc-delete-btn');
        if (deleteButton) {
            event.preventDefault();
            openDeleteModalById(parseInt(deleteButton.getAttribute('data-doc-id'), 10));
            return;
        }

        if (!event.target.closest('#doclistTable .apst-dropdown')) {
            closeDoclistActionMenus();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDoclistActionMenus();
            closeAllModals();
        }
    });

    window.addEventListener('scroll', closeDoclistActionMenus, true);
    window.addEventListener('resize', closeDoclistActionMenus);

    setRowsFromDom();
    renderRows();
})();
