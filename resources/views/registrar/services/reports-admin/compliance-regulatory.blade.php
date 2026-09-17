@extends('layouts.registrar')

@section('title', 'PLP - Compliance and Regulatory Reporting')
@section('page-title', 'COMPLIANCE AND REGULATORY REPORTING')

@push('styles')
<style>
    :root {
        --cr-ink: #15352a;
        --cr-muted: #66766f;
        --cr-line: #dce7e1;
        --cr-green: #0f6b46;
        --cr-green-dark: #084c33;
        --cr-mint: #ecf6f0;
        --cr-gold: #d9a62e;
        --cr-navy: #183b5b;
        --cr-bg: #f4f7f5;
    }

    .cr-page { color: var(--cr-ink); font-family: 'Poppins', sans-serif; }
    .cr-hero {
        display: flex; align-items: center; justify-content: space-between; gap: 20px;
        padding: 20px 22px; border-radius: 14px;
        background: linear-gradient(118deg, #07492f 0%, #0e7048 68%, #176f61 100%);
        color: #fff; box-shadow: 0 10px 26px rgba(8, 76, 51, .16); overflow: hidden; position: relative;
    }
    .cr-hero::after {
        content: ''; position: absolute; width: 260px; height: 260px; right: -105px; top: -115px;
        border: 45px solid rgba(255,255,255,.07); border-radius: 50%;
    }
    .cr-kicker { font-size: .7rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #c6ebd8; margin-bottom: 5px; }
    .cr-hero h1 { margin: 0; font-size: 1.35rem; line-height: 1.3; font-weight: 800; }
    .cr-hero p { margin: 5px 0 0; max-width: 670px; color: #e4f3eb; font-size: .78rem; line-height: 1.55; }
    .cr-actions { display: flex; gap: 8px; flex-wrap: wrap; position: relative; z-index: 1; }
    .cr-btn {
        height: 38px; border: 1px solid rgba(255,255,255,.35); border-radius: 8px; padding: 0 14px;
        background: rgba(255,255,255,.11); color: #fff; font-size: .75rem; font-weight: 700;
        display: inline-flex; align-items: center; gap: 7px; cursor: pointer; transition: .18s ease;
    }
    .cr-btn:hover { background: #fff; color: var(--cr-green-dark); }
    .cr-btn-primary { background: #fff; color: var(--cr-green-dark); border-color: #fff; }
    .cr-btn-primary:hover { background: #eff9f3; }

    .cr-notice {
        margin-top: 10px; padding: 9px 13px; border: 1px solid #e9d7a8; border-left: 4px solid var(--cr-gold);
        background: #fffaf0; border-radius: 8px; color: #665322; font-size: .72rem; line-height: 1.45;
        display: flex; align-items: center; gap: 9px;
    }
    .cr-notice i { color: #b98200; font-size: .95rem; }

    .cr-metrics { display: grid; grid-template-columns: 1.3fr repeat(4, 1fr); gap: 10px; margin-top: 10px; }
    .cr-metric {
        min-height: 92px; background: #fff; border: 1px solid var(--cr-line); border-radius: 11px;
        padding: 13px 15px; display: flex; justify-content: space-between; align-items: flex-start;
        box-shadow: 0 4px 15px rgba(27, 60, 45, .045);
    }
    .cr-metric-label { color: var(--cr-muted); font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .045em; }
    .cr-metric-value { margin-top: 4px; font-size: 1.55rem; line-height: 1; font-weight: 800; color: var(--cr-ink); }
    .cr-metric-note { margin-top: 6px; font-size: .64rem; color: #829087; }
    .cr-metric-icon { width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center; font-size: .95rem; }
    .cr-metric.overall { color: #fff; border-color: transparent; background: linear-gradient(140deg, #183b5b, #245b7c); }
    .cr-metric.overall .cr-metric-label, .cr-metric.overall .cr-metric-note { color: #d8e8f2; }
    .cr-metric.overall .cr-metric-value { color: #fff; }
    .cr-metric.overall .cr-metric-icon { background: rgba(255,255,255,.12); color: #fff; }
    .cr-metric.ready .cr-metric-icon { background: #e8f7ef; color: #11834f; }
    .cr-metric.review .cr-metric-icon { background: #edf3fa; color: #326793; }
    .cr-metric.soon .cr-metric-icon { background: #fff5df; color: #b67800; }
    .cr-metric.action .cr-metric-icon { background: #fdeeee; color: #b83a3a; }

    .cr-panel { margin-top: 10px; background: #fff; border: 1px solid var(--cr-line); border-radius: 12px; box-shadow: 0 5px 18px rgba(27, 60, 45, .045); }
    .cr-panel-head { padding: 14px 16px 12px; border-bottom: 1px solid var(--cr-line); display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .cr-panel-title { margin: 0; color: var(--cr-ink); font-size: .95rem; font-weight: 800; }
    .cr-panel-subtitle { margin: 3px 0 0; color: var(--cr-muted); font-size: .68rem; }
    .cr-period { display: flex; align-items: flex-end; gap: 8px; flex-wrap: wrap; }
    .cr-field label { display: block; color: #607067; font-size: .61rem; font-weight: 800; text-transform: uppercase; letter-spacing: .045em; margin-bottom: 4px; }
    .cr-select, .cr-search {
        height: 35px; border: 1px solid #cedbd4; border-radius: 7px; background: #fff; color: #263c32;
        font-size: .72rem; padding: 0 10px; outline: none;
    }
    .cr-select:focus, .cr-search:focus { border-color: #4b9b75; box-shadow: 0 0 0 3px rgba(15,107,70,.09); }
    .cr-period .cr-select { min-width: 125px; }
    .cr-apply { height: 35px; border: 0; border-radius: 7px; padding: 0 15px; background: var(--cr-green); color: #fff; font-size: .7rem; font-weight: 800; }

    .cr-toolbar { padding: 10px 16px; background: #fbfdfc; border-bottom: 1px solid var(--cr-line); display: flex; gap: 8px; align-items: center; }
    .cr-search-wrap { position: relative; flex: 1 1 260px; max-width: 410px; }
    .cr-search-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #718078; }
    .cr-search { width: 100%; padding-left: 33px; }
    .cr-toolbar .cr-select { min-width: 145px; }
    .cr-result-count { margin-left: auto; color: #718078; font-size: .67rem; font-weight: 600; white-space: nowrap; }

    .cr-table-wrap { overflow-x: auto; }
    .cr-table { width: 100%; border-collapse: collapse; min-width: 980px; }
    .cr-table th {
        padding: 9px 11px; background: #f3f7f5; color: #617168; border-bottom: 1px solid #dce7e1;
        font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .045em; text-align: left;
    }
    .cr-table td { padding: 11px; border-bottom: 1px solid #e8efeb; vertical-align: middle; font-size: .69rem; color: #34483e; }
    .cr-table tbody tr.cr-main-row:hover { background: #fbfdfc; }
    .cr-code { font-size: .58rem; color: #708078; font-weight: 700; letter-spacing: .03em; }
    .cr-report-name { font-size: .72rem; line-height: 1.4; color: #18372b; font-weight: 750; margin-top: 2px; }
    .cr-agency { display: inline-flex; min-width: 55px; justify-content: center; border-radius: 5px; padding: 4px 7px; background: #edf3f0; color: #315847; font-size: .61rem; font-weight: 800; }
    .cr-target { white-space: nowrap; font-weight: 700; color: #395247; }
    .cr-progress-line { display: flex; align-items: center; gap: 8px; min-width: 115px; }
    .cr-progress { flex: 1; height: 6px; border-radius: 99px; background: #e7eeea; overflow: hidden; }
    .cr-progress span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #24935f, #66b98a); }
    .cr-progress-value { min-width: 29px; color: #496157; font-size: .63rem; font-weight: 800; }
    .cr-status { display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; padding: 5px 8px; border-radius: 99px; font-size: .6rem; font-weight: 800; }
    .cr-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .cr-status-ready { color: #0d7b48; background: #e8f7ef; }
    .cr-status-review { color: #31668f; background: #edf3fa; }
    .cr-status-soon { color: #9b6700; background: #fff3d9; }
    .cr-status-action { color: #aa3434; background: #fdecec; }
    .cr-review-btn { border: 1px solid #cbdad2; background: #fff; color: #21543e; border-radius: 6px; padding: 6px 9px; font-size: .62rem; font-weight: 800; white-space: nowrap; }
    .cr-review-btn:hover, .cr-review-btn.active { border-color: var(--cr-green); background: var(--cr-mint); }
    .cr-detail-row { display: none; background: #f8fbf9; }
    .cr-detail-row.open { display: table-row; }
    .cr-detail { padding: 13px 18px 15px !important; }
    .cr-detail-grid { display: grid; grid-template-columns: 1fr 1.15fr .65fr; gap: 18px; }
    .cr-detail-title { margin-bottom: 6px; font-size: .61rem; font-weight: 800; color: #53685e; text-transform: uppercase; letter-spacing: .045em; }
    .cr-checks { list-style: none; padding: 0; margin: 0; }
    .cr-checks li { position: relative; padding-left: 17px; margin: 0 0 5px; font-size: .66rem; line-height: 1.45; color: #496057; }
    .cr-checks li::before { content: '\\f26a'; font-family: 'bootstrap-icons'; position: absolute; left: 0; color: #1a8856; }
    .cr-source { color: var(--cr-green); text-decoration: none; font-weight: 750; }
    .cr-source:hover { text-decoration: underline; }
    .cr-empty { display: none; padding: 28px; text-align: center; color: #738079; font-size: .76rem; }
    .cr-panel-foot { padding: 9px 16px; background: #fbfdfc; color: #7a8880; font-size: .61rem; border-radius: 0 0 12px 12px; }

    @media (max-width: 1100px) {
        .cr-metrics { grid-template-columns: repeat(3, 1fr); }
        .cr-metric.overall { grid-column: span 2; }
    }
    @media (max-width: 760px) {
        .cr-hero, .cr-panel-head { flex-direction: column; align-items: stretch; }
        .cr-actions .cr-btn { flex: 1; justify-content: center; }
        .cr-metrics { grid-template-columns: 1fr 1fr; }
        .cr-metric.overall { grid-column: span 2; }
        .cr-toolbar { flex-wrap: wrap; }
        .cr-search-wrap { max-width: none; width: 100%; flex-basis: 100%; }
        .cr-detail-grid { grid-template-columns: 1fr; }
    }
    @media print {
        .sidebar, .topbar, .cr-actions, .cr-toolbar, .cr-review-btn { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .cr-hero { background: #fff !important; color: #111 !important; box-shadow: none; border: 1px solid #bbb; }
        .cr-hero p, .cr-kicker { color: #333 !important; }
        .cr-panel, .cr-metric { box-shadow: none; }
        .cr-detail-row { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="cr-page" id="complianceModule">
    <section class="cr-hero">
        <div>
            <div class="cr-kicker">Institutional Assurance</div>
            <h1>Compliance and Regulatory Reporting</h1>
            <p>Consolidate reporting obligations, validate source-data readiness, assign accountability, and retain evidence before authorized external submission.</p>
        </div>
        <div class="cr-actions">
            <button type="button" class="cr-btn" id="crPrint"><i class="bi bi-printer"></i> Print summary</button>
            <button type="button" class="cr-btn cr-btn-primary" id="crExport"><i class="bi bi-download"></i> Export readiness CSV</button>
        </div>
    </section>

    <div class="cr-notice" role="note">
        <i class="bi bi-shield-exclamation"></i>
        <span><strong>Planning notice:</strong> Dates shown are configurable internal target dates. Confirm the current submission window, template, and filing instructions from the responsible agency before approval or submission.</span>
    </div>

    <section class="cr-metrics" aria-label="Compliance readiness summary">
        <article class="cr-metric overall">
            <div>
                <div class="cr-metric-label">Overall data readiness</div>
                <div class="cr-metric-value">{{ $summary['overall_readiness'] }}%</div>
                <div class="cr-metric-note">Average evidence completeness</div>
            </div>
            <div class="cr-metric-icon"><i class="bi bi-speedometer2"></i></div>
        </article>
        <article class="cr-metric ready">
            <div><div class="cr-metric-label">Ready</div><div class="cr-metric-value">{{ $summary['ready'] }}</div><div class="cr-metric-note">Passed current checks</div></div>
            <div class="cr-metric-icon"><i class="bi bi-check2-circle"></i></div>
        </article>
        <article class="cr-metric review">
            <div><div class="cr-metric-label">For review</div><div class="cr-metric-value">{{ $summary['for_review'] }}</div><div class="cr-metric-note">Awaiting reviewer sign-off</div></div>
            <div class="cr-metric-icon"><i class="bi bi-clipboard-check"></i></div>
        </article>
        <article class="cr-metric soon">
            <div><div class="cr-metric-label">Due soon</div><div class="cr-metric-value">{{ $summary['due_soon'] }}</div><div class="cr-metric-note">Prioritize evidence completion</div></div>
            <div class="cr-metric-icon"><i class="bi bi-clock-history"></i></div>
        </article>
        <article class="cr-metric action">
            <div><div class="cr-metric-label">Action required</div><div class="cr-metric-value">{{ $summary['action_required'] }}</div><div class="cr-metric-note">Blocking validation remains</div></div>
            <div class="cr-metric-icon"><i class="bi bi-exclamation-triangle"></i></div>
        </article>
    </section>

    <section class="cr-panel">
        <div class="cr-panel-head">
            <div>
                <h2 class="cr-panel-title">Reporting obligation register</h2>
                <p class="cr-panel-subtitle">Data readiness for CHED, UniFAST, and National Privacy Commission reporting</p>
            </div>
            <form class="cr-period" method="GET" action="{{ route('registrar.services.reports-admin.compliance-regulatory') }}">
                <div class="cr-field">
                    <label for="crSchoolYear">School year</label>
                    <select class="cr-select" id="crSchoolYear" name="school_year">
                        @foreach($schoolYears as $schoolYear)
                            <option value="{{ $schoolYear }}" {{ $selectedSchoolYear === $schoolYear ? 'selected' : '' }}>{{ $schoolYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="cr-field">
                    <label for="crTerm">Term</label>
                    <select class="cr-select" id="crTerm" name="term">
                        @foreach($termOptions as $term)
                            <option value="{{ $term }}" {{ $selectedTerm === $term ? 'selected' : '' }}>{{ $term }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="cr-apply" type="submit">Apply period</button>
            </form>
        </div>

        <div class="cr-toolbar">
            <div class="cr-search-wrap">
                <i class="bi bi-search"></i>
                <input type="search" class="cr-search" id="crSearch" placeholder="Search report, code, owner, or coverage" aria-label="Search reporting obligations">
            </div>
            <select class="cr-select" id="crAgency" aria-label="Filter by agency">
                <option value="">All agencies</option>
                <option value="CHED">CHED</option>
                <option value="UniFAST">UniFAST</option>
                <option value="NPC">NPC</option>
            </select>
            <select class="cr-select" id="crStatus" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="Ready">Ready</option>
                <option value="For review">For review</option>
                <option value="Due soon">Due soon</option>
                <option value="Action required">Action required</option>
            </select>
            <div class="cr-result-count"><span id="crVisibleCount">{{ count($requirements) }}</span> of {{ count($requirements) }} obligations</div>
        </div>

        <div class="cr-table-wrap">
            <table class="cr-table" id="crTable">
                <thead>
                    <tr>
                        <th style="width:72px;">Agency</th>
                        <th>Requirement</th>
                        <th>Accountable owner</th>
                        <th>Source-data coverage</th>
                        <th>Internal target</th>
                        <th style="width:130px;">Completeness</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requirements as $index => $requirement)
                        @php
                            $statusClass = [
                                'Ready' => 'ready',
                                'For review' => 'review',
                                'Due soon' => 'soon',
                                'Action required' => 'action',
                            ][$requirement['status']] ?? 'review';
                            $searchText = strtolower(implode(' ', [
                                $requirement['agency'], $requirement['code'], $requirement['report'],
                                $requirement['owner'], $requirement['coverage'], $requirement['status']
                            ]));
                        @endphp
                        <tr class="cr-main-row" data-index="{{ $index }}" data-agency="{{ $requirement['agency'] }}" data-status="{{ $requirement['status'] }}" data-search="{{ $searchText }}">
                            <td><span class="cr-agency">{{ $requirement['agency'] }}</span></td>
                            <td><div class="cr-code">{{ $requirement['code'] }}</div><div class="cr-report-name">{{ $requirement['report'] }}</div></td>
                            <td>{{ $requirement['owner'] }}</td>
                            <td>{{ $requirement['coverage'] }}</td>
                            <td class="cr-target">{{ \Carbon\Carbon::parse($requirement['target_date'])->format('d M Y') }}</td>
                            <td>
                                <div class="cr-progress-line"><div class="cr-progress"><span style="width:{{ $requirement['completeness'] }}%"></span></div><div class="cr-progress-value">{{ $requirement['completeness'] }}%</div></div>
                            </td>
                            <td><span class="cr-status cr-status-{{ $statusClass }}">{{ $requirement['status'] }}</span></td>
                            <td><button type="button" class="cr-review-btn" data-detail="crDetail{{ $index }}" aria-expanded="false">Review checks <i class="bi bi-chevron-down"></i></button></td>
                        </tr>
                        <tr class="cr-detail-row" id="crDetail{{ $index }}" data-parent-index="{{ $index }}">
                            <td colspan="8" class="cr-detail">
                                <div class="cr-detail-grid">
                                    <div>
                                        <div class="cr-detail-title">Required evidence</div>
                                        <ul class="cr-checks">@foreach($requirement['evidence'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                                    </div>
                                    <div>
                                        <div class="cr-detail-title">Validation controls</div>
                                        <ul class="cr-checks">@foreach($requirement['validations'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                                    </div>
                                    <div>
                                        <div class="cr-detail-title">Official reference</div>
                                        <a class="cr-source" href="{{ $requirement['reference_url'] }}" target="_blank" rel="noopener noreferrer">Open agency guidance <i class="bi bi-box-arrow-up-right"></i></a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="cr-empty" id="crEmpty"><i class="bi bi-search"></i> No reporting obligation matches the selected filters.</div>
        </div>
        <div class="cr-panel-foot">Readiness scores indicate completeness of locally available evidence only. Approval, authorized submission, and agency acknowledgement remain separate controlled steps.</div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var semesterMap = @json($semesterMap);
    var requirements = @json($requirements);
    var schoolYear = document.getElementById('crSchoolYear');
    var term = document.getElementById('crTerm');
    var search = document.getElementById('crSearch');
    var agency = document.getElementById('crAgency');
    var status = document.getElementById('crStatus');

    schoolYear.addEventListener('change', function () {
        var choices = semesterMap[this.value] || ['First', 'Second', 'Summer'];
        term.innerHTML = '';
        choices.forEach(function (choice) {
            var option = document.createElement('option');
            option.value = choice;
            option.textContent = choice;
            term.appendChild(option);
        });
    });

    function applyFilters() {
        var query = (search.value || '').trim().toLowerCase();
        var agencyValue = agency.value;
        var statusValue = status.value;
        var visible = 0;

        document.querySelectorAll('.cr-main-row').forEach(function (row) {
            var match = (!query || row.dataset.search.indexOf(query) !== -1)
                && (!agencyValue || row.dataset.agency === agencyValue)
                && (!statusValue || row.dataset.status === statusValue);
            row.style.display = match ? '' : 'none';
            var detail = document.querySelector('[data-parent-index="' + row.dataset.index + '"]');
            if (!match && detail) {
                detail.classList.remove('open');
            }
            if (match) visible += 1;
        });

        document.getElementById('crVisibleCount').textContent = visible;
        document.getElementById('crEmpty').style.display = visible ? 'none' : 'block';
    }

    [search, agency, status].forEach(function (control) {
        control.addEventListener(control === search ? 'input' : 'change', applyFilters);
    });

    document.querySelectorAll('.cr-review-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var detail = document.getElementById(this.dataset.detail);
            var open = detail.classList.toggle('open');
            this.classList.toggle('active', open);
            this.setAttribute('aria-expanded', open ? 'true' : 'false');
            this.querySelector('i').className = open ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
        });
    });

    document.getElementById('crPrint').addEventListener('click', function () { window.print(); });
    document.getElementById('crExport').addEventListener('click', function () {
        var rows = [['Code', 'Agency', 'Requirement', 'Owner', 'Coverage', 'Internal Target Date', 'Completeness', 'Status']];
        requirements.forEach(function (item, index) {
            var row = document.querySelector('.cr-main-row[data-index="' + index + '"]');
            if (row && row.style.display !== 'none') {
                rows.push([item.code, item.agency, item.report, item.owner, item.coverage, item.target_date, item.completeness + '%', item.status]);
            }
        });
        var csv = rows.map(function (row) {
            return row.map(function (value) { return '"' + String(value).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\r\n');
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'PLP-compliance-readiness-{{ $selectedSchoolYear }}-{{ strtolower($selectedTerm) }}.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
    });
})();
</script>
@endpush
