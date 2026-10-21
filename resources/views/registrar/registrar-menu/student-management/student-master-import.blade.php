@extends('layouts.registrar')
@section('title', 'Import StudentMaster')
@section('page-title', 'IMPORT STUDENTMASTER')
@section('content')
<div class="container-fluid py-4" style="max-width:1100px;">
    <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records') }}">&larr; Student masterlist</a>
    <h2 class="mt-3">Import StudentMaster</h2>
    <p>Upload the original Excel workbook to preview student profiles before saving.</p>
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @if(session('student_master_result'))
        @php($result = session('student_master_result'))
        <div class="alert alert-success" role="status">
            Imported {{ number_format($result['created']) }} students:
            {{ number_format($result['active']) }} active, {{ number_format($result['inactive']) }} inactive.
            Skipped {{ number_format($result['skipped']) }} existing student numbers. No existing records were overwritten.
            @if($result['skipped_invalid']) {{ number_format($result['skipped_invalid']) }} invalid source rows were excluded as confirmed. @endif
        </div>
    @endif
    <div class="card p-4 mb-4">
        <form method="POST" enctype="multipart/form-data" action="{{ route('registrar.registrar-menu.student-mgmt.student-master-import.preview') }}">
            @csrf
            <label for="student-master-workbook">StudentMaster workbook (.xlsx, maximum 16 MB)</label>
            <input id="student-master-workbook" class="form-control mb-3" type="file" name="workbook" accept=".xlsx" required>
            <button class="btn btn-success" type="submit">Preview import</button>
        </form>
    </div>
    <div class="alert alert-info">
        Supported personal details are copied into student profiles. Every original column is retained in
        <strong>StudentMaster source details</strong> on the student profile, including both email columns,
        full family names, emergency contacts, location codes, and student-type codes.
        Program, year level, and enrollment term remain unassigned. This import does not enroll students or create logins.
        Inactive source records remain inactive and are not marked withdrawn.
    </div>
    @isset($preview)
        <h3>Preview</h3>
        <p><strong>{{ number_format($preview['rows']) }}</strong> rows &middot;
            <strong>{{ number_format($preview['new']) }}</strong> new &middot;
            <strong>{{ number_format($preview['existing']) }}</strong> existing numbers to skip &middot;
            {{ number_format($preview['active']) }} active / {{ number_format($preview['inactive']) }} inactive in source</p>
        @foreach(['errors' => 'danger', 'warnings' => 'warning'] as $kind => $class)
            @if(count($preview[$kind]))
                <div class="alert alert-{{ $class }}">
                    <strong>{{ $kind === 'errors' ? $preview['error_count'].' source error(s)' : $preview['warning_count'].' warning(s)' }}</strong>
                    <ul class="mb-0">@foreach($preview[$kind] as $message)<li>{{ $message }}</li>@endforeach</ul>
                    <small>Up to 50 messages shown. All original values remain in source details.</small>
                </div>
            @endif
        @endforeach
        <div class="table-responsive"><table class="table table-bordered bg-white">
            <thead><tr><th>Source row</th><th>Student number</th><th>Name</th><th>Source status</th><th>Action</th></tr></thead>
            <tbody>@foreach($preview['sample'] as $row)
                <tr><td>{{ $row['row'] }}</td><td>{{ $row['number'] }}</td><td>{{ $row['name'] }}</td>
                    <td>{{ $row['active'] ? 'Active' : 'Inactive' }}</td><td>{{ $row['existing'] ? 'Skip existing' : 'Create profile' }}</td></tr>
            @endforeach</tbody>
        </table></div>
        @if($preview['rows'] && !$preview['duplicate_count'] && $preview['new'])
            <form method="POST" action="{{ route('registrar.registrar-menu.student-mgmt.student-master-import.store') }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Importing — please wait…';">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                @if($preview['invalid_rows'])
                    <p><label><input type="checkbox" name="skip_invalid" value="1" required>
                        Exclude the {{ $preview['invalid_rows'] }} invalid source rows listed above. Import only valid student profiles.</label></p>
                @endif
                <button class="btn btn-success" type="submit">Import {{ number_format($preview['new']) }} new student profiles</button>
                <p class="text-muted mt-2">The preview expires after one hour. The import is saved as one transaction.</p>
            </form>
        @endif
    @endisset
</div>
@endsection
