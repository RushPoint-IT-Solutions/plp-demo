@extends('layouts.registrar')

@section('title', 'PLP - Batch Print (COR/TOR)')
@section('page-title', 'BATCH PRINT (COR/TOR)')

@section('content')
<div class="pf-page" id="batchPrintPage">
    <style>
        .bp-filter-card{background:#fff;border:1px solid #dfe8e2;border-radius:8px;padding:16px;margin-bottom:16px;}
        .bp-filter-grid{display:grid;grid-template-columns:repeat(4,minmax(160px,1fr));gap:12px;align-items:end;}
        .bp-field label{display:block;margin-bottom:5px;color:#46564a;font-size:.78rem;font-weight:800;text-transform:uppercase;}
        .bp-field select{width:100%;min-height:38px;border:1px solid #cfd9d2;border-radius:7px;background:#fff;color:#143521;padding:8px 10px;}
        .bp-load-btn{border:0;border-radius:7px;background:#146c43;color:#fff;cursor:pointer;font-weight:800;min-height:38px;padding:8px 18px;}
        .bp-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;}
        .bp-toolbar-left{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
        .bp-print-btn{border:0;border-radius:7px;background:#0f7b43;color:#fff;cursor:pointer;font-weight:800;min-height:38px;padding:8px 18px;}
        .bp-print-btn:disabled{opacity:.5;cursor:not-allowed;}
        .bp-count{font-size:.82rem;color:#46564a;font-weight:700;}
        @media (max-width:900px){.bp-filter-grid{grid-template-columns:1fr 1fr;}}
    </style>

    <div class="bp-filter-card">
        <div class="bp-filter-grid">
            <div class="bp-field">
                <label for="bpDocType">Document</label>
                <select id="bpDocType">
                    <option value="cor">Certificate of Registration (COR)</option>
                    <option value="tor">Transcript of Records (TOR)</option>
                </select>
            </div>
            <div class="bp-field">
                <label for="bpCourse">Course</label>
                <select id="bpCourse">
                    <option value="">All Courses</option>
                    @foreach($courseOptions as $course)
                        <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="bp-field">
                <label for="bpYearBlock">Year/Block</label>
                <select id="bpYearBlock">
                    <option value="">All Year/Blocks</option>
                    @foreach($yearBlockOptions as $yearBlock)
                        <option value="{{ $yearBlock->id }}">{{ $yearBlock->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="bp-field">
                <label for="bpSection">Section</label>
                <select id="bpSection">
                    <option value="">All Sections</option>
                </select>
            </div>
        </div>
        <div style="margin-top:12px;">
            <button type="button" class="bp-load-btn" id="bpLoadBtn">Load Students</button>
        </div>
    </div>

    <div class="bp-toolbar">
        <div class="bp-toolbar-left">
            <label><input type="checkbox" id="bpSelectAll"> Select All</label>
            <span class="bp-count" id="bpCount">0 selected</span>
        </div>
        <button type="button" class="bp-print-btn" id="bpPrintBtn" disabled>Print Selected</button>
    </div>

    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table svc-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>Student No.</th>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody id="bpStudentBody">
                <tr><td colspan="3" style="text-align:center;padding:24px;color:#607264;">Choose filters and click "Load Students".</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var courseSelect = document.getElementById('bpCourse');
    var yearBlockSelect = document.getElementById('bpYearBlock');
    var sectionSelect = document.getElementById('bpSection');
    var loadBtn = document.getElementById('bpLoadBtn');
    var body = document.getElementById('bpStudentBody');
    var selectAll = document.getElementById('bpSelectAll');
    var printBtn = document.getElementById('bpPrintBtn');
    var countLabel = document.getElementById('bpCount');
    var docType = document.getElementById('bpDocType');

    var sectionsUrl = '{{ route('registrar.services.reports-admin.batch-print.sections') }}';
    var studentsUrl = '{{ route('registrar.services.reports-admin.batch-print.students') }}';
    var renderUrl = '{{ route('registrar.services.reports-admin.batch-print.render') }}';

    function updateCount() {
        var checked = body.querySelectorAll('input[type="checkbox"]:checked').length;
        countLabel.textContent = checked + ' selected';
        printBtn.disabled = checked === 0;
    }

    function refreshSections() {
        var params = new URLSearchParams({
            course_id: courseSelect.value,
            year_block_id: yearBlockSelect.value
        });

        fetch(sectionsUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                var results = payload.results || [];
                sectionSelect.innerHTML = '<option value="">All Sections</option>' + results.map(function (section) {
                    return '<option value="' + section + '">' + section + '</option>';
                }).join('');
            });
    }

    courseSelect.addEventListener('change', refreshSections);
    yearBlockSelect.addEventListener('change', refreshSections);

    loadBtn.addEventListener('click', function () {
        var params = new URLSearchParams({
            course_id: courseSelect.value,
            year_block_id: yearBlockSelect.value,
            section: sectionSelect.value
        });

        body.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:24px;">Loading...</td></tr>';
        selectAll.checked = false;

        fetch(studentsUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                var results = payload.results || [];
                if (!results.length) {
                    body.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:24px;color:#607264;">No students found for the selected filters.</td></tr>';
                    updateCount();
                    return;
                }

                body.innerHTML = results.map(function (student) {
                    return '<tr>' +
                        '<td><input type="checkbox" class="bp-student-check" value="' + student.id + '"></td>' +
                        '<td>' + student.student_no + '</td>' +
                        '<td>' + student.name + '</td>' +
                        '</tr>';
                }).join('');
                updateCount();
            });
    });

    body.addEventListener('change', function (event) {
        if (event.target.classList.contains('bp-student-check')) updateCount();
    });

    selectAll.addEventListener('change', function () {
        body.querySelectorAll('.bp-student-check').forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });
        updateCount();
    });

    printBtn.addEventListener('click', function () {
        var ids = Array.prototype.map.call(body.querySelectorAll('.bp-student-check:checked'), function (checkbox) {
            return checkbox.value;
        });
        if (!ids.length) return;

        var params = new URLSearchParams();
        params.append('type', docType.value);
        ids.forEach(function (id) { params.append('student_ids[]', id); });

        window.open(renderUrl + '?' + params.toString(), '_blank');
    });
});
</script>
@endpush
