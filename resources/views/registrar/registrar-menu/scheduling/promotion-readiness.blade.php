@extends('layouts.registrar')

@section('title', 'PLP - Promotion Readiness')
@section('page-title', 'PROMOTION READINESS')

@section('content')
<div class="pr-page"
    id="promotionReadinessPage"
    data-list-url="{{ route('registrar.registrar-menu.scheduling.promotion-readiness.data') }}"
    data-section-url="{{ route('registrar.registrar-menu.scheduling.promotion-readiness.section') }}"
    data-preview-url="{{ route('registrar.registrar-menu.scheduling.promotion-readiness.preview') }}"
    data-move-url="{{ route('registrar.registrar-menu.scheduling.promotion-readiness.move') }}"
    data-csrf="{{ csrf_token() }}">
    <style>
        /* ── Base ── */
        .pr-page { color:#143521; }
        .pr-panel { background:#fff; border:1px solid #dfe8e2; border-radius:8px; padding:16px; margin-bottom:16px; }
        .pr-title { margin:0 0 10px; font-size:1rem; font-weight:800; color:#123822; }
        .pr-guide { border:1px solid #d7eadf; background:#f3fbf6; border-radius:8px; padding:10px 12px; color:#315a3f; font-size:.84rem; line-height:1.45; margin-bottom:14px; }
        .pr-filter { display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; }
        .pr-field label { display:block; color:#46564a; font-size:.76rem; font-weight:800; text-transform:uppercase; margin-bottom:5px; }
        .pr-select { min-width:280px; min-height:38px; border:1px solid #cfd9d2; border-radius:7px; padding:8px 10px; background:#fff; font-size:.88rem; }
        .pr-select:focus { outline:none; border-color:#146c43; box-shadow:0 0 0 3px rgba(20,108,67,.1); }

        /* ── Buttons ── */
        .pr-btn { border:0; border-radius:7px; min-height:38px; padding:8px 14px; background:#146c43; color:#fff; font-weight:700; cursor:pointer; font-size:.84rem; display:inline-flex; align-items:center; gap:6px; transition:background .15s, opacity .15s; }
        .pr-btn:hover:not(:disabled) { background:#115a38; }
        .pr-btn.secondary { background:#eef6f1; color:#146c43; border:1px solid #b9d8c5; }
        .pr-btn.secondary:hover:not(:disabled) { background:#ddf0e6; }
        .pr-btn.warn { background:#7a5000; }
        .pr-btn.warn:hover:not(:disabled) { background:#5e3d00; }
        .pr-btn.danger { background:#9f1d1d; }
        .pr-btn.danger:hover:not(:disabled) { background:#7e1616; }
        .pr-btn:disabled { opacity:.5; cursor:not-allowed; }

        /* ── Spinner ── */
        .pr-spinner { width:13px; height:13px; border:2px solid currentColor; border-top-color:transparent; border-radius:50%; animation:pr-spin .7s linear infinite; flex-shrink:0; }
        @keyframes pr-spin { to { transform:rotate(360deg); } }

        /* ── Alert ── */
        .pr-alert { display:none; border-radius:8px; padding:10px 14px; margin-bottom:12px; font-size:.85rem; font-weight:700; position:relative; padding-right:40px; }
        .pr-alert.show { display:flex; align-items:flex-start; gap:8px; }
        .pr-alert.success { background:#e8f6ee; color:#17633a; border:1px solid #bfdfcc; }
        .pr-alert.error   { background:#fdecec; color:#9f1d1d;  border:1px solid #efb8b8; }
        .pr-alert-icon { flex-shrink:0; margin-top:1px; }
        .pr-alert-body { flex:1; }
        .pr-alert-close { position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:1.1rem; line-height:1; opacity:.5; padding:2px 4px; }
        .pr-alert-close:hover { opacity:1; }

        /* ── Summary banner ── */
        .pr-summary { display:flex; gap:8px; flex-wrap:wrap; padding:0 0 12px; }
        .pr-summary-card { display:flex; align-items:center; gap:7px; background:#f5faf7; border:1px solid #e3ece6; border-radius:8px; padding:7px 12px; font-size:.82rem; }
        .pr-summary-card.ready  { background:#eef6f1; border-color:#b9d8c5; color:#146c43; }
        .pr-summary-card.warn   { background:#fff8e8; border-color:#e8d18a; color:#7a5000; }
        .pr-summary-card.block  { background:#fdf3ec; border-color:#f0c9a0; color:#7a3800; }
        .pr-summary-card.info   { background:#f3f7fd; border-color:#c5d8ef; color:#1a4a7a; }
        .pr-summary-val { font-size:1.1rem; font-weight:800; line-height:1; }
        .pr-summary-lbl { font-size:.72rem; font-weight:700; opacity:.85; }

        /* ── Table ── */
        .pr-table-wrap { overflow:auto; border:1px solid #e3ece6; border-radius:8px; }
        .pr-table { width:100%; border-collapse:collapse; min-width:840px; }
        .pr-table th, .pr-table td { border-bottom:1px solid #edf3ef; padding:10px 12px; text-align:left; vertical-align:middle; font-size:.86rem; }
        .pr-table th { background:#f5faf7; color:#46564a; font-size:.74rem; font-weight:800; text-transform:uppercase; white-space:nowrap; }
        .pr-table tbody tr { transition:background .1s; }
        .pr-table tbody tr:last-child td { border-bottom:none; }
        .pr-table tbody tr:hover td { background:#f3faf6 !important; }

        /* Row tints by readiness */
        .pr-row-ready  td { background:#fafffe; }
        .pr-row-warn   td { background:#fffdf5; }
        .pr-row-fail   td { background:#fffafa; }
        .pr-row-block  td { background:#fffdf0; }

        /* Readiness progress bar */
        .pr-readiness-counts { display:flex; flex-wrap:wrap; gap:4px; margin-bottom:6px; }
        .pr-readiness-bar-wrap { height:5px; background:#e3ece6; border-radius:3px; overflow:hidden; width:100%; max-width:180px; }
        .pr-readiness-bar-fill { height:100%; border-radius:3px; transition:width .4s ease; }
        .pr-readiness-bar-fill.all-pass { background:#146c43; }
        .pr-readiness-bar-fill.mixed  { background:#d48b00; }
        .pr-readiness-bar-fill.no-pass { background:#c74b4b; }

        /* Action cell */
        .pr-action-cell { display:flex; flex-direction:column; gap:6px; align-items:flex-start; }
        .pr-action-row { display:flex; gap:6px; flex-wrap:wrap; }

        /* Shimmer skeleton */
        @keyframes pr-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
        .pr-skeleton td { background:linear-gradient(90deg,#f5faf7 25%,#eaeef0 50%,#f5faf7 75%); background-size:200% 100%; animation:pr-shimmer 1.3s infinite; border-color:transparent !important; }
        .pr-skeleton td span { display:inline-block; height:10px; border-radius:4px; background:rgba(0,0,0,.06); }

        /* Muted + pill */
        .pr-muted { color:#66756b; font-size:.8rem; }
        .pr-pill { display:inline-flex; border-radius:999px; padding:3px 9px; font-size:.74rem; font-weight:700; background:#eef6f1; color:#146c43; white-space:nowrap; }
        .pr-pill.fail    { background:#fdecec; color:#9f1d1d; }
        .pr-pill.pending { background:#fff5d8; color:#8a5b00; }
        .pr-pill.sm { padding:2px 7px; font-size:.68rem; }

        /* ── View-students modal ── */
        .pr-modal { position:fixed; inset:0; display:none; background:rgba(0,0,0,.5); z-index:1000; padding:20px; overflow-y:auto; opacity:0; transition:opacity .18s ease; }
        .pr-modal.show { display:block; }
        .pr-modal.pr-visible { opacity:1; }
        .pr-modal-panel { width:min(1200px,97vw); margin:0 auto; background:#fff; border-radius:10px; transform:translateY(12px); transition:transform .2s ease; }
        .pr-modal.pr-visible .pr-modal-panel { transform:translateY(0); }
        .pr-modal-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; padding:14px 16px 0; position:sticky; top:0; background:#fff; z-index:2; border-radius:10px 10px 0 0; border-bottom:1px solid #edf3ef; padding-bottom:10px; }
        .pr-modal-head-left { flex:1; min-width:0; }
        .pr-modal-head h3 { margin:0; color:#123822; font-size:1rem; font-weight:800; }
        .pr-modal-meta { font-size:.78rem; color:#66756b; margin-top:3px; }
        .pr-modal-head-actions { display:flex; gap:6px; align-items:center; flex-shrink:0; }
        .pr-modal-body { padding:0 16px 20px; }

        /* Subject tabs */
        .pr-tabs-wrap { padding:10px 16px 0; background:#fff; position:sticky; top:63px; z-index:2; border-bottom:1px solid #e3ece6; }
        .pr-tabs { display:flex; gap:5px; flex-wrap:nowrap; overflow-x:auto; padding-bottom:1px; scrollbar-width:thin; }
        .pr-tabs::-webkit-scrollbar { height:4px; }
        .pr-tabs::-webkit-scrollbar-thumb { background:#c9d8ce; border-radius:2px; }
        .pr-tab { border:1px solid #cfd9d2; border-radius:7px 7px 0 0; background:#f7faf8; color:#315a3f; cursor:pointer; padding:7px 11px; font-size:.78rem; text-align:left; min-width:110px; max-width:180px; border-bottom:2px solid transparent; transition:background .12s; flex-shrink:0; }
        .pr-tab:hover { background:#edf5f0; }
        .pr-tab.active { background:#fff; border-color:#146c43; border-bottom-color:#fff; color:#123822; position:relative; top:1px; }
        .pr-tab-code { font-weight:800; font-size:.79rem; white-space:nowrap; }
        .pr-tab-subj { font-size:.68rem; color:inherit; opacity:.82; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px; margin-top:1px; }
        .pr-tab-sched { font-size:.65rem; opacity:.65; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px; }
        .pr-tab-teacher { font-size:.65rem; opacity:.65; font-style:italic; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px; }

        /* Per-subject header */
        .pr-subject-header { margin:14px 0 10px; padding:10px 12px; background:#f5faf7; border-radius:8px; border-left:3px solid #146c43; }
        .pr-subject-title { font-weight:800; font-size:.92rem; color:#123822; }
        .pr-subject-detail { font-size:.78rem; color:#46564a; margin-top:3px; }
        .pr-grade-eq { font-weight:800; font-size:.88rem; }
        .pr-modal-table { overflow-x:auto; border:1px solid #e3ece6; border-radius:8px; }

        /* ── Preview modal ── */
        .pr-preview-modal { position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,.52); z-index:1100; padding:20px; overflow-y:auto; opacity:0; transition:opacity .18s ease; }
        .pr-preview-modal.show { display:flex; }
        .pr-preview-modal.pr-visible { opacity:1; }
        .pr-preview-panel { width:min(900px,97vw); background:#fff; border-radius:10px; overflow:hidden; transform:scale(.97); transition:transform .2s ease; }
        .pr-preview-modal.pr-visible .pr-preview-panel { transform:scale(1); }
        .pr-preview-head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:14px 16px 12px; border-bottom:1px solid #e3ece6; }
        .pr-preview-head-left { flex:1; min-width:0; }
        .pr-preview-head h3 { margin:0; color:#123822; font-size:1rem; font-weight:800; }
        .pr-preview-meta { font-size:.78rem; color:#66756b; margin-top:3px; }
        .pr-preview-body { padding:16px; max-height:68vh; overflow-y:auto; }
        .pr-preview-section { margin-bottom:16px; }
        .pr-preview-section-title { font-size:.72rem; font-weight:800; text-transform:uppercase; color:#46564a; letter-spacing:.04em; margin-bottom:8px; padding-bottom:4px; border-bottom:1px solid #edf3ef; }
        .pr-dest-box { background:#f3fbf6; border:1px solid #d7eadf; border-radius:8px; padding:10px 14px; display:flex; gap:20px; flex-wrap:wrap; }
        .pr-dest-item label { display:block; font-size:.7rem; font-weight:800; text-transform:uppercase; color:#46564a; }
        .pr-dest-item span { font-size:.9rem; font-weight:800; color:#123822; }
        .pr-students-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        @media(max-width:580px){ .pr-students-grid { grid-template-columns:1fr; } }
        .pr-students-col { border-radius:8px; padding:10px 12px; }
        .pr-students-col.promoted { background:#eef6f1; border:1px solid #b9d8c5; }
        .pr-students-col.review   { background:#fff5d8; border:1px solid #e8d18a; }
        .pr-students-col-title { font-size:.72rem; font-weight:800; text-transform:uppercase; margin-bottom:8px; }
        .pr-students-col.promoted .pr-students-col-title { color:#146c43; }
        .pr-students-col.review   .pr-students-col-title { color:#8a5b00; }
        .pr-student-item { font-size:.82rem; padding:5px 0; border-bottom:1px solid rgba(0,0,0,.06); color:#1a2e20; }
        .pr-student-item:last-child { border-bottom:none; }
        .pr-student-no { font-size:.71rem; color:#66756b; }
        .pr-preview-footer { display:flex; justify-content:space-between; align-items:center; gap:8px; padding:12px 16px; border-top:1px solid #e3ece6; background:#f9fdfb; }
        .pr-preview-footer-note { font-size:.75rem; color:#66756b; }
        .pr-preview-footer-btns { display:flex; gap:8px; }
        .pr-curric-table { width:100%; border-collapse:collapse; font-size:.84rem; }
        .pr-curric-table th { background:#f5faf7; color:#46564a; font-size:.72rem; font-weight:800; text-transform:uppercase; padding:8px 10px; text-align:left; border-bottom:1px solid #e3ece6; }
        .pr-curric-table td { padding:8px 10px; border-bottom:1px solid #edf3ef; vertical-align:middle; }
        .pr-curric-table tr:last-child td { border-bottom:none; }
        .pr-no-curric { color:#66756b; font-size:.82rem; padding:8px 0; }

        /* Confirm button warning state */
        .pr-btn.confirm-move { background:#7a5000; font-size:.88rem; }
        .pr-btn.confirm-move:hover:not(:disabled) { background:#5e3d00; }
    </style>

    <section class="pr-panel">
        <h2 class="pr-title">Promotion Readiness by Section</h2>
        <div class="pr-guide">
            Use this after final grades are submitted. Filter an active program, check each active section's passed, failed, and pending counts, review student grades, then generate movement. Eligible students move to the next semester/year; failed or incomplete students are flagged for adviser review.
        </div>
        <div class="pr-filter">
            <div class="pr-field">
                <label for="prProgram">Active Program</label>
                <select class="pr-select" id="prProgram">
                    @foreach($programOptions as $program)
                        <option value="{{ $program['id'] }}">{{ $program['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="pr-btn" id="prLoadBtn">Load Sections</button>
        </div>
    </section>

    <section class="pr-panel">
        <div class="pr-alert" id="prAlert" role="alert">
            <span class="pr-alert-icon" id="prAlertIcon"></span>
            <span class="pr-alert-body" id="prAlertBody"></span>
            <button type="button" class="pr-alert-close" id="prAlertClose" aria-label="Dismiss">&times;</button>
        </div>
        <div id="prSummary" class="pr-summary" style="display:none;"></div>
        <div class="pr-table-wrap">
            <table class="pr-table" data-no-auto-pager="1" id="prSectionsTable">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Current Term</th>
                        <th>Readiness</th>
                        <th>Next Destination</th>
                        <th style="min-width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="prRows">
                    <tr><td colspan="5" class="pr-muted" style="padding:18px 12px;">Select a program and click Load Sections.</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- Preview modal --}}
    <div class="pr-preview-modal req-modal-overlay" id="prPreviewModal" aria-hidden="true" role="dialog" aria-labelledby="prPreviewTitle" aria-modal="true">
        <div class="pr-preview-panel">
            <div class="pr-preview-head">
                <div class="pr-preview-head-left">
                    <h3 id="prPreviewTitle">Movement Preview</h3>
                    <div class="pr-preview-meta" id="prPreviewMeta"></div>
                </div>
                <button type="button" class="pr-btn secondary" id="prClosePreview" aria-label="Close preview">&#x2715;</button>
            </div>
            <div class="pr-preview-body" id="prPreviewBody">
                <div class="pr-muted" style="padding:16px 0;text-align:center;">
                    <span class="pr-spinner" style="width:18px;height:18px;border-width:3px;"></span>
                    &nbsp;Loading preview&hellip;
                </div>
            </div>
            <div class="pr-preview-footer">
                <span class="pr-preview-footer-note" id="prPreviewNote">Review the details above before confirming.</span>
                <div class="pr-preview-footer-btns">
                    <button type="button" class="pr-btn secondary" id="prPreviewCancel">Cancel</button>
                    <button type="button" class="pr-btn confirm-move" id="prPreviewConfirm" disabled>
                        Confirm &amp; Generate Movement
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- View-students modal --}}
    <div class="pr-modal req-modal-overlay" id="prModal" aria-hidden="true" role="dialog" aria-labelledby="prModalTitle" aria-modal="true">
        <div class="pr-modal-panel">
            <div class="pr-modal-head">
                <div class="pr-modal-head-left">
                    <h3 id="prModalTitle">Section Students</h3>
                    <div class="pr-modal-meta" id="prModalMeta"></div>
                </div>
                <div class="pr-modal-head-actions">
                    <button type="button" class="pr-btn warn" id="prModalMoveBtn" style="display:none;">Generate Movement</button>
                    <button type="button" class="pr-btn secondary" id="prCloseModal" aria-label="Close">&#x2715;</button>
                </div>
            </div>
            <div class="pr-tabs-wrap" id="prTabsWrap"></div>
            <div class="pr-modal-body" id="prModalBody"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var page         = document.getElementById('promotionReadinessPage');
    if (!page) return;
    var program      = document.getElementById('prProgram');
    var loadBtn      = document.getElementById('prLoadBtn');
    var rows         = document.getElementById('prRows');
    var alertBox     = document.getElementById('prAlert');
    var alertBody    = document.getElementById('prAlertBody');
    var alertIcon    = document.getElementById('prAlertIcon');
    var summaryBar   = document.getElementById('prSummary');
    var modal        = document.getElementById('prModal');
    var modalTitle   = document.getElementById('prModalTitle');
    var modalMeta    = document.getElementById('prModalMeta');
    var modalMoveBtn = document.getElementById('prModalMoveBtn');
    var tabsWrap     = document.getElementById('prTabsWrap');
    var modalBody    = document.getElementById('prModalBody');
    var previewModal = document.getElementById('prPreviewModal');
    var previewTitle = document.getElementById('prPreviewTitle');
    var previewMeta  = document.getElementById('prPreviewMeta');
    var previewBody  = document.getElementById('prPreviewBody');
    var previewNote  = document.getElementById('prPreviewNote');
    var previewConfirm = document.getElementById('prPreviewConfirm');
    var previewRow   = null;
    var alertTimer   = null;

    /* ── Utilities ── */
    function esc(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
        });
    }
    function fmt(val) {
        return val == null ? '<span class="pr-muted">—</span>' : esc(val);
    }
    function fmtTime(t) {
        if (!t) return '';
        var parts = String(t).split(':');
        var h = parseInt(parts[0], 10), m = parts[1] || '00';
        return (h % 12 || 12) + ':' + m + ' ' + (h >= 12 ? 'PM' : 'AM');
    }
    function scheduleLabel(g) {
        var parts = [];
        if (g.days) parts.push(g.days);
        if (g.time_start || g.time_end) {
            var t = fmtTime(g.time_start);
            if (g.time_end) t += '–' + fmtTime(g.time_end);
            if (t) parts.push(t);
        }
        if (g.room) parts.push('Rm ' + g.room);
        return parts.join(' · ');
    }
    function studentStatusClass(s) {
        return s === 'Failed' ? 'fail' : (s === 'Pending' ? 'pending' : '');
    }
    function subjectGradeFor(student, code) {
        var grades = student.grades || [];
        for (var i = 0; i < grades.length; i++) {
            if (String(grades[i].code) === String(code)) return grades[i];
        }
        return null;
    }
    function paramsFor(row) {
        return new URLSearchParams({
            academic_term_id: row.academic_term_id,
            course_id: row.course_id,
            year_block_id: row.year_block_id,
            section: row.section
        });
    }

    /* ── Alert ── */
    function showAlert(type, message) {
        clearTimeout(alertTimer);
        alertIcon.textContent = type === 'success' ? '✓' : '✕';
        alertBody.textContent = message;
        alertBox.className = 'pr-alert show ' + type;
        if (type === 'success') {
            alertTimer = setTimeout(function () { alertBox.className = 'pr-alert'; }, 6000);
        }
    }
    document.getElementById('prAlertClose').addEventListener('click', function () {
        clearTimeout(alertTimer);
        alertBox.className = 'pr-alert';
    });

    /* ── Modal helpers (scroll lock + animation) ── */
    function openModal(el) {
        el.classList.add('show');
        el.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { el.classList.add('pr-visible'); });
        });
    }
    function closeModal(el) {
        el.classList.remove('pr-visible');
        setTimeout(function () {
            el.classList.remove('show');
            el.setAttribute('aria-hidden', 'true');
            if (!modal.classList.contains('show') && !previewModal.classList.contains('show')) {
                document.body.style.overflow = '';
            }
        }, 180);
    }

    /* ESC to close topmost modal */
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' && e.keyCode !== 27) return;
        if (previewModal.classList.contains('show')) { closeModal(previewModal); return; }
        if (modal.classList.contains('show')) { closeModal(modal); }
    });

    /* Click backdrop to close */
    previewModal.addEventListener('click', function (e) {
        if (e.target === previewModal) closeModal(previewModal);
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal(modal);
    });

    /* ── Summary banner ── */
    function renderSummary(sections) {
        var total   = sections.length;
        var ready   = sections.filter(function (s) { return s.next_term_id && s.pending_count === 0 && s.failed_count === 0; }).length;
        var hasWarn = sections.filter(function (s) { return s.next_term_id && (s.pending_count > 0 || s.failed_count > 0); }).length;
        var blocked = sections.filter(function (s) { return !s.next_term_id; }).length;
        var totalStudents = sections.reduce(function (a, s) { return a + (s.student_count || 0); }, 0);
        summaryBar.style.display = '';
        summaryBar.innerHTML =
            '<div class="pr-summary-card info"><span class="pr-summary-val">' + total + '</span><span class="pr-summary-lbl">Sections</span></div>'
          + '<div class="pr-summary-card info"><span class="pr-summary-val">' + totalStudents + '</span><span class="pr-summary-lbl">Students</span></div>'
          + (ready   ? '<div class="pr-summary-card ready"><span class="pr-summary-val">' + ready + '</span><span class="pr-summary-lbl">Ready to Move</span></div>' : '')
          + (hasWarn ? '<div class="pr-summary-card warn"><span class="pr-summary-val">' + hasWarn + '</span><span class="pr-summary-lbl">Has Pending/Failed</span></div>' : '')
          + (blocked ? '<div class="pr-summary-card block"><span class="pr-summary-val">' + blocked + '</span><span class="pr-summary-lbl">Blocked (No Next Term)</span></div>' : '');
    }

    /* ── Subject index ── */
    function buildSubjectIndex(students) {
        var map = {};
        students.forEach(function (student) {
            (student.grades || []).forEach(function (g) {
                if (!map[g.code]) map[g.code] = { code:g.code, name:g.name, teacher:g.teacher, days:g.days, time_start:g.time_start, time_end:g.time_end, room:g.room };
            });
        });
        return Object.values(map).sort(function (a, b) { return String(a.code).localeCompare(String(b.code)); });
    }

    /* ── Tabs ── */
    function renderTabs(subjects, active) {
        var allBtn = '<button type="button" class="pr-tab' + (active === 'all' ? ' active' : '') + '" data-subject-tab="all">'
            + '<div class="pr-tab-code">All Subjects</div>'
            + '<div class="pr-tab-subj">Overview &amp; summary</div>'
            + '</button>';
        var subBtns = subjects.map(function (s) {
            var sched = scheduleLabel(s);
            return '<button type="button" class="pr-tab' + (active === s.code ? ' active' : '') + '" data-subject-tab="' + esc(s.code) + '">'
                + '<div class="pr-tab-code">' + esc(s.code) + '</div>'
                + '<div class="pr-tab-subj">' + esc(s.name) + '</div>'
                + (sched ? '<div class="pr-tab-sched">' + esc(sched) + '</div>' : '')
                + (s.teacher ? '<div class="pr-tab-teacher">' + esc(s.teacher) + '</div>' : '')
                + '</button>';
        }).join('');
        tabsWrap.innerHTML = '<div class="pr-tabs">' + allBtn + subBtns + '</div>';
    }

    /* ── Student rows ── */
    function renderStudentRows(students, subjectCode, subjects) {
        if (subjectCode === 'all') {
            var header = '<thead><tr><th>Student</th><th>Overall Status</th><th>Remarks</th><th>Grade Summary</th></tr></thead>';
            var body = students.map(function (st) {
                var pills = (st.grades || []).map(function (g) {
                    var display = g.eq_grade != null ? g.eq_grade : (g.final_average != null ? g.final_average : null);
                    var cls = g.final_average !== null ? (g.final_average >= 75 ? '' : 'fail') : 'pending';
                    return '<span class="pr-pill sm ' + cls + '" title="' + esc(g.name) + '">' + esc(g.code) + ': ' + (display != null ? esc(display) : '—') + '</span>';
                }).join('');
                return '<tr>'
                    + '<td><strong style="font-size:.88rem;">' + esc(st.student_no) + '</strong><div class="pr-muted">' + esc(st.name) + '</div></td>'
                    + '<td><span class="pr-pill ' + studentStatusClass(st.readiness_status) + '">' + esc(st.readiness_status) + '</span></td>'
                    + '<td class="pr-muted" style="font-size:.78rem;max-width:220px;">' + esc(st.remarks) + '</td>'
                    + '<td style="max-width:280px;">' + (pills || '<span class="pr-muted">No grades.</span>') + '</td>'
                    + '</tr>';
            }).join('');
            return header + '<tbody>' + body + '</tbody>';
        }

        var subjectInfo = subjects.find(function (s) { return s.code === subjectCode; }) || {};
        var header = '<thead><tr><th>Student</th><th>Status</th><th>Teacher</th><th style="text-align:center;">Midterm</th><th style="text-align:center;">Finals</th><th style="text-align:center;">EQ Grade</th></tr></thead>';
        var body = students.map(function (st) {
            var g       = subjectGradeFor(st, subjectCode);
            var teacher = g && g.teacher ? g.teacher : (subjectInfo.teacher || '');
            var mid     = g ? g.midterm : null;
            var fin     = g ? g.final   : null;
            var raw     = g ? g.final_average : null;
            var eq      = g && g.eq_grade != null ? g.eq_grade : raw;
            var eqCls   = raw !== null ? (raw >= 75 ? '' : 'fail') : 'pending';
            return '<tr>'
                + '<td><strong style="font-size:.88rem;">' + esc(st.student_no) + '</strong><div class="pr-muted">' + esc(st.name) + '</div></td>'
                + '<td><span class="pr-pill ' + studentStatusClass(st.readiness_status) + '">' + esc(st.readiness_status) + '</span></td>'
                + '<td style="font-size:.83rem;">' + (teacher ? esc(teacher) : '<span class="pr-muted">—</span>') + '</td>'
                + '<td style="text-align:center;">' + fmt(mid) + '</td>'
                + '<td style="text-align:center;">' + fmt(fin) + '</td>'
                + '<td style="text-align:center;"><span class="pr-grade-eq pr-pill ' + eqCls + '">' + (eq != null ? esc(eq) : '—') + '</span></td>'
                + '</tr>';
        }).join('');
        return header + '<tbody>' + body + '</tbody>';
    }

    /* ── Subject header in modal body ── */
    function renderSubjectHeader(subjectCode, subjects) {
        if (subjectCode === 'all') { modalBody.innerHTML = ''; return; }
        var s = subjects.find(function (i) { return i.code === subjectCode; });
        if (!s) { modalBody.innerHTML = ''; return; }
        var sched  = scheduleLabel(s);
        var detail = [s.teacher ? 'Teacher: ' + s.teacher : '', sched || ''].filter(Boolean).join(' &nbsp;&middot;&nbsp; ');
        modalBody.innerHTML = '<div class="pr-subject-header">'
            + '<div class="pr-subject-title">' + esc(s.code) + ' &mdash; ' + esc(s.name) + '</div>'
            + (detail ? '<div class="pr-subject-detail">' + detail + '</div>' : '')
            + '</div>';
    }

    /* ── Full modal render ── */
    function renderModal(students, subjectCode) {
        var subjects = buildSubjectIndex(students);
        var active = subjectCode || 'all';
        renderTabs(subjects, active);
        renderSubjectHeader(active, subjects);
        var table = document.createElement('table');
        table.className = 'pr-table';
        table.setAttribute('data-no-auto-pager', '1');
        table.innerHTML = renderStudentRows(students, active, subjects);
        var wrap = document.createElement('div');
        wrap.className = 'pr-modal-table';
        wrap.appendChild(table);
        modalBody.appendChild(wrap);
        tabsWrap._students = students;
        tabsWrap._subjects = subjects;
    }

    /* ── Switch tab ── */
    function switchTab(subjectCode) {
        var students = tabsWrap._students;
        var subjects = tabsWrap._subjects;
        if (!students) return;
        renderTabs(subjects, subjectCode);
        renderSubjectHeader(subjectCode, subjects);
        var table = document.createElement('table');
        table.className = 'pr-table';
        table.setAttribute('data-no-auto-pager', '1');
        table.innerHTML = renderStudentRows(students, subjectCode, subjects);
        var wrap = document.createElement('div');
        wrap.className = 'pr-modal-table';
        wrap.appendChild(table);
        modalBody.appendChild(wrap);
        tabsWrap._students = students;
        tabsWrap._subjects = subjects;
    }

    /* ── Sections list ── */
    function loadSections() {
        /* Loading state */
        loadBtn.disabled = true;
        loadBtn.innerHTML = '<span class="pr-spinner"></span> Loading&hellip;';
        summaryBar.style.display = 'none';
        rows.innerHTML =
            '<tr class="pr-skeleton"><td><span style="width:70px;"></span></td>'
            + '<td><span style="width:90px;"></span></td><td><span style="width:110px;"></span></td>'
            + '<td><span style="width:80px;"></span></td><td><span style="width:130px;"></span></td></tr>'
            + '<tr class="pr-skeleton"><td><span style="width:60px;"></span></td>'
            + '<td><span style="width:85px;"></span></td><td><span style="width:100px;"></span></td>'
            + '<td><span style="width:75px;"></span></td><td><span style="width:120px;"></span></td></tr>'
            + '<tr class="pr-skeleton"><td><span style="width:80px;"></span></td>'
            + '<td><span style="width:95px;"></span></td><td><span style="width:105px;"></span></td>'
            + '<td><span style="width:70px;"></span></td><td><span style="width:140px;"></span></td></tr>';

        fetch(page.dataset.listUrl + '?course_id=' + encodeURIComponent(program.value), { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json().then(function (p) { if (!r.ok) throw p; return p; }); })
            .then(function (payload) {
                var sections = payload.sections || [];
                if (!sections.length) {
                    rows.innerHTML = '<tr><td colspan="5" class="pr-muted" style="padding:18px 12px;">No active sections found for this program.</td></tr>';
                    return;
                }
                renderSummary(sections);
                rows.innerHTML = sections.map(function (row, index) {
                    var hasNextTerm = !!row.next_term_id;
                    var total       = row.student_count || 1;
                    var passedPct   = Math.round((row.passed_count / total) * 100);
                    var barClass    = row.passed_count === total ? 'all-pass' : (row.passed_count > 0 ? 'mixed' : 'no-pass');
                    var rowClass    = !hasNextTerm ? 'pr-row-block'
                                   : (row.failed_count > 0 ? 'pr-row-fail'
                                   : (row.pending_count > 0 ? 'pr-row-warn' : 'pr-row-ready'));

                    var moveBtnHtml;
                    if (!hasNextTerm) {
                        moveBtnHtml = '<button type="button" class="pr-btn warn pr-move" data-index="' + index + '" disabled'
                            + ' title="Create ' + esc(row.next_school_year) + ' — ' + esc(row.next_semester) + ' via Term Lifecycle first.">'
                            + 'Generate Movement</button>'
                            + '<span class="pr-muted" style="font-size:.71rem;color:#8a5b00;">&#9888; No next term yet</span>';
                    } else {
                        moveBtnHtml = '<button type="button" class="pr-btn warn pr-move" data-index="' + index + '">'
                            + 'Generate Movement'
                            + (row.pending_count > 0 ? ' <span style="background:rgba(255,255,255,.22);border-radius:999px;padding:1px 6px;font-size:.68rem;">' + esc(row.pending_count) + ' pending</span>' : '')
                            + '</button>';
                    }

                    return '<tr class="' + rowClass + '" data-index="' + index + '">'
                        + '<td>'
                            + '<strong>' + esc(row.section) + '</strong>'
                            + '<div class="pr-muted">' + esc(row.year_level) + '</div>'
                        + '</td>'
                        + '<td>'
                            + esc(row.school_year)
                            + '<div class="pr-muted">' + esc(row.semester) + '</div>'
                            + '<div class="pr-muted" style="font-size:.72rem;">' + esc(row.term_status) + '</div>'
                        + '</td>'
                        + '<td>'
                            + '<div class="pr-readiness-counts">'
                                + '<span class="pr-pill">' + esc(row.passed_count) + ' passed</span>'
                                + '<span class="pr-pill fail">' + esc(row.failed_count) + ' failed</span>'
                                + '<span class="pr-pill pending">' + esc(row.pending_count) + ' pending</span>'
                            + '</div>'
                            + '<div class="pr-readiness-bar-wrap" title="' + passedPct + '% passed">'
                                + '<div class="pr-readiness-bar-fill ' + barClass + '" style="width:' + passedPct + '%;"></div>'
                            + '</div>'
                            + '<div class="pr-muted" style="margin-top:3px;">' + esc(row.student_count) + ' students total</div>'
                        + '</td>'
                        + '<td>'
                            + '<strong style="font-size:.85rem;">' + esc(row.next_school_year) + ' / ' + esc(row.next_semester) + '</strong>'
                            + '<div class="pr-muted">' + esc(row.next_year_level) + '</div>'
                            + (hasNextTerm
                                ? '<span class="pr-pill sm" style="margin-top:3px;">' + esc(row.next_term_status || 'Draft') + '</span>'
                                : '<span class="pr-pill sm pending" style="margin-top:3px;">Not created</span>')
                        + '</td>'
                        + '<td>'
                            + '<div class="pr-action-cell">'
                                + '<div class="pr-action-row">'
                                    + '<button type="button" class="pr-btn secondary pr-view" data-index="' + index + '">View Students</button>'
                                    + moveBtnHtml
                                + '</div>'
                            + '</div>'
                        + '</td>'
                        + '</tr>';
                }).join('');
                page._sections = sections;
            })
            .catch(function (error) {
                rows.innerHTML = '<tr><td colspan="5" class="pr-muted" style="padding:18px 12px;">Unable to load sections. Please try again.</td></tr>';
                showAlert('error', error.message || 'Unable to load sections.');
            })
            .finally(function () {
                loadBtn.disabled = false;
                loadBtn.textContent = 'Load Sections';
            });
    }

    /* ── View students ── */
    function viewSection(row) {
        modal._row = row;
        modalTitle.textContent = row.section;
        modalMeta.textContent  = row.school_year + ' · ' + row.semester + ' · ' + row.year_level;
        tabsWrap.innerHTML = '';
        modalBody.innerHTML = '<div style="padding:24px 0;text-align:center;"><span class="pr-spinner" style="width:18px;height:18px;border-width:3px;border-color:#146c43;border-top-color:transparent;"></span> <span class="pr-muted">&nbsp;Loading students&hellip;</span></div>';

        /* Show Generate Movement button in modal if allowed */
        if (row.next_term_id) {
            modalMoveBtn.style.display = '';
            modalMoveBtn.disabled = false;
            modalMoveBtn.dataset.index = String(page._sections ? page._sections.indexOf(row) : -1);
        } else {
            modalMoveBtn.style.display = 'none';
        }

        openModal(modal);

        fetch(page.dataset.sectionUrl + '?' + paramsFor(row).toString(), { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json().then(function (p) { if (!r.ok) throw p; return p; }); })
            .then(function (payload) {
                modalBody.innerHTML = '';
                var students = payload.students || [];
                if (!students.length) {
                    modalBody.innerHTML = '<div class="pr-muted" style="padding:20px 0;text-align:center;">No students found in this section.</div>';
                    return;
                }
                renderModal(students, 'all');
            })
            .catch(function (err) {
                modalBody.innerHTML = '<div class="pr-alert show error" style="margin-top:16px;">'
                    + '<span>' + esc(err.message || 'Unable to load students. Please try again.') + '</span></div>';
            });
    }

    /* ── Generate movement ── */
    function doGenerateMovement(row, confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="pr-spinner"></span> Generating&hellip;';
        fetch(page.dataset.moveUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': page.dataset.csrf, Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: paramsFor(row).toString()
        })
            .then(function (r) { return r.json().then(function (p) { if (!r.ok) throw p; return p; }); })
            .then(function (payload) {
                closeModal(previewModal);
                closeModal(modal);
                showAlert('success',
                    payload.message
                    + ' Promoted: ' + payload.moved_count + ' student(s).'
                    + (payload.review_count > 0 ? ' Flagged for review: ' + payload.review_count + '.' : '')
                    + ' → ' + payload.next_term
                );
                loadSections();
            })
            .catch(function (error) {
                showAlert('error', error.message || 'Unable to generate movement.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = 'Confirm &amp; Generate Movement';
            });
    }

    /* ── Preview ── */
    function showPreview(row) {
        previewRow = row;
        previewTitle.textContent = 'Movement Preview — ' + row.section;
        previewMeta.textContent  = row.school_year + ' · ' + row.semester + ' · ' + row.year_level;
        previewBody.innerHTML = '<div style="padding:24px 0;text-align:center;"><span class="pr-spinner" style="width:18px;height:18px;border-width:3px;border-color:#146c43;border-top-color:transparent;"></span> <span class="pr-muted">&nbsp;Loading preview&hellip;</span></div>';
        previewNote.textContent = 'Review the details below before confirming.';
        previewConfirm.disabled = true;
        previewConfirm.innerHTML = 'Confirm &amp; Generate Movement';
        openModal(previewModal);

        fetch(page.dataset.previewUrl + '?' + paramsFor(row).toString(), { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json().then(function (p) { if (!r.ok) throw p; return p; }); })
            .then(function (payload) {
                var next     = payload.next || {};
                var students = payload.students || [];
                var subjects = payload.curriculum_subjects || [];
                var promoted = students.filter(function (s) { return s.readiness_status === 'Passed'; });
                var review   = students.filter(function (s) { return s.readiness_status !== 'Passed'; });
                var totalUnits = subjects.reduce(function (sum, s) { return sum + (s.units || 0); }, 0);

                var destHtml = '<div class="pr-preview-section">'
                    + '<div class="pr-preview-section-title">Next Destination</div>'
                    + '<div class="pr-dest-box">'
                    + '<div class="pr-dest-item"><label>School Year</label><span>' + esc(next.school_year || '—') + '</span></div>'
                    + '<div class="pr-dest-item"><label>Semester</label><span>' + esc(next.semester || '—') + '</span></div>'
                    + '<div class="pr-dest-item"><label>Year Level</label><span>' + esc(next.year_level || '—') + '</span></div>'
                    + '</div></div>';

                var curricHtml = '<div class="pr-preview-section">'
                    + '<div class="pr-preview-section-title">Next Curriculum Subjects'
                    + (subjects.length ? ' <span style="font-weight:400;text-transform:none;color:#66756b;">(' + subjects.length + ' subjects &middot; ' + totalUnits.toFixed(0) + ' units)</span>' : '')
                    + '</div>';
                if (subjects.length) {
                    curricHtml += '<div style="overflow-x:auto;border:1px solid #e3ece6;border-radius:8px;">'
                        + '<table class="pr-curric-table" data-no-auto-pager="1"><thead><tr>'
                        + '<th>#</th><th>Code</th><th>Subject</th><th style="text-align:right;">Units</th>'
                        + '</tr></thead><tbody>'
                        + subjects.map(function (s, i) {
                            return '<tr><td class="pr-muted">' + (i + 1) + '</td>'
                                + '<td><strong>' + esc(s.code) + '</strong></td>'
                                + '<td>' + esc(s.name) + '</td>'
                                + '<td style="text-align:right;">' + esc(s.units) + '</td></tr>';
                        }).join('')
                        + '</tbody></table></div>';
                } else {
                    curricHtml += '<div class="pr-no-curric">No curriculum subjects found for this year level and semester. Movement will still be generated.</div>';
                }
                curricHtml += '</div>';

                var studentsHtml = '<div class="pr-preview-section">'
                    + '<div class="pr-preview-section-title">Student Breakdown (' + students.length + ' total)</div>'
                    + '<div class="pr-students-grid">'
                    + '<div class="pr-students-col promoted">'
                    + '<div class="pr-students-col-title">&#10003; Will Be Promoted (' + promoted.length + ')</div>'
                    + (promoted.length
                        ? promoted.map(function (s) {
                            return '<div class="pr-student-item"><div>' + esc(s.name) + '</div><div class="pr-student-no">' + esc(s.student_no) + '</div></div>';
                        }).join('')
                        : '<div class="pr-muted" style="font-size:.8rem;padding:4px 0;">None</div>')
                    + '</div>'
                    + '<div class="pr-students-col review">'
                    + '<div class="pr-students-col-title">&#9888; Needs Adviser Review (' + review.length + ')</div>'
                    + (review.length
                        ? review.map(function (s) {
                            return '<div class="pr-student-item"><div>' + esc(s.name) + '</div><div class="pr-student-no">' + esc(s.student_no) + ' &middot; ' + esc(s.readiness_status) + '</div></div>';
                        }).join('')
                        : '<div class="pr-muted" style="font-size:.8rem;padding:4px 0;">None</div>')
                    + '</div>'
                    + '</div></div>';

                previewBody.innerHTML = destHtml + curricHtml + studentsHtml;

                if (review.length > 0) {
                    previewNote.textContent = review.length + ' student(s) will be flagged for adviser review — not promoted.';
                } else {
                    previewNote.textContent = 'All ' + promoted.length + ' student(s) are eligible for promotion.';
                }
                previewConfirm.disabled = false;
            })
            .catch(function (err) {
                previewBody.innerHTML = '<div class="pr-alert show error" style="margin-top:4px;"><span>'
                    + esc(err.message || 'Unable to load preview.') + '</span></div>';
            });
    }

    /* ── Event listeners ── */
    loadBtn.addEventListener('click', loadSections);
    program.addEventListener('change', loadSections);

    document.getElementById('prCloseModal').addEventListener('click', function () { closeModal(modal); });
    document.getElementById('prClosePreview').addEventListener('click', function () { closeModal(previewModal); });
    document.getElementById('prPreviewCancel').addEventListener('click', function () { closeModal(previewModal); });

    previewConfirm.addEventListener('click', function () {
        if (previewRow) doGenerateMovement(previewRow, previewConfirm);
    });

    /* Generate Movement from inside the view-students modal */
    modalMoveBtn.addEventListener('click', function () {
        var row = modal._row;
        if (!row) return;
        closeModal(modal);
        setTimeout(function () { showPreview(row); }, 200);
    });

    tabsWrap.addEventListener('click', function (e) {
        var tab = e.target.closest('[data-subject-tab]');
        if (!tab) return;
        modalBody.innerHTML = '';
        switchTab(tab.getAttribute('data-subject-tab'));
    });

    rows.addEventListener('click', function (e) {
        var button = e.target.closest('button');
        if (!button || !page._sections) return;
        var row = page._sections[parseInt(button.dataset.index, 10)];
        if (!row) return;
        if (button.classList.contains('pr-view')) viewSection(row);
        if (button.classList.contains('pr-move') && !button.disabled) showPreview(row);
    });

    loadSections();
});
</script>
@endsection
