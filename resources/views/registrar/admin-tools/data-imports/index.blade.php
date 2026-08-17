@extends('layouts.registrar')

@section('title', 'PLP - Data Imports')
@section('page-title', 'DATA IMPORTS')

@section('content')
<div
    class="pf-page di-page"
    id="dataImportsPage"
    data-csrf="{{ csrf_token() }}"
>
    <style>
        .di-intro { color:#5a6b62; font-size:.88rem; margin:0 0 16px; max-width:760px; line-height:1.5; }
        .di-grid { display:grid; grid-template-columns:260px 1fr; gap:16px; align-items:start; }
        .di-type-list { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:10px; display:flex; flex-direction:column; gap:4px; }
        .di-type-item { display:flex; flex-direction:column; gap:2px; width:100%; text-align:left; border:0; background:transparent; border-radius:7px; padding:10px 12px; cursor:pointer; }
        .di-type-item.is-active { background:#eef6f1; }
        .di-type-item strong { color:#123822; font-size:.88rem; }
        .di-type-item span { color:#66756b; font-size:.76rem; }
        .di-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:18px; }
        .di-panel h2 { margin:0 0 6px; color:#123822; font-size:1.05rem; font-weight:800; }
        .di-panel p.di-desc { margin:0 0 16px; color:#66756b; font-size:.84rem; line-height:1.5; }
        .di-field-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
        .di-field label { display:block; margin-bottom:5px; color:#46564a; font-size:.78rem; font-weight:800; text-transform:uppercase; }
        .di-select, .di-file-input { width:100%; min-height:38px; border:1px solid #cfd9d2; border-radius:7px; background:#fff; color:#143521; padding:8px 10px; }
        .di-note { display:block; margin:6px 0 14px; color:#66756b; font-size:.78rem; line-height:1.45; }
        .di-link { color:#006837; font-weight:700; text-decoration:underline; }
        .di-actions { display:flex; justify-content:flex-end; }
        .di-btn { border:0; border-radius:7px; background:#146c43; color:#fff; cursor:pointer; font-weight:800; min-height:38px; padding:8px 18px; }
        .di-btn:disabled { cursor:not-allowed; opacity:.5; }
        .di-btn-outline { background:#fff; color:#146c43; border:1px solid #146c43; }
        .di-btn-outline:hover { background:#eef6f1; }
        .di-validated-badge { display:none; align-items:center; gap:6px; margin-right:auto; color:#17633a; font-size:.82rem; font-weight:700; }
        .di-validated-badge.show { display:inline-flex; }
        .di-actions { align-items:center; }
        .di-alert { display:none; margin-bottom:14px; border-radius:8px; padding:10px 12px; font-weight:700; font-size:.85rem; }
        .di-alert.show { display:block; }
        .di-alert.success { background:#e8f6ee; color:#17633a; border:1px solid #bfdfcc; }
        .di-alert.warn { background:#fff5d8; color:#8a5b00; border:1px solid #ead28a; }
        .di-alert.error { background:#fdecec; color:#9f1d1d; border:1px solid #efb8b8; }
        .di-errors { max-height:180px; overflow-y:auto; margin:0 0 14px; padding:8px 12px; background:#fdecec; border:1px solid #efb8b8; border-radius:6px; font-size:.78rem; color:#9f1d1d; }
        .di-errors div { padding:2px 0; }
        .di-table-wrap { overflow:auto; border:1px solid #e3ece6; border-radius:8px; }
        .di-table { width:100%; min-width:680px; border-collapse:collapse; }
        .di-table th, .di-table td { padding:10px 12px; border-bottom:1px solid #edf3ef; text-align:left; vertical-align:top; font-size:.86rem; }
        .di-table th { background:#f5faf7; color:#46564a; font-size:.74rem; font-weight:800; text-transform:uppercase; }
        .di-table tr:last-child td { border-bottom:0; }
        .di-muted { color:#66756b; font-size:.82rem; }
        @media (max-width: 900px) {
            .di-grid { grid-template-columns:1fr; }
            .di-field-row { grid-template-columns:1fr; }
        }
    </style>

    <p class="di-intro">Bulk-import data using CSV files instead of entering records one at a time. Pick an import type on the left, download its template, fill it in, and upload it here.</p>

    <div class="di-grid">
        <div class="di-type-list" id="diTypeList"></div>

        <section class="di-panel">
            <h2 id="diPanelTitle"></h2>
            <p class="di-desc" id="diPanelDesc"></p>

            <div class="di-alert" id="diAlert"></div>

            <form id="diForm">
                <div class="di-field-row" id="diTermFields" style="display:none;">
                    <div class="di-field">
                        <label for="diSchoolYear">School Year</label>
                        <select class="di-select" id="diSchoolYear">
                            <option value="">- Select School Year -</option>
                            @foreach($schoolYearOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="di-field">
                        <label for="diSemester">Semester</label>
                        <select class="di-select" id="diSemester">
                            <option value="">- Select Semester -</option>
                            @foreach($semesterOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="di-field">
                    <label for="diFile">CSV File</label>
                    <input type="file" class="di-file-input" id="diFile" accept=".csv,text/csv" required>
                    <span class="di-note" id="diNote"></span>
                </div>

                <div class="di-actions">
                    <span class="di-validated-badge" id="diValidatedBadge">&#10003; File checked, ready to import</span>
                    <button type="submit" class="di-btn di-btn-outline" id="diValidateBtn">Validate File</button>
                    <button type="button" class="di-btn" id="diConfirmBtn" disabled>Confirm Import</button>
                </div>
            </form>

            <div id="diErrors" class="di-errors" style="display:none;"></div>

            <div class="di-table-wrap" style="margin-top:14px;" id="diTableWrap">
                <table class="di-table">
                    <thead id="diTableHead"></thead>
                    <tbody id="diResultRows">
                        <tr><td class="di-muted">Import results will appear here.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('dataImportsPage');
    if (!page) return;

    var IMPORT_TYPES = [
        {
            key: 'room-assignments',
            label: 'Room Assignments',
            hint: 'Assign rooms per section and subject/course',
            title: 'Room Assignments (Per Section & Subject)',
            desc: 'Upload exact room and schedule assignments for specific class offerings instead of using Auto Generate. Each row must match an existing class offering by Subject Code + Section within the school year/semester selected below.',
            importUrl: '{{ route('registrar.admin-tools.data-imports.room-assignments.import') }}',
            templateUrl: '{{ route('registrar.admin-tools.data-imports.room-assignments.template') }}',
            needsTerm: true,
            note: 'Columns: Subject Code, Section, Component (Lecture or Laboratory), Room Number, Day, Start Time, End Time. Rows that don\'t match an existing class offering, reference a room that doesn\'t exist, or conflict with an existing schedule are skipped and listed below.',
            resultColumns: ['Class Offering', 'Section', 'Component', 'Room', 'Schedule', 'Status'],
            resultRow: function (item) {
                return [
                    '<strong>' + esc(item.course_code) + '</strong><div class="di-muted">' + esc(item.subject_name) + '</div>',
                    esc(item.section_id),
                    esc(item.schedule_component_type),
                    esc(item.room_code || 'TBA'),
                    esc(item.day) + ' ' + esc(item.start_time) + '-' + esc(item.end_time),
                    esc(item.assignment_status)
                ];
            }
        },
        {
            key: 'rooms',
            label: 'Rooms',
            hint: 'Add rooms to Room File in bulk',
            title: 'Rooms',
            desc: 'Create room records in bulk. Buildings and hallways are created automatically if they don\'t already exist. Imported rooms have no allowed subjects yet — assign those afterward in Room File.',
            importUrl: '{{ route('registrar.admin-tools.data-imports.rooms.import') }}',
            templateUrl: '{{ route('registrar.admin-tools.data-imports.rooms.template') }}',
            needsTerm: false,
            note: 'Columns: Building, Hallway, Room Number, Floor, Capacity.',
            resultColumns: null
        },
        {
            key: 'course-room',
            label: 'Course Room',
            hint: 'Which rooms allow which courses/programs',
            title: 'Course Room Assignments',
            desc: 'Link rooms to the programs/courses allowed to use them. This only adds links — it never removes an existing room-course assignment.',
            importUrl: '{{ route('registrar.admin-tools.data-imports.course-room.import') }}',
            templateUrl: '{{ route('registrar.admin-tools.data-imports.course-room.template') }}',
            needsTerm: false,
            note: 'Columns: Room Number, Course Code. One row per Room/Course pair — repeat the room number on multiple rows to link it to multiple courses.',
            resultColumns: null
        },
        {
            key: 'schedules',
            label: 'Schedules',
            hint: 'Days/time/room/instructor per offering',
            title: 'Schedules',
            desc: 'Set the day, time, room, and instructor directly on existing class offerings (the same fields shown on Class Schedule Preparation). Each row must match an existing class offering by Subject Code + Section within the School Year/Semester you type in the row.',
            importUrl: '{{ route('registrar.admin-tools.data-imports.schedules.import') }}',
            templateUrl: '{{ route('registrar.admin-tools.data-imports.schedules.template') }}',
            needsTerm: false,
            note: 'Columns: Subject Code, Section, School Year, Semester, Days, Start Time, End Time, Room Number (optional), Faculty Code (optional). Room Number and Faculty Code are optional — leave the column blank/omit it to only update the day/time.',
            resultColumns: ['Class Offering', 'Section', 'Room', 'Schedule', 'Status'],
            resultRow: function (item) {
                return [
                    '<strong>' + esc(item.course_code) + '</strong><div class="di-muted">' + esc(item.subject_name) + '</div>',
                    esc(item.section_id),
                    esc(item.room_code || 'TBA'),
                    esc(item.day) + ' ' + esc(item.start_time) + '-' + esc(item.end_time),
                    esc(item.assignment_status)
                ];
            }
        },
        {
            key: 'section-offerings',
            label: 'Section Offers',
            hint: 'Open a new section from the curriculum',
            title: 'Section Offerings',
            desc: 'Create new class offerings (sections) from the program\'s published curriculum. Rows sharing the same Course Code + School Year + Semester + Year Level + Section are grouped into a single section; each Subject Code must exist in that program\'s published curriculum for the matching year level and semester, or it is skipped. A published curriculum is required for the course before importing.',
            importUrl: '{{ route('registrar.admin-tools.data-imports.section-offerings.import') }}',
            templateUrl: '{{ route('registrar.admin-tools.data-imports.section-offerings.template') }}',
            needsTerm: false,
            note: 'Columns: Course Code, School Year, Semester, Year Level, Section, Subject Code, Adviser (optional), Slots (optional). One row per subject in the section — repeat the Course/Year/Semester/Level/Section on multiple rows to list every subject for that section.',
            resultColumns: null
        },
        {
            key: 'subject-file',
            label: 'Subject File',
            hint: 'Add/update catalog subjects in bulk',
            title: 'Subject File (Catalog)',
            desc: 'Create or update subjects in the course catalog (Subject File) in bulk. If a Code already exists in the catalog, that subject is updated instead of duplicated.',
            importUrl: '{{ route('registrar.admin-tools.data-imports.subject-file.import') }}',
            templateUrl: '{{ route('registrar.admin-tools.data-imports.subject-file.template') }}',
            needsTerm: false,
            note: 'Columns: Code, Title, Lec (optional), Lab (optional), Hours, Course Type (Professional/Major, Gen Education/Minor, GE, or Elective), Core/Applied/Specialized (optional, 1 or 0).',
            resultColumns: null
        },
        {
            key: 'curriculum-subjects',
            label: 'Curriculum Subjects',
            hint: 'Map catalog subjects into a curriculum year',
            title: 'Curriculum Subjects',
            desc: 'Attach catalog subjects to a specific year level and semester within a course\'s curriculum year (the same mapping the Curriculum File setup screen builds one course at a time). The subject must already exist in Subject File and the target curriculum year must already exist in Curriculum File.',
            importUrl: '{{ route('registrar.admin-tools.data-imports.curriculum-subjects.import') }}',
            templateUrl: '{{ route('registrar.admin-tools.data-imports.curriculum-subjects.template') }}',
            needsTerm: false,
            note: 'Columns: Course Code, Curriculum Year Code (optional — defaults to the course\'s most recent curriculum year), Year Level, Semester, Subject Code, Credited Units (optional).',
            resultColumns: null
        }
    ];

    var typeList = document.getElementById('diTypeList');
    var panelTitle = document.getElementById('diPanelTitle');
    var panelDesc = document.getElementById('diPanelDesc');
    var termFields = document.getElementById('diTermFields');
    var noteEl = document.getElementById('diNote');
    var form = document.getElementById('diForm');
    var alertBox = document.getElementById('diAlert');
    var errorsBox = document.getElementById('diErrors');
    var tableWrap = document.getElementById('diTableWrap');
    var tableHead = document.getElementById('diTableHead');
    var resultRows = document.getElementById('diResultRows');
    var validateBtn = document.getElementById('diValidateBtn');
    var confirmBtn = document.getElementById('diConfirmBtn');
    var validatedBadge = document.getElementById('diValidatedBadge');
    var fileInput = document.getElementById('diFile');

    var activeType = IMPORT_TYPES[0];
    var isValidated = false;

    function esc(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'})[char];
        });
    }

    function renderTypeList() {
        typeList.innerHTML = IMPORT_TYPES.map(function (type, index) {
            return '<button type="button" class="di-type-item' + (type.key === activeType.key ? ' is-active' : '') + '" data-di-type="' + type.key + '">'
                + '<strong>' + esc(type.label) + '</strong>'
                + '<span>' + esc(type.hint) + '</span>'
                + '</button>';
        }).join('');
    }

    function resetResults() {
        alertBox.className = 'di-alert';
        alertBox.textContent = '';
        errorsBox.style.display = 'none';
        errorsBox.innerHTML = '';
        tableHead.innerHTML = '';
        resultRows.innerHTML = '<tr><td class="di-muted">Import results will appear here.</td></tr>';
        setValidated(false);
    }

    function setValidated(value) {
        isValidated = value;
        validatedBadge.classList.toggle('show', value);
        confirmBtn.disabled = !value;
        validateBtn.textContent = value ? 'Re-check File' : 'Validate File';
    }

    function selectType(key) {
        var type = IMPORT_TYPES.filter(function (item) { return item.key === key; })[0];
        if (!type) return;

        activeType = type;
        renderTypeList();

        panelTitle.textContent = type.title;
        panelDesc.textContent = type.desc;
        noteEl.innerHTML = type.note + ' <a href="' + type.templateUrl + '" class="di-link">Download template</a>.';
        termFields.style.display = type.needsTerm ? 'grid' : 'none';
        document.getElementById('diSchoolYear').required = !!type.needsTerm;
        document.getElementById('diSemester').required = !!type.needsTerm;

        form.reset();
        resetResults();
    }

    function showAlert(type, message) {
        alertBox.className = 'di-alert show ' + type;
        alertBox.textContent = message;
    }

    function renderResultTable(payload) {
        if (!activeType.resultColumns) {
            tableHead.innerHTML = '';
            resultRows.innerHTML = '<tr><td class="di-muted">' + esc(payload.message || 'Import completed.') + '</td></tr>';
            return;
        }

        tableHead.innerHTML = '<tr>' + activeType.resultColumns.map(function (col) {
            return '<th>' + esc(col) + '</th>';
        }).join('') + '</tr>';

        var rows = (payload.assigned || []).concat(payload.issues || []);
        if (!rows.length) {
            resultRows.innerHTML = '<tr><td colspan="' + activeType.resultColumns.length + '" class="di-muted">No rows were imported.</td></tr>';
            return;
        }

        resultRows.innerHTML = rows.map(function (item) {
            return '<tr>' + activeType.resultRow(item).map(function (cell) {
                return '<td>' + cell + '</td>';
            }).join('') + '</tr>';
        }).join('');
    }

    typeList.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-di-type]');
        if (!btn) return;
        selectType(btn.getAttribute('data-di-type'));
    });

    fileInput.addEventListener('change', function () {
        setValidated(false);
    });
    document.getElementById('diSchoolYear').addEventListener('change', function () { setValidated(false); });
    document.getElementById('diSemester').addEventListener('change', function () { setValidated(false); });

    function runImport(dryRun) {
        if (!fileInput.files || !fileInput.files.length) {
            showAlert('error', 'Please choose a CSV file first.');
            return;
        }

        if (activeType.needsTerm) {
            var schoolYear = document.getElementById('diSchoolYear').value;
            var semester = document.getElementById('diSemester').value;
            if (!schoolYear || !semester) {
                showAlert('error', 'Please select School Year and Semester.');
                return;
            }
        }

        var formData = new FormData();
        if (activeType.needsTerm) {
            formData.append('school_year', document.getElementById('diSchoolYear').value);
            formData.append('semester', document.getElementById('diSemester').value);
        }
        formData.append('file', fileInput.files[0]);
        formData.append('dry_run', dryRun ? '1' : '0');

        validateBtn.disabled = true;
        confirmBtn.disabled = true;
        var busyBtn = dryRun ? validateBtn : confirmBtn;
        busyBtn.textContent = dryRun ? 'Checking...' : 'Importing...';
        showAlert('warn', dryRun ? 'Checking file...' : 'Importing...');
        errorsBox.style.display = 'none';
        errorsBox.innerHTML = '';

        fetch(activeType.importUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': page.dataset.csrf
            },
            body: formData,
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (payload) {
                    if (!response.ok) throw payload;
                    return payload;
                });
            })
            .then(function (payload) {
                renderResultTable(payload);

                var errors = payload.errors || [];
                if (errors.length) {
                    errorsBox.style.display = 'block';
                    errorsBox.innerHTML = errors.map(function (line) {
                        return '<div>' + esc(line) + '</div>';
                    }).join('');
                }

                var summary = payload.summary || {};
                var hasIssues = (summary.pending || 0) > 0 || (summary.conflicts || 0) > 0;
                showAlert(hasIssues ? 'warn' : 'success', payload.message || (dryRun ? 'File checked.' : 'Import completed.'));

                if (dryRun) {
                    setValidated(true);
                } else {
                    // A real import consumed this file - require a fresh check before importing again.
                    form.reset();
                    setValidated(false);
                }
            })
            .catch(function (error) {
                showAlert('error', (error && error.message) || 'Unable to process the file.');
                setValidated(false);
            })
            .finally(function () {
                validateBtn.disabled = false;
                validateBtn.textContent = isValidated ? 'Re-check File' : 'Validate File';
                confirmBtn.textContent = 'Confirm Import';
                confirmBtn.disabled = !isValidated;
            });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        runImport(true);
    });

    confirmBtn.addEventListener('click', function () {
        runImport(false);
    });

    selectType(IMPORT_TYPES[0].key);
});
</script>
@endsection
