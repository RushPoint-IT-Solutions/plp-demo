@extends('layouts.registrar')

@section('title', 'PLP - Teacher Generation and Assignment')
@section('page-title', 'TEACHER GENERATION AND ASSIGNMENT')

@section('content')
<div
    class="tga-page"
    id="teacherGenerationAssignmentPage"
    data-generate-url="{{ route('registrar.registrar-menu.scheduling.teacher-generation-assignment.generate') }}"
    data-report-url="{{ route('registrar.registrar-menu.scheduling.teacher-generation-assignment.report') }}"
    data-csrf="{{ csrf_token() }}"
>
    <style>
        .tga-page { color:#143521; }
        .tga-grid { display:grid; grid-template-columns:minmax(280px, 410px) 1fr; gap:16px; align-items:start; }
        .tga-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:16px; }
        .tga-title { margin:0 0 12px; color:#123822; font-size:1rem; font-weight:800; }
        .tga-form { display:grid; gap:12px; }
        .tga-field label { display:block; margin-bottom:5px; color:#46564a; font-size:.78rem; font-weight:800; text-transform:uppercase; }
        .tga-select { width:100%; min-height:38px; border:1px solid #cfd9d2; border-radius:7px; background:#fff; color:#143521; padding:8px 10px; }
        .tga-actions { display:flex; flex-wrap:wrap; gap:8px; }
        .tga-btn { border:0; border-radius:7px; background:#146c43; color:#fff; cursor:pointer; font-weight:800; min-height:38px; padding:8px 14px; }
        .tga-btn.secondary { background:#eef6f1; color:#146c43; border:1px solid #b9d8c5; }
        .tga-btn:disabled { cursor:wait; opacity:.65; }
        .tga-alert { display:none; margin-bottom:14px; border-radius:8px; padding:10px 12px; font-weight:700; }
        .tga-alert.show { display:block; }
        .tga-alert.success { background:#e8f6ee; color:#17633a; border:1px solid #bfdfcc; }
        .tga-alert.warn { background:#fff5d8; color:#8a5b00; border:1px solid #ead28a; }
        .tga-alert.error { background:#fdecec; color:#9f1d1d; border:1px solid #efb8b8; }
        .tga-summary { display:grid; grid-template-columns:repeat(3, minmax(120px, 1fr)); gap:10px; margin-bottom:14px; }
        .tga-stat { border:1px solid #e3ece6; border-radius:8px; padding:10px 12px; background:#fbfdfb; }
        .tga-stat span { display:block; color:#607264; font-size:.72rem; font-weight:800; text-transform:uppercase; }
        .tga-stat strong { display:block; margin-top:3px; color:#123822; font-size:1.25rem; }
        .tga-note { display:block; margin:6px 0 0; color:#66756b; font-size:.78rem; line-height:1.45; }
        .tga-table-wrap { overflow:auto; border:1px solid #e3ece6; border-radius:8px; }
        .tga-table { width:100%; min-width:980px; border-collapse:collapse; }
        .tga-table th, .tga-table td { padding:10px 12px; border-bottom:1px solid #edf3ef; text-align:left; vertical-align:top; font-size:.86rem; }
        .tga-table th { background:#f5faf7; color:#46564a; font-size:.74rem; font-weight:800; text-transform:uppercase; }
        .tga-table tr:last-child td { border-bottom:0; }
        .tga-muted { color:#66756b; font-size:.82rem; }
        .tga-status { display:inline-flex; border-radius:999px; padding:4px 8px; background:#eef6f1; color:#146c43; font-size:.75rem; font-weight:800; }
        .tga-status.pending { background:#fff5d8; color:#8a5b00; }
        @media (max-width: 1100px) {
            .tga-grid { grid-template-columns:1fr; }
            .tga-summary { grid-template-columns:1fr; }
        }
    </style>

    <div class="tga-grid">
        <section class="tga-panel">
            <h2 class="tga-title">Teacher Assignment Filters</h2>
            <div class="tga-note" style="margin-bottom:12px;">Run this after room generation. Only class offerings with enrolled students, assigned room, day, and time will receive a teacher.</div>
            <form class="tga-form" id="tgaForm">
                <div class="tga-field">
                    <label for="tgaSchoolYear">Academic Year</label>
                    <select class="tga-select" id="tgaSchoolYear" name="school_year" required>
                        @foreach($schoolYearOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tga-field">
                    <label for="tgaSemester">Semester</label>
                    <select class="tga-select" id="tgaSemester" name="semester" required>
                        @foreach($semesterOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tga-field">
                    <label for="tgaProgram">Program</label>
                    <select class="tga-select" id="tgaProgram" name="course_id">
                        <option value="">All Programs</option>
                        @foreach($programOptions as $program)
                            <option value="{{ $program['id'] }}">{{ $program['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tga-field">
                    <label for="tgaYearLevel">Year Level</label>
                    <select class="tga-select" id="tgaYearLevel" name="year_block_id">
                        <option value="">All Year Levels</option>
                        @foreach($yearLevelOptions as $yearLevel)
                            <option value="{{ $yearLevel['id'] }}">{{ $yearLevel['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tga-field">
                    <label for="tgaSection">Section</label>
                    <select class="tga-select" id="tgaSection" name="section">
                        <option value="">All Sections</option>
                        @foreach($sectionOptions as $section)
                            <option value="{{ $section }}">{{ $section }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tga-actions">
                    <button type="submit" class="tga-btn" id="tgaGenerate">Generate Teachers</button>
                    <button type="button" class="tga-btn secondary" id="tgaGeneratePending">Generate Pending Only</button>
                    <button type="button" class="tga-btn secondary" id="tgaLoadReport">Load Report</button>
                </div>
            </form>
        </section>

        <section class="tga-panel">
            <h2 class="tga-title">Teacher Assignment Report</h2>
            <div class="tga-alert" id="tgaAlert"></div>
            <div class="tga-summary" id="tgaSummary">
                <div class="tga-stat"><span>Assigned</span><strong>0</strong></div>
                <div class="tga-stat"><span>Pending</span><strong>0</strong></div>
                <div class="tga-stat"><span>Existing</span><strong>0</strong></div>
            </div>
            <div class="tga-table-wrap">
                <table class="tga-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Program</th>
                            <th>Section</th>
                            <th>Students</th>
                            <th>Room</th>
                            <th>Schedule</th>
                            <th>Teacher</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="tgaRows">
                        <tr><td colspan="9" class="tga-muted">Generate or load teacher assignments to view the report.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('teacherGenerationAssignmentPage');
    if (!page) return;

    var form = document.getElementById('tgaForm');
    var rows = document.getElementById('tgaRows');
    var alertBox = document.getElementById('tgaAlert');
    var summary = document.getElementById('tgaSummary');
    var generateBtn = document.getElementById('tgaGenerate');
    var pendingBtn = document.getElementById('tgaGeneratePending');
    var reportBtn = document.getElementById('tgaLoadReport');

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'})[char];
        });
    }

    function showAlert(type, message) {
        alertBox.className = 'tga-alert show ' + type;
        alertBox.textContent = message;
    }

    function setSummary(data) {
        data = data || {};
        summary.innerHTML = [
            ['Assigned', data.assigned || 0],
            ['Pending', data.pending || 0],
            ['Existing', data.existing || 0]
        ].map(function (item) {
            return '<div class="tga-stat"><span>' + item[0] + '</span><strong>' + item[1] + '</strong></div>';
        }).join('');
    }

    function renderRows(items) {
        if (!items || !items.length) {
            rows.innerHTML = '<tr><td colspan="9" class="tga-muted">No teacher assignment rows found.</td></tr>';
            return;
        }

        rows.innerHTML = items.map(function (item) {
            var statusClass = /pending/i.test(item.assignment_status || '') ? ' pending' : '';
            var program = [item.program_code, item.program_name].filter(Boolean).join(' - ');
            return '<tr>'
                + '<td><strong>' + escapeHtml(item.subject_code) + '</strong><div class="tga-muted">' + escapeHtml(item.subject_name) + '</div></td>'
                + '<td>' + escapeHtml(program || '-') + '</td>'
                + '<td>' + escapeHtml(item.section) + '</td>'
                + '<td>' + escapeHtml(item.student_count || 0) + '</td>'
                + '<td>' + escapeHtml(item.room || 'TBA') + '</td>'
                + '<td>' + escapeHtml(item.days) + ' ' + escapeHtml(item.time_start) + '-' + escapeHtml(item.time_end) + '</td>'
                + '<td>' + escapeHtml(item.faculty_name || 'TBA') + '</td>'
                + '<td><span class="tga-status' + statusClass + '">' + escapeHtml(item.assignment_status) + '</span></td>'
                + '<td>' + escapeHtml(item.remarks) + '</td>'
                + '</tr>';
        }).join('');
    }

    function requestJson(url, options) {
        options = options || {};
        options.headers = Object.assign({
            'X-CSRF-TOKEN': page.dataset.csrf,
            'Accept': 'application/json'
        }, options.headers || {});

        return fetch(url, options).then(function (response) {
            return response.json().then(function (payload) {
                if (!response.ok) throw payload;
                return payload;
            });
        });
    }

    function loadReport() {
        var params = new URLSearchParams(new FormData(form));
        showAlert('warn', 'Loading teacher assignment report...');
        return requestJson(page.dataset.reportUrl + '?' + params.toString(), { method: 'GET' })
            .then(function (payload) {
                renderRows(payload.rows || []);
                var reportSummary = (payload.rows || []).reduce(function (carry, item) {
                    if (/pending/i.test(item.assignment_status || '')) carry.pending++;
                    else if (/assigned/i.test(item.assignment_status || '')) carry.assigned++;
                    return carry;
                }, {assigned: 0, pending: 0, existing: 0});
                setSummary(reportSummary);
                showAlert('success', 'Teacher assignment report loaded.');
            })
            .catch(function (payload) {
                showAlert('error', payload.message || 'Unable to load teacher assignment report.');
            });
    }

    function generateTeachers(onlyPending) {
        var body = new FormData(form);
        if (onlyPending) body.append('only_pending', '1');
        generateBtn.disabled = true;
        pendingBtn.disabled = true;
        showAlert('warn', 'Generating teacher assignments...');

        requestJson(page.dataset.generateUrl, { method: 'POST', body: body })
            .then(function (payload) {
                setSummary(payload.summary || {});
                renderRows((payload.assigned || []).concat(payload.issues || []));
                showAlert('success', payload.message || 'Teacher assignment completed.');
            })
            .catch(function (payload) {
                showAlert('error', payload.message || 'Unable to generate teacher assignments.');
            })
            .finally(function () {
                generateBtn.disabled = false;
                pendingBtn.disabled = false;
            });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        generateTeachers(false);
    });

    pendingBtn.addEventListener('click', function () {
        generateTeachers(true);
    });

    reportBtn.addEventListener('click', loadReport);
});
</script>
@endsection
