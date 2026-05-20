@extends('layouts.registrar')

@section('title', 'PLP - Room Generation and Assignment')
@section('page-title', 'ROOM GENERATION AND ASSIGNMENT')

@section('content')
<div
    class="rga-page"
    id="roomGenerationAssignmentPage"
    data-generate-url="{{ route('registrar.registrar-menu.scheduling.room-generation-assignment.generate') }}"
    data-assign-url="{{ route('registrar.registrar-menu.scheduling.room-generation-assignment.assign') }}"
    data-report-url="{{ route('registrar.registrar-menu.scheduling.room-generation-assignment.report') }}"
    data-csrf="{{ csrf_token() }}"
>
    <style>
        .rga-page { color:#143521; }
        .rga-grid { display:grid; grid-template-columns:minmax(280px, 430px) 1fr; gap:16px; align-items:start; }
        .rga-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:16px; }
        .rga-title { margin:0 0 12px; color:#123822; font-size:1rem; font-weight:800; }
        .rga-form { display:grid; gap:12px; }
        .rga-field label { display:block; margin-bottom:5px; color:#46564a; font-size:.78rem; font-weight:800; text-transform:uppercase; }
        .rga-input, .rga-select { width:100%; min-height:38px; border:1px solid #cfd9d2; border-radius:7px; background:#fff; color:#143521; padding:8px 10px; }
        .rga-actions { display:flex; flex-wrap:wrap; gap:8px; }
        .rga-btn { border:0; border-radius:7px; background:#146c43; color:#fff; cursor:pointer; font-weight:800; min-height:38px; padding:8px 14px; }
        .rga-btn.secondary { background:#eef6f1; color:#146c43; border:1px solid #b9d8c5; }
        .rga-btn:disabled { cursor:wait; opacity:.65; }
        .rga-alert { display:none; margin-bottom:14px; border-radius:8px; padding:10px 12px; font-weight:700; }
        .rga-alert.show { display:block; }
        .rga-alert.success { background:#e8f6ee; color:#17633a; border:1px solid #bfdfcc; }
        .rga-alert.warn { background:#fff5d8; color:#8a5b00; border:1px solid #ead28a; }
        .rga-alert.error { background:#fdecec; color:#9f1d1d; border:1px solid #efb8b8; }
        .rga-summary { display:grid; grid-template-columns:repeat(3, minmax(120px, 1fr)); gap:10px; margin-bottom:14px; }
        .rga-stat { border:1px solid #e3ece6; border-radius:8px; padding:10px 12px; background:#fbfdfb; }
        .rga-stat span { display:block; color:#607264; font-size:.72rem; font-weight:800; text-transform:uppercase; }
        .rga-stat strong { display:block; margin-top:3px; color:#123822; font-size:1.25rem; }
        .rga-table-wrap { overflow:auto; border:1px solid #e3ece6; border-radius:8px; }
        .rga-table { width:100%; min-width:920px; border-collapse:collapse; }
        .rga-table th, .rga-table td { padding:10px 12px; border-bottom:1px solid #edf3ef; text-align:left; vertical-align:top; font-size:.86rem; }
        .rga-table th { background:#f5faf7; color:#46564a; font-size:.74rem; font-weight:800; text-transform:uppercase; }
        .rga-table tr:last-child td { border-bottom:0; }
        .rga-muted { color:#66756b; font-size:.82rem; }
        .rga-note { display:block; margin:6px 0 0; color:#66756b; font-size:.78rem; line-height:1.45; }
        .rga-status { display:inline-flex; border-radius:999px; padding:4px 8px; background:#eef6f1; color:#146c43; font-size:.75rem; font-weight:800; }
        .rga-status.pending, .rga-status.conflict { background:#fff5d8; color:#8a5b00; }
        @media (max-width: 1100px) {
            .rga-grid { grid-template-columns:1fr; }
            .rga-summary { grid-template-columns:1fr; }
        }
    </style>

    <div class="rga-grid">
        <section class="rga-panel">
            <h2 class="rga-title">Room Assignment Filters</h2>
            <div class="rga-note" style="margin-bottom:12px;">Generated room schedules use the room availability window of 7:00 AM to 9:00 PM and skip any room that already has an overlapping assignment.</div>
            <form class="rga-form" id="rgaForm">
                <div class="rga-field">
                    <label for="rgaSchoolYear">Academic Year</label>
                    <select class="rga-select" id="rgaSchoolYear" name="school_year" required>
                        @foreach($schoolYearOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rga-field">
                    <label for="rgaSemester">Semester</label>
                    <select class="rga-select" id="rgaSemester" name="semester" required>
                        @foreach($semesterOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rga-field">
                    <label for="rgaProgram">Program</label>
                    <select class="rga-select" id="rgaProgram" name="course_id">
                        <option value="">All Programs</option>
                        @foreach($programOptions as $program)
                            <option value="{{ $program['id'] }}">{{ $program['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rga-field">
                    <label for="rgaYearLevel">Year Level</label>
                    <select class="rga-select" id="rgaYearLevel" name="year_block_id">
                        <option value="">All Year Levels</option>
                        @foreach($yearLevelOptions as $yearLevel)
                            <option value="{{ $yearLevel['id'] }}">{{ $yearLevel['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rga-field">
                    <label for="rgaSection">Section</label>
                    <select class="rga-select" id="rgaSection" name="section">
                        <option value="">All Sections</option>
                        @foreach($sectionOptions as $section)
                            <option value="{{ $section }}">{{ $section }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rga-actions">
                    <button type="button" class="rga-btn secondary" id="rgaGenerateRooms">Generate Rooms</button>
                    <button type="submit" class="rga-btn" id="rgaAssignRooms">Assign Rooms Per Section and Subject</button>
                    <button type="button" class="rga-btn secondary" id="rgaRegeneratePending">Regenerate Pending Room Assignments</button>
                    <button type="button" class="rga-btn secondary" id="rgaViewConflicts">View Room Conflicts</button>
                    <button type="button" class="rga-btn secondary" id="rgaManual">Manual Room Assignment</button>
                </div>
            </form>
        </section>

        <section class="rga-panel">
            <h2 class="rga-title">Room Assignment Report</h2>
            <div class="rga-alert" id="rgaAlert"></div>
            <div class="rga-summary" id="rgaSummary">
                <div class="rga-stat"><span>Assigned</span><strong>0</strong></div>
                <div class="rga-stat"><span>Pending</span><strong>0</strong></div>
                <div class="rga-stat"><span>Conflicts</span><strong>0</strong></div>
            </div>
            <div class="rga-table-wrap">
                <table class="rga-table">
                    <thead>
                        <tr>
                            <th>Class Offering</th>
                            <th>Section</th>
                            <th>Component</th>
                            <th>Required Room</th>
                            <th>Assigned Room</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="rgaRows">
                        <tr><td colspan="8" class="rga-muted">Generate or load assignments to view the report.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('roomGenerationAssignmentPage');
    if (!page) return;

    var form = document.getElementById('rgaForm');
    var rows = document.getElementById('rgaRows');
    var alertBox = document.getElementById('rgaAlert');
    var summary = document.getElementById('rgaSummary');
    var generateBtn = document.getElementById('rgaGenerateRooms');
    var assignBtn = document.getElementById('rgaAssignRooms');
    var pendingBtn = document.getElementById('rgaRegeneratePending');
    var conflictsBtn = document.getElementById('rgaViewConflicts');
    var manualBtn = document.getElementById('rgaManual');

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'})[char];
        });
    }

    function showAlert(type, message) {
        alertBox.className = 'rga-alert show ' + type;
        alertBox.textContent = message;
    }

    function setSummary(data) {
        data = data || {};
        summary.innerHTML = [
            ['Assigned', data.assigned || 0],
            ['Pending', data.pending || 0],
            ['Conflicts', data.conflicts || 0]
        ].map(function (item) {
            return '<div class="rga-stat"><span>' + item[0] + '</span><strong>' + item[1] + '</strong></div>';
        }).join('');
    }

    function renderRows(items) {
        if (!items || !items.length) {
            rows.innerHTML = '<tr><td colspan="8" class="rga-muted">No room assignment rows found.</td></tr>';
            return;
        }

        rows.innerHTML = items.map(function (item) {
            var statusClass = /pending|conflict/i.test(item.assignment_status || '') ? ' pending' : '';
            return '<tr>'
                + '<td><strong>' + escapeHtml(item.course_code) + '</strong><div class="rga-muted">' + escapeHtml(item.subject_name) + '</div></td>'
                + '<td>' + escapeHtml(item.section_id) + '</td>'
                + '<td>' + escapeHtml(item.schedule_component_type) + '</td>'
                + '<td>' + escapeHtml(item.room_type_required) + '</td>'
                + '<td>' + escapeHtml(item.room_code || 'TBA') + '</td>'
                + '<td>' + escapeHtml(item.day) + ' ' + escapeHtml(item.start_time) + '-' + escapeHtml(item.end_time) + '</td>'
                + '<td><span class="rga-status' + statusClass + '">' + escapeHtml(item.assignment_status) + '</span></td>'
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

    function reportRowsFromPayload(payload) {
        return (payload.assigned || []).concat(payload.issues || []);
    }

    generateBtn.addEventListener('click', function () {
        generateBtn.disabled = true;
        showAlert('warn', 'Generating missing room records...');
        requestJson(page.dataset.generateUrl, { method: 'POST', body: new FormData(form) })
            .then(function (payload) {
                showAlert('success', payload.message + ' Created: ' + payload.created_count + ', Updated: ' + payload.updated_count + ', Program links: ' + (payload.program_assignment_count || 0) + '.');
            })
            .catch(function (error) {
                showAlert('error', error.message || 'Unable to generate rooms.');
            })
            .finally(function () {
                generateBtn.disabled = false;
            });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        assignBtn.disabled = true;
        showAlert('warn', 'Assigning rooms per section and subject...');
        requestJson(page.dataset.assignUrl, {
            method: 'POST',
            body: new FormData(form)
        })
            .then(function (payload) {
                setSummary(payload.summary);
                renderRows(reportRowsFromPayload(payload));
                showAlert((payload.summary || {}).pending > 0 ? 'warn' : 'success', payload.message || 'Room assignment completed.');
            })
            .catch(function (error) {
                showAlert('error', error.message || 'Unable to assign rooms.');
            })
            .finally(function () {
                assignBtn.disabled = false;
            });
    });

    pendingBtn.addEventListener('click', function () {
        var data = new FormData(form);
        data.append('only_pending', '1');
        pendingBtn.disabled = true;
        showAlert('warn', 'Regenerating pending room assignments...');
        requestJson(page.dataset.assignUrl, { method: 'POST', body: data })
            .then(function (payload) {
                setSummary(payload.summary);
                renderRows(reportRowsFromPayload(payload));
                showAlert('success', payload.message || 'Pending room assignments regenerated.');
            })
            .catch(function (error) {
                showAlert('error', error.message || 'Unable to regenerate pending assignments.');
            })
            .finally(function () {
                pendingBtn.disabled = false;
            });
    });

    conflictsBtn.addEventListener('click', function () {
        var params = new URLSearchParams(new FormData(form));
        requestJson(page.dataset.reportUrl + '?' + params.toString())
            .then(function (payload) {
                var conflictRows = (payload.rows || []).filter(function (row) {
                    return /conflict|pending/i.test(row.assignment_status || '');
                });
                renderRows(conflictRows);
                showAlert(conflictRows.length ? 'warn' : 'success', conflictRows.length ? 'Pending room issues loaded.' : 'No pending room issues found.');
            })
            .catch(function (error) {
                showAlert('error', error.message || 'Unable to load room conflicts.');
            });
    });

    manualBtn.addEventListener('click', function () {
        showAlert('warn', 'Manual override uses the Room File and Class Schedule Preparation screens. Pending rows below show which section-subject combinations need attention.');
        conflictsBtn.click();
    });
});
</script>
@endsection
