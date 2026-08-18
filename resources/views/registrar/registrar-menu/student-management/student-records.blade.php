@extends('layouts.registrar')

@section('title', 'Student Records')

@push('styles')
<style>
/* ── page shell ─────────────────────────────────────────────── */
.sr-page { padding: 24px 28px; }

/* ── stat strip ─────────────────────────────────────────────── */
.sr-stats { display:flex; gap:14px; margin-bottom:20px; flex-wrap:wrap; }
.sr-stat  {
    flex:1; min-width:140px;
    background:#fff; border-radius:12px; padding:16px 20px;
    display:flex; align-items:center; gap:14px;
    box-shadow:0 1px 4px rgba(0,0,0,.08);
}
.sr-stat-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sr-stat-val  { font-size:26px; font-weight:700; line-height:1; color:#1e293b; }
.sr-stat-lbl  { font-size:11px; color:#64748b; margin-top:2px; text-transform:uppercase; letter-spacing:.05em; }

/* ── toolbar ────────────────────────────────────────────────── */
.sr-toolbar {
    display:flex; gap:10px; margin-bottom:18px; flex-wrap:wrap; align-items:flex-end;
    background:#fff; border-radius:12px; padding:14px 16px;
    box-shadow:0 1px 4px rgba(0,0,0,.08);
}
.sr-toolbar .form-control, .sr-toolbar .form-select {
    height:36px; font-size:13px; border-radius:8px; border:1px solid #e2e8f0;
    padding:0 10px;
}
.sr-search-wrap { flex:1; min-width:200px; position:relative; }
.sr-search-wrap input { width:100%; }
.sr-graduate-select {
    width:230px;
    min-width:230px;
    padding-right:44px !important;
    text-overflow:ellipsis;
}
.sr-filter-group { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.sr-btn-search {
    background:#004d27; color:#fff; border:none; border-radius:8px;
    padding:0 18px; height:36px; font-size:13px; font-weight:600; cursor:pointer;
    display:flex; align-items:center; gap:6px;
}
.sr-btn-search:hover { background:#006837; }
.sr-btn-clear  {
    background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:8px;
    padding:0 14px; height:36px; font-size:13px; cursor:pointer;
}
.sr-btn-clear:hover { background:#e2e8f0; }

/* ── status pills ───────────────────────────────────────────── */
.sr-status-bar { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
.sr-status-pill {
    padding:5px 14px; border-radius:20px; font-size:12px; font-weight:600;
    border:1.5px solid transparent; cursor:pointer; text-decoration:none;
    transition:all .15s;
}
.sr-status-pill.all      { border-color:#94a3b8; color:#475569; background:#f8fafc; }
.sr-status-pill.active   { border-color:#16a34a; color:#15803d; background:#f0fdf4; }
.sr-status-pill.withdrawn{ border-color:#dc2626; color:#dc2626; background:#fff1f2; }
.sr-status-pill.graduates{ border-color:#0f766e; color:#0f766e; background:#f0fdfa; }
.sr-status-pill.non_graduates{ border-color:#7c3aed; color:#6d28d9; background:#f5f3ff; }
.sr-status-pill.transferee{ border-color:#d97706; color:#b45309; background:#fffbeb; }
.sr-status-pill.irregular{ border-color:#db2777; color:#be185d; background:#fdf2f8; }
.sr-status-pill.selected { color:#fff !important; }
.sr-status-pill.all.selected      { background:#475569; border-color:#475569; }
.sr-status-pill.active.selected   { background:#16a34a; border-color:#16a34a; }
.sr-status-pill.withdrawn.selected{ background:#dc2626; border-color:#dc2626; }
.sr-status-pill.graduates.selected{ background:#0f766e; border-color:#0f766e; }
.sr-status-pill.non_graduates.selected{ background:#7c3aed; border-color:#7c3aed; }
.sr-status-pill.transferee.selected{ background:#d97706; border-color:#d97706; }
.sr-status-pill.irregular.selected{ background:#db2777; border-color:#db2777; }

.sr-view-controls { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.sr-view-toggle {
    display:inline-flex; align-items:center; gap:4px; padding:3px;
    border:1px solid #d8e2dc; border-radius:8px; background:#fff;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.sr-view-toggle-btn {
    min-width:34px; height:30px; border:0; border-radius:6px;
    padding:0 10px;
    display:inline-flex; align-items:center; justify-content:center;
    gap:6px;
    color:#64748b; background:transparent; cursor:pointer;
    font-size:12px; font-weight:800;
}
.sr-view-toggle-btn:hover { background:#f1f5f9; color:#0f5132; }
.sr-view-toggle-btn.is-active { background:#004d27; color:#fff; }
.sr-view-panel.is-hidden { display:none !important; }

/* ── student card grid ───────────────────────────────────────── */
.sr-grid {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap:14px;
}
.sr-card {
    background:#fff; border-radius:12px;
    box-shadow:0 1px 4px rgba(0,0,0,.08);
    overflow:hidden; transition:box-shadow .15s, transform .15s;
    display:flex; flex-direction:column;
}
.sr-card:hover { box-shadow:0 6px 20px rgba(0,77,39,.12); transform:translateY(-2px); }

.sr-card-head {
    background:linear-gradient(135deg,#004d27,#006837,#1a9e54);
    padding:16px; display:flex; align-items:center; gap:12px; position:relative;
}
.sr-avatar {
    width:52px; height:52px; border-radius:50%; border:2.5px solid rgba(255,255,255,.4);
    background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center;
    font-size:18px; font-weight:700; color:#fff; flex-shrink:0; text-transform:uppercase;
}
.sr-card-name  { font-size:14px; font-weight:700; color:#fff; line-height:1.3; }
.sr-card-no    { font-size:11px; color:rgba(255,255,255,.75); margin-top:2px; }
.sr-card-badge {
    position:absolute; top:10px; right:10px;
    font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px;
}
.badge-active    { background:#d1fae5; color:#065f46; }
.badge-withdrawn { background:#fee2e2; color:#991b1b; }
.badge-graduate  { background:#ccfbf1; color:#115e59; }
.badge-transferee{ background:#fef3c7; color:#92400e; }
.badge-irregular { background:#fce7f3; color:#9d174d; }
.sr-card-flags   { position:absolute; top:34px; right:10px; display:flex; flex-direction:column; gap:3px; align-items:flex-end; }

.sr-card-body { padding:12px 14px; flex:1; }
.sr-card-row  { display:flex; align-items:flex-start; gap:6px; margin-bottom:7px; }
.sr-card-row svg { flex-shrink:0; margin-top:2px; color:#64748b; }
.sr-card-row-val { font-size:12px; color:#374151; line-height:1.4; }
.sr-card-row-lbl { font-size:10.5px; color:#94a3b8; margin-top:1px; }

.sr-card-foot { padding:10px 14px; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:7px; flex-wrap:wrap; }
.sr-view-btn {
    background:linear-gradient(135deg,#004d27,#006837);
    color:#fff; border:none; border-radius:7px;
    padding:6px 16px; font-size:12px; font-weight:600; cursor:pointer;
    display:inline-flex; align-items:center; gap:5px; text-decoration:none;
    transition:opacity .15s;
}
.sr-view-btn:hover { opacity:.88; color:#fff; }
.sr-alumni-btn {
    background:#f8fafc; color:#0f5132; border:1px solid #d8e2dc; border-radius:7px;
    padding:6px 12px; font-size:12px; font-weight:700; cursor:pointer;
    display:inline-flex; align-items:center; gap:5px; text-decoration:none;
}
.sr-alumni-btn:hover { background:#eef7f1; color:#0f5132; }
.sr-hd-btn {
    background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; border-radius:7px;
    padding:6px 12px; font-size:12px; font-weight:700; cursor:pointer;
    display:inline-flex; align-items:center; gap:5px; text-decoration:none;
    font-family:inherit;
}
.sr-hd-btn:hover { background:#ffedd5; color:#9a3412; }
.sr-hd-btn:disabled { opacity:.6; cursor:not-allowed; }
.sr-hd-status {
    display:inline-flex; align-items:center; border-radius:999px; padding:5px 12px;
    font-size:12px; font-weight:700; text-decoration:none; gap:5px;
}
.sr-hd-status.pending { background:#fef3c7; color:#92400e; }
.sr-hd-status.issued { background:#dcfce7; color:#166534; }

/* ── empty state ────────────────────────────────────────────── */
.sr-empty {
    grid-column:1/-1; text-align:center; padding:60px 20px;
    color:#94a3b8;
}
.sr-empty svg { margin-bottom:14px; opacity:.5; }
.sr-empty h4  { font-size:18px; color:#64748b; margin-bottom:6px; }

.sr-table-wrap {
    overflow-x:auto; background:#fff; border:1px solid #e2e8f0; border-radius:10px;
    box-shadow:0 1px 4px rgba(0,0,0,.08);
}
.sr-table { width:100%; min-width:1180px; border-collapse:collapse; }
.sr-table th {
    background:#f8fafc; color:#334155; font-size:11px; font-weight:800;
    text-align:left; text-transform:uppercase; letter-spacing:.03em;
    border-bottom:1px solid #e2e8f0; padding:10px 12px; white-space:nowrap;
}
.sr-table td {
    color:#1f2937; font-size:12.5px; font-weight:600;
    border-bottom:1px solid #edf2f7; padding:10px 12px; vertical-align:middle;
}
.sr-table tbody tr:hover { background:#f8fbf9; }
.sr-table tbody tr:last-child td { border-bottom:0; }
.sr-table-name { color:#0f5132; font-weight:800; text-decoration:none; }
.sr-table-name:hover { color:#006837; text-decoration:underline; }
.sr-table-muted { color:#64748b; font-size:11.5px; margin-top:2px; }
.sr-table-status {
    display:inline-flex; align-items:center; border-radius:999px; padding:3px 9px;
    font-size:11px; font-weight:800; white-space:nowrap;
}
.sr-table-status.active { background:#dcfce7; color:#166534; }
.sr-table-status.withdrawn { background:#fee2e2; color:#991b1b; }
.sr-table-status.graduate { background:#ccfbf1; color:#115e59; }
.sr-table-status.transferee { background:#fef3c7; color:#92400e; }
.sr-table-status.irregular { background:#fce7f3; color:#9d174d; }
.sr-table-flags { display:flex; gap:4px; margin-top:4px; flex-wrap:wrap; }
.sr-table-actions { display:flex; align-items:center; gap:7px; justify-content:flex-end; white-space:nowrap; }
.sr-table-empty { text-align:center; color:#64748b; padding:36px 12px !important; }

/* ── pagination ─────────────────────────────────────────────── */
.sr-pager { margin-top:24px; display:flex; flex-direction:column; align-items:center; gap:10px; }
.sr-pager-info { font-size:12.5px; color:#64748b; }
.sr-pager .pagination { gap:4px; margin:0; }
.sr-pager .page-link {
    border-radius:8px !important; border:1px solid #e2e8f0;
    color:#374151; font-size:13px; padding:6px 12px;
}
.sr-pager .page-item.active .page-link { background:#004d27; border-color:#004d27; color:#fff; }
</style>
@endpush

@section('content')
<div class="sr-page">

    @if(session('status'))
        <div class="alert alert-{{ session('status_type', 'success') }}" role="alert" style="margin-bottom:16px;">{{ session('status') }}</div>
    @endif

    {{-- Page header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <h4 style="font-size:20px;font-weight:700;color:#1e293b;margin:0;">Student Records</h4>
            <p style="font-size:13px;color:#64748b;margin:2px 0 0;">Complete student database — search, filter, and view full profiles</p>
        </div>
        <div class="sr-view-controls">
            <form method="POST" action="{{ route('registrar.registrar-menu.student-mgmt.student-records.recalculate-irregular') }}" onsubmit="return confirm('Recalculate Irregular status for every active student from their current term grades?');">
                @csrf
                <button type="submit" class="sr-btn-clear" title="Recompute Irregular status from current term grades">Recalculate Irregular Status</button>
            </form>
            <div class="sr-view-toggle" role="group" aria-label="Student records view">
                <button type="button" id="srCardViewBtn" class="sr-view-toggle-btn is-active" data-sr-view-button="card" title="Card View" aria-pressed="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Card</span>
                </button>
                <button type="button" id="srTableViewBtn" class="sr-view-toggle-btn" data-sr-view-button="table" title="List View" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    <span>List</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Stat strip --}}
    <div class="sr-stats">
        <div class="sr-stat">
            <div class="sr-stat-icon" style="background:#eff6ff;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#2563eb" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div class="sr-stat-val">{{ number_format($totalCount) }}</div>
                <div class="sr-stat-lbl">Total Students</div>
            </div>
        </div>
        <div class="sr-stat">
            <div class="sr-stat-icon" style="background:#f0fdf4;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#16a34a" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5z"/><path d="M20.59 22c0-3.87-3.85-7-8.59-7S3.41 18.13 3.41 22"/></svg>
            </div>
            <div>
                <div class="sr-stat-val" style="color:#16a34a;">{{ number_format($activeCount) }}</div>
                <div class="sr-stat-lbl">Active</div>
            </div>
        </div>
        <div class="sr-stat">
            <div class="sr-stat-icon" style="background:#fff1f2;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#dc2626" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <div>
                <div class="sr-stat-val" style="color:#dc2626;">{{ number_format($withdrawnCount) }}</div>
                <div class="sr-stat-lbl">Withdrawn</div>
            </div>
        </div>
        <div class="sr-stat">
            <div class="sr-stat-icon" style="background:#fefce8;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#ca8a04" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2 2v19c0 1.66 1.34 3 3 3h17"/><path d="M5 17 9.59 11.64c.76-.88 2.11-.94 2.93-.11l.95.94c.82.83 2.17.77 2.93-.11L21 7"/></svg>
            </div>
            <div>
                <div class="sr-stat-val">{{ number_format($students->total()) }}</div>
                <div class="sr-stat-lbl">Matching Filter</div>
            </div>
        </div>
        <div class="sr-stat">
            <div class="sr-stat-icon" style="background:#f0fdfa;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#0f766e" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
            </div>
            <div>
                <div class="sr-stat-val" style="color:#0f766e;">{{ number_format($graduateCount ?? 0) }}</div>
                <div class="sr-stat-lbl">Graduates</div>
            </div>
        </div>
        <div class="sr-stat">
            <div class="sr-stat-icon" style="background:#fffbeb;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#d97706" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 7 12 2l9 5-9 5-9-5Z"/><path d="M3 7v10l9 5 9-5V7"/><path d="M12 12v10"/></svg>
            </div>
            <div>
                <div class="sr-stat-val" style="color:#d97706;">{{ number_format($transfereeCount ?? 0) }}</div>
                <div class="sr-stat-lbl">Transferee</div>
            </div>
        </div>
        <div class="sr-stat">
            <div class="sr-stat-icon" style="background:#fdf2f8;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#db2777" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
            </div>
            <div>
                <div class="sr-stat-val" style="color:#db2777;">{{ number_format($irregularCount ?? 0) }}</div>
                <div class="sr-stat-lbl">Irregular</div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <form method="GET" action="{{ route('registrar.registrar-menu.student-mgmt.student-records') }}">
        <div class="sr-toolbar">
            <div class="sr-search-wrap">
                <input type="text" name="q" class="form-control" placeholder="Enter name or student number" value="{{ $search }}">
            </div>
            <div class="sr-filter-group">
                <select name="program" class="form-select" style="width:auto;">
                    <option value="">All Programs</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->code }}" {{ $program === $c->code ? 'selected' : '' }}>{{ $c->code }} – {{ $c->name }}</option>
                    @endforeach
                </select>
                <select name="year" class="form-select" style="width:130px;">
                    <option value="">All Years</option>
                    @foreach($yearLevels as $yl)
                        <option value="{{ $yl }}" {{ $year === $yl ? 'selected' : '' }}>{{ $yl }}</option>
                    @endforeach
                </select>
                <select name="sy" class="form-select" style="width:130px;">
                    <option value="">All AY</option>
                    @foreach($schoolYears as $s)
                        <option value="{{ $s }}" {{ $sy === $s ? 'selected' : '' }}>AY {{ $s }}</option>
                    @endforeach
                </select>
                <select name="sem" class="form-select" style="width:160px;">
                    <option value="">All Semesters</option>
                    @foreach($semesterOptions as $so)
                        <option value="{{ $so }}" {{ $sem === $so ? 'selected' : '' }}>{{ $so }}</option>
                    @endforeach
                </select>
                <select name="graduate" class="form-select sr-graduate-select">
                    <option value="all" {{ ($graduate ?? 'all') === 'all' ? 'selected' : '' }}>All Graduation Status</option>
                    <option value="graduates" {{ ($graduate ?? 'all') === 'graduates' ? 'selected' : '' }}>Graduates</option>
                    <option value="non_graduates" {{ ($graduate ?? 'all') === 'non_graduates' ? 'selected' : '' }}>Non-Graduates</option>
                </select>
                <input type="hidden" name="status" value="{{ $status }}">
                <button type="submit" class="sr-btn-search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    Search
                </button>
                @if($search || $program || $year || $sy || $sem || $status !== 'all' || ($graduate ?? 'all') !== 'all')
                    <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records') }}" class="sr-btn-clear">Clear</a>
                @endif
            </div>
        </div>

        {{-- Status pills --}}
        <div class="sr-status-bar">
            @foreach([['all','All Students'],['active','Active'],['withdrawn','Withdrawn'],['transferee','Transferee'],['irregular','Irregular']] as [$val,$lbl])
                <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records', array_merge(request()->except('status','graduate','page'), ['status'=>$val, 'graduate'=>'all'])) }}"
                   class="sr-status-pill {{ $val }} {{ $status === $val ? 'selected' : '' }}">
                    {{ $lbl }}
                </a>
            @endforeach
            @foreach([['graduates','Graduates'],['non_graduates','Non-Graduates']] as [$val,$lbl])
                <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records', array_merge(request()->except('status','graduate','page'), ['status'=>'all', 'graduate'=>$val])) }}"
                   class="sr-status-pill {{ $val }} {{ $status === 'all' && ($graduate ?? 'all') === $val ? 'selected' : '' }}">
                    {{ $lbl }}
                </a>
            @endforeach
            <a href="{{ route('registrar.registrar-menu.alumni.tracker') }}" class="sr-status-pill graduates">
                Alumni Tracker
            </a>
        </div>
    </form>

    {{-- Grid --}}
    <div class="sr-grid sr-view-panel" id="srCardView">
        @forelse($students as $s)
            @php
                $prof    = $s->profile;
                $isWD    = $s->is_withdrawn ?? false;
                $name    = $prof ? trim($prof->first_name . ' ' . $prof->last_name) : $s->name;
                $name    = $name ?: $s->name;
                $parts   = explode(' ', strtoupper($name));
                $initials= substr($parts[0] ?? '?', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : '');
                $program = $s->program ?: optional($s->canonicalCourse)->code ?: 'N/A';
                $isGraduate = $s->relationLoaded('graduateTagging') && $s->graduateTagging && $s->graduateTagging->is_graduate;
                $course  = optional($s->canonicalCourse)->name ?: $s->program ?: '—';
            @endphp
            <div class="sr-card">
                <div class="sr-card-head">
                    <div class="sr-avatar">{{ $initials }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="sr-card-name">{{ $name }}</div>
                        <div class="sr-card-no">{{ $s->student_no }}</div>
                    </div>
                    <span class="sr-card-badge {{ $isWD ? 'badge-withdrawn' : 'badge-active' }}">
                        {{ $isWD ? 'Withdrawn' : 'Active' }}
                    </span>
                    @if($s->is_transferee || $s->is_irregular)
                    <div class="sr-card-flags">
                        @if($s->is_transferee)<span class="sr-card-badge badge-transferee" style="position:static;">Transferee</span>@endif
                        @if($s->is_irregular)<span class="sr-card-badge badge-irregular" style="position:static;">Irregular</span>@endif
                    </div>
                    @endif
                </div>
                <div class="sr-card-body">
                    @if($isGraduate)
                    <div class="sr-card-row">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="#0f766e" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
                        <div>
                            <div class="sr-card-row-val"><span class="sr-card-badge badge-graduate" style="position:static;display:inline-flex;">Graduate</span></div>
                            <div class="sr-card-row-lbl">{{ optional($s->graduateTagging->date_graduated)->format('M j, Y') ?: 'Tagged as alumni' }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="sr-card-row">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 16.74V4.67C22 3.47 21.02 2.58 19.83 2.68H19.77C17.67 2.86 14.48 3.93 12.7 5.05L12.53 5.16C12.24 5.34 11.76 5.34 11.47 5.16L11.22 5.01C9.44 3.9 6.26 2.84 4.16 2.67C2.97 2.57 2 3.47 2 4.66V16.74C2 17.7 2.78 18.6 3.74 18.72L4.03 18.76C6.2 19.05 9.55 20.15 11.47 21.2L11.51 21.22C11.78 21.37 12.21 21.37 12.47 21.22C14.39 20.16 17.75 19.05 19.93 18.76L20.26 18.72C21.22 18.6 22 17.7 22 16.74Z"/><path d="M12 5.49V20.49"/></svg>
                        <div>
                            <div class="sr-card-row-val">{{ $course }}</div>
                            <div class="sr-card-row-lbl">Program</div>
                        </div>
                    </div>
                    <div class="sr-card-row">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M8 2V5M16 2V5M3.5 9.09H20.5M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z"/></svg>
                        <div>
                            <div class="sr-card-row-val">{{ $s->year_level ?: optional($s->yearBlock)->label ?: '—' }}</div>
                            <div class="sr-card-row-lbl">Year Level</div>
                        </div>
                    </div>
                    <div class="sr-card-row">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                        <div>
                            <div class="sr-card-row-val">AY {{ $s->school_year ?: optional($s->academicTerm)->school_year ?: '—' }} · {{ $s->semester ?: optional($s->academicTerm)->term ?: '—' }}</div>
                            <div class="sr-card-row-lbl">Academic Year &amp; Semester</div>
                        </div>
                    </div>
                    @if($prof && ($prof->mobile_number || $prof->student_email))
                    <div class="sr-card-row">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 16.58A5 5 0 0 0 18 13h-2a5 5 0 0 0-5 5v2"/><path d="M22 21H2M12 9a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
                        <div>
                            <div class="sr-card-row-val">{{ $prof->mobile_number ?: $prof->student_email ?: '—' }}</div>
                            <div class="sr-card-row-lbl">Contact</div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="sr-card-foot">
                    @if($isGraduate)
                    <a href="{{ route('registrar.registrar-menu.alumni.tracker') }}?q={{ urlencode($s->student_no) }}" class="sr-alumni-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
                        Alumni
                    </a>
                    @else
                    <a href="{{ route('registrar.services.reports-admin.tagging-of-graduates') }}?student={{ $s->id }}" class="sr-alumni-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        Tag Graduate
                    </a>
                    @endif
                    @if($s->hd_record)
                        <a href="{{ route('registrar.registrar-menu.forms.honorable-dismissal.show', $s->id) }}" class="sr-hd-status {{ $s->hd_record->status === 'issued' ? 'issued' : 'pending' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            HD: {{ $s->hd_record->status === 'issued' ? 'Issued' : 'Pending' }}
                        </a>
                    @else
                        <button type="button" class="sr-hd-btn" data-hd-student-id="{{ $s->id }}" onclick="srTagHonorableDismissal({{ $s->id }}, this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            Tag HD
                        </button>
                    @endif
                    <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records.profile', $s->id) }}" class="sr-view-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View Profile
                    </a>
                </div>
            </div>
        @empty
            <div class="sr-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <h4>No students found</h4>
                <p style="font-size:13px;">Try adjusting your search or filters.</p>
            </div>
        @endforelse
    </div>

    <div class="sr-table-wrap sr-view-panel is-hidden" id="srTableView">
        <table class="sr-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Program</th>
                    <th>Year Level</th>
                    <th>Academic Term</th>
                    <th>Status</th>
                    <th>Graduation</th>
                    <th>Contact</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $s)
                    @php
                        $prof = $s->profile;
                        $isWD = $s->is_withdrawn ?? false;
                        $name = $prof ? trim($prof->first_name . ' ' . $prof->last_name) : $s->name;
                        $name = $name ?: $s->name;
                        $isGraduate = $s->relationLoaded('graduateTagging') && $s->graduateTagging && $s->graduateTagging->is_graduate;
                        $course = optional($s->canonicalCourse)->name ?: $s->program ?: 'N/A';
                        $courseCode = optional($s->canonicalCourse)->code ?: $s->program;
                        $schoolYearText = $s->school_year ?: optional($s->academicTerm)->school_year;
                        $semesterText = $s->semester ?: optional($s->academicTerm)->term;
                        $contactText = $prof ? ($prof->mobile_number ?: $prof->student_email) : '';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records.profile', $s->id) }}" class="sr-table-name">{{ $name ?: '-' }}</a>
                            <div class="sr-table-muted">{{ $s->student_no ?: '-' }}</div>
                        </td>
                        <td>
                            {{ $course }}
                            @if($courseCode && $courseCode !== $course)
                                <div class="sr-table-muted">{{ $courseCode }}</div>
                            @endif
                        </td>
                        <td>{{ $s->year_level ?: optional($s->yearBlock)->label ?: '-' }}</td>
                        <td>
                            AY {{ $schoolYearText ?: '-' }}
                            <div class="sr-table-muted">{{ $semesterText ?: '-' }}</div>
                        </td>
                        <td>
                            <span class="sr-table-status {{ $isWD ? 'withdrawn' : 'active' }}">{{ $isWD ? 'Withdrawn' : 'Active' }}</span>
                            @if($s->is_transferee || $s->is_irregular)
                            <div class="sr-table-flags">
                                @if($s->is_transferee)<span class="sr-table-status transferee">Transferee</span>@endif
                                @if($s->is_irregular)<span class="sr-table-status irregular">Irregular</span>@endif
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($isGraduate)
                                <span class="sr-table-status graduate">Graduate</span>
                                <div class="sr-table-muted">{{ optional($s->graduateTagging->date_graduated)->format('M j, Y') ?: 'Tagged as alumni' }}</div>
                            @else
                                <span class="sr-table-muted">Non-Graduate</span>
                            @endif
                        </td>
                        <td>{{ $contactText ?: '-' }}</td>
                        <td>
                            <div class="sr-table-actions">
                                @if($isGraduate)
                                    <a href="{{ route('registrar.registrar-menu.alumni.tracker') }}?q={{ urlencode($s->student_no) }}" class="sr-alumni-btn">Alumni</a>
                                @else
                                    <a href="{{ route('registrar.services.reports-admin.tagging-of-graduates') }}?student={{ $s->id }}" class="sr-alumni-btn">Tag Graduate</a>
                                @endif
                                @if($s->hd_record)
                                    <a href="{{ route('registrar.registrar-menu.forms.honorable-dismissal.show', $s->id) }}" class="sr-hd-status {{ $s->hd_record->status === 'issued' ? 'issued' : 'pending' }}">HD: {{ $s->hd_record->status === 'issued' ? 'Issued' : 'Pending' }}</a>
                                @else
                                    <button type="button" class="sr-hd-btn" data-hd-student-id="{{ $s->id }}" onclick="srTagHonorableDismissal({{ $s->id }}, this)">Tag HD</button>
                                @endif
                                <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records.profile', $s->id) }}" class="sr-view-btn">View Profile</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="sr-table-empty">No students found. Try adjusting your search or filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($students->hasPages())
        <div class="sr-pager">
            <div class="sr-pager-info">
                Showing {{ number_format($students->firstItem() ?? 0) }}&ndash;{{ number_format($students->lastItem() ?? 0) }} of {{ number_format($students->total()) }} student(s)
            </div>
            {{ $students->links() }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
var SR_HD_TAG_URL_TEMPLATE = @json(route('registrar.registrar-menu.forms.honorable-dismissal.tag', ['student' => '__STUDENT__']));

function srTagHonorableDismissal(studentId, btn) {
    if (!confirm('Tag this student for Honorable Dismissal?')) {
        return;
    }

    var url = SR_HD_TAG_URL_TEMPLATE.replace('__STUDENT__', encodeURIComponent(studentId));
    var csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
    btn.disabled = true;

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
    }).then(function (r) { return r.json(); }).then(function (data) {
        if (data.success) {
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast(data.message || 'Student tagged for Honorable Dismissal.', 'success');
            }
            setTimeout(function () { window.location.reload(); }, 600);
        } else {
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast(data.message || 'Unable to tag student.', 'error');
            } else {
                alert(data.message || 'Unable to tag student.');
            }
            btn.disabled = false;
        }
    }).catch(function () {
        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('Network error — check connection.', 'error');
        } else {
            alert('Network error — check connection.');
        }
        btn.disabled = false;
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var storageKey = 'registrarStudentRecordsViewMode';
    var cardView = document.getElementById('srCardView');
    var tableView = document.getElementById('srTableView');
    var buttons = Array.prototype.slice.call(document.querySelectorAll('[data-sr-view-button]'));

    function setStudentRecordsView(mode) {
        mode = mode === 'table' ? 'table' : 'card';

        if (cardView) {
            cardView.classList.toggle('is-hidden', mode !== 'card');
        }

        if (tableView) {
            tableView.classList.toggle('is-hidden', mode !== 'table');
        }

        buttons.forEach(function (button) {
            var isActive = button.getAttribute('data-sr-view-button') === mode;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        try {
            window.localStorage.setItem(storageKey, mode);
        } catch (error) {}
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            setStudentRecordsView(button.getAttribute('data-sr-view-button'));
        });
    });

    var savedMode = 'card';
    try {
        savedMode = window.localStorage.getItem(storageKey) || 'card';
    } catch (error) {}

    setStudentRecordsView(savedMode);
});
</script>
@endpush
