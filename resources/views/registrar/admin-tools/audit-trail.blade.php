@extends('layouts.registrar')

@section('title', 'PLP - Audit Trail')
@section('page-title', 'AUDIT TRAIL')

@section('content')
<div class="app-process-page">
    <div class="app-filter-bar" style="margin-bottom: 15px; padding: 12px 16px;">
        <div class="app-filter-row audit-filter-row">
            <div class="app-filter-group search-group">
                <label class="app-filter-label">Search Activity</label>
                <input type="text" class="app-filter-input w-100" placeholder="Search action or details...">
            </div>
            <div class="app-filter-group user-group">
                <label class="app-filter-label">User</label>
                <select class="app-filter-select w-100">
                    <option value="">All Users</option>
                    <option value="Admin User">Admin User</option>
                    <option value="Registrar Staff">Registrar Staff</option>
                    <option value="System">System</option>
                </select>
            </div>
            <div class="app-filter-group module-group">
                <label class="app-filter-label">Module</label>
                <select class="app-filter-select w-100">
                    <option value="">All Modules</option>
                    <option value="medical">Medical Clearance</option>
                    <option value="application">Application List</option>
                    <option value="academic">Academic Master</option>
                    <option value="access">Access Management</option>
                </select>
            </div>
            <div class="app-filter-group date-group">
                <label class="app-filter-label">Date Range</label>
                <input type="date" class="app-filter-input w-100">
            </div>
            <div class="app-filter-group btn-group">
                <button type="button" class="apst-new-btn search-btn">Search</button>
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
            <tbody>
                @forelse($auditLogs as $log)
                <tr>
                    <td><span class="text-muted">{{ $log['timestamp'] }}</span></td>
                    <td><strong>{{ $log['user'] }}</strong></td>
                    <td><span class="badge badge-soft-info">{{ $log['module'] }}</span></td>
                    <td>{{ $log['action'] }}</td>
                    <td><small class="text-muted">{{ $log['details'] }}</small></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No audit logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Simulated Pagination --}}
    <div class="app-table-pager" style="margin-top: 15px;">
        <div class="rtp-pagination">
            <nav class="rtp-nav" aria-label="Audit Trail pagination">
                <div class="rtp-list" role="group" aria-label="Page controls">
                    <button type="button" class="rtp-page-btn" disabled>&lt;</button>
                    <div class="rtp-pages">
                        <button type="button" class="rtp-page-num active">1</button>
                        <button type="button" class="rtp-page-num">2</button>
                        <button type="button" class="rtp-page-num">3</button>
                    </div>
                    <button type="button" class="rtp-page-btn">&gt;</button>
                </div>
            </nav>
        </div>
    </div>
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
    .audit-filter-row .date-group { flex: 1; min-width: 150px; }
    .audit-filter-row .btn-group { flex: 0 0 auto; }
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
