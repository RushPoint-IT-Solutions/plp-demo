@extends('layouts.registrar')

@section('title', 'PLP - Student Update')
@section('page-title', 'STUDENT UPDATE')
@section('body-class', 'page-student-update')



@section('content')
<div class="pf-page">
    <div class="su-page">
        <section class="su-card su-left">
            <div class="su-head">
                <h3 class="su-title">System Configuration</h3>
            </div>
            <div class="su-subtitle">Set scope, validate filters, then run your selected student update process.</div>

            <div class="su-section-title">Academic Scope</div>
            <div class="su-grid-4">
                <div class="su-field">
                    <label class="app-filter-label" for="suSY">School Year</label>
                    <select id="suSY" class="app-filter-select">
                        <option>2025-2026</option>
                        <option>2026-2027</option>
                    </select>
                </div>
                <div class="su-field">
                    <label class="app-filter-label" for="suTerm">Term</label>
                    <select id="suTerm" class="app-filter-select">
                        <option>First</option>
                        <option>Second</option>
                    </select>
                </div>
                <div class="su-field">
                    <label class="app-filter-label" for="suPeriod">Period</label>
                    <input id="suPeriod" type="text" class="app-filter-input" value="2025-2026">
                </div>
                <div class="su-field">
                    <label class="app-filter-label" for="suUserName">Operator</label>
                    <select id="suUserName" class="app-filter-select">
                        <option value="">User Name</option>
                        @foreach(($operatorOptions ?? []) as $operator)
                            <option value="{{ $operator }}">{{ $operator }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="su-section-title">Student Filters</div>
            <div class="su-grid-5">
                <div class="su-field">
                    <label class="app-filter-label" for="suCourse">Course</label>
                    <select id="suCourse" class="app-filter-select">
                        <option value="">-Select Course-</option>
                        @foreach(($courseOptions ?? []) as $course)
                            <option value="{{ $course }}">{{ $course }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="su-field">
                    <label class="app-filter-label" for="suYrLevel">Yr Level</label>
                    <select id="suYrLevel" class="app-filter-select">
                        <option value="">-yr level-</option>
                        <option>First</option>
                        <option>Second</option>
                        <option>Third</option>
                        <option>Fourth</option>
                    </select>
                </div>
                <div class="su-field">
                    <label class="app-filter-label" for="suSection">Section</label>
                    <select id="suSection" class="app-filter-select">
                        <option value="">-section-</option>
                        <option>A</option>
                        <option>B</option>
                        <option>C</option>
                    </select>
                </div>
                <div class="su-field">
                    <label class="app-filter-label" for="suStudentNo">Student No.</label>
                    <input id="suStudentNo" type="text" class="app-filter-input" placeholder="Optional">
                </div>
                <div class="su-field">
                    <label class="app-filter-label" for="suRunMode">Run Mode</label>
                    <select id="suRunMode" class="app-filter-select">
                        <option>Preview</option>
                        <option>Apply Changes</option>
                    </select>
                </div>
            </div>

            <div class="su-rule-box">
                <div class="su-rule-title">Execution Rules</div>
                <div class="su-rule-grid">
                    <label class="su-checkline"><input id="suCheckPaidOnly" type="checkbox" class="req-checkbox-input"> Include unpaid students only</label>
                    <label class="su-checkline"><input id="suActiveOnly" type="checkbox" class="req-checkbox-input"> Active students only</label>
                </div>
                <div class="su-note">Specify student number if you want to update a particular record.</div>
            </div>

            <label class="su-danger-note"><input id="suRiskAcknowledge" type="checkbox" class="req-checkbox-input"> I confirm the selected operation will update live student records.</label>

            <div class="su-save-wrap">
                <button type="button" class="pf-btn-new su-save-btn" id="suSaveBtn">Save</button>
            </div>
        </section>

        <section class="su-right">
            <div class="su-card su-op-card">
                <div class="su-task-title">Year Level Computation</div>
                <div class="su-task-sub">Recompute year level assignments based on your selected school year, term, and filter scope.</div>
                <div class="su-task-meta">
                    <div class="su-meta-item"><div class="su-meta-label">Affected Scope</div><div class="su-meta-value">Current Filters</div></div>
                    <div class="su-meta-item"><div class="su-meta-label">Expected Runtime</div><div class="su-meta-value">1-3 minutes</div></div>
                </div>
                <div class="su-progress"><span class="su-progress-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h5l2 3h11v7a2 2 0 0 1-2 2H3z"></path><path d="M3 7V5a2 2 0 0 1 2-2h3l2 2h9a2 2 0 0 1 2 2v3"></path></svg></span><span class="su-progress-dot"></span></div>
                <div class="su-task-foot">Waiting for Configuration and Selection</div>
                <div class="su-task-actions">
                    <button type="button" class="pf-btn-new" data-su-run="Year Level Computation">Start Now</button>
                    <button type="button" class="req-btn-cancel">Cancel</button>
                </div>
            </div>

            <div class="su-card su-op-card">
                <div class="su-task-title">Grade Recomputation</div>
                <div class="su-task-sub">Recalculate grades using the latest grading rules for selected students and current period.</div>
                <div class="su-task-meta">
                    <div class="su-meta-item"><div class="su-meta-label">Affected Scope</div><div class="su-meta-value">Current Filters</div></div>
                    <div class="su-meta-item"><div class="su-meta-label">Expected Runtime</div><div class="su-meta-value">2-5 minutes</div></div>
                </div>
                <div class="su-progress"><span class="su-progress-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h5l2 3h11v7a2 2 0 0 1-2 2H3z"></path><path d="M3 7V5a2 2 0 0 1 2-2h3l2 2h9a2 2 0 0 1 2 2v3"></path></svg></span><span class="su-progress-dot"></span></div>
                <div class="su-task-foot">Waiting for Configuration and Selection</div>
                <div class="su-task-actions">
                    <button type="button" class="pf-btn-new" data-su-run="Grade Recomputation">Start Now</button>
                    <button type="button" class="req-btn-cancel">Cancel</button>
                </div>
            </div>

            <div class="su-card su-op-card">
                <div class="su-task-title">Student Promotion</div>
                <div class="su-task-sub">Promote qualified students to the next year level using your selected period and validation rules.</div>
                <div class="su-task-meta">
                    <div class="su-meta-item"><div class="su-meta-label">Affected Scope</div><div class="su-meta-value">Current Filters</div></div>
                    <div class="su-meta-item"><div class="su-meta-label">Expected Runtime</div><div class="su-meta-value">1-2 minutes</div></div>
                </div>
                <div class="su-progress"><span class="su-progress-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h5l2 3h11v7a2 2 0 0 1-2 2H3z"></path><path d="M3 7V5a2 2 0 0 1 2-2h3l2 2h9a2 2 0 0 1 2 2v3"></path></svg></span><span class="su-progress-dot"></span></div>
                <div class="su-task-foot">Waiting for Configuration and Selection</div>
                <div class="su-task-actions">
                    <button type="button" class="pf-btn-new" data-su-run="Student Promotion">Promote Now</button>
                    <button type="button" class="req-btn-cancel">Cancel</button>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="req-modal-overlay" id="suConfirmModal" style="display:none;" onclick="if(event.target===this) suCloseConfirmModal()">
    <div class="req-modal-box su-action-modal" style="max-width:430px;">
        <h3 class="req-modal-title" id="suConfirmTitle">CONFIRM ACTION</h3>
        <p class="su-modal-text" id="suConfirmText">Do you want to continue?</p>
        <input type="hidden" id="suPendingAction" value="">
        <div class="req-modal-actions" style="margin-top:14px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="suCloseConfirmModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="suConfirmRun()">Proceed</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="suDoneModal" style="display:none;" onclick="if(event.target===this) suCloseDoneModal()">
    <div class="req-modal-box su-action-modal" style="max-width:430px;">
        <h3 class="req-modal-title">ACTION COMPLETED</h3>
        <p class="su-modal-text" id="suDoneText">The selected action has been processed successfully.</p>
        <div class="req-modal-actions" style="margin-top:14px; justify-content:center;">
            <button type="button" class="req-btn-save" onclick="suCloseDoneModal()">OK</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var suActiveCard = null;
    var suLoadingTimers = {};
    var suRunUrl = '{{ route('registrar.admin-tools.student-maintenance.student-update.run') }}';

    function suBuildPayload(actionName) {
        return {
            action_name: actionName || 'Action',
            run_mode: document.getElementById('suRunMode').value,
            school_year: document.getElementById('suSY').value,
            term: document.getElementById('suTerm').value,
            period: (document.getElementById('suPeriod').value || '').trim(),
            operator: document.getElementById('suUserName').value,
            course: document.getElementById('suCourse').value,
            year_level: document.getElementById('suYrLevel').value,
            section: document.getElementById('suSection').value,
            student_no: (document.getElementById('suStudentNo').value || '').trim(),
            include_unpaid_only: document.getElementById('suCheckPaidOnly').checked,
            active_only: document.getElementById('suActiveOnly').checked
        };
    }

    async function suRequestJson(url, method, payload) {
        var response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: payload ? JSON.stringify(payload) : null
        });

        var json = {};
        try {
            json = await response.json();
        } catch (e) {
            json = {};
        }

        if (!response.ok || json.ok === false) {
            throw new Error(
                (json.message) ||
                (json.errors && Object.values(json.errors)[0] && Object.values(json.errors)[0][0]) ||
                'Unable to process student update action.'
            );
        }

        return json;
    }

    function suOpenConfirmModal(actionName) {
        document.getElementById('suPendingAction').value = actionName || '';
        document.getElementById('suConfirmTitle').textContent = (actionName || 'Action').toUpperCase();
        document.getElementById('suConfirmText').textContent = 'Run ' + (actionName || 'this action') + ' now?';
        document.getElementById('suConfirmModal').style.display = 'flex';
    }

    function suCloseConfirmModal() {
        document.getElementById('suConfirmModal').style.display = 'none';
    }

    function suCloseDoneModal() {
        document.getElementById('suDoneModal').style.display = 'none';
    }

    function suSetCardLoading(card, isLoading) {
        if (!card) return;
        var progress = card.querySelector('.su-progress');
        var foot = card.querySelector('.su-task-foot');
        var key = card.getAttribute('data-su-key') || '';

        if (!key) {
            key = 'su-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
            card.setAttribute('data-su-key', key);
        }

        if (progress) progress.classList.toggle('running', !!isLoading);
        if (!foot) return;

        if (suLoadingTimers[key]) {
            clearInterval(suLoadingTimers[key]);
            delete suLoadingTimers[key];
        }

        if (!isLoading) {
            foot.textContent = 'Waiting for Configuration and Selection';
            return;
        }

        var frame = 0;
        foot.textContent = 'Processing update';
        suLoadingTimers[key] = setInterval(function() {
            frame = (frame + 1) % 10;
            var dots = (frame === 0) ? '' : ' .'.repeat(frame);
            foot.textContent = 'Processing update' + dots;
        }, 1000);
    }

    async function suConfirmRun() {
        var actionName = document.getElementById('suPendingAction').value || 'selected action';

        if (actionName !== 'System Configuration Save' && !document.getElementById('suRiskAcknowledge').checked) {
            alert('Please confirm the risk acknowledgement before running this action.');
            return;
        }

        suCloseConfirmModal();

        suSetCardLoading(suActiveCard, true);
        try {
            var result = await suRequestJson(suRunUrl, 'POST', suBuildPayload(actionName));
            suSetCardLoading(suActiveCard, false);
            document.getElementById('suDoneText').textContent = actionName + ' processed successfully. Affected students: ' + (result.affected_count || 0) + '.';
            document.getElementById('suDoneModal').style.display = 'flex';
            suActiveCard = null;
        } catch (error) {
            suSetCardLoading(suActiveCard, false);
            alert(error.message || 'Unable to process action.');
            suActiveCard = null;
        }
    }

    document.getElementById('suSaveBtn').addEventListener('click', function() {
        suOpenConfirmModal('System Configuration Save');
    });

    document.addEventListener('click', function(event) {
        var runBtn = event.target.closest('[data-su-run]');
        if (!runBtn) return;
        suActiveCard = runBtn.closest('.su-card');
        suOpenConfirmModal(runBtn.getAttribute('data-su-run'));
    });
</script>
@endpush

