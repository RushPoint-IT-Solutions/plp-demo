@extends('layouts.registrar')

@section('title', 'PLP - Class Schedule Preparation')
@section('page-title', 'CLASS SCHEDULE PREPARATION')

@section('content')
@php
    $selectedSchoolYear = (string) ($schoolYear ?? '');
    $selectedSemester = (string) ($semester ?? '');
    $selectedCourseId = (int) ($courseId ?? 0);
@endphp

<div
    class="pf-page csp-page"
    id="classSchedulePreparationPage"
    data-update-url-template="{{ route('registrar.registrar-menu.scheduling.class-schedule-preparation.update', ['subject' => '__SUBJECT__']) }}"
    data-csrf="{{ csrf_token() }}"
>
    <style>
        .csp-page .csp-summary { display:grid; grid-template-columns:repeat(5, minmax(130px, 1fr)); gap:12px; margin:0 0 16px; }
        .csp-page .csp-stat { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:12px 14px; }
        .csp-page .csp-stat-label { display:block; color:#607264; font-size:.78rem; font-weight:700; text-transform:uppercase; }
        .csp-page .csp-stat-value { color:#123822; display:block; font-size:1.45rem; font-weight:800; margin-top:4px; }
        .csp-page .csp-filters { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:14px; margin-bottom:16px; }
        .csp-page .csp-filter-grid { display:grid; grid-template-columns:1fr 1fr 1.2fr 1fr 1.5fr auto; gap:12px; align-items:end; }
        .csp-page .csp-field label { display:block; color:#46564a; font-size:.78rem; font-weight:700; margin-bottom:5px; }
        .csp-page .csp-input, .csp-page .csp-select { width:100%; min-height:38px; border:1px solid #cfd9d2; border-radius:7px; color:#143521; padding:8px 10px; background:#fff; }
        .csp-page .csp-table input, .csp-page .csp-table select { min-width:110px; height:34px; border:1px solid #cfd9d2; border-radius:7px; padding:5px 8px; background:#fff; }
        .csp-page .csp-table .csp-days { min-width:90px; }
        .csp-page .csp-table .csp-room { min-width:150px; }
        .csp-page .csp-table .csp-faculty { min-width:190px; }
        .csp-page .csp-subject { font-weight:800; color:#143521; }
        .csp-page .csp-muted { color:#66756b; font-size:.82rem; }
        .csp-page .csp-status { display:inline-flex; align-items:center; justify-content:center; min-width:88px; border-radius:999px; padding:4px 8px; font-size:.76rem; font-weight:800; }
        .csp-page .csp-status.done { background:#e8f6ee; color:#17633a; }
        .csp-page .csp-status.pending { background:#fff5d8; color:#8a5b00; }
        .csp-page .csp-save { border:0; border-radius:7px; background:#146c43; color:#fff; cursor:pointer; font-weight:800; min-height:34px; padding:6px 12px; }
        .csp-page .csp-save:disabled { cursor:wait; opacity:.65; }
        .csp-page .csp-empty { padding:28px; text-align:center; color:#607264; }
        @media (max-width: 1180px) {
            .csp-page .csp-summary { grid-template-columns:repeat(2, minmax(130px, 1fr)); }
            .csp-page .csp-filter-grid { grid-template-columns:1fr 1fr; }
        }
        @media (max-width: 640px) {
            .csp-page .csp-summary, .csp-page .csp-filter-grid { grid-template-columns:1fr; }
        }
    </style>

    <div class="csp-summary">
        <div class="csp-stat">
            <span class="csp-stat-label">Displayed Rows</span>
            <span class="csp-stat-value">{{ number_format((int) ($summary['total'] ?? 0)) }}</span>
        </div>
        <div class="csp-stat">
            <span class="csp-stat-label">Scheduled</span>
            <span class="csp-stat-value">{{ number_format((int) ($summary['scheduled'] ?? 0)) }}</span>
        </div>
        <div class="csp-stat">
            <span class="csp-stat-label">Needs Attention</span>
            <span class="csp-stat-value">{{ number_format((int) ($summary['needs_attention'] ?? 0)) }}</span>
        </div>
        <div class="csp-stat">
            <span class="csp-stat-label">Rooms</span>
            <span class="csp-stat-value">{{ number_format((int) ($summary['rooms'] ?? 0)) }}</span>
        </div>
        <div class="csp-stat">
            <span class="csp-stat-label">Instructors</span>
            <span class="csp-stat-value">{{ number_format((int) ($summary['faculty'] ?? 0)) }}</span>
        </div>
    </div>

    <form class="csp-filters" method="GET" action="{{ route('registrar.registrar-menu.scheduling.class-schedule-preparation') }}">
        <div class="csp-filter-grid">
            <div class="csp-field">
                <label for="cspSchoolYear">School Year</label>
                <select class="csp-select" id="cspSchoolYear" name="school_year">
                    <option value="">All School Years</option>
                    @foreach($schoolYearOptions as $option)
                        <option value="{{ $option['value'] }}" {{ $selectedSchoolYear === (string) $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="csp-field">
                <label for="cspSemester">Semester</label>
                <select class="csp-select" id="cspSemester" name="semester">
                    <option value="">All Semesters</option>
                    @foreach($semesterOptions as $option)
                        <option value="{{ $option['value'] }}" {{ $selectedSemester === (string) $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="csp-field">
                <label for="cspCourse">Program</label>
                <select class="csp-select" id="cspCourse" name="course_id">
                    <option value="0">All Programs</option>
                    @foreach($courseOptions as $option)
                        <option value="{{ $option['id'] }}" {{ $selectedCourseId === (int) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="csp-field">
                <label for="cspSection">Section</label>
                <input class="csp-input" id="cspSection" type="text" name="section" value="{{ $section ?? '' }}" placeholder="Example: BSIT 1A">
            </div>
            <div class="csp-field">
                <label for="cspSearch">Search</label>
                <input class="csp-input" id="cspSearch" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Subject, room, instructor">
            </div>
            <button type="submit" class="pf-btn-new">Filter</button>
        </div>
    </form>

    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table csp-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Term</th>
                    <th>Program / Section</th>
                    <th>Subject</th>
                    <th>Days</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Room</th>
                    <th>Instructor</th>
                    <th style="width:90px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr data-subject-id="{{ $row['id'] }}">
                        <td>
                            <span class="csp-status {{ $row['is_scheduled'] ? 'done' : 'pending' }}" data-csp-status>
                                {{ $row['is_scheduled'] ? 'Scheduled' : 'Pending' }}
                            </span>
                        </td>
                        <td>
                            <div>{{ $row['school_year'] !== '' ? $row['school_year'] : 'N/A' }}</div>
                            <div class="csp-muted">{{ $row['semester'] !== '' ? $row['semester'] : 'N/A' }}</div>
                        </td>
                        <td>
                            <div>{{ $row['course_code'] !== '' ? $row['course_code'] : 'N/A' }}</div>
                            <div class="csp-muted">{{ $row['section'] !== '' ? $row['section'] : 'No section' }}</div>
                        </td>
                        <td>
                            <div class="csp-subject">{{ $row['subject_code'] !== '' ? $row['subject_code'] : 'N/A' }}</div>
                            <div class="csp-muted">{{ $row['subject_name'] !== '' ? $row['subject_name'] : 'No title' }}</div>
                        </td>
                        <td><input class="csp-days" data-field="days" type="text" value="{{ $row['days'] }}" placeholder="MWF"></td>
                        <td><input data-field="time_start" type="time" value="{{ $row['time_start'] }}"></td>
                        <td><input data-field="time_end" type="time" value="{{ $row['time_end'] }}"></td>
                        <td>
                            <select class="csp-room" data-field="room">
                                <option value="">TBA</option>
                                @foreach($roomOptions as $room)
                                    <option value="{{ $room['value'] }}" {{ (string) $row['room'] === (string) $room['value'] ? 'selected' : '' }}>{{ $room['label'] }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="csp-faculty" data-field="faculty_id">
                                <option value="">TBA</option>
                                @foreach($facultyOptions as $faculty)
                                    <option value="{{ $faculty['id'] }}" {{ (int) ($row['faculty_id'] ?? 0) === (int) $faculty['id'] ? 'selected' : '' }}>{{ $faculty['label'] }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><button type="button" class="csp-save" data-csp-save>Save</button></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="csp-empty">No offered subjects matched the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    var page = document.getElementById('classSchedulePreparationPage');
    if (!page) {
        return;
    }

    var updateTemplate = page.getAttribute('data-update-url-template') || '';
    var csrf = page.getAttribute('data-csrf') || '';

    function toast(message, type) {
        if (typeof window.showRegistrarToast === 'function') {
            window.showRegistrarToast(message, type || 'success');
        }
    }

    function rowIsScheduled(row) {
        var days = row.querySelector('[data-field="days"]').value.trim();
        var start = row.querySelector('[data-field="time_start"]').value.trim();
        var end = row.querySelector('[data-field="time_end"]').value.trim();
        var room = row.querySelector('[data-field="room"]').value.trim();
        var faculty = row.querySelector('[data-field="faculty_id"]').value.trim();
        return days !== '' && start !== '' && end !== '' && room !== '' && faculty !== '';
    }

    function refreshStatus(row) {
        var status = row.querySelector('[data-csp-status]');
        if (!status) {
            return;
        }

        if (rowIsScheduled(row)) {
            status.textContent = 'Scheduled';
            status.classList.remove('pending');
            status.classList.add('done');
            return;
        }

        status.textContent = 'Pending';
        status.classList.remove('done');
        status.classList.add('pending');
    }

    page.addEventListener('click', function (event) {
        var button = event.target.closest('[data-csp-save]');
        if (!button) {
            return;
        }

        var row = button.closest('tr[data-subject-id]');
        if (!row) {
            return;
        }

        var subjectId = row.getAttribute('data-subject-id');
        var url = updateTemplate.replace('__SUBJECT__', subjectId);
        var payload = {
            days: row.querySelector('[data-field="days"]').value,
            time_start: row.querySelector('[data-field="time_start"]').value,
            time_end: row.querySelector('[data-field="time_end"]').value,
            room: row.querySelector('[data-field="room"]').value,
            faculty_id: row.querySelector('[data-field="faculty_id"]').value
        };

        button.disabled = true;
        button.textContent = 'Saving';

        fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw data;
                    }
                    return data;
                });
            })
            .then(function (data) {
                refreshStatus(row);
                toast((data && data.message) ? data.message : 'Class schedule updated successfully.', 'success');
            })
            .catch(function (error) {
                var message = 'Unable to save schedule.';
                if (error && error.errors) {
                    var keys = Object.keys(error.errors);
                    if (keys.length && error.errors[keys[0]] && error.errors[keys[0]][0]) {
                        message = error.errors[keys[0]][0];
                    }
                } else if (error && error.message) {
                    message = error.message;
                }
                toast(message, 'error');
            })
            .finally(function () {
                button.disabled = false;
                button.textContent = 'Save';
            });
    });
})();
</script>
@endsection
