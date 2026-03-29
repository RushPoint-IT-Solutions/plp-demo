@extends('layouts.registrar')

@section('title', 'PLP - Reports - OSS-NSTP Form')
@section('page-title', 'REPORTS - OSS-NSTP FORM')
@section('body-class', 'page-reports-oss-nstp')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ time() }}">
<style>
    .rp-sheet-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.83rem;
    }

    .rp-sheet-table th,
    .rp-sheet-table td {
        border: 1px solid #d6dee8;
        padding: 6px 8px;
        white-space: nowrap;
    }

    .rp-sheet-table thead th {
        background: #006837;
        color: #fff;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .rp-report-heading {
        margin: 14px 0 10px;
        padding: 4px 0;
        text-align: center;
    }

    .rp-report-title {
        margin: 0;
        color: #006837;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-size: 1.08rem;
    }

    .rp-report-sub {
        margin: 3px 0 0;
        color: #4b5563;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.82rem;
    }

    .rp-action-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
    }

    .rp-search-wrap {
        margin-right: auto;
        min-width: 280px;
        max-width: 360px;
        width: 100%;
    }

    .rp-action-btn {
        min-width: 130px;
        font-weight: 700;
        flex: 0 0 auto;
    }

    .rp-action-btn-download {
        min-width: 150px;
    }

    @media (max-width: 991.98px) {
        .rp-action-row {
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .rp-search-wrap {
            margin-right: 0;
            min-width: 0;
            max-width: none;
            flex: 1 1 100%;
        }

        .rp-action-btn {
            flex: 1 1 calc(50% - 4px);
            min-width: 0;
        }
    }

    @media (max-width: 575.98px) {
        .rp-action-btn {
            flex-basis: 100%;
        }
    }

</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <div class="app-filter-bar">
            <div class="app-filter-row" style="align-items:flex-end;">
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform:uppercase;">School Year</label>
                    <select class="app-filter-select" id="nstpSchoolYear">
                        <option>2024-2025</option>
                        <option>2025-2026</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform:uppercase;">Semester</label>
                    <select class="app-filter-select" id="nstpSemester">
                        <option>2nd Semester</option>
                        <option>1st Semester</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform:uppercase;">Component</label>
                    <select class="app-filter-select" id="nstpComponent">
                        <option value="">All</option>
                        <option>CWTS</option>
                        <option>LTS</option>
                        <option>ROTC</option>
                    </select>
                </div>
            </div>

            <div class="rp-action-row">
                <div class="rp-search-wrap">
                    <input type="text" class="app-filter-select" id="nstpSearch" style="width:100%;" placeholder="Search serial no., surname, first name, email...">
                </div>
                <input type="file" id="nstpImportInput" accept=".csv,text/csv" style="display:none;">
                <button type="button" class="req-btn-save rp-action-btn" style="background:#fff; color:#006837; border:1px solid #006837;" onclick="triggerNstpImport()">Import CSV</button>
                <button type="button" class="req-btn-save rp-action-btn rp-action-btn-download" onclick="nstpExportCsv()">Download CSV</button>
            </div>
        </div>

        <div class="rp-report-heading">
            <h3 class="rp-report-title">LIST OF NSTP GRADUATES FOR SERIAL NUMBER</h3>
            <p class="rp-report-sub"><span id="nstpSemLabel">2ND SEMESTER</span> | SCHOOL YEAR: <span id="nstpSyLabel">2024-2025</span></p>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table class="rp-sheet-table" id="nstpTable" style="min-width:2200px;">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Serial No.</th>
                        <th>Surname</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Course/Program</th>
                        <th>Gender</th>
                        <th>Birthdate</th>
                        <th>Street/Barangay Address</th>
                        <th>Town/City Address</th>
                        <th>Provincial Address</th>
                        <th>Contact Number Telephone/Mobile</th>
                        <th>Email Address</th>
                    </tr>
                </thead>
                <tbody id="nstpTableBody">
                    <tr data-semester="2nd semester" data-school-year="2024-2025" data-component="cwts"><td>1</td><td>000001</td><td>ADAYA</td><td>JEAN</td><td>OBAL</td><td>Bachelor of Arts in Psychology</td><td>FEMALE</td><td>1/23/2006</td><td>11 ALFONSO ST. SOLDIERS VILLAGE, STA. LUCIA</td><td>PASIG CITY</td><td>METRO MANILA</td><td>99276822436</td><td>adayajean23@gmail.com</td></tr>
                    <tr data-semester="2nd semester" data-school-year="2024-2025" data-component="cwts"><td>2</td><td>000002</td><td>ADORA</td><td>MARLON</td><td>MADROÑA</td><td>Bachelor of Arts in Psychology</td><td>MALE</td><td>7/22/2006</td><td>BLK 4 EUSEBIO AVE. NAGPAYONG II, PINAGBUHATAN</td><td>PASIG CITY</td><td>METRO MANILA</td><td>9817833289</td><td>marlonadora7@gmail.com</td></tr>
                    <tr data-semester="2nd semester" data-school-year="2024-2025" data-component="cwts"><td>3</td><td>000003</td><td>AGUILANDO</td><td>CHRISIA</td><td>DE LA TORRE</td><td>Bachelor of Arts in Psychology</td><td>FEMALE</td><td>7/2/2006</td><td>12410 BRIGHTER HOPE HOA EUSEBIO AVE. NAGPAYONG, PINAGBUHATAN</td><td>PASIG CITY</td><td>METRO MANILA</td><td>9106267156</td><td>chrisiaaguilando76@gmail.com</td></tr>
                    <tr data-semester="2nd semester" data-school-year="2024-2025" data-component="cwts"><td>4</td><td>000004</td><td>ALVAREZ</td><td>ELOISSA MAE</td><td>MARI</td><td>Bachelor of Arts in Psychology</td><td>FEMALE</td><td>2/24/2006</td><td>PH2 B8 L9 KATIWASAYAN ST. KARANGALAN VILLAGE, MANGGAHAN</td><td>PASIG CITY</td><td>METRO MANILA</td><td>9326562906</td><td>eloissaalvarez24@gmail.com</td></tr>
                    <tr data-semester="2nd semester" data-school-year="2024-2025" data-component="cwts"><td>5</td><td>000005</td><td>APUYA</td><td>CANDICE FAY</td><td>LUMINTAC</td><td>Bachelor of Arts in Psychology</td><td>FEMALE</td><td>6/8/2005</td><td>12234 LONTOC ST. MANGGA NAGPAYONG, PINAGBUHATAN</td><td>PASIG CITY</td><td>METRO MANILA</td><td>9302896742</td><td>candicefay08@gmail.com</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var schoolYear = document.getElementById('nstpSchoolYear');
    var semester = document.getElementById('nstpSemester');
    var component = document.getElementById('nstpComponent');
    var search = document.getElementById('nstpSearch');
    var importInput = document.getElementById('nstpImportInput');
    var semLabel = document.getElementById('nstpSemLabel');
    var syLabel = document.getElementById('nstpSyLabel');

    function updateHeading() {
        semLabel.textContent = (semester.value || '').toUpperCase();
        syLabel.textContent = schoolYear.value || '';
    }

    function parseCsvLine(line) {
        var result = [];
        var current = '';
        var inQuotes = false;
        for (var i = 0; i < line.length; i++) {
            var char = line[i];
            if (char === '"') {
                if (inQuotes && line[i + 1] === '"') {
                    current += '"';
                    i++;
                } else {
                    inQuotes = !inQuotes;
                }
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }
        result.push(current.trim());
        return result;
    }

    function normalizeColumns(cols, target) {
        var out = cols.slice(0, target);
        while (out.length < target) {
            out.push('');
        }
        return out;
    }

    function applyFilter() {
        var q = (search.value || '').toLowerCase().trim();
        var c = (component.value || '').toLowerCase().trim();

        document.querySelectorAll('#nstpTableBody tr').forEach(function(row) {
            var rowText = (row.textContent || '').toLowerCase();
            var selectedSem = (semester.value || '').toLowerCase().trim();
            var selectedSy = (schoolYear.value || '').toLowerCase().trim();
            var rowSem = (row.getAttribute('data-semester') || '').toLowerCase().trim();
            var rowSy = (row.getAttribute('data-school-year') || '').toLowerCase().trim();
            var rowComponent = (row.getAttribute('data-component') || '').toLowerCase().trim();

            var matchesSem = !selectedSem || rowSem === selectedSem;
            var matchesSy = !selectedSy || rowSy === selectedSy;
            var matchesComponent = !c || rowComponent === c;

            var passSearch = !q || rowText.indexOf(q) !== -1;

            row.style.display = (passSearch && matchesComponent && matchesSem && matchesSy) ? '' : 'none';
        });

        updateHeading();
    }

    window.nstpExportCsv = function() {
        var rows = Array.from(document.querySelectorAll('#nstpTable tr')).filter(function(row) {
            return row.style.display !== 'none';
        });

        var csv = rows.map(function(row) {
            var cols = Array.from(row.querySelectorAll('th,td')).map(function(cell) {
                var text = (cell.textContent || '').trim().replace(/\"/g, '""');
                return '"' + text + '"';
            });
            return cols.join(',');
        }).join('\n');

        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'oss-nstp-report.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    window.triggerNstpImport = function() {
        importInput.click();
    };

    importInput.addEventListener('change', function(e) {
        var file = e.target.files && e.target.files[0];
        if (!file) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function(evt) {
            var text = String(evt.target.result || '');
            var lines = text.split(/\r?\n/).filter(function(line) {
                return line.trim().length > 0;
            });

            if (!lines.length) {
                return;
            }

            var body = document.getElementById('nstpTableBody');
            body.innerHTML = '';
            var targetSem = (semester.value || '').toLowerCase().trim();
            var targetSy = (schoolYear.value || '').toLowerCase().trim();
            var targetComponent = (component.value || 'cwts').toLowerCase().trim();

            lines.forEach(function(line, idx) {
                var cols = parseCsvLine(line);
                if (!cols.length) {
                    return;
                }

                var first = (cols[0] || '').toLowerCase();
                if (idx === 0 && (first === 'no.' || first === 'no' || first.indexOf('serial') !== -1)) {
                    return;
                }

                cols = normalizeColumns(cols, 13);
                var row = document.createElement('tr');
                row.setAttribute('data-semester', targetSem);
                row.setAttribute('data-school-year', targetSy);
                row.setAttribute('data-component', targetComponent);

                cols.forEach(function(value) {
                    var td = document.createElement('td');
                    td.textContent = value;
                    row.appendChild(td);
                });

                body.appendChild(row);
            });

            applyFilter();
        };

        reader.readAsText(file);
        importInput.value = '';
    });

    [schoolYear, semester, component].forEach(function(el) {
        el.addEventListener('change', applyFilter);
    });

    search.addEventListener('input', applyFilter);
    applyFilter();
})();
</script>
@endpush
