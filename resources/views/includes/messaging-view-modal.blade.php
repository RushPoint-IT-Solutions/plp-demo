<div class="pf-modal-overlay faculty-gs-hidden" id="viewMessageModal" aria-hidden="true" style="backdrop-filter: blur(4px); background-color: rgba(15, 23, 42, 0.5);">
    <div class="pf-modal-box" style="width: 100%; max-width: 800px; padding: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        
        <!-- Header -->
        <div style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 1.1rem; font-weight: 700; color: #000000;">Message Details</div>
            <button type="button" class="msg-view-close" id="msgViewCloseBtn" style="background: none; border: none; color: #000000; cursor: pointer; transition: all 0.2s; padding: 4px; border-radius: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <!-- Body -->
        <div style="padding: 24px; background-color: #ffffff;">
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: grid; grid-template-columns: 80px 1fr; align-items: center;">
                    <span class="clean-label" style="margin: 0;">From:</span>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span id="viewMsgSender" style="font-weight: 700; color: #000000; font-size: 1rem;">-</span>
                        <span id="viewMsgType" style="font-size: 0.7rem; background: #059669; padding: 2px 10px; border-radius: 99px; color: #ffffff; font-weight: 700; text-transform: uppercase;">-</span>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 80px 1fr; align-items: center;">
                    <span class="clean-label" style="margin: 0;">Date:</span>
                    <span id="viewMsgDate" style="color: #000000; font-weight: 500;">-</span>
                </div>
                <div style="display: grid; grid-template-columns: 80px 1fr; align-items: center;">
                    <span class="clean-label" style="margin: 0;">Subject:</span>
                    <span id="viewMsgSubject" style="font-weight: 700; color: #000000; font-size: 1rem;">-</span>
                </div>
                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 8px 0;">
                <div id="viewMsgBody" style="font-size: 0.95rem; line-height: 1.6; color: #334155; min-height: 200px; white-space: pre-wrap;">
                    Loading message content...
                </div>
                <div id="viewMsgAttachment" style="display: none; align-items: center; gap: 8px; background: #f8fafc; padding: 10px 16px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    <span style="font-size: 0.9rem; font-weight: 500; color: #2563eb; cursor: pointer;">Attached_Document.pdf</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 14px 24px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" class="msg-view-close-btn clean-btn-secondary" style="min-width: 100px;">Close</button>
            <button type="button" class="clean-btn-primary" id="msgReplyBtn" style="min-width: 120px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><polyline points="9 17 4 12 9 7"></polyline><path d="M20 18v-2a4 4 0 0 0-4-4H4"></path></svg>
                Reply
            </button>
        </div>
    </div>
</div>
