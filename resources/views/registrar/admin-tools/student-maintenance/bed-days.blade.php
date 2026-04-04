@extends('layouts.registrar')

@section('title', 'PLP - BED Days')
@section('page-title', 'BED DAYS')
@section('body-class', 'page-bed-days')



@section('content')
<div class="pf-page">
    <div class="bd-page">
        <section class="cfg-card">
            <div class="bd-toolbar">
                <div class="bd-field">
                    <label class="app-filter-label" for="bdSY">SY</label>
                    <input id="bdSY" class="app-filter-input" type="text" value="2025-2026">
                </div>
                <div class="bd-field">
                    <label class="app-filter-label" for="bdSem">Sem</label>
                    <select id="bdSem" class="app-filter-select">
                        <option>First</option>
                        <option>Second</option>
                    </select>
                </div>
                <div class="bd-field">
                    <label class="app-filter-label" for="bdMonth">Month</label>
                    <input id="bdMonth" class="app-filter-input" type="text" placeholder="Month">
                </div>
                <div class="bd-field">
                    <label class="app-filter-label" for="bdDays">No. of Days</label>
                    <input id="bdDays" class="app-filter-input" type="number" min="0" placeholder="0">
                </div>
                <button type="button" class="pf-btn-new" id="bdSaveBtn">Save</button>
            </div>
        </section>

        <section>
            <div class="app-table-wrap">
                <table id="bdTable" class="app-table cfg-table" data-no-auto-pager="1">
                    <thead>
                        <tr>
                            <th style="width:54px;">#</th>
                            <th>SY</th>
                            <th>Semester</th>
                            <th>Month</th>
                            <th>No. Of Days</th>
                            <th style="width:110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bdTableBody"></tbody>
                </table>
            </div>

            <div class="app-table-pager"></div>
        </section>
    </div>
</div>

<div class="req-modal-overlay" id="bdEditModal" style="display:none;" onclick="if(event.target===this) bdCloseEditModal()">
    <div class="req-modal-box" style="max-width:560px;">
        <h3 class="req-modal-title">EDIT BED DAYS RECORD</h3>
        <input type="hidden" id="bdEditId" value="">

        <div class="sc-modal-grid-3" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">SY</label>
                <input id="bdEditSY" class="req-modal-input" type="text">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Semester</label>
                <select id="bdEditSem" class="req-modal-input">
                    <option>First</option>
                    <option>Second</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Month</label>
                <input id="bdEditMonth" class="req-modal-input" type="text">
            </div>
        </div>

        <div class="sc-modal-grid" style="margin-top:10px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">No. Of Days</label>
                <input id="bdEditDays" class="req-modal-input" type="number" min="0">
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="bdCloseEditModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="bdSaveEdit()">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="bdDeleteModal" style="display:none;" onclick="if(event.target===this) bdCloseDeleteModal()">
    <div class="req-modal-box req-modal-success" style="max-width:360px; min-width:300px;">
        <h3 class="req-modal-title" style="color:#c0392b;">DELETE BED DAYS RECORD</h3>
        <p style="text-align:center; color:#444; margin-bottom:14px;">Are you sure you want to delete this record?</p>
        <input type="hidden" id="bdDeleteId" value="">
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="bdCloseDeleteModal()">Cancel</button>
            <button type="button" class="req-btn-save" style="background:#c0392b;" onclick="bdConfirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var bdRows = @json($bedDayRows ?? []);
    var bdStoreUrl = '{{ route('registrar.admin-tools.student-maintenance.bed-days.store') }}';
    var bdUpdateTemplate = '{{ route('registrar.admin-tools.student-maintenance.bed-days.update', ['bedDay' => '__ID__']) }}';
    var bdDeleteTemplate = '{{ route('registrar.admin-tools.student-maintenance.bed-days.destroy', ['bedDay' => '__ID__']) }}';
    var bdCurrentPage = 1;
    var bdPageSize = 10;

    function bdBuildUrl(template, id) {
        return template.replace('__ID__', String(id));
    }

    async function bdRequestJson(url, method, payload) {
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
                'Unable to process BED Days request.'
            );
        }

        return json;
    }

    function bdEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function bdBuildMenu(menuId, id) {
        return '' +
            '<div class="apst-action-btn" data-bd-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="bdOpenEditModal(\'' + bdEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit</button>' +
                '<button type="button" class="apst-del-btn" onclick="bdOpenDeleteModal(\'' + bdEscapeHtml(id) + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>Delete</button>' +
            '</div>';
    }

    function bdCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function bdToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;

        var isOpen = menu.classList.contains('open');
        bdCloseActionMenus();
        if (isOpen) return;

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

    function bdRenderPager(totalRows) {
        var mount = document.querySelector('.app-table-pager');
        if (!mount) return;

        var maxPage = Math.max(1, Math.ceil(totalRows / bdPageSize));
        if (bdCurrentPage > maxPage) {
            bdCurrentPage = maxPage;
        }

        if (totalRows <= bdPageSize) {
            mount.innerHTML = '';
            return;
        }

        var start = Math.max(1, bdCurrentPage - 2);
        var end = Math.min(maxPage, bdCurrentPage + 2);
        if (bdCurrentPage <= 3) {
            end = Math.min(maxPage, 5);
        } else if (bdCurrentPage >= maxPage - 2) {
            start = Math.max(1, maxPage - 4);
        }

        var pageNums = '';
        for (var p = start; p <= end; p += 1) {
            pageNums += '<button type="button" class="rtp-page-num ' + (p === bdCurrentPage ? 'active' : '') + '" data-bd-page="' + p + '">' + p + '</button>';
        }

        mount.innerHTML = '' +
            '<div class="rtp-pagination">' +
                '<nav class="rtp-nav" aria-label="BED days pagination">' +
                    '<div class="rtp-list" role="group" aria-label="Page controls">' +
                        '<button type="button" class="rtp-page-btn" data-bd-page-prev="1" ' + (bdCurrentPage <= 1 ? 'disabled' : '') + '>&lt;</button>' +
                        pageNums +
                        '<button type="button" class="rtp-page-btn" data-bd-page-next="1" ' + (bdCurrentPage >= maxPage ? 'disabled' : '') + '>&gt;</button>' +
                    '</div>' +
                '</nav>' +
            '</div>';
    }

    function bdRenderTable() {
        var tbody = document.getElementById('bdTableBody');
        if (!tbody) return;

        bdCloseActionMenus();

        var maxPage = Math.max(1, Math.ceil(bdRows.length / bdPageSize));
        if (bdCurrentPage > maxPage) {
            bdCurrentPage = 1;
        }
        var startIndex = (bdCurrentPage - 1) * bdPageSize;
        var pageItems = bdRows.slice(startIndex, startIndex + bdPageSize);

        var html = pageItems.map(function(row, idx) {
            var menuId = 'bdMenu' + idx;
            return '' +
                '<tr>' +
                    '<td>' + (startIndex + idx + 1) + '</td>' +
                    '<td>' + bdEscapeHtml(row.sy) + '</td>' +
                    '<td>' + bdEscapeHtml(row.sem) + '</td>' +
                    '<td>' + bdEscapeHtml(row.month) + '</td>' +
                    '<td>' + bdEscapeHtml(row.days) + '</td>' +
                    '<td style="text-align:center;">' + bdBuildMenu(menuId, row.id) + '</td>' +
                '</tr>';
        }).join('');

        if (!html) {
            html = '<tr><td colspan="6" class="sc-empty-row">No data listed.</td></tr>';
        }

        tbody.innerHTML = html + '<tr class="bd-total-row"><td colspan="6">Total Records: <strong>' + bdRows.length + '</strong></td></tr>';
        bdRenderPager(bdRows.length);
    }

    function bdOpenEditModal(id) {
        var row = bdRows.find(function(item) { return item.id === id; });
        if (!row) return;
        bdCloseActionMenus();
        document.getElementById('bdEditId').value = row.id;
        document.getElementById('bdEditSY').value = row.sy;
        document.getElementById('bdEditSem').value = row.sem;
        document.getElementById('bdEditMonth').value = row.month;
        document.getElementById('bdEditDays').value = row.days;
        document.getElementById('bdEditModal').style.display = 'flex';
    }

    function bdCloseEditModal() {
        document.getElementById('bdEditModal').style.display = 'none';
    }

    async function bdSaveEdit() {
        var id = document.getElementById('bdEditId').value;
        var sy = (document.getElementById('bdEditSY').value || '').trim();
        var sem = document.getElementById('bdEditSem').value;
        var month = (document.getElementById('bdEditMonth').value || '').trim();
        var days = (document.getElementById('bdEditDays').value || '').trim();

        if (!sy || !month || !days) {
            alert('Please fill in SY, Month, and No. of Days.');
            return;
        }

        try {
            await bdRequestJson(bdBuildUrl(bdUpdateTemplate, id), 'PUT', {
                school_year: sy,
                semester: sem,
                month_name: month,
                number_of_days: parseInt(days, 10)
            });
        } catch (error) {
            alert(error.message || 'Unable to update BED Days record.');
            return;
        }

        bdRows = bdRows.map(function(item) {
            if (String(item.id) !== String(id)) return item;
            return { id: item.id, sy: sy, sem: sem, month: month, days: days };
        });

        bdCloseEditModal();
        bdRenderTable();
    }

    function bdOpenDeleteModal(id) {
        bdCloseActionMenus();
        document.getElementById('bdDeleteId').value = id;
        document.getElementById('bdDeleteModal').style.display = 'flex';
    }

    function bdCloseDeleteModal() {
        document.getElementById('bdDeleteModal').style.display = 'none';
    }

    async function bdConfirmDelete() {
        var id = document.getElementById('bdDeleteId').value;
        try {
            await bdRequestJson(bdBuildUrl(bdDeleteTemplate, id), 'DELETE');
        } catch (error) {
            alert(error.message || 'Unable to delete BED Days record.');
            return;
        }

        bdRows = bdRows.filter(function(item) { return String(item.id) !== String(id); });
        bdCloseDeleteModal();
        bdRenderTable();
    }

    document.getElementById('bdSaveBtn').addEventListener('click', async function() {
        var month = (document.getElementById('bdMonth').value || '').trim();
        var days = (document.getElementById('bdDays').value || '').trim();
        if (!month || !days) {
            alert('Please fill in Month and No. of Days.');
            return;
        }

        var payload = {
            school_year: document.getElementById('bdSY').value,
            semester: document.getElementById('bdSem').value,
            month_name: month,
            number_of_days: parseInt(days, 10)
        };

        try {
            var result = await bdRequestJson(bdStoreUrl, 'POST', payload);
            bdRows.unshift(result.row || {
                id: Date.now(),
                sy: payload.school_year,
                sem: payload.semester,
                month: payload.month_name,
                days: String(payload.number_of_days)
            });
        } catch (error) {
            alert(error.message || 'Unable to save BED Days record.');
            return;
        }

        document.getElementById('bdMonth').value = '';
        document.getElementById('bdDays').value = '';
        bdRenderTable();
    });

    document.addEventListener('click', function(event) {
        var menuToggle = event.target.closest('[data-bd-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            bdToggleActionMenu(menuToggle.getAttribute('data-bd-menu-toggle'), menuToggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            bdCloseActionMenus();
        }
    });

    window.addEventListener('scroll', bdCloseActionMenus, true);

    document.querySelector('.app-table-pager').addEventListener('click', function(event) {
        var prev = event.target.closest('[data-bd-page-prev]');
        if (prev && bdCurrentPage > 1) {
            bdCurrentPage -= 1;
            bdRenderTable();
            return;
        }

        var next = event.target.closest('[data-bd-page-next]');
        if (next) {
            var maxPage = Math.max(1, Math.ceil(bdRows.length / bdPageSize));
            if (bdCurrentPage < maxPage) {
                bdCurrentPage += 1;
                bdRenderTable();
            }
            return;
        }

        var pageBtn = event.target.closest('[data-bd-page]');
        if (pageBtn) {
            bdCurrentPage = parseInt(pageBtn.getAttribute('data-bd-page'), 10) || 1;
            bdRenderTable();
        }
    });

    bdRenderTable();
</script>
@endpush

