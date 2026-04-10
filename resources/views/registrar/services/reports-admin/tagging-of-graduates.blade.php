@extends('layouts.registrar')

@section('title', 'PLP - Tagging of Graduates')
@section('page-title', 'TAGGING OF GRADUATES')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <!-- Filters -->
        <div class="app-filter-bar">
            <div class="app-filter-row" style="align-items: flex-end; flex-wrap: nowrap; gap: 10px;">
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">School Year:</label>
                    <select class="app-filter-select" id="tagSchoolYearFilter">
                        <option value="all">All</option>
                        <option>2025-2026</option>
                        <option>2024-2025</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Semester</label>
                    <select class="app-filter-select" id="tagSemesterFilter">
                        <option value="all">All</option>
                        <option>First</option>
                        <option>Second</option>
                        <option>Summer</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1.2;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Program</label>
                    <select class="app-filter-select" id="tagProgramFilter">
                        <option value="all">All</option>
                        <option>BSCS</option>
                        <option>BSIT</option>
                        <option>BSED</option>
                        <option>BSBA</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Year Level</label>
                    <select class="app-filter-select" id="tagYearLevelFilter">
                        <option value="all">All</option>
                        <option>First</option>
                        <option>Second</option>
                        <option>Third</option>
                        <option>Fourth</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1; min-width:160px;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Status</label>
                    <select class="app-filter-select" id="tagStatusFilter">
                        <option value="all">All</option>
                        <option value="graduated">Graduated</option>
                        <option value="suspended">Suspended</option>
                        <option value="active">Active</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:0 0 auto; min-width:120px;">
                    <label class="app-filter-label" style="visibility:hidden;">Set</label>
                    <button type="button" class="req-btn-save" id="tagFilterSetBtn" style="width:100%; min-width: 120px; font-weight: 700; height:36px;">Set</button>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="ga-table-wrap app-table-wrap">
            <table class="ga-table app-table" style="min-width: 1000px;" data-no-auto-pager="1">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th style="text-align: center;">Tag As Graduate</th>
                        <th style="text-align: center;">Date Graduated</th>
                        <th style="text-align: center;">SO Num</th>
                        <th style="text-align: center;">SO Date</th>
                        <th style="text-align: center;">Suspend Account</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                    @php($tag = $taggings->get($student->id))
                    <tr
                        data-student-id="{{ $student->id }}"
                        data-school-year="{{ $student->school_year ?: '' }}"
                        data-semester="{{ $student->semester ?: '' }}"
                        data-program="{{ $student->program ?: '' }}"
                        data-year-level="{{ $student->year_level ?: '' }}"
                        data-lock-graduate="{{ $tag && $tag->is_graduate ? '1' : '0' }}"
                        data-saved-graduate="{{ $tag && $tag->is_graduate ? '1' : '0' }}"
                        data-saved-suspend="{{ $tag && $tag->suspend_account ? '1' : '0' }}"
                        data-saved-suspend-remarks="{{ $tag ? ($tag->suspend_remarks ?: '') : '' }}"
                    >
                        <td style="text-align: center;">{{ (($students->currentPage() - 1) * $students->perPage()) + $index + 1 }}</td>
                        <td>{{ $student->student_no ?: '-' }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->program ?: '-' }}</td>
                        <td>{{ $student->year_level ?: '-' }}</td>
                        <td style="text-align: center; white-space: nowrap;"><input type="checkbox" class="tog-check" data-tag-is-graduate style="vertical-align: middle;" {{ $tag && $tag->is_graduate ? 'checked' : '' }}></td>
                        <td style="text-align: center;"><input type="date" class="tog-input tog-input-date" data-tag-date-graduated value="{{ $tag && $tag->date_graduated ? $tag->date_graduated->format('Y-m-d') : '' }}"></td>
                        <td style="text-align: center;"><input type="text" class="tog-input tog-input-so" data-tag-so-number value="{{ $tag ? $tag->so_number : '' }}" placeholder="SO Number"></td>
                        <td style="text-align: center;"><input type="date" class="tog-input tog-input-date" data-tag-so-date value="{{ $tag && $tag->so_date ? $tag->so_date->format('Y-m-d') : '' }}"></td>
                        <td style="text-align: center; white-space: nowrap;">
                            <input type="checkbox" class="tog-check" data-tag-suspend style="vertical-align: middle; margin-right:10px;" {{ $tag && $tag->suspend_account ? 'checked' : '' }}>
                            <button type="button" class="req-btn-save tog-save-btn" data-tag-save style="vertical-align: middle;">Save</button>
                        </td>
                    </tr>
                    @if($loop->last)
                    <tr id="tagFilterEmptyRow" style="display:none;"><td colspan="10" style="text-align:center; color:#666;">No students matched the selected filters.</td></tr>
                    @endif
                    @empty
                    <tr><td colspan="10" style="text-align:center; color:#666;">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="app-table-pager">{{ $students->links() }}</div>

    </div>
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

        var isGraduateLocked = row.getAttribute('data-lock-graduate') === '1';
        setRowReadOnly(row, isGraduateLocked);
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
                if (row.getAttribute('data-lock-graduate') === '1') {
                    return;
                }

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

            if (selectedSchoolYear !== 'all' && rowSchoolYear !== selectedSchoolYear) {
                match = false;
            }
            if (match && selectedSemester !== 'all' && rowSemester !== selectedSemester) {
                match = false;
            }
            if (match && selectedProgram !== 'all' && rowProgram !== selectedProgram) {
                match = false;
            }
            if (match && selectedYearLevel !== 'all' && rowYearLevel !== selectedYearLevel) {
                match = false;
            }

            if (match && selectedStatus === 'graduated') {
                match = isGraduated;
            } else if (match && selectedStatus === 'suspended') {
                match = isSuspended;
            } else if (match && selectedStatus === 'active') {
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

            if (row.getAttribute('data-lock-graduate') === '1') {
                return;
            }

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

                    if (isGraduate) {
                        row.setAttribute('data-lock-graduate', '1');
                    }

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
                    if (row.getAttribute('data-lock-graduate') !== '1') {
                        button.disabled = false;
                    }
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
