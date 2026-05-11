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
.sr-search-wrap input { width:100%; padding-left:34px; }
.sr-search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; }
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
.sr-status-pill.selected { color:#fff !important; }
.sr-status-pill.all.selected      { background:#475569; border-color:#475569; }
.sr-status-pill.active.selected   { background:#16a34a; border-color:#16a34a; }
.sr-status-pill.withdrawn.selected{ background:#dc2626; border-color:#dc2626; }
.sr-status-pill.graduates.selected{ background:#0f766e; border-color:#0f766e; }
.sr-status-pill.non_graduates.selected{ background:#7c3aed; border-color:#7c3aed; }

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

/* ── empty state ────────────────────────────────────────────── */
.sr-empty {
    grid-column:1/-1; text-align:center; padding:60px 20px;
    color:#94a3b8;
}
.sr-empty svg { margin-bottom:14px; opacity:.5; }
.sr-empty h4  { font-size:18px; color:#64748b; margin-bottom:6px; }

/* ── pagination ─────────────────────────────────────────────── */
.sr-pager { margin-top:24px; display:flex; justify-content:center; }
.sr-pager .pagination { gap:4px; }
.sr-pager .page-link {
    border-radius:8px !important; border:1px solid #e2e8f0;
    color:#374151; font-size:13px; padding:6px 12px;
}
.sr-pager .page-item.active .page-link { background:#004d27; border-color:#004d27; color:#fff; }
</style>
@endpush

@section('content')
<div class="sr-page">

    {{-- Page header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <h4 style="font-size:20px;font-weight:700;color:#1e293b;margin:0;">Student Records</h4>
            <p style="font-size:13px;color:#64748b;margin:2px 0 0;">Complete student database — search, filter, and view full profiles</p>
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
                <div class="sr-stat-val">{{ $students->total() }}</div>
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
    </div>

    {{-- Toolbar --}}
    <form method="GET" action="{{ route('registrar.registrar-menu.student-mgmt.student-records') }}">
        <div class="sr-toolbar">
            <div class="sr-search-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="q" class="form-control" placeholder="Search name or student no…" value="{{ $search }}">
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
                    <option value="">All SY</option>
                    @foreach($schoolYears as $s)
                        <option value="{{ $s }}" {{ $sy === $s ? 'selected' : '' }}>SY {{ $s }}</option>
                    @endforeach
                </select>
                <select name="sem" class="form-select" style="width:160px;">
                    <option value="">All Semesters</option>
                    @foreach($semesterOptions as $so)
                        <option value="{{ $so }}" {{ $sem === $so ? 'selected' : '' }}>{{ $so }}</option>
                    @endforeach
                </select>
                <select name="graduate" class="form-select" style="width:160px;">
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
            @foreach([['all','All Students'],['active','Active'],['withdrawn','Withdrawn']] as [$val,$lbl])
                <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records', array_merge(request()->except('status','page'), ['status'=>$val])) }}"
                   class="sr-status-pill {{ $val }} {{ $status === $val ? 'selected' : '' }}">
                    {{ $lbl }}
                </a>
            @endforeach
            @foreach([['graduates','Graduates'],['non_graduates','Non-Graduates']] as [$val,$lbl])
                <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records', array_merge(request()->except('graduate','page'), ['graduate'=>$val])) }}"
                   class="sr-status-pill {{ $val }} {{ ($graduate ?? 'all') === $val ? 'selected' : '' }}">
                    {{ $lbl }}
                </a>
            @endforeach
            <a href="{{ route('registrar.registrar-menu.alumni.tracker') }}" class="sr-status-pill graduates">
                Alumni Tracker
            </a>
        </div>
    </form>

    {{-- Grid --}}
    <div class="sr-grid">
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
                            <div class="sr-card-row-val">{{ $s->year_level ?: '—' }}</div>
                            <div class="sr-card-row-lbl">Year Level</div>
                        </div>
                    </div>
                    <div class="sr-card-row">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                        <div>
                            <div class="sr-card-row-val">SY {{ $s->school_year ?: '—' }} · {{ $s->semester ?: '—' }}</div>
                            <div class="sr-card-row-lbl">School Year &amp; Semester</div>
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

    {{-- Pagination --}}
    @if($students->hasPages())
    <div class="sr-pager">
        {{ $students->links() }}
    </div>
    @endif

</div>
@endsection
