@extends('layouts.registrar')

@section('title', 'PLP - Forms')
@section('page-title', 'FORMS')
@section('body-class', 'page-forms-placeholder')

@section('content')
<div class="pf-page">
    <div class="rep-dashboard">
        <div class="rep-top-row">
            <div class="rep-sys-card rep-sys-card--compact">
                <div class="rep-sys-title">System Configuration</div>
                <div class="rep-sys-grid">
                    <div class="rep-sys-field">
                        <label class="app-filter-label">School Year:</label>
                        <select class="app-filter-select">
                            <option>2025-2026</option>
                            <option>2024-2025</option>
                        </select>
                    </div>
                    <div class="rep-sys-field">
                        <label class="app-filter-label">Term:</label>
                        <select class="app-filter-select">
                            <option>First</option>
                            <option>Second</option>
                            <option>Summer</option>
                        </select>
                    </div>
                    <div class="rep-sys-action">
                        <button class="req-btn-save" type="button" style="height:36px; min-width: 100px; padding:0 24px; font-weight:700;">Set</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rep-group-card" style="margin-top: 10px;">
            <div class="rep-group-title" style="color: #1e3a5f; font-size: 1.3rem;">Official Forms</div>
            <p style="font-size:0.82rem; color:#4b5563; margin: 2px 0 12px;">
                Initial digital placeholders are available. Final layout/content will be updated once your official hardcopy forms are uploaded.
            </p>
            <div class="rep-grid-3">
                <button class="rep-btn" type="button" onclick="openFormsModal('Application Form')">Application Form</button>
                <button class="rep-btn" type="button" onclick="openFormsModal('Enrollment Form')">Enrollment Form</button>
                <button class="rep-btn" type="button" onclick="openFormsModal('Student Information Sheet')">Student Information Sheet</button>

                <button class="rep-btn" type="button" onclick="openFormsModal('Request Slip')">Request Slip</button>
                <button class="rep-btn" type="button" onclick="openFormsModal('Clearance Form')">Clearance Form</button>
                <button class="rep-btn" type="button" onclick="openFormsModal('Release Form')">Release Form</button>
            </div>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="formsInfoModal" style="display:none;">
    <div class="req-modal-box" style="width: 460px;">
        <h3 class="req-modal-title" id="formsModalTitle" style="color:#006837; font-size: 1rem;">FORM</h3>
        <p style="font-size: 0.82rem; color: #4b5563; margin-bottom: 16px; text-align: center;">
            Placeholder template is currently active. Exact format will follow your official hardcopy sample.
        </p>

        <div class="req-modal-fields" style="display:flex; flex-direction:column; gap:12px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Current Version</label>
                <input type="text" class="req-modal-input" value="Draft Placeholder" readonly>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Status</label>
                <input type="text" class="req-modal-input" value="Waiting for hardcopy upload" readonly>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Notes</label>
                <textarea class="req-modal-input" rows="2" readonly>Once you upload the sample hardcopy, this form will be converted to the final exact design.</textarea>
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:20px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="closeFormsModal()">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openFormsModal(title) {
        var modal = document.getElementById('formsInfoModal');
        var heading = document.getElementById('formsModalTitle');
        if (!modal || !heading) return;
        heading.textContent = String(title || 'FORM').toUpperCase();
        modal.style.display = 'flex';
    }

    function closeFormsModal() {
        var modal = document.getElementById('formsInfoModal');
        if (!modal) return;
        modal.style.display = 'none';
    }

    window.addEventListener('click', function(event) {
        if (event.target && event.target.id === 'formsInfoModal') {
            closeFormsModal();
        }
    });
</script>
@endpush
