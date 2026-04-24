@extends('layouts.registrar')

@section('title', 'PLP - Document Archive')
@section('page-title', 'DOCUMENT ARCHIVE')
@section('body-class', 'page-services-archive')

@section('content')
<div class="pf-page">
    <div class="archive-page">
        <div class="apst-topbar">
            <div class="apst-search-box">
                <input type="text" class="apst-search-input" placeholder="Search archives..." id="archiveSearchInput">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="apst-search-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <div class="apst-topbar-actions">
                <button type="button" class="apst-new-btn" data-archive-modal-open="archiveNewRequestModal">+ New Archive</button>
            </div>
        </div>

        <div class="archive-tabs">
            <button class="archive-tab active" data-tab="archives">Archives</button>
            <button class="archive-tab" data-tab="retrieval">Retrieval Requests</button>
            <button class="archive-tab" data-tab="disposal">Disposal Queue</button>
            <button class="archive-tab" data-tab="policies">Retention Policies</button>
            <button class="archive-tab" data-tab="logs">Access Logs</button>
        </div>

        <div class="archive-content" id="archiveContent">
            <div class="archive-panel active" id="archivesPanel">
                <div class="ga-table-wrap app-table-wrap">
                    <table class="ga-table app-table" id="archivesTable">
                        <thead>
                            <tr>
                                <th style="text-align:center;">Action</th>
                                <th>Record Type</th>
                                <th>Record ID</th>
                                <th>Category</th>
                                <th>Archived By</th>
                                <th>Archived Date</th>
                                <th>Scheduled Disposal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($archives as $index => $archive)
                            <tr data-archive-id="{{ $archive->id }}">
                                <td>
                                    <div class="apst-action-btn" data-archive-menu-toggle="archiveMenu{{ $index }}" aria-label="Open row actions" title="Actions">
                                        <span></span><span></span><span></span>
                                    </div>
                                    <div class="apst-dropdown" id="archiveMenu{{ $index }}">
                                        <button type="button" data-archive-open-action="view" data-archive-id="{{ $archive->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            View
                                        </button>
                                        <button type="button" data-archive-open-action="retrieve" data-archive-id="{{ $archive->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Request Retrieval
                                        </button>
                                        @if($archive->status !== 'pending_disposal')
                                        <button type="button" data-archive-open-action="dispose" data-archive-id="{{ $archive->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                            Schedule Disposal
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $archive->record_type }}</td>
                                <td>{{ $archive->record_id }}</td>
                                <td>{{ $archive->category ?? 'N/A' }}</td>
                                <td>{{ $archive->archivedBy->name ?? 'System' }}</td>
                                <td>{{ $archive->created_at->format('M d, Y') }}</td>
                                <td>{{ $archive->scheduled_disposal_at ? $archive->scheduled_disposal_at->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-{{ $archive->status === 'active' ? 'success' : ($archive->status === 'pending_retrieval' ? 'warning' : 'info') }}">
                                        {{ ucfirst(str_replace('_', ' ', $archive->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No archived records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($archives->hasPages())
                <div class="pagination-wrapper">
                    {{ $archives->links() }}
                </div>
                @endif
            </div>

            <div class="archive-panel" id="retrievalPanel">
                <div class="ga-table-wrap app-table-wrap">
                    <table class="ga-table app-table" id="retrievalTable">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Archive</th>
                                <th>Requested By</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Requested Date</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($retrievalRequests as $request)
                            <tr data-request-id="{{ $request->id }}">
                                <td>#{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $request->archive->record_type }} #{{ $request->archive->record_id }}</td>
                                <td>{{ $request->requestedBy->name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($request->reason, 30) }}</td>
                                <td>
                                    <span class="badge badge-{{ $request->status === 'pending' ? 'warning' : ($request->status === 'approved' ? 'success' : ($request->status === 'fulfilled' ? 'info' : 'danger')) }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($request->status === 'pending')
                                    <div class="apst-action-btn" data-archive-menu-toggle="reqMenu{{ $request->id }}" aria-label="Open row actions">
                                        <span></span><span></span><span></span>
                                    </div>
                                    <div class="apst-dropdown" id="reqMenu{{ $request->id }}">
                                        <button type="button" data-archive-open-action="approve-request" data-request-id="{{ $request->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Approve
                                        </button>
                                        <button type="button" data-archive-open-action="reject-request" data-request-id="{{ $request->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            Reject
                                        </button>
                                    </div>
                                    @elseif($request->status === 'approved')
                                    <button type="button" class="apst-new-btn" data-archive-open-action="fulfill-request" data-request-id="{{ $request->id }}">Fulfill</button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No retrieval requests found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="archive-panel" id="disposalPanel">
                <div class="ga-table-wrap app-table-wrap">
                    <table class="ga-table app-table" id="disposalTable">
                        <thead>
                            <tr>
                                <th>Archive</th>
                                <th>Scheduled Date</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Certificate</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($disposalQueue as $item)
                            <tr data-disposal-id="{{ $item->id }}">
                                <td>{{ $item->archive->record_type }} #{{ $item->archive->record_id }}</td>
                                <td>{{ $item->scheduled_date->format('M d, Y') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $item->disposal_method)) }}</td>
                                <td>
                                    <span class="badge badge-{{ $item->status === 'pending' ? 'warning' : ($item->status === 'completed' ? 'success' : 'danger') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->status === 'completed' && $item->certificate)
                                    <a href="{{ route('registrar.services.archive.disposal-queue.certificate', $item->archive) }}" target="_blank" class="apst-new-btn">View</a>
                                    @elseif($item->status === 'completed')
                                    <span class="text-muted">N/A</span>
                                    @else
                                    <span class="text-muted">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status === 'pending')
                                    <button type="button" class="apst-new-btn" data-archive-open-action="cancel-disposal" data-disposal-id="{{ $item->id }}">Cancel</button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No items in disposal queue.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="archive-panel" id="policiesPanel">
                <div class="apst-topbar" style="margin-bottom:16px;">
                    <div></div>
                    <button type="button" class="apst-new-btn" data-archive-modal-open="policyNewModal">+ New Policy</button>
                </div>
                <div class="ga-table-wrap app-table-wrap">
                    <table class="ga-table app-table" id="policiesTable">
                        <thead>
                            <tr>
                                <th style="text-align:center;">Action</th>
                                <th>Record Type</th>
                                <th>Retention Period</th>
                                <th>Trigger</th>
                                <th>Disposal Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($retentionPolicies as $policy)
                            <tr data-policy-id="{{ $policy->id }}">
                                <td>
                                    <div class="apst-action-btn" data-archive-menu-toggle="policyMenu{{ $policy->id }}" aria-label="Open row actions">
                                        <span></span><span></span><span></span>
                                    </div>
                                    <div class="apst-dropdown" id="policyMenu{{ $policy->id }}">
                                        <button type="button" data-archive-open-action="edit-policy" data-policy-id="{{ $policy->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Edit
                                        </button>
                                        <button type="button" class="apst-del-btn" data-archive-open-action="delete-policy" data-policy-id="{{ $policy->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                                <td>{{ $policy->record_type }}</td>
                                <td>{{ $policy->retention_months }} months</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $policy->trigger_event)) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $policy->disposal_method)) }}</td>
                                <td>
                                    <span class="badge badge-{{ $policy->is_active ? 'success' : 'secondary' }}">
                                        {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No retention policies found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="archive-panel" id="logsPanel">
                <div class="ga-table-wrap app-table-wrap">
                    <table class="ga-table app-table" id="logsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Archive</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accessLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                                <td>{{ $log->archive_id ? $log->archive->record_type . ' #' . $log->archive->record_id : 'N/A' }}</td>
                                <td>{{ $log->ip_address ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No access logs found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($accessLogs) && $accessLogs->hasPages())
                <div class="pagination-wrapper">
                    {{ $accessLogs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Retrieval Request Modal -->
<div class="req-modal-overlay" id="archiveNewRequestModal" style="display:none;">
    <div class="req-modal-box" style="max-width:500px;">
        <h3 class="req-modal-title">REQUEST ARCHIVE RETRIEVAL</h3>
        <div class="req-modal-fields">
            <div class="req-modal-field-group">
                <label class="req-modal-label">ARCHIVE ID</label>
                <input type="number" class="req-modal-input" id="archiveRequestId" placeholder="Enter archive ID">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">REASON</label>
                <textarea class="req-modal-input" id="archiveRequestReason" rows="4" placeholder="Enter reason for retrieval"></textarea>
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" data-archive-close>Cancel</button>
            <button type="button" class="req-btn-save" data-archive-save-request>Submit Request</button>
        </div>
    </div>
</div>

<!-- Policy Modal -->
<div class="req-modal-overlay" id="policyNewModal" style="display:none;">
    <div class="req-modal-box" style="max-width:600px;">
        <h3 class="req-modal-title" id="policyModalTitle">ADD RETENTION POLICY</h3>
        <div class="req-modal-fields">
            <div class="req-modal-field-group">
                <label class="req-modal-label">RECORD TYPE</label>
                <input type="text" class="req-modal-input" id="policyRecordType" placeholder="e.g., student_grades, enrollment_records">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">RETENTION PERIOD (MONTHS)</label>
                <input type="number" class="req-modal-input" id="policyRetentionMonths" placeholder="e.g., 84">
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">TRIGGER EVENT</label>
                <select class="req-modal-input" id="policyTriggerEvent">
                    <option value="end_of_academic_year">End of Academic Year</option>
                    <option value="end_of_semester">End of Semester</option>
                    <option value="graduation">Graduation</option>
                    <option value="separation">Separation</option>
                    <option value="inactivity">Inactivity</option>
                    <option value="manual">Manual</option>
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">DISPOSAL METHOD</label>
                <select class="req-modal-input" id="policyDisposalMethod">
                    <option value="secure_delete">Secure Delete</option>
                    <option value="anonymize">Anonymize</option>
                    <option value="export_then_delete">Export then Delete</option>
                </select>
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" data-archive-close-policy>Cancel</button>
            <button type="button" class="req-btn-save" data-archive-save-policy>Save</button>
        </div>
    </div>
</div>

@include('includes.registrar-delete-modal', [
    'id' => 'archiveDeleteModal',
    'title' => 'DELETE RETENTION POLICY',
    'message' => 'Are you sure you want to delete this retention policy?',
    'confirmBtnText' => 'Delete',
    'cancelActionAttr' => 'data-archive-close-delete',
    'confirmActionAttr' => 'data-archive-confirm-delete'
])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.querySelector('.archive-page');
    if (!page) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

    // Tab switching
    document.querySelectorAll('.archive-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.archive-tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.archive-panel').forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            var panelId = this.getAttribute('data-tab') + 'Panel';
            document.getElementById(panelId).classList.add('active');
        });
    });

    // Modal open/close handlers
    document.querySelectorAll('[data-archive-modal-open]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modalId = this.getAttribute('data-archive-modal-open');
            document.getElementById(modalId).style.display = 'flex';
        });
    });

    document.querySelectorAll('[data-archive-close]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('archiveNewRequestModal').style.display = 'none';
        });
    });

    document.querySelectorAll('[data-archive-close-policy]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('policyNewModal').style.display = 'none';
        });
    });

    // Dropdown menus
    document.querySelectorAll('[data-archive-menu-toggle]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var menuId = this.getAttribute('data-archive-menu-toggle');
            document.querySelectorAll('.apst-dropdown').forEach(function(m) { m.style.display = 'none'; });
            document.getElementById(menuId).style.display = 'block';
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.apst-dropdown').forEach(function(m) { m.style.display = 'none'; });
    });

    // Search functionality
    var searchInput = document.getElementById('archiveSearchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            var query = this.value.toLowerCase();
            document.querySelectorAll('#archivesTable tbody tr').forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // Submit retrieval request
    var saveRequestBtn = document.querySelector('[data-archive-save-request]');
    if (saveRequestBtn) {
        saveRequestBtn.addEventListener('click', function() {
            var archiveId = document.getElementById('archiveRequestId').value;
            var reason = document.getElementById('archiveRequestReason').value;

            if (!archiveId || !reason) {
                alert('Please fill in all fields.');
                return;
            }

            fetch('/services/archive/retrieval-requests', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ archive_id: archiveId, reason: reason })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error submitting request');
                }
            });
        });
    }

    // Save policy
    var savePolicyBtn = document.querySelector('[data-archive-save-policy]');
    if (savePolicyBtn) {
        savePolicyBtn.addEventListener('click', function() {
            var recordType = document.getElementById('policyRecordType').value;
            var retentionMonths = document.getElementById('policyRetentionMonths').value;
            var triggerEvent = document.getElementById('policyTriggerEvent').value;
            var disposalMethod = document.getElementById('policyDisposalMethod').value;

            if (!recordType || !retentionMonths) {
                alert('Please fill in all required fields.');
                return;
            }

            fetch('/services/archive/retention-policies', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    record_type: recordType,
                    retention_months: retentionMonths,
                    trigger_event: triggerEvent,
                    disposal_method: disposalMethod
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error saving policy');
                }
            });
        });
    }

    // Action handlers
    document.querySelectorAll('[data-archive-open-action]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var action = this.getAttribute('data-archive-open-action');
            var id = this.getAttribute('data-archive-id') || this.getAttribute('data-request-id') || this.getAttribute('data-disposal-id') || this.getAttribute('data-policy-id');

            if (action === 'view' && id) {
                window.location.href = '/services/archive/view/' + id;
            } else if (action === 'retrieve' && id) {
                document.getElementById('archiveRequestId').value = id;
                document.getElementById('archiveNewRequestModal').style.display = 'flex';
            } else if (action === 'approve-request' && id) {
                fetch('/services/archive/retrieval-requests/' + id + '/approve', {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) location.reload();
                    else alert(data.message || 'Error approving request');
                });
            } else if (action === 'reject-request' && id) {
                fetch('/services/archive/retrieval-requests/' + id + '/reject', {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) location.reload();
                    else alert(data.message || 'Error rejecting request');
                });
            } else if (action === 'fulfill-request' && id) {
                fetch('/services/archive/retrieval-requests/' + id + '/fulfill', {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) location.reload();
                    else alert(data.message || 'Error fulfilling request');
                });
            } else if (action === 'cancel-disposal' && id) {
                fetch('/services/archive/disposal-queue/' + id + '/cancel', {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) location.reload();
                    else alert(data.message || 'Error canceling disposal');
                });
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.archive-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e0e0e0;
}
.archive-tab {
    padding: 10px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    color: #666;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}
.archive-tab:hover {
    color: #006837;
}
.archive-tab.active {
    color: #006837;
    border-bottom-color: #006837;
    font-weight: 600;
}
.archive-panel {
    display: none;
}
.archive-panel.active {
    display: block;
}
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}
.badge-success { background: #d4edda; color: #155724; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-danger { background: #f8d7da; color: #721c24; }
.badge-info { background: #d1ecf1; color: #0c5460; }
.badge-secondary { background: #e2e3e5; color: #383d41; }
.pagination-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}
</style>
@endpush