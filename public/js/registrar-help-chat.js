(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var chatWindow = document.getElementById('regHelpChatWindow');
    var chatInput = document.getElementById('regHelpChatInput');
    var sendBtn = document.getElementById('regHelpChatSendBtn');

    if (!chatWindow || !chatInput || !sendBtn) return;

    function appendMessage(role, text) {
      var row = document.createElement('div');
      row.className = 'reg-help-chat-row ' + role;

      var bubble = document.createElement('div');
      bubble.className = 'reg-help-chat-bubble';
      bubble.textContent = text;

      row.appendChild(bubble);
      chatWindow.appendChild(row);
      chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function botReply(userText) {
      var lower = (userText || '').toLowerCase();
      if (lower.indexOf('application') !== -1) {
        return 'I can assist with that. Please share your full name and application number for verification.';
      }
      if (lower.indexOf('password') !== -1 || lower.indexOf('login') !== -1) {
        return 'For account recovery, use the Forgot Password option on login. I can guide you step by step if needed.';
      }
      return 'Thanks for your message. A support representative will review this concern and guide you shortly.';
    }

    function sendMessage() {
      var value = (chatInput.value || '').trim();
      if (value === '') return;

      appendMessage('user', value);
      chatInput.value = '';

      window.setTimeout(function () {
        appendMessage('bot', botReply(value));
      }, 450);
    }

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        sendMessage();
      }
    });
  });
})();
