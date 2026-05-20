@extends('layouts.registrar')

@section('title', 'PLP - Departments')
@section('page-title', 'DEPARTMENT MANAGEMENT')
@section('body-class', 'page-faculty-departments')

@section('content')
<div class="rfl-wrap">
    <style>
        .dept-dashboard { display:grid; grid-template-columns:repeat(4, minmax(150px, 1fr)); gap:12px; margin-bottom:16px; }
        .dept-card { background:#fff; border:1px solid #dfe8e2; border-left:4px solid #006837; border-radius:8px; padding:14px 16px; box-shadow:0 1px 3px rgba(15,23,42,.05); }
        .dept-card span { display:block; color:#64748b; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
        .dept-card strong { display:block; margin-top:8px; color:#0f172a; font-size:28px; line-height:1; font-weight:800; }
        .dept-layout { display:grid; grid-template-columns:340px minmax(0, 1fr); gap:16px; align-items:start; }
        .dept-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:16px; }
        .dept-form-row { margin-bottom:12px; }
        .dept-form-row label { display:block; color:#334155; font-weight:800; font-size:.82rem; margin-bottom:5px; }
        .dept-input { width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:8px 10px; }
        .dept-actions { display:flex; gap:8px; align-items:center; }
        .dept-inline { display:grid; grid-template-columns:90px 1.4fr 1.2fr auto; gap:8px; align-items:center; }
        .dept-delete { border:0; background:#fee2e2; color:#991b1b; border-radius:6px; padding:8px 10px; font-weight:800; }
        .dept-filter-form { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
        .dept-filter-field { min-width:220px; }
        .dept-filter-field label { display:block; color:#64748b; font-size:11px; font-weight:800; text-transform:uppercase; margin-bottom:5px; }
        .dept-filter-field input, .dept-filter-field select { width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:8px 10px; min-height:38px; background:#fff; }
        .dept-filter-actions { display:flex; gap:8px; align-items:center; }
        .dept-filter-actions a { color:#64748b; font-weight:700; text-decoration:none; }
        @media (max-width:1100px) { .dept-dashboard { grid-template-columns:repeat(2, minmax(150px, 1fr)); } .dept-layout { grid-template-columns:1fr; } }
        @media (max-width:720px) { .dept-dashboard { grid-template-columns:1fr; } .dept-inline { grid-template-columns:1fr; } .dept-filter-field { min-width:100%; } }
    </style>

    @if(session('success'))
        <div class="alert alert-success" style="padding:12px 14px; margin-bottom:14px; background:#dcfce7; color:#166534; border-radius:6px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="padding:12px 14px; margin-bottom:14px; background:#fee2e2; color:#991b1b; border-radius:6px;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="dept-dashboard">
        <div class="dept-card"><span>Total Departments</span><strong>{{ number_format((int) ($summary['departments'] ?? 0)) }}</strong></div>
        <div class="dept-card"><span>Active Colleges</span><strong>{{ number_format((int) ($summary['colleges'] ?? 0)) }}</strong></div>
        <div class="dept-card"><span>Assigned Faculty</span><strong>{{ number_format((int) ($summary['assigned_faculty'] ?? 0)) }}</strong></div>
        <div class="dept-card"><span>Unassigned Faculty</span><strong>{{ number_format((int) ($summary['unassigned_faculty'] ?? 0)) }}</strong></div>
    </div>

    <div class="dept-layout">
        <div class="dept-panel">
            <h3 style="margin-top:0;">Add Department</h3>
            <form method="POST" action="{{ route('registrar.registrar-menu.faculty-mgmt.departments.store') }}">
                @csrf
                <div class="dept-form-row">
                    <label>Department Code</label>
                    <input class="dept-input" type="text" name="code" value="{{ old('code') }}" placeholder="CCS" required>
                </div>
                <div class="dept-form-row">
                    <label>Department Name</label>
                    <input class="dept-input" type="text" name="description" value="{{ old('description') }}" placeholder="College of Computer Studies" required>
                </div>
                <div class="dept-form-row">
                    <label>College</label>
                    <select class="dept-input" name="college_id">
                        <option value="">No College</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" {{ old('college_id') == $college->id ? 'selected' : '' }}>
                                {{ $college->abbr ?: $college->code }} - {{ $college->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Save Department</button>
            </form>
        </div>

        <div class="dept-panel">
            <div class="rfl-topbar" style="margin-bottom:12px;">
                <div class="rfl-search">
                    <form method="GET" action="{{ route('registrar.registrar-menu.faculty-mgmt.departments') }}" class="dept-filter-form">
                        <div class="dept-filter-field">
                            <label>Search</label>
                            <input type="text" name="q" placeholder="Code or department" value="{{ $search }}">
                        </div>
                        <div class="dept-filter-field">
                            <label>College</label>
                            <select name="college_id">
                                <option value="">All Colleges</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}" {{ (int) $selectedCollegeId === (int) $college->id ? 'selected' : '' }}>
                                        {{ $college->abbr ?: $college->code }} - {{ $college->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dept-filter-actions">
                            <button class="btn btn-success" type="submit">Filter</button>
                            @if($search !== '' || (int) $selectedCollegeId > 0)
                                <a href="{{ route('registrar.registrar-menu.faculty-mgmt.departments') }}">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="rfl-meta">{{ $departments->firstItem() ?? 0 }} - {{ $departments->lastItem() ?? 0 }} of {{ $departments->total() }}</div>
            </div>

            <div class="app-table-wrap">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>College</th>
                            <th style="width:120px;">Faculty</th>
                            <th style="width:260px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $department)
                            <tr>
                                <td colspan="4">
                                    <form method="POST" action="{{ route('registrar.registrar-menu.faculty-mgmt.departments.update', $department->id) }}" class="dept-inline">
                                        @csrf
                                        @method('PUT')
                                        <input class="dept-input" type="text" name="code" value="{{ $department->code }}" required>
                                        <input class="dept-input" type="text" name="description" value="{{ $department->description }}" required>
                                        <select class="dept-input" name="college_id">
                                            <option value="">No College</option>
                                            @foreach($colleges as $college)
                                                <option value="{{ $college->id }}" {{ (int) ($department->college_id ?? 0) === (int) $college->id ? 'selected' : '' }}>
                                                    {{ $college->abbr ?: $college->code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="dept-actions">
                                            <span class="badge badge-success">{{ number_format((int) $department->faculties_count) }}</span>
                                            <button type="submit" class="btn btn-success">Update</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('registrar.registrar-menu.faculty-mgmt.departments.destroy', $department->id) }}" style="margin-top:8px; text-align:right;" onsubmit="return confirm('Delete this department?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dept-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No departments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="rfl-footer" style="display:flex;justify-content:flex-end;margin-top:12px;">
                <div class="rfl-pagination">{{ $departments->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
