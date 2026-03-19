(function () {
  'use strict';

  var PAGE_SIZE = 5;
  var BUTTON_WINDOW = 5;
  var stateMap = new WeakMap();
  var watchedTables = new WeakSet();

  function shouldSkipTable(table) {
    if (!table) return true;
    if (table.dataset.noAutoPager === '1') return true;
    if (table.id && table.id.indexOf('cfg') === 0) return true;
    if (table.closest('.cfg-page')) return true;
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
      if (row.classList.contains('sc-empty-row')) return false;
      if (row.classList.contains('subject-total-row')) return false;
      if (row.classList.contains('program-total-row')) return false;
      return true;
    });
  }

  function findPagerMount(table) {
    var wrap = table.closest('.app-table-wrap, .student-table-wrapper, .table-responsive, .ga-table-wrap') || table.parentElement;
    if (!wrap) return null;

    var next = wrap.nextElementSibling;
    if (next && (next.classList.contains('pf-pagination') || next.classList.contains('rtp-pagination'))) {
      return next;
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
        (p === st.page ? 'aria-current="page"' : '') + ' data-rtp-page="' + p + '">' + p + '</button>';
    }
    return html;
  }

  function renderPager(table) {
    var st = stateMap.get(table);
    if (!st || !st.mount) return;

    st.mount.classList.add('rtp-pagination');
    st.mount.innerHTML = '' +
      '<nav class="rtp-nav" aria-label="Table pagination">' +
        '<div class="rtp-list" role="group" aria-label="Page controls">' +
          '<button type="button" class="rtp-page-btn" aria-label="Previous page" ' + (st.page <= 1 ? 'disabled' : '') + ' data-rtp-prev="1">&lt;</button>' +
          buildPageButtons(table, st) +
          '<button type="button" class="rtp-page-btn" aria-label="Next page" ' + (st.page >= st.maxPage ? 'disabled' : '') + ' data-rtp-next="1">&gt;</button>' +
        '</div>' +
      '</nav>';

    st.mount.onclick = function (event) {
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
})();
