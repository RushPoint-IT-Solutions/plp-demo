@extends('layouts.registrar')

@section('title', 'PLP - Tagging of Graduates')
@section('page-title', 'TAGGING OF GRADUATES')

@push('styles')
<style>
.tog-page { padding: 24px 28px; }
.tog-head { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; margin-bottom:16px; }
.tog-title { margin:0; color:#173b28; font-size:22px; font-weight:900; }
.tog-subtitle { margin:4px 0 0; color:#667085; font-size:13px; }
.tog-head-actions { display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
.tog-link-btn { height:36px; border-radius:7px; border:1px solid #d8e2dc; padding:0 13px; background:#fff; color:#23513b; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:7px; text-decoration:none; }
.tog-link-btn:hover { color:#0f3b24; background:#f8fbf9; }
.tog-summary { display:grid; grid-template-columns:repeat(4, minmax(150px, 1fr)); gap:12px; margin-bottom:14px; }
.tog-stat { background:#fff; border:1px solid #e8eee9; border-radius:8px; padding:14px 15px; box-shadow:0 1px 4px rgba(15,59,36,.06); }
.tog-stat span { display:block; color:#667085; font-size:11px; font-weight:800; text-transform:uppercase; }
.tog-stat strong { display:block; color:#173b28; font-size:24px; line-height:1.1; margin-top:5px; }
.tog-filter-card { background:#fff; border:1px solid #e8eee9; border-radius:8px; padding:14px; margin-bottom:14px; box-shadow:0 1px 4px rgba(15,59,36,.06); }
.tog-filter-grid { display:grid; grid-template-columns:1.5fr repeat(5, minmax(120px, 1fr)) auto auto; gap:10px; align-items:end; }
.tog-field label { display:block; margin-bottom:5px; color:#53645b; font-size:11px; font-weight:800; text-transform:uppercase; }
.tog-input-control { width:100%; height:36px; border:1px solid #d8e2dc; border-radius:7px; padding:0 10px; color:#1f2937; font-size:13px; background:#fff; }
.tog-filter-btn { height:36px; border:0; border-radius:7px; padding:0 16px; background:#006837; color:#fff; font-size:12px; font-weight:900; cursor:pointer; }
.tog-clear-btn { height:36px; border:1px solid #d8e2dc; border-radius:7px; padding:0 13px; background:#fff; color:#475569; font-size:12px; font-weight:800; display:inline-flex; align-items:center; text-decoration:none; }
.tog-table-card { background:#fff; border:1px solid #e8eee9; border-radius:8px; overflow:auto; box-shadow:0 1px 4px rgba(15,59,36,.06); }
.tog-table { width:100%; min-width:1120px; border-collapse:collapse; }
.tog-table th { background:#f7faf8; color:#465a50; font-size:11px; font-weight:900; text-transform:uppercase; padding:11px 12px; border-bottom:1px solid #e8eee9; text-align:left; white-space:nowrap; }
.tog-table td { padding:10px 12px; border-bottom:1px solid #f0f3f1; color:#344054; font-size:13px; vertical-align:middle; }
.tog-student-name { color:#173b28; font-weight:900; }
.tog-muted { color:#8a988f; font-size:12px; }
.tog-input { height:34px; border:1px solid #d8e2dc; border-radius:7px; padding:0 8px; font-size:12px; color:#1f2937; background:#fff; }
.tog-input-date { width:138px; }
.tog-input-so { width:135px; }
.tog-check { width:17px; height:17px; accent-color:#006837; }
.tog-save-btn { min-width:78px; height:32px; border-radius:7px !important; font-size:12px !important; }
.tog-badge { display:inline-flex; align-items:center; height:24px; border-radius:999px; padding:0 9px; font-size:11px; font-weight:900; }
.tog-badge-grad { background:#e9f8ef; color:#08783d; }
.tog-badge-pending { background:#f8fafc; color:#475569; }
.tog-badge-suspended { background:#fff1f2; color:#b42318; }
.tog-row-suspended td { background:#fff7f7 !important; color:#7f1d1d; }
.tog-empty { padding:34px 16px; text-align:center; color:#667085; }
.tog-pagination { margin-top:14px; display:flex; justify-content:center; }
@media (max-width: 1180px) {
    .tog-filter-grid { grid-template-columns:repeat(2, minmax(160px, 1fr)); }
    .tog-summary { grid-template-columns:repeat(2, minmax(150px, 1fr)); }
}
@media (max-width: 680px) {
    .tog-page { padding:18px 14px; }
    .tog-head { display:block; }
    .tog-head-actions { justify-content:flex-start; margin-top:12px; }
    .tog-filter-grid, .tog-summary { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')
<div class="tog-page">
    <div class="tog-head">
        <div>
            <h1 class="tog-title">Tagging of Graduates</h1>
            <p class="tog-subtitle">Tag graduating students, encode graduation date and SO details, and control alumni account access.</p>
        </div>
        <div class="tog-head-actions">
            <a class="tog-link-btn" href="{{ route('registrar.registrar-menu.alumni.tracker') }}">Alumni Tracker</a>
            <a class="tog-link-btn" href="{{ route('registrar.registrar-menu.student-mgmt.student-records') }}">Student List</a>
        </div>
    </div>

    <div class="tog-summary">
        <div class="tog-stat"><span>Total Students</span><strong>{{ number_format((int) ($summary['total_students'] ?? 0)) }}</strong></div>
        <div class="tog-stat"><span>Tagged Graduates</span><strong>{{ number_format((int) ($summary['graduates'] ?? 0)) }}</strong></div>
        <div class="tog-stat"><span>Suspended Accounts</span><strong>{{ number_format((int) ($summary['suspended'] ?? 0)) }}</strong></div>
        <div class="tog-stat"><span>Matching Filter</span><strong>{{ number_format((int) ($summary['matching'] ?? $students->total())) }}</strong></div>
    </div>

    <form method="GET" action="{{ route('registrar.services.reports-admin.tagging-of-graduates') }}" class="tog-filter-card">
        <div class="tog-filter-grid">
            <div class="tog-field">
                <label for="tagSearchFilter">Search</label>
                <input class="tog-input-control" id="tagSearchFilter" name="q" value="{{ $search ?? '' }}" placeholder="Student no, name, or ID">
            </div>
            <div class="tog-field">
                <label for="tagSchoolYearFilter">School Year</label>
                <select class="tog-input-control" id="tagSchoolYearFilter" name="school_year">
                    <option value="">All</option>
                    @foreach(($schoolYears ?? []) as $option)
                        <option value="{{ $option }}" {{ ($schoolYear ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tog-field">
                <label for="tagSemesterFilter">Semester</label>
                <select class="tog-input-control" id="tagSemesterFilter" name="semester">
                    <option value="">All</option>
                    @foreach(($semesters ?? []) as $option)
                        <option value="{{ $option }}" {{ ($semester ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tog-field">
                <label for="tagProgramFilter">Program</label>
                <select class="tog-input-control" id="tagProgramFilter" name="program">
                    <option value="">All</option>
                    @foreach(($programs ?? []) as $option)
                        <option value="{{ $option }}" {{ ($program ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tog-field">
                <label for="tagYearLevelFilter">Year Level</label>
                <select class="tog-input-control" id="tagYearLevelFilter" name="year_level">
                    <option value="">All</option>
                    @foreach(($yearLevels ?? []) as $option)
                        <option value="{{ $option }}" {{ ($yearLevel ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tog-field">
                <label for="tagStatusFilter">Status</label>
                <select class="tog-input-control" id="tagStatusFilter" name="status">
                    <option value="">All</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="graduated" {{ ($status ?? '') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                    <option value="suspended" {{ ($status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <button type="submit" class="tog-filter-btn" id="tagFilterSetBtn">Apply</button>
            <a href="{{ route('registrar.services.reports-admin.tagging-of-graduates') }}" class="tog-clear-btn">Clear</a>
        </div>
    </form>

        <div class="tog-table-card">
            <table class="tog-table" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Year Level</th>
                        <th>Status</th>
                        <th style="text-align: center;">Graduate</th>
                        <th style="text-align: center;">Date Graduated</th>
                        <th style="text-align: center;">SO Number</th>
                        <th style="text-align: center;">SO Date</th>
                        <th style="text-align: center;">Suspend</th>
                        <th style="text-align: center;">Save</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                    @php
                        $tag = $taggings->get($student->id) ?: $student->graduateTagging;
                        $isGraduate = $tag && $tag->is_graduate;
                        $isSuspended = $tag && $tag->suspend_account;
                        $programLabel = $student->program ?: optional($student->canonicalCourse)->code ?: '-';
                    @endphp
                    <tr
                        data-student-id="{{ $student->id }}"
                        data-school-year="{{ $student->school_year ?: '' }}"
                        data-semester="{{ $student->semester ?: '' }}"
                        data-program="{{ $student->program ?: '' }}"
                        data-year-level="{{ $student->year_level ?: '' }}"
                        data-lock-graduate="0"
                        data-saved-graduate="{{ $tag && $tag->is_graduate ? '1' : '0' }}"
                        data-saved-suspend="{{ $tag && $tag->suspend_account ? '1' : '0' }}"
                        data-saved-suspend-remarks="{{ $tag ? ($tag->suspend_remarks ?: '') : '' }}"
                    >
                        <td style="text-align: center;">{{ (($students->currentPage() - 1) * $students->perPage()) + $index + 1 }}</td>
                        <td>{{ $student->student_no ?: '-' }}</td>
                        <td><div class="tog-student-name">{{ $student->name }}</div><div class="tog-muted">ID {{ $student->id }}</div></td>
                        <td>{{ $programLabel }}</td>
                        <td>{{ $student->year_level ?: '-' }}</td>
                        <td>
                            @if($isSuspended)
                                <span class="tog-badge tog-badge-suspended">Suspended</span>
                            @elseif($isGraduate)
                                <span class="tog-badge tog-badge-grad">Graduate</span>
                            @else
                                <span class="tog-badge tog-badge-pending">Pending</span>
                            @endif
                        </td>
                        <td style="text-align: center; white-space: nowrap;"><input type="checkbox" class="tog-check" data-tag-is-graduate style="vertical-align: middle;" {{ $tag && $tag->is_graduate ? 'checked' : '' }}></td>
                        <td style="text-align: center;"><input type="date" class="tog-input tog-input-date" data-tag-date-graduated value="{{ $tag && $tag->date_graduated ? $tag->date_graduated->format('Y-m-d') : '' }}"></td>
                        <td style="text-align: center;"><input type="text" class="tog-input tog-input-so" data-tag-so-number value="{{ $tag ? $tag->so_number : '' }}" placeholder="SO Number"></td>
                        <td style="text-align: center;"><input type="date" class="tog-input tog-input-date" data-tag-so-date value="{{ $tag && $tag->so_date ? $tag->so_date->format('Y-m-d') : '' }}"></td>
                        <td style="text-align: center; white-space: nowrap;"><input type="checkbox" class="tog-check" data-tag-suspend style="vertical-align: middle;" {{ $tag && $tag->suspend_account ? 'checked' : '' }}></td>
                        <td style="text-align: center;"><button type="button" class="req-btn-save tog-save-btn" data-tag-save>Save</button></td>
                    </tr>
                    @if($loop->last)
                    <tr id="tagFilterEmptyRow" style="display:none;"><td colspan="11"><div class="tog-empty">No students matched the selected filters.</div></td></tr>
                    @endif
                    @empty
                    <tr><td colspan="11"><div class="tog-empty">No students found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="tog-pagination">{{ $students->links() }}</div>
</div>

<div class="req-modal-overlay" id="tagConfirmModal" style="display:none;">
    <div class="req-modal-box" style="width: 460px;">
        <h3 class="req-modal-title" id="tagConfirmTitle" style="color:#006837; font-size: 1rem;">Confirm Action</h3>
        <p id="tagConfirmText" style="font-size: 0.88rem; color: #374151; margin-bottom: 16px; text-align: center;"></p>
        <div class="req-modal-actions" style="justify-content:center;">
            <button type="button" class="req-btn-cancel" id="tagConfirmCancel">Cancel</button>
            <button type="button" class="req-btn-save" id="tagConfirmProceed" style="min-width: 120px;">Confirm</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="tagRemarksModal" style="display:none;">
    <div class="req-modal-box" style="width: 500px;">
        <h3 class="req-modal-title" style="color:#b91c1c; font-size: 1rem;">Suspension Remarks Required</h3>
        <p style="font-size: 0.86rem; color: #374151; margin-bottom: 12px; text-align: center;">Please enter the reason for suspension before saving.</p>
        <div class="req-modal-field-group">
            <label class="req-modal-label" for="tagSuspendRemarksInput">Remarks</label>
            <textarea id="tagSuspendRemarksInput" class="req-modal-input" rows="3" placeholder="Enter suspension reason..."></textarea>
            <div id="tagSuspendRemarksError" style="display:none; margin-top:6px; color:#b91c1c; font-size:0.8rem; font-weight:600;">Remarks is required.</div>
        </div>
        <div class="req-modal-actions" style="justify-content:center; margin-top:14px;">
            <button type="button" class="req-btn-cancel" id="tagRemarksCancel">Cancel</button>
            <button type="button" class="req-btn-save" id="tagRemarksSave">Save</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="tagViewRemarksModal" style="display:none;">
    <div class="req-modal-box" style="width: 500px;">
        <h3 class="req-modal-title" style="color:#b91c1c; font-size: 1rem;">Suspension Remarks</h3>
        <p style="font-size: 0.84rem; color:#6b7280; margin-bottom:10px; text-align:center;">Selected account is currently suspended.</p>
        <div id="tagViewRemarksText" style="border:1px solid #efc9c9; background:#fff5f5; color:#7f1d1d; border-radius:8px; padding:10px 12px; font-size:0.85rem; line-height:1.5;"></div>
        <div class="req-modal-actions" style="justify-content:center; margin-top:14px;">
            <button type="button" class="req-btn-cancel" id="tagViewRemarksClose">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrf = @json(csrf_token());
    var updateTemplate = @json(route('registrar.services.reports-admin.tagging-of-graduates.update', ['student' => '__STUDENT__']));

    function buildUrl(studentId) {
        return String(updateTemplate).replace('__STUDENT__', String(studentId));
    }

    function requestJson(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: payload ? JSON.stringify(payload) : null
        }).then(function (response) {
            if (!response.ok) {
                return response.json().catch(function () { return {}; }).then(function (data) {
                    var message = 'Unable to save graduate tagging.';
                    if (data && data.errors) {
                        var keys = Object.keys(data.errors);
                        if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                            message = data.errors[keys[0]][0];
                        }
                    }
                    throw new Error(message);
                });
            }
            return response.json().catch(function () { return { ok: true }; });
        });
    }

    function setRowSuspendedVisual(row, isSuspended) {
        var cells = row.querySelectorAll('td');
        Array.prototype.forEach.call(cells, function (cell) {
            cell.style.backgroundColor = isSuspended ? '#fdecec' : '';
            cell.style.color = isSuspended ? '#7f1d1d' : '';
        });
    }

    function setRowReadOnly(row, isReadOnly) {
        var controls = row.querySelectorAll('input, select, textarea, button[data-tag-save]');
        Array.prototype.forEach.call(controls, function (control) {
            control.disabled = !!isReadOnly;
        });

        if (isReadOnly) {
            row.style.opacity = '0.92';
        } else {
            row.style.opacity = '';
        }
    }

    function refreshRowState(row) {
        var suspendControl = row.querySelector('[data-tag-suspend]');
        var isSuspended = !!(suspendControl && suspendControl.checked);
        setRowSuspendedVisual(row, isSuspended);
    }

    function updateStatusBadge(row) {
        var badge = row.querySelector('.tog-badge');
        if (!badge) return;

        var isGraduated = row.getAttribute('data-saved-graduate') === '1';
        var isSuspended = row.getAttribute('data-saved-suspend') === '1';

        badge.className = 'tog-badge ';
        if (isSuspended) {
            badge.className += 'tog-badge-suspended';
            badge.textContent = 'Suspended';
        } else if (isGraduated) {
            badge.className += 'tog-badge-grad';
            badge.textContent = 'Graduate';
        } else {
            badge.className += 'tog-badge-pending';
            badge.textContent = 'Pending';
        }
    }

    function todayYmd() {
        var d = new Date();
        var m = String(d.getMonth() + 1);
        var day = String(d.getDate());
        var y = String(d.getFullYear());
        if (m.length < 2) m = '0' + m;
        if (day.length < 2) day = '0' + day;
        return y + '-' + m + '-' + day;
    }

    function openConfirmModal(message, variant) {
        return new Promise(function (resolve) {
            var modal = document.getElementById('tagConfirmModal');
            var text = document.getElementById('tagConfirmText');
            var title = document.getElementById('tagConfirmTitle');
            var cancelBtn = document.getElementById('tagConfirmCancel');
            var proceedBtn = document.getElementById('tagConfirmProceed');

            if (!modal || !text || !title || !cancelBtn || !proceedBtn) {
                resolve(false);
                return;
            }

            text.textContent = message;
            if (variant === 'danger') {
                title.textContent = 'Confirm Suspension';
                title.style.color = '#b91c1c';
                proceedBtn.style.background = '#b91c1c';
                proceedBtn.style.borderColor = '#991b1b';
            } else {
                title.textContent = 'Confirm Action';
                title.style.color = '#006837';
                proceedBtn.style.background = '';
                proceedBtn.style.borderColor = '';
            }
            modal.style.display = 'flex';

            function cleanup(result) {
                modal.style.display = 'none';
                cancelBtn.removeEventListener('click', onCancel);
                proceedBtn.removeEventListener('click', onProceed);
                resolve(result);
            }

            function onCancel() { cleanup(false); }
            function onProceed() { cleanup(true); }

            cancelBtn.addEventListener('click', onCancel);
            proceedBtn.addEventListener('click', onProceed);
        });
    }

    function openViewRemarksModal(remarks) {
        var modal = document.getElementById('tagViewRemarksModal');
        var text = document.getElementById('tagViewRemarksText');
        var closeBtn = document.getElementById('tagViewRemarksClose');

        if (!modal || !text || !closeBtn) {
            return;
        }

        text.textContent = remarks || 'No remarks provided.';
        modal.style.display = 'flex';

        function closeModal() {
            modal.style.display = 'none';
            closeBtn.removeEventListener('click', closeModal);
        }

        closeBtn.addEventListener('click', closeModal);
    }

    function openSuspendRemarksModal(defaultValue) {
        return new Promise(function (resolve) {
            var modal = document.getElementById('tagRemarksModal');
            var input = document.getElementById('tagSuspendRemarksInput');
            var error = document.getElementById('tagSuspendRemarksError');
            var cancelBtn = document.getElementById('tagRemarksCancel');
            var saveBtn = document.getElementById('tagRemarksSave');

            if (!modal || !input || !error || !cancelBtn || !saveBtn) {
                resolve(null);
                return;
            }

            input.value = defaultValue || '';
            error.style.display = 'none';
            modal.style.display = 'flex';
            input.focus();

            function cleanup(result) {
                modal.style.display = 'none';
                cancelBtn.removeEventListener('click', onCancel);
                saveBtn.removeEventListener('click', onSave);
                resolve(result);
            }

            function onCancel() { cleanup(null); }

            function onSave() {
                var value = (input.value || '').trim();
                if (!value) {
                    error.style.display = 'block';
                    input.focus();
                    return;
                }
                cleanup(value);
            }

            cancelBtn.addEventListener('click', onCancel);
            saveBtn.addEventListener('click', onSave);
        });
    }

    Array.prototype.forEach.call(document.querySelectorAll('tr[data-student-id]'), function (row) {
        refreshRowState(row);

        var graduateCheckbox = row.querySelector('[data-tag-is-graduate]');
        var graduatedDateInput = row.querySelector('[data-tag-date-graduated]');

        if (graduateCheckbox && graduatedDateInput) {
            graduateCheckbox.addEventListener('change', function () {
                if (graduateCheckbox.checked && !graduatedDateInput.value) {
                    graduatedDateInput.value = todayYmd();
                }
            });
        }
    });

    function norm(value) {
        return String(value || '').toLowerCase().trim();
    }

    function applyAllFilters() {
        var schoolYearFilter = document.getElementById('tagSchoolYearFilter');
        var semesterFilter = document.getElementById('tagSemesterFilter');
        var programFilter = document.getElementById('tagProgramFilter');
        var yearLevelFilter = document.getElementById('tagYearLevelFilter');
        var statusFilter = document.getElementById('tagStatusFilter');

        if (!schoolYearFilter || !semesterFilter || !programFilter || !yearLevelFilter || !statusFilter) {
            return;
        }

        var rows = document.querySelectorAll('tr[data-student-id]');
        var visibleCount = 0;
        var selectedSchoolYear = norm(schoolYearFilter.value);
        var selectedSemester = norm(semesterFilter.value);
        var selectedProgram = norm(programFilter.value);
        var selectedYearLevel = norm(yearLevelFilter.value);
        var selectedStatus = norm(statusFilter.value || 'all');

        Array.prototype.forEach.call(rows, function (row) {
            var isGraduated = row.getAttribute('data-saved-graduate') === '1';
            var isSuspended = row.getAttribute('data-saved-suspend') === '1';
            var rowSchoolYear = norm(row.getAttribute('data-school-year'));
            var rowSemester = norm(row.getAttribute('data-semester'));
            var rowProgram = norm(row.getAttribute('data-program'));
            var rowYearLevel = norm(row.getAttribute('data-year-level'));
            var match = true;

            if (selectedSchoolYear && rowSchoolYear !== selectedSchoolYear) {
                match = false;
            }
            if (match && selectedSemester && rowSemester !== selectedSemester) {
                match = false;
            }
            if (match && selectedProgram && rowProgram !== selectedProgram) {
                match = false;
            }
            if (match && selectedYearLevel && rowYearLevel !== selectedYearLevel) {
                match = false;
            }

            if (match && selectedStatus === 'graduated') {
                match = isGraduated;
            } else if (match && selectedStatus === 'suspended') {
                match = isSuspended;
            } else if (match && selectedStatus === 'pending') {
                match = !isGraduated && !isSuspended;
            }

            row.style.display = match ? '' : 'none';
            if (match) {
                visibleCount += 1;
            }
        });

        var emptyRow = document.getElementById('tagFilterEmptyRow');
        if (emptyRow) {
            emptyRow.style.display = visibleCount ? 'none' : '';
        }
    }

    var setFilterButton = document.getElementById('tagFilterSetBtn');
    if (setFilterButton) {
        setFilterButton.addEventListener('click', applyAllFilters);
    }

    document.querySelectorAll('[data-tag-save]').forEach(function (button) {
        button.addEventListener('click', function () {
            var row = button.closest('tr[data-student-id]');
            if (!row) return;

            var studentId = row.getAttribute('data-student-id');
            if (!studentId) return;

            var isGraduate = !!row.querySelector('[data-tag-is-graduate]').checked;
            var isSuspended = !!row.querySelector('[data-tag-suspend]').checked;
            var wasGraduate = row.getAttribute('data-saved-graduate') === '1';
            var wasSuspended = row.getAttribute('data-saved-suspend') === '1';
            var savedSuspendRemarks = row.getAttribute('data-saved-suspend-remarks') || '';

            var flow = Promise.resolve(true);

            if (isGraduate && !wasGraduate) {
                flow = flow.then(function (ok) {
                    if (!ok) return false;
                    return openConfirmModal('Are you sure you want to tag this account as Graduate?');
                });
            }

            var suspendRemarksToSave = savedSuspendRemarks;
            if (isSuspended) {
                flow = flow.then(function (ok) {
                    if (!ok) return false;
                    if (!wasSuspended) {
                        return openConfirmModal('Are you sure you want to suspend this account?', 'danger');
                    }
                    return true;
                }).then(function (ok) {
                    if (!ok) return false;
                    if (!wasSuspended) {
                        return openSuspendRemarksModal('');
                    }
                    return savedSuspendRemarks || '';
                }).then(function (remarksOrFalse) {
                    if (remarksOrFalse === false || remarksOrFalse === null) {
                        return false;
                    }
                    suspendRemarksToSave = String(remarksOrFalse || '').trim();
                    if (!suspendRemarksToSave) {
                        return openSuspendRemarksModal('');
                    }
                    return true;
                });
            } else {
                suspendRemarksToSave = '';
            }

            flow.then(function (ok) {
                if (!ok) {
                    row.querySelector('[data-tag-suspend]').checked = wasSuspended;
                    refreshRowState(row);
                    return;
                }

                button.disabled = true;
                requestJson(buildUrl(studentId), 'PUT', {
                    is_graduate: isGraduate,
                    date_graduated: row.querySelector('[data-tag-date-graduated]').value || null,
                    so_number: row.querySelector('[data-tag-so-number]').value || null,
                    so_date: row.querySelector('[data-tag-so-date]').value || null,
                    suspend_account: isSuspended,
                    suspend_remarks: isSuspended ? suspendRemarksToSave : null
                }).then(function () {
                    row.setAttribute('data-saved-graduate', isGraduate ? '1' : '0');
                    row.setAttribute('data-saved-suspend', isSuspended ? '1' : '0');
                    row.setAttribute('data-saved-suspend-remarks', isSuspended ? suspendRemarksToSave : '');

                    updateStatusBadge(row);
                    refreshRowState(row);
                    applyAllFilters();

                    if (typeof showRegistrarToast === 'function') {
                        showRegistrarToast('Graduate tagging saved.');
                    }
                }).catch(function (error) {
                    alert(error.message || 'Unable to save graduate tagging.');
                    row.querySelector('[data-tag-suspend]').checked = wasSuspended;
                    refreshRowState(row);
                }).finally(function () {
                    button.disabled = false;
                });
            });
        });
    });

    Array.prototype.forEach.call(document.querySelectorAll('tr[data-student-id]'), function (row) {
        row.addEventListener('click', function (event) {
            if (event.target.closest('input, button, textarea, select, label, a')) {
                return;
            }

            var isSuspended = row.getAttribute('data-saved-suspend') === '1';
            if (!isSuspended) {
                return;
            }

            var remarks = row.getAttribute('data-saved-suspend-remarks') || '';
            openViewRemarksModal(remarks);
        });
    });

    window.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'tagConfirmModal') {
            document.getElementById('tagConfirmCancel').click();
        }
        if (event.target && event.target.id === 'tagRemarksModal') {
            document.getElementById('tagRemarksCancel').click();
        }
        if (event.target && event.target.id === 'tagViewRemarksModal') {
            document.getElementById('tagViewRemarksClose').click();
        }
    });
});
</script>
@endpush
