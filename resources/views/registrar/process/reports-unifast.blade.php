@extends('layouts.registrar')

@section('title', 'PLP - Reports - UNIFAST')
@section('page-title', 'REPORTS - UNIFAST')
@section('body-class', 'page-reports-unifast')

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

</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="ga-page">
        <div class="app-filter-bar">
            <div class="app-filter-row" style="align-items:flex-end;">
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform:uppercase;">School Year</label>
                    <select class="app-filter-select" id="unifastSchoolYear">
                        <option>2025-2026</option>
                        <option>2024-2025</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform:uppercase;">Semester</label>
                    <select class="app-filter-select" id="unifastSemester">
                        <option>2nd Semester</option>
                        <option>1st Semester</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1;">
                    <label class="app-filter-label" style="text-transform:uppercase;">Sex</label>
                    <select class="app-filter-select" id="unifastSex">
                        <option value="">All</option>
                        <option>F</option>
                        <option>M</option>
                    </select>
                </div>
                <div class="app-filter-group" style="flex:1.2;">
                    <label class="app-filter-label" style="text-transform:uppercase;">Place of Birth</label>
                    <input type="text" class="app-filter-select" id="unifastPob" placeholder="City / Province">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:15px; gap:8px; align-items:center;">
                <div style="margin-right:auto; min-width:280px; max-width:360px; width:100%;">
                    <input type="text" class="app-filter-select" id="unifastSearch" style="width:100%;" placeholder="Search student no., name, place of birth...">
                </div>
                <input type="file" id="unifastImportInput" accept=".csv,text/csv" style="display:none;">
                <button type="button" class="req-btn-save" style="min-width:130px; font-weight:700; background:#fff; color:#006837; border:1px solid #006837;" onclick="triggerUnifastImport()">Import CSV</button>
                <button type="button" class="req-btn-save" style="min-width:150px; font-weight:700;" onclick="unifastExportCsv()">Download CSV</button>
            </div>
        </div>

        <div class="rp-report-heading">
            <h3 class="rp-report-title">UNIFAST</h3>
            <p class="rp-report-sub"><span id="unifastSemLabel">2ND SEMESTER</span> | SCHOOL YEAR: <span id="unifastSyLabel">2025-2026</span></p>
        </div>

        <div class="ga-table-wrap app-table-wrap">
            <table class="rp-sheet-table" id="unifastTable" style="min-width:3400px;">
                <thead>
                    <tr>
                        <th>CTRL. No.</th>
                        <th>Student No.</th>
                        <th>Student Name</th>
                        <th>Surname</th>
                        <th>Given Name</th>
                        <th>Middle Name</th>
                        <th>Middle Initial</th>
                        <th>Sex (M/F)</th>
                        <th>Date of Birth (mm/dd/yyyy)</th>
                        <th>Place of Birth</th>
                        <th>Residency (Address)</th>
                        <th>Residency Status (PR/NPR)</th>
                        <th>ZIP Code</th>
                        <th>Contact Number</th>
                        <th>Email Address</th>
                        <th>Pay Units - NSTP 101 &amp; 102</th>
                        <th>NSTP 101</th>
                        <th>NSTP 102</th>
                        <th>Pay Units</th>
                        <th>Program (Example: Bachelor of Science in Accountancy)</th>
                        <th>Program (Abbreviation: BSA)</th>
                        <th>Section</th>
                        <th>Year Level</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="unifastTableBody">
                    <tr><td>27</td><td>21-00037</td><td>CAMAR, HAYRONISAH MACAALIN</td><td>CAMAR</td><td>HAYRONISAH</td><td>MACAALIN</td><td>M.</td><td>F</td><td>08/06/1999</td><td>LANAO DEL SUR</td><td>612 VILLA CITIES ST. BAMBANG, PASIG CITY</td><td>PR</td><td>1600</td><td>09156405621</td><td>iharezldhs@gmail.com</td><td>15</td><td>0</td><td>0</td><td>15</td><td>BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION MAJOR IN MARKETING MANAGEMENT</td><td>BSBA</td><td>BSBA 4 - BAP</td><td>4</td><td></td></tr>
                    <tr><td>28</td><td>21-00058</td><td>IDALO, JOHAIRA GUTIERREZ</td><td>IDALO</td><td>JOHAIRA</td><td>GUTIERREZ</td><td>G.</td><td>F</td><td>05/18/2003</td><td>BULACAN</td><td>33 TULCO ST. STO. CRISTO, PASIG CITY</td><td>PR</td><td>1600</td><td>09336443740</td><td>jhoaira1112@gmail.com</td><td>15</td><td>0</td><td>0</td><td>15</td><td>BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION MAJOR IN MARKETING MANAGEMENT</td><td>BSBA</td><td>BSBA 4 - BAP</td><td>4</td><td></td></tr>
                    <tr><td>29</td><td>21-00108</td><td>GAMEZ, SHAINA OMA</td><td>GAMEZ</td><td>SHAINA</td><td>OMA</td><td>O.</td><td>F</td><td>10/13/2001</td><td>PASIG CITY</td><td>750 CHRYST ST. DAMAYAN, MAYBUNGA, PASIG CITY</td><td>PR</td><td>1607</td><td>099110110310</td><td>baldo_charlz@plagis.edu.ph</td><td>15</td><td>0</td><td>0</td><td>15</td><td>BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION MAJOR IN MARKETING MANAGEMENT</td><td>BSBA</td><td>BSBA 4 - BAP</td><td>4</td><td></td></tr>
                    <tr><td>30</td><td>21-00141</td><td>CORPIN, JHON CARLOS LIQUIT</td><td>CORPIN</td><td>JHON CARLOS</td><td>LIQUIT</td><td>L.</td><td>M</td><td>03/25/2000</td><td>LEYTE</td><td>40-E MENDOZA ST. BUTING, PASIG CITY</td><td>PR</td><td>1600</td><td>09940884218</td><td>edwarden22@gmail.com</td><td>15</td><td>0</td><td>0</td><td>15</td><td>BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION MAJOR IN MARKETING MANAGEMENT</td><td>BSBA</td><td>BSBA 4 - BAP</td><td>4</td><td></td></tr>
                    <tr><td>31</td><td>21-00146</td><td>TORRECAMPO, AYESHA JULIA GUNABE</td><td>TORRECAMPO</td><td>AYESHA JULIA</td><td>GUNABE</td><td>G.</td><td>F</td><td>09/22/2003</td><td>ANGELES CITY</td><td>480-B ENZO DELA ST. TINAJONG, PINAGBUHATAN, PASIG CITY</td><td>PR</td><td>1602</td><td>09496392723</td><td>melanieel22@gmail.com</td><td>15</td><td>0</td><td>0</td><td>15</td><td>BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION MAJOR IN MARKETING MANAGEMENT</td><td>BSBA</td><td>BSBA 4 - BAP</td><td>4</td><td></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var schoolYear = document.getElementById('unifastSchoolYear');
    var semester = document.getElementById('unifastSemester');
    var sex = document.getElementById('unifastSex');
    var pob = document.getElementById('unifastPob');
    var search = document.getElementById('unifastSearch');
    var importInput = document.getElementById('unifastImportInput');
    var semLabel = document.getElementById('unifastSemLabel');
    var syLabel = document.getElementById('unifastSyLabel');

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
        var sx = (sex.value || '').toLowerCase().trim();
        var pb = (pob.value || '').toLowerCase().trim();

        document.querySelectorAll('#unifastTableBody tr').forEach(function(row) {
            var cells = row.querySelectorAll('td');
            var rowText = (row.textContent || '').toLowerCase();
            var rowSex = (cells[7] ? cells[7].textContent : '').toLowerCase().trim();
            var rowPob = (cells[9] ? cells[9].textContent : '').toLowerCase();

            var passSearch = !q || rowText.indexOf(q) !== -1;
            var passSex = !sx || rowSex === sx;
            var passPob = !pb || rowPob.indexOf(pb) !== -1;

            row.style.display = (passSearch && passSex && passPob) ? '' : 'none';
        });
    }

    window.unifastExportCsv = function() {
        var rows = Array.from(document.querySelectorAll('#unifastTable tr')).filter(function(row) {
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
        a.download = 'unifast-report.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    window.triggerUnifastImport = function() {
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

            var body = document.getElementById('unifastTableBody');
            body.innerHTML = '';

            lines.forEach(function(line, idx) {
                var cols = parseCsvLine(line);
                if (!cols.length) {
                    return;
                }

                var first = (cols[0] || '').toLowerCase();
                if (idx === 0 && (first.indexOf('ctrl') !== -1 || first === 'student no.' || first === 'student no')) {
                    return;
                }

                cols = normalizeColumns(cols, 24);
                var row = document.createElement('tr');
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

    [schoolYear, semester].forEach(function(el) {
        el.addEventListener('change', updateHeading);
        el.addEventListener('change', applyFilter);
    });
    [sex, pob, search].forEach(function(el) {
        el.addEventListener('input', applyFilter);
        el.addEventListener('change', applyFilter);
    });

    updateHeading();
})();
</script>
@endpush
