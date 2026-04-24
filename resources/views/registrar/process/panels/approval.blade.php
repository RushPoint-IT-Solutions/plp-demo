@php
    $approvalStatusOptions = [
        ['value' => 'Document Submitted', 'label' => 'Document Submitted'],
        ['value' => 'On Probation', 'label' => 'On Probation'],
        ['value' => 'In Process', 'label' => 'In Process'],
        ['value' => 'Rejected', 'label' => 'Rejected'],
        ['value' => 'Incomplete', 'label' => 'Incomplete'],
        ['value' => 'Accepted', 'label' => 'Accepted'],
    ];
@endphp

<div class="approval-panel-wrapper">
    {{-- Main Container Card --}}
    <div class="apc-card p-4" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        {{-- Header inside card --}}
        <div class="row align-items-center mb-4 pb-3 border-bottom">
            <div class="col-md-8">
                <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 2px;">Application Program</span>
                <h3 id="approvalProgramTitle" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2;">Bachelor of Science in Computer Science</h3>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <span class="mc-item-status status-approved" id="currentApprovalBadge" style="font-size: 0.8rem; padding: 6px 16px; font-weight: 800; border-radius: 6px;">ACCEPTED</span>
            </div>
        </div>

        {{-- Decision Area --}}
        <div class="row g-3 align-items-end">
            <div class="col-lg-5 col-md-6">
                <div class="apc-field">
                    <label class="apc-label mb-2" style="font-size: 0.7rem; font-weight: 900; color: var(--plp-green); text-transform: uppercase; letter-spacing: 0.05em;">Final Application Status</label>
                    <div class="approval-listbox-override">
                        @include('registrar.components.listbox-select', [
                            'id' => 'approvalStatusSelect',
                            'name' => 'approvalStatusSelect',
                            'options' => $approvalStatusOptions,
                            'selected' => 'Accepted',
                            'placeholder' => 'Select status'
                        ])
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="apc-field">
                    <label class="apc-label mb-2" style="font-size: 0.7rem; font-weight: 900; color: var(--plp-green); text-transform: uppercase; letter-spacing: 0.05em;">Decision Date</label>
                    <div class="position-relative">
                        <input type="text" class="apc-input w-100 js-flatpickr-approval" id="approvalDateAccepted" placeholder="mm/dd/yyyy" readonly style="height: 40px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px 0 40px; font-size: 0.9rem; background: #fff; cursor: pointer;">
                        <div class="position-absolute top-50 translate-middle-y ps-3 text-muted" style="left: 0; pointer-events: none; opacity: 0.6;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-12">
                <button type="button" class="apst-new-btn w-100" id="approvalSaveBtn" style="height: 40px; justify-content: center; font-weight: 800; background: var(--plp-green); color: #fff; border: none; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Confirm Save</button>
            </div>
        </div>

        {{-- Integrated Status Banner --}}
        <div class="mt-4 p-3 d-flex align-items-center gap-3" id="approvalBanner" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; min-height: 54px;">
            <div style="flex-shrink: 0; width: 32px; height: 32px; background: #fff; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--plp-green); border: 1px solid #e2e8f0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="banner-text-content">
                <p class="m-0" style="font-size: 0.85rem; color: #475569; font-weight: 600;" id="approvalBannerText">Decision synced. Application status updated.</p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Ensure height consistency with the listbox trigger */
    .approval-listbox-override .rg-listbox-trigger {
        height: 40px !important;
        min-height: 40px !important;
        border-radius: 8px !important;
        border-color: #cbd5e1 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    .approval-listbox-override .rg-listbox-trigger-text {
        font-size: 0.9rem !important;
        font-weight: 500 !important;
    }
    .approval-listbox-override .rg-listbox-menu {
        border-radius: 8px !important;
    }
</style>
