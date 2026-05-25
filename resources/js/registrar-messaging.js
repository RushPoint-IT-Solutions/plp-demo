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
    var viewModal = document.getElementById('messageViewModal');
    var viewCloseBtn = document.getElementById('msgViewCloseBtn');
    var viewSender = document.getElementById('msgViewSender');
    var viewRecipient = document.getElementById('msgViewRecipient');
    var viewDate = document.getElementById('msgViewDate');
    var viewSubject = document.getElementById('msgViewSubject');
    var viewBody = document.getElementById('msgViewBody');
    var editingMessageId = null;

    if (!composeModal || !composeBtn || !closeBtn || !discardBtn || !sendBtn || !toInput || !subjectInput || !bodyInput) {
        return;
    }

    function resetComposeForm() {
        toInput.value = '';
        subjectInput.value = '';
        bodyInput.value = '';
        editingMessageId = null;
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

    function buildUrl(template, id) {
        return String(template || '').replace('__ID__', encodeURIComponent(id));
    }

    function getMessageFromRow(row) {
        if (!row) {
            return null;
        }

        try {
            return JSON.parse(row.getAttribute('data-message') || '{}');
        } catch (error) {
            return null;
        }
    }

    function openViewModal(message) {
        if (!viewModal || !message) {
            return;
        }

        viewSender.textContent = (message.sender_name || '-') + (message.sender_type ? ' (' + message.sender_type + ')' : '');
        viewRecipient.textContent = message.recipient || '-';
        viewDate.textContent = message.date || '-';
        viewSubject.textContent = message.subject || 'Untitled';
        viewBody.textContent = message.body || 'No message body.';
        viewModal.classList.remove('faculty-gs-hidden');
        viewModal.setAttribute('aria-hidden', 'false');
    }

    function closeViewModal() {
        if (!viewModal) {
            return;
        }

        viewModal.classList.add('faculty-gs-hidden');
        viewModal.setAttribute('aria-hidden', 'true');
    }

    function openEditDraft(message) {
        if (!message) {
            return;
        }

        editingMessageId = message.id;
        toInput.value = message.recipient || '';
        subjectInput.value = message.subject || '';
        bodyInput.value = message.body || '';
        openComposeModal();
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
        var endpoint = page.getAttribute('data-store-url');
        var method = 'POST';

        if (editingMessageId) {
            endpoint = buildUrl(page.getAttribute('data-update-template'), editingMessageId);
            method = 'PUT';
        }

        fetch(endpoint, {
            method: method,
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

    function deleteMessage(message) {
        if (!message || !message.id) {
            return;
        }

        var prompt = message.folder === 'trash'
            ? 'Delete this message permanently?'
            : 'Move this message to trash?';

        if (!window.confirm(prompt)) {
            return;
        }

        fetch(buildUrl(page.getAttribute('data-delete-template'), message.id), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': page.getAttribute('data-csrf') || ''
            },
            credentials: 'same-origin'
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
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast(payload.message || 'Message deleted.', 'success');
            }
            window.location.reload();
        }).catch(function (payload) {
            var messageText = payload && payload.message ? payload.message : 'Unable to delete message.';
            if (typeof showRegistrarToast === 'function') {
                showRegistrarToast(messageText, 'error');
            } else {
                alert(messageText);
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

    document.addEventListener('click', function (event) {
        var actionBtn = event.target.closest('[data-msg-action]');
        if (!actionBtn) {
            return;
        }

        var row = actionBtn.closest('tr');
        var message = getMessageFromRow(row);
        var action = actionBtn.getAttribute('data-msg-action');

        if (action === 'view') {
            openViewModal(message);
        } else if (action === 'edit') {
            openEditDraft(message);
        } else if (action === 'delete') {
            deleteMessage(message);
        }
    });

    if (viewCloseBtn) {
        viewCloseBtn.addEventListener('click', closeViewModal);
    }

    if (viewModal) {
        viewModal.addEventListener('click', function (event) {
            if (event.target === viewModal) {
                closeViewModal();
            }
        });
    }
});
