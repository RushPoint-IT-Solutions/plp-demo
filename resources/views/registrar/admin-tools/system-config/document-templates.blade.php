@extends('layouts.registrar')

@section('title', 'PLP - Document Templates')
@section('page-title', 'DOCUMENT TEMPLATES')

@push('scripts')
    <script src="{{ asset('js/document-layout-editor.js') }}?v={{ file_exists(public_path('js/document-layout-editor.js')) ? filemtime(public_path('js/document-layout-editor.js')) : time() }}"></script>
    <script src="{{ asset('js/reports-student-autocomplete.js') }}?v={{ file_exists(public_path('js/reports-student-autocomplete.js')) ? filemtime(public_path('js/reports-student-autocomplete.js')) : time() }}"></script>
@endpush

@php
    $selectedSlug = trim((string) request('document', $documents[0]['slug'] ?? ''));
    $selectedDoc = collect($documents)->firstWhere('slug', $selectedSlug) ?: ($documents[0] ?? null);
    $groupedDocs = collect($documents)->groupBy('group');
    $previewStudentId = (int) request('student_id', 0);
@endphp

@section('content')
<div class="pf-page dt-page">
    <style>
        .dt-toolbar { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:14px 16px; margin-bottom:16px; display:flex; align-items:flex-end; gap:14px; flex-wrap:wrap; }
        .dt-field label { display:block; margin-bottom:5px; color:#46564a; font-size:.78rem; font-weight:800; text-transform:uppercase; }
        .dt-field select, .dt-field input { min-height:38px; border:1px solid #cfd9d2; border-radius:7px; background:#fff; color:#143521; padding:8px 10px; min-width:260px; }
        .dt-note { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:16px; color:#46564a; font-size:.88rem; }
        .dt-note strong { color:#123822; }
        .dt-badge { display:inline-block; padding:2px 9px; border-radius:20px; font-size:.72rem; font-weight:800; margin-left:8px; }
        .dt-badge.has-editor { background:#eef6f1; color:#146c43; }
        .dt-badge.no-editor { background:#f1f5f9; color:#64748b; }

        /* editable canvas (mirrors COR/TOR pattern, scoped as dt-*) */
        .dt-sheet-wrap { width:100%; display:grid; place-items:center; padding:8px 0 14px; overflow-x:auto; }
        .dt-sheet { width:816px; min-height:600px; background:#fff; padding:24px 34px; border:1px solid #111; position:relative; font-family:Arial, Helvetica, sans-serif; font-size:11px; }
        .dt-sheet-editable { position:relative; min-height:560px; }
        .dt-tpl-element { position:absolute; white-space:pre-wrap; outline:none; cursor:default; padding:1px 3px; border:1px dashed transparent; }
        body.dt-editing .dt-tpl-element { cursor:move; }
        body.dt-editing .dt-tpl-element:hover { border-color:#0a7a3f66; }
        .dt-tpl-element.is-selected { border-color:#0a7a3f; background:rgba(10,122,63,.06); }
        .dt-edit-actions { display:flex; gap:8px; margin-bottom:10px; }
        .dt-edit-toggle-btn { height:36px; border:1px solid #94a3b8; background:#fff; color:#0f172a; border-radius:6px; padding:0 16px; font-size:13px; font-weight:700; cursor:pointer; }
        .dt-edit-toggle-btn.is-active { background:#0a7a3f; border-color:#0a7a3f; color:#fff; }
        .dt-editor-toolbar { display:none; position:fixed; top:120px; right:16px; z-index:200; width:220px; padding:10px; border:1px solid #cbd5d1; border-radius:8px; background:#fff; box-shadow:0 8px 20px rgba(0,0,0,.18); gap:7px; font-family:Arial, sans-serif; }
        .dt-editor-toolbar.is-visible { display:grid; }
        .dt-editor-toolbar select, .dt-editor-toolbar input, .dt-editor-toolbar button { height:30px; border:1px solid #cbd5d1; border-radius:5px; font-size:.78rem; font-family:Arial, sans-serif; }
        .dt-editor-toolbar button { font-weight:800; cursor:pointer; background:#fff; }
        .dt-editor-toolbar button.is-active { background:#0a7a3f; border-color:#0a7a3f; color:#fff; }
        .dt-editor-toolbar label { display:grid; grid-template-columns:42px 1fr; align-items:center; gap:6px; font-size:.74rem; font-weight:700; color:#333; }
        .dt-editor-toolbar .dt-toolbar-row { display:flex; gap:6px; }
        .dt-editor-toolbar .dt-toolbar-row select { flex:1; }
        .dt-editor-toolbar .dt-toolbar-row button { flex:0 0 30px; }
    </style>

    <form method="GET" action="{{ route('registrar.admin-tools.system-config.document-templates') }}" class="dt-toolbar">
        <div class="dt-field">
            <label for="dtDocumentSelect">Select Document</label>
            <select id="dtDocumentSelect" name="document" onchange="this.form.submit()">
                @foreach($groupedDocs as $groupName => $groupDocs)
                    <optgroup label="{{ $groupName }}">
                        @foreach($groupDocs as $doc)
                            <option value="{{ $doc['slug'] }}" {{ $selectedSlug === $doc['slug'] ? 'selected' : '' }}>{{ $doc['label'] }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        @if($selectedDoc && $selectedDoc['editor'] === 'generic')
            <div class="dt-field">
                <label for="dtStudentSearch">Preview With Student (optional)</label>
                <div class="rsa-wrap">
                    <input type="hidden" name="student_id" id="dtStudentId" value="{{ $previewStudentId ?: '' }}">
                    <input type="text" id="dtStudentSearch" placeholder="Type student no. or name"
                        data-student-autocomplete="reports"
                        data-student-id-target="dtStudentId"
                        data-student-search-url="{{ route('registrar.services.reports-admin.students.search') }}">
                </div>
            </div>
            <button type="submit" class="pf-btn-new">Load Preview</button>
        @endif
    </form>

    @if(!$selectedDoc)
        <div class="dt-note">No document types are registered.</div>
    @elseif($selectedDoc['editor'] === 'dedicated')
        <div class="dt-note">
            <strong>{{ $selectedDoc['label'] }}</strong> already has its own header layout editor
            <span class="dt-badge has-editor">Editable</span><br><br>
            This document is edited in place, from its own live page (open a student, then click "Edit Header" there).
            @if($selectedDoc['live_url'])
                <br><br><a href="{{ $selectedDoc['live_url'] }}" target="_blank" class="pf-btn-new" style="display:inline-block;text-decoration:none;">Open {{ $selectedDoc['label'] }}</a>
            @endif
        </div>
    @elseif($selectedDoc['editor'] === 'generic')
        <div class="dt-edit-actions">
            <button type="button" class="dt-edit-toggle-btn" id="dtEditToggleBtn" onclick="dtToggleEdit()">&#9998; Edit Layout</button>
            <button type="button" class="dt-edit-toggle-btn" id="dtSaveLayoutBtn" onclick="dtSaveLayout()" style="display:none;">Save Layout</button>
        </div>

        <div class="dt-editor-toolbar" id="dtEditorToolbar" aria-hidden="true">
            <div class="dt-toolbar-row">
                <select data-doc-font-family title="Font family">
                    <option value="Arial">Arial</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Courier New">Courier New</option>
                    <option value="Georgia">Georgia</option>
                </select>
                <input type="number" data-doc-font-size title="Font size" min="6" max="96" step="0.5">
            </div>
            <div class="dt-toolbar-row">
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

        <div class="dt-sheet-wrap">
            <article class="dt-sheet">
                <div id="dtSheet" class="dt-sheet-editable"></div>
            </article>
        </div>

        @push('scripts')
        <script>
        (function () {
            var slug = @json($selectedDoc['slug']);
            var studentId = @json($previewStudentId > 0 ? $previewStudentId : null);
            var loadUrlTemplate = @json(route('registrar.admin-tools.system-config.document-templates.layout', ['slug' => $selectedDoc['slug'], 'student' => '__STUDENT__']));
            var blankLoadUrl = @json(route('registrar.admin-tools.system-config.document-templates.layout', ['slug' => $selectedDoc['slug']]));
            var saveUrl = @json(route('registrar.admin-tools.system-config.document-templates.layout.save', ['slug' => $selectedDoc['slug']]));
            var csrf = document.querySelector('meta[name="csrf-token"]');
            csrf = csrf ? csrf.getAttribute('content') : @json(csrf_token());

            var dtEditor = DocLayoutEditor.create({
                sheetEl: document.getElementById('dtSheet'),
                toolbarEl: document.getElementById('dtEditorToolbar'),
                loadUrl: studentId ? loadUrlTemplate.replace('__STUDENT__', studentId) : blankLoadUrl,
                saveUrl: saveUrl,
                csrfToken: csrf,
                elementClass: 'dt-tpl-element',
                sheetClass: 'dt-sheet-editable'
            });

            dtEditor.load().then(function (layout) {
                dtEditor.render(layout);
            }).catch(function (error) {
                console.error('Unable to load ' + slug + ' layout template.', error);
            });

            window.dtToggleEdit = function () {
                var editing = !document.body.classList.contains('dt-editing');
                document.body.classList.toggle('dt-editing', editing);
                dtEditor.setEditable(editing);
                document.getElementById('dtEditToggleBtn').classList.toggle('is-active', editing);
                document.getElementById('dtSaveLayoutBtn').style.display = editing ? '' : 'none';
                if (!editing) dtEditor.selectElement(null);
            };

            window.dtSaveLayout = function () {
                var btn = document.getElementById('dtSaveLayoutBtn');
                btn.disabled = true;
                var originalText = btn.textContent;
                btn.textContent = 'Saving...';
                dtEditor.save().then(function (data) {
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
            };
        })();
        </script>
        @endpush
    @else
        <div class="dt-note">
            <strong>{{ $selectedDoc['label'] }}</strong> does not have an editable template yet
            <span class="dt-badge no-editor">Not available</span><br><br>
            This document currently prints from a fixed layout. Wiring it up to this editor is a follow-up task.
        </div>
    @endif
</div>
@endsection
