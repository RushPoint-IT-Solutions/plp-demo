(function () {
    var root = document.getElementById('auditTrailRoot');
    if (!root) {
        return;
    }

    var dataEndpoint = root.getAttribute('data-data-endpoint') || '';

    var state = {
        currentPage: 1,
        lastPage: 1,
        perPage: 20,
        total: 0,
        from: 0,
        rows: [],
    };

    var listRequestState = {
        controller: null,
        sequence: 0,
    };

    var searchDebounceTimer = null;

    var els = {
        search: document.getElementById('auditSearchInput'),
        user: document.getElementById('auditUserFilter'),
        module: document.getElementById('auditModuleFilter'),
        dateFrom: document.getElementById('auditDateFrom'),
        dateTo: document.getElementById('auditDateTo'),
        searchBtn: document.getElementById('auditSearchBtn'),
        clearBtn: document.getElementById('auditClearBtn'),
        tableBody: document.getElementById('auditTrailBody'),
        pager: document.getElementById('auditTrailPager'),
    };

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch] || ch;
        });
    }

    function getFilters() {
        return {
            search: (els.search ? els.search.value : '').trim(),
            user_id: (els.user ? els.user.value : '').trim(),
            module: (els.module ? els.module.value : '').trim(),
            date_from: (els.dateFrom ? els.dateFrom.value : '').trim(),
            date_to: (els.dateTo ? els.dateTo.value : '').trim(),
        };
    }

    function buildDataUrl(page) {
        var url = new URL(dataEndpoint, window.location.origin);
        var filters = getFilters();

        Object.keys(filters).forEach(function (key) {
            if (filters[key]) {
                url.searchParams.set(key, filters[key]);
            }
        });

        url.searchParams.set('page', String(page || 1));
        url.searchParams.set('per_page', String(state.perPage));

        return url.toString();
    }

    function renderLoading() {
        if (!els.tableBody) {
            return;
        }
        els.tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading audit logs...</td></tr>';
    }

    function renderTable() {
        if (!els.tableBody) {
            return;
        }

        if (!state.rows.length) {
            els.tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No audit logs found.</td></tr>';
            return;
        }

        var html = state.rows.map(function (log) {
            return '' +
                '<tr>' +
                    '<td><span class="text-muted">' + escapeHtml(log.timestamp) + '</span></td>' +
                    '<td><strong>' + escapeHtml(log.user) + '</strong></td>' +
                    '<td><span class="badge badge-soft-info">' + escapeHtml(log.module) + '</span></td>' +
                    '<td>' + escapeHtml(log.action) + '</td>' +
                    '<td><small class="text-muted">' + escapeHtml(log.details) + '</small></td>' +
                '</tr>';
        }).join('');

        els.tableBody.innerHTML = html;
    }

    function renderPager() {
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
            pageButtons += '<button type="button" class="rtp-page-num ' + (p === state.currentPage ? 'active' : '') + '" data-audit-page="' + p + '">' + p + '</button>';
        }

        els.pager.innerHTML = '' +
            '<div class="rtp-pagination">' +
                '<nav class="rtp-nav" aria-label="Audit Trail pagination">' +
                    '<div class="rtp-list" role="group" aria-label="Page controls">' +
                        '<button type="button" class="rtp-page-btn" data-audit-page-prev="1" ' + (state.currentPage <= 1 ? 'disabled' : '') + '>&lt;</button>' +
                        '<div class="rtp-pages">' + pageButtons + '</div>' +
                        '<button type="button" class="rtp-page-btn" data-audit-page-next="1" ' + (state.currentPage >= state.lastPage ? 'disabled' : '') + '>&gt;</button>' +
                    '</div>' +
                '</nav>' +
            '</div>';
    }

    async function fetchRows(page) {
        if (!dataEndpoint) {
            return;
        }

        if (listRequestState.controller) {
            listRequestState.controller.abort();
        }

        listRequestState.controller = new AbortController();
        listRequestState.sequence += 1;
        var requestSequence = listRequestState.sequence;

        renderLoading();

        try {
            var response = await fetch(buildDataUrl(page), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                signal: listRequestState.controller.signal,
            });

            var json = {};
            try {
                json = await response.json();
            } catch (error) {
                json = {};
            }

            if (requestSequence !== listRequestState.sequence) {
                return;
            }

            if (!response.ok || json.ok === false) {
                throw new Error(json.message || 'Unable to load audit logs.');
            }

            var meta = json.meta || {};
            state.rows = json.rows || [];
            state.currentPage = parseInt(meta.currentPage, 10) || 1;
            state.lastPage = parseInt(meta.lastPage, 10) || 1;
            state.perPage = parseInt(meta.perPage, 10) || 20;
            state.total = parseInt(meta.total, 10) || 0;
            state.from = parseInt(meta.from, 10) || 0;

            renderTable();
            renderPager();
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }
            if (requestSequence !== listRequestState.sequence) {
                return;
            }

            state.rows = [];
            renderTable();
            renderPager();
        } finally {
            if (requestSequence === listRequestState.sequence) {
                listRequestState.controller = null;
            }
        }
    }

    function wireEvents() {
        if (els.search) {
            els.search.addEventListener('input', function () {
                if (searchDebounceTimer) {
                    clearTimeout(searchDebounceTimer);
                }
                searchDebounceTimer = setTimeout(function () {
                    fetchRows(1);
                }, 300);
            });

            els.search.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (searchDebounceTimer) {
                        clearTimeout(searchDebounceTimer);
                    }
                    fetchRows(1);
                }
            });
        }

        [els.user, els.module, els.dateFrom, els.dateTo].forEach(function (el) {
            if (el) {
                el.addEventListener('change', function () {
                    fetchRows(1);
                });
            }
        });

        if (els.searchBtn) {
            els.searchBtn.addEventListener('click', function () {
                fetchRows(1);
            });
        }

        if (els.clearBtn) {
            els.clearBtn.addEventListener('click', function () {
                if (els.search) els.search.value = '';
                if (els.user) els.user.value = '';
                if (els.module) els.module.value = '';
                if (els.dateFrom) els.dateFrom.value = '';
                if (els.dateTo) els.dateTo.value = '';
                fetchRows(1);
            });
        }

        if (els.pager) {
            els.pager.addEventListener('click', function (event) {
                var pageBtn = event.target.closest('[data-audit-page]');
                if (pageBtn) {
                    fetchRows(parseInt(pageBtn.getAttribute('data-audit-page'), 10) || 1);
                    return;
                }

                var prevBtn = event.target.closest('[data-audit-page-prev]');
                if (prevBtn && state.currentPage > 1) {
                    fetchRows(state.currentPage - 1);
                    return;
                }

                var nextBtn = event.target.closest('[data-audit-page-next]');
                if (nextBtn && state.currentPage < state.lastPage) {
                    fetchRows(state.currentPage + 1);
                }
            });
        }
    }

    wireEvents();
    fetchRows(1);
})();
