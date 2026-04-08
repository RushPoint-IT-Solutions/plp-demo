@extends('layouts.registrar')

@section('title', 'PLP - Tagging of Graduates')
@section('page-title', 'TAGGING OF GRADUATES')

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <!-- Filters -->
        <div class="app-filter-bar">
            <div class="app-filter-row" style="align-items: flex-end;">
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">School Year:</label>
                    <select class="app-filter-select">
                        <option>2025-2026</option>
                        <option>2024-2025</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Semester</label>
                    <select class="app-filter-select">
                        <option>First</option>
                        <option>Second</option>
                        <option>Summer</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:2;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Program</label>
                    <select class="app-filter-select">
                        <option>-Select Program-</option>
                        <option>BSCS</option>
                        <option>BSIT</option>
                        <option>BSED</option>
                        <option>BSBA</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform: uppercase;">Year Level</label>
                    <select class="app-filter-select">
                        <option>First</option>
                        <option>Second</option>
                        <option>Third</option>
                        <option>Fourth</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:15px;">
                <button type="button" class="req-btn-save" style="min-width: 120px; font-weight: 700;">Set</button>
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
                    <tr data-student-id="{{ $student->id }}">
                        <td style="text-align: center;">{{ (($students->currentPage() - 1) * $students->perPage()) + $index + 1 }}</td>
                        <td>{{ $student->student_no ?: '-' }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->program ?: '-' }}</td>
                        <td>{{ $student->year_level ?: '-' }}</td>
                        <td style="text-align: center;"><input type="checkbox" class="tog-check" data-tag-is-graduate {{ $tag && $tag->is_graduate ? 'checked' : '' }}></td>
                        <td style="text-align: center;"><input type="date" class="tog-input tog-input-date" data-tag-date-graduated value="{{ $tag && $tag->date_graduated ? $tag->date_graduated->format('Y-m-d') : '' }}"></td>
                        <td style="text-align: center;"><input type="text" class="tog-input tog-input-so" data-tag-so-number value="{{ $tag ? $tag->so_number : '' }}" placeholder="SO Number"></td>
                        <td style="text-align: center;"><input type="date" class="tog-input tog-input-date" data-tag-so-date value="{{ $tag && $tag->so_date ? $tag->so_date->format('Y-m-d') : '' }}"></td>
                        <td style="text-align: center;">
                            <input type="checkbox" class="tog-check" data-tag-suspend {{ $tag && $tag->suspend_account ? 'checked' : '' }}>
                            <button type="button" class="req-btn-save tog-save-btn" data-tag-save>Save</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" style="text-align:center; color:#666;">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="app-table-pager">{{ $students->links() }}</div>

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

    document.querySelectorAll('[data-tag-save]').forEach(function (button) {
        button.addEventListener('click', function () {
            var row = button.closest('tr[data-student-id]');
            if (!row) return;
            var studentId = row.getAttribute('data-student-id');
            if (!studentId) return;

            button.disabled = true;
            requestJson(buildUrl(studentId), 'PUT', {
                is_graduate: !!row.querySelector('[data-tag-is-graduate]').checked,
                date_graduated: row.querySelector('[data-tag-date-graduated]').value || null,
                so_number: row.querySelector('[data-tag-so-number]').value || null,
                so_date: row.querySelector('[data-tag-so-date]').value || null,
                suspend_account: !!row.querySelector('[data-tag-suspend]').checked
            }).then(function () {
                if (typeof showRegistrarToast === 'function') {
                    showRegistrarToast('Graduate tagging saved.');
                }
            }).catch(function (error) {
                alert(error.message || 'Unable to save graduate tagging.');
            }).finally(function () {
                button.disabled = false;
            });
        });
    });
});
</script>
@endpush
