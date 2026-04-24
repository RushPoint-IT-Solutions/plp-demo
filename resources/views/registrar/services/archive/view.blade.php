@extends('layouts.registrar')

@section('title', 'PLP - View Archive')
@section('page-title', 'ARCHIVE DETAILS')
@section('body-class', 'page-services-archive-view')

@section('content')
<div class="pf-page">
    <div class="archive-view-page">
        <div class="archive-view-header">
            <a href="{{ route('registrar.services.archive.index') }}" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Archives
            </a>
        </div>

        <div class="archive-detail-card">
            <div class="archive-detail-header">
                <h2 class="archive-detail-title">Archive Record #{{ $archive->id }}</h2>
                <span class="badge badge-{{ $archive->status === 'active' ? 'success' : ($archive->status === 'pending_retrieval' ? 'warning' : 'info') }}">
                    {{ ucfirst(str_replace('_', ' ', $archive->status)) }}
                </span>
            </div>

            <div class="archive-detail-grid">
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Record Type</label>
                    <div class="archive-detail-value">{{ $archive->record_type }}</div>
                </div>
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Record ID</label>
                    <div class="archive-detail-value">{{ $archive->record_id }}</div>
                </div>
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Category</label>
                    <div class="archive-detail-value">{{ $archive->category ?? 'N/A' }}</div>
                </div>
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Archived By</label>
                    <div class="archive-detail-value">{{ $archive->archivedBy->name ?? 'System' }}</div>
                </div>
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Archived Date</label>
                    <div class="archive-detail-value">{{ $archive->created_at->format('M d, Y H:i') }}</div>
                </div>
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Scheduled Disposal</label>
                    <div class="archive-detail-value">{{ $archive->scheduled_disposal_at ? $archive->scheduled_disposal_at->format('M d, Y') : 'N/A' }}</div>
                </div>
                @if($archive->reason)
                <div class="archive-detail-item full-width">
                    <label class="archive-detail-label">Archive Reason</label>
                    <div class="archive-detail-value">{{ $archive->reason }}</div>
                </div>
                @endif
                @if($archive->metadata)
                <div class="archive-detail-item full-width">
                    <label class="archive-detail-label">Metadata</label>
                    <div class="archive-detail-value">
                        <pre class="metadata-pre">{{ json_encode($archive->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>

            <div class="archive-detail-actions">
                @if($archive->status === 'active')
                <button type="button" class="apst-new-btn" id="requestRetrievalBtn">Request Retrieval</button>
                @if(!$archive->scheduled_disposal_at)
                <button type="button" class="apst-new-btn" id="scheduleDisposalBtn">Schedule Disposal</button>
                @endif
                @endif
            </div>
        </div>

        @if($archive->retrievalRequests->count() > 0)
        <div class="archive-section">
            <h3 class="archive-section-title">Retrieval History</h3>
            <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table app-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Requested By</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archive->retrievalRequests as $request)
                        <tr>
                            <td>#{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $request->requestedBy->name ?? 'N/A' }}</td>
                            <td>{{ $request->reason }}</td>
                            <td>
                                <span class="badge badge-{{ $request->status === 'pending' ? 'warning' : ($request->status === 'approved' ? 'success' : ($request->status === 'fulfilled' ? 'info' : 'danger')) }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($archive->disposalRecord)
        <div class="archive-section">
            <h3 class="archive-section-title">Disposal Information</h3>
            <div class="archive-detail-grid">
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Scheduled Date</label>
                    <div class="archive-detail-value">{{ $archive->disposalRecord->scheduled_date->format('M d, Y') }}</div>
                </div>
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Method</label>
                    <div class="archive-detail-value">{{ ucfirst(str_replace('_', ' ', $archive->disposalRecord->disposal_method)) }}</div>
                </div>
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Status</label>
                    <div class="archive-detail-value">
                        <span class="badge badge-{{ $archive->disposalRecord->status === 'pending' ? 'warning' : ($archive->disposalRecord->status === 'completed' ? 'success' : 'danger') }}">
                            {{ ucfirst($archive->disposalRecord->status) }}
                        </span>
                    </div>
                </div>
                @if($archive->disposalRecord->certificate)
                <div class="archive-detail-item">
                    <label class="archive-detail-label">Certificate</label>
                    <div class="archive-detail-value">
                        <a href="{{ route('registrar.services.archive.disposal-queue.certificate', $archive) }}" target="_blank" class="apst-new-btn">View Certificate</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Retrieval Request Modal -->
<div class="req-modal-overlay" id="retrievalModal" style="display:none;">
    <div class="req-modal-box" style="max-width:500px;">
        <h3 class="req-modal-title">REQUEST ARCHIVE RETRIEVAL</h3>
        <div class="req-modal-fields">
            <div class="req-modal-field-group">
                <label class="req-modal-label">REASON</label>
                <textarea class="req-modal-input" id="retrievalReason" rows="4" placeholder="Enter reason for retrieval"></textarea>
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" id="closeRetrievalModal">Cancel</button>
            <button type="button" class="req-btn-save" id="submitRetrievalRequest">Submit Request</button>
        </div>
    </div>
</div>

<!-- Disposal Modal -->
<div class="req-modal-overlay" id="disposalModal" style="display:none;">
    <div class="req-modal-box" style="max-width:500px;">
        <h3 class="req-modal-title">SCHEDULE DISPOSAL</h3>
        <div class="req-modal-fields">
            <div class="req-modal-field-group">
                <label class="req-modal-label">DISPOSAL METHOD</label>
                <select class="req-modal-input" id="disposalMethod">
                    <option value="secure_delete">Secure Delete</option>
                    <option value="anonymize">Anonymize</option>
                    <option value="export_then_delete">Export then Delete</option>
                </select>
            </div>
        </div>
        <div class="req-modal-actions">
            <button type="button" class="req-btn-cancel" id="closeDisposalModal">Cancel</button>
            <button type="button" class="req-btn-save" id="submitDisposalSchedule">Schedule</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    var archiveId = {{ $archive->id }};

    // Request retrieval
    document.getElementById('requestRetrievalBtn').addEventListener('click', function() {
        document.getElementById('retrievalModal').style.display = 'flex';
    });

    document.getElementById('closeRetrievalModal').addEventListener('click', function() {
        document.getElementById('retrievalModal').style.display = 'none';
    });

    document.getElementById('submitRetrievalRequest').addEventListener('click', function() {
        var reason = document.getElementById('retrievalReason').value;
        if (!reason) {
            alert('Please enter a reason.');
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

    // Schedule disposal
    document.getElementById('scheduleDisposalBtn').addEventListener('click', function() {
        document.getElementById('disposalModal').style.display = 'flex';
    });

    document.getElementById('closeDisposalModal').addEventListener('click', function() {
        document.getElementById('disposalModal').style.display = 'none';
    });

    document.getElementById('submitDisposalSchedule').addEventListener('click', function() {
        var method = document.getElementById('disposalMethod').value;

        fetch('/services/archive/disposal-queue/' + archiveId + '/schedule', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ disposal_method: method })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error scheduling disposal');
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.archive-view-header {
    margin-bottom: 20px;
}
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #006837;
    text-decoration: none;
    font-size: 0.9rem;
}
.back-link:hover {
    text-decoration: underline;
}
.archive-detail-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
}
.archive-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e0e0e0;
}
.archive-detail-title {
    font-size: 1.25rem;
    color: #333;
    margin: 0;
}
.archive-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}
.archive-detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.archive-detail-item.full-width {
    grid-column: 1 / -1;
}
.archive-detail-label {
    font-size: 0.75rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.archive-detail-value {
    font-size: 0.95rem;
    color: #333;
}
.metadata-pre {
    background: #f5f5f5;
    padding: 12px;
    border-radius: 4px;
    font-size: 0.85rem;
    overflow-x: auto;
    margin: 0;
}
.archive-detail-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}
.archive-section {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
}
.archive-section-title {
    font-size: 1rem;
    color: #333;
    margin: 0 0 16px 0;
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
</style>
@endpush