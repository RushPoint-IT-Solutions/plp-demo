@extends('layouts.registrar')

@section('title', 'PLP - Faculty Loads')
@section('page-title', 'FACULTY LOADS')
@section('body-class', 'page-services-faculty-loads')

@push('scripts')
    <script src="{{ asset('js/registrar-faculty-loads.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
@php
    $facultyShowRoute = request()->routeIs('registrar.registrar-menu.faculty-mgmt.*')
        ? 'registrar.registrar-menu.faculty-mgmt.faculty-list.show'
        : 'registrar.services.classroom-faculty.faculty-loads.show';
    $facultyIndexRoute = request()->routeIs('registrar.registrar-menu.faculty-mgmt.*')
        ? 'registrar.registrar-menu.faculty-mgmt.faculty-list'
        : 'registrar.services.classroom-faculty.faculty-loads.index';
@endphp
<div class="rfl-wrap">
    <style>
        .fl-dashboard {
            display:grid;
            grid-template-columns:repeat(5, minmax(140px, 1fr));
            gap:12px;
            margin-bottom:16px;
        }
        .fl-card {
            background:#fff;
            border:1px solid #dfe8e2;
            border-radius:8px;
            padding:14px 16px;
            min-height:92px;
            box-shadow:0 1px 3px rgba(15,23,42,.05);
        }
        .fl-card span {
            display:block;
            color:#64748b;
            font-size:11px;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.04em;
        }
        .fl-card strong {
            display:block;
            margin-top:8px;
            color:#0f172a;
            font-size:28px;
            line-height:1;
            font-weight:800;
        }
        .fl-card small {
            display:block;
            margin-top:7px;
            color:#64748b;
            font-weight:600;
        }
        .fl-card-underload { border-left:4px solid #16a34a; }
        .fl-card-full { border-left:4px solid #2563eb; }
        .fl-card-overload { border-left:4px solid #dc2626; }
        .fl-card-remaining { border-left:4px solid #ca8a04; }
        .fl-card-total { border-left:4px solid #006837; }
        .fl-filter-form {
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            align-items:flex-end;
        }
        .fl-filter-field {
            min-width:190px;
        }
        .fl-filter-field.search {
            min-width:260px;
        }
        .fl-filter-field label {
            display:block;
            color:#64748b;
            font-size:11px;
            font-weight:800;
            text-transform:uppercase;
            margin-bottom:5px;
        }
        .fl-filter-field input,
        .fl-filter-field select {
            width:100%;
            border:1px solid #cbd5e1;
            border-radius:6px;
            padding:8px 10px;
            min-height:38px;
            background:#fff;
        }
        .fl-filter-actions {
            display:flex;
            gap:8px;
            align-items:center;
        }
        .fl-filter-actions a {
            color:#64748b;
            font-weight:700;
            text-decoration:none;
        }
        .rfl-load-pill {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-width:72px;
            padding:5px 9px;
            border-radius:999px;
            font-size:12px;
            line-height:1;
            font-weight:800;
            white-space:nowrap;
        }
        .rfl-load-pill.good {
            color:#166534 !important;
            background:#dcfce7 !important;
            border:1px solid #86efac;
        }
        .rfl-load-pill.full {
            color:#1d4ed8 !important;
            background:#dbeafe !important;
            border:1px solid #93c5fd;
        }
        .rfl-load-pill.warn {
            color:#991b1b !important;
            background:#fee2e2 !important;
            border:1px solid #fecaca;
        }
        @media (max-width:1100px) { .fl-dashboard { grid-template-columns:repeat(2, minmax(160px, 1fr)); } }
        @media (max-width:620px) { .fl-dashboard { grid-template-columns:1fr; } .fl-filter-field { min-width:100%; } }
    </style>

    @php
        $dashboard = $loadDashboard ?? [
            'total_faculty' => $faculties->total(),
            'underload_count' => 0,
            'full_load_count' => 0,
            'overload_count' => 0,
            'remaining_load' => 0,
            'overload_units' => 0,
        ];
    @endphp

    <div class="fl-dashboard">
        <div class="fl-card fl-card-total">
            <span>Total Faculty</span>
            <strong>{{ number_format((int) ($dashboard['total_faculty'] ?? 0)) }}</strong>
            <small>{{ $loadTermLabel ?: 'Current load term' }}</small>
        </div>
        <div class="fl-card fl-card-underload">
            <span>Underload</span>
            <strong>{{ number_format((int) ($dashboard['underload_count'] ?? 0)) }}</strong>
            <small>Faculty below max load</small>
        </div>
        <div class="fl-card fl-card-full">
            <span>Full Load</span>
            <strong>{{ number_format((int) ($dashboard['full_load_count'] ?? 0)) }}</strong>
            <small>Faculty exactly at max load</small>
        </div>
        <div class="fl-card fl-card-overload">
            <span>Overload</span>
            <strong>{{ number_format((int) ($dashboard['overload_count'] ?? 0)) }}</strong>
            <small>{{ number_format((float) ($dashboard['overload_units'] ?? 0), 1) }} excess load unit(s)</small>
        </div>
        <div class="fl-card fl-card-remaining">
            <span>Total Remaining</span>
            <strong>{{ number_format((float) ($dashboard['remaining_load'] ?? 0), 1) }}</strong>
            <small>Available load units</small>
        </div>
    </div>

    <div class="rfl-topbar">
        <div class="rfl-search">
            <form method="GET" action="{{ route($facultyIndexRoute) }}" class="fl-filter-form">
                <div class="fl-filter-field search">
                    <label>Search</label>
                    <input type="text" name="q" placeholder="Faculty code or name" value="{{ $search }}">
                </div>
                <div class="fl-filter-field">
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
                <div class="fl-filter-field">
                    <label>Department</label>
                    <select name="department_id">
                        <option value="">All Departments</option>
                        @foreach($departmentOptions as $department)
                            <option value="{{ $department->id }}" {{ (int) $selectedDepartmentId === (int) $department->id ? 'selected' : '' }}>
                                {{ $department->code }} - {{ $department->description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="fl-filter-actions">
                    <button class="btn btn-success" type="submit">Filter</button>
                    @if($search !== '' || (int) $selectedCollegeId > 0 || (int) $selectedDepartmentId > 0)
                        <a href="{{ route($facultyIndexRoute) }}">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="rfl-meta">
            @if(!empty($loadTermLabel))
                Current Load: {{ $loadTermLabel }} &bull;
            @endif
            {{ $faculties->firstItem() ?? 0 }} - {{ $faculties->lastItem() ?? 0 }} of {{ $faculties->total() }}
        </div>
    </div>

    <div class="app-table-wrap rfl-table-wrap">
        <table class="app-table rfl-table" id="rflFacultyTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th style="width:70px">#</th>
                    <th>Code</th>
                    <th>Faculty Name</th>
                    <th>Department</th>
                    <th>College</th>
                    <th>Employment</th>
                    <th style="width:120px">Current Load</th>
                    <th style="width:120px">Max Load</th>
                    <th style="width:120px">Remaining</th>
                    <th style="width:120px">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faculties as $i => $faculty)
                    @php
                        $summary = $loadSummaries->get($faculty->id, []);
                        $currentLoad = (float) ($summary['current_load'] ?? 0);
                        $maxLoad = (float) ($summary['max_load'] ?? 0);
                        $remaining = (float) ($summary['remaining_load'] ?? 0);
                        $loadStatus = (string) ($summary['status'] ?? 'Underload');
                        $type = \Illuminate\Support\Facades\Schema::hasColumn('faculties', 'employment_type') ? (string) ($faculty->employment_type ?? 'Full-time Teacher') : 'Full-time Teacher';
                        $department = optional($faculty->departmentLookup)->description ?: ($faculty->department ?: 'N/A');
                        $college = optional($faculty->college)->abbr ?: optional(optional($faculty->departmentLookup)->college)->abbr ?: optional($faculty->college)->code ?: optional(optional($faculty->departmentLookup)->college)->code ?: 'N/A';
                        $statusClass = $loadStatus === 'Overload' ? 'warn' : ($loadStatus === 'Full Load' ? 'full' : 'good');
                    @endphp
                    <tr class="rfl-row" data-href="{{ route($facultyShowRoute, $faculty->id) }}">
                        <td>{{ ($faculties->firstItem() ?? 0) + $i }}</td>
                        <td class="td-code">{{ $faculty->code }}</td>
                        <td>
                            <a class="rfl-name-link" href="{{ route($facultyShowRoute, $faculty->id) }}">
                                {{ $faculty->name }}
                            </a>
                        </td>
                        <td>{{ $department }}</td>
                        <td>{{ $college }}</td>
                        <td>{{ $type }}</td>
                        <td>{{ number_format($currentLoad, 1) }}</td>
                        <td>{{ number_format($maxLoad, 1) }}</td>
                        <td>
                            <span class="rfl-load-pill {{ $remaining < 0 ? 'warn' : 'good' }}">{{ number_format($remaining, 1) }}</span>
                        </td>
                        <td>
                            <span class="rfl-load-pill {{ $statusClass }}">{{ $loadStatus }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No faculty found.</td>
                    </tr>
                @endforelse
            <tr class="rfl-list-total-row">
                <td colspan="10" class="rfl-list-total-cell" style="color:#006837 !important;background:#f8fcf9 !important;text-align:left !important;font-weight:700 !important;">Total Faculty: <strong>{{ $faculties->total() }}</strong></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="rfl-footer" style="display:flex;justify-content:flex-end;">
    <div class="rfl-pagination">{{ $faculties->links() }}</div>
</div>
</div>
@endsection
