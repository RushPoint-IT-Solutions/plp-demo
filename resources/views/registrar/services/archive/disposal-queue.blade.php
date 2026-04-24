@extends('layouts.registrar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Disposal Queue</h3>
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
                                    <th>Scheduled Date</th>
                                    <th>Disposal Method</th>
                                    <th>Status</th>
                                    <th>Requested By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($disposalQueues as $queue)
                                <tr>
                                    <td>#{{ $queue->id }}</td>
                                    <td>
                                        @if($queue->archive)
                                            <a href="{{ route('archive.view', $queue->archive) }}">
                                                {{ $queue->archive->title ?? 'Archive #'.$queue->archive_id }}
                                            </a>
                                        @else
                                            Archive #{{ $queue->archive_id }}
                                        @endif
                                    </td>
                                    <td>{{ $queue->archive->record_type ?? 'N/A' }}</td>
                                    <td>{{ $queue->scheduled_date ? $queue->scheduled_date->format('M d, Y') : 'Immediate' }}</td>
                                    <td>
                                        <span class="badge badge-warning">
                                            {{ str_replace('_', ' ', $queue->disposal_method) }}
                                        </span>
                                    </td>
                                    <td>
                                        @switch($queue->status)
                                            @case('pending')
                                                <span class="badge badge-secondary">Pending</span>
                                                @break
                                            @case('approved')
                                                <span class="badge badge-info">Approved</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                                @break
                                            @case('executed')
                                                <span class="badge badge-success">Executed</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-dark">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge badge-light">{{ $queue->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($queue->requester)
                                            {{ $queue->requester->name }}
                                        @else
                                            System
                                        @endif
                                    </td>
                                    <td>
                                        @if($queue->status == 'pending')
                                            <button type="button" class="btn btn-success btn-sm" onclick="approveDisposal({{ $queue->id }})">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="rejectDisposal({{ $queue->id }})">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @elseif($queue->status == 'approved')
                                            <button type="button" class="btn btn-primary btn-sm" onclick="executeDisposal({{ $queue->id }})">
                                                <i class="fas fa-trash"></i> Execute
                                            </button>
                                        @elseif($queue->status == 'executed' && $queue->certificate)
                                            <a href="{{ route('archive.certificate.view', $queue->certificate) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-certificate"></i> View Certificate
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No items in disposal queue.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $disposalQueues->links() }}
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
                    <h5 class="modal-title">Approve Disposal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this disposal request?</p>
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
                    <h5 class="modal-title">Reject Disposal</h5>
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

<!-- Execute Modal -->
<div class="modal fade" id="executeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="executeForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Execute Disposal - Warning!</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action is irreversible. The archived record will be permanently deleted.
                    </div>
                    <p>Please confirm by typing "DELETE" below:</p>
                    <div class="form-group">
                        <input type="text" id="confirmDelete" class="form-control" placeholder="Type DELETE to confirm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="executeBtn" class="btn btn-danger" disabled onclick="submitExecute()">
                        <i class="fas fa-trash"></i> Execute Disposal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('#confirmDelete').on('input', function() {
        $('#executeBtn').prop('disabled', $(this).val() !== 'DELETE');
    });
});

function approveDisposal(id) {
    $('#approveForm').attr('action', '/registrar/services/archive/disposal/' + id + '/approve');
    $('#approveModal').modal('show');
}

function rejectDisposal(id) {
    $('#rejectForm').attr('action', '/registrar/services/archive/disposal/' + id + '/reject');
    $('#rejectModal').modal('show');
}

function executeDisposal(id) {
    $('#executeForm').attr('action', '/registrar/services/archive/disposal/' + id + '/execute');
    $('#confirmDelete').val('');
    $('#executeBtn').prop('disabled', true);
    $('#executeModal').modal('show');
}

function submitExecute() {
    if ($('#confirmDelete').val() === 'DELETE') {
        $('#executeForm').submit();
    }
}
</script>
@endsection