@extends('layouts.registrar')

@section('title', 'PLP - Alumni Tracker')
@section('page-title', 'ALUMNI TRACKER')
@section('body-class', 'page-alumni-tracker')

@section('content')
<div class="pf-page">
    <div class="sched-filter-bar at-top-row">
        <div class="at-search-block at-search-card">
            <span class="app-filter-label">Search</span>
            <div class="pf-search-wrap at-search-wrap">
                <span class="pf-search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input id="atSearchInput" type="text" class="pf-search-input" placeholder="Search Student ID / Name">
            </div>
        </div>

        <div class="at-config-card">
            <div class="at-config-title">System Configuration</div>
            <div class="at-config-grid">
                <div class="at-config-item">
                    <span class="at-config-inline-label">School Year:</span>
                    @include('registrar.components.listbox-select', [
                        'id' => 'atSchoolYear',
                        'name' => 'atSchoolYear',
                        'options' => ($alumniSchoolYears ?? []),
                        'selected' => ($alumniConfig['schoolYear'] ?? ''),
                        'placeholder' => '- Select School Year -',
                    ])
                </div>
                <div class="at-config-item">
                    <span class="at-config-inline-label">Term:</span>
                    @include('registrar.components.listbox-select', [
                        'id' => 'atTerm',
                        'name' => 'atTerm',
                        'options' => ($alumniTerms ?? ['First', 'Second', 'Summer']),
                        'selected' => ($alumniConfig['term'] ?? ''),
                        'placeholder' => '- Select Term -',
                    ])
                </div>
                <div class="at-config-action">
                    <button type="button" class="pf-btn-new at-btn-set" onclick="saveAlumniConfig()">Set</button>
                </div>
            </div>
        </div>
    </div>

    <div class="sched-filter-bar">
        <div class="sched-filter-row at-filter-row">
            <div class="sched-filter-group at-filter-group at-program-group">
                <span class="app-filter-label">Program</span>
                <select id="atProgram" class="app-filter-select" style="width:100%;">
                    <option value="">Select Course</option>
                    @foreach(($alumniPrograms ?? []) as $program)
                        <option value="{{ $program }}">{{ $program }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sched-filter-group at-filter-group">
                <span class="app-filter-label">Year Level</span>
                <select id="atYearLevel" class="app-filter-select" style="width:100%;">
                    <option value="">Select Year Level</option>
                    @foreach(($alumniYearLevels ?? []) as $yearLevel)
                        <option value="{{ $yearLevel }}">{{ $yearLevel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sched-filter-group at-filter-group at-sort-group">
                <span class="app-filter-label">Sort By</span>
                <div class="at-sort-row">
                    <select id="atSortBy" class="app-filter-select" style="width:100%;">
                        <option value="studentNo">Student ID</option>
                        <option value="studentName">Student Name</option>
                        <option value="program">Program</option>
                        <option value="yearLevel">Year Level</option>
                    </select>
                    <select id="atSortOrder" class="app-filter-select" style="width:100%;">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>
            </div>

            <div class="at-filter-action d-flex gap-2 align-items-center">
                <button type="button" class="apc-upload-btn" id="atImportExcelBtn" onclick="alert('Import functionality will be available after backend API update.')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Import Excel
                </button>
                <button type="button" class="pf-btn-new" id="atGenerateBtn">Generate Report</button>
            </div>
        </div>
    </div>

    <div class="student-table-wrapper table-responsive at-table-wrap">
        <table class="student-table registrar-table at-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Program</th>
                    <th>Year Level</th>
                </tr>
            </thead>
            <tbody id="atTableBody"></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script>
var atRows = @json($alumniRows ?? []);
var atConfig = @json($alumniConfig ?? ['schoolYear' => '2025-2026', 'term' => 'Second']);
var atSemesterMap = @json($alumniSemesterMap ?? []);
var atBaseTerms = @json($alumniTerms ?? ['First', 'Second', 'Summer']);
var atSaveConfigUrl = '{{ route('registrar.registrar-menu.alumni.tracker.config') }}';

function atRefreshListbox(selectElement) {
    if (!selectElement) {
        return;
    }

    if (window.registrarListboxSelect && typeof window.registrarListboxSelect.refresh === 'function') {
        window.registrarListboxSelect.refresh(selectElement);
        return;
    }

    if (typeof window.CustomEvent === 'function') {
        document.dispatchEvent(new CustomEvent('registrar:listbox:refresh', { detail: { target: selectElement } }));
    }
}

function atEscapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(ch) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return map[ch];
    });
}

function atNormalize(value) {
    return String(value || '').trim().toLowerCase();
}

function atResolveTermsForYear(schoolYear) {
    var yearKey = String(schoolYear || '').trim();
    if (yearKey && atSemesterMap && Array.isArray(atSemesterMap[yearKey]) && atSemesterMap[yearKey].length) {
        return atSemesterMap[yearKey];
    }

    return Array.isArray(atBaseTerms) && atBaseTerms.length
        ? atBaseTerms.slice()
        : ['First', 'Second', 'Summer'];
}

function atSyncTermOptions(preferredTerm) {
    var schoolYearSelect = document.getElementById('atSchoolYear');
    var termSelect = document.getElementById('atTerm');
    if (!schoolYearSelect || !termSelect) {
        return;
    }

    var terms = atResolveTermsForYear(schoolYearSelect.value);
    var selectedTerm = String(preferredTerm || termSelect.value || '').trim();

    termSelect.innerHTML = terms.map(function(term) {
        return '<option value="' + atEscapeHtml(term) + '">' + atEscapeHtml(term) + '</option>';
    }).join('');

    if (selectedTerm && terms.indexOf(selectedTerm) !== -1) {
        termSelect.value = selectedTerm;
    }

    if (!termSelect.value && terms.length) {
        termSelect.value = terms[0];
    }

    atRefreshListbox(termSelect);
}

function atGetFilteredRows() {
    var searchInput = document.getElementById('atSearchInput');
    var searchValue = atNormalize(searchInput ? searchInput.value : '');
    var schoolYear = document.getElementById('atSchoolYear').value;
    var term = document.getElementById('atTerm').value;
    var program = document.getElementById('atProgram').value;
    var yearLevel = document.getElementById('atYearLevel').value;
    var sortBy = document.getElementById('atSortBy').value;
    var sortOrder = document.getElementById('atSortOrder').value;

    var filtered = atRows.filter(function(row) {
        var matchSearch = !searchValue ||
            atNormalize(row.studentNo).indexOf(searchValue) !== -1 ||
            atNormalize(row.studentName).indexOf(searchValue) !== -1;
        var matchSchoolYear = !schoolYear || row.schoolYear === schoolYear;
        var matchTerm = !term || row.term === term;
        var matchProgram = !program || row.program === program;
        var matchYear = !yearLevel || row.yearLevel === yearLevel;
        return matchSearch && matchSchoolYear && matchTerm && matchProgram && matchYear;
    });

    filtered.sort(function(a, b) {
        var aVal = atNormalize(a[sortBy]);
        var bVal = atNormalize(b[sortBy]);
        if (aVal < bVal) return sortOrder === 'desc' ? 1 : -1;
        if (aVal > bVal) return sortOrder === 'desc' ? -1 : 1;
        return 0;
    });

    return filtered;
}

function atRenderTable() {
    var body = document.getElementById('atTableBody');
    if (!body) return;

    var filtered = atGetFilteredRows();

    if (!filtered.length) {
        body.innerHTML = '<tr><td colspan="5" class="sc-empty-row">No students found.</td></tr>';
        return;
    }

    var rowsHtml = filtered.map(function(row, idx) {
        return '' +
            '<tr>' +
                '<td>' + (idx + 1) + '</td>' +
                '<td>' + atEscapeHtml(row.studentNo) + '</td>' +
                '<td><a href="#">' + atEscapeHtml(row.studentName) + '</a></td>' +
                '<td>' + atEscapeHtml(row.program) + '</td>' +
                '<td>' + atEscapeHtml(row.yearLevel) + '</td>' +
            '</tr>';
    }).join('');

    rowsHtml += '' +
        '<tr class="at-total-row">' +
            '<td colspan="5" class="at-total-cell" style="text-align:left !important;color:#006837 !important;background:#f8fcf9 !important;font-weight:700 !important;">Total Students: <strong>' + filtered.length + '</strong></td>' +
        '</tr>';

    body.innerHTML = rowsHtml;
}

async function saveAlumniConfig() {
    var payload = {
        school_year: document.getElementById('atSchoolYear').value,
        term: document.getElementById('atTerm').value
    };

    try {
        var response = await fetch(atSaveConfigUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        var json = await response.json();
        if (!response.ok || json.ok === false) {
            throw new Error(json.message || 'Unable to save alumni tracker configuration.');
        }

        if (typeof showRegistrarToast === 'function') {
            showRegistrarToast('System configuration saved successfully.', 'success');
        }
        atRenderTable();
    } catch (error) {
        alert(error.message || 'Unable to save alumni tracker configuration.');
    }
}

function atGenerateReport() {
    var rows = atGetFilteredRows();
    if (!rows.length) {
        alert('No records found for the selected filters.');
        return;
    }

    var csvRows = [
        ['No.', 'Student ID', 'Student Name', 'Program', 'Year Level', 'School Year', 'Term']
    ];

    rows.forEach(function(row, idx) {
        csvRows.push([
            String(idx + 1),
            String(row.studentNo || ''),
            String(row.studentName || ''),
            String(row.program || ''),
            String(row.yearLevel || ''),
            String(row.schoolYear || ''),
            String(row.term || '')
        ]);
    });

    var csvContent = csvRows.map(function(cols) {
        return cols.map(function(value) {
            return '"' + String(value).replace(/"/g, '""') + '"';
        }).join(',');
    }).join('\n');

    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    var datePart = new Date().toISOString().slice(0, 10);
    link.href = url;
    link.download = 'alumni-tracker-report-' + datePart + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('Alumni report generated successfully.', 'success');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('atSearchInput');
    if (searchInput) {
        searchInput.value = '';
        searchInput.addEventListener('input', atRenderTable);
    }

    var schoolYearSelect = document.getElementById('atSchoolYear');
    if (schoolYearSelect) {
        schoolYearSelect.value = atConfig.schoolYear || schoolYearSelect.value;
    }

    atSyncTermOptions(atConfig.term || '');

    if (schoolYearSelect) {
        schoolYearSelect.addEventListener('change', function() {
            atSyncTermOptions('');
            atRenderTable();
        });
    }

    ['atTerm', 'atProgram', 'atYearLevel', 'atSortBy', 'atSortOrder'].forEach(function(id) {
        var element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', atRenderTable);
        }
    });

    document.getElementById('atGenerateBtn').addEventListener('click', atGenerateReport);
    atRenderTable();
});
</script>
@endpush
