<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Transcript of Records – {{ strtoupper($student->name) }}</title>
<style>
*  { box-sizing:border-box; margin:0; padding:0; font-family:'Times New Roman',Times,serif; }
body { background:#f0f0f0; color:#000; }

/* ── toolbar (hidden on print) ── */
.tor-toolbar {
    background:#004d27; color:#fff; padding:10px 24px;
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; position:sticky; top:0; z-index:100;
}
.tor-toolbar span { font-size:14px; font-weight:600; font-family:Arial,sans-serif; }
.tor-toolbar a  { color:rgba(255,255,255,.75); font-size:13px; text-decoration:none; font-family:Arial,sans-serif; }
.tor-toolbar a:hover { color:#fff; }
.tor-print-btn {
    background:#fff; color:#004d27; border:none; border-radius:6px;
    padding:7px 20px; font-size:13px; font-weight:700; cursor:pointer; font-family:Arial,sans-serif;
}
.tor-print-btn:hover { background:#e2f5ea; }

/* ── page ── */
.tor-page {
    width:215.9mm; min-height:279.4mm; margin:20px auto;
    background:#fff; padding:12mm 15mm; position:relative;
    box-shadow:0 4px 24px rgba(0,0,0,.18);
    font-size:10pt; line-height:1.35;
}

/* ── header space — blank; the letterhead is pre-printed on the physical paper ── */
.tor-header-space { height:45mm; }

/* ── editable layout (page 1 fixed content: student data, scholastic record,
     grading system, remarks, legal notice, signatures, footer). The grade
     table on page 2 stays server-rendered since its row count is per-student. ── */
.tor-page1-frame { position:relative; height:279.4mm; margin:-12mm -15mm 0; }
.tor-sheet { position:absolute; top:0; left:0; right:0; bottom:0; }
.tor-tpl-element { position:absolute; white-space:pre-wrap; outline:none; cursor:default; padding:1px 3px; border:1px dashed transparent; }
body.tor-editing .tor-tpl-element { cursor:move; }
body.tor-editing .tor-tpl-element:hover { border-color:#0a7a3f66; }
.tor-tpl-element.is-selected { border-color:#0a7a3f; background:rgba(10,122,63,.06); }
.tor-photo-box-fixed { position:absolute; top:21.5%; left:71%; width:18%; text-align:center; }
.tor-photo-box-fixed .tor-sd-photo-box { width:100%; aspect-ratio:130/150; }

.tor-edit-toggle-btn {
    background:transparent; color:#fff; border:1px solid rgba(255,255,255,.5); border-radius:6px;
    padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer; font-family:Arial,sans-serif;
}
.tor-edit-toggle-btn.is-active { background:#fff; color:#004d27; }

.tor-editor-toolbar {
    display:none; position:fixed; top:64px; right:16px; z-index:200; width:220px;
    padding:10px; border:1px solid #cbd5d1; border-radius:8px; background:#fff;
    box-shadow:0 8px 20px rgba(0,0,0,.18); gap:7px; font-family:Arial,sans-serif;
}
.tor-editor-toolbar.is-visible { display:grid; }
.tor-editor-toolbar select, .tor-editor-toolbar input, .tor-editor-toolbar button {
    height:30px; border:1px solid #cbd5d1; border-radius:5px; font-size:.78rem; font-family:Arial,sans-serif;
}
.tor-editor-toolbar button { font-weight:800; cursor:pointer; background:#fff; }
.tor-editor-toolbar button.is-active { background:#0a7a3f; border-color:#0a7a3f; color:#fff; }
.tor-editor-toolbar label { display:grid; grid-template-columns:42px 1fr; align-items:center; gap:6px; font-size:.74rem; font-weight:700; color:#333; }
.tor-editor-toolbar .tor-toolbar-row { display:flex; gap:6px; }
.tor-editor-toolbar .tor-toolbar-row select { flex:1; }
.tor-editor-toolbar .tor-toolbar-row button { flex:0 0 30px; }

/* ── section bar (STUDENT DATA / SCHOLASTIC RECORD) ── */
.tor-section-bar {
    text-align:center; font-weight:700; font-size:11pt;
    border-top:2px solid #000; border-bottom:2px solid #000;
    padding:1.5mm 0; margin:3mm 0 2mm;
}

/* ── student data ── */
.tor-sd-wrap { display:flex; gap:6mm; margin-bottom:2mm; }
.tor-sd-fields { flex:1; }
.tor-sd-row { display:flex; gap:6px; padding:0.6mm 0; font-size:10pt; }
.tor-sd-label { font-weight:700; min-width:150px; flex-shrink:0; }
.tor-sd-sep { font-weight:700; margin-right:6px; }
.tor-sd-photo { width:140px; text-align:center; flex-shrink:0; }
.tor-sd-photo-box {
    width:130px; height:150px; border:1px solid #000; display:flex;
    align-items:center; justify-content:center; overflow:hidden; background:#fafafa;
}
.tor-sd-photo-box img { width:100%; height:100%; object-fit:cover; }
.tor-sd-photo-caption { font-size:8pt; font-style:italic; margin-top:2mm; }

/* ── scholastic record ── */
.tor-sr-row { display:flex; gap:6px; padding:0.6mm 0; font-size:10pt; }
.tor-sr-label { font-weight:700; min-width:150px; flex-shrink:0; }

/* ── grading system + remarks ── */
.tor-gs-wrap { display:flex; gap:0; margin:2mm 0; border:2px solid #000; }
.tor-gs-box { flex:1.4; padding:2mm 3mm; border-right:2px solid #000; }
.tor-gs-title { text-align:center; font-weight:700; font-size:10pt; margin-bottom:1.5mm; }
.tor-gs-grid { display:grid; grid-template-columns:1fr 1fr auto; gap:0 8mm; font-size:9pt; }
.tor-gs-grid div { padding:0.3mm 0; }
.tor-gs-codes { font-size:9pt; }
.tor-gs-credits { font-size:8pt; margin-top:2mm; line-height:1.3; }
.tor-gs-credits strong { display:block; }
.tor-remarks-box { flex:1; padding:2mm 3mm; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
.tor-remarks-title { font-weight:700; font-size:10pt; margin-bottom:2mm; }
.tor-remarks-val { font-weight:700; font-size:10pt; }

/* ── legal notice ── */
.tor-legal { font-size:8.5pt; margin:2mm 0; text-align:justify; }

/* ── grade table (continuation pages reuse this) ── */
.tor-name-line { font-size:10pt; margin:2mm 0 1mm; display:flex; align-items:baseline; gap:6px; }
.tor-name-line b { display:inline-block; min-width:90px; flex-shrink:0; }
.tor-name-line span { flex:1; border-bottom:1px solid #000; padding-bottom:1px; }
.tor-tbl { width:100%; border-collapse:collapse; font-size:9.5pt; margin-bottom:2mm; border:2px solid #000; }
.tor-tbl thead { display:table-header-group; }
.tor-tbl th { border:1px solid #000; padding:1.5mm 2mm; text-align:center; font-weight:700; font-size:9pt; }
.tor-tbl td { border:1px solid #000; padding:1mm 2mm; vertical-align:top; }
.tor-tbl td.grade-cell { text-align:center; }
.tor-sem-row td { font-weight:700; font-style:normal; }

/* ── legal notice + signatures (page 1 only) ── */
.tor-sig-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 10mm; margin-top:10mm; }
.tor-sig-cell { text-align:center; }
.tor-sig-cell-label { text-align:left; font-weight:700; font-size:10pt; margin-bottom:10mm; }
.tor-sig-img { height:34px; margin-bottom:1mm; }
.tor-sig-line { border-top:1px solid #000; margin:0 8mm; padding-top:1mm; }
.tor-sig-name { font-weight:700; font-size:10pt; }
.tor-sig-title { font-size:9pt; font-style:italic; }
.tor-cert-block { text-align:center; margin-top:18mm; }
.tor-cert-label { font-weight:700; font-size:10pt; margin-bottom:10mm; }

/* ── footer ── */
.tor-footer { margin-top:8mm; display:flex; align-items:flex-end; justify-content:space-between; gap:6mm; }
.tor-footer-seal { display:flex; align-items:center; gap:3mm; }
.tor-footer-seal-circle {
    width:50px; height:50px; border-radius:50%; border:2px solid #b45309; color:#b45309;
    display:flex; align-items:center; justify-content:center; text-align:center; font-size:6pt; font-weight:700; flex-shrink:0;
}
.tor-footer-note { font-size:8.5pt; font-style:italic; }
.tor-footer-meta { font-size:8.5pt; }
.tor-continuation-footer { margin-top:4mm; font-size:8pt; display:flex; justify-content:space-between; align-items:flex-end; }

/* ── print ── */
@media print {
    body { background:#fff; }
    .tor-toolbar { display:none !important; }
    .tor-editor-toolbar { display:none !important; }
    .tor-page { margin:0; box-shadow:none; padding:10mm 14mm; width:auto; min-height:0; }
    .tor-tbl tr { page-break-inside:avoid; }
    .tor-tpl-element { border-color:transparent !important; background:transparent !important; }
    @page { size:letter portrait; margin:10mm; }
}
</style>
</head>
<body>

<div class="tor-toolbar">
    <span>Transcript of Records — {{ strtoupper($student->name) }}</span>
    <div style="display:flex;gap:12px;align-items:center;">
        <label style="font-size:13px;font-family:Arial,sans-serif;display:flex;gap:6px;align-items:center;">
            Purpose:
            <select onchange="window.location='{{ url()->current() }}?purpose='+encodeURIComponent(this.value)" style="font-family:Arial,sans-serif;font-size:12px;padding:3px 6px;border-radius:4px;border:none;">
                @foreach($purposeOptions as $option)
                    <option value="{{ $option }}" {{ $purpose === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </label>
        <a href="{{ url()->previous() }}">← Back to Profile</a>
        <button type="button" class="tor-edit-toggle-btn" id="torEditToggleBtn" onclick="torToggleEdit()">✎ Edit Layout</button>
        <button type="button" class="tor-edit-toggle-btn" id="torSaveLayoutBtn" onclick="torSaveLayout()" style="display:none;">Save Layout</button>
        <button class="tor-print-btn" onclick="window.print()">🖨 Print</button>
    </div>
</div>

<div class="tor-editor-toolbar" id="torEditorToolbar" aria-hidden="true">
    <div class="tor-toolbar-row">
        <select data-doc-font-family title="Font family">
            <option value="Arial">Arial</option>
            <option value="Times New Roman">Times New Roman</option>
            <option value="Courier New">Courier New</option>
            <option value="Georgia">Georgia</option>
        </select>
        <input type="number" data-doc-font-size title="Font size" min="6" max="96" step="0.5">
    </div>
    <div class="tor-toolbar-row">
        <button type="button" data-doc-style="bold" title="Bold">B</button>
        <button type="button" data-doc-style="italic" title="Italic"><em>I</em></button>
        <button type="button" data-doc-style="underline" title="Underline"><u>U</u></button>
        <select data-doc-text-align title="Text alignment">
            <option value="left">Left</option>
            <option value="center">Center</option>
            <option value="right">Right</option>
            <option value="justify">Justify</option>
        </select>
    </div>
    <label>Top <input type="number" data-doc-top min="0" max="100" step="0.1"></label>
    <label>Left <input type="number" data-doc-left min="0" max="100" step="0.1"></label>
    <button type="button" data-doc-delete title="Delete selected element">Delete Element</button>
</div>

<div class="tor-page">

    @php
        $prof      = $student->profile;
        $fullName  = $prof ? strtoupper(trim($prof->last_name . ', ' . $prof->first_name . ' ' . ($prof->middle_name ? $prof->middle_name[0].'.' : '') . ($prof->suffix ? ' ' . $prof->suffix : ''))) : strtoupper($student->name);
        $course    = optional($student->canonicalCourse)->name ?: $student->program ?: '—';
        $courseCode= optional($student->canonicalCourse)->code ?: '—';
        $photoUrl  = ($prof && $prof->profile_photo_path && file_exists(public_path($prof->profile_photo_path))) ? asset($prof->profile_photo_path) : null;
    @endphp

    {{-- Letterhead space — left blank because the school header (seal, school name,
         office name, "Official Transcript of Records" title) is pre-printed on the
         physical paper stock this prints onto, same as the Report of Grades sheet. --}}
    <div class="tor-header-space"></div>

    {{-- Editable page-1 frame: student data, scholastic record, grading system,
         remarks, legal notice, signatures, footer. Populated from the saved
         layout template (registrar-editable via "Edit Layout") with this
         student's data resolved into each text block server-side. --}}
    <div class="tor-page1-frame">
        <div class="tor-sheet" id="torSheet"></div>
        <div class="tor-photo-box-fixed">
            <div class="tor-sd-photo-box">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Student photo" style="width:100%;height:100%;object-fit:cover;">
                @endif
            </div>
            <div class="tor-sd-photo-caption">Not Valid Without University Seal and Student's Picture</div>
        </div>
        <div class="tor-footer-seal-circle" style="position:absolute; top:93.5%; left:7%;">PLP<br>SEAL</div>
    </div>

    {{-- Grade Tables --}}
    <div style="page-break-before:always;"></div>
    <div class="tor-header-space"></div>
    <div class="tor-name-line"><b>NAME</b><span>{{ $fullName }}</span></div>
    <div class="tor-name-line"><b>STUDENT NO.</b><span>{{ $student->student_no ?? '—' }}</span></div>

    @if($gradesBySyTerm->isEmpty())
        <p style="text-align:center;color:#888;font-size:10pt;margin:8mm 0;">No grade records on file.</p>
    @else
        <table class="tor-tbl">
            <thead>
                <tr>
                    <th rowspan="2" style="width:100px;">Course Number</th>
                    <th rowspan="2">Descriptive Title of the Course</th>
                    <th colspan="2">Grades</th>
                    <th rowspan="2" style="width:55px;">Credits</th>
                </tr>
                <tr>
                    <th style="width:70px;">Semestral</th>
                    <th style="width:80px;">Re-exam Completion</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gradesBySyTerm as $syTerm => $rows)
                    @php
                        [$sy, $term] = explode('|||', $syTerm);
                    @endphp
                    <tr class="tor-sem-row">
                        <td colspan="5">{{ $sy }}, {{ strtoupper($term) }} SEMESTER</td>
                    </tr>
                    @foreach($rows as $r)
                    @php
                        $grade = $r->final_grade;
                        $inc   = $r->inc;
                    @endphp
                    <tr>
                        <td>{{ $r->subject_code }}</td>
                        <td>{{ $r->description ?: $r->equiv_subject_code ?: '—' }}</td>
                        <td class="grade-cell">{{ $inc ? 'INC' : (is_numeric($grade) ? number_format((float) $grade, 2) : ($grade ?: '—')) }}</td>
                        <td class="grade-cell"></td>
                        <td class="grade-cell">{{ is_numeric($r->units) ? number_format((float)$r->units,2) : '—' }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- GWA Summary --}}
    <div style="margin-top:5mm;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        <div style="border:2px solid #000;padding:2mm 4mm;display:inline-flex;gap:8mm;align-items:center;font-size:9.5pt;">
            <div>
                <div style="font-size:8pt;margin-bottom:1mm;">GENERAL WEIGHTED AVERAGE</div>
                <div style="font-size:13pt;font-weight:700;">{{ $gwa !== null ? number_format($gwa,4) : 'N/A' }}</div>
            </div>
            <div style="border-left:1px solid #999;padding-left:6mm;">
                <div style="font-size:8pt;margin-bottom:1mm;">UNITS EARNED</div>
                <div style="font-size:13pt;font-weight:700;">{{ number_format($totalUnitsEarned,1) }}</div>
            </div>
        </div>
    </div>

    {{-- Continuation footer --}}
    <div class="tor-continuation-footer">
        <div>
            {{ now()->format('n/j/Y g:i A') }}<br>
            Prepared by: {{ optional(auth()->user())->name ?? 'Registrar Staff' }}<br>
            Checked by: {{ $assistantRegistrar->signer_name ?? 'Assistant Registrar' }}
        </div>
        <div style="text-align:right;">
            <em>Not valid without university seal</em>
        </div>
    </div>

</div>

<script src="{{ asset('js/document-layout-editor.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') { e.preventDefault(); window.print(); }
});

var torLayoutUrl = @json(route('registrar.registrar-menu.student-mgmt.student-records.print.tor.layout', ['student' => $student->id]));
var torSaveUrl = @json(route('registrar.registrar-menu.student-mgmt.student-records.print.tor.layout.save'));
var torCsrf = document.querySelector('meta[name="csrf-token"]');
torCsrf = torCsrf ? torCsrf.getAttribute('content') : '';

var torEditor = DocLayoutEditor.create({
    sheetEl: document.getElementById('torSheet'),
    toolbarEl: document.getElementById('torEditorToolbar'),
    loadUrl: torLayoutUrl,
    saveUrl: torSaveUrl,
    csrfToken: torCsrf,
    elementClass: 'tor-tpl-element',
    sheetClass: 'tor-sheet'
});

torEditor.load().then(function (layout) {
    torEditor.render(layout);
}).catch(function (error) {
    console.error('Unable to load TOR layout template.', error);
});

function torToggleEdit() {
    var editing = !document.body.classList.contains('tor-editing');
    document.body.classList.toggle('tor-editing', editing);
    torEditor.setEditable(editing);
    document.getElementById('torEditToggleBtn').classList.toggle('is-active', editing);
    document.getElementById('torSaveLayoutBtn').style.display = editing ? '' : 'none';
    if (!editing) torEditor.selectElement(null);
}

function torSaveLayout() {
    var btn = document.getElementById('torSaveLayoutBtn');
    btn.disabled = true;
    var originalText = btn.textContent;
    btn.textContent = 'Saving...';
    torEditor.save().then(function (data) {
        alert((data && data.message) || 'Layout saved.');
    }).catch(function (error) {
        var detail = error && error.errors ? Object.keys(error.errors).map(function (key) {
            return error.errors[key].join(' ');
        }).join('\n') : '';
        alert(detail || (error && error.message) || 'Unable to save layout.');
    }).then(function () {
        btn.disabled = false;
        btn.textContent = originalText;
    });
}
</script>
</body>
</html>
