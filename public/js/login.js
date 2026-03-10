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
