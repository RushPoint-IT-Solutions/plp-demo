<div class="apc-card status-management-card p-0" style="border: none; box-shadow: none; background: transparent;">
    {{-- Header & Update Section Row --}}
    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 20px; margin-bottom: 20px;">
        {{-- Current Status Summary --}}
        <div class="p-3" style="background: #ffffff; border-radius: 12px; border: 1px solid #d8e3dc; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 2px 8px rgba(0, 104, 55, 0.05);">
            <span class="apc-label mb-2" style="display: flex; align-items: center; gap: 6px; font-size: 0.7rem; font-weight: 800; color: var(--plp-green); text-transform: uppercase; letter-spacing: 0.5px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                Current State
            </span>
            <div class="d-flex align-items-center gap-3">
                @php
                    $currentStatus = 'ON PROCESS'; 
                    $statusMap = [
                        'ACCEPTED' => 'status-approved',
                        'REJECTED' => 'status-not-submitted',
                        'ON PROCESS' => 'status-review',
                        'PENDING' => 'status-submitted'
                    ];
                    $statusClass = $statusMap[$currentStatus] ?? 'status-submitted';
                @endphp
                <span class="mc-item-status {{ $statusClass }}" id="currentStatusBadge" style="font-size: 0.85rem; padding: 4px 12px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center;">{{ $currentStatus }}</span>
                <span style="font-size: 0.8rem; color: #0f172a; font-weight: 600; display: inline-flex; align-items: center;">Updated: Today, 2:30 PM</span>
            </div>
        </div>

        {{-- Update Controls --}}
        <div class="p-3" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; display: grid; grid-template-columns: 200px 1fr; gap: 15px;">
            <div class="apc-field">
                <label class="apc-label" style="font-size: 0.75rem; font-weight: 800; color: var(--plp-green); margin-bottom: 6px; display: block;">UPDATE STATUS</label>
                <select class="apc-select" id="updateStatusSelect" style="height: 38px; font-size: 0.85rem; font-weight: 500; color: #334155;">
                    <option value="PENDING">PENDING</option>
                    <option value="ON PROCESS" selected>ON PROCESS</option>
                    <option value="ACCEPTED">ACCEPTED</option>
                    <option value="REJECTED">REJECTED</option>
                </select>
            </div>
            <div class="apc-field">
                <label class="apc-label" style="font-size: 0.75rem; font-weight: 800; color: var(--plp-green); margin-bottom: 6px; display: block;">REMARKS / NOTES</label>
                <input type="text" class="apc-input" id="statusRemarksInput" placeholder="Add a reason or note..." style="width: 100%; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 0.85rem; color: #334155;">
            </div>
        </div>
    </div>

    {{-- Enrollment Details Section --}}
    <div style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
            <div style="width: 3px; height: 16px; background: var(--plp-green); border-radius: 2px;"></div>
            <h4 style="font-size: 0.8rem; font-weight: 900; color: var(--plp-green); text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Enrollment Details</h4>
        </div>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div class="apc-field">
                <label class="apc-label" style="font-size: 0.7rem; font-weight: 800; color: var(--plp-green); margin-bottom: 5px; display: block;">ASSIGNED COURSE</label>
                <select class="apc-select" style="height: 38px; font-size: 0.85rem; color: #334155; font-weight: 500;">
                    <option>Bachelor of Science in Computer Science</option>
                    <option>Bachelor of Science in Information Technology</option>
                </select>
            </div>
            <div class="apc-field">
                <label class="apc-label" style="font-size: 0.7rem; font-weight: 800; color: var(--plp-green); margin-bottom: 5px; display: block;">SECTION</label>
                <select class="apc-select" style="height: 38px; font-size: 0.85rem; color: #334155; font-weight: 500;">
                    <option>A</option>
                    <option>B</option>
                    <option>C</option>
                </select>
            </div>
            <div class="apc-field">
                <label class="apc-label" style="font-size: 0.7rem; font-weight: 800; color: var(--plp-green); margin-bottom: 5px; display: block;">CURRICULUM YEAR</label>
                <select class="apc-select" style="height: 38px; font-size: 0.85rem; color: #334155; font-weight: 500;">
                    <option>2025-2026</option>
                    <option>2024-2025</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="apc-field">
                <label class="apc-label" style="font-size: 0.7rem; font-weight: 800; color: var(--plp-green); margin-bottom: 5px; display: block;">ENROLLMENT STATUS</label>
                <select class="apc-select" style="height: 38px; font-size: 0.85rem; color: #334155; font-weight: 500;">
                    <option>Regular</option>
                    <option>Irregular</option>
                </select>
            </div>
            <div class="apc-field">
                <label class="apc-label" style="font-size: 0.7rem; font-weight: 800; color: var(--plp-green); margin-bottom: 5px; display: block;">ADMISSION STATUS</label>
                <select class="apc-select" style="height: 38px; font-size: 0.85rem; color: #334155; font-weight: 500;">
                    <option>New</option>
                    <option>Old</option>
                    <option>Transferee</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="apst-new-btn" style="width: 240px; height: 42px; font-size: 0.85rem; justify-content: center; border-radius: 6px; font-weight: 700; box-shadow: 0 4px 10px rgba(0, 104, 55, 0.1);">SAVE CHANGES</button>
    </div>
</div>
