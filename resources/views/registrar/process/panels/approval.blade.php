<div class="apc-card">
    <div class="apc-grid apc-grid--three">
        <div class="apc-field">
            <label class="apc-label">Program</label>
            <input type="text" class="apc-input" id="approvalProgramInput" value="Bachelor of Science in Computer Science" readonly />
        </div>
        <div class="apc-field">
            <label class="apc-label">Status</label>
            <select class="apc-select" id="approvalStatusSelect">
                <option value="Document Submitted">Document Submitted</option>
                <option value="On Probation">On Probation</option>
                <option value="In Process">In Process</option>
                <option value="Rejected">Rejected</option>
                <option value="Incomplete">Incomplete</option>
                <option value="Accepted" selected>Accepted</option>
            </select>
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
