@extends('layouts.registrar')

@section('title', 'PLP - Registrar Dashboard')
@section('page-title', 'DASHBOARD')
@section('body-class', 'page-registrar-dashboard')

@push('styles')
<style>
/* ═══════════════════════════════════════════
   REGISTRAR ANALYTICS DASHBOARD
═══════════════════════════════════════════ */

.rda-root { display: flex; flex-direction: column; gap: 18px; }

/* ── Welcome Banner ──────────────────────── */
.rda-banner {
    background: linear-gradient(130deg, #004d27 0%, #006837 45%, #1a9e54 100%);
    border-radius: 16px;
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: #fff;
    box-shadow: 0 6px 24px rgba(0, 80, 40, 0.28);
    position: relative;
    overflow: hidden;
}
.rda-banner::after {
    content: '';
    position: absolute;
    right: -40px; top: -40px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    pointer-events: none;
}
.rda-banner-greeting {
    font-size: 0.7rem;
    font-weight: 600;
    opacity: 0.75;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 5px;
}
.rda-banner-title {
    font-size: 1.3rem;
    font-weight: 800;
    line-height: 1.2;
}
.rda-banner-date {
    font-size: 0.74rem;
    opacity: 0.7;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.rda-banner-pills {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}
.rda-banner-pill {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.22);
    border-radius: 12px;
    padding: 10px 18px;
    text-align: center;
    backdrop-filter: blur(6px);
    min-width: 80px;
}
.rda-banner-pill-val {
    font-size: 1.2rem;
    font-weight: 800;
    line-height: 1;
}
.rda-banner-pill-lbl {
    font-size: 0.62rem;
    opacity: 0.75;
    margin-top: 3px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

/* ── Top Section: KPI Grid + Quick Access ── */
.rda-top {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 18px;
    align-items: start;
}
.rda-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

/* ── KPI Card ────────────────────────────── */
.rda-kpi {
    background: #fff;
    border-radius: 14px;
    padding: 18px 18px 14px;
    border: 1px solid #e4ede8;
    box-shadow: 0 1px 4px rgba(0,60,30,.05);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    cursor: default;
    min-height: 130px;
}
.rda-kpi:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,60,30,.12);
}
.rda-kpi-icon {
    position: absolute;
    top: 14px; right: 14px;
    width: 40px; height: 40px;
    border-radius: 11px;
    background: var(--kc, #006837);
    display: flex;
    align-items: center;
    justify-content: center;
}
.rda-kpi-label {
    font-size: 0.67rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 6px;
    padding-right: 50px;
}
.rda-kpi-value {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
    margin-bottom: 5px;
}
.rda-kpi-meta {
    font-size: 0.7rem;
    color: #94a3b8;
    flex: 1;
    margin-bottom: 8px;
}
.rda-kpi-tag {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 0.67rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
    width: fit-content;
}
.rda-kpi-tag.up   { background: #dcfce7; color: #15803d; }
.rda-kpi-tag.down { background: #fee2e2; color: #b91c1c; }
.rda-kpi-tag.neu  { background: #f1f5f9; color: #475569; }
.rda-kpi-bar {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--kc, #006837);
    opacity: 0.6;
}

/* ── Quick Access Card ───────────────────── */
.rda-quick {
    background: #fff;
    border-radius: 14px;
    padding: 18px 16px 14px;
    border: 1px solid #e4ede8;
    box-shadow: 0 1px 4px rgba(0,60,30,.05);
}
.rda-quick-hd {
    font-size: 0.7rem;
    font-weight: 700;
    color: #1e293b;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f4f2;
}
.rda-qa {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 8px;
    border-radius: 10px;
    text-decoration: none;
    color: #334155;
    transition: background 0.14s, color 0.14s;
    margin-bottom: 1px;
}
.rda-qa:hover {
    background: #f0faf4;
    color: #006837;
    text-decoration: none;
}
.rda-qa:hover .rda-qa-arrow { opacity: 1; transform: translateX(3px); }
.rda-qa-dot {
    width: 32px; height: 32px;
    border-radius: 9px;
    background: var(--qc, #e8f5e9);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.rda-qa-info { flex: 1; min-width: 0; }
.rda-qa-name { font-size: 0.76rem; font-weight: 600; line-height: 1.2; }
.rda-qa-sub  { font-size: 0.65rem; color: #94a3b8; margin-top: 1px; }
.rda-qa-arrow {
    color: #94a3b8;
    opacity: 0.4;
    transition: opacity 0.14s, transform 0.14s;
    flex-shrink: 0;
}

/* ── Chart Cards ─────────────────────────── */
.rda-charts-main   { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; }
.rda-charts-bottom { display: grid; grid-template-columns: 1fr; gap: 16px; }
.rda-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    border: 1px solid #e4ede8;
    box-shadow: 0 1px 4px rgba(0,60,30,.05);
}
.rda-card-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.rda-card-title {
    font-size: 0.71rem;
    font-weight: 700;
    color: #1e293b;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    display: flex;
    align-items: center;
    gap: 6px;
}
.rda-card-badge {
    font-size: 0.67rem;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e4ede8;
    border-radius: 20px;
    padding: 3px 10px;
    font-weight: 600;
}

/* ── Donut ───────────────────────────────── */
.rda-donut-wrap { position: relative; display: flex; align-items: center; justify-content: center; }
.rda-sex-chart-box {
    width: 170px;
    height: 170px;
    max-width: 100%;
    aspect-ratio: 1 / 1;
}
.rda-sex-chart-box canvas {
    width: 100% !important;
    height: 100% !important;
}
.rda-donut-label { position: absolute; text-align: center; pointer-events: none; }
.rda-donut-lv { font-size: 1.3rem; font-weight: 800; color: #0f172a; line-height: 1; }
.rda-donut-ls { font-size: 0.61rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 3px; }

/* ── Stat pills (exam card) ──────────────── */
.rda-pills { display: flex; gap: 8px; margin-bottom: 14px; }
.rda-pill {
    flex: 1;
    background: #f8fafc;
    border: 1px solid #e4ede8;
    border-radius: 10px;
    padding: 9px 8px;
    text-align: center;
}
.rda-pill-val { font-size: 1.15rem; font-weight: 800; line-height: 1; }
.rda-pill-lbl { font-size: 0.62rem; color: #94a3b8; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 600; }

/* ── Legend ──────────────────────────────── */
.rda-enroll-chart-wrap { height: 285px; position: relative; }

.rda-legend { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; justify-content: center; }
.rda-legend-item { display: flex; align-items: center; gap: 5px; font-size: 0.7rem; color: #475569; }
.rda-legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

/* ── Announcements ───────────────────────── */
.rda-ann-feed { display: flex; flex-direction: column; gap: 1px; }
.rda-ann-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 9px 8px;
    border-radius: 9px;
    transition: background 0.12s;
}
.rda-ann-row:hover { background: #f8fafc; }
.rda-ann-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #006837;
    flex-shrink: 0;
    margin-top: 5px;
}
.rda-ann-body { flex: 1; min-width: 0; }
.rda-ann-title {
    font-size: 0.78rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rda-ann-meta { font-size: 0.67rem; color: #94a3b8; margin-top: 2px; }
.rda-ann-x {
    width: 22px; height: 22px;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.12s, background 0.12s;
    flex-shrink: 0;
    padding: 0;
    color: #94a3b8;
}
.rda-ann-row:hover .rda-ann-x { opacity: 1; }
.rda-ann-x:hover { background: #fee2e2; color: #b91c1c; }
.rda-ann-empty { font-size: 0.78rem; color: #94a3b8; text-align: center; padding: 30px 0; }

/* ── Responsive ──────────────────────────── */
@media (max-width: 1280px) {
    .rda-top { grid-template-columns: 1fr; }
    .rda-charts-main { grid-template-columns: 1fr; }
}
@media (max-width: 960px) {
    .rda-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .rda-banner-pills { display: none; }
}
@media (max-width: 640px) {
    .rda-charts-bottom { grid-template-columns: 1fr; }
    .rda-kpi-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

@section('content')
@php
    $d              = $dashboardData ?? [];
    $activeTermLabel = trim((string) ($d['activeTermLabel'] ?? ''));
    $studentCount   = (int)   ($d['studentCount']   ?? 0);
    $maleCount      = (int)   ($d['maleCount']      ?? 0);
    $femaleCount    = (int)   ($d['femaleCount']     ?? 0);
    $applicantCount = (int)   ($d['applicantCount']  ?? 0);
    $facultyCount   = (int)   ($d['facultyCount']    ?? 0);
    $facultyFullTimeCount = (int) ($d['facultyFullTimeCount'] ?? 0);
    $facultyPartTimeCount = (int) ($d['facultyPartTimeCount'] ?? 0);
    $departmentCount= (int)   ($d['departmentCount'] ?? 0);
    $graduateCount  = (int)   ($d['graduateCount']   ?? 0);
    $examPassed     = (int)   ($d['examPassed']      ?? 0);
    $examFailed     = (int)   ($d['examFailed']      ?? 0);
    $examPending    = (int)   ($d['examPending']     ?? 0);
    $trendPercent   = (float) ($d['trendPercent']    ?? 0);
    $trendSign      = $trendPercent >= 0 ? '+' : '';
    $trendText      = $trendSign . rtrim(rtrim(number_format($trendPercent, 1), '0'), '.') . '%';
    $trendClass     = $trendPercent >= 0 ? 'up' : 'down';
    $trendArrow     = $trendPercent >= 0 ? '↑' : '↓';
    $trendValues    = $d['trendValues']  ?? [42, 48, 53, 59, 64, 68];
    $monthLabels    = $d['monthLabels']  ?? ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May'];
    $enrollmentMatrix = is_array($d['enrollmentProgramSemesterMatrix'] ?? null) ? $d['enrollmentProgramSemesterMatrix'] : [];
    $enrollmentSemesters = array_values((array) ($enrollmentMatrix['semesters'] ?? []));
    $enrollmentRows = array_values((array) ($enrollmentMatrix['rows'] ?? []));
    $enrollmentMax = max(1, (int) ($enrollmentMatrix['max'] ?? 1));
    $passRate       = $applicantCount > 0 ? round(($examPassed / $applicantCount) * 100, 1) : 0;
    $dashAnn        = is_array($dashboardAnnouncements ?? null) ? $dashboardAnnouncements : [];

    $hour     = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $dateStr  = now()->format('l, F j, Y');
@endphp

<div class="rda-root">

    {{-- ══ WELCOME BANNER ══════════════════════════════ --}}
    <div class="rda-banner">
        <div>
            <div class="rda-banner-greeting">{{ $greeting }}, Registrar</div>
            <div class="rda-banner-title">Dashboard Overview</div>
            <div class="rda-banner-date">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ $dateStr }}
            </div>
        </div>
        <div class="rda-banner-pills">
            <div class="rda-banner-pill">
                <div class="rda-banner-pill-val">{{ number_format($studentCount) }}</div>
                <div class="rda-banner-pill-lbl">Students</div>
            </div>
            <div class="rda-banner-pill">
                <div class="rda-banner-pill-val">{{ number_format($applicantCount) }}</div>
                <div class="rda-banner-pill-lbl">Applicants</div>
            </div>
            <div class="rda-banner-pill">
                <div class="rda-banner-pill-val">{{ $passRate }}%</div>
                <div class="rda-banner-pill-lbl">Pass Rate</div>
            </div>
        </div>
    </div>

    {{-- ══ KPI CARDS + QUICK ACCESS ════════════════════ --}}
    <div class="rda-top">

        {{-- KPI Grid (2×3) --}}
        <div class="rda-kpi-grid">

            {{-- Students --}}
            <div class="rda-kpi" style="--kc:#006837;">
                <div class="rda-kpi-icon" style="--kc:#006837;">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="rda-kpi-label">Total Students{{ $activeTermLabel !== '' ? ': ' . $activeTermLabel : '' }}</div>
                <div class="rda-kpi-value">{{ number_format($studentCount) }}</div>
                <div class="rda-kpi-meta">{{ number_format($maleCount) }} Male &bull; {{ number_format($femaleCount) }} Female</div>
                <div class="rda-kpi-tag {{ $trendClass }}">{{ $trendArrow }} {{ $trendText }} vs last month</div>
                <div class="rda-kpi-bar"></div>
            </div>

            {{-- Applicants --}}
            <div class="rda-kpi" style="--kc:#2563eb;">
                <div class="rda-kpi-icon" style="--kc:#2563eb;">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div class="rda-kpi-label">Applicants</div>
                <div class="rda-kpi-value">{{ number_format($applicantCount) }}</div>
                <div class="rda-kpi-meta">Current enrollment period</div>
                <div class="rda-kpi-tag neu">{{ $departmentCount }} Dept{{ $departmentCount !== 1 ? 's' : '' }}</div>
                <div class="rda-kpi-bar" style="--kc:#2563eb;"></div>
            </div>

            {{-- Exam Passed --}}
            <div class="rda-kpi" style="--kc:#16a34a;">
                <div class="rda-kpi-icon" style="--kc:#16a34a;">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="rda-kpi-label">Exam Passed</div>
                <div class="rda-kpi-value">{{ number_format($examPassed) }}</div>
                <div class="rda-kpi-meta">{{ number_format($examFailed) }} failed &bull; {{ number_format($examPending) }} pending</div>
                <div class="rda-kpi-tag up">{{ $passRate }}% pass rate</div>
                <div class="rda-kpi-bar" style="--kc:#16a34a;"></div>
            </div>

            {{-- Graduates --}}
            <div class="rda-kpi" style="--kc:#7c3aed;">
                <div class="rda-kpi-icon" style="--kc:#7c3aed;">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <div class="rda-kpi-label">Graduates Tagged</div>
                <div class="rda-kpi-value">{{ number_format($graduateCount) }}</div>
                <div class="rda-kpi-meta">All-time tagged graduates</div>
                <div class="rda-kpi-bar" style="--kc:#7c3aed;"></div>
            </div>

            {{-- Faculty --}}
            <div class="rda-kpi" style="--kc:#d97706;">
                <div class="rda-kpi-icon" style="--kc:#d97706;">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/><line x1="20" y1="8" x2="20" y2="14"/></svg>
                </div>
                <div class="rda-kpi-label">Faculty</div>
                <div class="rda-kpi-value">{{ number_format($facultyCount) }}</div>
                <div class="rda-kpi-meta">{{ number_format($facultyFullTimeCount) }} FULL-TIME &bull; {{ number_format($facultyPartTimeCount) }} PART-TIME</div>
                <div class="rda-kpi-bar" style="--kc:#d97706;"></div>
            </div>

            {{-- Departments --}}
            <div class="rda-kpi" style="--kc:#0891b2;">
                <div class="rda-kpi-icon" style="--kc:#0891b2;">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                </div>
                <div class="rda-kpi-label">Departments</div>
                <div class="rda-kpi-value">{{ number_format($departmentCount) }}</div>
                <div class="rda-kpi-meta">Active colleges / depts.</div>
                <div class="rda-kpi-bar" style="--kc:#0891b2;"></div>
            </div>

        </div>

        {{-- Quick Access Sidebar --}}
        <div class="rda-quick">
            <div class="rda-quick-hd">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Quick Access
            </div>

            <a href="{{ route('registrar.process.application') }}" class="rda-qa" style="--qc:#dbeafe;">
                <div class="rda-qa-dot" style="--qc:#dbeafe;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="rda-qa-info">
                    <div class="rda-qa-name">Applicant List</div>
                    <div class="rda-qa-sub">View & manage applicants</div>
                </div>
                <svg class="rda-qa-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <a href="{{ route('registrar.process.exam-list') }}" class="rda-qa" style="--qc:#fef9c3;">
                <div class="rda-qa-dot" style="--qc:#fef9c3;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <div class="rda-qa-info">
                    <div class="rda-qa-name">Exam List</div>
                    <div class="rda-qa-sub">Entrance exam results</div>
                </div>
                <svg class="rda-qa-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <a href="{{ route('registrar.process.document-list') }}" class="rda-qa" style="--qc:#dcfce7;">
                <div class="rda-qa-dot" style="--qc:#dcfce7;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                </div>
                <div class="rda-qa-info">
                    <div class="rda-qa-name">Document List</div>
                    <div class="rda-qa-sub">Submitted requirements</div>
                </div>
                <svg class="rda-qa-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <a href="{{ route('registrar.services.reports-admin.tagging-of-graduates') }}" class="rda-qa" style="--qc:#f3e8ff;">
                <div class="rda-qa-dot" style="--qc:#f3e8ff;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <div class="rda-qa-info">
                    <div class="rda-qa-name">Tagging of Graduates</div>
                    <div class="rda-qa-sub">Tag graduating students</div>
                </div>
                <svg class="rda-qa-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <a href="{{ route('registrar.services.grading-academic.scholastic-comments') }}" class="rda-qa" style="--qc:#fce7f3;">
                <div class="rda-qa-dot" style="--qc:#fce7f3;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#db2777" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div class="rda-qa-info">
                    <div class="rda-qa-name">Scholastic Comments</div>
                    <div class="rda-qa-sub">Academic standing remarks</div>
                </div>
                <svg class="rda-qa-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <a href="{{ route('registrar.registrar-menu.student-mgmt.student-enrollment') }}" class="rda-qa" style="--qc:#ffedd5;">
                <div class="rda-qa-dot" style="--qc:#ffedd5;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <div class="rda-qa-info">
                    <div class="rda-qa-name">Student Enrollment</div>
                    <div class="rda-qa-sub">Manage enrolled students</div>
                </div>
                <svg class="rda-qa-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

        </div>
    </div>

    {{-- ══ ENROLLMENT TREND + GENDER ═══════════════════ --}}
    <div class="rda-charts-main">

        <div class="rda-card">
            <div class="rda-card-hd">
                <div class="rda-card-title">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Student Enrollment Per Program
                </div>
                <div class="rda-card-badge">{{ $activeTermLabel !== '' ? $activeTermLabel : 'Per semester' }}</div>
            </div>
            <div class="rda-enroll-chart-wrap" role="img" aria-label="Student enrollment graph by program and semester">
                <canvas id="rdaEnrollProgramChart"></canvas>
            </div>
        </div>

        <div class="rda-card" style="display:flex; flex-direction:column;">
            <div class="rda-card-hd">
                <div class="rda-card-title">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    Gender Breakdown
                </div>
                <div class="rda-card-badge">{{ $activeTermLabel !== '' ? $activeTermLabel : 'Current semester' }}</div>
            </div>
            <div class="rda-donut-wrap" style="flex:1; justify-content:center; padding:10px 0;">
                <div class="rda-sex-chart-box">
                    <canvas id="rdaGenderChart" width="170" height="170"></canvas>
                </div>
                <div class="rda-donut-label">
                    <div class="rda-donut-lv">{{ number_format($studentCount) }}</div>
                    <div class="rda-donut-ls">Total</div>
                </div>
            </div>
            <div class="rda-legend">
                <div class="rda-legend-item">
                    <div class="rda-legend-dot" style="background:#006837;"></div>
                    <span>Male — {{ number_format($maleCount) }}@if($studentCount > 0) ({{ round($maleCount/$studentCount*100,1) }}%)@endif</span>
                </div>
                <div class="rda-legend-item">
                    <div class="rda-legend-dot" style="background:#4ade80;"></div>
                    <span>Female — {{ number_format($femaleCount) }}@if($studentCount > 0) ({{ round($femaleCount/$studentCount*100,1) }}%)@endif</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Announcements --}}
    <div class="rda-charts-bottom">

        {{-- Announcements --}}
        <div class="rda-card" style="display:flex; flex-direction:column;">
            <div class="rda-card-hd">
                <div class="rda-card-title">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
                    Announcements
                </div>
                <div class="rda-card-badge">{{ now()->format('M j, Y') }}</div>
            </div>
            <div class="rda-ann-feed" id="rdaAnnFeed" style="flex:1;">
                @forelse(array_slice($dashAnn, 0, 6) as $ann)
                <div class="rda-ann-row">
                    <div class="rda-ann-dot"></div>
                    <div class="rda-ann-body">
                        <div class="rda-ann-title" title="{{ $ann['title'] }}">{{ $ann['title'] }}</div>
                        <div class="rda-ann-meta">{{ $ann['timeLabel'] }} &bull; {{ $ann['audienceLabel'] }}</div>
                    </div>
                    <button type="button" class="rda-ann-x" title="Dismiss" aria-label="Dismiss">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                @empty
                <div class="rda-ann-empty">No active announcements for staff right now.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = '#64748b';

    var G  = '#006837';
    var G2 = '#4ade80';
    var TOOLTIP = { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8, titleColor: '#94a3b8', bodyColor: '#f1f5f9' };

    /* Enrollment Per Program Bar Graph */
    var enrollPrograms = {!! json_encode(array_values(array_map(function ($row) { return (string) ($row['program'] ?? 'Program'); }, $enrollmentRows))) !!};
    var enrollSemesters = {!! json_encode($enrollmentSemesters) !!};
    var enrollRows = {!! json_encode($enrollmentRows) !!};
    var enrollPalette = ['#006837', '#2563eb', '#d97706', '#7c3aed', '#0891b2'];
    var enrollDatasets = enrollSemesters.map(function (semester, index) {
        return {
            label: semester,
            data: enrollRows.map(function (row) {
                return Number((row.values || {})[semester] || 0);
            }),
            backgroundColor: enrollPalette[index % enrollPalette.length],
            borderRadius: 7,
            borderSkipped: false,
            barPercentage: 0.72,
            categoryPercentage: 0.68
        };
    });

    new Chart(document.getElementById('rdaEnrollProgramChart'), {
        type: 'bar',
        data: {
            labels: enrollPrograms,
            datasets: enrollDatasets
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, pointStyle: 'rectRounded' }
                },
                tooltip: Object.assign({}, TOOLTIP, {
                    callbacks: {
                        label: function (context) {
                            return '  ' + context.dataset.label + ': ' + context.parsed.x + ' students';
                        }
                    }
                })
            },
            scales: {
                x: {
                    grid: { color: '#eef3f0' },
                    beginAtZero: true,
                    ticks: { precision: 0 }
                },
                y: {
                    grid: { display: false },
                    ticks: { autoSkip: false }
                }
            }
        }
    });

    /* Gender Doughnut */
    new Chart(document.getElementById('rdaGenderChart'), {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{ data: [{{ $maleCount }}, {{ $femaleCount }}], backgroundColor: [G, G2], borderWidth: 0, hoverOffset: 6 }]
        },
        options: {
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: Object.assign({}, TOOLTIP, {
                    callbacks: {
                        label: function (c) {
                            var t = c.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                            return '  ' + c.label + ': ' + c.parsed + ' (' + (t > 0 ? Math.round(c.parsed / t * 100) : 0) + '%)';
                        }
                    }
                })
            }
        }
    });

    /* Announcement dismiss */
    var feed = document.getElementById('rdaAnnFeed');
    if (feed) {
        feed.addEventListener('click', function (e) {
            var btn = e.target.closest('.rda-ann-x');
            if (!btn) return;
            var row = btn.closest('.rda-ann-row');
            if (!row) return;
            row.style.transition = 'opacity .2s, transform .2s';
            row.style.opacity    = '0';
            row.style.transform  = 'translateX(12px)';
            setTimeout(function () {
                row.remove();
                if (!feed.querySelector('.rda-ann-row')) {
                    feed.innerHTML = '<div class="rda-ann-empty">All announcements cleared.</div>';
                }
            }, 210);
        });
    }

})();
</script>
@endpush
