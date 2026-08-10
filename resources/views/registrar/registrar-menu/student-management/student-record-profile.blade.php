@extends('layouts.registrar')

@section('title', 'Student Profile – ' . ($student->profile ? trim($student->profile->first_name.' '.$student->profile->last_name) : $student->name))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ file_exists(public_path('css/forms.css')) ? filemtime(public_path('css/forms.css')) : time() }}">
<style>
.srp-cert-card-btn { cursor: pointer; border: none; font-family: inherit; width: 100%; }
.srp-cert-card-btn:disabled { cursor: not-allowed; opacity: 0.5; }
.srp-cert-card-btn:disabled:hover { border-color: #e2e8f0 !important; background: #fafafa !important; color: #374151 !important; }
/* ── shell ─────────────────────────────────────────────────── */
.srp-page { padding:0; }

/* ── hero header card ───────────────────────────────────────── */
.srp-hero {
    background:linear-gradient(135deg,#004d27 0%,#006837 55%,#1a9e54 100%);
    padding:28px 32px 0; color:#fff; position:relative; overflow:hidden;
}
.srp-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:260px; height:260px; border-radius:50%;
    background:rgba(255,255,255,.05); pointer-events:none;
}
.srp-hero-top { display:flex; align-items:flex-start; gap:20px; flex-wrap:wrap; }
.srp-avatar-lg {
    width:80px; height:80px; border-radius:50%;
    border:3px solid rgba(255,255,255,.35);
    background:rgba(255,255,255,.18);
    display:flex; align-items:center; justify-content:center;
    font-size:30px; font-weight:700; color:#fff; flex-shrink:0;
    text-transform:uppercase; letter-spacing:1px;
}
.srp-hero-info { flex:1; min-width:0; }
.srp-hero-name { font-size:22px; font-weight:700; line-height:1.2; margin-bottom:6px; }
.srp-hero-meta { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:8px; }
.srp-hero-pill {
    background:rgba(255,255,255,.18); border-radius:20px;
    padding:3px 12px; font-size:11.5px; font-weight:600;
}
.srp-hero-pill.warn { background:rgba(239,68,68,.35); }
.srp-hero-contact { font-size:12.5px; color:rgba(255,255,255,.8); display:flex; gap:14px; flex-wrap:wrap; }

.srp-gwa-box {
    background:rgba(255,255,255,.12); border-radius:12px;
    padding:14px 20px; text-align:center; min-width:130px; flex-shrink:0;
}
.srp-gwa-num  { font-size:32px; font-weight:700; line-height:1; }
.srp-gwa-lbl  { font-size:11px; color:rgba(255,255,255,.7); margin-top:3px; letter-spacing:.05em; text-transform:uppercase; }
.srp-standing {
    display:inline-block; margin-top:8px;
    padding:3px 10px; border-radius:12px; font-size:11px; font-weight:700;
}
.srp-grades-needed {
    display:inline-flex; align-items:center; gap:4px; margin-top:6px;
    padding:3px 10px; border-radius:12px; font-size:11px; font-weight:700;
    background:rgba(251,191,36,.22); color:#fde68a;
}

/* back link */
.srp-back {
    display:inline-flex; align-items:center; gap:5px;
    color:rgba(255,255,255,.8); font-size:12px; text-decoration:none;
    margin-bottom:14px;
}
.srp-back:hover { color:#fff; }

/* ── tab bar ────────────────────────────────────────────────── */
.srp-tabs {
    display:flex; gap:2px; padding:0 32px 8px; margin-top:20px;
    overflow-x:auto; overflow-y:hidden; scrollbar-width:thin;
    scrollbar-color:rgba(255,255,255,.55) rgba(255,255,255,.12);
    max-width:100%;
    -webkit-overflow-scrolling:touch;
}
.srp-tabs::-webkit-scrollbar { height:8px; }
.srp-tabs::-webkit-scrollbar-track { background:rgba(255,255,255,.12); border-radius:999px; }
.srp-tabs::-webkit-scrollbar-thumb { background:rgba(255,255,255,.55); border-radius:999px; }
.srp-tab {
    padding:10px 18px; font-size:13px; font-weight:600;
    border-radius:10px 10px 0 0; cursor:pointer; border:none;
    background:rgba(255,255,255,.12); color:rgba(255,255,255,.8);
    display:flex; align-items:center; gap:6px; white-space:nowrap;
    flex:0 0 auto;
    transition:background .15s;
}
.srp-tab:hover { background:rgba(255,255,255,.2); color:#fff; }
.srp-tab.active { background:#fff; color:#004d27; }
.srp-tab-title-case { text-transform:none; }
.srp-tab-badge {
    background:#ef4444; color:#fff; border-radius:9px;
    padding:1px 6px; font-size:10px; font-weight:700; line-height:14px;
}
.srp-tab-badge.blue { background:#3b82f6; }

/* ── tab panels ─────────────────────────────────────────────── */
.srp-body  { padding:24px 32px; }
.srp-panel { display:none; }
.srp-panel.active { display:block; }

/* ── section card ───────────────────────────────────────────── */
.srp-card {
    background:#fff; border-radius:12px;
    box-shadow:0 1px 4px rgba(0,0,0,.08);
    margin-bottom:18px; overflow:hidden;
}
.srp-card-head {
    padding:13px 18px; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; gap:8px;
    font-size:13px; font-weight:700; color:#1e293b;
}
.srp-card-head svg { color:#004d27; }
.srp-card-body { padding:18px; }

/* ── field grid ─────────────────────────────────────────────── */
.srp-fields { display:grid; grid-template-columns:1fr 1fr; gap:14px 24px; }
.srp-fields.cols3 { grid-template-columns:repeat(3,1fr); }
@media(max-width:700px) { .srp-fields, .srp-fields.cols3 { grid-template-columns:1fr; } }
.srp-field label { font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:4px; }
.srp-field input, .srp-field select, .srp-field textarea {
    width:100%; border:1.5px solid #e2e8f0; border-radius:8px;
    padding:8px 10px; font-size:13px; color:#1e293b; background:#fafafa;
    transition:border .15s;
}
.srp-field input:focus, .srp-field select:focus, .srp-field textarea:focus {
    border-color:#004d27; outline:none; background:#fff;
}
.srp-field .val {
    font-size:13px; color:#1e293b; padding:7px 0;
    border-bottom:1px dashed #e2e8f0;
}
.srp-field .val-empty { color:#94a3b8; font-style:italic; }

/* ── save bar ───────────────────────────────────────────────── */
.srp-save-bar { display:flex; justify-content:flex-end; gap:10px; margin-top:16px; padding-top:14px; border-top:1px solid #f1f5f9; }
.srp-btn-save {
    background:linear-gradient(135deg,#004d27,#006837);
    color:#fff; border:none; border-radius:8px;
    padding:8px 22px; font-size:13px; font-weight:600; cursor:pointer;
    display:inline-flex; align-items:center; gap:6px;
}
.srp-btn-save:hover { opacity:.88; }
.srp-btn-primary {
    background:linear-gradient(135deg,#004d27,#006837);
    color:#fff; border:none; border-radius:8px;
    padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer;
    display:inline-flex; align-items:center; gap:6px;
}
.srp-btn-primary:hover { opacity:.88; }

#srp-tab-scholarships .srp-sch-form-section {
    border:1px solid #e2e8f0;
    border-radius:10px;
    background:#fbfcfd;
    padding:14px;
    margin-bottom:14px;
}
#srp-tab-scholarships .srp-sch-section-title {
    color:#004d27;
    font-size:12px;
    font-weight:800;
    margin-bottom:12px;
    display:flex;
    align-items:center;
    gap:7px;
}
#srp-tab-scholarships .srp-sch-section-title::before {
    content:'';
    width:7px;
    height:7px;
    border-radius:50%;
    background:#004d27;
}
#srp-tab-scholarships .srp-sch-grid {
    display:grid;
    grid-template-columns:repeat(12,minmax(0,1fr));
    gap:12px 14px;
    align-items:start;
}
#srp-tab-scholarships .srp-sch-grid .srp-field { grid-column:span 4; min-width:0; }
#srp-tab-scholarships .srp-sch-grid .srp-field.wide { grid-column:span 6; }
#srp-tab-scholarships .srp-sch-grid .srp-field.full { grid-column:1/-1; }
#srp-tab-scholarships .srp-field select,
#srp-tab-scholarships .srp-field input,
#srp-tab-scholarships .srp-field textarea { box-sizing:border-box; }
#srp-tab-scholarships .srp-sch-table-wrap { overflow-x:auto; }
#srp-tab-scholarships .srp-sch-table { min-width:980px; }
#srp-tab-scholarships .srp-sch-edit-cell { width:320px; }
#srp-tab-scholarships .srp-sch-edit-panel {
    border:1px solid #dbe7df;
    border-radius:8px;
    background:#f8fafc;
    padding:10px;
    margin-top:8px;
}
#srp-tab-scholarships .srp-sch-edit-panel .srp-field { margin-bottom:8px; }
#srp-tab-scholarships .srp-sch-edit-actions {
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    align-items:center;
    margin-top:8px;
}
@media(max-width:900px) {
    #srp-tab-scholarships .srp-sch-grid .srp-field,
    #srp-tab-scholarships .srp-sch-grid .srp-field.wide { grid-column:span 6; }
}
@media(max-width:640px) {
    #srp-tab-scholarships .srp-sch-grid .srp-field,
    #srp-tab-scholarships .srp-sch-grid .srp-field.wide { grid-column:1/-1; }
}

/* ── medical modal ──────────────────────────────────────────── */
.med-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9000; align-items:center; justify-content:center; }
.med-overlay.open { display:flex; }
.med-modal { background:#fff; border-radius:14px; width:min(780px,95vw); max-height:88vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
.med-modal-head { display:flex; align-items:center; justify-content:space-between; padding:18px 24px; border-bottom:1px solid #e2e8f0; }
.med-modal-head h3 { font-size:16px; font-weight:700; color:#0f172a; margin:0; }
.med-modal-body { padding:20px 24px; }
.med-section-title { font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.07em; margin:20px 0 10px; border-bottom:1px solid #f1f5f9; padding-bottom:6px; }
.med-section-title:first-child { margin-top:0; }
.med-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.med-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
.med-field { display:flex; flex-direction:column; gap:4px; }
.med-field label { font-size:11.5px; font-weight:600; color:#475569; }
.med-field input, .med-field select, .med-field textarea {
    border:1.5px solid #e2e8f0; border-radius:7px; padding:7px 10px;
    font-size:13px; color:#1e293b; width:100%; box-sizing:border-box;
    transition:border-color .15s;
}
.med-field input:focus, .med-field select:focus, .med-field textarea:focus { border-color:#006837; outline:none; }
.med-field textarea { resize:vertical; min-height:70px; }
.med-modal-foot { display:flex; justify-content:flex-end; gap:10px; padding:16px 24px; border-top:1px solid #f1f5f9; }
.med-btn-cancel { background:#f1f5f9; color:#475569; border:none; border-radius:8px; padding:8px 20px; font-size:13px; font-weight:600; cursor:pointer; }
.med-btn-cancel:hover { background:#e2e8f0; }
.med-close-btn { background:none; border:none; cursor:pointer; color:#94a3b8; font-size:20px; line-height:1; padding:0; }
.med-close-btn:hover { color:#475569; }

/* ── table shared ───────────────────────────────────────────── */
.srp-tbl { width:100%; border-collapse:collapse; font-size:13px; }
.srp-tbl th { background:#f8fafc; color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; padding:9px 12px; border-bottom:1px solid #e2e8f0; text-align:left; }
.srp-tbl td { padding:10px 12px; border-bottom:1px solid #f1f5f9; color:#374151; vertical-align:top; }
.srp-tbl tr:last-child td { border-bottom:none; }
.srp-tbl tr:hover td { background:#fafbfc; }

/* ── badges ─────────────────────────────────────────────────── */
.badge-green { display:inline-block; background:#d1fae5; color:#065f46; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; }
.badge-yellow{ display:inline-block; background:#fef9c3; color:#713f12; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; }
.badge-red   { display:inline-block; background:#fee2e2; color:#991b1b; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; }
.badge-blue  { display:inline-block; background:#dbeafe; color:#1e40af; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; }
.badge-gray  { display:inline-block; background:#f1f5f9; color:#475569; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; }

/* ── cert grid ──────────────────────────────────────────────── */
.srp-cert-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; }
.srp-cert-card {
    border:1.5px solid #e2e8f0; border-radius:10px;
    padding:16px 14px; text-align:center;
    text-decoration:none; color:#374151;
    transition:all .15s; background:#fafafa;
}
.srp-cert-card:hover { border-color:#004d27; background:#f0fdf4; color:#004d27; }
.srp-cert-card svg { color:#004d27; margin-bottom:8px; }
.srp-cert-card .srp-cert-name { font-size:12px; font-weight:600; line-height:1.4; }

/* ── conduct dots ───────────────────────────────────────────── */
.conduct-dot { width:10px;height:10px;border-radius:50%;display:inline-block;flex-shrink:0;margin-top:3px; }

/* ── curriculum table ───────────────────────────────────────── */
.curr-group-head { background:#f0fdf4; }
.curr-group-head td { font-size:12px; font-weight:700; color:#065f46; padding:8px 12px; }
.curr-check { width:18px;height:18px;border-radius:4px;border:2px solid #d1d5db;display:inline-flex;align-items:center;justify-content:center; }
.curr-check.done { background:#004d27; border-color:#004d27; }
.curr-check:not(.done) { display:none; }

/* ── schedule badge ─────────────────────────────────────────── */
.sched-pill { display:inline-block; background:#f0fdf4; color:#065f46; border-radius:6px; padding:2px 8px; font-size:11px; font-weight:600; margin-right:3px; }

/* ── toast ──────────────────────────────────────────────────── */
#srp-toast {
    position:fixed; bottom:24px; right:24px; z-index:9999;
    background:#1e293b; color:#fff; padding:10px 18px;
    border-radius:10px; font-size:13px; font-weight:600;
    opacity:0; pointer-events:none; transition:opacity .25s;
    display:flex; align-items:center; gap:8px; max-width:320px;
}
#srp-toast.show { opacity:1; }
#srp-toast.success { background:#004d27; }
#srp-toast.error   { background:#991b1b; }

/* ── enrolled sy term header ────────────────────────────────── */
.enr-term-head {
    background:linear-gradient(90deg,#f0fdf4,#fff);
    border-left:3px solid #004d27;
    padding:8px 14px; font-size:12.5px; font-weight:700; color:#004d27;
    margin-bottom:0;
}

.enr-summary-strip { display:flex; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
.enr-summary-card {
    min-width:170px; background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    padding:16px; box-shadow:0 1px 5px rgba(15,23,42,.07);
    display:flex; align-items:center; gap:13px; flex:0 1 220px;
}
.enr-summary-icon {
    width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.enr-summary-card--subjects .enr-summary-icon { background:#dbeafe; color:#2563eb; }
.enr-summary-card--units .enr-summary-icon { background:#dcfce7; color:#16a34a; }
.enr-summary-card--passed .enr-summary-icon { background:#fef9c3; color:#ca8a04; }
.enr-summary-value { color:#0f172a; font-size:26px; font-weight:900; line-height:1; }
.enr-summary-label { color:#64748b; font-size:11px; font-weight:800; margin-top:4px; text-transform:uppercase; letter-spacing:.05em; }
.enr-term-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    box-shadow:0 1px 5px rgba(15,23,42,.07); margin-bottom:18px; overflow:hidden;
}
.enr-term-headline {
    background:linear-gradient(135deg,#f0fdf4 0%,#ffffff 70%);
    border-bottom:1px solid #e2e8f0; padding:14px 16px;
    display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;
}
.enr-term-title { display:flex; align-items:center; gap:9px; color:#064e3b; font-size:14px; font-weight:800; }
.enr-term-icon {
    width:32px; height:32px; border-radius:8px; background:#dcfce7; color:#047857;
    display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
}
.enr-term-meta { display:flex; gap:6px; flex-wrap:wrap; }
.enr-subject-grid {
    display:grid; grid-template-columns:repeat(auto-fill,minmax(270px,1fr));
    gap:12px; padding:14px; background:#f8fafc;
}
.enr-subject-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:10px;
    padding:14px; display:flex; flex-direction:column; gap:12px;
    box-shadow:0 1px 3px rgba(15,23,42,.05);
}
.enr-subject-top { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; }
.enr-subject-code { color:#004d27; font-size:12px; font-weight:900; letter-spacing:.04em; text-transform:uppercase; }
.enr-subject-name { color:#0f172a; font-size:14px; font-weight:800; line-height:1.35; margin-top:3px; }
.enr-units-pill {
    background:#eff6ff; color:#1d4ed8; border-radius:999px;
    padding:4px 10px; font-size:11px; font-weight:800; white-space:nowrap;
}
.enr-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.enr-detail { background:#f8fafc; border-radius:8px; padding:9px 10px; min-width:0; }
.enr-detail.full { grid-column:1/-1; }
.enr-detail-label { display:block; color:#64748b; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; }
.enr-detail-value { color:#334155; font-size:12.5px; font-weight:650; line-height:1.35; }
.enr-grade-row {
    display:grid; grid-template-columns:repeat(4,1fr); gap:6px;
    padding-top:10px; border-top:1px solid #eef2f7;
}
.enr-grade-box { background:#fbfcfd; border:1px solid #eef2f7; border-radius:8px; padding:8px 6px; text-align:center; }
.enr-grade-label { display:block; color:#64748b; font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; margin-bottom:3px; }
.enr-grade-value { color:#0f172a; font-size:12px; font-weight:800; }
.enr-remarks {
    display:inline-flex; align-items:center; justify-content:center;
    border-radius:999px; padding:4px 10px; font-size:11px; font-weight:800;
}
.enr-remarks.pass { background:#dcfce7; color:#166534; }
.enr-remarks.fail { background:#fee2e2; color:#991b1b; }
.enr-remarks.neutral { background:#f1f5f9; color:#475569; }
@media(max-width:720px) {
    .enr-subject-grid { grid-template-columns:1fr; padding:10px; }
    .enr-detail-grid, .enr-grade-row { grid-template-columns:1fr 1fr; }
}
.docreq-add-btn {
    margin-left:auto; background:#004d27; color:#fff; border:none; border-radius:8px;
    padding:7px 12px; font-size:12px; font-weight:800; cursor:pointer;
    display:inline-flex; align-items:center; gap:6px;
}
.docreq-add-btn:hover { background:#006837; }
.docreq-count { font-size:12px;color:#64748b;font-weight:400; }
.docreq-add-panel {
    display:none; padding:14px 18px; border-bottom:1px solid #e2e8f0; background:#f8fafc;
}
.docreq-add-panel.open { display:block; }
.docreq-add-form { display:grid; grid-template-columns:1.3fr 180px 1.4fr auto; gap:10px; align-items:end; }
.docreq-field label { display:block; color:#64748b; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; }
.docreq-field input, .docreq-field select {
    width:100%; height:36px; border:1px solid #dbe4df; border-radius:8px; padding:0 10px; font-size:13px; color:#1e293b; background:#fff;
}
.docreq-submit { height:36px; white-space:nowrap; }
@media(max-width:900px) { .docreq-add-form { grid-template-columns:1fr; } .docreq-submit { width:100%; } }
</style>
@endpush

@section('content')
@php
    $prof    = $student->profile;
    $isWD    = $student->is_withdrawn ?? false;
    $fname   = $prof ? trim($prof->first_name . ' ' . ($prof->middle_name ? $prof->middle_name[0].'.' : '') . ' ' . $prof->last_name . ($prof->suffix ? ', '.$prof->suffix : '')) : $student->name;
    $fname   = trim($fname) ?: $student->name;
    $parts   = explode(' ', strtoupper($fname));
    $initials= substr($parts[0] ?? '?',0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : '');
    $program = $student->program ?: optional($student->canonicalCourse)->code ?: 'N/A';
    $course  = optional($student->canonicalCourse)->name ?: $student->program ?: 'N/A';
    $openBad = $disciplineRecords->where('status','open')->count() + ($deficiencies->where('is_completed',false)->count());
    $pendingDocs = $requirements->filter(function($r) { return !$r->is_submitted; })->count();
@endphp

<div class="srp-page">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- HERO HEADER                                            --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="srp-hero">
        <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records') }}" class="srp-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back to Student Records
        </a>
        <div class="srp-hero-top">
            <div class="srp-avatar-lg">{{ $initials }}</div>
            <div class="srp-hero-info">
                <div class="srp-hero-name">{{ $fname }}</div>
                <div class="srp-hero-meta">
                    <span class="srp-hero-pill">{{ $student->student_no }}</span>
                    <span class="srp-hero-pill">{{ $course }}</span>
                    <span class="srp-hero-pill">{{ $student->year_level ?: 'N/A' }}</span>
                    <span class="srp-hero-pill">AY {{ $student->school_year ?: 'N/A' }} · {{ $student->semester ?: 'N/A' }}</span>
                    @if($isWD) <span class="srp-hero-pill warn">Withdrawn</span> @endif
                </div>
                <div class="srp-hero-contact">
                    @if($prof && $prof->mobile_number) <span>📞 {{ $prof->mobile_number }}</span> @endif
                    @if($prof && $prof->student_email) <span>✉ {{ $prof->student_email }}</span> @endif
                    @if($prof && $prof->present_municipality) <span>📍 {{ $prof->present_municipality }}, {{ $prof->present_province }}</span> @endif
                </div>
            </div>
            <div class="srp-gwa-box">
                <div class="srp-gwa-num">{{ $cwa !== null ? number_format($cwa,2) : '—' }}</div>
                <div class="srp-gwa-lbl">CWA</div>
                <div class="srp-standing" style="background:{{ $academicStanding['bg'] }};color:{{ $academicStanding['color'] }};">
                    {{ $academicStanding['icon'] }} {{ $academicStanding['label'] }}
                </div>
                @if($missingGradesCount > 0)
                <div class="srp-grades-needed" title="Grades still needed this semester before CWA is final">
                    ⏳ {{ $missingGradesCount }} grade{{ $missingGradesCount === 1 ? '' : 's' }} needed
                </div>
                @endif
            </div>
        </div>

        {{-- Tab bar --}}
        <div class="srp-tabs">
            <button class="srp-tab active" data-tab="info">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5z"/><path d="M20.59 22c0-3.87-3.85-7-8.59-7S3.41 18.13 3.41 22"/></svg>
                Student Information
            </button>
            <button class="srp-tab" data-tab="background">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Background
            </button>
            <button class="srp-tab srp-tab-title-case" data-tab="subjects">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Enrollment Courses
                @if($enrolledSubjects->count())<span class="srp-tab-badge blue">{{ $enrolledSubjects->count() }}</span>@endif
            </button>
            <button class="srp-tab" data-tab="curriculum">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 16.74V4.67C22 3.47 21.02 2.58 19.83 2.68H19.77C17.67 2.86 14.48 3.93 12.7 5.05L12.53 5.16C12.24 5.34 11.76 5.34 11.47 5.16L11.22 5.01C9.44 3.9 6.26 2.84 4.16 2.67C2.97 2.57 2 3.47 2 4.66V16.74C2 17.7 2.78 18.6 3.74 18.72L4.03 18.76C6.2 19.05 9.55 20.15 11.47 21.2L11.51 21.22C11.78 21.37 12.21 21.37 12.47 21.22C14.39 20.16 17.75 19.05 19.93 18.76L20.26 18.72C21.22 18.6 22 17.7 22 16.74Z"/></svg>
                Curriculum
            </button>
            <button class="srp-tab" data-tab="scholarships" style="display:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3L3 7.5L12 12L21 7.5L12 3Z"/><path d="M5 10v5.5C5 17.43 8.13 19 12 19s7-1.57 7-3.5V10"/><path d="M21 7.5V13"/></svg>
                Scholarships
                @if(($studentScholarships ?? collect())->count())<span class="srp-tab-badge blue">{{ $studentScholarships->count() }}</span>@endif
            </button>
            <button class="srp-tab" data-tab="scholastic-comments">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                Scholastic Comments
                @if(($scholasticComments ?? collect())->count())<span class="srp-tab-badge blue">{{ $scholasticComments->count() }}</span>@endif
            </button>
            <button class="srp-tab" data-tab="documents">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Documents
                @if($pendingDocs > 0)<span class="srp-tab-badge">{{ $pendingDocs }}</span>@endif
            </button>
            <button class="srp-tab" data-tab="certificates">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
                Certificates
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- TAB PANELS                                             --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="srp-body">

        {{-- ─────────────────────────────────────────────────── --}}
        {{-- TAB 1 · STUDENT INFORMATION                        --}}
        {{-- ─────────────────────────────────────────────────── --}}
        <div class="srp-panel active" id="srp-tab-info">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

                {{-- Enrollment Details (read-only) --}}
                <div class="srp-card">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Enrollment Details
                    </div>
                    <div class="srp-card-body">
                        <div class="srp-fields">
                            @foreach([
                                ['Student No.',  $student->student_no],
                                ['Program',      $course],
                                ['Year Level',   $student->year_level],
                                ['Academic Year',  $student->school_year ? 'AY '.$student->school_year : null],
                                ['Semester',     $student->semester],
                                ['Section',      optional($student->yearBlock)->block_name],
                                ['Curriculum',   $student->curriculum],
                                ['Scholarship',  $student->scholarship],
                                ['Status',       $isWD ? 'Withdrawn' : 'Active'],
                            ] as [$lbl,$val])
                            <div class="srp-field">
                                <label>{{ $lbl }}</label>
                                <div class="val {{ !$val ? 'val-empty' : '' }}">
                                    @if($lbl === 'Status')
                                        <span class="{{ $isWD ? 'badge-red' : 'badge-green' }}">{{ $val }}</span>
                                    @else
                                        {{ $val ?: '—' }}
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($isWD)
                        <div style="margin-top:14px;background:#fff1f2;border-radius:8px;padding:10px 14px;font-size:12.5px;color:#991b1b;">
                            <strong>Withdrawn:</strong> {{ $student->withdrawn_date ? $student->withdrawn_date->format('F j, Y') : 'Date not recorded' }}
                            @if($student->withdrawn_remarks) — {{ $student->withdrawn_remarks }} @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Personal Information (editable) --}}
                <div class="srp-card">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5z"/><path d="M20.59 22c0-3.87-3.85-7-8.59-7S3.41 18.13 3.41 22"/></svg>
                        Personal Information
                        <span style="margin-left:auto;font-size:11px;font-weight:400;color:#94a3b8;">Click Save to update</span>
                    </div>
                    <div class="srp-card-body">
                        <div class="srp-fields">
                            <div class="srp-field"><label>First Name</label><input id="pi_first_name" value="{{ $prof->first_name ?? '' }}"></div>
                            <div class="srp-field"><label>Last Name</label><input id="pi_last_name" value="{{ $prof->last_name ?? '' }}"></div>
                            <div class="srp-field"><label>Middle Name</label><input id="pi_middle_name" value="{{ $prof->middle_name ?? '' }}"></div>
                            <div class="srp-field"><label>Suffix</label><input id="pi_suffix" value="{{ $prof->suffix ?? '' }}" placeholder="Jr., Sr., III…"></div>
                            <div class="srp-field"><label>Gender</label>
                                <select id="pi_gender">
                                    <option value="">— Select —</option>
                                    @foreach(['Male','Female','Non-binary','Prefer not to say'] as $g)
                                    <option {{ ($prof->gender ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="srp-field"><label>Date of Birth</label><input type="date" id="pi_dob" value="{{ optional($prof)->date_of_birth ? $prof->date_of_birth->format('Y-m-d') : '' }}"></div>
                            <div class="srp-field"><label>Place of Birth</label><input id="pi_pob" value="{{ $prof->place_of_birth ?? '' }}"></div>
                            <div class="srp-field"><label>Civil Status</label>
                                <select id="pi_civil_status">
                                    <option value="">— Select —</option>
                                    @foreach(['Single','Married','Widowed','Separated'] as $cs)
                                    <option {{ ($prof->civil_status ?? '') === $cs ? 'selected' : '' }}>{{ $cs }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="srp-field"><label>Nationality</label><input id="pi_nationality" value="{{ $prof->nationality ?? '' }}"></div>
                            <div class="srp-field"><label>Religion</label><input id="pi_religion" value="{{ $prof->religion ?? '' }}"></div>
                            <div class="srp-field"><label>Mobile Number</label><input id="pi_mobile" value="{{ $prof->mobile_number ?? '' }}"></div>
                            <div class="srp-field"><label>Student Email</label><input type="email" id="pi_email" value="{{ $prof->student_email ?? '' }}"></div>
                        </div>
                        <div class="srp-save-bar">
                            <button class="srp-btn-save" id="srpSaveInfoBtn" onclick="srpSaveInfo()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Save Personal Info
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="srp-card">
                <div class="srp-card-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Address
                </div>
                <div class="srp-card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                        <div>
                            <p style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Present Address</p>
                            <div class="srp-fields">
                                <div class="srp-field"><label>Street / Bldg.</label><input id="pi_pstreet" value="{{ $prof->present_street ?? '' }}"></div>
                                <div class="srp-field"><label>Barangay</label><input id="pi_pbrgy" value="{{ $prof->present_barangay ?? '' }}"></div>
                                <div class="srp-field"><label>Municipality / City</label><input id="pi_pmuni" value="{{ $prof->present_municipality ?? '' }}"></div>
                                <div class="srp-field"><label>Province</label><input id="pi_pprov" value="{{ $prof->present_province ?? '' }}"></div>
                                <div class="srp-field"><label>Region</label><input id="pi_pregion" value="{{ $prof->present_region ?? '' }}"></div>
                                <div class="srp-field"><label>ZIP Code</label><input id="pi_pzip" value="{{ $prof->present_zipcode ?? '' }}"></div>
                            </div>
                        </div>
                        <div>
                            <p style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Permanent Address</p>
                            <div class="srp-fields">
                                <div class="srp-field"><label>Street / Bldg.</label><input id="pi_perstreet" value="{{ $prof->permanent_street ?? '' }}"></div>
                                <div class="srp-field"><label>Barangay</label><input id="pi_perbrgy" value="{{ $prof->permanent_barangay ?? '' }}"></div>
                                <div class="srp-field"><label>Municipality / City</label><input id="pi_permuni" value="{{ $prof->permanent_municipality ?? '' }}"></div>
                                <div class="srp-field"><label>Province</label><input id="pi_perprov" value="{{ $prof->permanent_province ?? '' }}"></div>
                                <div class="srp-field"><label>Region</label><input id="pi_perregion" value="{{ $prof->permanent_region ?? '' }}"></div>
                                <div class="srp-field"><label>ZIP Code</label><input id="pi_perzip" value="{{ $prof->permanent_zipcode ?? '' }}"></div>
                            </div>
                        </div>
                    </div>
                    <div class="srp-save-bar">
                        <button class="srp-btn-save" onclick="srpSaveInfo()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Address
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────── --}}
        {{-- TAB 2 · BACKGROUND                                 --}}
        {{-- ─────────────────────────────────────────────────── --}}
        <div class="srp-panel" id="srp-tab-background">

            {{-- Educational Background --}}
            <div class="srp-card">
                <div class="srp-card-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    Educational Background
                </div>
                <div class="srp-card-body">
                    <div class="srp-fields cols3">
                        <div class="srp-field"><label>LRN (Learner Ref. No.)</label><input id="bg_lrn" value="{{ $prof->lrn ?? '' }}"></div>
                        <div class="srp-field"><label>SHS Track / Strand</label><input id="bg_shs_track" value="{{ $prof->shs_track_strand ?? '' }}"></div>
                        <div class="srp-field"><label>No K-12?</label>
                            <select id="bg_no_k12">
                                <option value="0" {{ empty($prof->no_k12) ? 'selected' : '' }}>No (Has K-12)</option>
                                <option value="1" {{ !empty($prof->no_k12) ? 'selected' : '' }}>Yes (No K-12)</option>
                            </select>
                        </div>
                        <div class="srp-field"><label>Elementary</label><input id="bg_elementary_school" value="{{ $prof->elementary_school ?? '' }}" placeholder="School name and location"></div>
                        <div class="srp-field"><label>High School</label><input id="bg_high_school" value="{{ $prof->high_school ?? '' }}" placeholder="School name and location"></div>
                        <div class="srp-field"><label>School Last Attended</label><input id="bg_school_last_attended" value="{{ $prof->school_last_attended ?? '' }}" placeholder="For transferee; N/A if not applicable"></div>
                        <div class="srp-field"><label>Junior High School</label><input id="bg_junior_school" value="{{ $prof->junior_school ?? '' }}" placeholder="School name, location, or N/A"></div>
                        <div class="srp-field"><label>Senior High School</label><input id="bg_senior_school" value="{{ $prof->senior_school ?? '' }}" placeholder="School name, location, or N/A"></div>
                    </div>
                </div>
            </div>

            {{-- Family Background --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                <div class="srp-card">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Mother's Information
                    </div>
                    <div class="srp-card-body">
                        <div class="srp-fields">
                            <div class="srp-field"><label>First Name</label><input id="bg_mfn" value="{{ $prof->mother_firstname ?? '' }}"></div>
                            <div class="srp-field"><label>Last Name</label><input id="bg_mln" value="{{ $prof->mother_lastname ?? '' }}"></div>
                            <div class="srp-field"><label>Contact</label><input id="bg_mc" value="{{ $prof->mother_contact ?? '' }}"></div>
                            <div class="srp-field"><label>Occupation</label><input id="bg_mo" value="{{ $prof->mother_occupation ?? '' }}"></div>
                        </div>
                    </div>
                </div>
                <div class="srp-card">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Father's Information
                    </div>
                    <div class="srp-card-body">
                        <div class="srp-fields">
                            <div class="srp-field"><label>First Name</label><input id="bg_ffn" value="{{ $prof->father_firstname ?? '' }}"></div>
                            <div class="srp-field"><label>Last Name</label><input id="bg_fln" value="{{ $prof->father_lastname ?? '' }}"></div>
                            <div class="srp-field"><label>Contact</label><input id="bg_fc" value="{{ $prof->father_contact ?? '' }}"></div>
                            <div class="srp-field"><label>Occupation</label><input id="bg_fo" value="{{ $prof->father_occupation ?? '' }}"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="srp-card">
                <div class="srp-card-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 16.58A5 5 0 0 0 18 13h-2a5 5 0 0 0-5 5v2"/><path d="M22 21H2M12 9a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
                    Guardian &amp; Socioeconomic
                </div>
                <div class="srp-card-body">
                    <div class="srp-fields cols3">
                        <div class="srp-field"><label>Guardian First Name</label><input id="bg_gfn" value="{{ $prof->guardian_firstname ?? '' }}"></div>
                        <div class="srp-field"><label>Guardian Last Name</label><input id="bg_gln" value="{{ $prof->guardian_lastname ?? '' }}"></div>
                        <div class="srp-field"><label>Guardian Contact</label><input id="bg_gc" value="{{ $prof->guardian_contact ?? '' }}"></div>
                        <div class="srp-field"><label>Guardian Occupation</label><input id="bg_go" value="{{ $prof->guardian_occupation ?? '' }}"></div>
                        <div class="srp-field"><label>Guardian Address</label><input id="bg_ga" value="{{ $prof->guardian_address ?? '' }}"></div>
                        <div class="srp-field"><label>Parent Marital Status</label><input id="bg_pms" value="{{ $prof->parent_marital_status ?? '' }}"></div>
                        <div class="srp-field"><label>Monthly Family Income</label><input id="bg_mfi" value="{{ $prof->monthly_family_income ?? '' }}"></div>
                        <div class="srp-field"><label>No. of Siblings</label><input type="number" id="bg_sib" value="{{ $prof->number_of_siblings ?? '' }}"></div>
                        <div class="srp-field"><label>Household Members</label><input type="number" id="bg_hh" value="{{ $prof->household_members ?? '' }}"></div>
                        <div class="srp-field"><label>Income Source</label><input id="bg_fis" value="{{ $prof->family_income_source ?? '' }}"></div>
                        <div class="srp-field"><label>Living Situation</label><input id="bg_ls" value="{{ $prof->living_situation ?? '' }}"></div>
                    </div>
                    <div class="srp-save-bar">
                        <button class="srp-btn-save" onclick="srpSaveBg()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Background
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────── --}}
        {{-- TAB 3 · ENROLLED COURSES                          --}}
        {{-- ─────────────────────────────────────────────────── --}}
        <div class="srp-panel" id="srp-tab-subjects">
            @if($enrolledSubjects->isEmpty())
                <div class="srp-card">
                    <div class="srp-card-body" style="text-align:center;padding:50px;color:#94a3b8;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="margin-bottom:12px;opacity:.5;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <div style="font-size:15px;color:#64748b;font-weight:600;margin-bottom:4px;">No enrolled courses found</div>
                        <div style="font-size:13px;">Courses enrolled via the Class List will appear here.</div>
                    </div>
                </div>
            @else
                {{-- Summary strip --}}
                @php
                    $totalSubjects = $enrolledSubjects->count();
                    $totalUnits    = $enrolledSubjects->sum('units');
                    $passedCount   = $subjectGrades->filter(function($g) { return !empty($g->final_average) && (float)$g->final_average >= 75.0; })->count();
                @endphp
                <div class="enr-summary-strip">
                    <div class="enr-summary-card enr-summary-card--subjects">
                        <div class="enr-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/></svg>
                        </div>
                        <div>
                            <div class="enr-summary-value">{{ $totalSubjects }}</div>
                            <div class="enr-summary-label">Courses Enrolled</div>
                        </div>
                    </div>
                    <div class="enr-summary-card enr-summary-card--units">
                        <div class="enr-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-6"/></svg>
                        </div>
                        <div>
                            <div class="enr-summary-value">{{ $totalUnits }}</div>
                            <div class="enr-summary-label">Total Units</div>
                        </div>
                    </div>
                    <div class="enr-summary-card enr-summary-card--passed">
                        <div class="enr-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                        <div>
                            <div class="enr-summary-value">{{ $passedCount }}</div>
                            <div class="enr-summary-label">Courses Passed</div>
                        </div>
                    </div>
                </div>
                @foreach($enrolledBySyTerm as $syTerm => $subjects)
                    @php
                        [$sy2,$sem2] = explode('|||', $syTerm, 2);
                        $isCurrentSem = $currentSyTermKey !== null && $syTerm === $currentSyTermKey;
                    @endphp
                    <div class="srp-card" style="margin-bottom:18px; {{ $isCurrentSem ? 'border:2px solid #15803d; box-shadow:0 0 0 3px rgba(21,128,61,.12);' : '' }}">
                        <div class="srp-card-head" style="background:{{ $isCurrentSem ? '#dcfce7' : '#f0fdf4' }};">
                            AY {{ $sy2 }} &middot; {{ $sem2 }}
                            @if($isCurrentSem)
                                <span class="badge-green" style="margin-left:8px;background:#15803d;color:#fff;">● CURRENT SEMESTER</span>
                            @endif
                            <span class="badge-blue" style="margin-left:8px;">{{ $subjects->count() }} courses</span>
                            <span class="badge-green" style="margin-left:4px;">{{ $subjects->sum('units') }} units</span>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="srp-tbl">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Course</th>
                                        <th>Units</th>
                                        <th>Schedule</th>
                                        <th>Room</th>
                                        <th>Faculty</th>
                                        <th>Midterm</th>
                                        <th>Final</th>
                                        <th>Average</th>
                                        <th>Grade Equivalent</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjects as $sub)
                                    @php $grade = $subjectGrades->get($sub->id); @endphp
                                    <tr>
                                        <td><strong>{{ $sub->code }}</strong></td>
                                        <td>{{ $sub->name }}</td>
                                        <td style="text-align:center;">{{ $sub->units }}</td>
                                        <td>
                                            @if($sub->days)
                                                <span class="sched-pill">{{ $sub->days }}</span>
                                            @endif
                                            @if($sub->time_start && $sub->time_end)
                                                <span style="font-size:12px;color:#374151;">{{ $sub->time_start }}-{{ $sub->time_end }}</span>
                                            @else
                                                <span style="color:#94a3b8;">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $sub->room ?: '-' }}</td>
                                        <td>{{ optional($sub->facultyModel)->name ?: ($sub->faculty_id ? '-' : '-') }}</td>
                                        <td style="text-align:center;">{{ $grade ? $grade->midterm : '-' }}</td>
                                        <td style="text-align:center;">{{ $grade ? $grade->final : '-' }}</td>
                                        <td style="text-align:center;">
                                            @if($grade && $grade->final_average !== null)
                                                @php $avg = (float)$grade->final_average; @endphp
                                                <span class="{{ $avg >= 75 ? 'badge-green' : 'badge-red' }}">{{ number_format($avg,2) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td style="text-align:center;">
                                            @if($grade && $grade->eq_grade !== null)
                                                <span class="{{ $grade->eq_grade <= 3.0 ? 'badge-green' : 'badge-red' }}">{{ number_format($grade->eq_grade,2) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $grade ? $grade->remarks : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- ─────────────────────────────────────────────────── --}}
        {{-- TAB 4 · CURRICULUM                                 --}}
        {{-- ─────────────────────────────────────────────────── --}}
        <div class="srp-panel" id="srp-tab-curriculum">
            @if(!$curriculum)
                <div class="srp-card">
                    <div class="srp-card-body" style="text-align:center;padding:50px;color:#94a3b8;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="margin-bottom:12px;opacity:.5;"><path d="M22 16.74V4.67C22 3.47 21.02 2.58 19.83 2.68H19.77C17.67 2.86 14.48 3.93 12.7 5.05L12.53 5.16C12.24 5.34 11.76 5.34 11.47 5.16L11.22 5.01C9.44 3.9 6.26 2.84 4.16 2.67C2.97 2.57 2 3.47 2 4.66V16.74C2 17.7 2.78 18.6 3.74 18.72L4.03 18.76C6.2 19.05 9.55 20.15 11.47 21.2L11.51 21.22C11.78 21.37 12.21 21.37 12.47 21.22C14.39 20.16 17.75 19.05 19.93 18.76L20.26 18.72C21.22 18.6 22 17.7 22 16.74Z"/></svg>
                        <div style="font-size:15px;color:#64748b;font-weight:600;margin-bottom:4px;">No curriculum found</div>
                        <div style="font-size:13px;">Assign a course/curriculum to this student to see the checklist.</div>
                    </div>
                </div>
            @else
                @php
                    $enrolledIds = $enrolledSubjects->pluck('id')->merge($subjectGrades->keys())->unique();
                    $gradeRecordsByCode = ($gradeRecords ?? collect())->keyBy(function($record) {
                        return strtoupper(trim((string) $record->subject_code));
                    });
                    $totalCurrSubj = $curriculum->curriculumSubjects->count();
                    $completedCurr = $curriculum->curriculumSubjects->filter(function($cs) use ($subjectGrades, $gradeRecordsByCode) {
                        $subjectCode = strtoupper(trim((string) optional($cs->subject)->code));
                        $grade = $subjectGrades->get($cs->subject_id);
                        $record = $subjectCode !== '' ? $gradeRecordsByCode->get($subjectCode) : null;

                        return ($record && ($record->inc || $record->final_grade !== null))
                            || ($grade && ($grade->final_average !== null || $grade->final !== null || $grade->midterm !== null));
                    })->count();
                @endphp
                <div class="srp-card" style="margin-bottom:18px;">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 16.74V4.67C22 3.47 21.02 2.58 19.83 2.68H19.77C17.67 2.86 14.48 3.93 12.7 5.05L12.53 5.16C12.24 5.34 11.76 5.34 11.47 5.16L11.22 5.01C9.44 3.9 6.26 2.84 4.16 2.67C2.97 2.57 2 3.47 2 4.66V16.74C2 17.7 2.78 18.6 3.74 18.72L4.03 18.76C6.2 19.05 9.55 20.15 11.47 21.2L11.51 21.22C11.78 21.37 12.21 21.37 12.47 21.22C14.39 20.16 17.75 19.05 19.93 18.76L20.26 18.72C21.22 18.6 22 17.7 22 16.74Z"/></svg>
                        {{ $curriculum->title }}
                        <span class="badge-blue" style="margin-left:8px;">{{ $curriculum->curriculum_year_code }}</span>
                        @if($curriculum->is_active) <span class="badge-green">Active</span> @endif
                        <span style="margin-left:auto;font-size:12px;color:#64748b;font-weight:400;">
                            {{ $completedCurr }} / {{ $totalCurrSubj }} subjects taken
                        </span>
                    </div>
                    <div class="srp-card-body" style="padding:0;">
                        {{-- Progress bar --}}
                        @if($totalCurrSubj > 0)
                        <div style="padding:12px 18px;border-bottom:1px solid #f1f5f9;">
                            <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-bottom:5px;">
                                <span>Curriculum Progress</span>
                                <span>{{ round($completedCurr/$totalCurrSubj*100) }}%</span>
                            </div>
                            <div style="background:#e2e8f0;border-radius:6px;height:8px;overflow:hidden;">
                                <div style="background:linear-gradient(90deg,#004d27,#1a9e54);height:100%;border-radius:6px;width:{{ round($completedCurr/$totalCurrSubj*100) }}%;transition:width .5s;"></div>
                            </div>
                        </div>
                        @endif

                        <div style="overflow-x:auto;">
                            <table class="srp-tbl">
                                <thead>
                                    <tr>
                                        <th style="width:70px;text-align:center;">Status</th>
                                        <th>Subject Code</th>
                                        <th>Course</th>
                                        <th style="text-align:center;">Units</th>
                                        <th>Year</th>
                                        <th>Semester</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($curriculumByYearSem as $yrSem => $csItems)
                                        @php [$yr,$sem] = explode('|||', $yrSem, 2); @endphp
                                        <tr class="curr-group-head">
                                            <td colspan="6">{{ $yr ?: 'N/A' }} · {{ $sem ?: 'N/A' }}</td>
                                        </tr>
                                        @foreach($csItems as $cs)
                                        @php
                                            $subjectCode = strtoupper(trim((string) optional($cs->subject)->code));
                                            $grade = $subjectGrades->get($cs->subject_id);
                                            $gradeRecord = $subjectCode !== '' ? $gradeRecordsByCode->get($subjectCode) : null;
                                            $gradeValue = null;
                                            $isInc = false;

                                            if ($gradeRecord && ($gradeRecord->inc || $gradeRecord->final_grade !== null)) {
                                                $isInc = (bool) $gradeRecord->inc;
                                                $gradeValue = $isInc ? 'INC' : $gradeRecord->final_grade;
                                            } elseif ($grade && $grade->final_average !== null) {
                                                $gradeValue = number_format((float) $grade->final_average, 2);
                                            } elseif ($grade && $grade->final !== null) {
                                                $gradeValue = number_format((float) $grade->final, 2);
                                            } elseif ($grade && $grade->midterm !== null) {
                                                $gradeValue = number_format((float) $grade->midterm, 2);
                                            }

                                            $taken = $gradeValue !== null;
                                        @endphp
                                        <tr>
                                            <td style="text-align:center;">
                                                <div class="curr-check {{ $taken ? 'done' : '' }}">
                                                    @if($taken)
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                                    @endif
                                                </div>
                                            </td>
                                            <td><strong>{{ optional($cs->subject)->code ?? '—' }}</strong></td>
                                            <td>{{ optional($cs->subject)->name ?? '—' }}</td>
                                            <td style="text-align:center;">{{ $cs->credited_units ?? optional($cs->subject)->units ?? '—' }}</td>
                                            <td>{{ optional($cs->yearBlock)->label ?? optional($cs->yearBlock)->block_name ?? '�' }}</td>
                                            <td>{{ optional($cs->semester)->name ?? '�' }}</td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                    @if($curriculumByYearSem->isEmpty())
                                        <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:30px;">No subjects in this curriculum.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ─────────────────────────────────────────────────── --}}
        {{-- TAB 5 · DOCUMENTS                                  --}}
        {{-- ─────────────────────────────────────────────────── --}}
        <div class="srp-panel" id="srp-tab-scholarships">
            <div class="srp-card">
                <div class="srp-card-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3L3 7.5L12 12L21 7.5L12 3Z"/><path d="M5 10v5.5C5 17.43 8.13 19 12 19s7-1.57 7-3.5V10"/></svg>
                    Tag Student Scholarship
                </div>
                <div class="srp-card-body">
                    @if(($scholarshipPrograms ?? collect())->isEmpty())
                        <div style="text-align:center;color:#94a3b8;padding:28px;">No scholarship programs are configured yet.</div>
                    @else
                        <form method="POST" action="{{ route('registrar.registrar-menu.student-mgmt.student-records.scholarships.save', $student->id) }}">
                            @csrf
                            <div class="srp-sch-form-section">
                                <div class="srp-sch-section-title">Scholarship and Term</div>
                                <div class="srp-sch-grid">
                                    <div class="srp-field wide">
                                        <label>Scholarship Program</label>
                                        <select name="scholarship_program_id" required>
                                            @foreach($scholarshipPrograms as $program)
                                                <option value="{{ $program->id }}">{{ $program->name }} - {{ $program->category }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="srp-field"><label>Academic Year</label><input name="school_year" value="{{ $student->school_year }}"></div>
                                    <div class="srp-field"><label>Semester</label><input name="semester" value="{{ $student->semester }}"></div>
                                </div>
                            </div>

                            <div class="srp-sch-form-section">
                                <div class="srp-sch-section-title">Application Workflow</div>
                                <div class="srp-sch-grid">
                                    <div class="srp-field"><label>Application Status</label><select name="application_status"><option>Tagged</option><option>Applied</option><option>For Evaluation</option><option>Complete</option></select></div>
                                    <div class="srp-field"><label>Evaluation Status</label><select name="evaluation_status"><option value="">Not set</option><option>Pending</option><option>Qualified</option><option>Not Qualified</option></select></div>
                                    <div class="srp-field"><label>Approval Status</label><select name="approval_status"><option value="">Not set</option><option>Pending</option><option>Approved</option><option>Disapproved</option></select></div>
                                    <div class="srp-field"><label>Award Status</label><select name="award_status"><option>Active</option><option>For Renewal</option><option>Renewed</option><option>Suspended</option><option>Ended</option></select></div>
                                    <div class="srp-field"><label>Renewal Status</label><select name="renewal_status"><option value="">Not set</option><option>Not Due</option><option>For Renewal</option><option>Renewed</option><option>Not Renewed</option></select></div>
                                    <div class="srp-field"><label>Monitoring Status</label><select name="monitoring_status"><option value="">Not set</option><option>Compliant</option><option>For Review</option><option>Below Maintaining Grade</option></select></div>
                                </div>
                            </div>

                            <div class="srp-sch-form-section">
                                <div class="srp-sch-section-title">Financial and Academic Details</div>
                                <div class="srp-sch-grid">
                                    <div class="srp-field"><label>Financial Posting</label><select name="financial_posting_status"><option>Pending</option><option>Posted</option><option>For Adjustment</option><option>Cancelled</option></select></div>
                                    <div class="srp-field"><label>Posted Amount</label><input type="number" step="0.01" min="0" name="posted_amount"></div>
                                    <div class="srp-field"><label>Discount Percent</label><input type="number" step="0.01" min="0" max="100" name="discount_percent"></div>
                                    <div class="srp-field"><label>Current GWA</label><input type="number" step="0.01" min="1" max="5" name="current_gwa" value="{{ $cwa }}"></div>
                                    <div class="srp-field"><label>Application Date</label><input type="date" name="application_date"></div>
                                    <div class="srp-field"><label>Approval Date</label><input type="date" name="approval_date"></div>
                                    <div class="srp-field"><label>Renewal Due Date</label><input type="date" name="renewal_due_date"></div>
                                    <div class="srp-field full"><label>Remarks</label><textarea name="remarks" rows="3"></textarea></div>
                                </div>
                            </div>
                            <div class="srp-save-bar"><button class="srp-btn-save" type="submit">Save Scholarship Tag</button></div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="srp-card">
                <div class="srp-card-head">
                    Current Scholarship Tags
                    <a href="{{ route('registrar.registrar-menu.scholarships.report') }}" class="srp-btn-primary" style="margin-left:auto;padding:6px 12px;font-size:12px;text-decoration:none;">View Report</a>
                </div>
                @if(($studentScholarships ?? collect())->isEmpty())
                    <div class="srp-card-body" style="text-align:center;color:#94a3b8;padding:30px;">No scholarship tags for this student yet.</div>
                @else
                    <div class="srp-sch-table-wrap">
                        <table class="srp-tbl srp-sch-table">
                            <thead>
                                <tr>
                                    <th>Scholarship</th>
                                    <th>Term</th>
                                    <th>Process Status</th>
                                    <th>Financial</th>
                                    <th class="srp-sch-edit-cell">Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($studentScholarships as $tag)
                                    <tr>
                                        <td>
                                            <strong>{{ optional($tag->program)->name ?: 'Scholarship #' . $tag->scholarship_program_id }}</strong><br>
                                            <small>{{ optional($tag->program)->category }}</small>
                                        </td>
                                        <td>AY {{ $tag->school_year ?: 'N/A' }}<br>{{ $tag->semester ?: 'N/A' }}</td>
                                        <td>
                                            <span class="badge-blue">{{ $tag->award_status }}</span><br>
                                            <small>Approval: {{ $tag->approval_status ?: 'N/A' }} | Monitoring: {{ $tag->monitoring_status ?: 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge-gray">{{ $tag->financial_posting_status }}</span><br>
                                            <small>{{ $tag->posted_amount ? number_format((float) $tag->posted_amount, 2) : 'No amount posted' }}</small>
                                        </td>
                                        <td class="srp-sch-edit-cell">
                                            <details>
                                                <summary style="cursor:pointer;color:#004d27;font-weight:700;">Edit tag</summary>
                                                <form class="srp-sch-edit-panel" method="POST" action="{{ route('registrar.registrar-menu.student-mgmt.student-records.scholarships.save', $student->id) }}">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $tag->id }}">
                                                    <input type="hidden" name="scholarship_program_id" value="{{ $tag->scholarship_program_id }}">
                                                    <input type="hidden" name="school_year" value="{{ $tag->school_year }}">
                                                    <input type="hidden" name="semester" value="{{ $tag->semester }}">
                                                    <input type="hidden" name="application_status" value="{{ $tag->application_status }}">
                                                    <input type="hidden" name="evaluation_status" value="{{ $tag->evaluation_status }}">
                                                    <input type="hidden" name="approval_status" value="{{ $tag->approval_status }}">
                                                    <input type="hidden" name="renewal_status" value="{{ $tag->renewal_status }}">
                                                    <input type="hidden" name="posted_amount" value="{{ $tag->posted_amount }}">
                                                    <input type="hidden" name="discount_percent" value="{{ $tag->discount_percent }}">
                                                    <input type="hidden" name="current_gwa" value="{{ $tag->current_gwa }}">
                                                    <input type="hidden" name="application_date" value="{{ optional($tag->application_date)->format('Y-m-d') }}">
                                                    <input type="hidden" name="approval_date" value="{{ optional($tag->approval_date)->format('Y-m-d') }}">
                                                    <input type="hidden" name="renewal_due_date" value="{{ optional($tag->renewal_due_date)->format('Y-m-d') }}">
                                                    <input type="hidden" name="remarks" value="{{ $tag->remarks }}">
                                                    <div class="srp-field"><label>Award Status</label><select name="award_status">@foreach(['Active','For Renewal','Renewed','Suspended','Ended'] as $item)<option {{ $tag->award_status === $item ? 'selected' : '' }}>{{ $item }}</option>@endforeach</select></div>
                                                    <div class="srp-field"><label>Monitoring</label><select name="monitoring_status"><option value="">Not set</option>@foreach(['Compliant','For Review','Below Maintaining Grade'] as $item)<option {{ $tag->monitoring_status === $item ? 'selected' : '' }}>{{ $item }}</option>@endforeach</select></div>
                                                    <div class="srp-field"><label>Financial Posting</label><select name="financial_posting_status">@foreach(['Pending','Posted','For Adjustment','Cancelled'] as $item)<option {{ $tag->financial_posting_status === $item ? 'selected' : '' }}>{{ $item }}</option>@endforeach</select></div>
                                                    <div class="srp-sch-edit-actions"><button type="submit" class="srp-btn-save" style="padding:6px 12px;">Update</button></div>
                                                </form>
                                                <form method="POST" action="{{ route('registrar.registrar-menu.student-mgmt.student-records.scholarships.delete', [$student->id, $tag->id]) }}" onsubmit="return confirm('Remove this scholarship tag?');" style="margin-top:8px;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="med-btn-cancel" type="submit" style="background:#fee2e2;color:#991b1b;">Remove</button>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="srp-panel" id="srp-tab-scholastic-comments">
            <div class="srp-card">
                <div class="srp-card-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                    Scholastic Comments
                    <button type="button" class="srp-btn-primary" style="margin-left:auto;padding:6px 12px;font-size:12px;" onclick="openScholasticCommentModal()">Add Comment</button>
                </div>
                @if(($scholasticComments ?? collect())->isEmpty())
                    <div class="srp-card-body" style="text-align:center;color:#94a3b8;padding:34px;">No scholastic comments recorded yet.</div>
                @else
                    <div style="overflow-x:auto;">
                        <table class="srp-tbl">
                            <thead>
                                <tr>
                                    <th>Academic Year</th>
                                    <th>Semester</th>
                                    <th>Comment / Remark</th>
                                    <th>Date Issued</th>
                                    <th>Issued By</th>
                                    <th style="width:72px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scholasticComments as $comment)
                                <tr>
                                    <td>{{ $comment->school_year ?: '—' }}</td>
                                    <td>{{ $comment->semester ?: '—' }}</td>
                                    <td style="white-space:pre-wrap;">{{ $comment->comment ?: '—' }}</td>
                                    <td>{{ $comment->date_issued ?: '—' }}</td>
                                    <td>{{ $comment->issued_by ?: '—' }}</td>
                                    <td style="text-align:center;">
                                        <button type="button" onclick='editScholasticComment(@json($comment))' style="background:none;border:none;cursor:pointer;color:#2563eb;font-size:11px;padding:2px 6px;" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="srp-panel" id="srp-tab-documents">
            <div class="srp-card">
                <div class="srp-card-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Submitted Documents &amp; Requirements
                    <button type="button" class="docreq-add-btn" onclick="srpToggleDocumentRequirementForm()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        Add Requirement
                    </button>
                    <span class="docreq-count">
                        {{ $requirements->where('is_submitted', true)->count() }} / {{ $requirements->count() }} submitted
                    </span>
                </div>
                <div class="docreq-add-panel" id="docRequirementAddPanel">
                    <form method="POST" action="{{ route('registrar.registrar-menu.student-mgmt.student-records.requirements.store', ['student' => $student->id]) }}" class="docreq-add-form">
                        @csrf
                        <div class="docreq-field">
                            <label>Requirement Name</label>
                            <input type="text" name="requirement_name" placeholder="e.g. Good Moral Certificate" required>
                        </div>
                        <div class="docreq-field">
                            <label>Type</label>
                            <select name="requirement_type">
                                <option value="Document">Document</option>
                                <option value="Medical">Medical</option>
                                <option value="Clearance">Clearance</option>
                            </select>
                        </div>
                        <div class="docreq-field">
                            <label>Remarks</label>
                            <input type="text" name="remarks" placeholder="Optional note for this student">
                        </div>
                        <button type="submit" class="pf-btn-new docreq-submit">Save Requirement</button>
                    </form>
                </div>
                @if($requirements->isEmpty())
                    <div class="srp-card-body" style="text-align:center;padding:40px;color:#94a3b8;">
                        No document requirements tracked for this student.
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table class="srp-tbl">
                            <thead>
                                <tr>
                                    <th>Requirement</th>
                                    <th>Type</th>
                                    <th style="text-align:center;">Status</th>
                                    <th>Date Submitted</th>
                                    <th>Verified By</th>
                                    <th>File</th>
                                    <th>Upload</th>
                                    <th>Remarks</th>
                                    <th style="text-align:center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requirements as $req)
                                @php
                                    $def      = optional(optional($req->requirementPolicy)->definition);
                                    $reqName  = $def->requirement_name ?? 'Requirement';
                                    $reqType  = optional($def->type)->name ?? '';
                                    $verifier = optional($req->verifier)->name ?? ($req->verified_by_user_id ? 'User #'.$req->verified_by_user_id : '—');
                                @endphp
                                <tr>
                                    <td><strong>{{ $reqName }}</strong></td>
                                    <td>{{ $reqType ?: '—' }}</td>
                                    <td style="text-align:center;">
                                        @if($req->is_submitted ?? false)
                                            <span class="badge-green">Submitted</span>
                                        @else
                                            <span class="badge-yellow">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $req->date_verified ? $req->date_verified->format('M j, Y') : '—' }}</td>
                                    <td>{{ $verifier }}</td>
                                    <td>
                                        @if(!empty($req->uploaded_path))
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($req->uploaded_path) }}" target="_blank" style="color:#2563eb;font-weight:700;">
                                                {{ $req->uploaded_original_name ?: 'View file' }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('registrar.registrar-menu.student-mgmt.student-records.requirements.upload', ['student' => $student->id, 'requirement' => $req->id]) }}" enctype="multipart/form-data" style="display:flex;gap:6px;align-items:center;min-width:260px;">
                                            @csrf
                                            <input type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required style="font-size:11px;max-width:155px;">
                                            <input type="hidden" name="remarks" value="{{ $req->remarks }}">
                                            <button type="submit" class="pf-btn-new" style="padding:5px 9px;font-size:11px;">Upload</button>
                                        </form>
                                    </td>
                                    <td>{{ $req->remarks ?? '—' }}</td>
                                    <td style="text-align:center;white-space:nowrap;">
                                        <button type="button"
                                            onclick="srpEditRequirement({{ $req->id }}, @json($reqName), @json($reqType), @json($req->remarks))"
                                            style="background:none;border:none;cursor:pointer;color:#2563eb;font-size:11px;padding:2px 6px;" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('registrar.registrar-menu.student-mgmt.student-records.requirements.delete', ['student' => $student->id, 'requirement' => $req->id]) }}" style="display:inline;" onsubmit="return confirm('Delete this document requirement? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:11px;padding:2px 6px;" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────── --}}
        {{-- TAB 6 · CERTIFICATES                               --}}
        {{-- ─────────────────────────────────────────────────── --}}
        <div class="srp-panel" id="srp-tab-certificates">

            {{-- ── Primary Print Documents ── --}}
            <div class="srp-card" style="margin-bottom:18px;">
                <div class="srp-card-head" style="justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Print Documents
                        @if($isGraduated)
                            <span class="badge-green" style="margin-left:8px;">Graduate</span>
                        @endif
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:3px;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#334155;cursor:{{ $honorableDismissalRecord ? 'default' : 'pointer' }};text-transform:none;letter-spacing:normal;">
                            <input type="checkbox"
                                   id="srpHdTagCheckbox"
                                   style="width:15px;height:15px;accent-color:#0f7b43;"
                                   {{ $honorableDismissalRecord ? 'checked disabled' : '' }}
                                   onchange="srpTagHonorableDismissal(this)">
                            Tag for Honorable Dismissal
                            @if($honorableDismissalRecord)
                                <span class="{{ $honorableDismissalRecord->status === 'issued' ? 'badge-green' : 'badge-red' }}">
                                    {{ $honorableDismissalRecord->status === 'issued' ? 'Issued' : 'Pending' }}
                                </span>
                            @endif
                        </label>
                        @if($honorableDismissalRecord && $honorableDismissalRecord->copy_for)
                            <span style="font-size:11px;color:#64748b;font-weight:500;text-transform:none;letter-spacing:normal;">
                                HD Copy For: {{ $honorableDismissalRecord->copy_for }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="srp-card-body">
                    <div style="display:flex;gap:12px;flex-wrap:wrap;">

                        {{-- COR --}}
                        <a href="{{ route('registrar.registrar-menu.forms.cor.certificate-of-registration') }}?student_id={{ $student->id }}"
                           target="_blank"
                           style="display:flex;flex-direction:column;align-items:center;gap:8px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1.5px solid #93c5fd;border-radius:12px;padding:18px 22px;text-decoration:none;color:#1e40af;min-width:145px;transition:box-shadow .15s;"
                           onmouseover="this.style.boxShadow='0 4px 16px rgba(37,99,235,.2)'" onmouseout="this.style.boxShadow=''">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <div style="font-size:12.5px;font-weight:700;text-align:center;line-height:1.3;">Certificate of<br>Registration</div>
                            <div style="font-size:10.5px;color:#3b82f6;font-weight:600;">COR</div>
                        </a>

                        {{-- Clearance 2 --}}
                        <a href="{{ route('registrar.registrar-menu.forms.clearance-2.show', ['student' => $student->id]) }}"
                           target="_blank"
                           style="display:flex;flex-direction:column;align-items:center;gap:8px;background:linear-gradient(135deg,#faf5ff,#f3e8ff);border:1.5px solid #c084fc;border-radius:12px;padding:18px 22px;text-decoration:none;color:#6b21a8;min-width:145px;transition:box-shadow .15s;"
                           onmouseover="this.style.boxShadow='0 4px 16px rgba(107,33,168,.2)'" onmouseout="this.style.boxShadow=''">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            <div style="font-size:12.5px;font-weight:700;text-align:center;line-height:1.3;">Clearance<br>2</div>
                            <div style="font-size:10.5px;color:#7e22ce;font-weight:600;">CLEARANCE</div>
                        </a>

                        {{-- TOR --}}
                        <a href="{{ route('registrar.registrar-menu.student-mgmt.student-records.print.tor', $student->id) }}"
                           target="_blank"
                           style="display:flex;flex-direction:column;align-items:center;gap:8px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;border-radius:12px;padding:18px 22px;text-decoration:none;color:#166534;min-width:145px;transition:box-shadow .15s;"
                           onmouseover="this.style.boxShadow='0 4px 16px rgba(22,101,52,.2)'" onmouseout="this.style.boxShadow=''">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0-1.756 1.077M12 12h4M12 16h4M8 12h.01M8 16h.01"/></svg>
                            <div style="font-size:12.5px;font-weight:700;text-align:center;line-height:1.3;">Transcript<br>of Records</div>
                            <div style="font-size:10.5px;color:#22c55e;font-weight:600;">TOR</div>
                        </a>

                        {{-- Diploma (graduates only) --}}
                        @if($isGraduated)
                        <a href="{{ route('registrar.registrar-menu.forms.diploma') }}?student_id={{ $student->id }}"
                           target="_blank"
                           style="display:flex;flex-direction:column;align-items:center;gap:8px;background:linear-gradient(135deg,#fefce8,#fef9c3);border:1.5px solid #fde047;border-radius:12px;padding:18px 22px;text-decoration:none;color:#713f12;min-width:145px;transition:box-shadow .15s;"
                           onmouseover="this.style.boxShadow='0 4px 16px rgba(161,98,7,.2)'" onmouseout="this.style.boxShadow=''">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            <div style="font-size:12.5px;font-weight:700;text-align:center;line-height:1.3;">Diploma</div>
                            <div style="font-size:10.5px;color:#ca8a04;font-weight:600;">
                                {{ $graduateTagging && $graduateTagging->date_graduated ? $graduateTagging->date_graduated->format('Y') : 'Graduate' }}
                            </div>
                        </a>
                        @else
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;background:#f8fafc;border:1.5px dashed #cbd5e1;border-radius:12px;padding:18px 22px;color:#94a3b8;min-width:145px;cursor:not-allowed;"
                             title="Only available for graduated students">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            <div style="font-size:12.5px;font-weight:600;text-align:center;line-height:1.3;">Diploma</div>
                            <div style="font-size:10px;text-align:center;">Graduate status required</div>
                        </div>
                        @endif

                    </div>
                    @if($isGraduated && $graduateTagging)
                    <div style="margin-top:12px;font-size:12px;color:#64748b;display:flex;gap:16px;flex-wrap:wrap;">
                        @if($graduateTagging->date_graduated)
                            <span>Date Graduated: <strong>{{ $graduateTagging->date_graduated->format('F j, Y') }}</strong></span>
                        @endif
                        @if($graduateTagging->so_number)
                            <span>BOR No.: <strong>{{ $graduateTagging->so_number }}</strong></span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Other Certificates ── --}}
            <div class="srp-card">
                <div class="srp-card-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
                    Generate Certificates &amp; Official Documents
                </div>
                <div class="srp-card-body">
                    <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Click any document to generate it for this student.</p>
                    <div class="srp-cert-grid">
                        @php
                            $certs = [
                                ['Certificate of Registration (COR)', route('registrar.registrar-menu.forms.cor.certificate-of-registration') . '?student_id=' . $student->id,'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0-1.756 1.077'],
                                ['Clearance 2', route('registrar.registrar-menu.forms.clearance-2.show', ['student' => $student->id]),'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
                                ['Copy of Grades (COG)', route('registrar.registrar-menu.forms.cog.copy-of-grades'),'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M12 12h4M12 16h4M8 12h.01M8 16h.01'],
                                ['Official Grade Report', route('registrar.registrar-menu.forms.official-grade-report'),'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8'],
                                ['Certificate of GWA', route('registrar.registrar-menu.forms.certificates.certificate-gwa.show', ['student' => $student->id]),'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'],
                                ['Dean\'s Honors', route('registrar.registrar-menu.forms.certificates.deans-honors.show', ['student' => $student->id]),'M12 2l2.4 4.86 5.36.78-3.88 3.78.92 5.34L12 14.94 7.2 17.46l.92-5.34-3.88-3.78 5.36-.78L12 2z'],
                                ['President\'s Honors', route('registrar.registrar-menu.forms.certificates.presidents-honors.show', ['student' => $student->id]),'M12 2l2.4 4.86 5.36.78-3.88 3.78.92 5.34L12 14.94 7.2 17.46l.92-5.34-3.88-3.78 5.36-.78L12 2z'],
                                ['Form 8C-2 (Graduation)', route('registrar.registrar-menu.forms.certificates.certificate-graduation-8c2.show', ['student' => $student->id]),'M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3'],
                                ['Form 8D-2 (Honor)', route('registrar.registrar-menu.forms.certificates.certificate-honor-8d2.show', ['student' => $student->id]),'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z'],
                                ['Diploma', route('registrar.registrar-menu.forms.diploma') . '?student_id=' . $student->id,'M22 10v6M2 10l10-5 10 5-10 5z M6 12v5c3 3 9 3 12 0v-5'],
                                ['Honorable Dismissal', route('registrar.registrar-menu.forms.honorable-dismissal.show', ['student' => $student->id]),'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
                                ['Graduation Clearance', route('registrar.registrar-menu.forms.graduation-clearance.show', ['student' => $student->id]),'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'],
                                ['Leave of Absence', route('registrar.registrar-menu.forms.application-leave-of-absence-enrolled.show', ['student' => $student->id]),'M8 2v3M16 2v3M3.5 9.09H20.5M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5z'],
                                ['Cross-Enroll Permit', route('registrar.registrar-menu.forms.permission-cross-enroll'),'M8 7H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3m-1 4-3 3-3-3m3-3v11'],
                                ['Request Form F137A', route('registrar.registrar-menu.forms.request-form-f-137a.show', ['student' => $student->id]),'M4 4h16v16H4zM4 9h16M9 9v11'],
                            ];
                        @endphp
                        @foreach($certs as [$certName,$certUrl,$certIcon])
                        <a href="{{ $certUrl }}" class="srp-cert-card">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="{{ $certIcon }}"/>
                                </svg>
                            </div>
                            <div class="srp-cert-name">{{ $certName }}</div>
                        </a>
                        @endforeach

                        <button type="button" class="srp-cert-card srp-cert-card-btn" onclick="srpOpenReportOfGrades({{ $student->id }})">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M9 17v-6M12 17v-3M15 17v-9M4 4h16v16H4z"/>
                                </svg>
                            </div>
                            <div class="srp-cert-name">Report of Grades (CWA)</div>
                        </button>
                    </div>
                    <p style="font-size:11.5px;color:#94a3b8;margin-top:10px;">
                        Report of Grades (CWA) becomes available once all of the student's current-semester subjects have posted final grades.
                    </p>
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────── --}}
        {{-- TAB 7 · BAD RECORDS                                --}}
        {{-- ─────────────────────────────────────────────────── --}}
        <div class="srp-panel" id="srp-tab-conduct">

            @if($openBad > 0)
            <div style="background:#fff1f2;border:1.5px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:13px;color:#991b1b;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <strong>{{ $openBad }} open issue{{ $openBad > 1 ? 's' : '' }}</strong> — This student has unresolved discipline or deficiency records.
            </div>
            @endif

            {{-- Discipline Records --}}
            <div class="srp-card">
                <div class="srp-card-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Discipline Records
                    @if($disciplineStudent)
                        <span class="badge-blue" style="margin-left:8px;">
                            {{ optional($disciplineStudent->studentType)->name ?? 'Student' }}
                        </span>
                    @endif
                    <span style="margin-left:auto;font-size:12px;font-weight:400;color:#64748b;">
                        {{ $disciplineRecords->count() }} record(s)
                    </span>
                </div>
                @if($disciplineRecords->isEmpty())
                    <div class="srp-card-body" style="text-align:center;color:#94a3b8;padding:30px;">
                        No discipline records on file.
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table class="srp-tbl">
                            <thead>
                                <tr>
                                    <th style="width:10px;"></th>
                                    <th>Case Type</th>
                                    <th>Action Taken</th>
                                    <th>Date</th>
                                    <th style="text-align:center;">Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($disciplineRecords as $dr)
                                @php
                                    $caseLabel = optional($dr->caseType)->name ?? '—';
                                    $isResolved = strtolower($dr->status ?? '') === 'resolved';
                                    $isMajor    = str_contains(strtolower($caseLabel), 'major');
                                    $dotColor   = $isResolved ? '#16a34a' : ($isMajor ? '#dc2626' : '#f59e0b');
                                @endphp
                                <tr>
                                    <td><span class="conduct-dot" style="background:{{ $dotColor }};"></span></td>
                                    <td>{{ $caseLabel }}</td>
                                    <td>{{ optional($dr->actionType)->name ?? '—' }}</td>
                                    <td>{{ $dr->incident_date ? \Carbon\Carbon::parse($dr->incident_date)->format('M j, Y') : ($dr->created_at ? $dr->created_at->format('M j, Y') : '—') }}</td>
                                    <td style="text-align:center;">
                                        @if($isResolved)
                                            <span class="badge-green">Resolved</span>
                                        @else
                                            <span class="badge-red">Open</span>
                                        @endif
                                    </td>
                                    <td>{{ $dr->remarks ?? $dr->notes ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Deficiency Records --}}
            <div class="srp-card">
                <div class="srp-card-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Deficiency Records
                    <span style="margin-left:auto;font-size:12px;font-weight:400;color:#64748b;">
                        {{ $deficiencies->count() }} record(s)
                    </span>
                </div>
                @if($deficiencies->isEmpty())
                    <div class="srp-card-body" style="text-align:center;color:#94a3b8;padding:30px;">
                        No deficiency records on file.
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table class="srp-tbl">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Remarks</th>
                                    <th>Date Filed</th>
                                    <th>Submission Deadline</th>
                                    <th>Compliance Date</th>
                                    <th style="text-align:center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deficiencies as $def)
                                <tr style="{{ !$def->is_completed ? 'background:#fffbeb;' : '' }}">
                                    <td>{{ $def->department ?: '—' }}</td>
                                    <td>{{ $def->remarks ?: '—' }}</td>
                                    <td>{{ $def->date_today ? $def->date_today->format('M j, Y') : '—' }}</td>
                                    <td>{{ $def->submission_date ? $def->submission_date->format('M j, Y') : '—' }}</td>
                                    <td>{{ $def->compliance_date ? $def->compliance_date->format('M j, Y') : '—' }}</td>
                                    <td style="text-align:center;">
                                        @if($def->is_completed)
                                            <span class="badge-green">Cleared</span>
                                        @else
                                            <span class="badge-yellow">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        {{-- ─────────────────────────────────────────────────── --}}
        {{-- TAB 8 · MEDICAL                                     --}}
        {{-- ─────────────────────────────────────────────────── --}}
        <div class="srp-panel" id="srp-tab-medical">
        @php
            $med = $medicalRecord;
            $bmi = null;
            $bmiCategory = null;
            if ($med && $med->height_cm > 0 && $med->weight_kg > 0) {
                $hm  = $med->height_cm / 100;
                $bmi = round($med->weight_kg / ($hm * $hm), 1);
                if ($bmi < 18.5)       { $bmiCategory = 'Underweight'; }
                elseif ($bmi < 25.0)   { $bmiCategory = 'Normal'; }
                elseif ($bmi < 30.0)   { $bmiCategory = 'Overweight'; }
                else                   { $bmiCategory = 'Obese'; }
            }
            $bmiColor = ['Underweight'=>'#f59e0b','Normal'=>'#16a34a','Overweight'=>'#f97316','Obese'=>'#dc2626'];
        @endphp

        {{-- Action bar --}}
        <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
            <button class="srp-btn-primary" onclick="openMedModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                {{ $med ? 'Edit Medical Record' : 'Add Medical Record' }}
            </button>
        </div>

        @if(!$med)
        <div class="srp-card" style="text-align:center;padding:48px 24px;color:#94a3b8;">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:10px;"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <div style="font-size:15px;font-weight:600;color:#64748b;margin-bottom:4px;">No medical record on file</div>
            <div style="font-size:13px;">Click <strong>Add Medical Record</strong> above to create one.</div>
        </div>
        @else

        {{-- ── Vitals strip ── --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:18px;">
            @foreach([
                ['Blood Type',  $med->blood_type ?: '—',  '#eff6ff','#2563eb'],
                ['Height',      $med->height_cm  ? $med->height_cm.' cm'   : '—', '#f0fdf4','#16a34a'],
                ['Weight',      $med->weight_kg  ? $med->weight_kg.' kg'   : '—', '#fef9c3','#ca8a04'],
                ['BMI',         $bmi             ? $bmi.' ('.$bmiCategory.')' : '—', '#fdf4ff','#9333ea'],
            ] as [$lbl,$val,$bg,$c])
            <div class="srp-stat" style="background:{{ $bg }};min-width:0;">
                <div style="font-size:18px;font-weight:700;color:{{ $c }};">{{ $val }}</div>
                <div style="font-size:11px;color:#64748b;margin-top:2px;">{{ $lbl }}</div>
            </div>
            @endforeach
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            {{-- ── Left column ── --}}
            <div>
                <div class="srp-card">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4.93 4.93l4.24 4.24M14.83 9.17l4.24-4.24M14.83 14.83l4.24 4.24M9.17 14.83l-4.24 4.24M12 2v4M12 18v4M2 12h4M18 12h4"/></svg>
                        Health Profile
                    </div>
                    <div class="srp-card-body">
                        <table class="srp-info-tbl">
                            <tr><td>Allergies</td><td>{{ $med->allergies ?: '—' }}</td></tr>
                            <tr><td>Medical Conditions</td><td>{{ $med->medical_conditions ?: '—' }}</td></tr>
                            <tr><td>Current Medications</td><td>{{ $med->current_medications ?: '—' }}</td></tr>
                            <tr><td>Past Illnesses</td><td>{{ $med->past_illnesses ?: '—' }}</td></tr>
                            <tr><td>Mental Health Notes</td><td>{{ $med->mental_health_notes ?: '—' }}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="srp-card" style="margin-top:18px;">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                        Sensory & Dental
                    </div>
                    <div class="srp-card-body">
                        <table class="srp-info-tbl">
                            <tr><td>Vision (Right/OD)</td><td>{{ $med->vision_od ?: '—' }}</td></tr>
                            <tr><td>Vision (Left/OS)</td><td>{{ $med->vision_os ?: '—' }}</td></tr>
                            <tr><td>Hearing (Right)</td><td>{{ $med->hearing_right ?: '—' }}</td></tr>
                            <tr><td>Hearing (Left)</td><td>{{ $med->hearing_left ?: '—' }}</td></tr>
                            <tr><td>Dental Status</td><td>{{ $med->dental_status ?: '—' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── Right column ── --}}
            <div>
                <div class="srp-card">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.59 21 9a12.02 12.02 0 0 0-.382-3.016z"/></svg>
                        Immunization
                    </div>
                    <div class="srp-card-body">
                        <table class="srp-info-tbl">
                            <tr>
                                <td>Status</td>
                                <td>
                                    @if($med->immunization_status === 'Complete')
                                        <span class="badge-green">Complete</span>
                                    @elseif($med->immunization_status === 'Incomplete')
                                        <span class="badge-yellow">Incomplete</span>
                                    @else
                                        <span style="color:#94a3b8;">{{ $med->immunization_status ?: '—' }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><td>Notes</td><td>{{ $med->immunization_notes ?: '—' }}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="srp-card" style="margin-top:18px;">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Physical Examination
                    </div>
                    <div class="srp-card-body">
                        <table class="srp-info-tbl">
                            <tr><td>Last Exam Date</td><td>{{ $med->last_physical_exam_date ? $med->last_physical_exam_date->format('F j, Y') : '—' }}</td></tr>
                            <tr><td>Physician</td><td>{{ $med->physician_name ?: '—' }}</td></tr>
                            <tr><td>Findings</td><td>{{ $med->exam_findings ?: '—' }}</td></tr>
                            <tr><td>Remarks</td><td>{{ $med->remarks ?: '—' }}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="srp-card" style="margin-top:18px;">
                    <div class="srp-card-head">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.62 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.81-.81a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Emergency Contact
                    </div>
                    <div class="srp-card-body">
                        <table class="srp-info-tbl">
                            <tr><td>Name</td><td>{{ $med->emergency_contact_name ?: '—' }}</td></tr>
                            <tr><td>Number</td><td>{{ $med->emergency_contact_number ?: '—' }}</td></tr>
                            <tr><td>Relation</td><td>{{ $med->emergency_contact_relation ?: '—' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="srp-card" style="margin-top:4px;">
            <div class="srp-card-body" style="font-size:11.5px;color:#94a3b8;display:flex;gap:16px;">
                <span>Recorded by: <strong>{{ $med->recorded_by ?: '—' }}</strong></span>
                <span>Last updated: <strong>{{ $med->updated_at ? $med->updated_at->format('M j, Y') : '—' }}</strong></span>
            </div>
        </div>
        @endif

        {{-- Clinic Visit Records — hidden from view per request; markup kept but not rendered --}}
        @if(false)
        <div class="srp-card" style="margin-top:22px;">
            <div class="srp-card-head">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>
                Clinic Visit Records
                <span style="margin-left:auto;font-size:12px;font-weight:400;color:#64748b;">
                    {{ $clinicRecords->count() }} visit(s)
                </span>
                <button class="srp-btn-primary" style="margin-left:12px;padding:5px 14px;font-size:12px;" onclick="openClinicModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Visit
                </button>
            </div>
            @if($clinicRecords->isEmpty())
                <div class="srp-card-body" style="text-align:center;color:#94a3b8;padding:28px;">
                    No clinic visits recorded yet.
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="srp-tbl">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Chief Complaint</th>
                                <th>Diagnosis</th>
                                <th>Vitals</th>
                                <th>Treatment / Meds</th>
                                <th>Disposition</th>
                                <th>Attended By</th>
                                <th style="width:72px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clinicRecords as $cr)
                            <tr>
                                <td style="white-space:nowrap;font-weight:600;">
                                    {{ $cr->visit_date ? $cr->visit_date->format('M j, Y') : '—' }}
                                </td>
                                <td>{{ $cr->chief_complaint ?: '—' }}</td>
                                <td>{{ $cr->diagnosis ?: '—' }}</td>
                                <td style="font-size:11.5px;color:#475569;">
                                    @if($cr->temperature)<div>Temp: {{ $cr->temperature }}°C</div>@endif
                                    @if($cr->blood_pressure)<div>BP: {{ $cr->blood_pressure }}</div>@endif
                                    @if($cr->pulse_rate)<div>PR: {{ $cr->pulse_rate }} bpm</div>@endif
                                    @if($cr->respiratory_rate)<div>RR: {{ $cr->respiratory_rate }}</div>@endif
                                    @if($cr->weight_kg)<div>Wt: {{ $cr->weight_kg }} kg</div>@endif
                                    @if(!$cr->temperature && !$cr->blood_pressure && !$cr->pulse_rate && !$cr->respiratory_rate && !$cr->weight_kg)—@endif
                                </td>
                                <td style="font-size:12.5px;">
                                    @if($cr->treatment)<div>{{ $cr->treatment }}</div>@endif
                                    @if($cr->medications_given)<div style="color:#64748b;font-size:11.5px;">Meds: {{ $cr->medications_given }}</div>@endif
                                    @if(!$cr->treatment && !$cr->medications_given)—@endif
                                </td>
                                <td>
                                    @if($cr->disposition)
                                        <span class="badge-blue">{{ $cr->disposition }}</span>
                                        @if($cr->referred_to)<div style="font-size:11px;color:#64748b;margin-top:3px;">→ {{ $cr->referred_to }}</div>@endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $cr->attended_by ?: '—' }}</td>
                                <td style="text-align:center;">
                                    <button onclick="editClinicRecord({{ $cr->id }}, {{ json_encode($cr) }})"
                                        style="background:none;border:none;cursor:pointer;color:#2563eb;font-size:11px;padding:2px 6px;" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button onclick="deleteClinicRecord({{ $cr->id }}, this)"
                                        style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:11px;padding:2px 6px;" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endif

        </div>{{-- end srp-tab-medical --}}

    </div>{{-- end srp-body --}}
</div>

{{-- Edit Document Requirement Modal --}}
<div class="med-overlay" id="reqEditOverlay">
    <div class="med-modal" role="dialog" aria-modal="true" style="width:min(520px,95vw);">
        <div class="med-modal-head">
            <h3>Edit Document Requirement</h3>
            <button type="button" class="med-close-btn" onclick="srpCloseReqEdit()" aria-label="Close">&times;</button>
        </div>
        <form method="POST" id="reqEditForm">
            @csrf
            @method('PUT')
            <div class="med-modal-body">
                <div class="med-field" style="margin-bottom:14px;">
                    <label>Requirement Name</label>
                    <input type="text" name="requirement_name" id="req_edit_name" required>
                </div>
                <div class="med-field" style="margin-bottom:14px;">
                    <label>Type</label>
                    <select name="requirement_type" id="req_edit_type">
                        <option value="Document">Document</option>
                        <option value="Medical">Medical</option>
                        <option value="Clearance">Clearance</option>
                    </select>
                </div>
                <div class="med-field">
                    <label>Remarks</label>
                    <input type="text" name="remarks" id="req_edit_remarks" placeholder="Optional note for this student">
                </div>
            </div>
            <div class="med-modal-foot">
                <button type="button" class="med-btn-cancel" onclick="srpCloseReqEdit()">Cancel</button>
                <button type="submit" class="srp-btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- HD Copy For Modal --}}
<div class="med-overlay" id="hdCopyForOverlay">
    <div class="med-modal" role="dialog" aria-modal="true" style="width:min(480px,95vw);">
        <div class="med-modal-head">
            <h3>Tag for Honorable Dismissal</h3>
            <button type="button" class="med-close-btn" onclick="srpCancelHdTag()" aria-label="Close">&times;</button>
        </div>
        <div class="med-modal-body">
            <div class="med-field">
                <label>HD Copy For</label>
                <textarea id="hd_copy_for" style="min-height:90px;" placeholder="e.g. Employer, CHED, Student's file..."></textarea>
            </div>
        </div>
        <div class="med-modal-foot">
            <button type="button" class="med-btn-cancel" onclick="srpCancelHdTag()">Cancel</button>
            <button type="button" class="srp-btn-primary" onclick="srpConfirmHdTag()">Tag Student</button>
        </div>
    </div>
</div>

{{-- Scholastic Comment Modal --}}
<div class="med-overlay" id="scholasticCommentOverlay">
    <div class="med-modal" role="dialog" aria-modal="true">
        <div class="med-modal-head">
            <h3 id="scholasticCommentTitle">Scholastic Comment</h3>
            <button class="med-close-btn" onclick="closeScholasticCommentModal()" aria-label="Close">&times;</button>
        </div>
        <div class="med-modal-body">
            <input type="hidden" id="sc_id">
            <div class="med-grid">
                <div class="med-field"><label>Academic Year</label><input type="text" id="sc_school_year" value="{{ $student->school_year ?: '' }}" placeholder="e.g. 2025-2026"></div>
                <div class="med-field"><label>Semester</label><input type="text" id="sc_semester" value="{{ $student->semester ?: '' }}" placeholder="First / Second"></div>
                <div class="med-field"><label>Date Issued</label><input type="date" id="sc_date_issued" value="{{ now()->toDateString() }}"></div>
                <div class="med-field"><label>Issued By</label><input type="text" id="sc_issued_by" value="{{ optional(auth()->user())->name ?: 'Registrar' }}"></div>
                <div class="med-field" style="grid-column:span 2;">
                    <label>Comment / Remark</label>
                    <textarea id="sc_comment" style="min-height:110px;" placeholder="Enter scholastic comment..."></textarea>
                </div>
            </div>
        </div>
        <div class="med-modal-foot">
            <button class="med-btn-cancel" onclick="closeScholasticCommentModal()">Cancel</button>
            <button class="srp-btn-primary" onclick="saveScholasticComment()">Save Comment</button>
        </div>
    </div>
</div>

{{-- Medical Record Modal --}}
<div class="med-overlay" id="medOverlay">
    <div class="med-modal" role="dialog" aria-modal="true">
        <div class="med-modal-head">
            <h3>Medical Record</h3>
            <button class="med-close-btn" onclick="closeMedModal()" aria-label="Close">&times;</button>
        </div>
        <div class="med-modal-body">
            <div class="med-section-title">Vitals</div>
            <div class="med-grid-3">
                <div class="med-field">
                    <label>Blood Type</label>
                    <select id="med_blood_type">
                        <option value="">— select —</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                            <option value="{{ $bt }}" {{ ($medicalRecord && $medicalRecord->blood_type === $bt) ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="med-field">
                    <label>Height (cm)</label>
                    <input type="number" step="0.01" id="med_height_cm" value="{{ $medicalRecord->height_cm ?? '' }}" placeholder="e.g. 165.5">
                </div>
                <div class="med-field">
                    <label>Weight (kg)</label>
                    <input type="number" step="0.01" id="med_weight_kg" value="{{ $medicalRecord->weight_kg ?? '' }}" placeholder="e.g. 60.0">
                </div>
            </div>

            <div class="med-section-title">Health History</div>
            <div class="med-grid">
                <div class="med-field">
                    <label>Allergies</label>
                    <textarea id="med_allergies" placeholder="List known allergies...">{{ $medicalRecord->allergies ?? '' }}</textarea>
                </div>
                <div class="med-field">
                    <label>Medical Conditions</label>
                    <textarea id="med_medical_conditions" placeholder="Chronic or existing conditions...">{{ $medicalRecord->medical_conditions ?? '' }}</textarea>
                </div>
                <div class="med-field">
                    <label>Current Medications</label>
                    <textarea id="med_current_medications" placeholder="Ongoing prescriptions...">{{ $medicalRecord->current_medications ?? '' }}</textarea>
                </div>
                <div class="med-field">
                    <label>Past Illnesses / Hospitalizations</label>
                    <textarea id="med_past_illnesses" placeholder="Previous illnesses or surgeries...">{{ $medicalRecord->past_illnesses ?? '' }}</textarea>
                </div>
                <div class="med-field">
                    <label>Mental Health Notes</label>
                    <textarea id="med_mental_health_notes" placeholder="Optional mental health notes...">{{ $medicalRecord->mental_health_notes ?? '' }}</textarea>
                </div>
            </div>

            <div class="med-section-title">Sensory &amp; Dental</div>
            <div class="med-grid">
                <div class="med-field"><label>Vision Right (OD)</label><input type="text" id="med_vision_od" value="{{ $medicalRecord->vision_od ?? '' }}" placeholder="e.g. 20/20"></div>
                <div class="med-field"><label>Vision Left (OS)</label><input type="text" id="med_vision_os" value="{{ $medicalRecord->vision_os ?? '' }}" placeholder="e.g. 20/40"></div>
                <div class="med-field"><label>Hearing Right</label><input type="text" id="med_hearing_right" value="{{ $medicalRecord->hearing_right ?? '' }}" placeholder="e.g. Normal"></div>
                <div class="med-field"><label>Hearing Left</label><input type="text" id="med_hearing_left" value="{{ $medicalRecord->hearing_left ?? '' }}" placeholder="e.g. Normal"></div>
                <div class="med-field" style="grid-column:span 2;">
                    <label>Dental Status</label>
                    <textarea id="med_dental_status" style="min-height:50px;" placeholder="Dental condition notes...">{{ $medicalRecord->dental_status ?? '' }}</textarea>
                </div>
            </div>

            <div class="med-section-title">Immunization</div>
            <div class="med-grid">
                <div class="med-field">
                    <label>Immunization Status</label>
                    <select id="med_immunization_status">
                        <option value="">— select —</option>
                        @foreach(['Complete','Incomplete','Unknown'] as $is)
                            <option value="{{ $is }}" {{ ($medicalRecord && $medicalRecord->immunization_status === $is) ? 'selected' : '' }}>{{ $is }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="med-field">
                    <label>Immunization Notes</label>
                    <textarea id="med_immunization_notes" style="min-height:50px;">{{ $medicalRecord->immunization_notes ?? '' }}</textarea>
                </div>
            </div>

            <div class="med-section-title">Physical Examination</div>
            <div class="med-grid">
                <div class="med-field"><label>Last Exam Date</label><input type="date" id="med_last_physical_exam_date" value="{{ $medicalRecord && $medicalRecord->last_physical_exam_date ? $medicalRecord->last_physical_exam_date->format('Y-m-d') : '' }}"></div>
                <div class="med-field"><label>Physician Name</label><input type="text" id="med_physician_name" value="{{ $medicalRecord->physician_name ?? '' }}" placeholder="Dr. "></div>
                <div class="med-field"><label>Exam Findings</label><textarea id="med_exam_findings">{{ $medicalRecord->exam_findings ?? '' }}</textarea></div>
                <div class="med-field"><label>Remarks</label><textarea id="med_remarks">{{ $medicalRecord->remarks ?? '' }}</textarea></div>
            </div>

            <div class="med-section-title">Emergency Contact</div>
            <div class="med-grid-3">
                <div class="med-field"><label>Name</label><input type="text" id="med_emergency_contact_name" value="{{ $medicalRecord->emergency_contact_name ?? '' }}"></div>
                <div class="med-field"><label>Number</label><input type="text" id="med_emergency_contact_number" value="{{ $medicalRecord->emergency_contact_number ?? '' }}"></div>
                <div class="med-field"><label>Relation</label><input type="text" id="med_emergency_contact_relation" value="{{ $medicalRecord->emergency_contact_relation ?? '' }}" placeholder="e.g. Parent, Sibling"></div>
            </div>
        </div>
        <div class="med-modal-foot">
            <button class="med-btn-cancel" onclick="closeMedModal()">Cancel</button>
            <button class="srp-btn-primary" onclick="saveMedical()">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Medical Record
            </button>
        </div>
    </div>
</div>

{{-- Clinic Visit Modal --}}
<div class="med-overlay" id="clinicOverlay">
    <div class="med-modal" role="dialog" aria-modal="true">
        <div class="med-modal-head">
            <h3 id="clinicModalTitle">Add Clinic Visit</h3>
            <button class="med-close-btn" onclick="closeClinicModal()" aria-label="Close">&times;</button>
        </div>
        <div class="med-modal-body">
            <input type="hidden" id="clinic_id">

            <div class="med-section-title">Visit Details</div>
            <div class="med-grid">
                <div class="med-field"><label>Visit Date <span style="color:#dc2626;">*</span></label><input type="date" id="clinic_visit_date"></div>
                <div class="med-field"><label>Chief Complaint</label><input type="text" id="clinic_chief_complaint" placeholder="Main reason for visit..."></div>
                <div class="med-field"><label>Diagnosis</label><input type="text" id="clinic_diagnosis" placeholder="Clinical diagnosis..."></div>
                <div class="med-field"><label>Attended By</label><input type="text" id="clinic_attended_by" placeholder="Nurse / Physician name..."></div>
            </div>

            <div class="med-section-title">Vital Signs</div>
            <div class="med-grid-3">
                <div class="med-field"><label>Temperature (°C)</label><input type="number" step="0.1" id="clinic_temperature" placeholder="e.g. 37.0"></div>
                <div class="med-field"><label>Blood Pressure</label><input type="text" id="clinic_blood_pressure" placeholder="e.g. 120/80"></div>
                <div class="med-field"><label>Pulse Rate (bpm)</label><input type="number" id="clinic_pulse_rate" placeholder="e.g. 72"></div>
                <div class="med-field"><label>Respiratory Rate</label><input type="number" id="clinic_respiratory_rate" placeholder="e.g. 16"></div>
                <div class="med-field"><label>Weight (kg)</label><input type="number" step="0.01" id="clinic_weight_kg" placeholder="e.g. 60.0"></div>
            </div>

            <div class="med-section-title">Management</div>
            <div class="med-grid">
                <div class="med-field"><label>Treatment / Management</label><textarea id="clinic_treatment" placeholder="Procedure or treatment given..."></textarea></div>
                <div class="med-field"><label>Medications Given</label><textarea id="clinic_medications_given" placeholder="Medicines dispensed..."></textarea></div>
            </div>

            <div class="med-section-title">Outcome</div>
            <div class="med-grid">
                <div class="med-field">
                    <label>Disposition</label>
                    <select id="clinic_disposition">
                        <option value="">— select —</option>
                        @foreach(['Sent home','Rest in clinic','Referred to hospital','Referred to specialist','Advised OPD','No action needed','Other'] as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="med-field"><label>Referred To</label><input type="text" id="clinic_referred_to" placeholder="Hospital / Specialist name..."></div>
                <div class="med-field" style="grid-column:span 2;"><label>Remarks</label><textarea id="clinic_remarks" style="min-height:50px;" placeholder="Additional notes..."></textarea></div>
            </div>
        </div>
        <div class="med-modal-foot">
            <button class="med-btn-cancel" onclick="closeClinicModal()">Cancel</button>
            <button class="srp-btn-primary" onclick="saveClinicRecord()">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Visit
            </button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="srp-toast"></div>

<div id="ogrPrintContainer" aria-hidden="true"></div>

@push('scripts')
<script>
const SRP_PROFILE_URL = @json($profileUpdateUrl);
const SRP_CSRF        = document.querySelector('meta[name=csrf-token]').getAttribute('content');
const HD_TAG_URL      = @json(route('registrar.registrar-menu.forms.honorable-dismissal.tag', ['student' => $student->id]));
const REQ_UPDATE_URL_BASE = @json(route('registrar.registrar-menu.student-mgmt.student-records.requirements.update', ['student' => $student->id, 'requirement' => '__ID__']));

// ── Edit Document Requirement ──────────────────────────────
function srpEditRequirement(id, name, type, remarks) {
    document.getElementById('req_edit_name').value = name || '';
    document.getElementById('req_edit_type').value = type || 'Document';
    document.getElementById('req_edit_remarks').value = remarks || '';
    document.getElementById('reqEditForm').action = REQ_UPDATE_URL_BASE.replace('__ID__', id);
    document.getElementById('reqEditOverlay').classList.add('open');
}
function srpCloseReqEdit() {
    document.getElementById('reqEditOverlay').classList.remove('open');
}
document.getElementById('reqEditOverlay').addEventListener('click', function(e) {
    if (e.target === this) srpCloseReqEdit();
});

// ── Tag for Honorable Dismissal ────────────────────────────
let _hdTagCheckbox = null;

function srpTagHonorableDismissal(checkbox) {
    if (!checkbox.checked) {
        return;
    }

    _hdTagCheckbox = checkbox;
    checkbox.disabled = true;
    document.getElementById('hd_copy_for').value = '';
    document.getElementById('hdCopyForOverlay').classList.add('open');
}

function srpCancelHdTag() {
    document.getElementById('hdCopyForOverlay').classList.remove('open');
    if (_hdTagCheckbox) {
        _hdTagCheckbox.checked = false;
        _hdTagCheckbox.disabled = false;
        _hdTagCheckbox = null;
    }
}

document.getElementById('hdCopyForOverlay').addEventListener('click', function(e) {
    if (e.target === this) srpCancelHdTag();
});

async function srpConfirmHdTag() {
    const checkbox = _hdTagCheckbox;
    const copyFor = document.getElementById('hd_copy_for').value.trim();

    try {
        const r = await fetch(HD_TAG_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': SRP_CSRF, Accept: 'application/json' },
            body: JSON.stringify({ copy_for: copyFor }),
        });
        const data = await r.json();

        if (data.success) {
            document.getElementById('hdCopyForOverlay').classList.remove('open');
            _hdTagCheckbox = null;
            srpToast(data.message || 'Student tagged for Honorable Dismissal.', 'success');
            setTimeout(function () { location.reload(); }, 700);
        } else {
            srpToast(data.message || 'Unable to tag student.', 'error');
            if (checkbox) {
                checkbox.checked = false;
                checkbox.disabled = false;
            }
        }
    } catch (e) {
        srpToast('Network error — check connection.', 'error');
        if (checkbox) {
            checkbox.checked = false;
            checkbox.disabled = false;
        }
    }
}

// ── Tab switching ──────────────────────────────────────────
document.querySelectorAll('.srp-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.srp-tab,.srp-panel').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('srp-tab-' + btn.dataset.tab).classList.add('active');
    });
});

// ── Collect personal info payload ─────────────────────────
function buildInfoPayload() {
    const g = id => document.getElementById(id)?.value?.trim() ?? '';
    return {
        first_name: g('pi_first_name'), last_name: g('pi_last_name'),
        middle_name: g('pi_middle_name'), suffix: g('pi_suffix'),
        gender: g('pi_gender'), date_of_birth: g('pi_dob'),
        place_of_birth: g('pi_pob'), civil_status: g('pi_civil_status'),
        nationality: g('pi_nationality'), religion: g('pi_religion'),
        mobile_number: g('pi_mobile'), student_email: g('pi_email'),
        present_street: g('pi_pstreet'), present_barangay: g('pi_pbrgy'),
        present_municipality: g('pi_pmuni'), present_province: g('pi_pprov'),
        present_region: g('pi_pregion'), present_zipcode: g('pi_pzip'),
        permanent_street: g('pi_perstreet'), permanent_barangay: g('pi_perbrgy'),
        permanent_municipality: g('pi_permuni'), permanent_province: g('pi_perprov'),
        permanent_region: g('pi_perregion'), permanent_zipcode: g('pi_perzip'),
    };
}

// ── Collect background payload ─────────────────────────────
function buildBgPayload() {
    const g = id => document.getElementById(id)?.value?.trim() ?? '';
    return {
        lrn: g('bg_lrn'), shs_track_strand: g('bg_shs_track'),
        no_k12: g('bg_no_k12'),
        elementary_school: g('bg_elementary_school'), high_school: g('bg_high_school'),
        school_last_attended: g('bg_school_last_attended'),
        junior_school: g('bg_junior_school'), senior_school: g('bg_senior_school'),
        mother_firstname: g('bg_mfn'), mother_lastname: g('bg_mln'),
        mother_contact: g('bg_mc'), mother_occupation: g('bg_mo'),
        father_firstname: g('bg_ffn'), father_lastname: g('bg_fln'),
        father_contact: g('bg_fc'), father_occupation: g('bg_fo'),
        guardian_firstname: g('bg_gfn'), guardian_lastname: g('bg_gln'),
        guardian_contact: g('bg_gc'), guardian_occupation: g('bg_go'),
        guardian_address: g('bg_ga'), parent_marital_status: g('bg_pms'),
        monthly_family_income: g('bg_mfi'),
        number_of_siblings: g('bg_sib'), household_members: g('bg_hh'),
        family_income_source: g('bg_fis'), living_situation: g('bg_ls'),
    };
}

async function srpSaveInfo() {
    await srpDoSave(buildInfoPayload());
}
async function srpSaveBg() {
    await srpDoSave(buildBgPayload());
}

async function srpDoSave(payload) {
    try {
        const r = await fetch(SRP_PROFILE_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': SRP_CSRF, Accept: 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await r.json();
        if (data.success) srpToast('Saved successfully!', 'success');
        else srpToast(data.message || 'Save failed.', 'error');
    } catch (e) {
        srpToast('Network error — check connection.', 'error');
    }
}

function srpToast(msg, type = '') {
    const t = document.getElementById('srp-toast');
    t.textContent = msg;
    t.className = 'show ' + type;
    clearTimeout(t._tid);
    t._tid = setTimeout(() => t.className = '', 3000);
}

// ── Medical modal ──────────────────────────────────────────
const SCHOLASTIC_COMMENT_SAVE_URL = '{{ route("registrar.registrar-menu.student-mgmt.student-records.scholastic-comments.save", ["student" => $student->id]) }}';

function openScholasticCommentModal() {
    document.getElementById('scholasticCommentTitle').textContent = 'Add Scholastic Comment';
    document.getElementById('sc_id').value = '';
    document.getElementById('sc_school_year').value = @json($student->school_year ?: '');
    document.getElementById('sc_semester').value = @json($student->semester ?: '');
    document.getElementById('sc_date_issued').value = @json(now()->toDateString());
    document.getElementById('sc_issued_by').value = @json(optional(auth()->user())->name ?: 'Registrar');
    document.getElementById('sc_comment').value = '';
    document.getElementById('scholasticCommentOverlay').classList.add('open');
}

function editScholasticComment(data) {
    document.getElementById('scholasticCommentTitle').textContent = 'Edit Scholastic Comment';
    document.getElementById('sc_id').value = data.id || '';
    document.getElementById('sc_school_year').value = data.school_year || '';
    document.getElementById('sc_semester').value = data.semester || '';
    document.getElementById('sc_date_issued').value = data.date_issued || '';
    document.getElementById('sc_issued_by').value = data.issued_by || '';
    document.getElementById('sc_comment').value = data.comment || '';
    document.getElementById('scholasticCommentOverlay').classList.add('open');
}

function closeScholasticCommentModal() {
    document.getElementById('scholasticCommentOverlay').classList.remove('open');
}

document.getElementById('scholasticCommentOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeScholasticCommentModal();
});

async function saveScholasticComment() {
    const comment = g('sc_comment');
    if (!comment) {
        srpToast('Comment is required.', 'error');
        return;
    }

    try {
        const r = await fetch(SCHOLASTIC_COMMENT_SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': SRP_CSRF, Accept: 'application/json' },
            body: JSON.stringify({
                id: g('sc_id') || null,
                school_year: g('sc_school_year'),
                semester: g('sc_semester'),
                comment: comment,
                date_issued: g('sc_date_issued'),
                issued_by: g('sc_issued_by'),
            }),
        });
        const data = await r.json();
        if (data.success) {
            srpToast('Scholastic comment saved!', 'success');
            closeScholasticCommentModal();
            setTimeout(function() { location.reload(); }, 700);
        } else {
            srpToast(data.message || 'Save failed.', 'error');
        }
    } catch (e) {
        srpToast('Network error - check connection.', 'error');
    }
}

const MED_SAVE_URL = '{{ route("registrar.registrar-menu.student-mgmt.student-records.medical.save", ["student" => $student->id]) }}';

function openMedModal() {
    document.getElementById('medOverlay').classList.add('open');
}
function closeMedModal() {
    document.getElementById('medOverlay').classList.remove('open');
}
document.getElementById('medOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeMedModal();
});

function g(id) {
    var el = document.getElementById(id);
    return el ? el.value.trim() : '';
}

async function saveMedical() {
    var payload = {
        blood_type: g('med_blood_type'),
        height_cm: g('med_height_cm'),
        weight_kg: g('med_weight_kg'),
        allergies: g('med_allergies'),
        medical_conditions: g('med_medical_conditions'),
        current_medications: g('med_current_medications'),
        past_illnesses: g('med_past_illnesses'),
        mental_health_notes: g('med_mental_health_notes'),
        vision_od: g('med_vision_od'),
        vision_os: g('med_vision_os'),
        hearing_right: g('med_hearing_right'),
        hearing_left: g('med_hearing_left'),
        dental_status: g('med_dental_status'),
        immunization_status: g('med_immunization_status'),
        immunization_notes: g('med_immunization_notes'),
        last_physical_exam_date: g('med_last_physical_exam_date'),
        physician_name: g('med_physician_name'),
        exam_findings: g('med_exam_findings'),
        remarks: g('med_remarks'),
        emergency_contact_name: g('med_emergency_contact_name'),
        emergency_contact_number: g('med_emergency_contact_number'),
        emergency_contact_relation: g('med_emergency_contact_relation'),
    };
    try {
        var r = await fetch(MED_SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': SRP_CSRF, Accept: 'application/json' },
            body: JSON.stringify(payload),
        });
        var data = await r.json();
        if (data.success) {
            srpToast('Medical record saved!', 'success');
            closeMedModal();
            setTimeout(function() { location.reload(); }, 900);
        } else {
            srpToast(data.message || 'Save failed.', 'error');
        }
    } catch (e) {
        srpToast('Network error — check connection.', 'error');
    }
}

function srpToggleDocumentRequirementForm() {
    var panel = document.getElementById('docRequirementAddPanel');
    if (panel) panel.classList.toggle('open');
}

// ── Clinic visit modal ─────────────────────────────────────
const CLINIC_SAVE_URL   = '{{ route("registrar.registrar-menu.student-mgmt.student-records.clinic.save", ["student" => $student->id]) }}';
const CLINIC_DELETE_URL = '{{ route("registrar.registrar-menu.student-mgmt.student-records.clinic.delete", ["student" => $student->id, "clinic" => "__ID__"]) }}';

function openClinicModal() {
    document.getElementById('clinicModalTitle').textContent = 'Add Clinic Visit';
    document.getElementById('clinic_id').value = '';
    ['visit_date','chief_complaint','diagnosis','attended_by','temperature',
     'blood_pressure','pulse_rate','respiratory_rate','weight_kg',
     'treatment','medications_given','referred_to','remarks'].forEach(function(f) {
        var el = document.getElementById('clinic_' + f);
        if (el) el.value = '';
    });
    document.getElementById('clinic_disposition').value = '';
    document.getElementById('clinicOverlay').classList.add('open');
}
function closeClinicModal() {
    document.getElementById('clinicOverlay').classList.remove('open');
}
document.getElementById('clinicOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeClinicModal();
});

function editClinicRecord(id, data) {
    document.getElementById('clinicModalTitle').textContent = 'Edit Clinic Visit';
    document.getElementById('clinic_id').value = id;
    var map = {
        visit_date: data.visit_date,
        chief_complaint: data.chief_complaint || '',
        diagnosis: data.diagnosis || '',
        attended_by: data.attended_by || '',
        temperature: data.temperature || '',
        blood_pressure: data.blood_pressure || '',
        pulse_rate: data.pulse_rate || '',
        respiratory_rate: data.respiratory_rate || '',
        weight_kg: data.weight_kg || '',
        treatment: data.treatment || '',
        medications_given: data.medications_given || '',
        disposition: data.disposition || '',
        referred_to: data.referred_to || '',
        remarks: data.remarks || '',
    };
    Object.keys(map).forEach(function(f) {
        var el = document.getElementById('clinic_' + f);
        if (el) el.value = map[f] || '';
    });
    document.getElementById('clinicOverlay').classList.add('open');
}

async function saveClinicRecord() {
    var visitDate = document.getElementById('clinic_visit_date').value.trim();
    if (!visitDate) { srpToast('Visit date is required.', 'error'); return; }
    var payload = {
        id: document.getElementById('clinic_id').value || null,
        visit_date: visitDate,
        chief_complaint: g('clinic_chief_complaint'),
        diagnosis: g('clinic_diagnosis'),
        attended_by: g('clinic_attended_by'),
        temperature: g('clinic_temperature'),
        blood_pressure: g('clinic_blood_pressure'),
        pulse_rate: g('clinic_pulse_rate'),
        respiratory_rate: g('clinic_respiratory_rate'),
        weight_kg: g('clinic_weight_kg'),
        treatment: g('clinic_treatment'),
        medications_given: g('clinic_medications_given'),
        disposition: g('clinic_disposition'),
        referred_to: g('clinic_referred_to'),
        remarks: g('clinic_remarks'),
    };
    try {
        var r = await fetch(CLINIC_SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': SRP_CSRF, Accept: 'application/json' },
            body: JSON.stringify(payload),
        });
        var data = await r.json();
        if (data.success) {
            srpToast('Clinic visit saved!', 'success');
            closeClinicModal();
            setTimeout(function() { location.reload(); }, 900);
        } else {
            srpToast(data.message || 'Save failed.', 'error');
        }
    } catch (e) {
        srpToast('Network error — check connection.', 'error');
    }
}

async function deleteClinicRecord(id, btn) {
    if (!confirm('Delete this clinic visit record?')) return;
    var url = CLINIC_DELETE_URL.replace('__ID__', id);
    try {
        var r = await fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': SRP_CSRF, Accept: 'application/json' },
        });
        var data = await r.json();
        if (data.success) {
            srpToast('Record deleted.', 'success');
            setTimeout(function() { location.reload(); }, 700);
        } else {
            srpToast(data.message || 'Delete failed.', 'error');
        }
    } catch (e) {
        srpToast('Network error.', 'error');
    }
}

// ── Report of Grades (CWA) ─────────────────────────────────
const SRP_REPORT_OF_GRADES_URL_BASE = @json(route('registrar.registrar-menu.student-mgmt.student-records.report-of-grades', ['student' => '__ID__']));

function srpOpenReportOfGrades(studentId) {
    var url = SRP_REPORT_OF_GRADES_URL_BASE.replace('__ID__', studentId);

    fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (payload) {
            if (!payload || !payload.ok) {
                Swal.fire({ title: 'Unable to generate report', text: (payload && payload.message) || 'Please try again.', icon: 'error', confirmButtonColor: '#15803d' });
                return;
            }

            if (!payload.complete) {
                var missing = (payload.subjects || []).filter(function (s) { return !s.is_posted; });
                Swal.fire({
                    title: 'Report of Grades Not Yet Available',
                    html: 'All subjects for the current semester must have posted final grades first.<br><br>'
                        + '<strong>Still missing:</strong><ul style="text-align:left;margin:8px 0 0;">'
                        + missing.map(function (s) { return '<li>' + s.code + ' — ' + s.desc + '</li>'; }).join('')
                        + '</ul>',
                    icon: 'warning',
                    confirmButtonColor: '#15803d',
                });
                return;
            }

            var data = {
                studentNo: payload.meta.studentNo,
                studentName: payload.meta.studentName,
                section: payload.meta.section,
            };

            var html = ogrBuildTemplate(data, payload.subjects, payload.meta);
            ogrPrintSheets([html]);
        })
        .catch(function () {
            Swal.fire({ title: 'Network Error', text: 'Something went wrong. Please try again.', icon: 'error', confirmButtonColor: '#15803d' });
        });
}
</script>
<script src="{{ asset('js/official-grade-report.js') }}?v={{ file_exists(public_path('js/official-grade-report.js')) ? filemtime(public_path('js/official-grade-report.js')) : time() }}"></script>
@endpush
@endsection
