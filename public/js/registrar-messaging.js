document.addEventListener('DOMContentLoaded', function () {
    var composeModal = document.getElementById('composeModal');
    var composeBtn = document.getElementById('msgComposeBtn');
    var closeBtn = document.getElementById('msgComposeCloseBtn');
    var discardBtn = document.getElementById('msgComposeDiscardBtn');
    var sendBtn = document.getElementById('msgComposeSendBtn');
    var toInput = document.getElementById('msgTo');
    var subjectInput = document.getElementById('msgSubject');
    var bodyInput = document.getElementById('msgBody');

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

        setTimeout(function () {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalHtml;
            closeComposeModal();
            alert('Message sent successfully.');
        }, 600);
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
