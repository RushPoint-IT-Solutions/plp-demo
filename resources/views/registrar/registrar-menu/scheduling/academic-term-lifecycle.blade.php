@extends('layouts.registrar')

@section('title', 'PLP - Academic Term Lifecycle')
@section('page-title', 'ACADEMIC TERM LIFECYCLE')

@section('content')
<div
    class="atl-page"
    id="academicTermLifecyclePage"
    data-close-url="{{ route('registrar.registrar-menu.scheduling.academic-term-lifecycle.close') }}"
    data-open-url="{{ route('registrar.registrar-menu.scheduling.academic-term-lifecycle.open') }}"
    data-publish-url-template="{{ route('registrar.registrar-menu.scheduling.academic-term-lifecycle.publish', ['academicTerm' => '__TERM__']) }}"
    data-csrf="{{ csrf_token() }}"
>
    <style>
        .atl-page { color:#143521; }
        .atl-grid { display:grid; grid-template-columns:minmax(280px, 420px) 1fr; gap:16px; align-items:start; }
        .atl-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:16px; min-width:0; }
        .atl-title { margin:0 0 12px; color:#123822; font-size:1rem; font-weight:800; }
        .atl-form { display:grid; gap:12px; }
        .atl-field label { display:block; margin-bottom:5px; color:#46564a; font-size:.78rem; font-weight:800; text-transform:uppercase; }
        .atl-input, .atl-select { width:100%; min-height:38px; border:1px solid #cfd9d2; border-radius:7px; background:#fff; color:#143521; padding:8px 10px; }
        .atl-current-term { min-height:38px; border:1px solid #cfd9d2; border-radius:7px; background:#f8fbf9; color:#143521; padding:9px 10px; font-weight:800; }
        .atl-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .atl-btn { border:0; border-radius:7px; background:#146c43; color:#fff; cursor:pointer; font-weight:800; min-height:38px; padding:8px 14px; }
        .atl-btn.secondary { background:#eef6f1; color:#146c43; border:1px solid #b9d8c5; }
        .atl-btn.warn { background:#9a6112; }
        .atl-btn:disabled { opacity:.65; cursor:wait; }
        .atl-flow { display:grid; grid-template-columns:repeat(auto-fill, minmax(120px, 1fr)); gap:8px; margin-top:12px; }
        .atl-step { border:1px solid #e4ece7; border-radius:8px; background:#fbfdfb; padding:9px 10px; color:#435448; font-size:.78rem; font-weight:800; min-width:0; box-sizing:border-box; }
        .atl-summary { display:grid; grid-template-columns:repeat(4, minmax(120px, 1fr)); gap:10px; margin-bottom:14px; }
        .atl-stat { border:1px solid #e3ece6; border-radius:8px; padding:10px 12px; background:#fbfdfb; }
        .atl-stat span { display:block; color:#607264; font-size:.72rem; font-weight:800; text-transform:uppercase; }
        .atl-stat strong { display:block; margin-top:3px; color:#123822; font-size:1.25rem; }
        .atl-alert { display:none; margin-bottom:14px; border-radius:8px; padding:10px 12px; font-weight:700; }
        .atl-alert.show { display:block; }
        .atl-alert.success { background:#e8f6ee; color:#17633a; border:1px solid #bfdfcc; }
        .atl-alert.warn { background:#fff5d8; color:#8a5b00; border:1px solid #ead28a; }
        .atl-alert.error { background:#fdecec; color:#9f1d1d; border:1px solid #efb8b8; }
        .atl-muted { color:#66756b; font-size:.82rem; }
        .atl-note { display:block; margin:6px 0 0; color:#66756b; font-size:.78rem; line-height:1.45; }
        .atl-guide { margin:0 0 14px; border:1px solid #d7eadf; border-radius:8px; background:#f3fbf6; padding:10px 12px; color:#315a3f; font-size:.82rem; line-height:1.45; }
        .atl-step small { display:block; margin-top:4px; color:#66756b; font-size:.7rem; font-weight:700; line-height:1.35; }
        .atl-table-wrap { overflow:auto; border:1px solid #e3ece6; border-radius:8px; }
        .atl-page table { width:100%; border-collapse:collapse; min-width:820px; }
        .atl-page th, .atl-page td { padding:10px 12px; border-bottom:1px solid #edf3ef; text-align:left; vertical-align:top; font-size:.86rem; }
        .atl-page th { background:#f5faf7; color:#46564a; font-size:.74rem; font-weight:800; text-transform:uppercase; }
        .atl-status { display:inline-flex; border-radius:999px; padding:4px 8px; background:#eef6f1; color:#146c43; font-size:.75rem; font-weight:800; }
        .atl-list { display:grid; gap:8px; margin-top:10px; }
        .atl-issue { border:1px solid #ead28a; border-radius:8px; background:#fffaf0; padding:10px 12px; }
        .atl-issue strong { display:block; color:#7a4d00; }
        @media (max-width: 1100px) {
            .atl-grid { grid-template-columns:1fr; }
            .atl-summary { grid-template-columns:repeat(2, minmax(120px, 1fr)); }
        }
        @media (max-width: 640px) {
            .atl-summary { grid-template-columns:1fr; }
            .atl-flow { grid-template-columns:repeat(auto-fill, minmax(100px, 1fr)); }
        }
    </style>

    <div class="atl-grid">
        <section class="atl-panel">
            <h2 class="atl-title">Current Semester</h2>
            <div class="atl-guide">
                Use this panel in order: close the active semester, open the next academic term, review any setup issues, then publish only when the report has no pending issues.
            </div>
            <form class="atl-form" id="termLifecycleForm">
                <div class="atl-field">
                    <label>Current Academic Term</label>
                    <input type="hidden" id="atlTerm" name="academic_term_id" value="{{ $currentTerm ? (int) $currentTerm->id : '' }}">
                    <div class="atl-current-term">
                        @if($currentTerm)
                            {{ $currentTerm->school_year }} / {{ $currentTerm->term }} - {{ $currentTerm->status ?? 'Draft' }}
                        @else
                            No academic term configured
                        @endif
                    </div>
                    <div class="atl-note">This is the term that will be closed and used as the source for promotions, section generation, loads, rooms, and schedules.</div>
                </div>
                <div class="atl-field">
                    <label for="atlMaxStudents">Maximum Students per Section</label>
                    <input class="atl-input" id="atlMaxStudents" name="max_students_per_section" type="number" min="1" max="300" value="40">
                    <div class="atl-note">The system uses this limit when splitting promoted students into new sections for the next term.</div>
                </div>
                <label class="atl-muted">
                    <input type="checkbox" name="auto_create_rooms" value="1" checked>
                    Generate missing rooms when a valid room cannot be found.
                    <span class="atl-note">Keep this checked when you want the system to create fallback rooms instead of leaving classes without a room.</span>
                </label>
                <div class="atl-actions">
                    <button type="button" class="atl-btn warn" id="atlCloseBtn">Close Current Semester</button>
                    <button type="button" class="atl-btn" id="atlOpenBtn">Open New Academic Term</button>
                    <button type="button" class="atl-btn secondary" id="atlPublishBtn" disabled>Publish New Term</button>
                </div>
            </form>

            <div class="atl-flow">
                <div class="atl-step">Select Programs Offered This Term<small>Checks which programs and year levels can run in the new term.</small></div>
                <div class="atl-step">Promote Students<small>Moves eligible students forward and flags irregular cases.</small></div>
                <div class="atl-step">Generate Sections<small>Creates sections based on program, year level, and maximum class size.</small></div>
                <div class="atl-step">Generate Class Offerings<small>Builds subject offerings from the active curriculum.</small></div>
                <div class="atl-step">Assign Rooms<small>Matches classes to available rooms or creates fallback rooms if enabled.</small></div>
                <div class="atl-step">Assign Faculty<small>Connects offerings to available faculty records when possible.</small></div>
                <div class="atl-step">Generate Schedule<small>Creates schedule slots while checking basic room and faculty conflicts.</small></div>
                <div class="atl-step">Generate Student Loads<small>Prepares student subject loads for enrollment review.</small></div>
                <div class="atl-step">Validate New Term<small>Lists missing setup items that must be fixed before publishing.</small></div>
                <div class="atl-step">Publish New Term<small>Makes the prepared term available for enrollment workflows.</small></div>
            </div>
        </section>

        <section class="atl-panel">
            <h2 class="atl-title">New Term Opening Report</h2>
            <div class="atl-guide">After opening a term, review the counts and issue list here. A warning means setup records were created but something still needs registrar review before publishing.</div>
            <div class="atl-alert" id="atlAlert"></div>
            <div class="atl-summary" id="atlSummary">
                @foreach(['Programs', 'Not Opened', 'Students', 'Irregular', 'Sections', 'Offerings', 'Rooms', 'Loads'] as $label)
                    <div class="atl-stat"><span>{{ $label }}</span><strong>0</strong></div>
                @endforeach
            </div>
            <div id="atlReport" class="atl-list"></div>
        </section>
    </div>

    <section class="atl-panel" style="margin-top:16px;">
        <h2 class="atl-title">Recent Term Lifecycle Logs</h2>
        <div class="atl-guide">Use these logs to verify what happened in previous term setup runs and to trace unresolved issues by date.</div>
        <div class="atl-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Generated</th>
                        <th>Issues</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->from_school_year }} / {{ $log->from_term_label }}</td>
                            <td>{{ $log->to_school_year }} / {{ $log->to_term_label }}</td>
                            <td>
                                Programs {{ (int) $log->programs_opened_count }},
                                Sections {{ (int) $log->sections_created_count }},
                                Loads {{ (int) $log->student_loads_generated_count }}
                            </td>
                            <td>{{ (int) $log->pending_issue_count }}</td>
                            <td><span class="atl-status">{{ $log->status }}</span></td>
                            <td>{{ optional(\Carbon\Carbon::parse($log->created_at))->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="atl-muted">No lifecycle logs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('academicTermLifecyclePage');
    if (!page) return;

    var form = document.getElementById('termLifecycleForm');
    var termSelect = document.getElementById('atlTerm');
    var alertBox = document.getElementById('atlAlert');
    var summaryBox = document.getElementById('atlSummary');
    var reportBox = document.getElementById('atlReport');
    var closeBtn = document.getElementById('atlCloseBtn');
    var openBtn = document.getElementById('atlOpenBtn');
    var publishBtn = document.getElementById('atlPublishBtn');
    var lastOpenedTermId = null;

    function showAlert(type, message) {
        alertBox.className = 'atl-alert show ' + type;
        alertBox.textContent = message;
    }

    function setBusy(button, busyText) {
        button.dataset.originalText = button.dataset.originalText || button.textContent;
        button.disabled = true;
        button.textContent = busyText;
    }

    function clearBusy(button) {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
    }

    function renderSummary(counts) {
        var rows = [
            ['Programs', counts.programs_opened_count || 0],
            ['Not Opened', counts.programs_not_opened_count || 0],
            ['Students', counts.students_promoted_count || 0],
            ['Irregular', counts.irregular_students_count || 0],
            ['Sections', counts.sections_created_count || 0],
            ['Offerings', counts.class_offerings_generated_count || 0],
            ['Rooms', counts.rooms_assigned_count || 0],
            ['Schedules', counts.schedules_generated_count || 0],
            ['Loads', counts.student_loads_generated_count || 0],
            ['Issues', counts.pending_issue_count || 0]
        ];

        summaryBox.innerHTML = rows.map(function (row) {
            return '<div class="atl-stat"><span>' + row[0] + '</span><strong>' + row[1] + '</strong></div>';
        }).join('');
    }

    function renderReport(report) {
        var html = '';
        if (report.new_term) {
            html += '<div class="atl-issue"><strong>New Academic Term Created</strong>'
                + '<div>' + report.new_term.academic_year + ' / ' + report.new_term.semester + '</div>'
                + '<div class="atl-muted">Status: ' + report.new_term.status + '</div></div>';
        }

        if (report.programs_opened && report.programs_opened.length) {
            html += '<div class="atl-issue"><strong>Programs Opened</strong><div>'
                + report.programs_opened.join(', ') + '</div></div>';
        }

        if (report.programs_not_opened && report.programs_not_opened.length) {
            html += '<div class="atl-issue"><strong>Programs Not Opened</strong>'
                + report.programs_not_opened.map(function (item) {
                    return '<div>' + item.code + ' - ' + item.reason + '</div>';
                }).join('') + '</div>';
        }

        if (report.pending_issues && report.pending_issues.length) {
            html += report.pending_issues.slice(0, 30).map(function (issue) {
                return '<div class="atl-issue"><strong>' + issue.type + '</strong>'
                    + '<div>' + (issue.label || 'N/A') + '</div>'
                    + '<div class="atl-muted">' + issue.description + '</div></div>';
            }).join('');
        } else {
            html += '<div class="atl-alert show success">No pending setup issues found.</div>';
        }

        reportBox.innerHTML = html;
    }

    function post(url, formData) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': page.dataset.csrf,
                'Accept': 'application/json'
            },
            body: formData
        }).then(function (response) {
            return response.json().then(function (payload) {
                if (!response.ok) throw payload;
                return payload;
            });
        });
    }

    closeBtn.addEventListener('click', function () {
        var data = new FormData();
        data.append('academic_term_id', termSelect.value);
        setBusy(closeBtn, 'Closing...');
        showAlert('warn', 'Validating grades, enrollment records, and pending issues...');

        post(page.dataset.closeUrl, data)
            .then(function (payload) {
                showAlert('success', payload.message || 'Semester closed.');
                reportBox.innerHTML = '';
                if (payload.issues && payload.issues.length) renderReport({ pending_issues: payload.issues });
            })
            .catch(function (error) {
                showAlert('error', error.message || 'Semester could not be closed.');
                if (error.issues) renderReport({ pending_issues: error.issues });
            })
            .finally(function () { clearBusy(closeBtn); });
    });

    openBtn.addEventListener('click', function () {
        var data = new FormData(form);
        data.append('current_academic_term_id', termSelect.value);
        setBusy(openBtn, 'Opening...');
        publishBtn.disabled = true;
        showAlert('warn', 'Opening the new term and generating setup records...');

        post(page.dataset.openUrl, data)
            .then(function (payload) {
                lastOpenedTermId = payload.term ? payload.term.id : null;
                renderSummary((payload.report || {}).counts || {});
                renderReport(payload.report || {});
                showAlert((payload.report.counts || {}).pending_issue_count > 0 ? 'warn' : 'success', payload.message || 'New term opened.');
                publishBtn.disabled = !lastOpenedTermId || (payload.report.counts || {}).pending_issue_count > 0;
            })
            .catch(function (error) {
                showAlert('error', error.message || 'New academic term could not be opened.');
                if (error.report) renderReport(error.report);
            })
            .finally(function () { clearBusy(openBtn); });
    });

    publishBtn.addEventListener('click', function () {
        if (!lastOpenedTermId) return;
        setBusy(publishBtn, 'Publishing...');
        var url = page.dataset.publishUrlTemplate.replace('__TERM__', lastOpenedTermId);

        post(url, new FormData())
            .then(function (payload) {
                showAlert('success', payload.message || 'New term published.');
            })
            .catch(function (error) {
                showAlert('error', error.message || 'New term could not be published.');
            })
            .finally(function () { clearBusy(publishBtn); });
    });
});
</script>
@endsection
