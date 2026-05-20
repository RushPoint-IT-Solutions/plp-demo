@extends('layouts.registrar')

@section('title', 'Scholarship Module')

@push('styles')
<style>
.sch-page { padding:24px 28px; }
.sch-header { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin-bottom:18px; }
.sch-title { margin:0; color:#0f172a; font-size:24px; font-weight:800; }
.sch-muted { color:#64748b; font-size:13px; margin-top:4px; }
.sch-stats { display:grid; grid-template-columns:repeat(4,minmax(120px,1fr)); gap:12px; margin-bottom:16px; }
.sch-stat,.sch-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 1px 4px rgba(15,23,42,.06); }
.sch-stat { padding:14px; }
.sch-stat strong { display:block; font-size:24px; color:#004d27; line-height:1; }
.sch-stat span { color:#64748b; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
.sch-card { margin-bottom:16px; overflow:hidden; }
.sch-card-head { padding:14px 16px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; gap:12px; align-items:center; }
.sch-card-head h2 { margin:0; font-size:15px; font-weight:800; color:#1e293b; }
.sch-card-body { padding:16px; }
.sch-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
.sch-field label { display:block; font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.05em; margin-bottom:5px; }
.sch-field input,.sch-field select,.sch-field textarea { width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font-size:13px; color:#1e293b; background:#fff; }
.sch-field textarea { min-height:72px; resize:vertical; }
.sch-field.wide { grid-column:span 2; }
.sch-field.full { grid-column:1 / -1; }
.sch-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:14px; }
.sch-btn { border:0; border-radius:8px; padding:8px 14px; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:6px; }
.sch-btn.primary { background:#004d27; color:#fff; }
.sch-btn.soft { background:#f1f5f9; color:#334155; border:1px solid #e2e8f0; }
.sch-btn.danger { background:#fee2e2; color:#991b1b; }
.sch-filter { display:flex; gap:8px; flex-wrap:wrap; align-items:end; }
.sch-filter .sch-field { min-width:180px; }
.sch-table { width:100%; border-collapse:collapse; font-size:13px; }
.sch-table th { background:#f8fafc; color:#64748b; font-size:11px; text-transform:uppercase; letter-spacing:.05em; text-align:left; padding:10px; border-bottom:1px solid #e2e8f0; }
.sch-table td { padding:10px; border-bottom:1px solid #f1f5f9; vertical-align:top; color:#334155; }
.sch-badge { display:inline-block; border-radius:999px; padding:2px 9px; font-size:11px; font-weight:800; background:#e0f2fe; color:#075985; }
.sch-badge.open { background:#dcfce7; color:#166534; }
.sch-badge.closed { background:#fee2e2; color:#991b1b; }
.sch-badge.ended { background:#e2e8f0; color:#475569; }
.sch-program-name { font-weight:800; color:#0f172a; }
.sch-program-meta { color:#64748b; font-size:12px; margin-top:2px; }
details.sch-edit summary { cursor:pointer; color:#004d27; font-weight:800; font-size:12px; }
.sch-alert { padding:10px 14px; border-radius:8px; margin-bottom:14px; font-size:13px; font-weight:700; }
.sch-alert.success { background:#dcfce7; color:#166534; }
.sch-alert.error { background:#fee2e2; color:#991b1b; }
@media(max-width:900px){ .sch-stats,.sch-grid { grid-template-columns:1fr 1fr; } .sch-field.wide { grid-column:1 / -1; } }
@media(max-width:640px){ .sch-page { padding:16px; } .sch-stats,.sch-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="sch-page">
    <div class="sch-header">
        <div>
            <h1 class="sch-title">Scholarship Module</h1>
            <div class="sch-muted">Setup scholarship programs, eligibility, coverage, slots, and active terms.</div>
        </div>
        <a class="sch-btn primary" href="{{ route('registrar.registrar-menu.scholarships.report') }}">Scholar Report</a>
    </div>

    @if(session('success')) <div class="sch-alert success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="sch-alert error">{{ $errors->first() }}</div> @endif

    <div class="sch-stats">
        <div class="sch-stat"><strong>{{ $summary['programs'] }}</strong><span>Total Programs</span></div>
        <div class="sch-stat"><strong>{{ $summary['open'] }}</strong><span>Open</span></div>
        <div class="sch-stat"><strong>{{ $summary['ongoing'] }}</strong><span>Ongoing</span></div>
        <div class="sch-stat"><strong>{{ $summary['scholars'] }}</strong><span>Tagged Scholars</span></div>
    </div>

    <div class="sch-card">
        <div class="sch-card-head"><h2>Create Scholarship Program</h2></div>
        <div class="sch-card-body">
            <form method="POST" action="{{ route('registrar.registrar-menu.scholarships.store') }}">
                @csrf
                @include('registrar.scholarships.partials.program-form', ['program' => null])
                <div class="sch-actions">
                    <button class="sch-btn primary" type="submit">Save Program</button>
                </div>
            </form>
        </div>
    </div>

    <div class="sch-card">
        <div class="sch-card-head">
            <h2>Scholarship Programs</h2>
            <form class="sch-filter" method="GET" action="{{ route('registrar.registrar-menu.scholarships.index') }}">
                <div class="sch-field"><label>Search</label><input name="search" value="{{ $search }}" placeholder="Name, SY, semester"></div>
                <div class="sch-field"><label>Category</label><select name="category"><option value="">All categories</option>@foreach($categories as $item)<option value="{{ $item }}" {{ $category === $item ? 'selected' : '' }}>{{ $item }}</option>@endforeach</select></div>
                <div class="sch-field"><label>Status</label><select name="status"><option value="">All statuses</option>@foreach($statuses as $item)<option value="{{ $item }}" {{ $status === $item ? 'selected' : '' }}>{{ $item }}</option>@endforeach</select></div>
                <button class="sch-btn soft" type="submit">Filter</button>
            </form>
        </div>
        <div class="sch-card-body" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="sch-table">
                    <thead>
                        <tr>
                            <th>Scholarship</th>
                            <th>Coverage</th>
                            <th>Eligibility</th>
                            <th>Slots</th>
                            <th>Status</th>
                            <th>Scholars</th>
                            <th style="width:220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programs as $program)
                            <tr>
                                <td>
                                    <div class="sch-program-name">{{ $program->name }}</div>
                                    <div class="sch-program-meta">{{ $program->category }} | SY {{ $program->school_year ?: 'Any' }} | {{ $program->semester ?: 'Any semester' }}</div>
                                </td>
                                <td>{{ $program->coverage_type }} @if($program->coverage_value) <br><small>{{ number_format((float) $program->coverage_value, 2) }}</small> @endif</td>
                                <td>
                                    {{ $program->year_level_eligibility ?: 'All year levels' }}<br>
                                    <small>Maintaining GWA: {{ $program->maintaining_gwa ?: 'N/A' }}</small>
                                </td>
                                <td>{{ $program->available_slots ?? 'Unlimited' }}</td>
                                <td><span class="sch-badge {{ strtolower($program->status) }}">{{ $program->status }}</span></td>
                                <td>{{ $program->scholar_tags_count }}</td>
                                <td>
                                    <details class="sch-edit">
                                        <summary>Edit</summary>
                                        <form method="POST" action="{{ route('registrar.registrar-menu.scholarships.update', $program->id) }}" style="margin-top:10px;">
                                            @csrf
                                            @method('PUT')
                                            @include('registrar.scholarships.partials.program-form', ['program' => $program])
                                            <div class="sch-actions">
                                                <button class="sch-btn primary" type="submit">Update</button>
                                            </div>
                                        </form>
                                    </details>
                                    <form method="POST" action="{{ route('registrar.registrar-menu.scholarships.destroy', $program->id) }}" onsubmit="return confirm('Delete this scholarship program? Existing tags will also be removed.');" style="margin-top:8px;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="sch-btn danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:28px;">No scholarship programs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:14px 16px;">{{ $programs->links() }}</div>
        </div>
    </div>
</div>
@endsection
