@extends('layouts.registrar')

@section('title', 'PLP - Section Merging')
@section('page-title', 'SECTION MERGING')
@section('body-class', 'page-section-merging')

@section('content')
<div class="pf-page">
    <div class="smrg-content">
        <div class="smrg-card">
            <div class="smrg-card-header">
                <h3 class="smrg-card-title">System Configuration</h3>
            </div>
            <div class="smrg-card-body">
                <div class="smrg-config-form">
                    <div class="smrg-form-group smrg-config-field">
                        <label>SCHOOL YEAR</label>
                        <select class="app-filter-select">
                            <option value="2026-2027">2026-2027</option>
                            <option value="2025-2026" selected>2025-2026</option>
                            <option value="2024-2025">2024-2025</option>
                            <option value="2023-2024">2023-2024</option>
                            <option value="2022-2023">2022-2023</option>
                        </select>
                    </div>
                    <div class="smrg-form-group smrg-config-field">
                        <label>TERM</label>
                        <select class="app-filter-select">
                            <option value="First">First</option>
                            <option value="Second">Second</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    <div class="smrg-form-group smrg-config-save">
                        <button type="button" class="pf-btn pf-btn-primary" onclick="handleSectionMergingConfigSave()">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="smrg-card">
            <div class="smrg-card-header">
                <h3 class="smrg-card-title">Section Merging</h3>
            </div>
            <div class="smrg-card-body">
                <div class="smrg-split-layout">
                    <div class="smrg-side smrg-side-source">
                        <h4 class="smrg-side-title">Source Section</h4>

                        <div class="smrg-form-group full-width">
                            <label>PROGRAM</label>
                            <select class="app-filter-select" style="width: 100%;">
                                <option value="">-select Program-</option>
                                <option>BSIT</option>
                                <option>BSCS</option>
                                <option>BSED</option>
                                <option>BSAT</option>
                                <option>BSBA</option>
                                <option>BSE</option>
                                <option>BSN</option>
                                <option>BSET</option>
                            </select>
                        </div>

                        <div class="smrg-form-row">
                            <div class="smrg-form-group flex-1">
                                <label>YEAR LEVEL</label>
                                <select class="app-filter-select" style="width: 100%;">
                                    <option value="">-select Level-</option>
                                    <option>First Year</option>
                                    <option>Second Year</option>
                                    <option>Third Year</option>
                                    <option>Fourth Year</option>
                                </select>
                            </div>
                            <div class="smrg-form-group flex-1">
                                <label>SECTION</label>
                                <select class="app-filter-select" style="width: 100%;">
                                    <option value="">-select Sec-</option>
                                    <option>A</option>
                                    <option>B</option>
                                    <option>C</option>
                                    <option>D</option>
                                    <option>E</option>
                                    <option>F</option>
                                </select>
                            </div>
                        </div>

                        <div class="smrg-form-group full-width">
                            <label>SUBJECT</label>
                            <select class="app-filter-select" style="width: 100%;">
                                <option value="">-select Subject-</option>
                                <option>IT101 - Introduction to Computing</option>
                                <option>IT102 - Computer Programming 1</option>
                                <option>IT103 - Computer Programming 2</option>
                                <option>GE1 - Understanding the Self</option>
                                <option>GE2 - Readings in Philippine History</option>
                                <option>GE3 - The Contemporary World</option>
                                <option>ENG101 - Purposive Communication</option>
                            </select>
                        </div>

                        <div class="smrg-clear-wrap">
                            <button type="button" class="pf-btn pf-btn-danger">Clear Entries</button>
                        </div>
                    </div>

                    <div class="smrg-side smrg-side-target">
                        <h4 class="smrg-side-title">Target Section</h4>

                        <div class="smrg-form-group full-width">
                            <label>PROGRAM</label>
                            <select class="app-filter-select" style="width: 100%;">
                                <option value="">-select Program-</option>
                                <option>BSIT</option>
                                <option>BSCS</option>
                                <option>BSED</option>
                                <option>BSAT</option>
                                <option>BSBA</option>
                                <option>BSE</option>
                                <option>BSN</option>
                                <option>BSET</option>
                            </select>
                        </div>

                        <div class="smrg-form-row">
                            <div class="smrg-form-group flex-1">
                                <label>YEAR LEVEL</label>
                                <select class="app-filter-select" style="width: 100%;">
                                    <option value="">-select Level-</option>
                                    <option>First Year</option>
                                    <option>Second Year</option>
                                    <option>Third Year</option>
                                    <option>Fourth Year</option>
                                </select>
                            </div>
                            <div class="smrg-form-group flex-1">
                                <label>SECTION</label>
                                <select class="app-filter-select" style="width: 100%;">
                                    <option value="">-select Sec-</option>
                                    <option>A</option>
                                    <option>B</option>
                                    <option>C</option>
                                    <option>D</option>
                                    <option>E</option>
                                    <option>F</option>
                                </select>
                            </div>
                        </div>

                        <div class="smrg-form-group full-width">
                            <label>SUBJECT</label>
                            <select class="app-filter-select" style="width: 100%;">
                                <option value="">-select Subject-</option>
                                <option>IT101 - Introduction to Computing</option>
                                <option>IT102 - Computer Programming 1</option>
                                <option>IT103 - Computer Programming 2</option>
                                <option>GE1 - Understanding the Self</option>
                                <option>GE2 - Readings in Philippine History</option>
                                <option>GE3 - The Contemporary World</option>
                                <option>ENG101 - Purposive Communication</option>
                            </select>
                        </div>

                        <div class="smrg-clear-wrap">
                            <button type="button" class="pf-btn pf-btn-danger">Clear Entries</button>
                        </div>
                    </div>
                </div>

                <div class="smrg-bottom-actions">
                    <button type="button" class="pf-btn pf-btn-secondary">Cancel</button>
                    <button type="button" class="pf-btn pf-btn-primary" onclick="openMergeConfirm()">Merge</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="pf-modal-overlay" id="mergeConfirmModal" style="display:none;">
        <div class="pf-modal-box" style="text-align:center; max-width:480px; padding:30px;">
            <div class="pf-modal-title" style="color:#006837; font-size: 1.25rem; font-weight:700; margin-bottom: 20px; letter-spacing: 1px;">CONFIRM SECTION INTEGRATION</div>
            <p style="font-size:0.95rem; color:#444; margin-bottom:30px; line-height: 1.5;">Are you sure you want to merge these sections? This action will consolidate all associated documents and metadata into a single repository. This process cannot be undone.</p>
            <div class="pf-modal-actions" style="justify-content:center; gap: 16px;">
                <button type="button" class="pf-modal-btn-cancel" onclick="closeMergeConfirm()">Cancel</button>
                <button type="button" class="pf-modal-btn-save" style="background:#006837;" onmouseover="this.style.background='#004d29'" onmouseout="this.style.background='#006837'" onclick="handleMerge()">Yes, Merge Sections</button>
            </div>
        </div>
    </div>
    
    <div class="pf-modal-overlay" id="mergeSuccessModal" style="display:none;">
        <div class="pf-modal-box" style="text-align:center; max-width:420px; padding: 30px;">
            <div class="pf-modal-title" style="color:#006837; font-size: 1.3rem; font-weight:700; margin-bottom: 16px; letter-spacing: 1px;">SUCCESSFUL!</div>
            <p style="font-size:0.95rem; color:#444; margin-bottom:30px;">Sections Successfully Integrated</p>
            <div class="pf-modal-actions" style="justify-content:center;">
                <button type="button" class="pf-modal-btn-save" style="background:#006837; min-width:120px;" onmouseover="this.style.background='#004d29'" onmouseout="this.style.background='#006837'" onclick="closeMergeSuccess()">Okay</button>
            </div>
        </div>
    </div>
</div>

<script>
function openMergeConfirm() {
    document.getElementById('mergeConfirmModal').style.display = 'flex';
}
function closeMergeConfirm() {
    document.getElementById('mergeConfirmModal').style.display = 'none';
}
function handleMerge() {
    closeMergeConfirm();
    document.getElementById('mergeSuccessModal').style.display = 'flex';
}
function closeMergeSuccess() {
    document.getElementById('mergeSuccessModal').style.display = 'none';
}
function handleSectionMergingConfigSave() {
    if (typeof showRegistrarToast === 'function') {
        showRegistrarToast('System configuration saved successfully.', 'success');
    }
}
</script>
@endsection
