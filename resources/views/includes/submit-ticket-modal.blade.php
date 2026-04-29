<style>
    /* Submit Ticket Modal Styles */
    .ticket-modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        overflow: hidden;
    }
    .ticket-modal-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .ticket-modal-title {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ticket-modal-title svg {
        color: #006837;
    }
    .ticket-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #64748b;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        transition: color 0.2s;
    }
    .ticket-modal-close:hover {
        color: #0f172a;
    }
    .ticket-modal-body {
        padding: 24px;
        background: #ffffff;
    }
    .ticket-form-group {
        margin-bottom: 20px;
    }
    .ticket-form-group:last-child {
        margin-bottom: 0;
    }
    .ticket-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 8px;
    }
    .ticket-input, .ticket-textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.95rem;
        color: #1e293b;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .ticket-input:focus, .ticket-textarea:focus {
        outline: none;
        border-color: #006837;
        box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.1);
    }
    .ticket-textarea {
        resize: vertical;
        min-height: 100px;
    }
    .ticket-file-upload {
        display: block;
        width: 100%;
        box-sizing: border-box;
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8fafc;
    }
    .ticket-file-upload:hover {
        border-color: #006837;
        background: #f0fdf4;
    }
    .ticket-file-upload input[type="file"] {
        display: none;
    }
    .ticket-file-upload-icon {
        color: #64748b;
        margin-bottom: 8px;
    }
    .ticket-file-upload:hover .ticket-file-upload-icon {
        color: #006837;
    }
    .ticket-file-name {
        margin-top: 10px;
        font-size: 0.85rem;
        color: #006837;
        font-weight: 600;
    }
    .ticket-modal-footer {
        padding: 16px 24px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    .ticket-btn-cancel {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ticket-btn-cancel:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
    .ticket-btn-submit {
        background: #006837;
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 8px 24px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .ticket-btn-submit:hover {
        background: #004d29;
    }
</style>

<div class="modal fade" id="submitTicketModal" tabindex="-1" aria-labelledby="submitTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content ticket-modal-content">
            <div class="ticket-modal-header">
                <h5 class="ticket-modal-title" id="submitTicketModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5.5a2.5 2.5 0 0 0-5 0V7H8a2 2 0 0 0-2 2v2.5a2.5 2.5 0 0 1 0 5V19a2 2 0 0 0 2 2h2v-1.5a2.5 2.5 0 0 1 5 0V21h2a2 2 0 0 0 2-2v-2.5a2.5 2.5 0 0 1 0-5V9a2 2 0 0 0-2-2h-2V5.5z"/></svg>
                    Submit a Ticket
                </h5>
                <button type="button" class="ticket-modal-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="ticket-modal-body">
                <form id="submitTicketForm">
                    <div class="ticket-form-group">
                        <label class="ticket-label" for="ticketSubject">1. Subject</label>
                        <input type="text" class="ticket-input" id="ticketSubject" placeholder="Brief summary of your issue">
                    </div>
                    
                    <div class="ticket-form-group">
                        <label class="ticket-label" for="ticketDescription">2. Description / Concerns</label>
                        <textarea class="ticket-textarea" id="ticketDescription" placeholder="Please describe your concern in detail..." rows="4"></textarea>
                    </div>

                    <div class="ticket-form-group">
                        <label class="ticket-label">3. Attach File / Image</label>
                        <label class="ticket-file-upload" for="ticketAttachment">
                            <div class="ticket-file-upload-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </div>
                            <div style="font-size: 0.9rem; color: #64748b;">Click to browse or drag and drop a file</div>
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px;">Supports JPG, PNG, PDF (Max 5MB)</div>
                            <input type="file" id="ticketAttachment" accept="image/*,.pdf">
                            <div class="ticket-file-name" id="ticketFileName" style="display: none;"></div>
                        </label>
                    </div>
                </form>
            </div>
            <div class="ticket-modal-footer">
                <button type="button" class="ticket-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="ticket-btn-submit" onclick="submitTicketAction()">Submit Ticket</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('ticketAttachment');
        const fileNameDisplay = document.getElementById('ticketFileName');
        
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                if (this.files && this.files.length > 0) {
                    fileNameDisplay.textContent = 'Selected: ' + this.files[0].name;
                    fileNameDisplay.style.display = 'block';
                } else {
                    fileNameDisplay.style.display = 'none';
                    fileNameDisplay.textContent = '';
                }
            });
        }
    });

    function submitTicketAction() {
        // Mock submission action
        const btn = document.querySelector('.ticket-btn-submit');
        const originalText = btn.textContent;
        btn.textContent = 'Submitting...';
        btn.disabled = true;

        setTimeout(() => {
            alert('Ticket successfully submitted!');
            btn.textContent = originalText;
            btn.disabled = false;
            
            // Reset form
            document.getElementById('submitTicketForm').reset();
            document.getElementById('ticketFileName').style.display = 'none';
            
            // Close modal
            const modalEl = document.getElementById('submitTicketModal');
            if (window.bootstrap && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInstance.hide();
            } else if (typeof $ !== 'undefined') {
                // jQuery fallback
                $('#submitTicketModal').modal('hide');
            }
        }, 800);
    }
</script>
