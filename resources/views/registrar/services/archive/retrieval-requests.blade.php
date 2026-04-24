@extends('layouts.registrar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Retrieval Requests</h3>
                    <div class="card-tools">
                        <a href="{{ route('archive.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Archive</th>
                                    <th>Record Type</th>
                                    <th>Requested By</th>
                                    <th>Request Date</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($retrievalRequests as $request)
                                <tr>
                                    <td>#{{ $request->id }}</td>
                                    <td>
                                        @if($request->archive)
                                            <a href="{{ route('archive.view', $request->archive) }}">
                                                {{ $request->archive->title ?? 'Archive #'.$request->archive_id }}
                                            </a>
                                        @else
                                            Archive #{{ $request->archive_id }}
                                        @endif
                                    </td>
                                    <td>{{ $request->archive->record_type ?? 'N/A' }}</td>
                                    <td>
                                        @if($request->requester)
                                            {{ $request->requester->name }}
                                            <br><small class="text-muted">{{ $request->requester->role ?? 'N/A' }}</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $request->requested_at ? $request->requested_at->format('M d, Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-link btn-sm" data-toggle="tooltip" title="{{ $request->reason }}">
                                            View Reason
                                        </button>
                                    </td>
                                    <td>
                                        @switch($request->status)
                                            @case('pending')
                                                <span class="badge badge-secondary">Pending</span>
                                                @break
                                            @case('approved')
                                                <span class="badge badge-info">Approved</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                                @break
                                            @case('fulfilled')
                                                <span class="badge badge-success">Fulfilled</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-dark">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge badge-light">{{ $request->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($request->status == 'pending')
                                            <button type="button" class="btn btn-success btn-sm" onclick="approveRetrieval({{ $request->id }})">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="rejectRetrieval({{ $request->id }})">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @elseif($request->status == 'approved')
                                            <button type="button" class="btn btn-primary btn-sm" onclick="fulfillRetrieval({{ $request->id }})">
                                                <i class="fas fa-check-double"></i> Fulfill
                                            </button>
                                        @elseif($request->status == 'fulfilled')
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i> Completed
                                            </span>
                                            @if($request->fulfilled_at)
                                                <br><small class="text-muted">{{ $request->fulfilled_at->format('M d, Y H:i') }}</small>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No retrieval requests found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $retrievalRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="approveForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Retrieval Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this retrieval request?</p>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="approval_notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="rejectForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Retrieval Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for Rejection</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Required reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Fulfill Modal -->
<div class="modal fade" id="fulfillModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="fulfillForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Fulfill Retrieval Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Confirm that the archived record has been provided to the requester.</p>
                    <div class="form-group">
                        <label>Delivery Notes</label>
                        <textarea name="delivery_notes" class="form-control" rows="3" placeholder="Optional delivery notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-double"></i> Mark as Fulfilled
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

function approveRetrieval(id) {
    $('#approveForm').attr('action', '/registrar/services/archive/retrieval/' + id + '/approve');
    $('#approveModal').modal('show');
}

function rejectRetrieval(id) {
    $('#rejectForm').attr('action', '/registrar/services/archive/retrieval/' + id + '/reject');
    $('#rejectModal').modal('show');
}

function fulfillRetrieval(id) {
    $('#fulfillForm').attr('action', '/registrar/services/archive/retrieval/' + id + '/fulfill');
    $('#fulfillModal').modal('show');
}
</script>
@endsection