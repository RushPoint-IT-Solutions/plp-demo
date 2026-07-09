@extends('layouts.registrar')

@section('title', 'PLP - Audit Trail')
@section('page-title', 'AUDIT TRAIL')

@section('content')
<div class="app-process-page" id="auditTrailRoot" data-data-endpoint="{{ $auditTrailDataUrl }}">
    <div class="app-filter-bar" style="margin-bottom: 15px; padding: 12px 16px;">
        <div class="app-filter-row audit-filter-row">
            <div class="app-filter-group search-group">
                <label class="app-filter-label">Search Activity</label>
                <input type="text" id="auditSearchInput" class="app-filter-input w-100" placeholder="Search user, module, action, or details...">
            </div>
            <div class="app-filter-group user-group">
                <label class="app-filter-label">User</label>
                <select id="auditUserFilter" class="app-filter-select w-100">
                    <option value="">All Users</option>
                    @foreach($userOptions ?? [] as $user)
                        <option value="{{ $user['id'] }}">{{ $user['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="app-filter-group module-group">
                <label class="app-filter-label">Module</label>
                <select id="auditModuleFilter" class="app-filter-select w-100">
                    <option value="">All Modules</option>
                    @foreach($moduleOptions ?? [] as $module)
                        <option value="{{ $module['code'] }}">{{ $module['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="app-filter-group date-group">
                <label class="app-filter-label">From Date</label>
                <input type="date" id="auditDateFrom" class="app-filter-input w-100">
            </div>
            <div class="app-filter-group date-group">
                <label class="app-filter-label">To Date</label>
                <input type="date" id="auditDateTo" class="app-filter-input w-100">
            </div>
            <div class="app-filter-group btn-group">
                <button type="button" id="auditSearchBtn" class="apst-new-btn search-btn">Search</button>
                <button type="button" id="auditClearBtn" class="apst-new-btn search-btn" style="background:#6c757d;">Clear</button>
            </div>
        </div>
    </div>

    <div class="app-table-wrap table-responsive">
        <table class="app-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th style="width: 18%;">Timestamp</th>
                    <th style="width: 15%;">User</th>
                    <th style="width: 15%;">Module</th>
                    <th style="width: 32%;">Action</th>
                    <th style="width: 20%;">Details</th>
                </tr>
            </thead>
            <tbody id="auditTrailBody">
                <tr><td colspan="5" class="text-center">Loading audit logs...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="app-table-pager" id="auditTrailPager" style="margin-top: 15px;"></div>
</div>

<style>
    .audit-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }
    .audit-filter-row .search-group { flex: 2; min-width: 200px; }
    .audit-filter-row .user-group { flex: 1; min-width: 150px; }
    .audit-filter-row .module-group { flex: 1; min-width: 150px; }
    .audit-filter-row .date-group { flex: 1; min-width: 130px; }
    .audit-filter-row .btn-group { flex: 0 0 auto; display: flex; gap: 8px; }
    .audit-filter-row .search-btn { height: 38px; min-width: 100px; }

    @media (max-width: 900px) {
        .audit-filter-row .search-group { flex: 1 1 100%; }
        .audit-filter-row .btn-group { flex: 1 1 100%; }
        .audit-filter-row .search-btn { width: 100%; }
    }

    .badge-soft-info {
        background-color: rgba(0, 104, 55, 0.1);
        color: #006837;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .text-muted {
        color: #6c757d !important;
        font-size: 0.85rem;
    }
    .app-table-pager {
        padding-bottom: 0;
        display: flex;
        justify-content: flex-end;
    }
    /* Pull up footer to eliminate the huge gap caused by margin-top: auto */
    .plp-footer {
        margin-top: 20px !important;
        padding: 15px 20px !important;
    }
</style>
@endsection

@push('scripts')
<script src="{{ mix('js/registrar-audit-trail.js') }}"></script>
@endpush
