function togglePassword() {
    var passwordInput = document.getElementById('password');
    var eyeIcon = document.getElementById('eye-icon');
    var eyeSlashIcon = document.getElementById('eye-slash-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.style.display = 'none';
        eyeSlashIcon.style.display = 'inline';
    } else {
        passwordInput.type = 'password';
        eyeIcon.style.display = 'inline';
        eyeSlashIcon.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('parentCreateAccountTrigger');
    var modal = document.getElementById('parentCreateAccountModal');
    var closeBtn = document.getElementById('parentCreateAccountClose');
    var createForm = document.getElementById('parentCreateAccountForm');

    if (!trigger || !modal) {
        return;
    }

    function enableControls() {
        var controls = modal.querySelectorAll('input, select, textarea, button');
        controls.forEach(function (control) {
            control.disabled = false;
            if ('readOnly' in control) {
                control.readOnly = false;
            }
        });
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('parent-create-modal-open');
        enableControls();

        var firstField = modal.querySelector('input, select, textarea');
        if (firstField) {
            firstField.focus();
        }
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('parent-create-modal-open');
    }

    trigger.addEventListener('click', function (event) {
        event.preventDefault();
        openModal();
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', function (event) {
            event.preventDefault();
            closeModal();
        });
    }

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    if (createForm) {
        createForm.addEventListener('submit', function (event) {
            event.preventDefault();
            closeModal();

            var redirectUrl = createForm.getAttribute('data-redirect-url');
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
