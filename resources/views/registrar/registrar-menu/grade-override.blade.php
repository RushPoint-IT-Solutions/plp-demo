@extends('layouts.registrar')

@section('title', 'PLP - Grade Override')
@section('page-title', 'GRADE OVERRIDE')

@section('content')
<div class="go-page"
    id="gradeOverridePage"
    data-terms-url="{{ route('registrar.admin-tools.grade-override.terms') }}"
    data-subjects-url="{{ route('registrar.admin-tools.grade-override.subjects') }}"
    data-students-url="{{ route('registrar.admin-tools.grade-override.students') }}"
    data-update-url="{{ route('registrar.admin-tools.grade-override.update') }}"
    data-logs-url="{{ route('registrar.admin-tools.grade-override.logs') }}"
    data-is-admin="{{ $isAdmin ? '1' : '0' }}"
    data-csrf="{{ csrf_token() }}">

    <style>
        /* ── Base ── */
        .go-page { color:#143521; }
        .go-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:16px; margin-bottom:16px; }
        .go-title { margin:0 0 10px; font-size:1rem; font-weight:800; color:#123822; }
        .go-guide { border:1px solid #d7eadf; background:#f3fbf6; border-radius:8px; padding:10px 12px; color:#315a3f; font-size:.84rem; line-height:1.5; margin-bottom:14px; }
        .go-admin-badge { display:inline-flex; align-items:center; gap:5px; background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:5px 10px; font-size:.78rem; font-weight:700; color:#7a5000; margin-bottom:14px; }
        .go-warn-box { background:#fff8e8; border:1px solid #e8d18a; border-radius:8px; padding:12px 14px; color:#7a5000; font-size:.84rem; font-weight:600; margin-bottom:14px; }

        /* ── Filters ── */
        .go-filter-row { display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; }
        .go-field label { display:block; color:#46564a; font-size:.76rem; font-weight:800; text-transform:uppercase; margin-bottom:5px; }
        .go-select { min-width:240px; min-height:38px; border:1px solid #cfd9d2; border-radius:7px; padding:8px 10px; background:#fff; font-size:.88rem; }
        .go-select:disabled { opacity:.55; cursor:not-allowed; }
        .go-select:focus { outline:none; border-color:#146c43; box-shadow:0 0 0 3px rgba(20,108,67,.1); }

        /* ── Buttons ── */
        .go-btn { border:0; border-radius:7px; min-height:36px; padding:7px 14px; background:#146c43; color:#fff; font-weight:700; cursor:pointer; font-size:.84rem; display:inline-flex; align-items:center; gap:6px; transition:background .15s; }
        .go-btn:hover:not(:disabled) { background:#115a38; }
        .go-btn.secondary { background:#eef6f1; color:#146c43; border:1px solid #b9d8c5; }
        .go-btn.secondary:hover:not(:disabled) { background:#ddf0e6; }
        .go-btn.ghost { background:transparent; color:#46564a; border:1px solid #cfd9d2; }
        .go-btn.ghost:hover:not(:disabled) { background:#f5faf7; border-color:#a8c4b0; }
        .go-btn.warn { background:#7a5000; }
        .go-btn.warn:hover:not(:disabled) { background:#5e3d00; }
        .go-btn:disabled { opacity:.5; cursor:not-allowed; }
        .go-spinner { width:13px; height:13px; border:2px solid currentColor; border-top-color:transparent; border-radius:50%; animation:go-spin .7s linear infinite; flex-shrink:0; }
        @keyframes go-spin { to { transform:rotate(360deg); } }

        /* ── Alert ── */
        .go-alert { display:none; border-radius:8px; padding:9px 14px; margin-bottom:12px; font-size:.85rem; font-weight:700; padding-right:38px; position:relative; }
        .go-alert.show { display:block; }
        .go-alert.success { background:#e8f6ee; color:#17633a; border:1px solid #bfdfcc; }
        .go-alert.error   { background:#fdecec; color:#9f1d1d; border:1px solid #efb8b8; }
        .go-alert-close { position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:1.1rem; opacity:.5; line-height:1; padding:2px 4px; }
        .go-alert-close:hover { opacity:1; }

        /* ── Subject tabs ── */
        .go-tabs-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; margin-bottom:16px; overflow:hidden; }
        .go-tabs-head { padding:12px 16px 0; border-bottom:1px solid #e3ece6; background:#f9fdfb; }
        .go-tabs-label { font-size:.74rem; font-weight:800; color:#46564a; text-transform:uppercase; margin-bottom:10px; }
        .go-tabs { display:flex; flex-wrap:nowrap; gap:5px; overflow-x:auto; padding-bottom:0; scrollbar-width:thin; }
        .go-tabs::-webkit-scrollbar { height:3px; }
        .go-tabs::-webkit-scrollbar-thumb { background:#b9d8c5; border-radius:2px; }
        .go-tab { border:1px solid #cfd9d2; border-radius:7px 7px 0 0; background:#f5faf7; color:#315a3f; cursor:pointer; padding:7px 12px; font-size:.78rem; text-align:left; min-width:120px; max-width:180px; border-bottom:2px solid transparent; transition:background .12s; flex-shrink:0; }
        .go-tab:hover { background:#edf5f0; }
        .go-tab.active { background:#fff; border-color:#146c43; border-bottom-color:#fff; color:#123822; position:relative; top:1px; }
        .go-tab-code { font-weight:800; font-size:.8rem; white-space:nowrap; }
        .go-tab-name { font-size:.68rem; opacity:.82; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px; margin-top:1px; }
        .go-tab-section { font-size:.65rem; opacity:.65; white-space:nowrap; }
        .go-tab-edited { display:inline-block; width:7px; height:7px; border-radius:50%; background:#d48b00; margin-left:4px; vertical-align:middle; title:"Has edit history"; }
        .go-tabs-empty { padding:14px 16px; color:#66756b; font-size:.84rem; }
        .go-tabs-body { padding:16px; }

        /* ── Grade table ── */
        .go-table-wrap { overflow-x:auto; border:1px solid #e3ece6; border-radius:8px; }
        .go-table { width:100%; border-collapse:collapse; min-width:780px; }
        .go-table th, .go-table td { border-bottom:1px solid #edf3ef; padding:9px 12px; text-align:left; vertical-align:middle; font-size:.86rem; }
        .go-table th { background:#f5faf7; color:#46564a; font-size:.74rem; font-weight:800; text-transform:uppercase; white-space:nowrap; }
        .go-table tbody tr:last-child td { border-bottom:none; }
        .go-table tbody tr:hover td { background:#f3faf6; }
        .go-table tbody tr.has-edits td:first-child { border-left:3px solid #d48b00; }
        .go-muted { color:#66756b; font-size:.8rem; }
        .go-grade-cell { text-align:center; min-width:70px; }
        .go-grade-val { font-weight:700; font-size:.9rem; }
        .go-grade-none { color:#aab8ae; font-style:italic; font-size:.82rem; }

        /* ── Inline edit form ── */
        .go-edit-row td { background:#f9fdfb !important; border-bottom:2px solid #146c43 !important; }
        .go-edit-form { display:flex; flex-direction:column; gap:10px; padding:4px 0; }
        .go-edit-fields { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
        .go-edit-field label { display:block; font-size:.72rem; font-weight:800; text-transform:uppercase; color:#46564a; margin-bottom:4px; }
        .go-input { border:1px solid #cfd9d2; border-radius:6px; padding:6px 10px; font-size:.88rem; min-width:90px; max-width:130px; }
        .go-input:focus { outline:none; border-color:#146c43; box-shadow:0 0 0 3px rgba(20,108,67,.1); }
        .go-reason-row { display:flex; gap:8px; align-items:flex-start; }
        .go-reason-wrap { flex:1; }
        .go-reason-wrap label { display:block; font-size:.72rem; font-weight:800; text-transform:uppercase; color:#9f1d1d; margin-bottom:4px; }
        .go-reason-input { width:100%; border:1px solid #efb8b8; border-radius:6px; padding:6px 10px; font-size:.85rem; resize:vertical; min-height:36px; }
        .go-reason-input:focus { outline:none; border-color:#9f1d1d; box-shadow:0 0 0 3px rgba(159,29,29,.08); }
        .go-edit-actions { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
        .go-save-note { font-size:.72rem; color:#66756b; }

        /* ── History modal ── */
        .go-history-modal { position:fixed; inset:0; display:none; background:rgba(0,0,0,.48); z-index:1100; justify-content:center; align-items:flex-start; padding:40px 20px; overflow-y:auto; opacity:0; transition:opacity .18s; }
        .go-history-modal.show { display:flex; }
        .go-history-modal.go-visible { opacity:1; }
        .go-history-panel { width:min(680px,97vw); background:#fff; border-radius:10px; overflow:hidden; transform:translateY(10px); transition:transform .2s; }
        .go-history-modal.go-visible .go-history-panel { transform:translateY(0); }
        .go-history-head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:14px 16px 12px; border-bottom:1px solid #e3ece6; }
        .go-history-head h3 { margin:0; font-size:1rem; font-weight:800; color:#123822; }
        .go-history-meta { font-size:.78rem; color:#66756b; margin-top:2px; }
        .go-history-body { padding:16px; max-height:60vh; overflow-y:auto; }
        .go-log-entry { border:1px solid #e3ece6; border-radius:8px; padding:10px 12px; margin-bottom:10px; background:#fafffe; }
        .go-log-entry:last-child { margin-bottom:0; }
        .go-log-header { display:flex; justify-content:space-between; align-items:flex-start; gap:8px; margin-bottom:6px; }
        .go-log-field { display:inline-block; background:#eef6f1; color:#146c43; border-radius:5px; padding:2px 8px; font-size:.72rem; font-weight:800; text-transform:uppercase; }
        .go-log-time { font-size:.72rem; color:#66756b; }
        .go-log-change { display:flex; gap:8px; align-items:center; font-size:.84rem; margin-bottom:5px; }
        .go-log-old { color:#9f1d1d; background:#fdecec; border-radius:4px; padding:1px 7px; font-weight:700; }
        .go-log-arrow { color:#66756b; }
        .go-log-new { color:#17633a; background:#e8f6ee; border-radius:4px; padding:1px 7px; font-weight:700; }
        .go-log-reason { font-size:.78rem; color:#46564a; background:#f5faf7; border-radius:5px; padding:5px 9px; margin-top:4px; font-style:italic; }
        .go-log-by { font-size:.72rem; color:#66756b; margin-top:4px; }
        .go-log-empty { color:#66756b; font-size:.84rem; padding:8px 0; }
        .go-history-footer { padding:12px 16px; border-top:1px solid #e3ece6; text-align:right; background:#f9fdfb; }
    </style>

    {{-- ── Filter Panel ── --}}
    <section class="go-panel">
        <h2 class="go-title">Grade Override &amp; Edit History</h2>

        @if($isAdmin)
            <div class="go-admin-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Administrator Mode — Grade edits are logged with reason and timestamp
            </div>
        @else
            <div class="go-warn-box">
                &#128274; View-only mode. Only administrators can edit grades.
            </div>
        @endif

        <div class="go-guide">
            Select a program and academic term to load all subject offerings. Click a subject tab to view enrolled students and their grades. Administrators can click any grade cell to override it — a reason is required and every change is permanently logged.
        </div>

        <div class="go-filter-row">
            <div class="go-field">
                <label for="goProgram">Program</label>
                <select class="go-select" id="goProgram">
                    @foreach($programOptions as $p)
                        <option value="{{ $p['id'] }}">{{ $p['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="go-field">
                <label for="goTerm">Academic Term</label>
                <select class="go-select" id="goTerm" disabled>
                    <option value="">— select program first —</option>
                </select>
            </div>
            <button type="button" class="go-btn" id="goLoadBtn" disabled>Load Subjects</button>
        </div>
    </section>

    {{-- ── Alert ── --}}
    <div class="go-alert" id="goAlert" role="alert">
        <span id="goAlertBody"></span>
        <button type="button" class="go-alert-close" id="goAlertClose">&times;</button>
    </div>

    {{-- ── Subject Tabs + Grade Table ── --}}
    <div class="go-tabs-panel" id="goTabsPanel" style="display:none;">
        <div class="go-tabs-head">
            <div class="go-tabs-label">Subject Offerings</div>
            <div class="go-tabs" id="goTabs"></div>
        </div>
        <div class="go-tabs-body" id="goTabsBody">
            <div class="go-muted">Select a subject tab above to view students.</div>
        </div>
    </div>

    {{-- ── History Modal ── --}}
    <div class="go-history-modal" id="goHistoryModal" aria-hidden="true" role="dialog" aria-labelledby="goHistoryTitle" aria-modal="true">
        <div class="go-history-panel">
            <div class="go-history-head">
                <div>
                    <h3 id="goHistoryTitle">Edit History</h3>
                    <div class="go-history-meta" id="goHistoryMeta"></div>
                </div>
                <button type="button" class="go-btn secondary" id="goCloseHistory" style="padding:6px 10px;">&#x2715;</button>
            </div>
            <div class="go-history-body" id="goHistoryBody">
                <div class="go-muted">Loading…</div>
            </div>
            <div class="go-history-footer">
                <button type="button" class="go-btn secondary" id="goCloseHistory2">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var page       = document.getElementById('gradeOverridePage');
    if (!page) return;
    var isAdmin    = page.dataset.isAdmin === '1';
    var programSel = document.getElementById('goProgram');
    var termSel    = document.getElementById('goTerm');
    var loadBtn    = document.getElementById('goLoadBtn');
    var alertBox   = document.getElementById('goAlert');
    var alertBody  = document.getElementById('goAlertBody');
    var tabsPanel  = document.getElementById('goTabsPanel');
    var tabsWrap   = document.getElementById('goTabs');
    var tabsBody   = document.getElementById('goTabsBody');
    var histModal  = document.getElementById('goHistoryModal');
    var histTitle  = document.getElementById('goHistoryTitle');
    var histMeta   = document.getElementById('goHistoryMeta');
    var histBody   = document.getElementById('goHistoryBody');
    var alertTimer = null;
    var currentSubjectId = null;
    var allSubjects = [];
    var currentStudents = [];

    /* ── Utilities ── */
    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
        });
    }
    function showAlert(type, msg, autoDismiss) {
        clearTimeout(alertTimer);
        alertBody.textContent = msg;
        alertBox.className = 'go-alert show ' + type;
        if (autoDismiss !== false && type === 'success') {
            alertTimer = setTimeout(function () { alertBox.className = 'go-alert'; }, 5000);
        }
    }
    document.getElementById('goAlertClose').addEventListener('click', function () {
        clearTimeout(alertTimer);
        alertBox.className = 'go-alert';
    });
    function fmt(v) { return v != null ? String(v) : '—'; }
    function fmtDate(s) {
        if (!s) return '';
        var d = new Date(s);
        if (isNaN(d)) return s;
        return d.toLocaleDateString('en-PH', {year:'numeric',month:'short',day:'numeric'})
            + ' ' + d.toLocaleTimeString('en-PH', {hour:'2-digit',minute:'2-digit'});
    }
    function fieldLabel(f) {
        return {midterm:'Midterm', final:'Finals', final_average:'Final Average', remarks:'Remarks'}[f] || f;
    }

    /* ── Modal helpers ── */
    function openModal(el) {
        el.classList.add('show');
        el.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(function () { requestAnimationFrame(function () { el.classList.add('go-visible'); }); });
    }
    function closeModal(el) {
        el.classList.remove('go-visible');
        setTimeout(function () {
            el.classList.remove('show');
            el.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }, 180);
    }
    document.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.keyCode === 27) && histModal.classList.contains('show')) closeModal(histModal);
    });
    histModal.addEventListener('click', function (e) { if (e.target === histModal) closeModal(histModal); });
    document.getElementById('goCloseHistory').addEventListener('click', function () { closeModal(histModal); });
    document.getElementById('goCloseHistory2').addEventListener('click', function () { closeModal(histModal); });

    /* ── Load terms when program changes ── */
    function loadTerms() {
        termSel.disabled = true;
        loadBtn.disabled = true;
        termSel.innerHTML = '<option value="">Loading…</option>';
        fetch(page.dataset.termsUrl + '?course_id=' + encodeURIComponent(programSel.value), { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (p) {
                if (!p.terms || !p.terms.length) {
                    termSel.innerHTML = '<option value="">No terms found</option>';
                    return;
                }
                termSel.innerHTML = p.terms.map(function (t) {
                    return '<option value="' + esc(t.id) + '">' + esc(t.label) + ' [' + esc(t.status) + ']</option>';
                }).join('');
                termSel.disabled = false;
                loadBtn.disabled = false;
            })
            .catch(function () {
                termSel.innerHTML = '<option value="">Error loading terms</option>';
            });
    }

    /* ── Load subjects ── */
    function loadSubjects() {
        if (!termSel.value) return;
        loadBtn.disabled = true;
        loadBtn.innerHTML = '<span class="go-spinner"></span> Loading…';
        tabsPanel.style.display = 'none';
        tabsWrap.innerHTML = '';
        tabsBody.innerHTML = '<div class="go-muted">Loading subjects…</div>';

        var params = new URLSearchParams({ academic_term_id: termSel.value, course_id: programSel.value });
        fetch(page.dataset.subjectsUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (p) {
                allSubjects = p.subjects || [];
                if (!allSubjects.length) {
                    tabsBody.innerHTML = '<div class="go-muted">No subject offerings found for this term.</div>';
                    tabsPanel.style.display = '';
                    return;
                }
                renderSubjectTabs();
                tabsPanel.style.display = '';
                /* Auto-select first tab */
                selectSubject(allSubjects[0]);
            })
            .catch(function () { showAlert('error', 'Unable to load subjects.'); })
            .finally(function () {
                loadBtn.disabled = false;
                loadBtn.textContent = 'Load Subjects';
            });
    }

    /* ── Subject tabs ── */
    function renderSubjectTabs(activeId) {
        tabsWrap.innerHTML = allSubjects.map(function (s) {
            var isActive = String(s.id) === String(activeId || (allSubjects[0] || {}).id);
            return '<button type="button" class="go-tab' + (isActive ? ' active' : '') + '" data-subject-id="' + s.id + '">'
                + '<div class="go-tab-code">' + esc(s.code) + '</div>'
                + '<div class="go-tab-name">' + esc(s.name) + '</div>'
                + (s.year_section ? '<div class="go-tab-section">' + esc(s.year_section) + '</div>' : '')
                + '</button>';
        }).join('');
    }
    tabsWrap.addEventListener('click', function (e) {
        var tab = e.target.closest('[data-subject-id]');
        if (!tab) return;
        var subjectId = parseInt(tab.getAttribute('data-subject-id'), 10);
        var subject = allSubjects.find(function (s) { return s.id === subjectId; });
        if (subject) selectSubject(subject);
    });

    /* ── Load students for a subject ── */
    function selectSubject(subject) {
        currentSubjectId = subject.id;
        renderSubjectTabs(subject.id);
        tabsBody.innerHTML = '<div class="go-muted" style="padding:12px 0;text-align:center;"><span class="go-spinner" style="border-color:#146c43;border-top-color:transparent;"></span> Loading students…</div>';

        fetch(page.dataset.studentsUrl + '?subject_id=' + encodeURIComponent(subject.id), { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (p) {
                currentStudents = p.students || [];
                renderGradeTable(subject);
            })
            .catch(function () {
                tabsBody.innerHTML = '<div class="go-muted">Unable to load students.</div>';
            });
    }

    /* ── Grade table ── */
    function renderGradeTable(subject) {
        if (!currentStudents.length) {
            tabsBody.innerHTML = '<div class="go-muted" style="padding:12px 0;">No enrolled students found for this subject.</div>';
            return;
        }

        var subjectInfo = '<div style="margin-bottom:12px;">'
            + '<strong style="font-size:.92rem;">' + esc(subject.code) + ' — ' + esc(subject.name) + '</strong>'
            + (subject.year_section ? '<span class="go-muted" style="margin-left:8px;">Section: ' + esc(subject.year_section) + '</span>' : '')
            + (subject.teacher ? '<span class="go-muted" style="margin-left:8px;">Teacher: ' + esc(subject.teacher) + '</span>' : '')
            + '</div>';

        var tableHtml = '<div class="go-table-wrap"><table class="go-table" data-no-auto-pager="1"><thead><tr>'
            + '<th>Student</th>'
            + '<th class="go-grade-cell">Midterm</th>'
            + '<th class="go-grade-cell">Finals</th>'
            + '<th class="go-grade-cell">Final Average</th>'
            + '<th>Remarks</th>'
            + '<th style="min-width:120px;">Actions</th>'
            + '</tr></thead><tbody id="goGradeTableBody">'
            + currentStudents.map(function (st, idx) {
                return buildStudentRow(st, idx, subject.id);
            }).join('')
            + '</tbody></table></div>';

        tabsBody.innerHTML = subjectInfo + tableHtml;
    }

    function buildStudentRow(st, idx, subjectId) {
        var midVal = st.midterm != null ? st.midterm : null;
        var finVal = st.final != null ? st.final : null;
        var avgVal = st.final_average != null ? st.final_average : null;

        function gradeCell(val) {
            if (val == null) return '<span class="go-grade-none">—</span>';
            var cls = val >= 75 ? 'color:#146c43;' : 'color:#9f1d1d;';
            return '<span class="go-grade-val" style="' + cls + '">' + esc(val) + '</span>';
        }

        var hasEdits = !!st.has_edits;
        var editDot = hasEdits ? '<span class="go-tab-edited" title="Has edit history"></span>' : '';

        return '<tr data-idx="' + idx + '" ' + (hasEdits ? 'class="has-edits"' : '') + '>'
            + '<td>'
                + '<strong style="font-size:.88rem;">' + esc(st.student_no) + '</strong>' + editDot
                + '<div class="go-muted">' + esc(st.name) + '</div>'
                + (st.grade_updated_at ? '<div class="go-muted" style="font-size:.7rem;">Last updated: ' + esc(fmtDate(st.grade_updated_at)) + '</div>' : '')
            + '</td>'
            + '<td class="go-grade-cell" data-field="midterm">' + gradeCell(midVal) + '</td>'
            + '<td class="go-grade-cell" data-field="final">' + gradeCell(finVal) + '</td>'
            + '<td class="go-grade-cell" data-field="final_average">' + gradeCell(avgVal) + '</td>'
            + '<td data-field="remarks">'
                + (st.remarks ? '<span style="font-size:.82rem;">' + esc(st.remarks) + '</span>' : '<span class="go-grade-none">—</span>')
            + '</td>'
            + '<td>'
                + '<div style="display:flex;gap:5px;flex-wrap:wrap;">'
                + (isAdmin ? '<button type="button" class="go-btn ghost go-edit-btn" style="font-size:.76rem;padding:4px 9px;" data-idx="' + idx + '" data-subject-id="' + subjectId + '">Edit Grade</button>' : '')
                + '<button type="button" class="go-btn ghost go-history-btn" style="font-size:.76rem;padding:4px 9px;" data-idx="' + idx + '" data-subject-id="' + subjectId + '">History</button>'
                + '</div>'
            + '</td>'
            + '</tr>';
    }

    /* ── Edit grade inline ── */
    var activeEditIdx = null;

    tabsBody.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.go-edit-btn');
        if (editBtn && isAdmin) {
            var idx = parseInt(editBtn.dataset.idx, 10);
            var subjectId = parseInt(editBtn.dataset.subjectId, 10);
            openEditRow(idx, subjectId);
            return;
        }

        var histBtn = e.target.closest('.go-history-btn');
        if (histBtn) {
            var idx = parseInt(histBtn.dataset.idx, 10);
            var subjectId = parseInt(histBtn.dataset.subjectId, 10);
            var st = currentStudents[idx];
            if (st) openHistory(st, subjectId);
            return;
        }

        var cancelBtn = e.target.closest('.go-cancel-edit');
        if (cancelBtn) {
            cancelEdit();
        }
    });

    function openEditRow(idx, subjectId) {
        if (activeEditIdx !== null) cancelEdit();
        activeEditIdx = idx;
        var tbody = document.getElementById('goGradeTableBody');
        if (!tbody) return;
        var row = tbody.querySelector('tr[data-idx="' + idx + '"]');
        if (!row) return;
        var st = currentStudents[idx];

        var editRow = document.createElement('tr');
        editRow.className = 'go-edit-row';
        editRow.id = 'goEditRow';
        editRow.innerHTML = '<td colspan="6">'
            + '<div class="go-edit-form">'
                + '<div style="font-size:.82rem;font-weight:700;color:#123822;margin-bottom:2px;">'
                    + 'Editing: ' + esc(st.student_no) + ' — ' + esc(st.name)
                + '</div>'
                + '<div class="go-edit-fields">'
                    + '<div class="go-edit-field">'
                        + '<label>Midterm</label>'
                        + '<input type="number" class="go-input" id="goEditMidterm" step="0.01" min="0" max="100" placeholder="—" value="' + (st.midterm != null ? esc(st.midterm) : '') + '">'
                    + '</div>'
                    + '<div class="go-edit-field">'
                        + '<label>Finals</label>'
                        + '<input type="number" class="go-input" id="goEditFinal" step="0.01" min="0" max="100" placeholder="—" value="' + (st.final != null ? esc(st.final) : '') + '">'
                    + '</div>'
                    + '<div class="go-edit-field">'
                        + '<label>Final Average</label>'
                        + '<input type="number" class="go-input" id="goEditAvg" step="0.01" min="0" max="100" placeholder="—" value="' + (st.final_average != null ? esc(st.final_average) : '') + '">'
                    + '</div>'
                    + '<div class="go-edit-field" style="min-width:160px;">'
                        + '<label>Remarks</label>'
                        + '<input type="text" class="go-input" id="goEditRemarks" style="max-width:180px;" placeholder="e.g. Passed" value="' + esc(st.remarks || '') + '">'
                    + '</div>'
                + '</div>'
                + '<div class="go-reason-row">'
                    + '<div class="go-reason-wrap">'
                        + '<label>Reason for Override <span style="color:#9f1d1d;">*</span></label>'
                        + '<textarea class="go-reason-input" id="goEditReason" placeholder="Required — explain why this grade is being changed (min 5 chars)…" rows="2"></textarea>'
                    + '</div>'
                + '</div>'
                + '<div class="go-edit-actions">'
                    + '<button type="button" class="go-btn warn go-save-btn" data-idx="' + idx + '" data-subject-id="' + subjectId + '">Save Changes</button>'
                    + '<button type="button" class="go-btn secondary go-cancel-edit">Cancel</button>'
                    + '<span class="go-save-note">All fields are optional except Reason. Leave blank to keep existing value.</span>'
                + '</div>'
            + '</div>'
            + '</td>';

        row.insertAdjacentElement('afterend', editRow);
        document.getElementById('goEditReason').focus();

        /* Save handler */
        editRow.querySelector('.go-save-btn').addEventListener('click', function () {
            saveGrades(idx, subjectId);
        });
    }

    function cancelEdit() {
        var editRow = document.getElementById('goEditRow');
        if (editRow) editRow.remove();
        activeEditIdx = null;
    }

    function saveGrades(idx, subjectId) {
        var st = currentStudents[idx];
        var reason = (document.getElementById('goEditReason').value || '').trim();
        if (reason.length < 5) {
            document.getElementById('goEditReason').style.borderColor = '#9f1d1d';
            document.getElementById('goEditReason').focus();
            showAlert('error', 'Reason is required and must be at least 5 characters.', false);
            return;
        }

        var fields = [
            { field: 'midterm',       inputId: 'goEditMidterm', orig: st.midterm },
            { field: 'final',         inputId: 'goEditFinal',   orig: st.final },
            { field: 'final_average', inputId: 'goEditAvg',     orig: st.final_average },
            { field: 'remarks',       inputId: 'goEditRemarks', orig: st.remarks },
        ];

        var changes = [];
        fields.forEach(function (f) {
            var input = document.getElementById(f.inputId);
            if (!input) return;
            var val = input.value.trim();
            var origStr = f.orig != null ? String(f.orig) : '';
            if (val !== origStr && !(val === '' && f.orig == null)) {
                changes.push({ field: f.field, new_value: val });
            }
        });

        if (!changes.length) {
            cancelEdit();
            showAlert('success', 'No changes detected.');
            return;
        }

        var saveBtn = document.querySelector('.go-save-btn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="go-spinner"></span> Saving…';

        /* Save changes sequentially */
        var promise = Promise.resolve();
        var savedCount = 0;
        changes.forEach(function (change) {
            promise = promise.then(function () {
                var body = new URLSearchParams({
                    student_id: st.student_id,
                    subject_id: subjectId,
                    field:      change.field,
                    new_value:  change.new_value,
                    reason:     reason,
                });
                return fetch(page.dataset.updateUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': page.dataset.csrf, Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                    .then(function (r) { return r.json().then(function (p) { if (!r.ok) throw p; return p; }); })
                    .then(function (p) {
                        /* Update in-memory student record */
                        if (change.field === 'midterm')       st.midterm        = p.new_value;
                        if (change.field === 'final')         st.final          = p.new_value;
                        if (change.field === 'final_average') st.final_average  = p.new_value;
                        if (change.field === 'remarks')       st.remarks        = p.new_value;
                        st.has_edits = true;
                        savedCount++;
                    });
            });
        });

        promise.then(function () {
            cancelEdit();
            /* Re-render just this row */
            var tbody = document.getElementById('goGradeTableBody');
            if (tbody) {
                var oldRow = tbody.querySelector('tr[data-idx="' + idx + '"]');
                if (oldRow) {
                    var tempDiv = document.createElement('tbody');
                    tempDiv.innerHTML = buildStudentRow(st, idx, subjectId);
                    tbody.replaceChild(tempDiv.firstChild, oldRow);
                }
            }
            showAlert('success', savedCount + ' grade field(s) updated and logged successfully.');
        }).catch(function (err) {
            if (saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = 'Save Changes'; }
            showAlert('error', (err && err.message) ? err.message : 'Unable to save grade. Please try again.', false);
        });
    }

    /* ── History modal ── */
    function openHistory(st, subjectId) {
        histTitle.textContent = 'Edit History';
        histMeta.textContent  = st.student_no + ' — ' + st.name;
        histBody.innerHTML    = '<div class="go-muted" style="text-align:center;padding:16px 0;"><span class="go-spinner" style="border-color:#146c43;border-top-color:transparent;"></span> Loading…</div>';
        openModal(histModal);

        var params = new URLSearchParams({ student_id: st.student_id, subject_id: subjectId });
        fetch(page.dataset.logsUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (p) {
                var logs = p.logs || [];
                if (!logs.length) {
                    histBody.innerHTML = '<div class="go-log-empty">No edits have been made to this student\'s grades for this subject.</div>';
                    return;
                }
                histBody.innerHTML = logs.map(function (log) {
                    var oldDisp = log.old_value != null ? log.old_value : '(empty)';
                    var newDisp = log.new_value != null ? log.new_value : '(cleared)';
                    return '<div class="go-log-entry">'
                        + '<div class="go-log-header">'
                            + '<span class="go-log-field">' + esc(fieldLabel(log.field)) + '</span>'
                            + '<span class="go-log-time">' + esc(fmtDate(log.edited_at)) + '</span>'
                        + '</div>'
                        + '<div class="go-log-change">'
                            + '<span class="go-log-old">' + esc(oldDisp) + '</span>'
                            + '<span class="go-log-arrow">&#8594;</span>'
                            + '<span class="go-log-new">' + esc(newDisp) + '</span>'
                        + '</div>'
                        + '<div class="go-log-reason">"' + esc(log.reason) + '"</div>'
                        + '<div class="go-log-by">Edited by: ' + esc(log.edited_by_name || 'Unknown') + '</div>'
                    + '</div>';
                }).join('');
            })
            .catch(function () {
                histBody.innerHTML = '<div class="go-muted">Unable to load history.</div>';
            });
    }

    /* ── Wire up controls ── */
    programSel.addEventListener('change', loadTerms);
    termSel.addEventListener('change', function () {
        loadBtn.disabled = !termSel.value;
    });
    loadBtn.addEventListener('click', loadSubjects);

    /* Initial term load */
    loadTerms();
});
</script>
@endsection
