@extends('layouts.registrar')

@section('title', 'Student Record — ' . $student->name)
@section('page-title', 'STUDENT RECORD')
@section('body-class', 'page-student-academic-record')

@section('content')
@php
    $profile  = $student->profile;
    $initials = collect(explode(' ', trim($student->name)))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('');
    $program  = $student->canonicalCourse->name ?? $student->program ?? 'N/A';

    $termGwa = function(\Illuminate\Support\Collection $rows): ?float {
        $valid = $rows->filter(fn($r) => !$r->inc && is_numeric($r->final_grade) && (float)$r->final_grade > 0 && (float)$r->units > 0);
        if ($valid->isEmpty()) return null;
        $tw = $valid->sum(fn($r) => (float)$r->final_grade * (float)$r->units);
        $tu = $valid->sum(fn($r) => (float)$r->units);
        return $tu > 0 ? round($tw / $tu, 4) : null;
    };

    $openDeficiencies    = $deficiencies->where('is_completed', false)->count();
    $openDiscipline      = $disciplineRecords->where('is_completed', false)->count();
    $submittedDocs       = $requirements->where('is_submitted', true)->count();
    $totalDocs           = $requirements->count();
    $pendingGradeCorrectionCount = ($gradeCorrectionRequests ?? collect())->where('status', 'pending')->count();
@endphp

@push('styles')
<style>
/* ─── Base ────────────────────────────────────── */
.sar-wrap { max-width:1280px; margin:0 auto; padding:0 0 48px; }

.sar-back-btn {
    display:inline-flex; align-items:center; gap:6px; font-size:0.81rem;
    font-weight:600; color:#004d27; text-decoration:none;
    padding:5px 14px 5px 10px; border:1.5px solid #c8e6c9;
    border-radius:20px; background:#f0faf1; margin-bottom:16px;
    transition:background .15s,border-color .15s;
}
.sar-back-btn:hover { background:#d4edda; border-color:#4caf50; }

/* ─── Student Header ──────────────────────────── */
.sar-header {
    background:linear-gradient(135deg,#004d27 0%,#006837 60%,#1a9e54 100%);
    border-radius:16px; padding:28px 32px; margin-bottom:20px;
    display:grid; grid-template-columns:auto 1fr auto;
    gap:24px; align-items:center;
    box-shadow:0 4px 20px rgba(0,77,39,.25);
}
.sar-avatar {
    width:80px; height:80px; border-radius:50%; border:3px solid rgba(255,255,255,.4);
    background:rgba(255,255,255,.15); backdrop-filter:blur(4px);
    display:flex; align-items:center; justify-content:center;
    font-size:1.8rem; font-weight:800; color:#fff; flex-shrink:0; letter-spacing:1px;
}
.sar-hname { font-size:1.25rem; font-weight:800; color:#fff; margin-bottom:6px; letter-spacing:.4px; }
.sar-hmeta { display:flex; flex-wrap:wrap; gap:8px 16px; font-size:0.81rem; color:rgba(255,255,255,.82); }
.sar-hmeta .hm-pill {
    display:inline-flex; align-items:center; gap:5px;
    background:rgba(255,255,255,.15); border-radius:12px; padding:3px 10px;
}
.sar-hcontact { margin-top:8px; display:flex; flex-wrap:wrap; gap:10px; }
.sar-hcontact span { font-size:0.79rem; color:rgba(255,255,255,.75); display:flex; align-items:center; gap:4px; }
.sar-standing-block { text-align:right; display:flex; flex-direction:column; align-items:flex-end; gap:8px; }
.sar-standing-badge {
    padding:6px 16px; border-radius:20px; font-size:0.78rem; font-weight:800;
    letter-spacing:.5px; display:inline-block;
}
.sar-gwa-big { font-size:2.2rem; font-weight:900; color:#fff; line-height:1; }
.sar-gwa-sub { font-size:0.72rem; color:rgba(255,255,255,.7); font-weight:700; letter-spacing:.6px; text-align:right; }

/* ─── Stat Strip ──────────────────────────────── */
.sar-stats { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:20px; }
.sar-stat {
    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    padding:14px 18px; box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.sar-stat-val { font-size:1.55rem; font-weight:800; color:#0f172a; line-height:1.1; }
.sar-stat-lbl { font-size:0.71rem; color:#64748b; font-weight:600; letter-spacing:.5px; text-transform:uppercase; margin-top:3px; }
.sar-stat-icon { font-size:1.1rem; margin-bottom:5px; }
.sar-stat.warn .sar-stat-val { color:#dc2626; }
.sar-stat.good .sar-stat-val { color:#065f46; }

/* ─── Tabs ────────────────────────────────────── */
.sar-tabs-bar {
    display:flex; gap:2px; background:#f8fafc; border:1px solid #e2e8f0;
    border-radius:12px; padding:4px; margin-bottom:20px; flex-wrap:wrap;
}
.sar-tab {
    padding:8px 16px; font-size:0.79rem; font-weight:600; color:#64748b;
    background:transparent; border:none; cursor:pointer; border-radius:8px;
    transition:all .15s; display:flex; align-items:center; gap:6px; white-space:nowrap;
}
.sar-tab:hover { color:#004d27; background:rgba(0,77,39,.06); }
.sar-tab.active { color:#004d27; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.10); font-weight:700; }
.sar-tab .tab-badge {
    background:#dc2626; color:#fff; font-size:0.68rem; font-weight:800;
    padding:1px 6px; border-radius:10px; min-width:18px; text-align:center;
}
.sar-tab .tab-badge.info { background:#0369a1; }

/* ─── Card ────────────────────────────────────── */
.sar-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    padding:24px; box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:16px;
}
.sar-section-title {
    font-size:0.74rem; font-weight:800; color:#94a3b8; letter-spacing:.8px;
    text-transform:uppercase; padding-bottom:10px; margin-bottom:16px;
    border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;
}
.sar-section-title span { color:#004d27; }

/* ─── Grid Fields ─────────────────────────────── */
.sar-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px 20px; }
.sar-grid-3 { grid-template-columns:1fr 1fr 1fr; }
.sar-grid-4 { grid-template-columns:repeat(4,1fr); }
.sar-field { display:flex; flex-direction:column; gap:4px; }
.sar-field label { font-size:0.72rem; font-weight:700; color:#94a3b8; letter-spacing:.3px; text-transform:uppercase; }
.sar-field input, .sar-field select {
    border:1.5px solid #e2e8f0; border-radius:7px; padding:8px 10px;
    font-size:0.84rem; color:#0f172a; background:#fff; width:100%; box-sizing:border-box;
    transition:border-color .15s,box-shadow .15s;
}
.sar-field input:focus, .sar-field select:focus {
    outline:none; border-color:#004d27; box-shadow:0 0 0 3px rgba(0,77,39,.1);
}
.sar-field .ro-val {
    font-size:0.88rem; color:#1e293b; font-weight:500;
    padding:8px 10px; background:#f8fafc; border-radius:7px; border:1px solid #f1f5f9;
}
.sar-col-span-2 { grid-column:span 2; }
.sar-col-span-3 { grid-column:span 3; }
.sar-col-span-full { grid-column:1/-1; }

/* ─── Grade Table ─────────────────────────────── */
.sar-term-head {
    display:flex; align-items:center; justify-content:space-between;
    background:#f0f9f4; border:1px solid #c8e6c9; border-radius:8px 8px 0 0;
    padding:9px 16px; margin-top:20px;
}
.sar-term-head:first-child { margin-top:0; }
.sar-term-label { font-size:0.82rem; font-weight:800; color:#004d27; }
.sar-term-meta  { font-size:0.76rem; color:#475569; font-weight:600; }
.sar-gtable { width:100%; border-collapse:collapse; border:1px solid #e2e8f0; border-top:none; font-size:0.82rem; margin-bottom:0; }
.sar-gtable th { background:#f8fafc; padding:8px 10px; font-size:0.71rem; font-weight:700; color:#64748b; letter-spacing:.4px; border-bottom:1px solid #e2e8f0; text-align:left; white-space:nowrap; }
.sar-gtable td { padding:9px 10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.sar-gtable tr:last-child td { border-bottom:none; }
.sar-gtable tr:hover td { background:#fafbfc; }
.g-pass { color:#065f46; font-weight:700; }
.g-fail { color:#991b1b; font-weight:700; }
.g-inc  { color:#92400e; font-style:italic; font-weight:700; }
.sar-act-row { display:flex; gap:6px; }
.btn-xs { border:none; cursor:pointer; border-radius:5px; padding:3px 9px; font-size:0.73rem; font-weight:700; transition:opacity .15s; }
.btn-xs:hover { opacity:.8; }
.btn-xs-edit { background:#dbeafe; color:#1d4ed8; }
.btn-xs-del  { background:#fee2e2; color:#dc2626; }
.btn-xs-approve { background:#dcfce7; color:#166534; }
.btn-xs-reject { background:#fee2e2; color:#991b1b; }
.sar-review-list { display:grid; gap:10px; }
.sar-review-item {
    border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;
    display:grid; grid-template-columns:1fr auto; gap:12px; align-items:start;
}
.sar-review-item.pending { border-color:#fde68a; background:#fffbeb; }
.sar-review-title { font-size:0.84rem; font-weight:800; color:#0f172a; margin-bottom:4px; }
.sar-review-meta { font-size:0.75rem; color:#64748b; display:flex; flex-wrap:wrap; gap:8px; }
.sar-review-reason { font-size:0.78rem; color:#334155; margin-top:6px; }
.sar-review-diff { font-size:0.75rem; color:#475569; margin-top:6px; }
.sar-review-actions { display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; }
.sar-review-status {
    padding:3px 9px; border-radius:12px; font-size:0.7rem; font-weight:800;
    text-transform:uppercase; background:#f1f5f9; color:#475569;
}
.sar-review-status.pending { background:#fef3c7; color:#92400e; }
.sar-review-status.approved { background:#dcfce7; color:#166534; }
.sar-review-status.rejected { background:#fee2e2; color:#991b1b; }

/* ─── Documents Table ─────────────────────────── */
.sar-doc-table { width:100%; border-collapse:collapse; font-size:0.83rem; }
.sar-doc-table th { font-size:0.71rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.4px; padding:8px 12px; border-bottom:2px solid #e2e8f0; text-align:left; }
.sar-doc-table td { padding:10px 12px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.sar-doc-table tr:last-child td { border-bottom:none; }
.doc-submitted { color:#065f46; font-weight:700; }
.doc-pending   { color:#92400e; font-weight:600; }

/* ─── Certificates Grid ───────────────────────── */
.sar-cert-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
.sar-cert-card {
    border:1.5px solid #e2e8f0; border-radius:10px; padding:16px;
    display:flex; flex-direction:column; gap:8px; text-decoration:none;
    transition:border-color .15s,box-shadow .15s,transform .12s; background:#fff;
}
.sar-cert-card:hover { border-color:#004d27; box-shadow:0 3px 12px rgba(0,77,39,.12); transform:translateY(-2px); }
.sar-cert-icon { font-size:1.6rem; }
.sar-cert-name { font-size:0.84rem; font-weight:700; color:#0f172a; }
.sar-cert-desc { font-size:0.74rem; color:#64748b; }

/* ─── Conduct ─────────────────────────────────── */
.sar-conduct-row {
    display:flex; gap:14px; align-items:flex-start; padding:14px 16px;
    border:1px solid #e2e8f0; border-radius:8px; margin-bottom:10px;
    background:#fff; transition:border-color .15s;
}
.sar-conduct-row.open-item { border-color:#fca5a5; background:#fff5f5; }
.sar-conduct-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; margin-top:4px; }
.sar-conduct-dot.major  { background:#dc2626; }
.sar-conduct-dot.minor  { background:#f59e0b; }
.sar-conduct-dot.couns  { background:#0369a1; }
.sar-conduct-dot.done   { background:#10b981; }
.sar-conduct-body { flex:1; }
.sar-conduct-title { font-size:0.84rem; font-weight:700; color:#0f172a; margin-bottom:4px; }
.sar-conduct-meta  { font-size:0.76rem; color:#64748b; display:flex; flex-wrap:wrap; gap:8px; }
.sar-badge { padding:3px 10px; border-radius:12px; font-size:0.72rem; font-weight:700; flex-shrink:0; }
.sar-badge-red    { background:#fee2e2; color:#991b1b; }
.sar-badge-yellow { background:#fef3c7; color:#92400e; }
.sar-badge-blue   { background:#dbeafe; color:#1e40af; }
.sar-badge-green  { background:#d1fae5; color:#065f46; }
.sar-badge-gray   { background:#f1f5f9; color:#475569; }

/* ─── Def Rows ────────────────────────────────── */
.sar-def-row {
    display:flex; align-items:center; gap:14px; padding:11px 16px;
    border:1px solid #e2e8f0; border-radius:8px; margin-bottom:8px; background:#fff;
}
.sar-def-row.pending { border-color:#fcd34d; background:#fffbeb; }
.sar-def-dept { font-size:0.8rem; font-weight:700; color:#334155; min-width:100px; }
.sar-def-remarks { flex:1; font-size:0.82rem; color:#475569; }
.sar-def-date { font-size:0.73rem; color:#94a3b8; white-space:nowrap; }

/* ─── Overview Quick Actions ─────────────────── */
.sar-quick-actions { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px; }
.sar-qa-btn {
    display:inline-flex; align-items:center; gap:6px; font-size:0.8rem; font-weight:700;
    padding:7px 16px; border-radius:8px; cursor:pointer; border:1.5px solid;
    text-decoration:none; transition:all .15s;
}
.sar-qa-btn-green { background:#f0faf1; color:#004d27; border-color:#c8e6c9; }
.sar-qa-btn-green:hover { background:#d4edda; border-color:#4caf50; }
.sar-qa-btn-blue  { background:#eff6ff; color:#1e40af; border-color:#bfdbfe; }
.sar-qa-btn-blue:hover  { background:#dbeafe; border-color:#60a5fa; }
.sar-qa-btn-slate { background:#f8fafc; color:#475569; border-color:#e2e8f0; }
.sar-qa-btn-slate:hover { background:#f1f5f9; }

/* ─── Save / Action Row ───────────────────────── */
.sar-save-row { display:flex; justify-content:flex-end; gap:10px; padding-top:18px; border-top:1px solid #f1f5f9; margin-top:8px; }
.btn-primary { background:#004d27; color:#fff; border:none; border-radius:8px; padding:9px 22px; font-size:0.82rem; font-weight:700; cursor:pointer; transition:background .15s; }
.btn-primary:hover { background:#00361b; }
.btn-cancel  { background:#f1f5f9; color:#475569; border:none; border-radius:8px; padding:9px 18px; font-size:0.82rem; font-weight:600; cursor:pointer; }

/* ─── Grade Toolbar ───────────────────────────── */
.sar-g-toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; gap:12px; flex-wrap:wrap; }
.sar-g-filter { display:flex; align-items:center; gap:8px; font-size:0.82rem; }
.sar-g-filter select { border:1.5px solid #e2e8f0; border-radius:7px; padding:6px 10px; font-size:0.82rem; }
.btn-add-grade { background:#004d27; color:#fff; border:none; border-radius:8px; padding:8px 18px; font-size:0.8rem; font-weight:700; cursor:pointer; transition:background .15s; }
.btn-add-grade:hover { background:#00361b; }

.sar-empty { text-align:center; padding:40px 20px; color:#94a3b8; }
.sar-empty svg { display:block; margin:0 auto 10px; }
.sar-empty-title { font-size:0.9rem; font-weight:700; color:#64748b; margin-bottom:4px; }
.sar-empty-sub { font-size:0.78rem; }

/* ─── Grade Modal ─────────────────────────────── */
.sar-modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,.45); display:none; align-items:center; justify-content:center; z-index:1200; }
.sar-modal-overlay.open { display:flex; }
.sar-modal { background:#fff; border-radius:16px; padding:28px; width:600px; max-width:95vw; max-height:92vh; overflow-y:auto; box-shadow:0 16px 48px rgba(0,0,0,.2); }
.sar-modal-title { font-size:1.05rem; font-weight:800; color:#0f172a; margin-bottom:20px; }
.sar-modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; padding-top:16px; border-top:1px solid #f1f5f9; }

/* ─── Responsive ──────────────────────────────── */
@media(max-width:1024px){ .sar-stats { grid-template-columns:repeat(3,1fr); } .sar-cert-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:768px){
    .sar-header { grid-template-columns:auto 1fr; }
    .sar-standing-block { grid-column:1/-1; flex-direction:row; justify-content:flex-start; }
    .sar-stats { grid-template-columns:1fr 1fr; }
    .sar-grid { grid-template-columns:1fr; }
    .sar-grid-3 { grid-template-columns:1fr; }
    .sar-cert-grid { grid-template-columns:1fr; }
    .sar-col-span-2, .sar-col-span-3 { grid-column:auto; }
    .sar-tabs-bar { gap:2px; }
    .sar-tab { padding:7px 10px; font-size:0.74rem; }
}
</style>
@endpush

<div class="sar-wrap">

{{-- Back --}}
<a href="{{ route('registrar.registrar-menu.student-mgmt.student-enrollment') }}" class="sar-back-btn">
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Student List
</a>

{{-- ── Header ─────────────────────────────────── --}}
<div class="sar-header">
    <div class="sar-avatar">{{ $initials }}</div>

    <div>
        <div class="sar-hname">{{ strtoupper($student->name) }}</div>
        <div class="sar-hmeta">
            <span class="hm-pill">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                {{ $student->student_no }}
            </span>
            <span class="hm-pill">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                {{ $program }}
            </span>
            <span class="hm-pill">{{ $student->year_level ?: 'Year N/A' }}</span>
            @if($student->school_year)
            <span class="hm-pill">SY {{ $student->school_year }} — {{ $student->semester }}</span>
            @endif
            @if($student->is_withdrawn)
            <span class="hm-pill" style="background:rgba(220,38,38,.25);color:#fca5a5;font-weight:800;">⚠ WITHDRAWN</span>
            @endif
        </div>
        <div class="sar-hcontact">
            @if($profile?->student_email)<span>✉ {{ $profile->student_email }}</span>@endif
            @if($profile?->mobile_number)<span>📱 {{ $profile->mobile_number }}</span>@endif
            @if($student->user)<span style="color:rgba(134,239,172,.9);">● Portal Account Active</span>@endif
        </div>
    </div>

    <div class="sar-standing-block">
        <span class="sar-standing-badge" style="background:{{ $academicStanding['bg'] }};color:{{ $academicStanding['color'] }}">
            {{ $academicStanding['icon'] }} {{ $academicStanding['label'] }}
        </span>
        @if($gwa)
        <div>
            <div class="sar-gwa-big">{{ number_format($gwa,2) }}</div>
            <div class="sar-gwa-sub">GENERAL WEIGHTED AVERAGE</div>
        </div>
        @else
        <div style="font-size:0.78rem;color:rgba(255,255,255,.5);">No grade data</div>
        @endif
    </div>
</div>

{{-- ── Stat Strip ──────────────────────────────── --}}
<div class="sar-stats">
    <div class="sar-stat good">
        <div class="sar-stat-icon">📚</div>
        <div class="sar-stat-val">{{ number_format((float)$totalUnitsEarned,0) }}</div>
        <div class="sar-stat-lbl">Units Earned</div>
    </div>
    <div class="sar-stat">
        <div class="sar-stat-icon">📋</div>
        <div class="sar-stat-val">{{ $gradeRecords->count() }}</div>
        <div class="sar-stat-lbl">Subjects on Record</div>
    </div>
    <div class="sar-stat">
        <div class="sar-stat-icon">📄</div>
        <div class="sar-stat-val">{{ $submittedDocs }}/{{ $totalDocs }}</div>
        <div class="sar-stat-lbl">Documents Submitted</div>
    </div>
    <div class="sar-stat {{ $openDeficiencies ? 'warn' : 'good' }}">
        <div class="sar-stat-icon">⚠️</div>
        <div class="sar-stat-val">{{ $openDeficiencies }}</div>
        <div class="sar-stat-lbl">Open Deficiencies</div>
    </div>
    <div class="sar-stat {{ $openDiscipline ? 'warn' : 'good' }}">
        <div class="sar-stat-icon">📌</div>
        <div class="sar-stat-val">{{ $disciplineRecords->count() }}</div>
        <div class="sar-stat-lbl">Conduct Incidents</div>
    </div>
</div>

{{-- ── Tab Bar ─────────────────────────────────── --}}
<div class="sar-tabs-bar">
    <button class="sar-tab active" data-tab="overview">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Overview
    </button>
    <button class="sar-tab" data-tab="personal">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Personal Info
    </button>
    <button class="sar-tab" data-tab="background">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Background
    </button>
    <button class="sar-tab" data-tab="grades">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Grades
        @if($gradeRecords->count())<span class="tab-badge info">{{ $gradeRecords->count() }}</span>@endif
        @if($pendingGradeCorrectionCount)<span class="tab-badge">{{ $pendingGradeCorrectionCount }}</span>@endif
    </button>
    <button class="sar-tab" data-tab="documents">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
        Documents
        @if($requirements->where('is_submitted',false)->count())<span class="tab-badge">{{ $requirements->where('is_submitted',false)->count() }}</span>@endif
    </button>
    <button class="sar-tab" data-tab="certificates">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
        Certificates
    </button>
    <button class="sar-tab" data-tab="conduct">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Conduct
        @if($openDeficiencies + $openDiscipline > 0)<span class="tab-badge">{{ $openDeficiencies + $openDiscipline }}</span>@endif
    </button>
</div>

{{-- ══════════════════ OVERVIEW TAB ══════════════════ --}}
<div class="sar-tab-content" id="sar-tab-overview">

    <div class="sar-quick-actions">
        <a href="{{ route('registrar.registrar-menu.forms.cor.certificate-of-registration') }}?student_id={{ $student->id }}" class="sar-qa-btn sar-qa-btn-green" target="_blank">
            📄 COR
        </a>
        <a href="{{ route('registrar.registrar-menu.forms.cog.copy-of-grades') }}" class="sar-qa-btn sar-qa-btn-green" target="_blank">
            📊 Copy of Grades
        </a>
        <a href="{{ route('registrar.registrar-menu.forms.certificates.certificate-gwa.show', ['student' => $student->id]) }}" class="sar-qa-btn sar-qa-btn-blue" target="_blank">
            🏅 Cert. of GWA
        </a>
        <a href="{{ route('registrar.registrar-menu.forms.official-grade-report') }}" class="sar-qa-btn sar-qa-btn-blue" target="_blank">
            📈 Grade Report
        </a>
        <a href="{{ route('registrar.registrar-menu.forms.diploma') }}" class="sar-qa-btn sar-qa-btn-slate" target="_blank">
            🎓 Diploma
        </a>
    </div>

    {{-- Most recent term --}}
    @php $firstKey = $gradesBySyTerm->keys()->first(); @endphp
    @if($firstKey)
    @php [$rsy,$rterm] = explode('|||',$firstKey.'|||'); $recentRows = $gradesBySyTerm->get($firstKey); @endphp
    <div class="sar-card">
        <div class="sar-section-title">Most Recent Term — <span>{{ $rsy }} ({{ $rterm }})</span></div>
        <table class="sar-gtable">
            <thead><tr><th>Code</th><th>Description</th><th>Units</th><th>Final Grade</th><th>Status</th><th>Remarks</th></tr></thead>
            <tbody>
            @foreach($recentRows as $r)
            <tr>
                <td><strong>{{ $r->subject_code }}</strong></td>
                <td>{{ $r->description ?: '—' }}</td>
                <td>{{ $r->units ?? '—' }}</td>
                <td class="{{ $r->inc ? 'g-inc' : ((!is_null($r->final_grade)&&(float)$r->final_grade>0&&(float)$r->final_grade<=3.0) ? 'g-pass' : ((!is_null($r->final_grade)&&(float)$r->final_grade>3.0) ? 'g-fail' : '')) }}">
                    {{ $r->inc ? 'INC' : ($r->final_grade ?? '—') }}
                </td>
                <td>{{ $r->status ?: '—' }}</td>
                <td>{{ $r->remarks ?: '—' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Profile snapshot --}}
    <div class="sar-card">
        <div class="sar-section-title">Profile Snapshot</div>
        <div class="sar-grid sar-grid-4">
            @php
                $snap = [
                    'Date of Birth'   => $profile?->date_of_birth?->format('M j, Y') ?? '—',
                    'Gender'          => $profile?->gender ?? '—',
                    'Civil Status'    => $profile?->civil_status ?? '—',
                    'Place of Birth'  => $profile?->place_of_birth ?? '—',
                    'LRN'             => $profile?->lrn ?? '—',
                    'SHS Track'       => $profile?->shs_track_strand ?? '—',
                    'Junior HS'       => $profile?->junior_school ?? '—',
                    'Senior HS'       => $profile?->senior_school ?? '—',
                    'Address'         => collect([$profile?->present_barangay,$profile?->present_municipality,$profile?->present_province])->filter()->join(', ') ?: '—',
                    'Official Email'  => $profile?->student_email ?? '—',
                    'Mobile'          => $profile?->mobile_number ?? '—',
                    'Profile Status'  => ($profile?->profile_complete ? '✓ Complete' : '⚠ Incomplete'),
                ];
            @endphp
            @foreach($snap as $lbl => $val)
            <div class="sar-field">
                <label>{{ $lbl }}</label>
                <div class="ro-val">{{ $val }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════════════════ PERSONAL INFO TAB ══════════════════ --}}
<div class="sar-tab-content" id="sar-tab-personal" style="display:none;">
    <div class="sar-card">
        <div class="sar-section-title">Enrollment Details</div>
        <div class="sar-grid sar-grid-4">
            <div class="sar-field"><label>Student No.</label><div class="ro-val">{{ $student->student_no }}</div></div>
            <div class="sar-field"><label>Full Name</label><div class="ro-val">{{ $student->name }}</div></div>
            <div class="sar-field"><label>Program</label><div class="ro-val">{{ $program }}</div></div>
            <div class="sar-field"><label>Year Level</label><div class="ro-val">{{ $student->year_level ?: '—' }}</div></div>
            <div class="sar-field"><label>School Year</label><div class="ro-val">{{ $student->school_year ?: '—' }}</div></div>
            <div class="sar-field"><label>Semester</label><div class="ro-val">{{ $student->semester ?: '—' }}</div></div>
            <div class="sar-field"><label>Section</label><div class="ro-val">{{ $student->yearBlock?->block ?? '—' }}</div></div>
            <div class="sar-field"><label>Status</label>
                <div class="ro-val" style="{{ $student->is_withdrawn ? 'color:#dc2626;font-weight:700;' : '' }}">
                    {{ $student->is_withdrawn ? '⚠ Withdrawn' : 'Active' }}
                    @if($student->is_withdrawn && $student->withdrawn_date)
                    <small style="color:#94a3b8;font-size:0.72rem;"> ({{ $student->withdrawn_date->format('M j, Y') }})</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="sar-card">
        <div class="sar-section-title">Personal Information</div>
        <div class="sar-grid sar-grid-3">
            <div class="sar-field"><label>First Name</label><input type="text" id="pf_first_name" value="{{ $profile?->first_name ?? '' }}" placeholder="First name"></div>
            <div class="sar-field"><label>Middle Name</label><input type="text" id="pf_middle_name" value="{{ $profile?->middle_name ?? '' }}" placeholder="Middle name"></div>
            <div class="sar-field"><label>Last Name</label><input type="text" id="pf_last_name" value="{{ $profile?->last_name ?? '' }}" placeholder="Last name"></div>
            <div class="sar-field"><label>Suffix</label><input type="text" id="pf_suffix" value="{{ $profile?->suffix ?? '' }}" placeholder="Jr., III…"></div>
            <div class="sar-field"><label>Nickname</label><input type="text" id="pf_nickname" value="{{ $profile?->nickname ?? '' }}" placeholder="Preferred name"></div>
            <div class="sar-field"><label>Gender</label>
                <select id="pf_gender"><option value="">— Select —</option>@foreach(['Male','Female','Non-binary','Prefer not to say'] as $g)<option value="{{ $g }}" {{ ($profile?->gender??'')===$g?'selected':'' }}>{{ $g }}</option>@endforeach</select>
            </div>
            <div class="sar-field"><label>Date of Birth</label><input type="date" id="pf_date_of_birth" value="{{ $profile?->date_of_birth?->format('Y-m-d') ?? '' }}"></div>
            <div class="sar-field"><label>Civil Status</label>
                <select id="pf_civil_status"><option value="">— Select —</option>@foreach(['Single','Married','Widowed','Separated'] as $cs)<option value="{{ $cs }}" {{ ($profile?->civil_status??'')===$cs?'selected':'' }}>{{ $cs }}</option>@endforeach</select>
            </div>
            <div class="sar-field"><label>Nationality</label><input type="text" id="pf_nationality" value="{{ $profile?->nationality ?? '' }}" placeholder="Filipino"></div>
            <div class="sar-field sar-col-span-3"><label>Place of Birth</label><input type="text" id="pf_place_of_birth" value="{{ $profile?->place_of_birth ?? '' }}" placeholder="City / Province, Country"></div>
        </div>
    </div>

    <div class="sar-card">
        <div class="sar-section-title">Contact & Official Email</div>
        <div class="sar-grid sar-grid-3">
            <div class="sar-field"><label>Mobile Number</label><input type="text" id="pf_mobile_number" value="{{ $profile?->mobile_number ?? '' }}" placeholder="09XXXXXXXXX"></div>
            <div class="sar-field sar-col-span-2"><label>Official PLP Email</label><input type="email" id="pf_student_email" value="{{ $profile?->student_email ?? '' }}" placeholder="firstname.lastname@plp.edu.ph"></div>
        </div>
    </div>

    <div class="sar-card">
        <div class="sar-section-title">Present Address</div>
        <div class="sar-grid sar-grid-3">
            <div class="sar-field sar-col-span-3"><label>Street / House No.</label><input type="text" id="pf_present_street" value="{{ $profile?->present_street ?? '' }}" placeholder="Street address"></div>
            <div class="sar-field"><label>Barangay</label><input type="text" id="pf_present_barangay" value="{{ $profile?->present_barangay ?? '' }}"></div>
            <div class="sar-field"><label>Municipality / City</label><input type="text" id="pf_present_municipality" value="{{ $profile?->present_municipality ?? '' }}"></div>
            <div class="sar-field"><label>Province</label><input type="text" id="pf_present_province" value="{{ $profile?->present_province ?? '' }}"></div>
            <div class="sar-field"><label>Region</label><input type="text" id="pf_present_region" value="{{ $profile?->present_region ?? '' }}"></div>
            <div class="sar-field"><label>ZIP Code</label><input type="text" id="pf_present_zipcode" value="{{ $profile?->present_zipcode ?? '' }}" placeholder="1234"></div>
        </div>
        <div class="sar-save-row">
            <button type="button" id="sarSaveProfileBtn" class="btn-primary">Save Personal Info</button>
        </div>
    </div>
</div>

{{-- ══════════════════ BACKGROUND TAB ══════════════════ --}}
<div class="sar-tab-content" id="sar-tab-background" style="display:none;">
    <div class="sar-card">
        <div class="sar-section-title">Educational Background</div>
        <div class="sar-grid sar-grid-3">
            <div class="sar-field"><label>LRN (Learner Reference No.)</label><input type="text" id="bg_lrn" value="{{ $profile?->lrn ?? '' }}" placeholder="12-digit LRN"></div>
            <div class="sar-field sar-col-span-2"><label>SHS Track / Strand</label><input type="text" id="bg_shs_track_strand" value="{{ $profile?->shs_track_strand ?? '' }}" placeholder="e.g. STEM, ABM, HUMSS"></div>
            <div class="sar-field sar-col-span-3"><label>Junior High School</label><input type="text" id="bg_junior_school" value="{{ $profile?->junior_school ?? '' }}" placeholder="School name and location"></div>
            <div class="sar-field sar-col-span-3"><label>Senior High School</label><input type="text" id="bg_senior_school" value="{{ $profile?->senior_school ?? '' }}" placeholder="School name and location"></div>
        </div>
    </div>

    <div class="sar-card">
        <div class="sar-section-title">Mother's Information</div>
        <div class="sar-grid sar-grid-3">
            <div class="sar-field"><label>First Name</label><input type="text" id="bg_mother_firstname" value="{{ $profile?->mother_firstname ?? '' }}"></div>
            <div class="sar-field"><label>Middle Name</label><input type="text" id="bg_mother_middlename" value="{{ $profile?->mother_middlename ?? '' }}"></div>
            <div class="sar-field"><label>Last Name</label><input type="text" id="bg_mother_lastname" value="{{ $profile?->mother_lastname ?? '' }}"></div>
            <div class="sar-field"><label>Contact No.</label><input type="text" id="bg_mother_contact" value="{{ $profile?->mother_contact ?? '' }}"></div>
            <div class="sar-field"><label>Occupation</label><input type="text" id="bg_mother_occupation" value="{{ $profile?->mother_occupation ?? '' }}"></div>
        </div>
    </div>

    <div class="sar-card">
        <div class="sar-section-title">Father's Information</div>
        <div class="sar-grid sar-grid-3">
            <div class="sar-field"><label>First Name</label><input type="text" id="bg_father_firstname" value="{{ $profile?->father_firstname ?? '' }}"></div>
            <div class="sar-field"><label>Middle Name</label><input type="text" id="bg_father_middlename" value="{{ $profile?->father_middlename ?? '' }}"></div>
            <div class="sar-field"><label>Last Name</label><input type="text" id="bg_father_lastname" value="{{ $profile?->father_lastname ?? '' }}"></div>
            <div class="sar-field"><label>Contact No.</label><input type="text" id="bg_father_contact" value="{{ $profile?->father_contact ?? '' }}"></div>
            <div class="sar-field"><label>Occupation</label><input type="text" id="bg_father_occupation" value="{{ $profile?->father_occupation ?? '' }}"></div>
        </div>
    </div>

    <div class="sar-card">
        <div class="sar-section-title">Guardian (if applicable)</div>
        <div class="sar-grid sar-grid-3">
            <div class="sar-field"><label>First Name</label><input type="text" id="bg_guardian_firstname" value="{{ $profile?->guardian_firstname ?? '' }}"></div>
            <div class="sar-field"><label>Middle Name</label><input type="text" id="bg_guardian_middlename" value="{{ $profile?->guardian_middlename ?? '' }}"></div>
            <div class="sar-field"><label>Last Name</label><input type="text" id="bg_guardian_lastname" value="{{ $profile?->guardian_lastname ?? '' }}"></div>
            <div class="sar-field"><label>Contact No.</label><input type="text" id="bg_guardian_contact" value="{{ $profile?->guardian_contact ?? '' }}"></div>
            <div class="sar-field"><label>Occupation</label><input type="text" id="bg_guardian_occupation" value="{{ $profile?->guardian_occupation ?? '' }}"></div>
        </div>
    </div>

    <div class="sar-card">
        <div class="sar-section-title">Socioeconomic Background</div>
        <div class="sar-grid sar-grid-3">
            <div class="sar-field"><label>Monthly Family Income</label><input type="text" id="bg_monthly_income" value="{{ $profile?->monthly_family_income ?? '' }}" placeholder="e.g. 10,000–20,000"></div>
            <div class="sar-field"><label>Family Income Source</label><input type="text" id="bg_income_source" value="{{ $profile?->family_income_source ?? '' }}" placeholder="e.g. Employment, Business"></div>
            <div class="sar-field"><label>Living Situation</label><input type="text" id="bg_living_situation" value="{{ $profile?->living_situation ?? '' }}" placeholder="With parents, Boarding, etc."></div>
        </div>
        <div class="sar-save-row">
            <button type="button" id="sarSaveBgBtn" class="btn-primary">Save Background Info</button>
        </div>
    </div>
</div>

{{-- ══════════════════ GRADES TAB ══════════════════ --}}
<div class="sar-tab-content" id="sar-tab-grades" style="display:none;">
    <div class="sar-card">
        <div class="sar-g-toolbar">
            <div class="sar-g-filter">
                <label for="sarSyFilter" style="font-size:0.81rem;color:#475569;">Filter by School Year:</label>
                <select id="sarSyFilter" onchange="sarFilterGrades()">
                    <option value="">All School Years</option>
                    @foreach($schoolYears as $sy)<option value="{{ $sy }}">{{ $sy }}</option>@endforeach
                </select>
            </div>
            <button type="button" class="btn-add-grade" onclick="sarOpenGradeModal()">+ Add Grade Record</button>
        </div>

        @forelse($gradesBySyTerm as $termKey => $termRows)
        @php [$sy,$term] = explode('|||',$termKey.'|||'); $tgwa = $termGwa($termRows); $tunits = $termRows->filter(fn($r)=>!$r->inc&&is_numeric($r->final_grade)&&(float)$r->final_grade>0&&(float)$r->units>0)->sum(fn($r)=>(float)$r->units); @endphp
        <div class="sar-term-block" data-sy="{{ $sy }}" data-term="{{ $term }}">
            <div class="sar-term-head">
                <span class="sar-term-label">{{ $sy }} — {{ $term }} Semester</span>
                <span class="sar-term-meta">
                    @if($tgwa)Term GWA: <strong>{{ number_format($tgwa,4) }}</strong> &nbsp;|&nbsp;@endif
                    Units: {{ number_format($tunits,0) }} &nbsp;| {{ $termRows->count() }} subjects
                </span>
            </div>
            <table class="sar-gtable">
                <thead><tr><th>Code</th><th>Equiv. Code</th><th>Description</th><th>Section</th><th>Professor</th><th>Units</th><th>Grade</th><th>Status</th><th>Remarks</th><th></th></tr></thead>
                <tbody>
                @foreach($termRows as $rec)
                <tr data-rid="{{ $rec->id }}">
                    <td><strong>{{ $rec->subject_code }}</strong></td>
                    <td>{{ $rec->equiv_subject_code ?: '—' }}</td>
                    <td>{{ $rec->description ?: '—' }}</td>
                    <td>{{ $rec->section_code ?: '—' }}</td>
                    <td>{{ $rec->professor ?: '—' }}</td>
                    <td>{{ $rec->units ?? '—' }}</td>
                    <td class="{{ $rec->inc ? 'g-inc' : ((!is_null($rec->final_grade)&&(float)$rec->final_grade>0&&(float)$rec->final_grade<=3.0)?'g-pass':((!is_null($rec->final_grade)&&(float)$rec->final_grade>3.0)?'g-fail':'')) }}">{{ $rec->inc ? 'INC' : ($rec->final_grade ?? '—') }}</td>
                    <td>{{ $rec->status ?: '—' }}</td>
                    <td>{{ $rec->remarks ?: '—' }}</td>
                    <td>
                        <div class="sar-act-row">
                            <button class="btn-xs btn-xs-edit" onclick="sarOpenGradeModal({{ $rec->id }})">Edit</button>
                            <button class="btn-xs btn-xs-del" onclick="sarDeleteGrade({{ $rec->id }})">Del</button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @empty
        <div class="sar-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <div class="sar-empty-title">No Grade Records</div>
            <div class="sar-empty-sub">Click <strong>+ Add Grade Record</strong> to begin.</div>
        </div>
        @endforelse

        <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;">
            <div class="sar-section-title"><span>Grade Corrections &amp; Removals</span></div>
            @if(($gradeCorrectionRequests ?? collect())->count())
                <div class="sar-review-list">
                    @foreach($gradeCorrectionRequests as $req)
                        @php
                            $old = (array) ($req->old_values ?? []);
                            $new = (array) ($req->new_values ?? []);
                            $subjectLabel = trim(($old['subject_code'] ?? 'Grade Record') . ' - ' . ($old['description'] ?? ''));
                            $oldGrade = array_key_exists('final_grade', $old) ? ($old['final_grade'] ?? 'blank') : 'blank';
                            $newGrade = array_key_exists('final_grade', $new) ? ($new['final_grade'] ?? 'blank') : 'blank';
                        @endphp
                        <div class="sar-review-item {{ $req->status === 'pending' ? 'pending' : '' }}">
                            <div>
                                <div class="sar-review-title">{{ strtoupper($req->action) }} request: {{ $subjectLabel }}</div>
                                <div class="sar-review-meta">
                                    <span class="sar-review-status {{ $req->status }}">{{ $req->status }}</span>
                                    <span>Requested {{ optional($req->requested_at)->format('M j, Y h:i A') ?: 'recently' }}</span>
                                    @if($req->reviewed_at)<span>Reviewed {{ optional($req->reviewed_at)->format('M j, Y h:i A') }}</span>@endif
                                </div>
                                @if($req->action === 'update')
                                    <div class="sar-review-diff">
                                        Grade: <strong>{{ $oldGrade }}</strong> to <strong>{{ $newGrade }}</strong>
                                        @if(($old['remarks'] ?? null) !== ($new['remarks'] ?? null))
                                            | Remarks updated
                                        @endif
                                    </div>
                                @else
                                    <div class="sar-review-diff">Removal will delete this grade only after approval.</div>
                                @endif
                                @if($req->reason)<div class="sar-review-reason">Reason: {{ $req->reason }}</div>@endif
                                @if($req->reviewer_remarks)<div class="sar-review-reason">Review notes: {{ $req->reviewer_remarks }}</div>@endif
                            </div>
                            <div class="sar-review-actions">
                                @if($req->status === 'pending')
                                    <button type="button" class="btn-xs btn-xs-approve" onclick="sarReviewGradeRequest({{ $req->id }}, 'approve')">Approve</button>
                                    <button type="button" class="btn-xs btn-xs-reject" onclick="sarReviewGradeRequest({{ $req->id }}, 'reject')">Reject</button>
                                @else
                                    <span class="sar-review-status {{ $req->status }}">{{ $req->status }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sar-empty">
                    <div class="sar-empty-title">No Grade Correction Requests</div>
                    <div class="sar-empty-sub">Edits and removals submitted from this page will appear here for approval.</div>
                </div>
            @endif
        </div>

        @if($gwa)
        <div style="margin-top:14px;padding:14px 0 0;border-top:2px solid #e2e8f0;display:flex;justify-content:flex-end;gap:30px;font-size:0.88rem;font-weight:700;color:#334155;">
            <span>Total Units Earned: <span style="color:#004d27;font-size:1rem;">{{ number_format((float)$totalUnitsEarned,0) }}</span></span>
            <span>Overall GWA: <span style="color:#004d27;font-size:1.1rem;">{{ number_format($gwa,4) }}</span></span>
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════ DOCUMENTS TAB ══════════════════ --}}
<div class="sar-tab-content" id="sar-tab-documents" style="display:none;">
    <div class="sar-card">
        <div class="sar-section-title">Required Documents & Requirements</div>
        @if($requirements->count())
        <table class="sar-doc-table">
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th>Status</th>
                    <th>Date Verified</th>
                    <th>Verified By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
            @foreach($requirements as $req)
            @php $defName = optional(optional($req->requirementPolicy)->definition)->requirement_name ?? 'Requirement #'.$req->id; @endphp
            <tr>
                <td><strong>{{ $defName }}</strong></td>
                <td>
                    @if($req->is_submitted)
                    <span class="sar-badge sar-badge-green">✓ Submitted</span>
                    @else
                    <span class="sar-badge sar-badge-yellow">⏳ Pending</span>
                    @endif
                </td>
                <td>{{ $req->date_verified?->format('M j, Y') ?? '—' }}</td>
                <td>{{ optional($req->verifiedBy)->name ?? '—' }}</td>
                <td>{{ $req->remarks ?: '—' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @else
        <div class="sar-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
            <div class="sar-empty-title">No document records linked to this student.</div>
        </div>
        @endif
        <div style="margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;font-size:0.78rem;color:#94a3b8;">
            Manage requirements at
            <a href="{{ route('registrar.process.document-list') }}" style="color:#004d27;font-weight:700;">Admissions → Document Submission</a>
        </div>
    </div>
</div>

{{-- ══════════════════ CERTIFICATES TAB ══════════════════ --}}
<div class="sar-tab-content" id="sar-tab-certificates" style="display:none;">
    <div class="sar-card">
        <div class="sar-section-title">Generate Official Documents</div>
        <div style="font-size:0.8rem;color:#64748b;margin-bottom:18px;">Click any document to open its generation page. Student details are pre-filled from the system.</div>
        <div class="sar-cert-grid">
            <a href="{{ route('registrar.registrar-menu.forms.cor.certificate-of-registration') }}?student_id={{ $student->id }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">📋</div>
                <div class="sar-cert-name">Certificate of Registration (COR)</div>
                <div class="sar-cert-desc">Official enrollment certificate for the current semester</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.cog.copy-of-grades') }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">📊</div>
                <div class="sar-cert-name">Copy of Grades (COG)</div>
                <div class="sar-cert-desc">Official record of all grades per subject and semester</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.official-grade-report') }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">📈</div>
                <div class="sar-cert-name">Official Grade Report</div>
                <div class="sar-cert-desc">Comprehensive official grade report document</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.certificates.certificate-gwa.show', ['student' => $student->id]) }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">🏅</div>
                <div class="sar-cert-name">Certificate of GWA</div>
                <div class="sar-cert-desc">Certifies the student's General Weighted Average</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.certificates.deans-honors.show', ['student' => $student->id]) }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">DH</div>
                <div class="sar-cert-name">Dean's Honors</div>
                <div class="sar-cert-desc">Dean's Honors List Award for this student</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.certificates.presidents-honors.show', ['student' => $student->id]) }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">PH</div>
                <div class="sar-cert-name">President's Honors</div>
                <div class="sar-cert-desc">President's Honors List Award for this student</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.certificates.certificate-graduation-8c2.show', ['student' => $student->id]) }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">🎓</div>
                <div class="sar-cert-name">Certificate of Graduation (8C-2)</div>
                <div class="sar-cert-desc">Official Form 8C-2 certifying graduation</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.certificates.certificate-honor-8d2') }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">⭐</div>
                <div class="sar-cert-name">Certificate of Honor (8D-2)</div>
                <div class="sar-cert-desc">Academic distinction certificate — Latin honors</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.diploma') }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">📜</div>
                <div class="sar-cert-name">Diploma</div>
                <div class="sar-cert-desc">Official PLP diploma for program completion</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.honorable-dismissal.show', ['student' => $student->id]) }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">📄</div>
                <div class="sar-cert-name">Honorable Dismissal</div>
                <div class="sar-cert-desc">Certificate for transferring students</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.graduation-clearance.show', ['student' => $student->id]) }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">✅</div>
                <div class="sar-cert-name">Graduation Clearance</div>
                <div class="sar-cert-desc">Clearance form verifying all requirements for graduation</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.permission-cross-enroll') }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">🔀</div>
                <div class="sar-cert-name">Cross-Enrollment Permit</div>
                <div class="sar-cert-desc">Permission to enroll subjects at another institution</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.request-form-f-137a.show', ['student' => $student->id]) }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">📝</div>
                <div class="sar-cert-name">Request Form for F 137A</div>
                <div class="sar-cert-desc">Formal request for high school records</div>
            </a>
            <a href="{{ route('registrar.registrar-menu.forms.application-leave-of-absence-enrolled.show', ['student' => $student->id]) }}" class="sar-cert-card" target="_blank">
                <div class="sar-cert-icon">⏸️</div>
                <div class="sar-cert-name">Leave of Absence</div>
                <div class="sar-cert-desc">Application form for temporary leave from enrollment</div>
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════ CONDUCT TAB ══════════════════ --}}
<div class="sar-tab-content" id="sar-tab-conduct" style="display:none;">

    {{-- Summary --}}
    @if($openDeficiencies + $openDiscipline > 0)
    <div style="background:#fff5f5;border:1.5px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:16px;display:flex;gap:14px;align-items:center;">
        <span style="font-size:1.4rem;">⚠️</span>
        <div>
            <div style="font-size:0.88rem;font-weight:800;color:#991b1b;">Attention Required</div>
            <div style="font-size:0.8rem;color:#dc2626;margin-top:2px;">
                {{ $openDiscipline }} open conduct incident(s) &nbsp;·&nbsp; {{ $openDeficiencies }} unresolved deficiency(ies)
            </div>
        </div>
    </div>
    @endif

    {{-- Discipline Records --}}
    <div class="sar-card">
        <div class="sar-section-title">Conduct &amp; Discipline Records</div>
        @if($disciplineRecords->count())
        @foreach($disciplineRecords->sortByDesc('incident_date') as $inc)
        @php
            $caseCode = optional($inc->caseType)->code ?? '';
            $isOpen   = !$inc->is_completed;
            $dotClass = str_contains($caseCode,'MAJOR') ? 'major' : (str_contains($caseCode,'MINOR') ? 'minor' : 'couns');
            if(!$isOpen) $dotClass = 'done';
        @endphp
        <div class="sar-conduct-row {{ $isOpen ? 'open-item' : '' }}">
            <div class="sar-conduct-dot {{ $dotClass }}"></div>
            <div class="sar-conduct-body">
                <div class="sar-conduct-title">{{ optional($inc->caseType)->label ?? 'Incident' }}</div>
                <div style="font-size:0.82rem;color:#334155;margin:4px 0;">{{ $inc->description ?: 'No description provided.' }}</div>
                <div class="sar-conduct-meta">
                    @if($inc->incident_date)<span>📅 {{ $inc->incident_date->format('M j, Y') }}</span>@endif
                    @if($inc->counselor)<span>👤 Counselor: {{ $inc->counselor }}</span>@endif
                    @if($inc->called_by)<span>Called by: {{ $inc->called_by }}</span>@endif
                    @if($inc->action_date)<span>Action: {{ $inc->action_date->format('M j, Y') }}</span>@endif
                    @if(optional($inc->actionType)->label)<span>{{ optional($inc->actionType)->label }}</span>@endif
                </div>
                @if($inc->remarks)<div style="font-size:0.78rem;color:#64748b;margin-top:6px;font-style:italic;">Remarks: {{ $inc->remarks }}</div>@endif
            </div>
            <div>
                @if($isOpen)
                <span class="sar-badge sar-badge-red">Open</span>
                @else
                <span class="sar-badge sar-badge-green">Resolved</span>
                @endif
            </div>
        </div>
        @endforeach
        @else
        <div class="sar-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d1fae5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <div class="sar-empty-title" style="color:#065f46;">No Conduct Incidents</div>
            <div class="sar-empty-sub" style="color:#94a3b8;">This student has no discipline records.</div>
        </div>
        @endif
        <div style="margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;font-size:0.78rem;color:#94a3b8;">
            Manage discipline records at <a href="{{ route('registrar.services.student-account.student-discipline') }}" style="color:#004d27;font-weight:700;">Student Services → Student Discipline</a>
        </div>
    </div>

    {{-- Deficiency Records --}}
    <div class="sar-card">
        <div class="sar-section-title">Deficiency Records</div>
        @if($deficiencies->count())
        @foreach($deficiencies->sortBy('is_completed') as $def)
        <div class="sar-def-row {{ !$def->is_completed ? 'pending' : '' }}">
            <div class="sar-def-dept">{{ $def->department ?: '—' }}</div>
            <div class="sar-def-remarks">{{ $def->remarks ?: '—' }}</div>
            <div class="sar-def-date">
                @if($def->date_today) Filed: {{ $def->date_today->format('M j, Y') }}@endif
                @if($def->submission_date) &nbsp;| Due: {{ $def->submission_date->format('M j, Y') }}@endif
            </div>
            @if($def->is_completed)
            <span class="sar-badge sar-badge-green">✓ Cleared</span>
            @else
            <span class="sar-badge sar-badge-yellow">⏳ Pending</span>
            @endif
        </div>
        @endforeach
        @else
        <div class="sar-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d1fae5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <div class="sar-empty-title" style="color:#065f46;">No Deficiencies</div>
        </div>
        @endif
        <div style="margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;font-size:0.78rem;color:#94a3b8;">
            Manage deficiencies at <a href="{{ route('registrar.services.grading-academic.deficiency') }}" style="color:#004d27;font-weight:700;">Grading &amp; Academic → Deficiency Tracking</a>
        </div>
    </div>
</div>

</div>{{-- /sar-wrap --}}

{{-- ── Grade Record Modal ──────────────────────── --}}
<div class="sar-modal-overlay" id="sarGradeModal">
    <div class="sar-modal">
        <div class="sar-modal-title" id="sarGradeModalTitle">Add Grade Record</div>
        <div class="sar-grid sar-grid-3">
            <div class="sar-field"><label>School Year *</label><input type="text" id="gm_sy" placeholder="e.g. 2025-2026"></div>
            <div class="sar-field"><label>Term *</label><select id="gm_term"><option value="">— Select —</option><option>First</option><option>Second</option><option>Summer</option></select></div>
            <div class="sar-field"><label>Subject Code *</label><input type="text" id="gm_code" placeholder="e.g. IT101"></div>
            <div class="sar-field"><label>Equiv. Code</label><input type="text" id="gm_equiv"></div>
            <div class="sar-field sar-col-span-2"><label>Description</label><input type="text" id="gm_desc" placeholder="Full subject name"></div>
            <div class="sar-field"><label>Section</label><input type="text" id="gm_section" placeholder="BSIT 2-A"></div>
            <div class="sar-field sar-col-span-2"><label>Professor</label><input type="text" id="gm_prof"></div>
            <div class="sar-field"><label>Units</label><input type="number" id="gm_units" step="0.5" min="0" max="30"></div>
            <div class="sar-field"><label>Final Grade</label><input type="number" id="gm_grade" step="0.0001" min="0" max="5" placeholder="1.5000"></div>
            <div class="sar-field"><label>Status</label><select id="gm_status"><option value="">— Select —</option><option>PASSED</option><option>FAILED</option><option>DROPPED</option><option>WITHDRAWN</option></select></div>
            <div class="sar-field"><label>Grade Status</label><select id="gm_gstatus"><option value="">— Select —</option><option>REGULAR</option><option>INCOMPLETE</option><option>CONDITIONAL</option></select></div>
            <div class="sar-field sar-col-span-2"><label>Remarks</label><input type="text" id="gm_remarks"></div>
            <div class="sar-field sar-col-span-3" style="flex-direction:row;align-items:center;gap:8px;padding-top:14px;">
                <input type="checkbox" id="gm_inc" style="width:16px;height:16px;margin:0;">
                <label for="gm_inc" style="font-size:0.83rem;color:#334155;cursor:pointer;font-weight:600;">Mark as INC (Incomplete)</label>
            </div>
        </div>
        <div class="sar-modal-actions">
            <button type="button" class="btn-cancel" onclick="sarCloseGradeModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="sarSaveGrade()">Save Record</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
var sarCsrf = (document.querySelector('meta[name="csrf-token"]') || {getAttribute:()=>''}).getAttribute('content');
var sarProfileUrl    = @json($profileUpdateUrl);
var sarGradeStoreUrl = @json($gradeStoreUrl);
var sarUpdateUrlTpl  = @json($gradeUpdateUrlTpl);
var sarDestroyUrlTpl = @json($gradeDestroyUrlTpl);
var sarCorrectionApproveUrlTpl = @json($gradeCorrectionApproveUrlTpl);
var sarCorrectionRejectUrlTpl  = @json($gradeCorrectionRejectUrlTpl);
var sarGradeData     = @json($gradeRecords);
var sarEditingId     = null;

/* ── Tabs ──────────────────────────────────── */
document.querySelectorAll('.sar-tab').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.querySelectorAll('.sar-tab').forEach(function(b){ b.classList.remove('active'); });
        document.querySelectorAll('.sar-tab-content').forEach(function(c){ c.style.display='none'; });
        btn.classList.add('active');
        var t = document.getElementById('sar-tab-' + btn.getAttribute('data-tab'));
        if(t) t.style.display = '';
    });
});

/* ── SY Filter ─────────────────────────────── */
function sarFilterGrades(){
    var sy = document.getElementById('sarSyFilter').value;
    document.querySelectorAll('.sar-term-block').forEach(function(b){
        b.style.display = (!sy || b.getAttribute('data-sy')===sy) ? '' : 'none';
    });
}

/* ── Grade Modal ───────────────────────────── */
function sarOpenGradeModal(id){
    sarEditingId = id || null;
    document.getElementById('sarGradeModalTitle').textContent = id ? 'Edit Grade Record' : 'Add Grade Record';
    var rec = id ? sarGradeData.find(function(r){ return r.id==id; }) : null;
    document.getElementById('gm_sy').value       = rec ? (rec.school_year||'') : '';
    document.getElementById('gm_term').value     = rec ? (rec.term||'') : '';
    document.getElementById('gm_code').value     = rec ? (rec.subject_code||'') : '';
    document.getElementById('gm_equiv').value    = rec ? (rec.equiv_subject_code||'') : '';
    document.getElementById('gm_desc').value     = rec ? (rec.description||'') : '';
    document.getElementById('gm_section').value  = rec ? (rec.section_code||'') : '';
    document.getElementById('gm_prof').value     = rec ? (rec.professor||'') : '';
    document.getElementById('gm_units').value    = rec ? (rec.units!==null?rec.units:'') : '';
    document.getElementById('gm_grade').value    = rec ? (rec.final_grade!==null?rec.final_grade:'') : '';
    document.getElementById('gm_status').value   = rec ? (rec.status||'') : '';
    document.getElementById('gm_gstatus').value  = rec ? (rec.grade_status||'') : '';
    document.getElementById('gm_remarks').value  = rec ? (rec.remarks||'') : '';
    document.getElementById('gm_inc').checked    = rec ? !!rec.inc : false;
    document.getElementById('sarGradeModal').classList.add('open');
}
function sarCloseGradeModal(){
    document.getElementById('sarGradeModal').classList.remove('open');
    sarEditingId = null;
}
function sarSaveGrade(){
    var sy=document.getElementById('gm_sy').value.trim(), trm=document.getElementById('gm_term').value, sc=document.getElementById('gm_code').value.trim();
    if(!sy||!trm||!sc){ sarToast('School Year, Term, and Subject Code are required.','warning'); return; }
    var payload = {
        school_year:sy, term:trm, subject_code:sc,
        equiv_subject_code:document.getElementById('gm_equiv').value.trim()||null,
        description:document.getElementById('gm_desc').value.trim()||null,
        section_code:document.getElementById('gm_section').value.trim()||null,
        professor:document.getElementById('gm_prof').value.trim()||null,
        units:document.getElementById('gm_units').value!==''?parseFloat(document.getElementById('gm_units').value):null,
        final_grade:document.getElementById('gm_grade').value!==''?parseFloat(document.getElementById('gm_grade').value):null,
        status:document.getElementById('gm_status').value||null,
        grade_status:document.getElementById('gm_gstatus').value||null,
        remarks:document.getElementById('gm_remarks').value.trim()||null,
        inc:document.getElementById('gm_inc').checked?1:0,
    };
    if(sarEditingId){
        var correctionReason = prompt('Reason for grade correction request:');
        if(correctionReason===null) return;
        payload.reason = correctionReason;
    }
    var url=sarEditingId?sarUpdateUrlTpl.replace('__RECORD__',sarEditingId):sarGradeStoreUrl;
    sarReq(url,sarEditingId?'PUT':'POST',payload).then(function(d){
        if(!d.success){ sarToast(d.message||'Error.','error'); return; }
        sarToast(d.message||'Saved.','success');
        sarCloseGradeModal();
        setTimeout(function(){ window.location.reload(); },700);
    }).catch(function(){ sarToast('Network error.','error'); });
}
function sarDeleteGrade(id){
    var reason = prompt('Reason for grade removal request:');
    if(reason===null) return;
    sarReq(sarDestroyUrlTpl.replace('__RECORD__',id),'DELETE',{reason:reason}).then(function(d){
        if(!d.success){ sarToast(d.message||'Error.','error'); return; }
        sarToast(d.message||'Grade removal request submitted.','success');
        setTimeout(function(){ window.location.reload(); },600);
    }).catch(function(){ sarToast('Network error.','error'); });
}
function sarReviewGradeRequest(id, action){
    var label = action === 'approve' ? 'approve and apply this grade request' : 'reject this grade request';
    if(!confirm('Do you want to ' + label + '?')) return;
    var notes = prompt('Review notes (optional):');
    if(notes===null) return;
    var tpl = action === 'approve' ? sarCorrectionApproveUrlTpl : sarCorrectionRejectUrlTpl;
    sarReq(tpl.replace('__REQUEST__', id), 'POST', {reviewer_remarks:notes}).then(function(d){
        if(!d.success){ sarToast(d.message||'Error.','error'); return; }
        sarToast(d.message||'Request reviewed.','success');
        setTimeout(function(){ window.location.reload(); },600);
    }).catch(function(){ sarToast('Network error.','error'); });
}

/* ── Profile Save ──────────────────────────── */
function buildProfilePayload(){
    var g=function(id){ var e=document.getElementById(id); return e?e.value.trim()||null:null; };
    return {
        first_name:g('pf_first_name'), middle_name:g('pf_middle_name'), last_name:g('pf_last_name'),
        suffix:g('pf_suffix'), gender:g('pf_gender'), civil_status:g('pf_civil_status'),
        date_of_birth:g('pf_date_of_birth'), place_of_birth:g('pf_place_of_birth'),
        mobile_number:g('pf_mobile_number'), student_email:g('pf_student_email'),
        nationality:g('pf_nationality'), nickname:g('pf_nickname'),
        present_street:g('pf_present_street'), present_barangay:g('pf_present_barangay'),
        present_municipality:g('pf_present_municipality'), present_province:g('pf_present_province'),
        present_region:g('pf_present_region'), present_zipcode:g('pf_present_zipcode'),
    };
}
function buildBgPayload(){
    var g=function(id){ var e=document.getElementById(id); return e?e.value.trim()||null:null; };
    return {
        lrn:g('bg_lrn'), shs_track_strand:g('bg_shs_track_strand'),
        junior_school:g('bg_junior_school'), senior_school:g('bg_senior_school'),
        mother_firstname:g('bg_mother_firstname'), mother_middlename:g('bg_mother_middlename'),
        mother_lastname:g('bg_mother_lastname'), mother_contact:g('bg_mother_contact'), mother_occupation:g('bg_mother_occupation'),
        father_firstname:g('bg_father_firstname'), father_middlename:g('bg_father_middlename'),
        father_lastname:g('bg_father_lastname'), father_contact:g('bg_father_contact'), father_occupation:g('bg_father_occupation'),
        guardian_firstname:g('bg_guardian_firstname'), guardian_middlename:g('bg_guardian_middlename'),
        guardian_lastname:g('bg_guardian_lastname'), guardian_contact:g('bg_guardian_contact'), guardian_occupation:g('bg_guardian_occupation'),
        monthly_family_income:g('bg_monthly_income'), family_income_source:g('bg_income_source'), living_situation:g('bg_living_situation'),
    };
}
function sarSaveProfile(btnId, payload){
    var btn = document.getElementById(btnId);
    btn.disabled=true; btn.textContent='Saving…';
    sarReq(sarProfileUrl,'PUT',payload).then(function(d){
        btn.disabled=false; btn.textContent='Save';
        if(!d.success){ sarToast(d.message||'Error.','error'); return; }
        btn.textContent = btn.id==='sarSaveProfileBtn' ? 'Save Personal Info' : 'Save Background Info';
        sarToast('Saved successfully.','success');
    }).catch(function(){ btn.disabled=false; btn.textContent='Save'; sarToast('Network error.','error'); });
}
document.getElementById('sarSaveProfileBtn').addEventListener('click', function(){ sarSaveProfile('sarSaveProfileBtn', buildProfilePayload()); });
document.getElementById('sarSaveBgBtn').addEventListener('click', function(){ sarSaveProfile('sarSaveBgBtn', buildBgPayload()); });

/* ── Helpers ───────────────────────────────── */
function sarReq(url,method,payload){
    var h={'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':sarCsrf};
    var o={method:method,credentials:'same-origin',headers:h};
    if(payload){h['Content-Type']='application/json';o.body=JSON.stringify(payload);}
    return fetch(url,o).then(function(r){return r.json();});
}
function sarToast(msg,type){
    if(typeof showRegistrarToast==='function'){showRegistrarToast(msg,type);return;}
    alert(msg);
}
document.getElementById('sarGradeModal').addEventListener('click',function(e){ if(e.target===this) sarCloseGradeModal(); });
</script>
@endpush
@endsection
