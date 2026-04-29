<div class="pf-modal-overlay faculty-gs-hidden" id="composeModal" aria-hidden="true" style="backdrop-filter: blur(4px); background-color: rgba(15, 23, 42, 0.5);">
    <div class="pf-modal-box msg-compose-modal-box" style="width: 100%; max-width: 900px; padding: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        
        <!-- Header -->
        <div class="msg-compose-modal-head" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 1.1rem; font-weight: 700; color: #000000; letter-spacing: -0.01em;">New Message</div>
            <button type="button" class="msg-compose-close" id="msgComposeCloseBtn" aria-label="Close compose modal" style="background: none; border: none; color: #000000; cursor: pointer; transition: all 0.2s; padding: 6px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="pf-modal-form" style="padding: 20px 24px; background-color: #ffffff;">
            
            <!-- Recipient Row (Reduced Space) -->
            <div style="margin-bottom: 12px;">
                <label class="clean-label" style="margin-bottom: 4px; display: block;">To</label>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <select id="msgRecipientTypeSelect" class="clean-input" onchange="toggleRecipientType()" style="width: 100%; height: 44px; cursor: pointer; font-size: 0.95rem;">
                        <option value="individual">Specific Student / Faculty</option>
                        <option value="batch">Per Batch / Group</option>
                        <option value="all_faculty">All Faculty Members</option>
                        <option value="everyone">Everyone (Broadcast)</option>
                    </select>

                    <!-- Individual Selection (Improved Mutual Exclusion) -->
                    <div id="msgToIndividualWrap" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; transition: all 0.3s ease;">
                        <div id="studentSelectContainer" style="transition: opacity 0.2s;">
                            <label class="clean-label" style="font-size: 0.75rem; color: #64748b; margin-bottom: 2px; display: block; font-weight: 600;">Student Recipient</label>
                            <select id="msgToStudent" class="clean-input" onchange="handleRecipientChange('student')" style="width: 100%; height: 44px; font-size: 0.9rem; transition: border-color 0.2s;">
                                <option value="">-- Select Student --</option>
                                @foreach($students ?? [] as $student)
                                    <option value="student_{{ $student->id }}">{{ $student->name }} ({{ $student->student_no }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="facultySelectContainer" style="transition: opacity 0.2s;">
                            <label class="clean-label" style="font-size: 0.75rem; color: #64748b; margin-bottom: 2px; display: block; font-weight: 600;">Faculty Recipient</label>
                            <select id="msgToFaculty" class="clean-input" onchange="handleRecipientChange('faculty')" style="width: 100%; height: 44px; font-size: 0.9rem; transition: border-color 0.2s;">
                                <option value="">-- Select Faculty --</option>
                                @foreach($faculties ?? [] as $faculty)
                                    <option value="faculty_{{ $faculty->id }}">{{ $faculty->name }} ({{ $faculty->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Batch Selectors -->
                    <div id="msgToBatchWrap" style="display: none; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                        <select class="clean-input" style="height: 44px; font-size: 0.85rem;"><option>- Dept -</option></select>
                        <select class="clean-input" style="height: 44px; font-size: 0.85rem;"><option>- Course -</option></select>
                        <select class="clean-input" style="height: 44px; font-size: 0.85rem;"><option>- Year -</option></select>
                        <select class="clean-input" style="height: 44px; font-size: 0.85rem;"><option>- Sect -</option></select>
                    </div>
                </div>
            </div>

            <!-- Subject Row (Reduced Space) -->
            <div style="margin-bottom: 12px;">
                <label class="clean-label" style="margin-bottom: 4px; display: block;">Subject</label>
                <input type="text" id="msgSubject" class="clean-input" placeholder="Enter subject line..." style="width: 100%; height: 44px; font-size: 0.95rem;">
            </div>

            <!-- Message Body -->
            <div style="margin-bottom: 12px;">
                <textarea id="msgBody" class="clean-input" rows="8" placeholder="Compose your message here..." style="width: 100%; resize: none; font-size: 0.95rem; line-height: 1.5; padding: 12px; min-height: 180px;"></textarea>
            </div>

            <!-- Attachment and Settings Row -->
            <div class="msg-compose-meta-row" style="display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc; padding: 10px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div class="msg-compose-attach" style="display: flex; align-items: center; gap: 12px;">
                    <label for="msgAttachment" class="app-apply-link-btn" style="cursor: pointer; gap: 8px; min-height: 34px; padding: 0 14px; font-size: 0.85rem; border-color: #cbd5e1; font-weight: 700; color: #000;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                        Choose File
                    </label>
                    <input type="file" id="msgAttachment" style="display: none;" onchange="updateFileName(this)">
                    <span id="msgFileName" style="font-size: 0.85rem; color: #000; font-weight: 500;">No file chosen</span>
                </div>
                <div class="msg-compose-savecopy">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.88rem; color: #000; font-weight: 700;">
                        <input type="checkbox" id="msgSaveToSent" checked class="app-apply-check-input" style="margin: 0;">
                        Save copy to Sent folder
                    </label>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="msg-compose-footer" style="padding: 14px 24px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div class="msg-compose-actions msg-compose-actions--left" style="display: flex;">
                <button type="button" id="msgComposeDiscardBtn" class="clean-btn-secondary" style="min-width: 100px; color: #e11d48 !important; border-color: #fecaca; background: #fff5f5;">Discard</button>
            </div>
            <div class="msg-compose-actions msg-compose-actions--right" style="display: flex; gap: 12px;">
                <button type="button" id="msgComposeDraftBtn" class="clean-btn-secondary" style="min-width: 110px;">Save Draft</button>
                <button type="button" id="msgComposeSendBtn" class="clean-btn-primary" style="min-width: 140px;">
                    Send
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Draft Saved Success Modal --}}
<div class="pf-modal-overlay faculty-gs-hidden" id="draftSavedModal" aria-hidden="true" style="backdrop-filter: blur(4px); background-color: rgba(15, 23, 42, 0.5); z-index: 10010;">
    <div class="pf-modal-box" style="width: 100%; max-width: 420px; padding: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="padding: 40px 30px; text-align: center; background: #ffffff;">
            <div style="width: 60px; height: 60px; background: #f0fdf4; color: #006837; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <h5 style="font-weight: 800; color: #1e293b; margin-bottom: 8px; font-size: 1.15rem;">Draft Saved!</h5>
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 24px; line-height: 1.5;">Your message has been saved to the Drafts folder. You can continue editing it later.</p>
            <button type="button" id="draftSavedOkBtn" style="width: 100%; padding: 10px 20px; background: #006837; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.2s;">OK</button>
        </div>
    </div>
</div>

{{-- Message Sent Success Modal --}}
<div class="pf-modal-overlay faculty-gs-hidden" id="messageSentModal" aria-hidden="true" style="backdrop-filter: blur(4px); background-color: rgba(15, 23, 42, 0.5); z-index: 10010;">
    <div class="pf-modal-box" style="width: 100%; max-width: 420px; padding: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="padding: 40px 30px; text-align: center; background: #ffffff;">
            <div style="width: 60px; height: 60px; background: #f0fdf4; color: #006837; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </div>
            <h5 style="font-weight: 800; color: #1e293b; margin-bottom: 8px; font-size: 1.15rem;">Message Sent!</h5>
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 24px; line-height: 1.5;">Your message has been sent successfully.</p>
            <button type="button" id="messageSentOkBtn" style="width: 100%; padding: 10px 20px; background: #006837; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.2s;">OK</button>
        </div>
    </div>
</div>

<style>
    #composeModal .pf-modal-box.msg-compose-modal-box {
        display: flex;
        flex-direction: column;
        max-height: 92vh;
    }
    #composeModal .pf-modal-form {
        overflow-y: auto;
    }
    #composeModal .msg-compose-meta-row {
        gap: 12px;
        flex-wrap: wrap;
    }
    #composeModal .msg-compose-attach {
        flex-wrap: wrap;
        gap: 8px;
    }
    #composeModal .msg-compose-savecopy {
        display: flex;
        align-items: center;
    }
    #composeModal #msgFileName {
        word-break: break-word;
    }
    #composeModal .msg-compose-footer {
        gap: 12px;
    }
    #composeModal .msg-compose-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    @media (max-width: 768px) {
        #composeModal .pf-modal-box.msg-compose-modal-box {
            max-width: 95vw !important;
            margin: 10px;
            max-height: 95vh;
        }
        #composeModal .pf-modal-form {
            padding: 14px 16px !important;
        }
        #composeModal #msgToIndividualWrap {
            grid-template-columns: 1fr !important;
        }
        #composeModal #msgToBatchWrap {
            grid-template-columns: 1fr 1fr !important;
        }
        #composeModal .msg-compose-meta-row {
            flex-direction: column;
            align-items: stretch !important;
        }
        #composeModal .msg-compose-footer {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-auto-rows: minmax(0, auto);
            align-items: stretch !important;
            padding: 12px 16px !important;
            gap: 10px;
        }
        #composeModal .msg-compose-actions {
            display: contents;
        }
        #composeModal #msgComposeDiscardBtn,
        #composeModal #msgComposeDraftBtn {
            width: 100%;
            min-width: 0 !important;
            white-space: nowrap;
            padding-left: 10px;
            padding-right: 10px;
        }
        #composeModal #msgComposeSendBtn {
            grid-column: 1 / -1;
            width: 100%;
            min-width: 0 !important;
        }
        #composeModal .msg-compose-modal-head {
            padding: 12px 16px !important;
        }
    }
    @media (max-width: 600px) {
        #composeModal .msg-compose-attach {
            width: 100%;
        }
    }
    @media (max-width: 480px) {
        #composeModal #msgToBatchWrap {
            grid-template-columns: 1fr !important;
        }
    }
</style>


<script>
    function updateFileName(input) {
        var fileNameSpan = document.getElementById('msgFileName');
        if (fileNameSpan && input.files && input.files[0]) {
            fileNameSpan.textContent = input.files[0].name;
        } else if (fileNameSpan) {
            fileNameSpan.textContent = 'No file chosen';
        }
    }

    function toggleRecipientType() {
        var selectElement = document.getElementById('msgRecipientTypeSelect');
        if (!selectElement) return;
        
        var recipientType = selectElement.value;
        var individualWrap = document.getElementById('msgToIndividualWrap');
        var batchWrap = document.getElementById('msgToBatchWrap');
        
        if (individualWrap) individualWrap.style.display = recipientType === 'individual' ? 'grid' : 'none';
        if (batchWrap) batchWrap.style.display = recipientType === 'batch' ? 'grid' : 'none';

        // Reset individual selections if switching away
        if (recipientType !== 'individual') {
            resetIndividualSelections();
        }
    }

    function handleRecipientChange(type) {
        var studentSelect = document.getElementById('msgToStudent');
        var facultySelect = document.getElementById('msgToFaculty');
        var studentContainer = document.getElementById('studentSelectContainer');
        var facultyContainer = document.getElementById('facultySelectContainer');

        if (type === 'student' && studentSelect.value !== '') {
            facultySelect.value = ''; // Clear faculty if student selected
            facultyContainer.style.opacity = '0.5';
            studentContainer.style.opacity = '1';
        } else if (type === 'faculty' && facultySelect.value !== '') {
            studentSelect.value = ''; // Clear student if faculty selected
            studentContainer.style.opacity = '0.5';
            facultyContainer.style.opacity = '1';
        } else {
            // Both empty
            studentContainer.style.opacity = '1';
            facultyContainer.style.opacity = '1';
        }
    }

    function resetIndividualSelections() {
        var studentSelect = document.getElementById('msgToStudent');
        var facultySelect = document.getElementById('msgToFaculty');
        var studentContainer = document.getElementById('studentSelectContainer');
        var facultyContainer = document.getElementById('facultySelectContainer');

        if (studentSelect) studentSelect.value = '';
        if (facultySelect) facultySelect.value = '';
        if (studentContainer) studentContainer.style.opacity = '1';
        if (facultyContainer) facultyContainer.style.opacity = '1';
    }

    // Modal open function to populate Drafts data
    function openComposeModal(isDraft = false, draftData = {}) {
        var composeModal = document.getElementById('composeModal');
        if (!composeModal) return;

        // Reset first
        var typeSelect = document.getElementById('msgRecipientTypeSelect');
        var toStudent = document.getElementById('msgToStudent');
        var toFaculty = document.getElementById('msgToFaculty');
        var subjectInput = document.getElementById('msgSubject');
        var bodyInput = document.getElementById('msgBody');
        var attachmentInput = document.getElementById('msgAttachment');

        if (toStudent) toStudent.value = '';
        if (toFaculty) toFaculty.value = '';
        if (subjectInput) subjectInput.value = '';
        if (bodyInput) bodyInput.value = '';
        if (attachmentInput) attachmentInput.value = '';
        if (typeSelect) {
            typeSelect.value = 'individual';
            toggleRecipientType();
        }

        if (isDraft && draftData) {
            if (subjectInput) subjectInput.value = draftData.subject || '';
            if (bodyInput) bodyInput.value = draftData.body || (draftData.subject ? 'Draft content for: ' + draftData.subject : '');
        }

        composeModal.classList.remove('faculty-gs-hidden');
        composeModal.setAttribute('aria-hidden', 'false');
    }

    document.addEventListener('DOMContentLoaded', function () {
        var composeModal = document.getElementById('composeModal');
        var composeBtn = document.getElementById('msgComposeBtn');
        var closeBtn = document.getElementById('msgComposeCloseBtn');
        var discardBtn = document.getElementById('msgComposeDiscardBtn');
        var sendBtn = document.getElementById('msgComposeSendBtn');
        var draftBtn = document.getElementById('msgComposeDraftBtn');

        if (!composeModal) return;

        function closeComposeModal() {
            composeModal.classList.add('faculty-gs-hidden');
            composeModal.setAttribute('aria-hidden', 'true');
        }

        if (composeBtn) {
            composeBtn.addEventListener('click', function() {
                openComposeModal(false);
            });
        }

        if (closeBtn) closeBtn.addEventListener('click', closeComposeModal);
        if (discardBtn) discardBtn.addEventListener('click', closeComposeModal);
        
        if (sendBtn) {
            sendBtn.addEventListener('click', function() {
                var subject = document.getElementById('msgSubject').value.trim();
                if (!subject) {
                    alert('Please enter a subject.');
                    return;
                }
                var originalHtml = sendBtn.innerHTML;
                sendBtn.disabled = true;
                sendBtn.textContent = 'Sending...';
                setTimeout(function () {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = originalHtml;
                    closeComposeModal();
                    var sentModal = document.getElementById('messageSentModal');
                    if (sentModal) sentModal.classList.remove('faculty-gs-hidden');
                }, 600);
            });
        }

        if (draftBtn) {
            draftBtn.addEventListener('click', function() {
                var originalHtml = draftBtn.innerHTML;
                draftBtn.disabled = true;
                draftBtn.textContent = 'Saving...';
                setTimeout(function () {
                    draftBtn.disabled = false;
                    draftBtn.innerHTML = originalHtml;
                    closeComposeModal();
                    var draftModal = document.getElementById('draftSavedModal');
                    if (draftModal) draftModal.classList.remove('faculty-gs-hidden');
                }, 600);
            });
        }

        composeModal.addEventListener('click', function (event) {
            if (event.target === composeModal) {
                event.stopPropagation();
                closeComposeModal();
            }
        });
        
        // Add click listener to draft rows
        var draftRows = document.querySelectorAll('.draft-row');
        draftRows.forEach(function(row) {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function() {
                var subject = this.getAttribute('data-subject') || '';
                openComposeModal(true, { subject: subject });
            });
        });

        // Draft saved modal close
        var draftSavedOkBtn = document.getElementById('draftSavedOkBtn');
        var draftSavedModal = document.getElementById('draftSavedModal');
        if (draftSavedOkBtn && draftSavedModal) {
            draftSavedOkBtn.addEventListener('click', function() {
                draftSavedModal.classList.add('faculty-gs-hidden');
            });
            draftSavedModal.addEventListener('click', function(e) {
                if (e.target === draftSavedModal) draftSavedModal.classList.add('faculty-gs-hidden');
            });
        }

        // Message sent modal close
        var messageSentOkBtn = document.getElementById('messageSentOkBtn');
        var messageSentModal = document.getElementById('messageSentModal');
        if (messageSentOkBtn && messageSentModal) {
            messageSentOkBtn.addEventListener('click', function() {
                messageSentModal.classList.add('faculty-gs-hidden');
            });
            messageSentModal.addEventListener('click', function(e) {
                if (e.target === messageSentModal) messageSentModal.classList.add('faculty-gs-hidden');
            });
        }
    });
</script>
