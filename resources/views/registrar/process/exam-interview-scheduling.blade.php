@extends('layouts.registrar')

@section('title', 'PLP - Exam & Interview Scheduling')
@section('page-title', 'EXAM & INTERVIEW SCHEDULING')

@section('content')
<div class="pf-page eis-page" id="examInterviewSchedulingPage" data-csrf="{{ csrf_token() }}">
    <style>
        .eis-page .eis-summary { display:grid; grid-template-columns:repeat(5, minmax(130px, 1fr)); gap:12px; margin-bottom:16px; }
        .eis-page .eis-stat { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:12px 14px; }
        .eis-page .eis-stat-label { color:#607264; display:block; font-size:.78rem; font-weight:800; text-transform:uppercase; }
        .eis-page .eis-stat-value { color:#123822; display:block; font-size:1.45rem; font-weight:900; margin-top:4px; }
        .eis-page .eis-filters { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:14px; margin-bottom:16px; }
        .eis-page .eis-filter-grid { display:grid; grid-template-columns:1.5fr 1fr auto; gap:12px; align-items:end; }
        .eis-page label { color:#46564a; display:block; font-size:.78rem; font-weight:800; margin-bottom:5px; }
        .eis-page input, .eis-page select { background:#fff; border:1px solid #cfd9d2; border-radius:7px; color:#143521; min-height:34px; padding:6px 8px; width:100%; }
        .eis-page .eis-table input, .eis-page .eis-table select { min-width:110px; }
        .eis-page .eis-person { color:#143521; font-weight:900; }
        .eis-page .eis-muted { color:#66756b; font-size:.82rem; }
        .eis-page .eis-save { background:#146c43; border:0; border-radius:7px; color:#fff; cursor:pointer; font-weight:800; min-height:34px; padding:6px 12px; }
        .eis-page .eis-save:disabled { cursor:wait; opacity:.65; }
        .eis-page .eis-empty { color:#607264; padding:24px; text-align:center; }
        @media (max-width:1180px) {
            .eis-page .eis-summary { grid-template-columns:repeat(2, minmax(130px, 1fr)); }
            .eis-page .eis-filter-grid { grid-template-columns:1fr; }
        }
    </style>

    <div class="eis-summary">
        <div class="eis-stat"><span class="eis-stat-label">Applicants</span><span class="eis-stat-value">{{ number_format((int) $summary['applicants']) }}</span></div>
        <div class="eis-stat"><span class="eis-stat-label">Exam Scheduled</span><span class="eis-stat-value">{{ number_format((int) $summary['exam_scheduled']) }}</span></div>
        <div class="eis-stat"><span class="eis-stat-label">Exam Passed</span><span class="eis-stat-value">{{ number_format((int) $summary['exam_passed']) }}</span></div>
        <div class="eis-stat"><span class="eis-stat-label">Interview Scheduled</span><span class="eis-stat-value">{{ number_format((int) $summary['interview_scheduled']) }}</span></div>
        <div class="eis-stat"><span class="eis-stat-label">Medical Cleared</span><span class="eis-stat-value">{{ number_format((int) $summary['medical_cleared']) }}</span></div>
    </div>

    <form class="eis-filters" method="GET" action="{{ route('registrar.process.exam-interview-scheduling') }}">
        <div class="eis-filter-grid">
            <div>
                <label for="eisSearch">Search</label>
                <input id="eisSearch" type="text" name="q" value="{{ $search }}" placeholder="Applicant ID, name, or email">
            </div>
            <div>
                <label for="eisStatus">Status</label>
                <select id="eisStatus" name="status">
                    <option value="">All Statuses</option>
                    @foreach(['Pending','Scheduled','Passed','Failed','Completed','No Show','Cleared','For Follow-up','Not Cleared'] as $option)
                        <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="pf-btn-new">Filter</button>
        </div>
    </form>

    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table eis-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Exam Date</th>
                    <th>Exam Time</th>
                    <th>Exam Room</th>
                    <th>Result</th>
                    <th>Score</th>
                    <th>Interview Date</th>
                    <th>Interview Time</th>
                    <th>Interview Room</th>
                    <th>Interview</th>
                    <th>Medical</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applicants as $row)
                    <tr data-update-url="{{ $row['update_url'] }}">
                        <td>
                            <div class="eis-person">{{ $row['name'] !== '' ? $row['name'] : 'Applicant' }}</div>
                            <div class="eis-muted">{{ $row['applicant_id'] }} · {{ $row['program'] }}</div>
                        </td>
                        <td><input data-field="exam_date" type="date" value="{{ $row['exam_date'] }}"></td>
                        <td><input data-field="exam_time" type="time" value="{{ $row['exam_time'] }}"></td>
                        <td><input data-field="exam_room" type="text" value="{{ $row['exam_room'] }}" placeholder="Room"></td>
                        <td>
                            <select data-field="exam_result_status">
                                @foreach(['Pending','Passed','Failed'] as $option)
                                    <option value="{{ $option }}" {{ $row['exam_result_status'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input data-field="exam_score" type="number" min="0" max="100" step="0.01" value="{{ $row['exam_score'] }}"></td>
                        <td><input data-field="interview_date" type="date" value="{{ $row['interview_date'] }}"></td>
                        <td><input data-field="interview_time" type="time" value="{{ $row['interview_time'] }}"></td>
                        <td><input data-field="interview_room" type="text" value="{{ $row['interview_room'] }}" placeholder="Room"></td>
                        <td>
                            <select data-field="interview_status">
                                @foreach(['Pending','Scheduled','Completed','No Show'] as $option)
                                    <option value="{{ $option }}" {{ $row['interview_status'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select data-field="medical_clearance_status">
                                @foreach(['Pending','Cleared','For Follow-up','Not Cleared'] as $option)
                                    <option value="{{ $option }}" {{ $row['medical_clearance_status'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><button type="button" class="eis-save" data-save>Save</button></td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="eis-empty">No applicants found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    var page = document.getElementById('examInterviewSchedulingPage');
    if (!page) return;
    var csrf = page.getAttribute('data-csrf') || '';
    function toast(message, type) {
        if (typeof window.showRegistrarToast === 'function') window.showRegistrarToast(message, type || 'success');
    }
    page.addEventListener('click', function (event) {
        var button = event.target.closest('[data-save]');
        if (!button) return;
        var row = button.closest('tr[data-update-url]');
        var payload = {};
        row.querySelectorAll('[data-field]').forEach(function (input) {
            payload[input.getAttribute('data-field')] = input.value;
        });
        button.disabled = true;
        button.textContent = 'Saving';
        fetch(row.getAttribute('data-update-url'), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) throw data;
                return data;
            });
        }).then(function (data) {
            toast(data.message || 'Saved successfully.', 'success');
        }).catch(function (error) {
            var message = error && error.message ? error.message : 'Unable to save details.';
            if (error && error.errors) {
                var keys = Object.keys(error.errors);
                if (keys.length && error.errors[keys[0]] && error.errors[keys[0]][0]) message = error.errors[keys[0]][0];
            }
            toast(message, 'error');
        }).finally(function () {
            button.disabled = false;
            button.textContent = 'Save';
        });
    });
})();
</script>
@endsection
