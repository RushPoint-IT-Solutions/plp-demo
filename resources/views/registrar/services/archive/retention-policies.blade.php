@extends('layouts.registrar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Retention Policies</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPolicyModal">
                            <i class="fas fa-plus"></i> Add Policy
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Record Type</th>
                                <th>Retention Period</th>
                                <th>Archive Trigger</th>
                                <th>Disposal Trigger</th>
                                <th>Archives</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($policies as $policy)
                            <tr>
                                <td>{{ $policy->name }}</td>
                                <td>{{ $policy->record_type }}</td>
                                <td>{{ $policy->retention_period_months }} months</td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ str_replace('_', ' ', $policy->archive_trigger) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        {{ str_replace('_', ' ', $policy->disposal_trigger) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $policy->archives_count }}</span>
                                </td>
                                <td>
                                    @if($policy->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewPolicyModal{{ $policy->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPolicyModal{{ $policy->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deletePolicy({{ $policy->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No retention policies found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Policy Modal -->
<div class="modal fade" id="addPolicyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('archive.retention-policies.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Retention Policy</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Record Type</label>
                        <input type="text" name="record_type" class="form-control" placeholder="e.g., student_record, grade_report" required>
                    </div>
                    <div class="form-group">
                        <label>Retention Period (Months)</label>
                        <input type="number" name="retention_period_months" class="form-control" min="1" max="120" value="12" required>
                    </div>
                    <div class="form-group">
                        <label>Archive Trigger</label>
                        <select name="archive_trigger" class="form-control" required>
                            <option value="manual">Manual</option>
                            <option value="end_of_academic_year">End of Academic Year</option>
                            <option value="end_of_semester">End of Semester</option>
                            <option value="inactivity">Inactivity</option>
                            <option value="graduation">Graduation</option>
                            <option value="separation">Separation</option>
                        </select>
                    </div>
                    <div class="form-group" id="inactivityMonthsField" style="display:none;">
                        <label>Inactivity Months</label>
                        <input type="number" name="inactivity_months" class="form-control" min="1" max="60">
                    </div>
                    <div class="form-group">
                        <label>Disposal Trigger</label>
                        <select name="disposal_trigger" class="form-control" required>
                            <option value="retention_expired">Retention Expired</option>
                            <option value="manual_approval">Manual Approval</option>
                            <option value="graduation_plus_years">Graduation + Years</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="requires_approval" value="1"> Requires Approval for Retrieval
                        </label>
                    </div>
                    <div class="form-group" id="approvalRoleField" style="display:none;">
                        <label>Approval Role</label>
                        <select name="approval_role" class="form-control">
                            <option value="registrar">Registrar</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@foreach($policies as $policy)
<!-- View Policy Modal -->
<div class="modal fade" id="viewPolicyModal{{ $policy->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Retention Policy Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Name</th>
                        <td>{{ $policy->name }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $policy->description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Record Type</th>
                        <td>{{ $policy->record_type }}</td>
                    </tr>
                    <tr>
                        <th>Retention Period</th>
                        <td>{{ $policy->retention_period_months }} months</td>
                    </tr>
                    <tr>
                        <th>Archive Trigger</th>
                        <td>{{ str_replace('_', ' ', $policy->archive_trigger) }}</td>
                    </tr>
                    @if($policy->inactivity_months)
                    <tr>
                        <th>Inactivity Months</th>
                        <td>{{ $policy->inactivity_months }} months</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Disposal Trigger</th>
                        <td>{{ str_replace('_', ' ', $policy->disposal_trigger) }}</td>
                    </tr>
                    <tr>
                        <th>Requires Approval</th>
                        <td>{{ $policy->requires_approval ? 'Yes' : 'No' }}</td>
                    </tr>
                    @if($policy->approval_role)
                    <tr>
                        <th>Approval Role</th>
                        <td>{{ ucfirst($policy->approval_role) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($policy->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Archives Count</th>
                        <td>{{ $policy->archives_count }}</td>
                    </tr>
                    <tr>
                        <th>Created</th>
                        <td>{{ $policy->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Policy Modal -->
<div class="modal fade" id="editPolicyModal{{ $policy->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('archive.retention-policies.update', $policy) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Retention Policy</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $policy->name }}" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ $policy->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Retention Period (Months)</label>
                        <input type="number" name="retention_period_months" class="form-control" min="1" max="120" value="{{ $policy->retention_period_months }}" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="requires_approval" value="1" {{ $policy->requires_approval ? 'checked' : '' }}> Requires Approval for Retrieval
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Approval Role</label>
                        <select name="approval_role" class="form-control">
                            <option value="registrar" {{ $policy->approval_role == 'registrar' ? 'selected' : '' }}>Registrar</option>
                            <option value="admin" {{ $policy->approval_role == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" {{ $policy->is_active ? 'checked' : '' }}> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

<form id="deletePolicyForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('select[name="archive_trigger"]').change(function() {
        if ($(this).val() === 'inactivity') {
            $('#inactivityMonthsField').show();
        } else {
            $('#inactivityMonthsField').hide();
        }
    });

    $('input[name="requires_approval"]').change(function() {
        if ($(this).is(':checked')) {
            $('#approvalRoleField').show();
        } else {
            $('#approvalRoleField').hide();
        }
    });
});

function deletePolicy(id) {
    if (confirm('Are you sure you want to delete this policy?')) {
        var form = $('#deletePolicyForm');
        form.attr('action', '/registrar/services/archive/retention-policies/' + id);
        form.submit();
    }
}
</script>
@endsection