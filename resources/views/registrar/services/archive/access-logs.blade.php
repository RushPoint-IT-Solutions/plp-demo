@extends('layouts.registrar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Archive Access Logs</h3>
                    <div class="card-tools">
                        <a href="{{ route('archive.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="action" class="form-control">
                                    <option value="">All Actions</option>
                                    <option value="view" {{ request('action') == 'view' ? 'selected' : '' }}>View</option>
                                    <option value="retrieve" {{ request('action') == 'retrieve' ? 'selected' : '' }}>Retrieve</option>
                                    <option value="restore" {{ request('action') == 'restore' ? 'selected' : '' }}>Restore</option>
                                    <option value="dispose" {{ request('action') == 'dispose' ? 'selected' : '' }}>Dispose</option>
                                    <option value="export" {{ request('action') == 'export' ? 'selected' : '' }}>Export</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('archive.access-logs') }}" class="btn btn-default">Clear</a>
                            </div>
                        </div>
                    </form>
                    
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Archive ID</th>
                                <th>Record Type</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->performed_at->format('M d, Y H:i:s') }}</td>
                                <td>
                                    @if($log->performer)
                                        {{ $log->performer->name }}
                                        <br><small class="text-muted">{{ $log->performer->role ?? 'N/A' }}</small>
                                    @else
                                        System
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $log->action == 'view' ? 'info' : ($log->action == 'retrieve' ? 'primary' : ($log->action == 'dispose' ? 'danger' : 'secondary')) }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->archive)
                                        <a href="{{ route('archive.view', $log->archive) }}">
                                            #{{ $log->archive_id }}
                                        </a>
                                    @else
                                        {{ $log->archive_id ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>{{ $log->archive->record_type ?? 'N/A' }}</td>
                                <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                <td>
                                    @if($log->metadata)
                                        @php $metadata = is_string($log->metadata) ? json_decode($log->metadata, true) : $log->metadata; @endphp
                                        @if(is_array($metadata))
                                            @foreach($metadata as $key => $value)
                                                <small><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</small><br>
                                            @endforeach
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No access logs found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-3">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection