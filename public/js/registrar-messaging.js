document.addEventListener('DOMContentLoaded', function () {
    var composeModal = document.getElementById('composeModal');
    var composeBtn = document.getElementById('msgComposeBtn');
    var closeBtn = document.getElementById('msgComposeCloseBtn');
    var discardBtn = document.getElementById('msgComposeDiscardBtn');
    var sendBtn = document.getElementById('msgComposeSendBtn');
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

    function sendComposeModal() {
        var to = toInput.value.trim();
        var subject = subjectInput.value.trim();
        var body = bodyInput.value.trim();

        if (!to || !subject || !body) {
            alert('Please fill in all fields before sending.');
            return;
        }

        var originalHtml = sendBtn.innerHTML;
        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';

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
                folder: 'sent'
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
            window.location.href = window.location.pathname + '?folder=sent';
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
        });
    }

    composeBtn.addEventListener('click', openComposeModal);
    closeBtn.addEventListener('click', closeComposeModal);
    discardBtn.addEventListener('click', closeComposeModal);
    sendBtn.addEventListener('click', sendComposeModal);

    composeModal.addEventListener('click', function (event) {
        if (event.target === composeModal) {
            closeComposeModal();
        }
    });
});
