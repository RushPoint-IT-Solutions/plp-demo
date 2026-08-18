@extends('layouts.registrar')

@section('title', 'PLP - Attendance')
@section('page-title', 'ATTENDANCE')
@section('body-class', 'page-services-attendance')

@push('scripts')
    <script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
@endpush

@push('styles')
<style>
    .att-summary-card{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#fff;border:1px solid #dfe8e2;border-radius:8px;padding:12px 14px;margin-bottom:14px;}
    .att-summary-meta{display:flex;gap:18px;flex-wrap:wrap;}
    .att-summary-pill{display:flex;flex-direction:column;}
    .att-summary-label{font-size:.72rem;color:#66756b;font-weight:800;text-transform:uppercase;}
    .att-summary-value{font-size:.92rem;color:#123822;font-weight:800;}
    .att-back-btn{border:1px solid #146c43;background:#fff;color:#146c43;border-radius:7px;padding:7px 12px;font-weight:800;cursor:pointer;}
    .att-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px;}
    .att-date-input{border:1px solid #cfd9d2;border-radius:7px;padding:7px 10px;font-size:.85rem;}
    .att-status-select{border:1px solid #cfd9d2;border-radius:6px;padding:5px 8px;font-size:.82rem;background:#fff;}
    .att-status-select[data-status="Present"]{color:#146c43;font-weight:700;}
    .att-status-select[data-status="Absent"]{color:#b3261e;font-weight:700;}
    .att-status-select[data-status="Late"]{color:#8a5b00;font-weight:700;}
    .att-status-select[data-status="Excused"]{color:#4a5568;font-weight:700;}
    .att-save-note{font-size:.78rem;color:#66756b;}
    .att-counts{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:12px;}
    .att-count-chip{background:#f5faf7;border:1px solid #dfe8e2;border-radius:7px;padding:6px 12px;font-size:.8rem;font-weight:800;color:#143521;}
</style>
@endpush

@section('content')
<div class="pf-page" id="attendancePage" data-csrf="{{ csrf_token() }}">
    @if(session('status'))
        <div class="alert alert-{{ session('status_type', 'success') }}" role="alert">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('registrar.services.classroom-faculty.attendance') }}" class="sched-filter-bar at-top-row">
        <div class="at-search-block at-search-card">
            <span class="app-filter-label">Search</span>
            @include('registrar.components.search-bar', [
                'id' => 'attSearchInput',
                'name' => 'q',
                'value' => $search,
                'placeholder' => 'Search Course / Section / Subject / Faculty',
                'containerClass' => 'at-search-wrap',
            ])
        </div>

        <div class="at-config-card">
            <div class="at-config-title">System Configuration</div>
            <div class="at-config-grid">
                <div class="at-config-item">
                    <span class="at-config-inline-label">School Year:</span>
                    @include('registrar.components.listbox-select', [
                        'id' => 'attSchoolYear',
                        'name' => 'school_year',
                        'options' => $schoolYearOptions,
                        'selected' => $selectedSchoolYear,
                        'placeholder' => 'All School Years',
                    ])
                </div>

                <div class="at-config-item">
                    <span class="at-config-inline-label">Semester:</span>
                    @include('registrar.components.listbox-select', [
                        'id' => 'attSemester',
                        'name' => 'semester',
                        'options' => $semesterOptions,
                        'selected' => $selectedSemester,
                        'placeholder' => 'All Semesters',
                    ])
                </div>

                <div class="at-config-action">
                    <button type="submit" class="pf-btn-new at-btn-set">Set</button>
                </div>
            </div>
        </div>
    </form>

    <div id="attAssignmentWrap" class="student-table-wrapper table-responsive att-assignment-wrap">
        <table class="student-table registrar-table svc-table" id="attAssignmentTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Section</th>
                    <th>Subject Code</th>
                    <th>Description</th>
                    <th>Professor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classRows as $index => $subject)
                    @php
                        $sectionLabel = trim((string) optional($subject->canonicalCourse)->code . ' ' . (string) $subject->year_section);
                        $professorLabel = trim((string) optional($subject->facultyModel)->name) ?: trim((string) $subject->faculty);
                    @endphp
                    <tr class="att-assignment-row" data-subject-id="{{ $subject->id }}" data-section="{{ $sectionLabel ?: 'N/A' }}" data-subject-line="{{ $subject->code }} - {{ $subject->name }}" style="cursor:pointer;">
                        <td>{{ ($classRows->firstItem() ?? 0) + $index }}</td>
                        <td>{{ $sectionLabel !== '' ? $sectionLabel : 'N/A' }}</td>
                        <td>{{ $subject->code }}</td>
                        <td>{{ $subject->name }}</td>
                        <td>{{ $professorLabel !== '' ? $professorLabel : 'TBA' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No sections found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="app-table-pager">
        {{ $classRows->links() }}
    </div>

    <div id="attResult" style="display:none;">
        <div class="att-summary-card">
            <div class="att-summary-meta">
                <div class="att-summary-pill"><span class="att-summary-label">Section</span><span class="att-summary-value" id="attSummarySection">-</span></div>
                <div class="att-summary-pill"><span class="att-summary-label">Subject</span><span class="att-summary-value" id="attSummarySubject">-</span></div>
            </div>
            <button type="button" class="att-back-btn" onclick="showAttendanceAssignments()">&larr; Back to List</button>
        </div>

        <div class="att-toolbar">
            <label class="app-filter-label" for="attDate">Session Date</label>
            <input type="date" id="attDate" class="att-date-input">
            <button type="button" class="pf-btn-new" id="attSaveBtn">Save Attendance</button>
            <button type="button" class="svc-btn-excel" id="attImportBtn">Import Attendance (CSV)</button>
            <span class="att-save-note" id="attSaveNote"></span>
        </div>

        <div class="att-counts" id="attCounts"></div>

        <div class="student-table-wrapper table-responsive">
            <table class="student-table registrar-table svc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="attDetailBody"></tbody>
            </table>
        </div>
    </div>

    <div class="pf-modal-overlay" id="attImportModal" style="display:none;">
        <div class="pf-modal-box" style="max-width:520px;">
            <div class="pf-modal-title">Import Attendance (CSV)</div>
            <p style="font-size:.8rem;color:#666;margin:0 0 12px;">Columns: <strong>Student No, Date, Status</strong> (Status must be Present, Absent, Late, or Excused). Only students already enrolled in this section will be matched.</p>
            <input type="file" id="attImportFile" accept=".csv,text/csv" class="req-modal-input" style="margin-bottom:12px;">
            <div id="attImportErrors" style="display:none;max-height:160px;overflow-y:auto;margin-bottom:12px;padding:8px 12px;background:#fdecec;border:1px solid #efb8b8;border-radius:6px;font-size:.78rem;color:#9f1d1d;"></div>
            <div class="pf-modal-actions">
                <button type="button" class="pf-modal-btn-cancel" onclick="closeAttImportModal()">Cancel</button>
                <button type="button" class="pf-modal-btn-save" id="attImportSubmit">Import</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('attendancePage');
    var assignmentWrap = document.getElementById('attAssignmentWrap');
    var pager = document.querySelector('.app-table-pager');
    var result = document.getElementById('attResult');
    var detailBody = document.getElementById('attDetailBody');
    var summarySection = document.getElementById('attSummarySection');
    var summarySubject = document.getElementById('attSummarySubject');
    var dateInput = document.getElementById('attDate');
    var saveBtn = document.getElementById('attSaveBtn');
    var saveNote = document.getElementById('attSaveNote');
    var countsWrap = document.getElementById('attCounts');
    var importBtn = document.getElementById('attImportBtn');
    var importModal = document.getElementById('attImportModal');
    var importFile = document.getElementById('attImportFile');
    var importSubmit = document.getElementById('attImportSubmit');
    var importErrors = document.getElementById('attImportErrors');

    var currentSubjectId = null;
    var STATUSES = @json($statuses);

    function todayStr() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function renderCounts(summary) {
        if (!summary) { countsWrap.innerHTML = ''; return; }
        countsWrap.innerHTML =
            '<span class="att-count-chip">Sessions recorded: ' + summary.sessions + '</span>' +
            '<span class="att-count-chip">Present: ' + summary.present + '</span>' +
            '<span class="att-count-chip">Absent: ' + summary.absent + '</span>' +
            '<span class="att-count-chip">Late: ' + summary.late + '</span>' +
            '<span class="att-count-chip">Excused: ' + summary.excused + '</span>';
    }

    function statusOptions(selected) {
        return STATUSES.map(function (status) {
            return '<option value="' + status + '"' + (status === selected ? ' selected' : '') + '>' + status + '</option>';
        }).join('');
    }

    function renderStudents(payload) {
        summarySection.textContent = payload.section || 'N/A';
        summarySubject.textContent = payload.subject || 'N/A';
        dateInput.value = payload.date;
        renderCounts(payload.summary);

        detailBody.innerHTML = '';
        if (!payload.students.length) {
            detailBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:16px;">No students enrolled in this section.</td></tr>';
            return;
        }

        payload.students.forEach(function (student, index) {
            var tr = document.createElement('tr');
            tr.setAttribute('data-student-id', student.id);
            tr.innerHTML =
                '<td>' + (index + 1) + '</td>' +
                '<td>' + student.student_no + '</td>' +
                '<td>' + student.name + '</td>' +
                '<td><select class="att-status-select" data-status="' + student.status + '">' + statusOptions(student.status) + '</select></td>';
            detailBody.appendChild(tr);
        });

        detailBody.querySelectorAll('.att-status-select').forEach(function (select) {
            select.addEventListener('change', function () {
                select.setAttribute('data-status', select.value);
            });
        });
    }

    function loadStudents(subjectId, date) {
        currentSubjectId = subjectId;
        var url = '{{ url("registrar/services/classroom-faculty/attendance") }}/' + subjectId + '/students?date=' + encodeURIComponent(date || todayStr());

        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                renderStudents(payload);
                assignmentWrap.style.display = 'none';
                if (pager) pager.style.display = 'none';
                result.style.display = 'block';
            });
    }

    function showAttendanceAssignments() {
        result.style.display = 'none';
        assignmentWrap.style.display = 'block';
        if (pager) pager.style.display = '';
        currentSubjectId = null;
    }
    window.showAttendanceAssignments = showAttendanceAssignments;

    assignmentWrap.addEventListener('click', function (event) {
        var row = event.target.closest('.att-assignment-row');
        if (!row) return;
        loadStudents(row.getAttribute('data-subject-id'), todayStr());
    });

    dateInput.addEventListener('change', function () {
        if (currentSubjectId) loadStudents(currentSubjectId, dateInput.value);
    });

    saveBtn.addEventListener('click', function () {
        if (!currentSubjectId) return;

        var records = [];
        detailBody.querySelectorAll('tr[data-student-id]').forEach(function (tr) {
            var select = tr.querySelector('.att-status-select');
            records.push({
                student_id: parseInt(tr.getAttribute('data-student-id'), 10),
                status: select ? select.value : 'Present'
            });
        });

        if (!records.length) return;

        saveBtn.disabled = true;
        saveNote.textContent = 'Saving...';

        fetch('{{ url("registrar/services/classroom-faculty/attendance") }}/' + currentSubjectId + '/students', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': page.dataset.csrf
            },
            credentials: 'same-origin',
            body: JSON.stringify({ date: dateInput.value, records: records })
        })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                renderCounts(payload.summary);
                saveNote.textContent = payload.message || 'Saved.';
            })
            .catch(function () {
                saveNote.textContent = 'Unable to save attendance.';
            })
            .finally(function () {
                saveBtn.disabled = false;
                setTimeout(function () { saveNote.textContent = ''; }, 4000);
            });
    });

    importBtn.addEventListener('click', function () {
        if (!currentSubjectId) return;
        importFile.value = '';
        importErrors.style.display = 'none';
        importErrors.innerHTML = '';
        importModal.style.display = 'flex';
    });

    window.closeAttImportModal = function () {
        importModal.style.display = 'none';
    };

    importSubmit.addEventListener('click', function () {
        if (!currentSubjectId || !importFile.files.length) {
            alert('Please choose a CSV file first.');
            return;
        }

        var formData = new FormData();
        formData.append('file', importFile.files[0]);
        formData.append('dry_run', '0');

        importSubmit.disabled = true;
        importSubmit.textContent = 'Importing...';

        fetch('{{ url("registrar/services/classroom-faculty/attendance") }}/' + currentSubjectId + '/import', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': page.dataset.csrf
            },
            credentials: 'same-origin',
            body: formData
        })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                if (payload.errors && payload.errors.length) {
                    importErrors.style.display = 'block';
                    importErrors.innerHTML = payload.errors.map(function (line) {
                        return '<div>' + line + '</div>';
                    }).join('');
                }
                renderCounts(payload.summary);
                if (!payload.errors || !payload.errors.length) {
                    closeAttImportModal();
                }
                loadStudents(currentSubjectId, dateInput.value);
                alert(payload.message || 'Import completed.');
            })
            .catch(function () {
                alert('Unable to import the file.');
            })
            .finally(function () {
                importSubmit.disabled = false;
                importSubmit.textContent = 'Import';
            });
    });
});
</script>
@endpush
