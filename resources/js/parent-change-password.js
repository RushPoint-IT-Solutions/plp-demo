(function () {
    function setPasswordVisibility(passwordInputId, eyeIconId, eyeSlashIconId, toggleButtonId, isVisible) {
        var passwordInput = document.getElementById(passwordInputId);
        var eyeIcon = document.getElementById(eyeIconId);
        var eyeSlashIcon = document.getElementById(eyeSlashIconId);
        var toggleButton = document.getElementById(toggleButtonId);

        if (!passwordInput || !eyeIcon || !eyeSlashIcon || !toggleButton) {
            return;
        }

        passwordInput.type = isVisible ? 'text' : 'password';
        eyeIcon.style.display = isVisible ? 'none' : 'inline';
        eyeSlashIcon.style.display = isVisible ? 'inline' : 'none';
        toggleButton.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
    }

    function bindPasswordToggle(config) {
        var toggleButton = document.getElementById(config.toggleButtonId);
        if (!toggleButton) {
            return;
        }

        var isVisible = false;
        setPasswordVisibility(
            config.passwordInputId,
            config.eyeIconId,
            config.eyeSlashIconId,
            config.toggleButtonId,
            isVisible
        );

        toggleButton.addEventListener('click', function () {
            isVisible = !isVisible;
            setPasswordVisibility(
                config.passwordInputId,
                config.eyeIconId,
                config.eyeSlashIconId,
                config.toggleButtonId,
                isVisible
            );
        });
    }

    function initParentChangePasswordToggles() {
        bindPasswordToggle({
            passwordInputId: 'parentCurrentPassword',
            eyeIconId: 'parentCurrentPasswordEyeIcon',
            eyeSlashIconId: 'parentCurrentPasswordEyeSlashIcon',
            toggleButtonId: 'parentCurrentPasswordToggleBtn'
        });

        bindPasswordToggle({
            passwordInputId: 'parentNewPassword',
            eyeIconId: 'parentNewPasswordEyeIcon',
            eyeSlashIconId: 'parentNewPasswordEyeSlashIcon',
            toggleButtonId: 'parentNewPasswordToggleBtn'
        });

        bindPasswordToggle({
            passwordInputId: 'parentConfirmPassword',
            eyeIconId: 'parentConfirmPasswordEyeIcon',
            eyeSlashIconId: 'parentConfirmPasswordEyeSlashIcon',
            toggleButtonId: 'parentConfirmPasswordToggleBtn'
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initParentChangePasswordToggles);
        return;
    }

    initParentChangePasswordToggles();
})();
