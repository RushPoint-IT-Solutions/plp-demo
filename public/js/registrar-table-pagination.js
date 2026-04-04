(function () {
  'use strict';

  var PAGE_SIZE = 10;
  var BUTTON_WINDOW = 5;
  var stateMap = new WeakMap();
  var watchedTables = new WeakSet();

  function getTableWrap(table) {
    return table.closest('.app-table-wrap, .student-table-wrapper, .table-responsive, .ga-table-wrap') || table.parentElement;
  }

  function shouldSkipTable(table) {
    if (!table) return true;
    if (table.dataset.noAutoPager === '1') return true;
    if (table.closest('.req-modal-overlay')) return true;
    if (table.closest('[id$="PrintContainer"]')) return true;
    if (table.id && table.id.indexOf('cfg') === 0) return true;
    if (table.classList.contains('rep-doc-table')) return true;
    return false;
  }

  function getTableRows(table) {
    var rows = [];

    if (table.tBodies && table.tBodies.length && table.tBodies[0].rows) {
      rows = Array.prototype.slice.call(table.tBodies[0].rows);
    } else if (table.rows && table.rows.length) {
      rows = Array.prototype.filter.call(table.rows, function (row) {
        return !(row.parentElement && row.parentElement.tagName === 'THEAD');
      });
    }

    return rows.filter(function (row) {
      var rowClass = (row.className || '').toString();
      if (row.classList.contains('sc-empty-row')) return false;
      if (row.classList.contains('subject-total-row')) return false;
      if (row.classList.contains('program-total-row')) return false;
      if (/(^|\s)[\w-]*total[\w-]*-row(\s|$)/.test(rowClass)) return false;
      return true;
    });
  }

  function findPagerMount(table) {
    var wrap = getTableWrap(table);
    if (!wrap) return null;

    var probe = wrap.nextElementSibling;
    var hops = 0;

    while (probe && hops < 6) {
      if (probe.classList && (probe.classList.contains('pf-pagination') || probe.classList.contains('rtp-pagination'))) {
        return probe;
      }

      var nestedMount = probe.querySelector ? probe.querySelector('.app-table-pager') : null;
      if (nestedMount && !nestedMount.querySelector('.pagination')) {
        return nestedMount;
      }

      if (probe.classList && probe.classList.contains('app-table-pager') && !probe.querySelector('.pagination')) {
        return probe;
      }

      probe = probe.nextElementSibling;
      hops += 1;
    }

    var created = document.createElement('div');
    created.className = 'rtp-pagination';
    wrap.insertAdjacentElement('afterend', created);
    return created;
  }

  function ensureState(table) {
    if (!stateMap.has(table)) {
      stateMap.set(table, {
        page: 1,
        size: PAGE_SIZE,
        rows: [],
        maxPage: 1,
        mount: findPagerMount(table)
      });
    }
    return stateMap.get(table);
  }

  function setPage(table, page) {
    var st = stateMap.get(table);
    if (!st) return;
    st.page = Math.min(st.maxPage, Math.max(1, page));
    renderTable(table);
  }

  function setPageById(tableId, mode, page) {
    if (!tableId) return;
    var table = document.getElementById(tableId);
    if (!table) return;
    var st = ensureState(table);
    if (!st) return;

    if (mode === 'prev') {
      setPage(table, st.page - 1);
      return;
    }

    if (mode === 'next') {
      setPage(table, st.page + 1);
      return;
    }

    setPage(table, page || 1);
  }

  function buildPageButtons(table, st) {
    var start = Math.max(1, st.page - 2);
    var end = Math.min(st.maxPage, st.page + 2);

    if (st.page <= 3) {
      end = Math.min(st.maxPage, BUTTON_WINDOW);
    } else if (st.page >= st.maxPage - 2) {
      start = Math.max(1, st.maxPage - (BUTTON_WINDOW - 1));
    }

    var html = '';
    for (var p = start; p <= end; p += 1) {
      html += '<button type="button" class="rtp-page-num ' + (p === st.page ? 'active' : '') + '" ' +
        (p === st.page ? 'aria-current="page"' : '') + ' data-rtp-page="' + p + '" ' + (table.id ? 'data-rtp-table="' + table.id + '"' : '') + '>' + p + '</button>';
    }
    return html;
  }

  function renderPager(table) {
    var st = stateMap.get(table);
    if (!st || !st.mount) return;

    if (st.maxPage <= 1) {
      st.mount.innerHTML = '';
      st.mount.style.display = 'none';
      return;
    }

    st.mount.style.display = '';
    if (table.id) {
      st.mount.setAttribute('data-rtp-table', table.id);
    }

    st.mount.classList.add('rtp-pagination');
    st.mount.innerHTML = '' +
      '<nav class="rtp-nav" aria-label="Table pagination">' +
        '<div class="rtp-list" role="group" aria-label="Page controls">' +
          '<button type="button" class="rtp-page-btn" aria-label="Previous page" ' + (st.page <= 1 ? 'disabled' : '') + ' data-rtp-prev="1" ' + (table.id ? 'data-rtp-table="' + table.id + '"' : '') + '>&lt;</button>' +
          buildPageButtons(table, st) +
          '<button type="button" class="rtp-page-btn" aria-label="Next page" ' + (st.page >= st.maxPage ? 'disabled' : '') + ' data-rtp-next="1" ' + (table.id ? 'data-rtp-table="' + table.id + '"' : '') + '>&gt;</button>' +
        '</div>' +
      '</nav>';

    st.mount.onclick = function (event) {
      event.stopPropagation();

      var prevBtn = event.target.closest('[data-rtp-prev]');
      if (prevBtn) {
        setPage(table, st.page - 1);
        return;
      }

      var nextBtn = event.target.closest('[data-rtp-next]');
      if (nextBtn) {
        setPage(table, st.page + 1);
        return;
      }

      var pageBtn = event.target.closest('[data-rtp-page]');
      if (pageBtn) {
        setPage(table, parseInt(pageBtn.getAttribute('data-rtp-page'), 10) || 1);
      }
    };
  }

  function renderRows(table) {
    var st = stateMap.get(table);
    if (!st) return;

    var start = (st.page - 1) * st.size;
    var end = start + st.size;

    if (st.rows.length <= st.size) {
      start = 0;
      end = st.rows.length;
    }

    st.rows.forEach(function (row, idx) {
      row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });
  }

  function refreshTable(table, resetPage) {
    if (shouldSkipTable(table)) return;

    var st = ensureState(table);
    st.rows = getTableRows(table);
    st.maxPage = Math.max(1, Math.ceil(st.rows.length / st.size));

    if (resetPage || st.page > st.maxPage) {
      st.page = 1;
    }

    renderRows(table);
    renderPager(table);
  }

  function renderTable(table) {
    refreshTable(table, false);
  }

  function observeTable(table) {
    if (watchedTables.has(table)) return;
    watchedTables.add(table);

    var body = (table.tBodies && table.tBodies.length) ? table.tBodies[0] : table;
    if (!body) return;

    var t;
    var obs = new MutationObserver(function () {
      window.clearTimeout(t);
      t = window.setTimeout(function () {
        refreshTable(table, true);
      }, 50);
    });
    obs.observe(body, { childList: true, subtree: false });
  }

  function initAutoPagination() {
    var tables = document.querySelectorAll('.student-content table');
    tables.forEach(function (table) {
      if (shouldSkipTable(table)) return;
      refreshTable(table, false);
      observeTable(table);
    });
  }

  function refreshTableById(tableId, resetPage) {
    var table = document.getElementById(tableId);
    if (!table || shouldSkipTable(table)) return;
    refreshTable(table, !!resetPage);
    observeTable(table);
  }

  function observeDocumentForTables() {
    var refreshTimer = null;

    function scheduleRefresh() {
      window.clearTimeout(refreshTimer);
      refreshTimer = window.setTimeout(function () {
        initAutoPagination();
      }, 60);
    }

    var docObserver = new MutationObserver(function (mutations) {
      var shouldRefresh = false;
      for (var i = 0; i < mutations.length; i += 1) {
        var m = mutations[i];
        if (m.type !== 'childList') continue;

        if ((m.addedNodes && m.addedNodes.length) || (m.removedNodes && m.removedNodes.length)) {
          var target = m.target;
          if (target && target.closest) {
            if (target.closest('table, .student-content')) {
              shouldRefresh = true;
              break;
            }
          }

          for (var j = 0; j < m.addedNodes.length; j += 1) {
            var node = m.addedNodes[j];
            if (!node || node.nodeType !== 1) continue;
            if ((node.matches && node.matches('table, tbody, tr')) ||
                (node.querySelector && node.querySelector('table, tbody, tr'))) {
              shouldRefresh = true;
              break;
            }
          }
        }
        if (shouldRefresh) break;
      }

      if (shouldRefresh) {
        scheduleRefresh();
      }
    });

    docObserver.observe(document.body, { childList: true, subtree: true });
  }

  function startAutoPagination() {
    initAutoPagination();
    observeDocumentForTables();

    // Some pages populate table rows shortly after load via inline scripts.
    // Retry a few times to ensure pagers attach to late-rendered rows.
    window.setTimeout(initAutoPagination, 120);
    window.setTimeout(initAutoPagination, 450);
    window.setTimeout(initAutoPagination, 900);
    window.setTimeout(initAutoPagination, 1800);

    window.addEventListener('load', function () {
      initAutoPagination();
      window.setTimeout(initAutoPagination, 300);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startAutoPagination);
  } else {
    startAutoPagination();
  }

  document.addEventListener('click', function (event) {
    var prevBtn = event.target.closest('[data-rtp-prev][data-rtp-table]');
    if (prevBtn) {
      setPageById(prevBtn.getAttribute('data-rtp-table'), 'prev');
      return;
    }

    var nextBtn = event.target.closest('[data-rtp-next][data-rtp-table]');
    if (nextBtn) {
      setPageById(nextBtn.getAttribute('data-rtp-table'), 'next');
      return;
    }

    var pageBtn = event.target.closest('[data-rtp-page][data-rtp-table]');
    if (pageBtn) {
      setPageById(pageBtn.getAttribute('data-rtp-table'), 'page', parseInt(pageBtn.getAttribute('data-rtp-page'), 10) || 1);
    }
  });

  window.registrarTablePagination = {
    refreshAll: initAutoPagination,
    refreshTableById: refreshTableById
  };
})();
