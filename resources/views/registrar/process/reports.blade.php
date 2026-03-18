@extends('layouts.registrar')

@section('title', 'PLP - Reports')
@section('page-title', 'REPORTS')
@section('body-class', 'page-reports')

@section('content')
@php
    $reportData = $reportData ?? [];
    $totalApplicants = (int) ($reportData['totalApplicants'] ?? 250);
    $students4thYear = (int) ($reportData['students4thYear'] ?? $totalApplicants);
    $verifiedCount = (int) ($reportData['verifiedCount'] ?? 98);
    $incompleteCount = (int) ($reportData['incompleteCount'] ?? 98);
    $approvedCount = (int) ($reportData['approvedCount'] ?? 68);
    $approvalRate = (float) ($reportData['approvalRate'] ?? 27.2);
    $approvalRateText = rtrim(rtrim(number_format($approvalRate, 1), '0'), '.') . '%';
    $sectionOptions = $reportData['sectionOptions'] ?? ['BSCS 1-C', 'BSCS 4-B', 'BSIT 4-A'];
    $reportRows = $reportData['rows'] ?? [];

    if (!count($reportRows)) {
        $reportRows = [
            ['name' => 'Elias Bartolome', 'section' => 'BSCS 1-C', 'missing_items' => ['Form 137', 'PSA'], 'last_updated' => 'Jan 1, 2026'],
            ['name' => 'Juan Dela Cruz', 'section' => 'BSCS 4-B', 'missing_items' => ['OJT Certificate'], 'last_updated' => 'Jan 1, 2026'],
            ['name' => 'Gabriel Villanueva', 'section' => 'BSCS 1-C', 'missing_items' => ['Form 137', 'PSA'], 'last_updated' => 'Jan 1, 2026'],
            ['name' => 'Andrea Jane Austero', 'section' => 'BSCS 4-B', 'missing_items' => ['Library Clearance', 'Graduation Application'], 'last_updated' => 'Jan 1, 2026'],
            ['name' => 'Maria Clara Santos', 'section' => 'BSCS 4-B', 'missing_items' => ['Form 137', 'PSA'], 'last_updated' => 'Jan 1, 2026'],
        ];
    }
@endphp
<div class="pf-page">
    <div class="rp-page">
        <div class="rp-stats-grid">
            <div class="rp-stat-card rp-stat-card-main">
                <div class="rp-stat-title">Total Applicants</div>
                <div class="rp-stat-subtitle">Total Students (4th Year)</div>
                <div class="rp-stat-main-row">
                    <div class="rp-stat-number">{{ $totalApplicants }}</div>
                    <div class="rp-mini-spark">
                        <span class="rp-mini-growth">+2%</span>
                        <svg viewBox="0 0 120 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 44 C16 38, 22 32, 34 28 C46 24, 56 18, 70 16 C84 14, 95 18, 118 8" stroke="#0d8a4a" stroke-width="2.4" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <div class="rp-stat-foot">
                    <span>Needs follow-up</span>
                    <span class="rp-stat-badge">+2% Past month</span>
                </div>
            </div>

            <div class="rp-stat-card rp-stat-card-verified">
                <div class="rp-stat-label-sm">Verified</div>
                <div class="rp-stat-sub-label">Fully Verified</div>
                <div class="rp-stat-number">{{ $verifiedCount }}</div>
                <div class="rp-stat-note">Ready for Final Approval</div>
            </div>

            <div class="rp-stat-card rp-stat-card-incomplete">
                <div class="rp-stat-label-sm">Incomplete</div>
                <div class="rp-stat-sub-label">Lacking Requirements</div>
                <div class="rp-stat-number">{{ $incompleteCount }}</div>
                <div class="rp-stat-note">Needs Follow-Up</div>
            </div>

            <div class="rp-stat-card rp-stat-card-approval">
                <div class="rp-stat-title">Approval Rate</div>
                <div class="rp-stat-subtitle">Final Approval Progress</div>
                <div class="rp-stat-number">{{ $approvalRateText }}</div>
                <div class="rp-stat-note">{{ $approvedCount }} of {{ $students4thYear }} students officially approved by Dean</div>
            </div>
        </div>

        <div class="rp-controls-row">
            <div class="app-filter-group rp-filter-group">
                <span class="app-filter-label">Filter By:</span>
                <select class="app-filter-select" id="rpSectionFilter">
                    <option value="">Section</option>
                    @foreach($sectionOptions as $sectionOption)
                        <option value="{{ $sectionOption }}">{{ $sectionOption }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="pf-btn-new rp-export-btn" id="rpExportBtn">Export</button>
        </div>

        <div class="rp-main-grid">
            <div class="rp-panel rp-table-panel">
                <div class="rp-table-wrap">
                    <table class="rp-table" id="rpStudentsTable">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Section</th>
                                <th>Missing Items</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportRows as $reportRow)
                                <tr>
                                    <td>{{ $reportRow['name'] ?? 'Unknown Student' }}</td>
                                    <td>{{ $reportRow['section'] ?? 'N/A' }}</td>
                                    <td>{{ is_array($reportRow['missing_items'] ?? null) ? implode(', ', $reportRow['missing_items']) : ($reportRow['missing_items'] ?? '-') }}</td>
                                    <td>{{ $reportRow['last_updated'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rp-panel rp-chart-panel">
                <div class="rp-chart-title">Most Common Missing Requirement</div>
                <div id="rpMissingReqChart" class="rp-missing-chart" aria-label="Most common missing requirements chart"></div>
            </div>
        </div>

        <div class="rp-panel rp-timeline-panel">
            <div class="rp-timeline-head">
                <div class="rp-chart-title">Approval Timeline</div>
                <select id="rpTimelineRange" class="app-filter-select" aria-label="Approval timeline range">
                    <option value="monthly" selected>Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            <div class="rp-approval-card">
                <div id="rpApprovalChart" class="rp-approval-chart" aria-label="Approval timeline area chart"></div>
            </div>
        </div>

        <div class="rp-export-overlay" id="rpExportOverlay" aria-hidden="true" style="display:none;">
            <div class="rp-export-modal" role="dialog" aria-modal="true" aria-labelledby="rpExportTitle">
                <div class="rp-export-title" id="rpExportTitle">EXPORTING</div>
                <div class="rp-export-track">
                    <div class="rp-export-progress" id="rpExportProgress"></div>
                </div>
            </div>
        </div>

        <div class="rp-toast" id="rpToast" style="display:none;">File Downloaded Successfully.</div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var exportBtn = document.getElementById('rpExportBtn');
    var overlay = document.getElementById('rpExportOverlay');
    var progress = document.getElementById('rpExportProgress');
    var toast = document.getElementById('rpToast');
    var backendReportData = @json($reportData);

    function getTimelineModeData(mode) {
        var timelineModes = backendReportData.approvalTimeline && typeof backendReportData.approvalTimeline === 'object'
            ? backendReportData.approvalTimeline
            : {};

        var preferredMode = (mode === 'yearly') ? 'yearly' : 'monthly';
        var modeData = timelineModes[preferredMode] || {};
        var categories = Array.isArray(modeData.categories) ? modeData.categories : [];
        var series = Array.isArray(modeData.series) ? modeData.series : [];
        var markerCategory = modeData.markerCategory || null;

        if (!categories.length || !series.length) {
            categories = Array.isArray(backendReportData.timelineCategories) && backendReportData.timelineCategories.length
                ? backendReportData.timelineCategories
                : ['Apr 2025', 'May 2025', 'Jun 2025', 'Jul 2025', 'Aug 2025', 'Sep 2025', 'Oct 2025', 'Nov 2025', 'Dec 2025', 'Jan 2026', 'Feb 2026', 'Mar 2026'];
            series = Array.isArray(backendReportData.timelineSeries) && backendReportData.timelineSeries.length
                ? backendReportData.timelineSeries
                : [22, 28, 30, 34, 39, 43, 49, 54, 58, 64, 70, 76];
            markerCategory = backendReportData.timelineMarkerCategory || categories[Math.max(categories.length - 2, 0)] || categories[0];
        }

        return {
            categories: categories,
            series: series,
            markerCategory: markerCategory || categories[Math.max(categories.length - 2, 0)] || categories[0],
            mode: preferredMode
        };
    }

    function renderApprovalTimelineChart(mode) {
        var chartEl = document.getElementById('rpApprovalChart');
        if (!chartEl || typeof ApexCharts === 'undefined') return;

        chartEl.innerHTML = '';

        var timelineData = getTimelineModeData(mode);
        var timelineCategories = timelineData.categories;
        var timelineSeries = timelineData.series;
        var markerCategory = timelineData.markerCategory;
        var maxSeriesValue = Math.max.apply(null, timelineSeries);
        var yAxisMax = Math.max(20, Math.ceil(maxSeriesValue / 10) * 10);
        var yTickAmount = timelineData.mode === 'yearly' ? 4 : 5;

        var options = {
            chart: {
                type: 'area',
                height: 280,
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 450 },
                foreColor: '#6b7280',
                background: 'transparent'
            },
            series: [{
                name: 'Activity',
                data: timelineSeries
            }],
            colors: ['#0f7b43'],
            stroke: {
                curve: 'smooth',
                width: 2,
                lineCap: 'round'
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    shadeIntensity: 0.15,
                    opacityFrom: 0.28,
                    opacityTo: 0.04,
                    stops: [0, 92, 100]
                }
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 0,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { left: 8, right: 18, top: 8, bottom: 0 }
            },
            markers: {
                size: 0,
                hover: { size: 4 }
            },
            xaxis: {
                categories: timelineCategories,
                tickPlacement: 'on',
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#6b7280', fontSize: '11px' }
                }
            },
            yaxis: {
                min: 0,
                max: yAxisMax,
                tickAmount: yTickAmount,
                labels: {
                    formatter: function (val) { return Math.round(val); },
                    style: { colors: '#6b7280', fontSize: '12px' }
                }
            },
            tooltip: {
                theme: 'light',
                x: { show: true },
                y: {
                    formatter: function (val) {
                        return Math.round(val);
                    }
                },
                marker: { show: true }
            },
            annotations: {
                xaxis: [{
                    x: markerCategory,
                    borderColor: 'rgba(15,123,67,0.35)',
                    strokeDashArray: 0,
                    opacity: 1
                }]
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                labels: { colors: '#4b5563' },
                markers: { fillColors: ['#0f7b43'] }
            }
        };

        var chart = new ApexCharts(chartEl, options);
        chart.render();
    }

    function renderMissingRequirementsChart() {
        var chartEl = document.getElementById('rpMissingReqChart');
        if (!chartEl || typeof ApexCharts === 'undefined') return;

        chartEl.innerHTML = '';

        var categories = Array.isArray(backendReportData.missingRequirementLabels) && backendReportData.missingRequirementLabels.length
            ? backendReportData.missingRequirementLabels
            : ['Form 137', 'PSA', 'OJT Certificate', 'Library Clearance', 'Graduation Application', 'Medical Certificate', 'Good Moral Certificate', 'TOR'];
        var values = Array.isArray(backendReportData.missingRequirementValues) && backendReportData.missingRequirementValues.length
            ? backendReportData.missingRequirementValues
            : [76, 69, 63, 58, 49, 43, 37, 31];
        var xAxisMax = Math.max(10, Math.ceil((Math.max.apply(null, values) || 10) / 10) * 10);
        var tickAmount = Math.max(2, Math.min(8, Math.round(xAxisMax / 10)));

        var options = {
            chart: {
                type: 'bar',
                height: '100%',
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 420 },
                background: 'transparent',
                offsetX: 4
            },
            series: [{
                name: 'Students',
                data: values
            }],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 5,
                    barHeight: '50%'
                }
            },
            colors: ['#3f7fde'],
            dataLabels: {
                enabled: false,
                style: { fontSize: '10px', fontWeight: 700 },
                formatter: function (val) {
                    return Math.round(val);
                },
                offsetX: 4
            },
            xaxis: {
                categories: categories,
                max: xAxisMax,
                tickAmount: tickAmount,
                labels: {
                    style: { colors: '#9aa5b4', fontSize: '11px' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
                title: {
                    text: 'Number of Students',
                    style: { color: '#9ca3af', fontSize: '11px', fontWeight: 600 }
                }
            },
            yaxis: {
                labels: {
                    maxWidth: 160,
                    style: { colors: '#4b5563', fontSize: '11px', fontWeight: 600 }
                }
            },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 2,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
                padding: { left: 14, right: 18 }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return Math.round(val) + ' students';
                    }
                }
            },
            legend: { show: false }
        };

        var chart = new ApexCharts(chartEl, options);
        chart.render();
    }

    renderMissingRequirementsChart();

    var timelineRangeSelect = document.getElementById('rpTimelineRange');
    var initialTimelineMode = timelineRangeSelect ? timelineRangeSelect.value : 'monthly';
    renderApprovalTimelineChart(initialTimelineMode);

    if (timelineRangeSelect) {
        timelineRangeSelect.addEventListener('change', function () {
            renderApprovalTimelineChart(timelineRangeSelect.value || 'monthly');
        });
    }

    if (!exportBtn || !overlay || !progress || !toast) return;

    function showToast(message) {
        toast.textContent = message;
        toast.style.display = 'block';
        toast.classList.add('show');
        window.setTimeout(function () {
            toast.classList.remove('show');
            toast.style.display = 'none';
        }, 2600);
    }

    exportBtn.addEventListener('click', function () {
        exportBtn.disabled = true;
        overlay.style.display = 'flex';
        overlay.setAttribute('aria-hidden', 'false');
        progress.style.width = '0%';

        var pct = 0;
        var timer = window.setInterval(function () {
            pct += 4;
            progress.style.width = Math.min(pct, 100) + '%';
            if (pct >= 100) {
                window.clearInterval(timer);
                window.setTimeout(function () {
                    overlay.style.display = 'none';
                    overlay.setAttribute('aria-hidden', 'true');
                    exportBtn.disabled = false;
                    showToast('File Downloaded Successfully.');
                }, 220);
            }
        }, 36);
    });
});
</script>
@endpush
