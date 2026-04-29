document.addEventListener('DOMContentLoaded', function () {
    var composeModal = document.getElementById('composeModal');
    var viewModal = document.getElementById('viewMessageModal');
    var composeBtn = document.getElementById('msgComposeBtn');
    
    // Delegation for row clicks (Inbox/Sent)
    document.addEventListener('click', function(e) {
        var row = e.target.closest('.msg-row');
        if (row && viewModal) {
            var sender = row.getAttribute('data-sender');
            var type = row.getAttribute('data-type');
            var subject = row.getAttribute('data-subject');
            var date = row.getAttribute('data-date');
            var folder = row.getAttribute('data-folder');
            
            document.getElementById('viewMsgSender').textContent = sender;
            document.getElementById('viewMsgType').textContent = type;
            document.getElementById('viewMsgSubject').textContent = subject;
            document.getElementById('viewMsgDate').textContent = date;

            // Hide reply button for sent folder
            var replyBtn = document.getElementById('msgReplyBtn');
            if (replyBtn) {
                if (folder === 'sent') {
                    replyBtn.style.display = 'none';
                } else {
                    replyBtn.style.display = 'inline-flex';
                }
            }

            document.getElementById('viewMsgBody').textContent = "Hello,\n\nThis is a mock message content for \"" + subject + "\". In a real system, this would be fetched from the database via an AJAX call.\n\nRegards,\n" + sender;
            
            viewModal.classList.remove('faculty-gs-hidden');
        }

        // Close View Modal
        if (e.target.closest('.msg-view-close') || e.target.closest('.msg-view-close-btn')) {
            if (viewModal) viewModal.classList.add('faculty-gs-hidden');
        }

        // Reply Flow
        if (e.target.closest('#viewMessageModal .clean-btn-primary')) {
            var sender = document.getElementById('viewMsgSender').textContent;
            var subject = document.getElementById('viewMsgSubject').textContent;
            
            if (viewModal) viewModal.classList.add('faculty-gs-hidden');
            
            if (typeof openComposeModal === 'function') {
                openComposeModal(true, { 
                    subject: "Re: " + subject,
                    body: "\n\n--- Original Message ---\nFrom: " + sender + "\nSubject: " + subject + "\n"
                });
            }
        }
    });

    // Close modals on backdrop click
    window.addEventListener('click', function(e) {
        if (e.target === composeModal) {
            composeModal.classList.add('faculty-gs-hidden');
        }
        if (e.target === viewModal) {
            viewModal.classList.add('faculty-gs-hidden');
        }
    });
});
