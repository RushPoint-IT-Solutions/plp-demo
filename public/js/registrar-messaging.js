document.addEventListener('DOMContentLoaded', function () {
    var composeModal = document.getElementById('composeModal');
    var composeBtn = document.getElementById('msgComposeBtn');
    var closeBtn = document.getElementById('msgComposeCloseBtn');
    var discardBtn = document.getElementById('msgComposeDiscardBtn');
    var sendBtn = document.getElementById('msgComposeSendBtn');
    var draftBtn = document.getElementById('msgComposeDraftBtn');
    var toInput = document.getElementById('msgTo');
    var subjectInput = document.getElementById('msgSubject');
    var bodyInput = document.getElementById('msgBody');
    var page = document.getElementById('registrarMessagingPage');

    if (!composeModal || !composeBtn || !closeBtn || !discardBtn || !sendBtn || !toInput || !subjectInput || !bodyInput) {
        return;
    }

    function resetComposeForm() {
        toInput.value = '';
        subjectInput.value = '';
        bodyInput.value = '';
    }

    function openComposeModal() {
        composeModal.classList.remove('faculty-gs-hidden');
        composeModal.setAttribute('aria-hidden', 'false');
    }

    function closeComposeModal() {
        composeModal.classList.add('faculty-gs-hidden');
        composeModal.setAttribute('aria-hidden', 'true');
        resetComposeForm();
    }

    function saveComposeModal(folder) {
        var to = toInput.value.trim();
        var subject = subjectInput.value.trim();
        var body = bodyInput.value.trim();
        var isDraft = folder === 'drafts';

        if (!isDraft && (!to || !subject || !body)) {
            alert('Please fill in all fields before sending.');
            return;
        }

        if (isDraft && !to && !subject && !body) {
            alert('Write a recipient, subject, or message before saving a draft.');
            return;
        }

        var actionBtn = isDraft && draftBtn ? draftBtn : sendBtn;
        var originalHtml = sendBtn.innerHTML;
        var originalDraftHtml = draftBtn ? draftBtn.innerHTML : '';
        sendBtn.disabled = true;
        if (draftBtn) draftBtn.disabled = true;
        actionBtn.textContent = isDraft ? 'Saving...' : 'Sending...';

        fetch(page.getAttribute('data-store-url'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': page.getAttribute('data-csrf') || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                recipient: to,
                subject: subject,
                body: body,
                folder: isDraft ? 'drafts' : 'sent'
            })
        }).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (payload) {
                if (!response.ok) {
                    throw payload;
                }

                return payload;
            });
        }).then(function (payload) {
            closeComposeModal();
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast(payload.message || 'Message saved to database.', 'success');
            }
            window.location.href = window.location.pathname + '?folder=' + (isDraft ? 'drafts' : 'sent');
        }).catch(function (payload) {
            var message = payload && payload.message ? payload.message : 'Unable to send message.';
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast(message, 'error');
            } else {
                alert(message);
            }
        }).finally(function () {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalHtml;
            if (draftBtn) {
                draftBtn.disabled = false;
                draftBtn.innerHTML = originalDraftHtml;
            }
        });
    }

    composeBtn.addEventListener('click', openComposeModal);
    closeBtn.addEventListener('click', closeComposeModal);
    discardBtn.addEventListener('click', closeComposeModal);
    sendBtn.addEventListener('click', function () { saveComposeModal('sent'); });
    if (draftBtn) {
        draftBtn.addEventListener('click', function () { saveComposeModal('drafts'); });
    }

    composeModal.addEventListener('click', function (event) {
        if (event.target === composeModal) {
            closeComposeModal();
        }
    });
});
