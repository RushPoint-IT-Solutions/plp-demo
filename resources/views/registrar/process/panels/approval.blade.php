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

<div class="apc-card">
    <div class="apc-grid apc-grid--three">
        <div class="apc-field">
            <label class="apc-label">Program</label>
            <input type="text" class="apc-input" id="approvalProgramInput" value="Bachelor of Science in Computer Science" readonly />
        </div>
        <div class="apc-field">
            <label class="apc-label">Status</label>
            @include('registrar.components.listbox-select', [
                'id' => 'approvalStatusSelect',
                'name' => 'approvalStatusSelect',
                'options' => $approvalStatusOptions,
                'selected' => 'Accepted',
                'placeholder' => 'Accepted'
            ])
        </div>
        <div class="apc-field apc-field--date-save">
            <div>
                <label class="apc-label">Date Accepted</label>
                <input type="text" class="apc-input" id="approvalDateAccepted" placeholder="mm/dd/yyyy" readonly />
            </div>
            <button type="button" class="apc-btn apc-btn--save" id="approvalSaveBtn">Save</button>
        </div>
    </div>

    <div class="apc-banner" id="approvalBanner">Application, Accepted</div>
</div>
