/* Room File JS */

var RF_PAGE = document.getElementById('roomFilePage');
var RF_FETCH_URL = RF_PAGE ? RF_PAGE.getAttribute('data-fetch-url') : '';
var RF_STORE_URL = RF_PAGE ? RF_PAGE.getAttribute('data-store-url') : '';
var RF_STORE_BUILDING_URL = RF_PAGE ? RF_PAGE.getAttribute('data-store-building-url') : '';
var RF_STORE_HALLWAY_URL = RF_PAGE ? RF_PAGE.getAttribute('data-store-hallway-url') : '';
var RF_PROGRAM_FILE_URL = RF_PAGE ? RF_PAGE.getAttribute('data-program-file-url') : '';
var RF_UPDATE_URL_TEMPLATE = RF_PAGE ? RF_PAGE.getAttribute('data-update-url-template') : '';
var RF_DELETE_URL_TEMPLATE = RF_PAGE ? RF_PAGE.getAttribute('data-delete-url-template') : '';
var RF_CSRF_TOKEN = RF_PAGE ? RF_PAGE.getAttribute('data-csrf-token') : '';

var ROOMS = [];
var ROOM_OPTIONS = {
    buildings: [],
    programs: []
};

var currentPage = 1;
var lastPage = 1;
var totalRows = 0;
var perPage = 25;
var activeSearch = '';
var currentSortBy = RF_PAGE ? String(RF_PAGE.getAttribute('data-default-sort-by') || 'floor_number') : 'floor_number';
var currentSortDir = RF_PAGE && String(RF_PAGE.getAttribute('data-default-sort-dir') || '').toLowerCase() === 'desc' ? 'desc' : 'asc';
var optionsLoaded = false;
var isLoading = false;
var searchTimer = null;

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function getPayloadErrorMessage(payload, fallbackMessage) {
    if (payload && payload.errors && typeof payload.errors === 'object') {
        var firstKey = Object.keys(payload.errors)[0];
        if (firstKey && payload.errors[firstKey] && payload.errors[firstKey][0]) {
            return payload.errors[firstKey][0];
        }
    }

    if (payload && payload.message) {
        return payload.message;
    }

    return fallbackMessage;
}

function showErrorMessage(message) {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast(message, 'warning');
        return;
    }

    alert(message);
}

function showSuccessMessage(message) {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast(message, 'success');
        return;
    }

    console.log(message);
}

function getUrlFromTemplate(template, id) {
    if (!template || !id) {
        return '';
    }

    return template.replace('__ROOM_ID__', String(id));
}

function closeOpenMenus() {
    document.querySelectorAll('#rfTable .apst-dropdown').forEach(function (dropdown) {
        dropdown.classList.remove('open');
        dropdown.classList.remove('drop-up');
        dropdown.style.top = '';
        dropdown.style.left = '';
        dropdown.style.bottom = '';
    });
}

function removeDuplicateAutoPagers() {
    var table = document.getElementById('rfTable');
    if (!table) {
        return;
    }

    table.dataset.noAutoPager = '1';

    if (!RF_PAGE) {
        return;
    }

    RF_PAGE.querySelectorAll('.rtp-pagination').forEach(function (pager) {
        if (pager.closest('#rfPaginationBar')) {
            return;
        }

        if (pager.parentNode) {
            pager.parentNode.removeChild(pager);
        }
    });
}

function getPaginationWindow(page, maxPage) {
    var start = Math.max(1, page - 2);
    var end = Math.min(maxPage, page + 2);

    if (page <= 3) {
        end = Math.min(maxPage, 5);
    } else if (page >= maxPage - 2) {
        start = Math.max(1, maxPage - 4);
    }

    return {
        start: start,
        end: end
    };
}

function normalizeSortDirection(direction) {
    return String(direction || '').toLowerCase() === 'desc' ? 'desc' : 'asc';
}

function setSortState(sortBy, sortDir) {
    currentSortBy = String(sortBy || 'floor_number').trim() || 'floor_number';
    currentSortDir = normalizeSortDirection(sortDir);
    renderSortIndicators();
}

function renderSortIndicators() {
    document.querySelectorAll('#rfTable .rf-sort-btn').forEach(function (button) {
        var sortBy = String(button.getAttribute('data-sort') || '');
        var isActive = sortBy === currentSortBy;
        var indicator = button.querySelector('.rf-sort-indicator');
        var th = button.closest('th');

        button.classList.toggle('is-active', isActive);

        if (indicator) {
            if (!isActive) {
                indicator.textContent = 'Sort';
            } else {
                indicator.textContent = currentSortDir === 'desc' ? 'Desc' : 'Asc';
            }
        }

        if (th) {
            th.setAttribute('aria-sort', isActive
                ? (currentSortDir === 'desc' ? 'descending' : 'ascending')
                : 'none');
        }
    });
}

function toggleSort(sortBy) {
    if (!sortBy || isLoading) {
        return;
    }

    if (currentSortBy === sortBy) {
        currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortBy = sortBy;
        currentSortDir = 'asc';
    }

    renderSortIndicators();
    loadRooms(activeSearch, 1);
}

function setLoadingState(loading) {
    isLoading = !!loading;

    var prevBtn = document.getElementById('rfPrevBtn');
    var nextBtn = document.getElementById('rfNextBtn');
    if (prevBtn) {
        prevBtn.disabled = isLoading || currentPage <= 1;
    }
    if (nextBtn) {
        nextBtn.disabled = isLoading || currentPage >= lastPage;
    }

    document.querySelectorAll('#rfTable .rf-sort-btn').forEach(function (button) {
        button.disabled = isLoading;
    });

    var tbody = document.getElementById('rfBody');
    if (isLoading && tbody) {
        tbody.innerHTML = '<tr><td colspan="7">Loading rooms...</td></tr>';
    }
}

function sendJsonRequest(url, method, payload) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': RF_CSRF_TOKEN
        },
        body: payload ? JSON.stringify(payload) : null,
        credentials: 'same-origin'
    }).then(function (response) {
        return response.json().catch(function () {
            return {};
        }).then(function (responsePayload) {
            if (!response.ok) {
                throw responsePayload;
            }
            return responsePayload;
        });
    });
}

function buildFetchUrl(searchValue, pageValue) {
    var params = [
        'page=' + encodeURIComponent(String(pageValue)),
        'per_page=' + encodeURIComponent(String(perPage)),
        'sort_by=' + encodeURIComponent(String(currentSortBy)),
        'sort_dir=' + encodeURIComponent(String(currentSortDir))
    ];

    if (!optionsLoaded) {
        params.push('include_options=1');
    }

    if (searchValue) {
        params.push('search=' + encodeURIComponent(searchValue));
    }

    return RF_FETCH_URL + (RF_FETCH_URL.indexOf('?') === -1 ? '?' : '&') + params.join('&');
}

function syncOptionCaches(options) {
    if (!options || !Array.isArray(options.buildings) || !Array.isArray(options.programs)) {
        return;
    }

    ROOM_OPTIONS.buildings = options.buildings;
    ROOM_OPTIONS.programs = options.programs;
    optionsLoaded = true;

    renderBuildingOptions('newRoomBuilding', null);
    renderBuildingOptions('editRoomBuilding', null);
    renderProgramOptions('newRoomProgram', null);
    renderProgramOptions('editRoomProgram', null);
}

function loadRooms(search, page) {
    if (!RF_FETCH_URL) {
        return;
    }

    if (typeof search === 'string') {
        activeSearch = String(search || '').trim();
    }

    if (typeof page === 'number' && page > 0) {
        currentPage = page;
    }

    var url = buildFetchUrl(activeSearch, currentPage);
    setLoadingState(true);

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    }).then(function (response) {
        return response.json().catch(function () {
            return {};
        }).then(function (responsePayload) {
            if (!response.ok) {
                throw responsePayload;
            }
            return responsePayload;
        });
    }).then(function (responsePayload) {
        ROOMS = responsePayload && responsePayload.rows ? responsePayload.rows : [];

        var meta = responsePayload && responsePayload.meta ? responsePayload.meta : {};
        currentPage = Number(meta.page || currentPage || 1);
        lastPage = Number(meta.last_page || 1);
        totalRows = Number(meta.total || 0);
        perPage = Number(meta.per_page || perPage || 25);
        setSortState(meta.sort_by || currentSortBy, meta.sort_dir || currentSortDir);

        if (lastPage < 1) {
            lastPage = 1;
        }
        if (currentPage < 1) {
            currentPage = 1;
        }

        if (currentPage > lastPage) {
            currentPage = lastPage;
        }

        if (perPage < 10) {
            perPage = 10;
        }

        if (perPage > 100) {
            perPage = 100;
        }

        perPage = 25;

        if (responsePayload && responsePayload.options) {
            syncOptionCaches(responsePayload.options);
        }

        if (ROOMS.length === 0 && totalRows > 0 && currentPage > 1) {
            loadRooms(activeSearch, currentPage - 1);
            return;
        }

        renderRoomTable();
        renderPagination();
    }).catch(function (errorPayload) {
        console.error(errorPayload);
        showErrorMessage(getPayloadErrorMessage(errorPayload, 'Unable to load room data right now.'));
    }).finally(function () {
        setLoadingState(false);
        renderPagination();
    });
}

function renderRoomTable() {
    var tbody = document.getElementById('rfBody');
    if (!tbody) {
        return;
    }

    tbody.innerHTML = '';

    if (!ROOMS.length) {
        tbody.innerHTML = '<tr><td colspan="7">No rooms found.</td></tr>';
        return;
    }

    ROOMS.forEach(function (room) {
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td>' +
                '<div class="apst-action-btn" onclick="toggleRoomMenu(' + room.id + ', event)">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
                '<div class="apst-dropdown" id="rfMenu' + room.id + '">' +
                    '<button onclick="openEditRoomModal(' + room.id + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                        ' Edit' +
                    '</button>' +
                    '<button class="apst-del-btn" onclick="openDeleteRoomModal(' + room.id + ')">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                        ' Delete' +
                    '</button>' +
                '</div>' +
            '</td>' +
            '<td>' + escapeHtml(room.room_number) + '</td>' +
            '<td>' + escapeHtml(room.floor_number) + '</td>' +
            '<td>' + escapeHtml(room.location_label || '-') + '</td>' +
            '<td>' + escapeHtml(room.capacity) + '</td>' +
            '<td>' + escapeHtml(room.program_label || '-') + '</td>' +
            '<td>' + escapeHtml(room.updated_by || '-') + '</td>';

        tbody.appendChild(tr);
    });
}

function renderPagination() {
    var prevBtn = document.getElementById('rfPrevBtn');
    var nextBtn = document.getElementById('rfNextBtn');
    var pageNumbers = document.getElementById('rfPageNumbers');

    if (prevBtn) {
        prevBtn.disabled = isLoading || currentPage <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = isLoading || currentPage >= lastPage;
    }

    if (pageNumbers) {
        pageNumbers.innerHTML = '';

        var windowRange = getPaginationWindow(currentPage, lastPage);
        for (var pageNumber = windowRange.start; pageNumber <= windowRange.end; pageNumber += 1) {
            var pageBtn = document.createElement('button');
            pageBtn.type = 'button';
            pageBtn.className = 'rtp-page-num' + (pageNumber === currentPage ? ' active' : '');
            pageBtn.setAttribute('data-rf-page', String(pageNumber));
            pageBtn.setAttribute('aria-label', 'Go to page ' + pageNumber);
            pageBtn.textContent = String(pageNumber);

            if (isLoading || pageNumber === currentPage) {
                pageBtn.disabled = true;
            }

            pageNumbers.appendChild(pageBtn);
        }
    }
}

function renderBuildingOptions(selectId, selectedId) {
    var select = document.getElementById(selectId);
    if (!select) {
        return;
    }

    var optionsHtml = '<option value="">- Select Building -</option>';
    ROOM_OPTIONS.buildings.forEach(function (building) {
        var selected = selectedId && Number(selectedId) === Number(building.id) ? ' selected' : '';
        optionsHtml += '<option value="' + Number(building.id) + '"' + selected + '>' + escapeHtml(building.name) + '</option>';
    });

    select.innerHTML = optionsHtml;
}

function renderProgramOptions(selectId, selectedId) {
    var select = document.getElementById(selectId);
    if (!select) {
        return;
    }

    var optionsHtml = '<option value="">- Select Program -</option>';
    ROOM_OPTIONS.programs.forEach(function (program) {
        var selected = selectedId && Number(selectedId) === Number(program.id) ? ' selected' : '';
        optionsHtml += '<option value="' + Number(program.id) + '"' + selected + '>' + escapeHtml(program.label || program.code || program.name) + '</option>';
    });

    select.innerHTML = optionsHtml;
}

function getBuildingById(buildingId) {
    var id = Number(buildingId || 0);
    var found = null;

    ROOM_OPTIONS.buildings.forEach(function (building) {
        if (Number(building.id) === id) {
            found = building;
        }
    });

    return found;
}

function upsertBuildingOption(buildingPayload) {
    var buildingId = Number(buildingPayload && buildingPayload.id ? buildingPayload.id : 0);
    var buildingName = String(buildingPayload && buildingPayload.name ? buildingPayload.name : '').trim();

    if (!buildingId || !buildingName) {
        return null;
    }

    var rawHallways = Array.isArray(buildingPayload && buildingPayload.hallways) ? buildingPayload.hallways : [];
    var normalizedHallways = rawHallways.map(function (hallway) {
        return {
            id: Number(hallway && hallway.id ? hallway.id : 0),
            name: String(hallway && hallway.name ? hallway.name : '').trim()
        };
    }).filter(function (hallway) {
        return hallway.id > 0 && hallway.name !== '';
    });

    normalizedHallways.sort(function (left, right) {
        return String(left.name || '').localeCompare(String(right.name || ''));
    });

    var existing = getBuildingById(buildingId);
    if (existing) {
        existing.name = buildingName;
        existing.hallways = normalizedHallways;
        return existing;
    }

    var newBuilding = {
        id: buildingId,
        name: buildingName,
        hallways: normalizedHallways
    };

    ROOM_OPTIONS.buildings.push(newBuilding);
    ROOM_OPTIONS.buildings.sort(function (left, right) {
        return String(left.name || '').localeCompare(String(right.name || ''));
    });

    return newBuilding;
}

function openProgramSetup() {
    if (!RF_PROGRAM_FILE_URL) {
        showErrorMessage('Program File page is unavailable right now.');
        return;
    }

    var popup = window.open(RF_PROGRAM_FILE_URL, '_blank');
    if (!popup) {
        window.location.href = RF_PROGRAM_FILE_URL;
    }
}

function openBuildingModal(prefix) {
    var modal = document.getElementById('addBuildingModal');
    var prefixInput = document.getElementById('addBuildingPrefix');
    var nameInput = document.getElementById('addBuildingName');

    if (!modal || !prefixInput || !nameInput) {
        showErrorMessage('Building setup modal is unavailable right now.');
        return;
    }

    if (prefix !== 'new' && prefix !== 'edit') {
        showErrorMessage('Unable to determine which room form to update.');
        return;
    }

    prefixInput.value = prefix;
    nameInput.value = '';
    modal.style.display = 'flex';

    setTimeout(function () {
        nameInput.focus();
    }, 0);
}

function closeBuildingModal() {
    var modal = document.getElementById('addBuildingModal');
    if (modal) {
        modal.style.display = 'none';
    }

    var form = document.getElementById('addBuildingForm');
    if (form) {
        form.reset();
    }
}

function handleBuildingModalSave(event) {
    event.preventDefault();

    var prefixInput = document.getElementById('addBuildingPrefix');
    var nameInput = document.getElementById('addBuildingName');
    var submitBtn = event.submitter ? event.submitter : document.getElementById('addBuildingSaveBtn');

    var prefix = prefixInput ? String(prefixInput.value || '').trim() : '';
    var buildingName = nameInput ? String(nameInput.value || '').trim() : '';

    if (prefix !== 'new' && prefix !== 'edit') {
        showErrorMessage('Unable to determine which room form to update.');
        return false;
    }

    if (!buildingName) {
        showErrorMessage('Building name is required.');
        return false;
    }

    if (submitBtn) {
        submitBtn.disabled = true;
    }

    createBuildingForSelect(prefix, buildingName)
        .then(function (responsePayload) {
            if (responsePayload) {
                closeBuildingModal();
            }
        })
        .finally(function () {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        });

    return false;
}

function createBuildingForSelect(prefix, providedName) {
    var addButton = document.getElementById(prefix + 'AddBuildingBtn');

    if (!RF_STORE_BUILDING_URL) {
        showErrorMessage('Building setup endpoint is unavailable right now.');
        return Promise.resolve(null);
    }

    if (typeof providedName !== 'string') {
        openBuildingModal(prefix);
        return Promise.resolve(null);
    }

    var buildingName = String(providedName || '').trim().replace(/\s+/g, ' ');
    if (!buildingName) {
        showErrorMessage('Building name is required.');
        return Promise.resolve(null);
    }

    var newSelect = document.getElementById('newRoomBuilding');
    var editSelect = document.getElementById('editRoomBuilding');

    var newSelected = newSelect && newSelect.value ? Number(newSelect.value) : null;
    var editSelected = editSelect && editSelect.value ? Number(editSelect.value) : null;

    if (addButton) {
        addButton.disabled = true;
    }

    return sendJsonRequest(RF_STORE_BUILDING_URL, 'POST', {
        name: buildingName
    })
        .then(function (responsePayload) {
            var buildingPayload = responsePayload && responsePayload.building ? responsePayload.building : null;
            if (!buildingPayload) {
                showErrorMessage('Unable to add building right now.');
                return null;
            }

            var building = upsertBuildingOption(buildingPayload);
            if (!building) {
                showErrorMessage('Unable to refresh building list right now.');
                return null;
            }

            var targetBuildingId = Number(building.id);
            var resolvedNewSelected = prefix === 'new' ? targetBuildingId : newSelected;
            var resolvedEditSelected = prefix === 'edit' ? targetBuildingId : editSelected;

            renderBuildingOptions('newRoomBuilding', resolvedNewSelected);
            renderBuildingOptions('editRoomBuilding', resolvedEditSelected);

            renderHallwayOptions(resolvedNewSelected, 'newRoomHallway', null);
            renderHallwayOptions(resolvedEditSelected, 'editRoomHallway', null);

            if (responsePayload && responsePayload.existing) {
                showSuccessMessage('Building already exists. Selected existing building.');
            } else {
                showSuccessMessage('Building added successfully.');
            }

            return responsePayload;
        })
        .catch(function (errorPayload) {
            showErrorMessage(getPayloadErrorMessage(errorPayload, 'Unable to add building right now.'));
            return null;
        })
        .finally(function () {
            if (addButton) {
                addButton.disabled = false;
            }
        });
}

function upsertHallwayOption(buildingId, hallwayPayload) {
    var building = getBuildingById(buildingId);
    if (!building) {
        return null;
    }

    if (!Array.isArray(building.hallways)) {
        building.hallways = [];
    }

    var hallwayId = Number(hallwayPayload && hallwayPayload.id ? hallwayPayload.id : 0);
    var hallwayName = String(hallwayPayload && hallwayPayload.name ? hallwayPayload.name : '').trim();

    if (!hallwayId || !hallwayName) {
        return null;
    }

    var existing = null;
    building.hallways.forEach(function (hallway) {
        if (Number(hallway.id) === hallwayId) {
            existing = hallway;
        }
    });

    if (existing) {
        existing.name = hallwayName;
        return existing;
    }

    var newHallway = {
        id: hallwayId,
        name: hallwayName
    };

    building.hallways.push(newHallway);
    building.hallways.sort(function (left, right) {
        return String(left.name || '').localeCompare(String(right.name || ''));
    });

    return newHallway;
}

function renderHallwayOptions(buildingId, selectId, selectedHallwayId) {
    var select = document.getElementById(selectId);
    if (!select) {
        return;
    }

    var building = getBuildingById(buildingId);
    var optionsHtml = '<option value="">- Select Hallway -</option>';

    if (building && building.hallways && building.hallways.length) {
        building.hallways.forEach(function (hallway) {
            var selected = '';
            if (selectedHallwayId && Number(selectedHallwayId) === Number(hallway.id)) {
                selected = ' selected';
            } else if (!selectedHallwayId && building.hallways.length === 1) {
                selected = ' selected';
            }

            optionsHtml += '<option value="' + Number(hallway.id) + '"' + selected + '>' + escapeHtml(hallway.name) + '</option>';
        });
    }

    select.innerHTML = optionsHtml;
}

function openHallwayModal(prefix) {
    var buildingSelect = document.getElementById(prefix + 'RoomBuilding');
    if (!buildingSelect || !buildingSelect.value) {
        showErrorMessage('Please select a building first.');
        return;
    }

    var modal = document.getElementById('addHallwayModal');
    var prefixInput = document.getElementById('addHallwayPrefix');
    var buildingInput = document.getElementById('addHallwayBuilding');
    var hallwayInput = document.getElementById('addHallwayName');

    if (!modal || !prefixInput || !buildingInput || !hallwayInput) {
        showErrorMessage('Hallway setup modal is unavailable right now.');
        return;
    }

    var building = getBuildingById(buildingSelect.value);
    var selectedText = buildingSelect.options && buildingSelect.selectedIndex >= 0
        ? buildingSelect.options[buildingSelect.selectedIndex].text
        : '';

    prefixInput.value = prefix;
    buildingInput.value = building && building.name ? building.name : selectedText;
    hallwayInput.value = '';

    modal.style.display = 'flex';
    setTimeout(function () {
        hallwayInput.focus();
    }, 0);
}

function closeHallwayModal() {
    var modal = document.getElementById('addHallwayModal');
    if (modal) {
        modal.style.display = 'none';
    }

    var form = document.getElementById('addHallwayForm');
    if (form) {
        form.reset();
    }
}

function handleHallwayModalSave(event) {
    event.preventDefault();

    var prefixInput = document.getElementById('addHallwayPrefix');
    var hallwayNameInput = document.getElementById('addHallwayName');
    var submitBtn = event.submitter ? event.submitter : document.getElementById('addHallwaySaveBtn');

    var prefix = prefixInput ? String(prefixInput.value || '').trim() : '';
    var hallwayName = hallwayNameInput ? String(hallwayNameInput.value || '').trim() : '';

    if (prefix !== 'new' && prefix !== 'edit') {
        showErrorMessage('Unable to determine which room form to update.');
        return false;
    }

    if (!hallwayName) {
        showErrorMessage('Hallway name is required.');
        return false;
    }

    if (submitBtn) {
        submitBtn.disabled = true;
    }

    createHallwayForSelect(prefix, hallwayName)
        .then(function (responsePayload) {
            if (responsePayload) {
                closeHallwayModal();
            }
        })
        .finally(function () {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        });

    return false;
}

function createHallwayForSelect(prefix, providedName) {
    var buildingSelect = document.getElementById(prefix + 'RoomBuilding');
    var hallwaySelectId = prefix + 'RoomHallway';
    var addButton = document.getElementById(prefix + 'AddHallwayBtn');

    if (!buildingSelect || !buildingSelect.value) {
        showErrorMessage('Please select a building first.');
        return Promise.resolve(null);
    }

    if (!RF_STORE_HALLWAY_URL) {
        showErrorMessage('Hallway setup endpoint is unavailable right now.');
        return Promise.resolve(null);
    }

    if (typeof providedName !== 'string') {
        openHallwayModal(prefix);
        return Promise.resolve(null);
    }

    var hallwayName = String(providedName || '').trim().replace(/\s+/g, ' ');
    if (!hallwayName) {
        showErrorMessage('Hallway name is required.');
        return Promise.resolve(null);
    }

    var payload = {
        room_building_id: Number(buildingSelect.value),
        name: hallwayName
    };

    if (addButton) {
        addButton.disabled = true;
    }

    return sendJsonRequest(RF_STORE_HALLWAY_URL, 'POST', payload)
        .then(function (responsePayload) {
            var hallwayPayload = responsePayload && responsePayload.hallway ? responsePayload.hallway : null;
            if (!hallwayPayload) {
                showErrorMessage('Unable to add hallway right now.');
                return null;
            }

            var hallway = upsertHallwayOption(payload.room_building_id, hallwayPayload);
            if (!hallway) {
                showErrorMessage('Unable to refresh hallway list right now.');
                return null;
            }

            renderHallwayOptions(payload.room_building_id, hallwaySelectId, hallway.id);

            if (responsePayload && responsePayload.existing) {
                showSuccessMessage('Hallway already exists. Selected existing hallway.');
            } else {
                showSuccessMessage('Hallway added successfully.');
            }

            return responsePayload;
        })
        .catch(function (errorPayload) {
            showErrorMessage(getPayloadErrorMessage(errorPayload, 'Unable to add hallway right now.'));
            return null;
        })
        .finally(function () {
            if (addButton) {
                addButton.disabled = false;
            }
        });
}

function extractRoomPayload(prefix) {
    var roomNumber = Number(document.getElementById(prefix + 'RoomNumber').value);
    var floorNumber = Number(document.getElementById(prefix + 'RoomFloor').value);
    var buildingId = Number(document.getElementById(prefix + 'RoomBuilding').value);
    var hallwayId = Number(document.getElementById(prefix + 'RoomHallway').value);
    var capacity = Number(document.getElementById(prefix + 'RoomStudents').value);
    var programId = Number(document.getElementById(prefix + 'RoomProgram').value);

    if (!roomNumber || !floorNumber || !buildingId || !hallwayId || !capacity || !programId) {
        showErrorMessage('Please fill in all fields.');
        return null;
    }

    if (roomNumber < 1 || floorNumber < 1 || capacity < 1) {
        showErrorMessage('Room number, floor, and capacity must be greater than zero.');
        return null;
    }

    return {
        room_number: roomNumber,
        floor_number: floorNumber,
        room_building_id: buildingId,
        room_hallway_id: hallwayId,
        capacity: capacity,
        course_ids: [programId]
    };
}

function openNewRoomModal() {
    var form = document.getElementById('newRoomForm');
    if (form) {
        form.reset();
    }

    renderBuildingOptions('newRoomBuilding', null);
    renderProgramOptions('newRoomProgram', null);

    var newBuildingSelect = document.getElementById('newRoomBuilding');
    if (newBuildingSelect && ROOM_OPTIONS.buildings.length) {
        newBuildingSelect.value = String(ROOM_OPTIONS.buildings[0].id);
        renderHallwayOptions(newBuildingSelect.value, 'newRoomHallway', null);
    } else {
        renderHallwayOptions(null, 'newRoomHallway', null);
    }

    var newProgramSelect = document.getElementById('newRoomProgram');
    if (newProgramSelect && newProgramSelect.options.length > 1) {
        newProgramSelect.value = newProgramSelect.options[1].value;
    }

    document.getElementById('newRoomModal').style.display = 'flex';
}

function closeNewRoomModal() {
    document.getElementById('newRoomModal').style.display = 'none';
}

function handleNewRoomSave(event) {
    event.preventDefault();

    var payload = extractRoomPayload('new');
    if (!payload) {
        return false;
    }

    var submitBtn = event.submitter ? event.submitter : document.querySelector('#newRoomForm button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
    }

    sendJsonRequest(RF_STORE_URL, 'POST', payload)
        .then(function (responsePayload) {
            closeNewRoomModal();
            loadRooms(activeSearch, 1);

            var row = responsePayload && responsePayload.row ? responsePayload.row : null;
            var roomLabel = row ? row.room_number : payload.room_number;
            showRoomSuccessModal('Room #' + roomLabel + ' added successfully.');
        })
        .catch(function (errorPayload) {
            showErrorMessage(getPayloadErrorMessage(errorPayload, 'Unable to add room right now.'));
        })
        .finally(function () {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        });

    return false;
}

function openEditRoomModal(id) {
    var room = ROOMS.find(function (item) {
        return Number(item.id) === Number(id);
    });

    if (!room) {
        return;
    }

    document.getElementById('editRoomId').value = room.id;
    document.getElementById('editRoomNumber').value = room.room_number;
    document.getElementById('editRoomFloor').value = room.floor_number;
    document.getElementById('editRoomStudents').value = room.capacity;

    renderBuildingOptions('editRoomBuilding', room.room_building_id);
    renderHallwayOptions(room.room_building_id, 'editRoomHallway', room.room_hallway_id);

    var selectedProgramId = room.program_ids && room.program_ids.length ? room.program_ids[0] : null;
    renderProgramOptions('editRoomProgram', selectedProgramId);

    document.getElementById('editRoomModal').style.display = 'flex';
}

function closeEditRoomModal() {
    document.getElementById('editRoomModal').style.display = 'none';
}

function handleEditRoomSave(event) {
    event.preventDefault();

    var roomId = Number(document.getElementById('editRoomId').value);
    if (!roomId) {
        showErrorMessage('Invalid room record. Please refresh and try again.');
        return false;
    }

    var updateUrl = getUrlFromTemplate(RF_UPDATE_URL_TEMPLATE, roomId);
    if (!updateUrl) {
        showErrorMessage('Unable to prepare room update request.');
        return false;
    }

    var payload = extractRoomPayload('edit');
    if (!payload) {
        return false;
    }

    var submitBtn = event.submitter ? event.submitter : document.querySelector('#editRoomForm button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
    }

    sendJsonRequest(updateUrl, 'PUT', payload)
        .then(function () {
            closeEditRoomModal();
            loadRooms(activeSearch, currentPage);
            showRoomSuccessModal('Room #' + payload.room_number + ' updated successfully.');
        })
        .catch(function (errorPayload) {
            showErrorMessage(getPayloadErrorMessage(errorPayload, 'Unable to update room right now.'));
        })
        .finally(function () {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        });

    return false;
}

function openDeleteRoomModal(id) {
    var room = ROOMS.find(function (item) {
        return Number(item.id) === Number(id);
    });

    if (!room) {
        return;
    }

    document.getElementById('deleteRoomId').value = room.id;
    document.getElementById('deleteRoomName').textContent = 'Room #' + room.room_number + ' - ' + (room.location_label || '-');
    document.getElementById('deleteRoomModal').style.display = 'flex';
}

function closeDeleteRoomModal() {
    document.getElementById('deleteRoomModal').style.display = 'none';
}

function handleDeleteRoomConfirm() {
    var roomId = Number(document.getElementById('deleteRoomId').value);
    var deleteUrl = getUrlFromTemplate(RF_DELETE_URL_TEMPLATE, roomId);

    if (!roomId || !deleteUrl) {
        showErrorMessage('Invalid room record. Please refresh and try again.');
        return;
    }

    var deleteBtn = document.querySelector('#deleteRoomModal .pf-modal-btn-save');
    if (deleteBtn) {
        deleteBtn.disabled = true;
    }

    sendJsonRequest(deleteUrl, 'DELETE')
        .then(function () {
            closeDeleteRoomModal();
            loadRooms(activeSearch, currentPage);
            showRoomSuccessModal('Room deleted successfully.');
        })
        .catch(function (errorPayload) {
            showErrorMessage(getPayloadErrorMessage(errorPayload, 'Unable to delete room right now.'));
        })
        .finally(function () {
            if (deleteBtn) {
                deleteBtn.disabled = false;
            }
        });
}

function showRoomSuccessModal(message) {
    document.getElementById('roomSuccessMsg').textContent = message;
    document.getElementById('roomSuccessModal').style.display = 'flex';
}

function closeRoomSuccessModal() {
    document.getElementById('roomSuccessModal').style.display = 'none';
}

function toggleRoomMenu(id, event) {
    event.stopPropagation();

    var menu = document.getElementById('rfMenu' + id);
    if (!menu) {
        return;
    }

    var isOpen = menu.classList.contains('open');
    closeOpenMenus();

    if (!isOpen) {
        var button = menu.parentElement.querySelector('.apst-action-btn');
        if (!button) {
            return;
        }

        var rect = button.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;

        menu.style.left = (rect.right + 4) + 'px';
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
}

function bindRoomFileControls() {
    removeDuplicateAutoPagers();

    var searchInput = document.getElementById('rfSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            if (searchTimer) {
                clearTimeout(searchTimer);
            }

            searchTimer = setTimeout(function () {
                loadRooms(searchInput.value, 1);
            }, 250);
        });
    }

    var prevBtn = document.getElementById('rfPrevBtn');
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (!isLoading && currentPage > 1) {
                loadRooms(activeSearch, currentPage - 1);
            }
        });
    }

    var nextBtn = document.getElementById('rfNextBtn');
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (!isLoading && currentPage < lastPage) {
                loadRooms(activeSearch, currentPage + 1);
            }
        });
    }

    var paginationBar = document.getElementById('rfPaginationBar');
    if (paginationBar) {
        paginationBar.addEventListener('click', function (event) {
            var pageBtn = event.target.closest('[data-rf-page]');
            if (!pageBtn || isLoading) {
                return;
            }

            var targetPage = parseInt(pageBtn.getAttribute('data-rf-page'), 10);
            if (isNaN(targetPage) || targetPage < 1 || targetPage > lastPage || targetPage === currentPage) {
                return;
            }

            loadRooms(activeSearch, targetPage);
        });
    }

    document.querySelectorAll('#rfTable .rf-sort-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            toggleSort(button.getAttribute('data-sort'));
        });
    });

    renderSortIndicators();

    var newBuildingSelect = document.getElementById('newRoomBuilding');
    if (newBuildingSelect) {
        newBuildingSelect.addEventListener('change', function () {
            renderHallwayOptions(newBuildingSelect.value, 'newRoomHallway', null);
        });
    }

    var editBuildingSelect = document.getElementById('editRoomBuilding');
    if (editBuildingSelect) {
        editBuildingSelect.addEventListener('change', function () {
            renderHallwayOptions(editBuildingSelect.value, 'editRoomHallway', null);
        });
    }

    var newAddBuildingBtn = document.getElementById('newAddBuildingBtn');
    if (newAddBuildingBtn) {
        newAddBuildingBtn.addEventListener('click', function () {
            openBuildingModal('new');
        });
    }

    var editAddBuildingBtn = document.getElementById('editAddBuildingBtn');
    if (editAddBuildingBtn) {
        editAddBuildingBtn.addEventListener('click', function () {
            openBuildingModal('edit');
        });
    }

    var addBuildingForm = document.getElementById('addBuildingForm');
    if (addBuildingForm) {
        addBuildingForm.addEventListener('submit', handleBuildingModalSave);
    }

    var addBuildingCancelBtn = document.getElementById('addBuildingCancelBtn');
    if (addBuildingCancelBtn) {
        addBuildingCancelBtn.addEventListener('click', closeBuildingModal);
    }

    var newAddHallwayBtn = document.getElementById('newAddHallwayBtn');
    if (newAddHallwayBtn) {
        newAddHallwayBtn.addEventListener('click', function () {
            openHallwayModal('new');
        });
    }

    var editAddHallwayBtn = document.getElementById('editAddHallwayBtn');
    if (editAddHallwayBtn) {
        editAddHallwayBtn.addEventListener('click', function () {
            openHallwayModal('edit');
        });
    }

    var addHallwayForm = document.getElementById('addHallwayForm');
    if (addHallwayForm) {
        addHallwayForm.addEventListener('submit', handleHallwayModalSave);
    }

    var addHallwayCancelBtn = document.getElementById('addHallwayCancelBtn');
    if (addHallwayCancelBtn) {
        addHallwayCancelBtn.addEventListener('click', closeHallwayModal);
    }

    var newGoProgramSetupBtn = document.getElementById('newGoProgramSetupBtn');
    if (newGoProgramSetupBtn) {
        newGoProgramSetupBtn.addEventListener('click', openProgramSetup);
    }

    var editGoProgramSetupBtn = document.getElementById('editGoProgramSetupBtn');
    if (editGoProgramSetupBtn) {
        editGoProgramSetupBtn.addEventListener('click', openProgramSetup);
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.apst-action-btn') && !event.target.closest('.apst-dropdown')) {
            closeOpenMenus();
        }
    });

    window.addEventListener('scroll', function () {
        closeOpenMenus();
    }, true);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeNewRoomModal();
            closeEditRoomModal();
            closeBuildingModal();
            closeHallwayModal();
            closeDeleteRoomModal();
            closeRoomSuccessModal();
        }
    });
}

if (RF_PAGE) {
    bindRoomFileControls();
    setSortState(currentSortBy, currentSortDir);
    loadRooms('', 1);
}
