@extends('layouts.registrar')

@section('title', 'PLP - Academic Setup Automation')
@section('page-title', 'ACADEMIC SETUP AUTOMATION')

@section('content')
<div
    class="asa-page"
    id="academicSetupAutomationPage"
    data-generate-url="{{ route('registrar.registrar-menu.scheduling.academic-setup-automation.generate') }}"
    data-preview-url="{{ route('registrar.registrar-menu.scheduling.academic-setup-automation.movement-preview') }}"
    data-publish-url-template="{{ route('registrar.registrar-menu.scheduling.academic-setup-automation.publish', ['generationLog' => '__LOG__']) }}"
    data-csrf="{{ csrf_token() }}"
>
    <style>
        .asa-page { color:#143521; }
        .asa-page .asa-grid { display:grid; grid-template-columns:minmax(260px, 420px) 1fr; gap:16px; align-items:start; }
        .asa-page .asa-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:16px; }
        .asa-page .asa-panel-title { margin:0 0 12px; color:#123822; font-size:1rem; font-weight:800; }
        .asa-page .asa-form { display:grid; gap:12px; }
        .asa-page .asa-field label { display:block; margin-bottom:5px; color:#46564a; font-size:.78rem; font-weight:800; text-transform:uppercase; }
        .asa-page .asa-input, .asa-page .asa-select { width:100%; min-height:38px; border:1px solid #cfd9d2; border-radius:7px; background:#fff; color:#143521; padding:8px 10px; }
        .asa-page .asa-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .asa-page .asa-btn { border:0; border-radius:7px; background:#146c43; color:#fff; cursor:pointer; font-weight:800; min-height:38px; padding:8px 14px; }
        .asa-page .asa-btn.secondary { background:#eef6f1; color:#146c43; border:1px solid #b9d8c5; }
        .asa-page .asa-btn:disabled { cursor:wait; opacity:.65; }
        .asa-page .asa-summary { display:grid; grid-template-columns:repeat(4, minmax(120px, 1fr)); gap:10px; margin-bottom:14px; }
        .asa-page .asa-stat { border:1px solid #e3ece6; border-radius:8px; padding:10px 12px; background:#fbfdfb; }
        .asa-page .asa-stat span { display:block; color:#607264; font-size:.72rem; font-weight:800; text-transform:uppercase; }
        .asa-page .asa-stat strong { display:block; margin-top:3px; color:#123822; font-size:1.25rem; }
        .asa-page .asa-details { display:grid; gap:12px; margin:0 0 14px; }
        .asa-page .asa-detail-grid { display:grid; grid-template-columns:repeat(2, minmax(180px, 1fr)); gap:10px; }
        .asa-page .asa-detail-card { border:1px solid #e3ece6; border-radius:8px; background:#fbfdfb; padding:10px 12px; }
        .asa-page .asa-detail-card strong { display:block; color:#123822; font-size:.84rem; }
        .asa-page .asa-detail-card span { display:block; margin-top:3px; color:#66756b; font-size:.78rem; line-height:1.35; }
        .asa-page .asa-action-list { margin:0; padding-left:18px; color:#315a3f; font-size:.82rem; line-height:1.5; }
        .asa-page .asa-alert { display:none; margin-bottom:14px; border-radius:8px; padding:10px 12px; font-weight:700; }
        .asa-page .asa-alert.show { display:block; }
        .asa-page .asa-alert.success { background:#e8f6ee; color:#17633a; border:1px solid #bfdfcc; }
        .asa-page .asa-alert.warn { background:#fff5d8; color:#8a5b00; border:1px solid #ead28a; }
        .asa-page .asa-alert.error { background:#fdecec; color:#9f1d1d; border:1px solid #efb8b8; }
        .asa-page .asa-table-wrap { overflow:auto; border:1px solid #e3ece6; border-radius:8px; }
        .asa-page table { width:100%; border-collapse:collapse; min-width:760px; }
        .asa-page th, .asa-page td { padding:10px 12px; border-bottom:1px solid #edf3ef; text-align:left; vertical-align:top; font-size:.86rem; }
        .asa-page th { background:#f5faf7; color:#46564a; font-size:.74rem; font-weight:800; text-transform:uppercase; }
        .asa-page tr:last-child td { border-bottom:0; }
        .asa-page .asa-muted { color:#66756b; font-size:.82rem; }
        .asa-page .asa-note { display:block; margin:6px 0 0; color:#66756b; font-size:.78rem; line-height:1.45; }
        .asa-page .asa-guide { margin:0 0 14px; border:1px solid #d7eadf; border-radius:8px; background:#f3fbf6; padding:10px 12px; color:#315a3f; font-size:.82rem; line-height:1.45; }
        .asa-page .asa-status { display:inline-flex; border-radius:999px; padding:4px 8px; background:#eef6f1; color:#146c43; font-size:.75rem; font-weight:800; }
        .asa-page .asa-status.warn { background:#fff5d8; color:#8a5b00; }
        .asa-page .asa-issues { margin-top:14px; }
        .asa-page .asa-issue { border:1px solid #ead28a; border-radius:8px; background:#fffaf0; padding:10px 12px; margin-bottom:8px; }
        .asa-page .asa-issue strong { display:block; color:#7a4d00; }
        .asa-page .asa-divider { height:1px; margin:16px 0; background:#e3ece6; }
        .asa-page .asa-mini-title { margin:14px 0 8px; font-size:.86rem; font-weight:800; color:#123822; text-transform:uppercase; }
        .asa-page .asa-preview-list { display:grid; gap:8px; margin-bottom:12px; }
        .asa-page .asa-preview-item { border:1px solid #e3ece6; border-radius:8px; padding:10px 12px; background:#fbfdfb; }
        .asa-page .asa-preview-flow { display:flex; align-items:center; justify-content:space-between; gap:8px; font-weight:800; color:#123822; }
        .asa-page .asa-preview-flow span { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .asa-page .asa-preview-counts { margin-top:5px; color:#66756b; font-size:.78rem; }
        .asa-page .asa-workflow { display:grid; gap:8px; margin:0 0 14px; }
        .asa-page .asa-workflow-step { display:grid; grid-template-columns:28px 1fr; gap:8px; align-items:start; border:1px solid #e3ece6; border-radius:8px; padding:9px 10px; background:#fbfdfb; }
        .asa-page .asa-workflow-step b { display:grid; place-items:center; width:24px; height:24px; border-radius:999px; background:#146c43; color:#fff; font-size:.75rem; }
        .asa-page .asa-workflow-step strong { display:block; color:#123822; font-size:.82rem; }
        .asa-page .asa-workflow-step span { display:block; color:#66756b; font-size:.76rem; line-height:1.35; }
        @media (max-width: 1100px) {
            .asa-page .asa-grid { grid-template-columns:1fr; }
            .asa-page .asa-summary { grid-template-columns:repeat(2, minmax(120px, 1fr)); }
        }
        @media (max-width: 640px) {
            .asa-page .asa-summary { grid-template-columns:1fr; }
        }
    </style>

    <div class="asa-grid">
        <section class="asa-panel">
            <h2 class="asa-panel-title">Generate Academic Setup</h2>
            <div class="asa-guide">
                Complete the setup from top to bottom. These choices determine the term, program, year level, sections, class offerings, room assignment, schedule, and student loads that will be generated.
            </div>
            <div class="asa-workflow" aria-label="Registrar workflow after final grades">
                <div class="asa-workflow-step"><b>1</b><div><strong>Post final grades first</strong><span>Faculty posts finals, then dean/registrar finalizes the grades used for promotion checking.</span></div></div>
                <div class="asa-workflow-step"><b>2</b><div><strong>Open the next academic term</strong><span>Academic Term Lifecycle evaluates grades. Eligible students move forward; failed, incomplete, or missing grades become Pending Review.</span></div></div>
                <div class="asa-workflow-step"><b>3</b><div><strong>Review irregular students</strong><span>Students with failed/back subjects or prerequisite problems need adviser or registrar action before regular block loading.</span></div></div>
                <div class="asa-workflow-step"><b>4</b><div><strong>Generate academic setup</strong><span>This page creates sections, offerings, schedules, rooms, and student loads for the students ready for the selected year level.</span></div></div>
            </div>
            <form class="asa-form" id="academicSetupForm">
                <div class="asa-field">
                    <label for="asaSchoolYear">Academic Year</label>
                    <select class="asa-select" id="asaSchoolYear" name="school_year" required>
                        @foreach($schoolYearOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="asa-note">Choose the school year where the generated sections and class offerings should belong.</div>
                </div>
                <div class="asa-field">
                    <label for="asaSemester">Semester / Term</label>
                    <select class="asa-select" id="asaSemester" name="semester" required>
                        @foreach($semesterOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="asa-note">Select the specific term to generate. This must match the term used in curriculum and section records.</div>
                </div>
                <div class="asa-field">
                    <label for="asaCampus">Campus / Branch</label>
                    <input class="asa-input" id="asaCampus" name="campus" type="text" value="Pasig" maxlength="120">
                    <div class="asa-note">Use this to label where the generated setup will run, especially when reports cover multiple campuses or branches.</div>
                </div>
                <div class="asa-field">
                    <label for="asaProgram">Program</label>
                    <select class="asa-select" id="asaProgram" name="course_id" required>
                        @foreach($programOptions as $program)
                            <option value="{{ $program['id'] }}">{{ $program['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="asa-note">The selected program controls which curriculum, students, sections, and offerings are included in this setup run.</div>
                </div>
                <div class="asa-field">
                    <label for="asaYearLevel">Year Level</label>
                    <select class="asa-select" id="asaYearLevel" name="year_block_id" required>
                        @foreach($yearLevelOptions as $yearLevel)
                            <option value="{{ $yearLevel['id'] }}">{{ $yearLevel['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="asa-note">Pick the year level to generate. Run the setup again for other year levels that need their own sections and loads.</div>
                </div>
                <div class="asa-field">
                    <label for="asaMaxStudents">Maximum Students per Section</label>
                    <input class="asa-input" id="asaMaxStudents" name="max_students_per_section" type="number" min="1" max="300" value="40" required>
                    <div class="asa-note">This number is used to split students into sections. Lower values create more sections; higher values create fewer sections.</div>
                </div>
                <label class="asa-muted">
                    <input type="checkbox" name="auto_create_rooms" value="1" checked>
                    Automatically add room records if no existing room can fit the class.
                    <span class="asa-note">Keep this checked if you want the setup to continue even when the room file has no matching available room.</span>
                </label>
                <div class="asa-actions">
                    <button type="submit" class="asa-btn" id="asaGenerateBtn">Generate Academic Setup</button>
                    <span class="asa-note">Review the result panel after generating. Publish only when the issue count is zero or all warnings are acceptable.</span>
                </div>
            </form>
        </section>

        <section class="asa-panel">
            <h2 class="asa-panel-title">Section Movement Preview</h2>
            <div class="asa-guide" id="asaPreviewMessage">Select a program and year level to preview the block sections before generating.</div>
            <div id="asaMovementPreview">
                <div class="asa-muted">Loading preview...</div>
            </div>

            <div class="asa-divider"></div>

            <h2 class="asa-panel-title">Generation Result</h2>
            <div class="asa-guide">After generating, this panel shows what the setup created and what still needs attention.</div>
            <div class="asa-alert" id="asaAlert"></div>
            <div class="asa-summary" id="asaSummary">
                @foreach(['Programs', 'Courses', 'Sections', 'Students', 'Offerings', 'Rooms', 'Schedules'] as $label)
                    <div class="asa-stat"><span>{{ $label }}</span><strong>0</strong></div>
                @endforeach
            </div>
            <div class="asa-details" id="asaGenerationDetails"></div>
            <div class="asa-issues" id="asaIssues"></div>
        </section>
    </div>

    <section class="asa-panel" style="margin-top:16px;">
        <h2 class="asa-panel-title">Recent Generation Logs</h2>
        <div class="asa-guide">Use the logs to confirm previous setup runs, compare counts, and publish generated setup batches that have no pending issues.</div>
        <div class="asa-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Academic Setup</th>
                        <th>Generated</th>
                        <th>Counts</th>
                        <th>Issues</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <strong>{{ $log->school_year }} {{ $log->semester }}</strong>
                                <div class="asa-muted">{{ $log->course_code }} - {{ $log->year_level_label }}</div>
                            </td>
                            <td>{{ optional(\Carbon\Carbon::parse($log->created_at))->format('M d, Y h:i A') }}</td>
                            <td>
                                Sections {{ (int) $log->sections_created_count }},
                                Loads {{ (int) $log->student_loads_generated_count }}
                            </td>
                            <td>{{ (int) $log->pending_issue_count }}</td>
                            <td><span class="asa-status">{{ ucwords(str_replace('_', ' ', $log->status)) }}</span></td>
                            <td>
                                <button type="button" class="asa-btn secondary asa-publish-btn" data-log-id="{{ (int) $log->id }}" {{ (int) $log->pending_issue_count > 0 || $log->status === 'published' ? 'disabled' : '' }}>Publish</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="asa-muted">No generation logs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('academicSetupAutomationPage');
    if (!page) return;

    var form = document.getElementById('academicSetupForm');
    var alertBox = document.getElementById('asaAlert');
    var issuesBox = document.getElementById('asaIssues');
    var summaryBox = document.getElementById('asaSummary');
    var detailsBox = document.getElementById('asaGenerationDetails');
    var generateBtn = document.getElementById('asaGenerateBtn');
    var previewBox = document.getElementById('asaMovementPreview');
    var previewMessage = document.getElementById('asaPreviewMessage');
    var previewTimer = null;

    function showAlert(type, message) {
        alertBox.className = 'asa-alert show ' + type;
        alertBox.textContent = message;
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderSummary(counts) {
        var items = [
            ['Programs', counts.programs_generated_count || 0],
            ['Courses', counts.courses_generated_count || 0],
            ['Sections', counts.sections_created_count || 0],
            ['Students', counts.students_assigned_count || 0],
            ['Offerings', counts.class_offerings_generated_count || 0],
            ['Rooms', counts.rooms_assigned_count || 0],
            ['Schedules', counts.schedules_generated_count || 0],
            ['Loads', counts.student_loads_generated_count || 0],
            ['Issues', counts.pending_issue_count || 0]
        ];

        summaryBox.innerHTML = items.map(function (item) {
            return '<div class="asa-stat"><span>' + item[0] + '</span><strong>' + item[1] + '</strong></div>';
        }).join('');
    }

    function renderIssues(issues) {
        if (!issues || !issues.length) {
            issuesBox.innerHTML = '<div class="asa-alert show success">No pending issues found.</div>';
            return;
        }

        issuesBox.innerHTML = issues.map(function (issue) {
            return '<div class="asa-issue">'
                + '<strong>' + issue.issue_type + '</strong>'
                + '<div>' + (issue.affected_label || 'N/A') + '</div>'
                + '<div class="asa-muted">' + issue.description + '</div>'
                + '<div class="asa-muted">' + (issue.suggested_action || '') + '</div>'
                + '</div>';
        }).join('');
    }

    function renderGenerationDetails(details) {
        if (!details) {
            detailsBox.innerHTML = '';
            return;
        }

        var sections = details.sections || [];
        var approvals = details.approval_statuses || [];
        var issueTypes = details.issue_types || [];
        var actions = details.next_actions || [];

        var html = '';

        if (sections.length) {
            html += '<div class="asa-mini-title">Generated Sections</div>'
                + '<div class="asa-detail-grid">'
                + sections.map(function (section) {
                    return '<div class="asa-detail-card">'
                        + '<strong>' + escapeHtml(section.section) + '</strong>'
                        + '<span>' + escapeHtml(section.students) + ' students, '
                        + escapeHtml(section.subjects) + ' subjects, '
                        + escapeHtml(section.loads) + ' student loads</span>'
                        + '<span>Rooms assigned: ' + escapeHtml(section.rooms_assigned)
                        + ' / Schedules ready: ' + escapeHtml(section.schedules_ready) + '</span>'
                        + '</div>';
                }).join('')
                + '</div>';
        }

        if (approvals.length || issueTypes.length) {
            html += '<div class="asa-mini-title">Status Breakdown</div><div class="asa-detail-grid">';
            approvals.forEach(function (row) {
                html += '<div class="asa-detail-card"><strong>' + escapeHtml(row.status) + '</strong><span>' + escapeHtml(row.total) + ' student(s)</span></div>';
            });
            issueTypes.forEach(function (row) {
                html += '<div class="asa-detail-card"><strong>' + escapeHtml(row.type) + '</strong><span>' + escapeHtml(row.total) + ' issue(s)</span></div>';
            });
            html += '</div>';
        }

        if (actions.length) {
            html += '<div class="asa-mini-title">Next Action</div>'
                + '<ul class="asa-action-list">'
                + actions.map(function (action) { return '<li>' + escapeHtml(action) + '</li>'; }).join('')
                + '</ul>';
        }

        detailsBox.innerHTML = html;
    }

    function renderMovementPreview(payload) {
        var requirementText = payload.requires_curriculum_check
            ? ' Curriculum check: ' + (payload.required_subject_count || 0) + ' required subject(s).'
            : '';
        previewMessage.textContent = (payload.message || 'Movement preview loaded.') + requirementText;

        var summary = payload.summary || [];
        var students = payload.students || [];

        if (!summary.length) {
            previewBox.innerHTML = '<div class="asa-alert show warn">No students found for this movement preview.</div>';
            return;
        }

        var summaryHtml = '<div class="asa-preview-list">' + summary.map(function (row) {
            return '<div class="asa-preview-item">'
                + '<div class="asa-preview-flow"><span>' + escapeHtml(row.from_section || '-') + '</span><strong>&rarr;</strong><span>' + escapeHtml(row.to_section || '-') + '</span></div>'
                + '<div class="asa-preview-counts">' + escapeHtml(row.student_count || 0) + ' students, '
                + escapeHtml(row.eligible_count || 0) + ' eligible, '
                + escapeHtml(row.pending_count || 0) + ' pending review</div>'
                + '</div>';
        }).join('') + '</div>';

        var studentHtml = '<div class="asa-mini-title">Student Preview</div>'
            + '<div class="asa-table-wrap"><table><thead><tr>'
            + '<th>Student</th><th>From</th><th>To</th><th>Status</th><th>Remarks</th>'
            + '</tr></thead><tbody>'
            + students.slice(0, 30).map(function (student) {
                var statusClass = student.status === 'Eligible' ? 'asa-status' : 'asa-status warn';
                return '<tr>'
                    + '<td><strong>' + escapeHtml(student.student_no) + '</strong><div class="asa-muted">' + escapeHtml(student.name) + '</div></td>'
                    + '<td>' + escapeHtml(student.from_year) + '<div class="asa-muted">' + escapeHtml(student.from_section) + '</div></td>'
                    + '<td>' + escapeHtml(student.to_year) + '<div class="asa-muted">' + escapeHtml(student.to_section) + '</div></td>'
                    + '<td><span class="' + statusClass + '">' + escapeHtml(student.status) + '</span></td>'
                    + '<td class="asa-muted">' + escapeHtml(student.remarks) + '</td>'
                    + '</tr>';
            }).join('')
            + '</tbody></table></div>';

        if (students.length > 30) {
            studentHtml += '<div class="asa-note">Showing first 30 students only. Total preview rows: ' + escapeHtml(students.length) + '.</div>';
        }

        previewBox.innerHTML = summaryHtml + studentHtml;
    }

    function loadMovementPreview() {
        var params = new URLSearchParams(new FormData(form));
        previewBox.innerHTML = '<div class="asa-muted">Loading preview...</div>';

        fetch(page.dataset.previewUrl + '?' + params.toString(), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    if (!response.ok) throw payload;
                    return payload;
                });
            })
            .then(renderMovementPreview)
            .catch(function () {
                previewBox.innerHTML = '<div class="asa-alert show error">Unable to load movement preview.</div>';
            });
    }

    function scheduleMovementPreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(loadMovementPreview, 250);
    }

    ['asaProgram', 'asaYearLevel', 'asaMaxStudents'].forEach(function (id) {
        var input = document.getElementById(id);
        if (input) {
            input.addEventListener('change', scheduleMovementPreview);
            input.addEventListener('input', scheduleMovementPreview);
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        generateBtn.disabled = true;
        generateBtn.textContent = 'Generating...';
        issuesBox.innerHTML = '';
        detailsBox.innerHTML = '';
        showAlert('warn', 'Generating academic setup. Please wait...');

        fetch(page.dataset.generateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': page.dataset.csrf,
                'Accept': 'application/json'
            },
            body: new FormData(form)
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    if (!response.ok) throw payload;
                    return payload;
                });
            })
            .then(function (payload) {
                renderSummary(payload.counts || {});
                renderGenerationDetails(payload.details || null);
                renderIssues(payload.issues || []);
                showAlert((payload.counts || {}).pending_issue_count > 0 ? 'warn' : 'success', payload.message || 'Academic setup generated.');
                scheduleMovementPreview();
            })
            .catch(function (error) {
                var message = error && error.message ? error.message : 'Unable to generate academic setup.';
                if (error && error.errors) {
                    var firstKey = Object.keys(error.errors)[0];
                    if (firstKey && error.errors[firstKey] && error.errors[firstKey][0]) {
                        message = error.errors[firstKey][0];
                    }
                }
                showAlert('error', message);
            })
            .finally(function () {
                generateBtn.disabled = false;
                generateBtn.textContent = 'Generate Academic Setup';
            });
    });

    loadMovementPreview();

    document.querySelectorAll('.asa-publish-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var logId = button.getAttribute('data-log-id');
            var url = page.dataset.publishUrlTemplate.replace('__LOG__', logId);
            button.disabled = true;
            button.textContent = 'Publishing...';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': page.dataset.csrf,
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    return response.json().then(function (payload) {
                        if (!response.ok) throw payload;
                        return payload;
                    });
                })
                .then(function (payload) {
                    showAlert('success', payload.message || 'Academic setup published.');
                    setTimeout(function () { window.location.reload(); }, 900);
                })
                .catch(function (error) {
                    showAlert('error', error && error.message ? error.message : 'Unable to publish academic setup.');
                    button.disabled = false;
                    button.textContent = 'Publish';
                });
        });
    });
});
</script>
@endsection
